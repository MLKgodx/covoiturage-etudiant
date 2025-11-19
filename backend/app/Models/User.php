<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'password',
        'first_name',
        'last_name',
        'photo',
        'field_of_study',
        'year',
        'bio',
        'profile_type',
        'smoker',
        'music',
        'chattiness',
        'vehicle_brand',
        'vehicle_model',
        'vehicle_color',
        'vehicle_seats',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'smoker' => 'boolean',
        'music' => 'boolean',
        'year' => 'integer',
        'vehicle_seats' => 'integer',
        'average_rating' => 'decimal:2',
        'total_trips' => 'integer',
        'total_co2_saved' => 'decimal:2',
    ];

    // JWT Methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // Relations
    public function tripsAsDriver()
    {
        return $this->hasMany(Trip::class, 'driver_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'passenger_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function givenRatings()
    {
        return $this->hasMany(Rating::class, 'rater_id');
    }

    public function receivedRatings()
    {
        return $this->hasMany(Rating::class, 'rated_id');
    }

    // Helper Methods
    public function isDriver(): bool
    {
        return in_array($this->profile_type, ['driver', 'both']);
    }

    public function isPassenger(): bool
    {
        return in_array($this->profile_type, ['passenger', 'both']);
    }

    public function hasVehicleInfo(): bool
    {
        return !empty($this->vehicle_brand) 
            && !empty($this->vehicle_model) 
            && !empty($this->vehicle_seats);
    }

    public function canCreateTrip(): bool
    {
        return $this->isDriver() && $this->hasVehicleInfo();
    }

    public function updateAverageRating()
    {
        $avgRating = $this->receivedRatings()->avg('overall_rating');
        $this->update([
            'average_rating' => round($avgRating ?? 0, 2)
        ]);
    }

    public function isTrustedDriver(): bool
    {
        return $this->total_trips >= 10 && $this->average_rating >= 4.5;
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
