@extends('backend.pages.reports.construction_financial_dashboard')

@section('title', 'DASHBOARD')

@section('styles')
@parent
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
        padding: 12px 14px 18px !important;
    }
    .app-content-column-full {
        padding: 0 2px !important;
        height: 100% !important;
        max-height: none !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
    }
    .app-content-column-full > * {
        min-height: 100%;
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
        .app-content-row {
            padding: 16px 18px 22px !important;
        }
        .app-content-column-full {
            padding: 0 4px !important;
        }
    }
</style>
@endsection

@section('breadcrumbs')
    <span>Dashboard</span> / <span class="highlight">KPI Dashboard</span>
@endsection
