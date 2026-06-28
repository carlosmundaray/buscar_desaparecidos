<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Desaparecido;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportGoogleSheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-google-sheet {--force : Ejecuta sin solicitar confirmación}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Descarga e importa el Registro Maestro de Pacientes de Google Sheets al sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sheetUrl = 'https://docs.google.com/spreadsheets/d/1K479lFt0jKwEQh3s67mXtzsXIs7Rh-PS/export?format=csv&gid=1834136712';
        
        $this->info("Descargando Registro Maestro de Pacientes desde Google Sheets...");
        
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            ])->timeout(45)->get($sheetUrl);

            if ($response->failed()) {
                $this->error("Fallo al descargar el archivo de Google Sheets. Código HTTP: " . $response->status());
                return 1;
            }

            $csvData = $response->body();
            
            // Parsear CSV manualmente para evitar problemas de formato
            $rows = [];
            $tempFile = tmpfile();
            fwrite($tempFile, $csvData);
            fseek($tempFile, 0);
            
            while (($row = fgetcsv($tempFile)) !== false) {
                $rows[] = $row;
            }
            fclose($tempFile);
            
            $totalRows = count($rows);
            $this->info("Total filas detectadas: {$totalRows}");

            if ($totalRows < 4) {
                $this->warn("El archivo de Google Sheets no contiene suficientes filas.");
                return 1;
            }

            // Confirmación si no se pasa --force
            if (!$this->option('force') && !$this->confirm("¿Deseas proceder con la importación/actualización de los pacientes en el sistema?")) {
                $this->warn("Operación cancelada.");
                return 0;
            }

            $imported = 0;
            $updated = 0;
            $skipped = 0;

            $bar = $this->output->createProgressBar($totalRows - 3);
            $bar->start();

            // Los datos reales empiezan en la fila de índice 3
            for ($i = 3; $i < $totalRows; $i++) {
                $bar->advance();
                $row = $rows[$i];
                
                // Ignorar filas vacías o incompletas
                if (empty($row) || count($row) < 3 || empty(trim($row[2]))) {
                    $skipped++;
                    continue;
                }

                $index = trim($row[0]);
                $hospital = trim($row[1]);
                $fullName = trim($row[2]);
                $ageText = trim($row[3]);
                $col4 = trim($row[4] ?? ''); // Cédula / ID
                $col5 = trim($row[5] ?? ''); // Teléfono
                $address = trim($row[6] ?? '');
                $observations = trim($row[7] ?? '');

                // Omitir si es fila de cabecera repetida accidentalmente
                if (stripos($fullName, 'Nombres') !== false || stripos($fullName, 'Apellidos') !== false) {
                    $skipped++;
                    continue;
                }

                // Normalizar Nombre Completo
                $fullName = preg_replace('/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/u', '', $fullName);
                $fullName = trim(preg_replace('/\s+/', ' ', $fullName));
                if (strlen($fullName) < 3) {
                    $skipped++;
                    continue;
                }

                // Parsear edad
                $age = null;
                if (!empty($ageText) && ctype_digit($ageText)) {
                    $age = intval($ageText);
                }

                // Mapear Cédula y Teléfono de manera robusta
                $cedula = null;
                $phone = null;

                foreach ([$col4, $col5] as $val) {
                    if (empty($val)) continue;
                    $digitsOnly = preg_replace('/[^0-9]/', '', $val);
                    
                    if (preg_match('/^(04\d{9}|02\d{9}|\+58)/', $digitsOnly) || strlen($digitsOnly) >= 10) {
                        $phone = $val;
                    } elseif (ctype_digit($digitsOnly) && strlen($digitsOnly) >= 6 && strlen($digitsOnly) <= 9) {
                        $cedula = $digitsOnly;
                    }
                }

                // Fallbacks si no se pudieron clasificar
                if (empty($cedula) && !empty($col4) && ctype_digit(preg_replace('/[^0-9]/', '', $col4))) {
                    $cedula = preg_replace('/[^0-9]/', '', $col4);
                }
                if (empty($phone) && !empty($col5)) {
                    $phone = $col5;
                }

                // Verificar si contiene palabras clave de fallecido
                $status = 'found';
                if (preg_match('/fallec/i', $observations) || preg_match('/sin vida/i', $observations) || preg_match('/cad[aá]ver/i', $observations) || preg_match('/difunt/i', $observations) || preg_match('/occis/i', $observations)) {
                    $status = 'deceased';
                }

                // Verificar duplicado en la base de datos
                $existe = null;
                if (!empty($cedula)) {
                    $existe = Desaparecido::where('cedula', $cedula)->first();
                }
                if (!$existe) {
                    $existe = Desaparecido::where('full_name', 'like', $fullName)->first();
                }

                $hospitalLabel = $hospital ?: 'Centro de Salud (Sismo Venezuela)';
                $descText = "Ubicación: {$hospitalLabel}." . ($address ? " Procedencia: {$address}." : "") . ($observations ? " Obs: {$observations}." : "");

                if ($existe) {
                    // Si ya existe, se actualiza el estado a Localizado o Fallecido
                    $existingDesc = $existe->description ?? '';
                    $updateMark = $status === 'deceased' ? ' (Fallecido)' : '';
                    
                    // Solo concatenar la actualización si no está ya registrada
                    if (stripos($existingDesc, $hospitalLabel) === false) {
                        $newDesc = $existingDesc . "\n\n[ACTUALIZACIÓN SÍSMICA]: Encontrado ingresado en " . $hospitalLabel . $updateMark . " según Registro Maestro de Google Sheets. " . $descText;
                    } else {
                        $newDesc = $existingDesc;
                    }

                    $existe->update([
                        'status' => $status,
                        'found_at' => $existe->found_at ?: now(),
                        'found_location' => $hospitalLabel,
                        'age' => $existe->age ?: $age,
                        'cedula' => $existe->cedula ?: $cedula,
                        'description' => $newDesc,
                        'source_url' => 'https://docs.google.com/spreadsheets/d/1K479lFt0jKwEQh3s67mXtzsXIs7Rh-PS/htmlview',
                    ]);
                    $updated++;
                } else {
                    // Crear nuevo caso de localizado
                    Desaparecido::create([
                        'code' => 'HOSP-' . strtoupper(substr(md5($fullName . uniqid()), 0, 6)),
                        'full_name' => $fullName,
                        'cedula' => $cedula,
                        'age' => $age,
                        'status' => $status,
                        'found_at' => now(),
                        'found_location' => $hospitalLabel,
                        'last_seen_location' => $address ?: 'Venezuela (Sismo)',
                        'description' => $status === 'deceased'
                            ? "Registrado como ingresado en centro de salud tras sismo (Fallecido). {$descText}"
                            : "Registrado como ingresado en centro de salud tras sismo. {$descText}",
                        'reporter_phone' => $phone,
                        'source_url' => 'https://docs.google.com/spreadsheets/d/1K479lFt0jKwEQh3s67mXtzsXIs7Rh-PS/htmlview',
                    ]);
                    $imported++;
                }
            }

            $bar->finish();
            $this->newLine();
            
            $this->info("========================================");
            $this->info(" IMPORTACIÓN DESDE GOOGLE SHEETS COMPLETADA");
            $this->info(" - Nuevos importados: {$imported}");
            $this->info(" - Casos actualizados: {$updated}");
            $this->info(" - Registros omitidos: {$skipped}");
            $this->info("========================================");

            return 0;

        } catch (\Exception $e) {
            $this->error("Ocurrió un error al procesar el Google Sheet: " . $e->getMessage());
            Log::error("Error importando Google Sheet: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return 1;
        }
    }
}
