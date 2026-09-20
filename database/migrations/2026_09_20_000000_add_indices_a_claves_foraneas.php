<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indices para las claves foraneas que no los tenian.
 *
 * Postgres crea un indice automaticamente para una PRIMARY KEY o una
 * restriccion UNIQUE, pero NO para una FOREIGN KEY. Solo se indexa el lado
 * referenciado (la clave primaria del padre), no la columna que referencia.
 * El resultado es que cada consulta "dame los hijos de este padre" -que es la
 * forma de practicamente todas las consultas de esta aplicacion- recorre la
 * tabla entera.
 *
 * El linter de la base de datos reporta 18 claves foraneas sin cubrir, y son
 * justo las de las consultas mas repetidas: los mensajes de un chat, los
 * comentarios de un post, las respuestas de un comentario, y sobre todo
 * trainer.user_id, que se consulta en CADA comprobacion de autorizacion de
 * entrenador (Trainer::where('user_id', ...)->exists()).
 *
 * Hoy no se nota porque las tablas estan casi vacias: un recorrido secuencial
 * de 30 filas es instantaneo. El coste aparece con el uso, y aparece de golpe.
 *
 * Lo que ya estaba cubierto y por eso NO se repite aqui:
 *   - chats.user_id        -> UNIQUE (user_id, trainer_id), sirve de prefijo
 *   - saved_news.user_id   -> UNIQUE (user_id, news_id)
 *   - likes.likeable_*     -> UNIQUE (likeable_id, likeable_type, user_id)
 *   - configuration_user.user_id -> UNIQUE (user_id, configuration_id)
 *
 * En messages el indice es COMPUESTO (chat_id, read) en lugar de simple: la
 * consulta del contador de no leidos filtra por los dos campos a la vez, y un
 * indice compuesto sirve igual para las consultas que solo usan chat_id,
 * porque es la primera columna. Dos indices en uno.
 */
return new class extends Migration
{
    /**
     * Tabla => [nombre del indice => columnas].
     *
     * Los nombres van explicitos para que la reversion sea exacta y no dependa
     * de como genere Laravel el nombre en cada motor.
     */
    private const INDICES = [
        'messages' => [
            'messages_chat_id_read_index' => ['chat_id', 'read'],
            'messages_sender_id_index' => ['sender_id'],
        ],
        'chats' => [
            'chats_trainer_id_index' => ['trainer_id'],
        ],
        'comments' => [
            'comments_post_id_index' => ['post_id'],
            'comments_user_id_index' => ['user_id'],
        ],
        'replies' => [
            'replies_comment_id_index' => ['comment_id'],
            'replies_user_id_index' => ['user_id'],
        ],
        'posts' => [
            'posts_user_id_index' => ['user_id'],
        ],
        'likes' => [
            'likes_user_id_index' => ['user_id'],
        ],
        'trainer' => [
            'trainer_user_id_index' => ['user_id'],
        ],
        'training_requests' => [
            'training_requests_user_id_index' => ['user_id'],
            'training_requests_trainer_id_index' => ['trainer_id'],
        ],
        'specialties' => [
            'specialties_trainer_id_index' => ['trainer_id'],
        ],
        'achievements' => [
            'achievements_trainer_id_index' => ['trainer_id'],
        ],
        'carts' => [
            'carts_user_id_index' => ['user_id'],
        ],
        'cart_items' => [
            'cart_items_cart_id_index' => ['cart_id'],
        ],
        'saved_news' => [
            'saved_news_news_id_index' => ['news_id'],
        ],
        'configuration_user' => [
            'configuration_user_configuration_id_index' => ['configuration_id'],
        ],
    ];

    public function up(): void
    {
        foreach (self::INDICES as $tabla => $indices) {
            // Se comprueba la tabla y cada columna antes de tocar nada: la base
            // de produccion y la de los tests no se crearon por el mismo camino,
            // y una migracion que asume una columna que no existe rompe el
            // arranque del contenedor entero (el Dockerfile corre migrate antes
            // de levantar el servidor).
            if (! Schema::hasTable($tabla)) {
                continue;
            }

            Schema::table($tabla, function (Blueprint $table) use ($tabla, $indices) {
                foreach ($indices as $nombre => $columnas) {
                    if (! Schema::hasColumns($tabla, $columnas)) {
                        continue;
                    }

                    $table->index($columnas, $nombre);
                }
            });
        }
    }

    public function down(): void
    {
        foreach (self::INDICES as $tabla => $indices) {
            if (! Schema::hasTable($tabla)) {
                continue;
            }

            // Solo se borra lo que existe: up() se salta los indices cuya
            // columna no esta, asi que down() no puede dar por hecho que estan
            // todos.
            $existentes = Schema::getIndexListing($tabla);

            Schema::table($tabla, function (Blueprint $table) use ($indices, $existentes) {
                foreach (array_keys($indices) as $nombre) {
                    if (in_array($nombre, $existentes, true)) {
                        $table->dropIndex($nombre);
                    }
                }
            });
        }
    }
};
