<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'name',
        'quantity',
        'price_per_item',
        'total_amount',
        'transaction_date',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'quantity'         => 'float',
        'price_per_item'   => 'float',
        'total_amount'     => 'float',
    ];

    /* ── Relationships ──────────────────────────────── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* ── Scopes ─────────────────────────────────────── */

    /** Filter by authenticated user */
    public function scopeMine($query)
    {
        return $query->where('user_id', auth()->id());
    }

    /** Filter by type */
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    /** Filter by month + year */
    public function scopeInMonth($query, int $month, int $year)
    {
        return $query->whereMonth('transaction_date', $month)
                     ->whereYear('transaction_date', $year);
    }

    /** Filter by year */
    public function scopeInYear($query, int $year)
    {
        return $query->whereYear('transaction_date', $year);
    }

    /** Filter by date range */
    public function scopeBetweenDates($query, string $from, string $to)
    {
        return $query->whereBetween('transaction_date', [$from, $to]);
    }

    /* ── Accessors ───────────────────────────────────── */

    public function getTypeLabel(): string
    {
        return $this->type === 'income' ? 'Pemasukan' : 'Pengeluaran';
    }
}