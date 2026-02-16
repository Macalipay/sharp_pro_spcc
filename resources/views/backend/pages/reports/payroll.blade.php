@extends('backend.master.index')

@section('title', 'PAYROLL REPORTS')

@section('breadcrumbs')
    <span>REPORTS</span> / <span class="highlight">PAYROLL REPORTS</span>
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
                <h5 class="card-title mb-0">PAYROLL REPORTS</h5>
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
                                    <a href="/reports/payroll/payroll-summary"><strong>Payroll Summary</strong></a>
                                </td>
                                <td>Summary report of payroll transactions.</td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="/reports/payroll/sss-contribution"><strong>SSS Contribution Report</strong></a>
                                </td>
                                <td>Employee SSS contribution report.</td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="/reports/payroll/philhealth-contribution"><strong>Philhealth Contribution Report</strong></a>
                                </td>
                                <td>Employee Philhealth contribution report.</td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="/reports/payroll/pagibig-contribution"><strong>PAG-IBIG Contribution</strong></a>
                                </td>
                                <td>Employee PAG-IBIG contribution report.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
