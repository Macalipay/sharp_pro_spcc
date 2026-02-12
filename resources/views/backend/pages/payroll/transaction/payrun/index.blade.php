@extends('backend.master.index')

@section('title', 'PAYRUN JOSH')

@section('breadcrumbs')
    <span>TRANSACTION / PAYROLL</span>  /  <span class="highlight">PAYRUN</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        @include('backend.partial.flash-message')
        <div class="col-12">
            <div id="filter-dialog" class="row">
                <div class="col-2">
                    <label for="f-payment-type">Payment Type:</label>
                    <select name="f-payment-type" id="f-payment-type" class="form-control form-control-sm" onchange="getPayrun()">
                        <option value="">ALL</option>
                        <option value="1">MONTHLY</option>
                        <option value="2">SEMI-MONTHLY</option>
                        <option value="3">BI-WEEKLY</option>
                        <option value="4">WEEKLY</option>
                    </select>
                </div>
                <div class="col-2">
                    <label for="f-payment-status">Payment Status:</label>
                    <select name="f-payment-status" id="f-payment-status" class="form-control form-control-sm" onchange="getPayrun()">
                        <option value="">ALL</option>
                        <option value="0">DRAFT</option>
                        <option value="1">COMPLETED</option>
                    </select>
                </div>
                <div class="col-2">
                    <label for="f-payment-start">Payment Date (Start):</label>
                    <input type="date" class="form-control form-control-sm" name="f-payment-start" id="f-payment-start" onchange="getPayrun()">
                </div>
                <div class="col-2">
                    <label for="f-payment-end">(End)</label>
                    <input type="date" class="form-control form-control-sm" name="f-payment-end" id="f-payment-end" onchange="getPayrun()">
                </div>
            </div>
            <div id="filter">
                <button class="btn btn-sm btn-light" style="display:none;" id="backBtn" onclick="backPressed()"><i class="fas fa-arrow-left"></i> BACK</button>
                {{-- <button class="btn btn-sm btn-light btn-filter" id="draftBtn">DRAFT</button>
                <button class="btn btn-sm btn-light btn-filter" id="completedBtn">COMPLETED</button> --}}
            </div>
        </div>
        <div class="col-12">
            <div id="first">
                <table id="payrun_table" class="table table-striped" style="width:100%"></table>
            </div>
            <div id="second" style="display:none;">
                <div class="header-details">
                    <div class="calendar-title"></div>
                    <div class="calendar-period">Period Covered: <span class="period-val">-</span></div>
                    <div class="calendar-cut-off">Period Cut-off: <span class="cut-off-val">-</span></div>
                </div>
                <table id="employee-table-list">
                    <thead>
                        <tr>
                            <th rowspan="2" colspan="3">ACTION</th>
                            <th rowspan="2" style="width: 150px;"><div>NAME OF EMPLOYEES</div></th>
                            <th rowspan="2" class="sm-col"><div>STATUS</div></th>
                            <th rowspan="2" class="sm-col"><div>NO. OF WORKED DAYS</div></th>
                            <th rowspan="2" class="md-col"><div>HOLIDAY</div></th>
                            <th colspan="3" class="editable-cells"><div>BASIC PAY</div></th>
                            <th rowspan="2" class="md-col"><div class="update-header">BASIC PAY</div></th>
                            <th colspan="2" class="editable-cells"><div>ALLOWANCE</div></th>
                            <th colspan="2"><div>REGULAR OT</div></th>
                            <th colspan="4"><div>TARDINESS DEDUCTION</div></th>
                            <th colspan="1" class="sm-col"><div>LEAVE</div></th>
                            <th rowspan="2" class="md-col td-gross"><div>GROSS SALARY</div></th>
                            <th rowspan="2" class="sm-col editable-cells"><div>SSS</div></th>
                            <th rowspan="2" class="sm-col editable-cells"><div>PHIC</div></th>
                            <th rowspan="2" class="sm-col editable-cells"><div>PAG-IBIG</div></th>
                            <th rowspan="2" class="sm-col editable-cells"><div>WITH- HOLDING TAX</div></th>
                            <th rowspan="2" class="sm-col editable-cells"><div>CASH ADVANCE</div></th>
                            <th rowspan="2" class="md-col td-gross"><div>GROSS DEDUCTION</div></th>
                            <th rowspan="2" class="md-col"><div>NET PAY</div></th>
                        </tr>
                        <tr>
                            <th class="md-col">MONTHLY</th>
                            <th class="md-col">DAILY</th>
                            <th class="sm-col">HOURS</th>
                            <th class="md-col">DAILY RATE</th>
                            <th class="md-col">AMOUNT</th>
                            <th class="sm-col"># OF HRS</th>
                            <th class="md-col">AMOUNT</th>
                            <th class="sm-col">DAYS</th>
                            <th class="md-col">AMOUNT</th>
                            <th class="sm-col">TARDY MINS.</th>
                            <th class="md-col">AMOUNT</th>
                            <th class="sm-col"># OF DAYS</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <td class="text-right" style="background:#fff;" colspan="5"></td>
                            <td id="total-work-days"></td>
                            <td id="total-holiday"></td>
                            <td colspan="3"></td>
                            <td id="total-basic-pay">0</td>
                            <td></td>
                            <td id="total-allowance">0</td>
                            <td id="total-ot-hours">0</td>
                            <td id="total-ot-amount">0</td>
                            {{-- <td></td> --}}
                            <td id="total-late-days">0</td>
                            <td id="total-absent-amount">0</td>
                            <td id="total-late-mins">0</td>
                            <td id="total-late-amount">0</td>
                            <td id="total-leave-amount">0</td>
                            <td id="total-gross-salary" class="td-gross">0</td>
                            <td id="total-sss">0</td>
                            <td id="total-phic">0</td>
                            <td id="total-pagibig">0</td>
                            <td id="total-wt">0</td>
                            <td id="total-ca">0</td>
                            <td id="total-gross-deduction" class="td-gross">0</td>
                            <td id="total-net-pay">0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@section('sc-modal')
