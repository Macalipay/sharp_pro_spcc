
var hold_id = null;
var time_logs_id = null;

$(function() {
    modal_content = 'daily';
    module_url = '/payroll/timesheet/daily';
    module_type = 'custom';
    page_title = "Timesheet";

    action = "update";

    scion.centralized_button(true, true, true, true);

    scion.create.table(
        'timesheet_table',  
        module_url + '/get/' + $('#date').val() + '/' + $('#project').val(), 
        [
            { data: "id", title:"", render: function(data, type, row, meta) {
                var html = "";
                // html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/payroll/timesheet/daily/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            {
                data: null,
                title: "Name",
                className: "name-td",
                render: function(data, type, row, meta) {
                    return row.employee !== null?`<span class="expandable" title="${row.employee.firstname} ${row.employee.lastname}">${row.employee.firstname} ${row.employee.lastname}</span>`:`-`;
                }
            },
            {
                data: null,
                title: "Work Schedule",
                className: "work-schedule",
                render: function(data, type, row, meta) {
                    var day = row.day.toLowerCase();

                    return row.schedule !== null?(row.schedule[day + "_start_time"] !== null?moment($('#date').val() + ' ' + row.schedule[day + "_start_time"]).format('hh:mm a') + " - " + moment($('#date').val() + ' ' + row.schedule[day + "_end_time"]).format('hh:mm a'):'NO SCHEDULE'):'REST DAY';
                }
            },
            {
                data: 'time_in',
                title: "Time in",
                render: function(data, type, row, meta) {
                    return data !== null ? moment(data).format('hh:mm a'):'-';
                }
            },
            {
                data: 'break_out',
                title: "Break out",
                render: function(data, type, row, meta) {
                    return data !== null ? moment(data).format('hh:mm a'):'-';
                }
            },
            {
                data: 'break_in',
                title: "Break In",
                render: function(data, type, row, meta) {
                    return data !== null ? moment(data).format('hh:mm a'):'-';
                }
            },
            {
                data: 'time_out',
                title: "Time out",
                render: function(data, type, row, meta) {
                    return data !== null ? moment(data).format('hh:mm a'):'-';
                }
            },
            {
                data: 'ot_in',
                title: "OT in",
                render: function(data, type, row, meta) {
                    return data !== null ? moment(data).format('hh:mm a'):'-';
                }
            },
            {
                data: 'ot_out',
                title: "OT out",
                render: function(data, type, row, meta) {
                    return data !== null ? moment(data).format('hh:mm a'):'-';
                }
            },
            {
                data: null,
                title: "Tardiness",
                render: function(data, type, row, meta) {
                    var day = row.day.toLowerCase();
                    var late = (row.schedule !== null ?(row.schedule[day + "_start_time"] !== null?calculateTotalHours($('#date').val() + ' ' + row.schedule[day + "_start_time"], row.time_in):0):0).toFixed(2);
                    
                    return row.time_in !== null? convertToMinutes(late):'-'; 
                }
            },
            {
                data: null,
                title: "Total hours",
                render: function(data, type, row, meta) {
                    var working_hours = (calculateTotalHours(row.time_in, row.time_out) - (row.break_out !== null?calculateTotalHours(break_in, break_out):1));
                    var ot_hours = row.ot_in !== null?(calculateTotalHours(row.ot_in, row.ot_out)):0;

                    var total_hours = row.time_out !== null?working_hours + ot_hours:0;
                    
                    var button = "";

                    if(row.employee !== null) {
                        if(parseFloat(total_hours) >= parseFloat(row.rendered + 1)) {  
                            button = `<button class="btn btn-sm btn-light btn-block ot-stats" onclick="viewDetailedWork(${row.employee_id}, ${total_hours}, '${row.employee.firstname + " " + row.employee.lastname}', ${row.id})">${total_hours.toFixed(2)}</button>`;
                        }
                        else {
                            button = `<button class="btn btn-sm btn-light btn-block" onclick="viewDetailedWork(${row.employee_id}, ${total_hours}, '${row.employee.firstname + " " + row.employee.lastname}', ${row.id})">${total_hours.toFixed(2)}</button>`;
                        }
                    }

                    $($('.ot-stats').parent().parent()).addClass('marked-ot');

                    return row.time_in !== null? button:'-';
                }
            },
            {
                data: null,
                title: "Number of work",
                render: function(data, type, row, meta) {
                    return row.workdetails !== null?row.workdetails.length:0;
                }
            },
            {
                data: null,
                title: "Detailed Work",
                render: function(data, type, row, meta) {
                    var d = "";

                    if(row.workdetails !== null) {
                        $.each(row.workdetails, (i,v) => {
                            d += `${v.worktype.name}: ${v.hours} hrs | `;
                        });
                        d += '-';
                        d = d.replace('|-','');
                    }
                    else {
                        d = '-';
                    }

                    return `<span class="expandables">${d}</span>`;
                }
            }

        ], '', []
    );

});

function success() {
    switch(actions) {
        case 'save':
            if(modal_content === "work_details") {
                workDetailTable();
                scion.create.sc_modal('add_details').hide();
                scion.centralized_button(true, true, true, true);
                $('#timesheet_table').DataTable().draw();
            }
            else if(modal_content === "daily") {
                scion.create.sc_modal('daily_form').hide();
                scion.centralized_button(true, true, true, true);
                $('#timesheet_table').DataTable().draw();
            }
            break;
        case 'update':
            if(modal_content === "work_details") {
                workDetailTable();
                scion.create.sc_modal('add_details').hide();
                scion.centralized_button(true, true, true, true);
                $('#timesheet_table').DataTable().draw();
            }
            else if(modal_content === "daily") {
                scion.create.sc_modal('daily_form').hide('all');
                scion.centralized_button(true, true, true, true);
                $('#timesheet_table').DataTable().draw();
            }
            break;
    }
}

function error() {
    toastr.error('Record already exist.', 'Failed')
}

function delete_success() {
    if(modal_content === "work_details") {
        workDetailTable();
        $('#timesheet_table').DataTable().draw();
    }
}

function delete_error() {}

function generateData() {
    switch(actions) {
        case 'save':
            if(modal_content === "work_details") {
                form_data = {
                    _token: _token,
                    employee_id: hold_id,
                    time_logs_id: time_logs_id,
                    worktype_id: $('#worktype_id').val(),
                    project_id: $('#project_id').val(),
                    earnings: $('#earnings').val(),
                    hours: $('#hours').val(),
                    remarks: $('#remarks').val()
                };
            }
            else if(modal_content === "daily") {
                form_data = {
                    _token: _token,
                    time_in: $('#time_in').val(),
                    time_out: $('#time_out').val(),
                    break_in: $('#break_in').val(),
                    break_out: $('#break_out').val(),
                    ot_in: $('#ot_in').val(),
                    ot_out: $('#ot_out').val(),
                };
            }
            break;
        case 'update':
            if(modal_content === "work_details") {
                form_data = {
                    _token: _token,
                    employee_id: hold_id,
                    time_logs_id: time_logs_id,
                    worktype_id: $('#worktype_id').val(),
                    project_id: $('#project_id').val(),
                    earnings: $('#earnings').val(),
                    hours: $('#hours').val(),
                    remarks: $('#remarks').val()
                };
            }
            else if(modal_content === "daily") {
                form_data = {
                    _token: _token,
                    time_in: $('#time_in').val(),
                    time_out: $('#time_out').val(),
                    break_in: $('#break_in').val(),
                    break_out: $('#break_out').val(),
                    ot_in: $('#ot_in').val(),
                    ot_out: $('#ot_out').val(),
                };
            }
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
}

function closeWorkDetails() {
    modal_content = 'daily';
    module_url = '/payroll/timesheet/daily';
    module_type = 'custom';
    page_title = "Timesheet";

    scion.centralized_button(true, true, true, true);
}

function timesheetTable() {
    
    if ($.fn.DataTable.isDataTable('#timesheet_table')) {
        $('#timesheet_table').DataTable().destroy();
    }
    
    scion.create.table(
        'timesheet_table',  
        module_url + '/get/' + $('#date').val() + '/' + $('#project').val(), 
        [
            { data: "id", title:"", render: function(data, type, row, meta) {
                var html = "";
                // html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/payroll/timesheet/daily/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                return html;
            }},
            {
                data: null,
                title: "Name",
                className: "name-td",
                render: function(data, type, row, meta) {
                    return row.employee !== null?`<span class="expandable" title="${row.employee.firstname} ${row.employee.lastname}">${row.employee.firstname} ${row.employee.lastname}</span>`:`-`;
                }
            },
            {
                data: null,
                title: "Work Schedule",
                className: "work-schedule",
                render: function(data, type, row, meta) {
                    var day = row.day.toLowerCase();

                    return row.schedule !== null?(row.schedule[day + "_start_time"] !== null?moment($('#date').val() + ' ' + row.schedule[day + "_start_time"]).format('hh:mm a') + " - " + moment($('#date').val() + ' ' + row.schedule[day + "_end_time"]).format('hh:mm a'):'NO SCHEDULE'):'REST DAY';
                }
            },
            {
                data: 'time_in',
                title: "Time in",
                render: function(data, type, row, meta) {
                    return data !== null ? moment(data).format('hh:mm a'):'-';
                }
            },
            {
                data: 'break_out',
                title: "Break out",
                render: function(data, type, row, meta) {
                    return data !== null ? moment(data).format('hh:mm a'):'-';
                }
            },
            {
                data: 'break_in',
                title: "Break In",
                render: function(data, type, row, meta) {
                    return data !== null ? moment(data).format('hh:mm a'):'-';
                }
            },
            {
                data: 'time_out',
                title: "Time out",
                render: function(data, type, row, meta) {
                    return data !== null ? moment(data).format('hh:mm a'):'-';
                }
            },
            {
                data: 'ot_in',
                title: "OT in",
                render: function(data, type, row, meta) {
                    return data !== null ? moment(data).format('hh:mm a'):'-';
                }
            },
            {
                data: 'ot_out',
                title: "OT out",
                render: function(data, type, row, meta) {
                    return data !== null ? moment(data).format('hh:mm a'):'-';
                }
            },
            {
                data: null,
                title: "Tardiness",
                render: function(data, type, row, meta) {
                    var day = row.day.toLowerCase();
                    var late = (row.schedule !== null ?(row.schedule[day + "_start_time"] !== null?calculateTotalHours($('#date').val() + ' ' + row.schedule[day + "_start_time"], row.time_in):0):0).toFixed(2);
                    
                    return row.time_in !== null? convertToMinutes(late):'-'; 
                }
            },
            {
                data: null,
                title: "Total hours",
                render: function(data, type, row, meta) {
                    var working_hours = (calculateTotalHours(row.time_in, row.time_out) - (row.break_out !== null?calculateTotalHours(break_in, break_out):1));
                    var ot_hours = row.ot_in !== null?(calculateTotalHours(row.ot_in, row.ot_out)):0;

                    var total_hours = row.time_out !== null?working_hours + ot_hours:0;
                    
                    var button = "";

                    if(row.employee !== null) {
                        if(parseFloat(total_hours) >= parseFloat(row.rendered + 1)) {  
                            button = `<button class="btn btn-sm btn-light btn-block ot-stats" onclick="viewDetailedWork(${row.employee_id}, ${total_hours}, '${row.employee.firstname + " " + row.employee.lastname}', ${row.id})">${total_hours.toFixed(2)}</button>`;
                        }
                        else {
                            button = `<button class="btn btn-sm btn-light btn-block" onclick="viewDetailedWork(${row.employee_id}, ${total_hours}, '${row.employee.firstname + " " + row.employee.lastname}', ${row.id})">${total_hours.toFixed(2)}</button>`;
                        }
                    }

                    $($('.ot-stats').parent().parent()).addClass('marked-ot');

                    return row.time_in !== null? button:'-';
                }
            },
            {
                data: null,
                title: "Number of work",
                render: function(data, type, row, meta) {
                    return row.workdetails !== null?row.workdetails.length:0;
                }
            },
            {
                data: null,
                title: "Detailed Work",
                render: function(data, type, row, meta) {
                    var d = "";

                    if(row.workdetails !== null) {
                        $.each(row.workdetails, (i,v) => {
                            d += `${v.worktype.name}: ${v.hours} hrs | `;
                        });
                        d += '-';
                        d = d.replace('|-','');
                    }
                    else {
                        d = '-';
                    }

                    return `<span class="expandables">${d}</span>`;
                }
            }

        ], '', []
    );
}

function calculateTotalHours(startTime, endTime) {
    const start = new Date(startTime);
    const end = new Date(endTime);

    const differenceInMilliseconds = end - start;

    const hours = differenceInMilliseconds / (1000 * 60 * 60);

    return hours < 0 ? hours + 24 : hours;
}

function viewDetailedWork(id, hours, name, timelog_id) {
    modal_content = 'work_details';
    module_url = '/payroll/timesheet/work-details';
    module_type = 'custom';
    actions = 'save';

    scion.create.sc_modal("detialed_work_modal", 'Detailed Work').show(() => {
        hold_id = id;
        time_logs_id = timelog_id;

        $('#total_hours').text(hours);
        $('#employee_name').text(name);

        workDetailTable();

        scion.centralized_button(true, true, true, true);
    });
}

function add_details() {
    actions = 'save';
    record_id = null;
    scion.create.sc_modal("add_details", 'Add').show(() => {
        scion.centralized_button(true, false, true, true);
    });
}

function workDetailTable() {
    var total = 0;

    $.get(`${module_url}/get/${time_logs_id}`, (response) => {
        var table = "";
        $.each(response.workdetails, (i,v)=> {
            table += `<tr>
                <td><a href="#" onclick="editWorkDetail(${v.id})"><i class="fas fa-pencil-alt"></i></a> <a href="#" onclick="deleteWorkDetail(${v.id})"><i class="fas fa-trash-alt"></i></a></td>
                <td>${v.worktype.name}</td>
                <td>${v.earnings === "RE"?'REGULAR':'OVER TIME'}</td>
                <td>${v.projects !== null?v.projects.project_name:'-'}</td>
                <td>${v.hours}</td>
                <td>${v?.remarks||''}</td>
            </tr>`;

            total += parseFloat(v.hours);
        });

        $('#status_rendered').html(parseFloat($('#total_hours').text()) === total?`<span class="text-success">Total hours are matched.</span>`:`<span class="text-danger">Total hours don't matched.</span>`);
        parseFloat($('#total_hours').text()) === total

        $('.total_hours_rendered').text(total.toFixed(2));
        $('#work_details_table tbody').html(table);
    });
}

function editWorkDetail(id) {
    actions = 'update';
    record_id = id;
    
    $.get(`${module_url}/edit/${id}`, function(response) {
        console.log(response);
        var work = response.workdetails;

        $('#worktype_id').val(work.worktype_id);
        $('#earnings').val(work.earnings);
        $('#hours').val(work.hours);
        $('#remarks').val(work.remarks);
        
        scion.create.sc_modal("add_details", 'Update').show(() => {
            scion.centralized_button(true, false, true, true);
        });
    });
}

function deleteWorkDetail(id) {
    delete_data.push(id);
    scion.record.delete(generateDeleteItems);
}

function convertToMinutes(decimalTimeString) {
    var timeParts = decimalTimeString.split(".");
    return Number(timeParts[0]) * 60 + Number(timeParts[1]);
}