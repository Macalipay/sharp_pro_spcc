<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccountingFinancialSetting extends Model
{
    protected $table = 'accounting_financial_settings';

    protected $fillable = [
        'workstation_id',
        'currency_base_code',
        'currency_base_name',
        'currency_is_base_currency',
        'financial_year_end_month',
        'financial_year_end_day',
        'financial_year_end_label',
        'sales_tax_tax_basis',
        'sales_tax_tax_id_number',
        'sales_tax_tax_id_display_name',
        'sales_tax_tax_period',
        'tax_defaults_sales_pricing',
        'tax_defaults_purchases_pricing',
        'lock_dates_enabled',
        'lock_dates_lock_date',
        'timezone_iana',
        'timezone_display',
        'created_by',
        'updated_by',
    ];
}

