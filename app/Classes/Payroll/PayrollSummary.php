<?php
namespace App\Classes\Payroll;

use Auth;
use App\EmployeeInformation;
use App\PayrollSummary as Summary;
use App\EarningsTransaction;
use App\DeductionsTransaction;
use App\PayrollSummaryDetails;
use App\EarningsTransactionController;
use Illuminate\Support\Arr;

class PayrollSummary {
    public function query($start, $set, $type) {
        $salaries = EmployeeInformation::selectRaw("
                        employees.id,
                        CASE WHEN SUM(CASE WHEN time_logs.date >= '".date('Y-m-d', $start)."' AND time_logs.date <= '".date('Y-m-d', $set)."' THEN time_logs.total_hours END) IS NOT NULL THEN SUM(CASE WHEN time_logs.date >= '".date('Y-m-d', $start)."' AND time_logs.date <= '".date('Y-m-d', $set)."' THEN time_logs.total_hours END) ELSE 0 END as 'reg',
                        CASE WHEN compensations.hourly_salary IS NOT NULL THEN compensations.hourly_salary ELSE 0 END hourly_rate,
                        ((CASE WHEN compensations.hourly_salary IS NOT NULL THEN compensations.hourly_salary ELSE 0 END) * (CASE WHEN SUM(CASE WHEN time_logs.date >= '".date('Y-m-d', $start)."' AND time_logs.date <= '".date('Y-m-d', $set)."' THEN time_logs.total_hours END) IS NOT NULL THEN SUM(CASE WHEN time_logs.date >= '".date('Y-m-d', $start)."' AND time_logs.date <= '".date('Y-m-d', $set)."' THEN time_logs.total_hours END) ELSE 0 END)) as total_salary
                    ")
                    ->leftJoin('time_logs', 'employees.id', '=', 'time_logs.employee_id')
                    ->leftJoin('compensations', 'compensations.employee_id', '=', 'employees.id')
                    ->leftJoin('employments', 'employments.employee_id', '=', 'employees.id')
                    ->leftJoin('payroll_calendars', 'payroll_calendars.id', '=', 'employments.payroll_calendar_id')
                    ->where('payroll_calendars.type', $type)
                    ->groupBy("employees.id")
                    ->get();

        return $salaries;
    }

    public function get_summary_details($start, $set, $type) {
        $salaries = EmployeeInformation::selectRaw("
                        employees.id,
                        (CASE WHEN SUM(CASE WHEN time_logs.date >= '".$start."' AND time_logs.date <= '".$set."' THEN time_logs.total_hours END) IS NOT NULL THEN SUM(CASE WHEN time_logs.date >= '".$start."' AND time_logs.date <= '".$set."' THEN time_logs.total_hours END) ELSE 0 END) as hours,
                        (CASE WHEN compensations.hourly_salary IS NOT NULL THEN compensations.hourly_salary ELSE 0 END) as rate,
                        CASE WHEN SUM(CASE WHEN time_logs.date >= '".$start."' AND time_logs.date <= '".$set."' THEN time_logs.total_hours END) IS NOT NULL THEN SUM(CASE WHEN time_logs.date >= '".$start."' AND time_logs.date <= '".$set."' THEN time_logs.total_hours END) ELSE 0 END as 'reg',
                        CASE WHEN compensations.hourly_salary IS NOT NULL THEN compensations.hourly_salary ELSE 0 END hourly_rate,
                        ((CASE WHEN compensations.hourly_salary IS NOT NULL THEN compensations.hourly_salary ELSE 0 END) * (CASE WHEN SUM(CASE WHEN time_logs.date >= '".$start."' AND time_logs.date <= '".$set."' THEN time_logs.total_hours END) IS NOT NULL THEN SUM(CASE WHEN time_logs.date >= '".$start."' AND time_logs.date <= '".$set."' THEN time_logs.total_hours END) ELSE 0 END)) as total_salary
                    ")
                    ->leftJoin('time_logs', 'employees.id', '=', 'time_logs.employee_id')
                    ->leftJoin('compensations', 'compensations.employee_id', '=', 'employees.id')
                    ->leftJoin('employments', 'employments.employee_id', '=', 'employees.id')
                    ->leftJoin('payroll_calendars', 'payroll_calendars.id', '=', 'employments.payroll_calendar_id')
                    ->where('payroll_calendars.type', $type)
                    ->groupBy("employees.id")
                    ->get();

        return $salaries;
    }

    public function summaryWholeCount($period, $type, $sequence_no) {
        
        $payroll_summary = Summary::where('payroll_period', $period)->where('schedule_type', $type)->where('sequence_no', $sequence_no)->count();

        return $payroll_summary;

    }
    
    public function summaryPeriodCount($sequence, $type) {

        $payroll_summary = Summary::where('sequence_no', $sequence)->where('schedule_type', $type)->count();

        return $payroll_summary;

    }

    public function insertSummary($data) {
        Summary::insert($data);
    }

    public function updateSummary($data) {

        foreach($data as $item) {
            $payroll_summary = Summary::where('sequence_no', $item['sequence_no'])->where('schedule_type', $item['schedule_type'])->where('no_of_employee', $item['no_of_employee'])->where('amount', $item['amount'])->count();
            
            if($payroll_summary === 0) {
                Summary::where('sequence_no', $item['sequence_no'])->update(['amount' => $item['amount'], 'no_of_employee' => $item['no_of_employee']]);
            }
        }
    }

    public function insertSummaryDetails($data) {
        $record = $this->array_except($data, ['hours', 'rate']);

        PayrollSummaryDetails::insert($record);

        foreach($data as $item) {
            $earning = array(
                'employee_id' => $item['employee_id'],
                'sequence_no' => $item['sequence_no'],
                'earning_id' => 1,
                'rate' => $item['rate'],
                'hours' => $item['hours'],
                'total' => $item['gross_earnings'],
                'status' => 1,
                "workstation_id" => Auth::user()->workstation_id,
                "created_by" => Auth::user()->id,
                "updated_by" => Auth::user()->id,
            );

            EarningsTransaction::insert($earning);
            
            $deduction = array(
                [
                    'employee_id' => $item['employee_id'],
                    'sequence_no' => $item['sequence_no'],
                    'deduction_id' => 1,
                    'rate' => $item['sss'],
                    'hours' => '',
                    'total' => $item['sss'],
                    'status' => 1,
                    "workstation_id" => Auth::user()->workstation_id,
                    "created_by" => Auth::user()->id,
                    "updated_by" => Auth::user()->id
                ],
                [
                    'employee_id' => $item['employee_id'],
                    'sequence_no' => $item['sequence_no'],
                    'deduction_id' => 2,
                    'rate' => $item['philhealth'],
                    'hours' => '',
                    'total' => $item['philhealth'],
                    'status' => 1,
                    "workstation_id" => Auth::user()->workstation_id,
                    "created_by" => Auth::user()->id,
                    "updated_by" => Auth::user()->id
                ],
                [
                    'employee_id' => $item['employee_id'],
                    'sequence_no' => $item['sequence_no'],
                    'deduction_id' => 3,
                    'rate' => $item['pagibig'],
                    'hours' => '',
                    'total' => $item['pagibig'],
                    'status' => 1,
                    "workstation_id" => Auth::user()->workstation_id,
                    "created_by" => Auth::user()->id,
                    "updated_by" => Auth::user()->id
                ]
            );

            DeductionsTransaction::insert($deduction);
        }
    }

    public function updateEarningsAndDeductions($employee_id, $sequence_no, $data) {
        
        $record = array(
            "gross_earnings" => $data['gross_earnings'],
            "sss" => $data['sss'],
            "pagibig" => $data['pagibig'],
            "philhealth" => $data['philhealth'],
            "tax" => $data['tax'],
            "net_pay" => $data['net_pay']
        );
        PayrollSummaryDetails::where('employee_id', $employee_id)->where('sequence_no', $sequence_no)->update($record);

        // Update Earnings: Regular Earning
        EarningsTransaction::where('employee_id', $employee_id)->where('sequence_no', $sequence_no)->where('earning_id', 1)->update(['total' => $data['gross_earnings'], 'rate' => $data['rate'], 'hours' => $data['hours']]);

        // Update Deductions: Mandated Benefits
        DeductionsTransaction::where('employee_id', $employee_id)->where('sequence_no', $sequence_no)->where('deduction_id', 1)->update(['total' => $data['sss'], 'rate' => $data['sss']]);
        DeductionsTransaction::where('employee_id', $employee_id)->where('sequence_no', $sequence_no)->where('deduction_id', 2)->update(['total' => $data['philhealth'], 'rate' => $data['philhealth']]);
        DeductionsTransaction::where('employee_id', $employee_id)->where('sequence_no', $sequence_no)->where('deduction_id', 3)->update(['total' => $data['pagibig'], 'rate' => $data['pagibig']]);
    }

    public function array_except($array, Array $excludeKeys) {
        $record = array();
        foreach($array as $data){
            array_push($record, Arr::except($data, $excludeKeys));
        }
        return $record;
    }
}
?>