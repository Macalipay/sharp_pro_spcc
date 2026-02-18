<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountingFinancialSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('accounting_financial_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('workstation_id')->nullable();

            $table->string('currency_base_code')->default('PHP');
            $table->string('currency_base_name')->default('Philippine Peso');
            $table->boolean('currency_is_base_currency')->default(1);

            $table->unsignedTinyInteger('financial_year_end_month')->default(12);
            $table->unsignedTinyInteger('financial_year_end_day')->default(31);
            $table->string('financial_year_end_label')->default('31 December');

            $table->string('sales_tax_tax_basis')->nullable();
            $table->string('sales_tax_tax_id_number')->nullable();
            $table->string('sales_tax_tax_id_display_name')->nullable();
            $table->string('sales_tax_tax_period')->nullable();

            $table->string('tax_defaults_sales_pricing')->default('TAX_EXCLUSIVE');
            $table->string('tax_defaults_purchases_pricing')->default('TAX_EXCLUSIVE');

            $table->boolean('lock_dates_enabled')->default(0);
            $table->date('lock_dates_lock_date')->nullable();

            $table->string('timezone_iana')->default('Asia/Singapore');
            $table->string('timezone_display')->default('(UTC+08:00) Kuala Lumpur, Singapore');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('accounting_financial_settings');
    }
}

