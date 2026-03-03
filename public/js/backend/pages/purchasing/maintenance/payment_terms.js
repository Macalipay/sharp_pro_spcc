$(function() {
    modal_content = 'payment_terms';
    module_url = '/purchasing/payment_terms';
    module_type = 'custom';
    page_title = "Payment Terms";

    scion.centralized_button(false, true, true, true);
    scion.create.table(
        'payment_terms_table',
        module_url + '/get',
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/purchasing/payment_terms/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            { data: "DT_RowIndex", title: "#" },
            {
                data: "term_text",
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
    $('#payment_terms_table').DataTable().draw();
    scion.create.sc_modal('payment_terms_form').hide('all', modalHideFunction);
}

function error() {}

function delete_success() {
    $('#payment_terms_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    return {
        _token: _token,
        term_text: $('#term_text').val(),
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

