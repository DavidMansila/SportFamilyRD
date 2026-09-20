<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Una sola linea por articulo dentro de cada carrito.
 *
 * CartController::addItem busca la linea existente, y si la encuentra suma; si
 * no, crea una nueva. Entre la lectura y la escritura no habia nada que
 * impidiera que dos peticiones simultaneas -dos pulsaciones seguidas en
 * "añadir al carrito"- pasaran las dos por la rama de crear y dejaran DOS
 * lineas del mismo articulo en el mismo carrito. Ademas de verse repetido, cada
 * linea arrastra su propio tope de 99 unidades, asi que el limite se podia
 * duplicar sin mas que provocar esa carrera.
 *
 * El codigo ya se corrigio para hacer todo el bloque en una transaccion con
 * bloqueo de fila, pero eso protege a esta aplicacion; la restriccion vive en
 * la base y protege el dato pase lo que pase -otro proceso, un seeder, una
 * importacion futura-. Es la diferencia entre "el codigo actual no lo hace" y
 * "no puede ocurrir".
 *
 * Comprobado antes de aplicarla: 0 duplicados en produccion.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cart_items')) {
            return;
        }

        // Red de seguridad por si en otra base si hubiera duplicados: se
        // consolidan en la linea mas antigua antes de crear el indice, en vez
        // de que la migracion aborte el arranque del contenedor.
        $duplicados = DB::table('cart_items')
            ->select('cart_id', 'item_type', 'item_id', DB::raw('MIN(id) as conservar'), DB::raw('SUM(quantity) as total'))
            ->groupBy('cart_id', 'item_type', 'item_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicados as $fila) {
            DB::table('cart_items')
                ->where('id', $fila->conservar)
                ->update(['quantity' => min((int) $fila->total, 99)]);

            DB::table('cart_items')
                ->where('cart_id', $fila->cart_id)
                ->where('item_type', $fila->item_type)
                ->where('item_id', $fila->item_id)
                ->where('id', '!=', $fila->conservar)
                ->delete();
        }

        Schema::table('cart_items', function (Blueprint $table) {
            $table->unique(['cart_id', 'item_type', 'item_id'], 'cart_items_linea_unica');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('cart_items')) {
            return;
        }

        if (in_array('cart_items_linea_unica', Schema::getIndexListing('cart_items'), true)) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropUnique('cart_items_linea_unica');
            });
        }
    }
};
