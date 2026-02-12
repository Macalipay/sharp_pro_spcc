@extends('backend.master.index')

@section('title', 'EMPLOYEE INFORMATION')

@section('breadcrumbs')
    <span>TRANSACTION</span>  /  <span class="highlight">EMPLOYEE INFORMATION</span>
@endsection

@section('left-content')
    @include('backend.partial.component.tab_list', [
        'id'=>'employee_menu',
        'data'=>array(
            array('id'=>'general', 'title'=>'GENERAL', 'icon'=>' fas fa-file-alt', 'active'=>true, 'disabled'=>false, 'function'=>true),
            array('id'=>'leaves', 'title'=>'LEAVES', 'icon'=>' fas fa-sign-out-alt', 'active'=>false, 'disabled'=>true, 'function'=>true),
            array('id'=>'work_calendar', 'title'=>'WORK CALENDAR', 'icon'=>' fas fa-calendar', 'active'=>false,'disabled'=>true, 'function'=>true),
            array('id'=>'compensation', 'title'=>'COMPENSATION, TAXES AND BENEFITS', 'icon'=>' fas fa-pager', 'active'=>false,'disabled'=>true, 'function'=>true),
            array('id'=>'educational_background', 'title'=>'EDUCATIONAL BACKGROUND', 'icon'=>' fas fa-user-graduate', 'active'=>false,'disabled'=>true, 'function'=>true),
            array('id'=>'work_history', 'title'=>'WORK HISTORY', 'icon'=>' fas fa-undo-alt', 'active'=>false, 'disabled'=>true, 'function'=>true),
            array('id'=>'certification', 'title'=>'CERTIFICATION', 'icon'=>' fas fa-certificate', 'active'=>false, 'disabled'=>true, 'function'=>true),
            array('id'=>'training', 'title'=>'TRAINING', 'icon'=>' fas fa-chalkboard-teacher', 'active'=>false, 'disabled'=>true, 'function'=>true),
            array('id'=>'cv', 'title'=>'BIODATA', 'icon'=>' fas fa-file', 'active'=>false, 'disabled'=>true, 'function'=>true),
            array('id'=>'clearance', 'title'=>'CLEARANCE', 'icon'=>' fas fa-file', 'active'=>false, 'disabled'=>true, 'function'=>true),
        )
    ])
@endsection


@section('content')
<div class="row" style="height:100%;">
@include('backend.partial.flash-message')
    <div class="col-12" style="height:100%;">
        <div class="tab" style="height:100%;">
            <div class="tab-content">
                <form class="form-record" method="post" id="employeeInformation">
                    @include('backend.pages.payroll.transaction.employee_information.tabs.general_tab')
                    @include('backend.pages.payroll.transaction.employee_information.tabs.leaves_tab')
                    @include('backend.pages.payroll.transaction.employee_information.tabs.work_calendar_tab')
                    @include('backend.pages.payroll.transaction.employee_information.tabs.compensation_tab')
                    @include('backend.pages.payroll.transaction.employee_information.tabs.educational_background_tab')
                    @include('backend.pages.payroll.transaction.employee_information.tabs.work_history_tab')
                    @include('backend.pages.payroll.transaction.employee_information.tabs.certification_tab')
                    @include('backend.pages.payroll.transaction.employee_information.tabs.training_tab')
                    @include('backend.pages.payroll.transaction.employee_information.tabs.cv_tab')
                    @include('backend.pages.payroll.transaction.employee_information.tabs.clearance_tab')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('sc-modal')
