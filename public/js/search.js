// State Management
let currentQuery = '';
let currentStatus = 'all';
let currentPhotoFilter = 'all';
let currentPage = 1;
let lastPage = 1;
let debounceTimeout = null;
let peopleData = {}; // Cache for person details by ID
let sseSource = null;
let currentPhotoFile = null;
let isPhotoSearchActive = false;
let lastSyncedQuery = '';

// Performance Optimization Cache & Controllers
const localSearchCache = {};
let currentSearchAbortController = null;

// DOM Elements & Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    // Initial fetch
    fetchResults();

    // Search input listener with debounce
    const searchInput = document.getElementById('search-input');
    const clearSearchBtn = document.getElementById('clear-search');

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            currentQuery = e.target.value;
            currentPage = 1;

            // Show/hide clear button
            if (currentQuery.length > 0) {
                clearSearchBtn.style.display = 'block';
            } else {
                clearSearchBtn.style.display = 'none';
            }

            // Debounce fetch request
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => {
                fetchResults();
            }, 250);
        });
    }

    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', () => {
            searchInput.value = '';
            currentQuery = '';
            clearSearchBtn.style.display = 'none';
            currentPage = 1;
            fetchResults();
        });
    }

    // Setup modal backdrop close triggers
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                closeModal(overlay.id);
            }
        });
    });
});

// ==========================================
// RESULTS FETCH & RENDER LLOGIC
// ==========================================

