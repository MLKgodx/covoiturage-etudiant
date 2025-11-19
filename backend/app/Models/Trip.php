<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'departure_address',
        'departure_lat',
        'departure_lng',
        'arrival_address',
        'arrival_lat',
        'arrival_lng',
        'departure_time',
        'arrival_time',
        'is_round_trip',
        'return_departure_time',
        'return_arrival_time',
        'is_recurring',
        'recurring_days',
        'recurring_end_date',
        'available_seats',
        'occupied_seats',
        'status',
        'smoker_allowed',
        'music_allowed',
        'chattiness_preference',
        'auto_validation',
        'distance_km',
        'estimated_co2_saved',
    ];

    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
        'return_departure_time' => 'datetime',
        'return_arrival_time' => 'datetime',
        'recurring_end_date' => 'date',
        'is_round_trip' => 'boolean',
        'is_recurring' => 'boolean',
        'recurring_days' => 'array',
        'available_seats' => 'integer',
        'occupied_seats' => 'integer',
        'smoker_allowed' => 'boolean',
        'music_allowed' => 'boolean',
        'auto_validation' => 'boolean',
        'departure_lat' => 'decimal:7',
        'departure_lng' => 'decimal:7',
        'arrival_lat' => 'decimal:7',
        'arrival_lng' => 'decimal:7',
        'distance_km' => 'decimal:2',
        'estimated_co2_saved' => 'decimal:2',
    ];

    // Relations
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function confirmedBookings()
    {
        return $this->hasMany(Booking::class)->where('status', 'confirmed');
    }

    // Helper Methods
    public function getRemainingSeatsAttribute(): int
    {
        return $this->available_seats - $this->occupied_seats;
    }

    public function isFull(): bool
    {
        return $this->remaining_seats <= 0;
    }

    public function canBook(int $seatsRequested = 1): bool
    {
        return $this->status === 'active' 
            && $this->remaining_seats >= $seatsRequested
            && $this->departure_time->isFuture();
    }

    public function calculateCO2Saved(): float
    {
        // Formule: Distance (km) × 150g × (Nb personnes - 1)
        $totalPeople = 1 + $this->occupied_seats; // Conducteur + passagers
        if ($totalPeople <= 1) {
            return 0;
        }
        
        $co2InGrams = $this->distance_km * 150 * ($totalPeople - 1);
        return round($co2InGrams / 1000, 2); // Conversion en kg
    }

    public function updateOccupiedSeats()
    {
        $occupied = $this->confirmedBookings()->sum('seats_booked');
        $this->update([
            'occupied_seats' => $occupied,
            'status' => $occupied >= $this->available_seats ? 'full' : 'active'
        ]);
        
        // Recalcul du CO2
        $this->update([
            'estimated_co2_saved' => $this->calculateCO2Saved()
        ]);
    }

    public function isDriverMatch(User $user): bool
    {
        $matchScore = 0;
        
        if ($this->smoker_allowed === $user->smoker) {
            $matchScore++;
        }
        
        if ($this->music_allowed === $user->music) {
            $matchScore++;
        }
        
        if ($this->chattiness_preference === $user->chattiness || !$this->chattiness_preference) {
            $matchScore++;
        }
        
        return $matchScore >= 2; // Au moins 2/3 de compatibilité
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('departure_time', '>', now());
    }

    public function scopeSearch($query, $filters)
    {
        return $query
            ->when($filters['departure'] ?? null, function ($q, $departure) {
                // Recherche dans un rayon de ~10km
                $q->whereBetween('departure_lat', [$departure['lat'] - 0.1, $departure['lat'] + 0.1])
                  ->whereBetween('departure_lng', [$departure['lng'] - 0.1, $departure['lng'] + 0.1]);
            })
            ->when($filters['arrival'] ?? null, function ($q, $arrival) {
                $q->whereBetween('arrival_lat', [$arrival['lat'] - 0.1, $arrival['lat'] + 0.1])
                  ->whereBetween('arrival_lng', [$arrival['lng'] - 0.1, $arrival['lng'] + 0.1]);
            })
            ->when($filters['date'] ?? null, function ($q, $date) {
                $startOfDay = Carbon::parse($date)->startOfDay();
                $endOfDay = Carbon::parse($date)->endOfDay();
                $q->whereBetween('departure_time', [$startOfDay, $endOfDay]);
            })
            ->when($filters['min_seats'] ?? null, function ($q, $minSeats) {
                $q->whereRaw('(available_seats - occupied_seats) >= ?', [$minSeats]);
            });
    }
}
