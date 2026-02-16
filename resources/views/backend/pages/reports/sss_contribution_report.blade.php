@extends('backend.master.index')

@section('title', 'SSS CONTRIBUTION REPORT')

@section('breadcrumbs')
    <span>REPORTS</span> / <span>PAYROLL REPORTS</span> / <span class="highlight">SSS CONTRIBUTION REPORT</span>
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
    @media print {
        .no-print {
            display: none !important;
        }
        .modal,
        .modal-backdrop {
            display: none !important;
        }
        body * {
            visibility: hidden !important;
        }
        .print-table-area,
        .print-table-area * {
            visibility: visible !important;
        }
        .print-table-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .print-table-area a {
            color: #111 !important;
            text-decoration: none !important;
            pointer-events: none !important;
        }
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    function printMainContributionTable(tableSelector, reportTitle) {
        var table = document.querySelector(tableSelector);
        if (!table) return;

        var printWin = window.open('', '_blank');
        if (!printWin) return;

        printWin.document.write('<html><head><title>' + reportTitle + '</title>');
        printWin.document.write('<style>@page{size:A4 landscape;margin:10mm}body{font-family:Arial,sans-serif;margin:0;color:#111}h2{margin:0 0 10px;font-size:18px}table{width:100%;border-collapse:collapse;font-size:12px}th,td{border:1px solid #ccc;padding:6px 8px}th{text-align:center;background:#f3f3f3}.text-right{text-align:right}</style>');
        printWin.document.write('</head><body><h2>' + reportTitle + '</h2>' + table.outerHTML + '</body></html>');
        printWin.document.close();
        printWin.focus();
        setTimeout(function () { printWin.print(); }, 200);
    }

    function formatCurrency(value) {
        var n = Number(value || 0);
        return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatPeriod(start, end) {
        if (!start && !end) return '-';
        if (start && end) return start + ' - ' + end;
        return start || end || '-';
    }

    function printContributionModal(modalSelector, titleSelector) {
        var modal = document.querySelector(modalSelector);
        if (!modal) return;

        var titleNode = document.querySelector(titleSelector);
        var title = titleNode ? titleNode.textContent.trim() : 'Contribution Breakdown';
        var modalBody = modal.querySelector('.modal-body');
        var content = modalBody ? modalBody.innerHTML : '';

        var printWin = window.open('', '_blank');
        if (!printWin) return;

        printWin.document.write('<html><head><title>' + title + '</title>');
        printWin.document.write('<style>body{font-family:Arial,sans-serif;margin:12px;color:#111}h2{margin:0 0 10px;font-size:18px}table{width:100%;border-collapse:collapse;font-size:12px}th,td{border:1px solid #ccc;padding:6px 8px}th{text-align:center;background:#f3f3f3}.text-right{text-align:right}.mt-3{margin-top:12px}</style>');
        printWin.document.write('</head><body><h2>' + title + '</h2>' + content + '</body></html>');
        printWin.document.close();
        printWin.focus();
        setTimeout(function () { printWin.print(); }, 200);
    }

    $(document).on('click', '.js-sss-employee', function (e) {
        e.preventDefault();
        var employeeId = $(this).data('employee-id');
        var employeeName = $(this).data('employee-name') || 'Employee';

        $('#sssBreakdownEmployee').text(employeeName);
        $('#sssBreakdownBody').html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');
        $('#sssTotalEe').text('0.00');
        $('#sssTotalEr').text('0.00');
        $('#sssTotalContribution').text('0.00');
        $('#sssBreakdownModal').modal('show');

        $.get('/reports/payroll/sss-contribution/breakdown/' + employeeId, function (resp) {
            var rows = (resp && resp.rows) ? resp.rows : [];
            var html = '';

            if (!rows.length) {
                html = '<tr><td colspan="4" class="text-center">No contribution breakdown found.</td></tr>';
            } else {
                rows.forEach(function (item) {
                    html += '<tr>' +
                        '<td>' + formatPeriod(item.period_start, item.payroll_period) + '</td>' +
                        '<td class="text-right">' + formatCurrency(item.ee_share) + '</td>' +
                        '<td class="text-right">' + formatCurrency(item.er_share) + '</td>' +
                        '<td class="text-right">' + formatCurrency(item.total_share) + '</td>' +
                    '</tr>';
                });
            }

            $('#sssBreakdownBody').html(html);
            $('#sssTotalEe').text(formatCurrency(resp && resp.totals ? resp.totals.ee : 0));
            $('#sssTotalEr').text(formatCurrency(resp && resp.totals ? resp.totals.er : 0));
            $('#sssTotalContribution').text(formatCurrency(resp && resp.totals ? resp.totals.total : 0));
        }).fail(function () {
            $('#sssBreakdownBody').html('<tr><td colspan="4" class="text-center text-danger">Unable to load breakdown.</td></tr>');
        });
    });
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center no-print">
                <h5 class="card-title mb-0">SSS CONTRIBUTION REPORT</h5>
                <div>
                    <button type="button" class="btn btn-sm btn-light mr-2" onclick="printMainContributionTable('.print-table-area table', 'SSS CONTRIBUTION REPORT')">Print</button>
                    <a href="/reports/payroll" class="btn btn-sm reports-btn">Back to Payroll Reports</a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" class="mb-2 not no-print">
                    <div class="form-row align-items-end">
                        <div class="col-md-4">
                            <label for="employee_id" class="mb-1 d-block">Employee</label>
                            <select name="employee_id" id="employee_id" class="form-control form-control-sm">
                                <option value="">All Employees</option>
                                @foreach($employeeOptions as $employee)
                                    @php
                                        $employeeName = trim(
                                            ($employee->lastname ?? '') .
                                            (($employee->firstname ?? '') !== '' ? (', ' . $employee->firstname) : '') .
                                            (($employee->middlename ?? '') !== '' ? (' ' . $employee->middlename) : '') .
                                            (($employee->suffix ?? '') !== '' ? (' ' . $employee->suffix) : '')
                                        );
                                    @endphp
                                    <option value="{{ $employee->id }}" {{ (int) request('employee_id') === (int) $employee->id ? 'selected' : '' }}>
                                        {{ $employeeName !== '' ? $employeeName : '-' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex">
                            <button type="submit" class="btn btn-sm reports-btn mr-2">Filter</button>
                            <a href="/reports/payroll/sss-contribution" class="btn btn-sm btn-light">Reset</a>
                        </div>
                    </div>
                </form>
                <div class="table-responsive print-table-area">
                    <table class="table table-bordered table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Employee Name</th>
                                <th class="text-right">Total SSS Contribution</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $row)
                                @php
                                    $fullName = trim(
                                        ($row->lastname ?? '') .
                                        (($row->firstname ?? '') !== '' ? (', ' . $row->firstname) : '') .
                                        (($row->middlename ?? '') !== '' ? (' ' . $row->middlename) : '') .
                                        (($row->suffix ?? '') !== '' ? (' ' . $row->suffix) : '')
                                    );
                                @endphp
                                <tr>
                                    <td>
                                        <a href="#" class="js-sss-employee"
                                           data-employee-id="{{ $row->employee_id }}"
                                           data-employee-name="{{ $fullName !== '' ? $fullName : '-' }}">
                                            {{ $fullName !== '' ? $fullName : '-' }}
                                        </a>
                                    </td>
                                    <td class="text-right">{{ number_format((float) ($row->contribution_amount ?? 0), 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center">No SSS contribution records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2 no-print">
                    <div class="d-flex align-items-center">
                        <small class="mr-2">Show Entries</small>
                        <form method="GET" class="not mb-0">
                            @if(request()->filled('employee_id'))
                                <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
                            @endif
                            <select name="per_page" class="form-control form-control-sm" onchange="this.form.submit()">
                                @foreach([10, 15, 20, 25, 30] as $n)
                                    <option value="{{ $n }}" {{ (int) request('per_page', 10) === $n ? 'selected' : '' }}>{{ $n }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <div>
                        <small class="mr-2">
                            Showing {{ $rows->firstItem() ?? 0 }} to {{ $rows->lastItem() ?? 0 }} of {{ $rows->total() }} entries
                        </small>
                        @if($rows->onFirstPage())
                            <span class="btn btn-sm btn-light disabled mr-1">Previous</span>
                        @else
                            <a class="btn btn-sm btn-light mr-1" href="{{ $rows->previousPageUrl() }}">Previous</a>
                        @endif

                        @if($rows->hasMorePages())
                            <a class="btn btn-sm btn-light" href="{{ $rows->nextPageUrl() }}">Next</a>
                        @else
                            <span class="btn btn-sm btn-light disabled">Next</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="sssBreakdownModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">SSS Contribution Breakdown: <span id="sssBreakdownEmployee">Employee</span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Period Covered</th>
                                <th class="text-right">EE Share</th>
                                <th class="text-right">ER Share</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody id="sssBreakdownBody">
                            <tr>
                                <td colspan="4" class="text-center">No contribution breakdown found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 text-right">
                    <div><strong>Total EE:</strong> <span id="sssTotalEe">0.00</span></div>
                    <div><strong>Total ER:</strong> <span id="sssTotalEr">0.00</span></div>
                    <div><strong>Grand Total:</strong> <span id="sssTotalContribution">0.00</span></div>
                </div>
                <div class="mt-3 d-flex justify-content-start no-print">
                    <button type="button" class="btn btn-sm btn-light" onclick="printContributionModal('#sssBreakdownModal', '#sssBreakdownEmployee')">Print</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
