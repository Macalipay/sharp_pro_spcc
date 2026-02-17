
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

let addressHydratingFromRecord = false;
let employeeMasterfileControlsInitialized = false;
let employeeMasterfileDateFilter = null;

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


        ], 'Bfrtip', [], true, false, '50vh'
    );

    initializeEmployeeMasterfileControls();

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
    initializeAttachmentInputs();

    $("#profile_img").cropzee({
        allowedInputs: ['png', 'jpg', 'jpeg']
    });
});

function success() {
    if(actions === 'save') {
        switch(modal_content) {
            case 'employee':
                $('#employee_table').DataTable().draw();
                syncRecordContent();
                break;
            case 'educational':
                syncRecordContent();
                break;
            case 'compensation':
                syncRecordContent();
                break;
            case 'leave-entitlement':
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
            case 'work-calendar':
                syncRecordContent();
                break;
        }
    }
    else {
        switch(modal_content) {
            case 'employee':
                $('#employee_table').DataTable().draw();
                syncRecordContent();
                break;
            case 'educational':
                syncRecordContent();
                break;
            case 'compensation':
                syncRecordContent();
                break;
            case 'leave-entitlement':
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
            case 'work-calendar':
                syncRecordContent();
                break;
        }
    }
}

function initializeEmployeeMasterfileControls() {
    if (employeeMasterfileControlsInitialized) {
        return;
    }

    const waitForTable = setInterval(() => {
        if (!$.fn.DataTable.isDataTable('#employee_table')) {
            return;
        }

        clearInterval(waitForTable);
        employeeMasterfileControlsInitialized = true;

        const table = $('#employee_table').DataTable();
        table.page.len(parseInt($('#entries_count').val(), 10)).draw();

        const populateDepartmentFilter = () => {
            const selectedDepartment = $('#filter_department').val();
            const departments = new Set();

            table.rows().every(function() {
                const row = this.data();
                const department = row && row.employments_tab && row.employments_tab.departments
                    ? row.employments_tab.departments.description
                    : '';
                if (department) {
                    departments.add(department);
                }
            });

            const options = ['<option value="">All</option>'];
            Array.from(departments).sort().forEach((department) => {
                options.push(`<option value="${department}">${department}</option>`);
            });

            $('#filter_department').html(options.join(''));
            if (selectedDepartment && departments.has(selectedDepartment)) {
                $('#filter_department').val(selectedDepartment);
            }
        };

        populateDepartmentFilter();
        table.on('draw', populateDepartmentFilter);

        $('#entries_count').on('change', function() {
            const length = parseInt($(this).val(), 10) || 10;
            table.page.len(length).draw();
        });

        $('#filter_department').on('change', function() {
            const value = $(this).val();
            const escaped = value ? $.fn.dataTable.util.escapeRegex(value) : '';
            table.column(6).search(value ? `^${escaped}$` : '', true, false).draw();
        });

        if (!employeeMasterfileDateFilter) {
            employeeMasterfileDateFilter = function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'employee_table') {
                    return true;
                }

                const from = $('#filter_employment_date_from').val();
                const to = $('#filter_employment_date_to').val();
                if (!from && !to) {
                    return true;
                }

                const rowData = table.row(dataIndex).data();
                const rawDate = rowData && rowData.employments_tab ? rowData.employments_tab.employment_date : null;
                if (!rawDate) {
                    return false;
                }

                const employmentDate = moment(rawDate);
                if (!employmentDate.isValid()) {
                    return false;
                }

                if (from && employmentDate.isBefore(moment(from).startOf('day'))) {
                    return false;
                }

                if (to && employmentDate.isAfter(moment(to).endOf('day'))) {
                    return false;
                }

                return true;
            };

            $.fn.dataTable.ext.search.push(employeeMasterfileDateFilter);
        }

        $('#filter_employment_date_from, #filter_employment_date_to').on('change', function() {
            table.draw();
        });

        $('#sort_field, #sort_order').on('change', function() {
            const field = $('#sort_field').val();
            const order = $('#sort_order').val();

            if (!field) {
                table.order([]).draw();
                return;
            }

            const columnIndex = field === 'employment_date' ? 5 : 6;
            table.order([columnIndex, order]).draw();
        });

        $('#clear_filters').on('click', function() {
            $('#filter_department').val('');
            $('#filter_employment_date_from').val('');
            $('#filter_employment_date_to').val('');
            $('#sort_field').val('');
            $('#sort_order').val('asc');
            table.column(6).search('', true, false);
            table.order([]).draw();
        });
    }, 100);
}

