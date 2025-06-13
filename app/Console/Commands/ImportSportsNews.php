<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\News;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class ImportSportsNews extends Command
{
    protected $signature = 'news:import';
    protected $description = 'Import sports news from external sources';

    private $scrapers = [
        'baseball' => 'scrapeBaseballNews',
        'futbol' => 'scrapeFutbolNews',
        'baloncesto' => 'scrapeBasketballNews',
        'volleyball' => 'scrapeVolleyballNews',
        'swimming' => 'scrapeSwimmingNews',
    ];

    public function handle()
    {
        $this->info('Starting news import process...');

        $totalImported = 0;

        foreach ($this->scrapers as $category => $method) {
            try {
                $this->info("Importing {$category} news...");
                $articles = $this->$method();

                if (count($articles)) {
                    $this->info("Found " . count($articles) . " articles for {$category}");
                    $imported = $this->importArticles($articles, $category);
                    $this->info("Imported {$imported} {$category} articles");
                    $totalImported += $imported;
                } else {
                    $this->warn("No articles found for {$category}");
                }
            } catch (\Exception $e) {
                $this->error("Error importing {$category} news: " . $e->getMessage());
                Log::error("Error importing {$category} news", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("Total imported: {$totalImported} articles");
        $this->info('News import completed successfully!');
    }

    private function importArticles($articles, $category)
    {
        $imported = 0;
        $today = now()->toDateString();

        foreach ($articles as $article) {
            try {
                if (empty($article['title'])) {
                    continue;
                }

                // Parseo de fecha con fallback
                $publishedAt = $this->parseDate($article['date'] ?? '', $category) ?? now();

                // Verificación flexible de duplicados
                $duplicate = News::where('title', 'like', '%' . mb_substr($article['title'], 0, 30) . '%')
                    ->where('category', $category)
                    ->whereDate('published_at', $publishedAt->toDateString())
                    ->exists();

                if ($duplicate) {
                    continue;
                }

                News::create([
                    'title' => $article['title'] ?? 'Sin título',
                    'description' => $article['description'] ?? '',
                    'author' => $article['author'] ?? $this->getSource($category),
                    'source' => $this->getSource($category),
                    'url' => $article['link'] ?? null,
                    'image' => $article['image'] ?? null,
                    'category' => $category,
                    'published_at' => $publishedAt
                ]);

                $imported++;
                $this->info("Nuevo: {$article['title']}");
            } catch (\Exception $e) {
                $this->error("Error en {$article['title']}: " . $e->getMessage());
            }
        }

        return $imported;
    }

    private function parseDate($dateString, $category)
    {
        if (empty($dateString)) {
            return Carbon::create(0);
        }

        $dateString = trim($dateString);

        try {
            switch ($category) {
                case 'futbol': // "7 de enero de 2025"
                    $partsFutbol = explode(' de ', $dateString);
                    if (count($partsFutbol) !== 3) return Carbon::create(0);
                    $dayFutbol = intval($partsFutbol[0]);
                    $monthFutbol = $this->getMonthNumber($partsFutbol[1]);
                    $yearFutbol = intval($partsFutbol[2]);
                    if ($dayFutbol === 0) return Carbon::create(0);
                    return Carbon::create($yearFutbol, $monthFutbol + 1, $dayFutbol, 0, 0, 0);

                case 'baloncesto': // "domingo 04 mayo, 2025"
                    $partsBaloncesto = explode(' ', $dateString);
                    if (count($partsBaloncesto) < 4) return Carbon::create(0);
                    $dayBaloncesto = intval($partsBaloncesto[1]);
                    $monthBaloncesto = $this->getMonthNumber(str_replace(',', '', $partsBaloncesto[2]));
                    $yearBaloncesto = intval($partsBaloncesto[3]);
                    if ($dayBaloncesto === 0) return Carbon::create(0);
                    return Carbon::create($yearBaloncesto, $monthBaloncesto + 1, $dayBaloncesto, 0, 0, 0);

                case 'baseball': // "06/05/2025   ·   01:31 PM"
                    $dateParts = explode('·', $dateString);
                    $datePart = trim($dateParts[0]);
                    $datePartsBeisbol = explode('/', $datePart);
                    if (count($datePartsBeisbol) !== 3) return Carbon::create(0);
                    $dayBeisbol = intval($datePartsBeisbol[0]);
                    $monthBeisbol = intval($datePartsBeisbol[1]);
                    $yearBeisbol = intval($datePartsBeisbol[2]);
                    if ($dayBeisbol === 0 || $monthBeisbol === 0) return Carbon::create(0);
                    return Carbon::create($yearBeisbol, $monthBeisbol, $dayBeisbol, 0, 0, 0);

                case 'volleyball': // "May 5, 2025"
                    $partsVolleyball = explode(' ', $dateString);
                    if (count($partsVolleyball) < 3) return Carbon::create(0);
                    $monthVolleyball = $this->getMonthNumber($partsVolleyball[0]);
                    $dayVolleyball = intval(str_replace(',', '', $partsVolleyball[1]));
                    $yearVolleyball = intval($partsVolleyball[2]);
                    if ($dayVolleyball === 0) return Carbon::create(0);
                    return Carbon::create($yearVolleyball, $monthVolleyball + 1, $dayVolleyball, 0, 0, 0);

                case 'swimming': // "agosto 22, 2024"
                    $partsSwimming = explode(' ', $dateString);
                    if (count($partsSwimming) < 3) return Carbon::create(0);
                    $monthSwimming = $this->getMonthNumber($partsSwimming[0]);
                    $daySwimming = intval(str_replace(',', '', $partsSwimming[1]));
                    $yearSwimming = intval($partsSwimming[2]);
                    if ($daySwimming === 0) return Carbon::create(0);
                    return Carbon::create($yearSwimming, $monthSwimming + 1, $daySwimming, 0, 0, 0);

                default:
                    try {
                        return Carbon::parse($dateString) ?: Carbon::create(0);
                    } catch (\Exception $e) {
                        return Carbon::create(0);
                    }
            }
        } catch (\Exception $e) {
            Log::error("Error parsing date ($category): \"$dateString\"", ['error' => $e->getMessage()]);
            return Carbon::create(0);
        }
    }

    private function getMonthNumber($monthName)
    {
        if (empty($monthName)) {
            return 0;
        }

        $months = [
            'enero' => 0,
            'january' => 0,
            'jan' => 0,
            'febrero' => 1,
            'february' => 1,
            'feb' => 1,
            'marzo' => 2,
            'march' => 2,
            'mar' => 2,
            'abril' => 3,
            'april' => 3,
            'apr' => 3,
            'mayo' => 4,
            'may' => 4,
            'junio' => 5,
            'june' => 5,
            'jun' => 5,
            'julio' => 6,
            'july' => 6,
            'jul' => 6,
            'agosto' => 7,
            'august' => 7,
            'aug' => 7,
            'septiembre' => 8,
            'september' => 8,
            'sep' => 8,
            'setiembre' => 8,
            'octubre' => 9,
            'october' => 9,
            'oct' => 9,
            'noviembre' => 10,
            'november' => 10,
            'nov' => 10,
            'diciembre' => 11,
            'december' => 11,
            'dec' => 11
        ];

        $normalizedMonth = strtolower(trim(str_replace(',', '', $monthName)));
        return $months[$normalizedMonth] ?? 0;
    }

    private function getSource($category)
    {
        $sources = [
            'baseball' => 'LIDOM',
            'futbol' => 'FEDOFUTBOL',
            'baloncesto' => 'FEDOMBAL',
            'volleyball' => 'Voleibol Dominicano',
            'swimming' => 'CDN Deportes',
        ];

        return $sources[$category] ?? 'Fuente desconocida';
    }

    // ============================
    // Métodos de scraping completos
    // ============================

    private function scrapeBaseballNews()
    {
        $client = new Client([
            'verify' => false,
            'timeout' => 15,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ]
        ]);

        $articles = [];

        try {
            $response = $client->get('https://lidom.com/');
            $crawler = new Crawler((string)$response->getBody());

            // Selector actualizado
            $links = $crawler->filter('.entry-box a.cover-link')->each(function (Crawler $node) {
                return $node->attr('href');
            });

            $links = array_filter($links);

            foreach ($links as $link) {
                try {
                    $response = $client->get($link);
                    $subCrawler = new Crawler((string)$response->getBody());

                    // Selectores actualizados
                    $title = $subCrawler->filter('section.nota-top-part h1')->text();
                    $author = $subCrawler->filter('div.autor div.extra-holder p')->text();
                    $date = $subCrawler->filter('div.autor div.extra-holder time')->text();
                    $image = $subCrawler->filter('div.preview-images figure img')->attr('src');

                    // Descripción limpia
                    $description = $subCrawler->filter('div.article-body')->each(function (Crawler $node) {
                        $node->filter('div.article-audio-container, amp-ad, amp, amp-youtube')->each(function (Crawler $childNode) {
                            $childNode->getNode(0)->parentNode->removeChild($childNode->getNode(0));
                        });
                        return $node->filter('p')->each(function (Crawler $pNode) {
                            return $pNode->text();
                        });
                    });
                    $description = implode("\n", $description[0] ?? []);

                    $articles[] = [
                        'title' => $title,
                        'author' => $author,
                        'published_at' => $date,
                        'image' => $image,
                        'description' => $description,
                        'link' => $link,
                    ];
                } catch (\Exception $e) {
                    Log::error("Error scraping baseball article", [
                        'link' => $link,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::critical("Error scraping baseball main page: " . $e->getMessage());
        }

        return $articles;
    }

    private function scrapeFutbolNews()
    {
        $client = new Client([
            'verify' => false,
            'timeout' => 20,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'es-ES,es;q=0.9',
            ]
        ]);

        $articles = [];

        try {
            $response = $client->get('https://www.fedofutbol.do/all-posts/');
            $crawler = new Crawler((string)$response->getBody());

            // Selector actualizado
            $links = $crawler->filter('.posts_container article .post_featured.with_thumb.hover_simple a')->each(function (Crawler $node) {
                return $node->attr('href');
            });

            $links = array_filter($links);

            foreach ($links as $link) {
                try {
                    $response = $client->get($link);
                    $subCrawler = new Crawler((string)$response->getBody());

                    // Selectores actualizados
                    $title = $subCrawler->filter('.post_title.entry-title')->text();
                    $image = $subCrawler->filter('.post_featured img')->attr('src');
                    $date = $subCrawler->filter('.post_meta_item.post_date')->text();
                    $author = $subCrawler->filter('.post_meta_item.post_categories')->text();

                    $description = $subCrawler->filter('.content .post_content.post_content_single.entry-content p')->each(function (Crawler $pNode) {
                        return $pNode->text();
                    });

                    $articles[] = [
                        'title' => $title,
                        'image' => $image,
                        'date' => $date,
                        'description' => implode("\n", $description),
                        'author' => $author,
                        'link' => $link,
                    ];
                } catch (\Exception $e) {
                    Log::error("Error scraping futbol article", [
                        'link' => $link,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::critical("Error scraping futbol main page: " . $e->getMessage());
        }

        return $articles;
    }


    private function scrapeBasketballNews()
    {
        $client = new Client(['verify' => false]);
        $articles = [];

        $response = $client->request('GET', 'https://fedombal.org/seccion/noticia/');
        $html = (string) $response->getBody();
        $crawler = new Crawler($html);

        // Selector actualizado
        $links = $crawler->filter('div.container div.row div.col-md-4.col-xs-12 div.seccion-item-box a:first-of-type')->each(function (Crawler $node) {
            return trim($node->attr('href'));
        });

        $links = array_filter($links);
        $links = array_unique($links);

        foreach ($links as $link) {
            try {
                $response = $client->get($link);
                $subCrawler = new Crawler((string)$response->getBody());

                $title = $subCrawler->filter('div.col-md-12.col-xs-12 h1.single-title')->text();
                $author = $subCrawler->filter('div.single-follow ul.nota-detalles li')->eq(1)->text();
                $date = $subCrawler->filter('div.single-follow ul.nota-detalles li')->eq(2)->text();
                $image = $subCrawler->filter('div.col-md-8.col-xs-12 div.white-box figure.nota-img amp-img')->attr('src');
                $description = $subCrawler->filter('div.col-md-8.col-xs-12 div.white-box div.nota p')->each(function (Crawler $pNode) {
                    return $pNode->text();
                });

                $articles[] = [
                    'title' => $title,
                    'author' => trim(str_replace('Por:', '', $author)),
                    'date' => $date,
                    'image' => $image,
                    'description' => implode("\n", $description),
                    'link' => $link,
                ];
            } catch (\Exception $e) {
                Log::error("Error scraping basketball article", [
                    'link' => $link,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $articles;
    }

    private function scrapeVolleyballNews()
    {
        $client = new Client(['verify' => false]);
        $baseUrl = 'https://voleiboldominicano.com/author/admin/';
        $currentPage = $baseUrl;
        $articles = [];

        do {
            try {
                $response = $client->request('GET', $currentPage);
                $html = (string) $response->getBody();
                $crawler = new Crawler($html);

                $links = $crawler->filter('div.mg-posts-sec.mg-posts-modul-6 div.mg-posts-sec-inner article div.col-12.col-md-6 div.mg-post-thumb.back-img.md a.link-div')->each(function (Crawler $node) {
                    try {
                        return trim($node->attr('href'));
                    } catch (\Exception $e) {
                        return null;
                    }
                });

                $links = array_filter($links);
                $links = array_unique($links);

                foreach ($links as $link) {
                    try {
                        $response = $client->get($link);
                        $html = (string) $response->getBody();
                        $subCrawler = new Crawler($html);

                        $title = $subCrawler->filter('div.mg-header h1.title.single')->text();
                        $author = $subCrawler->filter('div.media.mg-info-author-block div.media-body h4 a')->text();
                        $date = $subCrawler->filter('div.media.mg-info-author-block div.media-body span.mg-blog-date')->text();
                        $image = $subCrawler->filter('img.img-fluid.wp-post-image')->attr('src');
                        $description = $subCrawler->filter('article.page-content-single.small.single p')->each(function (Crawler $pNode) {
                            return $pNode->text();
                        });

                        $articles[] = [
                            'title' => $title,
                            'author' => $author,
                            'date' => $date,
                            'image' => $image,
                            'description' => implode("\n", $description),
                            'link' => $link,
                        ];
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                $nextPage = null;
                try {
                    $nextPage = $crawler->filter('a[rel="next"]')->attr('href');
                } catch (\Exception $e) {
                    $nextPage = null;
                }
                $currentPage = $nextPage;
            } catch (\Exception $e) {
                $currentPage = null;
            }
        } while ($currentPage);

        return $articles;
    }

    private function scrapeSwimmingNews()
    {
        $client = new Client(['verify' => false]);
        $articles = [];

        $response = $client->request('GET', 'https://cdndeportes.com.do/tag/natacion/');
        $html = (string) $response->getBody();
        $crawler = new Crawler($html);

        // Selector actualizado
        $links = $crawler->filter('div.row.justify-content-center div.col-md-6.tablet_full_width div.single_post.post__grid__layout__style__2 div.single_post_text h4 a')->each(function (Crawler $node) {
            return $node->attr('href');
        });

        $links = array_filter($links);

        foreach ($links as $link) {
            try {
                $response = $client->get($link);
                $subCrawler = new Crawler((string)$response->getBody());

                $title = $subCrawler->filter('div.elementor-widget-container h1.elementor-heading-title.elementor-size-default')->text();
                $image = $subCrawler->filter('div.elementor-element.elementor-element-a663d17.elementor-widget.elementor-widget-theme-post-featured-image.elementor-widget-image div.elementor-widget-container img')->attr('src');
                $author = $subCrawler->filter('div.elementor-author-box .elementor-author-box__text .elementor-author-box__name')->text();

                $description = $subCrawler->filter('div.elementor-element.elementor-element-0259b0e.elementor-widget.elementor-widget-theme-post-content .elementor-widget-container p')->each(function (Crawler $pNode) {
                    return trim($pNode->text());
                });

                $date = '';
                $subCrawler->filter('div.elementor-widget-container .post-single .page_comments ul.inline li')->each(function (Crawler $node) use (&$date) {
                    $text = $node->text();
                    if (strpos($text, ',') !== false) {
                        $date = $text;
                    }
                });

                $articles[] = [
                    'title' => $title,
                    'image' => $image,
                    'author' => $author,
                    'description' => implode("\n", $description),
                    'date' => $date,
                    'link' => trim($link),
                ];
            } catch (\Exception $e) {
                Log::error("Error scraping swimming article", [
                    'link' => $link,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $articles;
    }
}
