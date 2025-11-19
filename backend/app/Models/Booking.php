<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'passenger_id',
        'seats_booked',
        'message',
        'status',
        'driver_rated',
        'passenger_rated',
        'co2_saved',
    ];

    protected $casts = [
        'seats_booked' => 'integer',
        'driver_rated' => 'boolean',
        'passenger_rated' => 'boolean',
        'co2_saved' => 'decimal:2',
    ];

    // Relations
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function passenger()
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function driverRating()
    {
        return $this->hasOne(Rating::class)->where('rater_type', 'passenger');
    }

    public function passengerRating()
    {
        return $this->hasOne(Rating::class)->where('rater_type', 'driver');
    }

    // Helper Methods
    public function confirm()
    {
        $this->update(['status' => 'confirmed']);
        $this->trip->updateOccupiedSeats();
        $this->calculateCO2Saved();
    }

    public function refuse()
    {
        $this->update(['status' => 'refused']);
    }

    public function cancel(string $cancelledBy = 'passenger')
    {
        $status = $cancelledBy === 'passenger' 
            ? 'cancelled_by_passenger' 
            : 'cancelled_by_driver';
            
        $this->update(['status' => $status]);
        
        if ($this->status === 'confirmed') {
            $this->trip->updateOccupiedSeats();
        }
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed'])
            && $this->trip->departure_time->isFuture();
    }

    public function canBeRated(): bool
    {
        return $this->status === 'confirmed'
            && $this->trip->departure_time->isPast();
    }

    public function needsDriverRating(): bool
    {
        return $this->canBeRated() && !$this->driver_rated;
    }

    public function needsPassengerRating(): bool
    {
        return $this->canBeRated() && !$this->passenger_rated;
    }

    public function calculateCO2Saved()
    {
        // CO2 économisé = Distance × 150g × nombre de places de ce passager
        $co2InGrams = $this->trip->distance_km * 150 * $this->seats_booked;
        $this->update([
            'co2_saved' => round($co2InGrams / 1000, 2) // Conversion en kg
        ]);
    }

    public function getOtherParticipant(User $currentUser)
    {
        if ($currentUser->id === $this->passenger_id) {
            return $this->trip->driver;
        }
        return $this->passenger;
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('passenger_id', $user->id)
              ->orWhereHas('trip', function ($tripQuery) use ($user) {
                  $tripQuery->where('driver_id', $user->id);
              });
        });
    }
}
