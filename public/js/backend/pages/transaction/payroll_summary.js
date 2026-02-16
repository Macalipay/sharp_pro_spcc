var emp_id = null;
var date_selected = null;
var copied_schedule = [];
var date = new Date();
var period_id = null;

var sequence_no = null;
var schedule_type = null;
var selected_module = null;
var selected_period_start = null;
var selected_period_end = null;
var active_detail_id = null;
var emailRecipients = [];

var selected_details = '';

var timelog_edit = 0;
var summary_filters = {
    period_type: '',
    status: '',
    date_sort: 'desc',
    keyword: ''
};

function buildSummaryQuery() {
    return $.param({
        period_type: summary_filters.period_type,
        status: summary_filters.status,
        date_sort: summary_filters.date_sort,
        keyword: summary_filters.keyword
    });
}

function setupSummaryEntriesControl(tableSelector) {
    if (!$.fn.DataTable.isDataTable(tableSelector)) {
        return;
    }

    var table = $(tableSelector).DataTable();
    var wrapper = $(tableSelector + '_wrapper');
    var lengthSelect = wrapper.find('.dataTables_length select');

    if (!lengthSelect.length) {
        return;
    }

    lengthSelect.empty()
        .append('<option value="5">5</option>')
        .append('<option value="10">10</option>')
        .append('<option value="15">15</option>')
        .append('<option value="20">20</option>')
        .append('<option value="25">25</option>')
        .append('<option value="30">30</option>')
        .append('<option value="-2">Custom</option>');

    lengthSelect.val(String(table.page.len()));

    lengthSelect.off('change.customLen').on('change.customLen', function() {
        var selected = $(this).val();
        if (selected === '-2') {
            var input = window.prompt('Enter number of entries:', String(table.page.len()));
            if (input === null) {
                $(this).val(String(table.page.len()));
                return;
            }

            var customValue = parseInt(input, 10);
            if (isNaN(customValue) || customValue <= 0) {
                customValue = 10;
            }

            if ($(this).find('option[value="' + customValue + '"]').length === 0) {
                $(this).append('<option value="' + customValue + '">' + customValue + '</option>');
            }

            table.page.len(customValue).draw();
            $(this).val(String(customValue));
            return;
        }

        table.page.len(parseInt(selected, 10)).draw();
    });
}

function loadPayrollSummaryTables() {
    var currentUrl = module_url + '/get?' + buildSummaryQuery();
    var historyUrl = module_url + '/get_history?' + buildSummaryQuery();

    if ($.fn.DataTable.isDataTable('#payroll_summary_table')) {
        $('#payroll_summary_table').DataTable().clear().destroy();
        $('#payroll_summary_table').empty();
    }

    if ($.fn.DataTable.isDataTable('#payroll_history_table')) {
        $('#payroll_history_table').DataTable().clear().destroy();
        $('#payroll_history_table').empty();
    }

    scion.create.table(
        'payroll_summary_table',
        currentUrl,
        [
            { data: "sequence_no", title:"SEQUENCE NO", width: "100px"},
            { data: "schedule_type", title:"PERIOD TYPE", width: "100px", render: function(data, type, row, meta) {
                switch(row.schedule_type) {
                    case 0:
                        return "13th MONTH PAY";
                        break;
                    case 1:
                        return "MONTHLY";
                        break;
                    case 2:
                        return "SEMI-MONTHLY";
                        break;
                    case 3:
                        return "BI-WEEKLY";
                        break;
                    case 4:
                        return "WEEKLY";
                        break;
                }
            }},
            { data: "payroll_title", title:"WEEKLY PROJECT NAME", width: "150px", render: function(data, type, row, meta) {
                return row.payroll_title || '-';
            }},
            { data: null, title:"PERIOD COVERED", width: "220px", className: "col-period-covered", render: function(data, type, row, meta) {
                if (!row.period_start || !row.payroll_period) {
                    return '-';
                }
                return "<span class='period-covered-text'>" + moment(row.period_start).format('MMM DD, YYYY') + " - " + moment(row.payroll_period).format('MMM DD, YYYY') + "</span>";
            }},
            { data: "gross_earnings", title:"GROSS EARNINGS", width: "120px", render: function(data, type, row, meta) {
                return scion.currency(row.gross_earnings || row.amount || 0);
            }},
            { data: "gross_deduction", title:"GROSS DEDUCTION", width: "120px", render: function(data, type, row, meta) {
                return scion.currency(row.gross_deduction || 0);
            }},
            { data: "net_amount", title:"NET PAY", width: "110px", render: function(data, type, row, meta) {
                return scion.currency(row.net_amount || 0);
            }},
            { data: "status", title:"STATUS", width: "190px", render: function(data, type, row, meta) {
                var workflowStatus = parseInt(row.workflow_status || 0, 10);

                switch(workflowStatus) {
                    case 0:
                        return "<span class='text-primary' style='font-weight:bold;'>DRAFT</span>";
                        break;
                    case 1:
                        return "<span class='text-info' style='font-weight:bold;'>SUBMITTED FOR APPROVAL</span>";
                        break;
                    case 2:
                        return "<span class='text-success' style='font-weight:bold;'>APPROVED</span>";
                        break;
                    case 3:
                        return "<span class='text-warning' style='font-weight:bold;'>SUBMITTED FOR PAYMENT</span>";
                        break;
                }
                return "<span class='text-primary' style='font-weight:bold;'>DRAFT</span>";
            }}
        ], 'Bt<"row mt-2 align-items-center"<"col-sm-6"l><"col-sm-6 text-right"p>>', [], true, false, '46vh'
    );

    scion.create.table(
        'payroll_history_table',
        historyUrl,
        [
            { data: "sequence_no", title:"SEQUENCE NO", width: "100px"},
            { data: "schedule_type", title:"SCHEDULE_TYPE", width: "100px", render: function(data, type, row, meta) {
                switch(row.schedule_type) {
                    case 1:
                        return "MONTHLY";
                        break;
                    case 2:
                        return "BI-MONTHLY";
                        break;
                    case 3:
                        return "BI-WEEKLY";
                        break;
                    case 4:
                        return "WEEKLY";
                        break;
                }
            }},
            { data: "payroll_title", title:"WEEKLY PROJECT NAME", width: "150px", render: function(data, type, row, meta) {
                return row.payroll_title || '-';
            }},
            { data: null, title:"PERIOD COVERED", width: "220px", className: "col-period-covered", render: function(data, type, row, meta) {
                if (!row.period_start || !row.payroll_period) {
                    return '-';
                }
                return "<span class='period-covered-text'>" + moment(row.period_start).format('MMM DD, YYYY') + " - " + moment(row.payroll_period).format('MMM DD, YYYY') + "</span>";
            } },
            { data: "gross_earnings", title:"GROSS EARNINGS", width: "120px", render: function(data, type, row, meta) {
                return scion.currency(row.gross_earnings || row.amount || 0);
            }},
            { data: "gross_deduction", title:"GROSS DEDUCTION", width: "120px", render: function(data, type, row, meta) {
                return scion.currency(row.gross_deduction || 0);
            }},
            { data: "no_of_employee", title:"EMPLOYEE SUMMARY", render: function(data, type, row, meta) {
                var approved = parseInt(row.no_of_employee || 0, 10);
                var total = parseInt(row.total_of_employee || 0, 10);
                var pending = parseInt(row.pending_employee || 0, 10);
                return `${approved} approved / ${total} total (pending: ${pending})`;
            }},
            { data: "net_amount", title:"NET PAY", width: "110px", render: function(data, type, row, meta) {
                return scion.currency(row.net_amount || 0);
            }},
            { data: "status", title:"STATUS", width: "190px", render: function(data, type, row, meta) {
                var workflowStatus = parseInt(row.workflow_status || 0, 10);

                switch(workflowStatus) {
                    case 3:
                        return "<span class='text-warning' style='font-weight:bold;'>SUBMITTED FOR PAYMENT</span>";
                        break;
                }
                if (parseInt(row.status || 0, 10) === 1 || parseInt(row.workflow_status || 0, 10) === 3) {
                    return "<span class='text-warning' style='font-weight:bold;'>SUBMITTED FOR PAYMENT</span>";
                }
                return "<span class='text-info' style='font-weight:bold;'>SUBMITTED FOR APPROVAL</span>";
            }}
        ], 't<"row mt-2 align-items-center"<"col-sm-6"l><"col-sm-6 text-right"p>>', [], true, false, '46vh'
    );

    setTimeout(function() {
        if ($.fn.DataTable.isDataTable('#payroll_summary_table')) {
            $('#payroll_summary_table').DataTable().page.len(10).draw();
            setupSummaryEntriesControl('#payroll_summary_table');
            $('#payroll_summary_table tbody tr').css('cursor', 'pointer');
        }

        if ($.fn.DataTable.isDataTable('#payroll_history_table')) {
            $('#payroll_history_table').DataTable().page.len(10).draw();
            setupSummaryEntriesControl('#payroll_history_table');
            $('#payroll_history_table tbody tr').css('cursor', 'pointer');
        }
    }, 100);
}

