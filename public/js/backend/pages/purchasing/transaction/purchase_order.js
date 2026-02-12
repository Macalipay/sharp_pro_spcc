var selected_frame = 1;

let materials = [];
let po_detail_id;
let current_status = 'DRAFT';
$(function() {
    module_content = 'purchase_orders';
    module_url = '/purchasing/purchase_order';
    tab_active = 'preparation';
    page_title = "";
    actions = 'save';
    module_type = 'transaction';
    modal_content = 'preparation';
    po_id = '';

    scion.centralized_button(false, true, false, false);
    scion.action.tab(tab_active);

    preparation_func();
    $('.status-container button.status-DRAFT').addClass('s-selected');

    $.get('/purchasing/materials', function(data) {
        materials = data;
    });

     $(document).on('click', '#searchBtn', function() {
        generateTable(current_status);
    });

    $(document).on('click', '#addRowBtn', function() {
        addDetailRow();
    });

     $(document).on('change', '#employee_id', function() {
        employee_info(this.value);
    });

    $(document).on('shown.bs.modal', '#preparation_detail_form', function() {
        if ($('#detailsTableBody tr').length === 0) {
            addDetailRow();
        }
    });
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
            $('#row_' + po_detail_id).remove();
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
            console.log(delete_data);

            break;
    }
}

