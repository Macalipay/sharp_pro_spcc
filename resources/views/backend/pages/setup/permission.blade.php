@extends('backend.master.index')

@section('title', 'Permission')

@section('breadcrumbs')
    <span>Account</span> / <span class="highlight">Permission</span>
@endsection

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <h5>Permission Screen</h5>
            <select id="role" class="form-control">
                <option value="">-- Select Role --</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
        
        <div id="permissions" class="mt-4" style="display:none;">
            <form id="permissionForm">
                @csrf
                <input type="hidden" name="role_id" id="role_id">
                <div class="d-flex mb-2">
                    <button type="button" class="btn btn-sm btn-outline-primary mr-2" id="checkAllPermissions">Check All</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="uncheckAllPermissions">Uncheck All</button>
                </div>
        
                <div class="card mt-3">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-5">
                                <strong>App/Module</strong>
                            </div>
                            <div class="col-md-7">
                                <strong>Permissions</strong>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @php
                            $workflowSubCategories = [
                                'draft',
                                'for checking',
                                'for approval',
                                'approved',
                                'sent to supplier',
                                'partially delivered',
                                'completed',
                                'not delivered',
                                'cancelled',
                            ];

                            $permissionsByCategory = $permissionsList
                                ->sortBy(function ($permission) {
                                    return strtolower((string) $permission->category) . '|' .
                                        strtolower((string) $permission->sub_category) . '|' .
                                        strtolower((string) $permission->name);
                                })
                                ->groupBy('category');
                        @endphp

                        @foreach($permissionsByCategory as $category => $categoryPermissions)
                            @php
                                $displayCategory = $category;
                                if (strtolower((string) $category) === 'workflow') {
                                    $displayCategory = 'Workflow (Purchase Order)';
                                }

                                $subGroups = $categoryPermissions->groupBy('sub_category');

                                $normalGroups = $subGroups;
                                $workflowGroups = collect();

                                if (strtolower($category) === 'purchasing') {
                                    $workflowGroups = $subGroups->filter(function ($items, $subCategory) use ($workflowSubCategories) {
                                        return in_array(strtolower($subCategory), $workflowSubCategories, true);
                                    });

                                    $normalGroups = $subGroups->reject(function ($items, $subCategory) use ($workflowSubCategories) {
                                        return in_array(strtolower($subCategory), $workflowSubCategories, true);
                                    });
                                }
                            @endphp

                            <div class="mt-4 mb-2 permission-category-header" data-category="{{ strtolower($category) }}">
                                <h3 class="text-uppercase d-inline-block mb-0">{{ $displayCategory }}</h3>
                                <button type="button" class="btn btn-sm btn-link category-check" data-category="{{ strtolower($category) }}">All</button>
                                <button type="button" class="btn btn-sm btn-link category-uncheck" data-category="{{ strtolower($category) }}">None</button>
                            </div>

                            <div class="permission-category-block" data-category="{{ strtolower($category) }}">
                            @foreach($normalGroups as $subCategory => $permissions)
                                @php
                                    $displaySubCategory = ucfirst(strtolower($subCategory));
                                    if (strtolower($category) === 'purchasing' && strtolower($subCategory) === 'workflow') {
                                        $displaySubCategory = 'Purchase Order Workflow';
                                    }
                                @endphp
                                <div class="permission-group mb-3 permission-indent" data-subcategory="{{ strtolower($subCategory) }}">
                                    <div class="row role-row">
                                        <div class="col-md-5">
                                            <h5 class="permission-title">{{ $displaySubCategory }}</h5>
                                            <button type="button" class="btn btn-xs btn-link row-check">All</button>
                                            <button type="button" class="btn btn-xs btn-link row-uncheck">None</button>
                                        </div>
                                        <div class="col-md-7">
                                            @foreach($permissions as $permission)
                                                <div class="form-check form-check-inline per-action">
                                                    <input class="form-check-input permission-checkbox permission-{{ $permission->id }}"
                                                        type="checkbox"
                                                        name="permissions[{{ $permission->id }}]"
                                                        value="1"
                                                        id="permission-{{ $permission->id }}">
                                                    <label class="form-check-label" for="permission-{{ $permission->id }}">
                                                        {{ ucfirst(str_replace('_', ' ', explode('_', $permission->name)[0])) }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                @if(strtolower($category) === 'purchasing' && strtolower($subCategory) === 'purchase order' && $workflowGroups->isNotEmpty())
                                    <div class="workflow-permission-box workflow-under-po">
                                        <div class="workflow-title">Purchase Order Workflow Status Permissions</div>
                                        <div class="workflow-subtitle">These permissions apply to Purchase Order status flow only.</div>
                                        @foreach($workflowGroups as $subCategory => $permissions)
                                            <div class="permission-group mb-2 permission-indent">
                                                <div class="row role-row">
                                                    <div class="col-md-5">
                                                        <h5 class="permission-title">{{ strtoupper($subCategory) }}</h5>
                                                        <button type="button" class="btn btn-xs btn-link row-check">All</button>
                                                        <button type="button" class="btn btn-xs btn-link row-uncheck">None</button>
                                                    </div>
                                                    <div class="col-md-7">
                                                        @foreach($permissions as $permission)
                                                            <div class="form-check form-check-inline per-action">
                                                                <input class="form-check-input permission-checkbox permission-{{ $permission->id }}"
                                                                    type="checkbox"
                                                                    name="permissions[{{ $permission->id }}]"
                                                                    value="1"
                                                                    id="permission-{{ $permission->id }}">
                                                                <label class="form-check-label" for="permission-{{ $permission->id }}">
                                                                    {{ ucfirst(str_replace('_', ' ', explode('_', $permission->name)[0])) }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                            </div>
                        @endforeach
                    </div>

                </div>
                <div class="permission-save-bar">
                    <button type="submit" class="btn btn-primary">Save Permissions</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#role').on('change', function() {
                var roleId = $(this).val();
                if (roleId) {
                $('#role_id').val(roleId);
                $('#permissions').show();

                $.ajax({
                    url: '/settings/permission/' + roleId + '/role-permissions',
                    type: 'GET',
                    success: function(response) {
                        $('.permission-checkbox').prop('checked', false);
                        console.log(response);
                        response.permissions.forEach(function(permission) {
                            console.log(permission);
                            $('.permission-' + permission).prop('checked', true);
                        });
                    },
                    error: function() {
                        alert('Failed to load permissions');
                    }
                });
            } else {
                $('#permissions').hide();
            }
        });

        $('#permissionForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: '/settings/permission/update',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    toastr.success('Permission Updated!', 'Success');
                },
                error: function() {
                    toastr.error('Failed to update permissions', 'Error');
                }
            });
        });

        $('#checkAllPermissions').on('click', function() {
            $('.permission-checkbox').prop('checked', true);
        });

        $('#uncheckAllPermissions').on('click', function() {
            $('.permission-checkbox').prop('checked', false);
        });

        $(document).on('click', '.row-check', function() {
            $(this).closest('.role-row').find('.permission-checkbox').prop('checked', true);
        });

        $(document).on('click', '.row-uncheck', function() {
            $(this).closest('.role-row').find('.permission-checkbox').prop('checked', false);
        });

        $(document).on('click', '.category-check', function() {
            var category = $(this).data('category');
            $('.permission-category-block[data-category="' + category + '"] .permission-checkbox').prop('checked', true);
        });

        $(document).on('click', '.category-uncheck', function() {
            var category = $(this).data('category');
            $('.permission-category-block[data-category="' + category + '"] .permission-checkbox').prop('checked', false);
        });
    });
