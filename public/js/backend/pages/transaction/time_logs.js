var emp_id = null;
var earnings = [];
var schedule_selected = null;
var approval_data = {
    _token: $('meta[name="csrf-token"]').attr('content')
};
var hold_approval = {};
var timelog_edit = 0;
var timesheet_record = [];

var count_days_selected = 0;
var absent_record = [];

const exportButtons = canDownload ? ['csv', 'pdf'] : [];
console.log(canDownload)

$(function() {
    modal_content = 'time_logs';
    module_url = '/payroll/time_logs';
    module_type = 'custom';
    page_title = "Time Plotting";

    const today = new Date();

    $('#date-filter').val(moment(today).format('YYYY-MM-DD'));
    scion.centralized_button(true, true, true, true);
    
    scion.create.table(
        'employee_table',  
        module_url + '/get/all/' + scion.getDateRange($('#date-filter').val(), 1).first + '/' + scion.getDateRange($('#date-filter').val(), 1).last, 
        [
            { data: "firstname", title:"EMPLOYEE", className: "name-list", render: function(data, type, row, meta) {
                return "<div class='employee_info' onclick='showDetails("+row.id+")'>" + row.firstname + " " + (row.middlename !== null && row.middlename !== ""?row.middlename + " ":"") + row.lastname + (row.suffix !== null && row.suffix !== ""?" " + row.suffix:"") + "</div>";
            }},
            { data: null, title: "STATUS", render: function(data, type, row, meta) {
                return row.approval !== null?'APPROVED':'PENDING';
            }},
            { data: "reg", title: "REG", render: function(data) {
                return formatTwoDecimals(data);
            }},
            { data: "ot", title: "OT"},
            { data: "sick", title: "SICK"},
            { data: "vac", title: "VAC"},
            { data: "total", title: "TOTAL"}
        ], 'Brtip', exportButtons, false, false
    );

    scion.record.get('earnings', {}, function(response) {
        earnings = response.earnings;
    });

    $('#employee_table').on('click', '.record-status', function(){
        if($('#list_' + $(this).attr('list')).hasClass('show') !== true) {
            schedule_selected = $(this).attr('list');
            $('.status-list').removeClass('show');
            $('#list_' + $(this).attr('list')).addClass('show');
        }
        else {
            schedule_selected = null;
            $('#list_' + $(this).attr('list')).removeClass('show');
        }
    }).on('click', '.status-list a', function() {
        var status = $(this).attr('status');
        $.post(module_url+'/update-status', { "_token": _token, "id": schedule_selected, "status": status }).done((response)=>{
            $('#employee_table').DataTable().draw();
            toastr.success('Status updated!');
        });
    });

    $('#time_plotting').on('change', '.time-in', function(){
        var day = $($(this).parent()).attr('day');
        var time_in = this.value;
        var time_out = $('#entry_' + day + ' .time-out').val();

        if(time_out !== "") {
            var total_hours = (new Date(time_out) - new Date(time_in) ) / 1000 / 60 / 60;
            $('#entry_' + day + ' .total-hours').val(parseFloat(total_hours).toFixed(2));
        }
        else {
            $('#entry_' + day + ' .total-hours').val(parseFloat("0").toFixed(2));
        }
    }).on('change', '.time-out', function(){
        var day = $($(this).parent()).attr('day');
        var time_in = $('#entry_' + day + ' .time-in').val();
        var time_out = this.value;

        if(time_in !== "") {
            var total_hours = (new Date(time_out) - new Date(time_in) ) / 1000 / 60 / 60;
            $('#entry_' + day + ' .total-hours').val(parseFloat(total_hours).toFixed(2));
        }
        else {
            $('#entry_' + day + ' .total-hours').val(parseFloat("0").toFixed(2));
        }
    });

    $('#time_logs_form').on('click', '.crs', function() {
        $.each($('.time-entry'), (i,v)=>{
            var tag_id = v.id;
            var no_day = parseInt(i + 1);
            
            if(($('#'+tag_id+' .time-in').val() !== "" && $('#'+tag_id+' .time-out').val() !== "" && $('#'+tag_id+' .type').val() !== "") || ($('#'+tag_id+' .type').val() !== "")) {
                var data = {
                    date: scion.getDateRange($('#date-filter').val(), no_day).current,
                    time_in: $('#'+tag_id+' .time-in').val(),
                    time_out: $('#'+tag_id+' .time-out').val(),
                    employee_id: emp_id,
                    type: $('#'+tag_id+' .type').val(),
                    total_hours: $('#'+tag_id+' .total-hours').val(),
                    ot_hours: $('#'+tag_id+' .ot-hours').val(),
                    late_hours: $('#'+tag_id+' .late-hours').val(),
                    undertime: $('#'+tag_id+' .undertime').val()
                };
        
                scion.record.get('cross-matching', data, function(response){
                });

            }
        });
        addRecord(emp_id);
        success();
    });

});


function success() {
    switch(actions) {
        case 'save':
            if(modal_content === "payroll_summary") {
                scion.create.sc_modal("payroll_summary_form").hide('all', modalHideFunction);
                modal_content = 'time_logs';
                module_url = '/payroll/time_logs';
                scion.centralized_button(true, true, true, true);
            }
            else if(modal_content === "allowance_setup") {
                allowanceClose();
                showDetails(record_id);
            }
            else if(modal_content === "deduction_setup") {
                deductionClose();
                showDetails(record_id);
            }
            break;
        case 'update':
            break;
    }
    $('#employee_table').DataTable().draw();
    // scion.create.sc_modal('scheduling_form').hide('all', modalHideFunction);
}

function error() {}

