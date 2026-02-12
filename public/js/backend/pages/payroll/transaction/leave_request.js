$(function() {
    modal_content = 'leave_request';
    module_url = '/payroll/leave_request';
    module_type = 'custom';
    page_title = "Leave Request";

    scion.centralized_button(false, true, true, true);
    scion.create.table(
        'leave_request_table',  
        module_url + '/get', 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="editLeave('+row.id+')"><i class="fas fa-pen"></i></a>';
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
                data: "description",
                title: "DESCRIPTION",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "start_date",
                title: "START",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + moment(data).format('MMM DD, YYYY') + '</span>';
                }
            },
            {
                data: "end_date",
                title: "END",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + moment(data).format('MMM DD, YYYY') + '</span>';
                }
            },
            {
                data: "current_leave_balance",
                title: "CURRENT LEAVE BALANCE",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "pay_period",
                title: "PAY PERIOD",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "total_leave_hours",
                title: "TOTAL LEAVE DAYS",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "status",
                title: "STATUS",
                render: function(data, type, row, meta) {
                    var btn = '';
                    
                    if(data === 0) {
                        btn = `<button class="btn btn-sm btn-success" onclick="approveLeave(${row.id}, 1)"><i class="fas fa-check"></i></button> <button class="btn btn-sm btn-danger" onclick="approveLeave(${row.id}, 2)"><i class="fas fa-times"></i></button>`;
                    }
                    else {
                        btn = `<span class="status-${data}">${data===1?"APPROVED":"DECLINE"}</span>`;
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
    $('#leave_request_table').DataTable().draw();
    scion.create.sc_modal('leave_request_form').hide('all', modalHideFunction)
}

function error() {
    toastr.error('Record already exist.', 'Failed')
}

function delete_success() {
    $('#leave_request_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    switch(actions) {
        case 'save':
            form_data = {
                _token: _token,
                employee_id: store_record.employee.id,
                leave_type_id: $('#leave_type_id').val(),
                description: $('#description').val(),
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val()
            };
            break;
        case 'update':
            form_data = {
                _token: _token,
                employee_id: store_record.leave.employee_id,
                leave_type_id: $('#leave_type_id').val(),
                description: $('#description').val(),
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val()
            };
            break;
    }

    return form_data;
}

function generateDeleteItems(){}


function modalShowFunction() {
    scion.centralized_button(true, false, true, true);
}

function editLeave(id) {
    scion.record.edit('/payroll/leave_request/edit/',id);

    setTimeout(() => {
        $.post('/payroll/leave_request/get-leave', {_token:_token, employee_id: store_record.leave.employee_id}).done((response)=>{
            var leave = "";
            leave += "<option value=''></option>"
            $.each(response.leaves, (i,v)=>{
                leave += "<option value='"+v.leave_types.id+"' "+(v.leave_types.id === id? "selected":"")+">"+v.leave_types.leave_name+"</option>"
            });

            $('#leave_type_id').html(leave);

        });
    }, 1000);
}

function modalHideFunction() {
    scion.centralized_button(false, true, true, true);
}

function lookupReturn() {
    $('#employee_name').val(`${store_record.employee.firstname + " " + store_record.employee.lastname}`);
    record_id = null;
    actions = 'save';

    $.post('/payroll/leave_request/get-leave', {_token:_token, employee_id: store_record.employee.id}).done((response)=>{
        var leave = "";
        leave += "<option value=''></option>"
        $.each(response.leaves, (i,v)=>{
            leave += "<option value='"+v.leave_types.id+"'>"+v.leave_types.leave_name+"</option>"
        });

        $('#leave_type_id').html(leave);
    });

    scion.centralized_button(true, false, true, true);
}

function editShow() {
    $('#employee_name').val(`${store_record.leave.employee.firstname + " " + store_record.leave.employee.lastname}`);
}

function approveLeave(id, status) {
    $.post(`${module_url}/approve`, {_token:_token, id: id, status:status }, (response)=>{
        $('#leave_request_table').DataTable().draw();
    });
}