<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Calendar;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class ImportCalendarEvents extends Command
{
    protected $signature = 'calendar:import';
    protected $description = 'Import events from sdctickets.do into the calendars table';

    // El sitio mezcla abreviaturas de mes en español e ingles de forma inconsistente
    // entre eventos (ej. "Mar"/"Sep" valen para ambos, pero "Apr"/"Aug" solo en ingles).
    private $months = [
        'ene' => 1, 'jan' => 1,
        'feb' => 2,
        'mar' => 3,
        'abr' => 4, 'apr' => 4,
        'may' => 5,
        'jun' => 6,
        'jul' => 7,
        'ago' => 8, 'aug' => 8,
        'sep' => 9,
        'oct' => 10,
        'nov' => 11,
        'dic' => 12, 'dec' => 12,
    ];

    public function handle()
    {
        $this->info('Starting calendar import process...');

        $events = $this->scrapeEvents();

        if (!count($events)) {
            $this->warn('No events found.');
            return;
        }

        $this->info('Found ' . count($events) . ' events');

        $imported = 0;
        foreach ($events as $event) {
            try {
                if (empty($event['title']) || empty($event['date'])) {
                    continue;
                }

                $duplicate = Calendar::where('Title', $event['title'])
                    ->where('date', $event['date'])
                    ->exists();

                if ($duplicate) {
                    continue;
                }

                Calendar::create([
                    'Title' => $event['title'],
                    'date' => $event['date'],
                    'time' => $event['time'] ?: '00:00:00',
                    'place' => $event['place'] ?: 'Sin lugar',
                    'Description' => $event['description'],
                    'price' => $event['price'],
                    'image' => $event['image'],
                    'quantity' => 100,
                ]);

                $imported++;
                $this->info("Nuevo: {$event['title']}");
            } catch (\Exception $e) {
                $this->error("Error en {$event['title']}: " . $e->getMessage());
                Log::error('Error importing calendar event', [
                    'event' => $event,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Total imported: {$imported} events");
        $this->info('Calendar import completed successfully!');
    }

    private function scrapeEvents()
    {
        $events = [];

        try {
            $client = new Client(['verify' => false, 'timeout' => 20]);
            $response = $client->get('https://www.sdctickets.do/');
            $html = (string) $response->getBody();
            $crawler = new Crawler($html);

            $crawler->filter('.event-list .inners')->each(function (Crawler $node) use (&$events) {
                try {
                    $infoNode = $node->filter('.inner-info');
                    $detailNode = $node->filter('.inner-detail');

                    $title = $this->extractCleanTitle($infoNode);
                    $rawDate = $infoNode->filter('h4 em')->count() ? trim($infoNode->filter('h4 em')->text()) : '';

                    $place = $detailNode->filter('p.event_place')->count()
                        ? trim(str_replace(['Lugar:', "\n", "\r"], '', $detailNode->filter('p.event_place')->text()))
                        : '';

                    $hourText = $detailNode->filter('p')->eq(2)->count() ? $detailNode->filter('p')->eq(2)->text() : '';
                    $priceText = $detailNode->filter('p')->eq(3)->count() ? $detailNode->filter('p')->eq(3)->text() : '';

                    $image = $node->filter('img')->count() ? $node->filter('img')->attr('loader-src') : null;

                    $events[] = [
                        'title' => $title,
                        'date' => $this->parseDate($rawDate),
                        'time' => $this->parseHour($hourText),
                        'place' => $place,
                        'description' => null,
                        'price' => $this->parsePrice($priceText),
                        'image' => $image,
                    ];
                } catch (\Exception $e) {
                    Log::error('Error scraping calendar event node', ['error' => $e->getMessage()]);
                }
            });
        } catch (\Exception $e) {
            Log::critical('Error scraping sdctickets.do: ' . $e->getMessage());
        }

        return $events;
    }

    // El <span> del titulo contiene el texto del titulo y, anidado, un <em> con la fecha.
    // ->text() trae ambos concatenados, asi que se extrae solo el nodo de texto directo.
    private function extractCleanTitle(Crawler $infoNode)
    {
        if (!$infoNode->filter('h4 span')->count()) {
            return '';
        }

        $span = $infoNode->filter('h4 span')->getNode(0);
        $title = '';
        foreach ($span->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $title .= $child->textContent;
            }
        }

        return trim(preg_replace('/\s+/', ' ', $title));
    }

    // Acepta "6 jul., 2025" y "15 Mar, 2026" (el punto tras el mes es opcional)
    private function parseDate($dateStr)
    {
        if (!preg_match('/(\d{1,2})\s+([a-záéíóúñ]+)\.?,?\s*(\d{4})/i', $dateStr, $match)) {
            return null;
        }

        $day = str_pad($match[1], 2, '0', STR_PAD_LEFT);
        $monthAbbr = mb_strtolower(mb_substr($match[2], 0, 3));
        $month = $this->months[$monthAbbr] ?? null;
        $year = $match[3];

        if (!$month) {
            return null;
        }

        return sprintf('%s-%s-%s', $year, str_pad($month, 2, '0', STR_PAD_LEFT), $day);
    }

    // "07:00 AM" -> "07:00"
    private function parseHour($hourStr)
    {
        $hourStr = trim(preg_replace('/^(Hora):\s*/i', '', $hourStr));
        if (!$hourStr || !preg_match('/(\d{1,2}):(\d{2})\s*(AM|PM)?/i', $hourStr, $match)) {
            return '';
        }

        $hour = (int) $match[1];
        $minutes = $match[2];
        $ampm = strtoupper($match[3] ?? '');

        if ($ampm === 'PM' && $hour !== 12) {
            $hour += 12;
        }
        if ($ampm === 'AM' && $hour === 12) {
            $hour = 0;
        }

        return sprintf('%02d:%s', $hour, $minutes);
    }

    // "Inicial: USD$ 300.00" / "Desde: RD$ 1,850.00" -> 300.00 / 1850.00
    private function parsePrice($priceStr)
    {
        if (!$priceStr) {
            return 0;
        }

        $digits = preg_replace('/[^\d.,]/', '', $priceStr);
        $digits = str_replace(',', '', $digits);

        return $digits !== '' ? (float) $digits : 0;
    }
}
