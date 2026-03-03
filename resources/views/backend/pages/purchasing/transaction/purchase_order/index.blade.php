@extends('backend.master.index')

@section('title', 'PURCHASE ORDER')

@section('breadcrumbs')
    <span>TRANSACTION</span>  /  <span class="highlight">PURCHASE ORDER</span>
@endsection

@php
$user = auth()->user();
@endphp

@section('content')
<div class="row" style="height:100%;">
    <!-- <div class="col-6">
        <div class="form-group df-container">
            <label>Date Filter</label>
            <input id="" type="date" class="date-filter"> 
            <input id="" type="date" class="date-filter">
            <button type="submit" class="filter-button button">Filter</button>
        </div>
    </div> -->
    <div class="col-12" style="height:100%;">
        <div class="grid-container">
            <div class="grid-table-1">
                <div class="status-container mt-2">
                   @foreach ($status as $k => $item)
                        <button class="btn btn-sm btn-light status-{{ str_replace(" ", "_", $k) }}" 
                        style="font-size: 10px;padding: 3px 5px !important;" 
                        onclick="generateTable('{{ str_replace(" ", "_", $k) }}')" data-status="{{ str_replace(" ", "_", $k) }}">
                        (<span class="count-table">{{$item}}</span>) {{$k}}</button>
                    @endforeach
                </div>
              <div class="row">
                <!-- PROJECT -->
                <div class="col-md-3">
                    <label>Project:</label>
                    <select name="project_id" id="project_id" class="form-control">
                        <option value="all_project">All Project</option>
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                        @endforeach
                    </select>
                </div>

                 <!-- START DATE -->
                <div class="col-md-3">
                    <label for="start_date">PO #</label>
                    <input type="text" class="form-control" id="filter_po_no" name="filter_po_no" placeholder="Enter Po #">
                </div>

                <!-- START DATE -->
                <div class="col-md-3">
                    <label for="start_date">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date">
                </div>

                <!-- END DATE WITH SEARCH ICON -->
                <div class="col-md-3">
                    <label for="end_date">End Date</label>
                    <div class="input-group">
                        <input type="date" class="form-control" id="end_date" name="end_date">
                        <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>


                <table id="purchase_orders_table" class="table table-striped" style="width:100%"></table>
            </div>
            <div class="grid-print-1">
                <div class="grid-command text-right">
                    <button class="btn btn-primary btn-sm btn-note" onclick="createNote()">CREATE CREDIT NOTE</button>
                    <button class="btn btn-primary btn-sm discount-btn-action" onclick="add_discount()">ADD DISCOUNT</button>
                    <button class="btn btn-primary btn-sm" onclick="add_project()">ADD PROJECT</button>
                    <button class="btn btn-primary btn-sm" onclick="add_cart()">ADD DETAILS</button>
                </div>
                @include('backend.partial.po')
            </div>
        </div>
        

        <div class="grid-footer">
            @if($user->can('submit_Draft'))
                <button class="btn btn-primary btn-main for-DRAFT" onclick="setStatus('FOR_CHECKING')">SUBMIT FOR CHECKING</button>
            @endif
            @if($user->can('submit_For Checking'))
            <button class="btn btn-primary btn-main for-FOR_CHECKING btn-hide" onclick="setStatus('FOR_APPROVAL')">SUBMIT FOR APPROVAL</button>
            @endif
            @if($user->can('approve_For Approval'))
            <button class="btn btn-success for-FOR_APPROVAL btn-hide" onclick="setStatus('APPROVED')">APPROVE</button>
            @endif
            @if($user->can('decline_For Approval'))
            <button class="btn btn-danger for-FOR_APPROVAL btn-hide" onclick="setStatus('FOR_CHECKING')">DECLINE</button>
            @endif
            @if($user->can('send_Approved'))
            <button class="btn btn-primary btn-main for-APPROVED btn-hide" onclick="setStatus('SENT_TO_SUPPLIER')">SEND TO SUPPLIER</button>
            @endif
            @if($user->can('partially delivered_Sent to Supplier'))
            <button class="btn btn-primary btn-main for-SENT_TO_SUPPLIER btn-hide" onclick="setStatus('PARTIALLY_DELIVERED')">PARTIALLY DELIVERED</button>
            @endif
            @if($user->can('not delivered_Sent to Supplier'))
            <button class="btn btn-warning for-SENT_TO_SUPPLIER btn-hide" onclick="setStatus('NOT_DELIVERED')">NOT DELIVERED</button>
            @endif
            @if($user->can('cancelled_Sent to Supplier'))
            <button class="btn btn-danger for-SENT_TO_SUPPLIER btn-hide" onclick="setStatus('CANCELLED')">CANCELLED</button>
            @endif
            @if($user->can('completed_Sent to Supplier'))
            <button class="btn btn-primary btn-success for-SENT_TO_SUPPLIER btn-hide" onclick="setStatus('COMPLETED')">COMPLETED</button>
            @endif
            @if($user->can('not delivered_Partially Delivered'))
            <button class="btn btn-warning for-PARTIALLY_DELIVERED btn-hide" onclick="setStatus('NOT_DELIVERED')">NOT DELIVERED</button>
            @endif
            @if($user->can('cancelled_Partially Delivered'))
            <button class="btn btn-danger for-PARTIALLY_DELIVERED btn-hide" onclick="setStatus('CANCELLED')">CANCELLED</button>
            @endif
            @if($user->can('completed_Partially Delivered'))
            <button class="btn btn-primary btn-success for-PARTIALLY_DELIVERED btn-hide" onclick="setStatus('COMPLETED')">COMPLETED</button>
            @endif
        </div>

    </div>

    
