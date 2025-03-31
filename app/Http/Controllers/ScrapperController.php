<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Promise\Utils;
use Illuminate\Support\Facades\Cache;

class ScrapperController extends Controller
{

    // public function baseballNews()
    // {
    //     $client = new Client([
    //         'verify' => false,
    //     ]);
    
    //     // Primer scraping: Obtener solo los enlaces
    //     $response = $client->request('GET', 'https://lidom.com/');
    //     $html = (string) $response->getBody();
    
    //     $crawler = new Crawler($html);
    
    //     $links = $crawler->filter('.entry-box a.cover-link')->each(function (Crawler $node) {
    //         try {
    //             return $node->attr('href'); // Extraer solo el enlace
    //         } catch (\Exception $e) {
    //             return null;
    //         }
    //     });
    
    //     // Filtrar enlaces no válidos
    //     $links = array_filter($links);
    
    //     // Segundo scraping: Obtener los datos de cada enlace
    //     $promises = [];
    //     foreach ($links as $link) {
    //         $promises[] = $client->getAsync($link);
    //     }
    
    //     $responses = Utils::settle($promises)->wait();
    
    //     $articles = [];
    //     foreach ($responses as $index => $response) {
    //         if ($response['state'] === 'fulfilled') {
    //             try {
    //                 $html = (string) $response['value']->getBody();
    //                 $subCrawler = new Crawler($html);
    
    //                 // Extraer datos desde la página del enlace
    //                 $title = $subCrawler->filter('section.nota-top-part h1')->text();
    //                 $subtitle = $subCrawler->filter('section.nota-top-part h2')->text();
    
    //                 $author = $subCrawler->filter('div.autor div.extra-holder p')->text();
    //                 $date = $subCrawler->filter('div.autor div.extra-holder time')->text();
    
    //                 $image = $subCrawler->filter('div.preview-images figure img')->attr('src');
    
    //                 // Filtrar y extraer la descripción ignorando elementos no deseados
    //                 $description = $subCrawler->filter('div.article-body')->each(function (Crawler $node) {
    //                     // Eliminar elementos no deseados
    //                     $node->filter('div.article-audio-container, amp-ad, amp, amp-youtube')->each(function (Crawler $childNode) {
    //                         foreach ($childNode as $child) {
    //                             $child->parentNode->removeChild($child);
    //                         }
    //                     });
    
    //                     // Extraer solo el texto de las etiquetas <p>
    //                     return $node->filter('p')->each(function (Crawler $pNode) {
    //                         return $pNode->text();
    //                     });
    //                 });
    
    //                 $articles[] = [
    //                     'title' => $title,
    //                     'subtitle' => $subtitle,
    //                     'author' => $author,
    //                     'date' => $date,
    //                     'image' => $image,
    //                     'description' => implode("\n", $description[0] ?? []),
    //                     'link' => $links[$index], // Usar el enlace original
    //                 ];
    //             } catch (\Exception $e) {
    //                 $articles[] = [
    //                     'title' => 'No title available',
    //                     'subtitle' => 'No subtitle available',
    //                     'author' => 'No author available',
    //                     'date' => 'No date available',
    //                     'image' => 'No image available',
    //                     'description' => 'No description available',
    //                     'link' => $links[$index], // Usar el enlace original
    //                 ];
    //             }
    //         } else {
    //             $articles[] = [
    //                 'title' => 'No title available',
    //                 'subtitle' => 'No subtitle available',
    //                 'author' => 'No author available',
    //                 'date' => 'No date available',
    //                 'image' => 'No image available',
    //                 'description' => 'No description available',
    //                 'link' => $links[$index], // Usar el enlace original
    //             ];
    //         }
    //     }
    
    //     return response()->json([
    //         'baseball_news' => $articles,
    //     ]);
    // }

    // public function futbolNews()
    // {
    //     $client = new Client([
    //         'verify' => false,
    //     ]);

    //     // Primer scraping: Obtener solo los enlaces
    //     $response = $client->request('GET', 'https://www.fedofutbol.do/all-posts/');
    //     $html = (string) $response->getBody();

    //     $crawler = new Crawler($html);

