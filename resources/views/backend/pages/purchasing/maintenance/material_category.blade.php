@extends('backend.master.index')

@section('title', 'MATERIAL CATEGORY')

@section('breadcrumbs')
    <span>MAINTENANCE</span> / <span class="highlight">MATERIAL CATEGORY</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <table id="material_category_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="material_category_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('benefits_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="classForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12 description">
                        <label>Description</label>
                        <input type="text" class="form-control" id="description" name="description"/>
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
    <script src="/js/backend/pages/purchasing/maintenance/material_category.js"></script>
@endsection

@section('styles')


@endsection