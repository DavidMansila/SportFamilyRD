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

        // Get the title of the page
        $title = $crawler->filter('title')->text();

        // Get all links on the page
        $links = $crawler->filter('a')->each(function (Crawler $node) {
            return $node->attr('href');
        });

        // Get news headlines, links, descriptions, and images
        $news = $crawler->filter('.entry-box')->each(function (Crawler $node) use ($client) {
            $headline = $node->filter('.entry-title')->text();
            $link = $node->filter('a.cover-link')->attr('href');

            // Perform a second scraping for description and image
            $description = '';
            $image = '';
            try {
                $response = $client->request('GET', $link);
                $html = (string) $response->getBody();
                $subCrawler = new Crawler($html);

                // Extract description
                $description = $subCrawler->filter('.article-body')->text();

                // Extract image
                $image = $subCrawler->filter('.preview-images img')->attr('src');
            } catch (\Exception $e) {
                // Handle errors gracefully
                $description = 'No description available';
                $image = 'No image available';
            }

            return [
                'title' => $headline,
                'link' => $link,
                'description' => $description,
                'image' => $image,
            ];
        });

        // Group the news in a separate variable
        $newsData = [
            'Baseball_news' => $news,
        ];

        return response()->json($newsData);
    }
}