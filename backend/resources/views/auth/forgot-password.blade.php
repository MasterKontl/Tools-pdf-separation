<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi | Tools DKV</title>
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
        .logo-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 12px; border-radius: 999px;
            background: #EFF6FF; border: 1px solid #BFDBFE;
            color: var(--primary); font-size: 0.78rem; font-weight: 700; text-transform: uppercase;
            margin-bottom: 0.75rem; text-decoration: none;
        }
        .auth-title { font-size: 1.85rem; font-weight: 800; color: var(--primary); margin-bottom: 0.35rem; letter-spacing: -0.02em; }
        .auth-sub { font-size: 0.9rem; color: var(--text-muted); line-height: 1.5; }
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
        .alert-success {
            background: #F0FDF4; border: 1px solid #BBF7D0;
            color: #166534; padding: 0.85rem 1rem; border-radius: var(--radius-md);
            font-size: 0.85rem; margin-bottom: 1.25rem;
        }
        .auth-footer { text-align: center; margin-top: 1.75rem; font-size: 0.88rem; color: var(--text-muted); }
        .link-text { color: var(--accent); text-decoration: none; font-weight: 600; }
        .link-text:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="logo-box">
        <a href="{{ route('converter.index') }}" class="logo-badge">TOOLS DKV</a>
        <h1 class="auth-title">Atur Ulang Sandi</h1>
        <p class="auth-sub">Masukkan email Anda untuk menerima instruksi reset kata sandi.</p>
    </div>

    <div class="card">
        @if (session('status'))
            <div class="alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label" for="email">Email Terdaftar</label>
                <input type="email" name="email" id="email" class="form-input" value="{{ old('email') }}" required autofocus autocomplete="email">
            </div>

            <button type="submit" class="btn-submit">Kirim Tautan Reset</button>
        </form>
    </div>

    <div class="auth-footer">
        Ingat kata sandi Anda? <a href="{{ route('login') }}" class="link-text">Kembali ke Masuk</a>
    </div>
</div>

</body>
</html>
