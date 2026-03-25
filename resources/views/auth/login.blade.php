@extends('layouts.app')

@section('styles')
<style>
    :root {
        --login-primary: #a8142b;
        --login-primary-dark: #7f0f20;
        --login-primary-soft: #f9ebee;
        --login-brand-dark: #3a3d44;
        --login-brand-dark-alt: #575b63;
        --login-surface: #ffffff;
        --login-surface-muted: rgba(255, 255, 255, 0.72);
        --login-border: #ddd7db;
        --login-text: #2c3138;
        --login-text-soft: #676b73;
        --login-success: #e7f6ee;
        --login-error: #fff2f2;
        --login-error-text: #b84040;
        --login-shadow: 0 28px 70px rgba(74, 32, 39, 0.12);
        --login-card-shadow: 0 20px 45px rgba(44, 49, 56, 0.18);
    }

    html, body, #app {
        height: 100%;
        min-height: 100%;
        overflow: hidden;
    }

    body {
        margin: 0;
        color: var(--login-text);
        background:
            radial-gradient(circle at top left, rgba(168, 20, 43, 0.08), transparent 24%),
            radial-gradient(circle at bottom right, rgba(140, 145, 156, 0.1), transparent 20%),
            linear-gradient(180deg, #ffffff 0%, #f5f3f4 100%);
    }

    main.py-4 {
        height: 100vh;
        min-height: 100vh;
        padding: 0 !important;
        background: transparent !important;
        overflow: hidden;
    }

    .login-shell {
        height: 100vh;
        display: flex;
        align-items: stretch;
        justify-content: center;
        padding: 18px;
        position: relative;
        overflow: hidden;
    }

    .login-shell::before,
    .login-shell::after {
        content: "";
        position: absolute;
        border-radius: 999px;
        filter: blur(8px);
        pointer-events: none;
    }

    .login-shell::before {
        width: 320px;
        height: 320px;
        top: -100px;
        left: -80px;
        background: rgba(168, 20, 43, 0.1);
    }

    .login-shell::after {
        width: 260px;
        height: 260px;
        right: -70px;
        bottom: -70px;
        background: rgba(121, 126, 138, 0.14);
    }

    .login-layout {
        width: min(1380px, 100%);
        height: calc(100vh - 36px);
        display: grid;
        grid-template-columns: minmax(0, 1.3fr) minmax(480px, 1fr);
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid rgba(217, 228, 242, 0.9);
        border-radius: 32px;
        box-shadow: 0 30px 80px rgba(52, 40, 44, 0.14);
        backdrop-filter: blur(14px);
        overflow: hidden;
        position: relative;
        z-index: 1;
    }

    .login-brand-panel {
        position: relative;
        padding: 34px 40px 26px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background:
            linear-gradient(160deg, rgba(255,255,255,0.95) 0%, rgba(247, 241, 242, 0.93) 48%, rgba(241, 239, 240, 0.9) 100%);
    }

    .login-brand-panel::before,
    .login-brand-panel::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
    }

    .login-brand-panel::before {
        width: 320px;
        height: 320px;
        top: -100px;
        right: -120px;
        background: radial-gradient(circle, rgba(168, 20, 43, 0.14) 0%, rgba(168, 20, 43, 0) 70%);
    }

    .login-brand-panel::after {
        width: 260px;
        height: 260px;
        bottom: -90px;
        left: -100px;
        background: radial-gradient(circle, rgba(145, 149, 158, 0.18) 0%, rgba(145, 149, 158, 0) 70%);
    }

    .brand-top,
    .brand-bottom {
        position: relative;
        z-index: 1;
    }

    .brand-mark {
        display: inline-flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 18px;
    }

    .brand-mark img {
        width: 64px;
        height: 64px;
        object-fit: contain;
        border-radius: 18px;
        padding: 8px;
        background: rgba(255, 255, 255, 0.85);
        box-shadow: 0 12px 30px rgba(87, 91, 99, 0.12);
    }

    .brand-kicker {
        margin: 0 0 8px;
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: var(--login-primary);
    }

    .brand-name {
        margin: 0;
        font-size: clamp(1.9rem, 3vw, 2.85rem);
        line-height: 1.04;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #2b3138;
    }

    .brand-subtitle {
        margin: 12px 0 8px;
        font-size: 1rem;
        font-weight: 700;
        color: #5d222c;
    }

    .brand-copy {
        max-width: 600px;
        margin: 0;
        font-size: 0.95rem;
        line-height: 1.65;
        color: var(--login-text-soft);
    }

    .brand-insights {
        margin-top: 22px;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .insight-card {
        padding: 14px 14px 12px;
        border: 1px solid rgba(217, 228, 242, 0.9);
        border-radius: 18px;
        background: var(--login-surface-muted);
        box-shadow: 0 14px 30px rgba(97, 79, 84, 0.08);
    }

    .insight-icon {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
        background: linear-gradient(135deg, #f7ecee 0%, #ece8ea 100%);
        color: var(--login-primary);
    }

    .insight-card h3 {
        margin: 0 0 6px;
        font-size: 0.96rem;
        font-weight: 700;
        color: #31363d;
    }

    .insight-card p {
        margin: 0;
        font-size: 0.9rem;
        line-height: 1.45;
        color: var(--login-text-soft);
    }

    .brand-visual {
        margin-top: 16px;
        padding: 14px;
        border-radius: 20px;
        border: 1px solid rgba(217, 228, 242, 0.9);
        background: linear-gradient(145deg, rgba(255,255,255,0.92), rgba(245,243,244,0.96));
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.9);
    }

    .visual-grid {
        display: grid;
        grid-template-columns: 1.1fr .9fr;
        gap: 16px;
        align-items: end;
    }

    .metric-stack {
        display: grid;
        gap: 10px;
    }

    .metric-card {
        padding: 14px 16px;
        background: #fff;
        border: 1px solid rgba(217, 228, 242, 0.95);
        border-radius: 16px;
        box-shadow: 0 10px 24px rgba(74, 61, 65, 0.08);
    }

    .metric-label {
        display: block;
        margin-bottom: 8px;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #6e84a0;
    }

    .metric-value {
        display: block;
        font-size: 1.2rem;
        line-height: 1;
        font-weight: 800;
        color: #373b42;
    }

    .metric-footnote {
        display: block;
        margin-top: 6px;
        font-size: 0.84rem;
        color: var(--login-text-soft);
    }

    .mini-chart {
        height: 100%;
        min-height: 140px;
        padding: 16px;
        border-radius: 18px;
        background: linear-gradient(180deg, #a8142b 0%, #7f0f20 100%);
        box-shadow: 0 18px 36px rgba(122, 18, 35, 0.24);
        position: relative;
        overflow: hidden;
    }

    .mini-chart::before,
    .mini-chart::after {
        content: "";
        position: absolute;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.14);
    }

    .mini-chart::before {
        width: 180px;
        height: 180px;
        top: -90px;
        right: -40px;
    }

    .mini-chart::after {
        width: 120px;
        height: 120px;
        bottom: -50px;
        left: -30px;
    }

    .mini-chart-label,
    .mini-chart-value {
        position: relative;
        z-index: 1;
        color: #fff;
    }

    .mini-chart-label {
        display: block;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        opacity: 0.78;
    }

    .mini-chart-value {
        display: block;
        margin-top: 8px;
        font-size: 1.35rem;
        font-weight: 800;
    }

    .chart-bars {
        position: absolute;
        left: 18px;
        right: 18px;
        bottom: 16px;
        height: 56px;
        display: flex;
        align-items: end;
        gap: 8px;
        z-index: 1;
    }

    .chart-bars span {
        flex: 1;
        border-radius: 999px 999px 10px 10px;
        background: linear-gradient(180deg, rgba(255,255,255,0.92), rgba(255,255,255,0.42));
    }

    .chart-bars span:nth-child(1) { height: 34%; }
    .chart-bars span:nth-child(2) { height: 56%; }
    .chart-bars span:nth-child(3) { height: 74%; }
    .chart-bars span:nth-child(4) { height: 52%; }
    .chart-bars span:nth-child(5) { height: 86%; }

    .brand-footer {
        margin-top: 16px;
        display: flex;
        flex-wrap: wrap;
        gap: 12px 24px;
        font-size: 0.84rem;
        color: #6b8098;
    }

    .login-form-panel {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: linear-gradient(180deg, rgba(251,250,250,0.94), rgba(242,240,241,0.98));
        position: relative;
    }

    .login-card {
        width: 100%;
        max-width: none;
        height: 100%;
        padding: 40px 44px 36px;
        border-radius: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        border: 1px solid rgba(144, 132, 136, 0.22);
        background:
            radial-gradient(circle at top right, rgba(168, 20, 43, 0.06), transparent 24%),
            linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(247,244,245,0.98) 100%);
        box-shadow: 0 24px 48px rgba(54, 44, 47, 0.12);
    }

    .login-card-header {
        text-align: center;
        margin-bottom: 32px;
        padding: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .login-card-logo {
        width: 240px;
        height: auto;
        object-fit: contain;
        display: inline-block;
        margin-bottom: 26px;
        padding: 0;
        background: transparent;
        box-shadow: none;
        border-radius: 0;
    }

    .login-card-title {
        margin: 0;
        font-size: 2.2rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: var(--login-text);
    }

    .login-card-subtitle {
        margin: 10px 0 0;
        font-size: 1rem;
        line-height: 1.6;
        color: var(--login-text-soft);
    }

    .login-alert {
        margin-bottom: 20px;
        padding: 14px 16px;
        border-radius: 16px;
        border: 1px solid rgba(115, 201, 154, 0.26);
        background: rgba(37, 99, 69, 0.2);
        color: #d2f5e0;
        font-size: 0.94rem;
    }

    .login-field {
        margin-bottom: 20px;
    }

    .login-label {
        display: block;
        margin-bottom: 8px;
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--login-text);
    }

    .input-shell {
        position: relative;
    }

    .login-input {
        width: 100%;
        height: 60px;
        border: 1px solid rgba(180, 184, 191, 0.5);
        border-radius: 16px;
        background: #ffffff;
        color: var(--login-text);
        font-size: 1rem;
        padding: 0 16px;
        box-shadow: inset 0 1px 2px rgba(8, 18, 32, 0.04);
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }

    .login-input::placeholder {
        color: #98a0aa;
    }

    .login-input:focus {
        outline: none;
        border-color: rgba(214, 123, 138, 0.86);
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(168, 20, 43, 0.16);
    }

    .password-shell .login-input {
        padding-right: 54px;
    }

    .password-toggle {
        position: absolute;
        top: 50%;
        right: 12px;
        transform: translateY(-50%);
        width: 38px;
        height: 38px;
        border: 0;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        color: #7b8590;
        cursor: pointer;
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .password-toggle:hover,
    .password-toggle:focus {
        outline: none;
        color: var(--login-text);
        background: rgba(168, 20, 43, 0.06);
    }

    .field-error {
        display: block;
        margin-top: 8px;
        font-size: 0.88rem;
        color: #ffb6b6;
    }

    .login-input.is-invalid {
        border-color: rgba(255, 148, 148, 0.72);
        background: rgba(124, 35, 35, 0.22);
    }

    .login-options {
        margin: 8px 0 26px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .remember-shell {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 0.92rem;
        color: var(--login-text-soft);
        cursor: pointer;
        margin: 0;
    }

    .remember-shell input {
        width: 16px;
        height: 16px;
        accent-color: var(--login-primary);
    }

    .link-subtle {
        color: #f2b4bd;
        font-size: 0.92rem;
        font-weight: 700;
        text-decoration: none;
        transition: color 0.2s ease, opacity 0.2s ease;
    }

    .link-subtle:hover,
    .link-subtle:focus {
        color: #ffd8dd;
        text-decoration: none;
    }

    .login-button {
        width: 100%;
        height: 60px;
        border: 0;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: linear-gradient(135deg, var(--login-primary) 0%, #c62b43 100%);
        color: #fff;
        font-size: 1rem;
        font-weight: 800;
        letter-spacing: 0.02em;
        box-shadow: 0 18px 34px rgba(122, 18, 35, 0.24);
        transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
    }

    .login-button:hover,
    .login-button:focus {
        transform: translateY(-1px);
        box-shadow: 0 20px 38px rgba(122, 18, 35, 0.3);
        outline: none;
    }

    .login-button:active {
        transform: translateY(0);
    }

    .login-button[disabled] {
        opacity: 0.9;
        cursor: wait;
        transform: none;
    }

    .button-spinner {
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.35);
        border-top-color: #fff;
        border-radius: 50%;
        display: none;
        animation: login-spin 0.8s linear infinite;
    }

    .login-button.is-loading .button-spinner {
        display: inline-block;
    }

    @keyframes login-spin {
        to {
            transform: rotate(360deg);
        }
    }

    @media (max-width: 1199.98px) {
        .login-layout {
            grid-template-columns: 1.08fr .92fr;
        }

        .login-brand-panel {
            padding: 28px 30px 22px;
        }
    }

    @media (max-width: 991.98px) {
        html, body, #app,
        main.py-4,
        .login-shell {
            height: auto;
            overflow: auto;
        }

        .login-layout {
            height: auto;
            grid-template-columns: 1fr;
        }

        .login-form-panel {
            order: 1;
            padding: 18px;
        }

        .login-brand-panel {
            order: 2;
            padding: 22px 18px 24px;
        }

        .brand-insights,
        .visual-grid {
            grid-template-columns: 1fr;
        }

        .login-card {
            height: auto;
            border-radius: 24px;
            padding: 30px 24px 26px;
        }
    }

    @media (max-width: 575.98px) {
        .login-card {
            padding: 28px 20px 24px;
            border-radius: 24px;
        }

        .login-card-header {
            padding: 26px 18px 22px;
            border-radius: 24px;
        }

        .login-card-title {
            font-size: 1.7rem;
        }

        .login-options {
            flex-direction: column;
            align-items: flex-start;
        }

        .brand-mark {
            align-items: flex-start;
        }

        .brand-mark img {
            width: 60px;
            height: 60px;
        }

        .login-card-logo {
            width: 180px;
            height: auto;
        }

        .brand-visual {
            display: none;
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
                    <img src="{{ asset('images/jpbi-logo.svg') }}" alt="JPBI Builders Inc.">
                    <div>
                        <p class="brand-kicker">Enterprise Platform</p>
                        <h1 class="brand-name">JPBI Builders Inc.</h1>
                    </div>
                </div>

                <p class="brand-subtitle">Integrated Accounting, Payroll, and Business Operations System</p>
                <p class="brand-copy">
                    Access a cleaner operational workspace built for finance teams, payroll administrators, and decision makers who need reliable controls, reporting clarity, and day-to-day transaction visibility.
                </p>

                <div class="brand-insights">
                    <div class="insight-card">
                        <div class="insight-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <path d="M4 19H20M6.5 16V10M12 16V5M17.5 16V8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h3>Accounting Management</h3>
                        <p>Track transactions, maintain ledgers, and manage financial workflows with stronger control.</p>
                    </div>
                    <div class="insight-card">
                        <div class="insight-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <path d="M12 3V21M7 8H14.5a2.5 2.5 0 0 1 0 5H9.5a2.5 2.5 0 0 0 0 5H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h3>Payroll Processing</h3>
                        <p>Support salary cycles, time-based computations, and employee-related financial records.</p>
                    </div>
                    <div class="insight-card">
                        <div class="insight-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <path d="M6 18V11M12 18V6M18 18V13M4 20H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h3>Financial Reporting</h3>
                        <p>Generate timely management views for statements, dashboards, and operational review.</p>
                    </div>
                    <div class="insight-card">
                        <div class="insight-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm-7 8a7 7 0 0 1 14 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M18 7h3M19.5 5.5v3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h3>User Access &amp; Controls</h3>
                        <p>Keep permissions structured and operational tasks aligned with accountable system access.</p>
                    </div>
                </div>

                <div class="brand-visual" aria-hidden="true">
                    <div class="visual-grid">
                        <div class="metric-stack">
                            <div class="metric-card">
                                <span class="metric-label">Finance Workspace</span>
                                <span class="metric-value">Secure. Structured. Visible.</span>
                                <span class="metric-footnote">Designed for enterprise accuracy and daily execution.</span>
                            </div>
                        </div>
                        <div class="mini-chart">
                            <span class="mini-chart-label">System Readiness</span>
                            <span class="mini-chart-value">Connected Workflows</span>
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
                    <span>Version 2.0 Enterprise Suite</span>
                    <span>Support: support@jpbi.local</span>
                </div>
            </div>
        </aside>

        <section class="login-form-panel">
            <div class="login-card">
                <div class="login-card-header">
                    <img class="login-card-logo" src="{{ asset('images/jpbi-logo.svg') }}" alt="JPBI logo">
                    <h2 class="login-card-title">Welcome Back</h2>
                    <p class="login-card-subtitle">Sign in to continue to the system.</p>
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
                            <a class="link-subtle" href="{{ route('password.request') }}">Forgot password?</a>
                        @endif
                    </div>

                    <button type="submit" class="login-button" id="loginButton">
                        <span class="button-spinner" aria-hidden="true"></span>
                        <span class="button-text">Sign In</span>
                    </button>
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
