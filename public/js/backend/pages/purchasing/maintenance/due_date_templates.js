$(function() {
    modal_content = 'due_date_templates';
    module_url = '/purchasing/due_date_templates';
    module_type = 'custom';
    page_title = "Due Date Templates";

    scion.centralized_button(false, true, true, true);
    scion.create.table(
        'due_date_templates_table',
        module_url + '/get',
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/purchasing/due_date_templates/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            { data: "DT_RowIndex", title: "#" },
            {
                data: "template_text",
                title: "Template",
                render: function(data) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "description",
                title: "Description",
                render: function(data) {
                    const val = data || '-';
                    return '<span class="expandable" title="' + val + '">' + val + '</span>';
                }
            }
        ], 'Bfrtip', []
    );
});

function success() {
    $('#due_date_templates_table').DataTable().draw();
    scion.create.sc_modal('due_date_templates_form').hide('all', modalHideFunction);
}

function error() {}

function delete_success() {
    $('#due_date_templates_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    return {
        _token: _token,
        template_text: $('#template_text').val(),
        description: $('#description').val()
    };
}

function generateDeleteItems() {}

function modalShowFunction() {
    scion.centralized_button(true, false, true, true);
}

function modalHideFunction() {
    scion.centralized_button(false, true, true, true);
}

