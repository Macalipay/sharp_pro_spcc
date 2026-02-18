@extends('backend.master.index')

@section('title', 'JOURNAL ENTRY DETAIL')

@section('breadcrumbs')
    <span>REPORTS</span> / <span>ACCOUNTING REPORTS</span> / <span class="highlight">JOURNAL ENTRY DETAIL</span>
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
                <h5 class="mb-0">Journal Entry: {{ $entry->reference_number ?: ('JE-' . $entry->id) }}</h5>
                <a href="/reports/accounting" class="btn btn-sm btn-primary">Back</a>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Date:</strong> {{ $entry->entry_date }} | <strong>Status:</strong> {{ strtoupper((string) $entry->status) }}</p>
                <p class="mb-3"><strong>Description:</strong> {{ $entry->description }}</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Account</th>
                                <th>Tax</th>
                                <th>Source</th>
                                <th class="text-right">Debit</th>
                                <th class="text-right">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lines as $line)
                                <tr>
                                    <td>{{ $line->description ?: '-' }}</td>
                                    <td>{{ optional($line->chart_of_account)->account_name }} ({{ optional($line->chart_of_account)->account_number }})</td>
                                    <td>{{ $line->tax_rate ?: '-' }}</td>
                                    <td>{{ $line->data_type && $line->data_id ? ($line->data_type . ' #' . $line->data_id) : '-' }}</td>
                                    <td class="text-right">{{ $fmt($line->debit_amount) }}</td>
                                    <td class="text-right">{{ $fmt($line->credit_amount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
