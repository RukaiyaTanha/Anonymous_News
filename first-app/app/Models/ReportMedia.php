<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportMedia extends Model
{
    protected $table = 'report_media';

    protected $fillable = [
        'report_id',
        'file_path',
        'media_type',
        'created_at',
    ];

    public $timestamps = false;

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
