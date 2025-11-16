<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTietDatVe extends Model
{
    use HasFactory;
    protected $table = 'chi_tiet_dat_ve';
    public $timestamps = false;
    protected $fillable = ['id_dat_ve', 'id_ghe', 'gia'];

    public function datVe()
    {
        return $this->belongsTo(DatVe::class, 'id_dat_ve');
    }
    public function ghe()
    {
        return $this->belongsTo(Ghe::class, 'id_ghe');
    }
}
