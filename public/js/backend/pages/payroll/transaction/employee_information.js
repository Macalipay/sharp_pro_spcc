var hold_id = null;
var dy = null;
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
$(function() {
    module_content = 'employee-information';
    module_url = '/payroll/employee-information';
    tab_active = 'general';
    page_title = "";
    actions = 'save';
    module_type = 'transaction';

    scion.centralized_button(true, false, true, true);
    scion.action.tab(tab_active);

    $("#profile_img").cropzee({
        allowedInputs: ['png','jpg','jpeg']
    });

    onScan.attachTo(document, {
        suffixKeyCodes: [13],
        reactToPaste: false,
        onScan: function(sCode, iQty) {
            if(record_id !== null) {
                $('#rfid').val(sCode);
                $('.save').click();
            }
        }
    });
});


// DEFAULT FUNCTION
function success(record) {
    switch(actions) {
        case 'save':
            if(module_content === "work_type_setup") {
                workDetailTable();
                detailsClose();
            }
            else {
                record_id = record.id;
                $('#employee_no').val(record.employee_no);
                actions = 'update';

                $('.tab-list-menu-item ').removeAttr('disabled');
            }

            break;
        case 'update':
            switch(module_content) {
                case 'leaves':
                    $('#leaves_table').DataTable().draw();

                    break;
                case 'educational-background':
                    $('#educational_background_table').DataTable().draw();
                    break;

                case 'work-history':
                    $('#work_history_table').DataTable().draw();
                    break;

                case 'certification':
                    $('#certification_table').DataTable().draw();
                    break;
    
                case 'training':
                    $('#training_table').DataTable().draw();
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
                case 'work_type_setup':
                    workDetailTable();
                    detailsClose();

                    break;
            }
            break;
    }
}

function error() {
    toastr.error('Employee already exist.', 'Failed');
}

function delete_success() {

    switch(module_content) {
        case 'employee-information':
            var form_id = $('.form-record')[0].id;
            $('#'+form_id)[0].reset();
            actions = 'save';
            scion.centralized_button(true, false, true, true);

            break;
        case 'employment':
            $('#classes_id').val('');
            $('#position_id').val('');
            $('#department_id').val('');
            $('#payroll_calendar_id').val('');
            $('#employment_date').val('');
            $('#tax_rate').val('');

            actions = 'update';
            scion.centralized_button(false, false, false, true);
            break;

        case 'leaves':
            $('#leaves_table').DataTable().draw();
            scion.centralized_button(true, false, true, true);

            break;

        case 'educational-backgrond':
            $('#educational_attainment').val('');
            $('#course').val('');
            $('#school_year').val('');
            $('#school').val('');

            actions = 'update';
            scion.centralized_button(false, false, false, true);
            break;

        case 'compensation':
            $('#government_mandated_benefits_table').DataTable().draw();
            $('#other_company_benefits_amount_table').DataTable().draw();
            scion.centralized_button(true, false, true, true);

            break;
            
        case 'work_type_setup':
            workDetailTable();
            detailsClose();

            break;
    }

}

function delete_error() {}

