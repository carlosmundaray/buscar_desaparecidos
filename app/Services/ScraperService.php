<?php

namespace App\Services;

use App\Models\Desaparecido;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScraperService
{
    /**
     * Scrapes a single page of missing persons from buscardesaparecidos.com
     * and saves or updates the records in our SQLite database.
     *
     * @param int $page
     * @return array
     */
    public function scrapePage(int $page, ?string $query = null, bool $calculateHashes = true): array
    {
        $url = "https://buscardesaparecidos.com/buscar?page={$page}";
        if ($query !== null && $query !== '') {
            $url = "https://buscardesaparecidos.com/buscar?search=" . urlencode($query) . "&page={$page}";
        }
        
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            ])->timeout(30)->get($url);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => "HTTP request failed with status: " . $response->status(),
                    'imported' => 0,
                    'updated' => 0,
                    'total' => 0
                ];
            }

            $html = $response->body();

            if (!preg_match('/<script data-page="app" type="application\/json">(.*?)<\/script>/s', $html, $matches)) {
                return [
                    'success' => false,
                    'error' => "Could not find Inertia JSON script tag",
                    'imported' => 0,
                    'updated' => 0,
                    'total' => 0
                ];
            }

            $json = $matches[1];
            $data = json_decode($json, true);

            if ($data === null) {
                return [
                    'success' => false,
                    'error' => "JSON decode error: " . json_last_error_msg(),
                    'imported' => 0,
                    'updated' => 0,
                    'total' => 0
                ];
            }

            if (!isset($data['props']['people']['data'])) {
                return [
                    'success' => false,
                    'error' => "Missing 'people.data' path in JSON payload",
                    'imported' => 0,
                    'updated' => 0,
                    'total' => 0
                ];
            }

            $people = $data['props']['people']['data'];
            $imported = 0;
            $updated = 0;
            $total = count($people);

            foreach ($people as $item) {
                $externalId = $item['id'] ?? null;
                if (!$externalId) {
                    continue;
                }

                $foundAt = !empty($item['found_at']) ? $item['found_at'] : null;
                $status = $foundAt ? 'found' : 'missing';

                $description = $item['description'] ?? '';
                if (preg_match('/^fallecid[ao]$/i', trim($description)) || preg_match('/encontrado\s+fallecid[ao]/i', $description) || preg_match('/falleció\s*-\s*fue\s+encontrado/i', $description)) {
                    $status = 'deceased';
                }
                $cedula = Desaparecido::extractCedula($description);

                $slug = $item['slug'] ?? '';
                $sourceUrl = $slug ? "https://buscardesaparecidos.com/caso/{$slug}" : "https://buscardesaparecidos.com/buscar";

                $existing = Desaparecido::where('external_id', $externalId)
                    ->where('source_url', 'like', '%buscardesaparecidos.com%')
                    ->first();

                if ($existing) {
                    $hasChanges = false;

                    if ($existing->status !== $status) {
                        $existing->status = $status;
                        $existing->found_at = $foundAt;
                        $hasChanges = true;
                    }

                    if ($existing->photo_path !== ($item['photo_path'] ?? null)) {
                        $existing->photo_path = $item['photo_path'] ?? null;
                        $existing->photo_hash = ($existing->photo_path && $calculateHashes) ? \App\Services\ImageHashService::hash($existing->photo_path) : null;
                        $hasChanges = true;
                    }

                    if (empty($existing->cedula) && !empty($cedula)) {
                        $existing->cedula = $cedula;
                        $hasChanges = true;
                    }

                    if ($hasChanges) {
                        $existing->save();
                        $updated++;
                    }
                } else {
                    $photoPath = $item['photo_path'] ?? null;
                    $photoHash = ($photoPath && $calculateHashes) ? \App\Services\ImageHashService::hash($photoPath) : null;

                    Desaparecido::create([
                        'external_id' => $externalId,
                        'code' => $item['code'] ?? null,
                        'full_name' => $item['full_name'] ?? 'Desconocido',
                        'alias' => $item['alias'] ?? null,
                        'cedula' => $cedula,
                        'age' => $item['age'] ?? null,
                        'gender' => $item['gender'] ?? null,
                        'last_seen_at' => !empty($item['last_seen_at']) ? $item['last_seen_at'] : null,
                        'last_seen_location' => $item['last_seen_location'] ?? null,
                        'city' => $item['city'] ?? null,
                        'state' => $item['state'] ?? null,
                        'description' => $description,
                        'photo_path' => $photoPath,
                        'photo_hash' => $photoHash,
                        'reporter_name' => $item['reporter_name'] ?? 'Reporte importado',
                        'reporter_phone' => $item['reporter_phone'] ?? null,
                        'reporter_email' => $item['reporter_email'] ?? null,
                        'relationship' => $item['relationship'] ?? null,
                        'status' => $status,
                        'found_at' => $foundAt,
                        'found_location' => $item['found_location'] ?? null,
                        'source_url' => $sourceUrl,
                    ]);
                    $imported++;
                }
            }

            return [
                'success' => true,
                'imported' => $imported,
                'updated' => $updated,
                'total' => $total,
                'last_page' => $data['props']['people']['last_page'] ?? $page
            ];

        } catch (\Exception $e) {
            Log::error("Scraper error on page {$page}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'imported' => 0,
                'updated' => 0,
                'total' => 0
            ];
        }
    }

    /**
     * Scrapes a single page of missing persons from venezuelatebusca.com
     * and saves or updates the records in our SQLite database.
     *
     * @param int $page
     * @param string|null $query
     * @return array
     */
    public function scrapeVenezuelaTeBuscaPage(int $page, ?string $query = null, bool $calculateHashes = true): array
    {
        $baseUrl = "https://venezuelatebusca.com/";
        $url = $baseUrl . "?page={$page}";
        if ($query !== null && $query !== '') {
            $url = $baseUrl . "?query=" . urlencode($query) . "&page={$page}";
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            ])->timeout(30)->get($url);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => "HTTP request failed with status: " . $response->status(),
                    'imported' => 0,
                    'updated' => 0,
                    'total' => 0
                ];
            }

            $html = $response->body();

            // Load DOM
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
            libxml_clear_errors();

            $xpath = new \DOMXPath($dom);
            $cards = $xpath->query('//div[@data-slot="card"]');

            if ($cards->length === 0) {
                return [
                    'success' => true,
                    'imported' => 0,
                    'updated' => 0,
                    'total' => 0,
                    'last_page' => $page
                ];
            }

            $imported = 0;
            $updated = 0;
            $total = $cards->length;

            for ($i = 0; $i < $cards->length; $i++) {
                $card = $cards->item($i);

                // 1. Get Title (Name)
                $titleNode = $xpath->query('.//div[@data-slot="card-title"]', $card);
                $name = $titleNode->length > 0 ? trim($titleNode->item(0)->textContent) : 'Desconocido';
                
                // Format name cleanly
                $name = mb_convert_case($name, MB_CASE_TITLE, "UTF-8");

                // 2. Get Photo Path
                $imgNode = $xpath->query('.//img', $card);
                $photo = $imgNode->length > 0 ? $imgNode->item(0)->getAttribute('src') : null;
                if ($photo && !str_starts_with($photo, 'http://') && !str_starts_with($photo, 'https://')) {
                    if (str_starts_with($photo, '/')) {
                        $photo = 'https://venezuelatebusca.com' . $photo;
                    } else {
                        $photo = 'https://venezuelatebusca.com/' . $photo;
                    }
                }

                // 3. Parse Content Divs
                $age = null;
                $gender = null;
                $cedula = null;
                $location = 'No especificada';
                $dateText = null;
                $isDeceased = false;

                $contentDivs = $xpath->query('.//div[@data-slot="card-content"]/div', $card);
                foreach ($contentDivs as $div) {
                    $span = $xpath->query('.//span', $div);
                    if ($span->length > 0) {
                        $text = trim($span->item(0)->textContent);

                        if (preg_match('/fallec/i', $text)) {
                            $isDeceased = true;
                        }

                        if (str_contains($text, 'años') || str_contains($text, 'masculino') || str_contains($text, 'femenino')) {
                            // Extract details from string (e.g. "13.245.276 - 49 años - femenino")
                            $parts = explode('-', $text);
                            foreach ($parts as $part) {
                                $part = trim($part);
                                if (str_contains($part, 'años')) {
                                    $age = (int) filter_var($part, FILTER_SANITIZE_NUMBER_INT);
                                } elseif (str_contains($part, 'masculino') || str_contains($part, 'femenino')) {
                                    $gender = mb_convert_case($part, MB_CASE_TITLE, "UTF-8");
                                } else {
                                    // Extract potential cedula
                                    $cleanCedula = preg_replace('/[^0-9]/', '', $part);
                                    if (strlen($cleanCedula) >= 6 && strlen($cleanCedula) <= 10) {
                                        $cedula = $cleanCedula;
                                    }
                                }
                            }
                        } elseif (preg_match('/\d{1,2}\s+[a-z]{3}\.?\s+\d{4}/i', $text)) {
                            // E.g. "25 jun. 2026, 11:05 p. m." -> it's the date!
                            $dateText = $text;
                        } else {
                            // Location string
                            $location = $text;
                        }
                    }
                }

                // Parse Date if present
                $lastSeenAt = null;
                if ($dateText) {
                    try {
                        // Rough date translation for PHP: "25 jun. 2026, 11:05 p. m."
                        // Replace common Spanish month abbreviations
                        $spanishMonths = ['ene.', 'feb.', 'mar.', 'abr.', 'may.', 'jun.', 'jul.', 'ago.', 'sep.', 'oct.', 'nov.', 'dic.'];
                        $englishMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        
                        $normDate = str_replace($spanishMonths, $englishMonths, mb_strtolower($dateText));
                        $normDate = str_replace(['p. m.', 'a. m.'], ['PM', 'AM'], $normDate);
                        
                        // Parse using Carbon or PHP DateTime
                        $lastSeenAt = new \DateTime($normDate);
                    } catch (\Exception $e) {
                        Log::warning("Could not parse date text: '$dateText'. Error: " . $e->getMessage());
                    }
                }

                // Create a unique CRC32 hash of details for external_id to avoid overlaps
                $externalId = crc32($name . '_' . $location . '_' . ($age ?? '0'));
                // Keep it positive
                $externalId = sprintf("%u", $externalId);

                // Build source URL
                $sourceUrl = "https://venezuelatebusca.com/?query=" . urlencode($name);

                // Check if already exists in our database
                $existing = null;
                if (!empty($cedula)) {
                    $existing = Desaparecido::where('cedula', $cedula)->first();
                }

                if (!$existing) {
                    $existing = Desaparecido::where('full_name', 'like', $name)->first();
                }

                if (!$existing) {
                    $existing = Desaparecido::where('external_id', $externalId)
                        ->where(function($q) {
                            $q->where('source_url', 'like', '%workers.dev%')
                              ->orWhere('source_url', 'like', '%venezuelatebusca.com%');
                        })
                        ->first();
                }

                if ($existing) {
                    $hasChanges = false;

                    if ($existing->photo_path !== $photo) {
                        $existing->photo_path = $photo;
                        $existing->photo_hash = ($photo && $calculateHashes) ? \App\Services\ImageHashService::hash($photo) : null;
                        $hasChanges = true;
                    }

                    if (empty($existing->cedula) && !empty($cedula)) {
                        $existing->cedula = $cedula;
                        $hasChanges = true;
                    }

                    $newStatus = $isDeceased ? 'deceased' : $existing->status;
                    if ($existing->status !== $newStatus) {
                        $existing->status = $newStatus;
                        $hasChanges = true;
                    }

                    if ($hasChanges) {
                        $existing->save();
                        $updated++;
                    }
                } else {
                    // Create new record
                    $photoHash = ($photo && $calculateHashes) ? \App\Services\ImageHashService::hash($photo) : null;
                    Desaparecido::create([
                        'external_id' => $externalId,
                        'code' => 'VT-' . strtoupper(substr(md5(uniqid()), 0, 6)),
                        'full_name' => $name,
                        'alias' => null,
                        'cedula' => $cedula,
                        'age' => $age,
                        'gender' => $gender,
                        'last_seen_at' => $lastSeenAt,
                        'last_seen_location' => $location,
                        'city' => null,
                        'state' => null,
                        'description' => "Caso importado de Venezuela Te Busca. Información original: Nombre: {$name}, Edad: " . ($age ?? 'N/E') . ", Género: " . ($gender ?? 'N/E') . ", Lugar: {$location}.",
                        'photo_path' => $photo,
                        'photo_hash' => $photoHash,
                        'reporter_name' => 'Venezuela Te Busca',
                        'reporter_phone' => null,
                        'reporter_email' => null,
                        'relationship' => 'Iniciativa Ciudadana',
                        'status' => $isDeceased ? 'deceased' : 'missing',
                        'found_at' => null,
                        'found_location' => null,
                        'source_url' => $sourceUrl,
                    ]);
                    $imported++;
                }
            }

            // Read total page count from React Router stream if we can find it
            // For now, let's assume we can keep paging if we got 20 cards
            $lastPage = $cards->length >= 20 ? $page + 1 : $page;

            return [
                'success' => true,
                'imported' => $imported,
                'updated' => $updated,
                'total' => $total,
                'last_page' => $lastPage
            ];

        } catch (\Exception $e) {
            Log::error("Scraper error on Venezuela Te Busca page {$page}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'imported' => 0,
                'updated' => 0,
                'total' => 0
            ];
        }
    }
}
