<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'report_id',
        'action_type',
        'ip_hash',
        'created_at',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