function generateData() {
    switch(module_content) {
        case 'employee-information':
            form_data = {
                _token: _token,
                employment_date: $('#employment_date').val(),
                department_id: $('#department_id').val(),
                classes_id: $('#classes_id').val(),
                position_id: $('#position_id').val(),
                payroll_calendar_id: $('#payroll_calendar_id').val(),
                tax_rate: $('#tax_rate').val(),
                firstname: $('#firstname').val(),
                middlename: $('#middlename').val(),
                lastname: $('#lastname').val(),
                suffix: $('#suffix').val(),
                rfid: $('#rfid').val(),
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
                birthplace: $('#birthplace').val(),
                civil_status: $('#civil_status').val(),
                employment_status: $('#employment_status').val(),
                emergency_relationship: $('#emergency_relationship').val(),
                bank_name: $('#bank_name').val(),
                bank_account_no: $('#bank_account_no').val(),
                classes_id: $('#classes_id').val(),
                position_id: $('#position_id').val(),
                department_id: $('#department_id').val(),
                payroll_calendar_id: $('#payroll_calendar_id').val(),
                employment_date: $('#employment_date').val(),
                employment_type: $('#employment_type').val(),
                tax_rate: $('#tax_rate').val(),
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
                payroll_calendar_id: $('#payroll_calendar_id').val(),
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
                console.log('test');
            form_data = {
                _token: _token,
                employee_id: record_id,
                annual_salary: $('#annual_salary').val(),
                monthly_salary: $('#monthly_salary').val(),
                semi_monthly_salary: $('#semi_monthly_salary').val(),
                weekly_salary: $('#weekly_salary').val(),
                daily_salary: $('#daily_salary').val(),
                hourly_salary: $('#hourly_salary').val(),
                tax: $('#tax').val(),
                government_mandated_benefits: '1',
                other_company_benefits: '1',
                sss: $('#sss').val(),
                phic: $('#phic').val(),
                pagibig: $('#pagibig').val(),
                
            };
            break;
        case 'educational-background':
            form_data = {
                _token: _token,
                employee_id: record_id,
                educational_attainment: $('#educational_attainment').val(),
                course: $('#course').val(),
                school_year: $('#school_year').val(),
                school: $('#school').val(),
            };
            break;
        case 'work-history':
                form_data = {
                    _token: _token,
                    employee_id: record_id,
                    company: $('#company').val(),
                    position: $('#position').val(),
                    date_hired: $('#date_hired').val(),
                    date_of_resignation: $('#date_of_resignation').val(),
                    remarks: $('#remarks').val(),
                };
            break;
        case 'certification':
            form_data = {
                _token: _token,
                employee_id: record_id,
                certification_no: $('#certification_no').val(),
                certification_name: $('#certification_name').val(),
                certification_authority: $('#certification_authority').val(),
                certification_description: $('#certification_description').val(),
                certification_date: $('#certification_date').val(),
                certification_expiration_date: $('#certification_expiration_date').val(),
                certification_level: $('#certification_level').val(),
                certification_status: $('#certification_status').val(),
                certification_achievements: $('#certification_achievements').val(),
                certification_renewal_date: $('#certification_renewal_date').val(),
                recertification_date: $('#recertification_date').val(),

            };
            break;
        case 'training':
            form_data = {
                _token: _token,
                employee_id: record_id,
                training_no: $('#training_no').val(),
                training_name: $('#training_name').val(),
                training_provider: $('#training_provider').val(),
                training_description: $('#training_description').val(),
                training_date: $('#training_date').val(),
                training_location: $('#training_location').val(),
                training_duration: $('#training_duration').val(),
                training_outcome: $('#training_outcome').val(),
                training_type: $('#training_type').val(),
                expiration_date: $('#expiration_date').val(),

            };
            break;
        case 'clearance':
            form_data = {
                _token: _token,
                employee_id: record_id,
                clearance_date: $('#clearance_date').val(),

            };
            break;
        case 'work_type_setup':
            form_data = {
                _token: _token,
                employee_id: hold_id,
                days: dy,
                worktype_id: $('#wt_worktype_id').val(),
                earnings: $('#wt_earnings').val(),
                project_id: $('#project_id').val(),
                hours: $('#wt_hours').val(),
                remarks: $('#wt_remarks').val()
            };
            break;
    }

    return form_data;
}

function generateDeleteItems() {
    switch(module_content) {
        case 'employee-information':
            delete_data = [record_id];
            break;
    }
}

// EXTRA FUNCTION
function general_func() {
    module_content = 'employee-information';
    module_url = '/payroll/employee-information';
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

    $('.earning-item').removeClass('selected');
    $('.allowance-item').removeClass('selected');

    $.get(`/payroll/earning_setup/get/${record_id}`, function(response) {
        response.earning.forEach(earning => {
            $(`#earning_${earning.earning_id}`).addClass('selected');
        });
    });
    
    $.get(`/payroll/allowance_setup/get/${record_id}`, function(response) {
        response.allowance.forEach(allowance => {
            $(`#allowance_${allowance.allowance_id}`).addClass('selected');
        });
    });
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
            { data: "leave_types.leave_name", title: "LEAVE NAME" },
            { data: "total_hours", title: "TOTAL DAYS" },
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
    getAllowance();
    getProject();

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

function educational_background_func() {
    module_content = 'educational-background';
    module_url = '/payroll/educational_background';
    actions = 'update';
    module_type = 'sub_transaction';
    scion.centralized_button(true, false, true, true);

    if ($.fn.DataTable.isDataTable('#educational_background_table')) {
        $('#educational_background_table').DataTable().destroy();
    }

    scion.create.table(
        'educational_background_table',
        module_url + '/get/' + record_id,
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                return html;
            }},
            { data: "educational_attainment", title: "EDUCATIONAL ATTAINMENT" },
            { data: "course", title: "COURSE" },
            { data: "school_year", title: "SCHOOL YEAR" },
            { data: "school", title: "SCHOOL" },
        ], 'Bfrtip', []
    );
}

