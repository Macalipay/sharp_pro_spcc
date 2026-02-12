
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

let addressHydratingFromRecord = false;

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
        
        sub_tab(this.id);
    });

    covertStatus();
    initializePhoneFields();
    initializeGovernmentFieldFormats();
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
            case 'compensation':
                syncRecordContent();
                break;
            case 'work-history':
                syncRecordContent();
                break;
            case 'certification':
                syncRecordContent();
                break;
            case 'training':
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
            case 'compensation':
                syncRecordContent();
                break;
            case 'work-history':
                syncRecordContent();
                break;
            case 'certification':
                syncRecordContent();
                break;
            case 'training':
                syncRecordContent();
                break;
        }
    }
}

function sub_tab(second_tab) {
        switch(second_tab) {
            case 'tax':
                tax_func();
                break;
            case 'sss':
                sss_func();
                break;
            case 'pagibig':
                pagibig_func();
                break;
            case 'philhealth':
                philhealth_func();
                break;
    }
}

function error(message) {
    toastr.error(message, 'Failed')
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
                phone1: normalizePhilippinePhone($('#phone1').val(), true),
                phone2: normalizePhilippinePhone($('#phone2').val(), false),
                sss_number: formatWithPattern($('#sss_number').val(), [2, 7, 1]),
                pagibig_number: formatWithPattern($('#pagibig_number').val(), [4, 4, 4]),
                tin_number: normalizeTinNumber($('#tin_number').val()),
                philhealth_number: formatWithPattern($('#philhealth_number').val(), [2, 9, 1]),
                bank_name: $('#bank_name').val(),
                bank_account: $('#bank_account').val(),
                bank_account_no: $('#bank_account_no').val(),
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
        case 'compensation':
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
        case 'work-history':
            form_data = {
                _token: _token,
                employee_id: record_id,
                company: $('#company').val(),
                position: $('#position').val(),
                date_hired: $('#date_hired').val(),
                date_of_resignation: $('#date_of_resignation').val(),
                remarks: $('#remarks').val(),
            }
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
    }

    return form_data;
}

function generateDeleteItems(){}

