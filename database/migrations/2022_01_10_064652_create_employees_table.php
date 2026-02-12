<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('employee_no')->unique();
            $table->string('firstname');
            $table->string('middlename')->nullable();
            $table->string('lastname');
            $table->string('suffix')->nullable();
            $table->string('birthdate');
            $table->string('gender');
            $table->string('citizenship');
            $table->string('phone1')->nullable();
            $table->string('phone2')->nullable();
            $table->string('street_1')->nullable();
            $table->string('barangay_1')->nullable();
            $table->string('city_1')->nullable();;
            $table->string('province_1')->nullable();
            $table->string('country_1')->nullable();
            $table->string('zip_1')->nullable();
            $table->string('street_2')->nullable();
            $table->string('barangay_2')->nullable();
            $table->string('city_2')->nullable();
            $table->string('province_2')->nullable();
            $table->string('country_2')->nullable();
            $table->string('zip_2')->nullable();
            $table->string('profile_img')->default('default.png');
            $table->string('emergency_name')->nullable();
            $table->string('emergency_no')->nullable();
            $table->string('email')->unique();
            $table->string('bank_account')->nullable();
            $table->string('tin_number')->nullable();
            $table->string('sss_number')->nullable();
            $table->string('pagibig_number')->nullable();
            $table->string('philhealth_number')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('status');
            $table->string('rfid')->nullable();
            $table->unsignedBigInteger('workstation_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->integer('deleted_by')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('employees');
    }
}


