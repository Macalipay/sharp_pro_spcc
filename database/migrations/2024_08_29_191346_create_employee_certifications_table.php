<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeCertificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_certifications', function (Blueprint $table) {
            $table->bigIncrements('id');  
            $table->string('certification_no');
            $table->string('certification_name');
            $table->string('certification_authority');
            $table->string('certification_description');
            $table->string('certification_date');
            $table->string('certification_expiration_date');
            $table->string('certification_level');
            $table->string('certification_status');
            $table->string('certification_achievements');
            $table->string('certification_renewal_date');
            $table->string('recertification_date');

            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
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
        Schema::dropIfExists('employee_certifications');
    }
}
