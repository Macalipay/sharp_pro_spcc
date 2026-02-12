<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoryTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_transfers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('inventory_id');
            $table->unsignedBigInteger('from_project');
            $table->unsignedBigInteger('to_project');
            $table->string('quantity');
            $table->string('date');
            $table->string('remarks');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->string('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('from_project')
                ->references('id')
                ->on('projects');

            $table->foreign('to_project')
                ->references('id')
                ->on('projects');

            $table->foreign('inventory_id')
                ->references('id')
                ->on('inventories');

            $table->foreign('created_by')
                ->references('id')
                ->on('users');

            $table->foreign('updated_by')
                ->references('id')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_transfers');
    }
}
