<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Summary Details Report</title>
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
            border-radius: 10px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
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
            font-size: 11px;
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
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .report-signoff {
            margin-top: 16px;
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
            font-size: 12px;
        }
        .report-signoff .item {
            min-width: 260px;
        }
        .report-signoff .label {
            font-weight: 700;
            margin-bottom: 2px;
        }
        @media print {
            .btn-print {
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
        $formatDate = function ($value) {
            $raw = trim((string) $value);
            if ($raw === '' || $raw === '0000-00-00') {
                return null;
            }
            foreach (['Y-m-d', 'm/d/Y', 'm-d-Y', 'mdY'] as $fmt) {
                try {
                    return \Carbon\Carbon::createFromFormat($fmt, $raw)->format('M d, Y');
                } catch (\Throwable $e) {
                }
            }
            try {
                return \Carbon\Carbon::parse($raw)->format('M d, Y');
            } catch (\Throwable $e) {
                return null;
            }
        };

        $periodCovered = '-';
        $start = $formatDate($summary->period_start ?? null);
        $end = $formatDate($summary->payroll_period ?? null);
        $payDate = $formatDate($summary->pay_date ?? null);
        if ($start && $end) {
            $periodCovered = $start . ' - ' . $end;
        } elseif ($end) {
            $periodCovered = $end;
        } elseif ($start) {
            $periodCovered = $start;
        } elseif ($payDate) {
            $periodCovered = $payDate;
        } elseif (preg_match('/-(\d{8})$/', (string) ($summary->sequence_no ?? ''), $m)) {
            $legacy = $formatDate($m[1]);
            if ($legacy) {
                $periodCovered = $legacy;
            }
        }
        $preparedBy = trim((string) ($summary->submitted_by_name ?? ''));
        if ($preparedBy === '') {
            $preparedBy = trim((string) ($summary->created_by_name ?? ''));
        }
        $approvedBy = trim((string) ($summary->approved_by_name ?? ''));
    @endphp

    <div class="report-header">
        <div>
            <h1 class="report-title">Payroll Summary Details Report</h1>
            <p class="report-subtitle">Period Covered: {{ $periodCovered }}</p>
        </div>
        <button class="btn-print" onclick="window.print()">Print Report</button>
    </div>

    <div class="meta">Total Employees: {{ $rows->count() }}</div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Project Name</th>
                    <th>Payroll Status</th>
                    <th class="text-right">Gross Earnings</th>
                    <th class="text-right">SSS</th>
                    <th class="text-right">PHIC</th>
                    <th class="text-right">PAG-IBIG</th>
                    <th class="text-right">Withholding Tax</th>
                    <th class="text-right">Cash Advance</th>
                    <th class="text-right">Gross Deduction</th>
                    <th class="text-right">Net Pay</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    @php
                        $fullName = trim(
                            ($row->lastname ?? '') .
                            (($row->firstname ?? '') !== '' ? (', ' . $row->firstname) : '') .
                            (($row->middlename ?? '') !== '' ? (' ' . $row->middlename) : '') .
                            (($row->suffix ?? '') !== '' ? (' ' . $row->suffix) : '')
                        );
                    @endphp
                    <tr>
                        <td>{{ $fullName !== '' ? $fullName : '-' }}</td>
                        <td>{{ $row->project_name ?? '-' }}</td>
                        <td>{{ $row->payroll_status ?? '-' }}</td>
                        <td class="text-right">{{ number_format((float) $row->gross_earnings, 2) }}</td>
                        <td class="text-right">{{ number_format((float) ($row->sss ?? 0), 2) }}</td>
                        <td class="text-right">{{ number_format((float) ($row->philhealth ?? 0), 2) }}</td>
                        <td class="text-right">{{ number_format((float) ($row->pagibig ?? 0), 2) }}</td>
                        <td class="text-right">{{ number_format((float) ($row->tax ?? 0), 2) }}</td>
                        <td class="text-right">{{ number_format((float) ($row->ca_total ?? 0), 2) }}</td>
                        <td class="text-right">{{ number_format((float) $row->gross_deduction, 2) }}</td>
                        <td class="text-right">{{ number_format((float) $row->net_pay, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center">No employees found for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="report-signoff">
        <div class="item">
            <div class="label">Prepared by</div>
            <div>{{ $preparedBy !== '' ? $preparedBy : '-' }}</div>
        </div>
        <div class="item">
            <div class="label">Approved by</div>
            <div>{{ $approvedBy !== '' ? $approvedBy : '-' }}</div>
        </div>
    </div>
</body>
</html>
