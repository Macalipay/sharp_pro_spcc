<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkCalendarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_calendars', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sunday_start_time')->nullable();
            $table->string('sunday_end_time')->nullable();
            $table->string('monday_start_time')->nullable();
            $table->string('monday_end_time')->nullable();
            $table->string('tuesday_start_time')->nullable();
            $table->string('tuesday_end_time')->nullable();
            $table->string('wednesday_start_time')->nullable();
            $table->string('wednesday_end_time')->nullable();
            $table->string('thursday_start_time')->nullable();
            $table->string('thursday_end_time')->nullable();
            $table->string('friday_start_time')->nullable();
            $table->string('friday_end_time')->nullable();
            $table->string('saturday_start_time')->nullable();
            $table->string('saturday_end_time')->nullable();
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
        Schema::dropIfExists('work_calendars');
    }
}
