<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengguna | Tools DKV</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = savedTheme || (prefersDark ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <style>
        :root {
            --bg-body: #F8FAFC;
            --surface: #FFFFFF;
            --surface-alt: #F1F5F9;
            --surface-hover: #F1F5F9;
            --primary: #1E3A5F;
            --secondary: #274C77;
            --accent: #3B82F6;
            --accent-hover: #2563EB;
            --text-main: #0F172A;
            --text-muted: #64748B;
            --text-dim: #94A3B8;
            --border: #E2E8F0;
            --nav-bg: #1E3A5F;
            --nav-border: #274C77;
            --nav-item: #CBD5E1;
            --nav-item-active: #FFFFFF;
            --banner-bg: #EFF6FF;
            --banner-border: #BFDBFE;
            --banner-text: #1E3A5F;
            --success-bg: #F0FDF4;
            --success-border: #BBF7D0;
            --success-text: #166534;
            --badge-paid-bg: #DCFCE7;
            --badge-paid-text: #166534;
            --badge-paid-border: #BBF7D0;
            --badge-pending-bg: #FEF3C7;
            --badge-pending-text: #92400E;
            --badge-pending-border: #FDE68A;
            --radius-lg: 12px;
            --radius-md: 8px;
            --radius-sm: 6px;
            --shadow-card: 0 4px 16px rgba(30, 58, 95, 0.08);
            --touch-target: 44px;
        }

        [data-theme="dark"] {
            --bg-body: #0B1120;
            --surface: #1E293B;
            --surface-alt: #0F172A;
            --surface-hover: #334155;
            --primary: #3B82F6;
            --secondary: #2563EB;
            --accent: #60A5FA;
            --accent-hover: #93C5FD;
            --text-main: #F8FAFC;
            --text-muted: #94A3B8;
            --text-dim: #64748B;
            --border: #334155;
            --nav-bg: #0A0F1D;
            --nav-border: #1E293B;
            --nav-item: #94A3B8;
            --nav-item-active: #FFFFFF;
            --banner-bg: #1E293B;
            --banner-border: #3B82F6;
            --banner-text: #93C5FD;
            --success-bg: #052E16;
            --success-border: #14532D;
            --success-text: #86EFAC;
            --badge-paid-bg: #052E16;
            --badge-paid-text: #86EFAC;
            --badge-paid-border: #14532D;
            --badge-pending-bg: #451A03;
            --badge-pending-text: #FDE68A;
            --badge-pending-border: #78350F;
            --shadow-card: 0 4px 20px rgba(0, 0, 0, 0.4);
        }

        html, body {
            width: 100%;
            max-width: 100%;
            overflow-x: clip;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        /* SaaS Top Navbar */
        .navbar {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 2rem;
            border-bottom: 1px solid var(--nav-border);
            background: var(--nav-bg);
            position: sticky;
            top: 0;
            z-index: 50;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }
        .nav-brand {
            display: flex; align-items: center; gap: 8px;
            font-size: 1.15rem; font-weight: 800; color: #ffffff; text-decoration: none;
            letter-spacing: -0.02em;
        }
        .nav-brand-badge {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ffffff;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 4px;
        }
        .nav-menu { display: flex; align-items: center; gap: 20px; }
        .nav-item { color: var(--nav-item); text-decoration: none; font-size: 0.9rem; font-weight: 600; transition: color 0.15s; }
        .nav-item:hover, .nav-item.active { color: var(--nav-item-active); }
        .nav-user-actions { display: flex; align-items: center; gap: 10px; }

        .btn-mobile-nav-toggle {
            display: none;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-sm);
            width: 36px;
            height: 36px;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            cursor: pointer;
            padding: 0;
            flex-shrink: 0;
            transition: all 0.15s ease;
        }
        .btn-mobile-nav-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .mobile-nav-panel {
            display: none;
            position: fixed;
            top: var(--navbar-height, 61px);
            left: 0;
            width: 100%;
            background: var(--nav-bg);
            border-bottom: 1px solid var(--nav-border);
            padding: 1rem var(--radius-md);
            z-index: 49;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            flex-direction: column;
            gap: 12px;
        }
        .mobile-nav-panel.open {
            display: flex;
            animation: mobileNavSlideDown 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes mobileNavSlideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .mobile-nav-panel .mobile-nav-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.875rem 1rem;
            border-radius: var(--radius-md);
            color: var(--nav-item);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid transparent;
            transition: all 0.15s ease;
            min-height: var(--touch-target);
        }
        .mobile-nav-panel .mobile-nav-link:hover,
        .mobile-nav-panel .mobile-nav-link.active {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.15);
        }
        .mobile-nav-panel .mobile-nav-link.active::after {
            content: "•";
            color: var(--accent);
            font-size: 1.25rem;
            line-height: 1;
        }
        .mobile-nav-user-box {
            margin-top: 0.5rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .mobile-nav-user-box .user-name-tag {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            font-weight: 700;
            color: #ffffff;
        }
        .mobile-nav-logout-btn {
            width: 100%;
            text-align: center;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 0.75rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.88rem;
            cursor: pointer;
            transition: background 0.15s;
            min-height: var(--touch-target);
            line-height: calc(var(--touch-target) - 2px);
        }
        .mobile-nav-logout-btn:hover {
            background: rgba(239, 68, 68, 0.25);
            color: #ffffff;
        }

        .btn-theme-toggle {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-sm);
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #F8FAFC;
            cursor: pointer;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }
        .btn-theme-toggle:hover {
            background: rgba(255, 255, 255, 0.22);
            color: #FFFFFF;
        }

        .user-pill {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 6px 14px; border-radius: var(--radius-sm);
            background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.18);
            color: #ffffff; text-decoration: none; font-size: 0.85rem; font-weight: 600;
            transition: background 0.15s;
        }
        .user-pill:hover { background: rgba(255, 255, 255, 0.2); }

        .btn-nav-logout {
            background: none; border: none; color: #CBD5E1; cursor: pointer;
            font-size: 0.85rem; font-weight: 600; padding: 6px 10px;
            transition: color 0.15s;
        }
        .btn-nav-logout:hover { color: #f87171; }

        /* Container */
        .main-container { width: 100%; max-width: 1000px; margin: 2.5rem auto; padding: 0 1.5rem; flex: 1; }
        .header-section { margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; }
        .user-greeting { font-size: 1.85rem; font-weight: 800; color: var(--text-main); letter-spacing: -0.02em; }
        .user-sub { font-size: 0.92rem; color: var(--text-muted); margin-top: 4px; }

        /* Grid Cards */
        .dashboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem; }
        @media (max-width: 768px) { .dashboard-grid { grid-template-columns: 1fr; } }
        .dash-card {
            background: var(--surface);
            border: 1px solid var(--border); border-radius: var(--radius-lg);
            padding: 1.75rem; box-shadow: var(--shadow-card);
            display: flex; flex-direction: column; justify-content: space-between;
        }
        .card-label { font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 0.5rem; }
        .plan-title { font-size: 1.65rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 10px; }
        .plan-badge {
            font-size: 0.75rem; padding: 4px 10px; border-radius: 999px;
            background: var(--banner-bg); border: 1px solid var(--banner-border); color: var(--accent); font-weight: 700;
        }
        .usage-counter { font-size: 2.25rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.75rem; }
        .progress-bar-bg { width: 100%; height: 10px; background: var(--surface-alt); border-radius: 999px; overflow: hidden; margin-bottom: 0.75rem; border: 1px solid var(--border); }
        .progress-bar-fill { height: 100%; background: var(--primary); border-radius: 999px; transition: width 0.3s ease; }
        .usage-hint { font-size: 0.85rem; color: var(--text-muted); line-height: 1.4; }
        .btn-upgrade {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 0.75rem 1.25rem; border-radius: var(--radius-md); background: var(--primary);
            color: #fff; text-decoration: none; font-weight: 700; font-size: 0.9rem;
            margin-top: 1rem; align-self: flex-start; transition: background 0.15s;
        }
        .btn-upgrade:hover { background: var(--secondary); }

        /* History Table */
        .section-title { font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem; }
        .table-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-card);
        }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem; }
        th { padding: 0.9rem 1.25rem; background: var(--surface-alt); color: var(--text-muted); font-size: 0.78rem; text-transform: uppercase; font-weight: 700; border-bottom: 1px solid var(--border); }
        td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); color: var(--text-main); }
        tr:last-child td { border-bottom: none; }
        .status-badge {
            display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
        }
        .status-paid { background: var(--badge-paid-bg); color: var(--badge-paid-text); border: 1px solid var(--badge-paid-border); }
        .status-pending { background: var(--badge-pending-bg); color: var(--badge-pending-text); border: 1px solid var(--badge-pending-border); }
        .alert-success { background: var(--success-bg); border: 1px solid var(--success-border); color: var(--success-text); padding: 0.9rem 1.2rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; font-size: 0.9rem; }
        .alert-info { background: var(--banner-bg); border: 1px solid var(--banner-border); color: var(--banner-text); padding: 0.9rem 1.2rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; font-size: 0.9rem; }

        /* Mobile Transaction Cards & Responsive Helpers */
        .desktop-table-view { display: block; }
        .mobile-tx-list { display: none; }

        .mobile-tx-card {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0.875rem var(--radius-md);
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            transition: background-color 0.15s ease;
            min-height: var(--touch-target);
        }
        .mobile-tx-card:last-child {
            border-bottom: none;
        }
        .tx-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }
        .tx-card-plan {
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .tx-card-body {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.5rem;
        }
        .tx-card-amount {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.01em;
        }
        .tx-card-ref {
            font-size: 0.78rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            color: var(--text-muted);
            background: var(--surface-alt);
            padding: 3px 8px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            max-width: 140px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 0.72rem;
            padding: 2px 6px;
        }
        .tx-card-footer {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.75rem;
            color: var(--text-dim);
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 0.75rem 1rem;
            }
            .nav-menu {
                display: none !important;
            }
            .btn-mobile-nav-toggle {
                display: inline-flex;
            }
            .nav-user-actions .user-pill,
            .nav-user-actions .btn-nav-logout {
                display: none;
            }
            .main-container {
                margin: 1.25rem auto;
                padding: 0 1rem;
            }
            .header-section {
                flex-direction: column;
                align-items: stretch;
                gap: 0.85rem;
            }
            .header-section .btn-upgrade {
                width: 100%;
                text-align: center;
                margin-top: 0.25rem;
            }
            .user-greeting {
                font-size: clamp(1.35rem, 5.5vw, 1.65rem);
            }
            .user-sub {
                font-size: 0.86rem;
            }
            .dash-card {
                padding: 1.35rem 1.15rem;
            }
            .plan-title {
                font-size: 1.4rem;
            }
            .usage-counter {
                font-size: 1.9rem;
            }
            .btn-upgrade {
                width: 100%;
                text-align: center;
            }
        }

        @media (max-width: 640px) {
            .desktop-table-view {
                display: none !important;
            }
            .mobile-tx-list {
                display: block !important;
            }
            .table-card {
                border-radius: var(--radius-lg);
            }
        }
    </style>
