<?php

namespace Database\Seeders;

use App\Models\Configuration;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Repuebla la comunidad: usuarios, entrenadores aprobados y el catalogo de
 * configuraciones.
 *
 * Es ADITIVO e IDEMPOTENTE: usa firstOrCreate por correo, asi que se puede
 * volver a ejecutar sin duplicar nada y sin borrar lo que ya exista.
 *
 * Contraseña de todas las cuentas de ejemplo: "SportFamily2026"
 */
class ComunidadSeeder extends Seeder
{
    public const PASSWORD = 'SportFamily2026';

    /** Las 7 categorias del enum sport_category de la tabla trainer. */
    public const CATEGORIAS = [
        'Fútbol', 'Baloncesto', 'Tenis', 'Natación', 'Ciclismo', 'Atletismo', 'Artes Marciales',
    ];

    private const CIUDADES = [
        'Santo Domingo', 'Santiago de los Caballeros', 'La Vega', 'San Pedro de Macorís',
        'Puerto Plata', 'San Cristóbal', 'La Romana', 'Higüey', 'Moca', 'Bonao',
        'Barahona', 'Azua', 'San Francisco de Macorís', 'Baní', 'Monte Cristi',
    ];

    public function run(): void
    {
        $this->configuraciones();
        $this->admin();
        $this->miembros();
        $this->entrenadores();
    }

    private function configuraciones(): void
    {
        foreach ([
            'Perfil público',
            'Mostrar estadísticas',
            'Permitir mensajes directos',
            'Notificaciones por correo',
            'Recordatorios de entrenamiento',
        ] as $nombre) {
            Configuration::firstOrCreate(['configuration' => $nombre]);
        }

        $this->command->info('  Configuraciones: ' . Configuration::count());
    }

    private function admin(): void
    {
        $this->crearUsuario([
            'name' => 'David Mansilla',
            'email' => 'admin@sportfamilyrd.com',
            'user_type' => 'admin',
            'phone' => '809-555-0100',
            'location' => 'Santo Domingo',
            'birthdate' => '1998-04-12',
            'bio' => 'Administrador de SportFamilyRD. Construyendo la comunidad deportiva dominicana.',
        ]);
    }

