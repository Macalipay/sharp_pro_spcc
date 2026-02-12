@extends('backend.master.index')

@section('title', 'User')

@section('breadcrumbs')
    <span>Setup</span>  / <span>User Access</span> / <span class="highlight">User</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <table id="users_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="users_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('users_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form id="usersForm" method="post" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12">
                        <label for="">Firstname</label>
                        <input type="text" class="form-control" id="firstname" name="firstname">
                    </div>
                    <div class="form-group col-md-12">
                        <label for="">Middlename</label>
                        <input type="text" class="form-control" id="middlename" name="middlename">
                    </div>
                    <div class="form-group col-md-12">
                        <label for="">Lastname</label>
                        <input type="text" class="form-control" id="lastname" name="lastname">
                    </div>
                    <div class="form-group col-md-12">
                        <label for="">Suffix</label>
                        <input type="text" class="form-control" id="suffix" name="suffix">
                    </div>
                    <div class="form-group col-md-12">
                        <label for="">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    <div class="form-group col-md-12">
                        <label>STATUS</label>
                        <select name="status" id="status" class="form-control">
                            <option value="1">ACTIVE</option>
                            <option value="0">INACTIVE</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <div class="sc-modal-footer text-right">
            <button class="btn btn-sm btn-primary btn-sv" onclick="$('#sv').click()">SAVE</button>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="generate_key_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('generate_key_form').hide('all', modalHideFunction)" ><i class="fas fa-times" id="generate_key_close"></i></span>
        </div>
        <div class="sc-modal-body">
            <form id="generate_keyForm" method="post" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12">
                        <label>ROLE</label>
                        <select name="role" id="role" class="form-control">
                            <option value=""></option>
                            @foreach ($role as $item)
                            <option value="{{$item->id}}">{{$item->name}}</option>
                            @endforeach
                        </select>
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
<script src="/js/backend/pages/setup/user.js"></script>
@endsection