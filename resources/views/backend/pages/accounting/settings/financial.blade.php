@extends('backend.master.index')

@section('title', 'FINANCIAL SETTINGS')

@section('breadcrumbs')
    <span>ACCOUNTING</span> / <span class="highlight">FINANCIAL SETTINGS</span>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Financial Settings</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/accounting/financial_settings/save">
                    @csrf

                    <h6 class="mb-2">Currency</h6>
                    <div class="row">
                        <div class="form-group col-md-3">
                            <label>Base Code</label>
                            <input type="text" class="form-control" name="currency_base_code" value="{{ old('currency_base_code', $settings->currency_base_code) }}" required>
                        </div>
                        <div class="form-group col-md-5">
                            <label>Base Name</label>
                            <input type="text" class="form-control" name="currency_base_name" value="{{ old('currency_base_name', $settings->currency_base_name) }}">
                        </div>
                        <div class="form-group col-md-4 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" id="currency_is_base_currency" name="currency_is_base_currency" value="1" {{ old('currency_is_base_currency', $settings->currency_is_base_currency) ? 'checked' : '' }}>
                                <label class="form-check-label" for="currency_is_base_currency">Is Base Currency</label>
                            </div>
                        </div>
                    </div>

                    <h6 class="mb-2 mt-2">Financial Year</h6>
                    <div class="row">
                        <div class="form-group col-md-3">
                            <label>End Month</label>
                            <input type="number" min="1" max="12" class="form-control" name="financial_year_end_month" value="{{ old('financial_year_end_month', $settings->financial_year_end_month) }}" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label>End Day</label>
                            <input type="number" min="1" max="31" class="form-control" name="financial_year_end_day" value="{{ old('financial_year_end_day', $settings->financial_year_end_day) }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>End Label</label>
                            <input type="text" class="form-control" name="financial_year_end_label" value="{{ old('financial_year_end_label', $settings->financial_year_end_label) }}" readonly>
                        </div>
                    </div>

                    <h6 class="mb-2 mt-2">Sales Tax</h6>
                    <div class="row">
                        <div class="form-group col-md-3">
                            <label>Tax Basis</label>
                            <input type="text" class="form-control" name="sales_tax_tax_basis" value="{{ old('sales_tax_tax_basis', $settings->sales_tax_tax_basis) }}" placeholder="(leave blank)">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Tax ID Number</label>
                            <input type="text" class="form-control" name="sales_tax_tax_id_number" value="{{ old('sales_tax_tax_id_number', $settings->sales_tax_tax_id_number) }}" placeholder="(blank)">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Tax ID Display Name</label>
                            <input type="text" class="form-control" name="sales_tax_tax_id_display_name" value="{{ old('sales_tax_tax_id_display_name', $settings->sales_tax_tax_id_display_name) }}" placeholder="(blank)">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Tax Period</label>
                            <input type="text" class="form-control" name="sales_tax_tax_period" value="{{ old('sales_tax_tax_period', $settings->sales_tax_tax_period) }}" placeholder="(leave blank)">
                        </div>
                    </div>

                    <h6 class="mb-2 mt-2">Tax Defaults</h6>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Sales Pricing</label>
                            <select class="form-control" name="tax_defaults_sales_pricing" required>
                                <option value="TAX_EXCLUSIVE" {{ old('tax_defaults_sales_pricing', $settings->tax_defaults_sales_pricing) === 'TAX_EXCLUSIVE' ? 'selected' : '' }}>TAX_EXCLUSIVE</option>
                                <option value="TAX_INCLUSIVE" {{ old('tax_defaults_sales_pricing', $settings->tax_defaults_sales_pricing) === 'TAX_INCLUSIVE' ? 'selected' : '' }}>TAX_INCLUSIVE</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Purchases Pricing</label>
                            <select class="form-control" name="tax_defaults_purchases_pricing" required>
                                <option value="TAX_EXCLUSIVE" {{ old('tax_defaults_purchases_pricing', $settings->tax_defaults_purchases_pricing) === 'TAX_EXCLUSIVE' ? 'selected' : '' }}>TAX_EXCLUSIVE</option>
                                <option value="TAX_INCLUSIVE" {{ old('tax_defaults_purchases_pricing', $settings->tax_defaults_purchases_pricing) === 'TAX_INCLUSIVE' ? 'selected' : '' }}>TAX_INCLUSIVE</option>
                            </select>
                        </div>
                    </div>

                    <h6 class="mb-2 mt-2">Lock Dates</h6>
                    <div class="row">
                        <div class="form-group col-md-4 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" id="lock_dates_enabled" name="lock_dates_enabled" value="1" {{ old('lock_dates_enabled', $settings->lock_dates_enabled) ? 'checked' : '' }}>
                                <label class="form-check-label" for="lock_dates_enabled">Enable Lock Dates</label>
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Lock Date</label>
                            <input type="date" class="form-control" name="lock_dates_lock_date" value="{{ old('lock_dates_lock_date', $settings->lock_dates_lock_date) }}">
                        </div>
                    </div>

                    <h6 class="mb-2 mt-2">Time Zone</h6>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>IANA</label>
                            <input type="text" class="form-control" name="timezone_iana" value="{{ old('timezone_iana', $settings->timezone_iana) }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Display</label>
                            <input type="text" class="form-control" name="timezone_display" value="{{ old('timezone_display', $settings->timezone_display) }}">
                        </div>
                    </div>

                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary">Save Financial Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