function delete_success() {
    $('#employee_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    if(modal_content === "time_logs") {
        form_data = {
            _token: _token,
            record :[]
        };

        $.each($('.time-entry'), (i,v)=>{
            var tag_id = v.id;

            form_data.record.push({
                employee_id: emp_id,
                date: scion.getDateRange($('#date-filter').val(), parseInt(i+1)).current,
                time_in: $('#'+tag_id+' .time-in').val(),
                time_out: $('#'+tag_id+' .time-out').val(),
                break_in: '',
                break_out: '',
                ot_in: '',
                ot_out: '',
                type: $('#'+tag_id+' .type').val(),
                total_hours: $('#'+tag_id+' .total-hours').val(),
                break_hours: 0,
                ot_hours: $('#'+tag_id+' .ot-hours').val(),
                late_hours: $('#'+tag_id+' .late-hours').val(),
                undertime: $('#'+tag_id+' .undertime').val(),
                status: 0,
                schedule_status: 0
            });
        });
    }
    else if(modal_content === "payroll_summary") {
        form_data = {
            _token: _token,
            schedule_type: $('select#pay_type').val(),
            period_start: $('input#period_start').val(),
            payroll_period: $('input#payroll_period').val(),
            pay_date: $('input#pay_date').val(),
            date: $('#date-filter').val(),
            status: 0
        };
    }
    else if(modal_content === "allowance_setup") {
        form_data = {
            _token: _token,
            allowance_id: $('#allowance_id').val(),
            date: null,
            amount: $('#amount').val(),
            sequence_no: $('#sequence_no').text(),
            employee_id: emp_id,
            days: $('#days').val(),
            total_amount: $('#total_amount').val(),
        };
    }
    else if(modal_content === "deduction_setup") {
        form_data = {
            _token: _token,
            deduction_id: $('#deduction_id').val(),
            date: $('#deduction_date').val(),
            amount: $('#deduction_amount').val(),
            sequence_no: $('#sequence_no').text(),
            employee_id: emp_id,
        };
    }

    return form_data;
}

function generateDeleteItems(){}


function modalShowFunction() {
    scion.centralized_button(true, false, true, true);
}

function modalHideFunction() {
    scion.centralized_button(true, true, true, true);
}

