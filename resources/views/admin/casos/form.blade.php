@extends('admin.layout')

@section('title', ($caso->exists ? 'Editar Caso: ' . $caso->full_name : 'Registrar Nuevo Caso') . ' - Admin')

@section('styles')
<style>
    .form-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        padding: 2rem;
        margin-bottom: 2.5rem;
    }

    .form-container-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    @media (max-width: 768px) {
        .form-container-grid {
            grid-template-columns: 1fr;
        }
    }

    .form-group-full {
        grid-column: span 2;
    }

    @media (max-width: 768px) {
        .form-group-full {
            grid-column: span 1;
        }
    }

    .form-section-header {
        font-family: var(--font-heading);
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
        border-bottom: 1.5px solid var(--border-color);
        padding-bottom: 0.5rem;
        margin-top: 1.5rem;
        margin-bottom: 1.25rem;
        grid-column: span 2;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    @media (max-width: 768px) {
        .form-section-header {
            grid-column: span 1;
        }
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-group label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 0.75rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-sm);
        background-color: var(--bg-input);
        color: var(--text-primary);
        font-family: var(--font-sans);
        font-size: 0.95rem;
        outline: none;
        transition: var(--transition-smooth);
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: var(--accent-primary);
        background-color: var(--bg-card);
        box-shadow: 0 0 0 3px var(--accent-primary-glow);
    }

    .photo-preview-box {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        background-color: var(--bg-input);
        padding: 1rem;
        border-radius: var(--border-radius-sm);
        border: 1px solid var(--border-color);
    }

    .photo-preview-img {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        object-fit: cover;
        background-color: var(--border-color);
    }

    .btn-footer {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 2rem;
        border-top: 1px solid var(--border-color);
        padding-top: 1.5rem;
    }
</style>
@endsection

