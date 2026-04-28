<?php

namespace App\Http\Controllers;

use App\Models\Katalog;
use App\Models\Kategori;
use App\Models\Kutuphane;
use App\Models\OduncIslem;
use App\Models\Uye;
use App\Models\UyeRezerve;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $u = $request->user();
        abort_unless($u, 403);
        abort_unless($u->hasYetki(21), 403);

        $flags = [
            'catalog'   => $u->hasYetki(1) || $u->hasYetki(2) || $u->hasYetki(4) || $u->hasYetki(5),
            'loans'     => $u->hasYetki(7) || $u->hasYetki(8) || $u->hasYetki(9) || $u->hasYetki(10),
            'members'   => $u->hasYetki(11) || $u->hasYetki(13),
            'libraries' => $u->hasYetki(17) || $u->hasYetki(19),
        ];

        $stats = [];
        $durumBreakdown = collect();
        $recentLoans = collect();
        $recentMembers = collect();
        $kutuphaneCount = null;
        $kategoriBreakdown = collect();
        $kategoriMax = 1;
        $kutuphaneAktifKatalog = collect();
        $kutuphaneAktifMax = 1;
        $topCatalogCreators = collect();
        $uyeCinsiyetBreakdown = collect();
        $uyeCinsiyetMax = 1;
        $uyeYasBreakdown = collect();
        $uyeYasMax = 1;

        if ($flags['catalog']) {
            $base = Katalog::query();
            $this->scopeKatalogForUser($base, $u);
            $stats['katalog_total'] = (clone $base)->count();
            $stats['katalog_rafa'] = (clone $base)->where('kunyeDurum', 'Rafta')->count();
            $stats['katalog_odunc'] = (clone $base)->where('kunyeDurum', 'Ödünç')->count();
            $stats['katalog_rezerve'] = (clone $base)->where('kunyeDurum', 'Rezerve')->count();
            $stats['katalog_kayip'] = (clone $base)->where('kunyeDurum', 'Kayıp')->count();
            $stats['katalog_bakim'] = (clone $base)->where('kunyeDurum', 'Bakımda')->count();
            $stats['katalog_etiketlenmemis'] = (clone $base)->where(function ($q) {
                $q->whereNull('etiketlendi')->orWhere('etiketlendi', 0);
            })->count();
            $stats['katalog_bu_ay'] = (clone $base)->where('created_at', '>=', now()->startOfMonth())->count();

            $durumBreakdown = (clone $base)
                ->select('kunyeDurum', DB::raw('count(*) as c'))
                ->groupBy('kunyeDurum')
                ->orderByDesc('c')
                ->get()
                ->map(fn ($r) => [
                    'label' => $r->kunyeDurum ?: '—',
                    'count' => (int) $r->c,
                ]);

            $kategoriMap = Kategori::query()->pluck('title', 'id');
            $kategoriBreakdown = (clone $base)
                ->select('kunyeKategori', DB::raw('count(*) as c'))
                ->groupBy('kunyeKategori')
                ->orderByDesc('c')
                ->get()
                ->map(function ($r) use ($kategoriMap) {
                    $kid = $r->kunyeKategori;
                    if ($kid === null || $kid === '' || (int) $kid === 0) {
                        $label = 'Kategorisiz';
                    } else {
                        $label = $kategoriMap[(int) $kid] ?? ('Kategori #'.(int) $kid);
                    }

                    return ['label' => $label, 'count' => (int) $r->c];
                })
                ->values();
            $kategoriMax = max((int) $kategoriBreakdown->max('count'), 1);

            $khRows = (clone $base)
                ->whereIn('kunyeDurum', ['Rafta', 'Ödünç', 'Rezerve'])
                ->select('kutuphaneId', DB::raw('count(*) as c'))
                ->groupBy('kutuphaneId')
                ->orderByDesc('c')
                ->get();
            $khIds = $khRows->pluck('kutuphaneId')->filter(fn ($id) => $id !== null && $id !== '')->unique()->all();
            $khTitles = $khIds === []
                ? collect()
                : Kutuphane::query()->whereIn('id', $khIds)->pluck('title', 'id');
            $kutuphaneAktifKatalog = $khRows->map(function ($r) use ($khTitles) {
                $id = $r->kutuphaneId;
                if ($id === null || $id === '') {
                    $label = 'Kütüphane atanmamış';
                } elseif ($khTitles->has((int) $id)) {
                    $label = $khTitles[(int) $id];
                } else {
                    $label = 'Kütüphane #'.(int) $id;
                }

                return ['label' => $label, 'count' => (int) $r->c];
            })->values();
            $kutuphaneAktifMax = max((int) $kutuphaneAktifKatalog->max('count'), 1);

            $creatorRows = (clone $base)
                ->whereNotNull('created_user')
                ->select('created_user', DB::raw('count(*) as c'))
                ->groupBy('created_user')
                ->orderByDesc('c')
                ->limit(5)
                ->get();
            $creatorIds = $creatorRows->pluck('created_user')->map(fn ($id) => (int) $id)->all();
            $creatorNames = $creatorIds === []
                ? collect()
                : User::query()
                    ->whereIn('id', $creatorIds)
                    ->get(['id', 'name', 'soyad'])
                    ->keyBy('id');
            $topCatalogCreators = $creatorRows->map(function ($row) use ($creatorNames) {
                $user = $creatorNames->get((int) $row->created_user);
                $fullName = trim((string) ($user->name ?? '') . ' ' . (string) ($user->soyad ?? ''));
                if ($fullName === '') {
                    $fullName = 'Kullanıcı #' . (int) $row->created_user;
                }

                return [
                    'label' => $fullName,
                    'count' => (int) $row->c,
                ];
            })->values();
        }

        if ($flags['loans']) {
            $ob = OduncIslem::query();
            $this->scopeOduncForUser($ob, $u);
            $stats['odunc_aktif'] = (clone $ob)->where('statu', 'aktif')->count();
            $stats['odunc_gecikti'] = (clone $ob)->where('statu', 'aktif')
                ->where('iade_tarihi_planlanan', '<', now()->toDateString())
                ->count();
            $stats['odunc_bugun_iade'] = (clone $ob)->where('statu', 'iade_edildi')
                ->whereDate('iade_tarihi_gercek', today())
                ->count();
            $stats['odunc_kayip'] = (clone $ob)->where('statu', 'kayip')->count();
            $stats['odunc_bu_ay'] = (clone $ob)->where('odunc_tarihi', '>=', now()->startOfMonth())->count();

            $rq = UyeRezerve::query();
            if (! $u->hasYetki(9) && ! $u->hasYetki(10)) {
                $ids = $u->yetkiliKutuphaneIds();
                $rq->whereHas('katalog', function ($k) use ($ids) {
                    $k->whereIn('kutuphaneId', $ids ?: [-1]);
                });
            }
            $stats['rezerve_aktif'] = (clone $rq)->where('iptalMi', 'false')
                ->where('oduncAldiMi', 'false')
                ->where('rezerve_bitis', '>', now())
                ->count();

            $recentLoans = OduncIslem::query()
                ->with(['uye', 'katalog', 'kutuphane'])
                ->tap(fn ($q) => $this->scopeOduncForUser($q, $u))
                ->orderByDesc('id')
                ->limit(7)
                ->get();
        }

        if ($flags['members']) {
            $stats['uye_toplam'] = Uye::count();
            $stats['uye_aktif'] = Uye::aktif()->count();
            $stats['uye_bu_ay'] = Uye::where('created_at', '>=', now()->startOfMonth())->count();
            $recentMembers = Uye::query()
                ->orderByDesc('id')
                ->limit(6)
                ->get(['id', 'ad', 'soyad', 'statu', 'created_at']);

            [$uyeCinsiyetBreakdown, $uyeYasBreakdown] = $this->buildUyeDemografi();
            $uyeCinsiyetMax = max((int) $uyeCinsiyetBreakdown->max('count'), 1);
            $uyeYasMax = max((int) $uyeYasBreakdown->max('count'), 1);
        }

        if ($flags['libraries']) {
            $kutuphaneCount = Kutuphane::whereNull('deleted_at')->count();
        }

        $durumMax = max($durumBreakdown->max('count') ?? 0, 1);

        return view('dashboard', compact(
            'flags',
            'stats',
            'durumBreakdown',
            'durumMax',
            'recentLoans',
            'recentMembers',
            'kutuphaneCount',
            'kategoriBreakdown',
            'kategoriMax',
            'kutuphaneAktifKatalog',
            'kutuphaneAktifMax',
            'topCatalogCreators',
            'uyeCinsiyetBreakdown',
            'uyeCinsiyetMax',
            'uyeYasBreakdown',
            'uyeYasMax',
        ));
    }

    /**
     * @return array{0: Collection<int, array{label: string, count: int}>, 1: Collection<int, array{label: string, count: int}>}
     */
    private function buildUyeDemografi(): array
    {
        $cins = ['erkek' => 0, 'kadin' => 0, 'diger' => 0, '_none' => 0];
        $yas = [
            '0–12' => 0,
            '13–17' => 0,
            '18–24' => 0,
            '25–44' => 0,
            '45–64' => 0,
            '65+' => 0,
            'Belirsiz' => 0,
        ];

        foreach (Uye::query()->cursor() as $uye) {
            $raw = $uye->cinsiyet;
            if (in_array($raw, ['erkek', 'kadin', 'diger'], true)) {
                $cins[$raw]++;
            } else {
                $cins['_none']++;
            }

            if (! $uye->dogum_tarihi) {
                $yas['Belirsiz']++;
                continue;
            }
            $age = Carbon::parse($uye->dogum_tarihi)->age;
            if ($age < 0) {
                $yas['Belirsiz']++;
            } elseif ($age <= 12) {
                $yas['0–12']++;
            } elseif ($age <= 17) {
                $yas['13–17']++;
            } elseif ($age <= 24) {
                $yas['18–24']++;
            } elseif ($age <= 44) {
                $yas['25–44']++;
            } elseif ($age <= 64) {
                $yas['45–64']++;
            } else {
                $yas['65+']++;
            }
        }

        $cinsColl = collect([
            ['label' => 'Erkek', 'count' => $cins['erkek']],
            ['label' => 'Kadın', 'count' => $cins['kadin']],
            ['label' => 'Diğer', 'count' => $cins['diger']],
            ['label' => 'Belirtilmemiş', 'count' => $cins['_none']],
        ])->filter(fn ($row) => $row['count'] > 0)->values();

        $yasOrder = ['0–12', '13–17', '18–24', '25–44', '45–64', '65+', 'Belirsiz'];
        $yasColl = collect($yasOrder)
            ->map(function ($key) use ($yas) {
                $label = match ($key) {
                    'Belirsiz' => 'Doğum bilgisi yok / geçersiz',
                    default => $key.' yaş',
                };

                return ['label' => $label, 'count' => $yas[$key]];
            })
            ->filter(fn ($row) => $row['count'] > 0)
            ->values();

        return [$cinsColl, $yasColl];
    }

    private function scopeKatalogForUser($query, $requestUser): void
    {
        if ($requestUser->hasYetki(4) || $requestUser->hasYetki(5)) {
            return;
        }
        if ($requestUser->hasYetki(1) || $requestUser->hasYetki(2)) {
            $ids = $requestUser->yetkiliKutuphaneIds();
            $query->whereIn('kutuphaneId', $ids ?: [-1]);

            return;
        }
        $query->whereRaw('1 = 0');
    }

    private function scopeOduncForUser($query, $requestUser): void
    {
        if ($requestUser->hasYetki(9) || $requestUser->hasYetki(10)) {
            return;
        }
        if ($requestUser->hasYetki(7) || $requestUser->hasYetki(8)) {
            $ids = $requestUser->yetkiliKutuphaneIds();
            $query->where(function ($q) use ($ids) {
                $q->whereIn('kutuphane_id', $ids ?: [-1])
                    ->orWhere(function ($q2) use ($ids) {
                        $q2->whereNull('kutuphane_id')
                            ->whereHas('katalog', function ($k) use ($ids) {
                                $k->whereIn('kutuphaneId', $ids ?: [-1]);
                            });
                    });
            });

            return;
        }
        $query->whereRaw('1 = 0');
    }
}
