<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" href="{{ asset('images/' . ($app_settings->favicon ?? 'favicon.ico')) }}">
    <title>{{ $app_settings->app_name ?? 'Stocky | Ultimate Inventory With POS' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @php
      $loginAccent = '#0f766e';
      if (!empty($app_settings->login_bg_color) && preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $app_settings->login_bg_color)) {
          $loginAccent = $app_settings->login_bg_color;
      }
    @endphp
    <style>
      :root { --accent: {{ $loginAccent }}; --paper:#f4f1ea; --text:#171412; --muted:#5c574e; --border:#e4dfd4; --card:#fffcf7; }
      * { box-sizing: border-box; margin: 0; padding: 0; }
      body {
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
        background:
          radial-gradient(800px 400px at 90% -10%, color-mix(in srgb, var(--accent) 18%, transparent), transparent 60%),
          var(--paper);
        min-height: 100dvh;
        color: var(--text);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
      }
      .auth-card {
        width: 100%;
        max-width: 420px;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 28px;
      }
      .auth-brand { display: flex; margin-bottom: 18px; }
      .auth-brand img { max-height: 40px; }
      .auth-title { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.03em; }
      .auth-subtitle { margin: 6px 0 20px; color: var(--muted); font-size: 0.92rem; line-height: 1.5; }
      .form-group { margin-bottom: 14px; }
      .form-group label { display: block; margin-bottom: 6px; font-weight: 650; font-size: 0.78rem; color: var(--muted); }
      .auth-input {
        width: 100%; padding: 12px 14px; border: 1px solid var(--border); border-radius: 10px;
        font-size: 16px; font-family: inherit; background: #fff; outline: none;
      }
      .auth-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 22%, transparent); }
      .auth-btn {
        width: 100%; padding: 12px 14px; border: none; border-radius: 10px;
        background: var(--accent); color: #fff; font-weight: 700; font-family: inherit; cursor: pointer;
      }
      .auth-btn:hover { filter: brightness(0.94); }
      .auth-alert { padding: 10px 12px; border-radius: 10px; font-size: 13px; margin-bottom: 12px; }
      .auth-alert.error { background: #fef3f2; color: #7a271a; border: 1px solid #fecdca; }
    </style>
  </head>
  <body>
    <div class="auth-card">
      <div class="auth-brand">
        <img src="{{ asset('images/' . ($app_settings->logo ?? 'logo.png')) }}" alt="logo" />
      </div>
      @if ($errors->any())
      <div class="auth-alert error">
        <ul>
          @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
      @endif
      <h1 class="auth-title">{{ __('Reset Password') }}</h1>
      <p class="auth-subtitle">{{ __('Enter your new password below.') }}</p>
      <form method="POST" action="{{ route('password.update') }}" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}" />
        <div class="form-group">
          <label for="email">{{ __('E-Mail Address') }}</label>
          <input id="email" type="email" name="email" class="auth-input" value="{{ old('email') }}" required autocomplete="email" />
        </div>
        <div class="form-group">
          <label for="password">{{ __('Password') }}</label>
          <input id="password" type="password" name="password" class="auth-input" required autocomplete="new-password" />
        </div>
        <div class="form-group">
          <label for="password-confirm">{{ __('Confirm Password') }}</label>
          <input id="password-confirm" type="password" name="password_confirmation" class="auth-input" required autocomplete="new-password" />
        </div>
        <button type="submit" class="auth-btn">{{ __('Reset Password') }}</button>
      </form>
    </div>
  </body>
</html>
