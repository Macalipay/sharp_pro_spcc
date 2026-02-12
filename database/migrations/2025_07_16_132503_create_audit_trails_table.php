<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditTrailsTable extends Migration
{
    public function up()
    {
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('purchase_order_detail_id');
            $table->unsignedBigInteger('item_id');
            $table->string('sent_quantity');
            $table->string('dr_sequence')->nullable();
            $table->unsignedBigInteger('dr_id');
            $table->string('amount');
            $table->string('remark');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_trails');
    }
} 