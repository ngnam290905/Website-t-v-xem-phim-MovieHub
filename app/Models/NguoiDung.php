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
<<<<<<< HEAD
    protected $dates = ['deleted_at'];
    public $timestamps = false;
=======
    protected $dates = ['deleted_at', 'created_at', 'updated_at']; // ✅ Bổ sung để Laravel nhận dạng

    // Bật timestamps để Laravel tự quản lý created_at và updated_at
    public $timestamps = true;
>>>>>>> origin/hoanganh

    protected $fillable = [
        'ho_ten',
        'email',
        'mat_khau',
        'sdt',
        'ngay_sinh',
        'gioi_tinh',
        'dia_chi',
        'id_vai_tro',
        'trang_thai',
        'la_thanh_vien',
        'ngay_dang_ky_thanh_vien',
    ];

    protected $casts = [
        'trang_thai' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Ẩn mật khẩu khi trả về JSON
    protected $hidden = ['mat_khau'];

    /**
     * Laravel Auth - Xác định trường mật khẩu
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

<<<<<<< HEAD
=======
    /**
     * Ghi đè tên cột timestamps nếu bạn dùng đúng "created_at" và "updated_at"
     * thì KHÔNG cần thêm hai hàm này.
     * Nếu bạn dùng tên khác (vd: ngay_tao, ngay_cap_nhat), mới cần chỉnh.
     */
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

>>>>>>> origin/hoanganh
    public function diemThanhVien()
    {
        return $this->hasOne(DiemThanhVien::class, 'id_nguoi_dung');
    }

    public function hangThanhVien()
    {
        return $this->hasOne(HangThanhVien::class, 'id_nguoi_dung');
    }
<<<<<<< HEAD

    public function datVe()
    {
        return $this->hasMany(DatVe::class, 'id_nguoi_dung');
=======
    
    public function getTongChiTieuAttribute()
    {
        return $this->thanhToan()->sum('so_tien');
    }

    public function thanhToan()
    {
        return $this->hasManyThrough(
            ThanhToan::class,
            DatVe::class,
            'id_nguoi_dung',
            'id_dat_ve',
            'id',
            'id'
        );
>>>>>>> origin/hoanganh
    }
}
