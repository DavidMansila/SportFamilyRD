<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Siembra completa de la aplicacion.
 *
 *   php artisan db:seed
 *
 * Los cuatro seeders son ADITIVOS e IDEMPOTENTES (firstOrCreate), asi que se
 * puede volver a ejecutar sin duplicar ni borrar nada de lo que ya exista.
 *
 * El orden importa: ForoSeeder necesita usuarios a los que atribuir las
 * publicaciones, asi que ComunidadSeeder va primero.
 *
 * Las noticias y los eventos NO se siembran aqui: vienen de los comandos
 * 'news:import' y 'calendar:import', que traen contenido real.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SportSeeder::class,     // catalogo del directorio de deportes
            ComunidadSeeder::class, // usuarios, entrenadores y configuraciones
            TiendaSeeder::class,    // 270 productos en 27 categorias
            ForoSeeder::class,      // hilos con comentarios, respuestas y likes
        ]);
    }
}
