<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class ScrapperController extends Controller
{
    public function scrape()
    {
        $client = new Client([
            'verify' => false, // todo ponerlo normal ahora que le funciona a mansilla Desactivar la verificación SSL
        ]);//todo ejemplo new client

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
}