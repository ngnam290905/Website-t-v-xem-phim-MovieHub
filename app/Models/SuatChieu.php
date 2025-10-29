<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Carbon\Carbon;

class SuatChieu extends Model
{
    protected $table = 'suat_chieu';
    
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
        return $this->belongsTo(Movie::class, 'movie_id');
    }

    // Legacy relationship for backward compatibility
    public function phim(): BelongsTo
    {
        return $this->movie();
    }

    // Relationship with Room (PhongChieu)
    public function room(): BelongsTo
    {
        return $this->belongsTo(PhongChieu::class, 'room_id');
    }

    // Legacy relationship for backward compatibility
    public function phongChieu(): BelongsTo
    {
        return $this->room();
    }

    // Relationship with Bookings (DatVe)
    public function bookings(): HasMany
    {
        return $this->hasMany(DatVe::class, 'id_suat_chieu');
    }

    // Legacy relationship for backward compatibility
    public function datVe(): HasMany
    {
        return $this->bookings();
    }

    // Relationship with Seats through Room
    public function seats(): HasManyThrough
    {
        return $this->hasManyThrough(Ghe::class, PhongChieu::class, 'id', 'room_id', 'room_id', 'id');
    }

    // Legacy relationship for backward compatibility
    public function ghe(): HasManyThrough
    {
        return $this->seats();
    }

    // Scope for coming showtimes
    public function scopeComing($query)
    {
        return $query->where('status', 'coming');
    }

    // Scope for ongoing showtimes
    public function scopeOngoing($query)
    {
        return $query->where('status', 'ongoing');
    }

    // Scope for finished showtimes
    public function scopeFinished($query)
    {
        return $query->where('status', 'finished');
    }

    // Scope for today's showtimes
    public function scopeToday($query)
    {
        return $query->whereDate('start_time', Carbon::today());
    }

    // Scope for showtimes by date
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('start_time', $date);
    }

    // Scope for showtimes by movie
    public function scopeByMovie($query, $movieId)
    {
        return $query->where('movie_id', $movieId);
    }

    // Scope for showtimes by room
    public function scopeByRoom($query, $roomId)
    {
        return $query->where('room_id', $roomId);
    }

    // Check if showtime is coming
    public function isComing()
    {
        return $this->status === 'coming';
    }

    // Check if showtime is ongoing
    public function isOngoing()
    {
        return $this->status === 'ongoing';
    }

    // Check if showtime is finished
    public function isFinished()
    {
        return $this->status === 'finished';
    }

    // Check if showtime is today
    public function isToday()
    {
        return $this->start_time->isToday();
    }

    // Get available seats count
    public function getAvailableSeatsCountAttribute()
    {
        return $this->seats()->available()->count();
    }

    // Get booked seats count
    public function getBookedSeatsCountAttribute()
    {
        return $this->seats()->booked()->count();
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
