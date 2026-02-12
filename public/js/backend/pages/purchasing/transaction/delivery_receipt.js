var selected_frame = 1;

$(function() {
    module_content = 'delivery_receipt';
    module_url = '/purchasing/delivery_receipt';
    tab_active = 'preparation';
    page_title = "";
    actions = 'save';
    module_type = 'transaction';
    modal_content = 'preparation';
    po_id = '';

    scion.centralized_button(false, true, false, false);
    scion.action.tab(tab_active);

    preparation_func();
    // $('.status-container button.status-DRAFT').addClass('s-selected');

    let dfst = 'SENT_TO_SUPPLIER';
    let $defaultButton = $(`.status-container button[data-status="${dfst}"]`);

    if ($defaultButton.length) {
        $defaultButton.addClass('s-selected');
        generateTable(dfst);
    }

});

document.onkeydown = checkKey;

function checkKey(e) {

    e = e || window.event;

    if (e.keyCode == '38') {
        if(selected_frame > 1) {
            selected_frame -= 1;
            listNavigation();
        }
    }
    else if (e.keyCode == '40') {
        if($('.list-item').length  > selected_frame) {
            selected_frame += 1;
            listNavigation();
        }
    }

}

function listNavigation() {
    var id = $('#list_'+selected_frame).attr('data-id');
    print(id);
    $('.list-item').removeClass('list-selected');
    $('#list_'+selected_frame).addClass('list-selected');

    delete_data = [id];
    scion.centralized_button(false, true, false, false);
}

function selectList(data) {
    selected_frame = data;
    listNavigation();
}

// DEFAULT FUNCTION
function success(record) {
    switch(actions) {
        case 'save':
            switch(module_content) {
                case 'preparation':
                    $('#purchase_orders_table').DataTable().draw();
                    scion.create.sc_modal('preparation_form').hide('all', modalHideFunction);
                    break;

                case 'preparation_detail':
                    $('#purchase_orders_table').DataTable().draw();
                    scion.create.sc_modal('preparation_detail_form').hide('all', modalHideFunction_detail);
                    listNavigation();
                    break;

                case 'project_split':
                    scion.create.sc_modal('project_split_form').hide('all', modalHideFunction_detail);
                    listNavigation();
                    break;

                case 'discount':
                    $('#purchase_orders_table').DataTable().draw();
                    scion.create.sc_modal('discount_form').hide('all', modalHideFunction_detail);
                    listNavigation();
                    break;

                case 'credit_note':
                    $('#purchase_orders_table').DataTable().draw();
                    scion.create.sc_modal('credit_note_form').hide('all', modalHideFunction_detail);
                    listNavigation();
                    break;
            }
            break;
        case 'update':
            switch(module_content) {
                case 'preparation':
                    $('#purchase_orders_table').DataTable().draw();
                    scion.create.sc_modal('preparation_form').hide('all', modalHideFunction);
                    preparation_func();
                    break;
                case 'preparation_detail':
                    // $('#purchase_orders_table').DataTable().draw();
                    scion.create.sc_modal('preparation_detail_form').hide('all', modalHideFunction_detail);
                    preparation_func();
                    break;
                case 'project_split':
                    // $('#purchase_orders_table').DataTable().draw();
                    scion.create.sc_modal('project_split_form').hide('all', modalHideFunction_detail);
                    preparation_func();
                    break;

                case 'discount':
                    $('#purchase_orders_table').DataTable().draw();
                    scion.create.sc_modal('discount_form').hide('all', modalHideFunction_detail);
                    listNavigation();
                    break;

                case 'credit_note':
                    $('#purchase_orders_table').DataTable().draw();
                    scion.create.sc_modal('credit_note_form').hide('all', modalHideFunction_detail);
                    listNavigation();
                    break;
            }
            break;
    }
}

function error() {}

function delete_success() {

    switch(module_content) {
        case 'preparation':
            var form_id = $('.form-record')[0].id;
            $('#'+form_id)[0].reset();
            actions = 'save';
            scion.centralized_button(false, true, false, false);

            $('#purchase_orders_table').DataTable().draw();
            break;
        case 'preparation_detail':
            var id = $('#list_'+selected_frame).attr('data-id');

            preparation_func();

            break;
    }
}

