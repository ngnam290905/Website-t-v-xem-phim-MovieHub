<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Combo extends Model
{
    use HasFactory;
    protected $table = 'combo';
    public $timestamps = false;
    protected $fillable = ['ten','mo_ta','gia','gia_khuyen_mai','trang_thai'];

    public function chiTietCombos() {
        return $this->hasMany(ChiTietCombo::class, 'id_combo');
    }
}
