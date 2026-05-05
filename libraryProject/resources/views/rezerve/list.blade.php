@extends('layouts.base')

@section('title', 'Rezervasyon Islemleri')

@section('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @php($rezerveListCssPath = public_path('css/rezerve-list.css'))
    <link rel="stylesheet" href="{{ asset('css/rezerve-list.css') }}?v={{ @filemtime($rezerveListCssPath) ?: time() }}" />
    @php($rezerveListCss = @file_get_contents($rezerveListCssPath))
    @if($rezerveListCss)
        <style>{!! $rezerveListCss !!}</style>
    @endif
@endsection

@section('breadcrumb')
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('katalog.index') }}" class="breadcrumb-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Ana Sayfa
        </a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Rezervasyon İşlemleri</span>
    </nav>
@endsection

@section('content')
        <div class="rezerve-content">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Rezervasyon İşlemleri</h1>
                    <p class="page-subtitle">Rezervasyon oluşturma ve ödünç vermeye yönlendirme</p>
                </div>
                @if($canManage)
                <div class="page-header-actions" style="display:flex;align-items:flex-start;flex-shrink:0;">
                    <button type="button" class="btn btn-primary" id="btnOpenRezerveModal">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4"/><path d="M12 18v4"/><path d="m4.93 4.93 2.83 2.83"/><path d="m16.24 16.24 2.83 2.83"/><path d="M2 12h4"/><path d="M18 12h4"/><path d="m4.93 19.07 2.83-2.83"/><path d="m16.24 7.76 2.83-2.83"/></svg>
                        Yeni Rezervasyon Oluştur
                    </button>
                </div>
                @endif
            </div>

            <div class="stats-row">
                <a href="#" class="stat-card {{ $filtre === 'aktif' ? 'active-filter' : '' }}" data-filtre="aktif">
                    <div class="stat-label">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Aktif
                    </div>
                    <div class="stat-value">{{ $stats['aktif'] }}</div>
                    <div class="stat-sub">geçerli rezervasyon</div>
                </a>
                <a href="#" class="stat-card {{ $filtre === 'tamamlanan' ? 'active-filter' : '' }}" data-filtre="tamamlanan">
                    <div class="stat-label" style="color:#166534">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                        Ödünç verildi
                    </div>
                    <div class="stat-value green">{{ $stats['tamamlanan'] }}</div>
                    <div class="stat-sub">tamamlanan</div>
                </a>
                <a href="#" class="stat-card {{ $filtre === 'suresi_doldu' ? 'active-filter' : '' }}" data-filtre="suresi_doldu">
                    <div class="stat-label" style="color:var(--destructive)">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Süresi doldu
                    </div>
                    <div class="stat-value red">{{ $stats['suresi_doldu'] }}</div>
                    <div class="stat-sub">iptal edilmemiş</div>
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
                    <input type="text" id="searchInput" class="search-input" placeholder="Üye adı, TC, kitap adı, ISBN…" autocomplete="off" />
                </div>
                <select id="filtreSelect" class="filter-select">
                    <option value="aktif">Aktif rezervasyonlar</option>
                    <option value="tamamlanan">Ödünç verilmiş</option>
                    <option value="iptal">İptal</option>
                    <option value="suresi_doldu">Süresi dolmuş</option>
                    <option value="hepsi">Tümü</option>
                </select>
            </div>

            <div class="table-card" style="position:relative;">
                <div class="table-veil" id="tableVeil"><div class="veil-spinner"></div></div>
                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Üye</th>
                            <th>Kitap</th>
                            <th>Başlangıç</th>
                            <th>Bitiş</th>
                            <th>Durum</th>
                            <th>Kütüphane</th>
                            <th></th>
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
<div class="rezerve-modal-backdrop" id="rezerveModalBackdrop" role="dialog" aria-modal="true" aria-labelledby="rezerveModalTitle">
    <div class="rezerve-modal-box">
        <div class="rezerve-modal-head">
            <div>
                <div id="rezerveModalTitle" class="rezerve-modal-title">Rezervasyon oluşturma</div>
                <p class="rezerve-modal-sub">Üye ve raftaki kitabı seçerek rezervasyon kaydı oluşturun (süre: 24 saat).</p>
            </div>
            <button type="button" class="rezerve-modal-close" id="btnCloseRezerveModal" aria-label="Pencereyi kapat">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <div class="rezerve-modal-body">
            <div class="form-grid-rez">
                <div class="form-field-rz">
                    <label class="form-label-rz" for="uyeSearchRez">Üye <span class="req">*</span></label>
                    <input type="hidden" id="uyeIdRez" value="" />
                    <div id="uyeSearchFieldRez">
                        <div class="autocomplete-wrap">
                            <input type="text" id="uyeSearchRez" class="form-input-rz" placeholder="Ad, soyad veya TC ile ara…" autocomplete="off" />
                            <div id="uyeDropdownRez" class="autocomplete-dropdown"></div>
                        </div>
                    </div>
                    <div id="uyeCardRez" class="selected-card" style="display:none;">
                        <div class="selected-card-avatar" id="uyeCardAvRez"></div>
                        <div class="selected-card-info">
                            <div class="selected-card-name" id="uyeCardNameRez"></div>
                            <div class="selected-card-meta" id="uyeCardMetaRez"></div>
                        </div>
                        <button type="button" class="selected-card-clear" onclick="clearUyeRez()" title="Kaldır">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                    <div id="uyeNotUyariRez" class="warning-box--block" style="display:none;margin-top:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4"/><path d="M12 16h.01"/><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
                        <div>
                            <strong>Üye Notu</strong>
                            <span id="uyeNotMetinRez">—</span>
                        </div>
                    </div>
                </div>
                <div class="form-field-rz">
                    <label class="form-label-rz" for="kitapSearchRez">Kitap <span class="req">*</span></label>
                    <input type="hidden" id="katalogIdRez" value="" />
                    <div id="kitapSearchFieldRez">
                        <div class="autocomplete-wrap">
                            <input type="text" id="kitapSearchRez" class="form-input-rz" placeholder="Eser adı, ISBN veya demirbaş…" autocomplete="off" />
                            <div id="kitapDropdownRez" class="autocomplete-dropdown"></div>
                        </div>
                    </div>
                    <div id="kitapCardRez" class="selected-card" style="display:none;">
                        <div id="kitapCardCoverWrapRez"></div>
                        <div class="selected-card-info">
                            <div class="selected-card-name" id="kitapCardNameRez"></div>
                            <div class="selected-card-meta" id="kitapCardMetaRez"></div>
                        </div>
                        <button type="button" class="selected-card-clear" onclick="clearKitapRez()" title="Kaldır">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                </div>
                <div class="form-actions-rez">
                    <button type="button" class="btn btn-outline" id="btnRezerveModalIptal">Vazgeç</button>
                    <button type="button" class="btn btn-primary" id="btnRezerveOlustur">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4"/><path d="M12 18v4"/><circle cx="12" cy="12" r="3"/></svg>
                        Rezervasyon oluştur
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="toast-container" id="toastContainer"></div>
@endsection

