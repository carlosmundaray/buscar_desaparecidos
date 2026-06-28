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
    <div class="seo-banner-card">
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

    <!-- Navegación de Pestañas Principales -->
    <div class="main-tabs-nav">
        <button class="main-tab-btn active" onclick="switchMainTab('cases')">
            <i class="fa-solid fa-users"></i> Búsqueda de Personas
        </button>
        <button class="main-tab-btn" onclick="switchMainTab('resources')">
            <i class="fa-solid fa-hand-holding-heart"></i> Centros de Acopio y Ayuda
        </button>
    </div>

    <!-- Pestaña 1: Casos de Desaparecidos -->
    <div id="tab-content-cases" class="main-tab-content">
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

        <!-- Grilla de Resultados (Ancho Completo) -->
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
    </div>

    <!-- Pestaña 2: Directorio de Centros de Acopio y Oficinas -->
    <div id="tab-content-resources" class="main-tab-content" style="display: none;">
        <!-- Banner de Información -->
        <div class="resource-info-banner" style="background-color: var(--accent-primary-glow); border: 1px solid rgba(37, 99, 235, 0.15); border-radius: var(--border-radius-md); padding: 1.5rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 1.25rem;">
            <i class="fa-solid fa-circle-info" style="font-size: 2.2rem; color: var(--accent-primary); flex-shrink: 0;"></i>
            <div>
                <h3 style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem;">Directorio Oficial de Ayuda, Centros de Acopio y Refugios en Venezuela</h3>
                <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.45; margin: 0;">
                    Si deseas colaborar con insumos, alimentos o medicinas, o requieres refugio y asistencia inmediata ante la emergencia sísmica, utiliza este buscador dinámico. Datos obtenidos en tiempo real de la plataforma oficial.
                </p>
            </div>
        </div>

        <!-- Filtros y Buscador para Recursos -->
        <div class="resource-filters-box" style="background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--border-radius-md); padding: 1.25rem; margin-bottom: 1.5rem; box-shadow: var(--shadow-sm);">
            <div class="resource-filters-wrapper">
                <div class="resource-search-wrapper">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 13px; color: var(--text-muted);"></i>
                    <input type="text" id="resource-search-input" placeholder="Buscar por nombre del centro, dirección o ciudad..." oninput="filterResources()" style="width: 100%; padding: 10px 12px 10px 36px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); background-color: var(--bg-input); color: var(--text-primary); font-family: var(--font-sans); font-size: 0.9rem;">
                </div>
                <div class="resource-select-wrapper">
                    <select id="resource-city-filter" onchange="filterResources()" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); background-color: var(--bg-input); color: var(--text-primary); font-family: var(--font-sans); font-size: 0.9rem; cursor: pointer;">
                        <option value="">Todas las ciudades</option>
                    </select>
                </div>
                <div class="resource-select-wrapper">
                    <select id="resource-type-filter" onchange="filterResources()" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); background-color: var(--bg-input); color: var(--text-primary); font-family: var(--font-sans); font-size: 0.9rem; cursor: pointer;">
                        <option value="all">Todos los tipos</option>
                        <option value="acopio">Centros de Acopio</option>
                        <option value="refugio">Refugios / Albergues</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Indicador de Carga / Cantidad -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding: 0 0.25rem;">
            <span id="resource-results-count" style="font-size: 0.85rem; font-weight: 600; color: var(--text-secondary);">Cargando directorio de ayuda...</span>
        </div>

        <!-- Grilla de Centros y Oficinas (Renderizado Dinámico) -->
        <div class="resources-tab-grid" id="resources-grid-container">
            <!-- Las tarjetas se cargarán dinámicamente -->
        </div>

        <!-- Botón Cargar Más -->
        <div id="load-more-resources-container" style="display: none; justify-content: center; margin-top: 2rem; margin-bottom: 2rem;">
            <button class="btn btn-secondary" onclick="loadMoreResources()" style="display: flex; align-items: center; gap: 8px; font-weight: 700; padding: 0.75rem 1.5rem; border-radius: var(--border-radius-md); cursor: pointer;">
                <i class="fa-solid fa-angles-down"></i> Cargar más centros
            </button>
        </div>
    </div>

    <!-- Estilos de Soporte para las Pestañas y Recursos -->
    <style>
        /* Pestañas Principales */
        .main-tabs-nav {
            display: flex;
            gap: 1rem;
            margin: 1.5rem 0 2rem 0;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 0.5rem;
        }

        .main-tab-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.05rem;
            font-weight: 700;
            font-family: var(--font-heading);
            padding: 0.75rem 1.25rem;
            cursor: pointer;
            position: relative;
            transition: var(--transition-smooth);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-radius: var(--border-radius-sm);
        }

        .main-tab-btn:hover {
            color: var(--text-primary);
            background-color: var(--bg-input);
        }

        .main-tab-btn.active {
            color: var(--accent-primary);
        }

        .main-tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--accent-primary);
            border-radius: 2px;
        }

        /* Contenido de Pestañas */
        .main-tab-content {
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Grilla de Recursos */
        .resources-tab-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-top: 1rem;
        }

        @media (min-width: 768px) {
            .resources-tab-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .resource-detail-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-sm);
            padding: 1.5rem;
            display: flex;
            gap: 1.25rem;
            transition: var(--transition-smooth);
        }

        .resource-detail-card:hover {
            transform: translateY(-4px);
            border-color: var(--accent-primary);
            box-shadow: var(--shadow-md);
        }

        .card-icon-header {
            width: 50px;
            height: 50px;
            border-radius: var(--border-radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .card-content {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            flex-grow: 1;
        }

        .card-content h3 {
            font-family: var(--font-heading);
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        .city-tag {
            align-self: flex-start;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: var(--border-radius-sm);
            background-color: var(--accent-primary-glow);
            color: var(--accent-primary);
        }

        .card-content p {
            margin: 0;
            font-size: 0.85rem;
            color: var(--text-secondary);
            line-height: 1.45;
        }

        .card-content p i {
            margin-right: 6px;
            color: var(--text-muted);
            width: 14px;
            text-align: center;
        }

        .receives-box {
            background-color: var(--bg-input);
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius-sm);
            border-left: 3px solid var(--accent-primary);
            font-size: 0.8rem;
            line-height: 1.4;
            color: var(--text-secondary);
            margin-top: 0.5rem;
        }

        .col-span-full {
            grid-column: 1 / -1;
        }

        /* SEO Banner Responsivo */
        .seo-banner-card {
            background-color: var(--accent-primary-glow);
            border: 1px solid rgba(37, 99, 235, 0.15);
            border-radius: var(--border-radius-md);
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        @media (max-width: 480px) {
            .seo-banner-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
                padding: 1rem 1.25rem;
            }
        }

        /* Soporte para Filtros de Recursos */
        .resource-filters-wrapper {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            width: 100%;
        }

        .resource-search-wrapper {
            flex: 1;
            position: relative;
        }

        .resource-select-wrapper {
            width: 100%;
        }

        @media (min-width: 768px) {
            .resource-filters-wrapper {
                flex-direction: row;
            }
            .resource-select-wrapper {
                width: 200px;
            }
        }

        /* Adaptabilidad de las Pestañas Principales en Móvil */
        @media (max-width: 576px) {
            .main-tabs-nav {
                gap: 0.25rem;
                margin: 1rem 0 1.5rem 0;
            }

            .main-tab-btn {
                font-size: 0.85rem;
                padding: 0.6rem 0.5rem;
                flex: 1;
                justify-content: center;
                gap: 4px;
                text-align: center;
            }
            
            .main-tab-btn i {
                font-size: 0.9rem;
            }
        }

        /* Adaptabilidad de Tarjetas de Recursos en Móvil */
        @media (max-width: 480px) {
            .resource-detail-card {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
                padding: 1.25rem;
            }
            .card-icon-header {
                width: 40px;
                height: 40px;
                font-size: 1.25rem;
            }
            .card-content h3 {
                font-size: 0.95rem;
            }
        }
    </style>

    <!-- Script de Cambio de Pestañas y Carga Dinámica de Recursos -->
    <script>
        let allResources = [];
        let filteredResources = [];
        let displayedResourcesCount = 0;
        const resourcesPerPage = 12;
        let resourcesLoaded = false;

        function switchMainTab(tab) {
            const btnCases = document.querySelector('.main-tab-btn[onclick*="cases"]');
            const btnResources = document.querySelector('.main-tab-btn[onclick*="resources"]');
            const contentCases = document.getElementById('tab-content-cases');
            const contentResources = document.getElementById('tab-content-resources');

            if (tab === 'cases') {
                btnCases.classList.add('active');
                btnResources.classList.remove('active');
                contentCases.style.display = 'block';
                contentResources.style.display = 'none';
            } else {
                btnCases.classList.remove('active');
                btnResources.classList.add('active');
                contentCases.style.display = 'none';
                contentResources.style.display = 'block';
                
                // Cargar datos dinámicos si no han sido cargados aún
                if (!resourcesLoaded) {
                    loadResourcesData();
                }
            }
        }

        async function loadResourcesData() {
            const countEl = document.getElementById('resource-results-count');
            const gridEl = document.getElementById('resources-grid-container');
            
            gridEl.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 3rem 0; color: var(--text-secondary);"><div class="spinner" style="margin: 0 auto 1rem auto;"></div><p>Cargando centros de acopio y refugios oficiales...</p></div>';
            
            try {
                const response = await fetch('/data/centros_venezuela.json');
                if (!response.ok) throw new Error('Error al cargar datos');
                allResources = await response.json();
                resourcesLoaded = true;
                
                // Poblar select de ciudades
                populateCitiesDropdown();
                
                // Filtrar e inicializar renderizado
                filterResources();
            } catch (err) {
                console.error(err);
                gridEl.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 3rem 0; color: var(--state-missing);"><i class="fa-solid fa-triangle-exclamation" style="font-size: 2.5rem; margin-bottom: 1rem;"></i><p>Hubo un problema al cargar el directorio de ayuda. Por favor reintente más tarde.</p></div>';
                countEl.textContent = 'Error al cargar';
            }
        }

        function populateCitiesDropdown() {
            const select = document.getElementById('resource-city-filter');
            const cities = new Set();
            
            allResources.forEach(r => {
                if (r.city) {
                    const normalized = r.city.trim();
                    if (normalized) cities.add(normalized);
                }
            });
            
            // Ordenar alfabéticamente
            const sortedCities = Array.from(cities).sort((a, b) => a.localeCompare(b, 'es', {sensitivity: 'base'}));
            
            sortedCities.forEach(city => {
                const opt = document.createElement('option');
                opt.value = city;
                opt.textContent = city;
                select.appendChild(opt);
            });
        }

        function filterResources() {
            const searchQuery = document.getElementById('resource-search-input').value.toLowerCase().trim();
            const selectedCity = document.getElementById('resource-city-filter').value;
            const selectedType = document.getElementById('resource-type-filter').value;
            
            filteredResources = allResources.filter(r => {
                // Filtro texto
                const matchesSearch = !searchQuery || 
                    (r.name && r.name.toLowerCase().includes(searchQuery)) ||
                    (r.address && r.address.toLowerCase().includes(searchQuery)) ||
                    (r.city && r.city.toLowerCase().includes(searchQuery));
                    
                // Filtro ciudad
                const matchesCity = !selectedCity || r.city === selectedCity;
                
                // Filtro tipo (acopio vs refugio)
                const matchesType = selectedType === 'all' || r.type === selectedType;
                
                return matchesSearch && matchesCity && matchesType;
            });
            
            displayedResourcesCount = 0;
            const gridEl = document.getElementById('resources-grid-container');
            gridEl.innerHTML = '';
            
            renderNextResources();
        }

        function renderNextResources() {
            const gridEl = document.getElementById('resources-grid-container');
            const countEl = document.getElementById('resource-results-count');
            const loadMoreContainer = document.getElementById('load-more-resources-container');
            
            const start = displayedResourcesCount;
            const end = Math.min(start + resourcesPerPage, filteredResources.length);
            
            if (filteredResources.length === 0) {
                gridEl.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 4rem 0; color: var(--text-secondary);"><i class="fa-regular fa-face-frown" style="font-size: 3rem; margin-bottom: 1rem;"></i><h3>No se encontraron centros de ayuda</h3><p>Intenta cambiar los términos de búsqueda o los filtros.</p></div>';
                countEl.textContent = '0 centros encontrados';
                loadMoreContainer.style.display = 'none';
                return;
            }
            
            for (let i = start; i < end; i++) {
                const r = filteredResources[i];
                const card = createResourceCard(r);
                gridEl.appendChild(card);
            }
            
            displayedResourcesCount = end;
            countEl.textContent = `Mostrando ${displayedResourcesCount} de ${filteredResources.length} centros y albergues`;
            
            if (displayedResourcesCount < filteredResources.length) {
                loadMoreContainer.style.display = 'flex';
            } else {
                loadMoreContainer.style.display = 'none';
            }
        }

        function loadMoreResources() {
            renderNextResources();
        }

        function createResourceCard(r) {
            const div = document.createElement('div');
            div.className = 'resource-detail-card';
            
            // Icono e indicación de color según tipo
            let icon = 'fa-box-open';
            let iconBg = 'rgba(99, 102, 241, 0.08)';
            let iconColor = 'var(--accent-primary)';
            let typeLabel = 'Centro de Acopio';
            
            if (r.type === 'refugio') {
                icon = 'fa-people-roof';
                iconBg = 'rgba(22, 163, 74, 0.08)';
                iconColor = '#16a34a';
                typeLabel = 'Refugio / Albergue';
            } else if (r.name && (r.name.toLowerCase().includes('cruz roja') || r.name.toLowerCase().includes('bomberos') || r.name.toLowerCase().includes('hospital'))) {
                icon = 'fa-kit-medical';
                iconBg = 'rgba(220, 38, 38, 0.08)';
                iconColor = '#dc2626';
            }
            
            // Render de donaciones aceptadas
            let receivesHtml = '';
            if (r.receives && r.receives.length > 0) {
                const list = r.receives.map(item => item.charAt(0).toUpperCase() + item.slice(1)).join(', ');
                receivesHtml = `
                    <div class="receives-box" style="border-left-color: ${iconColor};">
                        <strong>Recibe:</strong> ${list}
                    </div>
                `;
            }
            
            // Contacto
            let contactHtml = '';
            if (r.contact) {
                contactHtml = `<p class="contact"><i class="fa-solid fa-phone"></i> ${r.contact}</p>`;
            }
            
            div.innerHTML = `
                <div class="card-icon-header" style="background-color: ${iconBg}; color: ${iconColor};">
                    <i class="fa-solid ${icon}"></i>
                </div>
                <div class="card-content">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; flex-wrap: wrap;">
                        <h3 style="font-size: 1rem; line-height: 1.35;">${r.name}</h3>
                        <span class="city-tag" style="background-color: ${iconBg}; color: ${iconColor};">${r.city || 'Venezuela'}</span>
                    </div>
                    <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); display: block; margin-top: -0.25rem;">${typeLabel}</span>
                    <p class="address"><i class="fa-solid fa-location-dot"></i> ${r.address || 'Dirección no especificada'}</p>
                    ${contactHtml}
                    ${receivesHtml}
                </div>
            `;
            
            return div;
        }
    </script>

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
