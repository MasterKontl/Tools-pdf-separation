@extends('admin.layout')

@section('title', 'Manajemen Paket (Plans)')

@section('content')
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
        <p style="color:var(--text-muted);font-size:0.9rem;">Daftar paket harga dan limit konversi yang tersedia di platform.</p>
        <a href="{{ route('admin.plans.create') }}" class="btn-action btn-primary" style="padding:0.6rem 1.2rem;font-size:0.85rem;">+ Tambah Paket Baru</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nama Paket</th>
                <th>Slug</th>
                <th>Daily Limit</th>
                <th>Harga (IDR)</th>
                <th>Total Pelanggan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($plans as $plan)
                <tr>
                    <td><strong>{{ $plan->name }}</strong></td>
                    <td><code>{{ $plan->slug }}</code></td>
                    <td>
                        @if ($plan->unlimited)
                            <span style="color:#34d399;font-weight:700;">∞ Unlimited</span>
                        @else
                            {{ $plan->daily_limit }} / hari
                        @endif
                    </td>
                    <td>Rp {{ number_format($plan->price, 0, ',', '.') }}</td>
                    <td>{{ $plan->users_count }} pengguna</td>
                    <td>
                        <span class="badge {{ $plan->is_active ? 'badge-success' : 'badge-danger' }}">
                            {{ $plan->is_active ? 'Aktif' : 'Non-Aktif' }}
                        </span>
                    </td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('admin.plans.edit', $plan->id) }}" class="btn-action btn-primary">Edit</a>

                        <form action="{{ route('admin.plans.toggle-active', $plan->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn-action btn-warning">
                                {{ $plan->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
