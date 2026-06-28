<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\ScraperService;
use App\Models\Desaparecido;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ScrapeController extends Controller
{
    /**
     * Ejecuta el scraper y envía el progreso en tiempo real usando Server-Sent Events (SSE).
     */
    public function run(Request $request, ScraperService $scraper)
    {
        $pages = min((int) $request->input('pages', 5), 50); // Limitar a máximo 50 páginas por petición web
        
        $response = new StreamedResponse(function () use ($pages, $scraper) {
            $totalImported = 0;
            $totalUpdated = 0;

            // Desactivar el límite de tiempo de ejecución de PHP para esta petición
            set_time_limit(0);

            for ($page = 1; $page <= $pages; $page++) {
                // 1. Scraping buscardesaparecidos.com
                $result1 = $scraper->scrapePage($page);
                
                // 2. Scraping venezuelatebusca.com
                $result2 = $scraper->scrapeVenezuelaTeBuscaPage($page);

                $imported1 = $result1['success'] ? $result1['imported'] : 0;
                $updated1 = $result1['success'] ? $result1['updated'] : 0;
                
                $imported2 = $result2['success'] ? $result2['imported'] : 0;
                $updated2 = $result2['success'] ? $result2['updated'] : 0;
                
                $imported = $imported1 + $imported2;
                $updated = $updated1 + $updated2;
                
                $totalImported += $imported;
                $totalUpdated += $updated;

                if ($result1['success'] || $result2['success']) {
                    $msg = "Página {$page} de {$pages} procesada.\n";
                    if ($result1['success']) {
                        $msg .= " - BuscarDesaparecidos: +{$imported1} nuevos, +{$updated1} act.\n";
                    } else {
                        $msg .= " - BuscarDesaparecidos: Fallido ({$result1['error']})\n";
                    }
                    if ($result2['success']) {
                        $msg .= " - VenezuelaTeBusca: +{$imported2} nuevos, +{$updated2} act.";
                    } else {
                        $msg .= " - VenezuelaTeBusca: Fallido ({$result2['error']})";
                    }

                    $data = [
                        'status' => 'progress',
                        'page' => $page,
                        'total_pages' => $pages,
                        'imported' => $imported,
                        'updated' => $updated,
                        'total_imported' => $totalImported,
                        'total_updated' => $totalUpdated,
                        'message' => $msg
                    ];

                    $this->sendSSEMessage($data);
                } else {
                    $data = [
                        'status' => 'error',
                        'page' => $page,
                        'message' => "Error en página {$page}: BD: " . ($result1['error'] ?? 'Ok') . " | VTB: " . ($result2['error'] ?? 'Ok')
                    ];
                    $this->sendSSEMessage($data);
                }

                // Esperar 0.3 segundos para no saturar al servidor remoto
                usleep(300000);
            }

            // Estadísticas finales actualizadas
            $stats = [
                'reported' => Desaparecido::count(),
                'missing' => Desaparecido::where('status', 'missing')->count(),
                'found' => Desaparecido::where('status', 'found')->count(),
            ];

            // Enviar mensaje de finalización
            $this->sendSSEMessage([
                'status' => 'completed',
                'total_imported' => $totalImported,
                'total_updated' => $totalUpdated,
                'stats' => $stats,
                'message' => "Raspado completado. Se importaron {$totalImported} nuevos casos y se actualizaron {$totalUpdated}."
            ]);

        });

        // Configurar cabeceras de Server-Sent Events
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no'); // Evita buffering de Nginx o Apache

        return $response;
    }

    /**
     * Helper para formatear y enviar un mensaje SSE.
     */
    private function sendSSEMessage(array $data)
    {
        echo "data: " . json_encode($data) . "\n\n";
        ob_flush();
        flush();
    }
}
