@extends('layouts.base')

@section('title', 'Odunc Islemleri')

@section('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <style>
        .odunc-content{display:flex;flex-direction:column;gap:20px}

        /* ── Buttons ── */
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:9px 16px;border-radius:calc(var(--radius) - 2px);font-size:14px;font-weight:500;cursor:pointer;transition:background .15s,opacity .15s;border:none;text-decoration:none}
        .btn svg{width:16px;height:16px}
        .btn-primary{background:var(--primary);color:var(--primary-foreground)}
        .btn-primary:hover{opacity:.9}
        .btn-outline{background:transparent;color:var(--foreground);border:1px solid var(--border)}
        .btn-outline:hover{background:var(--muted)}
        .btn-success{background:#16a34a;color:#fff}
        .btn-success:hover{opacity:.9}
        .btn-danger{background:var(--destructive);color:#fff}
        .btn-danger:hover{opacity:.9}
        .btn-sm{padding:5px 11px;font-size:13px}
        .btn-xs{padding:3px 9px;font-size:12px}

        /* ── Page Header ── */
        .page-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}
        .page-title{font-family:var(--font-serif);font-size:22px;font-weight:700}
        .page-subtitle{font-size:13px;color:var(--muted-foreground);margin-top:2px}

        /* ── Stats Row ── */
        .stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
        .stat-card{background:var(--card);border:1px solid rgba(217,208,194,.6);border-radius:var(--radius);padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,.04);cursor:pointer;transition:border-color .15s,box-shadow .15s;text-decoration:none;display:block}
        .stat-card:hover{border-color:var(--ring);box-shadow:0 2px 8px rgba(122,92,60,.12)}
        .stat-card.active-filter{border-color:var(--primary);box-shadow:0 0 0 2px rgba(122,92,60,.15)}
        .stat-label{font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--muted-foreground);margin-bottom:8px;display:flex;align-items:center;gap:6px}
        .stat-label svg{width:14px;height:14px}
        .stat-value{font-family:var(--font-serif);font-size:28px;font-weight:700;line-height:1}
        .stat-value.red{color:var(--destructive)}
        .stat-value.green{color:#16a34a}
        .stat-value.orange{color:#b45309}
        .stat-sub{font-size:12px;color:var(--muted-foreground);margin-top:4px}

        /* ── Toolbar ── */
        .toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
        .search-wrap{position:relative;flex:1;max-width:340px}
        .search-wrap svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--muted-foreground);pointer-events:none}
        .search-input{width:100%;padding:8px 12px 8px 34px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);color:var(--foreground);font-size:14px;outline:none;transition:border-color .15s,box-shadow .15s}
        .search-input:focus{border-color:var(--ring);box-shadow:0 0 0 2px rgba(122,92,60,.15)}
        .search-input::placeholder{color:var(--muted-foreground);opacity:.7}
        .filter-select{padding:8px 32px 8px 12px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);color:var(--foreground);font-size:14px;outline:none;cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237a7060' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;transition:border-color .15s}
        .filter-select:focus{border-color:var(--ring);box-shadow:0 0 0 2px rgba(122,92,60,.15)}
        .date-input{padding:8px 12px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);color:var(--foreground);font-size:14px;outline:none;transition:border-color .15s}
        .date-input:focus{border-color:var(--ring)}

        /* ── Table loading veil ── */
        .table-veil{position:absolute;inset:0;background:rgba(250,248,243,.75);display:flex;align-items:center;justify-content:center;z-index:10;border-radius:var(--radius);opacity:0;visibility:hidden;transition:opacity .18s,visibility .18s;pointer-events:none}
        .table-veil.visible{opacity:1;visibility:visible;pointer-events:all}
        .veil-spinner{width:32px;height:32px;border:3px solid var(--border);border-top-color:var(--primary);border-radius:50%;animation:spin .7s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}}

        /* ── Table ── */
        .table-card{background:var(--card);border:1px solid rgba(217,208,194,.6);border-radius:var(--radius);overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.04)}
        .table-wrap{overflow-x:auto}
        table{width:100%;border-collapse:collapse}
        thead{background:var(--secondary)}
        th{padding:11px 16px;text-align:left;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--muted-foreground);white-space:nowrap;border-bottom:1px solid var(--border)}
        td{padding:13px 16px;font-size:14px;border-bottom:1px solid rgba(217,208,194,.4);vertical-align:middle}
        tr:last-child td{border-bottom:none}
        tbody tr{transition:background .12s}
        tbody tr:hover{background:rgba(237,232,222,.5)}
        tbody tr.row-overdue{background:rgba(197,48,48,.04)}
        tbody tr.row-overdue:hover{background:rgba(197,48,48,.07)}

        /* ── Member cell ── */
        .member-cell{display:flex;align-items:center;gap:10px}
        .member-av{width:32px;height:32px;border-radius:50%;background:var(--sidebar-accent);color:var(--sidebar-foreground);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0}
        .member-name{font-weight:600;font-size:13px}
        .member-tc{font-size:12px;color:var(--muted-foreground)}

        /* ── Book cell ── */
        .book-cell{display:flex;align-items:center;gap:10px}
        .book-cover{width:32px;height:42px;border-radius:3px;object-fit:cover;flex-shrink:0;background:var(--secondary)}
        .book-cover-placeholder{width:32px;height:42px;border-radius:3px;background:var(--secondary);display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .book-cover-placeholder svg{width:16px;height:16px;color:var(--muted-foreground)}
        .book-title{font-weight:500;font-size:13px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .book-isbn{font-size:12px;color:var(--muted-foreground)}

        /* ── Date cell ── */
        .date-cell{font-size:13px}
        .date-main{font-weight:500}
        .date-sub{font-size:12px;color:var(--muted-foreground)}

        /* ── Overdue badge ── */
        .overdue-chip{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:700;background:rgba(197,48,48,.1);color:var(--destructive)}
        .overdue-chip svg{width:11px;height:11px}

        /* ── Status badges ── */
        .badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600}
        .badge-aktif{background:rgba(37,99,235,.08);color:#1e40af}
        .badge-gecikti{background:rgba(197,48,48,.1);color:#991b1b}
        .badge-iade{background:rgba(34,197,94,.1);color:#166534}
        .badge-kayip{background:rgba(107,114,128,.12);color:#374151}
        .badge-dot{width:6px;height:6px;border-radius:50%;flex-shrink:0}
        .badge-aktif .badge-dot{background:#3b82f6}
        .badge-gecikti .badge-dot{background:var(--destructive)}
        .badge-iade .badge-dot{background:#22c55e}
        .badge-kayip .badge-dot{background:#6b7280}

        /* ── Table footer ── */
        .table-footer{padding:12px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px;border-top:1px solid var(--border);font-size:13px;color:var(--muted-foreground);flex-wrap:wrap}
        .tf-info{display:flex;align-items:center;gap:12px}
        .per-page-wrap{display:flex;align-items:center;gap:6px;font-size:13px}
        .per-page-select{padding:4px 28px 4px 8px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);color:var(--foreground);font-size:13px;outline:none;cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%237a7060' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 8px center;transition:border-color .15s}
        .per-page-select:focus{border-color:var(--ring)}
        .pagination{display:flex;align-items:center;gap:4px;flex-wrap:wrap}
        .page-btn{display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:32px;padding:0 6px;border-radius:calc(var(--radius) - 2px);font-size:13px;font-weight:500;cursor:pointer;border:1px solid var(--border);background:var(--card);color:var(--foreground);transition:background .12s,border-color .12s;user-select:none}
        .page-btn:hover:not(.disabled):not(.active){background:var(--muted)}
        .page-btn.active{background:var(--primary);color:var(--primary-foreground);border-color:var(--primary);cursor:default}
        .page-btn.disabled{opacity:.38;cursor:default;pointer-events:none}
        .page-btn svg{width:13px;height:13px}
        .page-ellipsis{padding:0 4px;color:var(--muted-foreground);font-size:13px}

        /* ── Empty ── */
        .empty-state{padding:60px 24px;text-align:center}
        .empty-icon{width:56px;height:56px;border-radius:50%;background:var(--secondary);display:flex;align-items:center;justify-content:center;margin:0 auto 16px}
        .empty-icon svg{width:26px;height:26px;color:var(--muted-foreground)}
        .empty-title{font-size:16px;font-weight:600;margin-bottom:6px}
        .empty-desc{font-size:14px;color:var(--muted-foreground)}

        /* ── Modal ── */
        .modal-backdrop{position:fixed;inset:0;z-index:2000;background:rgba(61,50,38,.48);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:opacity .2s,visibility .2s}
        .modal-backdrop.visible{opacity:1;visibility:visible}
        .modal-box{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:0;max-width:480px;width:calc(100% - 32px);box-shadow:0 24px 64px rgba(0,0,0,.22);transform:scale(.93);transition:transform .2s;overflow:hidden}
        .modal-backdrop.visible .modal-box{transform:scale(1)}
        .modal-header{padding:20px 24px 0;display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
        .modal-title{font-family:var(--font-serif);font-size:18px;font-weight:700}
        .modal-close{width:28px;height:28px;border-radius:6px;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--muted-foreground);transition:background .15s}
        .modal-close:hover{background:var(--muted)}
        .modal-close svg{width:16px;height:16px}
        .modal-info{margin:14px 24px 0;padding:14px;background:var(--secondary);border-radius:8px;font-size:13px;display:flex;flex-direction:column;gap:6px}
        .modal-info-row{display:flex;justify-content:space-between;gap:12px}
        .modal-info-label{color:var(--muted-foreground);flex-shrink:0}
        .modal-info-val{font-weight:600;text-align:right}
        .modal-info-val.red{color:var(--destructive)}
        .modal-body{padding:16px 24px 0}
        .modal-footer{padding:16px 24px 20px;display:flex;justify-content:flex-end;gap:10px;border-top:1px solid var(--border);margin-top:16px}
        .form-field{display:flex;flex-direction:column;margin-bottom:14px}
        .form-label{font-size:14px;font-weight:500;margin-bottom:6px}
        .form-label .req{color:var(--destructive)}
        .form-input{width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);color:var(--foreground);font-size:14px;outline:none;transition:border-color .15s,box-shadow .15s}
        .form-input:focus{border-color:var(--ring);box-shadow:0 0 0 2px rgba(122,92,60,.15)}
        .form-textarea{width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);color:var(--foreground);font-size:14px;outline:none;resize:vertical;min-height:72px;transition:border-color .15s}
        .form-textarea:focus{border-color:var(--ring);box-shadow:0 0 0 2px rgba(122,92,60,.15)}
        .radio-row{display:flex;gap:10px}
        .radio-opt{flex:1;position:relative;cursor:pointer}
        .radio-opt input[type=radio]{position:absolute;opacity:0;width:0;height:0}
        .radio-opt-inner{padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;text-align:center;font-size:13px;font-weight:600;transition:border-color .15s,background .15s}
        .radio-opt input:checked ~ .radio-opt-inner{border-color:var(--primary);background:rgba(122,92,60,.06)}
        .radio-opt.danger input:checked ~ .radio-opt-inner{border-color:var(--destructive);background:rgba(197,48,48,.06);color:var(--destructive)}

        /* ── Toast ── */
        .toast-container{position:fixed;top:20px;right:20px;z-index:3000;display:flex;flex-direction:column;gap:10px}
        .toast{padding:14px 18px;border-radius:var(--radius);font-size:14px;font-weight:500;min-width:280px;box-shadow:0 4px 16px rgba(0,0,0,.12);border:1px solid transparent;animation:toast-in .3s ease}
        .toast.success{background:#f0fdf4;border-color:#bbf7d0;color:#166534}
        .toast.error{background:#fef2f2;border-color:#fecaca;color:#991b1b}
        .toast-desc{font-size:13px;opacity:.8;margin-top:2px}
        @keyframes toast-in{from{opacity:0;transform:translateX(20px)}to{opacity:1;transform:translateX(0)}}
        @keyframes toast-out{from{opacity:1;transform:translateX(0)}to{opacity:0;transform:translateX(20px)}}

        @media(max-width:900px){
            .stats-row{grid-template-columns:repeat(2,1fr)}
        }
        @media(max-width:768px){
            .stats-row{grid-template-columns:repeat(2,1fr)}
            .toolbar{gap:8px}
            .page-header{flex-direction:column;align-items:flex-start}
        }
    </style>
@endsection

@section('breadcrumb')
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('katalog.index') }}" class="breadcrumb-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Ana Sayfa
        </a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Ödünç İşlemleri</span>
    </nav>
@endsection

@section('content')
    <div class="odunc-content">

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Ödünç İşlemleri</h1>
                    <p class="page-subtitle">Kitap ödünç verme ve iade takibi</p>
                </div>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <button class="btn btn-success" id="exportBtn" title="Mevcut filtreyle Excel (CSV) indir">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                        Excel İndir
                    </button>
                    <a href="{{ route('odunc.new') }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M12 18v-6"/><path d="M9 15h6"/></svg>
                        Yeni Ödünç Ver
                    </a>
                </div>
            </div>

            <!-- Stats Row -->
            <div class="stats-row">
                <a href="#" class="stat-card {{ $statu === 'aktif' ? 'active-filter' : '' }}" data-statu="aktif">
                    <div class="stat-label">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3h5v5"/><path d="M8 3H3v5"/><path d="M12 22v-8.3a4 4 0 0 0-1.172-2.872L3 3"/><path d="m15 9 6-6"/></svg>
                        Aktif Ödünç
                    </div>
                    <div class="stat-value">{{ $stats['aktif'] }}</div>
                    <div class="stat-sub">şu an dışarıda</div>
                </a>
                <a href="#" class="stat-card {{ $statu === 'gecikti' ? 'active-filter' : '' }}" data-statu="gecikti">
                    <div class="stat-label" style="color:var(--destructive)">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Gecikmiş
                    </div>
                    <div class="stat-value red">{{ $stats['gecikti'] }}</div>
                    <div class="stat-sub">iade tarihi geçmiş</div>
                </a>
                <a href="#" class="stat-card {{ $statu === 'iade_edildi' ? 'active-filter' : '' }}" data-statu="iade_edildi">
                    <div class="stat-label" style="color:#166534">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        Bugün İade
                    </div>
                    <div class="stat-value green">{{ $stats['bugun_iade'] }}</div>
                    <div class="stat-sub">bugün teslim alındı</div>
                </a>
                <a href="#" class="stat-card {{ $statu === 'hepsi' ? 'active-filter' : '' }}" data-statu="hepsi">
                    <div class="stat-label">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h7v7H3z"/><path d="M14 3h7v7h-7z"/><path d="M14 14h7v7h-7z"/><path d="M3 14h7v7H3z"/></svg>
                        Toplam İşlem
                    </div>
                    <div class="stat-value">{{ $stats['toplam'] }}</div>
                    <div class="stat-sub">tüm zamanlar</div>
                </a>
            </div>

            <!-- Toolbar -->
            <div class="toolbar">
                <div class="search-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input type="text" id="searchInput" class="search-input" placeholder="Üye adı, TC, kitap adı, ISBN…" autocomplete="off" />
                </div>
                <input type="text" id="demirbasInput" class="search-input" style="max-width:180px;" placeholder="Demirbaş No…" autocomplete="off" />
                <select id="kutuphaneFilter" class="filter-select">
                    <option value="">Tüm Kütüphaneler</option>
                    @foreach($kutuphaneler as $kutuphane)
                        <option value="{{ $kutuphane->id }}">{{ $kutuphane->title }}</option>
                    @endforeach
                </select>
                <select id="statuFilter" class="filter-select">
                    <option value="aktif">Aktif Ödünçler</option>
                    <option value="gecikti">Gecikmiş</option>
                    <option value="iade_edildi">İade Edilmiş</option>
                    <option value="kayip">Kayıp</option>
                    <option value="hepsi">Tümü</option>
                </select>
                <input type="date" id="tarihBaslangic" class="date-input" title="Başlangıç tarihi" />
                <input type="date" id="tarihBitis" class="date-input" title="Bitiş tarihi" />
            </div>

            <!-- Table -->
            <div class="table-card" style="position:relative;">
                <div class="table-veil" id="tableVeil"><div class="veil-spinner"></div></div>
                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Üye</th>
                            <th>Kitap</th>
                            <th>Ödünç Tarihi</th>
                            <th>İade Tarihi</th>
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
                                <option value="500">500</option>
                            </select>
                        </div>
                    </div>
                    <div class="pagination" id="pagination"></div>
                </div>
            </div>
<!-- ─── Süre Uzat Modal ──────────────────────────────────────────────────────── -->
<div class="modal-backdrop" id="uzatModal">
    <div class="modal-box">
        <div class="modal-header">
            <h2 class="modal-title">Süre Uzat</h2>
            <button class="modal-close" onclick="closeUzatModal()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <div class="modal-info" id="uzatInfo">
            <div class="modal-info-row"><span class="modal-info-label">Üye</span><span class="modal-info-val" id="uzatUye">—</span></div>
            <div class="modal-info-row"><span class="modal-info-label">Kitap</span><span class="modal-info-val" id="uzatKitap" style="max-width:220px;overflow:hidden;text-overflow:ellipsis;">—</span></div>
            <div class="modal-info-row"><span class="modal-info-label">Planlanan İade</span><span class="modal-info-val" id="uzatMevcutTarih">—</span></div>
        </div>
        <div class="modal-body">
            <div class="form-field" style="margin-bottom:14px;">
                <label class="form-label" for="uzatma_gun">Kaç Gün Uzatılsın? <span class="req">*</span>
                    <span style="font-weight:400;color:var(--muted-foreground);font-size:12px;">(En fazla 15 gün)</span>
                </label>
                <input type="number" id="uzatma_gun" class="form-input" min="1" max="15" placeholder="1 – 15 gün"
                       oninput="hesaplaYeniTarih()" onchange="hesaplaYeniTarih()" />
            </div>
            <div id="uzatSonucWrap" style="display:none;padding:12px 14px;background:rgba(122,92,60,.06);border:1px solid rgba(122,92,60,.2);border-radius:8px;font-size:13px;color:var(--foreground);">
                <span style="color:var(--muted-foreground);">Yeni Planlanan İade Tarihi:</span>
                <strong id="uzatYeniTarih" style="margin-left:6px;font-size:15px;color:var(--primary);"></strong>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeUzatModal()">Vazgeç</button>
            <button type="button" class="btn btn-primary" id="uzatSubmitBtn" onclick="submitUzat()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/><path d="m22 22-4-4"/><path d="M18 22v-4h-4"/></svg>
                Süreyi Uzat
            </button>
        </div>
    </div>
</div>

<!-- ─── İade Modal ─────────────────────────────────────────────────────────── -->
<div class="modal-backdrop" id="iadeModal">
    <div class="modal-box">
        <div class="modal-header">
            <h2 class="modal-title">İade Al</h2>
            <button class="modal-close" onclick="closeIadeModal()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>

        <div class="modal-info" id="iadeInfo">
            <div class="modal-info-row"><span class="modal-info-label">Üye</span><span class="modal-info-val" id="iadeUye">—</span></div>
            <div class="modal-info-row"><span class="modal-info-label">Kitap</span><span class="modal-info-val" id="iadeKitap" style="max-width:220px;overflow:hidden;text-overflow:ellipsis;">—</span></div>
            <div class="modal-info-row"><span class="modal-info-label">Ödünç Tarihi</span><span class="modal-info-val" id="iadeOduncTarihi">—</span></div>
            <div class="modal-info-row"><span class="modal-info-label">Planlanan İade</span><span class="modal-info-val" id="iadePlanlanan">—</span></div>
            <div class="modal-info-row" id="iadeGecikmeRow" style="display:none;">
                <span class="modal-info-label">Gecikme</span>
                <span class="modal-info-val red" id="iadeGecikme">—</span>
            </div>
        </div>

        <form id="iadeForm">
            @csrf
            <div class="modal-body">
                <div class="form-field">
                    <label class="form-label">İşlem Türü <span class="req">*</span></label>
                    <div class="radio-row">
                        <label class="radio-opt">
                            <input type="radio" name="statu" value="iade_edildi" checked />
                            <div class="radio-opt-inner">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:3px;"><path d="M20 6 9 17l-5-5"/></svg><br/>
                                İade Alındı
                            </div>
                        </label>
                        <label class="radio-opt danger">
                            <input type="radio" name="statu" value="kayip" />
                            <div class="radio-opt-inner">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:3px;"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg><br/>
                                Kayıp
                            </div>
                        </label>
                    </div>
                </div>
                <div class="form-field">
                    <label class="form-label" for="iade_tarihi_gercek">İade Tarihi <span class="req">*</span></label>
                    <input type="date" id="iade_tarihi_gercek" name="iade_tarihi_gercek" class="form-input"
                           value="{{ date('Y-m-d') }}"
                           max="{{ date('Y-m-d') }}"
                           min="{{ date('Y-m-d', strtotime('-7 days')) }}"
                           required />
                </div>
                <div class="form-field">
                    <label class="form-label" for="iade_notu">Not <span style="font-weight:400;color:var(--muted-foreground);font-size:12px;">(isteğe bağlı)</span></label>
                    <textarea id="iade_notu" name="iade_notu" class="form-textarea" placeholder="İade notu, hasar durumu vs…"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeIadeModal()">Vazgeç</button>
                <button type="button" class="btn btn-success" id="iadeSubmitBtn" onclick="submitIade()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/></svg>
                    İadeyi Tamamla
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>
    </div>
@endsection

@section('scripts')
<script>

    // ── Toast ────────────────────────────────────────────────────────────────
    function showToast(type, title, desc) {
        var c = document.getElementById('toastContainer');
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<div>' + title + '</div>' + (desc ? '<div class="toast-desc">' + desc + '</div>' : '');
        c.appendChild(t);
        setTimeout(function() { t.style.animation = 'toast-out .3s ease forwards'; setTimeout(function() { if(t.parentNode) t.parentNode.removeChild(t); }, 300); }, 4500);
    }
    @if(session('success')) showToast('success', @json(session('success'))); @endif
    @if(session('error'))   showToast('error',   @json(session('error'))); @endif

    // ── State ────────────────────────────────────────────────────────────────
    var state = {
        search:          '',
        demirbasNo:      '',
        kutuphaneId:     '',
        statu:           '{{ $statu }}',
        tarih_baslangic: '',
        tarih_bitis:     '',
        per_page:        20,
        page:            1,
    };
    var fetchTimer = null;
    var activeXhr  = null; // AbortController — race condition önlemi

    // ── İlk yükleme: stat kartından gelen statu değerini select'e yansıt ────
    document.getElementById('statuFilter').value = state.statu;

    // ── HTML helpers ─────────────────────────────────────────────────────────
    function esc(s) { var d = document.createElement('div'); d.appendChild(document.createTextNode(s||'')); return d.innerHTML; }

    var bookIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>';
    var clockIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:11px;height:11px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>';
    var eyeIcon   = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
    var iadeIcon  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/></svg>';
    var uzatIcon  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/><path d="m22 22-4-4"/><path d="M18 22v-4h-4"/></svg>';

    function buildRow(i) {
        var rowClass = i.gecikiyor ? ' class="row-overdue"' : '';

        // Üye
        var uyeCell = '<div class="member-cell"><div class="member-av">' + esc(i.uye_initials) + '</div><div>' +
            '<div class="member-name">' + esc(i.uye_ad) + '</div>' +
            '<div class="member-tc">' + esc(i.uye_tc) + '</div></div></div>';

        // Kitap
        var coverHtml = i.kitap_kapak
            ? '<img src="' + i.kitap_kapak + '" alt="" class="book-cover" />'
            : '<div class="book-cover-placeholder">' + bookIcon + '</div>';
        var bookCell = '<div class="book-cell">' + coverHtml + '<div>' +
            '<div class="book-title" title="' + esc(i.kitap) + '">' + esc(i.kitap) + '</div>' +
            '<div class="book-isbn">Demirbaş: '+ esc(i.kitap_demir) + ' - ISBN: ' + esc(i.kitap_isbn || '—') + '</div></div></div>';

        // Ödünç tarihi
        var oduncCell = '<div class="date-cell"><div class="date-main">' + esc(i.odunc_tarihi) + '</div>' +
            (i.odunc_veren ? '<div class="date-sub">' + esc(i.odunc_veren) + '</div>' : '') + '</div>';

        // İade tarihi
        var iadeCell;
        if (i.statu === 'iade_edildi' || i.statu === 'kayip') {
            iadeCell = '<div class="date-cell"><div class="date-main">' + esc(i.iade_gercek || '—') + '</div>' +
                '<div class="date-sub">plan: ' + esc(i.iade_planlanan) + '</div></div>';
        } else if (i.gecikiyor) {
            iadeCell = '<div class="date-cell"><div class="date-main" style="color:var(--destructive)">' + esc(i.iade_planlanan) + '</div>' +
                '<div class="overdue-chip" style="margin-top:3px;">' + clockIcon + ' ' + i.gecikme_gun + ' gün gecikmiş</div></div>';
        } else {
            var kalanStyle = (i.kalan_gun !== null && i.kalan_gun <= 2) ? 'style="color:#b45309;font-weight:600;"' : '';
            iadeCell = '<div class="date-cell"><div class="date-main">' + esc(i.iade_planlanan) + '</div>' +
                (i.kalan_gun !== null ? '<div class="date-sub" ' + kalanStyle + '>' + i.kalan_gun + ' gün kaldı</div>' : '') + '</div>';
        }

        // Durum badge
        var badge;
        if      (i.statu === 'iade_edildi') badge = '<span class="badge badge-iade"><span class="badge-dot"></span> İade Edildi</span>';
        else if (i.statu === 'kayip')       badge = '<span class="badge badge-kayip"><span class="badge-dot"></span> Kayıp</span>';
        else if (i.gecikiyor)               badge = '<span class="badge badge-gecikti"><span class="badge-dot"></span> Gecikmiş</span>';
        else                                badge = '<span class="badge badge-aktif"><span class="badge-dot"></span> Aktif</span>';

        // Aksiyonlar
        var actions = '<div style="display:flex;align-items:center;gap:6px;justify-content:flex-end;">' +
            '<a href="' + i.detay_url + '" class="btn btn-outline btn-sm">' + eyeIcon + ' Detay</a>';
        if (i.statu === 'aktif') {
            actions += '<button class="btn btn-outline btn-sm" onclick="openUzatModal(' + i.id + ', \'' + esc(i.iade_planlanan) + '\', \'' + esc(i.uye_ad) + '\', \'' + esc(i.kitap) + '\')" title="Süre Uzat">' + uzatIcon + ' Süre Uzat</button>';
            actions += '<button class="btn btn-success btn-sm" onclick="openIadeModal(' + i.id + ')">' + iadeIcon + ' İade Al</button>';
        }
        actions += '</div>';

        return '<tr' + rowClass + '>' +
            '<td>' + uyeCell + '</td>' +
            '<td>' + bookCell + '</td>' +
            '<td>' + oduncCell + '</td>' +
            '<td>' + iadeCell + '</td>' +
            '<td>' + badge + '</td>' +
            '<td style="font-size:12px;color:var(--muted-foreground);max-width:100px;">' + esc(i.kutuphane || '—') + '</td>' +
            '<td>' + actions + '</td>' +
            '</tr>';
    }

    // ── Pagination ───────────────────────────────────────────────────────────
    function buildPagination(meta) {
        var container = document.getElementById('pagination');
        if (meta.last_page <= 1) { container.innerHTML = ''; return; }
        var cur = meta.current_page, last = meta.last_page;
        var html = '';

        html += '<button class="page-btn ' + (cur<=1?'disabled':'') + '" onclick="goPage(' + (cur-1) + ')">' +
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></button>';

        var pages = [];
        if (last <= 7) { for (var i=1;i<=last;i++) pages.push(i); }
        else {
            pages.push(1);
            if (cur>3) pages.push('…');
            for (var i=Math.max(2,cur-1);i<=Math.min(last-1,cur+1);i++) pages.push(i);
            if (cur<last-2) pages.push('…');
            pages.push(last);
        }
        pages.forEach(function(p) {
            if (p==='…') html += '<span class="page-ellipsis">…</span>';
            else html += '<button class="page-btn' + (p===cur?' active':'') + '" onclick="goPage(' + p + ')">' + p + '</button>';
        });

        html += '<button class="page-btn ' + (cur>=last?'disabled':'') + '" onclick="goPage(' + (cur+1) + ')">' +
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></button>';

        container.innerHTML = html;
    }

    function goPage(p) { if (p<1) return; state.page = p; fetchTable(); }

    // ── AJAX Fetch — AbortController ile race condition önlemi ───────────────
    function fetchTable(resetPage) {
        if (resetPage) state.page = 1;
        if (activeXhr) { activeXhr.abort(); }
        activeXhr = new AbortController();
        var ctrl = activeXhr;

        document.getElementById('tableVeil').classList.add('visible');

        var params = new URLSearchParams({
            search:          state.search,
            demirbasNo:      state.demirbasNo,
            kutuphaneId:     state.kutuphaneId,
            statu:           state.statu,
            tarih_baslangic: state.tarih_baslangic,
            tarih_bitis:     state.tarih_bitis,
            per_page:        state.per_page,
            page:            state.page,
        });

        fetch('/odunc/tablo?' + params.toString(), {
            signal:  ctrl.signal,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function(res) { if (!res.ok) throw new Error('HTTP ' + res.status); return res.json(); })
            .then(function(result) {
                if (ctrl.signal.aborted) return;
                activeXhr = null;
                document.getElementById('tableVeil').classList.remove('visible');

                if (!result.success) { showToast('error', 'Hata', 'Veriler yüklenemedi.'); return; }

                var rows = Array.isArray(result.data) ? result.data
                    : (result.data && Array.isArray(result.data.data) ? result.data.data : []);

                var tbody = document.getElementById('tableBody');
                if (rows.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state">' +
                        '<div class="empty-icon">' + bookIcon + '</div>' +
                        '<p class="empty-title">İşlem bulunamadı</p>' +
                        '<p class="empty-desc">Filtreleri değiştirin veya yeni ödünç işlemi başlatın.</p>' +
                        '</div></td></tr>';
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
                showToast('error', 'Bağlantı Hatası', 'Veriler yüklenemedi.');
            });
    }

    // ── Filtre olay dinleyiciler ─────────────────────────────────────────────
    function debounce(fn) {
        clearTimeout(fetchTimer);
        fetchTimer = setTimeout(fn, 400);
    }

    document.getElementById('searchInput').addEventListener('input', function() {
        state.search = this.value.trim();
        debounce(function() { fetchTable(true); });
    });
    document.getElementById('demirbasInput').addEventListener('input', function() {
        state.demirbasNo = this.value.trim();
        debounce(function() { fetchTable(true); });
    });
    document.getElementById('statuFilter').addEventListener('change', function() {
        clearTimeout(fetchTimer);
        state.statu = this.value;
        fetchTable(true);
    });
    document.getElementById('kutuphaneFilter').addEventListener('change', function() {
        clearTimeout(fetchTimer);
        state.kutuphaneId = this.value;
        fetchTable(true);
    });
    document.getElementById('tarihBaslangic').addEventListener('change', function() {
        clearTimeout(fetchTimer);
        state.tarih_baslangic = this.value;
        fetchTable(true);
    });
    document.getElementById('tarihBitis').addEventListener('change', function() {
        clearTimeout(fetchTimer);
        state.tarih_bitis = this.value;
        fetchTable(true);
    });
    document.getElementById('perPageSelect').addEventListener('change', function() {
        clearTimeout(fetchTimer);
        state.per_page = parseInt(this.value);
        fetchTable(true);
    });

    // ── Stat kartı tıklamaları artık sayfayı yenilemiyor,
    //    sadece statu state'ini güncelleyip AJAX yeniliyor ──────────────────
    document.querySelectorAll('.stat-card[data-statu]').forEach(function(card) {
        card.addEventListener('click', function(e) {
            e.preventDefault();
            var s = this.getAttribute('data-statu');
            state.statu = s;
            document.getElementById('statuFilter').value = s;
            document.querySelectorAll('.stat-card').forEach(function(c) { c.classList.remove('active-filter'); });
            this.classList.add('active-filter');
            fetchTable(true);
        });
    });

    // ── Excel Export ─────────────────────────────────────────────────────────
    document.getElementById('exportBtn').addEventListener('click', function() {
        var params = new URLSearchParams({
            search:          state.search,
            demirbasNo:      state.demirbasNo,
            kutuphaneId:     state.kutuphaneId,
            statu:           state.statu,
            tarih_baslangic: state.tarih_baslangic,
            tarih_bitis:     state.tarih_bitis,
        });
        var a = document.createElement('a');
        a.href = '/odunc/export?' + params.toString();
        a.download = '';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    });

    // ── Boot ─────────────────────────────────────────────────────────────────
    fetchTable();

    // ── İade Modal ───────────────────────────────────────────────────────────
    var currentIslemId = null;

    function openIadeModal(id) {
        currentIslemId = id;
        fetch('/odunc/' + id + '/iade-form', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
        })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                document.getElementById('iadeUye').textContent        = data.uye_ad + ' — ' + data.uye_tc;
                document.getElementById('iadeKitap').textContent      = data.kitap;
                document.getElementById('iadeOduncTarihi').textContent = data.odunc_tarihi;
                document.getElementById('iadePlanlanan').textContent   = data.iade_tarihi_planlanan;
                if (data.gecikiyor_mu) {
                    document.getElementById('iadeGecikmeRow').style.display = 'flex';
                    document.getElementById('iadeGecikme').textContent = data.gecikme_gun + ' gün gecikmiş';
                } else {
                    document.getElementById('iadeGecikmeRow').style.display = 'none';
                }
                document.getElementById('iade_tarihi_gercek').value = new Date().toISOString().slice(0,10);
                document.getElementById('iade_notu').value = '';
                document.querySelector('[name=statu][value=iade_edildi]').checked = true;
            });
        document.getElementById('iadeModal').classList.add('visible');
    }

    function closeIadeModal() {
        document.getElementById('iadeModal').classList.remove('visible');
        currentIslemId = null;
    }

    document.getElementById('iadeModal').addEventListener('click', function(e) {
        if (e.target === this) closeIadeModal();
    });

    function submitIade() {
        if (!currentIslemId) return;
        var btn = document.getElementById('iadeSubmitBtn');
        btn.disabled = true;
        btn.textContent = 'İşleniyor…';

        var formData = new FormData(document.getElementById('iadeForm'));
        fetch('/odunc/' + currentIslemId + '/iade', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
            body: formData
        })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    showToast('success', data.message);
                    closeIadeModal();
                    setTimeout(function() { fetchTable(); }, 700);
                } else {
                    showToast('error', data.message || 'Hata oluştu.');
                    btn.disabled = false;
                    btn.innerHTML = iadeIcon + ' İadeyi Tamamla';
                }
            })
            .catch(function() {
                showToast('error', 'Bağlantı hatası.');
                btn.disabled = false;
            });
    }

    // ── İade Tarihi Kısıtı ────────────────────────────────────────────────────
    (function() {
        var iadeDateInput = document.getElementById('iade_tarihi_gercek');
        iadeDateInput.addEventListener('change', function() {
            var today   = new Date(new Date().toDateString());
            var minDate = new Date(today); minDate.setDate(today.getDate() - 7);
            var chosen  = new Date(this.value + 'T00:00:00');
            if (chosen > today) this.value = today.toISOString().slice(0,10);
            if (chosen < minDate) this.value = minDate.toISOString().slice(0,10);
        });
    })();

    // ── Süre Uzat Modal ──────────────────────────────────────────────────────
    var currentUzatId        = null;
    var currentUzatPlanlanan = null; // 'DD.MM.YYYY' formatında

    function openUzatModal(id, planlananTarih, uyeAd, kitapAd) {
        currentUzatId        = id;
        currentUzatPlanlanan = planlananTarih;
        document.getElementById('uzatUye').textContent       = uyeAd || '—';
        document.getElementById('uzatKitap').textContent     = kitapAd || '—';
        document.getElementById('uzatMevcutTarih').textContent = planlananTarih;
        document.getElementById('uzatma_gun').value = '';
        document.getElementById('uzatSonucWrap').style.display = 'none';
        document.getElementById('uzatSubmitBtn').disabled = false;
        document.getElementById('uzatModal').classList.add('visible');
        setTimeout(function() { document.getElementById('uzatma_gun').focus(); }, 200);
    }

    function closeUzatModal() {
        document.getElementById('uzatModal').classList.remove('visible');
        currentUzatId = null; currentUzatPlanlanan = null;
    }

    document.getElementById('uzatModal').addEventListener('click', function(e) {
        if (e.target === this) closeUzatModal();
    });

    function hesaplaYeniTarih() {
        var gun = parseInt(document.getElementById('uzatma_gun').value);
        var wrap = document.getElementById('uzatSonucWrap');
        if (!currentUzatPlanlanan || isNaN(gun) || gun < 1 || gun > 15) { wrap.style.display = 'none'; return; }
        // DD.MM.YYYY → Date
        var parts = currentUzatPlanlanan.split('.');
        var base  = new Date(parseInt(parts[2]), parseInt(parts[1]) - 1, parseInt(parts[0]));
        base.setDate(base.getDate() + gun);
        var yeni = base.getDate().toString().padStart(2,'0') + '.'
            + (base.getMonth()+1).toString().padStart(2,'0') + '.'
            + base.getFullYear();
        document.getElementById('uzatYeniTarih').textContent = yeni;
        wrap.style.display = 'block';
    }

    function submitUzat() {
        if (!currentUzatId) return;
        var gun = parseInt(document.getElementById('uzatma_gun').value);
        if (isNaN(gun) || gun < 1 || gun > 15) {
            showToast('error', 'Geçersiz Gün', 'Lütfen 1 ile 15 arasında bir gün girin.');
            return;
        }
        var btn = document.getElementById('uzatSubmitBtn');
        btn.disabled = true;
        btn.textContent = 'İşleniyor…';

        var fd = new FormData();
        fd.append('uzatma_gun', gun);

        fetch('/odunc/' + currentUzatId + '/sure-uzat', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
            body: fd
        })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    showToast('success', data.message);
                    closeUzatModal();
                    setTimeout(function() { fetchTable(); }, 700);
                } else {
                    showToast('error', data.message || 'Hata oluştu.');
                    btn.disabled = false;
                    btn.textContent = 'Süreyi Uzat';
                }
            })
            .catch(function() {
                showToast('error', 'Bağlantı hatası.');
                btn.disabled = false;
                btn.textContent = 'Süreyi Uzat';
            });
    }
</script>
@endsection
