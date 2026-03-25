@extends('backend.master.index')

@section('title', 'TIME LOGS SCREEN')

@section('breadcrumbs')
    <span>TRANSACTION / TIMEKEEPING</span> / <span class="highlight">TIME LOGS</span>
@endsection

@section('content')
<div class="row time-logs-screen">
    <div class="col-md-12">
        <div class="time-logs-filter-shell">
            <input type="hidden" name="date-filter" id="date-filter">
            <div class="row time-logs-filter-grid">
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="form-group mb-0">
                        <label for="department">Department</label>
                        <select name="department" id="department" class="form-control form-control-sm">
                            <option value="all">ALL DEPARTMENT</option>
                            @foreach ($departments as $department)
                                <option value="{{$department->id}}">{{$department->description}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="form-group mb-0">
                        <label for="payroll-group-filter">Payroll Group</label>
                        <select name="payroll-group-filter" id="payroll-group-filter" class="form-control form-control-sm">
                            <option value="all">ALL PAYROLL GROUPS</option>
                            <option value="fixed_rate">FIXED RATE</option>
                            <option value="daily_rate">DAILY RATE</option>
                            <option value="monthly_rate">MONTHLY RATE</option>
                        </select>
                    </div>
                </div>
                <div class="col-xl-2 col-lg-6 col-md-6">
                    <div class="form-group mb-0">
                        <label for="start-date-filter">Start Date</label>
                        <input type="date" class="form-control form-control-sm" name="start-date-filter" id="start-date-filter"/>
                    </div>
                </div>
                <div class="col-xl-2 col-lg-6 col-md-6">
                    <div class="form-group mb-0">
                        <label for="end-date-filter">End Date</label>
                        <input type="date" class="form-control form-control-sm" name="end-date-filter" id="end-date-filter"/>
                    </div>
                </div>
                <div class="col-xl-2 col-lg-12 col-md-12">
                    <div class="time-logs-filter-actions">
                        <button type="button" id="sync-time-log-data" class="btn btn-sm btn-light mr-2">Sync Data</button>
                        <button type="button" id="clear-time-log-filters" class="btn btn-sm btn-light">Clear Filters</button>
                    </div>
                </div>
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
                <div class="col-12 details-panel">
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
                <div class="col-12 text-right mt-3">
                    <button class="btn btn-light btn-sm edit-btn" onclick="editTimesheet()">EDIT TIMESHEET</button>
                    <button class="btn btn-danger btn-sm btn-cancel" style="display:none;" onclick="cancelTimesheet()">CANCEL</button>
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
                Are you sure that you want to approve this timesheet for <span class="sequence"></span>?
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
