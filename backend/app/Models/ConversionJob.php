<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversionJob extends Model
{
    protected $primaryKey = 'job_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'job_id',
        'status',
        'format',
        'dpi',
        'original_name',
        'source_path',
        'error',
        'error_type',
        'file_path',
        'file_name',
        'mime_type',
        'is_zip',
        'page_count',
        'elapsed_sec',
        'quota_data',
    ];

    protected $casts = [
        'dpi' => 'integer',
        'is_zip' => 'boolean',
        'page_count' => 'integer',
        'elapsed_sec' => 'float',
        'quota_data' => 'array',
    ];
}
