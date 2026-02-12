@extends('backend.master.index')

@section('title', 'OVERTIME APPLICATION')

@section('breadcrumbs')
    <span>EMPLOYEE / OVERTIME APPLICATION</span> / <span class="highlight">APPLY FOR OVERTIME</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">OVERTIME APPLICATION: APPLY FOR OVERTIME</h5>
            </div>
            @include('backend.partial.flash-message')
            <div class="col-12">
                <div class="card-body">
                    <table id="earnings_table" class="table table-striped" style="width:100%">
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="earnings_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('earnings_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="earningForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12 payroll_period">
                        <label>PAYROLL PERIOD</label>
                        <select name="payroll_period" id="payroll_period" class="form-control">
                            <option value="0">MAY 1, 2023 - May 15, 2023</option>
                            <option value="0">MAY 15, 2023 - May 30, 2023</option>
                        </select>
                    </div>
                    <div class="form-group col-md-12 date">
                        <label>Select date to configure:</label>
                        <input type="date" class="form-control" id="date" name="date"/>
                    </div>
                    <div class="form-group col-md-12 overtime">
                        <label>How many extra hours/minutes will you work?</label>
                    </div>
                    <div class="form-group col-md-6 hours">
                        <input type="number" class="form-control" id="hours" name="hours" placeholder="Number of hours"/>
                    </div>
                    <div class="form-group col-md-6 name">
                        <input type="number" class="form-control" id="minutes" name="minutes" placeholder="Number of minutes"/>
                    </div>
                    <div class="form-group col-md-12 reason">
                        <label>Reason for Request:</label>
                        <input type="textarea" class="form-control" id="reason" name="reason"/>
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
    <script src="/js/backend/pages/payroll/transaction/employee/overtime_application.js"></script>
@endsection