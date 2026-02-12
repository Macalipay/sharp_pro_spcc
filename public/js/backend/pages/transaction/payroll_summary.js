var emp_id = null;
var date_selected = null;
var copied_schedule = [];
var date = new Date();
var period_id = null;

var sequence_no = null;
var schedule_type = null;
var selected_module = null;

var selected_details = '';

var timelog_edit = 0;

$(function() {
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

    scion.create.table(
        'payroll_summary_table',  
        module_url + '/get', 
        [
            { data: "sequence_no", title:"SEQUENCE NO"},
            { data: "schedule_type", title:"SCHEDULE TYPE", render: function(data, type, row, meta) {
                switch(row.schedule_type) {
                    case 0:
                        return "13th MONTH PAY";
                        break;
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
            { data: "payroll_period", title:"PAYROLL PERIOD", render: function(data, type, row, meta) {
                return moment(row.payroll_period).format('MMM DD YYYY')
            } },
            { data: "no_of_employee", title:"NO OF EMPLOYEE"},
            { data: "amount", title:"PAYROLL AMOUNT", render: function(data, type, row, meta) {
                return scion.currency(row.amount);
            }},
            { data: "status", title:"STATUS", render: function(data, type, row, meta) {
                switch(row.status) {
                    case 0:
                        return "<span class='text-primary' style='font-weight:bold;'>DRAFT</span>";
                        break;

                    case 2:
                        return "<span class='text-warning' style='font-weight:bold;'>PAYSLIP SENT</span>";
                        break;
                }
                return "DRAFT";
            }},
            { title:"ACTION", render: function(data, type, row, meta) {
                return "<button class='btn btn-sm btn-primary view-details' data-id='"+row.id+"' data-sequence='"+row.sequence_no+"' data-sched-type='"+row.schedule_type+"' data-date='"+row.payroll_period+"'>VIEW DETAILS</button>" + "<button class='btn btn-sm btn-success set-completed' data-id='"+row.id+"' data-sequence='"+row.sequence_no+"'>COMPLETE</button>";
            }}
        ], 'Bfrtip', []
    );
    
    scion.create.table(
        'payroll_history_table',  
        module_url + '/get_history', 
        [
            { data: "sequence_no", title:"SEQUENCE NO"},
            { data: "schedule_type", title:"SCHEDULE_TYPE", render: function(data, type, row, meta) {
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
            { data: "payroll_period", title:"PAYROLL PERIOD", render: function(data, type, row, meta) {
                return moment(row.payroll_period).format('MMM DD YYYY')
            } },
            { data: "no_of_employee", title:"NO OF EMPLOYEE"},
            { data: "amount", title:"PAYROLL AMOUNT", render: function(data, type, row, meta) {
                return scion.currency(row.amount);
            }},
            { data: "status", title:"STATUS", render: function(data, type, row, meta) {
                switch(row.status) {
                    case 1:
                        return "<span class='text-success' style='font-weight:bold;'>COMPLETED</span>";
                        break;
                }
                return "DRAFT";
            }},
            { title:"ACTION", render: function(data, type, row, meta) {
                return "<button class='btn btn-sm btn-primary view-details' data-id='"+row.id+"' data-sequence='"+row.sequence_no+"' data-sched-type='"+row.schedule_type+"'>VIEW DETAILS</button>";
            }}
        ], 'ftip', []
    );
    // });

    $('#payroll_summary_table').on('click', '.view-details', function() {
        period_id = $(this).attr('data-id');
        sequence_no = $(this).attr('data-sequence');
        schedule_type = $(this).attr('data-sched-type');
        selected_details = 'summary';
        date_selected = $(this).attr('data-date');

        $('.sent-email').css('display', 'block');
        $('#print_payslip button').css('display', 'block');

        scion.record.new();
        
        if ($.fn.DataTable.isDataTable('#payroll_details_table')) {
            $('#payroll_details_table').DataTable().clear().destroy();
            $('#payroll_details_table thead').empty();
            $('#payroll_details_table tbody').empty();
        }

        if(schedule_type !== "0") {
            scion.create.table(
                'payroll_details_table',  
                module_url + '/get_details/' + sequence_no, 
                [
                    { data: "status", title: "", render: function(data, type, row, meta) {
                        var html = "";
    
                        html = "<input type='checkbox' class='check-status status-"+row.id+"' data-id='"+row.id+"'/>"
                        $('.status-' + row.id).prop('checked', row.status === 1?true:false);
    
                        return html;
                    }},
                    { data: "employee.firstname", title:"NAME", width: "200px", render: function(data, type, row, meta) {
                        return row.employee.firstname + (row.employee.middlename === ""?" "+row.employee.middlename:" ") + row.employee.lastname + (row.employee.suffix === ""?" "+row.employee.suffix:"");
                    }},
                    { data: "sequence_no", title:"SEQUENCE"},
                    { data: "gross_earnings", title:"GROSS EARNING", render: function(data, type, row, meta) {
                        return scion.currency(row.gross_earnings);
                    }},
                    { data: "sss", title:"SSS", render: function(data, type, row, meta) {
                        return scion.currency(row.sss);
                    }},
                    { data: "pagibig", title:"PAG IBIG", render: function(data, type, row, meta) {
                        return scion.currency(row.pagibig);
                    }},
                    { data: "philhealth", title:"PHILHEALTH", render: function(data, type, row, meta) {
                        return scion.currency(row.philhealth);
                    }},
                    { data: "tax", title:"TAX", render: function(data, type, row, meta) {
                        return scion.currency(row.tax);
                    }},
                    { data: "net_pay", title:"NET PAY", render: function(data, type, row, meta) {
                        return scion.currency(row.net_pay);
                    }},
                    { title:"", render: function(data, type, row, meta) {
                        return "<button class='view-payslip' data-sequence='"+row.sequence_no+"' data-emp-id='"+row.employee_id+"'><i class='fas fa-receipt'></i></button>";
                    }}
                ], 'ftip', [], true, false
            );
        }
        else {
            scion.create.table(
                'payroll_details_table',  
                module_url + '/get_details/' + sequence_no, 
                [
                    { data: "status", title: "", render: function(data, type, row, meta) {
                        var html = "";
    
                        html = "<input type='checkbox' class='check-status status-"+row.id+"' data-id='"+row.id+"'/>"
                        $('.status-' + row.id).prop('checked', row.status === 1?true:false);
    
                        return html;
                    }},
                    { data: "employee.firstname", title:"NAME", width: "200px", render: function(data, type, row, meta) {
                        return row.employee.firstname + (row.employee.middlename === ""?" "+row.employee.middlename:" ") + row.employee.lastname + (row.employee.suffix === ""?" "+row.employee.suffix:"");
                    }},
                    { data: "sequence_no", title:"SEQUENCE"},
                    { data: "total_pay", title:"TOTAL PAY", render: function(data, type, row, meta) {
                        return scion.currency(row.net_pay);
                    }},
                    { title:"", className: "text-right", render: function(data, type, row, meta) {
                        return "<button class='payslip-form' onclick='viewPayslip("+row.employee_id+", \""+row.sequence_no+"\")'><i class='fas fa-receipt'></i></button>";
                    }}
                ], 'ftip', [], true, false
            );
        }

        get_overall(sequence_no, schedule_type);

        scion.centralized_button(true, true, true, true);

    }).on('click', '.set-completed', function() {
        period_id = $(this).attr('data-id');
        sequence_no = $(this).attr('data-sequence');
        $('.sequence_no_disp').text(sequence_no);

        scion.sc_modal_show('approval_confirmation');
        
    });

    $('#payroll_history_table').on('click', '.view-details', function() {
        period_id = $(this).attr('data-id');
        sequence_no = $(this).attr('data-sequence');
        schedule_type = $(this).attr('data-sched-type');
        selected_details = 'history';
        
        $('.sent-email').css('display', 'none');
        $('#print_payslip button').css('display', 'none');

        scion.record.new();
        
        if ($.fn.DataTable.isDataTable('#payroll_details_table')) {
            $('#payroll_details_table').DataTable().clear().destroy();
        }

        scion.create.table(
            'payroll_details_table',  
            module_url + '/get_details/' + sequence_no, 
            [
                { data: "status", title: "", render: function(data, type, row, meta) {
                    var html = "";

                    html = "<input type='checkbox' class='check-status status-"+row.id+"' data-id='"+row.id+"' disabled/>"
                    $('.status-' + row.id).prop('checked', row.status === 1?true:false);

                    return html;
                }},
                { data: "employee.firstname", title:"NAME", width: "200px", render: function(data, type, row, meta) {
                    return row.employee.firstname + (row.employee.middlename === ""?" "+row.employee.middlename:" ") + row.employee.lastname + (row.employee.suffix === ""?" "+row.employee.suffix:"");
                }},
                { data: "sequence_no", title:"SEQUENCE"},
                { data: "gross_earnings", title:"GROSS EARNING", render: function(data, type, row, meta) {
                    return scion.currency(row.gross_earnings);
                }},
                { data: "sss", title:"SSS", render: function(data, type, row, meta) {
                    return scion.currency(row.sss);
                }},
                { data: "pagibig", title:"PAG IBIG", render: function(data, type, row, meta) {
                    return scion.currency(row.pagibig);
                }},
                { data: "philhealth", title:"PHILHEALTH", render: function(data, type, row, meta) {
                    return scion.currency(row.philhealth);
                }},
                { data: "tax", title:"TAX", render: function(data, type, row, meta) {
                    return scion.currency(row.tax);
                }},
                { data: "net_pay", title:"NET PAY", render: function(data, type, row, meta) {
                    return scion.currency(row.net_pay);
                }},
                { title:"", render: function(data, type, row, meta) {
                    return "<button class='view-payslip' data-sequence='"+row.sequence_no+"' data-emp-id='"+row.employee_id+"'><i class='fas fa-receipt'></i></button>";
                }}
            ], 'ftip', [], true, false
        );

        get_overall(sequence_no, schedule_type);

        scion.centralized_button(true, true, true, true);
    });

    $('#payroll_summary_form').on('click', '.view-payslip', function() {
        var sequence_no = $(this).attr('data-sequence');
        emp_id = $(this).attr('data-emp-id');

        selected_print = 'print_payslip';

        $('#payslip_form').css('display', 'block');
        $('#payslip_form').css('position', 'fixed');

        $('#additional_buttons button').prop('disabled', false);
        scion.centralized_button(true, true, true, false);

        // updatePayslip();
        showDetails(emp_id);
    }).on('click', '.sent-email', function() {
        $.post(module_url + '/update_status', { _token: _token, id: period_id, status: 2 }).done(function(response) {
            $('#payroll_summary_table').DataTable().draw();
            $('#payroll_history_table').DataTable().draw();

            scion.create.sc_modal('payroll_summary_form').hide('all', modalHideFunction);
        });
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
                showDetails(emp_id);
            }
            else {
                if(response.previous !== null) {
                    emp_id = response.previous.employee_id;
                    showDetails(emp_id);
                }
            }
        });
    }).on('click', '#prev', function() {
        $.post(module_url + '/show', { _token:_token,  employee_id: emp_id, sequence_no: sequence_no }).done(function(response) {
            if(response.previous !== null) {
                emp_id = response.previous.employee_id;
                showDetails(emp_id);
            }
            else {
                if(response.next !== null) {
                    emp_id = response.next.employee_id;
                    showDetails(emp_id);
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
            $('#total_gross').text(scion.currency(response.total.gross));
            $('#total_sss').text(scion.currency(response.total.sss));
            $('#total_philhealth').text(scion.currency(response.total.philhealth));
            $('#total_pagibig').text(scion.currency(response.total.pagibig));
            $('#total_tax').text(scion.currency(response.total.tax));
            $('#total_netpay').text(scion.currency(response.total.net_pay));
        }
    );
}

function custom_modalHide() {
    selected_print = null;
    $('#additional_buttons button').prop('disabled', true);
    modalHideFunction();
}

function showDetails(id) {
    var regular_hours = 0;
    var regular_days = 0;
    var overtime = 0;
    var working_hours = 0;

    record_id = id;

    $.post('/payroll/time_logs/get_record/' + id, {_token: _token, date: date_selected}, function(response) {
        var timesheet = '';

        var drate = response.record.compensations !== null?response.record.compensations.daily_salary:0;
        var hrate = response.record.compensations !== null?response.record.compensations.hourly_salary:0;
        var total_earnings = 0;
        var other_deduction = 0;
        var total_deduction = 0;
        var total_leaves = 0;
        var total_gross = 0;
        var sss = 0;
        var philhealth = 0;
        var pagibig = 200/2;
        var wtax = 0;
        var government_deduction = 200/2;
        var net_pay = 0;

        
        $('#payslip_form #tbl_emp_name').text(response.record.firstname + (response.record.middlename !== "" && response.record.middlename !== null?" " + response.record.middlename:"") + " " + response.record.lastname);
        $('#payslip_form #tbl_emp_number').text(response.record.employee_no);
        $('#payslip_form #tbl_emp_address').text('-');
        $('#payslip_form #tbl_emp_info').text(response.record.phone1);
        $('#payslip_form #tbl_emp_department').text(response.record.employments_tab.departments.description);
        $('#payslip_form #tbl_emp_position').text(response.record.employments_tab.positions.description);
        
        if (response.record.status == "1") {
            $('#tbl_emp_status').text("ACTIVE");
        }
        else if (response.record.status == "6") {
            $('#tbl_emp_status').text("PROBATION");
        }
        else if (response.record.status == "7") {
            $('#tbl_emp_status').text("ON-CALL");
        }
        else if (response.record.status == "8") {
            $('#tbl_emp_status').text("INTERNSHIP/OJT");
        }
        else {
            $('#tbl_emp_status').text("INACTIVE");
        }

        $('#h_rate').text(scion.currency(hrate));

        if(response.semi_monthly !== null){
            var earning = "";
            var leave = "";
            var holiday = "";
            var allowance = "";
            var deduction = "";
            var tax_amount = 0;

            $('#payslip_form #pay_period').text(response.other.pay_period);
            $('#payslip_form #pay_date').text(response.other.pay_date);
            $('#payslip_form #pay_type').text(response.record.employments_tab.calendar.title);

            response.other.deductions.forEach((item) => {
                deduction += "<tr>";
                deduction += `<td style="width:90%" colspan="3"><i class="fas fa-trash text-danger"></i> ${item.deduction}</td>`;
                deduction += `<td style="width:30%" class="text-center">${scion.currency(item.total)}</td>`;
                deduction += "</tr>";
                other_deduction += item.total;
            });
            
            response.semi_monthly.forEach((semiMonth) => {
                timesheet += `<tr id="record_${semiMonth.date}" class="cell-${semiMonth.status.replace(' ', '-')}">`;
                    timesheet += `<td style="width: 90px;" class="tm-date">${semiMonth.date}</td>`;
                    timesheet += `<td>${semiMonth.day}</td>`;
                    timesheet += `<td><span class="status ${semiMonth.status.replace(' ', '-')}">${semiMonth.status}</span></td>`;
                    timesheet += `<td class="set-time-${timelog_edit}">${timelog_edit === 0?semiMonth.time_in!==null?moment(semiMonth.time_in).format('hh:mm A'):'-':'<input type="time" class="editable-time time-in" value="'+moment(semiMonth.time_in).format('HH:mm')+'"/>'}</td>`;
                    timesheet += `<td class="set-time-${timelog_edit}">${timelog_edit === 0?semiMonth.break_in!==null?moment(semiMonth.break_in).format('hh:mm A'):'-':'<input type="time" class="editable-time break-in" value="'+moment(semiMonth.break_in).format('HH:mm')+'"/>'}</td>`;
                    timesheet += `<td class="set-time-${timelog_edit}">${timelog_edit === 0?semiMonth.break_out!==null?moment(semiMonth.break_out).format('hh:mm A'):'-':'<input type="time" class="editable-time break-out" value="'+moment(semiMonth.break_out).format('HH:mm')+'"/>'}</td>`;
                    timesheet += `<td class="set-time-${timelog_edit}">${timelog_edit === 0?semiMonth.time_out!==null?moment(semiMonth.time_out).format('hh:mm A'):'-':'<input type="time" class="editable-time time-out" value="'+moment(semiMonth.time_out).format('HH:mm')+'"/>'}</td>`;
                    timesheet += `<td class="set-time-${timelog_edit}">${timelog_edit === 0?semiMonth.ot_in!==null?moment(semiMonth.ot_in).format('hh:mm A'):'-':'<input type="time" class="editable-time ot-in" value="'+moment(semiMonth.ot_in).format('HH:mm')+'"/>'}</td>`;
                    timesheet += `<td class="set-time-${timelog_edit}">${timelog_edit === 0?semiMonth.ot_out!==null?moment(semiMonth.ot_out).format('hh:mm A'):'-':'<input type="time" class="editable-time ot-out" value="'+moment(semiMonth.ot_out).format('HH:mm')+'"/>'}</td>`;
                    timesheet += `<td>${semiMonth.office_hours}</td>`;
                    timesheet += `<td>${semiMonth.break_time}</td>`;
                    timesheet += `<td>${parseFloat(semiMonth.office_hours-semiMonth.break_time)}</td>`;
                    timesheet += `<td>${semiMonth.overtime}</td>`;
                    timesheet += `<td>${((parseFloat(semiMonth.office_hours-semiMonth.break_time)) + parseFloat(semiMonth.overtime)).toFixed(2)}</td>`;
                timesheet += `</tr>`;

                regular_hours += parseFloat(semiMonth.office_hours-semiMonth.break_time);
                regular_days += semiMonth.status !== "HOLIDAY"?parseFloat(semiMonth.office_hours) === 0?0:1:0;
                // count_days_selected += parseFloat(semiMonth.office_hours) === 0?0:1;
                overtime += parseFloat(semiMonth.overtime);
                working_hours += (parseFloat(semiMonth.office_hours-semiMonth.break_time)) + parseFloat(semiMonth.overtime);
            });

            response.other.earnings.forEach(data => {
                // let rate = parseFloat(hrate*data.earning.multiplier);
                let rate_2 = parseFloat(drate*(data.earning !== null ? data.earning.multiplier:1));
                let hours = data.earning !== null ? (data.earning.code === "RE"?regular_hours:data.earning.code === "OT"?overtime:0):0;
                let days = data.earning !== null ? (data.earning.code === "RE"?regular_days:0):0;
                
                // if(parseFloat(rate*hours) !== 0) {
                if(parseFloat(rate_2*days) !== 0) {
                    earning += "<tr>";
                    earning += `<td style="width:30%">${data.earning.name}</td>`;
                    earning += `<td style="width:30%" class="text-center">${scion.currency(rate_2)}</td>`;
                    earning += `<td style="width:30%" class="text-center">${days}</td>`;
                    earning += `<td style="width:30%" class="text-center">${scion.currency(parseFloat(rate_2*days))}</td>`;
                    earning += "</tr>";
                }

                total_earnings += parseFloat(rate_2*days);
            });
            
            response.other.allowances.forEach((item) => {
                allowance += "<tr>";
                allowance += `<td style="width:30%">${item.allowance}</td>`;
                allowance += `<td style="width:30%;text-align:center;">${scion.currency(item.total)}</td>`;
                allowance += `<td style="width:30%;text-align:center;">${item.days}</td>`;
                allowance += `<td style="width:30%;text-align:center;">${scion.currency(item.grand_total)}</td>`;
                allowance += "</tr>";

                total_earnings += parseFloat(item.grand_total);
            });
            
            response.other.holiday.forEach((data, i) => {
                let rate = parseFloat(drate*data.holiday_type.multiplier);
                let hours = parseFloat((i+1));

                holiday += "<tr>";
                holiday += `<td style="width:30%">${data.name}</td>`;
                holiday += `<td style="width:30%" class="text-center">${scion.currency(rate)}</td>`;
                holiday += `<td style="width:30%" class="text-center">${hours}</td>`;
                holiday += `<td style="width:30%" class="text-center">${scion.currency(parseFloat(rate*hours))}</td>`;
                holiday += "</tr>";

                total_earnings += parseFloat(rate*hours);
            });
            
            if(response.other.overtime !== 0) {
                earning += "<tr>";
                earning += `<td style="width:30%">OVERTIME</td>`;
                earning += `<td style="width:30%" class="text-center"></td>`;
                earning += `<td style="width:30%" class="text-center"></td>`;
                earning += `<td style="width:30%" class="text-center">${scion.currency(parseFloat((hrate*response.ot_earning.multiplier)*response.other.overtime))}</td>`;
                earning += "</tr>";
            }

            response.other.leave.forEach(data => {
                let rate = parseFloat(drate*1);
                let hours = data.total_leave_hours;

                leave += "<tr>";
                leave += `<td style="width:30%">${data.leave_type.leave_name}</td>`;
                leave += `<td style="width:30%" class="text-center">${scion.currency(rate)}</td>`;
                leave += `<td style="width:30%" class="text-center">${hours}</td>`;
                leave += `<td style="width:30%" class="text-center">${scion.currency(parseFloat(rate*hours))}</td>`;
                leave += "</tr>";

                total_leaves += parseFloat(rate*hours);

            });

            total_gross = parseFloat(total_earnings + total_leaves);

            philhealth = (total_gross*0.05)/2;

            $.post(`/payroll/sss/get-val`, { _token:_token, gross: total_gross, type: response.record.employments_tab.calendar.type}, function(response) {
                sss = response.sss;
                government_deduction = sss + philhealth + pagibig;

                total_deduction = government_deduction + other_deduction;

                tax_amount = total_gross - total_deduction;

                net_pay = (tax_amount - response.w_tax);
                wtax = response.w_tax;

                $('#payslip_form #total_earnings').text(scion.currency(total_earnings));
                $('#payslip_form #other_deduction').text(scion.currency(other_deduction));
                $('#payslip_form #total_leaves').text(scion.currency(total_leaves));
                $('#payslip_form #total_gross').text(scion.currency(total_gross));

                $('#payslip_form #total_deduction').text(scion.currency(total_deduction));

                $('#payslip_form #total_government_deduction').text(scion.currency(government_deduction));
                
                $('#payslip_form #total_sss').text(scion.currency(sss));
                $('#payslip_form #total_philhealth').text(scion.currency(philhealth));
                $('#payslip_form #total_pagibig').text(scion.currency(pagibig));

                $('#payslip_form #tax_amount').text(scion.currency(tax_amount));
                $('#payslip_form #net_pay').text(scion.currency(net_pay));
                $('#payslip_form #total_net_pay').text(scion.currency(net_pay));
                $('#payslip_form #withholding_tax').text(scion.currency(response.w_tax));

                hold_approval = {
                    gross_earnings: total_gross,
                    sss: sss,
                    philhealth: philhealth,
                    pagibig: pagibig,
                    tax: wtax,
                    net_pay: net_pay
                };
            });


            $('#payslip_form .holiday-container').html(holiday);
            $('#payslip_form #payroll_rate_details .custom').html(earning);
            $('#payslip_form #payroll_leaves tbody').html(leave);
            $('#payslip_form .allowance-container').html(allowance);
            $('#payslip_form #payroll_other_deductions tbody').html(deduction);

            $('#payslip_form #sequence_no').text("M-" + moment(response.other.pay_period).format('MMDDY'));

            $('#payslip_form #total_regular_hours').text(regular_hours);
            $('#payslip_form #total_overtime').text(overtime);
            $('#payslip_form #total_working_hours').text(working_hours);
    
            $('#payslip_form #timesheet tbody').html(timesheet);
    
            scion.create.sc_modal("details_form", "Details").show();
        } 
        else {
            toastr.error(`Please setup a payroll calendar for <b style='font-weight:bold;'>${response.record.firstname + (response.record.middlename !== "" && response.record.middlename !== null?" " + response.record.middlename:"") + " " + response.record.lastname}</b>`, "Generating Timesheet Failed")
        }


    })
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
