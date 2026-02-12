$(function() {
    modal_content = 'sites';
    module_url = '/purchasing/sites';
    module_type = 'custom';
    page_title = "Sites";

    scion.centralized_button(false, true, true, true);
    scion.create.table(
        'sites_table',
        module_url + '/get',
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/purchasing/sites/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            { data: "DT_RowIndex", title:"#" },
            {
                data: "project_name",
                title: "Project Name",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "location",
                title: "Location",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "id",
                title: "Person in Charge",
                render: function(data, type, row, meta) {
                    if (row.employee && row.employee.firstname && row.employee.lastname) {
                        var fullName = row.employee.firstname + ' ' + row.employee.lastname;
                        return '<span class="expandable" title="' + fullName + '">' + fullName + '</span>';
                    } else {
                        return '<span class="expandable" title="N/A">N/A</span>';
                    }
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
    $('#sites_table').DataTable().draw();
    scion.create.sc_modal('benefits_form').hide('all', modalHideFunction)
}

function error() {}

function delete_success() {
    $('#sites_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token,
        project_name: $('#project_name').val(),
        location: $('#location').val(),
        person_in_charge: $('#person_in_charge').val(),
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

function getContact() {
    var selectedValue = $('#person_in_charge').val();

    $.get('/purchasing/sites/getEmployee/' + selectedValue, function(resp) { 
        $('#pic_contact').val(resp[0].phone1);
    });
}
