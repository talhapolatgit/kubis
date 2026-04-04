<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#7a5c3c" />
    <title>Kullanıcı Yetkileri — Beyoğlu Kütüphane Sistemi</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700;900&family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    
    <style>
        :root{
            --background:#f5f0e8;--foreground:#3d3226;--card:#faf8f3;
            --primary:#7a5c3c;--primary-foreground:#f5f0e8;
            --secondary:#ede8de;--muted:#ede8de;--muted-foreground:#7a7060;
            --destructive:#c53030;--border:#d9d0c2;--ring:#7a5c3c;--radius:.625rem;
            --sidebar:#3d3226;--sidebar-foreground:#e8e2d6;
            --sidebar-primary:#9b7b55;--sidebar-primary-foreground:#f5f0e8;
            --sidebar-accent:#524435;--sidebar-accent-foreground:#e8e2d6;
            --sidebar-border:#5a4a3a;
            --font-sans:'Source Sans 3',system-ui,sans-serif;
            --font-serif:'Merriweather',Georgia,serif;
        }
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:var(--font-sans);background:var(--background);color:var(--foreground);-webkit-font-smoothing:antialiased;line-height:1.5}
        input,button{font-family:inherit;font-size:inherit}
        .app-layout{display:flex;min-height:100vh}

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

        .content-area{flex:1;padding:24px;display:flex;flex-direction:column;gap:16px}
        .page-title{font-family:var(--font-serif);font-size:22px;font-weight:700}
        .page-sub{font-size:13px;color:var(--muted-foreground);margin-top:2px}

        .card{background:var(--card);border:1px solid rgba(217,208,194,.6);border-radius:var(--radius);box-shadow:0 1px 3px rgba(0,0,0,.04);overflow:hidden}
        .card-h{padding:18px 20px;display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
        .card-sep{height:1px;background:var(--border)}
        .card-b{padding:18px 20px}

        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:9px 16px;border-radius:calc(var(--radius) - 2px);font-size:14px;font-weight:500;cursor:pointer;transition:background .15s,opacity .15s;border:none;text-decoration:none}
        .btn svg{width:16px;height:16px}
        .btn-primary{background:var(--primary);color:var(--primary-foreground)}
        .btn-primary:hover{opacity:.9}
        .btn-outline{background:transparent;color:var(--foreground);border:1px solid var(--border)}
        .btn-outline:hover{background:var(--muted)}

        .grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
        .perm{display:flex;gap:10px;align-items:flex-start;padding:10px 12px;border:1px solid rgba(217,208,194,.7);border-radius:10px;background:rgba(237,232,222,.45)}
        .perm:hover{border-color:var(--border)}
        .perm input{margin-top:4px}
        .perm-title{font-weight:700;font-size:13px}
        .perm-desc{font-size:13px;color:var(--muted-foreground);margin-top:2px}
        .badge{display:inline-flex;align-items:center;gap:6px;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:700;background:rgba(122,92,60,.12);color:var(--primary)}

        .sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:35}
        .sidebar-overlay.visible{display:block}
        @media(max-width:900px){.grid{grid-template-columns:1fr}}
        @media(max-width:768px){.main-content{margin-left:0}}
    </style>
</head>
<body>
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
                <a href="{{ route('users.index') }}" class="breadcrumb-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Kullanıcılar
                </a>
                <span class="breadcrumb-sep">/</span>
                <a href="{{ route('users.edit', $user->id) }}" class="breadcrumb-link">{{ $user->name }}</a>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-current">Yetkiler</span>
            </nav>
        </header>

        <div class="content-area">
            <div>
                <div class="page-title">Kullanıcı Yetkileri</div>
                <div class="page-sub">
                    <span class="badge">#{{ $user->id }}</span>
                    <span style="margin-left:6px;font-weight:700;">{{ $user->name }}</span>
                    <span style="margin-left:6px;color:var(--muted-foreground);">({{ $user->email }})</span>
                </div>
            </div>

            <form class="card" method="POST" action="{{ route('users.yetkiler.update', $user->id) }}">
                @csrf
                <div class="card-h">
                    <div>
                        <div style="font-family:var(--font-serif);font-size:18px;font-weight:800;">Yetki Tanımla</div>
                        <div style="font-size:13px;color:var(--muted-foreground);margin-top:3px;">
                            Bu sayfada seçilen yetkiler, kullanıcı arayüzünde butonları ve erişimleri etkiler.
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end;">
                        <a class="btn btn-outline" href="{{ route('users.edit', $user->id) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            Geri Dön
                        </a>
                        <button class="btn btn-primary" type="submit">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v13a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            Kaydet
                        </button>
                    </div>
                </div>
                <div class="card-sep"></div>
                <div class="card-b">
                    <div class="grid">
                        @foreach($yetkiler as $no => $label)
                            @php
                                $col = 'y' . str_pad((string)$no, 2, '0', STR_PAD_LEFT);
                                $checked = (bool)($row->{$col} ?? false);
                            @endphp
                            <label class="perm">
                                <input type="checkbox" name="{{ $col }}" {{ $checked ? 'checked' : '' }} />
                                <div>
                                    <div class="perm-title">{{ $no }}. Yetki</div>
                                    <div class="perm-desc">{{ $label }}</div>
                                </div>
                            </label>
                        @endforeach
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
    window.addEventListener('resize', function() { isMobile = window.innerWidth <= 768; });
</script>
</body>
</html>