function renderEmailRecipientRows() {
    var tbody = $('#email_payslip_table tbody');
    var html = '';

    emailRecipients.forEach(function(item) {
        var disabled = item.has_email ? '' : 'disabled';
        var checked = item.has_email ? 'checked' : '';
        var emailDisplay = item.has_email ? item.email : '<span class="text-danger">No registered email</span>';
        var statusValue = (item.payslip_status || 'FOR SENDING').toUpperCase();
        var statusBadge = statusValue === 'SENT'
            ? '<span class="badge badge-success">SENT</span>'
            : '<span class="badge badge-secondary">FOR SENDING</span>';

        html += `<tr>
            <td class="text-center"><input type="checkbox" class="email-recipient-item" value="${item.employee_id}" ${checked} ${disabled}></td>
            <td>${item.employee_name} <small class="text-muted">(${item.employee_no || '-'})</small></td>
            <td>${emailDisplay}</td>
            <td class="text-center">${statusBadge}</td>
        </tr>`;
    });

    if (emailRecipients.length === 0) {
        html = `<tr><td colspan="4" class="text-center text-muted">No employees found for this payroll.</td></tr>`;
    }

    tbody.html(html);
    $('#email_select_all').prop('checked', $('.email-recipient-item:not(:disabled)').length === $('.email-recipient-item:checked:not(:disabled)').length);
}

function openEmailPayslipModal() {
    if (!period_id) {
        toastr.error('No payroll transaction selected.');
        return;
    }

    $.get(module_url + '/email_recipients/' + period_id)
        .done(function(response) {
            emailRecipients = response.recipients || [];
            renderEmailRecipientRows();
            scion.create.sc_modal('email_payslip_modal', '').show();
        })
        .fail(function() {
            toastr.error('Failed to load employee email list.');
        });
}

function sendSelectedPayslips() {
    var selectedEmployees = [];
    $('#email_payslip_table .email-recipient-item:checked:not(:disabled)').each(function() {
        selectedEmployees.push(parseInt($(this).val(), 10));
    });

    if (selectedEmployees.length === 0) {
        toastr.error('Please select at least one employee with a registered email.');
        return;
    }

    $.post(module_url + '/send_selected_payslips', {
        _token: _token,
        summary_id: period_id,
        employee_ids: selectedEmployees
    }).done(function(response) {
        toastr.success(`Payslips processed. Sent: ${response.sent_count || 0}, Skipped: ${response.skipped_count || 0}`);
        $('#payroll_summary_table').DataTable().draw();
        $('#payroll_history_table').DataTable().draw();
        if ($.fn.DataTable.isDataTable('#payroll_details_table')) {
            $('#payroll_details_table').DataTable().draw(false);
        }
        scion.create.sc_modal('email_payslip_modal').hide();
    }).fail(function(xhr) {
        var message = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to send selected payslips.';
        toastr.error(message);
    });
}