</head>
<body>

{{-- SaaS Navbar --}}
<nav class="navbar">
    <a href="{{ route('converter.index') }}" class="nav-brand">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="#60A5FA" stroke="#FFFFFF" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <span>MsterCV</span>
        <span class="nav-brand-badge">V3</span>
    </a>
    <div class="nav-menu">
        <a href="{{ route('converter.index') }}" class="nav-item">PDF Tools</a>
        <a href="{{ route('separation.index') }}" class="nav-item">Color Separation</a>
        <a href="{{ route('upscaler.index') }}" class="nav-item">Upscaler</a>
        <a href="{{ route('pricing.index') }}" class="nav-item">Pricing</a>
        <a href="{{ route('dashboard') }}" class="nav-item active">Dashboard</a>
    </div>
    <div class="nav-user-actions">
        <button type="button" id="themeToggleBtn" class="btn-theme-toggle" onclick="toggleDarkMode()" title="Ganti Mode Gelap / Terang" aria-label="Ganti Mode">
            <svg class="theme-icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
            </svg>
            <svg class="theme-icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                <circle cx="12" cy="12" r="5"></circle>
                <line x1="12" y1="1" x2="12" y2="3"></line>
                <line x1="12" y1="21" x2="12" y2="23"></line>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                <line x1="1" y1="12" x2="3" y2="12"></line>
                <line x1="21" y1="12" x2="23" y2="12"></line>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
            </svg>
        </button>
        <button type="button" id="mobileNavToggleBtn" class="btn-mobile-nav-toggle" onclick="toggleMobileNav()" aria-label="Buka Menu Navigasi" aria-expanded="false">
            <svg class="hamburger-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
            <svg class="close-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        @if ($user->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="user-pill" style="border-color:rgba(234,179,8,0.35);color:#fde047;">
                🛡️ Admin Panel
            </a>
        @endif
        <a href="{{ route('dashboard') }}" class="user-pill">
            <span>{{ $user->name }}</span>
        </a>
        <form action="{{ route('logout') }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn-nav-logout" title="Keluar">Keluar</button>
        </form>
    </div>
