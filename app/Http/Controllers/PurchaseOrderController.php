<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
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
use Illuminate\Http\Request;
use Carbon\Carbon;
use Auth;
use App\AuditTrails;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $statuses = [
            "DRAFT" => "view_Draft",
            "FOR CHECKING" => "view_For Checking",
            "FOR APPROVAL" => "view_For Approval",
            "APPROVED" => "view_Approved",
            "SENT TO SUPPLIER" => "view_Sent To Supplier",
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
    
        return view('backend.pages.purchasing.transaction.purchase_order.index', compact('employees', 'suppliers', 'projects', 'status', 'materials'), ["type" => "full-view"]);
    }

    public function inventoryIndex()
    {
        $status = array(
            "DRAFT" => PurchaseOrder::where('status', 'DRAFT')->count(),
            "FOR CHECKING" => PurchaseOrder::where('status', 'FOR_CHECKING')->count(),
            "FOR APPROVAL" => PurchaseOrder::where('status', 'FOR_APPROVAL')->count(),
            "APPROVED" => PurchaseOrder::where('status', 'APPROVED')->count(),
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
    if ($request->action === 'save') {
        $request->validate([
            'order_no' => ['required'],
            'supplier_id' => ['required', 'integer'],
            'delivery_date' => ['required', 'date'],
            'po_date' => ['required', 'date'],
            'contact_no' => ['required', 'string'],
            'terms' => ['required', 'string'],
            'due_date' => ['required', 'date'],
            'tax_type' => ['required', 'string'],
            'subtotal' => ['required', 'numeric'],
            'total_with_tax' => ['required', 'numeric'],
            'delivery_instruction' => ['required', 'string'],
            'manual_po' => ['nullable', 'string'],
            'project' => ['required']
        ]);

        $data = $request->all();
        $data['project_id'] = $request->project;

        $project = Project::findOrFail($request->project);

        $data['prepared_by'] = Auth::id();
        $data['prepared_at'] = now()->format('Y-m-d');
        $data['workstation_id'] = Auth::user()->workstation_id;
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        // Old PO number
        if (!empty($request->manual_po)) {
            $data['oldpo'] = $request->manual_po;
        }

        // Create PO
        $po = PurchaseOrder::create($data);

        // Create Split
        ProjectSplit::create([
            "po_id" => $po->id,
            "project_id" => $request->project,
            "percentage" => "100",
            "amount" => $request->subtotal
        ]);

        return back()->with('success', 'Successfully Added');

    } else {
        $id = $request->current_po_id;

        if (!$id) {
            return back()->with('error', 'Missing PO ID for update.');
        }

        PurchaseOrder::findOrFail($id)->update($request->all());

        return back()->with('success', 'Successfully Updated');
    }
}


   public function get($status, $project, $start_date, $end_date, $po) {
    // Count of each status
    $status_count = [
        "DRAFT" => PurchaseOrder::where('status', 'DRAFT')->count(),
        "FOR CHECKING" => PurchaseOrder::where('status', 'FOR_CHECKING')->count(),
        "FOR APPROVAL" => PurchaseOrder::where('status', 'FOR_APPROVAL')->count(),
        "APPROVED" => PurchaseOrder::where('status', 'APPROVED')->count(),
        "SENT TO SUPPLIER" => PurchaseOrder::where('status', 'SENT_TO_SUPPLIER')->count(),
        "PARTIALLY DELIVERED" => PurchaseOrder::where('status', 'PARTIALLY_DELIVERED')->count(),
        "COMPLETED" => PurchaseOrder::where('status', 'COMPLETED')->count(),
        "NOT DELIVERED" => PurchaseOrder::where('status', 'NOT_DELIVERED')->count(),
        "CANCELLED" => PurchaseOrder::where('status', 'CANCELLED')->count(),
    ];

        if(request()->ajax()) {
            // Start query
            $query = PurchaseOrder::with('supplier', 'prepared_by', 'reviewed_by', 'approved_by', 'received_by', 'details')
                        ->where('status', $status)
                        ->orderBy('order_no', 'asc');

        // Filter by project only if it's not 'all'
        if ($project !== 'all_project') {
            $query->where('project_id', $project);
        }

        // Filter by PO number
        if ($po !== 'no_po') {
            $query->where('order_no', $po);
        }

        // Filter by date range
        if ($start_date !== 'no_start' && $end_date !== 'no_end') {
            $query->whereBetween('po_date', [$start_date, $end_date]);
        }

        return datatables()->of($query->get())
            ->with('status', $status_count)
            ->make(true);
    }
}

    public function edit($id)
    {
        $purchase_orders = PurchaseOrder::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('purchase_orders'));
    }

    public function print($id)
    {
        $purchase_orders = PurchaseOrder::with('supplier', 'prepared_by', 'reviewed_by', 'approved_by', 'received_by', 'details', 'projects', 'projects.project', 'projects.project.region', 'projects.project.province', 'projects.project.city', 'projects.project.barangay', 'discount', 'details.discount', 'details.item', 'credits')->where('id', $id)->orderBy('id')->firstOrFail();
        $chart = ChartOfAccount::get();
        return response()->json(compact('purchase_orders', 'chart'));
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
    
    public function changeStatus(Request $request, $id)
    {
        $arr = [];

        switch($request->status) {
            case "FOR_CHECKING":
                $arr = ['status' => $request->status];

                break;
            case "FOR_APPROVAL":
                $arr = ['status' => $request->status, 'reviewed_by' => Auth::user()->id, 'reviewed_at' => date('Y-m-d')];

                break;
            case "APPROVED":
                $arr = ['status' => $request->status, 'approved_by' => Auth::user()->id, 'approved_at' => date('Y-m-d')];

                break;
            case "SENT_TO_SUPPLIER":
                $arr = ['status' => $request->status];
                PurchaseOrder::find($id)->update($arr);

                $purchaseOrder = PurchaseOrder::find($id);
                $auditExists = AuditTrails::where('purchase_order_id', $purchaseOrder->id)
                    ->where('remark', $purchaseOrder->status)
                    ->exists();

                if (!$auditExists) {
                    AuditTrails::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'remark' => $purchaseOrder->status . ' - ON PO',
                        'created_at' => $purchaseOrder->updated_at,
                        'updated_at' => $purchaseOrder->updated_at,
                    ]);
                }
                break;
            case "PARTIALLY_DELIVERED":
                $arr = ['status' => $request->status, 'received_by' => Auth::user()->id, 'received_at' => date('Y-m-d')];
                PurchaseOrder::find($id)->update($arr);

                $purchaseOrder = PurchaseOrder::find($id);
                $auditExists = AuditTrails::where('purchase_order_id', $purchaseOrder->id)
                    ->where('remark', $purchaseOrder->status)
                    ->exists();

                if (!$auditExists) {
                    AuditTrails::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'remark' => $purchaseOrder->status . ' - ON PO',
                        'created_at' => $purchaseOrder->updated_at,
                        'updated_at' => $purchaseOrder->updated_at,
                    ]);
                }

                $this->inventory($id);
                break;
            case "COMPLETED":
                $arr = ['status' => $request->status, 'received_by' => Auth::user()->id, 'received_at' => date('Y-m-d')];

                break;
            case "NOT DELIVERED":
                $arr = ['status' => $request->status];
                $purchaseOrder = PurchaseOrder::find($id);
                $auditExists = AuditTrails::where('purchase_order_id', $purchaseOrder->id)
                    ->where('remark', $purchaseOrder->status)
                    ->exists();

                if (!$auditExists) {
                    AuditTrails::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'remark' => $purchaseOrder->status . ' - ON PO',
                        'created_at' => $purchaseOrder->updated_at,
                        'updated_at' => $purchaseOrder->updated_at,
                    ]);
                }

                break;
            case "CANCELLED":
                $arr = ['status' => $request->status];
                $purchaseOrder = PurchaseOrder::find($id);
                $auditExists = AuditTrails::where('purchase_order_id', $purchaseOrder->id)
                    ->where('remark', $purchaseOrder->status)
                    ->exists();

                if (!$auditExists) {
                    AuditTrails::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'remark' => $purchaseOrder->status . ' - ON PO',
                        'created_at' => $purchaseOrder->updated_at,
                        'updated_at' => $purchaseOrder->updated_at,
                    ]);
                }

                break;
        }

        PurchaseOrder::find($id)->update($arr);

        return "Record Saved";
    }
}
