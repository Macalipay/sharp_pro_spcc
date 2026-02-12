$(function() {
    modal_content = 'allowance';
    module_url = '/payroll/allowance';
    module_type = 'custom';
    page_title = "Allowance";

    scion.centralized_button(false, true, true, true);
    scion.create.table(
        'allowance_table',  
        module_url + '/get', 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/payroll/allowance/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            {
                data: "name",
                title: "NAME",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "description",
                title: "DESCRIPTION",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "amount",
                title: "AMOUNT",
                render: function(data, type, row, meta) {
                    return scion.currency(data);
                }
            },
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
    $('#allowance_table').DataTable().draw();
    scion.create.sc_modal('allowance_form').hide('all', modalHideFunction)
}

function error() {
    toastr.error('Record already exist.', 'Failed')
}

function delete_success() {
    $('#allowance_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token,
        name: $('#name').val(),
        description: $('#description').val(),
        amount: $('#amount').val()
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