// CUSTOM FUNCTION
function addRecord(id) {
    
    emp_id = id;
    scion.record.new(function() {
        if ($.fn.DataTable.isDataTable('#time_plotting')) {
            $('#time_plotting').DataTable().clear().destroy();
        }
        scion.create.table(
            'time_plotting',  
            module_url + '/plot/' + id + '/' + scion.getDateRange($('#date-filter').val(), 1).first + '/' + scion.getDateRange($('#date-filter').val(), 1).last, 
            [
                { data: "sun", title:"SUNDAY <br> " + scion.getDateRange($('#date-filter').val(), 1).current, render: function(data, type, row, meta) {
                    var tag = "";
                    var sched_status = '-';
                    var date_current = scion.getDateRange($('#date-filter').val(), 1).current;
                    
                    if((row.sun!==null?row.sun.split('|')[7]:'0') === "1") {
                        sched_status = 'WITH SCHEDULE';
                    }
                    else if((row.sun!==null?row.sun.split('|')[7]:'0') === "2"){
                        sched_status = 'NO SCHEDULE';
                    }

                    tag += "<div class='time-entry' id='entry_sun'>";
                        tag += "<div class='form-group' day='sun'>";
                            tag += "<label>TIME IN:</label>";
                            tag += "<input type='datetime-local' class='form-control input-sm time-in'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='sun'>";
                            tag += "<label>TIME OUT:</label>";
                            tag += "<input type='datetime-local' class='form-control input-sm time-out'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='sun'>";
                            tag += "<label>TOTAL HOURS:</label>";
                            tag += "<input type='text' class='form-control input-sm total-hours' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='sun'>";
                            tag += "<label>TYPE:</label>";
                            tag += "<select class='form-control type' name='type'>";
                                tag += "<option value=''></option>";
                                $.each(earnings, (i, val)=>{
                                    tag += "<option value='"+val.id+"'>"+val.name+"</option>";
                                });
                            tag += "</select>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='sun'>";
                            tag += "<label>OT HOURS:</label>";
                            tag += "<input type='text' class='form-control input-sm ot-hours' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='sun'>";
                            tag += "<label>LATE HOURS:</label>";
                            tag += "<input type='text' class='form-control input-sm late-hours' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='sun'>";
                            tag += "<label>UNDERTIME:</label>";
                            tag += "<input type='text' class='form-control input-sm undertime' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group'>";
                            tag += "<label>SCHEDULE STATUS:</label>";
                            tag += "<div class='schedule-status sch-"+(row.sun!==null?row.sun.split('|')[7]:'0')+"'>"+sched_status+"</div>";
                        tag += "</div>";
                    tag += "</div>";
                    
                    $('#entry_sun .time-in').attr('min', date_current + " 00:00");
                    $('#entry_sun .time-in').attr('max', date_current + " 23:59");
                    $('#entry_sun .time-out').attr('min', date_current + " 00:00");

                    $('#entry_sun .time-in').val((row.sun!==null?row.sun.split('|')[0]:date_current + " 00:00"));
                    $('#entry_sun .time-out').val((row.sun!==null?row.sun.split('|')[1]:date_current + " 00:00"));
                    $('#entry_sun .total-hours').val((row.sun!==null?row.sun.split('|')[2]:'0.00'));
                    $('#entry_sun .type').val((row.sun!==null?row.sun.split('|')[3]:''));
                    $('#entry_sun .ot-hours').val((row.sun!==null?row.sun.split('|')[4]:'0.00'));
                    $('#entry_sun .late-hours').val((row.sun!==null?row.sun.split('|')[5]:'0.00'));
                    $('#entry_sun .undertime').val((row.sun!==null?row.sun.split('|')[6]:'0.00'));
                    $('#entry_sun .schedule-status').val((row.sun!==null?row.sun.split('|')[6]:'0.00'));



                    return tag;
                }},
                { data: "mon", title:"MONDAY <br> " + scion.getDateRange($('#date-filter').val(), 2).current, render: function(data, type, row, meta) {
                    var tag = "";
                    var sched_status = '-';
                    var date_current = scion.getDateRange($('#date-filter').val(), 2).current;
                    
                    if((row.mon!==null?row.mon.split('|')[7]:'0') === "1") {
                        sched_status = 'WITH SCHEDULE';
                    }
                    else if((row.mon!==null?row.mon.split('|')[7]:'0') === "2"){
                        sched_status = 'NO SCHEDULE';
                    }

                    tag += "<div class='time-entry' id='entry_mon'>";
                        tag += "<div class='form-group' day='mon'>";
                            tag += "<label>TIME IN:</label>";
                            tag += "<input type='datetime-local' class='form-control input-sm time-in'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='mon'>";
                            tag += "<label>TIME OUT:</label>";
                            tag += "<input type='datetime-local' class='form-control input-sm time-out'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='mon'>";
                            tag += "<label>TOTAL HOURS:</label>";
                            tag += "<input type='text' class='form-control input-sm total-hours' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='mon'>";
                            tag += "<label>TYPE:</label>";
                            tag += "<select class='form-control type' name='type'>";
                                tag += "<option value=''></option>";
                                $.each(earnings, (i, val)=>{
                                    tag += "<option value='"+val.id+"'>"+val.name+"</option>";
                                });
                            tag += "</select>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='mon'>";
                            tag += "<label>OT HOURS:</label>";
                            tag += "<input type='text' class='form-control input-sm ot-hours' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='mon'>";
                            tag += "<label>LATE HOURS:</label>";
                            tag += "<input type='text' class='form-control input-sm late-hours' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='mon'>";
                            tag += "<label>UNDERTIME:</label>";
                            tag += "<input type='text' class='form-control input-sm undertime' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group'>";
                            tag += "<label>SCHEDULE STATUS:</label>";
                            tag += "<div class='schedule-status sch-"+(row.mon!==null?row.mon.split('|')[7]:'0')+"'>"+sched_status+"</div>";
                        tag += "</div>";
                    tag += "</div>";

                    $('#entry_mon .time-in').attr('min', date_current + " 00:00");
                    $('#entry_mon .time-in').attr('max', date_current + " 23:59");
                    $('#entry_mon .time-out').attr('min', date_current + " 00:00");

                    $('#entry_mon .time-in').val((row.mon!==null?row.mon.split('|')[0]:date_current + " 00:00"));
                    $('#entry_mon .time-out').val((row.mon!==null?row.mon.split('|')[1]:date_current + " 00:00"));
                    $('#entry_mon .total-hours').val((row.mon!==null?row.mon.split('|')[2]:'0.00'));
                    $('#entry_mon .type').val((row.mon!==null?row.mon.split('|')[3]:''));
                    $('#entry_mon .ot-hours').val((row.mon!==null?row.mon.split('|')[4]:'0.00'));
                    $('#entry_mon .late-hours').val((row.mon!==null?row.mon.split('|')[5]:'0.00'));
                    $('#entry_mon .undertime').val((row.mon!==null?row.mon.split('|')[6]:'0.00'));



                    return tag;
                }},
                { data: "tue", title:"TUESDAY <br> " + scion.getDateRange($('#date-filter').val(), 3).current, render: function(data, type, row, meta) {
                    var tag = "";
                    var sched_status = '-';
                    var date_current = scion.getDateRange($('#date-filter').val(), 3).current;
                    
                    if((row.tue!==null?row.tue.split('|')[7]:'0') === "1") {
                        sched_status = 'WITH SCHEDULE';
                    }
                    else if((row.tue!==null?row.tue.split('|')[7]:'0') === "2"){
                        sched_status = 'NO SCHEDULE';
                    }

                    tag += "<div class='time-entry' id='entry_tue'>";
                        tag += "<div class='form-group' day='tue'>";
                            tag += "<label>TIME IN:</label>";
                            tag += "<input type='datetime-local' class='form-control input-sm time-in'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='tue'>";
                            tag += "<label>TIME OUT:</label>";
                            tag += "<input type='datetime-local' class='form-control input-sm time-out'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='tue'>";
                            tag += "<label>TOTAL HOURS:</label>";
                            tag += "<input type='text' class='form-control input-sm total-hours' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='tue'>";
                            tag += "<label>TYPE:</label>";
                            tag += "<select class='form-control type' name='type'>";
                                tag += "<option value=''></option>";
                                $.each(earnings, (i, val)=>{
                                    tag += "<option value='"+val.id+"'>"+val.name+"</option>";
                                });
                            tag += "</select>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='tue'>";
                            tag += "<label>OT HOURS:</label>";
                            tag += "<input type='text' class='form-control input-sm ot-hours' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='tue'>";
                            tag += "<label>LATE HOURS:</label>";
                            tag += "<input type='text' class='form-control input-sm late-hours' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='tue'>";
                            tag += "<label>UNDERTIME:</label>";
                            tag += "<input type='text' class='form-control input-sm undertime' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group'>";
                            tag += "<label>SCHEDULE STATUS:</label>";
                            tag += "<div class='schedule-status sch-"+(row.tue!==null?row.tue.split('|')[7]:'0')+"'>"+sched_status+"</div>";
                        tag += "</div>";
                    tag += "</div>";

                    $('#entry_tue .time-in').attr('min', date_current + " 00:00");
                    $('#entry_tue .time-in').attr('max', date_current + " 23:59");
                    $('#entry_tue .time-out').attr('min', date_current + " 00:00");

                    $('#entry_tue .time-in').val((row.tue!==null?row.tue.split('|')[0]:date_current + " 00:00"));
                    $('#entry_tue .time-out').val((row.tue!==null?row.tue.split('|')[1]:date_current + " 00:00"));
                    $('#entry_tue .total-hours').val((row.tue!==null?row.tue.split('|')[2]:'0.00'));
                    $('#entry_tue .type').val((row.tue!==null?row.tue.split('|')[3]:''));
                    $('#entry_tue .ot-hours').val((row.tue!==null?row.tue.split('|')[4]:'0.00'));
                    $('#entry_tue .late-hours').val((row.tue!==null?row.tue.split('|')[5]:'0.00'));
                    $('#entry_tue .undertime').val((row.tue!==null?row.tue.split('|')[6]:'0.00'));

                    return tag;
                }},
                { data: "wed", title:"WEDNESDAY <br> " + scion.getDateRange($('#date-filter').val(), 4).current, render: function(data, type, row, meta) {
                    var tag = "";
                    var sched_status = '-';
                    var date_current = scion.getDateRange($('#date-filter').val(), 4).current;
                    
                    if((row.wed!==null?row.wed.split('|')[7]:'0') === "1") {
                        sched_status = 'WITH SCHEDULE';
                    }
                    else if((row.wed!==null?row.wed.split('|')[7]:'0') === "2"){
                        sched_status = 'NO SCHEDULE';
                    }

                    tag += "<div class='time-entry' id='entry_wed'>";
                        tag += "<div class='form-group' day='wed'>";
                            tag += "<label>TIME IN:</label>";
                            tag += "<input type='datetime-local' class='form-control input-sm time-in'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='wed'>";
                            tag += "<label>TIME OUT:</label>";
                            tag += "<input type='datetime-local' class='form-control input-sm time-out'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='wed'>";
                            tag += "<label>TOTAL HOURS:</label>";
                            tag += "<input type='text' class='form-control input-sm total-hours' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='wed'>";
                            tag += "<label>TYPE:</label>";
                            tag += "<select class='form-control type' name='type'>";
                                tag += "<option value=''></option>";
                                $.each(earnings, (i, val)=>{
                                    tag += "<option value='"+val.id+"'>"+val.name+"</option>";
                                });
                            tag += "</select>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='wed'>";
                            tag += "<label>OT HOURS:</label>";
                            tag += "<input type='text' class='form-control input-sm ot-hours' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='wed'>";
                            tag += "<label>LATE HOURS:</label>";
                            tag += "<input type='text' class='form-control input-sm late-hours' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='wed'>";
                            tag += "<label>UNDERTIME:</label>";
                            tag += "<input type='text' class='form-control input-sm undertime' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group'>";
                            tag += "<label>SCHEDULE STATUS:</label>";
                            tag += "<div class='schedule-status sch-"+(row.wed!==null?row.wed.split('|')[7]:'0')+"'>"+sched_status+"</div>";
                        tag += "</div>";
                    tag += "</div>";

                    $('#entry_wed .time-in').attr('min', date_current + " 00:00");
                    $('#entry_wed .time-in').attr('max', date_current + " 23:59");
                    $('#entry_wed .time-out').attr('min', date_current + " 00:00");

                    $('#entry_wed .time-in').val((row.wed!==null?row.wed.split('|')[0]:date_current + " 00:00"));
                    $('#entry_wed .time-out').val((row.wed!==null?row.wed.split('|')[1]:date_current + " 00:00"));
                    $('#entry_wed .total-hours').val((row.wed!==null?row.wed.split('|')[2]:'0.00'));
                    $('#entry_wed .type').val((row.wed!==null?row.wed.split('|')[3]:''));
                    $('#entry_wed .ot-hours').val((row.wed!==null?row.wed.split('|')[4]:'0.00'));
                    $('#entry_wed .late-hours').val((row.wed!==null?row.wed.split('|')[5]:'0.00'));
                    $('#entry_wed .undertime').val((row.wed!==null?row.wed.split('|')[6]:'0.00'));

                    return tag;
                }},
                { data: "thu", title:"THURSDAY <br> " + scion.getDateRange($('#date-filter').val(), 5).current, render: function(data, type, row, meta) {
                    var tag = "";
                    var sched_status = '-';
                    var date_current = scion.getDateRange($('#date-filter').val(), 5).current;
                    
                    if((row.thu!==null?row.thu.split('|')[7]:'0') === "1") {
                        sched_status = 'WITH SCHEDULE';
                    }
                    else if((row.thu!==null?row.thu.split('|')[7]:'0') === "2"){
                        sched_status = 'NO SCHEDULE';
                    }

                    tag += "<div class='time-entry' id='entry_thu'>";
                        tag += "<div class='form-group' day='thu'>";
                            tag += "<label>TIME IN:</label>";
                            tag += "<input type='datetime-local' class='form-control input-sm time-in'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='thu'>";
                            tag += "<label>TIME OUT:</label>";
                            tag += "<input type='datetime-local' class='form-control input-sm time-out'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='thu'>";
                            tag += "<label>TOTAL HOURS:</label>";
                            tag += "<input type='text' class='form-control input-sm total-hours' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='thu'>";
                            tag += "<label>TYPE:</label>";
                            tag += "<select class='form-control type' name='type'>";
                                tag += "<option value=''></option>";
                                $.each(earnings, (i, val)=>{
                                    tag += "<option value='"+val.id+"'>"+val.name+"</option>";
                                });
                            tag += "</select>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='thu'>";
                            tag += "<label>OT HOURS:</label>";
                            tag += "<input type='text' class='form-control input-sm ot-hours' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='thu'>";
                            tag += "<label>LATE HOURS:</label>";
                            tag += "<input type='text' class='form-control input-sm late-hours' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='thu'>";
                            tag += "<label>UNDERTIME:</label>";
                            tag += "<input type='text' class='form-control input-sm undertime' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group'>";
                            tag += "<label>SCHEDULE STATUS:</label>";
                            tag += "<div class='schedule-status sch-"+(row.thu!==null?row.thu.split('|')[7]:'0')+"'>"+sched_status+"</div>";
                        tag += "</div>";
                    tag += "</div>";

                    $('#entry_thu .time-in').attr('min', date_current + " 00:00");
                    $('#entry_thu .time-in').attr('max', date_current + " 23:59");
                    $('#entry_thu .time-out').attr('min', date_current + " 00:00");

                    $('#entry_thu .time-in').val((row.thu!==null?row.thu.split('|')[0]:date_current + " 00:00"));
                    $('#entry_thu .time-out').val((row.thu!==null?row.thu.split('|')[1]:date_current + " 00:00"));
                    $('#entry_thu .total-hours').val((row.thu!==null?row.thu.split('|')[2]:'0.00'));
                    $('#entry_thu .type').val((row.thu!==null?row.thu.split('|')[3]:''));
                    $('#entry_thu .ot-hours').val((row.thu!==null?row.thu.split('|')[4]:'0.00'));
                    $('#entry_thu .late-hours').val((row.thu!==null?row.thu.split('|')[5]:'0.00'));
                    $('#entry_thu .undertime').val((row.thu!==null?row.thu.split('|')[6]:'0.00'));
                    
                    return tag;
                }},
                { data: "fri", title:"FRIDAY <br> " + scion.getDateRange($('#date-filter').val(), 6).current, render: function(data, type, row, meta) {
                    var tag = "";
                    var sched_status = '-';
                    var date_current = scion.getDateRange($('#date-filter').val(), 6).current;
                    
                    if((row.fri!==null?row.fri.split('|')[7]:'0') === "1") {
                        sched_status = 'WITH SCHEDULE';
                    }
                    else if((row.fri!==null?row.fri.split('|')[7]:'0') === "2"){
                        sched_status = 'NO SCHEDULE';
                    }

                    tag += "<div class='time-entry' id='entry_fri'>";
                        tag += "<div class='form-group' day='fri'>";
                            tag += "<label>TIME IN:</label>";
                            tag += "<input type='datetime-local' class='form-control input-sm time-in'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='fri'>";
                            tag += "<label>TIME OUT:</label>";
                            tag += "<input type='datetime-local' class='form-control input-sm time-out'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='fri'>";
                            tag += "<label>TOTAL HOURS:</label>";
                            tag += "<input type='text' class='form-control input-sm total-hours' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='fri'>";
                            tag += "<label>TYPE:</label>";
                            tag += "<select class='form-control type' name='type'>";
                                tag += "<option value=''></option>";
                                $.each(earnings, (i, val)=>{
                                    tag += "<option value='"+val.id+"'>"+val.name+"</option>";
                                });
                            tag += "</select>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='fri'>";
                            tag += "<label>OT HOURS:</label>";
                            tag += "<input type='text' class='form-control input-sm ot-hours' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='fri'>";
                            tag += "<label>LATE HOURS:</label>";
                            tag += "<input type='text' class='form-control input-sm late-hours' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='fri'>";
                            tag += "<label>UNDERTIME:</label>";
                            tag += "<input type='text' class='form-control input-sm undertime' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group'>";
                            tag += "<label>SCHEDULE STATUS:</label>";
                            tag += "<div class='schedule-status sch-"+(row.fri!==null?row.fri.split('|')[7]:'0')+"'>"+sched_status+"</div>";
                        tag += "</div>";
                    tag += "</div>";

                    $('#entry_fri .time-in').attr('min', date_current + " 00:00");
                    $('#entry_fri .time-in').attr('max', date_current + " 23:59");
                    $('#entry_fri .time-out').attr('min', date_current + " 00:00");

                    $('#entry_fri .time-in').val((row.fri!==null?row.fri.split('|')[0]:date_current + " 00:00"));
                    $('#entry_fri .time-out').val((row.fri!==null?row.fri.split('|')[1]:date_current + " 00:00"));
                    $('#entry_fri .total-hours').val((row.fri!==null?row.fri.split('|')[2]:'0.00'));
                    $('#entry_fri .type').val((row.fri!==null?row.fri.split('|')[3]:''));
                    $('#entry_fri .ot-hours').val((row.fri!==null?row.fri.split('|')[4]:'0.00'));
                    $('#entry_fri .late-hours').val((row.fri!==null?row.fri.split('|')[5]:'0.00'));
                    $('#entry_fri .undertime').val((row.fri!==null?row.fri.split('|')[6]:'0.00'));
                    
                    return tag;
                }},
                { data: "sat", title:"SATURDAY <br> " + scion.getDateRange($('#date-filter').val(), 7).current, render: function(data, type, row, meta) {
                    var tag = "";
                    var sched_status = '-';
                    var date_current = scion.getDateRange($('#date-filter').val(), 7).current;
                    
                    if((row.sat!==null?row.sat.split('|')[7]:'0') === "1") {
                        sched_status = 'WITH SCHEDULE';
                    }
                    else if((row.sat!==null?row.sat.split('|')[7]:'0') === "2"){
                        sched_status = 'NO SCHEDULE';
                    }

                    tag += "<div class='time-entry' id='entry_sat'>";
                        tag += "<div class='form-group' day='sat'>";
                            tag += "<label>TIME IN:</label>";
                            tag += "<input type='datetime-local' class='form-control input-sm time-in'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='sat'>";
                            tag += "<label>TIME OUT:</label>";
                            tag += "<input type='datetime-local' class='form-control input-sm time-out'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='sat'>";
                            tag += "<label>TOTAL HOURS:</label>";
                            tag += "<input type='text' class='form-control input-sm total-hours' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='sat'>";
                            tag += "<label>TYPE:</label>";
                            tag += "<select class='form-control type' name='type'>";
                                tag += "<option value=''></option>";
                                $.each(earnings, (i, val)=>{
                                    tag += "<option value='"+val.id+"'>"+val.name+"</option>";
                                });
                            tag += "</select>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='sat'>";
                            tag += "<label>OT HOURS:</label>";
                            tag += "<input type='text' class='form-control input-sm ot-hours' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='sat'>";
                            tag += "<label>LATE HOURS:</label>";
                            tag += "<input type='text' class='form-control input-sm late-hours' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group' day='sat'>";
                            tag += "<label>UNDERTIME:</label>";
                            tag += "<input type='text' class='form-control input-sm undertime' value='0.00'/>";
                        tag += "</div>";
                        tag += "<div class='form-group'>";
                            tag += "<label>SCHEDULE STATUS:</label>";
                            tag += "<div class='schedule-status sch-"+(row.sat!==null?row.sat.split('|')[7]:'0')+"'>"+sched_status+"</div>";
                        tag += "</div>";
                    tag += "</div>";
                    
                    $('#entry_sat .time-in').attr('min', date_current + " 00:00");
                    $('#entry_sat .time-in').attr('max', date_current + " 23:59");
                    $('#entry_sat .time-out').attr('min', date_current + " 00:00");

                    $('#entry_sat .time-in').val((row.sat!==null?row.sat.split('|')[0]:date_current + " 00:00"));
                    $('#entry_sat .time-out').val((row.sat!==null?row.sat.split('|')[1]:date_current + " 00:00"));
                    $('#entry_sat .total-hours').val((row.sat!==null?row.sat.split('|')[2]:'0.00'));
                    $('#entry_sat .type').val((row.sat!==null?row.sat.split('|')[3]:''));
                    $('#entry_sat .ot-hours').val((row.sat!==null?row.sat.split('|')[4]:'0.00'));
                    $('#entry_sat .late-hours').val((row.sat!==null?row.sat.split('|')[5]:'0.00'));
                    $('#entry_sat .undertime').val((row.sat!==null?row.sat.split('|')[6]:'0.00'));
                    
                    return tag;
                }}
            ], '', [], true, false
        );
    });

}

