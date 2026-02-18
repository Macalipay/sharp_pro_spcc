<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxAndAllowForPaymentsToChartOfAccounts extends Migration
{
    public function up()
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('chart_of_accounts', 'tax')) {
                $table->string('tax')->nullable()->after('description');
            }

            if (!Schema::hasColumn('chart_of_accounts', 'allow_for_payments')) {
                $table->boolean('allow_for_payments')->default(0)->after('tax');
            }
        });
    }

    public function down()
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('chart_of_accounts', 'allow_for_payments')) {
                $table->dropColumn('allow_for_payments');
            }

            if (Schema::hasColumn('chart_of_accounts', 'tax')) {
                $table->dropColumn('tax');
            }
        });
    }
}
