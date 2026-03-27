<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAutoReflectToAllowanceTaggingsTable extends Migration
{
    public function up()
    {
        Schema::table('allowance_taggings', function (Blueprint $table) {
            $table->boolean('auto_reflect_in_payroll')->default(true)->after('amount');
        });
    }

    public function down()
    {
        Schema::table('allowance_taggings', function (Blueprint $table) {
            $table->dropColumn('auto_reflect_in_payroll');
        });
    }
}
