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
            'description' => 'Convert PDF ke PNG atau JPG secara online dengan Tools DKV. Praktis untuk kebutuhan desain, percetakan, dan workflow DKV.',
            'h1' => 'Online PDF Converter',
        ],

        '/separation' => [
            'title' => 'PDF Color Separation — Separasi Warna untuk Printing | Tools DKV',
            'description' => 'Pisahkan warna PDF untuk kebutuhan desain dan printing dengan Color Separation Tools DKV.',
            'h1' => 'PDF Color Separation',
        ],

        '/upscaler' => [
            'title' => 'Image Upscaler — Upscale & Enlarge Images Online | Tools DKV',
            'description' => 'Upscale dan perbesar gambar hingga 4× menggunakan high-quality image resizing tanpa instalasi software.',
            'h1' => 'Image Upscaler',
        ],

        '/pricing' => [
            'title' => 'Pricing — Tools DKV',
            'description' => 'Lihat paket dan harga Tools DKV untuk kebutuhan PDF conversion dan image processing.',
            'h1' => 'Pricing',
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
