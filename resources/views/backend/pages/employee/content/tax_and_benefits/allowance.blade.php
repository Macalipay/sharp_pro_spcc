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
        <div class="col-md-2 d-flex align-items-end">
            <button type="button" id="add_fixed_allowance_btn" class="btn btn-sm btn-primary btn-block mb-2">ADD FIXED</button>
        </div>
    </div>

    <div class="table-responsive mt-2">
        <table class="table table-sm table-bordered mb-0">
            <thead>
                <tr>
                    <th style="width: 55%;">TYPE OF ALLOWANCE</th>
                    <th style="width: 30%;" class="text-right">AMOUNT (PER MONTH)</th>
                    <th style="width: 15%;" class="text-center">ACTION</th>
                </tr>
            </thead>
            <tbody id="fixedAllowanceBody">
                <tr>
                    <td colspan="3" class="text-center">No Data</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
