<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#7a5c3c" />
    <title>Kullanıcı Düzenle — Beyoğlu Kütüphane Sistemi</title>
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
        input, select, button { font-family: inherit; font-size: inherit; }

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
        .content-area { flex: 1; padding: 24px; display: flex; flex-direction: column; gap: 20px; }

        /* User hero */
        .user-hero { background: var(--card); border: 1px solid rgba(217,208,194,0.6); border-radius: var(--radius); padding: 20px 24px; display: flex; align-items: center; gap: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
        .user-hero-avatar { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 700; color: white; flex-shrink: 0; }
        .user-hero-name { font-family: var(--font-serif); font-size: 18px; font-weight: 700; }
        .user-hero-email { font-size: 13px; color: var(--muted-foreground); margin-top: 2px; }
        .user-hero-meta { display: flex; gap: 16px; margin-top: 8px; flex-wrap: wrap; }
        .user-hero-meta-item { font-size: 12px; color: var(--muted-foreground); display: flex; align-items: center; gap: 5px; }
        .user-hero-meta-item svg { width: 13px; height: 13px; }
        .role-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .role-admin    { background: rgba(122,92,60,0.12); color: #5a3e28; }
        .role-personel { background: rgba(26,107,26,0.1); color: #1a5c1d; }
        .role-okuyucu  { background: rgba(37,99,235,0.08); color: #1e40af; }

        /* ── Two-column layout ─────────────────────────────────────────────── */
        .edit-layout { display: flex; gap: 20px; align-items: flex-start; }
        .edit-form-wrap { flex: 1; min-width: 0; }

        /* Form Card */
        .form-card { background: var(--card); border: 1px solid rgba(217,208,194,0.6); border-radius: var(--radius); box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
        .form-card-header { padding: 24px 24px 16px; }
        .form-card-title { display: flex; align-items: center; gap: 10px; font-family: var(--font-serif); font-size: 20px; font-weight: 700; }
        .form-card-title svg { width: 20px; height: 20px; color: var(--primary); }
        .form-card-desc { font-size: 14px; color: var(--muted-foreground); margin-top: 4px; }
        .form-card-sep { height: 1px; background: var(--border); }
        .form-card-body { padding: 24px; display: flex; flex-direction: column; gap: 24px; }

        /* Section */
        .section-label { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted-foreground); margin-bottom: 14px; }
        .section-num { width: 20px; height: 20px; border-radius: 4px; background: rgba(122,92,60,0.1); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: var(--primary); }
        .section-sep { height: 1px; background: var(--border); }

        /* Form */
        .form-grid { display: grid; gap: 16px; }
        .form-grid.cols-2 { grid-template-columns: repeat(2, 1fr); }
        .form-field { display: flex; flex-direction: column; }
        .form-label { font-size: 14px; font-weight: 500; margin-bottom: 6px; }
        .form-label .req { color: var(--destructive); }
        .input-wrap { position: relative; }
        .input-icon { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--muted-foreground); pointer-events: none; }
        .form-input, .form-select { width: 100%; padding: 9px 12px; border: 1px solid var(--border); border-radius: calc(var(--radius) - 2px); background: var(--card); color: var(--foreground); font-size: 14px; outline: none; transition: border-color 0.15s, box-shadow 0.15s; }
        .form-input.has-icon { padding-left: 36px; }
        .form-input.has-icon-right { padding-right: 40px; }
        .form-input::placeholder { color: var(--muted-foreground); opacity: 0.7; }
        .form-input:focus, .form-select:focus { border-color: var(--ring); box-shadow: 0 0 0 2px rgba(122,92,60,0.14); }
        .form-input.is-error { border-color: var(--destructive); }
        .form-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237a7060' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px; cursor: pointer; }
        .form-hint { font-size: 12px; color: var(--muted-foreground); margin-top: 4px; }
        .form-error { font-size: 12px; color: var(--destructive); margin-top: 4px; }

        /* Şifre toggle */
        .pw-toggle { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 5px; color: var(--muted-foreground); border-radius: 4px; display: flex; transition: color 0.15s, background 0.15s; }
        .pw-toggle:hover { color: var(--foreground); background: var(--muted); }
        .pw-toggle svg { width: 15px; height: 15px; }
        .pw-strength { margin-top: 6px; }
        .pw-strength-bar { height: 3px; border-radius: 2px; background: var(--border); overflow: hidden; margin-bottom: 4px; }
        .pw-strength-fill { height: 100%; border-radius: 2px; transition: width 0.3s, background 0.3s; width: 0%; }
        .pw-strength-label { font-size: 11px; }

        /* Rol kartları */
        .role-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .role-card { border: 2px solid var(--border); border-radius: 10px; padding: 14px 12px; cursor: pointer; transition: border-color 0.15s, background 0.15s; position: relative; }
        .role-card:hover { border-color: var(--ring); background: rgba(122,92,60,0.04); }
        .role-card.selected { border-color: var(--primary); background: rgba(122,92,60,0.06); }
        .role-card input[type=radio] { position: absolute; opacity: 0; pointer-events: none; }
        .role-card-icon { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; }
        .role-card-icon svg { width: 18px; height: 18px; }
        .role-card-name { font-size: 14px; font-weight: 600; margin-bottom: 3px; }
        .role-card-desc { font-size: 12px; color: var(--muted-foreground); line-height: 1.4; }
        .role-card-check { position: absolute; top: 10px; right: 10px; width: 18px; height: 18px; border-radius: 50%; border: 2px solid var(--border); display: flex; align-items: center; justify-content: center; transition: border-color 0.15s, background 0.15s; }
        .role-card.selected .role-card-check { border-color: var(--primary); background: var(--primary); }
        .role-card.selected .role-card-check::after { content: ''; width: 6px; height: 6px; border-radius: 50%; background: white; }

        /* Meta bilgiler */
        .meta-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .meta-item { padding: 12px 14px; background: var(--secondary); border-radius: calc(var(--radius) - 2px); }
        .meta-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--muted-foreground); margin-bottom: 3px; }
        .meta-value { font-size: 13px; font-weight: 500; }

        /* Actions */
        .form-actions { display: flex; justify-content: flex-end; gap: 12px; padding-top: 4px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 9px 16px; border-radius: calc(var(--radius) - 2px); font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.15s, opacity 0.15s; border: none; text-decoration: none; }
        .btn svg { width: 16px; height: 16px; }
        .btn-primary { background: var(--primary); color: var(--primary-foreground); }
        .btn-primary:hover { opacity: 0.9; }
        .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
        .btn-outline { background: transparent; color: var(--foreground); border: 1px solid var(--border); }
        .btn-outline:hover { background: var(--muted); }

        /* Loading */
        .loading-overlay { position: fixed; inset: 0; z-index: 2000; background: rgba(61,50,38,0.45); backdrop-filter: blur(3px); display: flex; align-items: center; justify-content: center; opacity: 0; visibility: hidden; transition: opacity 0.2s, visibility 0.2s; }
        .loading-overlay.visible { opacity: 1; visibility: visible; }
        .loading-box { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 40px 56px; display: flex; flex-direction: column; align-items: center; gap: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.25); transform: scale(0.92); transition: transform 0.2s; }
        .loading-overlay.visible .loading-box { transform: scale(1); }
        .loading-spinner { width: 44px; height: 44px; border: 3px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.75s linear infinite; }
        .loading-text { font-size: 15px; font-weight: 600; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Toast */
        .toast-container { position: fixed; top: 16px; right: 16px; z-index: 3000; display: flex; flex-direction: column; gap: 8px; }
        .toast { padding: 14px 20px; border-radius: 10px; font-size: 14px; font-weight: 500; box-shadow: 0 4px 12px rgba(0,0,0,0.15); animation: toast-in 0.3s ease; max-width: 380px; }
        .toast.success { background: #2f7d32; color: white; }
        .toast.error { background: var(--destructive); color: white; }
        .toast.warning { background: #b45309; color: white; }
        .toast-desc { font-size: 13px; font-weight: 400; opacity: 0.9; margin-top: 2px; }
        @keyframes toast-in { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes toast-out { from { opacity: 1; } to { opacity: 0; } }

        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 35; }
        .sidebar-overlay.visible { display: block; }

        /* ── Auth Panel (Kütüphaneler) ─────────────────────────────────────── */
        .auth-panel { width: 360px; flex-shrink: 0; border: 1px solid rgba(217,208,194,0.6); border-radius: var(--radius); background: var(--card); box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; position: sticky; top: 24px; max-height: calc(100vh - 80px); }
        .auth-panel-header { padding: 20px 20px 14px; flex-shrink: 0; }
        .auth-panel-title { display: flex; align-items: center; gap: 8px; }
        .auth-panel-title-text { font-family: var(--font-serif); font-size: 17px; font-weight: 700; }
        .auth-panel-title svg { width: 18px; height: 18px; color: var(--primary); }
        .auth-panel-badge { margin-left: auto; background: rgba(122,92,60,0.12); color: var(--primary); font-size: 12px; font-weight: 700; padding: 2px 8px; border-radius: 99px; min-width: 24px; text-align: center; }
        .auth-panel-desc { font-size: 13px; color: var(--muted-foreground); margin-top: 4px; }
        .auth-panel-sep { height: 1px; background: var(--border); }

        /* Kutuphane list */
        .auth-lib-list { flex: 1; overflow-y: auto; padding: 12px; display: flex; flex-direction: column; gap: 8px; min-height: 100px; max-height: 320px; }
        .auth-lib-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: calc(var(--radius) - 2px); background: var(--secondary); border: 1px solid transparent; transition: border-color 0.15s; position: relative; }
        .auth-lib-item:hover { border-color: var(--border); }
        .auth-lib-icon { width: 34px; height: 34px; border-radius: 8px; background: rgba(122,92,60,0.12); color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .auth-lib-icon svg { width: 16px; height: 16px; }
        .auth-lib-info { flex: 1; min-width: 0; }
        .auth-lib-name { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .auth-lib-meta { font-size: 11px; color: var(--muted-foreground); margin-top: 1px; }
        .auth-lib-statu { display: inline-flex; font-size: 10px; font-weight: 600; text-transform: uppercase; padding: 1px 6px; border-radius: 4px; }
        .statu-aktif  { background: rgba(26,107,26,0.1); color: #1a5c1d; }
        .statu-pasif  { background: rgba(197,48,48,0.1); color: #c53030; }
        .auth-remove-btn { width: 26px; height: 26px; border-radius: 6px; border: none; background: transparent; cursor: pointer; color: var(--muted-foreground); display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: background 0.15s, color 0.15s; }
        .auth-remove-btn:hover { background: rgba(197,48,48,0.1); color: var(--destructive); }
        .auth-remove-btn svg { width: 14px; height: 14px; }
        .auth-remove-btn:disabled { opacity: 0.4; cursor: not-allowed; }

        /* Skeleton */
        .auth-skeleton { padding: 10px 12px; border-radius: calc(var(--radius) - 2px); background: var(--secondary); display: flex; align-items: center; gap: 10px; }
        .skeleton-sq { width: 34px; height: 34px; border-radius: 8px; background: var(--border); animation: shimmer 1.4s ease infinite; flex-shrink: 0; }
        .skeleton-lines { flex: 1; display: flex; flex-direction: column; gap: 6px; }
        .skeleton-line { height: 10px; border-radius: 4px; background: var(--border); animation: shimmer 1.4s ease infinite; }
        .skeleton-line.short { width: 55%; }
        @keyframes shimmer { 0%,100% { opacity: 1; } 50% { opacity: 0.45; } }

        /* Empty */
        .auth-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 28px 16px; gap: 8px; color: var(--muted-foreground); text-align: center; }
        .auth-empty svg { width: 34px; height: 34px; opacity: 0.32; }
        .auth-empty-title { font-size: 14px; font-weight: 600; }
        .auth-empty-desc { font-size: 13px; }

        /* Add section */
        .auth-add-section { padding: 12px; flex-shrink: 0; border-top: 1px solid var(--border); }
        .auth-add-title { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--muted-foreground); margin-bottom: 8px; }
        .auth-search-wrap { position: relative; }
        .auth-search-input { width: 100%; padding: 8px 36px 8px 10px; border: 1px solid var(--border); border-radius: calc(var(--radius) - 2px); background: var(--background); font-size: 13px; color: var(--foreground); outline: none; transition: border-color 0.15s, box-shadow 0.15s; }
        .auth-search-input:focus { border-color: var(--ring); box-shadow: 0 0 0 2px rgba(122,92,60,0.15); }
        .auth-search-input::placeholder { color: var(--muted-foreground); opacity: 0.7; }
        .auth-search-icon { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: var(--muted-foreground); pointer-events: none; }
        .auth-search-spinner { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; border: 2px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.7s linear infinite; display: none; }
        .auth-search-spinner.visible { display: block; }
        .auth-results-dropdown { position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: var(--card); border: 1px solid var(--border); border-radius: calc(var(--radius) - 2px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); z-index: 100; overflow: hidden; display: none; max-height: 220px; overflow-y: auto; }
        .auth-results-dropdown.visible { display: block; }
        .auth-result-item { display: flex; align-items: center; gap: 10px; padding: 9px 12px; cursor: pointer; transition: background 0.1s; }
        .auth-result-item:hover { background: var(--secondary); }
        .auth-result-item + .auth-result-item { border-top: 1px solid rgba(217,208,194,0.4); }
        .auth-result-icon { width: 30px; height: 30px; border-radius: 6px; background: rgba(122,92,60,0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .auth-result-icon svg { width: 14px; height: 14px; }
        .auth-result-info { flex: 1; min-width: 0; }
        .auth-result-name { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .auth-result-add-icon { width: 16px; height: 16px; color: var(--primary); flex-shrink: 0; opacity: 0.7; }
        .auth-result-empty { padding: 16px 12px; text-align: center; font-size: 13px; color: var(--muted-foreground); }
        .auth-result-loading { padding: 12px; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px; color: var(--muted-foreground); }

        .auth-lib-item { animation: fadeInUp 0.22s ease; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        .auth-lib-item.removing { animation: fadeOut 0.2s ease forwards; }
        @keyframes fadeOut { to { opacity: 0; transform: translateX(10px); } }

        @media (max-width: 1120px) {
            .edit-layout { flex-direction: column; }
            .auth-panel { width: 100%; position: static; max-height: none; }
            .auth-lib-list { max-height: 260px; }
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-260px); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .content-area { padding: 16px; }
            .form-grid.cols-2, .meta-grid, .role-cards { grid-template-columns: 1fr; }
            .form-actions { flex-direction: column-reverse; }
            .user-hero { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>

<div class="toast-container" id="toastContainer"></div>

<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-box">
        <div class="loading-spinner"></div>
        <span class="loading-text">Güncelleniyor...</span>
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
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Kullanıcılar
                </a>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-current">{{ $user->name }}</span>
            </nav>
        </header>

        <div class="content-area">

            <!-- Kullanıcı özet kartı -->
            <div class="user-hero">
                <div class="user-hero-avatar"
                     style="background:{{ ['admin'=>'#6b4c2a','personel'=>'#2e5e31','okuyucu'=>'#1e3a6b'][$user->role] ?? '#524435' }};">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div style="flex:1;">
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                        <span class="user-hero-name">{{ $user->name }}</span>
                        <span class="role-badge role-{{ $user->role }}">{{ $user->getRoleLabel() }}</span>
                        @if($user->id === auth()->id())
                            <span style="font-size:12px;background:rgba(122,92,60,0.1);color:var(--primary);padding:2px 10px;border-radius:999px;font-weight:600;">Siz</span>
                        @endif
                    </div>
                    <div class="user-hero-email">{{ $user->email }}</div>
                    <div class="user-hero-meta">
                        <span class="user-hero-meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                            Kayıt: {{ \Carbon\Carbon::parse($user->created_at)->format('d.m.Y') }}
                        </span>
                        @if($user->last_login_at)
                            <span class="user-hero-meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            Son giriş: {{ \Carbon\Carbon::parse($user->last_login_at)->format('d.m.Y H:i') }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Two-column layout -->
            <div class="edit-layout">

                <!-- LEFT: Edit Form -->
                <div class="edit-form-wrap">
                    @if(auth()->user()->isAdmin())
                        <div style="display:flex;justify-content:flex-end;gap:10px;margin-bottom:10px;flex-wrap:wrap;">
                            <a href="{{ route('users.yetkiler', $user->id) }}" class="btn btn-outline" style="border-color:rgba(122,92,60,0.35);">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v2"/><path d="M12 20v2"/><path d="M4.93 4.93l1.41 1.41"/><path d="M17.66 17.66l1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="M6.34 17.66l-1.41 1.41"/><path d="M19.07 4.93l-1.41 1.41"/><circle cx="12" cy="12" r="3"/></svg>
                                Yetkileri Yönet
                            </a>
                        </div>
                    @endif
                    <form id="editUserForm" class="form-card" method="POST"
                          action="{{ route('users.update', $user->id) }}" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="form-card-header">
                            <h2 class="form-card-title">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Kullanıcı Düzenle
                            </h2>
                            <p class="form-card-desc">Kullanıcı bilgilerini güncelleyin. Şifreyi boş bırakırsanız değişmez.</p>
                        </div>

                        <div class="form-card-sep"></div>

                        <div class="form-card-body">

                            <!-- Bölüm 1: Kimlik Bilgileri -->
                            <div>
                                <p class="section-label"><span class="section-num">1</span> Kimlik Bilgileri</p>
                                <div class="form-grid cols-2">
                                    <div class="form-field" style="grid-column:span 2;">
                                        <label class="form-label" for="name">Ad Soyad <span class="req">*</span></label>
                                        <div class="input-wrap">
                                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 1 0-16 0"/></svg>
                                            <input type="text" class="form-input has-icon @error('name') is-error @enderror"
                                                   id="name" name="name" value="{{ old('name', $user->name) }}" />
                                        </div>
                                        @error('name')<span class="form-error">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-field" style="grid-column:span 2;">
                                        <label class="form-label" for="email">E-posta Adresi <span class="req">*</span></label>
                                        <div class="input-wrap">
                                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                            <input type="email" class="form-input has-icon @error('email') is-error @enderror"
                                                   id="email" name="email" value="{{ old('email', $user->email) }}" />
                                        </div>
                                        @error('email')<span class="form-error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="section-sep"></div>

                            <!-- Bölüm 2: Şifre -->
                            <div>
                                <p class="section-label"><span class="section-num">2</span> Şifre Değiştir</p>
                                <div class="form-grid cols-2">
                                    <div class="form-field">
                                        <label class="form-label" for="password">Yeni Şifre</label>
                                        <div class="input-wrap">
                                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                            <input type="password" class="form-input has-icon has-icon-right @error('password') is-error @enderror"
                                                   id="password" name="password"
                                                   placeholder="Boş bırakın → değişmez"
                                                   autocomplete="new-password"
                                                   oninput="checkStrength(this.value)" />
                                            <button type="button" class="pw-toggle" onclick="togglePw('password', this)">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </button>
                                        </div>
                                        <div class="pw-strength" id="pwStrength" style="display:none;">
                                            <div class="pw-strength-bar"><div class="pw-strength-fill" id="pwFill"></div></div>
                                            <span class="pw-strength-label" id="pwLabel"></span>
                                        </div>
                                        @error('password')<span class="form-error">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="password_confirmation">Şifre Tekrar</label>
                                        <div class="input-wrap">
                                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                            <input type="password" class="form-input has-icon has-icon-right"
                                                   id="password_confirmation" name="password_confirmation"
                                                   placeholder="Şifreyi tekrar girin"
                                                   autocomplete="new-password" />
                                            <button type="button" class="pw-toggle" onclick="togglePw('password_confirmation', this)">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <p class="form-hint" style="margin-top:8px;">Şifre boş bırakılırsa mevcut şifre korunur. Değiştirmek için en az 8 karakter girin.</p>
                            </div>

                            <div class="section-sep"></div>

                            <!-- Bölüm 3: Rol -->
                            <div>
                                <p class="section-label"><span class="section-num">3</span> Kullanıcı Rolü</p>
                                <div class="role-cards" id="roleCards">
                                    <label class="role-card {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}" id="card-admin">
                                        <input type="radio" name="role" value="admin" {{ old('role', $user->role) === 'admin' ? 'checked' : '' }} />
                                        <div class="role-card-icon" style="background:rgba(90,62,40,0.1);">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#6b4c2a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                        </div>
                                        <div class="role-card-name">Yönetici</div>
                                        <div class="role-card-desc">Tüm sistem ayarlarına ve kullanıcı yönetimine erişim</div>
                                        <div class="role-card-check"></div>
                                    </label>
                                    <label class="role-card {{ old('role', $user->role) === 'personel' ? 'selected' : '' }}" id="card-personel">
                                        <input type="radio" name="role" value="personel" {{ old('role', $user->role) === 'personel' ? 'checked' : '' }} />
                                        <div class="role-card-icon" style="background:rgba(26,107,26,0.1);">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#1a6b1a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                        </div>
                                        <div class="role-card-name">Personel</div>
                                        <div class="role-card-desc">Katalog ve kütüphane yönetimine erişim</div>
                                        <div class="role-card-check"></div>
                                    </label>
                                    <label class="role-card {{ old('role', $user->role) === 'okuyucu' ? 'selected' : '' }}" id="card-okuyucu">
                                        <input type="radio" name="role" value="okuyucu" {{ old('role', $user->role) === 'okuyucu' ? 'checked' : '' }} />
                                        <div class="role-card-icon" style="background:rgba(30,64,175,0.08);">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#1e40af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
                                        </div>
                                        <div class="role-card-name">Okuyucu</div>
                                        <div class="role-card-desc">Yalnızca katalog görüntüleme ve ödünç alma</div>
                                        <div class="role-card-check"></div>
                                    </label>
                                </div>
                                @error('role')<span class="form-error" style="margin-top:8px;display:block;">{{ $message }}</span>@enderror
                            </div>

                            <div class="section-sep"></div>

                            <!-- Bölüm 4: Kayıt Bilgileri -->
                            <div>
                                <p class="section-label"><span class="section-num">4</span> Kayıt Bilgileri</p>
                                <div class="meta-grid">
                                    <div class="meta-item">
                                        <div class="meta-label">Kullanıcı ID</div>
                                        <div class="meta-value">#{{ $user->id }}</div>
                                    </div>
                                    <div class="meta-item">
                                        <div class="meta-label">Kayıt Tarihi</div>
                                        <div class="meta-value">{{ \Carbon\Carbon::parse($user->created_at)->format('d.m.Y H:i') }}</div>
                                    </div>
                                    <div class="meta-item">
                                        <div class="meta-label">Son Güncelleme</div>
                                        <div class="meta-value">{{ $user->updated_at ? \Carbon\Carbon::parse($user->updated_at)->format('d.m.Y H:i') : '—' }}</div>
                                    </div>
                                    <div class="meta-item">
                                        <div class="meta-label">Son Giriş</div>
                                        <div class="meta-value">{{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('d.m.Y H:i') : '—' }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Butonlar -->
                            <div class="form-actions">
                                <button type="button" class="btn btn-outline" id="resetBtn">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                    Sıfırla
                                </button>
                                <a href="{{ route('users.index') }}" class="btn btn-outline">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                                    Geri Dön
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v13a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                    Güncelle
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
                <!-- /LEFT -->

                <!-- RIGHT: Library Authorization Panel -->
                <aside class="auth-panel" id="authPanel" data-user-id="{{ $user->id }}">
                    <div class="auth-panel-header">
                        <div class="auth-panel-title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                            <span class="auth-panel-title-text">Yetkili Kütüphaneler</span>
                            <span class="auth-panel-badge" id="authCount">—</span>
                        </div>
                        <p class="auth-panel-desc">Kullanıcının erişim yetkisi olan kütüphaneler.</p>
                    </div>
                    <div class="auth-panel-sep"></div>

                    <div class="auth-lib-list" id="authLibList">
                        <div class="auth-skeleton" id="authSkeleton1"><div class="skeleton-sq"></div><div class="skeleton-lines"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div></div>
                        <div class="auth-skeleton" id="authSkeleton2"><div class="skeleton-sq"></div><div class="skeleton-lines"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div></div>
                    </div>

                    <div class="auth-add-section">
                        <div class="auth-add-title">Kütüphane Ekle</div>
                        <div class="auth-search-wrap">
                            <input type="text" class="auth-search-input" id="authSearchInput" placeholder="Kütüphane adı ile ara..." autocomplete="off" />
                            <svg class="auth-search-icon" id="authSearchIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            <div class="auth-search-spinner" id="authSearchSpinner"></div>
                            <div class="auth-results-dropdown" id="authResultsDropdown"></div>
                        </div>
                    </div>
                </aside>
                <!-- /RIGHT -->

            </div>
            <!-- /edit-layout -->

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
        setTimeout(function() { t.style.animation = 'toast-out 0.3s ease forwards'; setTimeout(function() { if (t.parentNode) t.parentNode.removeChild(t); }, 300); }, 4000);
    }

    // Orijinal değerler
    var origValues = { name: @json($user->name), email: @json($user->email), role: @json($user->role) };

    // Sıfırla
    document.getElementById('resetBtn').addEventListener('click', function() {
        document.getElementById('name').value  = origValues.name;
        document.getElementById('email').value = origValues.email;
        document.getElementById('password').value = '';
        document.getElementById('password_confirmation').value = '';
        document.getElementById('pwStrength').style.display = 'none';
        document.querySelectorAll('#roleCards .role-card').forEach(function(c) { c.classList.remove('selected'); });
        var origCard = document.getElementById('card-' + origValues.role);
        if (origCard) { origCard.classList.add('selected'); origCard.querySelector('input').checked = true; }
        showToast('success', 'Sıfırlandı', 'Alanlar orijinal değerlerine döndürüldü.');
    });

    // Rol kartı seçimi
    document.querySelectorAll('#roleCards .role-card').forEach(function(card) {
        card.addEventListener('click', function() {
            document.querySelectorAll('#roleCards .role-card').forEach(function(c) { c.classList.remove('selected'); });
            card.classList.add('selected');
        });
    });

    // Şifre göster/gizle
    var eyeOpen   = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
    var eyeClosed = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>';
    function togglePw(id, btn) {
        var input = document.getElementById(id);
        if (input.type === 'password') { input.type = 'text'; btn.innerHTML = eyeClosed; }
        else { input.type = 'password'; btn.innerHTML = eyeOpen; }
    }

    // Şifre güç
    function checkStrength(pw) {
        var el = document.getElementById('pwStrength');
        var fill = document.getElementById('pwFill');
        var label = document.getElementById('pwLabel');
        if (!pw) { el.style.display = 'none'; return; }
        el.style.display = 'block';
        var score = 0;
        if (pw.length >= 8) score++;
        if (/[A-Z]/.test(pw)) score++;
        if (/[0-9]/.test(pw)) score++;
        if (/[^A-Za-z0-9]/.test(pw)) score++;
        var levels = [
            { pct: '20%', color: '#c53030', text: 'Çok Zayıf' },
            { pct: '40%', color: '#e07b1c', text: 'Zayıf' },
            { pct: '65%', color: '#d4ac0d', text: 'Orta' },
            { pct: '100%', color: '#2f7d32', text: 'Güçlü' },
        ];
        var l = levels[Math.min(score - 1, 3)] || levels[0];
        fill.style.width = l.pct; fill.style.background = l.color;
        label.textContent = l.text; label.style.color = l.color;
    }

    // AJAX Submit
    var form = document.getElementById('editUserForm');
    var submitBtn = document.getElementById('submitBtn');
    var submitBtnHtml = submitBtn.innerHTML;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var pw  = document.getElementById('password').value;
        var pwc = document.getElementById('password_confirmation').value;
        if (pw && pw !== pwc) { showToast('error', 'Hata', 'Şifre tekrarı uyuşmuyor.'); return; }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin 0.8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Güncelleniyor...';
        document.getElementById('loadingOverlay').classList.add('visible');

        fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: new FormData(form),
        })
            .then(function(res) { return res.json().then(function(data) { return { status: res.status, data: data }; }); })
            .then(function(result) {
                if (result.status === 200 && result.data.success) {
                    showToast('success', 'Güncelleme Başarılı', result.data.message);
                    origValues.name  = document.getElementById('name').value.trim();
                    origValues.email = document.getElementById('email').value.trim();
                    var checkedRole = document.querySelector('#roleCards input[type=radio]:checked');
                    if (checkedRole) origValues.role = checkedRole.value;
                    document.getElementById('password').value = '';
                    document.getElementById('password_confirmation').value = '';
                    document.getElementById('pwStrength').style.display = 'none';
                } else if (result.status === 422 && result.data.errors) {
                    var msgs = Object.values(result.data.errors).flat();
                    showToast('error', 'Doğrulama Hatası', msgs[0]);
                } else {
                    showToast('error', 'Hata', result.data.message || 'Güncelleme sırasında bir hata oluştu.');
                }
            })
            .catch(function() { showToast('error', 'Bağlantı Hatası', 'Sunucuya ulaşılamadı.'); })
            .finally(function() {
                submitBtn.disabled = false;
                submitBtn.innerHTML = submitBtnHtml;
                document.getElementById('loadingOverlay').classList.remove('visible');
            });
    });

    // ════════════════════════════════════════════════════════════════════════════
    // ── Library Authorization Panel ─────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════════

    var userId        = document.getElementById('authPanel').dataset.userId;
    var csrfToken     = document.querySelector('meta[name="csrf-token"]').content;
    var authLibList   = document.getElementById('authLibList');
    var authCount     = document.getElementById('authCount');
    var searchInput   = document.getElementById('authSearchInput');
    var dropdown      = document.getElementById('authResultsDropdown');
    var searchSpinner = document.getElementById('authSearchSpinner');
    var searchIcon    = document.getElementById('authSearchIcon');

    function escHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str || ''));
        return div.innerHTML;
    }
    function formatDate(str) {
        if (!str) return '—';
        return new Date(str).toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function removeSkeleton() {
        ['authSkeleton1','authSkeleton2'].forEach(function(id) { var el = document.getElementById(id); if (el) el.remove(); });
    }

    function renderLibList(libs) {
        removeSkeleton();
        authLibList.innerHTML = '';
        if (!libs || libs.length === 0) {
            authCount.textContent = '0';
            authLibList.innerHTML =
                '<div class="auth-empty">' +
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>' +
                '<div class="auth-empty-title">Henüz yetki yok</div>' +
                '<div class="auth-empty-desc">Aşağıdan kütüphane arayarak ekleyebilirsiniz.</div>' +
                '</div>';
            return;
        }
        authCount.textContent = libs.length;
        libs.forEach(function(lib) { appendLibItem(lib); });
    }

    function appendLibItem(lib) {
        var emptyEl = authLibList.querySelector('.auth-empty');
        if (emptyEl) emptyEl.remove();

        var div = document.createElement('div');
        div.className = 'auth-lib-item';
        div.dataset.yetkiliId = lib.id;
        div.innerHTML =
            '<div class="auth-lib-icon">' +
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>' +
            '</div>' +
            '<div class="auth-lib-info">' +
            '<div class="auth-lib-name">' + escHtml(lib.title) + ' <span class="auth-lib-statu statu-' + lib.statu + '">' + lib.statu + '</span></div>' +
            '<div class="auth-lib-meta">Eklendi: ' + formatDate(lib.created_at) + (lib.created_by_name ? ' · ' + escHtml(lib.created_by_name) : '') + '</div>' +
            '</div>' +
            '<button class="auth-remove-btn" title="Yetkiyi Kaldır" onclick="removeYetkili(this, ' + lib.id + ')">' +
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>' +
            '</button>';
        authLibList.appendChild(div);
    }

    // Load list
    function loadYetkiliKutuphaneler() {
        fetch('/kullanicilar/' + userId + '/yetkili', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) renderLibList(data.data);
                else { removeSkeleton(); showToast('error', 'Hata', 'Kütüphane listesi yüklenemedi.'); }
            })
            .catch(function() { removeSkeleton(); showToast('error', 'Bağlantı Hatası', 'Kütüphane listesi yüklenemedi.'); });
    }

    // Remove
    function removeYetkili(btn, yetkiliId) {
        if (!confirm('Bu kütüphane yetkisini kaldırmak istediğinize emin misiniz?')) return;
        btn.disabled = true;
        var item = btn.closest('.auth-lib-item');
        fetch('/kullanicilar/' + userId + '/yetkili/' + yetkiliId, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
        })
            .then(function(res) { return res.json().then(function(d) { return { status: res.status, data: d }; }); })
            .then(function(result) {
                if (result.data.success) {
                    item.classList.add('removing');
                    setTimeout(function() {
                        item.remove();
                        var remaining = authLibList.querySelectorAll('.auth-lib-item').length;
                        authCount.textContent = remaining;
                        if (remaining === 0) renderLibList([]);
                    }, 200);
                    showToast('success', 'Yetki Kaldırıldı', result.data.message);
                } else {
                    btn.disabled = false;
                    showToast('error', 'Hata', result.data.message || 'İşlem başarısız.');
                }
            })
            .catch(function() { btn.disabled = false; showToast('error', 'Bağlantı Hatası', 'İşlem gerçekleştirilemedi.'); });
    }

    // Search
    var searchTimer = null;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimer);
        var q = this.value.trim();
        if (q.length < 2) { dropdown.classList.remove('visible'); dropdown.innerHTML = ''; return; }
        searchTimer = setTimeout(function() {
            searchIcon.style.display = 'none';
            searchSpinner.classList.add('visible');
            dropdown.classList.add('visible');
            dropdown.innerHTML = '<div class="auth-result-loading"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;animation:spin 0.7s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Aranıyor...</div>';
            fetch('/kullanicilar/' + userId + '/yetkili/search?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    searchIcon.style.display = '';
                    searchSpinner.classList.remove('visible');
                    renderSearchResults(data.data || []);
                })
                .catch(function() {
                    searchIcon.style.display = '';
                    searchSpinner.classList.remove('visible');
                    dropdown.innerHTML = '<div class="auth-result-empty">Arama başarısız.</div>';
                });
        }, 350);
    });

    function renderSearchResults(libs) {
        if (libs.length === 0) {
            dropdown.innerHTML = '<div class="auth-result-empty">Kütüphane bulunamadı veya tümü zaten yetkili.</div>';
            return;
        }
        dropdown.innerHTML = '';
        libs.forEach(function(lib) {
            var div = document.createElement('div');
            div.className = 'auth-result-item';
            div.innerHTML =
                '<div class="auth-result-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg></div>' +
                '<div class="auth-result-info"><div class="auth-result-name">' + escHtml(lib.title) + '</div></div>' +
                '<svg class="auth-result-add-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>';
            div.addEventListener('click', function() { addYetkili(lib); });
            dropdown.appendChild(div);
        });
    }

    // Add
    function addYetkili(lib) {
        dropdown.classList.remove('visible');
        dropdown.innerHTML = '';
        searchInput.value = '';
        fetch('/kullanicilar/' + userId + '/yetkili', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ kutuphane_id: lib.id })
        })
            .then(function(res) { return res.json().then(function(d) { return { status: res.status, data: d }; }); })
            .then(function(result) {
                if (result.data.success) {
                    appendLibItem(result.data.data);
                    authCount.textContent = authLibList.querySelectorAll('.auth-lib-item').length;
                    showToast('success', 'Yetki Eklendi', result.data.message);
                } else {
                    showToast('warning', 'Uyarı', result.data.message || 'Kütüphane eklenemedi.');
                }
            })
            .catch(function() { showToast('error', 'Bağlantı Hatası', 'İşlem gerçekleştirilemedi.'); });
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.auth-search-wrap')) { dropdown.classList.remove('visible'); }
    });

    // Boot
    loadYetkiliKutuphaneler();
</script>
</body>
</html>
