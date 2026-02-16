$(function() {
    modal_content = 'schedule_request';
    module_url = '/payroll/schedule_request';
    module_type = 'custom';
    page_title = 'Schedule Request';

    scion.centralized_button(false, true, true, true);

    scion.create.table(
        'schedule_request_table',
        module_url + '/get',
        [
            { data: 'id', title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row) {
                var html = '';
                html += '<input type="checkbox" class="single-checkbox" value="' + row.id + '" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit(\'/payroll/schedule_request/edit/\', ' + row.id + ')"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            { data: 'request_no', title: 'REQUEST NO.' },
            { data: 'employee', title: 'EMPLOYEE', render: function(data, type, row) {
                if (!row.employee) return '-';
                var fullName = ((row.employee.firstname || '') + ' ' + (row.employee.lastname || '')).trim();
                return fullName !== '' ? fullName : (row.employee.employee_no || '-');
            }},
            { data: 'request_date', title: 'REQUEST DATE', render: function(data) { return data ? moment(data).format('MMM DD, YYYY') : '-'; } },
            { data: 'schedule_type', title: 'SCHEDULE TYPE', render: function(data) { return (data || '').toUpperCase(); } },
            { data: null, title: 'PERIOD COVERED', render: function(data, type, row) {
                var start = row.period_start ? moment(row.period_start).format('MMM DD, YYYY') : '-';
                var end = row.period_end ? moment(row.period_end).format('MMM DD, YYYY') : '-';
                return start + ' - ' + end;
            }},
            { data: null, title: 'TIME', render: function(data, type, row) {
                return (row.start_time || '-') + ' - ' + (row.end_time || '-');
            }},
            { data: 'reason', title: 'REASON', render: function(data) { return data || '-'; } },
            { data: 'status', title: 'STATUS', render: function(data, type, row) {
                if (data === 'pending') {
                    return '<button class="btn btn-sm btn-success mr-1" onclick="updateScheduleRequestStatus(' + row.id + ', \'approved\')"><i class="fas fa-check"></i></button>' +
                           '<button class="btn btn-sm btn-danger" onclick="updateScheduleRequestStatus(' + row.id + ', \'declined\')"><i class="fas fa-times"></i></button>';
                }

                if (data === 'approved') {
                    return '<span class="sr-status-approved">APPROVED</span>';
                }

                if (data === 'declined') {
                    return '<span class="sr-status-declined">DECLINED</span>';
                }

                return '<span class="sr-status-pending">PENDING</span>';
            }},
            { data: 'requester', title: 'REQUESTED BY', render: function(data, type, row) {
                if (!row.requester) return '-';
                return ((row.requester.firstname || '') + ' ' + (row.requester.lastname || '')).trim();
            }},
            { data: 'approver', title: 'APPROVED BY', render: function(data, type, row) {
                if (!row.approver) return '-';
                return ((row.approver.firstname || '') + ' ' + (row.approver.lastname || '')).trim();
            }}
        ],
        'Bfrtip',
        []
    );

    $('#start_time').on('change', function() {
        autoSetEndTime();
    });
});

function success() {
    $('#schedule_request_table').DataTable().draw();
    scion.create.sc_modal('schedule_request_form').hide('all', modalHideFunction);
}

function error() {
    toastr.error('Please check required fields.', 'Failed');
}

function delete_success() {
    $('#schedule_request_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token,
        employee_id: (store_record && store_record.employee && store_record.employee.id) ? store_record.employee.id : (store_record && store_record.schedule_request && store_record.schedule_request.employee_id ? store_record.schedule_request.employee_id : null),
        request_date: $('#request_date').val(),
        schedule_type: $('#schedule_type').val(),
        period_start: $('#period_start').val(),
        period_end: $('#period_end').val(),
        start_time: $('#start_time').val(),
        end_time: $('#end_time').val(),
        reason: $('#reason').val(),
    };

    return form_data;
}

function generateDeleteItems() {}

function modalShowFunction() {
    scion.centralized_button(true, false, true, true);
    autoSetEndTime();
}

function modalHideFunction() {
    scion.centralized_button(false, true, true, true);
}

function editShow() {
    if (!store_record || !store_record.schedule_request) {
        return;
    }

    if (store_record.schedule_request.employee) {
        $('#employee_name').val(
            ((store_record.schedule_request.employee.firstname || '') + ' ' + (store_record.schedule_request.employee.lastname || '')).trim()
        );
    }

    $('#request_date').val(store_record.schedule_request.request_date || '');
    $('#schedule_type').val(store_record.schedule_request.schedule_type || '');
    $('#period_start').val(store_record.schedule_request.period_start || '');
    $('#period_end').val(store_record.schedule_request.period_end || '');
    $('#start_time').val(store_record.schedule_request.start_time || '');
    $('#end_time').val(store_record.schedule_request.end_time || '');
    $('#reason').val(store_record.schedule_request.reason || '');
}

function autoSetEndTime() {
    var start = $('#start_time').val();
    if (!start || start.indexOf(':') === -1) {
        return;
    }

    var parts = start.split(':');
    var hour = parseInt(parts[0], 10);
    var minute = parseInt(parts[1], 10);

    if (isNaN(hour) || isNaN(minute)) {
        return;
    }

    hour = (hour + 9) % 24;
    var hh = String(hour).padStart(2, '0');
    var mm = String(minute).padStart(2, '0');
    $('#end_time').val(hh + ':' + mm);
}

function lookupReturn() {
    if (store_record && store_record.employee) {
        $('#employee_name').val(`${store_record.employee.firstname || ''} ${store_record.employee.lastname || ''}`.trim());
    }
    record_id = null;
    actions = 'save';
    scion.centralized_button(true, false, true, true);
}

function updateScheduleRequestStatus(id, status) {
    $.post(module_url + '/approve', {
        _token: _token,
        id: id,
        status: status
    }).done(function() {
        $('#schedule_request_table').DataTable().draw();
    }).fail(function() {
        toastr.error('Unable to update status.', 'Failed');
    });
}
