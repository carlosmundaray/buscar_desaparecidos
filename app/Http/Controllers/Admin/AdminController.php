<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Desaparecido;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\ImageHashService;

class AdminController extends Controller
{
    /**
     * Muestra el panel principal de administración con estadísticas avanzadas.
     */
    public function index()
    {
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
            'local_reports' => Desaparecido::whereNull('external_id')->count(),
            'external_reports' => Desaparecido::whereNotNull('external_id')->count(),
            'no_photo' => Desaparecido::whereNull('photo_path')->orWhere('photo_path', '')->count(),
            'has_photo' => Desaparecido::whereNotNull('photo_path')->where('photo_path', '!=', '')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Listado de todos los casos con filtros y buscador integrado.
     */
    public function casosIndex(Request $request)
    {
        $query = Desaparecido::query();

        // Buscar por texto
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('alias', 'like', "%{$search}%")
                  ->orWhere('cedula', 'like', "%{$search}%")
                  ->orWhere('last_seen_location', 'like', "%{$search}%");
            });
        }

        // Filtrar por estado
        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        // Filtrar por origen
        if ($source = $request->input('source')) {
            if ($source === 'local') {
                $query->whereNull('external_id');
            } elseif ($source === 'external') {
                $query->whereNotNull('external_id');
            }
        }

        // Ordenar por defecto los más nuevos creados
        $casos = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('admin.casos.index', compact('casos'));
    }

    /**
     * Formulario para crear un nuevo caso.
     */
    public function casosCreate()
    {
        $caso = new Desaparecido(); // Instancia vacía para compartir formulario
        return view('admin.casos.form', compact('caso'));
    }

    /**
     * Guarda el nuevo caso.
     */
    public function casosStore(Request $request, ImageHashService $hashService)
    {
        $rules = [
            'full_name' => ['required', 'string', 'max:255'],
            'alias' => ['nullable', 'string', 'max:255'],
            'cedula' => ['nullable', 'string', 'max:20'],
            'age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'gender' => ['nullable', 'string', 'in:Masculino,Femenino,Otro'],
            'last_seen_at' => ['nullable', 'date'],
            'last_seen_location' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'], // 2MB max
            'description' => ['required', 'string'],
            'reporter_name' => ['nullable', 'string', 'max:255'],
            'reporter_phone' => ['nullable', 'string', 'max:255'],
            'reporter_email' => ['nullable', 'email', 'max:255'],
            'relationship' => ['nullable', 'string', 'max:255'],
        ];

        $validated = $request->validate($rules);

        // Si se subió foto, procesarla y calcular hash dHash
        $photoPath = null;
        $photoHash = null;

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/photos'), $filename);
            $photoPath = '/uploads/photos/' . $filename;

            try {
                $photoHash = $hashService->generateHash(public_path($photoPath));
            } catch (\Exception $e) {
                Log::warning("Error al calcular hash de imagen para nuevo caso: " . $e->getMessage());
            }
        }

        // Extraer cédula de la descripción si no fue provista explícitamente
        $cedula = $validated['cedula'] ?? Desaparecido::extractCedula($validated['description']);

        $caso = Desaparecido::create(array_merge($validated, [
            'cedula' => $cedula,
            'photo_path' => $photoPath,
            'photo_hash' => $photoHash,
            'status' => 'missing'
        ]));

        return redirect()->route('admin.casos.index')
            ->with('success', "Caso de '{$caso->full_name}' registrado con éxito.");
    }

    /**
     * Formulario para editar un caso.
     */
    public function casosEdit($id)
    {
        $caso = Desaparecido::findOrFail($id);
        return view('admin.casos.form', compact('caso'));
    }

    /**
     * Actualiza el caso.
     */
    public function casosUpdate(Request $request, $id, ImageHashService $hashService)
    {
        $caso = Desaparecido::findOrFail($id);

        $rules = [
            'full_name' => ['required', 'string', 'max:255'],
            'alias' => ['nullable', 'string', 'max:255'],
            'cedula' => ['nullable', 'string', 'max:20'],
            'age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'gender' => ['nullable', 'string', 'in:Masculino,Femenino,Otro'],
            'last_seen_at' => ['nullable', 'date'],
            'last_seen_location' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'], // 2MB max
            'description' => ['required', 'string'],
            'status' => ['required', 'string', 'in:missing,found,deceased'],
            'found_location' => ['nullable', 'string', 'max:255'],
            'found_at' => ['nullable', 'date'],
            'reporter_name' => ['nullable', 'string', 'max:255'],
            'reporter_phone' => ['nullable', 'string', 'max:255'],
            'reporter_email' => ['nullable', 'email', 'max:255'],
            'relationship' => ['nullable', 'string', 'max:255'],
        ];

        $validated = $request->validate($rules);

        // Si se subió nueva foto, procesarla y eliminar la anterior si era local
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/photos'), $filename);
            $newPhotoPath = '/uploads/photos/' . $filename;

            // Eliminar imagen anterior local
            if (!empty($caso->photo_path) && strpos($caso->photo_path, '/uploads/photos/') === 0) {
                $oldPath = public_path($caso->photo_path);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $caso->photo_path = $newPhotoPath;

            try {
                $caso->photo_hash = $hashService->generateHash(public_path($newPhotoPath));
            } catch (\Exception $e) {
                Log::warning("Error al calcular hash de imagen editada: " . $e->getMessage());
            }
        }

        // Extraer cédula de descripción si no se indicó
        $cedula = $validated['cedula'] ?? Desaparecido::extractCedula($validated['description']);

        // Fechas de localizado
        $foundAt = $validated['found_at'] ?? null;
        if ($validated['status'] === 'found' && empty($foundAt)) {
            $foundAt = now();
        }

        $caso->update(array_merge($validated, [
            'cedula' => $cedula,
            'found_at' => $foundAt,
        ]));

        return redirect()->route('admin.casos.index')
            ->with('success', "El caso de '{$caso->full_name}' se actualizó correctamente.");
    }

    /**
     * Elimina el caso de la base de datos.
     */
    public function casosDestroy($id)
    {
        $caso = Desaparecido::findOrFail($id);

        // Eliminar foto si es local
        if (!empty($caso->photo_path) && strpos($caso->photo_path, '/uploads/photos/') === 0) {
            $path = public_path($caso->photo_path);
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        $caso->delete();

        return redirect()->route('admin.casos.index')
            ->with('success', 'Caso eliminado permanentemente del sistema.');
    }

    /**
     * Muestra la vista de perfil para actualizar datos y contraseña.
     */
    public function perfilShow()
    {
        $user = auth()->user();
        return view('admin.perfil', compact('user'));
    }

    /**
     * Procesa la actualización de la contraseña del usuario.
     */
    public function perfilUpdatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'La contraseña actual es obligatoria.',
            'current_password.current_password' => 'La contraseña actual introducida no es correcta.',
            'password.required' => 'La nueva contraseña es obligatoria.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
        ]);

        $user = auth()->user();
        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password)
        ]);

        return redirect()->route('admin.perfil')
            ->with('success', 'Contraseña actualizada con éxito.');
    }

    /**
     * Muestra la vista para importar personas y hospitales desde Excel / CSV.
     */
    public function importarIndex()
    {
        return view('admin.importar');
    }

    /**
     * Descarga la plantilla modelo en formato CSV.
     */
    public function importarPlantilla()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_importacion_hospitales.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility in Spanish
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // CSV Headers
            fputcsv($file, [
                'Nombre Completo',
                'Hospital / Ubicación',
                'Cédula',
                'Edad',
                'Género',
                'Detalles / Observaciones',
                'Estado'
            ], ';'); // Use semicolon as standard separator for Spanish Excel

            // Example rows
            fputcsv($file, [
                'Juan Pérez Gomez',
                'Hospital Domingo Luciani (El Llanito)',
                '12345678',
                '35',
                'Masculino',
                'Condición estable. Traumatismo menor en brazo izquierdo.',
                'Localizado'
            ], ';');

            fputcsv($file, [
                'María Rodriguez Ruiz',
                'Hospital Universitario de Caracas (HUC)',
                '87654321',
                '42',
                'Femenino',
                'Ingresada en terapia intermedia. Diagnóstico reservado.',
                'Localizado'
            ], ';');

            fputcsv($file, [
                'Pedro José Flores',
                'Hospital Pérez Carreño',
                '11223344',
                '68',
                'Masculino',
                'Fallecido por paro cardíaco en triaje.',
                'Fallecido'
            ], ';');

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Procesa la subida del archivo Excel / CSV y realiza la importación/actualización.
     */
    public function importarPost(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:4096|mimes:xlsx,xls,csv,txt'
        ]);

        $file = $request->file('file');
        $filePath = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());

        $imported = 0;
        $updated = 0;
        $failed = 0;

        $rows = [];

        try {
            if ($extension === 'csv' || $extension === 'txt') {
                // Parse CSV natively
                $handle = fopen($filePath, 'r');
                if ($handle !== false) {
                    $firstLine = fgets($handle);
                    rewind($handle);
                    // Try to auto-detect delimiter: comma or semicolon
                    $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
                    
                    // Skip BOM if present
                    if (str_starts_with($firstLine, "\xEF\xBB\xBF")) {
                        fseek($handle, 3);
                    }

                    $header = fgetcsv($handle, 0, $delimiter);
                    if ($header === false) {
                        throw new \Exception("El archivo CSV está vacío o es inválido.");
                    }
                    
                    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                        if (empty(array_filter($row))) {
                            continue;
                        }
                        $rowData = [];
                        foreach ($header as $index => $colName) {
                            if (empty($colName)) continue;
                            // Clean control characters and strip BOM if any
                            $cleanColName = trim(preg_replace('/[\x00-\x1F\x7F]/', '', $colName));
                            $cleanColName = str_replace("\xEF\xBB\xBF", "", $cleanColName);
                            $cleanColName = mb_strtolower($cleanColName, 'UTF-8');
                            $rowData[$cleanColName] = $row[$index] ?? null;
                        }
                        $rows[] = $rowData;
                    }
                    fclose($handle);
                }
            } else {
                // Parse Excel using PhpSpreadsheet
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
                $worksheet = $spreadsheet->getActiveSheet();
                $highestRow = $worksheet->getHighestRow();
                $highestColumn = $worksheet->getHighestColumn();
                
                $headerRow = $worksheet->rangeToArray('A1:' . $highestColumn . '1', null, true, false)[0];
                
                for ($row = 2; $row <= $highestRow; $row++) {
                    $rowValues = $worksheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false)[0];
                    if (empty(array_filter($rowValues))) {
                        continue;
                    }
                    $rowData = [];
                    foreach ($headerRow as $index => $colName) {
                        if (empty($colName)) continue;
                        $cleanColName = mb_strtolower(trim($colName), 'UTF-8');
                        $rowData[$cleanColName] = $rowValues[$index] ?? null;
                    }
                    $rows[] = $rowData;
                }
            }

            // Standard column name mapping
            $mappings = [
                'nombre' => ['nombre completo', 'nombre y apellido', 'nombres y apellidos', 'nombre', 'nombres', 'fullname', 'name', 'apellidos y nombres', 'apellidos y nombre'],
                'cedula' => ['cedula', 'cédula', 'ci', 'c.i', 'c.i.', 'identidad', 'dni', 'documento'],
                'edad' => ['edad', 'age'],
                'genero' => ['genero', 'género', 'sexo', 'sex', 'gender'],
                'hospital' => ['hospital / ubicación', 'hospital / ubicacion', 'hospital', 'ubicacion', 'ubicación', 'centro de salud', 'centro', 'found_location', 'found location'],
                'detalles' => ['detalles / observaciones', 'detalles', 'observaciones', 'observación', 'observacion', 'diagnostico', 'diagnóstico', 'detalles medicos', 'detalles médicos', 'description', 'descripción', 'descripcion'],
                'estado' => ['estado', 'estatus', 'status']
            ];

            foreach ($rows as $rowData) {
                $fullName = null;
                $cedula = null;
                $age = null;
                $gender = null;
                $hospital = null;
                $description = null;
                $statusString = null;

                foreach ($rowData as $key => $val) {
                    $key = trim($key);
                    $val = trim($val);
                    if ($val === '') $val = null;

                    if ($this->matchMapping($key, $mappings['nombre'])) {
                        $fullName = $val;
                    } elseif ($this->matchMapping($key, $mappings['cedula'])) {
                        $cedula = $val;
                    } elseif ($this->matchMapping($key, $mappings['edad'])) {
                        $age = $val;
                    } elseif ($this->matchMapping($key, $mappings['genero'])) {
                        $gender = $val;
                    } elseif ($this->matchMapping($key, $mappings['hospital'])) {
                        $hospital = $val;
                    } elseif ($this->matchMapping($key, $mappings['detalles'])) {
                        $description = $val;
                    } elseif ($this->matchMapping($key, $mappings['estado'])) {
                        $statusString = $val;
                    }
                }

                // Positional fallback
                if (!$fullName || !$hospital) {
                    $values = array_values($rowData);
                    $fullName = $fullName ?: ($values[0] ?? null);
                    $hospital = $hospital ?: ($values[1] ?? null);
                    $cedula = $cedula ?: ($values[2] ?? null);
                    $age = $age ?: ($values[3] ?? null);
                    $gender = $gender ?: ($values[4] ?? null);
                    $description = $description ?: ($values[5] ?? null);
                    $statusString = $statusString ?: ($values[6] ?? null);
                }

                if (empty($fullName) || empty($hospital)) {
                    $failed++;
                    continue;
                }

                // Clean values
                if ($cedula) {
                    $cedula = preg_replace('/[^0-9]/', '', $cedula);
                }
                if ($age) {
                    $age = (int) filter_var($age, FILTER_SANITIZE_NUMBER_INT);
                }
                if ($gender) {
                    $gender = mb_convert_case(trim($gender), MB_CASE_TITLE, "UTF-8");
                    if (str_starts_with(strtolower($gender), 'm')) $gender = 'Masculino';
                    elseif (str_starts_with(strtolower($gender), 'f')) $gender = 'Femenino';
                }

                // Determine status
                $status = 'found';
                if ($statusString) {
                    $statusLower = strtolower(trim($statusString));
                    if (str_contains($statusLower, 'fallecid') || str_contains($statusLower, 'deceas') || str_contains($statusLower, 'muert')) {
                        $status = 'deceased';
                    }
                } elseif ($description && preg_match('/fallecid[ao]/i', $description)) {
                    $status = 'deceased';
                }

                // Check if already exists
                $existe = null;
                if (!empty($cedula)) {
                    $existe = Desaparecido::where('cedula', $cedula)->first();
                }
                if (!$existe) {
                    $existe = Desaparecido::where('full_name', 'like', $fullName)->first();
                }

                if ($existe) {
                    $descUpdate = $description ?: ($status === 'deceased' ? 'Fallecido.' : 'Localizado en centro de salud.');
                    $existe->update([
                        'status' => $status,
                        'found_at' => now(),
                        'found_location' => $hospital,
                        'description' => $existe->description . "\n\n[IMPORTACIÓN EXCEL]: Actualizado como ingresado en " . $hospital . " (" . ($status === 'deceased' ? 'Fallecido' : 'Estable') . "). Detalle: " . $descUpdate,
                    ]);
                    $updated++;
                } else {
                    Desaparecido::create([
                        'code' => 'HOSP-' . strtoupper(substr(md5($fullName . uniqid()), 0, 6)),
                        'full_name' => $fullName,
                        'cedula' => $cedula,
                        'age' => $age,
                        'gender' => $gender,
                        'status' => $status,
                        'found_at' => now(),
                        'found_location' => $hospital,
                        'last_seen_location' => 'Venezuela (Sismo)',
                        'description' => $description ?: ($status === 'deceased'
                            ? "Registrado como ingresado en centro de salud tras evento sismico (Fallecido). Ubicacion: {$hospital}."
                            : "Registrado como ingresado en centro de salud tras evento sismico. Ubicacion: {$hospital}."),
                    ]);
                    $imported++;
                }
            }

            return redirect()->route('admin.importar.index')->with('import_summary', [
                'imported' => $imported,
                'updated' => $updated,
                'failed' => $failed
            ]);

        } catch (\Exception $e) {
            Log::error("Error importando archivo Excel/CSV: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Error al procesar el archivo: ' . $e->getMessage()]);
        }
    }

    private function matchMapping(string $key, array $possibilities): bool
    {
        $key = strtolower(trim($key));
        foreach ($possibilities as $possibility) {
            if ($key === $possibility) {
                return true;
            }
            // Only allow partial match for longer descriptive words
            if (strlen($possibility) > 3 && str_contains($key, $possibility)) {
                return true;
            }
        }
        return false;
    }
}

