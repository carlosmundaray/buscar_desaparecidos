@extends('layouts.layout')

@section('title', 'API Developer Portal — Buscar Desaparecidos Venezuela')

@section('content')
<div class="api-docs-container">
    <!-- Sidebar de Navegación -->
    <aside class="api-sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fa-solid fa-server"></i>
                <span>Developer Portal</span>
            </div>
            <a href="{{ route('home') }}" class="back-home-btn">
                <i class="fa-solid fa-arrow-left"></i> Volver al Inicio
            </a>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-title">Primeros Pasos</div>
            <ul>
                <li><a href="#intro" class="nav-link active"><i class="fa-solid fa-circle-info"></i> Introducción</a></li>
                <li><a href="#cors" class="nav-link"><i class="fa-solid fa-globe"></i> CORS & Cabeceras</a></li>
                <li><a href="#rate-limiting" class="nav-link"><i class="fa-solid fa-gauge"></i> Límites de Tasa</a></li>
            </ul>

            <div class="nav-section-title">Endpoints API</div>
            <ul>
                <li>
                    <a href="#get-buscar" class="nav-link">
                        <span class="badge badge-get">GET</span> /api/buscar
                    </a>
                </li>
                <li>
                    <a href="#get-sincronizar" class="nav-link">
                        <span class="badge badge-get">GET</span> /api/sincronizar...
                    </a>
                </li>
                <li>
                    <a href="#get-centros" class="nav-link">
                        <span class="badge badge-get">GET</span> /api/centros
                    </a>
                </li>
                <li>
                    <a href="#post-buscar-foto" class="nav-link">
                        <span class="badge badge-post">POST</span> /api/buscar-foto
                    </a>
                </li>
                <li>
                    <a href="#post-reportar" class="nav-link">
                        <span class="badge badge-post">POST</span> /api/reportar
                    </a>
                </li>
                <li>
                    <a href="#post-localizado" class="nav-link">
                        <span class="badge badge-post">POST</span> /api/caso/...
                    </a>
                </li>
            </ul>
        </nav>
        <div class="sidebar-footer">
            <p>&copy; {{ date('Y') }} Buscar Desaparecidos</p>
            <span>v1.0.0 API Docs</span>
        </div>
    </aside>

    <!-- Contenido Principal -->
    <main class="api-content">
        <!-- Sección: Introducción -->
        <section id="intro" class="api-section">
            <div class="section-left">
                <h1>Documentación de la API</h1>
                <p class="lead">
                    Bienvenido a la API oficial de <strong>Buscar Desaparecidos Venezuela</strong>. Esta API permite a desarrolladores conectar sistemas externos, aplicaciones móviles o servicios de contingencia sísmica para reportar personas, buscar en el registro unificado y sincronizar datos en tiempo real.
                </p>
                <div class="info-card">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div>
                        <strong>Base URL:</strong>
                        <code>{{ url('/') }}</code>
                    </div>
                </div>
                <p>
                    Todas las respuestas del servidor se retornan en formato JSON. Las solicitudes de escritura (POST) no requieren token CSRF, lo que facilita su integración directa en backends, scripts automatizados o aplicaciones nativas.
                </p>
            </div>
            <div class="section-right">
                <div class="code-box">
                    <div class="code-header">
                        <span>Ejemplo de Endpoint Base</span>
                    </div>
                    <pre><code class="language-bash">GET {{ url('/') }}/api/buscar</code></pre>
                </div>
            </div>
        </section>

        <!-- Sección: CORS y Cabeceras -->
        <section id="cors" class="api-section">
            <div class="section-left">
                <h2>CORS & Cabeceras</h2>
                <p>
                    Para permitir integraciones directas desde el navegador (como aplicaciones React, Vue o Angular alojadas en otros dominios), la API tiene habilitado <strong>CORS (Cross-Origin Resource Sharing)</strong> globalmente.
                </p>
                <p>
                    El servidor responde automáticamente a las peticiones CORS inyectando las siguientes cabeceras HTTP:
                </p>
                <table class="params-table">
                    <thead>
                        <tr>
                            <th>Cabecera</th>
                            <th>Valor</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>Access-Control-Allow-Origin</code></td>
                            <td><code>*</code></td>
                            <td>Permite peticiones desde cualquier origen/dominio.</td>
                        </tr>
                        <tr>
                            <td><code>Access-Control-Allow-Methods</code></td>
                            <td><code>*</code> (GET, POST, etc.)</td>
                            <td>Permite todos los métodos HTTP estándar de la API.</td>
                        </tr>
                        <tr>
                            <td><code>Content-Type</code></td>
                            <td><code>application/json</code></td>
                            <td>Formato de respuesta predeterminado.</td>
                        </tr>
                        <tr>
                            <td><code>Accept</code></td>
                            <td><code>application/json</code></td>
                            <td>Debe enviarse en la petición para forzar respuestas en JSON.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="section-right">
                <div class="code-box">
                    <div class="code-header">
                        <span>Headers Sugeridos</span>
                    </div>
                    <pre><code class="language-json">{
  "Accept": "application/json",
  "Content-Type": "application/json"
}</code></pre>
                </div>
            </div>
        </section>

        <!-- Sección: Rate Limiting -->
        <section id="rate-limiting" class="api-section">
            <div class="section-left">
                <h2>Límites de Tasa (Rate Limiting)</h2>
                <p>
                    Para garantizar la disponibilidad y estabilidad del servidor, se aplican límites de tasa de peticiones basados en la dirección IP del cliente.
                </p>
                <p>
                    Las respuestas HTTP contienen cabeceras que indican el estado de su límite de tasa:
                </p>
                <ul>
                    <li><code>X-RateLimit-Limit</code>: El número total de peticiones permitidas por minuto.</li>
                    <li><code>X-RateLimit-Remaining</code>: El número de peticiones que le restan en el periodo actual.</li>
                </ul>
                <table class="params-table">
                    <thead>
                        <tr>
                            <th>Ruta</th>
                            <th>Límite</th>
                            <th>Propósito</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>/api/buscar</code></td>
                            <td>60 peticiones/min</td>
                            <td>Búsqueda pública por texto</td>
                        </tr>
                        <tr>
                            <td><code>/api/sincronizar-busqueda</code></td>
                            <td>5 peticiones/min</td>
                            <td>Sincronización masiva delta</td>
                        </tr>
                        <tr>
                            <td><code>/api/buscar-foto</code></td>
                            <td>10 peticiones/min</td>
                            <td>Procesamiento visual de rostros</td>
                        </tr>
                        <tr>
                            <td><code>/api/reportar</code></td>
                            <td>10 peticiones/min</td>
                            <td>Creación de nuevos reportes</td>
                        </tr>
                        <tr>
                            <td><code>/api/caso/{id}/localizado</code></td>
                            <td>10 peticiones/min</td>
                            <td>Actualización de estado a localizado</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="section-right">
                <div class="code-box">
                    <div class="code-header">
                        <span>Ejemplo de headers excedidos (429)</span>
                    </div>
                    <pre><code class="language-http">HTTP/1.1 429 Too Many Requests
