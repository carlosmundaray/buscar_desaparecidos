# Buscar Desaparecidos Venezuela — Plataforma de Búsqueda Inmediata

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-%5E8.2-blue?style=for-the-badge&logo=php" alt="PHP Version">
  <img src="https://img.shields.io/badge/Laravel-12.x-red?style=for-the-badge&logo=laravel" alt="Laravel Version">
  <img src="https://img.shields.io/badge/CSS-Vanilla-orange?style=for-the-badge&logo=css3" alt="Vanilla CSS">
  <img src="https://img.shields.io/badge/JS-Vanilla-yellow?style=for-the-badge&logo=javascript" alt="Vanilla JS">
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="MIT License">
</p>

**Buscar Desaparecidos** es una plataforma unificada y solidaria desarrollada para registrar, reportar y buscar personas desaparecidas en Venezuela tras sismos, temblores o contingencias sísmicas de gran magnitud. La plataforma integra bases de datos de hospitales locales, reportes ciudadanos e importaciones automatizadas.

---

## 🚀 Características Clave

### 🔍 Búsqueda Avanzada y Rápida
- Filtro inmediato por nombre completo, alias (apodo), cédula de identidad, género, ciudad o estado geográfico.
- Clasificación en tiempo real por el estado de la persona: **Desaparecido** (Missing), **Localizado** (Found), **Fallecido** (Deceased), y **Hospitalizado** (Hospitalized).

### 📸 Búsqueda Visual por Foto (Similitud Visual)
- Comparación biométrica aproximada mediante la carga de fotografías.
- Cálculo automático de **hashes perceptuales** (hashes visuales) de 64 bits en la carga del archivo.
- Búsqueda a través de distancia de Hamming para encontrar coincidencias ordenadas por porcentaje de similitud.

### 📝 Reporte Ciudadano Simplificado
- Formulario público accesible sin necesidad de autenticación.
- Carga de imágenes en formatos comunes (JPG, PNG, WEBP) con un límite estricto de 2MB.
- Registro automatizado del informante para facilitar el control e identificación posterior.

### 🛡️ Panel de Administración y Herramientas Administrativas
- Panel administrativo protegido por autenticación.
- Panel centralizado con estadísticas generales del incidente (Total reportados, localizados, fallecidos y hospitalizados).
- CRUD completo para la gestión y actualización del estado de los casos.
- Importador masivo de datos mediante archivos **Excel y CSV**.
- **Scraper integrado de Google Drive** para extraer de forma periódica listados oficiales de hospitales.

### 🌐 API Abierta para Desarrolladores (`/api-docs`)
- Endpoints del API totalmente abiertos para conectar aplicaciones móviles, bots de Telegram/WhatsApp u otros frontends de ayuda humanitaria.
- **Soporte CORS (Cross-Origin Resource Sharing)** global habilitado.
- Exclusión automática de validación CSRF para peticiones del API (`POST /api/reportar` y `POST /api/caso/{id}/localizado`).
- **Portal Developer Interactivo** alojado localmente en `/api-docs` con código listo para producción en `cURL`, `JavaScript`, `Python` y `PHP`.

---

## 🛠️ Stack Tecnológico

- **Backend**: PHP ^8.2 y Laravel ^12.0 (framework robusto de alto rendimiento).
- **Frontend**: HTML5, Vanilla JavaScript (sin librerías pesadas) e Iconos Premium con FontAwesome 6.
- **Diseño**: Vanilla CSS3 adaptado al sistema de diseño **"Warm Stone System"** (WCAG AAA/AA de alta legibilidad, mobile-first y responsivo).
- **Procesamiento de Archivos**: PhpSpreadsheet (para lectura de plantillas Excel) y PDFParser.

---

## 💻 Instalación y Configuración Local

Sigue los pasos a continuación para ejecutar la aplicación en tu entorno local (utilizando XAMPP u otro servidor de desarrollo PHP):

### Requisitos Previos
- PHP >= 8.2
- Composer
- Base de datos (MySQL o SQLite)
- Node.js y npm (opcional, para compilación de estilos si utilizas bundlers)

### Pasos

1. **Clonar el repositorio**:
   ```bash
   git clone https://github.com/tu-usuario/Buscar_Desaparecidos.git
   cd Buscar_Desaparecidos
   ```

2. **Instalar dependencias de PHP**:
   ```bash
   composer install
   ```

3. **Configurar el entorno**:
   Copia el archivo de ejemplo a `.env` y genera la clave de cifrado:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configurar la base de datos**:
   Edita las variables `DB_*` en tu archivo `.env`. Por ejemplo, para usar SQLite:
   ```env
   DB_CONNECTION=sqlite
   ```
   *(Si usas SQLite, crea el archivo vacío en `database/database.sqlite` antes de migrar)*.

5. **Ejecutar migraciones y seeders**:
   ```bash
   php artisan migrate
   ```

6. **Ejecutar el Servidor de Desarrollo**:
   ```bash
   php artisan serve
   ```
   La aplicación estará disponible en `http://localhost:8000`.

---

## 🔄 Tareas Programadas y Scrapers

La aplicación cuenta con comandos de consola integrados para automatizar la extracción de datos:

- **Ejecutar Scraper de Casos**:
  Para importar de forma periódica listados e información actualizada del portal centralizado:
  ```bash
  php artisan app:scrape-desaparecidos --pages=5
  ```
- **Scraper de Google Drive**:
  Automatiza la lectura de documentos de Google Sheets vinculados en el panel de administración.

---

## 🔌 Integración con la API (CORS & Endpoints)

La API cuenta con límites de tasa por IP para prevenir ataques de denegación de servicio (DoS) y está documentada de forma detallada en la ruta `/api-docs`.

### Resumen de Endpoints Disponibles:

| Método | Endpoint | CSRF | CORS | Descripción |
|---|---|---|---|---|
| **GET** | `/api/buscar` | No | Sí | Buscar personas desaparecidas por texto o filtros. |
| **GET** | `/api/sincronizar-busqueda` | No | Sí | Sincronización delta por fecha (`last_sync`). |
| **POST** | `/api/buscar-foto` | No | Sí | Carga de foto (`multipart/form-data`) para buscar por similitud. |
| **POST** | `/api/reportar` | No | Sí | Registrar un nuevo caso (no requiere sesión ni token CSRF). |
| **POST** | `/api/caso/{id}/localizado` | No | Sí | Marcar un caso como localizado con detalles. |

### Documentación Interactiva
Para ver ejemplos de código detallados en múltiples lenguajes de programación y esquemas de respuesta JSON en vivo, levanta el servidor local y navega a:
`http://localhost:8000/api-docs`

---

## 📄 Licencia

Este proyecto está bajo la Licencia **MIT**. Consulta el archivo [LICENSE](LICENSE) para más detalles.
