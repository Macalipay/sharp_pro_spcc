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
                <div class="row">
                    <div class="form-group col-md-12 po_type_choice">
                        <label>PO Type</label>
                        <select id="po_typeseries" name="po_typeseries" class="form-control">
                            <option value="">-- Select PO Type --</option>
                            <option value="AUTOMATIC">New PO</option>
                            <option value="MANUAL">Manual PO(Old PO)</option>
                        </select>
                    </div>

                    <div class="form-group col-md-12 order_no">
                        <label for="order_no">Enter PO #</label>
                        <input type="text" class="form-control" id="order_no" name="order_no" placeholder="Enter your PO number">
                    </div>

                    <div class="form-group col-md-12 manual_po" style="display: none;">
                        <label for="manual_po">Enter PO (Old PO)</label>
                        <input type="text" class="form-control" id="manual_po" name="manual_po" placeholder="Enter your manual PO number">
                    </div>

                    <div class="form-group col-md-6 reviewed_by">
                        <label>Supplier</label>
                        <select name="supplier_id" id="supplier_id" class="form-control">
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- <div class="form-group col-md-6 reviewed_by">
                        <label>Site</label>
                        <select name="site_id" id="site_id" class="form-control">
                            @foreach ($sites as $site)
                                <option value="{{ $site->id }}">{{ $site->project_name }}</option>
                            @endforeach
                        </select>
                    </div> --}}

                    <div class="form-group col-md-6 project">
                        <label>PROJECT:</label>
                        <select name="project" id="project" class="form-control">
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-6 delivery_date">
                        <label for="delivery_date">Delivery Date</label>
                        <input type="date" class="form-control" id="delivery_date" name="delivery_date" value="{{date('Y-m-d')}}"/>
                    </div>


                    <div class="form-group col-md-6 po_date">
                        <label for="po_date">PO Date</label>
                        <input type="date" class="form-control" id="po_date" name="po_date" value="{{date('Y-m-d')}}"/>
                    </div>
                    
                    <div class="form-group col-md-12 project">
                        <label>Employee:</label>
                        <select name="employee_id" id="employee_id" class="form-control">
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->fistname . ' ' . $employee->lastname  }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-12 contact_no">
                        <label for="contact_no">Contact No</label>
                        <input type="text" class="form-control" id="contact_no" name="contact_no"/>
                    </div>

                    <div class="form-group col-md-6 terms">
                        <label for="terms">Terms</label>
                        <div class="row">
                            <div class="col-6">
                                <input type="number" class="form-control" id="terms" name="terms" value="1" oninput="changeTerms()"/>
                            </div>
                            <div class="col-6">
                                <select name="term_type" id="term_type" class="form-control" onchange="changeTerms()">
                                    <option value="DAYS">DAYS</option>
                                    <option value="MONTHS">MONTHS</option>
                                    <option value="YEARS">YEARS</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group col-md-6 due_date">
                        <label for="due_date">Due Date</label>
                        <input type="date" class="form-control" id="due_date" name="due_date"/>
                    </div>

                    <div class="form-group col-md-12 tax_type">
                        <label>Site</label>
                        <select name="tax_type" id="tax_type" class="form-control">
                            <option value="TAX EXCLUSIVE">TAX EXCLUSIVE</option>
                            <option value="NON-VAT (3%)">NON-VAT (3%)</option>
                            <option value="VAT (12%)">VAT (12%)</option>
                        </select>
                    </div>

                    <div class="form-group col-md-6 subtotal">
                        <label for="subtotal">Subtotal</label>
                        <input type="text" class="form-control" id="subtotal" name="subtotal" value="0" readonly/>
                    </div>

                    <div class="form-group col-md-6 total_with_tax">
                        <label for="total_with_tax">Total with Tax</label>
                        <input type="text" class="form-control" id="total_with_tax" name="total_with_tax" value="0" readonly/>
                    </div>

                    <div class="form-group col-md-12 delivery_instruction">
                        <label for="delivery_instruction">Delivery Instruction</label>
                        <input type="text" class="form-control" id="delivery_instruction" name="delivery_instruction"/>
                    </div>

                    <div class="form-group col-md-12">
                        <label>Site</label>
                        <select name="split_type" id="split_type" class="form-control">
                            <option value="single">SINGLE PURCHASE ORDER</option>
                            <option value="split">SPLIT PURCHASE ORDER</option>
                        </select>
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