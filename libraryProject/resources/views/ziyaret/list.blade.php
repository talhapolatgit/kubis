@extends('layouts.base')

@section('title', 'Ziyaretçi İşlemleri')

@section('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @php($ziyaretCssPath = public_path('css/ziyaret-list.css'))
    <link rel="stylesheet" href="{{ asset('css/ziyaret-list.css') }}?v={{ @filemtime($ziyaretCssPath) ?: time() }}" />
@endsection

@section('breadcrumb')
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('katalog.index') }}" class="breadcrumb-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Ana Sayfa
        </a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Ziyaretçi İşlemleri</span>
    </nav>
@endsection

@section('content')
<div class="toast-container" id="toastContainer"></div>

<div class="ziyaret-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Ziyaretçi İşlemleri</h1>
            <p class="page-subtitle">Kütüphaneye gelen üyelerin giriş ve çıkış kayıtları</p>
        </div>
        <div class="page-header-actions">
            <button type="button" class="btn btn-success" id="exportBtn" title="Mevcut filtreyle Excel (CSV) indir">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                Excel İndir
            </button>
            @if($canManage)
            <button type="button" class="btn btn-primary" id="btnOpenZiyaretModal">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/></svg>
                Ziyaretçi Girişi Kaydet
            </button>
            @endif
        </div>
    </div>

    <div class="stats-row">
        <a href="#" class="stat-card {{ $filtre === 'bugun' ? 'active-filter' : '' }}" data-filtre="bugun">
            <div class="stat-label">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                Bugün
            </div>
            <div class="stat-value blue">{{ $stats['bugun'] }}</div>
            <div class="stat-sub">bugünkü giriş</div>
        </a>
        <a href="#" class="stat-card {{ $filtre === 'icinde' ? 'active-filter' : '' }}" data-filtre="icinde">
            <div class="stat-label" style="color:#1e40af">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                İçeride
            </div>
            <div class="stat-value blue">{{ $stats['icinde'] }}</div>
            <div class="stat-sub">çıkış yapılmamış</div>
        </a>
        <a href="#" class="stat-card {{ $filtre === 'cikisli' ? 'active-filter' : '' }}" data-filtre="cikisli">
            <div class="stat-label" style="color:#166534">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/></svg>
                Çıkışlı
            </div>
            <div class="stat-value green">{{ $stats['cikisli'] }}</div>
            <div class="stat-sub">tamamlanan ziyaret</div>
        </a>
        <a href="#" class="stat-card {{ $filtre === 'hepsi' ? 'active-filter' : '' }}" data-filtre="hepsi">
            <div class="stat-label">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h7v7H3z"/><path d="M14 3h7v7h-7z"/><path d="M14 14h7v7h-7z"/><path d="M3 14h7v7H3z"/></svg>
                Toplam
            </div>
            <div class="stat-value">{{ $stats['toplam'] }}</div>
            <div class="stat-sub">tüm kayıtlar</div>
        </a>
    </div>

    <div class="toolbar">
        <div class="search-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" id="searchInput" class="search-input" placeholder="Üye adı veya TC…" autocomplete="off" />
        </div>
        <select id="filtreSelect" class="filter-select">
            <option value="hepsi">Tüm kayıtlar</option>
            <option value="bugun">Bugünkü girişler</option>
            <option value="icinde">İçeride olanlar</option>
            <option value="cikisli">Çıkış yapılmış</option>
        </select>
        <select id="kutuphaneFilter" class="filter-select">
            <option value="">Tüm kütüphaneler</option>
            @foreach($kutuphaneler as $k)
                <option value="{{ $k->id }}">{{ $k->title }}</option>
            @endforeach
        </select>
    </div>

    <div class="table-card" style="position:relative;">
        <div class="table-veil" id="tableVeil"><div class="veil-spinner"></div></div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Üye</th>
                    <th>Kütüphane</th>
                    <th>Giriş</th>
                    <th>Çıkış</th>
                    <th>Süre</th>
                    <th>Durum</th>
                    <th>Not</th>
                    <th></th>
                </tr>
                </thead>
                <tbody id="tableBody">
                <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--muted-foreground);font-size:13px;">Yükleniyor…</td></tr>
                </tbody>
            </table>
        </div>
        <div class="table-footer">
            <div class="tf-info">
                <span id="rangeInfo">—</span>
                <div class="per-page-wrap">
                    <label for="perPageSelect">Sayfa başına:</label>
                    <select class="per-page-select" id="perPageSelect">
                        <option value="10">10</option>
                        <option value="20" selected>20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
            <div class="pagination" id="pagination"></div>
        </div>
    </div>
