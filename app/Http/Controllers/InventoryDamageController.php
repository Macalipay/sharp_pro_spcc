<?php

namespace App\Http\Controllers;

use App\InventoryDamage;
use Illuminate\Http\Request;

class InventoryDamageController extends Controller
{
    public function index()
    {
        $inventory_damages = InventoryDamage::orderBy('id', 'desc')->get();
        return view('backend.pages.inventory.transaction.damage', compact('inventory_damages'), ["type"=>"full-view"]);
    }

    public function get() {
        if(request()->ajax()) {
            return datatables()->of(InventoryDamage::orderBy('id', 'desc')->with('inventory', 'inventory.material', 'inventory.project')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'description' => ['required'],
        ]);

        if (!InventoryDamage::where('description', $validatedData['description'])->exists()) {

            $request['workstation_id'] = Auth::user()->workstation_id;
            $request['created_by'] = Auth::user()->id;
            $request['updated_by'] = Auth::user()->id;

            InventoryDamage::create($request->all());
        }
        else {
            return false;
        }
    }

    public function edit($id)
    {
        $inventory_damages = InventoryDamage::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('inventory_damages'));
    }

    public function update(Request $request, $id)
    {
        $request['updated_by'] = Auth::user()->id;
        InventoryDamage::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            InventoryDamage::find($item)->delete();
        }

        return 'Record Deleted';
    }
}
