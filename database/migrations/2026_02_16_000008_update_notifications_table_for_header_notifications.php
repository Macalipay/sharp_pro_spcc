<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateNotificationsTableForHeaderNotifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('notifications')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            // Keep legacy columns compatible with new inserts.
            if (Schema::hasColumn('notifications', 'subject')) {
                $table->string('subject')->nullable()->change();
            }
            if (Schema::hasColumn('notifications', 'details')) {
                $table->string('details')->nullable()->change();
            }
            if (Schema::hasColumn('notifications', 'module')) {
                $table->string('module')->nullable()->change();
            }
            if (Schema::hasColumn('notifications', 'source_id')) {
                $table->integer('source_id')->nullable()->change();
            }
            if (Schema::hasColumn('notifications', 'link')) {
                $table->string('link')->nullable()->change();
            }
            if (Schema::hasColumn('notifications', 'status')) {
                $table->integer('status')->nullable()->change();
            }
            if (Schema::hasColumn('notifications', 'workstation_id')) {
                $table->unsignedBigInteger('workstation_id')->nullable()->change();
            }
            if (Schema::hasColumn('notifications', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->change();
            }
            if (Schema::hasColumn('notifications', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->change();
            }

            if (!Schema::hasColumn('notifications', 'target_user_id')) {
                $table->unsignedBigInteger('target_user_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('notifications', 'target_roles')) {
                $table->json('target_roles')->nullable()->after('target_user_id');
            }

            if (!Schema::hasColumn('notifications', 'reference_id')) {
                $table->unsignedBigInteger('reference_id')->nullable()->after('type');
            }

            if (!Schema::hasColumn('notifications', 'title')) {
                $table->string('title')->nullable()->after('reference_id');
            }
            if (!Schema::hasColumn('notifications', 'message')) {
                $table->text('message')->nullable()->after('title');
            }
            if (!Schema::hasColumn('notifications', 'icon')) {
                $table->string('icon')->nullable()->after('message');
            }

            if (!Schema::hasColumn('notifications', 'url')) {
                $table->string('url')->nullable()->after('icon');
            }
            if (!Schema::hasColumn('notifications', 'route_name')) {
                $table->string('route_name')->nullable()->after('url');
            }
            if (!Schema::hasColumn('notifications', 'route_params')) {
                $table->json('route_params')->nullable()->after('route_name');
            }

            if (!Schema::hasColumn('notifications', 'is_read')) {
                $table->boolean('is_read')->default(false)->after('route_params');
            }
            if (!Schema::hasColumn('notifications', 'is_important')) {
                $table->boolean('is_important')->default(false)->after('is_read');
            }
            if (!Schema::hasColumn('notifications', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_important');
            }
            if (!Schema::hasColumn('notifications', 'seen_at')) {
                $table->timestamp('seen_at')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('notifications', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('seen_at');
            }

            if (!Schema::hasColumn('notifications', 'channel')) {
                $table->string('channel')->default('header')->after('read_at');
            }
            if (!Schema::hasColumn('notifications', 'priority')) {
                $table->tinyInteger('priority')->default(1)->after('channel');
            }
            if (!Schema::hasColumn('notifications', 'action_required')) {
                $table->boolean('action_required')->default(false)->after('priority');
            }

            if (!Schema::hasColumn('notifications', 'sender_id')) {
                $table->unsignedBigInteger('sender_id')->nullable()->after('action_required');
            }
            if (!Schema::hasColumn('notifications', 'context')) {
                $table->json('context')->nullable()->after('sender_id');
            }
            if (!Schema::hasColumn('notifications', 'tags')) {
                $table->json('tags')->nullable()->after('context');
            }
            if (!Schema::hasColumn('notifications', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('updated_at');
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'target_user_id')) {
                $table->foreign('target_user_id')->references('id')->on('users')->onDelete('set null');
            }

            if (Schema::hasColumn('notifications', 'sender_id')) {
                $table->foreign('sender_id')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('notifications')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'target_user_id')) {
                $table->dropForeign(['target_user_id']);
            }
            if (Schema::hasColumn('notifications', 'sender_id')) {
                $table->dropForeign(['sender_id']);
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            $columns = [
                'target_user_id',
                'target_roles',
                'reference_id',
                'title',
                'message',
                'icon',
                'url',
                'route_name',
                'route_params',
                'is_read',
                'is_important',
                'is_active',
                'seen_at',
                'read_at',
                'channel',
                'priority',
                'action_required',
                'sender_id',
                'context',
                'tags',
                'expires_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('notifications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
