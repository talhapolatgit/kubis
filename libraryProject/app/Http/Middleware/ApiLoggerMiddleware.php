<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiLoggerMiddleware
{
    /**
     * Gelen isteği işle.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Önce isteği bir sonraki aşamaya (Controller'a) gönderiyoruz
        $response = $next($request);

        // İstek tamamlandıktan sonra loglama yapıyoruz
        Log::info('API_LOG', [
            'url'    => $request->fullUrl(),
            'method' => $request->method(),
            'ip'     => $request->ip(),
            'request_body' => $request->all(),
            'status_code'  => $response->getStatusCode(),
            'response_body' => json_decode($response->getContent(), true),
        ]);

        return $response;
    }
}