// EXTRA FUNCTION
function preparation_func() {
    modal_content = 'preparation';
    module_content = 'preparation';
    module_url = '/purchasing/purchase_orders';
    module_type = 'custom';

    scion.centralized_button(false, true, false, false);

    if ($.fn.DataTable.isDataTable('#purchase_orders_table')) {
        $('#purchase_orders_table').DataTable().destroy();
    }

    var status = ($('.s-selected').text().slice(4)).replaceAll(' ', '_');

    var project_id   = $('#project_id').val()       || 'all_project';
    var filter_po_no = $('#filter_po_no').val()     || 'no_po';
    var start_date   = $('#start_date').val()       || 'no_start';
    var end_date     = $('#end_date').val()         || 'no_end';

    scion.create.table(
        'purchase_orders_table',
        module_url + '/get/' + (status===""?"DRAFT":status) + '/' + project_id + '/' + start_date + '/' + end_date + '/' + filter_po_no,
        [
            { data: "DT_RowIndex", title:`ORDER LIST <span class='command'><button class="btn btn-sm btn-primary" onclick="editPO()">EDIT PURCHASE ORDER</button></span>`, render: function(data, type, row, meta) {
                var html = "";
                let orderDisplay = row.order_no;

                if (row.oldpo && row.oldpo.trim() !== "") {
                    orderDisplay += ` <span class="text-muted" style="font-size:12px;font-style:italic;">( old po: ${row.oldpo} )</span>`;
                }

                html += `
                    <div class="list-item" id="list_${meta.row + 1}"
                    data-id="${row.id}" data-split-status="${row.split_type}" data-amount="${row.subtotal}" onclick="selectList(${meta.row + 1})">
                        <div class="row">
                            <div class="col-6">
                                <div class="list-order">${orderDisplay}</div>
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

function editDetails(id, edited) {
    module_content = 'preparation_detail';
    modal_content = 'preparation_detail';
    module_type = 'custom';
    module_url = '/purchasing/purchase_order_details';
    page_title = 'ADD TO CART';
    po_id = id;
    actions = 'update';
    po_detail_id=edited;

    scion.record.edit('/purchasing/purchase_order_details/edit/', edited);
    $('#detailsTableBody').empty();
    addDetailRow();
    $.get('/purchasing/purchase_order_details/edit/' + edited, function(response) {
        console.log(response);
          // Store the desired unit in a data attribute
        $('#detail_unit_measure').data('selected-unit', response.purchase_order_details.unit_measure);

        // Set the item value, which triggers the population of the unit dropdown
        $('#detail_item').val(response.purchase_order_details.item).trigger('change');

        $('#detail_description').val(response.purchase_order_details.description);
        $('#detail_quantity').val(response.purchase_order_details.quantity);
        $('#detail_unit_price').val(response.purchase_order_details.unit_price);
        $('#detail_tax_rate').val(response.purchase_order_details.tax_rate);
        $('#detail_total_amount').val(response.purchase_order_details.total_amount);
    });

    $('#addRowBtn').hide();
}

function add_cart() {
    var id = $('#list_' + selected_frame).attr('data-id');

    module_content = 'preparation_detail';
    modal_content = 'preparation_detail';
    module_type = 'custom';
    module_url = '/purchasing/purchase_order_details';
    page_title = 'ADD TO CART';
    po_id = id;

    actions = 'save';
    record_id = null;

    $('#preparation_detail_form .form-control').val('');
    $('#purchase_order_id').val(po_id);

    addDetailRow();
    scion.create.sc_modal("preparation_detail_form", 'Purchase Order Details').show(modalShowFunction);
}

function addDetailRow() {
    const tbody = document.getElementById('detailsTableBody');
    const rowCount = tbody.children.length;
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td>
            <select class="form-control form-control-sm item-select" id="detail_item" name="item" required>
                <option value=""></option>
                ${window.materials.map(item => `<option value="${item.id}">${item.item_name}</option>`).join('')}
            </select>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm" id="detail_description" name="description" placeholder="Enter description" required>
        </td>
        <td class="text-center">
            <input type="number" class="form-control form-control-sm quantity text-center" id="detail_quantity" name="detail_quantity" value="1" min="1" oninput="updatePrice()" required>
        </td>
        <td>
            <select class="form-control form-control-sm unit-measure" id="detail_unit_measure" name="unit_measure" required>
                <option value=""></option>
            </select>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm unit-price text-right" id="detail_unit_price" name="unit_price" value="0" min="0" step="0.01" oninput="updatePrice()" required>
        </td>
        <td class="text-center">
            <input type="number" class="form-control form-control-sm text-center" id="detail_tax_rate" name="tax_rate" value="0" min="0" required>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm total-amount text-right" id="detail_total_amount" name="total_amount" value="0" readonly>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger" onclick="removeDetailRow(this)" title="Remove Row">
                <i class="fas fa-times"></i>
            </button>
        </td>
    `;
    tbody.appendChild(newRow);

    $(newRow).find('.item-select').select2({
        dropdownParent: $('#preparation_detail_form'),
        width: '100%',
        placeholder: 'Select an item',
        allowClear: true
    });

    const quantityInput = newRow.querySelector('.quantity');
    const unitPriceInput = newRow.querySelector('.unit-price');
    const totalAmountInput = newRow.querySelector('.total-amount');

    function updateRowTotal() {
        const quantity = parseFloat(quantityInput.value) || 0;
        const unitPrice = parseFloat(unitPriceInput.value) || 0;
        totalAmountInput.value = (quantity * unitPrice).toFixed(2);
    }

    quantityInput.addEventListener('input', updateRowTotal);
    unitPriceInput.addEventListener('input', updateRowTotal);

    $(newRow).find('.item-select').on('change', function() {
        const itemId = this.value;
        const unitMeasureSelect = $(this).closest('tr').find('.unit-measure');

        unitMeasureSelect.empty().append('<option value="">Loading...</option>');

        if (itemId) {
            $.get(`/purchasing/purchase_orders/materials/${itemId}/units`, function(data) {
                unitMeasureSelect.empty().append('<option value=""></option>');

                data.forEach(function(unit) {
                    unitMeasureSelect.append(`<option value="${unit.trim()}">${unit.trim()}</option>`);
                });

                // If you have a pre-selected value (e.g., from edit), set it here
                if (unitMeasureSelect.data('selected-unit')) {
                    unitMeasureSelect.val(unitMeasureSelect.data('selected-unit')).trigger('change');
                }
            });
        } else {
            unitMeasureSelect.empty().append('<option value=""></option>');
        }
    });
}

function removeDetailRow(button) {
    button.closest('tr').remove();
}

function updateDetail(detail_id, po_id) {
    const data = {
            _token: _token,
            purchase_order_detail_id: detail_id,
            purchase_order_id: po_id,
            item: $('.item-select').val(),
            description: $('input[name="description"]').val(),
            quantity: $('.quantity').val(),
            unit_measure: $('.unit-measure').val(),
            unit_price: $('.unit-price').val(),
            tax_rate: $('input[name="tax_rate"]').val(),
            total_amount: $('.total-amount').val()
        };

         $.ajax({
            url: '/purchasing/purchase_order_details/update/' + detail_id,
            type: 'POST',
            data: data,
            success: function(response) {
                $('#preparation_detailForm')[0].reset();
                $('#detailsTableBody').empty();
                $('#purchase_orders_table').DataTable().draw();
                scion.create.sc_modal('preparation_detail_form').hide('all', modalHideFunction_detail);
            },
        });
}

function saveDetails(e) {
   if (actions == 'save') {
     e.preventDefault();

    const rows = $('#detailsTableBody tr');
    let savedCount = 0;
    let totalRows = rows.length;
    let hasError = false;

    rows.each(function() {
        const row = $(this);
        const data = {
            _token: _token,
            purchase_order_id: po_id,
            item: row.find('.item-select').val(),
            description: row.find('input[name="description"]').val(),
            quantity: row.find('.quantity').val(),
            unit_measure: row.find('.unit-measure').val(),
            unit_price: row.find('.unit-price').val(),
            tax_rate: row.find('input[name="tax_rate"]').val(),
            total_amount: row.find('.total-amount').val()
        };

        row.find('.is-invalid').removeClass('is-invalid');
        row.find('.invalid-feedback').remove();

        if (!data.item) {
            row.find('.item-select').addClass('is-invalid');
            row.find('.item-select').closest('td').append('<div class="invalid-feedback">Please select an item</div>');
            hasError = true;
        }
        if (!data.description) {
            row.find('input[name="description"]').addClass('is-invalid');
            row.find('input[name="description"]').closest('td').append('<div class="invalid-feedback">Description is required</div>');
            hasError = true;
        }
        if (!data.unit_measure) {
            row.find('.unit-measure').addClass('is-invalid');
            row.find('.unit-measure').closest('td').append('<div class="invalid-feedback">Please select a unit</div>');
            hasError = true;
        }
        if (!data.tax_rate) {
            row.find('input[name="tax_rate"]').addClass('is-invalid');
            row.find('input[name="tax_rate"]').closest('td').append('<div class="invalid-feedback">Tax rate is required</div>');
            hasError = true;
        }

        if (hasError) {
            return false;
        }

        $.ajax({
            url: '/purchasing/purchase_order_details/save',
            type: 'POST',
            data: data,
            success: function(response) {
                savedCount++;
                if (savedCount === totalRows) {
                    toastr.success('All records saved successfully!');

                    $('#preparation_detailForm')[0].reset();
                    $('#detailsTableBody').empty();
                    $('#purchase_orders_table').DataTable().draw();
                    scion.create.sc_modal('preparation_detail_form').hide('all', modalHideFunction_detail);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(field => {
                        const input = row.find(`[name="${field}"]`);
                        input.addClass('is-invalid');
                        input.closest('td').append(`<div class="invalid-feedback">${errors[field][0]}</div>`);
                    });
                } else {
                    console.error(xhr.responseText);
                    toastr.error('Error saving record!');
                }
            }
        });
    });
   } else {
     updateDetail(po_detail_id, po_id);
    
   }
}

function print(id) {
    var check_item = 0;

    if(id !== undefined) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/purchasing/purchase_orders/print/' + id,
            method: 'post',
            data: {},
            success: function(data) {
                var html = '';
                var total = parseFloat(data.purchase_orders.subtotal);

                // scion.create.sc_modal("poPrint", 'Purchase Order').show(modalShowFunction);

                $('#po_date1').text(moment(data.purchase_orders.po_date).format('MMM DD, YYYY'));
                $('#po_no').text(data.purchase_orders.order_no);
                $('#old_po').text(data.purchase_orders.oldpo);
                $('#po_vendor1').text(data.purchase_orders.supplier.supplier_name);
                $('#po_vendor_address1').text(data.purchase_orders.supplier.address);
                // $('#po_ship_to1').text(data.purchase_orders.site.project_name);
                // $('#po_ship_to_address1').text(data.purchase_orders.site.location);
                $('#po_terms1').text(data.purchase_orders.terms);
                $('#po_due_date1').text(data.purchase_orders.due_date);
                $('#po_prepared_by').text(data.purchase_orders.prepared_by !== null?data.purchase_orders.prepared_by.firstname + ' ' +data.purchase_orders.prepared_by.lastname:'');
                $('#po_prepared_by_date').text(data.purchase_orders.prepared_at);

                $('#po_checked_by').text(data.purchase_orders.reviewed_by !== null?data.purchase_orders.reviewed_by.firstname + ' ' +data.purchase_orders.reviewed_by.lastname:'');
                $('#po_checked_by_date').text(data.purchase_orders.reviewed_at);

                $('#po_checked_by_2').text(data.purchase_orders.reviewed_by !== null?data.purchase_orders.reviewed_by.firstname + ' ' +data.purchase_orders.reviewed_by.lastname:'');
                $('#po_checked_by_date_2').text(data.purchase_orders.reviewed_at);

                $('#po_approved_by').text(data.purchase_orders.approved_by !== null?data.purchase_orders.approved_by.firstname + ' ' +data.purchase_orders.approved_by.lastname:'');
                $('#po_approved_by_date').text(data.purchase_orders.approved_at);

                $('#po_received_by').text(data.purchase_orders.received_by !== null?data.purchase_orders.received_by.firstname + ' ' +data.purchase_orders.received_by.lastname:'');
                $('#po_received_by_date').text(data.purchase_orders.received_at);

                // Clear existing rows (if any) in the details table
                $('#details-table tbody').empty();

                // Populate details into a table
                $.each(data.purchase_orders.details, function(index, detail) {

                    check_item += parseFloat(check_item + (detail.discount !== null?1:0));
                    let unitMeasure = detail.unit_measure;
                    unitMeasure = unitMeasure ? unitMeasure.toUpperCase() : ' ';
                    
                    var row = $('<tr id="row_' + detail.id + '">');
                    row.append('<td class="text-left"><a href="#" class="align-middle edit" onclick="editDetails('+id+', '+detail.id+')"><i class="fas fa-pen"></i></a> <a href="#" class="align-middle delete" onclick="deleteDetails('+detail.id+')"><i class="fas fa-trash"></i></a> <a href="#" class="align-middle split" onclick="splitDetails('+detail.id+', ' + detail.total_amount + ')"><i class="fas fa-expand-alt"></i></a> ' + detail.item.item_code + ' - ' + detail.item.item_name +' - ' + detail.description + '</td>');
                    row.append('<td>' + detail.quantity + '</td>');
                    row.append('<td>' + unitMeasure  + '</td>');
                    if (!window.isStockClerk) {
                        row.append('<td>' + scion.currency(detail.unit_price) + '</td>');
                        row.append('<td>' + scion.currency(detail.total_amount) + '</td>');
                    }
                    row.append('</tr>');
                    $('#details-table tbody').append(row);

                    if(detail.discount !== null) {
                        if(detail.discount.value !== "0") {
                            var d_row = $('<tr>');
                            var discount_value = detail.discount.discount_type === 'percentage'?parseFloat(detail.total_amount * (detail.discount.value / 100)):detail.discount.value;

                            d_row.append(`<td colspan="4" style="text-align:right;font-style:italic;background:transparent;color:red;"> <a href="#" onclick="deleteDiscount(${detail.discount.id})"><i class="fas fa-times"></i></a> Discount ${(detail.discount.discount_type === 'percentage'?"(" + detail.discount.value + "%)":"")}</td>`);
                            d_row.append('<td style="text-align:center;background:transparent;color:red;">- '+scion.currency(discount_value)+'</td>');
                            d_row.append('</tr>');
                            total = total - discount_value;
                            $('#details-table tbody').append(d_row);
                        }
                    }

                });

                if(data.purchase_orders.discount !== null) {
                    if(data.purchase_orders.discount.value !== "0") {
                        var discount = '';
                        var discount_value = data.purchase_orders.discount.discount_type === 'percentage'?parseFloat(total * (data.purchase_orders.discount.value / 100)):data.purchase_orders.discount.value;

                        discount += '<td colspan="4" style="text-align:right;font-style:italic;background:yellow;color:red;"><a href="#"  onclick="deleteDiscount(' + data.purchase_orders.discount.id + ')"><i class="fas fa-times"></i></a> Discount ' + (data.purchase_orders.discount.discount_type === 'percentage'?"(" + data.purchase_orders.discount.value + "%)":"") + '</td>';
                        discount += '<td style="text-align:center;background:yellow;color:red;">- '+scion.currency(discount_value)+'</td>';

                        total = total - discount_value;

                        $('.discount-container').html(discount);
                        $('.discount-btn-action').text('UPDATE DISCOUNT');
                    }
                    // $('.discount-btn-action').prop('disabled', true);
                }
                else {
                    $('.discount-container').html('');
                    $('.discount-btn-action').text(check_item > 0?'UPDATE DISCOUNT':'ADD DISCOUNT');
                }

                if (data.purchase_orders.projects.length !== 0) {
                    const project = data.purchase_orders.projects[0].project;

                    if (project && project.region !== null) {
                        const address = `${project.barangay.name}, ${project.city.name}, ${project.province.name}, ${project.region.name}, Philippines, ${project.postal_code}`;
                        
                        const contactName = project.contact_name || '-';
                        const contactInfo = project.contact_info || '-';
                        const streetAddress = project.address || '';

                        const formattedShipping = 
                `${project.project_name}
                ${streetAddress}
                ${address}
                ${contactName}
                ${contactInfo}`;

                        $('#po_ship_to_address1').css('white-space', 'pre-line').text(formattedShipping);
                    } else {
                        $('#po_ship_to_address1').text('-');
                    }
                    
                    $.each(data.purchase_orders.projects, function(i, v) {
                        html += `${v.project.project_code} | `;
                    });

                    html = html.slice(0, html.lastIndexOf('|'));
                } else {
                    $('#po_ship_to_address1').text('-');
                    html = ' ';
                }

                if(data.purchase_orders.credits.length !== 0) {
                    $('.btn-note').addClass('hide');
                }
                else {
                    $('.btn-note').removeClass('hide');
                }

                if (!window.isStockClerk) {
                    $('#po_total').text(scion.currency(total));
                    $('#po_total').attr('data-total', total);
                } else {
                    $('#po_total').text('***');
                    $('#po_total').attr('data-total', '0');
                }

                $('#project_list').html(html);

            }
        });
    }
    else {
        $('#po_date1').text('-');
        $('#po_no').text('-');
        $('#po_vendor1').text('-');
        $('#po_vendor_address1').text('-');
        $('#po_ship_to_address1').text('-');
        $('#po_terms1').text('-');
        $('#po_due_date1').text('-');
        $('#po_prepared_by').text('');
        $('#po_prepared_by_date').text('');
        $('#po_checked_by').text('');
        $('#po_checked_by_date').text('');

        $('#po_checked_by_2').text('');
        $('#po_checked_by_date_2').text('');

        $('#po_approved_by').text('');
        $('#po_approved_by_date').text('');

        $('#po_received_by').text('');
        $('#po_received_by_date').text('');

        $('#details-table tbody').empty();

        $('#po_total').text(scion.currency(0));
        $('#po_total').attr('data-total', '0');

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
    module_url = '/purchasing/purchase_orders';
    generateTable(current_status);
    $('.sc-modal-content').css('display', 'none');
    scion.centralized_button(false, true, false, false);
}

function editPO() {
    po_id = $('.list-selected').attr('data-id');
    scion.record.edit('/purchasing/purchase_orders/edit/', $('.list-selected').attr('data-id'));
}

function updatePrice() {
    var quantity = parseFloat($('#quantity').val());
    var unit_price = parseFloat($('#unit_price').val());

    var total = unit_price * quantity;

    $('#total_amount').val(total);
}

function deleteDetails(id) {
    module_content = 'preparation_detail';
    modal_content = 'preparation_detail';
    module_type = 'custom';
    module_url = '/purchasing/purchase_order_details';
    page_title = 'ADD TO CART';
    po_id = id;
    po_detail_id = id;
    delete_data = [id];

    scion.record.delete(generateDeleteItems);
}

function splitDetails(id, amount) {
    var html = '';

    module_content = 'split_po';
    modal_content = 'split_po';
    module_type = 'custom';
    module_url = '/purchasing/split_po';
    page_title = 'SPLIT PURCHASE ORDER DETAILS';
    po_id = id;

    scion.create.sc_modal("split_po_form", 'SPLIT PURCHASE ORDER DETAILS').show(modalShowFunction);

    $.get('/purchasing/split_po/get-split/' + id, function(response) {
        $('#split_amount').val(amount);
        if(response.record.length === 0) {
            $('#split_no').val('1');
            splitItem();
        }
        else {
            $.get('/purchasing/project/get-record', function(resp) {
                $('#split_no').val(response.record.length);
                $.each(response.record, (i,v) => {
                    html += `<div class="row" id="split_item_${i}"><div class="col-md-4 mb-2"><select class="form-control form-control-sm">`;
                    html += `<option value=""></option>`;

                    resp.project.forEach(item => {
                        html += `<option value="${item.id}" ${v.site_id == item.id?'selected':''}>${item.project_name}</option>`;
                    });

                    html += `</select></div><div class="col-md-4 mb-2"><select class="form-control form-control-sm charts">`;
                    html += `<option value=""></option>`;
                    resp.chart.forEach(item => {
                        html += `<option value="${item.id}" ${v.chart_id == item.id?'selected':''}>${item.account_name}</option>`;
                    });
                    html += `</select></div>`;
                    html += `<div id="col_${i}" class="col-md-4 mb-2"><input type="number" class="form-control form-control-sm" value="${v.amount}"/></div></div>`;
                });

                $('#split_id').html(html);
            });
        }
    });
}

function splitItem() {
    var html = '';
    var id = $('#list_'+selected_frame).attr('data-id');

    if($('#split_no').val() !== '' && $('#split_no').val() !== "0" && $('#split_no').val() !== null) {
        $.get('/purchasing/project/get-record', function(response) {
            record = response.project;
            var amount = parseFloat($('#split_amount').val() / $('#split_no').val());
            $.get('/purchasing/project_split/get-split/' + id, function(resp) {
                if(resp.record.length === 0) {
                    for (let index = 0; index < $('#split_no').val(); index++) {
                        html += `<div class="row" id="split_item_${index}"><div class="col-md-4 mb-2"><select class="form-control form-control-sm">`;
                        html += `<option value=""></option>`;
                        response.project.forEach(item => {
                            html += `<option value="${item.id}">${item.project_name}</option>`;
                        });
                        html += `</select></div><div class="col-md-4 mb-2"><select class="form-control form-control-sm">`;
                        html += `<option value=""></option>`;
                        response.chart.forEach(item => {
                            html += `<option value="${item.id}">${item.account_name}</option>`;
                        });
                        html += `</select></div>`;
                        html += `<div id="col_${index}" class="col-md-4 mb-2"><input type="number" class="form-control form-control-sm" value="${amount}"/></div></div>`;
                    }
                }
                else {
                    $.each(resp.record, (i,v) => {
                        html += `<div class="row" id="split_item_${i}"><div class="col-md-4 mb-2"><select class="form-control form-control-sm">`;
                        html += `<option value=""></option>`;
                        response.project.forEach(item => {
                            html += `<option value="${item.id}" ${v.project_id === item.id?'selected':''}>${item.project_name}</option>`;
                        });
                        html += `</select></div><div class="col-md-4 mb-2"><select class="form-control form-control-sm charts">`;
                        html += `<option value=""></option>`;
                        response.chart.forEach(item => {
                            html += `<option value="${item.id}">${item.account_name}</option>`;
                        });
                        html += `</select></div>`;
                        html += `<div id="col_${i}" class="col-md-4 mb-2"><input type="number" class="form-control form-control-sm" value="${amount}"/></div></div>`;
                    });
                }

                $('#split_id').html(html);
            });

        });

    }
}

function add_project() {
    var html = '';
    var id = $('#list_'+selected_frame).attr('data-id');
    var split = $('#list_'+selected_frame).attr('data-split-status');
    var amount = $('#list_'+selected_frame).attr('data-amount');

    module_content = 'project_split';
    modal_content = 'project_split';
    module_type = 'custom';
    module_url = '/purchasing/project_split';
    page_title = 'ADD PROJECT';
    po_id = id;

    actions = "save";
    record_id = null;


    $('#project_split_no').val(1);
    $('#project_split_no').prop('disabled', split === 'single'?true:false);

    $('#split_project_amount').val(amount);
    splitProject();

    scion.create.sc_modal("project_split_form", 'ADD PROJECT').show(modalShowFunction);

    $.get('/purchasing/project_split/get-split/' + id, function(response) {
        $('#split_project_amount').val(amount);
        if(response.record.length === 0) {
            $('#project_split_no').val('1');
            splitItem();
        }
        else {
            $.get('/purchasing/project/get-record', function(resp) {
                $('#project_split_no').val(response.record.length);
                $.each(response.record, (i,v) => {
                    html += `<div class="row" id="split_project_${i}"><div class="col-md-6 mb-2"><select class="form-control form-control-sm">`;
                    html += `<option value=""></option>`;

                    resp.project.forEach(item => {
                        html += `<option value="${item.id}" ${v.project_id == item.id?'selected':''}>${item.project_name}</option>`;
                    });

                    html += `</select></div><div id="per_${i}" class="col-md-3 mb-2"><input type="number" class="form-control form-control-sm percentage-proj" value="${v.percentage}" ${split === 'single'?'disabled':''} oninput="percentageCompute(${i})"/></div><div id="col_${i}" class="col-md-3 mb-2"><input type="number" class="form-control form-control-sm value-proj" value="${v.amount}" ${split === 'single'?'disabled':''} oninput="valueCompute(${i})"/></div></div>`;
                });

                $('#project_container').html(html);
            });
        }
    });
}

function splitProject() {
    var html = '';
    var split = $('#list_'+selected_frame).attr('data-split-status');

    if($('#project_split_no').val() !== '' && $('#project_split_no').val() !== "0" && $('#project_split_no').val() !== null) {
        $.get('/purchasing/project/get-record', function(response) {
            record = response.project;
            var amount = parseFloat($('#split_project_amount').val() / $('#project_split_no').val());
            var perc = parseFloat((amount/$('#split_project_amount').val())*100);

            for (let index = 0; index < $('#project_split_no').val(); index++) {
                html += `<div class="row" id="split_project_${index}"><div class="col-md-6 mb-2"><select class="form-control form-control-sm">`;
                html += `<option value=""></option>`;
                response.project.forEach(item => {
                    html += `<option value="${item.id}">${item.project_name}</option>`;
                });
                html += `</select></div><div id="per_${index}" class="col-md-3 mb-2"><input type="number" class="form-control form-control-sm percentage-proj" value="${perc}" ${split === 'single'?'disabled':''} oninput="percentageCompute(${index})"/></div><div id="col_${index}" class="col-md-3 mb-2"><input type="number" class="form-control form-control-sm value-proj" value="${amount}" ${split === 'single'?'disabled':''} oninput="valueCompute(${index})"/></div></div>`;
            }

            $('#project_container').html(html);
        });
    }
}

function percentageCompute(i) {
    var percentage = $('#per_' + i + ' .percentage-proj').val();
    var amount = $('#split_project_amount').val();

    var total = parseFloat((percentage / 100) * amount);

    $('#col_' + i + ' .value-proj').val(total);
}

function valueCompute(i) {
    var value = $('#col_' + i + ' .value-proj').val();
    var amount = $('#split_project_amount').val();

    var total = parseFloat((value / amount) * 100);

    $('#per_' + i + ' .percentage-proj').val(total);
}

function add_discount() {

    module_content = 'discount';
    modal_content = 'discount';
    module_type = 'custom';
    module_url = '/purchasing/discount';
    page_title = 'ADD DISCOUNT';

    $('#po_type').val('all');

    selectType();
}

function selectType() {
    var id = $('#list_'+selected_frame).attr('data-id');
    var check_item = 0;

    $.post(`/purchasing/purchase_orders/print/${id}`, {_token: _token}).done((response) => {

        $('.discount-btn').removeClass('disc-selected');

        if(response.purchase_orders.discount !== null) {
            page_title = 'UPDATE DISCOUNT';
            actions = 'update';
            record_id = response.purchase_orders.discount.id;

            $('#po_type').val(response.purchase_orders.discount.po_type);
            $('#all_discount #discount_name').val(response.purchase_orders.discount.name !== null?response.purchase_orders.discount.name:'');
            $('#all_discount #discount_details').val(response.purchase_orders.discount.remarks !== null?response.purchase_orders.discount.remarks:'');
            $('#all_discount #discount_type').val(response.purchase_orders.discount.discount_type);
            $('#all_discount #discount_value').val(response.purchase_orders.discount.value);

            $('#all_discount').removeClass('hide-discount');
            $('#item_discount').addClass('hide-discount');

            selectDiscountType('all_discount', response.purchase_orders.discount.discount_type);
        }
        else {

            if(response.purchase_orders.details.length !== 0) {
                var item = "";

                item += `<div class="row"><div class="col-md-12"><table class="discount-table">`;
                item += `<thead style="font-size: 10px;"><th>ITEM</th><th>NAME</th><th>REMARKS</th><th>TYPE</th><th>VALUE</th></thead><tbody style="width:100%;font-size: 10px;">`;

                $.each(response.purchase_orders.details, (i,v) => {
                    check_item += parseFloat(check_item + (v.discount !== null?1:0));

                    item += `<tr id="disc_${v.id}">
                                <td style="width:100%;">${v.item.item_name}</td>
                                <td style="width:100%;">
                                    <input type="text" id="discount_name" name="discount_name" value="${v.discount !== null?v.discount.name !== null?v.discount.name:'':''}"/>
                                </td>
                                <td style="width:100%;">
                                    <input type="text" id="discount_details" name="discount_details" value="${v.discount !== null?v.discount.remarks !== null?v.discount.remarks:'':''}"/>
                                </td>
                                <td style="width:100%;">
                                    <select id="discount_type">
                                        <option value="percentage">PERCENTAGE</option>
                                        <option value="value">VALUE</option>
                                    </select>
                                </td>
                                <td style="width:100%;">
                                    <input type="number" id="discount_value" name="discount_value" value="${v.discount !== null?v.discount.value:''}"/>
                                </td>
                            </tr>`;

                            // if(v.discount !== null) {
                            //     selectDiscountType('disc_' + v.discount.id, v.discount.discount_type);
                            // }

                });

                item += `</tbody></table></div></div>`

                if(check_item > 0) {
                    page_title = 'UPDATE DISCOUNT';
                    actions = 'update';
                    record_id = 'all';

                    $('#po_type').val('item');

                    $('#all_discount').addClass('hide-discount');
                    $('#item_discount').removeClass('hide-discount');

                }
                else {
                    page_title = 'ADD DISCOUNT';
                    actions = 'save';
                    record_id = null;

                    if($('#po_type').val() === "item") {
                        $('#all_discount').addClass('hide-discount');
                        $('#item_discount').removeClass('hide-discount');
                    }
                    else {
                        $('#discount_po_number').val(response.purchase_orders.order_no);
                        $('#all_discount').removeClass('hide-discount');
                        $('#item_discount').addClass('hide-discount');
                    }

                }
                $('#item_discount').html(item);

            }
            else {
                $('#discount_po_number').val(response.purchase_orders.order_no);
                $('#all_discount').removeClass('hide-discount');
                $('#item_discount').addClass('hide-discount');
            }
        }

        scion.create.sc_modal("discount_form", page_title).show(modalShowFunction);
    });
}

function selectDiscountType(id, val) {
    $('#' + id + ' .discount-btn').removeClass('disc-selected');
    $('#' + id + ' .discount-' + val).addClass('disc-selected');
    $('#' + id + ' #discount_type').val(val);
}

function deleteDiscount(id) {
    $.post('/purchasing/discount/destroy', {_token:_token, id: id}).done((response) => {
        selectList($('#list_'+selected_frame).attr('data-id'));
    });
}

$(document).ready(function () {
    $('#po_typeseries').on('change', function() {
        if ($(this).val() === 'MANUAL') {
            console.log($(this).val())
            $('.manual_po').show();
        } else {
            $('.manual_po').hide();
            $('#manual_po').val('');
        }
    });

    $('#save_btn').on('click', function(e) {
        e.preventDefault();

        let formData = {
            current_po_id: po_id,
            action: actions,
            order_no: $('#order_no').val(),
            po_typeseries: $('#po_typeseries').val(),
            manual_po: $('#po_typeseries').val() === 'MANUAL' ? $('#manual_po').val() : null,
            supplier_id: $('#supplier_id').val(),
            project: $('#project').val(),
            delivery_date: $('#delivery_date').val(),
            po_date: $('#po_date').val(),
            contact_no: $('#contact_no').val(),
            terms: $('#terms').val(),
            term_type: $('#term_type').val(),
            due_date: $('#due_date').val(),
            tax_type: $('#tax_type').val(),
            subtotal: $('#subtotal').val(),
            total_with_tax: $('#total_with_tax').val(),
            delivery_instruction: $('#delivery_instruction').val(),
            split_type: $('#split_type').val()
        };

         $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/purchasing/purchase_orders/save',
            method: "POST",
            data: formData,
            success: function (response) {
                $('#preparationForm')[0].reset();
                $('.manual_po').hide();
                scion.create.sc_modal('preparation_form').hide('all');
                $('#purchase_orders_table').DataTable().ajax.reload(null, false);
                scion.centralized_button(false, true, false, false);
            },
            error: function (xhr) {
                let errors = xhr.responseJSON?.errors;
                if (errors) {
                    let errorMessages = Object.values(errors).map(err => err.join("\n")).join("\n");
                    toastr.error("Error:\n" + errorMessages);
                } else {
                    toastr.error("Something went wrong!");
                }
                $('#save_btn').prop('disabled', false).text('SAVE');
            }
        });
    });

    $('#item').on('change', function () {
        let itemId = $(this).val();

        $('#unit_measure').empty().append('<option value="">Loading...</option>');

        if (itemId) {
            $.get(`/purchasing/purchase_orders/materials/${itemId}/units`, function (data) {
                $('#detail_unit_measure').empty().append('<option value=""></option>');
                data.forEach(function (unit) {
                    $('#detail_unit_measure').append(`<option value="${unit}">${unit}</option>`);
                });
            });
        } else {
            $('#detail_unit_measure').empty().append('<option value=""></option>');
        }
    });

});

