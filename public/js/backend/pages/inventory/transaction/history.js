$(function() {
    modal_content = 'inventory_history';
    module_url = '/inventory/history';
    module_type = 'custom';
    page_title = "Inventory History";

    scion.centralized_button(false, true, true, true);
    scion.create.table(
        'history_table',
        module_url + '/get',
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/inventory/history/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            { data: "material.item_code",title: "Code", render: function(data, type, row, meta) {
                return '<span class="expandable" title="' + data + '">' + data + '</span>';
            }
            },
            { data: "material.item_name",title: "Name", render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            { data: "project.project_name",title: "Project", render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {data: "remarks", title: "Remarks", render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {data: "quantity", title: "Quantity", render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {data: "date", title: "Date", render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
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
    $('#classes_table').DataTable().draw();
    scion.create.sc_modal('benefits_form').hide('all', modalHideFunction)
}

function error() {
    toastr.error('Record already exist.', 'Failed')
}
function delete_success() {
    $('#classes_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token,
        description: $('#description').val(),
        payment_schedule: $('#payment_schedule').val(),
        tax_applicable: $('#tax_applicable').val(),
        government_mandated_benefits: $('#government_mandated_benefits').val(),
        other_company_benefits: $('#other_company_benefits').val()
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
