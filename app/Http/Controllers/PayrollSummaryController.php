<?php

namespace App\Http\Controllers;

use Auth;
use View;
use DateTime;
use DatePeriod;
use DateInterval;
use App\Employment;
use App\EmployeeInformation;
use App\Compensations;
use App\Earnings;
use App\Deductions;
use App\SSS;
use App\WithholdingTax;
use App\PayrollSummary;
use App\PayrollSummaryDetails;
use App\PayrollCalendar;
use App\EarningsTransaction;
use App\DeductionsTransaction;
use App\TimeLogs;
use App\TimeLogApprovals;
use Illuminate\Http\Request;

use App\Classes\Payroll\PayrollSummary as Summary;
use App\Classes\Computation\Payroll\SSS as SSS_Benefits;
use App\Classes\Computation\Payroll\WithholdingTax as WithholdingTax_Benefits;
use App\Classes\Computation\Payroll\Earnings as EarningsTotal;
use App\Classes\Computation\Payroll\Deductions as DeductionsTotal;
use App\Classes\Computation\Payroll\Pagibig;
use App\Classes\Computation\Payroll\Philhealth;
use App\Classes\Computation\Payroll\General;
use App\Classes\Computation\Payroll\Taxable_Amount;
use App\Classes\Computation\Payroll\NetPay;

use Carbon\Carbon;
use Illuminate\Support\Arr;

class PayrollSummaryController extends Controller
{
    protected $computation, $withholding_tax, $taxable_amount, $netpay, $summary, $sss_benefits, $pagibig_benefits, $philhealth_benefits, $earnings, $deductions;

    public function __construct() 
    {
        $this->computation = new General();
        $this->withholding_tax = new WithholdingTax_Benefits();
        $this->taxable_amount = new Taxable_Amount();
        $this->netpay = new NetPay();
        $this->summary = new Summary();
        $this->sss_benefits = new SSS_Benefits();
        $this->pagibig_benefits = new Pagibig();
        $this->philhealt_benefits = new Philhealth();
        $this->earnings = new EarningsTotal();
        $this->deductions = new DeductionsTotal();
    }

    public function index() {
        return view('backend.pages.transaction.payroll.summary', ["type"=>"full-view"]);
    }
    
    public function payslip() {
        return view('backend.pages.transaction.payroll.payslip');
    }
    
    public function save(Request $request)
    {
        $validate = $request->validate([
            'type' => 'required',
            'amount' => 'required'
        ]);

        if($request->module === "earning") {
            $data = array(
                "employee_id" => $request->employee_id,
                "sequence_no" => $request->sequence_no,
                "earning_id" => $request->type,
                "rate" => "-",
                "hours" => "-",
                "total" => $request->amount,
                "status" => 1,
                "workstation_id" => Auth::user()->workstation_id,
                "created_by" => Auth::user()->id,
                "updated_by" => Auth::user()->id,
            );

            EarningsTransaction::create($data);
            
            $this->update_details($request->sequence_no, $request->employee_id, $request->type);

        }
        else if($request->module === "deduction") {
            $data = array(
                "employee_id" => $request->employee_id,
                "sequence_no" => $request->sequence_no,
                "deduction_id" => $request->type,
                "rate" => "-",
                "hours" => "-",
                "total" => $request->amount,
                "status" => 1,
                "workstation_id" => Auth::user()->workstation_id,
                "created_by" => Auth::user()->id,
                "updated_by" => Auth::user()->id,
            );

            DeductionsTransaction::create($data);
        }
        

        return response()->json(compact('validate'));
    }

