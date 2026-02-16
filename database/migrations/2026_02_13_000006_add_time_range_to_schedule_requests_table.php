<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimeRangeToScheduleRequestsTable extends Migration
{
    public function up()
    {
        Schema::table('schedule_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('schedule_requests', 'start_time')) {
                $table->string('start_time')->nullable()->after('period_end');
            }

            if (!Schema::hasColumn('schedule_requests', 'end_time')) {
                $table->string('end_time')->nullable()->after('start_time');
            }
        });
    }

    public function down()
    {
        Schema::table('schedule_requests', function (Blueprint $table) {
            if (Schema::hasColumn('schedule_requests', 'end_time')) {
                $table->dropColumn('end_time');
            }

            if (Schema::hasColumn('schedule_requests', 'start_time')) {
                $table->dropColumn('start_time');
            }
        });
    }
}
