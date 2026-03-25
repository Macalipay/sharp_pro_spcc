@extends('backend.master.index')

@section('title', 'KPI DASHBOARD')

@section('breadcrumbs')
    <span>REPORTS</span> / <span>ACCOUNTING REPORTS</span> / <span class="highlight">KPI DASHBOARD</span>
@endsection

@section('styles')
<style>
    .app-main {
        display: flex !important;
        flex-direction: column !important;
        height: 100% !important;
        overflow: hidden !important;
    }
    .app-content-row {
        flex: 1 1 auto !important;
        min-height: 0 !important;
        overflow: hidden !important;
    }
    .app-content-column-full {
        height: 100% !important;
        max-height: none !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
    }
    .app-content-column-full > * {
        min-height: 100%;
        overflow: visible !important;
    }
    .reports-btn {
        background-color: #0f4c5c !important;
        border-color: #0f4c5c !important;
        color: #fff !important;
        border-radius: 10px !important;
        padding: 0.4rem 0.85rem;
    }
    .reports-btn:hover,
    .reports-btn:focus {
        background-color: #0b3944 !important;
        border-color: #0b3944 !important;
        color: #fff !important;
    }
    .finance-shell {
        padding-bottom: 24px;
        overflow: visible !important;
    }
    .hero-panel {
        background: linear-gradient(135deg, #0f4c5c 0%, #1f6f78 48%, #d9ed92 100%);
        border-radius: 18px;
        padding: 22px 24px;
        color: #fff;
        margin-bottom: 18px;
        box-shadow: 0 18px 36px rgba(15, 76, 92, 0.18);
    }
    .hero-title {
        font-size: 24px;
        font-weight: 700;
        letter-spacing: 0.02em;
    }
    .hero-subtitle {
        max-width: 720px;
        color: rgba(255,255,255,0.88);
        margin-top: 6px;
    }
    .hero-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 14px;
    }
    .hero-badge {
        background: rgba(255,255,255,0.14);
        border: 1px solid rgba(255,255,255,0.18);
        border-radius: 999px;
        padding: 6px 12px;
        font-size: 12px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .filter-card,
    .section-card {
        border: 1px solid #d8e2dc;
        border-radius: 16px;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }
    .filter-card .card-header,
    .section-card .card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(185px, 1fr));
        gap: 12px;
    }
    .kpi-card {
        border: 1px solid #d8e2dc;
        border-radius: 16px;
        padding: 14px 16px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        min-height: 116px;
    }
    .kpi-card.alert-danger {
        border-color: #fecaca;
        background: linear-gradient(180deg, #fff7f7 0%, #fee2e2 100%);
    }
    .kpi-card.alert-warning {
        border-color: #fde68a;
        background: linear-gradient(180deg, #fffdf4 0%, #fef3c7 100%);
    }
    .kpi-label {
        color: #64748b;
        font-size: 11px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }
    .kpi-value {
        margin-top: 10px;
        font-size: 26px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.1;
    }
    .kpi-meta {
        margin-top: 8px;
        font-size: 12px;
        color: #475569;
    }
    .alert-stack {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 10px;
    }
    .alert-box {
        border-radius: 14px;
        padding: 12px 14px;
        border: 1px solid transparent;
    }
    .alert-box.alert-danger {
        background: #fff1f2;
        border-color: #fecdd3;
        color: #9f1239;
    }
    .alert-box.alert-warning {
        background: #fff7ed;
        border-color: #fed7aa;
        color: #9a3412;
    }
    .metric-strip {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 10px;
    }
    .metric-tile {
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 12px 14px;
    }
    .metric-tile .title {
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .metric-tile .value {
        margin-top: 8px;
        font-size: 20px;
        font-weight: 700;
        color: #0f172a;
    }
    .mini-table td,
    .mini-table th {
        font-size: 12px;
    }
    .chart-card {
        border: 1px solid #d8e2dc;
        border-radius: 16px;
        padding: 14px;
        background: #fff;
        height: 100%;
    }
    .chart-card canvas {
        width: 100% !important;
        height: 290px !important;
    }
    .financial-table th {
        background: #0f4c5c;
        color: #fff;
        white-space: nowrap;
        font-size: 12px;
    }
    .financial-table td {
        font-size: 12px;
        vertical-align: middle;
    }
    .status-pill {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }
    .status-active { background: #dcfce7; color: #166534; }
    .status-completed { background: #dbeafe; color: #1d4ed8; }
    .status-not_started { background: #fef3c7; color: #92400e; }
    .status-at_risk { background: #fee2e2; color: #b91c1c; }
    .metric-negative { color: #b91c1c !important; font-weight: 700; }
    .metric-warning { color: #b45309 !important; font-weight: 700; }
    .section-note {
        font-size: 12px;
        color: #64748b;
    }
    .ranking-list {
        display: grid;
        gap: 10px;
    }
    .ranking-item {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px 12px;
        background: #fff;
    }
    .ranking-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        font-weight: 700;
        color: #0f172a;
    }
    .ranking-sub {
        font-size: 12px;
        color: #64748b;
        margin-top: 4px;
    }
    .project-link {
        color: #0f4c5c;
        font-weight: 700;
    }
    .legend-note {
        font-size: 11px;
        color: #94a3b8;
    }
    @media (max-width: 767px) {
        .hero-panel {
            padding: 18px;
        }
        .kpi-value {
            font-size: 22px;
        }
    }
</style>
@endsection

@section('content')
@php
    $money = function ($value) {
        return number_format((float) $value, 2);
    };
    $percent = function ($value) {
        return number_format((float) $value, 2) . '%';
    };
    $ratio = function ($value) {
        return $value === null ? 'N/A' : number_format((float) $value, 2) . 'x';
    };
    $statusLabel = function ($value) {
        return strtoupper(str_replace('_', ' ', (string) $value));
    };
    $baseQuery = request()->query();
@endphp

<div class="finance-shell">
    <div class="hero-panel">
        <div class="d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <div class="hero-title">KPI Dashboard</div>
                <div class="hero-subtitle">
                    Executive view of profitability, liquidity, receivables, WIP exposure, backlog health, and project cost control.
                </div>
                <div class="hero-badges">
                    <span class="hero-badge">{{ strtoupper($filters['view_mode']) }} VIEW</span>
                    <span class="hero-badge">{{ \Carbon\Carbon::parse($filters['start_date'])->format('M d, Y') }} TO {{ \Carbon\Carbon::parse($filters['end_date'])->format('M d, Y') }}</span>
                    <span class="hero-badge">{{ $projectRows->count() }} PROJECTS IN SCOPE</span>
                </div>
            </div>
            <div class="mt-3 mt-md-0">
                @if(empty($dashboardMode))
                    <a href="/reports/accounting" class="btn btn-sm reports-btn mr-2">Back to Accounting Reports</a>
                @else
                    <a href="/reports/accounting" class="btn btn-sm reports-btn mr-2">Open Accounting Reports</a>
                @endif
                <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn btn-sm btn-light mr-2">Export Excel</a>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" target="_blank" class="btn btn-sm btn-light">Export PDF</a>
            </div>
        </div>
    </div>

    <div class="card filter-card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ $currentPath ?? url()->current() }}" class="not">
                <div class="row">
                    <div class="col-md-2 form-group">
                        <label class="mb-1">View</label>
                        <select name="view_mode" class="form-control form-control-sm">
                            <option value="ytd" {{ $filters['view_mode'] === 'ytd' ? 'selected' : '' }}>Year-to-Date</option>
                            <option value="monthly" {{ $filters['view_mode'] === 'monthly' ? 'selected' : '' }}>Current Month</option>
                            <option value="custom" {{ $filters['view_mode'] === 'custom' ? 'selected' : '' }}>Custom Range</option>
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="mb-1">Date From</label>
                        <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="mb-1">Date To</label>
                        <input type="date" name="end_date" value="{{ $filters['end_date'] }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="mb-1">Project</label>
                        <select name="project" class="form-control form-control-sm">
                            <option value="">All Projects</option>
                            @foreach($filterOptions['projects'] as $projectOption)
                                <option value="{{ $projectOption->id }}" {{ (string) $filters['project'] === (string) $projectOption->id ? 'selected' : '' }}>
                                    {{ $projectOption->project_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="mb-1">Client</label>
                        <select name="client" class="form-control form-control-sm">
                            <option value="">All Clients</option>
                            @foreach($filterOptions['clients'] as $client)
                                <option value="{{ $client }}" {{ (string) $filters['client'] === (string) $client ? 'selected' : '' }}>{{ $client }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="mb-1">Project Manager</label>
                        <select name="project_manager" class="form-control form-control-sm">
                            <option value="">All Project Managers</option>
                            @foreach($filterOptions['project_managers'] as $manager)
                                <option value="{{ $manager }}" {{ (string) $filters['project_manager'] === (string) $manager ? 'selected' : '' }}>{{ $manager }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="mb-1">Contract Type</label>
                        <select name="contract_type" class="form-control form-control-sm">
                            <option value="">All Contract Types</option>
                            @foreach($filterOptions['contract_types'] as $contractType)
                                <option value="{{ $contractType['value'] }}" {{ (string) $filters['contract_type'] === (string) $contractType['value'] ? 'selected' : '' }}>
                                    {{ $contractType['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="mb-1">Project Status</label>
                        <select name="project_status" class="form-control form-control-sm">
                            <option value="">All Statuses</option>
                            @foreach($filterOptions['project_statuses'] as $status)
                                <option value="{{ $status['value'] }}" {{ (string) $filters['project_status'] === (string) $status['value'] ? 'selected' : '' }}>
                                    {{ $status['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="mb-1">Branch / Region</label>
                        <select name="region" class="form-control form-control-sm">
                            <option value="">All Regions</option>
                            @foreach($filterOptions['regions'] as $region)
                                <option value="{{ $region }}" {{ (string) $filters['region'] === (string) $region ? 'selected' : '' }}>{{ $region }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12 d-flex align-items-center">
                        <button type="submit" class="btn btn-sm reports-btn mr-2">Apply Filters</button>
                        <a href="{{ $currentPath ?? url()->current() }}" class="btn btn-sm btn-light mr-3">Reset</a>
                        <span class="section-note">Contract type is currently mapped from available project master data. Project-specific financials rely on project code tagging in journals and project-linked cost records.</span>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($alertItems->count() > 0)
        <div class="alert-stack mb-3">
            @foreach($alertItems as $alert)
                <div class="alert-box alert-{{ $alert['type'] }}">
                    <div class="font-weight-bold">{{ $alert['title'] }}</div>
                    <div>{{ $alert['message'] }}</div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="section-card card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">1. Executive Financial Summary</h5>
            <span class="legend-note">Primary metrics are based on posted journals and balance positions as of {{ \Carbon\Carbon::parse($filters['end_date'])->format('M d, Y') }}.</span>
        </div>
        <div class="card-body">
            <div class="kpi-grid">
                <div class="kpi-card {{ ($summary['net_margin'] < 5) ? 'alert-warning' : '' }}">
                    <div class="kpi-label">Total Revenue</div>
                    <div class="kpi-value">{{ $money($summary['revenue']) }}</div>
                    <div class="kpi-meta">Revenue growth: {{ $summary['revenue_growth_rate'] === null ? 'N/A' : $percent($summary['revenue_growth_rate']) }}</div>
                </div>
                <div class="kpi-card {{ ($summary['gross_margin'] < 15) ? 'alert-warning' : '' }}">
                    <div class="kpi-label">Gross Profit</div>
                    <div class="kpi-value">{{ $money($summary['gross_profit']) }}</div>
                    <div class="kpi-meta">Gross margin: {{ $percent($summary['gross_margin']) }}</div>
                </div>
                <div class="kpi-card {{ ($summary['net_profit'] < 0) ? 'alert-danger' : (($summary['net_margin'] < 5) ? 'alert-warning' : '') }}">
                    <div class="kpi-label">Net Profit</div>
                    <div class="kpi-value {{ $summary['net_profit'] < 0 ? 'metric-negative' : '' }}">{{ $money($summary['net_profit']) }}</div>
                    <div class="kpi-meta">Net margin: {{ $percent($summary['net_margin']) }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">EBITDA / Operating Profit</div>
                    <div class="kpi-value">{{ $money($summary['ebitda']) }}</div>
                    <div class="kpi-meta">Operating profit: {{ $money($summary['operating_profit']) }}</div>
                </div>
                <div class="kpi-card {{ ($summary['cash_balance'] < $cashFlowAnalytics['burn_rate']) ? 'alert-danger' : '' }}">
                    <div class="kpi-label">Cash Balance</div>
                    <div class="kpi-value {{ $summary['cash_balance'] < $cashFlowAnalytics['burn_rate'] ? 'metric-negative' : '' }}">{{ $money($summary['cash_balance']) }}</div>
                    <div class="kpi-meta">Average monthly burn: {{ $money($cashFlowAnalytics['burn_rate']) }}</div>
                </div>
                <div class="kpi-card {{ ($summary['working_capital'] < 0) ? 'alert-danger' : '' }}">
                    <div class="kpi-label">Working Capital</div>
                    <div class="kpi-value {{ $summary['working_capital'] < 0 ? 'metric-negative' : '' }}">{{ $money($summary['working_capital']) }}</div>
                    <div class="kpi-meta">Current ratio: {{ $ratio($summary['current_ratio']) }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Quick Ratio</div>
                    <div class="kpi-value">{{ $ratio($summary['quick_ratio']) }}</div>
                    <div class="kpi-meta">Debt to equity: {{ $ratio($summary['debt_to_equity']) }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Interest Coverage</div>
                    <div class="kpi-value">{{ $ratio($summary['interest_coverage']) }}</div>
                    <div class="kpi-meta">Direct costs: {{ $money($summary['direct_costs']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-3">
            <div class="chart-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h5 class="mb-1">Revenue, Cost, and Profit Trend</h5>
                        <div class="section-note">Monthly trend across the selected reporting period.</div>
                    </div>
                </div>
                <canvas id="financialTrendChart"></canvas>
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="chart-card">
                <h5 class="mb-1">AR Aging Summary</h5>
                <div class="section-note mb-2">Slow collections pressure cash flow and working capital.</div>
                <canvas id="arAgingChart"></canvas>
            </div>
        </div>
    </div>

    <div class="section-card card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">2. Project Profitability Monitoring</h5>
        </div>
        <div class="card-body">
            <div class="metric-strip mb-3">
                <div class="metric-tile">
                    <div class="title">Revenue per Project</div>
                    <div class="value">{{ $projectRows->count() ? $money($projectRows->avg('revenue_recognized')) : '0.00' }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Actual Cost per Project</div>
                    <div class="value">{{ $projectRows->count() ? $money($projectRows->avg('actual_cost')) : '0.00' }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Estimated Cost per Project</div>
                    <div class="value">{{ $projectRows->count() ? $money($projectRows->avg('estimated_total_cost')) : '0.00' }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Change Order Value</div>
                    <div class="value">{{ $money($projectRows->sum('change_order_value')) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Budget vs Actual Variance</div>
                    <div class="value {{ $projectRows->sum('budget_variance') < 0 ? 'metric-negative' : '' }}">{{ $money($projectRows->sum('budget_variance')) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Cost Overrun %</div>
                    <div class="value {{ $projectRows->avg('cost_overrun') > 10 ? 'metric-warning' : '' }}">{{ $percent($projectRows->avg('cost_overrun')) }}</div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-7 mb-3">
                    <div class="chart-card">
                        <h5 class="mb-1">Project Profitability Comparison</h5>
                        <div class="section-note mb-2">Gross margin and net margin by project.</div>
                        <canvas id="projectProfitChart"></canvas>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="chart-card">
                                <h5 class="mb-2">Top 5 Most Profitable</h5>
                                <div class="ranking-list">
                                    @forelse($projectAnalytics['top_projects'] as $project)
                                        <div class="ranking-item">
                                            <div class="ranking-head">
                                                <span>{{ $project->project_name }}</span>
                                                <span>{{ $percent($project->gross_margin) }}</span>
                                            </div>
                                            <div class="ranking-sub">{{ $project->project_owner }} | Gross profit {{ $money($project->gross_profit) }}</div>
                                        </div>
                                    @empty
                                        <div class="section-note">No project data available.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="chart-card">
                                <h5 class="mb-2">Bottom 5 Lowest Margin</h5>
                                <div class="ranking-list">
                                    @forelse($projectAnalytics['bottom_projects'] as $project)
                                        <div class="ranking-item">
                                            <div class="ranking-head">
                                                <span>{{ $project->project_name }}</span>
                                                <span class="{{ $project->gross_margin < 0 ? 'metric-negative' : '' }}">{{ $percent($project->gross_margin) }}</span>
                                            </div>
                                            <div class="ranking-sub">{{ $project->project_owner }} | Cost overrun {{ $percent($project->cost_overrun) }}</div>
                                        </div>
                                    @empty
                                        <div class="section-note">No project data available.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">3. Cash Flow and Liquidity</h5>
        </div>
        <div class="card-body">
            <div class="metric-strip mb-3">
                <div class="metric-tile">
                    <div class="title">Operating Cash Flow</div>
                    <div class="value {{ $cashFlowAnalytics['operating_cash_flow'] < 0 ? 'metric-negative' : '' }}">{{ $money($cashFlowAnalytics['operating_cash_flow']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Monthly Cash Inflows</div>
                    <div class="value">{{ $money(collect($cashFlowAnalytics['monthly_inflows'])->sum()) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Monthly Cash Outflows</div>
                    <div class="value">{{ $money(collect($cashFlowAnalytics['monthly_outflows'])->sum()) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Net Cash Flow</div>
                    <div class="value {{ $cashFlowAnalytics['net_cash_flow'] < 0 ? 'metric-negative' : '' }}">{{ $money($cashFlowAnalytics['net_cash_flow']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Burn Rate</div>
                    <div class="value">{{ $money($cashFlowAnalytics['burn_rate']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Debt-to-Equity</div>
                    <div class="value">{{ $ratio($summary['debt_to_equity']) }}</div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 mb-3">
                    <div class="chart-card">
                        <h5 class="mb-1">Cash Flow Trend</h5>
                        <div class="section-note mb-2">Monthly inflows, outflows, and net movement.</div>
                        <canvas id="cashFlowChart"></canvas>
                    </div>
                </div>
                <div class="col-lg-4 mb-3">
                    <div class="chart-card">
                        <h5 class="mb-1">Cash Flow Forecast</h5>
                        <div class="section-note mb-2">Average net movement projected over the next 6 months.</div>
                        <canvas id="cashForecastChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">4. Billing, Collection, and Receivables</h5>
        </div>
        <div class="card-body">
            <div class="metric-strip">
                <div class="metric-tile">
                    <div class="title">Total Accounts Receivable</div>
                    <div class="value">{{ $money($receivableMetrics['total_ar']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Days Sales Outstanding</div>
                    <div class="value {{ $receivableMetrics['dso'] !== null && $receivableMetrics['dso'] > 75 ? 'metric-warning' : '' }}">{{ $receivableMetrics['dso'] === null ? 'N/A' : number_format($receivableMetrics['dso'], 1) . ' days' }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Billing vs Collection Gap</div>
                    <div class="value {{ $receivableMetrics['billing_collection_gap'] > 0 ? 'metric-warning' : '' }}">{{ $money($receivableMetrics['billing_collection_gap']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Collection Efficiency</div>
                    <div class="value">{{ $receivableMetrics['collection_efficiency'] === null ? 'N/A' : $percent($receivableMetrics['collection_efficiency']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Retention Receivable</div>
                    <div class="value">{{ $money($receivableMetrics['retention_receivable']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Unbilled Revenue</div>
                    <div class="value">{{ $money($receivableMetrics['unbilled_revenue']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Overdue Invoices</div>
                    <div class="value {{ $receivableMetrics['overdue_invoices'] > 0 ? 'metric-warning' : '' }}">{{ number_format((int) $receivableMetrics['overdue_invoices']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">5. Work in Progress (WIP) and Contract Status</h5>
        </div>
        <div class="card-body">
            <div class="metric-strip mb-3">
                <div class="metric-tile">
                    <div class="title">Total Contract Value</div>
                    <div class="value">{{ $money($wipMetrics['total_contract_value']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Cost Incurred to Date</div>
                    <div class="value">{{ $money($wipMetrics['cost_incurred_to_date']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Estimated Cost to Complete</div>
                    <div class="value">{{ $money($wipMetrics['estimated_cost_to_complete']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Percent Complete</div>
                    <div class="value">{{ $percent($wipMetrics['percent_complete']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Earned Revenue</div>
                    <div class="value">{{ $money($wipMetrics['earned_revenue']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Amount Billed to Date</div>
                    <div class="value">{{ $money($wipMetrics['amount_billed_to_date']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Overbillings</div>
                    <div class="value">{{ $money($wipMetrics['overbillings']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Underbillings</div>
                    <div class="value">{{ $money($wipMetrics['underbillings']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">WIP Gain / Fade</div>
                    <div class="value {{ $wipMetrics['wip_gain_fade'] < 0 ? 'metric-negative' : '' }}">{{ $money($wipMetrics['wip_gain_fade']) }}</div>
                </div>
            </div>
            <div class="chart-card">
                <h5 class="mb-1">WIP Percent Complete vs Billed</h5>
                <div class="section-note mb-2">Comparing operational progress against billing pace helps surface overbilling and underbilling exposure.</div>
                <canvas id="wipChart"></canvas>
            </div>
        </div>
    </div>

    <div class="section-card card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">6. Backlog and Revenue Pipeline</h5>
        </div>
        <div class="card-body">
            <div class="metric-strip mb-3">
                <div class="metric-tile">
                    <div class="title">Total Backlog Value</div>
                    <div class="value">{{ $money($backlogAnalytics['total_backlog_value']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Backlog Gross Profit</div>
                    <div class="value">{{ $money($backlogAnalytics['backlog_gross_profit']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Secured Future Revenue</div>
                    <div class="value">{{ $money($backlogAnalytics['secured_future_revenue']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Backlog Coverage</div>
                    <div class="value">{{ $backlogAnalytics['backlog_coverage_months'] === null ? 'N/A' : number_format($backlogAnalytics['backlog_coverage_months'], 1) . ' mo' }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Bid Pipeline Value</div>
                    <div class="value">{{ $money($backlogAnalytics['bid_pipeline_value']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Bid Win Rate</div>
                    <div class="value">{{ $backlogAnalytics['bid_win_rate'] === null ? 'N/A' : $percent($backlogAnalytics['bid_win_rate']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Average Contract Value</div>
                    <div class="value">{{ $money($backlogAnalytics['average_contract_value']) }}</div>
                </div>
            </div>
            <div class="chart-card">
                <h5 class="mb-1">Backlog by Month</h5>
                <div class="section-note mb-2">Forward visibility of secured revenue spread across expected delivery months.</div>
                <canvas id="backlogChart"></canvas>
            </div>
        </div>
    </div>

    <div class="section-card card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">7. Cost Control and Resource Efficiency</h5>
        </div>
        <div class="card-body">
            <div class="metric-strip mb-3">
                <div class="metric-tile">
                    <div class="title">Labor Cost % of Revenue</div>
                    <div class="value">{{ $percent($costControlAnalytics['labor_cost_pct']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Material Cost % of Revenue</div>
                    <div class="value">{{ $percent($costControlAnalytics['material_cost_pct']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Equipment Cost % of Revenue</div>
                    <div class="value">{{ $percent($costControlAnalytics['equipment_cost_pct']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Subcontractor Cost % of Revenue</div>
                    <div class="value">{{ $percent($costControlAnalytics['subcontractor_cost_pct']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Equipment Utilization Rate</div>
                    <div class="value">{{ $percent($costControlAnalytics['equipment_utilization_rate']) }}</div>
                </div>
                <div class="metric-tile">
                    <div class="title">Idle Equipment Cost</div>
                    <div class="value">{{ $money($costControlAnalytics['idle_equipment_cost']) }}</div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="chart-card">
                        <h5 class="mb-2">Cost per Project Phase</h5>
                        <table class="table table-sm table-bordered mini-table mb-0">
                            <thead>
                                <tr>
                                    <th>Phase</th>
                                    <th class="text-right">Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($costControlAnalytics['cost_per_phase'] as $phase)
                                    <tr>
                                        <td>{{ $phase['phase'] }}</td>
                                        <td class="text-right">{{ $money($phase['amount']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">No phase cost records available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="chart-card">
                        <h5 class="mb-2">Project Margin Watchlist</h5>
                        <table class="table table-sm table-bordered mini-table mb-0">
                            <thead>
                                <tr>
                                    <th>Project</th>
                                    <th class="text-right">Gross Margin</th>
                                    <th class="text-right">Overrun</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projectRows->sortBy('gross_margin')->take(8) as $project)
                                    <tr>
                                        <td>{{ $project->project_name }}</td>
                                        <td class="text-right {{ $project->gross_margin < 0 ? 'metric-negative' : '' }}">{{ $percent($project->gross_margin) }}</td>
                                        <td class="text-right {{ $project->cost_overrun > 10 ? 'metric-warning' : '' }}">{{ $percent($project->cost_overrun) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No projects available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0">Project Financial Detail</h5>
                <div class="section-note">Click a project name to isolate its dashboard view.</div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="construction_financial_detail_table" class="table table-bordered table-hover financial-table mb-0">
                    <thead>
                        <tr>
                            <th>Project Name</th>
                            <th>Client</th>
                            <th>Contract Value</th>
                            <th>Revenue Recognized</th>
                            <th>Actual Cost</th>
                            <th>Estimated Total Cost</th>
                            <th>Gross Margin %</th>
                            <th>Percent Complete</th>
                            <th>Billed to Date</th>
                            <th>Collected to Date</th>
                            <th>Over/Under Billing</th>
                            <th>Backlog Remaining</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projectRows as $project)
                            <tr>
                                <td>
                                    <a class="project-link" href="{{ request()->fullUrlWithQuery(['project' => $project->id, 'client' => null, 'project_manager' => null, 'contract_type' => null, 'project_status' => null, 'region' => null]) }}">
                                        {{ $project->project_name }}
                                    </a>
                                    <div class="legend-note">{{ $project->project_code }} | {{ $project->project_in_charge ?: 'No PM' }}</div>
                                </td>
                                <td>{{ $project->project_owner ?: '-' }}</td>
                                <td class="text-right">{{ $money($project->contract_value) }}</td>
                                <td class="text-right">{{ $money($project->revenue_recognized) }}</td>
                                <td class="text-right">{{ $money($project->actual_cost) }}</td>
                                <td class="text-right">{{ $money($project->estimated_total_cost) }}</td>
                                <td class="text-right {{ $project->gross_margin < 0 ? 'metric-negative' : ($project->gross_margin < 10 ? 'metric-warning' : '') }}">{{ $percent($project->gross_margin) }}</td>
                                <td class="text-right">{{ $percent($project->percent_complete) }}</td>
                                <td class="text-right">{{ $money($project->billed_to_date) }}</td>
                                <td class="text-right">{{ $money($project->collected_to_date) }}</td>
                                <td class="text-right {{ $project->over_under_billing < 0 ? 'metric-warning' : '' }}">{{ $money($project->over_under_billing) }}</td>
                                <td class="text-right">{{ $money($project->backlog_remaining) }}</td>
                                <td><span class="status-pill status-{{ $project->status }}">{{ $statusLabel($project->status) }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-center">No construction financial records found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('chart-js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    $(function () {
        if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#construction_financial_detail_table')) {
            $('#construction_financial_detail_table').DataTable({
                pageLength: 10,
                order: [[6, 'asc']]
            });
        }

        var currencyFormat = function(value) {
            return Number(value || 0).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        };

        var trendSeries = @json($charts['trend']);
        var profitabilitySeries = @json($charts['project_profitability']);
        var cashSeries = @json($charts['cash_flow']);
        var arSeries = @json($charts['ar_aging']);
        var wipSeries = @json($charts['wip']);
        var backlogSeries = @json($charts['backlog']);
        var forecastSeries = @json($cashFlowAnalytics['forecast']);

        new Chart(document.getElementById('financialTrendChart'), {
            type: 'line',
            data: {
                labels: trendSeries.map(function(item) { return item.label; }),
                datasets: [
                    {
                        label: 'Revenue',
                        data: trendSeries.map(function(item) { return item.revenue; }),
                        borderColor: '#0f4c5c',
                        backgroundColor: 'rgba(15, 76, 92, 0.12)',
                        tension: 0.32,
                        fill: true
                    },
                    {
                        label: 'Cost',
                        data: trendSeries.map(function(item) { return item.cost; }),
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.08)',
                        tension: 0.32,
                        fill: false
                    },
                    {
                        label: 'Profit',
                        data: trendSeries.map(function(item) { return item.profit; }),
                        borderColor: '#84cc16',
                        backgroundColor: 'rgba(132, 204, 22, 0.1)',
                        tension: 0.32,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + currencyFormat(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: function(value) { return currencyFormat(value); }
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('projectProfitChart'), {
            type: 'bar',
            data: {
                labels: profitabilitySeries.map(function(item) { return item.label; }),
                datasets: [
                    {
                        label: 'Gross Margin %',
                        data: profitabilitySeries.map(function(item) { return item.gross_margin; }),
                        backgroundColor: '#1f6f78'
                    },
                    {
                        label: 'Net Margin %',
                        data: profitabilitySeries.map(function(item) { return item.net_margin; }),
                        backgroundColor: '#d9ed92'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y: {
                        ticks: {
                            callback: function(value) { return value + '%'; }
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('cashFlowChart'), {
            data: {
                labels: cashSeries.map(function(item) { return item.label; }),
                datasets: [
                    {
                        type: 'bar',
                        label: 'Inflows',
                        data: cashSeries.map(function(item) { return item.inflow; }),
                        backgroundColor: '#22c55e'
                    },
                    {
                        type: 'bar',
                        label: 'Outflows',
                        data: cashSeries.map(function(item) { return item.outflow; }),
                        backgroundColor: '#f97316'
                    },
                    {
                        type: 'line',
                        label: 'Net',
                        data: cashSeries.map(function(item) { return item.net; }),
                        borderColor: '#1d4ed8',
                        backgroundColor: 'rgba(29, 78, 216, 0.08)',
                        tension: 0.28
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + currencyFormat(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: function(value) { return currencyFormat(value); }
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('cashForecastChart'), {
            type: 'line',
            data: {
                labels: forecastSeries.map(function(item) { return item.label; }),
                datasets: [{
                    label: 'Projected Net Cash Flow',
                    data: forecastSeries.map(function(item) { return item.net; }),
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, 0.12)',
                    fill: true,
                    tension: 0.22
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: function(value) { return currencyFormat(value); }
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('arAgingChart'), {
            type: 'doughnut',
            data: {
                labels: ['Current', '31-60 Days', '61-90 Days', '91+ Days'],
                datasets: [{
                    data: [arSeries.current, arSeries['31_60'], arSeries['61_90'], arSeries['91_plus']],
                    backgroundColor: ['#0f4c5c', '#38bdf8', '#f59e0b', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + currencyFormat(context.parsed);
                            }
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('wipChart'), {
            data: {
                labels: wipSeries.map(function(item) { return item.label; }),
                datasets: [
                    {
                        type: 'bar',
                        label: 'Percent Complete',
                        data: wipSeries.map(function(item) { return item.percent_complete; }),
                        backgroundColor: '#0f766e'
                    },
                    {
                        type: 'line',
                        label: 'Billed %',
                        data: wipSeries.map(function(item) { return item.billed_percent; }),
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.08)',
                        tension: 0.24
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                },
                scales: {
                    y: {
                        max: 100,
                        ticks: {
                            callback: function(value) { return value + '%'; }
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('backlogChart'), {
            type: 'bar',
            data: {
                labels: backlogSeries.map(function(item) { return item.label; }),
                datasets: [{
                    label: 'Backlog Value',
                    data: backlogSeries.map(function(item) { return item.amount; }),
                    backgroundColor: '#1f6f78'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + currencyFormat(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: function(value) { return currencyFormat(value); }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