</div>

@if($canManage)
<div class="ziyaret-modal-backdrop" id="ziyaretModalBackdrop" role="dialog" aria-modal="true">
    <div class="ziyaret-modal-box">
        <div class="ziyaret-modal-head">
            <div>
                <div class="ziyaret-modal-title">Ziyaretçi girişi</div>
                <p class="ziyaret-modal-sub">Üye, kütüphane ve giriş saatini belirleyerek kayıt oluşturun.</p>
            </div>
            <button type="button" class="ziyaret-modal-close" id="btnCloseZiyaretModal" aria-label="Kapat">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <div class="ziyaret-modal-body">
            <form id="ziyaretForm" class="ziyaret-form-grid">
                <div class="ziyaret-field full">
                    <label class="ziyaret-label" for="uyeSearchZy">Üye <span class="req">*</span></label>
                    <input type="hidden" id="uyeIdZy" name="uye_id" value="" />
                    <div id="uyeSearchFieldZy">
                        <div class="autocomplete-wrap">
                            <input type="text" id="uyeSearchZy" class="ziyaret-input" placeholder="Ad, soyad veya TC ile ara…" autocomplete="off" />
                            <div id="uyeDropdownZy" class="autocomplete-dropdown"></div>
                        </div>
                    </div>
                    <div id="uyeCardZy" class="selected-card" style="display:none;">
                        <div class="selected-card-avatar" id="uyeCardAvZy">?</div>
                        <div class="selected-card-info">
                            <div class="selected-card-name" id="uyeCardNameZy">—</div>
                            <div class="selected-card-meta" id="uyeCardMetaZy">—</div>
                        </div>
                        <button type="button" class="selected-card-clear" onclick="clearUyeZy()" title="Seçimi kaldır">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                </div>
                <div class="ziyaret-field">
                    <label class="ziyaret-label" for="kutuphaneIdZy">Kütüphane <span class="req">*</span></label>
                    <select id="kutuphaneIdZy" name="kutuphane_id" class="ziyaret-select" required>
                        <option value="">Seçin…</option>
                        @foreach($kutuphaneler as $k)
                            <option value="{{ $k->id }}">{{ $k->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ziyaret-field">
                    <label class="ziyaret-label" for="girisSaatiZy">Giriş Saati <span class="req">*</span></label>
                    <input type="datetime-local" id="girisSaatiZy" name="giris_saati" class="ziyaret-input" required />
                </div>
                <div class="ziyaret-field">
                    <label class="ziyaret-label" for="cikisSaatiZy">Çıkış Saati</label>
                    <input type="datetime-local" id="cikisSaatiZy" name="cikis_saati" class="ziyaret-input" />
                </div>
                <div class="ziyaret-field full">
                    <label class="ziyaret-label" for="notlarZy">Not</label>
                    <textarea id="notlarZy" name="notlar" class="ziyaret-textarea" placeholder="İsteğe bağlı not…" rows="2"></textarea>
                </div>
                <div class="ziyaret-form-actions">
                    <button type="button" class="btn btn-outline" id="btnCancelZiyaret">Vazgeç</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveZiyaret">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="ziyaret-modal-backdrop" id="cikisModalBackdrop" role="dialog" aria-modal="true">
    <div class="ziyaret-modal-box sm">
        <div class="ziyaret-modal-head">
            <div>
                <div class="ziyaret-modal-title">Çıkış kaydı</div>
                <p class="ziyaret-modal-sub" id="cikisModalSub">—</p>
            </div>
            <button type="button" class="ziyaret-modal-close" id="btnCloseCikisModal" aria-label="Kapat">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <div class="ziyaret-modal-body">
            <form id="cikisForm">
                <input type="hidden" id="cikisKayitId" value="" />
                <div class="ziyaret-field" style="margin-bottom:14px;">
                    <label class="ziyaret-label" for="cikisSaatiKayit">Çıkış Saati <span class="req">*</span></label>
                    <input type="datetime-local" id="cikisSaatiKayit" class="ziyaret-input" required />
                </div>
                <div class="ziyaret-form-actions" style="margin-top:0;">
                    <button type="button" class="btn btn-outline" id="btnCancelCikis">Vazgeç</button>
                    <button type="submit" class="btn btn-success">Çıkışı Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
(function() {
    var canManage = @json($canManage);
    var updateUrlTpl = @json(route('ziyaret.update', ['ziyaretKaydi' => '__ID__']));

    function esc(s) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(s || ''));
        return d.innerHTML;
    }

    function showToast(type, title, desc) {
        var c = document.getElementById('toastContainer');
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<div>' + esc(title) + '</div>' + (desc ? '<div style="font-size:13px;opacity:.85;margin-top:2px;">' + esc(desc) + '</div>' : '');
        c.appendChild(t);
        setTimeout(function() { if (t.parentNode) t.parentNode.removeChild(t); }, 4000);
    }
    @if(session('success')) showToast('success', @json(session('success'))); @endif

    function pad2(n) { return String(n).padStart(2, '0'); }
    function nowLocalDatetime() {
        var n = new Date();
        return n.getFullYear() + '-' + pad2(n.getMonth() + 1) + '-' + pad2(n.getDate()) + 'T' + pad2(n.getHours()) + ':' + pad2(n.getMinutes());
    }

    function durumBadge(icinde) {
        if (icinde) return '<span class="badge badge-icinde"><span class="badge-dot"></span>İçeride</span>';
        return '<span class="badge badge-cikis"><span class="badge-dot"></span>Çıktı</span>';
    }

    function buildRow(i) {
        var uyeCell = '<div class="member-cell">' +
            '<div class="member-av">' + esc(i.uye_initials) + '</div>' +
            '<div>' +
            (i.profile_url ? '<a href="' + esc(i.profile_url) + '" class="member-name">' + esc(i.uye_ad) + '</a>' : '<div class="member-name">' + esc(i.uye_ad) + '</div>') +
            '<div class="member-tc">' + esc(i.uye_tc) + '</div></div></div>';
        var not = i.notlar ? '<span class="note-preview" title="' + esc(i.notlar) + '">' + esc(i.notlar) + '</span>' : '—';
        var actions = '';
        if (canManage && i.cikis_kaydedilebilir) {
            actions = '<button type="button" class="btn btn-success btn-sm js-cikis-kaydet" data-id="' + esc(String(i.id)) + '" data-uye="' + esc(i.uye_ad) + '">Çıkış Kaydet</button>';
        }
        return '<tr>' +
            '<td>' + uyeCell + '</td>' +
            '<td style="font-size:13px;">' + esc(i.kutuphane) + '</td>' +
            '<td style="font-size:13px;white-space:nowrap;">' + esc(i.giris_saati) + '</td>' +
            '<td style="font-size:13px;white-space:nowrap;">' + (i.cikis_saati ? esc(i.cikis_saati) : '—') + '</td>' +
            '<td style="font-size:13px;">' + esc(i.sure_label) + '</td>' +
            '<td>' + durumBadge(i.icinde_mi) + '</td>' +
            '<td>' + not + '</td>' +
            '<td style="text-align:right;white-space:nowrap;">' + actions + '</td>' +
            '</tr>';
    }

    var state = {
        search: '',
        filtre: @json($filtre),
        kutuphaneId: '',
        per_page: 20,
        page: 1
    };
    var fetchTimer = null;

    document.getElementById('filtreSelect').value = state.filtre;

    function buildPagination(meta) {
        var container = document.getElementById('pagination');
        if (meta.last_page <= 1) { container.innerHTML = ''; return; }
        var cur = meta.current_page, last = meta.last_page, html = '';
        html += '<button class="page-btn ' + (cur <= 1 ? 'disabled' : '') + '" onclick="window.__zyGoPage(' + (cur - 1) + ')">‹</button>';
        for (var p = Math.max(1, cur - 2); p <= Math.min(last, cur + 2); p++) {
            html += '<button class="page-btn' + (p === cur ? ' active' : '') + '" onclick="window.__zyGoPage(' + p + ')">' + p + '</button>';
        }
        html += '<button class="page-btn ' + (cur >= last ? 'disabled' : '') + '" onclick="window.__zyGoPage(' + (cur + 1) + ')">›</button>';
        container.innerHTML = html;
    }
    window.__zyGoPage = function(p) { if (p < 1) return; state.page = p; fetchTable(); };

    function fetchTable(resetPage) {
        if (resetPage) state.page = 1;
        document.getElementById('tableVeil').classList.add('visible');
        var params = new URLSearchParams({
            search: state.search,
            filtre: state.filtre,
            per_page: state.per_page,
            page: state.page
        });
        if (state.kutuphaneId) params.set('kutuphaneId', state.kutuphaneId);

        fetch('{{ route('ziyaret.tableData') }}?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) { if (!res.ok) throw new Error('HTTP ' + res.status); return res.json(); })
        .then(function(result) {
            document.getElementById('tableVeil').classList.remove('visible');
            if (!result.success) return;
            var rows = result.data || [];
            var tbody = document.getElementById('tableBody');
            if (!rows.length) {
                tbody.innerHTML = '<tr><td colspan="8"><div class="empty-state"><p class="empty-title">Kayıt bulunamadı</p><p class="empty-desc">Filtreleri değiştirin veya yeni ziyaretçi girişi ekleyin.</p></div></td></tr>';
            } else {
                tbody.innerHTML = rows.map(buildRow).join('');
            }
            var m = result.meta;
            document.getElementById('rangeInfo').textContent = m.from + '–' + m.to + ' / ' + m.total + ' kayıt';
            buildPagination(m);
        })
        .catch(function() {
            document.getElementById('tableVeil').classList.remove('visible');
            showToast('error', 'Hata', 'Veriler yüklenemedi.');
        });
    }

    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(fetchTimer);
        var val = this.value.trim();
        fetchTimer = setTimeout(function() { state.search = val; fetchTable(true); }, 350);
    });
    document.getElementById('filtreSelect').addEventListener('change', function() {
        state.filtre = this.value;
        document.querySelectorAll('.stat-card[data-filtre]').forEach(function(c) {
            c.classList.toggle('active-filter', c.getAttribute('data-filtre') === state.filtre);
        });
        fetchTable(true);
    });
    document.getElementById('kutuphaneFilter').addEventListener('change', function() {
        state.kutuphaneId = this.value;
        fetchTable(true);
    });
    document.getElementById('perPageSelect').addEventListener('change', function() {
        state.per_page = parseInt(this.value, 10);
        fetchTable(true);
    });
    document.querySelectorAll('.stat-card[data-filtre]').forEach(function(card) {
        card.addEventListener('click', function(e) {
            e.preventDefault();
            state.filtre = this.getAttribute('data-filtre');
            document.getElementById('filtreSelect').value = state.filtre;
            document.querySelectorAll('.stat-card[data-filtre]').forEach(function(c) { c.classList.remove('active-filter'); });
            this.classList.add('active-filter');
            fetchTable(true);
        });
    });

    document.getElementById('tableBody').addEventListener('click', function(e) {
        var btn = e.target.closest('.js-cikis-kaydet');
        if (!btn || !canManage) return;
        document.getElementById('cikisKayitId').value = btn.getAttribute('data-id');
        document.getElementById('cikisModalSub').textContent = btn.getAttribute('data-uye') || '';
        document.getElementById('cikisSaatiKayit').value = nowLocalDatetime();
        document.getElementById('cikisModalBackdrop').classList.add('visible');
    });

    fetchTable();

    document.getElementById('exportBtn').addEventListener('click', function() {
        var params = new URLSearchParams({
            search: state.search,
            filtre: state.filtre,
            kutuphaneId: state.kutuphaneId
        });
        var a = document.createElement('a');
        a.href = '{{ route('ziyaret.export') }}?' + params.toString();
        a.download = '';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    });

    @if($canManage)
    var acTimer = null;
    function setupUyeAutocomplete() {
        var inp = document.getElementById('uyeSearchZy');
        var dd = document.getElementById('uyeDropdownZy');
        var hi = -1;
        inp.addEventListener('input', function() {
            var q = this.value.trim();
            clearTimeout(acTimer);
            if (q.length < 2) { dd.classList.remove('open'); dd.innerHTML = ''; return; }
            acTimer = setTimeout(function() {
                dd.innerHTML = '<div class="ac-loading">Aranıyor…</div>';
                dd.classList.add('open');
                fetch('{{ route('odunc.uyeAra') }}?q=' + encodeURIComponent(q), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(r) { return r.json(); })
                .then(function(items) {
                    dd.innerHTML = '';
                    hi = -1;
                    if (!items.length) { dd.innerHTML = '<div class="ac-empty">Sonuç yok</div>'; return; }
                    items.forEach(function(item) {
                        var el = document.createElement('div');
                        el.className = 'ac-item';
                        var initials = item.label.split(' ').map(function(w) { return w[0]; }).join('').slice(0, 2).toUpperCase();
                        el.innerHTML = '<div class="ac-item-avatar">' + initials + '</div><div class="ac-item-body"><div class="ac-item-name">' + esc(item.label) + '</div><div class="ac-item-meta">TC: ' + esc(item.tc) + '</div></div>';
                        el.addEventListener('mousedown', function(ev) {
                            ev.preventDefault();
                            selectUyeZy(item);
                            dd.classList.remove('open');
                            inp.value = '';
                        });
                        dd.appendChild(el);
                    });
                });
            }, 280);
        });
        document.addEventListener('click', function(e) {
            if (!inp.contains(e.target) && !dd.contains(e.target)) dd.classList.remove('open');
        });
    }

    function selectUyeZy(item) {
        document.getElementById('uyeIdZy').value = item.id;
        var initials = item.label.split(' ').map(function(w) { return w[0]; }).join('').slice(0, 2).toUpperCase();
        document.getElementById('uyeCardAvZy').textContent = initials;
        document.getElementById('uyeCardNameZy').textContent = item.label;
        document.getElementById('uyeCardMetaZy').textContent = 'TC: ' + item.tc;
        document.getElementById('uyeSearchFieldZy').style.display = 'none';
        document.getElementById('uyeCardZy').style.display = 'flex';
    }
    window.clearUyeZy = function() {
        document.getElementById('uyeIdZy').value = '';
        document.getElementById('uyeSearchFieldZy').style.display = 'block';
        document.getElementById('uyeCardZy').style.display = 'none';
        document.getElementById('uyeSearchZy').value = '';
    };

    function openZiyaretModal() {
        document.getElementById('ziyaretForm').reset();
        clearUyeZy();
        document.getElementById('girisSaatiZy').value = nowLocalDatetime();
        document.getElementById('cikisSaatiZy').value = '';
        document.getElementById('ziyaretModalBackdrop').classList.add('visible');
    }
    function closeZiyaretModal() {
        document.getElementById('ziyaretModalBackdrop').classList.remove('visible');
    }
    function closeCikisModal() {
        document.getElementById('cikisModalBackdrop').classList.remove('visible');
    }

    document.getElementById('btnOpenZiyaretModal').addEventListener('click', openZiyaretModal);
    document.getElementById('btnCloseZiyaretModal').addEventListener('click', closeZiyaretModal);
    document.getElementById('btnCancelZiyaret').addEventListener('click', closeZiyaretModal);
    document.getElementById('btnCloseCikisModal').addEventListener('click', closeCikisModal);
    document.getElementById('btnCancelCikis').addEventListener('click', closeCikisModal);
    document.getElementById('ziyaretModalBackdrop').addEventListener('click', function(e) { if (e.target === this) closeZiyaretModal(); });
    document.getElementById('cikisModalBackdrop').addEventListener('click', function(e) { if (e.target === this) closeCikisModal(); });

    document.getElementById('ziyaretForm').addEventListener('submit', function(e) {
        e.preventDefault();
        if (!document.getElementById('uyeIdZy').value) {
            showToast('error', 'Eksik bilgi', 'Lütfen üye seçin.');
            return;
        }
        var btn = document.getElementById('btnSaveZiyaret');
        btn.disabled = true;
        var fd = new FormData(this);
        fetch('{{ route('ziyaret.store') }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: fd
        })
        .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, body: j }; }); })
        .then(function(res) {
            btn.disabled = false;
            if (res.ok && res.body.success) {
                showToast('success', 'Kaydedildi', res.body.message);
                closeZiyaretModal();
                fetchTable(true);
                return;
            }
            showToast('error', 'Hata', (res.body && res.body.message) || 'Kayıt oluşturulamadı.');
        })
        .catch(function() {
            btn.disabled = false;
            showToast('error', 'Hata', 'Sunucuya ulaşılamadı.');
        });
    });

    document.getElementById('cikisForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var id = document.getElementById('cikisKayitId').value;
        var url = updateUrlTpl.replace('__ID__', encodeURIComponent(id));
        fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                _method: 'PUT',
                cikis_saati: document.getElementById('cikisSaatiKayit').value
            })
        })
        .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, body: j }; }); })
        .then(function(res) {
            if (res.ok && res.body.success) {
                showToast('success', 'Çıkış kaydedildi', res.body.message);
                closeCikisModal();
                fetchTable();
                return;
            }
            showToast('error', 'Hata', (res.body && res.body.message) || 'Çıkış kaydedilemedi.');
        })
        .catch(function() { showToast('error', 'Hata', 'Sunucuya ulaşılamadı.'); });
    });

    setupUyeAutocomplete();
    @endif
})();
</script>
@endsection
