<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#7a5c3c" />
    <title>Yeni Ödünç Ver — Beyoğlu Kütüphane Sistemi</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700;900&family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        :root{--background:#f5f0e8;--foreground:#3d3226;--card:#faf8f3;--primary:#7a5c3c;--primary-foreground:#f5f0e8;--secondary:#ede8de;--muted:#ede8de;--muted-foreground:#7a7060;--destructive:#c53030;--border:#d9d0c2;--ring:#7a5c3c;--radius:0.625rem;--sidebar:#3d3226;--sidebar-foreground:#e8e2d6;--sidebar-primary:#9b7b55;--sidebar-primary-foreground:#f5f0e8;--sidebar-accent:#524435;--sidebar-accent-foreground:#e8e2d6;--sidebar-border:#5a4a3a;--font-sans:'Source Sans 3',system-ui,sans-serif;--font-serif:'Merriweather',Georgia,serif}
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:var(--font-sans);background:var(--background);color:var(--foreground);-webkit-font-smoothing:antialiased;line-height:1.5}
        input,select,button,textarea{font-family:inherit;font-size:inherit}
        .app-layout{display:flex;min-height:100vh}
        /* Sidebar */
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
        /* Main */
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
        /* Form Card */
        .form-card{border:1px solid rgba(217,208,194,.6);border-radius:var(--radius);background:var(--card);box-shadow:0 1px 3px rgba(0,0,0,.04);max-width:680px}
        .form-card-header{padding:24px 24px 16px}
        .form-card-title{display:flex;align-items:center;gap:10px;font-family:var(--font-serif);font-size:20px;font-weight:700}
        .form-card-title .title-icon{width:38px;height:38px;border-radius:10px;background:rgba(122,92,60,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .form-card-title .title-icon svg{width:20px;height:20px;color:var(--primary)}
        .form-card-desc{font-size:14px;color:var(--muted-foreground);margin-top:4px;margin-left:48px}
        .form-card-separator{height:1px;background:var(--border)}
        .form-card-body{padding:24px;display:flex;flex-direction:column;gap:22px}
        /* Section */
        .section-label{display:flex;align-items:center;gap:8px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted-foreground);margin-bottom:14px}
        .section-num{width:20px;height:20px;border-radius:4px;background:rgba(122,92,60,.1);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:var(--primary)}
        .section-sep{height:1px;background:var(--border)}
        /* Form Grid */
        .form-grid{display:grid;gap:14px}
        .form-grid.cols-2{grid-template-columns:repeat(2,1fr)}
        .span-2{grid-column:span 2}
        .form-field{display:flex;flex-direction:column}
        .form-label{font-size:14px;font-weight:500;color:var(--foreground);margin-bottom:6px}
        .form-label .req{color:var(--destructive)}
        .form-label .hint{font-weight:400;color:var(--muted-foreground);font-size:12px;margin-left:4px}
        .form-input,.form-select{width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);color:var(--foreground);font-size:14px;line-height:1.5;transition:border-color .15s,box-shadow .15s;outline:none}
        .form-input::placeholder{color:var(--muted-foreground);opacity:.7}
        .form-input:focus,.form-select:focus{border-color:var(--ring);box-shadow:0 0 0 2px rgba(122,92,60,.15)}
        .form-input.is-error{border-color:var(--destructive)}
        .form-textarea{width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);color:var(--foreground);font-size:14px;line-height:1.5;outline:none;resize:vertical;min-height:72px;transition:border-color .15s}
        .form-textarea:focus{border-color:var(--ring);box-shadow:0 0 0 2px rgba(122,92,60,.15)}
        .form-select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237a7060' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:36px}
        .form-error{font-size:12px;color:var(--destructive);margin-top:4px}
        .form-hint{font-size:12px;color:var(--muted-foreground);margin-top:4px}

        /* ── Autocomplete ── */
        .autocomplete-wrap{position:relative}
        .autocomplete-input{width:100%}
        .autocomplete-dropdown{position:absolute;top:calc(100% + 4px);left:0;right:0;background:var(--card);border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:100;max-height:280px;overflow-y:auto;display:none}
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
        .ac-item-badge{display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;padding:1px 7px;border-radius:999px;margin-left:6px}
        .ac-item-badge.warning{background:rgba(245,158,11,.12);color:#b45309}
        .ac-item-badge.danger{background:rgba(197,48,48,.1);color:#991b1b}
        .ac-empty{padding:14px;text-align:center;font-size:13px;color:var(--muted-foreground)}
        .ac-loading{padding:14px;text-align:center;font-size:13px;color:var(--muted-foreground);display:flex;align-items:center;justify-content:center;gap:8px}

        /* ── Selected Card ── */
        .selected-card{border:1.5px solid var(--primary);border-radius:calc(var(--radius) - 2px);background:rgba(122,92,60,.04);padding:12px 14px;display:flex;align-items:center;gap:12px;margin-top:8px}
        .selected-card-avatar{width:36px;height:36px;border-radius:50%;background:var(--sidebar-accent);color:var(--sidebar-foreground);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0}
        .selected-card-cover{width:32px;height:44px;border-radius:3px;object-fit:cover;flex-shrink:0}
        .selected-card-info{flex:1;min-width:0}
        .selected-card-name{font-size:14px;font-weight:600}
        .selected-card-meta{font-size:12px;color:var(--muted-foreground);margin-top:2px}
        .selected-card-clear{width:26px;height:26px;border-radius:6px;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--muted-foreground);transition:background .15s;flex-shrink:0}
        .selected-card-clear:hover{background:var(--muted);color:var(--foreground)}
        .selected-card-clear svg{width:14px;height:14px}

        /* ── Warning box ── */
        .warning-box{padding:10px 14px;background:rgba(197,48,48,.06);border:1px solid rgba(197,48,48,.2);border-radius:calc(var(--radius) - 2px);font-size:13px;color:#991b1b;display:flex;align-items:center;gap:8px}
        .warning-box svg{width:16px;height:16px;flex-shrink:0}
        .warning-box--block{padding:12px 16px;background:rgba(197,48,48,.08);border:1.5px solid rgba(197,48,48,.35);border-radius:calc(var(--radius) - 2px);font-size:13px;color:#7f1d1d;display:flex;align-items:flex-start;gap:10px}
        .warning-box--block svg{width:18px;height:18px;flex-shrink:0;margin-top:1px}
        .warning-box--block strong{display:block;font-size:13px;font-weight:700;margin-bottom:2px}
        .info-box{padding:10px 14px;background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25);border-radius:calc(var(--radius) - 2px);font-size:13px;color:#92400e;display:flex;align-items:center;gap:8px}
        .info-box svg{width:16px;height:16px;flex-shrink:0}

        /* ── Actions ── */
        .form-actions{display:flex;justify-content:flex-end;gap:12px;padding-top:4px}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:9px 16px;border-radius:calc(var(--radius) - 2px);font-size:14px;font-weight:500;cursor:pointer;transition:background .15s,opacity .15s;border:none;text-decoration:none}
        .btn svg{width:16px;height:16px}
        .btn-primary{background:var(--primary);color:var(--primary-foreground)}
        .btn-primary:hover{opacity:.9}
        .btn-primary:disabled{opacity:.5;cursor:not-allowed}
        .btn-outline{background:transparent;color:var(--foreground);border:1px solid var(--border)}
        .btn-outline:hover{background:var(--muted)}

        /* Toast */
        .toast-container{position:fixed;top:20px;right:20px;z-index:3000;display:flex;flex-direction:column;gap:10px}
        .toast{padding:14px 18px;border-radius:var(--radius);font-size:14px;font-weight:500;min-width:280px;box-shadow:0 4px 16px rgba(0,0,0,.12);border:1px solid transparent;animation:toast-in .3s ease}
        .toast.success{background:#f0fdf4;border-color:#bbf7d0;color:#166534}
        .toast.error{background:#fef2f2;border-color:#fecaca;color:#991b1b}
        .toast-desc{font-size:13px;opacity:.8;margin-top:2px}
        @keyframes toast-in{from{opacity:0;transform:translateX(20px)}to{opacity:1;transform:translateX(0)}}
        @keyframes toast-out{from{opacity:1}to{opacity:0}}
        @keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}

        /* Sidebar overlay (mobile) */
        .sidebar-overlay{display:none;position:fixed;inset:0;z-index:39;background:rgba(0,0,0,.4)}
        @media(max-width:768px){
            .main-content{margin-left:0}
            .sidebar{transform:translateX(-260px)}
            .sidebar.open{transform:translateX(0)}
            .sidebar-overlay.visible{display:block}
            .form-grid.cols-2{grid-template-columns:1fr}
            .span-2{grid-column:span 1}
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
                <a href="{{ route('odunc.index') }}" class="breadcrumb-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3h5v5"/><path d="M8 3H3v5"/><path d="M12 22v-8.3a4 4 0 0 0-1.172-2.872L3 3"/><path d="m15 9 6-6"/></svg>
                    Ödünç İşlemleri
                </a>
                <span class="breadcrumb-sep">›</span>
                <span class="breadcrumb-current">Yeni Ödünç Ver</span>
            </nav>
        </div>

        <div class="content-area">
            <form id="oduncForm" action="{{ route('odunc.store') }}" method="POST">
                @csrf
                <!-- Hidden IDs -->
                <input type="hidden" name="uye_id" id="uyeId" />
                <input type="hidden" name="katalog_id" id="katalogId" />

                <div class="form-card">
                    <div class="form-card-header">
                        <div class="form-card-title">
                            <div class="title-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M12 18v-6"/><path d="M9 15h6"/></svg>
                            </div>
                            Yeni Ödünç İşlemi
                        </div>
                        <p class="form-card-desc">Üye ve kitabı seçerek ödünç işlemini başlatın.</p>
                    </div>
                    <div class="form-card-separator"></div>
                    <div class="form-card-body">

                        <!-- ── 1. Üye Seç ── -->
                        <div>
                            <div class="section-label"><span class="section-num">1</span>Üye Seçimi</div>

                            <div class="form-field" id="uyeSearchField">
                                <label class="form-label" for="uyeSearch">Üye Ara <span class="req">*</span> <span class="hint">— ad, soyad veya TC ile</span></label>
                                <div class="autocomplete-wrap">
                                    <input type="text" id="uyeSearch" class="form-input autocomplete-input"
                                           placeholder="Üye adı veya TC kimlik no…"
                                           autocomplete="off" />
                                    <div class="autocomplete-dropdown" id="uyeDropdown"></div>
                                </div>
                            </div>

                            <!-- Seçilen üye kartı -->
                            <div id="uyeCard" style="display:none;" class="selected-card">
                                <div class="selected-card-avatar" id="uyeCardAv">—</div>
                                <div class="selected-card-info">
                                    <div class="selected-card-name" id="uyeCardName">—</div>
                                    <div class="selected-card-meta" id="uyeCardMeta">—</div>
                                </div>
                                <button type="button" class="selected-card-clear" onclick="clearUye()" title="Üyeyi Kaldır">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                </button>
                            </div>
                            <div id="uyeNotUyari" class="warning-box--block" style="display:none;margin-top:8px;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4"/><path d="M12 16h.01"/><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
                                <div>
                                    <strong>Üye Notu</strong>
                                    <span id="uyeNotMetin">—</span>
                                </div>
                            </div>

                            @error('uye_id')
                            <div class="form-error" style="margin-top:6px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="section-sep"></div>

                        <!-- ── 2. Kitap Seç ── -->
                        <div>
                            <div class="section-label"><span class="section-num">2</span>Kitap Seçimi</div>

                            <div class="form-field" id="kitapSearchField">
                                <label class="form-label" for="kitapSearch">Kitap Ara <span class="req">*</span> <span class="hint">— başlık, yazar veya ISBN ile</span></label>
                                <div class="autocomplete-wrap">
                                    <input type="text" id="kitapSearch" class="form-input autocomplete-input"
                                           placeholder="Kitap adı, yazar veya ISBN…"
                                           autocomplete="off" />
                                    <div class="autocomplete-dropdown" id="kitapDropdown"></div>
                                </div>
                            </div>

                            <!-- Seçilen kitap kartı -->
                            <div id="kitapCard" style="display:none;" class="selected-card">
                                <div id="kitapCardCoverWrap"></div>
                                <div class="selected-card-info">
                                    <div class="selected-card-name" id="kitapCardName">—</div>
                                    <div class="selected-card-meta" id="kitapCardMeta">—</div>
                                </div>
                                <button type="button" class="selected-card-clear" onclick="clearKitap()" title="Kitabı Kaldır">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                </button>
                            </div>

                            <!-- Uyarı: kitap ödünçte -->
                            <div id="kitapUyari" class="warning-box" style="display:none;margin-top:8px;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                                Bu kitap şu an başka bir üyede ödünçte. Yine de devam edebilirsiniz ancak kaydetme sırasında hata alırsınız.
                            </div>

                            <!-- Engel: kitap rafta değil veya ödünç verilemez -->
                            <div id="kitapEngel" class="warning-box--block" style="display:none;margin-top:8px;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m4.9 4.9 14.2 14.2"/></svg>
                                <div>
                                    <strong id="kitapEngelBaslik">Ödünç Verilemez</strong>
                                    <span id="kitapEngelAciklama">Bu kitap şu an ödünç verilemeyen durumda.</span>
                                </div>
                            </div>

                            @error('katalog_id')
                            <div class="form-error" style="margin-top:6px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="section-sep"></div>

                        <!-- ── 3. Tarih & Detay ── -->
                        <div>
                            <div class="section-label"><span class="section-num">3</span>Tarih & Detaylar</div>
                            <div class="form-grid cols-2">
                                <div class="form-field">
                                    <label class="form-label" for="odunc_tarihi">Ödünç Tarihi <span class="req">*</span></label>
                                    <input type="date" id="odunc_tarihi" name="odunc_tarihi"
                                           class="form-input {{ $errors->has('odunc_tarihi') ? 'is-error' : '' }}"
                                           min="{{ date('Y-m-d', strtotime('-7 days')) }}"
                                           max="{{ date('Y-m-d') }}"
                                           value="{{ old('odunc_tarihi', date('Y-m-d')) }}" />
                                    @error('odunc_tarihi') <div class="form-error">{{ $message }}</div> @enderror
                                    <div class="form-hint">En fazla 1 hafta öncesi seçilebilir</div>
                                </div>
                                <div class="form-field">
                                    <label class="form-label" for="iade_tarihi_planlanan">Planlanan İade <span class="req">*</span></label>
                                    <input type="date" id="iade_tarihi_planlanan" name="iade_tarihi_planlanan"
                                           class="form-input {{ $errors->has('iade_tarihi_planlanan') ? 'is-error' : '' }}"
                                           value="{{ old('iade_tarihi_planlanan', date('Y-m-d', strtotime('+14 days'))) }}" />
                                    @error('iade_tarihi_planlanan') <div class="form-error">{{ $message }}</div> @enderror
                                    <div class="form-hint">Ödünç tarihinden en fazla 30 gün ileri seçilebilir</div>
                                </div>

                                <div class="form-field span-2">
                                    <label class="form-label" for="notlar">Notlar <span class="hint">(isteğe bağlı)</span></label>
                                    <textarea id="notlar" name="notlar" class="form-textarea"
                                              placeholder="Ödünç işlemi hakkında not…">{{ old('notlar') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <a href="{{ route('odunc.index') }}" class="btn btn-outline">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                                Vazgeç
                            </a>
                            <button type="button" id="btnSubmit" class="btn btn-primary" onclick="submitForm()">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M12 18v-6"/><path d="M9 15h6"/></svg>
                                Ödünç Ver
                            </button>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </main>
</div>
<div class="toast-container" id="toastContainer"></div>

<script>
    // ── Sidebar ──────────────────────────────────────────────────────────────
    var sidebar = document.getElementById('sidebar');
    var mainContent = document.getElementById('mainContent');
    var isMobile = window.innerWidth <= 768;
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        if (isMobile) { sidebar.classList.toggle('open'); document.getElementById('sidebarOverlay').classList.toggle('visible'); }
        else { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); }
    });
    document.getElementById('sidebarOverlay').addEventListener('click', function() { sidebar.classList.remove('open'); this.classList.remove('visible'); });
    window.addEventListener('resize', function() { isMobile = window.innerWidth <= 768; });

    // ── Toast ────────────────────────────────────────────────────────────────
    function showToast(type, title, desc) {
        var c = document.getElementById('toastContainer');
        var t = document.createElement('div'); t.className = 'toast ' + type;
        t.innerHTML = '<div>' + title + '</div>' + (desc ? '<div class="toast-desc">' + desc + '</div>' : '');
        c.appendChild(t);
        setTimeout(function() { t.style.animation = 'toast-out .3s ease forwards'; setTimeout(function() { if(t.parentNode) t.parentNode.removeChild(t); }, 300); }, 4500);
    }
    @if($errors->has('uye_id') || $errors->has('katalog_id'))
    showToast('error', 'Form hatası', 'Lütfen üye ve kitap seçimini yapın.');
    @endif

    // ── Autocomplete Core ────────────────────────────────────────────────────
    var acTimers = {};

    function setupAutocomplete(inputId, dropdownId, fetchUrl, onSelect, renderItem, extraQueryFn) {
        var inp = document.getElementById(inputId);
        var dd  = document.getElementById(dropdownId);
        var hi  = -1; // highlighted index

        inp.addEventListener('input', function() {
            var q = this.value.trim();
            clearTimeout(acTimers[inputId]);
            if (q.length < 2) { dd.classList.remove('open'); dd.innerHTML = ''; return; }
            acTimers[inputId] = setTimeout(function() {
                dd.innerHTML = '<div class="ac-loading"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin .6s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Aranıyor…</div>';
                dd.classList.add('open');
                var extra = (typeof extraQueryFn === 'function') ? extraQueryFn() : '';
                fetch(fetchUrl + '?q=' + encodeURIComponent(q) + extra, { headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } })
                    .then(r => r.json())
                    .then(function(items) {
                        dd.innerHTML = '';
                        hi = -1;
                        if (!items.length) { dd.innerHTML = '<div class="ac-empty">Sonuç bulunamadı</div>'; return; }
                        items.forEach(function(item, idx) {
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
            if (e.key === 'ArrowDown')  { e.preventDefault(); if (hi < items.length - 1) { hi++; updateHighlight(items, hi); } }
            else if (e.key === 'ArrowUp')   { e.preventDefault(); if (hi > 0) { hi--; updateHighlight(items, hi); } }
            else if (e.key === 'Enter' && hi >= 0) { e.preventDefault(); items[hi].dispatchEvent(new MouseEvent('mousedown')); }
            else if (e.key === 'Escape') { dd.classList.remove('open'); }
        });

        document.addEventListener('click', function(e) {
            if (!inp.contains(e.target) && !dd.contains(e.target)) dd.classList.remove('open');
        });
    }

    function updateHighlight(items, idx) {
        items.forEach((el, i) => el.classList.toggle('highlighted', i === idx));
    }

    // ── Üye Autocomplete ─────────────────────────────────────────────────────
    var selectedUye = null;

    setupAutocomplete('uyeSearch', 'uyeDropdown', '{{ route('odunc.uyeAra') }}',
        function(item) { selectUye(item); },
        function(item) {
            var badge = item.aktif_odunc > 0
                ? '<span class="ac-item-badge warning">' + item.aktif_odunc + ' aktif ödünç</span>'
                : '';
            if (item.notlar && String(item.notlar).trim().length > 0) {
                badge += '<span class="ac-item-badge danger">Not var</span>';
            }
            var initials = item.label.split(' ').map(w => w[0]).join('').slice(0,2).toUpperCase();
            return '<div class="ac-item-avatar">' + initials + '</div>'
                + '<div class="ac-item-body">'
                +   '<div class="ac-item-name">' + item.label + badge + '</div>'
                +   '<div class="ac-item-meta">TC: ' + item.tc + ' · ' + item.telefon + '</div>'
                + '</div>';
        }
    );

    function selectUye(item) {
        selectedUye = item;
        document.getElementById('uyeId').value = item.id;
        var initials = item.label.split(' ').map(w => w[0]).join('').slice(0,2).toUpperCase();
        document.getElementById('uyeCardAv').textContent  = initials;
        document.getElementById('uyeCardName').textContent = item.label;
        document.getElementById('uyeCardMeta').textContent = 'TC: ' + item.tc + ' · ' + item.telefon
            + (item.aktif_odunc > 0 ? ' · ' + item.aktif_odunc + ' aktif ödünç' : '');
        document.getElementById('uyeSearchField').style.display = 'none';
        document.getElementById('uyeCard').style.display = 'flex';
        var uyeNot = String(item.notlar || '').trim();
        var uyeNotUyari = document.getElementById('uyeNotUyari');
        var uyeNotMetin = document.getElementById('uyeNotMetin');
        if (uyeNot) {
            uyeNotMetin.textContent = uyeNot;
            uyeNotUyari.style.display = 'flex';
        } else {
            uyeNotMetin.textContent = '';
            uyeNotUyari.style.display = 'none';
        }

        if (selectedKitap && selectedKitap.id) {
            fetch('{{ route('odunc.kitapAra') }}?katalog_id=' + encodeURIComponent(selectedKitap.id) + '&uye_id=' + encodeURIComponent(item.id), {
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
            })
                .then(function(r) { return r.json(); })
                .then(function(rows) {
                    if (rows && rows[0]) {
                        selectedKitap = rows[0];
                        selectKitap(rows[0]);
                    }
                })
                .catch(function() {});
        }
    }

    function clearUye() {
        selectedUye = null;
        document.getElementById('uyeId').value = '';
        document.getElementById('uyeSearchField').style.display = 'block';
        document.getElementById('uyeCard').style.display = 'none';
        document.getElementById('uyeNotUyari').style.display = 'none';
        document.getElementById('uyeNotMetin').textContent = '';
        document.getElementById('uyeSearch').value = '';
        document.getElementById('uyeSearch').focus();
    }

    // ── Kitap Autocomplete ───────────────────────────────────────────────────
    var selectedKitap = null;

    setupAutocomplete('kitapSearch', 'kitapDropdown', '{{ route('odunc.kitapAra') }}',
        function(item) { selectKitap(item); },
        function(item) {
            var coverHtml = item.kapak
                ? '<img src="' + item.kapak + '" class="ac-item-cover" />'
                : '<div class="ac-item-cover-ph"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg></div>';
            var badges = '';
            if (item.odunc_ta) {
                badges += '<span class="ac-item-badge danger">Ödünçte</span>';
            } else if (item.oduncVerilemez === 'true') {
                badges += '<span class="ac-item-badge danger">Ödünç Verilemez</span>';
            } else if (item.kunyeDurum === 'Rezerve' && item.rezerve_aktif_bu_uye) {
                badges += '<span class="ac-item-badge warning">Rezerve · seçili üye</span>';
            } else if (item.kunyeDurum && item.kunyeDurum !== 'Rafta') {
                badges += '<span class="ac-item-badge danger">' + item.kunyeDurum + '</span>';
            }
            return coverHtml
                + '<div class="ac-item-body">'
                +   '<div class="ac-item-name">' + item.label + badges + '</div>'
                +   '<div class="ac-item-meta">' + (item.yazar || '') + (item.demir ? ' · Demirbaş: ' + item.demir : '') + (item.isbn ? ' · ISBN: ' + item.isbn : '') + '</div>'
                + '</div>';
        }
    ,
        function() {
            var uid = document.getElementById('uyeId').value;
            return uid ? '&uye_id=' + encodeURIComponent(uid) : '';
        }
    );

    function selectKitap(item) {
        selectedKitap = item;
        document.getElementById('katalogId').value = item.id;

        // Kapak görseli
        var wrap = document.getElementById('kitapCardCoverWrap');
        if (item.kapak) {
            wrap.innerHTML = '<img src="' + item.kapak + '" class="selected-card-cover" />';
        } else {
            wrap.innerHTML = '';
        }

        document.getElementById('kitapCardName').textContent = item.label;
        document.getElementById('kitapCardMeta').textContent = (item.yazar || '') + (item.demir ? ' · Demirbaş: ' + item.demir : '') + (item.isbn ? ' · ISBN: ' + item.isbn : '') ;

        document.getElementById('kitapSearchField').style.display = 'none';
        document.getElementById('kitapCard').style.display = 'flex';

        // Ödünçte uyarısı (sadece ödünçte, diğer engeller yoksa)
        document.getElementById('kitapUyari').style.display = 'none';

        // Engel kutusu (Rafta değil veya ödünç verilemez işareti var veya şu an ödünçte)
        var engelDiv      = document.getElementById('kitapEngel');
        var engelBaslik   = document.getElementById('kitapEngelBaslik');
        var engelAciklama = document.getElementById('kitapEngelAciklama');
        var btnSubmit     = document.getElementById('btnSubmit');

        if (item.odunc_ta) {
            engelBaslik.textContent   = 'Kitap Ödünçte';
            engelAciklama.textContent = 'Bu kitap şu an başka bir üyede ödünçte olduğu için tekrar ödünç verilemez.';
            engelDiv.style.display    = 'flex';
            btnSubmit.disabled        = true;
        } else if (item.oduncVerilemez == 'true') {
            engelBaslik.textContent   = 'Ödünç Verilemez';
            engelAciklama.textContent = 'Bu kitap "Ödünç Verilemez" olarak işaretlendiğinden ödünç verilemiyor.';
            engelDiv.style.display    = 'flex';
            btnSubmit.disabled        = true;
        } else if (item.kunyeDurum == 'Rezerve') {
            if (item.rezerve_aktif_bu_uye) {
                engelDiv.style.display = 'none';
                btnSubmit.disabled     = false;
            } else {
                engelBaslik.textContent   = document.getElementById('uyeId').value ? 'Rezervasyon uyumsuz' : 'Üye seçin';
                var rezEk = (document.getElementById('uyeId').value && item.rezerve_eden_uye_adi)
                    ? (' Bu kitabı rezerve eden kişi: ' + item.rezerve_eden_uye_adi + '.')
                    : '';
                engelAciklama.textContent = document.getElementById('uyeId').value
                    ? ('Bu kitap başka bir üyeye rezerve; yalnızca rezervasyon sahibi seçildiğinde ödünç verilebilir.' + rezEk)
                    : 'Rezerve kitaplar yalnızca rezervasyon sahibi üyeye ödünç verilir. Önce üyeyi seçin, ardından kitabı tekrar arayın.';
                engelDiv.style.display    = 'flex';
                btnSubmit.disabled        = true;
            }
        } else if (item.kunyeDurum && item.kunyeDurum !== 'Rafta') {
            engelBaslik.textContent   = 'Kitap Rafta Değil';
            engelAciklama.textContent = 'Bu kitabın durumu "' + item.kunyeDurum + '" olduğu için ödünç verilemez. Yalnızca "Rafta" durumundaki kitaplar ödünç verilebilir.';
            engelDiv.style.display    = 'flex';
            btnSubmit.disabled        = true;
        } else {
            engelDiv.style.display = 'none';
            btnSubmit.disabled     = false;
        }
    }

    function clearKitap() {
        selectedKitap = null;
        document.getElementById('katalogId').value = '';
        document.getElementById('kitapSearchField').style.display = 'block';
        document.getElementById('kitapCard').style.display = 'none';
        document.getElementById('kitapUyari').style.display = 'none';
        document.getElementById('kitapEngel').style.display = 'none';
        document.getElementById('btnSubmit').disabled = false;
        document.getElementById('kitapSearch').value = '';
        document.getElementById('kitapSearch').focus();
    }

    // ── Tarih Kısıtlamaları ──────────────────────────────────────────────────
    (function () {
        var oduncInput = document.getElementById('odunc_tarihi');
        var iadeInput  = document.getElementById('iade_tarihi_planlanan');

        // Bugün ve 1 hafta öncesi
        function today()   { return new Date(new Date().toDateString()); }
        function toYmd(d)  { return d.toISOString().slice(0, 10); }
        function addDays(d, n) { var r = new Date(d); r.setDate(r.getDate() + n); return r; }

        function updateIadeLimits() {
            var oduncVal = oduncInput.value;
            if (!oduncVal) return;
            var oduncDate = new Date(oduncVal + 'T00:00:00');
            var maxIade   = addDays(oduncDate, 30);
            iadeInput.min = oduncVal;
            iadeInput.max = toYmd(maxIade);

            // Mevcut iade değeri kısıtın dışındaysa düzelt
            if (iadeInput.value && iadeInput.value < oduncVal) {
                iadeInput.value = oduncVal;
            }
            if (iadeInput.value && iadeInput.value > toYmd(maxIade)) {
                iadeInput.value = toYmd(maxIade);
            }
        }

        // Sayfa yüklenince sınırları uygula
        oduncInput.min = toYmd(addDays(today(), -7));
        oduncInput.max = toYmd(today());
        updateIadeLimits();

        // Ödünç tarihi değiştiğinde iade limitlerini güncelle
        oduncInput.addEventListener('change', function () {
            // İleri tarih girişini engelle
            var chosen   = new Date(this.value + 'T00:00:00');
            var minDate  = addDays(today(), -7);
            if (chosen > today()) { this.value = toYmd(today()); }
            if (chosen < minDate) { this.value = toYmd(minDate); }
            updateIadeLimits();
        });

        // İade tarihi değiştiğinde sınır kontrolü
        iadeInput.addEventListener('change', function () {
            var oduncVal = oduncInput.value;
            if (!oduncVal) return;
            var oduncDate = new Date(oduncVal + 'T00:00:00');
            var maxIade   = addDays(oduncDate, 30);
            var chosen    = new Date(this.value + 'T00:00:00');
            if (chosen < oduncDate) { this.value = oduncVal; }
            if (chosen > maxIade)   { this.value = toYmd(maxIade); }
        });
    })();

    // ── Submit ───────────────────────────────────────────────────────────────
    function submitForm() {
        var uyeId     = document.getElementById('uyeId').value;
        var katalogId = document.getElementById('katalogId').value;
        var oduncVal  = document.getElementById('odunc_tarihi').value;
        var iadeVal   = document.getElementById('iade_tarihi_planlanan').value;

        if (!uyeId) {
            showToast('error', 'Üye seçilmedi', 'Lütfen bir üye seçin.');
            document.getElementById('uyeSearch').focus();
            return;
        }
        if (!katalogId) {
            showToast('error', 'Kitap seçilmedi', 'Lütfen bir kitap seçin.');
            document.getElementById('kitapSearch').focus();
            return;
        }

        // Seçilen kitabın durumunu tekrar kontrol et
        if (selectedKitap) {
            if (selectedKitap.odunc_ta) {
                showToast('error', 'Kitap Ödünçte', 'Bu kitap şu an başka bir üyede ödünçte.');
                return;
            }
            if (selectedKitap.oduncVerilemez == 'true') {
                showToast('error', 'Ödünç Verilemez', 'Bu kitap ödünç verilemez olarak işaretlenmiş.');
                return;
            }
            if (selectedKitap.kunyeDurum === 'Rezerve') {
                if (!selectedKitap.rezerve_aktif_bu_uye) {
                    var rezToastEk = selectedKitap.rezerve_eden_uye_adi
                        ? (' Bu kitabı rezerve eden kişi: ' + selectedKitap.rezerve_eden_uye_adi + '.')
                        : '';
                    showToast('error', 'Rezervasyon', 'Bu rezerve kitap yalnızca rezervasyon sahibi üyeye ödünç verilebilir.' + rezToastEk);
                    return;
                }
            } else if (selectedKitap.kunyeDurum && selectedKitap.kunyeDurum !== 'Rafta') {
                showToast('error', 'Kitap Rafta Değil', '"' + selectedKitap.kunyeDurum + '" durumundaki kitap ödünç verilemez.');
                return;
            }
        }

        // Tarih kontrolleri
        if (oduncVal && iadeVal) {
            var today    = new Date(new Date().toDateString());
            var minOdunc = new Date(today); minOdunc.setDate(today.getDate() - 7);
            var oduncD   = new Date(oduncVal + 'T00:00:00');
            var iadeD    = new Date(iadeVal  + 'T00:00:00');

            if (oduncD > today) {
                showToast('error', 'Geçersiz Tarih', 'Ödünç tarihi ileri tarih olamaz.');
                return;
            }
            if (oduncD < minOdunc) {
                showToast('error', 'Geçersiz Tarih', 'Ödünç tarihi en fazla 1 hafta geriye alınabilir.');
                return;
            }
            if (iadeD < oduncD) {
                showToast('error', 'Geçersiz Tarih', 'Planlanan iade tarihi ödünç tarihinden önce olamaz.');
                return;
            }
            var maxIade = new Date(oduncD); maxIade.setDate(oduncD.getDate() + 30);
            if (iadeD > maxIade) {
                showToast('error', 'Geçersiz Tarih', 'Planlanan iade tarihi ödünç tarihinden en fazla 30 gün ileri olabilir.');
                return;
            }
        }

        var btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin .6s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Kaydediliyor…';

        document.getElementById('oduncForm').submit();
    }

    // ── Üye listesinden "Ödünç Ver" ile gelindiyse üyeyi önce seç (rezerve eşleşmesi için) ─
    @if(!empty($preUye))
    (function() {
        var preUye = @json($preUye);
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() { selectUye(preUye); });
        } else {
            selectUye(preUye);
        }
    })();
    @endif

    // ── Katalog listesinden "Ödünç Ver" ile gelindiyse kitabı otomatik seç ────
    @if(!empty($preKitap))
    (function() {
        var preKitap = @json($preKitap);
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() { selectKitap(preKitap); });
        } else {
            selectKitap(preKitap);
        }
    })();
    @endif
</script>
</body>
</html>
