var claim_id = null;
var release_data = {};

$(function() {
    modal_content = 'quit_claims';
    module_url = '/payroll/quit-claims';
    module_type = 'custom';
    page_title = "Quit Claims";

    scion.centralized_button(true, true, true, true);
    scion.create.table(
        'quit_claims_table',  
        module_url + '/get', 
        [
            // { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
            //     var html = "";
            //     html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
            //     html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/payroll/leave_request/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
            //     return html;
            // }},
            {
                data: null,
                title: "NAME",
                className: "name-td",
                render: function(data, type, row, meta) {
                    return `<span class="expandable" title="${row.firstname} ${row.lastname}">${row.firstname} ${row.lastname}</span>`;
                }
            },
            {
                data: null,
                title: "STATUS",
                render: function(data, type, row, meta) {
                    var statusMap = {
                        "2": "TERMINATED",
                        "3": "RESIGNED",
                        "4": "SUSPENDED",
                        "5": "DECEASED",
                        "9": "END OF CONTRACT"
                    };
                    
                    var status = statusMap[row.status] || ''; 
                    return '<span class="expandable" title="' + status + '">' + status + '</span>';
                }
            },
            {
                data: null,
                title: "FINAL PAY",
                className: "price-td",
                render: function(data, type, row, meta) {
                    var month_pay = 0;
                    var last_pay = 0;
                    var final_pay = 0;

                    var month = row.data_load.month;
                    var details = row.data_load.details;

                    const result = row.employments_tab !== null?getMonthsDifference(new Date(row.employments_tab.employment_date) < new Date(`${$('#year').val()}-01-01`)?`${$('#year').val()}-01-01`:row.employments_tab.employment_date):null;
                    const date_output = row.employments_tab !== null?(new Date(row.employments_tab.employment_date) < new Date(`${$('#year').val()}-01-01`)?`${$('#year').val()}-01-01`:row.employments_tab.employment_date):null;

                    var date = result !== null?(result.isLessThanOneMonth?0:result.totalMonths):0;

                    var salary = row.compensations !== null?row.compensations.monthly_salary:0;

                    var pay = (parseFloat(salary) * date)/12;
                    
                    const absent = row.absents.length;
                    const daily = parseFloat(row.compensations !== null?row.compensations.daily_salary:0);
                    
                    const absent_rate = daily*absent;

                    var total = pay - absent_rate;

                    var total_pay = parseFloat((details?.net_pay ?? 0) + total);

                    month_pay = total.toFixed(2);
                    last_pay = (details?.net_pay ?? 0).toFixed(2);
                    final_pay = ((parseFloat(total_pay.toFixed(2)) + parseFloat(row.total_additionals)) - parseFloat(row.total_deductions));

                    console.log(final_pay, row.total_additionals, row.total_additionals);

                    return scion.currency(final_pay);
                }
            },
            {
                data: null,
                title: "DUE FOR RELEASE",
                render: function(data, type, row, meta) {
                    return row.clearance !== null ? moment(row.clearance.clearance_date).add(30, 'days').format('MMM DD, YYYY'):'-';
                }
            },
            {
                data: null,
                title: "CLAIM STATUS",
                render: function(data, type, row, meta) {return row.quit_claims !== null
                    ? 'RELEASED'
                    : '<button class="btn btn-sm btn-primary" onclick=\'releasedClaims(' + JSON.stringify(row) + ')\'>RELEASE</button>';
                }
            },
            {
                data: null,
                title: "DUE FOR RELEASE",
                render: function(data, type, row, meta) {
                    return row.quit_claims !== null ? moment(row.quit_claims.date_released).format('MMM DD, YYYY'):'-';
                }
            },
            {
                data: null,
                title: "ACTION",
                render: function(data, type, row, meta) {
                    return row.quit_claims !== null ? '-': '<a href="#" onclick="otherAdditions('+row.id+')">Other Additions</a> | <a href="#" onclick="otherDeductions('+row.id+')">Other Deductions</a>';
                }
            },
        ], '', []
    );

});

function success() {
    switch(actions) {
        case 'save':
            break;
        case 'update':
            break;
    }
    $('#quit_claims_table').DataTable().draw();
    if(modal_content === "quit_claims") {
        // scion.create.sc_modal('quit_claims_form').hide('all', modalHideFunction);
    }
    else if(modal_content === "quit_claims_additions") {
        scion.create.sc_modal('quit_claims_additions_form').hide();
        $('#quit_claims_additions_table').DataTable().draw();
    }
    else if(modal_content === "quit_claims_deductions") {
        scion.create.sc_modal('quit_claims_deductions_form').hide();
        $('#quit_claims_deductions_table').DataTable().draw();
    }
}

function error() {
    toastr.error('Record already exist.', 'Failed')
}

function delete_success() {
    if(modal_content === "quit_claims") {
        $('#quit_claims_table').DataTable().draw();
    }
    else if(modal_content === "quit_claims_deductions") {
        $('#quit_claims_deductions_table').DataTable().draw();
    }
    else if(modal_content === "quit_claims_additions") {
        $('#quit_claims_additions_table').DataTable().draw();
    }
}

