<?php

namespace App\Http\Controllers;
use Auth;

use App\EmployeeEducationalBackground;
use Illuminate\Http\Request;

class EmployeeEducationalBackgroundController extends Controller
{
    public function save(Request $request, $id) {
        if (empty($request->employee_id)) {
            return response()->json([
                'message' => 'Please save basic information first before adding educational background.',
                'errors' => ['employee_id' => ['Employee record is required.']],
            ], 422);
        }

        $validate = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'educational_attainment' => 'required|string|max:255',
            'course' => 'nullable|string|max:255',
            'school_year' => 'nullable|string|max:50',
            'school' => 'nullable|string|max:255',
        ]);

        $payload = array_merge($validate, [
            'course' => $request->course ?? '',
            'school_year' => $request->school_year ?? '',
            'school' => $request->school ?? '',
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id,
        ]);

        $employment = EmployeeEducationalBackground::where('employee_id', $payload['employee_id'])
            ->where('school_year', $payload['school_year'])
            ->count();

        if($employment === 0) {
            EmployeeEducationalBackground::create($payload);
        }
        else {
            EmployeeEducationalBackground::where('employee_id', $payload['employee_id'])
                ->update([
                    'educational_attainment' => $payload['educational_attainment'],
                    'course' => $payload['course'],
                    'school_year' => $payload['school_year'],
                    'school' => $payload['school'],
                    'updated_by' => $payload['updated_by'],
                ]);
        }

        return response()->json(compact('validate'));
    }

    public function get($id) {
        if(request()->ajax()) {
            return datatables()->of(EmployeeEducationalBackground::where('employee_id', $id)->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            EmployeeEducationalBackground::find($item)->delete();
        }

        return 'Record Deleted';
    }
}
