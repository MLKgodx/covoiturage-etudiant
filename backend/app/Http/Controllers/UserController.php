<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function show($id)
    {
        $user = User::findOrFail($id);
        
        $user->load([
            'receivedRatings' => fn($q) => $q->latest()->take(10)
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'is_trusted_driver' => $user->isTrustedDriver(),
                'recent_ratings' => $user->receivedRatings,
            ]
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'bio' => 'nullable|string|max:150',
            'field_of_study' => 'string',
            'year' => 'integer|min:1|max:5',
            'profile_type' => 'in:driver,passenger,both',
            'smoker' => 'boolean',
            'music' => 'boolean',
            'chattiness' => 'in:quiet,normal,chatty',
            'vehicle_brand' => 'nullable|string',
            'vehicle_model' => 'nullable|string',
            'vehicle_color' => 'nullable|string',
            'vehicle_seats' => 'nullable|integer|min:1|max:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Validation spécifique pour les conducteurs
        if (in_array($request->profile_type, ['driver', 'both'])) {
            if (!$request->vehicle_brand || !$request->vehicle_model || !$request->vehicle_seats) {
                return response()->json([
                    'success' => false,
                    'message' => 'Les informations du véhicule sont obligatoires pour les conducteurs'
                ], 422);
            }
        }

        $user->update($request->only([
            'first_name',
            'last_name',
            'bio',
            'field_of_study',
            'year',
            'profile_type',
            'smoker',
            'music',
            'chattiness',
            'vehicle_brand',
            'vehicle_model',
            'vehicle_color',
            'vehicle_seats',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour',
            'data' => $user
        ]);
    }

    public function updatePhoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        // Supprimer l'ancienne photo
        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        // Sauvegarder la nouvelle photo
        $path = $request->file('photo')->store('photos', 'public');
        $user->update(['photo' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Photo mise à jour',
            'data' => [
                'photo_url' => Storage::url($path)
            ]
        ]);
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mot de passe actuel incorrect'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe mis à jour'
        ]);
    }

    public function dashboard()
    {
        $user = auth()->user();

        // Trajets à venir
        $upcomingTrips = [];
        if ($user->isDriver()) {
            $upcomingTrips = $user->tripsAsDriver()
                ->with(['confirmedBookings.passenger'])
                ->where('departure_time', '>', now())
                ->orderBy('departure_time')
                ->get();
        }

        // Réservations à venir
        $upcomingBookings = $user->bookings()
            ->with(['trip.driver'])
            ->whereHas('trip', fn($q) => $q->where('departure_time', '>', now()))
            ->where('status', 'confirmed')
            ->orderBy('created_at', 'desc')
            ->get();

        // Messages non lus
        $unreadMessagesCount = $user->receivedMessages()
            ->where('is_read', false)
            ->count();

        // Réservations en attente (pour conducteurs)
        $pendingBookingsCount = 0;
        if ($user->isDriver()) {
            $pendingBookingsCount = \App\Models\Booking::whereHas('trip', function ($q) use ($user) {
                $q->where('driver_id', $user->id);
            })
            ->where('status', 'pending')
            ->count();
        }

        // Statistiques
        $stats = [
            'total_trips' => $user->total_trips,
            'total_co2_saved' => $user->total_co2_saved,
            'average_rating' => $user->average_rating,
            'is_trusted_driver' => $user->isTrustedDriver(),
            'trees_equivalent' => round($user->total_co2_saved / 21, 1), // 1 arbre = 21 kg CO2/an
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'upcoming_trips' => $upcomingTrips,
                'upcoming_bookings' => $upcomingBookings,
                'unread_messages_count' => $unreadMessagesCount,
                'pending_bookings_count' => $pendingBookingsCount,
                'stats' => $stats,
            ]
        ]);
    }
}
