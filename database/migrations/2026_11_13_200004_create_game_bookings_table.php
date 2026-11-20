<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_ref')->unique();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('game_price_id')->nullable()->constrained('game_prices')->nullOnDelete();
            $table->foreignId('play_time_id')->nullable()->constrained('play_times')->nullOnDelete();
            $table->date('play_date');
            $table->string('duration_label');
            $table->unsignedInteger('duration_minutes');
            $table->decimal('price', 10, 2);
            $table->string('full_name');
            $table->string('country_name');
            $table->string('country_code', 8);
            $table->string('phone');
            $table->string('whatsapp_country_code', 8);
            $table->string('whatsapp');
            $table->string('payment_status')->default('pending')->comment('pending|paid');
            $table->string('attendance_status')->default('pending')->comment('pending|attended');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_bookings');
    }
};
