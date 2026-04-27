<?php

namespace App\Http\Controllers;

use App\Models\Kutuphane;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    // ─── Liste Sayfası ──────────────────────────────────────────────────────────
    public function index()
    {
        abort_unless(Auth::user()?->hasYetki(14) || Auth::user()?->hasYetki(16), 403);
        $kutuphaneler = Kutuphane::whereNull('deleted_at')
            ->orderBy('title')
            ->get(['id', 'title', 'statu']);

        return view('users.list', compact('kutuphaneler'));
    }

    // ─── AJAX Tablo Verisi ───────────────────────────────────────────────────────
    // GET /kullanicilar/tablo?search=&role=&kutuphane_id=&per_page=20&page=1
    public function tableData(Request $request)
    {
        abort_unless(Auth::user()?->hasYetki(14) || Auth::user()?->hasYetki(16), 403);
        $perPage     = in_array((int) $request->input('per_page'), [10, 20, 50, 100, 500])
            ? (int) $request->input('per_page')
            : 10;
        $search      = trim($request->input('search', ''));
        $role        = $request->input('role', '');
        $kutuphaneId = (int) $request->input('kutuphane_id', 0);

        $query = User::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name',  'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if ($role !== null) {
            $query->where('role', $role);
        }

        if ($kutuphaneId > 0) {
            $query->whereExists(function ($sub) use ($kutuphaneId) {
                $sub->select(DB::raw(1))
                    ->from('kutuphane_yetkili')
                    ->whereColumn('kutuphane_yetkili.user_id', 'users.id')
                    ->where('kutuphane_yetkili.kutuphane_id', $kutuphaneId)
                    ->whereNull('kutuphane_yetkili.deleted_at');
            });
        }

        $users = $query->orderBy('id')->paginate($perPage);

        $userIds    = $users->pluck('id');
        $yetkiliMap = DB::table('kutuphane_yetkili as ky')
            ->join('kutuphane as k', 'k.id', '=', 'ky.kutuphane_id')
            ->whereIn('ky.user_id', $userIds)
            ->whereNull('ky.deleted_at')
            ->whereNull('k.deleted_at')
            ->select('ky.user_id', 'k.id as kutuphane_id', 'k.title')
            ->orderBy('k.title')
            ->get()
            ->groupBy('user_id');

        // collect($users->items()) ile plain array alıyoruz — $users->map() bazı
        // Laravel versiyonlarında paginatörün kendisini döndürerek JSON'da
        // {current_page, data, ...} objesine dönüşür; bu JS tarafında kırılır.
        $items = collect($users->items())
            ->map(function ($user) use ($yetkiliMap) {
                return [
                    'id'            => $user->id,
                    'name'          => $user->name,
                    'email'         => $user->email,
                    'role'          => $user->role,
                    'role_label'    => $user->getRoleLabel(),
                    'created_at'    => $user->created_at
                        ? $user->created_at->format('d.m.Y')
                        : '—',
                    'last_login_at' => $user->last_login_at
                        ? \Carbon\Carbon::parse($user->last_login_at)->format('d.m.Y H:i')
                        : '—',
                    'is_self'       => $user->id === Auth::id(),
                    'edit_url'      => route('users.edit', $user->id),
                    'delete_url'    => '/kullanicilar/' . $user->id,
                    'kutuphaneler'  => $yetkiliMap->get($user->id, collect())
                        ->values()
                        ->map(fn($k) => ['id' => $k->kutuphane_id, 'title' => $k->title])
                        ->all(),
                ];
            })
            ->values() // 0-indexed sequential keys → kesinlikle JSON array
            ->all();   // plain PHP array → json_encode her zaman [...] üretir

        return response()->json([
            'success' => true,
            'data'    => $items,
            'meta'    => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
                'from'         => $users->firstItem() ?? 0,
                'to'           => $users->lastItem() ?? 0,
            ],
        ]);
    }

    // ─── CSV / Excel İndir ───────────────────────────────────────────────────────
    // GET /kullanicilar/export?search=&role=&kutuphane_id=
    public function export(Request $request)
    {
        abort_unless(Auth::user()?->hasYetki(14), 403);
        $search      = trim($request->input('search', ''));
        $role        = $request->input('role', '');
        $kutuphaneId = (int) $request->input('kutuphane_id', 0);

        $query = User::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name',  'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }
        if ($role !== null) {
            $query->where('role', $role);
        }
        if ($kutuphaneId > 0) {
            $query->whereExists(function ($sub) use ($kutuphaneId) {
                $sub->select(DB::raw(1))
                    ->from('kutuphane_yetkili')
                    ->whereColumn('kutuphane_yetkili.user_id', 'users.id')
                    ->where('kutuphane_yetkili.kutuphane_id', $kutuphaneId)
                    ->whereNull('kutuphane_yetkili.deleted_at');
            });
        }

        $users      = $query->orderBy('id')->get();
        $userIds    = $users->pluck('id');
        $yetkiliMap = DB::table('kutuphane_yetkili as ky')
            ->join('kutuphane as k', 'k.id', '=', 'ky.kutuphane_id')
            ->whereIn('ky.user_id', $userIds)
            ->whereNull('ky.deleted_at')
            ->whereNull('k.deleted_at')
            ->select('ky.user_id', 'k.title')
            ->orderBy('k.title')
            ->get()
            ->groupBy('user_id');

        $rolLabels = ['admin' => 'Yönetici', 'personel' => 'Personel', 'okuyucu' => 'Okuyucu'];
        $filename  = 'kullanicilar_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($users, $yetkiliMap, $rolLabels) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM — Türkçe için
            fputcsv($out, ['#', 'Ad Soyad', 'E-posta', 'Rol', 'Yetkili Kütüphaneler', 'Kayıt Tarihi', 'Son Giriş'], ';');

            foreach ($users as $user) {
                $libs = $yetkiliMap->get($user->id, collect())->pluck('title')->implode(' | ');
                fputcsv($out, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $rolLabels[$user->role] ?? $user->role,
                    $libs ?: '—',
                    $user->created_at ? $user->created_at->format('d.m.Y') : '—',
                    $user->last_login_at
                        ? \Carbon\Carbon::parse($user->last_login_at)->format('d.m.Y H:i')
                        : '—',
                ], ';');
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ─── Yeni Form ──────────────────────────────────────────────────────────────
    public function new()
    {
        abort_unless(Auth::user()?->hasYetki(15), 403);
        $kutuphaneler = Kutuphane::whereNull('deleted_at')
            ->where('statu', 'aktif')
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('users.new', compact('kutuphaneler'));
    }

    // ─── Kaydet ─────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        abort_unless(Auth::user()?->hasYetki(15), 403);
        $request->validate([
            'tc_kimlik'        => ['required', 'digits:11', 'unique:users,tc_kimlik'],
            'dogum_tarihi'     => ['required', 'date', 'before:today'],
            'ad'               => ['required', 'string', 'max:100'],
            'soyad'            => ['required', 'string', 'max:100'],
            'cinsiyet'         => ['nullable', 'string', 'in:erkek,kadin,diger'],
            'email'           => ['required', 'email', 'max:255', 'unique:users,email'],
            'telefon'         => ['required', 'string', 'max:20'],
            'il'              => ['nullable', 'string', 'max:100'],
            'ilce'            => ['nullable', 'string', 'max:100'],
            'mahalle'         => ['nullable', 'string', 'max:150'],
            'acik_adres'      => ['nullable', 'string', 'max:1000'],
            'password'        => ['required', 'confirmed', Password::min(8)],
            'role'            => ['required', 'in:admin,personel,okuyucu'],
            'kutuphane_ids'   => ['nullable', 'array'],
            'kutuphane_ids.*' => ['integer', 'exists:kutuphane,id'],
        ], [
            'tc_kimlik.required' => 'TC Kimlik No zorunludur.',
            'tc_kimlik.digits'   => 'TC Kimlik No 11 rakamdan oluşmalıdır.',
            'tc_kimlik.unique'   => 'Bu TC Kimlik No zaten kayıtlı.',
            'dogum_tarihi.required' => 'Doğum tarihi zorunludur.',
            'dogum_tarihi.before' => 'Doğum tarihi geçmişte olmalıdır.',
            'ad.required'        => 'Ad zorunludur.',
            'soyad.required'     => 'Soyad zorunludur.',
            'email.required'     => 'E-posta adresi zorunludur.',
            'email.email'        => 'Geçerli bir e-posta adresi girin.',
            'email.unique'       => 'Bu e-posta adresi zaten kayıtlı.',
            'telefon.required'   => 'Telefon numarası zorunludur.',
            'password.required'  => 'Şifre zorunludur.',
            'password.confirmed' => 'Şifre tekrarı uyuşmuyor.',
            'password.min'       => 'Şifre en az 8 karakter olmalıdır.',
            'role.required'      => 'Kullanıcı rolü seçilmelidir.',
        ]);

        $adSoyad = trim($request->input('ad') . ' ' . $request->input('soyad'));
        $user = User::create([
            'name'     => $adSoyad,
            'tc_kimlik' => $request->input('tc_kimlik'),
            'dogum_tarihi' => $request->input('dogum_tarihi'),
            'ad'       => $request->input('ad'),
            'soyad'    => $request->input('soyad'),
            'cinsiyet' => $request->input('cinsiyet'),
            'email'    => $request->input('email'),
            'telefon'  => $request->input('telefon'),
            'il'       => $request->input('il'),
            'ilce'     => $request->input('ilce'),
            'mahalle'  => $request->input('mahalle'),
            'acik_adres' => $request->input('acik_adres'),
            'password' => Hash::make($request->input('password')),
            'role'     => $request->input('role'),
        ]);

        $kutuphaneIds = $request->input('kutuphane_ids', []);
        if (!empty($kutuphaneIds)) {
            $now  = now();
            $rows = array_map(fn($kid) => [
                'kutuphane_id' => $kid,
                'user_id'      => $user->id,
                'created_by'   => Auth::id(),
                'created_at'   => $now,
            ], $kutuphaneIds);
            DB::table('kutuphane_yetkili')->insert($rows);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => '"' . $user->name . '" kullanıcısı başarıyla oluşturuldu.',
                'id'      => $user->id,
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', '"' . $user->name . '" kullanıcısı başarıyla oluşturuldu.');
    }

    // ─── Düzenle Form ───────────────────────────────────────────────────────────
    public function edit(User $user)
    {
        abort_unless(Auth::user()?->hasYetki(16), 403);
        return view('users.edit', compact('user'));
    }

    // ════════════════════════════════════════════════════════════════════════════
    // ─── Kullanıcı Yetkileri (21 maddelik izin seti) ────────────────────────────
    // ════════════════════════════════════════════════════════════════════════════

    public function yetkilerForm(User $user)
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $row = DB::table('user_yetkiler')->where('user_id', $user->id)->first();

        $yetkiler = [
            1  => 'Yetkili olduğu kütüphanelerin kitaplarını görebilir.',
            2  => 'Yetkili olduğu kütüphanelerin kitaplarını görebilir ve güncelleyebilir.',
            3  => 'Yetkili olduğu kütüphanelere yeni kitap kaydedebilir.',
            4  => 'Tüm kütüphanelerin kitaplarını görebilir.',
            5  => 'Tüm kütüphanelerin kitaplarını görebilir ve güncelleyebilir.',
            6  => 'Tüm kütüphanelere yeni kitap kaydedebilir.',
            7  => 'Yetkili olduğu kütüphanelerin ödünçlerini görebilir.',
            8  => 'Yetkili olduğu kütüphanelerin ödünçlerini görebilir ve yeni ödünç verebilir.',
            9  => 'Tüm kütüphanelerin ödünçlerini görebilir.',
            10 => 'Tüm kütüphanelerin ödünçlerini görebilir ve yeni ödünç verebilir.',
            11 => 'Üyeleri görebilir.',
            12 => 'Yeni üye oluşturabilir.',
            13 => 'Üye güncelleyebilir',
            14 => 'Kullanıcıları görebilir.',
            15 => 'Yeni kullanıcı oluşturabilir.',
            16 => 'Kullanıcıları görebilir ve güncelleyebilir.',
            17 => 'Kütüphaneleri görebilir.',
            18 => 'Yeni kütüphane oluşturabilir.',
            19 => 'Kütüphaneleri görebilir ve güncelleyebilir.',
            20 => 'Etiket oluşturabilir.',
            21 => 'Dashboard ekranı görme yetkisi.',
        ];

        return view('users.yetkiler', compact('user', 'row', 'yetkiler'));
    }

    public function yetkilerUpdate(Request $request, User $user)
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $data = ['user_id' => $user->id, 'updated_at' => now()];
        for ($i = 1; $i <= 21; $i++) {
            $col = 'y' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $data[$col] = $request->has($col) ? 1 : 0;
        }

        $exists = DB::table('user_yetkiler')->where('user_id', $user->id)->exists();
        if ($exists) {
            DB::table('user_yetkiler')->where('user_id', $user->id)->update($data);
        } else {
            $data['created_at'] = now();
            DB::table('user_yetkiler')->insert($data);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => '"' . $user->name . '" yetkileri güncellendi.']);
        }

        return redirect()->route('users.edit', $user->id)->with('success', '"' . $user->name . '" yetkileri güncellendi.');
    }

    // ─── Güncelle ───────────────────────────────────────────────────────────────
    public function update(Request $request, User $user)
    {
        abort_unless(Auth::user()?->hasYetki(16), 403);
        $request->validate([
            'tc_kimlik' => ['required', 'digits:11', 'unique:users,tc_kimlik,' . $user->id],
            'dogum_tarihi' => ['required', 'date', 'before:today'],
            'ad' => ['required', 'string', 'max:100'],
            'soyad' => ['required', 'string', 'max:100'],
            'cinsiyet' => ['nullable', 'string', 'in:erkek,kadin,diger'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'telefon' => ['required', 'string', 'max:20'],
            'il' => ['nullable', 'string', 'max:100'],
            'ilce' => ['nullable', 'string', 'max:100'],
            'mahalle' => ['nullable', 'string', 'max:150'],
            'acik_adres' => ['nullable', 'string', 'max:1000'],
            'role'  => ['required', 'in:admin,personel,okuyucu'],
        ], [
            'tc_kimlik.required' => 'TC Kimlik No zorunludur.',
            'tc_kimlik.digits' => 'TC Kimlik No 11 rakamdan oluşmalıdır.',
            'tc_kimlik.unique' => 'Bu TC Kimlik No başka bir kullanıcıya ait.',
            'dogum_tarihi.required' => 'Doğum tarihi zorunludur.',
            'dogum_tarihi.before' => 'Doğum tarihi geçmişte olmalıdır.',
            'ad.required' => 'Ad zorunludur.',
            'soyad.required' => 'Soyad zorunludur.',
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email'    => 'Geçerli bir e-posta adresi girin.',
            'email.unique'   => 'Bu e-posta adresi başka bir kullanıcıya ait.',
            'telefon.required' => 'Telefon numarası zorunludur.',
            'role.required'  => 'Kullanıcı rolü seçilmelidir.',
        ]);

        $adSoyad = trim($request->input('ad') . ' ' . $request->input('soyad'));
        $data = [
            'name' => $adSoyad,
            'tc_kimlik' => $request->input('tc_kimlik'),
            'dogum_tarihi' => $request->input('dogum_tarihi'),
            'ad' => $request->input('ad'),
            'soyad' => $request->input('soyad'),
            'cinsiyet' => $request->input('cinsiyet'),
            'email' => $request->input('email'),
            'telefon' => $request->input('telefon'),
            'il' => $request->input('il'),
            'ilce' => $request->input('ilce'),
            'mahalle' => $request->input('mahalle'),
            'acik_adres' => $request->input('acik_adres'),
            'role'  => $request->input('role'),
        ];

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Password::min(8)],
            ], [
                'password.confirmed' => 'Şifre tekrarı uyuşmuyor.',
                'password.min'       => 'Şifre en az 8 karakter olmalıdır.',
            ]);
            $data['password'] = Hash::make($request->input('password'));
        }

        $user->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => '"' . $user->name . '" başarıyla güncellendi.']);
        }

        return redirect()->route('users.index')->with('success', '"' . $user->name . '" başarıyla güncellendi.');
    }

    // ════════════════════════════════════════════════════════════════════════════
    // ─── Kütüphane Yetkilendirme ─────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════════

    public function getYetkiliKutuphaneler(User $user)
    {
        $yetkililer = DB::table('kutuphane_yetkili as ky')
            ->join('kutuphane as k',  'k.id',  '=', 'ky.kutuphane_id')
            ->join('users as cb',     'cb.id', '=', 'ky.created_by')
            ->whereNull('ky.deleted_at')
            ->whereNull('k.deleted_at')
            ->where('ky.user_id', $user->id)
            ->select('ky.id', 'k.id as kutuphane_id', 'k.title', 'k.statu', 'ky.created_at', 'cb.name as created_by_name')
            ->orderBy('k.title')
            ->get();

        return response()->json(['success' => true, 'data' => $yetkililer]);
    }

    public function addYetkiliKutuphane(Request $request, User $user)
    {
        $request->validate(['kutuphane_id' => 'required|integer|exists:kutuphane,id']);
        $kutuphaneId = (int) $request->input('kutuphane_id');

        $exists = DB::table('kutuphane_yetkili')
            ->where('user_id', $user->id)->where('kutuphane_id', $kutuphaneId)->whereNull('deleted_at')->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Bu kullanıcı zaten bu kütüphaneye yetkili.'], 422);
        }

        $yetkiliId = DB::table('kutuphane_yetkili')->insertGetId([
            'kutuphane_id' => $kutuphaneId, 'user_id' => $user->id,
            'created_by' => Auth::id(), 'created_at' => now(), 'deleted_at' => null, 'deleted_by' => null,
        ]);

        $yetkili = DB::table('kutuphane_yetkili as ky')
            ->join('kutuphane as k', 'k.id', '=', 'ky.kutuphane_id')
            ->join('users as cb',   'cb.id', '=', 'ky.created_by')
            ->where('ky.id', $yetkiliId)
            ->select('ky.id', 'k.id as kutuphane_id', 'k.title', 'k.statu', 'ky.created_at', 'cb.name as created_by_name')
            ->first();

        return response()->json(['success' => true, 'message' => '"' . Kutuphane::find($kutuphaneId)->title . '" yetkisi eklendi.', 'data' => $yetkili]);
    }

    public function removeYetkiliKutuphane(User $user, int $yetkiliId)
    {
        $row = DB::table('kutuphane_yetkili')
            ->where('id', $yetkiliId)->where('user_id', $user->id)->whereNull('deleted_at')->first();

        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Kayıt bulunamadı.'], 404);
        }

        DB::table('kutuphane_yetkili')->where('id', $yetkiliId)
            ->update(['deleted_at' => now(), 'deleted_by' => Auth::id()]);

        $kutuphane = Kutuphane::find($row->kutuphane_id);
        return response()->json(['success' => true, 'message' => ($kutuphane ? '"' . $kutuphane->title . '"' : 'Kütüphane') . ' yetkisi kaldırıldı.']);
    }

    public function searchKutuphaneler(Request $request, User $user)
    {
        $q = trim($request->input('q', ''));
        if (mb_strlen($q) < 2) return response()->json(['success' => true, 'data' => []]);

        $mevcutIds = DB::table('kutuphane_yetkili')
            ->where('user_id', $user->id)->whereNull('deleted_at')->pluck('kutuphane_id');

        $kutuphaneler = Kutuphane::whereNull('deleted_at')->where('statu', 'aktif')
            ->where('title', 'LIKE', '%' . $q . '%')
            ->whereNotIn('id', $mevcutIds)->orderBy('title')->limit(10)->get(['id', 'title', 'statu']);

        return response()->json(['success' => true, 'data' => $kutuphaneler]);
    }
}
