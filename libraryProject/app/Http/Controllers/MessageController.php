<?php

namespace App\Http\Controllers;

use App\Services\Sms\SmsService;

class MessageController extends Controller
{
    public static function smsGonder($gsm, $message, ?string $source = null)
    {
        return app(SmsService::class)->send($gsm, $message, $source);
    }
}
