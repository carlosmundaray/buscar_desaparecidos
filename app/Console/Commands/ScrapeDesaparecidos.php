<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\ScraperService;

class ScrapeDesaparecidos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scrape-desaparecidos {--pages=3 : Número de páginas a raspar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Raspa el sitio buscardesaparecidos.com para llenar e ir actualizando la base de datos local';

    /**
     * Execute the console command.
     */
    public function handle(ScraperService $scraper)
    {
        $pages = (int) $this->option('pages');
        $this->info("Iniciando raspado de datos para las primeras {$pages} páginas...");
        
        $totalImported = 0;
        $totalUpdated = 0;

        for ($page = 1; $page <= $pages; $page++) {
            $this->comment("Procesando página {$page}...");
            
            // Source 1
            $this->comment("  - Raspando BuscarDesaparecidos...");
            $result1 = $scraper->scrapePage($page);
            if ($result1['success']) {
                $imported1 = $result1['imported'];
                $updated1 = $result1['updated'];
                $this->info("    + BuscarDesaparecidos: Importados {$imported1}, Actualizados {$updated1}");
                $totalImported += $imported1;
                $totalUpdated += $updated1;
            } else {
                $this->error("    x Error BuscarDesaparecidos: {$result1['error']}");
            }

            // Source 2
            $this->comment("  - Raspando VenezuelaTeBusca...");
            $result2 = $scraper->scrapeVenezuelaTeBuscaPage($page);
            if ($result2['success']) {
                $imported2 = $result2['imported'];
                $updated2 = $result2['updated'];
                $this->info("    + VenezuelaTeBusca: Importados {$imported2}, Actualizados {$updated2}");
                $totalImported += $imported2;
                $totalUpdated += $updated2;
            } else {
                $this->error("    x Error VenezuelaTeBusca: {$result2['error']}");
            }

            // Sleep brief time to be polite to the server
            usleep(500000); // 0.5 seconds
        }

        $this->info("Proceso completado. Total Importados: {$totalImported}, Total Actualizados: {$totalUpdated}");
        return Command::SUCCESS;
    }
}
