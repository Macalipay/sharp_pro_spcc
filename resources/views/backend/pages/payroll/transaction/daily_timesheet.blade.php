@extends('backend.master.index')

@section('title', 'DAILY TIMESHEET')

@section('breadcrumbs')
    <span>TRANSACTION / PAYROLL</span> / <span class="highlight">DAILY TIMESHEET</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            @include('backend.partial.flash-message')
            <div class="col-12 filter">
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" id="date" name="date" class="form-control form-control-sm" value="{{date('Y-m-d')}}" onchange="timesheetTable()">
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label>Project</label>
                            <select name="project" id="project" class="form-control form-control-sm" onchange="timesheetTable()">
                                <option value="all">ALL</option>
                                @foreach ($projects as $item)
                                    <option value="{{$item['id']}}">{{$item['project_name']}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <table id="timesheet_table" class="table table-striped" style="width:100%"></table>
            </div>
        </div>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="daily_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('daily_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="classForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-6 time_in">
                        <label>TIME IN</label>
                        <input type="datetime-local" class="form-control" id="time_in" name="time_in"/>
                    </div>
                    <div class="form-group col-md-6 time_out">
                        <label>TIME OUT</label>
                        <input type="datetime-local" class="form-control" id="time_out" name="time_out"/>
                    </div>
                    <div class="form-group col-md-6 break_in">
                        <label>BREAK IN</label>
                        <input type="datetime-local" class="form-control" id="break_in" name="break_in"/>
                    </div>
                    <div class="form-group col-md-6 break_out">
                        <label>BREAK OUT</label>
                        <input type="datetime-local" class="form-control" id="break_out" name="break_out"/>
                    </div>
                    <div class="form-group col-md-6 ot_in">
                        <label>OT IN</label>
                        <input type="datetime-local" class="form-control" id="ot_in" name="ot_in"/>
                    </div>
                    <div class="form-group col-md-6 ot_out">
                        <label>OT OUT</label>
                        <input type="datetime-local" class="form-control" id="ot_out" name="ot_out"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="sc-modal-content" id="detialed_work_modal">
    <div class="sc-modal-dialog sc-xl">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('detialed_work_modal').hide('all', closeWorkDetails)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-8" style="font-size:12px">
                    Employee Name: <span id="employee_name">-</span><br>
                    Total Actual Hours: <span id="total_hours">-</span>
                </div>
                <div class="col-4 text-right">
                    <button class="btn btn-sm btn-success" onclick="add_details()">Add Details</button>
                </div>
                <div class="col-12">
                    <table id="work_details_table">
                        <thead>
                            <th></th>
                            <th>Type of Work</th>
                            <th>Earnings</th>
                            <th>Projects</th>
                            <th>Hours to be Rendered</th>
                            <th>Remarks</th>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4"></td>
                                <td><span class="total_hours_rendered">0</span></td>
                                <td id="status_rendered"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="add_details">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('add_details').hide()"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-12 form-group">
                    <label for="worktype_id">Work Type</label>
                    <select name="worktype_id" id="worktype_id" class="form-control form-control-sm">
                        <option value=""></option>
                        @foreach ($worktype as $item)
                            <option value="{{$item['id']}}">{{$item['name']}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 form-group">
                    <label for="earnings">Earnings</label>
                    <select name="earnings" id="earnings" class="form-control form-control-sm">
                        <option value="RE">REGULAR</option>
                        <option value="OT">OVERTIME</option>
                    </select>
                </div>
                <div class="col-6 form-group">
                    <label for="hours">Hours</label>
                    <input type="number" class="form-control form-control-sm" id="hours" name="hours"/>
                </div>
                <div class="col-12 form-group">
                    <label for="project_id">Project:</label>
                    <select name="project_id" id="project_id" class="form-control form-control-sm">
                        <option value=""></option>
                        @foreach ($projects as $item)
                            <option value="{{$item['id']}}">{{$item['project_name']}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 form-group">
                    <label for="remarks">Remarks</label>
                    <textarea name="remarks" id="remarks" class="form-control form-control-sm"></textarea>
                </div>
            </div>
        </div>
        <div class="sc-modal-footer text-right">
            <button class="btn btn-sm btn-primary btn-sv" onclick="$('#sv').click()">SAVE</button>
        </div>
    </div>
</div>
@endsection
@endsection

@section('scripts')
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="/js/backend/pages/payroll/transaction/daily_timesheet.js"></script>
@endsection

@section('styles')
    <link rel="stylesheet" href="/css/custom/daily_timesheet.css">
@endsection