@extends('backend.master.index')

@section('title', 'MATERIALS REQUISITION FORM')

@section('breadcrumbs')
    <span>TRANSACTION</span> / <span class="highlight">MATERIALS REQUISITION FORM</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <table id="material_requisition_form_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="material_requisition_form_form">
    <div class="sc-modal-dialog sc-xl" style="min-width: 90%; margin: 20px auto;">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('material_requisition_form_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="material_requisition_formForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-3 date">
                        <label for="date">Date</label>
                        <input type="date" class="form-control" id="date" name="date">
                    </div>
                    <div class="form-group col-md-3 mrf_no">
                        <label for="mrf_no">MRF No</label>
                        <input type="text" class="form-control" id="mrf_no" name="mrf_no">
                    </div>
                    <div class="form-group col-md-3 project_id">
                        <label for="project_id">Project</label>
                        <select class="form-control" id="project_id" name="project_id">
                            <option value=""></option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3 location">
                        <label for="location">Location</label>
                        <input type="text" class="form-control" id="location" name="location">
                    </div>

                    <div class="form-group col-md-4 requested_by">
                        <label for="requested_by">Requested By</label>
                        <select class="form-control" id="requested_by" name="requested_by">
                            <option value=""></option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->firstname }} {{ $employee->lastname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4 noted_by">
                        <label for="noted_by">Noted By</label>
                        <select class="form-control" id="noted_by" name="noted_by">
                            <option value=""></option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->firstname }} {{ $employee->lastname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4 approved_by">
                        <label for="approved_by">Approved By</label>
                        <select class="form-control" id="approved_by" name="approved_by">
                            <option value=""></option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->firstname }} {{ $employee->lastname }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12 mt-2">
                        <hr>
                        <label class="font-weight-bold">Details</label>
                    </div>

                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="mrf_details_table">
                                <thead>
                                    <tr>
                                        <th style="min-width:110px;">Quantity</th>
                                        <th style="min-width:100px;">Unit</th>
                                        <th style="min-width:220px;">Particulars</th>
                                        <th style="min-width:180px;">Location To Be Used</th>
                                        <th style="min-width:140px;">Date Required</th>
                                        <th style="min-width:140px;">Approved Quantity</th>
                                        <th style="min-width:220px;">Remarks</th>
                                        <th style="width:50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="mrf_details_body"></tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary" id="add_mrf_detail_row_btn">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
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
    <script src="/js/backend/pages/purchasing/transaction/material_requisition_form.js"></script>
@endsection