function openPayrollTransactionDetails(rowData, sourceType) {
    if (!rowData) {
        return;
    }

    period_id = rowData.id;
    sequence_no = rowData.sequence_no;
    schedule_type = rowData.schedule_type;
    selected_period_start = rowData.period_start || null;
    selected_period_end = rowData.payroll_period || null;
    selected_details = sourceType;

    $('.sent-email').css('display', 'inline-block');
    $('#print_payslip button').css('display', 'none');

    scion.record.new();

    if ($.fn.DataTable.isDataTable('#payroll_details_table')) {
        $('#payroll_details_table').DataTable().clear().destroy();
        $('#payroll_details_table thead').empty();
        $('#payroll_details_table tbody').empty();
    }

    var payrollDetailsTable = scion.create.table(
        'payroll_details_table',
        module_url + '/get_details/' + sequence_no,
        [
            { data: "employee.firstname", title:"LIST OF EMPLOYEES", width: "170px", render: function(data, type, row, meta) {
                if (!row.employee) {
                    return '-';
                }
                var fullName = row.employee.firstname + (row.employee.middlename === "" ? " " + row.employee.middlename : " ") + row.employee.lastname + (row.employee.suffix === "" ? " " + row.employee.suffix : "");
                return `<span class="employee-name-inline">${fullName}<a href="javascript:void(0)" class="view-payslip ml-1" data-detail-id="${row.id}" data-sequence="${row.sequence_no}" data-emp-id="${row.employee_id}" title="View Payslip"><i class="fas fa-receipt"></i></a></span>`;
            }},
            { data: "gross_earnings", title:"GROSS SALARY", width: "95px", render: function(data, type, row, meta) {
                var grossSalary = parseFloat(row.gross_earnings || row.gross_salary || 0);
                return "<span class='amount-inline'>" + scion.currency(isNaN(grossSalary) ? 0 : grossSalary) + "</span>";
            }},
            { data: "sss", title:"SSS", width: "70px", render: function(data, type, row, meta) {
                var sss = parseFloat(row.sss || 0);
                return "<span class='amount-inline'>" + scion.currency(isNaN(sss) ? 0 : sss) + "</span>";
            }},
            { data: "philhealth", title:"PHIC", width: "70px", render: function(data, type, row, meta) {
                var phic = parseFloat(row.philhealth || row.phic || 0);
                return "<span class='amount-inline'>" + scion.currency(isNaN(phic) ? 0 : phic) + "</span>";
            }},
            { data: "pagibig", title:"PAG-IBIG", width: "75px", render: function(data, type, row, meta) {
                var pagibig = parseFloat(row.pagibig || row.pag_ibig || row.hdmf || 0);
                return "<span class='amount-inline'>" + scion.currency(isNaN(pagibig) ? 0 : pagibig) + "</span>";
            }},
            { data: "tax", title:"WITHHOLDING TAX", width: "95px", render: function(data, type, row, meta) {
                var tax = parseFloat(row.tax || row.tax_final || row.withholding_tax || 0);
                return "<span class='amount-inline'>" + scion.currency(isNaN(tax) ? 0 : tax) + "</span>";
            }},
            { data: null, title:"GROSS DEDUCTION", width: "100px", render: function(data, type, row, meta) {
                var sss = parseFloat(row.sss || 0);
                var pagibig = parseFloat(row.pagibig || row.pag_ibig || row.hdmf || 0);
                var phic = parseFloat(row.philhealth || row.phic || 0);
                var tax = parseFloat(row.tax || row.tax_final || row.withholding_tax || 0);
                var ca = parseFloat(row.ca || 0);
                var grossDeduction = (isNaN(sss) ? 0 : sss)
                    + (isNaN(pagibig) ? 0 : pagibig)
                    + (isNaN(phic) ? 0 : phic)
                    + (isNaN(tax) ? 0 : tax)
                    + (isNaN(ca) ? 0 : ca);
                return "<span class='amount-inline'>" + scion.currency(grossDeduction) + "</span>";
            }},
            { data: "net_pay", title:"NET PAY", width: "90px", render: function(data, type, row, meta) {
                var sss = parseFloat(row.sss || 0);
                var pagibig = parseFloat(row.pagibig || row.pag_ibig || row.hdmf || 0);
                var phic = parseFloat(row.philhealth || row.phic || 0);
                var tax = parseFloat(row.tax || row.tax_final || row.withholding_tax || 0);
                var ca = parseFloat(row.ca || 0);
                var grossSalary = parseFloat(row.gross_earnings || row.gross_salary || 0);
                var deductions = (isNaN(sss) ? 0 : sss)
                    + (isNaN(pagibig) ? 0 : pagibig)
                    + (isNaN(phic) ? 0 : phic)
                    + (isNaN(tax) ? 0 : tax)
                    + (isNaN(ca) ? 0 : ca);
                var netPay = parseFloat(row.net_pay || row.net_amount || 0);
                if ((isNaN(netPay) || netPay === 0) && !isNaN(grossSalary) && grossSalary > 0) {
                    netPay = grossSalary - deductions;
                }
                return "<span class='amount-inline'>" + scion.currency(isNaN(netPay) ? 0 : netPay) + "</span>";
            }},
            { data: "payslip_status", title:"PAYSLIP STATUS", width: "95px", render: function(data, type, row, meta) {
                var status = (row.payslip_status || 'FOR SENDING').toUpperCase();
                return status === 'SENT'
                    ? "<span class='badge badge-success'>SENT</span>"
                    : "<span class='badge badge-secondary'>FOR SENDING</span>";
            }}
        ], 'tip', [], true, false
    );

    // Keep all requested payroll amounts as visible columns (no responsive column collapsing).
    if (payrollDetailsTable && payrollDetailsTable.responsive && typeof payrollDetailsTable.responsive.destroy === 'function') {
        payrollDetailsTable.responsive.destroy();
    }

    get_overall(sequence_no, schedule_type);
    scion.centralized_button(true, true, true, true);
}

