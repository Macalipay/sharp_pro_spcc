@extends('backend.master.index')

@section('title', 'ADD SUPPLIER')

@section('breadcrumbs')
    <span>ACCOUNTING</span> / <span>BILLS / EXPENSES</span> / <span class="highlight">ADD SUPPLIER</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">NEW SUPPLIER</h5>
                <a href="/accounting/bills?tab={{ $returnTab }}&mode={{ $returnMode }}@if(!empty($returnBillId))&bill_id={{ $returnBillId }}@endif" class="btn btn-sm btn-light">Back</a>
            </div>
            <div class="card-body">
                <form method="POST" action="/accounting/bills/suppliers/save" class="not">
                    @csrf
                    <input type="hidden" name="return_tab" value="{{ $returnTab }}">
                    <input type="hidden" name="return_mode" value="{{ $returnMode }}">
                    <input type="hidden" name="return_bill_id" value="{{ $returnBillId ?? 0 }}">

                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label>Supplier Name</label>
                            <input type="text" name="supplier_name" class="form-control" value="{{ old('supplier_name') }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Contact No</label>
                            <input type="text" name="contact_no" class="form-control" value="{{ old('contact_no') }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person') }}" required>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Address</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address') }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>TIN No</label>
                            <input type="text" name="tin_no" class="form-control" value="{{ old('tin_no') }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Payment Terms</label>
                            <input type="text" name="payment_terms" class="form-control" value="{{ old('payment_terms') }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Category</label>
                            <select name="vatable" class="form-control" required>
                                <option value="">Select One</option>
                                <option value="1" {{ old('vatable') === '1' ? 'selected' : '' }}>VAT</option>
                                <option value="2" {{ old('vatable') === '2' ? 'selected' : '' }}>NON-VAT</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Bank Account</label>
                            <input type="text" name="bank_account" class="form-control" value="{{ old('bank_account') }}">
                        </div>
                    </div>

                    <div class="text-right">
                        <a href="/accounting/bills?tab={{ $returnTab }}&mode={{ $returnMode }}@if(!empty($returnBillId))&bill_id={{ $returnBillId }}@endif" class="btn btn-sm btn-light mr-2">Cancel</a>
                        <button type="submit" class="btn btn-sm btn-primary">Save Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
