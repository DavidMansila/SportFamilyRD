<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('configuration_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('configuration_id')->constrained('configuration')->onDelete('cascade');
            $table->enum('status', ['disabled', 'enabled'])->default('enabled');
            $table->timestamps();
            $table->unique(['user_id', 'configuration_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('configuration_user');
    }
};
