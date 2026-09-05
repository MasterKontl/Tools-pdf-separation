<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun | Tools DKV</title>
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
            --primary: #1E3A5F;
            --secondary: #274C77;
            --accent: #3B82F6;
            --accent-hover: #2563EB;
            --text-main: #0F172A;
            --text-muted: #64748B;
            --border: #E2E8F0;
            --banner-bg: #EFF6FF;
            --banner-border: #BFDBFE;
            --radius-md: 8px;
            --radius-lg: 12px;
            --shadow-card: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        [data-theme="dark"] {
            --bg-body: #0B1120;
            --surface: #1E293B;
            --primary: #3B82F6;
            --secondary: #2563EB;
            --accent: #60A5FA;
            --accent-hover: #93C5FD;
            --text-main: #F8FAFC;
            --text-muted: #94A3B8;
            --border: #334155;
            --banner-bg: #1E293B;
            --banner-border: #3B82F6;
            --shadow-card: 0 4px 20px rgba(0, 0, 0, 0.4);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            transition: background-color 0.2s ease, color 0.2s ease;
            position: relative;
        }
        .top-nav-toggle {
            position: fixed;
            top: 1.25rem;
            right: 1.5rem;
            z-index: 100;
        }
        .btn-theme-toggle {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-theme-toggle:hover {
            border-color: var(--accent);
            color: var(--accent);
        }
        .auth-container { width: 100%; max-width: 440px; }
        .logo-box { text-align: center; margin-bottom: 1.75rem; }
        .logo-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 12px; border-radius: 999px;
            background: var(--banner-bg); border: 1px solid var(--banner-border);
            color: var(--accent); font-size: 0.78rem; font-weight: 700; text-transform: uppercase;
            margin-bottom: 0.75rem; text-decoration: none;
        }
        .auth-title { font-size: 1.85rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.35rem; letter-spacing: -0.02em; }
        .auth-sub { font-size: 0.9rem; color: var(--text-muted); }
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2.25rem 2rem;
            box-shadow: var(--shadow-card);
        }
        .form-group { margin-bottom: 1.25rem; }
        .form-label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.5rem; }
        .form-input {
            width: 100%; padding: 0.85rem 1rem;
            background: var(--bg-body); border: 1px solid var(--border);
            border-radius: var(--radius-md); color: var(--text-main); font-family: inherit; font-size: 0.92rem;
            outline: none; transition: border-color 0.15s;
        }
        .form-input:focus { border-color: var(--accent); }
        .btn-submit {
            width: 100%; padding: 0.85rem; border-radius: var(--radius-md); border: none;
            background: var(--primary); color: #fff; font-family: inherit;
            font-size: 1rem; font-weight: 700; cursor: pointer; margin-top: 0.5rem;
            transition: background 0.15s;
        }
        .btn-submit:hover { background: var(--secondary); }
        .alert-error {
            background: #FEF2F2; border: 1px solid #FECACA;
            color: #991B1B; padding: 0.85rem 1rem; border-radius: var(--radius-md);
            font-size: 0.85rem; margin-bottom: 1.25rem;
        }
        .auth-footer { text-align: center; margin-top: 1.75rem; font-size: 0.88rem; color: var(--text-muted); }
        .link-text { color: var(--accent); text-decoration: none; font-weight: 600; }
        .link-text:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="top-nav-toggle">
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
</div>

<div class="auth-container">
    <div class="logo-box">
        <a href="{{ route('converter.index') }}" class="logo-badge">
            TOOLS DKV &bull; V3 Platform
        </a>
        <h1 class="auth-title">Daftar Akun Baru</h1>
        <p class="auth-sub">Dapatkan 3 konversi PDF gratis setiap hari dan akses dashboard.</p>
    </div>

    <div class="card">
        @if ($errors->any())
            <div class="alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('register.attempt') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label" for="name">Nama Lengkap</label>
                <input type="text" name="name" id="name" class="form-input" value="{{ old('name') }}" required autofocus autocomplete="name">
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" name="email" id="email" class="form-input" value="{{ old('email') }}" required autocomplete="email">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Kata Sandi (Minimal 8 Karakter)</label>
                <input type="password" name="password" id="password" class="form-input" required autocomplete="new-password">
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-input" required autocomplete="new-password">
            </div>

            <button type="submit" class="btn-submit">Daftar & Dapatkan Kuota Gratis</button>
        </form>
    </div>

    <div class="auth-footer">
        Sudah memiliki akun? <a href="{{ route('login') }}" class="link-text">Masuk di sini</a>
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

    document.addEventListener('DOMContentLoaded', () => {
        const current = document.documentElement.getAttribute('data-theme') || 'light';
        updateThemeToggleIcons(current);
    });
</script>

</body>
</html>
