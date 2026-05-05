@extends('layouts.base')

@section('title', 'Uye Duzenle')

@section('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <style>
        /* Form Card */
        .form-card{border:1px solid rgba(217,208,194,.6);border-radius:var(--radius);background:var(--card);box-shadow:0 1px 3px rgba(0,0,0,.04);max-width:720px}
        .form-card-header{padding:24px 24px 16px}
        .form-card-title{display:flex;align-items:center;gap:10px;font-family:var(--font-serif);font-size:20px;font-weight:700}
        .form-card-title .title-icon{width:38px;height:38px;border-radius:10px;background:rgba(122,92,60,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .form-card-title .title-icon svg{width:20px;height:20px;color:var(--primary)}
        .form-card-desc{font-size:14px;color:var(--muted-foreground);margin-top:4px;margin-left:48px}
        .form-card-separator{height:1px;background:var(--border)}
        .form-card-body{padding:24px;display:flex;flex-direction:column;gap:24px}

        /* Section */
        .section-label{display:flex;align-items:center;gap:8px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted-foreground);margin-bottom:14px}
        .section-num{width:20px;height:20px;border-radius:4px;background:rgba(122,92,60,.1);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:var(--primary)}
        .section-sep{height:1px;background:var(--border)}

        /* Form Grid */
        .form-grid{display:grid;gap:14px}
        .form-grid.cols-2{grid-template-columns:repeat(2,1fr)}
        .form-grid.cols-3{grid-template-columns:repeat(3,1fr)}
        .span-2{grid-column:span 2}.span-3{grid-column:span 3}
        .form-field{display:flex;flex-direction:column}
        .form-label{font-size:14px;font-weight:500;color:var(--foreground);margin-bottom:6px}
        .form-label .req{color:var(--destructive)}
        .form-label .hint{font-weight:400;color:var(--muted-foreground);font-size:12px;margin-left:4px}
        .input-wrap{position:relative}
        .input-icon{position:absolute;left:11px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:var(--muted-foreground);pointer-events:none}
        .form-input,.form-select{width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);color:var(--foreground);font-size:14px;line-height:1.5;transition:border-color .15s,box-shadow .15s;outline:none}
        .form-input.has-icon{padding-left:34px}
        .form-input::placeholder{color:var(--muted-foreground);opacity:.7}
        .form-input:focus,.form-select:focus{border-color:var(--ring);box-shadow:0 0 0 2px rgba(122,92,60,.15)}
        .form-input.is-error,.form-select.is-error{border-color:var(--destructive)}
        .form-textarea{width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);color:var(--foreground);font-size:14px;line-height:1.5;transition:border-color .15s,box-shadow .15s;outline:none;resize:vertical;min-height:80px}
        .form-textarea:focus{border-color:var(--ring);box-shadow:0 0 0 2px rgba(122,92,60,.15)}
        /* Adres Sorgu Butonu */
        .adres-sorgu-btn{width:22px;height:22px;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--muted-foreground);border-radius:4px;padding:0;flex-shrink:0;transition:color .15s,background .15s}
        .adres-sorgu-btn:hover{color:var(--primary);background:rgba(122,92,60,.08)}
        .adres-sorgu-btn:active{background:rgba(122,92,60,.15)}
        .adres-sorgu-btn:disabled{pointer-events:none;opacity:.5}
        .adres-sorgu-btn svg{width:14px;height:14px;display:block}
        /* Adres Sonuç Paneli */
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
        .form-select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237a7060' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:36px}
        .form-error{font-size:12px;color:var(--destructive);margin-top:4px;display:flex;align-items:center;gap:4px}
        .form-error svg{width:12px;height:12px;flex-shrink:0}
        .form-hint-text{font-size:12px;color:var(--muted-foreground);margin-top:4px}

        /* OTP Box */
        .otp-box{border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--secondary);padding:16px;display:flex;flex-direction:column;gap:12px}
        .otp-status{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500}
        .otp-status.pending{color:var(--muted-foreground)}
        .otp-status.verified{color:#166534}
        .otp-status svg{width:16px;height:16px;flex-shrink:0}
        .otp-row{display:flex;gap:8px;align-items:flex-end}
        .otp-code-input{width:140px;padding:8px 12px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);font-size:18px;font-family:monospace;letter-spacing:.2em;text-align:center;outline:none;transition:border-color .15s,box-shadow .15s}
        .otp-code-input:focus{border-color:var(--ring);box-shadow:0 0 0 2px rgba(122,92,60,.15)}
        .countdown{font-size:13px;color:var(--muted-foreground);margin-left:4px}
        .countdown.urgent{color:var(--destructive)}
        .verified-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:999px;background:rgba(34,197,94,0.12);color:#166534;font-size:13px;font-weight:600}
        .verified-badge svg{width:14px;height:14px}

        /* Phone warning */
        .phone-warning{padding:10px 14px;background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.3);border-radius:calc(var(--radius) - 2px);font-size:13px;color:#92400e;display:flex;align-items:flex-start;gap:8px}
        .phone-warning svg{width:15px;height:15px;flex-shrink:0;margin-top:1px}

        /* Veli Bilgileri uyarı kutusu */
        .veli-alert{padding:12px 16px;background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.35);border-radius:calc(var(--radius) - 2px);font-size:13px;color:#92400e;display:flex;align-items:flex-start;gap:10px;margin-bottom:16px}
        .veli-alert svg{width:16px;height:16px;flex-shrink:0;margin-top:1px}
        .veli-section-num{width:20px;height:20px;border-radius:4px;background:rgba(245,158,11,0.15);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#92400e}

        /* Statu cards */
        .statu-cards{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}
        .statu-card{position:relative;cursor:pointer}
        .statu-card input[type=radio]{position:absolute;opacity:0;width:0;height:0}
        .statu-card-inner{padding:14px;border:1.5px solid var(--border);border-radius:calc(var(--radius) - 2px);transition:border-color .15s,background .15s;display:flex;flex-direction:column;gap:5px;user-select:none}
        .statu-card input:checked ~ .statu-card-inner{border-color:var(--primary);background:rgba(122,92,60,.05)}
        .statu-card-name{font-size:13px;font-weight:600}
        .statu-card-desc{font-size:11px;color:var(--muted-foreground)}
        .statu-check{position:absolute;top:10px;right:10px;width:16px;height:16px;border-radius:50%;border:1.5px solid var(--border);background:var(--card);display:flex;align-items:center;justify-content:center;transition:background .15s,border-color .15s}
        .statu-card input:checked ~ .statu-check{background:var(--primary);border-color:var(--primary)}
        .statu-check svg{width:9px;height:9px;color:white;opacity:0;transition:opacity .15s}
        .statu-card input:checked ~ .statu-check svg{opacity:1}

        /* Actions */
        .form-actions{display:flex;justify-content:flex-end;gap:12px;padding-top:4px}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:9px 16px;border-radius:calc(var(--radius) - 2px);font-size:14px;font-weight:500;cursor:pointer;transition:background .15s,opacity .15s;border:none;text-decoration:none}
        .btn svg{width:16px;height:16px}
        .btn-primary{background:var(--primary);color:var(--primary-foreground)}
        .btn-primary:hover{opacity:.9}
        .btn-primary:disabled{opacity:.6;cursor:not-allowed}
        .btn-outline{background:transparent;color:var(--foreground);border:1px solid var(--border)}
        .btn-outline:hover{background:var(--muted)}
        .btn-sm{padding:7px 13px;font-size:13px}
        .btn-ghost{background:rgba(122,92,60,.08);color:var(--primary);border:1px solid rgba(122,92,60,.2)}
        .btn-ghost:hover{background:rgba(122,92,60,.14)}

        /* Toast */
        .toast-container{position:fixed;top:20px;right:20px;z-index:3000;display:flex;flex-direction:column;gap:10px}
        .toast{padding:14px 18px;border-radius:var(--radius);font-size:14px;font-weight:500;min-width:280px;box-shadow:0 4px 16px rgba(0,0,0,.12);border:1px solid transparent;animation:toast-in .3s ease}
        .toast.success{background:#f0fdf4;border-color:#bbf7d0;color:#166534}
        .toast.error{background:#fef2f2;border-color:#fecaca;color:#991b1b}
        .toast.info{background:#eff6ff;border-color:#bfdbfe;color:#1e40af}
        .toast-desc{font-size:13px;opacity:.8;margin-top:2px}
        @keyframes toast-in{from{opacity:0;transform:translateX(20px)}to{opacity:1;transform:translateX(0)}}
        @keyframes toast-out{from{opacity:1;transform:translateX(0)}to{opacity:0;transform:translateX(20px)}}

        /* Mobile */
        @media(max-width:768px){
            .form-grid.cols-2,.form-grid.cols-3{grid-template-columns:1fr}
            .span-2,.span-3{grid-column:span 1}
            .statu-cards{grid-template-columns:1fr}
        }
    </style>
@endsection

@section('breadcrumb')
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('uyeler.index') }}" class="breadcrumb-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Üyeler
        </a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">{{ $uye->ad }} {{ $uye->soyad }}</span>
    </nav>
@endsection

@section('content')
        <div class="content-area">
            <form method="POST" action="{{ route('uyeler.update', $uye) }}" id="uyeForm">
                @csrf
                @method('PUT')
                <div class="form-card">
                    <div class="form-card-header">
                        <div class="form-card-title">
                            <div class="title-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            </div>
                            Üye Düzenle
                        </div>
                        <p class="form-card-desc">{{ $uye->ad }} {{ $uye->soyad }} — TC: {{ $uye->tc_kimlik }}</p>
                    </div>
                    <div class="form-card-separator"></div>
                    <div class="form-card-body">

                        {{-- 1. Kimlik --}}
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
                                    <input disabled type="text" id="tc_kimlik" name="tc_kimlik"
                                           class="form-input {{ $errors->has('tc_kimlik') ? 'is-error' : '' }}"
                                           value="{{ old('tc_kimlik', $uye->tc_kimlik) }}"
                                           maxlength="11" inputmode="numeric" pattern="\d{11}" autocomplete="off" />
                                    @error('tc_kimlik') <div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div> @enderror
                                </div>
                                <div class="form-field">
                                    <label class="form-label" for="dogum_tarihi">Doğum Tarihi <span class="req">*</span></label>
                                    <input disabled type="date" id="dogum_tarihi" name="dogum_tarihi"
                                           class="form-input {{ $errors->has('dogum_tarihi') ? 'is-error' : '' }}"
                                           value="{{ old('dogum_tarihi', $uye->dogum_tarihi?->format('Y-m-d')) }}"
                                           max="{{ date('Y-m-d') }}" />
                                    @error('dogum_tarihi') <div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="section-sep"></div>

                        {{-- 2. Kişisel --}}
                        <div>
                            <div class="section-label"><span class="section-num">2</span> Kişisel Bilgiler</div>
                            <div class="form-grid cols-2">
                                <div class="form-field">
                                    <label class="form-label" for="ad">Ad <span class="req">*</span></label>
                                    <input disabled type="text" id="ad" name="ad"
                                           class="form-input {{ $errors->has('ad') ? 'is-error' : '' }}"
                                           value="{{ old('ad', $uye->ad) }}" />
                                    @error('ad') <div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div> @enderror
                                </div>
                                <div class="form-field">
                                    <label class="form-label" for="soyad">Soyad <span class="req">*</span></label>
                                    <input disabled type="text" id="soyad" name="soyad"
                                           class="form-input {{ $errors->has('soyad') ? 'is-error' : '' }}"
                                           value="{{ old('soyad', $uye->soyad) }}" />
                                    @error('soyad') <div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div> @enderror
                                </div>
                                <div class="form-field">
                                    <label class="form-label" for="cinsiyet">Cinsiyet <span class="hint">(isteğe bağlı)</span></label>
                                    <select id="cinsiyet" name="cinsiyet" class="form-input {{ $errors->has('cinsiyet') ? 'is-error' : '' }}">
                                        <option value="" {{ old('cinsiyet', $uye->cinsiyet) === null || old('cinsiyet', $uye->cinsiyet) === '' ? 'selected' : '' }}>Belirtilmedi</option>
                                        <option value="erkek" {{ old('cinsiyet', $uye->cinsiyet) === 'erkek' ? 'selected' : '' }}>Erkek</option>
                                        <option value="kadin" {{ old('cinsiyet', $uye->cinsiyet) === 'kadin' ? 'selected' : '' }}>Kadın</option>
                                        <option value="diger" {{ old('cinsiyet', $uye->cinsiyet) === 'diger' ? 'selected' : '' }}>Diğer</option>
                                    </select>
                                    @error('cinsiyet') <div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div> @enderror
                                </div>
                                <div class="form-field">
                                    <label class="form-label" for="email">E-posta <span class="hint">(isteğe bağlı)</span></label>
                                    <div class="input-wrap">
                                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                        <input type="email" id="email" name="email"
                                               class="form-input has-icon {{ $errors->has('email') ? 'is-error' : '' }}"
                                               value="{{ old('email', $uye->email) }}" />
                                    </div>
                                    @error('email') <div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div> @enderror
                                </div>

                                {{-- Telefon --}}
                                <div class="form-field">
                                    <label class="form-label" for="telefon">
                                        Telefon <span class="req">*</span>
                                        @if($uye->telefon_dogrulandi)
                                            <span style="display:inline-flex;align-items:center;gap:3px;font-size:11px;font-weight:600;color:#16a34a;margin-left:6px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                                Doğrulanmış
                                            </span>
                                        @endif
                                    </label>
                                    <div style="display:flex;gap:8px;">
                                        <div class="input-wrap" style="flex:1;position:relative;">
                                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.18 2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.68 2.81a2 2 0 0 1-.45 2.11L7.91 8.56a16 16 0 0 0 8 8l.92-.92a2 2 0 0 1 2.11-.45c.91.32 1.85.55 2.81.68A2 2 0 0 1 22 16.92z"/></svg>
                                            <input type="tel" id="telefon" name="telefon"
                                                   class="form-input has-icon {{ $errors->has('telefon') ? 'is-error' : '' }}"
                                                   value="{{ old('telefon', $uye->telefon) }}"
                                                   maxlength="11" inputmode="tel"
                                                   oninput="onPhoneChange()" />
                                        </div>
                                        <button type="button" id="btnSendOtp" class="btn btn-ghost btn-sm" style="white-space:nowrap;display:none;" onclick="sendOtp()">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                                            Kod Gönder
                                        </button>
                                    </div>
                                    @error('telefon') <div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div> @enderror
                                </div>

                                {{-- OTP Uyarı & Kutu --}}
                                <div class="span-2" id="otpSection" style="display:none;">
                                    <div class="phone-warning" style="margin-bottom:10px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                                        Telefon numarası değiştirildi. Yeni numara SMS ile doğrulanmalıdır.
                                    </div>
                                    <div class="otp-box">
                                        <div class="otp-status pending" id="otpStatus">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                            <span id="otpStatusText">Kod göndermek için "Kod Gönder" butonuna tıklayın.</span>
                                        </div>
                                        <div class="otp-row" id="otpInputRow" style="display:none;">
                                            <input type="text" id="otpCode" class="otp-code-input" maxlength="6" placeholder="——————" inputmode="numeric" autocomplete="one-time-code" />
                                            <button type="button" class="btn btn-primary btn-sm" onclick="verifyOtp()">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                                Doğrula
                                            </button>
                                            <span class="countdown" id="otpCountdown"></span>
                                        </div>
                                        <div id="otpVerifiedRow" style="display:none;">
                                            <span class="verified-badge">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                                Yeni numara doğrulandı
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- 2. Telefon --}}
                                <div class="form-field">
                                    <label class="form-label" for="telefon2">2. Telefon <span class="hint">(isteğe bağlı)</span></label>
                                    <div class="input-wrap">
                                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.18 2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.68 2.81a2 2 0 0 1-.45 2.11L7.91 8.56a16 16 0 0 0 8 8l.92-.92a2 2 0 0 1 2.11-.45c.91.32 1.85.55 2.81.68A2 2 0 0 1 22 16.92z"/></svg>
                                        <input type="tel" id="telefon2" name="telefon2"
                                               class="form-input has-icon {{ $errors->has('telefon2') ? 'is-error' : '' }}"
                                               value="{{ old('telefon2', $uye->telefon2) }}"
                                               maxlength="11" inputmode="tel" />
                                    </div>
                                    @error('telefon2') <div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>



                        <div class="section-sep"></div>

                        {{-- 3. Adres --}}
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

                            {{-- Adres Sorgu Sonuç Paneli --}}
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
                                    <input type="text" id="il" name="il" class="form-input"
                                           value="{{ old('il', $uye->il) }}" placeholder="İl adı (adres sorgulamayla KPS'den gelir)" autocomplete="address-level1" />
                                </div>
                                <div class="form-field">
                                    <label class="form-label" for="ilce">İlçe</label>
                                    <input type="text" id="ilce" name="ilce"
                                           class="form-input"
                                           value="{{ old('ilce', $uye->ilce) }}" placeholder="İlçe adı" />
                                </div>
                                <div class="form-field">
                                    <label class="form-label" for="mahalle">Mahalle</label>
                                    <input type="text" id="mahalle" name="mahalle"
                                           class="form-input"
                                           value="{{ old('mahalle', $uye->mahalle) }}" placeholder="Mahalle adı" />
                                </div>
                                <div class="form-field span-3">
                                    <label class="form-label" for="acik_adres">Açık Adres</label>
                                    <textarea id="acik_adres" name="acik_adres" class="form-textarea">{{ old('acik_adres', $uye->acik_adres) }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="section-sep"></div>

                        {{-- 4. Eğitim Bilgileri --}}
                        <div>
                            <div class="section-label"><span class="section-num">4</span> Eğitim Bilgileri</div>
                            <div class="form-grid cols-2">
                                <div class="form-field span-2">
                                    <label class="form-label" for="ogretim_durumu">Öğrenim Durumu <span class="hint">(isteğe bağlı)</span></label>
                                    <select id="ogretim_durumu" name="ogretim_durumu"
                                            class="form-select {{ $errors->has('ogretim_durumu') ? 'is-error' : '' }}">
                                        <option value="">— Seçiniz —</option>
                                        @foreach(['İlkokul','Ortaokul','Lise','Önlisans','Lisans','Yüksek Lisans','Doktora'] as $seviye)
                                            <option value="{{ $seviye }}" {{ old('ogretim_durumu', $uye->ogretim_durumu) === $seviye ? 'selected' : '' }}>{{ $seviye }}</option>
                                        @endforeach
                                    </select>
                                    @error('ogretim_durumu') <div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div> @enderror
                                </div>
                                <div class="form-field">
                                    <label class="form-label" for="okul_adi">Okul Adı <span class="hint">(isteğe bağlı)</span></label>
                                    <input type="text" id="okul_adi" name="okul_adi"
                                           class="form-input {{ $errors->has('okul_adi') ? 'is-error' : '' }}"
                                           value="{{ old('okul_adi', $uye->okul_adi) }}" placeholder="Okul adını girin" />
                                    @error('okul_adi') <div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div> @enderror
                                </div>
                                <div class="form-field">
                                    <label class="form-label" for="bolum_adi">Bölüm Adı <span class="hint">(isteğe bağlı)</span></label>
                                    <input type="text" id="bolum_adi" name="bolum_adi"
                                           class="form-input {{ $errors->has('bolum_adi') ? 'is-error' : '' }}"
                                           value="{{ old('bolum_adi', $uye->bolum_adi) }}" placeholder="Bölüm adını girin" />
                                    @error('bolum_adi') <div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="section-sep"></div>

                        {{-- 5. Üyelik --}}
                        <div>
                            <div class="section-label"><span class="section-num">5</span> Üyelik Bilgileri</div>
                            <div class="form-grid cols-2">
                                <div class="form-field">
                                    <label class="form-label" for="uyelik_baslangic">Üyelik Başlangıç</label>
                                    <input type="date" id="uyelik_baslangic" name="uyelik_baslangic"
                                           class="form-input"
                                           value="{{ old('uyelik_baslangic', $uye->uyelik_baslangic?->format('Y-m-d')) }}" />
                                </div>
                                <div class="form-field">
                                    <label class="form-label" for="uyelik_bitis">Üyelik Bitiş <span class="hint">(isteğe bağlı)</span></label>
                                    <input type="date" id="uyelik_bitis" name="uyelik_bitis"
                                           class="form-input {{ $errors->has('uyelik_bitis') ? 'is-error' : '' }}"
                                           value="{{ old('uyelik_bitis', $uye->uyelik_bitis?->format('Y-m-d')) }}" />
                                    @error('uyelik_bitis') <div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div> @enderror
                                </div>
                                <div class="form-field span-2">
                                    <label class="form-label">Üyelik Durumu <span class="req">*</span></label>
                                    <div class="statu-cards">
                                        <label class="statu-card">
                                            <input type="radio" name="statu" value="aktif" {{ old('statu', $uye->statu) === 'aktif' ? 'checked' : '' }} />
                                            <div class="statu-card-inner">
                                                <div class="statu-card-name" style="color:#166534;">✓ Aktif Üye</div>
                                                <div class="statu-card-desc">Üye kitap ödünç alabilir.</div>
                                            </div>
                                            <div class="statu-check"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                                        </label>
                                        <label class="statu-card">
                                            <input type="radio" name="statu" value="pasif" {{ old('statu', $uye->statu) === 'pasif' ? 'checked' : '' }} />
                                            <div class="statu-card-inner">
                                                <div class="statu-card-name" style="color:#4b5563;">✕ Pasif Üye</div>
                                                <div class="statu-card-desc">Üye geçici olarak askıya alınmış.</div>
                                            </div>
                                            <div class="statu-check"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-field span-2">
                                    <label class="form-label" for="notlar">Notlar <span class="hint">(isteğe bağlı)</span></label>
                                    <textarea id="notlar" name="notlar" class="form-textarea">{{ old('notlar', $uye->notlar) }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- ── 5. Veli Bilgileri (18 yaş altı üyeler için) ── --}}
                        @if($isMinor)
                            <div id="veliSection">
                                <div class="section-sep"></div>
                                <div style="margin-top:24px;">
                                    <div class="section-label" style="justify-content:space-between;">
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <span class="section-num veli-section-num">6</span>
                                            Veli Bilgileri
                                            <span style="font-size:11px;font-weight:600;color:#92400e;background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.3);padding:2px 8px;border-radius:999px;">18 Yaş Altı — Zorunlu</span>
                                        </div>
                                        <button type="button" id="veliKimlikSorgulaBtn"
                                                class="adres-sorgu-btn"
                                                title="KPS üzerinden veli kimliğini sorgula"
                                                aria-label="Veli kimlik sorgula"
                                                onclick="veliKimlikSorgula()">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                        </button>
                                    </div>
                                    <div class="veli-alert">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                                        <div>Üye 18 yaşından küçük olduğu için <strong>veli bilgileri zorunludur.</strong> Tüm alanları eksiksiz doldurun.</div>
                                    </div>
                                    <div class="form-grid cols-2">
                                        <div class="form-field">
                                            <label class="form-label" for="veli_tc_kimlik">Veli TC Kimlik No <span class="req">*</span></label>
                                            <input type="text" id="veli_tc_kimlik" name="veli_tc_kimlik" required
                                                   class="form-input {{ $errors->has('veli_tc_kimlik') ? 'is-error' : '' }}"
                                                   value="{{ old('veli_tc_kimlik', $uye->veli_tc_kimlik) }}" placeholder="00000000000"
                                                   maxlength="11" inputmode="numeric" pattern="\d{11}" autocomplete="off" />
                                            @error('veli_tc_kimlik') <div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div> @enderror
                                        </div>
                                        <div class="form-field">
                                            <label class="form-label" for="veli_dogum_tarihi">Veli Doğum Tarihi <span class="req">*</span></label>
                                            <input type="date" id="veli_dogum_tarihi" name="veli_dogum_tarihi" required
                                                   class="form-input {{ $errors->has('veli_dogum_tarihi') ? 'is-error' : '' }}"
                                                   value="{{ old('veli_dogum_tarihi', $uye->veli_dogum_tarihi?->format('Y-m-d')) }}"
                                                   max="{{ date('Y-m-d') }}" />
                                            @error('veli_dogum_tarihi') <div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div> @enderror
                                        </div>
                                        <div class="form-field">
                                            <label class="form-label" for="veli_ad">Veli Adı <span class="req">*</span></label>
                                            <input type="text" id="veli_ad" name="veli_ad" required
                                                   class="form-input {{ $errors->has('veli_ad') ? 'is-error' : '' }}"
                                                   value="{{ old('veli_ad', $uye->veli_ad) }}" placeholder="Velinin adı" autocomplete="off" />
                                            @error('veli_ad') <div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div> @enderror
                                        </div>
                                        <div class="form-field">
                                            <label class="form-label" for="veli_soyad">Veli Soyadı <span class="req">*</span></label>
                                            <input type="text" id="veli_soyad" name="veli_soyad" required
                                                   class="form-input {{ $errors->has('veli_soyad') ? 'is-error' : '' }}"
                                                   value="{{ old('veli_soyad', $uye->veli_soyad) }}" placeholder="Velinin soyadı" autocomplete="off" />
                                            @error('veli_soyad') <div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div> @enderror
                                        </div>

                                        <div class="form-field">
                                            <label class="form-label" for="veli_telefon">Veli Telefon <span class="req">*</span></label>
                                            <div class="input-wrap">
                                                <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.18 2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.68 2.81a2 2 0 0 1-.45 2.11L7.91 8.56a16 16 0 0 0 8 8l.92-.92a2 2 0 0 1 2.11-.45c.91.32 1.85.55 2.81.68A2 2 0 0 1 22 16.92z"/></svg>
                                                <input type="tel" id="veli_telefon" name="veli_telefon" required
                                                       class="form-input has-icon {{ $errors->has('veli_telefon') ? 'is-error' : '' }}"
                                                       value="{{ old('veli_telefon', $uye->veli_telefon) }}" placeholder="05xxxxxxxxx"
                                                       maxlength="11" inputmode="tel" />
                                            </div>
                                            @error('veli_telefon') <div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="form-actions">
                            <a href="{{ route('uyeler.index') }}" class="btn btn-outline">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                                Geri
                            </a>
                            <button type="button" onclick="updateIdentity()" id="btnIdentity" class="btn btn-secondary" style="background-color: #5c7a7a; color: white;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                Kimlik Güncelle
                            </button>
                            <button type="button" id="btnSubmit" class="btn btn-primary" onclick="submitForm()">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v13a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                Değişiklikleri Kaydet
                            </button>
                        </div>

                        {{-- Kaydeden & Güncelleyen Bilgileri (kitap edit ile aynı düzen) --}}
                        <div style="margin-top:20px; padding-top:16px; border-top:1px solid var(--border); display:flex; flex-direction:column; gap:6px;">
                            <div style="font-size:13px; color:var(--muted-foreground);">
                                <span style="font-weight:600; color:var(--foreground);">Kaydeden:</span>
                                {{ $createdUser ? $createdUser->name : ($uye->created_user ? '#'.$uye->created_user : '—') }}
                                @if($uye->created_at)
                                    <span style="opacity:0.6; margin-left:6px;">{{ \Carbon\Carbon::parse($uye->created_at)->format('d.m.Y H:i') }}</span>
                                @endif
                            </div>
                            <div style="font-size:13px; color:var(--muted-foreground);">
                                <span style="font-weight:600; color:var(--foreground);">Son Güncelleyen:</span>
                                {{ $updatedUser ? $updatedUser->name : ($uye->updated_user ? '#'.$uye->updated_user : '—') }}
                                @if($uye->updated_at && $uye->updated_at != $uye->created_at)
                                    <span style="opacity:0.6; margin-left:6px;">{{ \Carbon\Carbon::parse($uye->updated_at)->format('d.m.Y H:i') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
<div class="toast-container" id="toastContainer"></div>
@endsection

@section('scripts')
<script>
    // Toast
    function showToast(type, title, desc) {
        var c = document.getElementById('toastContainer');
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<div>' + title + '</div>' + (desc ? '<div class="toast-desc">' + desc + '</div>' : '');
        c.appendChild(t);
        setTimeout(function() { t.style.animation = 'toast-out 0.3s ease forwards'; setTimeout(function() { if(t.parentNode) t.parentNode.removeChild(t); }, 300); }, 5000);
    }

    @if(session('success')) showToast('success', '{{ session('success') }}'); @endif
    @if($errors->any())     showToast('error', 'Form hatası', 'Lütfen alanları kontrol edin.'); @endif

    // OTP State
    var ORIGINAL_PHONE = '{{ $uye->telefon }}';
    var phoneChanged = false;
    var otpVerified  = false;
    var countdownInterval = null;

    function onPhoneChange() {
        var cur = document.getElementById('telefon').value.trim();
        var changed = cur !== ORIGINAL_PHONE;
        if (changed !== phoneChanged) {
            phoneChanged = changed;
            document.getElementById('btnSendOtp').style.display = changed ? 'inline-flex' : 'none';
            document.getElementById('otpSection').style.display = changed ? 'block' : 'none';
            if (!changed) { otpVerified = false; clearInterval(countdownInterval); }
        }
    }

    function sendOtp() {
        var telefon = document.getElementById('telefon').value.trim();
        var btn = document.getElementById('btnSendOtp');
        btn.disabled = true;
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin .6s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Gönderiliyor…';

        fetch('{{ route('otp.gonder') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify({ telefon: telefon })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('otpInputRow').style.display = 'flex';
                    document.getElementById('otpVerifiedRow').style.display = 'none';
                    document.getElementById('otpStatusText').textContent = data.message;
                    showToast('info', 'Kod gönderildi', data.message);
                    startCountdown(data.ttl || 300);
                    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg> Tekrar Gönder';
                    setTimeout(() => { btn.disabled = false; }, 30000);
                } else {
                    showToast('error', 'Hata', data.message);
                    btn.disabled = false;
                    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg> Kod Gönder';
                }
            })
            .catch(() => { showToast('error', 'Bağlantı hatası'); btn.disabled = false; });
    }

    function verifyOtp() {
        var telefon = document.getElementById('telefon').value.trim();
        var kod     = document.getElementById('otpCode').value.trim();
        if (!kod || kod.length !== 6) { showToast('error', 'Kod giriniz', '6 haneli kodu girin.'); return; }

        fetch('{{ route('otp.dogrula') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify({ telefon: telefon, kod: kod })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    otpVerified = true;
                    clearInterval(countdownInterval);
                    document.getElementById('otpStatus').className = 'otp-status verified';
                    document.getElementById('otpStatusText').textContent = data.message;
                    document.getElementById('otpInputRow').style.display = 'none';
                    document.getElementById('otpVerifiedRow').style.display = 'block';
                    document.getElementById('otpCountdown').textContent = '';
                    showToast('success', 'Telefon doğrulandı!');
                } else {
                    showToast('error', 'Hatalı kod', data.message);
                    document.getElementById('otpCode').value = '';
                    document.getElementById('otpCode').focus();
                }
            })
            .catch(() => showToast('error', 'Bağlantı hatası'));
    }

    function startCountdown(seconds) {
        clearInterval(countdownInterval);
        var el = document.getElementById('otpCountdown');
        var remaining = seconds;
        function tick() {
            var m = Math.floor(remaining / 60);
            var s = remaining % 60;
            el.textContent = m + ':' + (s < 10 ? '0' : '') + s;
            el.className = 'countdown' + (remaining <= 30 ? ' urgent' : '');
            if (remaining-- === 0) clearInterval(countdownInterval);
        }
        tick();
        countdownInterval = setInterval(tick, 1000);
    }

    document.getElementById('otpCode').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); verifyOtp(); }
    });
    document.getElementById('otpCode').addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 6);
    });

    function submitForm() {
        if (phoneChanged && !otpVerified) {
            showToast('error', 'Telefon doğrulanmamış', 'Yeni telefon numarası SMS ile doğrulanmalıdır.');
            return;
        }
        document.getElementById('uyeForm').submit();
    }

    document.getElementById('tc_kimlik').addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 11);
    });

    // Veli TC kimlik sadece rakam (sadece veli bölümü görünürse element var olacak)
    @if($isMinor)
    document.getElementById('veli_tc_kimlik').addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 11);
    });
    @endif

    var style = document.createElement('style');
    style.textContent = '@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}';
    document.head.appendChild(style);

    function updateIdentity() {
        const btn = document.getElementById('btnIdentity');
        const originalHtml = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin .6s linear infinite; margin-right:8px;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Sorgulanıyor...';

        fetch('{{ route("uyeler.kimlikGuncelle", $uye->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('ad').value = data.data.ad;
                    document.getElementById('soyad').value = data.data.soyad;
                    var cinsiyetEl = document.getElementById('cinsiyet');
                    if (cinsiyetEl && typeof data.data.cinsiyet !== 'undefined') {
                        cinsiyetEl.value = data.data.cinsiyet || '';
                        cinsiyetEl.dispatchEvent(new Event('change'));
                    }
                    showToast('success', 'Güncellendi', data.message);
                } else {
                    showToast('error', 'Hata', data.message);
                }
            })
            .catch(error => {
                showToast('error', 'Sistem Hatası', 'İşlem sırasında bir hata oluştu.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
    }

    // ── Kimlik Sorgula ────────────────────────────────────────────────────────────
    function kimlikSorgula() {
        // edit formunda tc_kimlik ve dogum_tarihi disabled — value yine okunur
        var tcKimlik    = document.getElementById('tc_kimlik')?.value?.trim()    || '';
        var dogumTarihi = document.getElementById('dogum_tarihi')?.value?.trim() || '';

        if (!tcKimlik || tcKimlik.length !== 11) {
            showToast('error', 'TC Kimlik gerekli', 'TC Kimlik No bilgisi okunamadı.');
            return;
        }
        if (!dogumTarihi) {
            showToast('error', 'Doğum Tarihi gerekli', 'Doğum Tarihi bilgisi okunamadı.');
            return;
        }

        var btn     = document.getElementById('kimlikSorgulaBtn');
        var adEl    = document.getElementById('ad');
        var soyadEl = document.getElementById('soyad');
        var cinsiyetEl = document.getElementById('cinsiyet');

        btn.disabled = true;
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="adres-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;display:block"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';

        var csrfToken = document.querySelector('meta[name=csrf-token]')?.content || '';

        fetch('{{ route("kps.kimlikSorgula") }}', {
            method: 'POST',
            headers: {
                'Content-Type'     : 'application/json',
                'X-CSRF-TOKEN'     : csrfToken,
                'X-Requested-With' : 'XMLHttpRequest',
            },
            body: JSON.stringify({ tc_kimlik: tcKimlik, dogum_tarihi: dogumTarihi }),
        })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                btn.disabled = false;
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;display:block"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';

                if (!data.success) {
                    showToast('error', 'Kimlik Sorgu Hatası', data.message || 'Kimlik doğrulaması başarısız.');
                    return;
                }

                if (adEl)    { adEl.value    = data.ad    || ''; adEl.dispatchEvent(new Event('input')); }
                if (soyadEl) { soyadEl.value = data.soyad || ''; soyadEl.dispatchEvent(new Event('input')); }
                if (cinsiyetEl && typeof data.cinsiyet !== 'undefined') {
                    cinsiyetEl.value = data.cinsiyet || '';
                    cinsiyetEl.dispatchEvent(new Event('change'));
                }

                showToast('success', 'Kimlik Doğrulandı', (data.ad || '') + ' ' + (data.soyad || '') + ' bilgileri getirildi.');
            })
            .catch(function(err) {
                btn.disabled = false;
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;display:block"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';
                console.error('Kimlik sorgu hatası:', err);
                showToast('error', 'Bağlantı Hatası', 'Sunucuya bağlanılamadı. Lütfen tekrar deneyin.');
            });
    }

    // ── Veli Kimlik Sorgula ───────────────────────────────────────────────────────
    function veliKimlikSorgula() {
        var tcKimlik    = document.getElementById('veli_tc_kimlik')?.value?.trim()    || '';
        var dogumTarihi = document.getElementById('veli_dogum_tarihi')?.value?.trim() || '';

        if (!tcKimlik || tcKimlik.length !== 11) {
            showToast('error', 'Veli TC Kimlik gerekli', 'Sorgulama için önce Veli TC Kimlik No giriniz (11 rakam).');
            return;
        }
        if (!dogumTarihi) {
            showToast('error', 'Veli Doğum Tarihi gerekli', 'Sorgulama için önce Veli Doğum Tarihi giriniz.');
            return;
        }

        var btn     = document.getElementById('veliKimlikSorgulaBtn');
        var adEl    = document.getElementById('veli_ad');
        var soyadEl = document.getElementById('veli_soyad');

        btn.disabled = true;
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="adres-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;display:block"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';

        var csrfToken = document.querySelector('meta[name=csrf-token]')?.content || '';

        fetch('{{ route("kps.kimlikSorgula") }}', {
            method: 'POST',
            headers: {
                'Content-Type'     : 'application/json',
                'X-CSRF-TOKEN'     : csrfToken,
                'X-Requested-With' : 'XMLHttpRequest',
            },
            body: JSON.stringify({ tc_kimlik: tcKimlik, dogum_tarihi: dogumTarihi }),
        })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                btn.disabled = false;
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;display:block"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';

                if (!data.success) {
                    showToast('error', 'Veli Kimlik Sorgu Hatası', data.message || 'Kimlik doğrulaması başarısız.');
                    return;
                }

                if (adEl)    { adEl.value    = data.ad    || ''; adEl.dispatchEvent(new Event('input')); }
                if (soyadEl) { soyadEl.value = data.soyad || ''; soyadEl.dispatchEvent(new Event('input')); }

                showToast('success', 'Veli Kimliği Doğrulandı', (data.ad || '') + ' ' + (data.soyad || '') + ' bilgileri getirildi.');
            })
            .catch(function(err) {
                btn.disabled = false;
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;display:block"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';
                console.error('Veli kimlik sorgu hatası:', err);
                showToast('error', 'Bağlantı Hatası', 'Sunucuya bağlanılamadı. Lütfen tekrar deneyin.');
            });
    }

    // ── Adres Sorgula ─────────────────────────────────────────────────────────────
    function adresSorgula() {
        // edit formunda tc_kimlik ve dogum_tarihi disabled olduğundan $uye model değerini okuruz
        var tcKimlik    = document.getElementById('tc_kimlik')?.value?.trim()    || '';
        var dogumTarihi = document.getElementById('dogum_tarihi')?.value?.trim() || '';

        if (!tcKimlik || tcKimlik.length !== 11) {
            showToast('error', 'TC Kimlik gerekli', 'TC Kimlik No bilgisi okunamadı.');
            return;
        }
        if (!dogumTarihi) {
            showToast('error', 'Doğum Tarihi gerekli', 'Doğum Tarihi bilgisi okunamadı.');
            return;
        }

        var btn   = document.getElementById('adresSorgulaBtn');
        var panel = document.getElementById('adresSonucPanel');
        var badge = document.getElementById('adresIkametBadge');
        var wrap  = document.getElementById('adresReadonlyWrap');
        var input = document.getElementById('adresReadonlyInput');

        btn.disabled = true;
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="adres-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;display:block"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';
        panel.style.display = 'none';

        var csrfToken = document.querySelector('meta[name=csrf-token]')?.content || '';

        fetch('{{ route("kps.adresSorgula") }}', {
            method: 'POST',
            headers: {
                'Content-Type'     : 'application/json',
                'X-CSRF-TOKEN'     : csrfToken,
                'X-Requested-With' : 'XMLHttpRequest',
            },
            body: JSON.stringify({ tc_kimlik: tcKimlik, dogum_tarihi: dogumTarihi }),
        })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                btn.disabled = false;
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;display:block"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';

                if (!data.success) {
                    showToast('error', 'Sorgu Hatası', data.message || 'Adres sorgulaması başarısız.');
                    return;
                }

                applyAdresSorguToForm(data);

                panel.style.display = 'block';

                if (data.ikametEdiyor) {
                    badge.className = 'adres-ikamet-badge ediyor';
                    badge.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>'
                        + 'İlçede İkamet Ediyor';
                    wrap.style.display = 'block';
                    input.value = data.adres || '';
                } else if (data.adres) {
                    badge.className = 'adres-ikamet-badge etmiyor';
                    badge.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" x2="9" y1="9" y2="15"/><line x1="9" x2="15" y1="9" y2="15"/></svg>'
                        + 'İlçede İkamet Etmiyor';
                    wrap.style.display = 'block';
                    input.value = data.adres;
                } else {
                    badge.className = 'adres-ikamet-badge bulunamadi';
                    badge.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>'
                        + 'Kayıtlı adres bulunamadı';
                    wrap.style.display = 'none';
                    input.value = '';
                }

                if (data.ilAdi || data.ilceAdi || data.mahalleAdi || (data.kapi !== undefined && data.kapi !== null && String(data.kapi).trim() !== '') || (data.daire !== undefined && data.daire !== null && String(data.daire).trim() !== '')) {
                    showToast('success', 'Adres getirildi', 'İl, ilçe, mahalle ve kapı/daire bilgileri forma yazıldı.');
                }
            })
            .catch(function(err) {
                btn.disabled = false;
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;display:block"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';
                console.error('Adres sorgu hatası:', err);
                showToast('error', 'Bağlantı Hatası', 'Sunucuya bağlanılamadı. Lütfen tekrar deneyin.');
            });
    }

    function kpsMahalleParcasi(mahalleAdi) {
        if (!mahalleAdi) return '';
        var m = String(mahalleAdi).trim();
        if (/mahallesi?$/i.test(m)) return m;
        return m;
    }
    function kpsSokakParcasi(data) {
        var raw = (data.caddesokakAdi || data.sokakAdi || data.caddeAdi || '').trim();
        if (!raw) return '';
        var lo = raw.toLowerCase();
        if (lo.endsWith(' sokak') || lo.endsWith(' cadde') || lo.endsWith(' bulvarı') || lo.endsWith(' bulvari')) return raw;
        return raw;
    }
    function buildAcikAdresKpsLine(data) {
        var parcalar = [];
        var mz = kpsMahalleParcasi(data.mahalleAdi);
        if (mz) parcalar.push(mz);
        var sk = kpsSokakParcasi(data);
        if (sk) parcalar.push(sk);
        if (data.kapi !== undefined && data.kapi !== null && String(data.kapi).trim() !== '') {
            parcalar.push('NO ' + String(data.kapi).trim());
        }
        var daireIlce = [];
        if (data.daire !== undefined && data.daire !== null && String(data.daire).trim() !== '') {
            daireIlce.push('DAİRE ' + String(data.daire).trim());
        }
        if (data.ilceAdi) daireIlce.push(String(data.ilceAdi).trim());
        if (daireIlce.length) parcalar.push(daireIlce.join(' '));
        if (data.ilAdi) parcalar.push(String(data.ilAdi).trim());
        return parcalar.filter(Boolean).join(' ');
    }

    function applyAdresSorguToForm(data) {
        if (!data || !data.success) return;
        var ilEl = document.getElementById('il');
        var ilceEl = document.getElementById('ilce');
        var mahalleEl = document.getElementById('mahalle');
        var acikEl = document.getElementById('acik_adres');
        if (ilEl && data.ilAdi) {
            ilEl.value = String(data.ilAdi).trim();
            ilEl.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (ilceEl && data.ilceAdi) {
            ilceEl.value = String(data.ilceAdi).trim();
            ilceEl.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (mahalleEl && data.mahalleAdi) {
            mahalleEl.value = String(data.mahalleAdi).trim();
            mahalleEl.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (acikEl) {
            var satir = buildAcikAdresKpsLine(data);
            if (satir) {
                acikEl.value = satir;
                acikEl.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
    }

</script>
@endsection