function filter(department) {
    var first = scion.getDateRange($('#date-filter').val(), 1).first;
    var last = scion.getDateRange($('#date-filter').val(), 1).last;

    $('#employee_table').DataTable().destroy().clear().draw();

    scion.create.table(
        'employee_table',  
        module_url + '/get/'+department+'/' + scion.getDateRange($('#date-filter').val(), 1).first + '/' + scion.getDateRange($('#date-filter').val(), 1).last, 
        [
            { data: "firstname", title:"EMPLOYEE", className: "name-list", render: function(data, type, row, meta) {
                return "<div class='employee_info' onclick='showDetails("+row.id+")'>" + row.firstname + " " + (row.middlename !== null && row.middlename !== ""?row.middlename + " ":"") + row.lastname + (row.suffix !== null && row.suffix !== ""?" " + row.suffix:"") + "</div>";
            }},
            { data: null, title: "STATUS", render: function(data, type, row, meta) {
                return row.approval !== null?'APPROVED':'PENDING';
            }},
            { data: "reg", title: "REG", render: function(data) {
                return formatTwoDecimals(data);
            }},
            { data: "ot", title: "OT"},
            { data: "sick", title: "SICK"},
            { data: "vac", title: "VAC"},
            { data: "total", title: "TOTAL"}
        ], 'Brtip', exportButtons, false, false
    );
}

