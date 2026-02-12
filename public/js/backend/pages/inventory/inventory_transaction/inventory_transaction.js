$(function() {
    modal_content = 'employee';
    module_url = '/payroll/employee-profile';
    module_type = 'custom';
    page_title = "EMPLOYEE PROFILE";

    scion.centralized_button(false, true, true, true);
    
    scion.create.table(
        'employee_table',  
        module_url + '/get', 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += `<a href="#" class="align-middle edit" onclick="scion.record.edit('${module_url}/edit/', ${row.id})"><i class="fas fa-pen"></i></a>`;
                return html;
            }},
            { data: "employee_no", title:"EMPLOYEE NO." },
            {
                data: "firstname",
                title: "NAME",
                render: function(data, type, row, meta) {
                    var fullName = row.firstname + " ";
                    if (row.middlename) fullName += row.middlename + " ";
                    fullName += row.lastname;
                    if (row.suffix) fullName += " " + row.suffix;
                    return '<span title="' + fullName.trim() + '">' + fullName.trim() + '</span>';
                }
            },
            {
                data: "birthdate",
                title: "BIRTHDATE",
                render: function(data, type, row, meta) {
                    return '<span title="' + moment(row.birthdate).format('MMM DD, YYYY') + '">' + moment(row.birthdate).format('MMM DD, YYYY') + '</span>';
                }
            },
            {
                data: "gender",
                title: "GENDER",
                render: function(data, type, row, meta) {
                    return '<span title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "employment_date",
                title: "EMPLOYMENT DATE",
                render: function(data, type, row, meta) {
                    if (row.employments_tab && row.employments_tab.employment_date) {
                        return '<span title="' + moment(row.employments_tab.employment_date).format('MMM DD, YYYY hh:mm A') + '">' + moment(row.employments_tab.employment_date).format('MMM DD, YYYY hh:mm A') + '</span>';
                    } else {
                        return '<span title="N/A">N/A</span>';
                    }
                }
            },
            {
                data: "department",
                title: "DEPARTMENT",
                render: function(data, type, row, meta) {
                    if (row.employments_tab && row.employments_tab.departments) {
                        return '<span title="' + (row.employments_tab.departments.description || 'N/A') + '">' + (row.employments_tab.departments.description || 'N/A') + '</span>';
                    } else {
                        return '<span title="N/A">N/A</span>';
                    }
                }
            },
            {
                data: "positions",
                title: "POSITION",
                render: function(data, type, row, meta) {
                    if (row.employments_tab && row.employments_tab.positions) {
                        return '<span title="' + (row.employments_tab.positions.description || 'N/A') + '">' + (row.employments_tab.positions.description || 'N/A') + '</span>';
                    } else {
                        return '<span title="N/A">N/A</span>';
                    }
                }
            }


        ], 'Bfrtip', [], true, false
    );

    $('ul.profile-tab-list li a').click(function() {
        modal_content = $(this).attr('data-group');
        module_url = $(this).attr('data-url');

        $('ul.profile-tab-list li a').removeClass('active');
        $(this).addClass('active');

        $('.content-employee').removeClass('active-screen');
        $(`#${this.id}Screen`).addClass('active-screen');

        syncRecordContent();
    });
    
    $('ul.second-tab li a').click(function() {
        $('ul.second-tab li a').removeClass('active');
        $(this).addClass('active');

        $('.content-sub-screen').removeClass('active');
        $(`#${this.id}SubScreen`).addClass('active');
    });
});

function success() {
    if(actions === 'save') {
        switch(modal_content) {
            case 'employee':
                $('#employee_table').DataTable().draw();
                scion.create.sc_modal('employee_form').hide('all', modalHideFunction)
                break;
            case 'educational':
                syncRecordContent();
                break;
        }
    }
    else {
        switch(modal_content) {
            case 'employee':
                $('#employee_table').DataTable().draw();
                scion.create.sc_modal('employee_form').hide('all', modalHideFunction)
                break;
            case 'educational':
                syncRecordContent();
                break;
        }
    }
}

function error() {
    toastr.error('Record already exist.', 'Failed')
}

