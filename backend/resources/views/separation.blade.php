<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Screen Print Separations Editor | MsterCV</title>
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
            --border-subtle: #CBD5E1;
            --nav-bg: #1E3A5F;
            --nav-border: #274C77;
            --nav-item: #CBD5E1;
            --nav-item-active: #FFFFFF;
            --banner-bg: #EFF6FF;
            --banner-border: #BFDBFE;
            --banner-text: #1E3A5F;
            --danger: #DC2626;
            --danger-bg: #FEF2F2;
            --danger-border: #FECACA;
            --danger-text: #991B1B;
            --success: #16A34A;
            --success-bg: #F0FDF4;
            --success-border: #BBF7D0;
            --success-text: #166534;
            --warning: #D97706;
            --warning-bg: #FFFBEB;
            --warning-border: #FDE68A;
            --warning-text: #92400E;
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
            --border-subtle: #475569;
            --nav-bg: #0A0F1D;
            --nav-border: #1E293B;
            --nav-item: #94A3B8;
            --nav-item-active: #FFFFFF;
            --banner-bg: #1E293B;
            --banner-border: #3B82F6;
            --banner-text: #93C5FD;
            --danger: #EF4444;
            --danger-bg: #450A0A;
            --danger-border: #7F1D1D;
            --danger-text: #FCA5A5;
            --success: #22C55E;
            --success-bg: #052E16;
            --success-border: #14532D;
            --success-text: #86EFAC;
            --warning: #F59E0B;
            --warning-bg: #451A03;
            --warning-border: #78350F;
            --warning-text: #FDE68A;
            --shadow-card: 0 4px 20px rgba(0, 0, 0, 0.4);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            padding: 0 0 3rem;
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
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 800;
            font-size: 1.15rem;
            color: #ffffff;
            text-decoration: none;
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
        .nav-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .nav-item {
            color: var(--nav-item);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: color 0.15s;
        }
        .nav-item:hover, .nav-item.active {
            color: var(--nav-item-active);
        }
        .nav-user-actions {
            display: flex;
            align-items: center;
            gap: 10px;
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
            background: none;
            border: none;
            color: #CBD5E1;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 6px 10px;
            transition: color 0.15s;
        }
        .btn-nav-logout:hover {
            color: #f87171;
        }

        .btn-nav-login {
            padding: 7px 16px;
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
            font-weight: 700;
            background: var(--accent);
            color: #ffffff;
            text-decoration: none;
            transition: background 0.15s;
        }
        .btn-nav-login:hover {
            background: var(--accent-hover);
        }

        .main-container {
            width: 100%;
            max-width: 1280px;
            margin: 2rem auto 0;
            padding: 0 1rem;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .badge-dkv {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 999px;
            background: var(--banner-bg);
            border: 1px solid var(--banner-border);
            color: var(--accent);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
        }
        .title {
            font-size: 2.15rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--text-main);
            margin-bottom: 0.4rem;
        }
        .subtitle {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.5;
            max-width: 760px;
            margin: 0 auto;
        }

        /* Alerts & Notices */
        .alert-error {
            background: var(--danger-bg);
            border: 1px solid var(--danger-border);
            color: var(--danger-text);
            padding: 0.9rem 1.2rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .alert-success {
            background: var(--success-bg);
            border: 1px solid var(--success-border);
            color: var(--success-text);
            padding: 0.9rem 1.2rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        /* Usage Banner */
        .usage-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--banner-bg);
            border: 1px solid var(--banner-border);
            border-radius: var(--radius-md);
            padding: 0.85rem 1.25rem;
            margin-bottom: 1.5rem;
            font-size: 0.88rem;
            color: var(--banner-text);
        }
        .usage-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            color: var(--banner-text);
        }
        .usage-badge-icon {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--accent);
        }
        .upgrade-link {
            color: var(--accent);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.84rem;
        }
        .upgrade-link:hover {
            text-decoration: underline;
        }

        .notice-box {
            background: #FFFFFF;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
        }
        .notice-content {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .engine-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .engine-badge.active {
            background: #DCFCE7;
            color: #15803D;
            border: 1px solid #BBF7D0;
        }
        .engine-badge.disabled {
            background: #F1F5F9;
            color: #64748B;
            border: 1px solid #E2E8F0;
        }

        /* Layout Grid */
        .editor-grid {
            display: grid;
            grid-template-columns: 360px 1fr;
            gap: 24px;
            align-items: start;
        }
        @media (max-width: 960px) {
            .editor-grid {
                grid-template-columns: 1fr;
            }
        }

        .panel-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
        }

        /* Form Inputs & Upload */
        .drop-zone {
            position: relative;
            background: var(--surface-alt);
            border: 2px dashed var(--border-subtle);
            border-radius: var(--radius-md);
            padding: 1.75rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .drop-zone:hover, .drop-zone.dragover {
            border-color: var(--accent);
            background: var(--surface-hover);
        }
        .drop-zone input[type="file"] {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        .upload-icon {
            width: 44px;
            height: 44px;
            background: var(--border);
            color: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.75rem;
        }
        .drop-text-primary {
            font-size: 0.92rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.25rem;
        }
        .drop-text-secondary {
            font-size: 0.75rem;
            color: var(--text-muted);
        }
        .file-preview {
            display: none;
            background: var(--surface-alt);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 0.75rem 1rem;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        .file-details {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 230px;
        }
        .file-name {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-main);
        }
        .file-meta {
            font-size: 0.74rem;
            color: var(--text-muted);
        }
        .btn-remove-file {
            background: none;
            border: none;
            color: var(--text-dim);
            cursor: pointer;
            padding: 4px;
        }
        .btn-remove-file:hover {
            color: var(--danger);
        }

        .field-label {
            display: block;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin: 1.2rem 0 0.5rem;
        }

        .mode-select {
            width: 100%;
            padding: 0.75rem 0.9rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            background: var(--surface-alt);
            font-family: inherit;
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text-main);
            outline: none;
            cursor: pointer;
        }
        .mode-select:focus {
            border-color: var(--accent);
            background: var(--surface);
        }

        /* Trap and Choke Inputs (in mm) */
        .controls-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 0.5rem;
        }
        .input-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .input-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
        }
        .input-mm-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-number {
            width: 100%;
            padding: 0.6rem 2.2rem 0.6rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            background: var(--surface-alt);
            font-family: inherit;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-main);
            outline: none;
        }
        .input-number:focus {
            border-color: var(--accent);
            background: var(--surface);
        }
        .unit-badge {
            position: absolute;
            right: 0.6rem;
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--text-muted);
            pointer-events: none;
        }

        /* DPI Radios */
        .dpi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }
        .segment-btn {
            background: var(--surface-alt);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .segment-title {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--text-muted);
        }
        .segment-desc {
            font-size: 0.68rem;
            color: var(--text-dim);
        }
        .dpi-radio-label input[type="radio"] {
            display: none;
        }
        .dpi-radio-label input[type="radio"]:checked + .segment-btn {
            background: var(--primary);
            border-color: var(--primary);
        }
        .dpi-radio-label input[type="radio"]:checked + .segment-btn .segment-title {
            color: #FFFFFF;
        }
        .dpi-radio-label input[type="radio"]:checked + .segment-btn .segment-desc {
            color: #E2E8F0;
        }
        .dpi-radio-label.disabled-option {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-main);
            margin: 1rem 0;
            cursor: pointer;
        }

        .btn-submit {
            width: 100%;
            padding: 0.85rem;
            border-radius: var(--radius-md);
            border: none;
            background: var(--primary);
            color: #FFFFFF;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.15s;
        }
        .btn-submit:hover:not(:disabled) {
            background: var(--secondary);
        }
        .btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Spinner */
        .processing-indicator {
            display: none;
            margin-top: 1rem;
            background: #F8FAFC;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1rem;
            text-align: center;
        }
        .spinner {
            width: 22px;
            height: 22px;
            border: 3px solid #E2E8F0;
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 0.5rem;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* EDITOR VIEWPORT & CANVAS */
        .viewport-panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            min-height: 600px;
        }
        .viewport-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
            margin-bottom: 1rem;
        }
        .tool-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .tool-label {
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
        }
        .color-swatch-btn {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: 2px solid #CBD5E1;
            cursor: pointer;
            transition: transform 0.15s;
        }
        .color-swatch-btn:hover, .color-swatch-btn.active {
            transform: scale(1.15);
            border-color: var(--primary);
        }
        .btn-tool {
            background: var(--surface-alt);
            border: 1px solid var(--border);
            color: var(--text-main);
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
        }
        .btn-tool:hover {
            background: var(--surface-hover);
        }
        .btn-tool.active {
            background: var(--primary);
            color: #FFFFFF;
            border-color: var(--primary);
        }

        /* Canvas Workspace */
        .canvas-stage {
            flex: 1;
            position: relative;
            background: #0F172A; /* Default dark garment */
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 420px;
        }
        .canvas-stage.light-garment {
            background: #FFFFFF;
            border-color: #CBD5E1;
        }
        .canvas-stage.navy-garment {
            background: #1E293B;
            border-color: #334155;
        }
        .canvas-stage.red-garment {
            background: #991B1B;
            border-color: #B91C1C;
        }
        #editorCanvas {
            max-width: 95%;
            max-height: 95%;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
            border-radius: 4px;
            background: transparent;
        }

        /* Channel Manager Inspector */
        .channel-manager {
            margin-top: 1.25rem;
            background: var(--surface-alt);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1rem;
        }
        .channel-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }
        .channel-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .channel-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 8px 12px;
            transition: border-color 0.15s;
        }
        .channel-row:hover {
            border-color: var(--accent);
        }
        .channel-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .channel-color-dot {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        .channel-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-main);
        }
        .channel-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-channel-toggle {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            color: var(--accent);
        }
        .btn-channel-toggle.muted {
            color: var(--text-dim);
            background: var(--surface-alt);
        }
        .btn-channel-solo {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            color: var(--text-muted);
        }
        .btn-channel-solo:hover {
            background: var(--surface-hover);
            color: var(--text-main);
        }
        .btn-channel-dl {
            text-decoration: none;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
        }
        .btn-channel-dl:hover {
            background: var(--surface-hover);
            color: var(--text-main);
        }

        /* Export Bar */
        .export-actions {
            margin-top: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }
        .btn-export-zip {
            background: var(--primary);
            color: #FFFFFF;
            padding: 0.75rem 1.25rem;
            border-radius: var(--radius-md);
            font-size: 0.88rem;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.15s;
        }
        .btn-export-zip:hover {
            background: var(--secondary);
        }

        .footer {
            margin-top: 3rem;
            text-align: center;
            font-size: 0.82rem;
            color: var(--text-muted);
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
        <a href="{{ route('separation.index') }}" class="nav-item active">Color Separation</a>
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
            @if (Auth::user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="user-pill" style="border-color:rgba(234,179,8,0.35);color:#fde047;">
                    🛡️ Admin Panel
                </a>
            @endif
            <a href="{{ route('dashboard') }}" class="user-pill">
                <span>{{ Auth::user()->name }}</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn-nav-logout" title="Keluar">Keluar</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="btn-nav-login">Masuk / Daftar</a>
        @endauth
    </div>
</nav>

<div class="main-container">
    {{-- Header --}}
    <div class="header">
        <div class="badge-dkv">ProDesigner-Inspired Prepress Engine</div>
        <h1 class="title">Screen Print Separation Editor (Color Separation)</h1>
        <p class="subtitle">Editor separasi film sablon digital interaktif. Pisahkan desain menjadi film positif murni (CMYK, RGB, Luminosity / Grayscale, White Underbase dengan Choke mm, Color Match, Outline) siap cetak.</p>
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
                    <a href="{{ route('pricing.index') }}" class="upgrade-link">Upgrade Paket & Kuota &rarr;</a>
                @endif
            @else
                <a href="{{ route('register') }}" class="upgrade-link">Daftar Akun Gratis &rarr;</a>
            @endauth
        </div>
    </div>

    {{-- System / Engine Notice --}}
    <div class="notice-box">
        <div class="notice-content">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <strong>Engine Status:</strong>
                PDF (halaman pertama saja via Poppler) &bull; PNG / JPG (PHP GD) &bull; CorelDRAW CDR:
                @if (!empty($cdrEngine['available']))
                    <span class="engine-badge active">Engine Terpasang (Inkscape CLI)</span>
                @else
                    <span class="engine-badge disabled">Membutuhkan Ekspor PDF/PNG</span>
                @endif
            </div>
        </div>
        <div style="font-size: 0.78rem; color: var(--text-muted);">
            DPI: Free/Student 150/300 &bull; 600 DPI mulai Paket Pro
        </div>
    </div>

    @if (session('error'))
        <div class="alert-error">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    @if (session('success'))
        <div class="alert-success">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <div class="editor-grid">
        {{-- LEFT PANEL: Separation Setup Form --}}
        <div class="panel-card">
            <h2 style="font-size: 1.1rem; font-weight: 800; color: var(--primary); margin-bottom: 1rem;">Setup Separasi</h2>

            <form id="separationForm" action="{{ route('separation.process') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- File Dropzone --}}
                <div class="drop-zone" id="dropZone">
                    <input type="file" name="file" id="fileInput" accept="application/pdf,image/png,image/jpeg,.pdf,.png,.jpg,.jpeg,.cdr" required>
                    <div class="upload-icon">
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="drop-text-primary">Pilih atau Drag & Drop Artwork</div>
                    <div class="drop-text-secondary">PDF, PNG, JPG, atau CorelDRAW (.CDR)</div>
                </div>

                <div class="file-preview" id="filePreview">
                    <div class="file-details">
                        <div class="file-name" id="fileNameText">file.png</div>
                        <div class="file-meta" id="fileSizeText">0 KB</div>
                    </div>
                    <button type="button" class="btn-remove-file" id="btnRemoveFile" title="Hapus file">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Separation Mode --}}
                <label class="field-label" for="modeSelect">Mode Separasi</label>
                <select name="mode" id="modeSelect" class="mode-select">
                    <option value="cmyk" {{ old('mode', $manifest['mode'] ?? 'cmyk') == 'cmyk' ? 'selected' : '' }}>CMYK Process (4 Channels C, M, Y, K)</option>
                    <option value="underbase" {{ old('mode', $manifest['mode'] ?? '') == 'underbase' ? 'selected' : '' }}>White Underbase (Dasar Putih Kaos Gelap)</option>
                    <option value="spot" {{ old('mode', $manifest['mode'] ?? '') == 'spot' ? 'selected' : '' }}>Spot Color Match (Eksperimental)</option>
                    <option value="grayscale" {{ old('mode', $manifest['mode'] ?? '') == 'grayscale' ? 'selected' : '' }}>Luminosity / Grayscale (1 Tonal Film)</option>
                    <option value="outline" {{ old('mode', $manifest['mode'] ?? '') == 'outline' ? 'selected' : '' }}>Contour & Outline (Keyline Sablon)</option>
                    <option value="rgb" {{ old('mode', $manifest['mode'] ?? '') == 'rgb' ? 'selected' : '' }}>RGB Reference (3 Channels)</option>
                </select>

                {{-- Technical Trap & Choke Controls (in Millimeters) --}}
                <label class="field-label">Prepress Trapping & Choke (mm)</label>
                <div class="controls-row">
                    <div class="input-group">
                        <span class="input-label" title="Choke: Menyusutkan batas tinta dasaran putih underbase ke dalam agar tidak peaking keluar">Choke (Underbase)</span>
                        <div class="input-mm-wrapper">
                            <input type="number" name="choke_mm" id="chokeMmInput" class="input-number" step="0.1" min="0" max="5.0" value="{{ old('choke_mm', '0.5') }}">
                            <span class="unit-badge">mm</span>
                        </div>
                    </div>
                    <div class="input-group">
                        <span class="input-label" title="Trap: Melebarkan batas antar warna tinta agar tidak timbul celah regristrasi cetak">Trap (Overlapping)</span>
                        <div class="input-mm-wrapper">
                            <input type="number" name="trap_mm" id="trapMmInput" class="input-number" step="0.1" min="0" max="5.0" value="{{ old('trap_mm', '0.2') }}">
                            <span class="unit-badge">mm</span>
                        </div>
                    </div>
                </div>

                {{-- Spot Colors Count (Only visible when spot mode selected) --}}
                <div id="spotColorsGroup" style="display: none; margin-top: 0.75rem;">
                    <span class="input-label">Jumlah Warna Spot (2 - 8 Warna):</span>
                    <input type="number" name="spot_colors" class="input-number" min="2" max="8" value="{{ old('spot_colors', '4') }}" style="margin-top: 4px;">
                </div>

                {{-- DPI Selection --}}
                <label class="field-label">Resolusi Output (DPI)</label>
                <div class="dpi-grid">
                    <label class="dpi-radio-label">
                        <input type="radio" name="dpi" value="150" {{ old('dpi', $manifest['dpi'] ?? '300') == '150' ? 'checked' : '' }}>
                        <div class="segment-btn">
                            <span class="segment-title">150</span>
                            <span class="segment-desc">Preview</span>
                        </div>
                    </label>
                    <label class="dpi-radio-label">
                        <input type="radio" name="dpi" value="300" {{ old('dpi', $manifest['dpi'] ?? '300') == '300' ? 'checked' : '' }}>
                        <div class="segment-btn">
                            <span class="segment-title">300</span>
                            <span class="segment-desc">Standar Sablon</span>
                        </div>
                    </label>
                    @php $is600Allowed = in_array(600, $allowedDpis ?? [150, 300]); @endphp
                    <label class="dpi-radio-label {{ !$is600Allowed ? 'disabled-option' : '' }}" title="{{ !$is600Allowed ? '600 DPI Ultra Presisi tersedia mulai Paket Pro & Unlimited' : '600 DPI Ultra Presisi' }}">
                        <input type="radio" name="dpi" value="600" {{ old('dpi', $manifest['dpi'] ?? '') == '600' && $is600Allowed ? 'checked' : '' }} {{ !$is600Allowed ? 'disabled' : '' }}>
                        <div class="segment-btn">
                            <span class="segment-title">600</span>
                            <span class="segment-desc">{{ $is600Allowed ? 'Ultra Presisi' : 'Mulai Paket Pro' }}</span>
                        </div>
                    </label>
                </div>

                {{-- Registration Marks Checkbox --}}
                <label class="checkbox-label">
                    <input type="checkbox" name="registration_marks" value="1" {{ old('registration_marks', '1') == '1' ? 'checked' : '' }}>
                    <span>Tambahkan Tanda Pas / Registration Marks (⊕)</span>
                </label>

                {{-- Submit Button --}}
                <button type="submit" class="btn-submit" id="btnSubmit" disabled>
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                    <span>Jalankan Separasi Sablon</span>
                </button>

                <div class="processing-indicator" id="processingIndicator">
                    <div class="spinner"></div>
                    <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);">Memproses engine separasi...</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">Menghitung densitas film & trapping (mm).</div>
                </div>
            </form>
        </div>

        {{-- RIGHT PANEL: Interactive Canvas Editor & Channel Inspector --}}
        <div class="viewport-panel">
            @if ($manifest)
                <div class="viewport-toolbar">
                    <div class="tool-group">
                        <span class="tool-label">Garment:</span>
                        <button type="button" class="color-swatch-btn active" style="background: #0F172A;" data-garment="dark" title="Kaos Hitam"></button>
                        <button type="button" class="color-swatch-btn" style="background: #FFFFFF;" data-garment="light" title="Kaos Putih"></button>
                        <button type="button" class="color-swatch-btn" style="background: #1E293B;" data-garment="navy" title="Kaos Navy"></button>
                        <button type="button" class="color-swatch-btn" style="background: #991B1B;" data-garment="red" title="Kaos Merah"></button>
                    </div>

                    <div class="tool-group">
                        <span class="tool-label">View:</span>
                        <button type="button" class="btn-tool active" id="btnViewComposite">Composite Cetak</button>
                        <button type="button" class="btn-tool" id="btnViewOriginal">Artwork Asli</button>
                    </div>

                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted);">
                        Dimensi: {{ $manifest['width'] }} &times; {{ $manifest['height'] }} px ({{ round($manifest['width'] * 25.4 / $manifest['dpi'], 1) }} &times; {{ round($manifest['height'] * 25.4 / $manifest['dpi'], 1) }} mm)
                    </div>
                </div>

                {{-- Interactive Multi-layer Canvas --}}
                <div class="canvas-stage" id="canvasStage">
                    <canvas id="editorCanvas" width="{{ $manifest['width'] }}" height="{{ $manifest['height'] }}"></canvas>
                </div>

                {{-- Channel Manager --}}
                <div class="channel-manager">
                    <div class="channel-header-row">
                        <span style="font-size: 0.82rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--primary);">
                            Channel Inspector ({{ count($manifest['channels']) }} Screen Films)
                        </span>
                        <button type="button" id="btnToggleAllChannels" style="background:none; border:none; color: var(--accent); font-size: 0.78rem; font-weight: 700; cursor: pointer;">
                            Toggle All
                        </button>
                    </div>

                    <div class="channel-list" id="channelListContainer">
                        @foreach ($manifest['channels'] as $chKey => $chInfo)
                            <div class="channel-row" data-channel-key="{{ $chKey }}">
                                <div class="channel-left">
                                    <div class="channel-color-dot" style="background-color: {{ $chInfo['color'] ?? '#111827' }};"></div>
                                    <span class="channel-title">{{ $chInfo['label'] }}</span>
                                </div>
                                <div class="channel-right">
                                    <button type="button" class="btn-channel-toggle" data-channel="{{ $chKey }}">MATA: ON</button>
                                    <button type="button" class="btn-channel-solo" data-channel="{{ $chKey }}">Solo Film</button>
                                    <a href="{{ route('separation.download', ['token' => $token, 'channel' => $chKey]) }}" class="btn-channel-dl" title="Download Film Grayscale PNG">Unduh Film</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Export Actions --}}
                <div class="export-actions">
                    <a href="{{ route('separation.download', ['token' => $token, 'channel' => 'original']) }}" class="btn-tool" style="padding: 0.75rem 1.25rem;">
                        Unduh Artwork Asli
                    </a>
                    @if (!empty($manifest['zipFileName']))
                        <a href="{{ route('separation.download', ['token' => $token, 'channel' => 'zip']) }}" class="btn-export-zip">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                            <span>Ekspor Semua Film Lengkap (.ZIP)</span>
                        </a>
                    @else
                        @php $onlyKey = array_key_first($manifest['channels']); @endphp
                        <a href="{{ route('separation.download', ['token' => $token, 'channel' => $onlyKey]) }}" class="btn-export-zip">
                            Unduh Film Grayscale (.PNG)
                        </a>
                    @endif
                </div>

            @else
                <div style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; color: var(--text-muted); padding: 3rem;">
                    <svg width="54" height="54" fill="none" viewBox="0 0 24 24" stroke="#94A3B8" style="margin-bottom: 1rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.35rem;">Belum Ada Artwork yang Diproses</h3>
                    <p style="font-size: 0.88rem; max-width: 440px;">Pilih dokumen PDF atau citra sablon di panel kiri untuk membuka workspace interaktif multi-channel screen separation.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="footer">
        MsterCV Tools DKV &bull; Screen Print Prepress Engine &bull; Standalone Zero-Gradient DKV SaaS
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const fileInput = document.getElementById('fileInput');
        const dropZone = document.getElementById('dropZone');
        const filePreview = document.getElementById('filePreview');
        const fileNameText = document.getElementById('fileNameText');
        const fileSizeText = document.getElementById('fileSizeText');
        const btnRemoveFile = document.getElementById('btnRemoveFile');
        const btnSubmit = document.getElementById('btnSubmit');
        const separationForm = document.getElementById('separationForm');
        const processingIndicator = document.getElementById('processingIndicator');
        const modeSelect = document.getElementById('modeSelect');
        const spotColorsGroup = document.getElementById('spotColorsGroup');

        // Mode change handler
        if (modeSelect && spotColorsGroup) {
            const toggleSpot = () => {
                spotColorsGroup.style.display = (modeSelect.value === 'spot') ? 'block' : 'none';
            };
            modeSelect.addEventListener('change', toggleSpot);
            toggleSpot();
        }

        function formatBytes(bytes) {
            if (!bytes || bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function updateFileState(file) {
            if (file) {
                fileNameText.textContent = file.name;
                fileSizeText.textContent = formatBytes(file.size);
                filePreview.style.display = 'flex';
                dropZone.style.display = 'none';
                btnSubmit.removeAttribute('disabled');
            } else {
                filePreview.style.display = 'none';
                dropZone.style.display = 'block';
                btnSubmit.setAttribute('disabled', 'true');
            }
        }

        fileInput.addEventListener('change', (e) => {
            if (e.target.files && e.target.files[0]) {
                updateFileState(e.target.files[0]);
            }
        });

        // Drag & drop
        ['dragenter', 'dragover'].forEach(name => {
            dropZone.addEventListener(name, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.add('dragover');
            });
        });
        ['dragleave', 'drop'].forEach(name => {
            dropZone.addEventListener(name, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.remove('dragover');
            });
        });
        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files.length > 0) {
                fileInput.files = files;
                updateFileState(files[0]);
            }
        });

        btnRemoveFile.addEventListener('click', () => {
            fileInput.value = '';
            updateFileState(null);
        });

        separationForm.addEventListener('submit', () => {
            btnSubmit.setAttribute('disabled', 'true');
            btnSubmit.style.display = 'none';
            processingIndicator.style.display = 'block';
        });

        // ============================================================
        // INTERACTIVE CLIENT-SIDE MULTI-LAYER CANVAS COMPOSITING
        // ============================================================
        @if ($manifest)
            const canvas = document.getElementById('editorCanvas');
            const ctx = canvas ? canvas.getContext('2d') : null;
            const canvasStage = document.getElementById('canvasStage');
            const btnViewComposite = document.getElementById('btnViewComposite');
            const btnViewOriginal = document.getElementById('btnViewOriginal');
            const token = "{{ $token ?? '' }}";

            // Garment Color Swatches
            const garmentBtns = document.querySelectorAll('.color-swatch-btn');
            garmentBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    garmentBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    const garment = btn.getAttribute('data-garment');
                    canvasStage.className = 'canvas-stage ' + garment + '-garment';
                });
            });

            // Channel Data
            const channels = @json($manifest['channels']);
            const channelKeys = Object.keys(channels);
            const channelVisibility = {};
            const channelImages = {};
            let originalImage = new Image();
            originalImage.src = "{{ route('separation.preview', ['token' => $token, 'channel' => 'original']) }}";
            let viewMode = 'composite'; // 'composite' | 'original' | soloKey

            // Preload all channel images
            channelKeys.forEach(k => {
                channelVisibility[k] = true;
                const img = new Image();
                img.src = "{{ url('/separation/preview') }}/" + token + "/" + k;
                img.onload = () => renderCanvas();
                channelImages[k] = img;
            });
            originalImage.onload = () => renderCanvas();

            function hexToRgb(hex) {
                const clean = hex.replace('#', '');
                const bigint = parseInt(clean, 16);
                return {
                    r: (bigint >> 16) & 255,
                    g: (bigint >> 8) & 255,
                    b: bigint & 255
                };
            }

            function renderCanvas() {
                if (!ctx || !canvas) return;
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                if (viewMode === 'original') {
                    if (originalImage.complete) {
                        ctx.drawImage(originalImage, 0, 0, canvas.width, canvas.height);
                    }
                    return;
                }

                if (viewMode.startsWith('solo_')) {
                    const soloKey = viewMode.replace('solo_', '');
                    const img = channelImages[soloKey];
                    if (img && img.complete) {
                        // Render raw film positive (grayscale / black ink on white)
                        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    }
                    return;
                }

                // COMPOSITE SIMULATION (Simulasi Tinta Sablon di atas Kaos)
                const offscreen = document.createElement('canvas');
                offscreen.width = canvas.width;
                offscreen.height = canvas.height;
                const offCtx = offscreen.getContext('2d');

                channelKeys.forEach(k => {
                    if (!channelVisibility[k]) return;
                    const img = channelImages[k];
                    if (!img || !img.complete) return;

                    // Draw grayscale mask into offscreen
                    offCtx.clearRect(0, 0, offscreen.width, offscreen.height);
                    offCtx.drawImage(img, 0, 0, offscreen.width, offscreen.height);

                    const imgData = offCtx.getImageData(0, 0, offscreen.width, offscreen.height);
                    const data = imgData.data;
                    const tint = hexToRgb(channels[k].color || '#111827');
                    const isUnderbase = (k === 'underbase' || channels[k].color === '#FFFFFF');

                    for (let i = 0; i < data.length; i += 4) {
                        // In film positive: 0 = 100% ink, 255 = 0% ink
                        const density = (255 - data[i]) / 255.0; // 1.0 (ink) to 0.0 (no ink)
                        data[i] = tint.r;
                        data[i + 1] = tint.g;
                        data[i + 2] = tint.b;
                        data[i + 3] = Math.round(density * 255);
                    }

                    offCtx.putImageData(imgData, 0, 0);

                    // Composite ink onto stage canvas
                    if (isUnderbase) {
                        ctx.globalCompositeOperation = 'source-over';
                    } else {
                        // Screen print inks layer on top of underbase/garment
                        ctx.globalCompositeOperation = 'source-over';
                    }
                    ctx.drawImage(offscreen, 0, 0);
                });

                ctx.globalCompositeOperation = 'source-over';
            }

            // View switch buttons
            if (btnViewComposite && btnViewOriginal) {
                btnViewComposite.addEventListener('click', () => {
                    viewMode = 'composite';
                    btnViewComposite.classList.add('active');
                    btnViewOriginal.classList.remove('active');
                    renderCanvas();
                });

                btnViewOriginal.addEventListener('click', () => {
                    viewMode = 'original';
                    btnViewOriginal.classList.add('active');
                    btnViewComposite.classList.remove('active');
                    renderCanvas();
                });
            }

            // Channel visibility and solo buttons
            const toggleBtns = document.querySelectorAll('.btn-channel-toggle');
            toggleBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const ch = btn.getAttribute('data-channel');
                    channelVisibility[ch] = !channelVisibility[ch];
                    btn.textContent = channelVisibility[ch] ? 'MATA: ON' : 'MATA: OFF';
                    btn.classList.toggle('muted', !channelVisibility[ch]);
                    viewMode = 'composite';
                    btnViewComposite.classList.add('active');
                    btnViewOriginal.classList.remove('active');
                    renderCanvas();
                });
            });

            const soloBtns = document.querySelectorAll('.btn-channel-solo');
            soloBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const ch = btn.getAttribute('data-channel');
                    if (viewMode === 'solo_' + ch) {
                        viewMode = 'composite';
                        btnViewComposite.classList.add('active');
                    } else {
                        viewMode = 'solo_' + ch;
                        btnViewComposite.classList.remove('active');
                    }
                    btnViewOriginal.classList.remove('active');
                    renderCanvas();
                });
            });

            const btnToggleAll = document.getElementById('btnToggleAllChannels');
            if (btnToggleAll) {
                let allOn = true;
                btnToggleAll.addEventListener('click', () => {
                    allOn = !allOn;
                    channelKeys.forEach(k => {
                        channelVisibility[k] = allOn;
                    });
                    toggleBtns.forEach(b => {
                        b.textContent = allOn ? 'MATA: ON' : 'MATA: OFF';
                        b.classList.toggle('muted', !allOn);
                    });
                    viewMode = 'composite';
                    renderCanvas();
                });
            }
        @endif
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
</script>

</body>
</html>
