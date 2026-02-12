@extends('backend.master.index')

@section('title', 'PAYROLL')

@section('breadcrumbs')
    <span>TRANSACTION </span> / <span class="highlight">PAYROLL</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">PAYROLL</h5>
            </div>
            @include('backend.partial.flash-message')
            <div class="col-12">
                <div class="card-body">
                    <table id="payroll_table" class="table table-striped" style="width:100%"></table>
                </div>
            </div>
        </div>
    </div>
</div>

@section('sc-modal')
@parent
@endsection
@endsection

@section('scripts')
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="/js/backend/pages/transaction/payroll.js"></script>
@endsection

@section('styles-2')
    <link href="{{asset('/css/custom/payroll.css')}}" rel="stylesheet">
@endsection