    //     $links = $crawler->filter('.posts_container article .post_featured.with_thumb.hover_simple a')->each(function (Crawler $node) {
    //         try {
    //             return $node->attr('href'); // Extraer solo el enlace
    //         } catch (\Exception $e) {
    //             return null;
    //         }
    //     });

    //     // Filtrar enlaces no válidos
    //     $links = array_filter($links);

    //     // Segundo scraping: Obtener los datos de cada enlace
    //     $promises = [];
    //     foreach ($links as $link) {
    //         $promises[] = $client->getAsync($link);
    //     }

    //     $responses = Utils::settle($promises)->wait();

    //     $articles = [];
    //     foreach ($responses as $response) {
    //         if ($response['state'] === 'fulfilled') {
    //             try {
    //                 $html = (string) $response['value']->getBody();
    //                 $subCrawler = new Crawler($html);

    //                 // Extraer datos desde la página del enlace
    //                 $title = $subCrawler->filter('.post_title.entry-title')->text();
    //                 $image = $subCrawler->filter('.post_featured img')->attr('src');
    //                 $date = $subCrawler->filter('.post_meta_item.post_date')->text();
    //                 $subtitle = $subCrawler->filter('.post_content_inner')->text();
    //                 $author = $subCrawler->filter('.post_meta_item.post_categories')->text();
                   
    //                 $description = $subCrawler->filter('.content .post_content.post_content_single.entry-content p')->each(function (Crawler $pNode) {
    //                     return $pNode->text();
    //                 });

    //                 $articles[] = [
    //                     'title' => $title,
    //                     'image' => $image,
    //                     'date' => $date,
    //                     'subtitle' => $subtitle,
    //                     'description' => implode("\n", $description),
    //                     'link' => $link,
    //                     'author' => $author,
    //                 ];
    //             } catch (\Exception $e) {
    //                 $articles[] = [
    //                     'title' => 'No title available',
    //                     'image' => 'No image available',
    //                     'date' => 'No date available',
    //                     'subtitle' => 'No subtitle available',
    //                     'description' => 'No description available',
    //                     'author' => 'No author available',	
    //                     'link' => $link,
    //                 ];
    //             }
    //         } else {
    //             $articles[] = [
    //                 'title' => 'No title available',
    //                 'image' => 'No image available',
    //                 'date' => 'No date available',
    //                 'subtitle' => 'No subtitle available',
    //                 'description' => 'No description available',
    //                 'link' => 'No link available',
    //                 'author' => 'No author available',	
    //             ];
    //         }
    //     }

    //     return response()->json([
    //         'futbol_news' => $articles,
    //     ]);
    // }


    // public function basketballNews()
    // {
    //     $client = new Client([
    //         'verify' => false,
    //     ]);
    
    //     // Primer scraping: Obtener solo los enlaces
    //     $response = $client->request('GET', 'https://fedombal.org/seccion/noticia/');
    //     $html = (string) $response->getBody();
    
    //     $crawler = new Crawler($html);
    
    //     // Ajustar el selector para evitar duplicados
    //     $links = $crawler->filter('div.container div.row div.col-md-4.col-xs-12 div.seccion-item-box a:first-of-type')->each(function (Crawler $node) {
    //         try {
    //             return $node->attr('href'); // Extraer solo el enlace
    //         } catch (\Exception $e) {
    //             return null;
    //         }
    //     });
    
    //     // Eliminar duplicados en los enlaces
    //     $links = array_unique($links);
    
    //     // Segundo scraping: Obtener los datos de cada enlace
    //     $promises = [];
    //     foreach ($links as $link) {
    //         $promises[] = $client->getAsync($link);
    //     }
    
    //     $responses = Utils::settle($promises)->wait();
    
    //     $articles = [];
    //     foreach ($responses as $index => $response) {
    //         if ($response['state'] === 'fulfilled') {
    //             try {
    //                 $html = (string) $response['value']->getBody();
    //                 $subCrawler = new Crawler($html);
    
