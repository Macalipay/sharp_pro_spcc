<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Balance Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 16px; color: #111; }
        .report-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; gap: 12px; flex-wrap: wrap; }
        .report-title { font-size: 18px; font-weight: 700; margin: 0; }
        .report-subtitle { margin: 4px 0 0; font-size: 12px; color: #555; }
        .btn-print { border: 0; background: #1f4c8f; color: #fff; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600; }
        .btn-filter { border: 0; background: #0b7a3e; color: #fff; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600; }
        .btn-reset { border: 1px solid #888; background: #fff; color: #333; padding: 7px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-block; }
        .filter-box { border: 1px solid #d7d7d7; padding: 10px; margin-bottom: 12px; background: #fafafa; }
        .filter-grid { display: grid; grid-template-columns: repeat(3, minmax(180px, 1fr)); gap: 8px; align-items: end; }
        .filter-group label { display: block; font-size: 11px; margin-bottom: 4px; font-weight: 700; }
        .filter-group input, .filter-group select { width: 100%; border: 1px solid #c8c8c8; border-radius: 4px; padding: 6px 8px; font-size: 12px; background: #fff; }
        .filter-group select[multiple] { min-height: 90px; }
        .meta { margin: 6px 0 10px; font-size: 12px; color: #333; font-weight: 700; }
        .table-wrap { overflow-x: auto; border: 1px solid #d7d7d7; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #d7d7d7; padding: 6px 8px; text-align: left; vertical-align: top; white-space: nowrap; }
        th { background: #f5f5f5; font-weight: 700; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        @media print {
            .btn-print, .btn-filter, .btn-reset, .filter-box { display: none; }
            body { margin: 0; font-size: 9pt; }
            @page { size: A4 landscape; margin: 6mm; }
            .report-title { font-size: 13pt; }
            .meta { font-size: 9pt; }
            table { font-size: 8pt; table-layout: fixed; }
            th, td { white-space: normal; word-break: break-word; padding: 3px 4px; }
            thead { display: table-header-group; }
        }
    </style>
</head>
<body>
    <div class="report-header">
        <div>
            <h1 class="report-title">Leave Balance Report</h1>
            <p class="report-subtitle">REPORTS / HR REPORTS / LEAVE BALANCE REPORT</p>
        </div>
        <button class="btn-print" onclick="window.print()">Print Report</button>
    </div>

    <form method="GET" action="/reports/hr/leave-balance" class="filter-box">
        <div class="filter-grid">
            <div class="filter-group">
                <label>EMPLOYEES</label>
                <select disabled>
                    <option selected>ALL</option>
                </select>
            </div>
            <div class="filter-group">
                <label>STATUS</label>
                <select name="status">
                    <option value="">All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="filter-group">
                <button type="submit" class="btn-filter">Apply Filter</button>
                <a href="/reports/hr/leave-balance" class="btn-reset">Reset</a>
            </div>
        </div>
    </form>

    <div class="meta">Generated on {{ now()->format('M d, Y h:i A') }}</div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Employee No.</th>
                    <th>Employee Name</th>
                    <th>Leave Type</th>
                    <th class="text-right">Entitlement (Days)</th>
                    <th class="text-right">Used (Days)</th>
                    <th class="text-right">Balance (Days)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($leaveRows as $row)
                    @php
                        $employee = $row->employee;
                        $employeeName = $employee ? trim($employee->firstname . ' ' . ($employee->middlename ?? '') . ' ' . $employee->lastname . ' ' . ($employee->suffix ?? '')) : '-';
                    @endphp
                    <tr>
                        <td>{{ $employee->employee_no ?? '-' }}</td>
                        <td>{{ $employeeName }}</td>
                        <td>{{ optional($row->leave_types)->leave_name ?? '-' }}</td>
                        <td class="text-right">{{ number_format((float) ($row->entitlement_days ?? 0), 2) }}</td>
                        <td class="text-right">{{ number_format((float) ($row->used_days ?? 0), 2) }}</td>
                        <td class="text-right">{{ number_format((float) ($row->balance_days ?? 0), 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No leave balance records found for the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