@parent
<div class="sc-modal-content" id="payrun_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('payrun_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="payrunForm" class="form-record">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="payment_schedule">PAYMENT SCHEDULE:</label>
                            <select name="payment_schedule" id="payment_schedule" class="form-control form-control-sm" onchange="selectPaymentSchedule()">
                                <option value="" style="display:none;">PLEASE SELECT PAYMENT SCHEDULE</option>
                                <option value=""></option>
                                @foreach ($calendar as $item)
                                    <option value="{{$item->id}}">{{$item->title}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="period_start">PERIOD START:</label>
                            <input type="date" class="form-control form-control-sm" name="period_start" id="period_start"/>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="period_start">PERIOD END:</label>
                            <input type="date" class="form-control form-control-sm" name="payroll_period" id="payroll_period"/>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label for="period_start">PAY DATE:</label>
                            <input type="date" class="form-control form-control-sm" name="pay_date" id="pay_date"/>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="sc-modal-footer text-right">
            <button class="btn btn-sm btn-primary btn-sv" onclick="$('#sv').click()">SAVE</button>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="details_form">
    <div class="sc-modal-dialog sc-xl">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('details_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-12">
                    <div class="info-card">
                        <div class="row">
                            <div class="col-4 e-details">
                                <div><b>Employee No.:</b> <span id="emp_no"></span></div>
                                <div><b>Name:</b> <span id="emp_name"></span></div>
                                <div><b>Payroll Status:</b> <span id="emp_status"></span></div>
                            </div>
                            <div class="col-4 e-details">
                                <div><b>Monthly Salary:</b> <span id="monthly_salary"></span></div>
                                <div><b>Daily Salary:</b> <span id="daily_salary"></span></div>
                                <div><b>Hourly Salary:</b> <span id="hourly_rate"></span></div>
                            </div>
                            <div class="col-4 e-details">
                                <div><b>Regular Hours:</b> <span id="regular_hours"></span> <span id="late_hours"></span></div>
                                <div><b>Overtime:</b> <span id="overtime"></span></div>
                                <div><b>Working Hours:</b> <span id="working_hours"></span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 mb-3">
                    <div class="row">
                        <div class="col-12">
                            <table id="timesheet">
                                <thead>
                                    <th>DATE</th>
                                    <th>DAY</th>
                                    <th>WORK STATUS</th>
                                    <th>TIME IN</th>
                                    <th>BREAK IN</th>
                                    <th>BREAK OUT</th>
                                    <th>TIME OUT</th>
                                    <th>OVERTIME IN</th>
                                    <th>OVERTIME OUT</th>
                                    <th>REGULAR HOURS</th>
                                    <th>LATE</th>
                                    <th>UT</th>
                                    <th>OVERTIME</th>
                                    <th>TOTAL WORKING HOURS</th>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-12 text-right">
                    <button class="btn btn-primary" onclick="saveUpdate()">SAVE UPDATE</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="ot_request_list">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('ot_request_list').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <table id="ot-request-table">
                <thead>
                    <th>REASON</th>
                    <th>STATUS</th>
                    <th>DATE</th>
                    <th>TIME</th>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="allowance_list">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('allowance_list').hide('all', closeEditable)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-12 text-right"><button class="btn btn-primary btn-sm" onclick="addAllowance()">ADD ALLOWANCE</button></div>
                <div class="col-12">
                    <table id="allowance-table"></table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="amount_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('amount_form').hide()"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label>Allowance Type:</label>
                        <select name="allowance_id" id="allowance_id" class="form-control" onchange="getAllowanceAmount()">
                            <option value=""></option>
                            @foreach ($allowance as $item)
                                <option value="{{$item->id}}">{{$item->name}}</option>
                            @endforeach
                        </select>
                        <div class="select-lbl">Allowance Type Amount:<span class="allowance-amount" data-val="0">₱ 0.00</span></div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label>No. of Days:</label>
                        <input type="number" class="form-control" id="no_days" name="no_days" value="1" onkeyup="countTotalAmount()">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label>Total:</label>
                        <input type="number" class="form-control" id="amount" name="amount" value="0">
                    </div>
                </div>
            </div>
        </div>
        <div class="sc-modal-footer text-right">
            <button class="btn btn-sm btn-primary btn-sv" onclick="$('#sv').click()">SAVE</button>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="editableCell">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('editableCell').hide('all', closeEditable)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label>Amount:</label>
                        <input type="number" class="form-control" id="editable_amount" name="editable_amount"/>
                    </div>
                </div>
            </div>
        </div>
        <div class="sc-modal-footer text-right">
            <button class="btn btn-sm btn-primary btn-sv" onclick="$('#sv').click()">SAVE</button>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="ca_list">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('ca_list').hide('all', closeEditable)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-12 text-right"><button class="btn btn-primary btn-sm" onclick="addCA()">ADD CASH ADVANCE</button></div>
                <div class="col-12">
                    <table id="cash-advance-table"></table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="ca_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('ca_form').hide()"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label>Date:</label>
                        <input type="date" class="form-control" id="ca_date" name="ca_date">
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label>Amount:</label>
                        <input type="number" class="form-control" id="ca_amount" name="ca_amount" value="0">
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label>Purpose:</label>
                        <textarea name="ca_purpose" id="ca_purpose" class="form-control"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="sc-modal-footer text-right">
            <button class="btn btn-sm btn-primary btn-sv" onclick="$('#sv').click()">SAVE</button>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="payslip_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('payslip_form').hide('all')"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-12">
                    <div id="parslipForm">
                        <table style="width: 100%;">
                            <tr>
                                <td>
                                    <h5 class="ps-company">S&P International Holdings Inc.</h5>
                                    <div class="ps-address">Lot 14 Blk 2 Yakal St. Agapito Subd. Brgy Santalon MN 1610</div>
                                    <div class="ps-contact">091 2345 6789</div>
                                </td>
                                <td style="text-align: right">
                                    <img class="payslip-img" src="/images/logo-2-dark.png" alt="">
                                </td>
                            </tr>
                        </table>
                        
                        <table class="ps-employee-details" style="width: 100%;">
                            <tr>
                                <td style="width: 50%;">
                                    <table class="ps-details" style="width: 100%;">
                                        <tr>
                                            <td colspan="2" class="ps-heading">Employee Information</td>
                                        </tr>
                                        <tr>
                                            <td>Name</td>
                                            <td class="ps-name ps-info">-</td>
                                        </tr>
                                        <tr>
                                            <td>ID</td>
                                            <td class="ps-id ps-info">-</td>
                                        </tr>
                                        <tr>
                                            <td>Department</td>
                                            <td class="ps-department ps-info">-</td>
                                        </tr>
                                        <tr>
                                            <td>Position</td>
                                            <td class="ps-position ps-info">-</td>
                                        </tr>
                                        <tr>
                                            <td>Status</td>
                                            <td class="ps-status ps-info">-</td>
                                        </tr>
                                    </table>
                                </td>
                                <td style="width: 50%;">
                                    <table class="ps-details" style="width: 100%;">
                                        <tr>
                                            <td colspan="2" class="ps-heading">Payroll Information</td>
                                        </tr>
                                        <tr>
                                            <td>Pay Date</td>
                                            <td class="ps-pay-date ps-info">-</td>
                                        </tr>
                                        <tr>
                                            <td>Pay Type</td>
                                            <td class="ps-pay-type ps-info">-</td>
                                        </tr>
                                        <tr>
                                            <td>Period</td>
                                            <td class="ps-pay-period ps-info">-</td>
                                        </tr>
                                        <tr>
                                            <td>Total Worked Days</td>
                                            <td class="ps-pay-wd ps-info">-</td>
                                        </tr>
                                        <tr>
                                            <td>Net Pay</td>
                                            <td class="ps-netpay ps-info">-</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <table class="ps-earnings" style="width: 100%;">
                            <tr>
                                <td class="ps-heading">Earnings</td>
                                <td class="ps-heading ps-rt">Monthly Rate</td>
                                <td class="ps-heading ps-rt">Daily Rate</td>
                                <td class="ps-heading ps-rt">Hourly Rate</td>
                                <td class="ps-heading ps-rt">Days</td>
                                <td class="ps-heading ps-rt">Hours</td>
                                <td class="ps-heading ps-rt">Total</td>
                            </tr>
                            <tr>
                                <td class="ps-val">Regular Earnings</td>
                                <td class="ps-r-mr ps-val ps-rt">-</td>
                                <td class="ps-r-dr ps-val ps-rt">-</td>
                                <td class="ps-r-hr ps-val ps-rt">-</td>
                                <td class="ps-r-dys ps-val ps-rt">-</td>
                                <td class="ps-r-hrs ps-val ps-rt">-</td>
                                <td class="ps-r-ttl ps-val ps-rt">-</td>
                            </tr>
                            <tr>
                                <td class="ps-val">Leaves</td>
                                <td class="ps-l-mr ps-val ps-rt">-</td>
                                <td class="ps-l-dr ps-val ps-rt">-</td>
                                <td class="ps-l-hr ps-val ps-rt">-</td>
                                <td class="ps-l-dys ps-val ps-rt">-</td>
                                <td class="ps-l-hrs ps-val ps-rt">-</td>
                                <td class="ps-l-ttl ps-val ps-rt">-</td>
                            </tr>
                            <tr>
                                <td class="ps-val">Overtime</td>
                                <td class="ps-o-mr ps-val ps-rt">-</td>
                                <td class="ps-o-dr ps-val ps-rt">-</td>
                                <td class="ps-o-hr ps-val ps-rt">-</td>
                                <td class="ps-o-dys ps-val ps-rt">-</td>
                                <td class="ps-o-hrs ps-val ps-rt">-</td>
                                <td class="ps-o-ttl ps-val ps-rt">-</td>
                            </tr>
                            <tr>
                                <td class="ps-val">Allowances</td>
                                <td class="ps-a-mr ps-val ps-rt">-</td>
                                <td class="ps-a-dr ps-val ps-rt">-</td>
                                <td class="ps-a-hr ps-val ps-rt">-</td>
                                <td class="ps-a-dys ps-val ps-rt">-</td>
                                <td class="ps-a-hrs ps-val ps-rt">-</td>
                                <td class="ps-a-ttl ps-val ps-rt">-</td>
                            </tr>
                            <tr>
                                <td class="ps-val">Holiday</td>
                                <td class="ps-h-mr ps-val ps-rt">-</td>
                                <td class="ps-h-dr ps-val ps-rt">-</td>
                                <td class="ps-h-hr ps-val ps-rt">-</td>
                                <td class="ps-h-dys ps-val ps-rt">-</td>
                                <td class="ps-h-hrs ps-val ps-rt">-</td>
                                <td class="ps-h-ttl ps-val ps-rt">-</td>
                            </tr>
                            <tr class="deduct">
                                <td class="ps-val">Tardiness Deductions</td>
                                <td class="ps-t-mr ps-val ps-rt">-</td>
                                <td class="ps-t-dr ps-val ps-rt">-</td>
                                <td class="ps-t-hr ps-val ps-rt">-</td>
                                <td class="ps-t-dys ps-val ps-rt">-</td>
                                <td class="ps-t-hrs ps-val ps-rt">-</td>
                                <td class="ps-t-ttl ps-val ps-rt">-</td>
                            </tr>
                            <tr class="total-footer">
                                <td class="ps-val" colspan="6" style="text-align:right;">Total Earnings</td>
                                <td class="ps-earning ps-val ps-rt">-</td>
                            </tr>
                            <tr>
                                <td class="ps-heading" colspan="6">Benefits</td>
                                <td class="ps-heading ps-rt">Total</td>
                            </tr>
                            <tr>
                                <td class="ps-val" colspan="6">SSS</td>
                                <td class="ps-d-sss ps-val ps-rt">-</td>
                            </tr>
                            <tr>
                                <td class="ps-val" colspan="6">Philhealth</td>
                                <td class="ps-d-ph ps-val ps-rt">-</td>
                            </tr>
                            <tr>
                                <td class="ps-val" colspan="6">Pag-ibig</td>
                                <td class="ps-d-pg ps-val ps-rt">-</td>
                            </tr>
                            <tr>
                                <td class="ps-heading" colspan="7">Other Deductions</td>
                            </tr>
                            <tr>
                                <td class="ps-val" colspan="6">Withholding Tax</td>
                                <td class="ps-d-wt ps-val ps-rt">-</td>
                            </tr>
                            <tr>
                                <td class="ps-val" colspan="6">Cash Advance</td>
                                <td class="ps-d-ca ps-val ps-rt">-</td>
                            </tr>
                            <tr class="total-footer">
                                <td class="ps-val" colspan="6" style="text-align:right;">Total Deductions</td>
                                <td class="ps-ttl-d ps-val ps-rt">-</td>
                            </tr>
                            <tr class="total-footer td-net">
                                <td class="ps-val" colspan="6" style="text-align:right;">Net Pay</td>
                                <td class="ps-netpay ps-val ps-rt">-</td>
                            </tr>
                        </table>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@endsection

@section('scripts')
<script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="/js/backend/pages/payroll/transaction/payrun.js"></script>
@endsection

@section('styles')
    <link rel="stylesheet" href="/css/custom/payrun.css">
@endsection
