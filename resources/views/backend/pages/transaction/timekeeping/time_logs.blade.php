@extends('backend.master.index')

@section('title', 'TIME LOGS SCREEN')

@section('breadcrumbs')
    <span>TRANSACTION / TIMEKEEPING</span> / <span class="highlight">TIME LOGS</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="row mt-2">
            <div class="col-2">
                <div class="form-group">
                    <select name="department" id="department" class="form-control form-control-sm" onchange="filter(this.value)">
                        <option value="all">ALL DEPARTMENT</option>
                        @foreach ($departments as $department)
                            <option value="{{$department->id}}">{{$department->description}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-2">
                <div class="form-group">
                    <input type="date" class="form-control form-control-sm" name="date-filter" id="date-filter" onchange="filter($('#department').val())"/>
                </div>
            </div>
            <div class="col-8 text-right">
                <button class="btn btn-sm btn-success" onclick="releasePayroll()">RELEASE PAYROLL SUMMARY</button>
            </div>
        </div>
        <table id="employee_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="time_logs_form">
    <div class="sc-modal-dialog sc-xl">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('time_logs_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="timeLogsForm" class="form-record">
                <div class="row">
                    <div class="col-6">
                        <h5>TIME LOGS</h5>
                    </div>
                    <div class="col-6 text-right">
                        <button class="btn btn-success crs">MATCH TIME LOGS</button>
                    </div>
                </div>
                <table id="time_plotting" class="table table-striped" style="width:100%"></table>
            </form>
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
                <div class="col-8">
                    <div class="row">
                        <div class="col-4">
                            <table>
                                <tr>
                                    <td class="td-label">TIMESHEET STATUS:</td>
                                    <td class="td-val"><span id='timesheet_status'>-</span></td>
                                </tr>
                                <tr>
                                    <td class="td-label">EMPLOYEE NAME:</td>
                                    <td class="td-val"><span id='employee_name'>-</span></td>
                                </tr>
                                <tr>
                                    <td class="td-label">EMPLOYEE NUMBER:</td>
                                    <td class="td-val"><span id='employee_number'>-</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-4">
                            <table>
                                <tr>
                                    <td class="td-label">TOTAL REGULAR HOURS:</td>
                                    <td class="td-val"><span id='total_regular_hours'>0</span></td>
                                </tr>
                                <tr>
                                    <td class="td-label">OVERTIME:</td>
                                    <td class="td-val"><span id='total_overtime'>0</span></td>
                                </tr>
                                <tr>
                                    <td class="td-label">TOTAL WORKING HOURS:</td>
                                    <td class="td-val"><span id='total_working_hours'>0</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-4">
                            <table>
                                <tr>
                                    <td class="td-label">MONTHLY SALARY:</td>
                                    <td class="td-val"><span id='monthly_salary'>0</span></td>
                                </tr>
                                <tr>
                                    <td class="td-label">DAILY SALARY:</td>
                                    <td class="td-val"><span id='daily_salary'>0</span></td>
                                </tr>
                                <tr>
                                    <td class="td-label">HOURLY RATE:</td>
                                    <td class="td-val"><span id='hourly_rate'>0</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
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
                                    <th>OFFICE HOURS</th>
                                    <th>BREAK TIME</th>
                                    <th>REGULAR HOURS</th>
                                    <th>APPROVED OVERTIME</th>
                                    <th>TOTAL WORKING HOURS</th>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="row">
                        <div class="col-12">
                            <div class="payrol-calc">
                                PAYROLL CALCULATION
                            </div>
                            <table id="payroll_calculation">
                                <thead>
                                    <th>PAY DATE</th>
                                    <th>PAY TYPE</th>
                                    <th>PERIOD</th>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td id="pay_date">-</td>
                                        <td id="pay_type">-</td>
                                        <td id="pay_period">-</td>
                                    </tr>
                                </tbody>
                                <thead>
                                    <th></th>
                                    <th>SEQUENCE #</th>
                                    <th>NET PAY</th>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>-</td>
                                        <td id="sequence_no">-</td>
                                        <td id="net_pay">-</td>
                                    </tr>
                                </tbody>
                            </table>

                            <hr>

                            <table id="payroll_rate_details">
                                <thead>
                                    <th>EARNINGS</th>
                                    <th class="text-left">DAILY RATE</th>
                                    <th class="text-left">HOURLY RATE</th>
                                    <th class="text-left">DAYS</th>
                                    <th class="text-left">HOURS</th>
                                    <th class="text-left">TOTAL</th>
                                </thead>
                                <tbody class="custom"></tbody>
                                <tbody class="holiday-container"></tbody>
                                <tbody class="allowance-container"></tbody>
                                <tbody class="custom-2"></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-right" style="width:90%">Total Earnings</td>
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
                                    <th>Benefits</th>
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
                <div class="col-12 text-right mt-3">
                    <button class="btn btn-light btn-sm edit-btn" onclick="editTimesheet()">EDIT TIMESHEET</button>
                    <button class="btn btn-danger btn-sm btn-cancel" style="display:none;" onclick="cancelTimesheet()">CANCEL</button>
                    <button class="btn btn-light btn-sm btn-allowance" onclick="addAllowance()">ADD ALLOWANCES</button>
                    <button class="btn btn-light btn-sm btn-deduction" onclick="addDeduction()">ADD DEDUCTIONS</button>
                    <button class="btn btn-success btn-sm approve-btn" onclick="approveTimesheet()">APPROVE TIMESHEET</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="payroll_summary_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('payroll_summary_form').hide('all', summaryClose)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="payroll_summaryForm" class="form-record">
                <div class="row">
                    <div class="col-md-12 mb-2">
                        <label for="pay_type">Pay Type</label>
                        <select name="pay_type" id="pay_type" class="form-control">
                            <option value="2">SEMI MONTHLY</option>
                        </select>
                    </div>
                    <div class="col-md-12 mb-2">
                        <label for="payroll_calendar">Payroll Calendar</label>
                        <select name="payroll_calendar" id="payroll_calendar" class="form-control" onchange="selectCalendar()"></select>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label for="pariod_start">Period Start</label>
                        <input type="date" class="form-control" id="period_start" name="period_start" disabled/>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label for="payroll_period">Payroll Period</label>
                        <input type="date" class="form-control" id="payroll_period" name="payroll_period" disabled/>
                    </div>
                    <div class="col-md-12 mb-2">
                        <label for="pay_date">Pay Date</label>
                        <input type="date" class="form-control" id="pay_date" name="pay_date" disabled/>
                    </div>
                    <div class="col-md-12 mb-2">
                        <div class="timesheet-approve">
                            <span class="count">0</span> Approved Timesheet
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

<div class="sc-modal-content" id="timesheet_approval_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('timesheet_approval_form').hide('', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="message-approval">
                Are you sure that you want to approve this timesheet 'Sequence No.:<span class="sequence"></span>' ?
            </div>
            <div class="approval-button text-right"><button class="btn btn-success" onclick="yesApprove()">YES</button> <button class="btn btn-light" onclick="scion.create.sc_modal('timesheet_approval_form').hide('', modalHideFunction)">NO</button></div>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="allowance_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('allowance_form').hide('', allowanceClose)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="payroll_summaryForm" class="form-record">
                <div class="row">
                    <div class="col-12 mb-2">
                        <label>Allowance</label>
                        <select name="allowance_id" id="allowance_id" class="form-control form-control-sm" onchange="getAllowanceAmount()">
                            <option value=""></option>
                        </select>
                    </div>
                    {{-- <div class="col-12 mb-2">
                        <label>Date</label>
                        <input type="date" name="date" id="date" class="form-control form-control-sm">
                    </div> --}}
                    <div class="col-6 mb-2">
                        <label>Amount</label>
                        <input type="number" name="amount" id="amount" value="0" class="form-control form-control-sm" oninput="countTotalAmount()">
                    </div>
                    <div class="col-6 mb-2">
                        <label>Number of Days</label>
                        <input type="number" name="days" id="days" class="form-control form-control-sm" oninput="countTotalAmount()">
                    </div>
                    <div class="col-12 mb-2">
                        <label>Total Amount</label>
                        <input type="number" name="total_amount" id="total_amount" class="form-control form-control-sm" disabled>
                    </div>
                </div>
            </form>
        </div>
        <div class="sc-modal-footer text-right">
            <button class="btn btn-sm btn-primary btn-sv" onclick="$('#sv').click()">SAVE</button>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="deduction_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('deduction_form').hide('', deductionClose)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="payroll_summaryForm" class="form-record">
                <div class="row">
                    <div class="col-12 mb-2">
                        <label>Deduction</label>
                        <select name="deduction_id" id="deduction_id" class="form-control form-control-sm">
                            <option value=""></option>
                            @foreach ($deductions as $item)
                                <option value="{{$item->id}}">{{$item->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 mb-2">
                        <label>Date</label>
                        <input type="date" name="deduction_date" id="deduction_date" class="form-control form-control-sm">
                    </div>
                    <div class="col-12 mb-2">
                        <label>Amount</label>
                        <input type="number" name="deduction_amount" id="deduction_amount" class="form-control form-control-sm">
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
    <script>const canDownload = @json($canDownload);</script>
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="/js/backend/pages/transaction/time_logs.js"></script>
@endsection

@section('styles-2')
    <link href="{{asset('/css/custom/time_logs.css')}}" rel="stylesheet">
@endsection
