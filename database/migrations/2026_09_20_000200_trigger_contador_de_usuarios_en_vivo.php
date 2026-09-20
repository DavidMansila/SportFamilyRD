<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * El contador de usuarios del Home, en vivo.
 *
 * HomeVIew.vue se suscribe al canal 'home-stats-realtime' y espera el evento
 * 'user_count_changed' (linea 1243). En la base existia la funcion que lo
 * emite, broadcast_user_count_change(), pero NO existia ningun trigger que la
 * llamara: la pieza estaba puesta y desconectada, asi que ese evento no se
 * emitia nunca y la suscripcion del Home no servia para nada. El contador solo
 * se actualizaba cuando caducaba su cache de 60 segundos.
 *
 * Esta migracion crea la funcion y el trigger, y los deja versionados. Es lo
 * que evita que vuelva a pasar lo mismo: hasta ahora esto vivia unicamente
 * dentro de Supabase, donde no se revisa en un pull request ni se recrea al
 * montar el proyecto en otro sitio.
 *
 * DETALLES QUE IMPORTAN:
 *
 * - AFTER, nunca BEFORE. La funcion termina en RETURN NULL, y en un trigger
 *   BEFORE eso CANCELA la operacion: nadie podria registrarse.
 *
 * - FOR EACH STATEMENT y no FOR EACH ROW. La funcion no usa NEW ni OLD, solo
 *   cuenta filas, asi que una ejecucion por sentencia basta. Con ROW, una
 *   insercion masiva emitiria un aviso por cada fila.
 *
 * - Solo INSERT y DELETE. Un UPDATE no cambia cuantos usuarios hay; incluirlo
 *   solo mandaria avisos inutiles cada vez que alguien edita su perfil.
 *
 * - La funcion captura sus propias excepciones. Un trigger AFTER que falla
 *   aborta la transaccion que lo disparo: sin ese bloque, un problema de
 *   realtime impediria registrarse. El aviso es accesorio y debe fallar solo.
 */
return new class extends Migration
{
    public function up(): void
    {
        // plpgsql y realtime.send son de Postgres. La suite corre sobre SQLite,
        // donde esto no existe ni hace falta.
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
            create or replace function public.broadcast_user_count_change()
            returns trigger
            language plpgsql
            security definer
            set search_path to 'public'
            as $function$
            begin
                perform realtime.send(
                    jsonb_build_object(
                        'count', (select count(*) from users where email_verified_at is not null)
                    ),
                    'user_count_changed',
                    'home-stats-realtime',
                    false
                );

                return null;
            exception
                when others then
                    raise warning 'broadcast_user_count_change: no se pudo emitir el aviso: %', sqlerrm;
                    return null;
            end;
            $function$;

            drop trigger if exists tr_broadcast_user_count on public.users;

            create trigger tr_broadcast_user_count
            after insert or delete or update of email_verified_at on public.users
            for each statement
            execute function public.broadcast_user_count_change();
        SQL);

        // Los otros dos contadores del Home no necesitan trigger: HomeVIew.vue
        // escucha 'posts' y 'calendars' directamente con postgres_changes,
        // porque son contenido publico y ahi no hay nada que ocultar (a
        // diferencia de users, que lleva correo y telefono y por eso va por
        // Broadcast). Lo que faltaba es que esas dos tablas estuvieran en la
        // publicacion de replicacion: sin eso, Supabase no emite sus cambios y
        // las dos suscripciones del Home no reciben nada.
        foreach (['posts', 'calendars'] as $tabla) {
            $yaPublicada = DB::selectOne(
                'select 1 from pg_publication_tables where pubname = ? and schemaname = ? and tablename = ?',
                ['supabase_realtime', 'public', $tabla]
            );

            // ALTER PUBLICATION ... ADD TABLE falla si ya esta, y no admite
            // IF NOT EXISTS.
            if (! $yaPublicada) {
                DB::unprepared("alter publication supabase_realtime add table public.{$tabla};");
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // Se quita el trigger pero se conserva la funcion: es inofensiva sin
        // nada que la invoque, y asi revertir no borra codigo que quiza alguien
        // este llamando desde otro sitio.
        DB::unprepared('drop trigger if exists tr_broadcast_user_count on public.users;');

        foreach (['posts', 'calendars'] as $tabla) {
            $publicada = DB::selectOne(
                'select 1 from pg_publication_tables where pubname = ? and schemaname = ? and tablename = ?',
                ['supabase_realtime', 'public', $tabla]
            );

            if ($publicada) {
                DB::unprepared("alter publication supabase_realtime drop table public.{$tabla};");
            }
        }
    }
};