Retry-After: 60
Content-Type: application/json

{
  "message": "Too Many Attempts."
}</code></pre>
                </div>
            </div>
        </section>

        <hr class="section-divider">

        <!-- Sección: GET /api/buscar -->
        <section id="get-buscar" class="api-section">
            <div class="section-left">
                <div class="endpoint-badge-wrapper">
                    <span class="badge badge-get">GET</span>
                    <code>/api/buscar</code>
                </div>
                <h3>Buscar Personas Desaparecidas</h3>
                <p>
                    Realiza consultas en el registro general de personas desaparecidas filtrando por texto, estado del caso, género y localización. Retorna resultados paginados junto a estadísticas generales.
                </p>

                <h4>Parámetros de Consulta (Query Params)</h4>
                <table class="params-table">
                    <thead>
                        <tr>
                            <th>Parámetro</th>
                            <th>Tipo</th>
                            <th>Requerido</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>query</code></td>
                            <td>string</td>
                            <td>No</td>
                            <td>Texto a buscar en el nombre, alias o cédula.</td>
                        </tr>
                        <tr>
                            <td><code>status</code></td>
                            <td>string</td>
                            <td>No</td>
                            <td>Estado del caso: <code>missing</code> (desaparecido), <code>found</code> (localizado), <code>deceased</code> (fallecido), <code>hospitalized</code> (hospitalizado).</td>
                        </tr>
                        <tr>
                            <td><code>gender</code></td>
                            <td>string</td>
                            <td>No</td>
                            <td>Género: <code>Masculino</code> o <code>Femenino</code>.</td>
                        </tr>
                        <tr>
                            <td><code>state</code></td>
                            <td>string</td>
                            <td>No</td>
                            <td>Nombre del estado o provincia en Venezuela.</td>
                        </tr>
                        <tr>
                            <td><code>city</code></td>
                            <td>string</td>
                            <td>No</td>
                            <td>Nombre de la ciudad.</td>
                        </tr>
                        <tr>
                            <td><code>page</code></td>
                            <td>integer</td>
                            <td>No</td>
                            <td>Número de página para paginación (por defecto es 1). Cada página retorna 24 resultados.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="section-right">
                <div class="code-playground">
                    <div class="playground-tabs">
                        <button class="tab-btn active" onclick="switchLang('get-buscar', 'curl')">cURL</button>
                        <button class="tab-btn" onclick="switchLang('get-buscar', 'js')">JavaScript</button>
                        <button class="tab-btn" onclick="switchLang('get-buscar', 'python')">Python</button>
                        <button class="tab-btn" onclick="switchLang('get-buscar', 'php')">PHP</button>
                        <button class="copy-btn" onclick="copySnippet('get-buscar')"><i class="fa-regular fa-copy"></i></button>
                    </div>
                    <div class="playground-snippets" id="get-buscar-snippets">
                        <!-- cURL -->
                        <pre class="snippet-code active" data-lang="curl"><code>curl -i "{{ url('/api/buscar?query=Juan&status=missing') }}" \
  -H "Accept: application/json"</code></pre>
                        <!-- JavaScript -->
                        <pre class="snippet-code" data-lang="js"><code>fetch('{{ url('/api/buscar?query=Juan&status=missing') }}', {
  headers: {
    'Accept': 'application/json'
  }
})
.then(res => res.json())
.then(data => console.log(data))
.catch(err => console.error(err));</code></pre>
                        <!-- Python -->
                        <pre class="snippet-code" data-lang="python"><code>import requests

url = "{{ url('/api/buscar') }}"
params = {
    'query': 'Juan',
    'status': 'missing'
}
headers = {
    'Accept': 'application/json'
}

response = requests.get(url, params=params, headers=headers)
print(response.json())</code></pre>
                        <!-- PHP -->
                        <pre class="snippet-code" data-lang="php"><code>&lt;?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "{{ url('/api/buscar?query=Juan&status=missing') }}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json'
]);

