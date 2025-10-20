<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoaiGhe extends Model
{
    protected $table = 'loai_ghe';
    
    protected $fillable = [
        'ten_loai',
        'he_so_gia'
    ];

    public function ghe()
    {
        return $this->hasMany(Ghe::class, 'id_loai');
    }
}
