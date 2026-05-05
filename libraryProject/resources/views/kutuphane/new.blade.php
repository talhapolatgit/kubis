@extends('layouts.base')

@section('title', 'Yeni Kutuphane')

@section('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <style>
        /* Form Card */
        .form-card { border: 1px solid rgba(217,208,194,0.6); border-radius: var(--radius); background: var(--card); box-shadow: 0 1px 3px rgba(0,0,0,0.04); max-width: 760px; }
        .form-card-header { padding: 24px 24px 16px; }
        .form-card-title { display: flex; align-items: center; gap: 8px; font-family: var(--font-serif); font-size: 20px; font-weight: 700; }
        .form-card-title svg { width: 20px; height: 20px; color: var(--primary); }
        .form-card-desc { font-size: 14px; color: var(--muted-foreground); margin-top: 4px; }
        .form-card-separator { height: 1px; background: var(--border); }
        .form-card-body { padding: 24px; display: flex; flex-direction: column; gap: 20px; }

        /* Section */
        .section-header { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted-foreground); margin-bottom: 14px; }
        .section-number { width: 20px; height: 20px; border-radius: 4px; background: rgba(122,92,60,0.1); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: var(--primary); }
        .section-sep { height: 1px; background: var(--border); }

        /* Form Grid */
        .form-grid { display: grid; gap: 16px; }
        .form-grid.cols-2 { grid-template-columns: repeat(2, 1fr); }
        .form-field { display: flex; flex-direction: column; }
        .form-label { font-size: 14px; font-weight: 500; color: var(--foreground); margin-bottom: 6px; }
        .form-label .required { color: var(--destructive); }
        .form-input, .form-select, .form-textarea { width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: calc(var(--radius) - 2px); background: var(--card); color: var(--foreground); font-size: 14px; line-height: 1.5; transition: border-color 0.15s, box-shadow 0.15s; outline: none; }
        .form-input::placeholder, .form-textarea::placeholder { color: var(--muted-foreground); opacity: 0.7; }
        .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: var(--ring); box-shadow: 0 0 0 2px rgba(122,92,60,0.15); }
        .form-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237a7060' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px; }
        .form-textarea { resize: vertical; }
        .form-hint { font-size: 12px; color: var(--muted-foreground); margin-top: 4px; }
        .form-error { font-size: 12px; color: var(--destructive); margin-top: 4px; }

        /* Actions */
        .form-actions { display: flex; justify-content: flex-end; gap: 12px; padding-top: 8px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 9px 16px; border-radius: calc(var(--radius) - 2px); font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.15s, opacity 0.15s; border: none; text-decoration: none; }
        .btn svg { width: 16px; height: 16px; }
        .btn-primary { background: var(--primary); color: var(--primary-foreground); }
        .btn-primary:hover { opacity: 0.9; }
        .btn-outline { background: transparent; color: var(--foreground); border: 1px solid var(--border); }
        .btn-outline:hover { background: var(--muted); }

        /* Loading Overlay */
        .loading-overlay { position: fixed; inset: 0; z-index: 2000; background: rgba(61,50,38,0.45); backdrop-filter: blur(3px); display: flex; align-items: center; justify-content: center; opacity: 0; visibility: hidden; transition: opacity 0.2s ease, visibility 0.2s ease; }
        .loading-overlay.visible { opacity: 1; visibility: visible; }
        .loading-box { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 40px 56px; display: flex; flex-direction: column; align-items: center; gap: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.25); transform: scale(0.92); transition: transform 0.2s ease; }
        .loading-overlay.visible .loading-box { transform: scale(1); }
        .loading-spinner { width: 48px; height: 48px; border: 3px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.75s linear infinite; }
        .loading-text { font-size: 15px; font-weight: 600; color: var(--foreground); }
        .loading-subtext { font-size: 13px; color: var(--muted-foreground); margin-top: -12px; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        /* Toast */
        .toast-container { position: fixed; top: 16px; right: 16px; z-index: 1000; display: flex; flex-direction: column; gap: 8px; }
        .toast { padding: 14px 20px; border-radius: 10px; font-size: 14px; font-weight: 500; box-shadow: 0 4px 12px rgba(0,0,0,0.15); animation: toast-in 0.3s ease; max-width: 380px; }
        .toast.success { background: #2f7d32; color: white; }
        .toast.error { background: var(--destructive); color: white; }
        .toast-desc { font-size: 13px; font-weight: 400; opacity: 0.9; margin-top: 2px; }
        @keyframes toast-in { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes toast-out { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }

        @media (max-width: 768px) {
            .form-grid.cols-2 { grid-template-columns: 1fr; }
            .form-actions { flex-direction: column-reverse; }
        }
    </style>
@endsection

@section('breadcrumb')
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('kutuphane.index') }}" class="breadcrumb-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Kütüphaneler
        </a>
        <span class="breadcrumb-sep">/</span>
        <span class="breadcrumb-current">Yeni Kütüphane</span>
    </nav>
@endsection

@section('content')
<div class="toast-container" id="toastContainer"></div>

<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-box">
        <div class="loading-spinner"></div>
        <span class="loading-text">Kaydediliyor...</span>
        <span class="loading-subtext">Lütfen bekleyin</span>
    </div>
</div>

        <div class="content-area">
            <form id="kutuphanEkleForm" class="form-card" method="POST" action="{{ route('kutuphane.store') }}" novalidate>
                @csrf

                <div class="form-card-header">
                    <h2 class="form-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        Yeni Kütüphane
                    </h2>
                    <p class="form-card-desc">Sisteme yeni bir kütüphane şubesi ekleyin.</p>
                </div>
                <div class="form-card-separator"></div>

                <div class="form-card-body">

                    <!-- Bölüm 1: Temel Bilgiler -->
                    <div>
                        <h3 class="section-header">
                            <span class="section-number">1</span>
                            Temel Bilgiler
                        </h3>
                        <div class="form-grid cols-2">
                            <div class="form-field" style="grid-column: span 2;">
                                <label class="form-label" for="title">Kütüphane Adı <span class="required">*</span></label>
                                <input type="text" class="form-input" id="title" name="title"
                                       placeholder="Örnek: Örnektepe Kütüphanesi"
                                       value="{{ old('title') }}" required />
                                @error('title')<span class="form-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="statu">Durum <span class="required">*</span></label>
                                <select class="form-select" id="statu" name="statu">
                                    <option value="aktif" {{ old('statu', 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="pasif" {{ old('statu') === 'pasif' ? 'selected' : '' }}>Pasif</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="section-sep"></div>

                    <!-- Bölüm 2: İletişim Bilgileri -->
                    <div>
                        <h3 class="section-header">
                            <span class="section-number">2</span>
                            İletişim Bilgileri
                        </h3>
                        <div class="form-grid cols-2">
                            <div class="form-field">
                                <label class="form-label" for="phone">Telefon</label>
                                <input type="text" class="form-input" id="phone" name="phone"
                                       placeholder="Örnek: 0212 555 00 00"
                                       value="{{ old('phone') }}" />
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="email">E-posta</label>
                                <input type="email" class="form-input" id="email" name="email"
                                       placeholder="Örnek: bilgi@kutuphane.gov.tr"
                                       value="{{ old('email') }}" />
                                @error('email')<span class="form-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-field" style="grid-column: span 2;">
                                <label class="form-label" for="address">Adres</label>
                                <textarea class="form-textarea" id="address" name="address"
                                          placeholder="Kütüphanenin tam adresi" rows="3">{{ old('address') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="form-actions">
                        <a href="{{ route('kutuphane.index') }}" class="btn btn-outline">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            Vazgeç
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v13a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            Kaydet
                        </button>
                    </div>

                </div>
            </form>
        </div>
@endsection

@section('scripts')
<script>
    // Toast
    function showToast(type, title, desc) {
        var c = document.getElementById('toastContainer');
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<div>' + title + '</div>' + (desc ? '<div class="toast-desc">' + desc + '</div>' : '');
        c.appendChild(t);
        setTimeout(function() { t.style.animation = 'toast-out 0.3s ease forwards'; setTimeout(function() { if (t.parentNode) t.parentNode.removeChild(t); }, 300); }, 3500);
    }

    // AJAX Submit
    var form = document.getElementById('kutuphanEkleForm');
    var submitBtn = document.getElementById('submitBtn');
    var submitBtnOriginalHtml = submitBtn.innerHTML;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        var title = document.getElementById('title').value.trim();
        if (!title) {
            showToast('error', 'Zorunlu alan eksik', 'Kütüphane adı boş bırakılamaz.');
            document.getElementById('title').focus();
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin 0.8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Kaydediliyor...';
        document.getElementById('loadingOverlay').classList.add('visible');

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: new FormData(form)
        })
        .then(function(res) {
            return res.json().then(function(data) { return { status: res.status, data: data }; });
        })
        .then(function(result) {
            if (result.status === 200 && result.data.success) {
                showToast('success', 'Kayıt Başarılı', result.data.message);
                form.reset();
                document.getElementById('statu').value = 'aktif';
            } else if (result.status === 422 && result.data.errors) {
                var msgs = Object.values(result.data.errors).flat();
                showToast('error', 'Doğrulama Hatası', msgs[0]);
            } else {
                showToast('error', 'Hata', result.data.message || 'Kayıt sırasında bir hata oluştu.');
            }
        })
        .catch(function() {
            showToast('error', 'Bağlantı Hatası', 'Sunucuya ulaşılamadı. Lütfen tekrar deneyin.');
        })
        .finally(function() {
            submitBtn.disabled = false;
            submitBtn.innerHTML = submitBtnOriginalHtml;
            document.getElementById('loadingOverlay').classList.remove('visible');
        });
    });
</script>
@endsection
