@extends('admin.layout')

@section('title', 'Dashboard Overview')

@section('content')
<style>
    .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-bottom: 2rem; }
    .metric-card {
        background: var(--bg-card); border: 1px solid var(--bg-card-border);
        border-radius: var(--radius-lg); padding: 1.25rem;
    }
    .metric-label { font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 0.4rem; }
    .metric-value { font-size: 1.85rem; font-weight: 800; color: #fff; }
    .recent-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    @media (max-width: 900px) { .recent-grid { grid-template-columns: 1fr; } }
</style>

{{-- Metrics Row --}}
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-label">Total Pengguna</div>
        <div class="metric-value">{{ $totalUsers }}</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Pengguna Aktif</div>
        <div class="metric-value">{{ $activeUsers }}</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Konversi Hari Ini</div>
        <div class="metric-value" style="color:#60a5fa;">{{ $conversionsToday }}</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Pengguna Premium</div>
        <div class="metric-value" style="color:#a78bfa;">{{ $premiumUsers }}</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Akses Unlimited</div>
        <div class="metric-value" style="color:#34d399;">{{ $unlimitedUsers }}</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Total Pendapatan</div>
        <div class="metric-value" style="color:#fbbf24;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
    </div>
</div>

<div class="recent-grid">
    {{-- Recent Users --}}
    <div class="card">
        <h3 style="font-size:1.1rem;font-weight:700;color:#fff;margin-bottom:1rem;">Pengguna Terbaru</h3>
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Paket</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recentUsers as $u)
                    <tr>
                        <td><strong>{{ $u->name }}</strong></td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->plan?->name ?? 'Free' }}</td>
                        <td><span class="badge {{ $u->isAdmin() ? 'badge-danger' : 'badge-success' }}">{{ $u->role }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Recent Payments --}}
    <div class="card">
        <h3 style="font-size:1.1rem;font-weight:700;color:#fff;margin-bottom:1rem;">Transaksi Terakhir</h3>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Pengguna</th>
                    <th>Nominal</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recentPayments as $p)
                    <tr>
                        <td><code>{{ $p->provider_reference }}</code></td>
                        <td>{{ $p->user->name ?? 'User' }}</td>
                        <td>Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                        <td><span class="badge {{ $p->status === 'PAID' ? 'badge-success' : 'badge-danger' }}">{{ $p->status }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
