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
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700;900&family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --background: #f5f0e8;
            --foreground: #3d3226;
            --card: #faf8f3;
            --primary: #7a5c3c;
            --primary-foreground: #f5f0e8;
            --secondary: #ede8de;
            --muted: #ede8de;
            --muted-foreground: #7a7060;
            --accent: #9b6b3f;
            --destructive: #c53030;
            --border: #d9d0c2;
            --ring: #7a5c3c;
            --radius: 0.625rem;
            --sidebar: #3d3226;
            --sidebar-foreground: #e8e2d6;
            --sidebar-primary: #9b7b55;
            --sidebar-primary-foreground: #f5f0e8;
            --sidebar-accent: #524435;
            --sidebar-accent-foreground: #e8e2d6;
            --sidebar-border: #5a4a3a;
            --font-sans: 'Source Sans 3', system-ui, sans-serif;
            --font-serif: 'Merriweather', Georgia, serif;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: var(--font-sans); background: var(--background); color: var(--foreground); -webkit-font-smoothing: antialiased; line-height: 1.5; }
        input, select, button { font-family: inherit; font-size: inherit; }

        /* Layout */
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
        .sidebar-menu-item { display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 6px; font-size: 14px; font-weight: 500; color: var(--sidebar-foreground); cursor: pointer; transition: background 0.15s ease; text-decoration: none; }
        .sidebar-menu-item:hover { background: var(--sidebar-accent); }
        .sidebar-menu-item.active { background: var(--sidebar-accent); color: var(--sidebar-accent-foreground); }
        .sidebar-menu-item svg { width: 18px; height: 18px; flex-shrink: 0; opacity: 0.8; }
        .sidebar-footer { padding: 16px; border-top: 1px solid var(--sidebar-border); }
        .sidebar-avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--sidebar-accent); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600; flex-shrink: 0; }
        .sidebar-user { display: flex; align-items: center; gap: 12px; }
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
        .header-actions { margin-left: auto; display: flex; align-items: center; gap: 8px; }

        /* Content */
        .content-area { flex: 1; padding: 24px; display: flex; flex-direction: column; gap: 20px; }

        /* Page Header */
        .page-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .page-title { font-family: var(--font-serif); font-size: 22px; font-weight: 700; color: var(--foreground); }
        .page-subtitle { font-size: 13px; color: var(--muted-foreground); margin-top: 2px; }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 9px 16px; border-radius: calc(var(--radius) - 2px); font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.15s, opacity 0.15s; border: none; text-decoration: none; }
        .btn svg { width: 16px; height: 16px; }
        .btn-primary { background: var(--primary); color: var(--primary-foreground); }
        .btn-primary:hover { opacity: 0.9; }
        .btn-outline { background: transparent; color: var(--foreground); border: 1px solid var(--border); }
        .btn-outline:hover { background: var(--muted); }
        .btn-sm { padding: 6px 12px; font-size: 13px; }

        /* Search Bar */
        .search-bar { display: flex; gap: 10px; align-items: center; }
        .search-input-wrap { position: relative; flex: 1; max-width: 360px; }
        .search-input-wrap svg { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--muted-foreground); pointer-events: none; }
        .search-input { width: 100%; padding: 8px 12px 8px 34px; border: 1px solid var(--border); border-radius: calc(var(--radius) - 2px); background: var(--card); color: var(--foreground); font-size: 14px; outline: none; transition: border-color 0.15s, box-shadow 0.15s; }
        .search-input:focus { border-color: var(--ring); box-shadow: 0 0 0 2px rgba(122,92,60,0.15); }
        .search-input::placeholder { color: var(--muted-foreground); opacity: 0.7; }

        /* Table Card */
        .table-card { background: var(--card); border: 1px solid rgba(217,208,194,0.6); border-radius: var(--radius); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: var(--secondary); }
        th { padding: 11px 16px; text-align: left; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--muted-foreground); white-space: nowrap; border-bottom: 1px solid var(--border); }
        td { padding: 13px 16px; font-size: 14px; border-bottom: 1px solid rgba(217,208,194,0.4); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tbody tr { transition: background 0.12s; }
        tbody tr:hover { background: rgba(237,232,222,0.5); }

        /* Badge */
        .badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .badge-green { background: rgba(34,139,34,0.12); color: #1a6b1a; }
        .badge-red { background: rgba(197,48,48,0.1); color: #9b1c1c; }
        .badge svg { width: 8px; height: 8px; }

        /* Table Footer */
        .table-footer { padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid rgba(217,208,194,0.5); font-size: 13px; color: var(--muted-foreground); }

        /* Empty State */
        .empty-state { padding: 60px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 12px; }
        .empty-icon { width: 56px; height: 56px; border-radius: 50%; background: var(--muted); display: flex; align-items: center; justify-content: center; }
        .empty-icon svg { width: 28px; height: 28px; color: var(--muted-foreground); }
        .empty-title { font-size: 15px; font-weight: 600; color: var(--foreground); }
        .empty-desc { font-size: 13px; color: var(--muted-foreground); }

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

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-260px); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .content-area { padding: 16px; }
            .page-header { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>

<div class="toast-container" id="toastContainer"></div>
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
                <a href="{{ route('katalog.index') }}" class="breadcrumb-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Ana Sayfa
                </a>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-current">Kütüphaneler</span>
            </nav>
        </header>

        <div class="content-area">

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Kütüphaneler</h1>
                    <p class="page-subtitle">Sistemde kayıtlı {{ $kutuphaneler->total() }} kütüphane</p>
                </div>
                <a href="{{ route('kutuphane.new') }}" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>
                    Yeni Kütüphane
                </a>
            </div>

            <!-- Search -->
            <div class="search-bar">
                <div class="search-input-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" class="search-input" id="searchInput" placeholder="Kütüphane adı ara..." value="{{ request('search') }}" />
                </div>
            </div>

            <!-- Table -->
            <div class="table-card">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kütüphane Adı</th>
                                <th>Adres</th>
                                <th>Telefon</th>
                                <th>E-posta</th>
                                <th>Durum</th>
                                <th>Kayıt Tarihi</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kutuphaneler as $kutuphane)
                            <tr>
                                <td style="color:var(--muted-foreground);font-size:13px;">{{ $kutuphane->id }}</td>
                                <td style="font-weight:600;">{{ $kutuphane->title }}</td>
                                <td style="color:var(--muted-foreground);font-size:13px;">{{ $kutuphane->address ?? '—' }}</td>
                                <td style="font-size:13px;">{{ $kutuphane->phone ?? '—' }}</td>
                                <td style="font-size:13px;">{{ $kutuphane->email ?? '—' }}</td>
                                <td>
                                    @if($kutuphane->statu === 'aktif')
                                        <span class="badge badge-green">
                                            <svg viewBox="0 0 8 8" fill="currentColor"><circle cx="4" cy="4" r="4"/></svg>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="badge badge-red">
                                            <svg viewBox="0 0 8 8" fill="currentColor"><circle cx="4" cy="4" r="4"/></svg>
                                            Pasif
                                        </span>
                                    @endif
                                </td>
                                <td style="font-size:13px;color:var(--muted-foreground);">{{ \Carbon\Carbon::parse($kutuphane->created_at)->format('d.m.Y') }}</td>
                                @if(auth()->user()->hasYetki(19))
                                <td>
                                    <a href="{{ route('kutuphane.edit', $kutuphane->id) }}" class="btn btn-outline btn-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Düzenle
                                    </a>
                                </td>
                                @endif
                                
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                        </div>
                                        <p class="empty-title">Kütüphane bulunamadı</p>
                                        <p class="empty-desc">Yeni kütüphane eklemek için "Yeni Kütüphane" butonunu kullanın.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="table-footer">
                    <span>{{ $kutuphaneler->firstItem() ?? 0 }}–{{ $kutuphaneler->lastItem() ?? 0 }} / {{ $kutuphaneler->total() }} kayıt</span>
                    <div style="display:flex;gap:6px;">
                        @if($kutuphaneler->onFirstPage())
                            <button class="btn btn-outline btn-sm" disabled style="opacity:0.4;cursor:default;">‹ Önceki</button>
                        @else
                            <a href="{{ $kutuphaneler->previousPageUrl() }}" class="btn btn-outline btn-sm">‹ Önceki</a>
                        @endif
                        @if($kutuphaneler->hasMorePages())
                            <a href="{{ $kutuphaneler->nextPageUrl() }}" class="btn btn-outline btn-sm">Sonraki ›</a>
                        @else
                            <button class="btn btn-outline btn-sm" disabled style="opacity:0.4;cursor:default;">Sonraki ›</button>
                        @endif
                    </div>
                </div>
            </div>

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
    window.addEventListener('resize', function() { isMobile = window.innerWidth <= 768; });

    // Toast
    function showToast(type, title, desc) {
        var c = document.getElementById('toastContainer');
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<div>' + title + '</div>' + (desc ? '<div class="toast-desc">' + desc + '</div>' : '');
        c.appendChild(t);
        setTimeout(function() { t.style.animation = 'toast-out 0.3s ease forwards'; setTimeout(function() { if (t.parentNode) t.parentNode.removeChild(t); }, 300); }, 3500);
    }

    @if(session('success'))
        showToast('success', 'Başarılı', '{{ session('success') }}');
    @endif

    // Arama — 400ms debounce ile URL'yi güncelle
    var searchInput = document.getElementById('searchInput');
    var searchTimer;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            var q = searchInput.value.trim();
            var url = new URL(window.location.href);
            if (q) url.searchParams.set('search', q);
            else url.searchParams.delete('search');
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }, 400);
    });
</script>
</body>
</html>
