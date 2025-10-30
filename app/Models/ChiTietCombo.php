<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTietCombo extends Model
{
    use HasFactory;
   protected $table = 'chi_tiet_dat_ve_combo';
   public $timestamps = false;
   protected $fillable = ['id_dat_ve','id_combo','so_luong','gia_ap_dung'];

   public function datVe() {
        return $this->belongsTo(DatVe::class, 'id_dat_ve');
    }
    
    public function combo() {
        return $this->belongsTo(Combo::class, 'id_combo');
    }
}
