<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GoogleDriveScraperService;
use App\Models\Desaparecido;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminScrapeDriveController extends Controller
{
    /**
     * Corre el scraper de Drive y reporta el progreso mediante Server-Sent Events (SSE).
     */
    public function run(GoogleDriveScraperService $scraper)
    {
        $response = new StreamedResponse(function () use ($scraper) {
            // Eliminar límites de tiempo para PHP
            set_time_limit(0);

            $this->sendSSEMessage([
                'status' => 'info',
                'message' => '[CONECTANDO] Extrayendo lista de archivos desde la carpeta de Google Drive...'
            ]);

            $ids = $scraper->getPdfFileIds();
            $totalFiles = count($ids);

            if ($totalFiles === 0) {
                $this->sendSSEMessage([
                    'status' => 'completed',
                    'total_imported' => 0,
                    'total_updated' => 0,
                    'stats' => $this->getSystemStats(),
                    'message' => 'No se encontraron archivos en la carpeta de Google Drive.'
                ]);
                return;
            }

            $this->sendSSEMessage([
                'status' => 'info',
                'message' => "[INFO] Se detectaron {$totalFiles} archivos PDF en Drive. Iniciando descargas..."
            ]);

            $totalImported = 0;
            $totalUpdated = 0;

            foreach ($ids as $index => $id) {
                $num = $index + 1;
                
                $this->sendSSEMessage([
                    'status' => 'info',
                    'message' => "[{$num}/{$totalFiles}] Procesando archivo ID: {$id}..."
                ]);

                // Ejecutar procesamiento con callback de logs
                $result = $scraper->scrapePdfFile($id, function($logMsg) {
                    $this->sendSSEMessage([
                        'status' => 'info',
                        'message' => "  -> " . $logMsg
                    ]);
                });

                if ($result['success']) {
                    $totalImported += $result['imported'];
                    $totalUpdated += $result['updated'];

                    $this->sendSSEMessage([
                        'status' => 'progress',
                        'current' => $num,
                        'total' => $totalFiles,
                        'imported' => $result['imported'],
                        'updated' => $result['updated'],
                        'total_imported' => $totalImported,
                        'total_updated' => $totalUpdated,
                        'message' => "[COMPLETADO] Archivo {$num} de {$totalFiles} procesado. +{$result['imported']} nuevos, +{$result['updated']} actualizados."
                    ]);
                } else {
                    $this->sendSSEMessage([
                        'status' => 'error',
                        'message' => "[FALLO] Error en archivo ID: {$id} - " . ($result['error'] ?? 'Error desconocido')
                    ]);
                }

                // Pausa de 0.5 seg
                usleep(500000);
            }

            $this->sendSSEMessage([
                'status' => 'completed',
                'total_imported' => $totalImported,
                'total_updated' => $totalUpdated,
                'stats' => $this->getSystemStats(),
                'message' => "Raspado de Google Drive finalizado. Se importaron {$totalImported} nuevos localizados en hospital y se actualizaron {$totalUpdated} casos."
            ]);
        });

        // Configurar cabeceras de Server-Sent Events
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    /**
     * Envía un mensaje SSE formateado en JSON.
     */
    private function sendSSEMessage(array $data)
    {
        echo "data: " . json_encode($data) . "\n\n";
        ob_flush();
        flush();
    }

    /**
     * Obtiene las estadísticas generales de desaparecidos.
     */
    private function getSystemStats(): array
    {
        return [
            'reported' => Desaparecido::count(),
            'missing' => Desaparecido::where('status', 'missing')->count(),
            'found' => Desaparecido::where('status', 'found')->count(),
            'deceased' => Desaparecido::where('status', 'deceased')->count(),
        ];
    }
}
