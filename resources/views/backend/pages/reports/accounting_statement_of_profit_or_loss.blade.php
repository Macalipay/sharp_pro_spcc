@extends('backend.master.index')

@section('title', 'STATEMENT OF PROFIT OR LOSS')

@section('breadcrumbs')
    <span>REPORTS</span> / <span>ACCOUNTING REPORTS</span> / <span class="highlight">STATEMENT OF PROFIT OR LOSS</span>
@endsection

@section('styles')
<style>
    .reports-btn { background-color:#1f4c8f!important; border-color:#1f4c8f!important; color:#fff!important; border-radius:10px!important; padding:.35rem .75rem; }
    .reports-btn:hover,.reports-btn:focus { background-color:#163968!important; border-color:#163968!important; color:#fff!important; }
    .report-table { font-size: 11px; }
    .report-table th,.report-table td { white-space: nowrap; padding:.35rem .5rem; vertical-align:middle; }
    .report-table th { background:#1f4c8f; color:#fff; border-color:#1f4c8f; text-align:center; }
    .pl-title-text {
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
    $dd = function ($amount, $params = []) use ($startDate, $endDate) {
        $url = '/reports/accounting/drilldown?' . http_build_query(array_merge([
            'report' => 'pl',
            'from' => $startDate,
            'to' => $endDate,
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
                <h5 class="card-title mb-0">STATEMENT OF PROFIT OR LOSS ({{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }})</h5>
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
                <form method="GET" action="/reports/accounting/statement-of-profit-or-loss" class="form-inline mb-3 not">
                    <label for="start_date" class="mr-2">From</label>
                    <input type="date" id="start_date" name="start_date" class="form-control form-control-sm mr-2" value="{{ $startDate }}">
                    <label for="end_date" class="mr-2">To</label>
                    <input type="date" id="end_date" name="end_date" class="form-control form-control-sm mr-2" value="{{ $endDate }}">
                    <button type="submit" class="btn btn-sm reports-btn mr-2">Filter</button>
                    <a href="/reports/accounting/statement-of-profit-or-loss" class="btn btn-sm btn-light">Reset</a>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm report-table mb-0">
                        <thead>
                            <tr><th style="width:70%">Particulars</th><th class="text-right">Amount</th></tr>
                        </thead>
                        <tbody>
                            <tr><td class="pl-title-text">REVENUE</td><td></td></tr>
                            @foreach($revenueRows as $row)
                                <tr><td>&nbsp;&nbsp;{{ $row->account_name }} ({{ $row->account_number }})</td><td class="text-right">{!! $dd($row->amount, ['category' => 'REVENUE', 'account_id' => $row->id]) !!}</td></tr>
                            @endforeach
                            <tr><td><strong>Total Revenue</strong></td><td class="text-right"><strong>{!! $dd($totals['revenue'], ['category' => 'REVENUE']) !!}</strong></td></tr>

                            <tr><td><strong>LESS: COST OF SALES</strong></td><td></td></tr>
                            @foreach($costOfSalesRows as $row)
                                <tr><td>&nbsp;&nbsp;{{ $row->account_name }} ({{ $row->account_number }})</td><td class="text-right">{!! $dd($row->amount, ['category' => 'EXPENSES', 'account_id' => $row->id]) !!}</td></tr>
                            @endforeach
                            <tr><td class="pl-title-text">TOTAL COST OF SALES</td><td class="text-right"><strong>{!! $dd($totals['cost_of_sales'], ['section' => 'cost_of_sales']) !!}</strong></td></tr>

                            <tr><td class="pl-title-text">GROSS PROFIT</td><td class="text-right"><strong>{!! $dd($totals['gross_profit']) !!}</strong></td></tr>

                            <tr><td><strong>LESS: OPERATING EXPENSES</strong></td><td></td></tr>
                            @foreach($operatingExpenseRows as $row)
                                <tr><td>&nbsp;&nbsp;{{ $row->account_name }} ({{ $row->account_number }})</td><td class="text-right">{!! $dd($row->amount, ['category' => 'EXPENSES', 'account_id' => $row->id]) !!}</td></tr>
                            @endforeach
                            <tr><td class="pl-title-text">TOTAL OPERATING EXPENSES</td><td class="text-right"><strong>{!! $dd($totals['operating_expenses'], ['section' => 'operating_expenses']) !!}</strong></td></tr>
                            <tr><td class="pl-title-text">NET PROFIT(LOSS)</td><td class="text-right"><strong>{!! $dd($totals['net_profit']) !!}</strong></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
