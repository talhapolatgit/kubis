@extends('layouts.base')

@section('title', 'Kutuphane Duzenle')

@section('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <style>
        /* Info Banner */
        .info-banner { display: flex; align-items: center; gap: 12px; padding: 12px 16px; background: rgba(122,92,60,0.07); border: 1px solid rgba(122,92,60,0.18); border-radius: var(--radius); font-size: 13px; color: var(--foreground); }
        .info-banner svg { width: 16px; height: 16px; color: var(--primary); flex-shrink: 0; }
        .info-banner strong { font-weight: 600; }

        /* ── Two-Column Edit Layout ────────────────────────────────────────── */
        .edit-layout { display: flex; gap: 20px; align-items: flex-start; }
        .edit-form-wrap { flex: 1; min-width: 0; }

        /* Form Card */
        .form-card { border: 1px solid rgba(217,208,194,0.6); border-radius: var(--radius); background: var(--card); box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
        .form-card-header { padding: 24px 24px 16px; }
        .form-card-title { display: flex; align-items: center; gap: 8px; font-family: var(--font-serif); font-size: 20px; font-weight: 700; }
        .form-card-title svg { width: 20px; height: 20px; color: var(--primary); }
        .form-card-desc { font-size: 14px; color: var(--muted-foreground); margin-top: 4px; }
        .form-card-separator { height: 1px; background: var(--border); }
        .form-card-body { padding: 24px; display: flex; flex-direction: column; gap: 20px; }

        /* Section */
        .section-header { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted-foreground); margin-bottom: 14px; }
        .section-number { width: 20px; height: 20px; border-radius: 4px; background: rgba(122,92,60,0.1); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: var(--primary); }
        .section-sep { height: 1px; background: var(--border); }

        /* Form Grid */
        .form-grid { display: grid; gap: 16px; }
        .form-grid.cols-2 { grid-template-columns: repeat(2, 1fr); }
        .form-field { display: flex; flex-direction: column; }
        .form-label { font-size: 14px; font-weight: 500; color: var(--foreground); margin-bottom: 6px; }
        .form-label .required { color: var(--destructive); }
        .form-input, .form-select, .form-textarea { width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: calc(var(--radius) - 2px); background: var(--card); color: var(--foreground); font-size: 14px; line-height: 1.5; transition: border-color 0.15s, box-shadow 0.15s; outline: none; }
        .form-input::placeholder, .form-textarea::placeholder { color: var(--muted-foreground); opacity: 0.7; }
        .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: var(--ring); box-shadow: 0 0 0 2px rgba(122,92,60,0.15); }
        .form-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237a7060' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px; }
        .form-textarea { resize: vertical; }
        .form-error { font-size: 12px; color: var(--destructive); margin-top: 4px; }

        /* Meta info */
        .meta-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
        .meta-item { padding: 12px 14px; background: var(--secondary); border-radius: calc(var(--radius) - 2px); }
        .meta-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--muted-foreground); margin-bottom: 3px; }
        .meta-value { font-size: 13px; font-weight: 500; color: var(--foreground); }

        /* Actions */
        .form-actions { display: flex; justify-content: flex-end; gap: 12px; padding-top: 8px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 9px 16px; border-radius: calc(var(--radius) - 2px); font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.15s, opacity 0.15s; border: none; text-decoration: none; }
        .btn svg { width: 16px; height: 16px; }
        .btn-primary { background: var(--primary); color: var(--primary-foreground); }
        .btn-primary:hover { opacity: 0.9; }
        .btn-outline { background: transparent; color: var(--foreground); border: 1px solid var(--border); }
        .btn-outline:hover { background: var(--muted); }
        .btn-sm { padding: 6px 12px; font-size: 13px; }
        .btn-icon { padding: 6px; width: 30px; height: 30px; }

        /* ── Loading Overlay ───────────────────────────────────────────────── */
        .loading-overlay { position: fixed; inset: 0; z-index: 2000; background: rgba(61,50,38,0.45); backdrop-filter: blur(3px); display: flex; align-items: center; justify-content: center; opacity: 0; visibility: hidden; transition: opacity 0.2s ease, visibility 0.2s ease; }
        .loading-overlay.visible { opacity: 1; visibility: visible; }
        .loading-box { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 40px 56px; display: flex; flex-direction: column; align-items: center; gap: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.25); transform: scale(0.92); transition: transform 0.2s ease; }
        .loading-overlay.visible .loading-box { transform: scale(1); }
        .loading-spinner { width: 48px; height: 48px; border: 3px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.75s linear infinite; }
        .loading-text { font-size: 15px; font-weight: 600; color: var(--foreground); }
        .loading-subtext { font-size: 13px; color: var(--muted-foreground); margin-top: -12px; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        /* ── Toast ────────────────────────────────────────────────────────── */
        .toast-container { position: fixed; top: 16px; right: 16px; z-index: 3000; display: flex; flex-direction: column; gap: 8px; }
        .toast { padding: 14px 20px; border-radius: 10px; font-size: 14px; font-weight: 500; box-shadow: 0 4px 12px rgba(0,0,0,0.15); animation: toast-in 0.3s ease; max-width: 380px; }
        .toast.success { background: var(--success); color: white; }
        .toast.error { background: var(--destructive); color: white; }
        .toast.warning { background: var(--warning); color: white; }
        .toast-desc { font-size: 13px; font-weight: 400; opacity: 0.9; margin-top: 2px; }
        @keyframes toast-in { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes toast-out { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }

        /* ── Authorization Panel ───────────────────────────────────────────── */
        .auth-panel { width: 380px; flex-shrink: 0; border: 1px solid rgba(217,208,194,0.6); border-radius: var(--radius); background: var(--card); box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; position: sticky; top: 24px; max-height: calc(100vh - 80px); }

        .auth-panel-header { padding: 20px 20px 14px; flex-shrink: 0; }
        .auth-panel-title { display: flex; align-items: center; gap: 8px; }
        .auth-panel-title-text { font-family: var(--font-serif); font-size: 17px; font-weight: 700; }
        .auth-panel-title svg { width: 18px; height: 18px; color: var(--primary); }
        .auth-panel-badge { margin-left: auto; background: rgba(122,92,60,0.12); color: var(--primary); font-size: 12px; font-weight: 700; padding: 2px 8px; border-radius: 99px; min-width: 24px; text-align: center; }
        .auth-panel-desc { font-size: 13px; color: var(--muted-foreground); margin-top: 4px; }
        .auth-panel-sep { height: 1px; background: var(--border); }

        /* User list */
        .auth-user-list { flex: 1; overflow-y: auto; padding: 12px; display: flex; flex-direction: column; gap: 8px; min-height: 120px; max-height: 340px; }
        .auth-user-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: calc(var(--radius) - 2px); background: var(--secondary); border: 1px solid transparent; transition: border-color 0.15s; position: relative; }
        .auth-user-item:hover { border-color: var(--border); }
        .auth-user-avatar { width: 34px; height: 34px; border-radius: 50%; background: rgba(122,92,60,0.15); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; flex-shrink: 0; }
        .auth-user-info { flex: 1; min-width: 0; }
        .auth-user-name { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .auth-user-email { font-size: 12px; color: var(--muted-foreground); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .auth-user-meta { font-size: 11px; color: var(--muted-foreground); margin-top: 1px; }
        .auth-user-role { display: inline-flex; align-items: center; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; padding: 1px 6px; border-radius: 4px; }
        .role-admin { background: rgba(197,48,48,0.1); color: #c53030; }
        .role-personel { background: rgba(122,92,60,0.1); color: var(--primary); }
        .role-okuyucu { background: rgba(47,125,50,0.1); color: #2f7d32; }
        .auth-remove-btn { width: 26px; height: 26px; border-radius: 6px; border: none; background: transparent; cursor: pointer; color: var(--muted-foreground); display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: background 0.15s, color 0.15s; }
        .auth-remove-btn:hover { background: rgba(197,48,48,0.1); color: var(--destructive); }
        .auth-remove-btn svg { width: 14px; height: 14px; }
        .auth-remove-btn:disabled { opacity: 0.4; cursor: not-allowed; }

        /* Empty state */
        .auth-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 32px 16px; gap: 8px; color: var(--muted-foreground); text-align: center; }
        .auth-empty svg { width: 36px; height: 36px; opacity: 0.35; }
        .auth-empty-title { font-size: 14px; font-weight: 600; }
        .auth-empty-desc { font-size: 13px; }

        /* Skeleton loading */
        .auth-skeleton { padding: 10px 12px; border-radius: calc(var(--radius) - 2px); background: var(--secondary); display: flex; align-items: center; gap: 10px; }
        .skeleton-circle { width: 34px; height: 34px; border-radius: 50%; background: var(--border); animation: shimmer 1.4s ease infinite; flex-shrink: 0; }
        .skeleton-lines { flex: 1; display: flex; flex-direction: column; gap: 6px; }
        .skeleton-line { height: 10px; border-radius: 4px; background: var(--border); animation: shimmer 1.4s ease infinite; }
        .skeleton-line.short { width: 60%; }
        @keyframes shimmer { 0%,100% { opacity: 1; } 50% { opacity: 0.45; } }

        /* ── Add User Section ─────────────────────────────────────────────── */
        .auth-add-section { padding: 12px; flex-shrink: 0; border-top: 1px solid var(--border); }
        .auth-add-title { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--muted-foreground); margin-bottom: 8px; }
        .auth-search-wrap { position: relative; }
        .auth-search-input { width: 100%; padding: 8px 36px 8px 10px; border: 1px solid var(--border); border-radius: calc(var(--radius) - 2px); background: var(--background); font-size: 13px; color: var(--foreground); outline: none; transition: border-color 0.15s, box-shadow 0.15s; }
        .auth-search-input:focus { border-color: var(--ring); box-shadow: 0 0 0 2px rgba(122,92,60,0.15); }
        .auth-search-input::placeholder { color: var(--muted-foreground); opacity: 0.7; }
        .auth-search-icon { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: var(--muted-foreground); pointer-events: none; }
        .auth-search-spinner { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; border: 2px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.7s linear infinite; display: none; }
        .auth-search-spinner.visible { display: block; }

        /* Dropdown results */
        .auth-results-dropdown { position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: var(--card); border: 1px solid var(--border); border-radius: calc(var(--radius) - 2px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); z-index: 100; overflow: hidden; display: none; max-height: 220px; overflow-y: auto; }
        .auth-results-dropdown.visible { display: block; }
        .auth-result-item { display: flex; align-items: center; gap: 10px; padding: 9px 12px; cursor: pointer; transition: background 0.1s; }
        .auth-result-item:hover { background: var(--secondary); }
        .auth-result-item + .auth-result-item { border-top: 1px solid rgba(217,208,194,0.4); }
        .auth-result-avatar { width: 30px; height: 30px; border-radius: 50%; background: rgba(122,92,60,0.12); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0; }
        .auth-result-info { flex: 1; min-width: 0; }
        .auth-result-name { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .auth-result-email { font-size: 12px; color: var(--muted-foreground); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .auth-result-add-icon { width: 16px; height: 16px; color: var(--primary); flex-shrink: 0; opacity: 0.7; }
        .auth-result-empty { padding: 16px 12px; text-align: center; font-size: 13px; color: var(--muted-foreground); }
        .auth-result-loading { padding: 12px; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px; color: var(--muted-foreground); }

        /* Adding spinner inline */
        .auth-user-item.adding { opacity: 0.6; pointer-events: none; }
        .auth-user-item { animation: fadeInUp 0.25s ease; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
        .auth-user-item.removing { animation: fadeOut 0.2s ease forwards; }
        @keyframes fadeOut { to { opacity: 0; transform: translateX(10px); } }

        @media (max-width: 1100px) {
            .edit-layout { flex-direction: column; }
            .auth-panel { width: 100%; position: static; max-height: none; }
            .auth-user-list { max-height: 280px; }
        }
        @media (max-width: 768px) {
            .form-grid.cols-2, .meta-grid { grid-template-columns: 1fr; }
            .form-actions { flex-direction: column-reverse; }
        }
    </style>
@endsection

@section('breadcrumb')
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('kutuphane.index') }}" class="breadcrumb-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Kütüphaneler
        </a>
        <span class="breadcrumb-sep">/</span>
        <span class="breadcrumb-current">{{ $kutuphane->title }}</span>
    </nav>
@endsection

@section('content')
<div class="toast-container" id="toastContainer"></div>

<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-box">
        <div class="loading-spinner"></div>
        <span class="loading-text">Güncelleniyor...</span>
        <span class="loading-subtext">Lütfen bekleyin</span>
    </div>
</div>

        <div class="content-area">

            <!-- Info Banner -->
            <div class="info-banner">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                <span>Kütüphane kaydı düzenleniyor: <strong>{{ $kutuphane->title }}</strong></span>
            </div>

            <!-- Two-column edit layout -->
            <div class="edit-layout">

                <!-- LEFT: Edit Form -->
                <div class="edit-form-wrap">
                    <form id="kutuphaneDuzenleForm" class="form-card" method="POST"
                          action="{{ route('kutuphane.update', $kutuphane->id) }}" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="form-card-header">
                            <h2 class="form-card-title">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Kütüphane Düzenle
                            </h2>
                            <p class="form-card-desc">Kütüphane bilgilerini güncelleyin.</p>
                        </div>
                        <div class="form-card-separator"></div>

                        <div class="form-card-body">

                            <!-- Bölüm 1: Temel Bilgiler -->
                            <div>
                                <h3 class="section-header">
                                    <span class="section-number">1</span>
                                    Temel Bilgiler
                                </h3>
                                <div class="form-grid cols-2">
                                    <div class="form-field" style="grid-column: span 2;">
                                        <label class="form-label" for="title">Kütüphane Adı <span class="required">*</span></label>
                                        <input type="text" class="form-input" id="title" name="title"
                                               placeholder="Örnek: Örnektepe Kütüphanesi"
                                               value="{{ old('title', $kutuphane->title) }}" required />
                                        @error('title')<span class="form-error">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="statu">Durum <span class="required">*</span></label>
                                        <select class="form-select" id="statu" name="statu">
                                            <option value="aktif" {{ old('statu', $kutuphane->statu) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                            <option value="pasif" {{ old('statu', $kutuphane->statu) === 'pasif' ? 'selected' : '' }}>Pasif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="section-sep"></div>

                            <!-- Bölüm 2: İletişim Bilgileri -->
                            <div>
                                <h3 class="section-header">
                                    <span class="section-number">2</span>
                                    İletişim Bilgileri
                                </h3>
                                <div class="form-grid cols-2">
                                    <div class="form-field">
                                        <label class="form-label" for="phone">Telefon</label>
                                        <input type="text" class="form-input" id="phone" name="phone"
                                               placeholder="Örnek: 0212 555 00 00"
                                               value="{{ old('phone', $kutuphane->phone) }}" />
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="email">E-posta</label>
                                        <input type="email" class="form-input" id="email" name="email"
                                               placeholder="Örnek: bilgi@kutuphane.gov.tr"
                                               value="{{ old('email', $kutuphane->email) }}" />
                                        @error('email')<span class="form-error">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-field" style="grid-column: span 2;">
                                        <label class="form-label" for="address">Adres</label>
                                        <textarea class="form-textarea" id="address" name="address"
                                                  placeholder="Kütüphanenin tam adresi" rows="3">{{ old('address', $kutuphane->address) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="section-sep"></div>

                            <!-- Bölüm 3: Kayıt Bilgileri (Salt Okunur) -->
                            <div>
                                <h3 class="section-header">
                                    <span class="section-number">3</span>
                                    Kayıt Bilgileri
                                </h3>
                                <div class="meta-grid">
                                    <div class="meta-item">
                                        <div class="meta-label">Kayıt ID</div>
                                        <div class="meta-value">#{{ $kutuphane->id }}</div>
                                    </div>
                                    <div class="meta-item">
                                        <div class="meta-label">Oluşturulma Tarihi</div>
                                        <div class="meta-value">{{ \Carbon\Carbon::parse($kutuphane->created_at)->format('d.m.Y H:i') }}</div>
                                    </div>
                                    <div class="meta-item">
                                        <div class="meta-label">Son Güncelleme</div>
                                        <div class="meta-value">{{ $kutuphane->updated_at ? \Carbon\Carbon::parse($kutuphane->updated_at)->format('d.m.Y H:i') : '—' }}</div>
                                    </div>
                                    <div class="meta-item">
                                        <div class="meta-label">Mevcut Durum</div>
                                        <div class="meta-value" style="color: {{ $kutuphane->statu === 'aktif' ? '#1a6b1a' : '#9b1c1c' }}; font-weight:600;">
                                            {{ $kutuphane->statu === 'aktif' ? 'Aktif' : 'Pasif' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="form-actions">
                                <button type="button" class="btn btn-outline" id="resetBtn">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                    Sıfırla
                                </button>
                                <a href="{{ route('kutuphane.index') }}" class="btn btn-outline">
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

                <!-- RIGHT: Authorization Panel -->
                <aside class="auth-panel" id="authPanel" data-kutuphane-id="{{ $kutuphane->id }}">

                    <div class="auth-panel-header">
                        <div class="auth-panel-title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            <span class="auth-panel-title-text">Yetkili Kullanıcılar</span>
                            <span class="auth-panel-badge" id="authCount">—</span>
                        </div>
                        <p class="auth-panel-desc">Bu kütüphaneye erişim yetkisi olan kullanıcılar.</p>
                    </div>
                    <div class="auth-panel-sep"></div>

                    <!-- User List -->
                    <div class="auth-user-list" id="authUserList">
                        <!-- Skeleton -->
                        <div class="auth-skeleton" id="authSkeleton1">
                            <div class="skeleton-circle"></div>
                            <div class="skeleton-lines">
                                <div class="skeleton-line"></div>
                                <div class="skeleton-line short"></div>
                            </div>
                        </div>
                        <div class="auth-skeleton" id="authSkeleton2">
                            <div class="skeleton-circle"></div>
                            <div class="skeleton-lines">
                                <div class="skeleton-line"></div>
                                <div class="skeleton-line short"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Add User -->
                    <div class="auth-add-section">
                        <div class="auth-add-title">Kullanıcı Ekle</div>
                        <div class="auth-search-wrap">
                            <input
                                type="text"
                                class="auth-search-input"
                                id="authSearchInput"
                                placeholder="Ad veya e-posta ile ara..."
                                autocomplete="off"
                            />
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
@endsection

@section('scripts')
<script>
    // ── Toast ──────────────────────────────────────────────────────────────────
    function showToast(type, title, desc) {
        var c = document.getElementById('toastContainer');
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<div>' + title + '</div>' + (desc ? '<div class="toast-desc">' + desc + '</div>' : '');
        c.appendChild(t);
        setTimeout(function() { t.style.animation = 'toast-out 0.3s ease forwards'; setTimeout(function() { if (t.parentNode) t.parentNode.removeChild(t); }, 300); }, 3500);
    }

    // ── Form: Sıfırla ──────────────────────────────────────────────────────────
    var originalValues = {
        title:   @json($kutuphane->title),
        statu:   @json($kutuphane->statu ?? 'aktif'),
        phone:   @json($kutuphane->phone ?? ''),
        email:   @json($kutuphane->email ?? ''),
        address: @json($kutuphane->address ?? ''),
    };
    document.getElementById('resetBtn').addEventListener('click', function() {
        document.getElementById('title').value   = originalValues.title;
        document.getElementById('statu').value   = originalValues.statu;
        document.getElementById('phone').value   = originalValues.phone;
        document.getElementById('email').value   = originalValues.email;
        document.getElementById('address').value = originalValues.address;
        showToast('success', 'Sıfırlandı', 'Alanlar orijinal değerlerine döndürüldü.');
    });

    // ── Form: AJAX Submit ──────────────────────────────────────────────────────
    var form      = document.getElementById('kutuphaneDuzenleForm');
    var submitBtn = document.getElementById('submitBtn');
    var submitBtnOriginalHtml = submitBtn.innerHTML;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var title = document.getElementById('title').value.trim();
        if (!title) {
            showToast('error', 'Zorunlu alan eksik', 'Kütüphane adı boş bırakılamaz.');
            document.getElementById('title').focus();
            return;
        }
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin 0.8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Güncelleniyor...';
        document.getElementById('loadingOverlay').classList.add('visible');

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: new FormData(form)
        })
            .then(function(res) {
                return res.json().then(function(data) { return { status: res.status, data: data }; });
            })
            .then(function(result) {
                if (result.status === 200 && result.data.success) {
                    showToast('success', 'Güncelleme Başarılı', result.data.message);
                    originalValues.title   = document.getElementById('title').value.trim();
                    originalValues.statu   = document.getElementById('statu').value;
                    originalValues.phone   = document.getElementById('phone').value.trim();
                    originalValues.email   = document.getElementById('email').value.trim();
                    originalValues.address = document.getElementById('address').value.trim();
                } else if (result.status === 422 && result.data.errors) {
                    var msgs = Object.values(result.data.errors).flat();
                    showToast('error', 'Doğrulama Hatası', msgs[0]);
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

    // ════════════════════════════════════════════════════════════════════════════
    // ── Authorization Panel Logic ────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════════

    var kutuphaneId   = document.getElementById('authPanel').dataset.kutuphaneId;
    var csrfToken     = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var authUserList  = document.getElementById('authUserList');
    var authCount     = document.getElementById('authCount');
    var searchInput   = document.getElementById('authSearchInput');
    var dropdown      = document.getElementById('authResultsDropdown');
    var searchSpinner = document.getElementById('authSearchSpinner');
    var searchIcon    = document.getElementById('authSearchIcon');

    // Helper: Initials from name
    function initials(name) {
        return name.split(' ').slice(0, 2).map(function(w) { return w[0]; }).join('').toUpperCase();
    }

    // Helper: Role badge
    function roleBadge(role) {
        var labels = { admin: 'Admin', personel: 'Personel', okuyucu: 'Okuyucu' };
        return '<span class="auth-user-role role-' + role + '">' + (labels[role] || role) + '</span>';
    }

    // Helper: Format date (ISO → dd.mm.yyyy)
    function formatDate(str) {
        if (!str) return '—';
        var d = new Date(str);
        return d.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    // Remove skeleton placeholders
    function removeSkeleton() {
        ['authSkeleton1', 'authSkeleton2'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.remove();
        });
    }

    // Render the full user list
    function renderUserList(users) {
        removeSkeleton();
        authUserList.innerHTML = '';

        if (!users || users.length === 0) {
            authCount.textContent = '0';
            authUserList.innerHTML =
                '<div class="auth-empty">' +
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>' +
                '<div class="auth-empty-title">Henüz yetkili yok</div>' +
                '<div class="auth-empty-desc">Aşağıdan kullanıcı arayarak ekleyebilirsiniz.</div>' +
                '</div>';
            return;
        }

        authCount.textContent = users.length;
        users.forEach(function(u) { appendUserItem(u); });
    }

    // Append a single user item (used on add)
    function appendUserItem(u) {
        // Remove empty state if present
        var emptyEl = authUserList.querySelector('.auth-empty');
        if (emptyEl) emptyEl.remove();

        var div = document.createElement('div');
        div.className = 'auth-user-item';
        div.dataset.yetkiliId = u.id;
        div.dataset.userId    = u.user_id;
        div.innerHTML =
            '<div class="auth-user-avatar">' + initials(u.name) + '</div>' +
            '<div class="auth-user-info">' +
            '<div class="auth-user-name">' + escHtml(u.name) + ' ' + roleBadge(u.role) + '</div>' +
            '<div class="auth-user-email">' + escHtml(u.email) + '</div>' +
            '<div class="auth-user-meta">Eklendi: ' + formatDate(u.created_at) + (u.created_by_name ? ' · ' + escHtml(u.created_by_name) : '') + '</div>' +
            '</div>' +
            '<button class="auth-remove-btn" title="Yetkiyi Kaldır" onclick="removeYetkili(this, ' + u.id + ')">' +
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>' +
            '</button>';
        authUserList.appendChild(div);
    }

    // HTML escape helper
    function escHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str || ''));
        return div.innerHTML;
    }

    // ── Load yetkili list on page load ──────────────────────────────────────
    function loadYetkililer() {
        fetch('/kutuphane/' + kutuphaneId + '/yetkili', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) { renderUserList(data.data); }
                else { removeSkeleton(); showToast('error', 'Hata', 'Yetkili listesi yüklenemedi.'); }
            })
            .catch(function() { removeSkeleton(); showToast('error', 'Bağlantı Hatası', 'Yetkili listesi yüklenemedi.'); });
    }

    // ── Remove yetkili ───────────────────────────────────────────────────────
    function removeYetkili(btn, yetkiliId) {
        if (!confirm('Bu kullanıcının yetkisini kaldırmak istediğinize emin misiniz?')) return;
        btn.disabled = true;
        var item = btn.closest('.auth-user-item');

        fetch('/kutuphane/' + kutuphaneId + '/yetkili/' + yetkiliId, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            }
        })
            .then(function(res) { return res.json().then(function(d) { return { status: res.status, data: d }; }); })
            .then(function(result) {
                if (result.data.success) {
                    item.classList.add('removing');
                    setTimeout(function() {
                        item.remove();
                        // Update count
                        var remaining = authUserList.querySelectorAll('.auth-user-item').length;
                        authCount.textContent = remaining;
                        if (remaining === 0) { renderUserList([]); }
                    }, 200);
                    showToast('success', 'Yetki Kaldırıldı', result.data.message);
                } else {
                    btn.disabled = false;
                    showToast('error', 'Hata', result.data.message || 'İşlem başarısız.');
                }
            })
            .catch(function() { btn.disabled = false; showToast('error', 'Bağlantı Hatası', 'İşlem gerçekleştirilemedi.'); });
    }

    // ── Search users (debounced) ─────────────────────────────────────────────
    var searchTimer = null;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimer);
        var q = this.value.trim();

        if (q.length < 2) {
            dropdown.classList.remove('visible');
            dropdown.innerHTML = '';
            return;
        }

        searchTimer = setTimeout(function() {
            searchIcon.style.display   = 'none';
            searchSpinner.classList.add('visible');
            dropdown.classList.add('visible');
            dropdown.innerHTML = '<div class="auth-result-loading"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;animation:spin 0.7s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Aranıyor...</div>';

            fetch('/kutuphane/' + kutuphaneId + '/yetkili/search?q=' + encodeURIComponent(q), {
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

    function renderSearchResults(users) {
        if (users.length === 0) {
            dropdown.innerHTML = '<div class="auth-result-empty">Kullanıcı bulunamadı veya tüm eşleşenler zaten yetkili.</div>';
            return;
        }
        dropdown.innerHTML = '';
        users.forEach(function(u) {
            var div = document.createElement('div');
            div.className = 'auth-result-item';
            div.innerHTML =
                '<div class="auth-result-avatar">' + initials(u.name) + '</div>' +
                '<div class="auth-result-info">' +
                '<div class="auth-result-name">' + escHtml(u.name) + ' ' + roleBadge(u.role) + '</div>' +
                '<div class="auth-result-email">' + escHtml(u.email) + '</div>' +
                '</div>' +
                '<svg class="auth-result-add-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>';
            div.addEventListener('click', function() { addYetkili(u); });
            dropdown.appendChild(div);
        });
    }

    // ── Add yetkili ──────────────────────────────────────────────────────────
    function addYetkili(user) {
        dropdown.classList.remove('visible');
        dropdown.innerHTML = '';
        searchInput.value = '';

        fetch('/kutuphane/' + kutuphaneId + '/yetkili', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ user_id: user.id })
        })
            .then(function(res) { return res.json().then(function(d) { return { status: res.status, data: d }; }); })
            .then(function(result) {
                if (result.data.success) {
                    appendUserItem(result.data.data);
                    var count = authUserList.querySelectorAll('.auth-user-item').length;
                    authCount.textContent = count;
                    showToast('success', 'Yetkili Eklendi', result.data.message);
                } else {
                    showToast('warning', 'Uyarı', result.data.message || 'Kullanıcı eklenemedi.');
                }
            })
            .catch(function() { showToast('error', 'Bağlantı Hatası', 'İşlem gerçekleştirilemedi.'); });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.auth-search-wrap')) {
            dropdown.classList.remove('visible');
        }
    });

    // ── Boot ─────────────────────────────────────────────────────────────────
loadYetkililer();
</script>
@endsection
