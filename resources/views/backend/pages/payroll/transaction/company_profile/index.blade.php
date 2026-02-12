@extends('backend.master.index')

@section('title', 'COMPANY PROFILE')

@section('breadcrumbs')
    <span>TRANSACTION</span>  / <span>PAYROLL</span> / <span class="highlight">COMPANY PROFILE</span>
@endsection

@section('action-header')
    @include('backend.partial.component.action_buttons')
@endsection

@section('left-content')
    @include('backend.partial.component.tab_list', [
        'id'=>'company_menu', 
        'data'=>array(
            array('id'=>'general', 'icon'=>' fas fa-file-alt', 'title'=>'GENERAL', 'active'=>true, 'disabled'=>false, 'function'=>false),
            array('id'=>'visual_appearance', 'icon'=>' fas fa-eye', 'title'=>'VISUAL APPEARANCE', 'active'=>false, 'disabled'=>false, 'function'=>false),
            array('id'=>'work_calendar', 'icon'=>' fas fa-calendar', 'title'=>'WORK CALENDAR', 'active'=>false, 'disabled'=>false, 'function'=>false),
            array('id'=>'account', 'icon'=>' fas fa-list', 'title'=>'ACCOUNT', 'active'=>false, 'disabled'=>false, 'function'=>false)
        )
    ])
@endsection

