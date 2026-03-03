<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaterialsRequisitionFormsTable extends Migration
{
    public function up()
    {
        Schema::create('materials_requisition_forms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date')->nullable();
            $table->string('mrf_no')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('location')->nullable();
            $table->decimal('quantity', 14, 2)->nullable();
            $table->string('unit')->nullable();
            $table->text('particulars')->nullable();
            $table->string('location_to_be_used')->nullable();
            $table->date('date_required')->nullable();
            $table->decimal('approved_quantity', 14, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->unsignedBigInteger('noted_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('workstation_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('materials_requisition_forms');
    }
}

