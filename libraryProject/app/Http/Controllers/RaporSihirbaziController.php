<?php

namespace App\Http\Controllers;

use App\Models\OduncIslem;
use App\Models\Katalog;
use App\Models\Kutuphane;
use App\Models\Uye;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RaporSihirbaziController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()?->hasYetki(26), 403);
        $kutuphaneler = Kutuphane::query()
            ->whereNull('deleted_at')
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('rapor.sihirbaz', compact('kutuphaneler'));
    }

    public function uyeListesiData(Request $request)
    {
        abort_unless(auth()->user()?->hasYetki(26), 403);

        $perPage = min(max((int) $request->input('per_page', 500), 1), 2000);
        $query = Uye::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('ad', 'LIKE', "%{$search}%")
                    ->orWhere('soyad', 'LIKE', "%{$search}%")
                    ->orWhere('tc_kimlik', 'LIKE', "%{$search}%")
                    ->orWhere('telefon', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $statu = (string) $request->input('statu', '');
        if (in_array($statu, ['aktif', 'pasif'], true)) {
            $query->where('statu', $statu);
        }

        $cinsiyet = (string) $request->input('cinsiyet', '');
        if (in_array($cinsiyet, ['erkek', 'kadin', 'diger'], true)) {
            $query->where('cinsiyet', $cinsiyet);
        }

        if ($request->filled('il')) {
            $query->where('il', 'LIKE', '%' . trim((string) $request->input('il')) . '%');
        }
        if ($request->filled('ilce')) {
            $query->where('ilce', 'LIKE', '%' . trim((string) $request->input('ilce')) . '%');
        }
        if ($request->filled('mahalle')) {
            $query->where('mahalle', 'LIKE', '%' . trim((string) $request->input('mahalle')) . '%');
        }

        $ogretimDurumu = trim((string) $request->input('ogretim_durumu', ''));
        if ($ogretimDurumu !== '') {
            $query->where('ogretim_durumu', $ogretimDurumu);
        }

        if ($request->filled('kayit_tarihi_bas')) {
            $query->whereDate('created_at', '>=', $request->input('kayit_tarihi_bas'));
        }
        if ($request->filled('kayit_tarihi_bit')) {
            $query->whereDate('created_at', '<=', $request->input('kayit_tarihi_bit'));
        }

        $yasIlk = $request->filled('yas_ilk') ? max(0, (int) $request->input('yas_ilk')) : null;
        $yasSon = $request->filled('yas_son') ? max(0, (int) $request->input('yas_son')) : null;
        if ($yasIlk !== null && $yasSon !== null && $yasIlk > $yasSon) {
            [$yasIlk, $yasSon] = [$yasSon, $yasIlk];
        }
        if ($yasIlk !== null) {
            $query->whereDate('dogum_tarihi', '<=', Carbon::today()->subYears($yasIlk)->toDateString());
        }
        if ($yasSon !== null) {
            $query->whereDate('dogum_tarihi', '>=', Carbon::today()->subYears($yasSon + 1)->addDay()->toDateString());
        }

        $oduncteKitabiOlanlar = (string) $request->input('oduncte_kitabi_olanlar', '');
        if ($oduncteKitabiOlanlar === 'evet') {
            $query->whereExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from((new OduncIslem())->getTable() . ' as oi')
                    ->whereColumn('oi.uye_id', 'uyeler.id')
                    ->where('oi.statu', 'aktif');
            });
        } elseif ($oduncteKitabiOlanlar === 'hayir') {
            $query->whereNotExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from((new OduncIslem())->getTable() . ' as oi')
                    ->whereColumn('oi.uye_id', 'uyeler.id')
                    ->where('oi.statu', 'aktif');
            });
        }

        $uyeler = $query
            ->orderBy('ad')
            ->orderBy('soyad')
            ->limit($perPage)
            ->get([
                'id',
                'ad',
                'soyad',
                'tc_kimlik',
                'telefon',
                'email',
                'cinsiyet',
                'il',
                'ilce',
                'mahalle',
                'ogretim_durumu',
                'statu',
                'uyelik_baslangic',
                'uyelik_bitis',
                'created_at',
            ]);

        $rows = $uyeler->map(function ($uye) {
            return [
                'id' => (int) $uye->id,
                'ad_soyad' => trim(($uye->ad ?? '') . ' ' . ($uye->soyad ?? '')),
                'tc_kimlik' => (string) ($uye->tc_kimlik ?? ''),
                'telefon' => (string) ($uye->telefon ?? ''),
                'email' => (string) ($uye->email ?? ''),
                'cinsiyet' => (string) ($uye->cinsiyet ?? ''),
                'il_ilce' => trim((string) ($uye->il ?? '')) . ((string) ($uye->ilce ?? '') !== '' ? ' / ' . (string) $uye->ilce : ''),
                'mahalle' => (string) ($uye->mahalle ?? ''),
                'ogretim_durumu' => (string) ($uye->ogretim_durumu ?? ''),
                'statu' => $uye->statu === 'aktif' ? 'Aktif' : 'Pasif',
                'uyelik_baslangic' => $uye->uyelik_baslangic ? Carbon::parse($uye->uyelik_baslangic)->format('d.m.Y') : '—',
                'uyelik_bitis' => $uye->uyelik_bitis ? Carbon::parse($uye->uyelik_bitis)->format('d.m.Y') : '—',
                'kayit_tarihi' => $uye->created_at ? Carbon::parse($uye->created_at)->format('d.m.Y') : '—',
            ];
        })->values();

        return response()->json([
            'rows' => $rows,
            'count' => $rows->count(),
            'limit' => $perPage,
        ]);
    }

    public function kullaniciKatalogKayitSayilariData(Request $request)
    {
        abort_unless(auth()->user()?->hasYetki(26), 403);
        $ayrimYap = $request->boolean('ayrim_yap');

        $katalogTable = (new Katalog())->getTable();
        $query = Katalog::query()
            ->from($katalogTable . ' as k')
            ->whereNull('k.deleted_at');

        if ($request->filled('kayit_tarihi_bas')) {
            $query->whereDate('k.created_at', '>=', $request->input('kayit_tarihi_bas'));
        }
        if ($request->filled('kayit_tarihi_bit')) {
            $query->whereDate('k.created_at', '<=', $request->input('kayit_tarihi_bit'));
        }

        $baseQuery = $query
            ->selectRaw('k.created_user as user_id')
            ->selectRaw('COUNT(*) as toplam')
            ->groupBy('k.created_user')
            ->orderByDesc('toplam');

        if ($ayrimYap) {
            $isbnExpr = "LOWER(REPLACE(REPLACE(REPLACE(TRIM(COALESCE(k.kunyeISBNISSN, '')), '-', ''), ' ', ''), '.', ''))";
            $isbnExistsEarlierExpr = "EXISTS (
                SELECT 1
                FROM {$katalogTable} k2
                WHERE k2.deleted_at IS NULL
                  AND LOWER(REPLACE(REPLACE(REPLACE(TRIM(COALESCE(k2.kunyeISBNISSN, '')), '-', ''), ' ', ''), '.', '')) = {$isbnExpr}
                  AND (k2.created_at < k.created_at OR (k2.created_at = k.created_at AND k2.id < k.id))
            )";
            $baseQuery
                ->selectRaw("SUM(CASE WHEN ({$isbnExpr} = '' OR NOT {$isbnExistsEarlierExpr}) THEN 1 ELSE 0 END) as ilk_giris_sayisi")
                ->selectRaw("SUM(CASE WHEN ({$isbnExpr} <> '' AND {$isbnExistsEarlierExpr}) THEN 1 ELSE 0 END) as kopya_sayisi");
        }

        $aggregates = $baseQuery->get();

        $userIds = $aggregates
            ->pluck('user_id')
            ->filter(fn ($id) => !is_null($id) && (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $usersById = User::query()
            ->whereIn('id', $userIds->all())
            ->get(['id', 'name'])
            ->keyBy('id');

        $rows = $aggregates->map(function ($row) use ($usersById, $ayrimYap) {
            $userId = is_null($row->user_id) ? null : (int) $row->user_id;
            $name = 'Atanmamış kullanıcı';
            if (!is_null($userId) && $userId > 0) {
                $name = trim((string) optional($usersById->get($userId))->name);
                if ($name === '') {
                    $name = 'Bilinmeyen kullanıcı (#' . $userId . ')';
                }
            }

            return [
                'kullanici_id' => $userId,
                'kullanici' => $name,
                'toplam' => (int) $row->toplam,
                'ilk_giris' => $ayrimYap ? (int) ($row->ilk_giris_sayisi ?? 0) : null,
                'kopya' => $ayrimYap ? (int) ($row->kopya_sayisi ?? 0) : null,
            ];
        })->values();

        return response()->json([
            'ayrim_yap' => $ayrimYap,
            'rows' => $rows,
            'count' => $rows->count(),
            'toplam_katalog' => (int) $rows->sum('toplam'),
            'toplam_ilk_giris' => $ayrimYap ? (int) $rows->sum('ilk_giris') : null,
            'toplam_kopya' => $ayrimYap ? (int) $rows->sum('kopya') : null,
        ]);
    }

    public function oduncListesiData(Request $request)
    {
        abort_unless(auth()->user()?->hasYetki(26), 403);

        $perPage = min(max((int) $request->input('per_page', 500), 1), 2000);
        $today = Carbon::today()->toDateString();

        $query = OduncIslem::query()->with(['uye', 'katalog', 'kutuphane']);

        $statu = (string) $request->input('statu', 'hepsi');
        if (in_array($statu, ['aktif', 'iade_edildi', 'kayip'], true)) {
            $query->where('statu', $statu);
        } elseif ($statu === 'gecikti') {
            $query->where('statu', 'aktif')
                ->whereDate('iade_tarihi_planlanan', '<', $today);
        }

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('uye', function ($u) use ($search) {
                    $u->where('ad', 'LIKE', "%{$search}%")
                        ->orWhere('soyad', 'LIKE', "%{$search}%")
                        ->orWhere('tc_kimlik', 'LIKE', "%{$search}%");
                })->orWhereHas('katalog', function ($k) use ($search) {
                    $k->where('kunyeEserAdi', 'LIKE', "%{$search}%")
                        ->orWhere('kunyeISBNISSN', 'LIKE', "%{$search}%");
                });
            });
        }

        $demirbasNo = trim((string) $request->input('demirbas_no', ''));
        if ($demirbasNo !== '') {
            $query->whereHas('katalog', function ($k) use ($demirbasNo) {
                $k->where('kunyeDemirbasKN', 'LIKE', "%{$demirbasNo}%");
            });
        }

        $kutuphaneId = (int) $request->input('kutuphane_id', 0);
        if ($kutuphaneId > 0) {
            $query->where(function ($q) use ($kutuphaneId) {
                $q->where('kutuphane_id', $kutuphaneId)
                    ->orWhere(function ($q2) use ($kutuphaneId) {
                        $q2->whereNull('kutuphane_id')
                            ->whereHas('katalog', function ($k) use ($kutuphaneId) {
                                $k->where('kutuphaneId', $kutuphaneId);
                            });
                    });
            });
        }

        if ($request->filled('odunc_tarihi_bas')) {
            $query->whereDate('odunc_tarihi', '>=', $request->input('odunc_tarihi_bas'));
        }
        if ($request->filled('odunc_tarihi_bit')) {
            $query->whereDate('odunc_tarihi', '<=', $request->input('odunc_tarihi_bit'));
        }
        if ($request->filled('iade_planlanan_bas')) {
            $query->whereDate('iade_tarihi_planlanan', '>=', $request->input('iade_planlanan_bas'));
        }
        if ($request->filled('iade_planlanan_bit')) {
            $query->whereDate('iade_tarihi_planlanan', '<=', $request->input('iade_planlanan_bit'));
        }

        $gecikmeDurumu = (string) $request->input('gecikme_durumu', 'hepsi');
        if ($gecikmeDurumu === 'geciken') {
            $query->where(function ($q) use ($today) {
                $q->where(function ($q1) use ($today) {
                    $q1->where('statu', 'aktif')
                        ->whereDate('iade_tarihi_planlanan', '<', $today);
                })->orWhere(function ($q2) {
                    $q2->where('statu', 'iade_edildi')
                        ->whereNotNull('iade_tarihi_planlanan')
                        ->whereNotNull('iade_tarihi_gercek')
                        ->whereColumn('iade_tarihi_gercek', '>', 'iade_tarihi_planlanan');
                });
            });
        } elseif ($gecikmeDurumu === 'gecikmeyen') {
            $query->where(function ($q) use ($today) {
                $q->where(function ($q1) use ($today) {
                    $q1->where('statu', 'aktif')
                        ->whereDate('iade_tarihi_planlanan', '>=', $today);
                })->orWhere(function ($q2) {
                    $q2->where('statu', 'iade_edildi')
                        ->whereNotNull('iade_tarihi_planlanan')
                        ->whereNotNull('iade_tarihi_gercek')
                        ->whereColumn('iade_tarihi_gercek', '<=', 'iade_tarihi_planlanan');
                });
            });
        }

        $gecikmeGunMin = $request->filled('gecikme_gun_min')
            ? max(0, (int) $request->input('gecikme_gun_min'))
            : null;
        $gecikmeGunMax = $request->filled('gecikme_gun_max')
            ? max(0, (int) $request->input('gecikme_gun_max'))
            : null;
        if (!is_null($gecikmeGunMin) && !is_null($gecikmeGunMax) && $gecikmeGunMin > $gecikmeGunMax) {
            [$gecikmeGunMin, $gecikmeGunMax] = [$gecikmeGunMax, $gecikmeGunMin];
        }

        $todaySql = Carbon::today()->toDateString();
        $gecikmeGunExpr = "CASE
            WHEN statu = 'aktif'
                AND iade_tarihi_planlanan IS NOT NULL
                AND DATE(iade_tarihi_planlanan) < '{$todaySql}'
                THEN DATEDIFF('{$todaySql}', DATE(iade_tarihi_planlanan))
            WHEN statu = 'iade_edildi'
                AND iade_tarihi_planlanan IS NOT NULL
                AND iade_tarihi_gercek IS NOT NULL
                AND DATE(iade_tarihi_gercek) > DATE(iade_tarihi_planlanan)
                THEN DATEDIFF(DATE(iade_tarihi_gercek), DATE(iade_tarihi_planlanan))
            ELSE 0
        END";
        if (!is_null($gecikmeGunMin)) {
            $query->whereRaw("({$gecikmeGunExpr}) >= {$gecikmeGunMin}");
        }
        if (!is_null($gecikmeGunMax)) {
            $query->whereRaw("({$gecikmeGunExpr}) <= {$gecikmeGunMax}");
        }

        $islemler = $query
            ->orderByDesc('odunc_tarihi')
            ->orderByDesc('id')
            ->limit($perPage)
            ->get();

        $rows = $islemler->map(function ($islem) use ($today) {
            $gecikmeDurumuLabel = '—';
            $gecikmeGun = 0;
            $hasPlanlanan = (bool) $islem->iade_tarihi_planlanan;

            if ($islem->statu === 'aktif' && $hasPlanlanan) {
                if ($islem->iade_tarihi_planlanan->toDateString() < $today) {
                    $gecikmeDurumuLabel = 'Geciken';
                    $gecikmeGun = Carbon::today()->diffInDays($islem->iade_tarihi_planlanan);
                } else {
                    $gecikmeDurumuLabel = 'Gecikmeyen';
                }
            } elseif ($islem->statu === 'iade_edildi' && $hasPlanlanan && $islem->iade_tarihi_gercek) {
                if ($islem->iade_tarihi_gercek->gt($islem->iade_tarihi_planlanan)) {
                    $gecikmeDurumuLabel = 'Geciken';
                    $gecikmeGun = $islem->iade_tarihi_planlanan->diffInDays($islem->iade_tarihi_gercek);
                } else {
                    $gecikmeDurumuLabel = 'Gecikmeyen';
                }
            }
            $gecikmeGun = abs((int) $gecikmeGun);

            $statuLabel = match ($islem->statu) {
                'aktif' => 'Aktif',
                'iade_edildi' => 'İade Edildi',
                'kayip' => 'Kayıp',
                default => (string) $islem->statu,
            };

            return [
                'id' => (int) $islem->id,
                'uye_ad' => trim((string) optional($islem->uye)->ad . ' ' . (string) optional($islem->uye)->soyad),
                'uye_tc' => (string) optional($islem->uye)->tc_kimlik,
                'kitap_adi' => (string) optional($islem->katalog)->kunyeEserAdi,
                'isbn' => (string) optional($islem->katalog)->kunyeISBNISSN,
                'demirbas' => (string) optional($islem->katalog)->kunyeDemirbasKN,
                'kutuphane' => (string) (
                    optional($islem->kutuphane)->title
                    ?: optional(optional($islem->katalog)->kutuphane)->title
                ),
                'odunc_tarihi' => $islem->odunc_tarihi ? Carbon::parse($islem->odunc_tarihi)->format('d.m.Y') : '—',
                'iade_planlanan' => $islem->iade_tarihi_planlanan ? Carbon::parse($islem->iade_tarihi_planlanan)->format('d.m.Y') : '—',
                'iade_gercek' => $islem->iade_tarihi_gercek ? Carbon::parse($islem->iade_tarihi_gercek)->format('d.m.Y') : '—',
                'statu' => $statuLabel,
                'gecikme_durumu' => $gecikmeDurumuLabel,
                'gecikme_gun' => $gecikmeGun,
            ];
        })->values();

        return response()->json([
            'rows' => $rows,
            'count' => $rows->count(),
            'limit' => $perPage,
        ]);
    }
}
