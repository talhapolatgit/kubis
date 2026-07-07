@extends('layouts.base')

@section('title', 'Kutuphane Listesi')

@section('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <style>
        /* Page Header */
        .page-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .page-title { font-family: var(--font-serif); font-size: 22px; font-weight: 700; color: var(--foreground); }
        .page-subtitle { font-size: 13px; color: var(--muted-foreground); margin-top: 2px; }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 9px 16px; border-radius: calc(var(--radius) - 2px); font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.15s, opacity 0.15s; border: none; text-decoration: none; }
        .btn svg { width: 16px; height: 16px; }
        .btn-primary { background: var(--primary); color: var(--primary-foreground); }
        .btn-primary:hover { opacity: 0.9; }
        .btn-outline { background: transparent; color: var(--foreground); border: 1px solid var(--border); }
        .btn-outline:hover { background: var(--muted); }
        .btn-sm { padding: 6px 12px; font-size: 13px; }
        .table-loading-wrapper{position:relative}
        .table-loading-overlay{position:absolute;inset:0;background:rgba(255,255,255,.6);display:none;align-items:center;justify-content:center;z-index:2}
        .table-loading-overlay.show{display:flex}
        .spinner{width:28px;height:28px;border-radius:50%;border:3px solid rgba(122,92,60,.18);border-top-color:var(--primary);animation:spin 1s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}}
        .filters-card { margin-bottom: 14px; }
        .form-card { background: var(--card); border: 1px solid rgba(217,208,194,0.6); border-radius: var(--radius); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
        .form-card-header { padding: 14px 16px; border-bottom: 1px solid rgba(217,208,194,0.5); }
        .form-card-title { display: flex; align-items: center; gap: 8px; font-size: 16px; font-weight: 700; margin: 0; }
        .form-card-body { padding: 14px 16px; }
        .table-card-header { padding: 14px 16px; border-bottom: 1px solid rgba(217,208,194,0.5); display: flex; align-items: center; justify-content: space-between; gap: 12px; }

        /* Search Bar */
        .search-bar { display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; }
        .search-bar .search-input-wrap { flex: 1; min-width: 200px; max-width: 360px; }
        .search-input-wrap { position: relative; flex: 1; max-width: 360px; }
        .search-input-wrap svg { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--muted-foreground); pointer-events: none; }
        .search-input { width: 100%; padding: 8px 12px 8px 34px; border: 1px solid var(--border); border-radius: calc(var(--radius) - 2px); background: var(--card); color: var(--foreground); font-size: 14px; outline: none; transition: border-color 0.15s, box-shadow 0.15s; }
        .search-input:focus { border-color: var(--ring); box-shadow: 0 0 0 2px rgba(122,92,60,0.15); }
        .search-input::placeholder { color: var(--muted-foreground); opacity: 0.7; }

        .filter-field-statu { min-width: 150px; }
        .filter-label { display: block; font-size: 12px; font-weight: 600; color: var(--muted-foreground); margin-bottom: 6px; }
        .filter-select { width: 100%; padding: 8px 10px; border: 1px solid var(--border); border-radius: calc(var(--radius) - 2px); background: var(--card); font-size: 14px; outline: none; }
        .filter-select:focus { border-color: var(--ring); box-shadow: 0 0 0 2px rgba(122,92,60,0.12); }

        th.sortable-th { cursor: pointer; user-select: none; }
        th.sortable-th:hover { color: var(--foreground); }
        th.sortable-th .sort-label { display: inline-flex; align-items: center; gap: 6px; }
        th.sortable-th .sort-caret { opacity: 0.35; font-size: 10px; line-height: 1; }
        th.sortable-th.sort-active .sort-caret { opacity: 1; }

        /* Table Card */
        .table-card { background: var(--card); border: 1px solid rgba(217,208,194,0.6); border-radius: var(--radius); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: var(--secondary); }
        th { padding: 11px 16px; text-align: left; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--muted-foreground); white-space: nowrap; border-bottom: 1px solid var(--border); }
        td { padding: 13px 16px; font-size: 14px; border-bottom: 1px solid rgba(217,208,194,0.4); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tbody tr { transition: background 0.12s; }
        tbody tr:hover { background: rgba(237,232,222,0.5); }

        /* Badge */
        .badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .badge-green { background: rgba(34,139,34,0.12); color: #1a6b1a; }
        .badge-red { background: rgba(197,48,48,0.1); color: #9b1c1c; }
        .badge svg { width: 8px; height: 8px; }

        /* Table Footer */
        .table-footer { padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid rgba(217,208,194,0.5); font-size: 13px; color: var(--muted-foreground); }

        /* Empty State */
        .empty-state { padding: 60px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 12px; }
        .empty-icon { width: 56px; height: 56px; border-radius: 50%; background: var(--muted); display: flex; align-items: center; justify-content: center; }
        .empty-icon svg { width: 28px; height: 28px; color: var(--muted-foreground); }
        .empty-title { font-size: 15px; font-weight: 600; color: var(--foreground); }
        .empty-desc { font-size: 13px; color: var(--muted-foreground); }

        /* Toast */
        .toast-container { position: fixed; top: 16px; right: 16px; z-index: 1000; display: flex; flex-direction: column; gap: 8px; }
        .toast { padding: 14px 20px; border-radius: 10px; font-size: 14px; font-weight: 500; box-shadow: 0 4px 12px rgba(0,0,0,0.15); animation: toast-in 0.3s ease; max-width: 380px; }
        .toast.success { background: #2f7d32; color: white; }
        .toast.error { background: var(--destructive); color: white; }
        .toast-desc { font-size: 13px; font-weight: 400; opacity: 0.9; margin-top: 2px; }
        @keyframes toast-in { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes toast-out { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header { flex-direction: column; align-items: flex-start; }
        }
    </style>
@endsection

@section('breadcrumb')
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('katalog.index') }}" class="breadcrumb-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Ana Sayfa
        </a>
        <span class="breadcrumb-sep">/</span>
        <span class="breadcrumb-current">Kütüphaneler</span>
    </nav>
@endsection

@section('content')
<div class="toast-container" id="toastContainer"></div>
        <div class="content-area">

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Kütüphaneler</h1>
                    <p class="page-subtitle" id="kutuphanePageSubtitle">Sistemde kayıtlı {{ $kutuphaneler->total() }} kütüphane</p>
                </div>
                <a href="{{ route('kutuphane.new') }}" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>
                    Yeni Kütüphane
                </a>
            </div>

            <div class="form-card filters-card">
                <div class="form-card-header">
                    <h2 class="form-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        Filtrele
                    </h2>
                </div>
                <div class="form-card-body">
                    <!-- Search -->
                    <div class="search-bar">
                        <div class="search-input-wrap">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            <input type="text" class="search-input" id="searchInput" placeholder="Kütüphane adı ara..." value="{{ request('search') }}" autocomplete="off" />
                        </div>
                        <div class="filter-field-statu">
                            <label class="filter-label" for="filterStatu">Durum</label>
                            <select class="filter-select" id="filterStatu" name="statu">
                                <option value="" {{ ($activeStatu ?? '') === '' ? 'selected' : '' }}>Tümü</option>
                                <option value="aktif" {{ ($activeStatu ?? '') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="pasif" {{ ($activeStatu ?? '') === 'pasif' ? 'selected' : '' }}>Pasif</option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-outline" id="clearFiltersBtn">Filtreyi Temizle</button>
                        <button type="button" class="btn btn-primary" id="kutuphaneSearchBtn">Ara</button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-card">
                <div class="table-card-header">
                    <h2 class="form-card-title" style="margin:0;">Kütüphane Listesi</h2>
                    <button type="button" class="btn btn-outline" id="exportExcelBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                        Excel Olarak İndir
                    </button>
                </div>
                <div class="table-loading-wrapper">
                    <div class="table-loading-overlay" id="tableLoading"><div class="spinner"></div></div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th class="sortable-th" data-sort="title" id="thSortTitle" title="Sıralamak için tıklayın">
                                    <span class="sort-label">Kütüphane Adı</span><span class="sort-caret" aria-hidden="true">◇</span>
                                </th>
                                <th class="sortable-th" data-sort="eser_sayisi" id="thSortEserSayisi" title="Sıralamak için tıklayın">
                                    <span class="sort-label">Eser Sayısı</span><span class="sort-caret" aria-hidden="true">◇</span>
                                </th>
                                <th>Adres</th>
                                <th>Telefon</th>
                                <th>E-posta</th>
                                <th>Durum</th>
                                <th>Kayıt Tarihi</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="kutuphaneTableBody">
                            @forelse($kutuphaneler as $kutuphane)
                            <tr>
                                <td style="color:var(--muted-foreground);font-size:13px;">{{ $kutuphane->id }}</td>
                                <td style="font-weight:600;">{{ $kutuphane->title }}</td>
                                <td style="font-size:13px;">{{ (int) ($kutuphane->eser_sayisi ?? 0) }}</td>
                                <td style="color:var(--muted-foreground);font-size:13px;">{{ $kutuphane->address ?? '—' }}</td>
                                <td style="font-size:13px;">{{ $kutuphane->phone ?? '—' }}</td>
                                <td style="font-size:13px;">{{ $kutuphane->email ?? '—' }}</td>
                                <td>
                                    @if($kutuphane->statu === 'aktif')
                                        <span class="badge badge-green">
                                            <svg viewBox="0 0 8 8" fill="currentColor"><circle cx="4" cy="4" r="4"/></svg>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="badge badge-red">
                                            <svg viewBox="0 0 8 8" fill="currentColor"><circle cx="4" cy="4" r="4"/></svg>
                                            Pasif
                                        </span>
                                    @endif
                                </td>
                                <td style="font-size:13px;color:var(--muted-foreground);">{{ \Carbon\Carbon::parse($kutuphane->created_at)->format('d.m.Y') }}</td>
                                @if(auth()->user()->hasYetki(19))
                                <td>
                                    <a href="{{ route('kutuphane.edit', $kutuphane->id) }}" class="btn btn-outline btn-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Düzenle
                                    </a>
                                </td>
                                @endif
                                
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                        </div>
                                        <p class="empty-title">Kütüphane bulunamadı</p>
                                        <p class="empty-desc">Yeni kütüphane eklemek için "Yeni Kütüphane" butonunu kullanın.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                </div>
                <div class="table-footer" id="kutuphaneTableFooter">
                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                        <span id="kutuphanePaginationInfo">{{ $kutuphaneler->firstItem() ?? 0 }}–{{ $kutuphaneler->lastItem() ?? 0 }} / {{ $kutuphaneler->total() }} kayıt</span>
                        <label for="perPageSelectFooter">Sayfa başına:</label>
                        <select class="filter-select" id="perPageSelectFooter" style="width:95px;padding:6px 8px;">
                            <option value="10" {{ (int) ($perPage ?? 20) === 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ (int) ($perPage ?? 20) === 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ (int) ($perPage ?? 20) === 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ (int) ($perPage ?? 20) === 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <div style="display:flex;gap:6px;" id="kutuphanePaginationNav">
                        @if($kutuphaneler->onFirstPage())
                            <button type="button" class="btn btn-outline btn-sm" disabled style="opacity:0.4;cursor:default;">‹ Önceki</button>
                        @else
                            <button type="button" class="btn btn-outline btn-sm kutuphane-page-btn" data-page="{{ $kutuphaneler->currentPage() - 1 }}">‹ Önceki</button>
                        @endif
                        @if($kutuphaneler->hasMorePages())
                            <button type="button" class="btn btn-outline btn-sm kutuphane-page-btn" data-page="{{ $kutuphaneler->currentPage() + 1 }}">Sonraki ›</button>
                        @else
                            <button type="button" class="btn btn-outline btn-sm" disabled style="opacity:0.4;cursor:default;">Sonraki ›</button>
                        @endif
                    </div>
                </div>
            </div>

        </div>
@endsection

@section('scripts')
<script>
    var kutuphaneListUrl = @json(route('kutuphane.index'));
    var kutuphaneExportUrl = @json(route('kutuphane.export'));
    var kutuphaneEditBase = @json(url('/kutuphane'));
    var kutuphaneReqCounter = 0;
    var sortBy = @json(($activeSortBy ?? '') !== '' ? ($activeSortBy ?? '') : '');
    var sortDir = @json((($activeSortBy ?? '') === 'title' || ($activeSortBy ?? '') === 'eser_sayisi') ? (($activeSortDir ?? 'asc') === 'desc' ? 'desc' : 'asc') : 'asc');

    // Toast
    function showToast(type, title, desc) {
        var c = document.getElementById('toastContainer');
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<div>' + title + '</div>' + (desc ? '<div class="toast-desc">' + desc + '</div>' : '');
        c.appendChild(t);
        setTimeout(function() { t.style.animation = 'toast-out 0.3s ease forwards'; setTimeout(function() { if (t.parentNode) t.parentNode.removeChild(t); }, 300); }, 3500);
    }

    @if(session('success'))
        showToast('success', 'Başarılı', '{{ session('success') }}');
    @endif

    function escapeHtml(str) {
        return (str == null ? '' : String(str)).replace(/[&<>"']/g, function(m) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m];
        });
    }

    function buildKutuphaneRows(rows, canEdit) {
        if (!rows || rows.length === 0) {
            var colSpan = canEdit ? 9 : 8;
            return '<tr><td colspan="' + colSpan + '"><div class="empty-state"><div class="empty-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></div><p class="empty-title">Kütüphane bulunamadı</p><p class="empty-desc">Aramanızı değiştirin veya yeni kütüphane ekleyin.</p></div></td></tr>';
        }
        return rows.map(function(k) {
            var statuHtml = k.statu === 'aktif'
                ? '<span class="badge badge-green"><svg viewBox="0 0 8 8" fill="currentColor"><circle cx="4" cy="4" r="4"/></svg>Aktif</span>'
                : '<span class="badge badge-red"><svg viewBox="0 0 8 8" fill="currentColor"><circle cx="4" cy="4" r="4"/></svg>Pasif</span>';
            var editCell = '';
            if (canEdit) {
                var editHref = kutuphaneEditBase + '/' + k.id + '/edit';
                editCell = '<td><a href="' + escapeHtml(editHref) + '" class="btn btn-outline btn-sm"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Düzenle</a></td>';
            }
            return '<tr>'
                + '<td style="color:var(--muted-foreground);font-size:13px;">' + k.id + '</td>'
                + '<td style="font-weight:600;">' + escapeHtml(k.title) + '</td>'
                + '<td style="font-size:13px;">' + (k.eser_sayisi || 0) + '</td>'
                + '<td style="color:var(--muted-foreground);font-size:13px;">' + (k.address ? escapeHtml(k.address) : '—') + '</td>'
                + '<td style="font-size:13px;">' + (k.phone ? escapeHtml(k.phone) : '—') + '</td>'
                + '<td style="font-size:13px;">' + (k.email ? escapeHtml(k.email) : '—') + '</td>'
                + '<td>' + statuHtml + '</td>'
                + '<td style="font-size:13px;color:var(--muted-foreground);">' + escapeHtml(k.created_at) + '</td>'
                + editCell
                + '</tr>';
        }).join('');
    }

    function updateKutuphaneSortHeader() {
        document.querySelectorAll('th.sortable-th').forEach(function (th) {
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

    function bindKutuphanePagination() {
        document.querySelectorAll('#kutuphanePaginationNav .kutuphane-page-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var p = parseInt(this.getAttribute('data-page'), 10);
                if (!isNaN(p) && p > 0) fetchKutuphaneList(p);
            });
        });
    }

    function fetchKutuphaneList(page) {
        var myReq = ++kutuphaneReqCounter;
        var q = document.getElementById('searchInput').value.trim();
        var statu = document.getElementById('filterStatu').value;
        var perPage = document.getElementById('perPageSelectFooter').value || '20';
        var params = new URLSearchParams();
        if (q) params.set('search', q);
        if (statu) params.set('statu', statu);
        if (perPage) params.set('per_page', perPage);
        if (sortBy) {
            params.set('sort_by', sortBy);
            params.set('sort_dir', sortDir || 'asc');
        }
        if (page && page > 1) params.set('page', String(page));
        document.getElementById('tableLoading').classList.add('show');

        fetch(kutuphaneListUrl + (params.toString() ? ('?' + params.toString()) : ''), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function(r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function(data) {
            if (myReq !== kutuphaneReqCounter) return;
            var rows = data.rows || [];
            var meta = data.meta || {};
            var canEdit = !!meta.can_edit;

            document.getElementById('kutuphaneTableBody').innerHTML = buildKutuphaneRows(rows, canEdit);

            if (meta.sort_by) {
                sortBy = meta.sort_by;
                sortDir = meta.sort_dir || 'asc';
            } else {
                sortBy = '';
                sortDir = 'asc';
            }
            var statuSel = document.getElementById('filterStatu');
            if (statuSel) statuSel.value = meta.statu != null && meta.statu !== '' ? meta.statu : '';
            if (meta.per_page != null) document.getElementById('perPageSelectFooter').value = String(meta.per_page);
            updateKutuphaneSortHeader();

            var total = meta.total != null ? meta.total : 0;
            var from = meta.from != null ? meta.from : 0;
            var to = meta.to != null ? meta.to : 0;
            document.getElementById('kutuphanePaginationInfo').textContent = from + '–' + to + ' / ' + total + ' kayıt';

            var sub = document.getElementById('kutuphanePageSubtitle');
            if (sub) sub.textContent = 'Sistemde kayıtlı ' + total + ' kütüphane';

            var cp = meta.current_page || 1;
            var lp = meta.last_page || 1;
            var nav = document.getElementById('kutuphanePaginationNav');
            var prevHtml = cp <= 1
                ? '<button type="button" class="btn btn-outline btn-sm" disabled style="opacity:0.4;cursor:default;">‹ Önceki</button>'
                : '<button type="button" class="btn btn-outline btn-sm kutuphane-page-btn" data-page="' + (cp - 1) + '">‹ Önceki</button>';
            var nextHtml = cp >= lp
                ? '<button type="button" class="btn btn-outline btn-sm" disabled style="opacity:0.4;cursor:default;">Sonraki ›</button>'
                : '<button type="button" class="btn btn-outline btn-sm kutuphane-page-btn" data-page="' + (cp + 1) + '">Sonraki ›</button>';
            nav.innerHTML = prevHtml + nextHtml;
            bindKutuphanePagination();

            var url = new URL(window.location.href);
            if (q) url.searchParams.set('search', q); else url.searchParams.delete('search');
            if (statu) url.searchParams.set('statu', statu); else url.searchParams.delete('statu');
            if (perPage) url.searchParams.set('per_page', perPage); else url.searchParams.delete('per_page');
            if (sortBy) {
                url.searchParams.set('sort_by', sortBy);
                url.searchParams.set('sort_dir', sortDir || 'asc');
            } else {
                url.searchParams.delete('sort_by');
                url.searchParams.delete('sort_dir');
            }
            if (cp > 1) url.searchParams.set('page', String(cp)); else url.searchParams.delete('page');
            window.history.replaceState({}, '', url.toString());
        })
        .catch(function() {
            if (myReq !== kutuphaneReqCounter) return;
            showToast('error', 'Hata', 'Liste yüklenirken bir sorun oluştu.');
        })
        .finally(function () {
            document.getElementById('tableLoading').classList.remove('show');
        });
    }

    var searchInput = document.getElementById('searchInput');
    document.getElementById('kutuphaneSearchBtn').addEventListener('click', function() {
        fetchKutuphaneList(1);
    });
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            fetchKutuphaneList(1);
        }
    });
    document.getElementById('clearFiltersBtn').addEventListener('click', function () {
        document.getElementById('searchInput').value = '';
        document.getElementById('filterStatu').value = '';
        document.getElementById('perPageSelectFooter').value = '20';
        sortBy = '';
        sortDir = 'asc';
        updateKutuphaneSortHeader();
        fetchKutuphaneList(1);
    });
    document.getElementById('perPageSelectFooter').addEventListener('change', function () {
        fetchKutuphaneList(1);
    });

    document.querySelectorAll('th.sortable-th').forEach(function (th) {
        th.addEventListener('click', function () {
            var col = this.getAttribute('data-sort');
            if (!col) return;
            if (sortBy === col) {
                sortDir = sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                sortBy = col;
                sortDir = 'asc';
            }
            updateKutuphaneSortHeader();
            fetchKutuphaneList(1);
        });
    });
    updateKutuphaneSortHeader();
    document.getElementById('exportExcelBtn').addEventListener('click', function () {
        var q = document.getElementById('searchInput').value.trim();
        var statu = document.getElementById('filterStatu').value;
        var perPage = document.getElementById('perPageSelectFooter').value || '20';
        var p = new URLSearchParams();
        if (q) p.set('search', q);
        if (statu) p.set('statu', statu);
        if (perPage) p.set('per_page', perPage);
        if (sortBy) {
            p.set('sort_by', sortBy);
            p.set('sort_dir', sortDir || 'asc');
        }
        window.location.href = kutuphaneExportUrl + (p.toString() ? ('?' + p.toString()) : '');
    });

    bindKutuphanePagination();
</script>
@endsection
