var total_pay = 0;
var hold_id = null;
var tbl = null;

var table = {
    fixed: null,
    daily: null,
    monthly: null
};

var released_pay = {
    sequence_no: null,
    schedule_type: 0,
    period_start: null,
    payroll_period: null,
    pay_date: null,
    status: 0,
    employee: []
};


$(function() {
    modal_content = 'pay_month';
    module_url = '/payroll/13-month';
    module_type = 'custom';
    page_title = "Overtime Request";

    scion.centralized_button(true, true, true, true);

    fixedRateTable();
    dailyRateTable();
    monthlyRateTable();
    // custom();
});

function success() {
    switch(actions) {
        case 'save':
            break;
        case 'update':
            break;
    }
    
    if(modal_content === "absent"){
        $('#absent_table').DataTable().draw();
        scion.create.sc_modal('absent_adjustments').hide(modalHideFunction);
    }
    else if(modal_content === "late") {
        $('#late_table').DataTable().draw();
        scion.create.sc_modal('late_adjustments').hide(modalHideFunction);
    }
    $('#fixed_rate_table').DataTable().draw();
    $('#daily_rate_table').DataTable().draw();
    $('#pay_table').DataTable().draw();

}

function error() {
    toastr.error('Record already exist.', 'Failed')
}

function delete_success() {
    $('#pay_table').DataTable().draw();
    $('#absent_table').DataTable().draw();
    $('#late_table').DataTable().draw();
}

function delete_error() {}

function generateData() {
    switch(actions) {
        case 'save':
            if(modal_content === "absent")
            {
                form_data = {
                    _token: _token,
                    employee_id: hold_id,
                    date: $('#date').val(),
                    status: 1,
                    remarks: $('#remarks').val()
                };
            }
            else if(modal_content === "late") {
                form_data = {
                    _token: _token,
                    employee_id: hold_id,
                    late: $('#late').val(),
                    date: $('#late_date').val(),
                    status: 1,
                    remarks: $('#late_remarks').val()
                };
            }
            break;
        case 'update':
            if(modal_content === "absent")
            {
                form_data = {
                    _token: _token,
                    employee_id: hold_id,
                    date: $('#date').val(),
                    status: 1,
                    remarks: $('#remarks').val()
                };
            }
            else if(modal_content === "late") {
                form_data = {
                    _token: _token,
                    employee_id: hold_id,
                    late: $('#late').val(),
                    date: $('#late_date').val(),
                    status: 1,
                    remarks: $('#late_remarks').val()
                };
            }
            break;
    }

    return form_data;
}

function generateDeleteItems(){}

function custom(){


}

function modalShowFunction() {
    scion.centralized_button(true, true, true, true);
}

function modalHideFunction() {
    modal_content = 'pay_month';
    module_url = '/payroll/13-month';
    module_type = 'custom';
    scion.centralized_button(true, true, true, true);
}

function getMonthsDifference(startDate) {
    const start = new Date(startDate);
    const end = new Date(); 

    const yearsDifference = end.getFullYear() - start.getFullYear();
    const monthsDifference = end.getMonth() - start.getMonth();

    let totalMonths = (yearsDifference * 12) + monthsDifference;

    if (end.getDate() < start.getDate()) {
        totalMonths--;
    }

    const isLessThanOneMonth = totalMonths < 1;

    return {
        totalMonths,
        isLessThanOneMonth
    };
}

