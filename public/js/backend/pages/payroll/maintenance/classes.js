$(function() {
    modal_content = 'classes';
    module_url = '/payroll/classes';
    module_type = 'custom';
    page_title = "Classes";

    scion.centralized_button(false, true, true, true);
    scion.create.table(
        'classes_table',  
        module_url + '/get', 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/payroll/classes/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            {
                data: "description",
                title: "Description",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "payment_schedule",
                title: "Payment Schedule",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            { data: "tax_applicable", title: "Tax Applicable", render: function(data, type, row, meta) {
                var html = "";
                if(row.tax_applicable === "1") {
                    html = '<i class="fas fa-check text-success"></i>';
                }
                else {
                    html = '<i class="fas fa-times text-danger"></i>';
                }
                return html;
            }},
            { data: "government_mandated_benefits", title: "Government Mandated Benefits", render: function(data, type, row, meta) {
                var html = "";
                if(row.government_mandated_benefits === "1") {
                    html = '<i class="fas fa-check text-success"></i>';
                }
                else {
                    html = '<i class="fas fa-times text-danger"></i>';
                }
                return html;
            }},
            { data: "other_company_benefits", title: "Other Company Benefits", render: function(data, type, row, meta) {
                var html = "";
                if(row.other_company_benefits === "1") {
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
    $('#classes_table').DataTable().draw();
    scion.create.sc_modal('benefits_form').hide('all', modalHideFunction)
}

function error() {
    toastr.error('Record already exist.', 'Failed')
}
function delete_success() {
    $('#classes_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token,
        description: $('#description').val(),
        payment_schedule: $('#payment_schedule').val(),
        tax_applicable: $('#tax_applicable').val(),
        government_mandated_benefits: $('#government_mandated_benefits').val(),
        other_company_benefits: $('#other_company_benefits').val()
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