    public function summary_save(Request $request) {
        $validate = $request->validate([
            'schedule_type' => 'required',
            'period_start' => 'required',
            'payroll_period' => 'required',
            'pay_date' => 'required',
        ]);

        $request['sequence_no'] = 'M-'.(new DateTime($request->payroll_period))->format('mdY');
        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        if(TimeLogApprovals::where('start_date','>=', $request->period_start)->count() !== 0) {
            if(PayrollSummary::where('sequence_no','M-'.(new DateTime($request->payroll_period))->format('mdY'))->count() === 0) {
                $summary = PayrollSummary::create($request->all());

                $logs = TimeLogApprovals::where('start_date','>=', $request->period_start)->get();

                foreach($logs as $item) {
                    $resp = (new TimeLogsController)->getSemiMonthly($request->payroll_start, $request->payroll_period, $request->date, $request->pay_date, $item->employee_id);

                    $data = [
                        'employee_id' => $item->employee_id,
                        'sequence_no' => 'M-'.(new DateTime($request->payroll_period))->format('mdY'),
                        'gross_earnings' => $item->gross_earnings,
                        'sss' => $item->sss,
                        'pagibig' => $item->pagibig,
                        'philhealth' => $item->philhealth,
                        'tax' => $item->tax,
                        'net_pay' => $item->net_pay,
                        'status' => 1,
                        'workstation_id' => Auth::user()->workstation_id,
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id,
                    ];

                    PayrollSummaryDetails::create($data);
                }

        
                return response()->json(compact('validate'));
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
        
    }

    public function get() {
        if(request()->ajax()) {
            $records = PayrollSummary::selectRaw('payroll_summaries.id,
                                        payroll_summaries.sequence_no,
                                        payroll_calendars.title as payroll_title,
                                        payroll_summaries.schedule_type,
                                        payroll_summaries.period_start,
                                        payroll_summaries.payroll_period,
                                        SUM(CASE WHEN payroll_summary_details.status = 1 THEN 1 ELSE 0 END) no_of_employee,
                                        SUM(CASE WHEN payroll_summary_details.status = 0 THEN 1 ELSE 0 END) pending_employee,
                                        SUM(CASE WHEN payroll_summary_details.deleted_at IS NULL THEN 1 ELSE 0 END) total_of_employee,
                                        SUM(CASE WHEN payroll_summary_details.id IS NOT NULL AND payroll_summary_details.deleted_at IS NULL THEN
                                            CASE
                                                WHEN COALESCE(payroll_summary_details.gross_earnings, 0) = 0
                                                THEN (COALESCE(payroll_summary_details.net_pay, 0)
                                                    + COALESCE(payroll_summary_details.sss, 0)
                                                    + COALESCE(payroll_summary_details.pagibig, 0)
                                                    + COALESCE(payroll_summary_details.philhealth, 0)
                                                    + COALESCE(payroll_summary_details.tax, 0)
                                                    + COALESCE((SELECT SUM(ca.amount + 0) FROM cash_advance ca WHERE ca.summary_id = payroll_summaries.id AND ca.employee_id = payroll_summary_details.employee_id), 0))
                                                ELSE COALESCE(payroll_summary_details.gross_earnings, 0)
                                            END
                                        ELSE 0 END) amount,
                                        SUM(CASE WHEN payroll_summary_details.id IS NOT NULL AND payroll_summary_details.deleted_at IS NULL THEN
                                            CASE
                                                WHEN COALESCE(payroll_summary_details.gross_earnings, 0) = 0
                                                THEN (COALESCE(payroll_summary_details.net_pay, 0)
                                                    + COALESCE(payroll_summary_details.sss, 0)
                                                    + COALESCE(payroll_summary_details.pagibig, 0)
                                                    + COALESCE(payroll_summary_details.philhealth, 0)
                                                    + COALESCE(payroll_summary_details.tax, 0)
                                                    + COALESCE((SELECT SUM(ca.amount + 0) FROM cash_advance ca WHERE ca.summary_id = payroll_summaries.id AND ca.employee_id = payroll_summary_details.employee_id), 0))
                                                ELSE COALESCE(payroll_summary_details.gross_earnings, 0)
                                            END
                                        ELSE 0 END) gross_earnings,
                                        SUM(CASE WHEN payroll_summary_details.id IS NOT NULL AND payroll_summary_details.deleted_at IS NULL THEN
                                            CASE
                                                WHEN COALESCE(payroll_summary_details.net_pay, 0) = 0
                                                THEN (COALESCE(payroll_summary_details.gross_earnings, 0)
                                                    - (COALESCE(payroll_summary_details.sss, 0)
                                                        + COALESCE(payroll_summary_details.pagibig, 0)
                                                        + COALESCE(payroll_summary_details.philhealth, 0)
                                                        + COALESCE(payroll_summary_details.tax, 0)
                                                        + COALESCE((SELECT SUM(ca.amount + 0) FROM cash_advance ca WHERE ca.summary_id = payroll_summaries.id AND ca.employee_id = payroll_summary_details.employee_id), 0)))
                                                ELSE COALESCE(payroll_summary_details.net_pay, 0)
                                            END
                                        ELSE 0 END) net_amount,
                                        SUM(CASE WHEN payroll_summary_details.id IS NOT NULL AND payroll_summary_details.deleted_at IS NULL THEN (COALESCE(payroll_summary_details.sss, 0) + COALESCE(payroll_summary_details.pagibig, 0) + COALESCE(payroll_summary_details.philhealth, 0) + COALESCE(payroll_summary_details.tax, 0) + COALESCE((SELECT SUM(ca.amount + 0) FROM cash_advance ca WHERE ca.summary_id = payroll_summaries.id AND ca.employee_id = payroll_summary_details.employee_id), 0)) ELSE 0 END) gross_deduction,
                                        payroll_summaries.status,
                                        COALESCE(payroll_summaries.workflow_status, 0) as workflow_status')
                                        ->leftJoin('payroll_summary_details', 'payroll_summary_details.summary_id', '=', 'payroll_summaries.id')
                                        ->leftJoin('payroll_calendars', 'payroll_summaries.sequence_title', '=', 'payroll_calendars.id')
                                        ->whereIn('payroll_summaries.workflow_status', [0, 1]);

            $periodType = request()->get('period_type');
            $status = request()->get('status');
            $dateSort = strtolower((string) request()->get('date_sort')) === 'asc' ? 'asc' : 'desc';
            $keyword = trim((string) request()->get('keyword', ''));

            if ($periodType !== null && $periodType !== '') {
                $records->where('payroll_summaries.schedule_type', (int) $periodType);
            }

            if ($status !== null && $status !== '') {
                $records->where('payroll_summaries.status', (int) $status);
            }

            if ($keyword !== '') {
                $records->where(function($q) use ($keyword) {
                    $q->where('payroll_summaries.sequence_no', 'like', "%{$keyword}%")
                      ->orWhere('payroll_calendars.title', 'like', "%{$keyword}%")
                      ->orWhere('payroll_summaries.period_start', 'like', "%{$keyword}%")
                      ->orWhere('payroll_summaries.payroll_period', 'like', "%{$keyword}%");
                });
            }

            $records = $records->groupBy('payroll_summaries.id',
                                                  'payroll_summaries.sequence_no',
                                                  'payroll_calendars.title',
                                                  'payroll_summaries.schedule_type',
                                                  'payroll_summaries.period_start',
                                                  'payroll_summaries.payroll_period',
                                                  'payroll_summaries.status',
                                                  'payroll_summaries.workflow_status')
                                        ->orderBy('payroll_summaries.period_start', $dateSort)
                                        ->orderBy('payroll_summaries.payroll_period', $dateSort)
                                        ->get()
            ;

            return datatables()->of($records)
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function get_history() {
        if(request()->ajax()) {
            $records = PayrollSummary::selectRaw('payroll_summaries.id,
                                        payroll_summaries.sequence_no,
                                        payroll_calendars.title as payroll_title,
                                        payroll_summaries.schedule_type,
                                        payroll_summaries.period_start,
                                        payroll_summaries.payroll_period,
                                        SUM(CASE WHEN payroll_summary_details.status = 1 THEN 1 ELSE 0 END) no_of_employee,
                                        SUM(CASE WHEN payroll_summary_details.status = 0 THEN 1 ELSE 0 END) pending_employee,
                                        SUM(CASE WHEN payroll_summary_details.deleted_at IS NULL THEN 1 ELSE 0 END) total_of_employee,
                                        SUM(CASE WHEN payroll_summary_details.id IS NOT NULL AND payroll_summary_details.deleted_at IS NULL THEN
                                            CASE
                                                WHEN COALESCE(payroll_summary_details.gross_earnings, 0) = 0
                                                THEN (COALESCE(payroll_summary_details.net_pay, 0)
                                                    + COALESCE(payroll_summary_details.sss, 0)
                                                    + COALESCE(payroll_summary_details.pagibig, 0)
                                                    + COALESCE(payroll_summary_details.philhealth, 0)
                                                    + COALESCE(payroll_summary_details.tax, 0)
                                                    + COALESCE((SELECT SUM(ca.amount + 0) FROM cash_advance ca WHERE ca.summary_id = payroll_summaries.id AND ca.employee_id = payroll_summary_details.employee_id), 0))
                                                ELSE COALESCE(payroll_summary_details.gross_earnings, 0)
                                            END
                                        ELSE 0 END) amount,
                                        SUM(CASE WHEN payroll_summary_details.id IS NOT NULL AND payroll_summary_details.deleted_at IS NULL THEN
                                            CASE
                                                WHEN COALESCE(payroll_summary_details.gross_earnings, 0) = 0
                                                THEN (COALESCE(payroll_summary_details.net_pay, 0)
                                                    + COALESCE(payroll_summary_details.sss, 0)
                                                    + COALESCE(payroll_summary_details.pagibig, 0)
                                                    + COALESCE(payroll_summary_details.philhealth, 0)
                                                    + COALESCE(payroll_summary_details.tax, 0)
                                                    + COALESCE((SELECT SUM(ca.amount + 0) FROM cash_advance ca WHERE ca.summary_id = payroll_summaries.id AND ca.employee_id = payroll_summary_details.employee_id), 0))
                                                ELSE COALESCE(payroll_summary_details.gross_earnings, 0)
                                            END
                                        ELSE 0 END) gross_earnings,
                                        SUM(CASE WHEN payroll_summary_details.id IS NOT NULL AND payroll_summary_details.deleted_at IS NULL THEN
                                            CASE
                                                WHEN COALESCE(payroll_summary_details.net_pay, 0) = 0
                                                THEN (COALESCE(payroll_summary_details.gross_earnings, 0)
                                                    - (COALESCE(payroll_summary_details.sss, 0)
                                                        + COALESCE(payroll_summary_details.pagibig, 0)
                                                        + COALESCE(payroll_summary_details.philhealth, 0)
                                                        + COALESCE(payroll_summary_details.tax, 0)
                                                        + COALESCE((SELECT SUM(ca.amount + 0) FROM cash_advance ca WHERE ca.summary_id = payroll_summaries.id AND ca.employee_id = payroll_summary_details.employee_id), 0)))
                                                ELSE COALESCE(payroll_summary_details.net_pay, 0)
                                            END
                                        ELSE 0 END) net_amount,
                                        SUM(CASE WHEN payroll_summary_details.id IS NOT NULL AND payroll_summary_details.deleted_at IS NULL THEN (COALESCE(payroll_summary_details.sss, 0) + COALESCE(payroll_summary_details.pagibig, 0) + COALESCE(payroll_summary_details.philhealth, 0) + COALESCE(payroll_summary_details.tax, 0) + COALESCE((SELECT SUM(ca.amount + 0) FROM cash_advance ca WHERE ca.summary_id = payroll_summaries.id AND ca.employee_id = payroll_summary_details.employee_id), 0)) ELSE 0 END) gross_deduction,
                                        payroll_summaries.status,
                                        COALESCE(payroll_summaries.workflow_status, 0) as workflow_status')
                                        ->leftJoin('payroll_summary_details', 'payroll_summary_details.summary_id', '=', 'payroll_summaries.id')
                                        ->leftJoin('payroll_calendars', 'payroll_summaries.sequence_title', '=', 'payroll_calendars.id')
                                        ->where(function($q) {
                                            $q->where('payroll_summaries.workflow_status', 3)
                                              ->orWhere('payroll_summaries.status', 1);
                                        });

            $periodType = request()->get('period_type');
            $status = request()->get('status');
            $dateSort = strtolower((string) request()->get('date_sort')) === 'asc' ? 'asc' : 'desc';
            $keyword = trim((string) request()->get('keyword', ''));

            if ($periodType !== null && $periodType !== '') {
                $records->where('payroll_summaries.schedule_type', (int) $periodType);
            }

            if ($status !== null && $status !== '') {
                $records->where('payroll_summaries.status', (int) $status);
            }

            if ($keyword !== '') {
                $records->where(function($q) use ($keyword) {
                    $q->where('payroll_summaries.sequence_no', 'like', "%{$keyword}%")
                      ->orWhere('payroll_calendars.title', 'like', "%{$keyword}%")
                      ->orWhere('payroll_summaries.period_start', 'like', "%{$keyword}%")
                      ->orWhere('payroll_summaries.payroll_period', 'like', "%{$keyword}%");
                });
            }

            $records = $records->groupBy('payroll_summaries.id',
                                                  'payroll_summaries.sequence_no',
                                                  'payroll_calendars.title',
                                                  'payroll_summaries.schedule_type',
                                                  'payroll_summaries.period_start',
                                                  'payroll_summaries.payroll_period',
                                                  'payroll_summaries.status',
                                                  'payroll_summaries.workflow_status')
                                        ->orderBy('payroll_summaries.period_start', $dateSort)
                                        ->orderBy('payroll_summaries.payroll_period', $dateSort)
                                        ->get()
            ;

            return datatables()->of($records)
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function get_details($sequence_no) {
        if(request()->ajax()) {
            return datatables()->of(
                PayrollSummaryDetails::with('employee')->where('sequence_no', $sequence_no)->get()
            )
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function get_summary() {
        $schedule = PayrollCalendar::get();

        $data = array();
        $data_2 = array();
        $summary_details_data = array();

        foreach($schedule as $sched) {
            $no_of_employee = Employment::where('payroll_calendar_id', $sched->id)->count();
            $hourly_rate = Compensations::first()->hourly_salary;
            
            if($sched->type === 1) {
                $startdate = $sched->start_date;
                $endate = $sched->end_date;
                $payment_date = $sched->payment_date;
                $today = Carbon::now()->format('Y-m-d');

                $start = strtotime($startdate);
                $set = strtotime($endate);
                $set2 = strtotime($endate);
                $end = strtotime($today);

                $new_payment = strtotime($payment_date);

                while($set < $end)
                {
                    $amount_total = $this->summary->query($start, $set, $sched->type)->sum('total_salary');
                    
                    $summary_details = $this->summary->get_summary_details($start, $set, $sched->type);

                    foreach($summary_details as $details) {
                        $basic_salary = $details->total_salary;

                        $sss = $this->sss_benefits->getValue($basic_salary)->ee;
                        $pagibig = $this->pagibig_benefits->getValue($basic_salary);
                        $philhealth = $this->philhealt_benefits->getValue($basic_salary);
                        $tax = $this->withholding_tax->getValue($basic_salary, $sched->type)->fix_tax;
                        $netpay =  $this->computation->deduction($sss, $pagibig, $philhealth, $basic_salary, $tax);

                        if(PayrollSummaryDetails::where('employee_id', $details->id)->where('sequence_no', "M-".date('mdY', $set))->count() === 0) {
                            array_push($summary_details_data, array(
                                "employee_id" => $details->id,
                                "sequence_no" => "M-".date('mdY', $set),
                                "gross_earnings" => $basic_salary,
                                "sss" => $sss,
                                "pagibig" => $pagibig,
                                "philhealth" => $philhealth,
                                "tax" => $tax,
                                "net_pay" => $netpay,
                                "status" => 1,
                                "workstation_id" => Auth::user()->workstation_id,
                                "created_by" => Auth::user()->id,
                                "updated_by" => Auth::user()->id,
                                "rate" => $details->rate,
                                "hours" => $details->hours
                            ));
                        }
                    }

                    $start = Carbon::parse(date('Y-m-d', $start));
                    $set = Carbon::parse(date('Y-m-d', $set));
                    $set2 = Carbon::parse(date('Y-m-d', $set2));

                    $new_payment = Carbon::parse(date('Y-m-d', $new_payment));
                    
                    if($this->summary->summaryWholeCount($set->format('Y-m-d'), 1, "M-".$set->format('mdY')) === 0) {
                        $input_data = array(
                            "sequence_no" => "M-".$set->format('mdY'),
                            "schedule_type" => 1,
                            "period_start" => $start->format('Y-m-d'),
                            "payroll_period" => $set->format('Y-m-d'),
                            "pay_date" => $new_payment->format('Y-m-d'),
                            "status" => 0,
                            "workstation_id" => Auth::user()->workstation_id,
                            "created_by" => Auth::user()->id,
                            "updated_by" => Auth::user()->id,
                            "created_at" => date('Y-m-d h:i:s'),
                            "updated_at" => date('Y-m-d h:i:s')
                        );

                        if($this->summary->summaryPeriodCount("M-".$set->format('mdY'), 1) === 0) {
                            array_push($data, $input_data);
                        }
                        else {
                            array_push($data_2, $input_data);
                        }
                    }

                    if((intval($set->format('m'))) === 1 && (intval($set->format('d'))) > 28) {
                        $new_payment = strtotime($new_payment->addMonth());

                        $start = strtotime($start->addMonth());
                        $set = strtotime($set->add('25 days')->endOfMonth());
                        $set2 = strtotime($set2->format('Y-m-d'));
                    }
                    else if((intval($set->format('m'))) === 2 && (intval($set->format('d'))) >= 28) {
                        $new_payment = strtotime($new_payment->addMonth());
                        
                        $start = strtotime($start->addMonth());
                        $set = strtotime($set2->addMonth(2));
                        $set2 = $set;
                    }
                    else {
                        if((intval(date('d', strtotime($endate)))) > 30) {
                            $new_payment = strtotime($new_payment->addMonth());

                            $start = strtotime($start->addMonth());
                            $set = strtotime($set2->add("30 days")->endOfMonth());
                            $set2 = strtotime($set2);
                        }
                        else {
                            $new_payment = strtotime($new_payment->addMonth());

                            $start = strtotime($start->addMonth());
                            $set = strtotime($set2->addMonth());
                            $set2 = strtotime($set2);
                        }
                    }

                }
            }

            else if($sched->type === 2) {
                $n1 = new DateTime('now');

                $collect = $this->generate_semi($n1, $sched);
                
                $data = $collect['output1'];
                $summary_details_data = $collect['output2'];

            }
            
        }

        print_r($data);
        // if(count($data) !== 0) {
        $this->summary->insertSummary($data);
        $this->summary->insertSummaryDetails($summary_details_data);
        // }
        // $this->summary->updateSummary($data_2);

    }

    public function generate_semi($date, $sched) {

        $final = array();
        $final2 = array();

        $s1 = new DateTime($sched->start_date);
        $e1 = new DateTime($sched->end_date);
        $p1 = new DateTime($sched->payment_date);

        $ns1 = new DateTime($date->format('Y').'-'.$date->format('m').'-'.$s1->format('d'));
        $ne1 = new DateTime($date->format('Y').'-'.$date->format('m').'-'.$e1->format('d'));
        $np1 = new DateTime($date->format('Y').'-'.$date->format('m').'-'.$p1->format('d'));

        $ns2 = (new DateTime($date->format('Y').'-'.$date->format('m').'-'.$e1->format('d')))->modify('+1 day');
        $ne2 = (new DateTime($date->format('Y').'-'.$date->format('m').'-'.$s1->format('d')))->modify('+1 month')->modify('-1 day');
        $np2 = (new DateTime($date->format('Y').'-'.$date->format('m').'-'.$s1->format('d')))->modify('+1 month')->modify('-1 day')->modify('+'.$ne1->diff($np1)->format("%r%a").' day');

        if($date >= $ns1 && $date <= $ne1) {
            $input_data = array(
                "sequence_no" => "M-".$ne1->format('mdY'),
                "schedule_type" => $sched->type,
                "period_start" => $ns1->format('Y-m-d'),
                "payroll_period" => $ne1->format('Y-m-d'),
                "pay_date" => $np1->format('Y-m-d'),
                "status" => 0,
                "workstation_id" => Auth::user()->workstation_id,
                "created_by" => Auth::user()->id,
                "updated_by" => Auth::user()->id,
                "created_at" => date('Y-m-d h:i:s'),
                "updated_at" => date('Y-m-d h:i:s')
            );
            
            if($this->summary->summaryPeriodCount("M-".$ne1->format('mdY'), $sched->type) === 0) {
                $final = $input_data;
            }

            $summary_details = $this->summary->get_summary_details($ns1->format('Y-m-d'), $ne1->format('Y-m-d'), $sched->id);

            foreach($summary_details as $details) {
                $basic_salary = $details->total_salary;

                $sss = $this->sss_benefits->getValue($basic_salary)->ee;
                $pagibig = $this->pagibig_benefits->getValue($basic_salary);
                $philhealth = $this->philhealt_benefits->getValue($basic_salary);
                $tax = $this->withholding_tax->getValue($basic_salary, $sched->id)->fix_tax;
                $netpay =  $this->computation->deduction($sss, $pagibig, $philhealth, $basic_salary, $tax);

                if(PayrollSummaryDetails::where('employee_id', $details->id)->where('sequence_no', "M-".$ne1->format('mdY'))->count() === 0) {
                    array_push($final2, array(
                        "employee_id" => $details->id,
                        "sequence_no" => "M-".$ne1->format('mdY'),
                        "gross_earnings" => $basic_salary,
                        "sss" => $sss,
                        "pagibig" => $pagibig,
                        "philhealth" => $philhealth,
                        "tax" => $tax,
                        "net_pay" => $netpay,
                        "status" => 1,
                        "workstation_id" => Auth::user()->workstation_id,
                        "created_by" => Auth::user()->id,
                        "updated_by" => Auth::user()->id,
                        "rate" => $details->rate,
                        "hours" => $details->hours
                    ));
                }
            }
        }
        else if($date >= $ns2 && $date <= $ne2) {
            $input_data = array(
                "sequence_no" => "M-".$ne2->format('mdY'),
                "schedule_type" => $sched->type,
                "period_start" => $ns2->format('Y-m-d'),
                "payroll_period" => $ne2->format('Y-m-d'),
                "pay_date" => $np2->format('Y-m-d'),
                "status" => 0,
                "workstation_id" => Auth::user()->workstation_id,
                "created_by" => Auth::user()->id,
                "updated_by" => Auth::user()->id,
                "created_at" => date('Y-m-d h:i:s'),
                "updated_at" => date('Y-m-d h:i:s')
            );

            if($this->summary->summaryPeriodCount("M-".$ne2->format('mdY'), $sched->type) === 0) {
                $final = $input_data;
            }
            
            $summary_details = $this->summary->get_summary_details($ns2->format('Y-m-d'), $ne2->format('Y-m-d'), $sched->id);

            foreach($summary_details as $details) {
                $basic_salary = $details->total_salary;

                $sss = $this->sss_benefits->getValue($basic_salary)->ee;
                $pagibig = $this->pagibig_benefits->getValue($basic_salary);
                $philhealth = $this->philhealt_benefits->getValue($basic_salary);
                $tax = $this->withholding_tax->getValue($basic_salary, $sched->type)->fix_tax;
                $netpay =  $this->computation->deduction($sss, $pagibig, $philhealth, $basic_salary, $tax);

                if(PayrollSummaryDetails::where('employee_id', $details->id)->where('sequence_no', "M-".$ne2->format('mdY'))->count() === 0) {
                    array_push($final2, array(
                        "employee_id" => $details->id,
                        "sequence_no" => "M-".$ne2->format('mdY'),
                        "gross_earnings" => $basic_salary,
                        "sss" => $sss,
                        "pagibig" => $pagibig,
                        "philhealth" => $philhealth,
                        "tax" => $tax,
                        "net_pay" => $netpay,
                        "status" => 1,
                        "workstation_id" => Auth::user()->workstation_id,
                        "created_by" => Auth::user()->id,
                        "updated_by" => Auth::user()->id,
                        "rate" => $details->rate,
                        "hours" => $details->hours
                    ));
                }
            }
        }

        return ['output1' => $final, 'output2' => $final2];

    }

    public function get_earnings_and_deductions(Request $request) {

        $employee = EmployeeInformation::with('employments_tab')->where('id', $request->employee_id)->firstOrFail();
        $summary = PayrollSummary::with('calendar')->where('sequence_no', $request->sequence_no)->firstOrFail();

        $earnings = EarningsTransaction::with('earning')->where('sequence_no', $request->sequence_no)->where('employee_id', $request->employee_id)->get();
        $deductions = DeductionsTransaction::with('deduction')->where('sequence_no', $request->sequence_no)->where('employee_id', $request->employee_id)->get();

        $earnings_total = $this->earnings->getValue($request->sequence_no, $request->employee_id);
        $deductions_total = $this->deductions->getValue($request->sequence_no, $request->employee_id);

        $taxable_amount = $this->taxable_amount->getValue($this->earnings->getTaxable($request->sequence_no, $request->employee_id), $this->deductions->getValue($request->sequence_no, $request->employee_id));

        if($taxable_amount < 0) {
            $withholding_tax = $this->withholding_tax->getValue(0, $request->schedule_type)->fix_tax;
        }
        else {
            $withholding_tax = $this->withholding_tax->getValue($taxable_amount, $request->schedule_type)->fix_tax;
        }

        $netpay = $this->netpay->getValue($earnings_total, $deductions_total, $withholding_tax);

        return response()->json(compact('employee', 'earnings', 'deductions', 'earnings_total', 'deductions_total', 'taxable_amount', 'withholding_tax', 'netpay', 'summary'));
    }

    public function get_earnings() {
        $earnings = Earnings::get();

        return response()->json(compact('earnings'));
    }

    public function get_deductions() {
        $deductions = Deductions::get();

        return response()->json(compact('deductions'));
    }

    public function update_status(Request $request) {
        PayrollSummary::where('id', $request->id)->update(['status' => $request->status]);
    }
    
    public function update_details_status(Request $request) {
        PayrollSummaryDetails::where('id', $request->id)->update(['status' => $request->status]);
    }

    public function email_recipients($summary_id) {
        $details = PayrollSummaryDetails::with('employee')
            ->where('summary_id', $summary_id)
            ->get();

        $recipients = $details->map(function($item) {
            $employee = $item->employee;
            $email = $employee ? trim((string) $employee->email) : '';
            $fullName = $employee
                ? trim(
                    ($employee->firstname ?? '') .
                    (($employee->middlename ?? '') !== '' ? (' ' . $employee->middlename) : '') .
                    ' ' . ($employee->lastname ?? '')
                )
                : 'Unknown Employee';

            return [
                'detail_id' => $item->id,
                'employee_id' => $item->employee_id,
                'employee_no' => $employee->employee_no ?? '-',
                'employee_name' => $fullName,
                'email' => $email,
                'has_email' => $email !== '',
                'payslip_status' => ($item->payslip_status ?: 'FOR SENDING'),
            ];
        })->values();

        return response()->json(compact('recipients'));
    }

    public function send_selected_payslips(Request $request) {
        $validated = $request->validate([
            'summary_id' => 'required|integer|exists:payroll_summaries,id',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'integer',
        ]);

        $details = PayrollSummaryDetails::with('employee')
            ->where('summary_id', $validated['summary_id'])
            ->whereIn('employee_id', $validated['employee_ids'])
            ->get();

        $sent = [];
        $skipped = [];

        foreach ($details as $item) {
            $email = trim((string) optional($item->employee)->email);
            $name = trim(
                (optional($item->employee)->firstname ?? '') .
                ((optional($item->employee)->middlename ?? '') !== '' ? (' ' . optional($item->employee)->middlename) : '') .
                ' ' . (optional($item->employee)->lastname ?? '')
            );

            if ($email === '') {
                $skipped[] = [
                    'employee_id' => $item->employee_id,
                    'employee_name' => $name !== '' ? $name : 'Unknown Employee',
                    'reason' => 'No registered email',
                ];
                continue;
            }

            // Placeholder: actual mail dispatch can be added here.
            PayrollSummaryDetails::where('id', $item->id)->update([
                'payslip_status' => 'SENT',
                'payslip_sent_at' => now(),
                'payslip_sent_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            $sent[] = [
                'detail_id' => $item->id,
                'employee_id' => $item->employee_id,
                'employee_name' => $name !== '' ? $name : 'Unknown Employee',
                'email' => $email,
            ];
        }
        
        $pendingEmailable = PayrollSummaryDetails::where('summary_id', $validated['summary_id'])
            ->whereHas('employee', function ($q) {
                $q->whereNotNull('email')->where('email', '!=', '');
            })
            ->where(function($q) {
                $q->whereNull('payslip_status')
                  ->orWhere('payslip_status', '!=', 'SENT');
            })
            ->count();

        if ($pendingEmailable === 0) {
            PayrollSummary::where('id', $validated['summary_id'])->update([
                'status' => 2,
                'updated_by' => Auth::user()->id,
            ]);
        }

        return response()->json([
            'message' => 'Payslip email dispatch processed.',
            'sent_count' => count($sent),
            'skipped_count' => count($skipped),
            'sent' => $sent,
            'skipped' => $skipped,
        ]);
    }

    public function get_overall(Request $request) {
        $summary_details = PayrollSummaryDetails::where('sequence_no', $request->data['sequence_no'])->get();

        $total = array(
            "gross" => $summary_details->sum('gross_earnings'),
            "gross_deduction" => $summary_details->sum(function($item) {
                return floatval($item->sss) + floatval($item->philhealth) + floatval($item->pagibig) + floatval($item->tax);
            }),
            "net_pay" => $summary_details->sum('net_pay')
        );

        return response()->json(compact('total'));
    }

    public function show(Request $request) {
        $summary_details = PayrollSummaryDetails::where('employee_id', $request->employee_id)->where('sequence_no', $request->sequence_no)->firstOrFail();
        $previous = PayrollSummaryDetails::where('id', '<', $summary_details->id)->where('sequence_no', $request->sequence_no)->orderBy('id','desc')->first();
        $next = PayrollSummaryDetails::where('id', '>', $summary_details->id)->where('sequence_no', $request->sequence_no)->orderBy('id','asc')->first();
    
        return response()->json(compact('summary_details', 'previous', 'next'));
    }

    public function update_details($sequence_no, $employee_id, $type) {
        $earnings_total = $this->earnings->getValue($sequence_no, $employee_id);
        $deductions_total = $this->deductions->getValue($sequence_no, $employee_id);
        $taxable_amount = $this->taxable_amount->getValue($this->earnings->getTaxable($sequence_no, $employee_id), $this->deductions->getTaxable($sequence_no, $employee_id));

        if($taxable_amount < 0) {
            $withholding_tax = $this->withholding_tax->getValue(0, $type)->fix_tax;
        }
        else {
            $withholding_tax = $this->withholding_tax->getValue($taxable_amount, $type)->fix_tax;
        }

        $netpay = $this->netpay->getValue($earnings_total, $deductions_total, 0);

        PayrollSummaryDetails::where('sequence_no', $sequence_no)->where('employee_id', $employee_id)->update(['gross_earnings' => $earnings_total, 'net_pay' => $netpay]);
    }

    public function get13thPay($id, $sequence) {
        $details = PayrollSummaryDetails::with('header', 'employee', 'employee.employments_tab', 'employee.employments_tab.departments', 'employee.employments_tab.positions')->where('employee_id', $id)->where('sequence_no', $sequence)->first();

        return response()->json(compact('details'));
    }
}
