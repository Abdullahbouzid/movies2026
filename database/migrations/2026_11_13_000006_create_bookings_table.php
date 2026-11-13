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
            $table->string('booking_ref')->unique();
            $table->foreignId('movie_id')->constrained('movies')->cascadeOnDelete();
            $table->foreignId('showtime_id')->constrained('showtimes')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->date('show_date');
            $table->string('full_name');
            $table->date('birthdate');
            $table->string('country_name');
            $table->string('country_code', 8);
            $table->string('phone');
            $table->string('whatsapp_country_code', 8);
            $table->string('whatsapp');
            $table->decimal('price', 10, 2);
            $table->string('payment_status')->default('pending')->comment('pending|paid');
            $table->string('attendance_status')->default('pending')->comment('pending|attended');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
