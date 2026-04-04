<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#7a5c3c" />
    <title>Üyeler — Beyoğlu Kütüphane Sistemi</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700;900&family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --background: #f5f0e8; --foreground: #3d3226; --card: #faf8f3;
            --primary: #7a5c3c; --primary-foreground: #f5f0e8;
            --secondary: #ede8de; --muted: #ede8de; --muted-foreground: #7a7060;
            --destructive: #c53030; --border: #d9d0c2; --ring: #7a5c3c; --radius: 0.625rem;
            --sidebar: #3d3226; --sidebar-foreground: #e8e2d6;
            --sidebar-primary: #9b7b55; --sidebar-primary-foreground: #f5f0e8;
            --sidebar-accent: #524435; --sidebar-accent-foreground: #e8e2d6;
            --sidebar-border: #5a4a3a;
            --font-sans: 'Source Sans 3', system-ui, sans-serif;
            --font-serif: 'Merriweather', Georgia, serif;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: var(--font-sans); background: var(--background); color: var(--foreground); -webkit-font-smoothing: antialiased; line-height: 1.5; }
        input, select, button { font-family: inherit; font-size: inherit; }
        .app-layout { display: flex; min-height: 100vh; }

        /* ── Sidebar ── */
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
        .sidebar-menu-item.active { background: var(--sidebar-accent); color: var(--sidebar-accent-foreground); }
        .sidebar-menu-item svg { width: 18px; height: 18px; flex-shrink: 0; opacity: 0.8; }
        .sidebar-footer { padding: 16px; border-top: 1px solid var(--sidebar-border); }
        .sidebar-user { display: flex; align-items: center; gap: 12px; }
        .sidebar-avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--sidebar-accent); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600; flex-shrink: 0; }
        .sidebar-user-name { font-size: 14px; font-weight: 500; }
        .sidebar-user-role { font-size: 12px; opacity: 0.6; }

        /* ── Main ── */
        .main-content { flex: 1; margin-left: 260px; display: flex; flex-direction: column; min-height: 100vh; transition: margin-left 0.3s ease; }
        .main-content.expanded { margin-left: 0; }
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
        .content-area { flex: 1; padding: 24px; display: flex; flex-direction: column; gap: 20px; }

        /* ── Page Header ── */
        .page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
        .page-title { font-family: var(--font-serif); font-size: 22px; font-weight: 700; }
        .page-subtitle { font-size: 13px; color: var(--muted-foreground); margin-top: 2px; }
        .page-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }

        /* ── Buttons ── */
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 8px 15px; border-radius: calc(var(--radius) - 2px); font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.15s, opacity 0.15s; border: none; text-decoration: none; white-space: nowrap; }
        .btn svg { width: 15px; height: 15px; flex-shrink: 0; }
        .btn-primary { background: var(--primary); color: var(--primary-foreground); }
        .btn-primary:hover { opacity: 0.9; }
        .btn-outline { background: transparent; color: var(--foreground); border: 1px solid var(--border); }
        .btn-outline:hover { background: var(--muted); }
        .btn-success { background: #2f7d32; color: white; }
        .btn-success:hover { opacity: 0.88; }
        .btn-sm { padding: 6px 12px; font-size: 13px; }
        .btn-sm svg { width: 14px; height: 14px; }

        /* ── Toolbar ── */
        .toolbar { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .filter-wrap { position: relative; }
        .filter-wrap svg.fi { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: var(--muted-foreground); pointer-events: none; }
        .filter-input { padding: 8px 12px 8px 33px; border: 1px solid var(--border); border-radius: calc(var(--radius) - 2px); background: var(--card); color: var(--foreground); font-size: 14px; outline: none; transition: border-color 0.15s, box-shadow 0.15s; min-width: 260px; }
        .filter-input:focus { border-color: var(--ring); box-shadow: 0 0 0 2px rgba(122,92,60,0.14); }
        .filter-input::placeholder { color: var(--muted-foreground); opacity: 0.7; }
        .filter-select { padding: 8px 32px 8px 10px; border: 1px solid var(--border); border-radius: calc(var(--radius) - 2px); background: var(--card); color: var(--foreground); font-size: 14px; outline: none; cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237a7060' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; transition: border-color 0.15s, box-shadow 0.15s; }
        .filter-select:focus { border-color: var(--ring); box-shadow: 0 0 0 2px rgba(122,92,60,0.14); }
        .filter-active { border-color: var(--primary) !important; background: rgba(122,92,60,0.04) !important; }

        /* ── Table ── */
        .table-card { background: var(--card); border: 1px solid rgba(217,208,194,0.6); border-radius: var(--radius); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04); position: relative; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: var(--secondary); }
        th { padding: 11px 16px; text-align: left; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--muted-foreground); white-space: nowrap; border-bottom: 1px solid var(--border); }
        td { padding: 12px 16px; font-size: 14px; border-bottom: 1px solid rgba(217,208,194,0.4); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tbody tr { transition: background 0.1s; }
        tbody tr:hover { background: rgba(237,232,222,0.5); }

        /* Member cell */
        .member-cell { display: flex; align-items: center; gap: 12px; }
        .member-avatar { width: 34px; height: 34px; border-radius: 50%; background: var(--sidebar-accent); color: var(--sidebar-foreground); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0; }
        .member-name { font-weight: 600; font-size: 14px; }
        .member-sub { font-size: 12px; color: var(--muted-foreground); }

        /* Badge */
        .badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .badge-aktif { background: rgba(34,197,94,0.1); color: #166534; }
        .badge-pasif { background: rgba(156,163,175,0.15); color: #4b5563; }

        /* Loading veil */
        .table-veil { position: absolute; inset: 0; background: rgba(250,248,243,0.75); display: flex; align-items: center; justify-content: center; z-index: 10; border-radius: var(--radius); opacity: 0; visibility: hidden; transition: opacity 0.18s, visibility 0.18s; pointer-events: none; }
        .table-veil.visible { opacity: 1; visibility: visible; pointer-events: all; }
        .veil-spinner { width: 32px; height: 32px; border: 3px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Table footer / pagination ── */
        .table-footer { padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px; border-top: 1px solid rgba(217,208,194,0.5); font-size: 13px; color: var(--muted-foreground); flex-wrap: wrap; }
        .tf-info { display: flex; align-items: center; gap: 12px; }
        .per-page-wrap { display: flex; align-items: center; gap: 6px; font-size: 13px; }
        .per-page-select { padding: 4px 28px 4px 8px; border: 1px solid var(--border); border-radius: calc(var(--radius) - 2px); background: var(--card); color: var(--foreground); font-size: 13px; outline: none; cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%237a7060' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 8px center; transition: border-color 0.15s; }
        .per-page-select:focus { border-color: var(--ring); }

        .pagination { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }
        .page-btn { display: inline-flex; align-items: center; justify-content: center; min-width: 32px; height: 32px; padding: 0 6px; border-radius: calc(var(--radius) - 2px); font-size: 13px; font-weight: 500; cursor: pointer; border: 1px solid var(--border); background: var(--card); color: var(--foreground); transition: background 0.12s, border-color 0.12s; user-select: none; }
        .page-btn:hover:not(.disabled):not(.active) { background: var(--muted); }
        .page-btn.active { background: var(--primary); color: var(--primary-foreground); border-color: var(--primary); cursor: default; }
        .page-btn.disabled { opacity: 0.38; cursor: default; pointer-events: none; }
        .page-btn svg { width: 13px; height: 13px; }
        .page-ellipsis { padding: 0 4px; color: var(--muted-foreground); font-size: 13px; }

        /* ── Empty ── */
        .empty-state { padding: 56px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 12px; }
        .empty-icon { width: 56px; height: 56px; border-radius: 50%; background: var(--muted); display: flex; align-items: center; justify-content: center; }
        .empty-icon svg { width: 28px; height: 28px; color: var(--muted-foreground); }
        .empty-title { font-size: 15px; font-weight: 600; }
        .empty-desc { font-size: 13px; color: var(--muted-foreground); }

        /* ── Toast ── */
        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 3000; display: flex; flex-direction: column; gap: 10px; }
        .toast { padding: 14px 18px; border-radius: var(--radius); font-size: 14px; font-weight: 500; min-width: 280px; box-shadow: 0 4px 16px rgba(0,0,0,0.12); border: 1px solid transparent; animation: toast-in 0.3s ease; }
        .toast.success { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
        .toast.error   { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
        .toast-desc { font-size: 13px; opacity: 0.8; margin-top: 2px; }
        @keyframes toast-in  { from { opacity:0; transform: translateX(20px); } to { opacity:1; transform: translateX(0); } }
        @keyframes toast-out { from { opacity:1; } to { opacity:0; transform: translateX(20px); } }

        /* ── Row Actions Dropdown ── */
        .row-actions-btn { width: 32px; height: 32px; border-radius: 6px; border: none; background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--muted-foreground); transition: background 0.15s, color 0.15s; }
        .row-actions-btn:hover { background: var(--muted); color: var(--foreground); }
        .row-actions-btn svg { width: 16px; height: 16px; pointer-events: none; }
        /* Floating menü — body'e eklenir, overflow:hidden'dan etkilenmez */
        #uyeFloatingMenu { position: fixed; background: var(--card); border: 1px solid var(--border); border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,0.14); min-width: 160px; z-index: 9999; padding: 4px; display: none; }
        #uyeFloatingMenu.open { display: block; animation: ram-in 0.15s ease; }
        @keyframes ram-in { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }
        .row-actions-item { display: flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 6px; font-size: 14px; font-weight: 500; color: var(--foreground); text-decoration: none; cursor: pointer; transition: background 0.12s; white-space: nowrap; border: none; background: transparent; width: 100%; text-align: left; font-family: inherit; }
        .row-actions-item:hover { background: var(--secondary); }
        .row-actions-item svg { width: 15px; height: 15px; flex-shrink: 0; color: var(--muted-foreground); }
        .row-actions-item.danger { color: var(--destructive); }
        .row-actions-item.danger svg { color: var(--destructive); }
        .row-actions-item.danger:hover { background: rgba(197,48,48,0.06); }
        .row-actions-item.primary { color: var(--primary); }
        .row-actions-item.primary svg { color: var(--primary); }
        .row-actions-sep { height: 1px; background: var(--border); margin: 4px 0; }

        /* ── Delete Modal ── */
        .modal-backdrop { position: fixed; inset: 0; z-index: 2000; background: rgba(61,50,38,0.45); backdrop-filter: blur(3px); display: flex; align-items: center; justify-content: center; opacity: 0; visibility: hidden; transition: opacity 0.2s, visibility 0.2s; }
        .modal-backdrop.visible { opacity: 1; visibility: visible; }
        .modal-box { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 32px; max-width: 400px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.2); transform: scale(0.93); transition: transform 0.2s; }
        .modal-backdrop.visible .modal-box { transform: scale(1); }
        .modal-icon { width: 48px; height: 48px; border-radius: 50%; background: rgba(197,48,48,0.1); display: flex; align-items: center; justify-content: center; margin-bottom: 16px; }
        .modal-icon svg { width: 24px; height: 24px; color: var(--destructive); }
        .modal-title { font-family: var(--font-serif); font-size: 18px; font-weight: 700; margin-bottom: 8px; }
        .modal-desc { font-size: 14px; color: var(--muted-foreground); margin-bottom: 24px; line-height: 1.6; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; }

        /* ── Sidebar overlay ── */
        .sidebar-overlay { display: none; position: fixed; inset: 0; z-index: 39; background: rgba(0,0,0,0.4); }
        .sidebar-overlay.visible { display: block; }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; }
            .sidebar { transform: translateX(-260px); }
            .sidebar.open { transform: translateX(0); }
            .content-area { padding: 16px; }
            .page-header { flex-direction: column; align-items: flex-start; }
            .toolbar { flex-direction: column; align-items: stretch; }
            .filter-input { min-width: 0; width: 100%; }
        }
    </style>
</head>
<body>
<div class="app-layout">

    @include('partials.sidebar')
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="main-content" id="mainContent">

        <div class="top-header">
            <button class="sidebar-trigger" id="sidebarToggle">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
            </button>
            <div class="header-separator"></div>
            <nav class="breadcrumb">
                <a href="{{ route('katalog.index') }}" class="breadcrumb-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Ana Sayfa
                </a>
                <span class="breadcrumb-sep">›</span>
                <span class="breadcrumb-current">Üyeler</span>
            </nav>
        </div>

        <div class="content-area">

            <!-- Sayfa Başlığı -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Üye Listesi</h1>
                    <p class="page-subtitle" id="pageSubtitle">Yükleniyor…</p>
                </div>
                <div class="page-actions">
                    <button class="btn btn-success" id="exportBtn" title="Mevcut filtreyle Excel (CSV) indir">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                        Excel İndir
                    </button>
                    @if(auth()->user()->hasYetki(12))
                    <a href="{{ route('uyeler.new') }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>
                        Yeni Üye Ekle
                    </a>
                    @endif
                    
                </div>
            </div>

            <!-- Filtreler -->
            <div class="toolbar">
                <div class="filter-wrap">
                    <svg class="fi" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" class="filter-input" id="searchInput" placeholder="Ad, soyad, TC kimlik, telefon, e-posta ara…" autocomplete="off" />
                </div>
                <select class="filter-select" id="statuFilter">
                    <option value="">Tüm Durumlar</option>
                    <option value="aktif">Aktif</option>
                    <option value="pasif">Pasif</option>
                </select>
                <button class="btn btn-outline btn-sm" id="clearFiltersBtn" style="display:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    Temizle
                </button>
            </div>

            <!-- Tablo -->
            <div class="table-card" id="tableCard">
                <div class="table-veil" id="tableVeil">
                    <div class="veil-spinner"></div>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Üye</th>
                            <th style="width:130px;">TC Kimlik No</th>
                            <th style="width:140px;">Telefon</th>
                            <th style="width:130px;">İl / İlçe</th>
                            <th style="width:90px;">Durum</th>
                            <th style="width:110px;">Üyelik Tarihi</th>
                            <th style="width:130px;"></th>
                        </tr>
                        </thead>
                        <tbody id="tableBody">
                        <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--muted-foreground);font-size:13px;">Yükleniyor…</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <div class="tf-info">
                        <span id="rangeInfo">—</span>
                        <div class="per-page-wrap">
                            <label for="perPageSelect" style="white-space:nowrap;">Sayfa başına:</label>
                            <select class="per-page-select" id="perPageSelect">
                                <option value="10" selected>10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="500">500</option>
                            </select>
                        </div>
                    </div>
                    <div class="pagination" id="pagination"></div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- Floating İşlemler Menüsü (overflow:hidden'dan etkilenmemesi için body'de) -->
<div id="uyeFloatingMenu">
    <a id="fmDuzenle" href="#" class="row-actions-item">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
        Düzenle
    </a>
    <div class="row-actions-sep"></div>
    <a id="fmOduncVer" href="#" class="row-actions-item primary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M12 18v-6"/><path d="M9 15h6"/></svg>
        Ödünç Ver
    </a>
    <div class="row-actions-sep"></div>
    <button id="fmSil" class="row-actions-item danger">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
        Sil
    </button>
</div>

<!-- Toast -->
<div class="toast-container" id="toastContainer"></div>

<!-- Delete Modal -->
<div class="modal-backdrop" id="deleteModal">
    <div class="modal-box">
        <div class="modal-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
        </div>
        <h2 class="modal-title">Üyeyi Sil</h2>
        <p class="modal-desc" id="deleteModalDesc"></p>
        <div class="modal-actions">
            <button class="btn btn-outline" id="deleteCancelBtn">Vazgeç</button>
            <form id="deleteForm" method="POST" style="margin:0;">
                @csrf @method('DELETE')
                <button type="submit" class="btn" style="background:var(--destructive);color:#fff;">Evet, Sil</button>
            </form>
        </div>
    </div>
</div>

<script>
    // ══════════════════════════════════════════════════════════════════════════════
    // Sidebar
    // ══════════════════════════════════════════════════════════════════════════════
    var sidebar = document.getElementById('sidebar');
    var mainContent = document.getElementById('mainContent');
    var sidebarOverlay = document.getElementById('sidebarOverlay');
    var isMobile = window.innerWidth <= 768;
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        if (isMobile) { sidebar.classList.toggle('open'); sidebarOverlay.classList.toggle('visible'); }
        else { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); }
    });
    sidebarOverlay.addEventListener('click', function() { sidebar.classList.remove('open'); sidebarOverlay.classList.remove('visible'); });
    window.addEventListener('resize', function() { isMobile = window.innerWidth <= 768; });

    // ══════════════════════════════════════════════════════════════════════════════
    // Toast
    // ══════════════════════════════════════════════════════════════════════════════
    function showToast(type, title, desc) {
        var c = document.getElementById('toastContainer');
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<div>' + title + '</div>' + (desc ? '<div class="toast-desc">' + desc + '</div>' : '');
        c.appendChild(t);
        setTimeout(function() {
            t.style.animation = 'toast-out 0.3s ease forwards';
            setTimeout(function() { if (t.parentNode) t.parentNode.removeChild(t); }, 300);
        }, 4000);
    }
    @if(session('success')) showToast('success', '{{ session('success') }}'); @endif
    @if(session('error'))   showToast('error',   '{{ session('error') }}');   @endif

    // ══════════════════════════════════════════════════════════════════════════════
    // State + request counter
    // ══════════════════════════════════════════════════════════════════════════════
    var state      = { search: '', statu: '', per_page: 10, page: 1 };
    var fetchTimer = null;
    var reqCounter = 0;   // yalnızca en son isteğin yanıtı işlenir

    // ══════════════════════════════════════════════════════════════════════════════
    // HTML helpers
    // ══════════════════════════════════════════════════════════════════════════════
    function esc(str) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(str || ''));
        return d.innerHTML;
    }

    var checkIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';

    function buildRow(u) {
        var ilIlce = [u.il, u.ilce].filter(Boolean).join(' / ') || '—';
        var telHtml = esc(u.telefon || '—') + (u.telefon_dogrulandi
            ? ' <span title="Doğrulanmış" style="color:#16a34a;vertical-align:middle;">' + checkIcon + '</span>'
            : '');
        var stateBadge = '<span class="badge badge-' + u.statu + '">' + esc(u.statu_label) + '</span>';

        var safeAd = esc(u.ad_soyad).replace(/'/g, "\\'");
        return '<tr>' +
            '<td>' +
            '<div class="member-cell">' +
            '<div class="member-avatar">' + esc(u.initials || '?') + '</div>' +
            '<div>' +
            '<div class="member-name">' + esc(u.ad_soyad) + '</div>' +
            '<div class="member-sub">' + esc(u.email || '—') + '</div>' +
            '</div>' +
            '</div>' +
            '</td>' +
            '<td style="font-family:monospace;font-size:13px;letter-spacing:0.04em;">' + esc(u.tc_kimlik) + '</td>' +
            '<td>' + telHtml + '</td>' +
            '<td style="font-size:13px;">' + esc(ilIlce) + '</td>' +
            '<td>' + stateBadge + '</td>' +
            '<td style="font-size:13px;color:var(--muted-foreground);">' + esc(u.uyelik_baslangic) + '</td>' +
            '<td style="text-align:right;padding-right:12px;">' +
            '<button class="row-actions-btn" onclick="toggleUyeMenu(' + u.id + ', \'' + u.edit_url + '\', \'' + u.delete_url + '\', \'' + safeAd + '\', event)" title="İşlemler">' +
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>' +
            '</button>' +
            '</td>' +
            '</tr>';
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // Pagination
    // ══════════════════════════════════════════════════════════════════════════════
    function buildPagination(meta) {
        var container = document.getElementById('pagination');
        if (meta.last_page <= 1) { container.innerHTML = ''; return; }

        var cur = meta.current_page, last = meta.last_page;
        var html = '';

        html += '<button class="page-btn ' + (cur <= 1 ? 'disabled' : '') + '" onclick="goPage(' + (cur - 1) + ')">' +
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>' +
            '</button>';

        var pages = [];
        if (last <= 7) {
            for (var i = 1; i <= last; i++) pages.push(i);
        } else {
            pages.push(1);
            if (cur > 3) pages.push('…');
            for (var i = Math.max(2, cur - 1); i <= Math.min(last - 1, cur + 1); i++) pages.push(i);
            if (cur < last - 2) pages.push('…');
            pages.push(last);
        }
        pages.forEach(function(p) {
            if (p === '…') { html += '<span class="page-ellipsis">…</span>'; }
            else { html += '<button class="page-btn ' + (p === cur ? 'active' : '') + '" onclick="goPage(' + p + ')">' + p + '</button>'; }
        });

        html += '<button class="page-btn ' + (cur >= last ? 'disabled' : '') + '" onclick="goPage(' + (cur + 1) + ')">' +
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>' +
            '</button>';

        container.innerHTML = html;
    }

    function goPage(p) { if (p < 1) return; state.page = p; fetchTable(); }

    // ══════════════════════════════════════════════════════════════════════════════
    // AJAX Fetch — reqCounter ile stale response önlemi
    // ══════════════════════════════════════════════════════════════════════════════
    function fetchTable(resetPage) {
        if (resetPage) state.page = 1;

        // Bu isteğe ait tekil numara — yalnızca bu numara hâlâ güncel sayı ile
        // eşleşiyorsa cevap DOM'a yazılır. Bu sayede hızlı filtre değişikliğinde
        // önceki yavaş yanıt yeni yanıtın üzerine yazmaz.
        var myReq = ++reqCounter;

        document.getElementById('tableVeil').classList.add('visible');

        var params = new URLSearchParams({
            search:   state.search,
            statu:    state.statu,
            per_page: state.per_page,
            page:     state.page,
        });

        fetch('/uyeler/tablo?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function(res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function(result) {
                if (myReq !== reqCounter) return; // daha yeni bir istek var, bu yanıtı yoksay

                document.getElementById('tableVeil').classList.remove('visible');

                if (!result.success) { showToast('error', 'Hata', 'Veriler yüklenemedi.'); return; }

                var rows = Array.isArray(result.data) ? result.data
                    : (result.data && Array.isArray(result.data.data) ? result.data.data : []);

                var tbody = document.getElementById('tableBody');
                if (rows.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7">' +
                        '<div class="empty-state">' +
                        '<div class="empty-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>' +
                        '<p class="empty-title">Üye bulunamadı</p>' +
                        '<p class="empty-desc">Arama kriterlerinizi değiştirin veya yeni üye ekleyin.</p>' +
                        '</div></td></tr>';
                } else {
                    tbody.innerHTML = rows.map(buildRow).join('');
                }

                var m = result.meta;
                document.getElementById('pageSubtitle').textContent = m.total + ' üye kayıtlı';
                document.getElementById('rangeInfo').textContent    = m.from + '–' + m.to + ' / ' + m.total + ' kayıt';
                buildPagination(m);
                updateClearBtn();
            })
            .catch(function() {
                if (myReq !== reqCounter) return;
                document.getElementById('tableVeil').classList.remove('visible');
                showToast('error', 'Bağlantı Hatası', 'Veriler yüklenemedi.');
            });
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // Filtre dinleyiciler
    // ══════════════════════════════════════════════════════════════════════════════
    function updateClearBtn() {
        var has = state.search !== '' || state.statu !== '';
        document.getElementById('clearFiltersBtn').style.display = has ? '' : 'none';
        document.getElementById('searchInput').classList.toggle('filter-active', state.search !== '');
        document.getElementById('statuFilter').classList.toggle('filter-active', state.statu !== '');
    }

    document.getElementById('searchInput').addEventListener('input', function() {
        state.search = this.value.trim();
        clearTimeout(fetchTimer);
        fetchTimer = setTimeout(function() { fetchTable(true); }, 380);
    });

    document.getElementById('statuFilter').addEventListener('change', function() {
        clearTimeout(fetchTimer);
        state.statu = this.value;
        fetchTable(true);
    });

    document.getElementById('perPageSelect').addEventListener('change', function() {
        clearTimeout(fetchTimer);
        state.per_page = parseInt(this.value);
        fetchTable(true);
    });

    document.getElementById('clearFiltersBtn').addEventListener('click', function() {
        clearTimeout(fetchTimer);
        state.search = ''; state.statu = '';
        document.getElementById('searchInput').value = '';
        document.getElementById('statuFilter').value = '';
        fetchTable(true);
    });

    // ══════════════════════════════════════════════════════════════════════════════
    // Excel Export
    // ══════════════════════════════════════════════════════════════════════════════
    document.getElementById('exportBtn').addEventListener('click', function() {
        var params = new URLSearchParams({ search: state.search, statu: state.statu });
        var a = document.createElement('a');
        a.href = '/uyeler/export?' + params.toString();
        a.download = '';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    });

    // ══════════════════════════════════════════════════════════════════════════════
    // Delete Modal
    // ══════════════════════════════════════════════════════════════════════════════
    var deleteModal = document.getElementById('deleteModal');
    var deleteForm  = document.getElementById('deleteForm');

    function confirmDelete(url, name) {
        document.getElementById('deleteModalDesc').textContent = '"' + name + '" üyesini silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.';
        deleteForm.action = url;
        deleteModal.classList.add('visible');
    }
    document.getElementById('deleteCancelBtn').addEventListener('click', function() { deleteModal.classList.remove('visible'); });
    deleteModal.addEventListener('click', function(e) { if (e.target === deleteModal) deleteModal.classList.remove('visible'); });

    // ══════════════════════════════════════════════════════════════════════════════
    // Row Actions — Floating menü (position:fixed, overflow:hidden'dan etkilenmez)
    // ══════════════════════════════════════════════════════════════════════════════
    var floatingMenu   = document.getElementById('uyeFloatingMenu');
    var openUyeMenuBtn = null; // şu an menüyü açan buton referansı

    function closeUyeMenu() {
        floatingMenu.classList.remove('open');
        openUyeMenuBtn = null;
    }

    function toggleUyeMenu(id, editUrl, deleteUrl, adSoyad, event) {
        event.stopPropagation();
        var btn = event.currentTarget;

        // Aynı butona tekrar basılırsa kapat
        if (openUyeMenuBtn === btn) { closeUyeMenu(); return; }

        // Menü bağlantılarını güncelle
        document.getElementById('fmDuzenle').href  = editUrl;
        document.getElementById('fmOduncVer').href = '/odunc/new?uye_id=' + id;
        document.getElementById('fmSil').onclick   = function(e) {
            e.stopPropagation();
            closeUyeMenu();
            confirmDelete(deleteUrl, adSoyad);
        };

        // Konumlandır: butonun altına veya üstüne (viewport taşmasını önle)
        floatingMenu.style.visibility = 'hidden';
        floatingMenu.classList.add('open');
        var rect    = btn.getBoundingClientRect();
        var mH      = floatingMenu.offsetHeight;
        var mW      = floatingMenu.offsetWidth;
        var spaceBelow = window.innerHeight - rect.bottom;
        var top, left;

        // Altta yer varsa altına, yoksa üstüne aç
        if (spaceBelow >= mH + 8) {
            top = rect.bottom + 4;
        } else {
            top = rect.top - mH - 4;
        }

        // Sağ kenardan taşmasın
        left = rect.right - mW;
        if (left < 8) left = 8;

        floatingMenu.style.top        = top + 'px';
        floatingMenu.style.left       = left + 'px';
        floatingMenu.style.visibility = '';

        openUyeMenuBtn = btn;
    }

    // Menü dışına tıklayınca kapat
    document.addEventListener('click', function(e) {
        if (openUyeMenuBtn && !floatingMenu.contains(e.target)) {
            closeUyeMenu();
        }
    });

    // Scroll'da menüyü kapat
    window.addEventListener('scroll', closeUyeMenu, true);

    // ══════════════════════════════════════════════════════════════════════════════
    // Boot
    // ══════════════════════════════════════════════════════════════════════════════
    fetchTable();
</script>
</body>
</html>
