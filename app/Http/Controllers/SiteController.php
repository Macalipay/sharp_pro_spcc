<?php

namespace App\Http\Controllers;

use App\Site;
use App\EmployeeInformation;
use Illuminate\Http\Request;
use Auth;

class SiteController extends Controller
{
    public function index()
    {
        $sites = Site::orderBy('id', 'desc')->get();
        $employees = EmployeeInformation::orderBy('id', 'desc')->get();
        return view('backend.pages.purchasing.maintenance.site', compact('sites', 'employees'), ["type"=>"full-view"]);
    }

    public function getEmployee($id)
    {
        $employees = EmployeeInformation::where('id', $id)->get();
        return response()->json($employees);
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_name' => ['required'],
            'location' => ['required'],
            'person_in_charge' => ['required']
        ]);

        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        Site::create($request->all());

        return redirect()->back()->with('success','Successfully Added');
    }

    public function get() {
        if(request()->ajax()) {
            return datatables()->of(Site::with('employee')->orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function edit($id)
    {
        $sites = Site::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('sites'));
    }

    public function update(Request $request, $id)
    {
        Site::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            Site::find($item)->delete();
        }

        return 'Record Deleted';
    }

    public function getRecord(Request $request)
    {
        $sites = Site::get();
        return response()->json(compact('sites'));
    }
}
