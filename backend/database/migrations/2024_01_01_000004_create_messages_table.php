<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            
            $table->text('content'); // Max 300 caractères (validé en backend)
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            
            // Pour les messages rapides prédéfinis
            $table->boolean('is_template')->default(false);
            $table->enum('template_type', [
                'on_my_way',
                'arriving_soon',
                'vehicle_description',
                'custom'
            ])->default('custom');
            
            $table->timestamps();
            
            // Index
            $table->index(['booking_id', 'created_at']);
            $table->index(['receiver_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
