<div align="center">

# Tools DKV

**PDF Converter &bull; Color Separation &bull; Image Upscaler**

Platform tool desain grafis berbasis web untuk kebutuhan DKV, sablon, dan printing.

[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)](https://www.php.net)
[![Railway](https://img.shields.io/badge/Deployed-On%20Railway-0B0D2E?logo=railway&logoColor=white)](https://railway.app)

**[Live Production](https://kurniawansatya.xyz)**

</div>

---

## Fitur

### PDF Converter
- Konversi PDF ke **PNG** atau **JPG**
- Resolusi: **150 DPI** (web), **300 DPI** (cetak), **600 DPI** (pro)
- Engine: Poppler `pdftoppm` (high-fidelity rendering)
- Multi-page PDF otomatis dibundel dalam file **ZIP**
- **Batch conversion** &mdash; pilih hingga 10 file sekaligus, proses independen, retry per-file
- Import dari URL, Google Drive, Dropbox, OneDrive

### Color Separation
- Editor separasi film sablon digital interaktif
- Mode: CMYK, Underbase, Spot Color, Grayscale, Outline, RGB
- Choke & Trap (mm) untuk prepress trapping
- Registration marks untuk alignment cetak
- Input: PDF, PNG, JPG, CorelDRAW (CDR)

### Image Upscaler
- Perbesar resolusi gambar 2&times; atau 4&times;
- Interpolasi bicubic
- Input: JPG, PNG, WEBP

### Platform
- Dark / Light mode
- Responsive (mobile &amp; desktop)
- Quota system (Guest 1/day, User 3/day, Admin unlimited)
- Payment gateway (Pakasir &mdash; QRIS, VA, E-Wallet)
- SEO optimized (meta tags, structured data, sitemap, GSC verified)

---

## Tech Stack

| Layer | Technology |
|:---|:---|
| Backend | Laravel 13 (PHP 8.4+) |
| Database | PostgreSQL (Supabase) |
| PDF Engine | Poppler CLI (`pdftoppm`) |
| Image Processing | PHP GD Library |
| Hosting | Railway |
| Payment | Pakasir |
| Email | Resend |

---

## Struktur Repositori

```
Tools-DKV/
├── backend/                  # Laravel 13
│   ├── app/
│   │   ├── Http/Controllers/ # PdfConverterController, ColorSeparationController, UpscalerController
│   │   ├── Services/         # PdfConverterService, ColorSeparationService, UpscalerService, QuotaService
│   │   └── Models/           # User, Plan, Subscription, Payment, ConversionUsage
│   ├── config/
│   │   └── converter.php     # PDF conversion settings (DPI, batch size, timeout)
│   ├── resources/views/      # Blade templates (converter, separation, upscaler, pricing)
│   ├── routes/web.php        # Web routes
│   ├── tests/Feature/        # 14 test files, 153 test methods
│   └── composer.json
│
├── docs/
│   └── ARCHITECTURE.md       # System architecture & data flow diagrams
│
├── AGENTS.md                 # AI agent instructions
├── CLAUDE.md                 # Claude agent config
└── README.md
```

---

## Local Development

### Prerequisites
- PHP 8.3+
- Composer
- Node.js (optional, for frontend)

### Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve --port=8000
```

Backend berjalan di `http://localhost:8000`

### Testing

```bash
cd backend
php artisan test
```

14 test files &bull; 153 test methods

---

## Deployment

Production di-deploy otomatis ke **Railway** setiap push ke `master`.

```text
Production URL : https://kurniawansatya.xyz
GitHub         : MasterKontl/Tools-pdf-separation
Branch         : master
```

### Rollback

Jika deployment gagal, rollback ke commit known-good terakhir:

```bash
git log --oneline -5       # Cari commit hash
git revert <commit-hash>   # Buat rollback commit
git push origin master     # Trigger redeploy
```

---

## Konfigurasi

### Batch Conversion

```env
# backend/.env
PDF_MAX_BATCH_SIZE=10        # Maksimum file per batch
PDF_MAX_FILE_SIZE_KB=256000  # 250 MB per file
PDFTOPPM_TIMEOUT=600         # 10 menit timeout per konversi
```

### Quota

| Role | Limit/Hari | DPI |
|:---|:---|:---|
| Guest | 1 | 150, 300 |
| User | 3 | 150, 300, 600 |
| Admin | Unlimited | 150, 300, 600 |

---

## Security

- SSRF protection untuk URL import (DNS resolution, private IP blocking)
- CSRF protection di semua form & AJAX request
- Rate limiting: 30 req/min (authenticated), 5 req/min (guest)
- File validation: type, extension, size (250 MB max)
- Quota atomic reservation dengan rollback on failure
- Temp files auto-cleanup setelah konversi

---

## SEO & Domain Setup

### Production Canonical Domain

```
https://kurniawansatya.xyz
```

The old Railway domain (`pdf-converter-app-production.up.railway.app`) is **not** the canonical SEO domain. All public URLs must use `kurniawansatya.xyz`.

### Sitemap

- **URL:** `https://kurniawansatya.xyz/sitemap.xml`
- Generated dynamically from `config('app.url')` + Laravel `route()` helper
- Indexable public pages:
  - `https://kurniawansatya.xyz/` (priority 1.0, weekly)
  - `https://kurniawansatya.xyz/separation` (priority 0.9, weekly)
  - `https://kurniawansatya.xyz/upscaler` (priority 0.9, weekly)
  - `https://kurniawansatya.xyz/pricing` (priority 0.8, monthly)
- Admin, auth, payment, and dashboard routes are excluded

### robots.txt

- **URL:** `https://kurniawansatya.xyz/robots.txt`
- Allows all crawlers on public pages
- Disallows: `/admin`, `/dashboard`, `/login`, `/register`, `/forgot-password`, `/reset-password`, `/logout`, `/payment/finish`, `/payment/webhook`
- References sitemap at `https://kurniawansatya.xyz/sitemap.xml`

### SEO Meta Tags

- Canonical, Open Graph, and Twitter Card tags are injected via `partials/seo-tags.blade.php`
- `$seo['url']` uses `url()->current()` which resolves to the canonical domain via `APP_URL`
- JSON-LD structured data (WebSite + WebApplication) is generated in `AppServiceProvider::buildStructuredData()`
- Google Search Console verified via meta tag: `2bjv5oSI_qog0xVzn6zLSJ9Xliyd-RnNH_nPuazdTGQ`

### Google Search Console

- Submit sitemap: `https://kurniawansatya.xyz/sitemap.xml`
- After domain migration, allow 1-2 weeks for Googlebot to recrawl and re-index
- Verify that the property is set to `https://kurniawansatya.xyz` (not the Railway URL)

### Cloudflare / Railway

- Cloudflare is used as CDN/proxy (`Server: cloudflare` in response headers)
- Railway serves the application backend (`x-railway-edge` header visible)
- `APP_URL` must be set to `https://kurniawansatya.xyz` in Railway environment variables
- Cloudflare SSL mode should be "Full (Strict)" to avoid redirect loops

---

## License

MIT