function createNote() {
    module_content = 'credit_note';
    modal_content = 'credit_note';
    module_type = 'custom';
    module_url = '/purchasing/credit_note';
    page_title = 'CREDIT NOTE';

    scion.create.sc_modal("credit_note_form", 'CREDIT NOTE').show(modalShowFunction);

    var id = $('#list_'+selected_frame).attr('data-id');

    $.post(`/purchasing/purchase_orders/print/${id}`, {_token:_token}).done((response)=>{
        var _d = response;
        var projects = '';
        var seperate = parseFloat($('#po_total').attr('data-total')/_d.purchase_orders.projects.length);

        $('#credit_po').val(_d.purchase_orders.order_no);
        $('#credit_supplier').val(_d.purchase_orders.supplier.supplier_name);
        $('#credit_total_amount').val($('#po_total').attr('data-total'));



        $.each(_d.purchase_orders.projects, (i,v)=>{
            projects += `<div class="row mb-1 selected-proj" id="c_proj_${v.project_id}" data-id="${v.project_id}">
                <div class="col-md-1"><input type="checkbox" onclick="checkProj(${v.project_id})" checked></div>
                <div class="col-md-3" style="font-weight:bold;">${v.project.project_code}</div>
                <div class="col-md-4" style="font-weight:bold;"><select class="form-control form-control-sm" id="credit_account" name="credit_account">
                    <option value=""></option>`

            $.each(_d.chart, (i,v) => {
                projects += `<option value="${v.id}">${v.account_name}</option>`;
            });

            projects += `</select></div>
                <div class="col-md-4 text-right"><input type="number" class="form-control form-control-sm" id="credit_amount" name="credit_amount" placeholder="AMOUNT" value="${seperate}"></div>
            </div>`;
        });

        $('.credit-project-list').html(projects);
    });
}

