$(function() {
    modal_content = 'reimbursements';
    module_url = '/payroll/reimbursements';
    module_type = 'custom';
    page_title = "Reimbursement";

    scion.centralized_button(false, true, true, true);
    scion.create.table(
        'reimbursements_table',  
        module_url + '/get', 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/payroll/reimbursements/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
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
                data: "chart.account_name",
                title: "CHART OF ACCOUNT",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            { data: "status", title: "STATUS", render: function(data, type, row, meta) {
                var html = "";
                if(row.status === 1) {
                    html = '<span class="active">ACTIVE</span>';
                }
                else {
                    html = '<span class="active">INACTIVE</span>';
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
    $('#reimbursements_table').DataTable().draw();
    scion.create.sc_modal('reimbursements_form').hide('all', modalHideFunction);
}

function error() {}

function delete_success() {
    $('#reimbursements_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token,
        name: $('#name').val(),
        chart_id: $('#chart_id').val(),
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