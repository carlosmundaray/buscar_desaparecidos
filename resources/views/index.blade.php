@extends('layouts.layout')

@section('title', 'Buscar Desaparecidos Venezuela — Personas tras Terremoto y Sismo')

@section('content')
<div class="app-container">
    <!-- Navbar Premium -->
    <header class="main-header">
        <div class="logo-area">
            <div class="pulse-indicator-red"></div>
            <h1>Buscar<span>Desaparecidos</span></h1>
        </div>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="openReportModal()">
                <i class="fa-solid fa-plus"></i> Reportar Caso
            </button>
        </div>
    </header>

    <!-- Banner Descriptivo SEO Terremoto / Sismo Venezuela -->
    <div class="seo-banner-card" style="background-color: var(--accent-primary-glow); border: 1px solid rgba(37, 99, 235, 0.15); border-radius: var(--border-radius-md); padding: 1.25rem 1.5rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem;">
        <i class="fa-solid fa-house-chimney-crack" style="font-size: 1.8rem; color: var(--accent-primary); flex-shrink: 0;"></i>
        <div>
            <h2 style="font-family: var(--font-heading); font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem;">Búsqueda de Desaparecidos en Venezuela por Terremoto y Sismo</h2>
            <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.4; margin: 0;">Canal de información solidario y unificado para reportar o buscar personas en Venezuela tras sismos, temblores o contingencias sísmicas. Consulta el registro de inmediato.</p>
        </div>
    </div>

    <!-- Stats Quick Cards -->
    <section class="stats-section">
        <div class="stat-card" data-filter="all" onclick="setFilter('all')">
            <div class="stat-icon icon-reported"><i class="fa-solid fa-users"></i></div>
            <div class="stat-info">
                <span class="stat-label">Total Reportados</span>
                <h3 class="stat-value" id="count-reported">{{ $stats['reported'] }}</h3>
            </div>
        </div>
        <div class="stat-card" data-filter="missing" onclick="setFilter('missing')">
            <div class="stat-icon icon-missing"><i class="fa-solid fa-user-xmark"></i></div>
            <div class="stat-info">
                <span class="stat-label">Activos Desaparecidos</span>
                <h3 class="stat-value" id="count-missing">{{ $stats['missing'] }}</h3>
            </div>
        </div>
        <div class="stat-card" data-filter="found" onclick="setFilter('found')">
            <div class="stat-icon icon-found"><i class="fa-solid fa-user-check"></i></div>
            <div class="stat-info">
                <span class="stat-label">Personas Localizadas</span>
                <h3 class="stat-value" id="count-found">{{ $stats['found'] }}</h3>
            </div>
        </div>
        <div class="stat-card" data-filter="deceased" onclick="setFilter('deceased')">
            <div class="stat-icon" style="background-color: rgba(68, 64, 60, 0.1); color: #44403c;"><i class="fa-solid fa-skull-crossbones"></i></div>
            <div class="stat-info">
                <span class="stat-label">Fallecidos</span>
                <h3 class="stat-value" id="count-deceased">{{ $stats['deceased'] }}</h3>
            </div>
        </div>
        <div class="stat-card" data-filter="hospitalized" onclick="setFilter('hospitalized')">
            <div class="stat-icon icon-hospital"><i class="fa-solid fa-hospital"></i></div>
            <div class="stat-info">
                <span class="stat-label">En Hospitales (Drive)</span>
                <h3 class="stat-value" id="count-hospitalized">{{ $stats['hospitalized'] }}</h3>
            </div>
        </div>
    </section>

    <!-- Barra de Búsqueda y Filtros -->
    <section class="search-section">
        <div class="search-wrapper">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" id="search-input" placeholder="Buscar por nombre, apellido, cédula de identidad..." autocomplete="off">
            <div class="search-actions-inside">
                <button class="clear-search-btn" id="clear-search" style="display: none;" title="Limpiar búsqueda">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <button class="photo-search-trigger-btn" id="photo-search-trigger" onclick="openPhotoSearchModal()" title="Buscar por foto">
                    <i class="fa-solid fa-camera"></i>
                </button>
            </div>
        </div>

        <!-- Banner de Búsqueda por Foto Activa -->
        <div class="photo-search-banner" id="photo-search-banner" style="display: none;">
            <div class="photo-search-banner-text">
                <i class="fa-solid fa-images"></i>
                <span>Búsqueda por Foto activa. Mostrando coincidencias visuales.</span>
            </div>
            <button class="btn btn-secondary btn-sm" onclick="clearPhotoSearch()">
                <i class="fa-solid fa-rotate-left"></i> Quitar Foto
            </button>
        </div>

        <div class="filters-wrapper">
            <div class="filter-group">
                <span class="filters-title">Estado:</span>
                <div class="filter-tabs">
                    <button class="filter-tab active" data-status="all" onclick="setStatusFilter('all')">Todos</button>
                    <button class="filter-tab" data-status="missing" onclick="setStatusFilter('missing')">Desaparecidos</button>
                    <button class="filter-tab" data-status="found" onclick="setStatusFilter('found')">Localizados</button>
                    <button class="filter-tab" data-status="deceased" onclick="setStatusFilter('deceased')"><i class="fa-solid fa-skull-crossbones"></i> Fallecidos</button>
                    <button class="filter-tab" data-status="hospitalized" onclick="setStatusFilter('hospitalized')"><i class="fa-solid fa-hospital"></i> En Hospitales</button>
                </div>
            </div>

            <div class="filter-group">
                <span class="filters-title">Foto:</span>
                <div class="filter-tabs">
                    <button class="filter-tab filter-tab-photo active" data-photo="all" onclick="setPhotoFilter('all')">Cualquiera</button>
                    <button class="filter-tab filter-tab-photo" data-photo="yes" onclick="setPhotoFilter('yes')">Con Foto</button>
                    <button class="filter-tab filter-tab-photo" data-photo="no" onclick="setPhotoFilter('no')">Sin Foto</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Grilla de Resultados y Recursos Colaterales -->
    <div class="main-layout-split">
        <main class="results-container">
            <div class="grid-header">
                <h2 id="results-title">Casos Recientes</h2>
                <span class="results-count" id="results-count">Mostrando...</span>
            </div>

            <div class="loading-overlay" id="loading-spinner" style="display: none;">
                <div class="spinner"></div>
                <p>Buscando en la base de datos...</p>
            </div>

            <div class="results-grid" id="results-grid">
                <!-- Las tarjetas se cargarán dinámicamente con JavaScript -->
            </div>

            <!-- Sin Resultados -->
            <div class="no-results" id="no-results" style="display: none;">
                <i class="fa-regular fa-face-frown"></i>
                <h3>No se encontraron resultados</h3>
                <p>Intenta ajustar tus criterios de búsqueda o limpia los filtros.</p>
            </div>

            <!-- Paginación -->
            <div class="pagination-area" id="pagination-area" style="display: none;">
                <button class="btn btn-secondary btn-sm" id="prev-page-btn" onclick="changePage(-1)">
                    <i class="fa-solid fa-chevron-left"></i> Anterior
                </button>
                <span class="page-indicator" id="page-indicator">Página 1 de 1</span>
                <button class="btn btn-secondary btn-sm" id="next-page-btn" onclick="changePage(1)">
                    Siguiente <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
        </main>

        <aside class="sidebar-resources">
            <!-- Tarjeta de Recursos -->
            <div class="resource-card">
                <div class="resource-card-header">
                    <h3><i class="fa-solid fa-hand-holding-heart"></i> Recursos de Emergencia</h3>
                </div>
                <div class="resource-card-body">
                    <h4 class="resource-title"><i class="fa-solid fa-box-open" style="color: var(--accent-secondary);"></i> Centros de Acopio en Caracas y Miranda</h4>
                    <ul class="resource-list">
                        <li>
                            <strong>Cruz Roja Venezolana:</strong> Av. Andrés Bello, Urb. San Bernardino, Caracas. <span style="color: var(--text-muted);">Tlf: 0212-5714380</span><br>
                            <span class="resource-meta">(Recibe: Agua, alimentos no perecederos, medicinas, pañales y herramientas de rescate)</span>
                        </li>
                        <li>
                            <strong>Centro Altamira (Quinta El Bejucal):</strong> 4ta Av. de Altamira, entre 9na y 10ma transversal, Caracas.<br>
                            <span class="resource-meta">(Recibe: Agua potable, enlatados, ropa de abrigo y frazadas)</span>
                        </li>
                        <li>
                            <strong>Iglesia La Chiquinquirá:</strong> Urbanización La Florida, Caracas.<br>
                            <span class="resource-meta">(Recibe: Alimentos listos para consumo, agua embotellada e insumos de higiene)</span>
                        </li>
                        <li>
                            <strong>Complejo Cultural Los Salias:</strong> San Antonio de los Altos, Miranda.<br>
                            <span class="resource-meta">(Recibe: Ropa de abrigo, linternas, baterías y agua potable)</span>
                        </li>
                    </ul>

                    <h4 class="resource-title"><i class="fa-solid fa-building-shield" style="color: var(--state-found);"></i> Oficina de Coordinación</h4>
                    <div class="office-box">
                        <strong>Protección Civil Nacional</strong><br>
                        <span class="office-address">Av. Rufino Blanco Fombona, Santa Mónica, Caracas.</span>
                        <span class="office-phone"><i class="fa-solid fa-phone"></i> 0212-6627671 / 0800-7248451</span>
                        <span class="emergency-badge"><i class="fa-solid fa-phone-volume"></i> Emergencias Sismo: 911</span>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <!-- Estilos de Soporte para el Sidebar Responsivo -->
    <style>
        .main-layout-split {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            align-items: start;
        }

        @media (min-width: 992px) {
            .main-layout-split {
                grid-template-columns: 2.8fr 1.2fr;
            }
        }

        .sidebar-resources {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .resource-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .resource-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            background-color: rgba(37, 99, 235, 0.03);
        }

        .resource-card-header h3 {
            font-family: var(--font-heading);
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .resource-card-header h3 i {
            color: var(--accent-primary);
        }

        .resource-card-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .resource-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .resource-title i {
            font-size: 0.95rem;
        }

        .resource-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .resource-list li {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 0.6rem;
            font-size: 0.85rem;
            color: var(--text-secondary);
            line-height: 1.45;
        }

        .resource-list li:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .resource-meta {
            color: var(--text-muted);
            font-size: 0.75rem;
            display: block;
            margin-top: 0.2rem;
        }

        .office-box {
            background-color: var(--bg-input);
            padding: 1rem;
            border-radius: var(--border-radius-sm);
            border: 1px solid var(--border-color);
            font-size: 0.85rem;
            line-height: 1.45;
            color: var(--text-secondary);
        }

        .office-address {
            font-size: 0.8rem;
            display: block;
            margin-top: 0.25rem;
            margin-bottom: 0.25rem;
        }

        .office-phone {
            font-size: 0.8rem;
            color: var(--text-muted);
            display: block;
        }

        .emergency-badge {
            color: var(--state-missing);
            font-weight: 700;
            font-size: 0.8rem;
            display: block;
            margin-top: 0.5rem;
        }
    </style>

    <!-- Footer -->
    <footer class="main-footer">
        <p>&copy; {{ date('Y') }} Buscar Desaparecidos. Plataforma Solidaria y de Búsqueda Inmediata. Datos importados y actualizados desde <a href="https://buscardesaparecidos.com" target="_blank" rel="noopener">buscardesaparecidos.com</a>.</p>
    </footer>
</div>

<!-- ================= MODALES ================= -->

<!-- 1. Modal: Reportar Caso -->
<div class="modal-overlay" id="report-modal">
    <div class="modal-card">
        <div class="modal-header">
            <h3><i class="fa-solid fa-user-plus"></i> Reportar Persona Desaparecida</h3>
            <button class="close-modal-btn" onclick="closeModal('report-modal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="report-form" onsubmit="submitReportForm(event)" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="form-section-title">Datos de la Persona Desaparecida</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="full_name">Nombre y Apellido completo *</label>
                        <input type="text" id="full_name" name="full_name" required placeholder="Ej. Juan Pérez">
                    </div>
                    <div class="form-group">
                        <label for="alias">Alias / Apodo</label>
                        <input type="text" id="alias" name="alias" placeholder="Ej. El Negro">
                    </div>
                    <div class="form-group">
                        <label for="cedula">Cédula de Identidad</label>
                        <input type="text" id="cedula" name="cedula" placeholder="Ej. 12345678">
                    </div>
                    <div class="form-group">
                        <label for="age">Edad</label>
                        <input type="number" id="age" name="age" min="0" max="120" placeholder="Ej. 25">
                    </div>
                    <div class="form-group">
                        <label for="gender">Género</label>
                        <select id="gender" name="gender">
                            <option value="">Seleccione...</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Femenino">Femenino</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="last_seen_at">Visto por última vez (Fecha)</label>
                        <input type="date" id="last_seen_at" name="last_seen_at">
                    </div>
                    <div class="form-group col-span-2">
                        <label for="last_seen_location">Lugar donde fue visto por última vez *</label>
                        <input type="text" id="last_seen_location" name="last_seen_location" required placeholder="Ej. Av. Principal de Catia, Caracas">
                    </div>
                    <div class="form-group">
                        <label for="city">Ciudad</label>
                        <input type="text" id="city" name="city" placeholder="Ej. Caracas">
                    </div>
                    <div class="form-group">
                        <label for="state">Estado / Provincia</label>
                        <input type="text" id="state" name="state" placeholder="Ej. Distrito Capital">
                    </div>
                    <div class="form-group col-span-2">
                        <label for="photo">Subir Foto (Formatos: JPG, PNG | Máx. 2MB)</label>
                        <input type="file" id="photo" name="photo" accept="image/*">
                    </div>
                    <div class="form-group col-span-2">
                        <label for="description">Descripción física, vestimenta o detalles importantes *</label>
                        <textarea id="description" name="description" rows="4" required placeholder="Ej. Cabello negro, mide 1.70m, vestía camisa azul y jean. Cicatriz en el brazo derecho..."></textarea>
                    </div>
                </div>

                <div class="form-section-title">Datos del Informante (Contacto)</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="reporter_name">Tu Nombre y Apellido *</label>
                        <input type="text" id="reporter_name" name="reporter_name" required placeholder="Ej. María Pérez">
                    </div>
                    <div class="form-group">
                        <label for="reporter_phone">Número de Teléfono *</label>
                        <input type="text" id="reporter_phone" name="reporter_phone" required placeholder="Ej. 0412-1234567">
                    </div>
                    <div class="form-group">
                        <label for="reporter_email">Correo Electrónico</label>
                        <input type="email" id="reporter_email" name="reporter_email" placeholder="Ej. maria@correo.com">
                    </div>
                    <div class="form-group">
                        <label for="relationship">Relación / Parentesco con el desaparecido *</label>
                        <input type="text" id="relationship" name="relationship" required placeholder="Ej. Madre, Hermano, Amigo">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('report-modal')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Registrar Reporte</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Modal: Detalles del Caso -->
<div class="modal-overlay" id="detail-modal">
    <div class="modal-card modal-large">
        <div class="modal-header">
            <h3 id="detail-title">Cargando caso...</h3>
            <button class="close-modal-btn" onclick="closeModal('detail-modal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div class="detail-layout">
                <div class="detail-image-wrapper" onclick="zoomImage()" style="position: relative; cursor: pointer;">
                    <img id="detail-photo" src="" alt="Foto de persona desaparecida">
                    <span id="detail-badge" class="badge"></span>
                </div>
                <div class="detail-info-wrapper">
                    <div class="detail-meta-grid">
                        <div class="meta-item">
                            <span class="meta-label">Código:</span>
                            <span class="meta-val" id="detail-code"></span>
                        </div>
                        <div class="meta-item" id="detail-cedula-row">
                            <span class="meta-label">Cédula:</span>
                            <span class="meta-val" id="detail-cedula"></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Edad:</span>
                            <span class="meta-val" id="detail-age"></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Género:</span>
                            <span class="meta-val" id="detail-gender"></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Última vez visto:</span>
                            <span class="meta-val" id="detail-last-seen"></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Lugar:</span>
                            <span class="meta-val" id="detail-location"></span>
                        </div>
                    </div>

                    <div class="detail-description">
                        <h4>Descripción y Detalles:</h4>
                        <p id="detail-desc"></p>
                    </div>

                    <div class="detail-reporter-box" id="detail-reporter-info">
                        <h4>Datos del Informante:</h4>
                        <p><strong>Nombre:</strong> <span id="detail-rep-name"></span> (<span id="detail-rep-rel"></span>)</p>
                        <p><strong>Teléfono:</strong> <span id="detail-rep-phone"></span></p>
                        <p id="detail-rep-email-row"><strong>Correo:</strong> <span id="detail-rep-email"></span></p>
                    </div>

                    <div class="detail-source-box" id="detail-source-row">
                        <p><i class="fa-solid fa-link"></i> Fuente original: <a id="detail-source-link" href="" target="_blank">Ver caso en buscardesaparecidos.com</a></p>
                    </div>

                    <div class="detail-found-box" id="detail-found-row" style="display: none;">
                        <h4><i class="fa-solid fa-location-dot"></i> Información de Localización</h4>
                        <p><strong>Fecha:</strong> <span id="detail-found-at"></span></p>
                        <p><strong>Lugar:</strong> <span id="detail-found-location"></span></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer" id="detail-actions">
            <!-- Los botones se insertarán con JavaScript de acuerdo al estado -->
        </div>
    </div>
</div>

<!-- 4. Modal: Marcar Encontrado -->
<div class="modal-overlay" id="found-modal">
    <div class="modal-card">
        <div class="modal-header">
            <h3><i class="fa-solid fa-location-crosshairs"></i> Persona Localizada</h3>
            <button class="close-modal-btn" onclick="closeModal('found-modal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="found-form" onsubmit="submitFoundForm(event)">
            <input type="hidden" id="found-person-id">
            <div class="modal-body">
                <p>
                    Vas a registrar que <strong id="found-person-name"></strong> ha sido localizado/a.
                </p>
                <div class="form-group">
                    <label for="found_location">¿Dónde o bajo qué circunstancias fue localizado/a? (Opcional)</label>
                    <input type="text" id="found_location" placeholder="Ej. Caracas, sano y salvo con sus familiares">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('found-modal')">Cancelar</button>
                <button type="submit" class="btn btn-success">Confirmar Localización</button>
            </div>
        </form>
    </div>
</div>

<!-- 5. Modal: Búsqueda por Foto -->
<div class="modal-overlay" id="photo-search-modal">
    <div class="modal-card">
        <div class="modal-header">
            <h3><i class="fa-solid fa-camera"></i> Búsqueda por Foto</h3>
            <button class="close-modal-btn" onclick="closeModal('photo-search-modal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <p class="scrape-intro-text">
                Sube una foto clara de la persona. Nuestro sistema comparará las características visuales (hashes perceptuales) y te mostrará las personas más similares.
            </p>
            
            <div class="upload-drop-zone" id="upload-drop-zone" onclick="document.getElementById('photo-search-input').click()">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <p>Arrastra la foto aquí o haz clic para buscar en tus archivos</p>
                <span>Formatos soportados: JPG, JPEG, PNG, WEBP (Máx. 2MB)</span>
                <input type="file" id="photo-search-input" style="display: none;" accept="image/*" onchange="handlePhotoSearchSelect(this)">
            </div>

            <!-- Vista previa y carga -->
            <div class="upload-preview-wrapper" id="photo-search-preview-wrapper" style="display: none;">
                <img id="photo-search-preview" class="upload-preview" src="" alt="Vista previa de búsqueda">
                <button class="btn btn-primary" id="photo-search-submit-btn" onclick="submitPhotoSearch()">
                    <i class="fa-solid fa-magnifying-glass"></i> Comparar e Identificar
                </button>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('photo-search-modal')">Cerrar</button>
        </div>
    </div>
</div>

<!-- 6. Modal: Visualizador de Imagen en Pantalla Completa (Zoom) -->
<div class="modal-overlay" id="image-zoom-modal" style="z-index: 2000; background-color: rgba(0, 0, 0, 0.95);">
    <button onclick="closeModal('image-zoom-modal')" style="position: absolute; top: 1.5rem; right: 1.5rem; background-color: rgba(255,255,255,0.1); color: white; border: none; border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; cursor: pointer; transition: background-color 0.2s ease; z-index: 2010;">
        <i class="fa-solid fa-xmark"></i>
    </button>
    <div style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; padding: 1.5rem;" onclick="closeModal('image-zoom-modal')">
        <img id="zoomed-image" src="" alt="Foto completa" style="max-width: 95%; max-height: 95%; object-fit: contain; border-radius: var(--border-radius-md); box-shadow: 0 10px 30px rgba(0,0,0,0.5); cursor: zoom-out;">
    </div>
</div>

<style>
.detail-image-wrapper {
    position: relative;
    overflow: hidden;
    border-radius: var(--border-radius-md);
}
.detail-image-wrapper::after {
    content: "\f00e  Ampliar";
    font-family: "Font Awesome 6 Free", sans-serif;
    font-weight: 900;
    position: absolute;
    bottom: 12px;
    right: 12px;
    background-color: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    opacity: 0;
    transition: opacity 0.25s ease, transform 0.25s ease;
    pointer-events: none;
    display: flex;
    align-items: center;
    gap: 4px;
    transform: translateY(5px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}
.detail-image-wrapper:not(.no-zoom):hover::after {
    opacity: 1;
    transform: translateY(0);
}
.detail-image-wrapper.no-zoom {
    cursor: default !important;
}
.detail-image-wrapper.no-zoom::after {
    display: none !important;
}
</style>
@endsection
