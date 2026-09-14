<?php

return [
    /*
    |--------------------------------------------------------------------------
    | pdftoppm Executable Binary Path
    |--------------------------------------------------------------------------
    |
    | Defines the command or full path to the poppler pdftoppm binary.
    |
    */
    'bin_path' => env('PDFTOPPM_PATH', 'pdftoppm'),

    /*
    |--------------------------------------------------------------------------
    | Supported Formats & Resolutions
    |--------------------------------------------------------------------------
    */
    'allowed_dpis' => [150, 300, 600],
    'allowed_formats' => ['png', 'jpg', 'jpeg'],

    /*
    |--------------------------------------------------------------------------
    | Process Timeout (in seconds)
    |--------------------------------------------------------------------------
    */
    'timeout' => (int) env('PDFTOPPM_TIMEOUT', 900),

    /*
    |--------------------------------------------------------------------------
    | Maximum Upload File Size (Kilobytes)
    |--------------------------------------------------------------------------
    */
    'max_file_size_kb' => (int) env('PDF_MAX_FILE_SIZE_KB', 256000), // 250 MB

    /*
    |--------------------------------------------------------------------------
    | Maximum Files per Batch Conversion
    |--------------------------------------------------------------------------
    */
    'max_batch_size' => (int) env('PDF_MAX_BATCH_SIZE', 10),

    /*
    |--------------------------------------------------------------------------
    | Parallel Chunks for Multi-Page Conversion
    |--------------------------------------------------------------------------
    |
    | Number of parallel pdftoppm processes for multi-page PDFs.
    | Set to 1 to disable parallel processing (sequential, backward compatible).
    | Recommended: 2 for Railway worker with 2 CPU cores.
    | PDFs with 3 or fewer pages always use single process regardless.
    |
    */
    'parallel_chunks' => (int) env('PDF_PARALLEL_CHUNKS', 1),
];
