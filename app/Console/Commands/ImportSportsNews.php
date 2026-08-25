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

        foreach ($articles as $article) {
            try {
                if (empty($article['title'])) {
                    continue;
                }

                // Parseo de fecha con fallback seguro
                $publishedAt = $this->parseDate($article['date'] ?? '', $category);

                // Verificación de fecha mínima válida para MySQL (1000-01-01)
                if ($publishedAt->year < 1000) {
                    $publishedAt = now();
                }

                // Verificación de duplicados
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
                    'published_at' => $publishedAt,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $imported++;
                $this->info("Nuevo: {$article['title']}");
            } catch (\Exception $e) {
                $this->error("Error en {$article['title']}: " . $e->getMessage());
                Log::error("Error importing article", [
                    'title' => $article['title'] ?? 'No title',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return $imported;
    }

    

    private function parseDate($dateString, $category)
    {
        if (empty($dateString)) {
            return now(); // Devuelve la fecha actual como fallback
        }

        $dateString = trim($dateString);

        try {
            switch ($category) {
                case 'futbol':
                    $partsFutbol = explode(' de ', $dateString);
                    if (count($partsFutbol) !== 3) return now();
                    $dayFutbol = intval($partsFutbol[0]);
                    $monthFutbol = $this->getMonthNumber($partsFutbol[1]);
                    $yearFutbol = intval($partsFutbol[2]);
                    return Carbon::create($yearFutbol, $monthFutbol + 1, $dayFutbol, 0, 0, 0);

                case 'baloncesto':
                    $partsBaloncesto = explode(' ', $dateString);
                    if (count($partsBaloncesto) < 4) return now();
                    $dayBaloncesto = intval($partsBaloncesto[1]);
                    $monthBaloncesto = $this->getMonthNumber(str_replace(',', '', $partsBaloncesto[2]));
                    $yearBaloncesto = intval($partsBaloncesto[3]);
                    return Carbon::create($yearBaloncesto, $monthBaloncesto + 1, $dayBaloncesto, 0, 0, 0);

                case 'baseball':
                    $dateParts = explode('·', $dateString);
                    $datePart = trim($dateParts[0]);
                    $datePartsBeisbol = explode('/', $datePart);
                    if (count($datePartsBeisbol) !== 3) return now();
                    $dayBeisbol = intval($datePartsBeisbol[0]);
                    $monthBeisbol = intval($datePartsBeisbol[1]);
                    $yearBeisbol = intval($datePartsBeisbol[2]);
                    return Carbon::create($yearBeisbol, $monthBeisbol, $dayBeisbol, 0, 0, 0);

                case 'volleyball':
                    $partsVolleyball = explode(' ', $dateString);
                    if (count($partsVolleyball) < 3) return now();
                    $monthVolleyball = $this->getMonthNumber($partsVolleyball[0]);
                    $dayVolleyball = intval(str_replace(',', '', $partsVolleyball[1]));
                    $yearVolleyball = intval($partsVolleyball[2]);
                    return Carbon::create($yearVolleyball, $monthVolleyball + 1, $dayVolleyball, 0, 0, 0);

                case 'swimming':
                    // La fecha viene del meta tag article:published_time (ISO 8601), Carbon la parsea directo.
                    try {
                        return Carbon::parse($dateString);
                    } catch (\Exception $e) {
                        return now();
                    }

                default:
                    try {
                        return Carbon::parse($dateString) ?: now();
                    } catch (\Exception $e) {
                        return now();
                    }
            }
        } catch (\Exception $e) {
            Log::error("Error parsing date ($category): \"$dateString\"", ['error' => $e->getMessage()]);
            return now(); // Devuelve la fecha actual cuando hay error
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

    /**
     * Algunos sitios (fedofutbol.do) cargan la imagen destacada con lazy-load:
     * el <img src> real es un placeholder SVG transparente de 1x1 y la URL
     * verdadera vive en un atributo data-* que un navegador real reemplaza
     * al hacer scroll, pero que el scraper (sin JS) nunca dispara.
     */
    private function extractLazyImage(Crawler $crawler, string $selector): ?string
    {
        $node = $crawler->filter($selector);
        if (!$node->count()) {
            return null;
        }

        foreach (['data-src', 'data-lazy-src', 'data-original', 'src'] as $attr) {
            $value = $node->attr($attr);
            if ($value && !str_starts_with($value, 'data:image')) {
                return $value;
            }
        }

        return null;
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
                    $image = $this->extractLazyImage($subCrawler, '.post_featured img');
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

        // Selector actualizado: el sitio cambio de tema (Elementor) a uno estandar de
        // WordPress; la clase "single_post" que se usaba antes ya no existe.
        $links = $crawler->filter('.entry-title a')->each(function (Crawler $node) {
            return $node->attr('href');
        });

        $links = array_unique(array_filter($links));

        foreach ($links as $link) {
            try {
                $response = $client->get($link);
                $subCrawler = new Crawler((string)$response->getBody());

                $title = $subCrawler->filter('h1.entry-title')->text();

                $image = null;
                $imageNode = $subCrawler->filter('meta[property="og:image"]');
                if ($imageNode->count()) {
                    $image = $imageNode->attr('content');
                }

                $author = null;
                $authorNode = $subCrawler->filter('a[rel="author"]');
                if ($authorNode->count()) {
                    $author = trim($authorNode->first()->text());
                }

                $date = null;
                $dateNode = $subCrawler->filter('meta[property="article:published_time"]');
                if ($dateNode->count()) {
                    $date = $dateNode->attr('content');
                }

                $description = $subCrawler->filter('.entry-content p')->each(function (Crawler $pNode) {
                    return trim($pNode->text());
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
