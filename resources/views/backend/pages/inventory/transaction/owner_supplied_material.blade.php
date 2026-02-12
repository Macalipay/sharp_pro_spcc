@extends('backend.master.index')

@section('title', 'OWNER SUPPLIED MATERIAL')

@section('breadcrumbs')
    <span>TRANSACTION</span> / <span class="highlight">OWNER SUPPLIED MATERIAL</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <table id="osm_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="owner_supplied_material_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('owner_supplied_material_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="classesForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12 project_id">
                        <label>Project</label>
                        <select name="project_id" id="project_id" class="form-control">
                            <option selected>SELECT ONE</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->project_name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-12 material_id">
                        <label>Material</label>
                        <select name="material_id" id="material_id" class="form-control">
                            <option selected>SELECT ONE</option>
                            @foreach ($materials as $material)
                                <option value="{{ $material->id }}">{{ $material->item_name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-12 description">
                        <label>Description</label>
                        <input type="text" class="form-control" id="description" name="description" placeholder="DESCRIPTION"/>
                    </div>
                </div>
            </form>
        </div>
        <div class="sc-modal-footer text-right">
            <button class="btn btn-sm btn-primary btn-sv" onclick="$('#sv').click()">SAVE</button>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="inventory_transaction_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('inventory_transaction_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="classesForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12 code">
                        <label>QUANTITY</label>
                        <input type="text" class="form-control" id="quantity" name="quantity" placeholder="QUANTITY"/>
                    </div>
                    <div class="form-group col-md-12 date">
                        <label>DATE</label>
                        <input type="date" class="form-control" id="date" name="date"/>
                    </div>
                    <div class="form-group col-md-12 remarks">
                        <label>REMARKS</label>
                        <textarea name="remarks" id="remarks" class="form-control" cols="30" rows="10" placeholder="REMARKS"></textarea>
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
    <script src="/js/backend/pages/inventory/transaction/owner_supplied_material.js"></script>
@endsection
