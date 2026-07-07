@extends('layouts.base')

@section('title', 'Odunc Detayi')

@section('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <style>
        .odunc-show-content{padding:24px;display:flex;flex-direction:column;gap:20px;max-width:760px}
        /* Status banner */
        .status-banner{border-radius:var(--radius);padding:16px 20px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
        .status-banner.aktif{background:rgba(37,99,235,.07);border:1px solid rgba(37,99,235,.2)}
        .status-banner.gecikti{background:rgba(197,48,48,.07);border:1px solid rgba(197,48,48,.2)}
        .status-banner.iade{background:rgba(34,197,94,.07);border:1px solid rgba(34,197,94,.2)}
        .status-banner.kayip{background:rgba(107,114,128,.1);border:1px solid rgba(107,114,128,.2)}
        .status-banner-left{display:flex;align-items:center;gap:12px}
        .status-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center}
        .status-icon svg{width:22px;height:22px}
        .status-title{font-family:var(--font-serif);font-size:17px;font-weight:700}
        .status-sub{font-size:13px;margin-top:2px}
        /* Cards */
        .detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .detail-card{background:var(--card);border:1px solid rgba(217,208,194,.6);border-radius:var(--radius);padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
        .detail-card-title{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted-foreground);margin-bottom:14px;display:flex;align-items:center;gap:6px}
        .detail-card-title svg{width:13px;height:13px}
        .detail-row{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;padding:7px 0;border-bottom:1px solid rgba(217,208,194,.4)}
        .detail-row:last-child{border-bottom:none;padding-bottom:0}
        .detail-row:first-of-type{padding-top:0}
        .detail-label{font-size:13px;color:var(--muted-foreground);flex-shrink:0}
        .detail-val{font-size:13px;font-weight:500;text-align:right}
        .detail-val.red{color:var(--destructive)}
        .detail-val.green{color:#16a34a}
        /* Person hero */
        .person-hero{display:flex;align-items:center;gap:14px;margin-bottom:16px}
        .person-av{width:48px;height:48px;border-radius:50%;background:var(--sidebar-accent);color:var(--sidebar-foreground);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;flex-shrink:0}
        .person-name{font-family:var(--font-serif);font-size:16px;font-weight:700}
        .person-meta{font-size:13px;color:var(--muted-foreground);margin-top:2px}
        /* Book hero */
        .book-hero{display:flex;align-items:flex-start;gap:14px;margin-bottom:16px}
        .book-cover-lg{width:56px;height:76px;border-radius:4px;object-fit:cover;flex-shrink:0;background:var(--secondary)}
        .book-cover-ph{width:56px;height:76px;border-radius:4px;background:var(--secondary);display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .book-cover-ph svg{width:22px;height:22px;color:var(--muted-foreground)}
        .book-title{font-family:var(--font-serif);font-size:15px;font-weight:700;line-height:1.4}
        .book-author{font-size:13px;color:var(--muted-foreground);margin-top:3px}
        /* Note box */
        .note-box{background:var(--secondary);border-radius:calc(var(--radius) - 2px);padding:12px 14px;font-size:13px;line-height:1.6;color:var(--foreground)}
        /* Buttons */
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:9px 16px;border-radius:calc(var(--radius) - 2px);font-size:14px;font-weight:500;cursor:pointer;transition:background .15s,opacity .15s;border:none;text-decoration:none}
        .btn svg{width:16px;height:16px}
        .btn-primary{background:var(--primary);color:var(--primary-foreground)}
        .btn-primary:hover{opacity:.9}
        .btn-outline{background:transparent;color:var(--foreground);border:1px solid var(--border)}
        .btn-outline:hover{background:var(--muted)}
        .btn-success{background:#16a34a;color:#fff}
        .btn-success:hover{opacity:.9}
        /* Badge */
        .badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600}
        .badge-aktif{background:rgba(37,99,235,.08);color:#1e40af}
        .badge-gecikti{background:rgba(197,48,48,.1);color:#991b1b}
        .badge-iade{background:rgba(34,197,94,.1);color:#166534}
        .badge-kayip{background:rgba(107,114,128,.12);color:#374151}
        /* Toast */
        .toast-container{position:fixed;top:20px;right:20px;z-index:3000;display:flex;flex-direction:column;gap:10px}
        .toast{padding:14px 18px;border-radius:var(--radius);font-size:14px;font-weight:500;min-width:280px;box-shadow:0 4px 16px rgba(0,0,0,.12);border:1px solid transparent;animation:toast-in .3s ease}
        .toast.success{background:#f0fdf4;border-color:#bbf7d0;color:#166534}
        .toast.error{background:#fef2f2;border-color:#fecaca;color:#991b1b}
        @keyframes toast-in{from{opacity:0;transform:translateX(20px)}to{opacity:1;transform:translateX(0)}}
        @media(max-width:768px){.odunc-show-content{padding:16px}.detail-grid{grid-template-columns:1fr}}
    </style>
@endsection

@section('breadcrumb')
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('odunc.index') }}" class="breadcrumb-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3h5v5"/><path d="M8 3H3v5"/><path d="M12 22v-8.3a4 4 0 0 0-1.172-2.872L3 3"/><path d="m15 9 6-6"/></svg>
            Ödünç İşlemleri
        </a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">#{{ $islem->id }}</span>
    </nav>
@endsection

@section('content')
        <div class="odunc-show-content">

            @php
                $gecikiyor = $islem->statu === 'aktif' && \Carbon\Carbon::today()->gt($islem->iade_tarihi_planlanan);
                $gecikmeGun = $gecikiyor ? \Carbon\Carbon::today()->diffInDays($islem->iade_tarihi_planlanan) : 0;
                $kalanGun = $islem->statu === 'aktif' && !$gecikiyor ? \Carbon\Carbon::today()->diffInDays($islem->iade_tarihi_planlanan, false) : null;
                $iadeTarihiFormatted = $islem->iade_tarihi_planlanan->format('d.m.Y');

                $loanFormData = [
                    'id' => $islem->id,
                    'statu' => $islem->statu_label,
                    'uye' => [
                        'adSoyad' => trim(($islem->uye->ad ?? '') . ' ' . ($islem->uye->soyad ?? '')),
                        'tc' => $islem->uye->tc_kimlik,
                        'telefon' => $islem->uye->telefon,
                        'email' => $islem->uye->email,
                        'uyelikDurumu' => $islem->uye->statu_label,
                    ],
                    'kitap' => [
                        'eserAdi' => $islem->katalog->kunyeEserAdi,
                        'yazar' => $islem->katalog->kunyeYazar,
                        'isbn' => $islem->katalog->kunyeISBNISSN,
                        'demirbas' => $islem->katalog->kunyeDemirbasKN,
                        'kutuphane' => $islem->kutuphane?->title,
                    ],
                    'odunc' => [
                        'oduncTarihi' => $islem->odunc_tarihi?->format('d.m.Y'),
                        'planlananIade' => $islem->iade_tarihi_planlanan?->format('d.m.Y'),
                        'gerceklesenIade' => $islem->iade_tarihi_gercek?->format('d.m.Y'),
                        'sureUzatimi' => $islem->sure_uzatimi ? ((string) $islem->sure_uzatimi . ' gün') : null,
                        'sureUzatmaTarihi' => $islem->sure_uzatma_tarihi ? \Carbon\Carbon::parse($islem->sure_uzatma_tarihi)->format('d.m.Y') : null,
                        'oduncVeren' => $islem->oduncVeren?->name,
                        'iadeAlan' => $islem->iadeAlan?->name,
                    ],
                    'notlar' => [
                        'oduncNotu' => $islem->notlar,
                        'iadeNotu' => $islem->iade_notu,
                    ],
                ];
            @endphp

                <!-- Status Banner -->
            @if($islem->statu === 'iade_edildi')
                <div class="status-banner iade">
                    <div class="status-banner-left">
                        <div class="status-icon" style="background:rgba(34,197,94,.12);">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <div>
                            <div class="status-title" style="color:#166534;">İade Edildi</div>
                            <div class="status-sub" style="color:#16a34a;">{{ $islem->iade_tarihi_gercek?->format('d.m.Y') }} tarihinde teslim alındı.</div>
                        </div>
                    </div>
                    <span class="badge badge-iade">İade Edildi</span>
                </div>
            @elseif($islem->statu === 'kayip')
                <div class="status-banner kayip">
                    <div class="status-banner-left">
                        <div class="status-icon" style="background:rgba(107,114,128,.12);">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                        </div>
                        <div>
                            <div class="status-title" style="color:#374151;">Kayıp Bildirildi</div>
                            <div class="status-sub" style="color:#6b7280;">Bu kitap kayıp olarak işaretlenmiş.</div>
                        </div>
                    </div>
                    <span class="badge badge-kayip">Kayıp</span>
                </div>
            @elseif($gecikiyor)
                <div class="status-banner gecikti">
                    <div class="status-banner-left">
                        <div class="status-icon" style="background:rgba(197,48,48,.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="var(--destructive)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <div>
                            <div class="status-title" style="color:#991b1b;">Gecikmiş Ödünç</div>
                            <div class="status-sub" style="color:#b91c1c;">{{ $islem->iade_tarihi_planlanan->format('d.m.Y') }} tarihinde iade edilmeliydi. {{ $gecikmeGun }} gün gecikmiş.</div>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;align-items:center;">
                        <button class="btn btn-outline" onclick="openUzatModal({{ $islem->id }}, '{{ $iadeTarihiFormatted }}')">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/><path d="m22 22-4-4"/><path d="M18 22v-4h-4"/></svg>
                            Süre Uzat
                        </button>
                        <button class="btn btn-success" onclick="openIadeModal({{ $islem->id }})">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/></svg>
                            İade Al
                        </button>
                    </div>
                </div>
            @else
                <div class="status-banner aktif">
                    <div class="status-banner-left">
                        <div class="status-icon" style="background:rgba(37,99,235,.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3h5v5"/><path d="M8 3H3v5"/><path d="M12 22v-8.3a4 4 0 0 0-1.172-2.872L3 3"/><path d="m15 9 6-6"/></svg>
                        </div>
                        <div>
                            <div class="status-title" style="color:#1e40af;">Aktif Ödünç</div>
                            <div class="status-sub" style="color:#3b82f6;">Son iade: {{ $islem->iade_tarihi_planlanan->format('d.m.Y') }} · {{ $kalanGun }} gün kaldı.</div>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;align-items:center;">
                        <button class="btn btn-outline" onclick="openUzatModal({{ $islem->id }}, '{{ $iadeTarihiFormatted }}')">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/><path d="m22 22-4-4"/><path d="M18 22v-4h-4"/></svg>
                            Süre Uzat
                        </button>
                        <button class="btn btn-success" onclick="openIadeModal({{ $islem->id }})">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/></svg>
                            İade Al
                        </button>
                    </div>
                </div>
            @endif

            <!-- Detail Cards -->
            <div class="detail-grid">

                <!-- Üye Kartı -->
                <div class="detail-card">
                    <div class="detail-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        Üye Bilgileri
                    </div>
                    <div class="person-hero">
                        <div class="person-av">{{ mb_strtoupper(mb_substr($islem->uye->ad, 0, 1, 'UTF-8') . mb_substr($islem->uye->soyad, 0, 1, 'UTF-8'), 'UTF-8') }}</div>
                        <div>
                            <div class="person-name">{{ $islem->uye->ad }} {{ $islem->uye->soyad }}</div>
                            <div class="person-meta">TC: {{ $islem->uye->tc_kimlik }}</div>
                        </div>
                    </div>
                    <div class="detail-row"><span class="detail-label">Telefon</span><span class="detail-val">{{ $islem->uye->telefon }}</span></div>
                    <div class="detail-row"><span class="detail-label">E-posta</span><span class="detail-val">{{ $islem->uye->email ?: '—' }}</span></div>
                    <div class="detail-row"><span class="detail-label">Üyelik Durumu</span><span class="detail-val {{ $islem->uye->statu === 'aktif' ? 'green' : '' }}">{{ $islem->uye->statu_label }}</span></div>
                    <div style="margin-top:14px;display:flex;gap:8px;flex-wrap:wrap;">
                        <a href="{{ route('uyeler.show', $islem->uye) }}" class="btn btn-primary" style="font-size:13px;padding:6px 12px;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Üye Profili
                        </a>
                        <a href="{{ route('uyeler.edit', $islem->uye) }}" class="btn btn-outline" style="font-size:13px;padding:6px 12px;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            Üyeyi Düzenle
                        </a>
                    </div>
                </div>

                <!-- Kitap Kartı -->
                <div class="detail-card">
                    <div class="detail-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
                        Kitap Bilgileri
                    </div>
                    <div class="book-hero">
                        @if($islem->katalog->kunyeKapakResmi)
                            <img src="{{ asset('storage/' . $islem->katalog->kunyeKapakResmi) }}" alt="" class="book-cover-lg" />
                        @else
                            <div class="book-cover-ph">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
                            </div>
                        @endif
                        <div>
                            <div class="book-title">{{ $islem->katalog->kunyeEserAdi }}</div>
                            <div class="book-author">{{ $islem->katalog->kunyeYazar }}</div>
                        </div>
                    </div>
                    <div class="detail-row"><span class="detail-label">ISBN</span><span class="detail-val" style="font-family:monospace;font-size:12px;">{{ $islem->katalog->kunyeISBNISSN ?: '—' }}</span></div>
                    <div class="detail-row"><span class="detail-label">Demirbaş No</span><span class="detail-val">{{ $islem->katalog->kunyeDemirbasKN ?: '—' }}</span></div>
                    <div class="detail-row"><span class="detail-label">Kütüphane</span><span class="detail-val">{{ $islem->kutuphane?->title ?? '—' }}</span></div>
                    <div style="margin-top:14px;">
                        <a href="{{ route('katalog.view', $islem->katalog) }}" class="btn btn-primary" style="font-size:13px;padding:6px 12px;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            Kitabı Görüntüle
                        </a>
                    </div>
                </div>

                <!-- İşlem Bilgileri -->
                <div class="detail-card">
                    <div class="detail-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                        Tarih Bilgileri
                    </div>
                    <div class="detail-row"><span class="detail-label">Ödünç Tarihi</span><span class="detail-val">{{ $islem->odunc_tarihi->format('d.m.Y') }}</span></div>
                    <div class="detail-row"><span class="detail-label">Planlanan İade</span><span class="detail-val {{ $gecikiyor ? 'red' : '' }}">{{ $islem->iade_tarihi_planlanan->format('d.m.Y') }}</span></div>
                    @if($islem->iade_tarihi_gercek)
                        <div class="detail-row"><span class="detail-label">Gerçekleşen İade</span><span class="detail-val green">{{ $islem->iade_tarihi_gercek->format('d.m.Y') }}</span></div>
                    @endif
                    @if($gecikiyor)
                        <div class="detail-row"><span class="detail-label">Gecikme</span><span class="detail-val red">{{ $gecikmeGun }} gün</span></div>
                    @endif
                    <div class="detail-row"><span class="detail-label">Ödünç Veren</span><span class="detail-val">{{ $islem->oduncVeren?->name ?? '—' }}</span></div>
                    @if($islem->iadeAlan)
                        <div class="detail-row"><span class="detail-label">İade Alan</span><span class="detail-val">{{ $islem->iadeAlan->name }}</span></div>
                    @endif
                </div>

                @if($islem->sure_uzatimi)
                    <div class="detail-card" style="border-color:rgba(122,92,60,.3);background:rgba(122,92,60,.03);">
                        <div class="detail-card-title" style="color:var(--primary);">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/><path d="m22 22-4-4"/><path d="M18 22v-4h-4"/></svg>
                            Süre Uzatma
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Uzatılan Süre</span>
                            <span class="detail-val" style="color:var(--primary);font-weight:700;">{{ $islem->sure_uzatimi }} gün</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Uzatma Tarihi</span>
                            <span class="detail-val">{{ $islem->sure_uzatma_tarihi ? \Carbon\Carbon::parse($islem->sure_uzatma_tarihi)->format('d.m.Y') : '—' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Uzatan Kişi</span>
                            <span class="detail-val">{{ $islem->sureUzatan?->name ?? '—' }}</span>
                        </div>
                    </div>
                @endif

                <!-- Notlar -->
                @if($islem->notlar || $islem->iade_notu)
                    <div class="detail-card">
                        <div class="detail-card-title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/></svg>
                            Notlar
                        </div>
                        @if($islem->notlar)
                            <p style="font-size:12px;font-weight:600;color:var(--muted-foreground);text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;">Ödünç Notu</p>
                            <div class="note-box" style="margin-bottom:12px;">{{ $islem->notlar }}</div>
                        @endif
                        @if($islem->iade_notu)
                            <p style="font-size:12px;font-weight:600;color:var(--muted-foreground);text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;">İade Notu</p>
                            <div class="note-box">{{ $islem->iade_notu }}</div>
                        @endif
                    </div>
                @endif
            </div>

            <div style="display:flex;gap:10px;align-items:center;">
                <a href="{{ route('odunc.index') }}" class="btn btn-outline">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                    Listeye Dön
                </a>
                <button type="button" class="btn btn-primary" onclick="createLoanFormPdf()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                    Ödünç Formu
                </button>
            </div>

        

<!-- Süre Uzat Modal -->
<div class="modal-backdrop" id="uzatModal" style="position:fixed;inset:0;z-index:2000;background:rgba(61,50,38,.48);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:opacity .2s,visibility .2s">
    <div style="background:var(--card);border:1px solid var(--border);border-radius:16px;padding:0;max-width:440px;width:calc(100% - 32px);box-shadow:0 24px 64px rgba(0,0,0,.22);transform:scale(.93);transition:transform .2s;overflow:hidden" id="uzatBox">
        <div style="padding:20px 24px 0;display:flex;align-items:center;justify-content:space-between;gap:12px;">
            <h2 style="font-family:var(--font-serif);font-size:18px;font-weight:700;">Süre Uzat</h2>
            <button onclick="closeUzatModal()" style="width:28px;height:28px;border-radius:6px;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--muted-foreground);">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <div style="margin:14px 24px 0;padding:14px;background:var(--secondary);border-radius:8px;font-size:13px;display:flex;flex-direction:column;gap:6px;">
            <div style="display:flex;justify-content:space-between;gap:12px;">
                <span style="color:var(--muted-foreground);">Üye</span>
                <strong style="text-align:right;">{{ $islem->uye->ad }} {{ $islem->uye->soyad }}</strong>
            </div>
            <div style="display:flex;justify-content:space-between;gap:12px;">
                <span style="color:var(--muted-foreground);">Kitap</span>
                <strong style="text-align:right;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $islem->katalog->kunyeEserAdi }}</strong>
            </div>
            <div style="display:flex;justify-content:space-between;gap:12px;">
                <span style="color:var(--muted-foreground);">Mevcut Planlanan İade</span>
                <strong id="uzatMevcutTarih">{{ $iadeTarihiFormatted }}</strong>
            </div>
        </div>
        <div style="padding:16px 24px 0;">
            <div style="margin-bottom:14px;">
                <label style="font-size:14px;font-weight:500;display:block;margin-bottom:6px;">
                    Kaç Gün Uzatılsın? <span style="color:var(--destructive)">*</span>
                    <span style="font-weight:400;color:var(--muted-foreground);font-size:12px;">(En fazla 15 gün)</span>
                </label>
                <input type="number" id="uzatma_gun" min="1" max="15" placeholder="1 – 15 gün"
                       oninput="hesaplaYeniTarih()" onchange="hesaplaYeniTarih()"
                       style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);color:var(--foreground);font-size:14px;outline:none;" />
            </div>
            <div id="uzatSonucWrap" style="display:none;padding:12px 14px;background:rgba(122,92,60,.06);border:1px solid rgba(122,92,60,.2);border-radius:8px;font-size:13px;color:var(--foreground);margin-bottom:4px;">
                <span style="color:var(--muted-foreground);">Yeni Planlanan İade Tarihi:</span>
                <strong id="uzatYeniTarih" style="margin-left:6px;font-size:15px;color:var(--primary);"></strong>
            </div>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;padding:16px 24px 20px;border-top:1px solid var(--border);margin-top:16px;">
            <button type="button" class="btn btn-outline" onclick="closeUzatModal()">Vazgeç</button>
            <button type="button" class="btn btn-primary" id="uzatSubmitBtn" onclick="submitUzat({{ $islem->id }})">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/><path d="m22 22-4-4"/><path d="M18 22v-4h-4"/></svg>
                Süreyi Uzat
            </button>
        </div>
    </div>
</div>

<!-- İade Modal (list.blade ile aynı) -->
<div class="modal-backdrop" id="iadeModal" style="position:fixed;inset:0;z-index:2000;background:rgba(61,50,38,.48);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:opacity .2s,visibility .2s">
    <div style="background:var(--card);border:1px solid var(--border);border-radius:16px;padding:0;max-width:480px;width:calc(100% - 32px);box-shadow:0 24px 64px rgba(0,0,0,.22);transform:scale(.93);transition:transform .2s;overflow:hidden" id="iadeBox">
        <div style="padding:20px 24px 0;display:flex;align-items:center;justify-content:space-between;gap:12px;">
            <h2 style="font-family:var(--font-serif);font-size:18px;font-weight:700;">İade Al</h2>
            <button onclick="closeIadeModal()" style="width:28px;height:28px;border-radius:6px;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--muted-foreground);">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <form id="iadeForm" style="padding:16px 24px 20px;">
            @csrf
            <div style="margin-bottom:14px;">
                <label style="font-size:14px;font-weight:500;display:block;margin-bottom:6px;">İşlem Türü</label>
                <div style="display:flex;gap:10px;">
                    <label style="flex:1;cursor:pointer;position:relative;">
                        <input type="radio" name="statu" value="iade_edildi" checked style="position:absolute;opacity:0;" />
                        <div class="radio-inner" style="padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;text-align:center;font-size:13px;font-weight:600;transition:border-color .15s,background .15s;">✓ İade Alındı</div>
                    </label>
                    <label style="flex:1;cursor:pointer;position:relative;">
                        <input type="radio" name="statu" value="kayip" style="position:absolute;opacity:0;" />
                        <div class="radio-inner" style="padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;text-align:center;font-size:13px;font-weight:600;transition:border-color .15s,background .15s;">⚠ Kayıp</div>
                    </label>
                </div>
            </div>
            <div style="margin-bottom:14px;">
                <label style="font-size:14px;font-weight:500;display:block;margin-bottom:6px;">İade Tarihi <span style="color:var(--destructive)">*</span></label>
                <input type="date" name="iade_tarihi_gercek" id="iade_tarihi_gercek" value="{{ date('Y-m-d') }}"
                       max="{{ date('Y-m-d') }}"
                       min="{{ date('Y-m-d', strtotime('-7 days')) }}"
                       style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);color:var(--foreground);font-size:14px;outline:none;" required />
            </div>
            <div style="margin-bottom:14px;">
                <label style="font-size:14px;font-weight:500;display:block;margin-bottom:6px;">Not</label>
                <textarea name="iade_notu" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);color:var(--foreground);font-size:14px;outline:none;resize:vertical;min-height:72px;"></textarea>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:10px;padding-top:8px;border-top:1px solid var(--border);">
                <button type="button" class="btn btn-outline" onclick="closeIadeModal()">Vazgeç</button>
                <button type="button" class="btn btn-success" id="iadeSubmitBtn" onclick="submitIade({{ $islem->id }})">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/></svg>
                    İadeyi Tamamla
                </button>
            </div>
        </form>
    </div>
</div>

<div class="toast-container" id="toastContainer"></div>
        </div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
    // Toast
    function showToast(type, title, desc) {
        var c = document.getElementById('toastContainer');
        var t = document.createElement('div'); t.className = 'toast ' + type;
        t.innerHTML = '<div>' + title + '</div>' + (desc ? '<div style="font-size:13px;opacity:.8;margin-top:2px">' + desc + '</div>' : '');
        c.appendChild(t);
        setTimeout(function() { t.style.opacity = '0'; setTimeout(() => { if(t.parentNode) t.parentNode.removeChild(t); }, 300); }, 4000);
    }

    @if(session('success')) showToast('success', @json(session('success'))); @endif

    var loanFormData = @json($loanFormData);

    // İade modal
    var iadeModal = document.getElementById('iadeModal');
    function openIadeModal(id) {
        iadeModal.style.opacity = '1'; iadeModal.style.visibility = 'visible';
        document.getElementById('iadeBox').style.transform = 'scale(1)';
    }
    function closeIadeModal() {
        iadeModal.style.opacity = '0'; iadeModal.style.visibility = 'hidden';
        document.getElementById('iadeBox').style.transform = 'scale(.93)';
    }
    iadeModal.addEventListener('click', function(e) { if (e.target === this) closeIadeModal(); });

    // İade tarihi kısıtı
    (function() {
        var inp = document.getElementById('iade_tarihi_gercek');
        inp.addEventListener('change', function() {
            var today   = new Date(new Date().toDateString());
            var minDate = new Date(today); minDate.setDate(today.getDate() - 7);
            var chosen  = new Date(this.value + 'T00:00:00');
            if (chosen > today)   this.value = today.toISOString().slice(0,10);
            if (chosen < minDate) this.value = minDate.toISOString().slice(0,10);
        });
    })();

    // Süre Uzat Modal
    var uzatModal = document.getElementById('uzatModal');
    var currentUzatPlanlanan = null;

    function openUzatModal(id, planlananTarih) {
        currentUzatPlanlanan = planlananTarih || '{{ $iadeTarihiFormatted }}';
        uzatModal.style.opacity = '1'; uzatModal.style.visibility = 'visible';
        document.getElementById('uzatBox').style.transform = 'scale(1)';
        document.getElementById('uzatma_gun').value = '';
        document.getElementById('uzatSonucWrap').style.display = 'none';
        document.getElementById('uzatSubmitBtn').disabled = false;
        setTimeout(function() { document.getElementById('uzatma_gun').focus(); }, 200);
    }

    function closeUzatModal() {
        uzatModal.style.opacity = '0'; uzatModal.style.visibility = 'hidden';
        document.getElementById('uzatBox').style.transform = 'scale(.93)';
        currentUzatPlanlanan = null;
    }

    uzatModal.addEventListener('click', function(e) { if (e.target === this) closeUzatModal(); });

    function hesaplaYeniTarih() {
        var gun  = parseInt(document.getElementById('uzatma_gun').value);
        var wrap = document.getElementById('uzatSonucWrap');
        if (!currentUzatPlanlanan || isNaN(gun) || gun < 1 || gun > 15) { wrap.style.display = 'none'; return; }
        var parts = currentUzatPlanlanan.split('.');
        var base  = new Date(parseInt(parts[2]), parseInt(parts[1]) - 1, parseInt(parts[0]));
        base.setDate(base.getDate() + gun);
        var yeni = base.getDate().toString().padStart(2,'0') + '.'
            + (base.getMonth()+1).toString().padStart(2,'0') + '.'
            + base.getFullYear();
        document.getElementById('uzatYeniTarih').textContent = yeni;
        wrap.style.display = 'block';
    }

    function submitUzat(id) {
        var gun = parseInt(document.getElementById('uzatma_gun').value);
        if (isNaN(gun) || gun < 1 || gun > 15) {
            showToast('error', 'Geçersiz Gün', 'Lütfen 1 ile 15 arasında bir gün girin.');
            return;
        }
        var btn = document.getElementById('uzatSubmitBtn');
        btn.disabled = true; btn.textContent = 'İşleniyor…';

        var fd = new FormData();
        fd.append('uzatma_gun', gun);

        fetch('/odunc/' + id + '/sure-uzat', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
            body: fd
        })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    showToast('success', data.message);
                    closeUzatModal();
                    setTimeout(function() { window.location.reload(); }, 800);
                } else {
                    showToast('error', data.message || 'Hata oluştu.');
                    btn.disabled = false; btn.textContent = 'Süreyi Uzat';
                }
            })
            .catch(function() {
                showToast('error', 'Bağlantı hatası.');
                btn.disabled = false; btn.textContent = 'Süreyi Uzat';
            });
    }

    function submitIade(id) {
        var btn = document.getElementById('iadeSubmitBtn');
        btn.disabled = true; btn.textContent = 'İşleniyor…';
        var formData = new FormData(document.getElementById('iadeForm'));
        fetch('/odunc/' + id + '/iade', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
            body: formData
        })
            .then(r => r.json())
            .then(function(data) {
                if (data.success) { showToast('success', data.message); closeIadeModal(); setTimeout(() => window.location.reload(), 800); }
                else { showToast('error', data.message); btn.disabled = false; btn.textContent = 'İadeyi Tamamla'; }
            })
            .catch(() => { showToast('error', 'Bağlantı hatası.'); btn.disabled = false; });
    }

    // Radio style
    document.querySelectorAll('[name=statu]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.radio-inner').forEach(el => {
                el.style.borderColor = 'var(--border)'; el.style.background = '';
            });
            var inner = this.parentElement.querySelector('.radio-inner');
            inner.style.borderColor = 'var(--primary)'; inner.style.background = 'rgba(122,92,60,.05)';
        });
    });

    /** Helvetica/cp1252 ile güvenli yedek metin (Türkçe font yüklenemezse). */
    function normPDFAscii(str) {
        return String(str || '')
            .replace(/ğ/g, 'g').replace(/Ğ/g, 'G')
            .replace(/ş/g, 's').replace(/Ş/g, 'S')
            .replace(/ı/g, 'i').replace(/İ/g, 'I')
            .replace(/ü/g, 'u').replace(/Ü/g, 'U')
            .replace(/ö/g, 'o').replace(/Ö/g, 'O')
            .replace(/ç/g, 'c').replace(/Ç/g, 'C');
    }

    function arrayBufferToBase64(buf) {
        var bytes = new Uint8Array(buf);
        var binary = '';
        var CHUNK = 8192;
        for (var i = 0; i < bytes.length; i += CHUNK) {
            binary += String.fromCharCode.apply(null, bytes.subarray(i, i + CHUNK));
        }
        return btoa(binary);
    }

    var _pdfDejaVuLoadPromise = null;

    /** DejaVu Sans (TTF) — Türkçe tam Unicode; CDN'den bir kez indirilir. */
    function ensurePdfTurkishFonts() {
        if (window._pdfDejaVuSansB64 && window._pdfDejaVuBoldB64) {
            return Promise.resolve(true);
        }
        if (_pdfDejaVuLoadPromise) {
            return _pdfDejaVuLoadPromise;
        }
        var base = 'https://unpkg.com/dejavu-fonts-ttf@2.37.3/ttf/';
        _pdfDejaVuLoadPromise = Promise.all([
            fetch(base + 'DejaVuSans.ttf').then(function(r) {
                if (!r.ok) throw new Error('sans');
                return r.arrayBuffer();
            }),
            fetch(base + 'DejaVuSans-Bold.ttf').then(function(r) {
                if (!r.ok) throw new Error('bold');
                return r.arrayBuffer();
            }),
        ]).then(function(bufs) {
            window._pdfDejaVuSansB64 = arrayBufferToBase64(bufs[0]);
            window._pdfDejaVuBoldB64 = arrayBufferToBase64(bufs[1]);
            return true;
        }).catch(function() {
            _pdfDejaVuLoadPromise = null;
            return false;
        });
        return _pdfDejaVuLoadPromise;
    }

    function valOrDash(v) {
        var s = String(v || '').trim();
        return s ? s : '-';
    }

    function createLoanFormPdf() {
        if (!window.jspdf || !window.jspdf.jsPDF) {
            showToast('error', 'PDF Kütüphanesi Yüklenemedi', 'Lütfen sayfayı yenileyip tekrar deneyin.');
            return;
        }
        ensurePdfTurkishFonts().then(function(ok) {
            if (!ok) {
                showToast('error', 'Bilgi', 'Türkçe font indirilemedi (ağ). Belge Latin karşılıklarıyla oluşturuldu.');
            }
            try {
                runLoanFormPdfBuild(ok);
            } catch (e) {
                console.error(e);
                showToast('error', 'PDF', 'Oluşturma sırasında hata oluştu.');
            }
        });
    }

    function runLoanFormPdfBuild(useDejaVu) {
        var jsPDF = window.jspdf.jsPDF;
        var doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        if (useDejaVu) {
            doc.addFileToVFS('DejaVuSans.ttf', window._pdfDejaVuSansB64);
            doc.addFont('DejaVuSans.ttf', 'dejavu', 'normal');
            doc.addFileToVFS('DejaVuSans-Bold.ttf', window._pdfDejaVuBoldB64);
            doc.addFont('DejaVuSans-Bold.ttf', 'dejavu', 'bold');
        }

        function T(s) {
            return useDejaVu ? String(s || '') : normPDFAscii(s);
        }

        function setBold(size) {
            doc.setFont(useDejaVu ? 'dejavu' : 'helvetica', 'bold');
            doc.setFontSize(size);
        }

        function setNormal(size) {
            doc.setFont(useDejaVu ? 'dejavu' : 'helvetica', 'normal');
            doc.setFontSize(size);
        }

        var pageW = doc.internal.pageSize.getWidth();
        var pageH = doc.internal.pageSize.getHeight();
        var m = 14;
        var y = 18;
        var BLACK = [0, 0, 0];

        function lineRgb(x1, y1, x2, y2, rgb) {
            doc.setDrawColor(rgb[0], rgb[1], rgb[2]);
            doc.line(x1, y1, x2, y2);
        }

        function sectionTitle(title, showUnderline) {
            if (showUnderline === undefined) {
                showUnderline = true;
            }
            if (y > pageH - 36) {
                doc.addPage();
                y = 18;
            }
            doc.setTextColor(0, 0, 0);
            setBold(10.5);
            doc.text(T(title), m, y);
            y += 2.2;
            if (showUnderline) {
                doc.setLineWidth(0.35);
                lineRgb(m, y, pageW - m, y, BLACK);
                doc.setLineWidth(0.2);
            }
            y += 5.5;
        }

        function pair(label, value) {
            var maxW = pageW - (m + 38) - m;
            setNormal(9.5);
            var valLines = doc.splitTextToSize(T(valOrDash(value)), maxW);
            var rowH = Math.max(6.5, valLines.length * 4.2 + 1.5);
            if (y + rowH > pageH - 20) {
                doc.addPage();
                y = 18;
            }
            doc.setTextColor(0, 0, 0);
            setBold(9.5);
            doc.text(T(label), m, y);

            setNormal(9.5);
            doc.text(valLines, m + 38, y);

            y += rowH;
        }

        doc.setTextColor(0, 0, 0);
        setBold(15);
        doc.text(T('ÖDÜNÇ BELGESİ'), m, y);
        y += 5.5;
        setNormal(9.5);
        doc.text(T('İşlem No: #' + loanFormData.id), m, y);
        doc.text(T('Durum: ' + valOrDash(loanFormData.statu)), m, y + 4.2);
        doc.text(T('Belge Tarihi: ' + new Date().toLocaleDateString('tr-TR')), pageW - m, y, { align: 'right' });
        doc.text(T('Belge Saati: ' + new Date().toLocaleTimeString('tr-TR')), pageW - m, y + 4.2, { align: 'right' });
        y += 12;

        sectionTitle('Üye Bilgileri');
        pair('Ad Soyad', loanFormData.uye.adSoyad);
        pair('T.C. Kimlik', loanFormData.uye.tc);
        pair('Telefon', loanFormData.uye.telefon);
        pair('E-posta', loanFormData.uye.email);
        pair('Üyelik durumu', loanFormData.uye.uyelikDurumu);

        y += 4;
        sectionTitle('Kitap Bilgileri');
        pair('Eser adı', loanFormData.kitap.eserAdi);
        pair('Yazar', loanFormData.kitap.yazar);
        pair('ISBN', loanFormData.kitap.isbn);
        pair('Demirbaş no', loanFormData.kitap.demirbas);
        pair('Kütüphane', loanFormData.kitap.kutuphane);

        y += 4;
        sectionTitle('Ödünç Bilgileri');
        pair('Ödünç tarihi', loanFormData.odunc.oduncTarihi);
        pair('Planlanan iade', loanFormData.odunc.planlananIade);
        pair('Gerçekleşen iade', loanFormData.odunc.gerceklesenIade);
        pair('Süre uzatımı', loanFormData.odunc.sureUzatimi);
        pair('Süre uzatma tarihi', loanFormData.odunc.sureUzatmaTarihi);
        pair('Ödünç veren', loanFormData.odunc.oduncVeren);
        pair('İade alan', loanFormData.odunc.iadeAlan);

        if (loanFormData.notlar.oduncNotu || loanFormData.notlar.iadeNotu) {
            y += 4;
            sectionTitle('Notlar', false);
            pair('Ödünç notu', loanFormData.notlar.oduncNotu);
            pair('İade notu', loanFormData.notlar.iadeNotu);
        }

        var footerY = pageH - 28;
        if (y > footerY - 12) {
            doc.addPage();
            y = 18;
            footerY = pageH - 28;
        }

        doc.setTextColor(0, 0, 0);
        setNormal(8.5);
        doc.text(T('Bu belge kütüphane bilgi sistemi tarafından üretilmiştir.'), m, footerY + 5);
        doc.text(T('İmza (görevli): ____________________'), m, footerY + 12);
        doc.text(T('İmza (üye): ____________________'), pageW - m, footerY + 12, { align: 'right' });

        doc.save('odunc-formu-' + loanFormData.id + '.pdf');
    }
</script>
@endsection