</div>
@endsection

@section('sc-modal')
@parent
<div class="sc-modal-content" id="preparation_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('preparation_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="preparationForm" class="form-record">
                <div class="row po-form-panels">
                    <div class="col-lg-8">
                        <div class="po-panel po-main-panel">
                            <div class="row">
                                <div class="form-group col-md-6 po_type_choice">
                                    <label>PO Type</label>
                                    <select id="po_typeseries" name="po_typeseries" class="form-control">
                                        <option value="">-- Select PO Type --</option>
                                        <option value="AUTOMATIC">New PO</option>
                                        <option value="MANUAL">Manual PO(Old PO)</option>
                                    </select>
                                </div>

                                <div class="col-md-6 ref_no">
                                    @include('backend.partial.component.lookup', [
                                        'label' => "Ref No",
                                        'placeholder' => 'Select MRF',
                                        'id' => "ref_no_text",
                                        'title' => "MRF REFERENCE",
                                        'url' => "/purchasing/material_requisition_forms/get",
                                        'data' => array(
                                            array('data' => "DT_RowIndex", 'title' => "#"),
                                            array('data' => "mrf_no", 'title' => "MRF No"),
                                            array('data' => "date", 'title' => "Date"),
                                            array('data' => "project_name", 'title' => "Project"),
                                        ),
                                        'disable' => true,
                                        'lookup_module' => '/purchasing/material_requisition_forms',
                                        'modal_type'=> '',
                                        'lookup_type' => 'main'
                                    ])
                                    <input type="hidden" id="ref_no" name="ref_no">
                                </div>

                                <div class="form-group col-md-6 order_no">
                                    <label for="order_no">Enter PO #</label>
                                    <input type="text" class="form-control" id="order_no" name="order_no" placeholder="Enter your PO number">
                                </div>

                                <div class="form-group col-md-6 manual_po" style="display: none;">
                                    <label for="manual_po">Enter PO (Old PO)</label>
                                    <input type="text" class="form-control" id="manual_po" name="manual_po" placeholder="Enter your manual PO number">
                                </div>

                                <div class="col-md-3 reviewed_by">
                                    @include('backend.partial.component.lookup', [
                                        'label' => "Supplier",
                                        'placeholder' => 'Select supplier',
                                        'id' => "supplier_name",
                                        'title' => "SUPPLIER",
                                        'url' => "/purchasing/supplier/get",
                                        'data' => array(
                                            array('data' => "DT_RowIndex", 'title' => "#"),
                                            array('data' => "supplier_name", 'title' => "Supplier"),
                                            array('data' => "email", 'title' => "Email"),
                                            array('data' => "contact_no", 'title' => "Contact"),
                                            array('data' => "address", 'title' => "Address"),
                                        ),
                                        'disable' => true,
                                        'lookup_module' => '/purchasing/supplier',
                                        'modal_type'=> '',
                                        'lookup_type' => 'main'
                                    ])
                                    <input type="hidden" name="supplier_id" id="supplier_id">
                                </div>

                                <div class="form-group col-md-3 supplier_contact_person">
                                    <label for="supplier_contact_person">Supplier Employee</label>
                                    <input type="text" class="form-control" id="supplier_contact_person" name="supplier_contact_person" readonly/>
                                </div>

                                <div class="form-group col-md-3 delivery_date">
                                    <label for="delivery_date">Delivery Date</label>
                                    <input type="date" class="form-control" id="delivery_date" name="delivery_date" value="{{date('Y-m-d')}}"/>
                                </div>

                                <div class="form-group col-md-3 po_date">
                                    <label for="po_date">PO Date</label>
                                    <input type="date" class="form-control" id="po_date" name="po_date" value="{{date('Y-m-d')}}"/>
                                </div>

                                <div class="form-group col-md-3 project">
                                    <label>PROJECT:</label>
                                    <select name="project" id="project" class="form-control">
                                        @foreach ($projects as $project)
                                            <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 employee_name">
                                    @include('backend.partial.component.lookup', [
                                        'label' => "Employee",
                                        'placeholder' => 'Select employee',
                                        'id' => "employee_name",
                                        'title' => "EMPLOYEE",
                                        'url' => "/payroll/employee-information/get",
                                        'data' => array(
                                            array('data' => "DT_RowIndex", 'title' => "#"),
                                            array('data' => "employee_no", 'title' => "Employee Number"),
                                            array('data' => "full_name", 'title' => "Name"),
                                            array('data' => "email", 'title' => "Email"),
                                        ),
                                        'disable' => true,
                                        'lookup_module' => '/payroll/employee-information',
                                        'modal_type'=> '',
                                        'lookup_type' => 'main'
                                    ])
                                    <input type="hidden" name="employee_id" id="employee_id">
                                </div>

                                <div class="form-group col-md-3 contact_no">
                                    <label for="contact_no">Contact No</label>
                                    <input type="text" class="form-control" id="contact_no" name="contact_no"/>
                                </div>

                                <div class="form-group col-md-3 supplier_email">
                                    <label for="supplier_email">Email</label>
                                    <input type="email" class="form-control" id="supplier_email" name="supplier_email"/>
                                </div>

                                <div class="form-group col-md-4 tax_type">
                                    <label>Tax</label>
                                    <select name="tax_type" id="tax_type" class="form-control">
                                        <option value="TAX EXCLUSIVE">TAX EXCLUSIVE</option>
                                        <option value="NON-VAT (3%)">NON-VAT (3%)</option>
                                        <option value="VAT (12%)">VAT (12%)</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-4 subtotal">
                                    <label for="subtotal">Subtotal</label>
                                    <input type="text" class="form-control" id="subtotal" name="subtotal" value="0" readonly/>
                                </div>

                                <div class="form-group col-md-4 total_with_tax">
                                    <label for="total_with_tax">Total with Tax</label>
                                    <input type="text" class="form-control" id="total_with_tax" name="total_with_tax" value="0" readonly/>
                                </div>

                                <div class="form-group col-md-12 delivery_instruction">
                                    <label for="delivery_instruction">Special Instruction</label>
                                    <textarea class="form-control" id="delivery_instruction" name="delivery_instruction" rows="3"></textarea>
                                </div>

                                <div class="form-group col-md-12">
                                    <label>Purchase Type</label>
                                    <select name="split_type" id="split_type" class="form-control">
                                        <option value="single">SINGLE PURCHASE ORDER</option>
                                        <option value="split">SPLIT PURCHASE ORDER</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-6 terms">
                                    <label for="terms">Terms</label>
                                    <div class="row">
                                        <div class="col-12">
                                            <input type="text" class="form-control" id="terms" name="terms" value=""/>
                                        </div>
                                    </div>
                                    <input type="hidden" name="term_type" id="term_type" value="MONTHS">
                                </div>

                                <div class="form-group col-md-6 due_date">
                                    <label for="due_date">Due Date</label>
                                    <input type="text" class="form-control" id="due_date" name="due_date"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="po-panel po-side-panel">
                            <div class="po-panel-title">Additional Fields</div>
                            <div id="po-extra-fields-panel">
                                <div class="form-group">
                                    <label for="payment_terms_template_select">Payment Terms Templates</label>
                                    <select id="payment_terms_template_select" class="form-control" size="9"></select>
                                    <input type="hidden" id="payment_terms_template_id" name="payment_terms_template_id">
                                </div>
                                <div class="form-group">
                                    <label for="payment_terms_quick_add">Add Template</label>
                                    <div class="input-group">
                                        <input type="text" id="payment_terms_quick_add" class="form-control" placeholder="e.g. 30 DAYS UPON RECEIVED OF DELIVERY">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-primary" id="add_payment_terms_template_btn" title="Add template">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <label for="due_date_template_select">Due Date Templates</label>
                                    <select id="due_date_template_select" class="form-control" size="6"></select>
                                    <input type="hidden" id="due_date_template_id" name="due_date_template_id">
                                </div>
                                <div class="form-group">
                                    <label for="due_date_quick_add">Add Due Date Template</label>
                                    <div class="input-group">
                                        <input type="text" id="due_date_quick_add" class="form-control" placeholder="e.g. UPON RECEIPT">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-primary" id="add_due_date_template_btn" title="Add due date template">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="sc-modal-footer text-right">
            {{-- <button class="btn btn-sm btn-primary btn-sv" onclick="$('#sv').click()">SAVE</button> --}}
            <button class="btn btn-sm btn-primary btn-sv" id="save_btn">SAVE</button>
            
        </div>
    </div>
