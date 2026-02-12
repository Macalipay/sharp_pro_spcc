@extends('backend.master.index')

@section('title', 'REIMBURSEMENT')

@section('breadcrumbs')
    <span>MAINTENANCE</span> / <span class="highlight">REIMBURSEMENT</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <table id="reimbursements_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="reimbursements_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('reimbursements_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="reimbursementsForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12 name">
                        <label>NAME</label>
                        <input type="text" class="form-control" id="name" name="name"/>
                    </div>
                    <div class="form-group col-md-12 chart_id">
                        <label>CHART OF ACCOUNT</label>
                        <select name="chart_id" id="chart_id" class="form-control">
                            <option value=""></option>
                            @foreach ($record as $item)
                            <option value="{{$item->id}}">{{$item->account_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-12 status">
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
@endsection

@endsection

@section('scripts')
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="/js/backend/pages/payroll/maintenance/reimbursements.js"></script>
@endsection