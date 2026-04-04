<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#7a5c3c" />
    <title>Yeni Kütüphane — Beyoğlu Kütüphane Sistemi</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700;900&family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --background: #f5f0e8; --foreground: #3d3226; --card: #faf8f3;
            --primary: #7a5c3c; --primary-foreground: #f5f0e8;
            --secondary: #ede8de; --muted: #ede8de; --muted-foreground: #7a7060;
            --accent: #9b6b3f; --destructive: #c53030;
            --border: #d9d0c2; --ring: #7a5c3c; --radius: 0.625rem;
            --sidebar: #3d3226; --sidebar-foreground: #e8e2d6;
            --sidebar-primary: #9b7b55; --sidebar-primary-foreground: #f5f0e8;
            --sidebar-accent: #524435; --sidebar-accent-foreground: #e8e2d6;
            --sidebar-border: #5a4a3a;
            --font-sans: 'Source Sans 3', system-ui, sans-serif;
            --font-serif: 'Merriweather', Georgia, serif;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: var(--font-sans); background: var(--background); color: var(--foreground); -webkit-font-smoothing: antialiased; line-height: 1.5; }
        input, select, textarea, button { font-family: inherit; font-size: inherit; }

        .app-layout { display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar { width: 260px; background: var(--sidebar); color: var(--sidebar-foreground); display: flex; flex-direction: column; flex-shrink: 0; border-right: 1px solid var(--sidebar-border); position: fixed; top: 0; left: 0; bottom: 0; z-index: 40; transition: transform 0.3s ease; }
        .sidebar.collapsed { transform: translateX(-260px); }
        .sidebar-header { padding: 16px; display: flex; align-items: center; gap: 12px; }
        .sidebar-logo { width: 36px; height: 36px; border-radius: 8px; background: var(--sidebar-primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .sidebar-logo svg { width: 20px; height: 20px; color: var(--sidebar-primary-foreground); }
        .sidebar-brand-name { font-size: 16px; font-weight: 700; letter-spacing: -0.025em; }
        .sidebar-brand-sub { font-size: 12px; opacity: 0.6; }
        .sidebar-separator { height: 1px; background: var(--sidebar-border); margin: 0 16px; }
        .sidebar-content { flex: 1; overflow-y: auto; padding: 8px 0; }
        .sidebar-group { padding: 8px 12px; }
        .sidebar-group-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--sidebar-foreground); opacity: 0.5; padding: 4px 8px; margin-bottom: 4px; }
        .sidebar-menu { list-style: none; }
        .sidebar-menu-item { display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 6px; font-size: 14px; font-weight: 500; color: var(--sidebar-foreground); cursor: pointer; transition: background 0.15s; text-decoration: none; }
        .sidebar-menu-item:hover { background: var(--sidebar-accent); }
        .sidebar-menu-item.active { background: var(--sidebar-accent); }
        .sidebar-menu-item svg { width: 18px; height: 18px; flex-shrink: 0; opacity: 0.8; }
        .sidebar-footer { padding: 16px; border-top: 1px solid var(--sidebar-border); }
        .sidebar-user { display: flex; align-items: center; gap: 12px; }
        .sidebar-avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--sidebar-accent); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600; flex-shrink: 0; }
        .sidebar-user-name { font-size: 14px; font-weight: 500; }
        .sidebar-user-role { font-size: 12px; opacity: 0.6; }

        /* Main */
        .main-content { flex: 1; margin-left: 260px; display: flex; flex-direction: column; min-height: 100vh; transition: margin-left 0.3s ease; }
        .main-content.expanded { margin-left: 0; }

        /* Header */
        .top-header { height: 56px; display: flex; align-items: center; gap: 16px; padding: 0 16px; border-bottom: 1px solid rgba(217,208,194,0.6); background: var(--card); flex-shrink: 0; }
        .sidebar-trigger { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; border: none; background: transparent; cursor: pointer; color: var(--foreground); transition: background 0.15s; }
        .sidebar-trigger:hover { background: var(--muted); }
        .sidebar-trigger svg { width: 18px; height: 18px; }
        .header-separator { width: 1px; height: 20px; background: var(--border); }
        .breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 14px; }
        .breadcrumb-link { display: flex; align-items: center; gap: 6px; color: var(--muted-foreground); text-decoration: none; transition: color 0.15s; }
        .breadcrumb-link:hover { color: var(--foreground); }
        .breadcrumb-link svg { width: 14px; height: 14px; }
        .breadcrumb-sep { color: var(--muted-foreground); opacity: 0.5; font-size: 12px; }
        .breadcrumb-current { font-weight: 500; color: var(--foreground); }

        /* Content */
        .content-area { flex: 1; padding: 24px; }

        /* Form Card */
        .form-card { border: 1px solid rgba(217,208,194,0.6); border-radius: var(--radius); background: var(--card); box-shadow: 0 1px 3px rgba(0,0,0,0.04); max-width: 760px; }
        .form-card-header { padding: 24px 24px 16px; }
        .form-card-title { display: flex; align-items: center; gap: 8px; font-family: var(--font-serif); font-size: 20px; font-weight: 700; }
        .form-card-title svg { width: 20px; height: 20px; color: var(--primary); }
        .form-card-desc { font-size: 14px; color: var(--muted-foreground); margin-top: 4px; }
        .form-card-separator { height: 1px; background: var(--border); }
        .form-card-body { padding: 24px; display: flex; flex-direction: column; gap: 20px; }

        /* Section */
        .section-header { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted-foreground); margin-bottom: 14px; }
        .section-number { width: 20px; height: 20px; border-radius: 4px; background: rgba(122,92,60,0.1); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: var(--primary); }
        .section-sep { height: 1px; background: var(--border); }

        /* Form Grid */
        .form-grid { display: grid; gap: 16px; }
        .form-grid.cols-2 { grid-template-columns: repeat(2, 1fr); }
        .form-field { display: flex; flex-direction: column; }
        .form-label { font-size: 14px; font-weight: 500; color: var(--foreground); margin-bottom: 6px; }
        .form-label .required { color: var(--destructive); }
        .form-input, .form-select, .form-textarea { width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: calc(var(--radius) - 2px); background: var(--card); color: var(--foreground); font-size: 14px; line-height: 1.5; transition: border-color 0.15s, box-shadow 0.15s; outline: none; }
        .form-input::placeholder, .form-textarea::placeholder { color: var(--muted-foreground); opacity: 0.7; }
        .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: var(--ring); box-shadow: 0 0 0 2px rgba(122,92,60,0.15); }
        .form-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237a7060' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px; }
        .form-textarea { resize: vertical; }
        .form-hint { font-size: 12px; color: var(--muted-foreground); margin-top: 4px; }
        .form-error { font-size: 12px; color: var(--destructive); margin-top: 4px; }

        /* Actions */
        .form-actions { display: flex; justify-content: flex-end; gap: 12px; padding-top: 8px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 9px 16px; border-radius: calc(var(--radius) - 2px); font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.15s, opacity 0.15s; border: none; text-decoration: none; }
        .btn svg { width: 16px; height: 16px; }
        .btn-primary { background: var(--primary); color: var(--primary-foreground); }
        .btn-primary:hover { opacity: 0.9; }
        .btn-outline { background: transparent; color: var(--foreground); border: 1px solid var(--border); }
        .btn-outline:hover { background: var(--muted); }

        /* Loading Overlay */
        .loading-overlay { position: fixed; inset: 0; z-index: 2000; background: rgba(61,50,38,0.45); backdrop-filter: blur(3px); display: flex; align-items: center; justify-content: center; opacity: 0; visibility: hidden; transition: opacity 0.2s ease, visibility 0.2s ease; }
        .loading-overlay.visible { opacity: 1; visibility: visible; }
        .loading-box { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 40px 56px; display: flex; flex-direction: column; align-items: center; gap: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.25); transform: scale(0.92); transition: transform 0.2s ease; }
        .loading-overlay.visible .loading-box { transform: scale(1); }
        .loading-spinner { width: 48px; height: 48px; border: 3px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.75s linear infinite; }
        .loading-text { font-size: 15px; font-weight: 600; color: var(--foreground); }
        .loading-subtext { font-size: 13px; color: var(--muted-foreground); margin-top: -12px; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        /* Toast */
        .toast-container { position: fixed; top: 16px; right: 16px; z-index: 1000; display: flex; flex-direction: column; gap: 8px; }
        .toast { padding: 14px 20px; border-radius: 10px; font-size: 14px; font-weight: 500; box-shadow: 0 4px 12px rgba(0,0,0,0.15); animation: toast-in 0.3s ease; max-width: 380px; }
        .toast.success { background: #2f7d32; color: white; }
        .toast.error { background: var(--destructive); color: white; }
        .toast-desc { font-size: 13px; font-weight: 400; opacity: 0.9; margin-top: 2px; }
        @keyframes toast-in { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes toast-out { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }

        /* Sidebar Overlay */
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 35; }
        .sidebar-overlay.visible { display: block; }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-260px); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .content-area { padding: 16px; }
            .form-grid.cols-2 { grid-template-columns: 1fr; }
            .form-actions { flex-direction: column-reverse; }
        }
    </style>
