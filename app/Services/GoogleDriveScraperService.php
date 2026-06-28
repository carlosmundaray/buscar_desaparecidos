<?php

namespace App\Services;

use App\Models\Desaparecido;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Parser;

class GoogleDriveScraperService
{
    protected $folderUrl = 'https://drive.google.com/drive/folders/1o36ifaRz45kAs5rKzci49aD0mP5JB_YI';
    protected $pdfParser;

    public function __construct()
    {
        $this->pdfParser = new Parser();
    }

    /**
     * Extrae recursivamente los IDs de todos los archivos válidos (PDFs y Google Docs)
     * en la carpeta de Drive y sus subcarpetas.
     */
    public function getPdfFileIds(): array
    {
        // Extraer el ID de la carpeta raíz
        $rootId = '1o36ifaRz45kAs5rKzci49aD0mP5JB_YI';
        
        $queue = [$rootId];
        $visited = [];
        $fileIds = [];

        Log::info("Iniciando escaneo recursivo de Google Drive desde la raíz: {$rootId}");

        while (!empty($queue)) {
            $folderId = array_shift($queue);
            if (isset($visited[$folderId])) {
                continue;
            }
            $visited[$folderId] = true;

            $items = $this->fetchFolderItems($folderId);
            Log::info("Escaneada carpeta {$folderId}. Encontrados " . count($items) . " elementos.");

            foreach ($items as $id => $item) {
                if ($item['type'] === 'folder') {
                    $queue[] = $id;
                } else if ($item['type'] === 'pdf' || $item['type'] === 'document') {
                    $fileIds[] = $id;
                }
            }
            // Pausa pequeña para evitar bloqueos
            usleep(150000);
        }

        $uniqueFileIds = array_unique($fileIds);
        Log::info("Escaneo recursivo finalizado. Total archivos a procesar: " . count($uniqueFileIds));

        return $uniqueFileIds;
    }

