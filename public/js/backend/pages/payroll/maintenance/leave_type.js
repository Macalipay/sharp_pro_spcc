$(function() {
    modal_content = 'leave-types';
    module_url = '/payroll/leave-type';
    module_type = 'custom';
    page_title = 'Leave Type';

    scion.centralized_button(false, true, true, true);
    
    scion.create.table(
        'leave_types_table',  
        module_url + '/get', 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/payroll/leave-type/edit/', "+ row.id + ' )"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            { data: "DT_RowIndex", title:"#" },
            {
                data: "leave_name",
                title: "LEAVE NAME",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "units",
                title: "UNITS",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "normal_entitlement",
                title: "NORMAL ENTITLEMENT",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "type_of_unit",
                title: "TYPE OF UNIT",
            },
            {
                data: "chart.account_name",
                title: "CHART OF ACCOUNT",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "paid_leave",
                title: "PAID LEAVE",
                render: function(data, type, row, meta) {
                    var checkboxHtml = data ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>';
                    return '<span class="expandable" title="' + data + '">' + checkboxHtml + '</span>';
                }
            },
            {
                data: "show_on_payslip",
                title: "SHOW ON PAYSLIP",
                render: function(data, type, row, meta) {
                    var checkboxHtml = data ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>';
                    return '<span class="expandable" title="' + data + '">' + checkboxHtml + '</span>';
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
    $('#leave_types_table').DataTable().draw();
    scion.create.sc_modal('leave-types_form').hide('all', modalHideFunction);
}

function error() {
    toastr.error('Record already exist.', 'Failed')
}

function delete_success() {
    $('#leave_types_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token
    };
    $.each($('#leaveTypeForm').serializeArray(), (i,v)=> {
        form_data[v.name] = v.value
    });

    return form_data;
}

function generateDeleteItems(){}

function modalShowFunction() {
    scion.centralized_button(true, false, true, true);
}

function modalHideFunction() {
    scion.centralized_button(false, true, true, true);
}