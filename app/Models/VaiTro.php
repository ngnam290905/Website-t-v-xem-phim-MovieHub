<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaiTro extends Model
{
    use HasFactory;

    protected $table = 'vai_tro';

    public $timestamps = false;

    protected $fillable = [
        'ten',
        'mo_ta',
    ];
}


