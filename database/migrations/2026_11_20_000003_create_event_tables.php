<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('includes')->nullable();
            $table->unsignedSmallInteger('people_count')->default(1);
            $table->decimal('price', 10, 2);
            $table->string('image')->nullable();
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('event_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_ref')->unique();
            $table->foreignId('event_package_id')->nullable()->constrained()->nullOnDelete();
            $table->date('event_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('package_name');
            $table->unsignedSmallInteger('people_count')->default(1);
            $table->decimal('price', 10, 2);
            $table->string('qr_path')->nullable();
            $table->string('full_name');
            $table->string('country_name');
            $table->string('country_code', 8);
            $table->string('phone');
            $table->string('whatsapp_country_code', 8);
            $table->string('whatsapp');
            $table->text('notes')->nullable();
            $table->string('payment_status')->default('pending')->comment('pending|paid');
            $table->string('attendance_status')->default('pending')->comment('pending|attended');
            $table->timestamps();
            $table->index(['event_date', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_bookings');
        Schema::dropIfExists('event_packages');
    }
};
