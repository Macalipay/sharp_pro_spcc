<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddSystemLockFieldsToChartOfAccounts extends Migration
{
    public function up()
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('chart_of_accounts', 'is_system_locked')) {
                $table->boolean('is_system_locked')->default(0)->after('allow_for_payments');
            }
            if (!Schema::hasColumn('chart_of_accounts', 'system_key')) {
                $table->string('system_key')->nullable()->after('is_system_locked');
            }
            if (!Schema::hasColumn('chart_of_accounts', 'allow_manual_journal_posting')) {
                $table->boolean('allow_manual_journal_posting')->default(1)->after('system_key');
            }
        });

        $liabilityType = DB::table('account_types')
            ->whereRaw("UPPER(COALESCE(category,'')) = 'LIABILITY'")
            ->orderBy('id', 'asc')
            ->first();

        $workstationId = DB::table('workstations')->orderBy('id', 'asc')->value('id');
        $userId = DB::table('users')->orderBy('id', 'asc')->value('id');

        if ($liabilityType && $workstationId && $userId) {
            $existing = DB::table('chart_of_accounts')
                ->whereNull('deleted_at')
                ->where('system_key', 'ACCOUNTS_PAYABLE_CONTROL')
                ->first();

            if (!$existing) {
                $existingByName = DB::table('chart_of_accounts')
                    ->whereNull('deleted_at')
                    ->whereRaw("UPPER(TRIM(account_name)) = 'ACCOUNTS PAYABLE'")
                    ->first();

                if ($existingByName) {
                    DB::table('chart_of_accounts')
                        ->where('id', $existingByName->id)
                        ->update([
                            'account_type' => $liabilityType->id,
                            'normal_balance' => 'CREDIT',
                            'allow_for_payments' => 0,
                            'is_system_locked' => 1,
                            'system_key' => 'ACCOUNTS_PAYABLE_CONTROL',
                            'allow_manual_journal_posting' => 0,
                            'updated_by' => $userId,
                            'updated_at' => now(),
                        ]);
                } else {
                    $candidate = 2000000001;
                    while (DB::table('chart_of_accounts')->where('account_number', (string) $candidate)->whereNull('deleted_at')->exists()) {
                        $candidate++;
                    }

                    DB::table('chart_of_accounts')->insert([
                        'account_number' => (string) $candidate,
                        'account_name' => 'Accounts Payable',
                        'account_type' => $liabilityType->id,
                        'description' => 'System-locked Accounts Payable control account',
                        'normal_balance' => 'CREDIT',
                        'workstation_id' => $workstationId,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                        'allow_for_payments' => 0,
                        'tax' => null,
                        'is_system_locked' => 1,
                        'system_key' => 'ACCOUNTS_PAYABLE_CONTROL',
                        'allow_manual_journal_posting' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down()
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('chart_of_accounts', 'allow_manual_journal_posting')) {
                $table->dropColumn('allow_manual_journal_posting');
            }
            if (Schema::hasColumn('chart_of_accounts', 'system_key')) {
                $table->dropColumn('system_key');
            }
            if (Schema::hasColumn('chart_of_accounts', 'is_system_locked')) {
                $table->dropColumn('is_system_locked');
            }
        });
    }
}

