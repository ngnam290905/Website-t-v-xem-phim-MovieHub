<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HangThanhVien extends Model
{
    use HasFactory;

    protected $table = 'hang_thanh_vien';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'id_nguoi_dung',
        'ten_hang',
        'uu_dai',
        'diem_toi_thieu',
    ];

    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }
}
