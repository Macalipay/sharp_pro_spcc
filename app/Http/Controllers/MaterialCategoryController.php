<?php

namespace App\Http\Controllers;

use Auth;
use App\MaterialCategory;
use Illuminate\Http\Request;

class MaterialCategoryController extends Controller
{

    public function index()
    {
        $material_categories = MaterialCategory::orderBy('id', 'desc')->get();
        return view('backend.pages.purchasing.maintenance.material_category', compact('material_categories'), ["type"=>"full-view"]);
    }

   
    public function store(Request $request)
    {
        $request->validate([
            'description' => ['required', 'string'],
        
        ]);

        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        MaterialCategory::create($request->all());

        return redirect()->back()->with('success','Successfully Added');
    }

    public function get() {
        if(request()->ajax()) {
            return datatables()->of(MaterialCategory::get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function edit($id)
    {
        $material_categories = MaterialCategory::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('material_categories'));
    }

    public function update(Request $request, $id)
    {
        MaterialCategory::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            MaterialCategory::find($item)->delete();
        }

        return 'Record Deleted';
    }
}