function fetchResults() {
    const spinner = document.getElementById('loading-spinner');
    const grid = document.getElementById('results-grid');
    const noResults = document.getElementById('no-results');
    const resultsCount = document.getElementById('results-count');
    const pagination = document.getElementById('pagination-area');

    // Show loading spinner
    spinner.style.display = 'flex';
    grid.style.opacity = '0.4';
    noResults.style.display = 'none';

    // Intercept with photo search if active
    if (isPhotoSearchActive && currentPhotoFile) {
        fetchPhotoSearch();
        return;
    }

    // Construct request URL
    const url = `/api/buscar?query=${encodeURIComponent(currentQuery)}&status=${currentStatus}&photo=${currentPhotoFilter}&page=${currentPage}`;

    // Inline function to trigger the background scraping sync
    const triggerBackgroundSync = () => {
        const queryToSync = currentQuery.trim();
        if (queryToSync.length >= 3 && queryToSync !== lastSyncedQuery && !isPhotoSearchActive) {
            lastSyncedQuery = queryToSync;
            
            fetch(`/api/sincronizar-busqueda?query=${encodeURIComponent(queryToSync)}`)
                .then(r => r.json())
                .then(syncData => {
                    // If new records were imported, clear the cache for this URL and re-fetch silently
                    if (syncData.success && syncData.new_records > 0) {
                        delete localSearchCache[url];
                        if (currentQuery.trim() === queryToSync) {
                            fetchResultsSilently();
                        }
                    }
                })
                .catch(e => console.warn('Error in background sync:', e));
        }
    };

    // Serve from local cache immediately (sub-5ms) if available
    if (localSearchCache[url]) {
        const data = localSearchCache[url];
        const total = data.people.total;
        const queryToSync = currentQuery.trim();

        if (total === 0 && queryToSync.length >= 3 && queryToSync !== lastSyncedQuery && !isPhotoSearchActive) {
            lastSyncedQuery = queryToSync;
            spinner.style.display = 'none';
            grid.style.opacity = '1';
            grid.innerHTML = '';
            pagination.style.display = 'none';
            resultsCount.textContent = 'Buscando en vivo...';

            noResults.innerHTML = `
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem;">
                    <div style="width: 40px; height: 40px; border: 3px solid rgba(37, 99, 235, 0.1); border-top-color: rgb(37, 99, 235); border-radius: 50%; animation: spin-realtime 1s linear infinite;"></div>
                    <h3 style="margin-top: 1.5rem; font-family: var(--font-heading); font-size: 1.25rem; font-weight: 700; color: var(--text-primary);">Buscando en tiempo real...</h3>
                    <p style="margin-top: 0.5rem; font-size: 0.9rem; color: var(--text-secondary); max-width: 400px; text-align: center;">No encontramos resultados en nuestra base de datos. Buscando en vivo en "Venezuela Te Busca" y listados externos de hospitales...</p>
                </div>
                <style>
                @keyframes spin-realtime {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                </style>
            `;
            noResults.style.display = 'block';

            fetch(`/api/sincronizar-busqueda?query=${encodeURIComponent(queryToSync)}`)
                .then(r => r.json())
                .then(syncData => {
                    if (syncData.success && syncData.new_records > 0) {
                        delete localSearchCache[url];
                        if (currentQuery.trim() === queryToSync) {
                            fetchResults();
                        }
                    } else {
                        noResults.innerHTML = `
                            <i class="fa-regular fa-face-frown"></i>
                            <h3>No se encontraron resultados</h3>
                            <p>No se hallaron coincidencias locales ni en la búsqueda en tiempo real de fuentes externas.</p>
                        `;
                        resultsCount.textContent = '0 casos encontrados';
                    }
                })
                .catch(e => {
                    console.warn('Error in active sync:', e);
                    noResults.innerHTML = `
                        <i class="fa-regular fa-face-frown"></i>
                        <h3>No se encontraron resultados</h3>
                        <p>Intenta ajustar tus criterios de búsqueda o limpia los filtros.</p>
                    `;
                    resultsCount.textContent = '0 casos encontrados';
                });
            return;
        }

        spinner.style.display = 'none';
        grid.style.opacity = '1';

        renderGrid(data.people.data);
        updateStats(data.stats);

        resultsCount.textContent = `${total} ${total === 1 ? 'caso encontrado' : 'casos encontrados'}`;

        lastPage = data.people.last_page;
        currentPage = data.people.current_page;
        
        if (lastPage > 1) {
            pagination.style.display = 'flex';
            document.getElementById('page-indicator').textContent = `Página ${currentPage} de ${lastPage}`;
            document.getElementById('prev-page-btn').disabled = (currentPage === 1);
            document.getElementById('next-page-btn').disabled = (currentPage === lastPage);
        } else {
            pagination.style.display = 'none';
        }

        triggerBackgroundSync();
        return;
    }

    // Abort any ongoing search request to prevent out-of-order rendering and reduce server strain
    if (currentSearchAbortController) {
        currentSearchAbortController.abort();
    }
    currentSearchAbortController = new AbortController();
    const signal = currentSearchAbortController.signal;

    fetch(url, { signal })
        .then(response => response.json())
        .then(data => {
            // Save results to cache
            localSearchCache[url] = data;

            spinner.style.display = 'none';
            grid.style.opacity = '1';

            const total = data.people.total;
            const queryToSync = currentQuery.trim();

            if (total === 0 && queryToSync.length >= 3 && queryToSync !== lastSyncedQuery && !isPhotoSearchActive) {
                lastSyncedQuery = queryToSync;
                grid.innerHTML = '';
                pagination.style.display = 'none';
                resultsCount.textContent = 'Buscando en vivo...';

                noResults.innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem;">
                        <div style="width: 40px; height: 40px; border: 3px solid rgba(37, 99, 235, 0.1); border-top-color: rgb(37, 99, 235); border-radius: 50%; animation: spin-realtime 1s linear infinite;"></div>
                        <h3 style="margin-top: 1.5rem; font-family: var(--font-heading); font-size: 1.25rem; font-weight: 700; color: var(--text-primary);">Buscando en tiempo real...</h3>
                        <p style="margin-top: 0.5rem; font-size: 0.9rem; color: var(--text-secondary); max-width: 400px; text-align: center;">No encontramos resultados en nuestra base de datos. Buscando en vivo en "Venezuela Te Busca" y listados externos de hospitales...</p>
                    </div>
                    <style>
                    @keyframes spin-realtime {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                    </style>
                `;
                noResults.style.display = 'block';

                fetch(`/api/sincronizar-busqueda?query=${encodeURIComponent(queryToSync)}`)
                    .then(r => r.json())
                    .then(syncData => {
                        if (syncData.success && syncData.new_records > 0) {
                            delete localSearchCache[url];
                            if (currentQuery.trim() === queryToSync) {
                                fetchResults();
                            }
                        } else {
                            noResults.innerHTML = `
                                <i class="fa-regular fa-face-frown"></i>
                                <h3>No se encontraron resultados</h3>
                                <p>No se hallaron coincidencias locales ni en la búsqueda en tiempo real de fuentes externas.</p>
                            `;
                            resultsCount.textContent = '0 casos encontrados';
                        }
                    })
                    .catch(e => {
                        console.warn('Error in active sync:', e);
                        noResults.innerHTML = `
                            <i class="fa-regular fa-face-frown"></i>
                            <h3>No se encontraron resultados</h3>
                            <p>Intenta ajustar tus criterios de búsqueda o limpia los filtros.</p>
                        `;
                        resultsCount.textContent = '0 casos encontrados';
                    });
            } else {
                // Render grid
                renderGrid(data.people.data);
                updateStats(data.stats);

                // Update result counts
                resultsCount.textContent = `${total} ${total === 1 ? 'caso encontrado' : 'casos encontrados'}`;

                // Update Pagination
                lastPage = data.people.last_page;
                currentPage = data.people.current_page;
                
                if (lastPage > 1) {
                    pagination.style.display = 'flex';
                    document.getElementById('page-indicator').textContent = `Página ${currentPage} de ${lastPage}`;
                    document.getElementById('prev-page-btn').disabled = (currentPage === 1);
                    document.getElementById('next-page-btn').disabled = (currentPage === lastPage);
                } else {
                    pagination.style.display = 'none';
                }

                triggerBackgroundSync();
            }
        })
        .catch(err => {
            if (err.name === 'AbortError') {
                return; // Ignore aborted requests
            }
            console.error('Error fetching results:', err);
            spinner.style.display = 'none';
            grid.style.opacity = '1';
        });
}

function renderGrid(people) {
    const grid = document.getElementById('results-grid');
    const noResults = document.getElementById('no-results');
    
    grid.innerHTML = '';
    peopleData = {}; // Reset cache

    if (people.length === 0) {
        noResults.style.display = 'block';
        document.getElementById('pagination-area').style.display = 'none';
        return;
    }

    noResults.style.display = 'none';

    people.forEach(person => {
        // Cache person details
        peopleData[person.id] = person;

        // Determine badge details
        const isFound = (person.status === 'found');
        const isDeceased = (person.status === 'deceased');
        const isHospitalized = isFound && (
            (person.found_location && (person.found_location.toLowerCase().includes('hospital') || person.found_location.toLowerCase().includes('salud') || person.found_location.toLowerCase().includes('vargas') || person.found_location.toLowerCase().includes('carreño') || person.found_location.toLowerCase().includes('luciani') || person.found_location.toLowerCase().includes('vargas') || person.found_location.toLowerCase().includes('huc') || person.found_location.toLowerCase().includes('fredy'))) ||
            (person.code && person.code.startsWith('HOSP-'))
        );

        let badgeClass = 'badge-missing';
        let badgeText = 'Desaparecido';
        if (isHospitalized) {
            badgeClass = 'badge-hospitalized';
            badgeText = '🏥 En Hospital';
        } else if (isFound) {
            badgeClass = 'badge-found';
            badgeText = 'Localizado';
        } else if (isDeceased) {
            badgeClass = 'badge-deceased';
            badgeText = '💀 Fallecido';
        }

        // Default placeholder photo if none exists (using initials/silhouette avatar generator)
        const photo = person.photo_path || getAvatarUrl(person.gender, person.full_name);
        
        // Visual similarity match badge (only during photo searches)
        const similarityBadge = person.similarity !== undefined 
            ? `<span class="badge-similarity"><i class="fa-solid fa-face-smile"></i> ${Math.round(person.similarity)}%</span>` 
            : '';

        // Build card HTML
        const card = document.createElement('div');
        card.className = 'person-card';
        card.setAttribute('onclick', `openDetailModal(${person.id})`);

        // Build metadata list
        let metaHtml = '';
        if (person.cedula) {
            metaHtml += `
                <div class="card-meta-item" style="color: var(--accent-primary); font-weight: 700;">
                    <i class="fa-solid fa-address-card" style="color: var(--accent-primary);"></i>
                    <span>C.I. ${formatCedulaString(person.cedula)}</span>
                </div>
            `;
        } else {
            metaHtml += `
                <div class="card-meta-item" style="color: var(--text-muted); font-style: italic;">
                    <i class="fa-solid fa-address-card"></i>
                    <span>C.I. No especificada (Verificar)</span>
                </div>
            `;
        }

        if (isHospitalized) {
            // Clean description for snippet
            let cleanDesc = person.description || '';
            cleanDesc = cleanDesc.replace(/\[ACTUALIZACIÓN SÍSMICA\]:.*$/s, '').trim();
            cleanDesc = cleanDesc.replace(/Registrado como ingresado en centro de salud tras evento sismico\./gi, '').trim();
            cleanDesc = cleanDesc.replace(/Ubicacion:.*$/gi, '').trim();
            cleanDesc = cleanDesc.replace(/^-+/g, '').trim();

            metaHtml += `
                <div class="card-hospital-meta">
                    <span><strong><i class="fa-solid fa-hospital"></i> Ubicación:</strong> ${person.found_location}</span>
                    ${cleanDesc ? `<div class="card-hospital-condition"><strong>Reporte Médico:</strong> ${cleanDesc.length > 90 ? cleanDesc.substring(0, 90) + '...' : cleanDesc}</div>` : '<div class="card-hospital-condition"><strong>Reporte Médico:</strong> Estable / Bajo Observación.</div>'}
                </div>
            `;
        } else {
            metaHtml += `
                <div class="card-meta-item">
                    <i class="fa-solid fa-calendar-days"></i>
                    <span>Visto: ${person.formatted_last_seen}</span>
                </div>
                <div class="card-meta-item">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Lugar: ${person.last_seen_location}</span>
                </div>
            `;
        }

        card.innerHTML = `
            <div class="card-img-wrapper">
                ${similarityBadge}
                <img src="${photo}" alt="Foto de ${person.full_name}" loading="lazy" onerror="handleImageError(this, '${person.gender || ''}', '${person.full_name}')">
                <span class="card-badge ${badgeClass}">${badgeText}</span>
            </div>
            <div class="card-content">
                <div class="card-title">
                    <h3>${person.full_name}</h3>
                    ${person.alias ? `<span class="card-alias">"${person.alias}"</span>` : ''}
                </div>
                <div class="card-meta-list">
                    ${metaHtml}
                </div>
            </div>
            <div class="card-footer">
                <span>Código: ${person.code || 'BD-N/A'}</span>
                <span>Edad: ${person.age ? person.age + ' años' : 'N/E'}</span>
            </div>
        `;

        grid.appendChild(card);
    });
}

function updateStats(stats) {
    document.getElementById('count-reported').textContent = stats.reported;
    document.getElementById('count-missing').textContent = stats.missing;
    document.getElementById('count-found').textContent = stats.found;
    if (document.getElementById('count-deceased') && stats.deceased !== undefined) {
        document.getElementById('count-deceased').textContent = stats.deceased;
    }
    if (document.getElementById('count-hospitalized') && stats.hospitalized !== undefined) {
        document.getElementById('count-hospitalized').textContent = stats.hospitalized;
    }

    // Highlight active stat filters
    document.querySelectorAll('.stat-card').forEach(card => {
        const filter = card.getAttribute('data-filter');
        if (filter === currentStatus) {
            card.classList.add('active-filter');
        } else {
            card.classList.remove('active-filter');
        }
    });
}

// ==========================================
// FILTERS & PAGINATION
// ==========================================

function setFilter(status) {
    currentStatus = status;
    currentPage = 1;
    
    // Update filter tabs class
    document.querySelectorAll('.filter-tab').forEach(tab => {
        if (tab.getAttribute('data-status') === status) {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
    });

    fetchResults();
}

function setStatusFilter(status) {
    setFilter(status);
}

function setPhotoFilter(photo) {
    currentPhotoFilter = photo;
    currentPage = 1;

    // Update photo filter tabs class
    document.querySelectorAll('.filter-tab-photo').forEach(tab => {
        if (tab.getAttribute('data-photo') === photo) {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
    });

    fetchResults();
}

function changePage(direction) {
    const targetPage = currentPage + direction;
    if (targetPage >= 1 && targetPage <= lastPage) {
        currentPage = targetPage;
        fetchResults();
    }
}

// ==========================================
// MODAL MANAGEMENT
// ==========================================

function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = 'auto';
    }
}

// Open and load detail modal
function openDetailModal(id) {
    const person = peopleData[id];
    if (!person) return;

    document.getElementById('detail-title').textContent = person.full_name;
    const photo = person.photo_path || getAvatarUrl(person.gender, person.full_name);
    const photoImg = document.getElementById('detail-photo');
    photoImg.src = photo;
    photoImg.setAttribute('onerror', `handleImageError(this, '${person.gender || ''}', '${person.full_name}')`);

    const imgWrapper = photoImg.parentElement;
    if (imgWrapper) {
        if (!person.photo_path) {
            imgWrapper.classList.add('no-zoom');
        } else {
            imgWrapper.classList.remove('no-zoom');
        }
    }
    
    // Badge status
    const isFound = (person.status === 'found');
    const isDeceased = (person.status === 'deceased');
    const isHospitalized = isFound && (
        (person.found_location && (person.found_location.toLowerCase().includes('hospital') || person.found_location.toLowerCase().includes('salud') || person.found_location.toLowerCase().includes('vargas') || person.found_location.toLowerCase().includes('carreño') || person.found_location.toLowerCase().includes('luciani') || person.found_location.toLowerCase().includes('vargas') || person.found_location.toLowerCase().includes('huc') || person.found_location.toLowerCase().includes('fredy'))) ||
        (person.code && person.code.startsWith('HOSP-'))
    );

    const badge = document.getElementById('detail-badge');
    if (isHospitalized) {
        badge.className = 'card-badge badge-hospitalized';
        badge.textContent = '🏥 En Hospital';
    } else if (isFound) {
        badge.className = 'card-badge badge-found';
        badge.textContent = 'Localizado';
    } else if (isDeceased) {
        badge.className = 'card-badge badge-deceased';
        badge.textContent = '💀 Fallecido';
    } else {
        badge.className = 'card-badge badge-missing';
        badge.textContent = 'Desaparecido';
    }

    // Meta details
    document.getElementById('detail-code').textContent = person.code || 'BD-N/A';
    document.getElementById('detail-age').textContent = person.age ? `${person.age} años` : 'No especificada';
    document.getElementById('detail-gender').textContent = person.gender || 'No especificado';
    document.getElementById('detail-last-seen').textContent = person.formatted_last_seen;
    document.getElementById('detail-location').textContent = person.last_seen_location;

    // Cedula row
    const cedulaRow = document.getElementById('detail-cedula-row');
    if (person.cedula) {
        cedulaRow.style.display = 'flex';
        document.getElementById('detail-cedula').innerHTML = `<span style="color: var(--accent-primary); font-weight: 700;">V-${formatCedulaString(person.cedula)}</span>`;
    } else if (isHospitalized) {
        cedulaRow.style.display = 'flex';
        document.getElementById('detail-cedula').innerHTML = `<span style="color: var(--text-muted); font-style: italic;">No especificada (Verificar con el hospital)</span>`;
    } else {
        cedulaRow.style.display = 'none';
    }

    // Description
    document.getElementById('detail-desc').textContent = person.description || 'Sin descripción disponible.';

    // Reporter Info (only show if it exists and is not "Reporte importado" to respect privacy unless manual)
    const reporterBox = document.getElementById('detail-reporter-info');
    if (person.reporter_name && person.reporter_name !== 'Reporte importado') {
        reporterBox.style.display = 'block';
        document.getElementById('detail-rep-name').textContent = person.reporter_name;
        document.getElementById('detail-rep-rel').textContent = person.relationship || 'Otro';
        document.getElementById('detail-rep-phone').textContent = person.reporter_phone;
        
        const repEmailRow = document.getElementById('detail-rep-email-row');
        if (person.reporter_email) {
            repEmailRow.style.display = 'block';
            document.getElementById('detail-rep-email').textContent = person.reporter_email;
        } else {
            repEmailRow.style.display = 'none';
        }
    } else if (person.reporter_phone) {
        // If it's imported, sometimes we only have telephone
        reporterBox.style.display = 'block';
        document.getElementById('detail-rep-name').textContent = 'Reportante Solidario';
        document.getElementById('detail-rep-rel').textContent = 'Importado';
        document.getElementById('detail-rep-phone').textContent = person.reporter_phone;
        document.getElementById('detail-rep-email-row').style.display = 'none';
    } else {
        reporterBox.style.display = 'none';
    }

    // Source Row
    const sourceRow = document.getElementById('detail-source-row');
    const sourceLink = document.getElementById('detail-source-link');
    if (person.source_url) {
        sourceRow.style.display = 'block';
        sourceLink.href = person.source_url;
        if (person.source_url.includes('workers.dev') || person.source_url.includes('venezuelatebusca.com')) {
            sourceLink.textContent = 'Ver caso en Venezuela Te Busca';
        } else if (person.source_url.includes('drive.google.com')) {
            sourceLink.textContent = 'Ver reporte consolidado en Google Drive';
        } else if (person.source_url.includes('/uploads/reportes/')) {
            sourceLink.textContent = 'Ver listado original consolidado (Copia local)';
        } else {
            sourceLink.textContent = 'Ver caso en buscardesaparecidos.com';
        }
    } else {
        sourceRow.style.display = 'none';
    }

    // Found Info Box
    const foundBox = document.getElementById('detail-found-row');
    if (isFound) {
        foundBox.style.display = 'block';
        if (isHospitalized) {
            // Clean description for medical report snippet
            let cleanDesc = person.description || '';
            cleanDesc = cleanDesc.replace(/\[ACTUALIZACIÓN SÍSMICA\]:.*$/gs, '').trim();
            cleanDesc = cleanDesc.replace(/Registrado como ingresado en centro de salud tras evento sismico\./gi, '').trim();
            cleanDesc = cleanDesc.replace(/Ubicacion:.*$/gi, '').trim();
            cleanDesc = cleanDesc.replace(/^-+/g, '').trim();
            if (!cleanDesc) {
                cleanDesc = 'Estable / Bajo Observación.';
            }

            const hInfo = getHospitalInfo(person.found_location);
            let hInfoHtml = '';
            if (hInfo) {
                hInfoHtml = `
                    <div style="margin-top: 10px; padding-top: 8px; border-top: 1px dashed rgba(139, 92, 246, 0.2); font-size: 12.5px;">
                        <p style="margin: 0 0 4px 0; color: var(--text-primary); line-height: 1.4;">
                            <strong>Dirección:</strong> ${hInfo.address}
                        </p>
                        <p style="margin: 0 0 4px 0; color: var(--text-primary);">
                            <strong>Teléfono:</strong> <a href="tel:${hInfo.phone.replace(/[^0-9]/g, '')}" style="color: var(--accent-secondary); font-weight: 600; text-decoration: none;">${hInfo.phone}</a>
                        </p>
                        <p style="margin: 0; color: var(--text-muted); font-style: italic; font-size: 12px; line-height: 1.35;">
                            <strong>Recomendación:</strong> ${hInfo.tips}
                        </p>
                    </div>
                `;
            }

            foundBox.innerHTML = `
                <div style="border-left: 4px solid var(--accent-secondary); padding: 12px; background: rgba(139, 92, 246, 0.05); border-radius: 4px;">
                    <h4 style="color: var(--accent-secondary); font-weight: 700; margin-top: 0; margin-bottom: 8px;">
                        <i class="fa-solid fa-hospital"></i> Ubicación e Ingreso Médico
                    </h4>
                    <p style="margin: 0 0 6px 0; font-size: 13.5px; color: var(--text-primary);">
                        <strong>Centro de Salud:</strong> <span style="color: var(--accent-secondary); font-weight: 700;">${person.found_location || 'No especificado'}</span>
                    </p>
                    <p style="margin: 0 0 10px 0; font-size: 13px; color: var(--text-secondary);">
                        <strong>Fecha de Ingreso:</strong> ${person.formatted_found || 'Recientemente'}
                    </p>
                    ${hInfoHtml}
                    <div style="background: var(--bg-card); border: 1px solid rgba(139, 92, 246, 0.15); padding: 10px; border-radius: var(--border-radius-sm); margin-top: 8px;">
                        <h5 style="margin: 0 0 4px 0; font-size: 12px; color: var(--accent-secondary); text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">
                            Reporte Médico / Diagnóstico:
                        </h5>
                        <p style="margin: 0; font-size: 13.5px; color: var(--text-primary); font-weight: 600;">
                            ${cleanDesc}
                        </p>
                    </div>
                </div>
            `;
            foundBox.style.borderLeft = 'none';
            foundBox.style.padding = '0';
            foundBox.style.background = 'none';
        } else {
            // Standard found display
            foundBox.innerHTML = `
                <h4><i class="fa-solid fa-location-dot"></i> Información de Localización</h4>
                <p><strong>Fecha:</strong> <span id="detail-found-at">${person.formatted_found || 'Recientemente'}</span></p>
                <p><strong>Lugar:</strong> <span id="detail-found-location">${person.found_location || 'No especificada'}</span></p>
            `;
            foundBox.style.borderLeft = '3px solid var(--state-found)';
            foundBox.style.padding = '16px';
            foundBox.style.background = 'rgba(0, 0, 0, 0.015)';
        }
    } else {
        foundBox.style.display = 'none';
    }

    // Set actions footer buttons
    const actionsArea = document.getElementById('detail-actions');
    actionsArea.innerHTML = '';
    
    // Add close button
    const closeBtn = document.createElement('button');
    closeBtn.className = 'btn btn-secondary';
    closeBtn.textContent = 'Cerrar';
    closeBtn.setAttribute('onclick', "closeModal('detail-modal')");
    actionsArea.appendChild(closeBtn);

    // If missing, show button to mark as found
    if (!isFound) {
        const foundBtn = document.createElement('button');
        foundBtn.className = 'btn btn-success';
        foundBtn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i> Reportar Localizado';
        foundBtn.setAttribute('onclick', `openFoundModal(${person.id}, "${person.full_name}")`);
        actionsArea.appendChild(foundBtn);
    }

    openModal('detail-modal');
}

// ==========================================
// CASE REPORT SUBMISSION
// ==========================================

function openReportModal() {
    document.getElementById('report-form').reset();
    openModal('report-modal');
}

function submitReportForm(event) {
    event.preventDefault();
    const form = document.getElementById('report-form');
    const formData = new FormData(form);

    const submitBtn = form.querySelector('button[type="submit"]');
    const origText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Registrando...';

    fetch('/reportar', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = origText;

        if (data.success) {
            closeModal('report-modal');
            alert('¡Éxito! El reporte de persona desaparecida ha sido registrado.');
            fetchResults();
        } else {
            alert('Ocurrió un error al guardar el reporte: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(err => {
        console.error('Error submitting report:', err);
        submitBtn.disabled = false;
        submitBtn.innerHTML = origText;
        alert('Error de conexión con el servidor.');
    });
}

// ==========================================
// MARK PERSON AS LOCATED (FOUND)
// ==========================================

function openFoundModal(id, name) {
    document.getElementById('found-form').reset();
    document.getElementById('found-person-id').value = id;
    document.getElementById('found-person-name').textContent = name;
    
    // Close detail modal first
    closeModal('detail-modal');
    openModal('found-modal');
}

function submitFoundForm(event) {
    event.preventDefault();
    const id = document.getElementById('found-person-id').value;
    const location = document.getElementById('found_location').value;

    const submitBtn = document.querySelector('#found-form button[type="submit"]');
    const origText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Procesando...';

    fetch(`/caso/${id}/localizado`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ found_location: location })
    })
    .then(response => response.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = origText;

        if (data.success) {
            closeModal('found-modal');
            alert('¡Gracias! El estado de la persona se ha actualizado a localizado/a.');
            fetchResults();
        } else {
            alert('Error al actualizar el estado: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(err => {
        console.error('Error marking as found:', err);
        submitBtn.disabled = false;
        submitBtn.innerHTML = origText;
        alert('Error de red al actualizar el estado.');
    });
}

// ==========================================
// STRING HELPERS
// ==========================================

function formatCedulaString(cedula) {
    if (!cedula) return '';
    // Format numeric string as standard Venezuelan format with dots (e.g. 12345678 -> 12.345.678)
    return parseInt(cedula).toLocaleString('es-VE');
}

function getHospitalInfo(location) {
    if (!location) return null;
    const locLower = location.toLowerCase();
    
    const directory = {
        'luciani': {
            name: 'Hospital Domingo Luciani (El Llanito)',
            address: 'Av. Río de Janeiro, Urbanización El Llanito, Caracas.',
            phone: '0212-2563222',
            tips: 'Se aconseja a los familiares dirigirse directamente al departamento de Trabajo Social o al área de Admisión de Emergencias.'
        },
        'universitario': {
            name: 'Hospital Universitario de Caracas (HUC)',
            address: 'Ciudad Universitaria de Caracas, Parroquia San Pedro, Caracas.',
            phone: '0212-6067111',
            tips: 'Para ingresar, use el control de acceso en la rampa de emergencias o diríjase al mostrador principal de Información en Planta Baja.'
        },
        'huc': {
            name: 'Hospital Universitario de Caracas (HUC)',
            address: 'Ciudad Universitaria de Caracas, Parroquia San Pedro, Caracas.',
            phone: '0212-6067111',
            tips: 'Para ingresar, use el control de acceso en la rampa de emergencias o diríjase al mostrador principal de Información en Planta Baja.'
        },
        'carreño': {
            name: 'Hospital Dr. Miguel Pérez Carreño',
            address: 'Av. Principal de La Yaguara, Calle El Algodonal, El Paraíso, Caracas.',
            phone: '0212-4428111 / 0212-4432122',
            tips: 'Consulte en la taquilla de Trabajo Social o en el área de Triaje/Traumashock.'
        },
        'vargas': {
            name: 'Hospital Vargas de Caracas',
            address: 'Av. Principal de San José, Monte Carmelo a San Fernando, Caracas.',
            phone: '0212-8608433',
            tips: 'Diríjase a la oficina de Admisión General o consulte al personal de guardia en la Emergencia de Adultos.'
        },
        'baquero': {
            name: 'Hospital Ricardo Baquero González (Periférico de Catia)',
            address: 'Av. Principal de Catia, frente a la Plaza Pérez Bonalde, Caracas.',
            phone: '0212-8713311',
            tips: 'El acceso es restringido. Ubique al supervisor de turno en la Emergencia o al personal de Trabajo Social.'
        },
        'cruz roja': {
            name: 'Hospital Carlos J. Bello (Cruz Roja Venezolana)',
            address: 'Av. Andrés Bello, cruce con calle Cruz Roja, Parroquia La Candelaria, Caracas.',
            phone: '0212-5713644 / 0212-5714022',
            tips: 'Consulte directamente en el módulo de recepción de emergencias de la Cruz Roja.'
        }
    };

    for (const key in directory) {
        if (locLower.includes(key)) {
            return directory[key];
        }
    }
    return null;
}

// ==========================================
// DYNAMIC SVG AVATAR SYSTEM
// ==========================================

function getAvatarUrl(gender, name) {
    const cleanName = name ? name.trim() : 'Desconocido';
    const initials = cleanName.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
    
    // Premium gradient background colors based on name hash
    const colors = [
        ['#312e81', '#4338ca'], // Deep Blue
        ['#4c0519', '#be123c'], // Rose / Dark Red
        ['#1e1b4b', '#4f46e5'], // Indigo
        ['#311042', '#7b1fa2'], // Purple
        ['#064e3b', '#047857'], // Emerald
        ['#18181b', '#3f3f46'], // Zinc
        ['#0c4a6e', '#0369a1'], // Light Blue
    ];
    
    let hash = 0;
    for (let i = 0; i < cleanName.length; i++) {
        hash += cleanName.charCodeAt(i);
    }
    const gradient = colors[hash % colors.length];
    
    // Choose profile silhouette based on gender
    let silhouette = '';
    if (gender && gender.toLowerCase().includes('fem')) {
        // Female Profile Outline (subtle hair curve)
        silhouette = `
            <circle cx="50" cy="38" r="15" fill="rgba(255,255,255,0.88)" />
            <path d="M50,23 C42,23 37,28 37,34 C37,36 39,40 39,42 C36,44 34,48 34,53 C34,58 37,60 38,62 C38,65 39,67 39,70 C41,70 42,67 43,65 C45,67 48,68 50,68 C52,68 55,67 57,65 C58,67 59,70 61,70 C61,67 62,65 62,62 C63,60 66,58 66,53 C66,48 64,44 61,42 C61,40 63,36 63,34 C63,28 58,23 50,23 Z" fill="rgba(255,255,255,0.06)" />
            <path d="M22 82 C22 66, 32 60, 50 60 C68 60, 78 66, 78 82 C78 85, 78 88, 78 90 H22 Z" fill="rgba(255,255,255,0.88)" />
        `;
    } else {
        // Standard Silhouette
        silhouette = `
            <circle cx="50" cy="38" r="16" fill="rgba(255,255,255,0.88)" />
            <path d="M22 82 C22 66, 32 60, 50 60 C68 60, 78 66, 78 82 C78 85, 78 88, 78 90 H22 Z" fill="rgba(255,255,255,0.88)" />
        `;
    }

    const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100%" height="100%">
        <defs>
            <linearGradient id="grad-${hash}" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:${gradient[0]};stop-opacity:1" />
                <stop offset="100%" style="stop-color:${gradient[1]};stop-opacity:1" />
            </linearGradient>
        </defs>
        <rect width="100" height="100" fill="url(#grad-${hash})" />
        ${silhouette}
        <text x="50" y="52" font-family="'Outfit', 'Plus Jakarta Sans', sans-serif" font-weight="800" font-size="12" fill="${gradient[0]}" text-anchor="middle" style="opacity: 0.95; letter-spacing: -0.5px;">${initials}</text>
    </svg>`;
    
    return 'data:image/svg+xml;utf8,' + encodeURIComponent(svg);
}

function handleImageError(img, gender, name) {
    img.onerror = null; // Prevent infinite loop
    img.src = getAvatarUrl(gender, name);
    img.classList.add('avatar-placeholder');
}

// ==========================================
// PHOTO SEARCH UI LOGIC
// ==========================================

function openPhotoSearchModal() {
    // Reset modal state
    currentPhotoFile = null;
    document.getElementById('photo-search-preview-wrapper').style.display = 'none';
    document.getElementById('photo-search-preview').src = '';
    document.getElementById('photo-search-input').value = '';
    
    // Set up drag and drop events if they haven't been setup
    const dropZone = document.getElementById('upload-drop-zone');
    if (dropZone && !dropZone.dataset.eventsSetup) {
        dropZone.dataset.eventsSetup = 'true';
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.remove('dragover');
            }, false);
        });

        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) {
                processSearchPhoto(files[0]);
            }
        }, false);
    }

    openModal('photo-search-modal');
}

