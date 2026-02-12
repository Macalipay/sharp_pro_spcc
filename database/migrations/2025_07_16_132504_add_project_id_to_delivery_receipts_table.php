<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProjectIdToDeliveryReceiptsTable extends Migration
{
    public function up()
    {
        Schema::table('delivery_receipts', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable()->after('purchase_order_id');
        });
    }

    public function down()
    {
        Schema::table('delivery_receipts', function (Blueprint $table) {
            $table->dropColumn('project_id');
        });
    }
} 