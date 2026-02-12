@extends('backend.master.index')

@section('title', 'MASTERLIST')

@section('breadcrumbs')
    <span>MASTERLIST</span> / <span class="highlight">EMPLOYEE</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="row">
            @include('backend.partial.flash-message')
            <div class="col-12">
                <table id="employee_table" class="table table-striped" style="width:100%"></table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script> const canDownload = @json($canDownload); </script>
    <script src="{{asset('/plugins/onscan.js')}}" ></script>
    <script src="{{asset('/plugins/onscan.min.js')}}" ></script>
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="/js/backend/pages/masterlist/employee.js"></script>
@endsection