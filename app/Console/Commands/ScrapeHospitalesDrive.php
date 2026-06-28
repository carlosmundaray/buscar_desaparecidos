<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleDriveScraperService;

class ScrapeHospitalesDrive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scrape-hospitales-drive';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Raspa los PDFs del listado de ingresados en hospitales por sismo desde Google Drive';

    /**
     * Execute the console command.
     */
    public function handle(GoogleDriveScraperService $scraper)
    {
        $this->info("Buscando archivos PDF en la carpeta de Google Drive...");
        
        $ids = $scraper->getPdfFileIds();
        $count = count($ids);

        if ($count === 0) {
            $this->warn("No se encontraron IDs de archivos en la carpeta de Drive.");
            return 1;
        }

        $this->info("Se detectaron {$count} archivos. Iniciando procesamiento...");

        $totalImported = 0;
        $totalUpdated = 0;

        foreach ($ids as $index => $id) {
            $num = $index + 1;
            $this->line("\n[{$num}/{$count}] Procesando archivo ID: {$id}");

            $result = $scraper->scrapePdfFile($id, function($msg) {
                $this->line("  -> " . $msg);
            });

            if ($result['success']) {
                $totalImported += $result['imported'];
                $totalUpdated += $result['updated'];
                $this->info("  Completado: +{$result['imported']} nuevos, +{$result['updated']} actualizados.");
            } else {
                $this->error("  Fallo al procesar: " . ($result['error'] ?? 'Error desconocido'));
            }
        }

        $this->newLine();
        $this->info("========================================");
        $this->info(" RASPADO DE DRIVE FINALIZADO");
        $this->info(" - Nuevos importados: {$totalImported}");
        $this->info(" - Casos actualizados: {$totalUpdated}");
        $this->info("========================================");

        return 0;
    }
}
