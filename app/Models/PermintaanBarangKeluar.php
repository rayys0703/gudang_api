<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermintaanBarangKeluar extends Model
{
    use HasFactory;

    protected $table = "permintaan_barang_keluar";

    protected $fillable = [
        'customer_id',
        'keperluan_id',
        'jumlah',
        'tanggal_awal',
        'tanggal_akhir',
        'keterangan',
        'alasan',
        'status',
        'created_by',
        'ba_project',
        'ba_no_po',
    ];
}
