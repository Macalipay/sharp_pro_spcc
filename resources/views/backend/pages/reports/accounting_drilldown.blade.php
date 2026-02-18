@extends('backend.master.index')

@section('title', 'ACCOUNTING DRILLDOWN')

@section('breadcrumbs')
    <span>REPORTS</span> / <span>ACCOUNTING REPORTS</span> / <span class="highlight">DRILLDOWN</span>
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
                <h5 class="mb-0">Accounting Drilldown</h5>
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
                                <th>Source Document</th>
                                <th>Line Detail</th>
                                <th class="text-right">Debit</th>
                                <th class="text-right">Credit</th>
                                <th class="text-right">Impact</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $row)
                                <tr>
                                    <td>{{ $row->entry_date }}</td>
                                    <td>
                                        <a href="/reports/accounting/journal-entry/{{ $row->journal_entry_id }}" target="_blank">
                                            {{ $row->reference_number ?: ('JE-' . $row->journal_entry_id) }}
                                        </a>
                                    </td>
                                    <td>{{ $row->account_name }} ({{ $row->account_number }})</td>
                                    <td>
                                        @if(!empty($row->data_type) && !empty($row->data_id))
                                            <a href="/reports/accounting/source-document/{{ urlencode($row->data_type) }}/{{ urlencode($row->data_id) }}" target="_blank">
                                                {{ $row->data_type }} #{{ $row->data_id }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $row->line_description ?: '-' }}</td>
                                    <td class="text-right">{{ $fmt($row->debit_amount) }}</td>
                                    <td class="text-right">{{ $fmt($row->credit_amount) }}</td>
                                    <td class="text-right">{{ $fmt($row->impact_amount) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center">No drilldown records found.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-right">Page Totals</th>
                                <th class="text-right">{{ $fmt($totals['debit']) }}</th>
                                <th class="text-right">{{ $fmt($totals['credit']) }}</th>
                                <th class="text-right">{{ $fmt($totals['impact']) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-2">
                    {{ $rows->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
