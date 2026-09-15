<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\Reply;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Conversaciones del foro: publicaciones con comentarios, respuestas y likes.
 *
 * Cada hilo esta escrito como una conversacion real entre varias personas -hay
 * preguntas, respuestas utiles, desacuerdos y seguimiento- porque el objetivo
 * es que alguien que entre por primera vez encuentre algo que leer, no relleno.
 *
 * Las fechas se reparten en los ultimos cuatro meses y cada comentario es
 * posterior a su publicacion, y cada respuesta posterior a su comentario.
 *
 * Aditivo: si un post con el mismo titulo ya existe, no se duplica.
 */
class ForoSeeder extends Seeder
{
    private const CATEGORIAS = [
        'deporte' => '🏅 Deporte',
        'gimnasio' => '🏋️ Gimnasio y Fitness',
        'lugares' => '📍 Lugares y Centros',
        'bienestar' => '🧠 Consejos y Bienestar',
    ];

    public function run(): void
    {
        $usuarios = User::where('user_type', '!=', 'admin')->pluck('id')->all();

        if (count($usuarios) < 10) {
            $this->command->error('  Hacen falta usuarios. Ejecuta ComunidadSeeder primero.');
            return;
        }

        $posts = 0;
        $comentarios = 0;
        $respuestas = 0;
        $likes = 0;

        foreach ($this->hilos() as $indice => $hilo) {
            if (Post::where('titulo', $hilo['titulo'])->exists()) {
                continue;
            }

            $fecha = now()->subDays(random_int(3, 120))->subHours(random_int(0, 23));
            $autor = $usuarios[($indice * 7) % count($usuarios)];

            $post = Post::create([
                'titulo' => $hilo['titulo'],
                'contenido' => $hilo['contenido'],
                'categoria' => self::CATEGORIAS[$hilo['categoria']],
                'user_id' => $autor,
                'likes_quantity' => 0,
            ]);

            $this->fechar('posts', $post->id, $fecha);
            $posts++;

            $likes += $this->repartirLikes(Post::class, $post->id, $usuarios, $autor, $hilo['likes'], $fecha);

            $fechaComentario = $fecha->copy();

            foreach ($hilo['comentarios'] as $ci => $c) {
                $fechaComentario = $fechaComentario->copy()->addHours(random_int(1, 20));

                if ($fechaComentario->isFuture()) {
                    $fechaComentario = now()->subMinutes(random_int(5, 600));
                }

                $autorComentario = $usuarios[($indice * 3 + $ci * 5 + 1) % count($usuarios)];

                $comentario = Comment::create([
                    'post_id' => $post->id,
                    'user_id' => $autorComentario,
                    'texto' => $c['texto'],
                ]);

                $this->fechar('comments', $comentario->id, $fechaComentario);
                $comentarios++;

                $likes += $this->repartirLikes(Comment::class, $comentario->id, $usuarios, $autorComentario, $c['likes'] ?? 0, $fechaComentario);

                $fechaRespuesta = $fechaComentario->copy();

                foreach ($c['respuestas'] ?? [] as $ri => $r) {
                    $fechaRespuesta = $fechaRespuesta->copy()->addHours(random_int(1, 12));

                    if ($fechaRespuesta->isFuture()) {
                        $fechaRespuesta = now()->subMinutes(random_int(2, 240));
                    }

                    // La respuesta la escribe el autor del post cuando el texto
                    // responde a una pregunta dirigida a el.
                    $autorRespuesta = ($r['delAutor'] ?? false)
                        ? $autor
                        : $usuarios[($indice * 11 + $ci * 3 + $ri * 7 + 4) % count($usuarios)];

                    $respuesta = Reply::create([
                        'comment_id' => $comentario->id,
                        'user_id' => $autorRespuesta,
                        'texto' => $r['texto'],
                    ]);

                    $this->fechar('replies', $respuesta->id, $fechaRespuesta);
                    $respuestas++;

                    $likes += $this->repartirLikes(Reply::class, $respuesta->id, $usuarios, $autorRespuesta, $r['likes'] ?? 0, $fechaRespuesta);
                }
            }
        }

        $this->command->info("  Publicaciones: {$posts}  ·  Comentarios: {$comentarios}  ·  Respuestas: {$respuestas}  ·  Likes: {$likes}");
    }

