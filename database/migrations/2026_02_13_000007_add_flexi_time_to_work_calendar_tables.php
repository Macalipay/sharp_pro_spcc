<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFlexiTimeToWorkCalendarTables extends Migration
{
    public function up()
    {
        Schema::table('work_calendars', function (Blueprint $table) {
            if (!Schema::hasColumn('work_calendars', 'is_flexi_time')) {
                $table->boolean('is_flexi_time')->default(0)->after('saturday_end_time');
            }
        });

        Schema::table('work_calendar_presets', function (Blueprint $table) {
            if (!Schema::hasColumn('work_calendar_presets', 'is_flexi_time')) {
                $table->boolean('is_flexi_time')->default(0)->after('time_off');
            }
        });
    }

    public function down()
    {
        Schema::table('work_calendars', function (Blueprint $table) {
            if (Schema::hasColumn('work_calendars', 'is_flexi_time')) {
                $table->dropColumn('is_flexi_time');
            }
        });

        Schema::table('work_calendar_presets', function (Blueprint $table) {
            if (Schema::hasColumn('work_calendar_presets', 'is_flexi_time')) {
                $table->dropColumn('is_flexi_time');
            }
        });
    }
}
