@extends('backend.master.index')

@section('title', 'SSS TABLE')

@section('breadcrumbs')
    <span>Maintenance</span>  /  <span class="highlight">SSS</span>
@endsection

@section('left-content')
<div class="row" id="sss_setup">
    <div class="col-md-12">
        @include('backend.partial.flash-message')
        <h5>SSS SETUP</h5>
        <div class="row">
            <div class="form-group col-12">
                <label for="">NUMBER OF ROW:</label>
                <input type="number" class="form-control" id="row" name="row" value="0">
            </div>

            <div class="side-title col-12">RANGE COMPENSATION</div>
            <div class="form-group col-6">
                <label for="">RANGE 1:</label>
                <input type="number" class="form-control" id="range_1" name="range_1" value="0">
            </div>
            <div class="form-group col-6">
                <label for="">RANGE 2:</label>
                <input type="number" class="form-control" id="range_2" name="range_2" value="0">
            </div>
            <div class="form-group col-12">
                <label for="">INTERVAL</label>
                <input type="number" class="form-control" id="range_interval" name="range_interval" value="0">
            </div>
            
            <div class="side-title col-12">MONTHLY SALARY</div>
            <div class="form-group col-12">
                <label for="">MINIMUM:</label>
                <input type="number" class="form-control" id="monthly_minimum" name="monthly_minimum" value="0">
            </div>
            <div class="form-group col-12">
                <label for="">INTERVAL</label>
                <input type="number" class="form-control" id="monthly_interval" name="monthly_interval" value="0">
            </div>

            <div class="side-title col-12">REGULAR SS</div>
            <div class="form-group col-12">
                <label for="">MINIMUM CONTRIBUTION:</label>
                <input type="number" class="form-control" id="regular_ss" name="regular_ss" value="0">
            </div>
            <div class="form-group col-12">
                <label for="">INTERVAL</label>
                <input type="number" class="form-control" id="regular_interval" name="regular_interval" value="0">
            </div>
            <div class="form-group col-6">
                <label for="">EE (%)</label>
                <input type="number" class="form-control" id="ee" name="ee" value="0">
            </div>
            <div class="form-group col-6">
                <label for="">ER (%)</label>
                <input type="number" class="form-control" id="er" name="er" value="0">
            </div>
            <div class="col-12 text-right mb-2">
                <button class="btn btn-success" id="generateSSS">GENERATE SSS TABLE</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="row" id="sss_main">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">SSS Maintenance Screen
                </h5>
            </div>
            @include('backend.partial.flash-message')
            <div class="col-12">
                <div class="card-body">
                    <table id="sss_table" class="table table-striped" style="width:100%"></table>
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
<script src="/js/backend/pages/payroll/maintenance/sss.js"></script>
@endsection

<style>
div#sss_setup {
    height: calc(100% - 22px);
    padding: 20px 10px;
    background: #f5f5f5;
}

div#sss_main {
    height: calc(100% - 40px);
}

</style>