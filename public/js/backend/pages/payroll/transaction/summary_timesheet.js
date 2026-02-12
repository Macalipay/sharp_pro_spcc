
var hold_id = null;

$(function() {
    modal_content = 'summary';
    module_url = '/payroll/timesheet/summary';
    module_type = 'custom';
    page_title = "Timesheet";

    action = "update";

    scion.centralized_button(true, true, true, true);

    timesheetTable();

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
                scion.create.sc_modal('daily_form').hide();
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
                    worktype_id: $('#worktype_id').val(),
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
                    worktype_id: $('#worktype_id').val(),
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

function timesheetTable() {
    var html = "";

    $.get(`${module_url}/get`, function(response) {

        if(response.results.length !== 0) {
            $.each(response.results, (i,v) => {
                html += `<div class="list-out">
                    <div class="list-title"><b>PROJECT NAME:</b> <span id="project_name">${v.project_name}</span></div>
                    <div class="list-title"><b>WORK TYPE:</b> <span id="work_type">${v.name}</span></div>
                    <div class="list-table">
                        <table>
                            <thead>
                                <th style="width:25%;">Employees</th>
                                <th style="width:25%;">Total Working Hours</th>
                                <th style="width:25%;">Absent</th>
                                <th style="width:25%;">Tardiness</th>
                            </thead>
                            <tbody>`;
                
                $.each(v.employees, (i,v)=>{
                    html += `<tr>
                        <td>${v.firstname} ${v.lastname}</td>
                        <td>${v.total_working_hours} hrs</td>   
                        <td>${v.total_absents} days</td>   
                        <td>${v.total_late} mins.</td>   
                    </tr>`;
                });
    
                html += `</tbody>
                        </table>
                    </div>
                </div>`;
            });
        }
        else {
            html = "<div class='empty-list'>No Records Found</div>";
        }

        $('#timesheet_output').html(html);
    });
}     

function generateRecord() {
    var start = $('#start_date').val();
    var end = $('#end_date').val();
    var project_id = $('#project_id').val();
    var worktype_id = $('#worktype_id').val();
    
    var html = "";

    if(start === "" || end === "") {
        toastr.info("Please select a date");
    }
    else {
        $.post(`${module_url}/get-response`, {_token:_token, start: start, end: end, project_id: project_id, worktype_id: worktype_id}, function(response) {
    
            if(response.results.length !== 0) {
                $.each(response.results, (i,v) => {
                    html += `<div class="list-out">
                        <div class="list-title"><b>PROJECT NAME:</b> <span id="project_name">${v.project_name}</span></div>
                        <div class="list-title"><b>WORK TYPE:</b> <span id="work_type">${v.name}</span></div>
                        <div class="list-table">
                            <table>
                                <thead>
                                    <th>Employees</th>
                                    <th>Total Working Hours</th>
                                    <th>Absent</th>
                                    <th>Tardiness</th>
                                </thead>
                                <tbody>`;
                    
                    $.each(v.employees, (i,v)=>{
                        html += `<tr>
                            <td>${v.firstname} ${v.lastname}</td>
                            <td>${v.total_working_hours} hrs</td>   
                            <td>${v.total_absents} days</td>   
                            <td>${v.total_late} mins.</td>   
                        </tr>`;
                    });
        
                    html += `</tbody>
                            </table>
                        </div>
                    </div>`;
                });
            }
            else {
                html = "<div class='empty-list'>No Records Found</div>";
            }
    
            $('#timesheet_output').html(html);
        });
    }
}

function clearRecord() {
    $('#start_date').val('');
    $('#end_date').val('');
    $('#project_id').val('');
    $('#worktype_id').val('');
    timesheetTable();
}

function filterRecord() {
    scion.create.sc_modal("filter_modal", 'Filter').show(()=> {
        
    });
}

function searchRecord() {

    var start = $('#start_date').val();
    var end = $('#end_date').val();
    var project_id = $('#project_id').val();
    var worktype_id = $('#worktype_id').val();
    var html = "";

    $.post(`${module_url}/get-response`, {_token:_token, start: start, end: end, project_id: project_id, worktype_id: worktype_id}, function(response) {
    
        if(response.results.length !== 0) {
            $.each(response.results, (i,v) => {
                html += `<div class="list-out">
                    <div class="list-title"><b>PROJECT NAME:</b> <span id="project_name">${v.project_name}</span></div>
                    <div class="list-title"><b>WORK TYPE:</b> <span id="work_type">${v.name}</span></div>
                    <div class="list-table">
                        <table>
                            <thead>
                                <th>Employees</th>
                                <th>Total Working Hours</th>
                                <th>Absent</th>
                                <th>Tardiness</th>
                            </thead>
                            <tbody>`;
                
                $.each(v.employees, (i,v)=>{
                    html += `<tr>
                        <td>${v.firstname} ${v.lastname}</td>
                        <td>${v.total_working_hours} hrs</td>   
                        <td>${v.total_absents} days</td>   
                        <td>${v.total_late} mins.</td>   
                    </tr>`;
                });
    
                html += `</tbody>
                        </table>
                    </div>
                </div>`;
            });
        }
        else {
            html = "<div class='empty-list'>No Records Found</div>";
        }

        $('#timesheet_output').html(html);
        // scion.create.sc_modal('filter_modal').hide('all')
    });
}