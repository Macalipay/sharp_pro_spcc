<?php

namespace App\Http\Controllers;

use Auth;
use App\QuitClaimsAdditions;
use Illuminate\Http\Request;

class QuitClaimsAdditionsController extends Controller
{
    public function save(Request $request) {
        
        $validatedData = $request->validate([
            'earning_type_id' => ['required']
        ]);

        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        QuitClaimsAdditions::create($request->all());
    }
    
    public function get($id) {
        if(request()->ajax()) {
            return datatables()->of(QuitClaimsAdditions::with('employee', 'earning')->where('employee_id', $id)->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function edit($id)
    {
        $quit_claims_addtions = QuitClaimsAdditions::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('quit_claims_addtions'));
    }
    
    public function update(Request $request, $id)
    {
        QuitClaimsAdditions::find($id)->update($request->all());
        return "Record Saved";
    }
    
    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            QuitClaimsAdditions::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
}
