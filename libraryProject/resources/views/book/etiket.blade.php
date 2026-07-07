@extends('layouts.base')

@section('title', 'Etiket Olustur')

@section('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="{{ asset('css/book-etiket.css') }}?v={{ @filemtime(public_path('css/book-etiket.css')) ?: time() }}">
    @php($bookEtiketCss = @file_get_contents(public_path('css/book-etiket.css')))
    @if($bookEtiketCss)
        <style>{!! $bookEtiketCss !!}</style>
    @endif
@endsection

@section('breadcrumb')
    <nav class="breadcrumb">
        <a href="{{ route('katalog.index') }}" class="breadcrumb-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            Katalog
        </a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Etiket Oluştur</span>
    </nav>
@endsection

@section('content')
<div class="etiket-page">
    <div class="page-header">
                <div class="page-title-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                </div>
                <div>
                    <div class="page-title">Etiket Oluştur</div>
                    <div class="page-subtitle">Kitapları seçin, etiket tipini belirleyin ve PDF oluşturun.</div>
                </div>
            </div>

            <div class="etiket-layout">

                {{-- ── Sol Panel ──────────────────────────────────────────────── --}}
                <div class="left-panel">

                    {{-- 1. Kitap Arama --}}
                    <div class="panel-card">
                        <div class="panel-card-header">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                            <span class="panel-card-header-title">Kitap Ara</span>
                        </div>
                        <div class="panel-card-body" style="display:flex;flex-direction:column;gap:10px;">

                            {{-- Eser adı / ISBN --}}
                            <div class="search-row">
                                <div class="search-wrap">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                                    <input type="text" id="searchInput" class="search-input"
                                           placeholder="Eser adı veya ISBN ile ara…"
                                           autocomplete="off" />
                                </div>
                                <button class="btn btn-ghost btn-sm" onclick="clearAllFilters()" title="Tüm filtreleri temizle">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                </button>
                            </div>

                            {{-- Ek filtreler --}}
                            <div class="filter-grid">
                                {{-- Demirbaş No --}}
                                <div class="filter-field span-2">
                                    <span class="filter-label">Demirbaş No</span>
                                    <input type="text" id="filterDemirbas" class="filter-input"
                                           placeholder="Demirbaş no ile ara…"
                                           autocomplete="off" />
                                </div>

                                {{-- Özel Notlar --}}
                                <div class="filter-field span-2">
                                    <span class="filter-label">Özel Notlar</span>
                                    <div class="ozel-notlar-wrap">
                                        <input type="text" id="filterOzelNotlar" class="filter-input ozel-notlar-input"
                                               placeholder="Notlar ara…"
                                               autocomplete="off" />
                                        <input type="hidden" id="filterOzelNotlarMode" value="contains" />
                                        <button type="button"
                                                id="filterOzelNotlarModeBtn"
                                                class="ozel-notlar-mode-btn"
                                                onclick="cycleOzelNotlarMode()"
                                                title="Özel notlar eşleşme tipi: İçinde Geçen">
                                            %%
                                        </button>
                                    </div>
                                </div>

                                {{-- Kütüphane --}}
                                <div class="filter-field span-2">
                                    <span class="filter-label">Kütüphane</span>
                                    <div class="combobox-wrapper" id="filterKutuphaneCombobox">
                                        <div class="combobox-input-wrap">
                                            <div class="combobox-face" id="filterKutuphaneFace">
                                                <span class="combobox-face-text" id="filterKutuphaneFaceText">Tüm Yetkili Kütüphaneler</span>
                                                <button type="button" class="combobox-clear-btn" id="filterKutuphaneClear" title="Seçimi kaldır" style="display:none;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                                </button>
                                            </div>
                                            <input type="text" class="filter-input" id="filterKutuphaneSearch" placeholder="Kütüphane ara..." autocomplete="off" style="display:none;" />
                                            <button type="button" class="combobox-toggle" tabindex="-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                            </button>
                                        </div>
                                        <div class="combobox-dropdown"></div>
                                    </div>
                                    <input type="hidden" id="filterKutuphaneId" value="" />
                                    <script id="filterKutuphaneData" type="application/json">@json(($kutuphaneler ?? collect())->map(fn($k) => ['id' => $k->id, 'ad' => $k->title])->values())</script>
                                </div>

                                {{-- Kaydeden (arama özellikli) --}}
                                <div class="filter-field span-2">
                                    <span class="filter-label">Kaydeden</span>
                                    <div class="combobox-wrapper" id="filterCreatedUserCombobox">
                                        <div class="combobox-input-wrap">
                                            <div class="combobox-face" id="filterCreatedUserFace">
                                                <span class="combobox-face-text" id="filterCreatedUserFaceText">Kullanıcı seçin...</span>
                                                <button type="button" class="combobox-clear-btn" id="filterCreatedUserClear" title="Seçimi kaldır" style="display:none;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                                </button>
                                            </div>
                                            <input type="text" class="filter-input" id="filterCreatedUserSearch" placeholder="Kullanıcı ara..." autocomplete="off" style="display:none;" />
                                            <button type="button" class="combobox-toggle" tabindex="-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                            </button>
                                        </div>
                                        <div class="combobox-dropdown"></div>
                                    </div>
                                    <input type="hidden" id="filterCreatedUser" value="" />
                                    <script id="filterCreatedUserData" type="application/json">@json($kaydedenler ?? [])</script>
                                </div>

                                {{-- Kayıt Tarihi Aralığı --}}
                                <div class="filter-field span-2">
                                    <span class="filter-label">Kayıt Tarihi Aralığı</span>
                                </div>
                                <div class="filter-field">
                                    <input type="date" id="filterKayitBaslangic" class="filter-input"
                                           title="Başlangıç tarihi"
                                    />
                                </div>
                                <div class="filter-field">
                                    <input type="date" id="filterKayitBitis" class="filter-input"
                                           title="Bitiş tarihi"
                                    />
                                </div>
                            </div>

                            {{-- Etiket oluşmayanlar toggle --}}
                            <label class="filter-toggle-row">
                                <input type="checkbox" id="filterEtiketOlusmayanlar"
                                />
                                <span class="toggle-switch"></span>
                                <span class="toggle-label">Etiket basılmayanlar</span>
                            </label>

                            {{-- Ara Butonu --}}
                            <button class="btn btn-primary" id="btnAra" onclick="runSearch()" style="width:100%;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                                Ara
                            </button>

                            <div class="filter-sep"></div>

                            {{-- Tümünü Seç (arama sonrası görünür) --}}
                            <div id="selectAllRow" style="display:none;gap:8px;align-items:stretch;">
                                <button class="btn btn-ghost" id="btnSelectAll" onclick="selectAllResults()" style="flex:1;justify-content:flex-start;gap:8px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><polyline points="9 11 12 14 22 4"/></svg>
                                    Tümünü Seç
                                    <span id="selectAllCount" style="margin-left:auto;font-size:11px;color:var(--muted-foreground);"></span>
                                </button>
                                <button class="btn btn-ghost" id="btnClearSelection" onclick="clearSelectedBooks()" style="width:auto;flex:0 0 auto;padding:0 12px;justify-content:center;gap:6px;white-space:nowrap;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                    Temizle
                                </button>
                            </div>

                            <div class="results-list" id="resultsList">
                                <div class="result-empty">Arama yapmak için filtre uygulayın.</div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Seçilen Kitaplar --}}
                    <div class="panel-card">
                        <div class="panel-card-header">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                            <span class="panel-card-header-title">Seçilen Kitaplar</span>
                            <span class="panel-card-header-count" id="selectedCount">0 kitap</span>
                        </div>
                        <div class="panel-card-body">
                            <div class="selected-list" id="selectedList">
                                <div class="selected-empty">Henüz kitap seçilmedi.<br>Arama sonuçlarından kitap ekleyin.</div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Etiket Tipi --}}
                    <div class="panel-card">
                        <div class="panel-card-header">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/></svg>
                            <span class="panel-card-header-title">Etiket Tipi</span>
                        </div>
                        <div class="panel-card-body">
                            <div class="label-type-grid">
                                <label class="label-type-card">
                                    <input type="radio" name="etiketTipi" value="tip1" checked />
                                    <div class="label-type-inner">
                                        <div class="label-type-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="5" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="3" y="12" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="5" rx="1"/></svg>
                                        </div>
                                        <div class="label-type-text">
                                            <div class="label-type-name">Tip 1 (Sırt)</div>
                                            <div class="label-type-desc">A4 · 4×9 düzen · 45×30mm · Sınıflama / Kopya / Cilt</div>
                                        </div>
                                        <div class="label-type-radio"></div>
                                    </div>
                                </label>
                                <label class="label-type-card">
                                    <input type="radio" name="etiketTipi" value="tip3" />
                                    <div class="label-type-inner">
                                        <div class="label-type-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="4" height="20" rx="1"/><line x1="8" y1="7" x2="22" y2="7"/><line x1="8" y1="12" x2="22" y2="12"/><line x1="8" y1="17" x2="22" y2="17"/></svg>
                                        </div>
                                        <div class="label-type-text">
                                            <div class="label-type-name">Tip 2 (Sırt Barkod)</div>
                                            <div class="label-type-desc">A4 · 4×9 düzen · 45×30mm · Demirbaş / Sınıf / Kopya / Cilt</div>
                                        </div>
                                        <div class="label-type-radio"></div>
                                    </div>
                                </label>
                                <label class="label-type-card">
                                    <input type="radio" name="etiketTipi" value="tip2" />
                                    <div class="label-type-inner">
                                        <div class="label-type-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="5" rx="1"/><rect x="2" y="13" width="20" height="5" rx="1"/><line x1="6" y1="8" x2="6" y2="8.01"/><line x1="6" y1="17" x2="6" y2="17.01"/></svg>
                                        </div>
                                        <div class="label-type-text">
                                            <div class="label-type-name">Tip 3 (Arka Barkod)</div>
                                            <div class="label-type-desc">A4 · 4×9 düzen · 45×30mm · Kütüphane / Demirbaş / Kitap Adı</div>
                                        </div>
                                        <div class="label-type-radio"></div>
                                    </div>
                                </label>
                                
                                <label class="label-type-card">
                                    <input type="radio" name="etiketTipi" value="tip4" />
                                    <div class="label-type-inner">
                                        <div class="label-type-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="9" height="18" rx="1"/><rect x="13" y="3" width="9" height="18" rx="1"/><line x1="6" y1="8" x2="6" y2="8.01"/><line x1="18" y1="8" x2="18" y2="8.01"/></svg>
                                        </div>
                                        <div class="label-type-text">
                                            <div class="label-type-name">Tip 4 (İkili Barkod)</div>
                                            <div class="label-type-desc">A4 · 4×9 düzen · Tip 2 + Tip 3 içerik</div>
                                        </div>
                                        <div class="label-type-radio"></div>
                                    </div>
                                </label>
                                <label class="label-type-card">
                                    <input type="radio" name="etiketTipi" value="tip8" />
                                    <div class="label-type-inner">
                                        <div class="label-type-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="6" height="18" rx="1"/><rect x="9" y="3" width="6" height="18" rx="1"/><rect x="16" y="3" width="6" height="18" rx="1"/><line x1="5" y1="8" x2="5" y2="8.01"/><line x1="12" y1="8" x2="12" y2="8.01"/><line x1="19" y1="8" x2="19" y2="8.01"/></svg>
                                        </div>
                                        <div class="label-type-text">
                                            <div class="label-type-name">Tip 8 (Üçlü Barkod)</div>
                                            <div class="label-type-desc">A4 · 4×9 düzen · 2× Tip 2 + 1× Tip 3 içerik</div>
                                        </div>
                                        <div class="label-type-radio"></div>
                                    </div>
                                </label>
                                <label class="label-type-card">
                                    <input type="radio" name="etiketTipi" value="tip7" />
                                    <div class="label-type-inner">
                                        <div class="label-type-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><rect x="2" y="6" width="6" height="12" rx="1"/><line x1="10" y1="10" x2="20" y2="10"/><line x1="10" y1="14" x2="20" y2="14"/></svg>
                                        </div>
                                        <div class="label-type-text">
                                            <div class="label-type-name">Tip 5 (Ribon Sırt Barkod)</div>
                                            <div class="label-type-desc">Ribonlu yazıcı · 60×40mm · Tip 3 içerik</div>
                                        </div>
                                        <div class="label-type-radio"></div>
                                    </div>
                                </label>
                                <label class="label-type-card">
                                    <input type="radio" name="etiketTipi" value="tip5" />
                                    <div class="label-type-inner">
                                        <div class="label-type-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><line x1="6" y1="10" x2="6" y2="10.01"/><line x1="6" y1="14" x2="18" y2="14"/><line x1="6" y1="10" x2="18" y2="10"/></svg>
                                        </div>
                                        <div class="label-type-text">
                                            <div class="label-type-name">Tip 6 (Ribon Arka Barkod)</div>
                                            <div class="label-type-desc">Ribonlu yazıcı · 60×40mm · Tip 2 içerik</div>
                                        </div>
                                        <div class="label-type-radio"></div>
                                    </div>
                                </label>
                                <label class="label-type-card">
                                    <input type="radio" name="etiketTipi" value="tip6" />
                                    <div class="label-type-inner">
                                        <div class="label-type-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="9" height="12" rx="1"/><rect x="13" y="6" width="9" height="12" rx="1"/><line x1="5" y1="10" x2="5" y2="10.01"/><line x1="16" y1="10" x2="16" y2="10.01"/></svg>
                                        </div>
                                        <div class="label-type-text">
                                            <div class="label-type-name">Tip 7 (Ribon İkili Barkod)</div>
                                            <div class="label-type-desc">Ribonlu yazıcı · 60×40mm · Tip 2 + Tip 3 içerik</div>
                                        </div>
                                        <div class="label-type-radio"></div>
                                    </div>
                                </label>
                                
                            </div>
                        </div>
                    </div>

                    {{-- 4. Etiket Kaydır + Oluştur --}}
                    <div class="generate-area">

                        {{-- Kaydır toggle --}}
                        <label class="filter-toggle-row" style="margin-bottom:10px;">
                            <input type="checkbox" id="chkSkip" onchange="toggleSkipInput()" />
                            <span class="toggle-switch"></span>
                            <span class="toggle-label">Etiket kaydır</span>
                        </label>

                        {{-- Kaydır sayı girişi (başlangıçta gizli) --}}
                        <div id="skipInputRow" style="display:none;margin-bottom:10px;">
                            <div class="filter-field">
                                <span class="filter-label">Kaydırılacak etiket sayısı</span>
                                <input type="number" id="skipCount" class="filter-input"
                                       min="1" max="35" value="1"
                                       placeholder="Kaç adet boş etiket?" />
                            </div>
                        </div>

                        <button class="btn btn-primary" id="btnGenerate" onclick="generatePDF()" style="width:100%;padding:11px;font-size:14px;" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                            Etiket PDF Oluştur
                        </button>
                    </div>

                </div>

                {{-- ── Sağ Panel — PDF Görüntüleyici ──────────────────────────── --}}
                <div class="right-panel">
                    <div class="pdf-panel">
                        <div class="pdf-panel-header">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                            <span class="pdf-panel-header-title">PDF Önizleme</span>
                            <div class="pdf-panel-actions">
                                <button class="btn btn-outline btn-sm" id="btnDownload" onclick="downloadPDF()" style="display:none;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                                    İndir
                                </button>
                                <button class="btn btn-ghost btn-sm" id="btnPrint" onclick="printPDF()" style="display:none;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                                    Yazdır
                                </button>
                                <button class="btn btn-primary btn-sm" id="btnMarkLabeled" onclick="markAsLabeled()" style="display:none;">
                                    Etiketlendi Olarak İşaretle
                                </button>
                            </div>
                        </div>
                        <div class="pdf-viewer-wrap">
                            <div class="pdf-placeholder" id="pdfPlaceholder">
                                <div class="pdf-placeholder-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                                </div>
                                <div class="pdf-placeholder-text">PDF henüz oluşturulmadı</div>
                                <div class="pdf-placeholder-sub">Sol panelden kitap seçip "Etiket PDF Oluştur" butonuna tıklayın.</div>
                            </div>
                            <div class="loading-overlay" id="loadingOverlay">
                                <div class="loading-spinner"></div>
                                <div class="loading-text">PDF oluşturuluyor…</div>
                            </div>
                            <iframe id="pdfFrame" title="Etiket PDF Önizleme"></iframe>
                        </div>
                    </div>
                </div>

            </div>{{-- / etiket-layout --}}
