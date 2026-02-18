@extends('backend.master.index')

@section('title', 'BILL DETAILS')

@section('breadcrumbs')
    <span>ACCOUNTING</span> / <span>BILLS / EXPENSES</span> / <span class="highlight">BILL DETAILS</span>
@endsection

@section('content')
@php
    $fmt = function ($value) {
        return number_format((float) $value, 2);
    };
@endphp
<div class="row mb-2">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">BILL {{ $bill->bill_no ?: ('BILL-' . str_pad($bill->id, 5, '0', STR_PAD_LEFT)) }}</h5>
        <a href="/accounting/bills?tab={{ $bill->status }}&mode=list" class="btn btn-sm btn-light">Back</a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Bill / Expense Document</h6>
                <small>Status: <strong>{{ str_replace('_', ' ', $bill->status) }}</strong></small>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Supplier:</strong> {{ optional($bill->supplier)->supplier_name ?: '-' }}</div>
                    <div class="col-md-2"><strong>Bill Date:</strong> {{ $bill->bill_date ?: '-' }}</div>
                    <div class="col-md-2"><strong>Due Date:</strong> {{ $bill->due_date ?: '-' }}</div>
                    <div class="col-md-4"><strong>Description:</strong> {{ $bill->description ?: '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Total Amount:</strong> {{ $fmt($bill->total_amount) }}</div>
                    <div class="col-md-4"><strong>Paid Amount:</strong> {{ $fmt($bill->paid_amount) }}</div>
                    <div class="col-md-4"><strong>Remaining Due:</strong> {{ $fmt($bill->remaining_amount) }}</div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Account</th>
                                <th class="text-right">Qty</th>
                                <th class="text-right">Unit Price</th>
                                <th class="text-right">Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bill->items as $item)
                                <tr>
                                    <td>{{ $item->description ?: '-' }}</td>
                                    <td>{{ optional($item->account)->account_number }} {{ optional($item->account)->account_name ? ('- ' . optional($item->account)->account_name) : '' }}</td>
                                    <td class="text-right">{{ number_format((float)$item->quantity, 2) }}</td>
                                    <td class="text-right">{{ $fmt($item->unit_price) }}</td>
                                    <td class="text-right">{{ $fmt($item->line_total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Make a Payment</h6></div>
            <div class="card-body">
                @if($bill->status === 'AWAITING_PAYMENT')
                    <form method="POST" action="/accounting/bills/pay/{{ $bill->id }}" class="not">
                        @csrf
                        <div class="form-group">
                            <label>Amount Paid</label>
                            <input type="number" step="0.01" min="0.01" max="{{ number_format((float)$bill->remaining_amount, 2, '.', '') }}" name="payment_amount" class="form-control" value="{{ number_format((float)$bill->remaining_amount, 2, '.', '') }}" required>
                        </div>
                        <div class="form-group">
                            <label>Date Paid</label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label>Paid From</label>
                            <select name="payment_account_id" class="form-control" required>
                                <option value="">Select Account</option>
                                @foreach($paymentAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->account_number }} - {{ $account->account_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Reference</label>
                            <input type="text" name="payment_reference" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-sm btn-success btn-block">Add Payment</button>
                    </form>
                @elseif($bill->status === 'PAID')
                    <div class="alert alert-success mb-0">
                        Bill is fully paid.
                    </div>
                @else
                    <div class="alert alert-light mb-0">
                        Bill must be approved before payment can be applied.
                    </div>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">History &amp; Notes</h6></div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Created By:</strong> {{ optional($bill->created_user)->name ?: 'System' }} |
                    <strong>Date:</strong> {{ $bill->created_at ?: '-' }}
                </div>
                <button class="btn btn-sm btn-outline-primary mb-2" type="button" data-toggle="collapse" data-target="#billHistoryCollapse">
                    Show History ({{ $bill->histories ? $bill->histories->count() : 0 }} entries)
                </button>
                <div class="collapse" id="billHistoryCollapse">
                    <div class="list-group mb-3">
                        @forelse(($bill->histories ?? collect())->sortByDesc('performed_at') as $history)
                            <div class="list-group-item p-2">
                                <div><strong>{{ $history->action }}</strong> - {{ $history->description ?: '-' }}</div>
                                <small>{{ optional($history->actor)->name ?: 'System' }} | {{ $history->performed_at ?: $history->created_at }} @if(!is_null($history->amount)) | Amount: {{ number_format((float)$history->amount, 2) }} @endif</small>
                            </div>
                        @empty
                            <div class="text-muted">No history entries yet.</div>
                        @endforelse
                    </div>
                </div>

                <form method="POST" action="/accounting/bills/notes/{{ $bill->id }}" class="not">
                    @csrf
                    <div class="form-group">
                        <label>Add Note</label>
                        <textarea name="note" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">Add Note</button>
                </form>

                <div class="mt-2">
                    @foreach(($bill->notes ?? collect())->sortByDesc('added_at') as $note)
                        <div class="border rounded p-2 mb-2">
                            <div>{{ $note->note }}</div>
                            <small>{{ optional($note->author)->name ?: 'User' }} | {{ $note->added_at ?: $note->created_at }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
