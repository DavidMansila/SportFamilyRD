<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Asigna una foto de perfil a cada usuario que no tenga una.
 *
 * El retrato se elige segun el genero del nombre de pila, para que la foto
 * corresponda con la persona y el directorio no parezca relleno.
 *
 * Va como comando y no como seeder a proposito: descarga imagenes por red, y
 * un seeder que depende de internet es un seeder que falla en el peor momento.
 *
 *   php artisan avatares:generar          solo a quien no tenga foto
 *   php artisan avatares:generar --todos  reasigna a todo el mundo
 *
 * OJO EN PRODUCCION: con PUBLIC_DISK_DRIVER=local en Render, el sistema de
 * archivos es efimero y estas fotos desaparecen al reiniciar el contenedor.
 * Para que persistan hay que usar el disco s3 (Supabase Storage).
 */
class GenerarAvatares extends Command
{
    protected $signature = 'avatares:generar {--todos : Reasigna la foto tambien a quien ya tenga una}';

    protected $description = 'Descarga y asigna fotos de perfil acordes al nombre de cada usuario';

    /** Nombres de pila femeninos presentes en los datos sembrados. */
    private const FEMENINOS = [
        'yamilex', 'massiel', 'nicole', 'wendy', 'andrea', 'katherine', 'genesis',
        'claudia', 'paola', 'rosanna', 'yohanna', 'luisa', 'sandra', 'marisol',
        'ingrid', 'yeimy', 'dahiana', 'carla', 'vanessa', 'rosa', 'laura',
        'patricia', 'yaneris', 'belkis', 'cinthia', 'marlenny', 'antonia',
        'ivelisse', 'esther', 'xiomara', 'deyanira', 'milagros', 'yaquelin',
        'ruth', 'digna', 'solange', 'maribel', 'maria', 'ana', 'carmen',
    ];

    /** Nombres de pila masculinos presentes en los datos sembrados. */
    private const MASCULINOS = [
        'carlos', 'jose', 'rafael', 'luis', 'pedro', 'manuel', 'francisco',
        'ramon', 'hector', 'juan', 'david', 'elvis', 'miguel', 'dario',
        'kelvin', 'eddy', 'alexis', 'roberto', 'adolfo', 'emilio', 'freddy',
        'ismael', 'ronny', 'amaury', 'gustavo', 'wilson', 'julio', 'norberto',
        'domingo', 'felix', 'osvaldo', 'bienvenido', 'santiago', 'joaquin',
        'radhames', 'ernesto', 'pablo', 'antonio', 'jesus',
    ];

    public function handle(): int
    {
        $usuarios = $this->option('todos')
            ? User::orderBy('id')->get()
            : User::whereNull('image')->orderBy('id')->get();

        if ($usuarios->isEmpty()) {
            $this->info('Todos los usuarios ya tienen foto. Usa --todos para reasignarlas.');
            return self::SUCCESS;
        }

        $this->info("Asignando fotos a {$usuarios->count()} usuarios...");
        $barra = $this->output->createProgressBar($usuarios->count());
        $barra->start();

        // Contadores independientes por genero: asi cada persona recibe un
        // retrato distinto en vez de repetirse cada pocas filas.
        $indices = ['men' => 0, 'women' => 0];
        $ok = 0;
        $fallos = [];

        foreach ($usuarios as $user) {
            $genero = $this->genero($user->name);
            $n = $indices[$genero] % 100;
            $indices[$genero]++;

            $url = "https://randomuser.me/api/portraits/{$genero}/{$n}.jpg";

            try {
                $contenido = @file_get_contents($url, false, stream_context_create([
                    'http' => ['timeout' => 20, 'user_agent' => 'SportFamilyRD/1.0'],
                ]));

                if ($contenido === false || strlen($contenido) < 1000) {
                    throw new \RuntimeException('descarga vacia o incompleta');
                }

                // Nombre estable: al reejecutar sobrescribe en vez de acumular
                // archivos huerfanos en la carpeta del usuario.
                $archivo = 'avatar.jpg';
                Storage::disk('public')->put("users/{$user->id}/{$archivo}", $contenido);

                $user->forceFill(['image' => $archivo])->save();
                $ok++;
            } catch (\Throwable $e) {
                $fallos[] = "{$user->name} (#{$user->id}): " . $e->getMessage();
            }

            $barra->advance();
        }

        $barra->finish();
        $this->newLine(2);
        $this->info("Fotos asignadas: {$ok} de {$usuarios->count()}");

        foreach (array_slice($fallos, 0, 10) as $f) {
            $this->warn('  fallo: ' . $f);
        }

        return self::SUCCESS;
    }

    /** 'women' o 'men' segun el nombre de pila. */
    private function genero(string $nombre): string
    {
        $pila = $this->normalizar(preg_split('/\s+/', trim($nombre))[0] ?? '');

        if (in_array($pila, self::FEMENINOS, true)) {
            return 'women';
        }

        if (in_array($pila, self::MASCULINOS, true)) {
            return 'men';
        }

        // Respaldo para nombres no listados: en español la -a final es
        // femenina en la gran mayoria de los casos.
        return str_ends_with($pila, 'a') ? 'women' : 'men';
    }

    private function normalizar(string $t): string
    {
        return strtr(mb_strtolower($t), [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', 'ü' => 'u',
        ]);
    }
}
