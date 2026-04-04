<?php

namespace App\Http\Controllers;

use App\Models\Kutuphane;
use App\Models\User;
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

        if ($request->filled('search')) {
            $query->where('title', 'LIKE', '%' . $request->input('search') . '%');
        }

        $kutuphaneler = $query->orderBy('id')->paginate(15)->withQueryString();

        return view('kutuphane.list', compact('kutuphaneler'));
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
