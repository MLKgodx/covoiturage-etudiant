<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->onDelete('cascade');
            $table->foreignId('passenger_id')->constrained('users')->onDelete('cascade');
            
            $table->integer('seats_booked')->default(1);
            $table->text('message')->nullable();
            
            $table->enum('status', [
                'pending',
                'confirmed',
                'refused',
                'cancelled_by_passenger',
                'cancelled_by_driver'
            ])->default('pending');
            
            // Notation
            $table->boolean('driver_rated')->default(false);
            $table->boolean('passenger_rated')->default(false);
            
            // CO2 économisé pour cette réservation
            $table->decimal('co2_saved', 10, 2)->default(0);
            
            $table->timestamps();
            
            // Index
            $table->index(['trip_id', 'status']);
            $table->index(['passenger_id', 'status']);
            
            // Contrainte unique pour éviter les doublons
            $table->unique(['trip_id', 'passenger_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
