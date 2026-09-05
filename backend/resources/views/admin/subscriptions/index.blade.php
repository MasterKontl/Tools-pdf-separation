@extends('admin.layout')

@section('title', 'Daftar Langganan Pengguna')

@section('content')
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
        <form method="GET" action="{{ route('admin.subscriptions.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." style="padding:0.6rem 1rem;background:rgba(0,0,0,0.3);border:1px solid var(--bg-card-border);border-radius:var(--radius-md);color:#fff;outline:none;">
            <select name="status" style="padding:0.6rem 1rem;background:#0f1420;border:1px solid var(--bg-card-border);border-radius:var(--radius-md);color:#fff;outline:none;">
                <option value="">-- Semua Status --</option>
                <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                <option value="EXPIRED" {{ request('status') === 'EXPIRED' ? 'selected' : '' }}>EXPIRED</option>
                <option value="CANCELLED" {{ request('status') === 'CANCELLED' ? 'selected' : '' }}>CANCELLED</option>
            </select>
            <button type="submit" class="btn-action btn-primary">Filter</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Pengguna</th>
                <th>Paket</th>
                <th>Status</th>
                <th>Mulai</th>
                <th>Berakhir (Expires At)</th>
                <th>Referensi Provider</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($subscriptions as $sub)
                <tr>
                    <td>
                        <strong>{{ $sub->user->name ?? 'User' }}</strong><br>
                        <small style="color:var(--text-dim);">{{ $sub->user->email ?? '' }}</small>
                    </td>
                    <td>{{ $sub->plan->name ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $sub->status === 'ACTIVE' ? 'badge-success' : 'badge-danger' }}">
                            {{ $sub->status }}
                        </span>
                    </td>
                    <td>{{ $sub->starts_at ? $sub->starts_at->translatedFormat('d M Y') : '-' }}</td>
                    <td>
                        @if ($sub->expires_at)
                            <span style="{{ $sub->expires_at->isPast() ? 'color:#f87171;' : 'color:#34d399;' }}">
                                {{ $sub->expires_at->translatedFormat('d M Y, H:i') }}
                            </span>
                        @else
                            <span style="color:#34d399;">∞ Selamanya</span>
                        @endif
                    </td>
                    <td><code>{{ $sub->provider_reference ?: '-' }}</code></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:2rem;color:var(--text-dim);">Belum ada langganan pengguna.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:1.5rem;">
        {{ $subscriptions->links() }}
    </div>
</div>
@endsection
