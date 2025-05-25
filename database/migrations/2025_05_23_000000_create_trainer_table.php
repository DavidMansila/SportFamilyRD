<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trainer', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('city_country');
            $table->enum('sport_category', ['futbol', 'basket', 'tenis', 'natacion', 'otro']);
            $table->string('experience');
            $table->enum('level_of_certification', ['basico', 'intermedio', 'avanzado', 'experto']);
            $table->string('certificates_linked')->nullable();
            $table->text('description')->nullable();
            $table->text('achievements')->nullable();
            $table->text('schedule')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trainer');
    }
};
