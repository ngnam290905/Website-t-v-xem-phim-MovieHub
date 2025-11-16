<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhongChieu extends Model
{
    protected $table = 'phong_chieu';
    
    protected $fillable = [
        'ten_phong',
        'so_hang',
        'so_cot',
        'suc_chua',
        'mo_ta',
        'trang_thai'
    ];

    public function suatChieu()
    {
        return $this->hasMany(SuatChieu::class, 'id_phong');
    }

    public function ghe()
    {
        return $this->hasMany(Ghe::class, 'id_phong');
    }
}
