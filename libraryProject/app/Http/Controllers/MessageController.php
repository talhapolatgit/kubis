<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

class MessageController extends Controller
{
    public static function smsGonder($gsm, $message, ?string $source = null)
    {
        $body = "muhatapIdList=[]&hizliGonder=true&gsmList=[{$gsm}]&content={$message}";
        $httpStatus = null;
        $responseBody = null;
        $isSuccess = false;
        $result = null;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'applicationkey=BRIDGE,requestdate=2022-07-21T15:55:51+03:00,md5hashcode=9278682f6caad7c8fa5ba3f330a3bfb3',
                'Content-Type'  => 'application/json',
            ])
                ->withBody($body, 'application/json')
                ->withoutVerifying()
                ->post('https://servis.beyoglu.bel.tr/FlexCityUi/rest/json/sms/SendSms');

            $httpStatus = $response->status();
            $responseBody = $response->body();
            $isSuccess = $response->successful();
            $result = $response->json();
        } catch (Throwable $e) {
            $responseBody = $e->getMessage();
            $result = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        } finally {
            DB::table('sms_logs')->insert([
                'gsm' => (string) $gsm,
                'message' => (string) $message,
                'is_success' => $isSuccess ? 1 : 0,
                'http_status' => $httpStatus,
                'response_body' => $responseBody,
                'source' => $source,
                'created_user' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $result;
    }
}
