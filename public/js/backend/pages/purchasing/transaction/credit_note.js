$(function() {
    modal_content = 'credit_note';
    module_url = '/purchasing/credit_note';
    module_type = 'custom';
    page_title = "Credit Note";

    scion.centralized_button(true, true, true, true);
    scion.create.table(
        'credit_note_table',
        module_url + '/get',
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/purchasing/material_category/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                html += '<a href="#" class="align-middle" onclick="viewNote('+row.id+')" title="VIEW CREDIT NOTE"><i class="fas fa-hand-holding-usd"></i></a>';
                return html;
            }},
            {
                data: "project.project_code",
                title: "PROJECT",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "po.supplier.supplier_name",
                title: "SUPPLIER",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "chart.account_name",
                title: "CHART",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "status",
                title: "STATUS"
            },
            {
                data: "amount",
                title: "AMOUNT",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + scion.currency(data) + '</span>';
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
    $('#material_category_table').DataTable().draw();
    scion.create.sc_modal('benefits_form').hide('all', modalHideFunction)
}

function error() {}

function delete_success() {
    $('#material_category_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token,
        description: $('#description').val(),
    
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

function viewNote(id) {
    $.get(`/purchasing/credit_note/get/${id}`, function(response) {
        $('#cn_no').text(response.credit.id);
        $('#cn_status').text(response.credit.status);
        $('#cn_supplier').html(`<b style="font-weight:bold;">${response.credit.po.supplier.supplier_name}</b><br>${response.credit.po.supplier.address}`);
        $('#cn_proj').text(response.credit.project.project_code);
        $('#cn_account').text(response.credit.chart.account_name);
        $('#cn_particulars').text(response.credit.particulars !== null && response.credit.particulars !== ""?response.credit.particulars:'-');
        $('#cn_amount').text(scion.currency(response.credit.amount));

        scion.create.sc_modal("view_note_form", 'Credit Note').show(modalShowFunction);
    });
}