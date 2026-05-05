@php
    $isViewOnly = $isViewOnly ?? false;
    $canTransferKutuphane = $canTransferKutuphane ?? false;
    $kutuphaneTransferTargets = $kutuphaneTransferTargets ?? collect();
    $kutuphaneTransferAllowedJson = $kutuphaneTransferAllowedJson ?? [];
    $currentKutuphaneTitle = $currentKutuphaneTitle ?? '—';
@endphp
@extends('layouts.base')

@section('title', 'Kitap Guncelle')

@section('styles')
    <meta name="description" content="Modern kutuphane yonetim ve otomasyon sistemi. Kitap kaydi, odunc verme ve envanter yonetimi." />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <style>
        .form-card-header-main {
            flex: 1;
            min-width: 0;
        }

        .form-card-edit-btn {
            flex-shrink: 0;
            height: 32px;
            padding: 0 12px;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: var(--card);
            color: var(--foreground);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 500;
            transition: background 0.15s, border-color 0.15s;
        }

        .form-card-edit-btn:hover {
            background: var(--muted);
            border-color: var(--muted-foreground);
        }

        .form-card-header-actions {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .form-card-edit-btn.is-disabled {
            pointer-events: none;
            opacity: 0.55;
            cursor: default;
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

        .form-card-header {
            padding: 24px 24px 16px;
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }

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
        .auto-grow-textarea {
            overflow-y: hidden;
            min-height: 38px; /* input ile ayni baslangic yuksekligi */
        }

        /* ============= Checkbox Toggle ============= */
        .checkbox-group { display: flex; flex-direction: column; gap: 10px; padding-top: 4px; }

        .checkbox-item { display: flex; align-items: center; gap: 10px; cursor: pointer; user-select: none; }

        .checkbox-item input[type="checkbox"] { display: none; }

        .checkbox-box {
            width: 18px;
            height: 18px;
            border: 1.5px solid var(--border);
            border-radius: 4px;
            background: var(--card);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: background 0.15s, border-color 0.15s;
        }

        .checkbox-box svg { width: 11px; height: 11px; color: #fff; opacity: 0; transition: opacity 0.1s; }

        .checkbox-item input[type="checkbox"]:checked ~ .checkbox-box { background: var(--primary); border-color: var(--primary); }
        .checkbox-item input[type="checkbox"]:checked ~ .checkbox-box svg { opacity: 1; }

        .checkbox-label { font-size: 14px; font-weight: 500; color: var(--foreground); line-height: 1.4; }
        .checkbox-label small { display: block; font-size: 12px; font-weight: 400; color: var(--muted-foreground); margin-top: 1px; }

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

        /* ISBN Search Button */
        .isbn-search-btn {
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 38px;
            border: none;
            background: transparent;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted-foreground);
            border-radius: 0 calc(var(--radius) - 2px) calc(var(--radius) - 2px) 0;
            transition: color 0.15s, background 0.15s;
        }

        .isbn-search-btn:hover { color: var(--primary); background: rgba(122, 92, 60, 0.08); }
        .isbn-search-btn:active { background: rgba(122, 92, 60, 0.15); }
        .isbn-search-btn:disabled { pointer-events: none; opacity: 0.6; }
        .isbn-search-btn svg { width: 16px; height: 16px; display: block; }

        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

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

        /* ============= Loading Overlay ============= */
        .loading-overlay {
            position: fixed;
            inset: 0;
            z-index: 2000;
            background: rgba(61, 50, 38, 0.45);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s ease, visibility 0.2s ease;
        }

        .loading-overlay.visible {
            opacity: 1;
            visibility: visible;
        }

        .loading-box {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 40px 56px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            transform: scale(0.92);
            transition: transform 0.2s ease;
        }

        .loading-overlay.visible .loading-box {
            transform: scale(1);
        }

        .loading-spinner {
            width: 48px;
            height: 48px;
            border: 3px solid var(--border);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.75s linear infinite;
        }

        .loading-text {
            font-family: var(--font-sans);
            font-size: 15px;
            font-weight: 600;
            color: var(--foreground);
            letter-spacing: 0.01em;
        }

        .loading-subtext {
            font-size: 13px;
            color: var(--muted-foreground);
            margin-top: -12px;
        }

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

        /* ============= Katalog transfer modal ============= */
        .katalog-transfer-backdrop {
            position: fixed;
            inset: 0;
            z-index: 2100;
            background: rgba(61, 50, 38, 0.5);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s ease, visibility 0.2s ease;
        }
        .katalog-transfer-backdrop.visible {
            opacity: 1;
            visibility: visible;
        }
        .katalog-transfer-modal {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 22px 24px 20px;
            max-width: 420px;
            width: calc(100% - 32px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.22);
            transform: scale(0.96);
            transition: transform 0.2s ease;
        }
        .katalog-transfer-backdrop.visible .katalog-transfer-modal {
            transform: scale(1);
        }
        .katalog-transfer-modal h2 {
            font-family: var(--font-serif);
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 14px;
            color: var(--foreground);
        }
        .katalog-transfer-field { margin-bottom: 14px; }
        .katalog-transfer-field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--muted-foreground);
            margin-bottom: 6px;
        }
        .katalog-transfer-current {
            font-size: 15px;
            font-weight: 600;
            color: var(--foreground);
            padding: 10px 12px;
            background: var(--muted);
            border-radius: calc(var(--radius) - 2px);
            border: 1px solid var(--border);
        }
        .katalog-transfer-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 18px;
        }

        /* ============= Combobox ============= */
        .combobox-wrapper {
            position: relative;
        }

        .combobox-input-wrap {
            position: relative;
        }

        .combobox-input-wrap .form-input {
            padding-right: 68px;
        }

        .combobox-clear {
            position: absolute;
            right: 35px;
            top: 1px;
            bottom: 1px;
            width: 30px;
            display: none;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            cursor: pointer;
            color: var(--muted-foreground);
            border-radius: 0;
            transition: color 0.15s, background 0.15s;
        }

        .combobox-clear:hover {
            color: var(--foreground);
            background: var(--muted);
        }

        .combobox-clear svg {
            width: 14px;
            height: 14px;
        }

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
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .content-area { padding: 16px; }
            .form-grid.cols-2, .form-grid.cols-3 { grid-template-columns: 1fr; }
            .form-grid .span-2 { grid-column: span 1; }
            .form-actions { flex-direction: column-reverse; }
            .form-actions .btn { width: 100%; }
        }
        /* ── Üst Eser Autocomplete ── */
        .ue-autocomplete-wrap{position:relative}
        .ue-autocomplete-dropdown{position:absolute;top:calc(100% + 4px);left:0;right:0;background:var(--card);border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:100;max-height:260px;overflow-y:auto;display:none}
        .ue-autocomplete-dropdown.open{display:block}
        .ue-ac-item{padding:10px 14px;cursor:pointer;display:flex;align-items:flex-start;gap:10px;transition:background .1s}
        .ue-ac-item:hover,.ue-ac-item.highlighted{background:var(--secondary)}
        .ue-ac-cover{width:26px;height:36px;border-radius:3px;object-fit:cover;flex-shrink:0;background:var(--secondary)}
        .ue-ac-cover-ph{width:26px;height:36px;border-radius:3px;background:var(--secondary);display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .ue-ac-cover-ph svg{width:13px;height:13px;color:var(--muted-foreground)}
        .ue-ac-body{flex:1;min-width:0}
        .ue-ac-name{font-size:14px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .ue-ac-meta{font-size:12px;color:var(--muted-foreground);margin-top:1px}
        .ue-ac-empty,.ue-ac-loading{padding:14px;text-align:center;font-size:13px;color:var(--muted-foreground)}
        .ue-selected-card{border:1.5px solid var(--primary);border-radius:calc(var(--radius) - 2px);background:rgba(122,92,60,.04);padding:10px 14px;display:flex;align-items:center;gap:12px;margin-top:6px}
        .ue-selected-cover{width:28px;height:40px;border-radius:3px;object-fit:cover;flex-shrink:0}
        .ue-selected-info{flex:1;min-width:0}
        .ue-selected-name{font-size:14px;font-weight:600}
        .ue-selected-meta{font-size:12px;color:var(--muted-foreground);margin-top:2px}
        .ue-selected-clear{width:24px;height:24px;border-radius:6px;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--muted-foreground);flex-shrink:0;transition:background .15s}
        .ue-selected-clear:hover{background:var(--muted)}
        .ue-selected-clear svg{width:13px;height:13px}
    </style>
@endsection

@section('breadcrumb')
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('katalog.index') }}" class="breadcrumb-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
            Katalog
        </a>
        <span class="breadcrumb-sep">/</span>
        <span class="breadcrumb-current">{{ $isViewOnly ? 'Kitap Görüntüle' : 'Kitap Güncelle' }}</span>
    </nav>
@endsection

@section('content')
<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-box">
        <div class="loading-spinner"></div>
        <span class="loading-text">Güncelleniyor...</span>
        <span class="loading-subtext">Lütfen bekleyin</span>
    </div>
</div>

