<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('rater_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('rated_id')->constrained('users')->onDelete('cascade');
            
            $table->enum('rater_type', ['driver', 'passenger']);
            
            // Notation conducteur
            $table->integer('driving_rating')->nullable(); // 1-5
            $table->integer('punctuality_rating')->nullable(); // 1-5
            $table->integer('vehicle_rating')->nullable(); // 1-5 (propreté)
            
            // Notation passager
            $table->integer('passenger_punctuality_rating')->nullable(); // 1-5
            $table->integer('respect_rating')->nullable(); // 1-5
            
            // Note globale (calculée)
            $table->decimal('overall_rating', 3, 2);
            
            $table->text('comment')->nullable();
            
            $table->timestamps();
            
            // Contrainte unique : une seule notation par réservation et par rôle
            $table->unique(['booking_id', 'rater_id']);
            
            // Index
            $table->index('rated_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