$(function() {
    if (!$('#payroll_summary_table').length || !$('#payroll_history_table').length) {
        return;
    }

    modal_content = 'payroll_summary';
    module_url = '/payroll/summary';
    module_type = 'custom';
    page_title = "List";

    scion.centralized_button(true, true, true, true);
    scion.create.additional_button([
        {
            title: "PREV",
            id: "prev",
            disable: true
        },
        {
            title: "NEXT",
            id: "next",
            disable: true
        }
    ]);
    summary_filters.period_type = $('#filter_period_type').val() || '';
    summary_filters.status = $('#filter_status').val() || '';
    summary_filters.date_sort = $('#sort_date_covered').val() || 'desc';
    summary_filters.keyword = $('#filter_keyword').val() || '';

    loadPayrollSummaryTables();

    $('#summary_apply_filter').on('click', function() {
        summary_filters.period_type = $('#filter_period_type').val() || '';
        summary_filters.status = $('#filter_status').val() || '';
        summary_filters.date_sort = $('#sort_date_covered').val() || 'desc';
        summary_filters.keyword = $('#filter_keyword').val() || '';
        loadPayrollSummaryTables();
    });

    $('#summary_reset_filter').on('click', function() {
        $('#filter_period_type').val('');
        $('#filter_status').val('');
        $('#sort_date_covered').val('desc');
        $('#filter_keyword').val('');
        summary_filters.period_type = '';
        summary_filters.status = '';
        summary_filters.date_sort = 'desc';
        summary_filters.keyword = '';
        loadPayrollSummaryTables();
    });

    $('#filter_keyword').on('keypress', function(e) {
        if (e.which === 13) {
            $('#summary_apply_filter').trigger('click');
        }
    });

    $('a[data-toggle="tab"][href="#payroll-current-pane"], a[data-toggle="tab"][href="#payroll-history-pane"]').on('shown.bs.tab', function() {
        if ($.fn.DataTable.isDataTable('#payroll_summary_table')) {
            $('#payroll_summary_table').DataTable().columns.adjust();
        }
        if ($.fn.DataTable.isDataTable('#payroll_history_table')) {
            $('#payroll_history_table').DataTable().columns.adjust();
        }
    });

    $('#payroll_summary_table tbody').on('click', 'tr', function() {
        var table = $('#payroll_summary_table').DataTable();
        var rowData = table.row(this).data();
        openPayrollTransactionDetails(rowData, 'summary');
    }).on('click', '.set-completed', function() {
        period_id = $(this).attr('data-id');
        sequence_no = $(this).attr('data-sequence');
        $('.sequence_no_disp').text(sequence_no);

        scion.sc_modal_show('approval_confirmation');
        
    });

    $('#payroll_history_table tbody').on('click', 'tr', function() {
        var table = $('#payroll_history_table').DataTable();
        var rowData = table.row(this).data();
        openPayrollTransactionDetails(rowData, 'history');
    });

    $('#payroll_summary_form').on('click', '.view-payslip', function() {
        active_detail_id = $(this).attr('data-detail-id');
        emp_id = $(this).attr('data-emp-id');

        selected_print = 'print_payslip';

        $('#payslip_form').css('display', 'block');
        $('#payslip_form').css('position', 'fixed');

        $('#additional_buttons button').prop('disabled', false);
        scion.centralized_button(true, true, true, false);

        showDetails(active_detail_id, emp_id);
    }).on('click', '.print-all-payslips', function() {
        printAllPayslips();
    }).on('click', '.sent-email', function() {
        openEmailPayslipModal();
    });

    $('#email_payslip_modal').on('change', '#email_select_all', function() {
        var checked = $(this).prop('checked');
        $('#email_payslip_table .email-recipient-item:not(:disabled)').prop('checked', checked);
    }).on('change', '.email-recipient-item', function() {
        var totalEnabled = $('#email_payslip_table .email-recipient-item:not(:disabled)').length;
        var totalChecked = $('#email_payslip_table .email-recipient-item:checked:not(:disabled)').length;
        $('#email_select_all').prop('checked', totalEnabled > 0 && totalEnabled === totalChecked);
    }).on('click', '.email-payslip-cancel', function() {
        scion.create.sc_modal('email_payslip_modal').hide();
    }).on('click', '.email-payslip-send', function() {
        sendSelectedPayslips();
    });

    $('#payslip_form').on('click', '#add_earnings', function() {
        selected_module = "earning";
        
        $('#add_details').css('display', 'block');
        $('#add_details').css('position', 'fixed');

        $('#add_details .sc-title-bar').text('ADD EARNING');

        $.get(module_url + '/get_earnings').done(function(response) {
            var option = "";

            option += "<option value=''>PLEASE SELECT EARNING TYPE</option>";

            $.each(response.earnings, function(i, val){
                option += "<option value='"+val.id+"'>"+val.name+"</option>";
            });

            $('#add_details #type').html(option);
        });

        scion.centralized_button(true, false, true, true);
    }).on('click', '#add_deductions', function() {
        selected_module = "deduction";
        $('#add_details').css('display', 'block');
        $('#add_details').css('position', 'fixed');

        $('#add_details .sc-title-bar').text('ADD DEDUCTION');
        
        $.get(module_url + '/get_deductions').done(function(response) {
            var option = "";

            option += "<option value=''>PLEASE SELECT DEDUCTION TYPE</option>";

            $.each(response.deductions, function(i, val){
                option += "<option value='"+val.id+"'>"+val.name+"</option>";
            });

            $('#add_details #type').html(option);
        });
        
        scion.centralized_button(true, false, true, true);
    });

    $('#approval_confirmation').on('click', '.positive-button', function() {
        $.post(module_url + '/update_status', { _token: _token, id: period_id, status: 1 }).done(function(response) {
            $('#payroll_summary_table').DataTable().draw();
            $('#payroll_history_table').DataTable().draw();
            scion.create.sc_modal('approval_confirmation').hide('all', modalHideFunction);
        });
    }).on('click', '.negative-button', function() {
        scion.create.sc_modal('approval_confirmation').hide('all', modalHideFunction)
    });

    $('#payroll_details_table').on('click', '.check-status', function() {
        var id = $(this).attr('data-id');
        var status = $(this).prop('checked') === true?1:0;

        $.post(module_url + '/update_details_status', { _token: _token, id: id, status: status }).done(function(response) {
            $('#payroll_summary_table').DataTable().draw();
            get_overall(sequence_no, schedule_type);
        });
    });

    $('.action-buttons').on('click', '#next', function() {
        $.post(module_url + '/show', { _token:_token,  employee_id: emp_id, sequence_no: sequence_no }).done(function(response) {
            if(response.next !== null) {
                emp_id = response.next.employee_id;
                active_detail_id = response.next.id;
                showDetails(active_detail_id, emp_id);
            }
            else {
                if(response.previous !== null) {
                    emp_id = response.previous.employee_id;
                    active_detail_id = response.previous.id;
                    showDetails(active_detail_id, emp_id);
                }
            }
        });
    }).on('click', '#prev', function() {
        $.post(module_url + '/show', { _token:_token,  employee_id: emp_id, sequence_no: sequence_no }).done(function(response) {
            if(response.previous !== null) {
                emp_id = response.previous.employee_id;
                active_detail_id = response.previous.id;
                showDetails(active_detail_id, emp_id);
            }
            else {
                if(response.next !== null) {
                    emp_id = response.next.employee_id;
                    active_detail_id = response.next.id;
                    showDetails(active_detail_id, emp_id);
                }
            }
        });
    });

});


function success() {
    switch(actions) {
        case 'save':
            break;
        case 'update':
            break;
    }

    $('#amount').val('0');

    $('#payroll_details_table').DataTable().draw();
    updatePayslip();

    scion.create.sc_modal('add_details').hide('', modalHideFunction);
}

function error() {}

function delete_success() {
    $('#payroll_summary_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token,
        "employee_id": emp_id,
        "sequence_no": sequence_no,
        "type": $('#type').val(),
        "amount": $('#amount').val(),
        "module": selected_module
    };

    return form_data;
}

function generateDeleteItems(){}


function modalShowFunction() {
    scion.centralized_button(true, false, true, true);
}

function modalHideFunction() {
    scion.centralized_button(true, true, true, true);
}

function customFunc() {
    scion.centralized_button(true, true, true, true);
}

function updatePayslip() {
    
    $.post(module_url + '/get_earnings_and_deductions', { _token: _token, sequence_no: sequence_no, employee_id: emp_id, schedule_type: schedule_type }).done(function(response){
        var data = response;
        var earning_tbl = "";
        var deduction_tbl = "";

        // Set Employee Information
        $('#t-full_name').text((data.employee.firstname + (data.employee.middlename !== "" && data.employee.middlename !== null?" " + data.employee.middlename + " ":" ") + data.employee.lastname + (data.employee.suffix !== "" && data.employee.suffix !== null?" " + data.employee.suffix:"")));
        $('#t-address').text(data.employee.street_1 + " " + data.employee.barangay_1 + " " + data.employee.city_1 + ", " + data.employee.province_1 + " " + data.employee.country_1+ ", " + data.employee.zip_1);
        $('#t-contact').text(data.employee.phone1);
        $('#t-email').text(data.employee.email);

        // Set Earnings
        $.each(data.earnings, function(i, val) {

            earning_tbl += "<tr>";
                earning_tbl += "<td><b>"+val.earning.name+"</b></td>";
                earning_tbl += "<td>"+(val.rate!==""?val.rate:"-")+"</td>";
                earning_tbl += "<td>"+(val.hours!==""?val.hours:"-")+"</td>";
                earning_tbl += "<td class='text-right'>"+(val.total!==""?scion.currency(val.total):"-")+"</td>";
            earning_tbl += "</tr>";

        });
        $('div#earnings tbody').html(earning_tbl);
        
        // Set Deductions
        $.each(data.deductions, function(i, val) {

            switch(val.deduction.id) {
                case 1:
                    $('#mandated #sss').text(scion.currency(val.total));
                    break;
                case 2:
                    $('#mandated #philhealth').text(scion.currency(val.total));
                    break;
                case 3:
                    $('#mandated #pagibig').text(scion.currency(val.total));
                    break;
                default:
                    deduction_tbl += "<tr>";
                        deduction_tbl += "<td><b>"+val.deduction.name+"</b> <i></td>";
                        deduction_tbl += "<td class='text-right'>"+(val.total!==""?scion.currency(val.total):"-")+"</td>";
                    deduction_tbl += "</tr>";
            }

        });
        
        $('#other tbody').html(deduction_tbl);
        
        $('#earnings .total').text(scion.currency(response.earnings_total));
        $('#deductions .total').text(scion.currency(response.deductions_total));
        $('#taxable-amount').text(scion.currency(response.taxable_amount));
        $('#withholding-tax').text(scion.currency(response.withholding_tax));
        $('#net-pay').text(scion.currency(response.netpay));

        $('#paydate').text(moment(response.summary.pay_date).format('MMM DD YYYY'));
        $('#paytype').text(response.summary.calendar !== null? response.summary.calendar.title:'');
        $('#period').text(moment(response.summary.payroll_period).format('MMM DD YYYY'));
        $('#sequence').text(sequence_no);
        $('#paymentmethod').text("CASH");
        $('#netpay').text(scion.currency(response.netpay));
    });
}

