var start_period = null;
var end_period = null;

var selected_type = null;
var selected_timelogs = [];

var employee_id = null;
var n_days = null;
var item_id = null;
var tax_up = 0;

var editable_cell = {
    id: null,
    cell: null
};

var summary_id = null;

var list_active = {
    id: null, 
    title: null, 
    sched: null,
    sched_type: null,
    workflow_status: 0
};

var filter_summary = {
    _token: $('meta[name="csrf-token"]').attr('content'),
    schedule_type: null,
    status: null,
    start_date: null,
    end_date: null,
    period_order: 'desc',
    keyword: ''
};

function setupPayrunEntriesControl(tableSelector) {
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

$(function() {
    modal_content = 'payrun';
    module_url = '/payroll/payrun';
    module_type = 'transaction_2';
    page_title = "Add Payrun";

    scion.centralized_button(false, true, true, true);

    $('#payrun-search-btn').on('click', function() {
        getPayrun();
    });

    $('#payrun-reset-btn').on('click', function() {
        $('#f-payment-status').val('');
        $('#f-payment-start').val('');
        $('#f-payment-end').val('');
        $('#f-payment-sort').val('');
        $('#f-period-order').val('desc');
        $('#f-payment-keyword').val('');
        getPayrun();
    });

    $('#f-payment-keyword').on('keypress', function(e) {
        if (e.which === 13) {
            getPayrun();
        }
    });

    getPayrun();
});


// DEFAULT FUNCTION
function success(record) {
    switch(actions) {
        case 'save':
            switch(modal_content) {
                case 'payrun':
                    $('#payrun_table').DataTable().draw();
                    scion.create.sc_modal('payrun_form').hide('all', modalHideFunction);

                    break;
                case 'cash_advance':
                    $('#cash-advance-table').DataTable().draw();
                    selectedList(list_active.id, start_period, end_period, list_active.title, list_active.sched, list_active.sched_type);
                    scion.create.sc_modal('ca_form').hide();
                    break;
                case 'allowance':
                    $('#allowance-table').DataTable().draw();
                    selectedList(list_active.id, start_period, end_period, list_active.title, list_active.sched, list_active.sched_type);
                    scion.create.sc_modal('amount_form').hide();
                    break;
            }

            break;
        case 'update':
            switch(modal_content) {
                case 'payrun':
                    $('#payrun_table').DataTable().draw();
                    scion.create.sc_modal('payrun_form').hide('all', modalHideFunction);
                    break;

                case 'allowance':
                    $('#allowance-table').DataTable().draw();
                    selectedList(list_active.id, start_period, end_period, list_active.title, list_active.sched, list_active.sched_type);
                    scion.create.sc_modal('amount_form').hide();
                    break;
                    
                case 'cash_advance':
                    $('#cash-advance-table').DataTable().draw();
                    selectedList(list_active.id, start_period, end_period, list_active.title, list_active.sched, list_active.sched_type);
                    scion.create.sc_modal('ca_form').hide();
                    break;

                case 'payrun_amount':
                    // $('#allowance-table').DataTable().draw();
                    selectedList(list_active.id, start_period, end_period, list_active.title, list_active.sched, list_active.sched_type);
                    scion.create.sc_modal('editableCell').hide('all', closeEditable);
                    break;
            }
            break;
    }
}

function error(record) {
    switch(modal_content) {
        case 'payrun':
            var msg = (record && record.responseJSON && record.responseJSON.responseJSON && record.responseJSON.responseJSON.message)
                ? record.responseJSON.responseJSON.message
                : 'Payroll with the same period coverage already exists.';
            toastr.error(msg);

            break;
    }
}

function delete_success() {}

function delete_error() {}

function generateData() {
    switch(actions) {
        case 'save':
            switch(modal_content) {
                case 'payrun':
                    form_data = {
                        _token: _token,
                        payment_schedule: selected_type,
                        sequence_title: $('#payment_schedule').val(),
                        period_start: $('#period_start').val(),
                        payroll_period: $('#payroll_period').val(),
                        pay_date: $('#pay_date').val()
                    };
                    break;
                case 'allowance':
                    form_data = {
                        _token: _token,
                        employee_id: employee_id,
                        allowance_id: $('#allowance_id').val(),
                        amount: $('#amount').val(),
                        sequence_no: summary_id,
                        date: null,
                        days: $('#no_days').val(),
                        total_amount: 0,
                    };
                    break;
                case 'cash_advance':
                    form_data = {
                        _token: _token,
                        employee_id: employee_id,
                        amount: $('#ca_amount').val(),
                        date: $('#ca_date').val(),
                        purpose: $('#ca_purpose').val(),
                        summary_id: summary_id
                    };
                    break;
            }
            break;
        case 'update':
            switch(modal_content) {
                case 'payrun':
                    form_data = {
                        _token: _token,
                        payment_schedule: selected_type,
                        sequence_title: $('#payment_schedule').val(),
                        period_start: $('#period_start').val(),
                        payroll_period: $('#payroll_period').val(),
                        pay_date: $('#pay_date').val()
                    };
                    break;
                case 'allowance':
                    form_data = {
                        _token: _token,
                        employee_id: employee_id,
                        allowance_id: $('#allowance_id').val(),
                        amount: $('#amount').val(),
                        sequence_no: summary_id,
                        date: null,
                        days: $('#no_days').val(),
                        total_amount: 0,
                    };
                    break;
                case 'payrun_amount':
                    form_data = {
                        _token: _token,
                        cell: editable_cell.cell,
                        amount: $('#editable_amount').val()
                    };
                    break;
                case 'cash_advance':
                    form_data = {
                        _token: _token,
                        employee_id: employee_id,
                        amount: $('#ca_amount').val(),
                        date: $('#ca_date').val(),
                        purpose: $('#ca_purpose').val(),
                        summary_id: summary_id
                    };
                    break;
            }
            break;
    }

    return form_data;
}

function generateDeleteItems() {}

function modalShowFunction() {
    scion.centralized_button(true, false, true, true);
}

function modalHideFunction() {
    scion.centralized_button(false, true, true, true);
}


// OTHER FUNCTION
function selectPaymentSchedule() {
    $.get(`/payroll/payrun/get-sched-type/${$('#payment_schedule').val()}`, (response)=>{
        selected_type = response.calendar.type;
    });
}

function getWeekDate(day){
    var weekdate = moment()
        .startOf('month')
        .day(day);
    if (weekdate.date() > 7) weekdate.add(7,'d');
    var month = weekdate.month();
    var dateList = [];

    while(month === weekdate.month()){
        dateList.push(moment(weekdate.toString()).format('MMM DD'));
        weekdate.add(7,'d');
    }

    return dateList;
}

function getPayrun() {
    const periodTypeFilter = $('#f-payment-sort').val()!==''?$('#f-payment-sort').val():null;

    filter_summary.schedule_type = periodTypeFilter;
    filter_summary.status = $('#f-payment-status').val()!==''?$('#f-payment-status').val():null;
    filter_summary.start_date = $('#f-payment-start').val();
    filter_summary.end_date = $('#f-payment-end').val();
    filter_summary.period_order = $('#f-period-order').val()!==''?$('#f-period-order').val():'desc';
    filter_summary.keyword = $('#f-payment-keyword').val()!==''?$('#f-payment-keyword').val():null;

    if ($.fn.DataTable.isDataTable('#payrun_table')) {
        $('#payrun_table').DataTable().destroy();
    }

    $('#payrun_table').DataTable({
        responsive: true,
        serverSide: true,
        ordering: false,
        scrollX: true,
        scrollY: '52vh',
        scrollCollapse: true,
        dom: 't<"row mt-2 align-items-center"<"col-sm-6"l><"col-sm-6 text-right"p>>',
        pageLength: 10,
        ajax: {
            url: '/payroll/payrun/get',
            type: 'POST',
            data: filter_summary
        },
        initComplete: function() {
            setupPayrunEntriesControl('#payrun_table');
        },
        drawCallback: function() {
            refreshPayrunListNetTotals(this.api());
        },
        columns: [
            {
                data: 'period_start',
                title: "PAYRUN DETAILS",
                className: "name-td",
                render: function(data, type, row, meta) {
                    var schedule = '';
                    var status = '';
                    var approvedCount = parseInt(row.no_of_employee || 0, 10);
                    var totalCount = parseInt(row.total_of_employee || 0, 10);
                    var pendingCount = parseInt(row.pending_employee || 0, 10);
                    var detailStatus = '';

                    switch(row.schedule_type) {
                        case 0:
                            schedule = "13th MONTH PAY";
                            break;
                        case 1:
                            schedule = "MONTHLY";
                            break;
                        case 2:
                            schedule = "SEMI-MONTHLY";
                            break;
                        case 3:
                            schedule = "SEMI-WEEKLY";
                            break;
                        case 4:
                            schedule = "WEEKLY";
                            break;
                    }
                    
                    switch(parseInt((row.workflow_status !== null && row.workflow_status !== undefined) ? row.workflow_status : 0, 10)) {
                        case 0:
                            status = "<span class='text-primary' style='font-weight:bold;'>DRAFT</span>";
                            break;
                        case 4:
                            status = "<span class='text-secondary' style='font-weight:bold;'>SUBMITTED FOR AUDIT</span>";
                            break;
                        case 1:
                            status = "<span class='text-info' style='font-weight:bold;'>SUBMITTED FOR APPROVAL</span>";
                            break;
                        case 2:
                            status = "<span class='text-success' style='font-weight:bold;'>APPROVED</span>";
                            break;
                        case 3:
                            status = "<span class='text-warning' style='font-weight:bold;'>SUBMITTED FOR PAYMENT</span>";
                            break;
                        default:
                            status = "<span class='text-primary' style='font-weight:bold;'>DRAFT</span>";
                            break;
                    }

                    var sideActions = parseInt((row.workflow_status !== null && row.workflow_status !== undefined) ? row.workflow_status : 0, 10) === 0
                        ? `<div class="side-action">
                                <a href="#" onclick="editPayrun(${row.id})" class="a-edit">EDIT</a> 
                                <a href="#" onclick="deleteConfirm(${row.id})" class="a-delete">DELETE</a>
                           </div>`
                        : '';

                    if (totalCount === 0) {
                        detailStatus = "<span class='text-muted'>NO DETAILS</span>";
                    } else if (approvedCount === totalCount) {
                        detailStatus = "<span class='text-success' style='font-weight:bold;'>PAYROLL COMPLETED</span>";
                    } else if (approvedCount > 0) {
                        detailStatus = "<span class='text-warning' style='font-weight:bold;'>PARTIALLY APPROVED</span>";
                    } else {
                        detailStatus = "<span class='text-primary' style='font-weight:bold;'>FOR APPROVAL</span>";
                    }

                    return `<div class="row lst-payrun" data-summary-id="${row.id}" onclick="selectedList('${row.id}', '${row.period_start}', '${row.payroll_period}', '${row.title}', '${schedule}', ${row.schedule_type})">
                        <div class="col-md-9">
                            <div class="lst-sequence-title">${row.title}</div>
                            <span class="lst-period">${moment(row.period_start).format('MMM DD, YYYY') + "-" + moment(row.payroll_period).format('MMM DD, YYYY')}</span> | <span class="lst-sequence-no">Sequence No: ${row.sequence_no || '-'}</span> | <span>${status}</span> | <span class="lst-employee">${approvedCount} approved of ${totalCount} employee(s)</span> | <span class="lst-employee">pending: ${pendingCount}</span> | <span class="lst-employee lst-net" id="lst-net-${row.id}">net: ${scion.currency(parseFloat(row.net_amount || 0))}</span><br>${detailStatus}
                        </div>
                        <div class="col-md-3 text-right">
                            ${status}
                            ${sideActions}
                        </div>
                    </div>`;
                }
            }
        ]
    });
}

function computeDetailNetPay(item, schedType) {
    var worked_days = (item.for_fixed !== null ? parseFloat(item.for_fixed) : parseFloat((item.timelogs || []).length));
    var daily_rate = item.daily !== "0" ? parseFloat(item.daily) : (item.employee && item.employee.compensations !== null ? parseFloat(item.employee.compensations.daily_salary) : 0);
    var hourly_rate = item.hourly !== "0" ? parseFloat(item.hourly) : (item.employee && item.employee.compensations !== null ? parseFloat(item.employee.compensations.hourly_salary) : 0);
    var monthly_rate = item.monthly !== "0" ? parseFloat(item.monthly) : (item.employee && item.employee.compensations !== null ? parseFloat(item.employee.compensations.monthly_salary) : 0);

    var late = parseFloat(item.late_hours || 0);
    var undertime = parseFloat(item.undertime || 0);
    var late_rate = (late / 60) * hourly_rate;
    var ut_rate = (undertime / 60) * hourly_rate;

    var basic_pay = (parseInt(schedType, 10) === 2 ? (monthly_rate / 2) : (worked_days * daily_rate));

    var holiday_rate = 0;
    if (item.holiday_data !== null && item.holiday_data !== undefined) {
        item.holiday_data.forEach(function(h_data) {
            holiday_rate += daily_rate * parseFloat(h_data.holiday_type.multiplier || 0);
        });
    }

    var ot_amount = parseFloat(item.ot_amount || 0);
    var allowance_amount = parseFloat(item.allowance_amount || 0);
    var leave_amount = parseFloat((item.leave_count || 0) * daily_rate);
    var absent_rate = parseFloat(item.absent_count || 0) * daily_rate;
    var tardiness_deduct = (late_rate + ut_rate);

    var gross_salary = (basic_pay + ot_amount + allowance_amount + leave_amount + holiday_rate) - (tardiness_deduct + absent_rate);

    var sss = parseFloat(item.sss || 0);
    var phic = parseFloat(item.philhealth || 0);
    var pagibig = parseFloat(item.pagibig || 0);
    var tax = parseFloat(item.tax || 0) !== 0 ? parseFloat(item.tax || 0) : parseFloat(item.tax_final || 0);
    var ca = parseFloat(item.ca || 0);

    var gross_deduction = sss + phic + pagibig + ca + tax;
    return gross_salary - gross_deduction;
}

function refreshPayrunListNetTotals(tableApi) {
    if (!tableApi || !$('#payrun_table').length) {
        return;
    }

    var rows = tableApi.rows({ page: 'current' }).data();
    for (var i = 0; i < rows.length; i++) {
        var row = rows[i];
        if (!row || !row.id) {
            continue;
        }

        (function(summaryId, startDate, endDate, schedType) {
            $.post('/payroll/payrun/get-details', {
                _token: _token,
                id: summaryId,
                start: startDate,
                end: endDate
            }).done(function(response) {
                var totalNet = 0;
                (response.details || []).forEach(function(item) {
                    totalNet += computeDetailNetPay(item, schedType);
                });
                $('#lst-net-' + summaryId).text('net: ' + scion.currency(totalNet));
            });
        })(row.id, row.period_start, row.payroll_period, row.schedule_type);
    }
}

function selectedList(id, start_date, end_date, title, sched, sched_type) {
    var td_details = '';
    
    start_period = start_date;
    end_period = end_date;

    list_active.id = id;
    list_active.title = title;
    list_active.sched = sched;
    list_active.sched_type = sched_type;
    list_active.workflow_status = 0;

    $('.calendar-title').text(title);
    $('.period-val').text(moment(start_period).format('MMM DD YYYY') + ' to ' + moment(end_period).format('MMM DD YYYY'));
    $('.cut-off-val').text(sched);
    $('.update-header').text(sched + ' BASIC PAY')

    $.post('/payroll/payrun/get-details', {
        _token: _token,
        id: id,
        start: start_period,
        end: end_period,
    }).done((response) => {
        const workflowStatus = response.summary && response.summary.workflow_status !== null
            ? parseInt(response.summary.workflow_status, 10)
            : 0;
        list_active.workflow_status = isNaN(workflowStatus) ? 0 : workflowStatus;

        updateWorkflowActions(list_active.workflow_status, response.allEmployeeApproved === true);

        var total = {
            work_days: 0,
            work_hours: 0,
            late: 0,
            absent: 0,
            late_days: 0,
            late_mins: 0,
            actual_hours: 0,
            allowance_amount: 0,
            basic_pay: 0,
            ot_hours: 0,
            ot_amount: 0,
            gross_salary: 0,
            sss: 0,
            phic: 0,
            pagibig: 0,
            tax: 0,
            gross_deduction: 0,
            netpay: 0,
            leave_count: 0,
            ca:0
        };

        response.details.forEach((item, index) => {
            

            var worked_days = (item.for_fixed !== null?item.for_fixed:parseInt(item.timelogs.length));
            var reg_hours = worked_days * 8;
            var holiday = item.holiday;
            var late = parseFloat(item.late_hours).toFixed(2);
            var undertime = parseFloat(item.undertime).toFixed(2);
            var tardiness_mins = parseFloat(item.late_hours + item.undertime).toFixed(2);
            var actual_hours = (reg_hours - late).toFixed(2);
            var daily_rate = item.daily !== "0"? parseFloat(item.daily):(item.employee.compensations!==null?item.employee.compensations.daily_salary:0);
            var hourly_rate = item.hourly !== "0"? parseFloat(item.hourly):(item.employee.compensations!==null?item.employee.compensations.hourly_salary:0);
            var monthly_rate = item.monthly !== "0"? parseFloat(item.monthly):(item.employee.compensations!==null?item.employee.compensations.monthly_salary:0);
            var late_rate = (late/60) * hourly_rate;
            var ut_rate = (undertime/60) * hourly_rate;
            var basic_pay = (sched_type === 2? (monthly_rate / 2) : (worked_days * daily_rate));
            
            var holiday_rate = 0;

            if(item.holiday_data !== null) {
                item.holiday_data.forEach(h_data => {
                    holiday_rate += daily_rate * parseFloat(h_data.holiday_type.multiplier)
                });
            }

            var ot_hours = parseFloat(item.ot_hours).toFixed(2);
            var ot_amount = parseFloat(item.ot_amount);

            var sss = parseFloat(item.sss);
            var phic = parseFloat(item.philhealth);
            var pagibig = parseFloat(item.pagibig);

            var allowance_amount = parseFloat(item.allowance_amount);
            // var allowance_daily = allowance_amount !== 0?(allowance_amount / (worked_days !== 0?worked_days:1)):0;
            var allowance_daily = allowance_amount !== 0?(allowance_amount / 13):0;

            var absent = item.absent_count;
            var absent_rate = absent * daily_rate;

            var tardiness_deduct = (late_rate + ut_rate);
            
            var leave_amount = parseFloat(item.leave_count * daily_rate);
            
            var gross_salary = (basic_pay + ot_amount + allowance_amount + leave_amount + holiday_rate) - (tardiness_deduct + absent_rate);
            
            var tax = item.tax !== 0?item.tax:item.tax_final;
            var ca = parseFloat(item.ca);

            var gross_deduction = (parseFloat(sss) + parseFloat(phic) + parseFloat(pagibig) + ca + tax);
            var net_pay = (gross_salary - gross_deduction);


            td_details += `<tr>
                ${item.status === 0?`<td style="text-align:center;" class="action-item-btn" id="approve-cell" onclick="approveDetails(${item.id})"><i class="fas fa-check"></i></td>`:`<td style="text-align:center;" class="action-item-btn" id="cross-cell" onclick="crossDetails(${item.id})"><i class="fas fa-times"></i></td>`}
                <td style="text-align:center;" class="action-item-btn" id="view-clock" onclick="showPayDetails(${item.employee_id}, ${item.id})"><i class="fas fa-clock"></i></td>
                <td style="text-align:center;" class="action-item-btn" id="payslip-cell" onclick="printPayslip(${item.id}, ${item.employee_id}, '${sched}')"><i class="fas fa-receipt"></i></td>
                <td>${item.employee.firstname + " " + item.employee.lastname}</td>
                <td style="text-align:center;" class="status-out-${item.status}">${item.status === 0?"DRAFT":item.status === 1?"APPROVED":"DECLINED"}</td>
                <td style="text-align:center;">${worked_days}</td>
                <td style="text-align:center;">${holiday}<br>(${scion.currency(holiday_rate)})</td>
                <td style="text-align:center;" class="td-editable" onclick="editCell(${item.id}, 'monthly', ${monthly_rate})">${scion.currency(monthly_rate)}</td>
                <td style="text-align:center;" class="td-editable" onclick="editCell(${item.id}, 'daily', ${daily_rate})">${scion.currency(daily_rate)}</td>
                <td style="text-align:center;" class="td-editable" onclick="editCell(${item.id}, 'hourly', ${hourly_rate})">${scion.currency(hourly_rate)}</td>
                <td style="text-align:center;">${scion.currency(basic_pay)}</td>
                <td style="text-align:center;" class="allowance-act" onmouseenter="allowanceHover(${index})" onmouseleave="allowanceLeave(${index})" onclick="selectedAllowance(${id}, ${item.employee_id}, ${worked_days})">${scion.currency(allowance_daily)}</td>
                <td style="text-align:center;" class="allowance-act" onmouseenter="allowanceHover(${index})" onmouseleave="allowanceLeave(${index})" onclick="selectedAllowance(${id}, ${item.employee_id}, ${worked_days})">${scion.currency(allowance_amount)}</td>
                <td style="text-align:center;">${ot_hours}</td>
                <td style="text-align:center;">${scion.currency(ot_amount)}</td>
                
                <td style="text-align:center;">${absent}</td>
                <td style="text-align:center;">${scion.currency(absent_rate)}</td>
                <td style="text-align:center;">${tardiness_mins}</td>
                <td style="text-align:center;">${scion.currency(tardiness_deduct)}</td>
                <td style="text-align:center;">${item.leave_count}</td>
                <td style="text-align:center;" class="td-gross">${scion.currency(gross_salary)}</td>
                <td style="text-align:center;" class="td-editable" onclick="editCell(${item.id}, 'sss', ${sss})">${scion.currency(sss)}</td>
                <td style="text-align:center;" class="td-editable" onclick="editCell(${item.id}, 'philhealth', ${phic})">${scion.currency(phic)}</td>
                <td style="text-align:center;" class="td-editable" onclick="editCell(${item.id}, 'pagibig', ${pagibig})">${scion.currency(pagibig)}</td>
                <td style="text-align:center;" class="td-editable" onclick="editCell(${item.id}, 'tax', ${tax})">${scion.currency(tax)}</td>
                <td style="text-align:center;" class="cash-advance-td" onclick="selectedCashAdvance(${id}, ${item.employee_id})">${scion.currency(ca)}</td>
                <td style="text-align:center;" class="td-gross">${scion.currency(gross_deduction)}</td>
                <td style="text-align:center;">${scion.currency(net_pay)}</td>
            </tr>`;

            {/* <td style="text-align:center;" class="action-item-btn" id="ot-cell" onclick="viewOTRequest(${item.employee_id})"><i class="fas fa-list"></i></td> */}

            total.work_days += worked_days;
            total.work_hours += reg_hours;
            total.absent += parseFloat(absent_rate);
            total.late += parseFloat(tardiness_deduct);
            total.late_mins += parseFloat(late);
            total.late_days += parseFloat(absent);
            total.actual_hours += parseFloat(actual_hours);
            total.basic_pay += basic_pay;
            total.allowance_amount += allowance_amount;
            total.ot_hours += parseFloat(ot_hours);
            total.ot_amount += parseFloat(ot_amount);
            total.gross_salary += parseFloat(gross_salary);
            total.sss += parseFloat(sss);
            total.phic += parseFloat(phic);
            total.pagibig += parseFloat(pagibig);
            total.tax += parseFloat(tax);
            total.gross_deduction += parseFloat(gross_deduction);
            total.netpay += parseFloat(net_pay);
            total.leave_count += item.leave_count;
            total.ca += ca;

        });

        $('#total-work-hours').text(total.work_hours);
        $('#total-absent-amount').text(scion.currency(total.absent));
        $('#total-late-amount').text(scion.currency(total.late));
        $('#total-late-days').text(total.late_days);
        $('#total-late-mins').text(total.late_mins.toFixed(2));
        $('#total-actual-hours').text(total.actual_hours.toFixed(2));
        $('#total-basic-pay').text(scion.currency(total.basic_pay));
        $('#total-ot-hours').text(total.ot_hours.toFixed(2));
        $('#total-ot-amount').text(scion.currency(total.ot_amount));
        $('#total-gross-salary').text(scion.currency(total.gross_salary));
        $('#total-sss').text(scion.currency(total.sss));
        $('#total-phic').text(scion.currency(total.phic));
        $('#total-pagibig').text(scion.currency(total.pagibig));
        $('#total-wt').text(scion.currency(total.tax));
        $('#total-gross-deduction').text(scion.currency(total.gross_deduction));
        $('#total-net-pay').text(scion.currency(total.netpay));
        $('#total-allowance').text(scion.currency(total.allowance_amount));
        $('#total-leave-amount').text(total.leave_count);
        $('#total-ca').text(scion.currency(total.ca));
        $(`#lst-net-${id}`).text(`net: ${scion.currency(total.netpay)}`);

        $('#employee-table-list tbody').html(td_details);
        loadPayrunHistoryNotes(id);

        $('#first').css('display', 'none');
        $('#second').css('display', 'block');
        $('#backBtn').css('display', 'inline-block');
        $('#workflowControls').css('display', 'inline-flex');
        $('#filter-dialog').css('display', 'none');
        $('.btn-filter').css('display', 'none');
    });

    scion.centralized_button(true, true, true, true);

}

function loadPayrunHistoryNotes(summaryId) {
    if (!summaryId) return;

    $('#payrun-history-list').html('<div class="text-muted">Loading history...</div>');
    $('#payrun-notes-list').html('<div class="text-muted">Loading notes...</div>');

    $.get('/payroll/payrun/history-notes/' + summaryId, function(response) {
        var historyHtml = '';
        (response.history || []).forEach(function(item) {
            historyHtml += `<div class="hn-item">
                <div><b>${item.action || '-'}</b> - ${item.description || '-'}</div>
                <div class="hn-meta">${item.by || 'User'} | ${item.at ? moment(item.at).format('MMM DD, YYYY hh:mm A') : '-'}</div>
            </div>`;
        });
        $('#payrun-history-list').html(historyHtml || '<div class="text-muted">No history yet.</div>');

        var notesHtml = '';
        (response.notes || []).forEach(function(item) {
            notesHtml += `<div class="hn-item">
                <div>${item.note || '-'}</div>
                <div class="hn-meta">${item.by || 'User'} | ${item.at ? moment(item.at).format('MMM DD, YYYY hh:mm A') : '-'}</div>
            </div>`;
        });
        $('#payrun-notes-list').html(notesHtml || '<div class="text-muted">No notes yet.</div>');
    }).fail(function() {
        $('#payrun-history-list').html('<div class="text-danger">Unable to load history.</div>');
        $('#payrun-notes-list').html('<div class="text-danger">Unable to load notes.</div>');
    });
}

function addPayrunNote() {
    if (!list_active.id) {
        toastr.error('Please select a payroll transaction first.');
        return;
    }

    var note = ($('#payrun-note-input').val() || '').trim();
    if (!note) {
        toastr.error('Please enter a note.');
        return;
    }

    $.post('/payroll/payrun/add-note', { _token: _token, summary_id: list_active.id, note: note }, function() {
        $('#payrun-note-input').val('');
        loadPayrunHistoryNotes(list_active.id);
        toastr.success('Note added.');
    }).fail(function(response) {
        toastr.error(response.responseJSON && response.responseJSON.message ? response.responseJSON.message : 'Failed to add note.');
    });
}

function backPressed() {
    $('#first').css('display', 'block');
    $('#second').css('display', 'none');
    $('#backBtn').css('display', 'none');
    $('#workflowControls').css('display', 'none');
    $('.btn-filter').css('display', 'inline-block');
    $('#filter-dialog').css('display', 'flex');
    
    scion.centralized_button(false, true, true, true);
}

function getWorkflowLabel(status) {
    switch (parseInt(status, 10)) {
        case 4:
            return "SUBMITTED FOR AUDIT";
        case 1:
            return "SUBMITTED FOR APPROVAL";
        case 2:
            return "APPROVED";
        case 3:
            return "SUBMITTED FOR PAYMENT";
        default:
            return "DRAFT";
    }
}

function getWorkflowClass(status) {
    switch (parseInt(status, 10)) {
        case 4:
            return "wf-audit";
        case 1:
            return "wf-submitted";
        case 2:
            return "wf-approved";
        case 3:
            return "wf-payment";
        default:
            return "wf-preparing";
    }
}

function updateWorkflowActions(status, allEmployeeApproved) {
    const label = getWorkflowLabel(status);
    const className = getWorkflowClass(status);

    $('#workflowStatusBadge')
        .removeClass('wf-preparing wf-audit wf-submitted wf-approved wf-payment')
        .addClass(className)
        .text(label);

    $('#submitAuditBtn').toggle(status === 0);
    $('#submitApprovalBtn').toggle(status === 4);
    $('#approveSummaryBtn').toggle(status === 1);
    $('#revertSummaryBtn').toggle(status === 4);
    $('#submitPaymentBtn').toggle(status === 2);

    const canSubmitPayment = status === 2 && allEmployeeApproved === true;
    $('#submitPaymentBtn').prop('disabled', !canSubmitPayment);
    $('#submitPaymentBtn').attr('title', canSubmitPayment ? '' : 'All employee payroll entries must be approved first.');
}

function submitForAudit() {
    if (!list_active.id) return;
    if (confirm("Submit this payroll for audit?") !== true) return;

    $.post('/payroll/payrun/submit-for-audit', { _token: _token, id: list_active.id }, function() {
        $('#payrun_table').DataTable().draw();
        selectedList(list_active.id, start_period, end_period, list_active.title, list_active.sched, list_active.sched_type);
    }).fail(function(response) {
        toastr.error(response.responseJSON && response.responseJSON.message ? response.responseJSON.message : 'Failed to submit for audit.');
    });
}

function showPayDetails(emp_id, id) {
    employee_id = emp_id;
    item_id = id;

    $.post(`/payroll/payrun/get-employee/`,{ _token: _token, employee_id: employee_id, id: id, start: start_period, end: end_period}, function(response) {
        $('#emp_no').text(response.employee.employee_no);
        $('#emp_name').text(response.employee.firstname + " " + response.employee.lastname);
        $('#emp_status').text(response.summary.status === 0?"DRAFT":response.summary.status === 1?"APPROVED":"DECLINED");

        $('#monthly_salary').text(scion.currency(response.employee.compensations !== null?response.employee.compensations.monthly_salary:0));
        $('#daily_salary').text(scion.currency(response.employee.compensations !== null?response.employee.compensations.daily_salary:0));
        $('#hourly_rate').text(scion.currency(response.employee.compensations !== null?response.employee.compensations.hourly_salary:0));

        $('#regular_hours').text(parseFloat(response.employee.timelogs.length * 8).toFixed(2));

        selected_timelogs = response.employee.timelogs;

        dateList(response.employee.works_calendar, response.holiday, response.leave, response.employee.employment_type);

        scion.create.sc_modal("details_form", "Details").show();
    });
}

function dateList(data, holiday, leave, e_type) {
    var dateList = "";
    var startDate = new Date(start_period);
    var endDate = new Date(end_period);

    var total_late = 0;
    var total_ut = 0;
    var total_ot = 0;
    var total_wh = 0;

    var weekdays = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

    for (var currentDate = new Date(startDate); currentDate <= endDate; currentDate.setDate(currentDate.getDate() + 1)) {
        var formattedDate = currentDate.toISOString().split('T')[0];
        var dayName = weekdays[currentDate.getDay()];

        var result = selected_timelogs.find(item => item.date === formattedDate);
        var schedule_time = data && data[dayName.toLowerCase() + "_start_time"] ? data[dayName.toLowerCase() + "_start_time"] : null;
        var schedule_e_time = data && data[dayName.toLowerCase() + "_end_time"] ? data[dayName.toLowerCase() + "_end_time"] : null;

        var total_hours = (result && result.time_in !== null) ? 8 : 0;
        var late_hours = parseFloat(computeLate(schedule_time || "00:00", result && result.time_in ? moment(result.time_in).format('HH:mm') : "00:00"));
        var under_time = parseFloat(computeUndertime(schedule_e_time || "00:00", result && result.time_out ? moment(result.time_out).format('HH:mm') : "00:00"));
        var overtime = 0;
        var total_working_hours = parseFloat(((total_hours + overtime) - (late_hours + under_time)));

        total_late += parseFloat(late_hours);
        total_ut += parseFloat(under_time);
        total_ot += parseFloat(overtime);
        total_wh += parseFloat(total_working_hours);

        var holiday_r = holiday.find(item => item.date === formattedDate);

        var statusClass = "dt-no";
        var statusText = "NO SCHEDULE";

        if (schedule_time) {
            if (result && result.time_in) {
                statusClass = holiday_r ? "dt-holiday" : "dt-work";
                statusText = holiday_r ? "HOLIDAY" : "WORK";
            } else {
                statusClass = holiday_r ? "dt-holiday" :  (e_type === 'fixed_rate' ? "dt-work" : "dt-absent");
                statusText = holiday_r ? "HOLIDAY" : (e_type === 'fixed_rate' ? "WORK" : "ABSENT");
            }
        } else {
            statusClass = "dt-off";
            statusText = "OFF";
        }

        let timeIn = result && result.time_in ? moment(result.time_in).format('HH:mm') : (e_type === 'fixed_rate' && schedule_time ? schedule_time : "");
        let breakIn = result && result.break_in ? moment(result.break_in).format('HH:mm') : "";
        let breakOut = result && result.break_out ? moment(result.break_out).format('HH:mm') : "";
        let timeOut = result && result.time_out ? moment(result.time_out).format('HH:mm') : (e_type === 'fixed_rate' && schedule_e_time ? schedule_e_time : "");

        dateList += `<tr id="tr-${formattedDate}" data-date="${formattedDate}" data-id="${result && result.id !== null ? result.id : ''}">
            <td class="data-date">${moment(formattedDate).format('MMM DD, YYYY')}</td>
            <td>${dayName}</td>
            <td class='dt dt-no ${statusClass}'>${statusText}</td>

            <td class="td-time" id="time-in-${formattedDate}">
                <input type="text" value="${timeIn}" placeholder="HH:MM" pattern="^([01]\\d|2[0-3]):([0-5]\\d)$" maxlength="5" required onkeydown="return /^[0-9]$/.test(event.key) || ['Backspace','ArrowLeft','ArrowRight','Tab','Delete'].includes(event.key)" oninput="formatTimeInput(this)" />
            </td>

            <td class="td-time" id="break-in-${formattedDate}">
                <input type="text" value="${breakIn}" placeholder="HH:MM" pattern="^([01]\\d|2[0-3]):([0-5]\\d)$" maxlength="5" required onkeydown="return /^[0-9]$/.test(event.key) || ['Backspace','ArrowLeft','ArrowRight','Tab','Delete'].includes(event.key)" oninput="formatTimeInput(this)" />
            </td>

            <td class="td-time" id="break-out-${formattedDate}">
                <input type="text" value="${breakOut}" placeholder="HH:MM" pattern="^([01]\\d|2[0-3]):([0-5]\\d)$" maxlength="5" required onkeydown="return /^[0-9]$/.test(event.key) || ['Backspace','ArrowLeft','ArrowRight','Tab','Delete'].includes(event.key)" oninput="formatTimeInput(this)" />
            </td>

            <td class="td-time" id="time-out-${formattedDate}">
                <input type="text" value="${timeOut}" placeholder="HH:MM" pattern="^([01]\\d|2[0-3]):([0-5]\\d)$" maxlength="5" required onkeydown="return /^[0-9]$/.test(event.key) || ['Backspace','ArrowLeft','ArrowRight','Tab','Delete'].includes(event.key)" oninput="formatTimeInput(this)" />
            </td>

            <td class="ot-time" id="ot-in-${formattedDate}">${result && result.ot_in ? moment(result.ot_in).format('hh:mm A') : "-"}</td>
            <td class="ot-time" id="ot-out-${formattedDate}">${result && result.ot_out ? moment(result.ot_out).format('hh:mm A') : "-"}</td>
            <td>${total_hours}</td>
            <td>${late_hours}</td>
            <td>${under_time}</td>
            <td>${overtime}</td>
            <td>${total_working_hours}</td>
        </tr>`;
    }

    $('#late_hours').text(`(-${(total_late + total_ut).toFixed(2)})`);
    $('#overtime').text(total_ot.toFixed(2));
    $('#working_hours').text(total_wh.toFixed(2));
    $('#timesheet tbody').html(dateList);

    leave.forEach(item => {
        var start = new Date(item.start_date);
        var end = new Date(item.end_date);

        for (var currentDate = new Date(start); currentDate <= end; currentDate.setDate(currentDate.getDate() + 1)) {
            var new_date = moment(currentDate).format('YYYY-MM-DD');
            $(`#tr-${new_date} .dt`).removeClass('dt-absent dt-work dt-holiday dt-off').addClass('dt-leave').text('LEAVE');
            $(`#tr-${new_date} input`).prop('disabled', true);
        }
    });
}


function formatTimeInput(input) {
    let value = input.value.replace(/[^0-9]/g, ''); 

    if (value.length >= 3) {
        value = value.slice(0, 2) + ':' + value.slice(2, 4);
    }

    input.value = value;

    const pattern = /^([01]\d|2[0-3]):([0-5]\d)$/;
    input.style.borderColor = pattern.test(value) ? 'green' : 'red';
}

function viewOTRequest(id) {
    $.post('/payroll/payrun/get-ot-list', { _token: _token, employee_id: id, start: start_period, end: end_period }).done((response) => {
        var html = '';

        response.ot.forEach(item => {
            html += `<tr>
                        <td>${item.reason}</td>
                        <td class="status-viewer status-${item.status}">${item.status}</td>
                        <td>${moment(item.ot_date).format('MMM DD, YYYY')}</td>
                        <td>${moment(item.start_time).format('hh:mm A') + "-" + moment(item.end_time).format('hh:mm A')}</td>
                    </tr>`;
        });

        $('#ot-request-table tbody').html(html);

        scion.create.sc_modal("ot_request_list", "Overtime Request List").show();
    });
}

function saveUpdate() {
    var submit_data = [];
    
    submit_data = [];

    $('#timesheet tbody tr').each((index, row_el) => {
        var date_v = $(`#${row_el.id}`).attr('data-date');
        var timelog_id = $(`#${row_el.id}`).attr('data-id');
        var time_in = $(`#time-in-${date_v} input`).val();
        var time_out = $(`#time-out-${date_v} input`).val();
        var break_in = $(`#break-in-${date_v} input`).val();
        var break_out = $(`#break-out-${date_v} input`).val();

        submit_data.push({
            id: timelog_id,
            date: date_v,
            time_in: time_in,
            time_out: time_out,
            break_in: break_in,
            break_out: break_out
        });
    });

    $.post('/payroll/payrun/save-update', {_token:_token, emp_id: employee_id, data: submit_data}, function(response) {
        toastr.success('Successfully Saved');
        selectedList(list_active.id, start_period, end_period, list_active.title, list_active.sched, list_active.sched_type);
        showPayDetails(employee_id, item_id);
    });
}

function computeLate(entry, schedule) {
    var output = null;

    console.log(entry, schedule);
    if (entry && schedule) {
        let workDate = new Date("2025-01-01T" + entry + ":00");
        let attendanceDate = new Date("2025-01-01T" + schedule + ":00");

        if (attendanceDate > workDate) {
            let diffMs = attendanceDate - workDate;
            let diffHrs = diffMs / (1000 * 60 * 60);

            output = diffHrs.toFixed(2)
        } else {
            output = 0.00;
        }
    }

    return output;
}
function computeUndertime(scheduleOut, actualOut) {
    let output = null;

    console.log(scheduleOut, actualOut);

    if (scheduleOut && actualOut) {
        if(actualOut !== "00:00") {
            let scheduledDate = new Date("2025-01-01T" + scheduleOut + ":00");
            let actualDate = new Date("2025-01-01T" + actualOut + ":00");

            if (actualDate < scheduledDate) {
                let diffMs = scheduledDate - actualDate;
                let diffHrs = diffMs / (1000 * 60 * 60);

                output = diffHrs.toFixed(2);
            } else {
                output = 0.00;
            }
        }
        else {
            output = 0.00;
        }
    }

    return output;
}

function deleteConfirm(id) {
    event.stopPropagation();

    if (confirm("Are you sure you want to delete this record?") == true) {
        $.post('/payroll/payrun/delete-record', { _token: _token, id: id }, (response) => {
            $('#payrun_table').DataTable().draw();
        });
    }
}

function editPayrun(id) {
    event.stopPropagation();
    actions = 'update';
    record_id = id;


    $.get(`/payroll/payrun/edit/${id}`, (response) => {
        scion.centralized_button(true, false, true, true);
        $('#payment_schedule').val(response.payrun.sequence_title);
        $('#period_start').val(response.payrun.period_start);
        $('#payroll_period').val(response.payrun.payroll_period);
        $('#period_start').val(response.payrun.period_start);
        $('#pay_date').val(response.payrun.pay_date);
        selectPaymentSchedule();
        scion.create.sc_modal("payrun_form", "Update Payrun").show();
    });

}

function allowanceHover(i) {
    $(`#employee-table-list tr:nth-child(${i+1}) .allowance-act`).addClass('hover');
}

function allowanceLeave(i) {
    $(`#employee-table-list tr:nth-child(${i+1}) .allowance-act`).removeClass('hover');
}

function selectedAllowance(id, emp_id, no_days) {
    summary_id = id;
    employee_id = emp_id;
    n_days = no_days;
    
    modal_content = 'allowance';
    module_url = '/payroll/allowance_setup';

    if ($.fn.DataTable.isDataTable('#allowance-table')) {
        $('#allowance-table').DataTable().destroy();
    }

    scion.create.table(
        'allowance-table',  
        module_url + '/get-by-sequence/' + summary_id + '/' + emp_id, 
        [
            { data: null, title: "", render: function(data, type, row, meta) {
                return `<a href="#" onclick="editAllowance(${row.id})"><i class="fas fa-edit"></i></a> <a href="#" onclick="deleteAllowance(${row.id})"><i class="fas fa-trash"></i></a>`;
            }},
            { data: 'allowances.name', title: "Allowance Type" },
            { data: 'days', title: "No. of Days" },
            { data: 'amount', title: "Amount", render: function(data, type, row, meta) {
                return scion.currency(data);
            }},
        ], '', []
    );
    
    scion.create.sc_modal("allowance_list", "Allowance").show();
}

function addAllowance() {
    actions = 'save';
    record_id = null;

    $('#allowance_id').val('');
    $('.allowance-amount').text(scion.currency(0));
    $('.allowance-amount').attr('data-val', 0);
    $('#no_days').val(n_days);

    countTotalAmount();

    scion.centralized_button(true, false, true, true);
    scion.create.sc_modal("amount_form", "Allowance").show();
}

function editAllowance(id) {
    actions = 'update';
    record_id = id;

    $.get(`${module_url}/edit/${id}`, function(response) {
        console.log(response);
        $('#allowance_id').val(response.allowance.allowance_id);
        $('#no_days').val(response.allowance.days);
        $('#amount').val(response.allowance.amount);

        getAllowanceAmount();

        scion.centralized_button(true, false, true, true);
        scion.create.sc_modal("amount_form", "Allowance").show();
    });

}

function deleteAllowance(id) {
    event.stopPropagation();

    if (confirm("Are you sure you want to delete this record?") == true) {
        $.post(`${module_url}/destroy`, { _token: _token, id: id }, (response) => {
            $('#allowance-table').DataTable().draw();
            selectedList(list_active.id, start_period, end_period, list_active.title, list_active.sched, list_active.sched_type);
        });
    }
}

function getAllowanceAmount() {
    $.get('/payroll/allowance/get-amount/' + $('#allowance_id').val(), (response)=>{
        $('.allowance-amount').text(scion.currency(response.allowance.amount));
        $('.allowance-amount').attr('data-val', response.allowance.amount);

        countTotalAmount();
    });
}

function countTotalAmount() {
    $('#amount').val(parseFloat($('.allowance-amount').attr('data-val')) * parseFloat($('#no_days').val()));
}

function approveDetails(id) {
    if (confirm("Are you sure you want to appprove this record?") == true) {
        $.post('/payroll/payrun/approve-details', { _token: _token, id: id }, (response) => {
            $('#payrun_table').DataTable().draw();
            selectedList(list_active.id, start_period, end_period, list_active.title, list_active.sched, list_active.sched_type);
        });
    }
}

function crossDetails(id) {
    if (confirm("Are you sure you want to set the record back to draft?") == true) {
        $.post('/payroll/payrun/cross-details', { _token: _token, id: id }, (response) => {
            $('#payrun_table').DataTable().draw();
            selectedList(list_active.id, start_period, end_period, list_active.title, list_active.sched, list_active.sched_type);
        });
    }
}

function submitForApproval() {
    if (!list_active.id) return;
    if (confirm("Submit this payroll for approval?") !== true) return;

    $.post('/payroll/payrun/submit-for-approval', { _token: _token, id: list_active.id }, function() {
        $('#payrun_table').DataTable().draw();
        selectedList(list_active.id, start_period, end_period, list_active.title, list_active.sched, list_active.sched_type);
    }).fail(function(response) {
        toastr.error(response.responseJSON && response.responseJSON.message ? response.responseJSON.message : 'Failed to submit for approval.');
    });
}

function approveSummary() {
    if (!list_active.id) return;
    if (confirm("Approve this payroll?") !== true) return;

    $.post('/payroll/payrun/approve-summary', { _token: _token, id: list_active.id }, function() {
        $('#payrun_table').DataTable().draw();
        selectedList(list_active.id, start_period, end_period, list_active.title, list_active.sched, list_active.sched_type);
    }).fail(function(response) {
        toastr.error(response.responseJSON && response.responseJSON.message ? response.responseJSON.message : 'Failed to approve payroll.');
    });
}

function revertSummary() {
    if (!list_active.id) return;
    if (confirm("Revert this payroll back to draft?") !== true) return;

    $.post('/payroll/payrun/revert-summary', { _token: _token, id: list_active.id }, function() {
        $('#payrun_table').DataTable().draw();
        selectedList(list_active.id, start_period, end_period, list_active.title, list_active.sched, list_active.sched_type);
    }).fail(function(response) {
        toastr.error(response.responseJSON && response.responseJSON.message ? response.responseJSON.message : 'Failed to revert payroll.');
    });
}

function submitForPayment() {
    if (!list_active.id) return;
    if (confirm("Submit this payroll for payment?") !== true) return;

    $.post('/payroll/payrun/submit-for-payment', { _token: _token, id: list_active.id }, function() {
        $('#payrun_table').DataTable().draw();
        selectedList(list_active.id, start_period, end_period, list_active.title, list_active.sched, list_active.sched_type);
    }).fail(function(response) {
        toastr.error(response.responseJSON && response.responseJSON.message ? response.responseJSON.message : 'Failed to submit for payment.');
    });
}

function editCell(id, cell, value) {
    editable_cell.id = id;
    editable_cell.cell = cell;
    modal_content = 'payrun_amount';
    module_url = '/payroll/payrun_amount';
    actions = 'update';
    record_id = id;

    scion.create.sc_modal("editableCell", "Update " + cell).show();
    $('#editable_amount').val(value);
}

function closeEditable() {
    modal_content = 'payrun';
    module_url = '/payroll/payrun';
    actions = 'save';
    record_id = null;
}

function selectedCashAdvance(id, emp_id) {
    summary_id = id;
    employee_id = emp_id;
    
    modal_content = 'cash_advance';
    module_url = '/payroll/cash_advance';

    if ($.fn.DataTable.isDataTable('#cash-advance-table')) {
        $('#cash-advance-table').DataTable().destroy();
    }

    scion.create.table(
        'cash-advance-table',  
        module_url + '/get-ca/' + summary_id + '/' + emp_id, 
        [
            { data: null, title: "", render: function(data, type, row, meta) {
                return `<a href="#" onclick="editCA(${row.id})"><i class="fas fa-edit"></i></a> <a href="#" onclick="deleteCA(${row.id})"><i class="fas fa-trash"></i></a>`;
            }},
            { data: 'date', title: "Date", render: function(data, type, row, meta) {
                return moment(row.date).format('MMM, DD YYYY');
            }},
            { data: 'amount', title: "Amount", render: function(data, type, row, meta) {
                return scion.currency(data);
            }},
        ], '', []
    );
    
    scion.create.sc_modal("ca_list", "Cash Advance").show();
}

function addCA() {
    actions = 'save';
    record_id = null;

    $('#ca_date').val('');
    $('#ca_amount').val('');
    $('#ca_purpose').val('');

    scion.centralized_button(true, false, true, true);
    scion.create.sc_modal("ca_form", "Cash Advance").show();
}

function editCA(id) {
    actions = 'update';
    record_id = id;

    $.get(`${module_url}/edit/${id}`, function(response) {
        console.log(response);
        $('#ca_date').val(response.cash_advance.date);
        $('#ca_amount').val(response.cash_advance.amount);
        $('#ca_purpose').val(response.cash_advance.purpose);


        scion.centralized_button(true, false, true, true);
        scion.create.sc_modal("ca_form", "Cash Advance").show();
    });

}

function deleteCA(id) {
    event.stopPropagation();

    if (confirm("Are you sure you want to delete this record?") == true) {
        $.post(`${module_url}/destroy`, { _token: _token, id: id }, (response) => {
            $('#cash-advance-table').DataTable().draw();
            selectedList(list_active.id, start_period, end_period, list_active.title, list_active.sched, list_active.sched_type);
        });
    }
}

function printPayslip(id, employee_id, sched) {
    $.post('/payroll/payrun/get-details-info', {
        _token: _token,
        id: id,
        start: start_period,
        end: end_period,
        employee_id: employee_id
    }).done((response) => {
        var item = response.details[0];
        
        var worked_days = (item.for_fixed !== null?item.for_fixed:parseInt(item.timelogs.length))
        var reg_hours = worked_days * 8;
        var holiday = item.holiday;
        var late = parseFloat(item.late_hours).toFixed(2);
        var undertime = parseFloat(item.undertime).toFixed(2);
        var tardiness_mins = parseFloat(item.late_hours + item.undertime).toFixed(2);
        var actual_hours = (reg_hours - late).toFixed(2);
        var daily_rate = item.daily !== "0"? parseFloat(item.daily):(item.employee.compensations!==null?item.employee.compensations.daily_salary:0);
        var hourly_rate = item.hourly !== "0"? parseFloat(item.hourly):(item.employee.compensations!==null?item.employee.compensations.hourly_salary:0);
        var monthly_rate = item.monthly !== "0"? parseFloat(item.monthly):(item.employee.compensations!==null?item.employee.compensations.monthly_salary:0);
        var late_rate = (late/60) * hourly_rate;
        var ut_rate = (undertime/60) * hourly_rate;
        var basic_pay = (list_active.sched_type === 2? (monthly_rate / 2) : (worked_days * daily_rate));
        
        var holiday_rate = 0;

        if(item.holiday_data !== null) {
            item.holiday_data.forEach(h_data => {
                holiday_rate += daily_rate * parseFloat(h_data.holiday_type.multiplier)
            });
        }

        var ot_hours = parseFloat(item.ot_hours).toFixed(2);
        var ot_amount = parseFloat(item.ot_amount);

        var sss = parseFloat(item.sss);
        var phic = parseFloat(item.philhealth);
        var pagibig = parseFloat(item.pagibig);

        var allowance_amount = parseFloat(item.allowance_amount);
        var allowance_daily = allowance_amount !== 0?(allowance_amount / (worked_days !== 0?worked_days:1)):0;

        var absent = item.absent_count;
        var absent_rate = absent * daily_rate;

        var tardiness_deduct = absent_rate === 0?(late_rate + ut_rate):(absent_rate - (late_rate + ut_rate));
        
        var leave_amount = parseFloat(item.leave_count * daily_rate);
        
        var gross_salary = (basic_pay + ot_amount + allowance_amount + leave_amount + holiday_rate) - tardiness_deduct;
        
        var tax = item.tax !== 0?item.tax:item.tax_final;
        var ca = parseFloat(item.ca);

        var gross_deduction = (parseFloat(sss) + parseFloat(phic) + parseFloat(pagibig) + ca + tax);
        var net_pay = (gross_salary - gross_deduction);

        $('.ps-name').text(`${item.employee.firstname} ${item.employee.lastname}`);
        $('.ps-id').text(`${item.employee.employee_no}`);
        $('.ps-department').text(`${item.employee.employments_tab.departments.description}`);
        $('.ps-position').text(`${item.employee.employments_tab.positions.description}`);
        $('.ps-status').text(`${item.status === 0?"DRAFT":item.status === 1?"APPROVED":"DECLINED"}`);

        $('.ps-pay-date').text(`${moment(item.header.pay_date).format('MMM DD,YYYY')}`);
        $('.ps-pay-type').text(`${sched}`);
        $('.ps-pay-wd').text(`${worked_days}`);
        $('.ps-pay-period').text(`${moment(item.header.payroll_period).format('MMM DD,YYYY')}`);

        // Regular
        if(list_active.sched_type === 2) {
            $('.ps-r-mr').text(`${scion.currency(monthly_rate)}`);
            $('.ps-r-dr').text(`${scion.currency(daily_rate)}`);
            $('.ps-r-hr').text(`${scion.currency(hourly_rate)}`);
            $('.ps-r-dys').text(`-`);
            $('.ps-r-ttl').text(`${scion.currency((basic_pay))}`);
        }
        else {
            $('.ps-r-mr').text(`-`);
            $('.ps-r-dr').text(`${scion.currency(daily_rate)}`);
            $('.ps-r-dys').text(`${worked_days}`);
            $('.ps-r-hr').text(`-`);
            $('.ps-r-ttl').text(`${scion.currency((basic_pay))}`);
        }

        // Leaves
        $('.ps-l-dr').text(`${item.leave_count !== 0?scion.currency(daily_rate):'-'}`);
        $('.ps-l-dys').text(`${item.leave_count !== 0?item.leave_count:'-'}`);
        $('.ps-l-ttl').text(`${item.leave_count !== 0?scion.currency(leave_amount):'-'}`);

        // Overtime
        $('.ps-o-hr').text(`${item.ot_hours !== 0?scion.currency(hourly_rate*item.ot_multiplier):'-'}`);
        $('.ps-o-hrs').text(`${item.ot_hours !== 0?(item.ot_hours).toFixed(2):'-'}`);
        $('.ps-o-ttl').text(`${item.ot_hours !== 0?scion.currency(ot_amount):'-'}`);
        
        // Holiday
        $('.ps-h-dr').text(`${holiday !== 0?scion.currency(daily_rate):'-'}`);
        $('.ps-h-dys').text(`${holiday !== 0?holiday:'-'}`);
        $('.ps-h-ttl').text(`${holiday !== 0?scion.currency(holiday_rate):'-'}`);

        // Allowance
        $('.ps-a-dr').text(`${worked_days !== 0?scion.currency(allowance_daily):'-'}`);
        $('.ps-a-dys').text(`${worked_days !== 0?worked_days:'-'}`);
        $('.ps-a-ttl').text(`${worked_days !== 0?scion.currency(allowance_amount):'-'}`);
        
        // Tardiness
        $('.ps-t-dr').text(`${absent !== 0 || late !== 0?scion.currency(daily_rate):'-'}`);
        $('.ps-t-hr').text(`${absent !== 0 || late !== 0?scion.currency(hourly_rate):'-'}`);
        $('.ps-t-dys').text(`${absent !== 0 || late !== 0?absent:'-'}`);
        $('.ps-t-hrs').text(`${absent !== 0 || late !== 0?(tardiness_mins/60).toFixed(2):'-'}`);
        $('.ps-t-ttl').text(`- ${absent !== 0 || late !== 0?scion.currency(tardiness_deduct):'-'}`);
            
        $('.ps-earning').text(`${scion.currency(gross_salary)}`);

        // Deduction
        $('.ps-d-sss').text(`${scion.currency(sss)}`);
        $('.ps-d-ph').text(`${scion.currency(phic)}`);
        $('.ps-d-pg').text(`${scion.currency(pagibig)}`);
        $('.ps-d-wt').text(`${scion.currency(tax)}`);
        $('.ps-d-ca').text(`${scion.currency(ca)}`);
        $('.ps-ttl-d').text(`${scion.currency(gross_deduction)}`);
        $('.ps-netpay').text(`${scion.currency(net_pay)}`);

        selected_print = 'parslipForm';
        
        scion.centralized_button(true, true, true, false);
        scion.create.sc_modal("payslip_form", "Payslip").show();
    });
}
