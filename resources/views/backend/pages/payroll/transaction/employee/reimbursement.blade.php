@extends('backend.master.index')

@section('title', 'APPLY FOR REIMBURSEMENT')

@section('breadcrumbs')
    <span>EMPLOYEE / MAIN</span> / <span class="highlight">APPLY FOR REIMBURSEMENT</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">MAIN: APPLY FOR REIMBURSEMENT</h5>
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
                    <div class="form-group col-md-12">
                        <label>Expense Period</label>
                    </div>
                    <div class="form-group col-md-6 start_date">
                        <input type="date" class="form-control" id="start_date" name="start_date"/>
                    </div>
                    <div class="form-group col-md-6 end_date">
                        <input type="date" class="form-control" id="end_date" name="end_date"/>
                    </div>
                    <div class="form-group col-md-12 date">
                        <label>Date</label>
                        <input type="date" class="form-control" id="date" name="date"/>
                    </div>
                    <div class="form-group col-md-12 description">
                        <label>Description</label>
                        <textarea id="description" class="form-control" name="description" rows="3" cols="50" style="width: 100%;"></textarea>
                    </div>
                    <div class="form-group col-md-12 category">
                        <label>Category</label>
                        <select name="category" id="category" class="form-control">
                            <option value="0">TRANSPORTATION EXPENSE</option>
                            <option value="0">FOOD EXPENSE</option>
                            <option value="0">OFFICE EXPENSE</option>
                            <option value="0">OTHER</option>
                        </select>
                    </div>
                    <div class="form-group col-md-12 reason">
                        <label>Amount</label>
                        <input type="number" class="form-control" id="reason" name="reason"/>
                    </div>
                    <div class="form-group col-md-12 attachment">
                        <label>Attachments (Receipt)</label>
                        <input type="file" class="form-control" id="attachment" name="attachment" multiple/>
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
    <script src="/js/backend/pages/payroll/transaction/employee/reimbursement.js"></script>
@endsection