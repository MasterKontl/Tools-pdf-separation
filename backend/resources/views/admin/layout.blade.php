<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') | Tools DKV</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body: #0F172A;
            --bg-sidebar: #1E293B;
            --bg-card: #1E293B;
            --bg-card-border: #334155;
            --primary: #1E3A5F;
            --secondary: #274C77;
            --accent-primary: #3B82F6;
            --text-main: #F8FAFC;
            --text-muted: #94A3B8;
            --text-dim: #64748B;
            --radius-md: 8px;
            --radius-lg: 12px;
            --touch-target: 44px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            overflow-x: hidden;
            max-width: 100vw;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
        }
        body.overflow-hidden {
            overflow: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            min-width: 260px;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--bg-card-border);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            min-height: 100vh;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
        }
        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--bg-card-border);
        }
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.15rem;
            font-weight: 800;
            color: #fff;
            text-decoration: none;
        }
        .sidebar-close-btn {
            display: none;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: var(--radius-md);
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-muted);
            cursor: pointer;
        }
        .sidebar-close-btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
        }
        .sidebar-menu {
            list-style: none;
            padding: 1.25rem 0.75rem;
            flex: 1;
        }
        .menu-item {
            margin-bottom: 4px;
        }
        .menu-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.75rem 1rem;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: var(--radius-md);
            transition: background 0.15s, color 0.15s;
            min-height: 44px;
        }
        .menu-link:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
        }
        .menu-link.active {
            background: rgba(99, 102, 241, 0.15);
            color: #818cf8;
            font-weight: 700;
        }
        .sidebar-footer {
            padding: 1.25rem;
            border-top: 1px solid var(--bg-card-border);
        }

        /* Sidebar Overlay */
        .sidebar-overlay {
            display: none;
        }

        /* Hamburger button */
        .hamburger-menu {
            display: none;
            align-items: center;
            justify-content: center;
            width: var(--touch-target);
            height: var(--touch-target);
            min-width: 44px;
            min-height: 44px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: var(--radius-md);
            color: #fff;
            cursor: pointer;
            flex-shrink: 0;
            padding: 0;
            transition: background 0.15s;
        }
        .hamburger-menu:hover,
        .hamburger-menu:focus-visible {
            background: rgba(255, 255, 255, 0.12);
            outline: none;
        }
        .hamburger-menu svg {
            width: 22px;
            height: 22px;
            stroke: currentColor;
        }

        /* Mobile Drawer Breakpoint (<= 768px) */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                height: 100vh;
                height: 100dvh;
                width: 280px;
                max-width: 85vw;
                z-index: 1000;
                transform: translateX(-100%);
                transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 4px 0 24px rgba(0, 0, 0, 0.5);
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .sidebar-close-btn {
                display: inline-flex;
            }
            .sidebar-overlay {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.75);
                backdrop-filter: blur(4px);
                -webkit-backdrop-filter: blur(4px);
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.25s ease, visibility 0.25s ease;
                z-index: 999;
            }
            .sidebar-overlay.open {
                opacity: 1;
                visibility: visible;
            }
            .hamburger-menu {
                display: inline-flex;
            }
        }

        /* Main Content */
        .content-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            max-width: 100%;
        }
        .topbar {
            min-height: 64px;
            border-bottom: 1px solid var(--bg-card-border);
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 2rem;
            position: sticky;
            top: 0;
            z-index: 50;
            gap: 0.75rem;
        }
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 0;
        }
        .page-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-shrink: 0;
        }
        .admin-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            background: rgba(99, 102, 241, 0.2);
            border: 1px solid rgba(99, 102, 241, 0.4);
            border-radius: 6px;
            color: #a5b4fc;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .admin-user-name {
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 500;
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @media (max-width: 768px) {
            .topbar {
                padding: 0.6rem 1rem;
            }
        }
        @media (max-width: 480px) {
            .page-title {
                font-size: 0.98rem;
            }
            .admin-user-name {
                display: none;
            }
            .topbar {
                padding: 0.5rem 0.75rem;
            }
        }

        .page-body {
            flex: 1;
            padding: 1.5rem 2rem;
            max-width: 100%;
            min-width: 0;
        }
        @media (max-width: 768px) {
            .page-body {
                padding: 1rem;
            }
        }
        @media (max-width: 480px) {
            .page-body {
                padding: 0.85rem 0.75rem;
            }
        }

        /* Card & Responsive Tables */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--bg-card-border);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            max-width: 100%;
            overflow: hidden;
        }
        @media (max-width: 640px) {
            .card {
                padding: 1rem 0.85rem;
                margin-bottom: 1rem;
            }
        }

        .responsive-table,
        .table-responsive {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 0.5rem;
            border-radius: var(--radius-md);
        }
        .responsive-table table,
        .table-responsive table {
            min-width: 600px;
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.88rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.88rem;
        }
        th {
            padding: 0.85rem 1rem;
            background: rgba(0, 0, 0, 0.3);
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--bg-card-border);
            white-space: nowrap;
        }
        td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            color: var(--text-main);
            vertical-align: middle;
        }
        tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        /* Action Buttons */
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 0.5rem 0.85rem;
            min-height: 38px;
            border-radius: var(--radius-md);
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: opacity 0.15s, background 0.15s;
            line-height: 1.2;
            white-space: nowrap;
        }
        @media (max-width: 640px) {
            .btn-action {
                min-height: 42px;
                padding: 0.55rem 0.9rem;
            }
        }
        .btn-action:hover {
            opacity: 0.9;
        }
        .btn-primary {
            background: var(--accent-primary);
            color: #fff;
        }
        .btn-warning {
            background: rgba(234, 179, 8, 0.2);
            color: #fde047;
            border: 1px solid rgba(234, 179, 8, 0.3);
        }
        .btn-danger {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .badge-success {
            background: rgba(34, 197, 94, 0.15);
            color: #86efac;
        }
        .badge-danger {
            background: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
        }
        .badge-warning {
            background: rgba(234, 179, 8, 0.15);
            color: #fde047;
        }
        .alert-success {
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
            padding: 0.85rem 1.2rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.25rem;
            font-size: 0.9rem;
        }

        /* Form Inputs & Mobile Scale */
        input[type="text"],
        input[type="email"],
        input[type="number"],
        select,
        textarea {
            font-size: 1rem; /* Prevent auto-zoom on iOS */
            max-width: 100%;
        }

        /* Utility styles for Admin components */
        .admin-filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .admin-filter-form {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            width: 100%;
        }
        .admin-filter-form input[type="text"],
        .admin-filter-form select {
            padding: 0.6rem 1rem;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--bg-card-border);
            border-radius: var(--radius-md);
            color: #fff;
            outline: none;
            min-height: 42px;
        }
        .admin-filter-form select {
            background-color: #0f1420;
        }
        @media (max-width: 640px) {
            .admin-filter-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .admin-filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            .admin-filter-form input[type="text"],
            .admin-filter-form select,
            .admin-filter-form button {
                width: 100%;
                min-height: 44px;
            }
        }
        .table-actions {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .price-currency-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        @media (max-width: 480px) {
            .price-currency-grid {
                grid-template-columns: 1fr;
            }
        }
        .form-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        @media (max-width: 480px) {
            .form-actions .btn-action {
                width: 100%;
                min-height: 44px;
            }
        }
        .pagination-wrapper {
            margin-top: 1.5rem;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            max-width: 100%;
        }
    </style>
</head>
<body>

{{-- Sidebar --}}
<aside class="sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="#6366f1"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            <span>ADMIN PANEL</span>
        </a>
        <button type="button" class="sidebar-close-btn" onclick="toggleSidebar(false)" aria-label="Tutup menu sidebar">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </div>
    <ul class="sidebar-menu">
        <li class="menu-item">
            <a href="{{ route('admin.dashboard') }}" class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                📊 Dashboard
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ route('admin.users.index') }}" class="menu-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                👥 Pengguna (Users)
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ route('admin.plans.index') }}" class="menu-link {{ request()->routeIs('admin.plans.*') ? 'active' : '' }}">
                📦 Paket (Plans)
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ route('admin.subscriptions.index') }}" class="menu-link {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}">
                📑 Langganan
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ route('admin.payments.index') }}" class="menu-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                💳 Pembayaran
            </a>
        </li>
    </ul>
    <div class="sidebar-footer">
        <a href="{{ route('converter.index') }}" class="menu-link" style="color:#818cf8;">
            &larr; Kembali ke Tools
        </a>
    </div>
