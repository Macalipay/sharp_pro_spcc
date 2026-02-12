@extends('backend.master.index')

@section('title', 'HISTORY')

@section('breadcrumbs')
    <span>TRANSACTION</span> / <span class="highlight">HISTORY</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <table id="history_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="classes_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('benefits_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="classForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12 description">
                        <label>DESCRIPTION</label>
                        <input type="text" class="form-control" id="description" name="description" placeholder="DESCRIPTION"/>
                    </div>
                    <div class="form-group col-md-12 description">
                        <label>PAYMENT SCHEDULE</label>
                        <select name="payment_schedule" id="payment_schedule" class="form-control">
                            <option value="default">COMPANY DEFAULT</option>
                            <option value="weekly">WEEKLY</option>
                            <option value="bimonthly">BI-MONTHLY</option>
                            <option value="monthly">MONTHLY</option>
                            <option value="custom">CUSTOM</option>
                        </select>
                    </div>
                    <div class="form-group col-md-12">
                        <label class="form-check">
                            <input class="form-check-input" id="tax_applicable" name="tax_applicable" value="0" type="checkbox" onchange="$('#'+this.id).val($('#'+this.id).prop('checked') === true?'1':'0');">
                            <span class="form-check-label">
                            TAX APPLICABLE
                            </span>
                        </label>
                    </div>
                    <div class="form-group col-md-12">
                        <label class="form-check">
                            <input class="form-check-input" id="government_mandated_benefits" name="government_mandated_benefits" value="0" type="checkbox" onchange="$('#'+this.id).val($('#'+this.id).prop('checked') === true?'1':'0');">
                            <span class="form-check-label">
                            GOVERNMENT MANDATED BENEFITS
                            </span>
                        </label>
                    </div>
                    <div class="form-group col-md-12">
                        <label class="form-check">
                            <input class="form-check-input" id="other_company_benefits" name="other_company_benefits" value="0" type="checkbox" onchange="$('#'+this.id).val($('#'+this.id).prop('checked') === true?'1':'0');">
                            <span class="form-check-label">
                            OTHER COMPANY BENEFITS
                            </span>
                        </label>
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
    <script src="/js/backend/pages/inventory/transaction/history.js"></script>
@endsection