function delete_success() {
    $('#benefits_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    
    switch(modal_content) {
        case 'employee':
            form_data = {
                _token: _token,
                status: $('#status').val(),
                firstname: $('#firstname').val(),
                middlename: $('#middlename').val(),
                lastname: $('#lastname').val(),
                suffix: $('#suffix').val(),
                birthdate: $('#birthdate').val(),
                birthplace: $('#birthplace').val(),
                gender: $('#gender').val(),
                citizenship: $('#citizenship').val(),
                civil_status: $('#civil_status').val(),
                phone1: $('#phone1').val(),
                phone2: $('#phone2').val(),
                email: $('#email').val(),
                country_1: $('#country_1').val(),
                province_1: $('#province_1').val(),
                city_1: $('#city_1').val(),
                barangay_1: $('#barangay_1').val(),
                street_1: $('#street_1').val(),
                zip_1: $('#zip_1').val(),
                emergency_name: $('#emergency_name').val(),
                emergency_no: $('#emergency_no').val(),
                emergency_relationship: $('#emergency_relationship').val(),
                employment_status: $('#employment_status').val(),
                classes_id: $('#classes_id').val(),
                position_id: $('#position_id').val(),
                department_id: $('#department_id').val(),
                employment_date: $('#employment_date').val(),
                payroll_calendar_id: $('#payroll_calendar_id').val(),
                employment_type: $('#employment_type').val(),
            };
            break;
        case 'educational':
            form_data = {
                _token: _token,
                employee_id: record_id,
                educational_attainment: $('#educational_attainment').val(),
                course: $('#course').val(),
                school_year: $('#school_year').val(),
                school: $('#school').val(),
            }
            break;
    }

    return form_data;
}

function generateDeleteItems(){}

function modalShowFunction() {
    scion.centralized_button(true, false, true, true);
    switch(modal_content) {
        case 'employee':
            modal_content = 'employee';
            module_url = '/payroll/employee-profile';

            if(record_id === null) {
                $('.profile-tab-list a:not(.active)').addClass('disabled');
            }
            else {
                $('.profile-tab-list a:not(.active)').removeClass('disabled');
            }
            
            $('.info-container').css('display','none');

            setTimeout(() => {
                $('#t_emp_no').text($('#employee_no').val());
                $('#t_full_name').text($('#firstname').val() + ' ' + $('#lastname').val());
                $('#t_hire_date').text(moment($('#employment_date').val()).format('MMM, DD Y'));
                $('#t_position').text($('#position_id option:selected').text());
                $('#t_status').text($('#status option:selected').text());
            }, 500);
            break;
    }
}

function modalHideFunction() {
    scion.centralized_button(false, true, true, true);
    modal_content = 'employee';
    module_url = '/payroll/employee-profile';
    $('ul.profile-tab-list li a').removeClass('active');
    $('ul.profile-tab-list li a#basicInformation').addClass('active');
    
    $('.content-employee').removeClass('active-screen');
    $(`#basicInformationScreen`).addClass('active-screen');
}

// Extra Functions

function getPosition() {
    var selectedValue = $('#position_id').val();

    if(actions == 'save') {

        $.get('/payroll/employee-information/getPosition/' + selectedValue, function(resp) {
            if (resp.exists) {
                alert('The position is already taken.');
                $('#position_id').val('');

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
            $('#province_1').val(store_record.employee.province_1);
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
            $('#city_1').val(store_record.employee.city_1);
            selectCity();
        }
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
            $('#barangay_1').val(store_record.employee.barangay_1);
        }
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

function syncRecordContent() {
    switch(modal_content) {
        case "employee":
            $('.info-container').css('display','none');
            break;
        case "educational":
            $.get(`${module_url}/get/${record_id}`, function(response) {
                var content_val = '';

                if(response.data.length === 0) {
                    content_val += '<div class="no-data">No Data</div>';
                }
                else {
                    content_val += `<div class="row">`;
                    response.data.forEach(background => {
                        content_val += `<div class="col-12 " id="background_${background.id}">
                                <div class="background-inner">
                                    <div class="background-attainment">${background.educational_attainment} (${background.course})</div>
                                    <div class="background-school-year">${background.school} - ${background.school_year}</div>
                                </div>
                            </div>`;
                    });
                    content_val += `</div>`;
                }
                $('.background-container').html(content_val);
            });
            $('.info-container').css('display','block');
            break;
    }
}