function handlePhotoSearchSelect(input) {
    if (input.files && input.files.length > 0) {
        processSearchPhoto(input.files[0]);
    }
}

function processSearchPhoto(file) {
    if (!file.type.startsWith('image/')) {
        alert('Por favor, selecciona un archivo de imagen válido.');
        return;
    }
    
    if (file.size > 2 * 1024 * 1024) {
        alert('La imagen es demasiado grande. El límite es de 2MB.');
        return;
    }

    currentPhotoFile = file;
    
    // Show preview
    const reader = new FileReader();
    reader.onload = (e) => {
        document.getElementById('photo-search-preview').src = e.target.result;
        document.getElementById('photo-search-preview-wrapper').style.display = 'flex';
    };
    reader.readAsDataURL(file);
}

function submitPhotoSearch() {
    if (!currentPhotoFile) return;

    closeModal('photo-search-modal');
    isPhotoSearchActive = true;
    currentPage = 1;

    // Show photo banner and disable standard input
    document.getElementById('photo-search-banner').style.display = 'flex';
    const searchInput = document.getElementById('search-input');
    searchInput.value = 'Búsqueda por foto activa...';
    searchInput.disabled = true;
    document.querySelector('.search-section').classList.add('photo-search-active');
    document.getElementById('clear-search').style.display = 'none';

    fetchResults();
}

