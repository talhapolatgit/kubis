<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#7a5c3c" />
    <title>Etiket Oluştur — Kütüphane Yönetim Sistemi</title>
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

        body {
            font-family: var(--font-sans);
            background: var(--background);
            color: var(--foreground);
            -webkit-font-smoothing: antialiased;
            line-height: 1.5;
        }

        input, select, button, textarea { font-family: inherit; font-size: inherit; }

        .app-layout { display: flex; min-height: 100vh; }

        /* ── Sidebar ─────────────────────────────────────────────────────────── */
        .sidebar {
            width: 260px;
            background: var(--sidebar);
            color: var(--sidebar-foreground);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            border-right: 1px solid var(--sidebar-border);
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 40;
            transition: transform .3s ease;
        }
        .sidebar.collapsed { transform: translateX(-260px); }
        .sidebar-header { padding: 16px; display: flex; align-items: center; gap: 12px; }
        .sidebar-logo { width: 36px; height: 36px; border-radius: 8px; background: var(--sidebar-primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .sidebar-logo svg { width: 20px; height: 20px; color: var(--sidebar-primary-foreground); }
        .sidebar-brand-name { font-size: 16px; font-weight: 700; letter-spacing: -.025em; }
        .sidebar-brand-sub { font-size: 12px; opacity: .6; }
        .sidebar-separator { height: 1px; background: var(--sidebar-border); margin: 0 16px; }
        .sidebar-content { flex: 1; overflow-y: auto; padding: 8px 0; }
        .sidebar-group { padding: 8px 12px; }
        .sidebar-group-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--sidebar-foreground); opacity: .5; padding: 4px 8px; margin-bottom: 4px; }
        .sidebar-menu { list-style: none; }
        .sidebar-menu-item { display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 6px; font-size: 14px; font-weight: 500; color: var(--sidebar-foreground); cursor: pointer; transition: background .15s; text-decoration: none; }
        .sidebar-menu-item:hover { background: var(--sidebar-accent); }
        .sidebar-menu-item.active { background: var(--sidebar-accent); color: var(--sidebar-accent-foreground); }
        .sidebar-menu-item svg { width: 18px; height: 18px; flex-shrink: 0; opacity: .8; }
        .sidebar-footer { padding: 16px; border-top: 1px solid var(--sidebar-border); }
        .sidebar-user { display: flex; align-items: center; gap: 12px; }
        .sidebar-avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--sidebar-accent); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600; flex-shrink: 0; }
        .sidebar-user-name { font-size: 14px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar-user-role { font-size: 12px; opacity: .6; }

        /* ── Main ────────────────────────────────────────────────────────────── */
        .main-content { flex: 1; margin-left: 260px; display: flex; flex-direction: column; min-height: 100vh; transition: margin-left .3s ease; }
        .main-content.expanded { margin-left: 0; }

        .top-header { height: 56px; display: flex; align-items: center; gap: 16px; padding: 0 16px; border-bottom: 1px solid rgba(217,208,194,.6); background: var(--card); flex-shrink: 0; }
        .sidebar-trigger { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; border: none; background: transparent; cursor: pointer; color: var(--foreground); transition: background .15s; }
        .sidebar-trigger:hover { background: var(--muted); }
        .sidebar-trigger svg { width: 18px; height: 18px; }
        .header-separator { width: 1px; height: 20px; background: var(--border); }
        .breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 14px; }
        .breadcrumb-link { display: flex; align-items: center; gap: 6px; color: var(--muted-foreground); text-decoration: none; transition: color .15s; }
        .breadcrumb-link:hover { color: var(--foreground); }
        .breadcrumb-link svg { width: 14px; height: 14px; }
        .breadcrumb-sep { color: var(--muted-foreground); opacity: .5; font-size: 12px; }
        .breadcrumb-current { font-weight: 500; color: var(--foreground); }

        .content-area { flex: 1; padding: 24px; display: flex; flex-direction: column; gap: 0; overflow: hidden; }

        /* ── Page title ──────────────────────────────────────────────────────── */
        .page-header { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
        .page-title-icon { width: 40px; height: 40px; border-radius: 10px; background: rgba(122,92,60,.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .page-title-icon svg { width: 22px; height: 22px; color: var(--primary); }
        .page-title { font-family: var(--font-serif); font-size: 22px; font-weight: 700; }
        .page-subtitle { font-size: 13px; color: var(--muted-foreground); margin-top: 2px; }

        /* ── Two-column layout ───────────────────────────────────────────────── */
        .etiket-layout {
            display: grid;
            grid-template-columns: 420px 1fr;
            gap: 20px;
            flex: 1;
            min-height: 0;
        }

        /* ── Left panel ──────────────────────────────────────────────────────── */
        .left-panel {
            display: flex;
            flex-direction: column;
            gap: 16px;
            min-height: 0;
        }

        .panel-card {
            border: 1px solid rgba(217,208,194,.7);
            border-radius: var(--radius);
            background: var(--card);
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
            overflow: hidden;
        }

        .panel-card-header {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .panel-card-header svg { width: 16px; height: 16px; color: var(--primary); flex-shrink: 0; }
        .panel-card-header-title { font-size: 13px; font-weight: 600; color: var(--foreground); }
        .panel-card-header-count { margin-left: auto; font-size: 12px; color: var(--muted-foreground); background: var(--muted); padding: 2px 8px; border-radius: 999px; }

        .panel-card-body { padding: 14px 16px; }

        /* Search input */
        .search-row { display: flex; gap: 8px; }
        .search-wrap { position: relative; flex: 1; }
        .search-wrap svg { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: var(--muted-foreground); pointer-events: none; }
        .search-input { width: 100%; padding: 8px 12px 8px 32px; border: 1px solid var(--border); border-radius: calc(var(--radius) - 2px); background: var(--card); color: var(--foreground); font-size: 14px; outline: none; transition: border-color .15s, box-shadow .15s; }
        .search-input:focus { border-color: var(--ring); box-shadow: 0 0 0 2px rgba(122,92,60,.15); }
        .search-input::placeholder { color: var(--muted-foreground); opacity: .7; }

        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 8px 14px; border-radius: calc(var(--radius) - 2px); font-size: 13px; font-weight: 500; cursor: pointer; transition: background .15s, opacity .15s; border: none; text-decoration: none; white-space: nowrap; }
        .btn svg { width: 15px; height: 15px; flex-shrink: 0; }
        .btn-primary { background: var(--primary); color: var(--primary-foreground); }
        .btn-primary:hover { opacity: .9; }
        .btn-primary:disabled { opacity: .5; cursor: not-allowed; }
        .btn-outline { background: transparent; color: var(--foreground); border: 1px solid var(--border); }
        .btn-outline:hover { background: var(--muted); }
        .btn-ghost { background: rgba(122,92,60,.07); color: var(--primary); border: 1px solid rgba(122,92,60,.2); }
        .btn-ghost:hover { background: rgba(122,92,60,.13); }
        .btn-sm { padding: 6px 10px; font-size: 12px; }
        .btn-danger { background: var(--destructive); color: white; }
        .btn-danger:hover { opacity: .9; }

        /* Results list */
        .results-list {
            max-height: 340px;
            overflow-y: auto;
            border: 1px solid var(--border);
            border-radius: calc(var(--radius) - 2px);
            background: var(--card);
        }

        .result-empty {
            padding: 24px;
            text-align: center;
            color: var(--muted-foreground);
            font-size: 13px;
        }

        .result-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-bottom: 1px solid rgba(217,208,194,.5);
            cursor: pointer;
            transition: background .1s;
            user-select: none;
        }

        .result-item:last-child { border-bottom: none; }
        .result-item:hover { background: rgba(122,92,60,.04); }
        .result-item.selected { background: rgba(122,92,60,.06); }

        .result-check {
            width: 16px;
            height: 16px;
            border: 1.5px solid var(--border);
            border-radius: 4px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--card);
            transition: background .1s, border-color .1s;
        }

        .result-item.selected .result-check {
            background: var(--primary);
            border-color: var(--primary);
        }

        .result-check svg { width: 10px; height: 10px; color: white; opacity: 0; transition: opacity .1s; }
        .result-item.selected .result-check svg { opacity: 1; }

        .result-info { flex: 1; min-width: 0; }
        .result-title { font-size: 13px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .result-meta { font-size: 11px; color: var(--muted-foreground); margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .result-badge {
            font-size: 10px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 999px;
            background: rgba(122,92,60,.1);
            color: var(--primary);
            flex-shrink: 0;
        }

        /* Selected books */
        .selected-list {
            max-height: 220px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .selected-empty {
            padding: 16px;
            text-align: center;
            color: var(--muted-foreground);
            font-size: 13px;
            border: 1px dashed var(--border);
            border-radius: calc(var(--radius) - 2px);
        }

        .selected-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            border: 1px solid var(--border);
            border-radius: calc(var(--radius) - 2px);
            background: var(--card);
        }

        .selected-item-info { flex: 1; min-width: 0; }
        .selected-item-title { font-size: 13px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .selected-item-sub { font-size: 11px; color: var(--muted-foreground); margin-top: 1px; }

        .selected-item-remove {
            width: 22px; height: 22px; border-radius: 50%; border: none;
            background: rgba(197,48,48,.1); color: var(--destructive);
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; transition: background .1s;
        }
        .selected-item-remove:hover { background: rgba(197,48,48,.2); }
        .selected-item-remove svg { width: 12px; height: 12px; }

        /* Label type selector */
        .label-type-grid { display: flex; flex-direction: column; gap: 8px; }

        .label-type-card {
            position: relative;
            cursor: pointer;
        }

        .label-type-card input[type=radio] { position: absolute; opacity: 0; width: 0; height: 0; }

        .label-type-inner {
            padding: 12px 14px;
            border: 1.5px solid var(--border);
            border-radius: calc(var(--radius) - 2px);
            display: flex;
            align-items: center;
            gap: 12px;
            transition: border-color .15s, background .15s;
            user-select: none;
        }

        .label-type-card input:checked ~ .label-type-inner {
            border-color: var(--primary);
            background: rgba(122,92,60,.04);
        }

        .label-type-icon {
            width: 36px; height: 36px; border-radius: 8px;
            background: rgba(122,92,60,.08); display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .label-type-icon svg { width: 18px; height: 18px; color: var(--primary); }

        .label-type-text { flex: 1; }
        .label-type-name { font-size: 13px; font-weight: 600; }
        .label-type-desc { font-size: 11px; color: var(--muted-foreground); margin-top: 2px; line-height: 1.5; }

        .label-type-radio {
            width: 16px; height: 16px; border-radius: 50%; border: 1.5px solid var(--border);
            background: var(--card); flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            transition: background .15s, border-color .15s;
        }
        .label-type-card input:checked ~ .label-type-inner .label-type-radio { background: var(--primary); border-color: var(--primary); }
        .label-type-card input:checked ~ .label-type-inner .label-type-radio::after { content: ''; width: 6px; height: 6px; border-radius: 50%; background: white; }

        /* Generate button area */
        .generate-area { padding-top: 4px; }

        /* ── Right panel ─────────────────────────────────────────────────────── */
        .right-panel {
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        .pdf-panel {
            flex: 1;
            border: 1px solid rgba(217,208,194,.7);
            border-radius: var(--radius);
            background: var(--card);
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .pdf-panel-header {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pdf-panel-header svg { width: 16px; height: 16px; color: var(--primary); flex-shrink: 0; }
        .pdf-panel-header-title { font-size: 13px; font-weight: 600; }
        .pdf-panel-actions { margin-left: auto; display: flex; gap: 8px; }

        .pdf-viewer-wrap {
            flex: 1;
            position: relative;
            min-height: 0;
        }

        .pdf-placeholder {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            color: var(--muted-foreground);
        }

        .pdf-placeholder-icon {
            width: 64px; height: 64px; border-radius: 50%;
            background: rgba(122,92,60,.07);
            display: flex; align-items: center; justify-content: center;
        }
        .pdf-placeholder-icon svg { width: 32px; height: 32px; color: var(--muted-foreground); opacity: .5; }
        .pdf-placeholder-text { font-size: 14px; font-weight: 500; }
        .pdf-placeholder-sub { font-size: 13px; opacity: .7; }

        #pdfFrame {
            width: 100%;
            height: 100%;
            border: none;
            display: none;
        }

        /* Loading overlay */
        .loading-overlay {
            position: absolute;
            inset: 0;
            background: rgba(250,248,243,.85);
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 12px;
            z-index: 10;
        }
        .loading-overlay.active { display: flex; }
        .loading-spinner {
            width: 36px; height: 36px;
            border: 3px solid var(--border);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }
        .loading-text { font-size: 13px; color: var(--muted-foreground); }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* Toast */
        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 3000; display: flex; flex-direction: column; gap: 10px; }
        .toast { padding: 13px 18px; border-radius: var(--radius); font-size: 14px; font-weight: 500; min-width: 260px; box-shadow: 0 4px 16px rgba(0,0,0,.12); border: 1px solid transparent; animation: toast-in .3s ease; }
        .toast.success { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
        .toast.error { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
        .toast.info { background: #eff6ff; border-color: #bfdbfe; color: #1e40af; }
        .toast-desc { font-size: 12px; opacity: .8; margin-top: 2px; }
        @keyframes toast-in { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:translateX(0); } }
        @keyframes toast-out { from { opacity:1; transform:translateX(0); } to { opacity:0; transform:translateX(20px); } }

        /* Sidebar overlay (mobile) */
        .sidebar-overlay { display: none; position: fixed; inset: 0; z-index: 39; background: rgba(0,0,0,.4); }

        @media(max-width:900px) {
            .etiket-layout { grid-template-columns: 1fr; }
            .main-content { margin-left: 0; }
            .sidebar { transform: translateX(-260px); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.visible { display: block; }
        }

        /* Scrollbar */
        .results-list::-webkit-scrollbar, .selected-list::-webkit-scrollbar { width: 6px; }
        .results-list::-webkit-scrollbar-track, .selected-list::-webkit-scrollbar-track { background: transparent; }
        .results-list::-webkit-scrollbar-thumb, .selected-list::-webkit-scrollbar-thumb { background: rgba(122,92,60,.2); border-radius: 3px; }

        /* ── Filtre alanlari ─────────────────────────────────────────────────── */
        .filter-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .filter-field { display: flex; flex-direction: column; gap: 4px; }
        .filter-field.span-2 { grid-column: span 2; }
        .filter-label { font-size: 11px; font-weight: 600; text-transform: uppercase;
            letter-spacing: .04em; color: var(--muted-foreground); }
        .filter-input { width: 100%; padding: 7px 10px; border: 1px solid var(--border);
            border-radius: calc(var(--radius) - 2px); background: var(--card);
            color: var(--foreground); font-size: 13px; outline: none;
            transition: border-color .15s, box-shadow .15s; }
        .filter-input:focus { border-color: var(--ring); box-shadow: 0 0 0 2px rgba(122,92,60,.15); }
        .filter-input::placeholder { color: var(--muted-foreground); opacity: .6; }
        .filter-sep { height: 1px; background: var(--border); margin: 4px 0; }
        .filter-toggle-row { display: flex; align-items: center; gap: 10px; padding: 8px 10px;
            border: 1px solid var(--border); border-radius: calc(var(--radius) - 2px);
            cursor: pointer; user-select: none; transition: background .1s; }
        .filter-toggle-row:hover { background: rgba(122,92,60,.04); }
        .filter-toggle-row input[type=checkbox] { display: none; }
        .toggle-switch { width: 32px; height: 18px; border-radius: 999px;
            background: var(--border); position: relative; flex-shrink: 0;
            transition: background .2s; }
        .filter-toggle-row input:checked ~ .toggle-switch { background: var(--primary); }
        .toggle-switch::after { content: ''; position: absolute; left: 2px; top: 2px;
            width: 14px; height: 14px; border-radius: 50%; background: white;
            transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,.2); }
        .filter-toggle-row input:checked ~ .toggle-switch::after { transform: translateX(14px); }
        .toggle-label { font-size: 13px; font-weight: 500; flex: 1; }
        .filter-row-title { font-size: 11px; font-weight: 600; text-transform: uppercase;
            letter-spacing: .05em; color: var(--muted-foreground); padding: 4px 0 2px; }
    </style>
</head>
<body>
<div class="app-layout">
    @include('partials.sidebar')
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="main-content" id="mainContent">
        {{-- Header --}}
        <div class="top-header">
            <button class="sidebar-trigger" id="sidebarToggle">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
            </button>
            <div class="header-separator"></div>
            <nav class="breadcrumb">
                <a href="{{ route('katalog.index') }}" class="breadcrumb-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    Katalog
                </a>
                <span class="breadcrumb-sep">›</span>
                <span class="breadcrumb-current">Etiket Oluştur</span>
            </nav>
        </div>

        {{-- Content --}}
        <div class="content-area">
            <div class="page-header">
                <div class="page-title-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                </div>
                <div>
                    <div class="page-title">Etiket Oluştur</div>
                    <div class="page-subtitle">Kitapları seçin, etiket tipini belirleyin ve PDF oluşturun.</div>
                </div>
            </div>

            <div class="etiket-layout">

                {{-- ── Sol Panel ──────────────────────────────────────────────── --}}
                <div class="left-panel">

                    {{-- 1. Kitap Arama --}}
                    <div class="panel-card">
                        <div class="panel-card-header">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                            <span class="panel-card-header-title">Kitap Ara</span>
                        </div>
                        <div class="panel-card-body" style="display:flex;flex-direction:column;gap:10px;">

                            {{-- Eser adı / ISBN --}}
                            <div class="search-row">
                                <div class="search-wrap">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                                    <input type="text" id="searchInput" class="search-input"
                                           placeholder="Eser adı veya ISBN ile ara…"
                                           autocomplete="off" />
                                </div>
                                <button class="btn btn-ghost btn-sm" onclick="clearAllFilters()" title="Tüm filtreleri temizle">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                </button>
                            </div>

                            {{-- Ek filtreler --}}
                            <div class="filter-grid">
                                {{-- Demirbaş No --}}
                                <div class="filter-field span-2">
                                    <span class="filter-label">Demirbaş No</span>
                                    <input type="text" id="filterDemirbas" class="filter-input"
                                           placeholder="Demirbaş no ile ara…"
                                           autocomplete="off" />
                                </div>

                                {{-- Özel Notlar --}}
                                <div class="filter-field span-2">
                                    <span class="filter-label">Özel Notlar</span>
                                    <input type="text" id="filterOzelNotlar" class="filter-input"
                                           placeholder="Notlar içinde ara…"
                                           autocomplete="off" />
                                </div>

                                {{-- Kütüphane --}}
                                <div class="filter-field span-2">
                                    <span class="filter-label">Kütüphane</span>
                                    <select id="filterKutuphaneId" class="filter-input">
                                        <option value="">Tüm Yetkili Kütüphaneler</option>
                                        @foreach(($kutuphaneler ?? []) as $kutuphane)
                                            <option value="{{ $kutuphane->id }}">{{ $kutuphane->title }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Kayıt Tarihi Aralığı --}}
                                <div class="filter-field span-2">
                                    <span class="filter-label">Kayıt Tarihi Aralığı</span>
                                </div>
                                <div class="filter-field">
                                    <input type="date" id="filterKayitBaslangic" class="filter-input"
                                           title="Başlangıç tarihi"
                                    />
                                </div>
                                <div class="filter-field">
                                    <input type="date" id="filterKayitBitis" class="filter-input"
                                           title="Bitiş tarihi"
                                    />
                                </div>
                            </div>

                            {{-- Etiket oluşmayanlar toggle --}}
                            <label class="filter-toggle-row">
                                <input type="checkbox" id="filterEtiketOlusmayanlar"
                                />
                                <span class="toggle-switch"></span>
                                <span class="toggle-label">Etiket basılmayanlar</span>
                            </label>

                            {{-- Ara Butonu --}}
                            <button class="btn btn-primary" id="btnAra" onclick="runSearch()" style="width:100%;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                                Ara
                            </button>

                            <div class="filter-sep"></div>

                            {{-- Tümünü Seç (arama sonrası görünür) --}}
                            <div id="selectAllRow" style="display:none;">
                                <button class="btn btn-ghost" id="btnSelectAll" onclick="selectAllResults()" style="width:100%;justify-content:flex-start;gap:8px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><polyline points="9 11 12 14 22 4"/></svg>
                                    Tümünü Seç
                                    <span id="selectAllCount" style="margin-left:auto;font-size:11px;color:var(--muted-foreground);"></span>
                                </button>
                            </div>

                            <div class="results-list" id="resultsList">
                                <div class="result-empty">Arama yapmak için filtre uygulayın.</div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Seçilen Kitaplar --}}
                    <div class="panel-card">
                        <div class="panel-card-header">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                            <span class="panel-card-header-title">Seçilen Kitaplar</span>
                            <span class="panel-card-header-count" id="selectedCount">0 kitap</span>
                        </div>
                        <div class="panel-card-body">
                            <div class="selected-list" id="selectedList">
                                <div class="selected-empty">Henüz kitap seçilmedi.<br>Arama sonuçlarından kitap ekleyin.</div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Etiket Tipi --}}
                    <div class="panel-card">
                        <div class="panel-card-header">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/></svg>
                            <span class="panel-card-header-title">Etiket Tipi</span>
                        </div>
                        <div class="panel-card-body">
                            <div class="label-type-grid">
                                <label class="label-type-card">
                                    <input type="radio" name="etiketTipi" value="tip1" checked />
                                    <div class="label-type-inner">
                                        <div class="label-type-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="5" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="3" y="12" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="5" rx="1"/></svg>
                                        </div>
                                        <div class="label-type-text">
                                            <div class="label-type-name">Tip 1 (Sırt)</div>
                                            <div class="label-type-desc">A4 · 4×9 düzen · 45×30mm · Sınıflama / Kopya / Cilt</div>
                                        </div>
                                        <div class="label-type-radio"></div>
                                    </div>
                                </label>
                                <label class="label-type-card">
                                    <input type="radio" name="etiketTipi" value="tip3" />
                                    <div class="label-type-inner">
                                        <div class="label-type-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="4" height="20" rx="1"/><line x1="8" y1="7" x2="22" y2="7"/><line x1="8" y1="12" x2="22" y2="12"/><line x1="8" y1="17" x2="22" y2="17"/></svg>
                                        </div>
                                        <div class="label-type-text">
                                            <div class="label-type-name">Tip 2 (Sırt Barkod)</div>
                                            <div class="label-type-desc">A4 · 4×9 düzen · 45×30mm · Demirbaş / Sınıf / Kopya / Cilt</div>
                                        </div>
                                        <div class="label-type-radio"></div>
                                    </div>
                                </label>
                                <label class="label-type-card">
                                    <input type="radio" name="etiketTipi" value="tip2" />
                                    <div class="label-type-inner">
                                        <div class="label-type-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="5" rx="1"/><rect x="2" y="13" width="20" height="5" rx="1"/><line x1="6" y1="8" x2="6" y2="8.01"/><line x1="6" y1="17" x2="6" y2="17.01"/></svg>
                                        </div>
                                        <div class="label-type-text">
                                            <div class="label-type-name">Tip 3 (Arka Barkod)</div>
                                            <div class="label-type-desc">A4 · 4×9 düzen · 45×30mm · Kütüphane / Demirbaş / Kitap Adı</div>
                                        </div>
                                        <div class="label-type-radio"></div>
                                    </div>
                                </label>
                                
                                <label class="label-type-card">
                                    <input type="radio" name="etiketTipi" value="tip4" />
                                    <div class="label-type-inner">
                                        <div class="label-type-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="9" height="18" rx="1"/><rect x="13" y="3" width="9" height="18" rx="1"/><line x1="6" y1="8" x2="6" y2="8.01"/><line x1="18" y1="8" x2="18" y2="8.01"/></svg>
                                        </div>
                                        <div class="label-type-text">
                                            <div class="label-type-name">Tip 4 (İkili Barkod)</div>
                                            <div class="label-type-desc">A4 · 4×9 düzen · Tip 2 + Tip 3 içerik</div>
                                        </div>
                                        <div class="label-type-radio"></div>
                                    </div>
                                </label>
                                <label class="label-type-card">
                                    <input type="radio" name="etiketTipi" value="tip7" />
                                    <div class="label-type-inner">
                                        <div class="label-type-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><rect x="2" y="6" width="6" height="12" rx="1"/><line x1="10" y1="10" x2="20" y2="10"/><line x1="10" y1="14" x2="20" y2="14"/></svg>
                                        </div>
                                        <div class="label-type-text">
                                            <div class="label-type-name">Tip 5 (Ribon Sırt Barkod)</div>
                                            <div class="label-type-desc">Ribonlu yazıcı · 60×40mm · Tip 3 içerik</div>
                                        </div>
                                        <div class="label-type-radio"></div>
                                    </div>
                                </label>
                                <label class="label-type-card">
                                    <input type="radio" name="etiketTipi" value="tip5" />
                                    <div class="label-type-inner">
                                        <div class="label-type-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><line x1="6" y1="10" x2="6" y2="10.01"/><line x1="6" y1="14" x2="18" y2="14"/><line x1="6" y1="10" x2="18" y2="10"/></svg>
                                        </div>
                                        <div class="label-type-text">
                                            <div class="label-type-name">Tip 6 (Ribon Arka Barkod)</div>
                                            <div class="label-type-desc">Ribonlu yazıcı · 60×40mm · Tip 2 içerik</div>
                                        </div>
                                        <div class="label-type-radio"></div>
                                    </div>
                                </label>
                                <label class="label-type-card">
                                    <input type="radio" name="etiketTipi" value="tip6" />
                                    <div class="label-type-inner">
                                        <div class="label-type-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="9" height="12" rx="1"/><rect x="13" y="6" width="9" height="12" rx="1"/><line x1="5" y1="10" x2="5" y2="10.01"/><line x1="16" y1="10" x2="16" y2="10.01"/></svg>
                                        </div>
                                        <div class="label-type-text">
                                            <div class="label-type-name">Tip 7 (Ribon İkili Barkod)</div>
                                            <div class="label-type-desc">Ribonlu yazıcı · 60×40mm · Tip 2 + Tip 3 içerik</div>
                                        </div>
                                        <div class="label-type-radio"></div>
                                    </div>
                                </label>
                                
                            </div>
                        </div>
                    </div>

                    {{-- 4. Etiket Kaydır + Oluştur --}}
                    <div class="generate-area">

                        {{-- Kaydır toggle --}}
                        <label class="filter-toggle-row" style="margin-bottom:10px;">
                            <input type="checkbox" id="chkSkip" onchange="toggleSkipInput()" />
                            <span class="toggle-switch"></span>
                            <span class="toggle-label">Etiket kaydır</span>
                        </label>

                        {{-- Kaydır sayı girişi (başlangıçta gizli) --}}
                        <div id="skipInputRow" style="display:none;margin-bottom:10px;">
                            <div class="filter-field">
                                <span class="filter-label">Kaydırılacak etiket sayısı</span>
                                <input type="number" id="skipCount" class="filter-input"
                                       min="1" max="35" value="1"
                                       placeholder="Kaç adet boş etiket?" />
                            </div>
                        </div>

                        <button class="btn btn-primary" id="btnGenerate" onclick="generatePDF()" style="width:100%;padding:11px;font-size:14px;" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                            Etiket PDF Oluştur
                        </button>
                    </div>

                </div>

                {{-- ── Sağ Panel — PDF Görüntüleyici ──────────────────────────── --}}
                <div class="right-panel">
                    <div class="pdf-panel">
                        <div class="pdf-panel-header">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                            <span class="pdf-panel-header-title">PDF Önizleme</span>
                            <div class="pdf-panel-actions">
                                <button class="btn btn-outline btn-sm" id="btnDownload" onclick="downloadPDF()" style="display:none;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                                    İndir
                                </button>
                                <button class="btn btn-ghost btn-sm" id="btnPrint" onclick="printPDF()" style="display:none;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                                    Yazdır
                                </button>
                            </div>
                        </div>
                        <div class="pdf-viewer-wrap">
                            <div class="pdf-placeholder" id="pdfPlaceholder">
                                <div class="pdf-placeholder-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                                </div>
                                <div class="pdf-placeholder-text">PDF henüz oluşturulmadı</div>
                                <div class="pdf-placeholder-sub">Sol panelden kitap seçip "Etiket PDF Oluştur" butonuna tıklayın.</div>
                            </div>
                            <div class="loading-overlay" id="loadingOverlay">
                                <div class="loading-spinner"></div>
                                <div class="loading-text">PDF oluşturuluyor…</div>
                            </div>
                            <iframe id="pdfFrame" title="Etiket PDF Önizleme"></iframe>
                        </div>
                    </div>
                </div>

            </div>{{-- / etiket-layout --}}
        </div>{{-- / content-area --}}
    </main>
</div>

<div class="toast-container" id="toastContainer"></div>

{{-- jsPDF CDN --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
{{-- JsBarcode (Code 39 / Tip 2 barkod) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.6/JsBarcode.all.min.js"></script>

<script>
    // ── Sidebar ──────────────────────────────────────────────────────────────────
    var sidebar     = document.getElementById('sidebar');
    var mainContent = document.getElementById('mainContent');
    var isMobile    = window.innerWidth <= 900;

    document.getElementById('sidebarToggle').addEventListener('click', function() {
        if (isMobile) { sidebar.classList.toggle('open'); document.getElementById('sidebarOverlay').classList.toggle('visible'); }
        else { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); }
    });
    document.getElementById('sidebarOverlay').addEventListener('click', function() {
        sidebar.classList.remove('open'); this.classList.remove('visible');
    });
    window.addEventListener('resize', function() { isMobile = window.innerWidth <= 900; });

    // ── Toast ─────────────────────────────────────────────────────────────────────
    function showToast(type, title, desc) {
        var c = document.getElementById('toastContainer');
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<div>' + title + '</div>' + (desc ? '<div class="toast-desc">' + desc + '</div>' : '');
        c.appendChild(t);
        setTimeout(function() {
            t.style.animation = 'toast-out .3s ease forwards';
            setTimeout(function() { if (t.parentNode) t.parentNode.removeChild(t); }, 300);
        }, 4500);
    }

    // ── State ─────────────────────────────────────────────────────────────────────
    var selectedBooks  = [];   // { id, title, siniflamaYer, kopya, cilt }
    var searchTimeout  = null;
    var lastPdfBlob    = null;
    var lastPdfUrl     = null;

    // ── Tahoma font (Tip 2 için) ──────────────────────────────────────────────────
    // public/fonts/tahoma.ttf dosyasını sayfa açılışında yükler.
    var tahomaB64 = null;   // null → henüz yüklenmedi / başarısız

    (function loadTahoma() {
        fetch('/fonts/tahoma.ttf')
            .then(function(r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.arrayBuffer();
            })
            .then(function(buf) {
                var bytes  = new Uint8Array(buf);
                var binary = '';
                // Büyük dosya için chunked btoa
                var CHUNK  = 8192;
                for (var i = 0; i < bytes.length; i += CHUNK) {
                    binary += String.fromCharCode.apply(null, bytes.subarray(i, i + CHUNK));
                }
                tahomaB64 = btoa(binary);
            })
            .catch(function(e) {
                console.warn('Tahoma yüklenemedi:', e.message,
                    '— public/fonts/tahoma.ttf dosyasının var olduğundan emin olun.');
            });
    })();

    // ── Search & Filters ─────────────────────────────────────────────────────────
    var lastSearchRows = [];   // arama sonucundaki tüm kayıtlar (Tümünü Seç için)

    function clearAllFilters() {
        document.getElementById('searchInput').value             = '';
        document.getElementById('filterDemirbas').value          = '';
        document.getElementById('filterOzelNotlar').value        = '';
        document.getElementById('filterKutuphaneId').value       = '';
        document.getElementById('filterKayitBaslangic').value    = '';
        document.getElementById('filterKayitBitis').value        = '';
        document.getElementById('filterEtiketOlusmayanlar').checked = false;
        lastSearchRows = [];
        renderResults([]);
        document.getElementById('selectAllRow').style.display = 'none';
    }

    function hasAnyFilter() {
        return document.getElementById('searchInput').value.trim().length > 0
            || document.getElementById('filterDemirbas').value.trim().length > 0
            || document.getElementById('filterOzelNotlar').value.trim().length > 0
            || document.getElementById('filterKutuphaneId').value.trim().length > 0
            || document.getElementById('filterKayitBaslangic').value.trim().length > 0
            || document.getElementById('filterKayitBitis').value.trim().length > 0
            || document.getElementById('filterEtiketOlusmayanlar').checked;
    }

    function runSearch() {
        if (!hasAnyFilter()) {
            showToast('info', 'Filtre gerekli', 'Lütfen en az bir arama kriteri girin.');
            return;
        }

        // Ara butonunu yükleniyor moduna al
        var btn = document.getElementById('btnAra');
        btn.disabled = true;
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin .6s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Aranıyor…';

        var params = new URLSearchParams();
        var q = document.getElementById('searchInput').value.trim();
        if (q)  params.set('search', q);
        var dm = document.getElementById('filterDemirbas').value.trim();
        if (dm) params.set('demirbasNo', dm);
        var on = document.getElementById('filterOzelNotlar').value.trim();
        if (on) params.set('ozelNotlar', on);
        var kt = document.getElementById('filterKutuphaneId').value.trim();
        if (kt) params.set('kutuphaneId', kt);
        var kb = document.getElementById('filterKayitBaslangic').value;
        if (kb) params.set('kayitBaslangic', kb);
        var ke = document.getElementById('filterKayitBitis').value;
        if (ke) params.set('kayitBitis', ke);
        var eo = document.getElementById('filterEtiketOlusmayanlar').checked;
        if (eo) params.set('etiketOlusmayanlar', '1');
        params.set('per_page', '200');

        fetch('/etiket/ara?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                lastSearchRows = data.rows || [];
                renderResults(lastSearchRows);
            })
            .catch(function() {
                lastSearchRows = [];
                renderResults([]);
                showToast('error', 'Bağlantı hatası', 'Arama sırasında bir hata oluştu.');
            })
            .finally(function() {
                btn.disabled = false;
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg> Ara';
            });
    }

    function renderResults(rows) {
        var el           = document.getElementById('resultsList');
        var selectAllRow = document.getElementById('selectAllRow');
        var countEl      = document.getElementById('selectAllCount');

        if (!rows.length) {
            el.innerHTML = '<div class="result-empty">Sonuç bulunamadı.</div>';
            selectAllRow.style.display = 'none';
            return;
        }

        // Tümünü Seç satırını göster
        selectAllRow.style.display = 'block';
        countEl.textContent = rows.length + ' kayıt';

        el.innerHTML = rows.map(function(k) {
            var isSelected = selectedBooks.some(function(b) { return b.id === k.id; });
            return '<div class="result-item' + (isSelected ? ' selected' : '') + '" onclick="toggleBook(' + JSON.stringify(k).replace(/"/g, '&quot;') + ')" data-id="' + k.id + '">' +
                '<div class="result-check">' +
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>' +
                '</div>' +
                '<div class="result-info">' +
                '<div class="result-title">' + escHtml(k.kunyeEserAdi || '—') + '</div>' +
                '<div class="result-meta">' + escHtml(k.kunyeYazar || '—') + ' · ' + escHtml(k.kunyeISBNISSN || '—') + '</div>' +
                '</div>' +
                '<div class="result-badge">#' + k.id + '</div>' +
                '</div>';
        }).join('');
    }

    function selectAllResults() {
        if (!lastSearchRows.length) return;

        lastSearchRows.forEach(function(k) {
            var alreadySelected = selectedBooks.some(function(b) { return b.id === k.id; });
            if (!alreadySelected) {
                selectedBooks.push({
                    id:           k.id,
                    title:        k.kunyeEserAdi       || '',
                    siniflamaYer: k.kunyeSiniflamaYer  || '',
                    yayinTarihi:  k.kunyeYayinTarihi   || '',
                    kopya:        k.kunyeKopya         || '',
                    cilt:         k.kunyeCilt          || '',
                    kutuphaneAdi: k.kutuphaneAdi       || '',
                    demirbasKN:   k.kunyeDemirbasKN    || ''
                });
            }
        });

        // Sonuç listesindeki tüm öğeleri seçili göster
        document.querySelectorAll('#resultsList .result-item').forEach(function(el) {
            el.classList.add('selected');
        });

        renderSelectedBooks();
        showToast('success', 'Tümü seçildi', lastSearchRows.length + ' kayıt seçime eklendi.');
    }

    function toggleBook(k) {
        var idx = selectedBooks.findIndex(function(b) { return b.id === k.id; });
        if (idx === -1) {
            selectedBooks.push({
                id:           k.id,
                title:        k.kunyeEserAdi      || '',
                siniflamaYer: k.kunyeSiniflamaYer  || '',
                yayinTarihi:  k.kunyeYayinTarihi   || '',
                kopya:        k.kunyeKopya         || '',
                cilt:         k.kunyeCilt          || '',
                kutuphaneAdi: k.kutuphaneAdi       || '',
                demirbasKN:   k.kunyeDemirbasKN    || ''
            });
        } else {
            selectedBooks.splice(idx, 1);
        }
        // refresh the result list item state without full re-render
        var item = document.querySelector('.result-item[data-id="' + k.id + '"]');
        if (item) {
            item.classList.toggle('selected', idx === -1);
        }
        renderSelectedBooks();
    }

    function renderSelectedBooks() {
        var el    = document.getElementById('selectedList');
        var count = document.getElementById('selectedCount');
        count.textContent = selectedBooks.length + ' kitap';

        if (!selectedBooks.length) {
            el.innerHTML = '<div class="selected-empty">Henüz kitap seçilmedi.<br>Arama sonuçlarından kitap ekleyin.</div>';
            document.getElementById('btnGenerate').disabled = true;
            return;
        }

        el.innerHTML = selectedBooks.map(function(b, i) {
            return '<div class="selected-item">' +
                '<div class="selected-item-info">' +
                '<div class="selected-item-title">' + escHtml(b.title) + '</div>' +
                '<div class="selected-item-sub">' +
                escHtml(b.siniflamaYer || '—') + ' · ' + escHtml(b.yayinTarihi || '—') + ' · ' + escHtml(buildKCLine(b.kopya, b.cilt) || '—') +
                '</div>' +
                '</div>' +
                '<button class="selected-item-remove" onclick="removeBook(' + i + ')" title="Çıkar">' +
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>' +
                '</button>' +
                '</div>';
        }).join('');

        document.getElementById('btnGenerate').disabled = false;
    }

    function removeBook(idx) {
        var bookId = selectedBooks[idx].id;
        selectedBooks.splice(idx, 1);
        // deselect in results list if visible
        var item = document.querySelector('.result-item[data-id="' + bookId + '"]');
        if (item) item.classList.remove('selected');
        renderSelectedBooks();
    }

    // ── PDF Generation (jsPDF) ───────────────────────────────────────────────────
    /*
     * Tip 1 — A4, 4 sütun × 9 satır, 45mm × 30mm etiket
     *
     * Kağıt özellikleri (gerçek ölçüler):
     *   Sol/Sağ kenar  : 4.5 mm
     *   Üst kenar      : 14 mm
     *   Etiket genişlik: 45 mm  →  4×45 = 180 mm
     *   Yatay boşluk   : (210 - 4.5 - 4.5 - 180) / 3 = 7 mm (etiketler arası)
     *   Dikey boşluk   : 0 mm (satırlar bitişik)
     *
     * Her etiket (tümü bold, ortalı, çerçevesiz):
     *   kunyeSiniflamaYer → "/" öncesini \n ile satırlara böl (ör: "Ç\nT813.42")
     *                     → "/" sonrası ayrı satır
     *   kunyeYayinTarihi  → ayrı satır
     *   k.X veya k.X/c.X  → ayrı satır
     *
     * Satırlar arasında ek boşluk yoktur. Metin bloğu etiket içinde dikey ortalanır.
     */
    var TIP1 = {
        cols:       4,
        rows:       9,
        labelW:     45,     // mm
        labelH:     30,     // mm
        marginLeft: 4.5,    // mm  — sol kenar boşluğu
        gapX:       7,      // mm  — etiketler arası yatay boşluk
        marginTop:  14,     // mm  — üst kenar boşluğu
        gapY:       0,      // mm  — satırlar arası boşluk (yok)
        FONT_SIZE:  12,      // pt
        LINE_H_MM:  12 * 0.3528 * 1.00   // ~4.234 mm
    };

    // ── Kaydır toggle ────────────────────────────────────────────────────────────
    function toggleSkipInput() {
        var chk = document.getElementById('chkSkip');
        var row = document.getElementById('skipInputRow');
        row.style.display = chk.checked ? 'block' : 'none';
        if (chk.checked) {
            document.getElementById('skipCount').focus();
        }
    }

    function generatePDF() {
        if (!selectedBooks.length) {
            showToast('error', 'Kitap seçilmedi', 'Lütfen önce kitap ekleyin.');
            return;
        }

        var tipEl = document.querySelector('input[name="etiketTipi"]:checked');
        var tip   = tipEl ? tipEl.value : 'tip1';

        // ── Kaydır (skip) ──────────────────────────────────────────────────────
        var skip = 0;
        if (document.getElementById('chkSkip').checked) {
            skip = parseInt(document.getElementById('skipCount').value, 10) || 0;
            if (skip < 0) skip = 0;
        }

        document.getElementById('loadingOverlay').classList.add('active');

        setTimeout(function() {
            try {
                var pdf;
                var perPage;
                if (tip === 'tip2') {
                    pdf     = buildTip2PDF(selectedBooks, skip);
                    perPage = TIP2.cols * TIP2.rows;
                } else if (tip === 'tip3') {
                    pdf     = buildTip3PDF(selectedBooks, skip);
                    perPage = TIP3.cols * TIP3.rows;
                } else if (tip === 'tip4') {
                    pdf     = buildTip4PDF(selectedBooks, skip);
                    perPage = TIP2.cols * TIP2.rows;
                } else if (tip === 'tip5') {
                    pdf     = buildTip5PDF(selectedBooks, skip);
                    perPage = 1;   // Ribonlu yazıcı: sayfa başına 1 etiket
                } else if (tip === 'tip6') {
                    pdf     = buildTip6PDF(selectedBooks, skip);
                    perPage = 1;   // Ribonlu yazıcı: kitap başına 2 sayfa (Tip2+Tip3)
                } else if (tip === 'tip7') {
                    pdf     = buildTip7PDF(selectedBooks, skip);
                    perPage = 1;   // Ribonlu yazıcı: sayfa başına 1 etiket
                } else {
                    pdf     = buildTip1PDF(selectedBooks, skip);
                    perPage = TIP1.cols * TIP1.rows;
                }

                lastPdfBlob = pdf.output('blob');
                if (lastPdfUrl) URL.revokeObjectURL(lastPdfUrl);
                lastPdfUrl  = URL.createObjectURL(lastPdfBlob);

                var frame = document.getElementById('pdfFrame');
                frame.src = lastPdfUrl + '#toolbar=1&navpanes=0';
                frame.style.display = 'block';
                document.getElementById('pdfPlaceholder').style.display = 'none';
                document.getElementById('btnDownload').style.display = '';
                document.getElementById('btnPrint').style.display = '';

                var skipNote = skip > 0 ? ' · ' + skip + ' boş etiket' : '';
                var pageCalcCount = (tip === 'tip4') ? selectedBooks.length * 2
                                  : (tip === 'tip6') ? selectedBooks.length * 2
                                  : selectedBooks.length;
                var perPageForToast = (tip === 'tip5' || tip === 'tip6') ? 1 : perPage;
                showToast('success', 'PDF oluşturuldu',
                    selectedBooks.length + ' kitap · ' +
                    (pageCalcCount + skip) + ' sayfa' + skipNote);
            } catch(e) {
                showToast('error', 'PDF hatası', e.message);
                console.error(e);
            } finally {
                document.getElementById('loadingOverlay').classList.remove('active');
            }
        }, 50);

window.scrollTo({
  top: 0,
  behavior: 'smooth'
});

    }

    function buildTip1PDF(books, skip) {
        skip = skip || 0;
        var cfg      = TIP1;
        var jsPDF    = window.jspdf.jsPDF;
        var doc      = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        var perPage  = cfg.cols * cfg.rows;
        var total    = skip + books.length;
        var pageCount = Math.ceil(total / perPage);

        for (var page = 0; page < pageCount; page++) {
            if (page > 0) doc.addPage();
            for (var i = 0; i < perPage; i++) {
                var slot = page * perPage + i;
                if (slot >= total) break;
                var col  = i % cfg.cols;
                var row  = Math.floor(i / cfg.cols);
                var lx   = cfg.marginLeft + col * (cfg.labelW + cfg.gapX);
                var ly   = cfg.marginTop  + row * (cfg.labelH + cfg.gapY);
                // TEST: tüm hücreler için gri arka plan (boş slotlar dahil)
                //doc.setFillColor(220, 220, 220);
                //doc.rect(lx, ly, cfg.labelW, cfg.labelH, 'F');
                if (slot < skip) continue;          // boş etiket — atla
                drawLabel(
                    doc,
                    books[slot - skip],
                    lx,
                    ly,
                    cfg.labelW,
                    cfg.labelH
                );
            }
        }

        return doc;
    }

    // ─────────────────────────────────────────────────────────────────────────────
    /*
     * Tip 4 — Kitap başına Tip 2 + Tip 3 etiketi yan yana
     *
     * Sayfa düzeni Tip 2/3 ile aynı (A4, 4×9, 45×30mm).
     * Her kitap için ardışık iki slot kullanılır:
     *   Çift slot (0,2,4…) → Tip 2 (Kütüphane / Demirbaş barkod / Kitap adı)
     *   Tek slot  (1,3,5…) → Tip 3 (Demirbaş dikey barkod / Sınıflama)
     *
     * 4 sütunlu grid'de iki ardışık slot aynı satırda yan yana düşer.
     * Örnek: 8 kitap → 16 slot → sütun dizilimi: [T2 T3 T2 T3 | T2 T3 T2 T3 | …]
     */
    function buildTip4PDF(books, skip) {
        if (!tahomaB64) {
            throw new Error(
                'Tahoma fontu henüz yüklenmedi.\n' +
                'public/fonts/tahoma.ttf dosyasının var olduğundan emin olun.'
            );
        }

        skip = skip || 0;
        var cfg       = TIP2;   // Aynı sayfa ızgarası
        var jsPDF     = window.jspdf.jsPDF;
        var doc       = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });

        // Tahoma embed (Tip 2 çizimi için gerekli)
        doc.addFileToVFS('tahoma.ttf', tahomaB64);
        doc.addFont('tahoma.ttf', 'tahoma', 'normal');

        var perPage    = cfg.cols * cfg.rows;          // 36
        // Her kitap 2 slot kullanır (1 × Tip2 + 1 × Tip3)
        var totalSlots = skip + books.length * 2;
        var pageCount  = Math.ceil(totalSlots / perPage);

        for (var page = 0; page < pageCount; page++) {
            if (page > 0) doc.addPage();
            for (var i = 0; i < perPage; i++) {
                var slot = page * perPage + i;
                if (slot >= totalSlots) break;
                if (slot < skip) continue;             // boş etiket — atla

                var bookSlot  = slot - skip;           // 0-tabanlı, kitap çiftleri
                var bookIdx   = Math.floor(bookSlot / 2);
                var labelType = bookSlot % 2;          // 0 = Tip 2, 1 = Tip 3

                var col = i % cfg.cols;
                var row = Math.floor(i / cfg.cols);
                var lx  = cfg.marginLeft + col * (cfg.labelW + cfg.gapX);
                var ly  = cfg.marginTop  + row * (cfg.labelH + cfg.gapY);

                var book = books[bookIdx];
                if (labelType === 0) {
                    drawLabel2(doc, book, lx, ly, cfg.labelW, cfg.labelH);
                } else {
                    drawLabel3(doc, book, lx, ly, cfg.labelW, cfg.labelH);
                }
            }
        }

        return doc;
    }

    // ─────────────────────────────────────────────────────────────────────────────
    /*
     * Tip 5 — Ribonlu / termal etiket yazıcısı · 60×40mm · Yatay (Landscape)
     *
     * PDF boyutu: 60mm × 40mm, her sayfa = 1 etiket.
     * İçerik: Tip 2 ile aynı düzen, 60×40mm'e orantılı ölçeklenmiş.
     *   - Kütüphane adı    (Tahoma, üstte)
     *   - Code 39 barkod   (demirbasKN)
     *   - Demirbaş no text (barkod altı)
     *   - Kitap adı        (altta)
     *
     * skip: baştaki boş sayfaları atlar.
     */
    var TIP5 = {
        labelW:     50,          // mm — etiket genişliği
        labelH:     30,          // mm — etiket yüksekliği
        FONT_SIZE:  9,           // pt  (~7pt × 60/45 ≈ 9.3pt → 9pt)
        LINE_H_MM:  9 * 0.3528 * 1.2,   // ≈ 3.81 mm
        PADDING_X:  2,           // mm — yatay iç boşluk (her kenar)
        PAD_TOP:    2.5,         // mm — üst iç boşluk
        GAP:        0.7,         // mm — bloklar arası boşluk
        BAR_H:      12           // mm — barkod yüksekliği
    };

    function buildTip5PDF(books, skip) {
        if (!tahomaB64) {
            throw new Error(
                'Tahoma fontu henüz yüklenmedi.\n' +
                'public/fonts/tahoma.ttf dosyasının var olduğundan emin olun.'
            );
        }

        skip = skip || 0;
        var cfg   = TIP5;
        var jsPDF = window.jspdf.jsPDF;

        // İlk sayfa: 60×40mm yatay
        var doc = new jsPDF({
            orientation: 'landscape',
            unit:        'mm',
            format:      [cfg.labelH, cfg.labelW]   // jsPDF: portrait=[kısa,uzun] → landscape=[kısa,uzun] aynı sıra
        });

        // Tahoma embed
        doc.addFileToVFS('tahoma.ttf', tahomaB64);
        doc.addFont('tahoma.ttf', 'tahoma', 'normal');

        var total = skip + books.length;

        for (var idx = 0; idx < total; idx++) {
            if (idx > 0) {
                doc.addPage([cfg.labelH, cfg.labelW], 'landscape');
            }
            if (idx < skip) continue;   // boş sayfa — atla
            drawLabel5(doc, books[idx - skip], 0, 0, cfg.labelW, cfg.labelH);
        }

        return doc;
    }

    /**
     * Tip 5 etiketini çizer — Tahoma (normal), 60×40mm.
     * Tip 2 ile aynı içerik ve düzen, orantılı ölçeklenmiş.
     */
    function drawLabel5(doc, book, x, y, w, h) {
        var cfg    = TIP5;
        var FS     = cfg.FONT_SIZE;
        var LH     = cfg.LINE_H_MM;
        var CHAR_H = FS * 0.3528;
        var availW = w - 2 * cfg.PADDING_X;
        var MAX_WRAP = 2;
        var cx     = x + w / 2;

        doc.setFont('tahoma', 'normal');
        doc.setFontSize(FS);
        doc.setTextColor(0, 0, 0);

        // ── Kütüphane adı — üstte ────────────────────────────────────────────
        var kutAll   = doc.splitTextToSize(String(book.kutuphaneAdi || ''), availW);
        var kutLines = kutAll.slice(0, MAX_WRAP);
        if (kutAll.length > MAX_WRAP) {
            kutLines[MAX_WRAP - 1] = truncateText(doc, kutLines[MAX_WRAP - 1] + ' \u2026', availW, FS);
        }

        var curY = y + cfg.PAD_TOP + CHAR_H / 2;
        for (var i = 0; i < kutLines.length; i++) {
            doc.text(kutLines[i], cx, curY + i * LH, { align: 'center', baseline: 'middle' });
        }
        curY += (kutLines.length - 1) * LH + CHAR_H / 2;

        // ── Barkod (Code 39) — demirbasKN ────────────────────────────────────
        curY += cfg.GAP;
        var rawDem     = String(book.demirbasKN || '');
        var barcodeVal = rawDem.toUpperCase().replace(/[^0-9A-Z\-\.\s\$\/\+\%]/g, '');
        if (barcodeVal) {
            drawBarcode39(doc, barcodeVal, x + cfg.PADDING_X, curY, availW, cfg.BAR_H);
        }
        curY += cfg.BAR_H;

        // ── Demirbaş no — barkodun altında metin ─────────────────────────────
        curY += cfg.GAP;
        var demText = truncateText(doc, rawDem, availW, FS);
        curY += CHAR_H / 2;
        doc.text(demText, cx, curY, { align: 'center', baseline: 'middle' });
        curY += CHAR_H / 2;

        // ── Kitap adı ─────────────────────────────────────────────────────────
        curY += cfg.GAP;
        var eserAll   = doc.splitTextToSize(String(book.title || ''), availW);
        var eserLines = eserAll.slice(0, MAX_WRAP);
        if (eserAll.length > MAX_WRAP) {
            eserLines[MAX_WRAP - 1] = truncateText(doc, eserLines[MAX_WRAP - 1] + ' \u2026', availW, FS);
        }
        curY += CHAR_H / 2;
        for (var j = 0; j < eserLines.length; j++) {
            doc.text(eserLines[j], cx, curY + j * LH, { align: 'center', baseline: 'middle' });
        }
    }

    // ─────────────────────────────────────────────────────────────────────────────
    /*
     * Tip 6 — Ribonlu yazıcı · 60×40mm yatay · Kitap başına Tip 2 + Tip 3
     *
     * Her kitap için arka arkaya iki sayfa üretilir:
     *   Sayfa 1 (çift index) → Tip 2 içeriği  — drawLabel5 ile çizilir
     *   Sayfa 2 (tek  index) → Tip 3 içeriği  — drawLabel6_3 ile çizilir
     *
     * TIP3'ün 45×30mm ölçüleri 60×40mm'e oransal olarak ölçeklenmiştir (×4/3).
     */
    var TIP6_3 = {
        labelW:     50,
        labelH:     30,
        // ── Sol dikey demirbaş sütunu (TIP3 × 4/3) ────────────────────────────
        DEM_W:      16,         // mm  (12 × 4/3)
        DEM_PAD:    1.0,        // mm  (0.8 × 4/3 ≈ 1.07 → 1.0)
        BAR_W:      9,          // mm  (7 × 4/3 ≈ 9.3 → 9)
        BAR_GAP:    0.7,        // mm  (0.5 × 4/3 ≈ 0.67 → 0.7)
        DEM_FS:     8,          // pt  (6 × 4/3 = 8)
        // ── Sağ veri sütunu ───────────────────────────────────────────────────
        FONT_SIZE:  14,         // pt  (12 × 4/3 = 16 → 14 okunabilirlik için)
        COL_GAP:    5,          // mm  (4 × 4/3 ≈ 5.3 → 5)
        LINE_H_MM:  14 * 0.3528 * 1.08,   // ≈ 5.33 mm
        PAD_RIGHT:  1.5         // mm  (1 × 4/3 ≈ 1.3 → 1.5)
    };

    function buildTip6PDF(books, skip) {
        if (!tahomaB64) {
            throw new Error(
                'Tahoma fontu henüz yüklenmedi.\n' +
                'public/fonts/tahoma.ttf dosyasının var olduğundan emin olun.'
            );
        }

        skip = skip || 0;
        var jsPDF      = window.jspdf.jsPDF;
        var labelW     = TIP5.labelW;     // 60mm
        var labelH     = TIP5.labelH;     // 40mm

        // İlk sayfa
        var doc = new jsPDF({
            orientation: 'landscape',
            unit:        'mm',
            format:      [labelH, labelW]
        });

        // Tahoma embed (Tip 2 çizimi için)
        doc.addFileToVFS('tahoma.ttf', tahomaB64);
        doc.addFont('tahoma.ttf', 'tahoma', 'normal');

        // Her kitap için 2 sayfa: [Tip2, Tip3]
        var totalPages = skip + books.length * 2;

        for (var idx = 0; idx < totalPages; idx++) {
            if (idx > 0) {
                doc.addPage([labelH, labelW], 'landscape');
            }
            if (idx < skip) continue;   // boş sayfa — atla

            var bookSlot  = idx - skip;
            var bookIdx   = Math.floor(bookSlot / 2);
            var labelType = bookSlot % 2;   // 0 = Tip 2, 1 = Tip 3

            var book = books[bookIdx];
            if (labelType === 0) {
                drawLabel5(doc, book, 0, 0, labelW, labelH);
            } else {
                drawLabel6_3(doc, book, 0, 0, labelW, labelH);
            }
        }

        return doc;
    }

    /**
     * Tip 6 — Tip 3 içeriğini 60×40mm'e ölçeklenmiş olarak çizer.
     * TIP3 → TIP6_3 config ile drawLabel3 mantığının ölçeklenmiş hâli.
     *
     * Sol: demirbasKN dikey barkod + metin
     * Sağ: siniflamaYer / yayinTarihi / k.X satırları, sola hizalı, dikey ortalı
     */
    function drawLabel6_3(doc, book, x, y, w, h) {
        var cfg    = TIP6_3;
        var FS     = cfg.FONT_SIZE;
        var LH     = cfg.LINE_H_MM;
        var CHAR_H = FS * 0.3528;

        // ── Sol sütun — dikey barkod + demirbaş no ───────────────────────────
        var rawDem3     = String(book.demirbasKN || '');
        var barcodeVal3 = rawDem3.toUpperCase().replace(/[^0-9A-Z\-\.\s\$\/\+\%]/g, '');

        var barX = x + cfg.DEM_PAD;
        var barY = y + cfg.DEM_PAD - 2;
        var barW = cfg.BAR_W;
        var barH = h - 2 * cfg.DEM_PAD + 1;
        if (barcodeVal3) {
            drawBarcode39Vertical(doc, barcodeVal3, barX, barY, barW, barH);
        }

        var demText  = normTR(rawDem3);
        var textColX = barX + barW + cfg.BAR_GAP;
        if (demText) {
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(cfg.DEM_FS);
            doc.setTextColor(0, 0, 0);
            var textCX      = textColX + (cfg.DEM_W - cfg.DEM_PAD - barW - cfg.BAR_GAP) / 2;
            var demTextW    = doc.getTextWidth(demText);
            var textAnchorY = y + h / 2 + demTextW / 2;
            doc.text(demText, textCX, textAnchorY, {
                angle:    90,
                align:    'left',
                baseline: 'middle'
            });
        }

        // ── Sağ veri sütunu ──────────────────────────────────────────────────
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(FS);
        doc.setTextColor(0, 0, 0);

        var divX   = x + cfg.DEM_W;
        var rightX = divX + cfg.COL_GAP;
        var rightW = w - cfg.DEM_W - cfg.COL_GAP - cfg.PAD_RIGHT;

        var lines  = buildLines3(book);
        var n      = lines.length;
        var blockH = (n - 1) * LH + CHAR_H;
        var firstY = y + (h - blockH) / 2 + CHAR_H / 2;

        for (var i = 0; i < n; i++) {
            var txt = truncateText(doc, lines[i], rightW, FS);
            doc.text(txt, rightX, firstY + i * LH, {
                align:    'left',
                baseline: 'middle'
            });
        }
    }

    // ─────────────────────────────────────────────────────────────────────────────
    /*
     * Tip 7 — Ribonlu yazıcı · 60×40mm yatay · Sayfa başına 1 etiket · Tip 3 içerik
     *
     * Tip 5 ile aynı sayfa yapısı (her kitap = 1 sayfa, 60×40mm landscape).
     * İçerik olarak drawLabel6_3 kullanılır (Tip 3'ün 60×40mm'e ölçeklenmiş hâli).
     */
    function buildTip7PDF(books, skip) {
        skip = skip || 0;
        var jsPDF  = window.jspdf.jsPDF;
        var labelW = TIP5.labelW;   // 60mm
        var labelH = TIP5.labelH;   // 40mm

        var doc = new jsPDF({
            orientation: 'landscape',
            unit:        'mm',
            format:      [labelH, labelW]
        });

        var total = skip + books.length;

        for (var idx = 0; idx < total; idx++) {
            if (idx > 0) {
                doc.addPage([labelH, labelW], 'landscape');
            }
            if (idx < skip) continue;   // boş sayfa — atla
            drawLabel6_3(doc, books[idx - skip], 0, 0, labelW, labelH);
        }

        return doc;
    }

    // ─────────────────────────────────────────────────────────────────────────────
    /*
     * Tip 3 — A4, 4 sütun × 9 satır, 45mm × 30mm etiket
     *
     * Sayfa düzeni Tip 1 ile aynı (margin/gap).
     *
     * Her etiket:
     *   Sol sütun (~8 mm) : kunyeDemirbasKN — dikey, ortalı, soldan hizalı
     *   İnce ayırıcı çizgi
     *   Sağ sütun         : 4 satır, sola hizalı, bold, Helvetica
     *     1. kunyeSiniflamaYer "/" öncesi (newline varsa her satır ayrı)
     *     2. kunyeSiniflamaYer "/" sonrası
     *     3. kunyeYayinTarihi
     *     4. k.X veya k.X/c.X
     *
     * Metin bloğu sağ sütunda dikey ortalanır. Çerçeve yoktur.
     */
    var TIP3 = {
        cols:       4,
        rows:       9,
        labelW:     45,
        labelH:     30,
        marginLeft: 4.5,    // mm  — sol kenar boşluğu
        gapX:       7,      // mm  — etiketler arası yatay boşluk
        marginTop:  14,     // mm  — üst kenar boşluğu
        gapY:       0,      // mm  — satırlar arası boşluk (yok)
        // ── Sol dikey demirbaş sütunu ──────────────────────────
        DEM_W:      12,      // mm  — toplam sol sütun genişliği
        DEM_PAD:    0.8,    // mm  — sol/üst/alt kenar iç boşluk
        BAR_W:      7,    // mm  — barkodun genişliği (kağıttaki "kalınlık")
        BAR_GAP:    0.5,    // mm  — barkod ile metin arasındaki boşluk
        DEM_FS:     6,      // pt  — demirbaş font boyutu
        // ── Sağ veri sütunu ────────────────────────────────────
        FONT_SIZE:  12,      // pt
        COL_GAP: 4,
        LINE_H_MM:  12 * 0.3528 * 1.08,   // ≈ 3.05 mm
        PAD_RIGHT:  1       // mm  — sağ kenar iç boşluk
    };

    function buildTip3PDF(books, skip) {
        skip = skip || 0;
        var cfg      = TIP3;
        var jsPDF    = window.jspdf.jsPDF;
        var doc      = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        var perPage  = cfg.cols * cfg.rows;
        var total    = skip + books.length;
        var pageCount = Math.ceil(total / perPage);

        for (var page = 0; page < pageCount; page++) {
            if (page > 0) doc.addPage();
            for (var i = 0; i < perPage; i++) {
                var slot = page * perPage + i;
                if (slot >= total) break;
                var col  = i % cfg.cols;
                var row  = Math.floor(i / cfg.cols);
                var lx   = cfg.marginLeft + col * (cfg.labelW + cfg.gapX);
                var ly   = cfg.marginTop  + row * (cfg.labelH + cfg.gapY);
                // TEST: tüm hücreler için gri arka plan (boş slotlar dahil)
                //doc.setFillColor(220, 220, 220);
                //doc.rect(lx, ly, cfg.labelW, cfg.labelH, 'F');
                if (slot < skip) continue;
                drawLabel3(
                    doc,
                    books[slot - skip],
                    lx,
                    ly,
                    cfg.labelW,
                    cfg.labelH
                );
            }
        }

        return doc;
    }

    /**
     * Tip 3 etiketini çizer.
     *
     * Sol: kunyeDemirbasKN dikey (90° CCW = baş sağa eğilerek okunur).
     * İnce dikey çizgi ayırıcı.
     * Sağ: Tip 1'deki buildLines çıktısı, sola hizalı, dikey ortalı.
     */
    function drawLabel3(doc, book, x, y, w, h) {
        var cfg    = TIP3;
        var FS     = cfg.FONT_SIZE;
        var LH     = cfg.LINE_H_MM;
        var CHAR_H = FS * 0.3528;

        // ── TEST: Gri arka plan (hizalama kontrolü için) ─────────────────────
        //doc.setFillColor(220, 220, 220);
        //doc.rect(x, y, w, h, 'F');

        // ── Sol sütun düzeni ─────────────────────────────────────────────────
        // Sütun içi: [DEM_PAD] [barkod: BAR_W] [BAR_GAP] [metin] [DEM_PAD]
        var rawDem3     = String(book.demirbasKN || '');
        var barcodeVal3 = rawDem3.toUpperCase().replace(/[^0-9A-Z\-\.\s\$\/\+\%]/g, '');

        // Barkod: sol ve üst/alt iç boşlukla
        var barX  = x + cfg.DEM_PAD;
        var barY  = y + cfg.DEM_PAD - 2;
        var barW  = cfg.BAR_W;
        var barH  = h - 2 * cfg.DEM_PAD + 1;
        if (barcodeVal3) {
            drawBarcode39Vertical(doc, barcodeVal3, barX, barY, barW, barH);
        }

        // Demirbaş no metni: barkodun sağında, dikey (90° CCW)
        var demText  = normTR(rawDem3);
        var textColX = barX + barW + cfg.BAR_GAP;   // metin sütununun sol kenarı
        if (demText) {
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(cfg.DEM_FS);
            doc.setTextColor(0, 0, 0);
            // Metnin orta noktası: sütunun yatay ortası
            var textCX = textColX + (cfg.DEM_W - cfg.DEM_PAD - barW - cfg.BAR_GAP) / 2;
            var demTextW = doc.getTextWidth(demText);
            // Dikey ortalama: anchor noktası metnin yarı uzunluğu kadar aşağıda
            var textAnchorY = y + h / 2 + demTextW / 2;
            doc.text(demText, textCX, textAnchorY, {
                angle:    90,
                align:    'left',
                baseline: 'middle'
            });
        }

        // ── Sağ veri sütunu ──────────────────────────────────────────────────
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(FS);
        doc.setTextColor(0, 0, 0);

        var divX    = x + cfg.DEM_W;
        var rightX = divX + cfg.COL_GAP;
        var rightW = w - cfg.DEM_W - cfg.COL_GAP - cfg.PAD_RIGHT;

        // buildLines ile aynı mantık (normTR uygulanmış)
        var lines   = buildLines3(book);

        var n      = lines.length;
        var blockH = (n - 1) * LH + CHAR_H;
        var firstY = y + (h - blockH) / 2 + CHAR_H / 2;

        for (var i = 0; i < n; i++) {
            var txt = truncateText(doc, lines[i], rightW, FS);
            doc.text(txt, rightX, firstY + i * LH, {
                align:    'left',
                baseline: 'middle'
            });
        }
    }

    /**
     * Tip 3 için veri satırlarını üretir (normTR uygulanmış).
     *
     * kunyeSiniflamaYer "Ç\nT813.42/DÖLy" gibi gelebilir:
     *   "/" öncesi → her \n satırı ayrı çizgi
     *   "/" sonrası → bir çizgi
     *   yayinTarihi → bir çizgi
     *   k.X veya k.X/c.X → bir çizgi
     */
    function buildLines3(book) {
        var raw      = normTR(String(book.siniflamaYer || '').trim());
        var lines    = [];

        var slashIdx = raw.indexOf('/');
        var before   = slashIdx !== -1 ? raw.substring(0, slashIdx).trim() : raw;
        var after    = slashIdx !== -1 ? raw.substring(slashIdx + 1).trim() : '';

        before
            .replace(/\r\n/g, '\n').replace(/\r/g, '\n')
            .split('\n')
            .forEach(function(s) { s = s.trim(); if (s) lines.push(s); });

        if (after) lines.push(after);

        var yt = normTR(String(book.yayinTarihi || '').trim());
        if (yt) lines.push(yt);

        var kc = buildKCLine(
            String(book.kopya || '').trim(),
            String(book.cilt  || '').trim()
        );
        if (kc) lines.push(kc);

        return lines;
    }

    /*
     * Tip 2 — A4, 4 sütun × 9 satır, 45mm × 30mm etiket
     *
     * Sayfa düzeni Tip 1 ile aynı (margin/gap değerleri).
     *
     * Her etiket (tümü ortalı, Tahoma, çerçevesiz):
     *   1. satır(lar) : kütüphane adı   — üstte, çok az boşlukla (max 2 satır)
     *   barkod        : demirbasKN — Code 39 barkod
     *   2. satır      : demirbasKN — barkodun altında metin
     *   3. satır(lar) : kitap adı       — (max 2 satır)
     */
    var TIP2 = {
        cols:       4,
        rows:       9,
        labelW:     45,
        labelH:     30,
        marginLeft: 4.5,    // mm  — sol kenar boşluğu
        gapX:       7,      // mm  — etiketler arası yatay boşluk
        marginTop:  14,     // mm  — üst kenar boşluğu
        gapY:       0,      // mm  — satırlar arası boşluk (yok)
        FONT_SIZE:  7,           // pt
        LINE_H_MM:  7 * 0.3528 * 1.2,   // ≈ 2.96 mm
        PADDING_X:  1.5,         // mm — yatay iç boşluk (her kenar)
        PAD_TOP:    2,           // mm — üst iç boşluk (çok az)
        GAP:        0.5,         // mm — bloklar arası boşluk
        BAR_H:      9            // mm — barkod yüksekliği
    };

    function buildTip2PDF(books, skip) {
        if (!tahomaB64) {
            throw new Error(
                'Tahoma fontu henüz yüklenmedi.\n' +
                'public/fonts/tahoma.ttf dosyasının var olduğundan emin olup sayfayı yenileyin.'
            );
        }

        skip = skip || 0;
        var cfg      = TIP2;
        var jsPDF    = window.jspdf.jsPDF;
        var doc      = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });

        // Tahoma embed
        doc.addFileToVFS('tahoma.ttf', tahomaB64);
        doc.addFont('tahoma.ttf', 'tahoma', 'normal');

        var perPage  = cfg.cols * cfg.rows;
        var total    = skip + books.length;
        var pageCount = Math.ceil(total / perPage);

        for (var page = 0; page < pageCount; page++) {
            if (page > 0) doc.addPage();
            for (var i = 0; i < perPage; i++) {
                var slot = page * perPage + i;
                if (slot >= total) break;
                var col  = i % cfg.cols;
                var row  = Math.floor(i / cfg.cols);
                var lx   = cfg.marginLeft + col * (cfg.labelW + cfg.gapX);
                var ly   = cfg.marginTop  + row * (cfg.labelH + cfg.gapY);
                // TEST: tüm hücreler için gri arka plan (boş slotlar dahil)
                //doc.setFillColor(220, 220, 220);
                //doc.rect(lx, ly, cfg.labelW, cfg.labelH, 'F');
                if (slot < skip) continue;
                drawLabel2(
                    doc,
                    books[slot - skip],
                    lx,
                    ly,
                    cfg.labelW,
                    cfg.labelH
                );
            }
        }

        return doc;
    }

    /**
     * jsPDF'in standart Helvetica fontu cp1252 (Windows-1252) encoding kullanır.
     * Türkçe'ye özgü ş, Ş, ğ, Ğ, ı, İ bu encoding'de yer almaz; etikette
     * boş/bozuk görünür. Bunları ASCII karşılıklarıyla değiştiriyoruz.
     * ü, Ü, ö, Ö, ç, Ç Latin-1'de var, doğrudan geçer.
     * (Sadece Tip 1 — Helvetica için kullanılır.)
     */
    function normTR(str) {
        return String(str || '')
            .replace(/[\u015F]/g, 's')   // ş
            .replace(/[\u015E]/g, 'S')   // Ş
            .replace(/[\u011F]/g, 'g')   // ğ
            .replace(/[\u011E]/g, 'G')   // Ğ
            .replace(/[\u0131]/g, 'i')   // ı
            .replace(/[\u0130]/g, 'I');  // İ
    }

    /**
     * Tip 2 etiketini çizer — Tahoma (normal), Türkçe karakterler doğal desteklenir.
     *
     * İçerik:
     *   - kutuphaneAdi  : word-wrap, max 2 satır
     *   - [boş satır]
     *   - demirbasKN    : tek satır (gerekirse kırp)
     *   - [boş satır]
     *   - kitap adı     : word-wrap, max 2 satır
     *
     * Normal ağırlık, yatay ortalı. Blok dikey ortalanır. Çerçeve yok.
     */
    function drawLabel2(doc, book, x, y, w, h) {
        var cfg      = TIP2;
        var FS       = cfg.FONT_SIZE;       // 7 pt
        var LH       = cfg.LINE_H_MM;       // ~2.96 mm
        var CHAR_H   = FS * 0.3528;         // ~2.47 mm
        var availW   = w - 2 * cfg.PADDING_X;
        var MAX_WRAP = 2;
        var cx       = x + w / 2;

        // ── TEST: Gri arka plan (hizalama kontrolü için) ─────────────────────
        //doc.setFillColor(220, 220, 220);
        //doc.rect(x, y, w, h, 'F');

        doc.setFont('tahoma', 'normal');
        doc.setFontSize(FS);
        doc.setTextColor(0, 0, 0);

        // ── Kütüphane adı — üstte, çok az boşlukla ──────────────────────────
        var kutAll   = doc.splitTextToSize(String(book.kutuphaneAdi || ''), availW);
        var kutLines = kutAll.slice(0, MAX_WRAP);
        if (kutAll.length > MAX_WRAP) {
            kutLines[MAX_WRAP - 1] = truncateText(doc, kutLines[MAX_WRAP - 1] + ' \u2026', availW, FS);
        }

        // İlk satır merkezini üst kenara dayandır (PAD_TOP + yarı karakter yüksekliği)
        var curY = y + cfg.PAD_TOP + CHAR_H / 2;
        for (var i = 0; i < kutLines.length; i++) {
            doc.text(kutLines[i], cx, curY + i * LH, { align: 'center', baseline: 'middle' });
        }
        // Son satırın altına geç
        curY += (kutLines.length - 1) * LH + CHAR_H / 2;

        // ── Barkod (Code 39) — demirbasKN ────────────────────────────────────
        curY += cfg.GAP;
        var rawDem = String(book.demirbasKN || '');
        // Code 39: yalnızca izin verilen karakterler (büyük harf + rakam + özel)
        var barcodeVal = rawDem.toUpperCase().replace(/[^0-9A-Z\-\.\s\$\/\+\%]/g, '');
        if (barcodeVal) {
            drawBarcode39(doc, barcodeVal, x + cfg.PADDING_X, curY, availW, cfg.BAR_H);
        }
        curY += cfg.BAR_H;

        // ── Demirbaş no — barkodun altında metin ─────────────────────────────
        curY += cfg.GAP;
        var demText = truncateText(doc, rawDem, availW, FS);
        curY += CHAR_H / 2;
        doc.text(demText, cx, curY, { align: 'center', baseline: 'middle' });
        curY += CHAR_H / 2;

        // ── Kitap adı ─────────────────────────────────────────────────────────
        curY += cfg.GAP;
        var eserAll   = doc.splitTextToSize(String(book.title || ''), availW);
        var eserLines = eserAll.slice(0, MAX_WRAP);
        if (eserAll.length > MAX_WRAP) {
            eserLines[MAX_WRAP - 1] = truncateText(doc, eserLines[MAX_WRAP - 1] + ' \u2026', availW, FS);
        }
        curY += CHAR_H / 2;
        for (var j = 0; j < eserLines.length; j++) {
            doc.text(eserLines[j], cx, curY + j * LH, { align: 'center', baseline: 'middle' });
        }
    }

    /**
     * Code 39 barkodunu jsPDF belgesine çizer.
     * JsBarcode kütüphanesi kullanılır; yüklü değilse sessizce atlanır.
     *
     * @param {object} doc       - jsPDF instance
     * @param {string} text      - Barkod değeri (Code 39 geçerli karakterler)
     * @param {number} x, y      - Sol üst köşe (mm)
     * @param {number} w, h      - Genişlik ve yükseklik (mm)
     */
    function drawBarcode39(doc, text, x, y, w, h) {
        if (typeof JsBarcode === 'undefined' || !text) return;

        // Yüksek çözünürlüklü canvas (mm → piksel: 1mm ≈ 3.78px × 3 = ~11.34px/mm)
        var scale  = 4;
        var canvas = document.createElement('canvas');
        canvas.width  = Math.round(w * 3.7795 * scale);
        canvas.height = Math.round(h * 3.7795 * scale);

        try {
            JsBarcode(canvas, text, {
                format:       'CODE39',
                displayValue: false,
                margin:       0,
                //background:   '#dcdcdc',  // test gri arka planı ile uyumlu
                lineColor:    '#000000',
                width:        scale,       // bar genişliği (piksel)
                height:       canvas.height
            });
        } catch (e) {
            // Geçersiz karakter vb. hata → barkod atlanır
            console.warn('Barkod oluşturulamadı:', text, e);
            return;
        }

        var imgData = canvas.toDataURL('image/png');
        doc.addImage(imgData, 'PNG', x, y, w, h);
    }

    /**
     * Code 39 barkodunu 90° döndürülmüş (dikey) olarak çizer.
     * Barkod soldan sağa yukarıdan aşağıya okunur (CCW döndürme).
     *
     * @param {object} doc    - jsPDF instance
     * @param {string} text   - Barkod değeri
     * @param {number} x, y   - Etiket sol üst köşe (mm) — barkod buraya yaslanır
     * @param {number} w      - Sütun genişliği (mm) — barkod yüksekliği olur
     * @param {number} h      - Etiket yüksekliği (mm) — barkod uzunluğu olur
     */
    function drawBarcode39Vertical(doc, text, x, y, w, h) {
        if (typeof JsBarcode === 'undefined' || !text) return;

        var PX_PER_MM = 3.7795;
        var RES       = 6;

        var narrowPx   = 2;
        var charsTotal = text.length + 2;
        var tmpW = Math.ceil(charsTotal * 16 * narrowPx + 40);
        var tmpH = Math.round(w * PX_PER_MM * RES);

        // 1) Kaynak canvas — yatay render
        var srcCanvas = document.createElement('canvas');
        srcCanvas.width  = tmpW;
        srcCanvas.height = tmpH;

        var srcCtx = srcCanvas.getContext('2d');
        srcCtx.fillStyle = '#ffffff';
        srcCtx.fillRect(0, 0, tmpW, tmpH);

        try {
            JsBarcode(srcCanvas, text, {
                format:       'CODE39',
                displayValue: false,
                margin:       0,
                background:   '#ffffff',
                lineColor:    '#000000',
                width:        narrowPx,
                height:       tmpH
            });
        } catch (e) {
            console.warn('Dikey barkod olusturulamadi:', text, e);
            return;
        }

        // 2) JsBarcode'un eklediği dahili beyaz boşluğu kırp (yatay yön = barkod uzunluğu)
        //    Pikselleri tarayarak siyah içerik olan ilk/son sütunu bul
        var imgPixels = srcCtx.getImageData(0, 0, tmpW, tmpH);
        var data      = imgPixels.data;
        var cropLeft  = 0;
        var cropRight = tmpW - 1;

        outer_left:
            for (var col = 0; col < tmpW; col++) {
                for (var row = 0; row < tmpH; row++) {
                    var idx = (row * tmpW + col) * 4;
                    if (data[idx] < 200) { cropLeft = col; break outer_left; }
                }
            }
        outer_right:
            for (var col2 = tmpW - 1; col2 >= 0; col2--) {
                for (var row2 = 0; row2 < tmpH; row2++) {
                    var idx2 = (row2 * tmpW + col2) * 4;
                    if (data[idx2] < 200) { cropRight = col2; break outer_right; }
                }
            }

        var cropW = cropRight - cropLeft + 1;

        // 3) Hedef canvas — döndürülmüş, PDF boyutuna göre
        var dstW = Math.round(w * PX_PER_MM * RES);
        var dstH = Math.round(h * PX_PER_MM * RES);

        var rotCanvas = document.createElement('canvas');
        rotCanvas.width  = dstW;
        rotCanvas.height = dstH;

        var ctx = rotCanvas.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, dstW, dstH);

        // 4) 90° CCW döndür — kırpılmış bölgeyi tam alana ölçekle
        ctx.translate(0, dstH);
        ctx.rotate(-Math.PI / 2);
        ctx.drawImage(srcCanvas, cropLeft, 0, cropW, tmpH, 0, 0, dstH, dstW);

        var imgData = rotCanvas.toDataURL('image/png');
        doc.addImage(imgData, 'PNG', x, y, w, h);
    }

    /**
     * kunyeSiniflamaYer örüntüleri:
     *   "813.42/MUHb"          → ["813.42", "MUHb"]
     *   "Ç\nT813.42/DÖLy"     → ["Ç", "T813.42", "DÖLy"]
     *   "Ç\n808.08681/TARh"   → ["Ç", "808.08681", "TARh"]
     *   "956.102092/KUTo"      → ["956.102092", "KUTo"]
     *
     * Ardından yayinTarihi ve k.X/c.X eklenir.
     */
    function buildLines(book) {
        var raw      = String(book.siniflamaYer || '').trim();
        var lines    = [];

        // "/" bul — sadece ilkini böl (k.X/c.X ile karıştırmasın)
        var slashIdx = raw.indexOf('/');
        var before   = slashIdx !== -1 ? raw.substring(0, slashIdx).trim() : raw;
        var after    = slashIdx !== -1 ? raw.substring(slashIdx + 1).trim() : '';

        // "/" öncesindeki \n satırları da ayrıştır (ör: "Ç\nT813.42" → ["Ç","T813.42"])
        before
            .replace(/\r\n/g, '\n')
            .replace(/\r/g, '\n')
            .split('\n')
            .forEach(function(s) {
                s = s.trim();
                if (s) lines.push(s);
            });

        // "/" sonrası
        if (after) lines.push(after);

        // Yayın tarihi
        var yt = String(book.yayinTarihi || '').trim();
        if (yt) lines.push(yt);

        // Kopya / Cilt
        var kc = buildKCLine(
            String(book.kopya || '').trim(),
            String(book.cilt  || '').trim()
        );
        if (kc) lines.push(kc);

        return lines;
    }

    /**
     * Etiketi çizer.
     *
     * Tüm satırlar 8pt bold, yatay ortalı.
     * Metin bloğu etiket içinde dikey ortalanır.
     * Satırlar arasında ek boşluk yoktur (doğal line-height).
     * Çerçeve yoktur.
     */
    function drawLabel(doc, book, x, y, w, h) {
        var lines = buildLines(book);
        var n     = lines.length;
        if (!n) return;

        // ── TEST: Gri arka plan (hizalama kontrolü için) ─────────────────────
        //doc.setFillColor(220, 220, 220);
        //doc.rect(x, y, w, h, 'F');

        var FS       = TIP1.FONT_SIZE;     // 12 pt
        var LINE_H   = TIP1.LINE_H_MM;
        var CHAR_H   = FS * 0.3528;        // tek karakter yüksekliği

        doc.setFont('helvetica', 'bold');
        doc.setFontSize(FS);
        doc.setTextColor(0, 0, 0);

        // Blok yüksekliği: (n-1) aralık + son satırın kendi yüksekliği
        var blockH = (n - 1) * LINE_H + CHAR_H;

        // İlk satırın orta noktası — blok dikey ortada
        var firstY = y + (h - blockH) / 2 + CHAR_H / 2;

        for (var i = 0; i < n; i++) {
            var lineText = truncateText(doc, lines[i], w - 2, FS);
            doc.text(lineText, x + w / 2, firstY + i * LINE_H, {
                align:    'center',
                baseline: 'middle'
            });
        }
    }

    /**
     * "k.X" veya "k.X/c.X" dizgesi üretir.
     */
    function buildKCLine(kopya, cilt) {
        var parts = [];
        if (kopya) parts.push('k.' + kopya);
        if (cilt)  parts.push('c.' + cilt);
        return parts.join('/');
    }

    /**
     * Metni verilen maksimum genişliğe göre kırpar, "…" ekler.
     */
    function truncateText(doc, text, maxWidth, fontSize) {
        doc.setFontSize(fontSize);
        if (!text) return '';
        if (doc.getTextWidth(text) <= maxWidth) return text;
        while (text.length > 1 && doc.getTextWidth(text + '…') > maxWidth) {
            text = text.slice(0, -1);
        }
        return text + '…';
    }

    // ── Download / Print ──────────────────────────────────────────────────────────
    function downloadPDF() {
        if (!lastPdfBlob) return;
        var a = document.createElement('a');
        a.href = lastPdfUrl;
        a.download = 'etiketler_' + new Date().toISOString().slice(0,10) + '.pdf';
        a.click();
    }

    function printPDF() {
        var frame = document.getElementById('pdfFrame');
        if (!frame || frame.style.display === 'none') return;
        frame.contentWindow.print();
    }

    // ── Enter tuşu ile arama ─────────────────────────────────────────────────────
    (function() {
        var textInputIds = ['searchInput', 'filterDemirbas', 'filterOzelNotlar'];
        textInputIds.forEach(function(id) {
            var el = document.getElementById(id);
            if (el) {
                el.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') { e.preventDefault(); runSearch(); }
                });
            }
        });
    })();

    // ── Helpers ───────────────────────────────────────────────────────────────────
    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
</script>
</body>
</html>