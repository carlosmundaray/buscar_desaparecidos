<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Desaparecido;
use App\Services\ImageHashService;

class GenerateHashes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:generate-hashes {--force : Force recalculation of all hashes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera los hashes perceptuales (dHash) para las imágenes de personas desaparecidas en la base de datos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');

        $query = Desaparecido::whereNotNull('photo_path')
            ->where('photo_path', '!=', '');

        if (!$force) {
            $query->whereNull('photo_hash');
        }

        $records = $query->get();
        $total = $records->count();

        if ($total === 0) {
            $this->info('No hay imágenes pendientes para generar hash.');
            return 0;
        }

        $this->info("Generando hashes perceptuales para {$total} registros...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($records as $record) {
            $hash = ImageHashService::hash($record->photo_path);

            if ($hash) {
                $record->update(['photo_hash' => $hash]);
                $success++;
            } else {
                $failed++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Proceso completado.");
        $this->info("Hashes generados exitosamente: {$success}");
        if ($failed > 0) {
            $this->warn("Imágenes que fallaron o no pudieron descargarse: {$failed}");
        }

        return 0;
    }
}