$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);
print_r($data);</code></pre>
                    </div>
                </div>

                <div class="code-box response-box">
                    <div class="code-header">
                        <span>Respuesta Exitosa (200 OK)</span>
                    </div>
                    <pre><code class="language-json">{
  "people": {
    "current_page": 1,
    "data": [
      {
        "id": 125,
        "code": "BD-3X7W9Y",
        "full_name": "Juan Perez",
        "alias": "El Negro",
        "cedula": "12345678",
        "age": 32,
        "gender": "Masculino",
        "last_seen_at": "2026-06-25T00:00:00.000000Z",
        "last_seen_location": "Av. Sucre, Caracas",
        "city": "Caracas",
        "state": "Distrito Capital",
        "description": "Cicatriz en brazo derecho, mide 1.75m.",
        "photo_path": "https://midominio.com/storage/photos/juan.jpg",
        "status": "missing",
        "created_at": "2026-06-26T03:00:00.000000Z"
      }
    ],
    "total": 1
  },
  "stats": {
    "reported": 12619,
    "missing": 9607,
    "found": 3000,
    "deceased": 12,
    "hospitalized": 1064
  }
}</code></pre>
                </div>
            </div>
        </section>

        <hr class="section-divider">

        <!-- Sección: GET /api/sincronizar-busqueda -->
        <section id="get-sincronizar" class="api-section">
            <div class="section-left">
                <div class="endpoint-badge-wrapper">
                    <span class="badge badge-get">GET</span>
                    <code>/api/sincronizar-busqueda</code>
                </div>
                <h3>Sincronizar Casos (Sincronización Delta)</h3>
                <p>
                    Permite obtener de manera eficiente los registros que han sido creados o modificados a partir de una fecha/hora específica. Esto evita descargar la base de datos completa y facilita el mantenimiento de una base de datos local replicada.
                </p>

                <h4>Parámetros de Consulta (Query Params)</h4>
                <table class="params-table">
                    <thead>
                        <tr>
                            <th>Parámetro</th>
                            <th>Tipo</th>
                            <th>Requerido</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>last_sync</code></td>
                            <td>string</td>
                            <td>No</td>
                            <td>Fecha en formato ISO 8601 o estándar de base de datos (Ej: <code>2026-06-27 12:00:00</code> o <code>2026-06-27T12:00:00Z</code>). Si no se envía, retornará los casos recientes.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="section-right">
                <div class="code-playground">
                    <div class="playground-tabs">
                        <button class="tab-btn active" onclick="switchLang('get-sincronizar', 'curl')">cURL</button>
                        <button class="tab-btn" onclick="switchLang('get-sincronizar', 'js')">JavaScript</button>
                        <button class="tab-btn" onclick="switchLang('get-sincronizar', 'python')">Python</button>
                        <button class="tab-btn" onclick="switchLang('get-sincronizar', 'php')">PHP</button>
                        <button class="copy-btn" onclick="copySnippet('get-sincronizar')"><i class="fa-regular fa-copy"></i></button>
                    </div>
                    <div class="playground-snippets" id="get-sincronizar-snippets">
                        <!-- cURL -->
                        <pre class="snippet-code active" data-lang="curl"><code>curl -i "{{ url('/api/sincronizar-busqueda?last_sync=2026-06-27+15:00:00') }}" \
  -H "Accept: application/json"</code></pre>
                        <!-- JavaScript -->
                        <pre class="snippet-code" data-lang="js"><code>fetch('{{ url('/api/sincronizar-busqueda?last_sync=2026-06-27T15:00:00Z') }}', {
  headers: {
    'Accept': 'application/json'
  }
})
.then(res => res.json())
.then(data => console.log(data));</code></pre>
                        <!-- Python -->
                        <pre class="snippet-code" data-lang="python"><code>import requests

url = "{{ url('/api/sincronizar-busqueda') }}"
params = {'last_sync': '2026-06-27 15:00:00'}
headers = {'Accept': 'application/json'}

response = requests.get(url, params=params, headers=headers)
print(response.json())</code></pre>
                        <!-- PHP -->
                        <pre class="snippet-code" data-lang="php"><code>&lt;?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "{{ url('/api/sincronizar-busqueda?last_sync=2026-06-27+15:00:00') }}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

$response = curl_exec($ch);
curl_close($ch);
print_r(json_decode($response, true));</code></pre>
                    </div>
                </div>

                <div class="code-box response-box">
                    <div class="code-header">
                        <span>Respuesta Exitosa (200 OK)</span>
                    </div>
                    <pre><code class="language-json">{
  "success": true,
  "people": {
    "current_page": 1,
    "data": [
      {
        "id": 125,
        "code": "BD-3X7W9Y",
        "full_name": "Juan Perez",
        "alias": "El Negro",
        "cedula": "12345678",
        "status": "found",
        "found_at": "2026-06-27T16:30:00.000000Z",
        "found_location": "Encontrado en Hospital de La Guaira",
        "created_at": "2026-06-26T03:00:00.000000Z",
        "updated_at": "2026-06-27T16:30:00.000000Z"
      }
    ],
    "total": 1
  }
}</code></pre>
                </div>
            </div>
        </section>

        <hr class="section-divider">

        <!-- Sección: GET /api/centros -->
        <section id="get-centros" class="api-section">
            <div class="section-left">
                <div class="endpoint-badge-wrapper">
                    <span class="badge badge-get">GET</span>
                    <code>/api/centros</code>
                </div>
                <h3>Obtener Centros de Acopio y Refugios</h3>
                <p>
                    Retorna el listado completo o filtrado de los centros de acopio autorizados y refugios/albergues temporales en Venezuela. Permite a otras aplicaciones geolocalizar la ayuda o indicar dónde llevar donaciones.
                </p>

                <h4>Parámetros de Consulta (Query Params)</h4>
                <table class="params-table">
                    <thead>
                        <tr>
                            <th>Parámetro</th>
                            <th>Tipo</th>
                            <th>Requerido</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>query</code></td>
                            <td>string</td>
                            <td>No</td>
                            <td>Texto de búsqueda para filtrar por nombre del centro o dirección.</td>
                        </tr>
                        <tr>
                            <td><code>city</code></td>
                            <td>string</td>
                            <td>No</td>
                            <td>Nombre de la ciudad (Ej. <code>Caracas</code>, <code>Valencia</code>, <code>Maracaibo</code>).</td>
                        </tr>
                        <tr>
                            <td><code>type</code></td>
                            <td>string</td>
                            <td>No</td>
                            <td>Tipo de recurso: <code>acopio</code> (Centros de acopio) o <code>refugio</code> (Refugios/albergues).</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="section-right">
                <div class="code-playground">
                    <div class="playground-tabs">
                        <button class="tab-btn active" onclick="switchLang('get-centros', 'curl')">cURL</button>
                        <button class="tab-btn" onclick="switchLang('get-centros', 'js')">JavaScript</button>
                        <button class="tab-btn" onclick="switchLang('get-centros', 'python')">Python</button>
                        <button class="tab-btn" onclick="switchLang('get-centros', 'php')">PHP</button>
                        <button class="copy-btn" onclick="copySnippet('get-centros')"><i class="fa-regular fa-copy"></i></button>
                    </div>
                    <div class="playground-snippets" id="get-centros-snippets">
                        <!-- cURL -->
                        <pre class="snippet-code active" data-lang="curl"><code>curl -i "{{ url('/api/centros?city=Valencia&type=acopio') }}" \
  -H "Accept: application/json"</code></pre>
                        <!-- JavaScript -->
                        <pre class="snippet-code" data-lang="js"><code>fetch('{{ url('/api/centros?city=Valencia&type=acopio') }}', {
  headers: {
    'Accept': 'application/json'
  }
})
.then(res => res.json())
.then(data => console.log(data));</code></pre>
                        <!-- Python -->
                        <pre class="snippet-code" data-lang="python"><code>import requests

