<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private const TWO_FACTOR_USER_SESSION_KEY = 'auth_2fa_user_id';
    private const TWO_FACTOR_REMEMBER_SESSION_KEY = 'auth_2fa_remember';
    private const MAX_FAILED_LOGIN_ATTEMPTS = 5;

    // ─── Giriş Sayfası ──────────────────────────────────────────────────────────
    public function loginForm()
    {
        // Zaten giriş yapmışsa katalog sayfasına yönlendir
        if (Auth::check()) {
            return redirect()->route('katalog.index');
        }

        return view('auth.login');
    }

    // ─── Giriş İşlemi ───────────────────────────────────────────────────────────
    public function login(Request $request)
    {
        $loginMethod = (string) $request->input('login_method', 'email');
        if (!in_array($loginMethod, ['email', 'ldap'], true)) {
            $loginMethod = 'email';
        }

        $rules = [
            'login_method' => ['required', 'in:email,ldap'],
            'password' => ['required'],
        ];
        $messages = [
            'password.required' => 'Şifre zorunludur.',
        ];

        if ($loginMethod === 'email') {
            $rules['email'] = ['required', 'email'];
            $messages['email.required'] = 'E-posta adresi zorunludur.';
            $messages['email.email'] = 'Geçerli bir e-posta adresi girin.';
        } else {
            $rules['ldap_username'] = ['required', 'string'];
            $messages['ldap_username.required'] = 'LDAP kullanıcı adı zorunludur.';
        }

        $credentials = $request->validate($rules, $messages);

        $remember = $request->boolean('remember');
        $user = $loginMethod === 'email'
            ? User::where('email', (string) ($credentials['email'] ?? ''))->first()
            : User::where('ldap_username', trim((string) ($credentials['ldap_username'] ?? '')))->first();

        if ($user && ($user->statu ?? 'aktif') !== 'aktif') {
            return back()
                ->withInput($request->only('login_method', 'email', 'ldap_username', 'remember'))
                ->withErrors([
                    'auth' => 'Kullanıcı hesabınız pasif durumda. Sistem yöneticiniz ile iletişime geçiniz.',
                ]);
        }

        $isPasswordValid = false;
        $ldapConnectionError = false;
        if ($user) {
            if ($loginMethod === 'ldap') {
                $ldapUsername = trim((string) ($user->ldap_username ?? ''));
                $ldapAuthResult = $ldapUsername !== ''
                    ? $this->authenticateViaLdap($ldapUsername, (string) $credentials['password'])
                    : ['ok' => false, 'connection_error' => false];
                $isPasswordValid = (bool) ($ldapAuthResult['ok'] ?? false);
                $ldapConnectionError = (bool) ($ldapAuthResult['connection_error'] ?? false);
            } else {
                $isPasswordValid = Hash::check($credentials['password'], $user->password);
            }
        }

        if ($user && $isPasswordValid) {
            $this->clearFailedLoginAttempts($user);

            if ((bool) $user->twofactor) {
                $phone = $this->normalizePhoneForSms($user->telefon);
                if (!$phone) {
                    return back()
                        ->withInput($request->only('login_method', 'email', 'ldap_username', 'remember'))
                        ->withErrors([
                            'auth' => 'Bu kullanıcının sistemde kayıtlı telefon numarası yok. Doğrulama kodu gönderilemedi. Lütfen sistem yöneticiniz ile iletişime geçiniz.',
                        ]);
                }

                $request->session()->put([
                    self::TWO_FACTOR_USER_SESSION_KEY => $user->id,
                    self::TWO_FACTOR_REMEMBER_SESSION_KEY => $remember,
                ]);

                app(OtpService::class)->send($phone, app(OtpService::class)->generate($phone), 'auth_login_2fa');

                return redirect()
                    ->route('auth.twofactor.form')
                    ->with('twofactor_info', 'Doğrulama kodu telefonunuza SMS ile gönderildi.');
            }

            Auth::login($user, $remember);
            $request->session()->regenerate();

            return redirect()->intended(route('katalog.index'));
        }

        if ($user) {
            $attemptCount = $this->incrementFailedLoginAttempts($user);
            if ($attemptCount >= self::MAX_FAILED_LOGIN_ATTEMPTS && ($user->statu ?? 'aktif') === 'aktif') {
                $user->update(['statu' => 'pasif']);
                $this->clearFailedLoginAttempts($user);
                $this->sendAccountLockedSms($user);

                return back()
                    ->withInput($request->only('login_method', 'email', 'ldap_username', 'remember'))
                    ->withErrors([
                        'auth' => 'Arka arkaya 5 hatalı giriş nedeniyle hesabınız askıya alındı.',
                    ]);
            }
        }

        return back()
            ->withInput($request->only('login_method', 'email', 'ldap_username', 'remember'))
            ->withErrors([
                'auth' => ($loginMethod === 'ldap' && $ldapConnectionError)
                    ? 'LDAP bağlantı hatası.'
                    : 'Kullanıcı adı veya şifre hatalı.',
            ]);
    }

    public function twoFactorForm(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('katalog.index');
        }

        $user = $this->getPendingTwoFactorUser($request);
        if (!$user) {
            return redirect()->route('login');
        }

        $phone = $this->normalizePhoneForSms($user->telefon);
        $remainingSeconds = $phone ? app(OtpService::class)->remainingSeconds($phone) : 0;

        return view('auth.twofactor', [
            'maskedPhone' => $this->maskPhone($user->telefon),
            'email' => $user->email,
            'remainingSeconds' => $remainingSeconds,
        ]);
    }

    public function twoFactorVerify(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('katalog.index');
        }

        $user = $this->getPendingTwoFactorUser($request);
        if (!$user) {
            return redirect()->route('login')->withErrors([
                'code' => 'Oturum süresi doldu. Lütfen tekrar giriş yapın.',
            ]);
        }

        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ], [
            'code.required' => 'SMS doğrulama kodu zorunludur.',
            'code.digits' => 'SMS doğrulama kodu 6 haneli olmalıdır.',
        ]);

        $phone = $this->normalizePhoneForSms($user->telefon);
        if (!$phone || !app(OtpService::class)->verify($phone, $data['code'])) {
            return back()->withErrors([
                'code' => 'Doğrulama kodu hatalı veya süresi dolmuş.',
            ]);
        }

        $remember = (bool) $request->session()->pull(self::TWO_FACTOR_REMEMBER_SESSION_KEY, false);
        $request->session()->forget(self::TWO_FACTOR_USER_SESSION_KEY);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('katalog.index'));
    }

    public function twoFactorResend(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('katalog.index');
        }

        $user = $this->getPendingTwoFactorUser($request);
        if (!$user) {
            return redirect()->route('login')->withErrors([
                'email' => 'Oturum süresi doldu. Lütfen tekrar giriş yapın.',
            ]);
        }

        $phone = $this->normalizePhoneForSms($user->telefon);
        if (!$phone) {
            return redirect()->route('login')->withErrors([
                'email' => 'Bu kullanıcı için geçerli telefon numarası bulunamadı.',
            ]);
        }

        $otpService = app(OtpService::class);
        $remainingSeconds = $otpService->remainingSeconds($phone);
        if ($remainingSeconds > 0) {
            return back()->withErrors([
                'code' => 'Yeni doğrulama kodu için bekleme süresi: ' . ceil($remainingSeconds / 60) . ' dakika.',
            ]);
        }

        $otpService->send($phone, $otpService->generate($phone), 'auth_login_2fa');

        return back()->with('twofactor_info', 'Yeni doğrulama kodu SMS ile gönderildi.');
    }

    public function passwordForm()
    {
        abort_unless(Auth::check(), 403);
        return view('auth.password-change-page');
    }

    public function passwordUpdate(Request $request)
    {
        abort_unless(Auth::check(), 403);

        $data = $request->validate([
            'current_password' => ['required'],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
        ], [
            'current_password.required' => 'Mevcut şifre zorunludur.',
            'password.required' => 'Yeni şifre zorunludur.',
            'password.confirmed' => 'Yeni şifre tekrarı eşleşmiyor.',
            'password.min' => 'Yeni şifre en az 8 karakter olmalıdır.',
            'password.regex' => 'Yeni şifre en az bir küçük harf, bir büyük harf ve bir rakam içermelidir.',
        ]);

        $user = Auth::user();
        if (!$user || !Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'Mevcut şifre hatalı.',
            ])->withInput();
        }

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('success', 'Şifreniz başarıyla güncellendi.');
    }

    // ─── Çıkış İşlemi ───────────────────────────────────────────────────────────
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('logout', true);
    }

    private function getPendingTwoFactorUser(Request $request): ?User
    {
        $userId = (int) $request->session()->get(self::TWO_FACTOR_USER_SESSION_KEY, 0);
        if ($userId <= 0) {
            return null;
        }

        return User::find($userId);
    }

    private function normalizePhoneForSms(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);
        if (!$digits) {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }
        if (str_starts_with($digits, '90')) {
            $digits = substr($digits, 2);
        }
        if (strlen($digits) !== 10 || !str_starts_with($digits, '5')) {
            return null;
        }

        return '90' . $digits;
    }

    private function maskPhone(?string $value): string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        if (strlen($digits) < 4) {
            return '***';
        }

        return '*** *** ** ' . substr($digits, -2);
    }

    private function failedLoginCacheKey(User $user): string
    {
        return 'auth:failed_login:' . $user->id;
    }

    private function incrementFailedLoginAttempts(User $user): int
    {
        $key = $this->failedLoginCacheKey($user);
        $current = (int) Cache::get($key, 0);
        $next = $current + 1;
        Cache::put($key, $next, now()->addHours(24));
        return $next;
    }

    private function clearFailedLoginAttempts(User $user): void
    {
        Cache::forget($this->failedLoginCacheKey($user));
    }

    private function sendAccountLockedSms(User $user): void
    {
        $phone = $this->normalizePhoneForSms($user->telefon);
        if (!$phone) {
            return;
        }

        $message = 'KÜBİS hesabınız, arka arkaya 5 hatalı giriş nedeniyle güvenlik amaçlı askıya alınmıştır. Lütfen sistem yöneticiniz ile iletişime geçiniz.';
        MessageController::smsGonder($phone, $message, 'auth_failed_login_lock');
    }

    /**
     * @return array{ok: bool, connection_error: bool}
     */
    private function authenticateViaLdap(string $ldapUsername, string $password): array
    {
        $ldapUsername = trim($ldapUsername);
        if ($ldapUsername === '' || $password === '') {
            return ['ok' => false, 'connection_error' => false];
        }
        if (!function_exists('ldap_connect') || !function_exists('ldap_bind')) {
            return ['ok' => false, 'connection_error' => true];
        }

        $host = (string) config('services.ldap.host', 'ldap://dc16.beyoglu.bel.tr:389');
        $baseDn = (string) config('services.ldap.base_dn', 'DC=beyoglu,DC=bel,DC=tr');

        $conn = @ldap_connect($host);
        if (!$conn) {
            return ['ok' => false, 'connection_error' => true];
        }

        @ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        @ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);

        $bindDn = $this->ldapBindPrincipal($ldapUsername, $baseDn);
        $ok = @ldap_bind($conn, $bindDn, $password);

        if ($ok) {
            @ldap_unbind($conn);
            return ['ok' => true, 'connection_error' => false];
        }

        $errno = function_exists('ldap_errno') ? (int) @ldap_errno($conn) : 0;
        @ldap_unbind($conn);
        $connectionErrorCodes = [81, 82, 85, 91];
        $isConnectionError = in_array($errno, $connectionErrorCodes, true);

        return ['ok' => false, 'connection_error' => $isConnectionError];
    }

    private function ldapBindPrincipal(string $ldapUsername, string $baseDn): string
    {
        if (str_contains($ldapUsername, '@') || str_contains($ldapUsername, '\\') || str_contains($ldapUsername, '=')) {
            return $ldapUsername;
        }

        $domain = $this->ldapDomainFromBaseDn($baseDn);
        if ($domain === '') {
            return $ldapUsername;
        }

        return $ldapUsername . '@' . $domain;
    }

    private function ldapDomainFromBaseDn(string $baseDn): string
    {
        if (!preg_match_all('/DC=([^,]+)/i', $baseDn, $matches)) {
            return '';
        }

        $parts = array_map(static fn($v) => trim((string) $v), $matches[1] ?? []);
        $parts = array_values(array_filter($parts, static fn($v) => $v !== ''));

        return implode('.', $parts);
    }
}
