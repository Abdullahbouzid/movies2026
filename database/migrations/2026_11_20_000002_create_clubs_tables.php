<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('color_from')->nullable();
            $table->string('color_to')->nullable();
            $table->string('badge')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('club_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('includes')->nullable();
            $table->unsignedSmallInteger('people_count')->default(1);
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('price', 10, 2);
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('club_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_ref')->unique();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->foreignId('club_package_id')->nullable()->constrained()->nullOnDelete();
            $table->date('visit_date');
            $table->string('package_name');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('people_count')->default(1);
            $table->decimal('price', 10, 2);
            $table->string('qr_path')->nullable();
            $table->string('full_name');
            $table->string('country_name');
            $table->string('country_code', 8);
            $table->string('phone');
            $table->string('whatsapp_country_code', 8);
            $table->string('whatsapp');
            $table->string('payment_status')->default('pending')->comment('pending|paid');
            $table->string('attendance_status')->default('pending')->comment('pending|attended');
            $table->timestamps();
            $table->index(['visit_date', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_bookings');
        Schema::dropIfExists('club_packages');
        Schema::dropIfExists('clubs');
    }
};
