<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class NguoiDung extends Authenticatable
{
    use Notifiable;
    protected $table = 'nguoi_dung';
    protected $fillable = [
        'ho_ten',
        'email',
        'mat_khau',
        'ngay_sinh',
        'gioi_tinh',
        'sdt',
        'dia_chi',
        'hinh_anh',
        'id_vai_tro',
        'trang_thai'
    ];
    protected $hidden = ['mat_khau', 'remember_token'];

    public function vaiTro()
    {
        return $this->belongsTo(VaiTro::class, 'id_vai_tro');
    }
    public function datVe()
    {
        return $this->hasMany(DatVe::class, 'id_nguoi_dung');
    }
    public function isAdmin()
    {
        return optional($this->vaiTro)->ten === 'admin';
    }
    public function isStaff()
    {
        return optional($this->vaiTro)->ten === 'staff';
    }
    public function getAuthPassword()
    {
        return $this->mat_khau;
    }
    public function diemThanhVien()
    {
        return $this->hasOne(DiemThanhVien::class, 'id_nguoi_dung');
    }
}