function delete_error() {}

function generateData() {
    active_id = po_id;
    switch(module_content) {
        case 'preparation':
            form_data = {
                _token: _token,
                supplier_id: $('#supplier_id').val(),
                delivery_date: $('#delivery_date').val(),
                site_id: $('#site_id').val(),
                po_date: $('#po_date').val(),
                contact_no: $('#contact_no').val(),
                reference: "NONE",
                terms: $('#terms').val() + " " + $('#term_type').val(),
                due_date: $('#due_date').val(),
                order_no: $('#order_no').val(),
                tax_type: $('#tax_type').val(),
                subtotal: $('#subtotal').val(),
                total_with_tax: $('#total_with_tax').val(),
                delivery_instruction: $('#delivery_instruction').val(),
                split_type: $('#split_type').val(),
                project: $('#project').val(),
            };
            break;

        case 'preparation_detail':
            form_data = {
                _token: _token,
                purchase_order_id: po_id,
                item: $('#item').val(),
                description: $('#description').val(),
                quantity: $('#quantity').val(),
                unit_price: $('#unit_price').val(),
                discount: $('#discount').val(),
                tax_rate: $('#tax_rate').val(),
                total_amount: $('#total_amount').val(),
                split: $('#split').val(),
            };
            break;

        case 'split_po':
            form_data = {
                _token: _token,
                data: [],
                po_id: po_id
            };

            $.each($('#split_id .row'), (i,v) => {
                form_data.data.push({
                    purchased_order_details_id: po_id,
                    site_id: $('#split_item_' + i + ' select').val(),
                    chart_id: $('#split_item_' + i + ' select.charts').val(),
                    amount: $('#split_item_' + i + ' input').val()
                });
            });

            break;

        case 'project_split':
            form_data = {
                _token: _token,
                data: [],
                po_id: $('#list_'+selected_frame).attr('data-id')
            };

            $.each($('#project_container .row'), (i,v) => {
                form_data.data.push({
                    po_id: $('#list_'+selected_frame).attr('data-id'),
                    project_id: $('#split_project_' + i + ' select').val(),
                    amount: $('#split_project_' + i + ' input.value-proj').val(),
                    percentage: $('#split_project_' + i + ' input.percentage-proj').val()
                });
            });

            break;

        case 'discount':
            if($('#po_type').val() === 'all') {
                form_data = {
                    _token: _token,
                    name: $('#all_discount #discount_name').val(),
                    remarks: $('#all_discount #discount_details').val(),
                    po_id: $('#list_'+selected_frame).attr('data-id'),
                    po_type: $('#po_type').val(),
                    discount_type: $('#all_discount #discount_type').val(),
                    value: $('#all_discount #discount_value').val()
                };
            }
            else {
                form_data = {
                    _token: _token,
                    po_type: $('#po_type').val(),
                    data: []
                };

                $.each($('.discount-table tbody tr'), (i, v) => {
                    form_data.data.push({
                        name: $('#'+v.id+' #discount_name').val(),
                        remarks: $('#'+v.id+' #discount_details').val(),
                        po_id: v.id.split('_')[1],
                        discount_type: $('#'+v.id+' #discount_type').val(),
                        value: $('#'+v.id+' #discount_value').val()
                    })
                });
            }

            break;

        case 'credit_note':

            form_data = {
                _token: _token,
                data: []
            };

            $.each($('.credit-project-list>.row.selected-proj'), (i, v) => {
                form_data.data.push({
                    po_id: $('#list_'+selected_frame).attr('data-id'),
                    project_id: $('#'+v.id).attr('data-id'),
                    status: 'draft',
                    amount: $('#'+v.id+' #credit_amount').val(),
                    chart_id: $('#'+v.id+' #credit_account').val(),
                    particulars: $('#credit_particulars').val()
                });
            });

            break;
    }

    return form_data;
}

