<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#7a5c3c" />
    <title>Yeni Kullanıcı — Beyoğlu Kütüphane Sistemi</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700;900&family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --background:#f5f0e8;--foreground:#3d3226;--card:#faf8f3;
            --primary:#7a5c3c;--primary-foreground:#f5f0e8;
            --secondary:#ede8de;--muted:#ede8de;--muted-foreground:#7a7060;
            --destructive:#c53030;--border:#d9d0c2;--ring:#7a5c3c;--radius:0.625rem;
            --sidebar:#3d3226;--sidebar-foreground:#e8e2d6;
            --sidebar-primary:#9b7b55;--sidebar-primary-foreground:#f5f0e8;
            --sidebar-accent:#524435;--sidebar-accent-foreground:#e8e2d6;
            --sidebar-border:#5a4a3a;
            --font-sans:'Source Sans 3',system-ui,sans-serif;
            --font-serif:'Merriweather',Georgia,serif;
        }
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:var(--font-sans);background:var(--background);color:var(--foreground);-webkit-font-smoothing:antialiased;line-height:1.5}
        input,select,button{font-family:inherit;font-size:inherit}

        .app-layout{display:flex;min-height:100vh}

        /* ── Sidebar ── */
        .sidebar{width:260px;background:var(--sidebar);color:var(--sidebar-foreground);display:flex;flex-direction:column;flex-shrink:0;border-right:1px solid var(--sidebar-border);position:fixed;top:0;left:0;bottom:0;z-index:40;transition:transform .3s ease}
        .sidebar.collapsed{transform:translateX(-260px)}
        .sidebar-header{padding:16px;display:flex;align-items:center;gap:12px}
        .sidebar-logo{width:36px;height:36px;border-radius:8px;background:var(--sidebar-primary);display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .sidebar-logo svg{width:20px;height:20px;color:var(--sidebar-primary-foreground)}
        .sidebar-brand-name{font-size:16px;font-weight:700;letter-spacing:-.025em}
        .sidebar-brand-sub{font-size:12px;opacity:.6}
        .sidebar-separator{height:1px;background:var(--sidebar-border);margin:0 16px}
        .sidebar-content{flex:1;overflow-y:auto;padding:8px 0}
        .sidebar-group{padding:8px 12px}
        .sidebar-group-label{font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--sidebar-foreground);opacity:.5;padding:4px 8px;margin-bottom:4px}
        .sidebar-menu{list-style:none}
        .sidebar-menu-item{display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:6px;font-size:14px;font-weight:500;color:var(--sidebar-foreground);cursor:pointer;transition:background .15s;text-decoration:none}
        .sidebar-menu-item:hover{background:var(--sidebar-accent)}
        .sidebar-menu-item.active{background:var(--sidebar-accent);color:var(--sidebar-accent-foreground)}
        .sidebar-menu-item svg{width:18px;height:18px;flex-shrink:0;opacity:.8}
        .sidebar-footer{padding:16px;border-top:1px solid var(--sidebar-border)}
        .sidebar-user{display:flex;align-items:center;gap:12px}
        .sidebar-avatar{width:32px;height:32px;border-radius:50%;background:var(--sidebar-accent);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;flex-shrink:0}
        .sidebar-user-name{font-size:14px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .sidebar-user-role{font-size:12px;opacity:.6}

        /* ── Main ── */
        .main-content{flex:1;margin-left:260px;display:flex;flex-direction:column;min-height:100vh;transition:margin-left .3s ease}
        .main-content.expanded{margin-left:0}
        .top-header{height:56px;display:flex;align-items:center;gap:16px;padding:0 16px;border-bottom:1px solid rgba(217,208,194,.6);background:var(--card);flex-shrink:0}
        .sidebar-trigger{width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:6px;border:none;background:transparent;cursor:pointer;color:var(--foreground);transition:background .15s}
        .sidebar-trigger:hover{background:var(--muted)}
        .sidebar-trigger svg{width:18px;height:18px}
        .header-separator{width:1px;height:20px;background:var(--border)}
        .breadcrumb{display:flex;align-items:center;gap:8px;font-size:14px}
        .breadcrumb-link{display:flex;align-items:center;gap:6px;color:var(--muted-foreground);text-decoration:none;transition:color .15s}
        .breadcrumb-link:hover{color:var(--foreground)}
        .breadcrumb-link svg{width:14px;height:14px}
        .breadcrumb-sep{color:var(--muted-foreground);opacity:.5;font-size:12px}
        .breadcrumb-current{font-weight:500;color:var(--foreground)}
        .content-area{flex:1;padding:24px}

        /* ── Form Card ── */
        .form-card{border:1px solid rgba(217,208,194,.6);border-radius:var(--radius);background:var(--card);box-shadow:0 1px 3px rgba(0,0,0,.04);max-width:720px}
        .form-card-header{padding:24px 24px 16px}
        .form-card-title{display:flex;align-items:center;gap:10px;font-family:var(--font-serif);font-size:20px;font-weight:700}
        .form-card-title .title-icon{width:38px;height:38px;border-radius:10px;background:rgba(122,92,60,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .form-card-title .title-icon svg{width:20px;height:20px;color:var(--primary)}
        .form-card-desc{font-size:14px;color:var(--muted-foreground);margin-top:4px;margin-left:48px}
        .form-card-separator{height:1px;background:var(--border)}
        .form-card-body{padding:24px;display:flex;flex-direction:column;gap:22px}

        /* ── Section ── */
        .section-label{display:flex;align-items:center;gap:8px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted-foreground);margin-bottom:14px}
        .section-num{width:20px;height:20px;border-radius:4px;background:rgba(122,92,60,.1);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:var(--primary)}
        .section-sep{height:1px;background:var(--border)}

        /* ── Form Grid ── */
        .form-grid{display:grid;gap:14px}
        .form-grid.cols-2{grid-template-columns:repeat(2,1fr)}
        .form-grid.cols-3{grid-template-columns:repeat(3,1fr)}
        .span-2{grid-column:span 2}
        .form-field{display:flex;flex-direction:column}
        .form-label{font-size:14px;font-weight:500;color:var(--foreground);margin-bottom:6px}
        .form-label .req{color:var(--destructive)}
        .form-label .hint{font-weight:400;color:var(--muted-foreground);font-size:12px;margin-left:4px}
        .input-wrap{position:relative}
        .input-icon{position:absolute;left:11px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:var(--muted-foreground);pointer-events:none}
        .form-input,.form-select{width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);color:var(--foreground);font-size:14px;line-height:1.5;transition:border-color .15s,box-shadow .15s;outline:none}
        .form-input.has-icon{padding-left:34px}
        .form-input.has-right{padding-right:40px}
        .form-input::placeholder{color:var(--muted-foreground);opacity:.7}
        .form-input:focus,.form-select:focus{border-color:var(--ring);box-shadow:0 0 0 2px rgba(122,92,60,.15)}
        .form-input.is-error,.form-select.is-error{border-color:var(--destructive)}
        .form-select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237a7060' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:36px}
        .form-textarea{width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);color:var(--foreground);font-size:14px;line-height:1.5;transition:border-color .15s,box-shadow .15s;outline:none;resize:vertical;min-height:80px}
        .form-textarea:focus{border-color:var(--ring);box-shadow:0 0 0 2px rgba(122,92,60,.15)}
        .adres-sorgu-btn{width:22px;height:22px;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--muted-foreground);border-radius:4px;padding:0;flex-shrink:0;transition:color .15s,background .15s}
        .adres-sorgu-btn:hover{color:var(--primary);background:rgba(122,92,60,.08)}
        .adres-sorgu-btn:active{background:rgba(122,92,60,.15)}
        .adres-sorgu-btn:disabled{pointer-events:none;opacity:.5}
        .adres-sorgu-btn svg{width:14px;height:14px;display:block}
        .adres-ikamet-badge{display:flex;align-items:center;gap:8px;padding:10px 14px;font-size:13px;font-weight:600;border-radius:calc(var(--radius) - 2px)}
        .adres-ikamet-badge.ediyor{background:rgba(34,197,94,.1);color:#15803d;border:1px solid rgba(34,197,94,.25)}
        .adres-ikamet-badge.etmiyor{background:rgba(239,68,68,.08);color:#b91c1c;border:1px solid rgba(239,68,68,.2)}
        .adres-ikamet-badge.bulunamadi{background:rgba(245,158,11,.08);color:#92400e;border:1px solid rgba(245,158,11,.25)}
        .adres-ikamet-badge svg{width:15px;height:15px;flex-shrink:0}
        .adres-readonly-wrap{margin-top:8px}
        .adres-readonly-label{font-size:12px;font-weight:600;color:var(--muted-foreground);text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px}
        .adres-readonly-input{width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--secondary);color:var(--foreground);font-size:14px;line-height:1.5;cursor:default;outline:none}
        @keyframes adres-spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
        .adres-spin{animation:adres-spin .7s linear infinite}
        .form-error{font-size:12px;color:var(--destructive);margin-top:4px;display:flex;align-items:center;gap:4px}
        .form-error svg{width:12px;height:12px;flex-shrink:0}
        .form-hint-text{font-size:12px;color:var(--muted-foreground);margin-top:4px}

        /* Password toggle */
        .pw-toggle{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:var(--muted-foreground);display:flex;border-radius:4px;transition:color .15s,background .15s}
        .pw-toggle:hover{color:var(--foreground);background:var(--muted)}
        .pw-toggle svg{width:15px;height:15px}

        /* Password strength */
        .pw-strength{margin-top:8px}
        .pw-strength-bar{display:flex;gap:3px;margin-bottom:4px}
        .pw-bar-seg{height:3px;flex:1;border-radius:999px;background:var(--border);transition:background .25s}
        .pw-bar-seg.weak{background:#ef4444}
        .pw-bar-seg.medium{background:#f59e0b}
        .pw-bar-seg.strong{background:#22c55e}
        .pw-strength-text{font-size:11px;color:var(--muted-foreground)}

        /* Role cards */
        .role-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
        .role-card{position:relative;cursor:pointer}
        .role-card input[type=radio]{position:absolute;opacity:0;width:0;height:0}
        .role-card-inner{padding:14px 12px;border:1.5px solid var(--border);border-radius:calc(var(--radius) - 2px);transition:border-color .15s,background .15s;display:flex;flex-direction:column;gap:6px;user-select:none}
        .role-card input:checked ~ .role-card-inner{border-color:var(--primary);background:rgba(122,92,60,.05)}
        .role-card-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center}
        .role-card-icon svg{width:18px;height:18px}
        .role-card-name{font-size:13px;font-weight:600;color:var(--foreground)}
        .role-card-desc{font-size:11px;color:var(--muted-foreground);line-height:1.4}
        .role-check{position:absolute;top:8px;right:8px;width:16px;height:16px;border-radius:50%;border:1.5px solid var(--border);background:var(--card);display:flex;align-items:center;justify-content:center;transition:background .15s,border-color .15s}
        .role-card input:checked ~ .role-check{background:var(--primary);border-color:var(--primary)}
        .role-check svg{width:9px;height:9px;color:white;opacity:0;transition:opacity .15s}
        .role-card input:checked ~ .role-check svg{opacity:1}

        /* ── Library Selection ─────────────────────────────────────────────── */
        .kutuphane-section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
        .kutuphane-count-badge{background:rgba(122,92,60,.12);color:var(--primary);font-size:12px;font-weight:700;padding:2px 8px;border-radius:99px}
        .kutuphane-search-wrap{position:relative;margin-bottom:10px}
        .kutuphane-search-input{width:100%;padding:7px 10px 7px 32px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--secondary);font-size:13px;color:var(--foreground);outline:none;transition:border-color .15s,box-shadow .15s}
        .kutuphane-search-input:focus{border-color:var(--ring);box-shadow:0 0 0 2px rgba(122,92,60,.12);background:var(--card)}
        .kutuphane-search-input::placeholder{color:var(--muted-foreground);opacity:.7}
        .kutuphane-search-icon{position:absolute;left:9px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:var(--muted-foreground);pointer-events:none}
        .kutuphane-list{border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);overflow:hidden;max-height:220px;overflow-y:auto}
        .kutuphane-item{display:flex;align-items:center;gap:10px;padding:9px 12px;cursor:pointer;transition:background .1s;border-bottom:1px solid rgba(217,208,194,.35);user-select:none}
        .kutuphane-item:last-child{border-bottom:none}
        .kutuphane-item:hover{background:rgba(237,232,222,.6)}
        .kutuphane-item.checked{background:rgba(122,92,60,.05)}
        .kutuphane-item input[type=checkbox]{width:16px;height:16px;accent-color:var(--primary);flex-shrink:0;cursor:pointer}
        .kutuphane-item-name{font-size:13px;font-weight:500;flex:1}
        .kutuphane-empty{padding:20px 12px;text-align:center;font-size:13px;color:var(--muted-foreground)}
        .kutuphane-hint{font-size:12px;color:var(--muted-foreground);margin-top:6px}
        .kutuphane-select-all{display:flex;align-items:center;gap:8px;padding:7px 12px;background:var(--secondary);border-bottom:1px solid var(--border);font-size:12px;font-weight:600;cursor:pointer;color:var(--muted-foreground);user-select:none;transition:background .1s}
        .kutuphane-select-all:hover{background:var(--muted)}

        /* Actions */
        .form-actions{display:flex;justify-content:flex-end;gap:12px;padding-top:4px}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:9px 16px;border-radius:calc(var(--radius) - 2px);font-size:14px;font-weight:500;cursor:pointer;transition:background .15s,opacity .15s;border:none;text-decoration:none}
        .btn svg{width:16px;height:16px}
        .btn-primary{background:var(--primary);color:var(--primary-foreground)}
        .btn-primary:hover{opacity:.9}
        .btn-primary:disabled{opacity:.6;cursor:not-allowed}
        .btn-outline{background:transparent;color:var(--foreground);border:1px solid var(--border)}
        .btn-outline:hover{background:var(--muted)}

        /* Loading overlay */
        .loading-overlay{position:fixed;inset:0;z-index:2000;background:rgba(61,50,38,.45);backdrop-filter:blur(3px);display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:opacity .2s ease,visibility .2s ease}
        .loading-overlay.visible{opacity:1;visibility:visible}
        .loading-box{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:40px 56px;display:flex;flex-direction:column;align-items:center;gap:20px;box-shadow:0 20px 60px rgba(0,0,0,.25);transform:scale(.92);transition:transform .2s ease}
        .loading-overlay.visible .loading-box{transform:scale(1)}
        .loading-spinner{width:48px;height:48px;border:3px solid var(--border);border-top-color:var(--primary);border-radius:50%;animation:spin .75s linear infinite}
        .loading-text{font-size:15px;font-weight:600}
        .loading-subtext{font-size:13px;color:var(--muted-foreground);margin-top:-12px}
        @keyframes spin{from{transform:rotate(0)}to{transform:rotate(360deg)}}

        /* Toast */
        .toast-container{position:fixed;top:16px;right:16px;z-index:3000;display:flex;flex-direction:column;gap:8px}
        .toast{padding:14px 20px;border-radius:10px;font-size:14px;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,.15);animation:toast-in .3s ease;max-width:380px}
        .toast.success{background:#2f7d32;color:#fff}
        .toast.error{background:var(--destructive);color:#fff}
        .toast-desc{font-size:13px;font-weight:400;opacity:.9;margin-top:2px}
        @keyframes toast-in{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}
        @keyframes toast-out{from{opacity:1}to{opacity:0;transform:translateX(100%)}}

        .sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:35}
        .sidebar-overlay.visible{display:block}

        @media(max-width:768px){
            .sidebar{transform:translateX(-260px)}
            .sidebar.open{transform:translateX(0)}
            .main-content{margin-left:0}
            .content-area{padding:16px}
            .form-grid.cols-2,.form-grid.cols-3,.role-cards{grid-template-columns:1fr}
            .span-2{grid-column:span 1}
            .form-actions{flex-direction:column-reverse}
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
            <button class="sidebar-trigger" id="sidebarToggle">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/></svg>
            </button>
            <div class="header-separator"></div>
            <nav class="breadcrumb">
                <a href="{{ route('users.index') }}" class="breadcrumb-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    Kullanıcılar
                </a>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-current">Yeni Kullanıcı</span>
            </nav>
        </header>

        <div class="content-area">
            <form id="userForm" class="form-card" method="POST"
                  action="{{ route('users.store') }}" novalidate>
                @csrf

                <div class="form-card-header">
                    <h2 class="form-card-title">
                        <span class="title-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                        </span>
                        Yeni Kullanıcı
                    </h2>
                    <p class="form-card-desc">Sisteme yeni bir kullanıcı hesabı ekleyin.</p>
                </div>
                <div class="form-card-separator"></div>

                <div class="form-card-body">

                    {{-- Bölüm 1: Kimlik Bilgileri --}}
                    <div>
                        <div class="section-label" style="justify-content:space-between;">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span class="section-num">1</span>
                                Kimlik Bilgileri
                            </div>
                            <button type="button" id="kimlikSorgulaBtn"
                                    class="adres-sorgu-btn"
                                    title="KPS üzerinden kimlik sorgula"
                                    aria-label="Kimlik sorgula"
                                    onclick="kimlikSorgula()">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            </button>
                        </div>
                        <div class="form-grid cols-2">
                            <div class="form-field">
                                <label class="form-label" for="tc_kimlik">TC Kimlik No <span class="req">*</span></label>
                                <input type="text" id="tc_kimlik" name="tc_kimlik"
                                       class="form-input @error('tc_kimlik') is-error @enderror"
                                       value="{{ old('tc_kimlik') }}" placeholder="00000000000"
                                       maxlength="11" inputmode="numeric" pattern="\d{11}" autocomplete="off" />
                                @error('tc_kimlik')<div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div>@enderror
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="dogum_tarihi">Doğum Tarihi <span class="req">*</span></label>
                                <input type="date" id="dogum_tarihi" name="dogum_tarihi"
                                       class="form-input @error('dogum_tarihi') is-error @enderror"
                                       value="{{ old('dogum_tarihi') }}"
                                       max="{{ date('Y-m-d') }}" />
                                @error('dogum_tarihi')<div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="section-sep"></div>

                    {{-- Bölüm 2: Kişisel Bilgiler --}}
                    <div>
                        <p class="section-label"><span class="section-num">2</span> Kişisel Bilgiler</p>
                        <div class="form-grid cols-2">
                            <div class="form-field">
                                <label class="form-label" for="ad">Ad <span class="req">*</span></label>
                                <input type="text" id="ad" name="ad"
                                       class="form-input @error('ad') is-error @enderror"
                                       value="{{ old('ad') }}" placeholder="Adı" autocomplete="given-name" />
                                @error('ad')<div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div>@enderror
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="soyad">Soyad <span class="req">*</span></label>
                                <input type="text" id="soyad" name="soyad"
                                       class="form-input @error('soyad') is-error @enderror"
                                       value="{{ old('soyad') }}" placeholder="Soyadı" autocomplete="family-name" />
                                @error('soyad')<div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div>@enderror
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="cinsiyet">Cinsiyet <span class="hint">(isteğe bağlı)</span></label>
                                <select id="cinsiyet" name="cinsiyet" class="form-select @error('cinsiyet') is-error @enderror">
                                    <option value="" {{ old('cinsiyet') === null || old('cinsiyet') === '' ? 'selected' : '' }}>Belirtilmedi</option>
                                    <option value="erkek" {{ old('cinsiyet') === 'erkek' ? 'selected' : '' }}>Erkek</option>
                                    <option value="kadin" {{ old('cinsiyet') === 'kadin' ? 'selected' : '' }}>Kadın</option>
                                    <option value="diger" {{ old('cinsiyet') === 'diger' ? 'selected' : '' }}>Diğer</option>
                                </select>
                                @error('cinsiyet')<div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div>@enderror
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="telefon">Telefon <span class="req">*</span></label>
                                <div class="input-wrap">
                                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.18 2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.68 2.81a2 2 0 0 1-.45 2.11L7.91 8.56a16 16 0 0 0 8 8l.92-.92a2 2 0 0 1 2.11-.45c.91.32 1.85.55 2.81.68A2 2 0 0 1 22 16.92z"/></svg>
                                    <input type="tel" id="telefon" name="telefon"
                                           class="form-input has-icon @error('telefon') is-error @enderror"
                                           value="{{ old('telefon') }}" placeholder="05xxxxxxxxx" maxlength="11" inputmode="tel" />
                                </div>
                                @error('telefon')<div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div>@enderror
                            </div>
                            <div class="form-field span-2">
                                <label class="form-label" for="email">E-posta <span class="req">*</span></label>
                                <div class="input-wrap">
                                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                    <input type="email" id="email" name="email"
                                           class="form-input has-icon @error('email') is-error @enderror"
                                           placeholder="kullanici@ornek.com"
                                           value="{{ old('email') }}" autocomplete="email" />
                                </div>
                                @error('email')<div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="section-sep"></div>

                    {{-- Bölüm 3: Adres Bilgileri --}}
                    <div>
                        <div class="section-label" style="justify-content:space-between;">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span class="section-num">3</span>
                                Adres Bilgileri
                            </div>
                            <button type="button" id="adresSorgulaBtn"
                                    class="adres-sorgu-btn"
                                    title="KPS üzerinden adres sorgula"
                                    aria-label="Adres sorgula"
                                    onclick="adresSorgula()">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            </button>
                        </div>
                        <div id="adresSonucPanel" style="display:none;margin-bottom:14px;">
                            <div id="adresIkametBadge" class="adres-ikamet-badge"></div>
                            <div id="adresReadonlyWrap" class="adres-readonly-wrap" style="display:none;">
                                <p class="adres-readonly-label">İkamet Adresi</p>
                                <input type="text" id="adresReadonlyInput" class="adres-readonly-input"
                                       readonly tabindex="-1" value="" />
                            </div>
                        </div>
                        <div class="form-grid cols-3">
                            <div class="form-field">
                                <label class="form-label" for="il">İl</label>
                                <input type="text" id="il" name="il"
                                       class="form-input @error('il') is-error @enderror"
                                       value="{{ old('il') }}" placeholder="İl adı" autocomplete="address-level1" />
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="ilce">İlçe</label>
                                <input type="text" id="ilce" name="ilce"
                                       class="form-input @error('ilce') is-error @enderror"
                                       value="{{ old('ilce') }}" placeholder="İlçe adı" />
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="mahalle">Mahalle</label>
                                <input type="text" id="mahalle" name="mahalle"
                                       class="form-input @error('mahalle') is-error @enderror"
                                       value="{{ old('mahalle') }}" placeholder="Mahalle adı" />
                            </div>
                            <div class="form-field span-2">
                                <label class="form-label" for="acik_adres">Açık Adres</label>
                                <textarea id="acik_adres" name="acik_adres" class="form-textarea"
                                          placeholder="Sokak, bina no, daire no...">{{ old('acik_adres') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="section-sep"></div>

                    {{-- Bölüm 4: Şifre Bilgileri --}}
                    <div>
                        <p class="section-label"><span class="section-num">4</span> Şifre Bilgileri</p>
                        <div class="form-grid cols-2">
                            <div class="form-field">
                                <label class="form-label" for="password">Şifre <span class="req">*</span></label>
                                <div class="input-wrap">
                                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    <input type="password" id="password" name="password"
                                           class="form-input has-icon has-right @error('password') is-error @enderror"
                                           placeholder="En az 8 karakter"
                                           autocomplete="new-password" />
                                    <button type="button" class="pw-toggle" data-target="password">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                </div>
                                <div class="pw-strength" id="pwStrength" style="display:none;">
                                    <div class="pw-strength-bar">
                                        <div class="pw-bar-seg" id="seg1"></div>
                                        <div class="pw-bar-seg" id="seg2"></div>
                                        <div class="pw-bar-seg" id="seg3"></div>
                                        <div class="pw-bar-seg" id="seg4"></div>
                                    </div>
                                    <span class="pw-strength-text" id="pwStrengthText"></span>
                                </div>
                                @error('password')<div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div>@enderror
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="password_confirmation">Şifre Tekrar <span class="req">*</span></label>
                                <div class="input-wrap">
                                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                           class="form-input has-icon has-right"
                                           placeholder="Şifreyi tekrar girin"
                                           autocomplete="new-password" />
                                    <button type="button" class="pw-toggle" data-target="password_confirmation">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                </div>
                                <div class="form-hint-text" id="matchHint" style="display:none;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="section-sep"></div>

                    {{-- Bölüm 5: Rol --}}
                    <div>
                        <p class="section-label"><span class="section-num">5</span> Kullanıcı Rolü <span style="color:var(--destructive)">*</span></p>
                        <div class="role-cards">
                            <label class="role-card">
                                <input type="radio" name="role" value="admin" {{ old('role') === 'admin' ? 'checked' : '' }} />
                                <div class="role-card-inner">
                                    <div class="role-card-icon" style="background:rgba(122,92,60,.12);">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#7a5c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                                    </div>
                                    <div class="role-card-name">Yönetici</div>
                                    <div class="role-card-desc">Tüm modüllere tam erişim.</div>
                                </div>
                                <div class="role-check"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                            </label>
                            <label class="role-card">
                                <input type="radio" name="role" value="personel" {{ old('role', 'personel') === 'personel' ? 'checked' : '' }} />
                                <div class="role-card-inner">
                                    <div class="role-card-icon" style="background:rgba(37,99,235,.1);">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                    </div>
                                    <div class="role-card-name">Personel</div>
                                    <div class="role-card-desc">Katalog ve kütüphane işlemleri</div>
                                </div>
                                <div class="role-check"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                            </label>
                            <label class="role-card">
                                <input type="radio" name="role" value="okuyucu" {{ old('role') === 'okuyucu' ? 'checked' : '' }} />
                                <div class="role-card-inner">
                                    <div class="role-card-icon" style="background:rgba(5,150,105,.1);">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
                                    </div>
                                    <div class="role-card-name">Okuyucu</div>
                                    <div class="role-card-desc">Yalnızca katalog görüntüleme</div>
                                </div>
                                <div class="role-check"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                            </label>
                        </div>
                        @error('role')<div class="form-error" style="margin-top:8px;"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div>@enderror
                    </div>

                    <div class="section-sep"></div>

                    {{-- Bölüm 6: Kütüphane Yetkileri --}}
                    <div>
                        <div class="kutuphane-section-header">
                            <p class="section-label" style="margin-bottom:0;">
                                <span class="section-num">6</span>
                                Kütüphane Yetkileri
                                <span style="font-size:11px;font-weight:400;color:var(--muted-foreground);text-transform:none;letter-spacing:0;margin-left:2px;">(İsteğe bağlı)</span>
                            </p>
                            <span class="kutuphane-count-badge" id="selectedCountBadge">0 seçili</span>
                        </div>

                        @if($kutuphaneler->isEmpty())
                            <div style="padding:16px 14px;background:var(--secondary);border-radius:calc(var(--radius) - 2px);font-size:13px;color:var(--muted-foreground);">
                                Sistemde aktif kütüphane bulunmuyor.
                            </div>
                        @else
                            <div class="kutuphane-search-wrap">
                                <svg class="kutuphane-search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                <input type="text" class="kutuphane-search-input" id="kutuphaneSearchInput" placeholder="Kütüphane adı ara..." autocomplete="off" />
                            </div>
                            <div class="kutuphane-list" id="kutuphaneList">
                                <div class="kutuphane-select-all" id="selectAllRow" onclick="toggleSelectAll()">
                                    <input type="checkbox" id="selectAllChk" style="width:16px;height:16px;accent-color:var(--primary);cursor:pointer;" onclick="event.stopPropagation();toggleSelectAll()" />
                                    <span>Tümünü Seç / Kaldır</span>
                                </div>
                                @foreach($kutuphaneler as $k)
                                    <label class="kutuphane-item" id="kutuphane-row-{{ $k->id }}" data-name="{{ strtolower($k->title) }}">
                                        <input type="checkbox"
                                               name="kutuphane_ids[]"
                                               value="{{ $k->id }}"
                                               class="kutuphane-chk"
                                               {{ in_array($k->id, old('kutuphane_ids', [])) ? 'checked' : '' }}
                                               onchange="updateSelectedCount()" />
                                        <span class="kutuphane-item-name">{{ $k->title }}</span>
                                    </label>
                                @endforeach
                                <div class="kutuphane-empty" id="kutuphaneEmptyMsg" style="display:none;">Eşleşen kütüphane bulunamadı.</div>
                            </div>
                            <p class="kutuphane-hint">Seçilen kütüphanelerde kullanıcı yetkili olarak tanımlanacaktır.</p>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="form-actions">
                        <a href="{{ route('users.index') }}" class="btn btn-outline">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            Vazgeç
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v13a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            Kullanıcı Oluştur
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
    var sidebarOverlay = document.getElementById('sidebarOverlay');
    var isMobile = window.innerWidth <= 768;
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        if (isMobile) { sidebar.classList.toggle('open'); sidebarOverlay.classList.toggle('visible'); }
        else { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); }
    });
    sidebarOverlay.addEventListener('click', function() { sidebar.classList.remove('open'); sidebarOverlay.classList.remove('visible'); });

    // Toast
    function showToast(type, title, desc) {
        var c = document.getElementById('toastContainer');
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<div>' + title + '</div>' + (desc ? '<div class="toast-desc">' + desc + '</div>' : '');
        c.appendChild(t);
        setTimeout(function() { t.style.animation = 'toast-out .3s ease forwards'; setTimeout(function() { t.remove(); }, 300); }, 3500);
    }

    // ── Şifre göster/gizle ────────────────────────────────────────────────────
    var eyeOpen   = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
    var eyeClosed = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>';
    document.querySelectorAll('.pw-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var inp = document.getElementById(btn.dataset.target);
            if (inp.type === 'password') { inp.type = 'text'; btn.innerHTML = eyeClosed; }
            else { inp.type = 'password'; btn.innerHTML = eyeOpen; }
        });
    });

    // ── Şifre gücü ────────────────────────────────────────────────────────────
    var pwInput = document.getElementById('password');
    var pwStrength = document.getElementById('pwStrength');
    var pwStrengthText = document.getElementById('pwStrengthText');
    var segs = [document.getElementById('seg1'), document.getElementById('seg2'), document.getElementById('seg3'), document.getElementById('seg4')];

    function calcStrength(pw) {
        var s = 0;
        if (pw.length >= 8) s++;
        if (/[A-Z]/.test(pw)) s++;
        if (/[0-9]/.test(pw)) s++;
        if (/[^A-Za-z0-9]/.test(pw)) s++;
        return s;
    }
    pwInput.addEventListener('input', function() {
        var val = pwInput.value;
        if (!val) { pwStrength.style.display = 'none'; return; }
        pwStrength.style.display = 'block';
        var s = calcStrength(val);
        var cls = s <= 1 ? 'weak' : s <= 2 ? 'medium' : 'strong';
        var label = s <= 1 ? 'Zayıf şifre' : s <= 2 ? 'Orta güçte şifre' : s === 3 ? 'Güçlü şifre' : 'Çok güçlü şifre';
        segs.forEach(function(seg, i) { seg.className = 'pw-bar-seg'; if (i < s) seg.classList.add(cls); });
        pwStrengthText.textContent = label;
        pwStrengthText.style.color = cls === 'weak' ? '#ef4444' : cls === 'medium' ? '#f59e0b' : '#22c55e';
    });

    // ── Şifre eşleşme ─────────────────────────────────────────────────────────
    var pwConf = document.getElementById('password_confirmation');
    var matchHint = document.getElementById('matchHint');
    function checkMatch() {
        if (!pwConf.value) { matchHint.style.display = 'none'; return; }
        if (pwInput.value === pwConf.value) { matchHint.style.display = 'block'; matchHint.textContent = '✓ Şifreler eşleşiyor'; matchHint.style.color = '#22c55e'; }
        else { matchHint.style.display = 'block'; matchHint.textContent = '✗ Şifreler eşleşmiyor'; matchHint.style.color = '#ef4444'; }
    }
    pwConf.addEventListener('input', checkMatch);
    pwInput.addEventListener('input', function() { if (pwConf.value) checkMatch(); });

    // ── Kütüphane arama (client-side filter) ──────────────────────────────────
    var kutuphaneSearchInput = document.getElementById('kutuphaneSearchInput');
    if (kutuphaneSearchInput) {
        kutuphaneSearchInput.addEventListener('input', function() {
            var q = this.value.toLowerCase().trim();
            var rows = document.querySelectorAll('.kutuphane-item');
            var visible = 0;
            rows.forEach(function(row) {
                var name = row.dataset.name || '';
                if (!q || name.includes(q)) { row.style.display = ''; visible++; }
                else { row.style.display = 'none'; }
            });
            var emptyMsg = document.getElementById('kutuphaneEmptyMsg');
            if (emptyMsg) emptyMsg.style.display = visible === 0 ? 'block' : 'none';
        });
    }

    // ── Seçili sayacı ─────────────────────────────────────────────────────────
    function updateSelectedCount() {
        var checked = document.querySelectorAll('.kutuphane-chk:checked').length;
        var badge = document.getElementById('selectedCountBadge');
        if (badge) badge.textContent = checked + ' seçili';
        // kutuphane item görünümü
        document.querySelectorAll('.kutuphane-item').forEach(function(item) {
            var chk = item.querySelector('.kutuphane-chk');
            if (chk) item.classList.toggle('checked', chk.checked);
        });
        // tümünü seç checkbox sync
        var allChk = document.getElementById('selectAllChk');
        var total = document.querySelectorAll('.kutuphane-chk').length;
        if (allChk) allChk.indeterminate = checked > 0 && checked < total;
        if (allChk) allChk.checked = total > 0 && checked === total;
    }

    function toggleSelectAll() {
        var allChk = document.getElementById('selectAllChk');
        var chks = document.querySelectorAll('.kutuphane-chk');
        var anyUnchecked = Array.from(chks).some(function(c) { return !c.checked; });
        chks.forEach(function(c) { c.checked = anyUnchecked; });
        updateSelectedCount();
    }

    // Init count on page load (for old() values)
    updateSelectedCount();

    // TC kimlik sadece rakam
    var tcKimlikInput = document.getElementById('tc_kimlik');
    if (tcKimlikInput) {
        tcKimlikInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 11);
        });
    }

    function kimlikSorgula() {
        var tcKimlik    = document.getElementById('tc_kimlik')?.value?.trim() || '';
        var dogumTarihi = document.getElementById('dogum_tarihi')?.value?.trim() || '';
        if (!tcKimlik || tcKimlik.length !== 11) {
            showToast('error', 'TC Kimlik gerekli', 'Kimlik sorgulama için önce TC Kimlik No giriniz (11 rakam).');
            return;
        }
        if (!dogumTarihi) {
            showToast('error', 'Doğum Tarihi gerekli', 'Kimlik sorgulama için önce Doğum Tarihi giriniz.');
            return;
        }

        var btn = document.getElementById('kimlikSorgulaBtn');
        var adEl = document.getElementById('ad');
        var soyadEl = document.getElementById('soyad');
        var cinsiyetEl = document.getElementById('cinsiyet');
        btn.disabled = true;
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="adres-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';

        fetch('{{ route("kps.kimlikSorgula") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ tc_kimlik: tcKimlik, dogum_tarihi: dogumTarihi }),
        })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                btn.disabled = false;
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';
                if (!data.success) {
                    showToast('error', 'Kimlik Sorgu Hatası', data.message || 'Kimlik doğrulaması başarısız.');
                    return;
                }

                if (adEl) adEl.value = data.ad || '';
                if (soyadEl) soyadEl.value = data.soyad || '';
                if (cinsiyetEl && typeof data.cinsiyet !== 'undefined') cinsiyetEl.value = data.cinsiyet || '';
                showToast('success', 'Kimlik Doğrulandı', (data.ad || '') + ' ' + (data.soyad || '') + ' bilgileri getirildi.');
            })
            .catch(function() {
                btn.disabled = false;
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';
                showToast('error', 'Bağlantı Hatası', 'Sunucuya bağlanılamadı. Lütfen tekrar deneyin.');
            });
    }

    function adresSorgula() {
        var tcKimlik    = document.getElementById('tc_kimlik')?.value?.trim() || '';
        var dogumTarihi = document.getElementById('dogum_tarihi')?.value?.trim() || '';
        if (!tcKimlik || tcKimlik.length !== 11) {
            showToast('error', 'TC Kimlik gerekli', 'Adres sorgulama için önce TC Kimlik No giriniz (11 rakam).');
            return;
        }
        if (!dogumTarihi) {
            showToast('error', 'Doğum Tarihi gerekli', 'Adres sorgulama için önce Doğum Tarihi giriniz.');
            return;
        }

        var btn   = document.getElementById('adresSorgulaBtn');
        var panel = document.getElementById('adresSonucPanel');
        var badge = document.getElementById('adresIkametBadge');
        var wrap  = document.getElementById('adresReadonlyWrap');
        var input = document.getElementById('adresReadonlyInput');
        btn.disabled = true;
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="adres-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';
        panel.style.display = 'none';

        fetch('{{ route("kps.adresSorgula") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ tc_kimlik: tcKimlik, dogum_tarihi: dogumTarihi }),
        })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                btn.disabled = false;
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';
                if (!data.success) {
                    showToast('error', 'Sorgu Hatası', data.message || 'Adres sorgulaması başarısız.');
                    return;
                }

                applyAdresSorguToForm(data);
                panel.style.display = 'block';
                if (data.ikametEdiyor) {
                    badge.className = 'adres-ikamet-badge ediyor';
                    badge.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>İlçede İkamet Ediyor';
                    wrap.style.display = 'block';
                    input.value = data.adres || '';
                } else if (data.adres) {
                    badge.className = 'adres-ikamet-badge etmiyor';
                    badge.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" x2="9" y1="9" y2="15"/><line x1="9" x2="15" y1="9" y2="15"/></svg>İlçede İkamet Etmiyor';
                    wrap.style.display = 'block';
                    input.value = data.adres;
                } else {
                    badge.className = 'adres-ikamet-badge bulunamadi';
                    badge.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>Kayıtlı adres bulunamadı';
                    wrap.style.display = 'none';
                    input.value = '';
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';
                showToast('error', 'Bağlantı Hatası', 'Sunucuya bağlanılamadı. Lütfen tekrar deneyin.');
            });
    }

    function buildAcikAdresKpsLine(data) {
        var parcalar = [];
        if (data.mahalleAdi) parcalar.push(String(data.mahalleAdi).trim());
        var sokak = (data.caddesokakAdi || data.sokakAdi || data.caddeAdi || '').trim();
        if (sokak) parcalar.push(sokak);
        if (data.kapi !== undefined && data.kapi !== null && String(data.kapi).trim() !== '') parcalar.push('NO ' + String(data.kapi).trim());
        var daireIlce = [];
        if (data.daire !== undefined && data.daire !== null && String(data.daire).trim() !== '') daireIlce.push('DAİRE ' + String(data.daire).trim());
        if (data.ilceAdi) daireIlce.push(String(data.ilceAdi).trim());
        if (daireIlce.length) parcalar.push(daireIlce.join(' '));
        if (data.ilAdi) parcalar.push(String(data.ilAdi).trim());
        return parcalar.filter(Boolean).join(' ');
    }

    function applyAdresSorguToForm(data) {
        if (!data || !data.success) return;
        if (data.ilAdi) document.getElementById('il').value = String(data.ilAdi).trim();
        if (data.ilceAdi) document.getElementById('ilce').value = String(data.ilceAdi).trim();
        if (data.mahalleAdi) document.getElementById('mahalle').value = String(data.mahalleAdi).trim();
        var satir = buildAcikAdresKpsLine(data);
        if (satir) document.getElementById('acik_adres').value = satir;
    }

    // ── AJAX submit ───────────────────────────────────────────────────────────
    var form = document.getElementById('userForm');
    var submitBtn = document.getElementById('submitBtn');
    var submitOriginal = submitBtn.innerHTML;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin .8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Kaydediliyor...';
        document.getElementById('loadingOverlay').classList.add('visible');

        fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: new FormData(form)
        })
            .then(function(r) { return r.json().then(function(d) { return { status: r.status, data: d }; }); })
            .then(function(result) {
                if (result.status === 200 && result.data.success) {
                    showToast('success', 'Kullanıcı Oluşturuldu', result.data.message);
                    form.reset();
                    document.querySelectorAll('input[name=role]').forEach(function(r) { r.checked = r.value === 'personel'; });
                    [pwStrength, matchHint].forEach(function(el) { if(el) el.style.display = 'none'; });
                    segs.forEach(function(s) { s.className = 'pw-bar-seg'; });
                    updateSelectedCount();
                } else if (result.status === 422 && result.data.errors) {
                    var msgs = Object.values(result.data.errors).flat();
                    showToast('error', 'Doğrulama Hatası', msgs[0]);
                } else {
                    showToast('error', 'Hata', result.data.message || 'Kayıt sırasında bir hata oluştu.');
                }
            })
            .catch(function() { showToast('error', 'Bağlantı Hatası', 'Sunucuya ulaşılamadı.'); })
            .finally(function() {
                submitBtn.disabled = false;
                submitBtn.innerHTML = submitOriginal;
                document.getElementById('loadingOverlay').classList.remove('visible');
            });
    });
</script>
</body>
</html>
