var emp_id = null;
var date_selected = null;
var copied_schedule = [];

$(function() {
    modal_content = 'scheduling';
    module_url = '/payroll/scheduling';
    module_type = 'custom';
    page_title = "Scheduling";

    const today = new Date();

    $('#date-filter').val(moment(today).format('YYYY-MM-DD'));

    scion.centralized_button(true, true, true, true);
    
    scion.create.table(
        'employee_table',  
        module_url + '/get/all/' + scion.getDateRange($('#date-filter').val(), 1).first + '/' + scion.getDateRange($('#date-filter').val(), 1).last, 
        [
            { data: "firstname", title:"EMPLOYEE", render: function(data, type, row, meta) {
                return "<div class='employee_info'><img src='/images/payroll/employee-information/"+row.profile_img+"'/>" + row.firstname + " " + (row.middlename !== null && row.middlename !== ""?row.middlename + " ":"") + row.lastname + (row.suffix !== null && row.suffix !== ""?" " + row.suffix:"") + "</div>";
            }},
            { data: "sun", title:"SUNDAY <br> " + scion.getDateRange($('#date-filter').val(), 1).current, render: function(data, type, row, meta) {
                var tag = "";
                if(row.sun !== null) {
                    if(row.sun.split('|')[2] === "0") {
                        tag = "<div class='time-set'><span class='time-range'>"+row.sun.split('|')[0]+"</span><span class='assignment'>"+row.sun.split('|')[1]+"</span></div>";
                    }
                    else {
                        tag = "<div class='time-set off'><span class='time-range'>"+row.sun.split('|')[3]+"</span></div>";
                    }
                }
                else {
                    tag = '<button class="add-schedule" onclick="addSchedule(1, '+row.id+')"><i class="fas fa-plus"></i></button>'
                }
                return tag;
            }},
            { data: "mon", title:"MONDAY <br> " + scion.getDateRange($('#date-filter').val(), 2).current, render: function(data, type, row, meta) {
                var tag = "";
                if(row.mon !== null) {
                    if(row.mon.split('|')[2] === "0") {
                        tag = "<div class='time-set'><span class='time-range'>"+row.mon.split('|')[0]+"</span><span class='assignment'>"+row.mon.split('|')[1]+"</span></div>";
                    }
                    else {
                        tag = "<div class='time-set off'><span class='time-range'>"+row.mon.split('|')[3]+"</span></div>";
                    }
                }
                else {
                    tag = '<button class="add-schedule" onclick="addSchedule(2, '+row.id+')"><i class="fas fa-plus"></i></button>'
                }
                return tag;
            }},
            { data: "tue", title:"TUESDAY <br> " + scion.getDateRange($('#date-filter').val(), 3).current, render: function(data, type, row, meta) {
                var tag = "";
                if(row.tue !== null) {
                    if(row.tue.split('|')[2] === "0") {
                        tag = "<div class='time-set'><span class='time-range'>"+row.tue.split('|')[0]+"</span><span class='assignment'>"+row.tue.split('|')[1]+"</span></div>";
                    }
                    else {
                        tag = "<div class='time-set off'><span class='time-range'>"+row.tue.split('|')[3]+"</span></div>";
                    }
                }
                else {
                    tag = '<button class="add-schedule" onclick="addSchedule(3, '+row.id+')"><i class="fas fa-plus"></i></button>'
                }
                return tag;
            }},
            { data: "wed", title:"WEDNESDAY <br> " + scion.getDateRange($('#date-filter').val(), 4).current, render: function(data, type, row, meta) {
                var tag = "";
                if(row.wed !== null) {
                    if(row.wed.split('|')[2] === "0") {
                        tag = "<div class='time-set'><span class='time-range'>"+row.wed.split('|')[0]+"</span><span class='assignment'>"+row.wed.split('|')[1]+"</span></div>";
                    }
                    else {
                        tag = "<div class='time-set off'><span class='time-range'>"+row.wed.split('|')[3]+"</span></div>";
                    }
                }
                else {
                    tag = '<button class="add-schedule" onclick="addSchedule(4, '+row.id+')"><i class="fas fa-plus"></i></button>'
                }
                return tag;
            }},
            { data: "thu", title:"THURSDAY <br> " + scion.getDateRange($('#date-filter').val(), 5).current, render: function(data, type, row, meta) {
                var tag = "";
                if(row.thu !== null) {
                    if(row.thu.split('|')[2] === "0") {
                        tag = "<div class='time-set'><span class='time-range'>"+row.thu.split('|')[0]+"</span><span class='assignment'>"+row.thu.split('|')[1]+"</span></div>";
                    }
                    else {
                        tag = "<div class='time-set off'><span class='time-range'>"+row.thu.split('|')[3]+"</span></div>";
                    }
                }
                else {
                    tag = '<button class="add-schedule" onclick="addSchedule(5, '+row.id+')"><i class="fas fa-plus"></i></button>'
                }
                return tag;
            }},
            { data: "fri", title:"FRIDAY <br> " + scion.getDateRange($('#date-filter').val(), 6).current, render: function(data, type, row, meta) {
                var tag = "";
                if(row.fri !== null) {
                    if(row.fri.split('|')[2] === "0") {
                        tag = "<div class='time-set'><span class='time-range'>"+row.fri.split('|')[0]+"</span><span class='assignment'>"+row.fri.split('|')[1]+"</span></div>";
                    }
                    else {
                        tag = "<div class='time-set off'><span class='time-range'>"+row.fri.split('|')[3]+"</span></div>";
                    }
                }
                else {
                    tag = '<button class="add-schedule" onclick="addSchedule(6, '+row.id+')"><i class="fas fa-plus"></i></button>'
                }
                return tag;
            }},
            { data: "sat", title:"SATURDAY <br> " + scion.getDateRange($('#date-filter').val(), 7).current, render: function(data, type, row, meta) {
                var tag = "";
                if(row.sat !== null) {
                    if(row.sat.split('|')[2] === "0") {
                        tag = "<div class='time-set'><span class='time-range'>"+row.sat.split('|')[0]+"</span><span class='assignment'>"+row.sat.split('|')[1]+"</span></div>";
                    }
                    else {
                        tag = "<div class='time-set off'><span class='time-range'>"+row.sat.split('|')[3]+"</span></div>";
                    }
                }
                else {
                    tag = '<button class="add-schedule" onclick="addSchedule(7, '+row.id+')"><i class="fas fa-plus"></i></button>'
                }
                return tag;
            }},
        ], 'Bfrtip', ['csv']
    );

});