function clearPhotoSearch() {
    currentPhotoFile = null;
    isPhotoSearchActive = false;
    currentPage = 1;

    // Hide photo banner and restore standard input
    document.getElementById('photo-search-banner').style.display = 'none';
    const searchInput = document.getElementById('search-input');
    searchInput.value = '';
    searchInput.disabled = false;
    document.querySelector('.search-section').classList.remove('photo-search-active');
    
    currentQuery = '';
    fetchResults();
}

function fetchPhotoSearch() {
    const spinner = document.getElementById('loading-spinner');
    const grid = document.getElementById('results-grid');
    const noResults = document.getElementById('no-results');
    const resultsCount = document.getElementById('results-count');
    const pagination = document.getElementById('pagination-area');

    const formData = new FormData();
    formData.append('photo', currentPhotoFile);
    formData.append('status', currentStatus);
    formData.append('page', currentPage);

    fetch(`/api/buscar-foto`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        spinner.style.display = 'none';
        grid.style.opacity = '1';

        if (data.success) {
            renderGrid(data.people.data);
            updateStats(data.stats);

            // Update result counts
            const total = data.people.total;
            resultsCount.textContent = `${total} ${total === 1 ? 'coincidencia visual' : 'coincidencias visuales'}`;

            // Update Pagination
            lastPage = data.people.last_page;
            currentPage = data.people.current_page;
            
            if (lastPage > 1) {
                pagination.style.display = 'flex';
                document.getElementById('page-indicator').textContent = `Página ${currentPage} de ${lastPage}`;
                document.getElementById('prev-page-btn').disabled = (currentPage === 1);
                document.getElementById('next-page-btn').disabled = (currentPage === lastPage);
            } else {
                pagination.style.display = 'none';
            }
        } else {
            alert('Error: ' + (data.message || 'Error desconocido al buscar por foto.'));
            clearPhotoSearch();
        }
    })
    .catch(err => {
        console.error('Error fetching photo search results:', err);
        spinner.style.display = 'none';
        grid.style.opacity = '1';
        alert('Error de conexión al buscar por foto.');
        clearPhotoSearch();
    });
}

