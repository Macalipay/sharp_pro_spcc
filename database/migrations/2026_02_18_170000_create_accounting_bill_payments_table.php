<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountingBillPaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('accounting_bill_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('accounting_bill_id');
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('payment_date');
            $table->unsignedBigInteger('payment_account_id');
            $table->string('payment_reference')->nullable();
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('accounting_bill_id')->references('id')->on('accounting_bills');
            $table->foreign('payment_account_id')->references('id')->on('chart_of_accounts');
            $table->foreign('journal_entry_id')->references('id')->on('journal_entries');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('accounting_bill_payments');
    }
}