</aside>

{{-- Sidebar Overlay for mobile --}}
<div class="sidebar-overlay" onclick="toggleSidebar(false)" aria-label="Tutup sidebar"></div>

{{-- Content Area --}}
<div class="content-area">
    <header class="topbar">
        <div class="topbar-left">
            <button type="button" class="hamburger-menu" onclick="toggleSidebar()" aria-label="Buka Menu Sidebar" aria-expanded="false" aria-controls="adminSidebar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            <h2 class="page-title">@yield('title', 'Admin Dashboard')</h2>
        </div>
        <div class="topbar-actions">
            <span class="admin-badge">ADMIN</span>
            <span class="admin-user-name">{{ auth()->user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn-action btn-danger" style="margin-left:4px;">Keluar</button>
            </form>
        </div>
    </header>

    <main class="page-body">
        @if (session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        @yield('content')
    </main>
</div>

<script>
    function toggleSidebar(forceState) {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const hamburger = document.querySelector('.hamburger-menu');
        const body = document.body;

        if (!sidebar || !overlay) return;

        const shouldOpen = typeof forceState === 'boolean' 
            ? forceState 
            : !sidebar.classList.contains('open');

        if (shouldOpen) {
            sidebar.classList.add('open');
            overlay.classList.add('open');
            if (hamburger) hamburger.setAttribute('aria-expanded', 'true');
            body.classList.add('overflow-hidden');
        } else {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
            if (hamburger) hamburger.setAttribute('aria-expanded', 'false');
            body.classList.remove('overflow-hidden');
        }
    }

    // Close drawer on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            toggleSidebar(false);
        }
    });

    // Reset drawer state on desktop resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            toggleSidebar(false);
        }
    });
</script>
</body>
</html>
