<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaterialsRequisitionFormDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('materials_requisition_form_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('materials_requisition_form_id');
            $table->decimal('quantity', 14, 2)->nullable();
            $table->string('unit')->nullable();
            $table->text('particulars')->nullable();
            $table->string('location_to_be_used')->nullable();
            $table->date('date_required')->nullable();
            $table->decimal('approved_quantity', 14, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('workstation_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('materials_requisition_form_id', 'mrf_detail_header_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('materials_requisition_form_details');
    }
}

