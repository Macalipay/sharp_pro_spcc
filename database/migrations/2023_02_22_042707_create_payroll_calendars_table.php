<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayrollCalendarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_calendars', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->integer('type');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('start_day')->nullable();
            $table->string('end_day')->nullable();
            $table->string('payment_day')->nullable();
            $table->integer('set_as_default');
            $table->integer('status');
            $table->unsignedBigInteger('workstation_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('workstation_id')
                ->references('id')
                ->on('workstations');

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
        Schema::dropIfExists('payroll_calendars');
    }
}
