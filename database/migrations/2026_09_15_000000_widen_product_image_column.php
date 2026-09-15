<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * products.image pasa de varchar(255) a text.
 *
 * El campo guarda una URL, y ProductController la valida con la regla 'url'
 * SIN limite de longitud: cualquier URL de mas de 255 caracteres reventaba con
 * un error de Postgres ("value too long for type character varying(255)") que
 * llegaba al cliente como un 500, no como un error de validacion.
 *
 * No es teorico: 59 de las 270 URLs del catalogo pasan de 255 caracteres (la
 * mas larga, 452). Las URLs de miniatura de Wikimedia Commons y las de
 * cualquier CDN con parametros de transformacion los superan con facilidad.
 *
 * text en Postgres no cuesta mas que varchar: el rendimiento es identico y
 * desaparece un limite arbitrario que solo servia para romper.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('image')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Las URLs largas no caben en 255: se truncan al revertir, que es
        // justamente el motivo por el que existe esta migracion.
        Schema::table('products', function (Blueprint $table) {
            $table->string('image', 255)->nullable()->change();
        });
    }
};
