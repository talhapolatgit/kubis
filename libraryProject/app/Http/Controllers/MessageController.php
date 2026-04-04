<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MessageController extends Controller
{
    public static function smsGonder($gsm,$message)
    {
        $body = "muhatapIdList=[]&hizliGonder=true&gsmList=[{$gsm}]&content={$message}";

        $response = Http::withHeaders([
            'Authorization' => 'applicationkey=BRIDGE,requestdate=2022-07-21T15:55:51+03:00,md5hashcode=9278682f6caad7c8fa5ba3f330a3bfb3',
            'Content-Type'  => 'application/json',
        ])
            ->withBody($body, 'application/json')
            ->withoutVerifying()
            ->post('https://servis.beyoglu.bel.tr/FlexCityUi/rest/json/sms/SendSms');

        return $response->json();
    }
}
