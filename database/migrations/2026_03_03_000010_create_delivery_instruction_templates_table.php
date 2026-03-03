<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryInstructionTemplatesTable extends Migration
{
    public function up()
    {
        Schema::create('delivery_instruction_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('template_text');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('workstation_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_instruction_templates');
    }
}