    //                 // Extraer datos desde la página del enlace
    //                 $title = $subCrawler->filter('div.col-md-12.col-xs-12 h1.single-title')->text();
    //                 $author = $subCrawler->filter('div.single-follow ul.nota-detalles li')->eq(1)->text();
    //                 $date = $subCrawler->filter('div.single-follow ul.nota-detalles li')->eq(2)->text();
    //                 $image = $subCrawler->filter('div.col-md-8.col-xs-12 div.white-box figure.nota-img amp-img')->attr('src');
    //                 $description = $subCrawler->filter('div.col-md-8.col-xs-12 div.white-box div.nota p')->each(function (Crawler $pNode) {
    //                     return $pNode->text();
    //                 });
    
    //                 $articles[] = [
    //                     'title' => $title,
    //                     'author' => trim(str_replace('Por:', '', $author)), // Limpiar el texto del autor
    //                     'date' => $date,
    //                     'image' => $image,
    //                     'description' => implode("\n", $description),
    //                     'link' => $links[$index], // Usar el enlace original
    //                 ];
    //             } catch (\Exception $e) {
    //                 $articles[] = [
    //                     'title' => 'No title available',
    //                     'author' => 'No author available',
    //                     'date' => 'No date available',
    //                     'image' => 'No image available',
    //                     'description' => 'No description available',
    //                     'link' => $links[$index], // Usar el enlace original
    //                 ];
    //             }
    //         }
    //     }
    
    //     return response()->json([
    //         'basketball_news' => $articles,
    //     ]);
    // }

    //??las funciones almacenadas en el chache por 2 dias, podemos manipular el tiempo

