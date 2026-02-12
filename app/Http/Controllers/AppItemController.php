<?php

namespace App\Http\Controllers;

use App\AppItem;
use Illuminate\Http\Request;

class AppItemController extends Controller
{
    public function index()
    {
        $app_items = AppItem::orderBy('id', 'desc')->get();
        return view('backend.pages.setup.application.index', compact('app_items'));
    }

    public function store(Request $request)
    {
        $class = $request->validate([
            'description' => ['required']
        ]);

        AppItem::create($request->all());

        return redirect()->back()->with('success','Successfully Added');
    }

    public function get() {
        if(request()->ajax()) {
            return datatables()->of(AppItem::get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function edit($id)
    {
        $app_items = AppItem::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('app_items'));
    }
    
    public function update(Request $request, $id)
    {
        $request['tax_applicable'] = isset($request['tax_applicable'])?1:0;
        $request['government_mandated_benefits'] = isset($request['government_mandated_benefits'])?1:0;
        $request['other_company_benefits'] = isset($request['other_company_benefits'])?1:0;
        
        AppItem::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            AppItem::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
}
