$(function() {
    modal_content = 'adjustments';
    module_url = 'employee_adjustment';
    module_type = 'custom';
    page_title = "Employee Adjustment";

    scion.centralized_button(false, true, true, true);

    scion.create.table(
        'employee_adjustment_table',
        module_url + '/get',
        [
            { data: "id", title: "ID" },

            {
                data: "employee.employee_no",
                title: "EMPLOYEE NO.",
                render: function (data, type, row) {
                    return '<span class="expandable" title="' + row.employee.employee_information.employee_no + '">' + row.employee.employee_information.employee_no + '</span>';
                }
            },

            {
                data: null,
                title: "EMPLOYEE NAME",
                render: function (data, type, row) {
                    let fullName = row.employee.employee_information.firstname + " ";
                    if (row.employee.employee_information.middlename) fullName += row.employee.employee_information.middlename + " ";
                    fullName += row.employee.employee_information.lastname;
                    if (row.employee.employee_information.suffix) fullName += " " + row.employee.employee_information.suffix;
                    return '<span class="expandable" title="' + fullName.trim() + '">' + fullName.trim() + '</span>';
                }
            },

            {
                data: "adjustment_type",
                title: "TYPE",
                render: function (data) {
                    return '<span class="badge badge-info text-uppercase">' + data + '</span>';
                }
            },

            {
                data: "old_value",
                title: "OLD VALUE",
                render: function (data) {
                    if (data === null || data === '') return 'N/A';
                    var formatted = formatPhpCurrency(data);
                    return '<span class="expandable" title="' + formatted + '">' + formatted + '</span>';
                }
            },

            {
                data: "new_value",
                title: "NEW VALUE",
                render: function (data) {
                    if (data === null || data === '') return 'N/A';
                    var formatted = formatPhpCurrency(data);
                    return '<span class="expandable" title="' + formatted + '">' + formatted + '</span>';
                }
            },

            {
                data: "amount",
                title: "AMOUNT",
                render: function (data) {
                    return data ? '<span class="expandable" title="' + data + '">' + data + '</span>' : 'N/A';
                }
            },

            {
                data: "effective_date",
                title: "EFFECTIVE DATE",
                render: function (data, type, row) {
                    return '<span class="expandable" title="' +
                        moment(row.effective_date).format('MMM DD, YYYY') +
                        '">' + moment(row.effective_date).format('MMM DD, YYYY') + '</span>';
                }
            },

            {
                data: "remarks",
                title: "REMARKS",
                render: function (data) {
                    return data ? '<span class="expandable" title="' + data + '">' + data + '</span>' : 'N/A';
                }
            },

            {
                data: "status",
                title: "STATUS",
                render: function (data) {
                    let badge = 'secondary';
                    if (data === 'APPROVED') badge = 'success';
                    if (data === 'PENDING') badge = 'warning';
                    if (data === 'REJECTED') badge = 'danger';

                    return '<span class="badge badge-' + badge + ' text-uppercase">' + data + '</span>';
                }
            },

            {
                data: "adjusted_by_user.name",
                title: "ADJUSTED BY",
                render: function (data, type, row) {
                    return '<span class="expandable" title="' + row.adjusted_by.firstname + ' ' + row.adjusted_by.lastname + '">' + row.adjusted_by.firstname + ' ' + row.adjusted_by.lastname + '</span>';
                }
            },

            {
                data: "created_at",
                title: "CREATED DATE",
                render: function (data, type, row) {
                    return '<span class="expandable" title="' +
                        moment(row.created_at).format('MMM DD, YYYY hh:mm A') +
                        '">' + moment(row.created_at).format('MMM DD, YYYY hh:mm A') + '</span>';
                }
            }
        ],
        'Bfrtip',
        '',
        true,
        false
    );
});


function success() {
    switch(actions) {
        case 'save':
            break;
        case 'update':
            break;
    }
    $('#employee_adjustment_table').DataTable().draw();
    scion.create.sc_modal('departments_form').hide('all', modalHideFunction);
}

function error() {
    toastr.error('Record already exist.', 'Failed')
}

function delete_success() {
    $('#employee_adjustment_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    var form_data = {
        _token: _token,
        employee_id: $('#employee_id').val(),
        adjustment_type: $('#adjustment_type').val(),
        old_value: $('#old_value').val(),
        new_value: $('#new_value').val(),
        amount: $('#amount').val(),
        effective_date: $('#effective_date').val(),
        remarks: $('#remarks').val(),
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

function formatPhpCurrency(value) {
    var numericValue = Number(value);

    if (!isNaN(numericValue)) {
        return new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(numericValue);
    }

    return value;
}
