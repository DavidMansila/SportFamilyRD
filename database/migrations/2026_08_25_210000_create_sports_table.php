<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('region');
            $table->string('type')->nullable();
            $table->string('popularity')->nullable();
            $table->string('image')->nullable();
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->jsonb('requirements')->nullable();
            $table->jsonb('places')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('sort_order');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sports');
    }
};
