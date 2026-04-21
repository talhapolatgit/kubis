<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#7a5c3c" />
    <title>Rezervasyon İşlemleri — Beyoğlu Kütüphane Sistemi</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700;900&family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --background:#f5f0e8;--foreground:#3d3226;--card:#faf8f3;
            --primary:#7a5c3c;--primary-foreground:#f5f0e8;
            --secondary:#ede8de;--muted:#ede8de;--muted-foreground:#7a7060;
            --destructive:#c53030;--success:#166534;
            --border:#d9d0c2;--ring:#7a5c3c;--radius:0.625rem;
            --sidebar:#3d3226;--sidebar-foreground:#e8e2d6;
            --sidebar-primary:#9b7b55;--sidebar-primary-foreground:#f5f0e8;
            --sidebar-accent:#524435;--sidebar-accent-foreground:#e8e2d6;
            --sidebar-border:#5a4a3a;
            --font-sans:'Source Sans 3',system-ui,sans-serif;
            --font-serif:'Merriweather',Georgia,serif;
        }
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:var(--font-sans);background:var(--background);color:var(--foreground);-webkit-font-smoothing:antialiased;line-height:1.5}
        input,select,button,textarea{font-family:inherit;font-size:inherit}
        .app-layout{display:flex;min-height:100vh}
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
        .content-area{flex:1;padding:24px;display:flex;flex-direction:column;gap:20px}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:9px 16px;border-radius:calc(var(--radius) - 2px);font-size:14px;font-weight:500;cursor:pointer;transition:background .15s,opacity .15s;border:none;text-decoration:none}
        .btn svg{width:16px;height:16px}
        .btn-primary{background:var(--primary);color:var(--primary-foreground)}
        .btn-primary:hover{opacity:.9}
        .btn-outline{background:transparent;color:var(--foreground);border:1px solid var(--border)}
        .btn-outline:hover{background:var(--muted)}
        .btn-sm{padding:5px 11px;font-size:13px}
        .page-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}
        .page-title{font-family:var(--font-serif);font-size:22px;font-weight:700}
        .page-subtitle{font-size:13px;color:var(--muted-foreground);margin-top:2px}
        .stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
        .stat-card{background:var(--card);border:1px solid rgba(217,208,194,.6);border-radius:var(--radius);padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,.04);cursor:pointer;transition:border-color .15s,box-shadow .15s;text-decoration:none;display:block}
        .stat-card:hover{border-color:var(--ring);box-shadow:0 2px 8px rgba(122,92,60,.12)}
        .stat-card.active-filter{border-color:var(--primary);box-shadow:0 0 0 2px rgba(122,92,60,.15)}
        .stat-label{font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--muted-foreground);margin-bottom:8px;display:flex;align-items:center;gap:6px}
        .stat-label svg{width:14px;height:14px}
        .stat-value{font-family:var(--font-serif);font-size:28px;font-weight:700;line-height:1}
        .stat-value.red{color:var(--destructive)}
        .stat-value.green{color:#16a34a}
        .stat-value.orange{color:#b45309}
        .stat-sub{font-size:12px;color:var(--muted-foreground);margin-top:4px}
        .toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
        .search-wrap{position:relative;flex:1;max-width:340px}
        .search-wrap svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--muted-foreground);pointer-events:none}
        .search-input{width:100%;padding:8px 12px 8px 34px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);color:var(--foreground);font-size:14px;outline:none;transition:border-color .15s,box-shadow .15s}
        .search-input:focus{border-color:var(--ring);box-shadow:0 0 0 2px rgba(122,92,60,.15)}
        .filter-select{padding:8px 32px 8px 12px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);color:var(--foreground);font-size:14px;outline:none;cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237a7060' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;transition:border-color .15s}
        .filter-select:focus{border-color:var(--ring);box-shadow:0 0 0 2px rgba(122,92,60,.15)}
        .table-veil{position:absolute;inset:0;background:rgba(250,248,243,.75);display:flex;align-items:center;justify-content:center;z-index:10;border-radius:var(--radius);opacity:0;visibility:hidden;transition:opacity .18s,visibility .18s;pointer-events:none}
        .table-veil.visible{opacity:1;visibility:visible;pointer-events:all}
        .veil-spinner{width:32px;height:32px;border:3px solid var(--border);border-top-color:var(--primary);border-radius:50%;animation:spin .7s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}}
        .table-card{background:var(--card);border:1px solid rgba(217,208,194,.6);border-radius:var(--radius);overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.04)}
        .table-wrap{overflow-x:auto}
        table{width:100%;border-collapse:collapse}
        thead{background:var(--secondary)}
        th{padding:11px 16px;text-align:left;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--muted-foreground);white-space:nowrap;border-bottom:1px solid var(--border)}
        td{padding:13px 16px;font-size:14px;border-bottom:1px solid rgba(217,208,194,.4);vertical-align:middle}
        tr:last-child td{border-bottom:none}
        tbody tr{transition:background .12s}
        tbody tr:hover{background:rgba(237,232,222,.5)}
        .member-cell{display:flex;align-items:center;gap:10px}
        .member-av{width:32px;height:32px;border-radius:50%;background:var(--sidebar-accent);color:var(--sidebar-foreground);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0}
        .member-name{font-weight:600;font-size:13px}
        .member-tc{font-size:12px;color:var(--muted-foreground)}
        .book-cell{display:flex;align-items:center;gap:10px}
        .book-cover{width:32px;height:42px;border-radius:3px;object-fit:cover;flex-shrink:0;background:var(--secondary)}
        .book-cover-placeholder{width:32px;height:42px;border-radius:3px;background:var(--secondary);display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .book-cover-placeholder svg{width:16px;height:16px;color:var(--muted-foreground)}
        .book-title{font-weight:500;font-size:13px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .book-isbn{font-size:12px;color:var(--muted-foreground)}
        .date-cell{font-size:13px}
        .date-main{font-weight:500}
        .badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600}
        .badge-dot{width:6px;height:6px;border-radius:50%;flex-shrink:0}
        .badge-aktif{background:rgba(37,99,235,.08);color:#1e40af}.badge-aktif .badge-dot{background:#3b82f6}
        .badge-iade{background:rgba(34,197,94,.1);color:#166534}.badge-iade .badge-dot{background:#22c55e}
        .badge-warn{background:rgba(245,158,11,.12);color:#b45309}.badge-warn .badge-dot{background:#f59e0b}
        .badge-muted{background:rgba(107,114,128,.12);color:#374151}.badge-muted .badge-dot{background:#6b7280}
        .table-footer{padding:12px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px;border-top:1px solid var(--border);font-size:13px;color:var(--muted-foreground);flex-wrap:wrap}
        .tf-info{display:flex;align-items:center;gap:12px}
        .per-page-wrap{display:flex;align-items:center;gap:6px;font-size:13px}
        .per-page-select{padding:4px 28px 4px 8px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);color:var(--foreground);font-size:13px;outline:none;cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%237a7060' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 8px center}
        .pagination{display:flex;align-items:center;gap:4px;flex-wrap:wrap}
        .page-btn{display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:32px;padding:0 6px;border-radius:calc(var(--radius) - 2px);font-size:13px;font-weight:500;cursor:pointer;border:1px solid var(--border);background:var(--card);color:var(--foreground);transition:background .12s;user-select:none}
        .page-btn:hover:not(.disabled):not(.active){background:var(--muted)}
        .page-btn.active{background:var(--primary);color:var(--primary-foreground);border-color:var(--primary);cursor:default}
        .page-btn.disabled{opacity:.38;cursor:default;pointer-events:none}
        .page-btn svg{width:13px;height:13px}
        .page-ellipsis{padding:0 4px;color:var(--muted-foreground);font-size:13px}
        .empty-state{padding:48px 24px;text-align:center}
        .empty-icon{width:56px;height:56px;border-radius:50%;background:var(--secondary);display:flex;align-items:center;justify-content:center;margin:0 auto 16px}
        .empty-icon svg{width:26px;height:26px;color:var(--muted-foreground)}
        .empty-title{font-size:16px;font-weight:600;margin-bottom:6px}
        .empty-desc{font-size:14px;color:var(--muted-foreground)}
        .toast-container{position:fixed;top:20px;right:20px;z-index:8000;display:flex;flex-direction:column;gap:10px}
        .toast{padding:14px 18px;border-radius:var(--radius);font-size:14px;font-weight:500;min-width:280px;box-shadow:0 4px 16px rgba(0,0,0,.12);border:1px solid transparent;animation:toast-in .3s ease}
        .toast.success{background:#f0fdf4;border-color:#bbf7d0;color:#166534}
        .toast.error{background:#fef2f2;border-color:#fecaca;color:#991b1b}
        .toast-desc{font-size:13px;opacity:.8;margin-top:2px}
        @keyframes toast-in{from{opacity:0;transform:translateX(20px)}to{opacity:1;transform:translateX(0)}}
        @keyframes toast-out{from{opacity:1;transform:translateX(0)}to{opacity:0;transform:translateX(20px)}}
        .sidebar-overlay{display:none;position:fixed;inset:0;z-index:39;background:rgba(0,0,0,.4)}
        /* Rezervasyon modal — backdrop-filter ve scale animasyonu GPU'da fareyle titremeye yol açabiliyor; kaldırıldı */
        .rezerve-modal-backdrop{position:fixed;inset:0;z-index:5000;min-height:100vh;box-sizing:border-box;background:rgba(61,50,38,.55);display:flex;align-items:flex-start;justify-content:center;padding:24px 16px;overflow-y:scroll;-webkit-overflow-scrolling:touch;overscroll-behavior:contain;opacity:0;visibility:hidden;transition:opacity .15s ease,visibility .15s ease}
        .rezerve-modal-backdrop.visible{opacity:1;visibility:visible}
        .rezerve-modal-box{background:var(--card);border:1px solid var(--border);border-radius:16px;max-width:640px;width:100%;display:flex;flex-direction:column;box-shadow:0 24px 64px rgba(0,0,0,.22);overflow:visible;flex-shrink:0;margin:0 auto 32px;isolation:isolate}
        .rezerve-modal-head{padding:18px 22px 14px;display:flex;align-items:flex-start;justify-content:space-between;gap:12px;border-bottom:1px solid var(--border);flex-shrink:0}
        .rezerve-modal-title{font-family:var(--font-serif);font-size:18px;font-weight:700}
        .rezerve-modal-sub{font-size:13px;color:var(--muted-foreground);margin-top:4px}
        .rezerve-modal-close{width:36px;height:36px;border-radius:8px;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--muted-foreground);flex-shrink:0;transition:background .15s}
        .rezerve-modal-close:hover{background:var(--muted);color:var(--foreground)}
        .rezerve-modal-close svg{width:18px;height:18px}
        .rezerve-modal-body{padding:18px 22px 22px;overflow:visible;position:relative}
        .form-grid-rez{display:grid;grid-template-columns:repeat(2,1fr);gap:14px 18px}
        .form-field-rz{display:flex;flex-direction:column;gap:6px}
        .form-label-rz{font-size:14px;font-weight:500}
        .form-label-rz .req{color:var(--destructive)}
        .form-input-rz{width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);font-size:14px;outline:none}
        .form-input-rz:focus{border-color:var(--ring);box-shadow:0 0 0 2px rgba(122,92,60,.15)}
        .autocomplete-wrap{position:relative}
        .autocomplete-dropdown{position:absolute;top:calc(100% + 4px);left:0;right:0;background:var(--card);border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:120;max-height:260px;overflow-y:auto;display:none}
        .autocomplete-dropdown.open{display:block}
        .ac-item{padding:10px 14px;cursor:pointer;transition:background .1s;display:flex;align-items:flex-start;gap:10px}
        .ac-item:hover,.ac-item.highlighted{background:var(--secondary)}
        .ac-item-avatar{width:32px;height:32px;border-radius:50%;background:var(--sidebar-accent);color:var(--sidebar-foreground);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0}
        .ac-item-cover{width:26px;height:36px;border-radius:3px;object-fit:cover;flex-shrink:0;background:var(--secondary)}
        .ac-item-cover-ph{width:26px;height:36px;border-radius:3px;background:var(--secondary);display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .ac-item-cover-ph svg{width:13px;height:13px;color:var(--muted-foreground)}
        .ac-item-body{flex:1;min-width:0}
        .ac-item-name{font-size:14px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .ac-item-meta{font-size:12px;color:var(--muted-foreground);margin-top:1px}
        .ac-item-badge{display:inline-flex;font-size:11px;font-weight:600;padding:1px 7px;border-radius:999px;margin-left:6px}
        .ac-item-badge.warning{background:rgba(245,158,11,.12);color:#b45309}
        .ac-item-badge.danger{background:rgba(197,48,48,.1);color:#991b1b}
        .ac-empty,.ac-loading{padding:14px;text-align:center;font-size:13px;color:var(--muted-foreground)}
        .selected-card{border:1.5px solid var(--primary);border-radius:calc(var(--radius) - 2px);background:rgba(122,92,60,.04);padding:12px 14px;display:flex;align-items:center;gap:12px;margin-top:8px}
        .selected-card-avatar{width:36px;height:36px;border-radius:50%;background:var(--sidebar-accent);color:var(--sidebar-foreground);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0}
        .selected-card-cover{width:32px;height:44px;border-radius:3px;object-fit:cover;flex-shrink:0}
        .selected-card-info{flex:1;min-width:0}
        .selected-card-name{font-size:14px;font-weight:600}
        .selected-card-meta{font-size:12px;color:var(--muted-foreground);margin-top:2px}
        .selected-card-clear{width:26px;height:26px;border-radius:6px;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--muted-foreground)}
        .selected-card-clear:hover{background:var(--muted);color:var(--foreground)}
        .selected-card-clear svg{width:14px;height:14px}
        .form-actions-rez{display:flex;justify-content:flex-end;gap:10px;margin-top:6px;grid-column:1/-1}
        @media(max-width:900px){.stats-row{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:768px){
            .main-content{margin-left:0}
            .sidebar{transform:translateX(-260px)}
            .sidebar.open{transform:translateX(0)}
            .sidebar-overlay.visible{display:block}
            .stats-row{grid-template-columns:repeat(2,1fr)}
            .form-grid-rez{grid-template-columns:1fr}
            .page-header{flex-direction:column;align-items:stretch;gap:12px}
            .page-header .page-header-actions{width:100%}
            .page-header .page-header-actions .btn{width:100%;justify-content:center}
        }
    </style>
</head>
<body>
<div class="app-layout">
    @include('partials.sidebar')
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="main-content" id="mainContent">
        <div class="top-header">
            <button type="button" class="sidebar-trigger" id="sidebarToggle">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
            </button>
            <div class="header-separator"></div>
            <nav class="breadcrumb">
                <a href="{{ route('katalog.index') }}" class="breadcrumb-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Ana Sayfa
                </a>
                <span class="breadcrumb-sep">›</span>
                <span class="breadcrumb-current">Rezervasyon İşlemleri</span>
            </nav>
        </div>

        <div class="content-area">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Rezervasyon İşlemleri</h1>
                    <p class="page-subtitle">Rezervasyon oluşturma ve ödünç vermeye yönlendirme</p>
                </div>
                @if($canManage)
                <div class="page-header-actions" style="display:flex;align-items:flex-start;flex-shrink:0;">
                    <button type="button" class="btn btn-primary" id="btnOpenRezerveModal">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4"/><path d="M12 18v4"/><path d="m4.93 4.93 2.83 2.83"/><path d="m16.24 16.24 2.83 2.83"/><path d="M2 12h4"/><path d="M18 12h4"/><path d="m4.93 19.07 2.83-2.83"/><path d="m16.24 7.76 2.83-2.83"/></svg>
                        Yeni Rezervasyon Oluştur
                    </button>
                </div>
                @endif
            </div>

            <div class="stats-row">
                <a href="#" class="stat-card {{ $filtre === 'aktif' ? 'active-filter' : '' }}" data-filtre="aktif">
                    <div class="stat-label">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Aktif
                    </div>
                    <div class="stat-value">{{ $stats['aktif'] }}</div>
                    <div class="stat-sub">geçerli rezervasyon</div>
                </a>
                <a href="#" class="stat-card {{ $filtre === 'tamamlanan' ? 'active-filter' : '' }}" data-filtre="tamamlanan">
                    <div class="stat-label" style="color:#166534">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                        Ödünç verildi
                    </div>
                    <div class="stat-value green">{{ $stats['tamamlanan'] }}</div>
                    <div class="stat-sub">tamamlanan</div>
                </a>
                <a href="#" class="stat-card {{ $filtre === 'suresi_doldu' ? 'active-filter' : '' }}" data-filtre="suresi_doldu">
                    <div class="stat-label" style="color:var(--destructive)">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Süresi doldu
                    </div>
                    <div class="stat-value red">{{ $stats['suresi_doldu'] }}</div>
                    <div class="stat-sub">iptal edilmemiş</div>
                </a>
                <a href="#" class="stat-card {{ $filtre === 'hepsi' ? 'active-filter' : '' }}" data-filtre="hepsi">
                    <div class="stat-label">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h7v7H3z"/><path d="M14 3h7v7h-7z"/><path d="M14 14h7v7h-7z"/><path d="M3 14h7v7H3z"/></svg>
                        Toplam
                    </div>
                    <div class="stat-value">{{ $stats['toplam'] }}</div>
                    <div class="stat-sub">tüm kayıtlar</div>
                </a>
            </div>

            <div class="toolbar">
                <div class="search-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input type="text" id="searchInput" class="search-input" placeholder="Üye adı, TC, kitap adı, ISBN…" autocomplete="off" />
                </div>
                <select id="filtreSelect" class="filter-select">
                    <option value="aktif">Aktif rezervasyonlar</option>
                    <option value="tamamlanan">Ödünç verilmiş</option>
                    <option value="iptal">İptal</option>
                    <option value="suresi_doldu">Süresi dolmuş</option>
                    <option value="hepsi">Tümü</option>
                </select>
            </div>

            <div class="table-card" style="position:relative;">
                <div class="table-veil" id="tableVeil"><div class="veil-spinner"></div></div>
                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Üye</th>
                            <th>Kitap</th>
                            <th>Başlangıç</th>
                            <th>Bitiş</th>
                            <th>Durum</th>
                            <th>Kütüphane</th>
                            <th></th>
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
                                <option value="10">10</option>
                                <option value="20" selected>20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                    <div class="pagination" id="pagination"></div>
                </div>
            </div>
        </div>
    </main>
</div>

@if($canManage)
<div class="rezerve-modal-backdrop" id="rezerveModalBackdrop" role="dialog" aria-modal="true" aria-labelledby="rezerveModalTitle">
    <div class="rezerve-modal-box">
        <div class="rezerve-modal-head">
            <div>
                <div id="rezerveModalTitle" class="rezerve-modal-title">Rezervasyon oluşturma</div>
                <p class="rezerve-modal-sub">Üye ve raftaki kitabı seçerek rezervasyon kaydı oluşturun (süre: 24 saat).</p>
            </div>
            <button type="button" class="rezerve-modal-close" id="btnCloseRezerveModal" aria-label="Pencereyi kapat">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <div class="rezerve-modal-body">
            <div class="form-grid-rez">
                <div class="form-field-rz">
                    <label class="form-label-rz" for="uyeSearchRez">Üye <span class="req">*</span></label>
                    <input type="hidden" id="uyeIdRez" value="" />
                    <div id="uyeSearchFieldRez">
                        <div class="autocomplete-wrap">
                            <input type="text" id="uyeSearchRez" class="form-input-rz" placeholder="Ad, soyad veya TC ile ara…" autocomplete="off" />
                            <div id="uyeDropdownRez" class="autocomplete-dropdown"></div>
                        </div>
                    </div>
                    <div id="uyeCardRez" class="selected-card" style="display:none;">
                        <div class="selected-card-avatar" id="uyeCardAvRez"></div>
                        <div class="selected-card-info">
                            <div class="selected-card-name" id="uyeCardNameRez"></div>
                            <div class="selected-card-meta" id="uyeCardMetaRez"></div>
                        </div>
                        <button type="button" class="selected-card-clear" onclick="clearUyeRez()" title="Kaldır">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                </div>
                <div class="form-field-rz">
                    <label class="form-label-rz" for="kitapSearchRez">Kitap <span class="req">*</span></label>
                    <input type="hidden" id="katalogIdRez" value="" />
                    <div id="kitapSearchFieldRez">
                        <div class="autocomplete-wrap">
                            <input type="text" id="kitapSearchRez" class="form-input-rz" placeholder="Eser adı, ISBN veya demirbaş…" autocomplete="off" />
                            <div id="kitapDropdownRez" class="autocomplete-dropdown"></div>
                        </div>
                    </div>
                    <div id="kitapCardRez" class="selected-card" style="display:none;">
                        <div id="kitapCardCoverWrapRez"></div>
                        <div class="selected-card-info">
                            <div class="selected-card-name" id="kitapCardNameRez"></div>
                            <div class="selected-card-meta" id="kitapCardMetaRez"></div>
                        </div>
                        <button type="button" class="selected-card-clear" onclick="clearKitapRez()" title="Kaldır">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                </div>
                <div class="form-actions-rez">
                    <button type="button" class="btn btn-outline" id="btnRezerveModalIptal">Vazgeç</button>
                    <button type="button" class="btn btn-primary" id="btnRezerveOlustur">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4"/><path d="M12 18v4"/><circle cx="12" cy="12" r="3"/></svg>
                        Rezervasyon oluştur
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="toast-container" id="toastContainer"></div>

<script>
(function() {
    var sidebar = document.getElementById('sidebar');
    var mainContent = document.getElementById('mainContent');
    var isMobile = window.innerWidth <= 768;
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        if (isMobile) { sidebar.classList.toggle('open'); document.getElementById('sidebarOverlay').classList.toggle('visible'); }
        else { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); }
    });
    document.getElementById('sidebarOverlay').addEventListener('click', function() { sidebar.classList.remove('open'); this.classList.remove('visible'); });
    window.addEventListener('resize', function() { isMobile = window.innerWidth <= 768; });

    function showToast(type, title, desc) {
        var c = document.getElementById('toastContainer');
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<div>' + title + '</div>' + (desc ? '<div class="toast-desc">' + desc + '</div>' : '');
        c.appendChild(t);
        setTimeout(function() { t.style.animation = 'toast-out .3s ease forwards'; setTimeout(function() { if(t.parentNode) t.parentNode.removeChild(t); }, 300); }, 4500);
    }
    @if(session('success')) showToast('success', @json(session('success'))); @endif
    @if(session('error')) showToast('error', @json(session('error'))); @endif

    function esc(s) { var d = document.createElement('div'); d.appendChild(document.createTextNode(s||'')); return d.innerHTML; }

    var bookIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>';

    function durumBadge(lbl) {
        if (lbl === 'Aktif') return '<span class="badge badge-aktif"><span class="badge-dot"></span> Aktif</span>';
        if (lbl === 'Ödünç verildi') return '<span class="badge badge-iade"><span class="badge-dot"></span> Ödünç verildi</span>';
        if (lbl === 'İptal') return '<span class="badge badge-muted"><span class="badge-dot"></span> İptal</span>';
        if (lbl === 'Süresi doldu') return '<span class="badge badge-warn"><span class="badge-dot"></span> Süresi doldu</span>';
        return '<span class="badge badge-muted"><span class="badge-dot"></span> ' + esc(lbl) + '</span>';
    }

    function buildRow(i) {
        var uyeCell = '<div class="member-cell"><div class="member-av">' + esc(i.uye_initials) + '</div><div>' +
            '<div class="member-name">' + esc(i.uye_ad) + '</div>' +
            '<div class="member-tc">' + esc(i.uye_tc) + '</div></div></div>';
        var coverHtml = i.kitap_kapak
            ? '<img src="' + esc(i.kitap_kapak) + '" alt="" class="book-cover" />'
            : '<div class="book-cover-placeholder">' + bookIcon + '</div>';
        var bookCell = '<div class="book-cell">' + coverHtml + '<div>' +
            '<div class="book-title" title="' + esc(i.kitap) + '">' + esc(i.kitap) + '</div>' +
            '<div class="book-isbn">Demirbaş: ' + esc(i.kitap_demir || '—') + ' · ISBN: ' + esc(i.kitap_isbn || '—') + '</div></div></div>';
        var bas = '<div class="date-cell"><div class="date-main">' + esc(i.rezerve_baslangic) + '</div></div>';
        var bit = '<div class="date-cell"><div class="date-main">' + esc(i.rezerve_bitis) + '</div></div>';
        var act = '';
        if (i.odunc_yapilabilir && i.odunc_new_url) {
            act = '<a href="' + String(i.odunc_new_url).replace(/"/g, '&quot;') + '" class="btn btn-primary btn-sm">Ödünç</a>';
        } else {
            act = '<span style="font-size:12px;color:var(--muted-foreground);">—</span>';
        }
        return '<tr><td>' + uyeCell + '</td><td>' + bookCell + '</td><td>' + bas + '</td><td>' + bit + '</td><td>' + durumBadge(i.durum_etiket) + '</td><td style="font-size:12px;color:var(--muted-foreground);max-width:120px;">' + esc(i.kutuphane || '—') + '</td><td style="text-align:right;white-space:nowrap;">' + act + '</td></tr>';
    }

    var state = {
        search: '',
        filtre: @json($filtre),
        per_page: 20,
        page: 1
    };
    var fetchTimer = null;
    var activeXhr = null;

    document.getElementById('filtreSelect').value = state.filtre;

    function buildPagination(meta) {
        var container = document.getElementById('pagination');
        if (meta.last_page <= 1) { container.innerHTML = ''; return; }
        var cur = meta.current_page, last = meta.last_page;
        var html = '';
        html += '<button class="page-btn ' + (cur<=1?'disabled':'') + '" onclick="window.__rezGoPage(' + (cur-1) + ')"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="m15 18-6-6 6-6"/></svg></button>';
        var pages = [];
        if (last <= 7) { for (var i=1;i<=last;i++) pages.push(i); }
        else {
            pages.push(1);
            if (cur>3) pages.push('…');
            for (var j=Math.max(2,cur-1);j<=Math.min(last-1,cur+1);j++) pages.push(j);
            if (cur<last-2) pages.push('…');
            pages.push(last);
        }
        pages.forEach(function(p) {
            if (p==='…') html += '<span class="page-ellipsis">…</span>';
            else html += '<button class="page-btn' + (p===cur?' active':'') + '" onclick="window.__rezGoPage(' + p + ')">' + p + '</button>';
        });
        html += '<button class="page-btn ' + (cur>=last?'disabled':'') + '" onclick="window.__rezGoPage(' + (cur+1) + ')"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="m9 18 6-6-6-6"/></svg></button>';
        container.innerHTML = html;
    }
    window.__rezGoPage = function(p) { if (p<1) return; state.page = p; fetchTable(); };

    function fetchTable(resetPage) {
        if (resetPage) state.page = 1;
        if (activeXhr) activeXhr.abort();
        activeXhr = new AbortController();
        var ctrl = activeXhr;
        document.getElementById('tableVeil').classList.add('visible');
        var params = new URLSearchParams({
            search: state.search,
            filtre: state.filtre,
            per_page: state.per_page,
            page: state.page
        });
        fetch('{{ route('rezerve.tableData') }}?' + params.toString(), {
            signal: ctrl.signal,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function(res) { if (!res.ok) throw new Error('HTTP ' + res.status); return res.json(); })
            .then(function(result) {
                if (ctrl.signal.aborted) return;
                activeXhr = null;
                document.getElementById('tableVeil').classList.remove('visible');
                if (!result.success) { showToast('error', 'Hata', 'Veriler yüklenemedi.'); return; }
                var rows = Array.isArray(result.data) ? result.data : [];
                var tbody = document.getElementById('tableBody');
                if (rows.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><div class="empty-icon">' + bookIcon + '</div><p class="empty-title">Kayıt yok</p><p class="empty-desc">Arama veya filtreyi değiştirin.</p></div></td></tr>';
                } else {
                    tbody.innerHTML = rows.map(buildRow).join('');
                }
                var m = result.meta;
                document.getElementById('rangeInfo').textContent = m.from + '–' + m.to + ' / ' + m.total + ' kayıt';
                buildPagination(m);
            })
            .catch(function(err) {
                if (err && err.name === 'AbortError') return;
                document.getElementById('tableVeil').classList.remove('visible');
                showToast('error', 'Bağlantı hatası', 'Tablo yüklenemedi.');
            });
    }

    function debounce(fn) {
        clearTimeout(fetchTimer);
        fetchTimer = setTimeout(fn, 400);
    }

    document.getElementById('searchInput').addEventListener('input', function() {
        state.search = this.value.trim();
        debounce(function() { fetchTable(true); });
    });
    document.getElementById('filtreSelect').addEventListener('change', function() {
        clearTimeout(fetchTimer);
        state.filtre = this.value;
        document.querySelectorAll('.stat-card[data-filtre]').forEach(function(c) {
            c.classList.toggle('active-filter', c.getAttribute('data-filtre') === state.filtre);
        });
        fetchTable(true);
    });
    document.getElementById('perPageSelect').addEventListener('change', function() {
        state.per_page = parseInt(this.value, 10);
        fetchTable(true);
    });

    document.querySelectorAll('.stat-card[data-filtre]').forEach(function(card) {
        card.addEventListener('click', function(e) {
            e.preventDefault();
            var f = this.getAttribute('data-filtre');
            state.filtre = f;
            document.getElementById('filtreSelect').value = f;
            document.querySelectorAll('.stat-card[data-filtre]').forEach(function(c) { c.classList.remove('active-filter'); });
            this.classList.add('active-filter');
            fetchTable(true);
        });
    });

    fetchTable();

    @if($canManage)
    var acTimers = {};
    function setupAutocomplete(inputId, dropdownId, fetchUrl, onSelect, renderItem, extraQueryFn) {
        var inp = document.getElementById(inputId);
        var dd = document.getElementById(dropdownId);
        var hi = -1;
        inp.addEventListener('input', function() {
            var q = this.value.trim();
            clearTimeout(acTimers[inputId]);
            if (q.length < 2) { dd.classList.remove('open'); dd.innerHTML = ''; return; }
            acTimers[inputId] = setTimeout(function() {
                dd.innerHTML = '<div class="ac-loading">Aranıyor…</div>';
                dd.classList.add('open');
                var extra = (typeof extraQueryFn === 'function') ? extraQueryFn() : '';
                fetch(fetchUrl + '?q=' + encodeURIComponent(q) + extra, { headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } })
                    .then(function(r) { return r.json(); })
                    .then(function(items) {
                        dd.innerHTML = '';
                        hi = -1;
                        if (!items.length) { dd.innerHTML = '<div class="ac-empty">Sonuç yok</div>'; return; }
                        items.forEach(function(item) {
                            var el = document.createElement('div');
                            el.className = 'ac-item';
                            el.innerHTML = renderItem(item);
                            el.addEventListener('mousedown', function(e) { e.preventDefault(); onSelect(item); dd.classList.remove('open'); inp.value = ''; });
                            dd.appendChild(el);
                        });
                    });
            }, 280);
        });
        inp.addEventListener('keydown', function(e) {
            var items = dd.querySelectorAll('.ac-item');
            if (e.key === 'ArrowDown') { e.preventDefault(); if (hi < items.length - 1) { hi++; items.forEach(function(el, i) { el.classList.toggle('highlighted', i === hi); }); } }
            else if (e.key === 'ArrowUp') { e.preventDefault(); if (hi > 0) { hi--; items.forEach(function(el, i) { el.classList.toggle('highlighted', i === hi); }); } }
            else if (e.key === 'Escape') {
                if (dd.classList.contains('open')) {
                    dd.classList.remove('open');
                    e.stopPropagation();
                    e.preventDefault();
                }
            }
        });
        document.addEventListener('click', function(e) {
            if (!inp.contains(e.target) && !dd.contains(e.target)) dd.classList.remove('open');
        });
    }

    var selectedUyeRez = null;
    var selectedKitapRez = null;

    setupAutocomplete('uyeSearchRez', 'uyeDropdownRez', '{{ route('odunc.uyeAra') }}',
        function(item) { selectUyeRez(item); },
        function(item) {
            var badge = item.aktif_odunc > 0 ? '<span class="ac-item-badge warning">' + item.aktif_odunc + ' aktif ödünç</span>' : '';
            var initials = item.label.split(' ').map(function(w) { return w[0]; }).join('').slice(0,2).toUpperCase();
            return '<div class="ac-item-avatar">' + initials + '</div><div class="ac-item-body"><div class="ac-item-name">' + esc(item.label) + badge + '</div><div class="ac-item-meta">TC: ' + esc(item.tc) + ' · ' + esc(item.telefon) + '</div></div>';
        }
    );

    function selectUyeRez(item) {
        selectedUyeRez = item;
        document.getElementById('uyeIdRez').value = item.id;
        var initials = item.label.split(' ').map(function(w) { return w[0]; }).join('').slice(0,2).toUpperCase();
        document.getElementById('uyeCardAvRez').textContent = initials;
        document.getElementById('uyeCardNameRez').textContent = item.label;
        document.getElementById('uyeCardMetaRez').textContent = 'TC: ' + item.tc + ' · ' + item.telefon;
        document.getElementById('uyeSearchFieldRez').style.display = 'none';
        document.getElementById('uyeCardRez').style.display = 'flex';
    }
    window.clearUyeRez = function() {
        selectedUyeRez = null;
        document.getElementById('uyeIdRez').value = '';
        document.getElementById('uyeSearchFieldRez').style.display = 'block';
        document.getElementById('uyeCardRez').style.display = 'none';
        document.getElementById('uyeSearchRez').value = '';
    };

    setupAutocomplete('kitapSearchRez', 'kitapDropdownRez', '{{ route('odunc.kitapAra') }}',
        function(item) { selectKitapRez(item); },
        function(item) {
            var coverHtml = item.kapak ? '<img src="' + esc(item.kapak) + '" class="ac-item-cover" />' : '<div class="ac-item-cover-ph">' + bookIcon.replace('width="24"','width="13"') + '</div>';
            var badges = '';
            if (item.odunc_ta) badges += '<span class="ac-item-badge danger">Ödünçte</span>';
            else if (item.oduncVerilemez === 'true') badges += '<span class="ac-item-badge danger">Ödünç verilemez</span>';
            else if (item.kunyeDurum === 'Rezerve' && item.rezerve_aktif_bu_uye) badges += '<span class="ac-item-badge warning">Rezerve · bu üye</span>';
            else if (item.kunyeDurum && item.kunyeDurum !== 'Rafta') badges += '<span class="ac-item-badge danger">' + esc(item.kunyeDurum) + '</span>';
            return coverHtml + '<div class="ac-item-body"><div class="ac-item-name">' + esc(item.label) + badges + '</div><div class="ac-item-meta">' + esc(item.yazar || '') + (item.demir ? ' · Demirbaş: ' + esc(item.demir) : '') + '</div></div>';
        },
        function() {
            var uid = document.getElementById('uyeIdRez').value;
            return uid ? '&uye_id=' + encodeURIComponent(uid) : '';
        }
    );

    function selectKitapRez(item) {
        selectedKitapRez = item;
        document.getElementById('katalogIdRez').value = item.id;
        var wrap = document.getElementById('kitapCardCoverWrapRez');
        if (item.kapak) wrap.innerHTML = '<img src="' + esc(item.kapak) + '" class="selected-card-cover" />';
        else wrap.innerHTML = '';
        document.getElementById('kitapCardNameRez').textContent = item.label;
        document.getElementById('kitapCardMetaRez').textContent = (item.yazar || '') + (item.demir ? ' · Demirbaş: ' + item.demir : '') + (item.isbn ? ' · ISBN: ' + item.isbn : '');
        document.getElementById('kitapSearchFieldRez').style.display = 'none';
        document.getElementById('kitapCardRez').style.display = 'flex';
    }
    window.clearKitapRez = function() {
        selectedKitapRez = null;
        document.getElementById('katalogIdRez').value = '';
        document.getElementById('kitapSearchFieldRez').style.display = 'block';
        document.getElementById('kitapCardRez').style.display = 'none';
        document.getElementById('kitapSearchRez').value = '';
        document.getElementById('kitapCardCoverWrapRez').innerHTML = '';
    };

    function lockBodyScroll() {
        var gap = window.innerWidth - document.documentElement.clientWidth;
        if (gap > 0) document.body.style.paddingRight = gap + 'px';
        document.body.style.overflow = 'hidden';
    }
    function unlockBodyScroll() {
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }

    function openRezerveModal() {
        clearUyeRez();
        clearKitapRez();
        document.getElementById('rezerveModalBackdrop').classList.add('visible');
        lockBodyScroll();
        setTimeout(function() {
            var el = document.getElementById('uyeSearchRez');
            if (el) el.focus();
        }, 80);
    }
    function closeRezerveModal() {
        document.getElementById('rezerveModalBackdrop').classList.remove('visible');
        unlockBodyScroll();
        var uyeDd = document.getElementById('uyeDropdownRez');
        var kitapDd = document.getElementById('kitapDropdownRez');
        if (uyeDd) { uyeDd.classList.remove('open'); uyeDd.innerHTML = ''; }
        if (kitapDd) { kitapDd.classList.remove('open'); kitapDd.innerHTML = ''; }
    }
    document.getElementById('btnOpenRezerveModal').addEventListener('click', openRezerveModal);
    document.getElementById('btnCloseRezerveModal').addEventListener('click', closeRezerveModal);
    document.getElementById('btnRezerveModalIptal').addEventListener('click', closeRezerveModal);
    document.getElementById('rezerveModalBackdrop').addEventListener('click', function(e) {
        if (e.target === this) closeRezerveModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key !== 'Escape') return;
        var b = document.getElementById('rezerveModalBackdrop');
        if (!b || !b.classList.contains('visible')) return;
        var uyeDd = document.getElementById('uyeDropdownRez');
        var kitapDd = document.getElementById('kitapDropdownRez');
        if (uyeDd && uyeDd.classList.contains('open')) {
            uyeDd.classList.remove('open');
            e.preventDefault();
            return;
        }
        if (kitapDd && kitapDd.classList.contains('open')) {
            kitapDd.classList.remove('open');
            e.preventDefault();
            return;
        }
        e.preventDefault();
        closeRezerveModal();
    });

    document.getElementById('btnRezerveOlustur').addEventListener('click', function() {
        var uid = document.getElementById('uyeIdRez').value;
        var kid = document.getElementById('katalogIdRez').value;
        if (!uid || !kid) { showToast('error', 'Eksik bilgi', 'Üye ve kitap seçin.'); return; }
        var btn = this;
        btn.disabled = true;
        fetch('{{ route('rezerve.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ uye_id: parseInt(uid, 10), katalog_id: parseInt(kid, 10) })
        })
            .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, status: r.status, body: j }; }); })
            .then(function(res) {
                btn.disabled = false;
                if (res.ok && res.body && res.body.success) {
                    showToast('success', res.body.message || 'Kayıt oluşturuldu.');
                    clearUyeRez();
                    clearKitapRez();
                    closeRezerveModal();
                    fetchTable(true);
                    return;
                }
                var msg = (res.body && res.body.message) ? res.body.message : 'İşlem başarısız.';
                if (res.body && res.body.errors) {
                    var vals = Object.values(res.body.errors);
                    if (vals.length && Array.isArray(vals[0])) msg = vals[0][0];
                }
                showToast('error', 'Rezervasyon', msg);
            })
            .catch(function() { btn.disabled = false; showToast('error', 'Hata', 'Sunucuya ulaşılamadı.'); });
    });
    @endif
})();
</script>
</body>
</html>
