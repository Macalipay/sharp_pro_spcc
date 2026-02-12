@extends('backend.master.index')

@section('title', 'SUPPLIER')

@section('breadcrumbs')
    <span>MAINTENANCE</span> / <span class="highlight">SUPPLIER</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <table id="supplier_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="supplier_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('benefits_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="classForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12 supplier_name">
                        <label>Supplier Name</label>
                        <input type="text" class="form-control" id="supplier_name" name="supplier_name"/>
                    </div>

                    <div class="form-group col-md-12 contact_no">
                        <label>Contact No</label>
                        <input type="number" class="form-control" id="contact_no" name="contact_no"/>
                    </div>

                    <div class="form-group col-md-12 contact_person">
                        <label>Contact Person</label>
                        <input type="text" class="form-control" id="contact_person" name="contact_person"/>
                    </div>

                    <div class="form-group col-md-12 address">
                        <label>Address</label>
                        <input type="text" class="form-control" id="address" name="address"/>
                    </div>

                    <div class="form-group col-md-12 tin_no">
                        <label>TIN No</label>
                        <input type="number" class="form-control" id="tin_no" name="tin_no"/>
                    </div>

                    <div class="form-group col-md-12 payment_terms">
                        <label>Payment Terms</label>
                        <input type="text" class="form-control" id="payment_terms" name="payment_terms"/>
                    </div>
                    
                    <div class="form-group col-md-12 vatable">
                        <label>Category</label>
                        <select name="vatable" id="vatable" class="form-control">
                            <option selected>SELECT ONE</option>
                            <option value="1">VAT</option>
                            <option value="2">NON-VAT</option>
                        </select>
                    </div>

                    <div class="form-group col-md-12 bank_name">
                        <label>Bank Name</label>
                        <input type="text" class="form-control" id="bank_name" name="bank_name"/>
                    </div>

                    <div class="form-group col-md-12 bank_account">
                        <label>Bank Account</label>
                        <input type="text" class="form-control" id="bank_account" name="bank_account"/>
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
    <script src="/js/backend/pages/purchasing/maintenance/supplier.js"></script>
@endsection

@section('styles')


@endsection