@extends('backend.master.index')

@section('title', 'DUE DATE TEMPLATES')

@section('breadcrumbs')
    <span>MAINTENANCE</span> / <span class="highlight">DUE DATE TEMPLATES</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <table id="due_date_templates_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="due_date_templates_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('due_date_templates_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="due_date_templatesForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12 template_text">
                        <label>Due Date Template</label>
                        <input type="text" class="form-control" id="template_text" name="template_text" placeholder="e.g. UPON RECEIPT"/>
                    </div>
                    <div class="form-group col-md-12 description">
                        <label>Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
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
    <script src="/js/backend/pages/purchasing/maintenance/due_date_templates.js"></script>
@endsection

