<div id="allowanceSubScreen" class="content-sub-screen">
    <h5>ALLOWANCE</h5>
    <div class="row">
        <div class="col-md-5">
            <div class="form-group">
                <label>TYPE OF ALLOWANCE</label>
                <select id="allowance_type" class="form-control form-control-sm">
                    <option value="">PLEASE SELECT ALLOWANCE</option>
                    @foreach ($allowance as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-5">
            <div class="form-group">
                <label>AMOUNT (PER MONTH)</label>
                <input type="text" id="allowance_monthly_amount" class="form-control form-control-sm" placeholder="₱0.00">
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group allowance-auto-reflect-group">
                <label class="allowance-auto-reflect-toggle">
                    <input type="checkbox" id="allowance_auto_reflect_in_payroll" checked>
                    <span>Auto reflect in payroll</span>
                </label>
                <button type="button" id="add_fixed_allowance_btn" class="btn btn-sm btn-primary btn-block mt-2">ADD FIXED</button>
            </div>
        </div>
    </div>

    <div class="table-responsive mt-2">
        <table class="table table-sm table-bordered mb-0">
            <thead>
                <tr>
                    <th style="width: 8%;" class="text-center">AUTO</th>
                    <th style="width: 20%;">TYPE OF ALLOWANCE</th>
                    <th style="width: 14%;" class="text-right">ENCODED AMOUNT</th>
                    <th style="width: 14%;">PAYROLL BASIS</th>
                    <th style="width: 12%;" class="text-center">PRESENT DAYS</th>
                    <th style="width: 20%;">REFLECTED IN PAYROLL</th>
                    <th style="width: 12%;" class="text-center">ACTION</th>
                </tr>
            </thead>
            <tbody id="fixedAllowanceBody">
                <tr>
                    <td colspan="7" class="text-center">No Data</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="allowance-payroll-note mt-2">
        “Reflected in Payroll” means the computed amount that will be included in the payroll period based on payroll group and approved present days. Outside a payroll period, this column shows the expected formula preview.
    </div>
</div>
