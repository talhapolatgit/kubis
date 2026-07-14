<?php

namespace App\Services\Ldap;

use App\Models\Entegrasyon;

class LdapSettings
{
    /**
     * Aktif LDAP entegrasyon ayarlarını döndürür.
     *
     * @return array{host: string, base_dn: string, protocol_version: int, referrals: bool}|null
     */
    public static function current(): ?array
    {
        $entegrasyon = Entegrasyon::ldap();
        if (! $entegrasyon) {
            return null;
        }

        $ayarlar = is_array($entegrasyon->ayarlar) ? $entegrasyon->ayarlar : [];
        $host = trim((string) ($ayarlar['host'] ?? ''));
        $baseDn = trim((string) ($ayarlar['base_dn'] ?? ''));

        if ($host === '' || $baseDn === '') {
            return null;
        }

        return [
            'host' => $host,
            'base_dn' => $baseDn,
            'protocol_version' => (int) ($ayarlar['protocol_version'] ?? 3) ?: 3,
            'referrals' => (bool) ($ayarlar['referrals'] ?? false),
        ];
    }
}
