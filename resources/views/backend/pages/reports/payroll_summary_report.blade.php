@extends('backend.master.index')

@section('title', 'PAYROLL SUMMARY REPORT')

@section('breadcrumbs')
    <span>REPORTS</span> / <span>PAYROLL REPORTS</span> / <span class="highlight">PAYROLL SUMMARY</span>
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
    .payroll-summary-table {
        font-size: 11px;
    }
    .payroll-summary-table th,
    .payroll-summary-table td {
        white-space: nowrap;
        vertical-align: middle;
        padding: 0.4rem 0.5rem;
    }
    .payroll-summary-table th {
        text-align: center;
        font-weight: 700;
        background-color: #1f4c8f;
        color: #ffffff;
        border-color: #1f4c8f;
    }
    .period-link {
        color: #1f4c8f;
        font-weight: 600;
        text-decoration: none;
    }
    .sort-link {
        color: #ffffff !important;
        text-decoration: none;
        font-weight: 700;
        display: inline-block;
        width: 100%;
    }
    .sort-link:hover {
        text-decoration: underline;
    }
    .period-link:hover {
        text-decoration: underline;
    }
    .report-wide-row {
        margin-left: 0;
        margin-right: 0;
    }
    .report-wide-row .col-12 {
        padding-left: 12px;
        padding-right: 12px;
    }
    .report-wide-row .card-body {
        padding: 0.75rem;
    }
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(150px, 1fr));
        gap: 10px;
        align-items: end;
        margin-bottom: 12px;
    }
    @media (max-width: 992px) {
        .filter-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
    @media (max-width: 576px) {
        .filter-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="row report-wide-row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">PAYROLL SUMMARY REPORT</h5>
                <a href="/reports/payroll" class="btn btn-sm reports-btn">Back to Payroll Reports</a>
            </div>
            <div class="card-body">
                <form method="GET" action="/reports/payroll/payroll-summary" class="mb-2 not">
                    <input type="hidden" name="sort_col" value="{{ request('sort_col', 'period') }}">
                    <input type="hidden" name="sort_dir" value="{{ request('sort_dir', 'asc') }}">
                    <div class="filter-grid">
                        <div>
                            <label for="per_page" class="mb-1 d-block">Show Entries</label>
                            <select id="per_page" name="per_page" class="form-control form-control-sm">
                                @foreach([10, 15, 20, 25, 30] as $n)
                                    <option value="{{ $n }}" {{ (int) request('per_page', 10) === $n ? 'selected' : '' }}>{{ $n }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="payroll_status" class="mb-1 d-block">Payroll Status</label>
                            <select id="payroll_status" name="payroll_status" class="form-control form-control-sm">
                                <option value="">All</option>
                                <option value="draft" {{ request('payroll_status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="approved" {{ request('payroll_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            </select>
                        </div>
                        <div>
                            <label for="period_from" class="mb-1 d-block">Period Covered From</label>
                            <input type="date" id="period_from" name="period_from" class="form-control form-control-sm" value="{{ request('period_from') }}">
                        </div>
                        <div>
                            <label for="period_to" class="mb-1 d-block">Period Covered To</label>
                            <input type="date" id="period_to" name="period_to" class="form-control form-control-sm" value="{{ request('period_to') }}">
                        </div>
                        <div class="d-flex align-items-end">
                            <button type="submit" class="btn btn-sm reports-btn mr-2">Filter</button>
                            <a href="/reports/payroll/payroll-summary" class="btn btn-sm btn-light">Reset</a>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    @php
                        $currentSortCol = request('sort_col', 'period');
                        $currentSortDir = strtolower((string) request('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
                        $baseQuery = request()->except('page', 'sort_col', 'sort_dir');
                        $sortUrl = function ($col) use ($currentSortCol, $currentSortDir, $baseQuery) {
                            $nextDir = ($currentSortCol === $col && $currentSortDir === 'asc') ? 'desc' : 'asc';
                            return '/reports/payroll/payroll-summary?' . http_build_query(array_merge($baseQuery, [
                                'sort_col' => $col,
                                'sort_dir' => $nextDir,
                            ]));
                        };
                        $sortIcon = function ($col) use ($currentSortCol, $currentSortDir) {
                            if ($currentSortCol !== $col) {
                                return '';
                            }
                            return $currentSortDir === 'asc' ? ' ▲' : ' ▼';
                        };
                    @endphp
                    <table class="table table-bordered table-sm mb-0 payroll-summary-table">
                        <thead>
                            <tr>
                                <th><a class="sort-link" href="{{ $sortUrl('period') }}">Period Covered{!! $sortIcon('period') !!}</a></th>
                                <th><a class="sort-link" href="{{ $sortUrl('project_name') }}">Project Name{!! $sortIcon('project_name') !!}</a></th>
                                <th><a class="sort-link" href="{{ $sortUrl('payroll_status') }}">Payroll Status{!! $sortIcon('payroll_status') !!}</a></th>
                                <th class="text-right"><a class="sort-link text-right" href="{{ $sortUrl('net_pay') }}">Net Pay{!! $sortIcon('net_pay') !!}</a></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($summaries as $summary)
                                @php
                                    $formatDate = function ($value) {
                                        $raw = trim((string) $value);
                                        if ($raw === '' || $raw === '0000-00-00') {
                                            return null;
                                        }
                                        foreach (['Y-m-d', 'm/d/Y', 'm-d-Y', 'mdY'] as $fmt) {
                                            try {
                                                return \Carbon\Carbon::createFromFormat($fmt, $raw)->format('M d, Y');
                                            } catch (\Throwable $e) {
                                            }
                                        }
                                        try {
                                            return \Carbon\Carbon::parse($raw)->format('M d, Y');
                                        } catch (\Throwable $e) {
                                            return null;
                                        }
                                    };

                                    $periodCovered = '-';
                                    $start = $formatDate($summary->period_start ?? null);
                                    $end = $formatDate($summary->payroll_period ?? null);
                                    $payDate = $formatDate($summary->pay_date ?? null);

                                    if ($start && $end) {
                                        $periodCovered = $start . ' - ' . $end;
                                    } elseif ($end) {
                                        $periodCovered = $end;
                                    } elseif ($start) {
                                        $periodCovered = $start;
                                    } elseif ($payDate) {
                                        $periodCovered = $payDate;
                                    } elseif (preg_match('/-(\d{8})$/', (string) ($summary->sequence_no ?? ''), $m)) {
                                        $legacy = $formatDate($m[1]);
                                        if ($legacy) {
                                            $periodCovered = $legacy;
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <a class="period-link" href="/reports/payroll/payroll-summary/{{ $summary->id }}" target="_blank">{{ $periodCovered }}</a>
                                    </td>
                                    <td>{{ $summary->project_name ?? '-' }}</td>
                                    <td class="text-center">{{ $summary->payroll_status_label ?? '-' }}</td>
                                    <td class="text-right">{{ number_format((float) ($summary->total_net_pay ?? 0), 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No payroll summary records found for the selected filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-2">
                    {{ $summaries->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