</div>

<div class="sc-modal-content" id="preparation_detail_form">
    <div class="sc-modal-dialog sc-xl" style="min-width: 90%; margin: 20px auto;">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('preparation_detail_form').hide('all', modalHideFunction_detail())"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="preparation_detailForm" class="form-record">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered" id="detailsTable">
                        <thead class="bg-light">
                            <tr>
                                <th style="min-width: 250px;">ITEM</th>
                                <th style="min-width: 200px;">DESCRIPTION</th>
                                <th style="min-width: 100px;">QUANTITY</th>
                                <th style="min-width: 100px;">U/M</th>
                                <th style="min-width: 150px;">UNIT PRICE</th>
                                <th style="min-width: 100px;">TAX RATE</th>
                                <th style="min-width: 150px;">TOTAL AMOUNT</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="detailsTableBody">
                            <!-- Rows will be added dynamically -->
                        </tbody>
                    </table>
                    <div class="text-left mt-3 mb-3">
                        <button type="button" class="btn btn-sm btn-secondary" id="addRowBtn">
                            <i class="fas fa-plus"></i> Add Row
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="sc-modal-footer text-right">
            <button type="button" class="btn btn-sm btn-primary" id="saveDetailBtn" onclick="saveDetails(event)">
                <i class="fas fa-save"></i> SAVE
            </button>
        </div>
    </div>
