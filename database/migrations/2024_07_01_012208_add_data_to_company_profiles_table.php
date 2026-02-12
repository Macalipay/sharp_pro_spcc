<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataToCompanyProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->string('color')->nullable();
            $table->string('details')->nullable();
            $table->integer('dark_mode')->default(0);
            $table->integer('bank_account')->nullable();
            $table->integer('payroll_liability')->nullable();
            $table->integer('salary_expense')->nullable();
            $table->integer('salary_payable')->nullable();
            $table->string('opening_balance_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            
        });
    }
}
