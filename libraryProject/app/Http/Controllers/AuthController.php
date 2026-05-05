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
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'E-posta adresi zorunludur.',
            'email.email'       => 'Geçerli bir e-posta adresi girin.',
            'password.required' => 'Şifre zorunludur.',
        ]);

        $remember = $request->boolean('remember');
        $user = User::where('email', $credentials['email'])->first();

        if ($user && ($user->statu ?? 'aktif') !== 'aktif') {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => 'Kullanıcı hesabınız pasif durumda. Sistem yöneticiniz ile iletişime geçiniz.',
                ]);
        }

        if ($user && Hash::check($credentials['password'], $user->password)) {
            $this->clearFailedLoginAttempts($user);

            if ((bool) $user->twofactor) {
                $phone = $this->normalizePhoneForSms($user->telefon);
                if (!$phone) {
                    return back()
                        ->withInput($request->only('email', 'remember'))
                        ->withErrors([
                            'email' => 'Bu kullanıcının sistemde kayıtlı telefon numarası yok. Doğrulama kodu gönderilemedi. Lütfen sistem yöneticiniz ile iletişime geçiniz.',
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
                    ->withInput($request->only('email', 'remember'))
                    ->withErrors([
                        'email' => 'Arka arkaya 5 hatalı giriş nedeniyle hesabınız askıya alındı.',
                    ]);
            }
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors([
                'email' => 'E-posta adresi veya şifre hatalı.',
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
}
