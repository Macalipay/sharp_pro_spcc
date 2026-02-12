@extends('backend.master.index')

@section('title', 'CREDIT NOTE')

@section('breadcrumbs')
    <span>TRANSACTION</span> / <span class="highlight">CREDIT NOTE</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <table id="credit_note_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>


@section('sc-modal')
@parent
<div class="sc-modal-content" id="credit_note_form">
    <div class="sc-modal-dialog modal-lg">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('credit_note_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="credit_noteForm" class="form-record">
                <div class="row">

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

<div class="sc-modal-content" id="view_note_form">
    <div class="sc-modal-dialog sc-xl">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('view_note_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-md-12 p-3">
                    <table style="width:100%;">
                        <thead>
                            <th>
                                <h3 style="font-family:system-ui; font-weight:bold;">CREDITE NOTE: CRN<span id="cn_no">0</span></h3>
                            </th>
                            <th class="text-right">
                                <h3 style="font-family:system-ui; font-weight:bold;text-transform:uppercase;"><span id="cn_status">DRAFT</span></h3>
                            </th>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <span id="cn_supplier">-</span>
                                </td>
                                <td>
                                    Project: <span id="cn_proj">-</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <br>
                    <table style="width:100%;">
                        <thead>
                            <th>Account</th>
                            <th>Particulars</th>
                            <th>Amount</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span id="cn_account">-</span></td>
                                <td><span id="cn_particulars">-</span></td>
                                <td><span id="cn_amount">-</span></td>
                            </tr>
                        </tbody>
                    </table>
                    <hr>
                    <div class="row"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@endsection

@section('scripts')
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="/js/backend/pages/purchasing/transaction/credit_note.js"></script>
@endsection
