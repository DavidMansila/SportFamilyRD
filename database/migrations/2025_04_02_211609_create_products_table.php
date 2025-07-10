<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // ID
            $table->string('name'); // Nombre
            $table->text('description'); // Descripción
            $table->integer('stock'); // Stock
            $table->decimal('price', 10, 2); // Precio
            $table->string('category'); // Categoría
            $table->string('image')->nullable(); // Imagen (puede ser nula)
            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
