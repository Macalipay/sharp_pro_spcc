@extends('backend.master.index')

@section('title', 'BILLS / EXPENSES')

@section('breadcrumbs')
    <span>ACCOUNTING</span> / <span class="highlight">BILLS / EXPENSES</span>
@endsection

@section('styles')
<style>
    .wf-tabs .nav-link { font-weight: 700; font-size: 12px; }
    .wf-status-badge { font-size: 10px; font-weight: 700; padding: 4px 8px; border-radius: 12px; }
    .wf-draft { background: #f2f2f2; color: #444; }
    .wf-awaiting-approval { background: #ffe7bf; color: #8a5100; }
    .wf-awaiting-payment { background: #d9edff; color: #0f4b80; }
    .wf-paid { background: #dff6df; color: #146c2e; }
    .line-table td, .line-table th { font-size: 12px; padding: 6px; }
</style>
@endsection

@section('content')
@php
    $fmt = function ($value) {
        $v = (float) $value;
        return number_format($v, 2);
    };
    $editMode = ($mode === 'edit' && !empty($editBill));
    $createMode = ($mode === 'create');
    $showEntryForm = $editMode || $createMode;
    $entryMainColClass = $createMode ? 'col-12' : 'col-xl-8 col-lg-8';
    $entrySideColClass = $createMode ? 'col-12' : 'col-xl-4 col-lg-4';
@endphp
<div class="row mb-2">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">BILLS / EXPENSES WORKFLOW</h5>
        @if(!$showEntryForm)
            <a href="/accounting/bills?tab={{ $statusTab }}&mode=create" class="btn btn-sm btn-primary">Add New</a>
        @else
            <a href="/accounting/bills?tab={{ $statusTab }}" class="btn btn-sm btn-light">Back to List</a>
        @endif
    </div>
</div>

@if($showEntryForm)
    <div class="row">
        <div class="{{ $entryMainColClass }}">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">{{ $editMode ? 'EDIT DRAFT BILL' : 'NEW BILL / EXPENSE' }}</h5>
                    @if($editMode)
                        <small class="d-block mt-1">Status: <strong>{{ str_replace('_', ' ', $editBill->status) }}</strong></small>
                    @else
                        <small class="d-block mt-1">Status: <strong>DRAFT</strong></small>
                    @endif
                </div>
                <div class="card-body">
                <form class="not" method="POST" action="/accounting/bills/save">
                    @csrf
                    <input type="hidden" name="bill_id" value="{{ $editMode ? $editBill->id : '' }}">
                    <input type="hidden" name="return_tab" value="{{ $statusTab }}">
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label>Supplier</label>
                            <select name="supplier_id" class="form-control" id="bill_supplier_id">
                                <option value="">Select Supplier</option>
                                <option value="__add_new_supplier__">+ Add New Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ (string) old('supplier_id', $editMode ? $editBill->supplier_id : ($preselectedSupplierId ?: '')) === (string) $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->supplier_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Bill Date</label>
                            <input type="date" name="bill_date" class="form-control" value="{{ old('bill_date', $editMode ? $editBill->bill_date : '') }}">
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Due Date</label>
                            <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $editMode ? $editBill->due_date : '') }}">
                        </div>
                        <div class="col-md-5 form-group">
                            <label>Description</label>
                            <input type="text" name="description" class="form-control" value="{{ old('description', $editMode ? $editBill->description : '') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Bill No.</label>
                            <input type="text" class="form-control" value="{{ $editMode ? ($editBill->bill_no ?: ('BILL-' . str_pad($editBill->id, 5, '0', STR_PAD_LEFT))) : 'Auto-generated' }}" readonly>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Accounts Payable Account</label>
                            <input type="text" class="form-control" value="Accounts Payable (System-Locked Control Account)" readonly>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered line-table" id="bill_lines_table">
                            <thead>
                                <tr>
                                    <th style="width:33%">Line Description</th>
                                    <th style="width:27%">Expense / Asset Account</th>
                                    <th style="width:10%">Qty</th>
                                    <th style="width:15%">Unit Price</th>
                                    <th style="width:15%">Line Total</th>
                                </tr>
                            </thead>
                            <tbody id="bill_line_body">
                                @php $rows = old('line_account_id') ? range(0, count(old('line_account_id')) - 1) : (($editMode && $editBill->items->count() > 0) ? $editBill->items->keys() : collect([0])); @endphp
                                @foreach($rows as $i)
                                    @php
                                        $item = ($editMode && $editBill->items->count() > $i) ? $editBill->items[$i] : null;
                                        $qty = old("line_qty.$i", $item ? $item->quantity : 1);
                                        $price = old("line_unit_price.$i", $item ? $item->unit_price : 0);
                                    @endphp
                                    <tr>
                                        <td><input type="text" name="line_description[]" class="form-control form-control-sm" value="{{ old("line_description.$i", $item ? $item->description : '') }}"></td>
                                        <td>
                                            <select name="line_account_id[]" class="form-control form-control-sm">
                                                <option value="">Select Account</option>
                                                @foreach($expenseAndAssetAccounts as $account)
                                                    <option value="{{ $account->id }}" {{ (string) old("line_account_id.$i", $item ? $item->chart_of_account_id : '') === (string) $account->id ? 'selected' : '' }}>
                                                        {{ $account->account_number }} - {{ $account->account_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" step="0.01" min="0" name="line_qty[]" class="form-control form-control-sm bill-line-qty" value="{{ $qty }}"></td>
                                        <td><input type="number" step="0.01" min="0" name="line_unit_price[]" class="form-control form-control-sm bill-line-price" value="{{ $price }}"></td>
                                        <td class="text-right align-middle bill-line-total">{{ number_format((float) $qty * (float) $price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">TOTAL</th>
                                    <th class="text-right" id="bill_grand_total">0.00</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-light mb-2" onclick="addBillLine()">Add Line</button>
                    <div class="mt-2 d-flex justify-content-end">
                        <a href="/accounting/bills?tab={{ $statusTab }}" class="btn btn-sm btn-light mr-2">Cancel</a>
                        <button type="submit" name="action_type" value="draft" class="btn btn-sm btn-primary mr-2">Save as Draft</button>
                        <button type="submit" name="action_type" value="submit" class="btn btn-sm btn-success">Submit for Approval</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="{{ $entrySideColClass }}">
            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">Make a Payment</h6></div>
                <div class="card-body">
                    @if($editMode && $editBill->status === 'AWAITING_PAYMENT')
                        <form method="POST" action="/accounting/bills/pay/{{ $editBill->id }}" class="not">
                            @csrf
                            <div class="form-group">
                                <label>Amount Paid</label>
                                <input type="number" step="0.01" min="0.01" max="{{ number_format((float)($editBill->remaining_amount ?? 0), 2, '.', '') }}" name="payment_amount" class="form-control" value="{{ number_format((float)($editBill->remaining_amount ?? 0), 2, '.', '') }}" required>
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
                    @else
                        <div class="alert alert-light mb-0">
                            Bill must be approved before payment can be applied.
                        </div>
                    @endif

                    @if($editMode)
                        <hr>
                        <div><strong>Paid Amount:</strong> {{ number_format((float)($editBill->paid_amount ?? 0), 2) }}</div>
                        <div><strong>Remaining Due:</strong> {{ number_format((float)($editBill->remaining_amount ?? $editBill->total_amount), 2) }}</div>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">History &amp; Notes</h6></div>
                <div class="card-body">
                    @if($editMode)
                        <div class="mb-2">
                            <strong>Created By:</strong> {{ optional($editBill->created_user)->name ?: 'System' }} |
                            <strong>Date:</strong> {{ $editBill->created_at ?: '-' }}
                        </div>
                        <button class="btn btn-sm btn-outline-primary mb-2" type="button" data-toggle="collapse" data-target="#billHistoryCollapse">
                            Show History ({{ $editBill->histories ? $editBill->histories->count() : 0 }} entries)
                        </button>
                        <div class="collapse" id="billHistoryCollapse">
                            <div class="list-group mb-3">
                                @forelse(($editBill->histories ?? collect())->sortByDesc('performed_at') as $history)
                                    <div class="list-group-item p-2">
                                        <div><strong>{{ $history->action }}</strong> - {{ $history->description ?: '-' }}</div>
                                        <small>{{ optional($history->actor)->name ?: 'System' }} | {{ $history->performed_at ?: $history->created_at }} @if(!is_null($history->amount)) | Amount: {{ number_format((float)$history->amount, 2) }} @endif</small>
                                    </div>
                                @empty
                                    <div class="text-muted">No history entries yet.</div>
                                @endforelse
                            </div>
                        </div>

                        <form method="POST" action="/accounting/bills/notes/{{ $editBill->id }}" class="not">
                            @csrf
                            <div class="form-group">
                                <label>Add Note</label>
                                <textarea name="note" class="form-control" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">Add Note</button>
                        </form>

                        <div class="mt-2">
                            @foreach(($editBill->notes ?? collect())->sortByDesc('added_at') as $note)
                                <div class="border rounded p-2 mb-2">
                                    <div>{{ $note->note }}</div>
                                    <small>{{ optional($note->author)->name ?: 'User' }} | {{ $note->added_at ?: $note->created_at }}</small>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted">History and notes will appear after the bill is saved.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
@if(!$showEntryForm)
@php
    $tabTotalAmount = (float) ($grouped[$statusTab]->sum('total_amount'));
    $allExpensesTotalAmount =
        (float) ($grouped['DRAFT']->sum('total_amount')) +
        (float) ($grouped['AWAITING_APPROVAL']->sum('total_amount')) +
        (float) ($grouped['AWAITING_PAYMENT']->sum('total_amount')) +
        (float) ($grouped['PAID']->sum('total_amount'));
@endphp
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs wf-tabs card-header-tabs">
                    <li class="nav-item"><a class="nav-link {{ $statusTab === 'DRAFT' ? 'active' : '' }}" href="/accounting/bills?tab=DRAFT&mode=list">DRAFT</a></li>
                    <li class="nav-item"><a class="nav-link {{ $statusTab === 'AWAITING_APPROVAL' ? 'active' : '' }}" href="/accounting/bills?tab=AWAITING_APPROVAL&mode=list">AWAITING APPROVAL</a></li>
                    <li class="nav-item"><a class="nav-link {{ $statusTab === 'AWAITING_PAYMENT' ? 'active' : '' }}" href="/accounting/bills?tab=AWAITING_PAYMENT&mode=list">AWAITING PAYMENT</a></li>
                    <li class="nav-item"><a class="nav-link {{ $statusTab === 'PAID' ? 'active' : '' }}" href="/accounting/bills?tab=PAID&mode=list">PAID</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-end mb-2" style="gap: 8px;">
                    <span class="badge badge-info p-2">Tab Total: {{ $fmt($tabTotalAmount) }}</span>
                    <span class="badge badge-primary p-2">All Expenses Total: {{ $fmt($allExpensesTotalAmount) }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Bill No.</th>
                                <th>Supplier</th>
                                <th>Bill Date</th>
                                <th>Due Date</th>
                                <th class="text-right">Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($grouped[$statusTab] as $bill)
                                @php $rowUrl = $bill->status === 'DRAFT' ? ('/accounting/bills?tab=' . $statusTab . '&mode=edit&bill_id=' . $bill->id) : ('/accounting/bills/show/' . $bill->id); @endphp
                                <tr class="bill-row" data-href="{{ $rowUrl }}">
                                    <td><a href="{{ $rowUrl }}">{{ $bill->bill_no ?: ('BILL-' . str_pad($bill->id, 5, '0', STR_PAD_LEFT)) }}</a></td>
                                    <td>{{ optional($bill->supplier)->supplier_name ?: '-' }}</td>
                                    <td>{{ $bill->bill_date ?: '-' }}</td>
                                    <td>{{ $bill->due_date ?: '-' }}</td>
                                    <td class="text-right">{{ $fmt($bill->total_amount) }}</td>
                                    <td>
                                        @if($bill->status === 'DRAFT')
                                            <span class="wf-status-badge wf-draft">DRAFT</span>
                                        @elseif($bill->status === 'AWAITING_APPROVAL')
                                            <span class="wf-status-badge wf-awaiting-approval">AWAITING APPROVAL</span>
                                        @elseif($bill->status === 'AWAITING_PAYMENT')
                                            <span class="wf-status-badge wf-awaiting-payment">AWAITING PAYMENT</span>
                                        @else
                                            <span class="wf-status-badge wf-paid">PAID</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($bill->status === 'DRAFT')
                                            <a class="btn btn-sm btn-primary" href="/accounting/bills?tab={{ $statusTab }}&mode=edit&bill_id={{ $bill->id }}">Edit</a>
                                            <form method="POST" action="/accounting/bills/destroy/{{ $bill->id }}" class="d-inline not">@csrf<button class="btn btn-sm btn-danger">Delete</button></form>
                                            <form method="POST" action="/accounting/bills/submit/{{ $bill->id }}" class="d-inline not">@csrf<button class="btn btn-sm btn-success">Submit for Approval</button></form>
                                        @elseif($bill->status === 'AWAITING_APPROVAL')
                                            <form method="POST" action="/accounting/bills/approve/{{ $bill->id }}" class="d-inline not">@csrf<button class="btn btn-sm btn-success">Approve</button></form>
                                            <form method="POST" action="/accounting/bills/reject/{{ $bill->id }}" class="d-inline not">@csrf<button class="btn btn-sm btn-warning">Reject</button></form>
                                            <a class="btn btn-sm btn-light" href="/accounting/bills/show/{{ $bill->id }}">View</a>
                                        @elseif($bill->status === 'AWAITING_PAYMENT')
                                            <a class="btn btn-sm btn-success" href="/accounting/bills/show/{{ $bill->id }}">Apply Payment</a>
                                            <a class="btn btn-sm btn-light" href="/accounting/bills/show/{{ $bill->id }}">View</a>
                                        @else
                                            <a class="btn btn-sm btn-light" href="/accounting/bills/show/{{ $bill->id }}">View</a>
                                            <a class="btn btn-sm btn-outline-secondary" href="/accounting/bills/show/{{ $bill->id }}" target="_blank">Print</a>
                                            <a class="btn btn-sm btn-outline-secondary" href="/accounting/bills/show/{{ $bill->id }}" target="_blank">Download</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center">No records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="modal fade" id="addSupplierModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form class="not" id="addSupplierModalForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Supplier</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Supplier Name</label>
                        <input type="text" name="supplier_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Contact No</label>
                        <input type="text" name="contact_no" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Person</label>
                        <input type="text" name="contact_person" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>TIN No</label>
                        <input type="text" name="tin_no" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Terms</label>
                        <input type="text" name="payment_terms" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="vatable" class="form-control" required>
                            <option value="">Select One</option>
                            <option value="1">VAT</option>
                            <option value="2">NON-VAT</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Bank Name</label>
                        <input type="text" name="bank_name" class="form-control">
                    </div>
                    <div class="form-group mb-0">
                        <label>Bank Account</label>
                        <input type="text" name="bank_account" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-primary">Save Supplier</button>
                    <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
var lastSelectedSupplierValue = '';

function computeBillTotals() {
    var grand = 0;
    document.querySelectorAll('#bill_line_body tr').forEach(function (tr) {
        var qty = parseFloat((tr.querySelector('.bill-line-qty') || {}).value || 0);
        var price = parseFloat((tr.querySelector('.bill-line-price') || {}).value || 0);
        var total = qty * price;
        grand += total;
        var totalCell = tr.querySelector('.bill-line-total');
        if (totalCell) {
            totalCell.innerText = total.toFixed(2);
        }
    });
    var grandCell = document.getElementById('bill_grand_total');
    if (grandCell) {
        grandCell.innerText = grand.toFixed(2);
    }
}

function addBillLine() {
    var tbody = document.getElementById('bill_line_body');
    if (!tbody) return;
    var firstRow = tbody.querySelector('tr');
    if (!firstRow) return;
    var clone = firstRow.cloneNode(true);
    clone.querySelectorAll('input').forEach(function (input) {
        if (input.classList.contains('bill-line-qty')) {
            input.value = '1';
        } else if (input.classList.contains('bill-line-price')) {
            input.value = '0';
        } else {
            input.value = '';
        }
    });
    clone.querySelectorAll('select').forEach(function (select) {
        select.selectedIndex = 0;
    });
    var totalCell = clone.querySelector('.bill-line-total');
    if (totalCell) {
        totalCell.innerText = '0.00';
    }
    tbody.appendChild(clone);
    computeBillTotals();
}

document.addEventListener('input', function (e) {
    if (e.target.classList.contains('bill-line-qty') || e.target.classList.contains('bill-line-price')) {
        computeBillTotals();
    }
});
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('bill_lines_table')) {
        computeBillTotals();
    }

    var supplierSelect = document.getElementById('bill_supplier_id');
    if (supplierSelect) {
        lastSelectedSupplierValue = supplierSelect.value || '';
    }

    $(document).on('change', '#bill_supplier_id', function () {
        if (this.value === '__add_new_supplier__') {
            this.value = lastSelectedSupplierValue || '';
            openAddSupplierModal();
            return;
        }
        lastSelectedSupplierValue = this.value || '';
    });

    document.querySelectorAll('.bill-row').forEach(function (row) {
        row.addEventListener('click', function (e) {
            if (e.target.closest('a,button,form,input,select,label')) {
                return;
            }
            var url = row.getAttribute('data-href');
            if (url) {
                window.location.href = url;
            }
        });
    });
});

function openAddSupplierModal() {
    var form = document.getElementById('addSupplierModalForm');
    if (form) {
        form.reset();
    }
    var modal = $('#addSupplierModal');
    if ($.fn.modal) {
        modal.modal('show');
    } else {
        modal.addClass('show').css('display', 'block');
        $('body').addClass('modal-open');
    }
}

function closeAddSupplierModal() {
    var modal = $('#addSupplierModal');
    if ($.fn.modal) {
        modal.modal('hide');
    } else {
        modal.removeClass('show').css('display', 'none');
        $('body').removeClass('modal-open');
    }
}

$(document).on('submit', '#addSupplierModalForm', function (e) {
    e.preventDefault();
    var formData = $(this).serialize();
    $.ajax({
        url: '/accounting/bills/suppliers/save',
        method: 'POST',
        data: formData + '&_token=' + encodeURIComponent($('meta[name="csrf-token"]').attr('content')),
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        success: function (response) {
            if (!response || !response.data) {
                toastr.error('Unable to add supplier.');
                return;
            }
            var supplierId = String(response.data.id);
            var supplierName = response.data.supplier_name || 'New Supplier';
            var supplierSelect = $('#bill_supplier_id');
            if (supplierSelect.find('option[value="' + supplierId + '"]').length === 0) {
                $('<option>', { value: supplierId, text: supplierName }).appendTo(supplierSelect);
            }
            supplierSelect.val(supplierId);
            lastSelectedSupplierValue = supplierId;
            closeAddSupplierModal();
            toastr.success(response.message || 'Supplier created successfully.');
        },
        error: function (xhr) {
            if (xhr && xhr.responseJSON && xhr.responseJSON.errors) {
                var firstKey = Object.keys(xhr.responseJSON.errors)[0];
                if (firstKey) {
                    toastr.error(xhr.responseJSON.errors[firstKey][0]);
                    return;
                }
            }
            toastr.error('Failed to save supplier.');
        }
    });
});

$(document).on('click', '#addSupplierModal [data-dismiss="modal"], #addSupplierModal .close', function () {
    closeAddSupplierModal();
});

</script>
@endsection
