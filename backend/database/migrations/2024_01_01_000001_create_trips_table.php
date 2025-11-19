<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            
            // Informations trajet
            $table->string('departure_address');
            $table->decimal('departure_lat', 10, 7);
            $table->decimal('departure_lng', 10, 7);
            $table->string('arrival_address');
            $table->decimal('arrival_lat', 10, 7);
            $table->decimal('arrival_lng', 10, 7);
            
            // Horaires
            $table->dateTime('departure_time');
            $table->dateTime('arrival_time')->nullable();
            
            // Aller-retour
            $table->boolean('is_round_trip')->default(false);
            $table->dateTime('return_departure_time')->nullable();
            $table->dateTime('return_arrival_time')->nullable();
            
            // Récurrence
            $table->boolean('is_recurring')->default(false);
            $table->json('recurring_days')->nullable(); // [1,2,3,4,5] pour Lun-Ven
            $table->date('recurring_end_date')->nullable();
            
            // Places et statut
            $table->integer('available_seats');
            $table->integer('occupied_seats')->default(0);
            $table->enum('status', ['active', 'full', 'completed', 'cancelled'])->default('active');
            
            // Préférences
            $table->boolean('smoker_allowed')->default(false);
            $table->boolean('music_allowed')->default(true);
            $table->enum('chattiness_preference', ['quiet', 'normal', 'chatty'])->nullable();
            
            // Validation
            $table->boolean('auto_validation')->default(true);
            
            // Calculs
            $table->decimal('distance_km', 8, 2);
            $table->decimal('estimated_co2_saved', 10, 2)->default(0);
            
            $table->timestamps();
            
            // Index pour optimiser les recherches
            $table->index(['departure_time', 'status']);
            $table->index(['driver_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