function checkProj(id) {
    if($('#c_proj_' + id + ' input[type="checkbox"]')[0].checked === true) {
        $('#c_proj_' + id).addClass('selected-proj');
    }
    else {
        $('#c_proj_' + id).removeClass('selected-proj');
    }
}

function changeTerms() {
    var chooseDate = new Date($('#po_date').val());
    var futureDate = null;

    switch($('#term_type').val()) {
        case "DAYS":
            chooseDate.setDate(chooseDate.getUTCDate()+parseInt($('#terms').val()));
            futureDate = chooseDate.getFullYear()+'-'+('0'+(chooseDate.getMonth()+1)).slice(-2)+'-'+('0'+(chooseDate.getDate())).slice(-2);
            break;
        case "MONTHS":
            chooseDate.setMonth(chooseDate.getMonth() + parseInt($('#terms').val()));
            futureDate = chooseDate.getFullYear() + '-' +
                         ('0' + (chooseDate.getMonth() + 1)).slice(-2) + '-' +
                         ('0' + (chooseDate.getDate())).slice(-2);
            break;
        case "YEARS":
            chooseDate.setFullYear(chooseDate.getFullYear() + parseInt($('#terms').val()));
            futureDate = chooseDate.getFullYear() + '-' +
                 ('0' + (chooseDate.getMonth() + 1)).slice(-2) + '-' +
                 ('0' + (chooseDate.getDate())).slice(-2);
            break;
    }

    $('#due_date').val(futureDate);
}