</div>

<style>
#preparation_form .sc-modal-dialog {
    width: 96vw;
    max-width: 1500px;
    margin: 14px auto;
}

#preparation_form .sc-modal-body {
    max-height: calc(100vh - 210px);
    overflow-y: auto;
}

#preparation_form .po-form-panels > [class*="col-"] {
    margin-bottom: 12px;
}

#preparation_form .po-panel {
    background: #f8fafc;
    border: 1px solid #d8e0ea;
    border-radius: 8px;
    padding: 12px;
    height: 100%;
}

#preparation_form .po-panel-title {
    font-size: 14px;
    font-weight: 700;
    color: #1f2d3d;
    margin-bottom: 10px;
}

#po-extra-fields-panel {
    min-height: 420px;
}

#preparation_form .form-group,
#preparation_form .input-group {
    margin-bottom: 0.75rem;
}

#preparation_detail_form .select2-container {
    width: 100% !important;
}

#detailsTable {
    margin-bottom: 0;
}

#detailsTable th {
    font-weight: 600;
    text-align: center;
    vertical-align: middle;
    padding: 12px 8px;
}

#detailsTable td {
    padding: 8px;
    vertical-align: middle;
}

#detailsTable .form-control-sm {
    height: calc(1.5em + 0.5rem + 2px);
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
    border-radius: 0.2rem;
}

