@extends('backend.master.index')

@section('title', 'OVERTIME REQUEST')

@section('breadcrumbs')
    <span>TRANSACTION / PAYROLL</span> / <span class="highlight">OVERTIME REQUEST</span>
@endsection

@section('content')
<div class="row">
    <div class="col-4">
        <div class="form-group">
            <label>Start Date</label>
            <input type="date" id="start_date" name="start_date" class="form-control form-control-sm"/>
        </div>
    </div>
    <div class="col-4">
        <div class="form-group">
            <label>End Date</label>
            <input type="date" id="end_date" name="end_date" class="form-control form-control-sm"/>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <button class="btn btn-sm btn-primary" onclick="generateRecord()">Generate Record</button>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <table id="overtime_request_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="overtime_request_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('overtime_request_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="overtime_requestForm" class="form-record">
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
                    <div class="form-group col-md-12 reason">
                        <label>REASON</label>
                        <textarea class="form-control" id="reason" name="reason"></textarea>
                    </div>
                    <div class="form-group col-md-12 ot_date">
                        <label>DATE</label>
                        <input type="date" class="form-control" id="ot_date" name="ot_date"/>
                    </div>
                    <div class="form-group col-md-6 start_time">
                        <label>START TIME</label>
                        <input type="datetime-local" class="form-control" id="start_time" name="start_time"/>
                    </div>
                    <div class="form-group col-md-6 end_date">
                        <label>END TIME</label>
                        <input type="datetime-local" class="form-control" id="end_time" name="end_time"/>
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
    <script src="/js/backend/pages/payroll/transaction/overtime_request.js"></script>
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