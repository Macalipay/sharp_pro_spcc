@extends('backend.master.index')

@section('title', 'STATEMENT OF CASH FLOWS')

@section('breadcrumbs')
    <span>REPORTS</span> / <span>ACCOUNTING REPORTS</span> / <span class="highlight">STATEMENT OF CASH FLOWS</span>
@endsection

@section('styles')
<style>
    .reports-btn { background-color:#1f4c8f!important; border-color:#1f4c8f!important; color:#fff!important; border-radius:10px!important; padding:.35rem .75rem; }
    .reports-btn:hover,.reports-btn:focus { background-color:#163968!important; border-color:#163968!important; color:#fff!important; }
    .report-table { font-size: 11px; }
    .report-table th,.report-table td { white-space: nowrap; padding:.35rem .5rem; vertical-align:middle; }
    .report-table th { background:#1f4c8f; color:#fff; border-color:#1f4c8f; text-align:center; }
    .fin-title-text {
        font-weight: 900;
        font-size: 12px;
        letter-spacing: 0.3px;
        color: #1f4c8f;
    }
</style>
@endsection

@section('content')
@php
    $fmt = function ($value) {
        $v = (float) $value;
        return $v < 0 ? '(' . number_format(abs($v), 2) . ')' : number_format($v, 2);
    };
@endphp
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">STATEMENT OF CASH FLOWS ({{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }})</h5>
                <div class="d-flex align-items-center">
                    <div class="btn-group mr-2">
                        <button type="button" class="btn btn-sm reports-btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Export</button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}">Export PDF</a>
                            <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}">Export Excel</a>
                        </div>
                    </div>
                    <a href="/reports/accounting" class="btn btn-sm reports-btn">Back to Accounting Reports</a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" class="form-inline mb-3">
                    <label for="start_date" class="mr-2">From</label>
                    <input type="date" id="start_date" name="start_date" class="form-control form-control-sm mr-2" value="{{ $startDate }}">
                    <label for="end_date" class="mr-2">To</label>
                    <input type="date" id="end_date" name="end_date" class="form-control form-control-sm mr-2" value="{{ $endDate }}">
                    <button type="submit" class="btn btn-sm reports-btn mr-2">Filter</button>
                    <a href="/reports/accounting/statement-of-cash-flows" class="btn btn-sm btn-light">Reset</a>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm report-table mb-0">
                        <thead>
                            <tr><th style="width:70%">Particulars</th><th class="text-right">Amount</th></tr>
                        </thead>
                        <tbody>
                            <tr><td class="fin-title-text">CASH FLOWS FROM OPERATING ACTIVITIES (INDIRECT)</td><td></td></tr>
                            <tr><td>&nbsp;&nbsp;Net Profit (Loss)</td><td class="text-right">{{ $fmt($netProfit) }}</td></tr>
                            <tr><td>&nbsp;&nbsp;Add: Non-cash Adjustment (Depreciation)</td><td class="text-right">{{ $fmt($depreciation) }}</td></tr>
                            <tr><td class="fin-title-text">NET CASH FROM OPERATING ACTIVITIES</td><td class="text-right"><strong>{{ $fmt($operatingActivities) }}</strong></td></tr>

                            <tr><td class="fin-title-text">CASH FLOWS FROM INVESTING ACTIVITIES</td><td class="text-right"><strong>{{ $fmt($investingActivities) }}</strong></td></tr>
                            <tr><td class="fin-title-text">CASH FLOWS FROM FINANCING ACTIVITIES</td><td class="text-right"><strong>{{ $fmt($financingActivities) }}</strong></td></tr>

                            <tr><td class="fin-title-text">NET INCREASE / (DECREASE) IN CASH</td><td class="text-right"><strong>{{ $fmt($netCashChange) }}</strong></td></tr>
                            <tr><td>Opening Cash (as at {{ \Carbon\Carbon::parse($openingDate)->format('M d, Y') }})</td><td class="text-right">{{ $fmt($openingCash) }}</td></tr>
                            <tr><td class="fin-title-text">CLOSING CASH</td><td class="text-right"><strong>{{ $fmt($closingCash) }}</strong></td></tr>
                        </tbody>
                    </table>
                </div>
                <small class="text-muted d-block mt-2">
                    Cash flow report is generated from posted journals and cash/bank account names containing “cash” or “bank”.
                </small>
            </div>
        </div>
    </div>
</div>
@endsection