@section('admin_content')
<div class="admin-header">
    <div class="admin-title">
        <h2>{{ $caso->exists ? 'Editar Caso de Desaparecido' : 'Registrar Nuevo Caso' }}</h2>
        <p>{{ $caso->exists ? "Modifica los datos del caso código #{$caso->id}" : 'Ingresa la información detallada para publicar un nuevo reporte' }}</p>
    </div>
    <div class="admin-actions">
        <a href="{{ route('admin.casos.index') }}" class="btn btn-secondary" style="text-decoration: none;">
            <i class="fa-solid fa-arrow-left"></i> Volver a la Lista
        </a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-error">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <div>
            <strong style="display: block; margin-bottom: 0.25rem;">Por favor corrige los siguientes errores:</strong>
            <ul style="list-style: square; padding-left: 1.25rem; font-size: 0.85rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="form-card">
    <form action="{{ $caso->exists ? route('admin.casos.update', $caso->id) : route('admin.casos.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-container-grid">
            <!-- ----------------------------------------- -->
            <!-- SECCIÓN 1: Datos Básicos del Caso         -->
            <!-- ----------------------------------------- -->
            <div class="form-section-header" style="margin-top: 0;">
                <i class="fa-solid fa-user-tag"></i> Datos de la Persona Desaparecida
            </div>

            <div class="form-group">
                <label for="full_name">Nombre Completo *</label>
                <input type="text" id="full_name" name="full_name" value="{{ old('full_name', $caso->full_name) }}" required placeholder="Nombre y Apellido">
            </div>

            <div class="form-group">
                <label for="alias">Alias / Apodo</label>
                <input type="text" id="alias" name="alias" value="{{ old('alias', $caso->alias) }}" placeholder="Ej. El Chino">
            </div>

            <div class="form-group">
                <label for="cedula">Cédula de Identidad (Opcional)</label>
                <input type="text" id="cedula" name="cedula" value="{{ old('cedula', $caso->cedula) }}" placeholder="Si no se coloca, se extraerá de la descripción">
            </div>

            <div class="form-group">
                <label for="age">Edad aproximada</label>
                <input type="number" id="age" name="age" min="0" max="120" value="{{ old('age', $caso->age) }}" placeholder="Ej. 28">
            </div>

            <div class="form-group">
                <label for="gender">Género</label>
                <select id="gender" name="gender">
                    <option value="">Seleccione...</option>
                    <option value="Masculino" {{ old('gender', $caso->gender) === 'Masculino' ? 'selected' : '' }}>Masculino</option>
                    <option value="Femenino" {{ old('gender', $caso->gender) === 'Femenino' ? 'selected' : '' }}>Femenino</option>
                    <option value="Otro" {{ old('gender', $caso->gender) === 'Otro' ? 'selected' : '' }}>Otro</option>
                </select>
            </div>

            <div class="form-group">
                <label for="last_seen_at">Fecha en que fue visto por última vez</label>
                <input type="date" id="last_seen_at" name="last_seen_at" value="{{ old('last_seen_at', $caso->last_seen_at ? $caso->last_seen_at->format('Y-m-d') : '') }}">
            </div>

            <div class="form-group-full form-group">
                <label for="last_seen_location">Lugar donde fue visto por última vez *</label>
                <input type="text" id="last_seen_location" name="last_seen_location" value="{{ old('last_seen_location', $caso->last_seen_location) }}" required placeholder="Dirección exacta, punto de referencia, etc.">
            </div>

            <div class="form-group">
                <label for="city">Ciudad</label>
                <input type="text" id="city" name="city" value="{{ old('city', $caso->city) }}" placeholder="Ej. Barquisimeto">
            </div>

            <div class="form-group">
                <label for="state">Estado / Provincia</label>
                <input type="text" id="state" name="state" value="{{ old('state', $caso->state) }}" placeholder="Ej. Lara">
            </div>

            <div class="form-group-full form-group">
                <label for="photo">Fotografía del desaparecido</label>
                <div class="photo-preview-box">
                    @if($caso->photo_path)
                        <img src="{{ $caso->photo_path }}" alt="Preview" class="photo-preview-img" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\' fill=\'%2378716c\'><rect width=\'100\' height=\'100\' fill=\'%23f5f5f4\'/><circle cx=\'50\' cy=\'40\' r=\'20\'/><path d=\'M20,90 C20,70 40,60 50,60 C60,60 80,70 80,90 Z\'/></svg>';">
                        <div>
                            <span style="font-size: 0.85rem; font-weight:600; display:block; color:var(--text-secondary);">Cambiar Imagen</span>
                            <span style="font-size: 0.75rem; color:var(--text-muted);">Formatos aceptados: JPG, PNG, WEBP (Max: 2MB)</span>
                        </div>
                    @else
                        <div class="photo-placeholder" style="width:80px; height:80px; font-size: 1.8rem; border-radius: 8px;">
                            <i class="fa-regular fa-image"></i>
                        </div>
                        <div>
                            <span style="font-size: 0.85rem; font-weight:600; display:block; color:var(--text-secondary);">Subir Imagen</span>
                            <span style="font-size: 0.75rem; color:var(--text-muted);">Formatos aceptados: JPG, PNG, WEBP (Max: 2MB)</span>
                        </div>
                    @endif
                    <input type="file" id="photo" name="photo" accept="image/*" style="border: none; background: none; padding:0; flex: 1;">
                </div>
            </div>

            <div class="form-group-full form-group">
                <label for="description">Descripción Física y del Caso *</label>
                <textarea id="description" name="description" rows="5" required placeholder="Describe detalles físicos relevantes, ropa que llevaba puesta, señas particulares y cualquier dato útil para la búsqueda.">{{ old('description', $caso->description) }}</textarea>
            </div>

            <!-- ----------------------------------------- -->
            <!-- SECCIÓN 2: Datos de Localización (Solo Edit)-->
            <!-- ----------------------------------------- -->
            @if($caso->exists)
                <div class="form-section-header">
                    <i class="fa-solid fa-flag-checkered"></i> Estado de Localización del Caso
                </div>

                <div class="form-group">
                    <label for="status">Estado del Caso *</label>
                    <select id="status" name="status" onchange="toggleFoundFields()">
                        <option value="missing" {{ old('status', $caso->status) === 'missing' ? 'selected' : '' }}>Desaparecido (Activo)</option>
                        <option value="found" {{ old('status', $caso->status) === 'found' ? 'selected' : '' }}>Localizado (Resuelto)</option>
                        <option value="deceased" {{ old('status', $caso->status) === 'deceased' ? 'selected' : '' }}>Fallecido</option>
                    </select>
                </div>

                <div class="form-group found-field">
                    <label for="found_at">Fecha de Localización</label>
                    <input type="date" id="found_at" name="found_at" value="{{ old('found_at', $caso->found_at ? $caso->found_at->format('Y-m-d') : '') }}">
                </div>

                <div class="form-group-full form-group found-field">
                    <label for="found_location">Ubicación donde fue localizado</label>
                    <input type="text" id="found_location" name="found_location" value="{{ old('found_location', $caso->found_location) }}" placeholder="Ej. Encontrado sano y salvo en San Cristóbal">
                </div>
            @endif

            <!-- ----------------------------------------- -->
            <!-- SECCIÓN 3: Contacto / Informante           -->
            <!-- ----------------------------------------- -->
            <div class="form-section-header">
                <i class="fa-solid fa-address-book"></i> Datos del Informante (Contacto interno)
            </div>

            <div class="form-group">
                <label for="reporter_name">Nombre de Contacto</label>
                <input type="text" id="reporter_name" name="reporter_name" value="{{ old('reporter_name', $caso->reporter_name) }}" placeholder="Nombre de familiar o testigo">
            </div>

            <div class="form-group">
                <label for="reporter_phone">Número de Teléfono</label>
                <input type="text" id="reporter_phone" name="reporter_phone" value="{{ old('reporter_phone', $caso->reporter_phone) }}" placeholder="Ej. 0424-1234567">
            </div>

            <div class="form-group">
                <label for="reporter_email">Correo Electrónico</label>
                <input type="email" id="reporter_email" name="reporter_email" value="{{ old('reporter_email', $caso->reporter_email) }}" placeholder="ejemplo@correo.com">
            </div>

            <div class="form-group">
                <label for="relationship">Relación / Parentesco con el Desaparecido</label>
                <input type="text" id="relationship" name="relationship" value="{{ old('relationship', $caso->relationship) }}" placeholder="Ej. Madre, Amigo, Vecino">
            </div>

        </div>

        <!-- Botones de Acción -->
        <div class="btn-footer">
            <a href="{{ route('admin.casos.index') }}" class="btn btn-secondary" style="text-decoration: none;">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> Guardar Caso
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    function toggleFoundFields() {
        const statusSelect = document.getElementById('status');
        const foundFields = document.querySelectorAll('.found-field');
        
        if (statusSelect && statusSelect.value === 'found') {
            foundFields.forEach(el => {
                el.style.display = 'flex';
                const input = el.querySelector('input');
                if (input && input.id === 'found_at' && !input.value) {
                    // Poner fecha de hoy por defecto si está vacío
                    const today = new Date().toISOString().split('T')[0];
                    input.value = today;
                }
            });
        } else {
            foundFields.forEach(el => {
                el.style.display = 'none';
            });
        }
    }

    // Inicializar al cargar
    document.addEventListener('DOMContentLoaded', function() {
        toggleFoundFields();
    });
</script>
@endsection
