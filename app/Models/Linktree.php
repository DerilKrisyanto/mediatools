<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Linktree extends Model
{
    protected $table = 'linktrees';

    protected $fillable = [
        'user_id',
        'unique_id',
        'name',
        'username',
        'bio',
        'avatar',
        'verified',
        'visitors',
        'links_data',
        'socials_data',
        'page_template',
        'is_active',
        'plan_type',
        'expired_at',
    ];

    protected $casts = [
        'verified'      => 'boolean',
        'is_active'     => 'boolean',
        'visitors'      => 'integer',
        'links_data'    => 'array',
        'socials_data'  => 'array',
        'expired_at'    => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeWhereUniqueId($query, string $id): mixed
    {
        return $query->where('unique_id', $id);
    }

    /** Allowed template slugs */
    public const TEMPLATES = ['dark', 'light', 'neon'];

    public function getPageTemplateAttribute(mixed $value): string
    {
        return in_array($value, self::TEMPLATES) ? $value : 'dark';
    }
}