function generateDeleteItems() {
    switch(module_content) {
        case 'preparation':
            delete_data = delete_data;

            break;
    }
}

// EXTRA FUNCTION
function preparation_func() {
    modal_content = 'preparation';
    module_content = 'preparation';
    module_url = '/purchasing/delivery_receipt';
    module_type = 'custom';

    scion.centralized_button(false, true, false, false);

    if ($.fn.DataTable.isDataTable('#purchase_orders_table')) {
        $('#purchase_orders_table').DataTable().destroy();
    }

    var status = ($('.s-selected').text().slice(4)).replaceAll(' ', '_');

    scion.create.table(
        'purchase_orders_table',
        module_url + '/get/' + (status===""?"DRAFT":status),
        [
            { data: "DT_RowIndex", title:`ORDER LIST <span class='command'>`, render: function(data, type, row, meta) {
                var html = "";
                html += `
                    <div class="list-item" id="list_${meta.row + 1}"
                    data-id="${row.id}" data-split-status="${row.split_type}" data-amount="${row.subtotal}" onclick="selectList(${meta.row + 1})">
                        <div class="row">
                            <div class="col-6">
                                <div class="list-order">${row.order_no}</div>
                                <div class="list-ref"><span class="split-status-${row.split_type}">${row.split_type === 'single'?"SINGLE":"SPLIT"}</span> | ${moment(row.po_date).format('MMM DD, YYYY')}</div>
                            </div>
                            <div class="col-6 text-right">
                                <div class="list-sub-total">${scion.currency(row.subtotal)}</div>
                            </div>
                        </div>
                    </div>
                `;
                //<span class="taxable-price">/ ${scion.currency(row.total_with_tax)}</span>
                return html;
            }},
        ], 'Brtip', [], false, false
    );

    setTimeout(() => {
        listNavigation();
    }, 500);
}

function add_cart() {
    var id = $('#list_'+selected_frame).attr('data-id');

    module_content = 'preparation_detail';
    modal_content = 'preparation_detail';
    module_type = 'custom';
    module_url = '/purchasing/purchase_order_details';
    page_title = 'ADD TO CART';
    po_id = id;

    actions = 'save';
    record_id = null;

    $('#preparation_detail_form .form-control').val('');

    scion.create.sc_modal("preparation_detail_form", 'DELIVERY RECEIPT   Details').show(modalShowFunction);
}

