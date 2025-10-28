<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class NguoiDung extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'nguoi_dung';

    public $timestamps = false;

    protected $fillable = [
        'ho_ten',
        'email',
        'password',   // ✅ Đổi từ mat_khau sang password
        'ngay_sinh',
        'gioi_tinh',
        'sdt',
        'dia_chi',
        'hinh_anh',
        'id_vai_tro',
        'trang_thai',
    ];

    protected $hidden = [
        'password',   // ✅ Cũng đổi ở đây
    ];

    public function vaiTro()
    {
        return $this->belongsTo(VaiTro::class, 'id_vai_tro');
    }
}
