<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_id' => 'required|exists:trips,id',
            'seats_booked' => 'required|integer|min:1',
            'message' => 'nullable|string|max:300',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $trip = Trip::findOrFail($request->trip_id);

        // Vérifications
        if ($trip->driver_id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas réserver votre propre trajet'
            ], 422);
        }

        if (!$trip->canBook($request->seats_booked)) {
            return response()->json([
                'success' => false,
                'message' => 'Places insuffisantes ou trajet non disponible'
            ], 422);
        }

        // Vérifier si déjà réservé
        $existingBooking = Booking::where('trip_id', $trip->id)
            ->where('passenger_id', $user->id)
            ->first();

        if ($existingBooking) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà réservé ce trajet'
            ], 422);
        }

        $booking = Booking::create([
            'trip_id' => $trip->id,
            'passenger_id' => $user->id,
            'seats_booked' => $request->seats_booked,
            'message' => $request->message,
            'status' => $trip->auto_validation ? 'confirmed' : 'pending',
        ]);

        if ($trip->auto_validation) {
            $booking->confirm();
        }

        return response()->json([
            'success' => true,
            'message' => $trip->auto_validation 
                ? 'Réservation confirmée' 
                : 'Demande envoyée au conducteur',
            'data' => $booking->load(['trip.driver', 'passenger'])
        ], 201);
    }

    public function confirm($id)
    {
        $booking = Booking::findOrFail($id);
        $user = auth()->user();

        if ($booking->trip->driver_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cette réservation ne peut pas être confirmée'
            ], 422);
        }

        if (!$booking->trip->canBook($booking->seats_booked)) {
            return response()->json([
                'success' => false,
                'message' => 'Places insuffisantes'
            ], 422);
        }

        $booking->confirm();

        return response()->json([
            'success' => true,
            'message' => 'Réservation confirmée',
            'data' => $booking
        ]);
    }

    public function refuse($id)
    {
        $booking = Booking::findOrFail($id);
        $user = auth()->user();

        if ($booking->trip->driver_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cette réservation ne peut pas être refusée'
            ], 422);
        }

        $booking->refuse();

        return response()->json([
            'success' => true,
            'message' => 'Réservation refusée'
        ]);
    }

    public function cancel($id)
    {
        $booking = Booking::findOrFail($id);
        $user = auth()->user();

        if (!$booking->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette réservation ne peut pas être annulée'
            ], 422);
        }

        $cancelledBy = 'passenger';
        if ($booking->trip->driver_id === $user->id) {
            $cancelledBy = 'driver';
        } elseif ($booking->passenger_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $booking->cancel($cancelledBy);

        return response()->json([
            'success' => true,
            'message' => 'Réservation annulée'
        ]);
    }

    public function myBookings()
    {
        $user = auth()->user();

        $bookings = Booking::where('passenger_id', $user->id)
            ->with(['trip.driver'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    public function pendingBookings()
    {
        $user = auth()->user();

        $bookings = Booking::whereHas('trip', function ($query) use ($user) {
            $query->where('driver_id', $user->id);
        })
        ->where('status', 'pending')
        ->with(['trip', 'passenger'])
        ->orderBy('created_at', 'desc')
        ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }
}
