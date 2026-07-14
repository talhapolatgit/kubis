<?php

namespace App\Http\Controllers;

use App\Models\OduncIslem;
use App\Models\Uye;
use App\Models\Katalog;
use App\Models\Kutuphane;
use App\Models\UyeBekleme;
use App\Models\UyeRezerve;
use App\Services\WebhookService;
use App\Support\TurkishSearch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OduncController extends Controller
{

    public function __construct(
        private readonly WebhookService $webhookService
    ) {}


    private function canViewAllLoans(): bool
    {
        $u = auth()->user();
        return $u && ($u->hasYetki(9) || $u->hasYetki(10));
    }

    private function canViewScopedLoans(): bool
    {
        $u = auth()->user();
        return $u && ($u->hasYetki(7) || $u->hasYetki(8));
    }

    private function canDoAllLoans(): bool
    {
        $u = auth()->user();
        return $u && ($u->hasYetki(10));
    }

    private function canDoScopedLoans(): bool
    {
        $u = auth()->user();
        return $u && ($u->hasYetki(8));
    }

    private function canExtendLoans(): bool
    {
        $u = auth()->user();
        return $u && ($u->hasYetki(35));
    }

    private function oduncFilterValues(Request $request): array
    {
        return [
            'filter_uye'   => trim((string) $request->input('filter_uye', '')),
            'filter_kitap' => trim((string) $request->input('filter_kitap', '')),
        ];
    }

    private function applyUyeFilterOnRelation($uyeQuery, string $term): void
    {
        $normalized = preg_replace('/\s+/', ' ', $term);
        $uyeQuery->where(function ($uq) use ($term, $normalized) {
            TurkishSearch::applyTextMatch($uq, 'ad', $term, 'contains', 'and');
            TurkishSearch::applyTextMatch($uq, 'soyad', $term, 'contains', 'or');
            $uq->orWhere('tc_kimlik', 'LIKE', '%' . $term . '%');

            $adCol = $uq->qualifyColumn('ad');
            $soyadCol = $uq->qualifyColumn('soyad');
            $uq->orWhereRaw(
                "CONCAT({$adCol}, ' ', {$soyadCol}) COLLATE utf8mb4_turkish_ci LIKE ?",
                ['%' . $normalized . '%']
            );
        });
    }

    private function applyKitapFilterOnRelation($katalogQuery, string $term): void
    {
        $katalogQuery->where(function ($kq) use ($term) {
            TurkishSearch::applyTextMatch($kq, 'kunyeEserAdi', $term, 'contains', 'and');
            $kq->orWhere('kunyeISBNISSN', 'LIKE', '%' . $term . '%');
        });
    }

    private function applyKitapAraFilterOnRelation($katalogQuery, string $term): void
    {
        $katalogQuery->where(function ($kq) use ($term) {
            TurkishSearch::applyTextMatch($kq, 'kunyeEserAdi', $term, 'contains', 'and');
            TurkishSearch::applyTextMatch($kq, 'kunyeYazar', $term, 'contains', 'or');
            $kq->orWhere('kunyeISBNISSN', 'LIKE', '%' . $term . '%')
                ->orWhere('kunyeDemirbasKN', 'LIKE', '%' . $term . '%');
        });
    }

    private function applyUyeAraFilterOnRelation($uyeQuery, string $term): void
    {
        $uyeQuery->where(function ($q) use ($term) {
            $this->applyUyeFilterOnRelation($q, $term);
            $q->orWhere('telefon', 'LIKE', '%' . $term . '%');
        });
    }

    private function applyLegacyOduncSearchFilter($query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->whereHas('uye', function ($u) use ($search) {
                $this->applyUyeFilterOnRelation($u, $search);
            })->orWhereHas('katalog', function ($k) use ($search) {
                $this->applyKitapFilterOnRelation($k, $search);
            });
        });
    }

    private function applyOduncTextFilters($query, Request $request): void
    {
        $filters = $this->oduncFilterValues($request);
        $hasNewFilters = $filters['filter_uye'] !== '' || $filters['filter_kitap'] !== '';

        if ($hasNewFilters) {
            if ($filters['filter_uye'] !== '') {
                $term = $filters['filter_uye'];
                $query->whereHas('uye', function ($u) use ($term) {
                    $this->applyUyeFilterOnRelation($u, $term);
                });
            }
            if ($filters['filter_kitap'] !== '') {
                $term = $filters['filter_kitap'];
                $query->whereHas('katalog', function ($k) use ($term) {
                    $this->applyKitapFilterOnRelation($k, $term);
                });
            }

            return;
        }

        $legacySearch = trim((string) $request->input('search', ''));
        if ($legacySearch !== '') {
            $this->applyLegacyOduncSearchFilter($query, $legacySearch);
        }
    }

    private function applyOduncStatuFilter($query, string $statu): void
    {
        if ($statu === 'aktif') {
            $query->where('statu', 'aktif');
        } elseif ($statu === 'gecikti') {
            $query->where('statu', 'aktif')
                ->where('iade_tarihi_planlanan', '<', now()->toDateString());
        } elseif ($statu === 'iade_edildi') {
            $query->where('statu', 'iade_edildi');
        } elseif ($statu === 'kayip') {
            $query->where('statu', 'kayip');
        }
    }

    // ─── İstatistik yardımcısı ──────────────────────────────────────────────────
    private function stats(): array
    {
        return [
            'aktif'      => OduncIslem::aktif()->count(),
            'gecikti'    => OduncIslem::gecikti()->count(),
            'bugun_iade' => OduncIslem::bugünIade()->count(),
            'toplam'     => OduncIslem::count(),
        ];
    }

    // ─── Liste Sayfası (sadece view + stats, veri AJAX ile yükleniyor) ──────────
    public function index(Request $request)
    {
        abort_unless($this->canViewAllLoans() || $this->canViewScopedLoans(), 403);
        $kutuphaneler = Kutuphane::query()
            ->whereNull('deleted_at')
            ->orderBy('title')
            ->get(['id', 'title']);

        if (!$this->canViewAllLoans()) {
            $ids = auth()->user()->yetkiliKutuphaneIds();
            $kutuphaneler = $kutuphaneler->whereIn('id', $ids ?: [-1])->values();
        }

        return view('odunc.list', [
            'stats'       => $this->stats(),
            'statu'       => $request->input('statu', 'aktif'),
            'kutuphaneler'=> $kutuphaneler,
        ]);
    }

    // ─── AJAX Tablo Verisi ───────────────────────────────────────────────────────
    // GET /odunc/tablo?filter_uye=&filter_kitap=&demirbasNo=&statu=&tarih_baslangic=&tarih_bitis=&per_page=20&page=1
    public function tableData(Request $request)
    {
        abort_unless($this->canViewAllLoans() || $this->canViewScopedLoans(), 403);
        $perPage = in_array((int) $request->input('per_page'), [10, 20, 50, 100, 500])
            ? (int) $request->input('per_page')
            : 20;

        $statu      = $request->input('statu', 'aktif');
        $demirbasNo = trim($request->input('demirbasNo', ''));
        $kutuphaneId= (int) $request->input('kutuphaneId', 0);

        $query = OduncIslem::with(['uye', 'katalog', 'kutuphane', 'oduncVeren']);

        if (!$this->canViewAllLoans()) {
            $ids = auth()->user()->yetkiliKutuphaneIds();
            $query->where(function ($q) use ($ids) {
                $q->whereIn('kutuphane_id', $ids ?: [-1])
                    ->orWhere(function ($q2) use ($ids) {
                        $q2->whereNull('kutuphane_id')
                            ->whereHas('katalog', function ($k) use ($ids) {
                                $k->whereIn('kutuphaneId', $ids ?: [-1]);
                            });
                    });
            });
        }

        $this->applyOduncStatuFilter($query, $statu);
        $this->applyOduncTextFilters($query, $request);

        // ── Demirbaş No filtresi ──────────────────────────────────────────────
        if ($demirbasNo !== '') {
            $query->whereHas('katalog', function ($k) use ($demirbasNo) {
                $k->where('kunyeDemirbasKN', 'LIKE', "%{$demirbasNo}%");
            });
        }

        // ── Kütüphane filtresi ────────────────────────────────────────────────
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

        // ── Tarih aralığı ─────────────────────────────────────────────────────
        if ($request->filled('tarih_baslangic')) {
            $query->where('odunc_tarihi', '>=', $request->input('tarih_baslangic'));
        }
        if ($request->filled('tarih_bitis')) {
            $query->where('odunc_tarihi', '<=', $request->input('tarih_bitis'));
        }

        $islemler = $query->orderByRaw("
            CASE statu
                WHEN 'aktif' THEN 0
                ELSE 1
            END,
            iade_tarihi_planlanan ASC
        ")->paginate($perPage);

        $todayStr = now()->toDateString();

        $items = collect($islemler->items())->map(function ($i) use ($todayStr) {
            $gecikiyor  = $i->statu === 'aktif' && $todayStr > $i->iade_tarihi_planlanan->toDateString();
            $gecikmeGun = $gecikiyor
                ? Carbon::today()->diffInDays($i->iade_tarihi_planlanan)
                : 0;
            $kalanGun   = (!$gecikiyor && $i->statu === 'aktif')
                ? Carbon::today()->diffInDays($i->iade_tarihi_planlanan, false)
                : null;

            return [
                'id'             => $i->id,
                'uye_ad'         => $i->uye->ad . ' ' . $i->uye->soyad,
                'uye_initials'   => mb_strtoupper(
                    mb_substr($i->uye->ad, 0, 1, 'UTF-8') .
                    mb_substr($i->uye->soyad, 0, 1, 'UTF-8'),
                    'UTF-8'
                ),
                'uye_tc'         => $i->uye->tc_kimlik,
                'kitap'          => $i->katalog->kunyeEserAdi,
                'kitap_isbn'     => $i->katalog->kunyeISBNISSN,
                'kitap_demir'    => $i->katalog->kunyeDemirbasKN,
                'kitap_kapak'    => $i->katalog->kapak_resim_path,
                'odunc_tarihi'   => $i->odunc_tarihi->format('d.m.Y'),
                'odunc_veren'    => $i->oduncVeren?->name,
                'iade_planlanan' => $i->iade_tarihi_planlanan->format('d.m.Y'),
                'iade_gercek'    => $i->iade_tarihi_gercek?->format('d.m.Y'),
                'statu'          => $i->statu,
                'gecikiyor'      => $gecikiyor,
                'gecikme_gun'    => $gecikmeGun,
                'kalan_gun'      => $kalanGun,
                'kutuphane'      => $i->kutuphane?->title,
                'detay_url'      => route('odunc.show', $i->id),
            ];
        })->values()->all();

        return response()->json([
            'success' => true,
            'data'    => $items,
            'meta'    => [
                'current_page' => $islemler->currentPage(),
                'last_page'    => $islemler->lastPage(),
                'per_page'     => $islemler->perPage(),
                'total'        => $islemler->total(),
                'from'         => $islemler->firstItem() ?? 0,
                'to'           => $islemler->lastItem()  ?? 0,
            ],
        ]);
    }

    // ─── CSV / Excel Export ──────────────────────────────────────────────────────
    // GET /odunc/export?filter_uye=&filter_kitap=&demirbasNo=&statu=&tarih_baslangic=&tarih_bitis=
    public function export(Request $request)
    {
        abort_unless($this->canViewAllLoans() || $this->canViewScopedLoans(), 403);
        $statu      = $request->input('statu', 'aktif');
        $demirbasNo = trim($request->input('demirbasNo', ''));
        $kutuphaneId= (int) $request->input('kutuphaneId', 0);

        $query = OduncIslem::with(['uye', 'katalog', 'kutuphane', 'oduncVeren']);

        if (!$this->canViewAllLoans()) {
            $ids = auth()->user()->yetkiliKutuphaneIds();
            $query->where(function ($q) use ($ids) {
                $q->whereIn('kutuphane_id', $ids ?: [-1])
                    ->orWhere(function ($q2) use ($ids) {
                        $q2->whereNull('kutuphane_id')
                            ->whereHas('katalog', function ($k) use ($ids) {
                                $k->whereIn('kutuphaneId', $ids ?: [-1]);
                            });
                    });
            });
        }

        $this->applyOduncStatuFilter($query, $statu);
        $this->applyOduncTextFilters($query, $request);

        if ($demirbasNo !== '') {
            $query->whereHas('katalog', function ($k) use ($demirbasNo) {
                $k->where('kunyeDemirbasKN', 'LIKE', "%{$demirbasNo}%");
            });
        }

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

        if ($request->filled('tarih_baslangic')) {
            $query->where('odunc_tarihi', '>=', $request->input('tarih_baslangic'));
        }
        if ($request->filled('tarih_bitis')) {
            $query->where('odunc_tarihi', '<=', $request->input('tarih_bitis'));
        }

        $islemler = $query->orderByRaw("
            CASE statu WHEN 'aktif' THEN 0 ELSE 1 END,
            iade_tarihi_planlanan ASC
        ")->get();

        $filename = 'odunc_' . now()->format('Ymd_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($islemler) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM — Excel Türkçe desteği
            fputcsv($out, [
                '#', 'Üye Adı Soyadı', 'TC Kimlik No',
                'Kitap', 'ISBN / ISSN', 'Demirbaş No',
                'Ödünç Tarihi', 'Planlanan İade', 'Gerçek İade',
                'Durum', 'Gecikme (gün)', 'Kütüphane', 'Ödünç Veren',
            ], ';');

            $today = now()->toDateString();

            foreach ($islemler as $i) {
                $gecikiyor  = $i->statu === 'aktif' && $today > $i->iade_tarihi_planlanan->toDateString();
                $gecikmeGun = $gecikiyor ? Carbon::today()->diffInDays($i->iade_tarihi_planlanan) : 0;

                if ($i->statu === 'iade_edildi')    $statuLabel = 'İade Edildi';
                elseif ($i->statu === 'kayip')       $statuLabel = 'Kayıp';
                elseif ($gecikiyor)                  $statuLabel = 'Gecikmiş';
                else                                 $statuLabel = 'Aktif';

                fputcsv($out, [
                    $i->id,
                    $i->uye->ad . ' ' . $i->uye->soyad,
                    $i->uye->tc_kimlik,
                    $i->katalog->kunyeEserAdi,
                    $i->katalog->kunyeISBNISSN  ?? '—',
                    $i->katalog->kunyeDemirbasKN ?? '—',
                    $i->odunc_tarihi->format('d.m.Y'),
                    $i->iade_tarihi_planlanan->format('d.m.Y'),
                    $i->iade_tarihi_gercek?->format('d.m.Y') ?? '—',
                    $statuLabel,
                    $gecikmeGun ?: '—',
                    $i->kutuphane?->title ?? '—',
                    $i->oduncVeren?->name ?? '—',
                ], ';');
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ─── Yeni Ödünç Formu ───────────────────────────────────────────────────────
    public function new(Request $request)
    {
        abort_unless($this->canDoAllLoans() || $this->canDoScopedLoans(), 403);
        $kutuphaneler = Kutuphane::whereNull('deleted_at')
            ->where('statu', 'aktif')
            ->orderBy('title')
            ->get();

        // Katalog listesinden "Ödünç Ver" ile gelindiyse kitabı önceden yükle
        $preKitap = null;
        if ($request->filled('katalog_id')) {
            $k = Katalog::find((int) $request->input('katalog_id'));
            if ($k) {
                $preUyeIdForKitap = $request->filled('uye_id') ? (int) $request->input('uye_id') : null;
                $preKitap         = $this->kitapAraSatir($k, $preUyeIdForKitap);
            }
        }

        // Üye listesinden "Ödünç Ver" ile gelindiyse üyeyi önceden yükle
        $preUye = null;
        if ($request->filled('uye_id')) {
            $u = Uye::find((int) $request->input('uye_id'));
            if ($u) {
                $preUye = [
                    'id'          => $u->id,
                    'label'       => $u->ad . ' ' . $u->soyad,
                    'tc'          => $u->tc_kimlik,
                    'telefon'     => $u->telefon ?? '',
                    'notlar'      => $u->notlar ?? '',
                    'aktif_odunc' => OduncIslem::where('uye_id', $u->id)
                        ->where('statu', 'aktif')->count(),
                ];
            }
        }

        return view('odunc.new', compact('kutuphaneler', 'preKitap', 'preUye'));
    }

    // ─── Üye Arama (AJAX) ───────────────────────────────────────────────────────
    public function uyeAra(Request $request)
    {
        $term = trim($request->input('q', ''));
        if (strlen($term) < 2) return response()->json([]);

        $uyeler = Uye::where('statu', 'aktif')
            ->where(function ($q) use ($term) {
                $this->applyUyeAraFilterOnRelation($q, $term);
            })
            ->select('id', 'ad', 'soyad', 'tc_kimlik', 'telefon', 'notlar', 'statu')
            ->limit(8)->get()
            ->map(fn($u) => [
                'id'          => $u->id,
                'label'       => $u->ad . ' ' . $u->soyad,
                'tc'          => $u->tc_kimlik,
                'telefon'     => $u->telefon,
                'notlar'      => $u->notlar ?? '',
                'aktif_odunc' => OduncIslem::where('uye_id', $u->id)->where('statu', 'aktif')->count(),
            ]);

        return response()->json($uyeler);
    }

    /**
     * Kitap arama / ön seçim için JSON satırı (isteğe bağlı üye: Rezerve + aktif rezervasyon eşleşmesi).
     */
    private function kitapAraSatir(Katalog $k, ?int $uyeId = null): array
    {
        $oduncTa = OduncIslem::where('katalog_id', $k->id)->where('statu', 'aktif')->exists();
        $rezerveAktifBuUye   = false;
        $rezerveEdenUyeAdi   = null;
        if ($k->kunyeDurum === 'Rezerve') {
            $rezKayit = UyeRezerve::query()
                ->where('katalog_id', $k->id)
                ->where('iptalMi', 'false')
                ->where('rezerve_bitis', '>', now())
                ->where('oduncAldiMi', 'false')
                ->with('uye:id,ad,soyad')
                ->first();
            if ($rezKayit && $rezKayit->uye) {
                $rezerveEdenUyeAdi = trim($rezKayit->uye->ad . ' ' . $rezKayit->uye->soyad);
            }
            if ($rezKayit && $uyeId !== null && $uyeId > 0) {
                $rezerveAktifBuUye = (int) $rezKayit->uye_id === $uyeId;
            }
        }
        $verilebilir = !$k->oduncVerilemez && !$oduncTa && (
            $k->kunyeDurum === 'Rafta'
            || ($k->kunyeDurum === 'Rezerve' && $rezerveAktifBuUye)
        );

        return [
            'id'                     => $k->id,
            'label'                  => $k->kunyeEserAdi,
            'yazar'                  => $k->kunyeYazar,
            'isbn'                   => $k->kunyeISBNISSN,
            'demir'                  => $k->kunyeDemirbasKN,
            'kapak'                  => $k->kapak_resim_path,
            'odunc_ta'               => $oduncTa,
            'kunyeDurum'             => $k->kunyeDurum,
            'oduncVerilemez'         => $k->oduncVerilemez,
            'rezerve_aktif_bu_uye'   => $rezerveAktifBuUye,
            'rezerve_eden_uye_adi'   => $rezerveEdenUyeAdi,
            'verilebilir'            => $verilebilir,
        ];
    }

    // ─── Kitap Arama (AJAX) ─────────────────────────────────────────────────────
    public function kitapAra(Request $request)
    {
        $uyeId = $request->filled('uye_id') ? (int) $request->input('uye_id') : null;

        if ($request->filled('katalog_id')) {
            $k = Katalog::find((int) $request->input('katalog_id'));
            if (!$k) {
                return response()->json([]);
            }
            if (!$this->canDoAllLoans()) {
                $ids = auth()->user()->yetkiliKutuphaneIds();
                if ($k->kutuphaneId && !in_array((int) $k->kutuphaneId, $ids ?: [-1], true)) {
                    return response()->json([]);
                }
            }

            return response()->json([$this->kitapAraSatir($k, $uyeId)]);
        }

        $term = trim($request->input('q', ''));
        if (strlen($term) < 2) {
            return response()->json([]);
        }

        if ($this->canDoAllLoans()) {
            $kitaplar = Katalog::where(function ($q) use ($term) {
                $this->applyKitapAraFilterOnRelation($q, $term);
            })
                ->select('id', 'kunyeEserAdi', 'kunyeYazar', 'kunyeISBNISSN', 'kunyeDemirbasKN', 'kunyeKapakResmi', 'kunyeDurum', 'oduncVerilemez')
                ->limit(8)->get()
                ->map(fn (Katalog $k) => $this->kitapAraSatir($k, $uyeId));
        } elseif ($this->canDoScopedLoans()) {
            $ids = auth()->user()->yetkiliKutuphaneIds();
            $kitaplar = Katalog::whereIn('kutuphaneId', $ids ?: [-1])
                ->where(function ($q) use ($term) {
                    $this->applyKitapAraFilterOnRelation($q, $term);
                })
                ->select('id', 'kunyeEserAdi', 'kunyeYazar', 'kunyeISBNISSN', 'kunyeDemirbasKN', 'kunyeKapakResmi', 'kunyeDurum', 'oduncVerilemez')
                ->limit(8)->get()
                ->map(fn (Katalog $k) => $this->kitapAraSatir($k, $uyeId));
        } else {
            $kitaplar = collect();
        }

        return response()->json($kitaplar);
    }

    // ─── Ödünç Ver ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {

        abort_unless($this->canDoAllLoans() || $this->canDoScopedLoans(), 403);
        $oduncTarihi = $request->input('odunc_tarihi');
        $maxIade     = $oduncTarihi ? \Carbon\Carbon::parse($oduncTarihi)->addDays(30)->format('Y-m-d') : now()->addDays(30)->format('Y-m-d');

        $request->validate([
            'uye_id'                => ['required', 'exists:uyeler,id'],
            'katalog_id'            => ['required', 'exists:katalog,id'],
            'kutuphane_id'          => ['nullable', 'exists:kutuphaneler,id'],
            'odunc_tarihi'          => ['required', 'date', 'before_or_equal:today', 'after_or_equal:' . now()->subDays(7)->format('Y-m-d')],
            'iade_tarihi_planlanan' => ['required', 'date', 'after_or_equal:odunc_tarihi', 'before_or_equal:' . $maxIade],
            'notlar'                => ['nullable', 'string', 'max:1000'],
        ], [
            'odunc_tarihi.before_or_equal'  => 'Ödünç tarihi ileri tarih olamaz.',
            'odunc_tarihi.after_or_equal'   => 'Ödünç tarihi en fazla 1 hafta geriye alınabilir.',
            'iade_tarihi_planlanan.after_or_equal'  => 'Planlanan iade tarihi, ödünç tarihinden önce olamaz.',
            'iade_tarihi_planlanan.before_or_equal' => 'Planlanan iade tarihi, ödünç tarihinden en fazla 30 gün ileri olabilir.',
        ]);

        $aktif = OduncIslem::where('katalog_id', $request->input('katalog_id'))
            ->where('statu', 'aktif')->exists();

        if ($aktif) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Bu kitap şu an başka bir üyede ödünçte.'], 422);
            }
            return back()->withInput()->withErrors(['katalog_id' => 'Bu kitap şu an başka bir üyede ödünçte.']);
        }

        // ── Kitabın durumu ve ödünç izni kontrol edilir ──────────────────────
        $kitapKontrol = Katalog::find($request->input('katalog_id'));


        if(!$this->canDoAllLoans()) {
            $ids = auth()->user()->yetkiliKutuphaneIds();
            if($kitapKontrol->kutuphaneId && !in_array((int) $kitapKontrol->kutuphaneId, $ids, true)) {
                return response()->json(['success' => false, 'message' => 'Yetkili olmadığınız kütüphaneye ait bir kitap ödünç verilemez.'], 422);
            }
        }


        if ($kitapKontrol) {
            $durum = $kitapKontrol->kunyeDurum;
            if ($durum === 'Rafta') {
                // ödünç verilebilir
            } elseif ($durum === 'Rezerve') {
                $uyeId = (int) $request->input('uye_id');
                $rezervasyonSahibi = UyeRezerve::query()
                    ->where('katalog_id', $kitapKontrol->id)
                    ->where('uye_id', $uyeId)
                    ->where('iptalMi', 'false')
                    ->where('rezerve_bitis', '>', now())
                    ->where('oduncAldiMi', 'false')
                    ->exists();
                if (!$rezervasyonSahibi) {
                    $mesaj = 'Bu kitap rezerve edilmiş. Ödünç vermek için seçili üyenin aktif rezervasyon sahibi olması gerekir.';
                    $rk    = UyeRezerve::query()
                        ->where('katalog_id', $kitapKontrol->id)
                        ->where('iptalMi', 'false')
                        ->where('rezerve_bitis', '>', now())
                        ->where('oduncAldiMi', 'false')
                        ->with('uye:id,ad,soyad')
                        ->first();
                    if ($rk && $rk->uye) {
                        $mesaj .= ' Bu kitabı rezerve eden kişi: ' . trim($rk->uye->ad . ' ' . $rk->uye->soyad) . '.';
                    }
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => $mesaj], 422);
                    }

                    return back()->withInput()->withErrors(['katalog_id' => $mesaj]);
                }
            } else {
                $mesaj = 'Bu kitabın durumu "' . ($durum ?? '?') . '" olduğu için ödünç verilemez. Yalnızca "Rafta" durumundaki kitaplar veya rezervasyon sahibi için "Rezerve" durumundaki kitaplar ödünç verilebilir.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $mesaj], 422);
                }

                return back()->withInput()->withErrors(['katalog_id' => $mesaj]);
            }
        }

        if ($kitapKontrol && $kitapKontrol->oduncVerilemez == "true") {
            $mesaj = 'Bu kitap "Ödünç Verilemez" olarak işaretlendiğinden ödünç verilemiyor.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $mesaj], 422);
            }
            return back()->withInput()->withErrors(['katalog_id' => $mesaj]);
        }

        $aktifRezerveKayit = UyeRezerve::query()
            ->where('katalog_id', $request->input('katalog_id'))
            ->where('uye_id', $request->input('uye_id'))
            ->where('iptalMi', 'false')
            ->where('rezerve_bitis', '>', now())
            ->where('oduncAldiMi', 'false')
            ->first();

        $islem = OduncIslem::create([
            'uye_id'                => $request->input('uye_id'),
            'katalog_id'            => $request->input('katalog_id'),
            'rezerve_id'            => $aktifRezerveKayit?->id,
            'kutuphane_id'          => $kitapKontrol->kutuphaneId,
            'odunc_tarihi'          => $request->input('odunc_tarihi'),
            'iade_tarihi_planlanan' => $request->input('iade_tarihi_planlanan'),
            'statu'                 => 'aktif',
            'odunc_veren_id'        => auth()->id(),
            'notlar'                => $request->input('notlar'),
        ]);

        if ($aktifRezerveKayit) {
            $aktifRezerveKayit->update([
                'oduncAldiMi' => 'true',
                'odunc_id'    => $islem->id,
            ]);
        }

        // ── Kitabın durumunu "Ödünç" olarak güncelle ─────────────────────────
        $kitapKontrol->update([
            'kunyeDurum' => 'Ödünç',
            'iade_tarihi_planlanan' => $request->input('iade_tarihi_planlanan')
        ]);

        $islem->load(['uye', 'katalog']);

        $uye       = Uye::find($request->input('uye_id'));
        $kitap     = Katalog::find($request->input('katalog_id'));
        $kutuphane = Kutuphane::find($kitap->kutuphaneId);

        MessageController::smsGonder(
            $uye->telefon,
            "Sayın " . $uye->ad . " " . $uye->soyad . ";\n\n " .
            $kitap->kunyeEserAdi . " isimli eser " .
            $kutuphane->title . " tarafından size ödünç verilmiştir. " .
            "Son iade tarihi: " . Carbon::parse($request->input('iade_tarihi_planlanan'))->format('d.m.Y')
        );

        try {
            $result = $this->webhookService->sendBildirim(
                tcList:  [$uye->tc_kimlik],
                title:   'Keyifli Okumalar 😊',
                message: $islem->katalog->kunyeEserAdi . ' isimli kitap ' . $kutuphane->title . ' tarafından sana ödünç verildi. Son iade tarihi ' . Carbon::parse($request->input('iade_tarihi_planlanan'))->format('d.m.Y') . '.',
            );

        } catch (\Exception $e) {
            // İade işlemi tamamlandı, sadece bildirim başarısız
            Log::error('Webhook gönderilemedi: ' . $e->getMessage());

        }

       
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => '"' . $islem->katalog->kunyeEserAdi . '" kitabı "' . $islem->uye->ad . ' ' . $islem->uye->soyad . '" adına ödünç verildi.',
                'id'      => $islem->id,
            ]);
        }

        return redirect()->route('odunc.index')
            ->with('success', '"' . $islem->katalog->kunyeEserAdi . '" başarıyla ödünç verildi.');
    }

    // ─── İade Formu (modal için JSON) ───────────────────────────────────────────
    public function iadeForm(OduncIslem $islem)
    {
        $islem->load(['uye', 'katalog']);
        return response()->json([
            'id'                    => $islem->id,
            'uye_ad'                => $islem->uye->ad . ' ' . $islem->uye->soyad,
            'uye_tc'                => $islem->uye->tc_kimlik,
            'kitap'                 => $islem->katalog->kunyeEserAdi,
            'kitap_isbn'            => $islem->katalog->kunyeISBNISSN,
            'odunc_tarihi'          => $islem->odunc_tarihi->format('d.m.Y'),
            'iade_tarihi_planlanan' => $islem->iade_tarihi_planlanan->format('d.m.Y'),
            'gecikiyor_mu'          => $islem->gecikiyor_mu,
            'gecikme_gun'           => $islem->gecikme_gun,
        ]);
    }

    // ─── İade Al ────────────────────────────────────────────────────────────────
    public function iade(Request $request, OduncIslem $islem)
    {

        if ($islem->statu !== 'aktif') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Bu işlem zaten tamamlanmış.'], 422);
            }
            return back()->with('error', 'Bu işlem zaten tamamlanmış.');
        }

        $request->validate([
            'iade_tarihi_gercek' => ['required', 'date', 'before_or_equal:today', 'after_or_equal:' . now()->subDays(7)->format('Y-m-d')],
            'iade_notu'          => ['nullable', 'string', 'max:1000'],
            'statu'              => ['required', 'in:iade_edildi,kayip'],
        ], [
            'iade_tarihi_gercek.before_or_equal' => 'İade tarihi ileri tarih olamaz.',
            'iade_tarihi_gercek.after_or_equal'  => 'İade tarihi en fazla 1 hafta geriye alınabilir.',
        ]);

        $islem->update([
            'iade_tarihi_gercek' => $request->input('iade_tarihi_gercek'),
            'statu'              => $request->input('statu', 'iade_edildi'),
            'iade_alan_id'       => auth()->id(),
            'iade_notu'          => $request->input('iade_notu'),
        ]);

        // ── Kitabın durumunu iade/kayıp durumuna göre güncelle ───────────────
        if ($islem->katalog_id) {
            $yeniDurum = $request->input('statu') === 'kayip' ? 'Kayıp' : 'Rafta';
            Katalog::where('id', $islem->katalog_id)->update(['kunyeDurum' => $yeniDurum]);
        }

        $islem->load(['uye', 'katalog']);

        $msg = $islem->statu === 'kayip'
            ? '"' . $islem->katalog->kunyeEserAdi . '" kayıp olarak işaretlendi.'
            : '"' . $islem->katalog->kunyeEserAdi . '" iade alındı.';

            $beklemeList = UyeBekleme::where('katalog_id', $islem->katalog_id)
            ->with('uye')
            ->get()
            ->pluck('uye.tc_kimlik')
            ->toArray();

            if (!empty($beklemeList)) {
                try {
                    $result = $this->webhookService->sendBildirim(
                        tcList:  $beklemeList,
                        title:   'Beklediğiniz kitap artık müsait!',
                        message: $islem->katalog->kunyeEserAdi . " isimli kitap artık müsait. Kaçırmamak için tıkla ve hemen rezerve et!",
                    );

                    UyeBekleme::where('katalog_id', $islem->katalog_id)
                        ->update(['bildirim' => DB::raw('COALESCE(bildirim, 0) + 1')]);
    
                } catch (\Exception $e) {
                    // İade işlemi tamamlandı, sadece bildirim başarısız
                    Log::error('Webhook gönderilemedi: ' . $e->getMessage());
    
                }
            }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->route('odunc.index')->with('success', $msg);
    }

    // ─── Süre Uzat ──────────────────────────────────────────────────────────────
    public function sureUzat(Request $request, OduncIslem $islem)
    {
        abort_unless($this->canExtendLoans(), 403, 'Ödünç süresini uzatma yetkiniz yok. Sistem yöneticinize başvurunuz.');
        if (!$this->canViewAllLoans()) {
            $ids = auth()->user()->yetkiliKutuphaneIds();
            $kutuphaneId = (int) ($islem->kutuphane_id ?? $islem->katalog?->kutuphaneId ?? 0);
            abort_unless(in_array($kutuphaneId, $ids, true), 403);
        }
        if ($islem->statu !== 'aktif') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Yalnızca aktif ödünç işlemlerinde süre uzatılabilir.'], 422);
            }
            return back()->with('error', 'Yalnızca aktif ödünç işlemlerinde süre uzatılabilir.');
        }

        $request->validate([
            'uzatma_gun' => ['required', 'integer', 'min:1', 'max:15'],
        ], [
            'uzatma_gun.required' => 'Uzatma günü girilmedi.',
            'uzatma_gun.min'      => 'En az 1 gün uzatılabilir.',
            'uzatma_gun.max'      => 'En fazla 15 gün uzatılabilir.',
        ]);

        $gun       = (int) $request->input('uzatma_gun');
        $yeniTarih = Carbon::parse($islem->iade_tarihi_planlanan)->addDays($gun);

        $islem->update([
            'iade_tarihi_planlanan' => $yeniTarih->format('Y-m-d'),
            'sure_uzatimi'          => $gun,
            'sure_uzatan_id'        => auth()->id(),
            'sure_uzatma_tarihi'    => Carbon::today()->format('Y-m-d'),
        ]);

        $mesaj = '"' . $islem->katalog->kunyeEserAdi . '" kitabının iade tarihi '
            . $gun . ' gün uzatıldı. Yeni tarih: ' . $yeniTarih->format('d.m.Y');

        $uye       = Uye::find($islem->uye_id);

        MessageController::smsGonder($uye->telefon,"Sayın " . $uye->ad . " " . $uye->soyad .
            "; ödünç aldığınız " . $islem->katalog->kunyeEserAdi . " isimli eserin iade tarihi " . $gun . " gün uzatıldı. Yeni iade tarihiniz " . $yeniTarih->format('d.m.Y'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'        => true,
                'message'        => $mesaj,
                'yeni_tarih'     => $yeniTarih->format('d.m.Y'),
                'yeni_tarih_iso' => $yeniTarih->format('Y-m-d'),
            ]);
        }

        return redirect()->back()->with('success', $mesaj);
    }

    // ─── Detay ──────────────────────────────────────────────────────────────────
    public function show(OduncIslem $islem)
    {
        $islem->load(['uye', 'katalog', 'kutuphane', 'oduncVeren', 'iadeAlan', 'sureUzatan']);
        return view('odunc.show', compact('islem'));
    }
}
