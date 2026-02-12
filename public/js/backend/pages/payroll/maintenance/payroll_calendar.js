$(function() {
    modal_content = 'payroll_calendar';
    module_url = '/payroll/payroll_calendar';
    module_type = 'custom';
    page_title = "Payroll Calendar";

    scion.centralized_button(false, true, true, true);
    scion.create.table(
        'payroll_calendar_table',  
        module_url + '/get', 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/payroll/payroll_calendar/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            {
                data: "title",
                title: "TITLE",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            { title: "TYPE", render: function(data, type, row, meta) {
                var type = '';
                switch(row.type) {
                    case 1:
                        type = 'MONTHLY';
                        break;
                    case 2:
                        type = 'BI-MONTHLY';
                        break;
                    case 3:
                        type = 'BI-WEEKLY';
                        break;
                    case 4:
                        type = 'WEEKLY';
                        break;
                }
                return type;
            }},
            { title: "START DAY/ DATE", render: function(data, type, row, meta) {
                var type = '';
                if(row.type === 1 || row.type === 2) {
                    type = moment(row.start_date).format('MMM DD YYYY');
                }
                else {
                    type = row.start_day;
                }
                return type;
            }},
            { title: "END DAY/ DATE", render: function(data, type, row, meta) {
                var type = '';
                if(row.type === 1 || row.type === 2) {
                    type = moment(row.end_date).format('MMM DD YYYY');
                }
                else {
                    type = row.end_day;
                }
                return type;
            }},
            { title: "PAYMENT DAY/ DATE", render: function(data, type, row, meta) {
                var type = '';
                if(row.type === 1 || row.type === 2) {
                    type = moment(row.payment_date).format('MMM DD YYYY');
                }
                else {
                    type = row.payment_day;
                }
                return type;
            }}
        ], 'Bfrtip', []
    );

    $('.main').on('change', '#type', function() {
        if(this.value === '1' || this.value === '2') {
            $('#day_field').addClass('hide');
            $('#date_field').removeClass('hide');
        }
        else {
            $('#date_field').addClass('hide');
            $('#day_field').removeClass('hide');
        }
    });

});

function success() {
    switch(actions) {
        case 'save':
            break;
        case 'update':
            break;
    }
    $('#payroll_calendar_table').DataTable().draw();
    scion.create.sc_modal('payroll_calendar_form').hide('all', modalHideFunction);
}

function error() {}

function delete_success() {
    $('#payroll_calendar_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token,
        title: $('#title').val(),
        type: $('#type').val(),
        start_date: ($('#type').val() === '1' || $('#type').val() === '2')?$('#start_date').val():'',
        end_date: ($('#type').val() === '1' || $('#type').val() === '2')?$('#end_date').val():'',
        payment_date: ($('#type').val() === '1' || $('#type').val() === '2')?$('#payment_date').val():'',
        start_day: ($('#type').val() === '3' || $('#type').val() === '4')?$('#start_day').val():'',
        end_day: ($('#type').val() === '3' || $('#type').val() === '4')?$('#end_day').val():'',
        payment_day: ($('#type').val() === '3' || $('#type').val() === '4')?$('#payment_day').val():'',
        set_as_default: $('#set_as_default').val(),
        status: 0
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