function work_history_func() {
    module_content = 'work-history';
    module_url = '/payroll/work_history';
    actions = 'update';
    module_type = 'sub_transaction';
    scion.centralized_button(true, false, true, true);

    if ($.fn.DataTable.isDataTable('#work_history_table')) {
        $('#work_history_table').DataTable().destroy();
    }

    scion.create.table(
        'work_history_table',
        module_url + '/get/' + record_id,
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                return html;
            }},
            { data: "company", title: "COMPANY" },
            { data: "position", title: "POSITION" },
            { data: "date_hired", title: "DATE HIRED" },
            { data: "date_of_resignation", title: "DATE OF RESIGNATION" },
        ], 'Bfrtip', []
    );

}

function certification_func() {
    alert('test');
    module_content = 'certification';
    module_url = '/payroll/certification';
    actions = 'update';
    module_type = 'sub_transaction';
    scion.centralized_button(false, true, true, true);

    if ($.fn.DataTable.isDataTable('#certification_table')) {
        $('#certification_table').DataTable().destroy();
    }

    scion.create.table(
        'certification_table',
        module_url + '/get/' + record_id,
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                return html;
            }},
            { data: "certification_no", title: "CERTIFICATION NO" },
            { data: "certification_name", title: "CERTIFICATION NAME" },
            { data: "certification_authority", title: "CERTIFICATION AUTHORITY" },
            { data: "certification_date", title: "DATE OF CERTIFICATION" },
        ], 'Bfrtip', []
    );

}

function training_func() {
    module_content = 'training';
    module_url = '/payroll/training';
    actions = 'update';
    module_type = 'sub_transaction';
    scion.centralized_button(true, false, true, true);

    if ($.fn.DataTable.isDataTable('#training_table')) {
        $('#training_table').DataTable().destroy();
    }

    scion.create.table(
        'training_table',
        module_url + '/get/' + record_id,
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                return html;
            }},
            { data: "training_no", title: "TRAINING NO" },
            { data: "training_name", title: "TRAINING NAME" },
            { data: "training_provider", title: "TRAINING PROVIDER" },
            { data: "training_date", title: "DATE OF TRAINING" },
        ], 'Bfrtip', []
    );

}

function cv_func() {
    module_content = 'cv';
    module_url = '/payroll/cv';
    actions = 'update';
    module_type = 'sub_transaction';
    selected_print = 'cvPrint';
    modal_content = 'cv';
    scion.centralized_button(true, false, true, false);

    $.get('/payroll/employee-information/getCV/' + record_id, function(response) {

        emp_info = response.employee[0];

        var date = emp_info.employments_tab.employment_date;
        var dateObj = new Date(date);

        var options = { year: 'numeric', month: 'long', day: 'numeric' };
        var formattedDate = dateObj.toLocaleDateString('en-US', options);

        $('#cv_name').text(emp_info.firstname + emp_info.middlename + emp_info.lastname);
        $('#cv_position').text(emp_info.employments_tab.positions.description);
        $('#cv_contact').text(emp_info.phone1);
        $('#cv_email').text(emp_info.email);
        $('#cv_employment_date').text(formattedDate);
        $('#cv_department').text(emp_info.employments_tab.departments.description);
        $('#cv_img').attr('src', '/images/payroll/employee-information/'+ emp_info.profile_img);
    

        $.each(response.education, (i,v)=>{
            $('#education-container').append(`
                <div class="cv-info-container">
                    <p class="cv-company-info">${v.school} <span class="cv-year">${v.school_year}</span></p> 
                    <p class="cv-company-title">${v.educational_attainment}</p>
                </div>
            `);
        });

        $.each(response.certification, (i,v)=>{
            $('#certification-container').append(`
                <div class="cv-info-container">
                    <p class="cv-company-info">${v.certification_name}<span class="cv-year">${v.certification_date}</span></p> 
                    <p class="cv-company-title">${v.certification_authority}</p>
                    <p class="cv-company-title">${v.certification_no}</p>
                </div>
            `);
        });

        $.each(response.training, (i,v)=>{
            $('#training-container').append(`
                <div class="cv-info-container">
                    <p class="cv-company-info">${v.training_name}<span class="cv-year">${v.training_date}</span></p> 
                    <p class="cv-company-title">${v.training_provider}</p>
                    <p class="cv-company-title">${v.training_no}</p>
                </div>
            `);
        });

    })

}

