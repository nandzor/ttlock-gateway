<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HpsEmas extends Model
{
    protected $table = 'hps_emas';

    protected $fillable = [
        'jenis_barang',
        'stle_rp',
        'kadar_karat',
        'berat_gram',
        'nilai_taksiran_rp',
        'ltv',
        'uang_pinjaman_rp',
        'active',
    ];

    protected $casts = [
        'stle_rp' => 'decimal:2',
        'kadar_karat' => 'integer',
        'berat_gram' => 'decimal:2',
        'nilai_taksiran_rp' => 'decimal:2',
        'ltv' => 'decimal:2',
        'uang_pinjaman_rp' => 'decimal:2',
        'active' => 'boolean',
    ];
}
