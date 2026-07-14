<?php

namespace App\Services\Sms;

use App\Models\Entegrasyon;
use InvalidArgumentException;
use RuntimeException;

class SmsGatewayFactory
{
    /**
     * @var array<string, class-string<SmsGatewayInterface>>
     */
    private const DRIVERS = [
        'flexcity' => FlexcitySmsGateway::class,
    ];

    public function make(?Entegrasyon $entegrasyon = null): SmsGatewayInterface
    {
        $entegrasyon ??= Entegrasyon::sms();

        if (! $entegrasyon) {
            throw new RuntimeException('Aktif SMS entegrasyonu bulunamadı.');
        }

        if ($entegrasyon->tip !== 'sms') {
            throw new InvalidArgumentException('Entegrasyon tipi SMS değil.');
        }

        $driver = strtolower((string) $entegrasyon->saglayici);
        $class = self::DRIVERS[$driver] ?? null;

        if (! $class) {
            throw new InvalidArgumentException("Desteklenmeyen SMS sağlayıcısı: {$entegrasyon->saglayici}");
        }

        /** @var array<string, mixed> $ayarlar */
        $ayarlar = $entegrasyon->ayarlar ?? [];

        return new $class($ayarlar);
    }

    /**
     * @return list<string>
     */
    public static function supportedDrivers(): array
    {
        return array_keys(self::DRIVERS);
    }
}
