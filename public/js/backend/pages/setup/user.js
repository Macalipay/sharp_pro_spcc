var model_id = null;
$(function() {
    modal_content = 'users';
    module_url = '/settings/users';
    module_type = 'custom';
    page_title = "Users";

    scion.centralized_button(false, true, true, true);
    
    scion.create.table(
        modal_content + '_table',  
        module_url + '/get', 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'"+module_url+"/edit/', "+ row.id + ' )"><i class="fas fa-pen"></i></a>';
                html += '<a href="#" class="align-middle edit" onclick="generateAccess('+row.id+')"><i class="fas fa-key"></i></a>';
                return html;
            }},
            { data: null, title: "Name", render: function(data, type, row, meta) {
                return row.firstname + ' ' + (row.middlename !== '' && row.middlename !== null?row.middlename + ' ':'') + row.lastname + (row.suffix !== '' && row.suffix !== null?' ' + row.suffix:'');
            }},
            { data: 'email', title: 'Email'}
        ], 'Bfrtip', []
    );

    $('#generate_key_close').click(function(){
        modal_content = 'users';
    });
});

function success() {
    switch(actions) {
        case 'save':
            break;
        case 'update':
            break;
    }
    $('#'+modal_content+'_table').DataTable().draw();
    scion.create.sc_modal(modal_content+'_form').hide('all', modalHideFunction);
}

function error() {
    toastr.error('Record already exist.', 'Failed')
}

function delete_success() {
    $('#'+modal_content+'_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    if (modal_content === 'users') {
        form_data = {
            _token: _token,
            firstname: $('#firstname').val(),
            middlename: $('#middlename').val(),
            lastname: $('#lastname').val(),
            suffix: $('#suffix').val(),
            email: $('#email').val(),
            status: $('#status').val()
        };
    } else {

        form_data = {
            _token: _token,
            role_id: $('#role').val(),
            model_id: model_id,
        };
        modal_content = 'users';
    }

    return form_data;
}

function generateDeleteItems(){}

function modalShowFunction() {
    scion.centralized_button(true, false, true, true);
}

function modalHideFunction() {
    scion.centralized_button(false, true, true, true);
}

function customFunc() {
    modal_content = 'users';
    module_url = '/settings/users';
    module_type = 'custom';
    page_title = "Users";

    scion.centralized_button(false, true, true, true);
}

function generateAccess(id) {
    modal_content = 'generate_key';
    module_url = '/settings/access';
    module_type = 'custom';
    page_title = "Generate Access";
    model_id = id;

    scion.centralized_button(false, true, true, true);
    scion.create.sc_modal(modal_content+'_form').show(modalShowFunction);

    $("input[name='po_tabs[]']").prop('checked', false);

    $.get(module_url + '/get/' + id, function(response) {
        if(response.record !== null) {
            record_id = id;
            actions = 'update';

            $('#role').val(response.record.role_id);if (response.record.po_tabs) {
                let assignedTabs = response.record.po_tabs; // Assume this is an array of assigned values
                $("input[name='po_tabs[]']").each(function() {
                    if (assignedTabs.includes(parseInt($(this).val()))) {
                        $(this).prop('checked', true);
                    }
                });
            }

        }
        else {
            record_id = null;
            actions = 'save';
            
        }
    });

}