function employee_info(id){
    $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
   $.post('/masterlist/employee/' + id, function(response) {
        console.log(response.employee.phone1);
        $('#contact_no').val(response.employee.phone1);
    });
}

function generateTable(status) {
    current_status = status;
    $('.status-container button').removeClass('s-selected');
    $('.status-container button.status-' + status).addClass('s-selected');

    if ($.fn.DataTable.isDataTable('#purchase_orders_table')) {
        $('#purchase_orders_table').DataTable().destroy();
    }

    
    var project_id   = $('#project_id').val()       || 'all_project';
    var filter_po_no = $('#filter_po_no').val()     || 'no_po';
    var start_date   = $('#start_date').val()       || 'no_start';
    var end_date     = $('#end_date').val()         || 'no_end';
    
    scion.create.table(
        'purchase_orders_table',
        module_url + '/get/' + status + '/' + project_id + '/' + start_date + '/' + end_date + '/' + filter_po_no,
        [
            { data: "DT_RowIndex", title:`ORDER LIST <span class='command'><button class="btn btn-sm btn-primary" onclick="editPO()">EDIT PURCHASE ORDER</button></span>`, 
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
        ], 'Brtip', false, false
    );

    selected_frame = 1;

    setTimeout(() => {
        selectList(1);
    }, 1000);

    $('.grid-footer button').addClass('btn-hide');
    $('.grid-footer button.for-'+status).removeClass('btn-hide');

    $('#printPO .po-title').text(status === "PARTIALLY_DELIVERED" || status === "COMPLETED"?"Delivery Receipt":"Purchase Order");

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
    var userConfirmed = confirm("Are you sure you want to move this Purchase Order?");

    if (userConfirmed) {
        $.post('/purchasing/purchase_orders/set-status/' + id, data).done((i, v) => {
            generateTable(status);
        });
    } else {
    }
}
