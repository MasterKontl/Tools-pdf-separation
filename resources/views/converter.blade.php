<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PDF Converter V1 | Tools DKV</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body: #0a0e17;
            --bg-card: rgba(18, 24, 38, 0.85);
            --bg-card-border: rgba(255, 255, 255, 0.08);
            --accent-primary: #3b82f6;
            --accent-gradient: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            --accent-glow: rgba(59, 130, 246, 0.25);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --text-dim: #64748b;
            --surface-hover: rgba(255, 255, 255, 0.04);
            --danger-bg: rgba(239, 68, 68, 0.12);
            --danger-border: rgba(239, 68, 68, 0.3);
            --danger-text: #fca5a5;
            --radius-lg: 18px;
            --radius-md: 12px;
            --radius-sm: 8px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-body);
            background-image: 
                radial-gradient(circle at 20% 15%, rgba(59, 130, 246, 0.12) 0%, transparent 40%),
                radial-gradient(circle at 80% 85%, rgba(139, 92, 246, 0.1) 0%, transparent 45%);
            background-attachment: fixed;
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .container {
            width: 100%;
            max-width: 640px;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .badge-dkv {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 999px;
            background: rgba(59, 130, 246, 0.12);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #60a5fa;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .badge-dkv::before {
            content: '';
            width: 8px;
            height: 8px;
            background: #3b82f6;
            border-radius: 50%;
            box-shadow: 0 0 10px #3b82f6;
        }

        .title {
            font-size: 2.25rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            background: linear-gradient(to right, #ffffff, #cbd5e1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .card {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--bg-card-border);
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
        }

        .alert-error {
            background: var(--danger-bg);
            border: 1px solid var(--danger-border);
            color: var(--danger-text);
            padding: 0.9rem 1.2rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .alert-error ul {
            margin-left: 1rem;
            margin-top: 0.3rem;
        }

        /* Upload Area */
        .drop-zone {
            border: 2px dashed rgba(255, 255, 255, 0.16);
            border-radius: var(--radius-md);
            padding: 2.5rem 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255, 255, 255, 0.015);
            position: relative;
        }

        .drop-zone:hover, .drop-zone.dragover {
            border-color: #3b82f6;
            background: rgba(59, 130, 246, 0.05);
            box-shadow: 0 0 25px var(--accent-glow);
        }

        .drop-zone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .upload-icon {
            width: 52px;
            height: 52px;
            margin: 0 auto 1rem;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #60a5fa;
            transition: transform 0.2s ease;
        }

        .drop-zone:hover .upload-icon {
            transform: scale(1.08);
            background: rgba(59, 130, 246, 0.2);
        }

        .drop-text-primary {
            font-size: 1.05rem;
            font-weight: 600;
            color: #f1f5f9;
            margin-bottom: 0.35rem;
        }

        .drop-text-secondary {
            font-size: 0.85rem;
            color: var(--text-dim);
        }

        /* File Preview Box */
        .file-preview {
            display: none;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-md);
            padding: 1rem 1.2rem;
            align-items: center;
            justify-content: space-between;
            margin-top: 1rem;
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 12px;
            overflow: hidden;
        }

        .file-icon {
            width: 40px;
            height: 40px;
            background: rgba(239, 68, 68, 0.15);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f87171;
            font-weight: 700;
            font-size: 0.75rem;
            flex-shrink: 0;
        }

        .file-details {
            overflow: hidden;
        }

        .file-name {
            font-size: 0.92rem;
            font-weight: 600;
            color: var(--text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 320px;
        }

        .file-size {
            font-size: 0.8rem;
            color: var(--text-dim);
        }

        .btn-remove-file {
            background: none;
            border: none;
            color: var(--text-dim);
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s, background 0.2s;
        }

        .btn-remove-file:hover {
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
        }

        /* Form Sections */
        .options-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin: 1.75rem 0 1.25rem;
        }

        @media (max-width: 520px) {
            .options-grid {
                grid-template-columns: 1fr;
            }
        }

        .section-label {
            display: block;
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 0.65rem;
        }

        /* Segmented Radio Group */
        .segment-group {
            display: flex;
            background: rgba(0, 0, 0, 0.35);
            padding: 4px;
            border-radius: var(--radius-md);
            border: 1px solid rgba(255, 255, 255, 0.06);
            gap: 4px;
        }

        .segment-option {
            flex: 1;
            position: relative;
        }

        .segment-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            inset: 0;
            width: 100%;
            height: 100%;
        }

        .segment-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 8px 4px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
        }

        .segment-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text-muted);
        }

        .segment-desc {
            font-size: 0.68rem;
            color: var(--text-dim);
            margin-top: 2px;
        }

        .segment-option input[type="radio"]:checked + .segment-btn {
            background: var(--accent-gradient);
            box-shadow: 0 4px 14px var(--accent-glow);
        }

        .segment-option input[type="radio"]:checked + .segment-btn .segment-title {
            color: #ffffff;
        }

        .segment-option input[type="radio"]:checked + .segment-btn .segment-desc {
            color: rgba(255, 255, 255, 0.85);
        }

        /* Features note */
        .multi-page-note {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(139, 92, 246, 0.08);
            border: 1px solid rgba(139, 92, 246, 0.2);
            padding: 0.75rem 1rem;
            border-radius: var(--radius-md);
            font-size: 0.82rem;
            color: #c4b5fd;
            margin-bottom: 1.5rem;
        }

        .multi-page-note svg {
            flex-shrink: 0;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 1rem;
            border-radius: var(--radius-md);
            border: none;
            background: var(--accent-gradient);
            color: #ffffff;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            cursor: pointer;
            box-shadow: 0 8px 24px var(--accent-glow);
            transition: transform 0.15s ease, box-shadow 0.2s ease, opacity 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(59, 130, 246, 0.4);
        }

        .btn-submit:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            box-shadow: none;
        }

        /* Loading Overlay */
        .processing-indicator {
            display: none;
            margin-top: 1.25rem;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: var(--radius-md);
            padding: 1rem 1.25rem;
            text-align: center;
        }

        .spinner {
            width: 24px;
            height: 24px;
            border: 3px solid rgba(59, 130, 246, 0.25);
            border-top-color: #60a5fa;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 0.5rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .processing-text {
            font-size: 0.88rem;
            color: var(--text-main);
            font-weight: 600;
        }

        .processing-sub {
            font-size: 0.78rem;
            color: var(--text-dim);
            margin-top: 2px;
        }

        .footer {
            margin-top: 2.5rem;
            text-align: center;
            font-size: 0.8rem;
            color: var(--text-dim);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="badge-dkv">Tools DKV &bull; Production Engine</div>
        <h1 class="title">PDF Converter V1</h1>
        <p class="subtitle">Konversi dokumen PDF ke format gambar PNG / JPG resolusi tinggi (hingga 600 DPI) berbasis Poppler pdftoppm.</p>
    </div>

    <div class="card">
        {{-- Flash / Error Messages --}}
        @if (session('error'))
            <div class="alert-error">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-error">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <strong>Periksa data formulir:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form id="convertForm" action="{{ route('converter.process') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Drag & Drop File Zone --}}
            <div class="drop-zone" id="dropZone">
                <input type="file" name="pdf" id="pdfFileInput" accept="application/pdf,.pdf" required>
                <div class="upload-icon">
                    <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </div>
                <div class="drop-text-primary">Pilih atau Drag & Drop file PDF</div>
                <div class="drop-text-secondary">Maksimum ukuran dokumen hingga 100 MB</div>
            </div>

            {{-- File Preview --}}
            <div class="file-preview" id="filePreview">
                <div class="file-info">
                    <div class="file-icon">PDF</div>
                    <div class="file-details">
                        <div class="file-name" id="fileNameText">document.pdf</div>
                        <div class="file-size" id="fileSizeText">0 KB</div>
                    </div>
                </div>
                <button type="button" class="btn-remove-file" id="btnRemoveFile" title="Hapus file">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Options Grid --}}
            <div class="options-grid">
                {{-- Format Output --}}
                <div>
                    <span class="section-label">Format Output</span>
                    <div class="segment-group">
                        <label class="segment-option">
                            <input type="radio" name="format" value="png" {{ old('format', 'png') === 'png' ? 'checked' : '' }}>
                            <div class="segment-btn">
                                <span class="segment-title">PNG</span>
                                <span class="segment-desc">Lossless & Tajam</span>
                            </div>
                        </label>
                        <label class="segment-option">
                            <input type="radio" name="format" value="jpg" {{ old('format') === 'jpg' ? 'checked' : '' }}>
                            <div class="segment-btn">
                                <span class="segment-title">JPG</span>
                                <span class="segment-desc">Ukuran Lebih Ringan</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- DPI Resolution --}}
                <div>
                    <span class="section-label">Resolusi (DPI)</span>
                    <div class="segment-group">
                        <label class="segment-option">
                            <input type="radio" name="dpi" value="150" {{ old('dpi') == '150' ? 'checked' : '' }}>
                            <div class="segment-btn">
                                <span class="segment-title">150</span>
                                <span class="segment-desc">Web / Preview</span>
                            </div>
                        </label>
                        <label class="segment-option">
                            <input type="radio" name="dpi" value="300" {{ old('dpi', '300') == '300' ? 'checked' : '' }}>
                            <div class="segment-btn">
                                <span class="segment-title">300</span>
                                <span class="segment-desc">Standar Cetak</span>
                            </div>
                        </label>
                        <label class="segment-option">
                            <input type="radio" name="dpi" value="600" {{ old('dpi') == '600' ? 'checked' : '' }}>
                            <div class="segment-btn">
                                <span class="segment-title">600</span>
                                <span class="segment-desc">Ultra Presisi</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Multi-page Note --}}
            <div class="multi-page-note">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
                <span>Dokumen dengan banyak halaman otomatis dibundel ke dalam file <strong>ZIP</strong> berurutan (page 1, 2, ...).</span>
            </div>

            {{-- Submit Action --}}
            <button type="submit" class="btn-submit" id="btnSubmit" disabled>
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span>Mulai Konversi & Download</span>
            </button>

            {{-- Processing Indicator --}}
            <div class="processing-indicator" id="processingIndicator">
                <div class="spinner"></div>
                <div class="processing-text">Merender halaman PDF dengan pdftoppm...</div>
                <div class="processing-sub">Harap tunggu, browser akan otomatis mengunduh hasil konversi.</div>
            </div>
        </form>
    </div>

    <div class="footer">
        Engine: Poppler pdftoppm &bull; Standalone DKV Tool
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const fileInput = document.getElementById('pdfFileInput');
        const dropZone = document.getElementById('dropZone');
        const filePreview = document.getElementById('filePreview');
        const fileNameText = document.getElementById('fileNameText');
        const fileSizeText = document.getElementById('fileSizeText');
        const btnRemoveFile = document.getElementById('btnRemoveFile');
        const btnSubmit = document.getElementById('btnSubmit');
        const convertForm = document.getElementById('convertForm');
        const processingIndicator = document.getElementById('processingIndicator');

        function formatBytes(bytes, decimals = 2) {
            if (!bytes || bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        function updateFileState(file) {
            if (file) {
                fileNameText.textContent = file.name;
                fileSizeText.textContent = formatBytes(file.size);
                filePreview.style.display = 'flex';
                dropZone.style.display = 'none';
                btnSubmit.removeAttribute('disabled');
            } else {
                filePreview.style.display = 'none';
                dropZone.style.display = 'block';
                btnSubmit.setAttribute('disabled', 'true');
            }
        }

        fileInput.addEventListener('change', (e) => {
            if (e.target.files && e.target.files[0]) {
                updateFileState(e.target.files[0]);
            }
        });

        // Drag and drop handlers
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.remove('dragover');
            }, false);
        });

        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files.length > 0) {
                fileInput.files = files;
                updateFileState(files[0]);
            }
        });

        btnRemoveFile.addEventListener('click', () => {
            fileInput.value = '';
            updateFileState(null);
        });

        convertForm.addEventListener('submit', () => {
            btnSubmit.setAttribute('disabled', 'true');
            btnSubmit.style.display = 'none';
            processingIndicator.style.display = 'block';

            // Reset UI state after 10 seconds if download started or user stays on page
            setTimeout(() => {
                btnSubmit.removeAttribute('disabled');
                btnSubmit.style.display = 'flex';
                processingIndicator.style.display = 'none';
            }, 10000);
        });
    });
</script>

</body>
</html>
