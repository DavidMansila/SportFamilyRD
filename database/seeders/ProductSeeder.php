<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
            'name' => 'Pelota de Fútbol',
            'description' => 'Pelota de fútbol de alta calidad para partidos profesionales.',
            'stock' => rand(10, 100),
            'price' => rand(20, 100),
            'category' => 'Equipo Deportivo',
            'image' => 'https://2.bp.blogspot.com/-6LPLEk3FMpw/UqtiyF086DI/AAAAAAAADZQ/kcVCqqdS-RM/s1600/pelota-futbol-adidas-brazuca-match-replica-mundial-2014-9302-MLC20015555696_122013-F.jpg'
            ],
          
            [
            'name' => 'Raqueta de Tenis',
            'description' => 'Raqueta de tenis ligera con excelente agarre.',
            'stock' => rand(10, 100),
            'price' => rand(50, 200),
            'category' => 'Equipo Deportivo',
            'image' => 'https://images-na.ssl-images-amazon.com/images/I/71ibT1m0ryL._AC_SL1500_.jpg'
            ],
            [
            'name' => 'Balon de baloncesto',
            'description' => 'Balón de baloncesto duradero apto para uso interior y exterior.',
            'stock' => rand(10, 100),
            'price' => rand(25, 120),
            'category' => 'Equipo Deportivo',
            'image' => 'https://contents.mediadecathlon.com/p298557/k$74a7f525136224fac43390162df7b775/sq/Bal+n+de+baloncesto+SPALDING+NBA+ALL+STAR+talla+7.jpg'
            ],
            [
            'name' => 'Zapatillas para Correr',
            'description' => 'Zapatillas cómodas para carreras de larga distancia.',
            'stock' => rand(10, 100),
            'price' => rand(60, 150),
            'category' => 'Calzado',
            'image' => 'https://th.bing.com/th/id/OIP.L71w2aeGXB-bJdp4lwWpSwAAAA?rs=1&pid=ImgDetMain'
            ],
            [
            'name' => 'Esterilla de Yoga',
            'description' => 'Esterilla antideslizante para todo tipo de ejercicios.',
            'stock' => rand(10, 100),
            'price' => rand(15, 50),
            'category' => 'Fitness',
            'image' => 'https://www.lavanguardia.com/files/og_thumbnail/uploads/2022/12/11/6395e26a8fee4.jpeg'
            ],
            [
            'name' => 'Guante de Béisbol',
            'description' => 'Guante de béisbol de cuero duradero para todas las posiciones.',
            'stock' => rand(10, 100),
            'price' => rand(30, 80),
            'category' => 'Equipo Deportivo',
            'image' => 'https://studyfinds.org/wp-content/uploads/2022/12/81oM43bmOhS._AC_SL1500_.jpg'
            ],
            [
            'name' => 'Gafas de Natación',
            'description' => 'Gafas de natación antivaho con protección UV.',
            'stock' => rand(10, 100),
            'price' => rand(10, 40),
            'category' => 'Natación',
            'image' => 'https://http2.mlstatic.com/D_NQ_NP_975173-MCO42232756600_062020-F.jpg'
            ],
            [
            'name' => 'Casco de Ciclismo',
            'description' => 'Casco de ciclismo ligero y duradero.',
            'stock' => rand(10, 100),
            'price' => rand(40, 120),
            'category' => 'Ciclismo',
            'image' => 'https://th.bing.com/th/id/OIP.q5Nct7g4dSQ8Su3lnFcaZAHaFJ?rs=1&pid=ImgDetMain'
            ],
            [
            'name' => 'Set de Palos de Golf',
            'description' => 'Set completo de palos de golf para principiantes.',
            'stock' => rand(10, 100),
            'price' => rand(200, 500),
            'category' => 'Golf',
            'image' => 'https://http2.mlstatic.com/set-palos-golf-D_NQ_NP_643405-MLM20868408932_082016-F.jpg'
            ],
            [
            'name' => 'Guantes de Boxeo',
            'description' => 'Guantes de boxeo cómodos y duraderos.',
            'stock' => rand(10, 100),
            'price' => rand(30, 100),
            'category' => 'Boxeo',
            'image' => 'https://th.bing.com/th/id/OIP.iN6BnPrCWIjELYdrDDl_ggHaHa?rs=1&pid=ImgDetMain'
            ],
            [
            'name' => 'Set de Mancuernas',
            'description' => 'Set de mancuernas ajustables para entrenamiento de fuerza.',
            'stock' => rand(10, 100),
            'price' => rand(50, 150),
            'category' => 'Fitness',
            'image' => 'https://www.corpomachine.com/Files/48757/Img/21/mancuernas-de-uretano-ATX-set-profesional-alta-calidad-0001-zoom.jpg'
            ],
            [
            'name' => 'Balón de Voleibol',
            'description' => 'Balón de voleibol de tacto suave para uso interior y exterior.',
            'stock' => rand(10, 100),
            'price' => rand(20, 60),
            'category' => 'Equipo Deportivo',
            'image' => ''
            ],
            [
            'name' => 'Mochila de Senderismo',
            'description' => 'Mochila ligera para senderismo con múltiples compartimentos.',
            'stock' => rand(10, 100),
            'price' => rand(50, 120),
            'category' => 'Volleyball',
            'image' => 'https://voleigram.com/wp-content/uploads/2021/01/balon-voleibol-mikasa-mva200-1.jpg'
            ],
            [
            'name' => 'Patineta',
            'description' => 'Patineta duradera para trucos y paseos.',
            'stock' => rand(10, 100),
            'price' => rand(40, 100),
            'category' => 'Patinaje',
            'image' => 'https://th.bing.com/th/id/R.b26a6978f581c4a745b73dd4ae42b437?rik=x%2bKBxTL1hvIneg&pid=ImgRaw&r=0'
            ],
            [
            'name' => 'Pala de Tenis de Mesa',
            'description' => 'Pala de tenis de mesa de alto rendimiento.',
            'stock' => rand(10, 100),
            'price' => rand(15, 50),
            'category' => 'Equipo Deportivo',
            'image' => 'https://m.media-amazon.com/images/I/51dSI2a1lkL._SL500_.jpg'
            ],
            [
            'name' => 'Cuerda de Escalada',
            'description' => 'Cuerda de escalada fuerte y duradera para aventuras al aire libre.',
            'stock' => rand(10, 100),
            'price' => rand(60, 200),
            'category' => 'Escalada',
            'image' => 'https://images-na.ssl-images-amazon.com/images/I/81nK782LnaL._AC_SL1200_.jpg'
            ],

            [
            'name' => 'Caña de Pescar',
            'description' => 'Caña de pescar ligera para pesca en agua dulce.',
            'stock' => rand(10, 100),
            'price' => rand(30, 100),
            'category' => 'Pesca',
            'image' => 'https://http2.mlstatic.com/D_NQ_NP_2X_741940-MLB41772807586_052020-F.jpg'
            ],
            [
            'name' => 'Tabla de Snowboard',
            'description' => 'Tabla de snowboard de alta calidad para todos los niveles.',
            'stock' => rand(10, 100),
            'price' => rand(150, 400),
            'category' => 'Deportes de Invierno',
            'image' => 'https://th.bing.com/th/id/OIP.dmAW3vhjcMe-03OF6_Cr9QHaEX?rs=1&pid=ImgDetMain'
            ],

            [
            'name' => 'Kayak',
            'description' => 'Kayak duradero para lagos y ríos.',
            'stock' => rand(10, 100),
            'price' => rand(300, 800),
            'category' => 'Deportes Acuáticos',
            'image' => 'https://th.bing.com/th/id/OIP.gaCG9eCxaBs_38xjptIDiQHaE8?rs=1&pid=ImgDetMain'
            ],

            [
            'name' => 'Bate de Cricket',
            'description' => 'Bate de cricket de calidad profesional.',
            'stock' => rand(10, 100),
            'price' => rand(50, 200),
            'category' => 'Equipo Deportivo',
            'image' => 'https://nwscdn.com/media/catalog/product/f/o/fortress_cricket_catching_bat_for_cricket_training_sessions.jpg'
            ],
            [
            'name' => 'Máquina Elíptica',
            'description' => 'Máquina elíptica compacta para entrenamientos en casa.',
            'stock' => rand(10, 100),
            'price' => rand(500, 1500),
            'category' => 'Fitness',
            'image' => 'https://th.bing.com/th/id/OIP.XrMWLfO5fJb8ofl4YWs1sQHaJD?rs=1&pid=ImgDetMain '
            ],
            [
            'name' => 'Tienda de Campaña',
            'description' => 'Tienda de campaña impermeable para 4 personas.',
            'stock' => rand(10, 100),
            'price' => rand(100, 300),
            'category' => 'Aire Libre',
            'image' => 'https://th.bing.com/th/id/OIP.XVQrZzHAgcNoCsQuMUpW1QHaE6?rs=1&pid=ImgDetMain'
            ],
            [
            'name' => 'Tabla de Surf',
            'description' => 'Tabla de surf ligera para principiantes.',
            'stock' => rand(10, 100),
            'price' => rand(200, 600),
            'category' => 'Deportes Acuáticos',
            'image' => 'https://th.bing.com/th/id/OIP.PPK4RRMeZQlcrLvTTeLIgAHaHa?w=1024&h=1024&rs=1&pid=ImgDetMain'
            ],
            [
            'name' => 'Arco de Tiro con Arco',
            'description' => 'Arco de precisión para práctica de tiro.',
            'stock' => rand(10, 100),
            'price' => rand(100, 300),
            'category' => 'Tiro con Arco',
            'image' => 'https://th.bing.com/th/id/R.79c9983e2d31e6006ce85e7665841f2d?rik=QHjInTmcPmaKjw&pid=ImgRaw&r=0'
            ],
            [
            'name' => 'Patines en Línea',
            'description' => 'Patines en línea cómodos para uso recreativo.',
            'stock' => rand(10, 100),
            'price' => rand(50, 150),
            'category' => 'Patinaje',
            'image' => 'https://th.bing.com/th/id/OIP.FFB0SB9SSqRoZpa5ZhePPAHaHa?rs=1&pid=ImgDetMain'
            ],
        ];

        foreach ($products as $product) {
            DB::table('products')->insert($product);
        }
    }
}