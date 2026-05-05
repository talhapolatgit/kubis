<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#7a5c3c" />
    <title>@yield('title', 'Kütüphane Bilgi Sistemi')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700;900&family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}" />
    @php($sidebarCss = @file_get_contents(public_path('css/sidebar.css')))
    @if($sidebarCss)
        <style>{!! $sidebarCss !!}</style>
    @endif
    <style>
        :root {
            --background:#f5f0e8;--foreground:#3d3226;--card:#faf8f3;
            --card-foreground:#3d3226;
            --primary:#7a5c3c;--primary-foreground:#f5f0e8;
            --secondary-foreground:#4a3c2e;
            --secondary:#ede8de;--muted:#ede8de;--muted-foreground:#7a7060;
            --accent:#9b6b3f;--accent-foreground:#f5f0e8;
            --destructive:#c53030;--border:#d9d0c2;--ring:#7a5c3c;--radius:0.625rem;
            --input:#e2dbd0;
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
        .content-area{flex:1;padding:12px}
        .form-card{border:1px solid rgba(217,208,194,.6);border-radius:var(--radius);background:var(--card);box-shadow:0 1px 3px rgba(0,0,0,.04);overflow:hidden}
        .form-card-header{padding:24px 24px 16px}
        .form-card-title{display:flex;align-items:center;gap:8px;font-family:var(--font-serif);font-size:20px;font-weight:700;color:var(--foreground)}
        .form-card-title svg{width:20px;height:20px;color:var(--primary)}
        .form-card-desc{font-size:14px;color:var(--muted-foreground);line-height:1.6;margin-top:4px}
        .form-card-separator{height:1px;background:var(--border)}
        .form-card-body{padding:24px}
        .section-sep{height:1px;background:var(--border)}
        .form-grid{display:grid;gap:16px}
        .form-grid.cols-2{grid-template-columns:repeat(2,1fr)}
        .form-grid.cols-3{grid-template-columns:repeat(3,1fr)}
        .form-grid .span-2{grid-column:span 2}
        .form-field{display:flex;flex-direction:column}
        .form-label{font-size:14px;font-weight:500;color:var(--foreground);margin-bottom:6px}
        .form-input,.form-select,.form-textarea{width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);color:var(--foreground);font-size:14px;line-height:1.5;transition:border-color .15s,box-shadow .15s;outline:none}
        .form-input::placeholder,.form-textarea::placeholder{color:var(--muted-foreground);opacity:.7}
        .form-input:focus,.form-select:focus,.form-textarea:focus{border-color:var(--ring);box-shadow:0 0 0 2px rgba(122,92,60,.15)}
        .form-select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237a7060' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:36px}
        .form-textarea{resize:vertical}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:9px 16px;border-radius:calc(var(--radius) - 2px);font-size:14px;font-weight:500;cursor:pointer;transition:background .15s,opacity .15s;border:none;text-decoration:none}
        .btn svg{width:16px;height:16px}
        .btn-primary{background:var(--primary);color:var(--primary-foreground)}
        .btn-primary:hover{opacity:.9}
        .btn-outline{background:transparent;color:var(--foreground);border:1px solid var(--border)}
        .btn-outline:hover{background:var(--muted)}
        @media(max-width:768px){.main-content{margin-left:0}}
    </style>
    @yield('styles')
</head>
<body>
<div class="app-layout">
    @include('partials.sidebar')
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="main-content" id="mainContent">
        <header class="top-header">
            <button class="sidebar-trigger" id="sidebarToggle" aria-label="Sidebar toggle">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/></svg>
            </button>
            <div class="header-separator"></div>
            @yield('breadcrumb')
        </header>

        <div class="content-area">
            @yield('content')
        </div>
    </main>
</div>

<script>
    var sidebar = document.getElementById('sidebar');
    var mainContent = document.getElementById('mainContent');
    var sidebarOverlay = document.getElementById('sidebarOverlay');
    var isMobile = window.innerWidth <= 768;
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        if (isMobile) {
            sidebar.classList.toggle('open');
            sidebarOverlay.classList.toggle('visible');
        } else {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        }
    });
    sidebarOverlay.addEventListener('click', function() {
        sidebar.classList.remove('open');
        sidebarOverlay.classList.remove('visible');
    });
    window.addEventListener('resize', function() { isMobile = window.innerWidth <= 768; });
</script>
@yield('scripts')
</body>
</html>
