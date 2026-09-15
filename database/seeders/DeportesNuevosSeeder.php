<?php

namespace Database\Seeders;

use App\Models\Sport;
use Illuminate\Database\Seeder;

/**
 * Amplía el directorio con 20 deportes que faltaban y asigna CATEGORÍA a todos.
 *
 * La categoría es lo que alimenta los botones de filtro del Directorio. Se
 * eligieron siete, pensadas por lo que la persona quiere hacer y no por como
 * los clasifica una federacion, que es como se busca de verdad un deporte:
 *
 *   Pelota · Contacto · Agua · Resistencia · Precisión · Aventura · Bienestar
 *
 * Los lugares y precios son reales y de República Dominicana. Donde el precio
 * varía o no es publico se dice explicitamente en vez de inventar una cifra.
 *
 * Aditivo e idempotente: updateOrCreate por nombre, igual que SportSeeder.
 */
class DeportesNuevosSeeder extends Seeder
{
    /** Las siete categorías del filtro, en el orden en que se muestran. */
    public const CATEGORIAS = [
        'Pelota',
        'Contacto',
        'Agua',
        'Resistencia',
        'Precisión',
        'Aventura',
        'Bienestar',
    ];

    /** Categoría de cada uno de los 25 deportes que ya existían. */
    private const CATEGORIA_EXISTENTES = [
        'Béisbol' => 'Pelota',
        'Sóftbol' => 'Pelota',
        'Fútbol' => 'Pelota',
        'Baloncesto' => 'Pelota',
        'Voleibol' => 'Pelota',
        'Tenis' => 'Pelota',
        'Pádel' => 'Pelota',
        'Golf' => 'Pelota',
        'Ultimate Frisbee' => 'Pelota',
        'Boxeo' => 'Contacto',
        'Judo' => 'Contacto',
        'Karate' => 'Contacto',
        'Surf' => 'Agua',
        'Windsurf y Kitesurf' => 'Agua',
        'Buceo' => 'Agua',
        'Pesca Deportiva' => 'Agua',
        'Yaque Rafting' => 'Agua',
        'Atletismo' => 'Resistencia',
        'Ciclismo' => 'Resistencia',
        'Dominó' => 'Precisión',
        'Tiro Deportivo' => 'Precisión',
        'Equitación' => 'Aventura',
        'Carreras de Caballos' => 'Aventura',
        'Parapente' => 'Aventura',
        'Yoga en Playa' => 'Bienestar',
    ];

    public function run(): void
    {
        $this->categorizarExistentes();
        $this->agregarNuevos();
    }

    private function categorizarExistentes(): void
    {
        $actualizados = 0;

        foreach (self::CATEGORIA_EXISTENTES as $nombre => $categoria) {
            $actualizados += Sport::where('name', $nombre)->update(['category' => $categoria]);
        }

        $this->command->info("  Categorías asignadas a deportes existentes: {$actualizados}");

        $sinCategoria = Sport::whereNull('category')->pluck('name');
        if ($sinCategoria->isNotEmpty()) {
            $this->command->warn('  Sin categoría: ' . $sinCategoria->implode(', '));
        }
    }

    private function agregarNuevos(): void
    {
        // sort_order arranca en 100 para que los nuevos queden despues de los
        // 25 originales sin tener que renumerar nada.
        $orden = 100;
        $creados = 0;

        foreach ($this->deportes() as $datos) {
            $datos['sort_order'] = $orden++;

            $sport = Sport::updateOrCreate(['name' => $datos['name']], $datos);

            if ($sport->wasRecentlyCreated) {
                $creados++;
            }
        }

        $this->command->info("  Deportes nuevos: {$creados}  ·  total en el directorio: " . Sport::count());
    }

