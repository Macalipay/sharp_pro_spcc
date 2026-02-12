<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDrSequenceToDeliveryReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_receipts', function (Blueprint $table) {
            $table->string('dr_sequence')->nullable()->after('sent_quantity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_receipts', function (Blueprint $table) {
            $table->dropColumn('dr_sequence');
        });
    }
}
