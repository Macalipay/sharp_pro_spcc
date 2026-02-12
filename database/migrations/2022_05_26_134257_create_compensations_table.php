<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompensationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compensations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('annual_salary');
            $table->string('monthly_salary');
            $table->string('semi_monthly_salary');
            $table->string('weekly_salary');
            $table->string('daily_salary');
            $table->string('hourly_salary');
            $table->string('tax');
            $table->integer('government_mandated_benefits');
            $table->integer('other_company_benefits');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees');
                
            $table->foreign('created_by')
                ->references('id')
                ->on('users');
                
            $table->foreign('updated_by')
                ->references('id')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('compensations');
    }
}
