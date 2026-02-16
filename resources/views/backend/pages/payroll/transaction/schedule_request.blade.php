@extends('backend.master.index')

@section('title', 'SCHEDULE REQUEST')

@section('breadcrumbs')
    <span>TRANSACTION / PAYROLL</span> / <span class="highlight">SCHEDULE REQUEST</span>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <table id="schedule_request_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="schedule_request_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('schedule_request_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="schedule_requestForm" class="form-record">
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
                    <div class="form-group col-md-6 request_date">
                        <label>REQUEST DATE <span class="required">*</span></label>
                        <input type="date" class="form-control" id="request_date" name="request_date"/>
                    </div>
                    <div class="form-group col-md-6 schedule_type">
                        <label>SCHEDULE TYPE <span class="required">*</span></label>
                        <select class="form-control" id="schedule_type" name="schedule_type">
                            <option value="">SELECT TYPE</option>
                            <option value="monthly">MONTHLY</option>
                            <option value="semi-monthly">SEMI-MONTHLY</option>
                            <option value="weekly">WEEKLY</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6 period_start">
                        <label>PERIOD START <span class="required">*</span></label>
                        <input type="date" class="form-control" id="period_start" name="period_start"/>
                    </div>
                    <div class="form-group col-md-6 period_end">
                        <label>PERIOD END <span class="required">*</span></label>
                        <input type="date" class="form-control" id="period_end" name="period_end"/>
                    </div>
                    <div class="form-group col-md-6 start_time">
                        <label>TIME IN <span class="required">*</span></label>
                        <input type="time" class="form-control" id="start_time" name="start_time"/>
                    </div>
                    <div class="form-group col-md-6 end_time">
                        <label>TIME OUT <span class="required">*</span></label>
                        <input type="time" class="form-control" id="end_time" name="end_time"/>
                    </div>
                    <div class="form-group col-md-12 reason">
                        <label>REASON</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3"></textarea>
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
    <script src="/js/backend/pages/payroll/transaction/schedule_request.js"></script>
@endsection

@section('styles')
<style>
span.sr-status-pending { color: #d39e00; font-weight: 700; }
span.sr-status-approved { color: #218838; font-weight: 700; }
span.sr-status-declined { color: #c82333; font-weight: 700; }
</style>
@endsection
