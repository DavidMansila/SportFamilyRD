<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Catalogo de la tienda: 10 productos por cada una de las 27 categorias que
 * ofrece el filtro de TiendaView.vue (270 en total).
 *
 * Las imagenes son fotos reales de Unsplash y TODAS fueron verificadas con una
 * peticion HTTP antes de escribir este archivo: no hay ninguna URL adivinada.
 * Se sirven por https, que es lo que admite la CSP (img-src ... https:).
 *
 * Aditivo e idempotente: firstOrCreate por nombre.
 */
class TiendaSeeder extends Seeder
{
    /**
     * Las imagenes de producto viven en el bucket propio (Supabase Storage),
     * en products/{id}.jpg, y NO en URLs de terceros.
     *
     * Antes cada producto guardaba un enlace a loremflickr o a Wikimedia. Eso
     * fallaba de dos formas distintas:
     *
     *   - URLs que MUEREN: 10 de las 108 de loremflickr pasaron a devolver 500
     *     en cuestion de horas, porque la foto de Flickr detras dejo de estar
     *     disponible. La tarjeta se quedaba en blanco para siempre.
     *   - Latencia de terceros: picos de 3 segundos por imagen, suficientes
     *     para que un parpadeo de red tumbara la peticion.
     *
     * Con el bucket propio la ruta es estable, la sirve el CDN de Supabase y no
     * depende de que un tercero mantenga la foto publicada.
     *
     * Para repoblar desde cero:
     *   php artisan storage:subir-supabase --prefijo=products
     */

