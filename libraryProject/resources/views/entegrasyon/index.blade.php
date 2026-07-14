@extends('layouts.base')

@section('title', 'Entegrasyonlar')

@section('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <style>
        .entegrasyon-stack { display: flex; flex-direction: column; gap: 20px; max-width: 860px; }
        .page-head { margin-bottom: 4px; }
        .page-title { display: flex; align-items: center; gap: 8px; font-family: var(--font-serif); font-size: 22px; font-weight: 700; }
        .page-title svg { width: 22px; height: 22px; color: var(--primary); }
        .page-desc { font-size: 14px; color: var(--muted-foreground); margin-top: 4px; }

        .form-card { border: 1px solid rgba(217,208,194,0.6); border-radius: var(--radius); background: var(--card); box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow: hidden; }
        .accordion-toggle {
            width: 100%; display: block; text-align: left; padding: 18px 24px;
            background: transparent; border: none; cursor: pointer; color: inherit; font: inherit;
            transition: background 0.15s;
        }
        .accordion-toggle:hover { background: rgba(122,92,60,0.04); }
        .accordion-toggle:focus-visible { outline: 2px solid rgba(122,92,60,0.35); outline-offset: -2px; }
        .form-card-title { display: flex; align-items: center; gap: 8px; font-family: var(--font-serif); font-size: 18px; font-weight: 700; }
        .form-card-title > svg:first-child { width: 18px; height: 18px; color: var(--primary); flex-shrink: 0; }
        .form-card-desc { font-size: 13px; color: var(--muted-foreground); margin-top: 4px; padding-right: 28px; }
        .accordion-chevron { width: 18px; height: 18px; color: var(--muted-foreground); flex-shrink: 0; margin-left: 8px; transition: transform 0.2s ease; }
        .form-card.is-open .accordion-chevron { transform: rotate(180deg); }
        .accordion-panel { display: none; }
        .form-card.is-open .accordion-panel { display: block; }
        .form-card-separator { height: 1px; background: var(--border); }
        .form-card-body { padding: 20px 24px 24px; display: flex; flex-direction: column; gap: 16px; }

        .section-header { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted-foreground); margin-bottom: 4px; }
        .status-pill { margin-left: auto; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; padding: 3px 10px; border-radius: 99px; }
        .status-pill.on { background: rgba(47,125,50,0.12); color: #2f7d32; }
        .status-pill.off { background: rgba(122,112,96,0.12); color: var(--muted-foreground); }

        .form-grid { display: grid; gap: 16px; }
        .form-grid.cols-2 { grid-template-columns: repeat(2, 1fr); }
        .form-field { display: flex; flex-direction: column; }
        .form-label { font-size: 14px; font-weight: 500; color: var(--foreground); margin-bottom: 6px; }
        .form-label .required { color: var(--destructive); }
        .form-input, .form-select { width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: calc(var(--radius) - 2px); background: var(--card); color: var(--foreground); font-size: 14px; line-height: 1.5; transition: border-color 0.15s, box-shadow 0.15s; outline: none; }
        .form-input::placeholder { color: var(--muted-foreground); opacity: 0.7; }
        .form-input:focus, .form-select:focus { border-color: var(--ring); box-shadow: 0 0 0 2px rgba(122,92,60,0.15); }
        .form-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237a7060' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px; }
        .form-hint { font-size: 12px; color: var(--muted-foreground); margin-top: 4px; }
        .form-error { font-size: 12px; color: var(--destructive); margin-top: 4px; }

        .switch-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 14px; background: var(--secondary); border-radius: calc(var(--radius) - 2px); }
        .switch-text { font-size: 14px; font-weight: 500; }
        .switch-sub { font-size: 12px; color: var(--muted-foreground); margin-top: 2px; }
        .switch { position: relative; width: 44px; height: 24px; flex-shrink: 0; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .switch-slider { position: absolute; inset: 0; cursor: pointer; background: var(--border); border-radius: 99px; transition: background 0.2s; }
        .switch-slider::before { content: ''; position: absolute; width: 18px; height: 18px; left: 3px; top: 3px; background: #fff; border-radius: 50%; transition: transform 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.15); }
        .switch input:checked + .switch-slider { background: var(--primary); }
        .switch input:checked + .switch-slider::before { transform: translateX(20px); }

        .form-actions { display: flex; justify-content: flex-end; gap: 12px; padding-top: 4px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 9px 16px; border-radius: calc(var(--radius) - 2px); font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.15s, opacity 0.15s; border: none; text-decoration: none; }
        .btn svg { width: 16px; height: 16px; }
        .btn-primary { background: var(--primary); color: var(--primary-foreground); }
        .btn-primary:hover { opacity: 0.9; }
        .btn-outline { background: transparent; color: var(--foreground); border: 1px solid var(--border); }
        .btn-outline:hover { background: var(--muted); }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }

        .modal-backdrop { position: fixed; inset: 0; z-index: 2100; background: rgba(61,50,38,0.48); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; opacity: 0; visibility: hidden; transition: opacity 0.2s ease, visibility 0.2s ease; padding: 16px; }
        .modal-backdrop.visible { opacity: 1; visibility: visible; }
        .modal-box { background: var(--card); border: 1px solid var(--border); border-radius: 16px; max-width: 520px; width: 100%; box-shadow: 0 24px 64px rgba(0,0,0,0.22); transform: scale(0.93); transition: transform 0.2s ease; overflow: hidden; }
        .modal-backdrop.visible .modal-box { transform: scale(1); }
        .modal-header { padding: 20px 24px 0; display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
        .modal-title { font-family: var(--font-serif); font-size: 18px; font-weight: 700; }
        .modal-desc { font-size: 13px; color: var(--muted-foreground); margin-top: 4px; }
        .modal-close { width: 28px; height: 28px; border-radius: 6px; border: none; background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--muted-foreground); flex-shrink: 0; transition: background 0.15s; }
        .modal-close:hover { background: var(--muted); }
        .modal-close svg { width: 16px; height: 16px; }
        .modal-body { padding: 16px 24px 0; display: flex; flex-direction: column; gap: 14px; }
        .modal-footer { padding: 16px 24px 20px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border); margin-top: 16px; }

        .loading-overlay { position: fixed; inset: 0; z-index: 2200; background: rgba(61,50,38,0.45); backdrop-filter: blur(3px); display: flex; align-items: center; justify-content: center; opacity: 0; visibility: hidden; transition: opacity 0.2s ease, visibility 0.2s ease; }
        .loading-overlay.visible { opacity: 1; visibility: visible; }
        .loading-box { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 40px 56px; display: flex; flex-direction: column; align-items: center; gap: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.25); transform: scale(0.92); transition: transform 0.2s ease; }
        .loading-overlay.visible .loading-box { transform: scale(1); }
        .loading-spinner { width: 48px; height: 48px; border: 3px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.75s linear infinite; }
        .loading-text { font-size: 15px; font-weight: 600; color: var(--foreground); }
        .loading-subtext { font-size: 13px; color: var(--muted-foreground); margin-top: -12px; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        .toast-container { position: fixed; top: 16px; right: 16px; z-index: 3000; display: flex; flex-direction: column; gap: 8px; }
        .toast { padding: 14px 20px; border-radius: 10px; font-size: 14px; font-weight: 500; box-shadow: 0 4px 12px rgba(0,0,0,0.15); animation: toast-in 0.3s ease; max-width: 380px; }
        .toast.success { background: #2f7d32; color: white; }
        .toast.error { background: var(--destructive); color: white; }
        .toast-desc { font-size: 13px; font-weight: 400; opacity: 0.9; margin-top: 2px; }
        @keyframes toast-in { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes toast-out { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }

        @media (max-width: 768px) {
            .form-grid.cols-2 { grid-template-columns: 1fr; }
            .form-actions { flex-direction: column-reverse; }
        }
    </style>
@endsection

@section('breadcrumb')
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <span class="breadcrumb-current">Entegrasyonlar</span>
    </nav>
@endsection

@section('content')
<div class="toast-container" id="toastContainer"></div>

<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-box">
        <div class="loading-spinner"></div>
        <span class="loading-text">Kaydediliyor...</span>
        <span class="loading-subtext">Lütfen bekleyin</span>
    </div>
</div>

@php
    $smsAktif = (bool) old('sms_aktif', $sms?->aktif ?? false);
    $smsBaseUrl = old('sms_base_url', $smsAyarlar['base_url'] ?? 'https://1.1.1.1/FlexCityUi/rest/json/sms/SendSms');
    $smsContentType = old('sms_content_type', $smsAyarlar['content_type'] ?? 'application/json');
    $smsVerifySsl = (bool) old('sms_verify_ssl', $smsAyarlar['verify_ssl'] ?? false);

    $kimlikAktif = (bool) old('kimlik_aktif', $kimlik?->aktif ?? false);
    $kimlikBaseUrl = old('kimlik_base_url', $kimlikAyarlar['base_url'] ?? 'https://1.1.1.1/FlexCityUi/rest/json/sbs/FindSbsKisiDtoByNvi');
    $kimlikContentType = old('kimlik_content_type', $kimlikAyarlar['content_type'] ?? 'application/json');
    $kimlikVerifySsl = (bool) old('kimlik_verify_ssl', $kimlikAyarlar['verify_ssl'] ?? false);

    $adresAktif = (bool) old('adres_aktif', $adres?->aktif ?? false);
    $adresBaseUrl = old('adres_base_url', $adresAyarlar['base_url'] ?? 'https://1.1.1.1/FlexCityUi/rest/json/nvi/FindAllBaseAdresDto');
    $adresContentType = old('adres_content_type', $adresAyarlar['content_type'] ?? 'application/json');
    $adresVerifySsl = (bool) old('adres_verify_ssl', $adresAyarlar['verify_ssl'] ?? false);

    $ldapAktif = (bool) old('ldap_aktif', $ldap?->aktif ?? false);
    $ldapHost = old('ldap_host', $ldapAyarlar['host'] ?? config('services.ldap.host', 'ldap://128.0.0.4:389'));
    $ldapBaseDn = old('ldap_base_dn', $ldapAyarlar['base_dn'] ?? config('services.ldap.base_dn', 'DC=xxx,DC=bel,DC=tr'));
    $ldapProtocolVersion = (int) old('ldap_protocol_version', $ldapAyarlar['protocol_version'] ?? 3);
    $ldapReferrals = (bool) old('ldap_referrals', $ldapAyarlar['referrals'] ?? false);

    $webhookAktif = (bool) old('webhook_aktif', $webhook?->aktif ?? false);
    $webhookUrl = old('webhook_url', $webhookAyarlar['webhook_url'] ?? config('services.webhook.url', ''));
@endphp

<div class="content-area entegrasyon-stack">
    <div class="page-head">
        <h1 class="page-title">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
            Entegrasyonlar
        </h1>
        <p class="page-desc">SMS, kimlik, adres, LDAP ve webhook servis bağlantılarını yönetin.</p>
    </div>

    {{-- SMS --}}
    <form id="smsEntegrasyonForm" class="form-card" method="POST" action="{{ route('entegrasyon.sms.update') }}" novalidate>
        @csrf
        @method('PUT')
        <button type="button" class="accordion-toggle" aria-expanded="false">
            <h2 class="form-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                SMS Entegrasyonu
                <span class="status-pill {{ $smsAktif ? 'on' : 'off' }}" id="smsStatusPill">{{ $smsAktif ? 'Aktif' : 'Pasif' }}</span>
                <svg class="accordion-chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
            </h2>
            <p class="form-card-desc">OTP, ödünç ve bildirim SMS gönderimleri için kullanılır.</p>
        </button>
        <div class="accordion-panel">
        <div class="form-card-separator"></div>
        <div class="form-card-body">
            <div class="form-grid cols-2">
                <div class="form-field" style="grid-column: span 2;">
                    <div class="switch-row">
                        <div>
                            <div class="switch-text">Entegrasyonu etkinleştir</div>
                            <div class="switch-sub">Kapalıyken SMS gönderimi yapılamaz.</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="aktif" id="sms_aktif" value="1" {{ $smsAktif ? 'checked' : '' }} />
                            <span class="switch-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="form-field">
                    <label class="form-label" for="sms_saglayici">Sağlayıcı <span class="required">*</span></label>
                    <select class="form-select" id="sms_saglayici" name="saglayici">
                        <option value="flexcity" selected>Flexcity SMS</option>
                    </select>
                </div>
                <div class="form-field">
                    <label class="form-label" for="sms_content_type">Content-Type</label>
                    <input type="text" class="form-input" id="sms_content_type" name="content_type" value="{{ $smsContentType }}" placeholder="application/json" />
                </div>
                <div class="form-field" style="grid-column: span 2;">
                    <label class="form-label" for="sms_base_url">Servis URL <span class="required">*</span></label>
                    <input type="url" class="form-input" id="sms_base_url" name="base_url" value="{{ $smsBaseUrl }}" placeholder="https://servis.ornek.bel.tr/.../SendSms" required />
                </div>
                <div class="form-field" style="grid-column: span 2;">
                    <label class="form-label" for="sms_authorization">
                        Authorization
                        @unless($smsAuthorizationKayitli)<span class="required">*</span>@endunless
                    </label>
                    <input type="password" class="form-input" id="sms_authorization" name="authorization" value=""
                           placeholder="{{ $smsAuthorizationKayitli ? 'Kayıtlı — değiştirmek için yeni değer girin' : 'applicationkey=...,requestdate=...,md5hashcode=...' }}"
                           autocomplete="new-password" {{ $smsAuthorizationKayitli ? '' : 'required' }} />
                    <p class="form-hint" id="smsAuthHint">
                        @if($smsAuthorizationKayitli)
                            Güvenlik nedeniyle mevcut değer gösterilmez. Boş bırakırsanız mevcut Authorization korunur.
                        @else
                            Flexcity Authorization başlık değerini girin.
                        @endif
                    </p>
                </div>
                <div class="form-field" style="grid-column: span 2;">
                    <div class="switch-row">
                        <div>
                            <div class="switch-text">SSL doğrulaması</div>
                            <div class="switch-sub">Kapalı bırakmak self-signed sertifikalarda bağlantıyı kolaylaştırır.</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="verify_ssl" id="sms_verify_ssl" value="1" {{ $smsVerifySsl ? 'checked' : '' }} />
                            <span class="switch-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-outline" id="smsTestOpenBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="m9 15 2 2 4-4"/></svg>
                    Test Et
                </button>
                <button type="submit" class="btn btn-primary" id="smsSubmitBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v13a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    SMS Ayarlarını Kaydet
                </button>
            </div>
        </div>
        </div>
    </form>

    {{-- Kimlik Sorgulama --}}
    <form id="kimlikEntegrasyonForm" class="form-card" method="POST" action="{{ route('entegrasyon.kimlik.update') }}" novalidate>
        @csrf
        @method('PUT')
        <button type="button" class="accordion-toggle" aria-expanded="false">
            <h2 class="form-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Kimlik Sorgulama Entegrasyonu
                <span class="status-pill {{ $kimlikAktif ? 'on' : 'off' }}" id="kimlikStatusPill">{{ $kimlikAktif ? 'Aktif' : 'Pasif' }}</span>
                <svg class="accordion-chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
            </h2>
            <p class="form-card-desc">TC kimlik / NVI kimlik doğrulama sorguları için kullanılır (KPS).</p>
        </button>
        <div class="accordion-panel">
        <div class="form-card-separator"></div>
        <div class="form-card-body">
            <div class="form-grid cols-2">
                <div class="form-field" style="grid-column: span 2;">
                    <div class="switch-row">
                        <div>
                            <div class="switch-text">Entegrasyonu etkinleştir</div>
                            <div class="switch-sub">Kapalıyken kimlik sorgusu yapılamaz.</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="aktif" id="kimlik_aktif" value="1" {{ $kimlikAktif ? 'checked' : '' }} />
                            <span class="switch-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="form-field">
                    <label class="form-label" for="kimlik_saglayici">Sağlayıcı <span class="required">*</span></label>
                    <select class="form-select" id="kimlik_saglayici" name="saglayici">
                        <option value="flexcity" selected>Flexcity KPS</option>
                    </select>
                </div>
                <div class="form-field">
                    <label class="form-label" for="kimlik_content_type">Content-Type</label>
                    <input type="text" class="form-input" id="kimlik_content_type" name="content_type" value="{{ $kimlikContentType }}" placeholder="application/json" />
                </div>
                <div class="form-field" style="grid-column: span 2;">
                    <label class="form-label" for="kimlik_base_url">Servis URL <span class="required">*</span></label>
                    <input type="url" class="form-input" id="kimlik_base_url" name="base_url" value="{{ $kimlikBaseUrl }}"
                           placeholder="https://1.1.1.1/FlexCityUi/rest/json/sbs/FindSbsKisiDtoByNvi" required />
                </div>
                <div class="form-field" style="grid-column: span 2;">
                    <label class="form-label" for="kimlik_authorization">
                        Authorization
                        @unless($kimlikAuthorizationKayitli)<span class="required">*</span>@endunless
                    </label>
                    <input type="password" class="form-input" id="kimlik_authorization" name="authorization" value=""
                           placeholder="{{ $kimlikAuthorizationKayitli ? 'Kayıtlı — değiştirmek için yeni değer girin' : 'applicationkey=...,requestdate=...,md5hashcode=...' }}"
                           autocomplete="new-password" {{ $kimlikAuthorizationKayitli ? '' : 'required' }} />
                    <p class="form-hint" id="kimlikAuthHint">
                        @if($kimlikAuthorizationKayitli)
                            Güvenlik nedeniyle mevcut değer gösterilmez. Boş bırakırsanız mevcut Authorization korunur.
                        @else
                            Flexcity Authorization başlık değerini girin.
                        @endif
                    </p>
                </div>
                <div class="form-field" style="grid-column: span 2;">
                    <div class="switch-row">
                        <div>
                            <div class="switch-text">SSL doğrulaması</div>
                            <div class="switch-sub">Kapalı bırakmak self-signed / iç ağ sertifikalarında bağlantıyı kolaylaştırır.</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="verify_ssl" id="kimlik_verify_ssl" value="1" {{ $kimlikVerifySsl ? 'checked' : '' }} />
                            <span class="switch-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-outline" id="kimlikTestOpenBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="m9 15 2 2 4-4"/></svg>
                    Test Et
                </button>
                <button type="submit" class="btn btn-primary" id="kimlikSubmitBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v13a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Kimlik Ayarlarını Kaydet
                </button>
            </div>
        </div>
        </div>
    </form>

    {{-- Adres Sorgulama --}}
    <form id="adresEntegrasyonForm" class="form-card" method="POST" action="{{ route('entegrasyon.adres.update') }}" novalidate>
        @csrf
        @method('PUT')
        <button type="button" class="accordion-toggle" aria-expanded="false">
            <h2 class="form-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
                Adres Sorgulama Entegrasyonu
                <span class="status-pill {{ $adresAktif ? 'on' : 'off' }}" id="adresStatusPill">{{ $adresAktif ? 'Aktif' : 'Pasif' }}</span>
                <svg class="accordion-chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
            </h2>
            <p class="form-card-desc">NVI adres sorguları için kullanılır (ikamet / açık adres).</p>
        </button>
        <div class="accordion-panel">
        <div class="form-card-separator"></div>
        <div class="form-card-body">
            <div class="form-grid cols-2">
                <div class="form-field" style="grid-column: span 2;">
                    <div class="switch-row">
                        <div>
                            <div class="switch-text">Entegrasyonu etkinleştir</div>
                            <div class="switch-sub">Kapalıyken adres sorgusu yapılamaz.</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="aktif" id="adres_aktif" value="1" {{ $adresAktif ? 'checked' : '' }} />
                            <span class="switch-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="form-field">
                    <label class="form-label" for="adres_saglayici">Sağlayıcı <span class="required">*</span></label>
                    <select class="form-select" id="adres_saglayici" name="saglayici">
                        <option value="flexcity" selected>Flexcity NVI Adres</option>
                    </select>
                </div>
                <div class="form-field">
                    <label class="form-label" for="adres_content_type">Content-Type</label>
                    <input type="text" class="form-input" id="adres_content_type" name="content_type" value="{{ $adresContentType }}" placeholder="application/json" />
                </div>
                <div class="form-field" style="grid-column: span 2;">
                    <label class="form-label" for="adres_base_url">Servis URL <span class="required">*</span></label>
                    <input type="url" class="form-input" id="adres_base_url" name="base_url" value="{{ $adresBaseUrl }}"
                           placeholder="https://1.1.1.1/FlexCityUi/rest/json/nvi/FindAllBaseAdresDto" required />
                </div>
                <div class="form-field" style="grid-column: span 2;">
                    <label class="form-label" for="adres_authorization">
                        Authorization
                        @unless($adresAuthorizationKayitli)<span class="required">*</span>@endunless
                    </label>
                    <input type="password" class="form-input" id="adres_authorization" name="authorization" value=""
                           placeholder="{{ $adresAuthorizationKayitli ? 'Kayıtlı — değiştirmek için yeni değer girin' : 'applicationkey=...,requestdate=...,md5hashcode=...' }}"
                           autocomplete="new-password" {{ $adresAuthorizationKayitli ? '' : 'required' }} />
                    <p class="form-hint" id="adresAuthHint">
                        @if($adresAuthorizationKayitli)
                            Güvenlik nedeniyle mevcut değer gösterilmez. Boş bırakırsanız mevcut Authorization korunur.
                        @else
                            Flexcity Authorization başlık değerini girin.
                        @endif
                    </p>
                </div>
                <div class="form-field" style="grid-column: span 2;">
                    <div class="switch-row">
                        <div>
                            <div class="switch-text">SSL doğrulaması</div>
                            <div class="switch-sub">Kapalı bırakmak self-signed / iç ağ sertifikalarında bağlantıyı kolaylaştırır.</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="verify_ssl" id="adres_verify_ssl" value="1" {{ $adresVerifySsl ? 'checked' : '' }} />
                            <span class="switch-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-outline" id="adresTestOpenBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="m9 15 2 2 4-4"/></svg>
                    Test Et
                </button>
                <button type="submit" class="btn btn-primary" id="adresSubmitBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v13a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Adres Ayarlarını Kaydet
                </button>
            </div>
        </div>
        </div>
    </form>

    {{-- LDAP --}}
    <form id="ldapEntegrasyonForm" class="form-card" method="POST" action="{{ route('entegrasyon.ldap.update') }}" novalidate>
        @csrf
        @method('PUT')
        <button type="button" class="accordion-toggle" aria-expanded="false">
            <h2 class="form-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                LDAP Entegrasyonu
                <span class="status-pill {{ $ldapAktif ? 'on' : 'off' }}" id="ldapStatusPill">{{ $ldapAktif ? 'Aktif' : 'Pasif' }}</span>
                <svg class="accordion-chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
            </h2>
            <p class="form-card-desc">LDAP ile giriş ve kullanıcı araması için Active Directory bağlantı ayarları.</p>
        </button>
        <div class="accordion-panel">
        <div class="form-card-separator"></div>
        <div class="form-card-body">
            <div class="form-grid cols-2">
                <div class="form-field" style="grid-column: span 2;">
                    <div class="switch-row">
                        <div>
                            <div class="switch-text">Entegrasyonu etkinleştir</div>
                            <div class="switch-sub">Kapalıyken LDAP girişi ve kullanıcı araması yapılamaz.</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="aktif" id="ldap_aktif" value="1" {{ $ldapAktif ? 'checked' : '' }} />
                            <span class="switch-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="form-field">
                    <label class="form-label" for="ldap_saglayici">Sağlayıcı <span class="required">*</span></label>
                    <select class="form-select" id="ldap_saglayici" name="saglayici">
                        <option value="active_directory" selected>Active Directory</option>
                    </select>
                </div>
                <div class="form-field">
                    <label class="form-label" for="ldap_protocol_version">Protokol Sürümü</label>
                    <select class="form-select" id="ldap_protocol_version" name="protocol_version">
                        <option value="3" {{ $ldapProtocolVersion === 3 ? 'selected' : '' }}>LDAP v3</option>
                        <option value="2" {{ $ldapProtocolVersion === 2 ? 'selected' : '' }}>LDAP v2</option>
                    </select>
                </div>
                <div class="form-field" style="grid-column: span 2;">
                    <label class="form-label" for="ldap_host">Sunucu Adresi (Host) <span class="required">*</span></label>
                    <input type="text" class="form-input" id="ldap_host" name="host" value="{{ $ldapHost }}"
                           placeholder="ldap://128.0.0.4:389" required />
                    <p class="form-hint">Örn: ldap://sunucu:389 veya ldaps://sunucu:636</p>
                    @error('host')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-field" style="grid-column: span 2;">
                    <label class="form-label" for="ldap_base_dn">Base DN <span class="required">*</span></label>
                    <input type="text" class="form-input" id="ldap_base_dn" name="base_dn" value="{{ $ldapBaseDn }}"
                           placeholder="DC=xxx,DC=bel,DC=tr" required />
                    <p class="form-hint">Kullanıcı araması ve oturum açma UPN alanı bu değerden türetilir.</p>
                    @error('base_dn')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-field" style="grid-column: span 2;">
                    <div class="switch-row">
                        <div>
                            <div class="switch-text">LDAP referrals</div>
                            <div class="switch-sub">Active Directory için genelde kapalı tutulur.</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="referrals" id="ldap_referrals" value="1" {{ $ldapReferrals ? 'checked' : '' }} />
                            <span class="switch-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-outline" id="ldapTestOpenBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="m9 15 2 2 4-4"/></svg>
                    Test Et
                </button>
                <button type="submit" class="btn btn-primary" id="ldapSubmitBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v13a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    LDAP Ayarlarını Kaydet
                </button>
            </div>
        </div>
        </div>
    </form>

    {{-- Webhook --}}
    <form id="webhookEntegrasyonForm" class="form-card" method="POST" action="{{ route('entegrasyon.webhook.update') }}" novalidate>
        @csrf
        @method('PUT')
        <button type="button" class="accordion-toggle" aria-expanded="false">
            <h2 class="form-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                Webhook Entegrasyonu
                <span class="status-pill {{ $webhookAktif ? 'on' : 'off' }}" id="webhookStatusPill">{{ $webhookAktif ? 'Aktif' : 'Pasif' }}</span>
                <svg class="accordion-chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
            </h2>
            <p class="form-card-desc">Push bildirim ve üye bildirimleri için HMAC imzalı webhook bağlantısı.</p>
        </button>
        <div class="accordion-panel">
        <div class="form-card-separator"></div>
        <div class="form-card-body">
            <div class="form-grid cols-2">
                <div class="form-field" style="grid-column: span 2;">
                    <div class="switch-row">
                        <div>
                            <div class="switch-text">Entegrasyonu etkinleştir</div>
                            <div class="switch-sub">Kapalıyken webhook bildirimleri gönderilemez.</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="aktif" id="webhook_aktif" value="1" {{ $webhookAktif ? 'checked' : '' }} />
                            <span class="switch-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="form-field">
                    <label class="form-label" for="webhook_saglayici">Sağlayıcı <span class="required">*</span></label>
                    <select class="form-select" id="webhook_saglayici" name="saglayici">
                        <option value="hmac" selected>HMAC-SHA256 Webhook</option>
                    </select>
                </div>
                <div class="form-field" style="grid-column: span 2;">
                    <label class="form-label" for="webhook_url">Webhook URL <span class="required">*</span></label>
                    <input type="url" class="form-input" id="webhook_url" name="webhook_url" value="{{ $webhookUrl }}"
                           placeholder="https://api.example.com/api/webhook/kutuphane/bildirim" required />
                    @error('webhook_url')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-field" style="grid-column: span 2;">
                    <label class="form-label" for="webhook_secret">
                        Secret
                        @unless($webhookSecretKayitli)<span class="required">*</span>@endunless
                    </label>
                    <input type="password" class="form-input" id="webhook_secret" name="secret" value=""
                           placeholder="{{ $webhookSecretKayitli ? 'Kayıtlı — değiştirmek için yeni değer girin' : 'HMAC imza anahtarı' }}"
                           autocomplete="new-password" {{ $webhookSecretKayitli ? '' : 'required' }} />
                    <p class="form-hint" id="webhookSecretHint">
                        @if($webhookSecretKayitli)
                            Güvenlik nedeniyle mevcut secret gösterilmez. Boş bırakırsanız mevcut değer korunur.
                        @else
                            İstek imzalamada kullanılacak gizli anahtar.
                        @endif
                    </p>
                    @error('secret')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-outline" id="webhookTestOpenBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="m9 15 2 2 4-4"/></svg>
                    Test Et
                </button>
                <button type="submit" class="btn btn-primary" id="webhookSubmitBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v13a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Webhook Ayarlarını Kaydet
                </button>
            </div>
        </div>
        </div>
    </form>
</div>

<div class="modal-backdrop" id="smsTestModal" role="dialog" aria-modal="true" aria-labelledby="smsTestModalTitle" hidden>
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h2 class="modal-title" id="smsTestModalTitle">SMS Testi</h2>
                <p class="modal-desc">Kayıtlı ve aktif SMS ayarlarıyla test mesajı gönderir.</p>
            </div>
            <button type="button" class="modal-close" id="smsTestModalClose" aria-label="Kapat">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-grid cols-2">
                <div class="form-field" style="grid-column: span 2;">
                    <label class="form-label" for="sms_test_telefon">Telefon <span class="required">*</span></label>
                    <input type="tel" class="form-input" id="sms_test_telefon" inputmode="tel" maxlength="13" placeholder="05xxxxxxxxx" autocomplete="off" />
                    <p class="form-hint">Türkiye cep numarası (05xxxxxxxxx).</p>
                </div>
                <div class="form-field" style="grid-column: span 2;">
                    <label class="form-label" for="sms_test_message">Mesaj <span class="required">*</span></label>
                    <textarea class="form-input" id="sms_test_message" rows="3" maxlength="1000" placeholder="Test SMS mesajı" style="resize:vertical;">Kütüphane SMS entegrasyon testi</textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" id="smsTestModalCancel">İptal</button>
            <button type="button" class="btn btn-primary" id="smsTestBtn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                Test Gönder
            </button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="kimlikTestModal" role="dialog" aria-modal="true" aria-labelledby="kimlikTestModalTitle" hidden>
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h2 class="modal-title" id="kimlikTestModalTitle">Kimlik Sorgulama Testi</h2>
                <p class="modal-desc">Kayıtlı ve aktif kimlik entegrasyonuyla KPS sorgusu dener.</p>
            </div>
            <button type="button" class="modal-close" id="kimlikTestModalClose" aria-label="Kapat">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-grid cols-2">
                <div class="form-field">
                    <label class="form-label" for="kimlik_test_tc">TC Kimlik No <span class="required">*</span></label>
                    <input type="text" class="form-input" id="kimlik_test_tc" inputmode="numeric" maxlength="11" placeholder="11 haneli TC" autocomplete="off" />
                </div>
                <div class="form-field">
                    <label class="form-label" for="kimlik_test_dogum">Doğum Tarihi <span class="required">*</span></label>
                    <input type="date" class="form-input" id="kimlik_test_dogum" autocomplete="off" />
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" id="kimlikTestModalCancel">İptal</button>
            <button type="button" class="btn btn-primary" id="kimlikTestBtn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                Sorgula
            </button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="adresTestModal" role="dialog" aria-modal="true" aria-labelledby="adresTestModalTitle" hidden>
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h2 class="modal-title" id="adresTestModalTitle">Adres Sorgulama Testi</h2>
                <p class="modal-desc">Kayıtlı ve aktif adres entegrasyonuyla NVI adres sorgusu dener.</p>
            </div>
            <button type="button" class="modal-close" id="adresTestModalClose" aria-label="Kapat">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-grid cols-2">
                <div class="form-field">
                    <label class="form-label" for="adres_test_tc">TC Kimlik No <span class="required">*</span></label>
                    <input type="text" class="form-input" id="adres_test_tc" inputmode="numeric" maxlength="11" placeholder="11 haneli TC" autocomplete="off" />
                </div>
                <div class="form-field">
                    <label class="form-label" for="adres_test_dogum">Doğum Tarihi <span class="required">*</span></label>
                    <input type="date" class="form-input" id="adres_test_dogum" autocomplete="off" />
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" id="adresTestModalCancel">İptal</button>
            <button type="button" class="btn btn-primary" id="adresTestBtn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                Sorgula
            </button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="ldapTestModal" role="dialog" aria-modal="true" aria-labelledby="ldapTestModalTitle" hidden>
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h2 class="modal-title" id="ldapTestModalTitle">LDAP Testi</h2>
                <p class="modal-desc">Kayıtlı ve aktif LDAP ayarlarıyla sunucu bağlantısı ve kimlik doğrulamayı dener.</p>
            </div>
            <button type="button" class="modal-close" id="ldapTestModalClose" aria-label="Kapat">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-grid cols-2">
                <div class="form-field" style="grid-column: span 2;">
                    <label class="form-label" for="ldap_test_username">LDAP Kullanıcı Adı <span class="required">*</span></label>
                    <input type="text" class="form-input" id="ldap_test_username" maxlength="255" placeholder="kullanici veya kullanici@domain" autocomplete="off" />
                    <p class="form-hint">sAMAccountName veya UPN girilebilir.</p>
                </div>
                <div class="form-field" style="grid-column: span 2;">
                    <label class="form-label" for="ldap_test_password">Şifre <span class="required">*</span></label>
                    <input type="password" class="form-input" id="ldap_test_password" maxlength="255" placeholder="LDAP şifresi" autocomplete="new-password" />
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" id="ldapTestModalCancel">İptal</button>
            <button type="button" class="btn btn-primary" id="ldapTestBtn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                Test Gönder
            </button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="webhookTestModal" role="dialog" aria-modal="true" aria-labelledby="webhookTestModalTitle" hidden>
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h2 class="modal-title" id="webhookTestModalTitle">Webhook Testi</h2>
                <p class="modal-desc">Kayıtlı ve aktif webhook ayarlarıyla test bildirimi gönderir.</p>
            </div>
            <button type="button" class="modal-close" id="webhookTestModalClose" aria-label="Kapat">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-grid cols-2">
                <div class="form-field">
                    <label class="form-label" for="webhook_test_tc">TC Kimlik No <span class="required">*</span></label>
                    <input type="text" class="form-input" id="webhook_test_tc" inputmode="numeric" maxlength="11" placeholder="11 haneli TC" autocomplete="off" />
                </div>
                <div class="form-field">
                    <label class="form-label" for="webhook_test_title">Başlık <span class="required">*</span></label>
                    <input type="text" class="form-input" id="webhook_test_title" maxlength="200" placeholder="Örn: Test Bildirimi" autocomplete="off" />
                </div>
                <div class="form-field" style="grid-column: span 2;">
                    <label class="form-label" for="webhook_test_message">Mesaj <span class="required">*</span></label>
                    <textarea class="form-input" id="webhook_test_message" rows="3" maxlength="2000" placeholder="Test bildirim mesajı" style="resize:vertical;"></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" id="webhookTestModalCancel">İptal</button>
            <button type="button" class="btn btn-primary" id="webhookTestBtn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                Test Gönder
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    document.querySelectorAll('.entegrasyon-stack .accordion-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            var card = toggle.closest('.form-card');
            if (!card) return;
            var willOpen = !card.classList.contains('is-open');

            document.querySelectorAll('.entegrasyon-stack .form-card.is-open').forEach(function (openCard) {
                if (openCard !== card) {
                    openCard.classList.remove('is-open');
                    var otherToggle = openCard.querySelector('.accordion-toggle');
                    if (otherToggle) otherToggle.setAttribute('aria-expanded', 'false');
                }
            });

            card.classList.toggle('is-open', willOpen);
            toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
    });

    function showToast(type, title, desc) {
        var c = document.getElementById('toastContainer');
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<div>' + title + '</div>' + (desc ? '<div class="toast-desc">' + desc + '</div>' : '');
        c.appendChild(t);
        setTimeout(function () {
            t.style.animation = 'toast-out 0.3s ease forwards';
            setTimeout(function () { if (t.parentNode) t.parentNode.removeChild(t); }, 300);
        }, 3500);
    }

    function bindIntegrationForm(opts) {
        var form = document.getElementById(opts.formId);
        var submitBtn = document.getElementById(opts.submitBtnId);
        var submitBtnOriginalHtml = submitBtn.innerHTML;
        var aktifEl = document.getElementById(opts.aktifId);
        var statusPill = document.getElementById(opts.statusPillId);
        var authInput = document.getElementById(opts.authId);
        var authHint = document.getElementById(opts.authHintId);
        var baseUrlInput = document.getElementById(opts.baseUrlId);
        var authorizationKayitli = opts.authorizationKayitli;

        function syncStatusPill() {
            if (aktifEl.checked) {
                statusPill.textContent = 'Aktif';
                statusPill.className = 'status-pill on';
            } else {
                statusPill.textContent = 'Pasif';
                statusPill.className = 'status-pill off';
            }
        }

        aktifEl.addEventListener('change', syncStatusPill);

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var baseUrl = baseUrlInput.value.trim();
            var authorization = authInput.value.trim();
            if (!baseUrl) {
                showToast('error', 'Zorunlu alan', 'Servis URL adresi zorunludur.');
                return;
            }
            if (!authorization && !authorizationKayitli) {
                showToast('error', 'Zorunlu alan', 'Authorization değeri zorunludur.');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin 0.8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Kaydediliyor...';
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
            .then(function (res) {
                return res.json().then(function (data) { return { status: res.status, data: data }; });
            })
            .then(function (result) {
                if (result.status === 200 && result.data.success) {
                    showToast('success', 'Kaydedildi', result.data.message);
                    if (result.data.authorization_kayitli) {
                        authorizationKayitli = true;
                        authInput.value = '';
                        authInput.removeAttribute('required');
                        authInput.placeholder = 'Kayıtlı — değiştirmek için yeni değer girin';
                        if (authHint) {
                            authHint.textContent = 'Güvenlik nedeniyle mevcut değer gösterilmez. Boş bırakırsanız mevcut Authorization korunur.';
                        }
                    }
                    syncStatusPill();
                } else if (result.status === 422 && result.data.errors) {
                    var msgs = Object.values(result.data.errors).flat();
                    showToast('error', 'Doğrulama Hatası', msgs[0] || result.data.message);
                } else {
                    showToast('error', 'Hata', (result.data && result.data.message) || 'Kayıt sırasında bir hata oluştu.');
                }
            })
            .catch(function () {
                showToast('error', 'Bağlantı Hatası', 'Sunucuya ulaşılamadı. Lütfen tekrar deneyin.');
            })
            .finally(function () {
                submitBtn.disabled = false;
                submitBtn.innerHTML = submitBtnOriginalHtml;
                document.getElementById('loadingOverlay').classList.remove('visible');
            });
        });
    }

    bindIntegrationForm({
        formId: 'smsEntegrasyonForm',
        submitBtnId: 'smsSubmitBtn',
        aktifId: 'sms_aktif',
        statusPillId: 'smsStatusPill',
        authId: 'sms_authorization',
        authHintId: 'smsAuthHint',
        baseUrlId: 'sms_base_url',
        authorizationKayitli: @json($smsAuthorizationKayitli)
    });

    bindIntegrationForm({
        formId: 'kimlikEntegrasyonForm',
        submitBtnId: 'kimlikSubmitBtn',
        aktifId: 'kimlik_aktif',
        statusPillId: 'kimlikStatusPill',
        authId: 'kimlik_authorization',
        authHintId: 'kimlikAuthHint',
        baseUrlId: 'kimlik_base_url',
        authorizationKayitli: @json($kimlikAuthorizationKayitli)
    });

    bindIntegrationForm({
        formId: 'adresEntegrasyonForm',
        submitBtnId: 'adresSubmitBtn',
        aktifId: 'adres_aktif',
        statusPillId: 'adresStatusPill',
        authId: 'adres_authorization',
        authHintId: 'adresAuthHint',
        baseUrlId: 'adres_base_url',
        authorizationKayitli: @json($adresAuthorizationKayitli)
    });

    function bindSimpleIntegrationForm(opts) {
        var form = document.getElementById(opts.formId);
        var submitBtn = document.getElementById(opts.submitBtnId);
        var submitBtnOriginalHtml = submitBtn.innerHTML;
        var aktifEl = document.getElementById(opts.aktifId);
        var statusPill = document.getElementById(opts.statusPillId);

        function syncStatusPill() {
            if (aktifEl.checked) {
                statusPill.textContent = 'Aktif';
                statusPill.className = 'status-pill on';
            } else {
                statusPill.textContent = 'Pasif';
                statusPill.className = 'status-pill off';
            }
        }

        aktifEl.addEventListener('change', syncStatusPill);

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (typeof opts.validate === 'function' && !opts.validate()) {
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin 0.8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Kaydediliyor...';
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
            .then(function (res) {
                return res.json().then(function (data) { return { status: res.status, data: data }; });
            })
            .then(function (result) {
                if (result.status === 200 && result.data.success) {
                    showToast('success', 'Kaydedildi', result.data.message);
                    if (typeof opts.onSuccess === 'function') {
                        opts.onSuccess(result.data);
                    }
                    syncStatusPill();
                } else if (result.status === 422 && result.data.errors) {
                    var msgs = Object.values(result.data.errors).flat();
                    showToast('error', 'Doğrulama Hatası', msgs[0] || result.data.message);
                } else {
                    showToast('error', 'Hata', (result.data && result.data.message) || 'Kayıt sırasında bir hata oluştu.');
                }
            })
            .catch(function () {
                showToast('error', 'Bağlantı Hatası', 'Sunucuya ulaşılamadı. Lütfen tekrar deneyin.');
            })
            .finally(function () {
                submitBtn.disabled = false;
                submitBtn.innerHTML = submitBtnOriginalHtml;
                document.getElementById('loadingOverlay').classList.remove('visible');
            });
        });
    }

    bindSimpleIntegrationForm({
        formId: 'ldapEntegrasyonForm',
        submitBtnId: 'ldapSubmitBtn',
        aktifId: 'ldap_aktif',
        statusPillId: 'ldapStatusPill',
        validate: function () {
            if (!document.getElementById('ldap_host').value.trim()) {
                showToast('error', 'Zorunlu alan', 'LDAP sunucu adresi zorunludur.');
                return false;
            }
            if (!document.getElementById('ldap_base_dn').value.trim()) {
                showToast('error', 'Zorunlu alan', 'Base DN zorunludur.');
                return false;
            }
            return true;
        }
    });

    var webhookSecretKayitli = @json($webhookSecretKayitli);
    bindSimpleIntegrationForm({
        formId: 'webhookEntegrasyonForm',
        submitBtnId: 'webhookSubmitBtn',
        aktifId: 'webhook_aktif',
        statusPillId: 'webhookStatusPill',
        validate: function () {
            if (!document.getElementById('webhook_url').value.trim()) {
                showToast('error', 'Zorunlu alan', 'Webhook URL zorunludur.');
                return false;
            }
            if (!document.getElementById('webhook_secret').value.trim() && !webhookSecretKayitli) {
                showToast('error', 'Zorunlu alan', 'Webhook secret zorunludur.');
                return false;
            }
            return true;
        },
        onSuccess: function (data) {
            if (data.secret_kayitli) {
                webhookSecretKayitli = true;
                var secretInput = document.getElementById('webhook_secret');
                var hint = document.getElementById('webhookSecretHint');
                secretInput.value = '';
                secretInput.removeAttribute('required');
                secretInput.placeholder = 'Kayıtlı — değiştirmek için yeni değer girin';
                if (hint) {
                    hint.textContent = 'Güvenlik nedeniyle mevcut secret gösterilmez. Boş bırakırsanız mevcut değer korunur.';
                }
            }
        }
    });

    (function bindSmsTest() {
        var modal = document.getElementById('smsTestModal');
        var openBtn = document.getElementById('smsTestOpenBtn');
        var testBtn = document.getElementById('smsTestBtn');
        var closeBtn = document.getElementById('smsTestModalClose');
        var cancelBtn = document.getElementById('smsTestModalCancel');
        if (!modal || !openBtn || !testBtn) return;

        var testBtnOriginalHtml = testBtn.innerHTML;
        var telefonInput = document.getElementById('sms_test_telefon');
        var messageInput = document.getElementById('sms_test_message');

        function openModal() {
            modal.hidden = false;
            requestAnimationFrame(function () {
                modal.classList.add('visible');
                if (telefonInput) telefonInput.focus();
            });
        }

        function closeModal() {
            modal.classList.remove('visible');
            setTimeout(function () {
                if (!modal.classList.contains('visible')) {
                    modal.hidden = true;
                }
            }, 200);
        }

        openBtn.addEventListener('click', openModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('visible')) {
                closeModal();
            }
        });

        testBtn.addEventListener('click', function () {
            var telefon = telefonInput.value.trim();
            var message = messageInput.value.trim();

            if (!/^(0|\+90|90)?5\d{9}$/.test(telefon)) {
                showToast('error', 'Zorunlu alan', 'Geçerli bir Türkiye cep numarası girin (05xxxxxxxxx).');
                return;
            }
            if (!message) {
                showToast('error', 'Zorunlu alan', 'SMS mesajı zorunludur.');
                return;
            }

            testBtn.disabled = true;
            testBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin 0.8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Gönderiliyor...';
            document.getElementById('loadingOverlay').classList.add('visible');
            var loadingText = document.querySelector('#loadingOverlay .loading-text');
            if (loadingText) loadingText.textContent = 'SMS gönderiliyor...';

            fetch(@json(route('entegrasyon.sms.test')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ telefon: telefon, message: message })
            })
            .then(function (res) {
                return res.json().then(function (data) { return { status: res.status, data: data || {} }; });
            })
            .then(function (result) {
                if (result.status === 200 && result.data.success) {
                    showToast('success', 'Test başarılı', result.data.message || 'SMS gönderildi.');
                    closeModal();
                } else if (result.status === 422 && result.data.errors) {
                    var msgs = Object.values(result.data.errors).flat();
                    showToast('error', 'Doğrulama Hatası', msgs[0] || result.data.message);
                } else {
                    showToast('error', 'Test başarısız', (result.data && result.data.message) || 'SMS testi başarısız oldu.');
                }
            })
            .catch(function () {
                showToast('error', 'Bağlantı Hatası', 'Sunucuya ulaşılamadı. Lütfen tekrar deneyin.');
            })
            .finally(function () {
                testBtn.disabled = false;
                testBtn.innerHTML = testBtnOriginalHtml;
                document.getElementById('loadingOverlay').classList.remove('visible');
                if (loadingText) loadingText.textContent = 'Kaydediliyor...';
            });
        });
    })();

    function bindTcDogumTest(opts) {
        var modal = document.getElementById(opts.modalId);
        var openBtn = document.getElementById(opts.openBtnId);
        var testBtn = document.getElementById(opts.testBtnId);
        var closeBtn = document.getElementById(opts.closeBtnId);
        var cancelBtn = document.getElementById(opts.cancelBtnId);
        if (!modal || !openBtn || !testBtn) return;

        var testBtnOriginalHtml = testBtn.innerHTML;
        var tcInput = document.getElementById(opts.tcId);
        var dogumInput = document.getElementById(opts.dogumId);

        function openModal() {
            modal.hidden = false;
            requestAnimationFrame(function () {
                modal.classList.add('visible');
                if (tcInput) tcInput.focus();
            });
        }

        function closeModal() {
            modal.classList.remove('visible');
            setTimeout(function () {
                if (!modal.classList.contains('visible')) {
                    modal.hidden = true;
                }
            }, 200);
        }

        openBtn.addEventListener('click', openModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('visible')) {
                closeModal();
            }
        });

        testBtn.addEventListener('click', function () {
            var tc = tcInput.value.trim();
            var dogum = dogumInput.value.trim();

            if (!/^[1-9][0-9]{10}$/.test(tc)) {
                showToast('error', 'Zorunlu alan', 'Geçerli bir TC kimlik numarası girin (11 rakam).');
                return;
            }
            if (!dogum) {
                showToast('error', 'Zorunlu alan', 'Doğum tarihi zorunludur.');
                return;
            }

            testBtn.disabled = true;
            testBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin 0.8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Sorgulanıyor...';
            document.getElementById('loadingOverlay').classList.add('visible');
            var loadingText = document.querySelector('#loadingOverlay .loading-text');
            if (loadingText) loadingText.textContent = opts.loadingText || 'Sorgulanıyor...';

            fetch(opts.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ tc_kimlik: tc, dogum_tarihi: dogum })
            })
            .then(function (res) {
                return res.json().then(function (data) { return { status: res.status, data: data || {} }; });
            })
            .then(function (result) {
                if (result.status === 200 && result.data.success) {
                    showToast('success', 'Test başarılı', result.data.message || 'Sorgu başarılı.');
                    closeModal();
                } else if (result.status === 422 && result.data.errors) {
                    var msgs = Object.values(result.data.errors).flat();
                    showToast('error', 'Doğrulama Hatası', msgs[0] || result.data.message);
                } else {
                    showToast('error', 'Test başarısız', (result.data && result.data.message) || opts.failMessage || 'Sorgu başarısız oldu.');
                }
            })
            .catch(function () {
                showToast('error', 'Bağlantı Hatası', 'Sunucuya ulaşılamadı. Lütfen tekrar deneyin.');
            })
            .finally(function () {
                testBtn.disabled = false;
                testBtn.innerHTML = testBtnOriginalHtml;
                document.getElementById('loadingOverlay').classList.remove('visible');
                if (loadingText) loadingText.textContent = 'Kaydediliyor...';
            });
        });
    }

    bindTcDogumTest({
        modalId: 'kimlikTestModal',
        openBtnId: 'kimlikTestOpenBtn',
        testBtnId: 'kimlikTestBtn',
        closeBtnId: 'kimlikTestModalClose',
        cancelBtnId: 'kimlikTestModalCancel',
        tcId: 'kimlik_test_tc',
        dogumId: 'kimlik_test_dogum',
        url: @json(route('entegrasyon.kimlik.test')),
        loadingText: 'Kimlik sorgulanıyor...',
        failMessage: 'Kimlik sorgusu başarısız oldu.'
    });

    bindTcDogumTest({
        modalId: 'adresTestModal',
        openBtnId: 'adresTestOpenBtn',
        testBtnId: 'adresTestBtn',
        closeBtnId: 'adresTestModalClose',
        cancelBtnId: 'adresTestModalCancel',
        tcId: 'adres_test_tc',
        dogumId: 'adres_test_dogum',
        url: @json(route('entegrasyon.adres.test')),
        loadingText: 'Adres sorgulanıyor...',
        failMessage: 'Adres sorgusu başarısız oldu.'
    });

    (function bindLdapTest() {
        var modal = document.getElementById('ldapTestModal');
        var openBtn = document.getElementById('ldapTestOpenBtn');
        var testBtn = document.getElementById('ldapTestBtn');
        var closeBtn = document.getElementById('ldapTestModalClose');
        var cancelBtn = document.getElementById('ldapTestModalCancel');
        if (!modal || !openBtn || !testBtn) return;

        var testBtnOriginalHtml = testBtn.innerHTML;
        var usernameInput = document.getElementById('ldap_test_username');
        var passwordInput = document.getElementById('ldap_test_password');

        function openModal() {
            modal.hidden = false;
            requestAnimationFrame(function () {
                modal.classList.add('visible');
                if (usernameInput) usernameInput.focus();
            });
        }

        function closeModal() {
            modal.classList.remove('visible');
            if (passwordInput) passwordInput.value = '';
            setTimeout(function () {
                if (!modal.classList.contains('visible')) {
                    modal.hidden = true;
                }
            }, 200);
        }

        openBtn.addEventListener('click', openModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('visible')) {
                closeModal();
            }
        });

        testBtn.addEventListener('click', function () {
            var username = usernameInput.value.trim();
            var password = passwordInput.value;

            if (!username) {
                showToast('error', 'Zorunlu alan', 'LDAP kullanıcı adı zorunludur.');
                return;
            }
            if (!password) {
                showToast('error', 'Zorunlu alan', 'LDAP şifresi zorunludur.');
                return;
            }

            testBtn.disabled = true;
            testBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin 0.8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Test ediliyor...';
            document.getElementById('loadingOverlay').classList.add('visible');
            var loadingText = document.querySelector('#loadingOverlay .loading-text');
            if (loadingText) loadingText.textContent = 'LDAP test ediliyor...';

            fetch(@json(route('entegrasyon.ldap.test')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ username: username, password: password })
            })
            .then(function (res) {
                return res.json().then(function (data) { return { status: res.status, data: data || {} }; });
            })
            .then(function (result) {
                if (result.status === 200 && result.data.success) {
                    showToast('success', 'Test başarılı', result.data.message || 'LDAP bağlantısı başarılı.');
                    closeModal();
                } else if (result.status === 422 && result.data.errors) {
                    var msgs = Object.values(result.data.errors).flat();
                    showToast('error', 'Doğrulama Hatası', msgs[0] || result.data.message);
                } else {
                    showToast('error', 'Test başarısız', (result.data && result.data.message) || 'LDAP testi başarısız oldu.');
                }
            })
            .catch(function () {
                showToast('error', 'Bağlantı Hatası', 'Sunucuya ulaşılamadı. Lütfen tekrar deneyin.');
            })
            .finally(function () {
                testBtn.disabled = false;
                testBtn.innerHTML = testBtnOriginalHtml;
                document.getElementById('loadingOverlay').classList.remove('visible');
                if (loadingText) loadingText.textContent = 'Kaydediliyor...';
            });
        });
    })();

    (function bindWebhookTest() {
        var modal = document.getElementById('webhookTestModal');
        var openBtn = document.getElementById('webhookTestOpenBtn');
        var testBtn = document.getElementById('webhookTestBtn');
        var closeBtn = document.getElementById('webhookTestModalClose');
        var cancelBtn = document.getElementById('webhookTestModalCancel');
        if (!modal || !openBtn || !testBtn) return;

        var testBtnOriginalHtml = testBtn.innerHTML;
        var tcInput = document.getElementById('webhook_test_tc');
        var titleInput = document.getElementById('webhook_test_title');
        var messageInput = document.getElementById('webhook_test_message');

        function openModal() {
            modal.hidden = false;
            requestAnimationFrame(function () {
                modal.classList.add('visible');
                if (tcInput) tcInput.focus();
            });
        }

        function closeModal() {
            modal.classList.remove('visible');
            setTimeout(function () {
                if (!modal.classList.contains('visible')) {
                    modal.hidden = true;
                }
            }, 200);
        }

        openBtn.addEventListener('click', openModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('visible')) {
                closeModal();
            }
        });

        testBtn.addEventListener('click', function () {
            var tc = tcInput.value.trim();
            var title = titleInput.value.trim();
            var message = messageInput.value.trim();

            if (!/^[1-9][0-9]{10}$/.test(tc)) {
                showToast('error', 'Zorunlu alan', 'Geçerli bir TC kimlik numarası girin (11 rakam).');
                return;
            }
            if (!title) {
                showToast('error', 'Zorunlu alan', 'Bildirim başlığı zorunludur.');
                return;
            }
            if (!message) {
                showToast('error', 'Zorunlu alan', 'Bildirim mesajı zorunludur.');
                return;
            }

            testBtn.disabled = true;
            testBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin 0.8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Gönderiliyor...';
            document.getElementById('loadingOverlay').classList.add('visible');
            var loadingText = document.querySelector('#loadingOverlay .loading-text');
            if (loadingText) loadingText.textContent = 'Test gönderiliyor...';

            fetch(@json(route('entegrasyon.webhook.test')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ tc: tc, title: title, message: message })
            })
            .then(function (res) {
                return res.json().then(function (data) { return { status: res.status, data: data || {} }; });
            })
            .then(function (result) {
                if (result.status === 200 && result.data.success) {
                    showToast('success', 'Test başarılı', result.data.message || 'Bildirim gönderildi.');
                    closeModal();
                } else if (result.status === 422 && result.data.errors) {
                    var msgs = Object.values(result.data.errors).flat();
                    showToast('error', 'Doğrulama Hatası', msgs[0] || result.data.message);
                } else {
                    showToast('error', 'Test başarısız', (result.data && result.data.message) || 'Webhook testi başarısız oldu.');
                }
            })
            .catch(function () {
                showToast('error', 'Bağlantı Hatası', 'Sunucuya ulaşılamadı. Lütfen tekrar deneyin.');
            })
            .finally(function () {
                testBtn.disabled = false;
                testBtn.innerHTML = testBtnOriginalHtml;
                document.getElementById('loadingOverlay').classList.remove('visible');
                if (loadingText) loadingText.textContent = 'Kaydediliyor...';
            });
        });
    })();

    @if(session('success'))
        showToast('success', 'Kaydedildi', @json(session('success')));
    @endif
})();
</script>
@endsection
