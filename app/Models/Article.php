<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'category_id',
        'title',
        'summary',
        'body',
        'permalink',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['category_id'] ?? null, fn (Builder $q, $v) => $q->where('category_id', $v))
            ->when($filters['title'] ?? null, fn (Builder $q, $v) => $q->where('title', 'like', "%{$v}%"))
            ->when($filters['summary'] ?? null, fn (Builder $q, $v) => $q->where('summary', 'like', "%{$v}%"))
            ->when($filters['body'] ?? null, fn (Builder $q, $v) => $q->where('body', 'like', "%{$v}%"));
    }
}
