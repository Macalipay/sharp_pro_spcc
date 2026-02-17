<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAttachmentToEmployeeProfileRecords extends Migration
{
    public function up()
    {
        Schema::table('employee_educational_backgrounds', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_educational_backgrounds', 'attachment')) {
                $table->string('attachment')->nullable()->after('school');
            }
        });

        Schema::table('employee_work_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_work_histories', 'attachment')) {
                $table->string('attachment')->nullable()->after('remarks');
            }
        });

        Schema::table('employee_certifications', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_certifications', 'attachment')) {
                $table->string('attachment')->nullable()->after('recertification_date');
            }
        });

        Schema::table('employee_trainings', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_trainings', 'attachment')) {
                $table->string('attachment')->nullable()->after('expiration_date');
            }
        });
    }

    public function down()
    {
        Schema::table('employee_educational_backgrounds', function (Blueprint $table) {
            if (Schema::hasColumn('employee_educational_backgrounds', 'attachment')) {
                $table->dropColumn('attachment');
            }
        });

        Schema::table('employee_work_histories', function (Blueprint $table) {
            if (Schema::hasColumn('employee_work_histories', 'attachment')) {
                $table->dropColumn('attachment');
            }
        });

        Schema::table('employee_certifications', function (Blueprint $table) {
            if (Schema::hasColumn('employee_certifications', 'attachment')) {
                $table->dropColumn('attachment');
            }
        });

        Schema::table('employee_trainings', function (Blueprint $table) {
            if (Schema::hasColumn('employee_trainings', 'attachment')) {
                $table->dropColumn('attachment');
            }
        });
    }
}
