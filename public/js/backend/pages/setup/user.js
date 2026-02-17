$(function() {
    modal_content = 'users';
    module_url = '/settings/users';
    module_type = 'custom';
    page_title = "Users";

    scion.centralized_button(false, true, true, true);
    
    scion.create.table(
        modal_content + '_table',  
        module_url + '/get', 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'"+module_url+"/edit/', "+ row.id + ' )"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            { data: null, title: "Name", render: function(data, type, row, meta) {
                return row.firstname + ' ' + (row.middlename !== '' && row.middlename !== null?row.middlename + ' ':'') + row.lastname + (row.suffix !== '' && row.suffix !== null?' ' + row.suffix:'');
            }},
            { data: 'email', title: 'Email'},
            { data: null, title: 'Roles', render: function(data, type, row, meta) {
                if (!row.roles || row.roles.length === 0) {
                    return '-';
                }
                return row.roles.map(function(role) { return role.name; }).join(', ');
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
    $('#'+modal_content+'_table').DataTable().draw();
    scion.create.sc_modal(modal_content+'_form').hide('all', modalHideFunction);
}

function error() {
    toastr.error('Record already exist.', 'Failed')
}

function delete_success() {
    $('#'+modal_content+'_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token,
        firstname: $('#firstname').val(),
        middlename: $('#middlename').val(),
        lastname: $('#lastname').val(),
        suffix: $('#suffix').val(),
        email: $('#email').val(),
        status: $('#status').val(),
        role_ids: $('#role_ids').val() || []
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

function customFunc() {
    modal_content = 'users';
    module_url = '/settings/users';
    module_type = 'custom';
    page_title = "Users";

    scion.centralized_button(false, true, true, true);
}
