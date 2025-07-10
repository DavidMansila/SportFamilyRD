<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trainer_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('achievement_date')->nullable();
            $table->timestamps();
            $table->foreign('trainer_id')->references('id')->on('trainer')->onDelete('cascade');
        });

        Schema::create('specialties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trainer_id');
            $table->string('description');
            $table->timestamps();
            $table->foreign('trainer_id')->references('id')->on('trainer')->onDelete('cascade');
        });

        Schema::create('training_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('trainer_id')->constrained('trainer')->onDelete('cascade');

            $table->string('sport_level');
            $table->text('description')->nullable();
            $table->string('status')->default('pendiente');

            $table->timestamps();
        });

        // Remove 'achievements' column 
        Schema::table('users', function (Blueprint $table) {

            $table->enum('category', ['Fútbol', 'Baloncesto', 'Tenis', 'Natación', 'Ciclismo', 'Atletismo', 'Artes Marciales'])->nullable()->default(null);
            if (Schema::hasColumn('users', 'achievements')) {
                $table->dropColumn('achievements');
            }
        });


        Schema::table('trainer', function (Blueprint $table) {
            if (Schema::hasColumn('trainer', 'achievements')) {
                $table->dropColumn('achievements');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specialties');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('training_requests');

        // Restore 'achievements' column to users table
        Schema::table('users', function (Blueprint $table) {
            $table->text('achievements')->nullable();
        });

        // Restore 'achievements' column to trainer table
        Schema::table('trainer', function (Blueprint $table) {
            $table->text('achievements')->nullable();
        });
    }
};
