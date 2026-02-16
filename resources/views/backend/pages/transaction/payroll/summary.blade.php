@extends('backend.master.index')

@section('title', 'PAYROLL SUMMARY')

@section('breadcrumbs')
    <span>TRANSACTION </span> / <span class="highlight">PAYROLL SUMMARY DETAILS</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">PAYROLL SUMMARY</h5>
            </div>
            @include('backend.partial.flash-message')
            <div class="col-12">
                <div class="card-body">
                    <div class="summary-filter-bar">
                        <div class="summary-filter-item">
                            <label for="sort_date_covered">Sort Date Covered</label>
                            <select id="sort_date_covered" class="form-control form-control-sm">
                                <option value="desc">Newest to Oldest</option>
                                <option value="asc">Oldest to Newest</option>
                            </select>
                        </div>
                        <div class="summary-filter-item">
                            <label for="filter_period_type">Filter Period Type</label>
                            <select id="filter_period_type" class="form-control form-control-sm">
                                <option value="">All</option>
                                <option value="0">13th Month Pay</option>
                                <option value="1">Monthly</option>
                                <option value="2">Semi-Monthly</option>
                                <option value="3">Bi-Weekly</option>
                                <option value="4">Weekly</option>
                            </select>
                        </div>
                        <div class="summary-filter-item">
                            <label for="filter_status">Filter Status</label>
                            <select id="filter_status" class="form-control form-control-sm">
                                <option value="">All</option>
                                <option value="0">Draft</option>
                                <option value="1">Completed</option>
                                <option value="2">Payslip Sent</option>
                            </select>
                        </div>
                        <div class="summary-filter-item summary-filter-keyword">
                            <label for="filter_keyword">Search</label>
                            <input type="text" id="filter_keyword" class="form-control form-control-sm" placeholder="Sequence no, project, period...">
                        </div>
                        <div class="summary-filter-actions">
                            <button type="button" id="summary_apply_filter" class="btn btn-sm btn-primary">Search</button>
                            <button type="button" id="summary_reset_filter" class="btn btn-sm btn-light">Reset</button>
                        </div>
                    </div>
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="payroll-current-tab" data-toggle="tab" href="#payroll-current-pane" role="tab" aria-controls="payroll-current-pane" aria-selected="true">
                                PAYROLL CURRENT
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="payroll-history-tab" data-toggle="tab" href="#payroll-history-pane" role="tab" aria-controls="payroll-history-pane" aria-selected="false">
                                PAYROLL HISTORY
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content p-3 border border-top-0 summary-tab-content">
                        <div class="tab-pane fade show active payroll-summary-pane" id="payroll-current-pane" role="tabpanel" aria-labelledby="payroll-current-tab">
                            <div class="summary-status-note"><strong>Status:</strong> FOR APPROVAL</div>
                            <div class="payroll-table-scroll" id="payroll-current-wrap">
                                <table id="payroll_summary_table" class="table table-striped" style="width:100%"></table>
                            </div>
                        </div>
                        <div class="tab-pane fade payroll-summary-pane" id="payroll-history-pane" role="tabpanel" aria-labelledby="payroll-history-tab">
                            <div class="summary-status-note"><strong>Status:</strong> PAYROLL COMPLETED</div>
                            <div class="payroll-table-scroll" id="payroll-history-wrap">
                                <table id="payroll_history_table" class="table table-striped" style="width:100%"></table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('sc-modal')
