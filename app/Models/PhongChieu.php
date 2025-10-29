<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhongChieu extends Model
{
    use HasFactory;

    protected $table = 'phong_chieu';

    public $timestamps = false;
    protected $fillable = [
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
        return $this->hasMany(SuatChieu::class, 'room_id');
    }

    // Relationship with Seats
    public function seats(): HasMany
    {
        return $this->hasMany(Ghe::class, 'room_id');
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
        return $this->rows * $this->cols;
    }

    // Scope for active rooms
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope for room type
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
    }
