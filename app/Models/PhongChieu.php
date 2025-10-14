<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhongChieu extends Model
{
    use HasFactory;

    // Tên bảng từ SQL dump
    protected $table = 'phong_chieu';

    // Tắt timestamps vì bảng không có created_at/updated_at đầy đủ
    public $timestamps = false;

    // Các trường có thể fill khi create/update
    protected $fillable = [
        'ten_phong',
        'so_hang',
        'so_cot',
        'suc_chua',
        'mo_ta',
        'trang_thai',
    ];

    // Relation: Một phòng có nhiều suất chiếu
    public function suatChieus()
    {
        return $this->hasMany(SuatChieu::class, 'id_phong');
    }

    // Relation: Một phòng có nhiều ghế
    public function ghes()
    {
        return $this->hasMany(Ghe::class, 'id_phong');
    }
}