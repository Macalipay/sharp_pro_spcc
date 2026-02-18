var currentEditingJournalId = null;
var currentEditingLineIds = [];

$(function() {
    let activeStatus = 'ALL';

    modal_content = 'journal_entries';
    module_content = 'journal_entries';
    module_url = '/accounting/journal_entries';
    module_type = 'custom';
    page_title = "Manual Journal Entry";
    record_id;

    scion.centralized_button(false, true, true, true);
    resetManualJournalLines();
    initializeJournalAttachmentInput();
    initializeManualJournalDateInputs();

    scion.create.table(
        'journal_entry_table',
        module_url + '/get',
        [
            { data: "description", title: "Description", render: function(data) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            { data: "entry_date", title: "Date", render: function(data) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            { data: "total_debit", title: "Total Debit", render: function(data) {
                    return '<span class="expandable" title="' + formatCurrency(data) + '">' + formatCurrency(data) + '</span>';
                }
            },
            { data: "total_credit", title: "Total Credit", render: function(data) {
                    return '<span class="expandable" title="' + formatCurrency(data) + '">' + formatCurrency(data) + '</span>';
                }
            },
            { data: "status", title: "StatusFilter", visible: false, searchable: true }
        ], 'Bfrtip', []
    );

    setTimeout(function() {
        const table = $('#journal_entry_table').DataTable();
        applyJournalStatusFilter(table, activeStatus);
    }, 200);

    $(document).on('click', '.journal-status-tab', function(e) {
        e.preventDefault();
        $('.journal-status-tab').removeClass('active');
        $(this).addClass('active');
        activeStatus = ($(this).data('status') || 'ALL').toString().toUpperCase();
        const table = $('#journal_entry_table').DataTable();
        applyJournalStatusFilter(table, activeStatus);
    });

    $('#journal_entry_table tbody').on('click', 'tr', function(e) {
        if ($(e.target).closest('a,button,input,label').length) {
            return;
        }

        const table = $('#journal_entry_table').DataTable();
        const rowData = table.row(this).data();
        if (!rowData || !rowData.id) {
            return;
        }

        const status = (rowData.status || '').toString().toUpperCase();
        if (status === 'DRAFT') {
            openDraftJournalForEdit(rowData);
            return;
        }

        openJournalEntryPresentedModal(rowData);
    });

    function openDraftJournalForEdit(rowData) {
        const id = parseInt(rowData.id, 10);
        if (!id) return;

        currentEditingJournalId = id;
        currentEditingLineIds = [];
        $('#journal_status').val('DRAFT');

        $.when(
            $.get('/accounting/journal_entries/edit/' + id),
            $.get('/accounting/journal_entry_line_fields/get_details/' + id)
        ).done(function(headerRes, linesRes) {
            const headerPayload = headerRes && headerRes[0] ? headerRes[0] : {};
            const header = headerPayload.journal_entries || {};
            const linePayload = linesRes && linesRes[0] ? linesRes[0] : {};
            const lines = linePayload.data || [];

            $('#description').val(header.description || '');
            $('#entry_date').val(convertYmdToMdy(header.entry_date || ''));
            $('#auto_reversing_date').val(convertYmdToMdy(header.auto_reversing_date || ''));
            resetJournalAttachmentField();

            $('#manual_journal_lines_body').html('');
            if (!lines.length) {
                addManualJournalLine();
            } else {
                lines.forEach(function(line) {
                    addManualJournalLine({
                        id: line.id,
                        description: line.description || '',
                        chart_of_account_id: line.chart_of_account_id || '',
                        tax_rate: line.tax_rate || 'VAT',
                        debit_amount: parseFloat(line.debit_amount) || 0,
                        credit_amount: parseFloat(line.credit_amount) || 0,
                    });
                    currentEditingLineIds.push(line.id);
                });
            }
            recomputeManualJournalSubtotals();

            scion.create.sc_modal('journal_entries_form', 'EDIT MANUAL JOURNAL ENTRY').show(modalShowFunction);
        }).fail(function() {
            currentEditingJournalId = null;
            currentEditingLineIds = [];
            toastr.error('Unable to load draft journal for editing.');
        });
    }
});

function applyJournalStatusFilter(table, status) {
    if (!table) return;

    if ((status || '').toUpperCase() === 'ALL') {
        table.column(4).search('', true, false).draw();
        return;
    }

    const escaped = $.fn.dataTable.util.escapeRegex(status || '');
    table.column(4).search(escaped ? '^' + escaped + '$' : '', true, false).draw();
}

function openJournalEntryPresentedModal(rowData) {
    const entryId = rowData.id;
    const entryDate = rowData.entry_date || '-';
    const status = (rowData.status || 'DRAFT').toString().toUpperCase();

    $('#journal_entry_details_title').text('Manual Journal Entry');
    $('#journal_entry_details_status').text('Status: ' + status);
    $('#journal_entry_details_date').text('Date: ' + entryDate);
    $('#journal_entry_details_lines').html('<tr><td colspan="5" style="text-align:center;">Loading...</td></tr>');
    $('#journal_entry_details_subtotal_debit').text('0.00');
    $('#journal_entry_details_subtotal_credit').text('0.00');
    $('#journal_entry_details_total_debit').text('0.00');
    $('#journal_entry_details_total_credit').text('0.00');

    scion.create.sc_modal('journal_entry_details', 'JOURNAL ENTRY DETAILS').show();

    $.get('/accounting/journal_entry_line_fields/get_details/' + entryId)
        .done(function(response) {
            const rows = response && response.data ? response.data : [];
            if (!rows.length) {
                $('#journal_entry_details_lines').html('<tr><td colspan="5" style="text-align:center;">No line entries found.</td></tr>');
                return;
            }

            let html = '';
            let totalDebit = 0;
            let totalCredit = 0;

            rows.forEach(function(line) {
                const description = line.description || '-';
                const accountName = line.chart_of_account && line.chart_of_account.account_name
                    ? line.chart_of_account.account_name + ' (' + (line.chart_of_account.account_number || '') + ')'
                    : '-';
                const taxRate = line.tax_rate || '-';
                const debit = parseFloat(line.debit_amount) || 0;
                const credit = parseFloat(line.credit_amount) || 0;

                totalDebit += debit;
                totalCredit += credit;

                html += `
                    <tr>
                        <td>${description}</td>
                        <td>${accountName}</td>
                        <td>${taxRate}</td>
                        <td style="text-align: right;">${formatCurrency(debit)}</td>
                        <td style="text-align: right;">${formatCurrency(credit)}</td>
                    </tr>
                `;
            });

            $('#journal_entry_details_lines').html(html);
            $('#journal_entry_details_subtotal_debit').text(formatCurrency(totalDebit));
            $('#journal_entry_details_subtotal_credit').text(formatCurrency(totalCredit));
            $('#journal_entry_details_total_debit').text(formatCurrency(totalDebit));
            $('#journal_entry_details_total_credit').text(formatCurrency(totalCredit));
        })
        .fail(function() {
            $('#journal_entry_details_lines').html('<tr><td colspan="5" style="text-align:center;">Failed to load journal entry details.</td></tr>');
            toastr.error('Unable to load selected journal entry.');
        });
}

function success() {
    switch(actions) {
        case 'save':
            switch(module_content) {
                case 'journal_entries':
                    if ($.fn.DataTable.isDataTable('#journal_entry_table')) {
                        $('#journal_entry_table').DataTable().draw();
                    }
                    scion.create.sc_modal('journal_entries_form').hide('all', modalHideFunction);
                    break;

                case 'journal_entry_line_fields':
                    if ($.fn.DataTable.isDataTable('#journal_entry_table')) {
                        $('#journal_entry_table').DataTable().draw();
                    }
                    $('#journal_detail_table').DataTable().draw();
                    scion.create.sc_modal('journal_details_form').hide(modalHideFunction);

                    break;
            }
            break;
        case 'update':
            switch(module_content) {
                case 'journal_entries':
                    if ($.fn.DataTable.isDataTable('#journal_entry_table')) {
                        $('#journal_entry_table').DataTable().draw();
                    }
                    scion.create.sc_modal('journal_entries_form').hide('all', modalHideFunction);
                    break;
            }
            break;
    }
}

function journal_detail(id, description, entry_date) {
    modal_content = 'journal_details';
    module_content = 'journal_entry_line_fields';
    module_url = '/accounting/journal_entry_line_fields';
    module_type = 'custom';
    record_id = id;
    actions = 'save';

    $('#manual_description').val(decodeURIComponent(description || ''));
    $('#manual_entry_date').val(entry_date);
    $('#record_id').val(record_id);


    scion.create.sc_modal("journal_entry_line_fields_form", 'MANUAL JOURNAL').show(modalShowFunction);

    if ($.fn.DataTable.isDataTable('#journal_detail_table')) {
        $('#journal_detail_table').DataTable().clear().destroy();
    }

    scion.create.table(
        'journal_detail_table',
        module_url + '/get_details/' + record_id,
        [
        { data: "description", title: "Description", render: function(data, type, row, meta) {
                return '<span class="expandable" title="' + data + '">' + data + '</span>';
            }
        },
        { data: "chart_of_account", title: "Description", render: function(data, type, row, meta) {
                return '<span class="expandable" title="' + row.chart_of_account.account_name + '">' + row.chart_of_account.account_name + ' (' + row.chart_of_account.account_number + ')'+ '</span>';
            }
        },
        { data: "tax_rate", title: "Description", render: function(data, type, row, meta) {
                return '<span class="expandable" title="' + data + '">' + data + '</span>';
            }
        },
        { data: "debit_amount", title: "Debit AUD", render: function(data, type, row, meta) {
                return '<span class="expandable" title="' + formatCurrency(data) + '">' + formatCurrency(data) + '</span>';
            },
        },
        { data: "credit_amount", title: "Credit AUD", render: function(data, type, row, meta) {
                return '<span class="expandable" title="' + formatCurrency(data) + '">' + formatCurrency(data) + '</span>';
            },
        },
        ], 'ftip', [], true, false
    );

    scion.centralized_button(false, true, true, true);

}

function error() {}

function postJournal(){
    $.get('/accounting/journal_entries/status/' + record_id, function(data) {

    });
}

function delete_success() {
    if ($.fn.DataTable.isDataTable('#journal_entry_table')) {
        $('#journal_entry_table').DataTable().draw();
    }
}

function delete_error() {}

function generateData() {

    switch(module_content) {
        case 'journal_entries':
            form_data = {
                _token: _token,
                entry_date: $('#entry_date').val(),
                description: $('#description').val(),
                auto_reversing_date: $('#auto_reversing_date').val(),
                status: $('#journal_status').val() || 'DRAFT',
            };
            break;

        case 'journal_entry_line_fields':
            form_data = {
                _token: _token,
                journal_entry_id: $('#record_id').val(),
                chart_of_account_id: $('#chart_of_account_id').val(),
                description: $('#detail_description').val(),
                debit_amount: $('#debit_amount').val(),
                credit_amount: $('#credit_amount').val(),
                tax_rate: $('#tax_rate').val(),
            };
            break;
    }

    return form_data;
}

function generateDeleteItems(){}

function saveJournalEntryDraft() {
    saveManualJournalEntry('DRAFT', false);
}

function saveJournalEntryDraftAndAddAnother() {
    saveManualJournalEntry('DRAFT', true);
}

function postJournalEntry() {
    saveManualJournalEntry('POSTED', false);
}

function postJournalEntryAndAddAnother() {
    saveManualJournalEntry('POSTED', true);
}

function modalShowFunction() {
    if (module_content === 'journal_entries' && (!currentEditingJournalId || currentEditingJournalId === null)) {
        $('#journal_status').val('DRAFT');
        resetManualJournalLines();
    }
    scion.centralized_button(true, false, true, true);
}

function modalHideFunction() {
    scion.centralized_button(false, true, true, true);
    currentEditingJournalId = null;
    currentEditingLineIds = [];

    modal_content = 'journal_entries';
    module_url = '/accounting/journal_entries';
    module_type = 'custom';
    page_title = "Manual Journal Entry";
}

function resetManualJournalLines() {
    $('#manual_journal_lines_body').html('');
    addManualJournalLine();
    recomputeManualJournalSubtotals();
}

function addManualJournalLine(initialData) {
    const accountOptions = '<option value="__add_new__">+ Add New Account</option>' + ($('#chart_of_account_id').html() || '');
    const line = initialData || {};
    const description = line.description || '';
    const accountId = line.chart_of_account_id ? String(line.chart_of_account_id) : '';
    const taxRate = line.tax_rate || 'VAT';
    const debitAmount = (parseFloat(line.debit_amount) || 0).toFixed(2);
    const creditAmount = (parseFloat(line.credit_amount) || 0).toFixed(2);
    const lineId = line.id ? String(line.id) : '';
    const rowHtml = `
        <tr class="manual-journal-row" data-line-id="${lineId}">
            <td><input type="text" class="form-control form-control-sm mj-description" placeholder="Description" value="${$('<div/>').text(description).html()}"></td>
            <td>
                <select class="form-control form-control-sm mj-account">
                    <option value="">Select Account</option>
                    ${accountOptions}
                </select>
            </td>
            <td>
                <select class="form-control form-control-sm mj-tax-rate">
                    <option value="VAT">VAT</option>
                    <option value="NON-VAT">NON-VAT</option>
                </select>
            </td>
            <td><input type="number" min="0" step="0.01" class="form-control form-control-sm mj-debit" value="${debitAmount}"></td>
            <td><input type="number" min="0" step="0.01" class="form-control form-control-sm mj-credit" value="${creditAmount}"></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeManualJournalLine(this)"><i class="fas fa-times"></i></button></td>
        </tr>
    `;

    const $row = $(rowHtml);
    $('#manual_journal_lines_body').append($row);
    if (accountId) {
        $row.find('.mj-account').val(accountId);
    }
    if (taxRate) {
        $row.find('.mj-tax-rate').val(taxRate);
    }
}

function openAddChartOfAccountModal() {
    $('#quickChartOfAccountForm')[0].reset();
    $('.error-message').remove();
    updateQuickAccountTypeReportImpact();
    scion.create.sc_modal('quick_chart_of_accounts_form', 'ADD NEW CHART OF ACCOUNT').show();
}

function saveQuickChartOfAccount() {
    $('.error-message').remove();

    const payload = {
        _token: _token,
        account_number: ($('#quick_account_number').val() || '').trim(),
        account_name: ($('#quick_account_name').val() || '').trim(),
        account_type: $('#quick_account_type').val(),
        description: ($('#quick_account_description').val() || '').trim(),
        tax: $('#quick_tax').val(),
        allow_for_payments: $('#quick_allow_for_payments').is(':checked') ? 1 : 0
    };

    if (!payload.account_number || !payload.account_name || !payload.account_type) {
        toastr.error('Please complete required Chart of Account fields.');
        return;
    }

    $.post('/accounting/chart_of_accounts/save', payload)
        .done(function (response) {
            const newId = response && response.data ? response.data.id : null;
            refreshManualJournalAccountOptions(newId, window.__manualJournalPendingAccountSelect || null);
            scion.create.sc_modal('quick_chart_of_accounts_form').hide();
            toastr.success('Chart of Account added successfully.');
            window.__manualJournalPendingAccountSelect = null;
        })
        .fail(function (xhr) {
            if (xhr && xhr.responseJSON && xhr.responseJSON.errors) {
                Object.keys(xhr.responseJSON.errors).forEach(function (field) {
                    const message = xhr.responseJSON.errors[field][0];
                    $('.quick_' + field).append('<span class="error-message">' + message + '</span>');
                });
                const firstKey = Object.keys(xhr.responseJSON.errors)[0];
                if (firstKey) toastr.error(xhr.responseJSON.errors[firstKey][0]);
                return;
            }
            toastr.error('Unable to add Chart of Account.');
            window.__manualJournalPendingAccountSelect = null;
        });
}

function updateQuickAccountTypeReportImpact() {
    const statementHtml = `
        <div class="ri-grid">
            <div class="ri-col">
                <div class="ri-box">
                    <div class="ri-title"><strong>PROFIT AND LOSS</strong></div>
                    <div class="ri-section">INCOME</div>
                    <div class="ri-line">Revenue</div>
                    <div class="ri-line">Sales</div>
                    <div class="ri-section">LESS: COST OF SALES</div>
                    <div class="ri-line">Direct Materials</div>
                    <div class="ri-total">GROSS PROFIT</div>
                    <div class="ri-section">PLUS: OTHER INCOME</div>
                    <div class="ri-line">Other Income</div>
                    <div class="ri-section">LESS: EXPENSES</div>
                    <div class="ri-line">Expenses</div>
                    <div class="ri-line">Depreciation</div>
                    <div class="ri-line">Overheads</div>
                    <div class="ri-total">NET PROFIT</div>
                </div>
            </div>
            <div class="ri-col">
                <div class="ri-box">
                    <div class="ri-title"><strong>BALANCE SHEET</strong></div>
                    <div class="ri-section">CURRENT ASSET</div>
                    <div class="ri-line">Current Assets</div>
                    <div class="ri-line">Inventory</div>
                    <div class="ri-line">Prepayments</div>
                    <div class="ri-section">PLUS: BANK</div>
                    <div class="ri-line">Bank Accounts</div>
                    <div class="ri-section">PLUS: FIXED ASSETS</div>
                    <div class="ri-section">PLUS: NON-CURRENT ASSETS</div>
                    <div class="ri-line">Non Current Assets</div>
                    <div class="ri-total">TOTAL ASSETS</div>
                    <div class="ri-section">LESS: CURRENT LIABILITIES</div>
                    <div class="ri-line">Current Liabilities</div>
                    <div class="ri-section">LESS: NON-CURRENT LIABILITIES</div>
                    <div class="ri-line">Liabilities</div>
                    <div class="ri-line">Non-Current Liabilities</div>
                    <div class="ri-total">NET ASSETS</div>
                    <div class="ri-section">EQUITY</div>
                    <div class="ri-line">Equity</div>
                    <div class="ri-section">PLUS: NET PROFIT</div>
                    <div class="ri-total">TOTAL EQUITY</div>
                </div>
            </div>
        </div>
    `;

    $('#quick_report_impact_list').html(statementHtml);
}

function refreshManualJournalAccountOptions(selectId, preferredSelectElement) {
    $.get('/accounting/chart_of_accounts/get?for_manual_journal=1')
        .done(function (response) {
            const rows = response && response.data ? response.data : [];
            let options = '<option value="">Select Account</option>';

            rows.forEach(function (row) {
                options += `<option value="${row.id}">${row.account_name} (${row.account_number})</option>`;
            });

            $('#chart_of_account_id').html(options);
            const optionsWithAddNew = '<option value="__add_new__">+ Add New Account</option>' + options;
            $('.mj-account').each(function () {
                const currentVal = $(this).val();
                $(this).html(optionsWithAddNew);
                if (currentVal && currentVal !== '__add_new__') {
                    $(this).val(currentVal);
                }
            });

            if (selectId) {
                const target = preferredSelectElement ? $(preferredSelectElement) : $('.mj-account').last();
                if (target && target.length) {
                    target.val(String(selectId));
                }
            }
        })
        .fail(function () {
            toastr.error('Unable to refresh Chart of Account list.');
        });
}

$(document).on('change', '.mj-account', function () {
    if ($(this).val() !== '__add_new__') {
        return;
    }

    window.__manualJournalPendingAccountSelect = this;
    openAddChartOfAccountModal();
});

$(document).on('change', '#quick_account_type', function () {
    updateQuickAccountTypeReportImpact();
});

function removeManualJournalLine(button) {
    const rowCount = $('#manual_journal_lines_body .manual-journal-row').length;
    if (rowCount <= 1) {
        toastr.error('At least one journal line is required.');
        return;
    }

    $(button).closest('tr').remove();
    recomputeManualJournalSubtotals();
}

$(document).on('input', '.mj-debit, .mj-credit', function() {
    recomputeManualJournalSubtotals();
});

function recomputeManualJournalSubtotals() {
    let totalDebit = 0;
    let totalCredit = 0;

    $('#manual_journal_lines_body .manual-journal-row').each(function() {
        totalDebit += parseFloat($(this).find('.mj-debit').val()) || 0;
        totalCredit += parseFloat($(this).find('.mj-credit').val()) || 0;
    });

    $('#manual_subtotal_debit').text(formatCurrency(totalDebit));
    $('#manual_subtotal_credit').text(formatCurrency(totalCredit));
}

function getManualJournalLines() {
    const lines = [];
    let hasError = false;

    $('#manual_journal_lines_body .manual-journal-row').each(function(index) {
        const description = ($(this).find('.mj-description').val() || '').trim();
        const accountId = $(this).find('.mj-account').val();
        const taxRate = $(this).find('.mj-tax-rate').val();
        const debit = parseFloat($(this).find('.mj-debit').val()) || 0;
        const credit = parseFloat($(this).find('.mj-credit').val()) || 0;

        if (!description || !accountId) {
            toastr.error(`Line ${index + 1}: Description and Chart of Account are required.`);
            hasError = true;
            return false;
        }

        if (debit <= 0 && credit <= 0) {
            toastr.error(`Line ${index + 1}: Debit or Credit must be greater than zero.`);
            hasError = true;
            return false;
        }

        lines.push({
            description: description,
            chart_of_account_id: accountId,
            tax_rate: taxRate || 'VAT',
            debit_amount: debit,
            credit_amount: credit,
        });
    });

    if (hasError) {
        return null;
    }

    return lines;
}

function saveManualJournalEntry(targetStatus, keepOpen) {
    const entryDateInput = ($('#entry_date').val() || '').trim();
    const description = ($('#description').val() || '').trim();
    const autoReversingDateInput = ($('#auto_reversing_date').val() || '').trim();
    const supportingDocData = $('#journal_supporting_doc_data').val();
    const supportingDocName = $('#journal_supporting_doc_name').val();
    const supportingDocMime = $('#journal_supporting_doc_mime').val();
    const lines = getManualJournalLines();

    if (!entryDateInput) {
        toastr.error('Date is required.');
        return;
    }
    if (!isValidMdyDate(entryDateInput)) {
        toastr.error('Date must be in MM-DD-YYYY format.');
        return;
    }
    if (autoReversingDateInput && !isValidMdyDate(autoReversingDateInput)) {
        toastr.error('Auto Reversing Date must be in MM-DD-YYYY format.');
        return;
    }

    const entryDate = convertMdyToYmd(entryDateInput);
    const autoReversingDate = autoReversingDateInput ? convertMdyToYmd(autoReversingDateInput) : '';

    if (!description) {
        toastr.error('Journal Entry Description is required.');
        return;
    }

    if (!lines || lines.length === 0) {
        toastr.error('At least one journal entry line is required.');
        return;
    }

    const totalDebit = lines.reduce((sum, line) => sum + (parseFloat(line.debit_amount) || 0), 0);
    const totalCredit = lines.reduce((sum, line) => sum + (parseFloat(line.credit_amount) || 0), 0);

    if (Math.abs(totalDebit - totalCredit) > 0.0001) {
        toastr.error('Subtotal validation failed: Debit must be equal to Credit.');
        return;
    }

    const saveLinesForJournal = function(journalEntryId) {
        const requests = lines.map(function(line) {
            return $.post('/accounting/journal_entry_line_fields/save', {
                _token: _token,
                journal_entry_id: journalEntryId,
                chart_of_account_id: line.chart_of_account_id,
                description: line.description,
                debit_amount: line.debit_amount,
                credit_amount: line.credit_amount,
                tax_rate: line.tax_rate
            });
        });

        $.when.apply($, requests).done(function() {
            toastr.success(targetStatus === 'DRAFT' ? 'Journal entry saved as draft.' : 'Journal entry posted successfully.');
            if ($.fn.DataTable.isDataTable('#journal_entry_table')) {
                $('#journal_entry_table').DataTable().ajax.reload(null, false);
            }
            resetManualJournalLines();
            $('#journalForm')[0].reset();
            resetJournalAttachmentField();
            currentEditingJournalId = null;
            currentEditingLineIds = [];

            if (keepOpen) {
                $('#journal_status').val('DRAFT');
            } else {
                scion.create.sc_modal('journal_entries_form').hide('all', modalHideFunction);
            }
        }).fail(function(xhr) {
            if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                toastr.error(xhr.responseJSON.message);
                return;
            }
            toastr.error('Header saved, but one or more journal lines failed to save.');
        });
    };

    const savePayload = {
        _token: _token,
        entry_date: entryDate,
        description: description,
        auto_reversing_date: autoReversingDate,
        total_debit: 0,
        total_credit: 0,
        status: targetStatus,
        supporting_doc_data: supportingDocData,
        supporting_doc_name: supportingDocName,
        supporting_doc_mime: supportingDocMime
    };

    const upsertHeaderRequest = currentEditingJournalId
        ? $.post('/accounting/journal_entries/update/' + currentEditingJournalId, savePayload)
        : $.post('/accounting/journal_entries/save', savePayload);

    upsertHeaderRequest.done(function(response) {
        const journalEntryId = currentEditingJournalId || (response && response.last_record ? response.last_record.id : null);
        if (!journalEntryId) {
            toastr.error('Unable to save journal entry header.');
            return;
        }

        const removeOldLines = currentEditingJournalId && currentEditingLineIds.length
            ? $.post('/accounting/journal_entry_line_fields/destroy', { _token: _token, data: currentEditingLineIds })
            : $.Deferred().resolve().promise();

        removeOldLines.done(function() {
            saveLinesForJournal(journalEntryId);
        }).fail(function() {
            toastr.error('Unable to refresh existing journal lines.');
        });
    }).fail(function(xhr) {
        if (xhr && xhr.responseJSON && xhr.responseJSON.errors) {
            const firstKey = Object.keys(xhr.responseJSON.errors)[0];
            if (firstKey) {
                toastr.error(xhr.responseJSON.errors[firstKey][0]);
                return;
            }
        }
        toastr.error('Failed to save manual journal entry.');
    });
}

function initializeManualJournalDateInputs() {
    $(document).on('input', '#entry_date, #auto_reversing_date', function () {
        this.value = formatMdyInput(this.value);
    });
}

function formatMdyInput(value) {
    const digits = (value || '').replace(/\D/g, '').slice(0, 8);
    if (digits.length <= 2) return digits;
    if (digits.length <= 4) return digits.slice(0, 2) + '-' + digits.slice(2);
    return digits.slice(0, 2) + '-' + digits.slice(2, 4) + '-' + digits.slice(4);
}

function isValidMdyDate(value) {
    if (!/^\d{2}-\d{2}-\d{4}$/.test(value || '')) {
        return false;
    }

    const parts = value.split('-');
    const month = parseInt(parts[0], 10);
    const day = parseInt(parts[1], 10);
    const year = parseInt(parts[2], 10);

    const date = new Date(year, month - 1, day);
    return (
        date.getFullYear() === year &&
        date.getMonth() === month - 1 &&
        date.getDate() === day
    );
}

function convertMdyToYmd(value) {
    const parts = (value || '').split('-');
    if (parts.length !== 3) {
        return value;
    }
    return `${parts[2]}-${parts[0]}-${parts[1]}`;
}

function convertYmdToMdy(value) {
    const raw = (value || '').trim();
    if (!raw || !/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
        return raw;
    }
    const parts = raw.split('-');
    return `${parts[1]}-${parts[2]}-${parts[0]}`;
}

function initializeJournalAttachmentInput() {
    $('#journal_supporting_doc').on('change', function () {
        const file = this.files && this.files[0] ? this.files[0] : null;
        if (!file) {
            resetJournalAttachmentField();
            return;
        }

        const maxSizeBytes = 25 * 1024 * 1024;
        if (file.size > maxSizeBytes) {
            toastr.error('Supporting document must not exceed 25MB.');
            resetJournalAttachmentField();
            return;
        }

        const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        const extension = (file.name.split('.').pop() || '').toLowerCase();
        if (!allowedExtensions.includes(extension)) {
            toastr.error('Unsupported file type. Allowed: PDF, JPEG, PNG, DOC, DOCX.');
            resetJournalAttachmentField();
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            $('#journal_supporting_doc_data').val(e.target.result || '');
            $('#journal_supporting_doc_name').val(file.name || '');
            $('#journal_supporting_doc_mime').val(file.type || '');
            $('#journal_supporting_doc_btn').addClass('attached');
            toastr.success('Supporting document attached.');
        };
        reader.onerror = function () {
            toastr.error('Unable to read supporting document.');
            resetJournalAttachmentField();
        };

        reader.readAsDataURL(file);
    });
}

function resetJournalAttachmentField() {
    $('#journal_supporting_doc').val('');
    $('#journal_supporting_doc_data').val('');
    $('#journal_supporting_doc_name').val('');
    $('#journal_supporting_doc_mime').val('');
    $('#journal_supporting_doc_btn').removeClass('attached');
}

function openJournalSupportingDocPicker() {
    $('#journal_supporting_doc').trigger('click');
}


function printJournalReport() {

    selected_print = 'journal_report_print';

    var divContents = document.getElementById(selected_print).innerHTML;
        var a = window.open('', '', 'height=800, width=1200');
        a.document.write('<html>');
        a.document.write('<head>');
        a.document.write('<link href="/css/custom.css" rel="stylesheet"></link>');
        a.document.write('<link href="/css/custom/accounting_reports/journal_entries.css" rel="stylesheet"></link>');
        a.document.write('<link href="/css/custom/'+modal_content+'.css" rel="stylesheet"></link>');
        a.document.write('<link href="/backend/css/modern.css" rel="stylesheet"></link>');
        a.document.write('<link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">');
        a.document.write('<link rel="preconnect" href="https://fonts.googleapis.com">');
        a.document.write('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>');
        a.document.write('<link href="https://fonts.googleapis.com/css2?family=Archivo+Black&display=swap" rel="stylesheet"></link>');
        a.document.write('</head>');
        a.document.write('<body id="print_canvas">');
        a.document.write(divContents);
        a.document.write('</body></html>');
        a.document.close();

        setTimeout(function() {
            a.print();
        }, 2000);
}

function printJournalDetailsReport() {

    selected_print = 'journal_details_report_print';

    var divContents = document.getElementById(selected_print).innerHTML;
        var a = window.open('', '', 'height=100%, width=100%');
        a.document.write('<html>');
        a.document.write('<head>');
        a.document.write('<link href="/css/custom.css" rel="stylesheet"></link>');
        a.document.write('<link href="/css/custom/accounting_reports/journal_entries.css" rel="stylesheet"></link>');
        a.document.write('<link href="/css/custom/'+modal_content+'.css" rel="stylesheet"></link>');
        a.document.write('<link href="/backend/css/modern.css" rel="stylesheet"></link>');
        a.document.write('<link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">');
        a.document.write('<link rel="preconnect" href="https://fonts.googleapis.com">');
        a.document.write('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>');
        a.document.write('<link href="https://fonts.googleapis.com/css2?family=Archivo+Black&display=swap" rel="stylesheet"></link>');
        a.document.write('</head>');
        a.document.write('<body id="print_canvas">');
        a.document.write(divContents);
        a.document.write('</body></html>');
        a.document.close();

        setTimeout(function() {
            a.print();
        }, 2000);
}

function printJournalEntryDetails() {

    selected_print = 'journal_entry_details_print';

    var divContents = document.getElementById(selected_print).innerHTML;
        var a = window.open('', '', 'height=800, width=1200');
        a.document.write('<html>');
        a.document.write('<head>');
        a.document.write('<link href="/css/custom.css" rel="stylesheet"></link>');
        a.document.write('<link href="/css/custom/accounting_reports/journal_entries.css" rel="stylesheet"></link>');
        a.document.write('<link href="/css/custom/'+modal_content+'.css" rel="stylesheet"></link>');
        a.document.write('<link href="/backend/css/modern.css" rel="stylesheet"></link>');
        a.document.write('<link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">');
        a.document.write('<link rel="preconnect" href="https://fonts.googleapis.com">');
        a.document.write('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>');
        a.document.write('<link href="https://fonts.googleapis.com/css2?family=Archivo+Black&display=swap" rel="stylesheet"></link>');
        a.document.write('</head>');
        a.document.write('<body id="print_canvas">');
        a.document.write(divContents);
        a.document.write('</body></html>');
        a.document.close();

        setTimeout(function() {
            a.print();
        }, 2000);
}
