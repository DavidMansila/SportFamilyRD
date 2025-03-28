<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class ScrapperController extends Controller
{

    // $authordiv = $node->filter('.post_layout_excerpt_wrap .post_header.entry-header .post_meta .post_meta_item.post_categories');
    // $author = $authordiv->text();
    public function baseballNews()
    {
        $client = new Client([
            'verify' => false, 
        ]);

        $response = $client->request('GET', 'https://lidom.com/');
        $html = (string) $response->getBody();

        $crawler = new Crawler($html);

            $baseball_news = $crawler->filter('.entry-box')->each(function (Crawler $node) use ($client) {
            $headline = $node->filter('.entry-title')->text();
            $link = $node->filter('a.cover-link')->attr('href');
            $author = $node->filter('.volanta')->text(); 

            //2do scraping 
            $description = '';
            $image = '';
            $subtitle = '';
            $date = '';
            
            try {
                $response = $client->request('GET', $link);
                $html = (string) $response->getBody();
                $subCrawler = new Crawler($html);

                $description = $subCrawler->filter('.article-body')->text();

                $image = $subCrawler->filter('.preview-images img')->attr('src');

                $subtitle = $subCrawler->filter('.nota-top-part h2')->text();

                // Extraer fecha del atributo datetime en el <time> dentro del div "extra-holder"
                $date = $subCrawler->filter(' .autor .extra-holder time')->attr('datetime');
            } catch (\Exception $e) {
                $description = 'No description available';
                $image = 'No image available';
                $subtitle = 'No subtitle available';
                $date = 'No date available';
            }

            return [
                'title' => $headline,
                'link' => $link,
                'description' => $description,
                'image' => $image,
                'author' => $author,
                'subtitle' => $subtitle,
                'date' => $date,
            ];
        });

        return response()->json([
            'baseball_news' => $baseball_news,
        ]);
    }

    public function futbolNews()
    {
        $client = new Client([
            'verify' => false,
        ]);
    
        $response = $client->request('GET', 'https://www.fedofutbol.do/all-posts/');
        $html = (string) $response->getBody();
    
        $crawler = new Crawler($html);
    
        $futbol_news = $crawler->filter('.posts_container article')->each(function (Crawler $node) use ($client) {
            $image = '';
            $link = '';
            $title = '';
            $date = '';
            $subtitle = '';
            $description = '';
            $author = '';
    
            try {
                // Obtener imagen y link
                $featuredDiv = $node->filter('.post_featured.with_thumb.hover_simple');
                $image = $featuredDiv->filter('img')->attr('src');
                $link = $featuredDiv->filter('a')->attr('href');
    
                // Obtener título y su link
                $titleDiv = $node->filter('.post_layout_excerpt_wrap .post_title.entry-title');
                $title = $titleDiv->filter('a')->text();
                $titleLink = $titleDiv->filter('a')->attr('href');
    
                // Obtener fecha
                $dateDiv = $node->filter('.post_layout_excerpt_wrap .post_header.entry-header .post_meta .post_meta_item.post_date');
                $date = $dateDiv->text();
                
                $authordiv = $node->filter('.post_layout_excerpt_wrap .post_header.entry-header .post_meta .post_meta_item.post_categories');
                $author = $authordiv->text();
    
                // Obtener subtitulo
                $descriptionDiv = $node->filter('.post_layout_excerpt_wrap .post_content.entry-content .post_content_inner');
                $subtitle = $descriptionDiv->text();
    
                // Segundo scraping para obtener la descripción completa
                try {
                    $response = $client->request('GET', $link);
                    $html = (string) $response->getBody();
                    $subCrawler = new Crawler($html);
    
                    // Extraer descripción desde el contenido del artículo
                    $description = $subCrawler->filter('.content .post_content.post_content_single.entry-content p')->each(function (Crawler $pNode) {
                        return $pNode->text();
                    });
    
                    // Convertir el array de párrafos en un solo string
                    $description = implode("\n", $description);
                } catch (\Exception $e) {
                    $description = 'No description available';
                }
            } catch (\Exception $e) {
                $image = 'No image available';
                $link = 'No link available';
                $title = 'No title available';
                $date = 'No date available';
                $subtitle = 'No subtitle available';
                $description = 'No description available';
                $author = 'No author available';
            }
    
            return [
                'image' => $image,
                'link' => $link,
                'title' => $title,
                'date' => $date,
                'subtitle' => $subtitle,
                'description' => $description,
                'author' => $author,
            ];
        });
    
        return response()->json([
            'futbol_news' => $futbol_news,
        ]);
    }



}