function view_pay(employee_id, date_ouput, month, type) {
    event.preventDefault();

    var pay_total = 0;
    
    const date = new Date(); 
    const date2 = new Date(date_ouput); 
    const date3 = new Date(`${date.getFullYear()}-01-01`); 


    const formattedDate = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;

    $('.val').text(scion.currency(0));
    $('.abs').text('No Absences');

    
    if(type === "daily_rate") {

        $.get(`/payroll/13-month/get-daily-logs/${$('#year').val()}/${employee_id}`, function(response) {
            $.each(response, function(i,v){
                var val = month * v.total_timelogs;
                var leave = month * v.total_leave_days;

                $('.month-'+i+' .val').text(scion.currency(val + leave));
                $('.month-'+i+' .abs').text(v.total_leave_days !== 0? v.total_leave_days + ' Paid Leaves':'No Absences');
                
                pay_total += val + leave;
            });
            $('.annual-total').text(scion.currency(pay_total));
            $('.pay-total-tbl').text(scion.currency(pay_total / 12));
            scion.create.sc_modal("13th_month_modal", '13 Month Pay').show(modalShowFunction);
        });

    }
    else if(type === "monthly_rate") {
        $.get(`/payroll/13-month/get-monthly-logs/${$('#year').val()}/${employee_id}`, function(response) {
            var countsPerMonth = {};
            var holiday = {};
            var no_of_months = countMonths(date_ouput, formattedDate);
            var start = parseInt(date_ouput.split('-')[1]);

            var output = (no_of_months + start);
            
            $.each(response.absents, function(index, data) {
                var dateObj = new Date(data.date);
                var year = dateObj.getFullYear();
                var month = ((dateObj.getMonth() + 1));
        
                var key = month;
    
                if (!countsPerMonth[key]) {
                    countsPerMonth[key] = 0;
                }
            
                countsPerMonth[key]++;
            });
            
            $.each(response.holiday, function(index, data) {
                var dateObj = new Date(data.date);
                var year = dateObj.getFullYear();
                var month = ((dateObj.getMonth() + 1));
        
                var key = month;
    
                if (!holiday[key]) {
                    holiday[key] = 0;
                }
            
                holiday[key]++;
            });

            for (let index = start + 1; index <= (output >= 12?12:output); index++) {
                var less_absences = (countsPerMonth[index] !== undefined? countsPerMonth[index]:0) * response.compensation.daily_salary;
                var less_holiday = (holiday[index] !== undefined?holiday[index]:0) * response.compensation.daily_salary;
                var less_total = (month - less_absences) - less_holiday;
                
                $('.month-' + index + ' .val').text(scion.currency(less_total));
                $('.month-' + index + ' .abs').text(countsPerMonth[index] !== undefined ? countsPerMonth[index] + (countsPerMonth[index] === 1?" day":" days"):'No Absences');

                if(countsPerMonth[index] !== undefined) {
                    $('.month-' + index + ' .abs').append(holiday[index] !== undefined ? ", " + holiday[index] + (holiday[index] === 1?" day":" days") + " holiday":'');
                }
                else {
                    $('.month-' + index + ' .abs').text(holiday[index] !== undefined ? holiday[index] + (holiday[index] === 1?" day":" days") + " holiday":'No Absences');
                }
                
                pay_total += less_total;
            }

            $.each(holiday, function(i, v) {
                holiday_less = 0;
                if(i !== undefined) {
                    if($('.month-'+i+' .abs').text() === "No Absences") {
                        holiday_less = 0 - (v * response.compensation.daily_salary);
                        $('.month-'+i+' .abs').text(v + (v === 1?" day":" days") + " holiday");
                        $('.month-' + i + ' .val').text(scion.currency(holiday_less));
                    }
                }
                pay_total += holiday_less;
            });
            
            
            $('.annual-total').text(scion.currency(pay_total));
            $('.pay-total-tbl').text(scion.currency(pay_total/12));

            console.log(response);
            
            scion.create.sc_modal("13th_month_modal", '13 Month Pay').show(modalShowFunction);
        });
    }
    else {
        $.get(`/payroll/absent/get/${$('#year').val()}/${employee_id}`, function(response) {
            var countsPerMonth = {};
    
            $.each(response.absents, function(index, data) {
                var dateObj = new Date(data.date);
                var year = dateObj.getFullYear();
                var month = ((dateObj.getMonth() + 1));
        
                var key = month;
    
                if (!countsPerMonth[key]) {
                    countsPerMonth[key] = 0;
                }
            
                countsPerMonth[key]++;
            });
            
            for(var i = (parseInt(formattedDate.split('-')[1]) - 1); i >= parseInt(date_ouput.split('-')[1]) + (date2 > date3?1:0); i--) {
                console.log(i);
                $('.month-'+i+' .val').text(scion.currency(month));
                $('.month-'+i+' .abs').text(type === 'fixed_rate'?'No Absences':(countsPerMonth[i] !== undefined ? countsPerMonth[i] + (countsPerMonth[i] === 1?" day":" days"):'No Absences'));
                
                pay_total += month;
            }
    
            $('.annual-total').text(scion.currency(pay_total));
            $('.pay-total-tbl').text(scion.currency(pay_total/12));
        
            scion.create.sc_modal("13th_month_modal", '13 Month Pay').show(modalShowFunction);
        });
    }
    
}

