@extends('backend.master.index')

@section('title', 'DASHBOARD')

@section('breadcrumbs')
    <span>DASHBOARD</span> / <span class="highlight">OVERVIEW</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card metric-card metric-employees">
            <div class="card-body">
                <div class="metric-title">TOTAL NUMBER OF EMPLOYEES</div>
                <div class="metric-value">{{ number_format((int) ($totalEmployees ?? 0)) }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card metric-card metric-draft">
            <div class="card-body">
                <div class="metric-title">TOTAL PAYROLL ON DRAFT</div>
                <div class="metric-value">{{ number_format((int) ($totalPayrollDraft ?? 0)) }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card metric-card metric-approved">
            <div class="card-body">
                <div class="metric-title">TOTAL PAYROLL APPROVED</div>
                <div class="metric-value">{{ number_format((int) ($totalPayrollApproved ?? 0)) }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card metric-card metric-net-2024">
            <div class="card-body">
                <div class="metric-title">TOTAL NET PAID (2024)</div>
                <div class="metric-value">PHP {{ number_format((float) ($totalNetPaid2024 ?? 0), 2) }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card metric-card metric-net-2025">
            <div class="card-body">
                <div class="metric-title">TOTAL NET PAID (2025)</div>
                <div class="metric-value">PHP {{ number_format((float) ($totalNetPaid2025 ?? 0), 2) }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .metric-card {
        border: 0;
        color: #fff;
    }
    .metric-card .metric-title {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.2px;
    }
    .metric-card .metric-value {
        font-size: 32px;
        font-weight: 700;
        line-height: 1.1;
        margin-top: 8px;
    }
    .metric-employees {
        background: #1f4c8f;
    }
    .metric-draft {
        background: #c77d00;
    }
    .metric-approved {
        background: #0f8a5f;
    }
    .metric-net-2024 {
        background: #005f73;
    }
    .metric-net-2025 {
        background: #6b3f9b;
    }
</style>
@endsection
