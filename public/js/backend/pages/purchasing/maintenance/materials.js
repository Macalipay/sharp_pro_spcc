$(function() {
    modal_content = 'materials';
    module_url = '/purchasing/materials';
    module_type = 'custom';
    page_title = "Materials";

    scion.centralized_button(false, true, true, true);
    scion.create.table(
        'materials_table',
        module_url + '/get',
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/purchasing/materials/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            { data: "DT_RowIndex", title: "#" },
            {
                data: "item_name",
                title: "Item Name",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "item_code",
                title: "Item Code",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "category",
                title: "Category",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "unit_of_measure",
                title: "U/M",
                render: function(data, type, row, meta) {
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
    $('#materials_table').DataTable().draw();
    scion.create.sc_modal('benefits_form').hide('all', modalHideFunction)
}

function error() {}

function delete_success() {
    $('#materials_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token,
        item_name: $('#item_name').val(),
        item_code: $('#item_code').val(),
        category: $('#category').val(),
        brand: $('#brand').val(),
        unit_of_measure: [].concat($('#unit_of_measure').val() || []),
    
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

$(document).ready(function() {
    $('#unit_of_measure').select2({
        placeholder: "Select Units",
        width: '100%',
        allowClear: true
    });
});
