<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'status',
        'ai_confidence_score',
        'duplicate_similarity_score',
        'credibility_score',
        'ai_realism_assessment',
        'ai_suspicious_indicators',
        'ai_entities',
        'ai_model',
        'moderator_note',
        'reviewed_by',
        'reviewed_at',
        'published_at',
    ];

    protected $casts = [
        'ai_confidence_score' => 'float',
        'duplicate_similarity_score' => 'float',
        'credibility_score' => 'float',
        'ai_suspicious_indicators' => 'array',
        'ai_entities' => 'array',
        'reviewed_at' => 'datetime',
        'published_at' => 'datetime',
    ];
    public function votes()
    {
    return $this->hasMany(\App\Models\Vote::class);
    }

    public function flags()
    {
        return $this->hasMany(Flag::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ReportMedia::class);
    }

    public function getPrimaryImagePathAttribute(): ?string
    {
        $image = $this->relationLoaded('media')
            ? $this->media->firstWhere('media_type', 'image')
            : $this->media()->where('media_type', 'image')->orderBy('id')->first();

        return $image?->file_path;
    }

    public function getPrimaryImageUrlAttribute(): ?string
    {
        $path = $this->primary_image_path;

        return $path ? '/storage/'.$path : null;
    }
}
