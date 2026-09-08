<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seo['title'] }}</title>
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
            --radius-lg: 12px;
            --radius-md: 8px;
            --radius-sm: 6px;
            --shadow-card: 0 4px 16px rgba(30, 58, 95, 0.08);
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
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: var(--radius-sm);
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: #ffffff;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: background 0.15s;
        }
        .user-pill:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .btn-nav-logout {
            background: transparent;
            border: none;
            color: #CBD5E1;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            padding: 6px 10px;
            transition: color 0.15s;
        }
        .btn-nav-logout:hover { color: #f87171; }

        .btn-nav-login {
            padding: 7px 16px; border-radius: var(--radius-sm); font-size: 0.85rem; font-weight: 700;
            background: var(--accent); color: #ffffff; text-decoration: none;
            transition: background 0.15s;
        }
        .btn-nav-login:hover { background: var(--accent-hover); }

        /* Mobile Nav Toggle Button */
        .btn-mobile-nav-toggle {
            display: none;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: var(--radius-sm);
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #FFFFFF;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-mobile-nav-toggle:hover {
            background: rgba(255, 255, 255, 0.22);
        }

        /* Mobile Drawer Panel */
        .mobile-nav-panel {
            display: none;
            position: fixed;
            top: 61px;
            left: 0;
            right: 0;
            width: 100%;
            background: var(--nav-bg);
            border-bottom: 1px solid var(--nav-border);
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.4);
            padding: 1rem 1.25rem 1.5rem;
            z-index: 9999;
            flex-direction: column;
            gap: 1rem;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        .mobile-nav-panel.open {
            display: flex;
            animation: mobileNavSlide 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes mobileNavSlide {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .mobile-nav-links {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .mobile-nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-md);
            color: var(--nav-item);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            transition: all 0.15s ease;
        }
        .mobile-nav-item:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #FFFFFF;
        }
        .mobile-nav-item.active {
            background: rgba(59, 130, 246, 0.2);
            color: #60A5FA;
            border-left: 3px solid #3B82F6;
        }
        .mobile-nav-item svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }
        .mobile-user-section {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 1rem;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .mobile-user-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.65rem 0.85rem;
            border-radius: var(--radius-md);
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .mobile-user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--accent);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.95rem;
            flex-shrink: 0;
        }
        .mobile-user-meta {
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .mobile-user-name {
            font-weight: 700;
            font-size: 0.9rem;
            color: #FFFFFF;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .mobile-user-email {
            font-size: 0.78rem;
            color: #94A3B8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .mobile-btn-logout {
            width: 100%;
            padding: 0.65rem;
            border-radius: var(--radius-sm);
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #FCA5A5;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            text-align: center;
            transition: background 0.15s ease;
        }
        .mobile-btn-logout:hover {
            background: rgba(239, 68, 68, 0.3);
            color: #FFFFFF;
        }
        .mobile-auth-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .mobile-btn-primary {
            display: block;
            width: 100%;
            padding: 0.75rem;
            border-radius: var(--radius-md);
            background: var(--accent);
            color: #FFFFFF;
            text-align: center;
            font-weight: 700;
            font-size: 0.9rem;
            text-decoration: none;
            transition: background 0.15s;
        }
        .mobile-btn-primary:hover {
            background: var(--accent-hover);
        }
        .mobile-btn-secondary {
            display: block;
            width: 100%;
            padding: 0.75rem;
            border-radius: var(--radius-md);
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #FFFFFF;
            text-align: center;
            font-weight: 700;
            font-size: 0.9rem;
            text-decoration: none;
            transition: background 0.15s;
        }
        .mobile-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.16);
        }

        .main-container { width: 100%; max-width: 1160px; margin: 2.5rem auto; padding: 0 1.5rem; flex: 1; }
        .pricing-header { text-align: center; margin-bottom: 3rem; }
        .badge-tag {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 12px; border-radius: 999px;
            background: var(--banner-bg); border: 1px solid var(--banner-border);
            color: var(--accent); font-size: 0.78rem; font-weight: 700; text-transform: uppercase; margin-bottom: 0.85rem;
        }
        .pricing-title { font-size: 2.35rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem; letter-spacing: -0.03em; }
        .pricing-sub { font-size: 1rem; color: var(--text-muted); max-width: 600px; margin: 0 auto; line-height: 1.5; }

        /* Pricing Grid */
        .plans-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem; }
        .plan-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2rem 1.75rem;
            box-shadow: var(--shadow-card);
            display: flex; flex-direction: column; justify-content: space-between; position: relative;
            transition: border-color 0.15s;
        }
        .plan-card:hover { border-color: var(--accent); }
        .plan-card.popular {
            border-color: var(--accent);
            border-width: 2px;
        }
        .popular-badge {
            position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
            background: var(--accent);
            color: #fff; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;
            padding: 4px 12px; border-radius: 999px; letter-spacing: 0.05em;
        }
        .card-plan-name { font-size: 1.35rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.4rem; }
        .card-plan-desc { font-size: 0.85rem; color: var(--text-muted); min-height: 40px; margin-bottom: 1.25rem; line-height: 1.4; }
        .card-price-box { margin-bottom: 1.5rem; }
        .card-price { font-size: 2.25rem; font-weight: 800; color: var(--text-main); }
        .card-period { font-size: 0.85rem; color: var(--text-dim); }
        .features-list { list-style: none; margin-bottom: 1.75rem; flex: 1; }
        .feature-item { display: flex; align-items: center; gap: 10px; font-size: 0.88rem; color: var(--text-main); margin-bottom: 0.8rem; }
        .feature-item svg { color: #16A34A; flex-shrink: 0; }
.btn-checkout {
            width: 100%; padding: 0.85rem var(--radius-md); border: none;
            background: var(--primary); color: #fff; font-family: inherit; font-size: 0.95rem;
            font-weight: 700; cursor: pointer; text-align: center; text-decoration: none;
            display: block; transition: background 0.15s;
            min-height: var(--touch-target);
            line-height: calc(var(--touch-target) - 2px);
        }
        .btn-checkout:hover { background: var(--secondary); }
        .btn-current-plan {
            background: var(--surface-alt); border: 1px solid var(--border);
            color: var(--text-muted); cursor: default;
        }
        .btn-current-plan:hover { background: var(--surface-alt); }

        @media (max-width: 768px) {
            .navbar {
                padding: 0.75rem 1rem;
            }
            .nav-menu {
                display: none;
            }
            .nav-user-actions .user-pill,
            .nav-user-actions form,
            .nav-user-actions .btn-nav-login {
                display: none;
            }
            .btn-mobile-nav-toggle {
                display: inline-flex;
            }
            .main-container {
                padding: 0 1rem;
                margin: 1.5rem auto;
            }
            .pricing-title {
                font-size: clamp(1.6rem, 5vw, 2.1rem);
            }
            .pricing-sub {
                font-size: 0.9rem;
            }
            .plans-grid {
                grid-template-columns: 1fr;
                gap: 1.25rem;
            }
            .plan-card {
                padding: 1.5rem 1.25rem;
            }
            .popular-badge {
                padding: 3px 8px; font-size: 0.65rem;
            }
            .card-plan-name {
                font-size: 1.15rem;
            }
            .card-plan-desc {
                font-size: 0.8rem;
                min-height: auto;
                margin-bottom: 1rem;
            }
            .card-price {
                font-size: 1.8rem;
            }
            .card-period {
                font-size: 0.75rem;
            }
            .features-list {
                margin-bottom: 1.25rem;
            }
            .feature-item {
                font-size: 0.82rem;
                margin-bottom: 0.6rem;
            }
            .feature-item svg {
                width: 14px;
                height: 14px;
            }
            .btn-checkout {
                padding: 0.75rem var(--radius-md);
                font-size: 0.88rem;
                min-height: var(--touch-target);
                line-height: calc(var(--touch-target) - 2px);
            }
        }
    </style>
    @include('partials.seo-tags')
</head>
<body>

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
        <a href="{{ route('pricing.index') }}" class="nav-item active">Pricing</a>
        @auth
            <a href="{{ route('dashboard') }}" class="nav-item">Dashboard</a>
        @endauth
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
        @auth
            @if ($user && $user->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="user-pill" style="border-color:rgba(234,179,8,0.35);color:#fde047;">
                    🛡️ Admin Panel
                </a>
            @endif
            @if ($user)
                <a href="{{ route('dashboard') }}" class="user-pill">
                    <span>{{ $user->name }}</span>
                </a>
            @endif
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn-nav-logout" title="Keluar">Keluar</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="btn-nav-login">Masuk / Daftar</a>
        @endauth

        <button type="button" id="mobileNavToggleBtn" class="btn-mobile-nav-toggle" onclick="toggleMobileNav()" aria-label="Toggle Menu" aria-expanded="false">
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
    </div>
</nav>

{{-- Mobile Navigation Drawer --}}
<div id="mobileNavPanel" class="mobile-nav-panel">
    <div class="mobile-nav-links">
        <a href="{{ route('converter.index') }}" class="mobile-nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <span>PDF Tools (Converter)</span>
        </a>
        <a href="{{ route('separation.index') }}" class="mobile-nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
            <span>Color Separation</span>
        </a>
        <a href="{{ route('upscaler.index') }}" class="mobile-nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
            <span>Upscaler</span>
        </a>
        <a href="{{ route('pricing.index') }}" class="mobile-nav-item active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><line x1="12" y1="6" x2="12" y2="8"/><line x1="12" y1="16" x2="12" y2="18"/></svg>
            <span>Pricing & Kuota</span>
        </a>
        @auth
            <a href="{{ route('dashboard') }}" class="mobile-nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                <span>Dashboard User</span>
            </a>
        @endauth
    </div>

    <div class="mobile-user-section">
        @auth
            @if ($user)
                <div class="mobile-user-card">
                    <div class="mobile-user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                    <div class="mobile-user-meta">
                        <div class="mobile-user-name">{{ $user->name }}</div>
                        <div class="mobile-user-email">{{ $user->email }}</div>
                    </div>
                </div>
                @if ($user->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="mobile-btn-secondary" style="border-color:rgba(234,179,8,0.35);color:#fde047;">
                        🛡️ Buka Admin Panel
                    </a>
                @endif
                <form action="{{ route('logout') }}" method="POST" style="width:100%;">
                    @csrf
                    <button type="submit" class="mobile-btn-logout">Keluar Akun</button>
                </form>
            @endif
        @else
            <div class="mobile-auth-actions">
                <a href="{{ route('login') }}" class="mobile-btn-primary">Masuk Akun</a>
                <a href="{{ route('register') }}" class="mobile-btn-secondary">Daftar</a>
            </div>
        @endauth
    </div>
</div>

<div class="main-container">
    <div class="pricing-header">
        <div class="badge-tag">PILIHAN PAKET TOOLS DKV</div>
        <h1 class="pricing-title">Tingkatkan Kuota Konversi Anda</h1>
        <p class="pricing-sub">Pilih paket yang paling sesuai dengan ritme kerja desain, tugas kuliah, atau kebutuhan studio sablon & DKV Anda.</p>
    </div>

    <div class="plans-grid">
        @foreach ($plans as $plan)
            @php
                $isCurrent = ($user && $user->plan_id === $plan->id) || (!$user && $plan->slug === 'free');
                $isPopular = ($plan->slug === 'student' || $plan->slug === 'pro');
            @endphp
            <div class="plan-card {{ $isPopular ? 'popular' : '' }}">
                @if ($isPopular)
                    <div class="popular-badge">Paling Populer</div>
                @endif
                <div>
                    <h3 class="card-plan-name">{{ $plan->name }}</h3>
                    <p class="card-plan-desc">{{ $plan->description }}</p>

                    <div class="card-price-box">
                        @if ($plan->price <= 0)
                            <span class="card-price">Gratis</span>
                            <span class="card-period">/ selamanya</span>
                        @else
                            <span class="card-price">Rp {{ number_format($plan->price, 0, ',', '.') }}</span>
                            <span class="card-period">/ 30 hari</span>
                        @endif
                    </div>

                    <ul class="features-list">
                        <li class="feature-item">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span><strong>{{ $plan->unlimited ? '∞ Unlimited' : $plan->daily_limit . ' Konversi' }}</strong> per hari</span>
                        </li>
                        <li class="feature-item">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @if (in_array($plan->slug, ['pro', 'unlimited']))
                                <span>Resolusi 150 / 300 / <strong>600 DPI Ultra Presisi</strong></span>
                            @else
                                <span>Resolusi 150 / 300 DPI (Standar Cetak)</span>
                            @endif
                        </li>
                        <li class="feature-item">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Format PNG Lossless & JPG</span>
                        </li>
                        <li class="feature-item">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Multi-page ZIP Bundling</span>
                        </li>
                        <li class="feature-item">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Screen Print Separations Engine</span>
                        </li>
                    </ul>
                </div>

                <div>
                    @if ($isCurrent)
                        <button type="button" class="btn-checkout btn-current-plan" disabled>Paket Anda Saat Ini</button>
                    @elseif ($user)
                        <form action="{{ route('pricing.checkout', ['plan' => $plan->id]) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-checkout">
                                {{ $plan->price <= 0 ? 'Pilih Paket' : 'Langganan via Pakasir' }}
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn-checkout">Masuk untuk Memilih</a>
                    @endif
                </div>
            </div>
        @endforeach
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
