<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Eski tek "ad" alanını ad + soyad + siralama_adi yapısına taşır.
     *
     * @return array{ad: string, soyad: string, siralama_adi: string}
     */
    private static function parseLegacyTamMetin(string $full): array
    {
        $full = preg_replace('/\s+/u', ' ', trim($full));
        if ($full === '') {
            return ['ad' => '', 'soyad' => '', 'siralama_adi' => ''];
        }
        if (preg_match('/^([^,]+),\s*(.+)$/u', $full, $m)) {
            $soyad = trim($m[1]);
            $ad = trim($m[2]);
            if ($ad === '' && $soyad !== '') {
                $ad = $soyad;
                $soyad = '';
            }

            return [
                'ad'           => $ad,
                'soyad'        => $soyad,
                'siralama_adi' => $soyad !== '' ? ($soyad . ', ' . $ad) : $ad,
            ];
        }
        $parts = preg_split('/\s+/u', $full, -1, PREG_SPLIT_NO_EMPTY);
        if (count($parts) >= 2) {
            $soyad = array_pop($parts);
            $ad = implode(' ', $parts);

            return [
                'ad'           => $ad,
                'soyad'        => $soyad,
                'siralama_adi' => $soyad . ', ' . $ad,
            ];
        }

        return [
            'ad'           => $full,
            'soyad'        => '',
            'siralama_adi' => $full,
        ];
    }

    private function mergeDuplicateYazarlar(): void
    {
        $groups = DB::table('yazarlar')
            ->selectRaw('ad, soyad, MIN(id) as keep_id, GROUP_CONCAT(id ORDER BY id) as all_ids')
            ->groupBy('ad', 'soyad')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($groups as $g) {
            $keepId = (int) $g->keep_id;
            $ids = array_map('intval', explode(',', (string) $g->all_ids));
            foreach ($ids as $id) {
                if ($id === $keepId) {
                    continue;
                }
                DB::table('katalog')->where('yazarId', $id)->update(['yazarId' => $keepId]);
                DB::table('yazarlar')->where('id', $id)->delete();
            }
        }
    }

    public function up(): void
    {
        Schema::table('yazarlar', function (Blueprint $table) {
            $table->string('soyad', 255)->default('')->after('ad');
            $table->string('siralama_adi', 510)->default('')->after('soyad');
        });

        $rows = DB::table('yazarlar')->select('id', 'ad')->get();
        foreach ($rows as $row) {
            $parsed = self::parseLegacyTamMetin((string) $row->ad);
            $tam = trim($parsed['ad'] . ' ' . $parsed['soyad']);
            if ($parsed['siralama_adi'] === '' && $tam !== '') {
                $parsed['siralama_adi'] = $parsed['soyad'] !== ''
                    ? ($parsed['soyad'] . ', ' . $parsed['ad'])
                    : $parsed['ad'];
            }
            DB::table('yazarlar')->where('id', $row->id)->update([
                'ad'           => $parsed['ad'],
                'soyad'        => $parsed['soyad'],
                'siralama_adi' => $parsed['siralama_adi'],
            ]);
        }

        Schema::table('yazarlar', function (Blueprint $table) {
            $table->dropUnique('uq_yazarlar_ad');
        });

        $this->mergeDuplicateYazarlar();

        Schema::table('yazarlar', function (Blueprint $table) {
            $table->unique(['ad', 'soyad'], 'uq_yazarlar_ad_soyad');
        });
    }

    public function down(): void
    {
        Schema::table('yazarlar', function (Blueprint $table) {
            $table->dropUnique('uq_yazarlar_ad_soyad');
        });

        $rows = DB::table('yazarlar')->select('id', 'ad', 'soyad')->get();
        foreach ($rows as $row) {
            $tam = trim(trim((string) $row->ad) . ' ' . trim((string) $row->soyad));
            DB::table('yazarlar')->where('id', $row->id)->update(['ad' => $tam !== '' ? $tam : (string) $row->ad]);
        }

        Schema::table('yazarlar', function (Blueprint $table) {
            $table->dropColumn(['soyad', 'siralama_adi']);
        });

        Schema::table('yazarlar', function (Blueprint $table) {
            $table->unique('ad', 'uq_yazarlar_ad');
        });
    }
};
