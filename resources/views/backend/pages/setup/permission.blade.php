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
                                $prevCategory = null;
                                $prevSubCategory = null;
                            @endphp

                            @foreach($permissionsList as $permission)

                                {{-- CATEGORY --}}
                                @if($prevCategory !== $permission->category)
                                    <div class="mt-4 mb-2">
                                        <h3 class="text-uppercase">{{ $permission->category }}</h3>
                                    </div>
                                    @php
                                        $prevCategory = $permission->category;
                                        $prevSubCategory = null;
                                    @endphp
                                @endif


                                {{-- SUB CATEGORY (Indented) --}}
                                @if($prevSubCategory !== $permission->sub_category)
                                    <div class="permission-group mb-3" style="margin-left: 30px;">
                                        <div class="row role-row">
                                            <div class="col-md-5">
                                                <h5 class="permission-title">
                                                    {{ ucfirst(strtolower($permission->sub_category)) }}
                                                </h5>
                                            </div>
                                            <div class="col-md-7">
                                                <div class="form-check form-check-inline">
                                    @php
                                        $prevSubCategory = $permission->sub_category;
                                    @endphp
                                @endif


                                {{-- ACTION CHECKBOX --}}
                                @php
                                    $action = explode('_', $permission->name)[0];
                                @endphp

                                <div class="form-check form-check-inline per-action">
                                    <input class="form-check-input permission-checkbox permission-{{ $permission->id }}"
                                        type="checkbox"
                                        name="permissions[{{ $permission->id }}][]"
                                        value="{{ $action }}"
                                        id="permission-{{ $permission->id }}-{{ $action }}"
                                        @if(in_array($action, old('permissions.' . $permission->id, []))) checked @endif>

                                    <label class="form-check-label" for="permission-{{ $permission->id }}-{{ $action }}">
                                        {{ ucfirst(str_replace('_', ' ', $action)) }}
                                    </label>
                                </div>


                                {{-- CLOSE SUB CATEGORY --}}
                                @php
                                    $next = $permissionsList[$loop->index + 1] ?? null;
                                @endphp

                                @if(!$next || $next->sub_category !== $permission->sub_category)
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                            @endforeach

                        </div>

                </div>
                <button type="submit" class="btn btn-primary mt-3">Save Permissions</button>
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

            $('input.permission-checkbox').each(function() {
                if (!this.checked) {
                    $(this).after(`<input type="hidden" name="permissions[${this.value}]" value="0">`);
                }
            });

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
    });
</script>
@endsection

@section('styles')
<style>
    .form-check.form-check-inline.per-action {
        width: 100px;
    }
    h4.permission-title {
        font-weight: bold;
        font-size: 16px;
        color: #1b547b;
    }
    .row.role-row {
        border-bottom: 1px solid #e8e8e8;
        padding: 10px 0px;
    }
    .row {
        overflow: auto;
    }
    
</style>
@endsection