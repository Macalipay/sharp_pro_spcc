@extends('backend.master.index')

@section('title', 'PROJECT')

@section('breadcrumbs')
    <span>MAINTENANCE</span> / <span class="highlight">PROJECT</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <table id="project_table" class="table table-striped" style="width:100%"></table>
    </div>
</div>

@section('sc-modal')
@parent
<div class="sc-modal-content" id="project_form">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar"></span>
            <span class="sc-close" onclick="scion.create.sc_modal('project_form').hide('all', modalHideFunction)"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <form method="post" id="projectForm" class="form-record">
                <div class="row">
                    <div class="form-group col-md-12">
                        <label>PROJECT NAME</label>
                        <input type="text" class="form-control" id="project_name" name="project_name"/>
                    </div>
                    <div class="form-group col-md-12 project_code">
                        <label>PROJECT CODE</label>
                        <input type="text" class="form-control" id="project_code" name="project_code"/>
                    </div>
                    <div class="form-group col-md-6 region_id">
                        <label>REGION</label>
                        <select name="region_id" id="region_id" class="form-control" onchange="selectRegion()">
                            <option value=""></option>
                            @foreach ($region as $item)
                                <option value="{{$item->region_id}}">{{$item->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-6 province_id">
                        <label>PROVINCE</label>
                        <select name="province_id" id="province_id" class="form-control" onchange="selectProvince()">
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="form-group col-md-4 city_id">
                        <label>CITY</label>
                        <select name="city_id" id="city_id" class="form-control" onchange="selectCity()">
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="form-group col-md-4 barangay_id">
                        <label>BARANGAY</label>
                        <select name="barangay_id" id="barangay_id" class="form-control">
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="form-group col-md-4 postal_code">
                        <label>POSTAL CODE</label>
                        <input type="text" class="form-control" id="postal_code" name="postal_code"/>
                    </div>
                     <div class="form-group col-md-12">
                        <label>ADDRESS</label>
                        <input type="text" class="form-control" id="address" name="address"/>
                    </div>
                    <div class="form-group col-md-12">
                        <label>PROJECT OWNER</label>
                        <input type="text" class="form-control" id="project_owner" name="project_owner"/>
                    </div>
                    <div class="form-group col-md-6">
                        <label>START DATE</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"/>
                    </div>
                    <div class="form-group col-md-6">
                        <label>COMPLETION DATE</label>
                        <input type="date" class="form-control" id="completion_date" name="completion_date"/>
                    </div>
                    <div class="form-group col-md-12">
                        <label>PROJECT COMPLETION</label>
                        <input type="text" class="form-control" id="project_completion" name="project_completion"/>
                    </div>
                    <div class="form-group col-md-6">
                        <label>PROJECT ARCHITECT</label>
                        <input type="text" class="form-control" id="project_architect" name="project_architect"/>
                    </div>
                    <div class="form-group col-md-6">
                        <label>PROJECT CONSULTANT</label>
                        <input type="text" class="form-control" id="project_consultant" name="project_consultant"/>
                    </div>
                    <div class="form-group col-md-6">
                        <label>PROJECT IN-CHARGE</label>
                        <input type="text" class="form-control" id="project_in_charge" name="project_in_charge"/>
                    </div>
                    <div class="form-group col-md-6">
                        <label>CONTRACT PRICE</label>
                        <input type="text" class="form-control" id="contract_price" name="contract_price"/>
                    </div>
                </div>
            </form>
        </div>
        <div class="sc-modal-footer text-right">
            <button class="btn btn-sm btn-primary btn-sv" onclick="$('#sv').click()">SAVE</button>
        </div>
    </div>
</div>

<div class="sc-modal-content" id="employee_modal">
    <div class="sc-modal-dialog">
        <div class="sc-modal-header">
            <span class="sc-title-bar">EMPLOYEE LIST</span>
            <span class="sc-close" onclick="scion.create.sc_modal('employee_modal').hide('all')"><i class="fas fa-times"></i></span>
        </div>
        <div class="sc-modal-body">
            <div class="row">
                <div class="col-12">
                    <div style="font-size:12px;margin-bottom:3px;">EMPLOYEES</div>
                    <ul class="employee-list">
                        @foreach ($employee as $item)
                            <li id="employee_{{$item->id}}">
                                <div class="employee-item" onclick="tagEmployee({{$item->id}})">
                                    {{$item->firstname." ".$item->lastname}}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@endsection

@section('scripts')
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="/js/backend/pages/payroll/maintenance/project.js"></script>
@endsection

@section('styles-2')
    <link href="{{asset('/css/custom/project.css')}}" rel="stylesheet">
@endsection