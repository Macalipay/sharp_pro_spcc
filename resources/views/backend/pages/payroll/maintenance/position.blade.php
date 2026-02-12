@extends('backend.master.index')

@section('title', 'Positions')

@section('breadcrumbs')
    <span>Maintenance</span>  /  <span class="highlight">Positions</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <table id="positions_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="positions_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('benefits_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form id="positionForm" method="post" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12">
                        <label for="">Description</label>
                        <input type="text" class="form-control" id="description" name="description" placeholder="Description">
                    </div>
                    <div class="form-group col-md-12">
                        <label for="">Description</label>
                        <select name="position_type" id="position_type" class="form-control">
                            <option selected>SELECT ONE</option>
                            <option value="SINGLE">SINGLE</option>
                            <option value="MULTIPLE">MULTIPLE</option>
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
<script src="/js/backend/pages/payroll/maintenance/positions.js"></script>
@endsection