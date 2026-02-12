
<div id="preparation_tab" class="form-tab">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                @include('backend.partial.flash-message')
                <div class="col-12">
                    <div class="card-body">
                        <table id="purchase_orders_table" class="table table-striped" style="width:100%"></table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="preparation_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('preparation_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="classForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-6 reviewed_by">
                        <label>Supplier</label>
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

<div class="sc-modal-content" id="preparation_detail_form">
    <div class="sc-modal-dialog sc-xl">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('preparation_detail_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" class="form-record">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="col-12">
                                    <table id="purchase_order_detail_table" class="table table-striped" style="width:100%"></table>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="details_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('details_form').hide('', modalHideFunction_detail())"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" class="form-record">
                <div class="row">

                    <div class="form-group col-md-12 item">
                        <label for="item">ITEM</label>
                        <input type="text" class="form-control" id="item" name="item"/>
                        <input type="hidden" class="form-control" id="purchase_order_id" name="purchase_order_id"/>
                    </div>


                    <div class="form-group col-md-12 description">
                        <label for="description">DESCRIPTION</label>
                        <input type="text" class="form-control" id="description" name="description"/>
                    </div>

                    <div class="form-group col-md-6 quantity">
                        <label for="quantity">QUANTITY</label>
                        <input type="number" class="form-control" id="quantity" name="quantity"/>
                    </div>

                    <div class="form-group col-md-6 unit_price">
                        <label for="unit_price">UNIT PRICE</label>
                        <input type="number" class="form-control" id="unit_price" name="unit_price"/>
                    </div>

                    <div class="form-group col-md-6 discount">
                        <label for="discount">DISCOUNT</label>
                        <input type="number" class="form-control" id="discount" name="discount"/>
                    </div>

                    <div class="form-group col-md-6 tax_rate">
                        <label for="tax_rate">TAX RATE</label>
                        <input type="text" class="form-control" id="tax_rate" name="tax_rate"/>
                    </div>

                    <div class="form-group col-md-12 total_amount">
                        <label for="total_amount">TOTAL AMOUNT</label>
                        <input type="text" class="form-control" id="total_amount" name="total_amount"/>
                    </div>

                </div>
            </form>
        </div>
        <div class="sc-modal-footer text-right">
            <button class="btn btn-sm btn-primary btn-sv" onclick="$('#sv').click()">SAVE</button>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="poPrint">
    <div class="sc-modal-dialog sc-xl">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('poPrint').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" class="form-record">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="col-12">
                                @include('backend.partial.po')
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="sc-modal-footer">
            <div class="footer" style="text-align: right;">
                <button  onclick="printDiv()" class="btn btn-primary">Print Purchase Order Form</button>
            </div>
        </div>
    </div>
</div>
@endsection
