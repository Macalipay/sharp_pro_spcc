$(function() {
    modal_content = 'account_types';
    module_url = '/accounting/account_types';
    module_type = 'custom';
    page_title = "Account Type";

    scion.centralized_button(false, true, true, true);
    scion.create.table(
        'account_type_table',
        module_url + '/get',
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/accounting/account_types/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            { data: "DT_RowIndex", title:"#" },
            {
                data: "category",
                title: "Category",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "account_type",
                title: "Account Type",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            }
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
    $('#account_type_table').DataTable().draw();
    scion.create.sc_modal('benefits_form').hide('all', modalHideFunction)
}

function error() {}

function delete_success() {
    $('#account_type_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token,
        category: $('#category').val(),
        account_type: $('#account_type').val(),
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
