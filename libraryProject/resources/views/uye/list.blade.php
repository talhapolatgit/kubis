@extends('layouts.base')

@section('title', 'Uyeler')

@section('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <style>
        .page-header{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:14px}
        .page-title{font-family:var(--font-serif);font-size:22px;font-weight:700;color:var(--foreground)}
        .page-subtitle{font-size:13px;color:var(--muted-foreground);margin-top:2px}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:9px 16px;border-radius:calc(var(--radius) - 2px);font-size:14px;font-weight:500;cursor:pointer;border:none;text-decoration:none}
        .btn-primary{background:var(--primary);color:var(--primary-foreground)}
        .btn-success,.btn-outline{background:transparent;color:var(--foreground);border:1px solid var(--border)}
        .btn-sm{padding:6px 12px;font-size:13px}
        .filters-card{margin-bottom:14px}
        .table-card,.form-card{background:var(--card);border:1px solid rgba(217,208,194,.6);border-radius:var(--radius);overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.04)}
        .form-card-header{padding:14px 16px;border-bottom:1px solid rgba(217,208,194,.5)}
        .form-card-title{display:flex;align-items:center;gap:8px;font-size:16px;font-weight:700;margin:0}
        .form-card-body{padding:14px 16px}
        .toolbar{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap}
        .filter-row{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;width:100%}
        .filter-field{flex:1;min-width:140px;max-width:220px}
        .field-label{display:block;font-size:12px;font-weight:600;color:var(--muted-foreground);margin-bottom:4px}
        .filter-input,.filter-select,.per-page-select{width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);font-size:14px;outline:none}
        .filter-input:focus,.filter-select:focus,.per-page-select:focus{border-color:var(--ring);box-shadow:0 0 0 2px rgba(122,92,60,.12)}
        .filter-input.filter-active,.filter-select.filter-active{border-color:var(--ring)}
        .filter-actions{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;margin-left:auto}
        .table-card-header{padding:14px 16px;border-bottom:1px solid rgba(217,208,194,.5);display:flex;align-items:center;justify-content:space-between;gap:12px}
        .table-wrap{overflow-x:auto}
        table{width:100%;border-collapse:collapse}
        thead{background:var(--secondary)}
        th{padding:11px 16px;text-align:left;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--muted-foreground);white-space:nowrap;border-bottom:1px solid var(--border)}
        td{padding:12px 16px;font-size:14px;border-bottom:1px solid rgba(217,208,194,.4);vertical-align:middle}
        tr:last-child td{border-bottom:none}
        .member-cell{display:flex;align-items:center;gap:10px}
        .member-avatar{width:34px;height:34px;border-radius:999px;background:var(--muted);display:inline-flex;align-items:center;justify-content:center;font-weight:700;color:var(--muted-foreground)}
        .member-name{font-weight:600}
        .member-name:hover{color:var(--primary)}
        .member-sub{font-size:12px;color:var(--muted-foreground)}
        .badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600}
        .badge-aktif{background:rgba(34,139,34,.12);color:#1a6b1a}
        .badge-pasif{background:rgba(197,48,48,.1);color:#9b1c1c}
        .table-footer{padding:12px 16px;display:flex;align-items:center;justify-content:space-between;border-top:1px solid rgba(217,208,194,.5);font-size:13px;color:var(--muted-foreground)}
        .tf-info{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
        .per-page-wrap{display:flex;align-items:center;gap:8px}
        .per-page-select{width:95px;padding:6px 8px}
        .pagination{display:flex;gap:6px;align-items:center}
        .page-btn{padding:6px 10px;border:1px solid var(--border);border-radius:8px;background:#fff;cursor:pointer;font-size:13px}
        .page-btn.active{background:var(--primary);color:var(--primary-foreground);border-color:var(--primary)}
        .page-btn.disabled{opacity:.45;cursor:default;pointer-events:none}
        .page-ellipsis{padding:0 4px}
        th.sortable-th{cursor:pointer;user-select:none}
        th.sortable-th:hover{color:var(--foreground)}
        th.sortable-th .sort-label{display:inline-flex;align-items:center;gap:6px}
        th.sortable-th .sort-caret{opacity:.35;font-size:10px;line-height:1}
        th.sortable-th.sort-active .sort-caret{opacity:1}
        .table-veil{position:absolute;inset:0;background:rgba(255,255,255,.6);display:none;align-items:center;justify-content:center;z-index:2}
        .table-veil.visible{display:flex}
        .table-card{position:relative}
        .veil-spinner{width:28px;height:28px;border-radius:50%;border:3px solid rgba(122,92,60,.18);border-top-color:var(--primary);animation:spin 1s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}}
        .empty-state{padding:48px 24px;text-align:center}
        .empty-title{font-size:15px;font-weight:600;color:var(--foreground);margin:8px 0 4px}
        .empty-desc{font-size:13px;color:var(--muted-foreground)}
        .empty-icon{width:56px;height:56px;border-radius:50%;background:var(--muted);display:flex;align-items:center;justify-content:center;margin:0 auto}
        .empty-icon svg{width:28px;height:28px;color:var(--muted-foreground)}
        .row-actions-btn{width:32px;height:32px;border-radius:6px;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--muted-foreground);transition:background .15s,color .15s}
        .row-actions-btn:hover{background:var(--muted);color:var(--foreground)}
        .row-actions-btn svg{width:16px;height:16px;pointer-events:none}
        #uyeFloatingMenu{position:fixed;z-index:1300;min-width:155px;background:var(--card);border:1px solid var(--border);border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.14);padding:4px;display:none}
        #uyeFloatingMenu.open{display:block}
        .row-actions-item{display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:6px;font-size:14px;font-weight:500;color:var(--foreground);text-decoration:none;cursor:pointer;transition:background .12s;white-space:nowrap;border:none;background:transparent;width:100%;text-align:left;font-family:inherit}
        .row-actions-item:hover{background:var(--secondary)}
        .row-actions-item.primary{font-weight:500}
        .row-actions-item.danger{color:var(--destructive)}
        .row-actions-item svg{width:15px;height:15px}
        .row-actions-sep{height:1px;background:var(--border)}
        .toast-container{position:fixed;top:16px;right:16px;z-index:1400;display:flex;flex-direction:column;gap:8px}
        .toast{padding:14px 20px;border-radius:10px;font-size:14px;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,.15);animation:toast-in .3s ease;max-width:380px}
        .toast.success{background:#2f7d32;color:#fff}
        .toast.error{background:var(--destructive);color:#fff}
        .toast-desc{font-size:13px;font-weight:400;opacity:.9;margin-top:2px}
        @keyframes toast-in{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}
        @keyframes toast-out{from{transform:translateX(0);opacity:1}to{transform:translateX(100%);opacity:0}}
        .modal-backdrop{position:fixed;inset:0;background:rgba(20,16,12,.5);display:none;align-items:center;justify-content:center;z-index:1500;padding:16px}
        .modal-backdrop.visible{display:flex}
        .modal-box{width:100%;max-width:460px;background:var(--card);border:1px solid var(--border);border-radius:12px;padding:18px}
        .modal-title{margin:10px 0 6px}
        .modal-desc{margin:0;color:var(--muted-foreground);font-size:14px}
        .modal-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:16px}
        .modal-icon{width:44px;height:44px;border-radius:999px;background:rgba(197,48,48,.12);display:flex;align-items:center;justify-content:center;color:var(--destructive)}
    </style>
@endsection

@section('breadcrumb')
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('katalog.index') }}" class="breadcrumb-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Ana Sayfa
        </a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Üyeler</span>
    </nav>
