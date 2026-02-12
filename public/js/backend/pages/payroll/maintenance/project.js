var region = null;
var province = null;
var city = null;
var barangay = null;

$(function() {
    modal_content = 'project';
    module_url = '/purchasing/project';
    module_type = 'custom';
    page_title = "Project";

    scion.centralized_button(false, true, true, true);
    scion.create.table(
        'project_table',  
        module_url + '/get', 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/purchasing/project/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                html += '<a href="#" onclick="showEmployee('+ row.id +')"><i class="fas fa-hashtag"></i></a>';
                return html;
            }},
            {
                data: "project_name",
                title: "PROJECT NAME",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "project_code",
                title: "PROJECT CODE",
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
    $('#project_table').DataTable().draw();
    scion.create.sc_modal('project_form').hide('all', modalHideFunction);
}

function error() {
    toastr.error('Record already exist.', 'Failed')
}

function delete_success() {
    $('#project_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token,
        project_name: $('#project_name').val(),
        project_code: $('#project_code').val(),
        region_id: $('#region_id').val(),
        province_id: $('#province_id').val(),
        city_id: $('#city_id').val(),
        barangay_id: $('#barangay_id').val(),
        postal_code: $('#postal_code').val(),
        address: $('#address').val(),
        project_owner: $('#project_owner').val(),
        start_date: $('#start_date').val(),
        completion_date: $('#completion_date').val(),
        project_completion: $('#project_completion').val(),
        project_architect: $('#project_architect').val(),
        project_consultant: $('#project_consultant').val(),
        project_in_charge: $('#project_in_charge').val(),
        contract_price: $('#contract_price').val(),
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

function selectRegion() {
    $.get('/address/province/'+$('#region_id').val(), function(response) {
        var html = '';
        html += '<option value=""></option>';
        response.province.forEach(province => {
            html += `<option value="${province.province_id}">${province.name}</option>`;
        });

        $('#province_id').html(html);

        if(record_id !== null) {
            $('#province_id').val(store_record.project.province_id);
            selectProvince();
        }
    });
}

function selectProvince() {
    $.get('/address/city/'+$('#province_id').val(), function(response) {
        var html = '';
        html += '<option val=""></option>';
        response.city.forEach(city => {
            html += `<option value="${city.city_id}">${city.name}</option>`;
        });

        $('#city_id').html(html);
        
        if(record_id !== null) {
            $('#city_id').val(store_record.project.city_id);
            selectCity();
        }
        console.log('ss');
    });
}

function selectCity() {
    $.get('/address/barangay/'+$('#city_id').val(), function(response) {
        var html = '';
        html += '<option val=""></option>';
        response.barangay.forEach(barangay => {
            html += `<option value="${barangay.id}">${barangay.name}</option>`;
        });

        $('#barangay_id').html(html);

        if(record_id !== null) {
            $('#barangay_id').val(store_record.project.barangay_id);
        }
    });
}

function showEmployee(id) {
    record_id = id;
    $.post('/purchasing/project/get-employee-tag', { _token: _token, project_id: id }).done((response)=>{
        $.each(response.project, (i,v)=>{
            $(`#employee_${v.employee_id} .employee-item`).addClass('selected');
        });
        scion.create.sc_modal("employee_modal", "Employee").show();
    });
}

function tagEmployee(id) {
    var data = {
        _token: _token,
        employee_id: id, 
        project_id: record_id,
        action: $(`#employee_${id} .employee-item`).hasClass('selected')?'remove':'add'
    };

    $.post('/purchasing/project/tag', data).done((response)=>{
        if($(`#employee_${id} .employee-item`).hasClass('selected')) {
            $(`#employee_${id} .employee-item`).removeClass('selected');
        }
        else {
            $(`#employee_${id} .employee-item`).addClass('selected');
        }
    });
}