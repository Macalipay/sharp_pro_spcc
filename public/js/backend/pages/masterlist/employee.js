$(function() {
    modal_content = 'employee';
    module_url = '/masterlist/employee';
    module_type = 'custom';
    page_title = "Employee Masterlist";

    scion.centralized_button(true, true, true, true);
    const exportButtons = canDownload ? ['csv', 'pdf'] : [];

    scion.create.table(
        'employee_table',  
        module_url + '/get', 
        [
            // { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
            //     var html = "";
            //     html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
            //     html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/payroll/benefits/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
            //     return html;
            // }},
            { data: "employee_no", title:"EMPLOYEE NO." },
            { data: "profile_img", title: "", render: function(data, type, row, meta) {
                return "<img height='50px' src='/images/payroll/employee-information/" + row.profile_img + "' />";
            }},
            {
                data: "firstname",
                title: "NAME",
                render: function(data, type, row, meta) {
                    var fullName = row.firstname + " ";
                    if (row.middlename) fullName += row.middlename + " ";
                    fullName += row.lastname;
                    if (row.suffix) fullName += " " + row.suffix;
                    return '<span class="expandable" title="' + fullName.trim() + '">' + fullName.trim() + '</span>';
                }
            },
            {
                data: "email",
                title: "EMAIL",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "birthdate",
                title: "BIRTHDATE",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + moment(row.birthdate).format('MMM DD, YYYY') + '">' + moment(row.birthdate).format('MMM DD, YYYY') + '</span>';
                }
            },
            {
                data: "gender",
                title: "GENDER",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "citizenship",
                title: "CITIZENSHIP",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + data + '">' + data + '</span>';
                }
            },
            {
                data: "created_at",
                title: "CREATED DATE",
                render: function(data, type, row, meta) {
                    return '<span class="expandable" title="' + moment(row.created_at).format('MMM DD, YYYY hh:mm A') + '">' + moment(row.created_at).format('MMM DD, YYYY hh:mm A') + '</span>';
                }
            },
            {
                data: "employment_date",
                title: "EMPLOYMENT DATE",
                render: function(data, type, row, meta) {
                    if (row.employments_tab && row.employments_tab.employment_date) {
                        return '<span class="expandable" title="' + moment(row.employments_tab.employment_date).format('MMM DD, YYYY hh:mm A') + '">' + moment(row.employments_tab.employment_date).format('MMM DD, YYYY hh:mm A') + '</span>';
                    } else {
                        return '<span class="expandable" title="N/A">N/A</span>';
                    }
                }
            },
            {
                data: "department",
                title: "DEPARTMENT",
                render: function(data, type, row, meta) {
                    if (row.employments_tab && row.employments_tab.departments) {
                        return '<span class="expandable" title="' + (row.employments_tab.departments.description || 'N/A') + '">' + (row.employments_tab.departments.description || 'N/A') + '</span>';
                    } else {
                        return '<span class="expandable" title="N/A">N/A</span>';
                    }
                }
            },
            {
                data: "classes",
                title: "CLASS",
                render: function(data, type, row, meta) {
                    if (row.employments_tab && row.employments_tab.classes) {
                        return '<span class="expandable" title="' + (row.employments_tab.classes.description || 'N/A') + '">' + (row.employments_tab.classes.description || 'N/A') + '</span>';
                    } else {
                        return '<span class="expandable" title="N/A">N/A</span>';
                    }
                }
            },
            {
                data: "positions",
                title: "POSITION",
                render: function(data, type, row, meta) {
                    if (row.employments_tab && row.employments_tab.positions) {
                        return '<span class="expandable" title="' + (row.employments_tab.positions.description || 'N/A') + '">' + (row.employments_tab.positions.description || 'N/A') + '</span>';
                    } else {
                        return '<span class="expandable" title="N/A">N/A</span>';
                    }
                }
            }


        ], 'Bfrtip', exportButtons, true, false
    );

});
