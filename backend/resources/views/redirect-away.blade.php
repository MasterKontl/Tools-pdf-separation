<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting...</title>
    <meta http-equiv="refresh" content="0;url={{ $url }}">
    <script>window.location.href = @js($url);</script>
    <style>
        body { font-family: system-ui, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; background: #f8fafc; color: #334155; }
        .box { text-align: center; padding: 2rem; }
        .box a { color: #2563eb; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="box">
        <p>Anda akan dialihkan ke halaman pembayaran...</p>
        <p><a href="{{ $url }}">Klik di sini jika tidak otomatis redirect</a></p>
    </div>
</body>
</html>
