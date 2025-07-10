<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Agregar campo quantity a calendars
        Schema::table('calendars', function (Blueprint $table) {
            $table->integer('quantity')->default(100)->after('id');
        });

        // Cambiar item_type de cart_items a enum
        Schema::table('cart_items', function (Blueprint $table) {
            $table->enum('item_type', ['product', 'event'])->default('product')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Quitar campo quantity de calendars
        Schema::table('calendars', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });

        // Volver item_type a string (asumiendo era string antes)
        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('item_type')->change();
        });
    }
};
