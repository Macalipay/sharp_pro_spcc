<?php

use App\Events\FormSubmitted;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::group(['middleware' => ['auth']], function() {

    Route::get('/', function () {
        $totalEmployees = \App\EmployeeInformation::whereNull('deleted_at')->count();
        $totalPayrollDraft = \App\PayrollSummary::whereNull('deleted_at')
            ->whereRaw('COALESCE(workflow_status, 0) = 0')
            ->count();
        $totalPayrollApproved = \App\PayrollSummary::whereNull('deleted_at')
            ->whereRaw('COALESCE(workflow_status, 0) IN (2, 3)')
            ->count();
        $totalNetPaid2024 = \Illuminate\Support\Facades\DB::table('payroll_summary_details as d')
            ->join('payroll_summaries as s', function ($join) {
                $join->on('s.id', '=', 'd.summary_id')
                    ->orOn('s.sequence_no', '=', 'd.sequence_no');
            })
            ->whereNull('d.deleted_at')
            ->whereNull('s.deleted_at')
            ->where(function ($query) {
                $query->whereRaw('COALESCE(s.workflow_status, 0) IN (2, 3)')
                    ->orWhereIn('s.status', [1, 2]);
            })
            ->whereRaw("YEAR(STR_TO_DATE(s.payroll_period, '%Y-%m-%d')) = 2024")
            ->sum('d.net_pay');
        $totalNetPaid2025 = \Illuminate\Support\Facades\DB::table('payroll_summary_details as d')
            ->join('payroll_summaries as s', function ($join) {
                $join->on('s.id', '=', 'd.summary_id')
                    ->orOn('s.sequence_no', '=', 'd.sequence_no');
            })
            ->whereNull('d.deleted_at')
            ->whereNull('s.deleted_at')
            ->where(function ($query) {
                $query->whereRaw('COALESCE(s.workflow_status, 0) IN (2, 3)')
                    ->orWhereIn('s.status', [1, 2]);
            })
            ->whereRaw("YEAR(STR_TO_DATE(s.payroll_period, '%Y-%m-%d')) = 2025")
            ->sum('d.net_pay');

        return view('backend.pages.payroll.transaction.employee.dashboard', compact(
            'totalEmployees',
            'totalPayrollDraft',
            'totalPayrollApproved',
            'totalNetPaid2024',
            'totalNetPaid2025'
        ), ["type" => "full-view"]);
    });

    Route::get('/po-sample', function () {
        // return view('backend.pages.dashboard');
        return view('backend.partial.purchase_order');
    });

    Route::get('/cv-sample', function () {
        // return view('backend.pages.dashboard');
        return view('backend.partial.cv');
    });


    Route::get('/dashboard', function () {
        $totalEmployees = \App\EmployeeInformation::whereNull('deleted_at')->count();
        $totalPayrollDraft = \App\PayrollSummary::whereNull('deleted_at')
            ->whereRaw('COALESCE(workflow_status, 0) = 0')
            ->count();
        $totalPayrollApproved = \App\PayrollSummary::whereNull('deleted_at')
            ->whereRaw('COALESCE(workflow_status, 0) IN (2, 3)')
            ->count();
        $totalNetPaid2024 = \Illuminate\Support\Facades\DB::table('payroll_summary_details as d')
            ->join('payroll_summaries as s', function ($join) {
                $join->on('s.id', '=', 'd.summary_id')
                    ->orOn('s.sequence_no', '=', 'd.sequence_no');
            })
            ->whereNull('d.deleted_at')
            ->whereNull('s.deleted_at')
            ->where(function ($query) {
                $query->whereRaw('COALESCE(s.workflow_status, 0) IN (2, 3)')
                    ->orWhereIn('s.status', [1, 2]);
            })
            ->whereRaw("YEAR(STR_TO_DATE(s.payroll_period, '%Y-%m-%d')) = 2024")
            ->sum('d.net_pay');
        $totalNetPaid2025 = \Illuminate\Support\Facades\DB::table('payroll_summary_details as d')
            ->join('payroll_summaries as s', function ($join) {
                $join->on('s.id', '=', 'd.summary_id')
                    ->orOn('s.sequence_no', '=', 'd.sequence_no');
            })
            ->whereNull('d.deleted_at')
            ->whereNull('s.deleted_at')
            ->where(function ($query) {
                $query->whereRaw('COALESCE(s.workflow_status, 0) IN (2, 3)')
                    ->orWhereIn('s.status', [1, 2]);
            })
            ->whereRaw("YEAR(STR_TO_DATE(s.payroll_period, '%Y-%m-%d')) = 2025")
            ->sum('d.net_pay');

        return view('backend.pages.payroll.transaction.employee.dashboard', compact(
            'totalEmployees',
            'totalPayrollDraft',
            'totalPayrollApproved',
            'totalNetPaid2024',
            'totalNetPaid2025'
        ), ["type" => "full-view"]);
    });

    Route::get('/reports', function () {
        return view('backend.pages.reports.index', ["type" => "full-view"]);
    })->name('reports');

    Route::get('/reports/payroll', function () {
        return view('backend.pages.reports.payroll', ["type" => "full-view"]);
    })->name('reports.payroll');

    Route::get('/reports/payroll/payroll-summary', function (\Illuminate\Http\Request $request) {
        $allowedPerPage = [10, 15, 20, 25, 30];
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }
        $sortCol = (string) $request->get('sort_col', 'period');
        $sortDir = strtolower((string) $request->get('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSortCols = ['period', 'project_name', 'payroll_status', 'net_pay'];
        if (!in_array($sortCol, $allowedSortCols, true)) {
            $sortCol = 'period';
        }

        $summaries = \Illuminate\Support\Facades\DB::table('payroll_summaries as s')
            ->leftJoin('payroll_calendars as c', 'c.id', '=', 's.sequence_title')
            ->whereNull('s.deleted_at')
            ->whereExists(function ($query) {
                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('payroll_summary_details as d')
                    ->where(function ($q) {
                        $q->whereColumn('d.summary_id', 's.id')
                          ->orWhereColumn('d.sequence_no', 's.sequence_no');
                    })
                    ->whereNull('d.deleted_at');
            });

        if ($request->filled('payroll_status')) {
            $payrollStatus = strtolower((string) $request->payroll_status);
            if ($payrollStatus === 'draft') {
                $summaries->whereRaw('COALESCE(s.workflow_status, 0) = 0');
            } elseif ($payrollStatus === 'approved') {
                $summaries->whereRaw('COALESCE(s.workflow_status, 0) IN (2, 3)');
            }
        }

        if ($request->filled('period_from')) {
            $summaries->whereDate('s.period_start', '>=', $request->period_from);
        }

        if ($request->filled('period_to')) {
            $summaries->whereDate('s.payroll_period', '<=', $request->period_to);
        }

        $netTotalExpr = "(SELECT COALESCE(SUM(COALESCE(d2.net_pay, 0)), 0)
            FROM payroll_summary_details d2
            WHERE d2.deleted_at IS NULL
              AND (d2.summary_id = s.id OR d2.sequence_no = s.sequence_no))";

        $summaries = $summaries
            ->select('s.id', 's.period_start', 's.payroll_period', 's.pay_date', 's.sequence_no', 's.workflow_status', 'c.title as project_name')
            ->selectRaw("{$netTotalExpr} as total_net_pay_sql");

        switch ($sortCol) {
            case 'project_name':
                $summaries->orderByRaw("COALESCE(c.title, '') {$sortDir}")
                    ->orderBy('s.id', $sortDir);
                break;
            case 'payroll_status':
                $summaries->orderByRaw("CASE COALESCE(s.workflow_status, 0)
                        WHEN 0 THEN 'DRAFT'
                        WHEN 1 THEN 'FOR APPROVAL'
                        WHEN 2 THEN 'APPROVED'
                        WHEN 3 THEN 'SUBMITTED FOR PAYMENT'
                        ELSE 'UNKNOWN'
                    END {$sortDir}")
                    ->orderBy('s.id', $sortDir);
                break;
            case 'net_pay':
                $summaries->orderByRaw("{$netTotalExpr} {$sortDir}")
                    ->orderBy('s.id', $sortDir);
                break;
            case 'period':
            default:
                $summaries->orderByRaw("STR_TO_DATE(s.period_start, '%Y-%m-%d') {$sortDir}")
                    ->orderByRaw("STR_TO_DATE(s.payroll_period, '%Y-%m-%d') {$sortDir}")
                    ->orderBy('s.id', $sortDir);
                break;
        }

        $summaries = $summaries->paginate($perPage)->appends($request->query());

        $summaryIds = $summaries->getCollection()
            ->pluck('id')
            ->filter()
            ->map(function ($id) {
                return (int) $id;
            })
            ->values()
            ->all();

        $sequenceNos = $summaries->getCollection()
            ->pluck('sequence_no')
            ->filter(function ($value) {
                return trim((string) $value) !== '';
            })
            ->values()
            ->all();

        $netBySummaryId = collect();
        if (!empty($summaryIds)) {
            $netBySummaryId = \Illuminate\Support\Facades\DB::table('payroll_summary_details')
                ->selectRaw('summary_id, SUM(COALESCE(net_pay, 0)) as total_net_pay')
                ->whereNull('deleted_at')
                ->whereIn('summary_id', $summaryIds)
                ->groupBy('summary_id')
                ->pluck('total_net_pay', 'summary_id');
        }

        $netBySequence = collect();
        if (!empty($sequenceNos)) {
            $netBySequence = \Illuminate\Support\Facades\DB::table('payroll_summary_details')
                ->selectRaw('sequence_no, SUM(COALESCE(net_pay, 0)) as total_net_pay')
                ->whereNull('deleted_at')
                ->whereIn('sequence_no', $sequenceNos)
                ->groupBy('sequence_no')
                ->pluck('total_net_pay', 'sequence_no');
        }

        $summaries->getCollection()->transform(function ($item) use ($netBySummaryId, $netBySequence) {
            $item->total_net_pay = (float) ($item->total_net_pay_sql ?? 0);
            if ($item->total_net_pay <= 0 && isset($netBySummaryId[$item->id])) {
                $item->total_net_pay = (float) $netBySummaryId[$item->id];
            } elseif ($item->total_net_pay <= 0 && !empty($item->sequence_no) && isset($netBySequence[$item->sequence_no])) {
                $item->total_net_pay = (float) $netBySequence[$item->sequence_no];
            }

            $workflowStatus = (int) ($item->workflow_status ?? 0);
            $item->payroll_status_label = 'DRAFT';
            if ($workflowStatus === 1) {
                $item->payroll_status_label = 'FOR APPROVAL';
            } elseif ($workflowStatus === 2) {
                $item->payroll_status_label = 'APPROVED';
            } elseif ($workflowStatus === 3) {
                $item->payroll_status_label = 'SUBMITTED FOR PAYMENT';
            }

            return $item;
        });

        return view('backend.pages.reports.payroll_summary_report', compact('summaries'));
    })->name('reports.payroll.payroll_summary');

    Route::get('/reports/payroll/payroll-summary/{summaryId}', function ($summaryId) {
        $summary = \Illuminate\Support\Facades\DB::table('payroll_summaries as s')
            ->leftJoin('users as submitted_user', 'submitted_user.id', '=', 's.submitted_by')
            ->leftJoin('users as created_user', 'created_user.id', '=', 's.created_by')
            ->leftJoin('users as approved_user', 'approved_user.id', '=', 's.approved_by')
            ->where('s.id', (int) $summaryId)
            ->whereNull('s.deleted_at')
            ->select(
                's.id',
                's.period_start',
                's.payroll_period',
                's.pay_date',
                's.sequence_no',
                \Illuminate\Support\Facades\DB::raw("TRIM(CONCAT_WS(' ', submitted_user.firstname, submitted_user.middlename, submitted_user.lastname, submitted_user.suffix)) as submitted_by_name"),
                \Illuminate\Support\Facades\DB::raw("TRIM(CONCAT_WS(' ', created_user.firstname, created_user.middlename, created_user.lastname, created_user.suffix)) as created_by_name"),
                \Illuminate\Support\Facades\DB::raw("TRIM(CONCAT_WS(' ', approved_user.firstname, approved_user.middlename, approved_user.lastname, approved_user.suffix)) as approved_by_name")
            )
            ->first();

        if (!$summary) {
            abort(404);
        }

        $cashAdvanceTotals = \Illuminate\Support\Facades\DB::table('cash_advance')
            ->select('summary_id', 'employee_id', \Illuminate\Support\Facades\DB::raw('SUM(amount + 0) as ca_total'))
            ->groupBy('summary_id', 'employee_id');

        $rows = \Illuminate\Support\Facades\DB::table('payroll_summary_details as d')
            ->join('payroll_summaries as s', 's.id', '=', 'd.summary_id')
            ->join('employees as e', 'e.id', '=', 'd.employee_id')
            ->leftJoin('payroll_calendars as c', 'c.id', '=', 's.sequence_title')
            ->leftJoinSub($cashAdvanceTotals, 'ca', function ($join) {
                $join->on('ca.summary_id', '=', 'd.summary_id')
                    ->on('ca.employee_id', '=', 'd.employee_id');
            })
            ->whereNull('d.deleted_at')
            ->where(function ($query) use ($summaryId, $summary) {
                $query->where('d.summary_id', (int) $summaryId);

                if (!empty($summary->sequence_no)) {
                    $query->orWhere('d.sequence_no', $summary->sequence_no);
                }
            })
            ->select(
                'd.id',
                'e.firstname',
                'e.middlename',
                'e.lastname',
                'e.suffix',
                's.schedule_type',
                's.workflow_status',
                's.status',
                'c.title as project_name',
                'd.gross_earnings',
                'd.sss',
                'd.pagibig',
                'd.philhealth',
                'd.tax',
                'd.net_pay',
                \Illuminate\Support\Facades\DB::raw('COALESCE(ca.ca_total, 0) as ca_total')
            )
            ->orderBy('d.id', 'desc')
            ->get()
            ->map(function ($row) {
                $grossDeduction = floatval($row->sss) + floatval($row->pagibig) + floatval($row->philhealth) + floatval($row->tax) + floatval($row->ca_total);
                $grossEarnings = floatval($row->gross_earnings);
                $netPay = floatval($row->net_pay);

                if ($grossEarnings <= 0 && $netPay > 0) {
                    $grossEarnings = $netPay + $grossDeduction;
                }
                if ($netPay <= 0 && $grossEarnings > 0) {
                    $netPay = $grossEarnings - $grossDeduction;
                }

                $row->payroll_status = in_array((int) ($row->status ?? 0), [1, 2], true)
                    ? 'PAYROLL COMPLETED'
                    : 'SUBMITTED FOR PAYMENT';

                $row->gross_deduction = $grossDeduction;
                $row->gross_earnings = $grossEarnings;
                $row->net_pay = $netPay;

                return $row;
            });

        return view('backend.pages.reports.payroll_summary_report_details', compact('summary', 'rows'));
    })->name('reports.payroll.payroll_summary_details');

    Route::get('/reports/payroll/sss-contribution', function (\Illuminate\Http\Request $request) {
        $allowedPerPage = [10, 15, 20, 25, 30];
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }
        $employeeOptions = \App\EmployeeInformation::select('id', 'firstname', 'middlename', 'lastname', 'suffix')
            ->orderBy('lastname', 'asc')
            ->orderBy('firstname', 'asc')
            ->orderBy('middlename', 'asc')
            ->orderBy('suffix', 'asc')
            ->get();

        $rows = \Illuminate\Support\Facades\DB::table('payroll_summary_details as d')
            ->join('payroll_summaries as s', 's.id', '=', 'd.summary_id')
            ->join('employees as e', 'e.id', '=', 'd.employee_id')
            ->whereNull('d.deleted_at')
            ->whereNull('s.deleted_at')
            ->where(function ($query) {
                $query->where('s.workflow_status', 3)
                    ->orWhereIn('s.status', [1, 2]);
            });

        if ($request->filled('employee_id')) {
            $rows->where('d.employee_id', (int) $request->employee_id);
        }

        $rows = $rows
            ->select(
                'd.employee_id',
                'e.firstname',
                'e.middlename',
                'e.lastname',
                'e.suffix',
                \Illuminate\Support\Facades\DB::raw('SUM(COALESCE(d.sss, 0) * 3) as contribution_amount')
            )
            ->groupBy('d.employee_id', 'e.firstname', 'e.middlename', 'e.lastname', 'e.suffix')
            ->orderBy('e.lastname', 'asc')
            ->orderBy('e.firstname', 'asc')
            ->paginate($perPage)
            ->appends($request->query());

        return view('backend.pages.reports.sss_contribution_report', compact('rows', 'employeeOptions'), ["type" => "full-view"]);
    })->name('reports.payroll.sss_contribution');

    Route::get('/reports/payroll/sss-contribution/breakdown/{employeeId}', function ($employeeId) {
        $employeeId = (int) $employeeId;
        if ($employeeId <= 0) {
            return response()->json([
                'rows' => [],
                'totals' => ['ee' => 0, 'er' => 0, 'total' => 0],
            ]);
        }

        $rows = \Illuminate\Support\Facades\DB::table('payroll_summary_details as d')
            ->join('payroll_summaries as s', function ($join) {
                $join->on('s.id', '=', 'd.summary_id')
                    ->orOn('s.sequence_no', '=', 'd.sequence_no');
            })
            ->where('d.employee_id', $employeeId)
            ->whereNull('d.deleted_at')
            ->whereNull('s.deleted_at')
            ->where(function ($query) {
                $query->where('s.workflow_status', 3)
                    ->orWhereIn('s.status', [1, 2]);
            })
            ->select(
                'd.id',
                's.period_start',
                's.payroll_period',
                \Illuminate\Support\Facades\DB::raw('COALESCE(d.sss, 0) as ee_share')
            )
            ->orderBy('s.period_start', 'desc')
            ->orderBy('s.payroll_period', 'desc')
            ->get()
            ->map(function ($row) {
                $ee = (float) ($row->ee_share ?? 0);
                $er = $ee * 2;
                $total = $ee + $er;
                return [
                    'id' => $row->id,
                    'period_start' => $row->period_start,
                    'payroll_period' => $row->payroll_period,
                    'ee_share' => $ee,
                    'er_share' => $er,
                    'total_share' => $total,
                ];
            })
            ->values();

        return response()->json([
            'rows' => $rows,
            'totals' => [
                'ee' => (float) $rows->sum('ee_share'),
                'er' => (float) $rows->sum('er_share'),
                'total' => (float) $rows->sum('total_share'),
            ],
        ]);
    })->name('reports.payroll.sss_contribution_breakdown');

    Route::get('/reports/payroll/philhealth-contribution', function (\Illuminate\Http\Request $request) {
        $allowedPerPage = [10, 15, 20, 25, 30];
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }

        $employeeOptions = \App\EmployeeInformation::select('id', 'firstname', 'middlename', 'lastname', 'suffix')
            ->orderBy('lastname', 'asc')
            ->orderBy('firstname', 'asc')
            ->orderBy('middlename', 'asc')
            ->orderBy('suffix', 'asc')
            ->get();

        $rows = \Illuminate\Support\Facades\DB::table('payroll_summary_details as d')
            ->join('payroll_summaries as s', function ($join) {
                $join->on('s.id', '=', 'd.summary_id')
                    ->orOn('s.sequence_no', '=', 'd.sequence_no');
            })
            ->join('employees as e', 'e.id', '=', 'd.employee_id')
            ->whereNull('d.deleted_at')
            ->whereNull('s.deleted_at')
            ->where(function ($query) {
                $query->where('s.workflow_status', 3)
                    ->orWhereIn('s.status', [1, 2]);
            });

        if ($request->filled('employee_id')) {
            $rows->where('d.employee_id', (int) $request->employee_id);
        }

        $rows = $rows
            ->select(
                'd.employee_id',
                'e.firstname',
                'e.middlename',
                'e.lastname',
                'e.suffix',
                \Illuminate\Support\Facades\DB::raw('SUM(COALESCE(d.philhealth, 0) * 2) as contribution_amount')
            )
            ->groupBy('d.employee_id', 'e.firstname', 'e.middlename', 'e.lastname', 'e.suffix')
            ->orderBy('e.lastname', 'asc')
            ->orderBy('e.firstname', 'asc')
            ->paginate($perPage)
            ->appends($request->query());

        return view('backend.pages.reports.philhealth_contribution_report', compact('rows', 'employeeOptions'), ["type" => "full-view"]);
    })->name('reports.payroll.philhealth_contribution');

    Route::get('/reports/payroll/philhealth-contribution/breakdown/{employeeId}', function ($employeeId) {
        $employeeId = (int) $employeeId;
        if ($employeeId <= 0) {
            return response()->json([
                'rows' => [],
                'totals' => ['ee' => 0, 'er' => 0, 'total' => 0],
            ]);
        }

        $rows = \Illuminate\Support\Facades\DB::table('payroll_summary_details as d')
            ->join('payroll_summaries as s', function ($join) {
                $join->on('s.id', '=', 'd.summary_id')
                    ->orOn('s.sequence_no', '=', 'd.sequence_no');
            })
            ->where('d.employee_id', $employeeId)
            ->whereNull('d.deleted_at')
            ->whereNull('s.deleted_at')
            ->where(function ($query) {
                $query->where('s.workflow_status', 3)
                    ->orWhereIn('s.status', [1, 2]);
            })
            ->select(
                'd.id',
                's.period_start',
                's.payroll_period',
                \Illuminate\Support\Facades\DB::raw('COALESCE(d.philhealth, 0) as ee_share')
            )
            ->orderBy('s.period_start', 'desc')
            ->orderBy('s.payroll_period', 'desc')
            ->get()
            ->map(function ($row) {
                $ee = (float) ($row->ee_share ?? 0);
                $er = $ee;
                $total = $ee + $er;
                return [
                    'id' => $row->id,
                    'period_start' => $row->period_start,
                    'payroll_period' => $row->payroll_period,
                    'ee_share' => $ee,
                    'er_share' => $er,
                    'total_share' => $total,
                ];
            })
            ->values();

        return response()->json([
            'rows' => $rows,
            'totals' => [
                'ee' => (float) $rows->sum('ee_share'),
                'er' => (float) $rows->sum('er_share'),
                'total' => (float) $rows->sum('total_share'),
            ],
        ]);
    })->name('reports.payroll.philhealth_contribution_breakdown');

    Route::get('/reports/payroll/pagibig-contribution', function (\Illuminate\Http\Request $request) {
        $allowedPerPage = [10, 15, 20, 25, 30];
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }

        $employeeOptions = \App\EmployeeInformation::select('id', 'firstname', 'middlename', 'lastname', 'suffix')
            ->orderBy('lastname', 'asc')
            ->orderBy('firstname', 'asc')
            ->orderBy('middlename', 'asc')
            ->orderBy('suffix', 'asc')
            ->get();

        $rows = \Illuminate\Support\Facades\DB::table('payroll_summary_details as d')
            ->join('payroll_summaries as s', function ($join) {
                $join->on('s.id', '=', 'd.summary_id')
                    ->orOn('s.sequence_no', '=', 'd.sequence_no');
            })
            ->join('employees as e', 'e.id', '=', 'd.employee_id')
            ->whereNull('d.deleted_at')
            ->whereNull('s.deleted_at')
            ->where(function ($query) {
                $query->where('s.workflow_status', 3)
                    ->orWhereIn('s.status', [1, 2]);
            });

        if ($request->filled('employee_id')) {
            $rows->where('d.employee_id', (int) $request->employee_id);
        }

        $rows = $rows
            ->select(
                'd.employee_id',
                'e.firstname',
                'e.middlename',
                'e.lastname',
                'e.suffix',
                \Illuminate\Support\Facades\DB::raw('SUM(COALESCE(d.pagibig, 0) * 2) as contribution_amount')
            )
            ->groupBy('d.employee_id', 'e.firstname', 'e.middlename', 'e.lastname', 'e.suffix')
            ->orderBy('e.lastname', 'asc')
            ->orderBy('e.firstname', 'asc')
            ->paginate($perPage)
            ->appends($request->query());

        return view('backend.pages.reports.pagibig_contribution_report', compact('rows', 'employeeOptions'), ["type" => "full-view"]);
    })->name('reports.payroll.pagibig_contribution');

    Route::get('/reports/payroll/pagibig-contribution/breakdown/{employeeId}', function ($employeeId) {
        $employeeId = (int) $employeeId;
        if ($employeeId <= 0) {
            return response()->json([
                'rows' => [],
                'totals' => ['ee' => 0, 'er' => 0, 'total' => 0],
            ]);
        }

        $rows = \Illuminate\Support\Facades\DB::table('payroll_summary_details as d')
            ->join('payroll_summaries as s', function ($join) {
                $join->on('s.id', '=', 'd.summary_id')
                    ->orOn('s.sequence_no', '=', 'd.sequence_no');
            })
            ->where('d.employee_id', $employeeId)
            ->whereNull('d.deleted_at')
            ->whereNull('s.deleted_at')
            ->where(function ($query) {
                $query->where('s.workflow_status', 3)
                    ->orWhereIn('s.status', [1, 2]);
            })
            ->select(
                'd.id',
                's.period_start',
                's.payroll_period',
                \Illuminate\Support\Facades\DB::raw('COALESCE(d.pagibig, 0) as ee_share')
            )
            ->orderBy('s.period_start', 'desc')
            ->orderBy('s.payroll_period', 'desc')
            ->get()
            ->map(function ($row) {
                $ee = (float) ($row->ee_share ?? 0);
                $er = $ee;
                $total = $ee + $er;
                return [
                    'id' => $row->id,
                    'period_start' => $row->period_start,
                    'payroll_period' => $row->payroll_period,
                    'ee_share' => $ee,
                    'er_share' => $er,
                    'total_share' => $total,
                ];
            })
            ->values();

        return response()->json([
            'rows' => $rows,
            'totals' => [
                'ee' => (float) $rows->sum('ee_share'),
                'er' => (float) $rows->sum('er_share'),
                'total' => (float) $rows->sum('total_share'),
            ],
        ]);
    })->name('reports.payroll.pagibig_contribution_breakdown');

    Route::get('/reports/accounting', function () {
        return view('backend.pages.reports.accounting', ["type" => "full-view"]);
    })->name('reports.accounting');

    Route::get('/reports/hr', function () {
        return view('backend.pages.reports.hr', ["type" => "full-view"]);
    })->name('reports.hr');

    Route::get('/reports/hr/employee-masterfile', function (\Illuminate\Http\Request $request) {
        $employeesQuery = \App\EmployeeInformation::with(['employments_tab.departments', 'employments_tab.positions'])
            ->orderBy('employee_no', 'asc');

        if ($request->filled('employee_ids')) {
            $employeeIds = collect((array) $request->employee_ids)
                ->map(function ($id) {
                    return (int) $id;
                })
                ->filter(function ($id) {
                    return $id > 0;
                })
                ->values()
                ->all();

            if (!empty($employeeIds)) {
                $employeesQuery->whereIn('id', $employeeIds);
            }
        }

        if ($request->filled('department_id')) {
            $departmentId = (int) $request->department_id;
            $employeesQuery->whereHas('employments_tab', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if ($request->filled('status')) {
            $status = strtolower((string) $request->status) === 'active' ? 1 : 0;
            $employeesQuery->where('status', $status);
        }

        if ($request->filled('employment_date_from') || $request->filled('employment_date_to')) {
            $from = $request->employment_date_from ?: null;
            $to = $request->employment_date_to ?: null;
            $employeesQuery->whereHas('employments_tab', function ($q) use ($from, $to) {
                if ($from) {
                    $q->whereDate('employment_date', '>=', $from);
                }
                if ($to) {
                    $q->whereDate('employment_date', '<=', $to);
                }
            });
        }

        $employees = $employeesQuery->get();
        $departments = \App\Departments::orderBy('description', 'asc')->get();
        $employeeOptions = \App\EmployeeInformation::select('id', 'employee_no', 'firstname', 'middlename', 'lastname', 'suffix')
            ->orderBy('lastname', 'asc')
            ->orderBy('firstname', 'asc')
            ->orderBy('middlename', 'asc')
            ->orderBy('suffix', 'asc')
            ->get();

        return view('backend.pages.reports.hr_employee_masterfile', compact('employees', 'departments', 'employeeOptions'));
    })->name('reports.hr.employee_masterfile');

    Route::get('/reports/hr/employee-compensation-details', function (\Illuminate\Http\Request $request) {
        $salaryType = strtolower((string) $request->get('salary_type', 'monthly'));
        $salaryColumnMap = [
            'annual' => 'annual_salary',
            'monthly' => 'monthly_salary',
            'semi_monthly' => 'semi_monthly_salary',
            'weekly' => 'weekly_salary',
        ];
        $salaryColumn = $salaryColumnMap[$salaryType] ?? 'monthly_salary';

        $employeesQuery = \App\EmployeeInformation::with(['employments_tab.positions', 'compensations'])
            ->orderBy('lastname', 'asc')
            ->orderBy('firstname', 'asc');

        if ($request->filled('employee_ids')) {
            $employeeIds = collect((array) $request->employee_ids)
                ->map(function ($id) {
                    return (string) $id;
                })
                ->filter(function ($id) {
                    return $id !== '' && $id !== '__all__';
                })
                ->map(function ($id) {
                    return (int) $id;
                })
                ->filter(function ($id) {
                    return $id > 0;
                })
                ->values()
                ->all();

            if (!empty($employeeIds)) {
                $employeesQuery->whereIn('id', $employeeIds);
            }
        }

        if ($request->filled('status')) {
            $status = strtolower((string) $request->status) === 'active' ? 1 : 0;
            $employeesQuery->where('status', $status);
        }

        $employeesQuery->whereHas('compensations', function ($q) use ($salaryColumn) {
            $q->whereNotNull($salaryColumn);
        });

        $employees = $employeesQuery->get();

        $allowanceByEmployee = \App\AllowanceTagging::with('allowances')
            ->get()
            ->groupBy('employee_id');

        $projectsByEmployee = \App\ProjectTagging::with('project')
            ->get()
            ->groupBy('employee_id');

        $employeeOptions = \App\EmployeeInformation::select('id', 'firstname', 'middlename', 'lastname', 'suffix')
            ->orderBy('lastname', 'asc')
            ->orderBy('firstname', 'asc')
            ->orderBy('middlename', 'asc')
            ->orderBy('suffix', 'asc')
            ->get();

        return view('backend.pages.reports.hr_employee_compensation_details', compact(
            'employees',
            'allowanceByEmployee',
            'projectsByEmployee',
            'employeeOptions',
            'salaryType'
        ));
    })->name('reports.hr.employee_compensation_details');

    Route::get('/reports/hr/leave-balance', function (\Illuminate\Http\Request $request) {
        $leaveQuery = \App\Leaves::with(['leave_types', 'employee'])
            ->orderBy('employee_id', 'asc')
            ->orderBy('leave_type', 'asc');

        if ($request->filled('status')) {
            $status = strtolower((string) $request->status) === 'active' ? 1 : 0;
            $leaveQuery->whereHas('employee', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        $leaveRows = $leaveQuery->get()->map(function ($row) {
            $usedDays = (float) \App\LeaveRequest::where('employee_id', $row->employee_id)
                ->where('leave_type_id', $row->leave_type)
                ->where('status', 1)
                ->sum('total_leave_hours');

            $balance = (float) ($row->total_hours ?? 0);
            $entitlement = $balance + $usedDays;

            $row->used_days = $usedDays;
            $row->entitlement_days = $entitlement;
            $row->balance_days = $balance;
            return $row;
        });

        return view('backend.pages.reports.hr_leave_balance', compact('leaveRows'));
    })->name('reports.hr.leave_balance');

    Route::get('/reports/hr/employee-attendance', function (\Illuminate\Http\Request $request) {
        $attendanceQuery = \App\TimeLogs::with('employee')
            ->whereNotNull('employee_id');

        if ($request->filled('date_from')) {
            $attendanceQuery->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $attendanceQuery->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $status = strtolower((string) $request->status) === 'active' ? 1 : 0;
            $attendanceQuery->whereHas('employee', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        $attendanceRows = $attendanceQuery->get()
            ->groupBy('employee_id')
            ->map(function ($logs) {
                $first = $logs->first();
                $employee = $first ? $first->employee : null;
                $sortedLogs = $logs->sortBy('date')->values();

                $presentLogs = $sortedLogs->filter(function ($item) {
                    return !is_null($item->time_in);
                })->values();
                $daysPresent = $presentLogs->count();

                $absentLogs = $sortedLogs->filter(function ($item) {
                    return is_null($item->time_in);
                })->values();

                $lateLogs = $sortedLogs->filter(function ($item) {
                    return floatval($item->late_hours ?? 0) > 0;
                })->values();

                $undertimeLogs = $sortedLogs->filter(function ($item) {
                    return floatval($item->undertime ?? 0) > 0;
                })->values();

                $totalLoggedDays = $sortedLogs->count();
                $daysAbsent = max(0, $totalLoggedDays - $daysPresent);

                $formatItem = function ($item) {
                    return [
                        'date' => $item->date,
                        'time_in' => $item->time_in,
                        'time_out' => $item->time_out,
                        'total_hours' => (float) ($item->total_hours ?? 0),
                        'late_hours' => (float) ($item->late_hours ?? 0),
                        'undertime' => (float) ($item->undertime ?? 0),
                    ];
                };

                return (object) [
                    'employee_no' => $employee->employee_no ?? '-',
                    'employee_name' => trim(($employee->firstname ?? '') . ' ' . ($employee->middlename ?? '') . ' ' . ($employee->lastname ?? '') . ' ' . ($employee->suffix ?? '')),
                    'days_present' => $daysPresent,
                    'days_absent' => $daysAbsent,
                    'total_hours' => (float) $sortedLogs->sum('total_hours'),
                    'late_hours' => (float) $sortedLogs->sum('late_hours'),
                    'undertime_hours' => (float) $sortedLogs->sum('undertime'),
                    'present_breakdown' => $presentLogs->map($formatItem)->values()->all(),
                    'absent_breakdown' => $absentLogs->map($formatItem)->values()->all(),
                    'late_breakdown' => $lateLogs->map($formatItem)->values()->all(),
                    'undertime_breakdown' => $undertimeLogs->map($formatItem)->values()->all(),
                ];
            })
            ->sortBy('employee_name')
            ->values();

        return view('backend.pages.reports.hr_employee_attendance', compact('attendanceRows'));
    })->name('reports.hr.employee_attendance');

    Route::group(['prefix' => '/masterlist'], function() {
        Route::group(['prefix' => '/employee'], function (){
            Route::get              ('/',                        'EmployeeInformationController@masterlist'                     )->name('employee_masterlist');
            Route::get              ('/get',                     'EmployeeInformationController@getmasterlist'                  )->name('get_data');
            Route::post              ('/{id}',           'EmployeeInformationController@employee'                  )->name('get_data');
        });
    });

    Route::group(['prefix' => '/api'], function (){
        Route::group(['prefix' => '/leave-type'], function (){
            Route::post         ('/getData',                     'LeaveTypeController@getData'                                  )->name('get_data_leave_type');
        });
    });

    Route::group(['prefix' => '/purchasing'], function (){
        Route::group(['prefix' => '/purchase_orders'], function (){
            Route::get          ('/',                            'PurchaseOrderController@index'                                      )->name('classes');
            Route::get          ('/get/{status}/{project?}/{sd?}/{ed?}/{po?}',                'PurchaseOrderController@get'                                        )->name('get_classes');
            Route::post         ('/save',                        'PurchaseOrderController@store'                                      )->name('save_classes');
            Route::get          ('/edit/{id}',                   'PurchaseOrderController@edit'                                       )->name('edit_classes');
            Route::post         ('/update/{id}',                 'PurchaseOrderController@update'                                     )->name('update_classes');
            Route::post         ('/print/{id}',                  'PurchaseOrderController@print'                                      )->name('update_classes');
            Route::post         ('/destroy',                     'PurchaseOrderController@destroy'                                    )->name('destroy_classes');
            Route::post         ('/set-status/{id}',             'PurchaseOrderController@changeStatus'                               )->name('status');
            Route::get('/materials/{id}/units', 'MaterialsController@getUnits');

        });

        Route::group(['prefix' => '/delivery_receipt'], function (){
            Route::get          ('/',                            'DeliveryReceiptController@index'                                      )->name('classes');
            Route::get          ('/get/{status}',                'DeliveryReceiptController@get'                                        )->name('get_classes');
            Route::post         ('/save',                        'DeliveryReceiptController@store'                                      )->name('save_classes');
            Route::get          ('/edit/{id}',                   'DeliveryReceiptController@edit'                                       )->name('edit_classes');
            
            Route::post         ('/update/{id}',                 'DeliveryReceiptController@update'                                     )->name('update_classes');
            Route::post         ('/print/{id}',                  'DeliveryReceiptController@print'                                      )->name('update_classes');
            Route::post         ('/destroy',                     'DeliveryReceiptController@destroy'                                    )->name('destroy_classes');
            Route::post         ('/set-status/{id}',             'DeliveryReceiptController@changeStatus'                               )->name('status');
            Route::get('/details/{id}', 'DeliveryReceiptController@showDetails');
           
            Route::get('/item-details/{id}', 'DeliveryReceiptController@viewItemDetailByPodId');



            Route::post('/{id}/send-quantity', 'DeliveryReceiptController@updateSentQuantity');

        });

        Route::group(['prefix' => '/purchase_order_details'], function (){
            Route::get          ('/',                            'PurchaseOrderDetailController@index'                                      )->name('classes');
            Route::get          ('/get/{id}',                    'PurchaseOrderDetailController@get'                                        )->name('get_classes');
            Route::post         ('/save',                        'PurchaseOrderDetailController@store'                                      )->name('save_classes');
            Route::get          ('/edit/{id}',                   'PurchaseOrderDetailController@edit'                                       )->name('edit_classes');
            Route::post         ('/update/{id}',                 'PurchaseOrderDetailController@update'                                     )->name('update_classes');
            Route::post         ('/destroy',                     'PurchaseOrderDetailController@destroy'                                    )->name('destroy_classes');
        });

        Route::group(['prefix' => '/project'], function (){
            Route::get          ('/',                            'ProjectController@index'                                     )->name('project');
            Route::get          ('/get',                         'ProjectController@get'                                       )->name('get');
            Route::post         ('/save',                        'ProjectController@store'                                     )->name('save');
            Route::get          ('/edit/{id}',                   'ProjectController@edit'                                      )->name('edit');
            Route::post         ('/update/{id}',                 'ProjectController@update'                                    )->name('update');
            Route::post         ('/destroy',                     'ProjectController@destroy'                                   )->name('destroy');
            Route::get          ('/get-record',                  'ProjectController@getRecord'                                 )->name('get');
            Route::post         ('/tag',                         'ProjectController@addProjectTag'                         )->name('save');
            Route::post         ('/get-tag',                     'ProjectController@getProject'                            )->name('get');
            Route::post         ('/get-employee-tag',            'ProjectController@getEmployeeTag'                            )->name('get');
        });

        Route::group(['prefix' => '/discount'], function (){
            Route::post         ('/save',                        'DiscountController@store'                                     )->name('save');
            Route::post         ('/update/{id}',                 'DiscountController@update'                                    )->name('update');
            Route::post         ('/destroy',                     'DiscountController@destroy'                                    )->name('destroy');
        });

        Route::group(['prefix' => '/credit_note'], function (){
            Route::get          ('/',                            'CreditNoteController@index'                                     )->name('page');
            Route::post         ('/save',                        'CreditNoteController@store'                                     )->name('save');
            Route::get          ('/get',                         'CreditNoteController@get'                                       )->name('get');
            // Route::post         ('/update/{id}',                 'DiscountController@update'                                    )->name('update');
            // Route::post         ('/destroy',                     'DiscountController@destroy'                                    )->name('destroy');
            Route::get          ('/get/{id}',                         'CreditNoteController@getCreditNote'                             )->name('get');

        });

        Route::group(['prefix' => '/split_po'], function (){
            Route::post         ('/save',                        'PurchaseOrderDetailController@split'                                      )->name('save');
            Route::get          ('/get-split/{id}',                    'PurchaseOrderDetailController@getSplit'                                   )->name('get');
        });

        Route::group(['prefix' => '/project_split'], function (){
            Route::post         ('/save',                        'ProjectController@split'                                      )->name('save');
            Route::get          ('/get-split/{id}',                    'ProjectController@getSplit'                                   )->name('get');
        });

        Route::group(['prefix' => '/sites'], function (){
            Route::get          ('/',                            'SiteController@index'                                      )->name('classes');
            Route::get          ('/get',                         'SiteController@get'                                        )->name('get_classes');
            Route::get          ('/getEmployee/{id}',            'SiteController@getEmployee'                                )->name('get_classes');
            Route::post         ('/save',                        'SiteController@store'                                      )->name('save_classes');
            Route::get          ('/edit/{id}',                   'SiteController@edit'                                       )->name('edit_classes');
            Route::post         ('/update/{id}',                 'SiteController@update'                                     )->name('update_classes');
            Route::post         ('/destroy',                     'SiteController@destroy'                                    )->name('destroy_classes');
            Route::get          ('/get-record',                  'SiteController@getRecord'                                  )->name('destroy_classes');
        });

        Route::group(['prefix' => '/supplier'], function (){
            Route::get          ('/',                            'SupplierController@index'                                      )->name('classes');
            Route::get          ('/get',                         'SupplierController@get'                                        )->name('get_classes');
            Route::post         ('/save',                        'SupplierController@store'                                      )->name('save_classes');
            Route::get          ('/edit/{id}',                   'SupplierController@edit'                                       )->name('edit_classes');
            Route::post         ('/update/{id}',                 'SupplierController@update'                                     )->name('update_classes');
            Route::post         ('/destroy',                     'SupplierController@destroy'                                    )->name('destroy_classes');
        });

        Route::group(['prefix' => '/material_category'], function (){
            Route::get          ('/',                            'MaterialCategoryController@index'                                      )->name('classes');
            Route::get          ('/get',                         'MaterialCategoryController@get'                                        )->name('get_classes');
            Route::post         ('/save',                        'MaterialCategoryController@store'                                      )->name('save_classes');
            Route::get          ('/edit/{id}',                   'MaterialCategoryController@edit'                                       )->name('edit_classes');
            Route::post         ('/update/{id}',                 'MaterialCategoryController@update'                                     )->name('update_classes');
            Route::post         ('/destroy',                     'MaterialCategoryController@destroy'                                    )->name('destroy_classes');
        });

        Route::group(['prefix' => '/materials'], function (){
            Route::get          ('/',                            'MaterialsController@index'                                      )->name('classes');
            Route::get          ('/get',                         'MaterialsController@get'                                        )->name('get_classes');
            Route::post         ('/save',                        'MaterialsController@store'                                      )->name('save_classes');
            Route::get          ('/edit/{id}',                   'MaterialsController@edit'                                       )->name('edit_classes');
            Route::post         ('/update/{id}',                 'MaterialsController@update'                                     )->name('update_classes');
            Route::post         ('/destroy',                     'MaterialsController@destroy'                                    )->name('destroy_classes');
        });
    });

    Route::group(['prefix' => '/accounting'], function (){
        Route::group(['prefix' => '/chart_of_accounts'], function (){
            Route::get          ('/',                            'ChartOfAccountController@index'                                      )->name('classes');
            Route::get          ('/get',                         'ChartOfAccountController@get'                                        )->name('get_classes');
            Route::post         ('/save',                        'ChartOfAccountController@store'                                      )->name('save_classes');
            Route::get          ('/edit/{id}',                   'ChartOfAccountController@edit'                                       )->name('edit_classes');
            Route::post         ('/update/{id}',                 'ChartOfAccountController@update'                                     )->name('update_classes');
            Route::post         ('/destroy',                     'ChartOfAccountController@destroy'                                    )->name('destroy_classes');
        });

        Route::group(['prefix' => '/account_types'], function (){
            Route::get          ('/',                            'AccountTypeController@index'                                      )->name('classes');
            Route::get          ('/get',                         'AccountTypeController@get'                                        )->name('get_classes');
            Route::post         ('/save',                        'AccountTypeController@store'                                      )->name('save_classes');
            Route::get          ('/edit/{id}',                   'AccountTypeController@edit'                                       )->name('edit_classes');
            Route::post         ('/update/{id}',                 'AccountTypeController@update'                                     )->name('update_classes');
            Route::post         ('/destroy',                     'AccountTypeController@destroy'                                    )->name('destroy_classes');
        });

        Route::group(['prefix' => '/journal_entries'], function (){
            Route::get          ('/',                            'JournalEntryController@index'                                      )->name('classes');
            Route::get          ('/get',                         'JournalEntryController@get'                                        )->name('get_classes');
            Route::post         ('/save',                        'JournalEntryController@store'                                      )->name('save_classes');
            Route::get          ('/edit/{id}',                   'JournalEntryController@edit'                                       )->name('edit_classes');
            Route::get          ('/status/{id}',                 'JournalEntryController@status'                                       )->name('edit_classes');
            Route::post         ('/update/{id}',                 'JournalEntryController@update'                                     )->name('update_classes');
            Route::post         ('/destroy',                     'JournalEntryController@destroy'                                    )->name('destroy_classes');
        });

        Route::group(['prefix' => '/journal_entry_line_fields'], function (){
            Route::get          ('/',                            'JournalEntryLineFieldController@index'                                      )->name('classes');
            Route::get          ('/get_details/{id}',            'JournalEntryLineFieldController@get'                                        )->name('get_classes');
            Route::post         ('/save',                        'JournalEntryLineFieldController@store'                                      )->name('save_classes');
            Route::get          ('/edit/{id}',                   'JournalEntryLineFieldController@edit'                                       )->name('edit_classes');
            Route::post         ('/update/{id}',                 'JournalEntryLineFieldController@update'                                     )->name('update_classes');
            Route::post         ('/destroy',                     'JournalEntryLineFieldController@destroy'                                    )->name('destroy_classes');
        });
    });

    Route::group(['prefix' => '/payroll'], function (){
        Route::get          ('/',                                'PayrollController@index'                                      )->name('payroll');

        Route::group(['prefix' => '/global'], function() {
            Route::post         ('/getdatesanddays',             'GlobalController@getDateAndDays'                              )->name('get_date_and_days');
        });
        
        Route::group(['prefix' => '/employee-profile'], function (){
            Route::get          ('/',                            'EmployeeProfileController@index'                              )->name('employee_profile');
            Route::get          ('/get',                         'EmployeeProfileController@get'                                )->name('get_employment_information');
            Route::get          ('/audit-trail/{id}',            'EmployeeProfileController@auditTrail'                         )->name('employee_profile_audit_trail');
            Route::post         ('/save',                        'EmployeeProfileController@save'                               )->name('save_employment_information');
            Route::get          ('/edit/{id}',                   'EmployeeProfileController@edit'                               )->name('edit_employment_information');
            Route::post         ('/update/{id}',                 'EmployeeProfileController@update'                             )->name('update_employment_information');
            Route::post         ('/destroy',                     'EmployeeProfileController@destroy'                            )->name('destroy_employment_information');
            Route::post         ('/convert-record',              'EmployeeProfileController@convert_employment_status'          )->name('convert_employment_status');
        });

        Route::group(['prefix' => '/employee_adjustment'], function (){
            Route::get          ('/',                            'EmployeeAdjustmentController@index'                              )->name('employee_profile');
            Route::get          ('/get',                         'EmployeeAdjustmentController@get'                                )->name('get_employment_information');
            Route::post         ('/save',                        'EmployeeAdjustmentController@store'                               )->name('save_employment_information');
            Route::get          ('/edit/{id}',                   'EmployeeAdjustmentController@edit'                               )->name('edit_employment_information');
            Route::post         ('/update/{id}',                 'EmployeeAdjustmentController@update'                             )->name('update_employment_information');
            Route::post         ('/destroy',                     'EmployeeAdjustmentController@destroy'                            )->name('destroy_employment_information');
        });

        Route::group(['prefix' => '/employee-information'], function (){
            Route::get          ('/',                            'EmployeeInformationController@index'                          )->name('employment_information');
            Route::get          ('/get',                         'EmployeeInformationController@get'                            )->name('get_employment_information');
            Route::get          ('/getPosition/{id}',            'EmployeeInformationController@positionValidate'               )->name('get_classes');
            Route::get          ('/getCV/{id}',                  'EmployeeInformationController@getCV'                          )->name('get_classes');
            Route::post         ('/save',                        'EmployeeInformationController@store'                          )->name('save_employment_information');
            Route::get          ('/edit/{id}',                   'EmployeeInformationController@edit'                           )->name('edit_employment_information');
            Route::post         ('/update/{id}',                 'EmployeeInformationController@update'                         )->name('update_employment_information');
            Route::post         ('/destroy',                     'EmployeeInformationController@destroy'                        )->name('destroy_employment_information');
        });

        Route::group(['prefix' => '/201-file'], function (){
            Route::get          ('/',                            'PersonnelFileController@index'                                )->name('employment_information');
            Route::get          ('/get',                         'PersonnelFileController@get'                                  )->name('get_employment_information');
            Route::post         ('/save',                        'PersonnelFileController@store'                                )->name('save_employment_information');
            Route::get          ('/edit/{id}',                   'PersonnelFileController@edit'                                 )->name('edit_employment_information');
            Route::post         ('/update/{id}',                 'PersonnelFileController@update'                               )->name('update_employment_information');
            Route::post         ('/destroy',                     'PersonnelFileController@destroy'                              )->name('destroy_employment_information');
        });

        Route::group(['prefix' => '/summary'], function() {
            Route::get          ('/',                            'PayrollSummaryController@index'                               )->name('payroll_summary');
            Route::post         ('/save',                        'PayrollSummaryController@save'                                )->name('save_earning_and_deduction');
            Route::get          ('/get',                         'PayrollSummaryController@get'                                 )->name('payroll_summary_get');
            Route::get          ('/get',                         'PayrollSummaryController@get'                                 )->name('payroll_summary_get');
            Route::get          ('/get_history',                 'PayrollSummaryController@get_history'                         )->name('payroll_summary_get');
            Route::post         ('/get_summary',                 'PayrollSummaryController@get_summary'                         )->name('payroll_summary_get');
            Route::get          ('/get_details/{sequence_no}',   'PayrollSummaryController@get_details'                         )->name('payroll_summary_get');
            Route::post         ('/get_earnings_and_deductions', 'PayrollSummaryController@get_earnings_and_deductions'         )->name('payroll_summary_get');
            Route::get          ('/get_earnings',                'PayrollSummaryController@get_earnings'                        )->name('earning_get');
            Route::get          ('/get_deductions',              'PayrollSummaryController@get_deductions'                      )->name('deduction_get');
            Route::post         ('/update_status',               'PayrollSummaryController@update_status'                       )->name('update_status_payroll_summary');
            Route::post         ('/update_details_status',       'PayrollSummaryController@update_details_status'               )->name('update_status_payroll_summary');
            Route::get          ('/email_recipients/{summary_id}','PayrollSummaryController@email_recipients'                   )->name('payroll_summary_email_recipients');
            Route::post         ('/send_selected_payslips',      'PayrollSummaryController@send_selected_payslips'             )->name('payroll_summary_send_selected_payslips');
            Route::post         ('/get_overall',                 'PayrollSummaryController@get_overall'                         )->name('get_overall');
            Route::post         ('/show',                        'PayrollSummaryController@show'                                )->name('show_payroll_sumamry');
            Route::post         ('/manual/save',                 'PayrollSummaryController@summary_save'                        )->name('save');
        });

        Route::group(['prefix' => '/sss'], function() {
            Route::get          ('/',                            'SSSController@index'                                          )->name('sss');
            Route::get          ('/get',                         'SSSController@get'                                            )->name('get_sss');
            Route::post         ('/generate',                    'SSSController@generateSSSTable'                               )->name('generate_sss');
            Route::post         ('/get-val',                     'TimeLogsController@getSSS'                                    )->name('get');
        });

        Route::group(['prefix' => '/earning_setup'], function() {
            Route::post         ('/save',                        'EarningSetupController@save'                               )->name('save');
            Route::post         ('/destroy',                     'EarningSetupController@destroy'                            )->name('destroy');
            Route::get         ('/get/{id}',                     'EarningSetupController@get'                                )->name('destroy');
        });

        Route::group(['prefix' => '/allowance_setup'], function() {
            Route::post         ('/save',                                 'AllowanceSetupController@save'                               )->name('save');
            Route::post         ('/destroy',                              'AllowanceSetupController@destroy'                            )->name('destroy');
            Route::get          ('/get-by-sequence/{id}/{emp_id}',        'AllowanceSetupController@getBySequence'                      )->name('get');
            Route::get          ('/get/{id}',                             'AllowanceSetupController@get'                                )->name('get');
            Route::post         ('/update/{id}',                          'AllowanceSetupController@update'                             )->name('update');
            Route::get          ('/edit/{id}',                            'AllowanceSetupController@edit'                               )->name('edit');
        });
        
        Route::group(['prefix' => '/cash_advance'], function() {
            Route::get          ('/get-ca/{id}/{emp_id}',                 'CashAdvanceController@getCA'                        )->name('get');
            Route::post         ('/save',                                 'CashAdvanceController@save'                         )->name('save');
            Route::post         ('/destroy',                              'CashAdvanceController@destroy'                      )->name('destroy');
            Route::post         ('/update/{id}',                          'CashAdvanceController@update'                       )->name('update');
            Route::get          ('/edit/{id}',                            'CashAdvanceController@edit'                         )->name('edit');
        });

        Route::group(['prefix' => '/deduction_setup'], function() {
            Route::post         ('/save',                        'DeductionSetupController@save'                               )->name('save');
            Route::post         ('/destroy',                     'DeductionSetupController@destroy'                            )->name('destroy');
            Route::get         ('/get/{id}',                     'DeductionSetupController@get'                            )->name('destroy');
        });

        Route::group(['prefix' => '/payslip'], function() {
            Route::get          ('/',                            'PayrollSummaryController@payslip'                             )->name('payslip');

        });

        Route::group(['prefix' => '/payroll_calendar'], function() {
            Route::get          ('/',                            'PayrollCalendarController@index'                              )->name('payroll_calendar');
            Route::get          ('/get',                         'PayrollCalendarController@get'                                )->name('get_payroll_calendar');
            Route::post         ('/save',                        'PayrollCalendarController@save'                               )->name('save_payroll_calendar');
            Route::get          ('/edit/{id}',                   'PayrollCalendarController@edit'                               )->name('edit_payroll_calendar');
            Route::post         ('/update/{id}',                 'PayrollCalendarController@update'                             )->name('update_payroll_calendar');
            Route::post         ('/destroy',                     'PayrollCalendarController@destroy'                            )->name('destroy_payroll_calendar');
        });

        Route::group(['prefix' => '/company-profile'], function (){
            Route::get          ('/',                            'CompanyProfileController@index'                               )->name('company_profile');
            Route::get          ('/get',                         'CompanyProfileController@get'                                 )->name('get_company_profile');
            Route::post         ('/save',                        'CompanyProfileController@store'                               )->name('save_company_profile');
            Route::get          ('/edit/{id}',                   'CompanyProfileController@edit'                                )->name('edit_company_profile');
            Route::post         ('/update/{id}',                 'CompanyProfileController@update'                              )->name('update_company_profile');
            Route::post         ('/destroy',                     'CompanyProfileController@destroy'                             )->name('destroy_company_profile');
        });

        Route::group(['prefix' => '/classes'], function (){
            Route::get          ('/',                            'ClassesController@index'                                      )->name('classes');
            Route::get          ('/get',                         'ClassesController@get'                                        )->name('get_classes');
            Route::post         ('/save',                        'ClassesController@store'                                      )->name('save_classes');
            Route::get          ('/edit/{id}',                   'ClassesController@edit'                                       )->name('edit_classes');
            Route::post         ('/update/{id}',                 'ClassesController@update'                                     )->name('update_classes');
            Route::post         ('/destroy',                     'ClassesController@destroy'                                    )->name('destroy_classes');
        });


        Route::group(['prefix' => '/department'], function (){
            Route::get          ('/',                            'DepartmentsController@index'                                  )->name('department');
            Route::get          ('/get',                         'DepartmentsController@get'                                    )->name('get_department');
            Route::post         ('/save',                        'DepartmentsController@store'                                  )->name('save_department');
            Route::get          ('/edit/{id}',                   'DepartmentsController@edit'                                   )->name('edit_department');
            Route::post         ('/update/{id}',                 'DepartmentsController@update'                                 )->name('update_department');
            Route::post         ('/destroy',                     'DepartmentsController@destroy'                                )->name('destroy_department');
        });

        Route::group(['prefix' => '/withholding_tax'], function (){
            Route::get          ('/',                            'WithholdingTaxController@index'                               )->name('withholding_tax');
            Route::get          ('/get',                         'WithholdingTaxController@get'                                 )->name('get_withholding_tax');
            Route::post         ('/save',                        'WithholdingTaxController@store'                               )->name('save_withholding_tax');
            Route::get          ('/edit/{id}',                   'WithholdingTaxController@edit'                                )->name('edit_withholding_tax');
            Route::post         ('/update/{id}',                 'WithholdingTaxController@update'                              )->name('update_withholding_tax');
            Route::post         ('/destroy',                     'WithholdingTaxController@destroy'                             )->name('destroy_withholding_tax');
        });

        Route::group(['prefix' => '/position'], function (){
            Route::get          ('/',                            'PositionsController@index'                                    )->name('position');
            Route::get          ('/get',                         'PositionsController@get'                                      )->name('get_position');
            Route::post         ('/save',                        'PositionsController@store'                                    )->name('save_position');
            Route::get          ('/edit/{id}',                   'PositionsController@edit'                                     )->name('edit_position');
            Route::post         ('/update/{id}',                 'PositionsController@update'                                   )->name('update_position');
            Route::post         ('/destroy',                     'PositionsController@destroy'                                  )->name('destroy_position');
        });

        Route::group(['prefix' => '/leave-type'], function (){
            Route::get          ('/',                            'LeaveTypeController@index'                                    )->name('leave_type');
            Route::get          ('/get',                         'LeaveTypeController@get'                                      )->name('get_leave_type');
            Route::post         ('/save',                        'LeaveTypeController@store'                                    )->name('save_leave_type');
            Route::get          ('/edit/{id}',                   'LeaveTypeController@edit'                                     )->name('edit_leave_type');
            Route::post         ('/update/{id}',                 'LeaveTypeController@update'                                   )->name('update_leave_type');
            Route::post         ('/destroy',                     'LeaveTypeController@destroy'                                  )->name('destroy_position');
        });

        Route::group(['prefix' => '/holiday_type'], function (){
            Route::get          ('/',                            'HolidayTypeController@index'                                    )->name('leave_type');
            Route::get          ('/get',                         'HolidayTypeController@get'                                      )->name('get_leave_type');
            Route::post         ('/save',                        'HolidayTypeController@store'                                    )->name('save_leave_type');
            Route::get          ('/edit/{id}',                   'HolidayTypeController@edit'                                     )->name('edit_leave_type');
            Route::post         ('/update/{id}',                 'HolidayTypeController@update'                                   )->name('update_leave_type');
            Route::post         ('/destroy',                     'HolidayTypeController@destroy'                                  )->name('destroy_position');
        });

        Route::group(['prefix' => '/holiday'], function (){
            Route::get          ('/',                            'HolidayController@index'                                    )->name('leave_type');
            Route::get          ('/get',                         'HolidayController@get'                                      )->name('get_leave_type');
            Route::post         ('/save',                        'HolidayController@store'                                    )->name('save_leave_type');
            Route::get          ('/edit/{id}',                   'HolidayController@edit'                                     )->name('edit_leave_type');
            Route::post         ('/update/{id}',                 'HolidayController@update'                                   )->name('update_leave_type');
            Route::post         ('/destroy',                     'HolidayController@destroy'                                  )->name('destroy_position');
        });

        Route::group(['prefix' => '/earnings'], function (){
            Route::get          ('/',                            'EarningsController@index'                                     )->name('earnings');
            Route::get          ('/get',                         'EarningsController@get'                                       )->name('get_earnings');
            Route::post         ('/save',                        'EarningsController@store'                                     )->name('save_earnings');
            Route::get          ('/edit/{id}',                   'EarningsController@edit'                                      )->name('edit_earnings');
            Route::post         ('/update/{id}',                 'EarningsController@update'                                    )->name('update_earnings');
            Route::post         ('/destroy',                     'EarningsController@destroy'                                   )->name('destroy_earnings');
        });

        Route::group(['prefix' => '/leave_request'], function (){
            Route::get          ('/',                            'LeaveRequestController@index'                                     )->name('page');
            Route::get          ('/get',                         'LeaveRequestController@get'                                       )->name('get');
            Route::post         ('/save',                        'LeaveRequestController@store'                                     )->name('save');
            Route::get          ('/edit/{id}',                   'LeaveRequestController@edit'                                      )->name('edit');
            Route::post         ('/update/{id}',                 'LeaveRequestController@update'                                    )->name('update');
            Route::post         ('/destroy',                     'LeaveRequestController@destroy'                                   )->name('destroy');
            Route::post         ('/approve',                     'LeaveRequestController@approve_leave'                                   )->name('update');
            Route::post         ('/get-leave',                     'LeaveRequestController@getLeave'                                   )->name('get');
        });

        Route::group(['prefix' => '/quit-claims'], function (){
            Route::get          ('/',                            'QuitClaimsController@index'                                     )->name('page');
            Route::get          ('/get',                         'QuitClaimsController@get'                                       )->name('get');
            Route::post         ('/save',                        'QuitClaimsController@store'                                     )->name('save');
            Route::get          ('/get-last-pay/{id}',           'QuitClaimsController@getLastPay'                                )->name('get');
        });

        Route::group(['prefix' => '/quit-claims-additions'], function (){
            Route::get          ('/get/{id}',                    'QuitClaimsAdditionsController@get'                                       )->name('get');
            Route::post         ('/save',                        'QuitClaimsAdditionsController@save'                                      )->name('save');
            Route::get          ('/edit/{id}',                   'QuitClaimsAdditionsController@edit'                                      )->name('edit');
            Route::post         ('/update/{id}',                 'QuitClaimsAdditionsController@update'                                    )->name('update');
            Route::post         ('/destroy',                     'QuitClaimsAdditionsController@destroy'                                   )->name('destroy');
        });

        Route::group(['prefix' => '/quit-claims-deductions'], function (){
            Route::get          ('/get/{id}',                    'QuitClaimsDeductionsController@get'                                       )->name('get');
            Route::post         ('/save',                        'QuitClaimsDeductionsController@save'                                      )->name('save');
            Route::get          ('/edit/{id}',                   'QuitClaimsDeductionsController@edit'                                      )->name('edit');
            Route::post         ('/update/{id}',                 'QuitClaimsDeductionsController@update'                                    )->name('update');
            Route::post         ('/destroy',                     'QuitClaimsDeductionsController@destroy'                                   )->name('destroy');
        });

        Route::group(['prefix' => '/13-month'], function (){
            Route::get          ('/',                                   'PayMonthController@index'                                          )->name('page');
            Route::get          ('/get-monthly/{year}',                 'PayMonthController@get'                                            )->name('get');
            Route::get          ('/get-absents/{year}/{employee_id}',   'PayMonthController@get_absents'                                    )->name('get');
            Route::get          ('/get-lates/{year}/{employee_id}',     'PayMonthController@get_lates'                                      )->name('get');
            Route::post         ('/release',                            'PayMonthController@release'                                        )->name('save');
            Route::get          ('/get-slip/{id}/{sequence}',           'PayrollSummaryController@get13thPay'                               )->name('get');
            Route::get          ('/get-fixed/{year}',                   'PayMonthController@getFixed'                                       )->name('get');
            Route::get          ('/get-daily/{year}',                   'PayMonthController@getDaily'                                       )->name('get');
            Route::get          ('/get-daily-logs/{year}/{id}',         'PayMonthController@getDailyLogs'                                   )->name('get');
            Route::get          ('/get-monthly-logs/{year}/{id}',       'PayMonthController@getMonthlyLogs'                                 )->name('get');
        });

        Route::group(['prefix' => '/absent'], function (){
            Route::post         ('/save',                        'AbsentController@store'                                 )->name('save');
            Route::post         ('/destroy',                     'AbsentController@destroy'                                   )->name('destroy');
            Route::get          ('/get/{year}/{employee_id}',    'AbsentController@get'                                       )->name('get');
        });
        
        Route::group(['prefix' => '/late'], function (){
            Route::post         ('/save',                        'LateController@store'                                                     )->name('save');
            Route::post         ('/destroy',                     'LateController@destroy'                                                   )->name('destroy');
            Route::get          ('/get/{year}/{employee_id}',    'LateController@get'                                                       )->name('get');
        });
        
        Route::group(['prefix' => '/overtime_request'], function (){
            Route::get          ('/',                            'OvertimeRequestController@index'                                 )->name('page');
            Route::get          ('/get',                         'OvertimeRequestController@get'                                   )->name('get');
            Route::get          ('/get_filter/{start}/{end}',    'OvertimeRequestController@get_filter'                            )->name('get_filter');
            Route::post         ('/save',                        'OvertimeRequestController@store'                                 )->name('save');
            Route::get          ('/edit/{id}',                   'OvertimeRequestController@edit'                                  )->name('edit');
            Route::post         ('/update/{id}',                 'OvertimeRequestController@update'                                )->name('update');
            Route::post         ('/destroy',                     'OvertimeRequestController@destroy'                               )->name('destroy');
            Route::post         ('/approve',                     'OvertimeRequestController@approve_leave'                         )->name('approve');
        });

        Route::group(['prefix' => '/schedule_request'], function (){
            Route::get          ('/',                            'ScheduleRequestController@index'                                 )->name('page');
            Route::get          ('/get',                         'ScheduleRequestController@get'                                   )->name('get');
            Route::post         ('/save',                        'ScheduleRequestController@store'                                 )->name('save');
            Route::get          ('/edit/{id}',                   'ScheduleRequestController@edit'                                  )->name('edit');
            Route::post         ('/update/{id}',                 'ScheduleRequestController@update'                                )->name('update');
            Route::post         ('/destroy',                     'ScheduleRequestController@destroy'                               )->name('destroy');
            Route::post         ('/approve',                     'ScheduleRequestController@approve'                               )->name('approve');
        });

        Route::group(['prefix' => '/allowance'], function (){
            Route::get          ('/',                            'AllowanceController@index'                                   )->name('page');
            Route::get          ('/get',                         'AllowanceController@get'                                     )->name('get');
            Route::post         ('/save',                        'AllowanceController@store'                                   )->name('save');
            Route::get          ('/edit/{id}',                   'AllowanceController@edit'                                    )->name('edit');
            Route::post         ('/update/{id}',                 'AllowanceController@update'                                  )->name('update');
            Route::post         ('/destroy',                     'AllowanceController@destroy'                                 )->name('destroy');
            Route::post         ('/tag',                         'AllowanceController@addAllowanceTag'                         )->name('save');
            Route::post         ('/get-tag',                     'AllowanceController@getAllowance'                            )->name('get');
            Route::get          ('/get-amount/{id}',             'AllowanceController@getAmount'                               )->name('get');
        });

        Route::group(['prefix' => '/deductions'], function (){
            Route::get          ('/',                            'DeductionsController@index'                                   )->name('deductions');
            Route::get          ('/get',                         'DeductionsController@get'                                     )->name('get_deductions');
            Route::post         ('/save',                        'DeductionsController@store'                                   )->name('save_deductions');
            Route::get          ('/edit/{id}',                   'DeductionsController@edit'                                    )->name('edit_deductions');
            Route::post         ('/update/{id}',                 'DeductionsController@update'                                  )->name('update_deductions');
            Route::post         ('/destroy',                     'DeductionsController@destroy'                                 )->name('destroy_deductions');
        });

        Route::group(['prefix' => '/work_assignments'], function (){
            Route::get          ('/',                            'WorkAssignmentsController@index'                              )->name('work_assignments');
            Route::get          ('/get',                         'WorkAssignmentsController@get'                                )->name('get_work_assignments');
            Route::post         ('/save',                        'WorkAssignmentsController@store'                              )->name('save_work_assignments');
            Route::get          ('/edit/{id}',                   'WorkAssignmentsController@edit'                               )->name('edit_work_assignments');
            Route::post         ('/update/{id}',                 'WorkAssignmentsController@update'                             )->name('update_work_assignments');
            Route::post         ('/destroy',                     'WorkAssignmentsController@destroy'                            )->name('destroy_work_assignments');
        });

        Route::group(['prefix' => '/scheduling'], function (){
            Route::get          ('/',                                'SchedulingsController@index'                              )->name('scheduling');
            Route::get          ('/get/{department}/{first}/{last}', 'SchedulingsController@get'                                )->name('get_scheduling');
            Route::post         ('/save',                            'SchedulingsController@save'                               )->name('save_scheduling');
            Route::post         ('/copy_schedule',                   'SchedulingsController@copy_schedule'                      )->name('copy_schedule');
            Route::post         ('/paste_schedule',                  'SchedulingsController@paste_schedule'                     )->name('paste_schedule');
        });
        
        Route::group(['prefix' => '/payrun'], function (){
            Route::get          ('/',                                 'PayrunController@index'                                )->name('payrun');
            Route::post         ('/get',                              'PayrunController@get'                                  )->name('get');
            Route::post         ('/get-details',                      'PayrunController@getDetails'                           )->name('get');
            Route::post         ('/save',                             'PayrunController@save'                                 )->name('save');
            Route::post         ('/get-employee',                     'PayrunController@getEmployeeDetails'                   )->name('get');
            Route::get          ('/get-sched-type/{id}',              'PayrunController@getSchedType'                         )->name('get');
            Route::post         ('/get-ot-list',                      'PayrunController@getOTList'                            )->name('get');
            Route::post         ('/save-update',                      'PayrunController@saveUpdate'                           )->name('save');
            Route::post         ('/delete-record',                    'PayrunController@deleteRecord'                         )->name('delete');
            Route::post         ('/approve-details',                  'PayrunController@approveDetails'                       )->name('approve');
            Route::post         ('/cross-details',                    'PayrunController@crossDetails'                         )->name('approve');
            Route::post         ('/submit-for-approval',              'PayrunController@submitForApproval'                    )->name('payrun_submit_for_approval');
            Route::post         ('/approve-summary',                  'PayrunController@approveSummary'                       )->name('payrun_approve_summary');
            Route::post         ('/revert-summary',                   'PayrunController@revertSummary'                        )->name('payrun_revert_summary');
            Route::post         ('/submit-for-payment',               'PayrunController@submitForPayment'                     )->name('payrun_submit_for_payment');
            Route::get          ('/edit/{id}',                        'PayrunController@edit'                                 )->name('edit');
            Route::post         ('/update/{id}',                      'PayrunController@update'                               )->name('update');
            Route::post         ('/get-details-info',                 'PayrunController@getDetailsInfo'                       )->name('get');
        });
        
        Route::group(['prefix' => '/payrun_amount'], function (){
            Route::post         ('/update/{id}',                      'PayrunController@updateAmount'                         )->name('update');
        });

        Route::group(['prefix' => '/time_logs'], function (){
            Route::get          ('/',                                 'TimeLogsController@index'                                )->name('time_logs');
            Route::get          ('/get/{department}/{first}/{last}',  'TimeLogsController@get'                                  )->name('get_time_logs');
            Route::get          ('/plot/{employee}/{first}/{last}',   'TimeLogsController@plot'                                 )->name('get_time_plotting_employee');
            Route::post         ('/earnings',                         'TimeLogsController@get_earnings'                         )->name('get_earnings');
            Route::post         ('/save',                             'TimeLogsController@save'                                 )->name('save_time_logs');
            Route::post         ('/update-status',                    'TimeLogsController@update_status'                        )->name('update_status');
            Route::post         ('/cross-matching',                   'TimeLogsController@cross_matching'                       )->name('cross_matching');
            Route::post         ('/get_record/{id}',                  'TimeLogsController@get_record'                           )->name('get');
            Route::post         ('/get_dates',                        'TimeLogsController@get_date'                             )->name('get_date');
            Route::post         ('/approve',                        'TimeLogsController@timelogs_approve'                       )->name('approve');
            Route::post         ('/get_summary',                        'TimeLogsController@get_summary'                        )->name('get');
            Route::get          ('/get_calendar/{type}',                        'TimeLogsController@get_calendar'               )->name('get');

        });

        Route::group(['prefix' => '/benefits'], function (){
            Route::get          ('/',                            'BenefitsController@index'                                     )->name('benefits');
            Route::get          ('/get',                         'BenefitsController@get'                                       )->name('get_benefits');
            Route::get          ('/employee_benefit/{id}',       'BenefitsController@employee_benefit'                               )->name('get_benefits');
            Route::get          ('/sss/{id}',                    'BenefitsController@sss_summary'                               )->name('get_benefits');
            Route::get          ('/sss-total/{id}',              'BenefitsController@sss_total'                                 )->name('get_benefits');
            Route::get          ('/pagibig/{id}',                'BenefitsController@pagibig_summary'                               )->name('get_benefits');
            Route::get          ('/pagibig-total/{id}',          'BenefitsController@pagibig_total'                             )->name('get_benefits');
            Route::get          ('/philhealth/{id}',             'BenefitsController@philhealth_summary'                               )->name('get_benefits');
            Route::get          ('/philhealth-total/{id}',       'BenefitsController@philhealth_total'                          )->name('get_benefits');
            Route::get          ('/tax/{id}',                    'BenefitsController@tax_summary'                               )->name('get_benefits');
            Route::post         ('/save',                        'BenefitsController@store'                                     )->name('save_benefits');
            Route::get          ('/edit/{id}',                   'BenefitsController@edit'                                      )->name('edit_benefits');
            Route::post         ('/update/{id}',                 'BenefitsController@update'                                    )->name('update_benefits');
            Route::post         ('/destroy',                     'BenefitsController@destroy'                                   )->name('destroy_benefits');
            Route::post         ('/governmentMandated',          'BenefitsController@governmentMandatedBenefits'                )->name('get_government_mandated_benefits');
            Route::post         ('/otherCompany',                'BenefitsController@otherCompanyBenefits'                      )->name('get_other_company_benefits');
        });
        
        Route::group(['prefix' => '/work_type'], function (){
            Route::get          ('/',                            'WorkTypeController@index'                                     )->name('page');
            Route::get          ('/get',                         'WorkTypeController@get'                                       )->name('get');
            Route::post         ('/save',                        'WorkTypeController@store'                                     )->name('save');
            Route::get          ('/edit/{id}',                   'WorkTypeController@edit'                                      )->name('edit');
            Route::post         ('/update/{id}',                 'WorkTypeController@update'                                    )->name('update');
            Route::post         ('/destroy',                     'WorkTypeController@destroy'                                   )->name('destroy');
        });

        Route::group(['prefix' => '/clearance_types'], function (){
            Route::get          ('/',                            'ClearanceTypeController@index'                                     )->name('clearance_types');
            Route::get          ('/get',                         'ClearanceTypeController@get'                                       )->name('get');
            Route::post         ('/save',                        'ClearanceTypeController@store'                                     )->name('save');
            Route::get          ('/edit/{id}',                   'ClearanceTypeController@edit'                                      )->name('edit');
            Route::post         ('/update/{id}',                 'ClearanceTypeController@update'                                    )->name('update');
            Route::post         ('/destroy',                     'ClearanceTypeController@destroy'                                   )->name('destroy');
        });

        Route::group(['prefix' => '/clearance'], function (){
            Route::post         ('/update/{id}',                 'ClearanceController@save'                                     )->name('save');
            Route::get          ('/get/{id}',                    'ClearanceController@get'                                       )->name('get');
        });

        Route::group(['prefix' => '/reimbursements'], function (){
            Route::get          ('/',                            'ReimbursementController@index'                                     )->name('page');
            Route::get          ('/get',                         'ReimbursementController@get'                                       )->name('get');
            Route::post         ('/save',                        'ReimbursementController@store'                                     )->name('save');
            Route::get          ('/edit/{id}',                   'ReimbursementController@edit'                                      )->name('edit');
            Route::post         ('/update/{id}',                 'ReimbursementController@update'                                    )->name('update');
            Route::post         ('/destroy',                     'ReimbursementController@destroy'                                   )->name('destroy');
        });

        Route::group(['prefix' => '/employment'], function (){
            Route::post          ('/update/{id}',                'EmploymentController@save'                                    )->name('save_employment');
            Route::post          ('/destroy',                    'EmploymentController@destroy'                                 )->name('destroy_employment');
        });

        Route::group(['prefix' => '/leaves'], function (){
            Route::post          ('/update/{id}',                'LeavesController@save'                                        )->name('save_employment');
            Route::get           ('/get/{id}',                   'LeavesController@get'                                         )->name('get_leaves');
            Route::get           ('/history/{id}',               'LeavesController@history'                                     )->name('get_leave_history');
            Route::post          ('/destroy',                    'LeavesController@destroy'                                     )->name('destroy_leaves');
        });

        Route::group(['prefix' => '/compensation'], function (){
            Route::post          ('/update/{id}',                'CompensationsController@save'                                 )->name('save_employment');
            Route::get           ('/get/{id}',                   'CompensationsController@get'                                 )->name('save_employment');
            Route::post          ('/compute',                    'CompensationsController@compute'                             )->name('compute_compensation');
            Route::get           ('/get-gov-record/{id}',        'CompensationsController@getGovernmentMandatedRecord'          )->name('get_government_mandated_record');
            Route::get           ('/get-com-record/{id}',        'CompensationsController@getCompanyBenefits'                   )->name('get_government_mandated_record');
            Route::post          ('/destroy',                    'CompensationsController@destroy'                              )->name('destroy_leaves');
            Route::post          ('/salary',                     'CompensationsController@salary'                               )->name('get_salaries');
        });

        Route::group(['prefix' => '/educational-background'], function (){
            Route::post          ('/update/{id}',                'EmployeeEducationalBackgroundController@save'                 )->name('save_education_background');
            Route::get           ('/get/{id}',                   'EmployeeEducationalBackgroundController@get'                  )->name('get_education_background');
            Route::post          ('/destroy',                    'EmployeeEducationalBackgroundController@destroy'              )->name('destroy_employment');
        });

        Route::group(['prefix' => '/work-history'], function (){
            Route::post          ('/update/{id}',                'EmployeeWorkHistoryController@save'                           )->name('save_education_background');
            Route::get           ('/get/{id}',                   'EmployeeWorkHistoryController@get'                            )->name('get_education_background');
            Route::post          ('/destroy',                    'EmployeeWorkHistoryController@destroy'                        )->name('destroy_employment');
        });

        Route::group(['prefix' => '/certification'], function (){
            Route::post          ('/update/{id}',                'EmployeeCertificationController@save'                         )->name('save_education_background');
            Route::get           ('/get/{id}',                   'EmployeeCertificationController@get'                          )->name('get_education_background');
            Route::post          ('/destroy',                    'EmployeeCertificationController@destroy'                      )->name('destroy_employment');
        });

        Route::group(['prefix' => '/training'], function (){
            Route::post          ('/update/{id}',                'EmployeeTrainingController@save'                              )->name('save_education_background');
            Route::get           ('/get/{id}',                   'EmployeeTrainingController@get'                               )->name('get_education_background');
            Route::post          ('/destroy',                    'EmployeeTrainingController@destroy'                           )->name('destroy_employment');
        });

        Route::group(['prefix' => '/work-calendar'], function (){
            Route::post          ('/update/{id}',                'WorkCalendarController@save'                                  )->name('save_employment');
            Route::get           ('/presets',                    'WorkCalendarController@getPresets'                            )->name('get_work_calendar_presets');
            Route::post          ('/preset',                     'WorkCalendarController@savePreset'                            )->name('save_work_calendar_preset');
        });
        
        Route::group(['prefix' => '/work_type_setup'], function (){
            Route::post         ('/save',                    'WorkTypeSetupController@save'                                   )->name('save');
            Route::get          ('/get/{id}/{days}',                'WorkTypeSetupController@get'                                    )->name('get');
            Route::get          ('/edit/{id}',               'WorkTypeSetupController@edit'                                   )->name('edit');
            Route::post         ('/update/{id}',             'WorkTypeSetupController@update'                                 )->name('update');
            Route::post         ('/destroy',                 'WorkTypeSetupController@destroy'                                )->name('destroy');
        });

        Route::group(['prefix' => '/timesheet'], function (){
            Route::group(['prefix' => '/daily'], function (){
                Route::get          ('/',                        'DailyTimesheetController@index'                               )->name('page');
                Route::get          ('/get/{date}/{project}',    'DailyTimesheetController@get'                                 )->name('get');
                Route::get          ('/edit/{id}',               'DailyTimesheetController@edit'                                )->name('edit');
                Route::post         ('/update/{id}',             'DailyTimesheetController@update'                              )->name('update');
                Route::post         ('/destroy',                 'DailyTimesheetController@destroy'                             )->name('destroy');
            });
            
            Route::group(['prefix' => '/summary'], function (){
                Route::get          ('/',                        'SummaryTimesheetController@index'                             )->name('page');
                Route::get          ('/get',                     'SummaryTimesheetController@get'                               )->name('get');
                Route::post         ('/get-response',            'SummaryTimesheetController@getRecord'                         )->name('get');
                Route::get          ('/edit/{id}',               'SummaryTimesheetController@edit'                              )->name('edit');
                Route::post         ('/update/{id}',             'SummaryTimesheetController@update'                            )->name('update');
                Route::post         ('/destroy',                 'SummaryTimesheetController@destroy'                           )->name('destroy');
            });

            Route::group(['prefix' => '/work-details'], function (){
                Route::post         ('/save',                    'WorkDetailsController@save'                                   )->name('save');
                Route::get          ('/get/{id}',                'WorkDetailsController@get'                                    )->name('get');
                Route::get          ('/edit/{id}',               'WorkDetailsController@edit'                                   )->name('edit');
                Route::post         ('/update/{id}',             'WorkDetailsController@update'                                 )->name('update');
                Route::post         ('/destroy',                 'WorkDetailsController@destroy'                                )->name('destroy');
            });
        });
    });

    Route::group(['prefix' => '/settings'], function (){

        Route::group(['prefix' => '/apps'], function (){
            Route::get          ('/',                            'AppController@index'                                          )->name('classes');
            Route::get          ('/get',                         'AppController@get'                                            )->name('get_classes');
            Route::post         ('/save',                        'AppController@store'                                          )->name('save_classes');
            Route::get          ('/edit/{id}',                   'AppController@edit'                                           )->name('edit_classes');
            Route::post         ('/update/{id}',                 'AppController@update'                                         )->name('update_classes');
            Route::get          ('/destroy/{id}',                'AppController@destroy'                                        )->name('destroy_classes');
        });

        Route::group(['prefix' => '/app_items'], function (){
            Route::get          ('/',                            'AppItemController@index'                                         )->name('classes');
            Route::get          ('/get',                         'AppItemController@get'                                           )->name('get_classes');
            Route::post         ('/save',                        'AppItemController@store'                                         )->name('save_classes');
            Route::get          ('/edit/{id}',                   'AppItemController@edit'                                          )->name('edit_classes');
            Route::post         ('/update/{id}',                 'AppItemController@update'                                        )->name('update_classes');
            Route::get          ('/destroy/{id}',                'AppItemController@destroy'                                       )->name('destroy_classes');
        });

        Route::group(['prefix' => '/users'], function (){
            Route::get          ('/',                            'UserController@index'                                         )->name('classes');
            Route::get          ('/get',                         'UserController@get'                                           )->name('get_classes');
            Route::post         ('/save',                        'UserController@store'                                         )->name('save_classes');
            Route::get          ('/edit/{id}',                   'UserController@edit'                                          )->name('edit_classes');
            Route::post         ('/update/{id}',                 'UserController@update'                                        )->name('update_classes');
            Route::post         ('/destroy',                     'UserController@destroy'                                       )->name('destroy_classes');
        });

        Route::group(['prefix' => '/role'], function (){
            Route::get          ('/',                            'RolesController@index'                                        )->name('employment_information');
            Route::get          ('/get',                         'RolesController@get'                                           )->name('get_classes');
            Route::post         ('/save',                        'RolesController@store'                                        )->name('save_employment_information');
            Route::get          ('/edit/{id}',                   'RolesController@edit'                                         )->name('edit_employment_information');
            Route::post         ('/update/{id}',                 'RolesController@update'                                       )->name('update_employment_information');
            Route::post          ('/destroy',                    'RolesController@destroy'                                      )->name('destroy_employment_information');
        });

        Route::group(['prefix' => '/access'], function (){
            Route::get          ('/',                            'ModelHasRolesController@index'                                        )->name('employment_information');
            Route::get          ('/get/{id}',                    'ModelHasRolesController@get'                                          )->name('get_classes');
            Route::post         ('/save',                        'ModelHasRolesController@store'                                        )->name('save_employment_information');
            Route::get          ('/edit/{id}',                   'ModelHasRolesController@edit'                                         )->name('edit_employment_information');
            Route::post         ('/update/{id}',                 'ModelHasRolesController@update'                                       )->name('update_employment_information');
            Route::post          ('/destroy',                    'ModelHasRolesController@destroy'                                      )->name('destroy_employment_information');
        });

        Route::group(['prefix' => 'permission', 'middleware' => ['auth']], function (){
            Route::get('/', 'PermissionController@index')->name('permissions.index');
            Route::post('/update', 'PermissionController@updatePermissions')->name('permissions.update');
            Route::get('/{id}/role-permissions', function ($id) {
                $role = App\Roles::find($id);
                return response()->json(['permissions' => $role->permissions->pluck('id')]);
            })->name('permissions.role_permissions');
        });

    });

    Route::group(['prefix' => '/inventory'], function (){

        Route::group(['prefix' => '/inventory_request'], function (){
            Route::get          ('/',                            'PurchaseOrderController@inventoryIndex'                                      )->name('classes');
        });

        Route::group(['prefix' => '/inventory'], function (){
            Route::get          ('/',                            'InventoryController@index'                                      )->name('classes');
            Route::get          ('/get',                         'InventoryController@get'                                        )->name('get_classes');
            Route::post         ('/save',                        'InventoryController@store'                                      )->name('save_classes');
            Route::post         ('/damage/save/{id}',            'InventoryController@damage'                                      )->name('save_classes');
            Route::post         ('/transaction/save/{id}',       'InventoryController@transaction'                                      )->name('save_classes');
            Route::post         ('/transfer/save/{id}',          'InventoryController@transfer'                                      )->name('save_classes');
            Route::get          ('/edit/{id}',                   'InventoryController@edit'                                       )->name('edit_classes');
            Route::post         ('/update/{id}',                 'InventoryController@update'                                     )->name('update_classes');
            Route::post         ('/destroy',                     'InventoryController@destroy'                                    )->name('destroy_classes');
            Route::post         ('/audittrails/{id}',                  'InventoryController@AuditTrails'                                    )->name('get_trails');
        });

        Route::group(['prefix' => '/inventory_transaction'], function (){
            Route::get          ('/',                            'InventoryTransactionController@index'                                      )->name('classes');
            Route::get          ('/get',                         'InventoryTransactionController@get'                                        )->name('get_classes');
            Route::post         ('/save',                        'InventoryTransactionController@store'                                      )->name('save_classes');
            Route::post         ('/damage/save/{id}',            'InventoryTransactionController@damage'                                      )->name('save_classes');
            Route::post         ('/transaction/save/{id}',       'InventoryTransactionController@transaction'                                      )->name('save_classes');
            Route::post         ('/transfer/save/{id}',          'InventoryTransactionController@transfer'                                      )->name('save_classes');
            Route::get          ('/edit/{id}',                   'InventoryTransactionController@edit'                                       )->name('edit_classes');
            Route::post         ('/update/{id}',                 'InventoryTransactionController@update'                                     )->name('update_classes');
            Route::post         ('/destroy',                     'InventoryTransactionController@destroy'                                    )->name('destroy_classes');
            Route::post         ('/audittrails/{id}',            'InventoryTransactionController@AuditTrails'                                    )->name('get_trails');
        });

        Route::group(['prefix' => '/history'], function (){
            Route::get          ('/',                            'InventoryTransactionController@index'                                      )->name('classes');
            Route::get          ('/get',                         'InventoryTransactionController@get'                                        )->name('get_classes');
            Route::post         ('/save',                        'InventoryTransactionController@store'                                      )->name('save_classes');
            Route::get          ('/edit/{id}',                   'InventoryTransactionController@edit'                                       )->name('edit_classes');
            Route::post         ('/update/{id}',                 'InventoryTransactionController@update'                                     )->name('update_classes');
            Route::post         ('/destroy',                     'InventoryTransactionController@destroy'                                    )->name('destroy_classes');
        });

        Route::group(['prefix' => '/transfer_history'], function (){
            Route::get          ('/',                            'InventoryTransferController@index'                                      )->name('classes');
            Route::get          ('/get',                         'InventoryTransferController@get'                                        )->name('get_classes');
            Route::post         ('/save',                        'InventoryTransferController@store'                                      )->name('save_classes');
            Route::get          ('/edit/{id}',                   'InventoryTransferController@edit'                                       )->name('edit_classes');
            Route::post         ('/update/{id}',                 'InventoryTransferController@update'                                     )->name('update_classes');
            Route::post         ('/destroy',                     'InventoryTransferController@destroy'                                    )->name('destroy_classes');
        });

        Route::group(['prefix' => '/owner_supplied_material'], function (){
            Route::get          ('/',                            'OwnerSuppliedMaterialController@index'                                      )->name('classes');
            Route::get          ('/get',                         'OwnerSuppliedMaterialController@get'                                        )->name('get_classes');
            Route::post         ('/save',                        'OwnerSuppliedMaterialController@store'                                      )->name('save_classes');
            Route::post         ('/transaction/save/{id}',       'OwnerSuppliedMaterialController@transaction'                                      )->name('save_classes');
            Route::get          ('/edit/{id}',                   'OwnerSuppliedMaterialController@edit'                                       )->name('edit_classes');
            Route::post         ('/update/{id}',                 'OwnerSuppliedMaterialController@update'                                     )->name('update_classes');
            Route::post         ('/destroy',                     'OwnerSuppliedMaterialController@destroy'                                    )->name('destroy_classes');
        });

        Route::group(['prefix' => '/damage'], function (){
            Route::get          ('/',                            'InventoryDamageController@index'                                      )->name('classes');
            Route::get          ('/get',                         'InventoryDamageController@get'                                        )->name('get_classes');
            Route::post         ('/save',                        'InventoryDamageController@store'                                      )->name('save_classes');
            Route::get          ('/edit/{id}',                   'InventoryDamageController@edit'                                       )->name('edit_classes');
            Route::post         ('/update/{id}',                 'InventoryDamageController@update'                                     )->name('update_classes');
            Route::post         ('/destroy',                     'InventoryDamageController@destroy'                                    )->name('destroy_classes');
        });

        // Route::group(['prefix' => '/inventory_trail'], function (){
        //     Route::get          ('/',                            'InventoryTransactionController@index'                                      )->name('classes');
        // });
    });

});


Route::group(['prefix' => '/address'], function (){
    Route::get          ('/province/{id}',                         'GlobalController@get_province'                                          )->name('get');
    Route::get          ('/city/{id}',                             'GlobalController@get_city'                                              )->name('get');
    Route::get          ('/barangay/{id}',                         'GlobalController@get_barangay'                                          )->name('get');
});

Route::group(['prefix' => '/activity'], function (){
    Route::post         ('/save',                                  'ActivityController@save'                                                )->name('save');
});

// EMPLOYEE UI

Route::get('/payroll/employee/dashboard', function() {
    return view('backend.pages.payroll.transaction.employee.dashboard');
});

Route::get('/payroll/employee/overtime_application', function() {
    return view('backend.pages.payroll.transaction.employee.overtime_application');
});

Route::get('/payroll/employee/leave_application', function() {
    return view('backend.pages.payroll.transaction.employee.leave_application');
});

Route::get('/payroll/employee/reimbursement', function() {
    return view('backend.pages.payroll.transaction.employee.reimbursement');
});

Route::get('/payroll/employee/leave_monetization', function() {
    return view('backend.pages.payroll.transaction.employee.leave_monetization');
});

Route::get('/payroll/employee/leave_management', function() {
    return view('backend.pages.payroll.transaction.employee.leave_management');
});

Route::get('/payroll/employee/employee_reports', function() {
    return view('backend.pages.payroll.transaction.employee.employee_reports');
});

// END EMPLOYEE UI

Route::post('/sender', function() {
    $text = request()->text;
    event(new FormSubmitted($text));
});

Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Auth::routes();


Route::post('change-password', 'UserController@changepass')->name('change.password');
Route::post('change-photo', 'UserController@changePicture')->name('change.picture');

Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');


Route::group(['prefix' => 'attendances'], function() {
    Route::get          ('/{type}',                            'AttendanceController@getScreen'                                   )->name('get');
});

Route::group(['prefix' => 'front'], function() {
    Route::group(['prefix' => 'attendance'], function() {
        Route::get          ('/get/{type}',                     'AttendanceController@get'                                        )->name('get');
        Route::post         ('/save',                           'AttendanceController@store'                                      )->name('save');
        Route::get          ('/get_employee/{rfid}',            'AttendanceController@get_employee'                               )->name('get');
    });
});
