<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phim extends Model
{
    protected $table = 'phim';
    protected $fillable = ['ten_phim','the_loai', 'do_dai', 'poster', 'mo_ta', 'dao_dien', 'dien_vien', 'trailer', 'trang_thai'];

    public function suatChieu()
    {
        return $this->hasMany(SuatChieu::class, 'id_phim');
    }
}
