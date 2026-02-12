<?php

namespace App\Http\Controllers;

use App\Supplier;
use Illuminate\Http\Request;
use Auth;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('id', 'desc')->get();
        return view('backend.pages.purchasing.maintenance.supplier', compact('suppliers'), ["type"=>"full-view"]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_name' => ['required', 'string'],
            'contact_no' => ['required', 'string'],
            'contact_person' => ['required', 'string'],
            'address' => ['required', 'string'],
            'tin_no' => ['required', 'string'],
            'payment_terms' => ['required', 'string'],
            'bank_name' => ['nullable', 'string'],
            'bank_account' => ['nullable', 'string'],
            'vatable' => ['required', 'string'],

        ]);

        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        Supplier::create($request->all());

        return redirect()->back()->with('success','Successfully Added');
    }

    public function get() {
        if(request()->ajax()) {
            return datatables()->of(Supplier::get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function edit($id)
    {
        $suppliers = Supplier::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('suppliers'));
    }

    public function update(Request $request, $id)
    {
        Supplier::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            Supplier::find($item)->delete();
        }

        return 'Record Deleted';
    }
}
