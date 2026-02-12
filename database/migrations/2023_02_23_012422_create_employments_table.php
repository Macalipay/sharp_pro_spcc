<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmploymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('classes_id');
            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('payroll_calendar_id');
            $table->string('employment_date');
            $table->string('tax_rate')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('employee_id')
                ->references('id')
                ->on('employees');
                
            $table->foreign('classes_id')
                ->references('id')
                ->on('classes');

            $table->foreign('position_id')
                ->references('id')
                ->on('positions');

            $table->foreign('department_id')
                ->references('id')
                ->on('departments');
                
            $table->foreign('payroll_calendar_id')
                ->references('id')
                ->on('payroll_calendars');
            
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
        Schema::dropIfExists('employments');
    }
}
