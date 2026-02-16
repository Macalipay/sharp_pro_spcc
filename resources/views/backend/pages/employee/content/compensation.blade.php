<div id="compensationScreen" class="content-employee">
    <h5>COMPENSATION, TAXES AND BENEFITS</h5>
    <br>
    <div class="row">
        <div class="col-12">
             @csrf
            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label>ANNUAL SALARY</label>
                        <input type="text" class="form-control" id="annual_salary" name="annual_salary" placeholder="AMOUNT" value="₱0.00" onblur="scion.get.salary(parseCurrencyToNumber(this.value), 'annual', salary)" disabled>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label>MONTHLY SALARY</label>
                        <input type="text" class="form-control" id="monthly_salary" name="monthly_salary" placeholder="AMOUNT" value="₱0.00" onblur="scion.get.salary(parseCurrencyToNumber(this.value), 'monthly', salary)">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label>DAILY SALARY</label>
                        <input type="text" class="form-control" id="daily_salary" name="daily_salary" placeholder="AMOUNT" value="₱0.00" onblur="scion.get.salary(parseCurrencyToNumber(this.value), 'daily', salary)">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label>SEMI-MONTHLY SALARY</label>
                        <input type="text" class="form-control" id="semi_monthly_salary" name="semi_monthly_salary" placeholder="AMOUNT" value="₱0.00" onblur="scion.get.salary(parseCurrencyToNumber(this.value), 'semi_monthly', salary)" disabled>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label>WEEKLY SALARY</label>
                        <input type="text" class="form-control" id="weekly_salary" name="weekly_salary" placeholder="AMOUNT" value="₱0.00" onblur="scion.get.salary(parseCurrencyToNumber(this.value), 'weekly', salary)" disabled>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label>HOURLY SALARY</label>
                        <input type="text" class="form-control" id="hourly_salary" name="hourly_salary" placeholder="AMOUNT" value="₱0.00" onblur="scion.get.salary(parseCurrencyToNumber(this.value), 'hourly', salary)" disabled>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label>SSS</label>
                        <input type="text" class="form-control" id="sss" name="sss" placeholder="AMOUNT" value="0" readonly>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label>PHILHEALTH</label>
                        <input type="text" class="form-control" id="phic" name="phic" placeholder="AMOUNT" value="0" readonly>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label>PAG-IBIG</label>
                        <input type="text" class="form-control" id="pagibig" name="pagibig" placeholder="AMOUNT" value="0" readonly>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label>TAX</label>
                        <input type="text" class="form-control" id="tax" name="tax" placeholder="AMOUNT" value="0" readonly>
                    </div>
                </div>
            </div>
            <hr>
    </div>
    </div>
</div>
