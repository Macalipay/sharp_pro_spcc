$(function() {
    modal_content = 'journal_entries';
    module_content = 'journal_entries';
    module_url = '/accounting/journal_entries';
    module_type = 'custom';
    page_title = "Journal Entry";
    record_id;

    scion.centralized_button(false, true, true, true);
    scion.create.table(
        'journal_entry_table',
        module_url + '/get',
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/accounting/journal_entries/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                html += '<a href="#" class="align-middle edit" onclick="journal_detail(\'' + row.id + '\', \'' + row.description + '\', \'' + row.entry_date + '\')"><i class="fas fa-th-list"></i></a>';
                return html;
            }},
            { data: "entry_date",title: "Entry Date",render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            { data: "reference_number", title: "Reference Number", render: function(data, type, row, meta) {
                    var formattedData = data ? data : '--';
                    return '<span class="expandable" title="' + formattedData + '">' + formattedData + '</span>';
                }
            },
            { data: "description", title: "Description", render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            { data: "total_debit", title: "Total Debit", render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + formatCurrency(data) + '">' + formatCurrency(data) + '</span>';
                }
            },
            { data: "total_credit", title: "Total Credit", render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + formatCurrency(data) + '">' + formatCurrency(data) + '</span>';
                }
            },
            { data: "status", title: "Status", render: function(data, type, row, meta) {
                    var badgeClass = '';

                    switch (data) {
                        case 'DRAFT':
                        return "<span class='text-primary' style='font-weight:bold;'>DRAFT</span>";
                        break;

                        case 'APPROVED':
                            return "<span class='text-warning' style='font-weight:bold;'>APPROVED</span>";
                            break;

                        case 'POSTED':
                            return "<span class='text-success' style='font-weight:bold;'>POSTED</span>";
                            break;
                    }

                    return '<span class="' + badgeClass + '">' + data + '</span>';
                }
            }
        ], 'Bfrtip', []
    );

});

function success() {
    switch(actions) {
        case 'save':
            switch(module_content) {
                case 'journal_entries':
                    $('#journal_entry_table').DataTable().draw();
                    scion.create.sc_modal('journal_entries_form').hide('all', modalHideFunction);
                    break;

                case 'journal_entry_line_fields':
                    $('#journal_entry_table').DataTable().draw();
                    $('#journal_detail_table').DataTable().draw();
                    scion.create.sc_modal('journal_details_form').hide(modalHideFunction);

                    break;
            }
            break;
        case 'update':
            switch(module_content) {
                case 'journal_entries':
                    $('#journal_entry_table').DataTable().draw();
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

    $('#manual_description').val(description);
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
    $('#journal_entry_table').DataTable().draw();
}

function delete_error() {}

function generateData() {

    switch(module_content) {
        case 'journal_entries':
            form_data = {
                _token: _token,
                entry_date: $('#entry_date').val(),
                description: $('#description').val(),
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

function modalShowFunction() {
    scion.centralized_button(true, false, true, true);
}

function modalHideFunction() {
    scion.centralized_button(false, true, true, true);

    modal_content = 'journal_entries';
    module_url = '/accounting/journal_entries';
    module_type = 'custom';
    page_title = "Journal Entry";
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