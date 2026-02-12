@extends('backend.master.index')

@section('title', 'QUIT CLAIMS')

@section('breadcrumbs')
    <span>TRANSACTION / PAYROLL</span> / <span class="highlight">QUIT CLAIMS</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        @include('backend.partial.flash-message')
        <table id="quit_claims_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="quit_claims_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('quit_claims_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="quit_claims_requestForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12 employee_id">
                        @include('backend.partial.component.lookup', [
                            'label' => "EMPLOYEE NUMBER",
                            'placeholder' => '<NEW>',
                            'id' => "employee_name",
                            'title' => "EMPLOYEE NUMBER",
                            'url' => "/payroll/employee-information/get",
                            'data' => array(
                                array('data' => "DT_RowIndex", 'title' => "#"),
                                array('data' => "employee_no", 'title' => "Employee Number"),
                                array('data' => "full_name", 'title' => "Name"),
                                array('data' => "email", 'title' => "Email"),
                            ),
                            'disable' => true,
                            'lookup_module' => 'employee-information',
                            'modal_type'=> '',
                            'lookup_type' => 'main'
                        ])
                    </div>
                    <div class="form-group col-md-6">
                        <label>Last Pay:</label>
                        <input type="number" class="form-control" id="last_pay" name="last_pay" disabled/>
                    </div>
                    <div class="form-group col-md-6">
                        <label>13th Month Pay:</label>
                        <input type="number" class="form-control" id="month_pay" name="month_pay" disabled/>
                    </div>
                    <div class="form-group col-md-12">
                        <label>Total Pay:</label>
                        <input type="number" class="form-control" id="total_pay" name="total_pay"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="release_confirm">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('release_confirm').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-md-12">
                    <span>Are you sure you want to release this item?</span>
                </div>
                <div class="col-md-12 text-right">
                    <button class="btn btn-success" onclick="yesClaim()">YES</button>
                    <button class="btn btn-primary" onclick="scion.create.sc_modal('release_confirm').hide('all', modalHideFunction)">NO</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="additions_modal">
    <div class="sc-modal-dialog sc-xl">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('additions_modal').hide('all', updateActionClose)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-md-12 text-right">
                    <button class="btn btn-sm btn-primary" onclick="addAdditionals()">ADD</button>
                </div>
                <div class="col-md-12">
                    <table id="quit_claims_additions_table" class="table table-striped" style="width:100%"></table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="quit_claims_additions_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('quit_claims_additions_form').hide()"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="additions_modal_requestForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Earning Type:</label>
                        <select name="earning_type_id" id="earning_type_id" class="form-control form-control-sm">
                            <option value=""></option>
                            @foreach ($earnings as $item)
                            <option value="{{$item->id}}">{{$item->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Amount:</label>
                        <input type="number" class="form-control form-control-sm" id="amount" name="amount" value="0"/>
                    </div>
                    <div class="form-group col-md-12">
                        <label>Description:</label>
                        <textarea name="description" id="description" class="form-control form-control-sm"></textarea>
                    </div>
                    <div class="form-group col-md-12">
                        <label>Remarks:</label>
                        <textarea name="remarks" id="remarks" class="form-control form-control-sm"></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="deductions_modal">
    <div class="sc-modal-dialog sc-xl">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('deductions_modal').hide('all', updateActionClose)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-md-12 text-right">
                    <button class="btn btn-sm btn-primary" onclick="addDeductions()">ADD</button>
                </div>
                <div class="col-md-12">
                    <table id="quit_claims_deductions_table" class="table table-striped" style="width:100%"></table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="quit_claims_deductions_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('quit_claims_deductions_form').hide()"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="deductions_modal_requestForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Deduction Type:</label>
                        <select name="deduction_type_id" id="deduction_type_id" class="form-control form-control-sm">
                            <option value=""></option>
                            @foreach ($deductions as $item)
                            <option value="{{$item->id}}">{{$item->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Amount:</label>
                        <input type="number" class="form-control form-control-sm" id="deduction_amount" name="deduction_amount" value="0"/>
                    </div>
                    <div class="form-group col-md-12">
                        <label>Description:</label>
                        <textarea name="deduction_description" id="deduction_description" class="form-control form-control-sm"></textarea>
                    </div>
                    <div class="form-group col-md-12">
                        <label>Remarks:</label>
                        <textarea name="deduction_remarks" id="deduction_remarks" class="form-control form-control-sm"></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@endsection

@section('scripts')
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="/js/backend/pages/payroll/transaction/quit_claims.js"></script>
@endsection

@section('styles')
    <link rel="stylesheet" href="/css/custom/quit_claim.css">
@endsection