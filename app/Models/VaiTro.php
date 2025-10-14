<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VaiTro extends Model
{
    protected $table = 'vai_tro';
    protected $fillable = [
        'ten', 
        'mo_ta'
    ];

    public function nguoiDung()
    {
        return $this->hasMany(NguoiDung::class, 'id_vai_tro');
    }
}
