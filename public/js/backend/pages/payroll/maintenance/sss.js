$(function() {
    modal_content = 'sss';
    module_url = '/payroll/sss';
    module_type = 'custom';
    page_title = "SSS";

    scion.centralized_button(true, true, true, true);
    
    scion.create.table(
        'sss_table',  
        module_url + '/get', 
        [
            // { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
            //     var html = "";
            //     html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
            //     html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/payroll/sss/edit/', "+ row.id + ' )"><i class="fas fa-pen"></i></a>';
            //     return html;
            // }},
            { data: "range_1", title: "RANGE 1", render: function(data, type, row, meta){
                return data.toFixed(2);
            }},
            { data: "range_2", title: "RANGE 2", render: function(data, type, row, meta){
                return data.toFixed(2);
            } },
            { data: "regular_ss", title: "REGULAR SS/ EC", render: function(data, type, row, meta){
                return data.toFixed(2);
            } },
            { data: "wisp", title: "WISP", render: function(data, type, row, meta){
                return data.toFixed(2);
            } },
            { data: "er", title: "ER", render: function(data, type, row, meta){
                return data.toFixed(2);
            } },
            { data: "ee", title: "EE", render: function(data, type, row, meta){
                return data.toFixed(2);
            } },
            { data: "ecc", title: "ECC", render: function(data, type, row, meta){
                return data.toFixed(2);
            } },
            { data: "wisp_er", title: "WISP ER", render: function(data, type, row, meta){
                return data.toFixed(2);
            } },
            { data: "wisp_ee", title: "WISP EE", render: function(data, type, row, meta){
                return data.toFixed(2);
            } },
        ], 'lfrtip', [], false, false, 500
    );

    $('.main').on('click', '#generateSSS', function() {
        var data = {
            _token: _token,
            row: $('#row').val(),
            range_1: $('#range_1').val(),
            range_2: $('#range_2').val(),
            range_interval: $('#range_interval').val(),
            monthly_minimum: $('#monthly_minimum').val(),
            monthly_interval: $('#monthly_interval').val(),
            regular_ss: $('#regular_ss').val(),
            regular_interval: $('#regular_interval').val(),
            ee: $('#ee').val(),
            er: $('#er').val(),
        }

        $.post(module_url + '/generate', data).done(function(response){
            $('#sss_table').DataTable().draw();
        });
    });

});

function success() {
    switch(actions) {
        case 'save':
            break;
        case 'update':
            break;
    }
    $('#sss_table').DataTable().draw();
    scion.create.sc_modal('sss_form').hide('all', modalHideFunction);
}

function error() {}

function delete_success() {
    $('#sss_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token,
        frequency: $('#frequency').val(),
        from: $('#from').val(),
        to: $('#to').val(),
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