@extends('layouts.app')

@section('styles')
<style>
    :root {
        --mediq-primary: #2d6cdf;
        --mediq-primary-dark: #1f56b8;
        --mediq-primary-soft: #eaf2ff;
        --mediq-accent: #56b6d9;
        --mediq-success: #33a37a;
        --mediq-surface: #ffffff;
        --mediq-surface-soft: rgba(255, 255, 255, 0.72);
        --mediq-border: #d8e4f2;
        --mediq-border-strong: #c6d8eb;
        --mediq-text: #19324f;
        --mediq-text-soft: #61758d;
        --mediq-bg: #f5f9ff;
        --mediq-bg-alt: #edf5ff;
        --mediq-shadow: 0 28px 70px rgba(33, 74, 131, 0.12);
        --mediq-card-shadow: 0 22px 60px rgba(40, 88, 148, 0.14);
        --mediq-error-bg: #fff3f3;
        --mediq-error-text: #c54949;
    }

    html, body, #app {
        min-height: 100%;
        height: 100%;
    }

    body {
        margin: 0;
        color: var(--mediq-text);
        background:
            radial-gradient(circle at top left, rgba(86, 182, 217, 0.16), transparent 24%),
            radial-gradient(circle at bottom right, rgba(45, 108, 223, 0.1), transparent 24%),
            linear-gradient(180deg, #fbfdff 0%, #f2f7fd 100%);
    }

    main.py-4 {
        min-height: 100vh;
        padding: 0 !important;
        background: transparent !important;
    }

    .login-shell {
        min-height: 100vh;
        padding: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .login-shell::before,
    .login-shell::after {
        content: "";
        position: absolute;
        border-radius: 999px;
        pointer-events: none;
        filter: blur(12px);
    }

    .login-shell::before {
        width: 360px;
        height: 360px;
        top: -120px;
        right: -90px;
        background: rgba(86, 182, 217, 0.2);
    }

    .login-shell::after {
        width: 320px;
        height: 320px;
        left: -80px;
        bottom: -120px;
        background: rgba(45, 108, 223, 0.14);
    }

    .login-layout {
        width: min(1320px, 100%);
        min-height: calc(100vh - 48px);
        display: grid;
        grid-template-columns: minmax(0, 1.08fr) minmax(430px, 0.92fr);
        border-radius: 32px;
        overflow: hidden;
        border: 1px solid rgba(215, 227, 240, 0.96);
        background: rgba(255, 255, 255, 0.82);
        backdrop-filter: blur(18px);
        box-shadow: var(--mediq-shadow);
        position: relative;
        z-index: 1;
    }

    .login-brand-panel {
        position: relative;
        padding: 42px 48px 34px;
        background:
            linear-gradient(150deg, rgba(255,255,255,0.98) 0%, rgba(244,249,255,0.96) 52%, rgba(236,245,255,0.96) 100%);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .login-brand-panel::before {
        content: "";
        position: absolute;
        inset: 38px 42px auto auto;
        width: 230px;
        height: 230px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(45, 108, 223, 0.1) 0%, rgba(45, 108, 223, 0) 72%);
        pointer-events: none;
    }

    .brand-top,
    .brand-bottom {
        position: relative;
        z-index: 1;
    }

    .brand-mark {
        display: inline-flex;
        align-items: center;
        gap: 18px;
        margin-bottom: 22px;
    }

    .brand-mark-frame {
        width: 78px;
        height: 78px;
        border-radius: 22px;
        background: linear-gradient(180deg, #ffffff 0%, #f1f7ff 100%);
        border: 1px solid rgba(201, 219, 240, 0.95);
        box-shadow: 0 14px 34px rgba(45, 108, 223, 0.1);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
    }

    .brand-mark-frame img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .brand-kicker {
        margin: 0 0 8px;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.18em;
        color: var(--mediq-primary);
    }

    .brand-name {
        margin: 0;
        font-size: clamp(2rem, 2.9vw, 3.25rem);
        line-height: 1.02;
        letter-spacing: -0.04em;
        font-weight: 800;
        color: #163250;
    }

    .brand-subtitle {
        margin: 14px 0 8px;
        font-size: 1.06rem;
        font-weight: 700;
        color: #28517b;
    }

    .brand-copy {
        max-width: 620px;
        margin: 0;
        font-size: 0.97rem;
        line-height: 1.75;
        color: var(--mediq-text-soft);
    }

    .brand-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
    }

    .brand-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.82);
        border: 1px solid rgba(205, 220, 239, 0.95);
        color: #30506e;
        font-size: 0.88rem;
        font-weight: 700;
        box-shadow: 0 10px 20px rgba(40, 88, 148, 0.06);
    }

    .brand-grid {
        margin-top: 28px;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .feature-card {
        padding: 18px;
        border-radius: 22px;
        background: var(--mediq-surface-soft);
        border: 1px solid rgba(206, 220, 238, 0.96);
        box-shadow: 0 14px 28px rgba(39, 84, 140, 0.08);
    }

    .feature-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        background: linear-gradient(135deg, #edf5ff 0%, #dbecff 100%);
        color: var(--mediq-primary);
    }

    .feature-card h3 {
        margin: 0 0 8px;
        font-size: 1rem;
        line-height: 1.3;
        font-weight: 700;
        color: #1e3b5c;
    }

    .feature-card p {
        margin: 0;
        font-size: 0.9rem;
        line-height: 1.55;
        color: var(--mediq-text-soft);
    }

    .brand-visual {
        margin-top: 20px;
        padding: 20px;
        border-radius: 26px;
        background: linear-gradient(145deg, #ffffff 0%, #f2f8ff 100%);
        border: 1px solid rgba(205, 220, 239, 0.96);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.85), 0 16px 34px rgba(45, 108, 223, 0.08);
        overflow: hidden;
        position: relative;
    }

    .brand-visual::after {
        content: "";
        position: absolute;
        width: 180px;
        height: 180px;
        right: -48px;
        top: -52px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(86, 182, 217, 0.22), rgba(86, 182, 217, 0));
        pointer-events: none;
    }

    .visual-stat {
        display: grid;
        grid-template-columns: 1.1fr 0.9fr;
        gap: 18px;
        align-items: stretch;
    }

    .stat-panel {
        padding: 18px;
        border-radius: 20px;
        background: #fff;
        border: 1px solid rgba(210, 224, 240, 0.96);
        box-shadow: 0 12px 24px rgba(40, 88, 148, 0.06);
    }

    .stat-panel-label {
        display: block;
        margin-bottom: 8px;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #6e88a5;
    }

    .stat-panel-value {
        display: block;
        font-size: 1.24rem;
        font-weight: 800;
        line-height: 1.2;
        color: #1b3757;
    }

    .stat-panel-footnote {
        display: block;
        margin-top: 8px;
        font-size: 0.85rem;
        line-height: 1.5;
        color: var(--mediq-text-soft);
    }

    .visual-chart {
        border-radius: 22px;
        padding: 18px;
        background: linear-gradient(180deg, #2d6cdf 0%, #4f8cf2 100%);
        box-shadow: 0 18px 36px rgba(45, 108, 223, 0.24);
        position: relative;
        overflow: hidden;
    }

    .visual-chart::before {
        content: "";
        position: absolute;
        width: 140px;
        height: 140px;
        border-radius: 50%;
        right: -34px;
        bottom: -50px;
        background: rgba(255,255,255,0.18);
    }

    .visual-chart-label,
    .visual-chart-value {
        position: relative;
        z-index: 1;
        display: block;
        color: #ffffff;
    }

    .visual-chart-label {
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        opacity: 0.82;
    }

    .visual-chart-value {
        margin-top: 10px;
        font-size: 1.36rem;
        line-height: 1.25;
        font-weight: 800;
    }

    .chart-bars {
        position: absolute;
        left: 18px;
        right: 18px;
        bottom: 18px;
        height: 58px;
        display: flex;
        align-items: end;
        gap: 8px;
        z-index: 1;
    }

    .chart-bars span {
        flex: 1;
        border-radius: 999px 999px 10px 10px;
        background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(255,255,255,0.38));
    }

    .chart-bars span:nth-child(1) { height: 38%; }
    .chart-bars span:nth-child(2) { height: 54%; }
    .chart-bars span:nth-child(3) { height: 72%; }
    .chart-bars span:nth-child(4) { height: 66%; }
    .chart-bars span:nth-child(5) { height: 86%; }

    .brand-footer {
        margin-top: 18px;
        display: flex;
        flex-wrap: wrap;
        gap: 12px 26px;
        color: #71869f;
        font-size: 0.84rem;
    }

    .login-form-panel {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 28px;
        background: linear-gradient(180deg, rgba(251, 253, 255, 0.96) 0%, rgba(241, 247, 255, 0.98) 100%);
    }

    .login-card {
        width: 100%;
        max-width: 520px;
        padding: 38px 36px 34px;
        border-radius: 30px;
        border: 1px solid rgba(209, 223, 239, 0.96);
        background: rgba(255,255,255,0.84);
        backdrop-filter: blur(16px);
        box-shadow: var(--mediq-card-shadow);
        position: relative;
        overflow: hidden;
    }

    .login-card::before {
        content: "";
        position: absolute;
        inset: 0 auto auto 0;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        transform: translate(-45%, -48%);
        background: radial-gradient(circle, rgba(86, 182, 217, 0.18), rgba(86, 182, 217, 0));
        pointer-events: none;
    }

    .login-card-header {
        position: relative;
        z-index: 1;
        margin-bottom: 28px;
    }

    .login-card-logo {
        width: 76px;
        height: 76px;
        object-fit: contain;
        display: inline-block;
        margin-bottom: 18px;
        padding: 10px;
        border-radius: 20px;
        background: linear-gradient(180deg, #ffffff 0%, #eff7ff 100%);
        border: 1px solid rgba(205, 220, 239, 0.96);
        box-shadow: 0 12px 26px rgba(45, 108, 223, 0.1);
    }

    .login-card-title {
        margin: 0;
        font-size: 2.15rem;
        line-height: 1.08;
        letter-spacing: -0.04em;
        font-weight: 800;
        color: #153352;
    }

    .login-card-subtitle {
        margin: 10px 0 0;
        max-width: 420px;
        font-size: 0.98rem;
        line-height: 1.65;
        color: var(--mediq-text-soft);
    }

    .login-alert {
        margin-bottom: 18px;
        padding: 14px 16px;
        border-radius: 16px;
        border: 1px solid rgba(55, 163, 122, 0.18);
        background: rgba(236, 250, 244, 0.94);
        color: #236f56;
        font-size: 0.93rem;
    }

    .login-field {
        margin-bottom: 18px;
    }

    .login-label {
        display: block;
        margin-bottom: 8px;
        font-size: 0.91rem;
        font-weight: 700;
        color: #274869;
    }

    .input-shell {
        position: relative;
    }

    .input-icon {
        position: absolute;
        top: 50%;
        left: 16px;
        transform: translateY(-50%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #8aa2be;
        pointer-events: none;
    }

    .login-input {
        width: 100%;
        height: 58px;
        border-radius: 16px;
        border: 1px solid var(--mediq-border-strong);
        background: rgba(255, 255, 255, 0.94);
        color: var(--mediq-text);
        font-size: 1rem;
        padding: 0 16px 0 50px;
        box-shadow: inset 0 1px 2px rgba(27, 55, 87, 0.03);
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease, transform 0.2s ease;
    }

    .login-input::placeholder {
        color: #9aaabe;
    }

    .login-input:focus {
        outline: none;
        border-color: rgba(45, 108, 223, 0.68);
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(45, 108, 223, 0.12);
    }

    .password-shell .login-input {
        padding-right: 54px;
    }

    .password-toggle {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        width: 40px;
        height: 40px;
        border: 0;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        color: #7e95af;
        cursor: pointer;
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .password-toggle:hover,
    .password-toggle:focus {
        outline: none;
        color: var(--mediq-primary);
        background: rgba(45, 108, 223, 0.08);
    }

    .field-error {
        display: block;
        margin-top: 8px;
        font-size: 0.88rem;
        color: var(--mediq-error-text);
    }

    .login-input.is-invalid {
        border-color: rgba(197, 73, 73, 0.42);
        background: var(--mediq-error-bg);
    }

    .login-options {
        margin: 10px 0 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .remember-shell {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        font-size: 0.92rem;
        color: var(--mediq-text-soft);
        cursor: pointer;
    }

    .remember-shell input {
        width: 16px;
        height: 16px;
        accent-color: var(--mediq-primary);
    }

    .link-subtle {
        color: var(--mediq-primary);
        font-size: 0.92rem;
        font-weight: 700;
        text-decoration: none;
        transition: color 0.2s ease, opacity 0.2s ease;
    }

    .link-subtle:hover,
    .link-subtle:focus {
        color: var(--mediq-primary-dark);
        text-decoration: none;
    }

    .login-button {
        width: 100%;
        height: 58px;
        border: 0;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: linear-gradient(135deg, var(--mediq-primary) 0%, #4e8df1 100%);
        color: #ffffff;
        font-size: 1rem;
        font-weight: 800;
        letter-spacing: 0.01em;
        box-shadow: 0 18px 30px rgba(45, 108, 223, 0.22);
        transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
    }

    .login-button:hover,
    .login-button:focus {
        outline: none;
        transform: translateY(-1px);
        box-shadow: 0 20px 34px rgba(45, 108, 223, 0.26);
    }

    .login-button:active {
        transform: translateY(0);
    }

    .login-button[disabled] {
        opacity: 0.92;
        cursor: wait;
        transform: none;
    }

    .button-spinner {
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255,255,255,0.4);
        border-top-color: #fff;
        border-radius: 50%;
        display: none;
        animation: login-spin 0.8s linear infinite;
    }

    .login-button.is-loading .button-spinner {
        display: inline-block;
    }

    .form-footnote {
        margin-top: 18px;
        font-size: 0.85rem;
        line-height: 1.6;
        color: #7a8ea7;
    }

    @keyframes login-spin {
        to { transform: rotate(360deg); }
    }

    @media (max-width: 1199.98px) {
        .login-layout {
            grid-template-columns: minmax(0, 1fr) minmax(420px, 0.92fr);
        }

        .login-brand-panel {
            padding: 34px 34px 28px;
        }
    }

    @media (max-width: 991.98px) {
        .login-shell {
            padding: 18px;
        }

        .login-layout {
            min-height: auto;
            grid-template-columns: 1fr;
        }

        .login-form-panel {
            order: 1;
            padding: 18px;
        }

        .login-brand-panel {
            order: 2;
            padding: 24px 20px 22px;
        }

        .brand-grid,
        .visual-stat {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .login-shell {
            padding: 12px;
        }

        .login-layout {
            border-radius: 24px;
        }

        .login-brand-panel {
            padding: 20px 16px 18px;
        }

        .login-card {
            padding: 28px 20px 24px;
            border-radius: 24px;
        }

        .login-card-title {
            font-size: 1.78rem;
        }

        .login-options {
            flex-direction: column;
            align-items: flex-start;
        }

        .brand-mark {
            align-items: flex-start;
        }

        .brand-mark-frame {
            width: 64px;
            height: 64px;
            border-radius: 18px;
        }

        .brand-visual {
            padding: 16px;
        }
    }
</style>
@endsection

@section('content')
<div class="login-shell">
    <div class="login-layout">
        <aside class="login-brand-panel">
            <div class="brand-top">
                <div class="brand-mark">
                    <div class="brand-mark-frame">
                        <img src="{{ asset('images/logo.png') }}" alt="MedIQ Smart System logo">
                    </div>
                    <div>
                        <p class="brand-kicker">Healthcare Information Platform</p>
                        <h1 class="brand-name">MedIQ Smart System</h1>
                    </div>
                </div>

                <p class="brand-subtitle">Secure, elegant access for modern healthcare operations.</p>
                <p class="brand-copy">
                    Welcome to MedIQ Smart System. Secure access to your healthcare information platform for patient coordination, clinical workflows, scheduling, reporting, and daily operational visibility.
                </p>

                <div class="brand-badges">
                    <span class="brand-badge">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 3l7 4v5c0 5-3.5 8-7 9-3.5-1-7-4-7-9V7l7-4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        </svg>
                        Secure sign-in
                    </span>
                    <span class="brand-badge">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Connected care workflows
                    </span>
                    <span class="brand-badge">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M7 12h10M7 16h6M7 8h10M5 4h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Enterprise-ready records
                    </span>
                </div>

                <div class="brand-grid">
                    <div class="feature-card">
                        <div class="feature-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <path d="M4 12h16M12 4v16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h3>Clinical Operations</h3>
                        <p>Manage healthcare workflows with structured access, consistent records, and dependable daily execution.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <path d="M7 4v16M17 4v16M4 8h16M4 16h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h3>Scheduling &amp; Coordination</h3>
                        <p>Keep teams aligned with cleaner scheduling, care visibility, and coordinated administrative operations.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <path d="M5 19V9m7 10V5m7 14v-7M3 21h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h3>Reporting &amp; Oversight</h3>
                        <p>Review healthcare operations, performance indicators, and records through a clear management workspace.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <path d="M12 21c4.97 0 9-4.03 9-9s-4.03-9-9-9-9 4.03-9 9 4.03 9 9 9Z" stroke="currentColor" stroke-width="2"/>
                                <path d="M12 7v5l3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h3>Reliable System Access</h3>
                        <p>Designed for secure sign-in, role-based navigation, and a trustworthy healthcare software experience.</p>
                    </div>
                </div>

                <div class="brand-visual" aria-hidden="true">
                    <div class="visual-stat">
                        <div class="stat-panel">
                            <span class="stat-panel-label">Care Workspace</span>
                            <span class="stat-panel-value">Trustworthy. Structured. Ready for daily care delivery.</span>
                            <span class="stat-panel-footnote">Built for clinics, administrators, and healthcare teams that need a calm and dependable platform.</span>
                        </div>
                        <div class="visual-chart">
                            <span class="visual-chart-label">System Health</span>
                            <span class="visual-chart-value">Connected access for records, scheduling, and operations.</span>
                            <div class="chart-bars">
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="brand-bottom">
                <div class="brand-footer">
                    <span>MedIQ Smart System</span>
                    <span>Protected healthcare access</span>
                    <span>Support: support@mediq.local</span>
                </div>
            </div>
        </aside>

        <section class="login-form-panel">
            <div class="login-card">
                <div class="login-card-header">
                    <img class="login-card-logo" src="{{ asset('images/logo.png') }}" alt="MedIQ Smart System logo">
                    <h2 class="login-card-title">Welcome to MedIQ Smart System</h2>
                    <p class="login-card-subtitle">Secure access to your healthcare information platform.</p>
                </div>

                @if (session('status'))
                    <div class="login-alert" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    <div class="login-field">
                        <label class="login-label" for="email">Email Address</label>
                        <div class="input-shell">
                            <span class="input-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                    <path d="M4 6h16v12H4V6Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    <path d="m5 7 7 6 7-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <input
                                id="email"
                                type="email"
                                class="login-input @error('email') is-invalid @enderror"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="Enter your email address"
                                required
                                autocomplete="email"
                                autofocus
                            >
                        </div>
                        @error('email')
                            <span class="field-error" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="login-field">
                        <label class="login-label" for="password">Password</label>
                        <div class="input-shell password-shell">
                            <span class="input-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                    <path d="M7 11V8a5 5 0 1 1 10 0v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M5 11h14v9H5v-9Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <input
                                id="password"
                                type="password"
                                class="login-input @error('password') is-invalid @enderror"
                                name="password"
                                placeholder="Enter your password"
                                required
                                autocomplete="current-password"
                            >
                            <button type="button" class="password-toggle" id="togglePassword" aria-label="Show password" aria-controls="password" aria-pressed="false">
                                <svg id="passwordIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6-10-6-10-6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <span class="field-error" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="login-options">
                        <label class="remember-shell" for="remember">
                            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <span>Remember me</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="link-subtle" href="{{ route('password.request') }}">Forgot Password?</a>
                        @endif
                    </div>

                    <button type="submit" class="login-button" id="loginButton">
                        <span class="button-spinner" aria-hidden="true"></span>
                        <span class="button-text">Sign In</span>
                    </button>

                    <p class="form-footnote">
                        Access is protected and intended for authorized MedIQ Smart System users only.
                    </p>
                </form>
            </div>
        </section>
    </div>
</div>

<script>
    (function () {
        var passwordInput = document.getElementById('password');
        var toggleButton = document.getElementById('togglePassword');
        var loginForm = document.getElementById('loginForm');
        var loginButton = document.getElementById('loginButton');

        if (toggleButton && passwordInput) {
            toggleButton.addEventListener('click', function () {
                var isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                toggleButton.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
                toggleButton.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
            });
        }

        if (loginForm && loginButton) {
            loginForm.addEventListener('submit', function () {
                loginButton.setAttribute('disabled', 'disabled');
                loginButton.classList.add('is-loading');
            });
        }
    })();
</script>
@endsection
