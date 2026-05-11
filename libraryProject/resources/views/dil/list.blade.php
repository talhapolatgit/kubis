@extends('layouts.base')

@section('title', 'Dil Yönetimi')

@section('styles')
<style>
    .page-header{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:14px}
    .page-title{font-family:var(--font-serif);font-size:22px;font-weight:700;color:var(--foreground)}
    .page-subtitle{font-size:13px;color:var(--muted-foreground);margin-top:2px}
    .search-bar{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap}
    .filter-field{min-width:180px;flex:1;max-width:300px}
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
    .table-wrap{overflow-x:auto}
    .table-loading-wrapper{position:relative}
    .table-loading-overlay{position:absolute;inset:0;background:rgba(255,255,255,.6);display:none;align-items:center;justify-content:center;z-index:2}
    .table-loading-overlay.show{display:flex}
    .spinner{width:28px;height:28px;border-radius:50%;border:3px solid rgba(122,92,60,.18);border-top-color:var(--primary);animation:spin 1s linear infinite}
    @keyframes spin{to{transform:rotate(360deg)}}
    table{width:100%;border-collapse:collapse}
    thead{background:var(--secondary)}
    th{padding:11px 16px;text-align:left;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--muted-foreground);white-space:nowrap;border-bottom:1px solid var(--border)}
    td{padding:12px 16px;font-size:14px;border-bottom:1px solid rgba(217,208,194,.4);vertical-align:middle}
    tr:last-child td{border-bottom:none}
    .badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600}
    .badge-green{background:rgba(34,139,34,.12);color:#1a6b1a}
    .badge-red{background:rgba(197,48,48,.1);color:#9b1c1c}
    .table-footer{padding:12px 16px;display:flex;align-items:center;justify-content:space-between;border-top:1px solid rgba(217,208,194,.5);font-size:13px;color:var(--muted-foreground)}
    .pagination-nav{display:flex;gap:6px;align-items:center}
    .toast-container{position:fixed;top:16px;right:16px;z-index:1000;display:flex;flex-direction:column;gap:8px}
    .toast{padding:14px 20px;border-radius:10px;font-size:14px;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,.15);animation:toast-in .3s ease;max-width:380px}
    .toast.success{background:#2f7d32;color:#fff}
    .toast.error{background:var(--destructive);color:#fff}
    @keyframes toast-in{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}
    @keyframes toast-out{from{transform:translateX(0);opacity:1}to{transform:translateX(100%);opacity:0}}
    .modal-backdrop{position:fixed;inset:0;background:rgba(20,16,12,.5);display:none;align-items:center;justify-content:center;z-index:1200;padding:16px}
    .modal-backdrop.open{display:flex}
    .modal{width:100%;max-width:520px;background:var(--card);border:1px solid var(--border);border-radius:12px;box-shadow:0 12px 40px rgba(0,0,0,.22)}
    .modal-header{padding:14px 16px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center}
    .modal-title{font-weight:700}
    .modal-body{padding:16px;display:grid;gap:12px}
    .modal-footer{padding:14px 16px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px}
    .btn-with-spinner{display:inline-flex;align-items:center;justify-content:center;min-width:90px}
    .btn-with-spinner .btn-spinner{display:none;width:16px;height:16px;border-radius:50%;border:2px solid rgba(245,240,232,.35);border-top-color:var(--primary-foreground);animation:spin .7s linear infinite;flex-shrink:0}
    .btn-with-spinner.is-loading .btn-spinner{display:inline-block;margin-right:10px}
    .btn-with-spinner.is-loading{pointer-events:none;opacity:.9}
    th.sortable-th{cursor:pointer;user-select:none}
    th.sortable-th .sort-label{display:inline-flex;align-items:center;gap:6px}
    th.sortable-th .sort-caret{opacity:.35;font-size:10px;line-height:1}
    th.sortable-th.sort-active .sort-caret{opacity:1}
</style>
@endsection

@section('breadcrumb')
<nav class="breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard.index') }}" class="breadcrumb-link">Ana Sayfa</a>
    <span class="breadcrumb-sep">/</span>
    <span class="breadcrumb-current">Dil Yönetimi</span>
</nav>
@endsection

@section('content')
<div class="toast-container" id="toastContainer"></div>
<div class="content-area">
    <div class="page-header">
        <div>
            <h1 class="page-title">Dil Yönetimi</h1>
            <p class="page-subtitle" id="pageSubtitle">Sistemde kayıtlı {{ $diller->total() }} dil</p>
        </div>
        <button type="button" class="btn btn-primary" id="openNewModal">Yeni Dil</button>
    </div>

    <div class="form-card filters-card">
        <div class="form-card-header">
            <h2 class="form-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                Filtrele
            </h2>
        </div>
        <div class="form-card-body">
            <div class="search-bar">
                <div class="filter-field">
                    <label class="field-label" for="searchInput">Dil</label>
                    <input type="text" id="searchInput" class="field-input" placeholder="Dil ara..." value="{{ request('search') }}">
                </div>
                <div class="filter-field">
                    <label class="field-label" for="statuFilter">Durum</label>
                    <select id="statuFilter" class="field-input">
                        <option value="" {{ ($activeStatu ?? '') === '' ? 'selected' : '' }}>Tüm Durumlar</option>
                        <option value="aktif" {{ ($activeStatu ?? '') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="pasif" {{ ($activeStatu ?? '') === 'pasif' ? 'selected' : '' }}>Pasif</option>
                    </select>
                </div>
                <div><button type="button" class="btn btn-outline" id="clearFiltersBtn">Filtreyi Temizle</button></div>
                <div><button type="button" class="btn btn-primary" id="searchBtn">Ara</button></div>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="form-card-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
            <h2 class="form-card-title" style="margin:0;">Dil Listesi</h2>
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
                        <th class="sortable-th" data-sort="ad"><span class="sort-label">Dil</span><span class="sort-caret">◇</span></th>
                        <th class="sortable-th" data-sort="sira"><span class="sort-label">Sıra</span><span class="sort-caret">◇</span></th>
                        <th>Durum</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($diller as $dil)
                        <tr>
                            <td>{{ $dil->ad }}</td>
                            <td>{{ (int) $dil->sira }}</td>
                            <td>
                                @if((int) $dil->aktif === 1)
                                    <span class="badge badge-green">Aktif</span>
                                @else
                                    <span class="badge badge-red">Pasif</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-outline btn-sm openEditBtn"
                                        data-id="{{ $dil->id }}"
                                        data-ad="{{ $dil->ad }}"
                                        data-sira="{{ (int) $dil->sira }}"
                                        data-aktif="{{ (int) $dil->aktif === 1 ? 'aktif' : 'pasif' }}">Düzenle</button>
                                <button type="button" class="btn btn-outline btn-sm deleteBtn" data-id="{{ $dil->id }}">Sil</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Kayıt bulunamadı.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>
        <div class="table-footer">
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <span id="paginationInfo">{{ $diller->firstItem() ?? 0 }}–{{ $diller->lastItem() ?? 0 }} / {{ $diller->total() }} kayıt</span>
                <label for="perPageSelectFooter">Sayfa başına:</label>
                <select class="field-input" id="perPageSelectFooter" style="width:95px;padding:6px 8px;">
                    <option value="10" {{ (int) ($perPage ?? 20) === 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ (int) ($perPage ?? 20) === 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ (int) ($perPage ?? 20) === 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ (int) ($perPage ?? 20) === 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>
            <div id="paginationNav" class="pagination-nav"></div>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="newModal">
    <div class="modal">
        <div class="modal-header"><strong>Yeni Dil</strong><button class="btn btn-sm btn-outline" data-close="#newModal">Kapat</button></div>
        <form id="newForm">
            @csrf
            <div class="modal-body">
                <div><label class="field-label">Dil</label><input class="field-input" name="ad" required></div>
                <div><label class="field-label">Sıra</label><input class="field-input" type="number" name="sira" min="0" value="0"></div>
                <div><label class="field-label">Durum</label><select class="field-select" name="aktif"><option value="aktif">Aktif</option><option value="pasif">Pasif</option></select></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline" data-close="#newModal">Vazgeç</button><button type="submit" class="btn btn-primary">Kaydet</button></div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="editModal">
    <div class="modal">
        <div class="modal-header"><strong>Dil Düzenle</strong><button class="btn btn-sm btn-outline" data-close="#editModal">Kapat</button></div>
        <form id="editForm">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <input type="hidden" id="editId">
                <div><label class="field-label">Dil</label><input class="field-input" id="editAd" name="ad" required></div>
                <div><label class="field-label">Sıra</label><input class="field-input" type="number" id="editSira" name="sira" min="0"></div>
                <div><label class="field-label">Durum</label><select class="field-select" id="editAktif" name="aktif"><option value="aktif">Aktif</option><option value="pasif">Pasif</option></select></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline" data-close="#editModal">Vazgeç</button><button type="submit" class="btn btn-primary">Güncelle</button></div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
const ajaxUrl = @json(route('dil.index'));
const exportUrl = @json(route('dil.export'));
const storeUrl = @json(route('dil.store'));
const apiBase = @json(url('/dil'));
let currentPage = {{ (int) $diller->currentPage() }};
let sortBy = @json($activeSortBy ?? '');
let sortDir = @json(($activeSortBy ?? '') !== '' ? ($activeSortDir ?? 'asc') : 'asc');
const csrfToken = @json(csrf_token());

function openModal(id){document.querySelector(id)?.classList.add('open')}
function closeModal(id){document.querySelector(id)?.classList.remove('open')}
function esc(s){return (s||'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]))}
function getFilters(){
  return {
    search: document.getElementById('searchInput').value.trim(),
    statu: document.getElementById('statuFilter').value,
    per_page: document.getElementById('perPageSelectFooter').value,
    sort_by: sortBy,
    sort_dir: sortDir
  };
}
function bindModalClose(){document.querySelectorAll('[data-close]').forEach(b=>b.onclick=()=>closeModal(b.dataset.close));}
function updateSortHeaderDisplay(){
  document.querySelectorAll('th.sortable-th').forEach(th=>{
    th.classList.remove('sort-active');
    const col = th.getAttribute('data-sort');
    const caret = th.querySelector('.sort-caret');
    if(!caret) return;
    if(sortBy && sortBy===col){
      th.classList.add('sort-active');
      caret.textContent = sortDir === 'desc' ? '▼' : '▲';
    } else {
      caret.textContent = '◇';
    }
  });
}
function showToast(type, title){
  const c = document.getElementById('toastContainer');
  const t = document.createElement('div');
  t.className = 'toast ' + type;
  t.innerHTML = '<div>'+esc(title)+'</div>';
  c.appendChild(t);
  setTimeout(()=>{ t.style.animation='toast-out 0.3s ease forwards'; setTimeout(()=>t.remove(), 300); }, 2600);
}
function setButtonLoading(btn, loading){
  if(!btn) return;
  btn.classList.toggle('is-loading', loading);
  btn.disabled = loading;
}

function buildRow(r){
  const badge = r.aktif === 'aktif' ? '<span class="badge badge-green">Aktif</span>' : '<span class="badge badge-red">Pasif</span>';
  return '<tr><td>'+esc(r.ad)+'</td><td>'+r.sira+'</td><td>'+badge+'</td><td>'
      + '<button type="button" class="btn btn-outline btn-sm openEditBtn" data-id="'+r.id+'" data-ad="'+esc(r.ad)+'" data-sira="'+r.sira+'" data-aktif="'+r.aktif+'">Düzenle</button> '
      + '<button type="button" class="btn btn-outline btn-sm deleteBtn" data-id="'+r.id+'">Sil</button></td></tr>';
}

function bindRowButtons(){
  document.querySelectorAll('.openEditBtn').forEach(btn=>{
    btn.onclick=function(){
      document.getElementById('editId').value=this.dataset.id;
      document.getElementById('editAd').value=this.dataset.ad||'';
      document.getElementById('editSira').value=this.dataset.sira||'0';
      document.getElementById('editAktif').value=this.dataset.aktif||'aktif';
      openModal('#editModal');
    };
  });
  document.querySelectorAll('.deleteBtn').forEach(btn=>{
    btn.onclick=function(){
      const id=this.dataset.id;
      if(!id || !confirm('Bu dili silmek istiyor musunuz?')) return;
      fetch(apiBase+'/'+id,{method:'DELETE',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json','X-CSRF-TOKEN':@json(csrf_token())}})
        .then(r=>r.json().then(j=>({ok:r.ok,body:j})))
        .then(res=>{ if(!res.ok) throw new Error((res.body&&res.body.message)||'Silme hatası'); showToast('success', 'Dil silindi.'); fetchTable(currentPage); })
        .catch(e=>showToast('error', e.message));
    };
  });
}

function renderPagination(meta){
  const cp = meta.current_page || 1, lp = meta.last_page || 1;
  let html = '<button class="btn btn-outline btn-sm pageBtn" data-page="'+(cp-1)+'"'+(cp<=1?' disabled':'')+'>‹</button>';
  html += '<button class="btn btn-outline btn-sm" disabled>'+cp+' / '+lp+'</button>';
  html += '<button class="btn btn-outline btn-sm pageBtn" data-page="'+(cp+1)+'"'+(cp>=lp?' disabled':'')+'>›</button>';
  document.getElementById('paginationNav').innerHTML = html;
  document.querySelectorAll('.pageBtn').forEach(b=>b.onclick=()=>{if(!b.disabled) fetchTable(parseInt(b.dataset.page,10)||1)});
}

function fetchTable(page){
  currentPage = page || 1;
  const f = getFilters();
  const searchBtn = document.getElementById('searchBtn');
  const p = new URLSearchParams();
  if(f.search) p.set('search', f.search);
  if(f.statu) p.set('statu', f.statu);
  if(f.per_page) p.set('per_page', f.per_page);
  if(f.sort_by){
    p.set('sort_by', f.sort_by);
    p.set('sort_dir', f.sort_dir || 'asc');
  }
  if(currentPage>1) p.set('page', String(currentPage));
  setButtonLoading(searchBtn, true);
  document.getElementById('tableLoading').classList.add('show');
  fetch(ajaxUrl + (p.toString()?('?'+p.toString()):''), {headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}})
    .then(r=>r.json())
    .then(data=>{
      const rows = data.rows || [];
      document.getElementById('tableBody').innerHTML = rows.length ? rows.map(buildRow).join('') : '<tr><td colspan="4">Kayıt bulunamadı.</td></tr>';
      const m = data.meta || {};
      if(m.sort_by){ sortBy = m.sort_by; sortDir = m.sort_dir || 'asc'; } else { sortBy=''; sortDir='asc'; }
      if(document.getElementById('statuFilter')) document.getElementById('statuFilter').value = m.statu || '';
      if(document.getElementById('perPageSelectFooter') && m.per_page) document.getElementById('perPageSelectFooter').value = String(m.per_page);
      document.getElementById('paginationInfo').textContent = (m.from||0)+'–'+(m.to||0)+' / '+(m.total||0)+' kayıt';
      const sub = document.getElementById('pageSubtitle'); if(sub) sub.textContent = 'Sistemde kayıtlı '+(m.total||0)+' dil';
      renderPagination(m);
      bindRowButtons();
      updateSortHeaderDisplay();
      const u = new URL(window.location.href);
      ['search','statu','per_page','sort_by','sort_dir','page'].forEach(k=>u.searchParams.delete(k));
      if(f.search) u.searchParams.set('search', f.search);
      if(f.statu) u.searchParams.set('statu', f.statu);
      if(f.per_page) u.searchParams.set('per_page', f.per_page);
      if(f.sort_by){ u.searchParams.set('sort_by', f.sort_by); u.searchParams.set('sort_dir', f.sort_dir || 'asc');}
      if(currentPage>1) u.searchParams.set('page', String(currentPage));
      window.history.replaceState({},'',u.toString());
    })
    .catch(()=>showToast('error', 'Liste güncellenemedi.'))
    .finally(()=>{
      setButtonLoading(searchBtn, false);
      document.getElementById('tableLoading').classList.remove('show');
    });
}

document.getElementById('openNewModal').onclick=()=>openModal('#newModal');
document.getElementById('searchBtn').innerHTML = '<span class="btn-spinner" aria-hidden="true"></span><span class="btn-label">Ara</span>';
document.getElementById('searchBtn').classList.add('btn-with-spinner');
document.getElementById('searchBtn').onclick=()=>fetchTable(1);
document.getElementById('searchInput').addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();fetchTable(1);}});
document.getElementById('statuFilter').addEventListener('change',()=>fetchTable(1));
document.getElementById('perPageSelectFooter').addEventListener('change',()=>fetchTable(1));
document.getElementById('clearFiltersBtn').addEventListener('click', function(){
  document.getElementById('searchInput').value = '';
  document.getElementById('statuFilter').value = '';
  document.getElementById('perPageSelectFooter').value = '20';
  sortBy = '';
  sortDir = 'asc';
  updateSortHeaderDisplay();
  fetchTable(1);
});
document.querySelectorAll('th.sortable-th').forEach(th=>{
  th.addEventListener('click', function(){
    const col = this.getAttribute('data-sort');
    if(!col) return;
    if(sortBy===col){ sortDir = sortDir === 'asc' ? 'desc' : 'asc'; }
    else { sortBy = col; sortDir = 'asc'; }
    updateSortHeaderDisplay();
    fetchTable(1);
  });
});
document.getElementById('exportExcelBtn').addEventListener('click', function(){
  const f = getFilters();
  const p = new URLSearchParams();
  if(f.search) p.set('search', f.search);
  if(f.statu) p.set('statu', f.statu);
  if(f.per_page) p.set('per_page', f.per_page);
  if(f.sort_by){
    p.set('sort_by', f.sort_by);
    p.set('sort_dir', f.sort_dir || 'asc');
  }
  window.location.href = exportUrl + (p.toString() ? ('?' + p.toString()) : '');
});
document.getElementById('newForm').addEventListener('submit',function(e){
  e.preventDefault();
  fetch(storeUrl,{method:'POST',body:new FormData(this),headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}})
    .then(r=>r.json().then(j=>({ok:r.ok,body:j})))
    .then(res=>{if(!res.ok) throw new Error((res.body&&res.body.message)||'Kayıt hatası'); closeModal('#newModal'); this.reset(); showToast('success', 'Dil başarıyla eklendi.'); fetchTable(1);})
    .catch(er=>showToast('error', er.message));
});
document.getElementById('editForm').addEventListener('submit',function(e){
  e.preventDefault();
  const id = document.getElementById('editId').value;
  const fd = new FormData(this);
  fd.append('_method','PUT');
  fetch(apiBase+'/'+id,{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}})
    .then(r=>r.json().then(j=>({ok:r.ok,body:j})))
    .then(res=>{if(!res.ok) throw new Error((res.body&&res.body.message)||'Güncelleme hatası'); closeModal('#editModal'); showToast('success', 'Dil güncellendi.'); fetchTable(currentPage);})
    .catch(er=>showToast('error', er.message));
});
bindModalClose();
bindRowButtons();
updateSortHeaderDisplay();
const initialMeta = { current_page: {{ (int) $diller->currentPage() }}, last_page: {{ (int) $diller->lastPage() }} };
renderPagination(initialMeta);
</script>
@endsection
