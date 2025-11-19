<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('photo')->nullable();
            $table->string('field_of_study'); // Filière
            $table->integer('year'); // Année d'étude
            $table->text('bio')->nullable();
            
            // Type de profil
            $table->enum('profile_type', ['driver', 'passenger', 'both'])->default('both');
            
            // Préférences
            $table->boolean('smoker')->default(false);
            $table->boolean('music')->default(true);
            $table->enum('chattiness', ['quiet', 'normal', 'chatty'])->default('normal');
            
            // Informations véhicule (pour conducteurs)
            $table->string('vehicle_brand')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->string('vehicle_color')->nullable();
            $table->integer('vehicle_seats')->nullable();
            
            // Statistiques
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('total_trips')->default(0);
            $table->decimal('total_co2_saved', 10, 2)->default(0);
            
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