function showAbsents(id) {
    hold_id = id;
    modal_content = 'absent';
    module_url = '/payroll/absent';
    module_type = 'custom';
    actions = 'save';

    if ($.fn.DataTable.isDataTable('#absent_table')) {
        $('#absent_table').DataTable().destroy();
    }

    scion.create.table(
        'absent_table',  
        '/payroll/13-month/get-absents/' + $('#year').val() + '/' + id, 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                return html;
            }},
            {
                data: null,
                title: "DATE",
                render: function(data, type, row, meta) {
                    return `<span class="expandable" title="${moment(row.date).format('MMM DD, YYYY')}">${moment(row.date).format('MMM DD, YYYY')}</span>`;
                }
            }
        ], '', [], false, false
    );

    scion.create.sc_modal("absent_modal", 'Absents').show(modalShowFunction);
}

function absentAdjustment() {

    scion.create.sc_modal("absent_adjustments", 'Add').show(modalShowFunction);
    scion.centralized_button(true, false, true, true);
}

function showLates(id) {
    hold_id = id;
    modal_content = 'late';
    module_url = '/payroll/late';
    module_type = 'custom';
    actions = 'save';

    if ($.fn.DataTable.isDataTable('#late_table')) {
        $('#late_table').DataTable().destroy();
    }

    scion.create.table(
        'late_table',  
        '/payroll/13-month/get-lates/' + $('#year').val() + '/' + id, 
        [
            { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                var html = "";
                html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                return html;
            }},
            {
                data: null,
                title: "DATE",
                render: function(data, type, row, meta) {
                    return `<span class="expandable" title="${moment(row.date).format('MMM DD, YYYY')}">${moment(row.date).format('MMM DD, YYYY')}</span>`;
                }
            },
            {
                data: 'late',
                title: "LATE(minutes)",
                render: function(data, type, row, meta) {
                    return `<span class="expandable" title="${data}">${data}</span>`;
                }
            }
        ], '', [], false, false
    );

    scion.create.sc_modal("late_modal", 'Lates').show(modalShowFunction);
}

function lateAdjustment() {

    scion.create.sc_modal("late_adjustments", 'Add').show(modalShowFunction);
    scion.centralized_button(true, false, true, true);
}

function releasePay() {
    scion.create.sc_modal("release_modal", 'Release 13th Month Pay').show(modalShowFunction);
}

function yesReleased() {
    $.post('/payroll/13-month/release', released_pay).done(function(response) {
        scion.create.sc_modal('release_modal').hide('all', modalHideFunction);
    });
}

function countMonths(startDate, endDate) {
    startDate = new Date(startDate);
    endDate = new Date(endDate);
    
    const yearsDifference = endDate.getFullYear() - startDate.getFullYear();
    const monthsDifference = endDate.getMonth() - startDate.getMonth();

    let totalMonths = yearsDifference * 12 + monthsDifference;

    if (endDate.getDate() < startDate.getDate()) {
        totalMonths -= 1;
    }
    
    return totalMonths;
}

// Custom Function

