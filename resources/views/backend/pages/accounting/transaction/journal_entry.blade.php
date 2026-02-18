@extends('backend.master.index')

@section('title', 'MANUAL JOURNAL ENTRY')

@section('breadcrumbs')
    <span>TRANSACTION</span> / <span class="highlight">MANUAL JOURNAL ENTRY</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs mb-3" id="journalStatusTabs">
            <li class="nav-item">
                <a href="#" class="nav-link active journal-status-tab" data-status="ALL">ALL</a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link journal-status-tab" data-status="DRAFT">DRAFT</a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link journal-status-tab" data-status="POSTED">POSTED</a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link journal-status-tab" data-status="VOIDED">VOIDED</a>
            </li>
        </ul>
        <div class="table-responsive">
            <table id="journal_entry_table" class="table table-striped" style="width:100%"></table>
        </div>
    </div>
</div>
@section('styles')
<link href="{{asset('/css/custom/accounting_reports/journal_entries.css')}}" rel="stylesheet">
<style>
    #manual_journal_lines_table th,
    #manual_journal_lines_table td {
        font-size: 12px;
        padding: 4px 6px;
        vertical-align: middle;
        line-height: 1.1;
    }

    #manual_journal_lines_table .form-control.form-control-sm {
        font-size: 12px;
        height: 28px;
        min-height: 28px;
        padding: 2px 6px;
    }

    #manual_journal_lines_table .btn.btn-sm {
        padding: 2px 6px;
        font-size: 11px;
        line-height: 1.1;
    }

    #manual_journal_lines_table tbody tr {
        margin: 0;
    }

    .journal-attach-btn {
        border: 0;
        background: transparent;
        color: #0d6efd;
        font-size: 16px;
        cursor: pointer;
        padding: 0 8px;
    }

    .journal-attach-btn.attached {
        color: #198754;
    }

    #journal_entry_table tbody tr {
        cursor: pointer;
    }

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

    @media (max-width: 991px) {
        .report-impact-statement .ri-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

@endsection
@section('sc-modal')
@parent



<div class="sc-modal-content" id="journal_entries_form">
    <div class="sc-modal-dialog sc-xl" style="max-width: 1300px; width: 96vw;">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <button type="button" class="journal-attach-btn" id="journal_supporting_doc_btn" title="Attach Supporting Docs" onclick="openJournalSupportingDocPicker()">
                <i class="fas fa-paperclip"></i>
            </button>
            <span class="sc-close" onclick="scion.create.sc_modal('journal_entries_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="journalForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12 account_name">
                        <label>JOURNAL ENTRY DESCRIPTION:</label>
                        <input type="text" class="form-control" id="description" name="description"/>
                    </div>

                    <div class="form-group col-md-6 account_number">
                        <label>DATE:</label>
                        <input type="text" class="form-control" id="entry_date" name="entry_date" placeholder="MM-DD-YYYY" maxlength="10" inputmode="numeric" autocomplete="off"/>
                    </div>

                    <div class="form-group col-md-6 account_name">
                        <label>AUTO REVERSING DATE:</label>
                        <input type="text" class="form-control" id="auto_reversing_date" name="auto_reversing_date" placeholder="MM-DD-YYYY" maxlength="10" inputmode="numeric" autocomplete="off"/>
                    </div>

                    <input type="file" class="d-none" id="journal_supporting_doc" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"/>
                    <input type="hidden" id="journal_supporting_doc_data">
                    <input type="hidden" id="journal_supporting_doc_name">
                    <input type="hidden" id="journal_supporting_doc_mime">

                    <input type="hidden" id="journal_status" name="status" value="DRAFT"/>
                </div>

                <div class="row mt-2">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-2" id="manual_journal_lines_table">
                                <thead>
                                    <tr>
                                        <th>Description</th>
                                        <th>Chart of Account</th>
                                        <th>Tax Rate</th>
                                        <th>Debit (PHP)</th>
                                        <th>Credit (PHP)</th>
                                        <th style="width: 70px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="manual_journal_lines_body"></tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-right">Subtotal</th>
                                        <th id="manual_subtotal_debit">0.00</th>
                                        <th id="manual_subtotal_credit">0.00</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mr-2" onclick="addManualJournalLine()">ADD LINE</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="sc-modal-footer d-flex justify-content-end">
            <div class="btn-group mr-2">
                <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    SAVE
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <button class="dropdown-item" type="button" onclick="saveJournalEntryDraft()">Save as Draft</button>
                    <button class="dropdown-item" type="button" onclick="saveJournalEntryDraftAndAddAnother()">Save Draft &amp; Add Another</button>
                </div>
            </div>
            <div class="btn-group mr-2">
                <button type="button" class="btn btn-sm btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    POST
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <button class="dropdown-item" type="button" onclick="postJournalEntry()">Post</button>
                    <button class="dropdown-item" type="button" onclick="postJournalEntryAndAddAnother()">Post &amp; Add Another</button>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-light" onclick="scion.create.sc_modal('journal_entries_form').hide('all', modalHideFunction)">CANCEL</button>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="quick_chart_of_accounts_form">
    <div class="sc-modal-dialog sc-xl" style="max-width: 980px; width: 92vw;">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('quick_chart_of_accounts_form').hide()"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form id="quickChartOfAccountForm">
                <div class="row">
                    <div class="col-md-7">
                        <div class="form-group col-md-12 quick_account_type">
                            <label>ACCOUNT TYPE</label>
                            <select id="quick_account_type" class="form-control">
                                @foreach ($account_types_by_category as $category => $types)
                                    <optgroup label="{{ strtoupper($category) }}">
                                        @foreach ($types as $account_type)
                                            <option value="{{ $account_type->id }}">{{ $account_type->account_type }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-12 quick_account_number">
                            <label>ACCOUNT CODE</label>
                            <input type="text" class="form-control" id="quick_account_number" inputmode="numeric" autocomplete="off" maxlength="10" placeholder="Number only" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)">
                        </div>
                        <div class="form-group col-md-12 quick_account_name">
                            <label>NAME</label>
                            <input type="text" class="form-control" id="quick_account_name" maxlength="150" placeholder="A short title for this account (limited to 150 characters)">
                        </div>
                        <div class="form-group col-md-12 quick_account_description">
                            <label>DESCRIPTION</label>
                            <input type="text" class="form-control" id="quick_account_description" placeholder="(Optional) A description of how this account should be used">
                        </div>
                        <div class="form-group col-md-12 quick_tax">
                            <label>TAX</label>
                            <select id="quick_tax" class="form-control">
                                <option value="">Select Tax</option>
                                <option value="VAT">VAT</option>
                                <option value="NON-VAT">NON-VAT</option>
                            </select>
                        </div>
                        <div class="form-group col-md-12 quick_allow_for_payments">
                            <div class="form-check mt-2">
                                <input type="checkbox" class="form-check-input" id="quick_allow_for_payments" value="1">
                                <label class="form-check-label" for="quick_allow_for_payments">Allow this account to be used for payments</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="card h-100">
                            <div class="card-header bg-light">
                                <strong>Report Impact Review</strong>
                            </div>
                            <div class="card-body">
                                <div id="quick_report_impact_list" class="report-impact-statement"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="sc-modal-footer d-flex justify-content-end">
            <button type="button" class="btn btn-sm btn-primary mr-2" onclick="saveQuickChartOfAccount()">SAVE</button>
            <button type="button" class="btn btn-sm btn-light" onclick="scion.create.sc_modal('quick_chart_of_accounts_form').hide()">CANCEL</button>
        </div>
    </div>
</div>


<div class="sc-modal-content" id="journal_entry_line_fields_form">
    <div class="sc-modal-dialog sc-xl">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('journal_entry_line_fields_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="classForm" class="form-record">
                <div class="row">
                    <!-- Left section for form inputs -->
                    <div class="col-md-12 d-flex mb-3">
                        <div class="form-group col-4">
                            <label for="manual_description">DESCRIPTION/NARRATION</label>
                            <input type="text" class="form-control" id="manual_description" name="manual_description"/>
                        </div>
                        <div class="form-group col-3">
                            <label for="manual_entry_date">DATE</label>
                            <input type="date" class="form-control" id="manual_entry_date" name="manual_entry_date"/>
                        </div>
                        <div class="form-group col-3 offset-md-2">
                            <label for="reversing_date">AUTO REVERSING DATE <small style="color: gray">(OPTIONAL)</small></label>
                            <input type="date" class="form-control" id="reversing_date" name="reversing_date"/>
                        </div>
                    </div>

                </div>

                <table id="journal_detail_table" class="table table-striped" style="width:100%"></table>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button class="btn btn-sm btn-dark me-2"><i class="fas fa-save"></i> SAVE AS DRAFT</button>

                    <div class="d-flex">
                        <button class="btn btn-sm btn-success me-2" onclick="postJournal()"><i class="fas fa-paper-plane"></i> POST</button>
                        <button class="btn btn-sm btn-primary"><i class="fas fa-times"></i> CANCEL</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="journal_details_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('journal_details_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="journalDetailForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12 account_name">
                        <label>DESCRIPTION</label>
                        <input type="hidden" class="form-control" id="record_id" name="record_id"/>
                        <input type="text" class="form-control" id="detail_description" name="detail_description"/>
                    </div>

                    <div class="form-group col-md-12 account_type">
                        <label>ACCOUNT</label>
                        <select name="chart_of_account_id" id="chart_of_account_id" class="form-control">
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->account_name . ' (' . $account->account_number . ')'}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-12 account_type">
                        <label>TAX RATE</label>
                        <select name="tax_rate" id="tax_rate" class="form-control">
                            <option value="VAT">VAT</option>
                            <option value="NON-VAT">NON-VAT</option>
                        </select>
                    </div>

                    <div class="form-group col-md-12 account_name">
                        <label>DEBIT AUD</label>
                        <input type="number" class="form-control" id="debit_amount" name="debit_amount" value="0"/>
                    </div>

                    <div class="form-group col-md-12 account_name">
                        <label>CREDIT AUD</label>
                        <input type="number" class="form-control" id="credit_amount" name="credit_amount" value="0"/>
                    </div>
                </div>
            </form>
        </div>
        <div class="sc-modal-footer text-right">
            <button class="btn btn-sm btn-primary btn-sv" onclick="$('#sv').click()">SAVE</button>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="journal_report">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('journal_report').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="container">
                <div id="journal_report_print">
                    <div class="report-container">
                    <div class="spacer"></div>
                    <div class="row">
                        <div class="col-4" style="text-align: left;">
                            <img src="/images/logo-dark.png" style="width: 70px;" alt="">
                        </div>
                        <div class="col-4" style="text-align: center;">
                            <p class="mb-0 text-title">Journal Report</p>
                            <p class="mb-0">SP Construction Corporation</p>

                        </div>
                        <div class="col-4" style="text-align: right;">
                            <p class="mb-0">From 09/14/24</p>
                            <p class="mb-0">To 09/14/24</p>
                        </div>
                    </div>
                    <div class="spacer"></div>
                    <div class="row">
                        <div class="col-12">
                        <table>
                            <tr>
                                <th>Account</th>
                                <th style="text-align: right;">Debit</th>
                                <th style="text-align: right;">Credit</th>
                            </tr>
                            <tr>
                                <td style="width:70%">Cash</td>
                                <td style="text-align: right;">0.00</td>
                                <td style="text-align: right;">1,500.00</td>
                            </tr>
                            <tr>
                                <td style="width:70%">Transportation</td>
                                <td style="text-align: right;">2,000.00</td>
                                <td style="text-align: right;">0.00</td>
                            </tr>
                            <tr>
                                <td style="width:70%">Travel Expenses</td>
                                <td style="text-align: right;">1,400.00</td>
                                <td style="text-align: right;">0.00</td>
                            </tr>
                            <tr>
                                <td style="width:70%">Utilities Expenses</td>
                                <td style="text-align: right;">5,000.00</td>
                                <td style="text-align: right;">0.00</td>
                            </tr>
                        </table>
                        </div>
                    </div>
                    <div class="spacer"></div>

                    <div class="row">
                        <div class="col-12">
                        <table>
                            <tr>
                                <th style="width:70%">Totals</th>
                                <th style="text-align: right;">8,400.00</th>
                                <th style="text-align: right;">1,500.00</th>
                            </tr>
                        </table>
                        </div>
                    </div>
                    <div class="spacer"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="sc-modal-footer">
            <div class="row">
                <div class="col-12" style="text-align: center;">
                <button class="btn btn-primary" type="button" onclick="printJournalReport()" style="width: 200px;">Print</button>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="sc-modal-content" id="journal_details_report">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('journal_details_report').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="container">
                <div id="journal_details_report_print">
                    <div class="report-container">
                    <div class="spacer"></div>
                    <div class="row">
                        <div class="col-4" style="text-align: left;">
                            <img src="/images/logo-dark.png" style="width: 70px;" alt="">
                        </div>
                        <div class="col-4" style="text-align: center;">
                            <p class="mb-0 text-title">Journal Report</p>
                            <p class="mb-0">SP Construction Corporation</p>
                            <p class="mb-0 text-subtitle">Account: Sales</p>

                        </div>
                        <div class="col-4" style="text-align: right;">
                            <p class="mb-0">From 09/14/24</p>
                            <p class="mb-0">To 09/14/24</p>
                        </div>
                    </div>
                    <div class="spacer"></div>
                    <div class="row">
                        <div class="col-12">
                        <table>
                            <tr>
                                <th>Period</th>
                                <th>Description</th>
                                <th>Source ID</th>
                                <th style="text-align: right;">Debit</th>
                                <th style="text-align: right;">Credit</th>
                            </tr>
                            <tr>
                                <td>02-Jun-2024</td>
                                <td>Sales June 02</td>
                                <td>S</td>
                                <td style="text-align: right;">0.00</td>
                                <td style="text-align: right;">250.00</td>
                            </tr>
                            <tr>
                                <td>02-Jun-2024</td>
                                <td>Sales June 02</td>
                                <td>S</td>
                                <td style="text-align: right;">0.00</td>
                                <td style="text-align: right;">250.00</td>
                            </tr>
                            <tr>
                                <td>05-Jun-2024</td>
                                <td>Sales June 05</td>
                                <td>JO</td>
                                <td style="text-align: right;">0.00</td>
                                <td style="text-align: right;">1000.00</td>
                            </tr>
                    
                        </table>
                        </div>
                    </div>
                    <div class="spacer"></div>

                    <div class="row">
                        <div class="col-12">
                        <table>
                            <tr>
                                <th style="width:80%;">Totals</th>
                                <th style="text-align: right;">1,500.00</th>
                            </tr>
                        </table>
                        </div>
                    </div>
                    <div class="spacer"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="sc-modal-footer">
            <div class="row">
                <div class="col-12" style="text-align: center;">
                <button class="btn btn-primary" type="button" onclick="printJournalDetailsReport()" style="width: 200px;">Print</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="journal_entry_details">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('journal_entry_details').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="container">
                <div id="journal_entry_details_print">
                    <div class="report-container">
                    <div class="spacer"></div>
                    <div class="row">
                        <div class="col-4" style="text-align: left;">
                            <img src="/images/logo-dark.png" style="width: 70px;" alt="">
                        </div>
                        <div class="col-4" style="text-align: center;">
                            <p class="mb-0 text-title" id="journal_entry_details_title">Manual Journal Entry</p>
                            <p class="mb-0" id="journal_entry_details_status">Status: -</p>

                        </div>
                        <div class="col-4" style="text-align: right;">
                            <p class="mb-0" id="journal_entry_details_date">Date: -</p>
                        </div>
                    </div>
                    <div class="spacer"></div>
                    <div class="row">
                        <div class="col-12">
                        <table>
                            <tr>
                                <th style="width:30%">Description</th>
                                <th style="width:20%">Account</th>
                                <th style="width:20%">Tax Rate</th>
                                <th style="text-align: right;">Debit</th>
                                <th style="text-align: right;">Credit</th>
                            </tr>
                            <tbody id="journal_entry_details_lines"></tbody>
                     
                        </table>
                        </div>
                    </div>
                    <div class="spacer"></div>

                    <div class="row">
                        <div class="col-12">
                        <table>
                            <tr>
                                <th style="width:70%">Subtotal</th>
                                <th style="text-align: right;" id="journal_entry_details_subtotal_debit">0.00</th>
                                <th style="text-align: right;" id="journal_entry_details_subtotal_credit">0.00</th>
                            </tr>
                            <tr>
                                <th style="width:70%">TOTAL</th>
                                <th style="text-align: right;" id="journal_entry_details_total_debit">0.00</th>
                                <th style="text-align: right;" id="journal_entry_details_total_credit">0.00</th>
                            </tr>
                        </table>
                        </div>
                    </div>
                    <div class="spacer"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="sc-modal-footer">
            <div class="row">
                <div class="col-12" style="text-align: center;">
                <button class="btn btn-primary" type="button" onclick="printJournalEntryDetails()" style="width: 200px;">Print</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@endsection

@section('scripts')
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="/js/backend/pages/accounting/transaction/journal_entries.js"></script>
@endsection
