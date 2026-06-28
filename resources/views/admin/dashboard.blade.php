@extends('admin.layout')

@section('title', 'Consola de Control - Buscar Desaparecidos')

@section('styles')
<style>
    .admin-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }

    .admin-stat-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-md);
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: var(--shadow-sm);
    }

    .stat-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: var(--border-radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .icon-blue { background-color: var(--accent-primary-glow); color: var(--accent-primary); }
    .icon-violet { background-color: var(--accent-secondary-glow); color: var(--accent-secondary); }
    .icon-red { background-color: var(--state-missing-glow); color: var(--state-missing); }
    .icon-green { background-color: var(--state-found-glow); color: var(--state-found); }
    .icon-stone { background-color: var(--bg-input); color: var(--text-secondary); }

    .stat-details {
        display: flex;
        flex-direction: column;
    }

    .stat-num {
        font-family: var(--font-heading);
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.2;
    }

    .stat-txt {
        font-size: 0.85rem;
        color: var(--text-muted);
        font-weight: 500;
        margin-top: 0.2rem;
    }

    .dashboard-sections {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
    }

    @media (max-width: 1024px) {
        .dashboard-sections {
            grid-template-columns: 1fr;
        }
    }

    .panel-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        display: flex;
        flex-direction: column;
    }

    .panel-card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .panel-card-header h3 {
        font-family: var(--font-heading);
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .panel-card-body {
        padding: 1.5rem;
        flex: 1;
    }

    /* Scraper Box styling */
    .scraper-control-wrapper {
        background-color: var(--bg-input);
        border-radius: var(--border-radius-sm);
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        border: 1px solid var(--border-color);
    }

    .scraper-inputs {
        display: flex;
        gap: 1rem;
        align-items: flex-end;
        margin-top: 1rem;
    }

    .form-group-inline {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
        flex: 1;
    }

    .form-group-inline label {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .form-group-inline input {
        padding: 0.65rem;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-sm);
        font-family: var(--font-sans);
        outline: none;
    }

    .form-group-inline input:focus {
        border-color: var(--accent-primary);
    }

    .console-log-box {
        background-color: #1c1917; /* Slate/Stone 900 */
        color: #f5f5f4;
        font-family: 'Courier New', Courier, monospace;
        font-size: 0.85rem;
        padding: 1rem;
        border-radius: var(--border-radius-sm);
        height: 250px;
        overflow-y: auto;
        white-space: pre-wrap;
        line-height: 1.5;
        border: 1px solid #2e2a24;
    }

    .log-line-success { color: #4ade80; }
    .log-line-error { color: #f87171; }
    .log-line-info { color: #60a5fa; }

    .progress-bar-container {
        width: 100%;
        background-color: var(--border-color);
        height: 8px;
        border-radius: 4px;
        margin: 1rem 0;
        overflow: hidden;
        display: none;
    }

    .progress-bar-fill {
        height: 100%;
        background-color: var(--accent-primary);
        width: 0%;
        transition: width 0.3s ease;
    }
</style>
@endsection

@section('admin_content')
<div class="admin-header">
    <div class="admin-title">
        <h2>Panel de Control</h2>
        <p>Estadísticas y herramientas de administración del sistema</p>
    </div>
    <div class="admin-actions">
        <a href="{{ route('admin.casos.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-user-plus"></i> Registrar Nuevo Caso
        </a>
    </div>
</div>

<!-- Grid de Estadísticas -->
<div class="admin-stats-grid">
    <div class="admin-stat-card">
        <div class="stat-icon-wrapper icon-blue">
            <i class="fa-solid fa-users"></i>
        </div>
        <div class="stat-details">
            <span class="stat-num">{{ $stats['reported'] }}</span>
            <span class="stat-txt">Total Casos</span>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="stat-icon-wrapper icon-red">
            <i class="fa-solid fa-user-xmark"></i>
        </div>
        <div class="stat-details">
            <span class="stat-num">{{ $stats['missing'] }}</span>
            <span class="stat-txt">Desaparecidos Activos</span>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="stat-icon-wrapper icon-green">
            <i class="fa-solid fa-user-check"></i>
        </div>
        <div class="stat-details">
            <span class="stat-num">{{ $stats['found'] }}</span>
            <span class="stat-txt">Localizados</span>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="stat-icon-wrapper" style="background-color: rgba(68, 64, 60, 0.1); color: #44403c;">
            <i class="fa-solid fa-skull-crossbones"></i>
        </div>
        <div class="stat-details">
            <span class="stat-num">{{ $stats['deceased'] }}</span>
            <span class="stat-txt">Fallecidos</span>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="stat-icon-wrapper icon-violet" style="background-color: var(--accent-secondary-glow); color: var(--accent-secondary);">
            <i class="fa-solid fa-hospital"></i>
        </div>
        <div class="stat-details">
            <span class="stat-num">{{ $stats['hospitalized'] }}</span>
            <span class="stat-txt">En Hospitales (Drive)</span>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="stat-icon-wrapper icon-violet">
            <i class="fa-solid fa-cloud-arrow-down"></i>
        </div>
        <div class="stat-details">
            <span class="stat-num">{{ $stats['external_reports'] }}</span>
            <span class="stat-txt">Importados de Web</span>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="stat-icon-wrapper icon-stone">
            <i class="fa-solid fa-house-chimney-user"></i>
        </div>
        <div class="stat-details">
            <span class="stat-num">{{ $stats['local_reports'] }}</span>
            <span class="stat-txt">Creados Localmente</span>
        </div>
    </div>
</div>

<div class="dashboard-sections">
    <!-- Panel Izquierdo: Herramienta de Actualización de Scraper -->
    <div class="panel-card">
        <div class="panel-card-header">
            <h3><i class="fa-solid fa-arrows-spin"></i> Sincronización Remota Automática</h3>
            <span class="badge badge-success" style="background-color: var(--accent-primary-glow); color: var(--accent-primary); border: none;">Base de Datos: SQLite WAL</span>
        </div>
        <div class="panel-card-body">
            <p style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.5; margin-bottom: 1.5rem;">
                Esta herramienta descarga y actualiza los registros de personas desaparecidas en tiempo real desde <strong>buscardesaparecidos.com</strong> y <strong>venezuela-te-busca</strong>. El proceso corre de forma síncrona en el servidor con reporteo SSE (Server-Sent Events) en tiempo real para esta consola.
            </p>

            <div class="scraper-control-wrapper">
                <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary);"><i class="fa-solid fa-gears"></i> Ejecutar Raspado Manual</h4>
                <div class="scraper-inputs">
                    <div class="form-group-inline">
                        <label for="pages-count">Número de Páginas a escanear:</label>
                        <input type="number" id="pages-count" min="1" max="50" value="5">
                    </div>
                    <button class="btn btn-primary" id="btn-run-scraper" onclick="startScraping()">
                        <i class="fa-solid fa-play"></i> Iniciar Sincronización
                    </button>
                </div>
            </div>

            <!-- Barra de Progreso -->
            <div class="progress-bar-container" id="progress-container">
                <div class="progress-bar-fill" id="progress-fill"></div>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <h4 style="font-size: 0.9rem; font-weight: 700; color: var(--text-secondary);">Log del Servidor en Tiempo Real:</h4>
                <button class="btn btn-secondary btn-sm" onclick="clearConsole()" style="padding: 0.2rem 0.6rem; font-size: 0.75rem;"><i class="fa-solid fa-eraser"></i> Limpiar consola</button>
            </div>
            
            <div class="console-log-box" id="console-log">Esperando que se inicie el proceso de sincronización...</div>
        </div>
    </div>

    <!-- Panel Derecho: Acceso Rápido y Recursos -->
    <div class="panel-card" style="height: fit-content;">
        <div class="panel-card-header">
            <h3><i class="fa-solid fa-circle-info"></i> Estado del Sistema</h3>
        </div>
        <div class="panel-card-body">
            <ul style="list-style: none; display: flex; flex-direction: column; gap: 1rem; font-size: 0.9rem;">
                <li style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                    <span style="color: var(--text-muted);">Total de imágenes con Hash:</span>
                    <strong>{{ $stats['has_photo'] }}</strong>
                </li>
                <li style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                    <span style="color: var(--text-muted);">Casos sin imagen:</span>
                    <strong>{{ $stats['no_photo'] }}</strong>
                </li>
                <li style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                    <span style="color: var(--text-muted);">Entorno APP_ENV:</span>
                    <strong style="color: var(--accent-primary);">{{ env('APP_ENV') }}</strong>
                </li>
                <li style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                    <span style="color: var(--text-muted);">Estado del Debug:</span>
                    <strong style="color: {{ env('APP_DEBUG') ? 'var(--state-missing)' : 'var(--state-found)' }}">{{ env('APP_DEBUG') ? 'Activado (Peligro)' : 'Desactivado (Seguro)' }}</strong>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Sección Scraper Google Drive -->
<div style="margin-top: 2rem;">
    <div class="panel-card">
        <div class="panel-card-header">
            <h3><i class="fa-brands fa-google-drive"></i> Importar Listados de Hospitales (Google Drive)</h3>
            <span class="badge badge-success" style="background-color: var(--accent-secondary-glow); color: var(--accent-secondary); border: none;">PDF → Base de Datos</span>
        </div>
        <div class="panel-card-body">
            <p style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.5; margin-bottom: 1.5rem;">
                Esta herramienta descarga y procesa los <strong>archivos PDF</strong> del listado de personas ingresadas en centros de salud tras el sismo, publicados en una <strong>carpeta pública de Google Drive</strong>. Los nombres y datos extraídos se cruzan automáticamente con los registros existentes de desaparecidos.
            </p>

            <div class="scraper-control-wrapper">
                <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary);"><i class="fa-solid fa-file-pdf"></i> Ejecutar Raspado de PDFs de Drive</h4>
                <div class="scraper-inputs" style="margin-top: 1rem;">
                    <button class="btn btn-primary" id="btn-run-drive-scraper" onclick="startDriveScraping()">
                        <i class="fa-brands fa-google-drive"></i> Importar desde Drive
                    </button>
                </div>
            </div>

            <!-- Barra de Progreso Drive -->
            <div class="progress-bar-container" id="drive-progress-container">
                <div class="progress-bar-fill" id="drive-progress-fill"></div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <h4 style="font-size: 0.9rem; font-weight: 700; color: var(--text-secondary);">Log de Importación Drive:</h4>
                <button class="btn btn-secondary btn-sm" onclick="clearDriveConsole()" style="padding: 0.2rem 0.6rem; font-size: 0.75rem;"><i class="fa-solid fa-eraser"></i> Limpiar</button>
            </div>

            <div class="console-log-box" id="drive-console-log">Esperando que se inicie la importación desde Google Drive...</div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let eventSource = null;

    function clearConsole() {
        document.getElementById('console-log').innerHTML = '';
    }

    function startScraping() {
        const pages = document.getElementById('pages-count').value;
        const btn = document.getElementById('btn-run-scraper');
        const consoleLog = document.getElementById('console-log');
        const progressContainer = document.getElementById('progress-container');
        const progressFill = document.getElementById('progress-fill');

        if (eventSource) {
            eventSource.close();
        }

        // Deshabilitar botón
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Procesando...';
        
        consoleLog.innerHTML = `<span class="log-line-info">[INFO] Iniciando scraping de ${pages} páginas...\n</span>`;
        progressContainer.style.display = 'block';
        progressFill.style.width = '0%';

        // Generar URL con el token si está configurado en .env
        const token = "{{ env('SCRAPE_TOKEN') }}";
        let url = `/scrape/run?pages=${pages}`;
        if (token) {
            url += `&token=${token}`;
        }

        eventSource = new EventSource(url);

        eventSource.onmessage = function(event) {
            const data = JSON.parse(event.data);
            
            if (data.status === 'progress') {
                const percent = (data.page / data.total_pages) * 100;
                progressFill.style.width = `${percent}%`;
                
                consoleLog.innerHTML += `[PÁGINA ${data.page}/${data.total_pages}]:\n${data.message}\n`;
                consoleLog.innerHTML += `  -> Nuevos casos: +${data.imported} | Casos actualizados: +${data.updated}\n`;
                consoleLog.scrollTop = consoleLog.scrollHeight;
            } else if (data.status === 'completed') {
                progressFill.style.width = '100%';
                consoleLog.innerHTML += `\n<span class="log-line-success">[EXITO] ${data.message}</span>\n`;
                consoleLog.innerHTML += `[INFO] Estadísticas actuales: Total=${data.stats.reported} | Desaparecidos=${data.stats.missing} | Localizados=${data.stats.found} | Fallecidos=${data.stats.deceased ?? 0}\n`;
                consoleLog.scrollTop = consoleLog.scrollHeight;
                
                // Actualizar números de las stats superiores
                location.reload(); // Recarga simple para reflejar los nuevos totales de la base de datos
            } else if (data.status === 'error') {
                consoleLog.innerHTML += `<span class="log-line-error">[ERROR] ${data.message}</span>\n`;
                consoleLog.scrollTop = consoleLog.scrollHeight;
            }
        };

        eventSource.onerror = function(err) {
            consoleLog.innerHTML += `<span class="log-line-error">[CONEXIÓN CERRADA] El canal de eventos se cerró o finalizó.</span>\n`;
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-play"></i> Iniciar Sincronización';
            if (eventSource) {
                eventSource.close();
            }
        };
    }

    // ==========================================
    // Google Drive Scraper SSE
    // ==========================================
    let driveEventSource = null;

    function clearDriveConsole() {
        document.getElementById('drive-console-log').innerHTML = '';
    }

    function startDriveScraping() {
        const btn = document.getElementById('btn-run-drive-scraper');
        const consoleLog = document.getElementById('drive-console-log');
        const progressContainer = document.getElementById('drive-progress-container');
        const progressFill = document.getElementById('drive-progress-fill');

        if (driveEventSource) {
            driveEventSource.close();
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Procesando PDFs...';

        consoleLog.innerHTML = '<span class="log-line-info">[INFO] Conectando con Google Drive para extraer listados de hospitales...\n</span>';
        progressContainer.style.display = 'block';
        progressFill.style.width = '0%';

        driveEventSource = new EventSource('{{ route("admin.scrape-drive.run") }}');

        driveEventSource.onmessage = function(event) {
            const data = JSON.parse(event.data);

            if (data.status === 'progress') {
                const percent = (data.current / data.total) * 100;
                progressFill.style.width = `${percent}%`;

                consoleLog.innerHTML += `<span class="log-line-info">${data.message}</span>\n`;
                consoleLog.scrollTop = consoleLog.scrollHeight;
            } else if (data.status === 'completed') {
                progressFill.style.width = '100%';
                consoleLog.innerHTML += `\n<span class="log-line-success">[FINALIZADO] ${data.message}</span>\n`;
                if (data.stats) {
                    consoleLog.innerHTML += `[INFO] Estadísticas: Total=${data.stats.reported} | Desaparecidos=${data.stats.missing} | Localizados=${data.stats.found} | Fallecidos=${data.stats.deceased ?? 0}\n`;
                }
                consoleLog.scrollTop = consoleLog.scrollHeight;

                btn.disabled = false;
                btn.innerHTML = '<i class="fa-brands fa-google-drive"></i> Importar desde Drive';

                // Recargar para reflejar nuevos totales
                setTimeout(() => location.reload(), 3000);
            } else if (data.status === 'error') {
                consoleLog.innerHTML += `<span class="log-line-error">${data.message}</span>\n`;
                consoleLog.scrollTop = consoleLog.scrollHeight;
            } else if (data.status === 'info') {
                consoleLog.innerHTML += `<span class="log-line-info">${data.message}</span>\n`;
                consoleLog.scrollTop = consoleLog.scrollHeight;
            }
        };

        driveEventSource.onerror = function(err) {
            consoleLog.innerHTML += `<span class="log-line-error">[CONEXIÓN CERRADA] El canal SSE finalizó.</span>\n`;
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-brands fa-google-drive"></i> Importar desde Drive';
            if (driveEventSource) {
                driveEventSource.close();
            }
        };
    }
</script>
@endsection
