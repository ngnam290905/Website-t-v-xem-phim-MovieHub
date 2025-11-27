<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HangThanhVien extends Model
{
    use HasFactory;

    protected $table = 'hang_thanh_vien';
<<<<<<< HEAD
    public $timestamps = false;

    protected $fillable = [
        'id_nguoi_dung',
        'id_tier',
        'ten_hang', 
        'uu_dai',
        'diem_toi_thieu',
        'ngay_cap_nhat_hang'
=======
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'id_nguoi_dung',
        'ten_hang',
        'uu_dai',
        'diem_toi_thieu',
>>>>>>> origin/hoanganh
    ];

    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }
}