function modalShowFunction() {
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

                if (record_id !== null && $('#country_1').val()) {
                    addressHydratingFromRecord = true;
                    selectRegion(true);
                }
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


function syncRecordContent() {
    switch(modal_content) {
        case "employee":
            $('.info-container').css('display','none');
            $('.sc-modal-footer .btn-sv').css('display','block');
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
            $('.sc-modal-footer .btn-sv').css('display','inline-block');
            break;

        case "compensation":
            compensation_func();
            $('.sc-modal-footer .btn-sv').css('display','inline-block');
        case "taxBenefits":
            sss_func();
            $('.sc-modal-footer .btn-sv').css('display','inline-block');
        break;
        case "work-history":
            $.get(`${module_url}/get/${record_id}`, function(response) {
                var content_val = '';

                if(response.data.length === 0) {
                    content_val += '<div class="no-data">No Data</div>';
                }
                else {
                    content_val += `<div class="row">`;
                    response.data.forEach(history => {
                        content_val += `<div class="col-12 " id="history_${history.id}">
                                <div class="history-inner">
                                    <div class="history-attainment">${history.company} (${history.position})</div>
                                    <div class="history-school-year">${history.date_hired} - ${history.date_of_resignation}</div>
                                </div>
                            </div>`;
                    });
                    content_val += `</div>`;
                }
                $('.history-container').html(content_val);
            });
            $('.info-container').css('display','block');
            $('.sc-modal-footer .btn-sv').css('display','inline-block');
            break;
        case "certification":
            $.get(`${module_url}/get/${record_id}`, function(response) {
                var content_val = '';

                if(response.data.length === 0) {
                    content_val += '<div class="no-data">No Data</div>';
                }
                else {
                    content_val += `<div class="row">`;
                    response.data.forEach(cert => {
                        content_val += `<div class="col-12 " id="certification_${cert.id}">
                                <div class="certification-inner">
                                    <div class="certification-attainment">${cert.certification_name} (${cert.certification_authority})</div>
                                    <div class="certification-school-year">${cert.certification_date} - ${cert.certification_expiration_date}</div>
                                </div>
                            </div>`;
                    });
                    content_val += `</div>`;
                }
                $('.certification-container').html(content_val);
            });
            $('.info-container').css('display','block');
            $('.sc-modal-footer .btn-sv').css('display','inline-block');
            break;
        case "training":
            $.get(`${module_url}/get/${record_id}`, function(response) {
                var content_val = '';

                if(response.data.length === 0) {
                    content_val += '<div class="no-data">No Data</div>';
                }
                else {
                    content_val += `<div class="row">`;
                    response.data.forEach(training => {
                        content_val += `<div class="col-12 " id="training_${training.id}">
                                <div class="training-inner">
                                    <div class="training-attainment">${training.training_name} (${training.training_provider})</div>
                                    <div class="training-school-year">${training.training_date}</div>
                                </div>
                            </div>`;
                    });
                    content_val += `</div>`;
                }
                $('.training-container').html(content_val);
            });
            $('.info-container').css('display','block');
            $('.sc-modal-footer .btn-sv').css('display','inline-block');
            break;
        case "audit-trail":
            loadAuditTrail();
            $('.info-container').css('display','block');
            $('.sc-modal-footer .btn-sv').css('display','none');
            break;
        case "biodata":
            
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
            $('.info-container').css('display','none');
            $('.sc-modal-footer .btn-sv').css('display','none');
            break;
    }
}

function loadAuditTrail() {
    if(!record_id) {
        $('#auditTrailBody').html('<tr><td colspan="4" class="text-center">No Data</td></tr>');
        return;
    }

    $.get(`${module_url}/audit-trail/${record_id}`, function(response) {
        var rows = '';

        if(!response.data || response.data.length === 0) {
            rows = '<tr><td colspan="4" class="text-center">No Data</td></tr>';
        }
        else {
            response.data.forEach(item => {
                var user = $('<div>').text(item.user || 'System').html();
                var changeType = $('<div>').text(item.change_type || '').html();
                var description = $('<div>').text(item.description || 'from - to record saved').html();
                var timestamp = item.timestamp ? moment(item.timestamp).format('MMM DD, YYYY hh:mm A') : '-';

                rows += `<tr>
                    <td>${user}</td>
                    <td>${changeType}</td>
                    <td>${description}</td>
                    <td>${timestamp}</td>
                </tr>`;
            });
        }

        $('#auditTrailBody').html(rows);
    }).fail(function() {
        $('#auditTrailBody').html('<tr><td colspan="4" class="text-center">Unable to load audit trail.</td></tr>');
    });
}

function salary(response) {
    $.each(Object.keys(response.data), function(i, v) {
        if(response.data[v] !== false) {
            $('#' + v + '_salary').val(response.data[v].toFixed(2));
        }
    });
}

function compensation_func() {
    module_content = 'compensation';
    module_url = '/payroll/compensation';
    actions = 'update';
    module_type = 'sub_transaction';
    scion.centralized_button(true, false, true, true);
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

function selectRegion(hydrate = false) {
    $.get('/address/province/'+$('#country_1').val(), function(response) {
        var html = '';
        html += '<option value=""></option>';
        response.province.forEach(province => {
            html += `<option value="${province.province_id}">${province.name}</option>`;
        });

        $('#province_1').html(html);

        if(record_id !== null && (hydrate || addressHydratingFromRecord)) {
            $('#province_1').val(store_record.employee.province_1);
            selectProvince(true);
        }
    });
}

function selectProvince(hydrate = false) {
    $.get('/address/city/'+$('#province_1').val(), function(response) {
        var html = '';
        html += '<option value=""></option>';
        response.city.forEach(city => {
            html += `<option value="${city.city_id}">${city.name}</option>`;
        });

        $('#city_1').html(html);
        
        if(record_id !== null && (hydrate || addressHydratingFromRecord)) {
            $('#city_1').val(store_record.employee.city_1);
            selectCity(true);
        }
    });
}

function selectCity(hydrate = false) {
    $.get('/address/barangay/'+$('#city_1').val(), function(response) {
        var html = '';
        html += '<option value=""></option>';
        response.barangay.forEach(barangay => {
            html += `<option value="${barangay.id}">${barangay.name}</option>`;
        });

        $('#barangay_1').html(html);

        if(record_id !== null && (hydrate || addressHydratingFromRecord)) {
            $('#barangay_1').val(store_record.employee.barangay_1);
        }

        if (hydrate || addressHydratingFromRecord) {
            addressHydratingFromRecord = false;
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

function normalizePhilippinePhone(value, required = false) {
    const digits = String(value || '').replace(/\D/g, '');
    let localNumber = '';

    if (digits.startsWith('63')) {
        localNumber = digits.slice(2);
    } else if (digits.startsWith('0')) {
        localNumber = digits.slice(1);
    } else {
        localNumber = digits;
    }

    if (localNumber.length > 0 && localNumber[0] !== '9') {
        localNumber = '';
    }

    if (!required && localNumber.length === 0) {
        return '';
    }

    return `+63${localNumber.slice(0, 10)}`;
}

function formatWithPattern(value, groups) {
    const digits = String(value || '').replace(/\D/g, '');
    if (!digits) {
        return '';
    }

    let cursor = 0;
    const parts = [];
    for (let i = 0; i < groups.length; i++) {
        const part = digits.slice(cursor, cursor + groups[i]);
        if (!part) {
            break;
        }
        parts.push(part);
        cursor += groups[i];
    }

    return parts.join('-');
}

function normalizeTinNumber(value) {
    const digits = String(value || '').replace(/\D/g, '');
    if (!digits) {
        return '';
    }

    if (digits.length <= 9) {
        return formatWithPattern(digits, [3, 3, 3]);
    }

    return formatWithPattern(digits, [3, 3, 3, 3]);
}

function bindPhoneInput(selector, required) {
    const phoneInput = $(selector);

    if (!phoneInput.length) {
        return;
    }

    const enforcePhoneFormat = () => {
        phoneInput.val(normalizePhilippinePhone(phoneInput.val(), required));
    };

    enforcePhoneFormat();
    phoneInput.on('input', enforcePhoneFormat);
    phoneInput.on('focus', () => {
        if (!phoneInput.val() || phoneInput.val().slice(0, 3) !== '+63') {
            phoneInput.val('+63');
        }
    });

    if (!required) {
        phoneInput.on('blur', () => {
            if (phoneInput.val() === '+63') {
                phoneInput.val('');
            }
        });
    }
}

function initializePhoneFields() {
    bindPhoneInput('#phone1', true);
    bindPhoneInput('#phone2', false);
}

function bindPatternInput(selector, formatter) {
    const input = $(selector);

    if (!input.length) {
        return;
    }

    input.on('input', () => {
        input.val(formatter(input.val()));
    });
}

function initializeGovernmentFieldFormats() {
    bindPatternInput('#sss_number', (value) => formatWithPattern(value, [2, 7, 1]));
    bindPatternInput('#pagibig_number', (value) => formatWithPattern(value, [4, 4, 4]));
    bindPatternInput('#tin_number', normalizeTinNumber);
    bindPatternInput('#philhealth_number', (value) => formatWithPattern(value, [2, 9, 1]));
    bindPatternInput('#bank_account_no', (value) => String(value || '').replace(/\D/g, ''));
}

function sss_func() {
     $.get(`/payroll/compensation/get/${record_id}`, function(response) {
               let monthly_salary = parseFloat(response.record.monthly_salary ?? 0);
                let employee_share = 0;
                let employer_share = 0;
                let total_contribution = 0;

                // SSS computation rule
                employee_share = monthly_salary * 0.05;  // 5%
                employer_share = monthly_salary * 0.10;  // 10%

                total_contribution = employee_share + employer_share;

                // Assign values to inputs
                $('#rec_sss_number').val(response.employee_information.sss_number ?? 'NO RECORD');
                $('#rec_monthly_salary').val(formatPhpCurrency(monthly_salary));
                $('#rec_employee_share').val(formatPhpCurrency(employee_share));
                $('#rec_employeer_share').val(formatPhpCurrency(employer_share));
                $('#rec_total_contribution').val(formatPhpCurrency(total_contribution));
            });
            if ($.fn.DataTable.isDataTable('#sss_table')) {
                $('#sss_table').DataTable().destroy();
                $('#sss_table').empty(); // Clear old headers/body to avoid duplication
            }

            scion.create.table(
                'sss_table',
                '/payroll/benefits/sss/' + record_id,
                [
                    {
                        data: "id",
                        title: "<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>",
                        render: function (data, type, row, meta) {
                            return `
                                <input type="checkbox" class="single-checkbox" 
                                    value="${row.id}" onclick="scion.table.checkOne()"/>`;
                        }
                    },
                    { data: "id", title: "ID" },
                    { data: "benefits.benefits", title: "BENEFITS" },
                    { data: "employee", title: "EMPLOYEE",
                        render: function (data, type, row) {
                            if (data) {
                                return data.firstname + ' ' + data.lastname;
                            }
                            return '';
                        }
                    },
                    { data: "user", title: "PROCESS BY",
                        render: function (data, type, row) {
                            if (data) {
                                return data.firstname + ' ' + data.lastname;
                            }
                            return '';
                        }
                    },
                    {
                        data: "amount",
                        title: "AMOUNT",
                        render: function(data) {
                            return formatPhpCurrency(data);
                        }
                    },
                    { data: "created_at", title: "DATE" },
                ],
                'Bfrtip',
                []
            );
}

function pagibig_func() {
    $.get(`/payroll/compensation/get/${record_id}`, function(response) {
        let monthly_salary = parseFloat(response.record.monthly_salary ?? 0);
        let employee_share = 0;
        let employer_share = 0;
        let total_contribution = 0;

        // Apply PAG-IBIG contribution rules
        if (monthly_salary <= 1500) {
            employee_share = monthly_salary * 0.01; // 1%
            employer_share = monthly_salary * 0.02; // 2%
        } else if (monthly_salary > 1500 && monthly_salary <= 10000) {
            employee_share = monthly_salary * 0.02; // 2%
            employer_share = monthly_salary * 0.02; // 2%
        } else {
            // Cap at ₱10,000
            employee_share = 200; // ₱200
            employer_share = 200; // ₱200
        }

        total_contribution = employee_share + employer_share;

        // Assign values to inputs
        $('#rec_pagibig_number').val(response.employee_information.pagibig_number ?? 'NO RECORD');
        $('#rec_pagibig_monthly_salary').val(formatPhpCurrency(monthly_salary));
        $('#rec_pagibig_employee_share').val(formatPhpCurrency(employee_share));
        $('#rec_pagibig_employer_share').val(formatPhpCurrency(employer_share));
        $('#rec_pagibig_total_contribution').val(formatPhpCurrency(total_contribution));
    });

    // ✅ Destroy existing DataTable before re-creating
    if ($.fn.DataTable.isDataTable('#pagibig_table')) {
        $('#pagibig_table').DataTable().destroy();
        $('#pagibig_table').empty(); // clear headers/body
    }

    // ✅ Create PAG-IBIG table
    scion.create.table(
        'pagibig_table',
        '/payroll/benefits/pagibig/' + record_id, // use the correct route
        [
            {
                data: "id",
                title: "<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>",
                render: function(data, type, row) {
                    return `<input type="checkbox" class="single-checkbox" 
                                value="${row.id}" onclick="scion.table.checkOne()"/>`;
                }
            },
            { data: "id", title: "ID" },
            { data: "benefits.benefits", title: "BENEFITS" },
            {
                data: "employee",
                title: "EMPLOYEE",
                render: function(data) {
                    return data ? `${data.firstname} ${data.lastname}` : '';
                }
            },
            {
                data: "user",
                title: "PROCESSED BY",
                render: function(data) {
                    return data ? `${data.firstname} ${data.lastname}` : '';
                }
            },
            {
                data: "amount",
                title: "AMOUNT",
                render: function(data) {
                    return formatPhpCurrency(data);
                }
            },
            { data: "created_at", title: "DATE" },
        ],
        'Bfrtip',
        []
    );
}

function philhealth_func() {
    $.get(`/payroll/compensation/get/${record_id}`, function(response) {
        let monthly_salary = parseFloat(response.record.monthly_salary ?? 0);
        let employee_share = 0;
        let employer_share = 0;
        let total_contribution = 0;
        let monthly_premium = 0;

        // 📘 Apply PhilHealth contribution rules (based on 2025 table)
        if (monthly_salary <= 10000) {
            monthly_premium = 500; // ₱500 fixed
        } else if (monthly_salary > 10000 && monthly_salary < 100000) {
            monthly_premium = monthly_salary * 0.05; // 5% of salary
            if (monthly_premium < 500) monthly_premium = 500;
            if (monthly_premium > 5000) monthly_premium = 5000;
        } else {
            monthly_premium = 5000; // cap at ₱5,000
        }

        // Divide premium equally
        employee_share = monthly_premium / 2;
        employer_share = monthly_premium / 2;
        total_contribution = monthly_premium;

        // ✅ Assign values to inputs
        $('#rec_philhealth_number').val(response.employee_information.philhealth_number ?? 'NO RECORD');
        $('#rec_philhealth_monthly_salary').val(formatPhpCurrency(monthly_salary));
        $('#rec_philhealth_employee_share').val(formatPhpCurrency(employee_share));
        $('#rec_philhealth_employer_share').val(formatPhpCurrency(employer_share));
        $('#rec_philhealth_total_contribution').val(formatPhpCurrency(total_contribution));
    });

    // ✅ Destroy existing DataTable before re-creating
    if ($.fn.DataTable.isDataTable('#philhealth_table')) {
        $('#philhealth_table').DataTable().destroy();
        $('#philhealth_table').empty(); // clear headers/body
    }

    // ✅ Create PHILHEALTH table
    scion.create.table(
        'philhealth_table',
        '/payroll/benefits/philhealth/' + record_id, // correct route
        [
            {
                data: "id",
                title: "<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>",
                render: function(data, type, row) {
                    return `<input type="checkbox" class="single-checkbox" 
                                value="${row.id}" onclick="scion.table.checkOne()"/>`;
                }
            },
            { data: "id", title: "ID" },
            { data: "benefits.benefits", title: "BENEFITS" },
            {
                data: "employee",
                title: "EMPLOYEE",
                render: function(data) {
                    return data ? `${data.firstname} ${data.lastname}` : '';
                }
            },
            {
                data: "user",
                title: "PROCESSED BY",
                render: function(data) {
                    return data ? `${data.firstname} ${data.lastname}` : '';
                }
            },
            {
                data: "amount",
                title: "AMOUNT",
                render: function(data) {
                    return formatPhpCurrency(data);
                }
            },
            { data: "created_at", title: "DATE" },
        ],
        'Bfrtip',
        []
    );
}

function covertStatus() {
    $.post('/payroll/employee-profile/convert-record', { _token: _token }).done((response) => {
        response.data.length === 0?null:toastr.success('Status Update');
    });
}

function formatPhpCurrency(value) {
    const amount = Number(value) || 0;
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
}