function showDetails(id) {
    var regular_hours = 0;
    var regular_days = 0;
    var overtime = 0;
    var working_hours = 0;

    record_id = id;

    $.post(module_url + '/get_record/' + id, {_token: _token, date: $('#date-filter').val()}, function(response) {
        var timesheet = '';

        var drate = response.record.compensations !== null?response.record.compensations.daily_salary:0;
        var hrate = response.record.compensations !== null?response.record.compensations.hourly_salary:0;
        var total_earnings = 0;
        var total_ot = 0;
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

        if(response.record.approval !== null) {
            $('#timesheet_status').text('APPROVED');
            $('.approve-btn').css('display', 'none');
        }
        else {
            $('#timesheet_status').text('DRAFT');
            $('.approve-btn').css('display', 'inline-block');
        }

        $('#employee_name').text(response.record.firstname + (response.record.middlename !== "" && response.record.middlename !== null?" " + response.record.middlename:"") + " " + response.record.lastname);
        $('#employee_number').text(response.record.employee_no);
        $('#monthly_salary').text(response.record.compensations !== null?scion.currency(response.record.compensations.monthly_salary):'0');
        $('#daily_salary').text(response.record.compensations !== null?scion.currency(response.record.compensations.daily_salary):'0');
        $('#hourly_rate').text(scion.currency(hrate));

        $('#h_rate').text(scion.currency(hrate));

        if(response.semi_monthly !== null){
            var earning = "";
            var ot = "";
            var leave = "";
            var holiday = "";
            var allowance = "";
            var deduction = "";
            var tax_amount = 0;

            $('#pay_period').text(response.other.pay_period);
            $('#pay_date').text(response.other.pay_date);
            $('#pay_type').text(response.record.employments_tab.calendar.title);

            response.other.deductions.forEach((item) => {
                deduction += "<tr>";
                deduction += `<td style="width:90%" colspan="3"><i class="fas fa-trash text-danger"></i> ${item.deduction}</td>`;
                deduction += `<td style="width:30%" class="text-center">${scion.currency(item.total)}</td>`;
                deduction += "</tr>";
                other_deduction += item.total;
            });
            absent_record = [];
            
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
                    var regularHourValue = Number(semiMonth.office_hours) - Number(semiMonth.break_time);
                    timesheet += `<td>${formatTwoDecimals(regularHourValue)}</td>`;
                    timesheet += `<td>${semiMonth.overtime}</td>`;
                    timesheet += `<td>${formatTwoDecimals(regularHourValue + Number(semiMonth.overtime))}</td>`;
                timesheet += `</tr>`;

                regular_hours += regularHourValue;
                regular_days += semiMonth.status !== "HOLIDAY"?parseFloat(semiMonth.office_hours) === 0?0:1:0;
                count_days_selected += parseFloat(semiMonth.office_hours) === 0?0:1;
                overtime += parseFloat(semiMonth.overtime);
                working_hours += regularHourValue + Number(semiMonth.overtime);
                
                if(semiMonth.status === "ABSENT") {
                    absent_record.push({
                        'employee_id': record_id,
                        'date': semiMonth.date
                    });
                }
            });

            response.other.earnings.forEach(data => {
                if(data.earning !== null) {
                    // let rate = parseFloat(hrate*data.earning.multiplier);
                    let rate_2 = parseFloat(drate*data.earning.multiplier);
                    let hours = data.earning.code === "RE"?regular_hours:data.earning.code === "OT"?overtime:0;
                    let days = data.earning.code === "RE"?regular_days:0;
                    
                    // if(parseFloat(rate*hours) !== 0) {
                    if(parseFloat(rate_2*days) !== 0) {
                        earning += "<tr>";
                        earning += `<td style="width:50%">${data.earning.name}</td>`;
                        earning += `<td style="width:10%" class="text-left">${scion.currency(rate_2)}</td>`;
                        earning += `<td style="width:10%"></td>`;
                        earning += `<td style="width:10%" class="text-left">${days}</td>`;
                        earning += `<td style="width:10%"></td>`;
                        earning += `<td style="width:10%" class="text-left">${scion.currency(parseFloat(rate_2*days))}</td>`;
                        earning += "</tr>";
                    }

                    total_earnings += parseFloat(rate_2*days);
                }
            });
            
            response.other.allowances.forEach((item) => {
                allowance += "<tr>";
                allowance += `<td style="width:50%">${item.allowance}</td>`;
                allowance += `<td style="width:10%;text-align:left;">${scion.currency(item.total)}</td>`;
                allowance += `<td style="width:10%"></td>`;
                allowance += `<td style="width:10%;text-align:left;">${item.days}</td>`;
                allowance += `<td style="width:10%"></td>`;
                allowance += `<td style="width:10%;text-align:left;">${scion.currency(item.grand_total)}</td>`;
                allowance += "</tr>";

                total_earnings += parseFloat(item.grand_total);
            });
            
            response.other.holiday.forEach((data, i) => {
                let rate = parseFloat(drate*data.holiday_type.multiplier);
                let hours = parseFloat((i+1));

                holiday += "<tr>";
                holiday += `<td style="width:50%">REGULAR HOLIDAY</td>`;
                holiday += `<td style="width:10%" class="text-left">${scion.currency(rate)}</td>`;
                holiday += `<td style="width:10%"></td>`;
                holiday += `<td style="width:10%" class="text-left">${hours}</td>`;
                holiday += `<td style="width:10%"></td>`;
                holiday += `<td style="width:10%" class="text-left">${scion.currency(parseFloat(rate*hours))}</td>`;
                holiday += "</tr>";

                total_earnings += parseFloat(rate*hours);
            });
            
            if(response.other.overtime !== 0) {
                let rate = parseFloat(hrate*response.ot_earning.multiplier);

                ot += "<tr>";
                ot += `<td style="width:50%">OVERTIME</td>`;
                ot += `<td style="width:10%" class="text-left"></td>`;
                ot += `<td style="width:10%" class="text-left">${scion.currency(rate)}</td>`;
                ot += `<td style="width:10%" class="text-left"></td>`;
                ot += `<td style="width:10%" class="text-left">${formatTwoDecimals(overtime)}</td>`;
                ot += `<td style="width:10%" class="text-left">${scion.currency(parseFloat((hrate*response.ot_earning.multiplier)*overtime))}</td>`;
                ot += "</tr>";

                total_earnings += parseFloat((hrate*response.ot_earning.multiplier)*overtime);
            }

            response.other.leave.forEach(data => {
                let rate = parseFloat(drate*1);
                let hours = data.total_leave_hours;

                leave += "<tr>";
                leave += `<td style="width:16.67%">${data.leave_type.leave_name}</td>`;
                leave += `<td style="width:16.67%" class="text-left">${scion.currency(rate)}</td>`;
                leave += `<td style="width:16.67%" class="text-left">${hours}</td>`;
                leave += `<td style="width:16.67%" class="text-left">${scion.currency(parseFloat(rate*hours))}</td>`;
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

                $('#total_earnings').text(scion.currency(total_earnings));
                $('#other_deduction').text(scion.currency(other_deduction));
                $('#total_leaves').text(scion.currency(total_leaves));
                $('#total_gross').text(scion.currency(total_gross));

                $('#total_deduction').text(scion.currency(total_deduction));

                $('#total_government_deduction').text(scion.currency(government_deduction));
                
                $('#total_sss').text(scion.currency(sss));
                $('#total_philhealth').text(scion.currency(philhealth));
                $('#total_pagibig').text(scion.currency(pagibig));

                $('#tax_amount').text(scion.currency(tax_amount));
                $('#net_pay').text(scion.currency(net_pay));
                $('#total_net_pay').text(scion.currency(net_pay));
                $('#withholding_tax').text(scion.currency(response.w_tax));

                hold_approval = {
                    gross_earnings: total_gross,
                    sss: sss,
                    philhealth: philhealth,
                    pagibig: pagibig,
                    tax: wtax,
                    net_pay: net_pay
                };
            });


            $('.holiday-container').html(holiday);
            $('#payroll_rate_details .custom').html(earning);
            $('#payroll_rate_details .custom-2').html(ot);
            $('#payroll_leaves tbody').html(leave);
            $('.allowance-container').html(allowance);
            $('#payroll_other_deductions tbody').html(deduction);

            $('#sequence_no').text("M-" + moment(response.other.pay_period).format('MMDDY'));

            $('#total_regular_hours').text(formatTwoDecimals(regular_hours));
            $('#total_overtime').text(formatTwoDecimals(overtime));
            $('#total_working_hours').text(working_hours);
    
            $('#timesheet tbody').html(timesheet);
    
            scion.create.sc_modal("details_form", "Details").show();
        } 
        else {
            toastr.error(`Please setup a payroll calendar for <b style='font-weight:bold;'>${response.record.firstname + (response.record.middlename !== "" && response.record.middlename !== null?" " + response.record.middlename:"") + " " + response.record.lastname}</b>`, "Generating Timesheet Failed")
        }


    })
}

