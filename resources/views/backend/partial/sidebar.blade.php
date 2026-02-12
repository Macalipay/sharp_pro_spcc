@php
$user = auth()->user();
@endphp

<nav id="sidebar" class="sidebar">
    <div class="sidebar-content">

        <a class="sidebar-toggle mr-2">
            {{-- <i class="fas fa-bars"></i> --}}
            <img src="/images/logo.png" class="logo1" alt="company-logo" width="100%"/>
            <img src="/images/logo-2.png" class="logo2" alt="company-logo-2" width="100%"/>
        </a>

        {{-- <div class="company-logo">
            <div class="company-name">
                Company Name 
            </div>
        </div> --}}
        <ul class="sidebar-nav" style="overflow-x: hidden;">
            <li class="sidebar-header">
                MAIN
            </li>
            <li class="sidebar-item">
                <a href="#dashboard" data-toggle="collapse" class="sidebar-link collapsed">
                    <span class="item">
                        <i class="align-middle mr-2 fas fa-fw fa-tachometer-alt"></i> <span class="align-middle">DASHBOARD</span>
                    </span>
                </a>
                <ul id="dashboard" class="sidebar-dropdown list-unstyled collapse" data-parent="#sidebar">
                    <li class="list-title">DASHBOARD</li>
                    <li class="sidebar-item"><a class="sidebar-link" href="/dashboard">MAIN</a></li>
                </ul>

                @if($user->can('view_Employee Profile') || $user->can('view_Employees Masterlist'))
                    <a href="#employee" data-toggle="collapse" class="sidebar-link collapsed">
                        <span class="item">
                            <i class="align-middle mr-2 fas fa-fw fa-user"></i> <span class="align-middle">EMPLOYEE</span>
                        </span>
                    </a>
                    <ul id="employee" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
                        <li class="list-title">EMPLOYEE</li>
                        @if($user->can('view_Employee Profile') || $user->can('edit_Employee Profile') || $user->can('delete_Employee Profile'))
                            <li class="sidebar-item"><a class="sidebar-link" href="/payroll/employee-information">EMPLOYEE PROFILE</a></li>
                        @endif
                        @if($user->can('view_Employee Profile') || $user->can('edit_Employee Profile') || $user->can('delete_Employee Profile'))
                        <li class="sidebar-item"><a class="sidebar-link" href="/payroll/employee-profile">EMPLOYEE MASTERFILE</a></li>
                       @endif
                       @if($user->can('view_Employee Profile') || $user->can('edit_Employee Profile') || $user->can('delete_Employee Profile'))
                        <li class="sidebar-item"><a class="sidebar-link" href="/payroll/employee_adjustment">EMPLOYEE ADJUSTMENT</a></li>
                       @endif
                    </ul>
                @endif
            </li>

            <li class="sidebar-header">
                TRANSACTION
            </li>
            @if($user->can('view_Time Logs') || $user->can('view_Daily Time Sheet') || $user->can('view_Time Sheet Summary'))
            <li class="sidebar-item">
                <a href="#timesheet_li" data-toggle="collapse" class="sidebar-link collapsed">
                    <span class="item">
                        <i class="align-middle mr-2 fas fa-fw fa-clock"></i> <span class="align-middle">TIMESHEET</span>
                    </span>
                </a>
                <ul id="timesheet_li" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
                    <li class="list-title">TIMESHEET</li>

                    @if($user->can('view_Time Logs') || $user->can('print_Time Logs') || $user->can('release_Time Logs'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/time_logs">TIME LOGS</a></li>
                    @endif

                    @if($user->can('view_Daily Time Sheet') || $user->can('print_Daily Time Sheet'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/timesheet/daily">DAILY TIMESHEET</a></li>
                    @endif

                    @if($user->can('view_Time Sheet Summary') || $user->can('delete_Time Sheet Summary'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/timesheet/summary">TIMESHEET SUMMARY</a></li>
                    @endif
                </ul>
            </li>
            @endif

            @if($user->can('view_Payroll Summary') || $user->can('view_Leave Request') || $user->can('view_Overtime Request') || $user->can('view_Scheduling') || $user->can('view_Quit Claims') || $user->can('view_13th Month'))
            <li class="sidebar-item">
                <a href="#payroll_system" data-toggle="collapse" class="sidebar-link collapsed">
                    <span class="item">
                        <i class="align-middle mr-2 fas fa-fw fa-receipt"></i> <span class="align-middle">PAYROLL</span>
                    </span>
                </a>
                <ul id="payroll_system" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
                    <li class="list-title">PAYROLL</li>
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/payrun">PAYROLL DETAILS</a></li>
                    @if($user->can('view_Payroll Summary') || $user->can('edit_Payroll Summary') || $user->can('delete_Payroll Summary'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/summary">PAYROLL SUMMARY</a></li>
                    @endif

                    @if($user->can('view_Leave Request') || $user->can('add_Leave Request') || $user->can('delete_Leave Request'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/leave_request">LEAVE REQUEST</a></li>
                    @endif

                    @if($user->can('view_Overtime Request') || $user->can('edit_Payroll Summary') || $user->can('delete_Payroll Summary') || $user->can('delete_Overtime Request'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/overtime_request">OVERTIME REQUEST</a></li>
                    @endif

                    @if($user->can('view_Scheduling') || $user->can('add_Scheduling') || $user->can('edit_Scheduling') || $user->can('delete_Scheduling') || $user->can('print_Scheduling'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/scheduling">SCHEDULING</a></li>
                    @endif

                    @if($user->can('view_Quit Claims') || $user->can('add_Quit Claims') || $user->can('edit_Quit Claims') || $user->can('delete_Quit Claims'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/quit-claims">QUIT CLAIMS</a></li>
                    @endif

                    @if($user->can('view_13th Month')  || $user->can('edit_13th Month') || $user->can('delete_13th Month'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/13-month">13th MONTH PAY</a></li>
                    @endif
                </ul>
            </li>
            @endif

            @if($user->can('view_Journal Entries') || $user->can('view_Change of Accounts') || $user->can('view_Account Type'))
            <li class="sidebar-item">
                <a href="#accounting" data-toggle="collapse" class="sidebar-link collapsed">
                    <span class="item">
                        <i class="align-middle mr-2 fas fa-fw fa-money-bill"></i> <span class="align-middle">ACCOUNTING</span>
                    </span>
                </a>
                <ul id="accounting" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
                    <li class="list-title">ACCOUNTING</li>
                    @if($user->can('view_Journal Entries') || $user->can('edit_Journal Entries') || $user->can('delete_Journal Entries'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/accounting/journal_entries">JOURNAL ENTRIES</a></li>
                    @endif

                    @if($user->can('view_Change of Accounts') || $user->can('edit_Change of Accounts') || $user->can('delete_Change of Accounts'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/accounting/chart_of_accounts">CHART OF ACCOUNTS</a></li>
                    @endif

                    @if($user->can('view_Account Type') || $user->can('edit_Account Type') || $user->can('delete_Account Type'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/accounting/account_types">ACCOUNT TYPE</a></li>
                    @endif
                </ul>
            </li>
            @endif

            @if($user->can('view_Purchase Order') || $user->can('view_Credit Note') || $user->can('view_Site') || $user->can('view_Supplier') || $user->can('view_Material Category') || $user->can('view_Materials'))

            <li class="sidebar-item">
                <a href="#purchasing" data-toggle="collapse" class="sidebar-link collapsed">
                    <span class="item">
                        <i class="align-middle mr-2 fas fa-fw fa-shopping-basket"></i> <span class="align-middle">PURCHASING</span>
                    </span>
                </a>
                <ul id="purchasing" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
                    <li class="list-title">PURCHASING</li>
                    @if($user->can('view_Purchase Order') || $user->can('add_Purchase Order') || $user->can('edit_Purchase Order') || $user->can('delete_Purchase Order') || $user->can('print_Purchase Order'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/purchasing/purchase_orders">PURCHASE ORDER</a></li>
                    @endif

                    @if($user->can('view_Purchase Order') || $user->can('add_Purchase Order') || $user->can('edit_Purchase Order') || $user->can('delete_Purchase Order') || $user->can('print_Purchase Order'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/purchasing/delivery_receipt">DELIVERY RECEIPT</a></li>
                    @endif
                    
                    @if($user->can('view_Credit Note') || $user->can('edit_Credit Note') || $user->can('delete_Credit Note'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/purchasing/credit_note">CREDIT NOTE</a></li>
                    @endif

                    @if($user->can('view_Site') || $user->can('add_Site') || $user->can('edit_Site') || $user->can('delete_Site'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/purchasing/sites">SITE</a></li>
                    @endif
                    
                    @if($user->can('view_Supplier') || $user->can('add_Supplier') || $user->can('edit_Supplier') || $user->can('delete_Supplier'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/purchasing/supplier">SUPPLIER</a></li>
                    @endif

                    @if($user->can('view_Material Category') || $user->can('add_Material Category') || $user->can('edit_Material Category') || $user->can('delete_Material Category'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/purchasing/material_category">MATERIAL CATEGORY</a></li>
                    @endif

                    @if($user->can('view_Materials') || $user->can('add_Materials') || $user->can('edit_Materials') || $user->can('delete_Materials'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/purchasing/materials">MATERIALS</a></li>
                    @endif

                </ul>
            </li>
            @endif

            @if($user->can('view_Inventory') || $user->can('view_Inventory Request') || $user->can('view_Owner Supplied Material') || $user->can('view_Inventory History') || $user->can('view_Damages')
            || $user->can('view_Transfer History'))
            <li class="sidebar-item">
                <a href="#inventory" data-toggle="collapse" class="sidebar-link collapsed">
                    <span class="item">
                        <i class="align-middle mr-2 fas fa-fw fa-dolly-flatbed"></i> <span class="align-middle">INVENTORY</span>
                    </span>
                </a>
                <ul id="inventory" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
                    <li class="list-title">INVENTORY</li>

                    {{-- @if($user->can('view_Inventory') || $user->can('add_Inventory') || $user->can('edit_Inventory') || $user->can('delete_Inventory') || $user->can('print_Inventory'))
                        <li class="sidebar-item"><a class="sidebar-link" href="/inventory/inventory_transaction">TRANSACTION</a></li>
                    @endif --}}

                    @if($user->can('view_Inventory') || $user->can('add_Inventory') || $user->can('edit_Inventory') || $user->can('delete_Inventory') || $user->can('print_Inventory'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/inventory/inventory">INVENTORY</a></li>
                    @endif

                    @if($user->can('view_Inventory Request') || $user->can('add_Inventory Request') || $user->can('edit_Inventory Request') || $user->can('delete_Inventory Request') || $user->can('print_Inventory Request'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/inventory/inventory_request">INVENTORY REQUEST</a></li>
                    @endif


                    @if($user->can('view_Owner Supplied Material') || $user->can('add_Owner Supplied Material') || $user->can('edit_Owner Supplied Material') || $user->can('delete_Owner Supplied Material'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/inventory/owner_supplied_material">OWNER SUPPLIED MATERIAL</a></li>
                    @endif

                    @if($user->can('view_Inventory History') || $user->can('add_Inventory History') || $user->can('edit_Inventory History') || $user->can('delete_Inventory History'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/inventory/history">INVENTORY HISTORY</a></li>
                    @endif

                    @if($user->can('view_Damages') || $user->can('add_Damages') || $user->can('edit_Damages') || $user->can('delete_Damages') )
                    <li class="sidebar-item"><a class="sidebar-link" href="/inventory/damage">DAMAGES</a></li>
                    @endif

                    @if($user->can('view_Transfer History') || $user->can('add_Transfer History') || $user->can('edit_Transfer History') || $user->can('delete_Transfer History'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/inventory/transfer_history">TRANSFER HISTORY</a></li>
                    @endif
                </ul>
            </li>
            @endif

            <li class="sidebar-header">
                SETUP
            </li>
            @if($user->can('view_Company Profile') || $user->can('view_Classes') || $user->can('view_Departments') || $user->can('view_Positions') || $user->can('view_Damages')
            || $user->can('view_Transfer History'))
            <li class="sidebar-item">
                <a href="#organizational_setup" data-toggle="collapse" class="sidebar-link collapsed">
                    <span class="item">
                        <i class="align-middle mr-2 fas fa-fw fa-list-alt"></i> <span class="align-middle">ORGANIZATION</span>
                    </span>
                </a>
                <ul id="organizational_setup" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
                    <li class="list-title">ORGANIZATIONAL SETUP</li>

                    @if($user->can('view_Company Profile') || $user->can('add_Company Profile') || $user->can('edit_Company Profile') || $user->can('delete_Company Profile') || $user->can('print_Company Profile'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/company-profile">COMPANY PROFILE</a></li>
                    @endif

                    @if($user->can('view_Classes') || $user->can('add_Classes') || $user->can('edit_Classes') || $user->can('delete_Classes'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/classes">CLASSES</a></li>
                     @endif

                    @if($user->can('view_Departments') || $user->can('add_Departments') || $user->can('edit_Departments') || $user->can('delete_Departments'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/department">DEPARTMENTS</a></li>
                    @endif

                    @if($user->can('view_Positions') || $user->can('add_Positions') || $user->can('edit_Positions') || $user->can('delete_Positions'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/position">POSITIONS</a></li>
                    @endif

                    @if($user->can('view_Projects') || $user->can('add_Projects') || $user->can('edit_Projects') || $user->can('delete_Projects'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/purchasing/project">PROJECTS</a></li>
                    @endif

                </ul>
            </li>
            @endif
            @if($user->can('view_Payroll Calendar') || $user->can('view_Allowance') || $user->can('view_Earnings') || $user->can('view_Clearance Type') || $user->can('view_Work Assignments')
            || $user->can('view_Withholding Tax') || $user->can('view_Reimbursement') || $user->can('view_Benefits') || $user->can('view_Leave Types') || $user->can('view_Holiday')
            || $user->can('view_SSS') || $user->can('view_Philhealth') || $user->can('view_Pagibig') || $user->can('view_Work Type'))
            <li class="sidebar-item">
                <a href="#payroll_setup" data-toggle="collapse" class="sidebar-link collapsed">
                    <span class="item">
                        <i class="align-middle mr-2 fas fa-fw fa-list-alt"></i> <span class="align-middle">PAYROLL</span>
                    </span>
                </a>
                <ul id="payroll_setup" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
                    <li class="list-title">PAYROLL SETUP</li>
                    @if($user->can('view_Payroll Calendar') || $user->can('add_Payroll Calendar') || $user->can('edit_Payroll Calendar') || $user->can('delete_Payroll Calendar'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/payroll_calendar">PAYROLL CALENDAR</a></li>
                    @endif

                    @if($user->can('view_Allowance') || $user->can('add_Allowance') || $user->can('edit_Allowance') || $user->can('delete_Allowance'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/allowance">ALLOWANCE</a></li>
                    @endif

                    @if($user->can('view_Earnings') || $user->can('add_Earnings') || $user->can('edit_Earnings') || $user->can('delete_Earnings'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/earnings">EARNINGS</a></li>
                    @endif

                    @if($user->can('view_Deductions') || $user->can('add_Deductions') || $user->can('edit_Deductions') || $user->can('delete_Deductions'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/deductions">DEDUCTIONS</a></li>
                    @endif

                    @if($user->can('view_Clearance Type') || $user->can('add_Clearance Type') || $user->can('edit_Clearance Type') || $user->can('delete_Clearance Type'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/clearance_types">CLEARANCE TYPE</a></li>
                    @endif

                    @if($user->can('view_Work Assignments') || $user->can('add_Work Assignments') || $user->can('edit_Work Assignments') || $user->can('delete_Work Assignments'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/work_assignments">WORK ASSIGNMENTS</a></li>
                    @endif

                    @if($user->can('view_Withholding Tax') || $user->can('add_Withholding Tax') || $user->can('edit_Withholding Tax') || $user->can('delete_Withholding Tax'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/withholding_tax">WITHHOLDING TAX</a></li>
                    @endif

                    @if($user->can('view_Reimbursement') || $user->can('add_Reimbursement') || $user->can('edit_Reimbursement') || $user->can('delete_Reimbursement'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/reimbursements">REIMBURSEMENT</a></li>
                    @endif

                    @if($user->can('view_Benefits') || $user->can('add_Benefits') || $user->can('edit_Benefits') || $user->can('delete_Benefits'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/benefits">BENEFITS</a></li>
                    @endif

                    @if($user->can('view_Leave Types') || $user->can('add_Leave Types') || $user->can('edit_Leave Types') || $user->can('delete_Leave Types'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/leave-type">LEAVE TYPES</a></li>
                    @endif

                    @if($user->can('view_Holiday') || $user->can('add_Holiday') || $user->can('edit_Holiday') || $user->can('delete_Holiday'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/holiday">HOLIDAY</a></li>
                    @endif

                    @if($user->can('view_Holiday Types') || $user->can('add_Holiday Types') || $user->can('edit_Holiday Types') || $user->can('delete_Holiday Types'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/holiday_type">HOLIDAY TYPES</a></li>
                    @endif

                    @if($user->can('view_SSS') || $user->can('add_SSS') || $user->can('edit_SSS') || $user->can('delete_SSS'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/sss">SSS</a></li>
                    @endif

                    @if($user->can('view_Philhealth') || $user->can('add_Philhealth') || $user->can('edit_Philhealth') || $user->can('delete_Philhealth'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/philhealth">PHILHEALTH</a></li>
                    @endif

                    @if($user->can('view_Pagibig') || $user->can('add_Pagibig') || $user->can('edit_Pagibig') || $user->can('delete_Pagibig'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/pagibig">PAG-IBIG</a></li>
                    @endif

                    @if($user->can('view_Work Type') || $user->can('add_Work Type') || $user->can('edit_Work Type') || $user->can('delete_Work Type'))
                    <li class="sidebar-item"><a class="sidebar-link" href="/payroll/work_type">WORK TYPE</a></li>
                    @endif
                </ul>
            </li>
            @endif
            <li class="sidebar-item">
                <a href="#roles_permission" data-toggle="collapse" class="sidebar-link collapsed">
                    <span class="item">
                        <i class="align-middle mr-2 fas fa-fw fa-list-alt"></i> <span class="align-middle">USER ACCESS</span>
                    </span>
                </a>
                <ul id="roles_permission" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
                    <li class="list-title">USER ACCESS</li>
                    <li class="sidebar-item"><a class="sidebar-link" href="/settings/users">USER</a></li>
                    <li class="sidebar-item"><a class="sidebar-link" href="/settings/role">ROLE</a></li>
                    <li class="sidebar-item"><a class="sidebar-link" href="/settings/permission">PERMISSIONS</a></li>
                </ul>
            </li>

        </ul>
        
    </div>
</nav>
