<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
    ];

    /**
     * User yang menginput memo ini.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: hanya ambil memo milik user yang sedang login.
     * Contoh pakai: MemoPengiriman::milikSaya()->get();
     */
    public function scopeMilikSaya(Builder $query): Builder
    {
        return $query->where('user_id', auth()->id());
    }
}