</script>
@endsection

@section('styles')
<style>
    .form-check.form-check-inline.per-action {
        width: 100px;
    }
    h5.permission-title {
        font-weight: bold;
        font-size: 16px;
        color: #1b547b;
        margin: 0;
    }
    .row.role-row {
        border-bottom: 1px solid #e8e8e8;
        padding: 10px 0px;
    }
    .btn-xs {
        font-size: 11px;
        padding: 0 4px;
    }
    .permission-indent {
        margin-left: 30px;
    }
    .workflow-permission-box {
        border: 1px solid #cfe0ef;
        background: #f7fbff;
        border-radius: 6px;
        padding: 12px 10px;
        margin: 12px 0 4px 30px;
    }
    .workflow-permission-box.workflow-under-po {
        margin: -8px 0 12px 60px;
    }
    .workflow-title {
        font-size: 13px;
        font-weight: 700;
        color: #0b4e78;
        margin-bottom: 2px;
        text-transform: uppercase;
    }
    .workflow-subtitle {
        font-size: 11px;
        color: #4d6a82;
        margin-bottom: 8px;
    }
    .row {
        overflow: auto;
    }
    .permission-save-bar {
        position: sticky;
        bottom: 10px;
        z-index: 20;
        display: flex;
        justify-content: flex-end;
        margin-top: 12px;
        padding: 8px 0;
        background: #fff;
    }
    
</style>
@endsection
