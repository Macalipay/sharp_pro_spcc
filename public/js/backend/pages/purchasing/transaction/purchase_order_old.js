$(function() {
    modal_content = 'purchase_orders';
    module_url = '/purchasing/purchase_orders';
    module_type = 'custom';
    page_title = "Sites";

    scion.centralized_button(false, true, true, true);
    scion.create.table(
        'purchase_orders_table',
        module_url + '/get',
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/purchasing/purchase_orders/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            { data: "DT_RowIndex", title:"#" },
            { data: "supplier.supplier_name", title: "Supplier" },
            { data: "delivery_date", title: "Delivery Date" },
            { data: "site.project_name", title: "Ship To" },
            { data: "site.location", title: "Address" },
            { data: "po_date", title: "PO Date" },
            { data: "contact_no", title: "Contact No" },
            { data: "reference", title: "Reference No" },
            { data: "terms", title: "Terms" },
            { data: "due_date", title: "Due Date" },
            { data: "order_no", title: "Order No." },
            { data: "tax_type", title: "Tax Type" },
            { data: "subtotal", title: "Sub Total" },
            { data: "total_with_tax", title: "Total with Tax" },
            { data: "delivery_instruction", title: "Delivery Instruction" },
            // }},
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
    $('#purchase_orders_table').DataTable().draw();
    scion.create.sc_modal('benefits_form').hide('all', modalHideFunction)
}

function error() {}

function delete_success() {
    $('#purchase_orders_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token,
        supplier_id: $('#supplier_id').val(),
        delivery_date: $('#delivery_date').val(),
        site_id: $('#site_id').val(),
        po_date: $('#po_date').val(),
        contact_no: $('#contact_no').val(),
        reference: $('#reference').val(),
        terms: $('#terms').val(),
        due_date: $('#due_date').val(),
        order_no: $('#order_no').val(),
        tax_type: $('#tax_type').val(),
        subtotal: $('#subtotal').val(),
        total_with_tax: $('#total_with_tax').val(),
        delivery_instruction: $('#delivery_instruction').val(),
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