    public function baseballNews()
    {
        // Usar Cache::remember para almacenar los resultados en caché
        $articles = \Cache::remember('baseball_news', now()->addDays(2), function () {
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
                        $subtitle = $subCrawler->filter('section.nota-top-part h2')->text();
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
                            'subtitle' => $subtitle,
                            'author' => $author,
                            'date' => $date,
                            'image' => $image,
                            'description' => implode("\n", $description[0] ?? []),
                            'link' => $links[$index], // Usar el enlace original
                        ];
                    } catch (\Exception $e) {
                        $articles[] = [
                            'title' => 'No title available',
                            'subtitle' => 'No subtitle available',
                            'author' => 'No author available',
                            'date' => 'No date available',
                            'image' => 'No image available',
                            'description' => 'No description available',
                            'link' => $links[$index], // Usar el enlace original
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
        // Usar Cache::remember para almacenar los resultados en caché
        $articles = \Cache::remember('futbol_news', now()->addDays(2), function () {
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
                        $subtitle = $subCrawler->filter('.post_content_inner')->text();
                        $author = $subCrawler->filter('.post_meta_item.post_categories')->text();
    
                        $description = $subCrawler->filter('.content .post_content.post_content_single.entry-content p')->each(function (Crawler $pNode) {
                            return $pNode->text();
                        });
    
                        $articles[] = [
                            'title' => $title,
                            'image' => $image,
                            'date' => $date,
                            'subtitle' => $subtitle,
                            'description' => implode("\n", $description),
                            'link' => $links[$index], // Usar el enlace original
                            'author' => $author,
                        ];
                    } catch (\Exception $e) {
                        $articles[] = [
                            'title' => 'No title available',
                            'image' => 'No image available',
                            'date' => 'No date available',
                            'subtitle' => 'No subtitle available',
                            'description' => 'No description available',
                            'author' => 'No author available',
                            'link' => $links[$index], // Usar el enlace original
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
                        'author' => trim(str_replace('Por:', '', $author)), // Limpiar el texto del autor
                        'date' => $date,
                        'image' => $image,
                        'description' => implode("\n", $description),
                        'link' => $links[$index], // Usar el enlace original
                    ];
                } catch (\Exception $e) {
                    $articles[] = [
                        'title' => 'No title available',
                        'author' => 'No author available',
                        'date' => 'No date available',
                        'image' => 'No image available',
                        'description' => 'No description available',
                        'link' => $links[$index], // Usar el enlace original
                    ];
                }
            }
        }
    
        return response()->json([
            'basketball_news' => $articles,
        ]);
    }

   
    public function volleyballNews()
    {
        // Usar Cache::remember para almacenar los resultados en caché durante 2 días
        $articles = \Cache::remember('volleyball_news', now()->addDays(2), function () {
            $client = new Client([
                'verify' => false,
            ]);
    
            $baseUrl = 'https://voleiboldominicano.com/author/admin/';
            $currentPage = $baseUrl;
            $articles = [];
    
            do {
                try {
                    // Realizar la solicitud HTTP
                    $response = $client->request('GET', $currentPage);
                    $html = (string) $response->getBody();
                    $crawler = new Crawler($html);
    
                    // Extraer los enlaces de las noticias
                    $links = $crawler->filter('SELECTOR_DE_LOS_ENLACES')->each(function (Crawler $node) {
                        try {
                            return $node->attr('href'); // Extraer solo el enlace
                        } catch (\Exception $e) {
                            return null;
                        }
                    });
    
                    // Filtrar enlaces no válidos
                    $links = array_filter($links);
    
                    // Scraping de cada noticia
                    foreach ($links as $link) {
                        try {
                            $response = $client->request('GET', $link);
                            $html = (string) $response->getBody();
                            $subCrawler = new Crawler($html);
    
                            // Extraer datos desde la página del enlace
                            $title = $subCrawler->filter('SELECTOR_DEL_TITULO')->text();
                            $author = $subCrawler->filter('SELECTOR_DEL_AUTOR')->text();
                            $date = $subCrawler->filter('SELECTOR_DE_LA_FECHA')->text();
                            $image = $subCrawler->filter('SELECTOR_DE_LA_IMAGEN')->attr('src');
                            $description = $subCrawler->filter('SELECTOR_DE_LA_DESCRIPCION')->each(function (Crawler $pNode) {
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
                            // Manejo de errores para noticias individuales
                            $articles[] = [
                                'title' => 'No title available',
                                'author' => 'No author available',
                                'date' => 'No date available',
                                'image' => 'No image available',
                                'description' => 'No description available',
                                'link' => $link,
                            ];
                        }
                    }
    
                    // Verificar si hay una página siguiente
                    $nextPage = $crawler->filter('a[rel="next"]')->attr('href') ?? null;
                    $currentPage = $nextPage;
                } catch (\Exception $e) {
                    // Manejo de errores para la página principal
                    $currentPage = null;
                }
            } while ($currentPage);
    
            return $articles;
        });
    
        return response()->json([
            'volleyball_news' => $articles,
        ]);
    }


    public function swimmingNews()
    {
        // Usar Cache::remember para almacenar los resultados en caché durante 2 días
        $articles = \Cache::remember('swimming_news', now()->addDays(2), function () {
            $client = new Client([
                'verify' => false,
            ]);

            // Aquí debes agregar la URL de la página de noticias de natación
            $response = $client->request('GET', 'URL_DE_NOTICIAS_DE_NATACION');
            $html = (string) $response->getBody();

            $crawler = new Crawler($html);

            // Extraer los enlaces de las noticias
            $links = $crawler->filter('SELECTOR_DE_LOS_ENLACES')->each(function (Crawler $node) {
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
                        $title = $subCrawler->filter('SELECTOR_DEL_TITULO')->text();
                        $author = $subCrawler->filter('SELECTOR_DEL_AUTOR')->text();
                        $date = $subCrawler->filter('SELECTOR_DE_LA_FECHA')->text();
                        $image = $subCrawler->filter('SELECTOR_DE_LA_IMAGEN')->attr('src');
                        $description = $subCrawler->filter('SELECTOR_DE_LA_DESCRIPCION')->each(function (Crawler $pNode) {
                            return $pNode->text();
                        });

                        $articles[] = [
                            'title' => $title,
                            'author' => $author,
                            'date' => $date,
                            'image' => $image,
                            'description' => implode("\n", $description),
                            'link' => $links[$index], // Usar el enlace original
                        ];
                    } catch (\Exception $e) {
                        $articles[] = [
                            'title' => 'No title available',
                            'author' => 'No author available',
                            'date' => 'No date available',
                            'image' => 'No image available',
                            'description' => 'No description available',
                            'link' => $links[$index], // Usar el enlace original
                        ];
                    }
                }
            }

            return $articles;
        });

        return response()->json([
            'swimming_news' => $articles,
        ]);
    }
}