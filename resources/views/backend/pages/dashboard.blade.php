@extends('backend.pages.reports.construction_financial_dashboard')

@section('title', 'DASHBOARD')

@section('styles')
@parent
<style>
    html,
    body,
    .wrapper,
    .wrapper > .main {
        height: auto !important;
        min-height: 100% !important;
        overflow: visible !important;
    }
    .wrapper > .main {
        display: block !important;
    }
    .wrapper > .main > .row {
        padding: 12px 14px 18px !important;
        height: auto !important;
        min-height: 0 !important;
        overflow: visible !important;
    }
    .wrapper > .main > .row > .col-xl-12 {
        padding: 0 2px !important;
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
    }
    .wrapper > .main > .row > .col-xl-12 > * {
        height: auto !important;
        overflow: visible !important;
    }
    .finance-shell {
        padding: 0 0 24px !important;
        overflow: visible !important;
    }
    .hero-panel,
    .filter-card,
    .section-card {
        border-radius: 14px;
    }
    .hero-panel {
        margin-bottom: 14px;
    }
    .section-card,
    .filter-card {
        margin-bottom: 14px !important;
    }
    .chart-card {
        border-radius: 14px;
    }
    @media (min-width: 1200px) {
        .wrapper > .main > .row {
            padding: 16px 18px 22px !important;
        }
        .wrapper > .main > .row > .col-xl-12 {
            padding: 0 4px !important;
        }
    }
</style>
@endsection

@section('breadcrumbs')
    <span>Dashboard</span> / <span class="highlight">KPI Dashboard</span>
@endsection
