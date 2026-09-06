@extends('admin.layout')

@section('title', 'Edit Pengguna: ' . $user->name)

@section('content')
<div class="card" style="max-width:640px;">
    @if ($errors->any())
        <div style="background:rgba(239,68,68,0.15);color:#fca5a5;padding:1rem;border-radius:8px;margin-bottom:1.5rem;">
            @foreach ($errors->all() as $err)
                <div>{{ $err }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-size:0.85rem;font-weight:600;color:var(--text-muted);margin-bottom:0.4rem;">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required style="width:100%;padding:0.75rem 1rem;background:rgba(0,0,0,0.3);border:1px solid var(--bg-card-border);border-radius:8px;color:#fff;outline:none;">
        </div>

        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-size:0.85rem;font-weight:600;color:var(--text-muted);margin-bottom:0.4rem;">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required style="width:100%;padding:0.75rem 1rem;background:rgba(0,0,0,0.3);border:1px solid var(--bg-card-border);border-radius:8px;color:#fff;outline:none;">
        </div>

        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-size:0.85rem;font-weight:600;color:var(--text-muted);margin-bottom:0.4rem;">Role Pengguna</label>
            <select name="role" style="width:100%;padding:0.75rem 1rem;background:#0f1420;border:1px solid var(--bg-card-border);border-radius:8px;color:#fff;outline:none;">
                <option value="USER" {{ old('role', $user->role) === 'USER' ? 'selected' : '' }}>USER (Pengguna Biasa)</option>
                <option value="ADMIN" {{ old('role', $user->role) === 'ADMIN' ? 'selected' : '' }}>ADMIN (Administrator)</option>
            </select>
        </div>

        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-size:0.85rem;font-weight:600;color:var(--text-muted);margin-bottom:0.4rem;">Paket Langganan (Plan)</label>
            <select name="plan_id" style="width:100%;padding:0.75rem 1rem;background:#0f1420;border:1px solid var(--bg-card-border);border-radius:8px;color:#fff;outline:none;">
                <option value="">-- Tanpa Paket (Default Free 3/hari) --</option>
                @foreach ($plans as $p)
                    <option value="{{ $p->id }}" {{ old('plan_id', $user->plan_id) == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} ({{ $p->unlimited ? 'Unlimited' : $p->daily_limit . '/hari' }})
                    </option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-size:0.85rem;font-weight:600;color:var(--text-muted);margin-bottom:0.4rem;">Custom Daily Limit (Override Paket)</label>
            <input type="number" name="daily_limit" value="{{ old('daily_limit', $user->daily_limit) }}" placeholder="Kosongkan jika menggunakan batas bawaan paket" min="0" style="width:100%;padding:0.75rem 1rem;background:rgba(0,0,0,0.3);border:1px solid var(--bg-card-border);border-radius:8px;color:#fff;outline:none;">
            <small style="color:var(--text-dim);display:block;margin-top:4px;">Jika diisi angka (misal 50), limit ini akan meng-override batas paket.</small>
        </div>

        <div style="margin-bottom:1.25rem;display:flex;align-items:center;gap:10px;">
            <input type="checkbox" name="unlimited" value="1" id="unlimitedCheck" {{ old('unlimited', $user->unlimited) ? 'checked' : '' }}>
            <label for="unlimitedCheck" style="color:#fff;font-weight:600;cursor:pointer;">Aktifkan Akses ∞ Unlimited (Tanpa Batas Kuota)</label>
        </div>

        <div style="margin-bottom:1.75rem;display:flex;align-items:center;gap:10px;">
            <input type="checkbox" name="is_active" value="1" id="activeCheck" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
            <label for="activeCheck" style="color:#fff;font-weight:600;cursor:pointer;">Akun Aktif</label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-action btn-primary" style="padding:0.75rem 1.5rem;font-size:0.95rem;">Simpan Perubahan</button>
            <a href="{{ route('admin.users.index') }}" class="btn-action" style="padding:0.75rem 1.5rem;background:rgba(255,255,255,0.08);color:#fff;">Batal</a>
        </div>
    </form>
</div>
@endsection
