$(function() {
    modal_content = 'holiday_type';
    module_url = '/payroll/holiday_type';
    module_type = 'custom';
    page_title = 'Holiday Type';

    scion.centralized_button(false, true, true, true);
    
    scion.create.table(
        'holiday_type_table',  
        module_url + '/get', 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/payroll/holiday_type/edit/', "+ row.id + ' )"><i class="fas fa-pen"></i></a>';
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
                data: "multiplier",
                title: "MULTIPLIER",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
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
    $('#holiday_type_table').DataTable().draw();
    scion.create.sc_modal('holiday_type_form').hide('all', modalHideFunction);
}

function error() {
    toastr.error('Record already exist.', 'Failed')
}

function delete_success() {
    $('#holiday_type_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token
    };
    $.each($('#holiday_typeForm').serializeArray(), (i,v)=> {
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