function print(id, itemCode = '', action = '') {
    var check_item = 0;
    const dataToSend = {};

    if(id !== undefined) {
        if (action && action.trim() !== '') {
        dataToSend.action = action;
        }
        if (itemCode && itemCode.trim() !== '') {
            dataToSend.item_code = itemCode;
        }
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/purchasing/delivery_receipt/print/' + id,
            method: 'post',
            data: dataToSend,
            success: function (data) {
                const order = data.purchase_order;
                const details = data.details;
                
                $('#po_date1').text(order && order.po_date ? moment(order.po_date).format('MMM DD, YYYY') : '-');
                $('#po_no').text(order && order.dr_sequence ? order.dr_sequence : '-');
                $('#po_vendor1').text(order && order.supplier_name ? order.supplier_name : '-');
                $('#po_vendor_address1').text(order && order.address ? order.address : '-');
                $('#po_terms1').text(order && order.terms ? order.terms : '-');
                $('#po_due_date1').text(order && order.due_date ? order.due_date : '-');

                $('#po_prepared_by').text(order && order.prepared_by_name ? order.prepared_by_name : '');
                $('#po_prepared_by_date').text(order && order.prepared_at ? order.prepared_at : '');

                $('#po_checked_by').text(order && order.reviewed_by_name ? order.reviewed_by_name : '');
                $('#po_checked_by_date').text(order && order.reviewed_at ? order.reviewed_at : '');

                $('#po_checked_by_2').text(order && order.reviewed_by_name ? order.reviewed_by_name : '');
                $('#po_checked_by_date_2').text(order && order.reviewed_at ? order.reviewed_at : '');

                $('#po_approved_by').text(order && order.approved_by_name ? order.approved_by_name : '');
                $('#po_approved_by_date').text(order && order.approved_at ? order.approved_at : '');

                $('#po_received_by').text(order && order.received_by_name ? order.received_by_name : '');
                $('#po_received_by_date').text(order && order.received_at);

                $('#details-table tbody').empty();

                let total = 0;
                $.each(details, function (index, detail) {
                    let row = $('<tr>');

                    const amount = detail.sent_quantity * detail.unit_price;

                    row.append('<td class="text-left">' + 
                        (detail.item_code ?? '') + ' - ' + 
                        (detail.item_name ?? '') + ' - ' + 
                        (detail.description ?? '') + '</td>');
                    row.append('<td>' + detail.sent_quantity + '</td>');
                    row.append('<td>' + (detail.unit_of_measure ?? '') + '</td>');
                    row.append('<td>' + scion.currency(detail.unit_price) + '</td>');
                    row.append('<td>' + scion.currency(amount) + '</td>');
                    $('#details-table tbody').append(row);

                    total += parseFloat(amount || 0);
                });

                $('#po_total').text(scion.currency(total));
                $('#po_total').attr('data-total', total);

                if (data.project) {
                    let projectText = '';
                    if (data.project.project_code && data.project.project_name) {
                        projectText = data.project.project_code + ' - ' + data.project.project_name;
                    } else if (data.project.project_code) {
                        projectText = data.project.project_code;
                    } else if (data.project.project_name) {
                        projectText = data.project.project_name;
                    }
                    $('#project_list').html(projectText);

                    if (data.project.address) {
                        $('#po_ship_to_address1').text(data.project.address);
                    } else {
                        $('#po_ship_to_address1').text('-');
                    }
                } else {
                    $('#project_list').html('-');
                    $('#po_ship_to_address1').text('-');
                }

                if (data.details.length > 0) {
                    $('.btn-note').addClass('hide');
                } else {
                    $('.btn-note').removeClass('hide');
                }
            }
        });
    } else {
        $('#po_date1, #po_no, #po_vendor1, #po_vendor_address1, #po_ship_to_address1, #po_terms1, #po_due_date1').text('-');
        $('#po_prepared_by, #po_prepared_by_date, #po_checked_by, #po_checked_by_date, #po_checked_by_2, #po_checked_by_date_2, #po_approved_by, #po_approved_by_date, #po_received_by, #po_received_by_date').text('');
        $('#details-table tbody').empty();
        $('#po_total').text(scion.currency(0)).attr('data-total', '0');
        $('#project_list').html('');
        $('.discount-container').html('');
    }

    selected_print = 'printPO';
}


function printDiv() {
    var divToPrint=document.getElementById('printPO');
    var newWin=window.open('','Print-Window');
    newWin.document.open();
    newWin.document.write('<html><head><link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet"><link rel="stylesheet" href="/css/custom/po-print.css"><link href="/backend/css/modern.css" rel="stylesheet"><link href="/css/custom/id.css" rel="stylesheet"></head><body onload="window.print()">'+divToPrint.innerHTML+'</body></html>');
    newWin.document.close();

    // setTimeout(function(){newWin.close();},10);

    setTimeout(function() {
        newWin.print();
    }, 3000); // Change the delay time as needed (in milliseconds)

}

function modalShowFunction() {
    scion.centralized_button(true, false, true, true);
}

function modalHideFunction() {
    scion.centralized_button(false, true, false, false);
    scion.create.sc_modal('preparation_detail').hide('all');
}

function modalHideFunction_detail() {
    preparation_func();
    $('.sc-modal-content').css('display', 'none');
    scion.centralized_button(false, true, false, false);
}

