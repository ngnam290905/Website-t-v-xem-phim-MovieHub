<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoaiGhe extends Model
{
    use HasFactory;

    // Tên bảng từ SQL dump
    protected $table = 'loai_ghe';

    // Tắt timestamps vì bảng không có created_at/updated_at
    public $timestamps = false;

    // Các trường có thể fill khi create/update
    protected $fillable = [
        'ten_loai',
        'he_so_gia',
    ];

    // Relation: Một loại ghế có nhiều ghế
    public function ghes()
    {
        return $this->hasMany(Ghe::class, 'id_loai');
    }
}