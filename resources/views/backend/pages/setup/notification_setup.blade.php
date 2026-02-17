@extends('backend.master.index')

@section('title', 'Notification Setup')

@section('breadcrumbs')
    <span>Setup</span> / <span>User Access</span> / <span class="highlight">Notification Setup</span>
@endsection

@section('content')
<div class="row notification-setup-page">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header pb-0">
                <ul class="nav nav-tabs card-header-tabs" id="notificationSetupTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-notifications-list" data-toggle="tab" href="#pane-notifications-list" role="tab">Notifications</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-workflow-rules" data-toggle="tab" href="#pane-workflow-rules" role="tab">PO Workflow Rules</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-create-notification" data-toggle="tab" href="#pane-create-notification" role="tab">Create Notification</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-all-rules" data-toggle="tab" href="#pane-all-rules" role="tab">All Module Rules</a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content" id="notificationSetupTabsContent">

                    <div class="tab-pane fade show active" id="pane-notifications-list" role="tabpanel">
                        <div class="table-title-bar px-3 py-2">
                            <strong>Notifications Table</strong>
                            <span class="text-muted ml-2">({{ method_exists($notifications, 'total') ? $notifications->total() : $notifications->count() }} rows)</span>
                        </div>
                        <div class="table-responsive">
                            <table id="notificationsTable" class="table table-striped mb-0" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Target User</th>
                                        <th>Role IDs</th>
                                        <th>Channel</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($notifications as $notification)
                                        <tr>
                                            <td>{{ $notification->id }}</td>
                                            <td>{{ $notification->display_title }}</td>
                                            <td>{{ $notification->type }}</td>
                                            <td>
                                                @if(!empty($notification->target_user_id))
                                                    @php
                                                        $targetUser = $users->firstWhere('id', $notification->target_user_id);
                                                    @endphp
                                                    @if($targetUser)
                                                        {{ trim($targetUser->firstname . ' ' . $targetUser->lastname) }} (ID: {{ $targetUser->id }})
                                                    @else
                                                        ID: {{ $notification->target_user_id }}
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($notification->roles->isEmpty())
                                                    -
                                                @else
                                                    {{ $notification->roles->pluck('name')->implode(', ') }}
                                                @endif
                                            </td>
                                            <td>{{ $notification->channel ?: 'header' }}</td>
                                            <td>
                                                @if($notification->is_active)
                                                    <span class="badge badge-success">Active</span>
                                                @else
                                                    <span class="badge badge-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <form method="POST" action="{{ route('notification_setup.destroy', $notification->id) }}" class="not" onsubmit="return confirm('Delete this notification?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">No notifications found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="pane-workflow-rules" role="tabpanel">
                        <form method="POST" action="{{ route('notification_setup.po_rule.store') }}" class="not mb-3">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label>From Status (optional)</label>
                                    <select class="form-control" name="from_status">
                                        <option value="">ANY</option>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status }}">{{ str_replace('_', ' ', $status) }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Use ANY to match all previous statuses.</small>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>To Status</label>
                                    <select class="form-control" name="to_status" required>
                                        <option value="" disabled selected>-- Select To Status --</option>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status }}">{{ str_replace('_', ' ', $status) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Channel</label>
                                    <input type="text" class="form-control" name="channel" value="header" placeholder="header">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Priority (1-3)</label>
                                    <input type="number" class="form-control" name="priority" min="1" max="3" value="1">
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Title Override (optional)</label>
                                    <input type="text" class="form-control" name="title" placeholder="e.g. Purchase Order Status Updated">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Message Override (optional)</label>
                                    <input type="text" class="form-control" name="message" placeholder="PO #@{{order_no}} (ID: @{{purchase_order_id}}) moved from @{{from_status}} to @{{to_status}}">
                                    <small class="text-muted">Available tokens: @{{purchase_order_id}}, @{{order_no}}, @{{from_status}}, @{{to_status}}, @{{actor_name}}</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label>Recipient Roles (multiple role IDs)</label>
                                    <div class="role-checkbox-list border rounded p-2">
                                        @foreach($roles as $role)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="rule_role_ids[]" value="{{ $role->id }}" id="rule_role_id_{{ $role->id }}">
                                                <label class="form-check-label" for="rule_role_id_{{ $role->id }}">{{ $role->name }} (#{{ $role->id }})</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-12">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="is_important" value="1" id="rule_is_important">
                                        <label class="form-check-label" for="rule_is_important">Important</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="action_required" value="1" id="rule_action_required">
                                        <label class="form-check-label" for="rule_action_required">Action Required</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="rule_is_active" checked>
                                        <label class="form-check-label" for="rule_is_active">Active</label>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success">Save Workflow Rule</button>
                        </form>

                        <div class="table-title-bar px-3 py-2">
                            <strong>Workflow Rules Table</strong>
                            <span class="text-muted ml-2">({{ $workflowRules->count() }} rows)</span>
                        </div>
                        <div class="table-responsive">
                            <table id="workflowRulesTable" class="table table-striped mb-0" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Role IDs</th>
                                        <th>Channel</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($workflowRules as $rule)
                                        <tr>
                                            <td>{{ $rule->id }}</td>
                                            <td>{{ $rule->from_status ? str_replace('_', ' ', $rule->from_status) : 'ANY' }}</td>
                                            <td>{{ str_replace('_', ' ', $rule->to_status) }}</td>
                                            <td>{{ $rule->roles->pluck('name')->implode(', ') }}</td>
                                            <td>{{ $rule->channel }}</td>
                                            <td>{{ $rule->priority }}</td>
                                            <td>
                                                @if($rule->is_active)
                                                    <span class="badge badge-success">Active</span>
                                                @else
                                                    <span class="badge badge-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <form method="POST" action="{{ route('notification_setup.po_rule.destroy', $rule->id) }}" class="not" onsubmit="return confirm('Delete this workflow rule?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">No workflow rules found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="pane-create-notification" role="tabpanel">
                        <form method="POST" action="{{ route('notification_setup.store') }}" class="not">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label>Type</label>
                                    <input type="text" class="form-control" name="type" placeholder="e.g. purchase_order_status_update" required>
                                </div>
                                <div class="form-group col-md-5">
                                    <label>Title</label>
                                    <input type="text" class="form-control" name="title" placeholder="e.g. Purchase Order Status Updated" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>URL (optional)</label>
                                    <input type="text" class="form-control" name="url" placeholder="/purchasing/purchase_orders">
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label>Message</label>
                                    <textarea class="form-control" name="message" rows="2" placeholder="e.g. PO #PO-001 moved to FOR APPROVAL"></textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label>Channel</label>
                                    <input type="text" class="form-control" name="channel" value="header" placeholder="header">
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Priority (1-3)</label>
                                    <input type="number" class="form-control" name="priority" min="1" max="3" value="1">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Target User (optional)</label>
                                    <select class="form-control" name="target_user_id">
                                        <option value="">-- Any User by Role/Broadcast --</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ trim($user->firstname . ' ' . $user->lastname) }} (#{{ $user->id }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Expires At (optional)</label>
                                    <input type="datetime-local" class="form-control" name="expires_at">
                                    <small class="text-muted">Leave blank if it should not expire.</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label>Target Roles (by role id)</label>
                                    <div class="role-checkbox-list border rounded p-2">
                                        @foreach($roles as $role)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="role_ids[]" value="{{ $role->id }}" id="role_id_{{ $role->id }}">
                                                <label class="form-check-label" for="role_id_{{ $role->id }}">{{ $role->name }} (#{{ $role->id }})</label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <small class="text-muted">You can select multiple roles. Leave empty for broadcast notifications.</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-12">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="is_important" value="1" id="is_important">
                                        <label class="form-check-label" for="is_important">Important</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="action_required" value="1" id="action_required">
                                        <label class="form-check-label" for="action_required">Action Required</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" checked>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Create Notification</button>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="pane-all-rules" role="tabpanel">
                        <div class="table-title-bar px-3 py-2">
                            <strong>All Module Rules Table</strong>
                            <span class="text-muted ml-2">({{ $allRules->count() }} rows)</span>
                        </div>
                        <div class="table-responsive">
                            <table id="allRulesTable" class="table table-striped mb-0" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Module</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Roles</th>
                                        <th>Priority</th>
                                        <th>Important</th>
                                        <th>Action Required</th>
                                        <th>Active</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($allRules as $rule)
                                        <tr>
                                            <td>{{ $rule->id }}</td>
                                            <td>{{ strtoupper(str_replace('_', ' ', $rule->module)) }}</td>
                                            <td>{{ $rule->from_status ? str_replace('_', ' ', $rule->from_status) : 'ANY' }}</td>
                                            <td>{{ str_replace('_', ' ', $rule->to_status) }}</td>
                                            <td>{{ $rule->roles->pluck('name')->implode(', ') }}</td>
                                            <td>{{ $rule->priority }}</td>
                                            <td>{{ $rule->is_important ? 'YES' : 'NO' }}</td>
                                            <td>{{ $rule->action_required ? 'YES' : 'NO' }}</td>
                                            <td>{{ $rule->is_active ? 'YES' : 'NO' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">No module rules found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('chart-js')
<script>
    $(function () {
        scion.centralized_button(false, false, false, false);

        if (!$.fn.DataTable.isDataTable('#notificationsTable')) {
            $('#notificationsTable').DataTable({
                pageLength: 10,
                order: [[0, 'desc']]
            });
        }
        if (!$.fn.DataTable.isDataTable('#workflowRulesTable')) {
            $('#workflowRulesTable').DataTable({
                pageLength: 10,
                order: [[0, 'desc']]
            });
        }
        if (!$.fn.DataTable.isDataTable('#allRulesTable')) {
            $('#allRulesTable').DataTable({
                pageLength: 10,
                order: [[0, 'desc']]
            });
        }

        $('a[data-toggle=\"tab\"][href=\"#pane-notifications-list\"]').on('shown.bs.tab', function () {
            if ($.fn.DataTable.isDataTable('#notificationsTable')) {
                $('#notificationsTable').DataTable().columns.adjust().draw(false);
            }
        });
        $('a[data-toggle=\"tab\"][href=\"#pane-workflow-rules\"]').on('shown.bs.tab', function () {
            if ($.fn.DataTable.isDataTable('#workflowRulesTable')) {
                $('#workflowRulesTable').DataTable().columns.adjust().draw(false);
            }
        });
        $('a[data-toggle=\"tab\"][href=\"#pane-all-rules\"]').on('shown.bs.tab', function () {
            if ($.fn.DataTable.isDataTable('#allRulesTable')) {
                $('#allRulesTable').DataTable().columns.adjust().draw(false);
            }
        });
    });
</script>
@endsection

@section('styles')
<style>
    #notificationSetupTabs .nav-link {
        border: 1px solid #d7dee6;
        border-bottom: 0;
        margin-right: 6px;
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
        background: #f3f6fa;
        color: #1f3e5a;
        font-weight: 600;
    }
    #notificationSetupTabs .nav-link.active {
        background: #ffffff;
        border-color: #c7d3df;
        color: #0b4e78;
    }
    #notificationSetupTabsContent {
        max-height: none !important;
        overflow: visible !important;
    }
    #pane-workflow-rules {
        max-height: none !important;
        overflow: visible !important;
        padding-right: 0;
    }
    .role-checkbox-list {
        max-height: 180px;
        overflow-y: auto;
        background: #fff;
    }
    .table-title-bar {
        background: #f8fafc;
        border-top: 1px solid #e9ecef;
        border-bottom: 1px solid #e9ecef;
    }
    .notification-setup-page {
        height: 100%;
        overflow-y: auto;
        padding-bottom: 16px;
    }
    .notification-setup-page > .col-md-12 {
        padding-bottom: 16px;
    }
</style>
@endsection