    private function deportes(): array
    {
        $img = 'imagenes/DirectorioDeDeportes/';

        return [
            [
                'name' => 'Natación',
                'region' => 'Todo el país',
                'type' => 'Individual',
                'category' => 'Agua',
                'popularity' => 'Alta',
                'image' => $img . 'Natacion.jpg',
                'short_description' => 'Base de la formación deportiva y puerta a todos los deportes acuáticos',
                'description' => 'La natación es uno de los deportes con estructura federada más sólida del país. El Centro Olímpico Juan Pablo Duarte concentra la formación de alto rendimiento, y en las últimas décadas la República Dominicana ha llevado nadadores a Juegos Panamericanos, Centroamericanos y del Caribe, y a Juegos Olímpicos. Fuera de la competencia es también el deporte que más se recomienda por su bajo impacto articular: se practica a cualquier edad y es la base para surf, buceo, vela y waterpolo. Muchos clubes privados y hoteles ofrecen escuelas para niños desde los cuatro años.',
                'requirements' => [
                    'Traje de baño deportivo (poliéster resistente al cloro)',
                    'Gafas de natación con buen sellado',
                    'Gorro de silicona o látex',
                    'Toalla y chancletas',
                    'Tabla y pull buoy (los presta casi toda escuela)',
                ],
                'places' => [
                    ['name' => 'Piscina Olímpica – Centro Olímpico Juan Pablo Duarte', 'location' => 'Santo Domingo', 'cost' => 'Entrada pública económica; mensualidad de escuela desde RD$1 500'],
                    ['name' => 'Complejo Deportivo Cibao', 'location' => 'Santiago de los Caballeros', 'cost' => 'Mensualidad aproximada RD$1 200 a RD$2 500 según horario'],
                    ['name' => 'Clubes privados y hoteles con piscina de 25 m', 'location' => 'Santo Domingo, Punta Cana, Puerto Plata', 'cost' => 'Membresía o pase de día (varía mucho por establecimiento)'],
                    ['name' => 'Piscinas municipales', 'location' => 'La Vega, San Cristóbal, San Pedro de Macorís', 'cost' => 'Gratis o cuota simbólica en programas municipales'],
                ],
            ],
            [
                'name' => 'Taekwondo',
                'region' => 'Todo el país',
                'type' => 'Individual',
                'category' => 'Contacto',
                'popularity' => 'Alta',
                'image' => $img . 'Taekwondo.jpg',
                'short_description' => 'El arte marcial con más medallas olímpicas para el país',
                'description' => 'El taekwondo le ha dado a República Dominicana algunas de sus páginas más recordadas del deporte olímpico, incluida la medalla de plata de Gabriel Mercedes en Pekín 2008 y la de bronce de Luisito Pie en Río 2016. Esa proyección internacional se apoya en una red amplia de dojangs en barrios y clubes de todo el país, donde la práctica empieza con niños de cinco o seis años. Se trabaja técnica de patadas, desplazamientos, formas (poomsae) y combate con protecciones, y el sistema de cinturones da una progresión clara que motiva mucho a los más jóvenes.',
                'requirements' => [
                    'Dobok (uniforme) y cinturón del grado correspondiente',
                    'Peto, casco y espinilleras para combate',
                    'Protector bucal y coquilla',
                    'Guantes y protectores de empeine',
                    'Inscripción en un dojang afiliado a la federación',
                ],
                'places' => [
                    ['name' => 'Federación Dominicana de Taekwondo – Centro Olímpico', 'location' => 'Santo Domingo', 'cost' => 'Programas federados; mensualidad desde RD$1 000'],
                    ['name' => 'Dojangs de clubes y politécnicos', 'location' => 'Todo el país', 'cost' => 'RD$800 a RD$2 500 mensuales según el club'],
                    ['name' => 'Programas deportivos municipales', 'location' => 'Santiago, La Romana, Higüey', 'cost' => 'Gratis o cuota mínima para la comunidad'],
                ],
            ],
            [
                'name' => 'Ajedrez',
                'region' => 'Todo el país',
                'type' => 'Individual',
                'category' => 'Precisión',
                'popularity' => 'Media-Alta',
                'image' => $img . 'Ajedrez.jpg',
                'short_description' => 'Deporte mental con fuerte presencia escolar y club activo',
                'description' => 'El ajedrez está reconocido como deporte y tiene en el país una federación activa, torneos nacionales por categorías y una presencia notable en los colegios. República Dominicana ha producido maestros internacionales y participa regularmente en Olimpiadas de Ajedrez. Es de los pocos deportes que no exige instalación especial ni condición física: basta un tablero y un rival, lo que explica que se juegue lo mismo en clubes formales que en parques y centros comunitarios. Los programas escolares lo usan como herramienta para trabajar concentración y pensamiento estructurado.',
                'requirements' => [
                    'Tablero reglamentario y juego de piezas Staunton',
                    'Reloj de ajedrez (obligatorio en torneo)',
                    'Planilla para anotar la partida',
                    'Afiliación a un club para competir oficialmente',
                ],
                'places' => [
                    ['name' => 'Federación Dominicana de Ajedrez', 'location' => 'Santo Domingo', 'cost' => 'Inscripción a torneos desde RD$300'],
                    ['name' => 'Clubes de ajedrez y casas de cultura', 'location' => 'Santiago, La Vega, San Francisco de Macorís', 'cost' => 'Cuota de club baja; muchas sesiones abiertas gratis'],
                    ['name' => 'Parque Colón y plazas públicas', 'location' => 'Santo Domingo (Zona Colonial)', 'cost' => 'Gratis (partidas informales)'],
                ],
            ],
            [
                'name' => 'Boliche',
                'region' => 'Zonas urbanas',
                'type' => 'Individual/Grupo',
                'category' => 'Precisión',
                'popularity' => 'Media',
                'image' => $img . 'Boliche.jpg',
                'short_description' => 'Deporte de precisión con ligas organizadas en las principales ciudades',
                'description' => 'El boliche combina lo social con lo competitivo: la mayoría lo conoce como salida de fin de semana, pero existen ligas organizadas con handicap y torneos nacionales federados. La técnica es más exigente de lo que parece —el efecto que se le da a la bola determina el ángulo de entrada a los pinos— y la progresión se nota rápido con práctica constante. Las boleras del país suelen tener bolas y zapatos de alquiler incluidos, así que se puede empezar sin comprar nada.',
                'requirements' => [
                    'Zapatos de boliche (se alquilan en la bolera)',
                    'Bola de peso adecuado (6 a 16 libras)',
                    'Muñequera de soporte (opcional, para jugar seguido)',
                    'Ropa cómoda que permita el deslizamiento',
                ],
                'places' => [
                    ['name' => 'Boleras de centros comerciales', 'location' => 'Santo Domingo, Santiago', 'cost' => 'RD$250 a RD$500 por línea; alquiler de zapatos aparte'],
                    ['name' => 'Ligas federadas de boliche', 'location' => 'Santo Domingo', 'cost' => 'Inscripción por temporada, varía según liga'],
                ],
            ],
            [
                'name' => 'Billar',
                'region' => 'Todo el país',
                'type' => 'Individual/Parejas',
                'category' => 'Precisión',
                'popularity' => 'Media-Alta',
                'image' => $img . 'Billar.jpg',
                'short_description' => 'Precisión y cálculo de ángulos, con fuerte arraigo social',
                'description' => 'El billar es parte del paisaje social dominicano: se juega en clubes, colmadones y salones de todo el país. Más allá del ambiente informal existe una vertiente federada con modalidades de pool (bola 8 y bola 9) y carambola, y torneos nacionales regulares. Es un deporte de precisión pura donde lo que decide no es la fuerza sino el control del efecto y la posición de la bola blanca para el siguiente tiro, y por eso se puede competir a buen nivel a cualquier edad.',
                'requirements' => [
                    'Taco propio (los salones prestan tacos de casa)',
                    'Tiza para la suela del taco',
                    'Guante de billar (opcional, mejora el deslizamiento)',
                    'Mesa reglamentaria de pool o carambola',
                ],
                'places' => [
                    ['name' => 'Salones de billar', 'location' => 'Todo el país', 'cost' => 'RD$150 a RD$400 la hora de mesa'],
                    ['name' => 'Clubes con mesas reglamentarias', 'location' => 'Santo Domingo, Santiago', 'cost' => 'Membresía o tarifa por hora'],
                ],
            ],
            [
                'name' => 'Vela',
                'region' => 'Costa Caribe y Atlántico',
                'type' => 'Individual/Grupo',
                'category' => 'Agua',
                'popularity' => 'Media',
                'image' => $img . 'Vela.jpg',
                'short_description' => 'Navegación deportiva con regatas en la costa sur y este',
                'description' => 'La vela aprovecha una de las mejores condiciones del Caribe: viento constante y aguas navegables casi todo el año. Se practica en clubes náuticos y marinas, con clases Optimist y Laser como puerta de entrada para los más jóvenes, y regatas que reúnen a participantes locales e internacionales. Es un deporte que enseña lectura del viento, navegación y trabajo en equipo, y que tiene además una vertiente de crucero para quien prefiere navegar sin competir.',
                'requirements' => [
                    'Chaleco salvavidas homologado',
                    'Ropa de secado rápido y protección solar',
                    'Guantes de vela y calzado antideslizante',
                    'Curso básico de navegación antes de salir solo',
                    'Embarcación propia o de club (Optimist, Laser, catamarán)',
                ],
                'places' => [
                    ['name' => 'Club Náutico de Santo Domingo', 'location' => 'Boca Chica', 'cost' => 'Membresía de club; cursos desde RD$5 000'],
                    ['name' => 'Marina Casa de Campo', 'location' => 'La Romana', 'cost' => 'Acceso privado y alquiler de embarcación'],
                    ['name' => 'Marina Cap Cana', 'location' => 'Punta Cana', 'cost' => 'Escuela de vela y alquiler (tarifas turísticas)'],
                ],
            ],
            [
                'name' => 'Remo y Kayak',
                'region' => 'Ríos, lagos y costa',
                'type' => 'Individual/Grupo',
                'category' => 'Agua',
                'popularity' => 'Creciente',
                'image' => $img . 'RemoYKayak.jpg',
                'short_description' => 'Remo en aguas tranquilas por ríos, lagunas y bahías',
                'description' => 'El remo y el kayak crecen de la mano del turismo de naturaleza y de los programas federados de canotaje. Se practican tanto en modalidad deportiva —con botes de competición en aguas tranquilas— como recreativa, recorriendo ríos, lagunas y manglares. La zona de Samaná y los ríos del Cibao concentran buena parte de la actividad recreativa, mientras que la formación competitiva se organiza desde la federación. Es un ejercicio completo de tren superior y core, y de bajo impacto para las articulaciones.',
                'requirements' => [
                    'Kayak o bote de remo (se alquila en casi todos los centros)',
                    'Remo de la longitud adecuada a tu estatura',
                    'Chaleco salvavidas (obligatorio)',
                    'Ropa de secado rápido y protección solar',
                    'Bolsa estanca para pertenencias',
                ],
                'places' => [
                    ['name' => 'Los Haitises y bahía de Samaná', 'location' => 'Samaná', 'cost' => 'Excursiones guiadas desde US$45 por persona'],
                    ['name' => 'Río Chavón', 'location' => 'La Romana', 'cost' => 'Alquiler y tours desde US$30'],
                    ['name' => 'Laguna Gri-Gri', 'location' => 'Río San Juan, María Trinidad Sánchez', 'cost' => 'Paseos en bote y kayak desde RD$500'],
                    ['name' => 'Federación Dominicana de Canotaje', 'location' => 'Santo Domingo', 'cost' => 'Programas federados de formación'],
                ],
            ],
            [
                'name' => 'Gimnasia',
                'region' => 'Zonas urbanas',
                'type' => 'Individual',
                'category' => 'Bienestar',
                'popularity' => 'Media',
                'image' => $img . 'Gimnasia.jpg',
                'short_description' => 'Artística y rítmica, con base en el Centro Olímpico',
                'description' => 'La gimnasia dominicana ha dado nombres de proyección internacional, entre ellos Audrys Nin Reyes, medallista panamericano en salto. La formación se concentra en el Centro Olímpico y en academias privadas de Santo Domingo y Santiago, con dos vertientes principales: artística (suelo, salto, barras, viga) y rítmica (aro, cinta, pelota, mazas). Es un deporte de iniciación temprana que desarrolla fuerza, flexibilidad y coordinación como pocos, y que sirve de base física para casi cualquier otra disciplina.',
                'requirements' => [
                    'Maillot o ropa ajustada que no estorbe el movimiento',
                    'Protecciones de manos para barras',
                    'Magnesio para el agarre',
                    'Colchonetas y aparatos (los aporta la academia)',
                    'Acompañamiento de entrenador certificado',
                ],
                'places' => [
                    ['name' => 'Pabellón de Gimnasia – Centro Olímpico Juan Pablo Duarte', 'location' => 'Santo Domingo', 'cost' => 'Programas federados de formación'],
                    ['name' => 'Academias privadas de gimnasia', 'location' => 'Santo Domingo, Santiago', 'cost' => 'Mensualidad desde RD$2 500'],
                ],
            ],
            [
                'name' => 'Balonmano',
                'region' => 'Todo el país',
                'type' => 'Equipo',
                'category' => 'Pelota',
                'popularity' => 'Media',
                'image' => $img . 'Balonmano.jpg',
                'short_description' => 'Deporte de equipo en crecimiento por la vía escolar',
                'description' => 'El balonmano se ha expandido en el país sobre todo por la vía escolar y universitaria, aprovechando que se juega en las mismas canchas techadas que el baloncesto y el voleibol. Combina velocidad, lanzamiento potente y contacto permitido, en partidos de ritmo alto y marcadores elevados. La federación organiza campeonatos nacionales por categorías y selecciones que compiten en el circuito centroamericano y del Caribe. Es una buena entrada para quien viene de otros deportes de cancha y busca algo más físico.',
                'requirements' => [
                    'Balón de balonmano (talla según categoría)',
                    'Zapatillas de cancha con buena tracción',
                    'Rodilleras (el juego incluye caídas)',
                    'Resina para el agarre (donde esté permitida)',
                    'Cancha techada reglamentaria de 40x20 m',
                ],
                'places' => [
                    ['name' => 'Canchas techadas del Centro Olímpico', 'location' => 'Santo Domingo', 'cost' => 'Uso federado y programas de formación'],
                    ['name' => 'Polideportivos escolares y universitarios', 'location' => 'Todo el país', 'cost' => 'Gratis en programas escolares'],
                    ['name' => 'Federación Dominicana de Balonmano', 'location' => 'Santo Domingo', 'cost' => 'Inscripción de equipos por temporada'],
                ],
            ],
            [
                'name' => 'Triatlón',
                'region' => 'Costa y zonas urbanas',
                'type' => 'Individual',
                'category' => 'Resistencia',
                'popularity' => 'Creciente',
                'image' => $img . 'Triatlon.jpg',
                'short_description' => 'Natación, ciclismo y carrera en una sola prueba',
                'description' => 'El triatlón encadena natación en aguas abiertas, ciclismo de ruta y carrera a pie sin pausa entre segmentos. En República Dominicana ha crecido con fuerza en la última década gracias a eventos en Juan Dolio, Boca Chica y Punta Cana, que aprovechan mar tranquilo y carreteras costeras. Hay distancias para todos los niveles: desde el sprint (750 m de nado, 20 km de bici y 5 km de carrera) hasta el olímpico y medias distancias. La comunidad es muy activa y suele entrenar en grupo, lo que facilita mucho la entrada a quien empieza.',
                'requirements' => [
                    'Bicicleta de ruta en buen estado y casco homologado',
                    'Traje de triatlón o ropa que sirva para los tres segmentos',
                    'Gafas de natación y gorro',
                    'Zapatillas de running con cordones elásticos',
                    'Reloj con GPS (muy recomendable para el ritmo)',
                ],
                'places' => [
                    ['name' => 'Circuito de Juan Dolio', 'location' => 'San Pedro de Macorís', 'cost' => 'Inscripción a competencia desde US$50'],
                    ['name' => 'Boca Chica', 'location' => 'Santo Domingo Este', 'cost' => 'Entrenamiento libre; eventos con inscripción'],
                    ['name' => 'Federación Dominicana de Triatlón', 'location' => 'Santo Domingo', 'cost' => 'Licencia federativa anual'],
                ],
            ],
            [
                'name' => 'Escalada',
                'region' => 'Zonas montañosas y urbanas',
                'type' => 'Individual',
                'category' => 'Aventura',
                'popularity' => 'Emergente',
                'image' => $img . 'Escalada.jpg',
                'short_description' => 'Roca natural en el interior y muros artificiales en la capital',
                'description' => 'La escalada dominicana combina dos mundos: los muros artificiales que han aparecido en gimnasios de Santo Domingo y Santiago, ideales para aprender técnica con seguridad, y la roca natural del interior del país, con sectores equipados en zonas kársticas. Es un deporte que exige tanto fuerza como lectura de la vía y control mental, y que se practica siempre en pareja o grupo por razones de seguridad. La comunidad es pequeña pero muy organizada, y la entrada habitual es un curso de iniciación en muro antes de salir a roca.',
                'requirements' => [
                    'Pies de gato (calzado de escalada)',
                    'Arnés y casco',
                    'Sistema de aseguramiento y cuerda dinámica',
                    'Magnesio y bolsa portamagnesio',
                    'Curso de iniciación y compañero de cordada (nunca en solitario)',
                ],
                'places' => [
                    ['name' => 'Muros de escalada en gimnasios', 'location' => 'Santo Domingo, Santiago', 'cost' => 'Pase por día desde RD$500; mensualidad desde RD$2 500'],
                    ['name' => 'Sectores de roca natural', 'location' => 'Interior del país (zonas kársticas)', 'cost' => 'Acceso libre; se recomienda ir con club o guía'],
                ],
            ],
            [
                'name' => 'Senderismo',
                'region' => 'Cordillera Central y parques nacionales',
                'type' => 'Individual/Grupo',
                'category' => 'Aventura',
                'popularity' => 'Alta',
                'image' => $img . 'Senderismo.jpg',
                'short_description' => 'Rutas de montaña, incluido el pico más alto del Caribe',
                'description' => 'República Dominicana tiene el relieve más alto de las Antillas y eso hace del senderismo una actividad de primer nivel. La ruta estrella es el ascenso al Pico Duarte (3 087 m), que se hace en dos o tres días desde La Ciénaga de Manabao con guía obligatorio del Ministerio de Medio Ambiente. Más allá de esa travesía hay decenas de rutas accesibles en Jarabacoa, Constanza y los parques nacionales, desde caminatas de una mañana hasta recorridos exigentes de varios días. Es la forma más directa de conocer la parte del país que no se ve desde la costa.',
                'requirements' => [
                    'Botas de montaña con buen agarre y tobillo sujeto',
                    'Mochila con capacidad para agua y capa de abrigo',
                    'Ropa de abrigo (en altura la temperatura baja mucho)',
                    'Linterna frontal y botiquín básico',
                    'Guía autorizado y permiso para rutas de parque nacional',
                ],
                'places' => [
                    ['name' => 'Pico Duarte (Parque Nacional José Armando Bermúdez)', 'location' => 'La Ciénaga de Manabao, Jarabacoa', 'cost' => 'Permiso RD$100; guía y mulas desde RD$2 500 por día'],
                    ['name' => 'Salto de Jimenoa y Salto Baiguate', 'location' => 'Jarabacoa, La Vega', 'cost' => 'Entrada desde RD$150'],
                    ['name' => 'Valle Nuevo', 'location' => 'Constanza', 'cost' => 'Entrada al parque nacional desde RD$100'],
                    ['name' => 'Parque Nacional Los Haitises', 'location' => 'Samaná / Hato Mayor', 'cost' => 'Excursión guiada desde US$40'],
                ],
            ],
            [
                'name' => 'Tenis de Mesa',
                'region' => 'Todo el país',
                'type' => 'Individual/Parejas',
                'category' => 'Pelota',
                'popularity' => 'Media-Alta',
                'image' => $img . 'TenisDeMesa.jpg',
                'short_description' => 'Reflejos y control en el deporte de raqueta más rápido',
                'description' => 'El tenis de mesa tiene en el país una federación consolidada y participación regular en Juegos Centroamericanos y Panamericanos. Es de los deportes más accesibles que existen: una mesa cabe en un salón comunitario y el equipo básico cuesta poco, lo que ha permitido que se practique en escuelas, clubes y centros de barrio de todo el territorio. Técnicamente exige reflejos, lectura del efecto que trae la bola y una enorme precisión de muñeca, y se puede competir a buen nivel hasta edades avanzadas.',
                'requirements' => [
                    'Raqueta con goma homologada',
                    'Pelotas de 40 mm',
                    'Mesa reglamentaria y red',
                    'Calzado de suela antideslizante para interior',
                ],
                'places' => [
                    ['name' => 'Federación Dominicana de Tenis de Mesa', 'location' => 'Centro Olímpico, Santo Domingo', 'cost' => 'Programas federados y torneos por categoría'],
                    ['name' => 'Clubes y centros comunitarios', 'location' => 'Todo el país', 'cost' => 'Gratis o cuota baja'],
                ],
            ],
            [
                'name' => 'Bádminton',
                'region' => 'Zonas urbanas',
                'type' => 'Individual/Parejas',
                'category' => 'Pelota',
                'popularity' => 'Emergente',
                'image' => $img . 'Badminton.jpg',
                'short_description' => 'Raqueta ligera y volante, en canchas techadas',
                'description' => 'El bádminton es un deporte de raqueta rapidísimo que en República Dominicana se practica sobre todo en canchas techadas de clubes y centros escolares, ya que el volante es muy sensible al viento. La federación nacional organiza torneos por categorías y ha llevado jugadores al circuito panamericano. Exige explosividad en desplazamientos cortos, buena muñeca y mucha lectura del juego: los intercambios son de los más veloces de cualquier deporte de raqueta.',
                'requirements' => [
                    'Raqueta de bádminton (ligera, de grafito o aluminio)',
                    'Volantes de pluma o sintéticos',
                    'Zapatillas de interior con suela no marcante',
                    'Cancha techada con red reglamentaria a 1,55 m',
                ],
                'places' => [
                    ['name' => 'Federación Dominicana de Bádminton', 'location' => 'Santo Domingo', 'cost' => 'Programas federados'],
                    ['name' => 'Canchas techadas de clubes', 'location' => 'Santo Domingo, Santiago', 'cost' => 'Alquiler por hora o membresía'],
                ],
            ],
            [
                'name' => 'Levantamiento de Pesas',
                'region' => 'Todo el país',
                'type' => 'Individual',
                'category' => 'Bienestar',
                'popularity' => 'Alta',
                'image' => $img . 'Pesas.jpg',
                'short_description' => 'Halterofilia olímpica, uno de los deportes más laureados del país',
                'description' => 'La halterofilia es probablemente el deporte que más alegrías olímpicas le ha dado a República Dominicana en proporción a su tamaño, con figuras como Yudelquis Contreras y Beatriz Pirón entre las más destacadas de los últimos años. Se compite en dos movimientos, arranque y envión, y la formación federada empieza temprano con mucho énfasis en técnica antes que en carga. En paralelo, el entrenamiento con pesas se ha vuelto masivo en gimnasios de todo el país como base de la preparación física de cualquier deportista.',
                'requirements' => [
                    'Barra olímpica y discos de goma',
                    'Zapatillas de halterofilia con talón rígido',
                    'Cinturón de levantamiento',
                    'Muñequeras y magnesio',
                    'Plataforma y supervisión de entrenador certificado',
                ],
                'places' => [
                    ['name' => 'Federación Dominicana de Pesas – Centro Olímpico', 'location' => 'Santo Domingo', 'cost' => 'Programas federados de alto rendimiento'],
                    ['name' => 'Gimnasios con área de halterofilia', 'location' => 'Todo el país', 'cost' => 'Mensualidad desde RD$1 200'],
                ],
            ],
            [
                'name' => 'Lucha',
                'region' => 'Todo el país',
                'type' => 'Individual',
                'category' => 'Contacto',
                'popularity' => 'Media',
                'image' => $img . 'Lucha.jpg',
                'short_description' => 'Estilo libre y grecorromana, con fuerte base federada',
                'description' => 'La lucha olímpica se practica en el país en sus dos estilos, libre y grecorromana, con una estructura federada que nutre selecciones para Juegos Centroamericanos y Panamericanos. Es un deporte de contacto total sin golpes: se gana por proyecciones, derribos y control del oponente sobre el tapiz. Desarrolla una condición física muy completa —fuerza, equilibrio, resistencia— y tiene la ventaja de que la formación inicial se hace con muy poco equipo, lo que ha facilitado su llegada a programas comunitarios y escolares.',
                'requirements' => [
                    'Singlet (uniforme de lucha)',
                    'Zapatillas de lucha con suela flexible',
                    'Protector de orejas',
                    'Tapiz reglamentario y entrenador certificado',
                ],
                'places' => [
                    ['name' => 'Federación Dominicana de Lucha – Centro Olímpico', 'location' => 'Santo Domingo', 'cost' => 'Programas federados'],
                    ['name' => 'Clubes y programas provinciales', 'location' => 'Santiago, San Cristóbal, La Vega', 'cost' => 'Cuota baja o gratuita'],
                ],
            ],
            [
                'name' => 'Esgrima',
                'region' => 'Zonas urbanas',
                'type' => 'Individual',
                'category' => 'Contacto',
                'popularity' => 'Emergente',
                'image' => $img . 'Esgrima.jpg',
                'short_description' => 'Florete, espada y sable con equipo de protección completo',
                'description' => 'La esgrima se practica en el país en las tres armas —florete, espada y sable—, cada una con sus propias reglas de blanco válido y prioridad. Es un deporte de contacto mediado por el arma, con protección integral, donde lo decisivo es la distancia, el tiempo de reacción y la anticipación más que la fuerza. La federación nacional mantiene programas de formación en el Centro Olímpico y algunos clubes privados, y participa en el circuito centroamericano. Se suele describir como "ajedrez a velocidad de reflejo", y esa es una descripción bastante justa.',
                'requirements' => [
                    'Arma según la modalidad (florete, espada o sable)',
                    'Careta con babero protector',
                    'Chaquetilla, peto y guante de esgrima',
                    'Pantalón de esgrima y medias largas',
                    'Pista con aparato de señalización para competir',
                ],
                'places' => [
                    ['name' => 'Federación Dominicana de Esgrima – Centro Olímpico', 'location' => 'Santo Domingo', 'cost' => 'Programas federados; equipo de préstamo para iniciación'],
                    ['name' => 'Clubes de esgrima', 'location' => 'Santo Domingo, Santiago', 'cost' => 'Mensualidad desde RD$2 000'],
                ],
            ],
            [
                'name' => 'Patinaje',
                'region' => 'Zonas urbanas',
                'type' => 'Individual',
                'category' => 'Bienestar',
                'popularity' => 'Media',
                'image' => $img . 'Patinaje.jpg',
                'short_description' => 'Velocidad y recreativo, en pistas y parques urbanos',
                'description' => 'El patinaje sobre ruedas tiene en el país una vertiente competitiva de velocidad, con pista y circuitos de ruta, y otra recreativa muy extendida en parques y malecones. La federación organiza campeonatos nacionales por categorías y República Dominicana ha competido en eventos panamericanos de la disciplina. Para empezar basta un par de patines en línea y protecciones; el Mirador Sur en la capital es el punto de encuentro habitual de patinadores los fines de semana, con ambiente muy abierto a quien llega nuevo.',
                'requirements' => [
                    'Patines en línea o quad según modalidad',
                    'Casco (imprescindible)',
                    'Rodilleras, coderas y muñequeras',
                    'Llave para ajustar ruedas y rodamientos',
                ],
                'places' => [
                    ['name' => 'Parque Mirador Sur', 'location' => 'Santo Domingo', 'cost' => 'Gratis'],
                    ['name' => 'Pista de patinaje del Centro Olímpico', 'location' => 'Santo Domingo', 'cost' => 'Programas federados'],
                    ['name' => 'Malecón y parques urbanos', 'location' => 'Santiago, Puerto Plata', 'cost' => 'Gratis'],
                ],
            ],
            [
                'name' => 'Rugby',
                'region' => 'Zonas urbanas',
                'type' => 'Equipo',
                'category' => 'Contacto',
                'popularity' => 'Emergente',
                'image' => $img . 'Rugby.jpg',
                'short_description' => 'Deporte de contacto en crecimiento, sobre todo en seven',
                'description' => 'El rugby es de lo más nuevo del panorama deportivo dominicano y crece sobre todo en su modalidad de seven, que por necesitar menos jugadores encaja mejor con una comunidad todavía en formación. Hay clubes en Santo Domingo y Santiago que organizan entrenamientos abiertos y torneos amistosos, y una federación que ha empezado a competir en el circuito caribeño. Es un deporte de contacto intenso pero con una cultura muy marcada de respeto al rival y al árbitro, y los clubes locales suelen recibir muy bien a quien nunca ha jugado.',
                'requirements' => [
                    'Balón de rugby',
                    'Botas con tacos',
                    'Protector bucal (obligatorio)',
                    'Hombreras ligeras y casco blando (opcionales)',
                    'Campo de hierba y equipo de al menos siete jugadores',
                ],
                'places' => [
                    ['name' => 'Clubes de rugby de la capital', 'location' => 'Santo Domingo', 'cost' => 'Cuota de club; entrenamientos abiertos gratuitos'],
                    ['name' => 'Campos universitarios', 'location' => 'Santo Domingo, Santiago', 'cost' => 'Uso compartido con otros deportes'],
                ],
            ],
            [
                'name' => 'CrossFit',
                'region' => 'Zonas urbanas',
                'type' => 'Individual/Grupo',
                'category' => 'Bienestar',
                'popularity' => 'Alta',
                'image' => $img . 'CrossFit.jpg',
                'short_description' => 'Entrenamiento funcional de alta intensidad en grupo',
                'description' => 'El CrossFit se expandió rápido en República Dominicana y hoy hay boxes en casi todas las ciudades principales. Combina halterofilia, gimnasia y trabajo cardiovascular en sesiones cortas y de alta intensidad que cambian cada día. Su atractivo está tanto en la variedad como en el componente comunitario: se entrena en grupo y con horario fijo, lo que ayuda mucho a la constancia. Se organizan competencias locales durante todo el año, y la mayoría de los boxes ofrecen una fase de iniciación para aprender la técnica de los movimientos antes de entrar a las clases regulares.',
                'requirements' => [
                    'Zapatillas de entrenamiento con suela estable',
                    'Ropa cómoda y transpirable',
                    'Calleras para proteger las manos en barra',
                    'Cuerda de saltar propia (recomendable)',
                    'Fase de iniciación con coach certificado',
                ],
                'places' => [
                    ['name' => 'Boxes de CrossFit', 'location' => 'Santo Domingo, Santiago, Punta Cana', 'cost' => 'Mensualidad desde RD$3 000; clase suelta desde RD$500'],
                    ['name' => 'Gimnasios con área funcional', 'location' => 'Todo el país', 'cost' => 'Incluido en mensualidad general'],
                ],
            ],
        ];
    }
}
