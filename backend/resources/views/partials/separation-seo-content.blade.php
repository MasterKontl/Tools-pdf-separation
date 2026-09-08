<section style="max-width:680px;margin:2.5rem auto 0;padding:0 1rem;">
    <h2 style="font-size:1.25rem;font-weight:700;color:var(--text-main);margin-bottom:0.75rem;">Tentang Color Separation</h2>
    <p style="color:var(--text-muted);font-size:0.92rem;line-height:1.65;margin-bottom:1rem;">
        Color Separation dari Tools DKV adalah tool online untuk memisahkan warna PDF menjadi channel individual (CMYK, spot color, grayscale, atau RGB).
        Hasilnya berupa file PNG per channel yang siap digunakan untuk persiapan cetak, screen printing, atau produksi sablon.
    </p>
    <p style="color:var(--text-muted);font-size:0.92rem;line-height:1.65;margin-bottom:1.5rem;">
        Tool ini mendukung berbagai mode separasi termasuk CMYK process, white underbase, spot color, grayscale, outline, dan RGB reference.
        File input dapat berupa PDF, PNG, JPG, atau CDR (CorelDRAW).
    </p>

    <h2 style="font-size:1.25rem;font-weight:700;color:var(--text-main);margin-bottom:0.75rem;">Cara Menggunakan</h2>
    <ol style="color:var(--text-muted);font-size:0.92rem;line-height:1.8;margin-bottom:1.5rem;padding-left:1.25rem;">
        <li>Unggah file PDF, PNG, JPG, atau CDR.</li>
        <li>Pilih mode separasi: CMYK, Underbase, Spot, Grayscale, Outline, atau RGB.</li>
        <li>Atur opsi seperti choke, trap, dan jumlah spot color jika diperlukan.</li>
        <li>Klik <strong>Proses</strong> dan tunggu hasil separasi.</li>
        <li>Download per channel atau bundle ZIP berisi semua channel.</li>
    </ol>

    <h2 style="font-size:1.25rem;font-weight:700;color:var(--text-main);margin-bottom:0.75rem;">Mode Separasi</h2>
    <ul style="color:var(--text-muted);font-size:0.92rem;line-height:1.8;margin-bottom:1.5rem;padding-left:1.25rem;">
        <li><strong>CMYK Process:</strong> Memisahkan ke 4 channel: Cyan, Magenta, Yellow, Black.</li>
        <li><strong>White Underbase:</strong> Membuat channel dasar putih untuk sablon dengan choke.</li>
        <li><strong>Spot Color:</strong> Mencocokkan warna spot (2-8 channel, experimental).</li>
        <li><strong>Grayscale:</strong> Menghasilkan channel luminosity untuk film tonal.</li>
        <li><strong>Outline:</strong> Membuat kontur dan garis tepi (keyline).</li>
        <li><strong>RGB Reference:</strong> Referensi 3 channel RGB untuk keperluan desain.</li>
    </ul>

    <h2 style="font-size:1.25rem;font-weight:700;color:var(--text-main);margin-bottom:0.75rem;">FAQ</h2>
    <details style="margin-bottom:0.75rem;border:1px solid var(--border);border-radius:var(--radius-md);padding:0.85rem 1rem;background:var(--surface-alt);">
        <summary style="font-weight:600;color:var(--text-main);cursor:pointer;font-size:0.92rem;">Apa itu color separation?</summary>
        <p style="color:var(--text-muted);font-size:0.88rem;line-height:1.6;margin-top:0.5rem;">Color separation adalah proses memisahkan gambar berwarna menjadi komponen warna individual (seperti CMYK atau spot color) yang masing-masing akan dicetak secara terpisah pada film atau screen printing.</p>
    </details>
    <details style="margin-bottom:0.75rem;border:1px solid var(--border);border-radius:var(--radius-md);padding:0.85rem 1rem;background:var(--surface-alt);">
        <summary style="font-weight:600;color:var(--text-main);cursor:pointer;font-size:0.92rem;">Apakah cocok untuk kebutuhan sablon?</summary>
        <p style="color:var(--text-muted);font-size:0.88rem;line-height:1.6;margin-top:0.5rem;">Ya. Tool ini dirancang khusus untuk kebutuhan screen printing dan sablon. Hasil separasi berupa file PNG per channel yang siap digunakan untuk pembuatan film cetak.</p>
    </details>
    <details style="margin-bottom:0.75rem;border:1px solid var(--border);border-radius:var(--radius-md);padding:0.85rem 1rem;background:var(--surface-alt);">
        <summary style="font-weight:600;color:var(--text-main);cursor:pointer;font-size:0.92rem;">Format file apa yang didukung?</summary>
        <p style="color:var(--text-muted);font-size:0.88rem;line-height:1.6;margin-top:0.5rem;">PDF, PNG, JPG/JPEG, dan CDR (CorelDRAW). Output selalu dalam format PNG per channel.</p>
    </details>
    <details style="margin-bottom:0.75rem;border:1px solid var(--border);border-radius:var(--radius-md);padding:0.85rem 1rem;background:var(--surface-alt);">
        <summary style="font-weight:600;color:var(--text-main);cursor:pointer;font-size:0.92rem;">Apakah output mempertahankan halaman PDF?</summary>
        <p style="color:var(--text-muted);font-size:0.88rem;line-height:1.6;margin-top:0.5rem;">Ya. Setiap halaman PDF diproses secara individual dan hasil separasi disajikan per halaman sesuai urutan asli dokumen.</p>
    </details>

    <h2 style="font-size:1.25rem;font-weight:700;color:var(--text-main);margin-bottom:0.75rem;">Tool Lainnya</h2>
    <p style="color:var(--text-muted);font-size:0.92rem;line-height:1.65;">
        Selain Color Separation, Tools DKV juga menyediakan
        <a href="{{ route('converter.index') }}" style="color:var(--accent);text-decoration:none;font-weight:600;">PDF Converter untuk mengonversi PDF ke PNG atau JPG</a>
        serta
        <a href="{{ route('upscaler.index') }}" style="color:var(--accent);text-decoration:none;font-weight:600;">Image Upscaler untuk memperbesar resolusi gambar</a>.
        Lihat <a href="{{ route('pricing.index') }}" style="color:var(--accent);text-decoration:none;font-weight:600;">paket dan harga</a> untuk upgrade kuota.
    </p>
</section>
