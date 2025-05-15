<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Promise\Utils;
use Illuminate\Support\Facades\Cache;

class ScrapperController extends Controller
{
  
    public function baseballNews()
    {
        // Usar Cache::remember para almacenar los resultados en caché durante 30 días
        $articles = Cache::remember('baseball_news', now()->addDays(30), function () {
            $client = new Client([
                'verify' => false,
            ]);

            // Primer scraping: Obtener solo los enlaces
            $response = $client->request('GET', 'https://lidom.com/');
            $html = (string) $response->getBody();

            $crawler = new Crawler($html);

            $links = $crawler->filter('.entry-box a.cover-link')->each(function (Crawler $node) {
                try {
                    return $node->attr('href'); // Extraer solo el enlace
                } catch (\Exception $e) {
                    return null;
                }
            });

            // Filtrar enlaces no válidos
            $links = array_filter($links);

            // Segundo scraping: Obtener los datos de cada enlace
            $promises = [];
            foreach ($links as $link) {
                $promises[] = $client->getAsync($link);
            }

            $responses = Utils::settle($promises)->wait();

            $articles = [];
            foreach ($responses as $index => $response) {
                if ($response['state'] === 'fulfilled') {
                    try {
                        $html = (string) $response['value']->getBody();
                        $subCrawler = new Crawler($html);

                        // Extraer datos desde la página del enlace
                        $title = $subCrawler->filter('section.nota-top-part h1')->text();
                        $author = $subCrawler->filter('div.autor div.extra-holder p')->text();
                        $date = $subCrawler->filter('div.autor div.extra-holder time')->text();
                        $image = $subCrawler->filter('div.preview-images figure img')->attr('src');

                        // Filtrar y extraer la descripción ignorando elementos no deseados
                        $description = $subCrawler->filter('div.article-body')->each(function (Crawler $node) {
                            $node->filter('div.article-audio-container, amp-ad, amp, amp-youtube')->each(function (Crawler $childNode) {
                                foreach ($childNode as $child) {
                                    $child->parentNode->removeChild($child);
                                }
                            });

                            return $node->filter('p')->each(function (Crawler $pNode) {
                                return $pNode->text();
                            });
                        });

                        $articles[] = [
                            'title' => $title,
                            'author' => $author,
                            'date' => $date,
                            'image' => $image,
                            'description' => implode("\n", $description[0] ?? []),
                        ];
                    } catch (\Exception $e) {
                        $articles[] = [
                            'title' => 'No title available',
                            'author' => 'No author available',
                            'date' => 'No date available',
                            'image' => 'No image available',
                            'description' => 'No description available',
                        ];
                    }
                }
            }

            return $articles;
        });

        return response()->json([
            'baseball_news' => $articles,
        ]);
    }

    public function futbolNews()
    {
        // Usar Cache::remember para almacenar los resultados en caché durante 30 días
        $articles = Cache::remember('futbol_news', now()->addDays(30), function () {
            $client = new Client([
                'verify' => false,
            ]);
    
            // Primer scraping: Obtener solo los enlaces
            $response = $client->request('GET', 'https://www.fedofutbol.do/all-posts/');
            $html = (string) $response->getBody();
    
            $crawler = new Crawler($html);
    
            $links = $crawler->filter('.posts_container article .post_featured.with_thumb.hover_simple a')->each(function (Crawler $node) {
                try {
                    return $node->attr('href'); // Extraer solo el enlace
                } catch (\Exception $e) {
                    return null;
                }
            });
    
            // Filtrar enlaces no válidos
            $links = array_filter($links);
    
            // Segundo scraping: Obtener los datos de cada enlace
            $promises = [];
            foreach ($links as $link) {
                $promises[] = $client->getAsync($link);
            }
    
            $responses = Utils::settle($promises)->wait();
    
            $articles = [];
            foreach ($responses as $index => $response) {
                if ($response['state'] === 'fulfilled') {
                    try {
                        $html = (string) $response['value']->getBody();
                        $subCrawler = new Crawler($html);
    
                        // Extraer datos desde la página del enlace
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
                        ];
                    } catch (\Exception $e) {
                        $articles[] = [
                            'title' => 'No title available',
                            'image' => 'No image available',
                            'date' => 'No date available',
                            'description' => 'No description available',
                            'author' => 'No author available',
                        ];
                    }
                }
            }
    
            return $articles;
        });
    