url = "{{ url('/api/centros') }}"
params = {
    'city': 'Valencia',
    'type': 'acopio'
}
headers = {'Accept': 'application/json'}

response = requests.get(url, params=params, headers=headers)
print(response.json())</code></pre>
                        <!-- PHP -->
                        <pre class="snippet-code" data-lang="php"><code>&lt;?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "{{ url('/api/centros?city=Valencia&type=acopio') }}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

$response = curl_exec($ch);
curl_close($ch);
print_r(json_decode($response, true));</code></pre>
                    </div>
                </div>

                <div class="code-box response-box">
                    <div class="code-header">
                        <span>Respuesta Exitosa (200 OK)</span>
                    </div>
                    <pre><code class="language-json">{
  "success": true,
  "total": 1,
  "centros": [
    {
      "id": 14,
      "type": "acopio",
      "name": "Vecinos La Isabelica",
      "address": "La Isabelica, sector 5 (Av. Principal), Valencia, Carabobo",
      "city": "Valencia",
      "receives": [
        "agua",
        "alimentos no perecederos",
        "ropa",
        "medicamentos"
      ],
      "contact": null,
      "lat": 10.1636281,
      "lng": -67.9694285
    }
  ]
}</code></pre>
                </div>
            </div>
        </section>

        <hr class="section-divider">

        <!-- Sección: POST /api/buscar-foto -->
        <section id="post-buscar-foto" class="api-section">
            <div class="section-left">
                <div class="endpoint-badge-wrapper">
                    <span class="badge badge-post">POST</span>
                    <code>/api/buscar-foto</code>
                </div>
                <h3>Búsqueda Visual por Foto</h3>
                <p>
                    Compara características biométricas o visuales cargando una fotografía en formato binario (`multipart/form-data`). El servidor calcula el hash perceptual de la foto y busca coincidencias aproximadas con menor distancia de Hamming en el sistema.
                </p>

                <h4>Parámetros del Body (Multipart Form-Data)</h4>
                <table class="params-table">
                    <thead>
                        <tr>
                            <th>Parámetro</th>
                            <th>Tipo</th>
                            <th>Requerido</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>photo</code></td>
                            <td>file</td>
                            <td>Sí</td>
                            <td>Archivo de imagen física a buscar. Formatos admitidos: <code>jpg, jpeg, png, webp</code>. Tamaño máximo: <code>2MB</code>.</td>
                        </tr>
                        <tr>
                            <td><code>status</code></td>
                            <td>string</td>
                            <td>No</td>
                            <td>Filtrar coincidencias por estado (Ej. <code>missing</code>).</td>
                        </tr>
                        <tr>
                            <td><code>page</code></td>
                            <td>integer</td>
                            <td>No</td>
                            <td>Número de página de resultados.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="section-right">
                <div class="code-playground">
                    <div class="playground-tabs">
                        <button class="tab-btn active" onclick="switchLang('post-buscar-foto', 'curl')">cURL</button>
                        <button class="tab-btn" onclick="switchLang('post-buscar-foto', 'js')">JavaScript</button>
                        <button class="tab-btn" onclick="switchLang('post-buscar-foto', 'python')">Python</button>
                        <button class="tab-btn" onclick="switchLang('post-buscar-foto', 'php')">PHP</button>
                        <button class="copy-btn" onclick="copySnippet('post-buscar-foto')"><i class="fa-regular fa-copy"></i></button>
                    </div>
                    <div class="playground-snippets" id="post-buscar-foto-snippets">
                        <!-- cURL -->
                        <pre class="snippet-code active" data-lang="curl"><code>curl -i -X POST "{{ url('/api/buscar-foto') }}" \
  -H "Accept: application/json" \
  -F "photo=@/ruta/local/foto.jpg" \
  -F "status=missing"</code></pre>
                        <!-- JavaScript -->
                        <pre class="snippet-code" data-lang="js"><code>const formData = new FormData();
// Suponiendo que 'fileInput' es un elemento HTML &lt;input type="file"&gt;
formData.append('photo', fileInput.files[0]);
formData.append('status', 'missing');

fetch('{{ url('/api/buscar-foto') }}', {
  method: 'POST',
  headers: {
    'Accept': 'application/json'
  },
  body: formData
})
.then(res => res.json())
.then(data => console.log(data));</code></pre>
                        <!-- Python -->
                        <pre class="snippet-code" data-lang="python"><code>import requests

url = "{{ url('/api/buscar-foto') }}"
files = {'photo': open('foto.jpg', 'rb')}
data = {'status': 'missing'}
headers = {'Accept': 'application/json'}

response = requests.post(url, files=files, data=data, headers=headers)
print(response.json())</code></pre>
                        <!-- PHP -->
                        <pre class="snippet-code" data-lang="php"><code>&lt;?php
$ch = curl_init();
$file = new CURLFile('foto.jpg', 'image/jpeg', 'photo');

curl_setopt($ch, CURLOPT_URL, "{{ url('/api/buscar-foto') }}");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'photo' => $file,
    'status' => 'missing'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);