function fetchResultsSilently() {
    const url = `/api/buscar?query=${encodeURIComponent(currentQuery)}&status=${currentStatus}&photo=${currentPhotoFilter}&page=${currentPage}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            // Update local cache with fresh data
            localSearchCache[url] = data;

            renderGrid(data.people.data);
            updateStats(data.stats);
            
            const resultsCount = document.getElementById('results-count');
            const total = data.people.total;
            resultsCount.textContent = `${total} ${total === 1 ? 'caso encontrado' : 'casos encontrados'}`;

            lastPage = data.people.last_page;
            currentPage = data.people.current_page;
            
            const pagination = document.getElementById('pagination-area');
            if (lastPage > 1) {
                pagination.style.display = 'flex';
                document.getElementById('page-indicator').textContent = `Página ${currentPage} de ${lastPage}`;
                document.getElementById('prev-page-btn').disabled = (currentPage === 1);
                document.getElementById('next-page-btn').disabled = (currentPage === lastPage);
            } else {
                pagination.style.display = 'none';
            }
        })
        .catch(err => console.warn('Error in silent fetch:', err));
}

function zoomImage() {
    const photoImg = document.getElementById('detail-photo');
    if (!photoImg) return;
    const photoSrc = photoImg.src;
    // Check that it's a valid custom photo, not a placeholder
    if (photoSrc && !photoSrc.includes('svg+xml') && !photoSrc.includes('avatar') && !photoSrc.includes('placeholder')) {
        document.getElementById('zoomed-image').src = photoSrc;
        openModal('image-zoom-modal');
    }
}
