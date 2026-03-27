<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProjectNameToTimeLogsTable extends Migration
{
    public function up()
    {
        Schema::table('time_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('time_logs', 'project_name')) {
                $table->string('project_name')->nullable()->after('schedule_status');
            }
        });
    }

    public function down()
    {
        Schema::table('time_logs', function (Blueprint $table) {
            if (Schema::hasColumn('time_logs', 'project_name')) {
                $table->dropColumn('project_name');
            }
        });
    }
}
