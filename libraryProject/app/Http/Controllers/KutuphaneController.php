<?php

namespace App\Http\Controllers;

use App\Models\Kutuphane;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KutuphaneController extends Controller
{
    // ─── Liste ──────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->hasYetki(17) || auth()->user()?->hasYetki(19), 403);
        $query = Kutuphane::whereNull('deleted_at');
        $this->applyKutuphaneListFilters($query, $request);
        $this->applyKutuphaneListSort($query, $request);
        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $kutuphaneler = $query->paginate($perPage)->withQueryString();

        $activeStatu = in_array((string) $request->input('statu'), ['aktif', 'pasif'], true)
            ? (string) $request->input('statu')
            : '';

        $activeSortBy = '';
        $activeSortDir = 'asc';
        if ((string) $request->input('sort_by') === 'title') {
            $activeSortBy = 'title';
            $activeSortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        }

        if ($request->ajax()) {
            $u = auth()->user();
            $canEdit = $u && $u->hasYetki(19);

            return response()->json([
                'rows' => collect($kutuphaneler->items())->map(function (Kutuphane $k) {
                    return [
                        'id' => (int) $k->id,
                        'title' => (string) $k->title,
                        'address' => $k->address !== null && $k->address !== '' ? (string) $k->address : null,
                        'phone' => $k->phone !== null && $k->phone !== '' ? (string) $k->phone : null,
                        'email' => $k->email !== null && $k->email !== '' ? (string) $k->email : null,
                        'statu' => (string) ($k->statu ?? ''),
                        'created_at' => $k->created_at ? $k->created_at->format('d.m.Y') : '—',
                    ];
                })->values()->all(),
                'meta' => [
                    'total' => $kutuphaneler->total(),
                    'from' => $kutuphaneler->firstItem(),
                    'to' => $kutuphaneler->lastItem(),
                    'current_page' => $kutuphaneler->currentPage(),
                    'last_page' => $kutuphaneler->lastPage(),
                    'per_page' => $kutuphaneler->perPage(),
                    'can_edit' => $canEdit,
                    'sort_by' => $activeSortBy !== '' ? $activeSortBy : null,
                    'sort_dir' => $activeSortBy !== '' ? $activeSortDir : null,
                    'statu' => $activeStatu !== '' ? $activeStatu : null,
                ],
            ]);
        }

        return view('kutuphane.list', compact(
            'kutuphaneler',
            'activeStatu',
            'activeSortBy',
            'activeSortDir',
            'perPage'
        ));
    }

    public function export(Request $request)
    {
        abort_unless(auth()->user()?->hasYetki(17) || auth()->user()?->hasYetki(19), 403);
        $query = Kutuphane::whereNull('deleted_at');
        $this->applyKutuphaneListFilters($query, $request);
        $this->applyKutuphaneListSort($query, $request);
        $rows = $query->get(['title', 'address', 'phone', 'email', 'statu', 'created_at']);

        $filename = 'kutuphaneler_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['Kütüphane Adı', 'Adres', 'Telefon', 'E-posta', 'Durum', 'Kayıt Tarihi'], ';');
            foreach ($rows as $k) {
                fputcsv($out, [
                    (string) $k->title,
                    (string) ($k->address ?? '—'),
                    (string) ($k->phone ?? '—'),
                    (string) ($k->email ?? '—'),
                    (string) ($k->statu === 'aktif' ? 'Aktif' : 'Pasif'),
                    $k->created_at ? $k->created_at->format('d.m.Y') : '—',
                ], ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function applyKutuphaneListFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $query->where('title', 'LIKE', '%' . $request->input('search') . '%');
        }

        $statu = (string) $request->input('statu', '');
        if ($statu === 'aktif' || $statu === 'pasif') {
            $query->where('statu', $statu);
        }
    }

    private function applyKutuphaneListSort(Builder $query, Request $request): void
    {
        $sortBy = (string) $request->input('sort_by', '');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortBy === 'title') {
            $query->orderBy('title', $sortDir)->orderBy('id');

            return;
        }

        $query->orderBy('id');
    }

    // ─── Yeni Form ──────────────────────────────────────────────────────────────
    public function new()
    {
        abort_unless(auth()->user()?->hasYetki(18), 403);
        return view('kutuphane.new');
    }

    // ─── Kaydet ─────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        abort_unless(auth()->user()?->hasYetki(18), 403);
        $request->validate([
            'title'  => 'required|string|max:250',
            'email'  => 'nullable|email|max:250',
            'phone'  => 'nullable|string|max:30',
            'address'=> 'nullable|string|max:500',
            'statu'  => 'required|in:aktif,pasif',
        ]);

        $kutuphane = Kutuphane::create([
            'title'   => $request->input('title'),
            'address' => $request->input('address'),
            'phone'   => $request->input('phone'),
            'email'   => $request->input('email'),
            'statu'   => $request->input('statu', 'aktif'),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => '"' . $kutuphane->title . '" başarıyla eklendi.',
                'id'      => $kutuphane->id,
            ]);
        }

        return redirect()->route('kutuphane.index')
            ->with('success', '"' . $kutuphane->title . '" başarıyla eklendi.');
    }

    // ─── Düzenle Form ───────────────────────────────────────────────────────────
    public function edit(Kutuphane $kutuphane)
    {
        abort_unless(auth()->user()?->hasYetki(19), 403);
        return view('kutuphane.edit', compact('kutuphane'));
    }

    // ─── Güncelle ───────────────────────────────────────────────────────────────
    public function update(Request $request, Kutuphane $kutuphane)
    {
        abort_unless(auth()->user()?->hasYetki(19), 403);
        $request->validate([
            'title'   => 'required|string|max:250',
            'email'   => 'nullable|email|max:250',
            'phone'   => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'statu'   => 'required|in:aktif,pasif',
        ]);

        $kutuphane->update([
            'title'   => $request->input('title'),
            'address' => $request->input('address'),
            'phone'   => $request->input('phone'),
            'email'   => $request->input('email'),
            'statu'   => $request->input('statu'),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => '"' . $kutuphane->title . '" başarıyla güncellendi.',
            ]);
        }

        return redirect()->route('kutuphane.index')
            ->with('success', '"' . $kutuphane->title . '" başarıyla güncellendi.');
    }

    // ════════════════════════════════════════════════════════════════════════════
    // ─── Yetkilendirme ──────────────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════════

    /**
     * GET /kutuphane/{kutuphane}/yetkili
     * Kütüphaneye ait aktif yetkili kullanıcıları listele.
     */
    public function getYetkililer(Kutuphane $kutuphane)
    {
        $yetkililer = DB::table('kutuphane_yetkili as ky')
            ->join('users as u',  'u.id',  '=', 'ky.user_id')
            ->join('users as cb', 'cb.id', '=', 'ky.created_by')
            ->whereNull('ky.deleted_at')
            ->where('ky.kutuphane_id', $kutuphane->id)
            ->select(
                'ky.id',
                'u.id   as user_id',
                'u.name',
                'u.email',
                'u.role',
                'ky.created_at',
                'cb.name as created_by_name'
            )
            ->orderBy('ky.created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $yetkililer,
        ]);
    }

    /**
     * POST /kutuphane/{kutuphane}/yetkili
     * Kullanıcıya kütüphane yetkisi ekle.
     */
    public function addYetkili(Request $request, Kutuphane $kutuphane)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $userId     = (int) $request->input('user_id');
        $authUserId = Auth::id();

        // Aynı kullanıcı zaten aktif yetkili mi?
        $exists = DB::table('kutuphane_yetkili')
            ->where('kutuphane_id', $kutuphane->id)
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Bu kullanıcı zaten bu kütüphaneye yetkili.',
            ], 422);
        }

        // Yeni yetkili kaydı oluştur
        $yetkiliId = DB::table('kutuphane_yetkili')->insertGetId([
            'kutuphane_id' => $kutuphane->id,
            'user_id'      => $userId,
            'created_by'   => $authUserId,
            'created_at'   => now(),
            'deleted_at'   => null,
            'deleted_by'   => null,
        ]);

        // Eklenen kaydı dön (blade'in JS'i kullanıcı kartını anında render eder)
        $yetkili = DB::table('kutuphane_yetkili as ky')
            ->join('users as u',  'u.id',  '=', 'ky.user_id')
            ->join('users as cb', 'cb.id', '=', 'ky.created_by')
            ->where('ky.id', $yetkiliId)
            ->select(
                'ky.id',
                'u.id   as user_id',
                'u.name',
                'u.email',
                'u.role',
                'ky.created_at',
                'cb.name as created_by_name'
            )
            ->first();

        $user = User::find($userId);

        return response()->json([
            'success' => true,
            'message' => '"' . $user->name . '" yetkili olarak eklendi.',
            'data'    => $yetkili,
        ]);
    }

    /**
     * DELETE /kutuphane/{kutuphane}/yetkili/{yetkiliId}
     * Kütüphane yetkisini kaldır (soft-delete).
     */
    public function removeYetkili(Kutuphane $kutuphane, int $yetkiliId)
    {
        $row = DB::table('kutuphane_yetkili')
            ->where('id', $yetkiliId)
            ->where('kutuphane_id', $kutuphane->id)
            ->whereNull('deleted_at')
            ->first();

        if (! $row) {
            return response()->json([
                'success' => false,
                'message' => 'Kayıt bulunamadı veya zaten kaldırılmış.',
            ], 404);
        }

        DB::table('kutuphane_yetkili')
            ->where('id', $yetkiliId)
            ->update([
                'deleted_at' => now(),
                'deleted_by' => Auth::id(),
            ]);

        $user = User::find($row->user_id);

        return response()->json([
            'success' => true,
            'message' => ($user ? '"' . $user->name . '"' : 'Kullanıcı') . ' yetkisi kaldırıldı.',
        ]);
    }

    /**
     * GET /kutuphane/{kutuphane}/yetkili/search?q=...
     * Eklenebilecek kullanıcıları ara (zaten yetkili olanları hariç tut).
     */
    public function searchUsers(Request $request, Kutuphane $kutuphane)
    {
        $q = trim($request->input('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['success' => true, 'data' => []]);
        }

        // Zaten aktif yetkili olan user_id'leri al
        $mevcutIds = DB::table('kutuphane_yetkili')
            ->where('kutuphane_id', $kutuphane->id)
            ->whereNull('deleted_at')
            ->pluck('user_id');

        $users = User::where(function ($query) use ($q) {
            $query->where('name',  'LIKE', '%' . $q . '%')
                ->orWhere('email', 'LIKE', '%' . $q . '%');
        })
            ->whereNotIn('id', $mevcutIds)
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'email', 'role']);

        return response()->json([
            'success' => true,
            'data'    => $users,
        ]);
    }
}
