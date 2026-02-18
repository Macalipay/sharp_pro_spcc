<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountingBillHistoriesTable extends Migration
{
    public function up()
    {
        Schema::create('accounting_bill_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('accounting_bill_id');
            $table->string('action');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->timestamp('performed_at')->nullable();
            $table->timestamps();

            $table->foreign('accounting_bill_id')->references('id')->on('accounting_bills');
            $table->foreign('performed_by')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('accounting_bill_histories');
    }
}

