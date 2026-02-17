<?php

use App\NotificationRule;
use App\Roles;
use Illuminate\Database\Seeder;

class NotificationRuleMatrixSeeder extends Seeder
{
    public function run()
    {
        $roleMap = Roles::query()->get(['id', 'name'])->mapWithKeys(function ($role) {
            return [strtolower(trim($role->name)) => (int) $role->id];
        });

        $roleGroups = [
            'admin_super' => $this->resolveRoles($roleMap, [
                ['admin'],
                ['super admin'],
            ]),
            'purchasing_core' => $this->resolveRoles($roleMap, [
                ['procurement officer'],
                ['procurement manager'],
                ['stock clerks', 'stock clerk'],
            ]),
            'executives' => $this->resolveRoles($roleMap, [
                ['chief operating officer (coo)', 'chief operating officer', 'coo'],
                ['chief executive officer (ceo)', 'chief executive officer', 'ceo'],
            ]),
            'hr_team' => $this->resolveRoles($roleMap, [
                ['human resources'],
                ['hr'],
                ['hr manager'],
                ['hr officer'],
            ]),
            'payroll_team' => $this->resolveRoles($roleMap, [
                ['payroll'],
                ['payroll officer'],
                ['payroll manager'],
            ]),
            'timekeeping_team' => $this->resolveRoles($roleMap, [
                ['timekeeping'],
                ['timekeeper'],
                ['timekeeping officer'],
            ]),
            'inventory_team' => $this->resolveRoles($roleMap, [
                ['stock clerks', 'stock clerk'],
                ['warehouse'],
                ['inventory officer'],
            ]),
            'accounting_team' => $this->resolveRoles($roleMap, [
                ['accounting'],
                ['accountant'],
                ['accounting manager'],
                ['finance'],
            ]),
        ];

        $matrix = [
            // PURCHASE ORDER
            ['module' => 'purchase_order', 'from' => 'FOR_CHECKING', 'to' => 'DRAFT', 'title' => 'PO Returned to Draft', 'priority' => 2, 'important' => 1, 'action' => 0, 'groups' => ['purchasing_core', 'admin_super']],
            ['module' => 'purchase_order', 'from' => null, 'to' => 'FOR_CHECKING', 'title' => 'PO Submitted for Checking', 'priority' => 2, 'important' => 1, 'action' => 1, 'groups' => ['purchasing_core', 'admin_super']],
            ['module' => 'purchase_order', 'from' => null, 'to' => 'FOR_APPROVAL', 'title' => 'PO Submitted for Approval', 'priority' => 3, 'important' => 1, 'action' => 1, 'groups' => ['purchasing_core', 'executives', 'admin_super']],
            ['module' => 'purchase_order', 'from' => null, 'to' => 'APPROVED', 'title' => 'PO Approved', 'priority' => 3, 'important' => 1, 'action' => 0, 'groups' => ['purchasing_core', 'executives', 'admin_super']],
            ['module' => 'purchase_order', 'from' => null, 'to' => 'SENT_TO_SUPPLIER', 'title' => 'PO Sent to Supplier', 'priority' => 2, 'important' => 0, 'action' => 0, 'groups' => ['purchasing_core', 'executives', 'admin_super']],
            ['module' => 'purchase_order', 'from' => null, 'to' => 'PARTIALLY_DELIVERED', 'title' => 'PO Partially Delivered', 'priority' => 2, 'important' => 0, 'action' => 0, 'groups' => ['purchasing_core', 'executives', 'admin_super']],
            ['module' => 'purchase_order', 'from' => null, 'to' => 'COMPLETED', 'title' => 'PO Completed', 'priority' => 1, 'important' => 0, 'action' => 0, 'groups' => ['purchasing_core', 'executives', 'admin_super']],
            ['module' => 'purchase_order', 'from' => null, 'to' => 'NOT_DELIVERED', 'title' => 'PO Not Delivered', 'priority' => 2, 'important' => 1, 'action' => 1, 'groups' => ['purchasing_core', 'executives', 'admin_super']],
            ['module' => 'purchase_order', 'from' => null, 'to' => 'CANCELLED', 'title' => 'PO Cancelled', 'priority' => 2, 'important' => 1, 'action' => 1, 'groups' => ['purchasing_core', 'executives', 'admin_super']],

            // INVENTORY
            ['module' => 'inventory_request', 'from' => null, 'to' => 'SUBMITTED', 'title' => 'Inventory Request Submitted', 'priority' => 2, 'important' => 0, 'action' => 1, 'groups' => ['inventory_team', 'admin_super']],
            ['module' => 'inventory_request', 'from' => null, 'to' => 'APPROVED', 'title' => 'Inventory Request Approved', 'priority' => 2, 'important' => 0, 'action' => 0, 'groups' => ['inventory_team', 'admin_super']],
            ['module' => 'inventory_request', 'from' => null, 'to' => 'REJECTED', 'title' => 'Inventory Request Rejected', 'priority' => 2, 'important' => 1, 'action' => 0, 'groups' => ['inventory_team', 'admin_super']],
            ['module' => 'inventory_request', 'from' => null, 'to' => 'ISSUED', 'title' => 'Inventory Issued', 'priority' => 2, 'important' => 0, 'action' => 0, 'groups' => ['inventory_team', 'admin_super']],
            ['module' => 'inventory_request', 'from' => null, 'to' => 'RECEIVED', 'title' => 'Inventory Received', 'priority' => 1, 'important' => 0, 'action' => 0, 'groups' => ['inventory_team', 'admin_super']],

            // PAYROLL
            ['module' => 'payroll_summary', 'from' => null, 'to' => 'FOR_APPROVAL', 'title' => 'Payroll Submitted for Approval', 'priority' => 3, 'important' => 1, 'action' => 1, 'groups' => ['payroll_team', 'executives', 'admin_super']],
            ['module' => 'payroll_summary', 'from' => null, 'to' => 'APPROVED', 'title' => 'Payroll Approved', 'priority' => 3, 'important' => 1, 'action' => 0, 'groups' => ['payroll_team', 'admin_super']],
            ['module' => 'payroll_summary', 'from' => null, 'to' => 'SUBMITTED_FOR_PAYMENT', 'title' => 'Payroll Submitted for Payment', 'priority' => 3, 'important' => 1, 'action' => 1, 'groups' => ['payroll_team', 'accounting_team', 'admin_super']],
            ['module' => 'payroll_summary', 'from' => null, 'to' => 'PAID', 'title' => 'Payroll Paid', 'priority' => 2, 'important' => 0, 'action' => 0, 'groups' => ['payroll_team', 'admin_super']],

            // TIMEKEEPING
            ['module' => 'timekeeping_request', 'from' => null, 'to' => 'SUBMITTED', 'title' => 'Timekeeping Request Submitted', 'priority' => 2, 'important' => 0, 'action' => 1, 'groups' => ['timekeeping_team', 'hr_team', 'admin_super']],
            ['module' => 'timekeeping_request', 'from' => null, 'to' => 'APPROVED', 'title' => 'Timekeeping Request Approved', 'priority' => 2, 'important' => 0, 'action' => 0, 'groups' => ['timekeeping_team', 'hr_team', 'admin_super']],
            ['module' => 'timekeeping_request', 'from' => null, 'to' => 'REJECTED', 'title' => 'Timekeeping Request Rejected', 'priority' => 2, 'important' => 1, 'action' => 0, 'groups' => ['timekeeping_team', 'hr_team', 'admin_super']],
            ['module' => 'timekeeping_request', 'from' => null, 'to' => 'OVERDUE', 'title' => 'Timekeeping Request Overdue', 'priority' => 2, 'important' => 1, 'action' => 1, 'groups' => ['timekeeping_team', 'hr_team', 'admin_super']],

            // HR
            ['module' => 'employee_lifecycle', 'from' => null, 'to' => 'SUBMITTED', 'title' => 'Employee Record Submitted', 'priority' => 2, 'important' => 0, 'action' => 1, 'groups' => ['hr_team', 'admin_super']],
            ['module' => 'employee_lifecycle', 'from' => null, 'to' => 'FOR_REVIEW', 'title' => 'Employee Record For Review', 'priority' => 2, 'important' => 0, 'action' => 1, 'groups' => ['hr_team', 'admin_super']],
            ['module' => 'employee_lifecycle', 'from' => null, 'to' => 'APPROVED', 'title' => 'Employee Record Approved', 'priority' => 2, 'important' => 0, 'action' => 0, 'groups' => ['hr_team', 'admin_super']],
            ['module' => 'employee_lifecycle', 'from' => null, 'to' => 'REJECTED', 'title' => 'Employee Record Rejected', 'priority' => 2, 'important' => 1, 'action' => 0, 'groups' => ['hr_team', 'admin_super']],
            ['module' => 'employee_lifecycle', 'from' => null, 'to' => 'EXPIRED', 'title' => 'Employee Document Expiring', 'priority' => 2, 'important' => 1, 'action' => 1, 'groups' => ['hr_team', 'admin_super']],

            // ACCOUNTING
            ['module' => 'journal_entry', 'from' => null, 'to' => 'SUBMITTED', 'title' => 'Journal Entry Submitted', 'priority' => 2, 'important' => 0, 'action' => 1, 'groups' => ['accounting_team', 'admin_super']],
            ['module' => 'journal_entry', 'from' => null, 'to' => 'APPROVED', 'title' => 'Journal Entry Approved', 'priority' => 2, 'important' => 0, 'action' => 0, 'groups' => ['accounting_team', 'admin_super']],
            ['module' => 'journal_entry', 'from' => null, 'to' => 'POSTED', 'title' => 'Journal Entry Posted', 'priority' => 2, 'important' => 0, 'action' => 0, 'groups' => ['accounting_team', 'admin_super']],
            ['module' => 'journal_entry', 'from' => null, 'to' => 'REVERSED', 'title' => 'Journal Entry Reversed', 'priority' => 3, 'important' => 1, 'action' => 1, 'groups' => ['accounting_team', 'admin_super']],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($matrix as $row) {
            $roleIds = $this->mergeRoleGroups($roleGroups, $row['groups']);

            if (empty($roleIds)) {
                $skipped++;
                continue;
            }

            $rule = NotificationRule::updateOrCreate(
                [
                    'module' => $row['module'],
                    'from_status' => $row['from'],
                    'to_status' => $row['to'],
                    'title' => $row['title'],
                ],
                [
                    'message' => 'Ref #{{reference_no}} (ID: {{transaction_id}}) moved from {{from_status}} to {{to_status}} by {{actor_name}}.',
                    'channel' => 'header',
                    'priority' => $row['priority'],
                    'is_important' => $row['important'],
                    'action_required' => $row['action'],
                    'is_active' => true,
                ]
            );

            $rule->roles()->sync($roleIds);
            $created++;
        }

        $this->command->info('NotificationRuleMatrixSeeder done. Upserted: ' . $created . ', skipped (no role mapping): ' . $skipped);
    }

    private function mergeRoleGroups($groups, $groupNames)
    {
        $ids = [];
        foreach ($groupNames as $groupName) {
            $ids = array_merge($ids, $groups[$groupName] ?? []);
        }

        $ids = array_values(array_unique(array_filter($ids)));
        sort($ids);

        return $ids;
    }

    private function resolveRoles($roleMap, $aliasGroups)
    {
        $ids = [];
        foreach ($aliasGroups as $aliases) {
            foreach ($aliases as $alias) {
                $k = strtolower(trim($alias));
                if (isset($roleMap[$k])) {
                    $ids[] = $roleMap[$k];
                    break;
                }
            }
        }

        return array_values(array_unique($ids));
    }
}