print_r(json_decode($response, true));</code></pre>
                    </div>
                </div>

                <div class="code-box response-box">
                    <div class="code-header">
                        <span>Respuesta Coincidencia (200 OK)</span>
                    </div>
                    <pre><code class="language-json">{
  "success": true,
  "people": {
    "current_page": 1,
    "data": [
      {
        "id": 84,
        "code": "BD-9A8W1Q",
        "full_name": "Maria Alejandra Gomez",
        "alias": "Maruja",
        "status": "missing",
        "photo_path": "https://midominio.com/storage/photos/maria.jpg",
        "last_seen_location": "Chacao, Caracas",
        "similarity": 98.4
      }
    ],
    "total": 1
  }
}</code></pre>
                </div>
            </div>
        </section>

        <hr class="section-divider">

        <!-- Sección: POST /api/reportar -->
        <section id="post-reportar" class="api-section">
            <div class="section-left">
                <div class="endpoint-badge-wrapper">
                    <span class="badge badge-post">POST</span>
                    <code>/api/reportar</code>
                </div>
                <h3>Reportar Persona Desaparecida</h3>
                <p>
                    Registra un caso nuevo en el sistema. Los datos enviados se procesan e insertan directamente. Si se incluye una fotografía (`photo`), el sistema generará y almacenará su hash de similitud visual de forma asíncrona para habilitar búsquedas por foto.
                </p>

                <h4>Parámetros de Entrada (Multipart Form Body)</h4>
                <table class="params-table">
                    <thead>
                        <tr>
                            <th>Campo</th>
                            <th>Tipo</th>
                            <th>Requerido</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>full_name</code></td>
                            <td>string</td>
                            <td>Sí</td>
                            <td>Nombre completo de la persona desaparecida.</td>
                        </tr>
                        <tr>
                            <td><code>alias</code></td>
                            <td>string</td>
                            <td>No</td>
                            <td>Apodo o alias familiar.</td>
                        </tr>
                        <tr>
                            <td><code>cedula</code></td>
                            <td>string</td>
                            <td>No</td>
                            <td>Número de cédula de identidad nacional.</td>
                        </tr>
                        <tr>
                            <td><code>age</code></td>
                            <td>integer</td>
                            <td>No</td>
                            <td>Edad.</td>
                        </tr>
                        <tr>
                            <td><code>gender</code></td>
                            <td>string</td>
                            <td>No</td>
                            <td>Debe ser <code>Masculino</code>, <code>Femenino</code> u <code>Otro</code>.</td>
                        </tr>
                        <tr>
                            <td><code>last_seen_at</code></td>
                            <td>date</td>
                            <td>No</td>
                            <td>Fecha en formato ISO <code>YYYY-MM-DD</code>.</td>
                        </tr>
                        <tr>
                            <td><code>last_seen_location</code></td>
                            <td>string</td>
                            <td>Sí</td>
                            <td>Dirección o descripción del lugar donde fue vista por última vez.</td>
                        </tr>
                        <tr>
                            <td><code>city</code></td>
                            <td>string</td>
                            <td>No</td>
                            <td>Ciudad donde desapareció.</td>
                        </tr>
                        <tr>
                            <td><code>state</code></td>
                            <td>string</td>
                            <td>No</td>
                            <td>Nombre del estado.</td>
                        </tr>
                        <tr>
                            <td><code>description</code></td>
                            <td>string</td>
                            <td>Sí</td>
                            <td>Descripción física detallada (cabello, señas, vestimenta, etc.).</td>
                        </tr>
                        <tr>
                            <td><code>photo</code></td>
                            <td>file</td>
                            <td>No</td>
                            <td>Archivo de imagen de la persona (máx 2MB, formats: jpg, png).</td>
                        </tr>
                        <tr>
                            <td><code>reporter_name</code></td>
                            <td>string</td>
                            <td>Sí</td>
                            <td>Nombre completo del informante / familiar.</td>
                        </tr>
                        <tr>
                            <td><code>reporter_phone</code></td>
                            <td>string</td>
                            <td>Sí</td>
                            <td>Teléfono del informante (para control y verificación).</td>
                        </tr>
                        <tr>
                            <td><code>reporter_email</code></td>
                            <td>string</td>
                            <td>No</td>
                            <td>Correo electrónico del informante.</td>
                        </tr>
                        <tr>
                            <td><code>relationship</code></td>
                            <td>string</td>
                            <td>Sí</td>
                            <td>Vínculo con el desaparecido: <code>Familiar</code>, <code>Amigo</code>, <code>Vecino</code>, <code>Otro</code>.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="section-right">
                <div class="code-playground">
                    <div class="playground-tabs">
                        <button class="tab-btn active" onclick="switchLang('post-reportar', 'curl')">cURL</button>
                        <button class="tab-btn" onclick="switchLang('post-reportar', 'js')">JavaScript</button>
                        <button class="tab-btn" onclick="switchLang('post-reportar', 'python')">Python</button>
                        <button class="tab-btn" onclick="switchLang('post-reportar', 'php')">PHP</button>
                        <button class="copy-btn" onclick="copySnippet('post-reportar')"><i class="fa-regular fa-copy"></i></button>
                    </div>
                    <div class="playground-snippets" id="post-reportar-snippets">
                        <!-- cURL -->
                        <pre class="snippet-code active" data-lang="curl"><code>curl -i -X POST "{{ url('/api/reportar') }}" \
  -H "Accept: application/json" \
  -F "full_name=Carlos Rodriguez" \
  -F "last_seen_location=Baruta, Miranda" \
  -F "description=Mide 1.80m, usa lentes, franela roja" \
  -F "reporter_name=Laura Rodriguez" \
  -F "reporter_phone=0414-7654321" \
  -F "relationship=Familiar"</code></pre>
                        <!-- JavaScript -->
                        <pre class="snippet-code" data-lang="js"><code>const formData = new FormData();
