<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Desaparecido extends Model
{
    protected $table = 'desaparecidos';

    protected $fillable = [
        'external_id',
        'code',
        'full_name',
        'alias',
        'cedula',
        'age',
        'gender',
        'last_seen_at',
        'last_seen_location',
        'city',
        'state',
        'description',
        'photo_path',
        'photo_hash',
        'reporter_name',
        'reporter_phone',
        'reporter_email',
        'relationship',
        'status',
        'found_at',
        'found_location',
        'source_url'
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'found_at' => 'datetime',
        'age' => 'integer',
        'external_id' => 'integer',
    ];

    /**
     * Extrae un posible número de cédula del texto de la descripción.
     * Retorna la cédula limpia (solo dígitos) o null si no se encuentra.
     */
    public static function extractCedula(?string $text): ?string
    {
        if (empty($text)) {
            return null;
        }

        // Regex para buscar patrones comunes de cédula:
        // C.I. 12.345.678, CI: 12345678, cédula v-12345678, v-12.345.678, etc.
        $pattern = '/(?:c\.?\s*i\.?\s*|cédula\s*(?:de\s*identidad)?\s*|cedula\s*(?:de\s*identidad)?\s*|v\s*[-–])\s*[:\-\.=\s]*\s*(?:[veVE]\s*[-–]?\s*)?([0-9]{1,3}(?:\.?[0-9]{3}){2}|[0-9]{6,9})/i';

        if (preg_match($pattern, $text, $matches)) {
            // Limpiamos los puntos, guiones y espacios para guardar solo dígitos
            $clean = preg_replace('/[^0-9]/', '', $matches[1]);
            if (strlen($clean) >= 6 && strlen($clean) <= 10) {
                return $clean;
            }
        }

        return null;
    }
}
