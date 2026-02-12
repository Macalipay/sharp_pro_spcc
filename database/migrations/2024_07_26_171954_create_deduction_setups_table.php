<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeductionSetupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deduction_setups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('deduction_id');
            $table->string('amount');
            $table->string('sequence_no');
            $table->string('date');
            $table->timestamps();

            $table->foreign('employee_id')
                ->references('id')
                ->on('employees');

            $table->foreign('deduction_id')
                ->references('id')
                ->on('deductions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deduction_setups');
    }
}
