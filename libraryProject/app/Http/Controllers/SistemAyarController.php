<?php

namespace App\Http\Controllers;

use App\Models\SistemAyar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SistemAyarController extends Controller
{
    private const YETKI = 36;

    public function index()
    {
        abort_unless(auth()->user()?->hasYetki(self::YETKI), 403);

        $ayar = SistemAyar::current();

        return view('sistem_ayar.edit', compact('ayar'));
    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()?->hasYetki(self::YETKI), 403);

        $validated = $request->validate([
            'kurum_adi'           => ['nullable', 'string', 'max:250'],
            'web_sitesi'          => ['nullable', 'string', 'max:250'],
            'is_telefonu'         => ['nullable', 'string', 'max:30'],
            'cep_telefonu'        => ['nullable', 'string', 'max:30'],
            'eposta'              => ['nullable', 'email', 'max:250'],
            'il'                  => ['nullable', 'string', 'max:100'],
            'ilce'                => ['nullable', 'string', 'max:100'],
            'adres'               => ['nullable', 'string', 'max:2000'],
            'izinli_ip_adresleri' => ['nullable', 'string', 'max:5000'],
            'logo'                => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:3072'],
            'logo_kaldir'         => ['nullable', 'boolean'],
        ], [
            'eposta.email'   => 'Geçerli bir e-posta adresi girin.',
            'logo.mimes'     => 'Logo formatı jpg, jpeg, png, webp veya svg olmalıdır.',
            'logo.max'       => 'Logo boyutu en fazla 3MB olabilir.',
        ]);

        $this->validateIzinliIpListesi($validated['izinli_ip_adresleri'] ?? null);

        $ayar = SistemAyar::current();

        $data = [
            'kurum_adi'           => $validated['kurum_adi'] ?? null,
            'web_sitesi'          => $validated['web_sitesi'] ?? null,
            'is_telefonu'         => $validated['is_telefonu'] ?? null,
            'cep_telefonu'        => $validated['cep_telefonu'] ?? null,
            'eposta'              => $validated['eposta'] ?? null,
            'il'                  => $validated['il'] ?? null,
            'ilce'                => $validated['ilce'] ?? null,
            'adres'               => $validated['adres'] ?? null,
            'izinli_ip_adresleri' => $this->normalizeIzinliIpListesi($validated['izinli_ip_adresleri'] ?? null),
            'updated_by'          => Auth::id(),
        ];

        if (! $ayar->created_by) {
            $data['created_by'] = Auth::id();
        }

        if ($request->hasFile('logo')) {
            if ($ayar->logo_path && Storage::disk('public')->exists($ayar->logo_path)) {
                Storage::disk('public')->delete($ayar->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('sistem', 'public');
        } elseif ($request->boolean('logo_kaldir')) {
            if ($ayar->logo_path && Storage::disk('public')->exists($ayar->logo_path)) {
                Storage::disk('public')->delete($ayar->logo_path);
            }
            $data['logo_path'] = null;
        }

        $ayar->update($data);
        $ayar->refresh();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Sistem ayarları başarıyla güncellendi.',
                'logo_url' => $ayar->logo_url,
            ]);
        }

        return redirect()->route('sistem_ayar.index')
            ->with('success', 'Sistem ayarları başarıyla güncellendi.');
    }

    private function validateIzinliIpListesi(?string $raw): void
    {
        $ips = $this->parseIpList($raw);
        $gecersiz = [];

        foreach ($ips as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
                $gecersiz[] = $ip;
            }
        }

        if ($gecersiz !== []) {
            throw ValidationException::withMessages([
                'izinli_ip_adresleri' => 'Geçersiz IP adresi: ' . implode(', ', $gecersiz),
            ]);
        }
    }

    private function normalizeIzinliIpListesi(?string $raw): ?string
    {
        $ips = $this->parseIpList($raw);

        return $ips === [] ? null : implode("\n", $ips);
    }

    /**
     * @return list<string>
     */
    private function parseIpList(?string $raw): array
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return [];
        }

        $parts = preg_split('/[\s,;]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique(array_map('trim', $parts)));
    }
}
