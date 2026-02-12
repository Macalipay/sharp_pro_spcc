<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOwnerSuppliedMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('owner_supplied_materials', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('material_id');
            $table->unsignedBigInteger('project_id');
            $table->string('description')->nullable();
            $table->integer('total_count');
            $table->integer('quantity_stock');
            $table->string('status')->default('active');
            $table->unsignedBigInteger('workstation_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('material_id')
                    ->references('id')
                    ->on('materials');

            $table->foreign('workstation_id')
                    ->references('id')
                    ->on('workstations');

            $table->foreign('project_id')
                    ->references('id')
                    ->on('projects');

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
        Schema::dropIfExists('owner_supplied_materials');
    }
}