    /** 20 miembros con perfil verosimil. */
    private function miembros(): void
    {
        $gente = [
            ['Carlos Manuel Peña',      'Santo Domingo', 'Fútbol',          'Juego de mediocampista en la liga de mi barrio desde los 12. Domingos sagrados en la cancha.'],
            ['Yamilex Rodríguez',       'Santiago de los Caballeros', 'Natación', 'Nadadora máster. Entreno 4 veces por semana en el complejo olímpico.'],
            ['José Alberto Fernández',  'San Pedro de Macorís', 'Baloncesto', 'Fanático del baloncesto desde chiquito. Armo pickup los sábados en la cancha del sector.'],
            ['Massiel Guzmán',          'La Vega', 'Atletismo',              'Corredora de 10K. Mi meta de este año es bajar de 50 minutos.'],
            ['Rafael Antonio Díaz',     'Puerto Plata', 'Ciclismo',          'Ruta y montaña. Salgo todos los domingos temprano por la costa.'],
            ['Nicole Batista',          'Santo Domingo', 'Tenis',            'Empecé en el tenis hace dos años y ya no puedo parar. Busco compañeros de dobles.'],
            ['Luis Emilio Santana',     'La Romana', 'Artes Marciales',      'Cinturón azul de jiu-jitsu. Entreno en las mañanas antes del trabajo.'],
            ['Wendy Paulino',           'Higüey', 'Fútbol',                  'Portera. Juego en un equipo femenino local y organizo torneos.'],
            ['Pedro Julio Ramírez',     'Moca', 'Baloncesto',                'Ex jugador universitario. Ahora entreno a los muchachos del barrio los fines de semana.'],
            ['Andrea Michelle Cruz',    'Santo Domingo', 'Natación',         'Nado para mantenerme en forma. Me encanta el ambiente de la comunidad aquí.'],
            ['Manuel de Jesús Reyes',   'Bonao', 'Ciclismo',                 'Mecánico de bicicletas y ciclista. Siempre dispuesto a dar una mano con un ajuste.'],
            ['Katherine Jiménez',       'San Cristóbal', 'Atletismo',        'Maratonista amateur. Ya llevo tres maratones completos.'],
            ['Francisco Alberto Núñez', 'Barahona', 'Fútbol',                'Árbitro certificado y jugador ocasional. El fútbol es mi vida.'],
            ['Génesis Marte',           'Santiago de los Caballeros', 'Tenis', 'Juego tenis desde el colegio. Busco canchas nuevas donde practicar.'],
            ['Ramón Antonio Ureña',     'San Francisco de Macorís', 'Baloncesto', 'Sigo la liga nacional religiosamente. Comento todos los partidos.'],
            ['Claudia Esther Pimentel', 'Baní', 'Artes Marciales',           'Practico taekwondo hace 6 años. Cinturón negro primer dan.'],
            ['Héctor Luis Valdez',      'Azua', 'Ciclismo',                  'Ciclismo de montaña los fines de semana. Conozco todas las rutas de la zona.'],
            ['Paola Andrea Herrera',    'Santo Domingo', 'Natación',         'Entrenadora de natación infantil en formación. Me apasiona enseñar.'],
            ['Juan Carlos Mejía',       'Monte Cristi', 'Fútbol',            'Organizo la liga municipal. Siempre buscando más equipos.'],
            ['Rosanna Beltré',          'La Vega', 'Atletismo',              'Empecé a correr en pandemia y no he parado. Comunidad > competencia.'],
        ];

        foreach ($gente as $i => [$nombre, $ciudad, $categoria, $bio]) {
            $this->crearUsuario([
                'name' => $nombre,
                'email' => $this->correo($nombre),
                'user_type' => 'user',
                'phone' => sprintf('809-%03d-%04d', 200 + $i, 1000 + ($i * 137) % 9000),
                'location' => $ciudad,
                'birthdate' => sprintf('19%02d-%02d-%02d', 80 + ($i % 20), 1 + ($i % 12), 1 + ($i % 28)),
                'bio' => $bio,
                'category' => $categoria,
            ]);
        }

        $this->command->info('  Miembros creados. Total usuarios: ' . User::count());
    }

