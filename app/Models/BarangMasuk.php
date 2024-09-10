<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{
    use HasFactory;

    protected $table = "barang_masuk";

    protected $fillable = [
        //'bm_kode',
        //'serial_number',
        'barang_id',
        //'status_barang_id',
        'jumlah',
        'keterangan',
        'tanggal'
    ];
}
