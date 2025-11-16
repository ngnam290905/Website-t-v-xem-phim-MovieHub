<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhongChieu extends Model
{
    protected $table = 'phong_chieu';

    public $timestamps = false;
    protected $fillable = [
        // App-level attributes; mapped to legacy columns via accessors/mutators
        'name',
        'rows',
        'cols',
        'description',
        'type',
        'status',
        'audio_system',
        'screen_type'
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // Relationship with SuatChieu (Showtimes)
    public function showtimes(): HasMany
    {
        return $this->hasMany(SuatChieu::class, 'id_phong');
    }

    // Relationship with Seats
    public function seats(): HasMany
    {
        return $this->hasMany(Ghe::class, 'id_phong');
    }

    // Legacy relationship names for backward compatibility
    public function suatChieu(): HasMany
    {
        return $this->showtimes();
    }

    public function ghe(): HasMany
    {
        return $this->seats();
    }

    // Accessor for capacity calculation
    public function getCapacityAttribute()
    {
        $rows = $this->rows ?? ($this->so_hang ?? null);
        $cols = $this->cols ?? ($this->so_cot ?? null);
        if ($rows && $cols) {
            return $rows * $cols;
        }
        return $this->suc_chua ?? null;
    }

    // Map legacy columns to modern attributes
    public function getNameAttribute()
    {
        return $this->attributes['name'] ?? ($this->attributes['ten_phong'] ?? null);
    }

    public function setNameAttribute($value)
    {
        $this->attributes['ten_phong'] = $value;
    }

    public function getRowsAttribute()
    {
        return $this->attributes['rows'] ?? ($this->attributes['so_hang'] ?? null);
    }

    public function setRowsAttribute($value)
    {
        $this->attributes['so_hang'] = $value;
    }

    public function getColsAttribute()
    {
        return $this->attributes['cols'] ?? ($this->attributes['so_cot'] ?? null);
    }

    public function setColsAttribute($value)
    {
        $this->attributes['so_cot'] = $value;
    }

    public function getDescriptionAttribute()
    {
        return $this->attributes['description'] ?? ($this->attributes['mo_ta'] ?? null);
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['mo_ta'] = $value;
    }

    public function getStatusAttribute()
    {
        if (array_key_exists('status', $this->attributes)) {
            return $this->attributes['status'];
        }
        if (array_key_exists('trang_thai', $this->attributes)) {
            return ((int) $this->attributes['trang_thai']) === 1 ? 'active' : 'inactive';
        }
        return null;
    }

    public function setStatusAttribute($value)
    {
        // Accept 'active'/'inactive' or 1/0
        if (is_string($value)) {
            $this->attributes['trang_thai'] = $value === 'active' ? 1 : 0;
        } else {
            $this->attributes['trang_thai'] = (int) $value;
        }
    }

    // Scope for active rooms
    public function scopeActive($query)
    {
        // Prefer legacy column if present
        return $query->where(function($q){
            $q->where('trang_thai', 1)->orWhere('status', 'active');
        });
    }

    // Scope for room type
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
    }
