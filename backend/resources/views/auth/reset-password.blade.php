<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kata Sandi Baru | Tools DKV</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body: #F8FAFC;
            --surface: #FFFFFF;
            --primary: #1E3A5F;
            --secondary: #274C77;
            --accent: #3B82F6;
            --text-main: #0F172A;
            --text-muted: #64748B;
            --border: #E2E8F0;
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
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .auth-container { width: 100%; max-width: 420px; margin: 0 auto; padding: 1.5rem; }
        .logo-box { text-align: center; margin-bottom: 2rem; }
        .auth-title { font-size: 1.85rem; font-weight: 800; color: var(--primary); margin-bottom: 0.35rem; letter-spacing: -0.02em; }
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2.25rem 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .form-group { margin-bottom: 1.25rem; }
        .form-label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.5rem; }
        .form-input {
            width: 100%; padding: 0.85rem 1rem;
            background: #FFFFFF; border: 1px solid #CBD5E1;
            border-radius: var(--radius-md); color: var(--text-main); font-family: inherit; font-size: 1rem;
            outline: none; transition: border-color 0.15s;
        }
        .form-input:focus { border-color: var(--primary); }
        .btn-submit {
            width: 100%; padding: 0.85rem; border-radius: var(--radius-md); border: none;
            background: var(--primary); color: #fff; font-family: inherit;
            font-size: 1rem; font-weight: 700; cursor: pointer;
            transition: background 0.15s;
        }
        .btn-submit:hover { background: var(--secondary); }
        .alert-error {
            background: #FEF2F2; border: 1px solid #FECACA;
            color: #991B1B; padding: 0.85rem 1rem; border-radius: var(--radius-md);
            font-size: 0.85rem; margin-bottom: 1.25rem;
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="logo-box">
        <h1 class="auth-title">Kata Sandi Baru</h1>
    </div>

    <div class="card">
        @if ($errors->any())
            <div class="alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" name="email" id="email" class="form-input" value="{{ old('email', $email) }}" required autocomplete="email">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Kata Sandi Baru</label>
                <input type="password" name="password" id="password" class="form-input" required autocomplete="new-password">
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-input" required autocomplete="new-password">
            </div>

            <button type="submit" class="btn-submit">Simpan Kata Sandi Baru</button>
        </form>
    </div>
</div>

</body>
</html>
