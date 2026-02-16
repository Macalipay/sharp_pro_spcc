<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmployeeIdToScheduleRequestsTable extends Migration
{
    public function up()
    {
        Schema::table('schedule_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('schedule_requests', 'employee_id')) {
                $table->unsignedBigInteger('employee_id')->nullable()->after('id');
                $table->foreign('employee_id')->references('id')->on('employees');
            }
        });
    }

    public function down()
    {
        Schema::table('schedule_requests', function (Blueprint $table) {
            if (Schema::hasColumn('schedule_requests', 'employee_id')) {
                $table->dropForeign(['employee_id']);
                $table->dropColumn('employee_id');
            }
        });
    }
}
