@extends('admin.layout')

@section('title', 'Importar Casos — Admin')

@section('styles')
<style>
    .import-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .import-instructions {
        background-color: var(--bg-input);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-sm);
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .import-instructions h3 {
        font-family: var(--font-heading);
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .instructions-list {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        font-size: 0.9rem;
        color: var(--text-secondary);
    }

    .instructions-list li {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        line-height: 1.5;
    }

    .instructions-list li i {
        color: var(--accent-primary);
        margin-top: 0.2rem;
    }

    .upload-drag-area {
        border: 2px dashed var(--border-color);
        border-radius: var(--border-radius-md);
        padding: 3rem 2rem;
        text-align: center;
        background-color: var(--bg-main);
        cursor: pointer;
        transition: var(--transition-smooth);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1rem;
    }

    .upload-drag-area:hover,
    .upload-drag-area.dragover {
        border-color: var(--accent-primary);
        background-color: var(--accent-primary-glow);
    }

    .upload-icon {
        font-size: 3rem;
        color: var(--text-muted);
        transition: var(--transition-smooth);
    }

    .upload-drag-area:hover .upload-icon {
        color: var(--accent-primary);
        transform: scale(1.1);
    }

    .upload-title {
        font-family: var(--font-heading);
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .upload-meta {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .column-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
        font-size: 0.85rem;
    }

    .column-table th,
    .column-table td {
        border: 1px solid var(--border-color);
        padding: 8px 12px;
        text-align: left;
    }

    .column-table th {
        background-color: var(--bg-input);
        font-weight: 700;
        color: var(--text-primary);
    }

    .badge-req {
        background-color: var(--state-missing-glow);
        color: var(--state-missing);
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: bold;
        text-transform: uppercase;
    }

    .badge-opt {
        background-color: var(--bg-input);
        color: var(--text-muted);
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: bold;
        text-transform: uppercase;
    }

    .summary-box {
        margin-top: 1.5rem;
        padding: 1rem;
        border-radius: var(--border-radius-sm);
        border: 1px solid;
    }

    .summary-success {
        background-color: var(--state-found-glow);
        color: var(--state-found);
        border-color: rgba(22, 163, 74, 0.2);
    }
</style>
@endsection

@section('admin_content')
<div class="admin-header">
    <div class="admin-title">
        <h2>Importar Casos desde Excel / CSV</h2>
        <p>Carga por lotes personas y centros de salud correspondientes</p>
    </div>
    <div class="admin-actions">
        <a href="{{ route('admin.importar.plantilla') }}" class="btn btn-secondary">
            <i class="fa-solid fa-download"></i> Descargar Plantilla Modelo
        </a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-error">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span>
            <strong>Error de validación:</strong>
            <ul style="margin-left: 1.5rem; margin-top: 0.25rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </span>
    </div>
@endif

@if(session('import_summary'))
    <div class="import-card" style="border-color: var(--state-found); background-color: var(--state-found-glow); padding: 1.5rem; margin-bottom: 2rem;">
        <h3 style="font-family: var(--font-heading); font-size: 1.2rem; font-weight: 700; color: var(--state-found); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-circle-check"></i> Importación Finalizada con Éxito
        </h3>
        <ul style="list-style: none; font-size: 0.95rem; color: var(--text-secondary); display: flex; flex-direction: column; gap: 0.5rem;">
            <li><strong>Nuevos casos creados:</strong> +{{ session('import_summary.imported') }}</li>
            <li><strong>Casos existentes actualizados:</strong> +{{ session('import_summary.updated') }}</li>
            @if(session('import_summary.failed') > 0)
                <li style="color: var(--state-missing);"><strong>Casos omitidos/fallidos:</strong> {{ session('import_summary.failed') }} (Fila vacía o sin nombre completo/hospital)</li>
            @endif
        </ul>
    </div>
@endif

<div class="import-instructions">
    <h3><i class="fa-solid fa-circle-info"></i> Estructura y Reglas del Archivo</h3>
    <ul class="instructions-list">
        <li>
            <i class="fa-solid fa-check"></i>
            <span>Puedes subir un archivo en formato <strong>Excel (.xlsx, .xls)</strong> o de texto plano <strong>CSV (.csv)</strong>.</span>
        </li>
        <li>
            <i class="fa-solid fa-check"></i>
            <span>La primera fila del archivo debe contener los nombres de las columnas. El importador buscará las columnas indicadas abajo.</span>
        </li>
        <li>
            <i class="fa-solid fa-check"></i>
            <span>Si una persona ya existe en la base de datos (se busca por número de Cédula o coincidencia exacta de Nombre), el importador <strong>actualizará su estado y su ubicación</strong> con los datos del archivo en lugar de duplicarlo.</span>
        </li>
    </ul>

    <h4 style="margin-top: 1.5rem; font-size: 0.9rem; font-weight: 700; color: var(--text-primary);">Columnas admitidas en el archivo:</h4>
    <table class="column-table">
        <thead>
            <tr>
                <th>Columna sugerida</th>
                <th style="width: 100px;">Requisito</th>
                <th>Descripción / Ejemplo</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Nombre Completo</strong></td>
                <td><span class="badge-req">Obligatorio</span></td>
                <td>Nombre y Apellido de la persona. Ej: <code>Yordano Flores</code> o <code>Juan Pérez</code>.</td>
            </tr>
            <tr>
                <td><strong>Hospital / Ubicación</strong></td>
                <td><span class="badge-req">Obligatorio</span></td>
                <td>Centro de salud o sitio donde está. Ej: <code>Hospital Pérez Carreño</code> o <code>Cruz Roja</code>.</td>
            </tr>
            <tr>
                <td><strong>Cédula</strong></td>
                <td><span class="badge-opt">Opcional</span></td>
                <td>Cédula de Identidad de la persona (sólo números, sin puntos ni letras). Ej: <code>26372024</code>.</td>
            </tr>
            <tr>
                <td><strong>Edad</strong></td>
                <td><span class="badge-opt">Opcional</span></td>
                <td>Edad aproximada en números. Ej: <code>35</code>.</td>
            </tr>
            <tr>
                <td><strong>Género</strong></td>
                <td><span class="badge-opt">Opcional</span></td>
                <td>Género. Admite: <code>Masculino</code>, <code>Femenino</code>, <code>Otro</code> o <code>M</code> / <code>F</code>.</td>
            </tr>
            <tr>
                <td><strong>Detalles / Observaciones</strong></td>
                <td><span class="badge-opt">Opcional</span></td>
                <td>Detalles de salud, vestimenta o diagnóstico. Ej: <code>Triaje, condición estable.</code></td>
            </tr>
            <tr>
                <td><strong>Estado</strong></td>
                <td><span class="badge-opt">Opcional</span></td>
                <td>Estado de la persona. Admite: <code>Localizado</code> o <code>Fallecido</code> (por defecto es <code>Localizado</code>).</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="import-card">
    <form action="{{ route('admin.importar.post') }}" method="POST" enctype="multipart/form-data" id="import-form">
        @csrf
        <div class="upload-drag-area" id="drop-zone" onclick="document.getElementById('import-file').click()">
            <i class="fa-solid fa-file-excel upload-icon"></i>
            <span class="upload-title" id="file-label">Arrastra tu archivo aquí o haz clic para seleccionarlo</span>
            <span class="upload-meta">Admite archivos .xlsx, .xls y .csv de hasta 4MB</span>
            <input type="file" name="file" id="import-file" accept=".xlsx,.xls,.csv" style="display: none;" onchange="handleFileSelected(this)">
        </div>

        <div style="margin-top: 2rem; display: flex; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary" id="btn-submit" disabled>
                <i class="fa-solid fa-upload"></i> Procesar e Importar Casos
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    const dropZone = document.getElementById('drop-zone');
    const fileLabel = document.getElementById('file-label');
    const btnSubmit = document.getElementById('btn-submit');
    const importFile = document.getElementById('import-file');

    // Drag & Drop event handlers
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
        }, false);
    });

    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;

        if (files.length > 0) {
            importFile.files = files;
            handleFileSelected(importFile);
        }
    });

    function handleFileSelected(input) {
        if (input.files.length > 0) {
            const file = input.files[0];
            fileLabel.textContent = `Archivo seleccionado: ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
            btnSubmit.disabled = false;
        } else {
            fileLabel.textContent = 'Arrastra tu archivo aquí o haz clic para seleccionarlo';
            btnSubmit.disabled = true;
        }
    }
</script>
@endsection
