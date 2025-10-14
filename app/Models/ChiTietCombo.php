<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTietCombo extends Model
{
    use HasFactory;
   protected $table = 'chi_tiet_combo';
   public $timestamps = false;
   protected $fillable = ['id','id_dat_ve','id_combo','so_luong','gia_khuyen_mai'];

   public function datVe() {
        return $this->belongsTo(DatVe::class, 'id_dat_ve');
    }
    
    public function combo() {
        return $this->belongsTo(Combo::class, 'id_combo');
    }
}
