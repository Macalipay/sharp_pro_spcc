@extends('backend.master.index')

@section('title', 'EMPLOYEE MASTERFILE')

@section('breadcrumbs')
    <span class="highlight">EMPLOYEE MASTERFILE</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <table id="employee_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="employee_form">
    <div class="sc-modal-dialog sc-full-view">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('employee_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="employeeForm" class="form-record" style="height: 100%;">
                @csrf
                <div class="profile-body" style="height: 100%;">
                    <div class="profile-menu">
                        <ul class="profile-tab-list">
                            <li><a href="#" id="basicInformation" class="active" data-group="employee" data-url="employee-profile">BASIC INFORMATION</a></li>
                            <li><a href="#" id="educationalBackground" data-group="educational" data-url="educational-background">EDUCATIONAL BACKGROUND</a></li>
                            <li><a href="#" id="workCalendar" data-group="work-calendar" data-url="work-calendar">WORK CALENDAR</a></li>
                            <li><a href="#" id="compensation" data-group="compensation" data-url="compensation">COMPENSATION SUMMARY</a></li>
                            <li><a href="#" id="taxBenefits" data-group="tax-benefits" data-url="tax-benefits">COMPENSATION HISTORY </a></li>
                            <li><a href="#" id="workHistory" data-group="work-history" data-url="work-history">WORK HISTORY</a></li>
                            <li><a href="#" id="certification" data-group="certification" data-url="certification">CERTIFICATION</a></li>
                            <li><a href="#" id="training" data-group="training" data-url="training">TRAINING</a></li>
                            <li><a href="#" id="biodata" data-group="biodata" data-url="biodata">BIODATA</a></li>
                            <li><a href="#" id="clearance" data-group="clearance" data-url="clearance">CLEARANCE</a></li>
                            <li><a href="#" id="movement" data-group="movement" data-url="employee-movement">MOVEMENT</a></li>
                            <li><a href="#" id="auditTrail" data-group="audit-trail" data-url="employee-profile">AUDIT TRAIL</a></li>
                        </ul>
                    </div>
                    <div class="profile-content" style="height: 100%;">
                        <div>
                            @include('backend.pages.employee.content.basic_information')
                            @include('backend.pages.employee.content.educational_background')
                            @include('backend.pages.employee.content.work_calendar')
                            @include('backend.pages.employee.content.compensation')
                            @include('backend.pages.employee.content.tax_benefits')
                            @include('backend.pages.employee.content.work_history')
                            @include('backend.pages.employee.content.certification')
                            @include('backend.pages.employee.content.training')
                            @include('backend.pages.employee.content.biodata')
                            @include('backend.pages.employee.content.clearance')
                            @include('backend.pages.employee.content.movement')
                            @include('backend.pages.employee.content.audit_trail')
                        </div>
                    </div>
                    <div class="profile-user-info" style="height: 100%;">
                        <div class="info-container">
                            <table>
                                <tr>
                                    <td class="tbl-lbl">EMPLOYEE NO.:</td>
                                    <td class="tbl-val" id="t_emp_no">-</td>
                                </tr>
                                <tr>
                                    <td class="tbl-lbl">FULL NAME:</td>
                                    <td class="tbl-val" id="t_full_name">-</td>
                                </tr>
                                <tr>
                                    <td class="tbl-lbl">HIRE DATE.:</td>
                                    <td class="tbl-val" id="t_hire_date">-</td>
                                </tr>
                                <tr>
                                    <td class="tbl-lbl">POSITION:</td>
                                    <td class="tbl-val" id="t_position">-</td>
                                </tr>
                                <tr>
                                    <td class="tbl-lbl">STATUS:</td>
                                    <td class="tbl-val" id="t_status">-</td>
                                </tr>
                            </table>
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
@endsection

@endsection

@section('scripts')
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="/js/backend/pages/employee/employee_list.js"></script>
@endsection


@section('styles-2')
    <link href="{{asset('/css/custom/employee.css')}}" rel="stylesheet">
    <link href="{{asset('/css/custom/cv.css')}}" rel="stylesheet">
@endsection
