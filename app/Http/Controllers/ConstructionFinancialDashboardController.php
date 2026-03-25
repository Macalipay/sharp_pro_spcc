<?php

namespace App\Http\Controllers;

use App\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ConstructionFinancialDashboardController extends Controller
{
    public function index(Request $request)
    {
        list($startDate, $endDate, $viewMode) = $this->resolveDateRange($request);
        $asOfDate = Carbon::parse($endDate);

        $filterOptions = $this->getFilterOptions();
        $projectRows = $this->buildProjectRows($request, $startDate, $endDate, $asOfDate, $filterOptions);

        $projectIds = $projectRows->pluck('id')->filter()->values()->all();
        $projectCodes = $projectRows->pluck('project_code')->filter()->unique()->values()->all();
        $projectScopeActive = $this->hasProjectScope($request);

        $companyFinancials = $this->buildCompanyFinancials($startDate, $endDate, $endDate, $projectCodes, $projectScopeActive);
        $receivableMetrics = $this->buildReceivableMetrics($startDate, $endDate, $endDate, $projectCodes, $projectScopeActive, $companyFinancials['revenue']);
        $projectAnalytics = $this->buildProjectAnalytics($projectRows);
        $cashFlowAnalytics = $this->buildCashFlowAnalytics($startDate, $endDate, $endDate, $projectCodes, $projectScopeActive);
        $backlogAnalytics = $this->buildBacklogAnalytics($projectRows, $asOfDate, $companyFinancials['revenue']);
        $costControlAnalytics = $this->buildCostControlAnalytics($projectRows, $startDate, $endDate, $projectIds, $companyFinancials['revenue']);
        $alertItems = $this->buildAlerts($companyFinancials, $receivableMetrics, $projectRows, $cashFlowAnalytics);
        $charts = $this->buildCharts(
            $startDate,
            $endDate,
            $endDate,
            $projectRows,
            $projectCodes,
            $projectScopeActive,
            $projectAnalytics,
            $cashFlowAnalytics,
            $receivableMetrics,
            $backlogAnalytics
        );

        $filters = [
            'view_mode' => $viewMode,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'project' => $request->get('project', ''),
            'client' => $request->get('client', ''),
            'project_manager' => $request->get('project_manager', ''),
            'contract_type' => $request->get('contract_type', ''),
            'project_status' => $request->get('project_status', ''),
            'region' => $request->get('region', ''),
        ];

        $pageData = [
            'filters' => $filters,
            'filterOptions' => $filterOptions,
            'summary' => $companyFinancials,
            'projectAnalytics' => $projectAnalytics,
            'cashFlowAnalytics' => $cashFlowAnalytics,
            'receivableMetrics' => $receivableMetrics,
            'wipMetrics' => $this->buildWipMetrics($projectRows),
            'backlogAnalytics' => $backlogAnalytics,
            'costControlAnalytics' => $costControlAnalytics,
            'projectRows' => $projectRows,
            'alertItems' => $alertItems,
            'charts' => $charts,
        ];

        $export = strtolower((string) $request->get('export', ''));
        if (in_array($export, ['pdf', 'excel'], true)) {
            return $this->exportDashboard($pageData, $export, $startDate, $endDate);
        }

        $currentPath = trim((string) $request->path(), '/');
        $pageData['dashboardMode'] = ($currentPath === '' || $currentPath === 'dashboard');
        $pageData['currentPath'] = $pageData['dashboardMode'] ? '/dashboard' : '/reports/accounting/construction-financial-dashboard';
        $pageData['type'] = 'full-view';

        return view(
            $pageData['dashboardMode'] ? 'backend.pages.dashboard' : 'backend.pages.reports.construction_financial_dashboard',
            $pageData
        );
    }

    private function resolveDateRange(Request $request)
    {
        $viewMode = strtolower((string) $request->get('view_mode', 'ytd'));
        $today = Carbon::today();

        if ($viewMode === 'monthly') {
            $startDate = $today->copy()->startOfMonth()->format('Y-m-d');
            $endDate = $today->format('Y-m-d');
        } elseif ($viewMode === 'custom') {
            $startDate = $this->safeDate($request->get('start_date'), $today->copy()->startOfMonth()->format('Y-m-d'));
            $endDate = $this->safeDate($request->get('end_date'), $today->format('Y-m-d'));
        } else {
            $viewMode = 'ytd';
            $startDate = $today->copy()->startOfYear()->format('Y-m-d');
            $endDate = $today->format('Y-m-d');
        }

        if ($startDate > $endDate) {
            $tmp = $startDate;
            $startDate = $endDate;
            $endDate = $tmp;
        }

        return [$startDate, $endDate, $viewMode];
    }

    private function safeDate($value, $fallback)
    {
        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return $fallback;
        }
    }

    private function getFilterOptions()
    {
        $projects = DB::table('projects as p')
            ->leftJoin('regions as r', 'r.region_id', '=', 'p.region_id')
            ->whereNull('p.deleted_at')
            ->select(
                'p.id',
                'p.project_name',
                'p.project_owner',
                'p.project_in_charge',
                'p.project_code',
                'p.project_completion',
                'p.start_date',
                'p.completion_date',
                'r.name as region_name'
            )
            ->orderBy('p.project_name')
            ->get();

        $today = Carbon::today();

        return [
            'projects' => $projects,
            'clients' => $projects->pluck('project_owner')->filter()->unique()->sort()->values(),
            'project_managers' => $projects->pluck('project_in_charge')->filter()->unique()->sort()->values(),
            'contract_types' => collect([
                ['value' => 'fixed_price', 'label' => 'Fixed Price'],
                ['value' => 'cost_plus', 'label' => 'Cost Plus'],
                ['value' => 'time_and_material', 'label' => 'Time & Material'],
                ['value' => 'unit_price', 'label' => 'Unit Price'],
            ]),
            'project_statuses' => collect([
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'completed', 'label' => 'Completed'],
                ['value' => 'not_started', 'label' => 'Not Started'],
                ['value' => 'at_risk', 'label' => 'At Risk'],
            ]),
            'regions' => $projects->pluck('region_name')->filter()->unique()->sort()->values(),
            'today' => $today->format('Y-m-d'),
        ];
    }

    private function hasProjectScope(Request $request)
    {
        foreach (['project', 'client', 'project_manager', 'contract_type', 'project_status', 'region'] as $field) {
            if (trim((string) $request->get($field, '')) !== '') {
                return true;
            }
        }

        return false;
    }

    private function buildProjectRows(Request $request, $startDate, $endDate, Carbon $asOfDate, array $filterOptions)
    {
        $projects = DB::table('projects as p')
            ->leftJoin('regions as r', 'r.region_id', '=', 'p.region_id')
            ->whereNull('p.deleted_at')
            ->select(
                'p.id',
                'p.project_name',
                'p.project_code',
                'p.project_owner',
                'p.project_in_charge',
                'p.start_date',
                'p.completion_date',
                'p.project_completion',
                'p.contract_price',
                'r.name as region_name'
            );

        if ($request->filled('project')) {
            $projects->where('p.id', (int) $request->get('project'));
        }
        if ($request->filled('client')) {
            $projects->where('p.project_owner', $request->get('client'));
        }
        if ($request->filled('project_manager')) {
            $projects->where('p.project_in_charge', $request->get('project_manager'));
        }
        if ($request->filled('region')) {
            $projects->where('r.name', $request->get('region'));
        }

        $projectRows = $projects->orderBy('p.project_name')->get()->map(function ($project) use ($asOfDate) {
            $project->contract_type = 'fixed_price';
            $project->status = $this->deriveProjectStatus($project, $asOfDate);
            $project->contract_value = $this->toFloat($project->contract_price);
            $project->percent_complete = $this->normalizePercent($project->project_completion);
            return $project;
        });

        if ($request->filled('contract_type')) {
            $expectedType = strtolower((string) $request->get('contract_type'));
            $projectRows = $projectRows->filter(function ($project) use ($expectedType) {
                return strtolower((string) $project->contract_type) === $expectedType;
            })->values();
        }

        if ($request->filled('project_status')) {
            $expectedStatus = strtolower((string) $request->get('project_status'));
            $projectRows = $projectRows->filter(function ($project) use ($expectedStatus) {
                return strtolower((string) $project->status) === $expectedStatus;
            })->values();
        }

        $projectIds = $projectRows->pluck('id')->filter()->values()->all();
        $projectCodes = $projectRows->pluck('project_code')->filter()->values()->all();

        $projectRevenueToDate = $this->getRevenueByProjectCode(null, $endDate, $projectCodes);
        $projectRevenuePeriod = $this->getRevenueByProjectCode($startDate, $endDate, $projectCodes);
        $projectCashToDate = $this->getCashCollectionsByProjectCode($endDate, $projectCodes);
        $projectMaterialCostToDate = $this->getMaterialCostsByProject($projectIds, null, $endDate);
        $projectMaterialCostPeriod = $this->getMaterialCostsByProject($projectIds, $startDate, $endDate);
        $projectLaborCostToDate = $this->getLaborCostsByProject($projectIds, null, $endDate);
        $projectLaborCostPeriod = $this->getLaborCostsByProject($projectIds, $startDate, $endDate);
        $projectPhaseCosts = $this->getProjectPhaseCosts($projectIds, $endDate);

        $overallOperatingExpenseRate = $this->computeOperatingExpenseRate($startDate, $endDate);
        $overallCollectionRate = $this->computeCollectionRate($startDate, $endDate);

        return $projectRows->map(function ($project) use (
            $projectRevenueToDate,
            $projectRevenuePeriod,
            $projectCashToDate,
            $projectMaterialCostToDate,
            $projectMaterialCostPeriod,
            $projectLaborCostToDate,
            $projectLaborCostPeriod,
            $projectPhaseCosts,
            $overallOperatingExpenseRate,
            $overallCollectionRate
        ) {
            $revenueRecognized = $this->toFloat(isset($projectRevenueToDate[$project->project_code]) ? $projectRevenueToDate[$project->project_code] : 0);
            $periodRevenue = $this->toFloat(isset($projectRevenuePeriod[$project->project_code]) ? $projectRevenuePeriod[$project->project_code] : 0);
            $earnedRevenue = $project->contract_value * ($project->percent_complete / 100);
            if ($revenueRecognized <= 0 && $earnedRevenue > 0) {
                $revenueRecognized = $earnedRevenue;
            }

            $materialCost = $this->toFloat(isset($projectMaterialCostToDate[$project->id]) ? $projectMaterialCostToDate[$project->id] : 0);
            $laborCost = $this->toFloat(isset($projectLaborCostToDate[$project->id]) ? $projectLaborCostToDate[$project->id] : 0);
            $periodMaterialCost = $this->toFloat(isset($projectMaterialCostPeriod[$project->id]) ? $projectMaterialCostPeriod[$project->id] : 0);
            $periodLaborCost = $this->toFloat(isset($projectLaborCostPeriod[$project->id]) ? $projectLaborCostPeriod[$project->id] : 0);
            $actualCost = $materialCost + $laborCost;
            $periodCost = $periodMaterialCost + $periodLaborCost;

            $estimatedTotalCost = $actualCost;
            if ($project->percent_complete > 0 && $actualCost > 0) {
                $estimatedTotalCost = max($actualCost, $actualCost / max($project->percent_complete / 100, 0.01));
            } elseif ($project->contract_value > 0) {
                $estimatedTotalCost = $project->contract_value * 0.82;
            }

            $budgetVariance = $estimatedTotalCost - $actualCost;
            $grossProfit = $revenueRecognized - $actualCost;
            $grossMargin = $revenueRecognized > 0 ? ($grossProfit / $revenueRecognized) * 100 : 0;
            $allocatedOverhead = $revenueRecognized * $overallOperatingExpenseRate;
            $netProfit = $grossProfit - $allocatedOverhead;
            $netMargin = $revenueRecognized > 0 ? ($netProfit / $revenueRecognized) * 100 : 0;
            $costOverrun = $estimatedTotalCost > 0 ? (($actualCost - $estimatedTotalCost) / $estimatedTotalCost) * 100 : 0;
            $changeOrderValue = max($revenueRecognized - $project->contract_value, 0);
            $baseMarginAmount = max($project->contract_value - $estimatedTotalCost, 0);
            $changeOrderImpact = $changeOrderValue > 0 && $baseMarginAmount !== 0
                ? (($grossProfit - $baseMarginAmount) / abs($baseMarginAmount)) * 100
                : 0;
            $billedToDate = $revenueRecognized;
            $collectedToDate = $this->toFloat(isset($projectCashToDate[$project->project_code]) ? $projectCashToDate[$project->project_code] : 0);
            if ($collectedToDate <= 0 && $billedToDate > 0) {
                $collectedToDate = $billedToDate * $overallCollectionRate;
            }

            $overUnderBilling = $billedToDate - $earnedRevenue;
            $backlogRemaining = max($project->contract_value - $revenueRecognized, 0);
            $estimatedCostToComplete = max($estimatedTotalCost - $actualCost, 0);
            $phaseCosts = isset($projectPhaseCosts[$project->id]) ? $projectPhaseCosts[$project->id] : [];

            $project->revenue_period = $periodRevenue;
            $project->cost_period = $periodCost;
            $project->revenue_recognized = $revenueRecognized;
            $project->actual_cost = $actualCost;
            $project->estimated_total_cost = $estimatedTotalCost;
            $project->budget_variance = $budgetVariance;
            $project->gross_profit = $grossProfit;
            $project->gross_margin = $grossMargin;
            $project->net_profit = $netProfit;
            $project->net_margin = $netMargin;
            $project->cost_overrun = $costOverrun;
            $project->change_order_value = $changeOrderValue;
            $project->change_order_margin_impact = $changeOrderImpact;
            $project->earned_revenue = $earnedRevenue;
            $project->billed_to_date = $billedToDate;
            $project->collected_to_date = $collectedToDate;
            $project->over_under_billing = $overUnderBilling;
            $project->backlog_remaining = $backlogRemaining;
            $project->estimated_cost_to_complete = $estimatedCostToComplete;
            $project->material_cost = $materialCost;
            $project->labor_cost = $laborCost;
            $project->phase_costs = $phaseCosts;

            return $project;
        })->values();
    }

    private function deriveProjectStatus($project, Carbon $asOfDate)
    {
        $completion = $this->normalizePercent(isset($project->project_completion) ? $project->project_completion : 0);

        if ($completion >= 100) {
            return 'completed';
        }

        $start = $this->safeCarbon(isset($project->start_date) ? $project->start_date : null);
        $end = $this->safeCarbon(isset($project->completion_date) ? $project->completion_date : null);

        if ($start && $start->greaterThan($asOfDate)) {
            return 'not_started';
        }

        if ($end && $end->lessThan($asOfDate) && $completion < 100) {
            return 'at_risk';
        }

        return 'active';
    }

    private function safeCarbon($value)
    {
        try {
            if (!$value) {
                return null;
            }

            return Carbon::parse((string) $value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function buildCompanyFinancials($startDate, $endDate, $asOfDate, array $projectCodes, $projectScopeActive)
    {
        $revenue = $this->journalProfitAndLossAmount($startDate, $endDate, 'revenue', $projectCodes, $projectScopeActive);
        $directCosts = $this->journalProfitAndLossAmount($startDate, $endDate, 'direct_costs', $projectCodes, $projectScopeActive);
        $operatingExpenses = $this->journalProfitAndLossAmount($startDate, $endDate, 'operating_expenses', $projectCodes, $projectScopeActive);
        $interestExpense = $this->journalProfitAndLossAmount($startDate, $endDate, 'interest_expense', $projectCodes, $projectScopeActive);
        $taxExpense = $this->journalProfitAndLossAmount($startDate, $endDate, 'tax_expense', $projectCodes, $projectScopeActive);
        $depreciation = $this->journalProfitAndLossAmount($startDate, $endDate, 'depreciation', $projectCodes, $projectScopeActive);

        $grossProfit = $revenue - $directCosts;
        $operatingProfit = $grossProfit - $operatingExpenses;
        $ebitda = $operatingProfit + $depreciation;
        $netProfit = $operatingProfit - $interestExpense - $taxExpense;

        $previousStart = Carbon::parse($startDate);
        $previousEnd = Carbon::parse($endDate);
        $daySpan = $previousStart->diffInDays($previousEnd) + 1;
        $previousEnd = $previousStart->copy()->subDay();
        $previousStart = $previousEnd->copy()->subDays(max($daySpan - 1, 0));
        $previousRevenue = $this->journalProfitAndLossAmount(
            $previousStart->format('Y-m-d'),
            $previousEnd->format('Y-m-d'),
            'revenue',
            $projectCodes,
            $projectScopeActive
        );

        $cashBalance = $this->balanceSheetAmount($asOfDate, 'cash', $projectCodes, $projectScopeActive);
        $currentAssets = $this->balanceSheetAmount($asOfDate, 'current_assets', $projectCodes, $projectScopeActive);
        $inventory = $this->balanceSheetAmount($asOfDate, 'inventory', $projectCodes, $projectScopeActive);
        $currentLiabilities = $this->balanceSheetAmount($asOfDate, 'current_liabilities', $projectCodes, $projectScopeActive);
        $totalLiabilities = $this->balanceSheetAmount($asOfDate, 'total_liabilities', $projectCodes, $projectScopeActive);
        $equity = $this->balanceSheetAmount($asOfDate, 'equity', $projectCodes, $projectScopeActive) + $netProfit;
        $workingCapital = $currentAssets - $currentLiabilities;
        $currentRatio = $currentLiabilities > 0 ? $currentAssets / $currentLiabilities : null;
        $quickRatio = $currentLiabilities > 0 ? (($currentAssets - $inventory) / $currentLiabilities) : null;
        $debtToEquity = $equity > 0 ? $totalLiabilities / $equity : null;
        $interestCoverage = $interestExpense > 0 ? $ebitda / $interestExpense : null;

        return [
            'revenue' => $revenue,
            'gross_profit' => $grossProfit,
            'net_profit' => $netProfit,
            'gross_margin' => $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0,
            'net_margin' => $revenue > 0 ? ($netProfit / $revenue) * 100 : 0,
            'operating_profit' => $operatingProfit,
            'ebitda' => $ebitda,
            'revenue_growth_rate' => $previousRevenue > 0 ? (($revenue - $previousRevenue) / $previousRevenue) * 100 : null,
            'cash_balance' => $cashBalance,
            'working_capital' => $workingCapital,
            'current_assets' => $currentAssets,
            'inventory' => $inventory,
            'current_liabilities' => $currentLiabilities,
            'total_liabilities' => $totalLiabilities,
            'equity' => $equity,
            'current_ratio' => $currentRatio,
            'quick_ratio' => $quickRatio,
            'debt_to_equity' => $debtToEquity,
            'interest_coverage' => $interestCoverage,
            'direct_costs' => $directCosts,
            'operating_expenses' => $operatingExpenses,
            'interest_expense' => $interestExpense,
            'tax_expense' => $taxExpense,
            'depreciation' => $depreciation,
        ];
    }

    private function buildReceivableMetrics($startDate, $endDate, $asOfDate, array $projectCodes, $projectScopeActive, $periodRevenue)
    {
        $agingBuckets = [
            'current' => 0,
            '31_60' => 0,
            '61_90' => 0,
            '91_plus' => 0,
        ];

        $arLines = $this->baseJournalLineQuery($projectCodes, $projectScopeActive)
            ->whereDate('je.entry_date', '<=', $asOfDate)
            ->where(function ($query) {
                $query->whereRaw("UPPER(TRIM(COALESCE(at.category,''))) = 'ASSETS'")
                    ->where(function ($nameQuery) {
                        $nameQuery->whereRaw("LOWER(COALESCE(coa.account_name,'')) LIKE '%receivable%'")
                            ->orWhereRaw("LOWER(COALESCE(coa.account_name,'')) LIKE '%retention%'")
                            ->orWhereRaw("LOWER(COALESCE(coa.account_name,'')) LIKE '%unbilled%'")
                            ->orWhereRaw("LOWER(COALESCE(coa.account_name,'')) LIKE '%contract asset%'");
                    });
            })
            ->select(
                'je.entry_date',
                'coa.account_name',
                DB::raw('SUM(COALESCE(jef.debit_amount,0) - COALESCE(jef.credit_amount,0)) as balance')
            )
            ->groupBy('je.entry_date', 'coa.account_name')
            ->get();

        $totalReceivable = 0;
        $retentionReceivable = 0;
        $unbilledRevenue = 0;
        $overdueInvoices = 0;

        foreach ($arLines as $line) {
            $balance = $this->toFloat($line->balance);
            if ($balance <= 0) {
                continue;
            }

            $ageDays = Carbon::parse($line->entry_date)->diffInDays(Carbon::parse($asOfDate));
            $name = strtolower((string) $line->account_name);
            $totalReceivable += $balance;

            if (strpos($name, 'retention') !== false) {
                $retentionReceivable += $balance;
            }
            if (strpos($name, 'unbilled') !== false || strpos($name, 'contract asset') !== false) {
                $unbilledRevenue += $balance;
            }

            if ($ageDays <= 30) {
                $agingBuckets['current'] += $balance;
            } elseif ($ageDays <= 60) {
                $agingBuckets['31_60'] += $balance;
            } elseif ($ageDays <= 90) {
                $agingBuckets['61_90'] += $balance;
            } else {
                $agingBuckets['91_plus'] += $balance;
                $overdueInvoices++;
            }
        }

        $cashCollections = $this->cashMovement($startDate, $endDate, $projectCodes, $projectScopeActive)['inflow'];
        $billingBase = max($periodRevenue, 0);
        $days = max(Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1, 1);
        $dso = $billingBase > 0 ? ($totalReceivable / ($billingBase / $days)) : null;
        $billingCollectionGap = $billingBase - $cashCollections;
        $collectionEfficiency = $billingBase > 0 ? ($cashCollections / $billingBase) * 100 : null;

        return [
            'total_ar' => $totalReceivable,
            'aging' => $agingBuckets,
            'dso' => $dso,
            'billing_collection_gap' => $billingCollectionGap,
            'collection_efficiency' => $collectionEfficiency,
            'retention_receivable' => $retentionReceivable,
            'unbilled_revenue' => $unbilledRevenue,
            'overdue_invoices' => $overdueInvoices,
        ];
    }

    private function buildProjectAnalytics(Collection $projectRows)
    {
        $totalContractValue = $projectRows->sum('contract_value');
        $costIncurred = $projectRows->sum('actual_cost');
        $estimatedCostToComplete = $projectRows->sum('estimated_cost_to_complete');
        $earnedRevenue = $projectRows->sum('earned_revenue');
        $billedToDate = $projectRows->sum('billed_to_date');
        $overbillings = $projectRows->filter(function ($row) {
            return $row->over_under_billing > 0;
        })->sum('over_under_billing');
        $underbillings = abs($projectRows->filter(function ($row) {
            return $row->over_under_billing < 0;
        })->sum('over_under_billing'));
        $weightedPercentComplete = $totalContractValue > 0
            ? ($projectRows->sum(function ($row) {
                return $row->contract_value * ($row->percent_complete / 100);
            }) / $totalContractValue) * 100
            : 0;

        $topProjects = $projectRows->sortByDesc('gross_margin')->take(5)->values();
        $bottomProjects = $projectRows->sortBy('gross_margin')->take(5)->values();
        $wipGainFade = $projectRows->sum(function ($row) {
            $expectedGrossProfit = $row->contract_value - $row->estimated_total_cost;
            return $row->gross_profit - $expectedGrossProfit;
        });

        return [
            'total_contract_value' => $totalContractValue,
            'cost_incurred_to_date' => $costIncurred,
            'estimated_cost_to_complete' => $estimatedCostToComplete,
            'percent_complete' => $weightedPercentComplete,
            'earned_revenue' => $earnedRevenue,
            'amount_billed_to_date' => $billedToDate,
            'overbillings' => $overbillings,
            'underbillings' => $underbillings,
            'wip_gain_fade' => $wipGainFade,
            'top_projects' => $topProjects,
            'bottom_projects' => $bottomProjects,
        ];
    }

    private function buildCashFlowAnalytics($startDate, $endDate, $asOfDate, array $projectCodes, $projectScopeActive)
    {
        $cashMovement = $this->cashMovement($startDate, $endDate, $projectCodes, $projectScopeActive);
        $monthly = $this->monthlyCashMovement($startDate, $endDate, $projectCodes, $projectScopeActive);
        $forecast = $this->forecastCashFlow($monthly, 6);
        $burnRate = $monthly->count() > 0 ? $monthly->avg('outflow') : 0;

        return [
            'operating_cash_flow' => $cashMovement['net'],
            'monthly_inflows' => $monthly->pluck('inflow')->values(),
            'monthly_outflows' => $monthly->pluck('outflow')->values(),
            'net_cash_flow' => $cashMovement['net'],
            'forecast' => $forecast,
            'burn_rate' => $burnRate,
            'monthly_series' => $monthly,
        ];
    }

    private function buildBacklogAnalytics(Collection $projectRows, Carbon $asOfDate, $periodRevenue)
    {
        $totalBacklog = $projectRows->sum('backlog_remaining');
        $backlogGrossProfit = $projectRows->sum(function ($row) {
            $marginRate = $row->revenue_recognized > 0 ? $row->gross_profit / $row->revenue_recognized : 0;
            return $row->backlog_remaining * $marginRate;
        });
        $avgMonthlyRevenue = $periodRevenue > 0 ? ($periodRevenue / max($asOfDate->month, 1)) : 0;
        $backlogCoverage = $avgMonthlyRevenue > 0 ? $totalBacklog / $avgMonthlyRevenue : null;
        $securedFutureRevenue = $projectRows->filter(function ($row) {
            return $row->status !== 'completed';
        })->sum('backlog_remaining');
        $bidPipelineValue = $projectRows->filter(function ($row) {
            return $row->status === 'not_started';
        })->sum('contract_value');
        $bidWinRate = $projectRows->count() > 0
            ? ($projectRows->filter(function ($row) {
                return in_array($row->status, ['active', 'completed'], true);
            })->count() / $projectRows->count()) * 100
            : null;
        $averageContractValue = $projectRows->count() > 0 ? $projectRows->avg('contract_value') : 0;

        return [
            'total_backlog_value' => $totalBacklog,
            'backlog_gross_profit' => $backlogGrossProfit,
            'secured_future_revenue' => $securedFutureRevenue,
            'backlog_coverage_months' => $backlogCoverage,
            'bid_pipeline_value' => $bidPipelineValue,
            'bid_win_rate' => $bidWinRate,
            'average_contract_value' => $averageContractValue,
            'backlog_monthly' => $this->buildBacklogMonthlySeries($projectRows, $asOfDate),
        ];
    }

    private function buildCostControlAnalytics(Collection $projectRows, $startDate, $endDate, array $projectIds, $periodRevenue)
    {
        $materialCost = $projectRows->sum('material_cost');
        $laborCost = $projectRows->sum('labor_cost');
        $subcontractorCost = $this->journalProfitAndLossAmount($startDate, $endDate, 'subcontractor_cost', [], false);
        $equipmentCost = $this->journalProfitAndLossAmount($startDate, $endDate, 'equipment_cost', [], false);
        $equipmentUtilization = $this->computeEquipmentUtilization($projectIds, $startDate, $endDate);
        $idleEquipmentCost = max($equipmentCost * (1 - ($equipmentUtilization / 100)), 0);

        return [
            'labor_cost_pct' => $periodRevenue > 0 ? ($laborCost / $periodRevenue) * 100 : 0,
            'material_cost_pct' => $periodRevenue > 0 ? ($materialCost / $periodRevenue) * 100 : 0,
            'equipment_cost_pct' => $periodRevenue > 0 ? ($equipmentCost / $periodRevenue) * 100 : 0,
            'subcontractor_cost_pct' => $periodRevenue > 0 ? ($subcontractorCost / $periodRevenue) * 100 : 0,
            'equipment_utilization_rate' => $equipmentUtilization,
            'idle_equipment_cost' => $idleEquipmentCost,
            'cost_per_phase' => $this->aggregatePhaseCosts($projectRows),
        ];
    }

    private function buildWipMetrics(Collection $projectRows)
    {
        return [
            'total_contract_value' => $projectRows->sum('contract_value'),
            'cost_incurred_to_date' => $projectRows->sum('actual_cost'),
            'estimated_cost_to_complete' => $projectRows->sum('estimated_cost_to_complete'),
            'percent_complete' => $projectRows->count() > 0 ? $projectRows->avg('percent_complete') : 0,
            'earned_revenue' => $projectRows->sum('earned_revenue'),
            'amount_billed_to_date' => $projectRows->sum('billed_to_date'),
            'overbillings' => $projectRows->where('over_under_billing', '>', 0)->sum('over_under_billing'),
            'underbillings' => abs($projectRows->where('over_under_billing', '<', 0)->sum('over_under_billing')),
            'wip_gain_fade' => $projectRows->sum(function ($row) {
                return $row->gross_profit - ($row->contract_value - $row->estimated_total_cost);
            }),
        ];
    }

    private function buildAlerts(array $summary, array $receivableMetrics, Collection $projectRows, array $cashFlowAnalytics)
    {
        $alerts = [];

        if ($summary['net_margin'] < 5) {
            $alerts[] = ['type' => 'danger', 'title' => 'Low Net Margin', 'message' => 'Net margin is below 5%.'];
        }
        if ($summary['cash_balance'] < max($cashFlowAnalytics['burn_rate'], 1)) {
            $alerts[] = ['type' => 'danger', 'title' => 'Cash Risk', 'message' => 'Cash balance is below one month of average cash burn.'];
        }
        if ($receivableMetrics['dso'] !== null && $receivableMetrics['dso'] > 75) {
            $alerts[] = ['type' => 'warning', 'title' => 'Slow Collections', 'message' => 'DSO is above 75 days.'];
        }
        if ($projectRows->contains(function ($row) {
            return $row->gross_margin < 0;
        })) {
            $alerts[] = ['type' => 'danger', 'title' => 'Negative Margin Project', 'message' => 'One or more projects are running at a gross loss.'];
        }
        if ($projectRows->contains(function ($row) {
            return $row->cost_overrun > 10;
        })) {
            $alerts[] = ['type' => 'warning', 'title' => 'Cost Overrun', 'message' => 'A project has exceeded estimated cost by more than 10%.'];
        }
        if ($projectRows->contains(function ($row) {
            return $row->over_under_billing < 0;
        })) {
            $alerts[] = ['type' => 'warning', 'title' => 'Underbilling Exposure', 'message' => 'Underbillings are present in current WIP.'];
        }
        if ($receivableMetrics['overdue_invoices'] > 0) {
            $alerts[] = ['type' => 'warning', 'title' => 'Overdue Receivables', 'message' => 'Receivables aging has balances beyond 90 days.'];
        }

        return collect($alerts);
    }

    private function buildCharts(
        $startDate,
        $endDate,
        $asOfDate,
        Collection $projectRows,
        array $projectCodes,
        $projectScopeActive,
        array $projectAnalytics,
        array $cashFlowAnalytics,
        array $receivableMetrics,
        array $backlogAnalytics
    ) {
        $trendSeries = $this->monthlyTrendSeries($startDate, $endDate, $projectCodes, $projectScopeActive, $projectRows);
        $profitabilityBars = $projectRows->sortByDesc('gross_margin')->take(10)->values()->map(function ($row) {
            return [
                'label' => $row->project_name,
                'gross_margin' => round($row->gross_margin, 2),
                'net_margin' => round($row->net_margin, 2),
            ];
        });
        $wipChart = $projectRows->take(10)->values()->map(function ($row) {
            return [
                'label' => $row->project_name,
                'percent_complete' => round($row->percent_complete, 2),
                'billed_percent' => $row->contract_value > 0 ? round(($row->billed_to_date / $row->contract_value) * 100, 2) : 0,
            ];
        });

        return [
            'trend' => $trendSeries,
            'project_profitability' => $profitabilityBars,
            'cash_flow' => $cashFlowAnalytics['monthly_series']->values(),
            'ar_aging' => $receivableMetrics['aging'],
            'wip' => $wipChart,
            'backlog' => $backlogAnalytics['backlog_monthly']->values(),
        ];
    }

    private function buildCompanyFinancialsLabel()
    {
        return 'Company';
    }

    private function baseJournalLineQuery(array $projectCodes, $projectScopeActive)
    {
        $query = DB::table('journal_entry_line_fields as jef')
            ->join('journal_entries as je', function ($join) {
                $join->on('je.id', '=', 'jef.journal_entry_id')
                    ->whereNull('je.deleted_at')
                    ->whereRaw("UPPER(COALESCE(je.status,'')) = 'POSTED'");
            })
            ->join('chart_of_accounts as coa', function ($join) {
                $join->on('coa.id', '=', 'jef.chart_of_account_id')
                    ->whereNull('coa.deleted_at');
            })
            ->leftJoin('account_types as at', 'at.id', '=', 'coa.account_type')
            ->whereNull('jef.deleted_at');

        if ($projectScopeActive) {
            if (empty($projectCodes)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('jef.project_code', $projectCodes);
            }
        }

        return $query;
    }

    private function journalProfitAndLossAmount($startDate, $endDate, $metric, array $projectCodes, $projectScopeActive)
    {
        $query = $this->baseJournalLineQuery($projectCodes, $projectScopeActive)
            ->whereDate('je.entry_date', '>=', $startDate)
            ->whereDate('je.entry_date', '<=', $endDate);

        $this->applyProfitAndLossMetricFilter($query, $metric);
        $row = $query->selectRaw('SUM(COALESCE(jef.debit_amount,0)) as debit_total, SUM(COALESCE(jef.credit_amount,0)) as credit_total')->first();
        $debit = $this->toFloat(isset($row->debit_total) ? $row->debit_total : 0);
        $credit = $this->toFloat(isset($row->credit_total) ? $row->credit_total : 0);

        if (in_array($metric, ['revenue'], true)) {
            return $credit - $debit;
        }

        return $debit - $credit;
    }

    private function applyProfitAndLossMetricFilter($query, $metric)
    {
        if ($metric === 'revenue') {
            $query->where(function ($metricQuery) {
                $metricQuery->whereRaw("UPPER(TRIM(COALESCE(at.category,''))) IN ('REVENUE','INCOME')")
                    ->orWhereRaw("UPPER(TRIM(COALESCE(at.account_type,''))) IN ('REVENUE','SALES','OTHER INCOME')");
            });
            return;
        }

        if ($metric === 'direct_costs') {
            $query->where(function ($metricQuery) {
                $metricQuery->whereRaw("UPPER(TRIM(COALESCE(at.account_type,''))) = 'DIRECT COSTS'")
                    ->orWhereRaw("LOWER(COALESCE(coa.account_name,'')) LIKE '%cost of sales%'");
            });
            return;
        }

        if ($metric === 'operating_expenses') {
            $query->where(function ($metricQuery) {
                $metricQuery->whereRaw("UPPER(TRIM(COALESCE(at.category,''))) IN ('EXPENSES','EXPENSE')")
                    ->whereRaw("UPPER(TRIM(COALESCE(at.account_type,''))) <> 'DIRECT COSTS'")
                    ->whereRaw("LOWER(COALESCE(coa.account_name,'')) NOT LIKE '%interest%'")
                    ->whereRaw("LOWER(COALESCE(coa.account_name,'')) NOT LIKE '%tax%'");
            });
            return;
        }

        if ($metric === 'interest_expense') {
            $query->where(function ($metricQuery) {
                $metricQuery->whereRaw("UPPER(TRIM(COALESCE(at.category,''))) IN ('EXPENSES','EXPENSE')")
                    ->whereRaw("LOWER(COALESCE(coa.account_name,'')) LIKE '%interest%'");
            });
            return;
        }

        if ($metric === 'tax_expense') {
            $query->where(function ($metricQuery) {
                $metricQuery->whereRaw("UPPER(TRIM(COALESCE(at.category,''))) IN ('EXPENSES','EXPENSE')")
                    ->whereRaw("LOWER(COALESCE(coa.account_name,'')) LIKE '%tax%'");
            });
            return;
        }

        if ($metric === 'depreciation') {
            $query->where(function ($metricQuery) {
                $metricQuery->whereRaw("UPPER(TRIM(COALESCE(at.category,''))) IN ('EXPENSES','EXPENSE')")
                    ->whereRaw("LOWER(COALESCE(coa.account_name,'')) LIKE '%depreciation%'");
            });
            return;
        }

        if ($metric === 'equipment_cost') {
            $query->where(function ($metricQuery) {
                $metricQuery->whereRaw("UPPER(TRIM(COALESCE(at.category,''))) IN ('EXPENSES','EXPENSE')")
                    ->where(function ($nameQuery) {
                        $nameQuery->whereRaw("LOWER(COALESCE(coa.account_name,'')) LIKE '%equipment%'")
                            ->orWhereRaw("LOWER(COALESCE(coa.account_name,'')) LIKE '%rental%'");
                    });
            });
            return;
        }

        if ($metric === 'subcontractor_cost') {
            $query->where(function ($metricQuery) {
                $metricQuery->whereRaw("UPPER(TRIM(COALESCE(at.category,''))) IN ('EXPENSES','EXPENSE')")
                    ->whereRaw("LOWER(COALESCE(coa.account_name,'')) LIKE '%subcontract%'");
            });
        }
    }

    private function balanceSheetAmount($asOfDate, $metric, array $projectCodes, $projectScopeActive)
    {
        $query = $this->baseJournalLineQuery($projectCodes, $projectScopeActive)
            ->whereDate('je.entry_date', '<=', $asOfDate);

        if ($metric === 'cash') {
            $query->whereRaw("UPPER(TRIM(COALESCE(at.category,''))) = 'ASSETS'")
                ->where(function ($cashQuery) {
                    $cashQuery->whereRaw("LOWER(COALESCE(coa.account_name,'')) LIKE '%cash%'")
                        ->orWhereRaw("LOWER(COALESCE(coa.account_name,'')) LIKE '%bank%'");
                });
            $row = $query->selectRaw('SUM(COALESCE(jef.debit_amount,0) - COALESCE(jef.credit_amount,0)) as balance')->first();
            return $this->toFloat(isset($row->balance) ? $row->balance : 0);
        }

        if ($metric === 'current_assets') {
            $query->whereRaw("UPPER(TRIM(COALESCE(at.category,''))) = 'ASSETS'")
                ->whereRaw("LOWER(TRIM(COALESCE(at.account_type,''))) IN ('current assets','inventory')");
            $row = $query->selectRaw('SUM(COALESCE(jef.debit_amount,0) - COALESCE(jef.credit_amount,0)) as balance')->first();
            return $this->toFloat(isset($row->balance) ? $row->balance : 0);
        }

        if ($metric === 'inventory') {
            $query->whereRaw("UPPER(TRIM(COALESCE(at.account_type,''))) = 'INVENTORY'");
            $row = $query->selectRaw('SUM(COALESCE(jef.debit_amount,0) - COALESCE(jef.credit_amount,0)) as balance')->first();
            return $this->toFloat(isset($row->balance) ? $row->balance : 0);
        }

        if ($metric === 'current_liabilities') {
            $query->whereRaw("UPPER(TRIM(COALESCE(at.category,''))) = 'LIABILITY'")
                ->whereRaw("LOWER(TRIM(COALESCE(at.account_type,''))) IN ('current liability','current liabilities')");
            $row = $query->selectRaw('SUM(COALESCE(jef.credit_amount,0) - COALESCE(jef.debit_amount,0)) as balance')->first();
            return $this->toFloat(isset($row->balance) ? $row->balance : 0);
        }

        if ($metric === 'total_liabilities') {
            $query->whereRaw("UPPER(TRIM(COALESCE(at.category,''))) = 'LIABILITY'");
            $row = $query->selectRaw('SUM(COALESCE(jef.credit_amount,0) - COALESCE(jef.debit_amount,0)) as balance')->first();
            return $this->toFloat(isset($row->balance) ? $row->balance : 0);
        }

        if ($metric === 'equity') {
            $query->whereRaw("UPPER(TRIM(COALESCE(at.category,''))) = 'EQUITY'");
            $row = $query->selectRaw('SUM(COALESCE(jef.credit_amount,0) - COALESCE(jef.debit_amount,0)) as balance')->first();
            return $this->toFloat(isset($row->balance) ? $row->balance : 0);
        }

        return 0;
    }

    private function getRevenueByProjectCode($startDate, $endDate, array $projectCodes)
    {
        if (empty($projectCodes)) {
            return [];
        }

        $query = $this->baseJournalLineQuery($projectCodes, true)
            ->whereIn('jef.project_code', $projectCodes)
            ->where(function ($metricQuery) {
                $metricQuery->whereRaw("UPPER(TRIM(COALESCE(at.category,''))) IN ('REVENUE','INCOME')")
                    ->orWhereRaw("UPPER(TRIM(COALESCE(at.account_type,''))) IN ('REVENUE','SALES','OTHER INCOME')");
            });

        if ($startDate) {
            $query->whereDate('je.entry_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('je.entry_date', '<=', $endDate);
        }

        return $query->groupBy('jef.project_code')
            ->select('jef.project_code', DB::raw('SUM(COALESCE(jef.credit_amount,0) - COALESCE(jef.debit_amount,0)) as amount'))
            ->pluck('amount', 'jef.project_code')
            ->toArray();
    }

    private function getCashCollectionsByProjectCode($endDate, array $projectCodes)
    {
        if (empty($projectCodes)) {
            return [];
        }

        return $this->baseJournalLineQuery($projectCodes, true)
            ->whereDate('je.entry_date', '<=', $endDate)
            ->whereRaw("UPPER(TRIM(COALESCE(at.category,''))) = 'ASSETS'")
            ->where(function ($query) {
                $query->whereRaw("LOWER(COALESCE(coa.account_name,'')) LIKE '%cash%'")
                    ->orWhereRaw("LOWER(COALESCE(coa.account_name,'')) LIKE '%bank%'");
            })
            ->groupBy('jef.project_code')
            ->select('jef.project_code', DB::raw('SUM(COALESCE(jef.debit_amount,0)) as amount'))
            ->pluck('amount', 'jef.project_code')
            ->toArray();
    }

    private function getMaterialCostsByProject(array $projectIds, $startDate, $endDate)
    {
        if (empty($projectIds)) {
            return [];
        }

        $query = DB::table('purchase_orders as po')
            ->join('purchase_order_details as pod', function ($join) {
                $join->on('pod.purchase_order_id', '=', 'po.id')
                    ->whereNull('pod.deleted_at');
            })
            ->whereNull('po.deleted_at')
            ->whereIn('po.project_id', $projectIds)
            ->whereRaw("UPPER(COALESCE(po.status,'')) <> 'CANCELLED'");

        if ($startDate) {
            $query->whereDate('po.po_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('po.po_date', '<=', $endDate);
        }

        return $query->groupBy('po.project_id')
            ->select('po.project_id', DB::raw('SUM(COALESCE(pod.total_amount,0)) as amount'))
            ->pluck('amount', 'po.project_id')
            ->toArray();
    }

    private function getLaborCostsByProject(array $projectIds, $startDate, $endDate)
    {
        if (empty($projectIds)) {
            return [];
        }

        $query = DB::table('work_details')
            ->whereIn('project_id', $projectIds);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query->groupBy('project_id')
            ->select('project_id', DB::raw('SUM(COALESCE(earnings,0)) as amount'))
            ->pluck('amount', 'project_id')
            ->toArray();
    }

    private function getProjectPhaseCosts(array $projectIds, $endDate)
    {
        if (empty($projectIds)) {
            return [];
        }

        $records = DB::table('work_details as wd')
            ->leftJoin('work_types as wt', 'wt.id', '=', 'wd.worktype_id')
            ->whereIn('wd.project_id', $projectIds)
            ->whereDate('wd.created_at', '<=', $endDate)
            ->select(
                'wd.project_id',
                DB::raw("COALESCE(wt.name, 'UNASSIGNED PHASE') as phase_name"),
                DB::raw('SUM(COALESCE(wd.earnings,0)) as phase_cost')
            )
            ->groupBy('wd.project_id', 'wt.name')
            ->get();

        $grouped = [];
        foreach ($records as $record) {
            if (!isset($grouped[$record->project_id])) {
                $grouped[$record->project_id] = [];
            }
            $grouped[$record->project_id][] = [
                'phase' => $record->phase_name,
                'amount' => $this->toFloat($record->phase_cost),
            ];
        }

        return $grouped;
    }

    private function computeOperatingExpenseRate($startDate, $endDate)
    {
        $revenue = $this->journalProfitAndLossAmount($startDate, $endDate, 'revenue', [], false);
        $operatingExpenses = $this->journalProfitAndLossAmount($startDate, $endDate, 'operating_expenses', [], false);

        return $revenue > 0 ? $operatingExpenses / $revenue : 0.12;
    }

    private function computeCollectionRate($startDate, $endDate)
    {
        $revenue = $this->journalProfitAndLossAmount($startDate, $endDate, 'revenue', [], false);
        $cashMovement = $this->cashMovement($startDate, $endDate, [], false);

        return $revenue > 0 ? min(max($cashMovement['inflow'] / $revenue, 0), 1) : 0.78;
    }

    private function cashMovement($startDate, $endDate, array $projectCodes, $projectScopeActive)
    {
        $row = $this->baseJournalLineQuery($projectCodes, $projectScopeActive)
            ->whereDate('je.entry_date', '>=', $startDate)
            ->whereDate('je.entry_date', '<=', $endDate)
            ->whereRaw("UPPER(TRIM(COALESCE(at.category,''))) = 'ASSETS'")
            ->where(function ($query) {
                $query->whereRaw("LOWER(COALESCE(coa.account_name,'')) LIKE '%cash%'")
                    ->orWhereRaw("LOWER(COALESCE(coa.account_name,'')) LIKE '%bank%'");
            })
            ->selectRaw('SUM(COALESCE(jef.debit_amount,0)) as inflow, SUM(COALESCE(jef.credit_amount,0)) as outflow')
            ->first();

        $inflow = $this->toFloat(isset($row->inflow) ? $row->inflow : 0);
        $outflow = $this->toFloat(isset($row->outflow) ? $row->outflow : 0);

        return [
            'inflow' => $inflow,
            'outflow' => $outflow,
            'net' => $inflow - $outflow,
        ];
    }

    private function monthlyCashMovement($startDate, $endDate, array $projectCodes, $projectScopeActive)
    {
        $rows = $this->baseJournalLineQuery($projectCodes, $projectScopeActive)
            ->whereDate('je.entry_date', '>=', $startDate)
            ->whereDate('je.entry_date', '<=', $endDate)
            ->whereRaw("UPPER(TRIM(COALESCE(at.category,''))) = 'ASSETS'")
            ->where(function ($query) {
                $query->whereRaw("LOWER(COALESCE(coa.account_name,'')) LIKE '%cash%'")
                    ->orWhereRaw("LOWER(COALESCE(coa.account_name,'')) LIKE '%bank%'");
            })
            ->groupBy(DB::raw("DATE_FORMAT(je.entry_date, '%Y-%m')"))
            ->orderBy(DB::raw("DATE_FORMAT(je.entry_date, '%Y-%m')"))
            ->select(
                DB::raw("DATE_FORMAT(je.entry_date, '%Y-%m') as period"),
                DB::raw('SUM(COALESCE(jef.debit_amount,0)) as inflow'),
                DB::raw('SUM(COALESCE(jef.credit_amount,0)) as outflow')
            )
            ->get();

        return $this->fillMonthlySeries($startDate, $endDate, $rows, ['inflow', 'outflow'], function ($item) {
            return [
                'inflow' => $this->toFloat($item->inflow),
                'outflow' => $this->toFloat($item->outflow),
                'net' => $this->toFloat($item->inflow) - $this->toFloat($item->outflow),
            ];
        });
    }

    private function forecastCashFlow(Collection $monthlyCashSeries, $months)
    {
        $averageNet = $monthlyCashSeries->count() > 0 ? $monthlyCashSeries->avg('net') : 0;
        $lastPeriod = $monthlyCashSeries->count() > 0
            ? Carbon::createFromFormat('Y-m', $monthlyCashSeries->last()['period'])
            : Carbon::today()->startOfMonth();

        $forecast = collect();
        for ($index = 1; $index <= $months; $index++) {
            $period = $lastPeriod->copy()->addMonths($index);
            $forecast->push([
                'period' => $period->format('Y-m'),
                'label' => $period->format('M Y'),
                'net' => round($averageNet, 2),
            ]);
        }

        return $forecast;
    }

    private function monthlyTrendSeries($startDate, $endDate, array $projectCodes, $projectScopeActive, Collection $projectRows)
    {
        if ($projectScopeActive) {
            $revenueByMonth = collect($this->getRevenueTrendByProjectCodes($startDate, $endDate, $projectCodes))->keyBy('period');
            $costByMonth = collect($this->getProjectCostTrend($startDate, $endDate, $projectRows->pluck('id')->values()->all()))->keyBy('period');
            return $this->fillMonthlySeries($startDate, $endDate, [], ['revenue', 'cost'], function () {
                return ['revenue' => 0, 'cost' => 0, 'profit' => 0];
            }, function ($period) use ($revenueByMonth, $costByMonth) {
                $revenue = $revenueByMonth->has($period) ? $this->toFloat($revenueByMonth[$period]['amount']) : 0;
                $cost = $costByMonth->has($period) ? $this->toFloat($costByMonth[$period]['amount']) : 0;
                return [
                    'revenue' => $revenue,
                    'cost' => $cost,
                    'profit' => $revenue - $cost,
                ];
            });
        }

        $rows = $this->baseJournalLineQuery([], false)
            ->whereDate('je.entry_date', '>=', $startDate)
            ->whereDate('je.entry_date', '<=', $endDate)
            ->select(
                DB::raw("DATE_FORMAT(je.entry_date, '%Y-%m') as period"),
                DB::raw("SUM(CASE WHEN UPPER(TRIM(COALESCE(at.category,''))) IN ('REVENUE','INCOME') OR UPPER(TRIM(COALESCE(at.account_type,''))) IN ('REVENUE','SALES','OTHER INCOME') THEN COALESCE(jef.credit_amount,0) - COALESCE(jef.debit_amount,0) ELSE 0 END) as revenue_amount"),
                DB::raw("SUM(CASE WHEN UPPER(TRIM(COALESCE(at.account_type,''))) = 'DIRECT COSTS' THEN COALESCE(jef.debit_amount,0) - COALESCE(jef.credit_amount,0) ELSE 0 END) as cost_amount"),
                DB::raw("SUM(CASE WHEN UPPER(TRIM(COALESCE(at.category,''))) IN ('EXPENSES','EXPENSE') AND UPPER(TRIM(COALESCE(at.account_type,''))) <> 'DIRECT COSTS' THEN COALESCE(jef.debit_amount,0) - COALESCE(jef.credit_amount,0) ELSE 0 END) as opex_amount")
            )
            ->groupBy(DB::raw("DATE_FORMAT(je.entry_date, '%Y-%m')"))
            ->orderBy(DB::raw("DATE_FORMAT(je.entry_date, '%Y-%m')"))
            ->get();

        return $this->fillMonthlySeries($startDate, $endDate, $rows, ['revenue_amount', 'cost_amount', 'opex_amount'], function ($item) {
            $revenue = $this->toFloat($item->revenue_amount);
            $cost = $this->toFloat($item->cost_amount) + $this->toFloat($item->opex_amount);
            return [
                'revenue' => $revenue,
                'cost' => $cost,
                'profit' => $revenue - $cost,
            ];
        });
    }

    private function getRevenueTrendByProjectCodes($startDate, $endDate, array $projectCodes)
    {
        if (empty($projectCodes)) {
            return [];
        }

        return $this->baseJournalLineQuery($projectCodes, true)
            ->whereDate('je.entry_date', '>=', $startDate)
            ->whereDate('je.entry_date', '<=', $endDate)
            ->where(function ($query) {
                $query->whereRaw("UPPER(TRIM(COALESCE(at.category,''))) IN ('REVENUE','INCOME')")
                    ->orWhereRaw("UPPER(TRIM(COALESCE(at.account_type,''))) IN ('REVENUE','SALES','OTHER INCOME')");
            })
            ->groupBy(DB::raw("DATE_FORMAT(je.entry_date, '%Y-%m')"))
            ->orderBy(DB::raw("DATE_FORMAT(je.entry_date, '%Y-%m')"))
            ->select(DB::raw("DATE_FORMAT(je.entry_date, '%Y-%m') as period"), DB::raw('SUM(COALESCE(jef.credit_amount,0) - COALESCE(jef.debit_amount,0)) as amount'))
            ->get()
            ->map(function ($row) {
                return ['period' => $row->period, 'amount' => $row->amount];
            })
            ->all();
    }

    private function getProjectCostTrend($startDate, $endDate, array $projectIds)
    {
        if (empty($projectIds)) {
            return [];
        }

        $materialRows = DB::table('purchase_orders as po')
            ->join('purchase_order_details as pod', function ($join) {
                $join->on('pod.purchase_order_id', '=', 'po.id')
                    ->whereNull('pod.deleted_at');
            })
            ->whereNull('po.deleted_at')
            ->whereIn('po.project_id', $projectIds)
            ->whereDate('po.po_date', '>=', $startDate)
            ->whereDate('po.po_date', '<=', $endDate)
            ->groupBy(DB::raw("DATE_FORMAT(po.po_date, '%Y-%m')"))
            ->select(DB::raw("DATE_FORMAT(po.po_date, '%Y-%m') as period"), DB::raw('SUM(COALESCE(pod.total_amount,0)) as amount'))
            ->get();

        $laborRows = DB::table('work_details')
            ->whereIn('project_id', $projectIds)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as period"), DB::raw('SUM(COALESCE(earnings,0)) as amount'))
            ->get();

        $merged = [];
        foreach ($materialRows as $row) {
            if (!isset($merged[$row->period])) {
                $merged[$row->period] = 0;
            }
            $merged[$row->period] += $this->toFloat($row->amount);
        }
        foreach ($laborRows as $row) {
            if (!isset($merged[$row->period])) {
                $merged[$row->period] = 0;
            }
            $merged[$row->period] += $this->toFloat($row->amount);
        }

        $results = [];
        foreach ($merged as $period => $amount) {
            $results[] = ['period' => $period, 'amount' => $amount];
        }

        return $results;
    }

    private function fillMonthlySeries($startDate, $endDate, $rows, array $fields, callable $mapper, callable $emptyMapper = null)
    {
        $indexed = collect($rows)->keyBy('period');
        $series = collect();
        $cursor = Carbon::parse($startDate)->startOfMonth();
        $end = Carbon::parse($endDate)->startOfMonth();

        while ($cursor->lte($end)) {
            $period = $cursor->format('Y-m');
            if ($indexed->has($period)) {
                $data = $mapper($indexed[$period]);
            } elseif ($emptyMapper) {
                $data = $emptyMapper($period);
            } else {
                $data = [];
                foreach ($fields as $field) {
                    $data[$field] = 0;
                }
            }

            $data['period'] = $period;
            $data['label'] = $cursor->format('M Y');
            $series->push($data);
            $cursor->addMonth();
        }

        return $series;
    }

    private function buildBacklogMonthlySeries(Collection $projectRows, Carbon $asOfDate)
    {
        $buckets = [];

        foreach ($projectRows as $row) {
            $remaining = $row->backlog_remaining;
            if ($remaining <= 0) {
                continue;
            }

            $completionDate = $this->safeCarbon($row->completion_date);
            if (!$completionDate || $completionDate->lessThan($asOfDate)) {
                $completionDate = $asOfDate->copy()->addMonth();
            }

            $start = $asOfDate->copy()->startOfMonth();
            $end = $completionDate->copy()->startOfMonth();
            $months = max($start->diffInMonths($end) + 1, 1);
            $monthlyShare = $remaining / $months;

            for ($index = 0; $index < $months; $index++) {
                $period = $start->copy()->addMonths($index)->format('Y-m');
                if (!isset($buckets[$period])) {
                    $buckets[$period] = 0;
                }
                $buckets[$period] += $monthlyShare;
            }
        }

        ksort($buckets);
        $series = collect();
        foreach ($buckets as $period => $amount) {
            $date = Carbon::createFromFormat('Y-m', $period);
            $series->push([
                'period' => $period,
                'label' => $date->format('M Y'),
                'amount' => round($amount, 2),
            ]);
        }

        return $series;
    }

    private function computeEquipmentUtilization(array $projectIds, $startDate, $endDate)
    {
        if (empty($projectIds)) {
            return 0;
        }

        $totalTransactions = DB::table('inventory_transactions')
            ->whereIn('project_id', $projectIds)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->count();

        if ($totalTransactions === 0) {
            return 0;
        }

        $activeTransactions = DB::table('inventory_transactions')
            ->whereIn('project_id', $projectIds)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->where(function ($query) {
                $query->whereRaw("LOWER(COALESCE(remarks,'')) LIKE '%issue%'")
                    ->orWhereRaw("LOWER(COALESCE(remarks,'')) LIKE '%use%'")
                    ->orWhereRaw("LOWER(COALESCE(remarks,'')) LIKE '%release%'");
            })
            ->count();

        return ($activeTransactions / $totalTransactions) * 100;
    }

    private function aggregatePhaseCosts(Collection $projectRows)
    {
        $totals = [];

        foreach ($projectRows as $row) {
            foreach ($row->phase_costs as $phaseRow) {
                $phase = $phaseRow['phase'];
                if (!isset($totals[$phase])) {
                    $totals[$phase] = 0;
                }
                $totals[$phase] += $this->toFloat($phaseRow['amount']);
            }
        }

        arsort($totals);
        $result = collect();
        foreach ($totals as $phase => $amount) {
            $result->push(['phase' => $phase, 'amount' => $amount]);
        }

        return $result->values();
    }

    private function exportDashboard(array $pageData, $export, $startDate, $endDate)
    {
        $summary = $pageData['summary'];
        $projectRows = $pageData['projectRows'];
        $alerts = $pageData['alertItems'];

        $html = '<html><head><meta charset="UTF-8"><style>
            body{font-family:Arial,sans-serif;font-size:12px;padding:20px;color:#1e293b;}
            h2,h3{margin:0 0 12px 0;}
            table{width:100%;border-collapse:collapse;margin-bottom:20px;}
            th,td{border:1px solid #cbd5e1;padding:6px 8px;vertical-align:top;}
            th{background:#0f4c5c;color:#fff;text-align:left;}
            .cards{display:flex;flex-wrap:wrap;margin:0 -8px 16px;}
            .card{width:25%;padding:0 8px;box-sizing:border-box;margin-bottom:12px;}
            .box{border:1px solid #cbd5e1;border-radius:8px;padding:10px;background:#f8fafc;}
            .label{font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.04em;}
            .value{font-size:20px;font-weight:bold;margin-top:4px;}
            .alert{padding:10px;border-radius:8px;margin-bottom:8px;}
            .danger{background:#fee2e2;color:#991b1b;}
            .warning{background:#fef3c7;color:#92400e;}
        </style></head><body>';
        $html .= '<h2>Construction Financial KPI Dashboard</h2>';
        $html .= '<div>Reporting Period: ' . e(Carbon::parse($startDate)->format('M d, Y')) . ' to ' . e(Carbon::parse($endDate)->format('M d, Y')) . '</div>';

        if ($alerts->count() > 0) {
            $html .= '<h3 style="margin-top:18px;">Critical Alerts</h3>';
            foreach ($alerts as $alert) {
                $html .= '<div class="alert ' . e($alert['type']) . '"><strong>' . e($alert['title']) . ':</strong> ' . e($alert['message']) . '</div>';
            }
        }

        $html .= '<div class="cards">';
        foreach ([
            'Total Revenue' => $summary['revenue'],
            'Gross Profit' => $summary['gross_profit'],
            'Net Profit' => $summary['net_profit'],
            'Cash Balance' => $summary['cash_balance'],
            'Working Capital' => $summary['working_capital'],
            'Gross Margin %' => round($summary['gross_margin'], 2) . '%',
            'Net Margin %' => round($summary['net_margin'], 2) . '%',
            'Current Ratio' => $summary['current_ratio'] !== null ? round($summary['current_ratio'], 2) . 'x' : 'N/A',
        ] as $label => $value) {
            $display = is_numeric($value) ? $this->formatCurrency($value) : $value;
            $html .= '<div class="card"><div class="box"><div class="label">' . e($label) . '</div><div class="value">' . e($display) . '</div></div></div>';
        }
        $html .= '</div>';

        $html .= '<h3>Project Financial Detail</h3>';
        $html .= '<table><thead><tr>
            <th>Project Name</th>
            <th>Client</th>
            <th>Contract Value</th>
            <th>Revenue Recognized</th>
            <th>Actual Cost</th>
            <th>Estimated Total Cost</th>
            <th>Gross Margin %</th>
            <th>Percent Complete</th>
            <th>Billed to Date</th>
            <th>Collected to Date</th>
            <th>Over/Under Billing</th>
            <th>Backlog Remaining</th>
            <th>Status</th>
        </tr></thead><tbody>';

        foreach ($projectRows as $row) {
            $html .= '<tr>';
            $html .= '<td>' . e($row->project_name) . '</td>';
            $html .= '<td>' . e($row->project_owner) . '</td>';
            $html .= '<td style="text-align:right;">' . e($this->formatCurrency($row->contract_value)) . '</td>';
            $html .= '<td style="text-align:right;">' . e($this->formatCurrency($row->revenue_recognized)) . '</td>';
            $html .= '<td style="text-align:right;">' . e($this->formatCurrency($row->actual_cost)) . '</td>';
            $html .= '<td style="text-align:right;">' . e($this->formatCurrency($row->estimated_total_cost)) . '</td>';
            $html .= '<td style="text-align:right;">' . e(number_format($row->gross_margin, 2)) . '%</td>';
            $html .= '<td style="text-align:right;">' . e(number_format($row->percent_complete, 2)) . '%</td>';
            $html .= '<td style="text-align:right;">' . e($this->formatCurrency($row->billed_to_date)) . '</td>';
            $html .= '<td style="text-align:right;">' . e($this->formatCurrency($row->collected_to_date)) . '</td>';
            $html .= '<td style="text-align:right;">' . e($this->formatCurrency($row->over_under_billing)) . '</td>';
            $html .= '<td style="text-align:right;">' . e($this->formatCurrency($row->backlog_remaining)) . '</td>';
            $html .= '<td>' . e(strtoupper(str_replace('_', ' ', $row->status))) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        if ($export === 'pdf') {
            $html .= '<script>window.onload=function(){window.print();}</script>';
        }
        $html .= '</body></html>';

        if ($export === 'excel') {
            return response($html, 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="construction_financial_dashboard_' . $startDate . '_to_' . $endDate . '.xls"',
            ]);
        }

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    private function normalizePercent($value)
    {
        $numeric = $this->toFloat($value);
        if ($numeric < 0) {
            return 0;
        }
        if ($numeric > 100) {
            return 100;
        }

        return $numeric;
    }

    private function toFloat($value)
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = preg_replace('/[^0-9.\-]/', '', (string) $value);
        if ($normalized === '' || $normalized === '-' || $normalized === '.' || $normalized === '-.') {
            return 0.0;
        }

        return (float) $normalized;
    }

    private function formatCurrency($value)
    {
        return number_format($this->toFloat($value), 2);
    }
}
