<?php

namespace App\Http\Controllers;

use Auth;
use App\Discount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function store(Request $request)
    {
        if($request->po_type === "all") {
            $validatedData = $request->validate([
                'po_type' => ['required'],
                'po_id' => ['required'],
                'discount_type' => ['required'],
                'value' => ['required']
            ]);
    
            $request['workstation_id'] = Auth::user()->workstation_id;
            $request['created_by'] = Auth::user()->id;
            $request['updated_by'] = Auth::user()->id;
        
            Discount::create($request->all());
        }
        else {
            foreach($request->data as $item) {
                if($item['value'] !== "" && $item['value'] !== null) {
                    $data = [
                        "name" => $item['name'],
                        "remarks" => $item['remarks'],
                        "po_type" => 'item',
                        "po_id" => $item['po_id'],
                        "discount_type" => $item['discount_type'],
                        "value" => $item['value'],
                        "workstation_id" => Auth::user()->workstation_id,
                        "created_by" => Auth::user()->id,
                        "updated_by" => Auth::user()->id
                    ];
                    Discount::create($data);
                }
                else {
                    $data = [
                        "name" => '',
                        "remarks" => '',
                        "po_type" => 'item',
                        "po_id" => $item['po_id'],
                        "discount_type" => 'percentage',
                        "value" => '0',
                        "workstation_id" => Auth::user()->workstation_id,
                        "created_by" => Auth::user()->id,
                        "updated_by" => Auth::user()->id
                    ];
                    Discount::create($data);
                }
            }
        }
    }
    
    public function update(Request $request, $id)
    {
        if($id === "all") {
            foreach($request->data as $item) {
                if($item['value'] !== "" && $item['value'] !== null) {
                    if(Discount::where('po_id', $item['po_id'])->where('po_type', 'item')->count() !== 0) {
                        $data = [
                            "name" => $item['name'],
                            "remarks" => $item['remarks'],
                            "po_type" => 'item',
                            "po_id" => $item['po_id'],
                            "discount_type" => $item['discount_type'],
                            "value" => $item['value'],
                            "workstation_id" => Auth::user()->workstation_id,
                            "created_by" => Auth::user()->id,
                            "updated_by" => Auth::user()->id
                        ];
                        Discount::where('po_id', $item['po_id'])->where('po_type', 'item')->update($data);
                    }
                    else {
                        $data = [
                            "name" => $item['name'],
                            "remarks" => $item['remarks'],
                            "po_type" => 'item',
                            "po_id" => $item['po_id'],
                            "discount_type" => $item['discount_type'],
                            "value" => $item['value'],
                            "workstation_id" => Auth::user()->workstation_id,
                            "created_by" => Auth::user()->id,
                            "updated_by" => Auth::user()->id
                        ];
                        Discount::create($data);
                    }
                }
                else {
                    if(Discount::where('po_id', $item['po_id'])->where('po_type', 'item')->count() !== 0) {
                        $data = [
                            "name" => '',
                            "remarks" => '',
                            "po_type" => 'item',
                            "po_id" => $item['po_id'],
                            "discount_type" => 'percentage',
                            "value" => '0',
                            "workstation_id" => Auth::user()->workstation_id,
                            "created_by" => Auth::user()->id,
                            "updated_by" => Auth::user()->id
                        ];
                        Discount::where('po_id', $item['po_id'])->where('po_type', 'item')->update($data);
                    }
                    else {
                        $data = [
                            "name" => '',
                            "remarks" => '',
                            "po_type" => 'item',
                            "po_id" => $item['po_id'],
                            "discount_type" => 'percentage',
                            "value" => '0',
                            "workstation_id" => Auth::user()->workstation_id,
                            "created_by" => Auth::user()->id,
                            "updated_by" => Auth::user()->id
                        ];
                        Discount::create($data);
                    }
                }
            }
        } 
        else {
            Discount::find($id)->update($request->all());
        }
        return "Record Saved";
    }
    
    public function destroy(Request $request)
    {
        Discount::find($request->id)->delete();

        return 'Record Deleted';
    }
}