function sub_tab(second_tab) {
    switch(second_tab) {
        case 'sss':
            sss_func();
            break;
        case 'pagibig':
            pagibig_func();
            break;
        case 'philhealth':
            philhealth_func();
            break;
        case 'withholdingtax':
            // Placeholder tab for now; avoid calling undefined handlers.
            break;
        case 'allowance':
            allowance_func();
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
                emergency_no: normalizePhilippinePhone($('#emergency_no').val(), false),
                emergency_relationship: $('#emergency_relationship').val(),
                employment_status: $('#employment_status').val(),
                classes_id: $('#classes_id').val(),
                position_id: $('#position_id').val(),
                department_id: $('#department_id').val(),
                employment_date: $('#employment_date').val(),
                payroll_calendar_id: $('#payroll_calendar_id').val(),
                employment_type: $('#employment_type').val(),
                profile_img: ($('#profile_img').val() !== '' ? cropzeeGetImage('profile_img') : ''),
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
                attachment_data: $('#educational_attachment_data').val(),
                attachment_name: $('#educational_attachment_name').val(),
                attachment_mime: $('#educational_attachment_mime').val(),
            }
            break;
        case 'compensation':
            form_data = {
                _token: _token,
                employee_id: record_id,
                annual_salary: parseCurrencyToNumber($('#annual_salary').val()),
                monthly_salary: parseCurrencyToNumber($('#monthly_salary').val()),
                semi_monthly_salary: parseCurrencyToNumber($('#semi_monthly_salary').val()),
                weekly_salary: parseCurrencyToNumber($('#weekly_salary').val()),
                daily_salary: parseCurrencyToNumber($('#daily_salary').val()),
                hourly_salary: parseCurrencyToNumber($('#hourly_salary').val()),
                tax: parseCurrencyToNumber($('#tax').val()),
                government_mandated_benefits: '1',
                other_company_benefits: '1',
                sss: parseCurrencyToNumber($('#sss').val()),
                phic: parseCurrencyToNumber($('#phic').val()),
                pagibig: parseCurrencyToNumber($('#pagibig').val()),
            };
            break;
        case 'leave-entitlement':
            form_data = {
                _token: _token,
                employee_id: record_id,
                leave_type: $('#leave_type').val(),
                total_hours: $('#total_hours').val(),
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
                attachment_data: $('#work_history_attachment_data').val(),
                attachment_name: $('#work_history_attachment_name').val(),
                attachment_mime: $('#work_history_attachment_mime').val(),
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
                attachment_data: $('#certification_attachment_data').val(),
                attachment_name: $('#certification_attachment_name').val(),
                attachment_mime: $('#certification_attachment_mime').val(),

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
                attachment_data: $('#training_attachment_data').val(),
                attachment_name: $('#training_attachment_name').val(),
                attachment_mime: $('#training_attachment_mime').val(),
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
                saturday_end_time: $('#saturday_end_time').val(),
                is_flexi_time: $('#wc_is_flexi_time').is(':checked') ? 1 : 0,
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

                if (record_id !== null && store_record && store_record.employee && store_record.employee.profile_img) {
                    const profileImage = '/images/payroll/employee-information/' + store_record.employee.profile_img;
                    $('#viewer').attr('src', profileImage);
                    $('#t_profile_img').attr('src', profileImage);
                } else {
                    $('#viewer').attr('src', '/images/payroll/employee-information/default.png');
                    $('#t_profile_img').attr('src', '/images/payroll/employee-information/default.png');
                }

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
                        const attachmentLink = background.attachment
                            ? `<a href="/images/payroll/employee-attachments/educational-background/${background.attachment}" target="_blank" class="background-attachment-link">View Attachment</a>`
                            : '<span class="text-muted">No Attachment</span>';
                        content_val += `<div class="col-12 " id="background_${background.id}">
                                <div class="background-inner">
                                    <div class="background-attainment">${background.educational_attainment} (${background.course})</div>
                                    <div class="background-school-year">${background.school} - ${background.school_year}</div>
                                    <div class="background-school-year">${attachmentLink}</div>
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
        case "leave-entitlement":
            leave_entitlement_func();
            $('.info-container').css('display','block');
            $('.sc-modal-footer .btn-sv').css('display','inline-block');
            break;
        case "work-calendar":
            work_calendar_func();
            $('.info-container').css('display','block');
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
                        const attachmentLink = history.attachment
                            ? `<a href="/images/payroll/employee-attachments/work-history/${history.attachment}" target="_blank" class="history-attachment-link">View Attachment</a>`
                            : '<span class="text-muted">No Attachment</span>';
                        content_val += `<div class="col-12 " id="history_${history.id}">
                                <div class="history-inner">
                                    <div class="history-attainment">${history.company} (${history.position})</div>
                                    <div class="history-school-year">${history.date_hired} - ${history.date_of_resignation}</div>
                                    <div class="history-school-year">${attachmentLink}</div>
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
                        const attachmentLink = cert.attachment
                            ? `<a href="/images/payroll/employee-attachments/certification/${cert.attachment}" target="_blank" class="certification-attachment-link">View Attachment</a>`
                            : '<span class="text-muted">No Attachment</span>';
                        content_val += `<div class="col-12 " id="certification_${cert.id}">
                                <div class="certification-inner">
                                    <div class="certification-attainment">${cert.certification_name} (${cert.certification_authority})</div>
                                    <div class="certification-school-year">${cert.certification_date} - ${cert.certification_expiration_date}</div>
                                    <div class="certification-school-year">${attachmentLink}</div>
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
                        const attachmentLink = training.attachment
                            ? `<a href="/images/payroll/employee-attachments/training/${training.attachment}" target="_blank" class="training-attachment-link">View Attachment</a>`
                            : '<span class="text-muted">No Attachment</span>';
                        content_val += `<div class="col-12 " id="training_${training.id}">
                                <div class="training-inner">
                                    <div class="training-attainment">${training.training_name} (${training.training_provider})</div>
                                    <div class="training-school-year">${training.training_date}</div>
                                    <div class="training-school-year">${attachmentLink}</div>
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

function printAuditTrail() {
    if (!record_id) {
        toastr.error('Please select an employee record first.');
        return;
    }

    const employeeNo = ($('#t_emp_no').text() || '-').trim();
    const fullName = ($('#t_full_name').text() || '-').trim();
    const tableHtml = $('#auditTrailPrintSection').html() || '';
    const printedAt = moment().format('MMM DD, YYYY hh:mm A');

    const printWindow = window.open('', '_blank', 'width=1200,height=800');
    if (!printWindow) {
        toastr.error('Unable to open print window. Please allow pop-ups.');
        return;
    }

    const html = `
        <html>
            <head>
                <title>Audit Trail</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; color: #111827; }
                    h2 { margin: 0 0 8px 0; font-size: 18px; }
                    .meta { margin-bottom: 10px; font-size: 12px; }
                    .meta div { margin-bottom: 2px; }
                    table { width: 100%; border-collapse: collapse; font-size: 11px; }
                    th, td { border: 1px solid #d1d5db; padding: 6px; vertical-align: top; }
                    th { background: #f3f4f6; text-align: left; }
                    @page { size: A4 portrait; margin: 10mm; }
                </style>
            </head>
            <body>
                <h2>Employee Audit Trail</h2>
                <div class="meta">
                    <div><strong>Employee No:</strong> ${employeeNo}</div>
                    <div><strong>Employee Name:</strong> ${fullName}</div>
                    <div><strong>Printed At:</strong> ${printedAt}</div>
                </div>
                ${tableHtml}
                <script>
                    window.onload = function() {
                        window.print();
                        window.close();
                    };
                <\/script>
            </body>
        </html>
    `;

    printWindow.document.open();
    printWindow.document.write(html);
    printWindow.document.close();
}

function salary(response) {
    $.each(Object.keys(response.data), function(i, v) {
        if(response.data[v] !== false) {
            if (['annual', 'monthly', 'semi_monthly', 'weekly', 'daily', 'hourly'].includes(v)) {
                $('#' + v + '_salary').val(formatPhpCurrency(response.data[v]));
            } else {
                $('#' + v + '_salary').val(response.data[v].toFixed(2));
            }
        }
    });

    if (response.data && response.data.monthly !== false) {
        refreshCompensationGovernmentFields(response.data.monthly);
    }
}

function compensation_func() {
    module_content = 'compensation';
    module_url = '/payroll/compensation';
    actions = 'update';
    module_type = 'sub_transaction';
    scion.centralized_button(true, false, true, true);

    if (!record_id) {
        return;
    }

    $.get(`/payroll/compensation/get/${record_id}`, function(response) {
        const record = response.record || {};
        const computed = response.computed || {};

        if (record.annual_salary !== undefined) $('#annual_salary').val(formatPhpCurrency(record.annual_salary ?? 0));
        if (record.monthly_salary !== undefined) $('#monthly_salary').val(formatPhpCurrency(record.monthly_salary ?? 0));
        if (record.semi_monthly_salary !== undefined) $('#semi_monthly_salary').val(formatPhpCurrency(record.semi_monthly_salary ?? 0));
        if (record.weekly_salary !== undefined) $('#weekly_salary').val(formatPhpCurrency(record.weekly_salary ?? 0));
        if (record.daily_salary !== undefined) $('#daily_salary').val(formatPhpCurrency(record.daily_salary ?? 0));
        if (record.hourly_salary !== undefined) $('#hourly_salary').val(formatPhpCurrency(record.hourly_salary ?? 0));

        $('#sss').val(formatPhpCurrency(computed.sss ?? 0));
        $('#pagibig').val(formatPhpCurrency(computed.pagibig ?? 0));
        $('#phic').val(formatPhpCurrency(computed.phic ?? 0));
        $('#tax').val(formatPhpCurrency(computed.tax ?? 0));

        $('#monthly_salary').off('.compensationComputed');
        $('#monthly_salary').on('change.compensationComputed blur.compensationComputed', function() {
            refreshCompensationGovernmentFields(parseCurrencyToNumber($(this).val()));
        });
    });
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
    bindPhoneInput('#emergency_no', false);
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

function initializeAttachmentInputs() {
    bindAttachmentField('educational_attachment', 'educational_attachment_data', 'educational_attachment_name', 'educational_attachment_mime');
    bindAttachmentField('work_history_attachment', 'work_history_attachment_data', 'work_history_attachment_name', 'work_history_attachment_mime');
    bindAttachmentField('certification_attachment', 'certification_attachment_data', 'certification_attachment_name', 'certification_attachment_mime');
    bindAttachmentField('training_attachment', 'training_attachment_data', 'training_attachment_name', 'training_attachment_mime');
}

function bindAttachmentField(inputId, dataId, nameId, mimeId) {
    const $input = $('#' + inputId);
    if (!$input.length) {
        return;
    }

    $input.on('change', function () {
        const file = this.files && this.files[0] ? this.files[0] : null;

        if (!file) {
            $('#' + dataId).val('');
            $('#' + nameId).val('');
            $('#' + mimeId).val('');
            return;
        }

        const maxSizeBytes = 25 * 1024 * 1024;
        if (file.size > maxSizeBytes) {
            toastr.error('Attachment must not exceed 25MB.');
            this.value = '';
            $('#' + dataId).val('');
            $('#' + nameId).val('');
            $('#' + mimeId).val('');
            return;
        }

        const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        const extension = (file.name.split('.').pop() || '').toLowerCase();
        if (!allowedExtensions.includes(extension)) {
            toastr.error('Unsupported attachment type. Allowed: PDF, JPEG, PNG, DOC, DOCX.');
            this.value = '';
            $('#' + dataId).val('');
            $('#' + nameId).val('');
            $('#' + mimeId).val('');
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            $('#' + dataId).val(e.target.result || '');
            $('#' + nameId).val(file.name || '');
            $('#' + mimeId).val(file.type || '');
        };

        reader.onerror = function () {
            toastr.error('Unable to read attachment file.');
            $input.val('');
            $('#' + dataId).val('');
            $('#' + nameId).val('');
            $('#' + mimeId).val('');
        };

        reader.readAsDataURL(file);
    });
}

function sss_func() {
    $.get(`/payroll/benefits/sss-total/${record_id}`, function(response) {
        $('#rec_sss_number').val(response.benefit_number ?? 'NO RECORD');
        $('#rec_monthly_salary').val(formatPhpCurrency(response.monthly_salary ?? 0));
        $('#rec_employee_share').val(formatPhpCurrency(response.employee_share ?? 0));
        $('#rec_employeer_share').val(formatPhpCurrency(response.employer_share ?? 0));
        $('#rec_total_contribution').val(formatPhpCurrency(response.total_contribution ?? 0));
    });

    if ($.fn.DataTable.isDataTable('#sss_table')) {
        $('#sss_table').DataTable().destroy();
        $('#sss_table').empty();
    }

    scion.create.table(
        'sss_table',
        '/payroll/benefits/sss/' + record_id,
        [
            { data: "sequence_no", title: "SEQUENCE NO." },
            {
                data: "schedule_type",
                title: "PERIOD TYPE",
                render: function(data) {
                    return (data || '').toUpperCase();
                }
            },
            {
                data: "period_start",
                title: "PERIOD COVERED",
                render: function(data, type, row) {
                    const start = row.period_start ? moment(row.period_start).format('MMM DD, YYYY') : '-';
                    const end = row.payroll_period ? moment(row.payroll_period).format('MMM DD, YYYY') : '-';
                    return `${start} - ${end}`;
                }
            },
            {
                data: "workflow_status",
                title: "STATUS",
                render: function(data) {
                    return parseInt(data, 10) === 3 ? 'PAYROLL COMPLETED' : 'COMPLETED';
                }
            },
            {
                data: "amount",
                title: "CONTRIBUTION",
                render: function(data) {
                    return formatPhpCurrency(data ?? 0);
                }
            },
            {
                data: "created_at",
                title: "PROCESSED DATE",
                render: function(data) {
                    return data ? moment(data).format('MMM DD, YYYY hh:mm A') : '-';
                }
            },
        ],
        'Bfrtip',
        []
    );
}

function pagibig_func() {
    $.get(`/payroll/benefits/pagibig-total/${record_id}`, function(response) {
        $('#rec_pagibig_number').val(response.benefit_number ?? 'NO RECORD');
        $('#rec_pagibig_monthly_salary').val(formatPhpCurrency(response.monthly_salary ?? 0));
        $('#rec_pagibig_employee_share').val(formatPhpCurrency(response.employee_share ?? 0));
        $('#rec_pagibig_employer_share').val(formatPhpCurrency(response.employer_share ?? 0));
        $('#rec_pagibig_total_contribution').val(formatPhpCurrency(response.total_contribution ?? 0));
    });

    if ($.fn.DataTable.isDataTable('#pagibig_table')) {
        $('#pagibig_table').DataTable().destroy();
        $('#pagibig_table').empty();
    }

    scion.create.table(
        'pagibig_table',
        '/payroll/benefits/pagibig/' + record_id,
        [
            {
                data: "sequence_no",
                title: "SEQUENCE NO."
            },
            {
                data: "schedule_type",
                title: "PERIOD TYPE",
                render: function(data) {
                    return (data || '').toUpperCase();
                }
            },
            {
                data: "period_start",
                title: "PERIOD COVERED",
                render: function(data, type, row) {
                    const start = row.period_start ? moment(row.period_start).format('MMM DD, YYYY') : '-';
                    const end = row.payroll_period ? moment(row.payroll_period).format('MMM DD, YYYY') : '-';
                    return `${start} - ${end}`;
                }
            },
            {
                data: "workflow_status",
                title: "STATUS",
                render: function(data) {
                    return parseInt(data, 10) === 3 ? 'PAYROLL COMPLETED' : 'COMPLETED';
                }
            },
            {
                data: "amount",
                title: "CONTRIBUTION",
                render: function(data) {
                    return formatPhpCurrency(data ?? 0);
                }
            },
            {
                data: "created_at",
                title: "PROCESSED DATE",
                render: function(data) {
                    return data ? moment(data).format('MMM DD, YYYY hh:mm A') : '-';
                }
            },
        ],
        'Bfrtip',
        []
    );
}

function philhealth_func() {
    $.get(`/payroll/benefits/philhealth-total/${record_id}`, function(response) {
        $('#rec_philhealth_number').val(response.benefit_number ?? 'NO RECORD');
        $('#rec_philhealth_monthly_salary').val(formatPhpCurrency(response.monthly_salary ?? 0));
        $('#rec_philhealth_employee_share').val(formatPhpCurrency(response.employee_share ?? 0));
        $('#rec_philhealth_employer_share').val(formatPhpCurrency(response.employer_share ?? 0));
        $('#rec_philhealth_total_contribution').val(formatPhpCurrency(response.total_contribution ?? 0));
    });

    if ($.fn.DataTable.isDataTable('#philhealth_table')) {
        $('#philhealth_table').DataTable().destroy();
        $('#philhealth_table').empty();
    }

    scion.create.table(
        'philhealth_table',
        '/payroll/benefits/philhealth/' + record_id,
        [
            {
                data: "sequence_no",
                title: "SEQUENCE NO."
            },
            {
                data: "schedule_type",
                title: "PERIOD TYPE",
                render: function(data) {
                    return (data || '').toUpperCase();
                }
            },
            {
                data: "period_start",
                title: "PERIOD COVERED",
                render: function(data, type, row) {
                    const start = row.period_start ? moment(row.period_start).format('MMM DD, YYYY') : '-';
                    const end = row.payroll_period ? moment(row.payroll_period).format('MMM DD, YYYY') : '-';
                    return `${start} - ${end}`;
                }
            },
            {
                data: "workflow_status",
                title: "STATUS",
                render: function(data) {
                    return parseInt(data, 10) === 3 ? 'PAYROLL COMPLETED' : 'COMPLETED';
                }
            },
            {
                data: "amount",
                title: "CONTRIBUTION",
                render: function(data) {
                    return formatPhpCurrency(data ?? 0);
                }
            },
            {
                data: "created_at",
                title: "PROCESSED DATE",
                render: function(data) {
                    return data ? moment(data).format('MMM DD, YYYY hh:mm A') : '-';
                }
            },
        ],
        'Bfrtip',
        []
    );
}

function allowance_func() {
    if (!record_id) {
        $('#fixedAllowanceBody').html('<tr><td colspan="3" class="text-center">No Data</td></tr>');
        return;
    }

    bindFixedAllowanceEvents();
    loadFixedAllowances();
}

function bindFixedAllowanceEvents() {
    $('#allowance_type').off('change.fixedAllowance').on('change.fixedAllowance', function() {
        const allowanceId = $(this).val();
        if (!allowanceId) {
            return;
        }

        $.get(`/payroll/allowance/get-amount/${allowanceId}`, function(response) {
            const amount = response && response.allowance ? parseCurrencyToNumber(response.allowance.amount) : 0;
            $('#allowance_monthly_amount').val(formatPhpCurrency(amount));
        });
    });

    $('#allowance_monthly_amount').off('blur.fixedAllowance').on('blur.fixedAllowance', function() {
        const amount = parseCurrencyToNumber($(this).val());
        $(this).val(formatPhpCurrency(amount));
    });

    $('#add_fixed_allowance_btn').off('click.fixedAllowance').on('click.fixedAllowance', function() {
        if (!record_id) {
            toastr.error('Please select an employee first.');
            return;
        }

        const allowanceId = $('#allowance_type').val();
        const amount = parseCurrencyToNumber($('#allowance_monthly_amount').val());

        if (!allowanceId) {
            toastr.error('Please select type of allowance.');
            return;
        }

        if (amount <= 0) {
            toastr.error('Please enter a valid allowance amount.');
            return;
        }

        $.post('/payroll/allowance/tag', {
            _token: _token,
            employee_id: record_id,
            allowance_id: allowanceId,
            amount: amount,
            action: 'add'
        }).done(function() {
            $('#allowance_type').val('');
            $('#allowance_monthly_amount').val('');
            loadFixedAllowances();
            toastr.success('Fixed allowance saved.');
        }).fail(function() {
            toastr.error('Unable to save fixed allowance.');
        });
    });

    $(document).off('click.fixedAllowanceDelete', '.fixed-allowance-delete');
    $(document).on('click.fixedAllowanceDelete', '.fixed-allowance-delete', function() {
        const taggingId = $(this).data('id');
        if (!taggingId) {
            return;
        }

        $.post('/payroll/allowance/tag', {
            _token: _token,
            id: taggingId,
            employee_id: record_id,
            action: 'remove'
        }).done(function() {
            loadFixedAllowances();
            toastr.success('Fixed allowance removed.');
        }).fail(function() {
            toastr.error('Unable to remove fixed allowance.');
        });
    });
}

function loadFixedAllowances() {
    if (!record_id) {
        $('#fixedAllowanceBody').html('<tr><td colspan="3" class="text-center">No Data</td></tr>');
        return;
    }

    $.post('/payroll/allowance/get-tag', { _token: _token, employee_id: record_id }).done(function(response) {
        const rows = (response && response.allowance) ? response.allowance : [];

        if (!rows.length) {
            $('#fixedAllowanceBody').html('<tr><td colspan="3" class="text-center">No Data</td></tr>');
            return;
        }

        let html = '';
        rows.forEach(function(item) {
            const allowanceName = item.allowances ? item.allowances.name : '-';
            const amount = parseCurrencyToNumber(item.amount || (item.allowances ? item.allowances.amount : 0));

            html += `<tr>
                <td>${allowanceName}</td>
                <td class="text-right">${formatPhpCurrency(amount)}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger fixed-allowance-delete" data-id="${item.id}">
                        REMOVE
                    </button>
                </td>
            </tr>`;
        });

        $('#fixedAllowanceBody').html(html);
    }).fail(function() {
        $('#fixedAllowanceBody').html('<tr><td colspan="3" class="text-center">Unable to load data.</td></tr>');
    });
}

function leave_entitlement_func() {
    module_content = 'leave-entitlement';
    module_url = '/payroll/leaves';
    actions = 'update';
    module_type = 'sub_transaction';
    scion.centralized_button(true, false, true, true);

    if ($.fn.DataTable.isDataTable('#leave_entitlement_table')) {
        $('#leave_entitlement_table').DataTable().destroy();
        $('#leave_entitlement_table').empty();
    }

    scion.create.table(
        'leave_entitlement_table',
        module_url + '/get/' + record_id,
        [
            { data: "leave_types.leave_name", title: "LEAVE TYPE" },
            {
                data: "entitlement_days",
                title: "ENTITLEMENT (DAYS)",
                render: function(data) {
                    const value = parseFloat(data || 0);
                    return Number.isNaN(value) ? '0.00' : value.toFixed(2);
                }
            },
            {
                data: "beginning_balance",
                title: "BEGINNING BALANCE",
                render: function(data) {
                    const value = parseFloat(data || 0);
                    return Number.isNaN(value) ? '0.00' : value.toFixed(2);
                }
            },
            {
                data: "updated_at",
                title: "LAST UPDATED",
                render: function(data) {
                    return data ? moment(data).format('MMM DD, YYYY hh:mm A') : '-';
                }
            },
        ],
        'Bfrtip',
        []
    );

    if ($.fn.DataTable.isDataTable('#leave_history_table')) {
        $('#leave_history_table').DataTable().destroy();
        $('#leave_history_table').empty();
    }

    scion.create.table(
        'leave_history_table',
        module_url + '/history/' + record_id,
        [
            {
                data: "leave_type.leave_name",
                title: "LEAVE TYPE",
                render: function(data) {
                    return data || '-';
                }
            },
            {
                data: "start_date",
                title: "START DATE",
                render: function(data) {
                    return data ? moment(data).format('MMM DD, YYYY') : '-';
                }
            },
            {
                data: "end_date",
                title: "END DATE",
                render: function(data) {
                    return data ? moment(data).format('MMM DD, YYYY') : '-';
                }
            },
            {
                data: "total_leave_hours",
                title: "TOTAL DAYS USED",
                render: function(data) {
                    const value = parseFloat(data || 0);
                    return Number.isNaN(value) ? '0.00' : value.toFixed(2);
                }
            },
            {
                data: "beginning_balance",
                title: "BEGINNING BALANCE",
                render: function(data) {
                    const value = parseFloat(data || 0);
                    return Number.isNaN(value) ? '0.00' : value.toFixed(2);
                }
            },
            {
                data: "balance_after_usage",
                title: "BALANCE AFTER USAGE",
                render: function(data) {
                    const value = parseFloat(data || 0);
                    return Number.isNaN(value) ? '0.00' : value.toFixed(2);
                }
            },
            {
                data: "pay_period",
                title: "PAY PERIOD",
                render: function(data) {
                    return data ? moment(data).format('MMM DD, YYYY') : '-';
                }
            },
            {
                data: "status",
                title: "STATUS",
                render: function(data) {
                    const normalized = String(data).toLowerCase();
                    if (normalized === '1' || normalized === 'approved') {
                        return 'APPROVED';
                    }
                    if (normalized === '2' || normalized === 'decline' || normalized === 'declined') {
                        return 'DECLINED';
                    }
                    return 'PENDING';
                }
            },
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

function parseCurrencyToNumber(value) {
    const numeric = String(value || '').replace(/[^0-9.-]/g, '');
    const amount = parseFloat(numeric);
    return Number.isNaN(amount) ? 0 : amount;
}

function refreshCompensationGovernmentFields(monthlySalary) {
    $.post('/payroll/compensation/compute', {
        _token: _token,
        monthly_salary: parseCurrencyToNumber(monthlySalary)
    }).done((response) => {
        const computed = response.computed || {};
        $('#sss').val(formatPhpCurrency(computed.sss ?? 0));
        $('#pagibig').val(formatPhpCurrency(computed.pagibig ?? 0));
        $('#phic').val(formatPhpCurrency(computed.phic ?? 0));
        $('#tax').val(formatPhpCurrency(computed.tax ?? 0));
    });
}

function validateProfileImageType(event) {
    const input = event.target;
    const file = input && input.files ? input.files[0] : null;

    if (!file) {
        return;
    }

    const allowedTypes = ['image/jpeg', 'image/png'];
    if (!allowedTypes.includes(file.type)) {
        toastr.error('Only JPG, JPEG, and PNG files are allowed.');
        input.value = '';
        $('#viewer').attr('src', '/images/payroll/employee-information/default.png');
        $('#t_profile_img').attr('src', '/images/payroll/employee-information/default.png');
    }
}

function previewReducedImage(event) {
    const input = event.target;
    const file = input && input.files ? input.files[0] : null;

    if (!file) {
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            const maxSide = 600;
            let width = img.width;
            let height = img.height;

            if (width > height && width > maxSide) {
                height = Math.round((height * maxSide) / width);
                width = maxSide;
            } else if (height >= width && height > maxSide) {
                width = Math.round((width * maxSide) / height);
                height = maxSide;
            }

            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);

            $('#viewer').attr('src', canvas.toDataURL('image/jpeg', 0.85));
            $('#t_profile_img').attr('src', canvas.toDataURL('image/jpeg', 0.85));
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

const WORK_CALENDAR_DAYS = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

function normalizeWorkCalendarTime(value) {
    if (!value) {
        return '';
    }

    const raw = String(value).trim();
    if (raw.length >= 5 && raw.indexOf(':') !== -1) {
        return raw.slice(0, 5);
    }

    return raw;
}

function clearWorkCalendarFields() {
    WORK_CALENDAR_DAYS.forEach((day) => {
        $(`#${day}_start_time`).val('');
        $(`#${day}_end_time`).val('');
    });
}

function selectedWorkCalendarDays() {
    return $('.wc-day-checkbox:checked').map(function() {
        return String($(this).val() || '').toLowerCase();
    }).get();
}

function parsePresetDays(value) {
    if (!value) {
        return [];
    }

    if (Array.isArray(value)) {
        return value.map((day) => String(day).toLowerCase());
    }

    try {
        const parsed = JSON.parse(value);
        if (Array.isArray(parsed)) {
            return parsed.map((day) => String(day).toLowerCase());
        }
    } catch (e) {
    }

    return String(value)
        .split(',')
        .map((day) => String(day).trim().toLowerCase())
        .filter((day) => WORK_CALENDAR_DAYS.includes(day));
}

function renderWorkCalendarFromSelection(days, timeIn, timeOff) {
    const normalizedTimeIn = normalizeWorkCalendarTime(timeIn);
    const normalizedTimeOff = normalizeWorkCalendarTime(timeOff);
    const daySet = new Set((days || []).map((day) => String(day).toLowerCase()));

    clearWorkCalendarFields();
    WORK_CALENDAR_DAYS.forEach((day) => {
        if (daySet.has(day)) {
            $(`#${day}_start_time`).val(normalizedTimeIn);
            $(`#${day}_end_time`).val(normalizedTimeOff);
        }
    });
}

function syncSelectionFromCalendarFields() {
    let firstTimeIn = '';
    let firstTimeOff = '';

    WORK_CALENDAR_DAYS.forEach((day) => {
        const start = normalizeWorkCalendarTime($(`#${day}_start_time`).val());
        const end = normalizeWorkCalendarTime($(`#${day}_end_time`).val());
        const selected = start !== '' && end !== '';

        $(`.wc-day-checkbox[value="${day}"]`).prop('checked', selected);

        if (selected && firstTimeIn === '' && firstTimeOff === '') {
            firstTimeIn = start;
            firstTimeOff = end;
        }
    });

    $('#wc_time_in').val(firstTimeIn);
    $('#wc_time_off').val(firstTimeOff);
}

function applySelectedDaysAndTime() {
    const days = selectedWorkCalendarDays();
    const timeIn = normalizeWorkCalendarTime($('#wc_time_in').val());
    const timeOff = normalizeWorkCalendarTime($('#wc_time_off').val());

    if (days.length === 0) {
        clearWorkCalendarFields();
        return;
    }

    if (!timeIn || !timeOff) {
        return;
    }

    renderWorkCalendarFromSelection(days, timeIn, timeOff);
}

function hydrateWorkCalendarFromRecord() {
    clearWorkCalendarFields();

    if (!store_record || !store_record.employee || !store_record.employee.works_calendar) {
        $('.wc-day-checkbox').prop('checked', false);
        $('#wc_time_in').val('');
        $('#wc_time_off').val('');
        $('#wc_is_flexi_time').prop('checked', false);
        return;
    }

    const workCalendar = store_record.employee.works_calendar;
    WORK_CALENDAR_DAYS.forEach((day) => {
        $(`#${day}_start_time`).val(normalizeWorkCalendarTime(workCalendar[`${day}_start_time`]));
        $(`#${day}_end_time`).val(normalizeWorkCalendarTime(workCalendar[`${day}_end_time`]));
    });

    $('#wc_is_flexi_time').prop('checked', parseInt(workCalendar.is_flexi_time || 0, 10) === 1);
    syncSelectionFromCalendarFields();
}

function work_calendar_func() {
    module_content = 'work-calendar';
    module_url = '/payroll/work-calendar';
    actions = 'update';
    module_type = 'sub_transaction';
    scion.centralized_button(true, false, true, true);

    customSchedule();
    hydrateWorkCalendarFromRecord();
    loadWorkCalendarPresets();

    $('.wc-day-checkbox').off('change.workcalendar').on('change.workcalendar', function() {
        applySelectedDaysAndTime();
    });

    $('#wc_time_in, #wc_time_off').off('change.workcalendar').on('change.workcalendar', function() {
        applySelectedDaysAndTime();
    });
}

function customSchedule() {
    // Kept for compatibility with existing references.
}

function workTypeModal(day) {
    if (day) {
        $(`.wc-day-checkbox[value="${day}"]`).prop('checked', true);
    }
    applySelectedDaysAndTime();
}

function loadWorkCalendarPresets() {
    const $presetList = $('#wc_preset_list');
    if (!$presetList.length) {
        return;
    }

    $.get('/payroll/work-calendar/presets', function(response) {
        const presets = response && response.presets ? response.presets : [];
        $presetList.empty().append('<option value="">Select Preset</option>');

        presets.forEach((preset) => {
            const selectedDays = parsePresetDays(preset.selected_days);
            const timeIn = normalizeWorkCalendarTime(preset.time_in || preset.start_time);
            const timeOff = normalizeWorkCalendarTime(preset.time_off || preset.end_time);
            const isFlexi = parseInt(preset.is_flexi_time || 0, 10) === 1;
            const shortDays = selectedDays.map((day) => day.slice(0, 3).toUpperCase()).join(', ');
            const label = `${preset.name} (${shortDays} | ${timeIn} - ${timeOff}${isFlexi ? ' | FLEXI' : ''})`;
            const option = $('<option/>', { value: preset.id, text: label });
            option.attr('data-selected-days', JSON.stringify(selectedDays));
            option.attr('data-time-in', timeIn);
            option.attr('data-time-off', timeOff);
            option.attr('data-is-flexi', isFlexi ? '1' : '0');
            $presetList.append(option);
        });
    });
}

function saveWorkCalendarPreset() {
    const name = $('#wc_preset_name').val().trim();
    const selectedDays = selectedWorkCalendarDays();
    const timeIn = normalizeWorkCalendarTime($('#wc_time_in').val());
    const timeOff = normalizeWorkCalendarTime($('#wc_time_off').val());

    if (!name) {
        toastr.error('Preset name is required.');
        return;
    }

    if (selectedDays.length === 0) {
        toastr.error('Select at least one work day.');
        return;
    }

    if (!timeIn || !timeOff) {
        toastr.error('Time in and time off are required.');
        return;
    }

    const orderedDays = WORK_CALENDAR_DAYS.filter((day) => selectedDays.includes(day));

    $.post('/payroll/work-calendar/preset', {
        _token: _token,
        name: name,
        selected_days: selectedDays,
        time_in: timeIn,
        time_off: timeOff,
        is_flexi_time: $('#wc_is_flexi_time').is(':checked') ? 1 : 0,
        start_day: orderedDays[0] || selectedDays[0],
        start_time: timeIn,
        end_day: orderedDays[orderedDays.length - 1] || selectedDays[selectedDays.length - 1],
        end_time: timeOff,
    }).done(function() {
        toastr.success('Work calendar preset saved.');
        $('#wc_preset_name').val('');
        loadWorkCalendarPresets();
    }).fail(function(xhr) {
        const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to save work calendar preset.';
        toastr.error(message);
    });
}

function applyCalendarPreset() {
    const selected = $('#wc_preset_list option:selected');
    const selectedId = selected.val();
    if (!selectedId) {
        applySelectedDaysAndTime();
        return;
    }

    const days = parsePresetDays(selected.attr('data-selected-days'));
    const timeIn = normalizeWorkCalendarTime(selected.attr('data-time-in'));
    const timeOff = normalizeWorkCalendarTime(selected.attr('data-time-off'));
    const isFlexi = String(selected.attr('data-is-flexi') || '0') === '1';

    $('.wc-day-checkbox').prop('checked', false);
    days.forEach((day) => {
        $(`.wc-day-checkbox[value="${day}"]`).prop('checked', true);
    });
    $('#wc_time_in').val(timeIn);
    $('#wc_time_off').val(timeOff);
    $('#wc_is_flexi_time').prop('checked', isFlexi);
    applySelectedDaysAndTime();
}
