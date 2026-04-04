<?php

namespace App\Support;

use RuntimeException;

class JwtToken
{
    public static function encode(array $payload, string $secret): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];

        $headerPart = self::base64UrlEncode((string) json_encode($header, JSON_UNESCAPED_SLASHES));
        $payloadPart = self::base64UrlEncode((string) json_encode($payload, JSON_UNESCAPED_SLASHES));
        $signature = hash_hmac('sha256', $headerPart.'.'.$payloadPart, $secret, true);
        $signaturePart = self::base64UrlEncode($signature);

        return $headerPart.'.'.$payloadPart.'.'.$signaturePart;
    }

    public static function decode(string $token, string $secret): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new RuntimeException('Token formati gecersiz.');
        }

        [$headerPart, $payloadPart, $signaturePart] = $parts;

        $headerJson = self::base64UrlDecode($headerPart);
        $payloadJson = self::base64UrlDecode($payloadPart);
        $signature = self::base64UrlDecode($signaturePart);

        $header = json_decode($headerJson, true);
        $payload = json_decode($payloadJson, true);

        if (!is_array($header) || !is_array($payload)) {
            throw new RuntimeException('Token icerigi gecersiz.');
        }

        if (($header['alg'] ?? null) !== 'HS256') {
            throw new RuntimeException('Desteklenmeyen algoritma.');
        }

        $expectedSignature = hash_hmac('sha256', $headerPart.'.'.$payloadPart, $secret, true);
        if (!hash_equals($expectedSignature, $signature)) {
            throw new RuntimeException('Imza dogrulama basarisiz.');
        }

        $now = time();
        if (isset($payload['exp']) && is_numeric($payload['exp']) && $now >= (int) $payload['exp']) {
            throw new RuntimeException('Token suresi dolmus.');
        }

        return $payload;
    }

    private static function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $value): string
    {
        $remainder = strlen($value) % 4;
        if ($remainder > 0) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);
        if ($decoded === false) {
            throw new RuntimeException('Base64 decode hatasi.');
        }

        return $decoded;
    }
}
