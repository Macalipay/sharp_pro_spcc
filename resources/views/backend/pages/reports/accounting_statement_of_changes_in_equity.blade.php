@extends('backend.master.index')

@section('title', 'STATEMENT OF CHANGES IN EQUITY')

@section('breadcrumbs')
    <span>REPORTS</span> / <span>ACCOUNTING REPORTS</span> / <span class="highlight">STATEMENT OF CHANGES IN EQUITY</span>
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
                <h5 class="card-title mb-0">STATEMENT OF CHANGES IN EQUITY ({{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }})</h5>
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
                    <a href="/reports/accounting/statement-of-changes-in-equity" class="btn btn-sm btn-light">Reset</a>
                </form>

                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm report-table mb-0">
                        <thead>
                            <tr><th style="width:70%">Particulars</th><th class="text-right">Amount</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>Opening Equity (as at {{ \Carbon\Carbon::parse($openingDate)->format('M d, Y') }})</td><td class="text-right">{{ $fmt($openingEquity) }}</td></tr>
                            <tr><td>Add: Owner Contributions</td><td class="text-right">{{ $fmt($ownerContributions) }}</td></tr>
                            <tr><td>Add: Net Profit (Loss)</td><td class="text-right">{{ $fmt($netProfit) }}</td></tr>
                            <tr><td>Less: Drawings / Distributions</td><td class="text-right">{{ $fmt($drawings) }}</td></tr>
                            <tr><td>Other Equity Movements</td><td class="text-right">{{ $fmt($periodEquityMovement) }}</td></tr>
                            <tr><td class="fin-title-text">CLOSING EQUITY</td><td class="text-right"><strong>{{ $fmt($closingEquity) }}</strong></td></tr>
                        </tbody>
                    </table>
                </div>

                <h6 class="mb-2">Equity Movement Breakdown (Period)</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm report-table mb-0">
                        <thead>
                            <tr>
                                <th>Account Code</th>
                                <th>Account Name</th>
                                <th class="text-right">Movement</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($equityMovementRows as $row)
                                <tr>
                                    <td>{{ $row->account_number ?? '-' }}</td>
                                    <td>{{ $row->account_name ?? '-' }}</td>
                                    <td class="text-right">{{ $fmt($row->movement ?? 0) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center">No equity movement records for selected period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