function generateTable(status) {
    $('.status-container button').removeClass('s-selected');
    $('.status-container button.status-' + status).addClass('s-selected');

    if ($.fn.DataTable.isDataTable('#purchase_orders_table')) {
        $('#purchase_orders_table').DataTable().destroy();
    }

    scion.create.table(
        'purchase_orders_table',
        module_url + '/get/' + status,
        [
            { data: "DT_RowIndex", 
                // title:`ORDER LIST <span class='command'><button class="btn btn-sm btn-primary" onclick="editPO()">EDIT DELIVERY RECEIPT</button></span>`, render: function(data, type, row, meta) {
                
                title:`ORDER LIST 
                    <span class='command'>
                        <button class="btn btn-sm btn-warning" onclick="viewItem()">VIEW DETAILS</button>

                    </span>`,
                    render: function(data, type, row, meta) {

                var html = "";
                html += `
                    <div class="list-item" id="list_${meta.row + 1}"
                    data-id="${row.id}" data-split-status="${row.split_type}" data-amount="${row.subtotal}" onclick="selectList(${meta.row + 1})">
                        <div class="row">
                            <div class="col-6">
                                <div class="list-order">${row.order_no}</div>
                                <div class="list-ref"><span class="split-status-${row.split_type}">${row.split_type === 'single'?"SINGLE":"SPLIT"}</span> | ${moment(row.po_date).format('MMM DD, YYYY')}</div>
                            </div>
                            <div class="col-6 text-right">
                                <div class="list-sub-total">${scion.currency(row.subtotal)}</div>
                            </div>
                        </div>
                    </div>
                `;
                //<span class="taxable-price">/ ${scion.currency(row.total_with_tax)}</span>
                return html;
            }},
        ], 'Brtip', [], false, false
    );

    selected_frame = 1;

    setTimeout(() => {
        selectList(1);
    }, 1000);

    $('.grid-footer button').addClass('btn-hide');
    $('.grid-footer button.for-'+status).removeClass('btn-hide');

    $('#printPO .po-title').text(status === "PARTIALLY_DELIVERED" || status === "COMPLETED"?"Delivery Receipt":"DELIVERY RECEIPT");

    if(status === "PARTIALLY_DELIVERED" || status === "COMPLETED") {
        $('.po-footer').css('display', 'none');
        $('.dr-footer').css('display', 'table');
    }
    else {
        $('.po-footer').css('display', 'table');
        $('.dr-footer').css('display', 'none');
    }

}

function setStatus(status) {
    var id = $('#list_'+selected_frame).attr('data-id');
    var data = {
        _token: _token,
        status: status,
    };
    var userConfirmed = confirm("Are you sure you want to move this DELIVERY RECEIPT?");

    if (userConfirmed) {
        $.post('/purchasing/delivery_receipt/set-status/' + id, data).done((i, v) => {
            generateTable(status);
        });
    } else {
    }
}


let maxQuantity = 0;
let itemsData = [];


function viewPO() {
    const selected = $('.list-selected');
    const poDetailId = selected.attr('data-id');
    const amount = selected.attr('data-amount');
    const maxQty = selected.attr('data-max-quantity');

    if (!poDetailId) {
        alert("No Purchase Order detail selected!");
        return;
    }

    $.get('/purchasing/delivery_receipt/details/' + poDetailId, function(response) {
        if (response.error) {
            alert(response.error);
            return;
        }

        openEditModal(response.order_no, response.items, maxQty,poDetailId);
    }).fail(function() {
        alert('Failed to fetch delivery receipt details.');
    });
}

function openEditModal(orderNo, items, qtyLimit,poDetailId) {
    maxQuantity = parseInt(qtyLimit);
    $('#purchase_order_detail_id').val(orderNo);
    $('#quantity_error').addClass('d-none');
    $('#save_quantity_btn').prop('disabled', false);

    let itemHtml = "";
    itemsData = [];
    items.forEach(item => {
        const remainingQty = item.quantity_set - item.sent_quantity;

        itemHtml += `
            <tr>
                <td>${item.material_name}</td>
                <td>${item.quantity_set}</td>
                <td>${item.sent_quantity}</td>
                <td>
                    <input 
                        type="number" 
                        class="form-control send-quantity"
                        data-material-id="${item.material_id}"
                        data-material-name="${item.material_name}"
                        data-pod-id="${item.pod_id}" 
                        data-remaining="${remainingQty}" 
                        min="0" 
                        max="${remainingQty}" 
                        value="0" 
                        oninput="validateQuantity(this)" 
                    />
                </td>
            </tr>
        `;
        itemsData.push(item);
    });
    
    $('#item_list').html(itemHtml);

    scion.create.sc_modal("edit_quantity_modal", 'DELIVERY RECEIPT DETAILS').show(modalShowFunction);
}

