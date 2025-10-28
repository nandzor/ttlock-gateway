<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HpsElektronik extends Model
{
    protected $table = 'hps_elektronik';

    protected $fillable = [
        'kdwilayah',
        'jenis_barang',
        'merek',
        'barang',
        'tahun',
        'harga',
        'active',
        'grade',
        'kondisi',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'harga' => 'decimal:2',
        'active' => 'boolean',
    ];
}
