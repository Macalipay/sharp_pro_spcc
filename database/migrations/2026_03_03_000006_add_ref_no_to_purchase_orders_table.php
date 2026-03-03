<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRefNoToPurchaseOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'ref_no')) {
                $table->unsignedBigInteger('ref_no')->nullable()->after('supplier_id');
                $table->foreign('ref_no')
                    ->references('id')
                    ->on('materials_requisition_forms')
                    ->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'ref_no')) {
                $table->dropForeign(['ref_no']);
                $table->dropColumn('ref_no');
            }
        });
    }
}

