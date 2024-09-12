<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keperluan extends Model
{
    use HasFactory;

    protected $table = "keperluan";

    protected $fillable = [
        'nama',
        //'nama_tanggal_awal',
        'nama_tanggal_akhir',
        'extend'
    ];
}
