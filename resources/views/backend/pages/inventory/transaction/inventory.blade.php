@extends('backend.master.index')

@section('title', 'INVENTORY')

@section('breadcrumbs')
    <span>TRANSACTION</span> / <span class="highlight">INVENTORY</span>
@endsection

@section('content')
<style>
#audit-trails-table-container {
    max-height: 70vh;
    overflow-y: auto;
    overflow-x: auto;
}
</style>
<div class="row">
    <div class="col-md-12">
        <table id="inventory_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div id="inventory-history-container"></div>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="inventory_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('inventory_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
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

                    <div class="form-group col-md-12 critical_level">
                        <label>Critical Level</label>
                        <input type="number" class="form-control" id="critical_level" name="critical_level" placeholder="CRITICAL LEVEL"/>
                    </div>
                </div>
            </form>
        </div>
        <div class="sc-modal-footer text-right">
            <button class="btn btn-sm btn-primary btn-sv" onclick="$('#sv').click()">SAVE</button>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="damage_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('damage_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="damageform" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12">
                        <label>TYPE</label>
                        <select class="form-control" id="conflict_type" name="conflict_type" required>
                            <option value="">-- Select Conflict Type --</option>
                            <option value="DAMAGE">Damage</option>
                            <option value="RETURN">Return</option>
                            <option value="THEFT">Theft</option>
                        </select>
                    </div>
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
            <button type="submit" class="btn btn-sm btn-primary" form="damageform">SAVE</button>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="transaction_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('transaction_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="transactform" class="form-record">
                @csrf
                <input type="hidden" name="inventory_id" id="inventory_id">
                <div class="row">
                    <div class="form-group col-md-12 code">
                        <label>QUANTITY</label>
                        <input type="text" class="form-control" id="code" name="code" placeholder="CODE"/>
                    </div>
                    <div class="form-group col-md-12 code">
                        <label>QUANTITY</label>
                        <input type="text" class="form-control" id="quantity" name="quantity" placeholder="QUANTITY"/>
                    </div>
                    <div class="form-group col-md-12 date">
                        <label>DATE</label>
                        <input type="date" class="form-control" id="date" name="date"/>
                    </div>
                    <div class="form-group col-md-12 project_id">
                        <label>REQUESTED BY</label>
                        <select name="requested_by" id="requested_by" class="form-control">
                            <option selected>SELECT ONE</option>
                           @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">
                                    {{ 
                                        data_get($employee, 'employee_information.firstname', '') 
                                        . ' ' . 
                                        data_get($employee, 'employee_information.lastname', '') 
                                    }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-12 project_id">
                        <label>ISSUED BY</label>
                        <select name="issued_by" id="issued_by" class="form-control">
                            <option selected>SELECT ONE</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">
                                    {{ 
                                        data_get($employee, 'employee_information.firstname', '') 
                                        . ' ' . 
                                        data_get($employee, 'employee_information.lastname', '') 
                                    }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-12 project_id">
                        <label>APPROVED BY</label>
                        <select name="approved_by" id="approved_by" class="form-control">
                            <option selected>SELECT ONE</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">
                                    {{ 
                                        data_get($employee, 'employee_information.firstname', '') 
                                        . ' ' . 
                                        data_get($employee, 'employee_information.lastname', '') 
                                    }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-12 remarks">
                        <label>REMARKS</label>
                        <textarea name="remarks" id="remarks" class="form-control" cols="30" rows="10" placeholder="REMARKS"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="sc-modal-footer text-right">
            <button type="button" class="btn btn-sm btn-primary btn-sv">SAVE</button>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="inventory_transfer_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('inventory_transfer_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="classesForm" class="form-record">
                <input type="hidden" id="inventory_id" name="inventory_id" value="">
                <div class="row">
                    <div class="form-group col-md-12 project_id">
                        <label>ITEM NAME</label>
                        <input type="text" class="form-control" id="transfer_item_name" name="transfer_item_name" readonly/>
                    </div>

                    <div class="form-group col-md-12 project_id">
                        <label>FROM PROJECT</label>
                        <input type="text" class="form-control" id="from_project" name="from_project" readonly/>
                    </div>

                    <div class="form-group col-md-12 project_id">
                        <label>TO PROJECT</label>
                        <select name="to_project" id="to_project" class="form-control">
                            <option selected>SELECT ONE</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->project_name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-12 unit_price">
                        <label>Unit Price</label>
                        <input type="text" class="form-control" id="unit_price" name="unit_price" placeholder="UNIT PRICE"/>
                    </div>

                    <div class="form-group col-md-12 total_amt">
                        <label>Total Amount</label>
                        <input type="text" class="form-control" id="total_amt" name="total_amt" placeholder="TOTAL AMOUNT"/>
                    </div>

                    <div class="form-group col-md-12 code">
                        <label>QUANTITY</label>
                        <input type="text" class="form-control" id="transfer_quantity" name="transfer_quantity" placeholder="QUANTITY"/>
                    </div>
                    <div class="form-group col-md-12 date">
                        <label>DATE</label>
                        <input type="date" class="form-control" id="transfer_date" name="transfer_date"/>
                    </div>
                    <div class="form-group col-md-12 remarks">
                        <label>REMARKS</label>
                        <textarea name="transfer_remarks" id="transfer_remarks" class="form-control" cols="30" rows="10" placeholder="REMARKS"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="sc-modal-footer text-right">
            <button type="button" id="transferBtn" class="btn btn-primary">Save</button>
        </div>
    </div>
</div>
@endsection
@endsection

@section('scripts')
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="/js/backend/pages/inventory/transaction/inventory.js"></script>
@endsection