@parent
<div class="sc-modal-content" id="rfid_modal">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar">RFID CODE DETECTED</span>
            <span class="sc-close" onclick="scion.create.sc_modal('rfid_modal').hide('all')"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-12">
                    <label>RFID CODE:</label>
                    <div class="rfid-code">-</div>
                </div>
                <div class="col-12 text-right">
                    <button class="btn btn-sm new-rfid btn-success">REGISTER</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="sc-modal-content" id="allowance_modal">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar">ALLOWANCE</span>
            <span class="sc-close" onclick="scion.create.sc_modal('allowance_modal').hide('all')"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-12">
                    <div style="font-size:12px;margin-bottom:3px;">Select Allowance</div>
                    <ul class="allowance-list">
                        @foreach ($allowance as $item)
                            <li id="allowance_{{$item->id}}">
                                <div class="allowance-item" onclick="tagAllowance({{$item->id}})">
                                    {{$item->name}}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="sc-modal-content" id="project_modal">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar">PROJECT</span>
            <span class="sc-close" onclick="scion.create.sc_modal('project_modal').hide('all')"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-12">
                    <div style="font-size:12px;margin-bottom:3px;">Select Project</div>
                    <ul class="project-list">
                        @foreach ($project as $item)
                            <li id="project_{{$item->id}}">
                                <div class="project-item" onclick="tagProject({{$item->id}})">
                                    {{$item->project_name}}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="sc-modal-content" id="worktype_modal">
    <div class="sc-modal-dialog sc-xl">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('worktype_modal').hide('all', worktypesetupClose())"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-8" style="font-size:12px">
                    Employee Name: <span id="wt_employee_name">-</span><br>
                    Total Hours: <span id="wt_total_hours">-</span>
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
                            <th>Project</th>
                            <th>Hours Rendered</th>
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
            <span class="sc-title-bar">WORK TYPE</span>
            <span class="sc-close" onclick="scion.create.sc_modal('add_details').hide(detailsClose())"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-6 form-group">
                    <label for="wt_worktype_id">Work Type</label>
                    <select name="wt_worktype_id" id="wt_worktype_id" class="form-control form-control-sm">
                        <option value=""></option>
                        @foreach ($worktype as $item)
                            <option value="{{$item->id}}">{{$item->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 form-group">
                    <label for="wt_earnings">Earning Type</label>
                    <select name="wt_earnings" id="wt_earnings" class="form-control form-control-sm">
                        <option value="RE">REGULAR</option>
                        <option value="OT">OVERTIME</option>
                    </select>
                </div>
                <div class="col-12 form-group">
                    <label for="project_id">Project:</label>
                    <select name="project_id" id="project_id" class="form-control form-control-sm">
                        <option value=""></option>
                        @foreach ($project as $item)
                            <option value="{{$item['id']}}">{{$item['project_name']}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 form-group">
                    <label for="wt_hours">Hours</label>
                    <input type="number" name="wt_hours" id="wt_hours" class="form-control form-control-sm">
                </div>
                <div class="col-12 form-group">
                    <label for="wt_remarks">Remarks</label>
                    <textarea name="wt_remarks" id="wt_remarks" class="form-control form-control-sm"></textarea>
                </div>
            </div>
        </div>
        <div class="sc-modal-footer text-right">
            <button class="btn btn-sm btn-primary btn-sv" onclick="$('#sv').click()">SAVE</button>
        </div>
    </div>
</div>
@endsection

@section('styles')
<link href="{{asset('/css/custom/cv.css')}}" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo+Black&display=swap" rel="stylesheet">
@endsection('')

@section('scripts')
<script src="{{asset('/plugins/onscan.js')}}" ></script>
<script src="{{asset('/plugins/onscan.min.js')}}" ></script>
<script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="/js/backend/pages/payroll/transaction/employee_information.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const citizenshipSelect = document.getElementById('citizenship');

        fetch('https://restcountries.com/v3.1/all')
        .then(response => response.json())
        .then(data => {
            const citizenshipOptions = data.map(country => {
                // Check if 'demonyms' is available and extract the nationality name
                const demonym = country.demonyms && country.demonyms.eng ? country.demonyms.eng.m : country.name.common;
                return demonym.toUpperCase();
            });

            citizenshipOptions.sort();

            citizenshipOptions.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.textContent = option;
                optionElement.value = option;
                citizenshipSelect.appendChild(optionElement);
            });

        })
        .catch(error => {
            console.error('Error fetching citizenship options:', error);
        });

        setTimeout(() => {
                $('#citizenship').val('FILIPINO');
        }, 1000);
    });
</script>
@endsection

@section('styles-2')
    <link href="{{asset('/css/custom/employee.css')}}" rel="stylesheet">
@endsection
