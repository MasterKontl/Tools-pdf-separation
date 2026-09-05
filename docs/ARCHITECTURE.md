# Tools DKV — System Architecture & Workflow

## 1. High-Level Architecture Overview

```
Browser / Client (Desktop & Mobile)
   │
   ▼
Frontend: Next.js (App Router, Vercel)
   │  • Flat Solid Colors Only (Strictly NO gradients)
   │  • Interactive Canvas (Multi-layer Compositing, Solo Film, Garment Color Toggle, Zoom/Pan)
   │  • Dedicated API Client (`NEXT_PUBLIC_API_URL`)
   │
   ▼ (REST / JSON / Multipart FormData)
Backend: Laravel API + Processing Engines (Railway)
   ├── routes/api.php
   ├── routes/web.php (Dual compatibility & existing Blade tests)
   ├── Auth: Stateless Bearer Tokens (AES-256 AuthTokenService) & Stateful Sessions
   ├── Quota: Server-side QuotaService (Guest 1/day, User 3/day, Admin unlimited)
   ├── Engine: Poppler CLI (`pdftoppm`), GD ColorSeparationService, CDR detector
   └── SSRF: PdfUrlFetcherService (Multi-layer validation, metadata IP blocking)
   │
   ├─────────────────────────┬─────────────────────────┐
   ▼                         ▼                         ▼
Supabase PostgreSQL       Pakasir Payment Gateway   Local Temp / Disk Storage
(Database & Migrations)   (Checkout & Webhook IPN)  (Ephemeral file processing)
```

---

## 2. Component Responsibility

| Component | Technology | Responsibility |
| :--- | :--- | :--- |
| **Frontend** | Next.js (App Router, Tailwind CSS, TypeScript) | UI rendering, client-side interactions, file dropzone, canvas multi-layer preview, dark mode, route navigation. No heavy image processing. |
| **Backend API** | Laravel 11/12 (PHP 8.3+) | Authentication, rate limiting, quota checks, file rasterization, color separation, Pakasir webhook signature verification, admin endpoints. |
| **Database** | PostgreSQL (Supabase) | Persistent storage for users, plans, subscriptions, payment logs, conversion usage records. |
| **Payment Gateway** | Pakasir | QRIS, Virtual Account, E-Wallet checkout and IPN webhook notification. |
| **Processing Engine** | Poppler (`pdftoppm`), GD Library, CLI | Heavy rendering of PDF pages, CMYK / Spot / Grayscale / Underbase channel separation, morphologic Choke and Trap operations. |

---

## 3. Core Workflow Diagrams

### A. Authentication Flow
```
User (Browser)               Next.js Frontend                Laravel API (Backend)
     │                               │                               │
     │── Fill Email & Password ─────▶│                               │
     │                               │── POST /api/auth/login ──────▶│
     │                               │                               │── Validate credentials & rate limit
     │                               │                               │── Check account status (is_active)
     │                               │                               │── Generate encrypted Bearer token
     │                               │◀── Return Token & User JSON ──│
     │◀── Store Token & Redirect ────│                               │
```

### B. Quota Enforcement Flow
- **Guest**: Exactly 1 conversion per day, tracked by client IP and session hash in database `conversion_usages`.
- **Registered User**: 3 conversions per day (or custom `daily_limit`).
- **Admin**: Unlimited conversions (`unlimited = true`).
- **DPI Entitlement**:
  - 150 & 300 DPI: Available to all users.
  - 600 DPI: Visible but locked for Guest and Free users; unlocked for Admin, Unlimited, and Pro plans.

### C. PDF Conversion Flow
```
User                         Next.js Frontend                Laravel API
 │                                   │                            │
 │── Upload PDF or Import URL ──────▶│                            │
 │                                   │── (Optional) Import URL ──▶│── Validate SSRF (DNS, private IP block)
 │                                   │◀── Return Temp ID ─────────│
 │── Click Convert (DPI, Format) ───▶│                            │
 │                                   │── POST /api/convert ──────▶│── Reserve quota slot atomically
 │                                   │   (Multipart or Temp ID)   │── Execute pdftoppm / GD rasterizer
 │                                   │                            │── Multi-page -> ZIP; Single -> Image
 │                                   │                            │── Confirm quota reservation
 │                                   │◀── Stream Binary File ─────│ (Rollback quota if conversion fails)
 │◀── Trigger Browser Download ──────│
```

### D. Screen Print Separation Flow
```
User                         Next.js Frontend                Laravel API
 │                                   │                            │
 │── Upload Image/PDF/CDR & Mode ───▶│                            │
 │── Set Choke (mm), Trap (mm) ─────▶│                            │
 │                                   │── POST /api/separation ───▶│── Rasterize input to standard DPI
 │                                   │                            │── Execute ColorSeparationService
 │                                   │                            │   (CMYK / Underbase / Spot / Outline)
 │                                   │                            │   (Apply Choke erosion & Trap dilation)
 │                                   │                            │   (Add Registration marks if enabled)
 │                                   │◀── Return Token & Manifest─│
 │◀── Render Multi-layer Canvas ─────│                            │
 │    - Toggle individual channels   │── GET /api/separation/     │
 │    - Solo film inspection         │   preview/{token}/{channel}│── Stream individual channel PNG
 │    - Garment color background     │◀───────────────────────────│
 │── Export Single Channel / ZIP ───▶│── GET /api/separation/     │
 │                                   │   download/{token}/{chan} ─│── Download PNG or full ZIP
```

### E. Pakasir Payment & Webhook Flow
```
User                    Next.js Frontend             Laravel API              Pakasir Server
 │                             │                          │                         │
 │── Click Upgrade (Plan) ────▶│                          │                         │
 │                             │── POST /api/payments/ ──▶│                         │
 │                             │   checkout/{plan}        │── Create PENDING order  │
 │                             │                          │── Build Checkout URL ──▶│
 │                             │◀── Return Checkout URL ──│                         │
 │◀── Redirect to Pakasir ─────│                          │                         │
 │                                                        │                         │
 │── Pay via QRIS/VA ──────────────────────────────────────────────────────────────▶│
 │                                                        │                         │
 │                                                        │◀── POST Webhook IPN ────│
 │                                                        │    (order_id, amount)   │
 │                                                        │── Verify transaction ──▶│
 │                                                        │◀── Transaction Valid ───│
 │                                                        │── Mark Payment PAID     │
 │                                                        │── Activate Subscription │
 │                                                        │── Return 200 OK ───────▶│
```

---

## 4. Local Development Workflow

### Terminal 1: Backend (Laravel API)
```bash
cd backend
php artisan serve --port=8000
```
API Base URL: `http://localhost:8000/api`

### Terminal 2: Frontend (Next.js)
```bash
cd frontend
npm run dev
```
Frontend URL: `http://localhost:3000`

---

## 5. Security & Isolation Mandates
1. **SSRF Multi-layered Defense**: Strict DNS pre-resolution, loopback (`127.0.0.0/8`), RFC1918 (`10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`), AWS metadata (`169.254.169.254`), and private IPv6 blocks.
2. **Quota Rollback Guarantee**: Atomic reservation prior to file processing; released automatically on any caught exception.
3. **Flat Solid Palette (Zero Gradients)**: Strict enforcement across all UI components.
4. **Environment Isolation**: Pakasir credentials and database secrets are strictly server-side.
