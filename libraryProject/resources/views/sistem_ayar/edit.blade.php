@extends('layouts.base')

@section('title', 'Sistem Ayarları')

@section('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <style>
        .form-card { border: 1px solid rgba(217,208,194,0.6); border-radius: var(--radius); background: var(--card); box-shadow: 0 1px 3px rgba(0,0,0,0.04); max-width: 860px; }
        .form-card-header { padding: 24px 24px 16px; }
        .form-card-title { display: flex; align-items: center; gap: 8px; font-family: var(--font-serif); font-size: 20px; font-weight: 700; }
        .form-card-title svg { width: 20px; height: 20px; color: var(--primary); }
        .form-card-desc { font-size: 14px; color: var(--muted-foreground); margin-top: 4px; }
        .form-card-separator { height: 1px; background: var(--border); }
        .form-card-body { padding: 24px; display: flex; flex-direction: column; gap: 20px; }

        .section-header { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted-foreground); margin-bottom: 14px; }
        .section-number { width: 20px; height: 20px; border-radius: 4px; background: rgba(122,92,60,0.1); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: var(--primary); }
        .section-sep { height: 1px; background: var(--border); }

        .form-grid { display: grid; gap: 16px; }
        .form-grid.cols-2 { grid-template-columns: repeat(2, 1fr); }
        .form-field { display: flex; flex-direction: column; }
        .form-label { font-size: 14px; font-weight: 500; color: var(--foreground); margin-bottom: 6px; }
        .form-input, .form-textarea { width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: calc(var(--radius) - 2px); background: var(--card); color: var(--foreground); font-size: 14px; line-height: 1.5; transition: border-color 0.15s, box-shadow 0.15s; outline: none; }
        .form-input::placeholder, .form-textarea::placeholder { color: var(--muted-foreground); opacity: 0.7; }
        .form-input:focus, .form-textarea:focus { border-color: var(--ring); box-shadow: 0 0 0 2px rgba(122,92,60,0.15); }
        .form-textarea { resize: vertical; }
        .form-hint { font-size: 12px; color: var(--muted-foreground); margin-top: 4px; }
        .form-error { font-size: 12px; color: var(--destructive); margin-top: 4px; }

        .logo-row { display: flex; gap: 16px; align-items: flex-start; }
        .logo-preview-wrap { width: 120px; height: 120px; border: 1px dashed var(--border); border-radius: calc(var(--radius) - 2px); background: var(--secondary); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; }
        .logo-preview-wrap img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .logo-preview-empty { font-size: 12px; color: var(--muted-foreground); text-align: center; padding: 8px; }
        .logo-actions { display: flex; flex-direction: column; gap: 8px; flex: 1; min-width: 0; }
        .logo-file-input { font-size: 13px; }
        .btn-text { background: none; border: none; color: var(--destructive); font-size: 13px; cursor: pointer; padding: 0; align-self: flex-start; }
        .btn-text:hover { text-decoration: underline; }
        .btn-text[hidden] { display: none; }

        .form-actions { display: flex; justify-content: flex-end; gap: 12px; padding-top: 8px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 9px 16px; border-radius: calc(var(--radius) - 2px); font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.15s, opacity 0.15s; border: none; text-decoration: none; }
        .btn svg { width: 16px; height: 16px; }
        .btn-primary { background: var(--primary); color: var(--primary-foreground); }
        .btn-primary:hover { opacity: 0.9; }
        .btn-outline { background: transparent; color: var(--foreground); border: 1px solid var(--border); }
        .btn-outline:hover { background: var(--muted); }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }

        .loading-overlay { position: fixed; inset: 0; z-index: 2000; background: rgba(61,50,38,0.45); backdrop-filter: blur(3px); display: flex; align-items: center; justify-content: center; opacity: 0; visibility: hidden; transition: opacity 0.2s ease, visibility 0.2s ease; }
        .loading-overlay.visible { opacity: 1; visibility: visible; }
        .loading-box { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 40px 56px; display: flex; flex-direction: column; align-items: center; gap: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.25); transform: scale(0.92); transition: transform 0.2s ease; }
        .loading-overlay.visible .loading-box { transform: scale(1); }
        .loading-spinner { width: 48px; height: 48px; border: 3px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.75s linear infinite; }
        .loading-text { font-size: 15px; font-weight: 600; color: var(--foreground); }
        .loading-subtext { font-size: 13px; color: var(--muted-foreground); margin-top: -12px; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        .toast-container { position: fixed; top: 16px; right: 16px; z-index: 3000; display: flex; flex-direction: column; gap: 8px; }
        .toast { padding: 14px 20px; border-radius: 10px; font-size: 14px; font-weight: 500; box-shadow: 0 4px 12px rgba(0,0,0,0.15); animation: toast-in 0.3s ease; max-width: 380px; }
        .toast.success { background: #2f7d32; color: white; }
        .toast.error { background: var(--destructive); color: white; }
        .toast-desc { font-size: 13px; font-weight: 400; opacity: 0.9; margin-top: 2px; }
        @keyframes toast-in { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes toast-out { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }

        @media (max-width: 768px) {
            .form-grid.cols-2 { grid-template-columns: 1fr; }
            .form-actions { flex-direction: column-reverse; }
            .logo-row { flex-direction: column; }
        }
    </style>
@endsection

@section('breadcrumb')
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <span class="breadcrumb-current">Sistem Ayarları</span>
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
    <form id="sistemAyarForm" class="form-card" method="POST" action="{{ route('sistem_ayar.update') }}" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')
        <input type="hidden" name="logo_kaldir" id="logoKaldir" value="0" />

        <div class="form-card-header">
            <h2 class="form-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                Sistem Ayarları
            </h2>
            <p class="form-card-desc">Kurum bilgileri, iletişim ve erişim ayarlarını yönetin.</p>
        </div>
        <div class="form-card-separator"></div>

        <div class="form-card-body">

            <div>
                <h3 class="section-header">
                    <span class="section-number">1</span>
                    Kurum Bilgileri
                </h3>
                <div class="form-grid cols-2">
                    <div class="form-field" style="grid-column: span 2;">
                        <label class="form-label" for="logo">Logo</label>
                        <div class="logo-row">
                            <div class="logo-preview-wrap" id="logoPreviewWrap">
                                @if($ayar->logo_url)
                                    <img src="{{ $ayar->logo_url }}" alt="Logo" id="logoPreviewImg" />
                                @else
                                    <span class="logo-preview-empty" id="logoPreviewEmpty">Logo yok</span>
                                @endif
                            </div>
                            <div class="logo-actions">
                                <input type="file" class="form-input logo-file-input" id="logo" name="logo"
                                       accept=".jpg,.jpeg,.png,.webp,.svg,image/jpeg,image/png,image/webp,image/svg+xml" />
                                <p class="form-hint">JPG, PNG, WEBP veya SVG. En fazla 3MB.</p>
                                <button type="button" class="btn-text" id="logoKaldirBtn" @if(!$ayar->logo_url) hidden @endif>Logoyu kaldır</button>
                                @error('logo')<span class="form-error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-field" style="grid-column: span 2;">
                        <label class="form-label" for="kurum_adi">Kurum Adı</label>
                        <input type="text" class="form-input" id="kurum_adi" name="kurum_adi"
                               placeholder="Örnek: Örnektepe Kültür Müdürlüğü"
                               value="{{ old('kurum_adi', $ayar->kurum_adi) }}" />
                        @error('kurum_adi')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-field" style="grid-column: span 2;">
                        <label class="form-label" for="web_sitesi">Web Sitesi</label>
                        <input type="text" class="form-input" id="web_sitesi" name="web_sitesi"
                               placeholder="Örnek: https://www.ornek.gov.tr"
                               value="{{ old('web_sitesi', $ayar->web_sitesi) }}" />
                        @error('web_sitesi')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="section-sep"></div>

            <div>
                <h3 class="section-header">
                    <span class="section-number">2</span>
                    İletişim Bilgileri
                </h3>
                <div class="form-grid cols-2">
                    <div class="form-field">
                        <label class="form-label" for="is_telefonu">İş Telefonu</label>
                        <input type="text" class="form-input" id="is_telefonu" name="is_telefonu"
                               placeholder="Örnek: 0212 555 00 00"
                               value="{{ old('is_telefonu', $ayar->is_telefonu) }}" />
                        @error('is_telefonu')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="cep_telefonu">Cep Telefonu</label>
                        <input type="text" class="form-input" id="cep_telefonu" name="cep_telefonu"
                               placeholder="Örnek: 0532 555 00 00"
                               value="{{ old('cep_telefonu', $ayar->cep_telefonu) }}" />
                        @error('cep_telefonu')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-field" style="grid-column: span 2;">
                        <label class="form-label" for="eposta">E-posta Adresi</label>
                        <input type="email" class="form-input" id="eposta" name="eposta"
                               placeholder="Örnek: bilgi@ornek.gov.tr"
                               value="{{ old('eposta', $ayar->eposta) }}" />
                        @error('eposta')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="section-sep"></div>

            <div>
                <h3 class="section-header">
                    <span class="section-number">3</span>
                    Adres Bilgileri
                </h3>
                <div class="form-grid cols-2">
                    <div class="form-field">
                        <label class="form-label" for="il">İl</label>
                        <input type="text" class="form-input" id="il" name="il"
                               placeholder="İl adı"
                               value="{{ old('il', $ayar->il) }}" />
                        @error('il')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="ilce">İlçe</label>
                        <input type="text" class="form-input" id="ilce" name="ilce"
                               placeholder="İlçe adı"
                               value="{{ old('ilce', $ayar->ilce) }}" />
                        @error('ilce')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-field" style="grid-column: span 2;">
                        <label class="form-label" for="adres">Adres</label>
                        <textarea class="form-textarea" id="adres" name="adres"
                                  placeholder="Kurumun tam adresi" rows="3">{{ old('adres', $ayar->adres) }}</textarea>
                        @error('adres')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="section-sep"></div>

            <div>
                <h3 class="section-header">
                    <span class="section-number">4</span>
                    Güvenlik
                </h3>
                <div class="form-grid">
                    <div class="form-field">
                        <label class="form-label" for="izinli_ip_adresleri">İzinli IP Adresleri</label>
                        <textarea class="form-textarea" id="izinli_ip_adresleri" name="izinli_ip_adresleri"
                                  placeholder="Her satıra bir IP yazın&#10;Örnek:&#10;192.168.1.10&#10;10.0.0.5"
                                  rows="5">{{ old('izinli_ip_adresleri', $ayar->izinli_ip_adresleri) }}</textarea>
                        <p class="form-hint">Birden fazla IP için satır, virgül veya boşluk kullanabilirsiniz.</p>
                        @error('izinli_ip_adresleri')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="form-actions">
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
(function () {
    function showToast(type, title, desc) {
        var c = document.getElementById('toastContainer');
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<div>' + title + '</div>' + (desc ? '<div class="toast-desc">' + desc + '</div>' : '');
        c.appendChild(t);
        setTimeout(function () {
            t.style.animation = 'toast-out 0.3s ease forwards';
            setTimeout(function () { if (t.parentNode) t.parentNode.removeChild(t); }, 300);
        }, 3500);
    }

    var form = document.getElementById('sistemAyarForm');
    var submitBtn = document.getElementById('submitBtn');
    var submitBtnOriginalHtml = submitBtn.innerHTML;
    var logoInput = document.getElementById('logo');
    var logoKaldir = document.getElementById('logoKaldir');
    var logoKaldirBtn = document.getElementById('logoKaldirBtn');
    var previewWrap = document.getElementById('logoPreviewWrap');
    var existingLogoUrl = @json($ayar->logo_url);

    function setPreview(url) {
        previewWrap.innerHTML = '';
        if (url) {
            var img = document.createElement('img');
            img.src = url;
            img.alt = 'Logo';
            img.id = 'logoPreviewImg';
            previewWrap.appendChild(img);
            logoKaldirBtn.hidden = false;
        } else {
            var empty = document.createElement('span');
            empty.className = 'logo-preview-empty';
            empty.id = 'logoPreviewEmpty';
            empty.textContent = 'Logo yok';
            previewWrap.appendChild(empty);
            logoKaldirBtn.hidden = true;
        }
    }

    logoInput.addEventListener('change', function () {
        var file = logoInput.files && logoInput.files[0];
        if (!file) {
            setPreview(logoKaldir.value === '1' ? null : existingLogoUrl);
            return;
        }
        logoKaldir.value = '0';
        var reader = new FileReader();
        reader.onload = function (e) { setPreview(e.target.result); };
        reader.readAsDataURL(file);
    });

    logoKaldirBtn.addEventListener('click', function () {
        logoInput.value = '';
        logoKaldir.value = '1';
        existingLogoUrl = null;
        setPreview(null);
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();

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
        .then(function (res) {
            return res.json().then(function (data) { return { status: res.status, data: data }; });
        })
        .then(function (result) {
            if (result.status === 200 && result.data.success) {
                showToast('success', 'Kaydedildi', result.data.message);
                if (result.data.logo_url) {
                    existingLogoUrl = result.data.logo_url;
                    logoKaldir.value = '0';
                    logoInput.value = '';
                    setPreview(existingLogoUrl);
                } else if (logoKaldir.value === '1') {
                    existingLogoUrl = null;
                    logoKaldir.value = '0';
                    setPreview(null);
                }
            } else if (result.status === 422 && result.data.errors) {
                var msgs = Object.values(result.data.errors).flat();
                showToast('error', 'Doğrulama Hatası', msgs[0]);
            } else {
                showToast('error', 'Hata', (result.data && result.data.message) || 'Kayıt sırasında bir hata oluştu.');
            }
        })
        .catch(function () {
            showToast('error', 'Bağlantı Hatası', 'Sunucuya ulaşılamadı. Lütfen tekrar deneyin.');
        })
        .finally(function () {
            submitBtn.disabled = false;
            submitBtn.innerHTML = submitBtnOriginalHtml;
            document.getElementById('loadingOverlay').classList.remove('visible');
        });
    });

    @if(session('success'))
        showToast('success', 'Kaydedildi', @json(session('success')));
    @endif
})();
</script>
@endsection
