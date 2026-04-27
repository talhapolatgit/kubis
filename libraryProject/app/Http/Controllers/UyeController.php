<?php

namespace App\Http\Controllers;

use App\Models\Uye;
use App\Models\User;
use App\Services\OtpService;
use App\Services\BeyogluWebhookService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UyeController extends Controller
{
    public function __construct(
        private readonly OtpService $otp,
        private readonly BeyogluWebhookService $webhookService
    ) {}

    // ─── Liste Sayfası (sadece view, veri AJAX ile yükleniyor) ──────────────────
    public function index()
    {
        abort_unless(auth()->user()?->hasYetki(11) || auth()->user()?->hasYetki(13), 403);
        return view('uye.list');
    }

    // ─── AJAX Tablo Verisi ───────────────────────────────────────────────────────
    // GET /uyeler/tablo?search=&statu=&per_page=20&page=1
    public function tableData(Request $request)
    {
        abort_unless(auth()->user()?->hasYetki(11) || auth()->user()?->hasYetki(13), 403);
        $perPage = in_array((int) $request->input('per_page'), [10, 20, 50, 100, 500])
            ? (int) $request->input('per_page')
            : 10;
        $search = trim($request->input('search', ''));
        $statu  = $request->input('statu', '');

        $query = Uye::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('ad',         'LIKE', "%{$search}%")
                    ->orWhere('soyad',    'LIKE', "%{$search}%")
                    ->orWhere('tc_kimlik','LIKE', "%{$search}%")
                    ->orWhere('telefon',  'LIKE', "%{$search}%")
                    ->orWhere('email',    'LIKE', "%{$search}%");
            });
        }

        if ($statu !== null) {
            $query->where('statu', $statu);
        }

        $uyeler = $query->orderBy('id', 'desc')->paginate($perPage);

        // collect($uyeler->items()) — plain array koleksiyonu, paginator objesi değil
        $items = collect($uyeler->items())
            ->map(function ($uye) {
                return [
                    'id'                 => $uye->id,
                    'ad'                 => $uye->ad,
                    'soyad'              => $uye->soyad,
                    'ad_soyad'           => $uye->ad . ' ' . $uye->soyad,
                    'initials'           => $uye->initials
                        ?? mb_strtoupper(mb_substr($uye->ad, 0, 1) . mb_substr($uye->soyad, 0, 1)),
                    'tc_kimlik'          => $uye->tc_kimlik,
                    'email'              => $uye->email,
                    'telefon'            => $uye->telefon,
                    'telefon_dogrulandi' => (bool) $uye->telefon_dogrulandi,
                    'il'                 => $uye->il,
                    'ilce'               => $uye->ilce,
                    'statu'              => $uye->statu,
                    'statu_label'        => $uye->statu_label
                        ?? ($uye->statu === 'aktif' ? 'Aktif' : 'Pasif'),
                    'uyelik_baslangic'   => $uye->uyelik_baslangic
                        ? Carbon::parse($uye->uyelik_baslangic)->format('d.m.Y')
                        : '—',
                    'edit_url'           => route('uyeler.edit', $uye->id),
                    'delete_url'         => '/uyeler/' . $uye->id,
                ];
            })
            ->values()   // sequential 0-indexed keys
            ->all();     // plain PHP array → JSON array garantisi

        return response()->json([
            'success' => true,
            'data'    => $items,
            'meta'    => [
                'current_page' => $uyeler->currentPage(),
                'last_page'    => $uyeler->lastPage(),
                'per_page'     => $uyeler->perPage(),
                'total'        => $uyeler->total(),
                'from'         => $uyeler->firstItem() ?? 0,
                'to'           => $uyeler->lastItem() ?? 0,
            ],
        ]);
    }

    // ─── CSV / Excel İndir ───────────────────────────────────────────────────────
    // GET /uyeler/export?search=&statu=
    public function export(Request $request)
    {
        abort_unless(auth()->user()?->hasYetki(11), 403);
        $search = trim($request->input('search', ''));
        $statu  = $request->input('statu', '');

        $query = Uye::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('ad',         'LIKE', "%{$search}%")
                    ->orWhere('soyad',    'LIKE', "%{$search}%")
                    ->orWhere('tc_kimlik','LIKE', "%{$search}%")
                    ->orWhere('telefon',  'LIKE', "%{$search}%")
                    ->orWhere('email',    'LIKE', "%{$search}%");
            });
        }

        if ($statu !== null) {
            $query->where('statu', $statu);
        }

        $uyeler   = $query->orderBy('id', 'desc')->get();
        $filename = 'uyeler_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($uyeler) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM — Türkçe için
            fputcsv($out, [
                '#', 'Ad', 'Soyad', 'TC Kimlik No', 'E-posta', 'Telefon', '2. Telefon',
                'Tel. Doğrulandı', 'İl', 'İlçe', 'Durum',
                'Üyelik Başlangıç', 'Üyelik Bitiş',
                'Veli Adı', 'Veli Soyadı', 'Veli TC', 'Veli Telefon',
                'Öğrenim Durumu', 'Okul Adı', 'Bölüm Adı',
            ], ';');

            foreach ($uyeler as $uye) {
                fputcsv($out, [
                    $uye->id,
                    $uye->ad,
                    $uye->soyad,
                    $uye->tc_kimlik,
                    $uye->email ?: '—',
                    $uye->telefon,
                    $uye->telefon2 ?: '—',
                    $uye->telefon_dogrulandi ? 'Evet' : 'Hayır',
                    $uye->il   ?: '—',
                    $uye->ilce ?: '—',
                    $uye->statu === 'aktif' ? 'Aktif' : 'Pasif',
                    $uye->uyelik_baslangic
                        ? Carbon::parse($uye->uyelik_baslangic)->format('d.m.Y') : '—',
                    $uye->uyelik_bitis
                        ? Carbon::parse($uye->uyelik_bitis)->format('d.m.Y') : '—',
                    $uye->veli_ad    ?: '—',
                    $uye->veli_soyad ?: '—',
                    $uye->veli_tc_kimlik ?: '—',
                    $uye->veli_telefon   ?: '—',
                    $uye->ogretim_durumu ?: '—',
                    $uye->okul_adi       ?: '—',
                    $uye->bolum_adi      ?: '—',
                ], ';');
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ─── Yeni Form ──────────────────────────────────────────────────────────────
    public function new()
    {
        abort_unless(auth()->user()?->hasYetki(12), 403);
        return view('uye.new');
    }

    // ─── OTP Gönder ─────────────────────────────────────────────────────────────
    public function otpGonder(Request $request)
    {
        $request->validate([
            'telefon' => ['required', 'string', 'regex:/^(0|\+90|90)?5\d{9}$/'],
        ], [
            'telefon.required' => 'Telefon numarası zorunludur.',
            'telefon.regex'    => 'Geçerli bir Türkiye cep numarası girin (05xxxxxxxxx).',
        ]);

        $telefon = $request->input('telefon');
        $code    = $this->otp->generate($telefon);
        $sent    = $this->otp->send($telefon, $code);

        if (!$sent) {
            return response()->json(['success' => false, 'message' => 'SMS gönderilemedi. Lütfen tekrar deneyin.'], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Doğrulama kodu ' . $this->maskPhone($telefon) . ' numarasına gönderildi.',
            'ttl'     => OtpService::TTL_MINUTES * 60,
        ]);
    }

    public function otpDogrula(Request $request)
    {
        $request->validate([
            'telefon' => ['required', 'string'],
            'kod'     => ['required', 'string', 'size:6'],
        ]);

        $telefon = $request->input('telefon');
        $kod     = $request->input('kod');

        if (!$this->otp->verify($telefon, $kod)) {
            return response()->json(['success' => false, 'message' => 'Doğrulama kodu hatalı veya süresi dolmuş.'], 422);
        }

        session(['otp_verified_phone' => $telefon]);

        return response()->json(['success' => true, 'message' => 'Telefon numarası başarıyla doğrulandı.']);
    }

    // ─── Kaydet (Yeni Üye) ──────────────────────────────────────────────────────
    public function store(Request $request)
    {
        abort_unless(auth()->user()?->hasYetki(12), 403);
        // ── Temel validasyon ──────────────────────────────────────────────────────
        $request->validate([
            'tc_kimlik'        => ['required', 'digits:11', 'unique:uyeler,tc_kimlik'],
            'dogum_tarihi'     => ['required', 'date', 'before:today'],
            'ad'               => ['required', 'string', 'max:100'],
            'soyad'            => ['required', 'string', 'max:100'],
            'cinsiyet'         => ['nullable', 'string', 'in:erkek,kadin,diger'],
            'email'            => ['nullable', 'email', 'max:255'],
            'telefon'          => ['required', 'string', 'max:20'],
            'il'               => ['nullable', 'string', 'max:100'],
            'ilce'             => ['nullable', 'string', 'max:100'],
            'mahalle'          => ['nullable', 'string', 'max:150'],
            'acik_adres'       => ['nullable', 'string', 'max:1000'],
            'statu'            => ['required', 'in:aktif,pasif'],
            'uyelik_baslangic' => ['nullable', 'date'],
            'uyelik_bitis'     => ['nullable', 'date', 'after_or_equal:uyelik_baslangic'],
            'notlar'           => ['nullable', 'string', 'max:2000'],
            'telefon2'         => ['nullable', 'string', 'max:20'],
            'ogretim_durumu'   => ['nullable', 'string', 'in:İlkokul,Ortaokul,Lise,Önlisans,Lisans,Yüksek Lisans,Doktora'],
            'okul_adi'         => ['nullable', 'string', 'max:200'],
            'bolum_adi'        => ['nullable', 'string', 'max:200'],
        ], [
            'tc_kimlik.required'          => 'TC Kimlik No zorunludur.',
            'tc_kimlik.digits'            => 'TC Kimlik No 11 rakamdan oluşmalıdır.',
            'tc_kimlik.unique'            => 'Bu TC Kimlik No zaten kayıtlı.',
            'dogum_tarihi.required'       => 'Doğum tarihi zorunludur.',
            'dogum_tarihi.before'         => 'Doğum tarihi geçmişte olmalıdır.',
            'ad.required'                 => 'Ad zorunludur.',
            'soyad.required'              => 'Soyad zorunludur.',
            'email.email'                 => 'Geçerli bir e-posta adresi girin.',
            'telefon.required'            => 'Telefon numarası zorunludur.',
            'statu.required'              => 'Üyelik durumu seçilmelidir.',
            'uyelik_bitis.after_or_equal' => 'Bitiş tarihi başlangıç tarihinden önce olamaz.',
        ]);

        // ── Yaş hesaplama & veli validasyonu ─────────────────────────────────────
        $yas      = Carbon::parse($request->input('dogum_tarihi'))->age;
        $isMinor  = $yas < 18;

        if ($isMinor) {
            $request->validate([
                'veli_ad'           => ['required', 'string', 'max:100'],
                'veli_soyad'        => ['required', 'string', 'max:100'],
                'veli_tc_kimlik'    => ['required', 'digits:11'],
                'veli_dogum_tarihi' => ['required', 'date', 'before:today'],
                'veli_telefon'      => ['required', 'string', 'max:20'],
            ], [
                'veli_ad.required'           => 'Veli adı zorunludur.',
                'veli_soyad.required'        => 'Veli soyadı zorunludur.',
                'veli_tc_kimlik.required'    => 'Veli TC Kimlik No zorunludur.',
                'veli_tc_kimlik.digits'      => 'Veli TC Kimlik No 11 rakamdan oluşmalıdır.',
                'veli_dogum_tarihi.required' => 'Veli doğum tarihi zorunludur.',
                'veli_dogum_tarihi.before'   => 'Veli doğum tarihi geçmişte olmalıdır.',
                'veli_telefon.required'      => 'Veli telefon numarası zorunludur.',
            ]);
        }

        // ── Telefon doğrulama kontrolü ────────────────────────────────────────────
        $verifiedPhone    = session('otp_verified_phone');
        $telefonDogrulandi = ($verifiedPhone && $verifiedPhone === $request->input('telefon'));

        if (!$telefonDogrulandi) {
            return back()->withInput()->withErrors(['telefon' => 'Telefon numarası doğrulanmamış. Lütfen doğrulama kodunu tamamlayın.']);
        }

        // ── KPS kimlik sorgusu ────────────────────────────────────────────────────
        $kpsController = new KpsController();
        $sonuc = $kpsController->kimlikSorgula(
            $request->input('dogum_tarihi'),
            $request->input('tc_kimlik')
        );

        if (isset($sonuc['success']) && $sonuc['success'] === true) {
            $kisi  = $sonuc['sbsKisiDto'];
            $ad    = $kisi['adi'];
            $soyad = $kisi['soyadi'];
            $kpsCinsiyet = $this->normalizeKpsCinsiyet($kisi['cinsiyeti'] ?? $kisi['cinsiyet'] ?? null);
        } else {
            $errorText = 'Kimlik doğrulama başarısız: ';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorText], 422);
            }
            return back()->withInput()->withErrors(['tc_kimlik' => $errorText]);
        }

        // ── Kaydet ───────────────────────────────────────────────────────────────
        // NOT: Uye modelinin $fillable dizisine veli alanlarını da eklemeyi unutmayın:
        // 'veli_ad', 'veli_soyad', 'veli_tc_kimlik', 'veli_dogum_tarihi', 'veli_telefon'
        $uye = Uye::create([
            'tc_kimlik'          => $request->input('tc_kimlik'),
            'dogum_tarihi'       => $request->input('dogum_tarihi'),
            'ad'                 => $ad,
            'soyad'              => $soyad,
            'cinsiyet'           => $kpsCinsiyet ?? ($request->input('cinsiyet') ?: null),
            'email'              => $request->input('email'),
            'telefon'            => $request->input('telefon'),
            'telefon_dogrulandi' => true,
            'il'                 => $request->input('il'),
            'ilce'               => $request->input('ilce'),
            'mahalle'            => $request->input('mahalle'),
            'acik_adres'         => $request->input('acik_adres'),
            'statu'              => $request->input('statu', 'aktif'),
            'uyelik_baslangic'   => $request->input('uyelik_baslangic') ?: now()->toDateString(),
            'uyelik_bitis'       => $request->input('uyelik_bitis'),
            'notlar'             => $request->input('notlar'),
            'created_user'       => auth()->id(),
            // Veli bilgileri — sadece reşit olmayan üyelerde dolu olur
            'veli_ad'            => $isMinor ? $request->input('veli_ad')           : null,
            'veli_soyad'         => $isMinor ? $request->input('veli_soyad')        : null,
            'veli_tc_kimlik'     => $isMinor ? $request->input('veli_tc_kimlik')    : null,
            'veli_dogum_tarihi'  => $isMinor ? $request->input('veli_dogum_tarihi') : null,
            'veli_telefon'       => $isMinor ? $request->input('veli_telefon')      : null,
            // Ek iletişim & eğitim bilgileri
            'telefon2'           => $request->input('telefon2') ?: null,
            'ogretim_durumu'     => $request->input('ogretim_durumu') ?: null,
            'okul_adi'           => $request->input('okul_adi') ?: null,
            'bolum_adi'          => $request->input('bolum_adi') ?: null,
        ]);

        session()->forget('otp_verified_phone');

        try {
            $result = $this->webhookService->sendBildirim(
                tcList:  [$uye->tc_kimlik],
                title:   'Kütüphane Üyeliğin Oluşturuldu 😊',
                message: 'Kütüphanemizdeki kitapları keşfetmek için tıkla ve ödünç almak istediğin kitabı hemen rezerve et.',
            );

        } catch (\Exception $e) {
            // İade işlemi tamamlandı, sadece bildirim başarısız
            Log::error('Webhook gönderilemedi: ' . $e->getMessage());

        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => '"' . $uye->ad_soyad . '" başarıyla üye olarak eklendi.',
                'id'      => $uye->id,
            ]);
        }

        return redirect()->route('uyeler.index')
            ->with('success', '"' . $uye->ad_soyad . '" başarıyla üye olarak eklendi.');
    }

    // ─── Düzenle Form ───────────────────────────────────────────────────────────
    public function edit(Uye $uye)
    {
        abort_unless(auth()->user()?->hasYetki(13), 403);
        $isMinor = Carbon::parse($uye->dogum_tarihi)->age < 18;
        $createdUser = $uye->created_user ? User::find($uye->created_user) : null;
        $updatedUser = $uye->updated_user ? User::find($uye->updated_user) : null;

        return view('uye.edit', compact('uye', 'isMinor', 'createdUser', 'updatedUser'));
    }

    // ─── Güncelle ───────────────────────────────────────────────────────────────
    public function update(Request $request, Uye $uye)
    {
        abort_unless(auth()->user()?->hasYetki(13), 403);
        // ── Temel validasyon ──────────────────────────────────────────────────────
        $request->validate([
            'email'            => ['nullable', 'email', 'max:255'],
            'telefon'          => ['required', 'string', 'max:20'],
            'il'               => ['nullable', 'string', 'max:100'],
            'ilce'             => ['nullable', 'string', 'max:100'],
            'mahalle'          => ['nullable', 'string', 'max:150'],
            'acik_adres'       => ['nullable', 'string', 'max:1000'],
            'statu'            => ['required', 'in:aktif,pasif'],
            'cinsiyet'         => ['nullable', 'string', 'in:erkek,kadin,diger'],
            'uyelik_baslangic' => ['nullable', 'date'],
            'uyelik_bitis'     => ['nullable', 'date', 'after_or_equal:uyelik_baslangic'],
            'notlar'           => ['nullable', 'string', 'max:2000'],
            'telefon2'         => ['nullable', 'string', 'max:20'],
            'ogretim_durumu'   => ['nullable', 'string', 'in:İlkokul,Ortaokul,Lise,Önlisans,Lisans,Yüksek Lisans,Doktora'],
            'okul_adi'         => ['nullable', 'string', 'max:200'],
            'bolum_adi'        => ['nullable', 'string', 'max:200'],
        ], [
            'email.email'                 => 'Geçerli bir e-posta adresi girin.',
            'telefon.required'            => 'Telefon numarası zorunludur.',
            'statu.required'              => 'Üyelik durumu seçilmelidir.',
            'uyelik_bitis.after_or_equal' => 'Bitiş tarihi başlangıç tarihinden önce olamaz.',
        ]);

        // ── Yaş hesaplama & veli validasyonu ─────────────────────────────────────
        // Doğum tarihi edit formunda değiştirilemez, $uye->dogum_tarihi üzerinden hesapla
        $yas     = Carbon::parse($uye->dogum_tarihi)->age;
        $isMinor = $yas < 18;

        if ($isMinor) {
            $request->validate([
                'veli_ad'           => ['required', 'string', 'max:100'],
                'veli_soyad'        => ['required', 'string', 'max:100'],
                'veli_tc_kimlik'    => ['required', 'digits:11'],
                'veli_dogum_tarihi' => ['required', 'date', 'before:today'],
                'veli_telefon'      => ['required', 'string', 'max:20'],
            ], [
                'veli_ad.required'           => 'Veli adı zorunludur.',
                'veli_soyad.required'        => 'Veli soyadı zorunludur.',
                'veli_tc_kimlik.required'    => 'Veli TC Kimlik No zorunludur.',
                'veli_tc_kimlik.digits'      => 'Veli TC Kimlik No 11 rakamdan oluşmalıdır.',
                'veli_dogum_tarihi.required' => 'Veli doğum tarihi zorunludur.',
                'veli_dogum_tarihi.before'   => 'Veli doğum tarihi geçmişte olmalıdır.',
                'veli_telefon.required'      => 'Veli telefon numarası zorunludur.',
            ]);
        }

        // ── Telefon değişikliği & OTP kontrolü ───────────────────────────────────
        $telefonDegisti    = $uye->telefon !== $request->input('telefon');
        $telefonDogrulandi = $uye->telefon_dogrulandi;

        if ($telefonDegisti) {
            $verifiedPhone = session('otp_verified_phone');
            if (!$verifiedPhone || $verifiedPhone !== $request->input('telefon')) {
                return back()->withInput()->withErrors(['telefon' => 'Yeni telefon numarası doğrulanmamış. Lütfen doğrulama kodunu tamamlayın.']);
            }
            $telefonDogrulandi = true;
            session()->forget('otp_verified_phone');
        }

        // ── Güncelle ─────────────────────────────────────────────────────────────
        $uye->update([
            'email'              => $request->input('email'),
            'cinsiyet'           => $request->input('cinsiyet') ?: null,
            'telefon'            => $request->input('telefon'),
            'telefon_dogrulandi' => $telefonDogrulandi,
            'il'                 => $request->input('il'),
            'ilce'               => $request->input('ilce'),
            'mahalle'            => $request->input('mahalle'),
            'acik_adres'         => $request->input('acik_adres'),
            'statu'              => $request->input('statu'),
            'uyelik_baslangic'   => $request->input('uyelik_baslangic'),
            'uyelik_bitis'       => $request->input('uyelik_bitis'),
            'notlar'             => $request->input('notlar'),
            'updated_user'       => auth()->id(),
            // Veli bilgileri — sadece reşit olmayan üyelerde güncellenir
            'veli_ad'            => $isMinor ? $request->input('veli_ad')           : null,
            'veli_soyad'         => $isMinor ? $request->input('veli_soyad')        : null,
            'veli_tc_kimlik'     => $isMinor ? $request->input('veli_tc_kimlik')    : null,
            'veli_dogum_tarihi'  => $isMinor ? $request->input('veli_dogum_tarihi') : null,
            'veli_telefon'       => $isMinor ? $request->input('veli_telefon')      : null,
            // Ek iletişim & eğitim bilgileri
            'telefon2'           => $request->input('telefon2') ?: null,
            'ogretim_durumu'     => $request->input('ogretim_durumu') ?: null,
            'okul_adi'           => $request->input('okul_adi') ?: null,
            'bolum_adi'          => $request->input('bolum_adi') ?: null,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => '"' . $uye->ad_soyad . '" başarıyla güncellendi.',
            ]);
        }

        return redirect()->route('uyeler.index')
            ->with('success', '"' . $uye->ad_soyad . '" başarıyla güncellendi.');
    }

    // ─── Kimlik Güncelle ────────────────────────────────────────────────────────
    public function kimlikGuncelle(Request $request, Uye $uye)
    {
        try {
            $sonuc = \App\Http\Controllers\KpsController::kimlikSorgula(
                $uye->dogum_tarihi,
                $uye->tc_kimlik
            );

            if (isset($sonuc['success']) && $sonuc['success'] === true) {
                $kisi = $sonuc['sbsKisiDto'];
                $kpsCinsiyet = $this->normalizeKpsCinsiyet($kisi['cinsiyeti'] ?? $kisi['cinsiyet'] ?? null);
                $updateData = ['ad' => $kisi['adi'], 'soyad' => $kisi['soyadi']];
                if ($kpsCinsiyet !== null) {
                    $updateData['cinsiyet'] = $kpsCinsiyet;
                }
                $uye->update(array_merge($updateData, ['updated_user' => auth()->id()]));
                return response()->json([
                    'success' => true,
                    'message' => 'Kimlik bilgileri başarıyla güncellendi.',
                    'data'    => [
                        'ad' => $kisi['adi'],
                        'soyad' => $kisi['soyadi'],
                        'cinsiyet' => $kpsCinsiyet,
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'KPS sisteminden veri alınamadı: ' . ($sonuc['resultMessage'] ?? 'Hata oluştu.'),
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Sistem hatası: ' . $e->getMessage()], 500);
        }
    }

    // ─── Sil ────────────────────────────────────────────────────────────────────
    public function destroy(Uye $uye)
    {
        $ad = $uye->ad_soyad;
        $uye->delete();

        return redirect()->route('uyeler.index')
            ->with('success', '"' . $ad . '" üyelikten çıkarıldı.');
    }

    // ─── Yardımcı ───────────────────────────────────────────────────────────────
    private function maskPhone(string $telefon): string
    {
        $clean = preg_replace('/\D/', '', $telefon);
        if (strlen($clean) >= 10) {
            return substr($clean, 0, 4) . '***' . substr($clean, -3);
        }
        return '***';
    }

    private function normalizeKpsCinsiyet(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $value = mb_strtoupper(trim($raw), 'UTF-8');

        return match ($value) {
            'ERKEK' => 'erkek',
            'KADIN' => 'kadin',
            default => null,
        };
    }
}
