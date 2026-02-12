$(function() {
    modal_content = 'role';
    module_url = '/settings/role';
    module_type = 'custom';
    page_title = "Role";

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
            { data: 'name', title: 'Name'}
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
        name: $('#name').val()
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