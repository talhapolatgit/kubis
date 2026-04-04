<?php

namespace App\Http\Middleware;

use App\Models\Uye;
use App\Support\JwtToken;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthenticateMemberJwt
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return $this->unauthorized('Bearer token bulunamadi.');
        }

        $jwtSecret = config('app.jwt_secret');
        if (!$jwtSecret) {
            return response()->json([
                'message' => 'JWT secret tanimli degil.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            $payload = JwtToken::decode($token, $jwtSecret);
        } catch (Throwable $e) {
            return $this->unauthorized('Gecersiz veya suresi dolmus token.');
        }

        $uyeId = $payload['sub'] ?? null;
        if (!$uyeId) {
            return $this->unauthorized('Token icerigi gecersiz.');
        }

        $uye = Uye::query()->find($uyeId);
        if (!$uye) {
            return $this->unauthorized('Uye bulunamadi.');
        }

        $request->attributes->set('jwt_payload', $payload);
        $request->attributes->set('uye', $uye);

        return $next($request);
    }

    private function unauthorized(string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], Response::HTTP_UNAUTHORIZED);
    }
}
