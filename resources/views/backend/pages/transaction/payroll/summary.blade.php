@extends('backend.master.index')

@section('title', 'PAYROLL SUMMARY')

@section('breadcrumbs')
    <span>TRANSACTION </span> / <span class="highlight">PAYROLL</span>
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
                    <h3>CURRENT PAYROLL</h3>
                    <table id="payroll_summary_table" class="table table-striped" style="width:100%"></table>
                    <h3>PAYROLL HISTORY</h3>
                    <table id="payroll_history_table" class="table table-striped" style="width:100%"></table>
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
                        <button class="btn btn-sm btn-warning sent-email"><i class="fas fa-envelope"></i> SEND PAYSLIP</button>
                        <h5><b>PAYROLL SCHEDULE: </b><span id="payroll_period"></span></h5>
                    </div>
                    <div class="col-12">
                        <div class="overall_label">OVERALL TOTAL</div>
                        <table id="overallTotal">
                            <thead>
                                <th>GROSS EARNING</th>
                                <th>SSS</th>
                                <th>PAG-IBIG</th>
                                <th>PHILHEALTH</th>
                                <th>TAX</th>
                                <th>NETPAY</th>
                            </thead>
                            <tbody>
                                <tr>
                                    <td id="total_gross">-</td>
                                    <td id="total_sss">-</td>
                                    <td id="total_pagibig">-</td>
                                    <td id="total_philhealth">-</td>
                                    <td id="total_tax">-</td>
                                    <td id="total_netpay">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <table id="payroll_details_table" class="table table-striped" style="width:100%"></table>
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