function fixedRateTable() {
    
    var self_total = 0;

    table.fixed = scion.create.table(
        'fixed_rate_table',  
        module_url + '/get-fixed/' + $('#year').val(), 
        [
            {
                data: null,
                title: "NAME",
                className: "name",
                render: function(data, type, row, meta) {
                    return `<span class="expandable" title="${row.firstname} ${row.lastname}">${row.firstname} ${row.lastname}</span>`;
                }
            },
            {
                data: "employment_status",
                title: "STATUS",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: null,
                title: "MONTHLY RATE",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + (row.compensations !== null?row.compensations.monthly_salary:0) + '">' + scion.currency(row.compensations !== null?row.compensations.monthly_salary:0) + '</span>';
                }
            },
            {
                data: null,
                title: "DAILY RATE",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + (row.compensations !== null?row.compensations.daily_salary:0) + '">' + scion.currency(row.compensations !== null?row.compensations.daily_salary:0) + '</span>';
                }
            },
            {
                data: null,
                title: "MONTHS",
                render: function(data, type, row, meta) {
                    const result = row.employments_tab !== null?getMonthsDifference(new Date(row.employments_tab.employment_date) < new Date(`${$('#year').val()}-01-01`)?`${$('#year').val()}-01-01`:row.employments_tab.employment_date):null;
                    var date = result !== null?(result.isLessThanOneMonth?0:result.totalMonths):0;

                    return date;
                }
            },
            {
                data: null,
                title: "13 MONTH PAY",
                className: "based-total",
                render: function(data, type, row, meta) {
                    const result = row.employments_tab !== null?getMonthsDifference(new Date(row.employments_tab.employment_date) < new Date(`${$('#year').val()}-01-01`)?`${$('#year').val()}-01-01`:row.employments_tab.employment_date):null;
                    const date_output = row.employments_tab !== null?(new Date(row.employments_tab.employment_date) < new Date(`${$('#year').val()}-01-01`)?`${$('#year').val()}-01-01`:row.employments_tab.employment_date):null;

                    var date = result !== null?(result.isLessThanOneMonth?0:result.totalMonths):0;

                    var salary = row.compensations !== null?row.compensations.monthly_salary:0;

                    var pay = (parseFloat(salary) * date)/12;

                    return `<a href="#" onclick="view_pay(${row.id}, '${date_output}', ${row.compensations !== null?row.compensations.monthly_salary:0}, 'fixed_rate')">${scion.currency(parseFloat(pay))}</a>`;

                }
            },
            {
                data: null,
                title: "ABSENT",
                className: "adjustment",
                render: function(data, type, row, meta) {
                    const absent = row.absents_adjustments.length;
                    const daily = parseFloat(row.compensations !== null?row.compensations.daily_salary:0);
                    
                    const absent_rate = daily*absent;

                    return `<a href="#" onclick="showAbsents(${row.id})">${scion.currency(absent_rate)}</a>`;
                }
            },
            {
                data: null,
                title: "LATES",
                className: "adjustment",
                render: function(data, type, row, meta) {
                    const late = parseFloat(row.total_lates_adjustments);
                    const hourly = parseFloat(row.compensations !== null?row.compensations.hourly_salary:0);
                    
                    const late_rate = (hourly*(late/60));

                    return `<a href="#" onclick="showLates(${row.id})">${scion.currency(late_rate)}</a>`;
                }
            },
            {
                data: null,
                title: "13TH MONTH PAY",
                className: "grand-total",
                render: function(data, type, row, meta) {
                    
                    const result = row.employments_tab !== null?getMonthsDifference(new Date(row.employments_tab.employment_date) < new Date(`${$('#year').val()}-01-01`)?`${$('#year').val()}-01-01`:row.employments_tab.employment_date):null;
                    const date_output = row.employments_tab !== null?(new Date(row.employments_tab.employment_date) < new Date(`${$('#year').val()}-01-01`)?`${$('#year').val()}-01-01`:row.employments_tab.employment_date):null;

                    var date = result !== null?(result.isLessThanOneMonth?0:result.totalMonths):0;

                    var salary = row.compensations !== null?row.compensations.monthly_salary:0;

                    var pay = (parseFloat(salary) * date)/12;
                    
                    const absent = row.absents_adjustments.length;
                    const daily = parseFloat(row.compensations !== null?row.compensations.daily_salary:0);
                    
                    const absent_rate = daily*absent;
                    
                    const late = parseFloat(row.total_lates_adjustments);
                    const hourly = parseFloat(row.compensations !== null?row.compensations.hourly_salary:0);
                    
                    const late_rate = (hourly*(late/60));

                    var total = (pay - absent_rate) - late_rate;

                    return `<a href="#" data-val="${total}">${scion.currency(total)}</a>`;
                }
            },
        ], '', [], false, false
    );

    
    table.fixed.on('draw', function() {
        $.each($('#fixed_rate_table td.grand-total a'), (i,v) => {
            self_total += parseFloat($(v).attr('data-val'));
        });

        $('#fixed_rate_table #total_pay').text(scion.currency(self_total));
        $('#fixed_rate_table .data-total').text(scion.currency(self_total));
    });
}

