<?php

namespace App\Http\Controllers;

use App\Models\Entegrasyon;
use App\Services\Ldap\LdapSettings;
use App\Services\Sms\SmsService;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class EntegrasyonController extends Controller
{
    private const YETKI = 37;

    public function index()
    {
        abort_unless(auth()->user()?->hasYetki(self::YETKI), 403);

        $sms = Entegrasyon::query()->tip('sms')->first();
        $smsAyarlar = is_array($sms?->ayarlar) ? $sms->ayarlar : [];

        $kimlik = Entegrasyon::query()->tip('kimlik')->first();
        $kimlikAyarlar = is_array($kimlik?->ayarlar) ? $kimlik->ayarlar : [];

        $adres = Entegrasyon::query()->tip('adres')->first();
        $adresAyarlar = is_array($adres?->ayarlar) ? $adres->ayarlar : [];

        $ldap = Entegrasyon::query()->tip('ldap')->first();
        $ldapAyarlar = is_array($ldap?->ayarlar) ? $ldap->ayarlar : [];

        $webhook = Entegrasyon::query()->tip('webhook')->first();
        $webhookAyarlar = is_array($webhook?->ayarlar) ? $webhook->ayarlar : [];

        return view('entegrasyon.index', [
            'sms' => $sms,
            'smsAyarlar' => $smsAyarlar,
            'smsAuthorizationKayitli' => filled($smsAyarlar['authorization'] ?? null),
            'kimlik' => $kimlik,
            'kimlikAyarlar' => $kimlikAyarlar,
            'kimlikAuthorizationKayitli' => filled($kimlikAyarlar['authorization'] ?? null),
            'adres' => $adres,
            'adresAyarlar' => $adresAyarlar,
            'adresAuthorizationKayitli' => filled($adresAyarlar['authorization'] ?? null),
            'ldap' => $ldap,
            'ldapAyarlar' => $ldapAyarlar,
            'webhook' => $webhook,
            'webhookAyarlar' => $webhookAyarlar,
            'webhookSecretKayitli' => filled($webhookAyarlar['secret'] ?? null),
        ]);
    }

    public function updateSms(Request $request)
    {
        return $this->updateFlexcityEntegrasyon(
            $request,
            tip: 'sms',
            successMessage: 'SMS entegrasyonu kaydedildi.',
        );
    }

    public function updateKimlik(Request $request)
    {
        return $this->updateFlexcityEntegrasyon(
            $request,
            tip: 'kimlik',
            successMessage: 'Kimlik sorgulama entegrasyonu kaydedildi.',
        );
    }

    public function updateAdres(Request $request)
    {
        return $this->updateFlexcityEntegrasyon(
            $request,
            tip: 'adres',
            successMessage: 'Adres sorgulama entegrasyonu kaydedildi.',
        );
    }

    public function updateLdap(Request $request)
    {
        abort_unless(auth()->user()?->hasYetki(self::YETKI), 403);

        $validated = $request->validate([
            'saglayici' => ['required', 'in:active_directory'],
            'aktif' => ['nullable', 'boolean'],
            'host' => ['required', 'string', 'max:250'],
            'base_dn' => ['required', 'string', 'max:500'],
            'protocol_version' => ['nullable', 'integer', 'in:2,3'],
            'referrals' => ['nullable', 'boolean'],
        ], [
            'host.required' => 'LDAP sunucu adresi zorunludur.',
            'base_dn.required' => 'Base DN zorunludur.',
            'saglayici.in' => 'Seçilen LDAP sağlayıcısı desteklenmiyor.',
        ]);

        $host = trim((string) $validated['host']);
        if (! preg_match('/^ldaps?:\/\//i', $host)) {
            $host = 'ldap://' . $host;
        }

        $ayarlar = [
            'host' => $host,
            'base_dn' => trim((string) $validated['base_dn']),
            'protocol_version' => (int) ($validated['protocol_version'] ?? 3),
            'referrals' => $request->boolean('referrals'),
        ];

        $data = [
            'saglayici' => $validated['saglayici'],
            'aktif' => $request->boolean('aktif'),
            'ayarlar' => $ayarlar,
            'updated_by' => Auth::id(),
        ];

        $entegrasyon = Entegrasyon::query()->tip('ldap')->first();
        if ($entegrasyon) {
            $entegrasyon->update($data);
        } else {
            Entegrasyon::create(array_merge($data, [
                'tip' => 'ldap',
                'created_by' => Auth::id(),
            ]));
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'LDAP entegrasyonu kaydedildi.',
            ]);
        }

        return redirect()->route('entegrasyon.index')
            ->with('success', 'LDAP entegrasyonu kaydedildi.');
    }

    public function updateWebhook(Request $request)
    {
        abort_unless(auth()->user()?->hasYetki(self::YETKI), 403);

        $entegrasyon = Entegrasyon::query()->tip('webhook')->first();
        $mevcutAyarlar = is_array($entegrasyon?->ayarlar) ? $entegrasyon->ayarlar : [];
        $secretMevcut = filled($mevcutAyarlar['secret'] ?? null);

        $validated = $request->validate([
            'saglayici' => ['required', 'in:hmac'],
            'aktif' => ['nullable', 'boolean'],
            'webhook_url' => ['required', 'string', 'max:500', 'url'],
            'secret' => [$secretMevcut ? 'nullable' : 'required', 'string', 'max:500'],
        ], [
            'webhook_url.required' => 'Webhook URL zorunludur.',
            'webhook_url.url' => 'Geçerli bir URL girin.',
            'secret.required' => 'Webhook secret zorunludur.',
            'saglayici.in' => 'Seçilen webhook sağlayıcısı desteklenmiyor.',
        ]);

        $secret = trim((string) ($validated['secret'] ?? ''));
        if ($secret === '') {
            $secret = (string) ($mevcutAyarlar['secret'] ?? '');
        }

        if ($secret === '') {
            return $this->failResponse($request, 'Webhook secret zorunludur.', 'secret');
        }

        $ayarlar = [
            'webhook_url' => rtrim(trim($validated['webhook_url']), '/'),
            'secret' => $secret,
        ];

        $data = [
            'saglayici' => $validated['saglayici'],
            'aktif' => $request->boolean('aktif'),
            'ayarlar' => $ayarlar,
            'updated_by' => Auth::id(),
        ];

        if ($entegrasyon) {
            $entegrasyon->update($data);
        } else {
            Entegrasyon::create(array_merge($data, [
                'tip' => 'webhook',
                'created_by' => Auth::id(),
            ]));
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Webhook entegrasyonu kaydedildi.',
                'secret_kayitli' => true,
            ]);
        }

        return redirect()->route('entegrasyon.index')
            ->with('success', 'Webhook entegrasyonu kaydedildi.');
    }

    public function testWebhook(Request $request, WebhookService $webhookService)
    {
        abort_unless(auth()->user()?->hasYetki(self::YETKI), 403);

        $validated = $request->validate([
            'tc' => ['required', 'string', 'regex:/^[1-9][0-9]{10}$/'],
            'title' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:2000'],
        ], [
            'tc.required' => 'TC kimlik numarası zorunludur.',
            'tc.regex' => 'Geçerli bir TC kimlik numarası girin (11 rakam).',
            'title.required' => 'Bildirim başlığı zorunludur.',
            'message.required' => 'Bildirim mesajı zorunludur.',
        ]);

        try {
            $result = $webhookService->sendBildirim(
                [(string) $validated['tc']],
                (string) $validated['title'],
                (string) $validated['message'],
            );

            return response()->json([
                'success' => true,
                'message' => 'Test bildirimi başarıyla gönderildi.',
                'data' => $result,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Webhook testi başarısız oldu.',
            ], 422);
        }
    }

    public function testSms(Request $request, SmsService $smsService)
    {
        abort_unless(auth()->user()?->hasYetki(self::YETKI), 403);

        $validated = $request->validate([
            'telefon' => ['required', 'string', 'regex:/^(0|\+90|90)?5\d{9}$/'],
            'message' => ['required', 'string', 'max:1000'],
        ], [
            'telefon.required' => 'Telefon numarası zorunludur.',
            'telefon.regex' => 'Geçerli bir Türkiye cep numarası girin (05xxxxxxxxx).',
            'message.required' => 'SMS mesajı zorunludur.',
        ]);

        if (! $smsService->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'SMS entegrasyonu aktif değil veya tanımlı değil. Önce ayarları kaydedip etkinleştirin.',
            ], 422);
        }

        $gsm = $this->normalizePhoneForSms((string) $validated['telefon']);
        if ($gsm === null) {
            return response()->json([
                'success' => false,
                'message' => 'Geçerli bir Türkiye cep numarası girin (05xxxxxxxxx).',
            ], 422);
        }

        $result = $smsService->send($gsm, (string) $validated['message'], 'entegrasyon_sms_test');
        $success = (bool) ($result['success'] ?? false);

        return response()->json([
            'success' => $success,
            'message' => $success
                ? 'Test SMS başarıyla gönderildi.'
                : ((string) ($result['message'] ?? 'SMS gönderilemedi. Lütfen ayarları kontrol edin.')),
            'data' => $result,
        ], $success ? 200 : 422);
    }

    public function testKimlik(Request $request)
    {
        abort_unless(auth()->user()?->hasYetki(self::YETKI), 403);

        $validated = $request->validate([
            'tc_kimlik' => ['required', 'string', 'regex:/^[1-9][0-9]{10}$/'],
            'dogum_tarihi' => ['required', 'date', 'before:today'],
        ], [
            'tc_kimlik.required' => 'TC kimlik numarası zorunludur.',
            'tc_kimlik.regex' => 'Geçerli bir TC kimlik numarası girin (11 rakam).',
            'dogum_tarihi.required' => 'Doğum tarihi zorunludur.',
            'dogum_tarihi.before' => 'Doğum tarihi geçmişte olmalıdır.',
        ]);

        $dogumTarihi = date('Y-m-d', strtotime((string) $validated['dogum_tarihi']));
        $ham = KpsController::kimlikSorgula($dogumTarihi, (string) $validated['tc_kimlik']);

        if (isset($ham['success']) && $ham['success'] === true && isset($ham['sbsKisiDto'])) {
            $dto = $ham['sbsKisiDto'];
            $ad = trim((string) ($dto['adi'] ?? ''));
            $soyad = trim((string) ($dto['soyadi'] ?? ''));
            $adSoyad = trim($ad.' '.$soyad);

            return response()->json([
                'success' => true,
                'message' => $adSoyad !== ''
                    ? "Kimlik sorgusu başarılı: {$adSoyad}"
                    : 'Kimlik sorgusu başarılı.',
                'data' => [
                    'ad' => $ad,
                    'soyad' => $soyad,
                    'cinsiyet' => $dto['cinsiyeti'] ?? $dto['cinsiyet'] ?? null,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => (string) ($ham['message'] ?? 'Kimlik doğrulaması başarısız. Lütfen bilgileri ve entegrasyon ayarlarını kontrol edin.'),
        ], 422);
    }

    public function testAdres(Request $request)
    {
        abort_unless(auth()->user()?->hasYetki(self::YETKI), 403);

        $validated = $request->validate([
            'tc_kimlik' => ['required', 'string', 'regex:/^[1-9][0-9]{10}$/'],
            'dogum_tarihi' => ['required', 'date', 'before:today'],
        ], [
            'tc_kimlik.required' => 'TC kimlik numarası zorunludur.',
            'tc_kimlik.regex' => 'Geçerli bir TC kimlik numarası girin (11 rakam).',
            'dogum_tarihi.required' => 'Doğum tarihi zorunludur.',
            'dogum_tarihi.before' => 'Doğum tarihi geçmişte olmalıdır.',
        ]);

        $dogumTarihi = date('Y-m-d', strtotime((string) $validated['dogum_tarihi']));
        $sonuc = KpsController::adresSorgula((string) $validated['tc_kimlik'], $dogumTarihi);

        if (! empty($sonuc['success'])) {
            $parts = array_filter([
                $sonuc['ilAdi'] ?? null,
                $sonuc['ilceAdi'] ?? null,
                $sonuc['mahalleAdi'] ?? null,
                $sonuc['sokakAdi'] ?? null,
            ], static fn ($v) => filled($v));

            $ozet = implode(' / ', $parts);

            return response()->json([
                'success' => true,
                'message' => $ozet !== ''
                    ? "Adres sorgusu başarılı: {$ozet}"
                    : 'Adres sorgusu başarılı.',
                'data' => $sonuc,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => (string) ($sonuc['message'] ?? 'Adres bulunamadı. Lütfen bilgileri ve entegrasyon ayarlarını kontrol edin.'),
        ], 422);
    }

    private function normalizePhoneForSms(string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', $value);
        if (! $digits) {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }
        if (str_starts_with($digits, '90')) {
            $digits = substr($digits, 2);
        }
        if (strlen($digits) !== 10 || ! str_starts_with($digits, '5')) {
            return null;
        }

        return '90'.$digits;
    }

    public function testLdap(Request $request)
    {
        abort_unless(auth()->user()?->hasYetki(self::YETKI), 403);

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ], [
            'username.required' => 'LDAP kullanıcı adı zorunludur.',
            'password.required' => 'LDAP şifresi zorunludur.',
        ]);

        if (! function_exists('ldap_connect') || ! function_exists('ldap_bind')) {
            return response()->json([
                'success' => false,
                'message' => 'Sunucuda LDAP desteği bulunamadı.',
            ], 500);
        }

        $settings = LdapSettings::current();
        if (! $settings) {
            return response()->json([
                'success' => false,
                'message' => 'LDAP entegrasyonu aktif değil veya tanımlı değil. Önce ayarları kaydedip etkinleştirin.',
            ], 422);
        }

        $host = $settings['host'];
        $baseDn = $settings['base_dn'];
        $conn = @ldap_connect($host);
        if (! $conn) {
            return response()->json([
                'success' => false,
                'message' => 'LDAP sunucusuna bağlanılamadı. Host adresini kontrol edin.',
            ], 422);
        }

        @ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, $settings['protocol_version']);
        @ldap_set_option($conn, LDAP_OPT_REFERRALS, $settings['referrals'] ? 1 : 0);

        $bindPrincipal = $this->ldapBindPrincipal((string) $validated['username'], $baseDn);
        $bindOk = @ldap_bind($conn, $bindPrincipal, (string) $validated['password']);
        if (! $bindOk) {
            $errno = function_exists('ldap_errno') ? (int) @ldap_errno($conn) : 0;
            @ldap_unbind($conn);

            $connectionErrorCodes = [81, 82, 85, 91];
            if (in_array($errno, $connectionErrorCodes, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'LDAP sunucusuna ulaşılamadı. Host veya ağ bağlantısını kontrol edin.',
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'LDAP kimlik doğrulama başarısız. Kullanıcı adı veya şifre hatalı olabilir.',
            ], 422);
        }

        $search = @ldap_search($conn, $baseDn, '(objectClass=*)', ['dn'], 0, 1);
        $baseDnOk = (bool) $search;
        if ($search) {
            @ldap_free_result($search);
        }
        @ldap_unbind($conn);

        if (! $baseDnOk) {
            return response()->json([
                'success' => false,
                'message' => 'Kimlik doğrulama başarılı ancak Base DN üzerinde arama yapılamadı. Base DN değerini kontrol edin.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'LDAP bağlantısı ve kimlik doğrulama başarılı.',
            'data' => [
                'host' => $host,
                'base_dn' => $baseDn,
                'bind_principal' => $bindPrincipal,
            ],
        ]);
    }

    private function ldapBindPrincipal(string $ldapUsername, string $baseDn): string
    {
        $ldapUsername = trim($ldapUsername);
        if ($ldapUsername === '') {
            return '';
        }

        if (str_contains($ldapUsername, '@') || str_contains($ldapUsername, '\\') || str_contains($ldapUsername, '=')) {
            return $ldapUsername;
        }

        $domain = $this->ldapDomainFromBaseDn($baseDn);
        if ($domain === '') {
            return $ldapUsername;
        }

        return $ldapUsername.'@'.$domain;
    }

    private function ldapDomainFromBaseDn(string $baseDn): string
    {
        if (! preg_match_all('/DC=([^,]+)/i', $baseDn, $matches)) {
            return '';
        }

        $parts = array_map(static fn ($v) => trim((string) $v), $matches[1] ?? []);
        $parts = array_values(array_filter($parts, static fn ($v) => $v !== ''));

        return implode('.', $parts);
    }

    private function updateFlexcityEntegrasyon(Request $request, string $tip, string $successMessage)
    {
        abort_unless(auth()->user()?->hasYetki(self::YETKI), 403);

        $entegrasyon = Entegrasyon::query()->tip($tip)->first();
        $mevcutAyarlar = is_array($entegrasyon?->ayarlar) ? $entegrasyon->ayarlar : [];
        $authorizationMevcut = filled($mevcutAyarlar['authorization'] ?? null);

        $validated = $request->validate([
            'saglayici' => ['required', 'in:flexcity'],
            'aktif' => ['nullable', 'boolean'],
            'base_url' => ['required', 'string', 'max:500', 'url'],
            'authorization' => [$authorizationMevcut ? 'nullable' : 'required', 'string', 'max:2000'],
            'content_type' => ['nullable', 'string', 'max:100'],
            'verify_ssl' => ['nullable', 'boolean'],
        ], [
            'base_url.required' => 'Servis URL adresi zorunludur.',
            'base_url.url' => 'Geçerli bir URL girin.',
            'authorization.required' => 'Authorization değeri zorunludur.',
            'saglayici.in' => 'Seçilen sağlayıcı desteklenmiyor.',
        ]);

        $authorization = trim((string) ($validated['authorization'] ?? ''));
        if ($authorization === '') {
            $authorization = (string) ($mevcutAyarlar['authorization'] ?? '');
        }

        if ($authorization === '') {
            return $this->failResponse($request, 'Authorization değeri zorunludur.', 'authorization');
        }

        $ayarlar = [
            'base_url' => rtrim(trim($validated['base_url']), '/'),
            'authorization' => $authorization,
            'content_type' => trim((string) ($validated['content_type'] ?? '')) ?: 'application/json',
            'verify_ssl' => $request->boolean('verify_ssl'),
        ];

        $data = [
            'saglayici' => $validated['saglayici'],
            'aktif' => $request->boolean('aktif'),
            'ayarlar' => $ayarlar,
            'updated_by' => Auth::id(),
        ];

        if ($entegrasyon) {
            $entegrasyon->update($data);
        } else {
            Entegrasyon::create(array_merge($data, [
                'tip' => $tip,
                'created_by' => Auth::id(),
            ]));
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'authorization_kayitli' => true,
            ]);
        }

        return redirect()->route('entegrasyon.index')
            ->with('success', $successMessage);
    }

    private function failResponse(Request $request, string $message, string $field = 'authorization')
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => [$field => [$message]],
            ], 422);
        }

        return back()->withErrors([$field => $message])->withInput();
    }
}
