@extends('admin.layout')

@section('title', 'Tambah Paket Baru')

@section('content')
<div class="card" style="max-width:600px;">
    @if ($errors->any())
        <div style="background:rgba(239,68,68,0.15);color:#fca5a5;padding:1rem;border-radius:8px;margin-bottom:1.5rem;">
            @foreach ($errors->all() as $err)
                <div>{{ $err }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('admin.plans.store') }}" method="POST">
        @csrf

        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-size:0.85rem;font-weight:600;color:var(--text-muted);margin-bottom:0.4rem;">Nama Paket</label>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Studio Pro" required style="width:100%;padding:0.75rem 1rem;background:rgba(0,0,0,0.3);border:1px solid var(--bg-card-border);border-radius:8px;color:#fff;outline:none;">
        </div>

        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-size:0.85rem;font-weight:600;color:var(--text-muted);margin-bottom:0.4rem;">Slug (Opsional)</label>
            <input type="text" name="slug" value="{{ old('slug') }}" placeholder="studio-pro" style="width:100%;padding:0.75rem 1rem;background:rgba(0,0,0,0.3);border:1px solid var(--bg-card-border);border-radius:8px;color:#fff;outline:none;">
        </div>

        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-size:0.85rem;font-weight:600;color:var(--text-muted);margin-bottom:0.4rem;">Deskripsi</label>
            <textarea name="description" rows="3" style="width:100%;padding:0.75rem 1rem;background:rgba(0,0,0,0.3);border:1px solid var(--bg-card-border);border-radius:8px;color:#fff;outline:none;">{{ old('description') }}</textarea>
        </div>

        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-size:0.85rem;font-weight:600;color:var(--text-muted);margin-bottom:0.4rem;">Daily Limit (Jumlah Konversi / Hari)</label>
            <input type="number" name="daily_limit" value="{{ old('daily_limit', 10) }}" min="1" style="width:100%;padding:0.75rem 1rem;background:rgba(0,0,0,0.3);border:1px solid var(--bg-card-border);border-radius:8px;color:#fff;outline:none;">
        </div>

        <div style="margin-bottom:1.25rem;display:flex;align-items:center;gap:10px;">
            <input type="checkbox" name="unlimited" value="1" id="unlimitedPlan" {{ old('unlimited') ? 'checked' : '' }}>
            <label for="unlimitedPlan" style="color:#fff;font-weight:600;cursor:pointer;">Paket Tanpa Batas (∞ Unlimited)</label>
        </div>

        <div class="price-currency-grid">
            <div>
                <label style="display:block;font-size:0.85rem;font-weight:600;color:var(--text-muted);margin-bottom:0.4rem;">Harga (Nominal)</label>
                <input type="number" name="price" value="{{ old('price', 0) }}" min="0" step="1000" required style="width:100%;padding:0.75rem 1rem;background:rgba(0,0,0,0.3);border:1px solid var(--bg-card-border);border-radius:8px;color:#fff;outline:none;">
            </div>
            <div>
                <label style="display:block;font-size:0.85rem;font-weight:600;color:var(--text-muted);margin-bottom:0.4rem;">Mata Uang</label>
                <input type="text" name="currency" value="{{ old('currency', 'IDR') }}" required style="width:100%;padding:0.75rem 1rem;background:rgba(0,0,0,0.3);border:1px solid var(--bg-card-border);border-radius:8px;color:#fff;outline:none;">
            </div>
        </div>

        <div style="margin-bottom:1.75rem;display:flex;align-items:center;gap:10px;">
            <input type="checkbox" name="is_active" value="1" id="activePlan" checked>
            <label for="activePlan" style="color:#fff;font-weight:600;cursor:pointer;">Aktifkan Paket Langsung</label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-action btn-primary" style="padding:0.75rem 1.5rem;font-size:0.95rem;">Simpan Paket</button>
            <a href="{{ route('admin.plans.index') }}" class="btn-action" style="padding:0.75rem 1.5rem;background:rgba(255,255,255,0.08);color:#fff;">Batal</a>
        </div>
    </form>
</div>
@endsection
