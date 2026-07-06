<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemoPengiriman extends Model
{
    use HasFactory;

    protected $table = 'memo_pengirimans';

    protected $fillable = [
        'user_id',
        'nomor_memo',
        'tanggal_memo',
        'diterima_dari',
        'no_struk',
        'telepon_dari',
        'berupa',
        'tujuan_contact_person',
        'tujuan_alamat',
        'tujuan_telepon',
        'customer_service',
        'pengiriman_hari_tanggal',
        'biaya_kirim',
        'instalasi',
        'instalasi_hari_tanggal',
        'no_struk_instalasi',
        'biaya_instalasi',
    ];

    protected $casts = [
        'tanggal_memo'    => 'date',
        'instalasi'       => 'boolean',
        'biaya_kirim'     => 'decimal:2',
        'biaya_instalasi' => 'decimal:2',
        'berupa'          => 'array', // disimpan sebagai JSON: [{"nama":"...","qty":2}, ...]
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeMilikSaya(Builder $query): Builder
    {
        return $query->where('user_id', auth()->id());
    }

    protected function noStrukArray(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->splitList($this->no_struk),
        );
    }

    protected function noStrukInstalasiArray(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->splitList($this->no_struk_instalasi),
        );
    }

    /**
     * Format daftar barang (array berupa) jadi teks siap tampil:
     * "AC Split 1PK (Qty: 2), Kabel Roll (Qty: 1)"
     * Dipakai di PDF & Excel.
     */
    protected function berupaText(): Attribute
    {
        return Attribute::make(
            get: function () {
                $items = is_array($this->berupa) ? $this->berupa : [];

                return collect($items)
                    ->filter(fn ($item) => !empty($item['nama'] ?? null))
                    ->map(fn ($item) => trim($item['nama']) . ' (Qty: ' . ((int) ($item['qty'] ?? 1)) . ')')
                    ->implode(', ');
            },
        );
    }

    private function splitList(?string $value): array
    {
        if (!$value) {
            return [];
        }

        return array_values(array_filter(
            array_map('trim', explode(',', $value)),
            fn ($v) => $v !== ''
        ));
    }
}