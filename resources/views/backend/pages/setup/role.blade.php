@extends('backend.master.index')

@section('title', 'Role')

@section('breadcrumbs')
    <span>Setup</span>  / <span>User Access</span> / <span class="highlight">Role</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <table id="role_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="role_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('role_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form id="roleForm" method="post" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12">
                        <label for="">Name</label>
                        <input type="text" class="form-control" id="name" name="name">
                    </div>
                </div>
            </form>
        </div>
        <div class="sc-modal-footer text-right">
            <button class="btn btn-sm btn-primary btn-sv" onclick="$('#sv').click()">SAVE</button>
        </div>
    </div>
</div>
@endsection
    
@endsection

@section('scripts')
<script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="/js/backend/pages/setup/role.js"></script>
@endsection