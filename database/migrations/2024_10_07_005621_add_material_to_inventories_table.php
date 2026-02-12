<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMaterialToInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn(['code', 'name', 'default_unit']);
            $table->unsignedBigInteger('material_id')->nullable();
            $table->unsignedBigInteger('po_id')->nullable();

            $table->foreign('material_id')
                  ->references('id')
                  ->on('materials');

            $table->foreign('po_id')
                  ->references('id')
                  ->on('purchase_orders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventories', function (Blueprint $table) {
            //
        });
    }
}
