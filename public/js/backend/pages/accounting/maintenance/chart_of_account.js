$(function() {
    let activeCategory = 'ASSETS';

    modal_content = 'chart_of_accounts';
    module_url = '/accounting/chart_of_accounts';
    module_type = 'custom';
    page_title = "Chart Of Accounts";

    scion.centralized_button(false, true, true, true);
    scion.create.table(
        'account_table',
        module_url + '/get',
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/accounting/chart_of_accounts/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            {
                data: "account_type.account_type",
                title: "Account Type",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "account_number",
                title: "Account Code",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "account_name",
                title: "Name",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "description",
                title: "Description",
                render: function(data, type, row, meta) {
                    const value = data || '-';
                    return '<span class="expandable" title="' + value + '">' + value + '</span>';
                }
            },
            {
                data: "tax",
                title: "Tax",
                render: function(data, type, row, meta) {
                    const value = data || '-';
                    return '<span class="expandable" title="' + value + '">' + value + '</span>';
                }
            },
            {
                data: "allow_for_payments",
                title: "For Payments",
                render: function(data, type, row, meta) {
                    const value = parseInt(data, 10) === 1 ? 'YES' : 'NO';
                    return '<span class="expandable" title="' + value + '">' + value + '</span>';
                }
            },
            {
                data: "account_type.category",
                title: "Category",
                visible: false,
                searchable: true,
                render: function(data) {
                    return data || '';
                }
            }
        ], 'Bfrtip', []
    );

    setTimeout(function() {
        const table = $('#account_table').DataTable();
        applyCategoryFilter(table, activeCategory);
    }, 200);

    $(document).on('click', '.coa-category-tab', function(e) {
        e.preventDefault();
        $('.coa-category-tab').removeClass('active');
        $(this).addClass('active');

        activeCategory = ($(this).data('category') || '').toString().toUpperCase();
        const table = $('#account_table').DataTable();
        applyCategoryFilter(table, activeCategory);
    });
});

function applyCategoryFilter(table, category) {
    if (!table) {
        return;
    }

    if ((category || '').toUpperCase() === 'ALL') {
        table.column(7).search('', true, false).draw();
        return;
    }

    const escaped = $.fn.dataTable.util.escapeRegex(category || '');
    table.column(7).search(escaped ? '^' + escaped + '$' : '', true, false).draw();
}

function openAddAccountModal() {
    modal_content = 'chart_of_accounts';
    module_url = '/accounting/chart_of_accounts';
    module_type = 'custom';
    scion.record.new();
}

function goToBankAccountSetup() {
    window.location.href = '/payroll/company-profile';
}

function printChartOfAccountsPdf() {
    const table = $('#account_table').DataTable();
    if (!table) {
        toastr.error('Account table is not ready.');
        return;
    }

    const visibleRows = table.rows({ search: 'applied' }).data().toArray();
    if (!visibleRows.length) {
        toastr.error('No records to print.');
        return;
    }

    const activeCategory = ($('#coaCategoryTabs .coa-category-tab.active').data('category') || 'ALL').toString();
    const rowsHtml = visibleRows.map(function(row) {
        const accountType = row.account_type && row.account_type.account_type ? row.account_type.account_type : '-';
        const description = row.description || '-';
        const tax = row.tax || '-';
        const forPayments = parseInt(row.allow_for_payments, 10) === 1 ? 'YES' : 'NO';
        return `
            <tr>
                <td>${accountType}</td>
                <td>${row.account_number || '-'}</td>
                <td>${row.account_name || '-'}</td>
                <td>${description}</td>
                <td>${tax}</td>
                <td>${forPayments}</td>
            </tr>
        `;
    }).join('');

    const printWindow = window.open('', '', 'height=900,width=1300');
    if (!printWindow) {
        toastr.error('Unable to open print window. Please allow pop-ups.');
        return;
    }

    printWindow.document.write(`
        <html>
            <head>
                <title>Chart of Accounts</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 24px; }
                    h2 { margin: 0 0 8px; }
                    p { margin: 0 0 16px; color: #444; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #222; padding: 8px; font-size: 12px; text-align: left; }
                    th { background: #f1f1f1; }
                </style>
            </head>
            <body>
                <h2>Chart of Accounts</h2>
                <p>Category: ${activeCategory}</p>
                <table>
                    <thead>
                        <tr>
                            <th>Account Type</th>
                            <th>Account Code</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Tax</th>
                            <th>For Payments</th>
                        </tr>
                    </thead>
                    <tbody>${rowsHtml}</tbody>
                </table>
            </body>
        </html>
    `);
    printWindow.document.close();
    setTimeout(function() {
        printWindow.print();
    }, 400);
}

function success() {
    switch(actions) {
        case 'save':
            $('.coa-category-tab').removeClass('active');
            $('.coa-category-tab[data-category="ALL"]').addClass('active');
            break;
        case 'update':
            break;
    }
    const table = $('#account_table').DataTable();
    table.ajax.reload(function() {
        applyCategoryFilter(table, 'ALL');
    }, false);
    scion.create.sc_modal('chart_of_accounts_form').hide('all', modalHideFunction);
}

function error() {}

function delete_success() {
    const table = $('#account_table').DataTable();
    table.ajax.reload(null, false);
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token,
        account_number: ($('#account_number').val() || '').replace(/\D/g, ''),
        account_name: $('#account_name').val(),
        account_type: $('#account_type').val(),
        description: $('#description').val(),
        tax: $('#tax').val(),
        allow_for_payments: $('#allow_for_payments').is(':checked') ? 1 : 0,
    };

    return form_data;
}

function generateDeleteItems(){}

function saveAccountRecord() {
    $('.error-message').remove();

    const isUpdate = (typeof record_id !== 'undefined' && record_id !== null);
    const payload = generateData();
    const url = isUpdate
        ? '/accounting/chart_of_accounts/update/' + record_id
        : '/accounting/chart_of_accounts/save';

    $.post(url, payload)
        .done(function() {
            $('.coa-category-tab').removeClass('active');
            $('.coa-category-tab[data-category="ALL"]').addClass('active');

            const table = $('#account_table').DataTable();
            table.ajax.reload(function() {
                applyCategoryFilter(table, 'ALL');
            }, false);

            scion.create.sc_modal('chart_of_accounts_form').hide('all', modalHideFunction);
            toastr.success('Record Saved!');
        })
        .fail(function(response) {
            if (response && response.responseJSON && response.responseJSON.errors) {
                for (var field in response.responseJSON.errors) {
                    $('#' + field + "_error_message").remove();
                    $('div.' + field).append('<span id="' + field + '_error_message" class="error-message">' + response.responseJSON.errors[field][0] + '</span>');
                }
            } else {
                toastr.error('Failed to save account record.');
            }
        });
}

function modalShowFunction() {
    updateAccountTypeReportImpact();
    scion.centralized_button(true, false, true, true);
}

function modalHideFunction() {
    scion.centralized_button(false, true, true, true);
}

$(document).on('change', '#account_type', function() {
    updateAccountTypeReportImpact();
});

function updateAccountTypeReportImpact() {
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

    $('#report_impact_list').html(statementHtml);
}