</nav>

{{-- Mobile Navigation Drawer --}}
<div id="mobileNavPanel" class="mobile-nav-panel">
    <a href="{{ route('converter.index') }}" class="mobile-nav-link">
        <span>PDF Tools</span>
    </a>
    <a href="{{ route('separation.index') }}" class="mobile-nav-link">
        <span>Color Separation</span>
    </a>
    <a href="{{ route('upscaler.index') }}" class="mobile-nav-link">
        <span>Upscaler</span>
    </a>
    <a href="{{ route('pricing.index') }}" class="mobile-nav-link">
        <span>Pricing</span>
    </a>
    <a href="{{ route('dashboard') }}" class="mobile-nav-link active">
        <span>Dashboard</span>
    </a>
    <div class="mobile-nav-user-box">
        <div class="user-name-tag">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            <span>{{ $user->name }}</span>
        </div>
        @if ($user->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="mobile-nav-link" style="color:#fde047;border-color:rgba(234,179,8,0.3);">
                <span>🛡️ Admin Panel</span>
            </a>
        @endif
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="mobile-nav-logout-btn">Keluar dari Akun</button>
        </form>
    </div>
</div>

<div class="main-container">
    @if (session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif
    @if (session('status'))
        <div class="alert-info">{{ session('status') }}</div>
    @endif

    <div class="header-section">
        <div>
            <h1 class="user-greeting">Halo, {{ $user->name }}!</h1>
            <p class="user-sub">Kelola kuota konversi, paket langganan, dan riwayat pembayaran Anda.</p>
        </div>
        <a href="{{ route('converter.index') }}" class="btn-upgrade">
            <span>Mulai Konversi PDF</span> &rarr;
        </a>
    </div>

    <div class="dashboard-grid">
        {{-- Plan Card --}}
        <div class="dash-card">
            <div>
                <div class="card-label">Paket Langganan Aktif</div>
                <div class="plan-title">
                    <span>{{ $usageInfo['plan_name'] }}</span>
                    @if ($usageInfo['unlimited'])
                        <span class="plan-badge">Unlimited</span>
                    @endif
                </div>
                <p class="usage-hint">
                    @if ($activeSubscription && $activeSubscription->expires_at)
                        Masa aktif hingga: <strong>{{ $activeSubscription->expires_at->translatedFormat('d F Y, H:i') }}</strong>
                    @elseif ($usageInfo['unlimited'])
                        Akses tidak terbatas tanpa batas tanggal kedaluwarsa.
                    @else
                        Paket bawaan dengan jatah 3 konversi per hari kerja.
                    @endif
                </p>
            </div>
            @if (!$usageInfo['unlimited'])
                <a href="{{ route('pricing.index') }}" class="btn-upgrade">Tingkatkan Paket (Upgrade)</a>
            @endif
        </div>

        {{-- Quota Card --}}
        <div class="dash-card">
            <div>
                <div class="card-label">Penggunaan Kuota Hari Ini</div>
                @if ($usageInfo['unlimited'])
                    <div class="usage-counter">∞ Unlimited</div>
                    <p class="usage-hint">Anda bebas melakukan konversi dokumen PDF tanpa batasan kuota harian.</p>
                @else
                    <div class="usage-counter">
                        {{ $usageInfo['used_today'] }} <span style="font-size:1.25rem;color:var(--text-dim);">/ {{ $usageInfo['daily_limit'] }}</span>
                    </div>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: {{ $usageInfo['percentage'] }}%;"></div>
                    </div>
                    <p class="usage-hint">
                        Tersisa <strong>{{ $usageInfo['remaining'] }}</strong> konversi hari ini. Kuota direset otomatis setiap pergantian hari.
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- Recent Payments Table & Mobile Card List --}}
    <h2 class="section-title">Riwayat Transaksi Terakhir</h2>
    <div class="table-card">
        @if ($recentPayments->isEmpty())
            <div style="padding: 2.5rem 1.5rem; text-align: center; color: var(--text-dim); font-size: 0.95rem;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 0.75rem; display: block; opacity: 0.5;">
                    <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                    <line x1="2" y1="10" x2="22" y2="10"></line>
                </svg>
                Belum ada riwayat transaksi pembayaran.
            </div>
        @else
            {{-- Desktop Table View --}}
            <div class="desktop-table-view">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID / Referensi</th>
                            <th>Paket</th>
                            <th>Nominal</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentPayments as $payment)
                            <tr>
                                <td><code>{{ $payment->provider_reference }}</code></td>
                                <td>{{ $payment->metadata['plan_name'] ?? 'Langganan' }}</td>
                                <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                <td>
                                    @if ($payment->status === 'PAID')
                                        <span class="status-badge status-paid">Lunas</span>
                                    @else
                                        <span class="status-badge status-pending">{{ $payment->status }}</span>
                                    @endif
                                </td>
                                <td>{{ $payment->created_at->translatedFormat('d M Y, H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile Card List View --}}
            <div class="mobile-tx-list">
                @foreach ($recentPayments as $payment)
                    <div class="mobile-tx-card">
                        <div class="tx-card-header">
                            <div class="tx-card-plan">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                <span>{{ $payment->metadata['plan_name'] ?? 'Langganan' }}</span>
                            </div>
                            @if ($payment->status === 'PAID')
                                <span class="status-badge status-paid">Lunas</span>
                            @else
                                <span class="status-badge status-pending">{{ $payment->status }}</span>
                            @endif
                        </div>
                        <div class="tx-card-body">
                            <div class="tx-card-amount">Rp {{ number_format($payment->amount, 0, ',', '.') }}</div>
                            <div class="tx-card-ref" title="{{ $payment->provider_reference }}">{{ $payment->provider_reference }}</div>
                        </div>
                        <div class="tx-card-footer">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            <span>{{ $payment->created_at->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<script>
    function toggleDarkMode() {
        const current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
        updateThemeToggleIcons(next);
    }

    function updateThemeToggleIcons(theme) {
        const sunIcons = document.querySelectorAll('.theme-icon-sun');
        const moonIcons = document.querySelectorAll('.theme-icon-moon');
        if (theme === 'dark') {
            sunIcons.forEach(el => el.style.display = 'inline-block');
            moonIcons.forEach(el => el.style.display = 'none');
        } else {
            sunIcons.forEach(el => el.style.display = 'none');
            moonIcons.forEach(el => el.style.display = 'inline-block');
        }
    }

    function toggleMobileNav() {
        const panel = document.getElementById('mobileNavPanel');
        const btn = document.getElementById('mobileNavToggleBtn');
        if (!panel || !btn) return;
        const isOpen = panel.classList.toggle('open');
        btn.setAttribute('aria-expanded', isOpen);
        const hamburger = btn.querySelector('.hamburger-icon');
        const close = btn.querySelector('.close-icon');
        if (hamburger && close) {
            hamburger.style.display = isOpen ? 'none' : 'inline-block';
            close.style.display = isOpen ? 'inline-block' : 'none';
        }
    }

    document.addEventListener('click', (e) => {
        const panel = document.getElementById('mobileNavPanel');
        const btn = document.getElementById('mobileNavToggleBtn');
        if (!panel || !btn) return;
        if (panel.classList.contains('open') && !panel.contains(e.target) && !btn.contains(e.target)) {
            toggleMobileNav();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const panel = document.getElementById('mobileNavPanel');
            if (panel && panel.classList.contains('open')) {
                toggleMobileNav();
            }
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        const current = document.documentElement.getAttribute('data-theme') || 'light';
        updateThemeToggleIcons(current);
    });
</script>

</body>
</html>
