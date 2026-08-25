<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * RLS ya estaba habilitado en todas las tablas (Supabase lo trae asi por
 * defecto), pero sin ninguna politica -> deniega todo por defecto para los
 * roles anon/authenticated que usa Supabase Realtime y, en teoria, un
 * cliente que hable directo con PostgREST. Eloquent/Laravel no se ve
 * afectado (se conecta con el rol dueño de las tablas, que ignora RLS), pero
 * sin una politica SELECT, Supabase Realtime (postgres_changes) nunca
 * entrega eventos de esas tablas, y el trabajo previo de "RLS hardening" de
 * este proyecto nunca habia quedado en un archivo versionado.
 *
 * Solo se agregan politicas de LECTURA (nunca INSERT/UPDATE/DELETE): todas
 * las escrituras siguen pasando exclusivamente por la API de Laravel.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Tablas de contenido publico que ademas usan Supabase Realtime
        // (postgres_changes) en el frontend: Foro, Calendario, Noticias.
        $publicReadTables = ['posts', 'likes', 'comments', 'replies', 'calendars', '"NewsScrapping"'];

        foreach ($publicReadTables as $table) {
            $policyTable = trim($table, '"');
            DB::statement("DROP POLICY IF EXISTS \"Public read access\" ON public.{$table}");
            DB::statement("CREATE POLICY \"Public read access\" ON public.{$table} FOR SELECT TO anon, authenticated USING (true)");
        }

        // Tablas de contenido publico sin Realtime (Tienda, Directorio): misma
        // politica, por defensa en profundidad si algo llega a leerlas directo
        // via PostgREST/anon key en vez de pasar por la API.
        foreach (['products', 'sports'] as $table) {
            DB::statement("DROP POLICY IF EXISTS \"Public read access\" ON public.{$table}");
            DB::statement("CREATE POLICY \"Public read access\" ON public.{$table} FOR SELECT TO anon, authenticated USING (true)");
        }

        // trainer: solo las solicitudes aprobadas son publicas. Las pendientes o
        // rechazadas tienen telefono/email/ciudad y nunca deben quedar expuestas.
        DB::statement('DROP POLICY IF EXISTS "Public read of approved trainers" ON public.trainer');
        DB::statement("CREATE POLICY \"Public read of approved trainers\" ON public.trainer FOR SELECT TO anon, authenticated USING (status = 'approved')");

        // users y el resto de las tablas (chats, messages, carts, cart_items,
        // training_requests, configuration*, sessions, jobs, personal_access_tokens,
        // etc.) se quedan sin ninguna politica a proposito: RLS habilitado sin
        // politica = denegado por defecto para anon/authenticated. Es el estado
        // correcto para datos privados o infraestructura interna de Laravel.
    }

    public function down(): void
    {
        $tables = ['posts', 'likes', 'comments', 'replies', 'calendars', '"NewsScrapping"', 'products', 'sports'];
        foreach ($tables as $table) {
            DB::statement("DROP POLICY IF EXISTS \"Public read access\" ON public.{$table}");
        }
        DB::statement('DROP POLICY IF EXISTS "Public read of approved trainers" ON public.trainer');
    }
};
