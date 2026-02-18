@extends('backend.master.index')

@section('title', 'ACCOUNTING REPORTS')

@section('breadcrumbs')
    <span>REPORTS</span> / <span class="highlight">ACCOUNTING REPORTS</span>
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
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">ACCOUNTING REPORTS</h5>
                <a href="/reports" class="btn btn-sm reports-btn">Back to Reports</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead>
                            <tr>
                                <th style="width: 34%;">Report Name</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <a href="/reports/accounting/statement-of-financial-position"><strong>Statement of Financial Position (Balance Sheet)</strong></a>
                                </td>
                                <td>Assets, Liabilities, and Equity balances as at a selected date.</td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="/reports/accounting/statement-of-profit-or-loss"><strong>Statement of Profit or Loss</strong></a>
                                </td>
                                <td>Revenue and Expenses for a selected reporting period.</td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="/reports/accounting/statement-of-changes-in-equity"><strong>Statement of Changes in Equity</strong></a>
                                </td>
                                <td>Opening equity, period movements, net profit/loss, and closing equity.</td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="/reports/accounting/statement-of-cash-flows"><strong>Statement of Cash Flows</strong></a>
                                </td>
                                <td>Operating, investing, and financing cash movements for a selected period.</td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="/reports/accounting/journal-entry-impact"><strong>Journal Entry Date Impact Report</strong></a>
                                </td>
                                <td>Shows Chart of Accounts report mapping and journal entry posting-date impact to Profit &amp; Loss and Balance Sheet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
