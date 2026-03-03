@extends('backend.master.index')

@section('title', 'PAYMENT TERMS')

@section('breadcrumbs')
    <span>MAINTENANCE</span> / <span class="highlight">PAYMENT TERMS</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <table id="payment_terms_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="payment_terms_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('payment_terms_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="payment_termsForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12 term_text">
                        <label>Payment Term Template</label>
                        <input type="text" class="form-control" id="term_text" name="term_text" placeholder="e.g. 30 DAYS UPON RECEIVED OF DELIVERY"/>
                    </div>
                    <div class="form-group col-md-12 description">
                        <label>Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
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
    <script src="/js/backend/pages/purchasing/maintenance/payment_terms.js"></script>
@endsection