formData.append('full_name', 'Carlos Rodriguez');
formData.append('last_seen_location', 'Baruta, Miranda');
formData.append('description', 'Mide 1.80m, usa lentes, franela roja');
formData.append('reporter_name', 'Laura Rodriguez');
formData.append('reporter_phone', '0414-7654321');
formData.append('relationship', 'Familiar');

fetch('{{ url('/api/reportar') }}', {
  method: 'POST',
  headers: {
    'Accept': 'application/json'
  },
  body: formData
})
.then(res => res.json())
.then(data => console.log(data));</code></pre>
                        <!-- Python -->
                        <pre class="snippet-code" data-lang="python"><code>import requests

url = "{{ url('/api/reportar') }}"
data = {
    'full_name': 'Carlos Rodriguez',
    'last_seen_location': 'Baruta, Miranda',
    'description': 'Mide 1.80m, usa lentes, franela roja',
    'reporter_name': 'Laura Rodriguez',
    'reporter_phone': '0414-7654321',
    'relationship': 'Familiar'
}
headers = {'Accept': 'application/json'}

response = requests.post(url, data=data, headers=headers)
print(response.json())</code></pre>
                        <!-- PHP -->
                        <pre class="snippet-code" data-lang="php"><code>&lt;?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "{{ url('/api/reportar') }}");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'full_name' => 'Carlos Rodriguez',
    'last_seen_location' => 'Baruta, Miranda',
    'description' => 'Mide 1.80m, usa lentes, franela roja',
    'reporter_name' => 'Laura Rodriguez',
    'reporter_phone' => '0414-7654321',
    'relationship' => 'Familiar'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);
print_r(json_decode($response, true));</code></pre>
                    </div>
                </div>

                <div class="code-box response-box">
                    <div class="code-header">
                        <span>Respuesta Registro Correcto (200 OK)</span>
                    </div>
                    <pre><code class="language-json">{
  "success": true,
  "message": "Reporte registrado exitosamente.",
  "person": {
    "id": 204,
    "code": "BD-8K9A2W",
    "full_name": "Carlos Rodriguez",
    "last_seen_location": "Baruta, Miranda",
    "status": "missing",
    "created_at": "2026-06-28T05:25:00.000000Z"
  }
}</code></pre>
                </div>
            </div>
        </section>

        <hr class="section-divider">

        <!-- Sección: POST /api/caso/{id}/localizado -->
        <section id="post-localizado" class="api-section">
            <div class="section-left">
                <div class="endpoint-badge-wrapper">
                    <span class="badge badge-post">POST</span>
                    <code>/api/caso/{id}/localizado</code>
                </div>
                <h3>Marcar Persona como Localizada</h3>
                <p>
                    Permite actualizar el estado de una persona a `found` (localizado/a) indicando de manera opcional detalles geográficos o circunstancias del reencuentro.
                </p>

                <h4>Parámetros de Ruta (URI Parameters)</h4>
                <table class="params-table">
                    <thead>
                        <tr>
                            <th>Parámetro</th>
                            <th>Tipo</th>
                            <th>Requerido</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>id</code></td>
                            <td>integer</td>
                            <td>Sí</td>
                            <td>El identificador único numérico del caso (ID interno).</td>
                        </tr>
                    </tbody>
                </table>

                <h4>Parámetros del Body (JSON o Form Body)</h4>
                <table class="params-table">
                    <thead>
                        <tr>
                            <th>Campo</th>
                            <th>Tipo</th>
                            <th>Requerido</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>found_location</code></td>
                            <td>string</td>
                            <td>No</td>
                            <td>Circunstancias del reencuentro o localización actual (Ej. <code>Localizado en casa de familiares, sano y salvo</code>).</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="section-right">
                <div class="code-playground">
                    <div class="playground-tabs">
                        <button class="tab-btn active" onclick="switchLang('post-localizado', 'curl')">cURL</button>
                        <button class="tab-btn" onclick="switchLang('post-localizado', 'js')">JavaScript</button>
                        <button class="tab-btn" onclick="switchLang('post-localizado', 'python')">Python</button>
                        <button class="tab-btn" onclick="switchLang('post-localizado', 'php')">PHP</button>
                        <button class="copy-btn" onclick="copySnippet('post-localizado')"><i class="fa-regular fa-copy"></i></button>
                    </div>
                    <div class="playground-snippets" id="post-localizado-snippets">
                        <!-- cURL -->
                        <pre class="snippet-code active" data-lang="curl"><code>curl -i -X POST "{{ url('/api/caso/125/localizado') }}" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"found_location": "Sano y salvo en el centro médico La Trinidad"}'</code></pre>
                        <!-- JavaScript -->
                        <pre class="snippet-code" data-lang="js"><code>fetch('{{ url('/api/caso/125/localizado') }}', {
  method: 'POST',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    found_location: 'Sano y salvo en el centro médico La Trinidad'
  })
})
.then(res => res.json())
.then(data => console.log(data));</code></pre>
                        <!-- Python -->
                        <pre class="snippet-code" data-lang="python"><code>import requests

url = "{{ url('/api/caso/125/localizado') }}"
data = {
    'found_location': 'Sano y salvo en el centro médico La Trinidad'
}
headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
}

response = requests.post(url, json=data, headers=headers)
print(response.json())</code></pre>
                        <!-- PHP -->
                        <pre class="snippet-code" data-lang="php"><code>&lt;?php
$ch = curl_init();
$payload = json_encode([
    'found_location' => 'Sano y salvo en el centro médico La Trinidad'
]);

curl_setopt($ch, CURLOPT_URL, "{{ url('/api/caso/125/localizado') }}");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);
print_r(json_decode($response, true));</code></pre>
                    </div>
                </div>

                <div class="code-box response-box">
                    <div class="code-header">
                        <span>Respuesta Localización Guardada (200 OK)</span>
                    </div>
                    <pre><code class="language-json">{
  "success": true,
  "message": "Persona marcada como localizada con éxito.",
  "person": {
    "id": 125,
    "code": "BD-3X7W9Y",
    "full_name": "Juan Perez",
    "status": "found",
    "found_at": "2026-06-28T05:25:10.000000Z",
    "found_location": "Sano y salvo en el centro médico La Trinidad",
    "updated_at": "2026-06-28T05:25:10.000000Z"
  }
}</code></pre>
                </div>
            </div>
        </section>
    </main>
