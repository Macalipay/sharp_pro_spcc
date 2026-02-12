@extends('backend.master.index')

@section('title', 'PURCHASE ORDER')

@section('breadcrumbs')
    <span>TRANSACTION</span> / <span class="highlight">PURCHASE ORDER</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">PURCHASE ORDER: TRANSACTION SCREEN</h5>
            </div>
            @include('backend.partial.flash-message')
            <div class="col-12">
                <div class="card-body">
                    <table id="purchase_orders_table" class="table table-striped" style="width:100%">
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


@section('sc-modal')
@parent
<div class="sc-modal-content" id="purchase_orders_form">
    <div class="sc-modal-dialog modal-lg">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('purchase_orders_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="classForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-6 reviewed_by">
                        <label>Supplier/s</label>
                        <select name="supplier_id" id="supplier_id" class="form-control">
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-6 reviewed_by">
                        <label>Site</label>
                        <select name="site_id" id="site_id" class="form-control">
                            @foreach ($sites as $site)
                                <option value="{{ $site->id }}">{{ $site->project_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-6 delivery_date">
                        <label for="delivery_date">Delivery Date</label>
                        <input type="date" class="form-control" id="delivery_date" name="delivery_date"/>
                    </div>


                    <div class="form-group col-md-6 po_date">
                        <label for="po_date">PO Date</label>
                        <input type="date" class="form-control" id="po_date" name="po_date"/>
                    </div>

                    <div class="form-group col-md-12 contact_no">
                        <label for="contact_no">Contact No</label>
                        <input type="number" class="form-control" id="contact_no" name="contact_no"/>
                    </div>

                    <div class="form-group col-md-6 reference">
                        <label for="reference">Reference</label>
                        <input type="text" class="form-control" id="reference" name="reference"/>
                    </div>

                    <div class="form-group col-md-6 terms">
                        <label for="terms">Terms</label>
                        <input type="text" class="form-control" id="terms" name="terms"/>
                    </div>

                    <div class="form-group col-md-12 due_date">
                        <label for="due_date">Due Date</label>
                        <input type="date" class="form-control" id="due_date" name="due_date"/>
                    </div>

                    <div class="form-group col-md-6 order_no">
                        <label for="order_no">Order No</label>
                        <input type="text" class="form-control" id="order_no" name="order_no"/>
                    </div>

                    <div class="form-group col-md-6 tax_type">
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
    <script src="/js/backend/pages/purchasing/transaction/purchase_order.js"></script>
@endsection