function releasePayroll() {
    var calendar = '';

    $.get(`/payroll/time_logs/get_calendar/${$('select#pay_type').val()}`, function(response) {
        calendar += `<option value=""></option>`;
        response.calendar.forEach(item => {
            calendar += `<option value="${item.id}">${item.title}</option>`;
        });

        $('#payroll_calendar').html(calendar);

        scion.create.sc_modal("payroll_summary_form", "Payroll Summary").show();
        
        modal_content = 'payroll_summary';
        module_url = '/payroll/summary/manual';
        actions = 'save';
        record_id = null;

        scion.centralized_button(true, false, true, true);
        
        $('#period_start').val("");
        $('#payroll_period').val("");
        $('input#pay_date').val("");
        $('.timesheet-approve .count').text("0");
    });
    
}

function summaryClose() {
    modal_content = 'time_logs';
    module_url = '/payroll/time_logs';
    scion.centralized_button(true, true, true, true);
}

function selectCalendar() {
    var date = {
        _token: _token,
        selected: $('#date-filter').val(),
        calendar: $('#payroll_calendar').val(),
    };

    $.post('/payroll/time_logs/get_summary', date)
    .done(function(response) {
        $('#period_start').val(response.return.arrange.start_date);
        $('#payroll_period').val(response.return.arrange.end_date);
        $('input#pay_date').val(response.return.arrange.pay_date);

        $('.timesheet-approve .count').text(response.return.timesheet);
    });
}

