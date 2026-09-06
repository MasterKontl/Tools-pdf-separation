@extends('admin.layout')

@section('title', 'Riwayat Transaksi Pembayaran')

@section('content')
<div class="card">
    <div class="admin-filter-bar">
        <form method="GET" action="{{ route('admin.payments.index') }}" class="admin-filter-form">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Order ID / Pengguna..." style="flex:1;min-width:180px;">
            <select name="status" style="min-width:160px;">
                <option value="">-- Semua Status --</option>
                <option value="PAID" {{ request('status') === 'PAID' ? 'selected' : '' }}>PAID (Lunas)</option>
                <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>PENDING</option>
                <option value="FAILED" {{ request('status') === 'FAILED' ? 'selected' : '' }}>FAILED</option>
            </select>
            <button type="submit" class="btn-action btn-primary" style="min-height:42px;">Filter</button>
        </form>
    </div>

    <div class="responsive-table">
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Pengguna</th>
                    <th>Paket</th>
                    <th>Nominal</th>
                    <th>Provider</th>
                    <th>Status</th>
                    <th>Tanggal Bayar</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr>
                        <td><code>{{ $payment->provider_reference }}</code></td>
                        <td>
                            <strong>{{ $payment->user->name ?? 'User' }}</strong><br>
                            <small style="color:var(--text-dim);">{{ $payment->user->email ?? '' }}</small>
                        </td>
                        <td>{{ $payment->metadata['plan_name'] ?? 'Langganan' }}</td>
                        <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                        <td><span class="badge" style="background:rgba(99,102,241,0.15);color:#a5b4fc;">{{ strtoupper($payment->provider) }}</span></td>
                        <td>
                            <span class="badge {{ $payment->status === 'PAID' ? 'badge-success' : ($payment->status === 'PENDING' ? 'badge-warning' : 'badge-danger') }}">
                                {{ $payment->status }}
                            </span>
                        </td>
                        <td>{{ $payment->paid_at ? $payment->paid_at->translatedFormat('d M Y, H:i') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:2rem;color:var(--text-dim);">Tidak ada transaksi pembayaran ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $payments->links() }}
    </div>
</div>
@endsection
