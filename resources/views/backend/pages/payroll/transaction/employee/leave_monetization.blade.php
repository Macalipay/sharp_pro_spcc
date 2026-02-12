@extends('backend.master.index')

@section('title', 'APPLY FOR LEAVE MONETIZATION')

@section('breadcrumbs')
    <span>EMPLOYEE / LEAVE APPLICATION</span> / <span class="highlight">APPLY FOR LEAVE MONETIZATION</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">LEAVE APPLICATION: APPLY FOR LEAVE MONETIZATION</h5>
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
                    <div class="form-group col-md-12 leave_type">
                        <label>LEAVE TYPE</label>
                        <select name="leave_type" id="leave_type" class="form-control">
                            <option value="0">SICK LEAVE</option>
                            <option value="0">VACATION LEAVE</option>
                            <option value="0">MATERNITY LEAVE</option>
                            <option value="0">BIRTHDAY LEAVE</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6 credits">
                        <label>Number of Credits</label>
                        <input type="number" class="form-control" id="credits" name="credits"/>
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
    <script src="/js/backend/pages/payroll/transaction/employee/leave_monetization.js"></script>
@endsection