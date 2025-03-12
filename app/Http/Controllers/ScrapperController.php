<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class ScrapperController extends Controller
{
    public function scrape()
    {
        $client = new Client();

        $response = $client->request('GET', 'https://lidom.com/');
        $html = (string) $response->getBody();

        $crawler = new Crawler($html);

        // Get the title of the page
        $title = $crawler->filter('title')->text();

        // Get all links on the page
        $links = $crawler->filter('a')->each(function (Crawler $node) {
            return $node->attr('href');
        });

        // Get news headlines and their links
        $news = $crawler->filter('.entry-box')->each(function (Crawler $node) {
            $headline = $node->filter('.entry-title')->text();
            $link = $node->filter('a.cover-link')->attr('href');
            return [
                'title' => $headline,
                'link' => $link
            ];
        });

        // Group the news in a separate variable
        $newsData = [
            'title' => $title,
            'links' => $links,
            'news' => $news,
        ];
        
    

        return response()->json($newsData);
    }
}