function approveTimesheet() {
    $.post('/payroll/time_logs/get_dates', {_token: _token, date: $('#date-filter').val(), employee_id: record_id})
    .done(function(response) {
        approval_data = response.data;
        scion.create.sc_modal("timesheet_approval_form", "Payroll Summary").show();
        $('.sequence').text($('#sequence_no').text());
    });
}

function yesApprove() {
    approval_data._token = _token;
    approval_data.gross_earnings = hold_approval.gross_earnings;
    approval_data.net_pay = hold_approval.net_pay;
    approval_data.pagibig = hold_approval.pagibig;
    approval_data.philhealth = hold_approval.philhealth;
    approval_data.sss = hold_approval.sss;
    approval_data.tax = hold_approval.tax;

    approval_data.absents = absent_record;

    $.post('/payroll/time_logs/approve', approval_data).done(function(response) {
        $('#timesheet_status').text('APPROVED');
        $('.approve-btn').css('display', 'none');
        scion.create.sc_modal('timesheet_approval_form').hide()
        toastr.success('Timesheet Approved', 'Approval Request');
        $('#employee_table').DataTable().draw();
    });
}

function addAllowance() {
    var html = "";

    modal_content = 'allowance_setup';
    module_url = '/payroll/allowance_setup';
    actions = 'save';
    emp_id = record_id;
    record_id = null;

    scion.centralized_button(true, false, true, true);

    $.post('/payroll/allowance/get-tag', { _token: _token, employee_id: emp_id }).done((response)=>{
        html += '<option value=""></option>'
        $.each(response.allowance, (i,v)=>{
            html += `<option value="${v.allowance_id}">${v.allowances.name}</option>`;
        }); 

        $('#allowance_id').html(html);
        $('#days').val(count_days_selected);

        countTotalAmount();

        scion.create.sc_modal("allowance_form", "Add Allowance").show();
    });

}

