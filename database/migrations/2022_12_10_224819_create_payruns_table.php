<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayrunsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payruns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('payment_schedule');
            $table->date('period_coverage_start');
            $table->date('period_coverage_end');
            $table->datetime('processed_date');
            $table->integer('processed_by');
            $table->decimal('gross_amount');
            $table->decimal('taxable_income');
            $table->decimal('deductions');
            $table->decimal('witholding_taxes');
            $table->decimal('net_pay');
            $table->string('payslip_status');
            $table->string('status');
            $table->unsignedBigInteger('workstation_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
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
        Schema::dropIfExists('payruns');
    }
}
