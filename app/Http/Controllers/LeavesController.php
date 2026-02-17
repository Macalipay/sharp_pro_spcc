<?php

namespace App\Http\Controllers;

use Auth;
use App\Leaves;
use App\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use LaravelJsonApi\Core\Document\Error;

class LeavesController extends Controller
{
    public function save(Request $request, $id) {
        $output = '';
        
        $validate = $request->validate([
            'leave_type' => 'required',
            'total_hours' => 'required'
        ]);

        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        $employment = Leaves::where('employee_id', $request->employee_id)->where('leave_type', $request->leave_type)->count();
        if($employment === 0) {
            $output = 'saved';
            Leaves::create($request->all());
        }
        else {
            $output = "updated";
            Leaves::where('employee_id', $request->employee_id)->where('leave_type', $request->leave_type)->update($request->except('_token', 'created_by'));
        }
        return response()->json(compact('validate'));
    }
    
    public function get($id) {
        if(request()->ajax()) {
            $rows = Leaves::with('leave_types')
                ->where('employee_id', $id)
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($row) use ($id) {
                    $currentBalance = floatval($row->total_hours ?? 0);
                    $totalDaysUsed = floatval(
                        LeaveRequest::where('employee_id', $id)
                            ->where('leave_type_id', $row->leave_type)
                            ->where('status', 1)
                            ->sum('total_leave_hours')
                    );

                    // Leaves.total_hours is updated as running balance after approvals.
                    // Original entitlement = current balance + total approved usage.
                    $row->entitlement_days = $currentBalance + $totalDaysUsed;
                    $row->total_days_used = $totalDaysUsed;
                    $row->beginning_balance = $currentBalance;

                    return $row;
                });

            return datatables()->of($rows)
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function history($id)
    {
        if (request()->ajax()) {
            $currentBalanceByType = Leaves::where('employee_id', $id)
                ->get()
                ->mapWithKeys(function ($leave) {
                    return [$leave->leave_type => floatval($leave->total_hours ?? 0)];
                });

            $historyRows = LeaveRequest::with('leave_type')
                ->where('employee_id', $id)
                ->orderBy('leave_type_id', 'asc')
                ->orderBy('start_date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $computedRows = collect();

            $historyRows->groupBy('leave_type_id')->each(function ($rows, $leaveTypeId) use ($currentBalanceByType, $computedRows) {
                $typeId = (int) $leaveTypeId;
                $totalDaysUsedAll = floatval($rows->sum(function ($row) {
                    return floatval($row->total_leave_hours ?? 0);
                }));

                $currentBalance = $currentBalanceByType->has($typeId)
                    ? floatval($currentBalanceByType->get($typeId))
                    : floatval(optional($rows->first())->current_leave_balance ?? 0);

                // Entitlement (initial input) = current balance + all used days for this leave type.
                $entitlementDays = $currentBalance + $totalDaysUsedAll;
                $runningBalance = $entitlementDays;

                $rows->values()->each(function ($row, $index) use (&$runningBalance, $entitlementDays, $computedRows) {
                    $usedDays = floatval($row->total_leave_hours ?? 0);
                    $beginningBalance = $index === 0 ? $entitlementDays : $runningBalance;
                    $balanceAfterUsage = $beginningBalance - $usedDays;

                    $row->entitlement_days = $entitlementDays;
                    $row->beginning_balance = $beginningBalance;
                    $row->balance_after_usage = $balanceAfterUsage;

                    $runningBalance = $balanceAfterUsage;
                    $computedRows->push($row);
                });
            });

            $history = $computedRows->sort(function ($a, $b) {
                $aTime = strtotime((string) ($a->start_date ?? '')) ?: 0;
                $bTime = strtotime((string) ($b->start_date ?? '')) ?: 0;

                if ($aTime === $bTime) {
                    return intval($b->id ?? 0) <=> intval($a->id ?? 0);
                }

                return $bTime <=> $aTime;
            })->values();

            return datatables()->of($history)
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            Leaves::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
}
