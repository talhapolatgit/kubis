@extends('layouts.base')

@section('title', 'Uyeler')

@section('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="{{ asset('css/uye-list.css') }}?v={{ @filemtime(public_path('css/uye-list.css')) ?: time() }}">
    @php($uyeListCss = @file_get_contents(public_path('css/uye-list.css')))
    @if($uyeListCss)
        <style>{!! $uyeListCss !!}</style>
    @endif
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
                    <button class="btn btn-success" id="exportBtn" title="Mevcut filtreyle Excel (CSV) indir">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                        Excel İndir
                    </button>
                    @if(auth()->user()->hasYetki(12))
                    <a href="{{ route('uyeler.new') }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>
                        Yeni Üye Ekle
                    </a>
                    @endif
                    
                </div>
            </div>

            <!-- Filtreler -->
            <div class="toolbar">
                <div class="filter-wrap">
                    <svg class="fi" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" class="filter-input" id="searchInput" placeholder="Ad, soyad, TC kimlik, telefon, e-posta ara…" autocomplete="off" />
                </div>
                <select class="filter-select" id="statuFilter">
                    <option value="">Tüm Durumlar</option>
                    <option value="aktif">Aktif</option>
                    <option value="pasif">Pasif</option>
                </select>
                <button class="btn btn-outline btn-sm" id="clearFiltersBtn" style="display:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    Temizle
                </button>
            </div>

            <!-- Tablo -->
            <div class="table-card" id="tableCard">
                <div class="table-veil" id="tableVeil">
                    <div class="veil-spinner"></div>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Üye</th>
                            <th style="width:130px;">TC Kimlik No</th>
                            <th style="width:140px;">Telefon</th>
                            <th style="width:130px;">İl / İlçe</th>
                            <th style="width:90px;">Durum</th>
                            <th style="width:110px;">Üyelik Tarihi</th>
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
    var state      = { search: '', statu: '', per_page: 10, page: 1 };
    var fetchTimer = null;
    var reqCounter = 0;   // yalnızca en son isteğin yanıtı işlenir

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
        return '<tr>' +
            '<td>' +
            '<div class="member-cell">' +
            '<div class="member-avatar">' + esc(u.initials || '?') + '</div>' +
            '<div>' +
            '<div class="member-name">' + esc(u.ad_soyad) + '</div>' +
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
            '<button class="row-actions-btn" onclick="toggleUyeMenu(' + u.id + ', \'' + u.edit_url + '\', \'' + u.delete_url + '\', \'' + safeAd + '\', event)" title="İşlemler">' +
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
            search:   state.search,
            statu:    state.statu,
            per_page: state.per_page,
            page:     state.page,
        });

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
                document.getElementById('pageSubtitle').textContent = m.total + ' üye kayıtlı';
                document.getElementById('rangeInfo').textContent    = m.from + '–' + m.to + ' / ' + m.total + ' kayıt';
                buildPagination(m);
                updateClearBtn();
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
        var has = state.search !== '' || state.statu !== '';
        document.getElementById('clearFiltersBtn').style.display = has ? '' : 'none';
        document.getElementById('searchInput').classList.toggle('filter-active', state.search !== '');
        document.getElementById('statuFilter').classList.toggle('filter-active', state.statu !== '');
    }

    document.getElementById('searchInput').addEventListener('input', function() {
        state.search = this.value.trim();
        clearTimeout(fetchTimer);
        fetchTimer = setTimeout(function() { fetchTable(true); }, 380);
    });

    document.getElementById('statuFilter').addEventListener('change', function() {
        clearTimeout(fetchTimer);
        state.statu = this.value;
        fetchTable(true);
    });

    document.getElementById('perPageSelect').addEventListener('change', function() {
        clearTimeout(fetchTimer);
        state.per_page = parseInt(this.value);
        fetchTable(true);
    });

    document.getElementById('clearFiltersBtn').addEventListener('click', function() {
        clearTimeout(fetchTimer);
        state.search = ''; state.statu = '';
        document.getElementById('searchInput').value = '';
        document.getElementById('statuFilter').value = '';
        fetchTable(true);
    });

    // ══════════════════════════════════════════════════════════════════════════════
    // Excel Export
    // ══════════════════════════════════════════════════════════════════════════════
    document.getElementById('exportBtn').addEventListener('click', function() {
        var params = new URLSearchParams({ search: state.search, statu: state.statu });
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

    function toggleUyeMenu(id, editUrl, deleteUrl, adSoyad, event) {
        event.stopPropagation();
        var btn = event.currentTarget;

        // Aynı butona tekrar basılırsa kapat
        if (openUyeMenuBtn === btn) { closeUyeMenu(); return; }

        // Menü bağlantılarını güncelle
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
    fetchTable();
</script>
@endsection
