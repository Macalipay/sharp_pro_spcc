@extends('backend.master.index')

@section('title', 'HOLIDAY')

@section('breadcrumbs')
    <span>MAINTENANCE</span> / <span class="highlight">HOLIDAY</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <table id="holiday_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="holiday_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('holiday_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form id="holidayForm" method="post" class="form-record">
                <div class="row">
                    <div class="form-group col-12">
                        <label for="">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required/>
                    </div>
                    <div class="form-group col-12">
                        <label for="">Date</label>
                        <input type="date" class="form-control" id="date" name="date" value="0" min="0" required/>
                    </div>
                    <div class="form-group col-md-12">
                        <label>TYPE</label>
                        <select name="holiday_type_id" id="holiday_type_id" class="form-control">
                            <option value=""></option>
                            @foreach ($record as $item)
                            <option value="{{$item->id}}">{{$item->name}}</option>
                            @endforeach
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
<script src="/js/backend/pages/payroll/maintenance/holiday.js"></script>
@endsection