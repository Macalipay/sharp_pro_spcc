<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttachmentToSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('2303')->nullable();
            $table->string('business_registration')->nullable();
            $table->string('sample_invoice')->nullable();
            $table->string('nda')->nullable();
            $table->string('vaf')->nullable();
            $table->string('business_permit')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('2303');
            $table->dropColumn('business_registration');
            $table->dropColumn('sample_invoice');
            $table->dropColumn('nda');
            $table->dropColumn('vaf');
            $table->dropColumn('business_permit');
            
        });
    }
}