#detailsTable .select2-container--default .select2-selection--single {
    height: calc(1.5em + 0.5rem + 2px);
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
    border-radius: 0.2rem;
}

#detailsTable .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 1.5;
    padding-left: 0;
}

#detailsTable .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: calc(1.5em + 0.5rem);
}

.table-responsive {
    max-height: calc(100vh - 250px);
    overflow-y: auto;
}

#detailsTable td {
    position: relative;
}

#detailsTable .invalid-feedback {
    display: block;
    position: absolute;
    bottom: -18px;
    left: 8px;
    font-size: 11px;
    color: #dc3545;
    white-space: nowrap;
}

#detailsTable .is-invalid {
    border-color: #dc3545;
}

#detailsTable .select2-container--default .select2-selection--single.is-invalid {
    border-color: #dc3545;
}

@media (max-width: 992px) {
    .sc-modal-dialog {
        min-width: 95% !important;
        margin: 10px auto !important;
    }

    #preparation_form .sc-modal-dialog {
        width: 95vw;
        max-width: 95vw;
    }

    #po-extra-fields-panel {
        min-height: 120px;
    }
}
</style>

<div class="sc-modal-content" id="split_po_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('split_po_form').hide('all', modalHideFunction_detail())"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="split_poForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label>NO. OF SITE</label>
                        <input type="number" class="form-control form-control-sm" id="split_no" name="split_no" value="1" min="1" oninput="splitItem()"/>
                    </div>
                    <div class="form-group col-md-6">
                        <label>AMOUNT</label>
                        <input type="number" class="form-control form-control-sm" id="split_amount" name="split_amount" value="1" min="1"/>
                    </div>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-4">
                                <label>PROJECT</label>
                            </div>
                            <div class="col-md-4">
                                <label>ACCOUNT</label>
                            </div>
                            <div class="col-md-4">
                                <label>VALUE</label>
                            </div>
                        </div>
                        <div id="split_id"></div>
                    </div>
                </div>
            </form>
        </div>
        <div class="sc-modal-footer text-right">
            <button class="btn btn-sm btn-primary btn-sv" onclick="$('#sv').click()">SAVE</button>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="project_split_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('project_split_form').hide('all', modalHideFunction_detail())"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="project_splitForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label>NO. OF PROJECT INVOLVE</label>
                        <input type="number" class="form-control form-control-sm" id="project_split_no" name="project_split_no" value="1" min="1" oninput="splitProject()"/>
                    </div>
                    <div class="form-group col-md-6">
                        <label>AMOUNT</label>
                        <input type="number" class="form-control form-control-sm" id="split_project_amount" name="split_project_amount" value="0" disabled/>
                    </div>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6">
                                <label>PROJECT</label>
                            </div>
                            <div class="col-md-3">
                                <label>%</label>
                            </div>
                            <div class="col-md-3">
                                <label>VALUE</label>
                            </div>
                        </div>
                        <div id="project_container"></div>
                    </div>
                </div>
            </form>
        </div>
        <div class="sc-modal-footer text-right">
            <button class="btn btn-sm btn-primary btn-sv" onclick="$('#sv').click()">SAVE</button>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="discount_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('discount_form').hide('all', modalHideFunction_detail())"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="discountForm" class="form-record">
                <div class="row">
                    
                    <div class="form-group col-md-12">
                        <label>APPLY DISCOUNT TO:</label>
                        <select name="po_type" id="po_type" class="form-control form-control-sm" onchange="selectType()">
                            <option value="all">ALL</option>
                            <option value="item">PER ITEM</option>
                        </select>
                    </div>

                    <div class="col-md-12" id="all_discount">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>PO No.</label>
                                <input type="text" class="form-control form-control-sm" id="discount_po_number" name="discount_po_number" disabled/>
                            </div>
                            <div class="form-group col-md-6">
                                <label>NAME (OPTIONAL)</label>
                                <input type="text" class="form-control form-control-sm" id="discount_name" name="discount_name"/>
                            </div>
                            <div class="form-group col-md-12">
                                <label>DETAILS (OPTIONAL)</label>
                                <textarea name="discount_details" id="discount_details" class="form-control form-control-sm"></textarea>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-sm btn-block btn-light discount-percentage discount-btn disc-selected" onclick="selectDiscountType('all_discount', 'percentage')" data-value="percentage">PERCENTAGE</button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-sm btn-block btn-light discount-value discount-btn" onclick="selectDiscountType('all_discount', 'value')" data-value="value">VALUE</button>
                            </div>
                            <div class="form-group col-md-12">
                                <label>DISCOUNT</label>
                                <input type="number" class="form-control form-control-sm" id="discount_value" name="discount_value"/>
                                <input type="hidden" class="form-control form-control-sm" id="discount_type" name="discount_type" value="percentage"/>
                            </div>
                        </div>
                    </div>

                    <dic class="col-md-12 hide-discount" id="item_discount">
                        {{-- Generated by selecting the option --}}
                    </dic>
                </div>
            </form>
        </div>
        <div class="sc-modal-footer text-right">
            <button class="btn btn-sm btn-primary btn-sv" onclick="$('#sv').click()">SAVE</button>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="credit_note_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('discount_form').hide('all', modalHideFunction_detail())"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="credit_noteForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label>PO NUMBER</label>
                        <input type="text" class="form-control form-control-sm" id="credit_po" name="credit_po" disabled>
                    </div>
                    <div class="form-group col-md-6">
                        <label>TOTAL AMOUNT</label>
                        <input type="number" class="form-control form-control-sm" id="credit_total_amount" name="credit_total_amount" value="0">
                    </div>
                    <div class="form-group col-md-12">
                        <label>SUPPLIER</label>
                        <input type="text" class="form-control form-control-sm" id="credit_supplier" name="credit_supplier" disabled>
                    </div>
                    <div class="form-group col-md-12">
                        <label>PROJECTS</label>
                        <div class="credit-project-list"></div>
                    </div>

                    <div class="form-group col-md-12">
                        <label>PARTICULARS</label>
                        <textarea name="credit_particulars" id="credit_particulars" class="form-control form-control-sm"></textarea>
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


@section('scripts')
<script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script>
    window.materials = @json($materials);
    window.isStockClerk = @json($user->hasRole('STOCK CLERK'));
</script>
<script src="/js/backend/pages/purchasing/transaction/purchase_order.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#preparation_detail_form #item').select2({
        dropdownParent: $('#preparation_detail_form'),
        width: '100%',
        placeholder: 'Select an item',
        allowClear: true
    });
});
</script>
@endsection



@section('styles-2')
    <link href="{{asset('/css/custom/po.css')}}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        input.date-filter {
            height: calc(1.90625rem + 2px);
            padding: .25rem .7rem;
            font-size: .9375rem;
            font-weight: 400;
        }
        .form-group.df-container {
            margin-top: 1rem;
        }
        button.filter-button.button {
            padding: 5px;
            border: 2px solid #084196;
            background: #084196;
            color: #fff;
            text-transform: uppercase;
            font-size: 12px;
            transition: .3s;
            margin-left: 3px;
            border-radius: 3px;
        }
    </style>
@endsection