function dailyRateTable() {
    
    var self_total = 0;

    table.daily = scion.create.table(
        'daily_rate_table',  
        module_url + '/get-daily/' + $('#year').val(), 
        [
            {
                data: null,
                title: "NAME",
                className: "name",
                render: function(data, type, row, meta) {
                    return `<span class="expandable" title="${row.firstname} ${row.lastname}">${row.firstname} ${row.lastname}</span>`;
                }
            },
            {
                data: "employment_status",
                title: "STATUS",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: null,
                title: "MONTHLY RATE",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + (row.compensations !== null?row.compensations.monthly_salary:0) + '">' + scion.currency(row.compensations !== null?row.compensations.monthly_salary:0) + '</span>';
                }
            },
            {
                data: null,
                title: "DAILY RATE",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + (row.compensations !== null?row.compensations.daily_salary:0) + '">' + scion.currency(row.compensations !== null?row.compensations.daily_salary:0) + '</span>';
                }
            },
            // {
            //     data: null,
            //     title: "MONTHS",
            //     render: function(data, type, row, meta) {
            //         const result = row.employments_tab !== null?getMonthsDifference(new Date(row.employments_tab.employment_date) < new Date(`${$('#year').val()}-01-01`)?`${$('#year').val()}-01-01`:row.employments_tab.employment_date):null;
            //         var date = result !== null?(result.isLessThanOneMonth?0:result.totalMonths):0;

            //         return date;
            //     }
            // },
            {
                data: null,
                title: "NO. OF DAYS WORKED",
                render: function(data, type, row, meta) {
                    // const result = row.employments_tab !== null?getMonthsDifference(new Date(row.employments_tab.employment_date) < new Date(`${$('#year').val()}-01-01`)?`${$('#year').val()}-01-01`:row.employments_tab.employment_date):null;
                    // var date = result !== null?(result.isLessThanOneMonth?0:result.totalMonths):0;

                    return row.timelogs.length;
                }
            },
            {
                data: null,
                title: "TOTAL BASIC PAY",
                className: "based-total",
                render: function(data, type, row, meta) {
                    const result = row.employments_tab !== null?getMonthsDifference(new Date(row.employments_tab.employment_date) < new Date(`${$('#year').val()}-01-01`)?`${$('#year').val()}-01-01`:row.employments_tab.employment_date):null;
                    const date_output = row.employments_tab !== null?(new Date(row.employments_tab.employment_date) < new Date(`${$('#year').val()}-01-01`)?`${$('#year').val()}-01-01`:row.employments_tab.employment_date):null;

                    var date = result !== null?(result.isLessThanOneMonth?0:result.totalMonths):0;

                    var salary = row.compensations !== null?row.compensations.daily_salary:0;

                    var pay = (parseFloat(salary) * (row.timelogs.length + row.total_leaves))/12;

                    return `<a href="#" onclick="view_pay(${row.id}, '${date_output}', ${row.compensations !== null?row.compensations.daily_salary:0}, 'daily_rate')">${scion.currency(parseFloat(pay))}</a>`;

                }
            },
            {
                data: null,
                title: "NO. OF PAID LEAVES",
                className: "adjustment",
                render: function(data, type, row, meta) {
                    return parseFloat(row.total_leaves).toFixed(2);
                }
            },
            {
                data: null,
                title: "TOTAL LEAVE AMOUNT",
                className: "adjustment",
                render: function(data, type, row, meta) {
                    
                    return scion.currency(parseFloat(row.compensations !== null?row.compensations.daily_salary:0 )* parseFloat(row.total_leaves));
                }
            },
            {
                data: null,
                title: "ABSENT",
                className: "adjustment",
                render: function(data, type, row, meta) {
                    const absent = row.absents_adjustments.length;
                    const daily = parseFloat(row.compensations !== null?row.compensations.daily_salary:0);
                    
                    const absent_rate = daily*absent;

                    return `<a href="#" onclick="showAbsents(${row.id})">${scion.currency(absent_rate)}</a>`;
                }
            },
            {
                data: null,
                title: "LATES",
                className: "adjustment",
                render: function(data, type, row, meta) {
                    const late = parseFloat(row.total_lates_adjustments);
                    const hourly = parseFloat(row.compensations !== null?row.compensations.hourly_salary:0);
                    
                    const late_rate = (hourly*(late/60));

                    return `<a href="#" onclick="showLates(${row.id})">${scion.currency(late_rate)}</a>`;
                }
            },
            {
                data: null,
                title: "13TH MONTH PAY",
                className: "grand-total",
                render: function(data, type, row, meta) {
                    
                    const result = row.employments_tab !== null?getMonthsDifference(new Date(row.employments_tab.employment_date) < new Date(`${$('#year').val()}-01-01`)?`${$('#year').val()}-01-01`:row.employments_tab.employment_date):null;
                    const date_output = row.employments_tab !== null?(new Date(row.employments_tab.employment_date) < new Date(`${$('#year').val()}-01-01`)?`${$('#year').val()}-01-01`:row.employments_tab.employment_date):null;

                    var date = result !== null?(result.isLessThanOneMonth?0:result.totalMonths):0;

                    var salary = row.compensations !== null?row.compensations.daily_salary:0;

                    var pay = (parseFloat(salary) * (row.timelogs.length + row.total_leaves))/12;
                    
                    const absent = row.absents_adjustments.length;
                    const daily = parseFloat(row.compensations !== null?row.compensations.daily_salary:0);
                    
                    const absent_rate = daily*absent;

                    const late = parseFloat(row.total_lates_adjustments);
                    const hourly = parseFloat(row.compensations !== null?row.compensations.hourly_salary:0);
                    
                    const late_rate = (hourly*(late/60));

                    var total = (pay - absent_rate) - late_rate;

                    return `<a href="#" data-val="${total}">${scion.currency(total)}</a>`;
                }
            },
        ], '', [], false, false
    );

    
    table.daily.on('draw', function() {
        $.each($('#daily_rate_table td.grand-total a'), (i,v) => {
            self_total += parseFloat($(v).attr('data-val'));
        });

        $('#daily_rate_table #total_pay').text(scion.currency(self_total));
        $('#daily_rate_table .data-total').text(scion.currency(self_total));
    });
}

