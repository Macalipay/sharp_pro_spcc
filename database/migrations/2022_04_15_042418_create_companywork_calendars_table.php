<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyworkCalendarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companywork_calendars', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sunday_start_time')->nullable();
            $table->string('sunday_end_time')->nullable();
            $table->string('monday_start_time')->nullable();
            $table->string('monday_end_time')->nullable();
            $table->string('tuesday_start_time')->nullable();
            $table->string('tuesday_end_time')->nullable();
            $table->string('wednesday_start_time')->nullable();
            $table->string('wednesday_end_time')->nullable();
            $table->string('thursday_start_time')->nullable();
            $table->string('thursday_end_time')->nullable();
            $table->string('friday_start_time')->nullable();
            $table->string('friday_end_time')->nullable();
            $table->string('saturday_start_time')->nullable();
            $table->string('saturday_end_time')->nullable();
            $table->unsignedBigInteger('workstation_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
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
        
        DB::table('companywork_calendars')->insert([
            [
                'sunday_start_time' => null, 
                'sunday_end_time' => null, 
                'monday_start_time' => '09:00', 
                'monday_end_time' => '17:00', 
                'tuesday_start_time' => '09:00', 
                'tuesday_end_time' => '17:00', 
                'wednesday_start_time' => '09:00', 
                'wednesday_end_time' => '17:00', 
                'thursday_start_time' => '09:00', 
                'thursday_end_time' => '17:00', 
                'friday_start_time' => '09:00', 
                'friday_end_time' => '17:00', 
                'saturday_start_time' => null, 
                'saturday_end_time' => null, 
                'workstation_id' => 1,
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
        Schema::dropIfExists('companywork_calendars');
    }
}
