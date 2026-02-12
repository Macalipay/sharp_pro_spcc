<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
use App\DeliveryReceipt;
use App\PurchaseOrder;
use App\PurchaseOrderDetail;
use App\Supplier;
use App\Project;
use App\Inventory;
use App\Site;
use App\InventoryTransaction;
use App\Materials;
use App\ProjectSplit;
use App\EmployeeInformation;
use App\PurchaseOrderDetailsSplit;
use App\AuditTrails;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Auth;
use Illuminate\Support\Facades\DB;

class DeliveryReceiptController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $statuses = [
            "SENT TO SUPPLIER" => "view_Partially Delivered",
            "PARTIALLY DELIVERED" => "view_Partially Delivered",
            "COMPLETED" => "view_Completed",
            "NOT DELIVERED" => "view_Not Delivered",
            "CANCELLED" => "view_Cancelled",
        ];
    
        $status = [];
    
        foreach ($statuses as $key => $permission) {
            if ($user->can($permission)) {
                $status[$key] = PurchaseOrder::where('status', str_replace(" ", "_", $key))->count();
            }
        }
    
        $employees = EmployeeInformation::orderBy('id', 'desc')->get();
        $suppliers = Supplier::orderBy('id', 'desc')->get();
        $projects = Project::get();
        $materials = Materials::get();
        
        $items = DB::table('purchase_orders')
        ->join('purchase_order_details as pod', 'purchase_orders.id','pod.purchase_order_id')
        ->join('materials', 'pod.item','materials.id')
        ->leftJoin('delivery_receipts as dr', 'dr.purchase_order_id','pod.purchase_order_id')
        ->select(
            'pod.id as pod_id','materials.item_name as material_name','pod.quantity as quantity_set','purchase_orders.status','dr.*'
        )
        ->get();    

        return view('backend.pages.purchasing.transaction.delivery_receipt.index', compact('employees', 'suppliers', 'projects', 'status', 'materials','items'), ["type" => "full-view"]);
    }

    public function inventoryIndex()
    {
        $status = array(
            "SENT TO SUPPLIER" => PurchaseOrder::where('status', 'SENT_TO_SUPPLIER')->count(),
            "PARTIALLY DELIVERED" => PurchaseOrder::where('status', 'PARTIALLY_DELIVERED')->count(),
            "COMPLETED" => PurchaseOrder::where('status', 'COMPLETED')->count(),
            "NOT DELIVERED" => PurchaseOrder::where('status', 'NOT_DELIVERED')->count(),
            "CANCELLED" => PurchaseOrder::where('status', 'CANCELLED')->count(),
        );

        $employees = EmployeeInformation::orderBy('id', 'desc')->get();
        $suppliers = Supplier::orderBy('id', 'desc')->get();
        $projects = Project::get();
        $materials = Materials::get();
        $sites = Site::get();
        
        return view('backend.pages.inventory.transaction.inventory_request', compact('employees', 'sites', 'suppliers', 'projects', 'status', 'materials'), ["type"=>"full-view"]);

    }


    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => ['required', 'integer'],
            'delivery_date' => ['required', 'date'],
            'po_date' => ['required', 'date'],
            'contact_no' => ['required', 'string'],
            'reference' => ['required', 'string'],
            'terms' => ['required', 'string'],
            'due_date' => ['required', 'date'],
            'tax_type' => ['required', 'string'],
            'subtotal' => ['required', 'numeric'],
            'total_with_tax' => ['required', 'numeric'],
            'delivery_instruction' => ['required', 'string'],
        ]);

        $last_order = PurchaseOrder::orderBy('id', 'desc')->first();

        $new_id = $last_order!==null?$last_order->id + 1:1;

        $request['order_no'] = 'PO-'.str_pad($new_id, 5, '0', STR_PAD_LEFT);;
        $request['prepared_by'] = Auth::user()->id;
        $request['prepared_at'] = date('Y-m-d');
        // $request['reviewed_by'] = Auth::user()->id;
        // $request['approved_by'] = Auth::user()->id;
        // $request['received_by'] = Auth::user()->id;
        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        $po = PurchaseOrder::create($request->all());

        $project = [
            "po_id" => $po->id,
            "project_id" => $request->project,
            "percentage" => "100",
            "amount" => $request->subtotal
        ];

        ProjectSplit::create($project);

        return redirect()->back()->with('success','Successfully Added');
    }

    public function get($status) {
        $status_count = array(
            "SENT TO SUPPLIER" => PurchaseOrder::where('status', 'SENT_TO_SUPPLIER')->count(),
            "PARTIALLY DELIVERED" => PurchaseOrder::where('status', 'PARTIALLY_DELIVERED')->count(),
            "COMPLETED" => PurchaseOrder::where('status', 'COMPLETED')->count(),
            "NOT DELIVERED" => PurchaseOrder::where('status', 'NOT_DELIVERED')->count(),
            "CANCELLED" => PurchaseOrder::where('status', 'CANCELLED')->count(),
        );

        if(request()->ajax()) {
            return datatables()->of(PurchaseOrder::with('supplier', 'prepared_by', 'reviewed_by', 'approved_by', 'received_by', 'details')->where('status', $status)->orderBy('id', 'asc')->get())
            ->with('status', $status_count)
            ->make(true);
        }
    }

    public function edit($id)
    {
        $purchase_orders = PurchaseOrder::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('purchase_orders'));
    }

    public function print(Request $request, $id){
        $action = $request->input('action');
        $itemCode = $request->input('item_code');

        $po_header = DeliveryReceipt::query()
            ->join('purchase_orders', 'delivery_receipts.purchase_order_id','purchase_orders.id')
            ->join('suppliers', 'purchase_orders.supplier_id','suppliers.id')
            ->leftJoin('users as prepared', 'purchase_orders.prepared_by','prepared.id')
            ->leftJoin('users as reviewed', 'purchase_orders.reviewed_by','reviewed.id')
            ->leftJoin('users as approved', 'purchase_orders.approved_by','approved.id')
            ->where('delivery_receipts.purchase_order_id', $id)
            ->select([
                'purchase_orders.id',
                'purchase_orders.subtotal',
                'purchase_orders.po_date',
                'purchase_orders.order_no',
                'suppliers.supplier_name',
                'suppliers.address',
                'purchase_orders.terms',
                'purchase_orders.due_date',
                'purchase_orders.prepared_by',
                'purchase_orders.prepared_at',

                'purchase_orders.reviewed_by',
                'purchase_orders.reviewed_at',

                'purchase_orders.approved_by',
                'purchase_orders.approved_at',

                'purchase_orders.received_by',
                'purchase_orders.received_at',

                'delivery_receipts.dr_sequence',
                DB::raw("CONCAT_WS(' ', prepared.firstname, prepared.middlename, prepared.lastname, prepared.suffix) AS prepared_by_name"),
                DB::raw("CONCAT_WS(' ', reviewed.firstname, reviewed.middlename, reviewed.lastname, reviewed.suffix) AS reviewed_by_name"),
                DB::raw("CONCAT_WS(' ', approved.firstname, approved.middlename, approved.lastname, approved.suffix) AS approved_by_name"),
            ])
            ->first();

            $deliveryReceipt = DeliveryReceipt::where('purchase_order_id', $id)->first();
            $project = null;
            if ($deliveryReceipt && $deliveryReceipt->projectSplit) {
                $project = Project::find($deliveryReceipt->projectSplit->project_id);
            }

            if ($action === 'preview' && empty($itemCode)) {
                return response()->json([
                    'purchase_order' => $po_header,
                    'details' => [],
                    'chart' => ChartOfAccount::get(),
                    'action' => $action,
                    'project' => $project,
                ]);
            }

            $poDetailsQuery = PurchaseOrderDetail::join('delivery_receipts', 'purchase_order_details.id', 'delivery_receipts.purchase_order_detail_id')
                ->join('materials', 'purchase_order_details.item', 'materials.id')
                ->where('purchase_order_details.purchase_order_id', $id);

            if ($action === 'preview' && $itemCode) {
                $poDetailsQuery->where('materials.item_code', $itemCode);
            }

            $po_details = $poDetailsQuery->select(
                    'materials.id',
                    'materials.item_name',
                    'materials.item_code',
                    'purchase_order_details.description',
                    'materials.unit_of_measure',
                    'purchase_order_details.unit_price',
                    'purchase_order_details.total_amount',
                    'delivery_receipts.sent_quantity',
                    'delivery_receipts.dr_sequence'
                )
                ->get();

            return response()->json([
                'purchase_order' => $po_header,
                'details' => $po_details,
                'chart' => ChartOfAccount::get(),
                'action' => $action,
                'project' => $project,
            ]);
    }

    public function update(Request $request, $id)
    {
        PurchaseOrder::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            PurchaseOrder::find($item)->delete();
            PurchaseOrderDetail::where('purchase_order_id', $item)->delete();
        }

        return 'Record Deleted';
    }

    public function inventory($id)
    {
        $purchaseOrder = PurchaseOrder::find($id);

        if (!$purchaseOrder || $purchaseOrder->split_type !== 'single') {
            return;
        }

        $projectSplit = ProjectSplit::where('po_id', $id)->first();
        $materials = PurchaseOrderDetail::where('purchase_order_id', $id)->get();
        $userId = Auth::id();
        $now = now();

        foreach ($materials as $material) {
            InventoryTransaction::create([
                'inventory_id' => null,
                'material_id' => $material->item,
                'project_id' => $projectSplit->project_id,
                'quantity' => $material->quantity,
                'date' => $now,
                'remarks' => '--',
                'created_by' => $userId,
                'updated_by' => $userId,
                'po_id' => $id,
            ]);
        }
    }

    public function changeStatus(Request $request, $id)
    {
        $arr = [];

        switch($request->status) {
            case "SENT_TO_SUPPLIER":
                $arr = ['status' => $request->status];
                break;
            case "PARTIALLY_DELIVERED":
                $arr = ['status' => $request->status, 'received_by' => Auth::user()->id, 'received_at' => date('Y-m-d')];
                break;
            case "COMPLETED":
                $arr = ['status' => $request->status, 'received_by' => Auth::user()->id, 'received_at' => date('Y-m-d')];

                $deliveryReceipts = DeliveryReceipt::where('purchase_order_id', $id)->get();
                foreach ($deliveryReceipts as $receipt) {
                    $materialId = $receipt->item_id;
                    $sentQuantity = $receipt->sent_quantity;
                    $poDetailId = $receipt->purchase_order_detail_id;
                    $drSequence = $receipt->dr_sequence;
                    $drId = $receipt->id;

                    $projectId = null;
                    if (isset($receipt->project_id) && $receipt->project_id) {
                        $projectId = $receipt->project_id;
                    } else {
                        $projectSplit = ProjectSplit::where('po_id', $id)->first();
                        $projectId = $projectSplit ? $projectSplit->project_id : null;
                    }
                    if (!$projectId) continue;

                    $inventory = Inventory::where('project_id', $projectId)
                        ->where('material_id', $materialId)
                        ->first();
                    if ($inventory) {
                        $newTotal = $inventory->total_count + $sentQuantity;
                        $newStock = $inventory->quantity_stock + $sentQuantity;
                        $status = 'GOOD';
                        if ($newStock <= $inventory->critical_level && $newStock > 0) {
                            $status = 'CRITICAL LEVEL';
                        } elseif ($newStock <= 0) {
                            $status = 'OUT OF STOCK';
                        }
                        $inventory->update([
                            'total_count' => $newTotal,
                            'quantity_stock' => $newStock,
                            'status' => $status,
                            'updated_by' => Auth::user()->id,
                        ]);
                    } else {
                        $inventory = Inventory::create([
                            'project_id' => $projectId,
                            'material_id' => $materialId,
                            'total_count' => $sentQuantity,
                            'quantity_stock' => $sentQuantity,
                            'critical_level' => 0,
                            'status' => 'GOOD',
                            'workstation_id' => Auth::user()->workstation_id,
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id,
                        ]);
                    }
                    AuditTrails::create([
                        'purchase_order_id' => $id,
                        'purchase_order_detail_id' => $poDetailId,
                        'item_id' => $materialId,
                        'sent_quantity' => $sentQuantity,
                        'dr_sequence' => $drSequence,
                        'dr_id' => $drId,
                        'amount' => $sentQuantity,
                        'project_id' => $projectId,
                        'event_type'=>'PURCHASE FROM PO',
                        'remarks' => 'DR COMPLETED',
                    ]);
                }
                break;
            case "NOT DELIVERED":
                $arr = ['status' => $request->status];
                break;
            case "CANCELLED":
                $arr = ['status' => $request->status];
                break;
        }

        PurchaseOrder::find($id)->update($arr);

        return "Record Saved";
    }

    public function showDetails($id) {
        $purchase_order = PurchaseOrder::where('id', $id)->first();
    
        if (!$purchase_order) {
            return response()->json(['error' => 'Purchase Order not found'], 404);
        }
    
        $items = DB::table('purchase_orders')
            ->join('purchase_order_details', 'purchase_orders.id','purchase_order_details.purchase_order_id')
            ->leftJoin('delivery_receipts', 'purchase_order_details.id', 'delivery_receipts.purchase_order_detail_id')
            ->join('materials', 'purchase_order_details.item','materials.id')
            ->select(
                'purchase_order_details.id as pod_id',
                'materials.item_name as material_name',
                'purchase_order_details.quantity as quantity_set',
                'materials.id as material_id',
                'materials.item_code',
                'delivery_receipts.dr_sequence',
                'delivery_receipts.updated_at',
                'purchase_orders.prepared_by',
                'purchase_orders.reviewed_by',
                'purchase_orders.approved_by',

                'purchase_orders.prepared_at',
                'purchase_orders.reviewed_at',
                'purchase_orders.approved_at',
            )
            ->where('purchase_orders.id', $id)
            ->get();

            foreach ($items as $item) {
                $sent_quantity = DB::table('delivery_receipts')
                    ->where('purchase_order_detail_id', $item->pod_id)
                    ->sum('sent_quantity');
        
                $item->sent_quantity = $sent_quantity;
            }
    
        if ($items->isEmpty()) {
            return response()->json(['error' => 'No items found for this order'], 404);
        }
    
        return response()->json([
            'order_no' => $purchase_order->order_no,
            'items' => $items
        ]);
    }

    public function updateSentQuantity(Request $request, $id) {
        $quantities = $request->input('quantities');
        $results = [];

        foreach ($quantities as $item) {
            $poDetailId = $item['po_detail_id'];
            $quantityToSend = $item['quantity'];
            $itemId = $item['material_id'];

            $purchaseOrderId = PurchaseOrderDetail::where('id', $poDetailId)->value('purchase_order_id');

            if ($purchaseOrderId) {
                $purchaseOrder = PurchaseOrder::find($purchaseOrderId);

                if ($purchaseOrder) {
                    $projectId = $request->input('project_id');
                    if (!$projectId) {
                        $projectSplit = ProjectSplit::where('po_id', $purchaseOrderId)->first();
                        $projectId = $projectSplit ? $projectSplit->project_id : null;
                    }

                    $project = Project::find($projectId);
                    $projectCode = $project ? $project->project_code : 'PRJ';

                    $lastDrNo = DeliveryReceipt::where('project_id', $projectId)
                        ->where('dr_sequence', 'like', '%DR' . $projectCode . '%')
                        ->orderByRaw("CAST(RIGHT(dr_sequence, 5) AS UNSIGNED) DESC")
                        ->value('dr_sequence');

                    $lastNumber = 0;
                    if ($lastDrNo && preg_match('/(\d{5})$/', $lastDrNo, $matches)) {
                        $lastNumber = (int) $matches[1];
                    }

                    $nextNumber = $lastNumber + 1;

                    $poSeries = $purchaseOrder->order_no ?? '';
                    $drSeries = 'DR' . $projectCode . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
                    $dr_no = $poSeries . $drSeries;

                    $existingReceipt = DeliveryReceipt::where('purchase_order_detail_id', $poDetailId)
                        ->where('item_id', $itemId)
                        ->where('project_id', $projectId)
                        ->first();

                    if ($existingReceipt) {
                        $existingReceipt->sent_quantity += $quantityToSend;
                        $existingReceipt->save();
                        $dr_id = $existingReceipt->id;
                        $dr_sequence = $existingReceipt->dr_sequence;
                    } else {
                        $newReceipt = DeliveryReceipt::create([
                            'dr_sequence' => $dr_no,
                            'purchase_order_detail_id' => $poDetailId,
                            'purchase_order_id' => $purchaseOrderId,
                            'sent_quantity' => $quantityToSend,
                            'item_id' => $itemId,
                            'project_id' => $projectId,
                            'created_by' => auth()->id(),
                            'updated_by' => auth()->id(),
                        ]);
                        $dr_id = $newReceipt->id;
                        $dr_sequence = $dr_no;
                    }

                    AuditTrails::create([
                        'purchase_order_id' => $purchaseOrderId,
                        'purchase_order_detail_id' => $poDetailId,
                        'item_id' => $itemId,
                        'sent_quantity' => $quantityToSend,
                        'project_id' => $projectId,
                        'event_type' => 'PURCHASE FROM PO',
                        'dr_sequence' => $dr_sequence,
                        'dr_id' => $dr_id,
                        'amount' => $quantityToSend,
                        'remark' => $quantityToSend . ' - ADDED QUANTITY IN DR'
                    ]);

                    $results[] = [
                        'dr_id' => $dr_id,
                        'po_detail_id' => $poDetailId,
                        'purchase_order_id' => $purchaseOrderId,
                        'existing_quantity' => $purchaseOrder->quantity,
                        'sent_quantity' => $quantityToSend,
                        'dr_sequence' => $dr_sequence,
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Delivery receipt quantities updated successfully.',
            'data' => $results,
        ]);
    }
    
    public function viewDetails($id){
        $po = PurchaseOrder::where('id', $id)->first();

        if (!$po) {
            return response()->json(['error' => 'Purchase Order not found'], 404);
        }

        $details = DB::table('purchase_orders')
            ->join('purchase_order_details', 'purchase_orders.id', 'purchase_order_details.purchase_order_id')
            ->join('materials', 'purchase_order_details.item', 'materials.id')
            ->select(
                'purchase_order_details.id as pod_id',
                'materials.item_name as material_name',
                'purchase_order_details.quantity as quantity_set',
                'materials.id as material_id'
            )
            ->where('purchase_orders.id', $id)
            ->get();

        foreach ($details as $item) {
            $sent_quantity = DB::table('delivery_receipts')
                ->where('purchase_order_detail_id', $item->pod_id)
                ->sum('sent_quantity');

            $item->sent_quantity = $sent_quantity;
        }

        if ($details->isEmpty()) {
            return response()->json(['error' => 'No items found for this order'], 404);
        }

        return response()->json([
            'purchase_order' => $po,
            'details' => $details
        ]);
    }

}