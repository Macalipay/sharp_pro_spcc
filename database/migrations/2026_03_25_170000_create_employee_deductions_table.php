<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeDeductionsTable extends Migration
{
    public function up()
    {
        Schema::create('employee_deductions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('deduction_id');
            $table->string('reference_name')->nullable();
            $table->text('description')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->unsignedInteger('payment_terms')->nullable();
            $table->decimal('deduction_per_payroll', 15, 2)->default(0);
            $table->string('deduction_frequency')->default('every_payroll');
            $table->date('effective_start_payroll')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('auto_deduct_in_payroll')->default(true);
            $table->boolean('stop_when_fully_paid')->default(true);
            $table->boolean('allow_manual_override')->default(false);
            $table->string('status')->default('active');
            $table->decimal('total_paid', 15, 2)->default(0);
            $table->decimal('remaining_balance', 15, 2)->default(0);
            $table->unsignedBigInteger('workstation_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index(['employee_id', 'auto_deduct_in_payroll']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_deductions');
    }
}
