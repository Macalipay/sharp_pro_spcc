<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeDeductionTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('employee_deduction_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_deduction_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('summary_id')->nullable();
            $table->string('sequence_no')->nullable();
            $table->unsignedBigInteger('deduction_id');
            $table->date('payroll_period_start')->nullable();
            $table->date('payroll_period_end')->nullable();
            $table->date('processed_date')->nullable();
            $table->string('reference_name')->nullable();
            $table->decimal('scheduled_amount', 15, 2)->default(0);
            $table->decimal('actual_deducted_amount', 15, 2)->default(0);
            $table->decimal('running_balance', 15, 2)->default(0);
            $table->string('source')->default('auto_payroll');
            $table->text('notes')->nullable();
            $table->string('payroll_reference_no')->nullable();
            $table->string('status')->default('posted');
            $table->unsignedBigInteger('workstation_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['employee_deduction_id', 'summary_id'], 'emp_ded_txn_ded_sum_idx');
            $table->index(['employee_id', 'processed_date'], 'emp_ded_txn_emp_date_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_deduction_transactions');
    }
}
