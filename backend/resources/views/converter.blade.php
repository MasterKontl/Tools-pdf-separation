<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            --bg-card: #FFFFFF;
            --bg-card-border: #E2E8F0;
            --border: #E2E8F0;
            --primary: #1E3A5F;
            --secondary: #274C77;
            --accent: #3B82F6;
            --accent-hover: #2563EB;
            --text-main: #0F172A;
            --text-muted: #64748B;
            --text-dim: #94A3B8;
            --surface-alt: #F1F5F9;
            --surface-hover: #F1F5F9;
            --nav-bg: #1E3A5F;
            --nav-border: #274C77;
            --nav-item: #CBD5E1;
            --nav-item-active: #FFFFFF;
            --banner-bg: #EFF6FF;
            --banner-border: #BFDBFE;
            --banner-text: #1E3A5F;
            --dropzone-bg: #F8FAFC;
            --dropzone-border: #CBD5E1;
            --danger-bg: #FEF2F2;
            --danger-border: #FECACA;
            --danger-text: #DC2626;
            --success-bg: #F0FDF4;
            --success-border: #BBF7D0;
            --success-text: #16A34A;
            --radius-lg: 14px;
            --radius-md: 10px;
            --radius-sm: 6px;
            --shadow-card: 0 4px 16px rgba(30, 58, 95, 0.08);
            --modal-overlay: rgba(15, 23, 42, 0.65);
        }

        [data-theme="dark"] {
            --bg-body: #0B1120;
            --bg-card: #1E293B;
            --bg-card-border: #334155;
            --border: #334155;
            --primary: #3B82F6;
            --secondary: #2563EB;
            --accent: #60A5FA;
            --accent-hover: #93C5FD;
            --text-main: #F8FAFC;
            --text-muted: #94A3B8;
            --text-dim: #64748B;
            --surface-alt: #0F172A;
            --surface-hover: #334155;
            --nav-bg: #0A0F1D;
            --nav-border: #1E293B;
            --nav-item: #94A3B8;
            --nav-item-active: #FFFFFF;
            --banner-bg: #1E293B;
            --banner-border: #3B82F6;
            --banner-text: #93C5FD;
            --dropzone-bg: #0F172A;
            --dropzone-border: #334155;
            --danger-bg: #450A0A;
            --danger-border: #7F1D1D;
            --danger-text: #FCA5A5;
            --success-bg: #052E16;
            --success-border: #14532D;
            --success-text: #86EFAC;
            --shadow-card: 0 4px 20px rgba(0, 0, 0, 0.4);
            --modal-overlay: rgba(0, 0, 0, 0.75);
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
            display: flex; align-items: center; justify-content: space-between;
            padding: 1rem 2rem; border-bottom: 1px solid var(--nav-border);
            background: var(--nav-bg);
            position: sticky; top: 0; z-index: 50;
            width: 100%; max-width: 100%;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }

        .nav-brand {
            display: flex; align-items: center; gap: 8px;
            font-size: 1.15rem; font-weight: 800; color: #ffffff; text-decoration: none;
            flex-shrink: 0;
        }

        .nav-brand-badge {
            font-size: 0.72rem; padding: 2px 7px; border-radius: 4px;
            background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ffffff; font-weight: 700;
        }

        .nav-menu { display: flex; align-items: center; gap: 20px; }
        .nav-item { color: var(--nav-item); text-decoration: none; font-size: 0.9rem; font-weight: 600; transition: color 0.15s; }
        .nav-item:hover, .nav-item.active { color: var(--nav-item-active); }

        .nav-user-actions { display: flex; align-items: center; gap: 10px; }

        .btn-theme-toggle {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-sm);
            width: 38px;
            height: 38px;
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

        .btn-mobile-nav-toggle {
            display: none;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-sm);
            width: 38px;
            height: 38px;
            align-items: center;
            justify-content: center;
            color: #F8FAFC;
            cursor: pointer;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }
        .btn-mobile-nav-toggle:hover {
            background: rgba(255, 255, 255, 0.22);
            color: #FFFFFF;
        }

        .btn-nav-login {
            padding: 7px 16px; border-radius: var(--radius-sm); font-size: 0.85rem; font-weight: 700;
            background: var(--accent); color: #ffffff; text-decoration: none;
            transition: background 0.15s; white-space: nowrap;
        }
        .btn-nav-login:hover { background: var(--accent-hover); }

        .user-pill {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 6px 14px; border-radius: var(--radius-sm);
            background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.18);
            color: #ffffff; text-decoration: none; font-size: 0.85rem; font-weight: 600;
            transition: background 0.15s; white-space: nowrap;
        }
        .user-pill:hover { background: rgba(255, 255, 255, 0.2); }

        .btn-nav-logout {
            background: none; border: none; color: #CBD5E1; cursor: pointer;
            font-size: 0.85rem; font-weight: 600; padding: 6px 10px;
            transition: color 0.15s; white-space: nowrap;
        }
        .btn-nav-logout:hover { color: #f87171; }

        /* Mobile Navigation Drawer / Panel */
        .mobile-nav-panel {
            display: none;
            position: fixed;
            top: 61px;
            left: 0;
            right: 0;
            background: var(--nav-bg);
            border-bottom: 1px solid var(--nav-border);
            padding: 1.25rem 1.25rem 1.5rem;
            z-index: 49;
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            transform: translateY(-8px);
            opacity: 0;
            pointer-events: none;
            transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease;
        }
        .mobile-nav-panel.open {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }
        .mobile-nav-links {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 1.25rem;
        }
        .mobile-nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: var(--radius-sm);
            color: var(--nav-item);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            transition: all 0.15s ease;
        }
        .mobile-nav-item:hover, .mobile-nav-item.active {
            color: #FFFFFF;
            background: rgba(255, 255, 255, 0.08);
        }
        .mobile-nav-item.active {
            color: var(--accent);
            background: var(--banner-bg);
            font-weight: 700;
        }
        .mobile-nav-item svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }
        .mobile-user-section {
            border-top: 1px solid var(--nav-border);
            padding-top: 1.15rem;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .mobile-user-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: var(--radius-md);
        }
        .mobile-user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
        }
        .mobile-user-meta {
            overflow: hidden;
            flex: 1;
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
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .mobile-auth-actions {
            display: flex;
            gap: 8px;
        }
        .mobile-btn-primary {
            flex: 1;
            padding: 10px 14px;
            border-radius: var(--radius-sm);
            background: var(--primary);
            color: #FFFFFF;
            font-weight: 700;
            font-size: 0.88rem;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .mobile-btn-secondary {
            flex: 1;
            padding: 10px 14px;
            border-radius: var(--radius-sm);
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #FFFFFF;
            font-weight: 600;
            font-size: 0.88rem;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .mobile-btn-logout {
            width: 100%;
            padding: 10px;
            border-radius: var(--radius-sm);
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #F87171;
            font-weight: 700;
            font-size: 0.88rem;
            cursor: pointer;
            transition: background 0.15s;
        }
        .mobile-btn-logout:hover {
            background: rgba(239, 68, 68, 0.2);
        }

        /* Main Container */
        .container {
            width: 100%; max-width: 680px;
            margin: 2.5rem auto; padding: 0 1rem; flex: 1;
        }

        .header { text-align: center; margin-bottom: 2rem; }
        .title {
            font-size: clamp(1.8rem, 5vw, 2.3rem); font-weight: 800; letter-spacing: -0.025em;
            color: var(--text-main);
            margin-bottom: 0.5rem;
            word-break: break-word;
        }
        .subtitle { color: var(--text-muted); font-size: clamp(0.85rem, 3vw, 0.95rem); line-height: 1.5; max-width: 100%; }

        /* Usage Banner */
        .usage-banner {
            display: flex; align-items: center; justify-content: space-between;
            background: var(--banner-bg); border: 1px solid var(--banner-border);
            border-radius: var(--radius-md); padding: 0.85rem 1.25rem; margin-bottom: 1.5rem;
            font-size: 0.88rem; color: var(--banner-text);
        }
        .usage-badge {
            display: inline-flex; align-items: center; gap: 8px;
            font-weight: 700; color: var(--banner-text);
        }
        .usage-badge-icon {
            width: 8px; height: 8px; border-radius: 50%; background: var(--accent);
        }
        .upgrade-link {
            color: var(--accent); text-decoration: none; font-weight: 700; font-size: 0.84rem;
        }
        .upgrade-link:hover { text-decoration: underline; color: var(--accent-hover); }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--bg-card-border); border-radius: var(--radius-lg);
            padding: 2.25rem 2rem; box-shadow: var(--shadow-card);
        }

        .alert-error {
            background: var(--danger-bg); border: 1px solid var(--danger-border);
            color: var(--danger-text); padding: 0.9rem 1.2rem; border-radius: var(--radius-md);
            margin-bottom: 1.5rem; font-size: 0.9rem; display: flex; align-items: flex-start; gap: 10px;
        }

        /* Upload Dropzone */
        .drop-zone {
            border: 2px dashed var(--dropzone-border);
            border-radius: var(--radius-md);
            padding: 2rem 1rem;
            text-align: center;
            transition: all 0.2s ease;
            background: var(--dropzone-bg);
            position: relative;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        .drop-zone.dragover {
            border-color: var(--accent);
            background: var(--banner-bg);
        }
        .drop-zone-text {
            color: var(--text-muted);
            pointer-events: none;
        }

        /* Split Button Widget */
        .choose-widget {
            display: inline-block; position: relative; margin-bottom: 1.25rem; z-index: 20;
        }
        .split-btn {
            display: inline-flex; align-items: stretch;
            background: var(--primary);
            border-radius: var(--radius-sm);
            transition: background 0.15s ease;
        }
        .split-btn:hover { background: var(--secondary); }
        .split-btn-main {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 12px 22px; background: transparent; border: none; color: #ffffff;
            font-family: inherit; font-size: 1.05rem; font-weight: 700; cursor: pointer;
            border-radius: var(--radius-sm) 0 0 var(--radius-sm); transition: background 0.15s ease;
        }
        .split-btn-main:hover { background: rgba(255, 255, 255, 0.08); }
        .split-btn-divider { width: 1px; background: rgba(255, 255, 255, 0.2); }
        .split-btn-toggle {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 12px 14px; background: transparent; border: none; color: #ffffff;
            cursor: pointer; border-radius: 0 var(--radius-sm) var(--radius-sm) 0; transition: background 0.15s ease;
        }
        .split-btn-toggle:hover { background: rgba(255, 255, 255, 0.08); }
        .split-btn-toggle svg { transition: transform 0.2s ease; }
        .split-btn-toggle.open svg { transform: rotate(180deg); }

        /* Dropdown Menu */
        .choose-dropdown {
            position: absolute; top: calc(100% + 8px); left: 50%; transform: translateX(-50%);
            width: 220px; background: var(--bg-card);
            border-radius: var(--radius-md); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
            border: 1px solid var(--border); padding: 6px 0; display: none; z-index: 100;
        }
        .choose-dropdown.show { display: block; }
        .choose-dropdown-item {
            display: flex; align-items: center; gap: 13px; width: 100%; padding: 11px 18px;
            background: transparent; border: none; color: var(--text-main); font-family: inherit;
            font-size: 0.95rem; font-weight: 600; text-align: left; cursor: pointer; transition: background 0.15s ease;
        }
        .choose-dropdown-item:hover { background: var(--surface-hover); }
        .choose-dropdown-item svg { width: 20px; height: 20px; flex-shrink: 0; color: var(--accent); }

        .drop-text-primary { font-size: 0.96rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.3rem; }
        .drop-text-secondary { font-size: 0.8rem; color: var(--text-muted); }

        /* File Preview Box */
        .file-preview {
            display: none; background: var(--surface-alt);
            border: 1px solid var(--border); border-radius: var(--radius-md);
            padding: 1rem 1.2rem; align-items: center; justify-content: space-between; margin-top: 1rem;
        }
        .file-info { display: flex; align-items: center; gap: 12px; overflow: hidden; }
        .file-icon {
            width: 42px; height: 42px; background: var(--danger-bg);
            border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center;
            color: var(--danger-text); font-weight: 700; font-size: 0.78rem; flex-shrink: 0; border: 1px solid var(--danger-border);
        }
        .file-details { overflow: hidden; }
        .file-name { font-size: 0.92rem; font-weight: 600; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 320px; }
        .file-size { font-size: 0.8rem; color: var(--text-muted); }
        .source-tag { display: inline-block; font-size: 0.72rem; padding: 2px 7px; background: var(--banner-bg); border: 1px solid var(--banner-border); border-radius: 4px; color: var(--accent); margin-top: 3px; font-weight: 600; }
        .btn-remove-file { background: none; border: none; color: var(--text-dim); cursor: pointer; padding: 6px; border-radius: 6px; display: flex; align-items: center; justify-content: center; transition: color 0.2s; }
        .btn-remove-file:hover { color: var(--danger-text); background: var(--danger-bg); }

        /* Form Options Grid */
        .options-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin: 1.75rem 0 1.25rem; }
        @media (max-width: 520px) { .options-grid { grid-template-columns: 1fr; } }
        .section-label { display: block; font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 0.65rem; }
        .segment-group { display: flex; background: var(--surface-alt); padding: 4px; border-radius: var(--radius-md); border: 1px solid var(--border); gap: 4px; }
        .segment-option { flex: 1; position: relative; }
        .segment-option input[type="radio"] { position: absolute; opacity: 0; cursor: pointer; inset: 0; width: 100%; height: 100%; }
        .segment-btn { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 8px 4px; border-radius: var(--radius-sm); cursor: pointer; transition: all 0.15s ease; text-align: center; }
        .segment-title { font-size: 0.9rem; font-weight: 700; color: var(--text-muted); }
        .segment-desc { font-size: 0.68rem; color: var(--text-dim); margin-top: 2px; }
        .segment-option input[type="radio"]:checked + .segment-btn { background: var(--primary); }
        .segment-option input[type="radio"]:checked + .segment-btn .segment-title { color: #ffffff; }
        .segment-option input[type="radio"]:checked + .segment-btn .segment-desc { color: rgba(255, 255, 255, 0.8); }

        .segment-option.disabled-option { opacity: 0.5; cursor: not-allowed; pointer-events: none; }
        .segment-option.disabled-option input[type="radio"] { cursor: not-allowed; pointer-events: none; }
        .segment-btn.btn-disabled { cursor: not-allowed; pointer-events: none; }

        .multi-page-note { display: flex; align-items: center; gap: 8px; background: var(--surface-alt); border: 1px solid var(--border); padding: 0.75rem 1rem; border-radius: var(--radius-md); font-size: 0.82rem; color: var(--text-muted); margin-bottom: 1.5rem; }
        .btn-submit { width: 100%; padding: 1rem; border-radius: var(--radius-md); border: none; background: var(--primary); color: #ffffff; font-size: 1rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.15s; }
        .btn-submit:hover:not(:disabled) { background: var(--secondary); }
        .btn-submit:disabled { opacity: 0.5; cursor: not-allowed; }

        .processing-indicator { display: none; margin-top: 1.25rem; background: var(--surface-alt); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1rem 1.25rem; text-align: center; }
        .spinner { width: 24px; height: 24px; border: 3px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 0.5rem; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Modal Styles */
        .modal-overlay { position: fixed; inset: 0; background: var(--modal-overlay); display: none; align-items: center; justify-content: center; z-index: 1000; padding: 1.25rem; }
        .modal-overlay.active { display: flex; }
        .modal-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); width: 100%; max-width: 480px; padding: 1.75rem; box-shadow: var(--shadow-card); }
        .modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; }
        .modal-title { font-size: 1.15rem; font-weight: 700; color: var(--text-main); }
        .modal-close-btn { background: transparent; border: none; color: var(--text-muted); cursor: pointer; padding: 4px; border-radius: 6px; }
        .modal-input { width: 100%; padding: 0.85rem 1rem; background: var(--bg-body); border: 1px solid var(--border); border-radius: var(--radius-sm); color: var(--text-main); font-family: inherit; font-size: 0.92rem; outline: none; margin-bottom: 1rem; }
        .modal-input:focus { border-color: var(--accent); }
        .modal-alert { display: none; padding: 0.8rem 1rem; border-radius: var(--radius-sm); font-size: 0.85rem; margin-bottom: 1rem; gap: 8px; }
        .modal-alert.error { display: flex; background: var(--danger-bg); border: 1px solid var(--danger-border); color: var(--danger-text); }
        .modal-alert.loading { display: flex; background: var(--banner-bg); border: 1px solid var(--banner-border); color: var(--accent); align-items: center; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; }
        .btn-modal-cancel { padding: 0.75rem 1.25rem; background: var(--surface-alt); border: 1px solid var(--border); color: var(--text-muted); border-radius: var(--radius-sm); font-weight: 600; cursor: pointer; }
        .btn-modal-submit { padding: 0.75rem 1.5rem; background: var(--primary); border: none; color: #ffffff; border-radius: var(--radius-sm); font-weight: 700; cursor: pointer; }
        .btn-modal-submit:hover { background: var(--secondary); }

        .footer { margin-top: 2.5rem; text-align: center; font-size: 0.8rem; color: var(--text-muted); }

        /* Responsive Media Queries (Mobile First & Tablet) */
        @media (max-width: 768px) {
            .navbar {
                padding: 0.75rem 1rem;
            }
            .nav-menu {
                display: none !important;
            }
            .nav-user-actions .user-pill,
            .nav-user-actions .btn-nav-logout,
            .nav-user-actions .btn-nav-login {
                display: none !important;
            }
            .btn-mobile-nav-toggle {
                display: inline-flex !important;
            }
            .mobile-nav-panel {
                display: block;
                top: 57px;
            }
            .container {
                padding: 0 0.85rem;
                margin: 1.25rem auto 2rem;
                width: 100%;
                max-width: 100%;
            }
            .header {
                margin-bottom: 1.5rem;
            }
            .title {
                font-size: clamp(1.6rem, 5.8vw, 2.1rem);
                line-height: 1.22;
                word-break: break-word;
            }
            .subtitle {
                font-size: clamp(0.82rem, 3.2vw, 0.92rem);
                line-height: 1.55;
            }
            .usage-banner {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
                padding: 0.85rem 1rem;
            }
            .upgrade-link {
                align-self: flex-start;
                display: inline-block;
                padding-top: 2px;
            }
            .card {
                padding: 1.35rem 1rem;
                border-radius: var(--radius-md);
            }
            .drop-zone {
                padding: 2rem 0.85rem 1.65rem;
            }
            .choose-widget {
                width: 100%;
                max-width: 320px;
                display: flex;
                justify-content: center;
                margin-bottom: 1rem;
            }
            .split-btn {
                width: 100%;
            }
            .split-btn-main {
                flex: 1;
                justify-content: center;
                font-size: 0.95rem;
                padding: 12px 14px;
            }
            .split-btn-toggle {
                padding: 12px 14px;
            }
            .choose-dropdown {
                width: calc(100vw - 2.5rem);
                max-width: 300px;
            }
            .options-grid {
                grid-template-columns: 1fr;
                gap: 1.15rem;
                margin: 1.35rem 0 1rem;
            }
            .segment-group {
                width: 100%;
                display: flex;
                gap: 4px;
            }
            .segment-option {
                flex: 1 1 0;
                min-width: 0;
            }
            .segment-btn {
                padding: 8px 2px;
                min-height: 48px;
            }
            .segment-title {
                font-size: 0.85rem;
            }
            .segment-desc {
                font-size: 0.65rem;
            }
            .file-preview {
                padding: 0.85rem 0.95rem;
                flex-wrap: wrap;
                gap: 8px;
            }
            .file-info {
                min-width: 0;
                flex: 1;
            }
            .file-details {
                min-width: 0;
                flex: 1;
            }
            .file-name {
                max-width: 190px;
                min-width: 0;
                font-size: 0.86rem;
            }
            .modal-card {
                width: calc(100% - 1.5rem);
                max-width: 440px;
                padding: 1.25rem 1rem;
                border-radius: var(--radius-md);
            }
            .multi-page-note {
                font-size: 0.78rem;
                padding: 0.65rem 0.85rem;
            }
        }

        @media (max-width: 480px) {
            .modal-card input, .modal-card select, .modal-card textarea {
                font-size: 1rem !important; /* Mencegah auto-zoom di iOS Safari */
            }
            .modal-actions {
                flex-direction: column-reverse;
                gap: 8px;
            }
            .modal-actions button, .modal-actions a {
                width: 100%;
                justify-content: center;
                min-height: 44px;
            }
            .btn-convert {
                min-height: 46px;
            }
        }

        @media (max-width: 400px) {
            .file-name {
                max-width: 140px;
            }
            .split-btn-main {
                font-size: 0.88rem;
                padding: 10px 10px;
            }
            .split-btn-toggle {
                padding: 10px 10px;
            }
        }
    </style>
    @include('partials.seo-tags')
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
        <a href="{{ route('converter.index') }}" class="nav-item active">PDF Tools</a>
        <a href="{{ route('separation.index') }}" class="nav-item">Color Separation</a>
        <a href="{{ route('upscaler.index') }}" class="nav-item">Upscaler</a>
        <a href="{{ route('pricing.index') }}" class="nav-item">Pricing</a>
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
        <a href="{{ route('converter.index') }}" class="mobile-nav-item active">
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
        <a href="{{ route('pricing.index') }}" class="mobile-nav-item">
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
        @else
            <div class="mobile-auth-actions">
                <a href="{{ route('login') }}" class="mobile-btn-primary">Masuk Akun</a>
                <a href="{{ route('register') }}" class="mobile-btn-secondary">Daftar</a>
            </div>
        @endauth
    </div>
</div>

<div class="container">
    {{-- Header --}}
    <div class="header">
        <h1 class="title">Online PDF Converter</h1>
        <p class="subtitle">PDF Converter V1 &bull; Konversi dokumen PDF ke format gambar PNG / JPG resolusi tinggi (hingga 600 DPI) berbasis Poppler pdftoppm.</p>
    </div>

    {{-- Usage Quota Banner --}}
    <div class="usage-banner">
        <div class="usage-badge">
            <span class="usage-badge-icon"></span>
            @auth
                @if ($usageInfo['unlimited'])
                    <span>Paket: <strong>{{ $usageInfo['plan_name'] }}</strong> &bull; Kuota: <strong>∞ Unlimited</strong></span>
                @else
                    <span>Kuota Hari Ini: <strong>{{ $usageInfo['used_today'] }} / {{ $usageInfo['daily_limit'] }}</strong> terpakai</span>
                @endif
            @else
                <span>Kuota Tamu: <strong>{{ $usageInfo['used_today'] }} / {{ $usageInfo['daily_limit'] }}</strong> terpakai &bull; Daftar akun gratis untuk kuota 3/hari</span>
            @endauth
        </div>
        <div>
            @auth
                @if (!$usageInfo['unlimited'])
                    <a href="{{ route('pricing.index') }}" class="upgrade-link">Upgrade Kuota &rarr;</a>
                @endif
            @else
                <a href="{{ route('register') }}" class="upgrade-link">Daftar Akun Gratis &rarr;</a>
            @endauth
        </div>
    </div>

    <div class="card">
        @if (session('error'))
            <div class="alert-error">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-error">
                <div>
                    <strong>Periksa data formulir:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form id="convertForm" action="{{ route('converter.process') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Hidden File Inputs --}}
            <input type="file" name="pdf" id="pdfFileInput" accept="application/pdf,.pdf" style="display:none;" required>
            <input type="hidden" name="temp_file_id" id="tempFileIdInput" value="">

            {{-- Drag & Drop File Zone --}}
            <div class="drop-zone" id="dropZone">
                <div class="choose-widget" id="chooseWidget">
                    <div class="split-btn">
                        <button type="button" class="split-btn-main" id="btnChooseFiles" title="Pilih file dari komputer">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="12" y1="18" x2="12" y2="12"></line>
                                <line x1="9" y1="15" x2="15" y2="15"></line>
                            </svg>
                            <span>Choose Files</span>
                        </button>
                        <div class="split-btn-divider"></div>
                        <button type="button" class="split-btn-toggle" id="btnDropdownToggle" title="Opsi sumber file lainnya" aria-label="Toggle pilihan sumber file">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                    </div>

                    {{-- Dropdown Menu (Sesuai Referensi Gambar) --}}
                    <div class="choose-dropdown" id="chooseDropdown">
                        <button type="button" class="choose-dropdown-item" id="optFromDevice">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 6h-8l-2-2H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 12H4V8h16v10z"/></svg>
                            <span>From Device</span>
                        </button>
                        <button type="button" class="choose-dropdown-item" id="optFromDropbox" data-provider="dropbox">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M7.06 2L1 6.06l5.06 4.06L12 6.06 7.06 2zm9.88 0L12 6.06l5.94 4.06L23 6.06 16.94 2zM1 14.18l6.06 4.06L12 14.18l-5.94-4.06L1 14.18zm22 0l-6.06-4.06L12 14.18l4.94 4.06L23 14.18zM12 15.34l-5.06 3.4-1.12-.76v1.44L12 22l6.18-2.58v-1.44l-1.12.76-5.06-3.4z"/></svg>
                            <span>From Dropbox</span>
                        </button>
                        <button type="button" class="choose-dropdown-item" id="optFromGoogleDrive" data-provider="gdrive">
                            <svg viewBox="0 0 87.3 78" style="width:20px;height:18px;">
                                <path d="M6.6 66.85l3.85 6.65c.8 1.4 1.95 2.5 3.3 3.3l13.75-23.8H0c0 1.55.4 3.1 1.2 4.5l5.4 9.35z" fill="#0066da"/>
                                <path d="M43.65 25L29.9 1.2c-1.35.8-2.5 1.9-3.3 3.3l-25.4 44c-.8 1.4-1.2 2.95-1.2 4.5h27.5L43.65 25z" fill="#00ac47"/>
                                <path d="M73.55 76.8c1.35-.8 2.5-1.9 3.3-3.3l1.6-2.75 7.65-13.25c.8-1.4 1.2-2.95 1.2-4.5H59.8l5.85 10.15 7.9 13.65z" fill="#ea4335"/>
                                <path d="M43.65 25L57.4 1.2c-1.35-.8-2.9-1.2-4.5-1.2H34.4c-1.6 0-3.15.4-4.5 1.2l13.75 23.8z" fill="#00832d"/>
                                <path d="M59.8 53H27.5L13.75 76.8c1.35.8 2.9 1.2 4.5 1.2h50.8c1.6 0 3.15-.4 4.5-1.2L59.8 53z" fill="#2684fc"/>
                                <path d="M73.4 26.5l-12.7-22c-.8-1.4-1.95-2.5-3.3-3.3L43.65 25l16.15 28h27.5c0-1.55-.4-3.1-1.2-4.5l-12.7-22z" fill="#ffba00"/>
                            </svg>
                            <span>From Google Drive</span>
                        </button>
                        <button type="button" class="choose-dropdown-item" id="optFromOneDrive" data-provider="onedrive">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM19 18H6c-2.21 0-4-1.79-4-4 0-2.05 1.53-3.76 3.56-3.97l1.07-.11.5-.95C8.08 7.14 9.94 6 12 6c2.62 0 4.88 1.86 5.39 4.43l.3 1.5 1.53.11c1.56.1 2.78 1.41 2.78 2.96 0 1.65-1.35 3-3 3z"/></svg>
                            <span>From OneDrive</span>
                        </button>
                        <button type="button" class="choose-dropdown-item" id="optFromUrl" data-provider="url">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                            <span>From Url</span>
                        </button>
                    </div>
                </div>

                <div class="drop-text-primary">Sentuh untuk memilih file PDF<br>atau tarik & lepas (drag and drop) ke sini</div>
                <div class="drop-text-secondary">Maksimum ukuran dokumen hingga 100 MB</div>
            </div>

            {{-- File Preview Box --}}
            <div class="file-preview" id="filePreview">
                <div class="file-info">
                    <div class="file-icon">PDF</div>
                    <div class="file-details">
                        <div class="file-name" id="fileNameText">document.pdf</div>
                        <div class="file-size" id="fileSizeText">0 KB</div>
                        <div class="source-tag" id="fileSourceTag">📁 Dari Perangkat</div>
                    </div>
                </div>
                <button type="button" class="btn-remove-file" id="btnRemoveFile" title="Hapus file">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Options Grid --}}
            <div class="options-grid">
                <div>
                    <span class="section-label">Format Output</span>
                    <div class="segment-group">
                        <label class="segment-option">
                            <input type="radio" name="format" value="png" {{ old('format', 'png') === 'png' ? 'checked' : '' }}>
                            <div class="segment-btn">
                                <span class="segment-title">PNG</span>
                                <span class="segment-desc">Lossless & Tajam</span>
                            </div>
                        </label>
                        <label class="segment-option">
                            <input type="radio" name="format" value="jpg" {{ old('format') === 'jpg' ? 'checked' : '' }}>
                            <div class="segment-btn">
                                <span class="segment-title">JPG</span>
                                <span class="segment-desc">Ukuran Lebih Ringan</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <span class="section-label">Resolusi (DPI)</span>
                    <div class="segment-group">
                        <label class="segment-option">
                            <input type="radio" name="dpi" value="150" {{ old('dpi') == '150' ? 'checked' : '' }}>
                            <div class="segment-btn">
                                <span class="segment-title">150</span>
                                <span class="segment-desc">Web / Preview</span>
                            </div>
                        </label>
                        <label class="segment-option">
                            <input type="radio" name="dpi" value="300" {{ old('dpi', '300') == '300' ? 'checked' : '' }}>
                            <div class="segment-btn">
                                <span class="segment-title">300</span>
                                <span class="segment-desc">Standar Cetak</span>
                            </div>
                        </label>
                        @php
                            $is600Allowed = in_array(600, $allowedDpis ?? [150, 300]);
                        @endphp
                        <label class="segment-option {{ !$is600Allowed ? 'disabled-option' : '' }}" title="{{ !$is600Allowed ? '600 DPI Ultra Presisi tersedia mulai Paket Pro & Unlimited' : '600 DPI Ultra Presisi' }}">
                            <input type="radio" name="dpi" value="600" {{ old('dpi') == '600' && $is600Allowed ? 'checked' : '' }} {{ !$is600Allowed ? 'disabled' : '' }}>
                            <div class="segment-btn">
                                <span class="segment-title">600</span>
                                <span class="segment-desc">{{ $is600Allowed ? 'Ultra Presisi' : 'Mulai Paket Pro' }}</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="multi-page-note">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
                <span>Dokumen dengan banyak halaman otomatis dibundel ke dalam file <strong>ZIP</strong> berurutan (page 1, 2, ...).</span>
            </div>

            <button type="submit" class="btn-submit" id="btnSubmit" disabled>
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span>Mulai Konversi & Download</span>
            </button>

            <div class="processing-indicator" id="processingIndicator">
                <div class="spinner"></div>
                <div class="processing-text" style="font-size:0.88rem;color:var(--text-main);font-weight:600;">Merender halaman PDF dengan pdftoppm...</div>
                <div class="processing-sub" style="font-size:0.78rem;color:var(--text-dim);margin-top:2px;">Harap tunggu, browser akan otomatis mengunduh hasil konversi.</div>
            </div>
        </form>
    </div>

    {{-- Limit Reached Modal --}}
    @if (session('quota_exceeded'))
        <div class="modal-overlay active" id="quotaLimitModal">
            <div class="modal-card" style="text-align:center;">
                <div style="margin: 0 auto 1rem; width: 54px; height: 54px; border-radius: 50%; background: #EFF6FF; border: 1px solid #BFDBFE; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                    <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 style="font-size:1.35rem;font-weight:800;color:var(--primary);margin-bottom:0.5rem;">Batas Kuota Harian Tercapai</h3>
                @auth
                    <p style="color:var(--text-muted);font-size:0.9rem;line-height:1.5;margin-bottom:1.5rem;">
                        Anda telah menggunakan seluruh jatah konversi gratis (3x per hari) untuk hari ini. Tingkatkan paket Anda untuk mendapatkan kuota harian yang lebih besar atau akses tanpa batas.
                    </p>
                    <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                        <a href="{{ route('pricing.index') }}" class="btn-modal-submit" style="text-decoration:none;padding:0.85rem 1.5rem;">Lihat Pilihan Paket & Upgrade</a>
                        <button type="button" class="btn-modal-cancel" onclick="document.getElementById('quotaLimitModal').classList.remove('active');">Tutup</button>
                    </div>
                @else
                    <p style="color:var(--text-muted);font-size:0.92rem;line-height:1.5;margin-bottom:0.85rem;">
                        Anda telah menggunakan seluruh jatah <strong>1x konversi gratis</strong> untuk tamu hari ini.
                    </p>
                    <div style="background:#EFF6FF;border:1px solid #BFDBFE;border-radius:var(--radius-md);padding:0.85rem 1rem;margin-bottom:1.5rem;font-size:0.88rem;color:var(--primary);line-height:1.45;text-align:left;">
                        <strong>Daftar akun gratis atau login</strong> sekarang juga untuk langsung mendapatkan jatah <strong>kuota 3x konversi per hari</strong> secara gratis.
                    </div>
                    <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                        <a href="{{ route('register') }}" class="btn-modal-submit" style="text-decoration:none;padding:0.85rem 1.35rem;">Daftar Akun Gratis (3x/hari)</a>
                        <a href="{{ route('login') }}" class="btn-modal-cancel" style="text-decoration:none;padding:0.85rem 1.25rem;color:var(--text-main);font-weight:600;display:inline-flex;align-items:center;">Masuk / Login</a>
                        <a href="{{ route('pricing.index') }}" class="btn-modal-cancel" style="text-decoration:none;padding:0.85rem 1.25rem;color:var(--text-muted);display:inline-flex;align-items:center;">Lihat Paket</a>
                        <button type="button" class="btn-modal-cancel" onclick="document.getElementById('quotaLimitModal').classList.remove('active');">Tutup</button>
                    </div>
                @endauth
            </div>
        </div>
    @endif

    {{-- Cloud / URL Import Modal --}}
    <div class="modal-overlay" id="urlModal" role="dialog" aria-modal="true">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title-wrap" style="display:flex;align-items:center;gap:10px;">
                    <span id="modalIcon">🔗</span>
                    <h3 class="modal-title" id="modalTitle">Import PDF dari URL</h3>
                </div>
                <button type="button" class="modal-close-btn" id="btnCloseModal" title="Tutup">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <p class="modal-desc" id="modalDesc" style="font-size:0.86rem;color:var(--text-muted);line-height:1.5;margin-bottom:1.25rem;">
                Masukkan tautan publik file PDF yang ingin dikonversi.
            </p>
            <div class="modal-input-group">
                <input type="url" class="modal-input" id="modalUrlInput" placeholder="https://example.com/document.pdf" autocomplete="off">
            </div>
            <div class="modal-alert loading" id="modalLoadingAlert">
                <div class="spinner" style="width:16px;height:16px;margin:0;border-width:2px;"></div>
                <span>Mengunduh dan memeriksa file PDF secara aman...</span>
            </div>
            <div class="modal-alert error" id="modalErrorAlert">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span id="modalErrorText">Error message</span>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-modal-cancel" id="btnCancelModal">Batal</button>
                <button type="button" class="btn-modal-submit" id="btnSubmitModal">
                    <span>Ambil & Muat PDF</span>
                </button>
            </div>
        </div>
    </div>

    {{-- SEO Content Section --}}
    <section style="margin-top:2.5rem;">
        <h2 style="font-size:1.25rem;font-weight:700;color:var(--text-main);margin-bottom:0.75rem;">Tentang PDF Converter</h2>
        <p style="color:var(--text-muted);font-size:0.92rem;line-height:1.65;margin-bottom:1rem;">
            PDF Converter dari Tools DKV memungkinkan Anda mengonversi dokumen PDF menjadi gambar PNG atau JPG secara online.
            Cukup unggah file PDF, pilih format output dan resolusi yang diinginkan, lalu unduh hasil konversi langsung dari browser tanpa perlu menginstal software apapun.
        </p>
        <p style="color:var(--text-muted);font-size:0.92rem;line-height:1.65;margin-bottom:1.5rem;">
            Tool ini cocok untuk desainer, pencetak, mahasiswa DKV, dan siapapun yang membutuhkan ekstrak halaman PDF ke dalam format gambar
            untuk keperluan presentasi, portofolio, atau persiapan cetak.
        </p>

        <h2 style="font-size:1.25rem;font-weight:700;color:var(--text-main);margin-bottom:0.75rem;">Cara Menggunakan</h2>
        <ol style="color:var(--text-muted);font-size:0.92rem;line-height:1.8;margin-bottom:1.5rem;padding-left:1.25rem;">
            <li>Klik <strong>Choose Files</strong> atau seret PDF ke area upload.</li>
            <li>Pilih format output: <strong>PNG</strong> (lossless, tajam) atau <strong>JPG</strong> (ukuran lebih ringan).</li>
            <li>Pilih resolusi DPI: 150 (web), 300 (standar cetak), atau 600 (Pro).</li>
            <li>Klik <strong>Mulai Konversi &amp; Download</strong> dan tunggu prosesnya.</li>
            <li>Hasil akan otomatis terunduh. Untuk dokumen multi-halaman, file dikemas dalam ZIP.</li>
        </ol>

        <h2 style="font-size:1.25rem;font-weight:700;color:var(--text-main);margin-bottom:0.75rem;">Format &amp; Spesifikasi</h2>
        <ul style="color:var(--text-muted);font-size:0.92rem;line-height:1.8;margin-bottom:1.5rem;padding-left:1.25rem;">
            <li><strong>Input:</strong> File PDF (maksimal 100 MB)</li>
            <li><strong>Output:</strong> PNG atau JPG</li>
            <li><strong>Resolusi:</strong> 150 DPI (web), 300 DPI (cetak), 600 DPI (Pro)</li>
            <li><strong>Engine:</strong> Poppler pdftoppm (high-fidelity rendering)</li>
            <li><strong>Multi-page:</strong> Otomatis dibundel dalam file ZIP</li>
        </ul>

        <h2 style="font-size:1.25rem;font-weight:700;color:var(--text-main);margin-bottom:0.75rem;">FAQ</h2>
        <details style="margin-bottom:0.75rem;border:1px solid var(--border);border-radius:var(--radius-md);padding:0.85rem 1rem;background:var(--surface-alt);">
            <summary style="font-weight:600;color:var(--text-main);cursor:pointer;font-size:0.92rem;">Apakah saya perlu menginstal software untuk mengonversi PDF?</summary>
            <p style="color:var(--text-muted);font-size:0.88rem;line-height:1.6;margin-top:0.5rem;">Tidak. Seluruh proses konversi berjalan di server dan dapat diakses langsung dari browser. Tidak ada software yang perlu diinstal di komputer Anda.</p>
        </details>
        <details style="margin-bottom:0.75rem;border:1px solid var(--border);border-radius:var(--radius-md);padding:0.85rem 1rem;background:var(--surface-alt);">
            <summary style="font-weight:600;color:var(--text-main);cursor:pointer;font-size:0.92rem;">Format output mana yang sebaiknya dipilih, PNG atau JPG?</summary>
            <p style="color:var(--text-muted);font-size:0.88rem;line-height:1.6;margin-top:0.5rem;">PNG menghasilkan gambar lossless tanpa kompresi, cocok untuk desain grafis dan pencetakan. JPG lebih ringan dan cocok untuk keperluan web atau presentasi.</p>
        </details>
        <details style="margin-bottom:0.75rem;border:1px solid var(--border);border-radius:var(--radius-md);padding:0.85rem 1rem;background:var(--surface-alt);">
            <summary style="font-weight:600;color:var(--text-main);cursor:pointer;font-size:0.92rem;">Berapa DPI yang cocok untuk kebutuhan saya?</summary>
            <p style="color:var(--text-muted);font-size:0.88rem;line-height:1.6;margin-top:0.5rem;">150 DPI cocok untuk keperluan web dan preview. 300 DPI adalah standar untuk pencetakan. 600 DPI tersedia untuk pengguna Pro dan menghasilkan resolusi tertinggi.</p>
        </details>
        <details style="margin-bottom:0.75rem;border:1px solid var(--border);border-radius:var(--radius-md);padding:0.85rem 1rem;background:var(--surface-alt);">
            <summary style="font-weight:600;color:var(--text-main);cursor:pointer;font-size:0.92rem;">Apakah PDF multi-halaman bisa diproses sekaligus?</summary>
            <p style="color:var(--text-muted);font-size:0.88rem;line-height:1.6;margin-top:0.5rem;">Ya. Dokumen PDF dengan banyak halaman akan otomatis dikonversi satu per satu dan dikemas dalam file ZIP berurutan.</p>
        </details>
        <details style="margin-bottom:0.75rem;border:1px solid var(--border);border-radius:var(--radius-md);padding:0.85rem 1rem;background:var(--surface-alt);">
            <summary style="font-weight:600;color:var(--text-main);cursor:pointer;font-size:0.92rem;">Apakah file saya aman selama proses konversi?</summary>
            <p style="color:var(--text-muted);font-size:0.88rem;line-height:1.6;margin-top:0.5rem;">Ya. File yang diunggah hanya diproses sementara di server dan dihapus otomatis setelah konversi selesai. Tidak ada file yang disimpan permanen.</p>
        </details>

        <h2 style="font-size:1.25rem;font-weight:700;color:var(--text-main);margin-bottom:0.75rem;">Tool Lainnya</h2>
        <p style="color:var(--text-muted);font-size:0.92rem;line-height:1.65;">
            Selain PDF Converter, Tools DKV juga menyediakan
            <a href="{{ route('separation.index') }}" style="color:var(--accent);text-decoration:none;font-weight:600;">Color Separation untuk sablon dan printing</a>
            serta
            <a href="{{ route('upscaler.index') }}" style="color:var(--accent);text-decoration:none;font-weight:600;">Image Upscaler untuk memperbesar resolusi gambar</a>.
            Lihat <a href="{{ route('pricing.index') }}" style="color:var(--accent);text-decoration:none;font-weight:600;">paket dan harga</a> untuk upgrade kuota konversi.
        </p>
    </section>

    <div class="footer">
        Engine: Poppler pdftoppm &bull; Tools DKV Platform V3 &bull; &copy; {{ date('Y') }}
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const fileInput = document.getElementById('pdfFileInput');
        const tempFileIdInput = document.getElementById('tempFileIdInput');
        const dropZone = document.getElementById('dropZone');
        const filePreview = document.getElementById('filePreview');
        const fileNameText = document.getElementById('fileNameText');
        const fileSizeText = document.getElementById('fileSizeText');
        const fileSourceTag = document.getElementById('fileSourceTag');
        const btnRemoveFile = document.getElementById('btnRemoveFile');
        const btnSubmit = document.getElementById('btnSubmit');
        const convertForm = document.getElementById('convertForm');
        const processingIndicator = document.getElementById('processingIndicator');

        // Split button & dropdown elements
        const btnChooseFiles = document.getElementById('btnChooseFiles');
        const btnDropdownToggle = document.getElementById('btnDropdownToggle');
        const chooseDropdown = document.getElementById('chooseDropdown');
        const optFromDevice = document.getElementById('optFromDevice');
        const optFromDropbox = document.getElementById('optFromDropbox');
        const optFromGoogleDrive = document.getElementById('optFromGoogleDrive');
        const optFromOneDrive = document.getElementById('optFromOneDrive');
        const optFromUrl = document.getElementById('optFromUrl');

        // Modal elements
        const urlModal = document.getElementById('urlModal');
        const modalIcon = document.getElementById('modalIcon');
        const modalTitle = document.getElementById('modalTitle');
        const modalDesc = document.getElementById('modalDesc');
        const modalUrlInput = document.getElementById('modalUrlInput');
        const modalLoadingAlert = document.getElementById('modalLoadingAlert');
        const modalErrorAlert = document.getElementById('modalErrorAlert');
        const modalErrorText = document.getElementById('modalErrorText');
        const btnCloseModal = document.getElementById('btnCloseModal');
        const btnCancelModal = document.getElementById('btnCancelModal');
        const btnSubmitModal = document.getElementById('btnSubmitModal');

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function formatBytes(bytes, decimals = 2) {
            if (!bytes || bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        function updateFileState(name, sizeFormatted, sourceText) {
            if (name) {
                fileNameText.textContent = name;
                fileSizeText.textContent = sizeFormatted;
                fileSourceTag.textContent = sourceText || '📁 Dari Perangkat';
                filePreview.style.display = 'flex';
                dropZone.style.display = 'none';
                btnSubmit.removeAttribute('disabled');
            } else {
                filePreview.style.display = 'none';
                dropZone.style.display = 'block';
                btnSubmit.setAttribute('disabled', 'true');
                fileInput.value = '';
                tempFileIdInput.value = '';
                fileInput.setAttribute('required', 'true');
            }
        }

        function toggleDropdown() {
            const isOpen = chooseDropdown.classList.contains('show');
            if (isOpen) {
                closeDropdown();
            } else {
                chooseDropdown.classList.add('show');
                btnDropdownToggle.classList.add('open');
            }
        }

        function closeDropdown() {
            chooseDropdown.classList.remove('show');
            btnDropdownToggle.classList.remove('open');
        }

        btnDropdownToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleDropdown();
        });

        btnChooseFiles.addEventListener('click', (e) => {
            e.stopPropagation();
            closeDropdown();
            fileInput.click();
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('#chooseWidget')) {
                closeDropdown();
            }
        });

        optFromDevice.addEventListener('click', () => {
            closeDropdown();
            fileInput.click();
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files && e.target.files[0]) {
                const file = e.target.files[0];
                tempFileIdInput.value = '';
                updateFileState(file.name, formatBytes(file.size), '📁 Dari Perangkat');
            }
        });

        const providerConfig = {
            gdrive: {
                title: 'Import PDF dari Google Drive',
                icon: '🎨',
                desc: 'Masukkan link share publik file Google Drive. Pastikan opsi berbagi file diset ke "Anyone with the link".',
                placeholder: 'https://drive.google.com/file/d/.../view?usp=sharing',
                sourceTag: '🎨 Dari Google Drive'
            },
            dropbox: {
                title: 'Import PDF dari Dropbox',
                icon: '📦',
                desc: 'Masukkan link tautan publik file PDF dari Dropbox Anda (share link).',
                placeholder: 'https://www.dropbox.com/s/.../file.pdf?dl=0',
                sourceTag: '📦 Dari Dropbox'
            },
            onedrive: {
                title: 'Import PDF dari OneDrive',
                icon: '☁️',
                desc: 'Masukkan tautan publik file PDF dari OneDrive yang dapat diunduh langsung tanpa login.',
                placeholder: 'https://1drv.ms/...',
                sourceTag: '☁️ Dari OneDrive'
            },
            url: {
                title: 'Import PDF dari URL',
                icon: '🔗',
                desc: 'Masukkan tautan langsung (direct link) file PDF publik di internet.',
                placeholder: 'https://example.com/document.pdf',
                sourceTag: '🔗 Dari URL Web'
            }
        };

        let currentActiveProvider = 'url';

        function openModal(provider) {
            currentActiveProvider = provider;
            const conf = providerConfig[provider] || providerConfig.url;
            modalTitle.textContent = conf.title;
            modalIcon.textContent = conf.icon;
            modalDesc.textContent = conf.desc;
            modalUrlInput.placeholder = conf.placeholder;
            modalUrlInput.value = '';
            modalLoadingAlert.style.display = 'none';
            modalErrorAlert.style.display = 'none';
            btnSubmitModal.removeAttribute('disabled');
            urlModal.classList.add('active');
            setTimeout(() => modalUrlInput.focus(), 50);
        }

        function closeModal() {
            urlModal.classList.remove('active');
        }

        [optFromDropbox, optFromGoogleDrive, optFromOneDrive, optFromUrl].forEach(btn => {
            btn.addEventListener('click', () => {
                closeDropdown();
                const prov = btn.getAttribute('data-provider');
                openModal(prov);
            });
        });

        btnCloseModal.addEventListener('click', closeModal);
        btnCancelModal.addEventListener('click', closeModal);
        urlModal.addEventListener('click', (e) => {
            if (e.target === urlModal) closeModal();
        });

        btnSubmitModal.addEventListener('click', async () => {
            const rawUrl = modalUrlInput.value.trim();
            if (!rawUrl) {
                modalErrorText.textContent = 'Silakan masukkan tautan URL file PDF terlebih dahulu.';
                modalErrorAlert.style.display = 'flex';
                return;
            }

            modalErrorAlert.style.display = 'none';
            modalLoadingAlert.style.display = 'flex';
            btnSubmitModal.setAttribute('disabled', 'true');

            try {
                const response = await fetch('{{ route("converter.fetch-url") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ url: rawUrl })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Gagal mengambil file PDF dari URL.');
                }

                tempFileIdInput.value = data.tempId;
                fileInput.removeAttribute('required');
                const conf = providerConfig[currentActiveProvider] || providerConfig.url;
                updateFileState(data.fileName, data.fileSizeFormatted, conf.sourceTag);
                closeModal();
            } catch (err) {
                modalLoadingAlert.style.display = 'none';
                modalErrorText.textContent = err.message || 'Terjadi kesalahan saat mengunduh file.';
                modalErrorAlert.style.display = 'flex';
                btnSubmitModal.removeAttribute('disabled');
            }
        });

        modalUrlInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                btnSubmitModal.click();
            }
        });

        // Drag and drop handlers
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.remove('dragover');
            }, false);
        });

        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files.length > 0) {
                fileInput.files = files;
                tempFileIdInput.value = '';
                updateFileState(files[0].name, formatBytes(files[0].size), '📁 Dari Perangkat');
            }
        });

        btnRemoveFile.addEventListener('click', () => {
            updateFileState(null);
        });

        convertForm.addEventListener('submit', () => {
            btnSubmit.setAttribute('disabled', 'true');
            btnSubmit.style.display = 'none';
            processingIndicator.style.display = 'block';

            setTimeout(() => {
                btnSubmit.removeAttribute('disabled');
                btnSubmit.style.display = 'flex';
                processingIndicator.style.display = 'none';
            }, 10000);
        });
    });

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
</script>

</body>
</html>
