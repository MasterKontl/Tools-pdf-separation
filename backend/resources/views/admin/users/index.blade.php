@extends('admin.layout')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
        <form method="GET" action="{{ route('admin.users.index') }}" style="display:flex;gap:10px;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." style="padding:0.6rem 1rem;background:rgba(0,0,0,0.3);border:1px solid var(--bg-card-border);border-radius:var(--radius-md);color:#fff;font-family:inherit;outline:none;">
            <button type="submit" class="btn-action btn-primary">Cari</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Pengguna</th>
                <th>Email</th>
                <th>Role</th>
                <th>Paket</th>
                <th>Entitlement Limit</th>
                <th>Penggunaan Hari Ini</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge {{ $user->isAdmin() ? 'badge-danger' : 'badge-success' }}">{{ $user->role }}</span></td>
                    <td>{{ $user->plan?->name ?? 'Free' }}</td>
                    <td>
                        @if ($user->entitlement['unlimited'])
                            <span style="color:#34d399;font-weight:700;">∞ Unlimited</span>
                        @else
                            <span>{{ $user->entitlement['daily_limit'] }} / hari</span>
                        @endif
                        @if ($user->daily_limit !== null)
                            <small style="color:#fbbf24;display:block;">(Custom Limit)</small>
                        @endif
                    </td>
                    <td><strong>{{ $user->today_usage }}</strong> kali</td>
                    <td>
                        <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                            {{ $user->is_active ? 'Aktif' : 'Disuspend' }}
                        </span>
                    </td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn-action btn-primary">Edit</a>

                        <form action="{{ route('admin.users.toggle-unlimited', $user->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn-action btn-warning" title="Toggle Unlimited">
                                {{ $user->unlimited ? 'Matikan ∞' : 'Set ∞' }}
                            </button>
                        </form>

                        @if (auth()->id() !== $user->id)
                            <form action="{{ route('admin.users.toggle-active', $user->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn-action btn-danger" title="Suspend/Aktifkan">
                                    {{ $user->is_active ? 'Suspend' : 'Aktifkan' }}
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top:1.5rem;">
        {{ $users->links() }}
    </div>
</div>
@endsection
