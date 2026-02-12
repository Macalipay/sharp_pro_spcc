<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDetailToInventoryTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            // $table->string('code')->nullable();
            // $table->unsignedBigInteger('requested_by')->nullable();
            // $table->unsignedBigInteger('issued_by')->nullable();
            // $table->unsignedBigInteger('approved_by')->nullable();

            // $table->foreign('requested_by')
            //     ->references('id')
            //     ->on('employments');

            // $table->foreign('issued_by')
            //     ->references('id')
            //     ->on('employments');

            // $table->foreign('approved_by')
            //     ->references('id')
            //     ->on('employments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn(['code', 'requested_by', 'issued_by', 'approved_by']);
        });
    }
}
