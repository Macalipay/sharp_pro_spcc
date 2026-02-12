<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRateToPayrollSummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_summary_details', function (Blueprint $table) {
            $table->string('daily')->default('0')->nullable();
            $table->string('monthly')->default('0')->nullable();
            $table->string('hourly')->default('0')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_summary_details', function (Blueprint $table) {
            //
        });
    }
}
