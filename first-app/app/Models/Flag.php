<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flag extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'report_id',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'created_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
