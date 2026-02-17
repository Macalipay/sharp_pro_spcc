<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationRulesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('notification_rules')) {
            return;
        }

        Schema::create('notification_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('module');
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->string('channel')->default('header');
            $table->tinyInteger('priority')->default(1);
            $table->boolean('is_important')->default(false);
            $table->boolean('action_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['module', 'from_status', 'to_status', 'is_active'], 'notification_rules_lookup_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notification_rules');
    }
}
