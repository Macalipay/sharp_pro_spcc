@extends('backend.master.index')

@section('title', 'TIMESHEET SUMMARY')

@section('breadcrumbs')
    <span>TRANSACTION / PAYROLL</span> / <span class="highlight">TIMESHEET SUMMARY</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            @include('backend.partial.flash-message')
            <div class="col-12 filter">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row period-cls">
                            <div class="form-group col-md-3">
                                <label>Start Date</label>
                                <input type="date" id="start_date" name="start_date" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-md-3">
                                <label>End Date</label>
                                <input type="date" id="end_date" name="end_date" class="form-control form-control-sm">
                            </div>
                            
                            <div class="col-md-3 form-group">
                                <label for="project_id">Project</label>
                                <select name="project_id" id="project_id" class="form-control form-control-sm">
                                    <option value="">ALL</option>
                                    @foreach ($projects as $item)
                                        <option value="{{$item['id']}}">{{$item['project_name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="worktype_id">Work Type</label>
                                <select name="worktype_id" id="worktype_id" class="form-control form-control-sm">
                                    <option value="">ALL</option>
                                    @foreach ($worktype as $item)
                                        <option value="{{$item['id']}}">{{$item['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 filter bg-light text-left p-2">
                        <button class="btn btn-sm btn-light" onclick="clearRecord()">Clear</button>
                        <button class="btn btn-sm btn-light" onclick="searchRecord()">Generate Record</button>
                    </div>
                </div>
            </div>
            <div class="col-12">
                {{-- <table id="timesheet_table" class="table table-striped" style="width:100%"></table> --}}
                <div id="timesheet_output"></div>
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
    <script src="/js/backend/pages/payroll/transaction/summary_timesheet.js"></script>
@endsection

@section('styles')
    <link rel="stylesheet" href="/css/custom/summary_timesheet.css">
@endsection