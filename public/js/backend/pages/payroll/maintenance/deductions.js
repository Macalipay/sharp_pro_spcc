$(function() {
    modal_content = 'deductions';
    module_url = '/payroll/deductions';
    module_type = 'custom';
    page_title = "Deductions";

    scion.centralized_button(false, true, true, true);
    scion.create.table(
        'deductions_table',  
        module_url + '/get', 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/payroll/deductions/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            {
                data: "code",
                title: "CODE",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "name",
                title: "NAME",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "multiplier",
                title: "MULTIPLIER",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "chart.account_name",
                title: "CHART OF ACCOUNT",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            { data: "type", title: "TYPE", render: function(data, type, row, meta) {
                var html = "";
                if(row.type === "deductions") {
                    html = 'DEDUCTION';
                }
                else {
                    html = 'NON-DEDUCTION';
                }
                return html;
            }},
            { data: "taxable", title: "TAXABLE", render: function(data, type, row, meta) {
                var html = "";
                if(row.taxable === 1) {
                    html = 'TAX SENSITIVE';
                }
                else {
                    html = 'NON TAX SENSITIVE';
                }
                return html;
            }},
            { data: "status", title: "STATUS", render: function(data, type, row, meta) {
                var html = "";
                if(row.status === 1) {
                    html = '<i class="fas fa-check text-success"></i>';
                }
                else {
                    html = '<i class="fas fa-times text-danger"></i>';
                }
                return html;
            }}
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
    $('#deductions_table').DataTable().draw();
    scion.create.sc_modal('deductions_form').hide('all', modalHideFunction)
}

function error() {
    toastr.error('Record already exist.', 'Failed')
}

function delete_success() {
    $('#deductions_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token,
        name: $('#name').val(),
        code: $('#code').val(),
        chart_id: $('#chart_id').val(),
        description: $('#description').val(),
        multiplier: $('#multiplier').val(),
        taxable: $('#taxable').val(),
        type: $('#type').val(),
        status: $('#status').val()
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