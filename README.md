# Tools DKV

Monorepo arsitektur terpisah untuk **Tools DKV** (High-Performance PDF Converter, Screen Print Separation Editor, dan Subscription System).

---

## Struktur Repositori

```text
Tools-DKV/
├── backend/                  # Laravel 11/12 API & Processing Engine
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/
│   ├── resources/            # Blade fallback & backward-compatible views
│   ├── routes/
│   │   ├── api.php           # REST API routes untuk Frontend Next.js
│   │   └── web.php           # Legacy & Web views routes
│   ├── storage/
│   ├── tests/                # PHPUnit Test Suite (70 tests, 100% PASS)
│   ├── artisan
│   ├── composer.json
│   └── .env.example
│
├── frontend/                 # Next.js 14/15 App Router (Target Fase 2)
│   ├── app/
│   ├── components/
│   ├── hooks/
│   ├── lib/
│   └── .env.example
│
├── docs/                     # Dokumentasi Sistem
│   ├── ARCHITECTURE.md       # Diagram alur, data flow, dan CORS
│   ├── PRD.md
│   └── TODOS.md
│
├── .gitignore
└── README.md
```

---

## Local Development Workflow

### 1. Menjalankan Backend (Laravel API)
```bash
cd backend
php artisan serve --port=8000
```
Backend API berjalan di `http://localhost:8000/api`

### 2. Menjalankan Frontend (Next.js) *(Fase 2)*
```bash
cd frontend
npm run dev
```
Frontend berjalan di `http://localhost:3000`

---

## Test Suite
Untuk menjalankan test backend:
```bash
cd backend
php artisan test
# atau
vendor/bin/phpunit
```
*Hasil status saat ini: 70 tests, 386 assertions, 100% PASS.*
