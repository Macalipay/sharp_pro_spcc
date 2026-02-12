$(function() {
    module_content = '201-file';
    module_url = '/payroll/201-file';
    tab_active = 'general';
    page_title = "";
    actions = 'save';
    module_type = 'transaction';

    scion.centralized_button(true, false, true, true);
    scion.action.tab(tab_active);
    
    $("#profile_img").cropzee({
        allowedInputs: ['png','jpg','jpeg']
    });
});


// DEFAULT FUNCTION
function success(record) {
    switch(actions) {
        case 'save':
            record_id = record.id;
            $('#employee_no').val(record.employee_no);
            actions = 'update';

            $('.tab-list-menu-item ').removeAttr('disabled');

            break;
        case 'update':
            switch(module_content) {
                case 'leaves':
                    $('#leaves_table').DataTable().draw();
                    
                    break;
                case 'work-calendar':
                    $('#edit_schedule')[0].click();
                    scion.centralized_button(true, true, true, true);
                    break;
                case 'compensation':
                    $('#government_mandated_benefits').val(''); 
                    $('#government_mandated_benefits_amount').val(''); 
                    $('#other_company_benefits').val(''); 
                    $('#other_company_benefits_amount').val(''); 
                    
                    

                    break;
            }
            break;
    }
}

function error() {}

function delete_success() {

    switch(module_content) {
        case '201-file':
            var form_id = $('.form-record')[0].id;
            $('#'+form_id)[0].reset();
            actions = 'save';
            scion.centralized_button(true, false, true, true);
            
            break;
        case 'employment':
            $('#classes_id').val('');
            $('#position_id').val('');
            $('#department_id').val('');
            $('#employment_date').val('');
            $('#tax_rate').val('');

            actions = 'update';
            scion.centralized_button(false, false, false, true);
            break;
        case 'leaves':
            $('#leaves_table').DataTable().draw();
            scion.centralized_button(true, false, true, true);

            break;
        case 'compensation':
            $('#government_mandated_benefits_table').DataTable().draw();
            $('#other_company_benefits_amount_table').DataTable().draw();
            scion.centralized_button(true, false, true, true);

            break;
    }

}

function delete_error() {}

function generateData() {
    switch(module_content) {
        case '201-file':
            form_data = {
                _token: _token,
                firstname: $('#firstname').val(),
                middlename: $('#middlename').val(),
                lastname: $('#lastname').val(),
                suffix: $('#suffix').val(),
                birthdate: $('#birthdate').val(),
                gender: $('#gender').val(),
                citizenship: $('#citizenship').val(),
                phone1: $('#phone1').val(),
                phone2: $('#phone2').val(),
                street_1: $('#street_1').val(),
                barangay_1: $('#barangay_1').val(),
                city_1: $('#city_1').val(),
                province_1: $('#province_1').val(),
                country_1: $('#country_1').val(),
                zip_1: $('#zip_1').val(),
                street_2: $('#street_2').val(),
                barangay_2: $('#barangay_2').val(),
                city_2: $('#city_2').val(),
                province_2: $('#province_2').val(),
                country_2: $('#country_2').val(),
                zip_2: $('#zip_2').val(),
                emergency_name: $('#emergency_name').val(),
                emergency_no: $('#emergency_no').val(),
                email : $('#email').val(),
                bank_account : $('#bank_account').val(),
                tin_number : $('#tin_number').val(),
                sss_number : $('#sss_number').val(),
                pagibig_number : $('#pagibig_number').val(),
                philhealth_number : $('#philhealth_number').val(),
                status: $('#status').val(),
                profile_img: ($('#profile_img').val() !== ''?cropzeeGetImage('profile_img'):'')
            };
            break;
        case 'employment':
            form_data = {
                _token: _token,
                employee_id: record_id,
                classes_id: $('#classes_id').val(),
                position_id: $('#position_id').val(),
                department_id: $('#department_id').val(),
                employment_date: $('#employment_date').val(),
                tax_rate: $('#tax_rate').val()
            };
            break;
        case 'leaves':
            form_data = {
                _token: _token,
                employee_id: record_id,
                leave_type: $('#leave_type').val(),
                total_hours: $('#total_hours').val()
            };
            break;
        case 'work-calendar':
            form_data = {
                _token: _token,
                employee_id: record_id,
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
            };
            break;
        case 'compensation':
            form_data = {
                _token: _token,
                employee_id: record_id,
                monthly_compensation: $('#monthly_compensation').val(),
                annual_compensation: $('#annual_compensation').val(),
                tax: $('#tax').val(),
                government_mandated_benefits: '1',
                other_company_benefits: '1',
                government_mandated: {
                    benefits_id: $('#government_mandated_benefits').val(),
                    amount: $('#government_mandated_benefits_amount').val()
                },
                other_company: {
                    benefits_id: $('#other_company_benefits').val(),
                    amount: $('#other_company_benefits_amount').val()
                }
            };

            break;
    }

    return form_data;
}

