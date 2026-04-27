<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#7a5c3c" />
    <title>Kütüphane Bilgi Sistemi</title>
    <meta name="description" content="Modern kutuphane yonetim ve otomasyon sistemi. Kitap kaydi, odunc verme ve envanter yonetimi." />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700;900&family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --background: #f5f0e8;
            --foreground: #3d3226;
            --card: #faf8f3;
            --card-foreground: #3d3226;
            --primary: #7a5c3c;
            --primary-foreground: #f5f0e8;
            --secondary: #ede8de;
            --secondary-foreground: #4a3c2e;
            --muted: #ede8de;
            --muted-foreground: #7a7060;
            --accent: #9b6b3f;
            --accent-foreground: #f5f0e8;
            --destructive: #c53030;
            --border: #d9d0c2;
            --input: #e2dbd0;
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

        /* ============= Reset & Base ============= */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font-sans);
            background: var(--background);
            color: var(--foreground);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            line-height: 1.5;
        }

        input, select, textarea, button { font-family: inherit; font-size: inherit; }

        /* ============= Layout ============= */
        .app-layout {
            display: flex;
            min-height: 100vh;
        }

        /* ============= Sidebar ============= */
        .sidebar {
            width: 260px;
            background: var(--sidebar);
            color: var(--sidebar-foreground);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            border-right: 1px solid var(--sidebar-border);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 40;
            transition: transform 0.3s ease;
        }

        .sidebar.collapsed {
            transform: translateX(-260px);
        }

        .sidebar-header {
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-logo {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: var(--sidebar-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .sidebar-logo svg { width: 20px; height: 20px; color: var(--sidebar-primary-foreground); }

        .sidebar-brand-name {
            font-family: var(--font-sans);
            font-size: 16px;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        .sidebar-brand-sub {
            font-size: 12px;
            opacity: 0.6;
        }

        .sidebar-separator {
            height: 1px;
            background: var(--sidebar-border);
            margin: 0 16px;
        }

        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            padding: 8px 0;
        }

        .sidebar-group { padding: 8px 12px; }

        .sidebar-group-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--sidebar-foreground);
            opacity: 0.5;
            padding: 4px 8px;
            margin-bottom: 4px;
        }

        .sidebar-menu { list-style: none; }

        .sidebar-menu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            color: var(--sidebar-foreground);
            cursor: pointer;
            transition: background 0.15s ease;
            text-decoration: none;
        }

        .sidebar-menu-item:hover { background: var(--sidebar-accent); }

        .sidebar-menu-item.active {
            background: var(--sidebar-accent);
            color: var(--sidebar-accent-foreground);
        }

        .sidebar-menu-item svg { width: 18px; height: 18px; flex-shrink: 0; opacity: 0.8; }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--sidebar-border);
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--sidebar-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 600;
            flex-shrink: 0;
        }

        .sidebar-user-name { font-size: 14px; font-weight: 500; }
        .sidebar-user-role { font-size: 12px; opacity: 0.6; }

        /* ============= Main Content ============= */
        .main-content {
            flex: 1;
            margin-left: 260px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background: var(--background);
            transition: margin-left 0.3s ease;
        }

        .main-content.expanded { margin-left: 0; }

        /* ============= Header ============= */
        .top-header {
            height: 56px;
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 0 16px;
            border-bottom: 1px solid rgba(217, 208, 194, 0.6);
            background: var(--card);
            flex-shrink: 0;
        }

        .sidebar-trigger {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: none;
            background: transparent;
            cursor: pointer;
            color: var(--foreground);
            transition: background 0.15s;
        }

        .sidebar-trigger:hover { background: var(--muted); }
        .sidebar-trigger svg { width: 18px; height: 18px; }

        .header-separator {
            width: 1px;
            height: 20px;
            background: var(--border);
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .breadcrumb-link {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--muted-foreground);
            text-decoration: none;
            transition: color 0.15s;
        }

        .breadcrumb-link:hover { color: var(--foreground); }
        .breadcrumb-link svg { width: 14px; height: 14px; }

        .breadcrumb-sep { color: var(--muted-foreground); opacity: 0.5; font-size: 12px; }

        .breadcrumb-current { font-weight: 500; color: var(--foreground); }

        .header-actions { margin-left: auto; }

        .notification-btn {
            position: relative;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: none;
            background: transparent;
            cursor: pointer;
            color: var(--foreground);
            transition: background 0.15s;
        }

        .notification-btn:hover { background: var(--muted); }
        .notification-btn svg { width: 16px; height: 16px; }

        .notification-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: var(--primary);
            color: var(--primary-foreground);
            font-size: 10px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ============= Content Area ============= */
        .content-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 24px;
            padding: 24px;
        }

        /* ============= Stats ============= */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }

        .stat-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px;
            border-radius: 12px;
            border: 1px solid rgba(217, 208, 194, 0.6);
            background: var(--card);
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: rgba(122, 92, 60, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-icon svg { width: 20px; height: 20px; color: var(--primary); }

        .stat-value {
            font-size: 18px;
            font-weight: 700;
            line-height: 1.2;
            color: var(--foreground);
        }

        .stat-label { font-size: 12px; color: var(--muted-foreground); }

        /* ============= Form Card ============= */
        .form-card {
            border: 1px solid rgba(217, 208, 194, 0.6);
            border-radius: var(--radius);
            background: var(--card);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            overflow: hidden;
        }

        .form-card-header {
            padding: 24px 24px 16px;
        }

        .form-card-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: var(--font-serif);
            font-size: 20px;
            font-weight: 700;
            color: var(--foreground);
        }

        .form-card-title svg { width: 20px; height: 20px; color: var(--primary); }

        .form-card-desc {
            font-size: 14px;
            color: var(--muted-foreground);
            line-height: 1.6;
            margin-top: 4px;
        }

        .form-card-separator {
            height: 1px;
            background: var(--border);
        }

        .form-card-body { padding: 24px; }

        /* ============= Form Layout ============= */
        .form-layout {
            display: flex;
            gap: 32px;
        }

        /* Left: Cover Upload */
        .cover-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            width: 300px;
            flex-shrink: 0;
        }

        .cover-section-title {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--muted-foreground);
        }

        .cover-upload-area {
            position: relative;
            width: 100%;
            max-width: 280px;
            aspect-ratio: 2/3;
            border: 2px dashed var(--border);
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: border-color 0.2s, background 0.2s, transform 0.2s;
            overflow: hidden;
        }

        .cover-upload-area:hover {
            border-color: rgba(122, 92, 60, 0.5);
            background: rgba(237, 232, 222, 0.5);
        }

        .cover-upload-area.drag-over {
            border-color: var(--primary);
            background: rgba(122, 92, 60, 0.05);
            transform: scale(1.02);
        }

        .cover-upload-area.has-image {
            border: 1px solid var(--border);
            border-style: solid;
        }

        .cover-upload-area img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cover-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            padding: 24px;
            text-align: center;
        }

        .cover-icon-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: var(--muted);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cover-icon-circle svg { width: 32px; height: 32px; color: var(--muted-foreground); }

        .cover-text-primary { font-size: 14px; font-weight: 500; color: var(--foreground); }
        .cover-text-secondary { font-size: 12px; color: var(--muted-foreground); line-height: 1.6; }
        .cover-text-hint { font-size: 12px; color: var(--muted-foreground); opacity: 0.7; }

        .cover-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 8px;
            background: rgba(122, 92, 60, 0.1);
            margin-top: 4px;
        }

        .cover-btn svg { width: 16px; height: 16px; color: var(--primary); }
        .cover-btn span { font-size: 12px; font-weight: 500; color: var(--primary); }

        .cover-remove-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: none;
            background: var(--destructive);
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            z-index: 2;
        }

        .cover-remove-btn svg { width: 16px; height: 16px; }

        .cover-change-hint { font-size: 12px; color: var(--muted-foreground); }

        /* Right: Form Fields */
        .fields-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* Section Header */
        .section-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--muted-foreground);
        }

        .section-number {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            background: rgba(122, 92, 60, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            color: var(--primary);
        }

        /* Form Grid */
        .form-grid { display: grid; gap: 16px; }
        .form-grid.cols-2 { grid-template-columns: repeat(2, 1fr); }
        .form-grid.cols-3 { grid-template-columns: repeat(3, 1fr); }
        .form-grid .span-2 { grid-column: span 2; }

        /* Form Fields */
        .form-field { display: flex; flex-direction: column; }

        .form-label {
            font-size: 14px;
            font-weight: 500;
            color: var(--foreground);
            margin-bottom: 6px;
        }

        .form-label .required { color: var(--destructive); }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: calc(var(--radius) - 2px);
            background: var(--card);
            color: var(--foreground);
            font-size: 14px;
            line-height: 1.5;
            transition: border-color 0.15s, box-shadow 0.15s;
            outline: none;
        }

        .form-input::placeholder, .form-textarea::placeholder {
            color: var(--muted-foreground);
            opacity: 0.7;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            border-color: var(--ring);
            box-shadow: 0 0 0 2px rgba(122, 92, 60, 0.15);
        }

        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237a7060' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 36px;
        }

        .form-textarea { resize: none; }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon .form-input { padding-right: 40px; }

        .input-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted-foreground);
            pointer-events: none;
        }

        .input-icon svg { width: 16px; height: 16px; }

        /* Section Separator */
        .section-sep {
            height: 1px;
            background: var(--border);
        }

        /* Buttons */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding-top: 8px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 9px 16px;
            border-radius: calc(var(--radius) - 2px);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.15s, opacity 0.15s;
            border: none;
            text-decoration: none;
        }

        .btn svg { width: 16px; height: 16px; }

        .btn-primary {
            background: var(--primary);
            color: var(--primary-foreground);
        }

        .btn-primary:hover { opacity: 0.9; }

        .btn-outline {
            background: transparent;
            color: var(--foreground);
            border: 1px solid var(--border);
        }

        .btn-outline:hover { background: var(--muted); }

        /* ============= Toast ============= */
        .toast-container {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .toast {
            padding: 14px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: toast-in 0.3s ease;
            max-width: 380px;
        }

        .toast.success { background: #2f7d32; color: white; }
        .toast.error { background: var(--destructive); color: white; }

        .toast-desc { font-size: 13px; font-weight: 400; opacity: 0.9; margin-top: 2px; }

        @keyframes toast-in {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes toast-out {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }

        /* ============= Combobox ============= */
        .combobox-wrapper {
            position: relative;
        }

        .combobox-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
            border: 1px solid var(--border);
            border-radius: calc(var(--radius) - 2px);
            background: var(--card);
            min-height: 38px;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .combobox-input-wrap:focus-within {
            border-color: var(--ring);
            box-shadow: 0 0 0 2px rgba(122, 92, 60, 0.15);
        }
        .combobox-input-wrap .form-input {
            border: none !important;
            box-shadow: none !important;
            background: transparent !important;
            flex: 1;
            min-width: 0;
            padding-right: 8px;
        }
        /* Face — seçili değer göstergesi (dropdown kapalıyken) */
        .combobox-face {
            flex: 1;
            display: flex;
            align-items: center;
            padding: 0 0 0 12px;
            cursor: pointer;
            min-width: 0;
            gap: 4px;
            height: 100%;
            min-height: 36px;
        }
        .combobox-face-text {
            flex: 1;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--muted-foreground);
            opacity: 0.7;
            user-select: none;
        }
        .combobox-face-text.is-selected {
            color: var(--foreground);
            opacity: 1;
        }
        /* X butonu */
        .combobox-clear-btn {
            flex-shrink: 0;
            width: 18px; height: 18px;
            border: none; background: transparent;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: var(--muted-foreground);
            border-radius: 3px;
            padding: 0;
            transition: background 0.12s, color 0.12s;
        }
        .combobox-clear-btn:hover { background: var(--muted); color: var(--foreground); }
        .combobox-clear-btn svg { width: 11px; height: 11px; }

        .combobox-toggle {
            position: absolute;
            right: 1px;
            top: 1px;
            bottom: 1px;
            width: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            cursor: pointer;
            color: var(--muted-foreground);
            border-radius: 0 calc(var(--radius) - 2px) calc(var(--radius) - 2px) 0;
            transition: color 0.15s, background 0.15s;
        }

        .combobox-toggle:hover {
            color: var(--foreground);
            background: var(--muted);
        }

        .combobox-toggle svg {
            width: 14px;
            height: 14px;
            transition: transform 0.2s;
        }

        .combobox-toggle.open svg {
            transform: rotate(180deg);
        }

        .combobox-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: calc(var(--radius) - 2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.10), 0 1px 4px rgba(0, 0, 0, 0.06);
            z-index: 50;
            max-height: 220px;
            overflow-y: auto;
            display: none;
            padding: 4px;
        }

        .combobox-dropdown.visible {
            display: block;
            animation: combo-in 0.15s ease;
        }

        @keyframes combo-in {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .combobox-option {
            padding: 8px 10px;
            font-size: 14px;
            color: var(--foreground);
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.1s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .combobox-option:hover,
        .combobox-option.highlighted {
            background: var(--muted);
        }

        .combobox-option.selected {
            font-weight: 600;
            color: var(--primary);
        }

        .combobox-option .check-icon {
            width: 14px;
            height: 14px;
            color: var(--primary);
            flex-shrink: 0;
            visibility: hidden;
        }

        .combobox-option.selected .check-icon {
            visibility: visible;
        }

        .combobox-no-result {
            padding: 12px 10px;
            font-size: 13px;
            color: var(--muted-foreground);
            text-align: center;
        }

        .combobox-hint {
            padding: 6px 10px;
            font-size: 11px;
            color: var(--muted-foreground);
            border-top: 1px solid var(--border);
            margin-top: 2px;
            text-align: center;
            opacity: 0.8;
        }

        /* ============= Mobile Overlay ============= */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 35;
        }

        .sidebar-overlay.visible { display: block; }

        /* ============= Responsive ============= */
        @media (max-width: 1024px) {
            .form-layout { flex-direction: column; }
            .cover-section {
                width: 100%;
                max-width: none;
            }
            .cover-upload-area { max-width: 280px; }
            .form-grid.cols-3 { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-260px);
            }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .content-area { padding: 16px; }
            .form-grid.cols-2, .form-grid.cols-3 { grid-template-columns: 1fr; }
            .form-grid .span-2 { grid-column: span 1; }
            .form-actions { flex-direction: column-reverse; }
            .form-actions .btn { width: 100%; }
        }

        /* ============= Custom Pagination ============= */
        .pagination-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            border-top: 1px solid var(--border);
            flex-wrap: wrap;
            gap: 12px;
        }

        .pagination-info {
            font-size: 13px;
            color: var(--muted-foreground);
        }

        .pagination-info strong {
            color: var(--foreground);
            font-weight: 600;
        }

        .pagination-nav {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .page-btn {
            min-width: 36px;
            height: 36px;
            padding: 0 10px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--card);
            color: var(--foreground);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.15s ease;
            text-decoration: none;
            font-family: var(--font-sans);
        }

        .page-btn:hover:not(:disabled):not(.active) {
            background: var(--secondary);
            border-color: var(--primary);
            color: var(--primary);
        }

        .page-btn.active {
            background: var(--primary);
            border-color: var(--primary);
            color: var(--primary-foreground);
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(122, 92, 60, 0.35);
        }

        .page-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            pointer-events: none;
        }

        .page-btn.nav-btn {
            background: var(--secondary);
            color: var(--foreground);
        }

        .page-btn.nav-btn:hover:not(:disabled) {
            background: var(--primary);
            border-color: var(--primary);
            color: var(--primary-foreground);
        }

        .page-btn svg {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
        }

        .page-ellipsis {
            min-width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--muted-foreground);
            font-size: 13px;
        }

        /* ── Table footer / per-page ── */
        .table-footer { padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; gap: 12px; border-top: 1px solid var(--border); font-size: 13px; color: var(--muted-foreground); flex-wrap: wrap; }
        .tf-info { display: flex; align-items: center; gap: 12px; }
        .per-page-wrap { display: flex; align-items: center; gap: 6px; font-size: 13px; }
        .per-page-select { padding: 4px 28px 4px 8px; border: 1px solid var(--border); border-radius: calc(var(--radius) - 2px); background: var(--card); color: var(--foreground); font-size: 13px; outline: none; cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%237a7060' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 8px center; transition: border-color 0.15s; }
        .per-page-select:focus { border-color: var(--ring); }

        /* Loading overlay for table */
        .table-loading-wrapper {
            position: relative;
        }
        .filter-row-extra { display: none; }
        .form-card-body.filters-expanded .filter-row-extra { display: grid; }
        .filter-toggle-row {
            margin-top: -2px;
            margin-bottom: 14px;
        }
        .more-filters-toggle {
            border: 0;
            background: transparent;
            color: var(--primary);
            font-weight: 600;
            cursor: pointer;
            padding: 0;
            text-decoration: underline;
            text-underline-offset: 2px;
        }
        .more-filters-toggle:hover { color: var(--accent); }

        .table-loading-overlay {
            display: none;
            position: absolute;
            inset: 0;
            background: rgba(245, 240, 232, 0.7);
            z-index: 10;
            align-items: center;
            justify-content: center;
            border-radius: 0 0 var(--radius) var(--radius);
        }

        .table-loading-overlay.visible {
            display: flex;
        }

        .spinner {
            width: 32px;
            height: 32px;
            border: 3px solid var(--border);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 600px) {
            .pagination-wrapper {
                flex-direction: column;
                align-items: flex-start;
            }
            .pagination-nav { flex-wrap: wrap; }
        }

        /* ── Satır aksiyon dropdown ── */
        .row-actions-btn { width: 32px; height: 32px; border-radius: 6px; border: none; background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--muted-foreground); transition: background 0.15s, color 0.15s; }
        .row-actions-btn:hover { background: var(--muted); color: var(--foreground); }
        .row-actions-btn svg { width: 16px; height: 16px; pointer-events: none; }
        /* Floating menü — body'e eklenir, overflow:hidden'dan etkilenmez */
        #kitapFloatingMenu { position: fixed; background: var(--card); border: 1px solid var(--border); border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,0.14); min-width: 155px; z-index: 9999; padding: 4px; display: none; }
        #kitapFloatingMenu.open { display: block; animation: menu-in 0.15s ease; }
        @keyframes menu-in { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }
        .row-actions-item { display: flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 6px; font-size: 14px; font-weight: 500; color: var(--foreground); text-decoration: none; cursor: pointer; transition: background 0.12s; white-space: nowrap; border: none; background: transparent; width: 100%; text-align: left; font-family: inherit; }
        .row-actions-item:hover { background: var(--secondary); }
        .row-actions-item svg { width: 15px; height: 15px; flex-shrink: 0; color: var(--muted-foreground); }
    </style>
