<?php

namespace App\Http\Controllers;

use App\AccountingFinancialSetting;
use Illuminate\Http\Request;
use Auth;

class AccountingFinancialSettingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $workstationId = $user ? $user->workstation_id : null;

        $defaults = [
            'currency_base_code' => 'PHP',
            'currency_base_name' => 'Philippine Peso',
            'currency_is_base_currency' => 1,
            'financial_year_end_month' => 12,
            'financial_year_end_day' => 31,
            'financial_year_end_label' => '31 December',
            'sales_tax_tax_basis' => null,
            'sales_tax_tax_id_number' => null,
            'sales_tax_tax_id_display_name' => null,
            'sales_tax_tax_period' => null,
            'tax_defaults_sales_pricing' => 'TAX_EXCLUSIVE',
            'tax_defaults_purchases_pricing' => 'TAX_EXCLUSIVE',
            'lock_dates_enabled' => 0,
            'lock_dates_lock_date' => null,
            'timezone_iana' => 'Asia/Singapore',
            'timezone_display' => '(UTC+08:00) Kuala Lumpur, Singapore',
            'workstation_id' => $workstationId,
            'created_by' => $user ? $user->id : null,
            'updated_by' => $user ? $user->id : null,
        ];

        $query = AccountingFinancialSetting::query();
        if ($workstationId) {
            $query->where('workstation_id', $workstationId);
        }
        $settings = $query->first();

        if (!$settings) {
            $settings = AccountingFinancialSetting::create($defaults);
        }

        return view('backend.pages.accounting.settings.financial', compact('settings'), ["type" => "full-view"]);
    }

    public function save(Request $request)
    {
        $request->validate([
            'currency_base_code' => ['required', 'string', 'max:10'],
            'financial_year_end_month' => ['required', 'integer', 'between:1,12'],
            'financial_year_end_day' => ['required', 'integer', 'between:1,31'],
            'tax_defaults_sales_pricing' => ['required', 'in:TAX_EXCLUSIVE,TAX_INCLUSIVE'],
            'tax_defaults_purchases_pricing' => ['required', 'in:TAX_EXCLUSIVE,TAX_INCLUSIVE'],
            'timezone_iana' => ['required', 'string', 'max:100'],
            'lock_dates_lock_date' => ['nullable', 'date'],
        ]);

        $user = Auth::user();
        $workstationId = $user ? $user->workstation_id : null;

        $query = AccountingFinancialSetting::query();
        if ($workstationId) {
            $query->where('workstation_id', $workstationId);
        }
        $settings = $query->first();
        if (!$settings) {
            $settings = new AccountingFinancialSetting();
            $settings->workstation_id = $workstationId;
            $settings->created_by = $user ? $user->id : null;
        }

        $month = (int) $request->financial_year_end_month;
        $day = (int) $request->financial_year_end_day;
        $monthName = date('F', mktime(0, 0, 0, $month, 1, 2000));

        $settings->currency_base_code = strtoupper((string) $request->currency_base_code);
        $settings->currency_base_name = (string) ($request->currency_base_name ?: 'Philippine Peso');
        $settings->currency_is_base_currency = $request->has('currency_is_base_currency') ? 1 : 0;

        $settings->financial_year_end_month = $month;
        $settings->financial_year_end_day = $day;
        $settings->financial_year_end_label = trim((string) $day . ' ' . $monthName);

        $settings->sales_tax_tax_basis = $request->sales_tax_tax_basis ?: null;
        $settings->sales_tax_tax_id_number = $request->sales_tax_tax_id_number ?: null;
        $settings->sales_tax_tax_id_display_name = $request->sales_tax_tax_id_display_name ?: null;
        $settings->sales_tax_tax_period = $request->sales_tax_tax_period ?: null;

        $settings->tax_defaults_sales_pricing = $request->tax_defaults_sales_pricing;
        $settings->tax_defaults_purchases_pricing = $request->tax_defaults_purchases_pricing;

        $settings->lock_dates_enabled = $request->has('lock_dates_enabled') ? 1 : 0;
        $settings->lock_dates_lock_date = $settings->lock_dates_enabled ? ($request->lock_dates_lock_date ?: null) : null;

        $settings->timezone_iana = (string) $request->timezone_iana;
        $settings->timezone_display = (string) ($request->timezone_display ?: '(UTC+08:00) Kuala Lumpur, Singapore');
        $settings->updated_by = $user ? $user->id : null;

        $settings->save();

        return redirect()->back()->with('success', 'Financial settings saved successfully.');
    }
}

