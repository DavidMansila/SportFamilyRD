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
            'verify' => false, // Desactivar la verificación SSL
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
            try {
                $response = $client->request('GET', $link);
                $html = (string) $response->getBody();
                $subCrawler = new Crawler($html);

                $description = $subCrawler->filter('.article-body')->text();

                $image = $subCrawler->filter('.preview-images img')->attr('src');
            } catch (\Exception $e) {
                $description = 'No description available';
                $image = 'No image available';
            }

            return [
                'title' => $headline,
                'link' => $link,
                'description' => $description,
                'image' => $image,
                'author' => $author,
            ];
        });

        return response()->json([
            'baseball_news' => $baseball_news,
        ]);
    }
}