@section('content')
<div class="row" style="height:100%;">
    <div class="col-12" style="height:100%;">
        <div class="tab" style="height:100%;">
            <div class="tab-content">
                <form class="form-record" method="post" id="companyProfile">
                    <div id="general_tab" class="form-tab">
                        <h3>COMPANY INFORMATION</h3>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    @include('backend.partial.component.lookup', [
                                        'label' => "COMPANY NO", 
                                        'placeholder' => '<NEW>',
                                        'id' => "company_no", 
                                        'title' => "COMPANY NO", 
                                        'url' => "/payroll/company-profile/get", 
                                        'data' => array(
                                            array('data' => "DT_RowIndex", 'title' => "#"), 
                                            array('data' => "company_name", 'title' => "Company Name")
                                        ),
                                        'disable' => true,
                                        'lookup_module' => 'company-profile',
                                        'modal_type' => 'single',
                                        'lookup_type' => 'main'
                                    ])
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group company_name">
                                    <label>COMPANY NAME <span class="required">*</span></label>
                                    <input type="text" class="form-control" name="company_name" id="company_name"/>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group email">
                                    <label>EMAIL <span class="required">*</span></label>
                                    <input type="email" class="form-control" name="email" id="email"/>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group contact_number">
                                    <label>CONTACT NUMBER</label>
                                    <input type="number" class="form-control" name="contact_number" id="contact_number"/>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group address">
                                    <label>ADDRESS</label>
                                    <textarea name="address" id="address" class="form-control"></textarea>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group legal_name">
                                    <label>LEGAL NAME <span class="required">*</span></label>
                                    <input type="text" class="form-control" name="legal_name" id="legal_name"/>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group tin">
                                    <label>TIN NUMBER</label>
                                    <input type="number" class="form-control" name="tin" id="tin"/>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group details">
                                    <label>DETAILS</label>
                                    <textarea name="details" id="details" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="visual_appearance_tab" class="form-tab">
                        <h3>VISUAL APPEARANCE</h3>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>COMPANY LOGO</label>
                                    <div id="" onclick="$('#company_logo').click()">
                                        <img src="/images/payroll/company-profile/default.png" alt="" width="200px" class="image-previewer" id="viewer" data-cropzee="company_logo">
                                    </div>
                                    <input id="company_logo" type="file" name="company_logo" class="form-control" onchange="scion.fileView(event)" style="display:none;">
                                    <button class="btn btn-primary" type="button" onclick="$('#company_logo').click()">Select Photo</button>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group color">
                                    <label>COLOR</label>
                                    <input type="color" class="form-control" name="color" id="color"/>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>DARK MODE</label>
                                <select name="dark_mode" id="dark_mode" class="form-control">
                                    <option value="0">DISABLE</option>
                                    <option value="1">ENABLE</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="work_calendar_tab" class="form-tab">
                        <h3>WORK CALENDAR</h3>
                        <div class="row">
                            <div class="col-12">
                                <table class="sc-table">
                                    <thead>
                                        <th>Day</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Sunday</td>
                                            <td>
                                                <input type="time" id="sunday_start_time" class="form-control">
                                            </td>
                                            <td>
                                                <input type="time" id="sunday_end_time" class="form-control">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Monday</td>
                                            <td>
                                                <input type="time" id="monday_start_time" class="form-control">
                                            </td>
                                            <td>
                                                <input type="time" id="monday_end_time" class="form-control">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Tuesday</td>
                                            <td>
                                                <input type="time" id="tuesday_start_time" class="form-control">
                                            </td>
                                            <td>
                                                <input type="time" id="tuesday_end_time" class="form-control">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Wednesday</td>
                                            <td>
                                                <input type="time" id="wednesday_start_time" class="form-control">
                                            </td>
                                            <td>
                                                <input type="time" id="wednesday_end_time" class="form-control">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Thursday</td>
                                            <td>
                                                <input type="time" id="thursday_start_time" class="form-control">
                                            </td>
                                            <td>
                                                <input type="time" id="thursday_end_time" class="form-control">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Friday</td>
                                            <td>
                                                <input type="time" id="friday_start_time" class="form-control">
                                            </td>
                                            <td>
                                                <input type="time" id="friday_end_time" class="form-control">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Saturday</td>
                                            <td>
                                                <input type="time" id="saturday_start_time" class="form-control">
                                            </td>
                                            <td>
                                                <input type="time" id="saturday_end_time" class="form-control">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="account_tab" class="form-tab">
                        <h3>ACCOUNT</h3>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>BANK ACCOUNT</label>
                                <select name="bank_account" id="bank_account" class="form-control">
                                    <option value=""></option>
                                    @foreach ($record as $item)
                                    <option value="{{$item->id}}">{{$item->account_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>PAYROLL LIABILITY</label>
                                <select name="payroll_liability" id="payroll_liability" class="form-control">
                                    <option value=""></option>
                                    @foreach ($record as $item)
                                    <option value="{{$item->id}}">{{$item->account_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>SALARY EXPENSE</label>
                                <select name="salary_expense" id="salary_expense" class="form-control">
                                    <option value=""></option>
                                    @foreach ($record as $item)
                                    <option value="{{$item->id}}">{{$item->account_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>SALARY PAYABLE</label>
                                <select name="salary_payable" id="salary_payable" class="form-control">
                                    <option value=""></option>
                                    @foreach ($record as $item)
                                    <option value="{{$item->id}}">{{$item->account_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-12">
                                <label>OPENING BALANCE DATE</label>
                                <input type="date" class="form-control" max="9999-12-31" name="opening_balance_date" id="opening_balance_date"/>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


@section('scripts')
<script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script>
    $(function() {
        modal_content = 'company-profile';
        module_url = '/payroll/company-profile';
        tab_active = 'general';
        actions = 'save';
        module_type = 'transaction';

        scion.centralized_button(true, false, true, true);
        scion.action.tab(tab_active);
        
        $("#company_logo").cropzee({
            allowedInputs: ['png','jpg','jpeg']
        });
    });

    function payrunType() {
        var payrun_type = $('#payrun_type').val();
        if(payrun_type === "weekly"){
            $('.pr_settings').css('display', 'block');
        }
        else {
            $('.pr_settings').css('display', 'none');
        }
        console.log(payrun_type);
    }
    
    function success(record) {
        switch(actions) {
            case 'save':
                record_id = record.id;
                $('#company_no').val(record.company_no);
                actions = 'update';
                break;
            case 'update':
                break;
        }
    }

    function error() {}

    function delete_success() {
        var form_id = $('.form-record')[0].id;
        $('#'+form_id)[0].reset();

        actions = 'save';
        scion.centralized_button(true, false, true, true);
    }

    function delete_error() {}

    function generateData() {
        form_data = {
            _token: _token,
            company_name: $('#company_name').val(),
            email: $('#email').val(),
            contact_number: $('#contact_number').val(),
            address: $('#address').val(),
            legal_name: $('#legal_name').val(),
            tin: $('#tin').val(),
            company_logo: ($('#company_logo').val() !== ''?cropzeeGetImage('company_logo'):''),
            theme: '',
            color: $('#color').val(),
            details: $('#details').val(),
            dark_mode: $('#dark_mode').val(),
            bank_account: $('#bank_account').val(),
            payroll_liability: $('#payroll_liability').val(),
            salary_expense: $('#salary_expense').val(),
            salary_payable: $('#salary_payable').val(),
            opening_balance_date: $('#opening_balance_date').val(),
            company_work_calendar: {
                sunday_start_time: $('#sunday_start_time').val(),
                sunday_end_time: $('#sunday_end_time').val(),
                monday_start_time: $('#monday_start_time').val(),
                monday_end_time: $('#monday_end_time').val(),
                tuesday_start_time: $('#tuesday_start_time').val(),
                tuesday_end_time: $('#tuesday_end_time').val(),
                wednesday_start_time: $('#wednesday_start_time').val(),
                wednesday_end_time: $('#wednesday_end_time').val(),
                thursday_start_time: $('#thursday_start_time').val(),
                thursday_end_time: $('#thursday_end_time').val(),
                friday_start_time: $('#friday_start_time').val(),
                friday_end_time: $('#friday_end_time').val(),
                saturday_start_time: $('#saturday_start_time').val(),
                saturday_end_time: $('#saturday_end_time').val()
            }
        };

        return form_data;
    }
</script>
@endsection

