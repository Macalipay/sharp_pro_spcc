<?php

namespace App\Http\Controllers;

use App\App;
use Illuminate\Http\Request;

class AppController extends Controller
{
    public function index()
    {
        $apps = App::orderBy('id', 'desc')->get();
        return view('backend.pages.setup.application.index', compact('apps'));
    }

    public function store(Request $request)
    {
        $class = $request->validate([
            'description' => ['required']
        ]);

        App::create($request->all());

        return redirect()->back()->with('success','Successfully Added');
    }

    public function get() {
        if(request()->ajax()) {
            return datatables()->of(App::get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function edit($id)
    {
        $apps = App::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('apps'));
    }
    
    public function update(Request $request, $id)
    {
        $request['tax_applicable'] = isset($request['tax_applicable'])?1:0;
        $request['government_mandated_benefits'] = isset($request['government_mandated_benefits'])?1:0;
        $request['other_company_benefits'] = isset($request['other_company_benefits'])?1:0;
        
        App::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            App::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
}
