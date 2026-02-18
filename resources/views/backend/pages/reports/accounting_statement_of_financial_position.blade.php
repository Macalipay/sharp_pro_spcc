@extends('backend.master.index')

@section('title', 'STATEMENT OF FINANCIAL POSITION')

@section('breadcrumbs')
    <span>REPORTS</span> / <span>ACCOUNTING REPORTS</span> / <span class="highlight">STATEMENT OF FINANCIAL POSITION</span>
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
    .difference-note {
        margin-top: 8px;
        font-size: 10px;
        color: #6c757d;
        font-weight: 500;
    }
</style>
@endsection

@section('content')
@php
    $fmt = function ($value) {
        $v = (float) $value;
        return $v < 0 ? '(' . number_format(abs($v), 2) . ')' : number_format($v, 2);
    };
    $dd = function ($label, $amount, $params = []) use ($asAt) {
        $url = '/reports/accounting/drilldown?' . http_build_query(array_merge([
            'report' => 'bs',
            'as_at' => $asAt,
        ], $params));
        $v = (float) $amount;
        $formatted = $v < 0 ? '(' . number_format(abs($v), 2) . ')' : number_format($v, 2);
        return '<a href="' . e($url) . '" style="font-weight:700;">' . $formatted . '</a>';
    };
@endphp
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">STATEMENT OF FINANCIAL POSITION (AS AT {{ \Carbon\Carbon::parse($asAt)->format('M d, Y') }})</h5>
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
                <form method="GET" action="/reports/accounting/statement-of-financial-position" class="form-inline mb-3 not">
                    <label for="as_at" class="mr-2">As At</label>
                    <input type="date" id="as_at" name="as_at" class="form-control form-control-sm mr-2" value="{{ preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $asAt) ? $asAt : '' }}" required>
                    <button type="submit" class="btn btn-sm reports-btn mr-2">Filter</button>
                    <a href="/reports/accounting/statement-of-financial-position" class="btn btn-sm btn-light">Reset</a>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm report-table mb-0">
                        <thead>
                            <tr><th style="width:70%">Particulars</th><th class="text-right">Amount</th></tr>
                        </thead>
                        <tbody>
                            <tr><td class="fin-title-text">ASSETS</td><td></td></tr>
                            <tr><td class="fin-title-text">&nbsp;&nbsp;CURRENT ASSETS</td><td></td></tr>
                            @foreach($currentAssets as $row)
                                <tr><td>&nbsp;&nbsp;&nbsp;&nbsp;{{ $row->account_name }} ({{ $row->account_number }})</td><td class="text-right">{!! $dd($row->account_name, $row->display_balance, ['category' => 'ASSETS', 'account_id' => $row->id]) !!}</td></tr>
                            @endforeach
                            <tr><td class="fin-title-text">&nbsp;&nbsp;TOTAL CURRENT ASSETS</td><td class="text-right"><strong>{!! $dd('TOTAL CURRENT ASSETS', $totals['current_assets'], ['section' => 'current_assets']) !!}</strong></td></tr>

                            <tr><td class="fin-title-text">&nbsp;&nbsp;NON-CURRENT ASSETS</td><td></td></tr>
                            @foreach($nonCurrentAssets as $row)
                                <tr><td>&nbsp;&nbsp;&nbsp;&nbsp;{{ $row->account_name }} ({{ $row->account_number }})</td><td class="text-right">{!! $dd($row->account_name, $row->display_balance, ['category' => 'ASSETS', 'account_id' => $row->id]) !!}</td></tr>
                            @endforeach
                            <tr><td class="fin-title-text">&nbsp;&nbsp;TOTAL NON-CURRENT ASSETS</td><td class="text-right"><strong>{!! $dd('TOTAL NON-CURRENT ASSETS', $totals['non_current_assets'], ['section' => 'non_current_assets']) !!}</strong></td></tr>

                            <tr><td class="fin-title-text">TOTAL ASSETS</td><td class="text-right"><strong>{!! $dd('TOTAL ASSETS', $totals['total_assets'], ['category' => 'ASSETS']) !!}</strong></td></tr>

                            <tr><td class="fin-title-text">LIABILITIES</td><td></td></tr>
                            <tr><td class="fin-title-text">&nbsp;&nbsp;CURRENT LIABILITIES</td><td></td></tr>
                            @foreach($currentLiabilities as $row)
                                <tr><td>&nbsp;&nbsp;&nbsp;&nbsp;{{ $row->account_name }} ({{ $row->account_number }})</td><td class="text-right">{!! $dd($row->account_name, $row->display_balance, ['category' => 'LIABILITY', 'account_id' => $row->id]) !!}</td></tr>
                            @endforeach
                            <tr><td class="fin-title-text">&nbsp;&nbsp;TOTAL CURRENT LIABILITIES</td><td class="text-right"><strong>{!! $dd('TOTAL CURRENT LIABILITIES', $totals['current_liabilities'], ['section' => 'current_liabilities']) !!}</strong></td></tr>

                            <tr><td class="fin-title-text">&nbsp;&nbsp;NON-CURRENT LIABILITIES</td><td></td></tr>
                            @foreach($nonCurrentLiabilities as $row)
                                <tr><td>&nbsp;&nbsp;&nbsp;&nbsp;{{ $row->account_name }} ({{ $row->account_number }})</td><td class="text-right">{!! $dd($row->account_name, $row->display_balance, ['category' => 'LIABILITY', 'account_id' => $row->id]) !!}</td></tr>
                            @endforeach
                            <tr><td class="fin-title-text">&nbsp;&nbsp;TOTAL NON-CURRENT LIABILITIES</td><td class="text-right"><strong>{!! $dd('TOTAL NON-CURRENT LIABILITIES', $totals['non_current_liabilities'], ['section' => 'non_current_liabilities']) !!}</strong></td></tr>
                            <tr><td class="fin-title-text">TOTAL LIABILITIES</td><td class="text-right"><strong>{!! $dd('TOTAL LIABILITIES', $totals['total_liabilities'], ['category' => 'LIABILITY']) !!}</strong></td></tr>

                            <tr><td class="fin-title-text">EQUITY</td><td></td></tr>
                            @foreach($equity as $row)
                                <tr><td>&nbsp;&nbsp;&nbsp;&nbsp;{{ $row->account_name }} ({{ $row->account_number }})</td><td class="text-right">{!! $dd($row->account_name, $row->display_balance, ['category' => 'EQUITY', 'account_id' => $row->id]) !!}</td></tr>
                            @endforeach
                            <tr><td class="fin-title-text">PLUS: NET PROFIT(LOSS)</td><td class="text-right"><strong>{!! $dd('NET PROFIT(LOSS)', $totals['net_profit'], ['report' => 'pl', 'from' => \Carbon\Carbon::parse($asAt)->startOfYear()->format('Y-m-d'), 'to' => $asAt]) !!}</strong></td></tr>
                            <tr><td class="fin-title-text">TOTAL EQUITY</td><td class="text-right"><strong>{!! $dd('TOTAL EQUITY', $totals['equity_total'], ['category' => 'EQUITY']) !!}</strong></td></tr>
                            <tr><td class="fin-title-text">TOTAL LIABILITIES + EQUITY</td><td class="text-right"><strong>{!! $dd('TOTAL LIABILITIES + EQUITY', $totals['total_liabilities_equity'], []) !!}</strong></td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="difference-note">
                    DIFFERENCE (ASSETS - LIABILITIES - EQUITY): {{ $fmt($totals['difference']) }}
                </div>
                <div class="difference-note" style="margin-top:4px;">
                    A/P CONTROL CHECK | LEDGER: {{ $fmt($apLedgerBalance ?? 0) }} | UNPAID BILLS: {{ $fmt($apUnpaidBalance ?? 0) }} | VARIANCE: {{ $fmt($apVariance ?? 0) }}
                </div>
                @if(abs((float)($apVariance ?? 0)) > 0.009)
                    <div class="alert alert-danger mt-2 mb-0">
                        Accounts Payable variance detected. Review Bills workflow posting before finalizing reports.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