    /** [nombre, descripcion, precio] x10 por categoria. */
    private function catalogo(): array
    {
        return [
            'Futbol' => [
                ['Balón de fútbol FIFA Quality Pro', 'Balón talla 5 cosido a máquina, homologado para competición oficial.', 3200],
                ['Guantes de portero con protección de dedos', 'Palma de látex alemán de 4 mm y varillas removibles.', 2400],
                ['Espinilleras con tobillera', 'Carcasa rígida y forro acolchado. Talla ajustable con velcro.', 850],
                ['Conos de entrenamiento (set de 20)', 'Conos flexibles de 23 cm en cuatro colores, con base antideslizante.', 780],
                ['Escalera de agilidad 6 metros', 'Doce peldaños ajustables para trabajo de coordinación y velocidad.', 1150],
                ['Medias de fútbol acolchadas', 'Refuerzo en talón y empeine, compresión suave en la pantorrilla.', 520],
                ['Bomba de inflado con manómetro', 'Doble acción con aguja de repuesto y lectura de presión.', 690],
                ['Mini portería plegable 120x80', 'Estructura de fibra con red incluida y bolsa de transporte.', 2950],
                ['Petos de entrenamiento (juego de 10)', 'Malla transpirable, tallas adulto, cinco de cada color.', 1680],
                ['Balón de fútbol sala talla 4', 'Bote reducido, ideal para superficies duras y cancha techada.', 2100],
            ],
            'Baloncesto' => [
                ['Balón de baloncesto talla 7 cuero compuesto', 'Agarre profundo para uso interior y exterior.', 2800],
                ['Aro de baloncesto con resorte', 'Acero reforzado de 45 cm con red de nailon incluida.', 4600],
                ['Rodilleras de compresión (par)', 'Soporte lateral sin restringir el movimiento.', 1250],
                ['Mangas de brazo de compresión', 'Tejido con protección UV y costura plana.', 680],
                ['Tablero de entrenador magnético', 'Pizarra de cancha completa y media cancha con marcadores.', 1450],
                ['Balón talla 5 para categorías menores', 'Peso y diámetro reducidos para formación infantil.', 2150],
                ['Red de repuesto anti-látigo', 'Doce enganches, resistente a la intemperie.', 720],
                ['Bolsa de transporte para dos balones', 'Malla reforzada con correa ajustable al hombro.', 890],
                ['Pesas de muñeca (par de 1 kg)', 'Para trabajo de tiro y fortalecimiento de antebrazo.', 1100],
                ['Cinta antideslizante para dedos', 'Rollo de 10 m, protege articulaciones durante el juego.', 380],
            ],
            'Tenis' => [
                ['Raqueta de tenis 300 g grafito', 'Marco de grafito, patrón 16x19, encordada de fábrica.', 8900],
                ['Tubo de 3 pelotas presurizadas', 'Fieltro de alta densidad para cancha dura.', 620],
                ['Overgrip absorbente (pack de 3)', 'Tacto seco y adherente incluso con sudoración alta.', 450],
                ['Antivibrador de silicona (par)', 'Reduce la vibración del encordado en el impacto.', 280],
                ['Bolso portarraquetas para 6 unidades', 'Compartimento térmico y bolsillo para calzado.', 5400],
                ['Máquina lanzapelotas portátil', 'Frecuencia y altura regulables, batería de 4 horas.', 42000],
                ['Cuerda de repuesto 12 m multifilamento', 'Buen equilibrio entre potencia y confort de brazo.', 1350],
                ['Muñequeras de rizo (par)', 'Algodón absorbente, talla única.', 390],
                ['Raqueta junior 25 pulgadas', 'Aluminio ligero para jugadores de 9 a 11 años.', 3200],
                ['Marcador de cancha portátil', 'Cuenta sets y juegos, se cuelga de la red.', 760],
            ],
            'Ciclismo' => [
                ['Casco de ruta ventilado', 'Veintidós entradas de aire, ajuste micrométrico, certificado CE.', 5800],
                ['Luces LED delantera y trasera', 'Recargables por USB, cinco modos y hasta 12 horas.', 1650],
                ['Culotte con badana de gel', 'Tirantes elásticos y costuras planas para fondos largos.', 3400],
                ['Bomba de pie con manómetro', 'Hasta 160 psi, compatible Presta y Schrader.', 1900],
                ['Kit de parches y desmontables', 'Seis parches, pegamento y tres palancas de neumático.', 480],
                ['Portabidón de aluminio', 'Ligero, con tornillería incluida.', 520],
                ['Guantes de ciclismo medio dedo', 'Palma acolchada con gel y dorso transpirable.', 1250],
                ['Computadora de ciclismo inalámbrica', 'Velocidad, distancia, tiempo y cadencia.', 3100],
                ['Gafas deportivas con lentes intercambiables', 'Tres lentes: clara, ahumada y espejada.', 2450],
                ['Candado plegable de acero', 'Seis barras articuladas con funda antirrayado.', 2900],
            ],
            'Natacion' => [
                ['Gafas de natación antiempañante', 'Sellado de silicona suave y correa de doble banda.', 1150],
                ['Gorro de silicona ergonómico', 'Sin tirones, talla adulto unisex.', 620],
                ['Tabla de flotación EVA', 'Espuma de alta densidad para trabajo de piernas.', 980],
                ['Pull buoy de entrenamiento', 'Aísla el tren inferior para fortalecer brazada.', 890],
                ['Aletas cortas de entrenamiento', 'Silicona flexible, mejoran técnica y tobillo.', 2350],
                ['Paletas de mano ajustables', 'Aumentan la resistencia en la fase de tracción.', 1420],
                ['Bañador de competición poliéster', 'Resistente al cloro, secado rápido.', 3600],
                ['Bolsa de malla para equipo húmedo', 'Ventilada, con cordón de cierre.', 640],
                ['Tapones de oído y pinza nasal', 'Silicona hipoalergénica con estuche.', 420],
                ['Cronómetro acuático de pared', 'Resistente a salpicaduras, lectura a gran distancia.', 2800],
            ],
            'Boxeo' => [
                ['Guantes de boxeo 12 oz', 'Piel sintética con acolchado multicapa y cierre de velcro.', 3900],
                ['Vendas elásticas de 4,5 m (par)', 'Algodón con elastano y cierre de pulgar.', 480],
                ['Protector bucal termomoldeable', 'Doble densidad, con estuche ventilado.', 620],
                ['Saco de boxeo 120 cm relleno', 'Lona reforzada con cadena y giratorio incluidos.', 9800],
                ['Manoplas de entrenamiento (par)', 'Curvas, con absorción de impacto en la palma.', 3200],
                ['Cuerda de saltar con rodamientos', 'Cable de acero recubierto, longitud ajustable.', 890],
                ['Casco de protección con pómulos', 'Visión lateral amplia y barbuquejo ajustable.', 4100],
                ['Coquilla de protección abdominal', 'Cintura elástica y refuerzo en zona baja.', 2600],
                ['Guantes de saco 10 oz', 'Más ligeros, pensados para trabajo de volumen.', 2400],
                ['Pera loca con plataforma', 'Base ajustable en altura y pera de cuero.', 7600],
            ],
            'Hombres' => [
                ['Camiseta técnica manga corta', 'Tejido de secado rápido con paneles de ventilación.', 1250],
                ['Pantalón corto de entrenamiento 7"', 'Bolsillos con cierre y bóxer interior.', 1650],
                ['Sudadera con capucha felpada', 'Interior cepillado, puños elásticos.', 3200],
                ['Pantalón jogger deportivo', 'Tobillo ajustado y cintura con cordón.', 2800],
                ['Camiseta sin mangas de tirantes', 'Corte holgado para levantamiento y gimnasio.', 980],
                ['Chaqueta cortavientos plegable', 'Repele agua ligera y cabe en su propio bolsillo.', 3900],
                ['Camiseta de compresión manga larga', 'Segunda piel, soporte muscular y control de humedad.', 2100],
                ['Pantalón largo de chándal', 'Poliéster con forro suave y bolsillos laterales.', 2650],
                ['Polo deportivo transpirable', 'Cuello con botones, ideal para tenis y golf.', 1850],
                ['Set de ropa interior deportiva (3 uds)', 'Bóxer sin costuras con banda elástica de refuerzo.', 1450],
            ],
            'Mujeres' => [
                ['Top deportivo de alto impacto', 'Sujeción firme con espalda cruzada y copas removibles.', 1750],
                ['Leggings de cintura alta', 'Tejido opaco con bolsillo lateral para teléfono.', 2450],
                ['Camiseta técnica entallada', 'Secado rápido y costuras planas antirroce.', 1350],
                ['Short deportivo con bóxer interno', 'Doble capa, cintura elástica ancha.', 1550],
                ['Chaqueta de entrenamiento con cierre', 'Cuello alto y orificios para pulgares.', 3400],
                ['Top deportivo de impacto medio', 'Ideal para yoga, pilates y entrenamiento de fuerza.', 1450],
                ['Conjunto de yoga (top y legging)', 'Tejido elástico en cuatro direcciones.', 3900],
                ['Falda-short de tenis', 'Con bolsillos internos para pelotas.', 2200],
                ['Sudadera oversize de algodón', 'Corte holgado para después del entrenamiento.', 2950],
                ['Calcetines deportivos (pack de 5)', 'Refuerzo en talón y arco, caña corta.', 890],
            ],
            'Ninos' => [
                ['Conjunto deportivo infantil', 'Camiseta y short en tejido transpirable, tallas 6 a 14.', 1650],
                ['Camiseta técnica junior', 'Secado rápido y protección solar UPF 30.', 890],
                ['Short de entrenamiento infantil', 'Cintura elástica con cordón interior.', 780],
                ['Sudadera con capucha para niños', 'Interior afelpado, bolsillo canguro.', 1950],
                ['Medias deportivas infantiles (pack 3)', 'Algodón con puño elástico suave.', 520],
                ['Chaqueta rompevientos junior', 'Ligera, con capucha guardable en el cuello.', 2400],
                ['Mochila escolar deportiva', 'Compartimento para calzado y botella.', 1850],
                ['Balón de iniciación talla 3', 'Ligero y blando, para primeros contactos.', 950],
                ['Peto de entrenamiento infantil (pack 5)', 'Malla ligera en tallas de 6 a 12 años.', 1100],
                ['Gorra deportiva ajustable junior', 'Visera curva y cierre de velcro.', 620],
            ],
            'Calzado' => [
                ['Zapatillas de running neutras', 'Amortiguación en talón y mediasuela de espuma EVA.', 6800],
                ['Tenis de baloncesto caña alta', 'Soporte de tobillo y suela de espiga para pivotar.', 7900],
                ['Botines de fútbol para césped natural', 'Doce tacos cónicos y empeine de microfibra.', 6400],
                ['Zapatillas de entrenamiento cruzado', 'Suela plana y estable para levantamiento.', 5600],
                ['Zapatillas de tenis para cancha dura', 'Refuerzo lateral y suela de goma resistente.', 7200],
                ['Sandalias deportivas post-entreno', 'Plantilla contorneada de EVA blanda.', 1650],
                ['Zapatillas de trail running', 'Taqueado agresivo y protección de puntera.', 8100],
                ['Botines de fútbol sala', 'Suela lisa de goma para pista techada.', 4900],
                ['Plantillas de gel amortiguadoras', 'Recortables, absorben impacto en talón.', 950],
                ['Zapatillas casuales deportivas', 'Para uso diario, con forro transpirable.', 4200],
            ],
            'Activewear' => [
                ['Mallas de compresión largas', 'Compresión graduada que mejora el retorno venoso.', 2900],
                ['Camiseta sin costuras', 'Tejido tubular que elimina roces en carreras largas.', 2350],
                ['Top de tirantes con sujeción', 'Diseño ligero para clima cálido.', 1650],
                ['Short de running con bolsillo trasero', 'Cierre con cremallera para llaves y tarjetas.', 1850],
                ['Chaleco reflectante para correr', 'Bandas de alta visibilidad, ajuste con velcro.', 1150],
                ['Conjunto de entrenamiento térmico', 'Capa base para entrenar en climas frescos.', 4200],
                ['Manguitos de compresión para pantorrilla', 'Reducen la fatiga en tiradas largas.', 1450],
                ['Camiseta manga larga con protección UV', 'UPF 50, ideal para actividades al aire libre.', 2650],
                ['Banda para la cabeza absorbente', 'Elástica, no se desplaza con el movimiento.', 480],
                ['Cinturón de running portaobjetos', 'Ajustable, sin rebote, con salida para auriculares.', 1350],
            ],
            // El filtro de TiendaView agrupa 'Accesorios' DENTRO de "Ropa
            // Deportiva", asi que aqui van accesorios de vestir. El material de
            // gimnasio (rodillos, esterillas, bandas) esta en Pesas y
            // Protecciones, que es donde la interfaz lo presenta.
            'Accesorios' => [
                ['Gorra deportiva de secado rápido', 'Visera curva, banda interior absorbente y cierre ajustable.', 890],
                ['Calcetines de compresión (par)', 'Compresión graduada, refuerzo en talón y arco.', 950],
                ['Muñequeras de rizo deportivas (par)', 'Algodón absorbente para tenis, pádel y gimnasio.', 450],
                ['Banda elástica para la cabeza', 'Sujeta el pelo y absorbe el sudor sin apretar.', 380],
                ['Guantes de entrenamiento sin dedos', 'Palma acolchada con cierre de velcro en la muñeca.', 1250],
                ['Gafas de sol deportivas polarizadas', 'Montura ligera con almohadillas antideslizantes.', 2650],
                ['Cinturón elástico para dorsal', 'Sujeta el número de competición sin agujerear la camiseta.', 620],
                ['Bufanda tubular multifunción', 'Se usa como cuello, banda o gorro. Tejido transpirable.', 580],
                ['Manguitos de compresión para brazo', 'Protección solar UPF 50 y soporte muscular ligero.', 1150],
                ['Set de cordones elásticos sin atar', 'Convierte cualquier zapatilla en calzado de quita y pon.', 490],
            ],
            'Pelotas' => [
                ['Balón medicinal 5 kg', 'Cubierta de goma con agarre texturizado, rebote controlado.', 3400],
                ['Pelota suiza de estabilidad 65 cm', 'Antiexplosión hasta 300 kg, con inflador.', 2100],
                ['Balón de voleibol de competición', 'Dieciocho paneles, cuero sintético cosido.', 3100],
                ['Pelota de pilates 25 cm', 'Blanda, para trabajo de core y estabilización.', 750],
                ['Balón de rugby talla 5', 'Superficie con relieve para agarre en mojado.', 2650],
                ['Slam ball 10 kg sin rebote', 'Relleno de arena, para lanzamientos contra el suelo.', 4200],
                ['Pelota de handball talla 2', 'Resina-compatible, agarre para juego profesional.', 2350],
                ['Balón de waterpolo', 'Superficie antideslizante para uso acuático.', 2900],
                ['Pelota de béisbol de cuero (docena)', 'Costura roja a mano, nivel competición.', 3800],
                ['Set de pelotas de reacción', 'Seis lados irregulares para entrenar reflejos.', 1150],
            ],
            'Raquetas' => [
                ['Raqueta de pádel formato lágrima', 'Núcleo EVA soft y cara de fibra de vidrio.', 7400],
                ['Raqueta de bádminton de carbono', 'Ochenta y cinco gramos, encordada a 24 lb.', 3900],
                ['Raqueta de squash 130 g', 'Marco de grafito con garganta abierta.', 5600],
                ['Raqueta de ping pong 5 estrellas', 'Cinco capas de madera con goma de doble cara.', 2400],
                ['Set de pádel para dos jugadores', 'Dos palas, tres pelotas y funda doble.', 11500],
                ['Raqueta de tenis de iniciación', 'Aluminio ligero, cabeza sobredimensionada.', 2900],
                ['Funda térmica para pala de pádel', 'Aislante, con bolsillo para pelotas.', 1450],
                ['Protector de marco autoadhesivo', 'Evita desgaste en el borde de la pala.', 380],
                ['Raqueta de bádminton junior', 'Mango reducido para manos pequeñas.', 1750],
                ['Set de ping pong con red portátil', 'Dos raquetas, tres pelotas y red ajustable.', 3200],
            ],
            'Bicicletas' => [
                ['Bicicleta de montaña rodado 29', 'Cuadro de aluminio, 21 velocidades y frenos de disco.', 32000],
                ['Bicicleta de ruta con grupo Shimano', 'Cuadro ligero, ruedas de perfil medio, 18 velocidades.', 48000],
                ['Bicicleta urbana con parrilla', 'Postura erguida, guardabarros y portaequipaje.', 21500],
                ['Bicicleta plegable rodado 20', 'Se pliega en 15 segundos, ideal para transporte.', 26500],
                ['Bicicleta infantil rodado 16', 'Con ruedas de apoyo desmontables y freno coaster.', 9800],
                ['Bicicleta fija de spinning', 'Volante de inercia de 18 kg y resistencia regulable.', 39000],
                ['Rodillo de entrenamiento magnético', 'Ocho niveles de resistencia, plegable.', 14500],
                ['Bicicleta de montaña doble suspensión', 'Recorrido de 120 mm delante y detrás.', 62000],
                ['Bicicleta híbrida de paseo', 'Combina comodidad urbana con eficiencia de ruta.', 24000],
                ['Portabicicletas para maletero', 'Soporta dos bicicletas, con correas de anclaje.', 8600],
            ],
            'Pesas' => [
                ['Mancuernas hexagonales 10 kg (par)', 'Hierro fundido con recubrimiento de goma antirruido.', 5400],
                ['Barra olímpica 20 kg', 'Acero con moleteado y rodamientos, 220 cm.', 18500],
                ['Set de discos de goma 60 kg', 'Pares de 5, 10 y 15 kg con agujero olímpico.', 22000],
                ['Kettlebell de 16 kg', 'Fundición de una pieza con asa ancha.', 4900],
                ['Mancuernas ajustables 2-24 kg', 'Cambio de peso con selector, ahorra espacio.', 26000],
                ['Banco de pesas regulable', 'Siete posiciones de respaldo, hasta 300 kg.', 12500],
                ['Rack de sentadillas plegable', 'Se abate contra la pared, con barras de seguridad.', 34000],
                ['Discos de peso fraccionados (0,5 a 2 kg)', 'Para progresiones finas en levantamientos.', 3200],
                ['Collarines de cierre rápido (par)', 'Sujeción firme sin herramientas.', 890],
                ['Barra Z para bíceps', 'Ciento veinte centímetros, agarre en zigzag.', 6800],
            ],
            'Protecciones' => [
                ['Coderas de compresión (par)', 'Soporte articular sin limitar el rango de movimiento.', 1350],
                ['Tobilleras elásticas con estabilizador', 'Varillas laterales para prevenir torceduras.', 1650],
                ['Muñequeras de levantamiento', 'Cincuenta centímetros con pulgar de anclaje.', 1250],
                ['Casco multideporte ajustable', 'Certificado para ciclismo, patinaje y skate.', 3400],
                ['Set de protecciones para patinaje', 'Rodilleras, coderas y muñequeras en una bolsa.', 2900],
                ['Rodillera con estabilizador rotuliano', 'Anillo de gel que centra la rótula.', 2200],
                ['Faja lumbar de neopreno', 'Retención de calor y soporte en zona baja.', 1850],
                ['Protector de espinilla para artes marciales', 'Empeine y tibia en una pieza acolchada.', 2650],
                ['Guantes de portero de protección extra', 'Varillas rígidas en los cinco dedos.', 3100],
                ['Gafas protectoras deportivas', 'Policarbonato antiimpacto con banda elástica.', 1950],
            ],
            'Proteinas' => [
                ['Proteína de suero 2 lb sabor chocolate', 'Veinticuatro gramos de proteína por porción, bajo en lactosa.', 4200],
                ['Proteína aislada 2 lb sabor vainilla', 'Noventa por ciento de pureza, absorción rápida.', 5600],
                ['Caseína micelar 2 lb', 'Liberación lenta, pensada para la noche.', 5100],
                ['Proteína vegana de guisante y arroz', 'Perfil completo de aminoácidos, sin lácteos.', 4800],
                ['Ganador de peso 5 lb', 'Mil doscientas calorías por porción con carbohidratos complejos.', 6400],
                ['Proteína de suero 5 lb sabor fresa', 'Formato económico para consumo diario.', 8900],
                ['BCAA en polvo 300 g', 'Ratio 2:1:1, para tomar durante el entrenamiento.', 3400],
                ['Creatina monohidratada 300 g', 'Micronizada, sin sabor, apta para mezclar.', 2800],
                ['Glutamina en polvo 250 g', 'Apoyo a la recuperación muscular.', 2400],
                ['Proteína de suero muestra (5 sobres)', 'Cinco sabores para probar antes de comprar.', 890],
            ],
            'Vitaminas' => [
                ['Multivitamínico deportivo 90 cápsulas', 'Fórmula completa con minerales y antioxidantes.', 2100],
                ['Vitamina D3 5000 UI (120 cápsulas)', 'Apoyo a la salud ósea y función inmune.', 1450],
                ['Omega 3 concentrado 1000 mg', 'EPA y DHA de aceite de pescado purificado.', 1950],
                ['Magnesio bisglicinato 120 cápsulas', 'Alta biodisponibilidad, apoyo muscular y descanso.', 1750],
                ['Vitamina C 1000 mg efervescente', 'Veinte tabletas sabor naranja.', 680],
                ['Complejo B en cápsulas', 'Ocho vitaminas del grupo B para el metabolismo energético.', 1350],
                ['Zinc + Magnesio + B6 (ZMA)', 'Noventa cápsulas, tomar antes de dormir.', 1850],
                ['Colágeno hidrolizado en polvo 300 g', 'Con vitamina C para articulaciones y piel.', 2650],
                ['Hierro con ácido fólico', 'Sesenta comprimidos, formulación suave al estómago.', 1150],
                ['Melatonina 3 mg (60 tabletas)', 'Apoyo al ciclo de sueño tras entrenamientos nocturnos.', 980],
            ],
            'Quemadores' => [
                ['Termogénico 60 cápsulas', 'Con cafeína, té verde y L-carnitina.', 2900],
                ['L-carnitina líquida 500 ml', 'Tres mil miligramos por porción, sabor cítrico.', 2400],
                ['Té verde en cápsulas 500 mg', 'Extracto estandarizado en EGCG.', 1450],
                ['CLA 1000 mg (90 cápsulas)', 'Ácido linoleico conjugado de origen vegetal.', 1950],
                ['Quemador sin estimulantes', 'Fórmula sin cafeína para tomar por la tarde.', 2650],
                ['Garcinia cambogia 60 cápsulas', 'Sesenta por ciento de HCA por dosis.', 1350],
                ['Pre-entreno termogénico 300 g', 'Energía y enfoque con beta-alanina.', 3200],
                ['Fibra soluble en polvo 250 g', 'Apoyo a la saciedad entre comidas.', 1250],
                ['Cafeína anhidra 200 mg (100 tabletas)', 'Dosis precisa para antes de entrenar.', 980],
                ['Pack quemador + L-carnitina', 'Combinación con descuento sobre el precio individual.', 4600],
            ],
            'Energizantes' => [
                ['Bebida energética sin azúcar (pack 12)', 'Doscientos cincuenta mililitros con cafeína y taurina.', 1850],
                ['Pre-entreno en polvo 400 g', 'Óxido nítrico, beta-alanina y cafeína.', 3600],
                ['Gel energético con cafeína (caja 12)', 'Veinticinco gramos de carbohidratos por gel.', 2400],
                ['Bebida isotónica en polvo 1 kg', 'Rinde 20 litros, con electrolitos y glucosa.', 2100],
                ['Shot de cafeína 60 ml (pack 6)', 'Doscientos miligramos por dosis, formato bolsillo.', 1450],
                ['Maltodextrina 1 kg', 'Carbohidrato de absorción rápida para recarga.', 1650],
                ['Tabletas efervescentes de electrolitos', 'Diez tabletas, sin azúcar, sabor limón.', 890],
                ['Gel energético sin cafeína (caja 12)', 'Para tiradas largas y consumo nocturno.', 2300],
                ['Bebida deportiva lista para beber (pack 6)', 'Quinientos mililitros, reposición durante ejercicio.', 1250],
                ['Pack de prueba de energéticos', 'Cuatro geles, dos shots y dos sobres isotónicos.', 1580],
            ],
            'Barras' => [
                ['Barra proteica 20 g (caja de 12)', 'Chocolate y cacahuate, baja en azúcar.', 2400],
                ['Barra energética de avena (caja 12)', 'Carbohidratos de liberación sostenida.', 1850],
                ['Barra de proteína vegana (caja 12)', 'Base de guisante, sin lácteos ni gluten.', 2650],
                ['Barra de cereales con frutas (caja 16)', 'Snack ligero para media mañana.', 1350],
                ['Barra sustitutiva de comida', 'Trescientas calorías con vitaminas añadidas, caja de 8.', 2900],
                ['Barra de nueces y semillas (caja 12)', 'Sin azúcar añadida, grasas saludables.', 2100],
                ['Barra proteica cubierta de chocolate', 'Veintidós gramos de proteína, caja de 12.', 2750],
                ['Barra de dátil y cacao (caja 10)', 'Solo ingredientes naturales, sin aditivos.', 1950],
                ['Caja mixta de barras (24 unidades)', 'Seis sabores distintos para variar.', 4200],
                ['Barra proteica junior (caja 10)', 'Porción reducida, pensada para adolescentes.', 1650],
            ],
            'Electrónicos' => [
                ['Reloj GPS multideporte', 'Perfiles de carrera, ciclismo y natación, batería de 14 días.', 18500],
                ['Banda de frecuencia cardíaca Bluetooth', 'Compatible con relojes y aplicaciones de entrenamiento.', 4200],
                ['Auriculares deportivos inalámbricos', 'Resistencia IPX7 y ocho horas de reproducción.', 5600],
                ['Báscula de composición corporal', 'Mide grasa, músculo y agua, sincroniza por app.', 3900],
                ['Podómetro clip con pantalla', 'Pasos, distancia y calorías sin necesidad de teléfono.', 1450],
                ['Altavoz portátil resistente al agua', 'Doce horas de batería, para entrenar en exteriores.', 3400],
                ['Sensor de cadencia y velocidad', 'Se monta en la bicicleta, conecta por Bluetooth.', 2900],
                ['Brazalete para teléfono', 'Pantalla táctil a través de la funda, ajustable.', 1150],
                ['Cuerda de saltar inteligente', 'Cuenta saltos y calorías, sincroniza con app.', 2650],
                ['Cronómetro de intervalos para box', 'Programable, con mando a distancia.', 5900],
            ],
            'Hidratación' => [
                ['Botella térmica de acero 750 ml', 'Mantiene frío 24 horas, tapa antiderrame.', 2400],
                ['Bidón deportivo 700 ml', 'Polietileno apto para alimentos, boquilla de apertura rápida.', 620],
                ['Mochila de hidratación 2 litros', 'Con bolsa de agua, tubo y válvula de mordida.', 5400],
                ['Cantimplora plegable 600 ml', 'Silicona, se enrolla cuando está vacía.', 890],
                ['Shaker con compartimento para polvo', 'Seiscientos mililitros con rejilla mezcladora.', 1150],
                ['Botella con infusor de frutas', 'Setecientos cincuenta mililitros, tritán sin BPA.', 1350],
                ['Jarra deportiva 2,2 litros', 'Con marcas horarias para controlar la ingesta diaria.', 1650],
                ['Botella de acero con pajilla 1 litro', 'Tapa con asa y pajilla incorporada.', 2900],
                ['Portabotellas de cintura', 'Para correr, con bolsillo pequeño para llaves.', 1450],
                ['Pack de 4 bidones para equipo', 'Setecientos mililitros cada uno, colores surtidos.', 2100],
            ],
            'Mochilas' => [
                ['Mochila deportiva 30 litros', 'Compartimento para portátil y bolsillo ventilado para calzado.', 3400],
                ['Bolsa de gimnasio con separador', 'Zona húmeda aislada y correa acolchada.', 2900],
                ['Mochila de hidratación para trail', 'Doce litros con bolsillos frontales para geles.', 6400],
                ['Saco de cuerdas ligero', 'Ideal para llevar lo justo al entrenamiento.', 890],
                ['Mochila antirrobo con puerto USB', 'Cierre oculto y bolsillo RFID.', 4200],
                ['Bolsa de viaje deportiva 60 litros', 'Con ruedas y asa telescópica.', 7600],
                ['Riñonera deportiva ajustable', 'Tres compartimentos, no rebota al correr.', 1250],
                ['Mochila impermeable 25 litros', 'Cierre enrollable, mantiene el contenido seco.', 3900],
                ['Bolsa para balón individual', 'Malla con cordón y asa reforzada.', 620],
                ['Mochila infantil deportiva 18 litros', 'Espalda acolchada, talla escolar.', 1850],
            ],
            'Relojes' => [
                ['Reloj deportivo con pulsómetro', 'Medición continua de frecuencia cardíaca en muñeca.', 12500],
                ['Reloj GPS de running', 'Ritmo, distancia y desnivel con precisión GPS.', 16800],
                ['Smartwatch con seguimiento de sueño', 'Fases del sueño y puntuación de recuperación diaria.', 9600],
                ['Reloj digital resistente al agua 50 m', 'Cronómetro, alarma y luz de fondo.', 2400],
                ['Reloj de natación con contador de largos', 'Detecta estilo y vueltas automáticamente.', 14500],
                ['Pulsera de actividad básica', 'Pasos, calorías y notificaciones del teléfono.', 3200],
                ['Reloj analógico deportivo', 'Correa de silicona y caja de acero inoxidable.', 5400],
                ['Reloj de triatlón multideporte', 'Transición automática entre los tres segmentos.', 24000],
                ['Correa de repuesto de silicona', 'Compatible con anchos de 20 y 22 mm.', 890],
                ['Cargador magnético de repuesto', 'Cable USB de 1 m para relojes deportivos.', 1150],
            ],
            'Toallas' => [
                ['Toalla de microfibra de secado rápido', 'Ochenta por ciento más absorbente que el algodón, 80x40 cm.', 890],
                ['Toalla refrescante de enfriamiento', 'Se activa con agua y baja la temperatura al contacto.', 750],
                ['Toalla de gimnasio con bolsillo', 'Cierre para guardar llaves mientras entrenas.', 1150],
                ['Set de 3 toallas deportivas', 'Tamaños pequeño, mediano y grande.', 1650],
                ['Toalla de playa deportiva 160x80', 'Microfibra ligera, cabe en la mochila.', 1450],
                ['Toalla para esterilla de yoga', 'Antideslizante, cubre la esterilla completa.', 1950],
                ['Toallitas refrescantes (pack 20)', 'Individuales, para después del entrenamiento.', 480],
                ['Toalla de mano para raqueta', 'Con mosquetón para colgar del bolso.', 620],
                ['Toalla de secado para natación', 'Absorbe el agua del cuerpo en una pasada.', 1250],
                ['Toalla personalizable de equipo', 'Bordado de nombre incluido, 100x50 cm.', 2100],
            ],
        ];
    }