function get_overall(sequence, type) {
    scion.record.get('get_overall', { _token: _token, sequence_no: sequence, type: type  },
        function(response) {
            $('#summary_total_gross_earnings').text(scion.currency(response.total.gross || 0));
            $('#summary_total_gross_deduction').text(scion.currency(response.total.gross_deduction || 0));
            $('#summary_total_net_pay').text(scion.currency(response.total.net_pay || 0));
        }
    );
}

function custom_modalHide() {
    selected_print = null;
    $('#additional_buttons button').prop('disabled', true);
    modalHideFunction();
}

function getPayslipStatusLabel(status) {
    switch (parseInt(status, 10)) {
        case 1: return 'APPROVED';
        case 2: return 'DECLINED';
        default: return 'DRAFT';
    }
}

function buildPayslipData(item) {
    var employee = item.employee || {};
    var employment = employee.employments_tab || {};
    var calendar = employment.calendar || {};
    var header = item.header || {};

    var worked_days = (item.for_fixed !== null ? item.for_fixed : parseInt((item.timelogs || []).length, 10) || 0);
    var daily_rate = item.daily !== "0" ? parseFloat(item.daily || 0) : parseFloat((employee.compensations && employee.compensations.daily_salary) || 0);
    var hourly_rate = item.hourly !== "0" ? parseFloat(item.hourly || 0) : parseFloat((employee.compensations && employee.compensations.hourly_salary) || 0);
    var monthly_rate = item.monthly !== "0" ? parseFloat(item.monthly || 0) : parseFloat((employee.compensations && employee.compensations.monthly_salary) || 0);

    var late = parseFloat(item.late_hours || 0);
    var undertime = parseFloat(item.undertime || 0);
    var late_rate = (late / 60) * hourly_rate;
    var ut_rate = (undertime / 60) * hourly_rate;
    var absent = parseFloat(item.absent_count || 0);
    var absent_rate = absent * daily_rate;

    var basic_pay = (parseInt(schedule_type, 10) === 2 ? (monthly_rate / 2) : (worked_days * daily_rate));
    var leave_amount = parseFloat((item.leave_count || 0) * daily_rate);
    var holiday_rate = 0;
    (item.holiday_data || []).forEach(function(h_data) {
        holiday_rate += daily_rate * parseFloat((h_data.holiday_type && h_data.holiday_type.multiplier) || 0);
    });

    var ot_hours = parseFloat(item.ot_hours || 0);
    var ot_amount = parseFloat(item.ot_amount || 0);
    var allowance_amount = parseFloat(item.allowance_amount || 0);
    var allowance_daily = allowance_amount !== 0 ? (allowance_amount / (worked_days !== 0 ? worked_days : 1)) : 0;
    var tardiness_deduct = absent_rate === 0 ? (late_rate + ut_rate) : (absent_rate - (late_rate + ut_rate));

    var gross_salary = (basic_pay + ot_amount + allowance_amount + leave_amount + holiday_rate) - tardiness_deduct;

    var sss = parseFloat(item.sss || 0);
    var phic = parseFloat(item.philhealth || 0);
    var pagibig = parseFloat(item.pagibig || 0);
    var tax = parseFloat(item.tax || 0) !== 0 ? parseFloat(item.tax || 0) : parseFloat(item.tax_final || 0);
    var ca = parseFloat(item.ca || 0);

    var government_deduction = sss + phic + pagibig;
    var other_deduction = ca;
    var total_deduction = government_deduction + other_deduction + tax;
    var tax_amount = gross_salary - (government_deduction + other_deduction);
    var net_pay = gross_salary - total_deduction;

    var fullName = (employee.firstname || '') +
        ((employee.middlename && employee.middlename !== '') ? (' ' + employee.middlename) : '') +
        ' ' + (employee.lastname || '');

    return {
        employee: employee,
        employment: employment,
        calendar: calendar,
        header: header,
        fullName: $.trim(fullName),
        worked_days: worked_days,
        daily_rate: daily_rate,
        hourly_rate: hourly_rate,
        monthly_rate: monthly_rate,
        ot_hours: ot_hours,
        ot_amount: ot_amount,
        allowance_amount: allowance_amount,
        allowance_daily: allowance_daily,
        holiday_count: parseFloat(item.holiday || 0),
        holiday_rate: holiday_rate,
        leave_count: parseFloat(item.leave_count || 0),
        leave_amount: leave_amount,
        basic_pay: basic_pay,
        sss: sss,
        phic: phic,
        pagibig: pagibig,
        tax: tax,
        ca: ca,
        government_deduction: government_deduction,
        other_deduction: other_deduction,
        total_deduction: total_deduction,
        tax_amount: tax_amount,
        gross_salary: gross_salary,
        net_pay: net_pay
    };
}

