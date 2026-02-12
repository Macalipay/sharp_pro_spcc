<?php

namespace App\Http\Controllers;

use Auth;
use App\WorkTypeSetup;
use Illuminate\Http\Request;

class WorkTypeSetupController extends Controller
{
    public function save(Request $request) {
        WorkTypeSetup::create($request->all());
    }

    public function get($id, $days) {
        $worktypedetails = WorkTypeSetup::with('worktype', 'employee', 'projects')->where('days', $days)->where('employee_id', $id)->get();

        return response()->json(compact('worktypedetails'));
    }
    
    public function edit($id)
    {
        $worktypedetails = WorkTypeSetup::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('worktypedetails'));
    }

    public function update(Request $request, $id)
    {
        WorkTypeSetup::find($id)->update($request->all());
        return "Record Saved";
    }
    
    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            WorkTypeSetup::find($item)->delete();
        }

        return 'Record Deleted';
    }
}
