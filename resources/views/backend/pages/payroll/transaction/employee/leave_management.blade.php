@extends('backend.master.index')

@section('title', 'LEAVE MANAGEMENT')

@section('breadcrumbs')
    <span>EMPLOYEE / LEAVE MANAGEMENT</span> / <span class="highlight">LEAVE INQUIRY</span>
@endsection

@section('left-content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Leave Balance</h5>
            </div>
            <div class="col-12">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <p class="notif-title">Vacation Leave</p>
                            <p class="notif-description-green">12/12</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <p class="notif-title">Sick Leave</p>
                            <p class="notif-description">10/12</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <p class="notif-title">Maternity Leave</p>
                            <p class="notif-description-green">30/30</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <p class="notif-title">Annual Leave</p>
                            <p class="notif-description-red">0/6</p>
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
                <h5 class="card-title">LEAVE MANAGEMENT: LEAVE HISTORY</h5>
            </div>
            @include('backend.partial.flash-message')
            <div class="col-12">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="section-label">Leave Logs/Records</label>
                        </div>
                        <div class="col-md-12">
                        <table class="dtl">
                            <tr>
                                <th>Leave Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Reason</th>
                                <th>Approver</th>
                            </tr>
                            <tr>
                                <td>ANNUAL LEAVE</td>
                                <td>May 1, 2023 - Monday</td>
                                <td>May 5, 2023 - Friday</td>
                                <td>Annual Checkup</td>
                                <td>HR - Janica Adolfo</td>
                            </tr>
                            <tr>
                                <td>ANNUAL LEAVE</td>
                                <td>May 1, 2023 - Monday</td>
                                <td>May 5, 2023 - Friday</td>
                                <td>Annual Checkup</td>
                                <td>HR - Janica Adolfo</td>
                            </tr>
                            <tr>
                                <td>ANNUAL LEAVE</td>
                                <td>May 1, 2023 - Monday</td>
                                <td>May 5, 2023 - Friday</td>
                                <td>Annual Checkup</td>
                                <td>HR - Janica Adolfo</td>
                            </tr>
                            <tr>
                                <td>ANNUAL LEAVE</td>
                                <td>May 1, 2023 - Monday</td>
                                <td>May 5, 2023 - Friday</td>
                                <td>Annual Checkup</td>
                                <td>HR - Robert Green</td>
                            </tr>
                            <tr>
                                <td>VACATION LEAVE</td>
                                <td>January 1, 2023 - Monday</td>
                                <td>January 5, 2023 - Friday</td>
                                <td>Celebrating a family member's birthday</td>
                                <td>HR - Ricky Pami</td>
                            </tr>
                        </table>
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
                <h5 class="card-title">Leave Application</h5>
            </div>
            <div class="col-12">
                <div class="card-body" style="padding: 0px !important;">
                    <div style="padding: 1em;"></div>
                    <table class="leave-appication">
                        <tr>
                            <th>Leave Date</th>
                            <th>Status</th>
                        </tr>
                        <tr>
                            <td>Jan 20, 2023</td>
                            <td style="color: #ffc300;">Pending</td>
                        </tr>
                        <tr>
                            <td>Feb 14, 2023</td>
                            <td style="color: #ffc300;">Pending</td>
                        </tr>
                    </table>
                    <div style="padding: 1em;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('sc-modal')
@parent
{{-- <div class="sc-modal-content" id="earnings_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('earnings_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="earningForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12 leave_type">
                        <label>LEAVE TYPE</label>
                        <select name="leave_type" id="leave_type" class="form-control">
                            <option value="0">SICK LEAVE</option>
                            <option value="0">VACATION LEAVE</option>
                            <option value="0">MATERNITY LEAVE</option>
                            <option value="0">BIRTHDAY LEAVE</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6 credits">
                        <label>Number of Credits</label>
                        <input type="number" class="form-control" id="credits" name="credits"/>
                    </div>
                    <div class="form-group col-md-12 reason">
                        <label>Reason for Request:</label>
                        <input type="textarea" class="form-control" id="reason" name="reason"/>
                    </div>
                    
                </div>
            </form>
        </div>
    </div>
</div> --}}
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
        color: #bbbbbb;
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
        font-size: 20px;
        color: #6eafdb;
        font-weight: bold;
    }
    p.notif-description-green {
        font-size: 20px;
        color: #28e04a;
        font-weight: bold;
    }
    p.notif-description-red {
        font-size: 20px;
        color: #e31919;
        font-weight: bold;
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
    {{-- <script src="/js/backend/pages/payroll/transaction/employee/leave_monetization.js"></script> --}}
@endsection