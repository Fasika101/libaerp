<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" href="{{ asset('images/' . ($app_settings->favicon ?? 'favicon.ico')) }}">

    {{-- PWA --}}
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#0b1016">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ $app_settings->app_name ?? 'Stocky' }}">
    <link rel="apple-touch-icon" href="/pwa_images/pwa-icon-192.png">

    <title>{{ $app_settings->app_name ?? 'Stocky | Ultimate Inventory With POS' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @php
      $loginAccent = '#0f766e';
      if (!empty($app_settings->login_bg_color) && preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $app_settings->login_bg_color)) {
          $loginAccent = $app_settings->login_bg_color;
      }
      $features = array_values(array_filter([
        $app_settings->login_hero_feature_1 ?? 'Real-time inventory tracking',
        $app_settings->login_hero_feature_2 ?? 'Multi-location POS support',
        $app_settings->login_hero_feature_3 ?? 'Advanced reporting & analytics',
      ]));
    @endphp

    <style>
      :root {
        --accent: {{ $loginAccent }};
        --accent-hover: color-mix(in srgb, var(--accent) 82%, #000);
        --ink: #0b1016;
        --ink-2: #141b24;
        --paper: #f4f1ea;
        --paper-card: #fffcf7;
        --text: #171412;
        --text-secondary: #5c574e;
        --text-muted: #8a8478;
        --border: #e4dfd4;
        --danger: #b42318;
        --danger-soft: #fef3f2;
        --danger-border: #fecdca;
        --success: #067647;
        --success-soft: #ecfdf3;
        --success-border: #abefc6;
        --radius: 12px;
        --transition: 180ms ease;
      }

      *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

      body {
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        color: var(--text);
        background: var(--paper);
        min-height: 100dvh;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
      }

      .auth-page {
        display: grid;
        grid-template-columns: minmax(360px, 460px) 1fr;
        min-height: 100dvh;
      }

      /* ─── SIGN-IN COLUMN ─── */
      .auth-panel {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: clamp(1.5rem, 4vw, 2.75rem);
        background: var(--paper);
        border-right: 1px solid var(--border);
      }

      .auth-brand {
        display: flex;
        align-items: center;
        gap: 0.75rem;
      }

      .auth-brand img {
        max-height: 40px;
        max-width: 180px;
        object-fit: contain;
      }

      .auth-main {
        width: 100%;
        max-width: 360px;
        margin: 2.5rem 0;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
      }

      .auth-heading h1 {
        font-size: 1.85rem;
        font-weight: 800;
        letter-spacing: -0.04em;
        line-height: 1.15;
      }

      .auth-heading p {
        margin-top: 0.45rem;
        color: var(--text-secondary);
        font-size: 0.95rem;
        line-height: 1.55;
      }

      .auth-form { display: flex; flex-direction: column; gap: 1.1rem; }

      .form-group { display: flex; flex-direction: column; gap: 0.4rem; }

      .form-label {
        font-size: 0.78rem;
        font-weight: 650;
        color: var(--text-secondary);
      }

      .input-wrapper {
        display: flex;
        align-items: center;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: var(--paper-card);
        transition: border-color var(--transition), box-shadow var(--transition);
      }

      .input-wrapper:focus-within {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 22%, transparent);
      }

      .input-icon {
        display: flex;
        padding-left: 0.85rem;
        color: var(--text-muted);
      }

      .input-wrapper:focus-within .input-icon { color: var(--accent); }

      .input-icon svg,
      .toggle-password svg {
        width: 17px; height: 17px;
        stroke: currentColor;
        fill: none;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
      }

      .form-input {
        flex: 1;
        border: none;
        background: transparent;
        padding: 0.78rem 0.8rem;
        font-size: 0.95rem;
        font-family: inherit;
        color: var(--text);
        outline: none;
        min-width: 0;
      }

      .form-input::placeholder { color: var(--text-muted); }

      .toggle-password {
        border: none;
        background: none;
        padding: 0 0.75rem;
        cursor: pointer;
        color: var(--text-muted);
      }

      .toggle-password:hover { color: var(--text); }

      .form-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
      }

      .remember-check {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        cursor: pointer;
        font-size: 0.85rem;
        color: var(--text-secondary);
        user-select: none;
      }

      .remember-check input {
        width: 15px; height: 15px;
        accent-color: var(--accent);
        cursor: pointer;
      }

      .forgot-link {
        font-size: 0.85rem;
        font-weight: 650;
        color: var(--accent);
        text-decoration: none;
      }

      .forgot-link:hover { text-decoration: underline; }

      .auth-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        margin-top: 0.15rem;
        padding: 0.85rem 1.25rem;
        border: none;
        border-radius: 10px;
        background: var(--accent);
        color: #fff;
        font-size: 0.95rem;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        transition: background var(--transition), transform 100ms ease;
      }

      .auth-btn:hover { background: var(--accent-hover); }
      .auth-btn:active { transform: scale(0.985); }
      .auth-btn:disabled { cursor: not-allowed; opacity: 0.8; }

      .btn-loading {
        display: none;
        align-items: center;
        gap: 0.5rem;
      }

      .spinner {
        width: 16px; height: 16px;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
      }

      @keyframes spin { to { transform: rotate(360deg); } }

      .auth-alert {
        padding: 0.75rem 0.9rem;
        border-radius: 10px;
        border: 1px solid var(--border);
        font-size: 0.85rem;
        line-height: 1.5;
        display: flex;
        align-items: flex-start;
        gap: 0.55rem;
      }

      .auth-alert ul { margin: 0; padding-left: 1rem; list-style: none; }
      .auth-alert ul li::before {
        content: '\2022';
        margin-right: 0.35rem;
        opacity: 0.5;
      }

      .auth-alert.error {
        background: var(--danger-soft);
        border-color: var(--danger-border);
        color: #7a271a;
      }

      .auth-alert.success {
        background: var(--success-soft);
        border-color: var(--success-border);
        color: #085d3a;
      }

      .alert-icon svg {
        width: 16px; height: 16px;
        stroke: currentColor;
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
      }

      .auth-footer {
        font-size: 0.75rem;
        color: var(--text-muted);
      }

      /* ─── PRODUCT CANVAS ─── */
      .auth-hero {
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: clamp(1.75rem, 4vw, 3rem);
        color: #e8edf4;
        background:
          radial-gradient(900px 480px at 80% -10%, color-mix(in srgb, var(--accent) 38%, transparent), transparent 60%),
          radial-gradient(700px 400px at -10% 110%, color-mix(in srgb, var(--accent) 18%, transparent), transparent 55%),
          linear-gradient(165deg, #101820 0%, var(--ink) 55%, #080c11 100%);
      }

      .auth-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image:
          linear-gradient(rgba(255,255,255,0.035) 1px, transparent 1px),
          linear-gradient(90deg, rgba(255,255,255,0.035) 1px, transparent 1px);
        background-size: 44px 44px;
        mask-image: radial-gradient(ellipse at 70% 40%, #000 20%, transparent 75%);
        pointer-events: none;
      }

      .hero-copy { position: relative; z-index: 1; max-width: 520px; }

      .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        margin-bottom: 1rem;
        padding: 0.28rem 0.7rem;
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 999px;
        background: rgba(255,255,255,0.06);
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #d7e0ea;
      }

      .hero-badge-dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        background: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.18);
      }

      .hero-title {
        font-size: clamp(1.85rem, 3.4vw, 2.7rem);
        font-weight: 800;
        letter-spacing: -0.045em;
        line-height: 1.12;
      }

      .hero-subtitle {
        margin-top: 0.85rem;
        max-width: 440px;
        color: rgba(232, 237, 244, 0.72);
        font-size: 1rem;
        line-height: 1.65;
      }

      .preview {
        position: relative;
        z-index: 1;
        margin: 2rem 0 1.5rem;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 16px;
        background: color-mix(in srgb, var(--ink-2) 86%, var(--accent));
        box-shadow: 0 30px 60px rgba(0,0,0,0.35);
        overflow: hidden;
      }

      .preview-bar {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.7rem 0.9rem;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        background: rgba(0,0,0,0.18);
      }

      .preview-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        background: rgba(255,255,255,0.22);
      }

      .preview-dot:first-child { background: #f87171; }
      .preview-dot:nth-child(2) { background: #fbbf24; }
      .preview-dot:nth-child(3) { background: #34d399; }

      .preview-bar span {
        margin-left: 0.5rem;
        font-size: 0.72rem;
        color: rgba(255,255,255,0.45);
      }

      .preview-body { padding: 1rem; display: grid; gap: 0.85rem; }

      .preview-kpis {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.65rem;
      }

      .kpi {
        padding: 0.7rem 0.75rem;
        border-radius: 10px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.06);
      }

      .kpi small {
        display: block;
        font-size: 0.68rem;
        color: rgba(255,255,255,0.45);
        margin-bottom: 0.3rem;
      }

      .kpi strong {
        font-size: 1.05rem;
        letter-spacing: -0.03em;
      }

      .kpi em {
        display: block;
        margin-top: 0.15rem;
        font-style: normal;
        font-size: 0.68rem;
        color: #6ee7b7;
      }

      .preview-chart {
        display: flex;
        align-items: flex-end;
        gap: 0.4rem;
        height: 84px;
        padding: 0.5rem 0.25rem 0;
      }

      .bar {
        flex: 1;
        border-radius: 4px 4px 0 0;
        background: color-mix(in srgb, var(--accent) 70%, #fff);
        opacity: 0.85;
      }

      .preview-rows { display: grid; gap: 0.4rem; }

      .row {
        display: grid;
        grid-template-columns: 1.4fr 0.8fr 0.6fr;
        gap: 0.5rem;
        align-items: center;
        font-size: 0.72rem;
        color: rgba(255,255,255,0.7);
      }

      .row i {
        height: 8px;
        border-radius: 99px;
        background: rgba(255,255,255,0.14);
        font-style: normal;
      }

      .hero-features {
        position: relative;
        z-index: 1;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
      }

      .hero-feature {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.7rem;
        border-radius: 999px;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.08);
        font-size: 0.8rem;
        color: rgba(255,255,255,0.86);
      }

      .hero-feature svg {
        width: 13px; height: 13px;
        stroke: #6ee7b7;
        fill: none;
        stroke-width: 2.5;
        stroke-linecap: round;
        stroke-linejoin: round;
      }

      .mobile-hero {
        display: none;
      }

      @media (max-width: 960px) {
        .auth-page { grid-template-columns: 1fr; }
        .auth-hero { display: none; }
        .auth-panel {
          min-height: 100dvh;
          border-right: none;
          padding-top: max(1.25rem, env(safe-area-inset-top));
          padding-bottom: max(1.25rem, env(safe-area-inset-bottom));
        }
        .mobile-hero {
          display: block;
          margin: 1.25rem 0 0.25rem;
        }
        .mobile-hero .hero-badge { color: var(--text-secondary); background: #efeae0; border-color: var(--border); text-transform: none; letter-spacing: 0; }
        .mobile-hero h2 {
          margin-top: 0.6rem;
          font-size: 1.35rem;
          letter-spacing: -0.03em;
        }
        .mobile-hero p {
          margin-top: 0.35rem;
          color: var(--text-secondary);
          font-size: 0.9rem;
          line-height: 1.5;
        }
        .auth-main { margin: 1.25rem 0; max-width: none; }
        .form-input { font-size: 16px; }
        .auth-btn { min-height: 48px; }
      }

      @media (max-width: 420px) {
        .form-row {
          flex-direction: column;
          align-items: flex-start;
        }
      }
    </style>
  </head>

  <body>
    <div class="auth-page">
      <section class="auth-panel">
        <div class="auth-brand">
          <img src="{{ asset('images/' . ($app_settings->logo ?? 'logo.png')) }}" alt="{{ $app_settings->app_name ?? 'Stocky' }}">
        </div>

        <div class="auth-main">
          <div class="mobile-hero">
            <div class="hero-badge">
              <span class="hero-badge-dot"></span>
              {{ $app_settings->login_hero_badge ?? 'Secure & Reliable' }}
            </div>
            <h2>{{ $app_settings->login_hero_title ?? 'Manage your business smarter.' }}</h2>
            <p>{{ $app_settings->login_hero_subtitle ?? 'Streamline inventory, track sales, and grow your business — all from one powerful dashboard.' }}</p>
          </div>

          <header class="auth-heading">
            <h1>{{ $app_settings->login_panel_title ?? 'Welcome back' }}</h1>
            <p>{{ $app_settings->login_panel_subtitle ?? 'Sign in to your account to continue' }}</p>
          </header>

          @if (session('status'))
          <div class="auth-alert success">
            <span class="alert-icon">
              <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </span>
            <span>{{ session('status') }}</span>
          </div>
          @endif

          @if ($errors->any())
          <div class="auth-alert error">
            <span class="alert-icon">
              <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </span>
            <div>
              <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          </div>
          @endif

          <form id="login_form" method="POST" action="{{ route('login') }}" class="auth-form">
            @csrf

            <div class="form-group">
              <label class="form-label" for="email">Email address</label>
              <div class="input-wrapper">
                <span class="input-icon">
                  <svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                </span>
                <input id="email" class="form-input" type="email" name="email" value="{{ old('email') }}" placeholder="you@company.com" required autofocus />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label" for="password">Password</label>
              <div class="input-wrapper">
                <span class="input-icon">
                  <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </span>
                <input id="password" class="form-input" type="password" name="password" placeholder="Enter your password" required />
                <button type="button" class="toggle-password" data-target="password" aria-label="Toggle password visibility">
                  <svg class="eye-open" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  <svg class="eye-closed" viewBox="0 0 24 24" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
              </div>
            </div>

            <div class="form-row">
              <label class="remember-check">
                <input type="checkbox" name="remember">
                Remember me
              </label>
              <a class="forgot-link" href="{{ route('password.request') }}">Forgot password?</a>
            </div>

            <button type="submit" class="auth-btn" id="login_submit_btn">
              <span class="btn-text">{{ $app_settings->login_btn_text ?? 'Sign in' }}</span>
              <svg class="btn-text" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
              <span class="btn-loading"><span class="spinner"></span> Signing in...</span>
            </button>
          </form>
        </div>

        <div class="auth-footer">
          {{ $app_settings->login_footer_text ?? '© ' . date('Y') . ' ' . ($app_settings->app_name ?? 'Stocky') . '. All rights reserved.' }}
        </div>
      </section>

      <section class="auth-hero" aria-hidden="false">
        <div class="hero-copy">
          <div class="hero-badge">
            <span class="hero-badge-dot"></span>
            {{ $app_settings->login_hero_badge ?? 'Secure & Reliable' }}
          </div>
          <h2 class="hero-title">{{ $app_settings->login_hero_title ?? 'Manage your business smarter.' }}</h2>
          <p class="hero-subtitle">
            {{ $app_settings->login_hero_subtitle ?? 'Streamline inventory, track sales, and grow your business — all from one powerful dashboard.' }}
          </p>
        </div>

        <div class="preview" aria-hidden="true">
          <div class="preview-bar">
            <span class="preview-dot"></span>
            <span class="preview-dot"></span>
            <span class="preview-dot"></span>
            <span>{{ $app_settings->app_name ?? 'Dashboard' }}</span>
          </div>
          <div class="preview-body">
            <div class="preview-kpis">
              <div class="kpi"><small>Today's sales</small><strong>48,250</strong><em>+12.4%</em></div>
              <div class="kpi"><small>Stock on hand</small><strong>1,284</strong><em>18 low</em></div>
              <div class="kpi"><small>Open invoices</small><strong>36</strong><em>3 overdue</em></div>
            </div>
            <div class="preview-chart">
              <div class="bar" style="height:38%"></div>
              <div class="bar" style="height:55%"></div>
              <div class="bar" style="height:46%"></div>
              <div class="bar" style="height:72%"></div>
              <div class="bar" style="height:64%"></div>
              <div class="bar" style="height:88%"></div>
              <div class="bar" style="height:70%"></div>
              <div class="bar" style="height:92%"></div>
              <div class="bar" style="height:60%"></div>
              <div class="bar" style="height:78%"></div>
            </div>
            <div class="preview-rows">
              <div class="row"><i style="width:78%"></i><i style="width:42%"></i><i style="width:28%"></i></div>
              <div class="row"><i style="width:64%"></i><i style="width:50%"></i><i style="width:22%"></i></div>
              <div class="row"><i style="width:86%"></i><i style="width:36%"></i><i style="width:34%"></i></div>
            </div>
          </div>
        </div>

        @if (count($features))
        <div class="hero-features">
          @foreach ($features as $feature)
            <span class="hero-feature">
              <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
              {{ $feature }}
            </span>
          @endforeach
        </div>
        @endif
      </section>
    </div>

    <script>
      (function() {
        const form = document.getElementById('login_form');
        const submitBtn = document.getElementById('login_submit_btn');

        document.querySelectorAll('.toggle-password').forEach(function(btn) {
          btn.addEventListener('click', function() {
            var target = document.getElementById(btn.dataset.target);
            var isHidden = target.type === 'password';
            target.type = isHidden ? 'text' : 'password';
            btn.querySelector('.eye-open').style.display = isHidden ? 'none' : 'block';
            btn.querySelector('.eye-closed').style.display = isHidden ? 'block' : 'none';
          });
        });

        if (!form || !submitBtn) return;

        function setBusy() {
          submitBtn.disabled = true;
          submitBtn.setAttribute('aria-busy', 'true');
          submitBtn.querySelectorAll('.btn-text').forEach(function(el) { el.style.display = 'none'; });
          submitBtn.querySelector('.btn-loading').style.display = 'inline-flex';
        }

        function clearBusy() {
          submitBtn.disabled = false;
          submitBtn.removeAttribute('aria-busy');
          submitBtn.querySelectorAll('.btn-text').forEach(function(el) { el.style.display = ''; });
          submitBtn.querySelector('.btn-loading').style.display = 'none';
        }

        var submitted = false;
        form.addEventListener('submit', function(e) {
          if (submitted) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return;
          }

          // Always refresh the CSRF token right before submit. This protects
          // against stale tokens from cached HTML (bfcache, browser cache,
          // service-worker shell) which were causing 419 Page Expired.
          e.preventDefault();
          submitted = true;
          setBusy();

          fetch('/csrf-token', {
            method: 'GET',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
          })
          .then(function(r) { return r.ok ? r.json() : null; })
          .then(function(data) {
            if (data && data.token) {
              var input = form.querySelector('input[name="_token"]');
              if (input) input.value = data.token;
              var meta = document.querySelector('meta[name="csrf-token"]');
              if (meta) meta.setAttribute('content', data.token);
            }
          })
          .catch(function() {})
          .finally(function() {
            form.submit();
          });
        });

        submitBtn.addEventListener('click', function(e) {
          if (submitted) {
            e.preventDefault();
            e.stopImmediatePropagation();
          }
        });

        // If the browser restores this page from bfcache (back/forward), the
        // CSRF token inside the form may be stale. Reset UI so the submit
        // handler runs again and refreshes the token.
        window.addEventListener('pageshow', function(ev) {
          if (ev.persisted) {
            submitted = false;
            clearBusy();
          }
        });

        // Self-heal: unregister any stale service worker on this page. The
        // login page must always be served fresh — if an older SW is still
        // holding a cached /login shell, drop it and clear its caches.
        try {
          if ('serviceWorker' in navigator && navigator.serviceWorker.getRegistrations) {
            navigator.serviceWorker.getRegistrations().then(function(regs) {
              regs.forEach(function(reg) { reg.unregister().catch(function() {}); });
            }).catch(function() {});
            if (window.caches && caches.keys) {
              caches.keys().then(function(keys) {
                keys.forEach(function(k) {
                  if (k && k.indexOf('shell') !== -1) caches.delete(k).catch(function() {});
                });
              }).catch(function() {});
            }
          }
        } catch (e) {}
      })();
    </script>

    {{-- Login page is intentionally NOT a PWA surface: no service worker is
         registered here. The SW is registered on the authenticated app shell
         instead, so the login HTML (which carries a session-specific CSRF
         token) is always served fresh from the network. --}}
  </body>
</html>
