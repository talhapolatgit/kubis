@extends('layouts.base')

@section('title', 'Rapor Sihirbazi')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/book-etiket.css') }}?v={{ @filemtime(public_path('css/book-etiket.css')) ?: time() }}">
    @php($bookEtiketCss = @file_get_contents(public_path('css/book-etiket.css')))
    @if($bookEtiketCss)
        <style>{!! $bookEtiketCss !!}</style>
    @endif
    <style>
        .report-param-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .report-param-grid .span-2 {
            grid-column: span 2;
        }

        .filter-select {
            width: 100%;
            height: 36px;
            border: 1px solid var(--input);
            border-radius: 10px;
            background: #fff;
            color: var(--foreground);
            font-size: 13px;
            padding: 0 10px;
            outline: none;
        }

        .filter-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(122, 92, 60, .15);
        }

        .report-type-card {
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px;
            background: #fff;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .report-type-card input {
            margin-top: 3px;
        }

        .report-type-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--foreground);
            margin-bottom: 3px;
        }

        .report-type-desc {
            font-size: 12px;
            color: var(--muted-foreground);
        }

        .filter-meta {
            margin-top: 10px;
            font-size: 12px;
            color: var(--muted-foreground);
            display: flex;
            justify-content: space-between;
        }
    </style>
@endsection

@section('breadcrumb')
    <nav class="breadcrumb">
        <a href="{{ route('dashboard.index') }}" class="breadcrumb-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
            Dashboard
        </a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Rapor Sihirbazı</span>
    </nav>
@endsection