    /** 8 entrenadores aprobados por cada una de las 7 categorias = 56. */
    private function entrenadores(): void
    {
        $nombres = [
            'Fútbol' => ['Ramón Guzmán', 'Elvis Martínez', 'Yohanna Peralta', 'Miguel Ángel Rosario', 'Darío Encarnación', 'Luisa Fernanda Ortiz', 'Kelvin Abreu', 'Sandra Polanco'],
            'Baloncesto' => ['Eddy Sánchez', 'Marisol Tavárez', 'Juan Pablo Contreras', 'Ingrid Vásquez', 'Alexis Morillo', 'Yeimy Castillo', 'Roberto Alcántara', 'Dahiana Espinal'],
            'Tenis' => ['Héctor Bonilla', 'Carla Santos', 'Adolfo Pichardo', 'Vanessa Liriano', 'Emilio Terrero', 'Rosa María Cabrera', 'Freddy Almonte', 'Laura Beltrán'],
            'Natación' => ['Ismael Quezada', 'Patricia Grullón', 'Ronny Beltré', 'Yaneris Suero', 'Amaury Nin', 'Belkis Rosario', 'Gustavo Mena', 'Cinthia Padilla'],
            'Ciclismo' => ['Wilson Objío', 'Marlenny Duarte', 'Rafael Sosa', 'Antonia Vargas', 'Julio César Brito', 'Ivelisse Frías', 'Norberto Lugo', 'Esther Carrasco'],
            'Atletismo' => ['Domingo Arias', 'Xiomara Bautista', 'Félix Adames', 'Deyanira Lantigua', 'Osvaldo Pimentel', 'Milagros Tejada', 'Bienvenido Castro', 'Yaquelin Soto'],
            'Artes Marciales' => ['Santiago Ferreras', 'Ruth Esther Gil', 'Joaquín Montás', 'Digna Aquino', 'Radhamés Ovalles', 'Solange Batista', 'Ernesto Villar', 'Maribel Then'],
        ];

        $niveles = ['basica', 'intermedia', 'avanzada', 'nacional', 'internacional'];

        $especialidades = [
            'Fútbol' => ['Técnica individual y control de balón', 'Preparación física para fútbol', 'Entrenamiento de porteros', 'Táctica ofensiva', 'Fútbol base infantil'],
            'Baloncesto' => ['Mecánica de tiro', 'Manejo de balón y penetración', 'Defensa individual', 'Preparación física específica', 'Baloncesto formativo'],
            'Tenis' => ['Técnica de derecha y revés', 'Servicio y devolución', 'Juego de red', 'Estrategia de partido', 'Iniciación al tenis'],
            'Natación' => ['Técnica de crol', 'Estilo mariposa', 'Natación para principiantes', 'Resistencia en agua abierta', 'Natación infantil'],
            'Ciclismo' => ['Ciclismo de ruta', 'Ciclismo de montaña', 'Entrenamiento por potencia', 'Mecánica básica', 'Preparación para fondos'],
            'Atletismo' => ['Velocidad y salidas', 'Fondo y medio fondo', 'Técnica de carrera', 'Preparación para maratón', 'Salto y lanzamiento'],
            'Artes Marciales' => ['Jiu-jitsu brasileño', 'Taekwondo', 'Boxeo técnico', 'Defensa personal', 'Acondicionamiento de combate'],
        ];

        $creados = 0;

        foreach (self::CATEGORIAS as $ci => $categoria) {
            foreach ($nombres[$categoria] as $i => $nombre) {
                $anios = 3 + (($ci * 8 + $i) % 18);
                $ciudad = self::CIUDADES[($ci * 8 + $i) % count(self::CIUDADES)];
                $nivel = $niveles[($ci + $i) % count($niveles)];

                $user = $this->crearUsuario([
                    'name' => $nombre,
                    'email' => $this->correo($nombre, 'entrenador'),
                    'user_type' => 'entrenador',
                    'phone' => sprintf('829-%03d-%04d', 300 + $ci * 10 + $i, 2000 + (($ci * 8 + $i) * 211) % 8000),
                    'location' => $ciudad,
                    'birthdate' => sprintf('19%02d-%02d-%02d', 70 + (($ci * 8 + $i) % 25), 1 + ($i % 12), 2 + ($i % 27)),
                    'bio' => "Entrenador de {$categoria} con {$anios} años de experiencia en {$ciudad}.",
                    'category' => $categoria,
                ]);

                $trainer = Trainer::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'name' => $nombre,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'city_country' => $ciudad . ', República Dominicana',
                        'sport_category' => $categoria,
                        'experience' => "{$anios} años",
                        'level_of_certification' => $nivel,
                        'certificates_linked' => null,
                        'description' => $this->descripcionEntrenador($nombre, $categoria, $anios, $ciudad),
                        'schedule' => json_encode($this->horario($i), JSON_UNESCAPED_UNICODE),
                        'cost' => [600, 800, 1000, 1200, 1500, 2000, 2500, 3000][$i],
                        'status' => 'approved',
                    ]
                );

                if ($trainer->wasRecentlyCreated) {
                    $creados++;

                    $trainer->specialties()->createMany(
                        collect($especialidades[$categoria])
                            ->shuffle()
                            ->take(2 + ($i % 3))
                            ->map(fn($d) => ['description' => $d])
                            ->all()
                    );

                    $trainer->achievements()->createMany($this->logros($categoria, $i, $anios));
                }
            }
        }

        $this->command->info("  Entrenadores aprobados: {$creados} nuevos, " . Trainer::count() . ' en total');
    }

    // ------------------------------------------------------------------ utilidades

    private function crearUsuario(array $datos): User
    {
        $user = User::firstOrCreate(
            ['email' => $datos['email']],
            array_merge([
                'password' => Hash::make(self::PASSWORD),
                'email_verified_at' => now(),
            ], $datos)
        );

        // Fechas de alta repartidas en los ultimos meses para que la comunidad
        // no parezca creada toda el mismo segundo.
        if ($user->wasRecentlyCreated) {
            $alta = now()->subDays(random_int(5, 240))->subHours(random_int(0, 23));
            DB::table('users')->where('id', $user->id)
                ->update(['created_at' => $alta, 'updated_at' => $alta]);
        }

        return $user;
    }

    private function correo(string $nombre, string $sufijo = ''): string
    {
        $partes = preg_split('/\s+/', $this->sinTildes(mb_strtolower($nombre)));
        $base = $partes[0] . '.' . end($partes);
        $base = preg_replace('/[^a-z.]/', '', $base);

        return $base . ($sufijo ? '.' . $sufijo : '') . '@sportfamilyrd.com';
    }

    private function sinTildes(string $t): string
    {
        return strtr($t, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u']);
    }

    private function descripcionEntrenador(string $nombre, string $categoria, int $anios, string $ciudad): string
    {
        $intro = explode(' ', $nombre)[0];

        return "Soy {$intro}, entrenador de {$categoria} con {$anios} años formando atletas en {$ciudad}. "
            . 'Trabajo tanto con principiantes que empiezan desde cero como con deportistas que buscan competir. '
            . 'Mi enfoque es la técnica primero: sin buena base no hay progreso sostenible. '
            . 'Planifico cada sesión según el objetivo y la condición de la persona, y doy seguimiento semanal.';
    }

    /**
     * Horario semanal del entrenador.
     *
     * EL FORMATO IMPORTA y no es libre: lo consume parseSchedule() en
     * EntrenadoresView.vue, que espera exactamente esto:
     *
     *   { "Lunes": { "available": true, "hours": { "desde": "06:00", "hasta": "09:00" } } }
     *
     * Dos detalles que hay que respetar al pie de la letra:
     *
     *   - La clave del dia va CAPITALIZADA Y CON TILDE ("Miércoles", "Sábado").
     *     La vista construye su tabla con esos nombres exactos y busca por
     *     ellos; con "miercoles" en minuscula no encuentra nada.
     *   - 'hours' es un OBJETO con 'desde' y 'hasta', no un array de dos
     *     posiciones. La vista lee hours.desde / hours.hasta.
     *
     * Con el formato anterior (claves en minuscula y hours como array) la ficha
     * del entrenador mostraba los siete dias como "No Disponible", porque
     * parseSchedule caia siempre en su valor por defecto de cadenas vacias.
     *
     * Los dias NO disponibles se omiten: parseSchedule ya rellena el resto con
     * vacios, que es como la vista marca "No Disponible".
     */
    private function horario(int $i): array
    {
        // Tres turnos tipicos y tres combinaciones de dias, para que no todos
        // los entrenadores tengan la misma disponibilidad.
        $turnos = [
            ['desde' => '06:00', 'hasta' => '09:00'],   // manana temprano
            ['desde' => '16:00', 'hasta' => '20:00'],   // tarde
            ['desde' => '08:00', 'hasta' => '12:00'],   // manana
        ];

        $combinaciones = [
            ['Lunes', 'Miércoles', 'Viernes'],
            ['Martes', 'Jueves', 'Sábado'],
            ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'],
            ['Lunes', 'Miércoles', 'Viernes', 'Sábado'],
            ['Martes', 'Miércoles', 'Jueves', 'Viernes'],
        ];

        $turno = $turnos[$i % count($turnos)];
        $dias = $combinaciones[$i % count($combinaciones)];

        $schedule = [];
        foreach ($dias as $dia) {
            $schedule[$dia] = ['available' => true, 'hours' => $turno];
        }

        return $schedule;
    }

    private function logros(string $categoria, int $i, int $anios): array
    {
        $plantillas = [
            ['Certificación nacional de entrenamiento', "Acreditación otorgada por la federación de {$categoria} de República Dominicana."],
            ['Campeonato nacional juvenil', "Equipo campeón en la categoría juvenil de {$categoria}."],
            ['Formación de atletas de selección', 'Cuatro de mis alumnos han sido convocados a selecciones provinciales.'],
            ['Curso internacional de metodología', 'Formación continua en planificación deportiva y prevención de lesiones.'],
        ];

        return collect($plantillas)
            ->shuffle()
            ->take(1 + ($i % 3))
            ->map(fn($l) => [
                'title' => $l[0],
                'description' => $l[1],
                'achievement_date' => now()->subYears(random_int(1, max(1, $anios - 1)))->format('Y-m-d'),
            ])
            ->all();
    }
}
