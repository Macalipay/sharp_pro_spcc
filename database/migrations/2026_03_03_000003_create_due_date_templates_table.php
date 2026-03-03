<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDueDateTemplatesTable extends Migration
{
    public function up()
    {
        Schema::create('due_date_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('template_text');
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
        Schema::dropIfExists('due_date_templates');
    }
}

