<div id="deductionsScreen" class="content-employee">
    <div class="employee-deductions-screen">
        <h5>DEDUCTIONS</h5>

        <div class="row employee-deduction-summary g-3">
            <div class="col-xl-3 col-md-6">
                <div class="employee-deduction-card">
                    <span class="employee-deduction-card-label">ACTIVE DEDUCTIONS</span>
                    <strong id="employeeDeductionActiveCount">0</strong>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="employee-deduction-card">
                    <span class="employee-deduction-card-label">TOTAL DEDUCTION BALANCE</span>
                    <strong id="employeeDeductionTotalBalance">₱0.00</strong>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="employee-deduction-card">
                    <span class="employee-deduction-card-label">TOTAL PAID</span>
                    <strong id="employeeDeductionTotalPaid">₱0.00</strong>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="employee-deduction-card">
                    <span class="employee-deduction-card-label">UPCOMING AUTO DEDUCTION</span>
                    <strong id="employeeDeductionUpcoming">₱0.00</strong>
                </div>
            </div>
        </div>

        <div class="employee-deduction-panel mt-3">
            <div class="employee-deduction-panel-header">
                <h6>Deduction Entry</h6>
                <div class="employee-deduction-form-actions">
                    <button type="button" id="reset_employee_deduction_btn" class="btn btn-sm btn-light">Reset</button>
                    <button type="button" id="save_employee_deduction_btn" class="btn btn-sm btn-primary">Add Deduction</button>
                </div>
            </div>

            <input type="hidden" id="employee_deduction_id">

            <div class="row g-3">
                <div class="col-xl-3 col-md-6">
                    <label for="deduction_type_id">Deduction Type</label>
                    <select id="deduction_type_id" class="form-control form-control-sm">
                        <option value="">PLEASE SELECT DEDUCTION</option>
                        @foreach ($deductions as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-3 col-md-6">
                    <label for="deduction_reference_name">Deduction Name / Reference</label>
                    <input type="text" id="deduction_reference_name" class="form-control form-control-sm" placeholder="Reference or note">
                </div>
                <div class="col-xl-3 col-md-6">
                    <label for="deduction_total_amount">Total Deduction Amount</label>
                    <input type="text" id="deduction_total_amount" class="form-control form-control-sm" placeholder="₱0.00">
                </div>
                <div class="col-xl-3 col-md-6">
                    <label for="deduction_frequency">Deduction Frequency</label>
                    <select id="deduction_frequency" class="form-control form-control-sm">
                        <option value="every_payroll">Every Payroll</option>
                        <option value="semi_monthly">Semi-Monthly Payroll Only</option>
                        <option value="weekly">Weekly Payroll Only</option>
                        <option value="monthly">Monthly Payroll Only</option>
                        <option value="one_time">One-Time</option>
                    </select>
                </div>
                <div class="col-xl-3 col-md-6">
                    <label for="deduction_payment_terms">Payment Terms</label>
                    <input type="number" min="1" id="deduction_payment_terms" class="form-control form-control-sm" placeholder="No. of payroll terms">
                </div>
                <div class="col-xl-3 col-md-6">
                    <label for="deduction_per_payroll">Deduction Per Payroll</label>
                    <input type="text" id="deduction_per_payroll" class="form-control form-control-sm" placeholder="₱0.00" readonly>
                </div>
                <div class="col-xl-3 col-md-6">
                    <label for="deduction_effective_start_payroll">Effective Start Payroll</label>
                    <input type="date" id="deduction_effective_start_payroll" class="form-control form-control-sm">
                </div>
                <div class="col-xl-3 col-md-6">
                    <label for="deduction_end_date">End Date</label>
                    <input type="date" id="deduction_end_date" class="form-control form-control-sm">
                </div>
                <div class="col-12">
                    <label for="deduction_description">Description / Notes</label>
                    <textarea id="deduction_description" rows="2" class="form-control form-control-sm" placeholder="Notes or payroll deduction instructions"></textarea>
                </div>
                <div class="col-xl-3 col-md-6">
                    <label for="deduction_status">Status</label>
                    <select id="deduction_status" class="form-control form-control-sm">
                        <option value="active">Active</option>
                        <option value="paused">Paused</option>
                        <option value="draft">Draft</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="col-xl-9 col-md-6">
                    <label>Options</label>
                    <div class="employee-deduction-options">
                        <label><input type="checkbox" id="deduction_auto_deduct" checked> Auto Deduct in Every Payroll</label>
                        <label><input type="checkbox" id="deduction_stop_when_paid" checked> Stop when fully paid</label>
                        <label><input type="checkbox" id="deduction_allow_override"> Allow manual override</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="employee-deduction-panel mt-3">
            <div class="employee-deduction-panel-header">
                <h6>Deduction Summary</h6>
                <span class="employee-deduction-note">This section tracks the deduction amount configured for payroll and the remaining balance per employee.</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>Deduction Type</th>
                            <th>Reference</th>
                            <th class="text-right">Total Amount</th>
                            <th class="text-right">Per Payroll Amount</th>
                            <th class="text-center">Terms</th>
                            <th class="text-right">Total Paid</th>
                            <th class="text-right">Remaining Balance</th>
                            <th class="text-center">Auto Deduct</th>
                            <th>Frequency</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="employeeDeductionBody">
                        <tr>
                            <td colspan="11" class="text-center">No Data</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="employee-deduction-panel mt-3" id="employeeDeductionDetailPanel">
            <div class="employee-deduction-panel-header">
                <h6>Deduction Detail / History</h6>
                <div class="employee-deduction-detail-actions">
                    <button type="button" class="btn btn-sm btn-light" id="pause_employee_deduction_btn">Pause</button>
                    <button type="button" class="btn btn-sm btn-light" id="resume_employee_deduction_btn">Resume</button>
                    <button type="button" class="btn btn-sm btn-light" id="complete_employee_deduction_btn">Mark as Completed</button>
                </div>
            </div>

            <div class="employee-deduction-detail-grid">
                <div><span>Deduction Type</span><strong id="selectedDeductionType">-</strong></div>
                <div><span>Reference</span><strong id="selectedDeductionReference">-</strong></div>
                <div><span>Payroll Basis</span><strong id="selectedDeductionPayrollBasis">-</strong></div>
                <div><span>Frequency</span><strong id="selectedDeductionFrequency">-</strong></div>
                <div><span>Total Amount</span><strong id="selectedDeductionTotalAmount">₱0.00</strong></div>
                <div><span>Per Payroll</span><strong id="selectedDeductionPerPayroll">₱0.00</strong></div>
                <div><span>Total Paid</span><strong id="selectedDeductionTotalPaid">₱0.00</strong></div>
                <div><span>Remaining Balance</span><strong id="selectedDeductionRemainingBalance">₱0.00</strong></div>
                <div><span>Status</span><strong id="selectedDeductionStatus">-</strong></div>
                <div><span>Reflected in Payroll</span><strong id="selectedDeductionFormula">-</strong></div>
            </div>

            <div class="employee-deduction-transaction-form mt-3">
                <h6>Manual Transaction</h6>
                <div class="row g-3">
                    <div class="col-xl-3 col-md-6">
                        <label for="deduction_transaction_source">Source</label>
                        <select id="deduction_transaction_source" class="form-control form-control-sm">
                            <option value="manual_payment">Manual Payment</option>
                            <option value="manual_adjustment">Manual Adjustment</option>
                            <option value="reversal">Reversal</option>
                        </select>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <label for="deduction_transaction_amount">Amount</label>
                        <input type="text" id="deduction_transaction_amount" class="form-control form-control-sm" placeholder="₱0.00">
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <label for="deduction_transaction_date">Processed Date</label>
                        <input type="date" id="deduction_transaction_date" class="form-control form-control-sm">
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <label for="deduction_transaction_notes">Notes</label>
                        <input type="text" id="deduction_transaction_notes" class="form-control form-control-sm" placeholder="Reason or note">
                    </div>
                    <div class="col-12 text-right">
                        <button type="button" id="save_employee_deduction_transaction_btn" class="btn btn-sm btn-primary">Save Transaction</button>
                    </div>
                </div>
            </div>

            <div class="employee-deduction-history mt-3">
                <h6>Deduction History</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Sequence No.</th>
                                <th>Payroll Period</th>
                                <th>Processed Date</th>
                                <th>Deduction Type</th>
                                <th>Reference</th>
                                <th class="text-right">Scheduled Amount</th>
                                <th class="text-right">Actual Deducted Amount</th>
                                <th class="text-right">Running Balance</th>
                                <th>Source</th>
                                <th>Payroll Reference No.</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="employeeDeductionHistoryBody">
                            <tr>
                                <td colspan="11" class="text-center">Select a deduction to view history.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
