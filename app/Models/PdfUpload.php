<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdfUpload extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'original_filename',
        'pdf_path',
        'image_path',
        'order',
        'is_active',
        'conversion_status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'image_path' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
