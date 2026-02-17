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
use App\NotificationRule;
use App\ProjectSplit;
use App\EmployeeInformation;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Auth;
use App\AuditTrails;

class PurchaseOrderController extends Controller
{
    /**
     * @var NotificationService
     */
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

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
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $previousStatus = $purchaseOrder->status;
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
            case "NOT_DELIVERED":
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
        $this->notifyPurchaseOrderStatusTransition($id, $previousStatus, $request->status);

        return "Record Saved";
    }

    private function notifyPurchaseOrderStatusTransition($purchaseOrderId, $fromStatus, $toStatus)
    {
        $actor = Auth::user();
        if (!$actor) {
            return;
        }

        $purchaseOrder = PurchaseOrder::find($purchaseOrderId);
        if (!$purchaseOrder) {
            return;
        }

        $normalizedFrom = strtoupper(str_replace(' ', '_', (string) $fromStatus));
        $normalizedTo = strtoupper(str_replace(' ', '_', (string) $toStatus));

        if (empty($normalizedTo) || $normalizedFrom === $normalizedTo) {
            return;
        }

        // Initial creation in DRAFT is not part of status transition workflow notifications.
        if ($normalizedTo === 'DRAFT' && empty($normalizedFrom)) {
            return;
        }

        $rules = NotificationRule::with('roles')
            ->where('module', 'purchase_order')
            ->where('to_status', $normalizedTo)
            ->where('is_active', true)
            ->where(function ($query) use ($normalizedFrom) {
                $query->whereNull('from_status')
                    ->orWhere('from_status', $normalizedFrom);
            })
            ->get();

        if ($rules->isEmpty()) {
            return;
        }

        // Prefer exact from_status rules over wildcard rules when both exist.
        $exactRules = $rules->filter(function ($rule) use ($normalizedFrom) {
            return strtoupper((string) $rule->from_status) === $normalizedFrom;
        })->values();

        $activeRules = $exactRules->isNotEmpty()
            ? $exactRules
            : $rules->filter(function ($rule) {
                return empty($rule->from_status);
            })->values();

        if ($activeRules->isEmpty()) {
            return;
        }

        $fromLabel = str_replace('_', ' ', $normalizedFrom);
        $toLabel = str_replace('_', ' ', $normalizedTo);
        $actorName = trim($actor->firstname . ' ' . $actor->lastname);
        $templateData = [
            'purchase_order_id' => $purchaseOrder->id,
            'order_no' => $purchaseOrder->order_no,
            'from_status' => $fromLabel,
            'to_status' => $toLabel,
            'actor_name' => $actorName,
        ];

        foreach ($activeRules as $rule) {
            $ruleRoleIds = $rule->roles->pluck('id')->map(function ($id) {
                return (int) $id;
            })->all();

            $this->notificationService->notifyRoles([
                'type' => 'purchase_order_status_update',
                'reference_id' => $purchaseOrder->id,
                'title' => $this->interpolateNotificationTemplate(
                    $rule->title ?: 'Purchase Order Status Updated',
                    $templateData
                ),
                'message' => $this->interpolateNotificationTemplate(
                    $rule->message ?: ('PO #{{order_no}} moved from {{from_status}} to {{to_status}} by {{actor_name}}.'),
                    $templateData
                ),
                'icon' => 'fa-file-alt',
                'url' => '/purchasing/purchase_orders',
                'is_read' => false,
                'is_important' => (bool) $rule->is_important,
                'is_active' => true,
                'channel' => $rule->channel ?: 'header',
                'priority' => (int) ($rule->priority ?: 1),
                'action_required' => (bool) $rule->action_required,
                'sender_id' => $actor->id,
                'context' => [
                    'purchase_order_id' => $purchaseOrder->id,
                    'order_no' => $purchaseOrder->order_no,
                    'rule_id' => $rule->id,
                    'previous_status' => $normalizedFrom,
                    'status' => $normalizedTo,
                ],
                'tags' => ['purchase_order', strtolower($normalizedTo)],
            ], $ruleRoleIds);
        }
    }

    private function interpolateNotificationTemplate($template, array $data)
    {
        $template = (string) $template;

        foreach ($data as $key => $value) {
            $template = str_replace('{{' . $key . '}}', (string) $value, $template);
        }

        return $template;
    }
}
