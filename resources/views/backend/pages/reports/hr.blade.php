@extends('backend.master.index')

@section('title', 'HR REPORTS')

@section('breadcrumbs')
    <span>REPORTS</span> / <span class="highlight">HR REPORTS</span>
@endsection

@section('styles')
<style>
    .reports-btn {
        background-color: #1f4c8f !important;
        border-color: #1f4c8f !important;
        color: #fff !important;
        border-radius: 10px !important;
        padding: 0.35rem 0.75rem;
    }
    .reports-btn:hover,
    .reports-btn:focus {
        background-color: #163968 !important;
        border-color: #163968 !important;
        color: #fff !important;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">HR REPORTS</h5>
                <a href="/reports" class="btn btn-sm reports-btn">Back to Reports</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead>
                            <tr>
                                <th style="width: 34%;">Report Name</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <a href="/reports/hr/employee-masterfile" target="_blank"><strong>Employee Masterfile Report</strong></a>
                                </td>
                                <td>List of employees with basic information and printable format.</td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="/reports/hr/employee-compensation-details"><strong>Employee Compensation Details</strong></a>
                                </td>
                                <td>Employee compensation and salary details report.</td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="/reports/hr/leave-balance"><strong>Leave Balance Report</strong></a>
                                </td>
                                <td>Employee leave entitlement, used days, and remaining balance.</td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="/reports/hr/employee-attendance"><strong>Employee Attendance Report</strong></a>
                                </td>
                                <td>Attendance summary by employee with days present, total hours, late hours, and undertime.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
