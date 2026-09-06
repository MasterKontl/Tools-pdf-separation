<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Image Upscaler | Tools DKV</title>
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
            --bg-body: #F8FAFC; --bg-card: #FFFFFF; --bg-card-border: #E2E8F0; --border: #E2E8F0;
            --primary: #1E3A5F; --secondary: #274C77; --accent: #3B82F6; --accent-hover: #2563EB;
            --text-main: #0F172A; --text-muted: #64748B; --text-dim: #94A3B8;
            --surface-alt: #F1F5F9; --surface-hover: #F1F5F9;
            --nav-bg: #1E3A5F; --nav-border: #274C77; --nav-item: #CBD5E1; --nav-item-active: #FFFFFF;
            --banner-bg: #EFF6FF; --banner-border: #BFDBFE; --banner-text: #1E3A5F;
            --dropzone-bg: #F8FAFC; --dropzone-border: #CBD5E1;
            --danger-bg: #FEF2F2; --danger-border: #FECACA; --danger-text: #DC2626;
            --success-bg: #F0FDF4; --success-border: #BBF7D0; --success-text: #16A34A;
            --radius-lg: 14px; --radius-md: 10px; --radius-sm: 6px;
            --shadow-card: 0 4px 16px rgba(30, 58, 95, 0.08);
            --modal-overlay: rgba(15, 23, 42, 0.65);
        }
        [data-theme="dark"] {
            --bg-body: #0B1120; --bg-card: #1E293B; --bg-card-border: #334155; --border: #334155;
            --primary: #3B82F6; --secondary: #2563EB; --accent: #60A5FA; --accent-hover: #93C5FD;
            --text-main: #F8FAFC; --text-muted: #94A3B8; --text-dim: #64748B;
            --surface-alt: #0F172A; --surface-hover: #334155;
            --nav-bg: #0A0F1D; --nav-border: #1E293B; --nav-item: #94A3B8; --nav-item-active: #FFFFFF;
            --banner-bg: #1E293B; --banner-border: #3B82F6; --banner-text: #93C5FD;
            --dropzone-bg: #0F172A; --dropzone-border: #334155;
            --danger-bg: #450A0A; --danger-border: #7F1D1D; --danger-text: #FCA5A5;
            --success-bg: #052E16; --success-border: #14532D; --success-text: #86EFAC;
            --shadow-card: 0 4px 20px rgba(0, 0, 0, 0.4);
            --modal-overlay: rgba(0, 0, 0, 0.75);
        }
        html, body { width: 100%; max-width: 100%; overflow-x: clip; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); color: var(--text-main); min-height: 100vh; display: flex; flex-direction: column; transition: background-color 0.2s ease, color 0.2s ease; }

        /* Navbar */
        .navbar { display: flex; align-items: center; justify-content: space-between; padding: 1rem 2rem; border-bottom: 1px solid var(--nav-border); background: var(--nav-bg); position: sticky; top: 0; z-index: 50; width: 100%; }
        .nav-brand { display: flex; align-items: center; gap: 8px; font-size: 1.15rem; font-weight: 800; color: #ffffff; text-decoration: none; flex-shrink: 0; }
        .nav-brand-badge { font-size: 0.72rem; padding: 2px 7px; border-radius: 4px; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); color: #ffffff; font-weight: 700; }
        .nav-menu { display: flex; align-items: center; gap: 20px; }
        .nav-item { color: var(--nav-item); text-decoration: none; font-size: 0.9rem; font-weight: 600; transition: color 0.15s; }
        .nav-item:hover, .nav-item.active { color: var(--nav-item-active); }
        .nav-user-actions { display: flex; align-items: center; gap: 10px; }
        .btn-theme-toggle { background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); border-radius: var(--radius-sm); width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; color: #F8FAFC; cursor: pointer; transition: all 0.15s ease; flex-shrink: 0; }
        .btn-theme-toggle:hover { background: rgba(255,255,255,0.22); color: #FFFFFF; }
        .btn-mobile-nav-toggle { display: none; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); border-radius: var(--radius-sm); width: 38px; height: 38px; align-items: center; justify-content: center; color: #F8FAFC; cursor: pointer; transition: all 0.15s ease; flex-shrink: 0; }
        .btn-mobile-nav-toggle:hover { background: rgba(255,255,255,0.22); }
        .btn-nav-login { padding: 7px 16px; border-radius: var(--radius-sm); font-size: 0.85rem; font-weight: 700; background: var(--accent); color: #ffffff; text-decoration: none; transition: background 0.15s; white-space: nowrap; }
        .btn-nav-login:hover { background: var(--accent-hover); }
        .user-pill { display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; border-radius: var(--radius-sm); background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.18); color: #ffffff; text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: background 0.15s; white-space: nowrap; }
        .user-pill:hover { background: rgba(255,255,255,0.2); }
        .btn-nav-logout { background: none; border: none; color: #CBD5E1; cursor: pointer; font-size: 0.85rem; font-weight: 600; padding: 6px 10px; transition: color 0.15s; white-space: nowrap; }
        .btn-nav-logout:hover { color: #f87171; }

        /* Mobile Nav */
        .mobile-nav-panel { display: none; position: fixed; top: 61px; left: 0; right: 0; background: var(--nav-bg); border-bottom: 1px solid var(--nav-border); padding: 1.25rem 1.25rem 1.5rem; z-index: 49; box-shadow: 0 16px 32px rgba(0,0,0,0.45); backdrop-filter: blur(12px); transform: translateY(-8px); opacity: 0; pointer-events: none; transition: transform 0.22s cubic-bezier(0.16,1,0.3,1), opacity 0.2s ease; }
        .mobile-nav-panel.open { transform: translateY(0); opacity: 1; pointer-events: auto; }
        .mobile-nav-links { display: flex; flex-direction: column; gap: 6px; margin-bottom: 1.25rem; }
        .mobile-nav-item { display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: var(--radius-sm); color: var(--nav-item); text-decoration: none; font-size: 0.95rem; font-weight: 600; transition: all 0.15s ease; }
        .mobile-nav-item:hover, .mobile-nav-item.active { color: #FFFFFF; background: rgba(255,255,255,0.08); }
        .mobile-nav-item.active { color: var(--accent); background: var(--banner-bg); font-weight: 700; }
        .mobile-nav-item svg { width: 18px; height: 18px; flex-shrink: 0; }
        .mobile-user-section { border-top: 1px solid var(--nav-border); padding-top: 1.15rem; display: flex; flex-direction: column; gap: 10px; }
        .mobile-user-card { display: flex; align-items: center; gap: 12px; padding: 10px 12px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: var(--radius-md); }
        .mobile-user-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--primary); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem; flex-shrink: 0; }
        .mobile-user-meta { overflow: hidden; flex: 1; }
        .mobile-user-name { font-weight: 700; font-size: 0.9rem; color: #FFFFFF; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .mobile-user-email { font-size: 0.78rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .mobile-auth-actions { display: flex; gap: 8px; }
        .mobile-btn-primary { flex: 1; padding: 10px 14px; border-radius: var(--radius-sm); background: var(--primary); color: #FFFFFF; font-weight: 700; font-size: 0.88rem; text-align: center; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 6px; }
        .mobile-btn-secondary { flex: 1; padding: 10px 14px; border-radius: var(--radius-sm); background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); color: #FFFFFF; font-weight: 600; font-size: 0.88rem; text-align: center; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; }
        .mobile-btn-logout { width: 100%; padding: 10px; border-radius: var(--radius-sm); background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.25); color: #F87171; font-weight: 700; font-size: 0.88rem; cursor: pointer; transition: background 0.15s; }
        .mobile-btn-logout:hover { background: rgba(239,68,68,0.2); }

        /* Main */
        .container { width: 100%; max-width: 680px; margin: 2.5rem auto; padding: 0 1rem; flex: 1; }
        .header { text-align: center; margin-bottom: 2rem; }
        .title { font-size: clamp(1.8rem, 5vw, 2.3rem); font-weight: 800; letter-spacing: -0.025em; color: var(--text-main); margin-bottom: 0.5rem; word-break: break-word; }
        .subtitle { color: var(--text-muted); font-size: clamp(0.85rem, 3vw, 0.95rem); line-height: 1.5; max-width: 100%; }
        .method-note { display: inline-flex; align-items: center; gap: 6px; background: var(--banner-bg); border: 1px solid var(--banner-border); color: var(--banner-text); padding: 5px 12px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; margin-top: 0.75rem; }

        /* Usage Banner */
        .usage-banner { display: flex; align-items: center; justify-content: space-between; background: var(--banner-bg); border: 1px solid var(--banner-border); border-radius: var(--radius-md); padding: 0.85rem 1.25rem; margin-bottom: 1.5rem; font-size: 0.88rem; color: var(--banner-text); }
        .usage-badge { display: inline-flex; align-items: center; gap: 8px; font-weight: 700; }
        .usage-badge-icon { width: 8px; height: 8px; border-radius: 50%; background: var(--accent); }
        .upgrade-link { color: var(--accent); text-decoration: none; font-weight: 700; font-size: 0.84rem; }
        .upgrade-link:hover { text-decoration: underline; }

        .card { background: var(--bg-card); border: 1px solid var(--bg-card-border); border-radius: var(--radius-lg); padding: 2.25rem 2rem; box-shadow: var(--shadow-card); }
        .alert-error { background: var(--danger-bg); border: 1px solid var(--danger-border); color: var(--danger-text); padding: 0.9rem 1.2rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; font-size: 0.9rem; display: flex; align-items: flex-start; gap: 10px; }

        /* Dropzone */
        .drop-zone { border: 2px dashed var(--dropzone-border); border-radius: var(--radius-md); padding: 2.5rem 1rem; text-align: center; transition: all 0.2s ease; background: var(--dropzone-bg); cursor: pointer; min-height: 180px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem; }
        .drop-zone:hover { border-color: var(--accent); background: var(--banner-bg); }
        .drop-zone.dragover { border-color: var(--accent); background: var(--banner-bg); }
        .drop-zone-icon { color: var(--accent); opacity: 0.6; }
        .drop-text-primary { font-size: 0.96rem; font-weight: 600; color: var(--text-main); }
        .drop-text-secondary { font-size: 0.8rem; color: var(--text-muted); }
        .btn-choose { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: var(--primary); color: #fff; border: none; border-radius: var(--radius-sm); font-family: inherit; font-size: 0.95rem; font-weight: 700; cursor: pointer; transition: background 0.15s; }
        .btn-choose:hover { background: var(--secondary); }

        /* Preview */
        .preview-section { display: none; margin-top: 1.5rem; }
        .preview-section.visible { display: block; }
        .preview-image-box { width: 100%; max-height: 320px; border-radius: var(--radius-md); overflow: hidden; background: var(--surface-alt); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; }
        .preview-image-box img { max-width: 100%; max-height: 320px; object-fit: contain; display: block; }
        .file-meta { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.25rem; }
        .meta-tag { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; background: var(--surface-alt); border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 0.78rem; font-weight: 600; color: var(--text-muted); }
        .meta-tag strong { color: var(--text-main); }

        /* Scale Selector */
        .scale-section { margin-bottom: 1.5rem; }
        .section-label { display: block; font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 0.65rem; }
        .scale-group { display: flex; gap: 8px; }
        .scale-option { flex: 1; position: relative; }
        .scale-option input[type="radio"] { position: absolute; opacity: 0; cursor: pointer; inset: 0; width: 100%; height: 100%; z-index: 2; }
        .scale-btn { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 12px 8px; border-radius: var(--radius-sm); cursor: pointer; transition: all 0.15s ease; text-align: center; background: var(--surface-alt); border: 1px solid var(--border); min-height: 48px; }
        .scale-title { font-size: 1.1rem; font-weight: 800; color: var(--text-muted); }
        .scale-desc { font-size: 0.7rem; color: var(--text-dim); margin-top: 2px; }
        .scale-option input[type="radio"]:checked + .scale-btn { background: var(--primary); border-color: var(--primary); }
        .scale-option input[type="radio"]:checked + .scale-btn .scale-title { color: #ffffff; }
        .scale-option input[type="radio"]:checked + .scale-btn .scale-desc { color: rgba(255,255,255,0.8); }

        /* Buttons */
        .btn-upscale { width: 100%; padding: 1rem; border-radius: var(--radius-md); border: none; background: var(--primary); color: #ffffff; font-size: 1rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.15s; min-height: 48px; }
        .btn-upscale:hover:not(:disabled) { background: var(--secondary); }
        .btn-upscale:disabled { opacity: 0.5; cursor: not-allowed; }
        .btn-remove { background: none; border: none; color: var(--text-dim); cursor: pointer; padding: 6px; font-size: 0.82rem; font-weight: 600; display: flex; align-items: center; gap: 4px; transition: color 0.15s; }
        .btn-remove:hover { color: var(--danger-text); }

        /* Processing */
        .processing-indicator { display: none; margin-top: 1.25rem; background: var(--surface-alt); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1.5rem; text-align: center; }
        .processing-indicator.visible { display: block; }
        .spinner { width: 28px; height: 28px; border: 3px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 0.75rem; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Result */
        .result-section { display: none; margin-top: 1.5rem; }
        .result-section.visible { display: block; }
        .result-card { background: var(--success-bg); border: 1px solid var(--success-border); border-radius: var(--radius-lg); padding: 1.5rem; }
        .result-title { font-size: 1rem; font-weight: 700; color: var(--success-text); margin-bottom: 1rem; display: flex; align-items: center; gap: 8px; }
        .result-preview { width: 100%; max-height: 360px; border-radius: var(--radius-md); overflow: hidden; background: var(--bg-card); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; }
        .result-preview img { max-width: 100%; max-height: 360px; object-fit: contain; display: block; }
        .result-meta { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.25rem; }
        .btn-download { width: 100%; padding: 0.85rem; border-radius: var(--radius-md); border: none; background: var(--success-text); color: #ffffff; font-size: 0.95rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: opacity 0.15s; text-decoration: none; min-height: 48px; }
        .btn-download:hover { opacity: 0.9; }
        .btn-new { width: 100%; padding: 0.75rem; border-radius: var(--radius-md); background: var(--surface-alt); border: 1px solid var(--border); color: var(--text-muted); font-size: 0.88rem; font-weight: 600; cursor: pointer; margin-top: 0.75rem; transition: background 0.15s; min-height: 44px; }
        .btn-new:hover { background: var(--surface-hover); color: var(--text-main); }

        .footer { margin-top: 2.5rem; text-align: center; font-size: 0.8rem; color: var(--text-muted); padding-bottom: 2rem; }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar { padding: 0.75rem 1rem; }
            .nav-menu { display: none !important; }
            .nav-user-actions .user-pill, .nav-user-actions .btn-nav-logout, .nav-user-actions .btn-nav-login { display: none !important; }
            .btn-mobile-nav-toggle { display: inline-flex !important; }
            .mobile-nav-panel { display: block; top: 57px; }
            .container { padding: 0 0.85rem; margin: 1.25rem auto 2rem; width: 100%; max-width: 100%; }
            .card { padding: 1.35rem 1rem; border-radius: var(--radius-md); }
            .usage-banner { flex-direction: column; align-items: stretch; gap: 8px; padding: 0.85rem 1rem; }
            .upgrade-link { align-self: flex-start; }
        }
        @media (max-width: 480px) {
            .preview-image-box { max-height: 220px; }
            .preview-image-box img { max-height: 220px; }
            .result-preview { max-height: 260px; }
            .result-preview img { max-height: 260px; }
        }
    </style>
</head>
<body>

{{-- Navbar --}}
<nav class="navbar">
    <a href="{{ route('converter.index') }}" class="nav-brand">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="#60A5FA" stroke="#FFFFFF" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <span>MsterCV</span>
        <span class="nav-brand-badge">V3</span>
    </a>
    <div class="nav-menu">
        <a href="{{ route('converter.index') }}" class="nav-item">PDF Tools</a>
        <a href="{{ route('separation.index') }}" class="nav-item">Color Separation</a>
        <a href="{{ route('upscaler.index') }}" class="nav-item active">Upscaler</a>
        <a href="{{ route('pricing.index') }}" class="nav-item">Pricing</a>
        @auth
            <a href="{{ route('dashboard') }}" class="nav-item">Dashboard</a>
        @endauth
    </div>
    <div class="nav-user-actions">
        <button type="button" id="themeToggleBtn" class="btn-theme-toggle" onclick="toggleDarkMode()" title="Ganti Mode" aria-label="Ganti Mode">
            <svg class="theme-icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
            <svg class="theme-icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
        </button>
        @auth
            @if ($user->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="user-pill" style="border-color:rgba(234,179,8,0.35);color:#fde047;">Admin</a>
            @endif
            <a href="{{ route('dashboard') }}" class="user-pill"><span>{{ $user->name }}</span></a>
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">@csrf<button type="submit" class="btn-nav-logout" title="Keluar">Keluar</button></form>
        @else
            <a href="{{ route('login') }}" class="btn-nav-login">Masuk / Daftar</a>
        @endauth
        <button type="button" id="mobileNavToggleBtn" class="btn-mobile-nav-toggle" onclick="toggleMobileNav()" aria-label="Toggle Menu" aria-expanded="false">
            <svg class="hamburger-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            <svg class="close-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </div>
</nav>

{{-- Mobile Nav --}}
<div id="mobileNavPanel" class="mobile-nav-panel">
    <div class="mobile-nav-links">
        <a href="{{ route('converter.index') }}" class="mobile-nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <span>PDF Tools</span>
        </a>
        <a href="{{ route('separation.index') }}" class="mobile-nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
            <span>Color Separation</span>
        </a>
        <a href="{{ route('upscaler.index') }}" class="mobile-nav-item active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
            <span>Image Upscaler</span>
        </a>
        <a href="{{ route('pricing.index') }}" class="mobile-nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><line x1="12" y1="6" x2="12" y2="8"/><line x1="12" y1="16" x2="12" y2="18"/></svg>
            <span>Pricing & Kuota</span>
        </a>
        @auth
            <a href="{{ route('dashboard') }}" class="mobile-nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                <span>Dashboard</span>
            </a>
        @endauth
    </div>
    <div class="mobile-user-section">
        @auth
            <div class="mobile-user-card">
                <div class="mobile-user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                <div class="mobile-user-meta">
                    <div class="mobile-user-name">{{ $user->name }}</div>
                    <div class="mobile-user-email">{{ $user->email }}</div>
                </div>
            </div>
            @if ($user->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="mobile-btn-secondary" style="border-color:rgba(234,179,8,0.35);color:#fde047;">Admin Panel</a>
            @endif
            <form action="{{ route('logout') }}" method="POST" style="width:100%;">@csrf<button type="submit" class="mobile-btn-logout">Keluar Akun</button></form>
        @else
            <div class="mobile-auth-actions">
                <a href="{{ route('login') }}" class="mobile-btn-primary">Masuk Akun</a>
                <a href="{{ route('register') }}" class="mobile-btn-secondary">Daftar</a>
            </div>
        @endauth
    </div>
</div>

<div class="container">
    <div class="header">
        <h1 class="title">Image Upscaler</h1>
        <p class="subtitle">Perbesar resolusi gambar JPG, PNG, atau WEBP hingga 4× dengan interpolasi bicubic berkualitas tinggi.</p>
        <div class="method-note">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            High-Quality Resize &bull; Bukan AI Super Resolution
        </div>
    </div>

    {{-- Usage Banner --}}
    <div class="usage-banner">
        <div class="usage-badge">
            <span class="usage-badge-icon"></span>
            @auth
                @if ($usageInfo['unlimited'])
                    <span>Paket: <strong>{{ $usageInfo['plan_name'] }}</strong> &bull; Kuota: <strong>&infin; Unlimited</strong></span>
                @else
                    <span>Kuota Hari Ini: <strong>{{ $usageInfo['used_today'] }} / {{ $usageInfo['daily_limit'] }}</strong> terpakai</span>
                @endif
            @else
                <span>Kuota Tamu: <strong>{{ $usageInfo['used_today'] }} / {{ $usageInfo['daily_limit'] }}</strong> &bull; Daftar untuk kuota lebih</span>
            @endauth
        </div>
        <div>
            @auth
                @if (!$usageInfo['unlimited'])
                    <a href="{{ route('pricing.index') }}" class="upgrade-link">Upgrade &rarr;</a>
                @endif
            @else
                <a href="{{ route('register') }}" class="upgrade-link">Daftar Gratis &rarr;</a>
            @endauth
        </div>
    </div>

    <div class="card">
        {{-- Error display --}}
        <div class="alert-error" id="errorAlert" style="display:none;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div id="errorText"></div>
        </div>

        {{-- Upload Dropzone --}}
        <input type="file" id="imageInput" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" style="display:none;">
        <div class="drop-zone" id="dropZone">
            <div class="drop-zone-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            </div>
            <button type="button" class="btn-choose" id="btnChoose">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <span>Pilih Gambar</span>
            </button>
            <div class="drop-text-primary">atau tarik & lepas gambar ke sini</div>
            <div class="drop-text-secondary">JPG, PNG, WEBP &bull; Maks 10 MB</div>
        </div>

        {{-- Preview Section --}}
        <div class="preview-section" id="previewSection">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem;">
                <span class="section-label" style="margin-bottom:0;">Preview Gambar</span>
                <button type="button" class="btn-remove" id="btnRemove">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Ganti
                </button>
            </div>
            <div class="preview-image-box"><img id="previewImg" src="" alt="Preview"></div>
            <div class="file-meta" id="fileMeta">
                <span class="meta-tag" id="metaName"><strong id="metaNameText">—</strong></span>
                <span class="meta-tag"><strong id="metaSizeText">—</strong></span>
                <span class="meta-tag"><strong id="metaDimsText">—</strong></span>
            </div>

            {{-- Scale Selector --}}
            <div class="scale-section">
                <span class="section-label">Skala Upscale</span>
                <div class="scale-group">
                    <label class="scale-option">
                        <input type="radio" name="scale" value="2" checked>
                        <div class="scale-btn">
                            <span class="scale-title">2&times;</span>
                            <span class="scale-desc">Standar</span>
                        </div>
                    </label>
                    <label class="scale-option">
                        <input type="radio" name="scale" value="4">
                        <div class="scale-btn">
                            <span class="scale-title">4&times;</span>
                            <span class="scale-desc">Maksimal</span>
                        </div>
                    </label>
                </div>
            </div>

            <button type="button" class="btn-upscale" id="btnUpscale">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                <span>Upscale Gambar</span>
            </button>

            <div class="processing-indicator" id="processingIndicator">
                <div class="spinner"></div>
                <div style="font-size:0.88rem;color:var(--text-main);font-weight:600;">Memproses upscale gambar...</div>
                <div style="font-size:0.78rem;color:var(--text-dim);margin-top:2px;">Harap tunggu, proses bergantung pada ukuran gambar.</div>
            </div>
        </div>

        {{-- Result Section --}}
        <div class="result-section" id="resultSection">
            <div class="result-card">
                <div class="result-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Upscale Berhasil
                </div>
                <div class="result-preview"><img id="resultImg" src="" alt="Result"></div>
                <div class="result-meta" id="resultMeta">
                    <span class="meta-tag">Dimensi: <strong id="resultDims">—</strong></span>
                    <span class="meta-tag">Ukuran: <strong id="resultSize">—</strong></span>
                    <span class="meta-tag">Skala: <strong id="resultScale">—</strong></span>
                </div>
                <a href="#" class="btn-download" id="btnDownload" download>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Download Hasil
                </a>
                <button type="button" class="btn-new" id="btnNewUpscale">Upscale Gambar Lain</button>
            </div>
        </div>
    </div>

    <div class="footer">Image Upscaler &bull; Tools DKV V3 &bull; High-Quality Bicubic Interpolation</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const imageInput = document.getElementById('imageInput');
    const btnChoose = document.getElementById('btnChoose');
    const previewSection = document.getElementById('previewSection');
    const previewImg = document.getElementById('previewImg');
    const btnRemove = document.getElementById('btnRemove');
    const btnUpscale = document.getElementById('btnUpscale');
    const processingIndicator = document.getElementById('processingIndicator');
    const resultSection = document.getElementById('resultSection');
    const errorAlert = document.getElementById('errorAlert');
    const errorText = document.getElementById('errorText');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    let selectedFile = null;

    function showError(msg) {
        errorText.textContent = msg;
        errorAlert.style.display = 'flex';
        setTimeout(() => { errorAlert.style.display = 'none'; }, 8000);
    }

    function hideError() { errorAlert.style.display = 'none'; }

    function formatSize(bytes) {
        if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
        return (bytes / 1024).toFixed(1) + ' KB';
    }

    function handleFile(file) {
        hideError();
        const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            showError('Format tidak didukung. Gunakan JPG, PNG, atau WEBP.');
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            showError('Ukuran file terlalu besar. Maksimum 10 MB.');
            return;
        }
        selectedFile = file;
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            const img = new Image();
            img.onload = function() {
                document.getElementById('metaNameText').textContent = file.name.length > 30 ? file.name.substring(0, 27) + '...' : file.name;
                document.getElementById('metaSizeText').textContent = formatSize(file.size);
                document.getElementById('metaDimsText').textContent = img.width + ' × ' + img.height + ' px';
                dropZone.style.display = 'none';
                previewSection.classList.add('visible');
                resultSection.classList.remove('visible');
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    // Click upload
    btnChoose.addEventListener('click', (e) => { e.stopPropagation(); imageInput.click(); });
    dropZone.addEventListener('click', () => imageInput.click());
    imageInput.addEventListener('change', (e) => { if (e.target.files[0]) handleFile(e.target.files[0]); });

    // Drag & drop
    dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('dragover'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        if (e.dataTransfer.files[0]) handleFile(e.dataTransfer.files[0]);
    });

    // Remove / reset
    btnRemove.addEventListener('click', () => {
        selectedFile = null;
        imageInput.value = '';
        previewSection.classList.remove('visible');
        resultSection.classList.remove('visible');
        dropZone.style.display = 'flex';
        hideError();
    });

    // New upscale
    document.getElementById('btnNewUpscale').addEventListener('click', () => {
        selectedFile = null;
        imageInput.value = '';
        previewSection.classList.remove('visible');
        resultSection.classList.remove('visible');
        dropZone.style.display = 'flex';
        hideError();
    });

    // Upscale
    btnUpscale.addEventListener('click', async () => {
        if (!selectedFile) { showError('Pilih gambar terlebih dahulu.'); return; }
        hideError();
        btnUpscale.disabled = true;
        processingIndicator.classList.add('visible');
        resultSection.classList.remove('visible');

        const scale = document.querySelector('input[name="scale"]:checked').value;
        const formData = new FormData();
        formData.append('image', selectedFile);
        formData.append('scale', scale);

        try {
            const resp = await fetch('{{ route("upscaler.process") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData,
            });
            const data = await resp.json();
            if (!resp.ok || !data.success) {
                showError(data.error || 'Gagal memproses gambar.');
                return;
            }

            // Show result
            const dlUrl = '/upscaler/download/' + data.result.tempId + '/' + data.result.extension + '?name=' + encodeURIComponent(data.result.fileName);
            document.getElementById('resultImg').src = dlUrl;
            document.getElementById('resultDims').textContent = data.result.width + ' × ' + data.result.height + ' px';
            document.getElementById('resultSize').textContent = data.result.fileSizeFormatted;
            document.getElementById('resultScale').textContent = data.result.scale + '×';
            document.getElementById('btnDownload').href = dlUrl;
            document.getElementById('btnDownload').setAttribute('download', data.result.fileName);
            resultSection.classList.add('visible');
        } catch (err) {
            showError('Terjadi kesalahan jaringan. Silakan coba lagi.');
        } finally {
            btnUpscale.disabled = false;
            processingIndicator.classList.remove('visible');
        }
    });
});

// Theme toggle
function toggleDarkMode() {
    const current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
    updateThemeToggleIcons(next);
}
function updateThemeToggleIcons(theme) {
    document.querySelectorAll('.theme-icon-sun').forEach(el => el.style.display = theme === 'dark' ? 'inline-block' : 'none');
    document.querySelectorAll('.theme-icon-moon').forEach(el => el.style.display = theme === 'dark' ? 'none' : 'inline-block');
}
function toggleMobileNav() {
    const panel = document.getElementById('mobileNavPanel');
    const btn = document.getElementById('mobileNavToggleBtn');
    if (!panel || !btn) return;
    const isOpen = panel.classList.toggle('open');
    btn.setAttribute('aria-expanded', isOpen);
    btn.querySelector('.hamburger-icon').style.display = isOpen ? 'none' : 'inline-block';
    btn.querySelector('.close-icon').style.display = isOpen ? 'inline-block' : 'none';
}
document.addEventListener('click', (e) => {
    const panel = document.getElementById('mobileNavPanel');
    const btn = document.getElementById('mobileNavToggleBtn');
    if (panel && btn && panel.classList.contains('open') && !panel.contains(e.target) && !btn.contains(e.target)) toggleMobileNav();
});
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') { const p = document.getElementById('mobileNavPanel'); if (p && p.classList.contains('open')) toggleMobileNav(); }
});
document.addEventListener('DOMContentLoaded', () => updateThemeToggleIcons(document.documentElement.getAttribute('data-theme') || 'light'));
</script>

</body>
</html>
