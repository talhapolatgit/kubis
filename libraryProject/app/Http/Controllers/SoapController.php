<?php

namespace App\Http\Controllers;

use App\Services\KatalogSoapService;
use Illuminate\Http\Request;

class SoapController extends Controller
{
    protected string $wsdlTemplate;
    protected string $serviceUrl;

    public function handle(Request $request)
    {
        $this->serviceUrl   = $request->getSchemeAndHttpHost() . '/soap/katalog';
        $this->wsdlTemplate = resource_path('wsdl/kutuphane.wsdl');

        if ($request->query('wsdl') !== null) {
            return $this->wsdl();
        }

        return $this->serve();
    }

    // ─── WSDL sun ────────────────────────────────────────────────────────────────
    private function wsdl()
    {
        $xml = file_get_contents($this->wsdlTemplate);
        $xml = str_replace('__SERVICE_URL__', $this->serviceUrl, $xml);

        return response($xml, 200)
            ->header('Content-Type', 'text/xml; charset=utf-8');
    }

    // ─── SOAP isteğini karşıla ────────────────────────────────────────────────────
    private function serve()
    {
        // WSDL'i geçici dosyaya yaz (php artisan serve single-thread sorununu aşmak için)
        $wsdlXml = file_get_contents($this->wsdlTemplate);
        $wsdlXml = str_replace('__SERVICE_URL__', $this->serviceUrl, $wsdlXml);

        $tmpFile = tempnam(sys_get_temp_dir(), 'wsdl_') . '.xml';
        file_put_contents($tmpFile, $wsdlXml);

        $server = new \SoapServer($tmpFile, [
            'encoding' => 'UTF-8',
        ]);

        $server->setObject(app(KatalogSoapService::class));

        ob_start();
        $server->handle();
        $response = ob_get_clean();

        @unlink($tmpFile);

        return response($response, 200)
            ->header('Content-Type', 'text/xml; charset=utf-8');
    }
}
