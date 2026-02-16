<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSelectedDaysToWorkCalendarPresetsTable extends Migration
{
    public function up()
    {
        Schema::table('work_calendar_presets', function (Blueprint $table) {
            if (!Schema::hasColumn('work_calendar_presets', 'selected_days')) {
                $table->text('selected_days')->nullable()->after('name');
            }

            if (!Schema::hasColumn('work_calendar_presets', 'time_in')) {
                $table->string('time_in')->nullable()->after('selected_days');
            }

            if (!Schema::hasColumn('work_calendar_presets', 'time_off')) {
                $table->string('time_off')->nullable()->after('time_in');
            }
        });
    }

    public function down()
    {
        Schema::table('work_calendar_presets', function (Blueprint $table) {
            if (Schema::hasColumn('work_calendar_presets', 'time_off')) {
                $table->dropColumn('time_off');
            }

            if (Schema::hasColumn('work_calendar_presets', 'time_in')) {
                $table->dropColumn('time_in');
            }

            if (Schema::hasColumn('work_calendar_presets', 'selected_days')) {
                $table->dropColumn('selected_days');
            }
        });
    }
}