function validateQuantity(input) {
    let isValid = true;

    $('.send-quantity').each(function() {
        const value = $(this).val();
        const quantity = parseInt(value);
        const remainingQty = parseInt($(this).data('remaining'));

        if (value !== '' && (isNaN(quantity) || quantity < 0 || quantity > remainingQty)) {
            isValid = false;
        }
    });

    if (!isValid) {
        $('#quantity_error').removeClass('d-none').text('Invalid quantity. It exceeds the remaining quantity allowed.');
        $('#save_quantity_btn').prop('disabled', true);
    } else {
        $('#quantity_error').addClass('d-none').text('');
        $('#save_quantity_btn').prop('disabled', false);
    }
}

function saveQuantity() {
    const quantitiesToSend = [];

    $('.send-quantity').each(function() {
        const quantity = parseInt($(this).val());
        
        if (quantity > 0) {
            quantitiesToSend.push({
                material_id: $(this).data('material-id'),
                material_name: $(this).data('material-name'),
                po_detail_id: $(this).data('pod-id'),
                quantity: quantity
            });
        }
    });

    if (quantitiesToSend.length === 0) {
        alert('Please enter at least one quantity to send.');
        return;
    }
    
    const projectId = $('#project').val();

    $.ajax({
        url: '/purchasing/delivery_receipt/' + $('#purchase_order_detail_id').val() + '/send-quantity',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            quantities: quantitiesToSend,
            project_id: projectId,
        },
        success: function(response) {
            if (response.success) {
                alert('Quantity successfully updated!');
                scion.create.sc_modal('edit_quantity_modal').hide('all');
                print()
            } else {
                alert(response.message);
            }
        },
        error: function(xhr, status, error) {
            alert('An error occurred. Please try again.');
        }
    });
}

function viewItem() {
    const selected = $('.list-selected');
    const poDetailId = selected.attr('data-id');
    const amount = selected.attr('data-amount');
    const maxQty = selected.attr('data-max-quantity');

    if (!poDetailId) {
        alert("No Purchase Order detail selected!");
        return;
    }

    $.get('/purchasing/delivery_receipt/details/' + poDetailId, function(response) {
        if (response.error) {
            alert(response.error);
            return;
        }

        openViewItem(response.order_no, response.items, maxQty,poDetailId);
    }).fail(function() {
        alert('Failed to fetch delivery receipt details.');
    });
}

function openViewItem(orderNo, items, qtyLimit, poDetailId) {
    maxQuantity = parseInt(qtyLimit);
    $('#purchase_order_detail_id').val(orderNo);
    $('#quantity_error').addClass('d-none');
    $('#save_quantity_btn').prop('disabled', false);

    let itemHtml = "";
    itemsData = [];

    items.forEach(item => {
        const remainingQty = item.quantity_set - item.sent_quantity;
        const drSequence = item.dr_sequence ? item.dr_sequence : 'N/A';
        const updatedAt = item.updated_at ? item.updated_at : 'N/A';

        itemHtml += `
            <tr data-item-code="${item.item_code}">
                <td>${drSequence}</td>
                <td>${updatedAt}</td>
                <td>${item.quantity_set}</td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="viewItemDetails(${poDetailId}, '${item.item_code}', 'preview')">
                        View
                    </button>
                </td>
            </tr>
        `;
        itemsData.push(item);
    });

    $('#detail_list').html(itemHtml);

    scion.create.sc_modal("view_quantity", 'DELIVERY RECEIPT DETAILS').show(modalShowFunction);
}

function viewItemDetails(id, itemCode, action = '') {
    print(id, itemCode, action);
    scion.create.sc_modal('view_quantity').hide('all');
}