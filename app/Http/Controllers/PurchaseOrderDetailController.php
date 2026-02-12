<?php

namespace App\Http\Controllers;

use App\PurchaseOrderDetail;
use App\PurchaseOrderDetailsSplit;
use App\PurchaseOrder;
use App\Supplier;
use App\Site;
use App\EmployeeInformation;
use Illuminate\Http\Request;
use Auth;

class PurchaseOrderDetailController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'purchase_order_id' => ['required', 'integer'],
            'item' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'quantity' => ['required', 'numeric'],
            'unit_price' => ['required', 'numeric'],
            'tax_rate' => ['required', 'string'],
            'total_amount' => ['required', 'numeric'],
            'split' => ['nullable', 'string'],
            'unit_measure'=>['required'],
        ]);

        $record = PurchaseOrder::findOrFail($request->purchase_order_id);
        
        $detail = new PurchaseOrderDetail([
            'purchase_order_id' => $request->purchase_order_id,
            'item' => $request->item,
            'description' => $request->description,
            'quantity' => $request->quantity,
            'unit_price' => $request->unit_price,
            'tax_rate' => $request->tax_rate,
            'total_amount' => $request->total_amount,
            'unit_measure' => $request->unit_measure,
            'discount' => 0,
            'workstation_id' => Auth::user()->workstation_id,
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id,
        ]);
        $detail->save();
        
        $total = PurchaseOrderDetail::where('purchase_order_id', $request->purchase_order_id)
            ->sum('total_amount');

        $record->update([
            'subtotal' => $total,
            'total_with_tax' => $total
        ]);

        return response()->json(compact('record'));
    }

    public function get($id) {
        if(request()->ajax()) {
            return datatables()->of(PurchaseOrderDetail::with('purchase_order')->where('purchase_order_id', $id)->orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function edit($id)
    {
        $purchase_order_details = PurchaseOrderDetail::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('purchase_order_details'));
    }

    public function update(Request $request, $id)
    {

        $detail_old_total = PurchaseOrderDetail::where('id', $request->purchase_order_detail_id)->first();
        $detail_new_total = ($request->total_amount - $detail_old_total->total_amount);
        $record = PurchaseOrder::where('id', $request->purchase_order_id)->first();
        $new_total = $record->subtotal + $detail_new_total;

        PurchaseOrder::where('id', $request->purchase_order_id)->update(['subtotal' => $new_total, 'total_with_tax' => $new_total]);

        PurchaseOrderDetail::find($id)->update($request->all());

        return "Record Saved";
    }

    public function destroy(Request $request)
    {

        $record = $request->data;

        foreach($record as $item) {
            $data = PurchaseOrderDetail::where('id', $item)->first();
            
            PurchaseOrderDetail::find($item)->delete();

            $total = PurchaseOrderDetail::where('purchase_order_id', $data->purchase_order_id)->sum('total_amount');

            PurchaseOrder::where('id', $data->purchase_order_id)->update(['subtotal' => $total, 'total_with_tax' => $total]);
        }

        return 'Record Deleted';
    }

    public function split(Request $request) {
        $count = PurchaseOrderDetailsSplit::where('purchased_order_details_id', $request->po_id)->count();

        if($count === 0) {
            foreach ($request->data as $item) {
                PurchaseOrderDetailsSplit::create($item);
            }
        }
        else {
            PurchaseOrderDetailsSplit::where('purchased_order_details_id', $request->po_id)->delete();
            foreach ($request->data as $item) {
                PurchaseOrderDetailsSplit::create($item);
            }
        }
    }
    
    public function getSplit($id) {
        $record = PurchaseOrderDetailsSplit::where('purchased_order_details_id', $id)->get();
        return response()->json(compact('record'));
    }
}
