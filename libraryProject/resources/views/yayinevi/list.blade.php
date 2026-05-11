@extends('layouts.base')

@section('title', 'Yayınevleri')

@section('styles')
    <style>
        .page-header{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:14px}
        .page-title{font-family:var(--font-serif);font-size:22px;font-weight:700;color:var(--foreground)}
        .page-subtitle{font-size:13px;color:var(--muted-foreground);margin-top:2px}
        .search-bar{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap}
        .filter-field-ad{min-width:160px;flex:1;max-width:280px}
        .search-input-wrap{position:relative;flex:1;max-width:360px}
        .search-input-wrap svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--muted-foreground);pointer-events:none}
        .search-input{width:100%;padding:8px 12px 8px 34px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);color:var(--foreground);font-size:14px;outline:none;transition:border-color .15s,box-shadow .15s}
        .search-input:focus{border-color:var(--ring);box-shadow:0 0 0 2px rgba(122,92,60,.15)}
        .field-label{display:block;font-size:12px;font-weight:600;color:var(--muted-foreground);margin-bottom:6px}
        .field-input{width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);font-size:14px;outline:none}
        .field-input:focus{border-color:var(--ring);box-shadow:0 0 0 2px rgba(122,92,60,.12)}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:9px 16px;border-radius:calc(var(--radius) - 2px);font-size:14px;font-weight:500;cursor:pointer;border:none;text-decoration:none}
        .btn-primary{background:var(--primary);color:var(--primary-foreground)}
        .btn-outline{background:transparent;color:var(--foreground);border:1px solid var(--border)}
        .btn-sm{padding:6px 12px;font-size:13px}
        .filters-card{margin-bottom:14px}
        .table-card,.form-card{background:var(--card);border:1px solid rgba(217,208,194,.6);border-radius:var(--radius);overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.04)}
        .form-card-header{padding:14px 16px;border-bottom:1px solid rgba(217,208,194,.5)}
        .form-card-title{display:flex;align-items:center;gap:8px;font-size:16px;font-weight:700;margin:0}
        .form-card-body{padding:14px 16px}
        .table-card-header{padding:14px 16px;border-bottom:1px solid rgba(217,208,194,.5);display:flex;align-items:center;justify-content:space-between;gap:12px}
        .table-wrap{overflow-x:auto}
        table{width:100%;border-collapse:collapse}
        thead{background:var(--secondary)}
        th{padding:11px 16px;text-align:left;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--muted-foreground);white-space:nowrap;border-bottom:1px solid var(--border)}
        td{padding:12px 16px;font-size:14px;border-bottom:1px solid rgba(217,208,194,.4);vertical-align:middle}
        tr:last-child td{border-bottom:none}
        .table-footer{padding:12px 16px;display:flex;align-items:center;justify-content:space-between;border-top:1px solid rgba(217,208,194,.5);font-size:13px;color:var(--muted-foreground)}
        .tf-left{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
        .error-box{margin-bottom:12px;padding:10px 12px;background:rgba(197,48,48,.08);border:1px solid rgba(197,48,48,.35);border-radius:8px;color:#8a1d1d;font-size:13px}
        .toast-container{position:fixed;top:16px;right:16px;z-index:1000;display:flex;flex-direction:column;gap:8px}
        .toast{padding:14px 20px;border-radius:10px;font-size:14px;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,.15);animation:toast-in .3s ease;max-width:380px}
        .toast.success{background:#2f7d32;color:#fff}
        .toast.error{background:var(--destructive);color:#fff}
        @keyframes toast-in{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}
        @keyframes toast-out{from{transform:translateX(0);opacity:1}to{transform:translateX(100%);opacity:0}}
        .modal-backdrop{position:fixed;inset:0;background:rgba(20,16,12,.5);display:none;align-items:center;justify-content:center;z-index:1100;padding:16px}
        .modal-backdrop.open{display:flex}
        .modal{width:100%;max-width:560px;background:var(--card);border:1px solid var(--border);border-radius:12px;box-shadow:0 12px 40px rgba(0,0,0,.22)}
        .modal-header{padding:14px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
        .modal-title{font-weight:700}
        .modal-body{padding:16px;display:grid;gap:12px}
        .modal-footer{padding:14px 16px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:flex-end;gap:14px;flex-wrap:wrap}
        .modal-footer-audit{font-size:11px;line-height:1.5;color:var(--muted-foreground);flex:1;min-width:200px;text-align:left}
        .audit-row{display:block;margin-bottom:4px}
        .audit-row:last-child{margin-bottom:0}
        .audit-key{font-weight:600;color:var(--muted-foreground);margin-right:6px}
        .modal-footer-actions{display:flex;justify-content:flex-end;gap:8px;flex-shrink:0;margin-left:auto}
        .btn-icon{width:30px;height:30px;border:none;border-radius:8px;background:transparent;cursor:pointer;color:var(--muted-foreground)}
        .btn-icon:hover{background:var(--muted);color:var(--foreground)}
        .table-loading-wrapper{position:relative}
        .table-loading-overlay{position:absolute;inset:0;background:rgba(255,255,255,.6);display:none;align-items:center;justify-content:center;z-index:2}
        .table-loading-overlay.show{display:flex}
        .spinner{width:28px;height:28px;border-radius:50%;border:3px solid rgba(122,92,60,.18);border-top-color:var(--primary);animation:spin 1s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}}
        .pagination-nav{display:flex;gap:6px;align-items:center}
        .pagination-btn{padding:6px 10px;border:1px solid var(--border);border-radius:8px;background:#fff;cursor:pointer;font-size:13px}
        .pagination-btn[disabled]{opacity:.45;cursor:default}
        .pagination-btn.active{background:var(--primary);color:var(--primary-foreground);border-color:var(--primary)}
        th.sortable-th{cursor:pointer;user-select:none}
        th.sortable-th:hover{color:var(--foreground)}
        th.sortable-th .sort-label{display:inline-flex;align-items:center;gap:6px}
        th.sortable-th .sort-caret{opacity:.35;font-size:10px;line-height:1}
        th.sortable-th.sort-active .sort-caret{opacity:1}
        .btn-outline.btn-sm.btn-danger-muted{color:var(--destructive);border-color:rgba(197,48,48,.45)}
        .btn-outline.btn-sm.btn-danger-muted:hover{background:rgba(197,48,48,.08)}
        .btn-with-spinner{display:inline-flex;align-items:center;justify-content:center;min-width:110px}
        .btn-with-spinner .btn-spinner{display:none;width:16px;height:16px;border-radius:50%;border:2px solid rgba(245,240,232,.35);border-top-color:var(--primary-foreground);animation:spin .7s linear infinite;flex-shrink:0}
        .btn-with-spinner.is-loading .btn-spinner{display:inline-block;margin-right:10px}
        .btn-with-spinner.is-loading{pointer-events:none;opacity:.9}
        .page-header-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
        .field-help{display:block;font-size:12px;color:var(--muted-foreground);margin-top:4px}
        .merge-combobox{position:relative}
        .merge-combobox-input{padding-right:34px}
        .merge-combobox-toggle{position:absolute;right:8px;top:50%;transform:translateY(-50%);border:none;background:transparent;cursor:pointer;color:var(--muted-foreground);padding:4px}
        .merge-combobox-dropdown{position:absolute;left:0;right:0;top:calc(100% + 6px);background:var(--card);border:1px solid var(--border);border-radius:8px;box-shadow:0 12px 30px rgba(0,0,0,.12);max-height:220px;overflow:auto;display:none;z-index:20}
        .merge-combobox.open .merge-combobox-dropdown{display:block}
        .merge-combobox-item{padding:8px 10px;font-size:14px;cursor:pointer}
        .merge-combobox-item:hover,.merge-combobox-item.active{background:var(--secondary)}
        .merge-combobox-empty{padding:8px 10px;font-size:13px;color:var(--muted-foreground)}
        .merge-add-row{display:flex;gap:8px;align-items:flex-start}
        .merge-add-row .merge-combobox{flex:1}
        .merge-selected-list{display:flex;flex-wrap:wrap;gap:8px;padding:8px;border:1px dashed var(--border);border-radius:8px;min-height:46px;background:rgba(245,240,232,.45)}
        .merge-chip{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;background:var(--secondary);font-size:13px}
        .merge-chip-x{border:none;background:transparent;cursor:pointer;color:var(--muted-foreground);font-size:14px;line-height:1}
        .merge-preview{margin-top:8px;font-size:12px;color:var(--muted-foreground)}
    </style>
@endsection

@section('breadcrumb')
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('katalog.index') }}" class="breadcrumb-link">Ana Sayfa</a>
        <span class="breadcrumb-sep">/</span>
        <span class="breadcrumb-current">Yayınevleri</span>
    </nav>
@endsection

@section('content')
    <div class="toast-container" id="toastContainer"></div>
    <div class="content-area">
        <div class="page-header">
            <div>
                <h1 class="page-title">Yayınevleri</h1>
                <p class="page-subtitle" id="yayineviPageSubtitle">Sistemde kayıtlı {{ $yayinevleri->total() }} yayınevi</p>
            </div>
            @if($canManageYayinevleri)
                <div class="page-header-actions">
                    <button type="button" class="btn btn-outline" id="openMergeYayineviModal">Birleştir</button>
                    <button type="button" class="btn btn-primary" id="openNewYayineviModal">Yeni Yayınevi</button>
                </div>
            @endif
        </div>

        @if($errors->any())
            <div class="error-box">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="form-card filters-card">
            <div class="form-card-header">
                <h2 class="form-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    Filtrele
                </h2>
            </div>
            <div class="form-card-body">
                <div class="search-bar">
                    <div class="filter-field-ad">
                        <label class="field-label" for="filterAdInput">Ad</label>
                        <input type="text" class="field-input" id="filterAdInput" name="filter_ad" placeholder="Ada göre..." autocomplete="off" value="{{ $activeFilterAd ?? request('filter_ad') }}" />
                    </div>
                    <div>
                        <label class="field-label" for="eserDurumu">Eser Durumu</label>
                        <select class="field-input" id="eserDurumu" name="eser_durumu" style="min-width:220px;">
                            <option value="tum" {{ ($activeEserDurumu ?? request('eser_durumu', 'tum')) === 'tum' ? 'selected' : '' }}>Tüm Yayınevleri</option>
                            <option value="var" {{ ($activeEserDurumu ?? request('eser_durumu')) === 'var' ? 'selected' : '' }}>Eseri Olanlar</option>
                            <option value="yok" {{ ($activeEserDurumu ?? request('eser_durumu')) === 'yok' ? 'selected' : '' }}>Eseri Olmayanlar</option>
                        </select>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline" id="clearFiltersBtn">Filtreyi Temizle</button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" id="applyFiltersBtn">Ara</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-card-header">
                <div>
                    <h2 class="form-card-title" style="margin:0;">Yayınevi Listesi</h2>
                </div>
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
                            <th class="sortable-th" data-sort="ad" id="thSortAd" title="Sıralamak için tıklayın">
                                <span class="sort-label">Ad</span><span class="sort-caret" data-caret-for="ad" aria-hidden="true">◇</span>
                            </th>
                            <th class="sortable-th" data-sort="eser_sayisi" id="thSortEserSayisi" title="Sıralamak için tıklayın">
                                <span class="sort-label">Eser Sayısı</span><span class="sort-caret" data-caret-for="eser_sayisi" aria-hidden="true">◇</span>
                            </th>
                            @if($canManageYayinevleri)<th>İşlem</th>@endif
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse($yayinevleri as $yayinevi)
                            @php($canDeleteYayinevi = $canManageYayinevleri && (int) ($yayinevi->eser_sayisi ?? 0) === 0)
                            <tr>
                                <td>{{ $yayinevi->ad }}</td>
                                <td><strong>{{ $yayinevi->eser_sayisi }}</strong></td>
                                @if($canManageYayinevleri)
                                    <td>
                                        <div style="display:flex;flex-wrap:wrap;gap:6px;align-items:center;">
                                        <button
                                            type="button"
                                            class="btn btn-outline btn-sm openEditYayineviModal"
                                            data-id="{{ $yayinevi->id }}"
                                            data-ad="{{ $yayinevi->ad }}"
                                            data-kayit-tarihi="{{ $yayinevi->created_at?->format('d.m.Y H:i') ?? '—' }}"
                                            data-kaydeden="{{ filled($yayinevi->olusturan?->name) ? $yayinevi->olusturan->name : '—' }}"
                                            data-guncelleme-tarihi="{{ $yayinevi->updated_at?->format('d.m.Y H:i') ?? '—' }}"
                                            data-guncelleyen="{{ filled($yayinevi->guncelleyen?->name) ? $yayinevi->guncelleyen->name : '—' }}"
                                        >
                                            Düzenle
                                        </button>
                                        @if($canDeleteYayinevi)
                                            <button type="button" class="btn btn-outline btn-sm btn-danger-muted delete-yayinevi-btn" data-id="{{ $yayinevi->id }}" title="Sil">Sil</button>
                                        @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canManageYayinevleri ? 3 : 2 }}" style="color:var(--muted-foreground);">Kayıt bulunamadı.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            </div>
            <div class="table-footer">
                <div class="tf-left">
                    <span id="paginationInfo">{{ $yayinevleri->firstItem() ?? 0 }}–{{ $yayinevleri->lastItem() ?? 0 }} / {{ $yayinevleri->total() }} kayıt</span>
                    <label for="perPageSelectFooter">Sayfa başına:</label>
                    <select class="field-input" id="perPageSelectFooter" style="width:95px;padding:6px 8px;">
                        <option value="10" {{ (int) ($perPage ?? 20) === 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ (int) ($perPage ?? 20) === 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ (int) ($perPage ?? 20) === 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ (int) ($perPage ?? 20) === 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <nav class="pagination-nav" id="paginationNav"></nav>
            </div>
        </div>
    </div>

    @if($canManageYayinevleri)
        <div class="modal-backdrop" id="newYayineviModal">
            <div class="modal">
                <div class="modal-header">
                    <div class="modal-title">Yeni Yayınevi</div>
                    <button class="btn-icon" type="button" data-close-modal="#newYayineviModal">✕</button>
                </div>
                <form method="POST" action="{{ route('yayinevleri.store') }}" id="newYayineviForm">
                    @csrf
                    <input type="hidden" name="_modal" value="new">
                    <div class="modal-body">
                        <div>
                            <label class="field-label" for="newYayineviAd">Yayınevi adı</label>
                            <input class="field-input" id="newYayineviAd" name="ad" value="{{ old('_modal') === 'new' ? old('ad') : '' }}" required />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="modal-footer-actions" style="margin-left:auto;">
                            <button type="button" class="btn btn-outline" data-close-modal="#newYayineviModal">Vazgeç</button>
                            <button type="submit" class="btn btn-primary btn-with-spinner" id="newYayineviSubmit"><span class="btn-spinner" aria-hidden="true"></span><span class="btn-label">Kaydet</span></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal-backdrop" id="editYayineviModal">
            <div class="modal">
                <div class="modal-header">
                    <div class="modal-title">Yayınevi Düzenle</div>
                    <button class="btn-icon" type="button" data-close-modal="#editYayineviModal">✕</button>
                </div>
                <form method="POST" id="editYayineviForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_modal" value="edit">
                    <input type="hidden" name="_yayinevi_id" id="editYayineviId" value="{{ old('_yayinevi_id') }}">
                    <div class="modal-body">
                        <div>
                            <label class="field-label" for="editYayineviAd">Yayınevi adı</label>
                            <input class="field-input" id="editYayineviAd" name="ad" value="{{ old('_modal') === 'edit' ? old('ad') : '' }}" required />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="modal-footer-audit" id="editYayineviAuditBlock" aria-label="Kayıt bilgisi">
                            <div class="audit-row"><span class="audit-key">Kayıt tarihi:</span> <span id="editYayineviMetaKayitTarihi">—</span></div>
                            <div class="audit-row"><span class="audit-key">Kaydeden:</span> <span id="editYayineviMetaKaydeden">—</span></div>
                            <div class="audit-row"><span class="audit-key">Güncelleme tarihi:</span> <span id="editYayineviMetaGuncelleme">—</span></div>
                            <div class="audit-row"><span class="audit-key">Güncelleyen:</span> <span id="editYayineviMetaGuncelleyen">—</span></div>
                        </div>
                        <div class="modal-footer-actions">
                            <button type="button" class="btn btn-outline" data-close-modal="#editYayineviModal">Vazgeç</button>
                            <button type="submit" class="btn btn-primary btn-with-spinner" id="editYayineviSubmit"><span class="btn-spinner" aria-hidden="true"></span><span class="btn-label">Güncelle</span></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal-backdrop" id="mergeYayineviModal">
            <div class="modal">
                <div class="modal-header">
                    <div class="modal-title">Yayınevi Birleştir</div>
                    <button class="btn-icon" type="button" data-close-modal="#mergeYayineviModal">✕</button>
                </div>
                <form id="mergeYayineviForm">
                    @csrf
                    <div class="modal-body">
                        <div>
                            <label class="field-label" for="mergeMainYayinevi">Asıl Yayınevi</label>
                            <div class="merge-combobox" id="mergeMainCombobox">
                                <input class="field-input merge-combobox-input" type="text" id="mergeMainSearch" placeholder="Asıl yayınevi ara..." autocomplete="off" />
                                <button type="button" class="merge-combobox-toggle" id="mergeMainToggle" tabindex="-1">▾</button>
                                <div class="merge-combobox-dropdown" id="mergeMainDropdown"></div>
                            </div>
                            <input type="hidden" id="mergeMainYayinevi" name="main_yayinevi_id" required />
                            <small class="field-help">Birleştirme işleminden sonra açık kalacak yayınevini seçin.</small>
                        </div>
                        <div>
                            <label class="field-label">Aktarılacak Yayınevleri</label>
                            <div class="merge-add-row" style="margin-bottom:8px;">
                                <div class="merge-combobox" id="mergeOtherCombobox">
                                    <input class="field-input merge-combobox-input" type="text" id="mergeOtherSearch" placeholder="Aktarılacak yayınevi ara..." autocomplete="off" />
                                    <button type="button" class="merge-combobox-toggle" id="mergeOtherToggle" tabindex="-1">▾</button>
                                    <div class="merge-combobox-dropdown" id="mergeOtherDropdown"></div>
                                </div>
                                <button type="button" class="btn btn-outline" id="mergeAddPublisherBtn">Ekle</button>
                            </div>
                            <div class="merge-selected-list" id="mergeSelectedList"></div>
                            <div class="merge-preview" id="mergePreviewInfo">Toplam taşınacak eser: 0</div>
                            <small class="field-help">Birleştirme işleminden sonra silinecek yayınevlerini seçin.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="modal-footer-actions" style="margin-left:auto;">
                            <button type="button" class="btn btn-outline" data-close-modal="#mergeYayineviModal">Vazgeç</button>
                            <button type="submit" class="btn btn-primary btn-with-spinner" id="mergeYayineviSubmit"><span class="btn-spinner" aria-hidden="true"></span><span class="btn-label">Birleştir</span></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
<script>
    var yayineviAjaxUrl = @json(route('yayinevleri.index'));
    var yayineviExportUrl = @json(route('yayinevleri.export'));
    var yayineviCanManage = @json($canManageYayinevleri);
    var currentPage = {{ (int) $yayinevleri->currentPage() }};
    var reqCounter = 0;
    var sortBy = @json($activeSortBy ?? '');
    var sortDir = @json(($activeSortBy ?? '') !== '' ? ($activeSortDir ?? 'asc') : 'asc');
    var csrfToken = @json(csrf_token());
    var yayineviApiBase = @json(url('/yayinevleri'));
    var yayineviMergeUrl = @json(route('yayinevleri.merge'));

    function showToast(type, title) {
        var c = document.getElementById('toastContainer');
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<div>' + title + '</div>';
        c.appendChild(t);
        setTimeout(function () {
            t.style.animation = 'toast-out 0.3s ease forwards';
            setTimeout(function () { if (t.parentNode) t.parentNode.removeChild(t); }, 300);
        }, 3000);
    }

    @if(session('success'))
    showToast('success', @json(session('success')));
    @endif

    function getFilters() {
        return {
            filter_ad: document.getElementById('filterAdInput').value.trim(),
            eser_durumu: document.getElementById('eserDurumu').value,
            per_page: document.getElementById('perPageSelectFooter').value,
            sort_by: sortBy,
            sort_dir: sortDir
        };
    }

    function updateSortHeaderDisplay() {
        document.querySelectorAll('th.sortable-th').forEach(function(th) {
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

    function escapeHtml(str) {
        return (str || '').replace(/[&<>"']/g, function(m) { return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]; });
    }

    function buildRow(row) {
        var editCol = '';
        if (yayineviCanManage) {
            var sil = row.can_delete
                ? '<button type="button" class="btn btn-outline btn-sm btn-danger-muted delete-yayinevi-btn" data-id="' + row.id + '" title="Sil">Sil</button>'
                : '';
            editCol = '<td><div style="display:flex;flex-wrap:wrap;gap:6px;align-items:center;"><button type="button" class="btn btn-outline btn-sm openEditYayineviModal"'
                + ' data-id="' + row.id + '"'
                + ' data-ad="' + escapeHtml(row.ad) + '"'
                + ' data-kayit-tarihi="' + escapeHtml(row.kayit_tarihi || '—') + '"'
                + ' data-kaydeden="' + escapeHtml(row.kaydeden || '—') + '"'
                + ' data-guncelleme-tarihi="' + escapeHtml(row.guncelleme_tarihi || '—') + '"'
                + ' data-guncelleyen="' + escapeHtml(row.guncelleyen || '—') + '"'
                + '>Düzenle</button>' + sil + '</div></td>';
        }
        return '<tr>'
            + '<td>' + escapeHtml(row.ad) + '</td>'
            + '<td><strong>' + row.eser_sayisi + '</strong></td>'
            + editCol
            + '</tr>';
    }

    function renderTable(data) {
        var tbody = document.getElementById('tableBody');
        var rows = data.rows || [];
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="' + (yayineviCanManage ? 3 : 2) + '" style="color:var(--muted-foreground);">Kayıt bulunamadı.</td></tr>';
        } else {
            tbody.innerHTML = rows.map(buildRow).join('');
        }

        var meta = data.meta || {};
        document.getElementById('paginationInfo').textContent = (meta.from || 0) + '–' + (meta.to || 0) + ' / ' + (meta.total || 0) + ' kayıt';
        var sub = document.getElementById('yayineviPageSubtitle');
        if (sub && typeof meta.total !== 'undefined' && meta.total !== null) {
            sub.textContent = 'Sistemde kayıtlı ' + meta.total + ' yayınevi';
        }
        if (meta.sort_by) {
            sortBy = meta.sort_by;
            sortDir = meta.sort_dir || 'asc';
        } else {
            sortBy = '';
            sortDir = 'asc';
        }
        updateSortHeaderDisplay();
        renderPagination(meta);
        bindEditButtons();
    }

    function renderPagination(meta) {
        var nav = document.getElementById('paginationNav');
        var cp = meta.current_page || 1;
        var lp = meta.last_page || 1;
        var html = '';
        html += '<button class="pagination-btn" data-page="' + (cp - 1) + '"' + (cp <= 1 ? ' disabled' : '') + '>‹ Önceki</button>';
        var start = Math.max(1, cp - 2);
        var end = Math.min(lp, cp + 2);
        for (var i = start; i <= end; i++) {
            html += '<button class="pagination-btn' + (i === cp ? ' active' : '') + '" data-page="' + i + '">' + i + '</button>';
        }
        html += '<button class="pagination-btn" data-page="' + (cp + 1) + '"' + (cp >= lp ? ' disabled' : '') + '>Sonraki ›</button>';
        nav.innerHTML = html;
        nav.querySelectorAll('[data-page]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (this.hasAttribute('disabled')) return;
                fetchTable(parseInt(this.getAttribute('data-page'), 10) || 1);
            });
        });
    }

    function fetchTable(page) {
        currentPage = page || 1;
        var token = ++reqCounter;
        var filters = getFilters();
        var params = new URLSearchParams();
        if (filters.filter_ad) params.set('filter_ad', filters.filter_ad);
        if (filters.eser_durumu && filters.eser_durumu !== 'tum') params.set('eser_durumu', filters.eser_durumu);
        if (filters.per_page) params.set('per_page', filters.per_page);
        if (filters.sort_by) {
            params.set('sort_by', filters.sort_by);
            params.set('sort_dir', filters.sort_dir || 'asc');
        }
        params.set('page', currentPage);

        document.getElementById('tableLoading').classList.add('show');
        fetch(yayineviAjaxUrl + '?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (token !== reqCounter) return;
            renderTable(data);
            var url = new URL(window.location.href);
            ['search','filter_ad','eser_durumu','per_page','page','sort_by','sort_dir'].forEach(function(k){ url.searchParams.delete(k); });
            if (filters.filter_ad) url.searchParams.set('filter_ad', filters.filter_ad);
            if (filters.eser_durumu && filters.eser_durumu !== 'tum') url.searchParams.set('eser_durumu', filters.eser_durumu);
            if (filters.per_page) url.searchParams.set('per_page', filters.per_page);
            if (filters.sort_by) {
                url.searchParams.set('sort_by', filters.sort_by);
                url.searchParams.set('sort_dir', filters.sort_dir || 'asc');
            }
            if (currentPage > 1) url.searchParams.set('page', String(currentPage));
            window.history.replaceState({}, '', url.toString());
        })
        .catch(function() { showToast('error', 'Liste güncellenemedi'); })
        .finally(function() { document.getElementById('tableLoading').classList.remove('show'); });
    }

    document.getElementById('applyFiltersBtn').addEventListener('click', function() { fetchTable(1); });
    document.getElementById('filterAdInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); fetchTable(1); }
    });
    document.getElementById('eserDurumu').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); fetchTable(1); }
    });
    document.getElementById('perPageSelectFooter').addEventListener('change', function() { fetchTable(1); });
    document.getElementById('clearFiltersBtn').addEventListener('click', function() {
        document.getElementById('filterAdInput').value = '';
        document.getElementById('eserDurumu').value = 'tum';
        document.getElementById('perPageSelectFooter').value = '20';
        sortBy = '';
        sortDir = 'asc';
        updateSortHeaderDisplay();
        fetchTable(1);
    });
    document.getElementById('exportExcelBtn').addEventListener('click', function() {
        var f = getFilters();
        var p = new URLSearchParams();
        if (f.filter_ad) p.set('filter_ad', f.filter_ad);
        if (f.eser_durumu && f.eser_durumu !== 'tum') p.set('eser_durumu', f.eser_durumu);
        if (f.sort_by) {
            p.set('sort_by', f.sort_by);
            p.set('sort_dir', f.sort_dir || 'asc');
        }
        window.location.href = yayineviExportUrl + (p.toString() ? ('?' + p.toString()) : '');
    });

    document.querySelectorAll('th.sortable-th').forEach(function(th) {
        th.addEventListener('click', function() {
            var col = this.getAttribute('data-sort');
            if (!col) return;
            if (sortBy === col) {
                sortDir = sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                sortBy = col;
                sortDir = 'asc';
            }
            updateSortHeaderDisplay();
            fetchTable(1);
        });
    });
    updateSortHeaderDisplay();

    document.getElementById('tableBody').addEventListener('click', function(e) {
        var delBtn = e.target.closest('.delete-yayinevi-btn');
        if (!delBtn) return;
        e.preventDefault();
        var id = delBtn.getAttribute('data-id');
        if (!id || !confirm('Bu yayınevini kalıcı olarak silmek istediğinize emin misiniz?')) return;
        delBtn.disabled = true;
        fetch(yayineviApiBase + '/' + id, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'same-origin'
        })
        .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, body: j }; }).catch(function() { return { ok: false, body: { message: 'Yanıt okunamadı.' } }; }); })
        .then(function(res) {
            if (res.ok) {
                showToast('success', (res.body && res.body.message) ? res.body.message : 'Yayınevi silindi.');
                fetchTable(currentPage);
                return;
            }
            showToast('error', firstValidationMessage(res.body));
        })
        .catch(function() { showToast('error', 'Bağlantı hatası.'); })
        .finally(function() { delBtn.disabled = false; });
    });

    function bindEditButtons() {
        if (!window.__openEditYayineviModalHandler) return;
        document.querySelectorAll('.openEditYayineviModal').forEach(function(btn) {
            btn.onclick = window.__openEditYayineviModalHandler;
        });
    }

    function firstValidationMessage(body) {
        if (!body) return 'İşlem yapılamadı.';
        if (body.errors) {
            var e = body.errors;
            for (var k in e) {
                if (e[k] && e[k][0]) return e[k][0];
            }
        }
        if (body.message && typeof body.message === 'string') return body.message;
        return 'İşlem yapılamadı.';
    }

    function setSubmitButtonLoading(btn, loading) {
        if (!btn) return;
        btn.disabled = loading;
        btn.classList.toggle('is-loading', loading);
    }

    function submitModalFormAjax(form, onSuccess) {
        var submitBtn = form.querySelector('button[type="submit"]');
        var fd = new FormData(form);
        setSubmitButtonLoading(submitBtn, true);
        fetch(form.getAttribute('action'), {
            method: 'POST',
            body: fd,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(function(r) {
            return r.json().then(function(j) {
                return { ok: r.ok, body: j };
            }).catch(function() {
                return { ok: false, body: { message: 'Sunucu yanıtı okunamadı.' } };
            });
        })
        .then(function(res) {
            if (res.ok) {
                showToast('success', (res.body && res.body.message) ? res.body.message : 'Kaydedildi.');
                if (onSuccess) onSuccess(res.body);
                return;
            }
            showToast('error', firstValidationMessage(res.body));
        })
        .catch(function() {
            showToast('error', 'Bağlantı hatası.');
        })
        .finally(function() {
            setSubmitButtonLoading(submitBtn, false);
        });
    }

    @if($canManageYayinevleri)
    var newYayineviModal = document.getElementById('newYayineviModal');
    var editYayineviModal = document.getElementById('editYayineviModal');
    var mergeYayineviModal = document.getElementById('mergeYayineviModal');
    var editYayineviForm = document.getElementById('editYayineviForm');
    var mergeYayineviForm = document.getElementById('mergeYayineviForm');
    var editYayineviAd = document.getElementById('editYayineviAd');
    var editYayineviId = document.getElementById('editYayineviId');
    var mergeMainYayineviInput = document.getElementById('mergeMainYayinevi');
    var mergeMainSearch = document.getElementById('mergeMainSearch');
    var mergeMainCombobox = document.getElementById('mergeMainCombobox');
    var mergeMainDropdown = document.getElementById('mergeMainDropdown');
    var mergeMainToggle = document.getElementById('mergeMainToggle');
    var mergeOtherSearch = document.getElementById('mergeOtherSearch');
    var mergeOtherCombobox = document.getElementById('mergeOtherCombobox');
    var mergeOtherDropdown = document.getElementById('mergeOtherDropdown');
    var mergeOtherToggle = document.getElementById('mergeOtherToggle');
    var mergeAddPublisherBtn = document.getElementById('mergeAddPublisherBtn');
    var mergeSelectedList = document.getElementById('mergeSelectedList');
    var mergePreviewInfo = document.getElementById('mergePreviewInfo');
    var mergePublisherOptions = @json($mergeYayineviOptions ?? []);
    var mergeSelectedMain = null;
    var mergeCandidateOther = null;
    var mergeSelectedOthers = [];
    var updateRouteBase = @json(url('/yayinevleri'));

    function openModal(modal) { if (modal) modal.classList.add('open'); }
    function closeModal(modal) { if (modal) modal.classList.remove('open'); }
    function setEditYayineviAuditFromBtn(btn) {
        if (!btn) return;
        var elK = document.getElementById('editYayineviMetaKayitTarihi');
        var elKd = document.getElementById('editYayineviMetaKaydeden');
        var elG = document.getElementById('editYayineviMetaGuncelleme');
        var elGy = document.getElementById('editYayineviMetaGuncelleyen');
        if (elK) elK.textContent = btn.getAttribute('data-kayit-tarihi') || '—';
        if (elKd) elKd.textContent = btn.getAttribute('data-kaydeden') || '—';
        if (elG) elG.textContent = btn.getAttribute('data-guncelleme-tarihi') || '—';
        if (elGy) elGy.textContent = btn.getAttribute('data-guncelleyen') || '—';
    }
    function populateEditModalByRow(id) {
        var rowBtn = document.querySelector('.openEditYayineviModal[data-id="' + id + '"]');
        if (!rowBtn) return;
        editYayineviId.value = id;
        editYayineviAd.value = rowBtn.getAttribute('data-ad') || '';
        editYayineviForm.action = updateRouteBase + '/' + id;
        setEditYayineviAuditFromBtn(rowBtn);
    }

    document.getElementById('openNewYayineviModal').addEventListener('click', function() {
        openModal(newYayineviModal);
    });
    document.getElementById('openMergeYayineviModal').addEventListener('click', function() {
        resetMergeSelections();
        openModal(mergeYayineviModal);
    });

    function getPublisherById(id) {
        var intId = parseInt(id, 10);
        for (var i = 0; i < mergePublisherOptions.length; i++) {
            if (parseInt(mergePublisherOptions[i].id, 10) === intId) return mergePublisherOptions[i];
        }
        return null;
    }

    function renderMergeSelectedList() {
        if (!mergeSelectedList) return;
        var totalEser = 0;
        if (!mergeSelectedOthers.length) {
            mergeSelectedList.innerHTML = '<span style="font-size:13px;color:var(--muted-foreground);">Henüz yayınevi eklenmedi.</span>';
            if (mergePreviewInfo) mergePreviewInfo.textContent = 'Toplam taşınacak eser: 0';
            return;
        }
        mergeSelectedList.innerHTML = mergeSelectedOthers.map(function(id) {
            var a = getPublisherById(id);
            var ad = a ? a.ad : ('Yayınevi #' + id);
            var eser = a ? (parseInt(a.eser_sayisi, 10) || 0) : 0;
            totalEser += eser;
            return '<span class="merge-chip">' + escapeHtml(ad) + ' (' + eser + ' eser) <button type="button" class="merge-chip-x" data-remove-id="' + id + '" title="Kaldır">✕</button></span>';
        }).join('');
        if (mergePreviewInfo) mergePreviewInfo.textContent = 'Toplam taşınacak eser: ' + totalEser;
        mergeSelectedList.querySelectorAll('[data-remove-id]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = parseInt(this.getAttribute('data-remove-id'), 10);
                mergeSelectedOthers = mergeSelectedOthers.filter(function(v){ return v !== id; });
                renderMergeSelectedList();
            });
        });
    }

    function openDropdown(box) { if (box) box.classList.add('open'); }
    function closeDropdown(box) { if (box) box.classList.remove('open'); }

    function renderMainDropdown() {
        if (!mergeMainDropdown) return;
        var q = (mergeMainSearch.value || '').toLocaleLowerCase('tr-TR').trim();
        var rows = mergePublisherOptions.filter(function(a) {
            var txt = (a.ad || '').toLocaleLowerCase('tr-TR');
            return q === '' || txt.indexOf(q) >= 0;
        });
        if (!rows.length) {
            mergeMainDropdown.innerHTML = '<div class="merge-combobox-empty">Sonuç bulunamadı</div>';
            return;
        }
        mergeMainDropdown.innerHTML = rows.map(function(a){
            return '<div class="merge-combobox-item" data-main-id="' + a.id + '">' + escapeHtml(a.ad) + '</div>';
        }).join('');
        mergeMainDropdown.querySelectorAll('[data-main-id]').forEach(function(item){
            item.addEventListener('mousedown', function(e){
                e.preventDefault();
                var id = parseInt(this.getAttribute('data-main-id'), 10);
                var a = getPublisherById(id);
                mergeSelectedMain = id;
                mergeMainYayineviInput.value = String(id);
                mergeMainSearch.value = a ? a.ad : '';
                mergeSelectedOthers = mergeSelectedOthers.filter(function(v){ return v !== id; });
                renderMergeSelectedList();
                closeDropdown(mergeMainCombobox);
            });
        });
    }

    function renderOtherDropdown() {
        if (!mergeOtherDropdown) return;
        var q = (mergeOtherSearch.value || '').toLocaleLowerCase('tr-TR').trim();
        var rows = mergePublisherOptions.filter(function(a) {
            var id = parseInt(a.id, 10);
            if (mergeSelectedMain && id === mergeSelectedMain) return false;
            if (mergeSelectedOthers.indexOf(id) >= 0) return false;
            var txt = (a.ad || '').toLocaleLowerCase('tr-TR');
            return q === '' || txt.indexOf(q) >= 0;
        });
        if (!rows.length) {
            mergeOtherDropdown.innerHTML = '<div class="merge-combobox-empty">Sonuç bulunamadı</div>';
            mergeCandidateOther = null;
            return;
        }
        mergeOtherDropdown.innerHTML = rows.map(function(a){
            return '<div class="merge-combobox-item" data-other-id="' + a.id + '">' + escapeHtml(a.ad) + '</div>';
        }).join('');
        mergeOtherDropdown.querySelectorAll('[data-other-id]').forEach(function(item){
            item.addEventListener('mousedown', function(e){
                e.preventDefault();
                var id = parseInt(this.getAttribute('data-other-id'), 10);
                var a = getPublisherById(id);
                mergeCandidateOther = id;
                mergeOtherSearch.value = a ? a.ad : '';
                closeDropdown(mergeOtherCombobox);
            });
        });
    }

    function addMergeOtherPublisher() {
        var id = mergeCandidateOther || null;
        if (!id) { showToast('error', 'Eklenecek yayınevini seçin.'); return; }
        if (mergeSelectedMain && id === mergeSelectedMain) { showToast('error', 'Asıl yayınevi aktarılacak listesine eklenemez.'); return; }
        if (mergeSelectedOthers.indexOf(id) >= 0) { showToast('error', 'Bu yayınevi zaten eklendi.'); return; }
        mergeSelectedOthers.push(id);
        mergeCandidateOther = null;
        mergeOtherSearch.value = '';
        renderMergeSelectedList();
        renderOtherDropdown();
    }

    function resetMergeSelections() {
        mergeSelectedMain = null;
        mergeMainYayineviInput.value = '';
        mergeMainSearch.value = '';
        mergeSelectedOthers = [];
        mergeCandidateOther = null;
        mergeOtherSearch.value = '';
        renderMergeSelectedList();
        renderMainDropdown();
        renderOtherDropdown();
    }

    if (mergeMainSearch) {
        mergeMainSearch.addEventListener('focus', function(){ renderMainDropdown(); openDropdown(mergeMainCombobox); });
        mergeMainSearch.addEventListener('input', function(){ mergeSelectedMain = null; mergeMainYayineviInput.value = ''; renderMainDropdown(); openDropdown(mergeMainCombobox); });
    }
    if (mergeMainToggle) {
        mergeMainToggle.addEventListener('mousedown', function(e){ e.preventDefault(); renderMainDropdown(); mergeMainCombobox.classList.contains('open') ? closeDropdown(mergeMainCombobox) : openDropdown(mergeMainCombobox); });
    }
    if (mergeOtherSearch) {
        mergeOtherSearch.addEventListener('focus', function(){ renderOtherDropdown(); openDropdown(mergeOtherCombobox); });
        mergeOtherSearch.addEventListener('input', function(){ mergeCandidateOther = null; renderOtherDropdown(); openDropdown(mergeOtherCombobox); });
        mergeOtherSearch.addEventListener('keydown', function(e){ if (e.key === 'Enter') { e.preventDefault(); addMergeOtherPublisher(); } });
    }
    if (mergeOtherToggle) {
        mergeOtherToggle.addEventListener('mousedown', function(e){ e.preventDefault(); renderOtherDropdown(); mergeOtherCombobox.classList.contains('open') ? closeDropdown(mergeOtherCombobox) : openDropdown(mergeOtherCombobox); });
    }
    if (mergeAddPublisherBtn) {
        mergeAddPublisherBtn.addEventListener('click', addMergeOtherPublisher);
    }
    document.addEventListener('click', function(e){
        if (mergeMainCombobox && !mergeMainCombobox.contains(e.target)) closeDropdown(mergeMainCombobox);
        if (mergeOtherCombobox && !mergeOtherCombobox.contains(e.target)) closeDropdown(mergeOtherCombobox);
    });

    document.querySelectorAll('[data-close-modal]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var modal = document.querySelector(this.getAttribute('data-close-modal'));
            closeModal(modal);
        });
    });

    [newYayineviModal, editYayineviModal, mergeYayineviModal].forEach(function(modal) {
        if (!modal) return;
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal(modal);
        });
    });

    window.__openEditYayineviModalHandler = function() {
            var id = this.getAttribute('data-id');
            var ad = this.getAttribute('data-ad') || '';
            editYayineviId.value = id;
            editYayineviAd.value = ad;
            editYayineviForm.action = updateRouteBase + '/' + id;
            setEditYayineviAuditFromBtn(this);
            openModal(editYayineviModal);
    };
    bindEditButtons();

    document.getElementById('newYayineviForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var form = this;
        submitModalFormAjax(form, function() {
            closeModal(newYayineviModal);
            form.reset();
            fetchTable(1);
        });
    });
    editYayineviForm.addEventListener('submit', function(e) {
        e.preventDefault();
        var form = this;
        submitModalFormAjax(form, function() {
            closeModal(editYayineviModal);
            fetchTable(currentPage);
        });
    });
    mergeYayineviForm.addEventListener('submit', function(e) {
        e.preventDefault();
        var mainId = mergeMainYayineviInput.value;
        var selected = mergeSelectedOthers.map(function(v){ return String(v); });
        if (!mainId) { showToast('error', 'Asıl yayınevi seçilmelidir.'); return; }
        if (!selected.length) { showToast('error', 'Aktarılacak en az bir yayınevi seçilmelidir.'); return; }
        if (selected.indexOf(mainId) >= 0) { showToast('error', 'Asıl yayınevi aktarılacaklar listesinde olamaz.'); return; }
        if (!confirm('Bu işlem geri alınamaz. Devam etmek istiyor musunuz?')) return;

        var submitBtn = document.getElementById('mergeYayineviSubmit');
        setSubmitButtonLoading(submitBtn, true);
        fetch(yayineviMergeUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                main_yayinevi_id: mainId,
                merge_yayinevi_ids: selected
            })
        })
        .then(function(r){ return r.json().then(function(j){ return {ok:r.ok, body:j}; }).catch(function(){ return {ok:false, body:{message:'Yanıt okunamadı.'}}; }); })
        .then(function(res){
            if (!res.ok) {
                showToast('error', firstValidationMessage(res.body));
                return;
            }
            showToast('success', (res.body && res.body.message) ? res.body.message : 'Birleştirme tamamlandı.');
            mergeYayineviForm.reset();
            resetMergeSelections();
            closeModal(mergeYayineviModal);
            fetchTable(1);
        })
        .catch(function(){ showToast('error', 'Bağlantı hatası.'); })
        .finally(function(){ setSubmitButtonLoading(submitBtn, false); });
    });

    @if($errors->any() && old('_modal') === 'new')
        openModal(newYayineviModal);
    @endif
    @if($errors->any() && old('_modal') === 'edit' && old('_yayinevi_id'))
        populateEditModalByRow({{ (int) old('_yayinevi_id') }});
        @if(old('ad') !== null)
            editYayineviAd.value = @json(old('ad'));
        @endif
        openModal(editYayineviModal);
    @endif
    @endif
</script>
@endsection