@section('scripts')
<script>
(function() {
    function showToast(type, title, desc) {
        var c = document.getElementById('toastContainer');
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<div>' + title + '</div>' + (desc ? '<div class="toast-desc">' + desc + '</div>' : '');
        c.appendChild(t);
        setTimeout(function() { t.style.animation = 'toast-out .3s ease forwards'; setTimeout(function() { if(t.parentNode) t.parentNode.removeChild(t); }, 300); }, 4500);
    }
    @if(session('success')) showToast('success', @json(session('success'))); @endif
    @if(session('error')) showToast('error', @json(session('error'))); @endif

    function esc(s) { var d = document.createElement('div'); d.appendChild(document.createTextNode(s||'')); return d.innerHTML; }

    var bookIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>';
    var cancelUrlTpl = @json(route('rezerve.cancel', ['rezerve' => '__ID__']));

    function durumBadge(lbl) {
        if (lbl === 'Aktif') return '<span class="badge badge-aktif"><span class="badge-dot"></span> Aktif</span>';
        if (lbl === 'Ödünç verildi') return '<span class="badge badge-iade"><span class="badge-dot"></span> Ödünç verildi</span>';
        if (lbl === 'İptal') return '<span class="badge badge-muted"><span class="badge-dot"></span> İptal</span>';
        if (lbl === 'Süresi doldu') return '<span class="badge badge-warn"><span class="badge-dot"></span> Süresi doldu</span>';
        return '<span class="badge badge-muted"><span class="badge-dot"></span> ' + esc(lbl) + '</span>';
    }

    function buildRow(i) {
        var uyeCell = '<div class="member-cell"><div class="member-av">' + esc(i.uye_initials) + '</div><div>' +
            '<div class="member-name">' + esc(i.uye_ad) + '</div>' +
            '<div class="member-tc">' + esc(i.uye_tc) + '</div></div></div>';
        var coverHtml = i.kitap_kapak
            ? '<img src="' + esc(i.kitap_kapak) + '" alt="" class="book-cover" />'
            : '<div class="book-cover-placeholder">' + bookIcon + '</div>';
        var bookCell = '<div class="book-cell">' + coverHtml + '<div>' +
            '<div class="book-title" title="' + esc(i.kitap) + '">' + esc(i.kitap) + '</div>' +
            '<div class="book-isbn">Demirbaş: ' + esc(i.kitap_demir || '—') + ' · ISBN: ' + esc(i.kitap_isbn || '—') + '</div></div></div>';
        var bas = '<div class="date-cell"><div class="date-main">' + esc(i.rezerve_baslangic) + '</div></div>';
        var bit = '<div class="date-cell"><div class="date-main">' + esc(i.rezerve_bitis) + '</div></div>';
        var actions = [];
        if (i.odunc_yapilabilir && i.odunc_new_url) {
            actions.push('<a href="' + String(i.odunc_new_url).replace(/"/g, '&quot;') + '" class="btn btn-primary btn-sm">Ödünç</a>');
        }
        if (i.iptal_edilebilir) {
            actions.push('<button type="button" class="btn btn-outline btn-sm js-rez-cancel" data-id="' + esc(String(i.id)) + '">İptal</button>');
        }
        var act = actions.length
            ? actions.join(' ')
            : '<span style="font-size:12px;color:var(--muted-foreground);">—</span>';
        return '<tr><td>' + uyeCell + '</td><td>' + bookCell + '</td><td>' + bas + '</td><td>' + bit + '</td><td>' + durumBadge(i.durum_etiket) + '</td><td style="font-size:12px;color:var(--muted-foreground);max-width:120px;">' + esc(i.kutuphane || '—') + '</td><td style="text-align:right;white-space:nowrap;"><div style="display:inline-flex;gap:6px;justify-content:flex-end;">' + act + '</div></td></tr>';
    }

    function handleCancelReservation(rezerveId, btn) {
        if (!rezerveId) return;
        if (!window.confirm('Bu rezervasyonu iptal etmek istiyor musunuz?')) return;

        if (btn) btn.disabled = true;

        var cancelUrl = cancelUrlTpl.replace('__ID__', encodeURIComponent(String(rezerveId)));
        fetch(cancelUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, body: j }; }); })
            .then(function(res) {
                if (btn) btn.disabled = false;
                if (res.ok && res.body && res.body.success) {
                    showToast('success', 'Rezervasyon', res.body.message || 'Rezervasyon iptal edildi.');
                    fetchTable(true);
                    return;
                }
                var msg = (res.body && res.body.message) ? res.body.message : 'Rezervasyon iptal edilemedi.';
                showToast('error', 'Rezervasyon', msg);
            })
            .catch(function() {
                if (btn) btn.disabled = false;
                showToast('error', 'Hata', 'Sunucuya ulaşılamadı.');
            });
        }
    
    document.getElementById('tableBody').addEventListener('click', function(e) {
        var btn = e.target.closest('.js-rez-cancel');
        if (!btn) return;
        handleCancelReservation(btn.getAttribute('data-id'), btn);
    });

    var state = {
        search: '',
        filtre: @json($filtre),
        per_page: 20,
        page: 1
    };
    var fetchTimer = null;
    var activeXhr = null;

    document.getElementById('filtreSelect').value = state.filtre;

    function buildPagination(meta) {
        var container = document.getElementById('pagination');
        if (meta.last_page <= 1) { container.innerHTML = ''; return; }
        var cur = meta.current_page, last = meta.last_page;
        var html = '';
        html += '<button class="page-btn ' + (cur<=1?'disabled':'') + '" onclick="window.__rezGoPage(' + (cur-1) + ')"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="m15 18-6-6 6-6"/></svg></button>';
        var pages = [];
        if (last <= 7) { for (var i=1;i<=last;i++) pages.push(i); }
        else {
            pages.push(1);
            if (cur>3) pages.push('…');
            for (var j=Math.max(2,cur-1);j<=Math.min(last-1,cur+1);j++) pages.push(j);
            if (cur<last-2) pages.push('…');
            pages.push(last);
        }
        pages.forEach(function(p) {
            if (p==='…') html += '<span class="page-ellipsis">…</span>';
            else html += '<button class="page-btn' + (p===cur?' active':'') + '" onclick="window.__rezGoPage(' + p + ')">' + p + '</button>';
        });
        html += '<button class="page-btn ' + (cur>=last?'disabled':'') + '" onclick="window.__rezGoPage(' + (cur+1) + ')"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="m9 18 6-6-6-6"/></svg></button>';
        container.innerHTML = html;
    }
    window.__rezGoPage = function(p) { if (p<1) return; state.page = p; fetchTable(); };

    function fetchTable(resetPage) {
        if (resetPage) state.page = 1;
        if (activeXhr) activeXhr.abort();
        activeXhr = new AbortController();
        var ctrl = activeXhr;
        document.getElementById('tableVeil').classList.add('visible');
        var params = new URLSearchParams({
            search: state.search,
            filtre: state.filtre,
            per_page: state.per_page,
            page: state.page
        });
        fetch('{{ route('rezerve.tableData') }}?' + params.toString(), {
            signal: ctrl.signal,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function(res) { if (!res.ok) throw new Error('HTTP ' + res.status); return res.json(); })
            .then(function(result) {
                if (ctrl.signal.aborted) return;
                activeXhr = null;
                document.getElementById('tableVeil').classList.remove('visible');
                if (!result.success) { showToast('error', 'Hata', 'Veriler yüklenemedi.'); return; }
                var rows = Array.isArray(result.data) ? result.data : [];
                var tbody = document.getElementById('tableBody');
                if (rows.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><div class="empty-icon">' + bookIcon + '</div><p class="empty-title">Kayıt yok</p><p class="empty-desc">Arama veya filtreyi değiştirin.</p></div></td></tr>';
                } else {
                    tbody.innerHTML = rows.map(buildRow).join('');
                }
                var m = result.meta;
                document.getElementById('rangeInfo').textContent = m.from + '–' + m.to + ' / ' + m.total + ' kayıt';
                buildPagination(m);
            })
            .catch(function(err) {
                if (err && err.name === 'AbortError') return;
                document.getElementById('tableVeil').classList.remove('visible');
                showToast('error', 'Bağlantı hatası', 'Tablo yüklenemedi.');
            });
    }

    function debounce(fn) {
        clearTimeout(fetchTimer);
        fetchTimer = setTimeout(fn, 400);
    }

    document.getElementById('searchInput').addEventListener('input', function() {
        state.search = this.value.trim();
        debounce(function() { fetchTable(true); });
    });
    document.getElementById('filtreSelect').addEventListener('change', function() {
        clearTimeout(fetchTimer);
        state.filtre = this.value;
        document.querySelectorAll('.stat-card[data-filtre]').forEach(function(c) {
            c.classList.toggle('active-filter', c.getAttribute('data-filtre') === state.filtre);
        });
        fetchTable(true);
    });
    document.getElementById('perPageSelect').addEventListener('change', function() {
        state.per_page = parseInt(this.value, 10);
        fetchTable(true);
    });

    document.querySelectorAll('.stat-card[data-filtre]').forEach(function(card) {
        card.addEventListener('click', function(e) {
            e.preventDefault();
            var f = this.getAttribute('data-filtre');
            state.filtre = f;
            document.getElementById('filtreSelect').value = f;
            document.querySelectorAll('.stat-card[data-filtre]').forEach(function(c) { c.classList.remove('active-filter'); });
            this.classList.add('active-filter');
            fetchTable(true);
        });
    });

    fetchTable();

    @if($canManage)
    var acTimers = {};
    function setupAutocomplete(inputId, dropdownId, fetchUrl, onSelect, renderItem, extraQueryFn) {
        var inp = document.getElementById(inputId);
        var dd = document.getElementById(dropdownId);
        var hi = -1;
        inp.addEventListener('input', function() {
            var q = this.value.trim();
            clearTimeout(acTimers[inputId]);
            if (q.length < 2) { dd.classList.remove('open'); dd.innerHTML = ''; return; }
            acTimers[inputId] = setTimeout(function() {
                dd.innerHTML = '<div class="ac-loading">Aranıyor…</div>';
                dd.classList.add('open');
                var extra = (typeof extraQueryFn === 'function') ? extraQueryFn() : '';
                fetch(fetchUrl + '?q=' + encodeURIComponent(q) + extra, { headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } })
                    .then(function(r) { return r.json(); })
                    .then(function(items) {
                        dd.innerHTML = '';
                        hi = -1;
                        if (!items.length) { dd.innerHTML = '<div class="ac-empty">Sonuç yok</div>'; return; }
                        items.forEach(function(item) {
                            var el = document.createElement('div');
                            el.className = 'ac-item';
                            el.innerHTML = renderItem(item);
                            el.addEventListener('mousedown', function(e) { e.preventDefault(); onSelect(item); dd.classList.remove('open'); inp.value = ''; });
                            dd.appendChild(el);
                        });
                    });
            }, 280);
        });
        inp.addEventListener('keydown', function(e) {
            var items = dd.querySelectorAll('.ac-item');
            if (e.key === 'ArrowDown') { e.preventDefault(); if (hi < items.length - 1) { hi++; items.forEach(function(el, i) { el.classList.toggle('highlighted', i === hi); }); } }
            else if (e.key === 'ArrowUp') { e.preventDefault(); if (hi > 0) { hi--; items.forEach(function(el, i) { el.classList.toggle('highlighted', i === hi); }); } }
            else if (e.key === 'Escape') {
                if (dd.classList.contains('open')) {
                    dd.classList.remove('open');
                    e.stopPropagation();
                    e.preventDefault();
                }
            }
        });
        document.addEventListener('click', function(e) {
            if (!inp.contains(e.target) && !dd.contains(e.target)) dd.classList.remove('open');
        });
    }

    var selectedUyeRez = null;
    var selectedKitapRez = null;

    setupAutocomplete('uyeSearchRez', 'uyeDropdownRez', '{{ route('odunc.uyeAra') }}',
        function(item) { selectUyeRez(item); },
        function(item) {
            var badge = item.aktif_odunc > 0 ? '<span class="ac-item-badge warning">' + item.aktif_odunc + ' aktif ödünç</span>' : '';
            if (item.notlar && String(item.notlar).trim().length > 0) {
                badge += '<span class="ac-item-badge danger">Not var</span>';
            }
            var initials = item.label.split(' ').map(function(w) { return w[0]; }).join('').slice(0,2).toUpperCase();
            return '<div class="ac-item-avatar">' + initials + '</div><div class="ac-item-body"><div class="ac-item-name">' + esc(item.label) + badge + '</div><div class="ac-item-meta">TC: ' + esc(item.tc) + ' · ' + esc(item.telefon) + '</div></div>';
        }
    );

    function selectUyeRez(item) {
        selectedUyeRez = item;
        document.getElementById('uyeIdRez').value = item.id;
        var initials = item.label.split(' ').map(function(w) { return w[0]; }).join('').slice(0,2).toUpperCase();
        document.getElementById('uyeCardAvRez').textContent = initials;
        document.getElementById('uyeCardNameRez').textContent = item.label;
        document.getElementById('uyeCardMetaRez').textContent = 'TC: ' + item.tc + ' · ' + item.telefon;
        document.getElementById('uyeSearchFieldRez').style.display = 'none';
        document.getElementById('uyeCardRez').style.display = 'flex';
        var uyeNot = String(item.notlar || '').trim();
        var uyeNotUyari = document.getElementById('uyeNotUyariRez');
        var uyeNotMetin = document.getElementById('uyeNotMetinRez');
        if (uyeNot) {
            uyeNotMetin.textContent = uyeNot;
            uyeNotUyari.style.display = 'flex';
        } else {
            uyeNotMetin.textContent = '';
            uyeNotUyari.style.display = 'none';
        }
    }
    window.clearUyeRez = function() {
        selectedUyeRez = null;
        document.getElementById('uyeIdRez').value = '';
        document.getElementById('uyeSearchFieldRez').style.display = 'block';
        document.getElementById('uyeCardRez').style.display = 'none';
        document.getElementById('uyeNotUyariRez').style.display = 'none';
        document.getElementById('uyeNotMetinRez').textContent = '';
        document.getElementById('uyeSearchRez').value = '';
    };

    setupAutocomplete('kitapSearchRez', 'kitapDropdownRez', '{{ route('odunc.kitapAra') }}',
        function(item) { selectKitapRez(item); },
        function(item) {
            var coverHtml = item.kapak ? '<img src="' + esc(item.kapak) + '" class="ac-item-cover" />' : '<div class="ac-item-cover-ph">' + bookIcon.replace('width="24"','width="13"') + '</div>';
            var badges = '';
            if (item.odunc_ta) badges += '<span class="ac-item-badge danger">Ödünçte</span>';
            else if (item.oduncVerilemez === 'true') badges += '<span class="ac-item-badge danger">Ödünç verilemez</span>';
            else if (item.kunyeDurum === 'Rezerve' && item.rezerve_aktif_bu_uye) badges += '<span class="ac-item-badge warning">Rezerve · bu üye</span>';
            else if (item.kunyeDurum && item.kunyeDurum !== 'Rafta') badges += '<span class="ac-item-badge danger">' + esc(item.kunyeDurum) + '</span>';
            return coverHtml + '<div class="ac-item-body"><div class="ac-item-name">' + esc(item.label) + badges + '</div><div class="ac-item-meta">' + esc(item.yazar || '') + (item.demir ? ' · Demirbaş: ' + esc(item.demir) : '') + '</div></div>';
        },
        function() {
            var uid = document.getElementById('uyeIdRez').value;
            return uid ? '&uye_id=' + encodeURIComponent(uid) : '';
        }
    );

    function selectKitapRez(item) {
        selectedKitapRez = item;
        document.getElementById('katalogIdRez').value = item.id;
        var wrap = document.getElementById('kitapCardCoverWrapRez');
        if (item.kapak) wrap.innerHTML = '<img src="' + esc(item.kapak) + '" class="selected-card-cover" />';
        else wrap.innerHTML = '';
        document.getElementById('kitapCardNameRez').textContent = item.label;
        document.getElementById('kitapCardMetaRez').textContent = (item.yazar || '') + (item.demir ? ' · Demirbaş: ' + item.demir : '') + (item.isbn ? ' · ISBN: ' + item.isbn : '');
        document.getElementById('kitapSearchFieldRez').style.display = 'none';
        document.getElementById('kitapCardRez').style.display = 'flex';
    }
    window.clearKitapRez = function() {
        selectedKitapRez = null;
        document.getElementById('katalogIdRez').value = '';
        document.getElementById('kitapSearchFieldRez').style.display = 'block';
        document.getElementById('kitapCardRez').style.display = 'none';
        document.getElementById('kitapSearchRez').value = '';
        document.getElementById('kitapCardCoverWrapRez').innerHTML = '';
    };

    function lockBodyScroll() {
        var gap = window.innerWidth - document.documentElement.clientWidth;
        if (gap > 0) document.body.style.paddingRight = gap + 'px';
        document.body.style.overflow = 'hidden';
    }
    function unlockBodyScroll() {
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }

    function openRezerveModal() {
        clearUyeRez();
        clearKitapRez();
        document.getElementById('rezerveModalBackdrop').classList.add('visible');
        lockBodyScroll();
        setTimeout(function() {
            var el = document.getElementById('uyeSearchRez');
            if (el) el.focus();
        }, 80);
    }
    function closeRezerveModal() {
        document.getElementById('rezerveModalBackdrop').classList.remove('visible');
        unlockBodyScroll();
        var uyeDd = document.getElementById('uyeDropdownRez');
        var kitapDd = document.getElementById('kitapDropdownRez');
        if (uyeDd) { uyeDd.classList.remove('open'); uyeDd.innerHTML = ''; }
        if (kitapDd) { kitapDd.classList.remove('open'); kitapDd.innerHTML = ''; }
    }
    document.getElementById('btnOpenRezerveModal').addEventListener('click', openRezerveModal);
    document.getElementById('btnCloseRezerveModal').addEventListener('click', closeRezerveModal);
    document.getElementById('btnRezerveModalIptal').addEventListener('click', closeRezerveModal);
    document.getElementById('rezerveModalBackdrop').addEventListener('click', function(e) {
        if (e.target === this) closeRezerveModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key !== 'Escape') return;
        var b = document.getElementById('rezerveModalBackdrop');
        if (!b || !b.classList.contains('visible')) return;
        var uyeDd = document.getElementById('uyeDropdownRez');
        var kitapDd = document.getElementById('kitapDropdownRez');
        if (uyeDd && uyeDd.classList.contains('open')) {
            uyeDd.classList.remove('open');
            e.preventDefault();
            return;
        }
        if (kitapDd && kitapDd.classList.contains('open')) {
            kitapDd.classList.remove('open');
            e.preventDefault();
            return;
        }
        e.preventDefault();
        closeRezerveModal();
    });

    document.getElementById('btnRezerveOlustur').addEventListener('click', function() {
        var uid = document.getElementById('uyeIdRez').value;
        var kid = document.getElementById('katalogIdRez').value;
        if (!uid || !kid) { showToast('error', 'Eksik bilgi', 'Üye ve kitap seçin.'); return; }
        var btn = this;
        btn.disabled = true;
        fetch('{{ route('rezerve.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ uye_id: parseInt(uid, 10), katalog_id: parseInt(kid, 10) })
        })
            .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, status: r.status, body: j }; }); })
            .then(function(res) {
                btn.disabled = false;
                if (res.ok && res.body && res.body.success) {
                    showToast('success', res.body.message || 'Kayıt oluşturuldu.');
                    clearUyeRez();
                    clearKitapRez();
                    closeRezerveModal();
                    fetchTable(true);
                    return;
                }
                var msg = (res.body && res.body.message) ? res.body.message : 'İşlem başarısız.';
                if (res.body && res.body.errors) {
                    var vals = Object.values(res.body.errors);
                    if (vals.length && Array.isArray(vals[0])) msg = vals[0][0];
                }
                showToast('error', 'Rezervasyon', msg);
            })
            .catch(function() { btn.disabled = false; showToast('error', 'Hata', 'Sunucuya ulaşılamadı.'); });
    });
    @endif
})();
</script>
@endsection
