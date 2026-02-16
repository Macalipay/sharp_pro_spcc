<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Compensation Details Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 16px;
            color: #111;
        }
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            gap: 12px;
            flex-wrap: wrap;
        }
        .report-title {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
        }
        .report-subtitle {
            margin: 4px 0 0;
            font-size: 12px;
            color: #555;
        }
        .btn-print {
            border: 0;
            background: #1f4c8f;
            color: #fff;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
        }
        .btn-filter {
            border: 0;
            background: #0b7a3e;
            color: #fff;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
        }
        .btn-reset {
            border: 1px solid #888;
            background: #fff;
            color: #333;
            padding: 7px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .btn-select {
            border: 1px solid #888;
            background: #fff;
            color: #333;
            padding: 7px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
        }
        .filter-box {
            border: 1px solid #d7d7d7;
            padding: 10px;
            margin-bottom: 12px;
            background: #fafafa;
        }
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(140px, 1fr));
            gap: 8px;
            align-items: end;
        }
        .filter-group label {
            display: block;
            font-size: 11px;
            margin-bottom: 4px;
            font-weight: 700;
        }
        .filter-group input,
        .filter-group select {
            width: 100%;
            border: 1px solid #c8c8c8;
            border-radius: 4px;
            padding: 6px 8px;
            font-size: 12px;
            background: #fff;
        }
        .meta {
            margin: 6px 0 10px;
            font-size: 12px;
            color: #333;
            font-weight: 700;
        }
        .table-wrap {
            overflow-x: auto;
            border: 1px solid #d7d7d7;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #d7d7d7;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
            white-space: nowrap;
        }
        thead th {
            text-align: center;
            vertical-align: middle;
        }
        .ee-header {
            background: #e6e6e6;
        }
        .er-header {
            background: #bdbdbd;
        }
        .total-header {
            background: #6b6b6b;
            color: #fff;
        }
        th {
            background: #f5f5f5;
            font-weight: 700;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .employee-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 12px;
        }
        .employee-modal.open {
            display: flex;
        }
        .employee-modal-card {
            width: min(760px, 100%);
            background: #fff;
            border-radius: 6px;
            border: 1px solid #d7d7d7;
            overflow: hidden;
        }
        .employee-modal-header {
            padding: 10px 12px;
            background: #f5f5f5;
            border-bottom: 1px solid #d7d7d7;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .employee-modal-body {
            padding: 10px 12px;
        }
        .employee-list-box {
            border: 1px solid #d7d7d7;
            max-height: 320px;
            overflow: auto;
            padding: 8px;
        }
        .employee-item {
            display: block;
            margin-bottom: 5px;
            font-size: 12px;
        }
        .employee-modal-footer {
            padding: 10px 12px;
            border-top: 1px solid #d7d7d7;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            background: #fafafa;
        }
        @media print {
            .btn-print,
            .btn-filter,
            .btn-reset,
            .btn-select,
            .filter-box,
            .employee-modal {
                display: none;
            }
            body {
                margin: 0;
                font-size: 9pt;
            }
            @page {
                size: A4 landscape;
                margin: 6mm;
            }
            .report-title {
                font-size: 13pt;
            }
            .meta {
                font-size: 9pt;
            }
            .table-wrap {
                overflow: visible;
                border: 1px solid #000;
            }
            table {
                font-size: 8pt;
                table-layout: fixed;
            }
            th, td {
                padding: 3px 4px;
                line-height: 1.15;
                white-space: normal;
                word-break: break-word;
            }
            thead {
                display: table-header-group;
            }
        }
    </style>