</head>
<body>

<div class="toast-container" id="toastContainer"></div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="app-layout">

    @include('partials.sidebar')

    <!-- ====== MAIN CONTENT ====== -->
    <main class="main-content" id="mainContent">
        <!-- Header -->
        <header class="top-header">
            <button class="sidebar-trigger" id="sidebarToggle" aria-label="Sidebar toggle">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/></svg>
            </button>
            <div class="header-separator"></div>
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <a href="#" class="breadcrumb-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
                    Katalog
                </a>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-current">Kitap Kayıt</span>
            </nav>
            <div class="header-actions">
                <button class="notification-btn" aria-label="Bildirimler">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                    <span class="notification-badge">3</span>
                </button>
            </div>
        </header>

        <!-- Content -->
        <div class="content-area">
        
            <div class="form-card">
                <div class="form-card-header">
                    <h2 class="form-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        Filtrele
                    </h2>
                </div>
                <div class="form-card-body">

                    {{-- Satır 1: Eser Adı/ISBN + Yazar + Yayınevi + Kütüphane --}}
                    <div class="form-grid cols-3 filter-row-primary" style="margin-bottom:14px;grid-template-columns:repeat(4,1fr);">
                        <div class="form-field">
                            <label class="form-label">Eser Adı / Demirbaş / ISBN</label>
                            <input type="text" id="filterSearch" class="form-input" placeholder="Eser adı veya ISBN..." autocomplete="off">
                        </div>
                        <div class="form-field">
                            <label class="form-label">Yazar</label>
                            <div class="combobox-wrapper" id="filterYazarCombobox">
                                <div class="combobox-input-wrap">
                                    <div class="combobox-face" id="filterYazarFace">
                                        <span class="combobox-face-text" id="filterYazarFaceText">Yazar seçin...</span>
                                        <button type="button" class="combobox-clear-btn" id="filterYazarClear" title="Seçimi kaldır" style="display:none;"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                                    </div>
                                    <input type="text" class="form-input" id="filterYazarSearch" placeholder="Yazar ara..." autocomplete="off" style="display:none;" />
                                    <button type="button" class="combobox-toggle" tabindex="-1"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg></button>
                                </div>
                                <div class="combobox-dropdown"></div>
                            </div>
                            <input type="hidden" id="filterYazar" value="" />
                            <script id="filterYazarData" type="application/json">@json($yazarlar->map(fn($y) => ['id' => $y->id, 'ad' => $y->ad]))</script>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Yayınevi</label>
                            <div class="combobox-wrapper" id="filterYayineviCombobox">
                                <div class="combobox-input-wrap">
                                    <div class="combobox-face" id="filterYayineviFace">
                                        <span class="combobox-face-text" id="filterYayineviFaceText">Yayınevi seçin...</span>
                                        <button type="button" class="combobox-clear-btn" id="filterYayineviClear" title="Seçimi kaldır" style="display:none;"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                                    </div>
                                    <input type="text" class="form-input" id="filterYayineviSearch" placeholder="Yayınevi ara..." autocomplete="off" style="display:none;" />
                                    <button type="button" class="combobox-toggle" tabindex="-1"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg></button>
                                </div>
                                <div class="combobox-dropdown"></div>
                            </div>
                            <input type="hidden" id="filterYayinevi" value="" />
                            <script id="filterYayineviData" type="application/json">@json($yayinevleri->map(fn($y) => ['id' => $y->id, 'ad' => $y->ad]))</script>
                        </div>
                        {{-- Kütüphane — arama destekli combobox --}}
                        <div class="form-field">
                            <label class="form-label">Kütüphane</label>
                            <div class="combobox-wrapper" id="filterKutuphaneCombobox">
                                <div class="combobox-input-wrap">
                                    <div class="combobox-face" id="filterKutuphaneFace">
                                        <span class="combobox-face-text" id="filterKutuphaneFaceText">Kütüphane seçin...</span>
                                        <button type="button" class="combobox-clear-btn" id="filterKutuphaneClear" title="Seçimi kaldır" style="display:none;"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                                    </div>
                                    <input type="text" class="form-input" id="filterKutuphaneSearch" placeholder="Kütüphane ara..." autocomplete="off" style="display:none;" />
                                    <button type="button" class="combobox-toggle" tabindex="-1"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg></button>
                                </div>
                                <div class="combobox-dropdown"></div>
                            </div>
                            <input type="hidden" id="filterKutuphane" value="" />
                            <script id="filterKutuphaneData" type="application/json">@json($kutuphaneler->map(fn($k) => ['id' => $k->id, 'ad' => $k->title]))</script>
                        </div>
                    </div>

                    <div class="filter-toggle-row filter-row-primary">
                        <button type="button" class="more-filters-toggle" id="toggleMoreFilters" aria-expanded="false">Daha fazla filtre</button>
                    </div>

                    {{-- Satır 2: Kategori + Tür + Sınıflama/Yer Kodu + Durum --}}
                    <div class="form-grid cols-3 filter-row-extra" style="margin-bottom:14px;grid-template-columns:repeat(4,1fr);">
                        {{-- Kategori — arama destekli combobox --}}
                        <div class="form-field">
                            <label class="form-label">Kategori</label>
                            <div class="combobox-wrapper" id="filterKategoriCombobox">
                                <div class="combobox-input-wrap">
                                    <div class="combobox-face" id="filterKategoriFace">
                                        <span class="combobox-face-text" id="filterKategoriFaceText">Kategori seçin...</span>
                                        <button type="button" class="combobox-clear-btn" id="filterKategoriClear" title="Seçimi kaldır" style="display:none;"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                                    </div>
                                    <input type="text" class="form-input" id="filterKategoriSearch" placeholder="Kategori ara..." autocomplete="off" style="display:none;" />
                                    <button type="button" class="combobox-toggle" tabindex="-1"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg></button>
                                </div>
                                <div class="combobox-dropdown"></div>
                            </div>
                            <input type="hidden" id="filterKategori" value="" />
                            <script id="filterKategoriData" type="application/json">@json($kategoriler->map(fn($k) => ['id' => $k->id, 'ad' => $k->title]))</script>
                        </div>
                        {{-- Tür — arama destekli combobox --}}
                        <div class="form-field">
                            <label class="form-label">Tür</label>
                            <div class="combobox-wrapper" id="filterTurCombobox">
                                <div class="combobox-input-wrap">
                                    <div class="combobox-face" id="filterTurFace">
                                        <span class="combobox-face-text" id="filterTurFaceText">Tür seçin...</span>
                                        <button type="button" class="combobox-clear-btn" id="filterTurClear" title="Seçimi kaldır" style="display:none;"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                                    </div>
                                    <input type="text" class="form-input" id="filterTurSearch" placeholder="Tür ara..." autocomplete="off" style="display:none;" />
                                    <button type="button" class="combobox-toggle" tabindex="-1"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg></button>
                                </div>
                                <div class="combobox-dropdown"></div>
                            </div>
                            <input type="hidden" id="filterTur" value="" />
                            <script id="filterTurData" type="application/json">@json($turler->map(fn($t) => ['id' => $t->id, 'ad' => $t->ad]))</script>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Sınıflama / Yer Kodu</label>
                            <input type="text" id="filterSiniflamaYer" class="form-input" placeholder="Ör: 914.3, FEN-001..." autocomplete="off">
                        </div>
                        <div class="form-field">
                            <label class="form-label">Durum</label>
                            <select id="filterDurum" class="form-select">
                                <option value="">Tümü</option>
                                <option value="Rafta">Rafta (Müsait)</option>
                                <option value="Ödünç">Ödünç Verildi</option>
                                <option value="Rezerve">Rezerve Edildi</option>
                                <option value="Kayıp">Kayıp</option>
                                <option value="Bakımda">Bakımda / Onarımda</option>
                                <option value="Hurdaya Ayrıldı">Hurdaya Ayrıldı</option>
                            </select>
                        </div>
                    </div>

                    {{-- Satır 3: Dil + Konu Başlığı + Özel Notlar + Ödünç Verilebilir + Etiketlendi --}}
                    <div class="form-grid cols-3 filter-row-extra" style="margin-bottom:14px;grid-template-columns:repeat(5,1fr);">
                        <div class="form-field">
                            <label class="form-label">Dil</label>
                            <select id="filterDil" class="form-select">
                                <option value="">Tümü</option>
                                <option value="Türkçe">Türkçe</option>
                                <option value="İngilizce">İngilizce</option>
                                <option value="Almanca">Almanca</option>
                                <option value="Fransızca">Fransızca</option>
                                <option value="Arapça">Arapça</option>
                                <option value="İspanyolca">İspanyolca</option>
                                <option value="Rusça">Rusça</option>
                                <option value="Farsça">Farsça</option>
                                <option value="Diğer">Diğer</option>
                            </select>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Konu Başlığı</label>
                            <input type="text" id="filterKonuBasligi" class="form-input" placeholder="Konu başlığı..." autocomplete="off">
                        </div>
                        <div class="form-field">
                            <label class="form-label">Özel Notlar</label>
                            <input type="text" id="filterOzelNotlar" class="form-input" placeholder="Not içeriği..." autocomplete="off">
                        </div>
                        <div class="form-field">
                            <label class="form-label">Ödünç Verilebilir</label>
                            <select id="filterOduncVerilebilir" class="form-select">
                                <option value="">Hepsi</option>
                                <option value="evet">Evet</option>
                                <option value="hayir">Hayır</option>
                            </select>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Etiketlendi</label>
                            <select id="filterEtiketlendi" class="form-select">
                                <option value="">Hepsi</option>
                                <option value="evet">Evet</option>
                                <option value="hayir">Hayır</option>
                            </select>
                        </div>
                    </div>

                    {{-- Satır 4: Kayıt Tarihi --}}
                    <div class="form-grid cols-3 filter-row-extra" style="margin-bottom:14px;grid-template-columns:repeat(4,1fr);">
                        <div class="form-field">
                            <label class="form-label">Kayıt Tarihi (Başlangıç)</label>
                            <input type="date" id="filterKayitBaslangic" class="form-input" autocomplete="off">
                        </div>
                        <div class="form-field">
                            <label class="form-label">Kayıt Tarihi (Bitiş)</label>
                            <input type="date" id="filterKayitBitis" class="form-input" autocomplete="off">
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top:16px;">
                        <button class="btn btn-outline" id="clearFilters">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                            Temizle
                        </button>
                        <button class="btn btn-primary" id="applyFilters">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            Filtrele
                        </button>
                    </div>
                </div>
            </div>


            <div class="form-card" id="kitaplarCard">
                <div class="form-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 class="form-card-title">Envanter</h2>
                        <p class="form-card-desc" id="totalInfo">Toplam <strong>{{ $bookcount }}</strong> kayıt</p>
                    </div>
                    <button class="btn btn-outline" id="exportExcel">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                        Excel Olarak İndir
                    </button>
                </div>

                <div class="table-loading-wrapper">
                    <div class="table-loading-overlay" id="tableLoading">
                        <div class="spinner"></div>
                    </div>
                    <div style="overflow-x: auto;" id="tableContainer">
                        <table style="width: 100%; border-collapse: collapse; font-size: 14px; text-align: left;">
                            <thead>
                            <tr style="background: var(--muted); border-bottom: 1px solid var(--border);">
                                <th style="padding: 12px 24px; font-weight: 600;">Görsel</th>
                                <th style="padding: 12px; font-weight: 600;">Kitap Bilgisi</th>
                                <th style="padding: 12px; font-weight: 600;">Demirbaş</th>
                                <th style="padding: 12px; font-weight: 600;">ISBN</th>
                                <th style="padding: 12px; font-weight: 600;">Kategori</th>
                                <th style="padding: 12px; font-weight: 600;">Durum</th>
                                <th style="padding: 12px; font-weight: 600;">İşlem</th>
                            </tr>
                            </thead>
                            <tbody id="tableBody">
                            <tr><td colspan="6" style="padding:40px;text-align:center;color:var(--muted-foreground);font-size:13px;">Yükleniyor…</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Pagination — AJAX tarafından doldurulur --}}
                <div class="table-footer" id="paginationWrapper">
                    <div class="tf-info">
                        <span id="paginationInfo">—</span>
                        <div class="per-page-wrap">
                            <label for="perPageSelect" style="white-space:nowrap;">Sayfa başına:</label>
                            <select class="per-page-select" id="perPageSelect">
                                <option value="10">10</option>
                                <option value="20" selected>20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="500">500</option>
                            </select>
                        </div>
                    </div>
                    <nav class="pagination-nav" id="paginationNav" aria-label="Sayfalama"></nav>
                </div>
            </div>
        </div>

        <script>
            // ============================
            // Config
            // ============================
            var ajaxUrl    = '{{ route('katalog.index') }}';
            var exportUrl  = '{{ route('katalog.export') }}';
            var searchTimer = null;
            var reqCounter  = 0; // race condition önlemi

            // ============================
            // Filtre değerleri
            // ============================
            function getFilters() {
                return {
                    search:            document.getElementById('filterSearch').value.trim(),
                    kategori:          document.getElementById('filterKategori').value,
                    siniflamaYer:      document.getElementById('filterSiniflamaYer').value.trim(),
                    yazarId:           document.getElementById('filterYazar').value,
                    yayineviId:        document.getElementById('filterYayinevi').value,
                    per_page:          document.getElementById('perPageSelect').value,
                    kutuphaneId:       document.getElementById('filterKutuphane').value,
                    turId:             document.getElementById('filterTur').value,
                    durum:             document.getElementById('filterDurum').value,
                    dil:               document.getElementById('filterDil').value,
                    konuBasligi:       document.getElementById('filterKonuBasligi').value.trim(),
                    ozelNotlar:        document.getElementById('filterOzelNotlar').value.trim(),
                    oduncVerilebilir:  document.getElementById('filterOduncVerilebilir').value,
                    etiketlendi:       document.getElementById('filterEtiketlendi').value,
                    kayitBaslangic:    document.getElementById('filterKayitBaslangic').value,
                    kayitBitis:        document.getElementById('filterKayitBitis').value,
                };
            }

            // ============================
            // SVG sabitler
            // ============================
            var SVG = {
                first:  '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m11 17-5-5 5-5"/><path d="m18 17-5-5 5-5"/></svg>',
                prev:   '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>',
                next:   '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>',
                last:   '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m13 17 5-5-5-5"/><path d="m6 17 5-5-5-5"/></svg>'
            };

            // ============================
            // Sayfalama HTML oluşturucu
            // ============================
            function buildPaginationHTML(data) {
                var cp = data.current_page, lp = data.last_page;
                var fi = data.from, li = data.to, total = data.total_records;

                document.getElementById('paginationInfo').innerHTML =
                    fi + '&ndash;' + li + ' / <strong>' + total + '</strong> kayıt';

                var nav = '';
                nav += '<button class="page-btn nav-btn" data-page="1"' + (cp==1?' disabled':'') + ' title="İlk sayfa">' + SVG.first + '</button>';
                nav += '<button class="page-btn nav-btn" data-page="' + (cp-1) + '"' + (cp==1?' disabled':'') + ' title="Önceki sayfa">' + SVG.prev + '</button>';
                var rs = Math.max(1, cp-2), re = Math.min(lp, cp+2);
                if (rs > 1) { nav += '<button class="page-btn" data-page="1">1</button>'; if (rs > 2) nav += '<span class="page-ellipsis">…</span>'; }
                for (var i = rs; i <= re; i++) { nav += '<button class="page-btn' + (i==cp?' active':'') + '" data-page="' + i + '">' + i + '</button>'; }
                if (re < lp) { if (re < lp-1) nav += '<span class="page-ellipsis">…</span>'; nav += '<button class="page-btn" data-page="' + lp + '">' + lp + '</button>'; }
                nav += '<button class="page-btn nav-btn" data-page="' + (cp+1) + '"' + (cp==lp?' disabled':'') + ' title="Sonraki sayfa">' + SVG.next + '</button>';
                nav += '<button class="page-btn nav-btn" data-page="' + lp + '"' + (cp==lp?' disabled':'') + ' title="Son sayfa">' + SVG.last + '</button>';

                document.getElementById('paginationNav').innerHTML = nav;
                bindPaginationEvents();

                document.getElementById('totalInfo').innerHTML =
                    'Toplam <strong>' + total + '</strong> kayıt &middot; ' +
                    '<strong>' + cp + '</strong>/<strong>' + lp + '</strong> sayfa gösteriliyor.';
            }

            // ============================
            // Tablo satırları HTML oluşturucu
            // ============================
            function buildTableRowsHTML(rows) {
                if (!rows || rows.length === 0) {
                    return '<tr><td colspan="6" style="padding:48px;text-align:center;color:var(--muted-foreground);">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 12px;display:block;opacity:0.4;"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>Kayıtlı kitap bulunamadı.</td></tr>';
                }
                return rows.map(function(k) {
                    var coverSrc = k.kunyeKapakResmi
                        ? k.kunyeKapakResmi
                        : ('https://ui-avatars.com/api/?name=' + encodeURIComponent(k.kunyeEserAdi || '') + '&background=7a5c3c&color=fff');
                    var viewUrl = '/katalog/' + k.id + '/view';
                    return '<tr style="border-bottom:1px solid var(--border);transition:background 0.2s;" onmouseover="this.style.background=\'var(--card)\'" onmouseout="this.style.background=\'transparent\'">' +
                        '<td style="padding:12px 24px;"><a href="' + viewUrl + '" style="display:inline-block;width:45px;height:65px;background:#ddd;border-radius:4px;overflow:hidden;border:1px solid var(--border);">' +
                        '<img src="' + coverSrc + '" alt="Kapak" style="width:100%;height:100%;object-fit:cover;"></a></td>' +
                        '<td style="padding:12px;"><a href="' + viewUrl + '" style="font-weight:600;color:var(--foreground);text-decoration:none;">' + (k.kunyeEserAdi || '') + '</a>' +
                        '<div style="font-size:12px;color:var(--muted-foreground);">' + (k.kunyeYazar || '') + (k.kunyeYayinlayan ? ' &middot; ' + k.kunyeYayinlayan : '') + '</div></td>' +
                        '<td style="padding:12px;color:var(--muted-foreground);">' + (k.kunyeDemirbasKN || '') + '</td>' +
                        '<td style="padding:12px;color:var(--muted-foreground);">' + (k.kunyeISBNISSN || '') + '</td>' +
                        '<td style="padding:12px;"><span style="padding:4px 8px;background:rgba(122,92,60,0.1);color:var(--primary);border-radius:4px;font-size:12px;">' + (k.kunyeSiniflamaYer || 'Genel') + '</span></td>' +
                        '<td style="padding:12px;">' + (k.kunyeDurum || 1) + '</td>' +
                        '<td style="padding:12px;text-align:right;">' +
                        '<button class="row-actions-btn" onclick="toggleRowMenu(' + k.id + ', event)" title="İşlemler">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>' +
                        '</button>' +
                        '</td></tr>';
                }).join('');
            }

            // ============================
            // AJAX Veri Çekme — reqCounter ile race condition önlemi
            // ============================
            function fetchPage(page) {
                var myReq = ++reqCounter;
                var params = new URLSearchParams(getFilters());
                params.set('page', page || 1);

                document.getElementById('tableLoading').classList.add('visible');

                fetch(ajaxUrl + '?' + params.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                    .then(function(res) {
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        return res.json();
                    })
                    .then(function(data) {
                        if (myReq !== reqCounter) return; // eski istek, yoksay

                        // rows dizisini güvenle al
                        var rows = Array.isArray(data.rows) ? data.rows
                            : (data.rows && Array.isArray(data.rows.data) ? data.rows.data : []);

                        document.getElementById('tableBody').innerHTML = buildTableRowsHTML(rows);
                        buildPaginationHTML(data);
                        //document.getElementById('kitaplarCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
                    })
                    .catch(function() {
                        if (myReq !== reqCounter) return;
                        showToast('error', 'Hata', 'Veriler yüklenirken bir sorun oluştu.');
                    })
                    .finally(function() {
                        if (myReq === reqCounter) {
                            document.getElementById('tableLoading').classList.remove('visible');
                        }
                    });
            }

            // ============================
            // Sayfalama buton olayları
            // ============================
            function bindPaginationEvents() {
                document.querySelectorAll('#paginationNav [data-page]').forEach(function(btn) {
                    if (!btn.disabled) {
                        btn.addEventListener('click', function() {
                            fetchPage(parseInt(this.getAttribute('data-page')));
                        });
                    }
                });
            }

            // ============================
            // Excel Export — aktif filtreleri URL'ye ekle
            // ============================
            document.getElementById('exportExcel').addEventListener('click', function() {
                var f = getFilters();
                var params = new URLSearchParams();
                if (f.search)       params.set('search',       f.search);
                if (f.kategori)     params.set('kategori',     f.kategori);
                if (f.siniflamaYer) params.set('siniflamaYer', f.siniflamaYer);
                if (f.yazarId)      params.set('yazarId',      f.yazarId);
                if (f.yayineviId)   params.set('yayineviId',   f.yayineviId);
                if (f.kutuphaneId)       params.set('kutuphaneId',       f.kutuphaneId);
                if (f.turId)             params.set('turId',             f.turId);
                if (f.durum)             params.set('durum',             f.durum);
                if (f.dil)               params.set('dil',               f.dil);
                if (f.konuBasligi)       params.set('konuBasligi',       f.konuBasligi);
                if (f.ozelNotlar)        params.set('ozelNotlar',        f.ozelNotlar);
                if (f.oduncVerilebilir)  params.set('oduncVerilebilir',  f.oduncVerilebilir);
                if (f.etiketlendi)       params.set('etiketlendi',       f.etiketlendi);
                if (f.kayitBaslangic)    params.set('kayitBaslangic',    f.kayitBaslangic);
                if (f.kayitBitis)        params.set('kayitBitis',        f.kayitBitis);
                var a = document.createElement('a');
                a.href = exportUrl + (params.toString() ? '?' + params.toString() : '');
                a.download = '';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            });

            // ============================
            // Filtre olay dinleyiciler
            // ============================
            document.getElementById('applyFilters').addEventListener('click', function() {
                clearTimeout(searchTimer);
                fetchPage(1);
            });

            // Sayfa başına kayıt değişince hemen yenile
            document.getElementById('perPageSelect').addEventListener('change', function() {
                clearTimeout(searchTimer);
                fetchPage(1);
            });

            document.getElementById('clearFilters').addEventListener('click', function() {
                clearTimeout(searchTimer);
                document.getElementById('filterSearch').value       = '';
                document.getElementById('filterKategori').value     = '';
                document.getElementById('filterSiniflamaYer').value = '';
                // yazar combobox — face sıfırla
                document.getElementById('filterYazar').value = '';
                resetComboboxFace('filterYazarFace', 'filterYazarFaceText', 'filterYazarClear', 'Yazar seçin...');
                // yayınevi combobox — face sıfırla
                document.getElementById('filterYayinevi').value = '';
                resetComboboxFace('filterYayineviFace', 'filterYayineviFaceText', 'filterYayineviClear', 'Yayınevi seçin...');
                // kütüphane combobox — face sıfırla
                document.getElementById('filterKutuphane').value = '';
                resetComboboxFace('filterKutuphaneFace', 'filterKutuphaneFaceText', 'filterKutuphaneClear', 'Kütüphane seçin...');
                // kategori combobox — face sıfırla
                document.getElementById('filterKategori').value = '';
                resetComboboxFace('filterKategoriFace', 'filterKategoriFaceText', 'filterKategoriClear', 'Kategori seçin...');
                // tür combobox — face sıfırla
                document.getElementById('filterTur').value = '';
                resetComboboxFace('filterTurFace', 'filterTurFaceText', 'filterTurClear', 'Tür seçin...');
                // diğer yeni filtreler
                document.getElementById('filterDurum').value            = '';
                document.getElementById('filterDil').value              = '';
                document.getElementById('filterKonuBasligi').value      = '';
                document.getElementById('filterOzelNotlar').value       = '';
                document.getElementById('filterOduncVerilebilir').value = '';
                document.getElementById('filterEtiketlendi').value      = '';
                document.getElementById('filterKayitBaslangic').value   = '';
                document.getElementById('filterKayitBitis').value       = '';
                // per-page sıfırla
                document.getElementById('perPageSelect').value = '20';
                fetchPage(1);
            });

            // Enter tuşuyla arama (Filtrele butonuyla eşdeğer)
            ['filterSearch', 'filterSiniflamaYer', 'filterKonuBasligi', 'filterOzelNotlar'].forEach(function(id) {
                document.getElementById(id).addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') { clearTimeout(searchTimer); fetchPage(1); }
                });
            });

            (function() {
                var toggleMoreFilters = document.getElementById('toggleMoreFilters');
                var formCardBody = document.querySelector('.form-card-body');
                if (!toggleMoreFilters || !formCardBody) return;

                var expanded = false;
                toggleMoreFilters.addEventListener('click', function() {
                    expanded = !expanded;
                    formCardBody.classList.toggle('filters-expanded', expanded);
                    this.textContent = expanded ? 'Daha az filtre' : 'Daha fazla filtre';
                    this.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                });
            })();



            // ============================
            // resetComboboxFace — clearFilters tarafından kullanılır
            // ============================
            function resetComboboxFace(faceId, faceTextId, clearBtnId, placeholder) {
                var faceText = document.getElementById(faceTextId);
                var clearBtn = document.getElementById(clearBtnId);
                if (faceText) { faceText.textContent = placeholder; faceText.className = 'combobox-face-text'; }
                if (clearBtn) clearBtn.style.display = 'none';
                // Input + Face görünürlüğünü sıfırla
                var face = document.getElementById(faceId);
                if (face) face.style.display = '';
            }

            // ============================
            // Filtre Combobox — Yazar & Yayınevi
            // Seçim gösterimi (face) ile arama inputu birbirinden ayrıdır.
            // Dropdown açılınca arama inputu temizlenir → her zaman tam liste görünür.
            // ============================
            (function() {
                function initFilterCombobox(cfg) {
                    var wrapper     = document.getElementById(cfg.wrapperId);
                    var searchInput = document.getElementById(cfg.searchInputId);
                    var hiddenId    = document.getElementById(cfg.hiddenId);
                    var faceEl      = document.getElementById(cfg.faceId);
                    var faceText    = document.getElementById(cfg.faceTextId);
                    var clearBtn    = document.getElementById(cfg.clearBtnId);
                    var dropdown    = wrapper.querySelector('.combobox-dropdown');
                    var toggle      = wrapper.querySelector('.combobox-toggle');
                    var placeholder = cfg.placeholder || 'Seçin...';

                    var rawData = [];
                    try { rawData = JSON.parse(document.getElementById(cfg.dataScriptId).textContent || '[]'); } catch(e) {}

                    var highlightedIndex = -1;
                    var filtered = rawData.slice();

                    function esc(s) { var d = document.createElement('div'); d.appendChild(document.createTextNode(s||'')); return d.innerHTML; }

                    function highlight(text, term) {
                        if (!term) return esc(text);
                        var idx = text.toLowerCase().indexOf(term.toLowerCase());
                        if (idx === -1) return esc(text);
                        return esc(text.substring(0, idx)) +
                            '<strong style="color:var(--primary)">' + esc(text.substring(idx, idx + term.length)) + '</strong>' +
                            esc(text.substring(idx + term.length));
                    }

                    // Face göstergesi güncelle
                    function updateFace() {
                        if (hiddenId.value) {
                            var sel = null;
                            for (var i = 0; i < rawData.length; i++) {
                                if (String(rawData[i].id) === String(hiddenId.value)) { sel = rawData[i]; break; }
                            }
                            if (sel) {
                                faceText.textContent = sel.ad;
                                faceText.className = 'combobox-face-text is-selected';
                                clearBtn.style.display = 'flex';
                                return;
                            }
                        }
                        faceText.textContent = placeholder;
                        faceText.className = 'combobox-face-text';
                        clearBtn.style.display = 'none';
                    }

                    function render(filter) {
                        var term = (filter || '').toLowerCase();
                        filtered = rawData.filter(function(r) {
                            return r.ad.toLowerCase().indexOf(term) !== -1;
                        });
                        var html = '';
                        var allSel = hiddenId.value === '';
                        html += '<div class="combobox-option' + (allSel ? ' selected' : '') + (highlightedIndex === -1 && allSel ? ' highlighted' : '') + '" data-id="" data-ad="">' +
                            '<svg class="check-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>' +
                            '<span>Tümü</span></div>';
                        if (filtered.length === 0 && term) {
                            html += '<div class="combobox-no-result">Eşleşen kayıt bulunamadı.</div>';
                        } else {
                            filtered.forEach(function(r, i) {
                                var sel  = (hiddenId.value !== '' && parseInt(hiddenId.value) === r.id);
                                var high = (i === highlightedIndex);
                                html += '<div class="combobox-option' + (sel?' selected':'') + (high?' highlighted':'') + '" data-id="' + r.id + '" data-ad="' + esc(r.ad) + '">' +
                                    '<svg class="check-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>' +
                                    '<span>' + highlight(r.ad, filter) + '</span></div>';
                            });
                        }
                        dropdown.innerHTML = html;
                        dropdown.querySelectorAll('.combobox-option').forEach(function(el) {
                            el.addEventListener('mousedown', function(e) {
                                e.preventDefault();
                                selectOption(this.getAttribute('data-id'), this.getAttribute('data-ad'));
                            });
                        });
                    }

                    function selectOption(id, ad) {
                        hiddenId.value = id;
                        updateFace();
                        close();
                    }

                    function open() {
                        if (isOpen()) return;
                        highlightedIndex = -1;
                        // Arama inputunu göster, face'i gizle
                        faceEl.style.display = 'none';
                        searchInput.style.display = '';
                        searchInput.value = ''; // Her açılışta temizle — tam liste görünür
                        render('');
                        dropdown.classList.add('visible');
                        toggle.classList.add('open');
                        searchInput.focus();
                    }

                    function close() {
                        dropdown.classList.remove('visible');
                        toggle.classList.remove('open');
                        highlightedIndex = -1;
                        // Arama inputunu gizle, face'i göster
                        searchInput.style.display = 'none';
                        faceEl.style.display = '';
                    }

                    function isOpen() { return dropdown.classList.contains('visible'); }

                    // X butonu — seçimi kaldır
                    clearBtn.addEventListener('mousedown', function(e) {
                        e.preventDefault();
                        hiddenId.value = '';
                        updateFace();
                    });

                    // Toggle butonu
                    toggle.addEventListener('mousedown', function(e) {
                        e.preventDefault();
                        isOpen() ? close() : open();
                    });

                    // Face tıklaması — arama aç (X butonuna tıklamayı geçme)
                    faceEl.addEventListener('mousedown', function(e) {
                        if (e.target === clearBtn || clearBtn.contains(e.target)) return;
                        e.preventDefault();
                        open();
                    });

                    // Arama inputu event'leri
                    searchInput.addEventListener('input', function() {
                        highlightedIndex = -1;
                        render(this.value);
                    });
                    searchInput.addEventListener('blur', function() { setTimeout(close, 160); });
                    searchInput.addEventListener('keydown', function(e) {
                        if (!isOpen() && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) { e.preventDefault(); open(); return; }
                        if (!isOpen()) return;
                        if (e.key === 'ArrowDown') {
                            e.preventDefault();
                            highlightedIndex = Math.min(highlightedIndex + 1, filtered.length - 1);
                            render(searchInput.value);
                            var h = dropdown.querySelector('.highlighted'); if (h) h.scrollIntoView({block:'nearest'});
                        } else if (e.key === 'ArrowUp') {
                            e.preventDefault();
                            highlightedIndex = Math.max(highlightedIndex - 1, -1);
                            render(searchInput.value);
                            var h2 = dropdown.querySelector('.highlighted'); if (h2) h2.scrollIntoView({block:'nearest'});
                        } else if (e.key === 'Enter') {
                            e.preventDefault();
                            if (highlightedIndex >= 0 && filtered[highlightedIndex]) {
                                selectOption(filtered[highlightedIndex].id, filtered[highlightedIndex].ad);
                            } else if (highlightedIndex === -1) {
                                selectOption('', '');
                            }
                        } else if (e.key === 'Escape') { close(); }
                    });

                    document.addEventListener('click', function(e) { if (!wrapper.contains(e.target)) close(); });

                    // İlk render
                    updateFace();
                    searchInput.style.display = 'none';
                }

                initFilterCombobox({
                    wrapperId:    'filterYazarCombobox',
                    searchInputId:'filterYazarSearch',
                    hiddenId:     'filterYazar',
                    faceId:       'filterYazarFace',
                    faceTextId:   'filterYazarFaceText',
                    clearBtnId:   'filterYazarClear',
                    dataScriptId: 'filterYazarData',
                    placeholder:  'Yazar seçin...',
                });
                initFilterCombobox({
                    wrapperId:    'filterYayineviCombobox',
                    searchInputId:'filterYayineviSearch',
                    hiddenId:     'filterYayinevi',
                    faceId:       'filterYayineviFace',
                    faceTextId:   'filterYayineviFaceText',
                    clearBtnId:   'filterYayineviClear',
                    dataScriptId: 'filterYayineviData',
                    placeholder:  'Yayınevi seçin...',
                });
                initFilterCombobox({
                    wrapperId:    'filterKutuphaneCombobox',
                    searchInputId:'filterKutuphaneSearch',
                    hiddenId:     'filterKutuphane',
                    faceId:       'filterKutuphaneFace',
                    faceTextId:   'filterKutuphaneFaceText',
                    clearBtnId:   'filterKutuphaneClear',
                    dataScriptId: 'filterKutuphaneData',
                    placeholder:  'Kütüphane seçin...',
                });
                initFilterCombobox({
                    wrapperId:    'filterKategoriCombobox',
                    searchInputId:'filterKategoriSearch',
                    hiddenId:     'filterKategori',
                    faceId:       'filterKategoriFace',
                    faceTextId:   'filterKategoriFaceText',
                    clearBtnId:   'filterKategoriClear',
                    dataScriptId: 'filterKategoriData',
                    placeholder:  'Kategori seçin...',
                });
                initFilterCombobox({
                    wrapperId:    'filterTurCombobox',
                    searchInputId:'filterTurSearch',
                    hiddenId:     'filterTur',
                    faceId:       'filterTurFace',
                    faceTextId:   'filterTurFaceText',
                    clearBtnId:   'filterTurClear',
                    dataScriptId: 'filterTurData',
                    placeholder:  'Tür seçin...',
                });
            })();

            // ============================
            // İlk yüklemede veri çek
            // ============================
            fetchPage(1);
        </script>
    </main>
