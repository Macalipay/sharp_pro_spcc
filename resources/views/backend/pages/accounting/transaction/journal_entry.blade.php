@extends('backend.master.index')

@section('title', 'JOURNAL ENTRY')

@section('breadcrumbs')
    <span>TRANSACTION</span> / <span class="highlight">JOURNAL ENTRY</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <table id="journal_entry_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>
@section('styles')
<link href="{{asset('/css/custom/accounting_reports/journal_entries.css')}}" rel="stylesheet">

@endsection
@section('sc-modal')
@parent



<div class="sc-modal-content" id="journal_entries_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('journal_entries_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="journalForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12 account_number">
                        <label>ENTRY DATE</label>
                        <input type="date" class="form-control" id="entry_date" name="entry_date"/>
                    </div>

                    <div class="form-group col-md-12 account_name">
                        <label>DESCRIPTION/NARRATION</label>
                        <input type="text" class="form-control" id="description" name="description"/>
                    </div>
                </div>
            </form>
        </div>
        <div class="sc-modal-footer text-right">
            <button class="btn btn-sm btn-primary btn-sv" onclick="$('#sv').click()">SAVE</button>
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
                            <p class="mb-0 text-title">Posted Journal Entry</p>
                            <p class="mb-0">Status: Posted</p>

                        </div>
                        <div class="col-4" style="text-align: right;">
                            <p class="mb-0">Date: June 05,2024</p>
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
                            <tr>
                                <td style="">Sales June 05</td>
                                <td style="">Sales</td>
                                <td style="">Cash</td>
                                <td style="text-align: right;">0.00</td>
                                <td style="text-align: right;">1,000.00</td>
                            </tr>
                            <tr>
                                <td style="">Sales June 05</td>
                                <td style="">Sales</td>
                                <td style="">Cash</td>
                                <td style="text-align: right;">1,000.00</td>
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
                                <th style="width:70%">Subtotal</th>
                                <th style="text-align: right;">1,000.00</th>
                                <th style="text-align: right;">1,000.00</th>
                            </tr>
                            <tr>
                                <th style="width:70%">TOTAL</th>
                                <th style="text-align: right;">1,000.00</th>
                                <th style="text-align: right;">1,000.00</th>
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
