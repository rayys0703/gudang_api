<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailBarangMasuk extends Model
{
    use HasFactory;

    protected $table = "detail_barang_masuk";

    protected $fillable = [
        'barangmasuk_id',
        'serial_number_id',
        'status_barang_id',
        'kelengkapan',
    ];
}
