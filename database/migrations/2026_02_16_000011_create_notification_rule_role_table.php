<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationRuleRoleTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('notification_rule_role')) {
            return;
        }

        Schema::create('notification_rule_role', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('notification_rule_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamps();

            $table->foreign('notification_rule_id')->references('id')->on('notification_rules')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->unique(['notification_rule_id', 'role_id'], 'notification_rule_role_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notification_rule_role');
    }
}
