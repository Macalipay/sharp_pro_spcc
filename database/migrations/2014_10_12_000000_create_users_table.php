<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('firstname');
            $table->string('middlename')->nullable();
            $table->string('lastname');
            $table->string('suffix')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('profile_img')->default('default.jpg');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('status');
            $table->integer('workstation_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->integer('deleted_by')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('created_by')
                ->references('id')
                ->on('users');
                
            $table->foreign('updated_by')
                ->references('id')
                ->on('users');
        });

        
        DB::table('users')->insert([
            [
                'firstname' => 'Super', 
                'middlename' => '', 
                'lastname' => 'Admin',
                'suffix' => '',
                'profile_img' => 'default.jpg', 
                'email' => 'superadmin@gmail.com', 
                'status' => '1', 
                'workstation_id' => 1, 
                'created_by' => 1, 
                'updated_by' => 1, 
                'password' => Hash::make('P@ssw0rd')
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
        Schema::dropIfExists('users');
    }
}