    /** Reparte $cantidad de likes entre usuarios distintos del autor. */
    private function repartirLikes(string $tipo, int $id, array $usuarios, int $autor, int $cantidad, $fecha): int
    {
        if ($cantidad < 1) {
            return 0;
        }

        $candidatos = array_values(array_diff($usuarios, [$autor]));
        shuffle($candidatos);
        $elegidos = array_slice($candidatos, 0, min($cantidad, count($candidatos)));

        foreach ($elegidos as $uid) {
            Like::create([
                'user_id' => $uid,
                'likeable_type' => $tipo,
                'likeable_id' => $id,
            ]);
        }

        if ($tipo === Post::class) {
            DB::table('posts')->where('id', $id)->update(['likes_quantity' => count($elegidos)]);
        }

        return count($elegidos);
    }

    private function fechar(string $tabla, int $id, $fecha): void
    {
        DB::table($tabla)->where('id', $id)->update([
            'created_at' => $fecha,
            'updated_at' => $fecha,
        ]);
    }

    private function hilos(): array
    {
        return [
            [
                'titulo' => '¿Dónde juegan fútbol amateur los domingos en Santo Domingo?',
                'categoria' => 'deporte',
                'likes' => 14,
                'contenido' => "Llevo tres meses en la capital y no encuentro dónde caerle a un partido los domingos. Juego de mediocampista, nivel intermedio, nada de tomárselo tan en serio pero tampoco puro relajo.\n\n¿Alguien sabe de grupos que se reúnan por la zona de Naco, Piantini o el Mirador? Estoy dispuesto a moverme si el ambiente vale la pena.",
                'comentarios' => [
                    [
                        'texto' => "En el Parque Mirador Sur hay gente jugando desde las 7 de la mañana los domingos. Llega temprano porque después de las 9 el sol pega durísimo y las canchas se llenan.",
                        'likes' => 9,
                        'respuestas' => [
                            ['texto' => 'Confirmo. Yo caigo ahí casi todos los domingos. Es informal, llegas y te anotas para el siguiente partido.', 'likes' => 5],
                            ['texto' => '¿A qué hora dejan de armar equipos más o menos? Es que yo vivo lejos y quiero calcular.', 'likes' => 1],
                            ['texto' => 'Perfecto, me voy a dar la vuelta este domingo. Gracias por el dato.', 'likes' => 3, 'delAutor' => true],
                        ],
                    ],
                    [
                        'texto' => "También hay una liga los sábados en San Isidro, más organizada, con árbitro y todo. Cobran una cuota pequeña por partido para pagar la cancha. Si te interesa te paso el contacto.",
                        'likes' => 7,
                        'respuestas' => [
                            ['texto' => 'Me interesa bastante, la verdad. ¿Es por mensajería o tienen grupo?', 'likes' => 2, 'delAutor' => true],
                            ['texto' => 'Tienen grupo. El nivel ahí es más alto, te aviso de una vez para que no te agarre de sorpresa.', 'likes' => 4],
                        ],
                    ],
                    [
                        'texto' => 'Ojo con las canchas del Mirador después de que llueve, se ponen resbalosas y se han lesionado varios. Mejor esperar un día.',
                        'likes' => 6,
                    ],
                ],
            ],
            [
                'titulo' => 'Bajé de 1:05 a 52 minutos en 10K: lo que sí me funcionó',
                'categoria' => 'deporte',
                'likes' => 31,
                'contenido' => "Empecé a correr hace año y medio pesando 95 kilos y sin poder terminar un kilómetro sin caminar. Hace dos semanas hice 52:14 en los 10K de La Vega.\n\nLo que de verdad movió la aguja:\n\n1. Correr lento el 80% del tiempo. Suena contraintuitivo pero fue lo que más cambió todo.\n2. Una sola sesión de series por semana, nada más.\n3. Dormir. Cuando dormía mal, los entrenamientos se sentían el doble de duros.\n4. Dejar de compararme con la gente de mi grupo.\n\nLo que no me sirvió: comprar zapatillas caras pensando que iban a hacer el trabajo, y salir a correr fuerte todos los días.",
                'comentarios' => [
                    [
                        'texto' => 'El punto 1 es el que más cuesta aceptar. Yo duré un año corriendo todo a la misma intensidad y estancado. Cuando bajé el ritmo en los rodajes fáciles empecé a mejorar de verdad.',
                        'likes' => 18,
                        'respuestas' => [
                            ['texto' => '¿Cómo saben que van "lento"? Yo no tengo pulsómetro ni nada.', 'likes' => 4],
                            ['texto' => 'La prueba de la conversación: si puedes hablar una frase completa sin quedarte sin aire, vas al ritmo correcto. No hace falta reloj.', 'likes' => 15],
                            ['texto' => 'Justo así lo hacía yo al principio. Funciona.', 'likes' => 6, 'delAutor' => true],
                        ],
                    ],
                    [
                        'texto' => 'Felicidades, tremendo progreso. ¿Cuántos kilómetros a la semana llegaste a hacer?',
                        'likes' => 5,
                        'respuestas' => [
                            ['texto' => 'Arranqué con 15 km semanales y terminé alrededor de 45. Subí muy despacio, como 10% por semana, justo para no lesionarme.', 'likes' => 11, 'delAutor' => true],
                        ],
                    ],
                    [
                        'texto' => 'Lo del sueño nadie lo menciona y es clave. Yo trabajo turnos rotativos y se me nota muchísimo en el rendimiento.',
                        'likes' => 12,
                    ],
                    [
                        'texto' => 'Discrepo un poco con lo de las zapatillas. No hacen el trabajo, cierto, pero unas malas sí te pueden lesionar. Yo tuve fascitis por correr con unas gastadas.',
                        'likes' => 10,
                        'respuestas' => [
                            ['texto' => 'Totalmente de acuerdo, me expliqué mal. Lo que quise decir es que unas caras no te hacen más rápido. Pero unas rotas sí te hacen daño.', 'likes' => 9, 'delAutor' => true],
                        ],
                    ],
                ],
            ],
            [
                'titulo' => 'Rutina de gimnasio para principiantes: 3 días a la semana',
                'categoria' => 'gimnasio',
                'likes' => 27,
                'contenido' => "Me piden esto mucho, así que lo dejo por escrito de una vez.\n\nLunes, miércoles y viernes. Cuerpo completo cada día, porque para alguien que empieza es más eficiente que dividir por grupos musculares.\n\nSentadilla 3x8, press de banca 3x8, remo 3x10, peso muerto 1x5, plancha 3x30 segundos.\n\nSube 2,5 kg cuando completes todas las series con buena técnica. Sin prisa. El primer mes es para aprender a moverte, no para levantar pesado.\n\nSi no sabes la técnica, paga una sesión con alguien que sepa. Es la mejor inversión que vas a hacer.",
                'comentarios' => [
                    [
                        'texto' => '¿Peso muerto desde la primera semana? A mí me da miedo la espalda.',
                        'likes' => 8,
                        'respuestas' => [
                            ['texto' => 'Con barra vacía o muy ligero al principio, solo para aprender el patrón de movimiento. El miedo viene de hacerlo pesado sin técnica, no del ejercicio.', 'likes' => 14, 'delAutor' => true],
                            ['texto' => 'Yo empecé con peso muerto rumano con mancuernas livianas y me sirvió para agarrar confianza antes de la barra.', 'likes' => 7],
                        ],
                    ],
                    [
                        'texto' => 'Llevo dos meses con algo parecido y he subido 15 kg en sentadilla. Lo más importante para mí fue anotar cada sesión, si no se me olvidaba qué peso había usado.',
                        'likes' => 13,
                        'respuestas' => [
                            ['texto' => '¿Qué usas para anotar? ¿Libreta o alguna app?', 'likes' => 2],
                            ['texto' => 'Libreta de toda la vida. Las apps me distraían con el teléfono entre series.', 'likes' => 9],
                        ],
                    ],
                    [
                        'texto' => 'Agregaría trabajo de movilidad de cadera antes de sentadilla. Mucha gente no baja bien no por falta de fuerza sino por movilidad.',
                        'likes' => 11,
                    ],
                ],
            ],
            [
                'titulo' => 'Piscinas públicas y semipúblicas en Santiago: lo que encontré',
                'categoria' => 'lugares',
                'likes' => 19,
                'contenido' => "Me mudé a Santiago y pasé dos semanas buscando dónde nadar. Comparto lo que averigüé por si le sirve a alguien.\n\nComplejo Deportivo Cibao: piscina olímpica de 50 metros. Horario de mañana bastante libre, en la tarde se llena de equipos. Cuota mensual razonable.\n\nVarios hoteles de la zona venden membresías de piscina por separado. Más caro pero mucho menos gente.\n\nHay clubes privados con piscina de 25 metros, requieren socio que te recomiende.\n\nSi alguien conoce algo más, agréguenlo abajo y voy actualizando el post.",
                'comentarios' => [
                    [
                        'texto' => 'El Complejo Cibao de 6 a 8 de la mañana está prácticamente vacío. Es mi horario desde hace dos años.',
                        'likes' => 10,
                        'respuestas' => [
                            ['texto' => '¿Hay que reservar carril o llegas y ya?', 'likes' => 3],
                            ['texto' => 'Llegas y ya. A esa hora siempre hay carriles libres.', 'likes' => 6],
                        ],
                    ],
                    [
                        'texto' => 'Ojo con revisar el mantenimiento del agua antes de comprometerte con una mensualidad. Estuve en un sitio donde el cloro estaba altísimo y terminé con irritación en los ojos toda la temporada.',
                        'likes' => 15,
                        'respuestas' => [
                            ['texto' => 'Buen punto. ¿Cómo se nota eso a simple vista?', 'likes' => 4],
                            ['texto' => 'Olor fuerte a cloro es mala señal, aunque suene raro. Una piscina bien tratada casi no huele.', 'likes' => 12],
                        ],
                    ],
                    [
                        'texto' => 'Gracias por el resumen, justo estaba buscando esto. Voy a probar el Cibao la semana que viene.',
                        'likes' => 5,
                    ],
                ],
            ],
            [
                'titulo' => 'Cómo manejo la ansiedad antes de competir',
                'categoria' => 'bienestar',
                'likes' => 22,
                'contenido' => "Llevo compitiendo en atletismo desde los 16 y durante años los nervios me arruinaban las carreras. Me pasaba que entrenaba bien toda la temporada y el día de la competencia rendía muy por debajo.\n\nLo que me ha ayudado:\n\nTener una rutina fija de calentamiento, siempre igual. Le da al cerebro algo conocido en un ambiente que no lo es.\n\nAceptar los nervios en vez de pelear con ellos. Los nervios son energía, el problema es interpretarlos como amenaza.\n\nNo mirar a los rivales calentando. Eso nunca me ha ayudado en nada.\n\nRespiración 4-7-8 los diez minutos antes de la salida.\n\nNo es magia y todavía me pongo nervioso, pero ya no me paraliza.",
                'comentarios' => [
                    [
                        'texto' => 'Lo de no mirar a los rivales es tan cierto. Yo me desanimaba viendo a gente calentando y ni había empezado la carrera.',
                        'likes' => 16,
                        'respuestas' => [
                            ['texto' => 'Me pasa igual pero en baloncesto. Ver al otro equipo en el calentamiento me metía en la cabeza cosas que no eran.', 'likes' => 8],
                        ],
                    ],
                    [
                        'texto' => '¿La respiración 4-7-8 cómo es exactamente? He escuchado el nombre pero nunca la he hecho bien.',
                        'likes' => 6,
                        'respuestas' => [
                            ['texto' => 'Inhalas contando 4, retienes 7, exhalas 8. Lo importante es que la exhalación sea más larga que la inhalación, eso es lo que baja el pulso.', 'likes' => 17, 'delAutor' => true],
                            ['texto' => 'La probé anoche para dormir y funcionó. No sabía que servía también para esto.', 'likes' => 7],
                        ],
                    ],
                    [
                        'texto' => 'Añado una que me sirve: comer exactamente lo mismo que como antes de un entrenamiento fuerte. Nada nuevo el día de competencia.',
                        'likes' => 14,
                    ],
                    [
                        'texto' => 'Gracias por escribir esto. Se habla poquísimo de la parte mental y es la mitad del deporte.',
                        'likes' => 11,
                    ],
                ],
            ],
            [
                'titulo' => 'Busco compañeros para rutas de ciclismo en Puerto Plata',
                'categoria' => 'deporte',
                'likes' => 12,
                'contenido' => "Salgo los domingos a las 6 de la mañana, entre 40 y 70 km según el día. Ritmo tranquilo, 25 a 28 km/h en llano, sin dejar a nadie atrás.\n\nConozco bien la costa hacia Sosúa y también rutas hacia el interior con más desnivel para el que quiera sufrir un poco.\n\nSi alguien anda por la zona y quiere unirse, avise. Mientras más seamos, más seguro es en carretera.",
                'comentarios' => [
                    [
                        'texto' => 'Me apunto. Tengo una de ruta y llevo un año rodando solo, que es aburridísimo y además peligroso por aquí.',
                        'likes' => 8,
                        'respuestas' => [
                            ['texto' => 'Perfecto, este domingo salimos desde el malecón a las 6. Llega 10 minutos antes.', 'likes' => 6, 'delAutor' => true],
                        ],
                    ],
                    [
                        'texto' => 'Recomendación para todos: luces traseras encendidas aunque sea de día. Aquí los carros se te pegan mucho y se nota la diferencia.',
                        'likes' => 13,
                        'respuestas' => [
                            ['texto' => 'Esto debería ser obligatorio. Yo llevo dos, una fija y una intermitente.', 'likes' => 9],
                        ],
                    ],
                ],
            ],
            [
                'titulo' => '¿Vale la pena la creatina? Mi experiencia después de 6 meses',
                'categoria' => 'gimnasio',
                'likes' => 24,
                'contenido' => "Pregunta que sale cada semana, así que dejo lo mío.\n\nTomé 5 g diarios de monohidratada durante seis meses, sin fase de carga.\n\nLo que noté: aguanto una o dos repeticiones más en las series pesadas, y me recupero mejor entre sesiones. Subí unos 2 kg en las primeras semanas, casi todo agua, y eso es normal.\n\nLo que no noté: nada mágico. No sustituye entrenar bien ni comer suficiente.\n\nEs de los pocos suplementos con evidencia sólida detrás y es barato. Pero si tu alimentación o tu sueño están mal, arregla eso primero.",
                'comentarios' => [
                    [
                        'texto' => 'Lo de los 2 kg de agua asusta a mucha gente y abandonan a las dos semanas. Vale la pena avisarlo desde el principio.',
                        'likes' => 15,
                        'respuestas' => [
                            ['texto' => 'Exacto, me pasó a mí. Pensé que estaba engordando y casi la dejo.', 'likes' => 8],
                            ['texto' => 'Es agua intramuscular, no grasa. Además hace ver el músculo más lleno.', 'likes' => 10],
                        ],
                    ],
                    [
                        'texto' => '¿Hay que ciclarla o se puede tomar continuo?',
                        'likes' => 7,
                        'respuestas' => [
                            ['texto' => 'Continuo. Lo de ciclar no tiene respaldo, es de las cosas que se repiten en el gimnasio sin fundamento.', 'likes' => 13, 'delAutor' => true],
                        ],
                    ],
                    [
                        'texto' => 'Importante: tomar bastante agua. Yo me descuidé con eso y me daban calambres.',
                        'likes' => 9,
                    ],
                ],
            ],
            [
                'titulo' => 'Canchas de baloncesto con buena iluminación nocturna',
                'categoria' => 'lugares',
                'likes' => 17,
                'contenido' => "Trabajo de día y solo puedo jugar de noche. Hago una lista de canchas donde de verdad se ve bien después de las 7.\n\nParque del Este: luces buenas, se llena rápido.\nCancha del Centro Olímpico: la mejor iluminación que he visto, pero horario limitado.\nVarias canchas de barrio tienen luces pero la mitad fundidas.\n\n¿Qué otras conocen? Especialmente por la zona oriental.",
                'comentarios' => [
                    [
                        'texto' => 'La del Centro Olímpico cierra a las 10 en punto y sacan a todo el mundo. Buena para entre semana.',
                        'likes' => 9,
                    ],
                    [
                        'texto' => 'En Los Mina hay una cancha techada que arreglaron el año pasado. Buena luz y no se moja cuando llueve.',
                        'likes' => 12,
                        'respuestas' => [
                            ['texto' => '¿Esa es la que está cerca del liceo? Paso por ahí casi todos los días.', 'likes' => 3],
                            ['texto' => 'Esa misma. De 7 a 9 siempre hay juego.', 'likes' => 7],
                            ['texto' => 'Anotado, voy a darme una vuelta. Gracias.', 'likes' => 4, 'delAutor' => true],
                        ],
                    ],
                ],
            ],
            [
                'titulo' => 'Volver a entrenar después de una lesión: mi proceso con el menisco',
                'categoria' => 'bienestar',
                'likes' => 20,
                'contenido' => "Me operaron del menisco hace ocho meses jugando fútbol. Cuento el proceso porque cuando me pasó no encontré casi nada escrito desde la experiencia de alguien normal.\n\nPrimer mes: nada. Reposo y los ejercicios que mandó el fisio, por aburridos que fueran.\n\nMes 2 y 3: fuerza sin impacto. Bicicleta estática, sentadillas de rango corto, mucho trabajo de cuádriceps.\n\nMes 4 a 6: trote muy progresivo. Empecé con 5 minutos.\n\nMes 7: primer partido, 20 minutos y con miedo.\n\nHoy juego normal. El error que casi cometo fue querer volver al mes 3 porque ya no me dolía. No doler no es lo mismo que estar listo.",
                'comentarios' => [
                    [
                        'texto' => 'Esa última frase debería estar en la pared de todos los gimnasios. Yo me relesioné dos veces por eso exactamente.',
                        'likes' => 19,
                        'respuestas' => [
                            ['texto' => 'Igual yo con un tobillo. Tres recaídas hasta que aprendí.', 'likes' => 7],
                        ],
                    ],
                    [
                        'texto' => '¿Hiciste fisioterapia todo el tiempo o solo al principio?',
                        'likes' => 6,
                        'respuestas' => [
                            ['texto' => 'Los primeros cuatro meses con sesiones, después seguí solo con los ejercicios que me dejó pautados. Todavía hago dos de ellos como mantenimiento.', 'likes' => 11, 'delAutor' => true],
                        ],
                    ],
                    [
                        'texto' => 'Gracias por compartirlo con tanto detalle. Estoy en el mes 2 de algo parecido y me sirve bastante saber qué esperar.',
                        'likes' => 13,
                        'respuestas' => [
                            ['texto' => 'Ánimo, el mes 2 y 3 son los más frustrantes. De ahí en adelante se siente el progreso.', 'likes' => 10, 'delAutor' => true],
                        ],
                    ],
                ],
            ],
            [
                'titulo' => 'Torneo de voleibol de playa en Boca Chica: buscamos equipos',
                'categoria' => 'deporte',
                'likes' => 15,
                'contenido' => "Estamos organizando un torneo de voleibol de playa 4x4 para el mes que viene en Boca Chica.\n\nCategorías mixta y libre. Inscripción por equipo, incluye arbitraje e hidratación.\n\nYa tenemos seis equipos confirmados y queremos llegar a doce para que valga la pena el formato de grupos.\n\nSi tienen equipo o quieren armar uno, comenten aquí y coordinamos.",
                'comentarios' => [
                    [
                        'texto' => 'Nosotros tenemos equipo mixto en La Romana. ¿Hay límite de nivel o entra cualquiera?',
                        'likes' => 7,
                        'respuestas' => [
                            ['texto' => 'Entra cualquiera. Justamente separamos en dos categorías para que nadie se sienta fuera de lugar.', 'likes' => 8, 'delAutor' => true],
                        ],
                    ],
                    [
                        'texto' => 'Yo voy solo pero me sumo a cualquier equipo que necesite un cuarto. Juego de armador.',
                        'likes' => 9,
                        'respuestas' => [
                            ['texto' => 'A nosotros nos falta uno justamente. Te escribo por mensaje.', 'likes' => 6],
                        ],
                    ],
                    [
                        'texto' => 'Sugerencia: pongan el horario temprano o después de las 4. A mediodía la arena quema y es insoportable.',
                        'likes' => 14,
                        'respuestas' => [
                            ['texto' => 'Buen punto, lo estamos pensando así. Probablemente de 7 a 11 y luego de 4 a 7.', 'likes' => 5, 'delAutor' => true],
                        ],
                    ],
                ],
            ],
            [
                'titulo' => 'Qué comer antes y después de entrenar sin complicarse',
                'categoria' => 'bienestar',
                'likes' => 18,
                'contenido' => "Sin suplementos caros ni nada raro, comida normal de aquí.\n\nAntes (1 a 2 horas): algo con carbohidrato y poca grasa. Avena con guineo, pan con mermelada, o arroz con pollo si es más temprano.\n\nDespués: proteína y carbohidrato en la comida siguiente. No hace falta correr al vestuario con el batido, esa ventana de 30 minutos está muy exagerada.\n\nSi entrenas en ayunas y te sientes bien, tampoco pasa nada.\n\nLo que sí importa de verdad: el total del día y ser constante. El resto son detalles.",
                'comentarios' => [
                    [
                        'texto' => 'Lo de la ventana anabólica me lo creí años. Me estresaba muchísimo por llegar a tiempo con el batido.',
                        'likes' => 16,
                        'respuestas' => [
                            ['texto' => 'Y de ahí vendían muchísimo suplemento. Si comes bien en el día, da igual media hora arriba o abajo.', 'likes' => 12],
                        ],
                    ],
                    [
                        'texto' => '¿El guineo verde también sirve o tiene que ser maduro?',
                        'likes' => 4,
                        'respuestas' => [
                            ['texto' => 'Maduro se digiere más rápido, mejor si vas justo de tiempo. El verde tiene más almidón resistente y cae más pesado antes de entrenar.', 'likes' => 10, 'delAutor' => true],
                        ],
                    ],
                    [
                        'texto' => 'Yo entreno en ayunas a las 5 de la mañana y me va bien, pero solo para cardio suave. Para pesas no aguanto.',
                        'likes' => 8,
                    ],
                ],
            ],
            [
                'titulo' => 'Gimnasios con buen equipamiento de fuerza en la capital',
                'categoria' => 'lugares',
                'likes' => 16,
                'contenido' => "Busco gimnasio donde de verdad se pueda entrenar fuerza: rack de sentadillas, barras olímpicas suficientes y plataforma para peso muerto.\n\nHe visitado cuatro y en tres solo hay máquinas y un rack ocupado siempre.\n\n¿Alguien conoce sitios enfocados a fuerza más que a estética? Zona de Bella Vista o alrededores preferiblemente.",
                'comentarios' => [
                    [
                        'texto' => 'Los gimnasios de barrio muchas veces tienen mejor equipo de fuerza que las cadenas grandes. Menos bonito pero más funcional.',
                        'likes' => 13,
                        'respuestas' => [
                            ['texto' => 'Coincido totalmente. El mío tiene tres racks y nunca hay que esperar.', 'likes' => 9],
                            ['texto' => '¿Cuál es? Si no te molesta decirlo.', 'likes' => 2],
                        ],
                    ],
                    [
                        'texto' => 'Pregunta siempre si permiten soltar el peso muerto. Hay sitios con barras olímpicas donde igual te llaman la atención.',
                        'likes' => 11,
                        'respuestas' => [
                            ['texto' => 'Esto me pasó. Tenían plataforma y aun así no dejaban. Sin sentido.', 'likes' => 7],
                        ],
                    ],
                ],
            ],
        ];
    }
}
