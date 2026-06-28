<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Desaparecido;
use Illuminate\Support\Facades\DB;

class DeduplicateCases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deduplicate-cases {--dry-run : Ejecuta una simulación sin guardar cambios} {--force : Salta la confirmación y aplica los cambios}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Fusiona y limpia registros duplicados en la base de datos de desaparecidos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->info("=== MODO SIMULACIÓN: No se aplicarán cambios a la base de datos ===");
        }

        $this->info("Cargando todos los registros de la base de datos...");
        $all = Desaparecido::orderBy('id', 'asc')->get();
        $totalCount = $all->count();
        $this->info("Se cargaron {$totalCount} registros.");

        // ETAPA PREVIA: Limpiar nombres y cédulas sucias en los registros existentes
        $cleanedNamesCount = 0;
        $hospitalPrefixes = [
            'Hospital Universitario de Caracas',
            'Hospital Domingo Luciani (El Llanito)',
            'Hospital Domingo Luciani',
            'Hospital Perez Carreño',
            'Hospital Pérez Carreño',
            'Hopital Ricardo Baquero Gonzalez',
            'Hospital Ricardo Baquero Gonzalez',
            'Ricardo Baquero Gonzalez',
            'Baquero Gonzalez',
            'Hospital Vargas de Caracas',
            'Hospital Vargas',
            'Periférico de Catia',
            'Cruz Roja',
        ];

        foreach ($all as $item) {
            $name = $item->full_name;
            $hasHospitalPrefix = false;
            $firstPos = null;
            $matchedPrefix = null;

            foreach ($hospitalPrefixes as $prefix) {
                $pos = stripos($name, $prefix);
                if ($pos !== false) {
                    if ($firstPos === null || $pos < $firstPos) {
                        $firstPos = $pos;
                        $matchedPrefix = $prefix;
                    }
                }
            }

            if ($matchedPrefix !== null) {
                if ($firstPos === 0) {
                    $name = trim(substr($name, strlen($matchedPrefix)));
                } else {
                    $name = trim(substr($name, 0, $firstPos));
                }
                $hasHospitalPrefix = true;
            }

            if ($hasHospitalPrefix) {
                $name = ltrim($name, " -/\t");
                
                // Tratar de recuperar cédula y edad si están en el nombre antes de limpiarlo
                $recoveredCedula = null;
                $recoveredAge = null;
                $recoveredGender = null;

                // Si hay un bloque largo de números en el nombre restante (ej: OROZCO YUSBELIS 2584622129F)
                if (preg_match('/(\d{7,8})(\d{2})([MFmf])\b/i', $name, $matches)) {
                    $recoveredCedula = $matches[1];
                    $recoveredAge = intval($matches[2]);
                    $char = strtolower($matches[3]);
                    $recoveredGender = ($char === 'f') ? 'Femenino' : 'Masculino';
                    $name = trim(str_replace($matches[0], '', $name));
                } elseif (preg_match('/(\d{7,8})(\d{2})\b/', $name, $matches)) {
                    $recoveredCedula = $matches[1];
                    $recoveredAge = intval($matches[2]);
                    $name = trim(str_replace($matches[0], '', $name));
                } elseif (preg_match('/(\d{7,8})\b/', $name, $matches)) {
                    $recoveredCedula = $matches[1];
                    $name = trim(str_replace($matches[0], '', $name));
                }

                // Limpiar caracteres extraños
                $name = preg_replace('/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/', '', $name);
                $name = trim(preg_replace('/\s+/', ' ', $name));

                if (!empty($name) && $name !== $item->full_name) {
                    $oldName = $item->full_name;
                    $item->full_name = $name;
                    if ($recoveredCedula && empty($item->cedula)) {
                        $item->cedula = $recoveredCedula;
                    }
                    if ($recoveredAge && empty($item->age)) {
                        $item->age = $recoveredAge;
                    }
                    if ($recoveredGender && empty($item->gender)) {
                        $item->gender = $recoveredGender;
                    }

                    if (!$dryRun) {
                        $item->save();
                    }
                    $cleanedNamesCount++;
                    $this->line("  -> Limpiado: '{$oldName}' => '{$name}'" . ($recoveredCedula ? " (C.I. recuperada: {$recoveredCedula})" : ""));
                }
            }
        }

        if ($cleanedNamesCount > 0) {
            $this->info("Se corrigieron {$cleanedNamesCount} nombres/cédulas en la base de datos.");
        }

        // Recargar los registros actualizados si no fue una simulación
        if ($cleanedNamesCount > 0 && !$dryRun) {
            $all = Desaparecido::orderBy('id', 'asc')->get();
        }

        $groups = [];
        foreach ($all as $item) {
            $normName = $this->normalizeString($item->full_name);
            if (empty($normName)) {
                continue;
            }
            $groups[$normName][] = $item;
        }

        $duplicateGroupsCount = 0;
        $totalDuplicatesRecords = 0;
        $toDeleteIds = [];
        $updates = [];

        foreach ($groups as $normName => $items) {
            if (count($items) <= 1) {
                continue;
            }

            $duplicateGroupsCount++;
            $totalDuplicatesRecords += count($items);

            // Escoger el registro primario usando puntaje
            $primary = null;
            foreach ($items as $item) {
                if ($primary === null) {
                    $primary = $item;
                    continue;
                }

                $scoreCurrent = 0;
                $scorePrimary = 0;

                // Preferir localizado
                if ($item->status === 'found') $scoreCurrent += 10;
                if ($primary->status === 'found') $scorePrimary += 10;

                // Preferir reporte local
                if (strpos($item->code, 'HOSP-') === false && strpos($item->code, 'VT-') === false && strpos($item->code, 'BD-') === false) $scoreCurrent += 5;
                if (strpos($primary->code, 'HOSP-') === false && strpos($primary->code, 'VT-') === false && strpos($primary->code, 'BD-') === false) $scorePrimary += 5;

                // Preferir tener foto
                if (!empty($item->photo_path)) $scoreCurrent += 3;
                if (!empty($primary->photo_path)) $scorePrimary += 3;

                // Preferir descripcion mas larga
                $scoreCurrent += min(5, strlen($item->description ?? '') / 50);
                $scorePrimary += min(5, strlen($primary->description ?? '') / 50);

                if ($scoreCurrent > $scorePrimary) {
                    $primary = $item;
                }
            }

            // Fusionar datos
            $descriptions = [$primary->description ?? ''];
            $foundLocations = array_filter([$primary->found_location ?? '']);
            $lastSeenLocations = array_filter([$primary->last_seen_location ?? '']);

            $mergedData = [
                'status' => $primary->status,
                'found_at' => $primary->found_at,
                'photo_path' => $primary->photo_path,
                'photo_hash' => $primary->photo_hash,
                'cedula' => $primary->cedula,
                'age' => $primary->age,
                'gender' => $primary->gender,
            ];

            foreach ($items as $item) {
                if ($item->id === $primary->id) {
                    continue;
                }

                // Satus
                if ($item->status === 'found') {
                    $mergedData['status'] = 'found';
                    if (empty($mergedData['found_at']) && !empty($item->found_at)) {
                        $mergedData['found_at'] = $item->found_at;
                    }
                }

                // Photo
                if (empty($mergedData['photo_path']) && !empty($item->photo_path)) {
                    $mergedData['photo_path'] = $item->photo_path;
                    $mergedData['photo_hash'] = $item->photo_hash;
                }

                // Cedula
                if (empty($mergedData['cedula']) && !empty($item->cedula)) {
                    $mergedData['cedula'] = $item->cedula;
                }

                // Age
                if (empty($mergedData['age']) && !empty($item->age)) {
                    $mergedData['age'] = $item->age;
                }

                // Gender
                if (empty($mergedData['gender']) && !empty($item->gender)) {
                    $mergedData['gender'] = $item->gender;
                }

                // Desc
                $desc = trim($item->description ?? '');
                if (!empty($desc) && !in_array($desc, $descriptions)) {
                    $isSub = false;
                    foreach ($descriptions as $d) {
                        if (stripos($d, $desc) !== false) {
                            $isSub = true;
                            break;
                        }
                    }
                    if (!$isSub) {
                        $descriptions[] = $desc;
                    }
                }

                // Locations
                $fl = trim($item->found_location ?? '');
                if (!empty($fl) && !in_array($fl, $foundLocations)) {
                    $foundLocations[] = $fl;
                }

                $lsl = trim($item->last_seen_location ?? '');
                if (!empty($lsl) && !in_array($lsl, $lastSeenLocations)) {
                    $lastSeenLocations[] = $lsl;
                }

                // Mark for deletion
                $toDeleteIds[] = $item->id;
            }

            $mergedData['description'] = implode("\n---\n", array_filter($descriptions)) ?: null;
            $mergedData['found_location'] = implode(" / ", array_unique($foundLocations)) ?: null;
            $mergedData['last_seen_location'] = implode(" / ", array_unique($lastSeenLocations)) ?: null;

            $updates[$primary->id] = $mergedData;
        }

        $redundantCount = count($toDeleteIds);
        $this->info("Grupos de duplicados detectados: {$duplicateGroupsCount}");
        $this->info("Registros redundantes a eliminar: {$redundantCount}");

        if ($redundantCount === 0) {
            $this->info("La base de datos ya está limpia. No hay duplicados.");
            return 0;
        }

        if ($dryRun) {
            $this->info("Simulación terminada con éxito. No se realizaron cambios.");
            return 0;
        }

        if (!$this->option('force') && !$this->confirm("¿Deseas proceder con la fusión y limpieza de {$redundantCount} registros?")) {
            $this->warn("Operación cancelada por el usuario.");
            return 1;
        }

        $this->info("Aplicando cambios en la base de datos...");
        DB::beginTransaction();
        try {
            // Actualizar registros principales
            foreach ($updates as $id => $data) {
                Desaparecido::where('id', $id)->update($data);
            }

            // Eliminar duplicados en chunks
            $chunks = array_chunk($toDeleteIds, 100);
            foreach ($chunks as $chunk) {
                Desaparecido::whereIn('id', $chunk)->delete();
            }

            DB::commit();
            $this->info("¡Fusión y limpieza completadas con éxito!");

            $this->info("Optimizando base de datos SQLite con VACUUM...");
            DB::statement("VACUUM");
            $this->info("VACUUM completado con éxito.");

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Ocurrió un error al limpiar los duplicados: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Normaliza un string removiendo acentos, convirtiendo a minúsculas y limpiando espacios.
     */
    protected function normalizeString($string)
    {
        if (empty($string)) return '';
        $string = trim($string);
        $string = mb_strtolower($string, 'UTF-8');

        $utf8 = [
            '/[áàâãªä]/u'   =>   'a',
            '/[éèêë]/u'     =>   'e',
            '/[íìîï]/u'     =>   'i',
            '/[óòôõºö]/u'   =>   'o',
            '/[úùûü]/u'     =>   'u',
            '/[ç]/u'        =>   'c',
            '/[ñ]/u'        =>   'n',
        ];
        $string = preg_replace(array_keys($utf8), array_values($utf8), $string);
        $string = preg_replace('/\s+/', ' ', $string);
        return $string;
    }
}
