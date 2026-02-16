<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Attendance Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 16px; color: #111; }
        .report-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; gap: 12px; flex-wrap: wrap; }
        .report-title { font-size: 18px; font-weight: 700; margin: 0; }
        .report-subtitle { margin: 4px 0 0; font-size: 12px; color: #555; }
        .btn-print { border: 0; background: #1f4c8f; color: #fff; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600; }
        .btn-filter { border: 0; background: #0b7a3e; color: #fff; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600; }
        .btn-reset { border: 1px solid #888; background: #fff; color: #333; padding: 7px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-block; }
        .filter-box { border: 1px solid #d7d7d7; padding: 10px; margin-bottom: 12px; background: #fafafa; }
        .filter-grid { display: grid; grid-template-columns: repeat(5, minmax(140px, 1fr)); gap: 8px; align-items: end; }
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
        .metric-link {
            border: 0;
            background: transparent;
            color: #1f4c8f;
            font-weight: 700;
            cursor: pointer;
            padding: 0;
            text-decoration: underline;
            font-size: 12px;
        }
        .metric-link:hover { color: #163968; }
        .breakdown-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 12px;
        }
        .breakdown-modal.open { display: flex; }
        .breakdown-card {
            width: min(980px, 100%);
            background: #fff;
            border: 1px solid #d7d7d7;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 6px 30px rgba(0, 0, 0, 0.2);
        }
        .breakdown-header {
            padding: 10px 12px;
            border-bottom: 1px solid #d7d7d7;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            background: #f5f5f5;
        }
        .breakdown-title {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
        }
        .breakdown-close {
            border: 0;
            background: #eee;
            color: #111;
            padding: 4px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
        }
        .breakdown-body {
            max-height: 65vh;
            overflow: auto;
            padding: 10px 12px;
        }
        .breakdown-footer {
            padding: 10px 12px;
            border-top: 1px solid #d7d7d7;
            background: #fafafa;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }
        .breakdown-print {
            border: 0;
            background: #1f4c8f;
            color: #fff;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
        }
        .breakdown-body table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .breakdown-body th,
        .breakdown-body td {
            border: 1px solid #d7d7d7;
            padding: 6px 8px;
            white-space: nowrap;
        }
        .breakdown-empty {
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        @media print {
            .btn-print, .btn-filter, .btn-reset, .filter-box { display: none; }
            .metric-link { text-decoration: none; color: #111; pointer-events: none; }
            .breakdown-modal { display: none !important; }
            .breakdown-footer { display: none !important; }
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
            <h1 class="report-title">Employee Attendance Report</h1>
            <p class="report-subtitle">REPORTS / HR REPORTS / EMPLOYEE ATTENDANCE REPORT</p>
        </div>
        <button class="btn-print" onclick="window.print()">Print Report</button>
    </div>

    <form method="GET" action="/reports/hr/employee-attendance" class="filter-box">
        <div class="filter-grid">
            <div class="filter-group">
                <label>DATE FROM</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="filter-group">
                <label>DATE TO</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}">
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
                <label>EMPLOYEES</label>
                <select disabled>
                    <option selected>ALL</option>
                </select>
            </div>
            <div class="filter-group">
                <button type="submit" class="btn-filter">Apply Filter</button>
                <a href="/reports/hr/employee-attendance" class="btn-reset">Reset</a>
            </div>
        </div>
    </form>

    @php
        $dateFrom = request('date_from');
        $dateTo = request('date_to');
        $rangeLabel = 'All Dates';
        if ($dateFrom && $dateTo) {
            $rangeLabel = \Carbon\Carbon::parse($dateFrom)->format('M d, Y') . ' to ' . \Carbon\Carbon::parse($dateTo)->format('M d, Y');
        } elseif ($dateFrom) {
            $rangeLabel = 'From ' . \Carbon\Carbon::parse($dateFrom)->format('M d, Y');
        } elseif ($dateTo) {
            $rangeLabel = 'Up to ' . \Carbon\Carbon::parse($dateTo)->format('M d, Y');
        }
    @endphp
    <div class="meta">
        Date Range: {{ $rangeLabel }}<br>
        Generated on {{ now()->format('M d, Y h:i A') }}
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Employee No.</th>
                    <th>Employee Name</th>
                    <th class="text-right">Days Present</th>
                    <th class="text-right">Absent</th>
                    <th class="text-right">Total Hours</th>
                    <th class="text-right">Late Hours</th>
                    <th class="text-right">Undertime Hours</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($attendanceRows as $row)
                    <tr>
                        <td>{{ $row->employee_no }}</td>
                        <td>{{ $row->employee_name !== '' ? $row->employee_name : '-' }}</td>
                        <td class="text-right">
                            <button type="button" class="metric-link breakdown-trigger"
                                data-title="Days Present Breakdown - {{ $row->employee_name !== '' ? $row->employee_name : '-' }}"
                                data-items='@json($row->present_breakdown ?? [])'>
                                {{ number_format((float) ($row->days_present ?? 0), 0) }}
                            </button>
                        </td>
                        <td class="text-right">
                            <button type="button" class="metric-link breakdown-trigger"
                                data-title="Absent Breakdown - {{ $row->employee_name !== '' ? $row->employee_name : '-' }}"
                                data-items='@json($row->absent_breakdown ?? [])'>
                                {{ number_format((float) ($row->days_absent ?? 0), 0) }}
                            </button>
                        </td>
                        <td class="text-right">{{ number_format((float) ($row->total_hours ?? 0), 2) }}</td>
                        <td class="text-right">
                            <button type="button" class="metric-link breakdown-trigger"
                                data-title="Late Hours Breakdown - {{ $row->employee_name !== '' ? $row->employee_name : '-' }}"
                                data-items='@json($row->late_breakdown ?? [])'>
                                {{ number_format((float) ($row->late_hours ?? 0), 2) }}
                            </button>
                        </td>
                        <td class="text-right">
                            <button type="button" class="metric-link breakdown-trigger"
                                data-title="Undertime Hours Breakdown - {{ $row->employee_name !== '' ? $row->employee_name : '-' }}"
                                data-items='@json($row->undertime_breakdown ?? [])'>
                                {{ number_format((float) ($row->undertime_hours ?? 0), 2) }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No attendance records found for the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div id="attendanceBreakdownModal" class="breakdown-modal">
        <div class="breakdown-card">
            <div class="breakdown-header">
                <h2 id="breakdownTitle" class="breakdown-title">Attendance Breakdown</h2>
                <button type="button" class="breakdown-close" id="closeBreakdownModal">Close</button>
            </div>
            <div class="breakdown-body" id="breakdownBody"></div>
            <div class="breakdown-footer">
                <button type="button" class="breakdown-print" id="printBreakdownBtn">Print</button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var modal = document.getElementById('attendanceBreakdownModal');
            var titleNode = document.getElementById('breakdownTitle');
            var bodyNode = document.getElementById('breakdownBody');
            var closeBtn = document.getElementById('closeBreakdownModal');
            var printBtn = document.getElementById('printBreakdownBtn');
            var triggers = document.querySelectorAll('.breakdown-trigger');
            var currentTitle = 'Attendance Breakdown';
            var currentItems = [];

            function formatDate(value) {
                if (!value) return '-';
                var date = new Date(value);
                if (isNaN(date.getTime())) return value;
                return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: '2-digit' });
            }

            function formatDateTime(value) {
                if (!value) return '-';
                var date = new Date(value);
                if (isNaN(date.getTime())) return value;
                return date.toLocaleString('en-US', { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' });
            }

            function renderTable(items) {
                if (!items || items.length === 0) {
                    bodyNode.innerHTML = '<div class="breakdown-empty">No records found.</div>';
                    return;
                }

                var rows = items.map(function (item) {
                    var totalHours = Number(item.total_hours || 0).toFixed(2);
                    var lateHours = Number(item.late_hours || 0).toFixed(2);
                    var undertime = Number(item.undertime || 0).toFixed(2);

                    return '<tr>'
                        + '<td>' + formatDate(item.date) + '</td>'
                        + '<td>' + formatDateTime(item.time_in) + '</td>'
                        + '<td>' + formatDateTime(item.time_out) + '</td>'
                        + '<td class="text-right">' + totalHours + '</td>'
                        + '<td class="text-right">' + lateHours + '</td>'
                        + '<td class="text-right">' + undertime + '</td>'
                        + '</tr>';
                }).join('');

                bodyNode.innerHTML = ''
                    + '<table>'
                    + '<thead><tr>'
                    + '<th>Date</th>'
                    + '<th>Time In</th>'
                    + '<th>Time Out</th>'
                    + '<th class="text-right">Total Hours</th>'
                    + '<th class="text-right">Late Hours</th>'
                    + '<th class="text-right">Undertime</th>'
                    + '</tr></thead>'
                    + '<tbody>' + rows + '</tbody>'
                    + '</table>';
            }

            function openModal(title, items) {
                currentTitle = title || 'Attendance Breakdown';
                currentItems = Array.isArray(items) ? items : [];
                titleNode.textContent = currentTitle;
                renderTable(currentItems);
                modal.classList.add('open');
            }

            function closeModal() {
                modal.classList.remove('open');
            }

            triggers.forEach(function (trigger) {
                trigger.addEventListener('click', function () {
                    var rawItems = this.getAttribute('data-items') || '[]';
                    var title = this.getAttribute('data-title') || 'Attendance Breakdown';
                    var items = [];
                    try {
                        items = JSON.parse(rawItems);
                    } catch (e) {
                        items = [];
                    }
                    openModal(title, items);
                });
            });

            closeBtn.addEventListener('click', closeModal);
            printBtn.addEventListener('click', function () {
                var printWindow = window.open('', '_blank', 'width=1100,height=800');
                if (!printWindow) {
                    return;
                }

                var rows = '';
                if (!currentItems || currentItems.length === 0) {
                    rows = '<tr><td colspan="6" style="text-align:center;">No records found.</td></tr>';
                } else {
                    rows = currentItems.map(function (item) {
                        var totalHours = Number(item.total_hours || 0).toFixed(2);
                        var lateHours = Number(item.late_hours || 0).toFixed(2);
                        var undertime = Number(item.undertime || 0).toFixed(2);
                        return '<tr>'
                            + '<td>' + formatDate(item.date) + '</td>'
                            + '<td>' + formatDateTime(item.time_in) + '</td>'
                            + '<td>' + formatDateTime(item.time_out) + '</td>'
                            + '<td style="text-align:right;">' + totalHours + '</td>'
                            + '<td style="text-align:right;">' + lateHours + '</td>'
                            + '<td style="text-align:right;">' + undertime + '</td>'
                            + '</tr>';
                    }).join('');
                }

                var html = ''
                    + '<html><head><title>' + currentTitle + '</title>'
                    + '<style>'
                    + '@page{size:A4 landscape;margin:8mm;}'
                    + 'body{font-family:Arial,sans-serif;color:#111;}'
                    + 'h2{margin:0 0 10px;font-size:16px;}'
                    + 'table{width:100%;border-collapse:collapse;font-size:12px;}'
                    + 'th,td{border:1px solid #d7d7d7;padding:6px 8px;white-space:nowrap;}'
                    + 'th{background:#f5f5f5;text-align:left;}'
                    + '</style>'
                    + '</head><body>'
                    + '<h2>' + currentTitle + '</h2>'
                    + '<table><thead><tr>'
                    + '<th>Date</th><th>Time In</th><th>Time Out</th><th>Total Hours</th><th>Late Hours</th><th>Undertime</th>'
                    + '</tr></thead><tbody>' + rows + '</tbody></table>'
                    + '<script>window.onload=function(){window.print();window.close();};<\/script>'
                    + '</body></html>';

                printWindow.document.open();
                printWindow.document.write(html);
                printWindow.document.close();
            });
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeModal();
                }
            });
        })();
    </script>
</body>
</html>
