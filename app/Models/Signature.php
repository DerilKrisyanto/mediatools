<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Signature extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'job_title',
        'company',
        'email',
        'phone',
        'website',
        'avatar',
        'template_style'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
