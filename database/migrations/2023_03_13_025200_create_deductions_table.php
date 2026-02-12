<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deductions', function (Blueprint $table) {
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

        DB::table('deductions')->insert([
            [
                'name' => 'SSS BENEFITS',
                'code' => 'SSS',
                'description' => 'SSS Benefits',
                'multiplier' => 1,
                'type' => 'deductions',
                'taxable' => 1,
                'status' => 1,
                'workstation_id' => 1,
                'created_by' => 1,
                'updated_by' => 1
            ],
            [
                'name' => 'PHILHEALTH BENEFITS',
                'code' => 'PHILHEALTH',
                'description' => 'Philhealth Benefits',
                'multiplier' => 1,
                'type' => 'deductions',
                'taxable' => 1,
                'status' => 1,
                'workstation_id' => 1,
                'created_by' => 1,
                'updated_by' => 1
            ],
            [
                'name' => 'PAG-IBIG BENEFITS',
                'code' => 'PAG-IBIG',
                'description' => 'Pag-ibig Benefits',
                'multiplier' => 1,
                'type' => 'deductions',
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
        Schema::dropIfExists('deductions');
    }
}
