<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade 'category' a la tabla de deportes.
 *
 * El directorio solo se podia filtrar por region, que no es como la gente busca
 * un deporte: se busca por lo que uno quiere hacer ("algo de pelota", "algo de
 * contacto", "algo en el agua"). La categoria es la que alimenta los botones de
 * filtro del Directorio.
 *
 * Va indexada porque el filtro del listado consulta siempre por este campo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sports', function (Blueprint $table) {
            $table->string('category', 60)->nullable()->after('type');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::table('sports', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn('category');
        });
    }
};