function clearance_func() {
    module_content = 'clearance';
    module_url = '/payroll/clearance';
    actions = 'update';
    module_type = 'sub_transaction';
    modal_content = 'clearance';
    scion.centralized_button(true, false, true, true);

    $.get('/payroll/clearance/get/' + record_id, function(response) {
        $('#clearance_date').val(response.clearance.clearance_date);
    })

}

function salary(response) {
    $.each(Object.keys(response.data), function(i, v) {
        if(response.data[v] !== false) {
            $('#' + v + '_salary').val(response.data[v].toFixed(2));
        }
    });
}

function earningSetup(id) {
    if($(`#earning_${id}`).hasClass("selected")) {
        $.post('/payroll/earning_setup/destroy', {_token:_token, earning_id:id, employee_id:record_id}).done(function(response){
            $(`#earning_${id}`).removeClass('selected');
        });
    }
    else {
        $.post('/payroll/earning_setup/save', {_token:_token, earning_id:id, employee_id:record_id}).done(function(response){
            $(`#earning_${id}`).addClass('selected');
        });
    }
}

function allowanceSetup(id) {
    if($(`#allowance_${id}`).hasClass("selected")) {
        $.post('/payroll/allowance_setup/destroy', {_token:_token, allowance_id:id, employee_id:record_id}).done(function(response){
            $(`#allowance_${id}`).removeClass('selected');
        });
    }
    else {
        $.post('/payroll/allowance_setup/save', {_token:_token, allowance_id:id, employee_id:record_id}).done(function(response){
            $(`#allowance_${id}`).addClass('selected');
        });
    }
}

function getPosition() {
    var selectedValue = $('#position_id').val();

    if(actions == 'save') {

        $.get('/payroll/employee-information/getPosition/' + selectedValue, function(resp) {
            if (resp.exists) {
                alert('The position is already taken.');
                $('#position_id').val('');

            } else {
                // alert('The position is available.');
            }
        }).fail(function() {
            alert('An error occurred while checking the position.');
        });

    }
}

function selectRegion() {
    $.get('/address/province/'+$('#country_1').val(), function(response) {
        var html = '';
        html += '<option value=""></option>';
        response.province.forEach(province => {
            html += `<option value="${province.province_id}">${province.name}</option>`;
        });

        $('#province_1').html(html);

        if(record_id !== null) {
            $('#province_1').val(store_record.project.province_1);
            selectProvince();
        }
    });
}

function selectProvince() {
    $.get('/address/city/'+$('#province_1').val(), function(response) {
        var html = '';
        html += '<option val=""></option>';
        response.city.forEach(city => {
            html += `<option value="${city.city_id}">${city.name}</option>`;
        });

        $('#city_1').html(html);
        
        if(record_id !== null) {
            $('#city_1').val(store_record.project.city_1);
            selectCity();
        }
        console.log('ss');
    });
}

function selectCity() {
    $.get('/address/barangay/'+$('#city_1').val(), function(response) {
        var html = '';
        html += '<option val=""></option>';
        response.barangay.forEach(barangay => {
            html += `<option value="${barangay.id}">${barangay.name}</option>`;
        });

        $('#barangay_1').html(html);

        if(record_id !== null) {
            $('#barangay_1').val(store_record.project.barangay_id);
        }
    });
}

function addAllowance() {
    scion.create.sc_modal("allowance_modal", "Allowance").show();
}

function tagAllowance(id) {
    var data = {
        _token: _token,
        employee_id: record_id, 
        allowance_id: id,
        amount: 0,
        action: $(`#allowance_${id} .allowance-item`).hasClass('selected')?'remove':'add'
    };

    $.post('/payroll/allowance/tag', data).done((response)=>{
        if($(`#allowance_${id} .allowance-item`).hasClass('selected')) {
            $(`#allowance_${id} .allowance-item`).removeClass('selected');
        }
        else {
            $(`#allowance_${id} .allowance-item`).addClass('selected');
        }
        getAllowance();
    });
}

function getAllowance() {
    $('.allowance-available').html('');
    $.post('/payroll/allowance/get-tag', { _token: _token, employee_id: record_id }).done((response)=>{
        $.each(response.allowance, (i,v)=>{
            $(`#allowance_${v.allowance_id} .allowance-item`).addClass('selected');
            $('.allowance-available').append(`<span class="allowance-eligible">${v.allowances.name}</span>`)
        });
    });
}

function addProject() {
    scion.create.sc_modal("project_modal", "Projects").show();
}

