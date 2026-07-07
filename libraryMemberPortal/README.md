# Kütüphane Üye Portalı

Modern, mobil öncelikli üye web uygulaması. [libraryProject](../libraryProject) Laravel API'si (`/api/mobile/*`) ile haberleşir.

## Özellikler

- T.C. kimlik + doğum tarihi ile giriş
- KPS doğrulamalı üye kaydı (18 yaş altı için veli bilgisi)
- Kitap kataloğu (arama, filtreleme, sayfalama)
- Kitap detayı, favori ekleme/çıkarma
- Rezervasyon oluşturma ve iptal
- Profil ve üye istatistikleri
- Mobilde alt navigasyon çubuğu (uygulama hissi)

## Gereksinimler

- Node.js 18+
- Çalışır durumda [libraryProject](../libraryProject) (XAMPP / Laravel)

## Kurulum

```bash
cd c:\xampp\htdocs\libraryMemberPortal
npm install
cp .env.example .env   # production build için API URL
npm run dev
```

Uygulama: **http://localhost:5174**

Geliştirme modunda Vite proxy, `/api` isteklerini `http://localhost:85` adresine yönlendirir (Laravel `htdocs/index.php` üzerinden çalışır).

## Production Build

```bash
npm run build
npm run preview
```

`.env` dosyasında API adresini ayarlayın:

```
VITE_API_BASE_URL=http://localhost:85/api/mobile
```

Build çıktısını Apache/Nginx ile statik olarak sunabilirsiniz.

## API Endpoints (libraryProject)

| Metot | Endpoint | Açıklama |
|-------|----------|----------|
| POST | `/api/mobile/auth/register` | Üye kaydı |
| POST | `/api/mobile/auth/token` | Giriş (JWT) |
| GET | `/api/mobile/kitaplar` | Kitap listesi |
| GET | `/api/mobile/kitapdetay?katalog_id=` | Kitap detayı |
| GET/POST/DELETE | `/api/mobile/favoriler`, `favoriekle`, `favorisil` | Favoriler |
| GET/POST | `/api/mobile/rezervasyonlar`, `rezervasyonekle`, `rezervasyoniptal` | Rezervasyon |

Tüm korumalı isteklerde `Authorization: Bearer {token}` header'ı gerekir.

## Not

Bu proje **libraryProject'ten bağımsızdır**; backend kodunda değişiklik yapılmamıştır. CORS sorunu yaşarsanız Laravel tarafında ilgili origin'e izin vermeniz gerekebilir (production için).
