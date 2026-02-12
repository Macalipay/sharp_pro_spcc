<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEarningsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('earnings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('code');
            $table->string('description')->nullable();
            $table->double('multiplier');
            $table->string('type');
            $table->integer('taxable');
            $table->integer('status');
            $table->integer('chart_id')->nullable();
            $table->unsignedBigInteger('workstation_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('workstation_id')
                ->references('id')
                ->on('workstations');

            $table->foreign('created_by')
                ->references('id')
                ->on('users');

            $table->foreign('updated_by')
                ->references('id')
                ->on('users');
        });


        DB::table('earnings')->insert([
            [
                'name' => 'REGULAR EARNINGS',
                'code' => 'RE',
                'description' => 'Regular Earnings',
                'multiplier' => 1,
                'type' => 1,
                'taxable' => 1,
                'status' => 1,
                'workstation_id' => 1,
                'created_by' => 1,
                'updated_by' => 1
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('earnings');
    }
}
