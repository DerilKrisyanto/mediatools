<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinktreeOrder extends Model {
    protected $table = 'linktree_orders';

    protected $fillable = ['user_id', 'linktree_id', 'order_id', 'plan_type', 'amount', 'status', 'snap_token'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeWhereUniqueId($query, $id)
    {
        return $query->where('unique_id', $id);
    }
}