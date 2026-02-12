@extends('backend.master.index')

@section('title', 'INVENTORY REQUEST')

@section('breadcrumbs')
    <span>TRANSACTION</span>  /  <span class="highlight">INVENTORY REQUEST</span>
@endsection

@section('content')
<div class="row" style="height:100%;">
    <div class="col-12" style="height:100%;">
        <div class="grid-container">
            <div class="grid-table-1">
                <div class="status-container mt-2">
                    <button class="btn btn-sm btn-light status-draft" style="font-size: 10px;padding: 3px 5px !important;">(<span class="count-table">0</span>) DRAFT </button>
                    <button class="btn btn-sm btn-light status-draft" style="font-size: 10px;padding: 3px 5px !important;">(<span class="count-table">0</span>) FOR CHECKING </button>
                    <button class="btn btn-sm btn-light status-draft" style="font-size: 10px;padding: 3px 5px !important;">(<span class="count-table">0</span>) FOR APPROVAL </button>
                    <button class="btn btn-sm btn-light status-draft" style="font-size: 10px;padding: 3px 5px !important;">(<span class="count-table">0</span>) APPROVED </button>
                    <button class="btn btn-sm btn-light status-draft" style="font-size: 10px;padding: 3px 5px !important;">(<span class="count-table">0</span>) COMPLETED </button>
                    <button class="btn btn-sm btn-light status-draft" style="font-size: 10px;padding: 3px 5px !important;">(<span class="count-table">0</span>) NOT DELIVERED </button>
                    <button class="btn btn-sm btn-light status-draft" style="font-size: 10px;padding: 3px 5px !important;">(<span class="count-table">0</span>) CANCELLED </button>
                </div>
                <table id="purchase_orders_table" class="table table-striped" style="width:100%"></table>
            </div>
            <div class="grid-print-1">
                <div class="grid-command text-right">
                    {{-- <button class="btn btn-primary btn-sm btn-note" onclick="createNote()">CREATE CREDIT NOTE</button> --}}
                    {{-- <button class="btn btn-primary btn-sm discount-btn-action" onclick="add_discount()">ADD DISCOUNT</button> --}}
                    <button class="btn btn-primary btn-sm" onclick="add_project()">ADD PROJECT</button>
                    <button class="btn btn-primary btn-sm" onclick="add_cart()">ADD DETAILS</button>
                </div>
                @include('backend.partial.inventory_request')
            </div>
        </div>

        <div class="grid-footer">
            <button class="btn btn-primary btn-main for-DRAFT" onclick="setStatus('FOR_CHECKING')">SUBMIT FOR CHECKING</button>
            <button class="btn btn-primary btn-main for-FOR_CHECKING btn-hide" onclick="setStatus('FOR_APPROVAL')">SUBMIT FOR APPROVAL</button>
            <button class="btn btn-success for-FOR_APPROVAL btn-hide" onclick="setStatus('APPROVED')">APPROVE</button>
            <button class="btn btn-danger for-FOR_APPROVAL btn-hide" onclick="setStatus('FOR_CHECKING')">DECLINE</button>
            <button class="btn btn-primary btn-main for-APPROVED btn-hide" onclick="setStatus('SENT_TO_SUPPLIER')">SEND TO SUPPLIER</button>
            <button class="btn btn-primary btn-main for-SENT_TO_SUPPLIER btn-hide" onclick="setStatus('PARTIALLY_DELIVERED')">PARTIALLY DELIVERED</button>
            <button class="btn btn-warning for-SENT_TO_SUPPLIER btn-hide" onclick="setStatus('NOT_DELIVERED')">NOT DELIVERED</button>
            <button class="btn btn-danger for-SENT_TO_SUPPLIER btn-hide" onclick="setStatus('CANCELLED')">CANCELLED</button>
            <button class="btn btn-primary btn-success for-SENT_TO_SUPPLIER btn-hide" onclick="setStatus('COMPLETED')">COMPLETED</button>
            <button class="btn btn-warning for-PARTIALLY_DELIVERED btn-hide" onclick="setStatus('NOT_DELIVERED')">NOT DELIVERED</button>
            <button class="btn btn-danger for-PARTIALLY_DELIVERED btn-hide" onclick="setStatus('CANCELLED')">CANCELLED</button>
            <button class="btn btn-primary btn-success for-PARTIALLY_DELIVERED btn-hide" onclick="setStatus('COMPLETED')">COMPLETED</button>
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
                    <div class="form-group col-md-6 reviewed_by">
                        <label>Site</label>
                        <select name="site_id" id="site_id" class="form-control">
                            @foreach ($sites as $site)
                                <option value="{{ $site->id }}">{{ $site->project_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-6 project">
                        <label>PROJECT:</label>
                        <select name="project" id="project" class="form-control">
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-12 tax_type">
                        <label>Inventory Type</label>
                        <select name="tax_type" id="tax_type" class="form-control">
                            <option value="TAX EXCLUSIVE">Main Inventory</option>
                            <option value="NON-VAT (3%)">OSM</option>
                        </select>
                    </div>

                    <div class="form-group col-md-6 delivery_date">
                        <label for="delivery_date">Delivery Date</label>
                        <input type="date" class="form-control" id="delivery_date" name="delivery_date" value="{{date('Y-m-d')}}"/>
                    </div>


                    <div class="form-group col-md-6 po_date">
                        <label for="po_date">RO Date</label>
                        <input type="date" class="form-control" id="po_date" name="po_date" value="{{date('Y-m-d')}}"/>
                    </div>

                    <div class="form-group col-md-12 contact_no">
                        <label for="contact_no">Contact No</label>
                        <input type="number" class="form-control" id="contact_no" name="contact_no"/>
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

                    <div class="form-group col-md-12 delivery_instruction">
                        <label for="delivery_instruction">Requestor Name</label>
                        <input type="text" class="form-control" id="delivery_instruction" name="delivery_instruction"/>
                    </div>

                    <div class="form-group col-md-12 tax_type">
                        <label>Priority Level</label>
                        <select name="tax_type" id="tax_type" class="form-control">
                            <option value="TAX EXCLUSIVE">URGENT</option>
                            <option value="NON-VAT (3%)">HIGH</option>
                            <option value="VAT (12%)">MEDIUM</option>
                            <option value="VAT (12%)">LOW</option>
                            <option value="VAT (12%)">ROUTINE</option>
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

<div class="sc-modal-content" id="preparation_detail_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('preparation_detail_form').hide('all', modalHideFunction_detail())"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="preparation_detailForm" class="form-record">
                <div class="row">

                    <div class="form-group col-md-12 item">
                        <label for="item">ITEM</label>
                        {{-- <input type="text" class="form-control" id="item" name="item"/> --}}
                        <select name="item" id="item" class="form-control">
                            <option value=""></option>
                            @foreach ($materials as $item)
                            <option value="{{$item->id}}">{{$item->item_name}}</option>
                            @endforeach
                        </select>
                        <input type="hidden" class="form-control" id="purchase_order_id" name="purchase_order_id"/>
                    </div>


                    <div class="form-group col-md-12 description">
                        <label for="description">DESCRIPTION</label>
                        <input type="text" class="form-control" id="description" name="description"/>
                    </div>

                    <div class="form-group col-md-6 quantity">
                        <label for="quantity">QUANTITY</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" value="1" oninput="updatePrice()"/>
                    </div>

                    <div class="form-group col-md-6 unit_price">
                        <label for="unit_price">UNIT PRICE</label>
                        <input type="number" class="form-control" id="unit_price" name="unit_price" value="1" oninput="updatePrice()"/>
                    </div>

                    {{-- <div class="form-group col-md-6 discount">
                        <label for="discount">DISCOUNT</label>
                        <input type="number" class="form-control" id="discount" name="discount" value="0"/>
                    </div> --}}

                    <div class="form-group col-md-6 tax_rate">
                        <label for="tax_rate">TAX RATE</label>
                        <input type="number" class="form-control" id="tax_rate" name="tax_rate" value="0"/>
                    </div>

                    <div class="form-group col-md-6 total_amount">
                        <label for="total_amount">TOTAL AMOUNT</label>
                        <input type="text" class="form-control" id="total_amount" name="total_amount"  value="1" disabled/>
                    </div>

                </div>
            </form>
        </div>
        <div class="sc-modal-footer text-right">
            <button class="btn btn-sm btn-primary btn-sv" onclick="$('#sv').click()">SAVE</button>
        </div>
    </div>
</div>

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
<script src="/js/backend/pages/inventory/transaction/inventory_request.js"></script>
@endsection



@section('styles-2')
    <link href="{{asset('/css/custom/po.css')}}" rel="stylesheet">
@endsection