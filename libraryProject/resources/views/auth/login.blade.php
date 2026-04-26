<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#7a5c3c" />
    <title>Kütüphane Yönetim Sistemi</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400&family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --background: #f5f0e8;
            --foreground: #3d3226;
            --card: #faf8f3;
            --primary: #7a5c3c;
            --primary-foreground: #f5f0e8;
            --primary-hover: #6a4e34;
            --secondary: #ede8de;
            --muted: #ede8de;
            --muted-foreground: #7a7060;
            --accent: #9b6b3f;
            --destructive: #c53030;
            --border: #d9d0c2;
            --ring: #7a5c3c;
            --font-sans: 'Source Sans 3', system-ui, sans-serif;
            --font-serif: 'Merriweather', Georgia, serif;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font-sans);
            background: var(--background);
            color: var(--foreground);
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
            display: flex;
        }

        input, button { font-family: inherit; font-size: inherit; }

        /* Layout */
        .login-layout { display: flex; width: 100%; min-height: 100vh; }

        /* Left decorative panel */
        .login-panel {
            flex: 1;
            background: #3d3226;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 48px;
            position: relative;
            overflow: hidden;
        }

        .login-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 50% at 20% 80%, rgba(155,107,63,0.35) 0%, transparent 70%),
                radial-gradient(ellipse 40% 60% at 80% 20%, rgba(122,92,60,0.2) 0%, transparent 60%);
            pointer-events: none;
        }

        /* Kitap sırtı dekor */
        .panel-shelves {
            position: absolute; right: 0; top: 0; bottom: 0; width: 72px;
            display: flex; flex-direction: column;
        }
        .shelf-book { flex: 1; }
        .shelf-book:nth-child(odd)  { background: rgba(200,164,110,0.08); }
        .shelf-book:nth-child(even) { background: rgba(232,213,176,0.05); }

        .panel-top { position: relative; z-index: 1; }

        .panel-logo { display: flex; align-items: center; gap: 14px; }
        .panel-logo-icon {
            width: 48px; height: 48px; border-radius: 12px;
            background: rgba(155,123,85,0.8);
            display: flex; align-items: center; justify-content: center;
        }
        .panel-logo-icon svg { width: 26px; height: 26px; color: #f5f0e8; }
        .panel-brand-name { font-size: 20px; font-weight: 700; color: #e8e2d6; letter-spacing: -0.02em; line-height: 1.2; }
        .panel-brand-sub { font-size: 13px; color: rgba(232,226,214,0.6); margin-top: 2px; }

        .panel-middle { position: relative; z-index: 1; }

        .panel-tagline {
            font-family: var(--font-serif);
            font-size: 30px; font-weight: 700; color: #e8e2d6;
            line-height: 1.35; letter-spacing: -0.02em; max-width: 340px;
        }
        .panel-tagline em { font-style: italic; color: #c8a46e; }

        .panel-tagline-sub {
            color: rgba(232,226,214,0.55); font-size: 14px;
            margin-top: 16px; max-width: 320px; line-height: 1.6;
        }

        .panel-stats { display: flex; gap: 32px; margin-top: 40px; }
        .stat-num { font-family: var(--font-serif); font-size: 28px; font-weight: 700; color: #c8a46e; }
        .stat-label { font-size: 11px; color: rgba(232,226,214,0.45); margin-top: 2px; text-transform: uppercase; letter-spacing: 0.05em; }

        .panel-bottom { position: relative; z-index: 1; font-size: 12px; color: rgba(232,226,214,0.3); }

        /* Right side */
        .login-form-side {
            width: 480px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            padding: 48px 40px;
            background: var(--background);
        }

        .login-form-wrap { width: 100%; max-width: 380px; }

        /* Form header */
        .form-header { margin-bottom: 32px; }
        .form-title {
            font-family: var(--font-serif); font-size: 26px; font-weight: 700;
            color: var(--foreground); letter-spacing: -0.02em;
        }
        .form-subtitle { font-size: 14px; color: var(--muted-foreground); margin-top: 6px; }

        /* Error alert */
        .alert-error {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 12px 14px; border-radius: 8px;
            background: rgba(197,48,48,0.08); border: 1px solid rgba(197,48,48,0.2);
            margin-bottom: 20px; font-size: 13px; color: var(--destructive); line-height: 1.5;
        }
        .alert-error svg { width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px; }

        /* Fields */
        .form-field { margin-bottom: 16px; }
        .form-label { display: block; font-size: 14px; font-weight: 500; color: var(--foreground); margin-bottom: 7px; }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            width: 16px; height: 16px; color: var(--muted-foreground); pointer-events: none;
        }
        .form-input {
            width: 100%; padding: 10px 12px 10px 38px;
            border: 1px solid var(--border); border-radius: 8px;
            background: var(--card); color: var(--foreground);
            font-size: 14px; line-height: 1.5; outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-input::placeholder { color: var(--muted-foreground); opacity: 0.7; }
        .form-input:focus { border-color: var(--ring); box-shadow: 0 0 0 3px rgba(122,92,60,0.12); }
        .form-input.is-error { border-color: var(--destructive); }
        .field-error { font-size: 12px; color: var(--destructive); margin-top: 5px; }

        /* Password toggle */
        .pw-toggle {
            position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; padding: 4px;
            color: var(--muted-foreground); display: flex; border-radius: 4px;
            transition: color 0.15s, background 0.15s;
        }
        .pw-toggle:hover { color: var(--foreground); background: var(--muted); }
        .pw-toggle svg { width: 16px; height: 16px; }

        /* Remember row */
        .form-row {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 24px;
        }
        .checkbox-label {
            display: flex; align-items: center; gap: 8px;
            font-size: 14px; color: var(--foreground); cursor: pointer;
        }
        .form-checkbox { width: 16px; height: 16px; accent-color: var(--primary); cursor: pointer; }

        /* Submit btn */
        .btn-login {
            width: 100%; padding: 11px 16px;
            background: var(--primary); color: var(--primary-foreground);
            border: none; border-radius: 8px;
            font-size: 15px; font-weight: 600; cursor: pointer;
            transition: background 0.15s; display: flex;
            align-items: center; justify-content: center; gap: 8px;
        }
        .btn-login:hover { background: var(--primary-hover); }
        .btn-login:disabled { opacity: 0.6; cursor: not-allowed; }
        .btn-login svg { width: 18px; height: 18px; }
        .btn-spinner {
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,0.35); border-top-color: white;
            border-radius: 50%; animation: spin 0.7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .form-footer {
            margin-top: 28px; padding-top: 20px;
            border-top: 1px solid var(--border);
            text-align: center; font-size: 12px; color: var(--muted-foreground);
        }

        /* Toast */
        .toast-container { position: fixed; top: 16px; right: 16px; z-index: 1000; display: flex; flex-direction: column; gap: 8px; }
        .toast { padding: 14px 20px; border-radius: 10px; font-size: 14px; font-weight: 500; box-shadow: 0 4px 12px rgba(0,0,0,0.15); animation: toast-in 0.3s ease; }
        .toast.success { background: #2f7d32; color: white; }
        .toast.error { background: #c53030; color: white; }
        @keyframes toast-in { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes toast-out { from { opacity: 1; } to { opacity: 0; } }

        @media (max-width: 900px) {
            .login-panel { display: none; }
            .login-form-side { width: 100%; }
        }
        @media (max-width: 480px) {
            .login-form-side { padding: 32px 24px; }
        }
    </style>
</head>
<body>

<div class="toast-container" id="toastContainer"></div>

<div class="login-layout">

    <!-- Sol dekoratif panel -->
    <div class="login-panel">
        <div class="panel-shelves" aria-hidden="true">
            @for($i = 0; $i < 16; $i++)<div class="shelf-book"></div>@endfor
        </div>

        <div class="panel-top">
            <div class="panel-logo">
                <div class="panel-logo-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
                </div>
                <div>
                    <div class="panel-brand-name">Beyoğlu Belediyesi</div>
                    <div class="panel-brand-sub">Kütüphane Bilgi Sistemii</div>
                </div>
            </div>
        </div>

        <div class="panel-middle">
            <h2 class="panel-tagline">
                Bilgiye giden yol,<br>
                <em>düzenli bir kütüphaneden</em><br>
                geçer.
            </h2>
            <p class="panel-tagline-sub">
                Kitap envanteri, ödünç takibi ve kütüphane yönetimini tek ekrandan kolayca yönetin.
            </p>
            {{--<div class="panel-stats">
                <div><div class="stat-num">∞</div><div class="stat-label">Katalog</div></div>
                <div><div class="stat-num">7/24</div><div class="stat-label">Erişim</div></div>
                <div><div class="stat-num">100%</div><div class="stat-label">Güvenli</div></div>
            </div>--}}
        </div>

        <div class="panel-bottom">© {{ date('Y') }} Beyoğlu Belediyesi — Bilgi İşlem Müdürlüğü</div>
    </div>

    <!-- Sağ form paneli -->
    <div class="login-form-side">
        <div class="login-form-wrap">

            <div class="form-header">
                <h1 class="form-title">Hoş Geldiniz</h1>
                <p class="form-subtitle">Devam etmek için giriş yapın.</p>
            </div>

            @if($errors->any())
                <div class="alert-error">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                    <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                </div>
            @endif

            <form id="loginForm" method="POST" action="{{ route('auth.login.post') }}" novalidate>
                @csrf

                <div class="form-field">
                    <label class="form-label" for="email">E-posta Adresi</label>
                    <div class="input-wrap">
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        <input type="email" class="form-input @error('email') is-error @enderror"
                               id="email" name="email" value="{{ old('email') }}"
                               placeholder="kullanici@ornek.com" autocomplete="email" autofocus />
                    </div>
                    @error('email')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-field">
                    <label class="form-label" for="password">Şifre</label>
                    <div class="input-wrap">
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input type="password" class="form-input @error('password') is-error @enderror"
                               id="password" name="password" placeholder="••••••••"
                               autocomplete="current-password" style="padding-right:42px;" />
                        <button type="button" class="pw-toggle" id="pwToggle" aria-label="Şifreyi göster">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    @error('password')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-row">
                    <label class="checkbox-label">
                        <input type="checkbox" class="form-checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} />
                        Beni hatırla
                    </label>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg>
                    Giriş Yap
                </button>
            </form>

            <div class="form-footer">Kütüphane Yönetim Sistemi &nbsp;·&nbsp; © {{ date('Y') }}</div>
        </div>
    </div>
</div>

<script>
    // Şifre göster/gizle
    var pwToggle = document.getElementById('pwToggle');
    var passwordInput = document.getElementById('password');
    var eyeOpen   = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
    var eyeClosed = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>';
    pwToggle.addEventListener('click', function() {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            pwToggle.innerHTML = eyeClosed;
        } else {
            passwordInput.type = 'password';
            pwToggle.innerHTML = eyeOpen;
        }
    });

    // Submit loading
    document.getElementById('loginForm').addEventListener('submit', function() {
        var btn = document.getElementById('loginBtn');
        btn.disabled = true;
        btn.innerHTML = '<div class="btn-spinner"></div> Giriş yapılıyor...';
    });

    // Çıkış başarılı toast
    function showToast(type, msg) {
        var c = document.getElementById('toastContainer');
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.textContent = msg;
        c.appendChild(t);
        setTimeout(function() { t.style.animation = 'toast-out 0.3s forwards'; setTimeout(function() { t.remove(); }, 300); }, 3500);
    }
    @if(session('logout'))
    showToast('success', 'Güvenli çıkış yapıldı.');
    @endif
</script>
</body>
</html>
