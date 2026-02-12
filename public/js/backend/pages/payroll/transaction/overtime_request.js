$(function() {
    modal_content = 'overtime_request';
    module_url = '/payroll/overtime_request';
    module_type = 'custom';
    page_title = "Overtime Request";

    scion.centralized_button(false, true, true, true);
    scion.create.table(
        'overtime_request_table',  
        module_url + '/get', 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/payroll/overtime_request/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            {
                data: null,
                title: "NAME",
                render: function(data, type, row, meta) {
                    return `<span class="expandable" title="${row.employee.firstname} ${row.employee.lastname}">${row.employee.firstname} ${row.employee.lastname}</span>`;
                }
            },
            {
                data: "reason",
                title: "REASON",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + (data !== null?data:"") + '">' + (data !== null?data:"-") + '</span>';
                }
            },
            {
                data: "start_time",
                title: "START",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + moment(data).format('MMM DD, YYYY - h:mm A') + '</span>';
                }
            },
            {
                data: "end_time",
                title: "END",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + moment(data).format('MMM DD, YYYY - h:mm A') + '</span>';
                }
            },
            {
                data: "total_hours",
                title: "TOTAL HOURS",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "ot_type",
                title: "TYPE",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + (data === "OTR"?"Overtime Rest Day":"Overtime") + '">' + (data === "OTR"?"Overtime Rest Day":"Overtime") + '</span>';
                }
            },
            {
                data: "status",
                title: "STATUS",
                render: function(data, type, row, meta) {
                    var btn = '';
                    
                    if(data === "pending") {
                        btn = `<button class="btn btn-sm btn-success" onclick="approveOvertime(${row.id}, 'approved')"><i class="fas fa-check"></i></button> <button class="btn btn-sm btn-danger" onclick="approveOvertime(${row.id}, 'declined')"><i class="fas fa-times"></i></button>`;
                    }
                    else {
                        btn = `<span class="status-${data}">${data==='approved'?"APPROVED":"DECLINED"}</span>`;
                    }

                    return btn;
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
    $('#overtime_request_table').DataTable().draw();
    scion.create.sc_modal('overtime_request_form').hide('all', modalHideFunction)
}

function error() {
    toastr.error('Record already exist.', 'Failed')
}

function delete_success() {
    $('#overtime_request_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    switch(actions) {
        case 'save':
            form_data = {
                _token: _token,
                employee_id: store_record.employee.id,
                ot_date: $('#ot_date').val(),
                reason: $('#reason').val(),
                start_time: $('#start_time').val(),
                end_time: $('#end_time').val(),
                status: "pending"
            };
            break;
        case 'update':
            form_data = {
                _token: _token,
                employee_id: store_record.overtime.employee.id,
                ot_date: $('#ot_date').val(),
                reason: $('#reason').val(),
                start_time: $('#start_time').val(),
                end_time: $('#end_time').val()
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

function lookupReturn() {
    $('#employee_name').val(`${store_record.employee.firstname + " " + store_record.employee.lastname}`);
    record_id = null;
    actions = 'save';
    scion.centralized_button(true, false, true, true);
}

function editShow() {
    $('#employee_name').val(`${store_record.overtime.employee.firstname + " " + store_record.overtime.employee.lastname}`);
}

function approveOvertime(id, status) {
    $.post(`${module_url}/approve`, {_token:_token, id: id, status:status }, (response)=>{
        $('#overtime_request_table').DataTable().draw();
    });
}

function generateRecord() {

    if ($.fn.DataTable.isDataTable('#overtime_request_table')) {
        $('#overtime_request_table').DataTable().destroy();
    }

    scion.create.table(
        'overtime_request_table',  
        module_url + '/get_filter/' + $('#start_date').val() + '/' + $('#end_date').val(), 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/payroll/overtime_request/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            {
                data: null,
                title: "NAME",
                render: function(data, type, row, meta) {
                    return `<span class="expandable" title="${row.employee.firstname} ${row.employee.lastname}">${row.employee.firstname} ${row.employee.lastname}</span>`;
                }
            },
            {
                data: "reason",
                title: "REASON",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + (data !== null?data:"") + '">' + (data !== null?data:"-") + '</span>';
                }
            },
            {
                data: "start_time",
                title: "START",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + moment(data).format('MMM DD, YYYY - h:mm A') + '</span>';
                }
            },
            {
                data: "end_time",
                title: "END",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + moment(data).format('MMM DD, YYYY - h:mm A') + '</span>';
                }
            },
            {
                data: "total_hours",
                title: "TOTAL HOURS",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "ot_type",
                title: "TYPE",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + (data === "OTR"?"Overtime Rest Day":"Overtime") + '">' + (data === "OTR"?"Overtime Rest Day":"Overtime") + '</span>';
                }
            },
            {
                data: "status",
                title: "STATUS",
                render: function(data, type, row, meta) {
                    var btn = '';
                    
                    if(data === "pending") {
                        btn = `<button class="btn btn-sm btn-success" onclick="approveOvertime(${row.id}, 'approved')"><i class="fas fa-check"></i></button> <button class="btn btn-sm btn-danger" onclick="approveOvertime(${row.id}, 'declined')"><i class="fas fa-times"></i></button>`;
                    }
                    else {
                        btn = `<span class="status-${data}">${data==='approved'?"APPROVED":"DECLINED"}</span>`;
                    }

                    return btn;
                }
            },
        ], 'Bfrtip', []
    );

}