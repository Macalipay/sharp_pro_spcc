@extends('backend.master.index')

@section('title', 'JOURNAL ENTRY DATE IMPACT REPORT')

@section('breadcrumbs')
    <span>REPORTS</span> / <span>ACCOUNTING REPORTS</span> / <span class="highlight">JOURNAL ENTRY DATE IMPACT REPORT</span>
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
    .impact-table {
        font-size: 11px;
    }
    .impact-table th,
    .impact-table td {
        white-space: nowrap;
        vertical-align: middle;
        padding: 0.4rem 0.5rem;
    }
    .impact-table th {
        text-align: center;
        font-weight: 700;
        background-color: #1f4c8f;
        color: #ffffff;
        border-color: #1f4c8f;
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
@php
    $fmt = function ($value) {
        $v = (float) $value;
        return $v < 0 ? '(' . number_format(abs($v), 2) . ')' : number_format($v, 2);
    };
@endphp
<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">JOURNAL ENTRY DATE IMPACT REPORT</h5>
                <a href="/reports/accounting" class="btn btn-sm reports-btn">Back to Accounting Reports</a>
            </div>
            <div class="card-body">
                <form method="GET" action="/reports/accounting/journal-entry-impact" class="mb-2">
                    <input type="hidden" name="coa_page" value="{{ request('coa_page', 1) }}">
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
                            <label for="status" class="mb-1 d-block">Journal Status</label>
                            <select id="status" name="status" class="form-control form-control-sm">
                                <option value="">All</option>
                                <option value="DRAFT" {{ strtoupper((string) request('status')) === 'DRAFT' ? 'selected' : '' }}>Draft</option>
                                <option value="POSTED" {{ strtoupper((string) request('status')) === 'POSTED' ? 'selected' : '' }}>Posted</option>
                                <option value="VOIDED" {{ strtoupper((string) request('status')) === 'VOIDED' ? 'selected' : '' }}>Voided</option>
                            </select>
                        </div>
                        <div>
                            <label for="date_from" class="mb-1 d-block">Posting Date From</label>
                            <input type="date" id="date_from" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                        </div>
                        <div>
                            <label for="date_to" class="mb-1 d-block">Posting Date To</label>
                            <input type="date" id="date_to" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                        </div>
                        <div class="d-flex align-items-end">
                            <button type="submit" class="btn btn-sm reports-btn mr-2">Filter</button>
                            <a href="/reports/accounting/journal-entry-impact" class="btn btn-sm btn-light">Reset</a>
                        </div>
                    </div>
                </form>

                <h6 class="mb-2">Journal Entries by Date and Affected Report</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0 impact-table">
                        <thead>
                            <tr>
                                <th>Posting Date</th>
                                <th>JE No.</th>
                                <th>Status</th>
                                <th>Account Code</th>
                                <th>Account Name</th>
                                <th>Account Type</th>
                                <th>Affected Report</th>
                                <th class="text-right">Debit</th>
                                <th class="text-right">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($journalImpactRows as $row)
                                <tr>
                                    <td>{{ $row->posting_date ?? '-' }}</td>
                                    <td>{{ $row->je_number ?? '-' }}</td>
                                    <td class="text-center">{{ strtoupper((string) ($row->journal_status ?? '-')) }}</td>
                                    <td>{{ $row->account_code ?? '-' }}</td>
                                    <td>{{ $row->account_name ?? '-' }}</td>
                                    <td>{{ $row->account_type ?? '-' }}</td>
                                    <td class="text-center">{{ $row->affected_report ?? '-' }}</td>
                                    <td class="text-right">{{ $fmt($row->debit_amount ?? 0) }}</td>
                                    <td class="text-right">{{ $fmt($row->credit_amount ?? 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No journal entry impact records found for the selected filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-2">
                    {{ $journalImpactRows->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Chart of Accounts Report Mapping</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0 impact-table">
                        <thead>
                            <tr>
                                <th>Account Code</th>
                                <th>Account Name</th>
                                <th>Account Type</th>
                                <th>Category</th>
                                <th>Primary Report</th>
                                <th>Normal Balance</th>
                                <th>Write Effect</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($coaImpactRows as $row)
                                <tr>
                                    <td>{{ $row->account_code ?? '-' }}</td>
                                    <td>{{ $row->account_name ?? '-' }}</td>
                                    <td>{{ $row->account_type ?? '-' }}</td>
                                    <td>{{ $row->category ?? '-' }}</td>
                                    <td class="text-center">{{ $row->primary_report ?? '-' }}</td>
                                    <td class="text-center">{{ $row->normal_balance ?? '-' }}</td>
                                    <td>{{ $row->write_effect ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No chart of account mapping records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-2">
                    {{ $coaImpactRows->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
