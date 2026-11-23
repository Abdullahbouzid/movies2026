<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('image')->nullable();
            $table->json('services')->nullable();
            $table->boolean('is_main')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_main', 'is_active']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')
                ->constrained()->nullOnDelete();
        });

        Schema::table('game_bookings', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')
                ->constrained()->nullOnDelete();
        });

        Schema::table('club_bookings', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')
                ->constrained()->nullOnDelete();
        });

        Schema::table('event_bookings', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        foreach (['event_bookings', 'club_bookings', 'game_bookings', 'bookings'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropConstrainedForeignId('branch_id');
            });
        }

        Schema::dropIfExists('branches');
    }
};
