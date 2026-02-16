<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Masterfile Report</title>
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
        .filter-box {
            border: 1px solid #d7d7d7;
            padding: 10px;
            margin-bottom: 12px;
            background: #fafafa;
        }
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(140px, 1fr));
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
        .employment-from-group {
            margin-right: 8px;
        }
        .filter-group select[multiple] {
            min-height: 90px;
        }
        .employee-select-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            min-height: 34px;
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
            table-layout: auto;
        }
        th, td {
            border: 1px solid #d7d7d7;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
            white-space: nowrap;
        }
        th {
            background: #f5f5f5;
            font-weight: 700;
        }
        .text-center {
            text-align: center;
        }
        @media print {
            .btn-print,
            .btn-filter,
            .btn-reset,
            .filter-box {
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
            .report-header {
                margin-bottom: 6px;
            }
            .report-title {
                font-size: 13pt;
            }
            .report-subtitle {
                font-size: 9pt;
            }
            .meta {
                margin: 4px 0 6px;
                font-size: 9pt;
            }
            .table-wrap {
                overflow: visible;
                border: 1px solid #000;
            }
            table {
                width: 100%;
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
            tr, td, th {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="report-header">
        <div>
            <h1 class="report-title">Employee Masterfile Report</h1>
            <p class="report-subtitle">Generated: {{ now()->format('F d, Y h:i A') }}</p>
        </div>
        <button class="btn-print" onclick="window.print()">Print Report</button>
    </div>

    <form method="GET" action="/reports/hr/employee-masterfile" class="filter-box">
        <div class="filter-grid">
            <div class="filter-group">
                <label>Employees</label>
                @php
                    $selectedEmployeeIds = collect((array) request('employee_ids'))->map(function ($id) { return (string) $id; })->all();
                    $isAllEmployeesSelected = count($selectedEmployeeIds) === 0 || in_array('__all__', $selectedEmployeeIds, true);
                @endphp
                <div class="employee-select-actions">
                    <button type="button" class="btn-select" id="openEmployeeModalBtn">Select Employees</button>
                </div>
                <div id="employeeHiddenInputs"></div>
            </div>

            <div class="filter-group">
                <label for="department_id">Department</label>
                <select name="department_id" id="department_id">
                    <option value="">All</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ (string)request('department_id') === (string)$department->id ? 'selected' : '' }}>
                            {{ $department->description }}
                        </option>
                    @endforeach
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

            <div class="filter-group employment-from-group">
                <label for="employment_date_from">Employment Date From</label>
                <input type="date" name="employment_date_from" id="employment_date_from" value="{{ request('employment_date_from') }}">
            </div>

            <div class="filter-group">
                <label for="employment_date_to">Employment Date To</label>
                <input type="date" name="employment_date_to" id="employment_date_to" value="{{ request('employment_date_to') }}">
            </div>

            <div class="filter-group">
                <button type="submit" class="btn-filter">Apply Filter</button>
                <a href="/reports/hr/employee-masterfile" class="btn-reset">Reset</a>
            </div>
        </div>
    </form>

    <div class="meta">Total Employees: {{ $employees->count() }}</div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Employee No.</th>
                    <th>Employee Name</th>
                    <th>Birthdate</th>
                    <th>Gender</th>
                    <th>Civil Status</th>
                    <th>Contact No.</th>
                    <th>Email Address</th>
                    <th>Employment Date</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th>Employment Status</th>
                    <th>Payout Schedule</th>
                    <th>Status</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    @php
                        $fullName = trim(
                            ($employee->firstname ?? '') .
                            (($employee->middlename ?? '') !== '' ? (' ' . $employee->middlename) : '') .
                            ' ' . ($employee->lastname ?? '') .
                            (($employee->suffix ?? '') !== '' ? (' ' . $employee->suffix) : '')
                        );
                        $address = trim(
                            ($employee->street_1 ?? '') .
                            (($employee->barangay_1 ?? '') !== '' ? (', ' . ($employee->barangay_1->name ?? $employee->barangay_1)) : '') .
                            (($employee->city_1 ?? '') !== '' ? (', ' . ($employee->city_1->name ?? $employee->city_1)) : '') .
                            (($employee->province_1 ?? '') !== '' ? (', ' . ($employee->province_1->name ?? $employee->province_1)) : '')
                        );
                        $statusLabel = ((int) ($employee->status ?? 0) === 1) ? 'ACTIVE' : 'INACTIVE';
                    @endphp
                    <tr>
                        <td>{{ $employee->employee_no ?? '-' }}</td>
                        <td>{{ $fullName !== '' ? $fullName : '-' }}</td>
                        <td>
                            {{ $employee->birthdate ? \Carbon\Carbon::parse($employee->birthdate)->format('M d, Y') : '-' }}
                        </td>
                        <td>{{ $employee->gender ?? '-' }}</td>
                        <td>{{ $employee->civil_status ?? '-' }}</td>
                        <td>{{ $employee->phone1 ?? '-' }}</td>
                        <td>{{ $employee->email ?? '-' }}</td>
                        <td>
                            {{ optional($employee->employments_tab)->employment_date ? \Carbon\Carbon::parse(optional($employee->employments_tab)->employment_date)->format('M d, Y') : '-' }}
                        </td>
                        <td>{{ optional(optional($employee->employments_tab)->departments)->description ?? '-' }}</td>
                        <td>{{ optional(optional($employee->employments_tab)->positions)->description ?? '-' }}</td>
                        <td>{{ $employee->employment_status ?? '-' }}</td>
                        <td>{{ $employee->employment_type ?? '-' }}</td>
                        <td>{{ $statusLabel }}</td>
                        <td>{{ $address !== '' ? $address : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="text-center">No employee records found.</td>
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
                            $optionLabel = $optionName !== '' ? $optionName : 'NO NAME';
                            $checked = !$isAllEmployeesSelected && in_array((string) $optEmployee->id, $selectedEmployeeIds, true);
                        @endphp
                        <label class="employee-item">
                            <input type="checkbox" class="employee-item-checkbox" value="{{ $optEmployee->id }}" {{ $checked ? 'checked' : '' }}>
                            {{ $optionLabel }}
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