    public function run(): void
    {
        $catalogo = $this->catalogo();
        $creados = 0;

        $sinFoto = [];

        foreach ($catalogo as $categoria => $productos) {
            foreach ($productos as [$nombre, $descripcion, $precio]) {
                $p = Product::firstOrCreate(
                    ['name' => $nombre],
                    [
                        'description' => $descripcion,
                        'price' => $precio,
                        'category' => $categoria,
                        'stock' => random_int(4, 60),
                        // Marcador: la URL definitiva se fija justo despues,
                        // cuando ya existe el id con el que se nombra el fichero.
                        'image' => '',
                    ]
                );

                // products/{id}.jpg en el disco publico. Con PUBLIC_DISK_DRIVER=s3
                // esto resuelve a la URL del bucket; en local, a /storage/...
                $rutaImagen = public_storage_url('products/' . $p->id . '.jpg');

                if ($p->image !== $rutaImagen) {
                    $p->image = $rutaImagen;
                    $p->save();
                }

                if (! Storage::disk('public')->exists('products/' . $p->id . '.jpg')) {
                    $sinFoto[] = $nombre;
                }

                if ($p->wasRecentlyCreated) {
                    $creados++;
                }
            }
        }

        $this->command->info("  Productos: {$creados} nuevos, " . Product::count() . ' en total en ' . count($catalogo) . ' categorías');

        // Aviso explicito si alguien agrega un producto y olvida su foto: sin
        // esto se quedaria con la generica y nadie se enteraria.
        if ($sinFoto) {
            $this->command->warn('  Sin fichero en el disco publico (' . count($sinFoto) . '): '
                . implode(', ', array_slice($sinFoto, 0, 5))
                . '  -> ejecuta: php artisan storage:subir-supabase --prefijo=products');
        }
    }
}
