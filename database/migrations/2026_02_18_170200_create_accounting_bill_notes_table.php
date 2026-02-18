<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountingBillNotesTable extends Migration
{
    public function up()
    {
        Schema::create('accounting_bill_notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('accounting_bill_id');
            $table->text('note');
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamp('added_at')->nullable();
            $table->timestamps();

            $table->foreign('accounting_bill_id')->references('id')->on('accounting_bills');
            $table->foreign('added_by')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('accounting_bill_notes');
    }
}