function monthlyRateTable() {

    var self_total = 0;

    table.monthly = scion.create.table(
        'pay_table',  
        module_url + '/get-monthly/' + $('#year').val(), 
        [
            {
                data: null,
                title: "NAME",
                className: "name",
                render: function(data, type, row, meta) {
                    return `<span class="expandable" title="${row.firstname} ${row.lastname}">${row.firstname} ${row.lastname}</span>`;
                }
            },
            {
                data: "employment_status",
                title: "STATUS",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: null,
                title: "MONTHLY RATE",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + (row.compensations !== null?row.compensations.monthly_salary:0) + '">' + scion.currency(row.compensations !== null?row.compensations.monthly_salary:0) + '</span>';
                }
            },
            {
                data: null,
                title: "DAILY RATE",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + (row.compensations !== null?row.compensations.daily_salary:0) + '">' + scion.currency(row.compensations !== null?row.compensations.daily_salary:0) + '</span>';
                }
            },
            {
                data: null,
                title: "MONTHS",
                render: function(data, type, row, meta) {
                    const result = row.employments_tab !== null?getMonthsDifference(new Date(row.employments_tab.employment_date) < new Date(`${$('#year').val()}-01-01`)?`${$('#year').val()}-01-01`:row.employments_tab.employment_date):null;
                    var date = result !== null?(result.isLessThanOneMonth?0:result.totalMonths):0;

                    return date;
                }
            },
            {
                data: null,
                title: "13 MONTH PAY",
                className: "based-total",
                render: function(data, type, row, meta) {
                    const result = row.employments_tab !== null?getMonthsDifference(new Date(row.employments_tab.employment_date) < new Date(`${$('#year').val()}-01-01`)?`${$('#year').val()}-01-01`:row.employments_tab.employment_date):null;
                    const date_output = row.employments_tab !== null?(new Date(row.employments_tab.employment_date) < new Date(`${$('#year').val()}-01-01`)?`${$('#year').val()}-01-01`:row.employments_tab.employment_date):null;

                    var date = result !== null?(result.isLessThanOneMonth?0:result.totalMonths):0;

                    var salary = row.compensations !== null?row.compensations.monthly_salary:0;
                    var salary_2 = row.compensations !== null?row.compensations.daily_salary:0;

                    const absent = row.absents_timelogs.length;
                    const absent_rate = salary_2*absent;
                    
                    const holiday = row.total_holidays;
                    const holiday_rate = salary_2*holiday;

                    var pay = (((parseFloat(salary) * date) - absent_rate) - holiday_rate)/12;
                    
                    return `<a href="#" onclick="view_pay(${row.id}, '${date_output}', ${row.compensations !== null?row.compensations.monthly_salary:0}, 'monthly_rate')">${scion.currency(parseFloat(pay))}</a>`;

                }
            },
            {
                data: null,
                title: "ABSENT",
                className: "adjustment",
                render: function(data, type, row, meta) {
                    const absent = row.absents_adjustments.length;
                    const daily = parseFloat(row.compensations !== null?row.compensations.daily_salary:0);
                    
                    const absent_rate = daily*absent;

                    return `<a href="#" onclick="showAbsents(${row.id})">${scion.currency(absent_rate)}</a>`;
                }
            },
            {
                data: null,
                title: "LATES",
                className: "adjustment",
                render: function(data, type, row, meta) {
                    const late = parseFloat(row.total_lates_adjustments);
                    const hourly = parseFloat(row.compensations !== null?row.compensations.hourly_salary:0);
                    
                    const late_rate = (hourly*(late/60));

                    return `<a href="#" onclick="showLates(${row.id})">${scion.currency(late_rate)}</a>`;
                }
            },
            {
                data: null,
                title: "13TH MONTH PAY",
                className: "grand-total",
                render: function(data, type, row, meta) {
                    
                    const result = row.employments_tab !== null?getMonthsDifference(new Date(row.employments_tab.employment_date) < new Date(`${$('#year').val()}-01-01`)?`${$('#year').val()}-01-01`:row.employments_tab.employment_date):null;
                    const date_output = row.employments_tab !== null?(new Date(row.employments_tab.employment_date) < new Date(`${$('#year').val()}-01-01`)?`${$('#year').val()}-01-01`:row.employments_tab.employment_date):null;

                    var date = result !== null?(result.isLessThanOneMonth?0:result.totalMonths):0;

                    var salary = row.compensations !== null?row.compensations.monthly_salary:0;
                    var salary_2 = row.compensations !== null?row.compensations.daily_salary:0;

                    const absent = row.absents.length;
                    const absent_rate = salary_2*absent;
                    
                    const holiday = row.total_holidays;
                    const holiday_rate = salary_2*holiday;
                    
                    const late = parseFloat(row.total_lates_adjustments);
                    const hourly = parseFloat(row.compensations !== null?row.compensations.hourly_salary:0);
                    
                    const late_rate = (hourly*(late/60));


                    var pay = ((((parseFloat(salary) * date) - absent_rate) - late_rate) - holiday_rate)/12;

                    return `<a href="#" data-val="${pay}">${scion.currency(pay)}</a>`;
                }
            },
        ], '', [], false, false
    );
    

    table.monthly.on('draw', function() {
        $.each($('#pay_table td.grand-total a'), (i,v) => {
            self_total += parseFloat($(v).attr('data-val'));
        });
        $('#pay_table #total_pay').text(scion.currency(self_total));
        $('#pay_table .data-total').text(scion.currency(self_total));
    });
}

