@extends('backend.master.index')

@section('title', 'WITHHOLDING TAX')

@section('breadcrumbs')
    <span>Maintenance</span>  /  <span class="highlight">Withholding Tax</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <table id="withholding_tax_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="withholding_tax_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('withholding_tax_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form id="withholding_taxForm" method="post" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12">
                        <label for="">FREQUENCY:</label>
                        <select name="frequency" id="frequency" class="form-control">
                            <option value="" style="display:none;">PLEASE SELECT FREQUENCY</option>
                            <option value="1">MONTHLY</option>
                            <option value="2">SEMI-MONTHLY</option>
                            <option value="3">WEEKLY</option>
                            <option value="4">DAILY</option>
                        </select>
                    </div>
                    <div class="form-group col-md-12">
                        <label for="">TAXABLE INCOME FROM:</label>
                        <input type="number" class="form-control" id="range_from" name="range_from" placeholder="" value="0">
                    </div>
                    <div class="form-group col-md-12">
                        <label for="">TAXABLE INCOME TO:</label>
                        <input type="number" class="form-control" id="range_to" name="range_to" placeholder="" value="0">
                    </div>
                    <div class="form-group col-md-12">
                        <label for="">FIX TAX:</label>
                        <input type="number" class="form-control" id="fix_tax" name="fix_tax" placeholder="" value="0">
                    </div>
                    <div class="form-group col-md-12">
                        <label for="">RATE ON EXCESS:</label>
                        <input type="number" class="form-control" id="rate_on_excess" name="rate_on_excess" placeholder="" value="0">
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
<script src="/js/backend/pages/payroll/maintenance/withholding.js"></script>
@endsection