</div>

<!-- Floating İşlemler Menüsü — script'ten önce DOM'a eklenir -->
<div id="kitapFloatingMenu">
    <a id="kfmGoruntule" href="#" class="row-actions-item">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        Görüntüle
    </a>
    <a id="kfmDuzenle" href="#" class="row-actions-item">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
        Düzenle
    </a>
    <a id="kfmKopyala" href="#" class="row-actions-item">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
        Kopyala
    </a>
    <div style="height:1px;background:var(--border);margin:4px 0;"></div>
    <a id="kfmOduncVer" href="#" class="row-actions-item" style="color:var(--primary);">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--primary);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M12 18v-6"/><path d="M9 15h6"/></svg>
        Ödünç Ver
    </a>
</div>

<script>
            var canEditBook = @json(auth()->user()->hasYetki(2) || auth()->user()->hasYetki(5));
            var canViewBook = @json(auth()->user()->hasYetki(1) || auth()->user()->hasYetki(2) || auth()->user()->hasYetki(4) || auth()->user()->hasYetki(5));
    // ============================
    // Toast System
    // ============================
    function showToast(type, title, description) {
        var container = document.getElementById('toastContainer');
        var toast = document.createElement('div');
        toast.className = 'toast ' + type;
        toast.innerHTML = '<div>' + title + '</div>' + (description ? '<div class="toast-desc">' + description + '</div>' : '');
        container.appendChild(toast);

        setTimeout(function() {
            toast.style.animation = 'toast-out 0.3s ease forwards';
            setTimeout(function() {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 300);
        }, 3500);
    }

    // ============================
    // Sidebar Toggle
    // ============================
    var sidebar = document.getElementById('sidebar');
    var mainContent = document.getElementById('mainContent');
    var sidebarToggle = document.getElementById('sidebarToggle');
    var sidebarOverlay = document.getElementById('sidebarOverlay');
    var isMobile = window.innerWidth <= 768;

    function toggleSidebar() {
        if (isMobile) {
            sidebar.classList.toggle('open');
            sidebarOverlay.classList.toggle('visible');
        } else {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        }
    }

    sidebarToggle.addEventListener('click', toggleSidebar);

    sidebarOverlay.addEventListener('click', function() {
        sidebar.classList.remove('open');
        sidebarOverlay.classList.remove('visible');
    });

    window.addEventListener('resize', function() {
        isMobile = window.innerWidth <= 768;
        if (!isMobile) {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('visible');
        }
    });

    // ============================
    // Sidebar active item highlight
    // ============================
    var menuItems = document.querySelectorAll('.sidebar-menu-item');
    menuItems.forEach(function(item) {
        item.addEventListener('click', function(e) {
            //e.preventDefault();
            menuItems.forEach(function(mi) { mi.classList.remove('active'); });
            this.classList.add('active');

            // Close sidebar on mobile
            if (isMobile) {
                sidebar.classList.remove('open');
                sidebarOverlay.classList.remove('visible');
            }
        });
    });

    // ============================
    // Satır Aksiyon Dropdown — Floating menü
    // ============================
    var kitapFloatingMenu   = document.getElementById('kitapFloatingMenu');
    var openRowMenuBtn = null;

    function closeRowMenu() {
        kitapFloatingMenu.classList.remove('open');
        openRowMenuBtn = null;
    }

    function toggleRowMenu(id, event) {
        event.stopPropagation();
        var btn = event.currentTarget;

        // Aynı butona tekrar basılırsa kapat
        if (openRowMenuBtn === btn) { closeRowMenu(); return; }

        // Menü bağlantılarını güncelle
        var editEl = document.getElementById('kfmDuzenle');
        if (editEl) {
            if (canEditBook) {
                editEl.style.display = '';
                editEl.href = '/katalog/' + id + '/edit';
            } else {
                editEl.style.display = 'none';
                editEl.href = '#';
            }
        }
        var viewEl = document.getElementById('kfmGoruntule');
        if (viewEl) {
            if (canViewBook) {
                viewEl.style.display = '';
                viewEl.href = '/katalog/' + id + '/view';
            } else {
                viewEl.style.display = 'none';
                viewEl.href = '#';
            }
        }
        document.getElementById('kfmKopyala').href  = '/katalog/' + id + '/copy';
        document.getElementById('kfmOduncVer').href = '/odunc/new?katalog_id=' + id;

        // Konumlandır: butonun altına veya üstüne
        kitapFloatingMenu.style.visibility = 'hidden';
        kitapFloatingMenu.classList.add('open');
        var rect       = btn.getBoundingClientRect();
        var mH         = kitapFloatingMenu.offsetHeight;
        var mW         = kitapFloatingMenu.offsetWidth;
        var spaceBelow = window.innerHeight - rect.bottom;

        var top  = spaceBelow >= mH + 8 ? rect.bottom + 4 : rect.top - mH - 4;
        var left = rect.right - mW;
        if (left < 8) left = 8;

        kitapFloatingMenu.style.top        = top + 'px';
        kitapFloatingMenu.style.left       = left + 'px';
        kitapFloatingMenu.style.visibility = '';

        openRowMenuBtn = btn;
    }

    // Menü dışına tıklanınca kapat
    document.addEventListener('click', function(e) {
        if (openRowMenuBtn && !kitapFloatingMenu.contains(e.target)) {
            closeRowMenu();
        }
    });

    // Scroll'da kapat
    window.addEventListener('scroll', closeRowMenu, true);
</script>



</body>
</html>
