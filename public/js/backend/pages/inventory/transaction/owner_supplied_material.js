$(function() {
    modal_content = 'owner_supplied_material';
    module_content = 'owner_supplied_material';
    module_url = '/inventory/owner_supplied_material';
    module_type = 'custom';
    page_title = "Owner Supplied Material";

    scion.centralized_button(false, true, true, true);
    scion.create.table(
        'osm_table',
        module_url + '/get',
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" title="EDIT" onclick="scion.record.edit('+"'/inventory/inventory/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                html += '<a href="#" class="align-middle edit" title="TRANSACTION" onclick="inventory_transaction('+ row.id +')"><i class="fas fa-plus-circle"></i></a>';
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
            { data: "description",title: "Description", render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            { data: "total_count",title: "Total Count", render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            { data: "quantity_stock",title: "Quantity Stock", render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
        ], 'Bfrtip', []
    );

    $('.create').click(function() {
        modal_content = 'owner_supplied_material';
        module_content = 'owner_supplied_material';
        module_url = '/inventory/owner_supplied_material';
        record_id = null;
        actions = 'save';
    });

});

function success() {
    switch(actions) {
        case 'save':
            switch(module_content) {
                case 'owner_supplied_material':
                    $('#osm_table').DataTable().draw();
                    scion.create.sc_modal('owner_supplied_material_form').hide('all', modalHideFunction);
                    break;

                case 'inventory_transaction':
                    $('#osm_table').DataTable().draw();
                    scion.create.sc_modal('transaction_form').hide('all', modalHideFunction);
                    break;
            }
            break;
        case 'update':
            switch(module_content) {
                case 'inventory':
                    $('#osm_table').DataTable().draw();
                    scion.create.sc_modal('inventory_form').hide('all', modalHideFunction);
                    preparation_func();
                    break;
            }
            break;
    }
    $('#osm_table').DataTable().draw();
    scion.create.sc_modal('inventory_form').hide('all', modalHideFunction)
    scion.create.sc_modal('damage_form').hide('all', modalHideFunction)

}

function error() {
    toastr.error('Record already exist.', 'Failed')
}
function delete_success() {
    $('#osm_table').DataTable().draw();
}

function inventory_transaction(id) {
    module_content = 'inventory_transaction';
    module_url = '/inventory/owner_supplied_material/transaction';
    record_id = id;
    actions = 'save';
    scion.create.sc_modal("inventory_transaction_form", 'INVENTORY TRANSACTION').show(modalShowFunction);
}


function delete_error() {}

function generateData() {
    switch(module_content) {
        case 'owner_supplied_material':
            form_data = {
                _token: _token,
                material_id: $('#material_id').val(),
                project_id: $('#project_id').val(),
                description: $('#description').val(),
            };
            break;

        case 'inventory_transaction':
            form_data = {
                _token: _token,
                inventory_id: record_id,
                quantity: $('#quantity').val(),
                date: $('#date').val(),
                remarks: $('#remarks').val(),
            };
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