@if(!$isViewOnly && $canTransferKutuphane && $kutuphaneTransferTargets->isNotEmpty())
<div class="katalog-transfer-backdrop" id="katalogTransferBackdrop" role="dialog" aria-modal="true" aria-labelledby="katalogTransferTitle" hidden>
    <div class="katalog-transfer-modal" role="document">
        <h2 id="katalogTransferTitle">Katalog Transfer</h2>
        <div class="katalog-transfer-field">
            <label>Mevcut Kütüphane</label>
            <div class="katalog-transfer-current" id="katalogTransferCurrentLib">{{ $currentKutuphaneTitle }}</div>
        </div>
        <div class="katalog-transfer-field">
            <label class="form-label" for="transferTargetKutuphaneId">Transfer edilecek kütüphane</label>
            <select class="form-select" id="transferTargetKutuphaneId" autocomplete="off">
                <option value="">— Seçiniz —</option>
                @foreach($kutuphaneTransferTargets as $k)
                    <option value="{{ $k->id }}">{{ $k->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="katalog-transfer-field">
            <label class="form-label" for="transferAciklama">Açıklama <span style="font-weight:400;color:var(--muted-foreground);">(isteğe bağlı)</span></label>
            <textarea class="form-textarea" id="transferAciklama" rows="3" maxlength="5000" placeholder="Transfer ile ilgili not ekleyebilirsiniz…" autocomplete="off"></textarea>
        </div>
        <div class="katalog-transfer-actions">
            <button type="button" class="btn btn-outline" id="katalogTransferCancel">İptal</button>
            <button type="button" class="btn btn-primary" id="katalogTransferSubmit">Transfer et</button>
        </div>
    </div>
</div>
@endif

            <!-- Book Registration Form -->
            <form id="bookForm" class="form-card" method="POST" action="{{ $isViewOnly ? '#' : route('katalog.update', $kitap->id) }}" enctype="multipart/form-data" novalidate>
                @csrf
                @if(!$isViewOnly)
                    @method('PUT')
                @endif
                <div class="form-card-header">
                    <div class="form-card-header-main">
                        <h1 class="form-card-title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/><line x1="12" x2="12" y1="7" y2="7"/></svg>
                            {{ $isViewOnly ? 'Kitap Detayı' : 'Kitap Güncelle' }}
                        </h1>
                        <p class="form-card-desc">
                            {{ $isViewOnly
                                ? ($kitap->kunyeEserAdi . ' kitabının bilgileri görüntüleniyor.')
                                : ($kitap->kunyeEserAdi . ' kitabının bilgilerini güncelleyebilirsiniz.')
                            }}
                        </p>
                    </div>
                    <div class="form-card-header-actions">
                        @if(!empty($prevKatalogId))
                            @if($isViewOnly)
                                <a href="{{ route('katalog.view', array_merge(['kitap' => $prevKatalogId], request()->query())) }}" class="form-card-edit-btn" id="prevKatalogBtn">Önceki Katalog</a>
                            @else
                                <a href="{{ route('katalog.edit', array_merge(['kitap' => $prevKatalogId], request()->query())) }}" class="form-card-edit-btn" id="prevKatalogBtn">Önceki Katalog</a>
                            @endif
                        @else
                            <span class="form-card-edit-btn is-disabled">Önceki Katalog</span>
                        @endif

                        @if(!empty($nextKatalogId))
                            @if($isViewOnly)
                                <a href="{{ route('katalog.view', array_merge(['kitap' => $nextKatalogId], request()->query())) }}" class="form-card-edit-btn" id="nextKatalogBtn">Sonraki Katalog</a>
                            @else
                                <a href="{{ route('katalog.edit', array_merge(['kitap' => $nextKatalogId], request()->query())) }}" class="form-card-edit-btn" id="nextKatalogBtn">Sonraki Katalog</a>
                            @endif
                        @else
                            <span class="form-card-edit-btn is-disabled">Sonraki Katalog</span>
                        @endif

                        @if(auth()->user()->hasYetki(3) || auth()->user()->hasYetki(6))
                            <a href="{{ route('katalog.copy', array_merge(['kitap' => $kitap->id], request()->query())) }}" class="form-card-edit-btn">Kopyala</a>
                        @endif

                        @if($isViewOnly && (auth()->user()->hasYetki(2) || auth()->user()->hasYetki(5)))
                            <a href="{{ route('katalog.edit', array_merge(['kitap' => $kitap->id], request()->query())) }}" class="form-card-edit-btn">Düzenle</a>
                        @endif

                        @if(!$isViewOnly)
                            <a href="{{ route('katalog.view', array_merge(['kitap' => $kitap->id], request()->query())) }}" class="form-card-edit-btn">Görüntüle</a>
                        @endif
                    </div>
                </div>

                <div class="form-card-separator"></div>

                <div class="form-card-body">
                    <div class="form-layout">

                        <!-- LEFT: Book Cover Upload -->
                        <div class="cover-section">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <h3 class="cover-section-title">Kapak Görseli</h3>
                                @if(!$isViewOnly)
                                <button type="button" id="coverSearchBtn" title="ISBN ile kapak görselini getir" aria-label="Kapak görselini ara" style="width:24px;height:24px;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--muted-foreground);border-radius:4px;padding:0;flex-shrink:0;transition:color 0.15s,background 0.15s;" onmouseover="this.style.color='var(--primary)';this.style.background='rgba(122,92,60,0.08)'" onmouseout="this.style.color='var(--muted-foreground)';this.style.background='transparent'">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;display:block"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                </button>
                                @endif
                            </div>
                            <div class="cover-upload-area {{ $kitap->kunyeKapakResmi ? 'has-image' : '' }}" id="coverUploadArea" role="button" tabindex="0" aria-label="Kitap kapak resmi yukle">
                                <div class="cover-placeholder" id="coverPlaceholder" style="{{ $kitap->kunyeKapakResmi ? 'display:none' : '' }}">
                                    <div class="cover-icon-circle">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
                                    </div>
                                    <div>
                                        <p class="cover-text-primary">Kapak Resmi Yükle</p>
                                        <p class="cover-text-secondary">Sürükle bırak veya tıklayarak seç</p>
                                        <p class="cover-text-hint">PNG, JPG - Maks. 5MB</p>
                                    </div>
                                    <div class="cover-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7"/><line x1="16" x2="22" y1="5" y2="5"/><line x1="19" x2="19" y1="2" y2="8"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                        <span>Dosya Seç</span>
                                    </div>
                                </div>
                                @if($kitap->kunyeKapakResmi)
                                    <img id="existingCoverImg" src="{{ asset('storage/' . $kitap->kunyeKapakResmi) }}" alt="Kapak" style="width:100%;height:100%;object-fit:cover;">
                                    @if(!$isViewOnly)
                                    <button type="button" class="cover-remove-btn" id="coverRemoveBtn" aria-label="Resmi kaldır">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                    </button>
                                    @endif
                                @endif
                                <!-- New preview will be injected here -->
                            </div>
                            <input type="file" accept="image/*" id="coverInput" name="kunyeKapakResmi" style="display:none" aria-hidden="true" />
                            {{-- Mevcut kapak yolunu sakla, silme durumunda controller'a bildir --}}
                            <input type="hidden" name="mevcut_kapak" value="{{ $kitap->kunyeKapakResmi }}" />
                            <input type="hidden" name="kapak_sil" id="kapakSilInput" value="0" />
                            <input type="hidden" name="copy_kapak_from_katalog_id" id="copyKapakFromId" value="" />
                            @if(!$isViewOnly)
                            <p class="cover-change-hint" id="coverChangeHint" style="{{ $kitap->kunyeKapakResmi ? '' : 'display:none' }}">Değiştirmek için resme tıklayin</p>
                            @endif
                        </div>

                        <!-- RIGHT: Form Fields -->
                        <div class="fields-section">

                            <!-- Section 1: Temel Bilgiler -->
                            <div>
                                <h3 class="section-header">
                                    <span class="section-number">1</span>
                                    Temel Bilgiler
                                </h3>
                                <div class="form-grid cols-2">
                                    <div class="form-field">
                                        <label class="form-label" for="kunyeEserAdi">Eser Adı <span class="required">*</span></label>
                                        <textarea class="form-textarea auto-grow-textarea" id="kunyeEserAdi" name="kunyeEserAdi" placeholder="Örnek: Sefiller" rows="1" required>{{ old('kunyeEserAdi', $kitap->kunyeEserAdi) }}</textarea>
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="kunyeEserAdiAlt">Alt Başlık</label>
                                        <textarea class="form-textarea auto-grow-textarea" id="kunyeEserAdiAlt" name="kunyeEserAdiAlt" placeholder="Örnek: Birinci Kısım" rows="1">{{ old('kunyeEserAdiAlt', $kitap->kunyeEserAdiAlt) }}</textarea>
                                    </div>
                                    @if($isViewOnly)
                                    <div class="form-field" style="grid-column: span 2;">
                                        <span class="form-label">Yazar(lar)</span>
                                        <div class="form-input" style="min-height:40px;display:flex;align-items:center;background:var(--secondary);color:var(--foreground);">{{ $kitap->formattedYazarlarAdi() ?: '—' }}</div>
                                    </div>
                                    @else
                                    <div class="form-field" style="grid-column: span 2;">
                                        <span class="form-label">Yazar(lar)</span>
                                        <div class="yazar-giris-tip-row" style="display:flex;flex-wrap:wrap;gap:18px;margin-top:6px;margin-bottom:10px;">
                                            <label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;font-size:14px;">
                                                <input type="radio" name="yazar_giris_tipi" id="yazarGirisKayitli" value="kayitli" {{ old('yazar_giris_tipi', 'kayitli') === 'kayitli' ? 'checked' : '' }} />
                                                <span>Kayıtlı Yazarlardan Seç</span>
                                            </label>
                                            <label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;font-size:14px;">
                                                <input type="radio" name="yazar_giris_tipi" id="yazarGirisManuel" value="manuel" {{ old('yazar_giris_tipi') === 'manuel' ? 'checked' : '' }} />
                                                <span>Manuel Giriş</span>
                                            </label>
                                        </div>

                                        <fieldset id="yazarKayitliFieldset" style="border:none;padding:0;margin:0;min-width:0;">
                                            <div id="yazarKayitliPickArea" style="{{ old('yazar_giris_tipi') === 'manuel' ? 'display:none;' : '' }}">
                                                <p class="form-hint" style="margin:0 0 8px;font-size:12px;color:var(--muted-foreground);">Listeden arayıp seçin; birden fazla yazar ekleyebilirsiniz. Yalnızca listedeki kayıtlar seçilebilir.</p>
                                                <div class="combobox-wrapper" id="authorCombobox">
                                                    <div class="combobox-input-wrap">
                                                        <input type="text" class="form-input" id="kunyeYazarSearch" placeholder="Yazar ara…" autocomplete="off" />
                                                        <button type="button" class="combobox-toggle" tabindex="-1" aria-label="Seçenekleri aç">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                                        </button>
                                                    </div>
                                                    <div class="combobox-dropdown"></div>
                                                </div>
                                                <input type="hidden" name="kunyeYazar" id="kunyeYazar" value="{{ old('kunyeYazar', $kitap->kunyeYazar) }}" />
                                                <div id="yazarKayitliChips" style="display:flex;flex-wrap:wrap;gap:6px;margin-top:10px;min-height:28px;"></div>
                                                <div id="yazarKayitliHiddenIds"></div>
                                            </div>
                                        </fieldset>

                                        <fieldset id="yazarManuelFieldset" disabled style="border:none;padding:0;margin:0;margin-top:12px;min-width:0;{{ old('yazar_giris_tipi') === 'manuel' ? '' : 'display:none;' }}">
                                            <p class="form-hint" style="margin:0 0 8px;font-size:12px;color:var(--muted-foreground);">Ad ve soyadı girin, «Ekle» ile listeye ekleyin.</p>
                                            <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:10px;align-items:end;max-width:720px;">
                                                <div class="form-field" style="margin:0;">
                                                    <label class="form-label" for="yazarManuelAdEntry">Yazar Adı</label>
                                                    <input type="text" class="form-input" id="yazarManuelAdEntry" autocomplete="given-name" />
                                                </div>
                                                <div class="form-field" style="margin:0;">
                                                    <label class="form-label" for="yazarManuelSoyadEntry">Yazar Soyadı</label>
                                                    <input type="text" class="form-input" id="yazarManuelSoyadEntry" autocomplete="family-name" />
                                                </div>
                                                <button type="button" class="btn btn-outline" id="yazarManuelEkleBtn" style="height:38px;white-space:nowrap;">Ekle</button>
                                            </div>
                                            <div id="yazarManuelChips" style="display:flex;flex-wrap:wrap;gap:6px;margin-top:10px;min-height:28px;"></div>
                                            <div id="yazarManuelHiddenPairs"></div>
                                        </fieldset>

                                        <script id="yazarData" type="application/json">
                                            @json($yazarlar->map(fn($y) => ['id' => $y->id, 'ad' => $y->tam_ad]))
                                        </script>
                                        <script>
                                            window.__initialYazarIds = @json($kitap->yazarlar->pluck('id')->values()->all());
                                        </script>
                                    </div>
                                    @endif
                                    <div class="form-field">
                                        <label class="form-label" for="kunyeISBNISSN">ISBN / ISSN</label>
                                        <div class="input-with-icon" style="position:relative;">
                                            <input type="text" class="form-input" id="kunyeISBNISSN" name="kunyeISBNISSN" placeholder="978-3-16-148410-0" value="{{ old('kunyeISBNISSN', $kitap->kunyeISBNISSN) }}" style="padding-right:{{ $isViewOnly ? '12px' : '40px' }};" />
                                            @if(!$isViewOnly)
                                            <button type="button" class="isbn-search-btn" id="isbnSearchBtn" title="ISBN ile kitap bilgilerini getir" aria-label="ISBN ara">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="kunyeDemirbasKN">Demirbaş / Künye No</label>
                                        <input type="text" class="form-input" id="kunyeDemirbasKN" name="kunyeDemirbasKN"
                                               value="{{ $kitap->kunyeDemirbasKN }}"
                                               readonly
                                               style="background:var(--secondary);color:var(--muted-foreground);cursor:default;"
                                               title="Demirbaş numarası değiştirilemez" />
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="kunyeCilt">Cilt</label>
                                        <textarea class="form-textarea auto-grow-textarea" id="kunyeCilt" name="kunyeCilt" placeholder="Örnek: 1. Cilt" rows="1">{{ old('kunyeCilt', $kitap->kunyeCilt) }}</textarea>
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="kunyeKopya">Kopya Sayısı</label>
                                        <input type="number" class="form-input" id="kunyeKopya" name="kunyeKopya" min="1" value="{{ old('kunyeKopya', $kitap->kunyeKopya) }}" />
                                    </div>
                                </div>

                                {{-- Tür / Alt Tür / Şekil / Ortam — arama destekli combobox --}}
                                <div class="form-grid cols-2" style="margin-top:16px;">
                                    <div class="form-field">
                                        <label class="form-label" for="turIdSearch">Tür</label>
                                        <div class="combobox-wrapper" id="turCombobox">
                                            <div class="combobox-input-wrap">
                                                <input type="text" class="form-input" id="turIdSearch" placeholder="Tür ara veya seçin…" autocomplete="off" value="{{ $turler->firstWhere('id', old('turId', $kitap->turId))?->ad ?? '' }}" />
                                                <button type="button" class="combobox-toggle" tabindex="-1" aria-label="Seçenekleri aç">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                                </button>
                                            </div>
                                            <div class="combobox-dropdown"></div>
                                        </div>
                                        <input type="hidden" id="turId" name="turId" value="{{ old('turId', $kitap->turId) }}" />
                                        <script id="turData" type="application/json">@json($turler->map(fn($t) => ['id' => $t->id, 'ad' => $t->ad]))</script>
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="altTurIdSearch">Alt Tür</label>
                                        <div class="combobox-wrapper" id="altTurCombobox">
                                            <div class="combobox-input-wrap">
                                                <input type="text" class="form-input" id="altTurIdSearch" placeholder="Alt tür ara veya seçin…" autocomplete="off" value="{{ $altturler->firstWhere('id', old('altTurId', $kitap->altTurId))?->ad ?? '' }}" />
                                                <button type="button" class="combobox-toggle" tabindex="-1" aria-label="Seçenekleri aç">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                                </button>
                                            </div>
                                            <div class="combobox-dropdown"></div>
                                        </div>
                                        <input type="hidden" id="altTurId" value="{{ old('altTurId', $kitap->altTurId) }}" />
                                        <input type="hidden" id="altTurAd" name="altTurAd" value="{{ old('altTurAd', $altturler->firstWhere('id', old('altTurId', $kitap->altTurId))?->ad ?? '') }}" />
                                        <script id="altTurData" type="application/json">@json($altturler->map(fn($t) => ['id' => $t->id, 'ad' => $t->ad]))</script>
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="sekilIdSearch">Şekil</label>
                                        <div class="combobox-wrapper" id="sekilCombobox">
                                            <div class="combobox-input-wrap">
                                                <input type="text" class="form-input" id="sekilIdSearch" placeholder="Şekil ara veya seçin…" autocomplete="off" value="{{ $sekiller->firstWhere('id', old('sekilId', $kitap->sekilId))?->ad ?? '' }}" />
                                                <button type="button" class="combobox-toggle" tabindex="-1" aria-label="Seçenekleri aç">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                                </button>
                                            </div>
                                            <div class="combobox-dropdown"></div>
                                        </div>
                                        <input type="hidden" id="sekilId" name="sekilId" value="{{ old('sekilId', $kitap->sekilId) }}" />
                                        <script id="sekilData" type="application/json">@json($sekiller->map(fn($t) => ['id' => $t->id, 'ad' => $t->ad]))</script>
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="ortamIdSearch">Ortam</label>
                                        <div class="combobox-wrapper" id="ortamCombobox">
                                            <div class="combobox-input-wrap">
                                                <input type="text" class="form-input" id="ortamIdSearch" placeholder="Ortam ara veya seçin…" autocomplete="off" value="{{ $ortamlar->firstWhere('id', old('ortamId', $kitap->ortamId))?->ad ?? '' }}" />
                                                <button type="button" class="combobox-toggle" tabindex="-1" aria-label="Seçenekleri aç">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                                </button>
                                            </div>
                                            <div class="combobox-dropdown"></div>
                                        </div>
                                        <input type="hidden" id="ortamId" name="ortamId" value="{{ old('ortamId', $kitap->ortamId) }}" />
                                        <script id="ortamData" type="application/json">@json($ortamlar->map(fn($t) => ['id' => $t->id, 'ad' => $t->ad]))</script>
                                    </div>
                                </div>

                                {{-- Üst Eser --}}
                                <div style="margin-top:16px;">
                                    <div class="form-field" id="ustEserSearchField" style="{{ \App\Models\Katalog::find(old('ustEserKatalogId', $kitap->ustEserKatalogId)) ? 'display:none;' : '' }}">
                                        <label class="form-label" for="ustEserSearch">Üst Eser <small style="font-weight:400;color:var(--muted-foreground);">(varsa bağlı olduğu üst eser)</small></label>
                                        <div class="ue-autocomplete-wrap">
                                            <input type="text" id="ustEserSearch" class="form-input"
                                                   placeholder="Eser adı, yazar veya ISBN ile arayın…"
                                                   autocomplete="off" />
                                            <div class="ue-autocomplete-dropdown" id="ustEserDropdown"></div>
                                        </div>
                                    </div>
                                    <div id="ustEserCard" class="ue-selected-card" style="{{ \App\Models\Katalog::find(old('ustEserKatalogId', $kitap->ustEserKatalogId)) ? 'display:flex;' : 'display:none;' }}">
                                        <div id="ustEserCoverWrap">
                                            @if(optional(\App\Models\Katalog::find(old('ustEserKatalogId', $kitap->ustEserKatalogId)))->kunyeKapakResmi)
                                                <img src="{{ asset('storage/' . optional(\App\Models\Katalog::find(old('ustEserKatalogId', $kitap->ustEserKatalogId)))->kunyeKapakResmi) }}" class="ue-selected-cover" />
                                            @endif
                                        </div>
                                        <div class="ue-selected-info">
                                            <div class="ue-selected-name" id="ustEserName">{{ optional(\App\Models\Katalog::find(old('ustEserKatalogId', $kitap->ustEserKatalogId)))->kunyeEserAdi ?? '—' }}</div>
                                            <div class="ue-selected-meta" id="ustEserMeta">{{ optional(\App\Models\Katalog::find(old('ustEserKatalogId', $kitap->ustEserKatalogId)))->kunyeYazar ?? '' }}{{ optional(\App\Models\Katalog::find(old('ustEserKatalogId', $kitap->ustEserKatalogId)))->kunyeDemirbasKN ? ' · Demirbaş: ' . optional(\App\Models\Katalog::find(old('ustEserKatalogId', $kitap->ustEserKatalogId)))->kunyeDemirbasKN : '' }}{{ optional(\App\Models\Katalog::find(old('ustEserKatalogId', $kitap->ustEserKatalogId)))->kunyeISBNISSN ? ' · ISBN: ' . optional(\App\Models\Katalog::find(old('ustEserKatalogId', $kitap->ustEserKatalogId)))->kunyeISBNISSN : '' }}</div>
                                        </div>
                                        <button type="button" class="ue-selected-clear" onclick="clearUstEser()" title="Üst Eseri Kaldır">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                        </button>
                                    </div>
                                    <input type="hidden" id="ustEserKatalogId" name="ustEserKatalogId" value="{{ old('ustEserKatalogId', $kitap->ustEserKatalogId) }}" />
                                </div>
                            </div>

                            <div class="section-sep"></div>

                            <!-- Section 2: Yayın Bilgileri -->
                            <div>
                                <h3 class="section-header">
                                    <span class="section-number">2</span>
                                    Yayın Bilgileri
                                </h3>
                                <div class="form-grid cols-3">
                                    <div class="form-field">
                                        <label class="form-label" for="kunyeYayinlayanSearch">Yayınlayan</label>
                                        <div class="combobox-wrapper" id="publisherCombobox">
                                            <div class="combobox-input-wrap">
                                                <input type="text" class="form-input" id="kunyeYayinlayanSearch" placeholder="Yayınevi yazın veya seçin" autocomplete="off" value="{{ old('kunyeYayinlayan', $kitap->kunyeYayinlayan) }}" />
                                                <button type="button" class="combobox-toggle" tabindex="-1" aria-label="Seçenekleri aç">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                                </button>
                                            </div>
                                            <div class="combobox-dropdown"></div>
                                        </div>
                                        <input type="hidden" id="kunyeYayinlayan" name="kunyeYayinlayan" value="{{ old('kunyeYayinlayan', $kitap->kunyeYayinlayan) }}" />
                                        <script id="yayineviData" type="application/json">
                                            @json($yayinevleri->map(fn($y) => ['id' => $y->id, 'ad' => $y->ad]))
                                        </script>
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="kunyeYayinTarihi">Yayın Tarihi</label>
                                        <textarea class="form-textarea auto-grow-textarea" id="kunyeYayinTarihi" name="kunyeYayinTarihi" placeholder="Örnek: 2024 veya Mart 2024" rows="1">{{ old('kunyeYayinTarihi', $kitap->kunyeYayinTarihi) }}</textarea>
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="kunyeBasimKaydi">Basım Kaydı</label>
                                        <textarea class="form-textarea auto-grow-textarea" id="kunyeBasimKaydi" name="kunyeBasimKaydi" placeholder="Örnek: 3. baskı" rows="1">{{ old('kunyeBasimKaydi', $kitap->kunyeBasimKaydi) }}</textarea>
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="kunyeYayinYeri">Yayın Yeri</label>
                                        <textarea class="form-textarea auto-grow-textarea" id="kunyeYayinYeri" name="kunyeYayinYeri" placeholder="Örnek: İstanbul" rows="1">{{ old('kunyeYayinYeri', $kitap->kunyeYayinYeri) }}</textarea>
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="kunyeSorumlular">Sorumlular <small style="font-weight:400; color:var(--muted-foreground);">(Çevirmen, editör vb.)</small></label>
                                        <textarea class="form-textarea auto-grow-textarea" id="kunyeSorumlular" name="kunyeSorumlular" placeholder="Örnek: Çev. Ahmet Yılmaz ; Ed. Mehmet Demir" rows="1">{{ old('kunyeSorumlular', $kitap->kunyeSorumlular) }}</textarea>
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="kunyeDiziKaydi">Dizi Kaydı</label>
                                        <textarea class="form-textarea auto-grow-textarea" id="kunyeDiziKaydi" name="kunyeDiziKaydi" placeholder="Örnek: Dünya Klasikleri ; 12" rows="1">{{ old('kunyeDiziKaydi', $kitap->kunyeDiziKaydi) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="section-sep"></div>

                            <!-- Section 3: Sınıflandırma & Fiziksel -->
                            <div>
                                <h3 class="section-header">
                                    <span class="section-number">3</span>
                                    Sınıflandırma &amp; Fiziksel Özellikler
                                </h3>
                                <div class="form-grid cols-3">
                                    <div class="form-field">
                                        <label class="form-label" for="kunyeKategoriSearch">Kategori</label>
                                        <div class="combobox-wrapper" id="categoryCombobox">
                                            <div class="combobox-input-wrap">
                                                <input type="text" class="form-input" id="kunyeKategoriSearch" placeholder="Kategori ara veya seçin" autocomplete="off" />
                                                <button type="button" class="combobox-toggle" tabindex="-1" aria-label="Seçenekleri aç">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                                </button>
                                            </div>
                                            <div class="combobox-dropdown"></div>
                                        </div>
                                        {{-- Gerçek değer (id) buraya yazılır --}}
                                        <input type="hidden" id="kunyeKategori" name="kunyeKategori" value="{{ old('kunyeKategori', $kitap->kunyeKategori) }}" />
                                        {{-- JS için veri kaynağı --}}
                                        <script id="kategoriData" type="application/json">
                                            @json($kategoriler->map(fn($k) => ['id' => $k->id, 'title' => $k->title]))
                                        </script>
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="kunyeSiniflamaYer">Sınıflama / Yer Kodu</label>
                                        <textarea class="form-textarea auto-grow-textarea" id="kunyeSiniflamaYer" name="kunyeSiniflamaYer" placeholder="Örnek: 603.41/FER" rows="1">{{ old('kunyeSiniflamaYer', $kitap->kunyeSiniflamaYer) }}</textarea>
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="koleksiyon_id">Koleksiyon</label>
                                        <select class="form-select" id="koleksiyon_id" name="koleksiyon_id" {{ $isViewOnly ? 'disabled' : '' }}>
                                            <option value="">— Seçiniz —</option>
                                            @foreach($koleksiyonlar as $kol)
                                                <option value="{{ $kol->id }}" {{ (string) old('koleksiyon_id', $kitap->koleksiyon_id ?? '') === (string) $kol->id ? 'selected' : '' }}>{{ $kol->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="kunyeKonuBasligi">Konu Başlığı</label>
                                        <textarea class="form-textarea auto-grow-textarea" id="kunyeKonuBasligi" name="kunyeKonuBasligi" placeholder="Örnek: Roman -- Fransız edebiyatı" rows="1">{{ old('kunyeKonuBasligi', $kitap->kunyeKonuBasligi) }}</textarea>
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="kunyeDilKN">Dil</label>
                                        <select class="form-select" id="kunyeDilKN" name="kunyeDilKN">
                                            <option value="" disabled {{ old('kunyeDilKN', $kitap->kunyeDilKN) ? '' : 'selected' }}>Dil seçin</option>
                                            @foreach(['Türkçe','İngilizce','Almanca','Fransızca','Arapça','İspanyolca','Rusça','Farsça','Diğer'] as $dil)
                                                <option value="{{ $dil }}" {{ old('kunyeDilKN', $kitap->kunyeDilKN) == $dil ? 'selected' : '' }}>{{ $dil }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="kunyeDil2">2. Dil <small style="font-weight:400;color:var(--muted-foreground);">(varsa)</small></label>
                                        <select class="form-select" id="kunyeDil2" name="kunyeDil2">
                                            <option value="" {{ old('kunyeDil2', $kitap->kunyeDil2) ? '' : 'selected' }}>— Yok —</option>
                                            @foreach(['Türkçe','İngilizce','Almanca','Fransızca','Arapça','İspanyolca','Rusça','Farsça','Diğer'] as $dil)
                                                <option value="{{ $dil }}" {{ old('kunyeDil2', $kitap->kunyeDil2) == $dil ? 'selected' : '' }}>{{ $dil }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="kunyeFizikselTanim">Fiziksel Tanım</label>
                                        <textarea class="form-textarea auto-grow-textarea" id="kunyeFizikselTanim" name="kunyeFizikselTanim" placeholder="Örnek: 350 s. ; 21 cm." rows="1">{{ old('kunyeFizikselTanim', $kitap->kunyeFizikselTanim) }}</textarea>
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="kunyeSayfaSayisi">Sayfa Sayısı</label>
                                        <input type="number" class="form-input" id="kunyeSayfaSayisi" name="kunyeSayfaSayisi" placeholder="Örnek: 350" min="1" value="{{ old('kunyeSayfaSayisi', $kitap->kunyeSayfaSayisi) }}" />
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="kunyeGelisTarihi">Geliş Tarihi</label>
                                        <input type="date" class="form-input" id="kunyeGelisTarihi" name="kunyeGelisTarihi" placeholder="Geliş Tarihi" value="{{ old('kunyeGelisTarihi', $kitap->kunyeGelisTarihi) }}" />
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="icerik">İçindekiler</label>
                                        <textarea class="form-textarea auto-grow-textarea" id="icerik" name="icerik" placeholder="Kitabın içindekiler bilgisi…" rows="1">{{ old('icerik', $kitap->icerik) }}</textarea>
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="aciklama">Açıklama</label>
                                        <textarea class="form-textarea auto-grow-textarea" id="aciklama" name="aciklama" placeholder="Kitap hakkında açıklama…" rows="1">{{ old('aciklama', $kitap->aciklama) }}</textarea>
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="ozelNotlar">Özel Notlar</label>
                                        <textarea class="form-textarea auto-grow-textarea" id="ozelNotlar" name="ozelNotlar" placeholder="Kütüphaneye özel notlar…" rows="1">{{ old('ozelNotlar', $kitap->ozelNotlar) }}</textarea>
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="ozelNotlar2">Özel Notlar 2</label>
                                        <textarea class="form-textarea auto-grow-textarea" id="ozelNotlar2" name="ozelNotlar2" placeholder="Ek özel notlar…" rows="1">{{ old('ozelNotlar2', $kitap->ozelNotlar2) }}</textarea>
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="ozelNotlar3">Özel Notlar 3</label>
                                        <textarea class="form-textarea auto-grow-textarea" id="ozelNotlar3" name="ozelNotlar3" placeholder="Ek özel notlar…" rows="1">{{ old('ozelNotlar3', $kitap->ozelNotlar3) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="section-sep"></div>

                            <!-- Section 4: Stok & Durum -->
                            <div>
                                <h3 class="section-header">
                                    <span class="section-number">4</span>
                                    Satın Alma Bilgileri
                                </h3>
                                {{-- Giriş Türü verisi (JS için) --}}
                                <script id="giristuruData" type="application/json">
                                    @json($girisTurleri->map(fn($g) => ['id' => $g->id, 'ad' => $g->ad]))
                                </script>

                                <!-- Satınalma Bilgileri Alt Başlığı -->
                                <div style="margin-top:24px;">
                                    <div class="form-grid cols-3">
                                        <div class="form-field">
                                            <label class="form-label" for="girisTuruId">Giriş Türü</label>
                                            <select class="form-select" id="girisTuruId" name="girisTuruId">
                                                <option value="">— Seçiniz —</option>
                                                @foreach($girisTurleri as $gt)
                                                    <option value="{{ $gt->id }}"
                                                            data-ad="{{ $gt->ad }}"
                                                        {{ old('girisTuruId', $kitap->girisTuruId) == $gt->id ? 'selected' : '' }}>
                                                        {{ $gt->ad }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Satın Alma Alanları -->
                                    <div id="satin-alma-fields" class="form-grid cols-3" style="display:none;margin-top:16px;">
                                        <div class="form-field">
                                            <label class="form-label" for="faturaNo">Fatura No</label>
                                            <textarea class="form-textarea auto-grow-textarea" id="faturaNo" name="faturaNo"
                                                   placeholder="Örnek: FTR-2024-001" rows="1">{{ old('faturaNo', $kitap->faturaNo) }}</textarea>
                                        </div>
                                        <div class="form-field">
                                            <label class="form-label" for="faturaTarihi">Fatura Tarihi</label>
                                            <input type="date" class="form-input" id="faturaTarihi" name="faturaTarihi"
                                                   value="{{ old('faturaTarihi', $kitap->faturaTarihi ? \Carbon\Carbon::parse($kitap->faturaTarihi)->format('Y-m-d') : '') }}" />
                                        </div>
                                        <div class="form-field">
                                            <label class="form-label" for="tedarikci">Firma Adı</label>
                                            <textarea class="form-textarea auto-grow-textarea" id="tedarikci"
                                                   placeholder="Örnek: Kitapsan A.Ş." rows="1">{{ old('tedarikci', $kitap->tedarikci) }}</textarea>
                                        </div>
                                        <div class="form-field">
                                            <label class="form-label" for="tedarikciTelefon">Telefon</label>
                                            <textarea class="form-textarea auto-grow-textarea" id="tedarikciTelefon"
                                                   placeholder="Örnek: 0212 555 00 00" rows="1">{{ old('tedarikciTelefon', $kitap->tedarikciTelefon) }}</textarea>
                                        </div>
                                        <div class="form-field">
                                            <label class="form-label" for="tedarikciEposta">E-posta Adresi</label>
                                            <input type="email" class="form-input" id="tedarikciEposta"
                                                   placeholder="ornek@firma.com"
                                                   value="{{ old('tedarikciEposta', $kitap->tedarikciEposta) }}" />
                                        </div>
                                        <div class="form-field">
                                            <label class="form-label" for="fiyat">Fiyat (₺)</label>
                                            <input type="number" class="form-input" id="fiyat" name="fiyat"
                                                   placeholder="0.00" step="0.01" min="0"
                                                   value="{{ old('fiyat', $kitap->fiyat) }}" />
                                        </div>
                                    </div>

                                    <!-- Hibe Alanları -->
                                    <div id="hibe-fields" class="form-grid cols-3" style="display:none;margin-top:16px;">
                                        <div class="form-field">
                                            <label class="form-label" for="tedarikci_hibe">Hibe Eden</label>
                                            <input type="text" class="form-input" id="tedarikci_hibe"
                                                   placeholder="Kurum veya kişi adı"
                                                   value="{{ old('tedarikci', $kitap->tedarikci) }}" />
                                        </div>
                                        <div class="form-field">
                                            <label class="form-label" for="tedarikciTelefon_hibe">Telefon</label>
                                            <input type="text" class="form-input" id="tedarikciTelefon_hibe"
                                                   placeholder="Örnek: 0212 555 00 00"
                                                   value="{{ old('tedarikciTelefon', $kitap->tedarikciTelefon) }}" />
                                        </div>
                                        <div class="form-field">
                                            <label class="form-label" for="tedarikciEposta_hibe">E-posta Adresi</label>
                                            <input type="email" class="form-input" id="tedarikciEposta_hibe"
                                                   placeholder="ornek@kurum.com"
                                                   value="{{ old('tedarikciEposta', $kitap->tedarikciEposta) }}" />
                                        </div>
                                    </div>

                                    <!-- Bağış Alanları -->
                                    <div id="bagis-fields" class="form-grid cols-3" style="display:none;margin-top:16px;">
                                        <div class="form-field">
                                            <label class="form-label" for="tedarikci_bagis">Bağışlayan</label>
                                            <input type="text" class="form-input" id="tedarikci_bagis"
                                                   placeholder="Bağışlayan kişi veya kurum"
                                                   value="{{ old('tedarikci', $kitap->tedarikci) }}" />
                                        </div>
                                        <div class="form-field">
                                            <label class="form-label" for="tedarikciTelefon_bagis">Telefon</label>
                                            <input type="text" class="form-input" id="tedarikciTelefon_bagis"
                                                   placeholder="Örnek: 0212 555 00 00"
                                                   value="{{ old('tedarikciTelefon', $kitap->tedarikciTelefon) }}" />
                                        </div>
                                        <div class="form-field">
                                            <label class="form-label" for="tedarikciEposta_bagis">E-posta Adresi</label>
                                            <input type="email" class="form-input" id="tedarikciEposta_bagis"
                                                   placeholder="ornek@eposta.com"
                                                   value="{{ old('tedarikciEposta', $kitap->tedarikciEposta) }}" />
                                        </div>
                                    </div>

                                    {{-- Gizli gerçek input'lar - JS ile senkronize edilir --}}
                                    <input type="hidden" id="tedarikci_hidden"        name="tedarikci"        value="{{ old('tedarikci',        $kitap->tedarikci) }}" />
                                    <input type="hidden" id="tedarikciTelefon_hidden" name="tedarikciTelefon" value="{{ old('tedarikciTelefon', $kitap->tedarikciTelefon) }}" />
                                    <input type="hidden" id="tedarikciEposta_hidden"  name="tedarikciEposta"  value="{{ old('tedarikciEposta',  $kitap->tedarikciEposta) }}" />
                                </div>
                            </div>

                            <div>
                                <h3 class="section-header">
                                    <span class="section-number">5</span>
                                    Stok &amp; Durum
                                </h3>
                                <div class="form-grid cols-3">
                                    <div class="form-field">
                                        <label class="form-label" for="kunyeDurum">Durum</label>
                                        @if(in_array($kitap->kunyeDurum, ['Ödünç', 'Rezerve'], true))
                                            <select class="form-select" id="kunyeDurum" disabled aria-describedby="kunyeDurumKilitAciklama">
                                                @foreach(['Rafta' => 'Rafta (Müsait)', 'Ödünç' => 'Ödünç Verildi', 'Rezerve' => 'Rezerve Edildi', 'Kayıp' => 'Kayıp', 'Bakımda' => 'Bakımda / Onarımda', 'Hurdaya Ayrıldı' => 'Hurdaya Ayrıldı'] as $val => $label)
                                                    <option value="{{ $val }}" {{ old('kunyeDurum', $kitap->kunyeDurum) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="kunyeDurum" value="{{ old('kunyeDurum', $kitap->kunyeDurum) }}" tabindex="-1" aria-hidden="true" />
                                            <p id="kunyeDurumKilitAciklama" class="form-hint" style="margin-top:6px;font-size:12px;color:var(--muted-foreground);">
                                                Ödünçte veya rezerve iken durum sistem tarafından yönetilir; buradan değiştirilemez.
                                            </p>
                                        @else
                                            <select class="form-select" id="kunyeDurum" name="kunyeDurum">
                                                @foreach(['Rafta' => 'Rafta (Müsait)', 'Kayıp' => 'Kayıp', 'Bakımda' => 'Bakımda / Onarımda', 'Hurdaya Ayrıldı' => 'Hurdaya Ayrıldı'] as $val => $label)
                                                    <option value="{{ $val }}" {{ old('kunyeDurum', $kitap->kunyeDurum) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <p class="form-hint" style="margin-top:6px;font-size:12px;color:var(--muted-foreground);">
                                                Rafta, kayıp, bakımda veya hurda olarak güncelleyebilirsiniz.
                                            </p>
                                        @endif
                                    </div>

                                    <div class="form-field">
                                        <label class="form-label" for="kutuphaneId">
                                            Kütüphane
                                        </label>
                                        <div style="display:flex; gap:10px; align-items:flex-start; flex-wrap:wrap;">
                                            <select class="form-select" id="kutuphaneId" name="kutuphaneId" disabled style="flex:1; min-width:200px;">
                                                <option value="">— Seçiniz —</option>
                                                @foreach($kutuphaneler as $kutuphane)
                                                    <option value="{{ $kutuphane->id }}"
                                                        {{ old('kutuphaneId', $kitap->kutuphaneId) == $kutuphane->id ? 'selected' : '' }}>
                                                        {{ $kutuphane->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if(!$isViewOnly && $canTransferKutuphane && $kutuphaneTransferTargets->isNotEmpty())
                                                <button type="button" class="btn btn-outline" id="btnKatalogTransferOpen" style="flex-shrink:0;">Transfer</button>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-field">
                                        <span class="form-label">Özellikler</span>
                                        <div class="checkbox-group" style="margin-top:2px;">
                                            <label class="checkbox-item">
                                                <input type="checkbox" id="oduncVerilemez" name="oduncVerilemez" value="true" {{ old('oduncVerilemez', $kitap->oduncVerilemez == "true") ? 'checked' : '' }}>
                                                <span class="checkbox-box">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                </span>
                                                <span class="checkbox-label">
                                                    Ödünç Verilemez
                                                    <small>Bu kitap ödünç işlemine kapatılır</small>
                                                </span>
                                            </label>
                                            <label class="checkbox-item">
                                                <input type="checkbox" id="etiketlendi" name="etiketlendi" value="1" {{ old('etiketlendi', $kitap->etiketlendi) ? 'checked' : '' }}>
                                                <span class="checkbox-box">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                </span>
                                                <span class="checkbox-label">
                                                    Etiketlendi
                                                    <small>Kitap etiketi yapıştırıldı</small>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- Actions -->
                            <div class="form-actions">
                                @if($isViewOnly)
                                    <a href="{{ route('katalog.index') }}" class="btn btn-outline">
                                        Listeye Dön
                                    </a>
                                @endif
                                <button type="button" class="btn btn-outline" id="resetBtn" style="{{ $isViewOnly ? 'display:none;' : '' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                    Değişiklikleri Sıfırla
                                </button>
                                <button type="submit" class="btn btn-primary" style="{{ $isViewOnly ? 'display:none;' : '' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/><line x1="12" x2="12" y1="7" y2="7"/></svg>
                                    Güncelle
                                </button>
                            </div>

                            {{-- Kaydeden & Güncelleyen Bilgileri --}}
                            <div style="margin-top:20px; padding-top:16px; border-top:1px solid var(--border); display:flex; flex-direction:column; gap:6px;">
                                <div style="font-size:13px; color:var(--muted-foreground);">
                                    <span style="font-weight:600; color:var(--foreground);">Kaydeden:</span>
                                    {{ $createdUser ? $createdUser->name : ($kitap->created_user ? '#'.$kitap->created_user : '—') }}
                                    @if($kitap->created_at)
                                        <span style="opacity:0.6; margin-left:6px;">{{ \Carbon\Carbon::parse($kitap->created_at)->format('d.m.Y H:i') }}</span>
                                    @endif
                                </div>
                                <div style="font-size:13px; color:var(--muted-foreground);">
                                    <span style="font-weight:600; color:var(--foreground);">Son Güncelleyen:</span>
                                    {{ $updatedUser ? $updatedUser->name : ($kitap->updated_user ? '#'.$kitap->updated_user : '—') }}
                                    @if($kitap->updated_at && $kitap->updated_at != $kitap->created_at)
                                        <span style="opacity:0.6; margin-left:6px;">{{ \Carbon\Carbon::parse($kitap->updated_at)->format('d.m.Y H:i') }}</span>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </form>
@endsection

@section('scripts')
<script>
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
    // Cover Image Upload
    // ============================
    var coverUploadArea = document.getElementById('coverUploadArea');
    var coverInput = document.getElementById('coverInput');
    var coverPlaceholder = document.getElementById('coverPlaceholder');
    var coverChangeHint = document.getElementById('coverChangeHint');
    var currentPreviewImg = null;
    var removeBtn = null;

    coverUploadArea.addEventListener('click', function(e) {
        // Yeni resim kaldırma butonu
        if (removeBtn && (e.target === removeBtn || removeBtn.contains(e.target))) return;
        // Mevcut resim kaldırma butonu
        var existingRemoveBtn = document.getElementById('coverRemoveBtn');
        if (existingRemoveBtn && (e.target === existingRemoveBtn || existingRemoveBtn.contains(e.target))) return;
        coverInput.click();
    });

    // Mevcut (DB'deki) kapak resmi kaldırma
    var existingRemoveBtn = document.getElementById('coverRemoveBtn');
    if (existingRemoveBtn) {
        existingRemoveBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            var existingImg = document.getElementById('existingCoverImg');
            if (existingImg) existingImg.style.display = 'none';
            existingRemoveBtn.style.display = 'none';
            coverPlaceholder.style.display = 'flex';
            coverUploadArea.classList.remove('has-image');
            var hintEl = document.getElementById('coverChangeHint');
            if (hintEl) hintEl.style.display = 'none';
            document.getElementById('kapakSilInput').value = '1';
        });
    }

    coverUploadArea.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            coverInput.click();
        }
    });

    coverUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        coverUploadArea.classList.add('drag-over');
    });

    coverUploadArea.addEventListener('dragleave', function() {
        coverUploadArea.classList.remove('drag-over');
    });

    function isLikelyImageFile(file) {
        if (!file) return false;
        if (file.type && file.type.indexOf('image/') === 0) return true;
        return /\.(jpe?g|png|gif|webp|bmp|svg)$/i.test(file.name || '');
    }

    function assignFileToCoverInput(file) {
        try {
            var dt = new DataTransfer();
            dt.items.add(file);
            coverInput.files = dt.files;
        } catch (err) {
            console.error('Kapak dosyası inputa atanamadı:', err);
        }
    }

    coverUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        coverUploadArea.classList.remove('drag-over');
        var file = e.dataTransfer.files && e.dataTransfer.files[0];
        if (file && isLikelyImageFile(file)) {
            handleCoverFile(file);
        }
    });

    coverInput.addEventListener('change', function() {
        var file = this.files[0];
        if (file) handleCoverFile(file);
    });

    function handleCoverFile(file) {
        assignFileToCoverInput(file);
        var isbnHidden = document.getElementById('isbnCoverUrl');
        if (isbnHidden) isbnHidden.value = '';
        var kapakSil = document.getElementById('kapakSilInput');
        if (kapakSil) kapakSil.value = '0';
        var existingImg = document.getElementById('existingCoverImg');
        if (existingImg) existingImg.style.display = 'none';
        var existingRmBtn = document.getElementById('coverRemoveBtn');
        if (existingRmBtn) existingRmBtn.style.display = 'none';

        var reader = new FileReader();
        reader.onloadend = function() {
            showCoverPreview(reader.result);
        };
        reader.readAsDataURL(file);
    }

    function showCoverPreview(src) {
        coverPlaceholder.style.display = 'none';
        coverUploadArea.classList.add('has-image');

        if (currentPreviewImg) {
            currentPreviewImg.src = src;
        } else {
            currentPreviewImg = document.createElement('img');
            currentPreviewImg.src = src;
            currentPreviewImg.alt = 'Kitap kapak onizlemesi';
            coverUploadArea.appendChild(currentPreviewImg);
        }

        if (!removeBtn) {
            removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'cover-remove-btn';
            removeBtn.setAttribute('aria-label', 'Resmi kaldir');
            removeBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>';
            removeBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                removeCoverImage();
            });
            coverUploadArea.appendChild(removeBtn);
        }

        if (coverChangeHint) coverChangeHint.style.display = 'block';
    }

    function removeCoverImage() {
        if (currentPreviewImg) {
            if (currentPreviewImg.parentNode) currentPreviewImg.parentNode.removeChild(currentPreviewImg);
            currentPreviewImg = null;
        }
        if (removeBtn) {
            if (removeBtn.parentNode) removeBtn.parentNode.removeChild(removeBtn);
            removeBtn = null;
        }
        coverPlaceholder.style.display = 'flex';
        coverUploadArea.classList.remove('has-image');
        if (coverChangeHint) coverChangeHint.style.display = 'none';
        coverInput.value = '';
        // ISBN URL ve başka kayıttan kopya referansını temizle
        var isbnHidden = document.getElementById('isbnCoverUrl');
        if (isbnHidden) isbnHidden.value = '';
        var copyKap = document.getElementById('copyKapakFromId');
        if (copyKap) copyKap.value = '';
    }

    // ============================
    // Cover Search (ISBN → Kapak)
    // ============================
    (function () {
        var btn       = document.getElementById('coverSearchBtn');
        if (!btn) return;
        var spinSvg   = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;display:block;animation:spin 0.8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';
        var searchSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;display:block"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';

        btn.addEventListener('click', function () {
            var isbn = document.getElementById('kunyeISBNISSN').value.trim();
            if (!isbn) {
                showToast('error', 'Uyarı', 'Kapak görseli aramak için önce bir ISBN numarası girin.');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = spinSvg;

            fetch('{{ route("katalog.coverSearch") }}?isbn=' + encodeURIComponent(isbn), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success && data.cover) {
                        coverInput.value = '';
                        var hidden = document.getElementById('isbnCoverUrl');
                        if (!hidden) {
                            hidden = document.createElement('input');
                            hidden.type = 'hidden';
                            hidden.id   = 'isbnCoverUrl';
                            hidden.name = 'isbn_cover_url';
                            document.getElementById('bookForm').appendChild(hidden);
                        }
                        hidden.value = data.cover;
                        // Mevcut DB kapağını gizle
                        var existingImg = document.getElementById('existingCoverImg');
                        if (existingImg) existingImg.style.display = 'none';
                        var existingRmBtn = document.getElementById('coverRemoveBtn');
                        if (existingRmBtn) existingRmBtn.style.display = 'none';
                        document.getElementById('kapakSilInput').value = '0';
                        showCoverPreview(data.cover);
                        showToast('success', 'Başarılı', 'Kapak görseli getirildi.');
                    } else {
                        showToast('error', 'Bulunamadı', data.message || 'Bu ISBN için kapak görseli bulunamadı.');
                    }
                })
                .catch(function () {
                    showToast('error', 'Hata', 'Kapak sorgusu sırasında bir hata oluştu.');
                })
                .finally(function () {
                    btn.disabled = false;
                    btn.innerHTML = searchSvg;
                });
        });
    })();

    // ============================
    // Form Submit (AJAX) & Reset
    // ============================
    var bookForm = document.getElementById('bookForm');
    var resetBtn = document.getElementById('resetBtn');
    var submitBtn = bookForm.querySelector('button[type="submit"]');
    var submitBtnOriginalHtml = submitBtn.innerHTML;
    var autoGrowEls = Array.prototype.slice.call(document.querySelectorAll('.auto-grow-textarea'));

    function autoGrowTextarea(el) {
        if (!el) return;
        el.style.height = 'auto';
        el.style.height = el.scrollHeight + 'px';
    }

    autoGrowEls.forEach(function(el) {
        autoGrowTextarea(el);
        el.addEventListener('input', function() { autoGrowTextarea(el); });
    });

    bookForm.addEventListener('submit', function(e) {
        e.preventDefault();

        var title  = document.getElementById('kunyeEserAdi').value.trim();

        if (!title) {
            showToast('error', 'Zorunlu alanlar eksik', 'Eser adı zorunludur.');
            return;
        }

        if (window.syncKunyeYazarHiddenForSubmit) window.syncKunyeYazarHiddenForSubmit();
        var altTurSearch = document.getElementById('altTurIdSearch');
        if (altTurSearch) document.getElementById('altTurAd').value = altTurSearch.value.trim();
        var publisherSearch = document.getElementById('kunyeYayinlayanSearch');
        if (publisherSearch) document.getElementById('kunyeYayinlayan').value = publisherSearch.value.trim();

        // Butonu yüklenme moduna al
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin 0.8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Güncelleniyor...';

        // Overlay'i göster
        document.getElementById('loadingOverlay').classList.add('visible');

        // Laravel PUT → _method spoofing FormData'ya zaten ekli
        var formData = new FormData(bookForm);

        fetch(bookForm.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                    ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    : formData.get('_token')
            },
            body: formData
        })
            .then(function(res) {
                return res.json().then(function(data) {
                    return { status: res.status, data: data };
                });
            })
            .then(function(result) {
                if (result.status === 200 && result.data.success) {
                    showToast('success', 'Güncelleme Başarılı', result.data.message || 'Kitap başarıyla güncellendi.');
                } else if (result.status === 422 && result.data.errors) {
                    var msgs = Object.values(result.data.errors).flat();
                    showToast('error', 'Doğrulama Hatası', msgs[0] || 'Lütfen formu kontrol edin.');
                } else {
                    showToast('error', 'Hata', result.data.message || 'Güncelleme sırasında bir hata oluştu.');
                }
            })
            .catch(function() {
                showToast('error', 'Bağlantı Hatası', 'Sunucuya ulaşılamadı. Lütfen tekrar deneyin.');
            })
            .finally(function() {
                submitBtn.disabled = false;
                submitBtn.innerHTML = submitBtnOriginalHtml;
                document.getElementById('loadingOverlay').classList.remove('visible');
            });
    });

    resetBtn.addEventListener('click', resetForm);

    // Orijinal DB değerleri — sıfırlama için
    var originalValues = {!! json_encode([
        'kunyeEserAdi'      => $kitap->kunyeEserAdi,
        'kunyeYazar'        => $kitap->kunyeYazar,
        'kunyeISBNISSN'     => $kitap->kunyeISBNISSN,
        'kunyeDemirbasKN'   => $kitap->kunyeDemirbasKN,
        'kunyeCilt'         => $kitap->kunyeCilt,
        'kunyeYayinlayan'   => $kitap->kunyeYayinlayan,
        'kunyeYayinTarihi'  => $kitap->kunyeYayinTarihi,
        'kunyeBasimKaydi'   => $kitap->kunyeBasimKaydi,
        'kunyeYayinYeri'    => $kitap->kunyeYayinYeri,
        'kunyeSorumlular'   => $kitap->kunyeSorumlular,
        'kunyeDiziKaydi'    => $kitap->kunyeDiziKaydi,
        'kunyeSiniflamaYer' => $kitap->kunyeSiniflamaYer,
        'koleksiyon_id'     => $kitap->koleksiyon_id !== null && $kitap->koleksiyon_id !== '' ? (string) $kitap->koleksiyon_id : '',
        'kunyeKategori'     => $kitap->kunyeKategori,
        'kunyeKonuBasligi'  => $kitap->kunyeKonuBasligi,
        'kunyeFizikselTanim'=> $kitap->kunyeFizikselTanim,
        'kunyeSayfaSayisi'  => $kitap->kunyeSayfaSayisi,
        'KGTZ'              => $kitap->KGTZ,
        'KG'                => $kitap->KG,
        'kunyeKopya'        => $kitap->kunyeKopya ?? 1,
        'kunyeDilKN'        => $kitap->kunyeDilKN,
        'kunyeDurum'        => $kitap->kunyeDurum,
        // Yeni alanlar
        'turId'             => $kitap->turId,
        'altTurId'          => $kitap->altTurId,
        'sekilId'           => $kitap->sekilId,
        'ortamId'           => $kitap->ortamId,
        'icerik'            => $kitap->icerik,
        'aciklama'          => $kitap->aciklama,
        'ozelNotlar'        => $kitap->ozelNotlar,
        'ustEserKatalogId'  => $kitap->ustEserKatalogId,
    ]) !!};

    function resetForm() {
        // Her alanı orijinal değerine döndür
        Object.keys(originalValues).forEach(function(key) {
            var el = document.getElementById(key);
            if (el) el.value = originalValues[key];
        });

        // Yazar çoklu seçim + yayınevi search alanlarını senkronize et
        if (window.resetYazarFormToKayitli) window.resetYazarFormToKayitli();
        if (typeof window.__initialYazarIds !== 'undefined' && window.__initialYazarIds.length && window.setYazarKayitliChipsFromIds) {
            window.setYazarKayitliChipsFromIds(window.__initialYazarIds);
        }
        var yayineviSearch = document.getElementById('kunyeYayinlayanSearch');
        if (yayineviSearch) yayineviSearch.value = originalValues.kunyeYayinlayan  || '';

        // Tür / Alt Tür / Şekil / Ortam search inputlarını da sıfırla
        // (initDbCombobox selectOption çağrılmadığı için search'ü manuel set ediyoruz)
        var lookupSearchMap = {
            turIdSearch:    originalValues.turId    ? null : '',
            altTurIdSearch: originalValues.altTurId ? null : '',
            sekilIdSearch:  originalValues.sekilId  ? null : '',
            ortamIdSearch:  originalValues.ortamId  ? null : '',
        };
        // Adları JSON verisinden çöz
        ['tur','altTur','sekil','ortam'].forEach(function(key) {
            var hiddenId  = key + 'Id';
            var searchId  = key + 'IdSearch';
            var scriptId  = key + 'Data';
            var val       = originalValues[hiddenId];
            var searchEl  = document.getElementById(searchId);
            if (!searchEl) return;
            if (!val) { searchEl.value = ''; return; }
            try {
                var data = JSON.parse(document.getElementById(scriptId).textContent || '[]');
                var found = data.find(function(r) { return r.id == val; });
                searchEl.value = found ? found.ad : '';
            } catch(e) { searchEl.value = ''; }
        });

        // Üst Eser sıfırla — orijinal değer varsa tekrar fetch etmek yerine
        // mevcut DOM bilgisini kullan (sayfa yüklendiğinde zaten Blade tarafından doldurulmuş)
        if (!originalValues.ustEserKatalogId) {
            clearUstEser();
        } else {
            // hidden değeri sıfırla ve kaydı göster
            document.getElementById('ustEserKatalogId').value = originalValues.ustEserKatalogId;
            // Kart zaten Blade ile render edilmiş — sadece görünürlüğü düzelt
            document.getElementById('ustEserSearchField').style.display = 'none';
            document.getElementById('ustEserCard').style.display = 'flex';
        }

        // Kapak: kaldırılmışsa geri getir
        document.getElementById('kapakSilInput').value = '0';
        var existingImg = document.getElementById('existingCoverImg');
        if (existingImg) {
            existingImg.style.display = '';
            var coverRemoveBtn = document.getElementById('coverRemoveBtn');
            if (coverRemoveBtn) coverRemoveBtn.style.display = '';
        }
        // Yeni yüklenen önizlemeyi temizle
        if (currentPreviewImg) {
            coverUploadArea.removeChild(currentPreviewImg);
            currentPreviewImg = null;
        }
        if (removeBtn) {
            coverUploadArea.removeChild(removeBtn);
            removeBtn = null;
        }
        coverInput.value = '';
        var copyKapReset = document.getElementById('copyKapakFromId');
        if (copyKapReset) copyKapReset.value = '';
        var isbnReset = document.getElementById('isbnCoverUrl');
        if (isbnReset) isbnReset.value = '';

        // Placeholder görünürlüğü
        var hasExisting = document.getElementById('existingCoverImg') !== null;
        if (hasExisting) {
            coverPlaceholder.style.display = 'none';
            coverUploadArea.classList.add('has-image');
            var hintReset = document.getElementById('coverChangeHint');
            if (hintReset) hintReset.style.display = 'block';
        }

        // Combobox dropdownları kapat
        document.querySelectorAll('.combobox-dropdown').forEach(function(d) {
            d.classList.remove('visible');
        });
        document.querySelectorAll('.combobox-toggle').forEach(function(t) {
            t.classList.remove('open');
        });

        autoGrowEls.forEach(autoGrowTextarea);

        showToast('success', 'Sıfırlandı', 'Alanlar orijinal değerlerine döndürüldü.');
    }

    // ============================
    // Combobox System
    // ============================
    // ============================
    // Yazar & Yayınevi Combobox — DB'den veri
    // ============================
    (function() {
        function initDbCombobox(cfg) {
            // strict: true → yalnızca listeden seçim, serbest metin kabul edilmez
            // idKey: belirtilirse hidden input'a label yerine bu alanın değeri (id) yazılır
            var strict      = !!cfg.strict;
            var idKey       = cfg.idKey || null;  // örn: 'id'
            var wrapper     = document.getElementById(cfg.wrapperId);
            var searchInput = document.getElementById(cfg.searchInputId);
            var hiddenInput = document.getElementById(cfg.hiddenInputId);
            if (!wrapper || !searchInput || !hiddenInput) return;
            var dropdown    = wrapper.querySelector('.combobox-dropdown');
            var toggle      = wrapper.querySelector('.combobox-toggle');
            var inputWrap   = wrapper.querySelector('.combobox-input-wrap');
            var clearBtn    = inputWrap ? inputWrap.querySelector('.combobox-clear') : null;
            if (!clearBtn && inputWrap) {
                clearBtn = document.createElement('button');
                clearBtn.type = 'button';
                clearBtn.className = 'combobox-clear';
                clearBtn.setAttribute('aria-label', 'Seçimi temizle');
                clearBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>';
                inputWrap.appendChild(clearBtn);
            }
            var rawData = [];
            try { rawData = JSON.parse(document.getElementById(cfg.dataScriptId).textContent || '[]'); } catch(e) {}
            var highlightedIndex = -1;
            var filtered = rawData.slice();

            function esc(s) { var d = document.createElement('div'); d.appendChild(document.createTextNode(s||'')); return d.innerHTML; }
            function highlight(text, term) {
                if (!term) return esc(text);
                var idx = text.toLowerCase().indexOf(term.toLowerCase());
                if (idx === -1) return esc(text);
                return esc(text.substring(0, idx)) + '<strong style="color:var(--primary)">' + esc(text.substring(idx, idx + term.length)) + '</strong>' + esc(text.substring(idx + term.length));
            }
            function render(filter) {
                var term = (filter || '').toLowerCase();
                filtered = rawData.filter(function(r) { return r[cfg.labelKey].toLowerCase().indexOf(term) !== -1; });
                var html = '';
                if (filtered.length === 0) {
                    html = '<div class="combobox-no-result">Bulunamadı</div>';
                    if (term && !strict) html += '<div class="combobox-hint">Enter ile "' + esc(searchInput.value.trim()) + '" yeni olarak eklenecek</div>';
                } else {
                    filtered.forEach(function(r, i) {
                        // Seçili: hiddenInput'un değeri bu kayıtla eşleşiyorsa
                        var curVal = hiddenInput.value;
                        var sel  = curVal !== '' && (
                            idKey
                                ? String(r[idKey]) === String(curVal)
                                : r[cfg.labelKey].toLowerCase() === curVal.toLowerCase()
                        );
                        html += '<div class="combobox-option' + (sel?' selected':'') + (i===highlightedIndex?' highlighted':'') + '" data-val="' + esc(r[cfg.labelKey]) + '" data-id="' + esc(String(r[idKey] !== undefined ? r[idKey] : r[cfg.labelKey])) + '">';
                        html += '<svg class="check-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
                        html += '<span>' + highlight(r[cfg.labelKey], filter) + '</span></div>';
                    });
                }
                dropdown.innerHTML = html;
                dropdown.querySelectorAll('.combobox-option').forEach(function(el) {
                    el.addEventListener('mousedown', function(e) {
                        e.preventDefault();
                        selectOption(this.getAttribute('data-val'), this.getAttribute('data-id'));
                    });
                });
            }
            function selectOption(label, id) {
                searchInput.value  = label;
                hiddenInput.value  = (idKey && id !== null && id !== undefined) ? id : label;
                updateClearButton();
                close();
                searchInput.focus();
            }
            function validateOnBlur() {
                if (!strict) return;
                var typed = searchInput.value.trim().toLowerCase();
                if (!typed) { hiddenInput.value = ''; return; }
                var match = rawData.find(function(r) { return r[cfg.labelKey].toLowerCase() === typed; });
                if (!match) { searchInput.value = ''; hiddenInput.value = ''; }
                else if (idKey) { hiddenInput.value = match[idKey]; }
            }
            function syncSearchFromHidden() {
                var curVal = hiddenInput.value;
                if (!curVal) {
                    searchInput.value = '';
                    updateClearButton();
                    return;
                }
                var found = rawData.find(function(r) {
                    return idKey ? String(r[idKey]) === String(curVal) : r[cfg.labelKey].toLowerCase() === String(curVal).toLowerCase();
                });
                searchInput.value = found ? found[cfg.labelKey] : (strict ? '' : curVal);
                updateClearButton();
            }

            function updateClearButton() {
                if (!clearBtn) return;
                var hasValue = String(hiddenInput.value || '').trim() !== '' || String(searchInput.value || '').trim() !== '';
                clearBtn.style.display = hasValue ? 'flex' : 'none';
            }
            function open() {
                highlightedIndex = -1;
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
                // Kapatırken seçili değerin label'ını geri yaz
                var curVal = hiddenInput.value;
                if (curVal) {
                    var found = rawData.find(function(r) {
                        return idKey ? String(r[idKey]) === String(curVal) : r[cfg.labelKey].toLowerCase() === curVal.toLowerCase();
                    });
                    searchInput.value = found ? found[cfg.labelKey] : (strict ? '' : curVal);
                } else {
                    searchInput.value = '';
                }
            }
            function isOpen() { return dropdown.classList.contains('visible'); }
            toggle.addEventListener('mousedown', function(e) { e.preventDefault(); isOpen() ? close() : open(); });
            searchInput.addEventListener('focus', function() { if (!isOpen()) open(); });
            searchInput.addEventListener('input',  function() {
                if (!strict) hiddenInput.value = this.value.trim();
                highlightedIndex = -1; render(this.value); updateClearButton(); if (!isOpen()) open();
            });
            hiddenInput.addEventListener('change', syncSearchFromHidden);
            hiddenInput.addEventListener('input', syncSearchFromHidden);
            searchInput.addEventListener('blur', function() { setTimeout(function() { validateOnBlur(); close(); }, 150); });
            if (clearBtn) {
                clearBtn.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    hiddenInput.value = '';
                    searchInput.value = '';
                    highlightedIndex = -1;
                    updateClearButton();
                    close();
                    searchInput.focus();
                });
            }
            searchInput.addEventListener('keydown', function(e) {
                if (!isOpen() && (e.key==='ArrowDown'||e.key==='ArrowUp')) { e.preventDefault(); open(); return; }
                if (!isOpen()) return;
                if (e.key==='ArrowDown') { e.preventDefault(); highlightedIndex=Math.min(highlightedIndex+1,filtered.length-1); render(searchInput.value); var h=dropdown.querySelector('.highlighted'); if(h)h.scrollIntoView({block:'nearest'}); }
                else if (e.key==='ArrowUp') { e.preventDefault(); highlightedIndex=Math.max(highlightedIndex-1,0); render(searchInput.value); var h2=dropdown.querySelector('.highlighted'); if(h2)h2.scrollIntoView({block:'nearest'}); }
                else if (e.key==='Enter') {
                    e.preventDefault();
                    if (highlightedIndex >= 0 && filtered[highlightedIndex]) {
                        var r = filtered[highlightedIndex];
                        selectOption(r[cfg.labelKey], idKey ? r[idKey] : null);
                    } else if (!strict && searchInput.value.trim()) {
                        hiddenInput.value = searchInput.value.trim(); close();
                    } else if (strict) {
                        searchInput.value = ''; hiddenInput.value = ''; close();
                    }
                }
                else if (e.key==='Escape') { close(); }
            });
            document.addEventListener('click', function(e) { if (!wrapper.contains(e.target)) close(); });
            syncSearchFromHidden();
            updateClearButton();
        }
        function initYazarMultiAuthorBlock() {
            var wrapper     = document.getElementById('authorCombobox');
            var searchInput = document.getElementById('kunyeYazarSearch');
            var chipsKay    = document.getElementById('yazarKayitliChips');
            var hiddensKay  = document.getElementById('yazarKayitliHiddenIds');
            var hiddenKunye = document.getElementById('kunyeYazar');
            var fieldKay    = document.getElementById('yazarKayitliFieldset');
            var fieldMan    = document.getElementById('yazarManuelFieldset');
            var pickArea    = document.getElementById('yazarKayitliPickArea');
            var radKay      = document.getElementById('yazarGirisKayitli');
            var radMan      = document.getElementById('yazarGirisManuel');
            var chipsMan    = document.getElementById('yazarManuelChips');
            var hiddensMan  = document.getElementById('yazarManuelHiddenPairs');
            var adEntry     = document.getElementById('yazarManuelAdEntry');
            var soyEntry    = document.getElementById('yazarManuelSoyadEntry');
            var btnEkle     = document.getElementById('yazarManuelEkleBtn');
            if (!wrapper || !searchInput || !chipsKay || !hiddensKay || !hiddenKunye) return;

            var rawData = [];
            try { rawData = JSON.parse(document.getElementById('yazarData').textContent || '[]'); } catch (e) {}

            var selectedKay = [];

            function esc(s) {
                var d = document.createElement('div');
                d.appendChild(document.createTextNode(s || ''));
                return d.innerHTML;
            }

            function syncKunyeHiddenFromKayitli() {
                hiddenKunye.value = selectedKay.map(function (x) { return x.label; }).join(' ; ');
            }

            function renderKayitliChips() {
                chipsKay.innerHTML = '';
                hiddensKay.innerHTML = '';
                selectedKay.forEach(function (item) {
                    var chip = document.createElement('span');
                    chip.style.cssText = 'display:inline-flex;align-items:center;gap:4px;padding:4px 8px;border-radius:6px;background:var(--secondary);font-size:13px;border:1px solid var(--border);';
                    chip.innerHTML = esc(item.label) + ' <button type="button" class="yazar-chip-x" data-id="' + String(item.id) + '" style="border:none;background:transparent;cursor:pointer;padding:0 2px;line-height:1;font-size:16px;" aria-label="Kaldır">&times;</button>';
                    chip.querySelector('.yazar-chip-x').addEventListener('click', function () {
                        var rid = parseInt(this.getAttribute('data-id'), 10);
                        selectedKay = selectedKay.filter(function (x) { return x.id !== rid; });
                        renderKayitliChips();
                    });
                    chipsKay.appendChild(chip);
                    var inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = 'yazar_ids[]';
                    inp.value = String(item.id);
                    hiddensKay.appendChild(inp);
                });
                syncKunyeHiddenFromKayitli();
            }

            function addKayitliId(idStr, label) {
                var id = parseInt(idStr, 10);
                if (!id || isNaN(id)) return;
                if (selectedKay.some(function (x) { return x.id === id; })) return;
                selectedKay.push({ id: id, label: label });
                renderKayitliChips();
                searchInput.value = '';
            }

            window.setYazarKayitliChipsFromIds = function (ids) {
                if (!Array.isArray(ids)) return;
                if (radKay) radKay.checked = true;
                toggleMode();
                selectedKay = [];
                ids.forEach(function (rid) {
                    var id = parseInt(rid, 10);
                    if (!id) return;
                    var row = rawData.find(function (r) { return parseInt(r.id, 10) === id; });
                    if (row) selectedKay.push({ id: id, label: row.ad });
                });
                renderKayitliChips();
                searchInput.value = '';
            };

            window.clearYazarKayitliChips = function () {
                selectedKay = [];
                renderKayitliChips();
            };

            window.syncKunyeYazarHiddenForSubmit = function () {
                if (radMan && radMan.checked) return;
                syncKunyeHiddenFromKayitli();
            };

            function parseTamMetinJs(full) {
                full = String(full || '').replace(/\s+/g, ' ').trim();
                if (!full) return { ad: '', soyad: '' };
                var m = full.match(/^([^,]+),\s*(.+)$/);
                if (m) {
                    var soyad = m[1].trim();
                    var ad = m[2].trim();
                    if (ad === '' && soyad !== '') {
                        ad = soyad;
                        soyad = '';
                    }
                    return { ad: ad, soyad: soyad };
                }
                var parts = full.split(/\s+/).filter(Boolean);
                if (parts.length >= 2) {
                    var soy = parts.pop();
                    return { ad: parts.join(' '), soyad: soy };
                }
                return { ad: full, soyad: '' };
            }

            window.applyIsbnApiAuthorsString = function (authorsStr) {
                var s = String(authorsStr || '').trim();
                if (!s) return;
                var pieces = s.split(/\s*,\s*|\s*&\s*|\s+ve\s+|\s+and\s+/i).map(function (p) { return p.trim(); }).filter(Boolean);
                if (pieces.length === 0) pieces = [s];

                function findRowExact(token) {
                    var t = String(token || '').replace(/\s+/g, ' ').trim().toLowerCase();
                    if (!t) return null;
                    return rawData.find(function (r) {
                        var a = String(r.ad || '').replace(/\s+/g, ' ').trim().toLowerCase();
                        return a === t;
                    }) || null;
                }

                var allMatched = true;
                var matchedIds = [];
                for (var i = 0; i < pieces.length; i++) {
                    var row = findRowExact(pieces[i]);
                    if (!row) {
                        allMatched = false;
                        break;
                    }
                    matchedIds.push(parseInt(row.id, 10));
                }

                if (allMatched && matchedIds.length > 0) {
                    window.setYazarKayitliChipsFromIds(matchedIds);
                    return;
                }

                if (radMan) radMan.checked = true;
                if (radKay) radKay.checked = false;
                toggleMode();
                var first = pieces[0] || s;
                var parsed = parseTamMetinJs(first);
                if (adEntry) adEntry.value = parsed.ad;
                if (soyEntry) soyEntry.value = parsed.soyad;
                if (searchInput) searchInput.value = '';
                var kh = document.getElementById('kunyeYazar');
                if (kh) kh.value = s;
            };

            var manuelRows = [];

            function renderManuel() {
                chipsMan.innerHTML = '';
                hiddensMan.innerHTML = '';
                manuelRows.forEach(function (row) {
                    var lab = (row.ad + ' ' + row.soyad).trim();
                    var chip = document.createElement('span');
                    chip.style.cssText = 'display:inline-flex;align-items:center;gap:4px;padding:4px 8px;border-radius:6px;background:var(--secondary);font-size:13px;border:1px solid var(--border);';
                    chip.innerHTML = esc(lab) + ' <button type="button" class="y-man-chip-x" style="border:none;background:transparent;cursor:pointer;padding:0 2px;line-height:1;font-size:16px;" aria-label="Kaldır">&times;</button>';
                    chip.querySelector('.y-man-chip-x').addEventListener('click', function () {
                        manuelRows = manuelRows.filter(function (x) { return x !== row; });
                        renderManuel();
                    });
                    chipsMan.appendChild(chip);
                    var i1 = document.createElement('input');
                    i1.type = 'hidden';
                    i1.name = 'yazar_manuel_ad[]';
                    i1.value = row.ad;
                    var i2 = document.createElement('input');
                    i2.type = 'hidden';
                    i2.name = 'yazar_manuel_soyad[]';
                    i2.value = row.soyad;
                    hiddensMan.appendChild(i1);
                    hiddensMan.appendChild(i2);
                });
            }

            function toggleMode() {
                var man = radMan && radMan.checked;
                if (fieldKay) fieldKay.disabled = !!man;
                if (fieldMan) fieldMan.disabled = !man;
                if (pickArea) pickArea.style.display = man ? 'none' : '';
                if (fieldMan) fieldMan.style.display = man ? '' : 'none';
                if (man) {
                    selectedKay = [];
                    renderKayitliChips();
                    searchInput.value = '';
                } else {
                    manuelRows = [];
                    renderManuel();
                    if (adEntry) adEntry.value = '';
                    if (soyEntry) soyEntry.value = '';
                }
            }

            if (radKay) radKay.addEventListener('change', toggleMode);
            if (radMan) radMan.addEventListener('change', toggleMode);

            if (btnEkle) {
                btnEkle.addEventListener('click', function () {
                    var a = (adEntry && adEntry.value) ? adEntry.value.trim() : '';
                    var s = (soyEntry && soyEntry.value) ? soyEntry.value.trim() : '';
                    if (!a) {
                        if (typeof showToast === 'function') showToast('error', 'Uyarı', 'Yazar adı zorunludur.');
                        return;
                    }
                    manuelRows.push({ ad: a, soyad: s });
                    renderManuel();
                    adEntry.value = '';
                    soyEntry.value = '';
                });
            }

            window.resetYazarFormToKayitli = function () {
                if (radKay) radKay.checked = true;
                if (radMan) radMan.checked = false;
                toggleMode();
                selectedKay = [];
                renderKayitliChips();
                if (searchInput) searchInput.value = '';
                if (adEntry) adEntry.value = '';
                if (soyEntry) soyEntry.value = '';
                manuelRows = [];
                renderManuel();
            };

            var dropdown = wrapper.querySelector('.combobox-dropdown');
            var toggle = wrapper.querySelector('.combobox-toggle');
            var highlightedIndex = -1;
            var filtered = rawData.slice();

            function highlight(text, term) {
                if (!term) return esc(text);
                var idx = text.toLowerCase().indexOf(term.toLowerCase());
                if (idx === -1) return esc(text);
                return esc(text.substring(0, idx)) + '<strong style="color:var(--primary)">' + esc(text.substring(idx, idx + term.length)) + '</strong>' + esc(text.substring(idx + term.length));
            }

            function render(filter) {
                var term = (filter || '').toLowerCase();
                filtered = rawData.filter(function (r) { return r.ad.toLowerCase().indexOf(term) !== -1; });
                var html = '';
                if (filtered.length === 0) {
                    html = '<div class="combobox-no-result">Bulunamadı</div>';
                } else {
                    filtered.forEach(function (r, i) {
                        var high = (i === highlightedIndex);
                        html += '<div class="combobox-option' + (high ? ' highlighted' : '') + '" data-val="' + esc(r.ad) + '" data-id="' + esc(String(r.id)) + '">';
                        html += '<svg class="check-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
                        html += '<span>' + highlight(r.ad, filter) + '</span></div>';
                    });
                }
                dropdown.innerHTML = html;
                dropdown.querySelectorAll('.combobox-option').forEach(function (el) {
                    el.addEventListener('mousedown', function (e) {
                        e.preventDefault();
                        addKayitliId(this.getAttribute('data-id'), this.getAttribute('data-val'));
                        close();
                    });
                });
            }

            function open() {
                highlightedIndex = -1;
                searchInput.value = '';
                render('');
                dropdown.classList.add('visible');
                toggle.classList.add('open');
                searchInput.focus();
            }
            function close() {
                dropdown.classList.remove('visible');
                if (toggle) toggle.classList.remove('open');
                highlightedIndex = -1;
                searchInput.value = '';
            }
            function isOpen() { return dropdown.classList.contains('visible'); }

            if (toggle) toggle.addEventListener('mousedown', function (e) { e.preventDefault(); isOpen() ? close() : open(); });
            searchInput.addEventListener('focus', function () { if (!isOpen()) open(); });
            searchInput.addEventListener('input', function () {
                highlightedIndex = -1;
                render(this.value);
                if (!isOpen()) open();
            });
            searchInput.addEventListener('blur', function () {
                setTimeout(function () { searchInput.value = ''; close(); }, 150);
            });
            searchInput.addEventListener('keydown', function (e) {
                if (!isOpen() && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
                    e.preventDefault();
                    open();
                    return;
                }
                if (!isOpen()) return;
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    highlightedIndex = Math.min(highlightedIndex + 1, filtered.length - 1);
                    render(searchInput.value);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    highlightedIndex = Math.max(highlightedIndex - 1, 0);
                    render(searchInput.value);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (highlightedIndex >= 0 && filtered[highlightedIndex]) {
                        var r = filtered[highlightedIndex];
                        addKayitliId(String(r.id), r.ad);
                        close();
                    }
                } else if (e.key === 'Escape') {
                    close();
                }
            });
            document.addEventListener('click', function (e) { if (!wrapper.contains(e.target)) close(); });

            toggleMode();
            renderKayitliChips();
            if (typeof window.__initialYazarIds !== 'undefined' && window.__initialYazarIds.length && window.setYazarKayitliChipsFromIds) {
                window.setYazarKayitliChipsFromIds(window.__initialYazarIds);
            }
        }

        initYazarMultiAuthorBlock();
        initDbCombobox({ wrapperId:'publisherCombobox', searchInputId:'kunyeYayinlayanSearch', hiddenInputId:'kunyeYayinlayan', dataScriptId:'yayineviData', labelKey:'ad' });
        initDbCombobox({ wrapperId:'turCombobox',    searchInputId:'turIdSearch',    hiddenInputId:'turId',    dataScriptId:'turData',    labelKey:'ad', idKey:'id', strict: true });
        initDbCombobox({ wrapperId:'altTurCombobox', searchInputId:'altTurIdSearch', hiddenInputId:'altTurAd', dataScriptId:'altTurData', labelKey:'ad' });
        initDbCombobox({ wrapperId:'sekilCombobox',  searchInputId:'sekilIdSearch',  hiddenInputId:'sekilId',  dataScriptId:'sekilData',  labelKey:'ad', idKey:'id', strict: true });
        initDbCombobox({ wrapperId:'ortamCombobox',  searchInputId:'ortamIdSearch',  hiddenInputId:'ortamId',  dataScriptId:'ortamData',  labelKey:'ad', idKey:'id', strict: true });
    })();

    // ============================
    // Kategori Combobox (DB'den, id kaydeder, manuel giriş yok)
    // ============================
    (function () {
        var wrapper     = document.getElementById('categoryCombobox');
        if (!wrapper) return;

        var searchInput = document.getElementById('kunyeKategoriSearch');
        var hiddenInput = document.getElementById('kunyeKategori');
        var toggle      = wrapper.querySelector('.combobox-toggle');
        var dropdown    = wrapper.querySelector('.combobox-dropdown');
        var inputWrap   = wrapper.querySelector('.combobox-input-wrap');
        var clearBtn    = inputWrap ? inputWrap.querySelector('.combobox-clear') : null;
        if (!clearBtn && inputWrap) {
            clearBtn = document.createElement('button');
            clearBtn.type = 'button';
            clearBtn.className = 'combobox-clear';
            clearBtn.setAttribute('aria-label', 'Seçimi temizle');
            clearBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>';
            inputWrap.appendChild(clearBtn);
        }

        var rawData = [];
        try {
            rawData = JSON.parse(document.getElementById('kategoriData').textContent || '[]');
        } catch(e) {}

        var filtered        = rawData.slice();
        var highlightedIdx  = -1;
        var selectedId      = hiddenInput.value || '';
        var selectedTitle   = '';

        function syncSelectedFromHidden() {
            selectedId = hiddenInput.value || '';
            if (!selectedId) {
                selectedTitle = '';
                searchInput.value = '';
                updateClearButton();
                return;
            }
            var found = rawData.find(function(k) { return String(k.id) === String(selectedId); });
            selectedTitle = found ? found.title : '';
            searchInput.value = selectedTitle;
            updateClearButton();
        }

        function updateClearButton() {
            if (!clearBtn) return;
            clearBtn.style.display = String(selectedId || '').trim() !== '' ? 'flex' : 'none';
        }

        function escHtml(s) {
            return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        function hlMatch(text, term) {
            if (!term) return escHtml(text);
            var idx = text.toLowerCase().indexOf(term.toLowerCase());
            if (idx === -1) return escHtml(text);
            return escHtml(text.substring(0, idx))
                + '<strong style="color:var(--primary)">' + escHtml(text.substring(idx, idx + term.length)) + '</strong>'
                + escHtml(text.substring(idx + term.length));
        }

        function render(filter) {
            var term = (filter || '').toLowerCase();
            filtered = rawData.filter(function(k) {
                return k.title.toLowerCase().indexOf(term) !== -1;
            });

            var html = '';
            if (filtered.length === 0) {
                html = '<div class="combobox-no-result">Sonuç bulunamadı</div>';
            } else {
                filtered.forEach(function(k, i) {
                    var isSel  = String(k.id) === String(selectedId);
                    var isHigh = i === highlightedIdx;
                    html += '<div class="combobox-option'
                        + (isSel  ? ' selected'     : '')
                        + (isHigh ? ' highlighted'  : '')
                        + '" data-id="' + escHtml(String(k.id)) + '" data-title="' + escHtml(k.title) + '">';
                    html += '<svg class="check-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
                    html += '<span>' + hlMatch(k.title, filter || '') + '</span>';
                    html += '</div>';
                });
            }
            dropdown.innerHTML = html;

            dropdown.querySelectorAll('.combobox-option').forEach(function(el) {
                el.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    pick(this.getAttribute('data-id'), this.getAttribute('data-title'));
                });
            });
        }

        function pick(id, title) {
            selectedId    = id;
            selectedTitle = title;
            hiddenInput.value  = id;
            searchInput.value  = title;
            updateClearButton();
            close();
            searchInput.focus();
        }

        function open() {
            highlightedIdx = -1;
            searchInput.value = ''; // Her açılışta temizle
            render('');
            dropdown.classList.add('visible');
            toggle.classList.add('open');
            searchInput.focus();
        }

        function close() {
            dropdown.classList.remove('visible');
            toggle.classList.remove('open');
            highlightedIdx = -1;
            // Manuel girişi engelle: blur'da listedeki değere geri dön
            searchInput.value = selectedTitle;
        }

        function isOpen() { return dropdown.classList.contains('visible'); }

        function scrollHigh() {
            var el = dropdown.querySelector('.combobox-option.highlighted');
            if (el) el.scrollIntoView({ block: 'nearest' });
        }

        toggle.addEventListener('mousedown', function(e) {
            e.preventDefault();
            isOpen() ? close() : open();
        });

        searchInput.addEventListener('focus', function() { if (!isOpen()) open(); });

        searchInput.addEventListener('input', function() {
            highlightedIdx = -1;
            render(this.value);
            if (!isOpen()) open();
        });

        searchInput.addEventListener('blur', function() {
            setTimeout(close, 150);
        });
        if (clearBtn) {
            clearBtn.addEventListener('mousedown', function(e) {
                e.preventDefault();
                e.stopPropagation();
                selectedId = '';
                selectedTitle = '';
                hiddenInput.value = '';
                searchInput.value = '';
                highlightedIdx = -1;
                updateClearButton();
                close();
                searchInput.focus();
            });
        }

        hiddenInput.addEventListener('change', syncSelectedFromHidden);
        hiddenInput.addEventListener('input', syncSelectedFromHidden);

        searchInput.addEventListener('keydown', function(e) {
            if (!isOpen() && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
                e.preventDefault(); open(); return;
            }
            if (!isOpen()) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                highlightedIdx = highlightedIdx < filtered.length - 1 ? highlightedIdx + 1 : 0;
                render(searchInput.value); scrollHigh();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                highlightedIdx = highlightedIdx > 0 ? highlightedIdx - 1 : filtered.length - 1;
                render(searchInput.value); scrollHigh();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (highlightedIdx >= 0 && highlightedIdx < filtered.length) {
                    pick(String(filtered[highlightedIdx].id), filtered[highlightedIdx].title);
                }
            } else if (e.key === 'Escape') {
                close();
            }
        });

        document.addEventListener('click', function(e) {
            if (!wrapper.contains(e.target)) close();
        });

        syncSelectedFromHidden();
        updateClearButton();
    })();

    // ============================
    // ISBN Search
    // ============================
    (function () {
        var btn       = document.getElementById('isbnSearchBtn');
        if (!btn) return;
        var spinSvg   = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin 0.8s linear infinite;display:block"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';
        var searchSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';
        var excluded  = {
            girisTuruId: true,
            kunyeDurum: true,
            kutuphaneId: true,
            oduncVerilemez: true,
            etiketlendi: true,
            yazar_ids: true
        };

        function applyDatabasePrefill(prefill) {
            if (!prefill || typeof prefill !== 'object') return;

            Object.keys(prefill).forEach(function (key) {
                if (excluded[key]) return;

                var value = prefill[key];
                if (value === null || typeof value === 'undefined') return;

                var el = document.getElementById(key);
                if (!el) return;

                if (el.type === 'checkbox') {
                    el.checked = value === 1 || value === '1' || value === true;
                } else {
                    el.value = value;
                }

                el.dispatchEvent(new Event('input', { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
            });

            if (Array.isArray(prefill.yazar_ids) && window.setYazarKayitliChipsFromIds) {
                window.setYazarKayitliChipsFromIds(prefill.yazar_ids);
            }
            if ((!Array.isArray(prefill.yazar_ids) || prefill.yazar_ids.length === 0) && prefill.kunyeYazar) {
                var yazarSearch = document.getElementById('kunyeYazarSearch');
                if (yazarSearch) yazarSearch.value = prefill.kunyeYazar;
            }
            if (prefill.kunyeYayinlayan) {
                var yayineviSearch = document.getElementById('kunyeYayinlayanSearch');
                if (yayineviSearch) yayineviSearch.value = prefill.kunyeYayinlayan;
            }
        }

        btn.addEventListener('click', function () {
            var isbn = document.getElementById('kunyeISBNISSN').value.trim();
            if (!isbn) {
                showToast('error', 'Uyarı', 'Lütfen önce bir ISBN numarası girin.');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = spinSvg;

            fetch('{{ route("katalog.isbnSearch") }}?isbn=' + encodeURIComponent(isbn), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success) {
                        if (window.resetYazarFormToKayitli) window.resetYazarFormToKayitli();
                        if (data.source === 'database' && data.prefill) {
                            applyDatabasePrefill(data.prefill);
                        }
                        if (data.title)     document.getElementById('kunyeEserAdi').value   = data.title;
                        if (data.authors) {
                            if (data.source === 'api' && window.applyIsbnApiAuthorsString) {
                                window.applyIsbnApiAuthorsString(data.authors);
                            }
                        } else {
                            var ys = document.getElementById('kunyeYazarSearch');
                            var kh = document.getElementById('kunyeYazar');
                            if (ys) ys.value = '';
                            if (kh) kh.value = '';
                            if (window.syncKunyeYazarHiddenForSubmit) window.syncKunyeYazarHiddenForSubmit();
                        }
                        if (data.publisher) {
                            document.getElementById('kunyeYayinlayanSearch').value = data.publisher;
                            document.getElementById('kunyeYayinlayan').value       = data.publisher;
                        }

                        if (data.cover) {
                            var existingImg    = document.getElementById('existingCoverImg');
                            var existingRmBtn  = document.getElementById('coverRemoveBtn');
                            if (existingImg)   existingImg.style.display   = 'none';
                            if (existingRmBtn) existingRmBtn.style.display = 'none';
                            document.getElementById('kapakSilInput').value = '0';

                            coverInput.value = '';

                            var copyKapakHidden = document.getElementById('copyKapakFromId');
                            var hidden = document.getElementById('isbnCoverUrl');
                            var previewSrc = data.coverPreviewUrl || data.cover;
                            if (data.source === 'database' && data.sourceKatalogId) {
                                if (hidden) hidden.value = '';
                                if (copyKapakHidden) copyKapakHidden.value = String(data.sourceKatalogId);
                                if (data.coverPreviewUrl) previewSrc = data.coverPreviewUrl;
                            } else {
                                if (copyKapakHidden) copyKapakHidden.value = '';
                                if (!hidden) {
                                    hidden      = document.createElement('input');
                                    hidden.type = 'hidden';
                                    hidden.id   = 'isbnCoverUrl';
                                    hidden.name = 'isbn_cover_url';
                                    document.getElementById('bookForm').appendChild(hidden);
                                }
                                hidden.value = data.cover;
                            }
                            showCoverPreview(previewSrc);
                        }

                        showToast('success', 'Başarılı', (data.source === 'database')
                            ? 'Bu ISBN mevcut katalog kaydından getirildi.'
                            : 'Kitap bilgileri ISBN\'den getirildi.');
                    } else {
                        showToast('error', 'Bulunamadı', data.message || 'Bu ISBN için kayıt bulunamadı.');
                    }
                })
                .catch(function () {
                    showToast('error', 'Hata', 'ISBN sorgusu sırasında bir hata oluştu.');
                })
                .finally(function () {
                    btn.disabled   = false;
                    btn.innerHTML  = searchSvg;
                });
        });
    })();

    // ============================
    // Giriş Türü → Dinamik Alan Gösterimi
    // ============================
    (function () {
        var girisTuruSel    = document.getElementById('girisTuruId');
        var satinAlmaFields = document.getElementById('satin-alma-fields');
        var hibeFields      = document.getElementById('hibe-fields');
        var bagisFields     = document.getElementById('bagis-fields');

        // Gizli gerçek input'lar (form submit eder)
        var hiddenTedarikci = document.getElementById('tedarikci_hidden');
        var hiddenTelefon   = document.getElementById('tedarikciTelefon_hidden');
        var hiddenEposta    = document.getElementById('tedarikciEposta_hidden');

        function getSelectedAd() {
            var opt = girisTuruSel.options[girisTuruSel.selectedIndex];
            return opt ? (opt.dataset.ad || '').toLowerCase() : '';
        }

        function syncHidden() {
            var tip = getSelectedAd();
            var tedarikci = '', tel = '', eposta = '';

            if (tip === 'satın alma') {
                tedarikci = document.getElementById('tedarikci').value;
                tel       = document.getElementById('tedarikciTelefon').value;
                eposta    = document.getElementById('tedarikciEposta').value;
            } else if (tip === 'hibe') {
                tedarikci = document.getElementById('tedarikci_hibe').value;
                tel       = document.getElementById('tedarikciTelefon_hibe').value;
                eposta    = document.getElementById('tedarikciEposta_hibe').value;
            } else if (tip === 'bağış') {
                tedarikci = document.getElementById('tedarikci_bagis').value;
                tel       = document.getElementById('tedarikciTelefon_bagis').value;
                eposta    = document.getElementById('tedarikciEposta_bagis').value;
            }

            hiddenTedarikci.value = tedarikci;
            hiddenTelefon.value   = tel;
            hiddenEposta.value    = eposta;
        }

        function applyGirisTuru() {
            satinAlmaFields.style.display = 'none';
            hibeFields.style.display      = 'none';
            bagisFields.style.display     = 'none';

            var tip = getSelectedAd();

            if (tip === 'satın alma') {
                satinAlmaFields.style.display = '';
            } else if (tip === 'hibe') {
                hibeFields.style.display = '';
            } else if (tip === 'bağış') {
                bagisFields.style.display = '';
            }
            // protokol ve diğer: hiçbir ek alan açılmaz

            syncHidden();
        }

        // Görünen inputlardaki değişiklikleri hidden'a yansıt
        ['tedarikci', 'tedarikciTelefon', 'tedarikciEposta',
            'tedarikci_hibe', 'tedarikciTelefon_hibe', 'tedarikciEposta_hibe',
            'tedarikci_bagis', 'tedarikciTelefon_bagis', 'tedarikciEposta_bagis'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('input', syncHidden);
        });

        girisTuruSel.addEventListener('change', applyGirisTuru);

        // Sayfa yüklenince mevcut kitabın verilerine göre uygula
        applyGirisTuru();
    })();

    // ============================
    // Üst Eser Autocomplete
    // ============================
    var selectedUstEser = null;
    var ueTimer = null;
    var ueBookIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>';

    (function() {
        var inp = document.getElementById('ustEserSearch');
        var dd  = document.getElementById('ustEserDropdown');
        var hi  = -1;

        inp.addEventListener('input', function() {
            var q = this.value.trim();
            clearTimeout(ueTimer);
            if (q.length < 2) { dd.classList.remove('open'); dd.innerHTML = ''; return; }
            ueTimer = setTimeout(function() {
                dd.innerHTML = '<div class="ue-ac-loading">Aranıyor…</div>';
                dd.classList.add('open');
                fetch('{{ route('odunc.kitapAra') }}?q=' + encodeURIComponent(q), {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                })
                    .then(function(r) { return r.json(); })
                    .then(function(items) {
                        dd.innerHTML = '';
                        hi = -1;
                        if (!items.length) { dd.innerHTML = '<div class="ue-ac-empty">Sonuç bulunamadı</div>'; return; }
                        items.forEach(function(item) {
                            var el = document.createElement('div');
                            el.className = 'ue-ac-item';
                            var coverHtml = item.kapak
                                ? '<img src="' + item.kapak + '" class="ue-ac-cover" />'
                                : '<div class="ue-ac-cover-ph">' + ueBookIcon + '</div>';
                            el.innerHTML = coverHtml
                                + '<div class="ue-ac-body">'
                                +   '<div class="ue-ac-name">' + (item.label || '') + '</div>'
                                +   '<div class="ue-ac-meta">' + (item.yazar || '') + (item.demir ? ' · Demirbaş: ' + item.demir : '') + (item.isbn ? ' · ISBN: ' + item.isbn : '') + '</div>'
                                + '</div>';
                            el.addEventListener('mousedown', function(e) {
                                e.preventDefault();
                                selectUstEser(item);
                                dd.classList.remove('open');
                                inp.value = '';
                            });
                            dd.appendChild(el);
                        });
                    });
            }, 280);
        });

        inp.addEventListener('keydown', function(e) {
            var items = dd.querySelectorAll('.ue-ac-item');
            if (e.key === 'ArrowDown')  { e.preventDefault(); if (hi < items.length - 1) { hi++; items.forEach(function(el,i){ el.classList.toggle('highlighted', i===hi); }); } }
            else if (e.key === 'ArrowUp')   { e.preventDefault(); if (hi > 0) { hi--; items.forEach(function(el,i){ el.classList.toggle('highlighted', i===hi); }); } }
            else if (e.key === 'Enter' && hi >= 0) { e.preventDefault(); items[hi].dispatchEvent(new MouseEvent('mousedown')); }
            else if (e.key === 'Escape') { dd.classList.remove('open'); }
        });

        document.addEventListener('click', function(e) {
            if (!inp.contains(e.target) && !dd.contains(e.target)) dd.classList.remove('open');
        });
    })();

    function selectUstEser(item) {
        selectedUstEser = item;
        document.getElementById('ustEserKatalogId').value = item.id;

        var wrap = document.getElementById('ustEserCoverWrap');
        if (item.kapak) {
            wrap.innerHTML = '<img src="' + item.kapak + '" class="ue-selected-cover" />';
        } else {
            wrap.innerHTML = '';
        }

        document.getElementById('ustEserName').textContent = item.label;
        document.getElementById('ustEserMeta').textContent = (item.yazar || '') + (item.demir ? ' · Demirbaş: ' + item.demir : '') + (item.isbn ? ' · ISBN: ' + item.isbn : '');
        document.getElementById('ustEserSearchField').style.display = 'none';
        document.getElementById('ustEserCard').style.display = 'flex';
    }

    function clearUstEser() {
        selectedUstEser = null;
        document.getElementById('ustEserKatalogId').value = '';
        document.getElementById('ustEserSearchField').style.display = 'block';
        document.getElementById('ustEserCard').style.display = 'none';
        document.getElementById('ustEserSearch').value = '';
    }

    // ============================
    // Katalog kütüphane transferi
    // ============================
    (function () {
        var backdrop = document.getElementById('katalogTransferBackdrop');
        var openBtn = document.getElementById('btnKatalogTransferOpen');
        var cancelBtn = document.getElementById('katalogTransferCancel');
        var submitBtn = document.getElementById('katalogTransferSubmit');
        var targetSel = document.getElementById('transferTargetKutuphaneId');
        var aciklamaEl = document.getElementById('transferAciklama');
        var mainKutSel = document.getElementById('kutuphaneId');
        var currentLibEl = document.getElementById('katalogTransferCurrentLib');
        var allowedList = @json($kutuphaneTransferAllowedJson);

        function rebuildTransferTargetOptions(excludeId) {
            if (!targetSel) return;
            var ex = excludeId != null && excludeId !== '' ? parseInt(excludeId, 10) : null;
            targetSel.innerHTML = '<option value="">— Seçiniz —</option>';
            (allowedList || []).forEach(function (row) {
                if (ex != null && !isNaN(ex) && row.id === ex) return;
                var opt = document.createElement('option');
                opt.value = String(row.id);
                opt.textContent = row.title;
                targetSel.appendChild(opt);
            });
        }

        function setBackdropVisible(show) {
            if (!backdrop) return;
            if (show) {
                backdrop.hidden = false;
                backdrop.classList.add('visible');
                document.body.style.overflow = 'hidden';
            } else {
                backdrop.classList.remove('visible');
                document.body.style.overflow = '';
                setTimeout(function () { backdrop.hidden = true; }, 200);
            }
        }

        if (openBtn && backdrop) {
            openBtn.addEventListener('click', function () {
                rebuildTransferTargetOptions(mainKutSel ? mainKutSel.value : null);
                if (targetSel) targetSel.value = '';
                if (aciklamaEl) aciklamaEl.value = '';
                setBackdropVisible(true);
            });
        }
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function () { setBackdropVisible(false); });
        }
        if (backdrop) {
            backdrop.addEventListener('click', function (e) {
                if (e.target === backdrop) setBackdropVisible(false);
            });
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && backdrop && backdrop.classList.contains('visible')) {
                setBackdropVisible(false);
            }
        });
        if (submitBtn) {
            submitBtn.addEventListener('click', function () {
                if (!targetSel || !mainKutSel) return;
                var toId = parseInt(targetSel.value, 10);
                if (!toId) {
                    showToast('error', 'Seçim gerekli', 'Lütfen transfer edilecek kütüphaneyi seçin.');
                    return;
                }
                submitBtn.disabled = true;
                fetch(@json(route('katalog.transferKutuphane', $kitap)), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        to_kutuphane_id: toId,
                        aciklama: aciklamaEl && aciklamaEl.value ? aciklamaEl.value.trim() : ''
                    })
                })
                .then(function (r) {
                    return r.text().then(function (text) {
                        var data = {};
                        try { data = text ? JSON.parse(text) : {}; } catch (err) { data = {}; }
                        if (!r.ok) {
                            var msg = 'HTTP ' + r.status;
                            if (data && data.message) msg = data.message;
                            else if (data && data.errors) {
                                if (data.errors.to_kutuphane_id && data.errors.to_kutuphane_id[0]) {
                                    msg = data.errors.to_kutuphane_id[0];
                                } else if (data.errors.aciklama && data.errors.aciklama[0]) {
                                    msg = data.errors.aciklama[0];
                                }
                            }
                            throw new Error(msg);
                        }
                        return data;
                    });
                })
                .then(function (data) {
                    if (mainKutSel) {
                        mainKutSel.value = String(data.kutuphane_id);
                    }
                    if (currentLibEl && data.kutuphane_title) {
                        currentLibEl.textContent = data.kutuphane_title;
                    }
                    rebuildTransferTargetOptions(data.kutuphane_id);
                    if (targetSel) targetSel.value = '';
                    if (aciklamaEl) aciklamaEl.value = '';
                    setBackdropVisible(false);
                    showToast('success', 'Transfer tamamlandı', data.message || 'Katalog yeni kütüphaneye taşındı.');
                })
                .catch(function (err) {
                    showToast('error', 'Transfer başarısız', err.message || 'Bilinmeyen hata.');
                })
                .finally(function () {
                    submitBtn.disabled = false;
                });
            });
        }
    })();

    // ============================
    // View Only Mode
    // ============================
    (function () {
        var isViewOnly = @json($isViewOnly);
        if (!isViewOnly) return;

        var form = document.getElementById('bookForm');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();
        });

        form.querySelectorAll('input, select, textarea, button').forEach(function (el) {
            if (el.type === 'hidden') return;
            el.disabled = true;
        });

        // Combobox ve kapak alanlarında etkileşimi tamamen kapat.
        form.querySelectorAll('.combobox-wrapper, .combobox-dropdown, .cover-upload-area, .ue-autocomplete-wrap, #ustEserSearchField').forEach(function (el) {
            el.style.pointerEvents = 'none';
        });
    })();

    // ============================
    // Keyboard Navigation (Left/Right)
    // ============================
    (function () {
        function isEditableTarget(el) {
            if (!el) return false;
            var tag = (el.tagName || '').toLowerCase();
            return tag === 'input' || tag === 'textarea' || tag === 'select' || el.isContentEditable;
        }

        document.addEventListener('keydown', function (e) {
            if (isEditableTarget(document.activeElement)) return;
            if (e.altKey || e.ctrlKey || e.metaKey || e.shiftKey) return;

            if (e.key === 'ArrowLeft') {
                var prevBtn = document.getElementById('prevKatalogBtn');
                if (prevBtn) {
                    e.preventDefault();
                    window.location.href = prevBtn.href;
                }
            } else if (e.key === 'ArrowRight') {
                var nextBtn = document.getElementById('nextKatalogBtn');
                if (nextBtn) {
                    e.preventDefault();
                    window.location.href = nextBtn.href;
                }
            }
        });
    })();

    // ============================
    // Sidebar active item highlight
    // ============================

</script>
@endsection
