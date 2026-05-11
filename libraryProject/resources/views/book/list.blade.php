@extends('layouts.base')

@section('title', 'Kütüphane Bilgi Sistemi')

@section('styles')
    @php($bookListCssPath = public_path('css/book-list.css'))
    <link rel="stylesheet" href="{{ asset('css/book-list.css') }}?v={{ @filemtime($bookListCssPath) ?: time() }}" />
    @php($bookListCss = @file_get_contents($bookListCssPath))
    @if($bookListCss)
        <style>{!! $bookListCss !!}</style>
    @endif
    <style>
        th.sortable-th{cursor:pointer;user-select:none}
        th.sortable-th:hover{color:var(--foreground)}
        th.sortable-th .sort-label{display:inline-flex;align-items:center;gap:6px}
        th.sortable-th .sort-caret{opacity:.35;font-size:10px;line-height:1}
        th.sortable-th.sort-active .sort-caret{opacity:1}
    </style>
@endsection

@section('breadcrumb')
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="#" class="breadcrumb-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
            Katalog
        </a>
        <span class="breadcrumb-sep">/</span>
        <span class="breadcrumb-current">Kitap Kayıt</span>
    </nav>
@endsection

@section('content')
<div class="toast-container" id="toastContainer"></div>
        
            <div class="form-card filters-card">
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
                            <script id="filterYazarData" type="application/json">@json($yazarlar->map(fn($y) => ['id' => $y->id, 'ad' => $y->tam_ad]))</script>
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
                                @foreach(($dilSecenekleri ?? []) as $dil)
                                    <option value="{{ $dil }}">{{ $dil }}</option>
                                @endforeach
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

                    {{-- Satır 4: Kaydeden + Kayıt Tarihi --}}
                    <div class="form-grid cols-3 filter-row-extra" style="margin-bottom:14px;grid-template-columns:repeat(4,1fr);">
                        <div class="form-field">
                            <label class="form-label">Kaydeden</label>
                            <div class="combobox-wrapper" id="filterCreatedUserCombobox">
                                <div class="combobox-input-wrap">
                                    <div class="combobox-face" id="filterCreatedUserFace">
                                        <span class="combobox-face-text" id="filterCreatedUserFaceText">Kullanıcı seçin...</span>
                                        <button type="button" class="combobox-clear-btn" id="filterCreatedUserClear" title="Seçimi kaldır" style="display:none;"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                                    </div>
                                    <input type="text" class="form-input" id="filterCreatedUserSearch" placeholder="Kullanıcı ara..." autocomplete="off" style="display:none;" />
                                    <button type="button" class="combobox-toggle" tabindex="-1"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg></button>
                                </div>
                                <div class="combobox-dropdown"></div>
                            </div>
                            <input type="hidden" id="filterCreatedUser" value="" />
                            <script id="filterCreatedUserData" type="application/json">@json($kaydedenler ?? [])</script>
                        </div>
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
                        <table id="kitaplarTable" style="width: 100%; border-collapse: collapse; font-size: 14px; text-align: left;">
                            <thead>
                            <tr style="background: var(--muted); border-bottom: 1px solid var(--border);">
                                <th style="padding: 12px 24px; font-weight: 600;">Görsel</th>
                                <th class="sortable-th" data-sort="kunyeEserAdi" style="padding: 12px; font-weight: 600;" title="Sıralamak için tıklayın">
                                    <span class="sort-label">Kitap adı</span><span class="sort-caret" aria-hidden="true">◇</span>
                                </th>
                                <th class="sortable-th" data-sort="kunyeDemirbasKN" style="padding: 12px; font-weight: 600;" title="Sıralamak için tıklayın">
                                    <span class="sort-label">Demirbaş</span><span class="sort-caret" aria-hidden="true">◇</span>
                                </th>
                                <th style="padding: 12px; font-weight: 600;">ISBN</th>
                                <th style="padding: 12px; font-weight: 600;">Kategori</th>
                                <th class="sortable-th" data-sort="kutuphane" style="padding: 12px; font-weight: 600;" title="Sıralamak için tıklayın">
                                    <span class="sort-label">Kütüphane</span><span class="sort-caret" aria-hidden="true">◇</span>
                                </th>
                                <th style="padding: 12px; font-weight: 600;">Durum</th>
                                <th style="padding: 12px; font-weight: 600;">İşlem</th>
                            </tr>
                            </thead>
                            <tbody id="tableBody">
                            <tr><td colspan="8" style="padding:40px;text-align:center;color:var(--muted-foreground);font-size:13px;">Yükleniyor…</td></tr>
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
            var sortBy      = '';
            var sortDir     = 'asc';

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
                    createdUserId:     document.getElementById('filterCreatedUser').value,
                };
            }

            function getFilterQueryForView() {
                var f = getFilters();
                var params = new URLSearchParams();
                Object.keys(f).forEach(function (k) {
                    var v = f[k];
                    if (v === null || typeof v === 'undefined') return;
                    var s = String(v).trim();
                    if (s === '') return;
                    params.set(k, s);
                });
                if (sortBy) {
                    params.set('sort_by', sortBy);
                    params.set('sort_dir', sortDir || 'asc');
                }
                return params.toString();
            }

            function updateSortHeaderDisplay() {
                document.querySelectorAll('#kitaplarTable th.sortable-th').forEach(function (th) {
                    th.classList.remove('sort-active');
                    var col = th.getAttribute('data-sort');
                    var caret = th.querySelector('.sort-caret');
                    if (!caret) return;
                    if (sortBy && col === sortBy) {
                        th.classList.add('sort-active');
                        caret.textContent = sortDir === 'desc' ? '▼' : '▲';
                    } else {
                        caret.textContent = '◇';
                    }
                });
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
                    return '<tr><td colspan="8" style="padding:48px;text-align:center;color:var(--muted-foreground);">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 12px;display:block;opacity:0.4;"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>Kayıtlı kitap bulunamadı.</td></tr>';
                }
                return rows.map(function(k) {
                    var coverSrc = k.kunyeKapakResmi
                        ? k.kunyeKapakResmi
                        : ('https://ui-avatars.com/api/?name=' + encodeURIComponent(k.kunyeEserAdi || '') + '&background=7a5c3c&color=fff');
                    var viewUrl = '/katalog/' + k.id + '/view';
                    var fq = getFilterQueryForView();
                    if (fq) viewUrl += '?' + fq;
                    return '<tr style="border-bottom:1px solid var(--border);transition:background 0.2s;" onmouseover="this.style.background=\'var(--card)\'" onmouseout="this.style.background=\'transparent\'">' +
                        '<td style="padding:12px 24px;"><a href="' + viewUrl + '" style="display:inline-block;width:45px;height:65px;background:#ddd;border-radius:4px;overflow:hidden;border:1px solid var(--border);">' +
                        '<img src="' + coverSrc + '" alt="Kapak" style="width:100%;height:100%;object-fit:cover;"></a></td>' +
                        '<td style="padding:12px;"><a href="' + viewUrl + '" style="font-weight:600;color:var(--foreground);text-decoration:none;">' + (k.kunyeEserAdi || '') + '</a>' +
                        '<div style="font-size:12px;color:var(--muted-foreground);">' + (k.kunyeYazar || '') + (k.kunyeYayinlayan ? ' &middot; ' + k.kunyeYayinlayan : '') + '</div></td>' +
                        '<td style="padding:12px;color:var(--muted-foreground);">' + (k.kunyeDemirbasKN || '') + '</td>' +
                        '<td style="padding:12px;color:var(--muted-foreground);">' + (k.kunyeISBNISSN || '') + '</td>' +
                        '<td style="padding:12px;"><span style="padding:4px 8px;background:rgba(122,92,60,0.1);color:var(--primary);border-radius:4px;font-size:12px;">' + (k.kunyeSiniflamaYer || 'Genel') + '</span></td>' +
                        '<td style="padding:12px;color:var(--muted-foreground);">' + (k.kutuphane_title || '—') + '</td>' +
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
                if (sortBy) {
                    params.set('sort_by', sortBy);
                    params.set('sort_dir', sortDir || 'asc');
                }

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
                        if (data.sort_by) {
                            sortBy = data.sort_by;
                            sortDir = data.sort_dir || 'asc';
                        } else {
                            sortBy = '';
                            sortDir = 'asc';
                        }
                        updateSortHeaderDisplay();
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
                if (f.createdUserId)     params.set('createdUserId',     f.createdUserId);
                if (sortBy) {
                    params.set('sort_by', sortBy);
                    params.set('sort_dir', sortDir || 'asc');
                }
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
                document.getElementById('filterCreatedUser').value = '';
                resetComboboxFace('filterCreatedUserFace', 'filterCreatedUserFaceText', 'filterCreatedUserClear', 'Kullanıcı seçin...');
                // per-page sıfırla
                document.getElementById('perPageSelect').value = '20';
                sortBy = '';
                sortDir = 'asc';
                updateSortHeaderDisplay();
                fetchPage(1);
            });

            document.querySelectorAll('#kitaplarTable th.sortable-th').forEach(function (th) {
                th.addEventListener('click', function () {
                    var col = this.getAttribute('data-sort');
                    if (!col) return;
                    if (sortBy === col) {
                        sortDir = sortDir === 'asc' ? 'desc' : 'asc';
                    } else {
                        sortBy = col;
                        sortDir = 'asc';
                    }
                    updateSortHeaderDisplay();
                    fetchPage(1);
                });
            });
            updateSortHeaderDisplay();

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
                initFilterCombobox({
                    wrapperId:    'filterCreatedUserCombobox',
                    searchInputId:'filterCreatedUserSearch',
                    hiddenId:     'filterCreatedUser',
                    faceId:       'filterCreatedUserFace',
                    faceTextId:   'filterCreatedUserFaceText',
                    clearBtnId:   'filterCreatedUserClear',
                    dataScriptId: 'filterCreatedUserData',
                    placeholder:  'Kullanıcı seçin...',
                });
            })();

            // ============================
            // İlk yüklemede veri çek
            // ============================
            fetchPage(1);
        </script>

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

@endsection

@section('scripts')
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
    // Sidebar active item highlight
    // ============================
    var isMobile = window.innerWidth <= 768;
    var sidebar = document.getElementById('sidebar');
    var sidebarOverlay = document.getElementById('sidebarOverlay');
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
                var listCtxQuery = getFilterQueryForView();
                var editEl = document.getElementById('kfmDuzenle');
        if (editEl) {
            if (canEditBook) {
                editEl.style.display = '';
                        editEl.href = '/katalog/' + id + '/edit' + (listCtxQuery ? ('?' + listCtxQuery) : '');
            } else {
                editEl.style.display = 'none';
                editEl.href = '#';
            }
        }
                var viewEl = document.getElementById('kfmGoruntule');
        if (viewEl) {
            if (canViewBook) {
                viewEl.style.display = '';
                        viewEl.href = '/katalog/' + id + '/view' + (listCtxQuery ? ('?' + listCtxQuery) : '');
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
@endsection
