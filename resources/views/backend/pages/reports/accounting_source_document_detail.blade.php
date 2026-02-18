@extends('backend.master.index')

@section('title', 'SOURCE DOCUMENT DETAIL')

@section('breadcrumbs')
    <span>REPORTS</span> / <span>ACCOUNTING REPORTS</span> / <span class="highlight">SOURCE DOCUMENT DETAIL</span>
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
                <h5 class="mb-0">Source Document: {{ $type }} #{{ $id }}</h5>
                <a href="/reports/accounting" class="btn btn-sm btn-primary">Back</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Posting Date</th>
                                <th>Journal Entry</th>
                                <th>Account</th>
                                <th>Line Detail</th>
                                <th class="text-right">Debit</th>
                                <th class="text-right">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lines as $line)
                                <tr>
                                    <td>{{ optional($line->journal_entry)->entry_date }}</td>
                                    <td>
                                        <a href="/reports/accounting/journal-entry/{{ $line->journal_entry_id }}" target="_blank">
                                            {{ optional($line->journal_entry)->reference_number ?: ('JE-' . $line->journal_entry_id) }}
                                        </a>
                                    </td>
                                    <td>{{ optional($line->chart_of_account)->account_name }} ({{ optional($line->chart_of_account)->account_number }})</td>
                                    <td>{{ $line->description ?: '-' }}</td>
                                    <td class="text-right">{{ $fmt($line->debit_amount) }}</td>
                                    <td class="text-right">{{ $fmt($line->credit_amount) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center">No records found for this source document key.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
