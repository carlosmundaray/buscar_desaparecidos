<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Desaparecido;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DesaparecidoController extends Controller
{
    /**
     * Muestra la página principal con el buscador.
     */
    public function index()
    {
        // Calcular estadísticas generales
        $stats = [
            'reported' => Desaparecido::count(),
            'missing' => Desaparecido::where('status', 'missing')->count(),
            'found' => Desaparecido::where('status', 'found')->count(),
            'deceased' => Desaparecido::where('status', 'deceased')->count(),
            'hospitalized' => Desaparecido::where('status', 'found')
                ->where(function ($q) {
                    $q->where('found_location', 'like', '%Hospital%')
                      ->orWhere('found_location', 'like', '%Centro de Salud%')
                      ->orWhere('code', 'like', 'HOSP-%');
                })->count(),
        ];

        return view('index', compact('stats'));
    }

    /**
     * Endpoint API para la búsqueda rápida de desaparecidos.
     */
    public function search(Request $request, \App\Services\ScraperService $scraper)
    {
        $searchQuery = $request->input('query');
        $statusFilter = $request->input('status', 'all');

        // Búsqueda en tiempo real bajo demanda removida de aquí para optimizar la velocidad.
        // Se ejecuta de manera asíncrona mediante un endpoint dedicado.
        
        $dbQuery = Desaparecido::query();

        // Aplicar filtro de búsqueda de texto / cédula
        if (!empty($searchQuery)) {
            // Limpiar la consulta para buscar cédula (solo números)
            $cleanQuery = preg_replace('/[^0-9]/', '', $searchQuery);

            $dbQuery->where(function ($q) use ($searchQuery, $cleanQuery) {
                $q->where('full_name', 'like', "%{$searchQuery}%")
                  ->orWhere('alias', 'like', "%{$searchQuery}%")
                  ->orWhere('last_seen_location', 'like', "%{$searchQuery}%")
                  ->orWhere('description', 'like', "%{$searchQuery}%");

                if (!empty($cleanQuery)) {
                    $q->orWhere('cedula', 'like', "%{$cleanQuery}%");
                }
            });
        }

        // Aplicar filtro de estado
        if ($statusFilter === 'hospitalized') {
            $dbQuery->where('status', 'found')
                    ->where(function ($q) {
                        $q->where('found_location', 'like', '%Hospital%')
                          ->orWhere('found_location', 'like', '%Centro de Salud%')
                          ->orWhere('code', 'like', 'HOSP-%');
                    });
        } elseif ($statusFilter !== 'all') {
            $dbQuery->where('status', $statusFilter);
        }

        // Aplicar filtro de presencia de foto
        $photoFilter = $request->input('photo', 'all');
        if ($photoFilter === 'yes') {
            $dbQuery->whereNotNull('photo_path')->where('photo_path', '!=', '');
        } elseif ($photoFilter === 'no') {
            $dbQuery->where(function ($q) {
                $q->whereNull('photo_path')->orWhere('photo_path', '');
            });
        }

        // Paginación rápida de 24 elementos por página
        $people = $dbQuery->orderBy('created_at', 'desc')->paginate(24);

        // Formatear fechas para que el frontend las muestre amigablemente
        $people->getCollection()->transform(function ($person) {
            $person->formatted_last_seen = $person->last_seen_at 
                ? $person->last_seen_at->format('d/m/Y') 
                : 'No especificada';
            $person->formatted_found = $person->found_at 
                ? $person->found_at->format('d/m/Y') 
                : null;
            return $person;
        });

        // Recalcular estadísticas filtradas / totales
        $stats = [
            'reported' => Desaparecido::count(),
            'missing' => Desaparecido::where('status', 'missing')->count(),
            'found' => Desaparecido::where('status', 'found')->count(),
            'deceased' => Desaparecido::where('status', 'deceased')->count(),
            'hospitalized' => Desaparecido::where('status', 'found')
                ->where(function ($q) {
                    $q->where('found_location', 'like', '%Hospital%')
                      ->orWhere('found_location', 'like', '%Centro de Salud%')
                      ->orWhere('code', 'like', 'HOSP-%');
                })->count(),
        ];

        return response()->json([
            'people' => $people,
            'stats' => $stats
        ]);
    }

    /**
     * Guarda un nuevo reporte de persona desaparecida.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'alias' => 'nullable|string|max:255',
            'cedula' => 'nullable|string|max:20',
            'age' => 'nullable|integer|min:0|max:120',
            'gender' => 'nullable|string|in:Masculino,Femenino,Otro',
            'last_seen_at' => 'nullable|date',
            'last_seen_location' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'description' => 'required|string',
            'reporter_name' => 'required|string|max:255',
            'reporter_phone' => 'required|string|max:50',
            'reporter_email' => 'nullable|email|max:255',
            'relationship' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Manejar subida de foto
        $photoPath = null;
        $photoHash = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            // Almacenar en public/uploads para fácil acceso
            $file->move(public_path('uploads/photos'), $filename);
            $photoPath = asset('uploads/photos/' . $filename);
            
            // Calcular hash local
            $localPath = public_path('uploads/photos/' . $filename);
            $photoHash = \App\Services\ImageHashService::hash($localPath);
        }

        // Limpiar cédula si se proporcionó
        $cedula = null;
        if (!empty($validated['cedula'])) {
            $cedula = preg_replace('/[^0-9]/', '', $validated['cedula']);
        } else {
            // Tratar de extraerla de la descripción
            $cedula = Desaparecido::extractCedula($validated['description']);
        }

        // Crear registro
        $desaparecido = Desaparecido::create([
            'code' => 'BD-' . strtoupper(substr(md5(uniqid()), 0, 6)),
            'full_name' => $validated['full_name'],
            'alias' => $validated['alias'],
            'cedula' => $cedula,
            'age' => $validated['age'],
            'gender' => $validated['gender'],
            'last_seen_at' => $validated['last_seen_at'],
            'last_seen_location' => $validated['last_seen_location'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'description' => $validated['description'],
            'photo_path' => $photoPath,
            'photo_hash' => $photoHash,
            'reporter_name' => $validated['reporter_name'],
            'reporter_phone' => $validated['reporter_phone'],
            'reporter_email' => $validated['reporter_email'],
            'relationship' => $validated['relationship'],
            'status' => 'missing',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reporte creado exitosamente.',
            'person' => $desaparecido
        ]);
    }

    /**
     * Marca una persona como localizada.
     */
    public function markFound(Request $request, $id)
    {
        $person = Desaparecido::findOrFail($id);
        
        $validated = $request->validate([
            'found_location' => 'nullable|string|max:255'
        ]);

        $person->update([
            'status' => 'found',
            'found_at' => Carbon::now(),
            'found_location' => $validated['found_location'] ?? 'Ubicación no especificada'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Persona marcada como localizada con éxito.',
            'person' => $person
        ]);
    }

    /**
     * Endpoint API para la búsqueda por foto.
     */
    public function searchByPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'nullable|string|in:all,missing,found,deceased'
        ]);

        $photoFile = $request->file('photo');
        $statusFilter = $request->input('status', 'all');

        // Calcular hash de la foto subida
        $queryHash = \App\Services\ImageHashService::hash($photoFile->getRealPath());

        if (!$queryHash) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo procesar la imagen cargada. Por favor intente con otra foto o formato.'
            ], 422);
        }

        // Obtener todos los desaparecidos que tienen un hash calculado
        $dbQuery = Desaparecido::whereNotNull('photo_hash')
            ->where('photo_hash', '!=', '');

        if ($statusFilter !== 'all') {
            $dbQuery->where('status', $statusFilter);
        }

        $people = $dbQuery->get();

        // Calcular distancias y mapear similitud
        $results = $people->map(function ($person) use ($queryHash) {
            $person->similarity = \App\Services\ImageHashService::similarity($queryHash, $person->photo_hash);
            
            // Formatear fechas
            $person->formatted_last_seen = $person->last_seen_at 
                ? $person->last_seen_at->format('d/m/Y') 
                : 'No especificada';
            $person->formatted_found = $person->found_at 
                ? $person->found_at->format('d/m/Y') 
                : null;
                
            return $person;
        });

        // Filtrar y ordenar por similitud de mayor a menor
        // Umbral mínimo del 45%
        $filteredResults = $results->filter(function ($person) {
            return $person->similarity >= 45.0;
        })->sortByDesc('similarity')->values();

        // Paginación manual
        $perPage = 24;
        $currentPage = intval($request->input('page', 1));
        $total = $filteredResults->count();
        $slicedData = $filteredResults->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedResults = new \Illuminate\Pagination\LengthAwarePaginator(
            $slicedData,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $stats = [
            'reported' => Desaparecido::count(),
            'missing' => Desaparecido::where('status', 'missing')->count(),
            'found' => Desaparecido::where('status', 'found')->count(),
            'deceased' => Desaparecido::where('status', 'deceased')->count(),
        ];

        return response()->json([
            'success' => true,
            'people' => $paginatedResults,
            'stats' => $stats
        ]);
    }

    /**
     * Sincroniza en segundo plano búsquedas externas para un término dado de forma asíncrona.
     */
    public function syncSearch(Request $request, \App\Services\ScraperService $scraper)
    {
        $searchQuery = $request->input('query');
        if (empty($searchQuery)) {
            return response()->json(['success' => true, 'new_records' => 0]);
        }

        $cleanQuery = trim($searchQuery);
        if (strlen($cleanQuery) < 3) {
            return response()->json(['success' => true, 'new_records' => 0]);
        }

        // Check cache to avoid hitting external scrapers repeatedly for the same term
        $cacheKey = 'sync_query_' . md5(strtolower($cleanQuery));
        if (cache()->has($cacheKey)) {
            return response()->json([
                'success' => true,
                'new_records' => 0,
                'cached' => true
            ]);
        }

        $beforeCount = Desaparecido::count();

        // Run scrapers with calculateHashes = false for near-instant execution
        try {
            $scraper->scrapeVenezuelaTeBuscaPage(1, $cleanQuery, false);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Error en sincro externa (VTB): " . $e->getMessage());
        }

        try {
            $scraper->scrapePage(1, $cleanQuery, false);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Error en sincro externa (BD): " . $e->getMessage());
        }

        $afterCount = Desaparecido::count();
        $newRecords = $afterCount - $beforeCount;

        // Cache this search sync for 15 minutes
        cache()->put($cacheKey, true, now()->addMinutes(15));

        // If new records were imported, trigger hash generation for missing images in the background
        if ($newRecords > 0) {
            $artisanPath = base_path('artisan');
            if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
                pclose(popen("start /B php \"{$artisanPath}\" db:generate-hashes > NUL 2>&1", "r"));
            } else {
                exec("php \"{$artisanPath}\" db:generate-hashes > /dev/null 2>&1 &");
            }
        }

        return response()->json([
            'success' => true,
            'new_records' => $newRecords
        ]);
    }
}
