<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiemThanhVien extends Model
{
    use HasFactory;
<<<<<<< HEAD
    public $timestamps = false;
    protected $table = 'diem_thanh_vien';
    protected $fillable = [
=======

    protected $table = 'diem_thanh_vien';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id',
>>>>>>> origin/hoanganh
        'id_nguoi_dung',
        'tong_diem',
        'ngay_het_han',
    ];
<<<<<<< HEAD
=======

>>>>>>> origin/hoanganh
    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }
}
