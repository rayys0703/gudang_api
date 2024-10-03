<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SerialNumberPermintaan extends Model
{
    use HasFactory;

    protected $table = "serial_number_permintaan";

    protected $fillable = [
        'detail_permintaan_bk_id',
        'serial_number_id',
    ];
}
