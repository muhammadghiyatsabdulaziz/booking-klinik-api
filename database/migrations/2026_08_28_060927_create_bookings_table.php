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
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->date('booking_date');
            $table->time('time_slot');
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->text('complaint')->nullable(); // keluhan pasien
            $table->text('notes')->nullable(); // catatan dokter setelah konsultasi
            $table->timestamps();

            // Mencegah slot yang sama dibooking dua kali oleh dokter yang sama
            $table->unique(['doctor_id', 'booking_date', 'time_slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};