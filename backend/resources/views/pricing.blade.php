<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilihan Paket & Harga | Tools DKV</title>
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

        .btn-nav-login {
            padding: 7px 16px; border-radius: var(--radius-sm); font-size: 0.85rem; font-weight: 700;
            background: var(--accent); color: #ffffff; text-decoration: none;
            transition: background 0.15s;
        }
        .btn-nav-login:hover { background: var(--accent-hover); }

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
            width: 100%; padding: 0.85rem; border-radius: var(--radius-md); border: none;
            background: var(--primary); color: #fff; font-family: inherit; font-size: 0.95rem;
            font-weight: 700; cursor: pointer; text-align: center; text-decoration: none;
            display: block; transition: background 0.15s;
        }
        .btn-checkout:hover { background: var(--secondary); }
        .btn-current-plan {
            background: var(--surface-alt); border: 1px solid var(--border);
            color: var(--text-muted); cursor: default;
        }
        .btn-current-plan:hover { background: var(--surface-alt); }
    </style>
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
    </div>
</nav>

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

    document.addEventListener('DOMContentLoaded', () => {
        const current = document.documentElement.getAttribute('data-theme') || 'light';
        updateThemeToggleIcons(current);
    });
</script>

</body>
</html>
