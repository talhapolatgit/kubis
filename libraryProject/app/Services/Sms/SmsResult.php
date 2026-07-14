<?php

namespace App\Services\Sms;

final class SmsResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?int $httpStatus = null,
        public readonly ?string $responseBody = null,
        public readonly mixed $payload = null,
        public readonly ?string $errorMessage = null,
    ) {}

    public static function failure(string $message, ?int $httpStatus = null, ?string $responseBody = null): self
    {
        return new self(
            success: false,
            httpStatus: $httpStatus,
            responseBody: $responseBody ?? $message,
            payload: ['success' => false, 'message' => $message],
            errorMessage: $message,
        );
    }
}