function success() {
    switch(actions) {
        case 'save':
            break;
        case 'update':
            break;
    }
    $('#employee_table').DataTable().draw();
    scion.create.sc_modal('scheduling_form').hide('all', modalHideFunction);
}

function error() {}

function delete_success() {
    $('#employee_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    form_data = {
        _token: _token,
        employee_id: emp_id,
        date: date_selected,
        start_time: $('#start_time').val(),
        end_time: $('#end_time').val(),
        type: $('#type').val(),
        work_assignment_id: $('#work_assignment_id').val(),
        earning_id: $('#earning_id').val(),
        details:'',
        type_description:$('#type_description').val(),
        status:$('#status').val(),
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

function subFunction(property) {
    $('.employee-name span').text(selected_data[property].firstname + ' ' + (selected_data[property].middlename === '' || selected_data[property].middlename === null?'':selected_data[property].middlename + ' ') + selected_data[property].lastname + (selected_data[property].suffix === '' || selected_data[property].suffix === null?'':' ' + selected_data[property].suffix));
    $('.employee-email span').text(selected_data[property].email);
    $('.employee-picture').attr('style', 'background:url(/images/payroll/employee-information/' + selected_data[property].profile_img+')no-repeat;');
}

function addSchedule(dayOfWeek, id) {
    emp_id = id;
    date_selected = scion.getDateRange($('#date-filter').val(), dayOfWeek).current;
    $('#start_time').attr('min', date_selected + " 00:00");
    $('#start_time').attr('max', date_selected + " 23:59");
    $('#end_time').attr('min', date_selected + " 00:00");
    
    scion.record.new();
    $('#start_time').val(date_selected + " 00:00");
    $('#end_time').val(date_selected + " 00:00");
}

function selectType(val) {
    if(val === "1") {
        $('.start_time, .end_time, .work_assignment_id, .earning_id').css('display', 'none');
        $('.type_description').css('display', 'block');
        $('#start_time').val('');
        $('#end_time').val('');
    }
    else {
        $('.start_time, .end_time, .work_assignment_id, .earning_id').css('display', 'block');
        $('.type_description').css('display', 'none');
    }
}

function filter(department) {
    var first = scion.getDateRange($('#date-filter').val(), 1).first;
    var last = scion.getDateRange($('#date-filter').val(), 1).last;

    $('#employee_table').DataTable().destroy().clear().draw();

    scion.create.table(
        'employee_table',  
        module_url + '/get/' + department + '/' + first + '/' + last, 
        [
            { data: "firstname", title:"EMPLOYEE", render: function(data, type, row, meta) {
                return "<div class='employee_info'><img src='/images/payroll/employee-information/"+row.profile_img+"'/>" + row.firstname + " " + (row.middlename !== null && row.middlename !== ""?row.middlename + " ":"") + row.lastname + (row.suffix !== null && row.suffix !== ""?" " + row.suffix:"") + "</div>";
            }},
            { data: "sun", title:"SUNDAY <br> " + scion.getDateRange($('#date-filter').val(), 1).current, width:'150px', render: function(data, type, row, meta) {
                var tag = "";
                if(row.sun !== null) {
                    if(row.sun.split('|')[2] === "0") {
                        tag = "<div class='time-set'><span class='time-range'>"+row.sun.split('|')[0]+"</span><span class='assignment'>"+row.sun.split('|')[1]+"</span></div>";
                    }
                    else {
                        tag = "<div class='time-set off'><span class='time-range'>"+row.sun.split('|')[3]+"</span></div>";
                    }
                }
                else {
                    tag = '<button class="add-schedule" onclick="addSchedule(1, '+row.id+')"><i class="fas fa-plus"></i></button>'
                }
                return tag;
            }},
            { data: "mon", title:"MONDAY <br> " + scion.getDateRange($('#date-filter').val(), 2).current, width:'150px', render: function(data, type, row, meta) {
                var tag = "";
                if(row.mon !== null) {
                    if(row.mon.split('|')[2] === "0") {
                        tag = "<div class='time-set'><span class='time-range'>"+row.mon.split('|')[0]+"</span><span class='assignment'>"+row.mon.split('|')[1]+"</span></div>";
                    }
                    else {
                        tag = "<div class='time-set off'><span class='time-range'>"+row.mon.split('|')[3]+"</span></div>";
                    }
                }
                else {
                    tag = '<button class="add-schedule" onclick="addSchedule(2, '+row.id+')"><i class="fas fa-plus"></i></button>'
                }
                return tag;
            }},
            { data: "tue", title:"TUESDAY <br> " + scion.getDateRange($('#date-filter').val(), 3).current, width:'150px', render: function(data, type, row, meta) {
                var tag = "";
                if(row.tue !== null) {
                    if(row.tue.split('|')[2] === "0") {
                        tag = "<div class='time-set'><span class='time-range'>"+row.tue.split('|')[0]+"</span><span class='assignment'>"+row.tue.split('|')[1]+"</span></div>";
                    }
                    else {
                        tag = "<div class='time-set off'><span class='time-range'>"+row.tue.split('|')[3]+"</span></div>";
                    }
                }
                else {
                    tag = '<button class="add-schedule" onclick="addSchedule(3, '+row.id+')"><i class="fas fa-plus"></i></button>'
                }
                return tag;
            }},
            { data: "wed", title:"WEDNESDAY <br> " + scion.getDateRange($('#date-filter').val(), 4).current, width:'150px', render: function(data, type, row, meta) {
                var tag = "";
                if(row.wed !== null) {
                    if(row.wed.split('|')[2] === "0") {
                        tag = "<div class='time-set'><span class='time-range'>"+row.wed.split('|')[0]+"</span><span class='assignment'>"+row.wed.split('|')[1]+"</span></div>";
                    }
                    else {
                        tag = "<div class='time-set off'><span class='time-range'>"+row.wed.split('|')[3]+"</span></div>";
                    }
                }
                else {
                    tag = '<button class="add-schedule" onclick="addSchedule(4, '+row.id+')"><i class="fas fa-plus"></i></button>'
                }
                return tag;
            }},
            { data: "thu", title:"THURSDAY <br> " + scion.getDateRange($('#date-filter').val(), 5).current, width:'150px', render: function(data, type, row, meta) {
                var tag = "";
                if(row.thu !== null) {
                    if(row.thu.split('|')[2] === "0") {
                        tag = "<div class='time-set'><span class='time-range'>"+row.thu.split('|')[0]+"</span><span class='assignment'>"+row.thu.split('|')[1]+"</span></div>";
                    }
                    else {
                        tag = "<div class='time-set off'><span class='time-range'>"+row.thu.split('|')[3]+"</span></div>";
                    }
                }
                else {
                    tag = '<button class="add-schedule" onclick="addSchedule(5, '+row.id+')"><i class="fas fa-plus"></i></button>'
                }
                return tag;
            }},
            { data: "fri", title:"FRIDAY <br> " + scion.getDateRange($('#date-filter').val(), 6).current, width:'150px', render: function(data, type, row, meta) {
                var tag = "";
                if(row.fri !== null) {
                    if(row.fri.split('|')[2] === "0") {
                        tag = "<div class='time-set'><span class='time-range'>"+row.fri.split('|')[0]+"</span><span class='assignment'>"+row.fri.split('|')[1]+"</span></div>";
                    }
                    else {
                        tag = "<div class='time-set off'><span class='time-range'>"+row.fri.split('|')[3]+"</span></div>";
                    }
                }
                else {
                    tag = '<button class="add-schedule" onclick="addSchedule(6, '+row.id+')"><i class="fas fa-plus"></i></button>'
                }
                return tag;
            }},
            { data: "sat", title:"SATURDAY <br> " + scion.getDateRange($('#date-filter').val(), 7).current, width:'150px', render: function(data, type, row, meta) {
                var tag = "";
                if(row.sat !== null) {
                    if(row.sat.split('|')[2] === "0") {
                        tag = "<div class='time-set'><span class='time-range'>"+row.sat.split('|')[0]+"</span><span class='assignment'>"+row.sat.split('|')[1]+"</span></div>";
                    }
                    else {
                        tag = "<div class='time-set off'><span class='time-range'>"+row.sat.split('|')[3]+"</span></div>";
                    }
                }
                else {
                    tag = '<button class="add-schedule" onclick="addSchedule(7, '+row.id+')"><i class="fas fa-plus"></i></button>'
                }
                return tag;
            }},
        ], 'Bfrtip', ['csv']
    );
}

function copySchedule() {
    var first = scion.getDateRange($('#date-filter').val(), 1).first;
    var last = scion.getDateRange($('#date-filter').val(), 1).last;
    var data = {
        first: first,
        last: last,
        department: $('#department').val()
    };

    scion.record.get('copy_schedule', data, (response)=>{
        if(response.schedulings.length === 0) {
            toastr.error('No schedule selected!');
        }
        else {
            copied_schedule = response.schedulings;
            toastr.info('Schedule Copied!');
            $('.btn-copy').css('display', 'none');
            $('.btn-paste').css('display', 'inline-block');
        }
    });
}

function pasteSchedule() {
    var log = '';
    $.each(copied_schedule, (i, val)=>{
        var new_date = scion.getDateRange($('#date-filter').val(), val.no_day).current;
        
        if(val.date !== new_date) {
            var data = {
                _token: _token,
                employee_id: val.employee_id,
                date: new_date,
                start_time: val.start_time,
                end_time: val.end_time,
                details: val.details,
                type: val.type,
                type_description: val.type_description,
                status: val.status,
                work_assignment_id: val.work_assignment_id,
                earning_id: val.earning_id
            };

            $.post(module_url+'/paste_schedule', data)
            .done(function(response) {})
            .fail(function(response) {});

            log = 'success';
        }
        else {
            log = 'error';
        }
    });

    if(log === "error") {
        toastr.error('Same schedule selected!');
    }
    else if(log === "success") {
        $('#employee_table').DataTable().draw();
        toastr.success('Schedule Saved');
        $('.btn-paste').css('display', 'none');
        $('.btn-copy').css('display', 'inline-block');
    }
}