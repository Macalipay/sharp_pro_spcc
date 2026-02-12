@extends('backend.master.index')

@section('title', 'EMPLOYEE DASHBOARD')

@section('breadcrumbs')
    <span>EMPLOYEE / EMPLOYEE DASHBOARD</span> / <span class="highlight">OVERVIEW</span>
@endsection

@section('left-content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Notifications</h5>
            </div>
            <div class="col-12">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <p class="notif-title">Missing Requirement</p>
                            <p class="notif-description">Please submit your missing requirement.</p>
                            <button type="button" class="btn btn-primary">Update Requirements</button>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <p class="notif-title">Incomplete Timesheet</p>
                            <p class="notif-description">Complete your timesheet to avoid payroll delay.</p>
                            <button type="button" class="btn btn-primary">View Timesheet</button>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <p class="notif-title-green">Holiday Announcement</p>
                            <p class="notif-description">Happy Labor Day! There will be no work on May 1, 2023 - Monday. Have a great holiday.</p>
                        </div>
                    </div>
                    <hr>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Overview</h5>
            </div>
            <div class="col-12">
                <div class="card-body">
                    <label class="section-label">United Laboratories, Inc.</label>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="job-title">REGISTERED PROFESSIONAL NURSE</p>
                            <p class="employment-status">Regular Employee</p>
                        </div>
                        <div class="col-md-6">
                            <p class="payroll-title">Current Payroll</p>
                            <p class="payroll-date">May 1, 2023 (Mon) - May 15, 2023 (Wed)</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="section-title">Hours Worked Today</p>
                            <p class="section-desc">0 Hours, 12 Minutes</p>
                        </div>
                        <div class="col-md-6">
                            <p class="section-title">Today's Earnings</p>
                            <p class="section-desc">PHP 18.27</p>
                        </div>
                        <div class="col-md-6">
                            <p class="section-title">Hourly Rate</p>
                            <p class="section-desc">PHP 86.21</p>
                        </div>
                        <div class="col-md-6">
                            <p class="section-title">Monthly Rate</p>
                            <p class="section-desc">PHP 15,000.00</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <label class="section-label">Week Schedule (May 1, 2023 - May 5, 2023)</label>
                        </div>
                        <div class="col-md-12">
                        <table class="wks">
                            <tr>
                                <th>Date</th>
                                <th>Time Schedule</th>
                            </tr>
                            <tr>
                                <td>May 1, 2023 - Monday</td>
                                <td>8:00 AM - 5:00 PM</td>
                            </tr>
                            <tr>
                                <td>May 2, 2023 - Tuesday</td>
                                <td>8:00 AM - 5:00 PM</td>
                            </tr>
                            <tr>
                                <td>May 3, 2023 - Wednesday</td>
                                <td>8:00 AM - 5:00 PM</td>
                            </tr>
                            <tr>
                                <td>May 4, 2023 - Thursday</td>
                                <td>10:00 AM - 7:00 PM</td>
                            </tr>
                            <tr>
                                <td>May 5, 2023 - Friday</td>
                                <td>8:00 AM - 5:00 PM</td>
                            </tr>
                        </table>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <label class="section-label">Daily Time Logs/Records</label>
                        </div>
                        <div class="col-md-12">
                        <table class="dtl">
                            <tr>
                                <th>Date</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                            </tr>
                            <tr>
                                <td>May 1, 2023 - Monday</td>
                                <td>8:00 AM</td>
                                <td>5:00 PM</td>
                            </tr>
                            <tr>
                                <td>May 2, 2023 - Tuesday</td>
                                <td>8:05 AM</td>
                                <td>5:43 PM</td>
                            </tr>
                            <tr>
                                <td>May 3, 2023 - Wednesday</td>
                                <td>7:49 AM</td>
                                <td>5:03 PM</td>
                            </tr>
                            <tr>
                                <td>May 4, 2023 - Thursday</td>
                                <td>ON-LEAVE</td>
                                <td>ON-LEAVE</td>
                            </tr>
                            <tr>
                                <td>May 5, 2023 - Friday</td>
                                <td>8:00 AM</td>
                                <td>5:02 PM</td>
                            </tr>
                        </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <div class="row">
                        <div class="col-md-6">
                            {{-- <button type="button" class="btn btn-primary">View Timelogs</button> --}}
                        </div>
                        <div class="col-md-6" style="text-align: right;">
                            <button type="button" class="btn btn-primary">Request for Overtime</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@section('right-content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Account Balance</h5>
                <h5 class="card-title"><span class="account-balance">PHP 18.27</span></h5>
            </div>
            <div class="col-12">
                <div class="card-body" style="padding: 0px !important;">
                    <div style="padding: 1em;"></div>
                    <table>
                        <tr>
                            <td>Payroll</td>
                            <td>PHP 18.27</td>
                        </tr>
                        <tr>
                            <td>Payroll Bonus</td>
                            <td>PHP 0.00</td>
                        </tr>
                        <tr>
                            <td>Performance Bonus</td>
                            <td>PHP 0.00</td>
                        </tr>
                    </table>
                    <div style="padding: 1em;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@endsection

@section('styles')
<style>
    .main {
        overflow-x: hidden;
    }
    .card-header {
        background: #e9e9e9;
    }
    h5.card-title {
        color: #3282b8;
    }
    p.section-title {
        font-size: 12px;
        color: #b6b6b6;
        margin-bottom: 0px;
    }
    p.notif-title {
        font-weight: bold;
        color: #e02828;
        font-size: 13px;
        margin-bottom: 0px;
    }
    p.notif-title-green {
        font-weight: bold;
        color: #28e04a;
        font-size: 13px;
        margin-bottom: 0px;
    }
    p.notif-description {
        font-size: 10px;
        color: #b6b6b6;
    }
    p.section-desc {
        font-weight: bold;
        color: #6eafdb;
    }
    label.section-label {
        font-weight: bold;
    }
    .payroll-title {
        color: #b6b6b6;
        margin-bottom: 0px !important;
    }
    .payroll-date {
        color: #6eafdb;
        font-weight: bold;
    }
    .employment-status {
        color: #b6b6b6;
        font-size: 12px;
    }
    .job-title {
        font-weight: bold;
        color: #6eafdb;
        margin-bottom: 0px !important;
    }
    .account-balance {
        font-weight: bold;
        font-size: 20px;
    }
    table {
        border-collapse: collapse;
        width: 100%;
        font-size: 10px;
    }
    
    td, th {
        padding: 5px;
    }
    th {
        color: #6eafdb;
        font-weight: bold;
    }
    table.dtl tr:nth-child(even) {
        background: #d3d3d3;
    }
    table.dtl td, th {
        /* border: 1px solid #dddddd; */
        text-align: left;
        padding: 6px;
    }
    table.wks tr:nth-child(even) {
        background: #e7f5ff;
    }
    table.wks td, th {
        /* border: 1px solid #dddddd; */
        text-align: left;
        padding: 6px;
    }
  </style>
@endsection

@section('scripts')
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    {{-- <script src="/js/backend/pages/payroll/transaction/employee/leave_application.js"></script> --}}
@endsection