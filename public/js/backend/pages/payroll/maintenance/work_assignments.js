$(function() {
    modal_content = 'work_assignments';
    module_url = '/payroll/work_assignments';
    module_type = 'custom';
    page_title = "Work Assignments";

    scion.centralized_button(false, true, true, true);
    scion.create.table(
        'work_assignments_table',  
        module_url + '/get', 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/payroll/"+modal_content+"/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            { data: "title", title: "TITLE", render: function(data, type, row, meta) {
                return '<span class="expandable" title="' + data + '">' + data + '</span>';
            }},
            { data: "status", title: "STATUS", render: function(data, type, row, meta) {
                var html = "";
                if(row.status === 1) {
                    html = '<i class="fas fa-check text-success"></i>';
                }
                else {
                    html = '<i class="fas fa-times text-danger"></i>';
                }
                return html;
            }}
        ], 'Bfrtip', []
    );

});

function success() {
    switch(actions) {
        case 'save':
            break;
        case 'update':
            break;
    }
    $('#work_assignments_table').DataTable().draw();
    scion.create.sc_modal('work_assignments_form').hide('all', modalHideFunction)
}

function error() {
    toastr.error('Record already exist.', 'Failed');
}

function delete_success() {
    $('#work_assignments_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token,
        title: $('#title').val(),
        status: $('#status').val()
    };

    return form_data;
}

function generateDeleteItems(){}


function modalShowFunction() {
    scion.centralized_button(true, false, true, true);
}

function modalHideFunction() {
    scion.centralized_button(false, true, true, true);
}