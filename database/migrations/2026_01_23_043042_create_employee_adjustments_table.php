<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeAdjustmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_adjustments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id');
            $table->enum('adjustment_type', ['SALARY', 'ALLOWANCE', 'DEDUCTION', 'POSITION', 'CORRECTION']);
            $table->decimal('old_value', 10, 2)->nullable();
            $table->decimal('new_value', 10, 2)->nullable();
            $table->decimal('amount', 10, 2)->nullable(); // for allowance/deduction
            $table->text('remarks')->nullable();
            $table->date('effective_date');
            $table->unsignedBigInteger('adjusted_by')->nullable(); // user/admin who made adjustment
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->timestamps();

            $table->foreign('employee_id')
                ->references('id')
                ->on('employments');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_adjustments');
    }
}
