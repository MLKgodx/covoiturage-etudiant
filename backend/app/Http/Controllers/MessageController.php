<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function index($bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        $user = auth()->user();

        // Vérifier que l'utilisateur fait partie de cette réservation
        if ($booking->passenger_id !== $user->id && $booking->trip->driver_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $messages = Message::forBooking($bookingId)
            ->with(['sender', 'receiver'])
            ->get();

        // Marquer les messages reçus comme lus
        Message::where('booking_id', $bookingId)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    public function store(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        $user = auth()->user();

        // Vérifier que l'utilisateur fait partie de cette réservation
        if ($booking->passenger_id !== $user->id && $booking->trip->driver_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        // Vérifier que la réservation est confirmée
        if ($booking->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez envoyer des messages que pour des réservations confirmées'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:300',
            'template_type' => 'nullable|in:on_my_way,arriving_soon,vehicle_description,custom',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Déterminer le destinataire
        $receiverId = $booking->passenger_id === $user->id 
            ? $booking->trip->driver_id 
            : $booking->passenger_id;

        $message = Message::create([
            'booking_id' => $bookingId,
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'content' => $request->content,
            'is_template' => $request->template_type !== null && $request->template_type !== 'custom',
            'template_type' => $request->template_type ?? 'custom',
        ]);

        return response()->json([
            'success' => true,
            'data' => $message->load(['sender', 'receiver'])
        ], 201);
    }

    public function templates()
    {
        $templates = [
            [
                'type' => 'on_my_way',
                'label' => 'Je suis en route',
                'content' => "Je suis en route ! 🚗"
            ],
            [
                'type' => 'arriving_soon',
                'label' => "J'arrive bientôt",
                'content' => "J'arrive dans 5 minutes !"
            ],
            [
                'type' => 'vehicle_description',
                'label' => 'Description véhicule',
                'content' => "Voiture {color} {brand} {model}"
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $templates
        ]);
    }

    public function unreadCount()
    {
        $user = auth()->user();
        $count = $user->receivedMessages()->unread()->count();

        return response()->json([
            'success' => true,
            'data' => ['count' => $count]
        ]);
    }
}