function renderSinglePayslip(data) {
    $('#payslip_form #tbl_emp_name').text(data.fullName);
    $('#payslip_form #tbl_emp_number').text(data.employee.employee_no || '-');
    $('#payslip_form #tbl_emp_department').text((data.employment.departments && data.employment.departments.description) || '-');
    $('#payslip_form #tbl_emp_position').text((data.employment.positions && data.employment.positions.description) || '-');
    $('#payslip_form #tbl_emp_status').text(getPayslipStatusLabel(data.employee.status));

    $('#payslip_form #pay_date').text(data.header.pay_date ? moment(data.header.pay_date).format('MMM DD, YYYY') : '-');
    $('#payslip_form #pay_type').text(data.calendar.title || '-');
    $('#payslip_form #pay_period').text(
        selected_period_start && selected_period_end
            ? (moment(selected_period_start).format('MMM DD, YYYY') + ' - ' + moment(selected_period_end).format('MMM DD, YYYY'))
            : '-'
    );
    $('#payslip_form #sequence_no').text(data.header.sequence_no || sequence_no || '-');
    $('#payslip_form #net_pay').text(scion.currency(data.net_pay));

    var earningRows = `<tr><td>BASIC PAY</td><td class="text-center">${scion.currency(data.daily_rate)}</td><td class="text-center">${data.worked_days}</td><td class="text-center">${scion.currency(data.basic_pay)}</td></tr>`;
    if (data.ot_amount !== 0) {
        earningRows += `<tr><td>OVERTIME</td><td class="text-center">${scion.currency(data.hourly_rate)}</td><td class="text-center">${data.ot_hours.toFixed(2)}</td><td class="text-center">${scion.currency(data.ot_amount)}</td></tr>`;
    }

    var holidayRows = '';
    if (data.holiday_rate !== 0) {
        holidayRows += `<tr><td>HOLIDAY PAY</td><td class="text-center">${scion.currency(data.daily_rate)}</td><td class="text-center">${data.holiday_count}</td><td class="text-center">${scion.currency(data.holiday_rate)}</td></tr>`;
    }

    var allowanceRows = '';
    if (data.allowance_amount !== 0) {
        allowanceRows += `<tr><td>ALLOWANCE</td><td class="text-center">${scion.currency(data.allowance_daily)}</td><td class="text-center">${data.worked_days}</td><td class="text-center">${scion.currency(data.allowance_amount)}</td></tr>`;
    }

    var leaveRows = '';
    if (data.leave_amount !== 0) {
        leaveRows += `<tr><td>PAID LEAVE</td><td class="text-center">${scion.currency(data.daily_rate)}</td><td class="text-center">${data.leave_count}</td><td class="text-center">${scion.currency(data.leave_amount)}</td></tr>`;
    }

    var deductionRows = '';
    if (data.other_deduction !== 0) {
        deductionRows += `<tr><td style="width:90%" colspan="3">CASH ADVANCE</td><td style="width:30%" class="text-center">${scion.currency(data.other_deduction)}</td></tr>`;
    }

    $('#payslip_form #payroll_rate_details .custom').html(earningRows);
    $('#payslip_form .holiday-container').html(holidayRows);
    $('#payslip_form .allowance-container').html(allowanceRows);
    $('#payslip_form #payroll_leaves tbody').html(leaveRows);
    $('#payslip_form #payroll_other_deductions tbody').html(deductionRows);

    var total_earnings = data.basic_pay + data.ot_amount + data.allowance_amount + data.holiday_rate;
    $('#payslip_form #total_earnings').text(scion.currency(total_earnings));
    $('#payslip_form #total_leaves').text(scion.currency(data.leave_amount));
    $('#payslip_form #total_gross').text(scion.currency(data.gross_salary));
    $('#payslip_form #total_sss').text(scion.currency(data.sss));
    $('#payslip_form #total_philhealth').text(scion.currency(data.phic));
    $('#payslip_form #total_pagibig').text(scion.currency(data.pagibig));
    $('#payslip_form #total_government_deduction').text(scion.currency(data.government_deduction));
    $('#payslip_form #other_deduction').text(scion.currency(data.other_deduction));
    $('#payslip_form #total_deduction').text(scion.currency(data.total_deduction));
    $('#payslip_form #tax_amount').text(scion.currency(data.tax_amount));
    $('#payslip_form #withholding_tax').text(scion.currency(data.tax));
    $('#payslip_form #total_net_pay').text(scion.currency(data.net_pay));
}

function buildPrintablePayslipCard(data) {
    return `
        <div class="print-slip">
            <div class="print-header">
                <h3>Company Information</h3>
                <div><strong>Company Name:</strong> SP CONSTRUCTION CORPORATION (SPCC)</div>
                <div><strong>Sequence:</strong> ${data.header.sequence_no || sequence_no || '-'}</div>
                <div><strong>Period:</strong> ${selected_period_start && selected_period_end ? (moment(selected_period_start).format('MMM DD, YYYY') + ' - ' + moment(selected_period_end).format('MMM DD, YYYY')) : '-'}</div>
                <div><strong>Pay Date:</strong> ${data.header.pay_date ? moment(data.header.pay_date).format('MMM DD, YYYY') : '-'}</div>
            </div>
            <table class="print-info">
                <tr><td><strong>Employee</strong></td><td>${data.fullName}</td></tr>
                <tr><td><strong>Employee No.</strong></td><td>${data.employee.employee_no || '-'}</td></tr>
                <tr><td><strong>Department</strong></td><td>${(data.employment.departments && data.employment.departments.description) || '-'}</td></tr>
                <tr><td><strong>Position</strong></td><td>${(data.employment.positions && data.employment.positions.description) || '-'}</td></tr>
                <tr><td><strong>Status</strong></td><td>${getPayslipStatusLabel(data.employee.status)}</td></tr>
            </table>
            <table class="print-pay">
                <tr><td>Gross Earnings</td><td>${scion.currency(data.gross_salary)}</td></tr>
                <tr><td>SSS</td><td>${scion.currency(data.sss)}</td></tr>
                <tr><td>PHIC</td><td>${scion.currency(data.phic)}</td></tr>
                <tr><td>PAG-IBIG</td><td>${scion.currency(data.pagibig)}</td></tr>
                <tr><td>Withholding Tax</td><td>${scion.currency(data.tax)}</td></tr>
                <tr><td>Other Deductions</td><td>${scion.currency(data.other_deduction)}</td></tr>
                <tr><td>Total Deductions</td><td>${scion.currency(data.total_deduction)}</td></tr>
                <tr class="net"><td>Net Pay</td><td>${scion.currency(data.net_pay)}</td></tr>
            </table>
        </div>
    `;
}

