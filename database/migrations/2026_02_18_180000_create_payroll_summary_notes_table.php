`<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollSummaryNotesTable extends Migration
{
    public function up()
    {
        Schema::create('payroll_summary_notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('summary_id');
            $table->text('note');
            $table->unsignedBigInteger('workstation_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('summary_id')->references('id')->on('payroll_summaries');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_summary_notes');
    }
}