function tagProject(id) {
    var data = {
        _token: _token,
        employee_id: record_id, 
        project_id: id,
        action: $(`#project_${id} .project-item`).hasClass('selected')?'remove':'add'
    };

    $.post('/purchasing/project/tag', data).done((response)=>{
        if($(`#project_${id} .project-item`).hasClass('selected')) {
            $(`#project_${id} .project-item`).removeClass('selected');
        }
        else {
            $(`#project_${id} .project-item`).addClass('selected');
        }
        getProject();
    });
}

function getProject() {
    $('.project-available').html('');
    $.post('/purchasing/project/get-tag', { _token: _token, employee_id: record_id }).done((response)=>{
        $.each(response.project, (i,v)=>{
            $(`#project_${v.project_id} .project-item`).addClass('selected');
            $('.project-available').append(`<span class="allowance-eligible">${v.project.project_name}</span>`)
        });
    });
}

function lookupReturn() {

    var status = $('#status').val();
    if(status === "2" || status === "3" || status === "4" || status === "5" || status === "9" ) {
        $('#clearance').css('display', 'block');
    }
    else {
        $('#clearance').css('display', 'none');
    }

}

function workTypeModal(day) {

    module_content = 'work_type_setup';
    module_url = '/payroll/work_type_setup';
    hold_id = record_id;
    record_id = null;

    var today = new Date();
    var dd = String(today.getDate()).padStart(2, '0');
    var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
    var yyyy = today.getFullYear();

    today = yyyy + '-' + mm + '-' + dd;

    var start = new Date(today + " " + $(`#${day}_start_time`).val());
    var end = new Date(today + " " + $(`#${day}_end_time`).val());

    dy = day;

    $('#wt_employee_name').text(store_record.employee.firstname + " " + store_record.employee.lastname);
    $('#wt_total_hours').text(calculateTotalHours(start, end));
    
    workDetailTable();

    scion.create.sc_modal("worktype_modal", "Work Type Setup").show();
}

function add_details() {
    actions = 'save';
    scion.create.sc_modal("add_details", 'ADD SETUP').show(() => {
        scion.centralized_button(true, false, true, true);
    });
}

function workDetailTable() {
    var total = 0;

    $.get(`${module_url}/get/${hold_id}/${dy}`, (response) => {
        var table = "";
        $.each(response.worktypedetails, (i,v)=> {
            table += `<tr>
                <td><a href="#" onclick="editWorkDetail(${v.id})"><i class="fas fa-pencil-alt"></i></a> <a href="#" onclick="deleteWorkDetail(${v.id})"><i class="fas fa-trash-alt"></i></a></td>
                <td>${v.worktype.name}</td>
                <td>${v.earnings === "RE"?'REGULAR':'OVER TIME'}</td>
                <td>${v.projects.project_name}</td>
                <td>${v.hours}</td>
                <td>${v?.remarks||''}</td>
            </tr>`;

            total += parseFloat(v.hours);
        });

        $('#status_rendered').html(parseFloat($('#total_hours').text()) === total?`<span class="text-success">Total hours are matched.</span>`:`<span class="text-danger">Total hours don't matched.</span>`);
        parseFloat($('#total_hours').text()) === total

        $('.total_hours_rendered').text(total.toFixed(2));
        $('#work_details_table tbody').html(table);
    });
}

function calculateTotalHours(startTime, endTime) {
    const start = new Date(startTime);
    const end = new Date(endTime);

    const differenceInMilliseconds = end - start;

    const hours = differenceInMilliseconds / (1000 * 60 * 60);

    return hours < 0 ? hours + 24 : hours;
}

function worktypesetupClose() {
    module_content = 'work-calendar';
    module_url = '/payroll/work-calendar';
    actions = 'update';
    record_id = hold_id;
}


function editWorkDetail(id) {
    actions = 'update';
    record_id = id;
    
    $.get(`${module_url}/edit/${id}`, function(response) {
        console.log(response);
        var work = response.worktypedetails;

        $('#wt_worktype_id').val(work.worktype_id);
        $('#wt_earnings').val(work.earnings);
        $('#wt_hours').val(work.hours);
        $('#wt_remarks').val(work.remarks);
        
        scion.create.sc_modal("add_details", 'Update').show(() => {
            scion.centralized_button(true, false, true, true);
        });
    });
}

function deleteWorkDetail(id) {
    delete_data.push(id);
    scion.record.delete(generateDeleteItems);
}

function detailsClose() {
    scion.create.sc_modal('add_details').hide();
    record_id = null;
    scion.centralized_button(true, true, true, true);
}