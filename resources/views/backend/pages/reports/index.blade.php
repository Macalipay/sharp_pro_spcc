@extends('backend.master.index')

@section('title', 'REPORTS')

@section('breadcrumbs')
    <span>REPORTS</span> / <span class="highlight">OVERVIEW</span>
@endsection

@section('styles')
<style>
    .reports-overview-card {
        height: 100%;
        border-radius: 22px;
        border: 1px solid rgba(154, 23, 41, 0.08);
        background: linear-gradient(180deg, #ffffff 0%, #fbfbfd 100%);
        box-shadow: 0 16px 34px rgba(91, 24, 35, 0.08);
        overflow: hidden;
    }

    .reports-overview-card .card-header {
        padding: 16px 20px;
        background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
        border-bottom: 1px solid rgba(30, 99, 214, 0.1);
    }

    .reports-overview-card .card-title {
        color: #2f3541;
        font-size: 15px;
        font-weight: 800;
        letter-spacing: 0.01em;
    }

    .reports-overview-card .card-body {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 18px;
        padding: 18px 20px 20px;
    }

    .reports-overview-card .card-body p {
        margin: 0;
        color: #434954;
        font-size: 13px;
        line-height: 1.55;
    }

    .reports-btn {
        min-height: 44px;
        padding: 0 18px;
        border-radius: 14px !important;
        border: 1px solid rgba(30, 99, 214, 0.22) !important;
        background: linear-gradient(180deg, #ffffff 0%, #f5f9ff 100%) !important;
        color: #1f3558 !important;
        box-shadow: 0 10px 22px rgba(24, 62, 115, 0.08);
        font-size: 13px !important;
        font-weight: 700 !important;
        letter-spacing: 0.01em;
        text-transform: none !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none !important;
        white-space: nowrap;
    }

    .reports-btn:hover,
    .reports-btn:focus {
        background: linear-gradient(180deg, #ffffff 0%, #edf4ff 100%) !important;
        border-color: rgba(30, 99, 214, 0.3) !important;
        color: #183f7b !important;
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(24, 62, 115, 0.12);
    }

    @media (max-width: 991.98px) {
        .reports-overview-card {
            margin-bottom: 14px;
        }

        .reports-btn {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="card reports-overview-card">
            <div class="card-header">
                <h5 class="card-title mb-0">PAYROLL REPORTS</h5>
            </div>
            <div class="card-body">
                <p class="mb-3">Payroll-related reports and summaries.</p>
                <a href="/reports/payroll" class="btn btn-sm reports-btn">Open Payroll Reports</a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card reports-overview-card">
            <div class="card-header">
                <h5 class="card-title mb-0">ACCOUNTING REPORTS</h5>
            </div>
            <div class="card-body">
                <p class="mb-3">Accounting financial and ledger reports.</p>
                <a href="/reports/accounting" class="btn btn-sm reports-btn">Open Accounting Reports</a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card reports-overview-card">
            <div class="card-header">
                <h5 class="card-title mb-0">HR REPORTS</h5>
            </div>
            <div class="card-body">
                <p class="mb-3">Human resources and employee reports.</p>
                <a href="/reports/hr" class="btn btn-sm reports-btn">Open HR Reports</a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card reports-overview-card">
            <div class="card-header">
                <h5 class="card-title mb-0">KPI DASHBOARD</h5>
            </div>
            <div class="card-body">
                <p class="mb-3">Executive construction profitability, cash flow, WIP, backlog, and cost analytics.</p>
                <a href="/reports/accounting/construction-financial-dashboard" class="btn btn-sm reports-btn">Open Dashboard</a>
            </div>
        </div>
    </div>
</div>
@endsection
