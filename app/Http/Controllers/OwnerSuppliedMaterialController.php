<?php

namespace App\Http\Controllers;

use App\OwnerSuppliedMaterial;
use App\OwnerSuppliedMaterialTransaction;
use App\Materials;
use App\Project;
use Illuminate\Http\Request;
use Auth;

class OwnerSuppliedMaterialController extends Controller
{
    public function index()
    {
        $materials = Materials::orderBy('id', 'desc')->get();
        $projects = Project::orderBy('id', 'desc')->get();
        return view('backend.pages.inventory.transaction.owner_supplied_material', compact('materials', 'projects'), ["type"=>"full-view"]);
    }

    public function get() {
        if(request()->ajax()) {
            return datatables()->of(OwnerSuppliedMaterial::orderBy('id', 'desc')->with('material', 'project')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'project_id' => ['required'],
            'material_id' => ['required'],
            'description' => ['required'],
        ]);

        if (!OwnerSuppliedMaterial::where('project_id', $validatedData['project_id'])
                                  ->where('material_id', $validatedData['material_id'])->exists()) {

            $request['workstation_id'] = Auth::user()->workstation_id;
            $request['created_by'] = Auth::user()->id;
            $request['updated_by'] = Auth::user()->id;
            $request['quantity'] = 0;
            $request['total_count'] = 0;

            OwnerSuppliedMaterial::create($request->all());
        }
        else {
            return false;
        }
    }

    public function transaction(Request $request, $id)
    {
        $validatedData = $request->validate([
            'quantity' => ['required'],
            'date' => ['required'],
            'remarks' => ['required'],
        ]);

        $request['inventory_id'] = $id;
        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        OwnerSuppliedMaterialTransaction::create($request->all());

        $inventory_item = OwnerSuppliedMaterial::where('id', $request->inventory_id)->first();
        $quantity_stock = $inventory_item->quantity_stock + $request->quantity;
        $total_count = $inventory_item->total_count + $request->quantity;

        if($quantity_stock > $inventory_item->critical_level) {
            OwnerSuppliedMaterial::where('id', $request->inventory_id)->update(['total_count' => $total_count ,'quantity_stock' => $quantity_stock]);
        } else if ($quantity_stock <= $inventory_item->critical_level && $quantity_stock > 0) {
            OwnerSuppliedMaterial::where('id', $request->inventory_id)->update(['total_count' => $total_count , 'quantity_stock' => $quantity_stock]);
        } else {
            OwnerSuppliedMaterial::where('id', $request->inventory_id)->update(['total_count' => $total_count , 'quantity_stock' => $quantity_stock]);
        }
    }

    public function edit($id)
    {
        $inventory_histories = OwnerSuppliedMaterial::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('inventory_histories'));
    }

    public function update(Request $request, $id)
    {
        $request['updated_by'] = Auth::user()->id;
        OwnerSuppliedMaterial::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            OwnerSuppliedMaterial::find($item)->delete();
        }

        return 'Record Deleted';
    }
}