function delete_error() {}

function generateData() {
    switch(actions) {
        case 'save':
            if(modal_content === "quit_claims") {
                form_data = {
                    _token: _token,
                    employee_id: store_record.employee.id,
                    amount: $('#total_pay').val(),
                    status: 'for-release'
                };
            }
            else if(modal_content === "quit_claims_deductions") {
                form_data = {
                    _token: _token,
                    employee_id: claim_id,
                    deduction_type_id: $('#deduction_type_id').val(),
                    deduction_description: $('#deduction_description').val(),
                    deduction_amount: $('#deduction_amount').val(),
                    deduction_remarks: $('#deduction_remarks').val()
                };
            }
            else if(modal_content === "quit_claims_additions") {
                form_data = {
                    _token: _token,
                    employee_id: claim_id,
                    earning_type_id: $('#earning_type_id').val(),
                    description: $('#description').val(),
                    amount: $('#amount').val(),
                    remarks: $('#remarks').val()
                };
            }
            break;
        case 'update':
            if(modal_content === "quit_claims") {
                form_data = {
                    _token: _token,
                    employee_id: store_record.employee.id,
                    amount: $('#total_pay').val(),
                    status: 'for-release'
                };
            }
            else if(modal_content === "quit_claims_deductions") {
                form_data = {
                    _token: _token,
                    employee_id: claim_id,
                    deduction_type_id: $('#deduction_type_id').val(),
                    deduction_description: $('#deduction_description').val(),
                    deduction_amount: $('#deduction_amount').val(),
                    deduction_remarks: $('#deduction_remarks').val()
                };
            }
            else if(modal_content === "quit_claims_additions") {
                form_data = {
                    _token: _token,
                    employee_id: claim_id,
                    earning_type_id: $('#earning_type_id').val(),
                    description: $('#description').val(),
                    amount: $('#amount').val(),
                    remarks: $('#remarks').val()
                };
            }
            break;
    }

    return form_data;
}

function generateDeleteItems(){}


function modalShowFunction() {
    scion.centralized_button(true, false, true, true);
}

function modalHideFunction() {
    scion.centralized_button(false, true, true, true);
}

function updateActionClose() {
    modal_content = 'quit_claims';
    module_url = '/payroll/quit-claims';
    module_type = 'custom';
    page_title = "Quit Claims";

    scion.centralized_button(true, true, true, true);
}

function lookupReturn() {
    $('#employee_name').val(`${store_record.employee.firstname + " " + store_record.employee.lastname}`);
    record_id = null;
    actions = 'save';
    scion.centralized_button(true, false, true, true);
    
    $('.sc-title-bar').text('QUIT CLAIMS');

    $.get(`/payroll/quit-claims/get-last-pay/${store_record.employee.id}`, (response) => {
        var month = response.month;
        var details = response.details;

        const result = month.employments_tab !== null?getMonthsDifference(new Date(month.employments_tab.employment_date) < new Date(`${$('#year').val()}-01-01`)?`${$('#year').val()}-01-01`:month.employments_tab.employment_date):null;
        const date_output = month.employments_tab !== null?(new Date(month.employments_tab.employment_date) < new Date(`${$('#year').val()}-01-01`)?`${$('#year').val()}-01-01`:month.employments_tab.employment_date):null;

        var date = result !== null?(result.isLessThanOneMonth?0:result.totalMonths):0;

        var salary = month.compensations !== null?month.compensations.monthly_salary:0;

        var pay = (parseFloat(salary) * date)/12;
        
        const absent = month.absents.length;
        const daily = parseFloat(month.compensations !== null?month.compensations.daily_salary:0);
        
        const absent_rate = daily*absent;

        var total = pay - absent_rate;

        var total_pay = parseFloat((details?.net_pay ?? 0) + total);

        $('#month_pay').val(total.toFixed(2));
        $('#last_pay').val((details?.net_pay ?? 0).toFixed(2));
        $('#total_pay').val(total_pay.toFixed(2));


    });
}

function editShow() {
    // $('#employee_name').val(`${store_record.leave.employee.firstname + " " + store_record.leave.employee.lastname}`);
}

function getMonthsDifference(startDate) {
    const start = new Date(startDate);
    const end = new Date(); 

    const yearsDifference = end.getFullYear() - start.getFullYear();
    const monthsDifference = end.getMonth() - start.getMonth();

    let totalMonths = (yearsDifference * 12) + monthsDifference;

    if (end.getDate() < start.getDate()) {
        totalMonths--;
    }

    const isLessThanOneMonth = totalMonths < 1;

    return {
        totalMonths,
        isLessThanOneMonth
    };
}

