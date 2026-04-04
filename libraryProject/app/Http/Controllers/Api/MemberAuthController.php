<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\KpsController;
use App\Models\Uye;
use App\Support\JwtToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class MemberAuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tc_kimlik' => ['required', 'digits:11'],
            'dogum_tarihi' => ['required', 'date_format:Y-m-d', 'before:today'],
            'email' => ['required', 'email', 'max:255'],
            'telefon' => ['required', 'string', 'max:20'],
            'il' => ['required', 'string', 'max:100'],
            'ilce' => ['required', 'string', 'max:100'],
            'mahalle' => ['nullable', 'string', 'max:150'],
            'acik_adres' => ['nullable', 'string', 'max:1000'],
        ], [
            'tc_kimlik.required' => 'TC kimlik numarasi zorunludur.',
            'tc_kimlik.digits' => 'TC kimlik numarasi 11 haneli olmalidir.',
            'dogum_tarihi.required' => 'Dogum tarihi zorunludur.',
            'dogum_tarihi.date_format' => 'Dogum tarihi Y-m-d formatinda olmalidir.',
            'dogum_tarihi.before' => 'Dogum tarihi bugunden once olmalidir.',
            'email.required' => 'E-posta zorunludur.',
            'email.email' => 'Gecerli bir e-posta adresi girin.',
            'telefon.required' => 'Telefon zorunludur.',
            'il.required' => 'Il zorunludur.',
            'ilce.required' => 'Ilce zorunludur.',
        ]);

        $mevcutUye = Uye::query()->where('tc_kimlik', $validated['tc_kimlik'])->first();
        if ($mevcutUye) {
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'success' => false,
                'message' => 'Bu TC kimlik numarasina ait aktif bir uyelik zaten bulunuyor.',
                'uye_id' => $mevcutUye->id,
            ], Response::HTTP_CONFLICT);
        }

        $emailKullanimda = Uye::query()->where('email', $validated['email'])->exists();
        if ($emailKullanimda) {
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'success' => false,
                'message' => 'Bu e-posta adresi ile daha once uyelik olusturulmus.',
            ], Response::HTTP_CONFLICT);
        }

        $yas = Carbon::parse($validated['dogum_tarihi'])->age;
        $veli = [];

        if ($yas < 18) {
            $veli = $request->validate([
                'veli_ad' => ['required', 'string', 'max:100'],
                'veli_soyad' => ['required', 'string', 'max:100'],
                'veli_tc_kimlik' => ['required', 'digits:11'],
                'veli_dogum_tarihi' => ['required', 'date_format:Y-m-d', 'before:today'],
                'veli_telefon' => ['required', 'string', 'max:20'],
            ], [
                'veli_ad.required' => 'Veli adi zorunludur.',
                'veli_soyad.required' => 'Veli soyadi zorunludur.',
                'veli_tc_kimlik.required' => 'Veli TC kimlik numarasi zorunludur.',
                'veli_tc_kimlik.digits' => 'Veli TC kimlik numarasi 11 haneli olmalidir.',
                'veli_dogum_tarihi.required' => 'Veli dogum tarihi zorunludur.',
                'veli_dogum_tarihi.date_format' => 'Veli dogum tarihi Y-m-d formatinda olmalidir.',
                'veli_dogum_tarihi.before' => 'Veli dogum tarihi bugunden once olmalidir.',
                'veli_telefon.required' => 'Veli telefonu zorunludur.',
            ]);
        }

        $kpsSonuc = KpsController::kimlikSorgula($validated['dogum_tarihi'], $validated['tc_kimlik']);

        if (!($kpsSonuc['success'] ?? false) || empty($kpsSonuc['sbsKisiDto'])) {
            $kpsMesaji = $kpsSonuc['message'] ?? null;

            return response()->json([
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'success' => false,
                'message' => $kpsMesaji
                    ? 'Kimlik dogrulamasi basarisiz: '.$kpsMesaji
                    : 'Kimlik dogrulamasi basarisiz. TC kimlik numarasi ve dogum tarihini kontrol edin.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $kisi = $kpsSonuc['sbsKisiDto'];
        $ad = $kisi['adi'] ?? '';
        $soyad = $kisi['soyadi'] ?? '';

        if ($ad === '' || $soyad === '') {
            return response()->json([
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'success' => false,
                'message' => 'Kimlik servisinden ad/soyad alinamadi.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $uye = Uye::query()->create([
            'tc_kimlik' => $validated['tc_kimlik'],
            'dogum_tarihi' => $validated['dogum_tarihi'],
            'ad' => $ad,
            'soyad' => $soyad,
            'email' => $validated['email'],
            'telefon' => $validated['telefon'],
            'telefon_dogrulandi' => false,
            'il' => $validated['il'],
            'ilce' => $validated['ilce'],
            'mahalle' => $validated['mahalle'],
            'acik_adres' => $validated['acik_adres'],
            'statu' => 'aktif',
            'uyelik_baslangic' => Carbon::now()->toDateString(),
            'veli_ad' => $veli['veli_ad'] ?? null,
            'veli_soyad' => $veli['veli_soyad'] ?? null,
            'veli_tc_kimlik' => $veli['veli_tc_kimlik'] ?? null,
            'veli_dogum_tarihi' => $veli['veli_dogum_tarihi'] ?? null,
            'veli_telefon' => $veli['veli_telefon'] ?? null,
        ]);

        if ($uye) {
            $secret = config('app.jwt_secret');
            if (!$secret) {
                return response()->json([
                    'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'success' => false,
                    'message' => 'JWT secret tanimli degil.',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $ttlMinutes = (int) config('app.jwt_ttl_minutes', 120);
            $issuedAt = Carbon::now();
            $expiresAt = $issuedAt->copy()->addMinutes($ttlMinutes);
            
            $payload = [
                'iss' => config('app.url'),
                'sub' => $uye->id,
                'iat' => $issuedAt->timestamp,
                'exp' => $expiresAt->timestamp,
            ];

            $token = JwtToken::encode($payload, $secret);
            return response()->json([
                'status' => Response::HTTP_CREATED,
                'success' => true,
                'message' => 'Kayıt başarıyla oluşturuldu.',
                'data' => [
                    'token_type' => 'Bearer',
                    'expires_in' => $ttlMinutes * 60,
                    'uye_id' => $uye->id,
                    'kimlik_kps_ile_dogrulandi' => true,
                    'token' => $token,
                ],
            ], Response::HTTP_CREATED);
        } else {
            return response()->json([
                'message' => 'Üye kaydı oluşturulamadı. Lütfen daha sonra tekrar deneyiniz.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function token(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tc_kimlik' => ['required', 'digits:11'],
            'dogum_tarihi' => ['required', 'date_format:Y-m-d'],
        ], [
            'tc_kimlik.required' => 'T.C. kimlik numarası zorunludur.',
            'tc_kimlik.digits' => 'T.C. kimlik numarası 11 haneli olmalıdır.',
            'dogum_tarihi.required' => 'Doğum tarihi zorunludur.',
            'dogum_tarihi.date_format' => 'Doğum tarihi Y-m-d formatında olmalıdır.',
        ]);

        $uye = Uye::query()
            ->where('tc_kimlik', $validated['tc_kimlik'])
            ->first();

        if (!$uye) {
            return response()->json([
                'status' => Response::HTTP_UNAUTHORIZED,
                'success' => false,
                'message' => 'Girilen T.C. kimlik numarasına ait bir üye bulunamadı.',
            ], Response::HTTP_UNAUTHORIZED);
        } else {
            if ($uye->dogum_tarihi->format('Y-m-d') !== $validated['dogum_tarihi']) {
                return response()->json([
                    'status' => Response::HTTP_UNAUTHORIZED,
                    'success' => false,
                    'message' => "Bilgileri kontrol ediniz.",
                ], Response::HTTP_UNAUTHORIZED);
            }
            if ($uye->statu !== 'aktif') {
                return response()->json([
                    'status' => Response::HTTP_UNAUTHORIZED,
                    'success' => false,
                    'message' => 'Üyeliğiniz aktif değil.',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        $secret = config('app.jwt_secret');
        if (!$secret) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'success' => false,
                'message' => 'JWT secret tanimli degil.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $ttlMinutes = (int) config('app.jwt_ttl_minutes', 120);
        $issuedAt = Carbon::now();
        $expiresAt = $issuedAt->copy()->addMinutes($ttlMinutes);

        $payload = [
            'iss' => config('app.url'),
            'sub' => $uye->id,
            'iat' => $issuedAt->timestamp,
            'exp' => $expiresAt->timestamp,
        ];

        $token = JwtToken::encode($payload, $secret);

        return response()->json([
            'status' => Response::HTTP_OK,
            'success' => true,
            'message' => 'Token başarıyla oluşturuldu.',
            'data' => [
                'token_type' => 'Bearer',
                'token' => $token,
                'expires_in' => $ttlMinutes * 60,
                'uye_id' => $uye->id,
            ],
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        /** @var Uye $uye */
        $uye = $request->attributes->get('uye');

        if ($uye->statu !== 'aktif') {
            return response()->json([
                'status' => Response::HTTP_UNAUTHORIZED,
                'success' => false,
                'message' => 'Üyeliğiniz aktif değil.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($uye->uyelik_bitis && $uye->uyelik_bitis < Carbon::now()) {
            return response()->json([
                'status' => Response::HTTP_UNAUTHORIZED,
                'success' => false,
                'message' => 'Üyeliğinizin süresi doldu.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'success' => true,
            'message' => 'Profil bilgileri başarıyla getirildi.',
            'data' => [
                    'id' => $uye->id,
                    'tc_kimlik' => $uye->tc_kimlik,
                    'ad' => $uye->ad,
                    'soyad' => $uye->soyad,
                    'ad_soyad' => $uye->ad_soyad,
                    'dogum_tarihi' => optional($uye->dogum_tarihi)->format('Y-m-d'),
                    'email' => $uye->email,
                    'telefon' => $uye->telefon,
                    'telefon2' => $uye->telefon2,
                    'telefon_dogrulandi' => (bool) $uye->telefon_dogrulandi,
                    'il' => $uye->il,
                    'ilce' => $uye->ilce,
                    'mahalle' => $uye->mahalle,
                    'acik_adres' => $uye->acik_adres,
                    'ogretim_durumu' => $uye->ogretim_durumu,
                    'okul_adi' => $uye->okul_adi,
                    'bolum_adi' => $uye->bolum_adi,
                    'statu' => $uye->statu,
                    'uyelik_baslangic' => optional($uye->uyelik_baslangic)->format('Y-m-d'),
                    'uyelik_bitis' => optional($uye->uyelik_bitis)->format('Y-m-d'),
                    'veli_ad' => $uye->veli_ad,
                    'veli_soyad' => $uye->veli_soyad,
                    'veli_tc_kimlik' => $uye->veli_tc_kimlik,
                    'veli_dogum_tarihi' => optional($uye->veli_dogum_tarihi)->format('Y-m-d'),
                    'veli_telefon' => $uye->veli_telefon,
            ],
        ]);
    }
}
