@extends('admin.layout')

@section('title', 'Gestionar Casos - Buscar Desaparecidos')

@section('styles')
<style>
    .filter-bar {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-md);
        padding: 1.25rem 1.5rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-sm);
    }

    .filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: flex-end;
    }

    .filter-item {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .filter-item label {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .filter-item input,
    .filter-item select {
        padding: 0.6rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-sm);
        background-color: var(--bg-input);
        color: var(--text-primary);
        font-family: var(--font-sans);
        font-size: 0.9rem;
        outline: none;
        min-width: 180px;
    }

    .filter-item input:focus,
    .filter-item select:focus {
        border-color: var(--accent-primary);
        background-color: var(--bg-card);
    }

    .table-container {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-md);
        overflow-x: auto;
        box-shadow: var(--shadow-sm);
        margin-bottom: 2rem;
    }

    .admin-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.9rem;
    }

    .admin-table th {
        background-color: var(--bg-input);
        color: var(--text-secondary);
        font-weight: 700;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border-color);
    }

    .admin-table td {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
    }

    .admin-table tr:last-child td {
        border-bottom: none;
    }

    .admin-table tr:hover {
        background-color: rgba(0,0,0,0.01);
    }

    .admin-photo-thumb {
        width: 44px;
        height: 44px;
        border-radius: 6px;
        object-fit: cover;
        background-color: var(--border-color);
    }

    .photo-placeholder {
        width: 44px;
        height: 44px;
        border-radius: 6px;
        background-color: var(--bg-input);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        font-size: 1.1rem;
        border: 1px dashed var(--border-color);
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .badge-status {
        padding: 0.35rem 0.75rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .badge-status-missing {
        background-color: var(--state-missing-glow);
        color: var(--state-missing);
    }

    .badge-status-found {
        background-color: var(--state-found-glow);
        color: var(--state-found);
    }

    .badge-source {
        font-size: 0.75rem;
        color: var(--text-muted);
        border: 1px solid var(--border-color);
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        background-color: var(--bg-input);
    }

    .badge-source-local {
        border-color: rgba(37, 99, 235, 0.2);
        background-color: var(--accent-primary-glow);
        color: var(--accent-primary);
    }

    .admin-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        background-color: var(--bg-card);
        border-top: 1px solid var(--border-color);
    }

    .pagination-links {
        display: flex;
        gap: 0.25rem;
    }

    .pagination-links a,
    .pagination-links span {
        padding: 0.5rem 0.85rem;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        text-decoration: none;
        color: var(--text-secondary);
        font-size: 0.85rem;
    }

    .pagination-links a:hover {
        background-color: var(--bg-input);
        color: var(--text-primary);
    }

    .pagination-links .active {
        background-color: var(--accent-primary);
        color: white;
        border-color: var(--accent-primary);
    }

    .pagination-links .disabled {
        color: var(--text-muted);
        background-color: var(--bg-input);
        cursor: not-allowed;
    }

    @media (max-width: 768px) {
        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }
        .filter-item {
            flex: unset !important;
            width: 100%;
        }
        .filter-item input,
        .filter-item select {
            min-width: 0;
            width: 100%;
        }
        .action-buttons-filter {
            display: flex;
            gap: 0.5rem;
            width: 100%;
        }
        .action-buttons-filter button,
        .action-buttons-filter a {
            flex: 1;
            text-align: center;
            justify-content: center;
        }
        .admin-pagination {
            flex-direction: column;
            gap: 1rem;
            align-items: center;
            text-align: center;
        }
    }
</style>
@endsection

@section('admin_content')
<div class="admin-header">
    <div class="admin-title">
        <h2>Casos Reportados</h2>
        <p>Listado general e interactivo de desaparecidos y localizados</p>
    </div>
    <div class="admin-actions">
        <a href="{{ route('admin.casos.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-user-plus"></i> Registrar Caso
        </a>
    </div>
</div>

<!-- Filtros de búsqueda y origen -->
<div class="filter-bar">
    <form action="{{ route('admin.casos.index') }}" method="GET" class="filter-form">
        <div class="filter-item" style="flex: 2; min-width: 250px;">
            <label for="search">Buscar Persona:</label>
            <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Nombre, alias, cédula, ubicación...">
        </div>

        <div class="filter-item">
            <label for="status">Estado:</label>
            <select id="status" name="status">
                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Todos los Estados</option>
                <option value="missing" {{ request('status') === 'missing' ? 'selected' : '' }}>Desaparecido</option>
                <option value="found" {{ request('status') === 'found' ? 'selected' : '' }}>Localizado</option>
                <option value="deceased" {{ request('status') === 'deceased' ? 'selected' : '' }}>Fallecido</option>
            </select>
        </div>

        <div class="filter-item">
            <label for="source">Origen:</label>
            <select id="source" name="source">
                <option value="all" {{ request('source') === 'all' ? 'selected' : '' }}>Todos los Orígenes</option>
                <option value="local" {{ request('source') === 'local' ? 'selected' : '' }}>Creado en Local</option>
                <option value="external" {{ request('source') === 'external' ? 'selected' : '' }}>Importado de Web</option>
            </select>
        </div>

        <div class="action-buttons-filter">
            <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.25rem;">
                <i class="fa-solid fa-filter"></i> Filtrar
            </button>
            <a href="{{ route('admin.casos.index') }}" class="btn btn-secondary" style="padding: 0.65rem 1.25rem; text-decoration: none; display: inline-block;">
                Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Tabla del CRUD -->