function allowanceClose() {
    modal_content = 'time_logs';
    module_url = '/payroll/time_logs';
    actions = null;
    record_id = emp_id;
    emp_id = null;

    $('#allowance_id').val('');
    $('#date').val('');
    $('#amount').val('');
    $('#days').val('');
    $('#total_amount').val('');

    scion.create.sc_modal('allowance_form').hide();
}


function addDeduction() {
    modal_content = 'deduction_setup';
    module_url = '/payroll/deduction_setup';
    actions = 'save';
    emp_id = record_id;
    record_id = null;

    scion.centralized_button(true, false, true, true);
    scion.create.sc_modal("deduction_form", "Add Deduction").show();
}

function deductionClose() {
    modal_content = 'time_logs';
    module_url = '/payroll/time_logs';
    actions = null;
    record_id = emp_id;
    emp_id = null;

    $('#deduction_id').val('');
    $('#date').val('');
    $('#amount').val('');

    scion.create.sc_modal('deduction_form').hide();
}

function editTimesheet() {
    timelog_edit = timelog_edit === 0?1:0;

    $('.edit-btn').text(timelog_edit !== 0?"SAVE CHANGES":"EDIT TIMESHEET");
    $('.btn-cancel').css('display', timelog_edit !== 0?'inline-block':'none');


    if(timelog_edit === 0) {
        timesheet_record = [];
        $.each($('#timesheet tbody tr'), (i,v) => {
            timesheet_record.push({
                employee_id: record_id,
                date: $('#' + v.id + ' .tm-date').text(),
                type: 1,
                time_in: $('#' + v.id + ' .time-in').val() !== ''?$('#' + v.id + ' .tm-date').text() + ' ' + $('#' + v.id + ' .time-in').val():null,
                time_out: $('#' + v.id + ' .time-out').val() !== ''?$('#' + v.id + ' .tm-date').text() + ' ' + $('#' + v.id + ' .time-out').val():null,
                break_in: $('#' + v.id + ' .break-in').val() !== ''?$('#' + v.id + ' .tm-date').text() + ' ' + $('#' + v.id + ' .break-in').val():null,
                break_out: $('#' + v.id + ' .break-out').val() !== ''?$('#' + v.id + ' .tm-date').text() + ' ' + $('#' + v.id + ' .break-out').val():null,
                ot_in: $('#' + v.id + ' .ot-in').val() !== ''?$('#' + v.id + ' .tm-date').text() + ' ' + $('#' + v.id + ' .ot-in').val():null,
                ot_out: $('#' + v.id + ' .ot-out').val() !== ''?$('#' + v.id + ' .tm-date').text() + ' ' + $('#' + v.id + ' .ot-out').val():null,
            });
        });

        $.post('/payroll/time_logs/save', { _token: _token, record: timesheet_record }).done((i,v) => {}).fail((i,v)=>{})

    }
    showDetails(record_id);
}

function cancelTimesheet() {
    timelog_edit = timelog_edit === 0?1:0;

    $('.edit-btn').text(timelog_edit !== 0?"SAVE CHANGES":"EDIT TIMESHEET");

    $('.btn-cancel').css('display', timelog_edit !== 0?'inline-block':'none');

    showDetails(record_id);
}

function countTotalAmount() {
    $('#total_amount').val(parseFloat($('#amount').val() * $('#days').val()));
}

function getAllowanceAmount() {
    $.get('/payroll/allowance/get-amount/' + $('#allowance_id').val(), (response)=>{
        $('#amount').val(response.allowance.amount);

        countTotalAmount();
    });
}

function formatTwoDecimals(value) {
    var numericValue = Number(value);
    return isNaN(numericValue) ? '0.00' : numericValue.toFixed(2);
}
