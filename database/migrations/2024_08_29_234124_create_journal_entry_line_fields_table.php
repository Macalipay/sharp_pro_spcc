<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJournalEntryLineFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('journal_entry_line_fields', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('journal_entry_id');
            $table->unsignedBigInteger('chart_of_account_id');
            $table->string('data_type')->nullable();
            $table->string('data_id')->nullable();
            $table->string('tax_rate')->nullable();
            $table->string('region')->nullable();
            $table->string('description')->nullable();
            $table->string('debit_amount')->default(0);
            $table->string('credit_amount')->default(0);
            $table->string('department_cost_center')->nullable();
            $table->string('project_code')->nullable();
            $table->unsignedBigInteger('workstation_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('journal_entry_id')
                  ->references('id')
                  ->on('journal_entries');

            $table->foreign('chart_of_account_id')
                  ->references('id')
                  ->on('chart_of_accounts');

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
        Schema::dropIfExists('journal_entry_line_fields');
    }
}
