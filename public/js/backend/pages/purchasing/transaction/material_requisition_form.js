$(function() {
    modal_content = 'material_requisition_form';
    module_url = '/purchasing/material_requisition_forms';
    module_type = 'custom';
    page_title = "Materials Requisition Form";

    scion.centralized_button(false, true, true, true);
    scion.create.table(
        'material_requisition_form_table',
        module_url + '/get',
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/purchasing/material_requisition_forms/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            { data: "DT_RowIndex", title: "#" },
            { data: "date", title: "Date", render: function(data) { return data || '-'; } },
            { data: "mrf_no", title: "MRF No", render: function(data) { return data || '-'; } },
            { data: "project_name", title: "Project", render: function(data) { return data || '-'; } },
            { data: "location", title: "Location", render: function(data) { return data || '-'; } },
            { data: "requested_by_name", title: "Requested By", render: function(data) { return data || '-'; } },
            { data: "noted_by_name", title: "Noted By", render: function(data) { return data || '-'; } },
            { data: "approved_by_name", title: "Approved By", render: function(data) { return data || '-'; } },
            { data: "details_count", title: "Items", render: function(data) { return data || 0; } },
        ], 'Bfrtip', []
    );

    $(document).on('click', '#add_mrf_detail_row_btn', function() {
        addMrfDetailRow();
    });

    $(document).on('click', '.remove-mrf-detail-row', function() {
        $(this).closest('tr').remove();
        if ($('#mrf_details_body tr').length === 0) {
            addMrfDetailRow();
        }
    });
});

function addMrfDetailRow(detail) {
    var d = detail || {};
    var row = `
        <tr>
            <td><input type="number" step="0.01" class="form-control form-control-sm mrf-quantity" value="${d.quantity || ''}"></td>
            <td><input type="text" class="form-control form-control-sm mrf-unit" value="${d.unit || ''}"></td>
            <td><input type="text" class="form-control form-control-sm mrf-particulars" value="${d.particulars || ''}"></td>
            <td><input type="text" class="form-control form-control-sm mrf-location-used" value="${d.location_to_be_used || ''}"></td>
            <td><input type="date" class="form-control form-control-sm mrf-date-required" value="${d.date_required || ''}"></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm mrf-approved-qty" value="${d.approved_quantity || ''}"></td>
            <td><input type="text" class="form-control form-control-sm mrf-remarks" value="${d.remarks || ''}"></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-danger remove-mrf-detail-row"><i class="fas fa-times"></i></button></td>
        </tr>
    `;
    $('#mrf_details_body').append(row);
}

function collectMrfDetails() {
    var details = [];
    $('#mrf_details_body tr').each(function() {
        details.push({
            quantity: $(this).find('.mrf-quantity').val(),
            unit: $(this).find('.mrf-unit').val(),
            particulars: $(this).find('.mrf-particulars').val(),
            location_to_be_used: $(this).find('.mrf-location-used').val(),
            date_required: $(this).find('.mrf-date-required').val(),
            approved_quantity: $(this).find('.mrf-approved-qty').val(),
            remarks: $(this).find('.mrf-remarks').val(),
        });
    });
    return details;
}

function success() {
    $('#material_requisition_form_table').DataTable().draw();
    scion.create.sc_modal('material_requisition_form_form').hide('all', modalHideFunction);
}

function error() {}

function delete_success() {
    $('#material_requisition_form_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    return {
        _token: _token,
        date: $('#date').val(),
        mrf_no: $('#mrf_no').val(),
        project_id: $('#project_id').val(),
        location: $('#location').val(),
        requested_by: $('#requested_by').val(),
        noted_by: $('#noted_by').val(),
        approved_by: $('#approved_by').val(),
        details: collectMrfDetails()
    };
}

function generateDeleteItems() {}

function modalShowFunction() {
    scion.centralized_button(true, false, true, true);
    if ($('#mrf_details_body tr').length === 0) {
        addMrfDetailRow();
    }
}

function modalHideFunction() {
    $('#mrf_details_body').empty();
    scion.centralized_button(false, true, true, true);
}

function editShow() {
    if (!store_record || !store_record.materials_requisition_forms) return;

    var record = store_record.materials_requisition_forms;
    $('#date').val(record.date || '');
    $('#mrf_no').val(record.mrf_no || '');
    $('#project_id').val(record.project_id || '');
    $('#location').val(record.location || '');
    $('#requested_by').val(record.requested_by || '');
    $('#noted_by').val(record.noted_by || '');
    $('#approved_by').val(record.approved_by || '');

    $('#mrf_details_body').empty();
    if (record.details && record.details.length > 0) {
        record.details.forEach(function(detail) {
            addMrfDetailRow(detail);
        });
    } else {
        addMrfDetailRow();
    }
}

