@extends('backend.master.index')

@section('title', 'WORK ASSIGNMENTS')

@section('breadcrumbs')
    <span>MAINTENANCE</span> / <span class="highlight">WORK ASSIGNMENTS</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <table id="work_assignments_table" class="table table-striped" style="width:100%"> </table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="work_assignments_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('work_assignments_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="workAssignmentsForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12 title">
                        <label>TITLE</label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="TITLE"/>
                    </div>
                    <div class="form-group col-md-12 status">
                        <label>STATUS</label>
                        <select class="form-control" name="status" id="status">
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
    <script src="/js/backend/pages/payroll/maintenance/work_assignments.js"></script>
@endsection