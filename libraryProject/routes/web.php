<?php

use App\Http\Controllers\EtiketController;
use App\Http\Controllers\OduncController;
use App\Http\Controllers\RezerveController;
use App\Http\Controllers\SoapController;
use App\Http\Controllers\UyeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DilController;
use App\Http\Controllers\KatalogParametreController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\KutuphaneController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\YazarController;
use App\Http\Controllers\YayineviController;
use Illuminate\Support\Facades\Storage;

// ─── Ana sayfa → girişli kullanıcıya katalog, değilse giriş ───────────────────
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('katalog.index')
        : redirect()->route('login');
});

// ─── Auth (giriş/çıkış — misafir) ─────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/giris',  [AuthController::class, 'loginForm'])->name('login');
    Route::post('/giris', [AuthController::class, 'login'])->name('auth.login.post');
    Route::get('/giris/2fa', [AuthController::class, 'twoFactorForm'])->name('auth.twofactor.form');
    Route::post('/giris/2fa', [AuthController::class, 'twoFactorVerify'])->name('auth.twofactor.verify');
    Route::post('/giris/2fa/yeniden-gonder', [AuthController::class, 'twoFactorResend'])->name('auth.twofactor.resend');
});

Route::post('/cikis', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('auth.logout');

// ─── Korumalı alan (giriş gerektirir) ─────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/sifre-degistir', [AuthController::class, 'passwordForm'])->name('auth.password.form');
    Route::post('/sifre-degistir', [AuthController::class, 'passwordUpdate'])->name('auth.password.update');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::get('/etiket',     [EtiketController::class, 'index'])->name('etiket.index');
    Route::get('/etiket/ara', [EtiketController::class, 'ara'])->name('etiket.ara');
    Route::post('/etiket/isaretle', [EtiketController::class, 'isaretle'])->name('etiket.isaretle');
    Route::get('/dil', [DilController::class, 'index'])->name('dil.index');
    Route::get('/dil/export', [DilController::class, 'export'])->name('dil.export');
    Route::post('/dil', [DilController::class, 'store'])->name('dil.store');
    Route::put('/dil/{dil}', [DilController::class, 'update'])->name('dil.update');
    Route::delete('/dil/{dil}', [DilController::class, 'destroy'])->name('dil.destroy');
    Route::get('/katalog-parametreler', [KatalogParametreController::class, 'index'])->name('katalog_parametre.index');
    Route::get('/katalog-parametreler/{tab}/list', [KatalogParametreController::class, 'list'])->name('katalog_parametre.list');
    Route::get('/katalog-parametreler/{tab}/export', [KatalogParametreController::class, 'export'])->name('katalog_parametre.export');
    Route::post('/katalog-parametreler/{tab}', [KatalogParametreController::class, 'store'])->name('katalog_parametre.store');
    Route::put('/katalog-parametreler/{tab}/{id}', [KatalogParametreController::class, 'update'])->name('katalog_parametre.update');
    Route::delete('/katalog-parametreler/{tab}/{id}', [KatalogParametreController::class, 'destroy'])->name('katalog_parametre.destroy');

    // Katalog
    Route::get('/katalog',              [KatalogController::class, 'index'])->name('katalog.index');
    Route::get('/katalog/new',          [KatalogController::class, 'new'])->name('katalog.new');
    Route::post('/katalog/new',         [KatalogController::class, 'store'])->name('katalog.store');
    Route::get('/katalog/isbn-search',  [KatalogController::class, 'isbnSearch'])->name('katalog.isbnSearch');
    Route::get('/katalog/cover-search', [KatalogController::class, 'coverSearch'])->name('katalog.coverSearch');
    // literal export rotası {kitap} wildcard'dan ÖNCE gelmeli
    Route::get('/katalog/export',       [KatalogController::class, 'export'])->name('katalog.export');
    Route::get('/katalog/{kitap}/copy', [KatalogController::class, 'copy'])->name('katalog.copy');
    Route::get('/katalog/{kitap}/view', [KatalogController::class, 'view'])->name('katalog.view');
    Route::get('/katalog/{kitap}/edit', [KatalogController::class, 'edit'])->name('katalog.edit');
    Route::post('/katalog/{kitap}/transfer-kutuphane', [KatalogController::class, 'transferKutuphane'])->name('katalog.transferKutuphane');
    Route::put('/katalog/{kitap}',      [KatalogController::class, 'update'])->name('katalog.update');
    Route::get('/yazarlar',             [YazarController::class, 'index'])->name('yazarlar.index');
    Route::get('/yazarlar/export',      [YazarController::class, 'export'])->name('yazarlar.export');
    Route::post('/yazarlar/merge',      [YazarController::class, 'merge'])->name('yazarlar.merge');
    Route::post('/yazarlar',            [YazarController::class, 'store'])->name('yazarlar.store');
    Route::put('/yazarlar/{yazar}',     [YazarController::class, 'update'])->name('yazarlar.update');
    Route::delete('/yazarlar/{yazar}',  [YazarController::class, 'destroy'])->name('yazarlar.destroy');

    Route::get('/yayinevleri',            [YayineviController::class, 'index'])->name('yayinevleri.index');
    Route::get('/yayinevleri/export',     [YayineviController::class, 'export'])->name('yayinevleri.export');
    Route::post('/yayinevleri/merge',     [YayineviController::class, 'merge'])->name('yayinevleri.merge');
    Route::post('/yayinevleri',            [YayineviController::class, 'store'])->name('yayinevleri.store');
    Route::put('/yayinevleri/{yayinevi}', [YayineviController::class, 'update'])->name('yayinevleri.update');
    Route::delete('/yayinevleri/{yayinevi}', [YayineviController::class, 'destroy'])->name('yayinevleri.destroy');

    // Kütüphane
    Route::get('/kutuphane',                  [KutuphaneController::class, 'index'])->name('kutuphane.index');
    Route::get('/kutuphane/export',           [KutuphaneController::class, 'export'])->name('kutuphane.export');
    Route::get('/kutuphane/new',              [KutuphaneController::class, 'new'])->name('kutuphane.new');
    Route::post('/kutuphane/new',             [KutuphaneController::class, 'store'])->name('kutuphane.store');
    Route::get('/kutuphane/{kutuphane}/edit', [KutuphaneController::class, 'edit'])->name('kutuphane.edit');
    Route::put('/kutuphane/{kutuphane}',      [KutuphaneController::class, 'update'])->name('kutuphane.update');

    Route::get('/kutuphane/{kutuphane}/yetkili',              [KutuphaneController::class, 'getYetkililer']);
    Route::get('/kutuphane/{kutuphane}/yetkili/search',       [KutuphaneController::class, 'searchUsers']);
    Route::post('/kutuphane/{kutuphane}/yetkili',             [KutuphaneController::class, 'addYetkili']);
    Route::delete('/kutuphane/{kutuphane}/yetkili/{yetkiliId}', [KutuphaneController::class, 'removeYetkili']);

    // Kullanıcılar
    Route::get('/kullanicilar',               [UserController::class, 'index'])->name('users.index');
    Route::get('/kullanicilar/new',           [UserController::class, 'new'])->name('users.new');
    Route::post('/kullanicilar/new',          [UserController::class, 'store'])->name('users.store');
    Route::get('/kullanicilar/{user}/edit',   [UserController::class, 'edit'])->name('users.edit');
    Route::put('/kullanicilar/{user}',        [UserController::class, 'update'])->name('users.update');
    Route::get('/kullanicilar/{user}/yetkiler',  [UserController::class, 'yetkilerForm'])->name('users.yetkiler');
    Route::post('/kullanicilar/{user}/yetkiler', [UserController::class, 'yetkilerUpdate'])->name('users.yetkiler.update');
    Route::get('/kullanicilar/{user}/yetkili',              [UserController::class, 'getYetkiliKutuphaneler']);
    Route::get('/kullanicilar/{user}/yetkili/search',       [UserController::class, 'searchKutuphaneler']);
    Route::post('/kullanicilar/{user}/yetkili',             [UserController::class, 'addYetkiliKutuphane']);
    Route::delete('/kullanicilar/{user}/yetkili/{yetkiliId}', [UserController::class, 'removeYetkiliKutuphane']);
    Route::post('/kullanicilar/ldap/search', [UserController::class, 'ldapSearchUsers'])->name('users.ldap.search');
    Route::get('/kullanicilar/tablo',  [UserController::class, 'tableData']);
    Route::get('/kullanicilar/export', [UserController::class, 'export']);

    // Üyeler
    Route::get('/uyeler',        [UyeController::class, 'index'])->name('uyeler.index');
    Route::get('/uyeler/new',    [UyeController::class, 'new'])->name('uyeler.new');
    Route::post('/uyeler/new',   [UyeController::class, 'store'])->name('uyeler.store');
    Route::get('/uyeler/tablo',  [UyeController::class, 'tableData'])->name('uyeler.tableData');
    Route::get('/uyeler/export', [UyeController::class, 'export'])->name('uyeler.export');
    // wildcard rotalar en sona
    Route::get('/uyeler/{uye}/edit',                         [UyeController::class, 'edit'])->name('uyeler.edit');
    Route::put('/uyeler/{uye}',                              [UyeController::class, 'update'])->name('uyeler.update');
    Route::delete('/uyeler/{uye}',                           [UyeController::class, 'destroy'])->name('uyeler.destroy');
    Route::post('/uyeler/{uye}/kimlik-guncelle',             [UyeController::class, 'kimlikGuncelle'])->name('uyeler.kimlikGuncelle');

    // OTP (telefon doğrulama)
    Route::post('/otp/gonder',   [UyeController::class, 'otpGonder'])->name('otp.gonder');
    Route::post('/otp/dogrula',  [UyeController::class, 'otpDogrula'])->name('otp.dogrula');

    Route::post('/kps/adres-sorgula', [\App\Http\Controllers\KpsController::class, 'adresSorgulaHttp'])->name('kps.adresSorgula');
    Route::post('/kps/kimlik-sorgula', [\App\Http\Controllers\KpsController::class, 'kimlikSorgulaHttp'])->name('kps.kimlikSorgula');

    // Ödünç — AJAX arama rotaları önce gelmeli (wildcard çakışmasını önlemek için)
    Route::get('/rezerve',                 [RezerveController::class, 'index'])->name('rezerve.index');
    Route::get('/rezerve/tablo',           [RezerveController::class, 'tableData'])->name('rezerve.tableData');
    Route::post('/rezerve',                [RezerveController::class, 'store'])->name('rezerve.store');
    Route::post('/rezerve/{rezerve}/iptal',[RezerveController::class, 'cancel'])->name('rezerve.cancel');

    Route::get('/odunc/ara/uye',           [OduncController::class, 'uyeAra'])->name('odunc.uyeAra');
    Route::get('/odunc/ara/kitap',         [OduncController::class, 'kitapAra'])->name('odunc.kitapAra');
    Route::get('/odunc/tablo',             [OduncController::class, 'tableData'])->name('odunc.tableData');
    Route::get('/odunc/export',            [OduncController::class, 'export'])->name('odunc.export');
    Route::get('/odunc',                   [OduncController::class, 'index'])->name('odunc.index');
    Route::get('/odunc/new',               [OduncController::class, 'new'])->name('odunc.new');
    Route::post('/odunc/new',              [OduncController::class, 'store'])->name('odunc.store');
    Route::get('/odunc/{islem}',           [OduncController::class, 'show'])->name('odunc.show');
    Route::get('/odunc/{islem}/iade-form', [OduncController::class, 'iadeForm'])->name('odunc.iadeForm');
    Route::post('/odunc/{islem}/iade',     [OduncController::class, 'iade'])->name('odunc.iade');
    Route::post('/odunc/{islem}/sure-uzat', [OduncController::class, 'sureUzat'])->name('odunc.sureUzat');
});

// public/storage sembolik bağlantısı yoksa bile kapak görsellerinin açılması için (Storage disk: public)
Route::get('/storage/kapaklar/{file}', function (string $file) {
    if (!preg_match('/^[A-Za-z0-9._-]+$/', $file)) {
        abort(404);
    }
    $relative = 'kapaklar/' . $file;
    if (!Storage::disk('public')->exists($relative)) {
        abort(404);
    }

    return Storage::disk('public')->response($relative);
})->where('file', '[A-Za-z0-9._-]+');

Route::get('/storage/yazarlar/{file}', function (string $file) {
    if (!preg_match('/^[A-Za-z0-9._-]+$/', $file)) {
        abort(404);
    }
    $relative = 'yazarlar/' . $file;
    if (!Storage::disk('public')->exists($relative)) {
        abort(404);
    }

    return Storage::disk('public')->response($relative);
})->where('file', '[A-Za-z0-9._-]+');


Route::match(['get', 'post'], '/soap/katalog', [SoapController::class, 'handle'])
    ->name('soap.katalog');