@section('content')
    <div class="etiket-page">
        <div class="page-header">
            <div class="page-title-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 13h6"/><path d="M9 17h6"/><path d="M9 9h1"/></svg>
            </div>
            <div>
                <div class="page-title">Rapor Sihirbazı</div>
                <div class="page-subtitle">Rapor tipini seçin, parametreleri belirleyin ve PDF önizleme alın.</div>
            </div>
        </div>

        <div class="etiket-layout">
            <div class="left-panel">
                <div class="panel-card">
                    <div class="panel-card-header">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m4 6 8-4 8 4"/><path d="m4 10 8 4 8-4"/><path d="m4 14 8 4 8-4"/></svg>
                        <span class="panel-card-header-title">Rapor Seçimi</span>
                    </div>
                    <div class="panel-card-body">
                        <label class="report-type-card">
                            <input type="radio" name="raporTipi" value="uye-listesi" checked>
                            <div>
                                <div class="report-type-name">Üye Listesi</div>
                                <div class="report-type-desc">Filtrelenmiş üye bilgilerini PDF tablo olarak üretir.</div>
                            </div>
                        </label>
                        <label class="report-type-card" style="margin-top:10px;">
                            <input type="radio" name="raporTipi" value="kullanici-katalog-kayit-sayilari">
                            <div>
                                <div class="report-type-name">Kullanıcı Katalog Kayıt Sayıları</div>
                                <div class="report-type-desc">Kullanıcı bazında toplam, ilk giriş ve kopya katalog kayıt adetlerini gösterir.</div>
                            </div>
                        </label>
                        <label class="report-type-card" style="margin-top:10px;">
                            <input type="radio" name="raporTipi" value="odunc-listesi">
                            <div>
                                <div class="report-type-name">Ödünç Listesi</div>
                                <div class="report-type-desc">Ödünç kayıtlarını filtreleyerek listeler, gecikme durumuna göre raporlar.</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="panel-card">
                    <div class="panel-card-header">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M6 12h12"/><path d="M10 18h4"/></svg>
                        <span class="panel-card-header-title">Parametreler</span>
                    </div>
                    <div class="panel-card-body" style="display:flex;flex-direction:column;gap:12px;">
                        <div id="paramsUyeListesi" class="report-param-grid">
                            <div class="filter-field span-2">
                                <span class="filter-label">Arama</span>
                                <input type="text" id="search" class="filter-input" placeholder="Ad, soyad, TCKN, telefon, e-posta">
                            </div>

                            <div class="filter-field">
                                <span class="filter-label">Üyelik Durumu</span>
                                <select id="statu" class="filter-select">
                                    <option value="">Tümü</option>
                                    <option value="aktif">Aktif</option>
                                    <option value="pasif">Pasif</option>
                                </select>
                            </div>

                            <div class="filter-field">
                                <span class="filter-label">Yaş Aralığı (İlk)</span>
                                <input type="number" id="yas_ilk" class="filter-input" min="0" max="130" placeholder="Örn: 18">
                            </div>

                            <div class="filter-field">
                                <span class="filter-label">Yaş Aralığı (Son)</span>
                                <input type="number" id="yas_son" class="filter-input" min="0" max="130" placeholder="Örn: 65">
                            </div>

                            <div class="filter-field">
                                <span class="filter-label">Cinsiyet</span>
                                <select id="cinsiyet" class="filter-select">
                                    <option value="">Tümü</option>
                                    <option value="erkek">Erkek</option>
                                    <option value="kadin">Kadın</option>
                                    <option value="diger">Diğer</option>
                                </select>
                            </div>

                            <div class="filter-field">
                                <span class="filter-label">İl</span>
                                <input type="text" id="il" class="filter-input" placeholder="İl adı girin">
                            </div>

                            <div class="filter-field">
                                <span class="filter-label">İlçe</span>
                                <input type="text" id="ilce" class="filter-input" placeholder="İlçe adı girin">
                            </div>

                            <div class="filter-field">
                                <span class="filter-label">Mahalle</span>
                                <input type="text" id="mahalle" class="filter-input" placeholder="Mahalle adı girin">
                            </div>

                            <div class="filter-field">
                                <span class="filter-label">Öğretim Durumu</span>
                                <select id="ogretim_durumu" class="filter-select">
                                    <option value="">Tümü</option>
                                    <option value="İlkokul">İlkokul</option>
                                    <option value="Ortaokul">Ortaokul</option>
                                    <option value="Lise">Lise</option>
                                    <option value="Önlisans">Önlisans</option>
                                    <option value="Lisans">Lisans</option>
                                    <option value="Yüksek Lisans">Yüksek Lisans</option>
                                    <option value="Doktora">Doktora</option>
                                </select>
                            </div>

                            <div class="filter-field">
                                <span class="filter-label">Ödünçte Kitabı Olanlar</span>
                                <select id="oduncte_kitabi_olanlar" class="filter-select">
                                    <option value="">Tümü</option>
                                    <option value="evet">Evet</option>
                                    <option value="hayir">Hayır</option>
                                </select>
                            </div>

                            <div class="filter-field">
                                <span class="filter-label">Kayıt Tarihi (İlk)</span>
                                <input type="date" id="kayit_tarihi_bas" class="filter-input">
                            </div>

                            <div class="filter-field">
                                <span class="filter-label">Kayıt Tarihi (Son)</span>
                                <input type="date" id="kayit_tarihi_bit" class="filter-input">
                            </div>

                            <div class="filter-field span-2">
                                <span class="filter-label">Maksimum Kayıt</span>
                                <select id="per_page" class="filter-select">
                                    <option value="250">250</option>
                                    <option value="500" selected>500</option>
                                    <option value="1000">1000</option>
                                    <option value="2000">2000</option>
                                </select>
                            </div>
                        </div>

                        <div id="paramsKullaniciKatalog" class="report-param-grid" style="display:none;">
                            <div class="filter-field span-2">
                                <span class="filter-label">İlk kayıt / Kopya ayrımı yap</span>
                                <select id="kkks_ayrim_yap" class="filter-select">
                                    <option value="0" selected>Hayır (sadece toplam)</option>
                                    <option value="1">Evet</option>
                                </select>
                            </div>

                            <div class="filter-field">
                                <span class="filter-label">Kayıt Tarihi (İlk)</span>
                                <input type="date" id="kkks_kayit_tarihi_bas" class="filter-input">
                            </div>

                            <div class="filter-field">
                                <span class="filter-label">Kayıt Tarihi (Son)</span>
                                <input type="date" id="kkks_kayit_tarihi_bit" class="filter-input">
                            </div>
                        </div>

                        <div id="paramsOduncListesi" class="report-param-grid" style="display:none;">
                            <div class="filter-field span-2">
                                <span class="filter-label">Arama</span>
                                <input type="text" id="od_search" class="filter-input" placeholder="Üye, TCKN, kitap adı, ISBN">
                            </div>

                            <div class="filter-field">
                                <span class="filter-label">Demirbaş No</span>
                                <input type="text" id="od_demirbas_no" class="filter-input" placeholder="Demirbaş no">
                            </div>

                            <div class="filter-field">
                                <span class="filter-label">Kütüphane</span>
                                <select id="od_kutuphane_id" class="filter-select">
                                    <option value="">Tümü</option>
                                    @foreach(($kutuphaneler ?? collect()) as $kutuphane)
                                        <option value="{{ $kutuphane->id }}">{{ $kutuphane->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="filter-field">
                                <span class="filter-label">Durum</span>
                                <select id="od_statu" class="filter-select">
                                    <option value="hepsi" selected>Tümü</option>
                                    <option value="aktif">Aktif</option>
                                    <option value="iade_edildi">İade Edildi</option>
                                    <option value="kayip">Kayıp</option>
                                </select>
                            </div>

                            <div class="filter-field">
                                <span class="filter-label">Gecikme Durumu</span>
                                <select id="od_gecikme_durumu" class="filter-select">
                                    <option value="hepsi">Tümü</option>
                                    <option value="geciken">Geciken</option>
                                    <option value="gecikmeyen">Gecikmeyen</option>
                                </select>
                            </div>

                            <div class="filter-field">
                                <span class="filter-label">Gecikme Gün (Min)</span>
                                <input type="number" id="od_gecikme_gun_min" class="filter-input" min="0" placeholder="0">
                            </div>

                            <div class="filter-field">
                                <span class="filter-label">Gecikme Gün (Max)</span>
                                <input type="number" id="od_gecikme_gun_max" class="filter-input" min="0" placeholder="30">
                            </div>

                            <div class="filter-field">
                                <span class="filter-label">Ödünç Tarihi (İlk)</span>
                                <input type="date" id="od_odunc_tarihi_bas" class="filter-input">
                            </div>

                            <div class="filter-field">
                                <span class="filter-label">Ödünç Tarihi (Son)</span>
                                <input type="date" id="od_odunc_tarihi_bit" class="filter-input">
                            </div>

                            <div class="filter-field">
                                <span class="filter-label">Planlanan İade (İlk)</span>
                                <input type="date" id="od_iade_planlanan_bas" class="filter-input">
                            </div>

                            <div class="filter-field">
                                <span class="filter-label">Planlanan İade (Son)</span>
                                <input type="date" id="od_iade_planlanan_bit" class="filter-input">
                            </div>

                            <div class="filter-field">
                                <span class="filter-label">Maksimum Kayıt</span>
                                <select id="od_per_page" class="filter-select">
                                    <option value="250">250</option>
                                    <option value="500" selected>500</option>
                                    <option value="1000">1000</option>
                                    <option value="2000">2000</option>
                                </select>
                            </div>
                        </div>

                        <button class="btn btn-primary" id="btnGenerate" onclick="generateReport()" style="width:100%;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            Rapor Oluştur
                        </button>
                        <div class="filter-meta">
                            <span id="resultCount">Henüz rapor üretilmedi</span>
                            <button type="button" class="btn btn-ghost btn-sm" onclick="resetFilters()">Filtreleri Temizle</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="right-panel">
                <div class="pdf-panel">
                    <div class="pdf-panel-header">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <span class="pdf-panel-header-title">PDF Önizleme</span>
                        <div class="pdf-panel-actions">
                            <button class="btn btn-outline btn-sm" id="btnExcel" onclick="downloadExcel()" style="display:none;">
                                Excel
                            </button>
                            <button class="btn btn-outline btn-sm" id="btnDownload" onclick="downloadPDF()" style="display:none;">
                                İndir
                            </button>
                            <button class="btn btn-ghost btn-sm" id="btnPrint" onclick="printPDF()" style="display:none;">
                                Yazdır
                            </button>
                        </div>
                    </div>
                    <div class="pdf-viewer-wrap">
                        <div class="pdf-placeholder" id="pdfPlaceholder">
                            <div class="pdf-placeholder-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            </div>
                            <div class="pdf-placeholder-text">PDF henüz oluşturulmadı</div>
                            <div class="pdf-placeholder-sub">Soldaki parametreleri seçip "Rapor Oluştur" butonuna tıklayın.</div>
                        </div>
                        <div class="loading-overlay" id="loadingOverlay">
                            <div class="loading-spinner"></div>
                            <div class="loading-text">Rapor hazırlanıyor...</div>
                        </div>
                        <iframe id="pdfFrame" title="Rapor PDF Önizleme"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container" id="toastContainer"></div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
@endsection

@section('scripts')
    <script>
        var lastPdfBlob = null;
        var lastPdfUrl = null;
        var tahomaB64 = null;
        var lastReportRows = [];
        var lastReportType = '';

        (function loadTahoma() {
            fetch('/fonts/tahoma.ttf')
                .then(function(r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.arrayBuffer();
                })
                .then(function(buf) {
                    var bytes = new Uint8Array(buf);
                    var binary = '';
                    var CHUNK = 8192;
                    for (var i = 0; i < bytes.length; i += CHUNK) {
                        binary += String.fromCharCode.apply(null, bytes.subarray(i, i + CHUNK));
                    }
                    tahomaB64 = btoa(binary);
                })
                .catch(function() {});
        })();

        function showToast(type, title, desc) {
            var c = document.getElementById('toastContainer');
            var t = document.createElement('div');
            t.className = 'toast ' + type;
            t.innerHTML = '<div>' + title + '</div>' + (desc ? '<div class="toast-desc">' + desc + '</div>' : '');
            c.appendChild(t);
            setTimeout(function() {
                t.style.animation = 'toast-out .3s ease forwards';
                setTimeout(function() { if (t.parentNode) t.parentNode.removeChild(t); }, 300);
            }, 4000);
        }

        function getEl(id) {
            return document.getElementById(id);
        }

        function clearPdfPreview() {
            var frame = getEl('pdfFrame');
            frame.src = '';
            frame.style.display = 'none';
            getEl('pdfPlaceholder').style.display = '';
            getEl('btnExcel').style.display = 'none';
            getEl('btnDownload').style.display = 'none';
            getEl('btnPrint').style.display = 'none';

            if (lastPdfUrl) {
                URL.revokeObjectURL(lastPdfUrl);
                lastPdfUrl = null;
            }
            lastPdfBlob = null;
            lastReportRows = [];
            lastReportType = '';
        }

        function selectedReportType() {
            var selected = document.querySelector('input[name="raporTipi"]:checked');
            return selected ? selected.value : 'uye-listesi';
        }

        function updateReportTypeUI() {
            var type = selectedReportType();
            getEl('paramsUyeListesi').style.display = type === 'uye-listesi' ? '' : 'none';
            getEl('paramsKullaniciKatalog').style.display = type === 'kullanici-katalog-kayit-sayilari' ? '' : 'none';
            getEl('paramsOduncListesi').style.display = type === 'odunc-listesi' ? '' : 'none';
        }

        function collectUyeListesiFilters() {
            var keys = [
                'search', 'statu', 'yas_ilk', 'yas_son', 'cinsiyet',
                'il', 'ilce', 'mahalle', 'ogretim_durumu', 'oduncte_kitabi_olanlar',
                'kayit_tarihi_bas', 'kayit_tarihi_bit', 'per_page'
            ];

            var params = new URLSearchParams();
            keys.forEach(function(key) {
                var value = (getEl(key).value || '').trim();
                if (value) params.set(key, value);
            });
            return params;
        }

        function collectKullaniciKatalogFilters() {
            var params = new URLSearchParams();
            var bas = (getEl('kkks_kayit_tarihi_bas').value || '').trim();
            var bit = (getEl('kkks_kayit_tarihi_bit').value || '').trim();
            var ayrimYap = (getEl('kkks_ayrim_yap').value || '0').trim();
            params.set('ayrim_yap', ayrimYap === '1' ? '1' : '0');
            if (bas) params.set('kayit_tarihi_bas', bas);
            if (bit) params.set('kayit_tarihi_bit', bit);
            return params;
        }

        function collectOduncFilters() {
            var params = new URLSearchParams();
            var keys = [
                'od_search', 'od_demirbas_no', 'od_kutuphane_id', 'od_statu', 'od_gecikme_durumu',
                'od_gecikme_gun_min', 'od_gecikme_gun_max',
                'od_odunc_tarihi_bas', 'od_odunc_tarihi_bit', 'od_iade_planlanan_bas', 'od_iade_planlanan_bit', 'od_per_page'
            ];
            keys.forEach(function(id) {
                var value = (getEl(id).value || '').trim();
                if (!value) return;
                if (id === 'od_search') params.set('search', value);
                else if (id === 'od_demirbas_no') params.set('demirbas_no', value);
                else if (id === 'od_kutuphane_id') params.set('kutuphane_id', value);
                else if (id === 'od_statu') params.set('statu', value);
                else if (id === 'od_gecikme_durumu') params.set('gecikme_durumu', value);
                else if (id === 'od_gecikme_gun_min') params.set('gecikme_gun_min', value);
                else if (id === 'od_gecikme_gun_max') params.set('gecikme_gun_max', value);
                else if (id === 'od_odunc_tarihi_bas') params.set('odunc_tarihi_bas', value);
                else if (id === 'od_odunc_tarihi_bit') params.set('odunc_tarihi_bit', value);
                else if (id === 'od_iade_planlanan_bas') params.set('iade_planlanan_bas', value);
                else if (id === 'od_iade_planlanan_bit') params.set('iade_planlanan_bit', value);
                else if (id === 'od_per_page') params.set('per_page', value);
            });
            if (!params.has('statu')) params.set('statu', 'hepsi');
            if (!params.has('gecikme_durumu')) params.set('gecikme_durumu', 'hepsi');
            return params;
        }

        function getUyeListesiFilterTags() {
            var tags = [];
            if (getEl('search').value.trim()) tags.push('Arama: ' + getEl('search').value.trim());
            if (getEl('statu').value) tags.push('Durum: ' + (getEl('statu').value === 'aktif' ? 'Aktif' : 'Pasif'));
            if (getEl('yas_ilk').value || getEl('yas_son').value) {
                tags.push('Yaş aralığı: ' + (getEl('yas_ilk').value || '...') + ' - ' + (getEl('yas_son').value || '...'));
            }
            if (getEl('cinsiyet').value) tags.push('Cinsiyet: ' + getEl('cinsiyet').options[getEl('cinsiyet').selectedIndex].text);
            if (getEl('il').value) tags.push('İl: ' + getEl('il').value);
            if (getEl('ilce').value) tags.push('İlçe: ' + getEl('ilce').value);
            if (getEl('mahalle').value) tags.push('Mahalle: ' + getEl('mahalle').value);
            if (getEl('ogretim_durumu').value) tags.push('Öğretim: ' + getEl('ogretim_durumu').value);
            if (getEl('oduncte_kitabi_olanlar').value) tags.push('Ödünçte kitap: ' + (getEl('oduncte_kitabi_olanlar').value === 'evet' ? 'Evet' : 'Hayır'));
            if (getEl('kayit_tarihi_bas').value || getEl('kayit_tarihi_bit').value) {
                tags.push('Kayıt tarihi: ' + (getEl('kayit_tarihi_bas').value || '...') + ' - ' + (getEl('kayit_tarihi_bit').value || '...'));
            }
            return tags;
        }

        function getKullaniciKatalogFilterTags() {
            var tags = [];
            tags.push('İlk kayıt/kopya ayrımı: ' + (getEl('kkks_ayrim_yap').value === '1' ? 'Evet' : 'Hayır'));
            if (getEl('kkks_kayit_tarihi_bas').value || getEl('kkks_kayit_tarihi_bit').value) {
                tags.push('Kayıt tarihi: ' + (getEl('kkks_kayit_tarihi_bas').value || '...') + ' - ' + (getEl('kkks_kayit_tarihi_bit').value || '...'));
            }
            return tags;
        }

        function getOduncFilterTags() {
            var tags = [];
            if (getEl('od_search').value.trim()) tags.push('Arama: ' + getEl('od_search').value.trim());
            if (getEl('od_demirbas_no').value.trim()) tags.push('Demirbaş: ' + getEl('od_demirbas_no').value.trim());
            if (getEl('od_kutuphane_id').value) tags.push('Kütüphane: ' + getEl('od_kutuphane_id').options[getEl('od_kutuphane_id').selectedIndex].text);
            if (getEl('od_statu').value && getEl('od_statu').value !== 'hepsi') tags.push('Durum: ' + getEl('od_statu').options[getEl('od_statu').selectedIndex].text);
            if (getEl('od_gecikme_durumu').value && getEl('od_gecikme_durumu').value !== 'hepsi') tags.push('Gecikme: ' + getEl('od_gecikme_durumu').options[getEl('od_gecikme_durumu').selectedIndex].text);
            if (getEl('od_gecikme_gun_min').value || getEl('od_gecikme_gun_max').value) {
                tags.push('Gecikme gün: ' + (getEl('od_gecikme_gun_min').value || '0') + ' - ' + (getEl('od_gecikme_gun_max').value || '...'));
            }
            if (getEl('od_odunc_tarihi_bas').value || getEl('od_odunc_tarihi_bit').value) {
                tags.push('Ödünç tarihi: ' + (getEl('od_odunc_tarihi_bas').value || '...') + ' - ' + (getEl('od_odunc_tarihi_bit').value || '...'));
            }
            if (getEl('od_iade_planlanan_bas').value || getEl('od_iade_planlanan_bit').value) {
                tags.push('Planlanan iade: ' + (getEl('od_iade_planlanan_bas').value || '...') + ' - ' + (getEl('od_iade_planlanan_bit').value || '...'));
            }
            return tags;
        }

        function renderPdfBlob(doc) {
            lastPdfBlob = doc.output('blob');
            if (lastPdfUrl) URL.revokeObjectURL(lastPdfUrl);
            lastPdfUrl = URL.createObjectURL(lastPdfBlob);

            getEl('pdfFrame').src = lastPdfUrl + '#toolbar=1&navpanes=0';
            getEl('pdfFrame').style.display = 'block';
            getEl('pdfPlaceholder').style.display = 'none';
            getEl('btnExcel').style.display = '';
            getEl('btnDownload').style.display = '';
            getEl('btnPrint').style.display = '';
        }

        function generateReport() {
            var btn = getEl('btnGenerate');
            var reportType = selectedReportType();
            var endpoint = reportType === 'kullanici-katalog-kayit-sayilari'
                ? '{{ route('rapor-sihirbazi.kullanici-katalog-kayit-sayilari.data') }}'
                : (reportType === 'odunc-listesi'
                    ? '{{ route('rapor-sihirbazi.odunc-listesi.data') }}'
                    : '{{ route('rapor-sihirbazi.uye-listesi.data') }}');
            var params = reportType === 'kullanici-katalog-kayit-sayilari'
                ? collectKullaniciKatalogFilters()
                : (reportType === 'odunc-listesi'
                    ? collectOduncFilters()
                    : collectUyeListesiFilters());

            btn.disabled = true;
            btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin .6s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Oluşturuluyor...';
            getEl('loadingOverlay').classList.add('active');
            clearPdfPreview();

            fetch(endpoint + '?' + params.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function(r) {
                    return r.text().then(function(text) {
                        var data = {};
                        try { data = text ? JSON.parse(text) : {}; } catch (e) { data = {}; }
                        if (!r.ok) {
                            var msg = (data && (data.message || data.error)) ? (data.message || data.error) : ('Veri alınamadı (HTTP ' + r.status + ')');
                            throw new Error(msg);
                        }
                        return data;
                    });
                })
                .then(function(data) {
                    var rows = data.rows || [];
                    getEl('resultCount').textContent = rows.length + ' kayıt rapora dahil edildi';

                    if (!rows.length) {
                        showToast('info', 'Kayıt bulunamadı', 'Parametrelere uygun kayıt bulunamadı.');
                        return;
                    }
                    lastReportRows = rows.slice();
                    lastReportType = reportType;

                    if (reportType === 'kullanici-katalog-kayit-sayilari') {
                        renderPdfBlob(buildKullaniciKatalogKayitPdf(rows, getKullaniciKatalogFilterTags(), !!data.ayrim_yap));
                    } else if (reportType === 'odunc-listesi') {
                        renderPdfBlob(buildOduncListesiPdf(rows, getOduncFilterTags()));
                    } else {
                        renderPdfBlob(buildUyeListesiPdf(rows, getUyeListesiFilterTags()));
                    }

                    showToast('success', 'Rapor oluşturuldu', rows.length + ' satır PDF önizlemeye yansıtıldı.');
                })
                .catch(function(err) {
                    showToast('error', 'Rapor hatası', err.message || 'Bilinmeyen hata.');
                })
                .finally(function() {
                    btn.disabled = false;
                    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg> Rapor Oluştur';
                    getEl('loadingOverlay').classList.remove('active');
                });
        }

        function buildUyeListesiPdf(rows, filterTags) {
            var jsPDF = window.jspdf.jsPDF;
            // A4 sabit: 210x297 mm (yatay kullanım)
            var doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: [210, 297] });

            if (tahomaB64) {
                doc.addFileToVFS('tahoma.ttf', tahomaB64);
                doc.addFont('tahoma.ttf', 'tahoma', 'normal');
                doc.setFont('tahoma', 'normal');
            }

            doc.setFontSize(14);
            doc.text('Rapor: Üye Listesi', 14, 14);

            doc.setFontSize(9);
            doc.text('Oluşturma Tarihi: ' + new Date().toLocaleString('tr-TR'), 14, 20);

            var filterLine = filterTags.length ? filterTags.join(' | ') : 'Filtre: Tüm Kayıtlar';
            var filterLines = doc.splitTextToSize(filterLine, 265);
            doc.text(filterLines, 14, 26);

            var startY = 26 + (filterLines.length * 4) + 3;
            var bodyRows = rows.map(function(r, idx) {
                return [
                    String(idx + 1),
                    r.ad_soyad || '—',
                    r.tc_kimlik || '—',
                    r.telefon || '—',
                    r.email || '—',
                    r.cinsiyet || '—',
                    r.statu || '—',
                    r.uyelik_baslangic || '—',
                    r.uyelik_bitis || '—',
                    r.il_ilce || '—',
                    r.mahalle || '—',
                    r.ogretim_durumu || '—'
                ];
            });

            doc.autoTable({
                startY: startY,
                tableWidth: 'auto',
                head: [[
                    '#', 'Ad Soyad', 'TC Kimlik', 'Telefon', 'E-posta',
                    'Cinsiyet', 'Durum', 'Üy. Başlangıç', 'Üy. Bitiş', 'İl / İlçe', 'Mahalle', 'Öğretim'
                ]],
                body: bodyRows,
                theme: 'grid',
                styles: {
                    font: tahomaB64 ? 'tahoma' : 'helvetica',
                    fontSize: 8,
                    cellPadding: 1.8,
                    textColor: [43, 35, 25],
                    lineColor: [217, 208, 194],
                    lineWidth: 0.1
                },
                headStyles: {
                    fillColor: [122, 92, 60],
                    textColor: [245, 240, 232],
                    fontStyle: 'normal'
                },
                alternateRowStyles: { fillColor: [251, 248, 243] },
                margin: { left: 10, right: 10, top: 8, bottom: 10 },
                columnStyles: {
                    0: { cellWidth: 9, halign: 'center' },
                    1: { cellWidth: 37 },
                    2: { cellWidth: 27 },
                    3: { cellWidth: 24 },
                    4: { cellWidth: 42 },
                    5: { cellWidth: 16, halign: 'center' },
                    6: { cellWidth: 15, halign: 'center' },
                    7: { cellWidth: 18, halign: 'center' },
                    8: { cellWidth: 18, halign: 'center' },
                    9: { cellWidth: 27 },
                    10: { cellWidth: 20 },
                    11: { cellWidth: 19 }
                },
                didDrawPage: function(data) {
                    var pageSize = doc.internal.pageSize;
                    var pageHeight = pageSize.height ? pageSize.height : pageSize.getHeight();
                    var pageWidth = pageSize.width ? pageSize.width : pageSize.getWidth();
                    doc.setFontSize(8);
                    doc.text('Toplam: ' + rows.length + ' uye', 10, pageHeight - 5);
                    doc.text('Sayfa ' + doc.internal.getNumberOfPages(), pageWidth - 24, pageHeight - 5);
                }
            });

            return doc;
        }

        function buildKullaniciKatalogKayitPdf(rows, filterTags, ayrimYap) {
            var jsPDF = window.jspdf.jsPDF;
            var doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: [210, 297] });

            if (tahomaB64) {
                doc.addFileToVFS('tahoma.ttf', tahomaB64);
                doc.addFont('tahoma.ttf', 'tahoma', 'normal');
                doc.setFont('tahoma', 'normal');
            }

            doc.setFontSize(14);
            doc.text('Rapor: Kullanıcı Katalog Kayıt Sayıları', 14, 14);
            doc.setFontSize(9);
            doc.text('Oluşturma Tarihi: ' + new Date().toLocaleString('tr-TR'), 14, 20);

            var filterLine = filterTags.length ? filterTags.join(' | ') : 'Filtre: Tüm Kayıtlar';
            var filterLines = doc.splitTextToSize(filterLine, 265);
            doc.text(filterLines, 14, 26);

            var startY = 26 + (filterLines.length * 4) + 3;
            var bodyRows = rows.map(function(r, idx) {
                if (!ayrimYap) {
                    return [
                        String(idx + 1),
                        r.kullanici || '—',
                        String(r.toplam || 0)
                    ];
                }
                return [
                    String(idx + 1),
                    r.kullanici || '—',
                    String(r.toplam || 0),
                    String(r.ilk_giris || 0),
                    String(r.kopya || 0)
                ];
            });

            doc.autoTable({
                startY: startY,
                tableWidth: 'auto',
                head: [ayrimYap
                    ? ['#', 'Kullanıcı', 'Toplam Kayıt', 'İlk Giriş', 'Kopya']
                    : ['#', 'Kullanıcı', 'Toplam Kayıt']],
                body: bodyRows,
                theme: 'grid',
                styles: {
                    font: tahomaB64 ? 'tahoma' : 'helvetica',
                    fontSize: 9,
                    cellPadding: 2.2,
                    textColor: [43, 35, 25],
                    lineColor: [217, 208, 194],
                    lineWidth: 0.1
                },
                headStyles: {
                    fillColor: [122, 92, 60],
                    textColor: [245, 240, 232],
                    fontStyle: 'normal'
                },
                alternateRowStyles: { fillColor: [251, 248, 243] },
                margin: { left: 10, right: 10, top: 8, bottom: 10 },
                columnStyles: ayrimYap
                    ? {
                        0: { cellWidth: 14, halign: 'center' },
                        1: { cellWidth: 130 },
                        2: { cellWidth: 40, halign: 'center' },
                        3: { cellWidth: 40, halign: 'center' },
                        4: { cellWidth: 40, halign: 'center' }
                    }
                    : {
                        0: { cellWidth: 14, halign: 'center' },
                        1: { cellWidth: 180 },
                        2: { cellWidth: 70, halign: 'center' }
                    },
                didDrawPage: function() {
                    var pageSize = doc.internal.pageSize;
                    var pageHeight = pageSize.height ? pageSize.height : pageSize.getHeight();
                    var pageWidth = pageSize.width ? pageSize.width : pageSize.getWidth();
                    var toplam = rows.reduce(function(acc, row) { return acc + (Number(row.toplam) || 0); }, 0);
                    doc.setFontSize(8);
                    if (ayrimYap) {
                        var ilkGiris = rows.reduce(function(acc, row) { return acc + (Number(row.ilk_giris) || 0); }, 0);
                        var kopya = rows.reduce(function(acc, row) { return acc + (Number(row.kopya) || 0); }, 0);
                        doc.text('Toplam katalog: ' + toplam + ' | Ilk giris: ' + ilkGiris + ' | Kopya: ' + kopya, 10, pageHeight - 5);
                    } else {
                        doc.text('Toplam katalog: ' + toplam, 10, pageHeight - 5);
                    }
                    doc.text('Sayfa ' + doc.internal.getNumberOfPages(), pageWidth - 24, pageHeight - 5);
                }
            });

            return doc;
        }

        function buildOduncListesiPdf(rows, filterTags) {
            var jsPDF = window.jspdf.jsPDF;
            var doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: [210, 297] });

            if (tahomaB64) {
                doc.addFileToVFS('tahoma.ttf', tahomaB64);
                doc.addFont('tahoma.ttf', 'tahoma', 'normal');
                doc.setFont('tahoma', 'normal');
            }

            doc.setFontSize(14);
            doc.text('Rapor: Ödünç Listesi', 14, 14);
            doc.setFontSize(9);
            doc.text('Oluşturma Tarihi: ' + new Date().toLocaleString('tr-TR'), 14, 20);

            var filterLine = filterTags.length ? filterTags.join(' | ') : 'Filtre: Tüm Kayıtlar';
            var filterLines = doc.splitTextToSize(filterLine, 265);
            doc.text(filterLines, 14, 26);

            var startY = 26 + (filterLines.length * 4) + 3;
            var bodyRows = rows.map(function(r, idx) {
                var gecikmeGun = Number(r.gecikme_gun || 0);
                if (isNaN(gecikmeGun)) gecikmeGun = 0;
                gecikmeGun = Math.abs(gecikmeGun);
                return [
                    String(idx + 1),
                    r.uye_ad || '—',
                    r.kitap_adi || '—',
                    r.demirbas || '—',
                    r.kutuphane || '—',
                    r.odunc_tarihi || '—',
                    r.iade_planlanan || '—',
                    r.iade_gercek || '—',
                    r.statu || '—',
                    r.gecikme_durumu || '—',
                    String(gecikmeGun)
                ];
            });

            doc.autoTable({
                startY: startY,
                tableWidth: 277,
                head: [['#', 'Uye', 'Kitap', 'Demirbas', 'Kutuphane', 'Odunc Tarihi', 'Planlanan Iade', 'Gercek Iade', 'Durum', 'Gecikme Durumu', 'Gecikme Gun']],
                body: bodyRows,
                theme: 'grid',
                styles: {
                    font: tahomaB64 ? 'tahoma' : 'helvetica',
                    fontSize: 8,
                    cellPadding: 1.9,
                    textColor: [43, 35, 25],
                    lineColor: [217, 208, 194],
                    lineWidth: 0.1
                },
                headStyles: {
                    fillColor: [122, 92, 60],
                    textColor: [245, 240, 232],
                    fontStyle: 'normal'
                },
                alternateRowStyles: { fillColor: [251, 248, 243] },
                margin: { left: 10, right: 10, top: 8, bottom: 10 },
                columnStyles: {
                    0: { cellWidth: 8, halign: 'center' },
                    1: { cellWidth: 34 },
                    2: { cellWidth: 58 },
                    3: { cellWidth: 23 },
                    4: { cellWidth: 32 },
                    5: { cellWidth: 23, halign: 'center' },
                    6: { cellWidth: 22, halign: 'center' },
                    7: { cellWidth: 22, halign: 'center' },
                    8: { cellWidth: 22, halign: 'center' },
                    9: { cellWidth: 23, halign: 'center' },
                    10: { cellWidth: 10, halign: 'center' }
                },
                didDrawPage: function() {
                    var pageSize = doc.internal.pageSize;
                    var pageHeight = pageSize.height ? pageSize.height : pageSize.getHeight();
                    var pageWidth = pageSize.width ? pageSize.width : pageSize.getWidth();
                    doc.setFontSize(8);
                    doc.text('Toplam odunc kaydi: ' + rows.length, 10, pageHeight - 5);
                    doc.text('Sayfa ' + doc.internal.getNumberOfPages(), pageWidth - 24, pageHeight - 5);
                }
            });

            return doc;
        }

        function resetFilters() {
            [
                'search', 'statu', 'yas_ilk', 'yas_son', 'cinsiyet', 'il', 'ilce', 'mahalle',
                'ogretim_durumu', 'oduncte_kitabi_olanlar', 'kayit_tarihi_bas', 'kayit_tarihi_bit',
                'kkks_ayrim_yap', 'kkks_kayit_tarihi_bas', 'kkks_kayit_tarihi_bit',
                'od_search', 'od_demirbas_no', 'od_kutuphane_id', 'od_statu', 'od_gecikme_durumu',
                'od_gecikme_gun_min', 'od_gecikme_gun_max',
                'od_odunc_tarihi_bas', 'od_odunc_tarihi_bit', 'od_iade_planlanan_bas', 'od_iade_planlanan_bit', 'od_per_page'
            ].forEach(function(id) {
                getEl(id).value = '';
            });
            getEl('per_page').value = '500';
            getEl('kkks_ayrim_yap').value = '0';
            getEl('od_statu').value = 'hepsi';
            getEl('od_gecikme_durumu').value = 'hepsi';
            getEl('od_per_page').value = '500';
            getEl('resultCount').textContent = 'Filtreler temizlendi';
            clearPdfPreview();
        }

        function downloadPDF() {
            if (!lastPdfBlob) return;
            var a = document.createElement('a');
            a.href = lastPdfUrl;
            var type = selectedReportType();
            var prefix = type === 'kullanici-katalog-kayit-sayilari'
                ? 'kullanici_katalog_kayit_sayilari_raporu_'
                : (type === 'odunc-listesi'
                    ? 'odunc_listesi_raporu_'
                    : 'uye_listesi_raporu_');
            a.download = prefix + new Date().toISOString().slice(0, 10) + '.pdf';
            a.click();
        }

        function printPDF() {
            var frame = getEl('pdfFrame');
            if (!frame || frame.style.display === 'none') return;
            frame.contentWindow.print();
        }

        function toExcelRows(reportType, rows) {
            if (reportType === 'kullanici-katalog-kayit-sayilari') {
                var ayrimYap = getEl('kkks_ayrim_yap').value === '1';
                return rows.map(function(r, idx) {
                    var base = {
                        'Sıra': idx + 1,
                        'Kullanıcı': r.kullanici || '',
                        'Toplam Kayıt': Number(r.toplam || 0)
                    };
                    if (ayrimYap) {
                        base['İlk Giriş'] = Number(r.ilk_giris || 0);
                        base['Kopya'] = Number(r.kopya || 0);
                    }
                    return base;
                });
            }

            if (reportType === 'odunc-listesi') {
                return rows.map(function(r, idx) {
                    return {
                        'Sıra': idx + 1,
                        'Üye': r.uye_ad || '',
                        'Üye TC': r.uye_tc || '',
                        'Kitap': r.kitap_adi || '',
                        'ISBN': r.isbn || '',
                        'Demirbaş': r.demirbas || '',
                        'Kütüphane': r.kutuphane || '',
                        'Ödünç Tarihi': r.odunc_tarihi || '',
                        'Planlanan İade': r.iade_planlanan || '',
                        'Gerçek İade': r.iade_gercek || '',
                        'Durum': r.statu || '',
                        'Gecikme Durumu': r.gecikme_durumu || '',
                        'Gecikme Gün': Number(r.gecikme_gun || 0)
                    };
                });
            }

            return rows.map(function(r, idx) {
                return {
                    'Sıra': idx + 1,
                    'Ad Soyad': r.ad_soyad || '',
                    'TC Kimlik': r.tc_kimlik || '',
                    'Telefon': r.telefon || '',
                    'E-posta': r.email || '',
                    'Cinsiyet': r.cinsiyet || '',
                    'Durum': r.statu || '',
                    'Üyelik Başlangıç': r.uyelik_baslangic || '',
                    'Üyelik Bitiş': r.uyelik_bitis || '',
                    'İl / İlçe': r.il_ilce || '',
                    'Mahalle': r.mahalle || '',
                    'Öğretim': r.ogretim_durumu || ''
                };
            });
        }

        function excelFilePrefix(reportType) {
            if (reportType === 'kullanici-katalog-kayit-sayilari') return 'kullanici_katalog_kayit_sayilari_raporu_';
            if (reportType === 'odunc-listesi') return 'odunc_listesi_raporu_';
            return 'uye_listesi_raporu_';
        }

        function downloadExcel() {
            if (!lastReportRows.length || !lastReportType) {
                showToast('info', 'Excel hazır değil', 'Önce bir rapor oluşturun.');
                return;
            }
            if (typeof XLSX === 'undefined') {
                showToast('error', 'Excel hatası', 'Excel kütüphanesi yüklenemedi.');
                return;
            }

            var excelRows = toExcelRows(lastReportType, lastReportRows);
            var sheet = XLSX.utils.json_to_sheet(excelRows);
            var wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, sheet, 'Rapor');

            var fileName = excelFilePrefix(lastReportType) + new Date().toISOString().slice(0, 10) + '.xlsx';
            XLSX.writeFile(wb, fileName);
        }

        document.querySelectorAll('input[name="raporTipi"]').forEach(function(el) {
            el.addEventListener('change', function() {
                updateReportTypeUI();
                clearPdfPreview();
                getEl('resultCount').textContent = 'Rapor tipi değiştirildi';
            });
        });
        updateReportTypeUI();
    </script>
@endsection