function allView() {
    $('.filter-view').css('display', 'block');

    $('.filter-action button').removeClass('selected');
    $('.filter-action button#all_btn').addClass('selected');
}

function filterView(selected) {
    $('.filter-view').css('display', 'none');
    $('#'+selected+'_container').css('display', 'block');

    $('.filter-action button').removeClass('selected');
    $('.filter-action button#'+selected+'_btn').addClass('selected');
}

function selectRate(x) {
    var id = x.id;
    
    $(`#${id} input`)[0].checked = $(`#${id} input`)[0].checked === true?false:true;
}

async function submitPay() {
    var pay_list = {
        _token: _token,
        fixed: null,
        daily: null,
        monthly: null
    };

    var list = ['fixed', 'daily', 'monthly'];
    var year = $('#year').val();

    const promises = [];

    $.each($('#id_rate_list li input'), (i, v) => {
        if ($(v)[0].checked === true) {
            pay_list[list[i]] = {
                sequence_no: `13-${list[i].toUpperCase()}-${year}`,
                schedule_type: 0,
                period_start: `${year}-01-01`,
                payroll_period: `${year}-12-31`,
                pay_date: `${year}-12-31`,
                status: 0,
                employee: []
            };

            const list_select = list[i];

            const promise = new Promise((resolve, reject) => {
                $.get(module_url + '/get-' + list[i] + '/' + $('#year').val(), function(response) {
                    $.each(response.data, (i, row) => {
                        let total = 0;

                        if (list_select === "fixed") {
                            const result = row.employments_tab !== null ? getMonthsDifference(new Date(row.employments_tab.employment_date) < new Date(`${year}-01-01`) ? `${year}-01-01` : row.employments_tab.employment_date) : null;
                            const date = result !== null ? (result.isLessThanOneMonth ? 0 : result.totalMonths) : 0;
                            const salary = row.compensations !== null ? row.compensations.monthly_salary : 0;
                            const pay = (parseFloat(salary) * date) / 12;
                            const absent = row.absents_adjustments.length;
                            const daily = parseFloat(row.compensations !== null ? row.compensations.daily_salary : 0);
                            const absent_rate = daily * absent;
                            total = pay - absent_rate;

                        } else if (list_select === "daily") {
                            const result = row.employments_tab !== null ? getMonthsDifference(new Date(row.employments_tab.employment_date) < new Date(`${year}-01-01`) ? `${year}-01-01` : row.employments_tab.employment_date) : null;
                            const date = result !== null ? (result.isLessThanOneMonth ? 0 : result.totalMonths) : 0;
                            const salary = row.compensations !== null ? row.compensations.daily_salary : 0;
                            const pay = (parseFloat(salary) * (row.timelogs.length + row.total_leaves)) / 12;
                            const absent = row.absents_adjustments.length;
                            const daily = parseFloat(row.compensations !== null ? row.compensations.daily_salary : 0);
                            const absent_rate = daily * absent;
                            total = pay - absent_rate;

                        } else if (list_select === "monthly") {
                            const result = row.employments_tab !== null ? getMonthsDifference(new Date(row.employments_tab.employment_date) < new Date(`${year}-01-01`) ? `${year}-01-01` : row.employments_tab.employment_date) : null;
                            const date = result !== null ? (result.isLessThanOneMonth ? 0 : result.totalMonths) : 0;
                            const salary = row.compensations !== null ? row.compensations.monthly_salary : 0;
                            const salary_2 = row.compensations !== null ? row.compensations.daily_salary : 0;
                            const absent = row.absents.length;
                            const absent_rate = salary_2 * absent;
                            const holiday = row.total_holidays;
                            const holiday_rate = salary_2 * holiday;
                            const pay = (((parseFloat(salary) * date) - absent_rate) - holiday_rate) / 12;
                            total = pay;
                        }

                        pay_list[list_select].employee.push({
                            employee_id: row.id,
                            pay_total: total
                        });
                    });

                    resolve();
                }).fail(reject);
            });

            promises.push(promise);
        }
    });

    // Wait for all promises to complete
    try {
        await Promise.all(promises);
        console.log(pay_list);

        $.post('/payroll/13-month/release', pay_list).done(function(response) {
            scion.create.sc_modal('release_modal').hide('all', modalHideFunction);
        });
    } catch (error) {
        console.error('Error processing payroll:', error);
    }
}
