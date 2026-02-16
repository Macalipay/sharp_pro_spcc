@extends('backend.master.index')

@section('title', 'REPORTS')

@section('breadcrumbs')
    <span>REPORTS</span> / <span class="highlight">OVERVIEW</span>
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
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">PAYROLL REPORTS</h5>
            </div>
            <div class="card-body">
                <p class="mb-3">Payroll-related reports and summaries.</p>
                <a href="/reports/payroll" class="btn btn-sm reports-btn">Open Payroll Reports</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">ACCOUNTING REPORTS</h5>
            </div>
            <div class="card-body">
                <p class="mb-3">Accounting financial and ledger reports.</p>
                <a href="/reports/accounting" class="btn btn-sm reports-btn">Open Accounting Reports</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">HR REPORTS</h5>
            </div>
            <div class="card-body">
                <p class="mb-3">Human resources and employee reports.</p>
                <a href="/reports/hr" class="btn btn-sm reports-btn">Open HR Reports</a>
            </div>
        </div>
    </div>
</div>
@endsection