@endsection

@section('content')
        <div class="content-area">

            <!-- Sayfa Başlığı -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Üye Listesi</h1>
                    <p class="page-subtitle" id="pageSubtitle">Yükleniyor…</p>
                </div>
                <div class="page-actions">
                    @if(auth()->user()->hasYetki(12))
                    <a href="{{ route('uyeler.new') }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>
                        Yeni Üye Ekle
                    </a>
                    @endif
                    
                </div>
            </div>

            <div class="form-card filters-card">
                <div class="form-card-header">
                    <h2 class="form-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        Filtrele
                    </h2>
                </div>
                <div class="form-card-body">
                    <div class="toolbar">
                        <div class="filter-row">
                            <div class="filter-field">
                                <label class="field-label" for="filterAdInput">Ad</label>
                                <input type="text" class="filter-input" id="filterAdInput" placeholder="Ada göre..." autocomplete="off" />
                            </div>
                            <div class="filter-field">
                                <label class="field-label" for="filterSoyadInput">Soyad</label>
                                <input type="text" class="filter-input" id="filterSoyadInput" placeholder="Soyada göre..." autocomplete="off" />
                            </div>
                            <div class="filter-field">
                                <label class="field-label" for="filterTcInput">TC Kimlik No</label>
                                <input type="text" class="filter-input" id="filterTcInput" placeholder="TC kimlik..." autocomplete="off" inputmode="numeric" />
                            </div>
                            <div class="filter-field">
                                <label class="field-label" for="filterTelefonInput">Telefon</label>
                                <input type="text" class="filter-input" id="filterTelefonInput" placeholder="Telefon..." autocomplete="off" />
                            </div>
                            <div class="filter-field">
                                <label class="field-label" for="filterEmailInput">E-posta</label>
                                <input type="text" class="filter-input" id="filterEmailInput" placeholder="E-posta..." autocomplete="off" />
                            </div>
                            <div class="filter-field" style="min-width:180px;max-width:200px;">
                                <label class="field-label" for="statuFilter">Durum</label>
                                <select class="filter-select" id="statuFilter">
                                    <option value="">Tüm Durumlar</option>
                                    <option value="aktif">Aktif</option>
                                    <option value="pasif">Pasif</option>
                                </select>
                            </div>
                            <div class="filter-actions">
                                <button type="button" class="btn btn-outline" id="clearFiltersBtn" style="display:none;">Filtreyi Temizle</button>
                                <button type="button" class="btn btn-primary" id="applyFiltersBtn">Ara</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tablo -->
            <div class="table-card" id="tableCard">
                <div class="table-card-header">
                    <h2 class="form-card-title" style="margin:0;">Üye Listesi</h2>
                    <button class="btn btn-outline" id="exportBtn" title="Mevcut filtreyle Excel (CSV) indir">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                        Excel Olarak İndir
                    </button>
                </div>
                <div class="table-veil" id="tableVeil">
                    <div class="veil-spinner"></div>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th class="sortable-th" data-sort="ad_soyad" title="Sıralamak için tıklayın"><span class="sort-label">Üye</span><span class="sort-caret">◇</span></th>
                            <th style="width:130px;">TC Kimlik No</th>
                            <th style="width:140px;">Telefon</th>
                            <th style="width:130px;">İl / İlçe</th>
                            <th style="width:90px;">Durum</th>
                            <th class="sortable-th" data-sort="uyelik_baslangic" style="width:110px;" title="Sıralamak için tıklayın"><span class="sort-label">Üyelik Tarihi</span><span class="sort-caret">◇</span></th>
                            <th style="width:130px;"></th>
                        </tr>
                        </thead>
                        <tbody id="tableBody">
                        <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--muted-foreground);font-size:13px;">Yükleniyor…</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <div class="tf-info">
                        <span id="rangeInfo">—</span>
                        <div class="per-page-wrap">
                            <label for="perPageSelect" style="white-space:nowrap;">Sayfa başına:</label>
                            <select class="per-page-select" id="perPageSelect">
                                <option value="10" selected>10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="500">500</option>
                            </select>
                        </div>
                    </div>
                    <div class="pagination" id="pagination"></div>
                </div>
            </div>

        </div>

