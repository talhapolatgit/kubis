<?php

namespace App\Services\Sms;

interface SmsGatewayInterface
{
    public function send(string $gsm, string $message): SmsResult;
}