<div class="sc-modal-content" id="payroll_summary_form">
    <div class="sc-modal-dialog sc-xl">
        <div class="sc-modal-header">
            <span class="sc-title-bar">Payroll Details</span>
            <span class="sc-close" onclick="scion.create.sc_modal('payroll_summary_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="timeLogsForm" class="form-record">
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="summary-action-row">
                            <button type="button" class="btn btn-sm btn-info print-all-payslips btn-compact"><i class="fas fa-print"></i> PRINT ALL PAYSLIPS</button>
                            <button type="button" class="btn btn-sm btn-warning sent-email btn-compact"><i class="fas fa-envelope"></i> EMAIL PAYSLIPS</button>
                        </div>
                        <h5 class="payroll-summary-details-label"><b>PAYROLL SUMMARY DETAILS: </b><span id="payroll_period"></span></h5>
                    </div>
                    <div class="col-12">
                        <div class="overall_label">OVERALL TOTAL</div>
                        <table id="overallTotal">
                            <thead>
                                <th>TOTAL GROSS EARNINGS</th>
                                <th>TOTAL GROSS DEDUCTION</th>
                                <th>TOTAL NET PAY</th>
                            </thead>
                            <tbody>
                                <tr>
                                    <td id="summary_total_gross_earnings">-</td>
                                    <td id="summary_total_gross_deduction">-</td>
                                    <td id="summary_total_net_pay">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="payroll-details-scroll">
                    <table id="payroll_details_table" class="table table-striped" style="width:100%"></table>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="approval_confirmation">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar">COMPLETE SEQUENCE: <b class="sequence_no_disp"></b></span>
            <span class="sc-close" onclick="scion.create.sc_modal('approval_confirmation').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="message">
                ARE YOU SURE YOU WANT TO COMPLETE THIS PAYROLL?
            </div>
        </div>
        <div class="sc-modal-footer">
            <div class="row">
                <div class="col-12 text-right">
                    <button class="btn btn-sm btn-success positive-button">YES</button>
                    <button class="btn btn-sm btn-danger negative-button">NO</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="email_payslip_modal">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar">Email Payslips</span>
            <span class="sc-close" onclick="scion.create.sc_modal('email_payslip_modal').hide()"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="email-payslip-actions mb-2">
                <label class="mb-0"><input type="checkbox" id="email_select_all" checked> Select All</label>
            </div>
            <div class="email-payslip-table-wrap">
                <table class="table table-striped table-sm mb-0" id="email_payslip_table">
                    <thead>
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>Employee</th>
                            <th>Registered Email</th>
                            <th style="width: 140px;">Payslip Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div class="sc-modal-footer">
            <div class="row">
                <div class="col-12 text-right">
                    <button type="button" class="btn btn-sm btn-secondary email-payslip-cancel">Cancel</button>
                    <button type="button" class="btn btn-sm btn-warning email-payslip-send">Send Selected</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="payslip_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar">Payslip</span>
            <span class="sc-close" onclick="scion.create.sc_modal('payslip_form').hide('', custom_modalHide)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body" id="print_payslip">
            <div class="row">
                <div class="col-12" style="display: flex;">
                    <div class="company-info-payslip">
                        <p class="payslip-title">Payslip</p>
                        <p class="payslip-company-name">S&P Internation Holdings Inc.</p>
                        <div class="company-info">Lot 14 Blk 2 Yakal St. Agapito Subd. Brgy Santalon MN 1610</div>
                        <div class="company-info">900000000</div>
                    </div>
                    <div class="company-img-payslip">
                        <img class="payslip-img" src="/images/logo-2-dark.png" alt="">
                    </div>
                </div>
            </div>
            <hr style="margin-bottom: 10px;">
            <div class="row">
                <div class="col-6">
                    <table id="payroll_employee_info">
                        <thead>
                            <th colspan="2">EMPLOYEE INFORMATION</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Employee Name:</td>
                                <td id="tbl_emp_name"></td>
                            </tr>
                            <tr>
                                <td>Employee Number:</td>
                                <td id="tbl_emp_number"></td>
                            </tr>
                            <tr>
                                <td>Department:</td>
                                <td id="tbl_emp_department"></td>
                            </tr>
                            <tr>
                                <td>Position:</td>
                                <td id="tbl_emp_position"></td>
                            </tr>
                            <tr>
                                <td>Status:</td>
                                <td id="tbl_emp_status"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-6">
                    <table id="payroll_calculation">
                        <thead>
                            <th colspan="2">PAY DATE</th>
                            <th colspan="2">PAY TYPE</th>
                            <th colspan="2">PERIOD</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="2" id="pay_date">-</td>
                                <td colspan="2" id="pay_type">-</td>
                                <td colspan="2" id="pay_period">-</td>
                            </tr>
                        </tbody>
                        <thead>
                            <th colspan="3">SEQUENCE #</th>
                            <th colspan="3">NET PAY</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="3" id="sequence_no">-</td>
                                <td colspan="3" id="net_pay">-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <hr/>
            <table id="payroll_rate_details">
                <thead>
                    <th>EARNINGS</th>
                    <th class="text-center">DAILY RATE</th>
                    <th class="text-center">DAYS</th>
                    <th class="text-center">TOTAL</th>
                </thead>
                <tbody class="custom"></tbody>
                <tbody class="holiday-container"></tbody>
                <tbody class="allowance-container"></tbody>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right" style="width:90%">Total Earnings</td>
                        <td class="text-center" id="total_earnings" style="width:30%">-</td>
                    </tr>
                </tfoot>
            </table>

            <br>

            <table id="payroll_leaves">
                <thead>
                    <th>LEAVES</th>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right" style="width:90%">Paid Leaves</td>
                        <td class="text-center" id="total_leaves" style="width:30%">-</td>
                    </tr>
                    
                    <tr class="gross">
                        <td colspan="3" class="text-right" style="width:90%">Total Gross Earnings</td>
                        <td class="text-center" id="total_gross" style="width:30%">-</td>
                    </tr>
                </tfoot>
            </table>
            
            <table id="payroll_deductions">
                <thead>
                    <th>Government Mandated Benefits</th>
                </thead>
                <tbody>
                    <tr>
                        <td>SSS</td>
                        <td class="text-right" style="width:90%"></td>
                        <td class="text-center" id="total_sss" style="width:30%">-</td>
                    </tr>
                    <tr>
                        <td>Philhealth</td>
                        <td class="text-right" style="width:90%"></td>
                        <td class="text-center" id="total_philhealth" style="width:30%">-</td>
                    </tr>
                    <tr>
                        <td>Pag-ibig</td>
                        <td class="text-right" style="width:90%"></td>
                        <td class="text-center" id="total_pagibig" style="width:30%">-</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-right" style="width:90%">Government Deductions</td>
                        <td class="text-center" id="total_government_deduction" style="width:30%">-</td>
                    </tr>
                </tfoot>
            </table>

            <table id="payroll_other_deductions">
                <thead>
                    <th>Other Deductions</th>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr class="other-deduct-total">
                        <td colspan="3" class="text-right" style="width:90%">Other Deductions</td>
                        <td class="text-center" id="other_deduction" style="width:30%">-</td>
                    </tr>
                    <tr class="gross">
                        <td colspan="3" class="text-right" style="width:90%">Total Deductions</td>
                        <td class="text-center" id="total_deduction" style="width:30%">-</td>
                    </tr>
                </tfoot>
            </table>

            <table id="payroll_tax">
                <tbody>
                    <tr>
                        <td style="width:90%">Tax Amount</td>
                        <td class="text-center" id="tax_amount" style="width:30%">-</td>
                    </tr>
                    <tr>
                        <td style="width:90%">Withholding Tax</td>
                        <td class="text-center" id="withholding_tax" style="width:30%">-</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td style="width:90%">NET PAY</td>
                        <td class="text-center" id="total_net_pay" style="width:30%">-</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="payslip_form_2">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar">Payslip</span>
            <span class="sc-close" onclick="scion.create.sc_modal('payslip_form_2').hide('', custom_modalHide)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body" id="print_payslip_2">
            <div class="row">
                <div class="col-12" style="display: flex;">
                    <div class="company-info-payslip">
                        <p class="payslip-title">Payslip</p>
                        <p class="payslip-company-name">S&P Internation Holdings Inc.</p>
                        <div class="company-info">Lot 14 Blk 2 Yakal St. Agapito Subd. Brgy Santalon MN 1610</div>
                        <div class="company-info">900000000</div>
                    </div>
                    <div class="company-img-payslip">
                        <img class="payslip-img" src="/images/logo-2-dark.png" alt="">
                    </div>
                </div>
            </div>
            <hr style="margin-bottom: 10px;">
            <div class="row">
                <div class="col-12">
                    <table id="payroll_employee_info">
                        <thead>
                            <th colspan="2">EMPLOYEE INFORMATION</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Employee Name:</td>
                                <td id="13_tbl_emp_name"></td>
                            </tr>
                            <tr>
                                <td>Employee Number:</td>
                                <td id="13_tbl_emp_number"></td>
                            </tr>
                            <tr>
                                <td>Department:</td>
                                <td id="13_tbl_emp_department"></td>
                            </tr>
                            <tr>
                                <td>Position:</td>
                                <td id="13_tbl_emp_position"></td>
                            </tr>
                            <tr>
                                <td>Status:</td>
                                <td id="13_tbl_emp_status"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-12">
                    <table id="payroll_calculation">
                        <thead>
                            <th colspan="2">PAY DATE</th>
                            <th colspan="2">PAY TYPE</th>
                            <th colspan="2">PERIOD</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="2" id="13_pay_date">-</td>
                                <td colspan="2" id="13_pay_type">-</td>
                                <td colspan="2" id="13_pay_period">-</td>
                            </tr>
                        </tbody>
                        <thead>
                            <th colspan="3">SEQUENCE #</th>
                            <th colspan="3">NET PAY</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="3" id="13_sequence_no">-</td>
                                <td colspan="3" id="13_net_pay">-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <hr/>
            <table id="payroll_rate_details_13">
                <thead>
                    <th style="width:50%">EARNINGS</th>
                    <th class="text-right" style="width:50%">TOTAL</th>
                </thead>
                <tbody class="custom"></tbody>
                <tfoot>
                    <tr>
                        <td class="text-right" style="width:50%">Total Earnings</td>
                        <td class="text-right" id="13_total_earnings" style="width:50%">-</td>
                    </tr>
                </tfoot>
            </table>

            <table id="payroll_tax">
                <tfoot>
                    <tr>
                        <td style="width:50%">NET PAY</td>
                        <td class="text-right" id="13_total_net_pay" style="width:50%">-</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="add_details">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('add_details').hide('', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="detailsForm" class="form-record">
                <div class="form-group col-md-12 type">
                    <label>EARNING TYPE</label>
                    <select name="type" id="type" class="form-control">
                    </select>
                </div>
                <div class="form-group col-md-12 amount">
                    <label>AMOUNT</label>
                    <input type="number" class="form-control" id="amount" name="amount" value="0" min="0"/>
                </div>
            </form>
        </div>
    </div>
</div>

@parent
@endsection
@endsection

@section('scripts')
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="/js/backend/pages/transaction/payroll_summary.js"></script>
@endsection

@section('styles-2')
    <link href="{{asset('/css/custom/payroll_summary.css')}}" rel="stylesheet">
@endsection
