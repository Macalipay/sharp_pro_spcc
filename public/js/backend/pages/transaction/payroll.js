var emp_id = null;
var date_selected = null;
var copied_schedule = [];

$(function() {
    modal_content = 'payroll';
    module_url = '/payroll';
    module_type = 'custom';
    page_title = "Payroll";

    scion.centralized_button(true, true, true, true);
    
    // scion.create.table(
    //     'payroll_table',  
    //     module_url + '/get/all/' + scion.getDateRange($('#date-filter').val(), 1).first + '/' + scion.getDateRange($('#date-filter').val(), 1).last, 
    //     [], 'Bfrtip', ['csv']
    // );

});


function success() {
    switch(actions) {
        case 'save':
            break;
        case 'update':
            break;
    }
    $('#employee_table').DataTable().draw();
    scion.create.sc_modal('scheduling_form').hide('all', modalHideFunction);
}

function error() {}

function delete_success() {
    $('#employee_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token,
        employee_id: emp_id,
        date: date_selected,
        start_time: $('#start_time').val(),
        end_time: $('#end_time').val(),
        type: $('#type').val(),
        work_assignment_id: $('#work_assignment_id').val(),
        earning_id: $('#earning_id').val(),
        details:'',
        type_description:$('#type_description').val(),
        status:$('#status').val(),
    };

    return form_data;
}

function generateDeleteItems(){}


function modalShowFunction() {
    scion.centralized_button(true, false, true, true);
}

function modalHideFunction() {
    scion.centralized_button(true, true, true, true);
}

function subFunction(property) {
    $('.employee-name span').text(selected_data[property].firstname + ' ' + (selected_data[property].middlename === '' || selected_data[property].middlename === null?'':selected_data[property].middlename + ' ') + selected_data[property].lastname + (selected_data[property].suffix === '' || selected_data[property].suffix === null?'':' ' + selected_data[property].suffix));
    $('.employee-email span').text(selected_data[property].email);
    $('.employee-picture').attr('style', 'background:url(/images/payroll/employee-information/' + selected_data[property].profile_img+')no-repeat;');
}
