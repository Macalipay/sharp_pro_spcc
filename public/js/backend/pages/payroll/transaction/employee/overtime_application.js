$(function() {
    modal_content = 'earnings';
    module_url = '/payroll/earnings';
    module_type = 'custom';
    page_title = "Overtime Application";

    scion.centralized_button(false, true, true, true);
    scion.create.table(
        'earnings_table',  
        module_url + '/get', 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/payroll/earnings/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            { data: "payroll_period", title: "PAYROLL PERIOD" },
            { data: "date", title: "DATE" },
            { data: "hours", title: "EXTRA HOURS" },
            { data: "minutes", title: "EXTRA MINUTES" },
            { data: "reason", title: "REASON" },
            { data: "status", title: "STATUS" },

        ]
    );

});

function success() {
    switch(actions) {
        case 'save':
            break;
        case 'update':
            break;
    }
    $('#earnings_table').DataTable().draw();
    scion.create.sc_modal('earnings_form').hide('all', modalHideFunction)
}

function error() {}

function delete_success() {
    $('#earnings_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token,
        name: $('#name').val(),
        code: $('#code').val(),
        description: $('#description').val(),
        multiplier: $('#multiplier').val(),
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