function printAllPayslips() {
    if (!period_id || !selected_period_start || !selected_period_end) {
        toastr.error('Please open a payroll transaction first.');
        return;
    }

    $.post('/payroll/payrun/get-details', {
        _token: _token,
        id: period_id,
        start: selected_period_start,
        end: selected_period_end
    }).done(function(response) {
        var details = (response && response.details) ? response.details : [];
        if (details.length === 0) {
            toastr.error('No payroll details found to print.');
            return;
        }

        var cards = details.map(function(item) {
            return buildPrintablePayslipCard(buildPayslipData(item));
        });

        var pages = [];
        for (var i = 0; i < cards.length; i += 4) {
            pages.push(`<div class="print-page">${cards.slice(i, i + 4).join('')}</div>`);
        }
        var slipsHtml = pages.join('');

        var printWindow = window.open('', '_blank');
        if (!printWindow) {
            toastr.error('Unable to open print window. Please allow pop-ups.');
            return;
        }

        printWindow.document.write(`
            <html>
                <head>
                    <title>All Payslips - ${sequence_no || ''}</title>
                    <style>
                        @page { size: A4; margin: 8mm; }
                        body { font-family: Arial, sans-serif; margin: 0; color: #111; }
                        .print-page {
                            width: 100%;
                            min-height: calc(297mm - 16mm);
                            display: grid;
                            grid-template-columns: 1fr 1fr;
                            grid-template-rows: 1fr 1fr;
                            gap: 6mm;
                            page-break-after: always;
                            box-sizing: border-box;
                        }
                        .print-page:last-child { page-break-after: auto; }
                        .print-slip {
                            border: 1px solid #ccc;
                            padding: 6px;
                            page-break-inside: avoid;
                            box-sizing: border-box;
                            overflow: hidden;
                        }
                        .print-header h3 { margin: 0 0 6px 0; font-size: 12px; }
                        .print-header div { font-size: 9px; margin-bottom: 1px; }
                        table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 10px; }
                        td { border: 1px solid #ddd; padding: 2px 4px; }
                        .print-info td:first-child, .print-pay td:first-child { width: 40%; background: #f7f7f7; }
                        .print-pay .net td { font-weight: bold; background: #eef7ff; }
                        @media print {
                            body { margin: 0; }
                            .print-page { page-break-after: always; }
                            .print-page:last-child { page-break-after: auto; }
                        }
                    </style>
                </head>
                <body>${slipsHtml}</body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
    }).fail(function() {
        toastr.error('Failed to generate printable payslips.');
    });
}

function showDetails(detailId, employeeId) {
    if (!detailId || !selected_period_start || !selected_period_end) {
        toastr.error('Unable to load payslip details.');
        return;
    }

    record_id = detailId;
    emp_id = employeeId || emp_id;

    $.post('/payroll/payrun/get-details-info', {
        _token: _token,
        id: detailId,
        start: selected_period_start,
        end: selected_period_end,
        employee_id: emp_id
    }).done(function(response) {
        if (!response || !response.details || response.details.length === 0) {
            toastr.error('No payslip data found for the selected employee.');
            return;
        }

        var item = response.details[0];
        renderSinglePayslip(buildPayslipData(item));
    }).fail(function() {
        toastr.error('Failed to load payslip details.');
    });
}

function viewPayslip(id, sequence) {
    console.log(id, sequence_no);
    scion.create.sc_modal("payslip_form_2", "").show();

    scion.centralized_button(true, true, true, false);
    selected_print = 'print_payslip_2';

    $.get(`/payroll/13-month/get-slip/${id}/${sequence}`, (response) => {
        $('#13_tbl_emp_name').text(response.details.employee.firstname + (response.details.employee.middlename !== "" && response.details.employee.middlename !== null?" " + response.details.employee.middlename:"") + " " + response.details.employee.lastname);
        $('#13_tbl_emp_number').text(response.details.employee.employee_no);
        $('#13_tbl_emp_address').text('-');
        $('#13_tbl_emp_info').text(response.details.employee.phone1);
        $('#13_tbl_emp_department').text(response?.details?.employee?.employments_tab?.departments?.description ?? '-');
        $('#13_tbl_emp_position').text(response?.details?.employee?.employments_tab?.positions?.description ?? '-');

        if (response.details.employee.status == "1") {
            $('#13_tbl_emp_status').text("ACTIVE");
        }
        else if (response.details.employee.status == "6") {
            $('#13_tbl_emp_status').text("PROBATION");
        }
        else if (response.details.employee.status == "7") {
            $('#13_tbl_emp_status').text("ON-CALL");
        }
        else if (response.details.employee.status == "8") {
            $('#13_tbl_emp_status').text("INTERNSHIP/OJT");
        }
        else {
            $('#13_tbl_emp_status').text("INACTIVE");
        }

        $('#13_pay_date').text(moment(response.details.header.pay_date).format('MMM DD, YYYY'));
        $('#13_pay_date').text(moment(response.details.header.payroll_period).format('MMM DD, YYYY'));
        $('#13_pay_period').text('13th MONTH');
        $('#13_sequence_no').text(response.details.header.sequence_no);
        $('#13_net_pay').text(scion.currency(response.details.net_pay));
        $('#13_total_earnings').text(scion.currency(response.details.net_pay));
        $('#13_total_net_pay').text(scion.currency(response.details.net_pay));

        $('#payroll_rate_details_13 .custom').html(`<tr><td style="width:50%">13th Month</td><td style="width:50%" class="text-right">${scion.currency(parseFloat(response.details.net_pay))}</td></tr>`);
        
    });
} 

// function showDetails(id) {
//     var regular_hours = 0;
//     var overtime = 0;
//     var working_hours = 0;

//     record_id = id;

//     $.post('/payroll/time_logs/get_record/' + id, {_token: _token, date: $('#date-filter').val()}, function(response) {
//         var timesheet = '';

//         var hrate = response.record.compensations !== null?response.record.compensations.hourly_salary:0;
//         var total_earnings = 0;
//         var other_deduction = 0;
//         var total_deduction = 0;
//         var total_leaves = 0;
//         var total_gross = 0;
//         var sss = 0;
//         var philhealth = 0;
//         var pagibig = 200/2;
//         var wtax = 0;
//         var government_deduction = 200/2;
//         var net_pay = 0;

//         if(response.record.approval !== null) {
//             $('#timesheet_status').text('APPROVED');
//             $('.approve-btn').css('display', 'none');
//         }
//         else {
//             $('#timesheet_status').text('DRAFT');
//             $('.approve-btn').css('display', 'inline-block');
//         }

//         $('#tbl_emp_name').text(response.record.firstname + (response.record.middlename !== "" && response.record.middlename !== null?" " + response.record.middlename:"") + " " + response.record.lastname);
//         $('#tbl_emp_number').text(response.record.employee_no);
//         $('#tbl_emp_address').text('-');
//         $('#tbl_emp_info').text(response.record.phone1);
//         $('#tbl_emp_department').text(response.record.employments_tab.departments.description);
//         $('#tbl_emp_position').text(response.record.employments_tab.positions.description);

//         if (response.record.status == "1") {
//             $('#tbl_emp_status').text("ACTIVE");
//         }
//         else if (response.record.status == "6") {
//             $('#tbl_emp_status').text("PROBATION");
//         }
//         else if (response.record.status == "7") {
//             $('#tbl_emp_status').text("ON-CALL");
//         }
//         else if (response.record.status == "8") {
//             $('#tbl_emp_status').text("INTERNSHIP/OJT");
//         }
//         else {
//             $('#tbl_emp_status').text("INACTIVE");
//         }

//         $('#h_rate').text(scion.currency(hrate));

//         if(response.semi_monthly !== null){
//             var earning = "";
//             var leave = "";
//             var holiday = "";
//             var allowance = "";
//             var deduction = "";
//             var tax_amount = 0;

//             $('#pay_period').text(response.other.pay_period);
//             $('#pay_date').text(response.other.pay_date);
//             $('#pay_type').text(response.record.employments_tab.calendar.title);

//             response.other.deductions.forEach((item) => {
//                 deduction += "<tr>";
//                 deduction += `<td style="width:90%" colspan="3">${item.deduction}</td>`;
//                 deduction += `<td style="width:30%" class="text-center">${scion.currency(item.total)}</td>`;
//                 deduction += "</tr>";
//                 other_deduction += item.total;
//             });
            


//             response.semi_monthly.forEach((semiMonth) => {
//                 timesheet += `<tr>`;
//                     timesheet += `<td>${semiMonth.date}</td>`;
//                     timesheet += `<td>${semiMonth.day}</td>`;
//                     timesheet += `<td><span class="status ${semiMonth.status.replace(' ', '-')}">${semiMonth.status}</span></td>`;
//                     timesheet += `<td>${semiMonth.time_in!==null?moment(semiMonth.time_in).format('h:mm A'):'-'}</td>`;
//                     timesheet += `<td>${semiMonth.break_in!==null?moment(semiMonth.break_in).format('h:mm A'):'-'}</td>`;
//                     timesheet += `<td>${semiMonth.break_out!==null?moment(semiMonth.break_out).format('h:mm A'):'-'}</td>`;
//                     timesheet += `<td>${semiMonth.time_out!==null?moment(semiMonth.time_out).format('h:mm A'):'-'}</td>`;
//                     timesheet += `<td>${semiMonth.ot_in!==null?moment(semiMonth.ot_in).format('h:mm A'):'-'}</td>`;
//                     timesheet += `<td>${semiMonth.ot_out!==null?moment(semiMonth.ot_out).format('h:mm A'):'-'}</td>`;
//                     timesheet += `<td>${semiMonth.office_hours}</td>`;
//                     timesheet += `<td>${semiMonth.break_time}</td>`;
//                     timesheet += `<td>${parseFloat(semiMonth.office_hours-semiMonth.break_time)}</td>`;
//                     timesheet += `<td>${semiMonth.overtime}</td>`;
//                     timesheet += `<td>${(parseFloat(semiMonth.office_hours-semiMonth.break_time)) + parseFloat(semiMonth.overtime)}</td>`;
//                 timesheet += `</tr>`;

//                 regular_hours += parseFloat(semiMonth.office_hours-semiMonth.break_time);
//                 overtime += parseFloat(semiMonth.overtime);
//                 working_hours += (parseFloat(semiMonth.office_hours-semiMonth.break_time)) + parseFloat(semiMonth.overtime);
//             });

//             response.other.earnings.forEach(data => {
//                 let rate = parseFloat(hrate*data.earning.multiplier);
//                 let hours = data.earning.code === "RE"?regular_hours:data.earning.code === "OT"?overtime:0;

//                 earning += "<tr>";
//                 earning += `<td style="width:30%">${data.earning.name}</td>`;
//                 earning += `<td style="width:30%" class="text-center">${scion.currency(rate)}</td>`;
//                 earning += `<td style="width:30%" class="text-center">${hours}</td>`;
//                 earning += `<td style="width:30%" class="text-center">${scion.currency(parseFloat(rate*hours))}</td>`;
//                 earning += "</tr>";

//                 total_earnings += parseFloat(rate*hours);
//             });
            
            
//             response.other.allowances.forEach((item) => {
//                 allowance += "<tr>";
//                 allowance += `<td style="width:90%" colspan="3">${item.allowance}</td>`;
//                 allowance += `<td style="width:30%" class="text-center">${scion.currency(item.total)}</td>`;
//                 allowance += "</tr>";

//                 total_earnings += parseFloat(item.total);
//             });
            
//             response.other.holiday.forEach((data, i) => {
//                 let rate = parseFloat(hrate*data.holiday_type.multiplier);

//                 console.log(i);
//                 let hours = parseFloat(8*(i+1));

//                 holiday += "<tr>";
//                 holiday += `<td style="width:30%">${data.name}</td>`;
//                 holiday += `<td style="width:30%" class="text-center">${scion.currency(rate)}</td>`;
//                 holiday += `<td style="width:30%" class="text-center">${hours}</td>`;
//                 holiday += `<td style="width:30%" class="text-center">${scion.currency(parseFloat(rate*hours))}</td>`;
//                 holiday += "</tr>";

//                 total_earnings += parseFloat(rate*hours);
//             });

//             response.other.leave.forEach(data => {
//                 let rate = parseFloat(hrate*1);

//                 let hours = data.total_leave_hours;

//                 leave += "<tr>";
//                 leave += `<td style="width:30%">${data.leave_type.leave_name}</td>`;
//                 leave += `<td style="width:30%" class="text-center">${scion.currency(rate)}</td>`;
//                 leave += `<td style="width:30%" class="text-center">${hours}</td>`;
//                 leave += `<td style="width:30%" class="text-center">${scion.currency(parseFloat(rate*hours))}</td>`;
//                 leave += "</tr>";

//                 total_leaves += parseFloat(rate*hours);

//             });

//             total_gross = parseFloat(total_earnings + total_leaves);

//             philhealth = (total_gross*0.05)/2;

//             $.post(`/payroll/sss/get-val`, { _token:_token, gross: total_gross, type: response.record.employments_tab.calendar.type}, function(response) {
//                 sss = response.sss;
//                 government_deduction = sss + philhealth + pagibig;

//                 total_deduction = government_deduction + other_deduction;

//                 tax_amount = total_gross - total_deduction;

//                 net_pay = (tax_amount - response.w_tax);
//                 wtax = response.w_tax;

//                 $('#total_earnings').text(scion.currency(total_earnings));
//                 $('#other_deduction').text(scion.currency(other_deduction));
//                 $('#total_leaves').text(scion.currency(total_leaves));
//                 $('#payroll_leaves #total_gross').text(scion.currency(total_gross));

//                 $('#total_deduction').text(scion.currency(total_deduction));

//                 $('#total_government_deduction').text(scion.currency(government_deduction));
                
//                 $('#payroll_deductions #total_sss').text(scion.currency(sss));
//                 $('#payroll_deductions #total_philhealth').text(scion.currency(philhealth));
//                 $('#payroll_deductions #total_pagibig').text(scion.currency(pagibig));

//                 $('#tax_amount').text(scion.currency(tax_amount));
//                 $('#net_pay').text(scion.currency(net_pay));
//                 $('#total_net_pay').text(scion.currency(net_pay));
//                 $('#withholding_tax').text(scion.currency(response.w_tax));

//                 hold_approval = {
//                     gross_earnings: total_gross,
//                     sss: sss,
//                     philhealth: philhealth,
//                     pagibig: pagibig,
//                     tax: wtax,
//                     net_pay: net_pay
//                 };
//             });


//             $('.holiday-container').html(holiday);
//             $('#payroll_rate_details .custom').html(earning);
//             $('#payroll_leaves tbody').html(leave);
//             $('.allowance-container').html(allowance);
//             $('#payroll_other_deductions tbody').html(deduction);

//             $('#sequence_no').text("M-" + moment(response.other.pay_period).format('MMDDY'));

//             $('#total_regular_hours').text(regular_hours);
//             $('#total_overtime').text(overtime);
//             $('#total_working_hours').text(working_hours);
    
//             $('#timesheet tbody').html(timesheet);
    
//             scion.create.sc_modal("details_form", "Details").show();
//         } 
//         else {
//             toastr.error(`Please setup a payroll calendar for <b style='font-weight:bold;'>${response.record.firstname + (response.record.middlename !== "" && response.record.middlename !== null?" " + response.record.middlename:"") + " " + response.record.lastname}</b>`, "Generating Timesheet Failed")
//         }


//     })
// }