function releasedClaims(data) {
    release_data = {
        _token: _token,
        employee_id: data.id || null,
        amount: data.amount || null,
        status: 'release'
    };

    var claim_id = data.id;

    
    $.get(`/payroll/quit-claims/get-last-pay/${claim_id}`, (response) => {
        var month = response.month;
        var details = response.details;

        const result = month.employments_tab !== null?getMonthsDifference(new Date(month.employments_tab.employment_date) < new Date(`${$('#year').val()}-01-01`)?`${$('#year').val()}-01-01`:month.employments_tab.employment_date):null;
        const date_output = month.employments_tab !== null?(new Date(month.employments_tab.employment_date) < new Date(`${$('#year').val()}-01-01`)?`${$('#year').val()}-01-01`:month.employments_tab.employment_date):null;

        var date = result !== null?(result.isLessThanOneMonth?0:result.totalMonths):0;

        var salary = month.compensations !== null?month.compensations.monthly_salary:0;

        var pay = (parseFloat(salary) * date)/12;
        
        const absent = month.absents.length;
        const daily = parseFloat(month.compensations !== null?month.compensations.daily_salary:0);
        
        const absent_rate = daily*absent;

        var total = pay - absent_rate;

        var total_pay = parseFloat((details?.net_pay ?? 0) + total);
        
        release_data.amount = (total_pay + response.additions) - response.deductions;

    });

    scion.create.sc_modal("release_confirm", 'Release').show(modalShowFunction);

    console.log(release_data);
}

function yesClaim() {
    $.post('/payroll/quit-claims/save', release_data).done(function(response) {
        $('#quit_claims_table').DataTable().draw();
        scion.create.sc_modal("release_confirm").hide('all', modalHideFunction);
    });
}

function otherAdditions(id) {
    modal_content = 'quit_claims_additions';
    module_url = '/payroll/quit-claims-additions';
    module_type = 'custom';
    page_title = "Other Additions";

    claim_id = id;
    actions = 'save';

    if ($.fn.DataTable.isDataTable('#quit_claims_additions_table')) {
        $('#quit_claims_additions_table').DataTable().destroy();
    }
    
    scion.create.table(
        'quit_claims_additions_table',  
        '/payroll/quit-claims-additions/get/'+claim_id, 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/payroll/quit-claims-additions/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            {
                data: null,
                title: "EARNING",
                render: function(data, type, row, meta) {
                    return row.earning.name;
                }
            },
            {
                data: 'description',
                title: "DESCRIPTION",
                render: function(data, type, row, meta) {
                    return `<span class="expandable" title="${data!==null?data:'-'}">${data!==null?data:'-'}</span>`;
                }
            },
            {
                data: 'remarks',
                title: "REMARKS",
                render: function(data, type, row, meta) {
                    return `<span class="expandable" title="${data!==null?data:'-'}">${data!==null?data:'-'}</span>`;
                }
            },
            {
                data: 'amount',
                title: "AMOUNT",
                render: function(data, type, row, meta) {
                    return `<span class="expandable" title="${data}">${scion.currency(data)}</span>`;
                }
            }
        ], '', [], false, false
    );

    scion.create.sc_modal("additions_modal", 'Other Additions').show(modalShowFunction);
}

function addAdditionals() {
    actions = 'save';
    scion.create.sc_modal("quit_claims_additions_form", 'Other Additions').show(modalShowFunction);
}

function otherDeductions(id) {
    modal_content = 'quit_claims_deductions';
    module_url = '/payroll/quit-claims-deductions';
    module_type = 'custom';
    page_title = "Other Deductions";

    claim_id = id;
    actions = 'save';

    if ($.fn.DataTable.isDataTable('#quit_claims_deductions_table')) {
        $('#quit_claims_deductions_table').DataTable().destroy();
    }
    
    scion.create.table(
        'quit_claims_deductions_table',  
        '/payroll/quit-claims-deductions/get/'+claim_id, 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/payroll/quit-claims-deductions/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            {
                data: null,
                title: "DEDUCTIONS",
                render: function(data, type, row, meta) {
                    return row.deductions.name;
                }
            },
            {
                data: 'deduction_description',
                title: "DESCRIPTION",
                render: function(data, type, row, meta) {
                    return `<span class="expandable" title="${data!==null?data:'-'}">${data!==null?data:'-'}</span>`;
                }
            },
            {
                data: 'deduction_remarks',
                title: "REMARKS",
                render: function(data, type, row, meta) {
                    return `<span class="expandable" title="${data!==null?data:'-'}">${data!==null?data:'-'}</span>`;
                }
            },
            {
                data: 'deduction_amount',
                title: "AMOUNT",
                render: function(data, type, row, meta) {
                    return `<span class="expandable" title="${data}">${scion.currency(data)}</span>`;
                }
            }
        ], '', [], false, false
    );

    scion.create.sc_modal("deductions_modal", 'Other Deductions').show(modalShowFunction);
}

function addDeductions() {
    actions = 'save';
    record_id = null;
    scion.create.sc_modal("quit_claims_deductions_form", 'Other Deductions').show(modalShowFunction);
}