function generateDeleteItems() {
    switch(module_content) {
        case '201-file':
            delete_data = [record_id];
            break;
    }
}

// EXTRA FUNCTION
function general_func() {
    module_content = '201-file';
    module_url = '/payroll/201-file';
    module_type = 'transaction';

    if(record_id !== '') {
        actions = 'update';
    }
    
    scion.centralized_button(false, false, false, true);
}

function employment_func() {
    module_content = 'employment';
    module_url = '/payroll/employment';
    module_type = 'sub_transaction';
    actions = 'update';
}

function leaves_func() {
    module_content = 'leaves';
    module_url = '/payroll/leaves';
    actions = 'update';
    module_type = 'sub_transaction';
    scion.centralized_button(true, false, true, true);
    
    if ($.fn.DataTable.isDataTable('#leaves_table')) {
        $('#leaves_table').DataTable().destroy();
    }

    scion.create.table(
        'leaves_table',  
        module_url + '/get/' + record_id, 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                return html;
            }},
            { data: "leave_type.leave_name", title: "LEAVE NAME" },
            { data: "total_hours", title: "TOTAL HOURS" },
        ], 'Bfrtip', []
    );
}

function work_calendar_func() {
    module_content = 'work-calendar';
    module_url = '/payroll/work-calendar';
    actions = 'update';
    module_type = 'sub_transaction';
    scion.centralized_button(true, true, true, true);
}

function customSchedule(){
    var custom_sched = $('#edit_schedule')[0];

    $('#work_calendar_tab input[type="time"]').prop('disabled', custom_sched.checked===true?false:true);
    scion.centralized_button(true, custom_sched.checked===true?false:true, true, true);
}

function compensation_func() {
    module_content = 'compensation';
    module_url = '/payroll/compensation';
    actions = 'update';
    module_type = 'sub_transaction';
    scion.centralized_button(true, false, true, true);

    var government_mandated_benefits = "<option value=''></option>";
    var other_company_benefits = "<option value=''></option>";
    
    if ($.fn.DataTable.isDataTable('#government_mandated_benefits_table') && $.fn.DataTable.isDataTable('#other_company_benefits_amount_table')) {
        $('#government_mandated_benefits_table').DataTable().destroy();
        $('#other_company_benefits_amount_table').DataTable().destroy();
    }

    $.post('/payroll/benefits/governmentMandated', { _token: _token}, function(response){
        $.each(response.benefits, (i,v)=>{
            government_mandated_benefits += "<option value='"+v.id+"'>"+v.benefits+"</option>";
        });
        $('#government_mandated_benefits').html(government_mandated_benefits);
    });

    $.post('/payroll/benefits/otherCompany', { _token: _token}, function(response){
        $.each(response.benefits, (i,v)=>{
            other_company_benefits += "<option value='"+v.id+"'>"+v.benefits+"</option>";
        });
        $('#other_company_benefits').html(other_company_benefits);
    });

    scion.create.table(
        'government_mandated_benefits_table',  
        module_url + '/get-gov-record/' + record_id, 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                return html;
            }},
            { data: "benefits.benefits", title: "BENEFITS" },
            { data: "amount", title: "AMOUNT" },
        ], 'Bfrtip', []
    );
    
    scion.create.table(
        'other_company_benefits_amount_table',  
        module_url + '/get-com-record/' + record_id, 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                return html;
            }},
            { data: "benefits.benefits", title: "BENEFITS" },
            { data: "amount", title: "AMOUNT" },
        ], 'Bfrtip', []
    );
}