<?php

namespace App\Services;

use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Cache;

class OtpService
{
    // Kaç dakika geçerli
    const TTL_MINUTES = 3;
    // Kaç haneli
    const DIGITS = 6;

    // ─── Kod Üret & Önbelleğe Al ───────────────────────────────────────────────
    public function generate(string $telefon): string
    {
        $code = str_pad(random_int(0, 999999), self::DIGITS, '0', STR_PAD_LEFT);
        Cache::put($this->cacheKey($telefon), [
            'code' => $code,
            'expires_at' => now()->addMinutes(self::TTL_MINUTES)->timestamp,
        ], now()->addMinutes(self::TTL_MINUTES));
        return $code;
    }

    // ─── Doğrula ──────────────────────────────────────────────────────────────
    public function verify(string $telefon, string $code): bool
    {
        $stored = Cache::get($this->cacheKey($telefon));
        $storedCode = is_array($stored) ? ($stored['code'] ?? null) : $stored;
        if ($storedCode && $storedCode === $code) {
            Cache::forget($this->cacheKey($telefon));
            return true;
        }
        return false;
    }

    // ─── SMS Gönder ───────────────────────────────────────────────────────────
    /**
     * @return array{success: bool, message?: string}
     */
    public function send(string $telefon, string $code, ?string $source = null): array
    {
        $mesaj = "{$code} doğrulama kodu ile Kütüphane Bilgi Sistemine giriş yapabilirsiniz.";

        $result = MessageController::smsGonder($telefon, $mesaj, $source);

        if ($this->isSmsSuccessful($result)) {
            \Illuminate\Support\Facades\Log::info("OTP SMS [{$telefon}]: {$code}");

            return ['success' => true];
        }

        // SMS gitmediyse üretilen OTP kullanılmasın
        Cache::forget($this->cacheKey($telefon));

        $error = $this->extractSmsError($result);
        \Illuminate\Support\Facades\Log::warning("OTP SMS gönderilemedi [{$telefon}]: {$error}");

        return [
            'success' => false,
            'message' => $error,
        ];
    }

    private function isSmsSuccessful(mixed $result): bool
    {
        if (! is_array($result)) {
            return false;
        }

        if (array_key_exists('success', $result)) {
            return (bool) $result['success'];
        }

        // HTTP başarılı olup özel success alanı dönmeyen sağlayıcı yanıtları
        return true;
    }

    private function extractSmsError(mixed $result): string
    {
        if (is_array($result)) {
            foreach (['message', 'Message', 'error', 'Error', 'msg'] as $key) {
                if (! empty($result[$key]) && is_string($result[$key])) {
                    return $result[$key];
                }
            }
        }

        return 'SMS gönderilemedi. Lütfen tekrar deneyin.';
    }

    // ─── Geçerliliği Kontrol ───────────────────────────────────────────────────
    public function exists(string $telefon): bool
    {
        return $this->remainingSeconds($telefon) > 0;
    }

    public function remainingSeconds(string $telefon): int
    {
        $stored = Cache::get($this->cacheKey($telefon));
        if (!$stored) {
            return 0;
        }

        if (is_array($stored)) {
            $expiresAt = (int) ($stored['expires_at'] ?? 0);
            if ($expiresAt <= 0) {
                return 0;
            }

            return max(0, $expiresAt - now()->timestamp);
        }

        // Eski string formatında kayıtlar için yaklaşık kalan süre.
        return self::TTL_MINUTES * 60;
    }

    // ─── Önbellek Anahtarı ────────────────────────────────────────────────────
    private function cacheKey(string $telefon): string
    {
        return 'otp:' . preg_replace('/\D/', '', $telefon);
    }

    // ─── Telefon Formatla (+90xxxxxxxxxx) ─────────────────────────────────────
    private function formatPhone(string $telefon): string
    {
        $clean = preg_replace('/\D/', '', $telefon);
        if (str_starts_with($clean, '0')) {
            $clean = '90' . substr($clean, 1);
        } elseif (!str_starts_with($clean, '90')) {
            $clean = '90' . $clean;
        }
        return '+' . $clean;
    }
}
