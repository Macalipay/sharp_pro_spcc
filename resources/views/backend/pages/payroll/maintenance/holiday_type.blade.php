@extends('backend.master.index')

@section('title', 'HOLIDAY TYPE')

@section('breadcrumbs')
    <span>MAINTENANCE</span> / <span class="highlight">HOLIDAY TYPE</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <table id="holiday_type_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="holiday_type_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('holiday_type_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form id="holiday_typeForm" method="post" class="form-record">
                <div class="row">
                    <div class="form-group col-12">
                        <label for="">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required/>
                    </div>
                    <div class="form-group col-12">
                        <label for="">Multiplier</label>
                        <input type="number" class="form-control" id="multiplier" name="multiplier" value="0" min="0" required/>
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
<script src="/js/backend/pages/payroll/maintenance/holiday_type.js"></script>
@endsection