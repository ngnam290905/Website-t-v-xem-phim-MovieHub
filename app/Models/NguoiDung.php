<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class NguoiDung extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'nguoi_dung';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    public $timestamps = false; // nếu bảng không có created_at, updated_at

    protected $fillable = [
        'ho_ten',
        'email',
        'mat_khau',
        'dien_thoai',
        'dia_chi',
        'id_vai_tro',
        'trang_thai',
    ];

    // Ẩn mật khẩu khi trả về JSON
    protected $hidden = ['mat_khau'];

    /**
     * Dùng cho Laravel Auth để biết cột nào là mật khẩu
     */
    public function getAuthPassword()
    {
        return $this->mat_khau;
    }

    /**
     * Quan hệ: 1 người dùng thuộc về 1 vai trò
     */
    public function vaiTro()
    {
        return $this->belongsTo(VaiTro::class, 'id_vai_tro');
    }
}
