<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sube a Supabase Storage todo lo que hay en el disco local storage/app/public
 * (avatares, imagenes de posts, de noticias...).
 *
 * POR QUE HACE FALTA
 * ------------------
 * En Render el sistema de archivos es EFIMERO: cada vez que el contenedor se
 * reinicia o despierta, se pierde todo lo que no venga en la imagen. Con
 * PUBLIC_DISK_DRIVER=local, las fotos de perfil y las imagenes que suba la
 * gente desaparecen sin aviso. Supabase Storage las conserva.
 *
 * COMO USARLO
 * -----------
 *   1. En Supabase: Project Settings > Storage > S3 Connection, generar unas
 *      credenciales y copiarlas al .env:
 *
 *        AWS_ACCESS_KEY_ID=...
 *        AWS_SECRET_ACCESS_KEY=...
 *        AWS_BUCKET=sportfamilyrd
 *        AWS_DEFAULT_REGION=us-east-2
 *        AWS_ENDPOINT=https://<project-ref>.storage.supabase.co/storage/v1/s3
 *        AWS_USE_PATH_STYLE_ENDPOINT=true
 *
 *   2. php artisan storage:subir-supabase --dry-run     (ver que subiria)
 *   3. php artisan storage:subir-supabase               (subir de verdad)
 *
 *   4. Recien entonces, cambiar el disco publico de la aplicacion:
 *
 *        PUBLIC_DISK_DRIVER=s3
 *        PUBLIC_DISK_URL=https://<project-ref>.supabase.co/storage/v1/object/public/sportfamilyrd
 *
 * El orden importa: primero se suben los archivos y despues se cambia el disco.
 * Al reves, la aplicacion apuntaria a un bucket que todavia no los tiene y
 * TODAS las imagenes saldrian rotas mientras dure la subida.
 */
class SubirStorageASupabase extends Command
{
    protected $signature = 'storage:subir-supabase
                            {--dry-run : Solo lista lo que subiria, sin escribir nada}
                            {--sobrescribir : Vuelve a subir los archivos que ya existan en el bucket}
                            {--prefijo= : Sube solo lo que cuelgue de esta carpeta (p. ej. users)}';

    protected $description = 'Copia storage/app/public a Supabase Storage (bucket S3)';

    public function handle(): int
    {
        if (! $this->configuracionCompleta()) {
            return self::FAILURE;
        }

        // Disco construido a mano y anclado a storage/app/public.
        //
        // No se usa Storage::disk('local') porque en Laravel 11 su raiz es
        // storage/app/PRIVATE, no storage/app: buscar ahi "public/..." no
        // encontraba ni un archivo y el comando terminaba diciendo que no habia
        // nada que subir. Tampoco se usa el disco 'public', porque si ya esta
        // conmutado a s3 el origen seria el propio bucket.
        $origen = Storage::build([
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'throw' => false,
        ]);

        $destino = Storage::disk('s3');

        $raiz = trim((string) $this->option('prefijo'), '/');

        $archivos = collect($origen->allFiles($raiz))
            // Los .gitignore que Laravel deja en storage/ no pintan nada en el bucket.
            ->reject(fn($ruta) => str_ends_with($ruta, '.gitignore'))
            ->values();

        if ($archivos->isEmpty()) {
            $this->warn('No hay archivos en storage/app/public' . ($raiz !== '' ? '/' . $raiz : ''));
            return self::SUCCESS;
        }

        $seco = (bool) $this->option('dry-run');
        $sobrescribir = (bool) $this->option('sobrescribir');

        $this->info(($seco ? '[SIMULACION] ' : '') . "Archivos a procesar: {$archivos->count()}");
        $this->newLine();

        $subidos = $saltados = 0;
        $fallos = [];
        $bytes = 0;

        $barra = $this->output->createProgressBar($archivos->count());
        $barra->start();

        foreach ($archivos as $ruta) {
            // El origen ya esta anclado en storage/app/public, asi que la ruta
            // relativa ("users/7/avatar.jpg") es directamente la clave del bucket.
            $clave = $ruta;

            try {
                if (! $sobrescribir && $destino->exists($clave)) {
                    $saltados++;
                    $barra->advance();
                    continue;
                }

                if (! $seco) {
                    $contenido = $origen->get($ruta);
                    $destino->put($clave, $contenido, 'public');

                    // put() con 'throw' => false no lanza excepcion: hay que
                    // confirmar que el objeto quedo escrito de verdad.
                    if (! $destino->exists($clave)) {
                        throw new \RuntimeException('la subida no dejo el objeto en el bucket');
                    }

                    $bytes += strlen($contenido);
                }

                $subidos++;
            } catch (\Throwable $e) {
                $fallos[] = $clave . ': ' . $e->getMessage();
            }

            $barra->advance();
        }

        $barra->finish();
        $this->newLine(2);

        $this->info('Subidos:  ' . $subidos . ($seco ? ' (simulado)' : ' (' . $this->humano($bytes) . ')'));
        $this->line('Saltados: ' . $saltados . ' (ya estaban en el bucket)');

        if ($fallos) {
            $this->newLine();
            $this->error('Fallos: ' . count($fallos));
            foreach (array_slice($fallos, 0, 10) as $f) {
                $this->line('  ' . $f);
            }

            return self::FAILURE;
        }

        if (! $seco && $subidos > 0) {
            $this->newLine();
            $this->info('Listo. Ahora ya puedes cambiar el disco publico en el entorno:');
            $this->line('  PUBLIC_DISK_DRIVER=s3');
            $this->line('  PUBLIC_DISK_URL=' . $this->urlPublicaSugerida());
        }

        return self::SUCCESS;
    }

    private function configuracionCompleta(): bool
    {
        $faltan = collect([
            'AWS_ACCESS_KEY_ID' => config('filesystems.disks.s3.key'),
            'AWS_SECRET_ACCESS_KEY' => config('filesystems.disks.s3.secret'),
            'AWS_BUCKET' => config('filesystems.disks.s3.bucket'),
            'AWS_ENDPOINT' => config('filesystems.disks.s3.endpoint'),
            'AWS_DEFAULT_REGION' => config('filesystems.disks.s3.region'),
        ])->filter(fn($v) => blank($v))->keys();

        if ($faltan->isNotEmpty()) {
            $this->error('Faltan variables de entorno: ' . $faltan->implode(', '));
            $this->newLine();
            $this->line('Se generan en Supabase: Project Settings > Storage > S3 Connection.');
            $this->line('El comando no puede continuar sin ellas: subir a Storage exige');
            $this->line('credenciales de escritura, y la clave anon no sirve (el bucket');
            $this->line('tiene RLS y no hay ninguna politica de INSERT, que es lo correcto).');

            return false;
        }

        return true;
    }

    private function urlPublicaSugerida(): string
    {
        $endpoint = (string) config('filesystems.disks.s3.endpoint');
        $bucket = (string) config('filesystems.disks.s3.bucket');

        // De "https://<ref>.storage.supabase.co/storage/v1/s3" se saca el <ref>
        // para armar la URL publica, que vive en otro subdominio.
        if (preg_match('#https://([a-z0-9]+)\.(?:storage\.)?supabase\.co#i', $endpoint, $m)) {
            return "https://{$m[1]}.supabase.co/storage/v1/object/public/{$bucket}";
        }

        return '<url publica de tu bucket>';
    }

    private function humano(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        return $bytes < 1048576
            ? round($bytes / 1024, 1) . ' KB'
            : round($bytes / 1048576, 1) . ' MB';
    }
}
