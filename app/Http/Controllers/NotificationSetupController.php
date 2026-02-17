<?php

namespace App\Http\Controllers;

use App\NotificationRule;
use App\Notifications;
use App\Roles;
use App\Services\NotificationService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationSetupController extends Controller
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
        $roles = Roles::orderBy('name')->get();
        $users = User::orderBy('firstname')->orderBy('lastname')->get();
        $statuses = $this->purchaseOrderStatuses();
        $notifications = Notifications::with('roles', 'sender', 'sender.roles')
            ->orderBy('id', 'desc')
            ->get();
        $workflowRules = NotificationRule::with('roles')
            ->where('module', 'purchase_order')
            ->orderBy('to_status')
            ->orderBy('from_status')
            ->get();
        $allRules = NotificationRule::with('roles')
            ->orderBy('module')
            ->orderBy('to_status')
            ->orderBy('from_status')
            ->get();

        return view('backend.pages.setup.notification_setup', compact('roles', 'users', 'notifications', 'workflowRules', 'allRules', 'statuses'), ['type' => 'full-view']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            'url' => ['nullable', 'string', 'max:255'],
            'channel' => ['nullable', 'string', 'max:50'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:3'],
            'target_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
            'is_important' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'action_required' => ['nullable', 'boolean'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $notificationData = [
            'type' => $request->type,
            'title' => $request->title,
            'message' => $request->message,
            'url' => $request->url,
            'channel' => $request->channel ?: 'header',
            'priority' => $request->priority ?: 1,
            'target_user_id' => $request->target_user_id,
            'is_important' => $this->toBool($request->input('is_important')),
            'is_active' => $request->has('is_active') ? $this->toBool($request->input('is_active')) : true,
            'action_required' => $this->toBool($request->input('action_required')),
            'expires_at' => $request->expires_at,
            'sender_id' => Auth::id(),
            'is_read' => false,
        ];

        $roleIds = $request->role_ids ?: [];

        $this->notificationService->create($notificationData, $roleIds);

        return redirect()->back()->with('success', 'Notification created successfully.');
    }

    public function destroy($id)
    {
        $notification = Notifications::findOrFail($id);
        $notification->roles()->detach();
        $notification->delete();

        return redirect()->back()->with('success', 'Notification deleted successfully.');
    }

    public function storePurchaseOrderRule(Request $request)
    {
        $allowedStatuses = $this->purchaseOrderStatuses();

        $request->validate([
            'from_status' => ['nullable', 'in:' . implode(',', $allowedStatuses)],
            'to_status' => ['required', 'in:' . implode(',', $allowedStatuses)],
            'rule_role_ids' => ['required', 'array', 'min:1'],
            'rule_role_ids.*' => ['integer', 'exists:roles,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            'channel' => ['nullable', 'string', 'max:50'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:3'],
            'is_important' => ['nullable', 'boolean'],
            'action_required' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $rule = NotificationRule::create([
            'module' => 'purchase_order',
            'from_status' => $request->from_status ?: null,
            'to_status' => $request->to_status,
            'title' => $request->title,
            'message' => $request->message,
            'channel' => $request->channel ?: 'header',
            'priority' => $request->priority ?: 1,
            'is_important' => $this->toBool($request->input('is_important')),
            'action_required' => $this->toBool($request->input('action_required')),
            'is_active' => $request->has('is_active') ? $this->toBool($request->input('is_active')) : true,
        ]);

        $rule->roles()->sync(collect($request->rule_role_ids)->map(function ($id) {
            return (int) $id;
        })->unique()->values()->all());

        return redirect()->back()->with('success', 'Purchase Order workflow rule created.');
    }

    public function destroyPurchaseOrderRule($id)
    {
        $rule = NotificationRule::where('module', 'purchase_order')->findOrFail($id);
        $rule->roles()->detach();
        $rule->delete();

        return redirect()->back()->with('success', 'Purchase Order workflow rule deleted.');
    }

    /**
     * Example endpoint: fetch notifications for the currently logged-in user.
     */
    public function myNotifications()
    {
        $notifications = $this->notificationService->forUser(Auth::user(), 'header');

        return response()->json([
            'count' => $notifications->count(),
            'notifications' => $notifications,
        ]);
    }

    /**
     * Example usage:
     * Send one notification to users who have any of the provided role IDs.
     */
    public function sendExampleByRoles(Request $request)
    {
        $request->validate([
            'role_ids' => ['required', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
        ]);

        $notification = $this->notificationService->notifyRoles([
            'type' => 'example_role_notification',
            'title' => $request->title,
            'message' => $request->message,
            'url' => '/notifications',
            'channel' => 'header',
            'priority' => 2,
            'is_active' => true,
            'is_read' => false,
            'sender_id' => Auth::id(),
        ], $request->role_ids);

        return response()->json([
            'success' => true,
            'notification_id' => $notification->id,
            'attached_role_ids' => $notification->roles()->pluck('roles.id')->all(),
        ]);
    }

    private function purchaseOrderStatuses()
    {
        return [
            'DRAFT',
            'FOR_CHECKING',
            'FOR_APPROVAL',
            'APPROVED',
            'SENT_TO_SUPPLIER',
            'PARTIALLY_DELIVERED',
            'COMPLETED',
            'NOT_DELIVERED',
            'CANCELLED',
        ];
    }

    private function toBool($value)
    {
        return in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true);
    }
}