<!-- Floating İşlemler Menüsü (overflow:hidden'dan etkilenmemesi için body'de) -->
<div id="uyeFloatingMenu">
    <a id="fmProfil" href="#" class="row-actions-item primary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Profil
    </a>
    <div class="row-actions-sep"></div>
    <a id="fmDuzenle" href="#" class="row-actions-item">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
        Düzenle
    </a>
    <div class="row-actions-sep"></div>
    <a id="fmOduncVer" href="#" class="row-actions-item primary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M12 18v-6"/><path d="M9 15h6"/></svg>
        Ödünç Ver
    </a>
    <div class="row-actions-sep"></div>
    <button id="fmSil" class="row-actions-item danger">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
        Sil
    </button>
</div>

<!-- Toast -->
<div class="toast-container" id="toastContainer"></div>

<!-- Delete Modal -->
<div class="modal-backdrop" id="deleteModal">
    <div class="modal-box">
        <div class="modal-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
        </div>
        <h2 class="modal-title">Üyeyi Sil</h2>
        <p class="modal-desc" id="deleteModalDesc"></p>
        <div class="modal-actions">
            <button class="btn btn-outline" id="deleteCancelBtn">Vazgeç</button>
            <form id="deleteForm" method="POST" style="margin:0;">
                @csrf @method('DELETE')
                <button type="submit" class="btn" style="background:var(--destructive);color:#fff;">Evet, Sil</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // ══════════════════════════════════════════════════════════════════════════════
    // Toast
    // ══════════════════════════════════════════════════════════════════════════════
    function showToast(type, title, desc) {
        var c = document.getElementById('toastContainer');
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<div>' + title + '</div>' + (desc ? '<div class="toast-desc">' + desc + '</div>' : '');
        c.appendChild(t);
        setTimeout(function() {
            t.style.animation = 'toast-out 0.3s ease forwards';
            setTimeout(function() { if (t.parentNode) t.parentNode.removeChild(t); }, 300);
        }, 4000);
    }
    @if(session('success')) showToast('success', '{{ session('success') }}'); @endif
    @if(session('error'))   showToast('error',   '{{ session('error') }}');   @endif

    // ══════════════════════════════════════════════════════════════════════════════
    // State + request counter
    // ══════════════════════════════════════════════════════════════════════════════
    var urlParams  = new URLSearchParams(window.location.search);
    var initialPerPage = parseInt(urlParams.get('per_page') || '10', 10);
    if (![10, 20, 50, 100, 500].includes(initialPerPage)) initialPerPage = 10;
    var initialPage = parseInt(urlParams.get('page') || '1', 10);
    if (!Number.isFinite(initialPage) || initialPage < 1) initialPage = 1;
    var initialSortBy = urlParams.get('sort_by') || '';
    if (!['ad_soyad', 'uyelik_baslangic'].includes(initialSortBy)) initialSortBy = '';
    var initialSortDir = (urlParams.get('sort_dir') || 'asc').toLowerCase() === 'desc' ? 'desc' : 'asc';
    var legacySearch = (urlParams.get('search') || '').trim();
    var state      = {
        filter_ad: (urlParams.get('filter_ad') || '').trim(),
        filter_soyad: (urlParams.get('filter_soyad') || '').trim(),
        filter_tc_kimlik: (urlParams.get('filter_tc_kimlik') || '').trim(),
        filter_telefon: (urlParams.get('filter_telefon') || '').trim(),
        filter_email: (urlParams.get('filter_email') || '').trim(),
        statu: (urlParams.get('statu') || ''),
        per_page: initialPerPage,
        page: initialPage,
        sort_by: initialSortBy,
        sort_dir: initialSortDir
    };
    var filterInputIds = ['filterAdInput', 'filterSoyadInput', 'filterTcInput', 'filterTelefonInput', 'filterEmailInput'];
    var filterStateKeys = ['filter_ad', 'filter_soyad', 'filter_tc_kimlik', 'filter_telefon', 'filter_email'];
    var fetchTimer = null;
    var reqCounter = 0;   // yalnızca en son isteğin yanıtı işlenir

    function readFiltersFromDom() {
        return {
            filter_ad: (document.getElementById('filterAdInput').value || '').trim(),
            filter_soyad: (document.getElementById('filterSoyadInput').value || '').trim(),
            filter_tc_kimlik: (document.getElementById('filterTcInput').value || '').trim(),
            filter_telefon: (document.getElementById('filterTelefonInput').value || '').trim(),
            filter_email: (document.getElementById('filterEmailInput').value || '').trim(),
            statu: document.getElementById('statuFilter').value || ''
        };
    }

    function syncFilterInputsFromState() {
        document.getElementById('filterAdInput').value = state.filter_ad || '';
        document.getElementById('filterSoyadInput').value = state.filter_soyad || '';
        document.getElementById('filterTcInput').value = state.filter_tc_kimlik || '';
        document.getElementById('filterTelefonInput').value = state.filter_telefon || '';
        document.getElementById('filterEmailInput').value = state.filter_email || '';
        document.getElementById('statuFilter').value = state.statu || '';
    }

    function appendFilterParams(params) {
        if (state.filter_ad) params.set('filter_ad', state.filter_ad);
        if (state.filter_soyad) params.set('filter_soyad', state.filter_soyad);
        if (state.filter_tc_kimlik) params.set('filter_tc_kimlik', state.filter_tc_kimlik);
        if (state.filter_telefon) params.set('filter_telefon', state.filter_telefon);
        if (state.filter_email) params.set('filter_email', state.filter_email);
        if (state.statu) params.set('statu', state.statu);
    }

    function syncUrlFromState() {
        var u = new URL(window.location.href);
        ['search','filter_ad','filter_soyad','filter_tc_kimlik','filter_telefon','filter_email','statu','per_page','page','sort_by','sort_dir'].forEach(function(k){ u.searchParams.delete(k); });
        appendFilterParams(u.searchParams);
        if (state.per_page) u.searchParams.set('per_page', String(state.per_page));
        if (state.page > 1) u.searchParams.set('page', String(state.page));
        if (state.sort_by) {
            u.searchParams.set('sort_by', state.sort_by);
            u.searchParams.set('sort_dir', state.sort_dir || 'asc');
        }
        window.history.replaceState({}, '', u.toString());
    }

    function hasActiveFilters(filters) {
        filters = filters || state;
        return filterStateKeys.some(function(k) { return (filters[k] || '') !== ''; }) || (filters.statu || '') !== '';
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // HTML helpers
    // ══════════════════════════════════════════════════════════════════════════════
    function esc(str) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(str || ''));
        return d.innerHTML;
    }

    var checkIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';

    function buildRow(u) {
        var ilIlce = [u.il, u.ilce].filter(Boolean).join(' / ') || '—';
        var telHtml = esc(u.telefon || '—') + (u.telefon_dogrulandi
            ? ' <span title="Doğrulanmış" style="color:#16a34a;vertical-align:middle;">' + checkIcon + '</span>'
            : '');
        var stateBadge = '<span class="badge badge-' + u.statu + '">' + esc(u.statu_label) + '</span>';

        var safeAd = esc(u.ad_soyad).replace(/'/g, "\\'");
        var profileUrl = u.profile_url || ('/uyeler/' + u.id);
        return '<tr>' +
            '<td>' +
            '<div class="member-cell">' +
            '<div class="member-avatar">' + esc(u.initials || '?') + '</div>' +
            '<div>' +
            '<a href="' + esc(profileUrl) + '" class="member-name" style="color:var(--foreground);text-decoration:none;font-weight:600;">' + esc(u.ad_soyad) + '</a>' +
            '<div class="member-sub">' + esc(u.email || '—') + '</div>' +
            '</div>' +
            '</div>' +
            '</td>' +
            '<td style="font-family:monospace;font-size:13px;letter-spacing:0.04em;">' + esc(u.tc_kimlik) + '</td>' +
            '<td>' + telHtml + '</td>' +
            '<td style="font-size:13px;">' + esc(ilIlce) + '</td>' +
            '<td>' + stateBadge + '</td>' +
            '<td style="font-size:13px;color:var(--muted-foreground);">' + esc(u.uyelik_baslangic) + '</td>' +
            '<td style="text-align:right;padding-right:12px;">' +
            '<button class="row-actions-btn" onclick="toggleUyeMenu(' + u.id + ', \'' + profileUrl + '\', \'' + u.edit_url + '\', \'' + u.delete_url + '\', \'' + safeAd + '\', event)" title="İşlemler">' +
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>' +
            '</button>' +
            '</td>' +
            '</tr>';
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // Pagination
    // ══════════════════════════════════════════════════════════════════════════════
    function buildPagination(meta) {
        var container = document.getElementById('pagination');
        if (meta.last_page <= 1) { container.innerHTML = ''; return; }

        var cur = meta.current_page, last = meta.last_page;
        var html = '';

        html += '<button class="page-btn ' + (cur <= 1 ? 'disabled' : '') + '" onclick="goPage(' + (cur - 1) + ')">' +
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>' +
            '</button>';

        var pages = [];
        if (last <= 7) {
            for (var i = 1; i <= last; i++) pages.push(i);
        } else {
            pages.push(1);
            if (cur > 3) pages.push('…');
            for (var i = Math.max(2, cur - 1); i <= Math.min(last - 1, cur + 1); i++) pages.push(i);
            if (cur < last - 2) pages.push('…');
            pages.push(last);
        }
        pages.forEach(function(p) {
            if (p === '…') { html += '<span class="page-ellipsis">…</span>'; }
            else { html += '<button class="page-btn ' + (p === cur ? 'active' : '') + '" onclick="goPage(' + p + ')">' + p + '</button>'; }
        });

        html += '<button class="page-btn ' + (cur >= last ? 'disabled' : '') + '" onclick="goPage(' + (cur + 1) + ')">' +
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>' +
            '</button>';

        container.innerHTML = html;
    }

    function goPage(p) { if (p < 1) return; state.page = p; fetchTable(); }

    function updateSortHeaderDisplay() {
        document.querySelectorAll('th.sortable-th').forEach(function (th) {
            th.classList.remove('sort-active');
            var col = th.getAttribute('data-sort');
            var caret = th.querySelector('.sort-caret');
            if (!caret) return;
            if (state.sort_by && col === state.sort_by) {
                th.classList.add('sort-active');
                caret.textContent = state.sort_dir === 'desc' ? '▼' : '▲';
            } else {
                caret.textContent = '◇';
            }
        });
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // AJAX Fetch — reqCounter ile stale response önlemi
    // ══════════════════════════════════════════════════════════════════════════════
    function fetchTable(resetPage) {
        if (resetPage) state.page = 1;

        // Bu isteğe ait tekil numara — yalnızca bu numara hâlâ güncel sayı ile
        // eşleşiyorsa cevap DOM'a yazılır. Bu sayede hızlı filtre değişikliğinde
        // önceki yavaş yanıt yeni yanıtın üzerine yazmaz.
        var myReq = ++reqCounter;

        document.getElementById('tableVeil').classList.add('visible');

        var params = new URLSearchParams({
            per_page: state.per_page,
            page:     state.page,
        });
        appendFilterParams(params);
        if (legacySearch && !hasActiveFilters()) {
            params.set('search', legacySearch);
        }
        if (state.sort_by) {
            params.set('sort_by', state.sort_by);
            params.set('sort_dir', state.sort_dir || 'asc');
        }

        fetch('/uyeler/tablo?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function(res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function(result) {
                if (myReq !== reqCounter) return; // daha yeni bir istek var, bu yanıtı yoksay

                document.getElementById('tableVeil').classList.remove('visible');

                if (!result.success) { showToast('error', 'Hata', 'Veriler yüklenemedi.'); return; }

                var rows = Array.isArray(result.data) ? result.data
                    : (result.data && Array.isArray(result.data.data) ? result.data.data : []);

                var tbody = document.getElementById('tableBody');
                if (rows.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7">' +
                        '<div class="empty-state">' +
                        '<div class="empty-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>' +
                        '<p class="empty-title">Üye bulunamadı</p>' +
                        '<p class="empty-desc">Arama kriterlerinizi değiştirin veya yeni üye ekleyin.</p>' +
                        '</div></td></tr>';
                } else {
                    tbody.innerHTML = rows.map(buildRow).join('');
                }

                var m = result.meta;
                state.sort_by = m.sort_by || '';
                state.sort_dir = m.sort_dir || 'asc';
                state.page = m.current_page || state.page;
                document.getElementById('pageSubtitle').textContent = m.total + ' üye kayıtlı';
                document.getElementById('rangeInfo').textContent    = m.from + '–' + m.to + ' / ' + m.total + ' kayıt';
                buildPagination(m);
                updateSortHeaderDisplay();
                updateClearBtn();
                syncUrlFromState();
            })
            .catch(function() {
                if (myReq !== reqCounter) return;
                document.getElementById('tableVeil').classList.remove('visible');
                showToast('error', 'Bağlantı Hatası', 'Veriler yüklenemedi.');
            });
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // Filtre dinleyiciler
    // ══════════════════════════════════════════════════════════════════════════════
    function updateClearBtn() {
        var filters = readFiltersFromDom();
        var has = hasActiveFilters(filters);
        document.getElementById('clearFiltersBtn').style.display = has ? '' : 'none';
        filterInputIds.forEach(function(id, i) {
            var el = document.getElementById(id);
            if (el) el.classList.toggle('filter-active', (filters[filterStateKeys[i]] || '') !== '');
        });
        document.getElementById('statuFilter').classList.toggle('filter-active', (filters.statu || '') !== '');
    }

    function applyFilters() {
        var filters = readFiltersFromDom();
        state.filter_ad = filters.filter_ad;
        state.filter_soyad = filters.filter_soyad;
        state.filter_tc_kimlik = filters.filter_tc_kimlik;
        state.filter_telefon = filters.filter_telefon;
        state.filter_email = filters.filter_email;
        state.statu = filters.statu;
        legacySearch = '';
        fetchTable(true);
    }

    filterInputIds.forEach(function(id) {
        document.getElementById(id).addEventListener('input', updateClearBtn);
        document.getElementById(id).addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyFilters();
            }
        });
    });
    document.getElementById('statuFilter').addEventListener('change', updateClearBtn);
    document.getElementById('applyFiltersBtn').addEventListener('click', function () {
        applyFilters();
    });
    document.getElementById('statuFilter').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            applyFilters();
        }
    });

    document.getElementById('perPageSelect').addEventListener('change', function() {
        clearTimeout(fetchTimer);
        state.per_page = parseInt(this.value);
        fetchTable(true);
    });

    document.getElementById('clearFiltersBtn').addEventListener('click', function() {
        state.filter_ad = '';
        state.filter_soyad = '';
        state.filter_tc_kimlik = '';
        state.filter_telefon = '';
        state.filter_email = '';
        state.statu = '';
        state.sort_by = '';
        state.sort_dir = 'asc';
        legacySearch = '';
        syncFilterInputsFromState();
        updateSortHeaderDisplay();
        fetchTable(true);
    });

    document.querySelectorAll('th.sortable-th').forEach(function (th) {
        th.addEventListener('click', function () {
            var col = this.getAttribute('data-sort');
            if (!col) return;
            if (state.sort_by === col) {
                state.sort_dir = state.sort_dir === 'asc' ? 'desc' : 'asc';
            } else {
                state.sort_by = col;
                state.sort_dir = 'asc';
            }
            updateSortHeaderDisplay();
            fetchTable(true);
        });
    });

    // ══════════════════════════════════════════════════════════════════════════════
    // Excel Export
    // ══════════════════════════════════════════════════════════════════════════════
    document.getElementById('exportBtn').addEventListener('click', function() {
        var params = new URLSearchParams();
        appendFilterParams(params);
        if (legacySearch && !hasActiveFilters()) {
            params.set('search', legacySearch);
        }
        var a = document.createElement('a');
        a.href = '/uyeler/export?' + params.toString();
        a.download = '';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    });

    // ══════════════════════════════════════════════════════════════════════════════
    // Delete Modal
    // ══════════════════════════════════════════════════════════════════════════════
    var deleteModal = document.getElementById('deleteModal');
    var deleteForm  = document.getElementById('deleteForm');

    function confirmDelete(url, name) {
        document.getElementById('deleteModalDesc').textContent = '"' + name + '" üyesini silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.';
        deleteForm.action = url;
        deleteModal.classList.add('visible');
    }
    document.getElementById('deleteCancelBtn').addEventListener('click', function() { deleteModal.classList.remove('visible'); });
    deleteModal.addEventListener('click', function(e) { if (e.target === deleteModal) deleteModal.classList.remove('visible'); });

    // ══════════════════════════════════════════════════════════════════════════════
    // Row Actions — Floating menü (position:fixed, overflow:hidden'dan etkilenmez)
    // ══════════════════════════════════════════════════════════════════════════════
    var floatingMenu   = document.getElementById('uyeFloatingMenu');
    var openUyeMenuBtn = null; // şu an menüyü açan buton referansı

    function closeUyeMenu() {
        floatingMenu.classList.remove('open');
        openUyeMenuBtn = null;
    }

    function toggleUyeMenu(id, profileUrl, editUrl, deleteUrl, adSoyad, event) {
        event.stopPropagation();
        var btn = event.currentTarget;

        // Aynı butona tekrar basılırsa kapat
        if (openUyeMenuBtn === btn) { closeUyeMenu(); return; }

        // Menü bağlantılarını güncelle
        document.getElementById('fmProfil').href  = profileUrl;
        document.getElementById('fmDuzenle').href  = editUrl;
        document.getElementById('fmOduncVer').href = '/odunc/new?uye_id=' + id;
        document.getElementById('fmSil').onclick   = function(e) {
            e.stopPropagation();
            closeUyeMenu();
            confirmDelete(deleteUrl, adSoyad);
        };

        // Konumlandır: butonun altına veya üstüne (viewport taşmasını önle)
        floatingMenu.style.visibility = 'hidden';
        floatingMenu.classList.add('open');
        var rect    = btn.getBoundingClientRect();
        var mH      = floatingMenu.offsetHeight;
        var mW      = floatingMenu.offsetWidth;
        var spaceBelow = window.innerHeight - rect.bottom;
        var top, left;

        // Altta yer varsa altına, yoksa üstüne aç
        if (spaceBelow >= mH + 8) {
            top = rect.bottom + 4;
        } else {
            top = rect.top - mH - 4;
        }

        // Sağ kenardan taşmasın
        left = rect.right - mW;
        if (left < 8) left = 8;

        floatingMenu.style.top        = top + 'px';
        floatingMenu.style.left       = left + 'px';
        floatingMenu.style.visibility = '';

        openUyeMenuBtn = btn;
    }

    // Menü dışına tıklayınca kapat
    document.addEventListener('click', function(e) {
        if (openUyeMenuBtn && !floatingMenu.contains(e.target)) {
            closeUyeMenu();
        }
    });

    // Scroll'da menüyü kapat
    window.addEventListener('scroll', closeUyeMenu, true);

    // ══════════════════════════════════════════════════════════════════════════════
    // Boot
    // ══════════════════════════════════════════════════════════════════════════════
    syncFilterInputsFromState();
    document.getElementById('perPageSelect').value = String(state.per_page || 10);
    updateClearBtn();
    updateSortHeaderDisplay();
    fetchTable();
</script>
@endsection
