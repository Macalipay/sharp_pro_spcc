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

    Route::get('/reports/accounting/journal-entry-impact', function (\Illuminate\Http\Request $request) {
        $perPageAllowed = [10, 15, 20, 25, 30];
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, $perPageAllowed, true)) {
            $perPage = 10;
        }

        $coaImpactRows = \Illuminate\Support\Facades\DB::table('chart_of_accounts as coa')
            ->leftJoin('account_types as at', 'at.id', '=', 'coa.account_type')
            ->whereNull('coa.deleted_at')
            ->select([
                'coa.account_number as account_code',
                'coa.account_name',
                'at.account_type',
                'at.category',
                \Illuminate\Support\Facades\DB::raw("CASE
                    WHEN UPPER(COALESCE(at.category, '')) IN ('ASSETS','LIABILITY','EQUITY') THEN 'Balance Sheet'
                    WHEN UPPER(COALESCE(at.category, '')) IN ('REVENUE','EXPENSES') THEN 'Profit & Loss'
                    ELSE 'Review'
                END AS primary_report"),
                \Illuminate\Support\Facades\DB::raw("CASE
                    WHEN UPPER(COALESCE(at.category, '')) IN ('ASSETS','EXPENSES') THEN 'Normal: DEBIT'
                    WHEN UPPER(COALESCE(at.category, '')) IN ('LIABILITY','EQUITY','REVENUE') THEN 'Normal: CREDIT'
                    ELSE 'Review'
                END AS normal_balance"),
                \Illuminate\Support\Facades\DB::raw("CASE
                    WHEN UPPER(COALESCE(at.category, '')) IN ('ASSETS','EXPENSES') THEN 'Increase with DEBIT, decrease with CREDIT'
                    WHEN UPPER(COALESCE(at.category, '')) IN ('LIABILITY','EQUITY','REVENUE') THEN 'Increase with CREDIT, decrease with DEBIT'
                    ELSE 'Review'
                END AS write_effect"),
            ])
            ->orderByRaw("COALESCE(at.category, '') ASC")
            ->orderByRaw("CAST(COALESCE(coa.account_number, '0') AS UNSIGNED) ASC")
            ->paginate(15, ['*'], 'coa_page')
            ->appends($request->query());

        $journalImpactQuery = \Illuminate\Support\Facades\DB::table('journal_entry_line_fields as jef')
            ->join('journal_entries as je', 'je.id', '=', 'jef.journal_entry_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'jef.chart_of_account_id')
            ->leftJoin('account_types as at', 'at.id', '=', 'coa.account_type')
            ->whereNull('jef.deleted_at')
            ->whereNull('je.deleted_at')
            ->whereNull('coa.deleted_at');

        if ($request->filled('status')) {
            $status = strtoupper((string) $request->status);
            if (in_array($status, ['DRAFT', 'POSTED', 'VOIDED'], true)) {
                $journalImpactQuery->whereRaw('UPPER(COALESCE(je.status, \'\')) = ?', [$status]);
            }
        }

        if ($request->filled('date_from')) {
            $journalImpactQuery->whereDate('je.entry_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $journalImpactQuery->whereDate('je.entry_date', '<=', $request->date_to);
        }

        $journalImpactRows = $journalImpactQuery
            ->select([
                'je.entry_date as posting_date',
                'je.reference_number as je_number',
                'je.status as journal_status',
                'coa.account_number as account_code',
                'coa.account_name',
                'at.account_type',
                'at.category',
                'jef.debit_amount',
                'jef.credit_amount',
                \Illuminate\Support\Facades\DB::raw("CASE
                    WHEN UPPER(COALESCE(at.category, '')) IN ('REVENUE','EXPENSES') THEN 'Profit & Loss'
                    WHEN UPPER(COALESCE(at.category, '')) IN ('ASSETS','LIABILITY','EQUITY') THEN 'Balance Sheet'
                    ELSE 'Review'
                END AS affected_report"),
            ])
            ->orderBy('je.entry_date', 'desc')
            ->orderBy('je.id', 'desc')
            ->orderBy('jef.id', 'asc')
            ->paginate($perPage, ['*'], 'je_page')
            ->appends($request->query());

        return view('backend.pages.reports.accounting_journal_entry_impact', compact('coaImpactRows', 'journalImpactRows'));
    })->name('reports.accounting.journal_entry_impact');

    Route::get('/reports/accounting/statement-of-financial-position', function (\Illuminate\Http\Request $request) {
        $requestedAsAt = trim((string) $request->query('as_at', ''));
        if ($requestedAsAt === '') {
            $asAt = date('Y-m-d');
        } elseif (preg_match('/^\d{4}$/', $requestedAsAt)) {
            $asAt = $requestedAsAt . '-12-31';
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedAsAt)) {
            $asAt = $requestedAsAt;
        } else {
            try {
                $asAt = \Carbon\Carbon::parse($requestedAsAt)->format('Y-m-d');
            } catch (\Throwable $e) {
                $asAt = date('Y-m-d');
            }
        }

        $rows = \Illuminate\Support\Facades\DB::table('journal_entry_line_fields as jef')
            ->join('journal_entries as je', function ($join) use ($asAt) {
                $join->on('je.id', '=', 'jef.journal_entry_id')
                    ->whereNull('je.deleted_at')
                    ->whereDate('je.entry_date', '<=', $asAt)
                    ->whereRaw("UPPER(COALESCE(je.status, '')) = 'POSTED'");
            })
            ->join('chart_of_accounts as coa', function ($join) {
                $join->on('coa.id', '=', 'jef.chart_of_account_id')
                    ->whereNull('coa.deleted_at');
            })
            ->leftJoin('account_types as at', 'at.id', '=', 'coa.account_type')
            ->whereNull('jef.deleted_at')
            ->whereRaw("UPPER(COALESCE(at.category, '')) IN ('ASSETS','LIABILITY','EQUITY')")
            ->groupBy('coa.id', 'coa.account_number', 'coa.account_name', 'at.category', 'at.account_type')
            ->select(
                'coa.id',
                'coa.account_number',
                'coa.account_name',
                'at.category',
                'at.account_type',
                \Illuminate\Support\Facades\DB::raw("SUM(COALESCE(jef.debit_amount,0)) as total_debit"),
                \Illuminate\Support\Facades\DB::raw("SUM(COALESCE(jef.credit_amount,0)) as total_credit")
            )
            ->orderByRaw("COALESCE(at.category, '') ASC")
            ->orderByRaw("CAST(COALESCE(coa.account_number, '0') AS UNSIGNED) ASC")
            ->get()
            ->map(function ($row) {
                $category = strtoupper((string) $row->category);
                $debit = (float) ($row->total_debit ?? 0);
                $credit = (float) ($row->total_credit ?? 0);
                $displayBalance = $category === 'ASSETS'
                    ? ($debit - $credit)
                    : ($credit - $debit);
                $row->display_balance = $displayBalance;
                return $row;
            });

        $groupByType = function ($collection, array $types) {
            return $collection->filter(function ($r) use ($types) {
                return in_array(strtolower((string) $r->account_type), array_map('strtolower', $types), true);
            })->values();
        };

        $assets = $rows->where('category', 'ASSETS')->values();
        $liabilities = $rows->where('category', 'LIABILITY')->values();
        $equity = $rows->where('category', 'EQUITY')->values();

        $currentAssets = $groupByType($assets, ['Current Assets', 'Inventory', 'Prepayment', 'Prepayments']);
        $nonCurrentAssets = $assets->reject(function ($r) {
            return in_array(strtolower((string) $r->account_type), ['current assets', 'inventory', 'prepayment', 'prepayments'], true);
        })->values();

        $currentLiabilities = $groupByType($liabilities, ['Current Liability', 'Current Liabilities']);
        $nonCurrentLiabilities = $liabilities->reject(function ($r) {
            return in_array(strtolower((string) $r->account_type), ['current liability', 'current liabilities'], true);
        })->values();

        $totals = [
            'current_assets' => (float) $currentAssets->sum('display_balance'),
            'non_current_assets' => (float) $nonCurrentAssets->sum('display_balance'),
            'current_liabilities' => (float) $currentLiabilities->sum('display_balance'),
            'non_current_liabilities' => (float) $nonCurrentLiabilities->sum('display_balance'),
            'equity' => (float) $equity->sum('display_balance'),
        ];
        $plStartDate = (string) \Illuminate\Support\Facades\DB::table('journal_entries')
            ->whereNull('deleted_at')
            ->whereRaw("UPPER(COALESCE(status, '')) = 'POSTED'")
            ->min('entry_date');
        if (empty($plStartDate)) {
            $plStartDate = $asAt;
        }
        $plAsAtNetProfit = (float) \Illuminate\Support\Facades\DB::table('journal_entry_line_fields as jef')
            ->join('journal_entries as je', function ($join) use ($plStartDate, $asAt) {
                $join->on('je.id', '=', 'jef.journal_entry_id')
                    ->whereNull('je.deleted_at')
                    ->whereDate('je.entry_date', '>=', $plStartDate)
                    ->whereDate('je.entry_date', '<=', $asAt)
                    ->whereRaw("UPPER(COALESCE(je.status, '')) = 'POSTED'");
            })
            ->join('chart_of_accounts as coa', function ($join) {
                $join->on('coa.id', '=', 'jef.chart_of_account_id')
                    ->whereNull('coa.deleted_at');
            })
            ->leftJoin('account_types as at', 'at.id', '=', 'coa.account_type')
            ->whereNull('jef.deleted_at')
            ->where(function ($q) {
                $q->whereRaw("UPPER(TRIM(COALESCE(at.category, ''))) IN ('REVENUE','INCOME','EXPENSES','EXPENSE')")
                    ->orWhereRaw("UPPER(TRIM(COALESCE(at.account_type, ''))) IN ('REVENUE','SALES','OTHER INCOME','EXPENSE','EXPENSES','DIRECT COSTS','DEPRECIATION','OVERHEAD')");
            })
            ->selectRaw("SUM(CASE WHEN UPPER(TRIM(COALESCE(at.category,''))) IN ('REVENUE','INCOME')
                                OR UPPER(TRIM(COALESCE(at.account_type,''))) IN ('REVENUE','SALES','OTHER INCOME')
                          THEN COALESCE(jef.credit_amount,0) - COALESCE(jef.debit_amount,0) ELSE 0 END) -
                        SUM(CASE WHEN UPPER(TRIM(COALESCE(at.category,''))) IN ('EXPENSES','EXPENSE')
                                OR UPPER(TRIM(COALESCE(at.account_type,''))) IN ('EXPENSE','EXPENSES','DIRECT COSTS','DEPRECIATION','OVERHEAD')
                          THEN COALESCE(jef.debit_amount,0) - COALESCE(jef.credit_amount,0) ELSE 0 END) as net_profit")
            ->value('net_profit') ?: 0;

        $totals['net_profit'] = $plAsAtNetProfit;
        $totals['equity_total'] = $totals['equity'] + $totals['net_profit'];
        $totals['total_assets'] = $totals['current_assets'] + $totals['non_current_assets'];
        $totals['total_liabilities'] = $totals['current_liabilities'] + $totals['non_current_liabilities'];
        $totals['total_liabilities_equity'] = $totals['total_liabilities'] + $totals['equity_total'];
        $totals['difference'] = $totals['total_assets'] - $totals['total_liabilities_equity'];
        $apControl = \App\ChartOfAccount::whereNull('deleted_at')
            ->where('system_key', 'ACCOUNTS_PAYABLE_CONTROL')
            ->first();
        $apLedgerBalance = 0;
        if ($apControl) {
            $apRow = $liabilities->firstWhere('id', $apControl->id);
            $apLedgerBalance = $apRow ? (float) $apRow->display_balance : 0;
        }
        $apUnpaidBalance = (float) \App\AccountingBill::whereNull('deleted_at')
            ->where('status', 'AWAITING_PAYMENT')
            ->sum('total_amount');
        $apVariance = $apLedgerBalance - $apUnpaidBalance;
        $fmt = function ($value) {
            $v = (float) $value;
            return $v < 0 ? '(' . number_format(abs($v), 2) . ')' : number_format($v, 2);
        };

        $export = strtolower((string) $request->get('export', ''));
        if (in_array($export, ['pdf', 'excel'], true)) {
            $rowsHtml = '';
            $appendRows = function ($title, $collection) use (&$rowsHtml, $fmt) {
                $rowsHtml .= "<tr><td><strong>{$title}</strong></td><td></td></tr>";
                foreach ($collection as $row) {
                    $rowsHtml .= '<tr><td style="padding-left:18px;">' . e($row->account_name . ' (' . $row->account_number . ')') . '</td><td style="text-align:right;">' . $fmt($row->display_balance) . '</td></tr>';
                }
            };

            $appendRows('CURRENT ASSETS', $currentAssets);
            $rowsHtml .= '<tr><td><strong>TOTAL CURRENT ASSETS</strong></td><td style="text-align:right;"><strong>' . $fmt($totals['current_assets']) . '</strong></td></tr>';
            $appendRows('NON-CURRENT ASSETS', $nonCurrentAssets);
            $rowsHtml .= '<tr><td><strong>TOTAL NON-CURRENT ASSETS</strong></td><td style="text-align:right;"><strong>' . $fmt($totals['non_current_assets']) . '</strong></td></tr>';
            $rowsHtml .= '<tr><td><strong>TOTAL ASSETS</strong></td><td style="text-align:right;"><strong>' . $fmt($totals['total_assets']) . '</strong></td></tr>';
            $appendRows('CURRENT LIABILITIES', $currentLiabilities);
            $rowsHtml .= '<tr><td><strong>TOTAL CURRENT LIABILITIES</strong></td><td style="text-align:right;"><strong>' . $fmt($totals['current_liabilities']) . '</strong></td></tr>';
            $appendRows('NON-CURRENT LIABILITIES', $nonCurrentLiabilities);
            $rowsHtml .= '<tr><td><strong>TOTAL NON-CURRENT LIABILITIES</strong></td><td style="text-align:right;"><strong>' . $fmt($totals['non_current_liabilities']) . '</strong></td></tr>';
            $rowsHtml .= '<tr><td><strong>TOTAL LIABILITIES</strong></td><td style="text-align:right;"><strong>' . $fmt($totals['total_liabilities']) . '</strong></td></tr>';
            $appendRows('EQUITY', $equity);
            $rowsHtml .= '<tr><td><strong>PLUS: NET PROFIT(LOSS)</strong></td><td style="text-align:right;"><strong>' . $fmt($totals['net_profit']) . '</strong></td></tr>';
            $rowsHtml .= '<tr><td><strong>TOTAL EQUITY</strong></td><td style="text-align:right;"><strong>' . $fmt($totals['equity_total']) . '</strong></td></tr>';
            $rowsHtml .= '<tr><td><strong>TOTAL LIABILITIES + EQUITY</strong></td><td style="text-align:right;"><strong>' . $fmt($totals['total_liabilities_equity']) . '</strong></td></tr>';

            $title = 'Statement of Financial Position (As At ' . \Carbon\Carbon::parse($asAt)->format('M d, Y') . ')';
            $html = '<html><head><meta charset="UTF-8"><style>body{font-family:Arial,sans-serif;font-size:12px;padding:20px;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #222;padding:6px;}th{background:#1f4c8f;color:#fff;}</style></head><body>';
            $html .= '<h3>' . e($title) . '</h3><table><thead><tr><th style="text-align:left;">Particulars</th><th style="text-align:right;">Amount</th></tr></thead><tbody>' . $rowsHtml . '</tbody></table>';
            if ($export === 'pdf') {
                $html .= '<script>window.onload=function(){window.print();}</script>';
            }
            $html .= '</body></html>';

            if ($export === 'excel') {
                return response($html, 200, [
                    'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                    'Content-Disposition' => 'attachment; filename="statement_of_financial_position_' . $asAt . '.xls"',
                ]);
            }
            return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
        }

        return view('backend.pages.reports.accounting_statement_of_financial_position', compact(
            'asAt',
            'currentAssets',
            'nonCurrentAssets',
            'currentLiabilities',
            'nonCurrentLiabilities',
            'equity',
            'totals',
            'apLedgerBalance',
            'apUnpaidBalance',
            'apVariance'
        ));
    })->name('reports.accounting.statement_of_financial_position');

    Route::get('/reports/accounting/statement-of-profit-or-loss', function (\Illuminate\Http\Request $request) {
        try {
            $startDate = \Carbon\Carbon::parse((string) $request->get('start_date', date('Y-01-01')))->format('Y-m-d');
        } catch (\Throwable $e) {
            $startDate = date('Y-01-01');
        }
        try {
            $endDate = \Carbon\Carbon::parse((string) $request->get('end_date', date('Y-m-d')))->format('Y-m-d');
        } catch (\Throwable $e) {
            $endDate = date('Y-m-d');
        }
        if ($startDate > $endDate) {
            $tmp = $startDate;
            $startDate = $endDate;
            $endDate = $tmp;
        }

        $rows = \Illuminate\Support\Facades\DB::table('journal_entry_line_fields as jef')
            ->join('journal_entries as je', 'je.id', '=', 'jef.journal_entry_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'jef.chart_of_account_id')
            ->leftJoin('account_types as at', 'at.id', '=', 'coa.account_type')
            ->whereNull('jef.deleted_at')
            ->whereNull('je.deleted_at')
            ->whereNull('coa.deleted_at')
            ->whereDate('je.entry_date', '>=', $startDate)
            ->whereDate('je.entry_date', '<=', $endDate)
            ->whereRaw("UPPER(COALESCE(je.status, '')) = 'POSTED'")
            ->where(function ($q) {
                $q->whereRaw("UPPER(TRIM(COALESCE(at.category, ''))) IN ('REVENUE','INCOME','EXPENSES','EXPENSE')")
                    ->orWhereRaw("UPPER(TRIM(COALESCE(at.account_type, ''))) IN ('REVENUE','SALES','OTHER INCOME','EXPENSE','EXPENSES','DIRECT COSTS','DEPRECIATION','OVERHEAD')");
            })
            ->groupBy('coa.id', 'coa.account_number', 'coa.account_name', 'at.category', 'at.account_type')
            ->select(
                'coa.id',
                'coa.account_number',
                'coa.account_name',
                'at.category',
                'at.account_type',
                \Illuminate\Support\Facades\DB::raw('SUM(COALESCE(jef.debit_amount, 0)) as total_debit'),
                \Illuminate\Support\Facades\DB::raw('SUM(COALESCE(jef.credit_amount, 0)) as total_credit')
            )
            ->orderByRaw("COALESCE(at.category, '') ASC")
            ->orderByRaw("CAST(COALESCE(coa.account_number, '0') AS UNSIGNED) ASC")
            ->get()
            ->map(function ($row) {
                $rawCategory = strtoupper(trim((string) $row->category));
                $rawType = strtoupper(trim((string) $row->account_type));
                $isRevenue = in_array($rawCategory, ['REVENUE', 'INCOME'], true) || in_array($rawType, ['REVENUE', 'SALES', 'OTHER INCOME'], true);
                $category = $isRevenue ? 'REVENUE' : 'EXPENSES';
                $debit = (float) ($row->total_debit ?? 0);
                $credit = (float) ($row->total_credit ?? 0);
                $amount = $category === 'REVENUE'
                    ? ($credit - $debit)
                    : ($debit - $credit);
                $row->category = $category;
                $row->amount = $amount;
                return $row;
            });

        $revenueRows = $rows->where('category', 'REVENUE')->values();
        $expenseRows = $rows->where('category', 'EXPENSES')->values();

        $costOfSalesRows = $expenseRows->filter(function ($row) {
            return strtolower((string) $row->account_type) === 'direct costs';
        })->values();

        $operatingExpenseRows = $expenseRows->reject(function ($row) {
            return strtolower((string) $row->account_type) === 'direct costs';
        })->values();

        $totals = [
            'revenue' => (float) $revenueRows->sum('amount'),
            'cost_of_sales' => (float) $costOfSalesRows->sum('amount'),
            'operating_expenses' => (float) $operatingExpenseRows->sum('amount'),
        ];
        $totals['gross_profit'] = $totals['revenue'] - $totals['cost_of_sales'];
        $totals['net_profit'] = $totals['gross_profit'] - $totals['operating_expenses'];
        $fmt = function ($value) {
            $v = (float) $value;
            return $v < 0 ? '(' . number_format(abs($v), 2) . ')' : number_format($v, 2);
        };

        $export = strtolower((string) $request->get('export', ''));
        if (in_array($export, ['pdf', 'excel'], true)) {
            $rowsHtml = '<tr><td><strong>REVENUE</strong></td><td></td></tr>';
            foreach ($revenueRows as $row) {
                $rowsHtml .= '<tr><td style="padding-left:18px;">' . e($row->account_name . ' (' . $row->account_number . ')') . '</td><td style="text-align:right;">' . $fmt($row->amount) . '</td></tr>';
            }
            $rowsHtml .= '<tr><td><strong>TOTAL REVENUE</strong></td><td style="text-align:right;"><strong>' . $fmt($totals['revenue']) . '</strong></td></tr>';
            $rowsHtml .= '<tr><td><strong>LESS: COST OF SALES</strong></td><td></td></tr>';
            foreach ($costOfSalesRows as $row) {
                $rowsHtml .= '<tr><td style="padding-left:18px;">' . e($row->account_name . ' (' . $row->account_number . ')') . '</td><td style="text-align:right;">' . $fmt($row->amount) . '</td></tr>';
            }
            $rowsHtml .= '<tr><td><strong>TOTAL COST OF SALES</strong></td><td style="text-align:right;"><strong>' . $fmt($totals['cost_of_sales']) . '</strong></td></tr>';
            $rowsHtml .= '<tr><td><strong>GROSS PROFIT</strong></td><td style="text-align:right;"><strong>' . $fmt($totals['gross_profit']) . '</strong></td></tr>';
            $rowsHtml .= '<tr><td><strong>LESS: OPERATING EXPENSES</strong></td><td></td></tr>';
            foreach ($operatingExpenseRows as $row) {
                $rowsHtml .= '<tr><td style="padding-left:18px;">' . e($row->account_name . ' (' . $row->account_number . ')') . '</td><td style="text-align:right;">' . $fmt($row->amount) . '</td></tr>';
            }
            $rowsHtml .= '<tr><td><strong>TOTAL OPERATING EXPENSES</strong></td><td style="text-align:right;"><strong>' . $fmt($totals['operating_expenses']) . '</strong></td></tr>';
            $rowsHtml .= '<tr><td><strong>NET PROFIT(LOSS)</strong></td><td style="text-align:right;"><strong>' . $fmt($totals['net_profit']) . '</strong></td></tr>';

            $title = 'Statement of Profit or Loss (' . \Carbon\Carbon::parse($startDate)->format('M d, Y') . ' - ' . \Carbon\Carbon::parse($endDate)->format('M d, Y') . ')';
            $html = '<html><head><meta charset="UTF-8"><style>body{font-family:Arial,sans-serif;font-size:12px;padding:20px;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #222;padding:6px;}th{background:#1f4c8f;color:#fff;}</style></head><body>';
            $html .= '<h3>' . e($title) . '</h3><table><thead><tr><th style="text-align:left;">Particulars</th><th style="text-align:right;">Amount</th></tr></thead><tbody>' . $rowsHtml . '</tbody></table>';
            if ($export === 'pdf') {
                $html .= '<script>window.onload=function(){window.print();}</script>';
            }
            $html .= '</body></html>';

            if ($export === 'excel') {
                return response($html, 200, [
                    'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                    'Content-Disposition' => 'attachment; filename="statement_of_profit_or_loss_' . $startDate . '_to_' . $endDate . '.xls"',
                ]);
            }
            return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
        }

        return view('backend.pages.reports.accounting_statement_of_profit_or_loss', compact(
            'startDate',
            'endDate',
            'revenueRows',
            'costOfSalesRows',
            'operatingExpenseRows',
            'totals'
        ));
    })->name('reports.accounting.statement_of_profit_or_loss');

    Route::get('/reports/accounting/statement-of-changes-in-equity', function (\Illuminate\Http\Request $request) {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-t'));
        $openingDate = \Carbon\Carbon::parse($startDate)->subDay()->format('Y-m-d');

        $equityAsAt = function ($asAt) {
            return (float) \Illuminate\Support\Facades\DB::table('journal_entry_line_fields as jef')
                ->join('journal_entries as je', 'je.id', '=', 'jef.journal_entry_id')
                ->join('chart_of_accounts as coa', 'coa.id', '=', 'jef.chart_of_account_id')
                ->leftJoin('account_types as at', 'at.id', '=', 'coa.account_type')
                ->whereNull('jef.deleted_at')
                ->whereNull('je.deleted_at')
                ->whereNull('coa.deleted_at')
                ->whereRaw("UPPER(COALESCE(je.status, '')) = 'POSTED'")
                ->whereDate('je.entry_date', '<=', $asAt)
                ->whereRaw("UPPER(COALESCE(at.category, '')) = 'EQUITY'")
                ->selectRaw('SUM(COALESCE(jef.credit_amount,0) - COALESCE(jef.debit_amount,0)) as equity_total')
                ->value('equity_total') ?: 0;
        };

        $equityMovementRows = \Illuminate\Support\Facades\DB::table('journal_entry_line_fields as jef')
            ->join('journal_entries as je', 'je.id', '=', 'jef.journal_entry_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'jef.chart_of_account_id')
            ->leftJoin('account_types as at', 'at.id', '=', 'coa.account_type')
            ->whereNull('jef.deleted_at')
            ->whereNull('je.deleted_at')
            ->whereNull('coa.deleted_at')
            ->whereRaw("UPPER(COALESCE(je.status, '')) = 'POSTED'")
            ->whereDate('je.entry_date', '>=', $startDate)
            ->whereDate('je.entry_date', '<=', $endDate)
            ->whereRaw("UPPER(COALESCE(at.category, '')) = 'EQUITY'")
            ->groupBy('coa.id', 'coa.account_number', 'coa.account_name')
            ->select(
                'coa.account_number',
                'coa.account_name',
                \Illuminate\Support\Facades\DB::raw('SUM(COALESCE(jef.credit_amount,0) - COALESCE(jef.debit_amount,0)) as movement')
            )
            ->orderByRaw("CAST(COALESCE(coa.account_number, '0') AS UNSIGNED) ASC")
            ->get();

        $netProfit = (float) \Illuminate\Support\Facades\DB::table('journal_entry_line_fields as jef')
            ->join('journal_entries as je', 'je.id', '=', 'jef.journal_entry_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'jef.chart_of_account_id')
            ->leftJoin('account_types as at', 'at.id', '=', 'coa.account_type')
            ->whereNull('jef.deleted_at')
            ->whereNull('je.deleted_at')
            ->whereNull('coa.deleted_at')
            ->whereRaw("UPPER(COALESCE(je.status, '')) = 'POSTED'")
            ->whereDate('je.entry_date', '>=', $startDate)
            ->whereDate('je.entry_date', '<=', $endDate)
            ->selectRaw("SUM(CASE WHEN UPPER(COALESCE(at.category,'')) = 'REVENUE' THEN COALESCE(jef.credit_amount,0) - COALESCE(jef.debit_amount,0) ELSE 0 END) -
                        SUM(CASE WHEN UPPER(COALESCE(at.category,'')) = 'EXPENSES' THEN COALESCE(jef.debit_amount,0) - COALESCE(jef.credit_amount,0) ELSE 0 END) as net_profit")
            ->value('net_profit') ?: 0;

        $openingEquity = $equityAsAt($openingDate);
        $periodEquityMovement = (float) $equityMovementRows->sum('movement');
        $ownerContributions = (float) $equityMovementRows->filter(function ($row) {
            $name = strtolower((string) $row->account_name);
            return strpos($name, 'capital') !== false || strpos($name, 'contribution') !== false;
        })->sum('movement');
        $drawings = (float) $equityMovementRows->filter(function ($row) {
            $name = strtolower((string) $row->account_name);
            return strpos($name, 'drawing') !== false || strpos($name, 'withdraw') !== false;
        })->sum('movement');

        $closingEquity = $openingEquity + $periodEquityMovement + $netProfit;
        $fmt = function ($value) {
            $v = (float) $value;
            return $v < 0 ? '(' . number_format(abs($v), 2) . ')' : number_format($v, 2);
        };

        $export = strtolower((string) $request->get('export', ''));
        if (in_array($export, ['pdf', 'excel'], true)) {
            $rowsHtml = '';
            $rowsHtml .= '<tr><td>Opening Equity (as at ' . e(\Carbon\Carbon::parse($openingDate)->format('M d, Y')) . ')</td><td style="text-align:right;">' . $fmt($openingEquity) . '</td></tr>';
            $rowsHtml .= '<tr><td>Add: Owner Contributions</td><td style="text-align:right;">' . $fmt($ownerContributions) . '</td></tr>';
            $rowsHtml .= '<tr><td>Add: Net Profit (Loss)</td><td style="text-align:right;">' . $fmt($netProfit) . '</td></tr>';
            $rowsHtml .= '<tr><td>Less: Drawings / Distributions</td><td style="text-align:right;">' . $fmt($drawings) . '</td></tr>';
            $rowsHtml .= '<tr><td>Other Equity Movements</td><td style="text-align:right;">' . $fmt($periodEquityMovement) . '</td></tr>';
            $rowsHtml .= '<tr><td><strong>CLOSING EQUITY</strong></td><td style="text-align:right;"><strong>' . $fmt($closingEquity) . '</strong></td></tr>';
            $rowsHtml .= '<tr><td colspan="2"><strong>Equity Movement Breakdown</strong></td></tr>';
            foreach ($equityMovementRows as $row) {
                $rowsHtml .= '<tr><td style="padding-left:18px;">' . e($row->account_name . ' (' . $row->account_number . ')') . '</td><td style="text-align:right;">' . $fmt($row->movement) . '</td></tr>';
            }

            $title = 'Statement of Changes in Equity (' . \Carbon\Carbon::parse($startDate)->format('M d, Y') . ' - ' . \Carbon\Carbon::parse($endDate)->format('M d, Y') . ')';
            $html = '<html><head><meta charset="UTF-8"><style>body{font-family:Arial,sans-serif;font-size:12px;padding:20px;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #222;padding:6px;}th{background:#1f4c8f;color:#fff;}</style></head><body>';
            $html .= '<h3>' . e($title) . '</h3><table><thead><tr><th style="text-align:left;">Particulars</th><th style="text-align:right;">Amount</th></tr></thead><tbody>' . $rowsHtml . '</tbody></table>';
            if ($export === 'pdf') {
                $html .= '<script>window.onload=function(){window.print();}</script>';
            }
            $html .= '</body></html>';

            if ($export === 'excel') {
                return response($html, 200, [
                    'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                    'Content-Disposition' => 'attachment; filename="statement_of_changes_in_equity_' . $startDate . '_to_' . $endDate . '.xls"',
                ]);
            }
            return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
        }

        return view('backend.pages.reports.accounting_statement_of_changes_in_equity', compact(
            'startDate',
            'endDate',
            'openingDate',
            'equityMovementRows',
            'openingEquity',
            'ownerContributions',
            'drawings',
            'netProfit',
            'periodEquityMovement',
            'closingEquity'
        ));
    })->name('reports.accounting.statement_of_changes_in_equity');

    Route::get('/reports/accounting/statement-of-cash-flows', function (\Illuminate\Http\Request $request) {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-t'));
        $openingDate = \Carbon\Carbon::parse($startDate)->subDay()->format('Y-m-d');

        $cashBalanceAsAt = function ($asAt) {
            return (float) \Illuminate\Support\Facades\DB::table('journal_entry_line_fields as jef')
                ->join('journal_entries as je', 'je.id', '=', 'jef.journal_entry_id')
                ->join('chart_of_accounts as coa', 'coa.id', '=', 'jef.chart_of_account_id')
                ->leftJoin('account_types as at', 'at.id', '=', 'coa.account_type')
                ->whereNull('jef.deleted_at')
                ->whereNull('je.deleted_at')
                ->whereNull('coa.deleted_at')
                ->whereRaw("UPPER(COALESCE(je.status, '')) = 'POSTED'")
                ->whereDate('je.entry_date', '<=', $asAt)
                ->whereRaw("UPPER(COALESCE(at.category, '')) = 'ASSETS'")
                ->where(function ($q) {
                    $q->whereRaw("LOWER(COALESCE(coa.account_name, '')) LIKE '%cash%'")
                        ->orWhereRaw("LOWER(COALESCE(coa.account_name, '')) LIKE '%bank%'");
                })
                ->selectRaw('SUM(COALESCE(jef.debit_amount,0) - COALESCE(jef.credit_amount,0)) as cash_balance')
                ->value('cash_balance') ?: 0;
        };

        $openingCash = $cashBalanceAsAt($openingDate);
        $closingCash = $cashBalanceAsAt($endDate);
        $netCashChange = $closingCash - $openingCash;

        $netProfit = (float) \Illuminate\Support\Facades\DB::table('journal_entry_line_fields as jef')
            ->join('journal_entries as je', 'je.id', '=', 'jef.journal_entry_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'jef.chart_of_account_id')
            ->leftJoin('account_types as at', 'at.id', '=', 'coa.account_type')
            ->whereNull('jef.deleted_at')
            ->whereNull('je.deleted_at')
            ->whereNull('coa.deleted_at')
            ->whereRaw("UPPER(COALESCE(je.status, '')) = 'POSTED'")
            ->whereDate('je.entry_date', '>=', $startDate)
            ->whereDate('je.entry_date', '<=', $endDate)
            ->selectRaw("SUM(CASE WHEN UPPER(COALESCE(at.category,'')) = 'REVENUE' THEN COALESCE(jef.credit_amount,0) - COALESCE(jef.debit_amount,0) ELSE 0 END) -
                        SUM(CASE WHEN UPPER(COALESCE(at.category,'')) = 'EXPENSES' THEN COALESCE(jef.debit_amount,0) - COALESCE(jef.credit_amount,0) ELSE 0 END) as net_profit")
            ->value('net_profit') ?: 0;

        $depreciation = (float) \Illuminate\Support\Facades\DB::table('journal_entry_line_fields as jef')
            ->join('journal_entries as je', 'je.id', '=', 'jef.journal_entry_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'jef.chart_of_account_id')
            ->leftJoin('account_types as at', 'at.id', '=', 'coa.account_type')
            ->whereNull('jef.deleted_at')
            ->whereNull('je.deleted_at')
            ->whereNull('coa.deleted_at')
            ->whereRaw("UPPER(COALESCE(je.status, '')) = 'POSTED'")
            ->whereDate('je.entry_date', '>=', $startDate)
            ->whereDate('je.entry_date', '<=', $endDate)
            ->whereRaw("UPPER(COALESCE(at.category,'')) = 'EXPENSES'")
            ->whereRaw("LOWER(COALESCE(coa.account_name, '')) LIKE '%depreciation%'")
            ->selectRaw('SUM(COALESCE(jef.debit_amount,0) - COALESCE(jef.credit_amount,0)) as depreciation_total')
            ->value('depreciation_total') ?: 0;

        $investingActivities = (float) \Illuminate\Support\Facades\DB::table('journal_entry_line_fields as jef')
            ->join('journal_entries as je', 'je.id', '=', 'jef.journal_entry_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'jef.chart_of_account_id')
            ->leftJoin('account_types as at', 'at.id', '=', 'coa.account_type')
            ->whereNull('jef.deleted_at')
            ->whereNull('je.deleted_at')
            ->whereNull('coa.deleted_at')
            ->whereRaw("UPPER(COALESCE(je.status, '')) = 'POSTED'")
            ->whereDate('je.entry_date', '>=', $startDate)
            ->whereDate('je.entry_date', '<=', $endDate)
            ->whereRaw("UPPER(COALESCE(at.category,'')) = 'ASSETS'")
            ->whereRaw("LOWER(COALESCE(at.account_type,'')) IN ('fixed assets','non-current assets')")
            ->selectRaw('SUM((COALESCE(jef.debit_amount,0) - COALESCE(jef.credit_amount,0)) * -1) as investing_total')
            ->value('investing_total') ?: 0;

        $financingActivities = (float) \Illuminate\Support\Facades\DB::table('journal_entry_line_fields as jef')
            ->join('journal_entries as je', 'je.id', '=', 'jef.journal_entry_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'jef.chart_of_account_id')
            ->leftJoin('account_types as at', 'at.id', '=', 'coa.account_type')
            ->whereNull('jef.deleted_at')
            ->whereNull('je.deleted_at')
            ->whereNull('coa.deleted_at')
            ->whereRaw("UPPER(COALESCE(je.status, '')) = 'POSTED'")
            ->whereDate('je.entry_date', '>=', $startDate)
            ->whereDate('je.entry_date', '<=', $endDate)
            ->whereRaw("UPPER(COALESCE(at.category,'')) IN ('LIABILITY','EQUITY')")
            ->selectRaw('SUM(COALESCE(jef.credit_amount,0) - COALESCE(jef.debit_amount,0)) as financing_total')
            ->value('financing_total') ?: 0;

        $operatingActivities = $netProfit + $depreciation;
        $fmt = function ($value) {
            $v = (float) $value;
            return $v < 0 ? '(' . number_format(abs($v), 2) . ')' : number_format($v, 2);
        };

        $export = strtolower((string) $request->get('export', ''));
        if (in_array($export, ['pdf', 'excel'], true)) {
            $rowsHtml = '';
            $rowsHtml .= '<tr><td><strong>CASH FLOWS FROM OPERATING ACTIVITIES (INDIRECT)</strong></td><td></td></tr>';
            $rowsHtml .= '<tr><td>Net Profit (Loss)</td><td style="text-align:right;">' . $fmt($netProfit) . '</td></tr>';
            $rowsHtml .= '<tr><td>Add: Non-cash Adjustment (Depreciation)</td><td style="text-align:right;">' . $fmt($depreciation) . '</td></tr>';
            $rowsHtml .= '<tr><td><strong>NET CASH FROM OPERATING ACTIVITIES</strong></td><td style="text-align:right;"><strong>' . $fmt($operatingActivities) . '</strong></td></tr>';
            $rowsHtml .= '<tr><td><strong>CASH FLOWS FROM INVESTING ACTIVITIES</strong></td><td style="text-align:right;"><strong>' . $fmt($investingActivities) . '</strong></td></tr>';
            $rowsHtml .= '<tr><td><strong>CASH FLOWS FROM FINANCING ACTIVITIES</strong></td><td style="text-align:right;"><strong>' . $fmt($financingActivities) . '</strong></td></tr>';
            $rowsHtml .= '<tr><td><strong>NET INCREASE / (DECREASE) IN CASH</strong></td><td style="text-align:right;"><strong>' . $fmt($netCashChange) . '</strong></td></tr>';
            $rowsHtml .= '<tr><td>Opening Cash (as at ' . e(\Carbon\Carbon::parse($openingDate)->format('M d, Y')) . ')</td><td style="text-align:right;">' . $fmt($openingCash) . '</td></tr>';
            $rowsHtml .= '<tr><td><strong>CLOSING CASH</strong></td><td style="text-align:right;"><strong>' . $fmt($closingCash) . '</strong></td></tr>';

            $title = 'Statement of Cash Flows (' . \Carbon\Carbon::parse($startDate)->format('M d, Y') . ' - ' . \Carbon\Carbon::parse($endDate)->format('M d, Y') . ')';
            $html = '<html><head><meta charset="UTF-8"><style>body{font-family:Arial,sans-serif;font-size:12px;padding:20px;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #222;padding:6px;}th{background:#1f4c8f;color:#fff;}</style></head><body>';
            $html .= '<h3>' . e($title) . '</h3><table><thead><tr><th style="text-align:left;">Particulars</th><th style="text-align:right;">Amount</th></tr></thead><tbody>' . $rowsHtml . '</tbody></table>';
            if ($export === 'pdf') {
                $html .= '<script>window.onload=function(){window.print();}</script>';
            }
            $html .= '</body></html>';

            if ($export === 'excel') {
                return response($html, 200, [
                    'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                    'Content-Disposition' => 'attachment; filename="statement_of_cash_flows_' . $startDate . '_to_' . $endDate . '.xls"',
                ]);
            }
            return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
        }

        return view('backend.pages.reports.accounting_statement_of_cash_flows', compact(
            'startDate',
            'endDate',
            'openingDate',
            'openingCash',
            'closingCash',
            'netCashChange',
            'netProfit',
            'depreciation',
            'operatingActivities',
            'investingActivities',
            'financingActivities'
        ));
    })->name('reports.accounting.statement_of_cash_flows');

    Route::get('/reports/accounting/drilldown', function (\Illuminate\Http\Request $request) {
        $report = strtolower((string) $request->get('report', 'pl'));
        $from = $request->get('from');
        $to = $request->get('to');
        $asAt = $request->get('as_at');
        $category = strtoupper((string) $request->get('category', ''));
        $accountId = (int) $request->get('account_id', 0);
        $section = strtolower((string) $request->get('section', ''));

        $query = \Illuminate\Support\Facades\DB::table('journal_entry_line_fields as jef')
            ->join('journal_entries as je', 'je.id', '=', 'jef.journal_entry_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'jef.chart_of_account_id')
            ->leftJoin('account_types as at', 'at.id', '=', 'coa.account_type')
            ->whereNull('jef.deleted_at')
            ->whereNull('je.deleted_at')
            ->whereNull('coa.deleted_at')
            ->whereRaw("UPPER(COALESCE(je.status, '')) = 'POSTED'");

        if ($report === 'bs') {
            if (!empty($asAt)) {
                $query->whereDate('je.entry_date', '<=', $asAt);
            }
        } else {
            if (!empty($from)) {
                $query->whereDate('je.entry_date', '>=', $from);
            }
            if (!empty($to)) {
                $query->whereDate('je.entry_date', '<=', $to);
            }
        }

        if (!empty($category)) {
            $query->whereRaw("UPPER(COALESCE(at.category, '')) = ?", [$category]);
        }
        if ($accountId > 0) {
            $query->where('coa.id', $accountId);
        }

        if ($section === 'current_assets') {
            $query->whereRaw("UPPER(COALESCE(at.category,'')) = 'ASSETS'")
                ->whereRaw("LOWER(COALESCE(at.account_type,'')) IN ('current assets','inventory','prepayment','prepayments')");
        } elseif ($section === 'non_current_assets') {
            $query->whereRaw("UPPER(COALESCE(at.category,'')) = 'ASSETS'")
                ->whereRaw("LOWER(COALESCE(at.account_type,'')) NOT IN ('current assets','inventory','prepayment','prepayments')");
        } elseif ($section === 'current_liabilities') {
            $query->whereRaw("UPPER(COALESCE(at.category,'')) = 'LIABILITY'")
                ->whereRaw("LOWER(COALESCE(at.account_type,'')) IN ('current liability','current liabilities')");
        } elseif ($section === 'non_current_liabilities') {
            $query->whereRaw("UPPER(COALESCE(at.category,'')) = 'LIABILITY'")
                ->whereRaw("LOWER(COALESCE(at.account_type,'')) NOT IN ('current liability','current liabilities')");
        } elseif ($section === 'cost_of_sales') {
            $query->whereRaw("UPPER(COALESCE(at.category,'')) = 'EXPENSES'")
                ->whereRaw("LOWER(COALESCE(at.account_type,'')) = 'direct costs'");
        } elseif ($section === 'operating_expenses') {
            $query->whereRaw("UPPER(COALESCE(at.category,'')) = 'EXPENSES'")
                ->whereRaw("LOWER(COALESCE(at.account_type,'')) <> 'direct costs'");
        } elseif ($section === 'cash_accounts') {
            $query->whereRaw("UPPER(COALESCE(at.category,'')) = 'ASSETS'")
                ->where(function ($q) {
                    $q->whereRaw("LOWER(COALESCE(coa.account_name,'')) LIKE '%cash%'")
                        ->orWhereRaw("LOWER(COALESCE(coa.account_name,'')) LIKE '%bank%'");
                });
        }

        $rows = $query->select(
                'je.id as journal_entry_id',
                'je.reference_number',
                'je.entry_date',
                'jef.id as line_id',
                'jef.description as line_description',
                'jef.data_type',
                'jef.data_id',
                'coa.id as account_id',
                'coa.account_number',
                'coa.account_name',
                'at.category',
                'at.account_type',
                \Illuminate\Support\Facades\DB::raw('COALESCE(jef.debit_amount,0) as debit_amount'),
                \Illuminate\Support\Facades\DB::raw('COALESCE(jef.credit_amount,0) as credit_amount')
            )
            ->orderBy('je.entry_date', 'desc')
            ->orderBy('je.id', 'desc')
            ->orderBy('jef.id', 'asc')
            ->paginate(25)
            ->appends($request->query());

        $rows->getCollection()->transform(function ($row) use ($report) {
            $category = strtoupper((string) $row->category);
            $debit = (float) $row->debit_amount;
            $credit = (float) $row->credit_amount;

            if ($report === 'pl') {
                $row->impact_amount = $category === 'REVENUE' ? ($credit - $debit) : ($debit - $credit);
            } elseif ($report === 'bs') {
                $row->impact_amount = $category === 'ASSETS' ? ($debit - $credit) : ($credit - $debit);
            } else {
                $row->impact_amount = $debit - $credit;
            }
            return $row;
        });

        $totals = [
            'debit' => (float) $rows->getCollection()->sum('debit_amount'),
            'credit' => (float) $rows->getCollection()->sum('credit_amount'),
            'impact' => (float) $rows->getCollection()->sum('impact_amount'),
        ];

        return view('backend.pages.reports.accounting_drilldown', compact('rows', 'totals', 'report', 'from', 'to', 'asAt', 'category', 'section'));
    })->name('reports.accounting.drilldown');

    Route::get('/reports/accounting/journal-entry/{id}', function ($id) {
        $entry = \App\JournalEntry::whereNull('deleted_at')->findOrFail($id);
        $lines = \App\JournalEntryLineField::with('chart_of_account')
            ->whereNull('deleted_at')
            ->where('journal_entry_id', $id)
            ->orderBy('id', 'asc')
            ->get();

        return view('backend.pages.reports.accounting_journal_entry_detail', compact('entry', 'lines'));
    })->name('reports.accounting.journal_entry_detail');

    Route::get('/reports/accounting/source-document/{type}/{id}', function ($type, $id) {
        if (strtoupper((string) $type) === 'BILL') {
            $bill = \App\AccountingBill::whereNull('deleted_at')->find($id);
            if ($bill) {
                return redirect('/accounting/bills/show/' . $bill->id);
            }
        }

        $lines = \App\JournalEntryLineField::with(['journal_entry', 'chart_of_account'])
            ->whereNull('deleted_at')
            ->where('data_type', $type)
            ->where('data_id', $id)
            ->orderBy('journal_entry_id', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        return view('backend.pages.reports.accounting_source_document_detail', compact('lines', 'type', 'id'));
    })->name('reports.accounting.source_document_detail');

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
        $employeeOptions = \App\EmployeeInformation::select('id', 'employee_no', 'firstname', 'middlename', 'lastname', 'suffix')
            ->orderBy('lastname', 'asc')
            ->orderBy('firstname', 'asc')
            ->orderBy('middlename', 'asc')
            ->orderBy('suffix', 'asc')
            ->get();
        $leaveTypeOptions = \App\LeaveType::orderBy('leave_name', 'asc')->get();

        $leaveQuery = \App\Leaves::with(['leave_types', 'employee'])
            ->orderBy('employee_id', 'asc')
            ->orderBy('leave_type', 'asc');

        if ($request->filled('employee_id')) {
            $employeeId = (int) $request->employee_id;
            if ($employeeId > 0) {
                $leaveQuery->where('employee_id', $employeeId);
            }
        }

        if ($request->filled('leave_type_id')) {
            $leaveTypeId = (int) $request->leave_type_id;
            if ($leaveTypeId > 0) {
                $leaveQuery->where('leave_type', $leaveTypeId);
            }
        }

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

        return view('backend.pages.reports.hr_leave_balance', compact('leaveRows', 'employeeOptions', 'leaveTypeOptions'));
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

        Route::group(['prefix' => '/financial_settings'], function (){
            Route::get          ('/',                            'AccountingFinancialSettingController@index'                        )->name('accounting_financial_settings');
            Route::post         ('/save',                        'AccountingFinancialSettingController@save'                         )->name('accounting_financial_settings_save');
        });

        Route::group(['prefix' => '/bills'], function (){
            Route::get          ('/',                            'AccountingBillController@index'                                    )->name('accounting_bills');
            Route::get          ('/suppliers/new',              'AccountingBillController@createSupplier'                           )->name('accounting_bills_supplier_new');
            Route::post         ('/suppliers/save',             'AccountingBillController@saveSupplier'                             )->name('accounting_bills_supplier_save');
            Route::post         ('/save',                        'AccountingBillController@save'                                     )->name('accounting_bills_save');
            Route::post         ('/submit/{id}',                 'AccountingBillController@submit'                                   )->name('accounting_bills_submit');
            Route::post         ('/destroy/{id}',                'AccountingBillController@destroy'                                  )->name('accounting_bills_destroy');
            Route::post         ('/approve/{id}',                'AccountingBillController@approve'                                  )->name('accounting_bills_approve');
            Route::post         ('/reject/{id}',                 'AccountingBillController@reject'                                   )->name('accounting_bills_reject');
            Route::post         ('/pay/{id}',                    'AccountingBillController@pay'                                      )->name('accounting_bills_pay');
            Route::post         ('/notes/{id}',                  'AccountingBillController@addNote'                                  )->name('accounting_bills_add_note');
            Route::get          ('/show/{id}',                   'AccountingBillController@show'                                     )->name('accounting_bills_show');
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
            Route::post         ('/submit-for-audit',                 'PayrunController@submitForAudit'                       )->name('payrun_submit_for_audit');
            Route::post         ('/submit-for-approval',              'PayrunController@submitForApproval'                    )->name('payrun_submit_for_approval');
            Route::post         ('/approve-summary',                  'PayrunController@approveSummary'                       )->name('payrun_approve_summary');
            Route::post         ('/revert-summary',                   'PayrunController@revertSummary'                        )->name('payrun_revert_summary');
            Route::post         ('/submit-for-payment',               'PayrunController@submitForPayment'                     )->name('payrun_submit_for_payment');
            Route::get          ('/history-notes/{id}',               'PayrunController@getHistoryNotes'                      )->name('payrun_history_notes');
            Route::post         ('/add-note',                         'PayrunController@addNote'                              )->name('payrun_add_note');
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

        Route::group(['prefix' => 'notification-setup', 'middleware' => ['auth']], function () {
            Route::get('/', 'NotificationSetupController@index')->name('notification_setup.index');
            Route::post('/save', 'NotificationSetupController@store')->name('notification_setup.store');
            Route::delete('/destroy/{id}', 'NotificationSetupController@destroy')->name('notification_setup.destroy');
            Route::post('/purchase-order-rule/save', 'NotificationSetupController@storePurchaseOrderRule')->name('notification_setup.po_rule.store');
            Route::delete('/purchase-order-rule/destroy/{id}', 'NotificationSetupController@destroyPurchaseOrderRule')->name('notification_setup.po_rule.destroy');
            Route::post('/example-send-by-roles', 'NotificationSetupController@sendExampleByRoles')->name('notification_setup.example_send_by_roles');
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
Route::group(['prefix' => 'notifications', 'middleware' => ['auth']], function () {
    Route::get('/', 'NotificationsController@index')->name('notifications.index');
    Route::get('/read/{id}', 'NotificationsController@markAsRead')->name('notifications.read');
    Route::get('/read-all', 'NotificationsController@markAllAsRead')->name('notifications.read_all');
    Route::get('/my', 'NotificationSetupController@myNotifications')->name('notifications.my');
});

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
