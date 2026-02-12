$(function() {
    modal_content = 'withholding_tax';
    module_url = '/payroll/withholding_tax';
    module_type = 'custom';
    page_title = "Withholding Tax";

    scion.centralized_button(false, true, true, true);
    
    scion.create.table(
        'withholding_tax_table',  
        module_url + '/get', 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/payroll/withholding_tax/edit/', "+ row.id + ' )"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            { data:"frequency", title:"PAYROLL FREQUENCY", render: function(data, type, row, meta) {
                switch(row.frequency) {
                    case 1:
                        return "MONTHLY";
                        break;
                    case 2:
                        return "SEMI-MONTHLY";
                        break;
                    case 3:
                        return "WEEKLY";
                        break;
                    case 4:
                        return "DAILY";
                        break;
                }
            } },
            { data: "range_from", title: "FROM" },
            { data: "range_to", title: "TO" },
            { data: "fix_tax", title: "FIX TAX" },
            { data: "rate_on_excess", title: "RATE ON EXCESS" }
        ], 'lfrtip', [], false, false, 500
    );

});

function success() {
    switch(actions) {
        case 'save':
            break;
        case 'update':
            break;
    }
    $('#withholding_tax_table').DataTable().draw();
    scion.create.sc_modal('withholding_tax_form').hide('all', modalHideFunction);
}

function error() {
    toastr.error('Record already exist.','Failed');

}

function delete_success() {
    $('#withholding_tax_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token,
        frequency: $('#frequency').val(),
        range_from: $('#range_from').val(),
        range_to: $('#range_to').val(),
        fix_tax: $('#fix_tax').val(),
        rate_on_excess: $('#rate_on_excess').val()
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