</head>
<body>

<div class="toast-container" id="toastContainer"></div>

<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-box">
        <div class="loading-spinner"></div>
        <span class="loading-text">Kaydediliyor...</span>
        <span class="loading-subtext">Lütfen bekleyin</span>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="app-layout">

    @include('partials.sidebar')

    <main class="main-content" id="mainContent">

        <header class="top-header">
            <button class="sidebar-trigger" id="sidebarToggle" aria-label="Sidebar toggle">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/></svg>
            </button>
            <div class="header-separator"></div>
            <nav class="breadcrumb">
                <a href="{{ route('kutuphane.index') }}" class="breadcrumb-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Kütüphaneler
                </a>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-current">Yeni Kütüphane</span>
            </nav>
        </header>

        <div class="content-area">
            <form id="kutuphanEkleForm" class="form-card" method="POST" action="{{ route('kutuphane.store') }}" novalidate>
                @csrf

                <div class="form-card-header">
                    <h2 class="form-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        Yeni Kütüphane
                    </h2>
                    <p class="form-card-desc">Sisteme yeni bir kütüphane şubesi ekleyin.</p>
                </div>
                <div class="form-card-separator"></div>

                <div class="form-card-body">

                    <!-- Bölüm 1: Temel Bilgiler -->
                    <div>
                        <h3 class="section-header">
                            <span class="section-number">1</span>
                            Temel Bilgiler
                        </h3>
                        <div class="form-grid cols-2">
                            <div class="form-field" style="grid-column: span 2;">
                                <label class="form-label" for="title">Kütüphane Adı <span class="required">*</span></label>
                                <input type="text" class="form-input" id="title" name="title"
                                       placeholder="Örnek: Örnektepe Kütüphanesi"
                                       value="{{ old('title') }}" required />
                                @error('title')<span class="form-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="statu">Durum <span class="required">*</span></label>
                                <select class="form-select" id="statu" name="statu">
                                    <option value="aktif" {{ old('statu', 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="pasif" {{ old('statu') === 'pasif' ? 'selected' : '' }}>Pasif</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="section-sep"></div>

                    <!-- Bölüm 2: İletişim Bilgileri -->
                    <div>
                        <h3 class="section-header">
                            <span class="section-number">2</span>
                            İletişim Bilgileri
                        </h3>
                        <div class="form-grid cols-2">
                            <div class="form-field">
                                <label class="form-label" for="phone">Telefon</label>
                                <input type="text" class="form-input" id="phone" name="phone"
                                       placeholder="Örnek: 0212 555 00 00"
                                       value="{{ old('phone') }}" />
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="email">E-posta</label>
                                <input type="email" class="form-input" id="email" name="email"
                                       placeholder="Örnek: bilgi@kutuphane.gov.tr"
                                       value="{{ old('email') }}" />
                                @error('email')<span class="form-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-field" style="grid-column: span 2;">
                                <label class="form-label" for="address">Adres</label>
                                <textarea class="form-textarea" id="address" name="address"
                                          placeholder="Kütüphanenin tam adresi" rows="3">{{ old('address') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="form-actions">
                        <a href="{{ route('kutuphane.index') }}" class="btn btn-outline">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            Vazgeç
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v13a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            Kaydet
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </main>
</div>

<script>
    // Sidebar
    var sidebar = document.getElementById('sidebar');
    var mainContent = document.getElementById('mainContent');
    var sidebarToggle = document.getElementById('sidebarToggle');
    var sidebarOverlay = document.getElementById('sidebarOverlay');
    var isMobile = window.innerWidth <= 768;
    function toggleSidebar() {
        if (isMobile) { sidebar.classList.toggle('open'); sidebarOverlay.classList.toggle('visible'); }
        else { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); }
    }
    sidebarToggle.addEventListener('click', toggleSidebar);
    sidebarOverlay.addEventListener('click', function() { sidebar.classList.remove('open'); sidebarOverlay.classList.remove('visible'); });

    // Toast
    function showToast(type, title, desc) {
        var c = document.getElementById('toastContainer');
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<div>' + title + '</div>' + (desc ? '<div class="toast-desc">' + desc + '</div>' : '');
        c.appendChild(t);
        setTimeout(function() { t.style.animation = 'toast-out 0.3s ease forwards'; setTimeout(function() { if (t.parentNode) t.parentNode.removeChild(t); }, 300); }, 3500);
    }

    // AJAX Submit
    var form = document.getElementById('kutuphanEkleForm');
    var submitBtn = document.getElementById('submitBtn');
    var submitBtnOriginalHtml = submitBtn.innerHTML;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        var title = document.getElementById('title').value.trim();
        if (!title) {
            showToast('error', 'Zorunlu alan eksik', 'Kütüphane adı boş bırakılamaz.');
            document.getElementById('title').focus();
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin 0.8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Kaydediliyor...';
        document.getElementById('loadingOverlay').classList.add('visible');

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: new FormData(form)
        })
        .then(function(res) {
            return res.json().then(function(data) { return { status: res.status, data: data }; });
        })
        .then(function(result) {
            if (result.status === 200 && result.data.success) {
                showToast('success', 'Kayıt Başarılı', result.data.message);
                form.reset();
                document.getElementById('statu').value = 'aktif';
            } else if (result.status === 422 && result.data.errors) {
                var msgs = Object.values(result.data.errors).flat();
                showToast('error', 'Doğrulama Hatası', msgs[0]);
            } else {
                showToast('error', 'Hata', result.data.message || 'Kayıt sırasında bir hata oluştu.');
            }
        })
        .catch(function() {
            showToast('error', 'Bağlantı Hatası', 'Sunucuya ulaşılamadı. Lütfen tekrar deneyin.');
        })
        .finally(function() {
            submitBtn.disabled = false;
            submitBtn.innerHTML = submitBtnOriginalHtml;
            document.getElementById('loadingOverlay').classList.remove('visible');
        });
    });
</script>
</body>
</html>
