<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    public function store(Request $request, $bookingId)
    {
        $booking = Booking::with(['trip', 'passenger'])->findOrFail($bookingId);
        $user = auth()->user();

        // Vérifier que l'utilisateur fait partie de cette réservation
        if ($booking->passenger_id !== $user->id && $booking->trip->driver_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        // Vérifier que la réservation peut être notée
        if (!$booking->canBeRated()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette réservation ne peut pas encore être notée'
            ], 422);
        }

        $isDriver = $booking->trip->driver_id === $user->id;
        $raterType = $isDriver ? 'driver' : 'passenger';

        // Vérifier si déjà noté
        $existingRating = Rating::where('booking_id', $bookingId)
            ->where('rater_id', $user->id)
            ->first();

        if ($existingRating) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà noté ce trajet'
            ], 422);
        }

        // Validation selon le type de notation
        if ($raterType === 'passenger') {
            // Passager note le conducteur
            $validator = Validator::make($request->all(), [
                'driving_rating' => 'required|integer|between:1,5',
                'punctuality_rating' => 'required|integer|between:1,5',
                'vehicle_rating' => 'required|integer|between:1,5',
                'comment' => 'nullable|string|max:200',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $rating = Rating::create([
                'booking_id' => $bookingId,
                'rater_id' => $user->id,
                'rated_id' => $booking->trip->driver_id,
                'rater_type' => 'passenger',
                'driving_rating' => $request->driving_rating,
                'punctuality_rating' => $request->punctuality_rating,
                'vehicle_rating' => $request->vehicle_rating,
                'comment' => $request->comment,
            ]);
        } else {
            // Conducteur note le passager
            $validator = Validator::make($request->all(), [
                'passenger_punctuality_rating' => 'required|integer|between:1,5',
                'respect_rating' => 'required|integer|between:1,5',
                'comment' => 'nullable|string|max:200',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $rating = Rating::create([
                'booking_id' => $bookingId,
                'rater_id' => $user->id,
                'rated_id' => $booking->passenger_id,
                'rater_type' => 'driver',
                'passenger_punctuality_rating' => $request->passenger_punctuality_rating,
                'respect_rating' => $request->respect_rating,
                'comment' => $request->comment,
            ]);
        }

        // Incrémenter le compteur de trajets
        $ratedUser = \App\Models\User::find($rating->rated_id);
        $ratedUser->increment('total_trips');

        return response()->json([
            'success' => true,
            'message' => 'Notation enregistrée',
            'data' => $rating->load(['rater', 'rated'])
        ], 201);
    }

    public function pendingRatings()
    {
        $user = auth()->user();

        // Réservations terminées non notées (en tant que passager)
        $passengerBookings = Booking::where('passenger_id', $user->id)
            ->where('status', 'confirmed')
            ->where('driver_rated', false)
            ->whereHas('trip', function ($query) {
                $query->where('departure_time', '<', now());
            })
            ->with(['trip.driver'])
            ->get();

        // Réservations terminées non notées (en tant que conducteur)
        $driverBookings = Booking::whereHas('trip', function ($query) use ($user) {
            $query->where('driver_id', $user->id)
                  ->where('departure_time', '<', now());
        })
        ->where('status', 'confirmed')
        ->where('passenger_rated', false)
        ->with(['trip', 'passenger'])
        ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'to_rate_as_passenger' => $passengerBookings,
                'to_rate_as_driver' => $driverBookings,
            ]
        ]);
    }

    public function userRatings($userId)
    {
        $ratings = Rating::where('rated_id', $userId)
            ->with(['rater', 'booking.trip'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'average_rating' => Rating::where('rated_id', $userId)->avg('overall_rating'),
            'total_ratings' => Rating::where('rated_id', $userId)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'ratings' => $ratings,
                'stats' => $stats,
            ]
        ]);
    }
}
