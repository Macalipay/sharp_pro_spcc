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
            return datatables()->of(Leaves::with('leave_types')->where('employee_id', $id)->orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function history($id)
    {
        if (request()->ajax()) {
            $entitlementByType = Leaves::where('employee_id', $id)
                ->get()
                ->mapWithKeys(function ($leave) {
                    return [$leave->leave_type => floatval($leave->total_hours ?? 0)];
                });

            $history = LeaveRequest::with('leave_type')
                    ->where('employee_id', $id)
                    ->orderBy('start_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->get()
                    ->map(function ($row) {
                        $entitlementDays = floatval($row->current_leave_balance ?? 0);
                        if ($entitlementByType->has($row->leave_type_id)) {
                            $entitlementDays = floatval($entitlementByType->get($row->leave_type_id));
                        }

                        $totalDaysUsed = floatval($row->total_leave_hours ?? 0);
                        $row->entitlement_days = $entitlementDays;
                        $row->initial_balance = $entitlementDays;
                        $row->balance_after_usage = $entitlementDays - $totalDaysUsed;
                        return $row;
                    });

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
