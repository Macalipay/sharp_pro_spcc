@extends('backend.master.index')

@section('title', 'MATERIALS')

@section('breadcrumbs')
    <span>MAINTENANCE</span> / <span class="highlight">MATERIALS</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <table id="materials_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="materials_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('benefits_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="classForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12 item_code">
                        <label>Item Code</label>
                        <input type="text" class="form-control" id="item_code" name="item_code"/>
                    </div>
                    <div class="form-group col-md-12 item_name">
                        <label>Item Name</label>
                        <input type="text" class="form-control" id="item_name" name="item_name"/>
                    </div>
                    <div class="form-group col-md-12 category">
                        <label>Category</label>
                        <select name="category" id="category" class="form-control">
                            <option selected>SELECT ONE</option>
                            @foreach ($material_categories as $material_category)
                                <option value="{{ $material_category->id }}">{{ $material_category->description}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-12 brand">
                        <label>Brand</label>
                        <input type="text" class="form-control" id="brand" name="brand"/>
                    </div>
                    <div class="form-group col-md-12 unit_of_measure">
                        <label>Unit of Measurement</label>
                        <select id="unit_of_measure" name="unit_of_measure[]" class="form-control minimal-multi" multiple>
                            <option value="PCS">PCS</option>
                            <option value="BOX">Box</option>
                            <option value="KG">KG</option>
                            <option value="METER">Meter</option>
                            <option value="OZ">Oz</option>
                            <option value="GRAMS">Grams</option>
                            <option value="SQM">SQM</option>
                            <option value="ROLL">ROLL</option>
                            <option value="SET">SET</option>
                            <option value="EA">EA</option>
                            <option value="MTR">MTR</option>
                            <option value="CAN">CAN</option>
                            <option value="LOT">LOT</option>
                            <option value="SET">SET</option>
                            <option value="TUBE">TUBE</option>
                            <option value="UNIT">UNIT</option>
                            <option value="CU.M">CU.M</option>
                            <option value="GAL">GAL</option>
                            <option value="LTR">LTR</option>
                            <option value="MD">MD</option>
                            <option value="QT">QT</option>
                            <option value="TIN">TIN</option>
                            <option value="BAG">BAG</option>
                            <option value="BAG">REAM</option>
                            <option value="DOZEN">DOZEN</option>
                            <option value="ASSY">ASSY</option>
                            <option value="1/4 Ltr">1/4 Ltr</option>
                            <option value="1/2 Ltr">1/2 Ltr</option>
                            <option value="ELF">ELF</option>
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
    <script src="/js/backend/pages/purchasing/maintenance/materials.js"></script>
@endsection

@section('styles')
<style>
.select2-container--default .select2-selection--multiple {
    border-radius: 6px;
    padding: 4px;
    border: 1px solid #ced4da;
    min-height: 38px;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #f8f9fa;
    border: 1px solid #ccc;
    border-radius: 4px;
    color: #333;
    padding: 2px 8px;
    font-size: 12px;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: #888;
    margin-right: 4px;
}
</style>


@endsection