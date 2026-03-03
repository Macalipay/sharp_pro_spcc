<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentTermTemplatesTable extends Migration
{
    public function up()
    {
        Schema::create('payment_term_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('term_text');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('workstation_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_term_templates');
    }
}
