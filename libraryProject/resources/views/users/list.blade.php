@extends('layouts.base')

@section('title', 'Kullanicilar')

@section('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <style>
        .content-area { display: flex; flex-direction: column; gap: 20px; }

        /* ── Page header ── */
        .page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
        .page-title { font-family: var(--font-serif); font-size: 22px; font-weight: 700; }
        .page-subtitle { font-size: 13px; color: var(--muted-foreground); margin-top: 2px; }
        .page-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }

        /* ── Buttons ── */
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 8px 15px; border-radius: calc(var(--radius) - 2px); font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.15s, opacity 0.15s, box-shadow 0.15s; border: none; text-decoration: none; white-space: nowrap; }
        .btn svg { width: 15px; height: 15px; flex-shrink: 0; }
        .btn-primary { background: var(--primary); color: var(--primary-foreground); }
        .btn-primary:hover { opacity: 0.9; }
        .btn-outline { background: transparent; color: var(--foreground); border: 1px solid var(--border); }
        .btn-outline:hover { background: var(--muted); }
        .btn-success { background: #2f7d32; color: white; }
        .btn-success:hover { opacity: 0.88; }
        .btn-sm { padding: 6px 12px; font-size: 13px; }
        .btn-sm svg { width: 14px; height: 14px; }

        /* ── Toolbar ── */
        .toolbar { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .toolbar-group { display: flex; align-items: center; gap: 8px; }

        .filter-wrap { position: relative; }
        .filter-wrap svg.fi { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: var(--muted-foreground); pointer-events: none; }
        .filter-input { padding: 8px 12px 8px 33px; border: 1px solid var(--border); border-radius: calc(var(--radius) - 2px); background: var(--card); color: var(--foreground); font-size: 14px; outline: none; transition: border-color 0.15s, box-shadow 0.15s; min-width: 220px; }
        .filter-input:focus { border-color: var(--ring); box-shadow: 0 0 0 2px rgba(122,92,60,0.14); }
        .filter-input::placeholder { color: var(--muted-foreground); opacity: 0.7; }
        .filter-select { padding: 8px 32px 8px 10px; border: 1px solid var(--border); border-radius: calc(var(--radius) - 2px); background: var(--card); color: var(--foreground); font-size: 14px; outline: none; cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237a7060' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; transition: border-color 0.15s, box-shadow 0.15s; }
        .filter-select.has-icon { padding-left: 33px; }
        .filter-select:focus { border-color: var(--ring); box-shadow: 0 0 0 2px rgba(122,92,60,0.14); }

        /* Active filter indicator */
        .filter-active { border-color: var(--primary) !important; background: rgba(122,92,60,0.04) !important; }

        /* ── Table ── */
        .table-card { background: var(--card); border: 1px solid rgba(217,208,194,0.6); border-radius: var(--radius); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: var(--secondary); }
        th { padding: 11px 16px; text-align: left; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--muted-foreground); white-space: nowrap; border-bottom: 1px solid var(--border); }
        td { padding: 12px 16px; font-size: 14px; border-bottom: 1px solid rgba(217,208,194,0.4); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tbody tr { transition: background 0.1s; }
        tbody tr:hover { background: rgba(237,232,222,0.5); }

        /* User cell */
        .user-cell { display: flex; align-items: center; gap: 12px; }
        .user-avatar-sm { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; color: white; flex-shrink: 0; }
        .user-name { font-weight: 600; font-size: 14px; }
        .user-name-link { color: inherit; text-decoration: none; }
        .user-name-link:hover { text-decoration: underline; }
        .user-email { font-size: 12px; color: var(--muted-foreground); }

        /* Role badge */
        .role-badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .role-admin    { background: rgba(122,92,60,0.12); color: #5a3e28; }
        .role-personel { background: rgba(26,107,26,0.10); color: #1a5c1d; }
        .role-okuyucu  { background: rgba(37,99,235,0.08); color: #1e40af; }
        .status-badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; text-transform: capitalize; }
        .status-aktif { background: rgba(26,107,26,0.10); color: #1a5c1d; }
        .status-pasif { background: rgba(197,48,48,0.10); color: #c53030; }

        /* Lib pills */
        .lib-cell { display: flex; flex-wrap: wrap; gap: 4px; align-items: center; max-width: 240px; }
        .lib-pill { display: inline-flex; align-items: center; gap: 3px; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 500; background: rgba(122,92,60,0.09); color: var(--primary); border: 1px solid rgba(122,92,60,0.16); white-space: nowrap; max-width: 150px; overflow: hidden; text-overflow: ellipsis; }
        .lib-pill svg { width: 10px; height: 10px; flex-shrink: 0; }
        .lib-pill-more { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 600; background: var(--secondary); color: var(--muted-foreground); border: 1px solid var(--border); white-space: nowrap; }
        .lib-none { font-size: 12px; color: var(--muted-foreground); }
        .lib-tooltip-wrap { position: relative; display: inline-flex; }
        .lib-tooltip { position: absolute; bottom: calc(100% + 6px); left: 50%; transform: translateX(-50%); background: #3d3226; color: #e8e2d6; font-size: 11px; padding: 6px 10px; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); opacity: 0; visibility: hidden; transition: opacity 0.15s, visibility 0.15s; pointer-events: none; z-index: 50; max-width: 220px; white-space: normal; text-align: center; line-height: 1.5; }
        .lib-tooltip::after { content: ''; position: absolute; top: 100%; left: 50%; transform: translateX(-50%); border: 5px solid transparent; border-top-color: #3d3226; }
        .lib-tooltip-wrap:hover .lib-tooltip { opacity: 1; visibility: visible; }

        /* ── Table loading overlay ── */
        .table-loading { position: relative; }
        .table-loading-veil { position: absolute; inset: 0; background: rgba(250,248,243,0.72); display: flex; align-items: center; justify-content: center; z-index: 10; border-radius: var(--radius); opacity: 0; visibility: hidden; transition: opacity 0.2s, visibility 0.2s; pointer-events: none; }
        .table-loading-veil.visible { opacity: 1; visibility: visible; pointer-events: all; }
        .tl-spinner { width: 32px; height: 32px; border: 3px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Table footer / pagination ── */
        .table-footer { padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px; border-top: 1px solid rgba(217,208,194,0.5); font-size: 13px; color: var(--muted-foreground); flex-wrap: wrap; }
        .tf-info { display: flex; align-items: center; gap: 12px; }
        .per-page-wrap { display: flex; align-items: center; gap: 6px; font-size: 13px; }
        .per-page-select { padding: 4px 28px 4px 8px; border: 1px solid var(--border); border-radius: calc(var(--radius) - 2px); background: var(--card); color: var(--foreground); font-size: 13px; outline: none; cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%237a7060' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 8px center; transition: border-color 0.15s; }
        .per-page-select:focus { border-color: var(--ring); }

        .pagination { display: flex; align-items: center; gap: 4px; }
        .page-btn { display: inline-flex; align-items: center; justify-content: center; min-width: 32px; height: 32px; padding: 0 6px; border-radius: calc(var(--radius) - 2px); font-size: 13px; font-weight: 500; cursor: pointer; border: 1px solid var(--border); background: var(--card); color: var(--foreground); transition: background 0.12s, border-color 0.12s; user-select: none; }
        .page-btn:hover:not(.disabled):not(.active) { background: var(--muted); }
        .page-btn.active { background: var(--primary); color: var(--primary-foreground); border-color: var(--primary); cursor: default; }
        .page-btn.disabled { opacity: 0.38; cursor: default; pointer-events: none; }
        .page-btn svg { width: 13px; height: 13px; }
        .page-ellipsis { padding: 0 4px; color: var(--muted-foreground); font-size: 13px; }

        /* ── Empty state ── */
        .empty-state { padding: 60px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 12px; }
        .empty-icon { width: 56px; height: 56px; border-radius: 50%; background: var(--muted); display: flex; align-items: center; justify-content: center; }
        .empty-icon svg { width: 28px; height: 28px; color: var(--muted-foreground); }
        .empty-title { font-size: 15px; font-weight: 600; }
        .empty-desc { font-size: 13px; color: var(--muted-foreground); }

        /* ── Toast ── */
        .toast-container { position: fixed; top: 16px; right: 16px; z-index: 3000; display: flex; flex-direction: column; gap: 8px; }
        .toast { padding: 14px 20px; border-radius: 10px; font-size: 14px; font-weight: 500; box-shadow: 0 4px 12px rgba(0,0,0,0.15); animation: toast-in 0.3s ease; max-width: 380px; }
        .toast.success { background: #2f7d32; color: white; }
        .toast.error { background: var(--destructive); color: white; }
        .toast-desc { font-size: 13px; font-weight: 400; opacity: 0.9; margin-top: 2px; }
        @keyframes toast-in { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes toast-out { from { opacity: 1; } to { opacity: 0; transform: translateX(60px); } }

        /* ── Delete Modal ── */
        .modal-backdrop { position: fixed; inset: 0; background: rgba(61,50,38,0.5); backdrop-filter: blur(3px); z-index: 500; display: flex; align-items: center; justify-content: center; opacity: 0; visibility: hidden; transition: opacity 0.2s, visibility 0.2s; }
        .modal-backdrop.visible { opacity: 1; visibility: visible; }
        .modal { background: var(--card); border: 1px solid var(--border); border-radius: 14px; padding: 32px; max-width: 400px; width: calc(100% - 32px); box-shadow: 0 20px 60px rgba(0,0,0,0.2); transform: scale(0.95); transition: transform 0.2s; }
        .modal-backdrop.visible .modal { transform: scale(1); }
        .modal-icon { width: 48px; height: 48px; border-radius: 50%; background: rgba(197,48,48,0.1); display: flex; align-items: center; justify-content: center; margin-bottom: 16px; }
        .modal-icon svg { width: 24px; height: 24px; color: var(--destructive); }
        .modal-title { font-family: var(--font-serif); font-size: 18px; font-weight: 700; margin-bottom: 8px; }
        .modal-desc { font-size: 14px; color: var(--muted-foreground); line-height: 1.6; margin-bottom: 24px; }
        .modal-actions { display: flex; gap: 10px; justify-content: flex-end; }
        .btn-danger { background: var(--destructive); color: white; }
        .btn-danger:hover { opacity: 0.9; }

        @media (max-width: 768px) {
            .page-header { flex-direction: column; align-items: flex-start; }
            .toolbar { flex-direction: column; align-items: stretch; }
            .filter-input { min-width: 0; width: 100%; }
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
        <span class="breadcrumb-current">Kullanıcılar</span>
    </nav>
@endsection

@section('content')
<div class="toast-container" id="toastContainer"></div>
<!-- Delete Modal -->
<div class="modal-backdrop" id="deleteModal">
    <div class="modal">
        <div class="modal-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
        </div>
        <div class="modal-title">Kullanıcıyı Sil</div>
        <div class="modal-desc" id="deleteModalDesc"></div>
        <div class="modal-actions">
            <button class="btn btn-outline" id="deleteCancelBtn">Vazgeç</button>
            <form id="deleteForm" method="POST" style="margin:0;">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                    Evet, Sil
                </button>
            </form>
        </div>
    </div>
</div>

        <div class="content-area">

            <!-- Sayfa Başlığı -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Kullanıcılar</h1>
                    <p class="page-subtitle" id="pageSubtitle">Yükleniyor…</p>
                </div>
                <div class="page-actions">
                    <!-- Excel İndir -->
                    <button class="btn btn-success" id="exportBtn" title="Mevcut filtreyle Excel (CSV) indir">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                        Excel İndir
                    </button>
                    @if(auth()->user()->hasYetki(15))
                    <a href="{{ route('users.new') }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                        Yeni Kullanıcı
                    </a>
                    @endif
                    
                </div>
            </div>

            <!-- Filtreler -->
            <div class="toolbar">
                <!-- Metin arama -->
                <div class="filter-wrap">
                    <svg class="fi" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" class="filter-input" id="searchInput" placeholder="İsim veya e-posta ara…" autocomplete="off" />
                </div>

                <!-- Rol filtresi -->
                <div class="filter-wrap">
                    <svg class="fi" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <select class="filter-select has-icon" id="roleFilter">
                        <option value="">Tüm Roller</option>
                        <option value="admin">Yönetici</option>
                        <option value="personel">Personel</option>
                        <option value="okuyucu">Okuyucu</option>
                    </select>
                </div>

                <!-- Durum filtresi -->
                <div class="filter-wrap">
                    <svg class="fi" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                    <select class="filter-select has-icon" id="statuFilter">
                        <option value="">Tüm Durumlar</option>
                        <option value="aktif" selected>Aktif</option>
                        <option value="pasif">Pasif</option>
                    </select>
                </div>

                <!-- Kütüphane filtresi -->
                <div class="filter-wrap">
                    <svg class="fi" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                    <select class="filter-select has-icon" id="kutuphaneFilter">
                        <option value="">Tüm Kütüphaneler</option>
                        @foreach($kutuphaneler as $k)
                            <option value="{{ $k->id }}">{{ $k->title }}{{ $k->statu === 'pasif' ? ' (Pasif)' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtre temizle -->
                <button class="btn btn-outline btn-sm" id="clearFiltersBtn" style="display:none;" title="Filtreleri temizle">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    Temizle
                </button>
            </div>

            <!-- Tablo -->
            <div class="table-card table-loading" id="tableCard">
                <div class="table-loading-veil" id="tableVeil">
                    <div class="tl-spinner"></div>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th style="width:48px;">#</th>
                            <th>Kullanıcı</th>
                            <th style="width:110px;">Rol</th>
                            <th style="width:110px;">Durum</th>
                            <th>Yetkili Kütüphaneler</th>
                            <th style="width:100px;">Kayıt Tarihi</th>
                            <th style="width:130px;">Son Giriş</th>
                            <th style="width:120px;"></th>
                        </tr>
                        </thead>
                        <tbody id="tableBody">
                        <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--muted-foreground);font-size:13px;">Yükleniyor…</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer: bilgi + per-page + pagination -->
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
        setTimeout(function() { t.style.animation = 'toast-out 0.3s ease forwards'; setTimeout(function() { if (t.parentNode) t.parentNode.removeChild(t); }, 300); }, 4000);
    }
    @if(session('success'))
    showToast('success', '{{ session('success') }}');
    @endif

    // ══════════════════════════════════════════════════════════════════════════════
    // State
    // ══════════════════════════════════════════════════════════════════════════════
    var state = { search: '', role: '', statu: 'aktif', kutuphane_id: 0, per_page: 10, page: 1 };
    var fetchTimer   = null;   // debounce timer (arama kutusu)
    var activeXhr    = null;   // mevcut AbortController — önceki isteği iptal etmek için
    var currentAuthId = {{ auth()->id() }};

    // ══════════════════════════════════════════════════════════════════════════════
    // HTML helpers
    // ══════════════════════════════════════════════════════════════════════════════
    function esc(str) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(str || ''));
        return d.innerHTML;
    }

    var avatarColors = { admin: '#6b4c2a', personel: '#2e5e31', okuyucu: '#1e3a6b' };
    var roleBgs = {
        admin:    'background:rgba(122,92,60,0.12);color:#5a3e28;',
        personel: 'background:rgba(26,107,26,0.1);color:#1a5c1d;',
        okuyucu:  'background:rgba(37,99,235,0.08);color:#1e40af;'
    };
    var bookIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>';

    function renderLibPills(libs) {
        if (!libs || libs.length === 0) return '<span class="lib-none">—</span>';
        var MAX = 2;
        var html = '<div class="lib-cell">';
        for (var i = 0; i < Math.min(libs.length, MAX); i++) {
            html += '<span class="lib-pill" title="' + esc(libs[i].title) + '">' + bookIcon + esc(libs[i].title) + '</span>';
        }
        if (libs.length > MAX) {
            var rest = libs.slice(MAX).map(function(l) { return esc(l.title); }).join(', ');
            html += '<span class="lib-tooltip-wrap"><span class="lib-pill-more">+' + (libs.length - MAX) + ' daha</span><span class="lib-tooltip">' + rest + '</span></span>';
        }
        html += '</div>';
        return html;
    }

    function buildRow(u) {
        var initial = (u.name || '?')[0].toUpperCase();
        var avatarColor = avatarColors[u.role] || '#524435';
        var roleBg = roleBgs[u.role] || '';

        var deleteBtn = u.is_self ? '' :
            '<button class="btn btn-sm" style="background:rgba(197,48,48,0.08);color:var(--destructive);border:1px solid rgba(197,48,48,0.2);" onclick="confirmDelete(\'' + u.delete_url + '\', \'' + esc(u.name) + '\')">' +
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>' +
            'Sil</button>';

        return '<tr>' +
            '<td style="color:var(--muted-foreground);font-size:13px;">' + u.id + '</td>' +
            '<td><div class="user-cell">' +
            '<div class="user-avatar-sm" style="background:' + avatarColor + ';">' + initial + '</div>' +
            '<div><div class="user-name"><a class="user-name-link" href="' + u.edit_url + '">' + esc(u.name) + '</a>' + (u.is_self ? ' <span style="font-size:11px;background:rgba(122,92,60,0.1);color:var(--primary);padding:1px 7px;border-radius:999px;font-weight:600;">Siz</span>' : '') + '</div>' +
            '<div class="user-email">' + esc(u.email) + '</div></div>' +
            '</div></td>' +
            '<td><span class="role-badge" style="' + roleBg + '">' + esc(u.role_label) + '</span></td>' +
            '<td><span class="status-badge status-' + esc(u.statu || 'aktif') + '">' + esc(u.statu || 'aktif') + '</span></td>' +
            '<td>' + renderLibPills(u.kutuphaneler) + '</td>' +
            '<td style="font-size:13px;color:var(--muted-foreground);">' + esc(u.created_at) + '</td>' +
            '<td style="font-size:13px;color:var(--muted-foreground);">' + esc(u.last_login_at) + '</td>' +
            '<td><div style="display:flex;gap:6px;justify-content:flex-end;">' +
            '<a href="' + u.edit_url + '" class="btn btn-outline btn-sm">' +
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Düzenle</a>' +
            deleteBtn +
            '</div></td>' +
            '</tr>';
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // Pagination builder
    // ══════════════════════════════════════════════════════════════════════════════
    function buildPagination(meta) {
        var container = document.getElementById('pagination');
        if (meta.last_page <= 1) { container.innerHTML = ''; return; }

        var cur  = meta.current_page;
        var last = meta.last_page;
        var html = '';

        // Prev
        html += '<button class="page-btn ' + (cur <= 1 ? 'disabled' : '') + '" onclick="goPage(' + (cur - 1) + ')">' +
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>' +
            '</button>';

        // Page numbers with ellipsis
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
            if (p === '…') {
                html += '<span class="page-ellipsis">…</span>';
            } else {
                html += '<button class="page-btn ' + (p === cur ? 'active' : '') + '" onclick="goPage(' + p + ')">' + p + '</button>';
            }
        });

        // Next
        html += '<button class="page-btn ' + (cur >= last ? 'disabled' : '') + '" onclick="goPage(' + (cur + 1) + ')">' +
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>' +
            '</button>';

        container.innerHTML = html;
    }

    function goPage(p) {
        if (p < 1) return;
        state.page = p;
        fetchTable();
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // AJAX Fetch  —  AbortController ile race condition önlemi
    // ══════════════════════════════════════════════════════════════════════════════
    function fetchTable(resetPage) {
        if (resetPage) state.page = 1;

        // Önceki isteği iptal et (race condition önlemi)
        if (activeXhr) { activeXhr.abort(); }
        activeXhr = new AbortController();

        var veil = document.getElementById('tableVeil');
        veil.classList.add('visible');

        var params = new URLSearchParams({
            search:       state.search,
            role:         state.role,
            statu:        state.statu,
            kutuphane_id: state.kutuphane_id > 0 ? state.kutuphane_id : '',
            per_page:     state.per_page,
            page:         state.page,
        });

        var ctrl = activeXhr; // closure için yerel referans

        fetch('/kullanicilar/tablo?' + params.toString(), {
            signal:  ctrl.signal,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function(res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function(result) {
                // Bu istek iptal edildiyse (daha yeni bir istek başlatıldıysa) DOM'u güncelleme
                if (ctrl.signal.aborted) return;
                activeXhr = null;

                veil.classList.remove('visible');
                if (!result.success) { showToast('error', 'Hata', 'Veriler yüklenemedi.'); return; }

                // result.data'nın kesinlikle array olmasını garanti et
                // (bazı durumlarda paginator objesi gelebilir)
                var rows = Array.isArray(result.data)
                    ? result.data
                    : (result.data && Array.isArray(result.data.data) ? result.data.data : []);

                var tbody = document.getElementById('tableBody');
                if (rows.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8">' +
                        '<div class="empty-state">' +
                        '<div class="empty-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>' +
                        '<p class="empty-title">Kullanıcı bulunamadı</p>' +
                        '<p class="empty-desc">Arama kriterlerinizi değiştirin veya yeni kullanıcı ekleyin.</p>' +
                        '</div></td></tr>';
                } else {
                    tbody.innerHTML = rows.map(buildRow).join('');
                }

                var m = result.meta;
                document.getElementById('pageSubtitle').textContent = 'Toplam ' + m.total + ' kullanıcı kayıtlı';
                document.getElementById('rangeInfo').textContent    = m.from + '–' + m.to + ' / ' + m.total + ' kayıt';
                buildPagination(m);
                updateClearBtn();
            })
            .catch(function(err) {
                // AbortError = bilerek iptal edildi, hata gösterme
                if (err && err.name === 'AbortError') return;
                document.getElementById('tableVeil').classList.remove('visible');
                showToast('error', 'Bağlantı Hatası', 'Veriler yüklenemedi.');
            });
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // Filter listeners
    // ══════════════════════════════════════════════════════════════════════════════
    function updateClearBtn() {
        var hasFilter = state.search !== '' || state.role !== '' || state.statu !== '' || state.kutuphane_id > 0;
        document.getElementById('clearFiltersBtn').style.display = hasFilter ? '' : 'none';
        document.getElementById('searchInput').classList.toggle('filter-active', state.search !== '');
        document.getElementById('roleFilter').classList.toggle('filter-active', state.role !== '');
        document.getElementById('statuFilter').classList.toggle('filter-active', state.statu !== '');
        document.getElementById('kutuphaneFilter').classList.toggle('filter-active', state.kutuphane_id > 0);
    }

    document.getElementById('searchInput').addEventListener('input', function() {
        state.search = this.value.trim();
        clearTimeout(fetchTimer);
        fetchTimer = setTimeout(function() { fetchTable(true); }, 380);
    });

    document.getElementById('roleFilter').addEventListener('change', function() {
        clearTimeout(fetchTimer); // bekleyen arama timer'ını iptal et
        state.role = this.value;
        fetchTable(true);
    });

    document.getElementById('statuFilter').addEventListener('change', function() {
        clearTimeout(fetchTimer); // bekleyen arama timer'ını iptal et
        state.statu = this.value;
        fetchTable(true);
    });

    document.getElementById('kutuphaneFilter').addEventListener('change', function() {
        clearTimeout(fetchTimer); // bekleyen arama timer'ını iptal et
        state.kutuphane_id = parseInt(this.value) || 0;
        fetchTable(true);
    });

    document.getElementById('perPageSelect').addEventListener('change', function() {
        clearTimeout(fetchTimer);
        state.per_page = parseInt(this.value);
        fetchTable(true);
    });

    document.getElementById('clearFiltersBtn').addEventListener('click', function() {
        clearTimeout(fetchTimer);
        state.search = ''; state.role = ''; state.statu = 'aktif'; state.kutuphane_id = 0;
        document.getElementById('searchInput').value    = '';
        document.getElementById('roleFilter').value     = '';
        document.getElementById('statuFilter').value    = 'aktif';
        document.getElementById('kutuphaneFilter').value = '';
        fetchTable(true);
    });

    // ══════════════════════════════════════════════════════════════════════════════
    // Excel Export
    // ══════════════════════════════════════════════════════════════════════════════
    document.getElementById('exportBtn').addEventListener('click', function() {
        var params = new URLSearchParams({
            search:       state.search,
            role:         state.role,
            statu:        state.statu,
            kutuphane_id: state.kutuphane_id || '',
        });
        var a = document.createElement('a');
        a.href = '/kullanicilar/export?' + params.toString();
        a.download = '';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    });

    // ══════════════════════════════════════════════════════════════════════════════
    // Delete
    // ══════════════════════════════════════════════════════════════════════════════
    var deleteModal = document.getElementById('deleteModal');
    var deleteForm  = document.getElementById('deleteForm');

    function confirmDelete(url, name) {
        document.getElementById('deleteModalDesc').textContent = '"' + name + '" kullanıcısını silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.';
        deleteForm.action = url;
        deleteModal.classList.add('visible');
    }

    document.getElementById('deleteCancelBtn').addEventListener('click', function() { deleteModal.classList.remove('visible'); });
    deleteModal.addEventListener('click', function(e) { if (e.target === deleteModal) deleteModal.classList.remove('visible'); });

    // ══════════════════════════════════════════════════════════════════════════════
    // Boot
    // ══════════════════════════════════════════════════════════════════════════════
    fetchTable();
</script>
@endsection
