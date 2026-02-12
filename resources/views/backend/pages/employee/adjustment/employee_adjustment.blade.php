@extends('backend.master.index')

@section('title', 'Employee Adjustment')

@section('breadcrumbs')
    <span>Employee</span>  /  <span class="highlight">Adjustment</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <table id="employee_adjustment_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="adjustments_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('departments_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form id="adjustments_form" method="post" class="form-record">
            <div class="row">
                <div class="form-group col-md-12">
                    <label for="employee_id">Employee</label>
                    <select class="form-control" id="employee_id" name="employee_id">
                        @foreach ($employments as $employment)
                            <option value="{{$employment->id}}">{{ ($employment->employee_information->firstname ?? '') . ' ' . ($employment->employee_information->lastname ?? '') }}</option>

                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-12">
                    <label for="adjustment_type">Adjustment Type</label>
                    <select class="form-control" id="adjustment_type" name="adjustment_type">
                        <option value="SALARY">SALARY</option>
                        <option value="ALLOWANCE">ALLOWANCE</option>
                        <option value="DEDUCTION">DEDUCTION</option>
                        <option value="POSITION">POSITION</option>
                        <option value="CORRECTION">CORRECTION</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6">
                    <label for="old_value">Old Value</label>
                    <input type="number" step="0.01" class="form-control" id="old_value" name="old_value" placeholder="Old Value">
                </div>

                <div class="form-group col-md-6">
                    <label for="new_value">New Value</label>
                    <input type="number" step="0.01" class="form-control" id="new_value" name="new_value" placeholder="New Value">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-12">
                    <label for="amount">Amount (for Allowance / Deduction)</label>
                    <input type="number" step="0.01" class="form-control" id="amount" name="amount" placeholder="Amount">
                </div>

                <div class="form-group col-md-12">
                    <label for="effective_date">Effective Date</label>
                    <input type="date" class="form-control" id="effective_date" name="effective_date">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-12">
                    <label for="remarks">Remarks</label>
                    <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Enter remarks"></textarea>
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-12">
                    <label for="status">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="PENDING">PENDING</option>
                        <option value="APPROVED">APPROVED</option>
                        <option value="REJECTED">REJECTED</option>
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
<script src="/js/backend/pages/employee/adjustment.js"></script>
@endsection