</div>

<!-- Estilos Dedicados para el Portal de Desarrollador -->
<style>
    /* Estilos base del Layout General adaptados */
    body {
        margin: 0;
        padding: 0;
        background-color: #0b0f19;
        color: #f3f4f6;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        line-height: 1.6;
    }

    /* Contenedor de Tres Columnas */
    .api-docs-container {
        display: flex;
        min-height: 100vh;
        background-color: #0b0f19;
    }

    /* Sidebar Lateral Izquierdo */
    .api-sidebar {
        width: 280px;
        background-color: #0d1222;
        border-right: 1px solid #1f293d;
        display: flex;
        flex-direction: column;
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        z-index: 100;
        overflow-y: auto;
    }
    .sidebar-header {
        padding: 24px;
        border-bottom: 1px solid #1f293d;
    }
    .sidebar-header .logo {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.15rem;
        font-weight: 800;
        color: #6366f1;
        font-family: 'Outfit', sans-serif;
    }
    .sidebar-header .logo i {
        font-size: 1.3rem;
        color: #6366f1;
        text-shadow: 0 0 10px rgba(99, 102, 241, 0.4);
    }
    .back-home-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 16px;
        font-size: 0.8rem;
        color: #9ca3af;
        text-decoration: none;
        padding: 6px 12px;
        border: 1px solid #1f293d;
        border-radius: 6px;
        background: #111827;
        transition: all 0.2s ease;
        width: 100%;
        box-sizing: border-box;
    }
    .back-home-btn:hover {
        color: #fff;
        border-color: #6366f1;
        background: #1f293d;
    }

    .sidebar-nav {
        flex: 1;
        padding: 24px 16px;
    }
    .nav-section-title {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #4b5563;
        font-weight: 700;
        margin-bottom: 12px;
        margin-top: 24px;
        padding-left: 8px;
    }
    .nav-section-title:first-of-type {
        margin-top: 0;
    }
    .sidebar-nav ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .sidebar-nav li {
        margin-bottom: 4px;
    }
    .nav-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        color: #9ca3af;
        text-decoration: none;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .nav-link:hover {
        color: #fff;
        background-color: #1f293d;
    }
    .nav-link.active {
        color: #fff;
        background-color: #6366f1;
        font-weight: 600;
    }

    /* Badges de Metodos HTTP */
    .badge {
        font-size: 0.7rem;
        font-weight: 800;
        padding: 2px 6px;
        border-radius: 4px;
        text-transform: uppercase;
        font-family: monospace;
    }
    .badge-get {
        background-color: rgba(16, 185, 129, 0.1);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }
    .badge-post {
        background-color: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
        border: 1px solid rgba(59, 130, 246, 0.2);
    }
    .nav-link.active .badge-get {
        background-color: #10b981;
        color: #0b0f19;
    }
    .nav-link.active .badge-post {
        background-color: #3b82f6;
        color: #0b0f19;
    }

    .sidebar-footer {
        padding: 20px;
        border-top: 1px solid #1f293d;
        font-size: 0.75rem;
        color: #4b5563;
        text-align: center;
    }

    /* Contenedor de Secciones */
    .api-content {
        margin-left: 280px;
        flex: 1;
        padding: 0;
        overflow-y: visible;
        background-color: #0b0f19;
    }

    /* Filas de la Documentación */
    .api-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        padding: 60px 48px;
        align-items: start;
        border-bottom: 1px solid #111827;
    }
    .api-section:last-of-type {
        border-bottom: none;
    }

    /* Lado Izquierdo (Textos y Tablas) */
    .section-left {
        max-width: 680px;
    }
    .section-left h1 {
        font-family: 'Outfit', sans-serif;
        font-size: 2.25rem;
        font-weight: 800;
        margin-top: 0;
        margin-bottom: 20px;
        background: linear-gradient(135deg, #fff 30%, #6366f1 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .section-left h2 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.75rem;
        font-weight: 700;
        margin-top: 0;
        margin-bottom: 16px;
        color: #fff;
    }
    .section-left h3 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.4rem;
        font-weight: 700;
        margin-top: 10px;
        margin-bottom: 16px;
        color: #fff;
    }
    .section-left h4 {
        font-size: 0.95rem;
        font-weight: 700;
        margin-top: 30px;
        margin-bottom: 12px;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .section-left p {
        color: #9ca3af;
        font-size: 0.95rem;
        margin-bottom: 20px;
    }
    .section-left p.lead {
        font-size: 1.1rem;
        color: #d1d5db;
    }

    .endpoint-badge-wrapper {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }
    .endpoint-badge-wrapper code {
        font-family: monospace;
        font-weight: 700;
        color: #fff;
        font-size: 1.1rem;
        background: #111827;
        padding: 4px 10px;
        border-radius: 6px;
        border: 1px solid #1f293d;
    }

    /* Caja de Información URL Base */
    .info-card {
        display: flex;
        align-items: center;
        gap: 15px;
        background: rgba(99, 102, 241, 0.05);
        border: 1px solid rgba(99, 102, 241, 0.2);
        border-radius: 8px;
        padding: 16px 20px;
        margin: 24px 0;
    }
    .info-card i {
        font-size: 1.4rem;
        color: #6366f1;
    }
    .info-card strong {
        color: #fff;
        margin-right: 8px;
        font-size: 0.9rem;
    }
    .info-card code {
        font-size: 0.95rem;
        color: #818cf8;
        font-weight: 700;
    }

    /* Tabla de Parámetros */
    .params-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        font-size: 0.875rem;
        text-align: left;
    }
    .params-table th {
        border-bottom: 1px solid #1f293d;
        padding: 12px 10px;
        color: #fff;
        font-weight: 700;
    }
    .params-table td {
        border-bottom: 1px solid #111827;
        padding: 12px 10px;
        color: #d1d5db;
        vertical-align: top;
    }
    .params-table tr:hover td {
        background-color: rgba(255,255,255,0.01);
    }
    .params-table code {
        background: #1f293d;
        color: #fff;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.8rem;
    }

    /* Lado Derecho (Snippets de Código) */
    .section-right {
        position: sticky;
        top: 30px;
    }

    /* Code Playground / Snippets */
    .code-playground {
        background-color: #090d16;
        border: 1px solid #1f293d;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }
    .playground-tabs {
        display: flex;
        background-color: #0d1222;
        border-bottom: 1px solid #1f293d;
        padding: 0 16px;
        align-items: center;
    }
    .tab-btn {
        background: none;
        border: none;
        color: #6b7280;
        padding: 14px 16px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        border-bottom: 2px solid transparent;
    }
    .tab-btn:hover {
        color: #fff;
    }
    .tab-btn.active {
        color: #6366f1;
        border-bottom-color: #6366f1;
    }
    .copy-btn {
        margin-left: auto;
        background: none;
        border: none;
        color: #4b5563;
        font-size: 0.95rem;
        cursor: pointer;
        padding: 10px;
        transition: color 0.2s;
    }
    .copy-btn:hover {
        color: #fff;
    }

    .playground-snippets {
        padding: 20px;
    }
    .snippet-code {
        display: none;
        margin: 0;
    }
    .snippet-code.active {
        display: block;
    }
    .snippet-code code {
        font-family: 'Fira Code', monospace, Consolas, sans-serif;
        font-size: 0.85rem;
        color: #38bdf8;
        white-space: pre-wrap;
    }

    /* Caja de Código Simple */
    .code-box {
        background-color: #090d16;
        border: 1px solid #1f293d;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }
    .code-header {
        background-color: #0d1222;
        border-bottom: 1px solid #1f293d;
        padding: 10px 16px;
        font-size: 0.8rem;
        color: #9ca3af;
        font-weight: 600;
    }
    .code-box pre {
        margin: 0;
        padding: 20px;
        overflow-x: auto;
    }
    .code-box code {
        font-family: 'Fira Code', monospace, Consolas, sans-serif;
        font-size: 0.85rem;
        color: #d1d5db;
    }

    /* Caja de Respuestas JSON */
    .response-box {
        margin-top: 24px;
        border-color: rgba(99,102,241,0.15);
    }
    .response-box .code-header {
        background-color: #090d1a;
        border-bottom-color: rgba(99,102,241,0.15);
    }
    .response-box code {
        color: #a5b4fc;
    }

    .section-divider {
        grid-column: span 2;
        border: none;
        border-top: 1px solid #111827;
        margin: 0;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .api-sidebar {
            width: 240px;
        }
        .api-content {
            margin-left: 240px;
        }
        .api-section {
            gap: 30px;
            padding: 40px 24px;
        }
    }

    @media (max-width: 800px) {
        .api-docs-container {
            flex-direction: column;
        }
        .api-sidebar {
            width: 100%;
            position: relative;
            border-right: none;
            border-bottom: 1px solid #1f293d;
        }
        .api-content {
            margin-left: 0;
        }
        .api-section {
            grid-template-columns: 1fr;
            gap: 40px;
            padding: 40px 20px;
        }
        .section-divider {
            grid-column: span 1;
        }
        .section-right {
            position: static;
        }
    }
</style>

<!-- Lógica de navegación e interactividad de tabs -->
<script>
    // Variable global para recordar el lenguaje seleccionado por el desarrollador
    let currentSelectedLang = 'curl';

    // Función para cambiar de tab de lenguaje
    function switchLang(sectionId, lang) {
        currentSelectedLang = lang;
        
        // Encontrar todos los contenedores de snippets y actualizar el estado
        const sections = document.querySelectorAll('.api-section');
        sections.forEach(sec => {
            const tabs = sec.querySelectorAll('.playground-tabs .tab-btn');
            const snippets = sec.querySelectorAll('.playground-snippets .snippet-code');
            
            // Si la sección tiene snippets de código
            if (tabs.length > 0 && snippets.length > 0) {
                // Activar el tab correspondiente
                tabs.forEach(t => {
                    const onClickAttr = t.getAttribute('onclick') || '';
                    if (onClickAttr.includes(`'${lang}'`)) {
                        t.classList.add('active');
                    } else {
                        t.classList.remove('active');
                    }
                });

                // Mostrar el código correspondiente
                snippets.forEach(s => {
                    if (s.getAttribute('data-lang') === lang) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
            }
        });
    }

    // Copiar código al portapapeles con feedback visual
    function copySnippet(sectionId) {
        const container = document.getElementById(`${sectionId}-snippets`);
        if (!container) return;
        
        const activeCode = container.querySelector('.snippet-code.active code');
        if (!activeCode) return;

        const textToCopy = activeCode.innerText;

        navigator.clipboard.writeText(textToCopy).then(() => {
            const btn = container.previousElementSibling.querySelector('.copy-btn');
            const origHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-check" style="color: #10b981;"></i>';
            setTimeout(() => {
                btn.innerHTML = origHTML;
            }, 2000);
        }).catch(err => {
            console.error('Error copying code: ', err);
        });
    }

    // Navegación Sidebar Active links scroll spy
    window.addEventListener('DOMContentLoaded', () => {
        const links = document.querySelectorAll('.nav-link');
        const sections = document.querySelectorAll('.api-section');

        function changeActiveLink() {
            let index = sections.length;

            while(--index && window.scrollY + 100 < sections[index].offsetTop) {}
            
            links.forEach((link) => link.classList.remove('active'));
            if (sections[index]) {
                const targetId = sections[index].getAttribute('id');
                const activeLink = document.querySelector(`.nav-link[href="#${targetId}"]`);
                if (activeLink) {
                    activeLink.classList.add('active');
                }
            }
        }

        changeActiveLink();
        window.addEventListener('scroll', changeActiveLink);
    });
</script>
@endsection