        return response()->json([
            'futbol_news' => $articles,
        ]);
    }

    public function basketballNews()
    {
        // Usar Cache::remember para almacenar los resultados en caché durante 30 días
        $articles = Cache::remember('basketball_news', now()->addDays(30), function () {
            $client = new Client([
                'verify' => false,
            ]);

            // Primer scraping: Obtener solo los enlaces
            $response = $client->request('GET', 'https://fedombal.org/seccion/noticia/');
            $html = (string) $response->getBody();

            $crawler = new Crawler($html);

            // Ajustar el selector para evitar duplicados
            $links = $crawler->filter('div.container div.row div.col-md-4.col-xs-12 div.seccion-item-box a:first-of-type')->each(function (Crawler $node) {
                try {
                    return trim($node->attr('href')); // Extraer y normalizar el enlace
                } catch (\Exception $e) {
                    return null;
                }
            });

            // Filtrar enlaces no válidos y eliminar duplicados
            $links = array_filter($links);
            $links = array_unique($links);

            // Segundo scraping: Obtener los datos de cada enlace
            $promises = [];
            foreach ($links as $link) {
                $promises[] = $client->getAsync($link);
            }

            $responses = Utils::settle($promises)->wait();

            $articles = [];
            foreach ($responses as $index => $response) {
                if ($response['state'] === 'fulfilled') {
                    try {
                        $html = (string) $response['value']->getBody();
                        $subCrawler = new Crawler($html);

                        // Extraer datos desde la página del enlace
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
                        ];
                    } catch (\Exception $e) {
                        $articles[] = [
                            'title' => 'No title available',
                            'author' => 'No author available',
                            'date' => 'No date available',
                            'image' => 'No image available',
                            'description' => 'No description available',
                        ];
                    }
                }
            }

            return $articles;
        });

        return response()->json([
            'basketball_news' => $articles,
        ]);
    }

    public function volleyballNews()
    {
        // Usar Cache::remember para almacenar los resultados en caché durante 30 días
        $articles = Cache::remember('volleyball_news', now()->addDays(30), function () {
            $client = new Client([
                'verify' => false,
            ]);
    
            $baseUrl = 'https://voleiboldominicano.com/author/admin/';
            $currentPage = $baseUrl;
            $articles = [];
    
            do {
                try {
                    $response = $client->request('GET', $currentPage);
                    $html = (string) $response->getBody();
                    $crawler = new Crawler($html);
    
                    // Extraer los enlaces únicos de las etiquetas <a class="link-div">
                    $links = $crawler->filter('div.mg-posts-sec.mg-posts-modul-6 div.mg-posts-sec-inner article div.col-12.col-md-6 div.mg-post-thumb.back-img.md a.link-div')->each(function (Crawler $node) {
                        try {
                            return trim($node->attr('href'));
                        } catch (\Exception $e) {
                            return null;
                        }
                    });
    
                    // Filtrar enlaces no válidos y eliminar duplicados
                    $links = array_filter($links);
                    $links = array_unique($links);
    
                    // Segundo scraping: Obtener los datos de cada enlace
                    $promises = [];
                    foreach ($links as $link) {
                        $promises[] = $client->getAsync($link);
                    }
    
                    $responses = Utils::settle($promises)->wait();
    
                    foreach ($responses as $index => $response) {
                        if ($response['state'] === 'fulfilled') {
                            try {
                                $html = (string) $response['value']->getBody();
                                $subCrawler = new Crawler($html);
    
                                // Extraer datos desde la página del enlace
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
                                ];
                            } catch (\Exception $e) {
                                $articles[] = [
                                    'title' => 'No title available',
                                    'author' => 'No author available',
                                    'date' => 'No date available',
                                    'image' => 'No image available',
                                    'description' => 'No description available',
                                ];
                            }
                        }
                    }
    
                    // Verificar si hay una página siguiente
                    $nextPage = null;
                    try {
                        $nextPage = $crawler->filter('a[rel="next"]')->attr('href');
                    } catch (\Exception $e) {
                        $nextPage = null;
                    }
                    $currentPage = $nextPage;
                } catch (\Exception $e) {
                    // Manejo de errores para la página principal
                    $currentPage = null;
                }
            } while ($currentPage);
            // Almacenar los artículos en caché
            return $articles;
        });
    
        return response()->json([
            'volleyball_news' => $articles,
        ]);
    }

    public function swimmingNews()
    {
        // Usar Cache::remember para almacenar los resultados en caché durante 30 días
        $articles = Cache::remember('swimming_news', now()->addDays(30), function () {
            $client = new Client([
                'verify' => false,
            ]);

            $response = $client->request('GET', 'https://cdndeportes.com.do/tag/natacion/');
            $html = (string) $response->getBody();
            $crawler = new Crawler($html);

            // Extraer los enlaces y subtítulos
            $linksAndSubtitles = $crawler->filter('div.row.justify-content-center div.col-md-6.tablet_full_width div.single_post.post__grid__layout__style__2 div.single_post_text')->each(function (Crawler $node) use ($client) {
                try {
                    $link = $node->filter('h4 a')->attr('href'); // Extraer el enlace

                    // Realizar el segundo scraping
                    $response = $client->request('GET', $link);
                    $html = (string) $response->getBody();
                    $subCrawler = new Crawler($html);

                    // Extraer datos desde la página del enlace
                    $title = $subCrawler->filter('div.elementor-widget-container h1.elementor-heading-title.elementor-size-default')->text();
                    $image = $subCrawler->filter('div.elementor-element.elementor-element-a663d17.elementor-widget.elementor-widget-theme-post-featured-image.elementor-widget-image div.elementor-widget-container img')->attr('src');
                    $author = $subCrawler->filter('div.elementor-author-box .elementor-author-box__text .elementor-author-box__name')->text();

                    // Ajustar selector para descripción
                    $description = $subCrawler->filter('div.elementor-element.elementor-element-0259b0e.elementor-widget.elementor-widget-theme-post-content .elementor-widget-container p')->each(function (Crawler $pNode) {
                        return trim($pNode->text());
                    });

                    // Ajustar selector para fecha
                    $date = $subCrawler->filter('div.elementor-widget-container .post-single .page_comments ul.inline li')->reduce(function (Crawler $node) {
                        return $node->text() !== '' && strpos($node->text(), ',') !== false;
                    })->text();

                    return [
                        'title' => $title,
                        'image' => $image,
                        'author' => $author,
                        'description' => implode("\n", $description),
                        'date' => $date,
                        'link' => trim($link),
                    ];
                } catch (\Exception $e) {
                    return null;
                }
            });

            // Filtrar resultados no válidos
            $linksAndSubtitles = array_filter($linksAndSubtitles);

            return $linksAndSubtitles;
        });

        return response()->json([
            'swimming_news' => $articles,
        ]);
    }
}