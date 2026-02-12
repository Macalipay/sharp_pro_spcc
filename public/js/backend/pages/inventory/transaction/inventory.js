$(function() {
    modal_content = 'inventory';
    module_content = 'inventory';
    module_url = '/inventory/inventory';
    module_type = 'custom';
    page_title = "Inventory";

    scion.centralized_button(false, true, true, true);
    scion.create.table(
        'inventory_table',
        module_url + '/get',
        [
            { 
                data: null, 
                className: 'details-control', 
                orderable: false, 
                defaultContent: '', 
                title: '' 
            },
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" title="EDIT" onclick="scion.record.edit('+"'/inventory/inventory/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                html += '<a href="#" class="align-middle edit" title="CONSUMPTION" onclick="inventory_transaction('+ row.id +')"><i class="fas fa-plus-circle"></i></a>';
                html += '<a href="#" class="align-middle edit" title="CONFLICT" onclick="inventory_damage(' + row.id +')"><i class="fas fa-minus"></i></a>';
                html += '<a href="#" class="align-middle edit" title="TRANSFER" onclick="inventory_transfer(' + row.id + ', \'' + (row.material ? row.material.item_name : '') + '\', \'' + (row.project ? row.project.project_name : '') + '\')"><i class="fas fa-exchange-alt"></i></a>';
                return html;
            }},
            { data: "material.item_code",title: "Code", render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            { data: "material.item_name",title: "Name", render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            { data: "project.project_name",title: "Project", render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            { data: "material.unit_of_measure",title: "Default Unit", render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            { data: "description",title: "Description", render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            { data: "critical_level",title: "Critical Level", render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            { data: "total_count",title: "Total Count", render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            { data: "quantity_stock",title: "Quantity Stock", render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            { data: "status",title: "Status", render: function(data, type, row, meta) {
                    if (data == 'GOOD') {
                        inventory_status = '<span class="expandable badge badge-success" title="' + data + '">' + data + '</span>';
                    } else if (data == 'CRITICAL LEVEL' || data == 'Critical Level') {
                        inventory_status = '<span class="expandable badge badge-warning" title="' + data + '">' + data + '</span>';
                    } else {
                        inventory_status = '<span class="expandable badge badge-danger" title="' + data + '">' + data + '</span>';
                    }

                    return inventory_status;
                }
            },
        ], 'Bfrtip', []
    );

    $('.create').click(function() {
        module_content = 'inventory';
        module_url = '/inventory/inventory';
        record_id = null;
        actions = 'save';
    });

    var _token = $('meta[name="csrf-token"]').attr('content');

    $('#inventory_table tbody').on('dblclick', 'tr', function () {
        var table = $('#inventory_table').DataTable();
        var row = table.row(this);
        var data = row.data();

        $.post('/inventory/inventory/audittrails/' + data.id, {_token: _token}, function(response) {
            $('#inventory-history-container').html(formatHistoryTable(response));
            $('html, body').animate({
                scrollTop: $("#inventory-history-container").offset().top
            }, 500);
        });
    });

});

function success() {
    switch(actions) {
        case 'save':
            switch(module_content) {
                case 'inventory':
                    $('#inventory_table').DataTable().draw();
                    scion.create.sc_modal('inventory_form').hide('all', modalHideFunction);
                    break;

                case 'inventory_damage':
                    $('#inventory_table').DataTable().draw();
                    scion.create.sc_modal('damage_form').hide('all', modalHideFunction);
                    break;

                case 'inventory_transaction':
                    $('#inventory_table').DataTable().draw();
                    scion.create.sc_modal('transaction_form').hide('all', modalHideFunction);
                    break;

                case 'inventory_transfer':
                    $('#inventory_table').DataTable().draw();
                    scion.create.sc_modal('inventory_transfer_form').hide('all', modalHideFunction);
                    break;
            }
            break;
        case 'update':
            switch(module_content) {
                case 'inventory':
                    $('#inventory_table').DataTable().draw();
                    scion.create.sc_modal('inventory_form').hide('all', modalHideFunction);
                    preparation_func();
                    break;
            }
            break;
    }
    $('#inventory_table').DataTable().draw();
    scion.create.sc_modal('inventory_form').hide('all', modalHideFunction)
    scion.create.sc_modal('damage_form').hide('all', modalHideFunction)

}

function error() {
    toastr.error('Record already exist.', 'Failed')
}
function delete_success() {
    $('#inventory_table').DataTable().draw();
}

function inventory_damage(id) {
    scion.create.sc_modal("damage_form", 'INVENTORY MOVEMENT ITEM').show(() => {
        $('#inventory_id').val(id);
        console.log(id)

        $('#damageform').off('submit').on('submit', function(e) {
            e.preventDefault();

            const formData = $(this).serialize();

            $.ajax({
                url: `/inventory/inventory/damage/save/${id}`,
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#inventory_table').DataTable().draw();
                    scion.create.sc_modal('damage_form').hide('all', modalHideFunction);
                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                }
            });
        });
    });
}
function generateCode() {
    const year = new Date().getFullYear(); // current year
    const random = Math.floor(10000 + Math.random() * 90000); // 5-digit random number
    return `CI-${year}-${random}`;
}

function inventory_transaction(id) {
    scion.create.sc_modal("transaction_form", 'INVENTORY TRANSACTION').show(modalShowFunction);
    $('#code').val(generateCode());
    setTimeout(() => {
        $('#transaction_form').find('[name="inventory_id"]').val(id);

        $('.btn-sv').off('click').on('click', function (e) {
            e.preventDefault();

            const form = $('#transaction_form');

            const formData = {
                _token: form.find('[name="_token"]').val(),
                inventory_id: form.find('[name="inventory_id"]').val(),
                quantity: form.find('[name="quantity"]').val(),
                code: form.find('[name="code"]').val(),
                requested_by: form.find('[name="requested_by"]').val(),
                issued_by: form.find('[name="issued_by"]').val(),
                approved_by: form.find('[name="approved_by"]').val(),
                date: form.find('[name="date"]').val(),
                remarks: form.find('[name="remarks"]').val()
            };

            $.post(`/inventory/inventory/transaction/save/${id}`, formData)
                .done(function () {
                    $('#inventory_table').DataTable().draw();
                    scion.create.sc_modal('transaction_form').hide('all', modalHideFunction);
                })
                .fail(function (xhr) {
                    const errors = xhr.responseJSON?.errors;
                    const message = errors
                        ? Object.entries(errors).map(([k, v]) => `${k}: ${v.join(', ')}`).join('\n')
                        : 'Something went wrong.';
                    alert("Validation Error:\n" + message);
                });
        });
    }, 100);
}


function inventory_transfer(id, item, project) {
    module_content = 'inventory_transfer';
    module_url = '/inventory/inventory/transfer';
    record_id = id;
    actions = 'save';
    scion.create.sc_modal("inventory_transfer_form", 'TRANSFER INVENTORY').show(modalShowFunction);

    $('#inventory_id').val(id);
    $('#from_project').val(project);
    $('#transfer_item_name').val(item);
}


$(document).on('click', '#transferBtn', function(e) {
    e.preventDefault();

    let inventoryId = $('#inventory_id').val();

    let formData = {
        inventory_id: inventoryId,
        transfer_item_name: $('#transfer_item_name').val(),
        from_project: $('#from_project').val(),
        to_project: $('#to_project').val(),
        unit_price: $('#unit_price').val(),
        total_amt: $('#total_amt').val(),
        transfer_quantity: $('#transfer_quantity').val(),
        transfer_date: $('#transfer_date').val(),
        transfer_remarks: $('#transfer_remarks').val(),
        _token: $('meta[name="csrf-token"]').attr('content')
    };

    $.ajax({
        url: `/inventory/inventory/transfer/save/${inventoryId}`,
        type: "POST",
        data: formData,
        success: function(response) {
            if (response.success) {
                alert('Transfer saved successfully!');
                $('#inventory_table').DataTable().draw();
                scion.create.sc_modal('inventory_transfer_form').hide('all', modalHideFunction);
                // scion.create.sc_modal('inventory_transfer_form').hide('all', modalHideFunction);
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr) {
            console.log(xhr.responseText);
            alert('Something went wrong. Please try again.');
        }
    });
});


function delete_error() {}

function generateData() {
    switch(module_content) {
        case 'inventory':
            form_data = {
                _token: _token,
                material_id: $('#material_id').val(),
                project_id: $('#project_id').val(),
                description: $('#description').val(),
                critical_level: $('#critical_level').val(),
            };
            break;

        case 'inventory_damage':
            form_data = {
                _token: _token,
                inventory_id: record_id,
                quantity: $('#quantity').val(),
                date: $('#date').val(),
                remarks: $('#remarks').val(),
            };
            break;
        case 'inventory_transaction':
            form_data = {
                _token: _token,
                inventory_id: record_id,
                quantity: $('#quantity').val(),
                code: $('#code').val(),
                requested_by: $('#requested_by').val(),
                issued_by: $('#issued_by').val(),
                approved_by: $('#approved_by').val(),
                date: $('#date').val(),
                remarks: $('#remarks').val(),
            };
            break;

        case 'inventory_transfer':
                form_data = {
                    _token: _token,
                    inventory_id: record_id,
                    to_project: $('#to_project').val(),
                    quantity: $('#transfer_quantity').val(),
                    date: $('#transfer_date').val(),
                    remarks: $('#transfer_remarks').val(),
                };
                break;
    }

    return form_data;
}

function generateDeleteItems(){}

function formatHistoryTable(data) {
    var itemCode = data.inventory.material ? data.inventory.material.item_code : '';
    var itemName = data.inventory.material ? data.inventory.material.item_name : '';
    var projectName = data.inventory.project ? data.inventory.project.project_name : '';
    var createdAt = data.inventory.created_at ? moment(data.inventory.created_at).format('ll') : '';
    var totalCount = data.inventory.total_count;
    var criticalLevel = data.inventory.critical_level;

    var html = `
        <div class="mt-4 mb-4" id="audit-trails-table-container">
            <h4 style="font-weight: 700; margin-bottom: 1rem;">Audit Trails</h4>
            <div style="margin-bottom: 0.5rem; color: #444;">
                <span><strong>Item:</strong> ${itemCode} - ${itemName}</span> &nbsp; | &nbsp;
                <span><strong>Project:</strong> ${projectName}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm" style="background: #fff; font-size: 13px;">
                    <thead>
                        <tr style="background: #084196; color: #fff;">
                            <th style="width: 180px;">Date & Time</th>
                            <th>Transaction Type</th>
                            <th>Ref#</th>
                            <th>QTY Processed</th>
                            <th>Unit Measurement</th>
                            <th>Beginning Balance</th>
                            <th>Ending Balance</th>
                            <th>Remarks</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

    if (data.events && Array.isArray(data.events)) {
        data.events.forEach(function(event) {
            let refNo = event.ref_no != null ? String(event.ref_no) : '';
            html += `
                <tr>
                    <td>${event.date ? moment(event.date).format('lll') : ''}</td>
                    <td>${event.type || ''}</td>
                    <td>${refNo}</td>
                    <td>${event.quantity_processed ?? ''}</td>
                    <td>${event.unit_price ?? ''}</td>
                    <td>${event.beginning_balance ?? ''}</td>
                    <td>${event.ending_balance ?? ''}</td>
                    <td>${event.remarks || ''}</td>
                    <td>${event.updated_by || ''}</td>
                </tr>
            `;
        });
    } else {
        html += `<tr><td colspan="7" class="text-center">No audit trail events found.</td></tr>`;
    }

    html += `
                    </tbody>
                </table>
            </div>
            <div class="text-muted small mt-2">
                Critical Level: <strong>${criticalLevel}</strong>
            </div>
        </div>
    `;

    return html;
}

function modalShowFunction() {
    scion.centralized_button(true, false, true, true);
}

function modalHideFunction() {
    scion.centralized_button(false, true, true, true);
}
