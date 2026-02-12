<?php

namespace App\Http\Controllers;

use Auth;
use App\MaterialCategory;
use App\Materials;
use App\MaterialUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialsController extends Controller
{
    public function index()
    {
        $materials = Materials::orderBy('id', 'desc')->get();
        $material_categories = MaterialCategory::orderBy('id', 'desc')->get();
        return view('backend.pages.purchasing.maintenance.materials', compact('materials','material_categories'), ["type"=>"full-view"]);
    }
   
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name'        => ['required', 'string'],
            'item_code'        => ['required', 'string'],
            'category'         => ['required', 'string'],
            'brand'            => ['required', 'string'],
            'unit_of_measure'  => 'required',
        ]);

        $units = (array) $request->unit_of_measure;
        $authId = Auth::id();

        $material = Materials::create([
            'item_name'      => $validated['item_name'],
            'item_code'      => $validated['item_code'],
            'category'       => $validated['category'],
            'brand'          => $validated['brand'],
            'workstation_id' => Auth::user()->workstation_id,
            'created_by'     => $authId,
            'updated_by'     => $authId,
        ]);

        $material->units()->createMany(
            collect($units)->map(function ($unit) {
                return [
                    'unit_of_measure' => $unit,
                    'description'     => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            })->toArray()
        );

        return redirect()->back()->with('success', 'Successfully Added');
    }

    public function get() {
        if(request()->ajax()) {
            $materials = Materials::with('units')->get();

            return datatables()->of($materials)
                ->addIndexColumn()
                ->addColumn('unit_of_measure', function($row) {
                    return $row->units->pluck('unit_of_measure')->implode(', ');
                })
                ->make(true);
        }
    }

    public function getUnits($id){
        return MaterialUnit::where('material_id', $id)->pluck('unit_of_measure');
    }

    public function edit($id)
    {
        $materials = Materials::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('materials'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'item_name'        => ['required', 'string'],
            'item_code'        => ['required', 'string'],
            'category'         => ['required', 'string'],
            'brand'            => ['required', 'string'],
            'unit_of_measure'  => 'required',
        ]);

        $units = (array) $request->unit_of_measure;
        $authId = Auth::id();

        $material = Materials::findOrFail($id);

        $material->update([
            'item_name'  => $validated['item_name'],
            'item_code'  => $validated['item_code'],
            'category'   => $validated['category'],
            'brand'      => $validated['brand'],
            'updated_by' => $authId,
        ]);

        $material->units()->delete();

        $material->units()->createMany(
            collect($units)->map(function ($unit) {
                return [
                    'unit_of_measure' => $unit,
                    'description'     => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            })->toArray()
        );

        return redirect()->back()->with('success', 'Successfully Updated');
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            Materials::find($item)->delete();
        }

        return 'Record Deleted';
    }
}
