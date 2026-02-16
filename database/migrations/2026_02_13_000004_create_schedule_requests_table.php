<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduleRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('schedule_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('request_no')->unique();
            $table->date('request_date');
            $table->string('schedule_type');
            $table->date('period_start');
            $table->date('period_end');
            $table->text('reason')->nullable();
            $table->string('status')->default('pending');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('requested_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->unsignedBigInteger('workstation_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('requested_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->foreign('workstation_id')->references('id')->on('workstations');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('schedule_requests');
    }
}
