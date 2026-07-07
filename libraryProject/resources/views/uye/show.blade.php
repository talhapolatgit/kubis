@extends('layouts.base')

@section('title', $uye->ad_soyad . ' — Üye Profili')

@section('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <style>
        .uye-profile{max-width:1100px;margin:0 auto;display:flex;flex-direction:column;gap:20px}
        .profile-hero{background:var(--card);border:1px solid rgba(217,208,194,.6);border-radius:var(--radius);padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.04);display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap}
        .profile-hero-left{display:flex;align-items:center;gap:18px;min-width:0}
        .profile-avatar{width:72px;height:72px;border-radius:50%;background:var(--sidebar-accent);color:var(--sidebar-foreground);display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:700;flex-shrink:0}
        .profile-name{font-family:var(--font-serif);font-size:24px;font-weight:700;line-height:1.2}
        .profile-meta{display:flex;flex-wrap:wrap;align-items:center;gap:10px;margin-top:8px;font-size:13px;color:var(--muted-foreground)}
        .profile-actions{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:9px 16px;border-radius:calc(var(--radius) - 2px);font-size:14px;font-weight:500;cursor:pointer;border:none;text-decoration:none;transition:opacity .15s,background .15s}
        .btn svg{width:16px;height:16px}
        .btn-primary{background:var(--primary);color:var(--primary-foreground)}
        .btn-primary:hover{opacity:.92}
        .btn-outline{background:transparent;color:var(--foreground);border:1px solid var(--border)}
        .btn-outline:hover{background:var(--muted)}
        .badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600}
        .badge-aktif{background:rgba(34,139,34,.12);color:#1a6b1a}
        .badge-pasif{background:rgba(197,48,48,.1);color:#9b1c1c}
        .stats-row{display:grid;grid-template-columns:repeat(5,1fr);gap:12px}
        .stat-card{background:var(--card);border:1px solid rgba(217,208,194,.6);border-radius:var(--radius);padding:16px 18px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
        .stat-label{font-size:12px;font-weight:600;color:var(--muted-foreground);text-transform:uppercase;letter-spacing:.04em}
        .stat-value{font-family:var(--font-serif);font-size:28px;font-weight:700;margin-top:4px;line-height:1}
        .stat-value.blue{color:#1e40af}
        .stat-value.red{color:var(--destructive)}
        .stat-value.green{color:#16a34a}
        .tabs-bar{display:flex;gap:8px;flex-wrap:wrap;border-bottom:1px solid rgba(217,208,194,.5);padding-bottom:0}
        .tab-btn{padding:10px 16px;border:none;border-bottom:2px solid transparent;background:transparent;cursor:pointer;font-size:14px;font-weight:500;color:var(--muted-foreground);margin-bottom:-1px;transition:color .15s,border-color .15s}
        .tab-btn:hover{color:var(--foreground)}
        .tab-btn.active{color:var(--primary);border-bottom-color:var(--primary);font-weight:600}
        .tab-panel{display:none}
        .tab-panel.active{display:block}
        .detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .detail-card{background:var(--card);border:1px solid rgba(217,208,194,.6);border-radius:var(--radius);padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
        .detail-card.full{grid-column:1/-1}
        .detail-card-title{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted-foreground);margin-bottom:14px;display:flex;align-items:center;gap:6px}
        .detail-card-title svg{width:13px;height:13px}
        .detail-row{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;padding:8px 0;border-bottom:1px solid rgba(217,208,194,.4)}
        .detail-row:last-child{border-bottom:none;padding-bottom:0}
        .detail-label{font-size:13px;color:var(--muted-foreground);flex-shrink:0}
        .detail-val{font-size:13px;font-weight:500;text-align:right;word-break:break-word}
        .detail-val.green{color:#16a34a}
        .detail-val.red{color:var(--destructive)}
        .note-box{background:var(--secondary);border-radius:calc(var(--radius) - 2px);padding:12px 14px;font-size:13px;line-height:1.6;color:var(--foreground)}
        .table-card{background:var(--card);border:1px solid rgba(217,208,194,.6);border-radius:var(--radius);overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.04);position:relative}
        .table-toolbar{padding:14px 16px;border-bottom:1px solid rgba(217,208,194,.5);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
        .filter-select{padding:8px 10px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);font-size:13px}
        .table-wrap{overflow-x:auto}
        table{width:100%;border-collapse:collapse}
        thead{background:var(--secondary)}
        th{padding:11px 16px;text-align:left;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--muted-foreground);white-space:nowrap;border-bottom:1px solid var(--border)}
        td{padding:12px 16px;font-size:14px;border-bottom:1px solid rgba(217,208,194,.4);vertical-align:middle}
        tr:last-child td{border-bottom:none}
        .book-cell{display:flex;align-items:center;gap:10px;min-width:200px}
        .book-thumb{width:36px;height:50px;border-radius:4px;object-fit:cover;background:var(--muted);flex-shrink:0;border:1px solid var(--border)}
        .book-title{font-weight:600;font-size:13px;line-height:1.35}
        .book-sub{font-size:12px;color:var(--muted-foreground);margin-top:2px}
        .loan-badge{display:inline-flex;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600}
        .loan-badge.aktif{background:rgba(37,99,235,.08);color:#1e40af}
        .loan-badge.gecikti{background:rgba(197,48,48,.1);color:#991b1b}
        .loan-badge.iade{background:rgba(34,197,94,.1);color:#166534}
        .loan-badge.kayip{background:rgba(107,114,128,.12);color:#374151}
        .rez-badge{display:inline-flex;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;background:rgba(122,92,60,.1);color:var(--primary)}
        .ziy-badge{display:inline-flex;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600}
        .ziy-badge.icinde{background:rgba(37,99,235,.08);color:#1e40af}
        .ziy-badge.cikis{background:rgba(34,197,94,.1);color:#166534}
        .link-btn{font-size:13px;font-weight:500;color:var(--primary);text-decoration:none}
        .link-btn:hover{text-decoration:underline}
        .table-footer{padding:12px 16px;display:flex;align-items:center;justify-content:space-between;border-top:1px solid rgba(217,208,194,.5);font-size:13px;color:var(--muted-foreground)}
        .pagination{display:flex;gap:6px;align-items:center}
        .page-btn{padding:6px 10px;border:1px solid var(--border);border-radius:8px;background:#fff;cursor:pointer;font-size:13px}
        .page-btn.active{background:var(--primary);color:var(--primary-foreground);border-color:var(--primary)}
        .page-btn.disabled{opacity:.45;cursor:default;pointer-events:none}
        .table-veil{position:absolute;inset:0;background:rgba(255,255,255,.6);display:none;align-items:center;justify-content:center;z-index:2}
        .table-veil.visible{display:flex}
        .veil-spinner{width:28px;height:28px;border-radius:50%;border:3px solid rgba(122,92,60,.18);border-top-color:var(--primary);animation:spin 1s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}}
        .empty-state{padding:48px 24px;text-align:center;color:var(--muted-foreground);font-size:13px}
        .empty-title{font-size:15px;font-weight:600;color:var(--foreground);margin:8px 0 4px}
        @media(max-width:900px){.stats-row{grid-template-columns:repeat(2,1fr)}.detail-grid{grid-template-columns:1fr}}
        @media(max-width:600px){.profile-hero{padding:18px}.profile-name{font-size:20px}.stats-row{grid-template-columns:1fr}}
    </style>
@endsection

@section('breadcrumb')
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('uyeler.index') }}" class="breadcrumb-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            Üyeler
        </a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">{{ $uye->ad_soyad }}</span>
    </nav>
@endsection

@section('content')
@php
    $cinsiyetLabel = match($uye->cinsiyet) {
        'erkek' => 'Erkek',
        'kadin' => 'Kadın',
        'diger' => 'Diğer',
        default => '—',
    };
    $yas = $uye->dogum_tarihi ? \Carbon\Carbon::parse($uye->dogum_tarihi)->age : null;
    $adresParcalari = array_filter([$uye->mahalle, $uye->ilce, $uye->il]);
    $createdLabel = $createdUser
        ? trim(($createdUser->ad ?? '') . ' ' . ($createdUser->soyad ?? '')) ?: ($createdUser->name ?? '—')
        : '—';
    $updatedLabel = $updatedUser
        ? trim(($updatedUser->ad ?? '') . ' ' . ($updatedUser->soyad ?? '')) ?: ($updatedUser->name ?? '—')
        : '—';
@endphp

<div class="uye-profile">

    {{-- Hero --}}
    <div class="profile-hero">
        <div class="profile-hero-left">
            <div class="profile-avatar">{{ $uye->initials }}</div>
            <div>
                <h1 class="profile-name">{{ $uye->ad_soyad }}</h1>
                <div class="profile-meta">
                    <span>TC: {{ $uye->tc_kimlik }}</span>
                    @if($yas !== null)<span>·</span><span>{{ $yas }} yaş</span>@endif
                    <span>·</span>
                    <span class="badge badge-{{ $uye->statu }}">{{ $uye->statu_label }}</span>
                    @if($uye->telefon_dogrulandi)
                        <span title="Telefon doğrulandı" style="color:#16a34a;display:inline-flex;align-items:center;gap:4px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            Doğrulanmış telefon
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="profile-actions">
            <a href="{{ route('uyeler.index') }}" class="btn btn-outline">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                Listeye Dön
            </a>
            @if($canEdit)
            <a href="{{ route('uyeler.edit', $uye) }}" class="btn btn-outline">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                Düzenle
            </a>
            @endif
            @if($canLend)
            <a href="{{ route('odunc.new', ['uye_id' => $uye->id]) }}" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M12 18v-6"/><path d="M9 15h6"/></svg>
                Ödünç Ver
            </a>
            @endif
        </div>
    </div>

    {{-- İstatistikler --}}
    @if($canViewLoans || $canViewRezerve)
    <div class="stats-row">
        @if($canViewLoans)
        <div class="stat-card">
            <div class="stat-label">Aktif Ödünç</div>
            <div class="stat-value blue">{{ $stats['aktif_odunc'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Gecikmiş</div>
            <div class="stat-value red">{{ $stats['gecikmis_odunc'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Toplam Ödünç</div>
            <div class="stat-value">{{ $stats['toplam_odunc'] }}</div>
        </div>
        @endif
        @if($canViewRezerve)
        <div class="stat-card">
            <div class="stat-label">Aktif Rezerve</div>
            <div class="stat-value green">{{ $stats['aktif_rezerve'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Toplam Rezerve</div>
            <div class="stat-value">{{ $stats['toplam_rezerve'] }}</div>
        </div>
        @endif
    </div>
    @endif

    {{-- Sekmeler --}}
    <div class="tabs-bar" role="tablist">
        <button type="button" class="tab-btn active" data-tab="bilgiler" role="tab">Bilgiler</button>
        @if($canViewLoans)
        <button type="button" class="tab-btn" data-tab="odunc" role="tab">Ödünç Kayıtları</button>
        @endif
        @if($canViewRezerve)
        <button type="button" class="tab-btn" data-tab="rezerve" role="tab">Rezervasyonlar</button>
        @endif
        @if($canViewZiyaret)
        <button type="button" class="tab-btn" data-tab="ziyaret" role="tab">Ziyaret Geçmişi</button>
        @endif
    </div>

    {{-- Bilgiler --}}
    <div class="tab-panel active" id="tab-bilgiler" role="tabpanel">
        <div class="detail-grid">
            <div class="detail-card">
                <div class="detail-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Kimlik Bilgileri
                </div>
                <div class="detail-row"><span class="detail-label">Ad Soyad</span><span class="detail-val">{{ $uye->ad_soyad }}</span></div>
                <div class="detail-row"><span class="detail-label">TC Kimlik No</span><span class="detail-val" style="font-family:monospace;">{{ $uye->tc_kimlik }}</span></div>
                <div class="detail-row"><span class="detail-label">Doğum Tarihi</span><span class="detail-val">{{ $uye->dogum_tarihi?->format('d.m.Y') ?? '—' }}</span></div>
                <div class="detail-row"><span class="detail-label">Cinsiyet</span><span class="detail-val">{{ $cinsiyetLabel }}</span></div>
            </div>

            <div class="detail-card">
                <div class="detail-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    İletişim
                </div>
                <div class="detail-row"><span class="detail-label">Telefon</span><span class="detail-val">{{ $uye->telefon ?: '—' }}</span></div>
                <div class="detail-row"><span class="detail-label">2. Telefon</span><span class="detail-val">{{ $uye->telefon2 ?: '—' }}</span></div>
                <div class="detail-row"><span class="detail-label">E-posta</span><span class="detail-val">{{ $uye->email ?: '—' }}</span></div>
            </div>

            <div class="detail-card">
                <div class="detail-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    Adres
                </div>
                <div class="detail-row"><span class="detail-label">İl / İlçe</span><span class="detail-val">{{ collect([$uye->il, $uye->ilce])->filter()->join(' / ') ?: '—' }}</span></div>
                <div class="detail-row"><span class="detail-label">Mahalle</span><span class="detail-val">{{ $uye->mahalle ?: '—' }}</span></div>
                <div class="detail-row"><span class="detail-label">Açık Adres</span><span class="detail-val">{{ $uye->acik_adres ?: '—' }}</span></div>
            </div>

            <div class="detail-card">
                <div class="detail-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                    Eğitim
                </div>
                <div class="detail-row"><span class="detail-label">Öğrenim Durumu</span><span class="detail-val">{{ $uye->ogretim_durumu ?: '—' }}</span></div>
                <div class="detail-row"><span class="detail-label">Okul</span><span class="detail-val">{{ $uye->okul_adi ?: '—' }}</span></div>
                <div class="detail-row"><span class="detail-label">Bölüm</span><span class="detail-val">{{ $uye->bolum_adi ?: '—' }}</span></div>
            </div>

            <div class="detail-card">
                <div class="detail-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                    Üyelik
                </div>
                <div class="detail-row"><span class="detail-label">Durum</span><span class="detail-val {{ $uye->statu === 'aktif' ? 'green' : 'red' }}">{{ $uye->statu_label }}</span></div>
                <div class="detail-row"><span class="detail-label">Başlangıç</span><span class="detail-val">{{ $uye->uyelik_baslangic?->format('d.m.Y') ?? '—' }}</span></div>
                <div class="detail-row"><span class="detail-label">Bitiş</span><span class="detail-val">{{ $uye->uyelik_bitis?->format('d.m.Y') ?? '—' }}</span></div>
                <div class="detail-row"><span class="detail-label">Kayıt Tarihi</span><span class="detail-val">{{ $uye->created_at?->format('d.m.Y H:i') ?? '—' }}</span></div>
                <div class="detail-row"><span class="detail-label">Kaydeden</span><span class="detail-val">{{ $createdLabel }}</span></div>
                @if($uye->updated_at && $uye->updated_at->ne($uye->created_at))
                <div class="detail-row"><span class="detail-label">Son Güncelleme</span><span class="detail-val">{{ $uye->updated_at->format('d.m.Y H:i') }}</span></div>
                <div class="detail-row"><span class="detail-label">Güncelleyen</span><span class="detail-val">{{ $updatedLabel }}</span></div>
                @endif
            </div>

            @if($isMinor)
            <div class="detail-card">
                <div class="detail-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Veli Bilgileri
                </div>
                <div class="detail-row"><span class="detail-label">Veli Ad Soyad</span><span class="detail-val">{{ trim(($uye->veli_ad ?? '') . ' ' . ($uye->veli_soyad ?? '')) ?: '—' }}</span></div>
                <div class="detail-row"><span class="detail-label">Veli TC</span><span class="detail-val">{{ $uye->veli_tc_kimlik ?: '—' }}</span></div>
                <div class="detail-row"><span class="detail-label">Veli Doğum</span><span class="detail-val">{{ $uye->veli_dogum_tarihi?->format('d.m.Y') ?? '—' }}</span></div>
                <div class="detail-row"><span class="detail-label">Veli Telefon</span><span class="detail-val">{{ $uye->veli_telefon ?: '—' }}</span></div>
            </div>
            @endif

            @if($uye->notlar)
            <div class="detail-card full">
                <div class="detail-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/></svg>
                    Notlar
                </div>
                <div class="note-box">{{ $uye->notlar }}</div>
            </div>
            @endif
        </div>
    </div>

    @if($canViewLoans)
    <div class="tab-panel" id="tab-odunc" role="tabpanel">
        <div class="table-card" id="oduncTableCard">
            <div class="table-toolbar">
                <strong style="font-size:15px;">Ödünç Kayıtları</strong>
                <select id="oduncStatuFilter" class="filter-select">
                    <option value="hepsi" selected>Tümü</option>
                    <option value="aktif">Aktif</option>
                    <option value="gecikti">Gecikmiş</option>
                    <option value="iade_edildi">İade edildi</option>
                    <option value="kayip">Kayıp</option>
                </select>
            </div>
            <div class="table-veil" id="oduncVeil"><div class="veil-spinner"></div></div>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Kitap</th>
                        <th>Ödünç Tarihi</th>
                        <th>Planlanan İade</th>
                        <th>Durum</th>
                        <th>Kütüphane</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody id="oduncTableBody">
                    <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted-foreground);">Yükleniyor…</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="table-footer">
                <span id="oduncRangeInfo">—</span>
                <div class="pagination" id="oduncPagination"></div>
            </div>
        </div>
    </div>
    @endif

    @if($canViewRezerve)
    <div class="tab-panel" id="tab-rezerve" role="tabpanel">
        <div class="table-card" id="rezerveTableCard">
            <div class="table-toolbar">
                <strong style="font-size:15px;">Rezervasyon Kayıtları</strong>
                <select id="rezerveFiltre" class="filter-select">
                    <option value="hepsi" selected>Tümü</option>
                    <option value="aktif">Aktif</option>
                    <option value="tamamlanan">Ödünç verildi</option>
                    <option value="iptal">İptal</option>
                    <option value="suresi_doldu">Süresi doldu</option>
                </select>
            </div>
            <div class="table-veil" id="rezerveVeil"><div class="veil-spinner"></div></div>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Kitap</th>
                        <th>Başlangıç</th>
                        <th>Bitiş</th>
                        <th>Durum</th>
                        <th>Kütüphane</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody id="rezerveTableBody">
                    <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted-foreground);">Yükleniyor…</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="table-footer">
                <span id="rezerveRangeInfo">—</span>
                <div class="pagination" id="rezervePagination"></div>
            </div>
        </div>
    </div>
    @endif

    @if($canViewZiyaret)
    <div class="tab-panel" id="tab-ziyaret" role="tabpanel">
        <div class="table-card" id="ziyaretTableCard">
            <div class="table-toolbar">
                <strong style="font-size:15px;">Ziyaret Geçmişi</strong>
                <select id="ziyaretFiltre" class="filter-select">
                    <option value="hepsi" selected>Tümü</option>
                    <option value="bugun">Bugün</option>
                    <option value="icinde">İçeride</option>
                    <option value="cikisli">Çıkış yapılmış</option>
                </select>
            </div>
            <div class="table-veil" id="ziyaretVeil"><div class="veil-spinner"></div></div>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Kütüphane</th>
                        <th>Giriş</th>
                        <th>Çıkış</th>
                        <th>Süre</th>
                        <th>Durum</th>
                        <th>Not</th>
                    </tr>
                    </thead>
                    <tbody id="ziyaretTableBody">
                    <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted-foreground);">Yükleniyor…</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="table-footer">
                <span id="ziyaretRangeInfo">—</span>
                <div class="pagination" id="ziyaretPagination"></div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@section('scripts')
<script>
(function() {
    var uyeId = {{ $uye->id }};
    var oduncUrl = @json(route('uyeler.oduncTable', $uye));
    var rezerveUrl = @json(route('uyeler.rezerveTable', $uye));
    var ziyaretUrl = @json(route('uyeler.ziyaretTable', $uye));
    var canViewLoans = @json($canViewLoans);
    var canViewRezerve = @json($canViewRezerve);
    var canViewZiyaret = @json($canViewZiyaret);

    function esc(s) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(s || ''));
        return d.innerHTML;
    }

    // ── Sekmeler ──────────────────────────────────────────────────────────────
    var loadedTabs = { bilgiler: true };
    document.querySelectorAll('.tab-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var tab = this.getAttribute('data-tab');
            document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
            document.querySelectorAll('.tab-panel').forEach(function(p) { p.classList.remove('active'); });
            this.classList.add('active');
            var panel = document.getElementById('tab-' + tab);
            if (panel) panel.classList.add('active');
            if (!loadedTabs[tab]) {
                loadedTabs[tab] = true;
                if (tab === 'odunc') fetchOdunc(1);
                if (tab === 'rezerve') fetchRezerve(1);
                if (tab === 'ziyaret') fetchZiyaret(1);
            }
        });
    });

    // URL hash ile sekme aç
    var hash = (window.location.hash || '').replace('#', '');
    if (hash && document.querySelector('.tab-btn[data-tab="' + hash + '"]')) {
        document.querySelector('.tab-btn[data-tab="' + hash + '"]').click();
    }

    // ── Ödünç tablosu ───────────────────────────────────────────────────────
    var oduncPage = 1;
    function loanBadgeClass(row) {
        if (row.statu === 'iade_edildi') return 'iade';
        if (row.statu === 'kayip') return 'kayip';
        if (row.gecikiyor) return 'gecikti';
        return 'aktif';
    }
    function loanBadgeText(row) {
        if (row.statu === 'iade_edildi') return 'İade edildi';
        if (row.statu === 'kayip') return 'Kayıp';
        if (row.gecikiyor) return 'Gecikmiş (' + row.gecikme_gun + ' gün)';
        if (row.kalan_gun !== null) return 'Aktif (' + row.kalan_gun + ' gün kaldı)';
        return row.statu_label || 'Aktif';
    }
    function buildOduncRows(rows) {
        if (!rows.length) {
            return '<tr><td colspan="6"><div class="empty-state"><p class="empty-title">Ödünç kaydı bulunamadı</p><p>Bu üyeye ait seçilen kriterlere uygun kayıt yok.</p></div></td></tr>';
        }
        return rows.map(function(r) {
            var cover = r.kitap_kapak || ('https://ui-avatars.com/api/?name=' + encodeURIComponent(r.kitap || '') + '&background=7a5c3c&color=fff&size=72');
            return '<tr>' +
                '<td><div class="book-cell">' +
                '<img src="' + esc(cover) + '" alt="" class="book-thumb">' +
                '<div><div class="book-title">' + esc(r.kitap) + '</div>' +
                '<div class="book-sub">' + esc(r.kitap_demir || r.kitap_isbn || '') + '</div></div></div></td>' +
                '<td style="font-size:13px;">' + esc(r.odunc_tarihi) + '</td>' +
                '<td style="font-size:13px;' + (r.gecikiyor ? 'color:var(--destructive);font-weight:600;' : '') + '">' + esc(r.iade_planlanan) + '</td>' +
                '<td><span class="loan-badge ' + loanBadgeClass(r) + '">' + esc(loanBadgeText(r)) + '</span></td>' +
                '<td style="font-size:13px;color:var(--muted-foreground);">' + esc(r.kutuphane) + '</td>' +
                '<td style="text-align:right;"><a href="' + esc(r.detay_url) + '" class="link-btn">Detay →</a></td>' +
                '</tr>';
        }).join('');
    }
    function buildPagination(containerId, meta, onPage) {
        var el = document.getElementById(containerId);
        if (!el || meta.last_page <= 1) { if (el) el.innerHTML = ''; return; }
        var cur = meta.current_page, last = meta.last_page, html = '';
        html += '<button class="page-btn ' + (cur <= 1 ? 'disabled' : '') + '" data-p="' + (cur - 1) + '">‹</button>';
        for (var i = Math.max(1, cur - 2); i <= Math.min(last, cur + 2); i++) {
            html += '<button class="page-btn ' + (i === cur ? 'active' : '') + '" data-p="' + i + '">' + i + '</button>';
        }
        html += '<button class="page-btn ' + (cur >= last ? 'disabled' : '') + '" data-p="' + (cur + 1) + '">›</button>';
        el.innerHTML = html;
        el.querySelectorAll('[data-p]').forEach(function(btn) {
            if (!btn.classList.contains('disabled')) {
                btn.addEventListener('click', function() { onPage(parseInt(this.getAttribute('data-p'), 10)); });
            }
        });
    }
    function fetchOdunc(page) {
        oduncPage = page || 1;
        var statu = document.getElementById('oduncStatuFilter').value;
        document.getElementById('oduncVeil').classList.add('visible');
        fetch(oduncUrl + '?page=' + oduncPage + '&statu=' + encodeURIComponent(statu) + '&per_page=10', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) { return res.json(); })
        .then(function(result) {
            document.getElementById('oduncVeil').classList.remove('visible');
            if (!result.success) return;
            document.getElementById('oduncTableBody').innerHTML = buildOduncRows(result.data || []);
            var m = result.meta;
            document.getElementById('oduncRangeInfo').textContent = m.from + '–' + m.to + ' / ' + m.total + ' kayıt';
            buildPagination('oduncPagination', m, fetchOdunc);
        })
        .catch(function() {
            document.getElementById('oduncVeil').classList.remove('visible');
        });
    }
    if (canViewLoans) {
        var oduncFilter = document.getElementById('oduncStatuFilter');
        if (oduncFilter) oduncFilter.addEventListener('change', function() { fetchOdunc(1); });
    }

    // ── Rezervasyon tablosu ───────────────────────────────────────────────────
    function buildRezerveRows(rows) {
        if (!rows.length) {
            return '<tr><td colspan="6"><div class="empty-state"><p class="empty-title">Rezervasyon bulunamadı</p><p>Bu üyeye ait seçilen kriterlere uygun kayıt yok.</p></div></td></tr>';
        }
        return rows.map(function(r) {
            var cover = r.kitap_kapak || ('https://ui-avatars.com/api/?name=' + encodeURIComponent(r.kitap || '') + '&background=7a5c3c&color=fff&size=72');
            var action = r.odunc_yapilabilir && r.odunc_new_url
                ? '<a href="' + esc(r.odunc_new_url) + '" class="link-btn">Ödünç Ver →</a>'
                : '';
            return '<tr>' +
                '<td><div class="book-cell">' +
                '<img src="' + esc(cover) + '" alt="" class="book-thumb">' +
                '<div><div class="book-title">' + esc(r.kitap) + '</div>' +
                '<div class="book-sub">' + esc(r.kitap_demir || r.kitap_isbn || '') + '</div></div></div></td>' +
                '<td style="font-size:13px;">' + esc(r.rezerve_baslangic) + '</td>' +
                '<td style="font-size:13px;">' + esc(r.rezerve_bitis) + '</td>' +
                '<td><span class="rez-badge">' + esc(r.durum_etiket) + '</span></td>' +
                '<td style="font-size:13px;color:var(--muted-foreground);">' + esc(r.kutuphane) + '</td>' +
                '<td style="text-align:right;">' + action + '</td>' +
                '</tr>';
        }).join('');
    }
    function fetchRezerve(page) {
        var filtre = document.getElementById('rezerveFiltre').value;
        document.getElementById('rezerveVeil').classList.add('visible');
        fetch(rezerveUrl + '?page=' + (page || 1) + '&filtre=' + encodeURIComponent(filtre) + '&per_page=10', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) { return res.json(); })
        .then(function(result) {
            document.getElementById('rezerveVeil').classList.remove('visible');
            if (!result.success) return;
            document.getElementById('rezerveTableBody').innerHTML = buildRezerveRows(result.data || []);
            var m = result.meta;
            document.getElementById('rezerveRangeInfo').textContent = m.from + '–' + m.to + ' / ' + m.total + ' kayıt';
            buildPagination('rezervePagination', m, fetchRezerve);
        })
        .catch(function() {
            document.getElementById('rezerveVeil').classList.remove('visible');
        });
    }
    if (canViewRezerve) {
        var rezFilter = document.getElementById('rezerveFiltre');
        if (rezFilter) rezFilter.addEventListener('change', function() { fetchRezerve(1); });
    }

    // ── Ziyaret geçmişi tablosu ───────────────────────────────────────────────
    function buildZiyaretRows(rows) {
        if (!rows.length) {
            return '<tr><td colspan="6"><div class="empty-state"><p class="empty-title">Ziyaret kaydı bulunamadı</p><p>Bu üyeye ait seçilen kriterlere uygun kayıt yok.</p></div></td></tr>';
        }
        return rows.map(function(r) {
            var durum = r.icinde_mi
                ? '<span class="ziy-badge icinde">İçeride</span>'
                : '<span class="ziy-badge cikis">Çıktı</span>';
            var not = r.notlar
                ? '<span title="' + esc(r.notlar) + '" style="font-size:13px;color:var(--muted-foreground);max-width:200px;display:inline-block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + esc(r.notlar) + '</span>'
                : '—';
            return '<tr>' +
                '<td style="font-size:13px;">' + esc(r.kutuphane) + '</td>' +
                '<td style="font-size:13px;white-space:nowrap;">' + esc(r.giris_saati) + '</td>' +
                '<td style="font-size:13px;white-space:nowrap;">' + (r.cikis_saati ? esc(r.cikis_saati) : '—') + '</td>' +
                '<td style="font-size:13px;">' + esc(r.sure_label) + '</td>' +
                '<td>' + durum + '</td>' +
                '<td>' + not + '</td>' +
                '</tr>';
        }).join('');
    }
    function fetchZiyaret(page) {
        var filtre = document.getElementById('ziyaretFiltre').value;
        document.getElementById('ziyaretVeil').classList.add('visible');
        fetch(ziyaretUrl + '?page=' + (page || 1) + '&filtre=' + encodeURIComponent(filtre) + '&per_page=10', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) { return res.json(); })
        .then(function(result) {
            document.getElementById('ziyaretVeil').classList.remove('visible');
            if (!result.success) return;
            document.getElementById('ziyaretTableBody').innerHTML = buildZiyaretRows(result.data || []);
            var m = result.meta;
            document.getElementById('ziyaretRangeInfo').textContent = m.from + '–' + m.to + ' / ' + m.total + ' kayıt';
            buildPagination('ziyaretPagination', m, fetchZiyaret);
        })
        .catch(function() {
            document.getElementById('ziyaretVeil').classList.remove('visible');
        });
    }
    if (canViewZiyaret) {
        var ziyFilter = document.getElementById('ziyaretFiltre');
        if (ziyFilter) ziyFilter.addEventListener('change', function() { fetchZiyaret(1); });
    }
})();
</script>
@endsection
