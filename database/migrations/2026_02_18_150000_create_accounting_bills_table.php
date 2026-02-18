<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountingBillsTable extends Migration
{
    public function up()
    {
        Schema::create('accounting_bills', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('bill_no')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->date('bill_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('description')->nullable();
            $table->string('status')->default('DRAFT');
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->unsignedBigInteger('accounts_payable_account_id')->nullable();
            $table->unsignedBigInteger('payment_account_id')->nullable();
            $table->unsignedBigInteger('recognition_journal_entry_id')->nullable();
            $table->unsignedBigInteger('payment_journal_entry_id')->nullable();

            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('rejected_reason')->nullable();
            $table->unsignedBigInteger('paid_by')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference')->nullable();

            $table->unsignedBigInteger('workstation_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('suppliers');
            $table->foreign('accounts_payable_account_id')->references('id')->on('chart_of_accounts');
            $table->foreign('payment_account_id')->references('id')->on('chart_of_accounts');
            $table->foreign('recognition_journal_entry_id')->references('id')->on('journal_entries');
            $table->foreign('payment_journal_entry_id')->references('id')->on('journal_entries');
            $table->foreign('submitted_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->foreign('rejected_by')->references('id')->on('users');
            $table->foreign('paid_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('accounting_bills');
    }
}

