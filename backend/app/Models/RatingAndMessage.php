<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'rater_id',
        'rated_id',
        'rater_type',
        'driving_rating',
        'punctuality_rating',
        'vehicle_rating',
        'passenger_punctuality_rating',
        'respect_rating',
        'overall_rating',
        'comment',
    ];

    protected $casts = [
        'driving_rating' => 'integer',
        'punctuality_rating' => 'integer',
        'vehicle_rating' => 'integer',
        'passenger_punctuality_rating' => 'integer',
        'respect_rating' => 'integer',
        'overall_rating' => 'decimal:2',
    ];

    // Relations
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function rater()
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    public function rated()
    {
        return $this->belongsTo(User::class, 'rated_id');
    }

    // Helper Methods
    public function calculateOverallRating()
    {
        if ($this->rater_type === 'passenger') {
            // Notation du conducteur
            $ratings = [
                $this->driving_rating,
                $this->punctuality_rating,
                $this->vehicle_rating,
            ];
        } else {
            // Notation du passager
            $ratings = [
                $this->passenger_punctuality_rating,
                $this->respect_rating,
            ];
        }

        $validRatings = array_filter($ratings);
        $average = count($validRatings) > 0 
            ? array_sum($validRatings) / count($validRatings) 
            : 0;

        return round($average, 2);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($rating) {
            $rating->overall_rating = $rating->calculateOverallRating();
        });

        static::created(function ($rating) {
            // Mettre à jour la note moyenne de l'utilisateur noté
            $rating->rated->updateAverageRating();
            
            // Marquer la réservation comme notée
            if ($rating->rater_type === 'passenger') {
                $rating->booking->update(['driver_rated' => true]);
            } else {
                $rating->booking->update(['passenger_rated' => true]);
            }
        });
    }
}

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'sender_id',
        'receiver_id',
        'content',
        'is_read',
        'read_at',
        'is_template',
        'template_type',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_template' => 'boolean',
        'read_at' => 'datetime',
    ];

    // Relations
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // Helper Methods
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    public static function getTemplateContent(string $type, array $params = []): string
    {
        $templates = [
            'on_my_way' => "Je suis en route ! 🚗",
            'arriving_soon' => "J'arrive dans 5 minutes !",
            'vehicle_description' => "Voiture {color} {brand} {model}",
        ];

        $content = $templates[$type] ?? '';

        // Remplacer les variables
        foreach ($params as $key => $value) {
            $content = str_replace("{{$key}}", $value, $content);
        }

        return $content;
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForBooking($query, int $bookingId)
    {
        return $query->where('booking_id', $bookingId)
                    ->orderBy('created_at', 'asc');
    }
}
