<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'email',
                'unique:users',
                'regex:/@etudiant\.cesi\.fr$/', // Email étudiant uniquement
            ],
            'password' => 'required|string|min:8|confirmed',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'field_of_study' => 'required|string',
            'year' => 'required|integer|min:1|max:5',
            'profile_type' => 'required|in:driver,passenger,both',
            'smoker' => 'boolean',
            'music' => 'boolean',
            'chattiness' => 'in:quiet,normal,chatty',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'field_of_study' => $request->field_of_study,
            'year' => $request->year,
            'profile_type' => $request->profile_type,
            'smoker' => $request->smoker ?? false,
            'music' => $request->music ?? true,
            'chattiness' => $request->chattiness ?? 'normal',
        ]);

        // TODO: Envoyer email de vérification
        
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Inscription réussie. Vérifiez votre email.',
            'data' => [
                'user' => $user,
                'token' => $token,
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Identifiants incorrects'
            ], 401);
        }

        $user = auth()->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => $token,
            ]
        ]);
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie'
        ]);
    }

    public function me()
    {
        $user = auth()->user();
        
        return response()->json([
            'success' => true,
            'data' => $user->load([
                'tripsAsDriver' => fn($q) => $q->active()->latest()->take(5),
                'bookings' => fn($q) => $q->with('trip')->latest()->take(5)
            ])
        ]);
    }

    public function refresh()
    {
        $token = JWTAuth::refresh(JWTAuth::getToken());

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token
            ]
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // TODO: Générer token et envoyer email
        
        return response()->json([
            'success' => true,
            'message' => 'Email de réinitialisation envoyé'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // TODO: Vérifier token et réinitialiser mot de passe
        
        return response()->json([
            'success' => true,
            'message' => 'Mot de passe réinitialisé avec succès'
        ]);
    }
}
