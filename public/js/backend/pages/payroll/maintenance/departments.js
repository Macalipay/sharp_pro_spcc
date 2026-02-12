$(function() {
    modal_content = 'departments';
    module_url = '/payroll/department';
    module_type = 'custom';
    page_title = "Department";

    scion.centralized_button(false, true, true, true);
    
    scion.create.table(
        'departments_table',  
        module_url + '/get', 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/payroll/department/edit/', "+ row.id + ' )"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            {
                data: "code",
                title: "Department Code",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "description",
                title: "Description",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            }
        
        ], 'Bfrtip', [],
    );

    $('#departments_table').DataTable().order([1, 'asc']).draw();

});

function success() {
    switch(actions) {
        case 'save':
            break;
        case 'update':
            break;
    }
    $('#departments_table').DataTable().draw();
    scion.create.sc_modal('departments_form').hide('all', modalHideFunction);
}

function error() {
    toastr.error('Record already exist.', 'Failed')
}

function delete_success() {
    $('#departments_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token,
        description: $('#description').val(),
        code: $('#code').val()

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