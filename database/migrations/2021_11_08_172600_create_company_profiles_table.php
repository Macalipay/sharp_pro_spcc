<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('company_no')->unique();
            $table->string('company_name');
            $table->string('email')->unique();
            $table->string('contact_number')->nullable();
            $table->string('address')->nullable();
            $table->string('legal_name')->nullable();
            $table->string('tin')->nullable();;
            $table->string('company_logo')->default('default.png');;
            $table->string('theme')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->integer('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('created_by')
                ->references('id')
                ->on('users');
                
            $table->foreign('updated_by')
                ->references('id')
                ->on('users');
        });

        DB::table('company_profiles')->insert([
            [
                'company_no' => 'C0000001', 
                'company_name' => 'Admin Setup', 
                'email' => 'superadmin@gmail.com',
                'contact_number' => '',
                'address' => '', 
                'legal_name' => '', 
                'company_logo' => 'admin.png', 
                'theme' => '1', 
                'created_by' => 1,
                'updated_by' => 1
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_profiles');
    }
}
