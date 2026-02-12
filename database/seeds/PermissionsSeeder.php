<?php

use App\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission as ModelsPermission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'Employee' => [
                'Employee Profile' => ['view', 'edit','delete'],
                'Employees Masterlist' => ['view','download'],
            ],
            'TimeSheet' => [
                'Time Logs' => ['view', 'print', 'release'],
                'Daily Time Sheet' => ['view','print'],
                'Time Sheet Summary' => ['view', 'delete'],
            ],
            'Payroll' => [
                'Payroll Summary' => ['view', 'edit', 'delete'],
                'Leave Request' => ['view', 'add', 'delete'],
                'Overtime Request' => ['view', 'add', 'edit', 'update', 'delete'],
                'Scheduling' => ['view', 'add', 'edit', 'delete', 'print'],
                'Quit Claims' => ['view', 'add', 'edit', 'delete'],
                '13th Month' => ['view', 'edit', 'delete'],
            ],
            'Accounting' => [
                'Journal Entries' => ['view', 'edit', 'delete'],
                'Change of Accounts' => ['view', 'edit', 'delete'],
                'Account Type' => ['view', 'edit', 'delete'],
            ],
            'Purchasing' => [
                'Purchase Order' => ['view', 'add', 'edit', 'delete', 'print'],

                'Draft' => ['view', 'submit'],
                'For Checking' => ['view', 'submit'],
                'For Approval' => ['view', 'approve','decline'],
                'Approved' => ['view', 'send'],
                'Sent to Supplier' => ['view', 'partially delivered', 'not delivered', 'cancelled', 'completed'],
                'Partially Delivered' => ['view', 'not delivered', 'cancelled', 'completed'],
                'Completed' => ['view'],
                'Not Delivered' => ['view'],
                'Cancelled' => ['view'],

                'Credit Note' => ['view', 'edit', 'delete'],
                'Site' => ['view', 'add', 'edit', 'delete'],
                'Supplier' => ['view', 'add', 'edit', 'delete'],
                'Material Category' => ['view', 'add', 'edit', 'delete'],
                'Materials' => ['view', 'add', 'edit', 'delete'],
            ],
            'Inventory' => [
                'Inventory' => ['view', 'add', 'edit', 'delete', 'print'],
                'Inventory Request' => ['view', 'add', 'edit', 'delete', 'print'],
                'Owner Supplied Material' => ['view', 'add', 'edit', 'delete'],
                'Inventory History' => ['view', 'add', 'edit', 'delete'],
                'Damages' => ['view', 'add', 'edit', 'delete'],
                'Transfer History' => ['view', 'add', 'edit', 'delete'],
            ],
            'Organization' => [
                'Company Profile' => ['view', 'add', 'edit', 'delete', 'print'],
                'Classes' => ['view', 'add', 'edit', 'delete'],
                'Departments' => ['view', 'add', 'edit', 'delete'],
                'Positions' => ['view', 'add', 'edit', 'delete'],
                'Projects' => ['view', 'add', 'edit', 'delete'],
            ],
            'Payroll Setup' => [
                'Payroll Calendar' => ['view', 'add', 'edit', 'delete'],
                'Allowance' => ['view', 'add', 'edit', 'delete'],
                'Earnings' => ['view', 'add', 'edit', 'delete'],
                'Deductions' => ['view', 'add', 'edit', 'delete'],
                'Clearance Type' => ['view', 'add', 'edit', 'delete'],
                'Work Assignments' => ['view', 'add', 'edit', 'delete'],
                'Withholding Tax' => ['view', 'add', 'edit', 'delete'],
                'Reimbursement' => ['view', 'add', 'edit', 'delete'],
                'Benefits' => ['view', 'add', 'edit', 'delete'],
                'Leave Types' => ['view', 'add', 'edit', 'delete'],
                'Holiday' => ['view', 'add', 'edit', 'delete'],
                'Holiday Types' => ['view', 'add', 'edit', 'delete'],
                'SSS' => ['view', 'add', 'edit', 'delete'],
                'Pagibig' => ['view', 'add', 'edit', 'delete'],
                'Work Type' => ['view', 'add', 'edit', 'delete'],
            ],
        ];

        foreach ($permissions as $module => $categories) {
            foreach ($categories as $category => $actions) {
                foreach ($actions as $action) {
                    $permissionName = "{$action}_{$category}";
                    ModelsPermission::firstOrCreate(['name' => $permissionName]);
                }
            }
        }
        echo "Permissions with actions seeded.\n";
    }
}