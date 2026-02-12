@extends('backend.master.index')

@section('title', 'ALLOWANCE')

@section('breadcrumbs')
    <span>SETUP / PAYROLL</span> / <span class="highlight">ALLOWANCE</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <table id="allowance_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="allowance_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('allowance_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="allowanceForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12 name">
                        <label>NAME</label>
                        <input type="text" class="form-control" id="name" name="name"/>
                    </div>
                    <div class="form-group col-md-12 description">
                        <label>DESCRIPTION</label>
                        <textarea class="form-control" name="description" id="description"></textarea>
                    </div>
                    <div class="form-group col-md-12 amount">
                        <label>AMOUNT</label>
                        <input type="number" class="form-control" id="amount" name="amount" min="1"/>
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
    <script src="/js/backend/pages/payroll/maintenance/allowance.js"></script>
@endsection