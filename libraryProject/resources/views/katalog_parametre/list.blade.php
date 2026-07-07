@extends('layouts.base')

@section('title', 'Katalog Parametreleri')

@section('styles')
<style>
    .page-header{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:14px}
    .page-title{font-family:var(--font-serif);font-size:22px;font-weight:700;color:var(--foreground)}
    .page-subtitle{font-size:13px;color:var(--muted-foreground);margin-top:2px}
    .tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px}
    .tab-btn{padding:8px 12px;border:1px solid var(--border);border-radius:8px;background:var(--card);cursor:pointer;font-size:13px}
    .tab-btn.active{background:var(--primary);color:var(--primary-foreground);border-color:var(--primary)}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:9px 16px;border-radius:calc(var(--radius) - 2px);font-size:14px;font-weight:500;cursor:pointer;border:none;text-decoration:none}
    .btn-primary{background:var(--primary);color:var(--primary-foreground)}
    .btn-outline{background:transparent;color:var(--foreground);border:1px solid var(--border)}
    .btn-sm{padding:6px 12px;font-size:13px}
    .form-card,.table-card{background:var(--card);border:1px solid rgba(217,208,194,.6);border-radius:var(--radius);overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.04)}
    .form-card-header{padding:14px 16px;border-bottom:1px solid rgba(217,208,194,.5)}
    .form-card-title{display:flex;align-items:center;gap:8px;font-size:16px;font-weight:700;margin:0}
    .form-card-body{padding:14px 16px}
    .search-bar{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap}
    .field{min-width:180px;flex:1;max-width:320px}
    .field-label{display:block;font-size:12px;font-weight:600;color:var(--muted-foreground);margin-bottom:6px}
    .field-input{width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);font-size:14px}
    .field-input:focus{border-color:var(--ring);box-shadow:0 0 0 2px rgba(122,92,60,.12);outline:none}
    .table-wrap{overflow-x:auto}
    .table-loading-wrapper{position:relative}
    .table-loading-overlay{position:absolute;inset:0;background:rgba(255,255,255,.6);display:none;align-items:center;justify-content:center;z-index:2}
    .table-loading-overlay.show{display:flex}
    .spinner{width:28px;height:28px;border-radius:50%;border:3px solid rgba(122,92,60,.18);border-top-color:var(--primary);animation:spin 1s linear infinite}
    @keyframes spin{to{transform:rotate(360deg)}}
    table{width:100%;border-collapse:collapse}
    thead{background:var(--secondary)}
    th{padding:11px 16px;text-align:left;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--muted-foreground);border-bottom:1px solid var(--border)}
    .sortable-th{cursor:pointer;user-select:none;white-space:nowrap}
    .sortable-th .sort-caret{margin-left:6px;opacity:.45}
    td{padding:12px 16px;font-size:14px;border-bottom:1px solid rgba(217,208,194,.4)}
    .badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600}
    .badge-green{background:rgba(34,139,34,.12);color:#1a6b1a}
    .badge-red{background:rgba(197,48,48,.1);color:#9b1c1c}
    .table-footer{padding:12px 16px;display:flex;align-items:center;justify-content:space-between;border-top:1px solid rgba(217,208,194,.5);font-size:13px;color:var(--muted-foreground)}
    .pagination{display:flex;gap:6px}
    .modal-backdrop{position:fixed;inset:0;background:rgba(20,16,12,.5);display:none;align-items:center;justify-content:center;z-index:1200;padding:16px}
    .modal-backdrop.open{display:flex}
    .modal{width:100%;max-width:520px;background:var(--card);border:1px solid var(--border);border-radius:12px}
    .modal-header{padding:14px 16px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center}
    .modal-body{padding:16px;display:grid;gap:12px}
    .modal-footer{padding:14px 16px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px}
    .btn-icon{width:30px;height:30px;border:none;border-radius:8px;background:transparent;cursor:pointer;color:var(--muted-foreground)}
    .btn-icon:hover{background:var(--muted);color:var(--foreground)}
    .modal-footer-audit{margin-right:auto;display:grid;gap:4px;font-size:12px;color:var(--muted-foreground)}
    .audit-row{display:flex;align-items:center;gap:6px}
    .audit-key{font-weight:600;color:var(--foreground)}
    .modal-footer-actions{display:flex;align-items:center;gap:8px}
</style>
@endsection

@section('breadcrumb')
<nav class="breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('katalog.index') }}" class="breadcrumb-link">Ana Sayfa</a>
    <span class="breadcrumb-sep">/</span>
    <span class="breadcrumb-current">Katalog Parametreleri</span>
</nav>
@endsection

@section('content')
<div class="content-area">
    <div class="page-header">
        <div>
            <h1 class="page-title">Katalog Parametreleri</h1>
            <p class="page-subtitle" id="subTitle">Tür, Alt Tür, Şekil, Ortam, Dil ve Koleksiyon yönetimi</p>
        </div>
        <button type="button" class="btn btn-primary" id="openNewBtn">Yeni Kayıt</button>
    </div>

    <div class="tabs" id="tabBar">
        <button class="tab-btn" data-tab="tur">Tür</button>
        <button class="tab-btn" data-tab="alttur">Alt Tür</button>
        <button class="tab-btn" data-tab="sekil">Şekil</button>
        <button class="tab-btn" data-tab="ortam">Ortam</button>
        <button class="tab-btn" data-tab="dil">Dil</button>
        <button class="tab-btn" data-tab="koleksiyon">Koleksiyon</button>
    </div>

    <div class="form-card" style="margin-bottom:14px;">
        <div class="form-card-header"><h2 class="form-card-title">Filtrele</h2></div>
        <div class="form-card-body">
            <div class="search-bar">
                <div class="field">
                    <label class="field-label" for="searchInput">Ad</label>
                    <input id="searchInput" class="field-input" type="text" placeholder="Ada göre ara...">
                </div>
                <div class="field">
                    <label class="field-label" for="statuFilter">Durum</label>
                    <select id="statuFilter" class="field-input">
                        <option value="">Tüm Durumlar</option>
                        <option value="aktif">Aktif</option>
                        <option value="pasif">Pasif</option>
                    </select>
                </div>
                <div><button class="btn btn-outline" id="clearBtn" type="button">Filtreyi Temizle</button></div>
                <div><button class="btn btn-primary" id="searchBtn" type="button">Ara</button></div>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="form-card-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
            <h2 class="form-card-title" id="tableTitle">Liste</h2>
            <button type="button" class="btn btn-outline btn-sm" id="exportExcelBtn">Excel Olarak İndir</button>
        </div>
        <div class="table-loading-wrapper">
            <div class="table-loading-overlay" id="tableLoading"><div class="spinner"></div></div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="sortable-th" data-sort="ad">Ad <span class="sort-caret"></span></th>
                            <th class="sortable-th" data-sort="sira">Sıra <span class="sort-caret"></span></th>
                            <th class="sortable-th" data-sort="eser_sayisi">Eser Sayısı <span class="sort-caret"></span></th>
                            <th>Durum</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="tableBody"><tr><td colspan="5">Yükleniyor…</td></tr></tbody>
                </table>
            </div>
        </div>
        <div class="table-footer">
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <span id="rangeInfo">—</span>
                <label for="perPage">Sayfa başına:</label>
                <select id="perPage" class="field-input" style="width:95px;padding:6px 8px;">
                    <option value="10">10</option>
                    <option value="20" selected>20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div class="pagination" id="pagination"></div>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="editModal">
    <div class="modal">
        <div class="modal-header"><strong id="modalTitle">Yeni Kayıt</strong><button class="btn-icon" type="button" data-close-modal="#editModal">✕</button></div>
        <form id="editForm">
            @csrf
            <input type="hidden" id="editId">
            <div class="modal-body">
                <div><label class="field-label">Ad</label><input class="field-input" name="ad" id="editAd" required></div>
                <div><label class="field-label">Sıra</label><input class="field-input" type="number" min="0" name="sira" id="editSira" value="0"></div>
                <div><label class="field-label">Durum</label><select class="field-input" name="aktif" id="editAktif"><option value="aktif">Aktif</option><option value="pasif">Pasif</option></select></div>
            </div>
            <div class="modal-footer">
                <div class="modal-footer-audit" aria-label="Kayıt bilgisi">
                    <div class="audit-row"><span class="audit-key">Kayıt tarihi:</span> <span id="editMetaKayitSatiri">—</span></div>
                    <div class="audit-row"><span class="audit-key">Güncelleme tarihi:</span> <span id="editMetaGuncellemeSatiri">—</span></div>
                </div>
                <div class="modal-footer-actions">
                    <button type="button" class="btn btn-outline" data-close-modal="#editModal">Vazgeç</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
const activeTabDefault = @json($activeTab ?? 'tur');
const baseUrl = @json(url('/katalog-parametreler'));
const csrf = @json(csrf_token());
let state = { tab: activeTabDefault, search: '', statu: '', page: 1, per_page: 20, sort_by: '', sort_dir: 'asc' };

function esc(s){return (s||'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]))}
function reqUrl(path, params){ const p = new URLSearchParams(params||{}); return baseUrl + path + (p.toString() ? ('?' + p.toString()) : ''); }
function setActiveTab(){
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.toggle('active', b.dataset.tab === state.tab));
}
function openModal(){document.getElementById('editModal').classList.add('open')}
function closeModal(){document.getElementById('editModal').classList.remove('open')}
function formatAuditLine(dateValue, userValue){
  const dateText = dateValue || '—';
  const userText = userValue || '';
  return userText && userText !== '—' ? (dateText + ' (' + userText + ')') : dateText;
}
function setEditAuditFromDataset(ds){
  const k = document.getElementById('editMetaKayitSatiri');
  const g = document.getElementById('editMetaGuncellemeSatiri');
  if(k) k.textContent = formatAuditLine(ds.kayitTarihi || '—', ds.kaydeden || '');
  if(g) g.textContent = formatAuditLine(ds.guncellemeTarihi || '—', ds.guncelleyen || '');
}

function buildRow(r){
  const badge = r.aktif === 'aktif' ? '<span class="badge badge-green">Aktif</span>' : '<span class="badge badge-red">Pasif</span>';
  return '<tr><td>'+esc(r.ad)+'</td><td>'+r.sira+'</td><td>'+ (r.eser_sayisi || 0) +'</td><td>'+badge+'</td><td>'
      + '<button class="btn btn-outline btn-sm editBtn" data-id="'+r.id+'" data-ad="'+esc(r.ad)+'" data-sira="'+r.sira+'" data-aktif="'+r.aktif+'" data-kayit-tarihi="'+esc(r.kayit_tarihi || '—')+'" data-kaydeden="'+esc(r.kaydeden || '—')+'" data-guncelleme-tarihi="'+esc(r.guncelleme_tarihi || '—')+'" data-guncelleyen="'+esc(r.guncelleyen || '—')+'">Düzenle</button> '
      + '<button class="btn btn-outline btn-sm delBtn" data-id="'+r.id+'">Sil</button></td></tr>';
}
function bindRowBtns(){
  document.querySelectorAll('.editBtn').forEach(b=>b.onclick=function(){
    document.getElementById('modalTitle').textContent = 'Kayıt Düzenle';
    document.getElementById('editId').value = this.dataset.id;
    document.getElementById('editAd').value = this.dataset.ad || '';
    document.getElementById('editSira').value = this.dataset.sira || '0';
    document.getElementById('editAktif').value = this.dataset.aktif || 'aktif';
    setEditAuditFromDataset(this.dataset);
    openModal();
  });
  document.querySelectorAll('.delBtn').forEach(b=>b.onclick=function(){
    const id = this.dataset.id;
    if(!id || !confirm('Bu kaydı silmek istiyor musunuz?')) return;
    fetch(baseUrl + '/' + state.tab + '/' + id, { method:'DELETE', headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json','X-CSRF-TOKEN':csrf}})
      .then(r=>r.json().then(j=>({ok:r.ok,body:j})))
      .then(res=>{ if(!res.ok) throw new Error((res.body&&res.body.message)||'Silinemedi'); fetchTable(); })
      .catch(e=>alert(e.message));
  });
}
function renderPagination(m){
  const cp = m.current_page || 1, lp = m.last_page || 1;
  let html = '<button class="btn btn-outline btn-sm pBtn" data-p="'+(cp-1)+'"'+(cp<=1?' disabled':'')+'>‹</button>';
  html += '<button class="btn btn-outline btn-sm" disabled>'+cp+' / '+lp+'</button>';
  html += '<button class="btn btn-outline btn-sm pBtn" data-p="'+(cp+1)+'"'+(cp>=lp?' disabled':'')+'>›</button>';
  document.getElementById('pagination').innerHTML = html;
  document.querySelectorAll('.pBtn').forEach(b=>b.onclick=()=>{ if(!b.disabled){ state.page = parseInt(b.dataset.p,10)||1; fetchTable(); }});
}
function updateSortHeaderDisplay(){
  document.querySelectorAll('.sortable-th').forEach(th => {
    const key = th.dataset.sort;
    const caret = th.querySelector('.sort-caret');
    if(!caret) return;
    if(state.sort_by !== key){
      caret.textContent = '↕';
      return;
    }
    caret.textContent = state.sort_dir === 'desc' ? '▼' : '▲';
  });
}
function setTableLoading(isLoading){
  const el = document.getElementById('tableLoading');
  if(!el) return;
  el.classList.toggle('show', !!isLoading);
}
function fetchTable(){
  const params = {
    search: state.search,
    statu: state.statu,
    page: state.page,
    per_page: state.per_page,
    sort_by: state.sort_by || '',
    sort_dir: state.sort_dir || 'asc'
  };
  setTableLoading(true);
  fetch(reqUrl('/'+state.tab+'/list', params), { headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'} })
    .then(r=>r.json())
    .then(data=>{
      const rows = data.rows || [];
      document.getElementById('tableBody').innerHTML = rows.length ? rows.map(buildRow).join('') : '<tr><td colspan="5">Kayıt bulunamadı.</td></tr>';
      const m = data.meta || {};
      document.getElementById('tableTitle').textContent = (m.title || 'Liste') + ' Listesi';
      document.getElementById('rangeInfo').textContent = (m.from||0)+'–'+(m.to||0)+' / '+(m.total||0)+' kayıt';
      if(m.per_page) document.getElementById('perPage').value = String(m.per_page);
      state.sort_by = m.sort_by || '';
      state.sort_dir = m.sort_dir || 'asc';
      updateSortHeaderDisplay();
      renderPagination(m);
      bindRowBtns();
    })
    .catch(()=>{})
    .finally(()=>setTableLoading(false));
}

document.querySelectorAll('[data-close-modal]').forEach(function(btn){
  btn.addEventListener('click', function(){
    const modal = document.querySelector(this.getAttribute('data-close-modal'));
    closeModal(modal);
  });
});
document.getElementById('editModal').addEventListener('click', function(e){
  if(e.target === this) closeModal(this);
});
document.getElementById('openNewBtn').addEventListener('click', function(){
  document.getElementById('modalTitle').textContent = 'Yeni Kayıt';
  document.getElementById('editId').value = '';
  document.getElementById('editForm').reset();
  document.getElementById('editSira').value = '0';
  document.getElementById('editAktif').value = 'aktif';
  setEditAuditFromDataset({ kayitTarihi: '—', kaydeden: '', guncellemeTarihi: '—', guncelleyen: '' });
  openModal();
});
document.getElementById('editForm').addEventListener('submit', function(e){
  e.preventDefault();
  const id = document.getElementById('editId').value;
  const fd = new FormData(this);
  let url = baseUrl + '/' + state.tab;
  if(id){ fd.append('_method','PUT'); url += '/' + id; }
  fetch(url, { method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'} })
    .then(r=>r.json().then(j=>({ok:r.ok,body:j})))
    .then(res=>{ if(!res.ok) throw new Error((res.body&&res.body.message)||'Kaydedilemedi'); closeModal(); fetchTable(); })
    .catch(e=>alert(e.message));
});
document.getElementById('searchBtn').addEventListener('click', function(){
  state.search = document.getElementById('searchInput').value.trim();
  state.statu = document.getElementById('statuFilter').value;
  state.page = 1;
  fetchTable();
});
document.getElementById('clearBtn').addEventListener('click', function(){
  document.getElementById('searchInput').value = '';
  document.getElementById('statuFilter').value = '';
  state.search = ''; state.statu = ''; state.page = 1;
  fetchTable();
});
document.getElementById('perPage').addEventListener('change', function(){
  state.per_page = parseInt(this.value, 10) || 20;
  state.page = 1;
  fetchTable();
});
document.getElementById('exportExcelBtn').addEventListener('click', function(){
  const params = new URLSearchParams({
    search: state.search || '',
    statu: state.statu || '',
    sort_by: state.sort_by || '',
    sort_dir: state.sort_dir || 'asc'
  });
  window.location.href = baseUrl + '/' + state.tab + '/export?' + params.toString();
});
document.querySelectorAll('.sortable-th').forEach(th => {
  th.addEventListener('click', function(){
    const key = this.dataset.sort;
    if(!key) return;
    if(state.sort_by === key){
      state.sort_dir = state.sort_dir === 'asc' ? 'desc' : 'asc';
    } else {
      state.sort_by = key;
      state.sort_dir = 'asc';
    }
    state.page = 1;
    updateSortHeaderDisplay();
    fetchTable();
  });
});
document.querySelectorAll('.tab-btn').forEach(btn=>{
  btn.addEventListener('click', function(){
    const tab = this.dataset.tab;
    if(!tab || tab === state.tab) return;
    state.tab = tab;
    state.page = 1;
    setActiveTab();
    fetchTable();
  });
});
setActiveTab();
updateSortHeaderDisplay();
fetchTable();
</script>
@endsection
