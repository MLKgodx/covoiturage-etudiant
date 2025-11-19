<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $query = Trip::with(['driver', 'confirmedBookings'])
            ->active();

        // Filtres de recherche
        if ($request->has('departure_lat')) {
            $query->whereBetween('departure_lat', [
                $request->departure_lat - 0.1,
                $request->departure_lat + 0.1
            ])
            ->whereBetween('departure_lng', [
                $request->departure_lng - 0.1,
                $request->departure_lng + 0.1
            ]);
        }

        if ($request->has('arrival_lat')) {
            $query->whereBetween('arrival_lat', [
                $request->arrival_lat - 0.1,
                $request->arrival_lat + 0.1
            ])
            ->whereBetween('arrival_lng', [
                $request->arrival_lng - 0.1,
                $request->arrival_lng + 0.1
            ]);
        }

        if ($request->has('date')) {
            $date = \Carbon\Carbon::parse($request->date);
            $query->whereDate('departure_time', $date);
        }

        if ($request->has('min_seats')) {
            $query->whereRaw('(available_seats - occupied_seats) >= ?', [$request->min_seats]);
        }

        // Filtres préférences
        if ($request->has('smoker')) {
            $query->where('smoker_allowed', $request->smoker);
        }

        if ($request->has('music')) {
            $query->where('music_allowed', $request->music);
        }

        $trips = $query->orderBy('departure_time')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $trips
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user->canCreateTrip()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez compléter vos informations de véhicule pour créer un trajet'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'departure_address' => 'required|string',
            'departure_lat' => 'required|numeric',
            'departure_lng' => 'required|numeric',
            'arrival_address' => 'required|string',
            'arrival_lat' => 'required|numeric',
            'arrival_lng' => 'required|numeric',
            'departure_time' => 'required|date|after:now',
            'arrival_time' => 'nullable|date|after:departure_time',
            'available_seats' => 'required|integer|min:1|max:' . $user->vehicle_seats,
            'is_round_trip' => 'boolean',
            'return_departure_time' => 'nullable|required_if:is_round_trip,true|date|after:departure_time',
            'return_arrival_time' => 'nullable|date|after:return_departure_time',
            'is_recurring' => 'boolean',
            'recurring_days' => 'nullable|required_if:is_recurring,true|array',
            'recurring_days.*' => 'integer|between:1,5',
            'recurring_end_date' => 'nullable|required_if:is_recurring,true|date|after:departure_time',
            'smoker_allowed' => 'boolean',
            'music_allowed' => 'boolean',
            'chattiness_preference' => 'nullable|in:quiet,normal,chatty',
            'auto_validation' => 'boolean',
            'distance_km' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $trip = Trip::create([
            'driver_id' => $user->id,
            'departure_address' => $request->departure_address,
            'departure_lat' => $request->departure_lat,
            'departure_lng' => $request->departure_lng,
            'arrival_address' => $request->arrival_address,
            'arrival_lat' => $request->arrival_lat,
            'arrival_lng' => $request->arrival_lng,
            'departure_time' => $request->departure_time,
            'arrival_time' => $request->arrival_time,
            'available_seats' => $request->available_seats,
            'is_round_trip' => $request->is_round_trip ?? false,
            'return_departure_time' => $request->return_departure_time,
            'return_arrival_time' => $request->return_arrival_time,
            'is_recurring' => $request->is_recurring ?? false,
            'recurring_days' => $request->recurring_days,
            'recurring_end_date' => $request->recurring_end_date,
            'smoker_allowed' => $request->smoker_allowed ?? false,
            'music_allowed' => $request->music_allowed ?? true,
            'chattiness_preference' => $request->chattiness_preference,
            'auto_validation' => $request->auto_validation ?? true,
            'distance_km' => $request->distance_km,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Trajet créé avec succès',
            'data' => $trip->load('driver')
        ], 201);
    }

    public function show($id)
    {
        $trip = Trip::with([
            'driver.receivedRatings',
            'confirmedBookings.passenger'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $trip
        ]);
    }

    public function update(Request $request, $id)
    {
        $trip = Trip::findOrFail($id);
        $user = auth()->user();

        if ($trip->driver_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        if ($trip->occupied_seats > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de modifier un trajet avec des réservations confirmées'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'departure_time' => 'date|after:now',
            'arrival_time' => 'nullable|date|after:departure_time',
            'available_seats' => 'integer|min:1',
            'auto_validation' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $trip->update($request->only([
            'departure_time',
            'arrival_time',
            'available_seats',
            'auto_validation',
            'smoker_allowed',
            'music_allowed',
            'chattiness_preference',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Trajet mis à jour',
            'data' => $trip
        ]);
    }

    public function destroy($id)
    {
        $trip = Trip::findOrFail($id);
        $user = auth()->user();

        if ($trip->driver_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        if ($trip->occupied_seats > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer un trajet avec des réservations confirmées'
            ], 422);
        }

        $trip->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Trajet annulé'
        ]);
    }

    public function myTrips()
    {
        $user = auth()->user();

        $trips = Trip::where('driver_id', $user->id)
            ->with(['confirmedBookings.passenger'])
            ->orderBy('departure_time', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $trips
        ]);
    }
}
