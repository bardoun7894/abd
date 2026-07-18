<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Phase 3 — a single async interactive-extraction request (see the migration).
 */
class AiExtractionJob extends Model
{
    protected $table = 'ai_extraction_jobs';

    protected $fillable = [
        'user_id', 'module', 'status', 'file_path', 'file_url', 'model', 'result_json', 'error',
    ];

    protected $casts = [
        'result_json' => 'array',
    ];
}
