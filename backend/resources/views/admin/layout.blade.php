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
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
        }
        /* Sidebar */
        .sidebar {
            width: 260px; background: var(--bg-sidebar); border-right: 1px solid var(--bg-card-border);
            display: flex; flex-direction: column; flex-shrink: 0; min-height: 100vh;
        }
        .sidebar-brand {
            padding: 1.5rem; display: flex; align-items: center; gap: 10px;
            font-size: 1.15rem; font-weight: 800; color: #fff; text-decoration: none;
            border-bottom: 1px solid var(--bg-card-border);
        }
        .sidebar-menu { list-style: none; padding: 1.25rem 0.75rem; flex: 1; }
        .menu-item { margin-bottom: 4px; }
        .menu-link {
            display: flex; align-items: center; gap: 12px; padding: 0.75rem 1rem;
            color: var(--text-muted); text-decoration: none; font-size: 0.9rem; font-weight: 600;
            border-radius: var(--radius-md); transition: background 0.15s, color 0.15s;
        }
        .menu-link:hover { background: rgba(255, 255, 255, 0.05); color: #fff; }
        .menu-link.active { background: rgba(99, 102, 241, 0.15); color: #818cf8; font-weight: 700; }
        .sidebar-footer { padding: 1.25rem; border-top: 1px solid var(--bg-card-border); }

        /* Main Content */
        .content-area { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .topbar {
            height: 64px; border-bottom: 1px solid var(--bg-card-border);
            background: rgba(10, 14, 23, 0.8); backdrop-filter: blur(12px);
            display: flex; align-items: center; justify-content: space-between; padding: 0 2rem;
        }
        .page-title { font-size: 1.25rem; font-weight: 800; color: #fff; }
        .topbar-actions { display: flex; align-items: center; gap: 1rem; }
        .admin-badge {
            font-size: 0.75rem; padding: 4px 10px; border-radius: 999px;
            background: rgba(234, 179, 8, 0.15); border: 1px solid rgba(234, 179, 8, 0.3);
            color: #fde047; font-weight: 700; text-transform: uppercase;
        }
        .page-body { padding: 2rem; flex: 1; }

        /* Card & Tables */
        .card {
            background: var(--bg-card); border: 1px solid var(--bg-card-border);
            border-radius: var(--radius-lg); padding: 1.5rem; margin-bottom: 1.5rem;
        }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem; }
        th { padding: 0.85rem 1rem; background: rgba(0,0,0,0.3); color: var(--text-muted); font-size: 0.78rem; text-transform: uppercase; font-weight: 700; border-bottom: 1px solid var(--bg-card-border); }
        td { padding: 0.85rem 1rem; border-bottom: 1px solid rgba(255, 255, 255, 0.04); color: var(--text-main); }
        tr:hover td { background: rgba(255, 255, 255, 0.02); }
        .btn-action {
            display: inline-flex; align-items: center; gap: 4px; padding: 5px 10px;
            border-radius: 6px; font-size: 0.78rem; font-weight: 600; cursor: pointer;
            text-decoration: none; border: none; transition: opacity 0.15s;
        }
        .btn-primary { background: var(--accent-primary); color: #fff; }
        .btn-warning { background: rgba(234, 179, 8, 0.2); color: #fde047; border: 1px solid rgba(234, 179, 8, 0.3); }
        .btn-danger { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; }
        .badge-success { background: rgba(34, 197, 94, 0.15); color: #86efac; }
        .badge-danger { background: rgba(239, 68, 68, 0.15); color: #fca5a5; }
        .alert-success { background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3); color: #86efac; padding: 0.85rem 1.2rem; border-radius: var(--radius-md); margin-bottom: 1.25rem; font-size: 0.9rem; }
    </style>
</head>
<body>

{{-- Sidebar --}}
<aside class="sidebar">
    <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="#6366f1"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
        <span>ADMIN PANEL</span>
    </a>
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

{{-- Content Area --}}
<div class="content-area">
    <header class="topbar">
        <h2 class="page-title">@yield('title', 'Admin Dashboard')</h2>
        <div class="topbar-actions">
            <span class="admin-badge">ADMIN</span>
            <span style="font-size:0.88rem;color:var(--text-muted);">{{ auth()->user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn-action btn-danger" style="margin-left:8px;">Keluar</button>
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

</body>
</html>