</head>
<body>
    @php
        $selectedEmployeeIds = collect((array) request('employee_ids'))->map(function ($id) { return (string) $id; })->all();
        $isAllEmployeesSelected = count($selectedEmployeeIds) === 0 || in_array('__all__', $selectedEmployeeIds, true);
        $salaryTypeLabelMap = [
            'annual' => 'ANNUAL',
            'monthly' => 'MONTHLY',
            'semi_monthly' => 'SEMI-MONTHLY',
            'weekly' => 'WEEKLY',
        ];
        $salaryTypeCurrent = strtolower($salaryType ?? 'monthly');
        $salaryTypeLabel = $salaryTypeLabelMap[$salaryTypeCurrent] ?? 'MONTHLY';
    @endphp

    <div class="report-header">
        <div>
            <h1 class="report-title">Employee Compensation Details Report</h1>
            <p class="report-subtitle">Generated: {{ now()->format('F d, Y h:i A') }}</p>
        </div>
        <button class="btn-print" onclick="window.print()">Print Report</button>
    </div>

    <form method="GET" action="/reports/hr/employee-compensation-details" class="filter-box">
        <div class="filter-grid">
            <div class="filter-group">
                <label>Employee</label>
                <button type="button" class="btn-select" id="openEmployeeModalBtn">Select Employees</button>
                <div id="employeeHiddenInputs"></div>
            </div>

            <div class="filter-group">
                <label for="salary_type">Salary Type</label>
                <select name="salary_type" id="salary_type">
                    <option value="annual" {{ ($salaryType ?? 'monthly') === 'annual' ? 'selected' : '' }}>Annual</option>
                    <option value="monthly" {{ ($salaryType ?? 'monthly') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="semi_monthly" {{ ($salaryType ?? 'monthly') === 'semi_monthly' ? 'selected' : '' }}>Semi Monthly</option>
                    <option value="weekly" {{ ($salaryType ?? 'monthly') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="status">Status</label>
                <select name="status" id="status">
                    <option value="">All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="filter-group">
                <button type="submit" class="btn-filter">Apply Filter</button>
                <a href="/reports/hr/employee-compensation-details" class="btn-reset">Reset</a>
            </div>
        </div>
    </form>

    <div class="meta">Total Employees: {{ $employees->count() }} | Salary Type: {{ $salaryTypeLabel }}</div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th rowspan="2">Employee Name</th>
                    <th rowspan="2">Position</th>
                    <th rowspan="2">Current Salary</th>
                    <th colspan="3" class="text-center">SSS</th>
                    <th colspan="3" class="text-center">PAG-IBIG</th>
                    <th colspan="3" class="text-center">PHILHEALTH</th>
                    <th rowspan="2">Allowance</th>
                    <th rowspan="2">Project Site</th>
                </tr>
                <tr>
                    <th class="ee-header">EE</th>
                    <th class="er-header">ER</th>
                    <th class="total-header">Total</th>
                    <th class="ee-header">EE</th>
                    <th class="er-header">ER</th>
                    <th class="total-header">Total</th>
                    <th class="ee-header">EE</th>
                    <th class="er-header">ER</th>
                    <th class="total-header">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    @php
                        $fullName = trim(
                            ($employee->lastname ?? '') .
                            (($employee->firstname ?? '') !== '' ? (', ' . $employee->firstname) : '') .
                            (($employee->middlename ?? '') !== '' ? (' ' . $employee->middlename) : '') .
                            (($employee->suffix ?? '') !== '' ? (' ' . $employee->suffix) : '')
                        );
                        $comp = $employee->compensations;
                        $currentSalary = 0;
                        if ($comp) {
                            if ($salaryTypeCurrent === 'annual') {
                                $currentSalary = floatval($comp->annual_salary ?? (floatval($comp->monthly_salary ?? 0) * 12));
                            } elseif ($salaryTypeCurrent === 'semi_monthly') {
                                $currentSalary = floatval($comp->semi_monthly_salary ?? (floatval($comp->monthly_salary ?? 0) / 2));
                            } elseif ($salaryTypeCurrent === 'weekly') {
                                $currentSalary = floatval($comp->weekly_salary ?? (floatval($comp->monthly_salary ?? 0) / 4));
                            } else {
                                $currentSalary = floatval($comp->monthly_salary ?? 0);
                            }
                        }

                        $monthlyBaseSalary = $comp ? floatval($comp->monthly_salary ?? 0) : 0;
                        $sssBasis = min($monthlyBaseSalary, 35000);

                        // Match Compensation History formulas with separated shares.
                        $sssEeMonthly = $sssBasis * 0.05;
                        $sssErMonthly = $sssBasis * 0.10;
                        $sssMonthly = $sssEeMonthly + $sssErMonthly;

                        if ($monthlyBaseSalary <= 1500) {
                            $pagibigEeMonthly = $monthlyBaseSalary * 0.02;
                            $pagibigErMonthly = $monthlyBaseSalary * 0.01;
                        } elseif ($monthlyBaseSalary <= 10000) {
                            $pagibigEeMonthly = $monthlyBaseSalary * 0.02;
                            $pagibigErMonthly = $monthlyBaseSalary * 0.02;
                        } else {
                            $pagibigEeMonthly = 200;
                            $pagibigErMonthly = 200;
                        }
                        $pagibigMonthly = $pagibigEeMonthly + $pagibigErMonthly;

                        if ($monthlyBaseSalary <= 10000) {
                            $philhealthEeMonthly = 250;
                            $philhealthErMonthly = 250;
                        } elseif ($monthlyBaseSalary <= 100000) {
                            $philhealthMonthlyRaw = $monthlyBaseSalary * 0.05;
                            $philhealthEeMonthly = $philhealthMonthlyRaw / 2;
                            $philhealthErMonthly = $philhealthMonthlyRaw / 2;
                        } else {
                            $philhealthEeMonthly = 2500;
                            $philhealthErMonthly = 2500;
                        }
                        $philhealthMonthly = $philhealthEeMonthly + $philhealthErMonthly;

                        $contributionFactor = 1;
                        if ($salaryTypeCurrent === 'semi_monthly') {
                            $contributionFactor = 0.5;
                        } elseif ($salaryTypeCurrent === 'weekly') {
                            $contributionFactor = 0.25;
                        } elseif ($salaryTypeCurrent === 'annual') {
                            $contributionFactor = 12;
                        }

                        $sssEe = $sssEeMonthly * $contributionFactor;
                        $sssEr = $sssErMonthly * $contributionFactor;
                        $sss = $sssMonthly * $contributionFactor;

                        $pagibigEe = $pagibigEeMonthly * $contributionFactor;
                        $pagibigEr = $pagibigErMonthly * $contributionFactor;
                        $pagibig = $pagibigMonthly * $contributionFactor;

                        $philhealthEe = $philhealthEeMonthly * $contributionFactor;
                        $philhealthEr = $philhealthErMonthly * $contributionFactor;
                        $philhealth = $philhealthMonthly * $contributionFactor;

                        $allowanceRows = $allowanceByEmployee->get($employee->id, collect());
                        $allowanceTotal = $allowanceRows->sum(function ($row) {
                            return floatval($row->amount ?? 0);
                        });

                        $projectRows = $projectsByEmployee->get($employee->id, collect());
                        $projectNames = $projectRows
                            ->map(function ($row) {
                                return optional($row->project)->project_name;
                            })
                            ->filter()
                            ->unique()
                            ->values()
                            ->all();
                    @endphp
                    <tr>
                        <td>{{ $fullName !== '' ? $fullName : '-' }}</td>
                        <td>{{ optional(optional($employee->employments_tab)->positions)->description ?? '-' }}</td>
                        <td class="text-right">{{ number_format($currentSalary, 2) }}</td>
                        <td class="text-right">{{ number_format($sssEe, 2) }}</td>
                        <td class="text-right">{{ number_format($sssEr, 2) }}</td>
                        <td class="text-right">{{ number_format($sss, 2) }}</td>
                        <td class="text-right">{{ number_format($pagibigEe, 2) }}</td>
                        <td class="text-right">{{ number_format($pagibigEr, 2) }}</td>
                        <td class="text-right">{{ number_format($pagibig, 2) }}</td>
                        <td class="text-right">{{ number_format($philhealthEe, 2) }}</td>
                        <td class="text-right">{{ number_format($philhealthEr, 2) }}</td>
                        <td class="text-right">{{ number_format($philhealth, 2) }}</td>
                        <td class="text-right">{{ number_format($allowanceTotal, 2) }}</td>
                        <td>{{ count($projectNames) ? implode(', ', $projectNames) : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="text-center">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="employee-modal" id="employeeSelectionModal" aria-hidden="true">
        <div class="employee-modal-card">
            <div class="employee-modal-header">
                <span>Select Employees</span>
                <button type="button" class="btn-select" id="closeEmployeeModalTop">Close</button>
            </div>
            <div class="employee-modal-body">
                <label class="employee-item">
                    <input type="checkbox" id="employeeAllCheckbox" {{ $isAllEmployeesSelected ? 'checked' : '' }}>
                    <strong>Select All</strong>
                </label>

                <div class="employee-list-box">
                    @foreach($employeeOptions as $optEmployee)
                        @php
                            $optionName = trim(
                                ($optEmployee->lastname ?? '') .
                                (($optEmployee->firstname ?? '') !== '' ? (', ' . $optEmployee->firstname) : '') .
                                (($optEmployee->middlename ?? '') !== '' ? (' ' . $optEmployee->middlename) : '') .
                                (($optEmployee->suffix ?? '') !== '' ? (' ' . $optEmployee->suffix) : '')
                            );
                            $checked = !$isAllEmployeesSelected && in_array((string) $optEmployee->id, $selectedEmployeeIds, true);
                        @endphp
                        <label class="employee-item">
                            <input type="checkbox" class="employee-item-checkbox" value="{{ $optEmployee->id }}" {{ $checked ? 'checked' : '' }}>
                            {{ $optionName !== '' ? $optionName : 'NO NAME' }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="employee-modal-footer">
                <button type="button" class="btn-reset" id="cancelEmployeeModalBtn">Cancel</button>
                <button type="button" class="btn-filter" id="applyEmployeeSelectionBtn">Apply Selection</button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var modal = document.getElementById('employeeSelectionModal');
            var openBtn = document.getElementById('openEmployeeModalBtn');
            var closeTopBtn = document.getElementById('closeEmployeeModalTop');
            var cancelBtn = document.getElementById('cancelEmployeeModalBtn');
            var applyBtn = document.getElementById('applyEmployeeSelectionBtn');
            var allCheckbox = document.getElementById('employeeAllCheckbox');
            var itemCheckboxes = Array.from(document.querySelectorAll('.employee-item-checkbox'));
            var hiddenInputs = document.getElementById('employeeHiddenInputs');
            var filterForm = document.querySelector('form.filter-box');

            if (!modal || !openBtn || !allCheckbox || itemCheckboxes.length === 0 || !hiddenInputs || !filterForm) {
                return;
            }

            function getSelectedIds() {
                return itemCheckboxes
                    .filter(function (checkbox) { return checkbox.checked; })
                    .map(function (checkbox) { return checkbox.value; });
            }

            function syncHiddenInputs() {
                hiddenInputs.innerHTML = '';

                if (allCheckbox.checked || getSelectedIds().length === 0) {
                    var allInput = document.createElement('input');
                    allInput.type = 'hidden';
                    allInput.name = 'employee_ids[]';
                    allInput.value = '__all__';
                    hiddenInputs.appendChild(allInput);
                    return;
                }

                getSelectedIds().forEach(function (id) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'employee_ids[]';
                    input.value = id;
                    hiddenInputs.appendChild(input);
                });
            }

            function openModal() {
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
            }

            function closeModal() {
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
            }

            openBtn.addEventListener('click', openModal);
            closeTopBtn.addEventListener('click', closeModal);
            cancelBtn.addEventListener('click', closeModal);

            allCheckbox.addEventListener('change', function () {
                if (allCheckbox.checked) {
                    itemCheckboxes.forEach(function (checkbox) {
                        checkbox.checked = false;
                    });
                }
            });

            itemCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    if (checkbox.checked) {
                        allCheckbox.checked = false;
                    }
                });
            });

            applyBtn.addEventListener('click', function () {
                if (!allCheckbox.checked && getSelectedIds().length === 0) {
                    allCheckbox.checked = true;
                }
                syncHiddenInputs();
                closeModal();
            });

            filterForm.addEventListener('submit', function () {
                syncHiddenInputs();
            });

            syncHiddenInputs();
        })();
    </script>
</body>
</html>
