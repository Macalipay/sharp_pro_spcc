@extends('backend.master.index')

@section('title', 'LEAVE REQUEST')

@section('breadcrumbs')
    <span>TRANSACTION / PAYROLL</span> / <span class="highlight">LEAVE REQUEST</span>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        @include('backend.partial.flash-message')
        <table id="leave_request_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="leave_request_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('leave_request_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="leave_requestForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-6 employee_id">
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
                    <div class="form-group col-md-6 leave_type_id">
                        <label>LEAVE TYPE</label>
                        <select name="leave_type_id" id="leave_type_id" class="form-control">
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="form-group col-md-12 description">
                        <label>DESCRIPTION</label>
                        <textarea class="form-control" id="description" name="description"></textarea>
                    </div>
                    <div class="form-group col-md-6 start_date">
                        <label>START</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"/>
                    </div>
                    <div class="form-group col-md-6 end_date">
                        <label>END</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"/>
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
    <script src="/js/backend/pages/payroll/transaction/leave_request.js"></script>
@endsection

@section('styles')
<style>
span.status-1 {
    color: green;
    font-weight: bold;
}
span.status-2 {
    color: red;
    font-weight: bold;
}
</style>
@endsection