</div>

<div class="toast-container" id="toastContainer"></div>

{{-- jsPDF CDN --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
{{-- JsBarcode (Code 39 / Tip 2 barkod) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.6/JsBarcode.all.min.js"></script>

@endsection

@section('scripts')
<script>

    // ── Toast ─────────────────────────────────────────────────────────────────────
    function showToast(type, title, desc) {
        var c = document.getElementById('toastContainer');
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<div>' + title + '</div>' + (desc ? '<div class="toast-desc">' + desc + '</div>' : '');
        c.appendChild(t);
        setTimeout(function() {
            t.style.animation = 'toast-out .3s ease forwards';
            setTimeout(function() { if (t.parentNode) t.parentNode.removeChild(t); }, 300);
        }, 4500);
    }

    // ── State ─────────────────────────────────────────────────────────────────────
    var selectedBooks  = [];   // { id, title, siniflamaYer, kopya, cilt }
    var searchTimeout  = null;
    var lastPdfBlob    = null;
    var lastPdfUrl     = null;
    var generatedBookIds = [];

    var ozelNotlarMatchModes = ['contains', 'starts_with', 'exact'];

    function modeSymbolFor(mode) {
        if (mode === 'starts_with') return '%';
        if (mode === 'exact') return '=';
        return '%%';
    }

    function modeTooltipFor(mode) {
        if (mode === 'starts_with') return 'İle başlayanları arar';
        if (mode === 'exact') return 'Tam eşleşenleri arar';
        return 'İçinde geçenleri arar';
    }

    function syncOzelNotlarModeButton() {
        var modeEl = document.getElementById('filterOzelNotlarMode');
        var btnEl  = document.getElementById('filterOzelNotlarModeBtn');
        if (!modeEl || !btnEl) return;
        var mode = modeEl.value || 'contains';
        btnEl.textContent = modeSymbolFor(mode);
        btnEl.title = modeTooltipFor(mode);
    }

    function cycleOzelNotlarMode() {
        var modeEl = document.getElementById('filterOzelNotlarMode');
        if (!modeEl) return;
        var currentIdx = ozelNotlarMatchModes.indexOf(modeEl.value);
        var nextIdx = (currentIdx + 1) % ozelNotlarMatchModes.length;
        modeEl.value = ozelNotlarMatchModes[nextIdx];
        syncOzelNotlarModeButton();
    }

    // ── Tahoma fontlar ──────────────────────────────────────────────────────────────
    // /fonts/tahoma.ttf (normal), /fonts/tahomabd.ttf (tercih edilen bold),
    // /fonts/tahoma-semibold.ttf (opsiyonel fallback)
    var tahomaB64 = null;           // normal
    var tahomaSemiBoldB64 = null;   // semi-bold (opsiyonel, fallback)
    var tahomaBoldB64 = null;       // bold (opsiyonel, öncelikli)

    function bufferToB64(buf) {
        var bytes  = new Uint8Array(buf);
        var binary = '';
        var CHUNK  = 8192;
        for (var i = 0; i < bytes.length; i += CHUNK) {
            binary += String.fromCharCode.apply(null, bytes.subarray(i, i + CHUNK));
        }
        return btoa(binary);
    }

    function loadFontB64(url) {
        return fetch(url)
            .then(function(r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.arrayBuffer();
            })
            .then(bufferToB64);
    }

    (function loadTahomaFonts() {
        loadFontB64('/fonts/tahoma.ttf')
            .then(function(b64) {
                tahomaB64 = b64;
            })
            .catch(function(e) {
                console.warn('Tahoma yüklenemedi:', e.message,
                    '— /fonts/tahoma.ttf dosyasının var olduğundan emin olun.');
            });

        loadFontB64('/fonts/tahoma-semibold.ttf')
            .then(function(b64) {
                tahomaSemiBoldB64 = b64;
            })
            .catch(function() {
                // Semi-bold opsiyonel.
            });

        loadFontB64('/fonts/tahomabd.ttf')
            .then(function(b64) {
                tahomaBoldB64 = b64;
            })
            .catch(function() {
                // Bold font opsiyonel: yoksa normal font bold style adıyla kaydedilir.
            });
    })();

    function registerTahomaFonts(doc) {
        if (!tahomaB64) {
            throw new Error(
                'Tahoma fontu henüz yüklenmedi.\n' +
                '/fonts/tahoma.ttf dosyasının var olduğundan emin olun.'
            );
        }

        doc.addFileToVFS('tahoma.ttf', tahomaB64);
        doc.addFont('tahoma.ttf', 'tahoma', 'normal');

        if (tahomaBoldB64) {
            doc.addFileToVFS('tahomabd.ttf', tahomaBoldB64);
            doc.addFont('tahomabd.ttf', 'tahoma', 'bold');
        } else if (tahomaSemiBoldB64) {
            doc.addFileToVFS('tahoma-semibold.ttf', tahomaSemiBoldB64);
            doc.addFont('tahoma-semibold.ttf', 'tahoma', 'bold');
        } else {
            doc.addFont('tahoma.ttf', 'tahoma', 'bold');
        }
    }

    // ── Search & Filters ─────────────────────────────────────────────────────────
    var lastSearchRows = [];   // arama sonucundaki tüm kayıtlar (Tümünü Seç için)

    function clearAllFilters() {
        document.getElementById('searchInput').value             = '';
        document.getElementById('filterDemirbas').value          = '';
        document.getElementById('filterOzelNotlar').value        = '';
        document.getElementById('filterOzelNotlarMode').value    = 'contains';
        syncOzelNotlarModeButton();
        document.getElementById('filterKutuphaneId').value       = '';
        resetComboboxFace('filterKutuphaneFaceText', 'filterKutuphaneClear', 'Tüm Yetkili Kütüphaneler');
        document.getElementById('filterCreatedUser').value       = '';
        resetComboboxFace('filterCreatedUserFaceText', 'filterCreatedUserClear', 'Kullanıcı seçin...');
        document.getElementById('filterKayitBaslangic').value    = '';
        document.getElementById('filterKayitBitis').value        = '';
        document.getElementById('filterEtiketOlusmayanlar').checked = false;
        lastSearchRows = [];
        renderResults([]);
        document.getElementById('selectAllRow').style.display = 'none';
    }

    function hasAnyFilter() {
        return document.getElementById('searchInput').value.trim().length > 0
            || document.getElementById('filterDemirbas').value.trim().length > 0
            || document.getElementById('filterOzelNotlar').value.trim().length > 0
            || document.getElementById('filterKutuphaneId').value.trim().length > 0
            || document.getElementById('filterCreatedUser').value.trim().length > 0
            || document.getElementById('filterKayitBaslangic').value.trim().length > 0
            || document.getElementById('filterKayitBitis').value.trim().length > 0
            || document.getElementById('filterEtiketOlusmayanlar').checked;
    }

    function runSearch() {
        if (!hasAnyFilter()) {
            showToast('info', 'Filtre gerekli', 'Lütfen en az bir arama kriteri girin.');
            return;
        }

        // Ara butonunu yükleniyor moduna al
        var btn = document.getElementById('btnAra');
        btn.disabled = true;
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin .6s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Aranıyor…';

        var params = new URLSearchParams();
        var q = document.getElementById('searchInput').value.trim();
        if (q)  params.set('search', q);
        var dm = document.getElementById('filterDemirbas').value.trim();
        if (dm) params.set('demirbasNo', dm);
        var on = document.getElementById('filterOzelNotlar').value.trim();
        if (on) {
            params.set('ozelNotlar', on);
            params.set('ozelNotlarMatch', document.getElementById('filterOzelNotlarMode').value);
        }
        var kt = document.getElementById('filterKutuphaneId').value.trim();
        if (kt) params.set('kutuphaneId', kt);
        var cu = document.getElementById('filterCreatedUser').value.trim();
        if (cu) params.set('createdUserId', cu);
        var kb = document.getElementById('filterKayitBaslangic').value;
        if (kb) params.set('kayitBaslangic', kb);
        var ke = document.getElementById('filterKayitBitis').value;
        if (ke) params.set('kayitBitis', ke);
        var eo = document.getElementById('filterEtiketOlusmayanlar').checked;
        if (eo) params.set('etiketOlusmayanlar', '1');
        params.set('per_page', '540');

        fetch('/etiket/ara?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                lastSearchRows = data.rows || [];
                renderResults(lastSearchRows);
            })
            .catch(function() {
                lastSearchRows = [];
                renderResults([]);
                showToast('error', 'Bağlantı hatası', 'Arama sırasında bir hata oluştu.');
            })
            .finally(function() {
                btn.disabled = false;
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg> Ara';
            });
    }

    function renderResults(rows) {
        var el           = document.getElementById('resultsList');
        var selectAllRow = document.getElementById('selectAllRow');
        var countEl      = document.getElementById('selectAllCount');

        if (!rows.length) {
            el.innerHTML = '<div class="result-empty">Sonuç bulunamadı.</div>';
            selectAllRow.style.display = 'none';
            return;
        }

        // Tümünü Seç satırını göster
        selectAllRow.style.display = 'flex';
        countEl.textContent = rows.length + ' kayıt';

        el.innerHTML = rows.map(function(k) {
            var isSelected = selectedBooks.some(function(b) { return b.id === k.id; });
            return '<div class="result-item' + (isSelected ? ' selected' : '') + '" onclick="toggleBook(' + JSON.stringify(k).replace(/"/g, '&quot;') + ')" data-id="' + k.id + '">' +
                '<div class="result-check">' +
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>' +
                '</div>' +
                '<div class="result-info">' +
                '<div class="result-title">' + escHtml(k.kunyeEserAdi || '—') + '</div>' +
                '<div class="result-meta">' + escHtml(k.kunyeYazar || '—') + ' · ' + escHtml(k.kunyeISBNISSN || '—') + '</div>' +
                '</div>' +
                '<div class="result-badge">#' + k.id + '</div>' +
                '</div>';
        }).join('');
    }

    function selectAllResults() {
        if (!lastSearchRows.length) return;

        lastSearchRows.forEach(function(k) {
            var alreadySelected = selectedBooks.some(function(b) { return b.id === k.id; });
            if (!alreadySelected) {
                selectedBooks.push({
                    id:           k.id,
                    title:        k.kunyeEserAdi       || '',
                    siniflamaYer: k.kunyeSiniflamaYer  || '',
                    yayinTarihi:  k.kunyeYayinTarihi   || '',
                    kopya:        k.kunyeKopya         || '',
                    cilt:         k.kunyeCilt          || '',
                    kutuphaneAdi: k.kutuphaneAdi       || '',
                    demirbasKN:   k.kunyeDemirbasKN    || ''
                });
            }
        });

        // Sonuç listesindeki tüm öğeleri seçili göster
        document.querySelectorAll('#resultsList .result-item').forEach(function(el) {
            el.classList.add('selected');
        });

        renderSelectedBooks();
        showToast('success', 'Tümü seçildi', lastSearchRows.length + ' kayıt seçime eklendi.');
    }

    function clearSelectedBooks() {
        if (!selectedBooks.length) {
            showToast('info', 'Seçim yok', 'Temizlenecek seçili kitap bulunmuyor.');
            return;
        }

        var removedCount = selectedBooks.length;
        selectedBooks = [];

        // Sonuç listesindeki seçili vurgularını kaldır
        document.querySelectorAll('#resultsList .result-item.selected').forEach(function(el) {
            el.classList.remove('selected');
        });

        renderSelectedBooks();
        showToast('success', 'Seçim temizlendi', removedCount + ' kayıt seçimden çıkarıldı.');
    }

    function toggleBook(k) {
        var idx = selectedBooks.findIndex(function(b) { return b.id === k.id; });
        if (idx === -1) {
            selectedBooks.push({
                id:           k.id,
                title:        k.kunyeEserAdi      || '',
                siniflamaYer: k.kunyeSiniflamaYer  || '',
                yayinTarihi:  k.kunyeYayinTarihi   || '',
                kopya:        k.kunyeKopya         || '',
                cilt:         k.kunyeCilt          || '',
                kutuphaneAdi: k.kutuphaneAdi       || '',
                demirbasKN:   k.kunyeDemirbasKN    || ''
            });
        } else {
            selectedBooks.splice(idx, 1);
        }
        // refresh the result list item state without full re-render
        var item = document.querySelector('.result-item[data-id="' + k.id + '"]');
        if (item) {
            item.classList.toggle('selected', idx === -1);
        }
        renderSelectedBooks();
    }

    function renderSelectedBooks() {
        var el    = document.getElementById('selectedList');
        var count = document.getElementById('selectedCount');
        count.textContent = selectedBooks.length + ' kitap';

        if (!selectedBooks.length) {
            el.innerHTML = '<div class="selected-empty">Henüz kitap seçilmedi.<br>Arama sonuçlarından kitap ekleyin.</div>';
            document.getElementById('btnGenerate').disabled = true;
            return;
        }

        el.innerHTML = selectedBooks.map(function(b, i) {
            return '<div class="selected-item">' +
                '<div class="selected-item-info">' +
                '<div class="selected-item-title">' + escHtml(b.title) + '</div>' +
                '<div class="selected-item-sub">' +
                escHtml(b.siniflamaYer || '—') + ' · ' + escHtml(b.yayinTarihi || '—') + ' · ' + escHtml(buildKCLine(b.kopya, b.cilt) || '—') +
                '</div>' +
                '</div>' +
                '<button class="selected-item-remove" onclick="removeBook(' + i + ')" title="Çıkar">' +
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>' +
                '</button>' +
                '</div>';
        }).join('');

        document.getElementById('btnGenerate').disabled = false;
    }

    function removeBook(idx) {
        var bookId = selectedBooks[idx].id;
        selectedBooks.splice(idx, 1);
        // deselect in results list if visible
        var item = document.querySelector('.result-item[data-id="' + bookId + '"]');
        if (item) item.classList.remove('selected');
        renderSelectedBooks();
    }

    // ── PDF Generation (jsPDF) ───────────────────────────────────────────────────
    /*
     * Tip 1 — A4, 4 sütun × 9 satır, 45mm × 30mm etiket
     *
     * Kağıt özellikleri (gerçek ölçüler):
     *   Sol/Sağ kenar  : 4.5 mm
     *   Üst kenar      : 14 mm
     *   Etiket genişlik: 45 mm  →  4×45 = 180 mm
     *   Yatay boşluk   : (210 - 4.5 - 4.5 - 180) / 3 = 7 mm (etiketler arası)
     *   Dikey boşluk   : 0 mm (satırlar bitişik)
     *
     * Her etiket (tümü bold, ortalı, çerçevesiz):
     *   kunyeSiniflamaYer → "/" öncesini \n ile satırlara böl (ör: "Ç\nT813.42")
     *                     → "/" sonrası ayrı satır
     *   kunyeYayinTarihi  → ayrı satır
     *   k.X veya k.X/c.X  → ayrı satır
     *
     * Satırlar arasında ek boşluk yoktur. Metin bloğu etiket içinde dikey ortalanır.
     */
    var TIP1 = {
        cols:       4,
        rows:       9,
        labelW:     45,     // mm
        labelH:     30,     // mm
        marginLeft: 4.5,    // mm  — sol kenar boşluğu
        gapX:       7,      // mm  — etiketler arası yatay boşluk
        marginTop:  14,     // mm  — üst kenar boşluğu
        gapY:       0,      // mm  — satırlar arası boşluk (yok)
        FONT_SIZE:  12,      // pt
        LINE_H_MM:  12 * 0.3528 * 1.00   // ~4.234 mm
    };

    // ── Kaydır toggle ────────────────────────────────────────────────────────────
    function toggleSkipInput() {
        var chk = document.getElementById('chkSkip');
        var row = document.getElementById('skipInputRow');
        row.style.display = chk.checked ? 'block' : 'none';
        if (chk.checked) {
            document.getElementById('skipCount').focus();
        }
    }

    function generatePDF() {
        if (!selectedBooks.length) {
            showToast('error', 'Kitap seçilmedi', 'Lütfen önce kitap ekleyin.');
            return;
        }

        var tipEl = document.querySelector('input[name="etiketTipi"]:checked');
        var tip   = tipEl ? tipEl.value : 'tip1';

        // ── Kaydır (skip) ──────────────────────────────────────────────────────
        var skip = 0;
        if (document.getElementById('chkSkip').checked) {
            skip = parseInt(document.getElementById('skipCount').value, 10) || 0;
            if (skip < 0) skip = 0;
        }

        document.getElementById('loadingOverlay').classList.add('active');

        setTimeout(function() {
            try {
                var pdf;
                var perPage;
                if (tip === 'tip2') {
                    pdf     = buildTip2PDF(selectedBooks, skip);
                    perPage = TIP2.cols * TIP2.rows;
                } else if (tip === 'tip3') {
                    pdf     = buildTip3PDF(selectedBooks, skip);
                    perPage = TIP3.cols * TIP3.rows;
                } else if (tip === 'tip4') {
                    pdf     = buildTip4PDF(selectedBooks, skip);
                    perPage = TIP2.cols * TIP2.rows;
                } else if (tip === 'tip8') {
                    pdf     = buildTip8PDF(selectedBooks, skip);
                    perPage = TIP2.cols * TIP2.rows;
                } else if (tip === 'tip5') {
                    pdf     = buildTip5PDF(selectedBooks, skip);
                    perPage = 1;   // Ribonlu yazıcı: sayfa başına 1 etiket
                } else if (tip === 'tip6') {
                    pdf     = buildTip6PDF(selectedBooks, skip);
                    perPage = 1;   // Ribonlu yazıcı: kitap başına 2 sayfa (Tip2+Tip3)
                } else if (tip === 'tip7') {
                    pdf     = buildTip7PDF(selectedBooks, skip);
                    perPage = 1;   // Ribonlu yazıcı: sayfa başına 1 etiket
                } else {
                    pdf     = buildTip1PDF(selectedBooks, skip);
                    perPage = TIP1.cols * TIP1.rows;
                }

                lastPdfBlob = pdf.output('blob');
                if (lastPdfUrl) URL.revokeObjectURL(lastPdfUrl);
                lastPdfUrl  = URL.createObjectURL(lastPdfBlob);

                var frame = document.getElementById('pdfFrame');
                frame.src = lastPdfUrl + '#toolbar=1&navpanes=0';
                frame.style.display = 'block';
                document.getElementById('pdfPlaceholder').style.display = 'none';
                document.getElementById('btnDownload').style.display = '';
                document.getElementById('btnPrint').style.display = '';
                document.getElementById('btnMarkLabeled').style.display = '';
                generatedBookIds = selectedBooks.map(function(b) { return b.id; });

                var skipNote = skip > 0 ? ' · ' + skip + ' boş etiket' : '';
                var pageCalcCount = (tip === 'tip4') ? selectedBooks.length * 2
                                  : (tip === 'tip8') ? selectedBooks.length * 3
                                  : (tip === 'tip6') ? selectedBooks.length * 2
                                  : selectedBooks.length;
                var perPageForToast = (tip === 'tip5' || tip === 'tip6') ? 1 : perPage;
                showToast('success', 'PDF oluşturuldu',
                    selectedBooks.length + ' kitap · ' +
                    (pageCalcCount + skip) + ' sayfa' + skipNote);
            } catch(e) {
                showToast('error', 'PDF hatası', e.message);
                console.error(e);
            } finally {
                document.getElementById('loadingOverlay').classList.remove('active');
            }
        }, 50);

window.scrollTo({
  top: 0,
  behavior: 'smooth'
});

    }

    function buildTip1PDF(books, skip) {
        skip = skip || 0;
        var cfg      = TIP1;
        var jsPDF    = window.jspdf.jsPDF;
        var doc      = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });

        registerTahomaFonts(doc);

        var perPage  = cfg.cols * cfg.rows;
        var total    = skip + books.length;
        var pageCount = Math.ceil(total / perPage);

        for (var page = 0; page < pageCount; page++) {
            if (page > 0) doc.addPage();
            for (var i = 0; i < perPage; i++) {
                var slot = page * perPage + i;
                if (slot >= total) break;
                var col  = i % cfg.cols;
                var row  = Math.floor(i / cfg.cols);
                var lx   = cfg.marginLeft + col * (cfg.labelW + cfg.gapX);
                var ly   = cfg.marginTop  + row * (cfg.labelH + cfg.gapY);
                // TEST: tüm hücreler için gri arka plan (boş slotlar dahil)
                //doc.setFillColor(220, 220, 220);
                //doc.rect(lx, ly, cfg.labelW, cfg.labelH, 'F');
                if (slot < skip) continue;          // boş etiket — atla
                drawLabel(
                    doc,
                    books[slot - skip],
                    lx,
                    ly,
                    cfg.labelW,
                    cfg.labelH
                );
            }
        }

        return doc;
    }

    // ─────────────────────────────────────────────────────────────────────────────
    /*
     * Tip 4 — Kitap başına Tip 2 + Tip 3 etiketi yan yana
     *
     * Sayfa düzeni Tip 2/3 ile aynı (A4, 4×9, 45×30mm).
     * Her kitap için ardışık iki slot kullanılır:
     *   Çift slot (0,2,4…) → Tip 2 (Kütüphane / Demirbaş barkod / Kitap adı)
     *   Tek slot  (1,3,5…) → Tip 3 (Demirbaş dikey barkod / Sınıflama)
     *
     * 4 sütunlu grid'de iki ardışık slot aynı satırda yan yana düşer.
     * Örnek: 8 kitap → 16 slot → sütun dizilimi: [T2 T3 T2 T3 | T2 T3 T2 T3 | …]
     */
    function buildTip4PDF(books, skip) {
        skip = skip || 0;
        var cfg       = TIP2;   // Aynı sayfa ızgarası
        var jsPDF     = window.jspdf.jsPDF;
        var doc       = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });

        registerTahomaFonts(doc);

        var perPage    = cfg.cols * cfg.rows;          // 36
        // Her kitap 2 slot kullanır (1 × Tip2 + 1 × Tip3)
        var totalSlots = skip + books.length * 2;
        var pageCount  = Math.ceil(totalSlots / perPage);

        for (var page = 0; page < pageCount; page++) {
            if (page > 0) doc.addPage();
            for (var i = 0; i < perPage; i++) {
                var slot = page * perPage + i;
                if (slot >= totalSlots) break;
                if (slot < skip) continue;             // boş etiket — atla

                var bookSlot  = slot - skip;           // 0-tabanlı, kitap çiftleri
                var bookIdx   = Math.floor(bookSlot / 2);
                var labelType = bookSlot % 2;          // 0 = Tip 2, 1 = Tip 3

                var col = i % cfg.cols;
                var row = Math.floor(i / cfg.cols);
                var lx  = cfg.marginLeft + col * (cfg.labelW + cfg.gapX);
                var ly  = cfg.marginTop  + row * (cfg.labelH + cfg.gapY);

                var book = books[bookIdx];
                if (labelType === 0) {
                    drawLabel2(doc, book, lx, ly, cfg.labelW, cfg.labelH);
                } else {
                    drawLabel3(doc, book, lx, ly, cfg.labelW, cfg.labelH);
                }
            }
        }

        return doc;
    }

    // ─────────────────────────────────────────────────────────────────────────────
    /*
     * Tip 8 — Kitap başına 3 etiket: 2× Tip 2 (arka barkod) + 1× Tip 3 (sırt barkod)
     *
     * Sayfa düzeni Tip 2/3 ile aynı (A4, 4×9, 45×30mm).
     * Her kitap için ardışık üç slot kullanılır:
     *   Slot 0 → Tip 2 (Arka barkod)
     *   Slot 1 → Tip 2 (Arka barkod)
     *   Slot 2 → Tip 3 (Sırt barkod)
     */
    function buildTip8PDF(books, skip) {
        skip = skip || 0;
        var cfg       = TIP2;   // Aynı sayfa ızgarası
        var jsPDF     = window.jspdf.jsPDF;
        var doc       = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });

        registerTahomaFonts(doc);

        var perPage    = cfg.cols * cfg.rows;          // 36
        var totalSlots = skip + books.length * 3;      // Her kitap 3 slot
        var pageCount  = Math.ceil(totalSlots / perPage);

        for (var page = 0; page < pageCount; page++) {
            if (page > 0) doc.addPage();
            for (var i = 0; i < perPage; i++) {
                var slot = page * perPage + i;
                if (slot >= totalSlots) break;
                if (slot < skip) continue;             // boş etiket — atla

                var bookSlot   = slot - skip;
                var bookIdx    = Math.floor(bookSlot / 3);
                var labelType3 = bookSlot % 3;         // 0/1 = Tip 2, 2 = Tip 3

                var col = i % cfg.cols;
                var row = Math.floor(i / cfg.cols);
                var lx  = cfg.marginLeft + col * (cfg.labelW + cfg.gapX);
                var ly  = cfg.marginTop  + row * (cfg.labelH + cfg.gapY);

                var book = books[bookIdx];
                if (labelType3 === 2) {
                    drawLabel3(doc, book, lx, ly, cfg.labelW, cfg.labelH);
                } else {
                    drawLabel2(doc, book, lx, ly, cfg.labelW, cfg.labelH);
                }
            }
        }

        return doc;
    }

    // ─────────────────────────────────────────────────────────────────────────────
    /*
     * Tip 5 — Ribonlu / termal etiket yazıcısı · 60×40mm · Yatay (Landscape)
     *
     * PDF boyutu: 60mm × 40mm, her sayfa = 1 etiket.
     * İçerik: Tip 2 ile aynı düzen, 60×40mm'e orantılı ölçeklenmiş.
     *   - Kütüphane adı    (Tahoma, üstte)
     *   - Code 39 barkod   (demirbasKN)
     *   - Demirbaş no text (barkod altı)
     *   - Kitap adı        (altta)
     *
     * skip: baştaki boş sayfaları atlar.
     */
    var TIP5 = {
        labelW:     50,          // mm — etiket genişliği
        labelH:     30,          // mm — etiket yüksekliği
        FONT_SIZE:  9,           // pt  (~7pt × 60/45 ≈ 9.3pt → 9pt)
        LINE_H_MM:  9 * 0.3528 * 1.2,   // ≈ 3.81 mm
        PADDING_X:  2,           // mm — yatay iç boşluk (her kenar)
        PAD_TOP:    2.5,         // mm — üst iç boşluk
        GAP:        0.7,         // mm — bloklar arası boşluk
        BAR_H:      12           // mm — barkod yüksekliği
    };

    function buildTip5PDF(books, skip) {
        skip = skip || 0;
        var cfg   = TIP5;
        var jsPDF = window.jspdf.jsPDF;

        // İlk sayfa: 60×40mm yatay
        var doc = new jsPDF({
            orientation: 'landscape',
            unit:        'mm',
            format:      [cfg.labelH, cfg.labelW]   // jsPDF: portrait=[kısa,uzun] → landscape=[kısa,uzun] aynı sıra
        });

        registerTahomaFonts(doc);

        var total = skip + books.length;

        for (var idx = 0; idx < total; idx++) {
            if (idx > 0) {
                doc.addPage([cfg.labelH, cfg.labelW], 'landscape');
            }
            if (idx < skip) continue;   // boş sayfa — atla
            drawLabel5(doc, books[idx - skip], 0, 0, cfg.labelW, cfg.labelH);
        }

        return doc;
    }

    /**
     * Tip 5 etiketini çizer — Tahoma (normal), 60×40mm.
     * Tip 2 ile aynı içerik ve düzen, orantılı ölçeklenmiş.
     */
    function drawLabel5(doc, book, x, y, w, h) {
        var cfg    = TIP5;
        var FS     = cfg.FONT_SIZE;
        var LH     = cfg.LINE_H_MM;
        var CHAR_H = FS * 0.3528;
        var availW = w - 2 * cfg.PADDING_X;
        var MAX_WRAP = 2;
        var cx     = x + w / 2;

        doc.setFont('tahoma', 'normal');
        doc.setFontSize(FS);
        doc.setTextColor(0, 0, 0);

        // ── Kütüphane adı — üstte ────────────────────────────────────────────
        var kutAll   = doc.splitTextToSize(String(book.kutuphaneAdi || ''), availW);
        var kutLines = kutAll.slice(0, MAX_WRAP);
        if (kutAll.length > MAX_WRAP) {
            kutLines[MAX_WRAP - 1] = truncateText(doc, kutLines[MAX_WRAP - 1] + ' \u2026', availW, FS);
        }

        var curY = y + cfg.PAD_TOP + CHAR_H / 2;
        for (var i = 0; i < kutLines.length; i++) {
            doc.text(kutLines[i], cx, curY + i * LH, { align: 'center', baseline: 'middle' });
        }
        curY += (kutLines.length - 1) * LH + CHAR_H / 2;

        // ── Barkod (Code 39) — demirbasKN ────────────────────────────────────
        curY += cfg.GAP;
        var rawDem     = String(book.demirbasKN || '');
        var barcodeVal = rawDem.toUpperCase().replace(/[^0-9A-Z\-\.\s\$\/\+\%]/g, '');
        if (barcodeVal) {
            drawBarcode39(doc, barcodeVal, x + cfg.PADDING_X, curY, availW, cfg.BAR_H);
        }
        curY += cfg.BAR_H;

        // ── Demirbaş no — barkodun altında metin ─────────────────────────────
        curY += cfg.GAP;
        var demText = truncateText(doc, rawDem, availW, FS);
        curY += CHAR_H / 2;
        doc.text(demText, cx, curY, { align: 'center', baseline: 'middle' });
        curY += CHAR_H / 2;

        // ── Kitap adı ─────────────────────────────────────────────────────────
        curY += cfg.GAP;
        var eserAll   = doc.splitTextToSize(String(book.title || ''), availW);
        var eserLines = eserAll.slice(0, MAX_WRAP);
        if (eserAll.length > MAX_WRAP) {
            eserLines[MAX_WRAP - 1] = truncateText(doc, eserLines[MAX_WRAP - 1] + ' \u2026', availW, FS);
        }
        curY += CHAR_H / 2;
        for (var j = 0; j < eserLines.length; j++) {
            doc.text(eserLines[j], cx, curY + j * LH, { align: 'center', baseline: 'middle' });
        }
    }

    // ─────────────────────────────────────────────────────────────────────────────
    /*
     * Tip 6 — Ribonlu yazıcı · 60×40mm yatay · Kitap başına Tip 2 + Tip 3
     *
     * Her kitap için arka arkaya iki sayfa üretilir:
     *   Sayfa 1 (çift index) → Tip 2 içeriği  — drawLabel5 ile çizilir
     *   Sayfa 2 (tek  index) → Tip 3 içeriği  — drawLabel6_3 ile çizilir
     *
     * TIP3'ün 45×30mm ölçüleri 60×40mm'e oransal olarak ölçeklenmiştir (×4/3).
     */
    var TIP6_3 = {
        labelW:     50,
        labelH:     30,
        // ── Sol dikey demirbaş sütunu (TIP3 × 4/3) ────────────────────────────
        DEM_W:      16,         // mm  (12 × 4/3)
        DEM_PAD:    1.0,        // mm  (0.8 × 4/3 ≈ 1.07 → 1.0)
        BAR_W:      9,          // mm  (7 × 4/3 ≈ 9.3 → 9)
        BAR_GAP:    0.7,        // mm  (0.5 × 4/3 ≈ 0.67 → 0.7)
        DEM_FS:     8,          // pt  (6 × 4/3 = 8)
        // ── Sağ veri sütunu ───────────────────────────────────────────────────
        FONT_SIZE:  14,         // pt  (12 × 4/3 = 16 → 14 okunabilirlik için)
        COL_GAP:    5,          // mm  (4 × 4/3 ≈ 5.3 → 5)
        LINE_H_MM:  14 * 0.3528 * 1.08,   // ≈ 5.33 mm
        PAD_RIGHT:  1.5         // mm  (1 × 4/3 ≈ 1.3 → 1.5)
    };

    function buildTip6PDF(books, skip) {
        skip = skip || 0;
        var jsPDF      = window.jspdf.jsPDF;
        var labelW     = TIP5.labelW;     // 60mm
        var labelH     = TIP5.labelH;     // 40mm

        // İlk sayfa
        var doc = new jsPDF({
            orientation: 'landscape',
            unit:        'mm',
            format:      [labelH, labelW]
        });

        registerTahomaFonts(doc);

        // Her kitap için 2 sayfa: [Tip2, Tip3]
        var totalPages = skip + books.length * 2;

        for (var idx = 0; idx < totalPages; idx++) {
            if (idx > 0) {
                doc.addPage([labelH, labelW], 'landscape');
            }
            if (idx < skip) continue;   // boş sayfa — atla

            var bookSlot  = idx - skip;
            var bookIdx   = Math.floor(bookSlot / 2);
            var labelType = bookSlot % 2;   // 0 = Tip 2, 1 = Tip 3

            var book = books[bookIdx];
            if (labelType === 0) {
                drawLabel5(doc, book, 0, 0, labelW, labelH);
            } else {
                drawLabel6_3(doc, book, 0, 0, labelW, labelH);
            }
        }

        return doc;
    }

    /**
     * Tip 6 — Tip 3 içeriğini 60×40mm'e ölçeklenmiş olarak çizer.
     * TIP3 → TIP6_3 config ile drawLabel3 mantığının ölçeklenmiş hâli.
     *
     * Sol: demirbasKN dikey barkod + metin
     * Sağ: siniflamaYer / yayinTarihi / k.X satırları, sola hizalı, dikey ortalı
     */
    function drawLabel6_3(doc, book, x, y, w, h) {
        var cfg    = TIP6_3;
        var FS     = cfg.FONT_SIZE;
        var LH     = cfg.LINE_H_MM;
        var CHAR_H = FS * 0.3528;

        // ── Sol sütun — dikey barkod + demirbaş no ───────────────────────────
        var rawDem3     = String(book.demirbasKN || '');
        var barcodeVal3 = rawDem3.toUpperCase().replace(/[^0-9A-Z\-\.\s\$\/\+\%]/g, '');

        var barX = x + cfg.DEM_PAD;
        var barY = y + cfg.DEM_PAD - 2;
        var barW = cfg.BAR_W;
        var barH = h - 2 * cfg.DEM_PAD + 1;
        if (barcodeVal3) {
            drawBarcode39Vertical(doc, barcodeVal3, barX, barY, barW, barH);
        }

        var demText  = rawDem3;
        var textColX = barX + barW + cfg.BAR_GAP;
        if (demText) {
            doc.setFont('tahoma', 'bold');
            doc.setFontSize(cfg.DEM_FS);
            doc.setTextColor(0, 0, 0);
            var textCX      = textColX + (cfg.DEM_W - cfg.DEM_PAD - barW - cfg.BAR_GAP) / 2;
            var demTextW    = doc.getTextWidth(demText);
            var textAnchorY = y + h / 2 + demTextW / 2;
            doc.text(demText, textCX, textAnchorY, {
                angle:    90,
                align:    'left',
                baseline: 'middle'
            });
        }

        // ── Sağ veri sütunu ──────────────────────────────────────────────────
        doc.setFont('tahoma', 'bold');
        doc.setFontSize(FS);
        doc.setTextColor(0, 0, 0);

        var divX   = x + cfg.DEM_W;
        var rightX = divX + cfg.COL_GAP;
        var rightW = w - cfg.DEM_W - cfg.COL_GAP - cfg.PAD_RIGHT;

        var lines  = buildLines3(book);
        var n      = lines.length;
        var blockH = (n - 1) * LH + CHAR_H;
        var firstY = y + (h - blockH) / 2 + CHAR_H / 2;

        for (var i = 0; i < n; i++) {
            var txt = truncateText(doc, lines[i], rightW, FS);
            doc.text(txt, rightX, firstY + i * LH, {
                align:    'left',
                baseline: 'middle'
            });
        }
    }

    // ─────────────────────────────────────────────────────────────────────────────
    /*
     * Tip 7 — Ribonlu yazıcı · 60×40mm yatay · Sayfa başına 1 etiket · Tip 3 içerik
     *
     * Tip 5 ile aynı sayfa yapısı (her kitap = 1 sayfa, 60×40mm landscape).
     * İçerik olarak drawLabel6_3 kullanılır (Tip 3'ün 60×40mm'e ölçeklenmiş hâli).
     */
    function buildTip7PDF(books, skip) {
        skip = skip || 0;
        var jsPDF  = window.jspdf.jsPDF;
        var labelW = TIP5.labelW;   // 60mm
        var labelH = TIP5.labelH;   // 40mm

        var doc = new jsPDF({
            orientation: 'landscape',
            unit:        'mm',
            format:      [labelH, labelW]
        });

        registerTahomaFonts(doc);

        var total = skip + books.length;

        for (var idx = 0; idx < total; idx++) {
            if (idx > 0) {
                doc.addPage([labelH, labelW], 'landscape');
            }
            if (idx < skip) continue;   // boş sayfa — atla
            drawLabel6_3(doc, books[idx - skip], 0, 0, labelW, labelH);
        }

        return doc;
    }

    // ─────────────────────────────────────────────────────────────────────────────
    /*
     * Tip 3 — A4, 4 sütun × 9 satır, 45mm × 30mm etiket
     *
     * Sayfa düzeni Tip 1 ile aynı (margin/gap).
     *
     * Her etiket:
     *   Sol sütun (~8 mm) : kunyeDemirbasKN — dikey, ortalı, soldan hizalı
     *   İnce ayırıcı çizgi
     *   Sağ sütun         : 4 satır, sola hizalı, bold, Helvetica
     *     1. kunyeSiniflamaYer "/" öncesi (newline varsa her satır ayrı)
     *     2. kunyeSiniflamaYer "/" sonrası
     *     3. kunyeYayinTarihi
     *     4. k.X veya k.X/c.X
     *
     * Metin bloğu sağ sütunda dikey ortalanır. Çerçeve yoktur.
     */
    var TIP3 = {
        cols:       4,
        rows:       9,
        labelW:     45,
        labelH:     30,
        marginLeft: 4.5,    // mm  — sol kenar boşluğu
        gapX:       7,      // mm  — etiketler arası yatay boşluk
        marginTop:  14,     // mm  — üst kenar boşluğu
        gapY:       0,      // mm  — satırlar arası boşluk (yok)
        // ── Sol dikey demirbaş sütunu ──────────────────────────
        DEM_W:      12,      // mm  — toplam sol sütun genişliği
        DEM_PAD:    0.8,    // mm  — sol/üst/alt kenar iç boşluk
        BAR_W:      7,    // mm  — barkodun genişliği (kağıttaki "kalınlık")
        BAR_GAP:    0.5,    // mm  — barkod ile metin arasındaki boşluk
        DEM_FS:     6,      // pt  — demirbaş font boyutu
        // ── Sağ veri sütunu ────────────────────────────────────
        FONT_SIZE:  12,      // pt
        COL_GAP: 4,
        LINE_H_MM:  12 * 0.3528 * 1.08,   // ≈ 3.05 mm
        PAD_RIGHT:  1       // mm  — sağ kenar iç boşluk
    };

    function buildTip3PDF(books, skip) {
        skip = skip || 0;
        var cfg      = TIP3;
        var jsPDF    = window.jspdf.jsPDF;
        var doc      = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });

        registerTahomaFonts(doc);

        var perPage  = cfg.cols * cfg.rows;
        var total    = skip + books.length;
        var pageCount = Math.ceil(total / perPage);

        for (var page = 0; page < pageCount; page++) {
            if (page > 0) doc.addPage();
            for (var i = 0; i < perPage; i++) {
                var slot = page * perPage + i;
                if (slot >= total) break;
                var col  = i % cfg.cols;
                var row  = Math.floor(i / cfg.cols);
                var lx   = cfg.marginLeft + col * (cfg.labelW + cfg.gapX);
                var ly   = cfg.marginTop  + row * (cfg.labelH + cfg.gapY);
                // TEST: tüm hücreler için gri arka plan (boş slotlar dahil)
                //doc.setFillColor(220, 220, 220);
                //doc.rect(lx, ly, cfg.labelW, cfg.labelH, 'F');
                if (slot < skip) continue;
                drawLabel3(
                    doc,
                    books[slot - skip],
                    lx,
                    ly,
                    cfg.labelW,
                    cfg.labelH
                );
            }
        }

        return doc;
    }

    /**
     * Tip 3 etiketini çizer.
     *
     * Sol: kunyeDemirbasKN dikey (90° CCW = baş sağa eğilerek okunur).
     * İnce dikey çizgi ayırıcı.
     * Sağ: Tip 1'deki buildLines çıktısı, sola hizalı, dikey ortalı.
     */
    function drawLabel3(doc, book, x, y, w, h) {
        var cfg    = TIP3;
        var FS     = cfg.FONT_SIZE;
        var LH     = cfg.LINE_H_MM;
        var CHAR_H = FS * 0.3528;

        // ── TEST: Gri arka plan (hizalama kontrolü için) ─────────────────────
        //doc.setFillColor(220, 220, 220);
        //doc.rect(x, y, w, h, 'F');

        // ── Sol sütun düzeni ─────────────────────────────────────────────────
        // Sütun içi: [DEM_PAD] [barkod: BAR_W] [BAR_GAP] [metin] [DEM_PAD]
        var rawDem3     = String(book.demirbasKN || '');
        var barcodeVal3 = rawDem3.toUpperCase().replace(/[^0-9A-Z\-\.\s\$\/\+\%]/g, '');

        // Barkod: sol ve üst/alt iç boşlukla
        var barX  = x + cfg.DEM_PAD;
        var barY  = y + cfg.DEM_PAD - 2;
        var barW  = cfg.BAR_W;
        var barH  = h - 2 * cfg.DEM_PAD + 1;
        if (barcodeVal3) {
            drawBarcode39Vertical(doc, barcodeVal3, barX, barY, barW, barH);
        }

        // Demirbaş no metni: barkodun sağında, dikey (90° CCW)
        var demText  = rawDem3;
        var textColX = barX + barW + cfg.BAR_GAP;   // metin sütununun sol kenarı
        if (demText) {
            doc.setFont('tahoma', 'bold');
            doc.setFontSize(cfg.DEM_FS);
            doc.setTextColor(0, 0, 0);
            // Metnin orta noktası: sütunun yatay ortası
            var textCX = textColX + (cfg.DEM_W - cfg.DEM_PAD - barW - cfg.BAR_GAP) / 2;
            var demTextW = doc.getTextWidth(demText);
            // Dikey ortalama: anchor noktası metnin yarı uzunluğu kadar aşağıda
            var textAnchorY = y + h / 2 + demTextW / 2;
            doc.text(demText, textCX, textAnchorY, {
                angle:    90,
                align:    'left',
                baseline: 'middle'
            });
        }

        // ── Sağ veri sütunu ──────────────────────────────────────────────────
        doc.setFont('tahoma', 'bold');
        doc.setFontSize(FS);
        doc.setTextColor(0, 0, 0);

        var divX    = x + cfg.DEM_W;
        var rightX = divX + cfg.COL_GAP;
        var rightW = w - cfg.DEM_W - cfg.COL_GAP - cfg.PAD_RIGHT;

        // buildLines ile aynı mantık
        var lines   = buildLines3(book);

        var n      = lines.length;
        var blockH = (n - 1) * LH + CHAR_H;
        var firstY = y + (h - blockH) / 2 + CHAR_H / 2;

        for (var i = 0; i < n; i++) {
            var txt = truncateText(doc, lines[i], rightW, FS);
            doc.text(txt, rightX, firstY + i * LH, {
                align:    'left',
                baseline: 'middle'
            });
        }
    }

    /**
     * kunyeSiniflamaYer parçasını etiket satırlarına ekler.
     * Boşluk karakterlerinde de satır kırımı yapılır.
     */
    function pushSiniflamaLines(lines, part) {
        String(part || '')
            .trim()
            .split(/\s+/)
            .forEach(function(token) {
                if (token) lines.push(token);
            });
    }

    /**
     * Tip 3 için veri satırlarını üretir.
     *
     * kunyeSiniflamaYer "Ç\nT813.42/DÖLy" gibi gelebilir:
     *   "/" öncesi → her \n satırı ayrı çizgi, boşluklarda alt satıra kırılır
     *   "/" sonrası → boşluklarda alt satıra kırılır
     *   yayinTarihi → bir çizgi
     *   k.X veya k.X/c.X → bir çizgi
     */
    function buildLines3(book) {
        var raw      = String(book.siniflamaYer || '').trim();
        var lines    = [];

        var slashIdx = raw.indexOf('/');
        var before   = slashIdx !== -1 ? raw.substring(0, slashIdx).trim() : raw;
        var after    = slashIdx !== -1 ? raw.substring(slashIdx + 1).trim() : '';

        before
            .replace(/\r\n/g, '\n').replace(/\r/g, '\n')
            .split('\n')
            .forEach(function(s) {
                pushSiniflamaLines(lines, s);
            });

        pushSiniflamaLines(lines, after);

        var yt = String(book.yayinTarihi || '').trim();
        if (yt) lines.push(yt);

        var kc = buildKCLine(
            String(book.kopya || '').trim(),
            String(book.cilt  || '').trim()
        );
        if (kc) lines.push(kc);

        return lines;
    }

    /*
     * Tip 2 — A4, 4 sütun × 9 satır, 45mm × 30mm etiket
     *
     * Sayfa düzeni Tip 1 ile aynı (margin/gap değerleri).
     *
     * Her etiket (tümü ortalı, Tahoma, çerçevesiz):
     *   1. satır(lar) : kütüphane adı   — üstte, çok az boşlukla (max 2 satır)
     *   barkod        : demirbasKN — Code 39 barkod
     *   2. satır      : demirbasKN — barkodun altında metin
     *   3. satır(lar) : kitap adı       — (max 2 satır)
     */
    var TIP2 = {
        cols:       4,
        rows:       9,
        labelW:     45,
        labelH:     30,
        marginLeft: 4.5,    // mm  — sol kenar boşluğu
        gapX:       7,      // mm  — etiketler arası yatay boşluk
        marginTop:  14,     // mm  — üst kenar boşluğu
        gapY:       0,      // mm  — satırlar arası boşluk (yok)
        FONT_SIZE:  7,           // pt
        LINE_H_MM:  7 * 0.3528 * 1.2,   // ≈ 2.96 mm
        PADDING_X:  1.5,         // mm — yatay iç boşluk (her kenar)
        PAD_TOP:    2,           // mm — üst iç boşluk (çok az)
        GAP:        0.5,         // mm — bloklar arası boşluk
        BAR_H:      9            // mm — barkod yüksekliği
    };

    function buildTip2PDF(books, skip) {
        skip = skip || 0;
        var cfg      = TIP2;
        var jsPDF    = window.jspdf.jsPDF;
        var doc      = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });

        registerTahomaFonts(doc);

        var perPage  = cfg.cols * cfg.rows;
        var total    = skip + books.length;
        var pageCount = Math.ceil(total / perPage);

        for (var page = 0; page < pageCount; page++) {
            if (page > 0) doc.addPage();
            for (var i = 0; i < perPage; i++) {
                var slot = page * perPage + i;
                if (slot >= total) break;
                var col  = i % cfg.cols;
                var row  = Math.floor(i / cfg.cols);
                var lx   = cfg.marginLeft + col * (cfg.labelW + cfg.gapX);
                var ly   = cfg.marginTop  + row * (cfg.labelH + cfg.gapY);
                // TEST: tüm hücreler için gri arka plan (boş slotlar dahil)
                //doc.setFillColor(220, 220, 220);
                //doc.rect(lx, ly, cfg.labelW, cfg.labelH, 'F');
                if (slot < skip) continue;
                drawLabel2(
                    doc,
                    books[slot - skip],
                    lx,
                    ly,
                    cfg.labelW,
                    cfg.labelH
                );
            }
        }

        return doc;
    }

    /**
     * Tip 2 etiketini çizer — Tahoma (normal), Türkçe karakterler doğal desteklenir.
     *
     * İçerik:
     *   - kutuphaneAdi  : word-wrap, max 2 satır
     *   - [boş satır]
     *   - demirbasKN    : tek satır (gerekirse kırp)
     *   - [boş satır]
     *   - kitap adı     : word-wrap, max 2 satır
     *
     * Normal ağırlık, yatay ortalı. Blok dikey ortalanır. Çerçeve yok.
     */
    function drawLabel2(doc, book, x, y, w, h) {
        var cfg      = TIP2;
        var FS       = cfg.FONT_SIZE;       // 7 pt
        var LH       = cfg.LINE_H_MM;       // ~2.96 mm
        var CHAR_H   = FS * 0.3528;         // ~2.47 mm
        var availW   = w - 2 * cfg.PADDING_X;
        var MAX_WRAP = 2;
        var cx       = x + w / 2;

        // ── TEST: Gri arka plan (hizalama kontrolü için) ─────────────────────
        //doc.setFillColor(220, 220, 220);
        //doc.rect(x, y, w, h, 'F');

        doc.setFont('tahoma', 'normal');
        doc.setFontSize(FS);
        doc.setTextColor(0, 0, 0);

        // ── Kütüphane adı — üstte, çok az boşlukla ──────────────────────────
        var kutAll   = doc.splitTextToSize(String(book.kutuphaneAdi || ''), availW);
        var kutLines = kutAll.slice(0, MAX_WRAP);
        if (kutAll.length > MAX_WRAP) {
            kutLines[MAX_WRAP - 1] = truncateText(doc, kutLines[MAX_WRAP - 1] + ' \u2026', availW, FS);
        }

        // İlk satır merkezini üst kenara dayandır (PAD_TOP + yarı karakter yüksekliği)
        var curY = y + cfg.PAD_TOP + CHAR_H / 2;
        for (var i = 0; i < kutLines.length; i++) {
            doc.text(kutLines[i], cx, curY + i * LH, { align: 'center', baseline: 'middle' });
        }
        // Son satırın altına geç
        curY += (kutLines.length - 1) * LH + CHAR_H / 2;

        // ── Barkod (Code 39) — demirbasKN ────────────────────────────────────
        curY += cfg.GAP;
        var rawDem = String(book.demirbasKN || '');
        // Code 39: yalnızca izin verilen karakterler (büyük harf + rakam + özel)
        var barcodeVal = rawDem.toUpperCase().replace(/[^0-9A-Z\-\.\s\$\/\+\%]/g, '');
        if (barcodeVal) {
            drawBarcode39(doc, barcodeVal, x + cfg.PADDING_X, curY, availW, cfg.BAR_H);
        }
        curY += cfg.BAR_H;

        // ── Demirbaş no — barkodun altında metin ─────────────────────────────
        curY += cfg.GAP;
        var demText = truncateText(doc, rawDem, availW, FS);
        curY += CHAR_H / 2;
        doc.text(demText, cx, curY, { align: 'center', baseline: 'middle' });
        curY += CHAR_H / 2;

        // ── Kitap adı ─────────────────────────────────────────────────────────
        curY += cfg.GAP;
        var eserAll   = doc.splitTextToSize(String(book.title || ''), availW);
        var eserLines = eserAll.slice(0, MAX_WRAP);
        if (eserAll.length > MAX_WRAP) {
            eserLines[MAX_WRAP - 1] = truncateText(doc, eserLines[MAX_WRAP - 1] + ' \u2026', availW, FS);
        }
        curY += CHAR_H / 2;
        for (var j = 0; j < eserLines.length; j++) {
            doc.text(eserLines[j], cx, curY + j * LH, { align: 'center', baseline: 'middle' });
        }
    }

    /**
     * Code 39 barkodunu jsPDF belgesine çizer.
     * JsBarcode kütüphanesi kullanılır; yüklü değilse sessizce atlanır.
     *
     * @param {object} doc       - jsPDF instance
     * @param {string} text      - Barkod değeri (Code 39 geçerli karakterler)
     * @param {number} x, y      - Sol üst köşe (mm)
     * @param {number} w, h      - Genişlik ve yükseklik (mm)
     */
    function drawBarcode39(doc, text, x, y, w, h) {
        if (typeof JsBarcode === 'undefined' || !text) return;

        // Yüksek çözünürlüklü canvas (mm → piksel: 1mm ≈ 3.78px × 3 = ~11.34px/mm)
        var scale  = 4;
        var canvas = document.createElement('canvas');
        canvas.width  = Math.round(w * 3.7795 * scale);
        canvas.height = Math.round(h * 3.7795 * scale);

        try {
            JsBarcode(canvas, text, {
                format:       'CODE39',
                displayValue: false,
                margin:       0,
                //background:   '#dcdcdc',  // test gri arka planı ile uyumlu
                lineColor:    '#000000',
                width:        scale,       // bar genişliği (piksel)
                height:       canvas.height
            });
        } catch (e) {
            // Geçersiz karakter vb. hata → barkod atlanır
            console.warn('Barkod oluşturulamadı:', text, e);
            return;
        }

        var imgData = canvas.toDataURL('image/png');
        doc.addImage(imgData, 'PNG', x, y, w, h);
    }

    /**
     * Code 39 barkodunu 90° döndürülmüş (dikey) olarak çizer.
     * Barkod soldan sağa yukarıdan aşağıya okunur (CCW döndürme).
     *
     * @param {object} doc    - jsPDF instance
     * @param {string} text   - Barkod değeri
     * @param {number} x, y   - Etiket sol üst köşe (mm) — barkod buraya yaslanır
     * @param {number} w      - Sütun genişliği (mm) — barkod yüksekliği olur
     * @param {number} h      - Etiket yüksekliği (mm) — barkod uzunluğu olur
     */
    function drawBarcode39Vertical(doc, text, x, y, w, h) {
        if (typeof JsBarcode === 'undefined' || !text) return;

        var PX_PER_MM = 3.7795;
        var RES       = 6;

        var narrowPx   = 2;
        var charsTotal = text.length + 2;
        var tmpW = Math.ceil(charsTotal * 16 * narrowPx + 40);
        var tmpH = Math.round(w * PX_PER_MM * RES);

        // 1) Kaynak canvas — yatay render
        var srcCanvas = document.createElement('canvas');
        srcCanvas.width  = tmpW;
        srcCanvas.height = tmpH;

        var srcCtx = srcCanvas.getContext('2d');
        srcCtx.fillStyle = '#ffffff';
        srcCtx.fillRect(0, 0, tmpW, tmpH);

        try {
            JsBarcode(srcCanvas, text, {
                format:       'CODE39',
                displayValue: false,
                margin:       0,
                background:   '#ffffff',
                lineColor:    '#000000',
                width:        narrowPx,
                height:       tmpH
            });
        } catch (e) {
            console.warn('Dikey barkod olusturulamadi:', text, e);
            return;
        }

        // 2) JsBarcode'un eklediği dahili beyaz boşluğu kırp (yatay yön = barkod uzunluğu)
        //    Pikselleri tarayarak siyah içerik olan ilk/son sütunu bul
        var imgPixels = srcCtx.getImageData(0, 0, tmpW, tmpH);
        var data      = imgPixels.data;
        var cropLeft  = 0;
        var cropRight = tmpW - 1;

        outer_left:
            for (var col = 0; col < tmpW; col++) {
                for (var row = 0; row < tmpH; row++) {
                    var idx = (row * tmpW + col) * 4;
                    if (data[idx] < 200) { cropLeft = col; break outer_left; }
                }
            }
        outer_right:
            for (var col2 = tmpW - 1; col2 >= 0; col2--) {
                for (var row2 = 0; row2 < tmpH; row2++) {
                    var idx2 = (row2 * tmpW + col2) * 4;
                    if (data[idx2] < 200) { cropRight = col2; break outer_right; }
                }
            }

        var cropW = cropRight - cropLeft + 1;

        // 3) Hedef canvas — döndürülmüş, PDF boyutuna göre
        var dstW = Math.round(w * PX_PER_MM * RES);
        var dstH = Math.round(h * PX_PER_MM * RES);

        var rotCanvas = document.createElement('canvas');
        rotCanvas.width  = dstW;
        rotCanvas.height = dstH;

        var ctx = rotCanvas.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, dstW, dstH);

        // 4) 90° CCW döndür — kırpılmış bölgeyi tam alana ölçekle
        ctx.translate(0, dstH);
        ctx.rotate(-Math.PI / 2);
        ctx.drawImage(srcCanvas, cropLeft, 0, cropW, tmpH, 0, 0, dstH, dstW);

        var imgData = rotCanvas.toDataURL('image/png');
        doc.addImage(imgData, 'PNG', x, y, w, h);
    }

    /**
     * kunyeSiniflamaYer örüntüleri:
     *   "813.42/MUHb"          → ["813.42", "MUHb"]
     *   "Ç\nT813.42/DÖLy"     → ["Ç", "T813.42", "DÖLy"]
     *   "Ç\n808.08681/TARh"   → ["Ç", "808.08681", "TARh"]
     *   "956.102092/KUTo"      → ["956.102092", "KUTo"]
     *
     * Ardından yayinTarihi ve k.X/c.X eklenir.
     * kunyeSiniflamaYer içindeki boşluklar satır kırımı olarak değerlendirilir.
     */
    function buildLines(book) {
        var raw      = String(book.siniflamaYer || '').trim();
        var lines    = [];

        // "/" bul — sadece ilkini böl (k.X/c.X ile karıştırmasın)
        var slashIdx = raw.indexOf('/');
        var before   = slashIdx !== -1 ? raw.substring(0, slashIdx).trim() : raw;
        var after    = slashIdx !== -1 ? raw.substring(slashIdx + 1).trim() : '';

        // "/" öncesindeki \n satırları da ayrıştır (ör: "Ç\nT813.42" → ["Ç","T813.42"])
        before
            .replace(/\r\n/g, '\n')
            .replace(/\r/g, '\n')
            .split('\n')
            .forEach(function(s) {
                pushSiniflamaLines(lines, s);
            });

        // "/" sonrası
        pushSiniflamaLines(lines, after);

        // Yayın tarihi
        var yt = String(book.yayinTarihi || '').trim();
        if (yt) lines.push(yt);

        // Kopya / Cilt
        var kc = buildKCLine(
            String(book.kopya || '').trim(),
            String(book.cilt  || '').trim()
        );
        if (kc) lines.push(kc);

        return lines;
    }

    /**
     * Etiketi çizer.
     *
     * Tüm satırlar 8pt bold, yatay ortalı.
     * Metin bloğu etiket içinde dikey ortalanır.
     * Satırlar arasında ek boşluk yoktur (doğal line-height).
     * Çerçeve yoktur.
     */
    function drawLabel(doc, book, x, y, w, h) {
        var lines = buildLines(book);
        var n     = lines.length;
        if (!n) return;

        // ── TEST: Gri arka plan (hizalama kontrolü için) ─────────────────────
        //doc.setFillColor(220, 220, 220);
        //doc.rect(x, y, w, h, 'F');

        var FS       = TIP1.FONT_SIZE;     // 12 pt
        var LINE_H   = TIP1.LINE_H_MM;
        var CHAR_H   = FS * 0.3528;        // tek karakter yüksekliği

        doc.setFont('tahoma', 'bold');
        doc.setFontSize(FS);
        doc.setTextColor(0, 0, 0);

        // Blok yüksekliği: (n-1) aralık + son satırın kendi yüksekliği
        var blockH = (n - 1) * LINE_H + CHAR_H;

        // İlk satırın orta noktası — blok dikey ortada
        var firstY = y + (h - blockH) / 2 + CHAR_H / 2;

        for (var i = 0; i < n; i++) {
            var lineText = truncateText(doc, lines[i], w - 2, FS);
            doc.text(lineText, x + w / 2, firstY + i * LINE_H, {
                align:    'center',
                baseline: 'middle'
            });
        }
    }

    /**
     * "k.X" veya "k.X/c.X" dizgesi üretir.
     */
    function buildKCLine(kopya, cilt) {
        var parts = [];
        if (kopya) parts.push('k.' + kopya);
        if (cilt)  parts.push('c.' + cilt);
        return parts.join('/');
    }

    /**
     * Metni verilen maksimum genişliğe göre kırpar, "…" ekler.
     */
    function truncateText(doc, text, maxWidth, fontSize) {
        doc.setFontSize(fontSize);
        if (!text) return '';
        if (doc.getTextWidth(text) <= maxWidth) return text;
        while (text.length > 1 && doc.getTextWidth(text + '…') > maxWidth) {
            text = text.slice(0, -1);
        }
        return text + '…';
    }

    // ── Download / Print ──────────────────────────────────────────────────────────
    function downloadPDF() {
        if (!lastPdfBlob) return;
        var a = document.createElement('a');
        a.href = lastPdfUrl;
        a.download = 'etiketler_' + new Date().toISOString().slice(0,10) + '.pdf';
        a.click();
    }

    function printPDF() {
        var frame = document.getElementById('pdfFrame');
        if (!frame || frame.style.display === 'none') return;
        frame.contentWindow.print();
    }

    function markAsLabeled() {
        if (!generatedBookIds.length) {
            showToast('info', 'Önce etiket oluşturun', 'İşaretleme için önce Etiket PDF Oluştur adımını tamamlayın.');
            return;
        }
        var btn = document.getElementById('btnMarkLabeled');
        var original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin .6s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Güncelleniyor…';

        postMarkLabeledRequest(true)
            .then(function(preview) {
                var updateCount = Number(preview.updateable || 0);
                if (updateCount <= 0) {
                    showToast('info', 'Güncellenecek kayıt yok', 'Seçili kayıtlar zaten etiketlendi olabilir.');
                    return null;
                }
                var ok = window.confirm(updateCount + ' katalog Etiketlendi olarak işaretlenecek. Devam edilsin mi?');
                return ok ? postMarkLabeledRequest(false) : null;
            })
            .then(function(data) {
                if (!data) return;
                var updated = Number(data.updated || 0);
                showToast('success', 'Güncelleme tamamlandı', updated + ' katalog etiketlendi olarak işaretlendi.');
            })
            .catch(function(e) {
                showToast('error', 'Güncelleme hatası', e.message || 'Bilinmeyen hata.');
            })
            .finally(function() {
                btn.disabled = false;
                btn.innerHTML = original;
            });
    }

    function postMarkLabeledRequest(dryRun) {
        return fetch('{{ route('etiket.isaretle') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: generatedBookIds, dry_run: !!dryRun })
        })
        .then(function(r) {
            return r.text().then(function(text) {
                var data = {};
                try { data = text ? JSON.parse(text) : {}; } catch (e) { data = {}; }
                if (!r.ok) {
                    var msg = (data && (data.message || data.error)) ? (data.message || data.error) : ('HTTP ' + r.status);
                    throw new Error(msg);
                }
                return data;
            });
        });
    }

    // ── Enter tuşu ile arama ─────────────────────────────────────────────────────
    (function() {
        syncOzelNotlarModeButton();
        var textInputIds = ['searchInput', 'filterDemirbas', 'filterOzelNotlar'];
        textInputIds.forEach(function(id) {
            var el = document.getElementById(id);
            if (el) {
                el.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') { e.preventDefault(); runSearch(); }
                });
            }
        });

        function resetComboboxFace(faceTextId, clearBtnId, placeholder) {
            var faceText = document.getElementById(faceTextId);
            var clearBtn = document.getElementById(clearBtnId);
            if (faceText) {
                faceText.textContent = placeholder;
                faceText.className = 'combobox-face-text';
            }
            if (clearBtn) clearBtn.style.display = 'none';
        }
        window.resetComboboxFace = resetComboboxFace;

        function initFilterCombobox(cfg) {
            var wrapper     = document.getElementById(cfg.wrapperId);
            var searchInput = document.getElementById(cfg.searchInputId);
            var hiddenId    = document.getElementById(cfg.hiddenId);
            var faceEl      = document.getElementById(cfg.faceId);
            var faceText    = document.getElementById(cfg.faceTextId);
            var clearBtn    = document.getElementById(cfg.clearBtnId);
            if (!wrapper || !searchInput || !hiddenId || !faceEl || !faceText || !clearBtn) return;

            var dropdown    = wrapper.querySelector('.combobox-dropdown');
            var toggle      = wrapper.querySelector('.combobox-toggle');
            var placeholder = cfg.placeholder || 'Seçin...';

            var rawData = [];
            try { rawData = JSON.parse(document.getElementById(cfg.dataScriptId).textContent || '[]'); } catch(e) {}

            var highlightedIndex = -1;
            var filtered = rawData.slice();

            function esc(s) { var d = document.createElement('div'); d.appendChild(document.createTextNode(s || '')); return d.innerHTML; }
            function lowerTr(value) { return String(value || '').toLocaleLowerCase('tr-TR'); }
            function displayText(item) { return String(item.ad || ''); }

            function highlight(text, term) {
                if (!term) return esc(text);
                var idx = lowerTr(text).indexOf(lowerTr(term));
                if (idx === -1) return esc(text);
                return esc(text.substring(0, idx)) +
                    '<strong style="color:var(--primary)">' + esc(text.substring(idx, idx + term.length)) + '</strong>' +
                    esc(text.substring(idx + term.length));
            }

            function updateFace() {
                if (hiddenId.value) {
                    var sel = null;
                    for (var i = 0; i < rawData.length; i++) {
                        if (String(rawData[i].id) === String(hiddenId.value)) { sel = rawData[i]; break; }
                    }
                    if (sel) {
                        faceText.textContent = displayText(sel);
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
                var term = lowerTr(filter);
                filtered = rawData.filter(function(r) {
                    return lowerTr(displayText(r)).indexOf(term) !== -1;
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
                        var label = displayText(r);
                        var sel  = (hiddenId.value !== '' && String(hiddenId.value) === String(r.id));
                        var high = (i === highlightedIndex);
                        html += '<div class="combobox-option' + (sel ? ' selected' : '') + (high ? ' highlighted' : '') + '" data-id="' + r.id + '" data-ad="' + esc(label) + '">' +
                            '<svg class="check-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>' +
                            '<span>' + highlight(label, filter) + '</span></div>';
                    });
                }

                dropdown.innerHTML = html;
                dropdown.querySelectorAll('.combobox-option').forEach(function(el) {
                    el.addEventListener('mousedown', function(e) {
                        e.preventDefault();
                        selectOption(this.getAttribute('data-id'));
                    });
                });
            }

            function selectOption(id) {
                hiddenId.value = id;
                updateFace();
                close();
            }

            function open() {
                if (isOpen()) return;
                highlightedIndex = -1;
                faceEl.style.display = 'none';
                searchInput.style.display = '';
                searchInput.value = '';
                render('');
                dropdown.classList.add('visible');
                toggle.classList.add('open');
                searchInput.focus();
            }

            function close() {
                dropdown.classList.remove('visible');
                toggle.classList.remove('open');
                highlightedIndex = -1;
                searchInput.style.display = 'none';
                faceEl.style.display = '';
            }

            function isOpen() { return dropdown.classList.contains('visible'); }

            clearBtn.addEventListener('mousedown', function(e) {
                e.preventDefault();
                hiddenId.value = '';
                updateFace();
            });

            toggle.addEventListener('mousedown', function(e) {
                e.preventDefault();
                isOpen() ? close() : open();
            });

            faceEl.addEventListener('mousedown', function(e) {
                if (e.target === clearBtn || clearBtn.contains(e.target)) return;
                e.preventDefault();
                open();
            });

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
                    var h = dropdown.querySelector('.highlighted'); if (h) h.scrollIntoView({block: 'nearest'});
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    highlightedIndex = Math.max(highlightedIndex - 1, -1);
                    render(searchInput.value);
                    var h2 = dropdown.querySelector('.highlighted'); if (h2) h2.scrollIntoView({block: 'nearest'});
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (highlightedIndex >= 0 && filtered[highlightedIndex]) {
                        selectOption(filtered[highlightedIndex].id);
                    } else if (highlightedIndex === -1) {
                        selectOption('');
                    }
                } else if (e.key === 'Escape') {
                    close();
                }
            });

            document.addEventListener('click', function(e) {
                if (!wrapper.contains(e.target)) close();
            });

            updateFace();
            searchInput.style.display = 'none';
        }

        initFilterCombobox({
            wrapperId:    'filterKutuphaneCombobox',
            searchInputId:'filterKutuphaneSearch',
            hiddenId:     'filterKutuphaneId',
            faceId:       'filterKutuphaneFace',
            faceTextId:   'filterKutuphaneFaceText',
            clearBtnId:   'filterKutuphaneClear',
            dataScriptId: 'filterKutuphaneData',
            placeholder:  'Tüm Yetkili Kütüphaneler'
        });

        initFilterCombobox({
            wrapperId:    'filterCreatedUserCombobox',
            searchInputId:'filterCreatedUserSearch',
            hiddenId:     'filterCreatedUser',
            faceId:       'filterCreatedUserFace',
            faceTextId:   'filterCreatedUserFaceText',
            clearBtnId:   'filterCreatedUserClear',
            dataScriptId: 'filterCreatedUserData',
            placeholder:  'Kullanıcı seçin...'
        });
    })();

    // ── Helpers ───────────────────────────────────────────────────────────────────
    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
</script>
@endsection