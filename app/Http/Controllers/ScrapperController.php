<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class ScrapperController extends Controller
{
    public function scrape()
    {
        try {
            // Crear un cliente HTTP
            $client = new Client([
                'verify' => false, // Desactivar la verificación SSL
            ]);

            // Hacer la solicitud GET
            $response = $client->request('GET', 'https://lidom.com/');
            $html = (string) $response->getBody();

            // Verificar si la respuesta fue exitosa
            if ($response->getStatusCode() !== 200) {
                return response()->json(['error' => 'Error al obtener la página'], 500);
            }

            // Crear el objeto Crawler con el HTML obtenido
            $crawler = new Crawler($html);

            // Obtener el título de la página
            $title = $crawler->filter('title')->text();

            // Obtener todos los enlaces en la página
            $links = $crawler->filter('a')->each(function (Crawler $node) {
                return $node->attr('href');
            });

            // Obtener los titulares de noticias y sus enlaces
            $news = $crawler->filter('.entry-box')->each(function (Crawler $node) {
                $headline = $node->filter('.entry-title')->text();  // Titular
                $link = $node->filter('a.cover-link')->attr('href'); // Enlace
                
                return [
                    'title' => $headline,
                    //subtitle => $subtitle,
                    //image => $image,
                    //autor => $autor,
                    //date => $date,
                    'link' => $link
                ];
            });

            // Verificar si se obtuvieron noticias
            if (empty($news)) {
                return response()->json(['message' => 'No se encontraron noticias'], 404);
            }

            // Agrupar los datos en una variable separada
            $newsData = [
                'title' => $title,
                //subtitle => $subtitle,
                //image => $image,
                //autor => $autor,
                //date => $date,
                'links' => $links,
                'news' => $news,
            ];

            // Devolver los datos como JSON
            return response()->json($newsData);
        } catch (\Exception $e) {
            // Log de error
            Log::error('Error en Scraper: ' . $e->getMessage());

            // Devolver error si algo salió mal
            return response()->json(['error' => 'Hubo un problema al procesar la solicitud'], 500);
        }
    }
}