<?php

return [

    'site_name' => 'Tools DKV',

    'default' => [
        'title' => 'Tools DKV — PDF Converter, Color Separation & Image Upscaler',
        'description' => 'Tools DKV menyediakan PDF Converter, color separation, dan image upscaler online untuk kebutuhan desain dan printing.',
        'type' => 'website',
    ],

    'pages' => [

        '/' => [
            'title' => 'PDF Converter Online — Convert PDF to PNG & JPG | Tools DKV',
            'description' => 'Konversi PDF ke PNG atau JPG secara online gratis. Pilih resolusi 150, 300, atau 600 DPI. Tanpa software tambahan, langsung dari browser.',
            'h1' => 'PDF Converter Online',
        ],

        '/separation' => [
            'title' => 'Color Separation PDF — Separasi Warna untuk Sablon & Printing | Tools DKV',
            'description' => 'Pisahkan warna PDF untuk kebutuhan sablon, screen printing, dan CMYK separation. Download per channel dalam format PNG.',
            'h1' => 'Color Separation untuk Sablon & Printing',
        ],

        '/upscaler' => [
            'title' => 'Image Upscaler Online — Perbesar Gambar 2x & 4x Tanpa Software | Tools DKV',
            'description' => 'Perbesar resolusi gambar JPG, PNG, atau WEBP hingga 4 kali lipat. Menggunakan bicubic interpolation berkualitas tinggi.',
            'h1' => 'Image Upscaler Online',
        ],

        '/pricing' => [
            'title' => 'Harga & Paket Tools DKV — PDF Converter, Separation, Upscaler | Tools DKV',
            'description' => 'Lihat paket dan harga Tools DKV. Nikmati kuota konversi harian atau upgrade ke paket Pro untuk fitur unlimited dan resolusi 600 DPI.',
            'h1' => 'Harga & Paket Tools DKV',
        ],

    ],

    'private_paths' => [
        '/login',
        '/register',
        '/forgot-password',
        '/reset-password',
        '/dashboard',
        '/logout',
    ],

];
