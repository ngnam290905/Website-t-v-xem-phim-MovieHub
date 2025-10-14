<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class NguoiDung extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'nguoi_dung';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'ho_ten', 'email', 'mat_khau', 'dien_thoai', 'dia_chi', 'id_vai_tro', 'trang_thai'
    ];

    protected $hidden = ['mat_khau'];

    public function getAuthPassword()
    {
        return $this->mat_khau; // Laravel cần hàm này để xác thực mật khẩu
    }

    public function vaiTro()
    {
        return $this->belongsTo(VaiTro::class, 'id_vai_tro');
    }
}