<div class="table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th style="width: 70px;">Foto</th>
                <th>Nombre Completo</th>
                <th>Cédula</th>
                <th>Última Ubicación</th>
                <th>Origen</th>
                <th>Estado</th>
                <th style="text-align: right; width: 200px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($casos as $caso)
                <tr>
                    <td>
                        @if($caso->photo_path)
                            <img src="{{ $caso->photo_path }}" alt="Foto" class="admin-photo-thumb" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\' fill=\'%2378716c\'><rect width=\'100\' height=\'100\' fill=\'%23f5f5f4\'/><circle cx=\'50\' cy=\'40\' r=\'20\'/><path d=\'M20,90 C20,70 40,60 50,60 C60,60 80,70 80,90 Z\'/></svg>';">
                        @else
                            <div class="photo-placeholder">
                                <i class="fa-regular fa-image"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <strong style="color: var(--text-primary); font-size: 0.95rem;">{{ $caso->full_name }}</strong>
                        @if($caso->alias)
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Apodo: "{{ $caso->alias }}"</div>
                        @endif
                    </td>
                    <td>
                        {{ $caso->cedula ?? 'No indicada' }}
                    </td>
                    <td>
                        <span style="font-size: 0.85rem; color: var(--text-secondary);">{{ $caso->last_seen_location }}</span>
                        @if($caso->city || $caso->state)
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ implode(', ', array_filter([$caso->city, $caso->state])) }}</div>
                        @endif
                    </td>
                    <td>
                        @if($caso->external_id)
                            <span class="badge-source">Importado</span>
                        @else
                            <span class="badge-source badge-source-local">Local</span>
                        @endif
                    </td>
                    <td>
                        @if($caso->status === 'found')
                            <span class="badge-status badge-status-found">
                                <i class="fa-solid fa-circle-check"></i> Localizado
                            </span>
                        @elseif($caso->status === 'deceased')
                            <span class="badge-status" style="background-color: #44403c; color: #fff; padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fa-solid fa-skull-crossbones"></i> Fallecido
                            </span>
                        @else
                            <span class="badge-status badge-status-missing">
                                <i class="fa-solid fa-circle-xmark"></i> Desaparecido
                            </span>
                        @endif
                    </td>
                    <td>
                        <div class="action-buttons" style="justify-content: flex-end;">
                            <a href="{{ route('admin.casos.edit', $caso->id) }}" class="btn btn-secondary btn-sm" style="padding: 0.4rem 0.75rem;" title="Editar caso">
                                <i class="fa-solid fa-pen-to-square"></i> Editar
                            </a>
                            <form action="{{ route('admin.casos.destroy', $caso->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este caso permanentemente? Esta acción borrará el registro de la base de datos y su foto asociada.');">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.4rem 0.75rem; color: var(--state-missing); border-color: rgba(220,38,38,0.2);" title="Eliminar caso">
                                    <i class="fa-solid fa-trash-can"></i> Borrar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                        <i class="fa-regular fa-face-frown" style="font-size: 2.5rem; margin-bottom: 1rem; display: block;"></i>
                        No se encontraron registros que coincidan con los filtros.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Paginación -->
    @if($casos->hasPages())
        <div class="admin-pagination">
            <span style="font-size: 0.85rem; color: var(--text-muted);">
                Mostrando {{ $casos->firstItem() }} - {{ $casos->lastItem() }} de {{ $casos->total() }} casos
            </span>
            <div class="pagination-links">
                {{-- Previous Page Link --}}
                @if ($casos->onFirstPage())
                    <span class="disabled">&laquo; Anterior</span>
                @else
                    <a href="{{ $casos->previousPageUrl() }}" rel="prev">&laquo; Anterior</a>
                @endif

                {{-- Next Page Link --}}
                @if ($casos->hasMorePages())
                    <a href="{{ $casos->nextPageUrl() }}" rel="next">Siguiente &raquo;</a>
                @else
                    <span class="disabled">Siguiente &raquo;</span>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
