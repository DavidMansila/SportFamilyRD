<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        // Cambiar item_type de cart_items a un string acotado a 'product'/'event'.
        // Nota: en Postgres, enum()->change() genera "ALTER COLUMN ... TYPE ... CHECK (...)"
        // en una sola sentencia, lo cual no es sintaxis valida de Postgres. Por eso el
        // cambio de tipo/default y el CHECK constraint se aplican por separado.
        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('item_type', 20)->default('product')->change();
        });

        DB::statement("ALTER TABLE cart_items ADD CONSTRAINT cart_items_item_type_check CHECK (item_type IN ('product', 'event'))");
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
        DB::statement('ALTER TABLE cart_items DROP CONSTRAINT IF EXISTS cart_items_item_type_check');

        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('item_type')->default(null)->change();
        });
    }
};
