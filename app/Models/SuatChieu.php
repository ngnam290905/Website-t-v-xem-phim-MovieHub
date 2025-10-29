<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\Movie;
use Carbon\Carbon;

class SuatChieu extends Model
{
    use HasFactory;

    protected $table = 'suat_chieu';

    public $timestamps = false;

    protected $fillable = [
    'movie_id',
    'room_id',
    'start_time',
    'end_time',
    'status'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'status' => 'string',
    ];


    // Relationship with Movie
    public function movie(): BelongsTo
    {
    return $this->belongsTo(Phim::class, 'movie_id');
    }

    // Get total seats count
    public function getTotalSeatsCountAttribute()
    {
        return $this->seats()->count();
    }

    // Get occupancy percentage
    public function getOccupancyPercentageAttribute()
    {
        $total = $this->total_seats_count;
        if ($total === 0) return 0;
        
        return round(($this->booked_seats_count / $total) * 100, 2);
    }

    // Update status based on current time
    public function updateStatus()
    {
        $now = Carbon::now();
        
        if ($now->lt($this->start_time)) {
            $this->status = 'coming';
        } elseif ($now->between($this->start_time, $this->end_time)) {
            $this->status = 'ongoing';
        } else {
            $this->status = 'finished';
        }
        
    $this->save();
    }
}
