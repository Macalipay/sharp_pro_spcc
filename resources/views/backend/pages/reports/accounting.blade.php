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
                <p class="mb-0">Accounting reports module is ready.</p>
            </div>
        </div>
    </div>
</div>
@endsection
