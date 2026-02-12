<?php

namespace App\Http\Controllers;

use App\InventoryTransfer;
use Illuminate\Http\Request;

class InventoryTransferController extends Controller
{
    public function index()
    {
        return view('backend.pages.inventory.transaction.transfer_history', ["type"=>"full-view"]);
    }

    public function get() {
        if(request()->ajax()) {
            return datatables()->of(InventoryTransfer::orderBy('id', 'desc')->with('inventory', 'inventory.material', 'inventory.project', 'project')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'description' => ['required'],
        ]);

        if (!InventoryTransaction::where('description', $validatedData['description'])->exists()) {

            $request['workstation_id'] = Auth::user()->workstation_id;
            $request['created_by'] = Auth::user()->id;
            $request['updated_by'] = Auth::user()->id;

            InventoryTransaction::create($request->all());
        }
        else {
            return false;
        }
    }

    public function edit($id)
    {
        $inventory_histories = InventoryTransaction::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('inventory_histories'));
    }

    public function update(Request $request, $id)
    {
        $request['updated_by'] = Auth::user()->id;
        InventoryTransaction::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            InventoryTransaction::find($item)->delete();
        }

        return 'Record Deleted';
    }
}
