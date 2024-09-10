<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SerialNumber extends Model
{
    use HasFactory;

    protected $table = "serial_number";

    protected $fillable = [
        'barangmasuk_id',
        'serial_number'
    ];
}
