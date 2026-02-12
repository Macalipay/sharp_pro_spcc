<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayrollCalendarDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_calendar_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('payroll_calendar_header_id');
            $table->date('start_date');
            $table->date('first_payment');
            $table->string('start_of_week');
            $table->string('end_of_week');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('payroll_calendar_header_id')
                ->references('id')
                ->on('payroll_calendar_headers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_calendar_details');
    }
}
