
<div id="compensation_tab" class="form-tab">
    <h5>COMPENSATION, TAXES AND BENEFITS TAB</h5>
    <br>
    <div class="row">
        <div class="col-12">
            <h4>COMPENSATION AND TAXES</h4>
            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label>ANNUAL SALARY</label>
                        <input type="number" class="form-control" id="annual_salary" name="annual_salary" placeholder="AMOUNT" value="0" onblur="scion.get.salary(this.value, 'annual', salary)" disabled>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label>MONTHLY SALARY</label>
                        <input type="number" class="form-control" id="monthly_salary" name="monthly_salary" placeholder="AMOUNT" value="0" onblur="scion.get.salary(this.value, 'monthly', salary)">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label>DAILY SALARY</label>
                        <input type="number" class="form-control" id="daily_salary" name="daily_salary" placeholder="AMOUNT" value="0" onblur="scion.get.salary(this.value, 'daily', salary)">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label>SEMI-MONTHLY SALARY</label>
                        <input type="number" class="form-control" id="semi_monthly_salary" name="semi_monthly_salary" placeholder="AMOUNT" value="0" onblur="scion.get.salary(this.value, 'semi_monthly', salary)" disabled>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label>WEEKLY SALARY</label>
                        <input type="number" class="form-control" id="weekly_salary" name="weekly_salary" placeholder="AMOUNT" value="0" onblur="scion.get.salary(this.value, 'weekly', salary)" disabled>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label>HOURLY SALARY</label>
                        <input type="number" class="form-control" id="hourly_salary" name="hourly_salary" placeholder="AMOUNT" value="0" onblur="scion.get.salary(this.value, 'hourly', salary)" disabled>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label>SSS</label>
                        <input type="number" class="form-control" id="sss" name="sss" placeholder="AMOUNT" value="0">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label>PHILHEALTH</label>
                        <input type="number" class="form-control" id="phic" name="phic" placeholder="AMOUNT" value="0">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label>PAG-IBIG</label>
                        <input type="number" class="form-control" id="pagibig" name="pagibig" placeholder="AMOUNT" value="0">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label>TAX</label>
                        <input type="number" class="form-control" id="tax" name="tax" placeholder="AMOUNT" value="0">
                    </div>
                </div>
            </div>
            <hr>
        </div>
        <div class="col-12">
            <h4>ALLOWANCE TAGGING</h4>
            <div class="row">
                <div class="col-md-12 allowance-available">
                </div>
                <div class="col-md-12">
                    <button class="btn btn-sm btn-primary" onclick="addAllowance()" type="button">UPDATE ALLOWANCE</button>
                </div>
            </div>
            <hr>
        </div>
        <div class="col-12">
            <h4>PROJECT TAGGING</h4>
            <div class="row">
                <div class="col-md-12 project-available">
                </div>
                <div class="col-md-12">
                    <button class="btn btn-sm btn-primary" onclick="addProject()" type="button">UPDATE PROJECTS</button>
                </div>
            </div>
            <hr>
        </div>
        {{-- <div class="col-12">
            <h4>GOVERNMENT MANDATED BENEFITS</h4>
            <div class="row">
                <div class="col-3">
                    <select name="government_mandated_benefits" id="government_mandated_benefits" class="form-control"></select>
                </div>
                <div class="col-3">
                    <input type="number" id="government_mandated_benefits_amount" name="government_mandated_benefits_amount" class="form-control" placeholder="AMOUNT">
                    <br>
                </div>
                <div class="col-12">
                    <table id="government_mandated_benefits_table" class="table table-striped" style="width:100%"></table>
                </div>
            </div>
            <hr>
        </div>
        <div class="col-12">
            <h4>OTHER COMPANY BENEFITS</h4>
            <div class="row">
                <div class="col-3">
                    <select name="other_company_benefits" id="other_company_benefits" class="form-control"></select>
                </div>
                <div class="col-3">
                    <input type="number" id="other_company_benefits_amount" name="other_company_benefits_amount" class="form-control" placeholder="AMOUNT">
                    <br>
                </div>
                <div class="col-12">
                    <table id="other_company_benefits_amount_table" class="table table-striped" style="width:100%"></table>
                </div>
            </div>
            <hr>
        </div> --}}
    </div>
</div>