    /**
     * Obtiene los elementos de una carpeta de Google Drive específica por ID.
     */
    protected function fetchFolderItems(string $folderId): array
    {
        $url = "https://drive.google.com/drive/folders/" . $folderId;

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            ])->timeout(30)->get($url);

            if ($response->failed()) {
                Log::error("Error cargando carpeta de Drive: HTTP " . $response->status() . " para ID " . $folderId);
                return [];
            }

            $html = $response->body();
            
            // Buscar posibles strings de IDs de 33 a 44 caracteres
            preg_match_all('/"([a-zA-Z0-9_-]{33,44})"/', $html, $matches);
            if (empty($matches[1])) {
                return [];
            }

            $strings = array_unique($matches[1]);
            $items = [];

            foreach ($strings as $str) {
                $escaped = preg_quote($str, '/');
                // Intentar emparejar el patrón JSON que incluye el nombre del archivo
                if (preg_match('/(?:\\\x22|")' . $escaped . '(?:\\\x22|")\s*,\s*(?:\\\x5b|\[)\s*(?:\\\x22|")([a-zA-Z0-9_-]+)(?:\\\x22|")\s*(?:\\\x5d|\])\s*,\s*(?:\\\x22|")([^"]+?)(?:\\\x22|")/', $html, $m)) {
                    
                    // Buscar tipo mime en el contexto de este string
                    $pos = strpos($html, $str);
                    $start = max(0, $pos - 1500);
                    $context = substr($html, $start, 3000);
                    
                    $mime = 'unknown';
                    if (preg_match('/application\/vnd\.google-apps\.[a-z]+|application\/pdf|image\/[a-z]+/', $context, $mimeM)) {
                        $mime = $mimeM[0];
                    }

                    $name = stripcslashes($m[2]);
                    $name = preg_replace_callback('/\\\\x([0-9a-fA-F]{2})/', function($match) {
                        return chr(hexdec($match[1]));
                    }, $name);

                    // Clasificar tipo de elemento
                    $type = 'unknown';
                    if (strpos($mime, 'google-apps.folder') !== false) {
                        $type = 'folder';
                    } else if (strpos($mime, 'pdf') !== false || stripos($name, '.pdf') !== false) {
                        $type = 'pdf';
                    } else if (strpos($mime, 'google-apps.document') !== false || stripos($name, 'Ingresos') !== false || stripos($name, 'Listado') !== false || stripos($name, 'LISTAS') !== false) {
                        $type = 'document';
                    } else if (strpos($mime, 'image') !== false || stripos($name, '.jpg') !== false || stripos($name, '.jpeg') !== false || stripos($name, '.png') !== false) {
                        $type = 'image';
                    }

                    $items[$str] = [
                        'id' => $str,
                        'name' => $name,
                        'type' => $type
                    ];
                }
            }

            return $items;

        } catch (\Exception $e) {
            Log::error("Excepción al obtener elementos de Drive para carpeta " . $folderId . ": " . $e->getMessage());
            return [];
        }
    }

    /**
     * Descarga y parsea un archivo específico (PDF o Google Doc) por ID.
     */
    public function scrapePdfFile(string $fileId, \Closure $onProgress = null): array
    {
        $imported = 0;
        $updated = 0;
        $text = null;
        $tempPath = storage_path('app/temp_drive_' . $fileId . '.pdf');
        $downloadUrl = "https://drive.google.com/uc?export=download&id=" . $fileId;

        try {
            $localDestUrl = null;
            $publicDir = public_path('uploads/reportes');
            if (!file_exists($publicDir)) {
                mkdir($publicDir, 0755, true);
            }

            // Intentar primero como Google Doc (Exportar a TXT plain text)
            $exportDocUrl = "https://docs.google.com/document/d/{$fileId}/export?format=txt";
            if ($onProgress) $onProgress("Probando si es Google Doc (exportar a texto)...");

            $docResponse = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            ])->timeout(20)->get($exportDocUrl);

            if ($docResponse->successful() && strlen($docResponse->body()) > 100 && stripos($docResponse->body(), '<html') === false) {
                if ($onProgress) $onProgress("¡Es un Google Doc! Descargado como TXT con éxito.");
                $text = $docResponse->body();

                // Guardar copia local
                $localPath = $publicDir . '/' . $fileId . '.txt';
                file_put_contents($localPath, $docResponse->body());
                $localDestUrl = '/uploads/reportes/' . $fileId . '.txt';
            } else {
                // Si falla, es un PDF. Intentar descarga directa
                if ($onProgress) $onProgress("Descargando archivo como PDF...");
                
                $pdfResponse = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                ])->timeout(45)->get($downloadUrl);

                if ($pdfResponse->failed() || substr($pdfResponse->body(), 0, 4) !== '%PDF') {
                    // Reintentar con confirm=t (por si tiene aviso de virus por tamaño)
                    if ($onProgress) $onProgress("Reintentando descarga de PDF con confirmación...");
                    $confirmUrl = $downloadUrl . "&confirm=t";
                    $pdfResponse = Http::withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                    ])->timeout(45)->get($confirmUrl);
                }

                if ($pdfResponse->successful() && substr($pdfResponse->body(), 0, 4) === '%PDF') {
                    if ($onProgress) $onProgress("PDF descargado. Parseando texto...");
                    file_put_contents($tempPath, $pdfResponse->body());

                    // Guardar copia local
                    $localPath = $publicDir . '/' . $fileId . '.pdf';
                    file_put_contents($localPath, $pdfResponse->body());
                    $localDestUrl = '/uploads/reportes/' . $fileId . '.pdf';

                    $pdf = $this->pdfParser->parseFile($tempPath);
                    $text = $pdf->getText();
                    @unlink($tempPath);
                }
            }

            if (empty($text)) {
                $msg = "No se pudo obtener el contenido del archivo ID {$fileId} (no es PDF ni Google Doc accesible).";
                if ($onProgress) $onProgress("[ERROR] $msg");
                Log::warning($msg);
                return ['imported' => 0, 'updated' => 0, 'success' => false, 'error' => $msg];
            }

            // Procesar el texto obtenido
            $lines = explode("\n", $text);
            $totalLines = count($lines);
            if ($onProgress) $onProgress("Procesando {$totalLines} líneas extraídas...");

            // Listado de hospitales conocidos para mapeo rápido
            $hospitales = [
                'Domingo Luciani' => 'Hospital Domingo Luciani (El Llanito)',
                'Universitario de Caracas' => 'Hospital Universitario de Caracas (HUC)',
                'Perez Carreño' => 'Hospital Pérez Carreño',
                'Vargas de Caracas' => 'Hospital Vargas de Caracas',
                'Ricardo Baquero' => 'Hospital Ricardo Baquero González (Periférico de Catia)',
                'Baquero Gonzalez' => 'Hospital Ricardo Baquero González (Periférico de Catia)',
            ];

            $lastRecord = null;
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                // Estructura mínima: debe comenzar con un número de índice
                if (!preg_match('/^(\d+)\s*(.*)$/', $line, $lineMatches)) {
                    if ($lastRecord && preg_match('/fallecid[ao]/i', $line)) {
                        $lastRecord->update([
                            'status' => 'deceased',
                            'description' => str_replace('ingresado en centro de salud', 'ingresado en centro de salud (Fallecido)', $lastRecord->description)
                        ]);
                    }
                    continue;
                }

                $remainingText = trim($lineMatches[2]);

                // Intentar extraer cédula (CI): buscar número de 7 u 8 dígitos o con puntos o combinaciones pegadas
                $cedula = null;
                $age = null;
                $gender = null;

                // 1. Cédulas con puntos (ej. 31.445.070)
                if (preg_match('/\b(\d{1,2})\.(\d{3})\.(\d{3})\b/', $remainingText, $dotMatches)) {
                    $cedula = $dotMatches[1] . $dotMatches[2] . $dotMatches[3];
                    $remainingText = trim(str_replace($dotMatches[0], '', $remainingText));
                }
                // 2. Cédulas concatenadas con edad y género (ej. 2584622129F)
                elseif (preg_match('/\b(\d{7,8})(\d{2})([MFmf])\b/i', $remainingText, $concatMatches)) {
                    $cedula = $concatMatches[1];
                    $age = intval($concatMatches[2]);
                    $char = strtolower($concatMatches[3]);
                    $gender = ($char === 'f') ? 'Femenino' : 'Masculino';
                    $remainingText = trim(str_replace($concatMatches[0], '', $remainingText));
                }
                // 3. Cédulas concatenadas con edad de 1 dígito y género (ej. 258462219F)
                elseif (preg_match('/\b(\d{7,8})(\d{1})([MFmf])\b/i', $remainingText, $concatMatches)) {
                    $cedula = $concatMatches[1];
                    $age = intval($concatMatches[2]);
                    $char = strtolower($concatMatches[3]);
                    $gender = ($char === 'f') ? 'Femenino' : 'Masculino';
                    $remainingText = trim(str_replace($concatMatches[0], '', $remainingText));
                }
                // 4. Cédulas concatenadas con edad de 2 dígitos (ej. 2625462428)
                elseif (preg_match('/\b(\d{7,8})(\d{2})\b/', $remainingText, $concatMatches)) {
                    $cedula = $concatMatches[1];
                    $age = intval($concatMatches[2]);
                    $remainingText = trim(str_replace($concatMatches[0], '', $remainingText));
                }
                // 5. Cédulas concatenadas con edad de 1 dígito (ej. 262546248)
                elseif (preg_match('/\b(\d{7,8})(\d{1})\b/', $remainingText, $concatMatches)) {
                    $cedula = $concatMatches[1];
                    $age = intval($concatMatches[2]);
                    $remainingText = trim(str_replace($concatMatches[0], '', $remainingText));
                }
                // 6. Cédulas simples sin edad ni género pegados
                elseif (preg_match('/\b(\d{7,8})\b/', $remainingText, $simpleMatches)) {
                    $cedula = $simpleMatches[1];
                    $remainingText = trim(str_replace($simpleMatches[0], '', $remainingText));
                }

                // Intentar extraer edad y género si no se capturaron en la cédula
                if ($age === null || $gender === null) {
                    if (preg_match('/\b(\d{1,2})\s*([mFfM])\b/i', $remainingText, $ageGenderMatches)) {
                        if ($age === null) $age = intval($ageGenderMatches[1]);
                        if ($gender === null) {
                            $char = strtolower($ageGenderMatches[2]);
                            $gender = ($char === 'f') ? 'Femenino' : 'Masculino';
                        }
                        $remainingText = trim(str_replace($ageGenderMatches[0], '', $remainingText));
                    } elseif (preg_match('/\b(\d{1,2})\b/', $remainingText, $ageOnlyMatches)) {
                        if ($age === null) $age = intval($ageOnlyMatches[1]);
                        $remainingText = trim(str_replace($ageOnlyMatches[0], '', $remainingText));
                    }
                }

                // Determinar hospital en base al texto
                $hospitalDetectado = 'Centro de Salud (Sismo Venezuela)';
                foreach ($hospitales as $keyword => $fullName) {
                    if (stripos($line, $keyword) !== false) {
                        $hospitalDetectado = $fullName;
                        break;
                    }
                }

                // Limpiar y obtener el nombre completo (lo que queda al inicio del texto restante)
                $nameParts = preg_split('/\t+|\s{2,}/', $remainingText);
                $fullName = trim($nameParts[0] ?? '');

                // Limpiar prefijos de hospital que hayan quedado pegados al nombre completo
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

                foreach ($hospitalPrefixes as $prefix) {
                    if (stripos($fullName, $prefix) === 0) {
                        $fullName = trim(substr($fullName, strlen($prefix)));
                        break;
                    }
                }
                $fullName = ltrim($fullName, " -/\t");

                // Limpiar caracteres extraños que queden al final del nombre
                $fullName = preg_replace('/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/', '', $fullName);
                $fullName = trim(preg_replace('/\s+/', ' ', $fullName));

                // Ignorar filas de encabezado o nombres vacíos / demasiado cortos
                if (empty($fullName) || strlen($fullName) < 4 || stripos($fullName, 'Apellidos') !== false || stripos($fullName, 'Nombre') !== false) {
                    continue;
                }

                // Comprobar si ya existe por Cédula o por Nombre exacto
                $existe = null;
                if (!empty($cedula)) {
                    $existe = Desaparecido::where('cedula', $cedula)->first();
                }

                if (!$existe) {
                    $existe = Desaparecido::where('full_name', 'like', $fullName)->first();
                }

                // Determinar estado de la persona en base a si la línea contiene "fallecido" o "fallecida"
                $status = 'found';
                if (preg_match('/fallecid[ao]/i', $line)) {
                    $status = 'deceased';
                }

                if ($existe) {
                    // Actualizar el caso
                    $existe->update([
                        'status' => $status,
                        'found_at' => now(),
                        'found_location' => $hospitalDetectado,
                        'description' => $status === 'deceased'
                            ? ($existe->description . "\n\n[ACTUALIZACIÓN SÍSMICA]: Encontrado ingresado en " . $hospitalDetectado . " (Fallecido) según reporte consolidado de Google Drive.")
                            : ($existe->description . "\n\n[ACTUALIZACIÓN SÍSMICA]: Encontrado ingresado en " . $hospitalDetectado . " según reporte consolidado de Google Drive."),
                        'source_url' => $localDestUrl ?: $existe->source_url ?: $downloadUrl,
                    ]);
                    $updated++;
                    $lastRecord = $existe;
                } else {
                    // Crear nuevo caso de localizado en hospital
                    $lastRecord = Desaparecido::create([
                        'code' => 'HOSP-' . strtoupper(substr(md5($fullName . uniqid()), 0, 6)),
                        'full_name' => $fullName,
                        'cedula' => $cedula,
                        'age' => $age,
                        'gender' => $gender,
                        'status' => $status,
                        'found_at' => now(),
                        'found_location' => $hospitalDetectado,
                        'last_seen_location' => 'Venezuela (Sismo)',
                        'description' => $status === 'deceased'
                            ? "Registrado como ingresado en centro de salud tras evento sismico (Fallecido). Ubicacion: {$hospitalDetectado}."
                            : "Registrado como ingresado en centro de salud tras evento sismico. Ubicacion: {$hospitalDetectado}.",
                        'source_url' => $localDestUrl ?: $downloadUrl,
                    ]);
                    $imported++;
                }
            }

            Log::info("Archivo ID {$fileId} procesado: +{$imported} importados, +{$updated} actualizados.");
            return ['imported' => $imported, 'updated' => $updated, 'success' => true];

        } catch (\Exception $e) {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
            $msg = "Error procesando archivo Drive: " . $e->getMessage();
            if ($onProgress) $onProgress("[ERROR] $msg");
            Log::error($msg);
            return ['imported' => 0, 'updated' => 0, 'success' => false, 'error' => $msg];
        }
    }
}
