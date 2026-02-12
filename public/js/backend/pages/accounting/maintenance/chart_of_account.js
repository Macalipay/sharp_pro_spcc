$(function() {
    modal_content = 'chart_of_accounts';
    module_url = '/accounting/chart_of_accounts';
    module_type = 'custom';
    page_title = "Chart Of Accounts";

    scion.centralized_button(false, true, true, true);
    scion.create.table(
        'account_table',
        module_url + '/get',
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/accounting/chart_of_accounts/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            {
                data: "account_number",
                title: "Account Number",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "account_name",
                title: "Account Name",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "account_type.account_type",
                title: "Account Type",
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
            },
            {
                data: "normal_balance",
                title: "Normal Balance",
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
    $('#account_table').DataTable().draw();
    scion.create.sc_modal('benefits_form').hide('all', modalHideFunction)
}

function error() {}

function delete_success() {
    $('#account_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token,
        account_number: $('#account_number').val(),
        account_name: $('#account_name').val(),
        account_type: $('#account_type').val(),
        description: $('#description').val(),
        normal_balance: $('#normal_balance').val(),
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
