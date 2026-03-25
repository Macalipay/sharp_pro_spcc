<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDeviceUserIdToEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('device_user_id')->nullable()->after('rfid');
        });

        $sampleEmployee = DB::table('employees')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->first();

        if ($sampleEmployee !== null) {
            DB::table('employees')
                ->where('id', $sampleEmployee->id)
                ->whereNull('device_user_id')
                ->update(['device_user_id' => '5']);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('device_user_id');
        });
    }
}
