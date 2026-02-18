@extends('backend.master.index')

@section('title', 'CHART OF ACCOUNTS')

@section('breadcrumbs')
    <span>MAINTENANCE</span> / <span class="highlight">CHART OF ACCOUNTS</span>
@endsection

@section('content')
<style>
    .report-impact-statement {
        font-size: 11px;
        color: #1f2937;
        line-height: 1.35;
        overflow-x: auto;
        overflow-y: hidden;
        padding-bottom: 2px;
    }
    .report-impact-statement .ri-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .report-impact-statement .ri-col {
        min-width: 0;
    }
    .report-impact-statement .ri-box {
        border: 1px solid #bfdbfe;
        background-color: #eaf4ff;
        border-radius: 6px;
        padding: 6px 8px;
    }
    .report-impact-statement .ri-title {
        font-weight: 800 !important;
        text-transform: uppercase;
        margin-bottom: 4px;
        color: #000 !important;
        font-size: 10px;
        letter-spacing: 0.4px;
        border-bottom: 1px solid #000;
        padding-bottom: 2px;
        white-space: nowrap;
    }
    .report-impact-statement .ri-section {
        font-weight: 700;
        margin-top: 4px;
        font-size: 9px;
        white-space: nowrap;
    }
    .report-impact-statement .ri-line {
        padding-left: 8px;
        font-size: 9px;
        white-space: nowrap;
    }
    .report-impact-statement .ri-total {
        font-weight: 700;
        margin-top: 4px;
        font-size: 9px;
        white-space: nowrap;
    }
    .coa-wide-wrap {
        width: calc(100% + 48px);
        margin-left: -24px;
        margin-right: -24px;
    }
    @media (max-width: 991px) {
        .report-impact-statement .ri-grid {
            grid-template-columns: 1fr;
        }
        .coa-wide-wrap {
            width: 100%;
            margin-left: 0;
            margin-right: 0;
        }
    }
</style>
<div class="row">
    <div class="col-md-12 coa-wide-wrap">
        <div class="d-flex justify-content-end mb-2">
            <button type="button" class="btn btn-sm btn-primary mr-2" onclick="openAddAccountModal()">Add Account</button>
            <button type="button" class="btn btn-sm btn-info mr-2" onclick="goToBankAccountSetup()">Add Bank Account</button>
            <button type="button" class="btn btn-sm btn-dark" onclick="printChartOfAccountsPdf()">Print PDF</button>
        </div>
        <ul class="nav nav-tabs mb-3" id="coaCategoryTabs">
            <li class="nav-item">
                <a href="#" class="nav-link coa-category-tab" data-category="ALL">All</a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link active coa-category-tab" data-category="ASSETS">Assets</a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link coa-category-tab" data-category="LIABILITY">Liability</a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link coa-category-tab" data-category="EQUITY">Equity</a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link coa-category-tab" data-category="EXPENSES">Expenses</a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link coa-category-tab" data-category="REVENUE">Revenue</a>
            </li>
        </ul>
        <table id="account_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="chart_of_accounts_form">
    <div class="sc-modal-dialog sc-xl" style="max-width: 980px; width: 92vw;">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('chart_of_accounts_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="classForm" class="form-record">
                <div class="row">
                    <div class="col-md-7">
                        <div class="form-group col-md-12 account_type">
                            <label>ACCOUNT TYPE</label>
                            <select name="account_type" id="account_type" class="form-control">
                                @foreach ($account_types_by_category as $category => $types)
                                    <optgroup label="{{ strtoupper($category) }}">
                                        @foreach ($types as $account_type)
                                            <option value="{{ $account_type->id }}">{{ $account_type->account_type }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-12 account_number">
                            <label>ACCOUNT CODE</label>
                            <input type="text" class="form-control" id="account_number" name="account_number" inputmode="numeric" autocomplete="off" maxlength="10" placeholder="Number only" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)"/>
                        </div>

                        <div class="form-group col-md-12 account_name">
                            <label>NAME</label>
                            <input type="text" class="form-control" id="account_name" name="account_name" maxlength="150" placeholder="A short title for this account (limited to 150 characters)"/>
                        </div>

                        <div class="form-group col-md-12 description">
                            <label>DESCRIPTION</label>
                            <input type="text" class="form-control" id="description" name="description" placeholder="(Optional) A description of how this account should be used"/>
                        </div>

                        <div class="form-group col-md-12 tax">
                            <label>TAX</label>
                            <select name="tax" id="tax" class="form-control">
                                <option value="">Select Tax</option>
                                <option value="VAT">VAT</option>
                                <option value="NON-VAT">NON-VAT</option>
                            </select>
                        </div>

                        <div class="form-group col-md-12 allow_for_payments">
                            <div class="form-check mt-2">
                                <input type="checkbox" class="form-check-input" id="allow_for_payments" name="allow_for_payments" value="1">
                                <label class="form-check-label" for="allow_for_payments">Allow this account to be used for payments</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="card h-100">
                            <div class="card-header bg-light">
                                <strong>Report Impact Review</strong>
                            </div>
                            <div class="card-body">
                                <div id="report_impact_list" class="report-impact-statement"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="sc-modal-footer text-right">
            <button type="button" class="btn btn-sm btn-primary btn-sv" onclick="saveAccountRecord()">SAVE</button>
        </div>
    </div>
</div>
@endsection
@endsection

@section('scripts')
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="/js/backend/pages/accounting/maintenance/chart_of_account.js"></script>
@endsection
