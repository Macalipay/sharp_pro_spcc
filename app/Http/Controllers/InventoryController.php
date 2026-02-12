<?php

namespace App\Http\Controllers;

use App\AuditTrails;
use App\DeliveryReceipt;
use App\Inventory;
use App\Materials;
use App\Employment;
use App\Project;
use App\InventoryDamage;
use App\InventoryTransaction;
use App\InventoryTransfer;
use App\User;
use Illuminate\Http\Request;
use Auth;
use SebastianBergmann\Environment\Console;

class InventoryController extends Controller
{
    public function index()
    {
        $inventory = Inventory::orderBy('id', 'desc')->get();
        $materials = Materials::orderBy('id', 'desc')->get();
        $projects = Project::orderBy('id', 'desc')->get();
        $employees = Employment::with('employee_information')->orderBy('id', 'desc')->get();
        return view('backend.pages.inventory.transaction.inventory', compact('inventory', 'materials', 'projects', 'employees'), ["type"=>"full-view"]);
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'material_id' => ['required'],
            'project_id' => ['required'],
            'description' => ['required'],
            'critical_level' => ['required'],
        ]);

        $exists = Inventory::where('material_id', $validatedData['material_id'])
            ->where('project_id', $validatedData['project_id'])
            ->first();

        if (!$exists) {
            $request['workstation_id'] = Auth::user()->workstation_id;
            $request['created_by'] = Auth::user()->id;
            $request['updated_by'] = Auth::user()->id;

            $newInventory = Inventory::create($request->all());
            AuditTrails::create([
                'item_id' => $validatedData['material_id'],
                'project_id' => $validatedData['project_id'],
                'event_type' => 'Adjustment',
                'remarks' => 'Created Manually',
                'created_by' => Auth::user()->id,
            ]);

        } else {
            AuditTrails::create([
                'item_id' => $validatedData['material_id'],
                'project_id' => $validatedData['project_id'],
                'event_type' => 'adjustment',
                'remarks' => $exists->description ?? 'Adjustment made',
                'created_by' => Auth::user()->id,
            ]);
        }

        return response()->json(['message' => 'Processed successfully.']);
    }

    public function damage(Request $request, $id)
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

        InventoryDamage::create($request->all());

        $inventory_item = Inventory::where('id', $request->inventory_id)->first();
        $quantity_stock = $inventory_item->quantity_stock - $request->quantity;
        
        $total_count = $inventory_item->total_count - $request->quantity;

        if($quantity_stock > $inventory_item->critical_level) {
            Inventory::where('id', $request->inventory_id)->update(['total_count' => $total_count ,'quantity_stock' => $quantity_stock, 'status' => 'Good']);
        } else if ($quantity_stock <= $inventory_item->critical_level && $quantity_stock > 0) {
            Inventory::where('id', $request->inventory_id)->update(['total_count' => $total_count , 'quantity_stock' => $quantity_stock, 'status' => 'Critical Level']);
        } else {
            Inventory::where('id', $request->inventory_id)->update(['total_count' => $total_count , 'quantity_stock' => $quantity_stock, 'status' => 'Out of Stock']);
        }

        AuditTrails::create([
            'item_id' => $inventory_item->material_id,
            'sent_quantity' => $request->quantity,
            'amount' => 0,
            'remark' => $request->remarks,
            'event_type' => $request->conflict_type,
            'project_id' => $inventory_item->project_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function transaction(Request $request, $id)
    {
        $validatedData = $request->validate([
            'code' => ['required'],
            'requested_by' => ['nullable'],
            'issued_by' => ['nullable'],
            'approved_by' => ['nullable'],
            'code' => ['required'],
            'quantity' => ['required'],
            'date' => ['required'],
            'remarks' => ['required'],
        ]);

        $request['inventory_id'] = $id;
        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        InventoryTransaction::create($request->all());

        $inventory_item = Inventory::where('id', $request->inventory_id)->first();
        $quantity_stock = $inventory_item->quantity_stock + $request->quantity;
        $total_count = $inventory_item->total_count + $request->quantity;

        if($quantity_stock > $inventory_item->critical_level) {
            Inventory::where('id', $request->inventory_id)->update(['total_count' => $total_count ,'quantity_stock' => $quantity_stock, 'status' => 'GOOD']);
        } else if ($quantity_stock <= $inventory_item->critical_level && $quantity_stock > 0) {
            Inventory::where('id', $request->inventory_id)->update(['total_count' => $total_count , 'quantity_stock' => $quantity_stock, 'status' => 'CRITICAL LEVEL']);
        } else {
            Inventory::where('id', $request->inventory_id)->update(['total_count' => $total_count , 'quantity_stock' => $quantity_stock, 'status' => 'OUT OF STOCK']);
        }

        AuditTrails::create([
            'item_id' => $inventory_item->material_id,
            'sent_quantity' => $request->quantity,
            'amount' => 0,
            'remark' => $request->remarks,
            'event_type' => 'ADJUSTMENT(ADD)',
            'project_id' => $inventory_item->project_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function transfer(Request $request, $id)
{
    $validatedData = $request->validate([
        'to_project' => ['required'],
        'transfer_quantity' => ['required'],
        'transfer_date' => ['required'],
        'transfer_remarks' => ['required'],
        'unit_price'=> ['required'],
        'total_amt'=> ['required'],
    ]);

    $inventory_item = Inventory::where('id', $request->inventory_id)->first();

    $request['inventory_id'] = $id;
    $request['from_project'] = $inventory_item->project_id;
    $request['material_id'] = $inventory_item->material_id;
    $request['workstation_id'] = Auth::user()->workstation_id;
    $request['created_by'] = Auth::user()->id;
    $request['updated_by'] = Auth::user()->id;

    $transfer = InventoryTransfer::create($request->all());

    // --- FROM PROJECT ---
    $quantity_stock = $inventory_item->quantity_stock - $request->transfer_quantity;
    $total_count   = $inventory_item->total_count - $request->transfer_quantity;

    if ($quantity_stock > $inventory_item->critical_level) {
        $inventory_item->update(['total_count' => $total_count, 'quantity_stock' => $quantity_stock, 'status' => 'GOOD']);
    } elseif ($quantity_stock <= $inventory_item->critical_level && $quantity_stock > 0) {
        $inventory_item->update(['total_count' => $total_count, 'quantity_stock' => $quantity_stock, 'status' => 'CRITICAL LEVEL']);
    } else {
        $inventory_item->update(['total_count' => $total_count, 'quantity_stock' => $quantity_stock, 'status' => 'OUT OF STOCK']);
    }

    // --- TO PROJECT ---
    $to_project = Inventory::where('project_id', $request->to_project)
        ->where('material_id', $inventory_item->material_id)
        ->first();

    if ($to_project) {
        $transfer_quantity_stock = $to_project->quantity_stock + $request->transfer_quantity;
        $transfer_total_count   = $to_project->total_count + $request->transfer_quantity;

        if ($transfer_quantity_stock > $to_project->critical_level) {
            $to_project->update(['total_count' => $transfer_total_count, 'quantity_stock' => $transfer_quantity_stock, 'status' => 'GOOD']);
        } elseif ($transfer_quantity_stock <= $to_project->critical_level && $transfer_quantity_stock > 0) {
            $to_project->update(['total_count' => $transfer_total_count, 'quantity_stock' => $transfer_quantity_stock, 'status' => 'CRITICAL LEVEL']);
        } else {
            $to_project->update(['total_count' => $transfer_total_count, 'quantity_stock' => $transfer_quantity_stock, 'status' => 'OUT OF STOCK']);
        }
    } else {
        $inventory = [
            "project_id"     => $request->to_project,
            "material_id"    => $inventory_item->material_id,
            "critical_level" => "0",
            "total_count"    => $request->transfer_quantity,
            "quantity_stock" => $request->transfer_quantity,
            "workstation_id" => Auth::user()->workstation_id,
            "created_by"     => Auth::user()->id,
            "updated_by"     => Auth::user()->id,
            "status"         => 'GOOD',
        ];

        Inventory::create($inventory);

        AuditTrails::create([
            'item_id'      => $inventory_item->material_id,
            'sent_quantity'=> $request->transfer_quantity,
            'amount'       => 0,
            'remark'       => $request->transfer_remarks,
            'event_type'   => 'TRANSFER',
            'project_id'   => $request->to_project,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    return response()->json([
        'success' => true,
        'message' => 'Transfer saved successfully!',
        'data'    => $transfer
    ]);
}


    public function get() {
        if(request()->ajax()) {
            return datatables()->of(Inventory::with('material', 'project')->orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function edit($id)
    {
        $inventory = Inventory::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('inventory'));
    }

    public function update(Request $request, $id)
    {
        $request['tax_applicable'] = isset($request['tax_applicable'])?1:0;
        $request['government_mandated_benefits'] = isset($request['government_mandated_benefits'])?1:0;
        $request['other_company_benefits'] = isset($request['other_company_benefits'])?1:0;

        Inventory::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            Inventory::find($item)->delete();
        }

        return 'Record Deleted';
    }

    public function AuditTrails($id){
        $inventory = Inventory::with(['material', 'project'])->findOrFail($id);
        $material_id = $inventory->material_id;
        $project_id = $inventory->project_id;

        $running_balance = $inventory->total_count;

        $audits = AuditTrails::where('audit_trails.item_id', $material_id)
            ->where('audit_trails.project_id', $project_id)
            ->leftJoin('purchase_order_details', 'audit_trails.purchase_order_detail_id', 'purchase_order_details.id')
            ->select(
                'audit_trails.*',
                'purchase_order_details.unit_price as unit_price',
                'purchase_order_details.quantity as quantity',
                'purchase_order_details.created_by'
            )
            ->whereNotNull('audit_trails.created_at')
            ->orderBy('audit_trails.created_at')
            ->get();

            $userIds = $audits->pluck('created_by')->filter()->unique();
            $users = User::whereIn('id', $userIds)->get();
        
       $audits = $audits->map(function ($audit) use (&$running_balance, $users) {
            $unit_price = is_numeric($audit->unit_price) ? (float) $audit->unit_price : 0;
            $sent_qty = is_numeric($audit->sent_quantity) ? (float) $audit->sent_quantity : 0;

            $event_type = strtoupper($audit->event_type ?? '');
            $beginning = $running_balance;

            if (in_array($event_type, ['DAMAGE', 'CONSUME', 'THEFT','RETURN'])) {
                $running_balance -= $sent_qty;
            } else {
                $running_balance += $sent_qty;
            }

            $total_amount = $unit_price * $sent_qty;
           
            $user = $users->where('id', $audit->created_by)->first();
            $fullName = $user ? $user->firstname . ' ' . $user->lastname : 'Unknown';

            return [
                'type' => $audit->event_type ?? $audit->remark ?? 'Processed',
                'date' => $audit->created_at,
                'quantity_processed' => $sent_qty,
                'remarks' => $audit->remarks ?? $audit->remark,
                'ref_no' => $audit->dr_sequence ?? 'N/A',
                'unit_price' => $unit_price,
                'total_amount' => $total_amount,
                'beginning_balance' => $beginning,
                'ending_balance' => $running_balance,
                'updated_by' => $fullName
            ];
        });

        return response()->json([
            'inventory' => $inventory,
            'events' => $audits,
        ]);
    }
}
