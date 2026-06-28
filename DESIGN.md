# BRAND CONTRACT: Buscar Desaparecidos (Light High-Contrast "Warm Stone" System)

Este documento define el sistema de diseño visual de la plataforma, alineado a los principios de **Open Design** para lograr una interfaz clara, accesible y de alta fidelidad, con un soporte responsivo excepcional y capacidad de búsqueda por foto.

---

## 1. SISTEMA DE COLOR (Light High-Contrast - WCAG AAA/AA)
*   **Fondo Principal (`--bg-main`)**: `#fafaf9` (Warm Stone 50 - Fondo cálido suave que disminuye la fatiga visual).
*   **Fondo de Tarjetas (`--bg-card`)**: `#ffffff` (Blanco puro para crear niveles de profundidad limpios).
*   **Fondo de Inputs (`--bg-input`)**: `#f5f5f4` (Stone 100 - Gris cálido claro para delimitar campos).
*   **Bordes de Alta Definición (`--border-color`)**: `#e7e5e4` (Stone 200 - Bordes bien definidos para separación visual).
*   **Texto Principal (`--text-primary`)**: `#1c1917` (Stone 900 - Máximo contraste > 7:1 para legibilidad del cuerpo y títulos).
*   **Texto Secundario (`--text-secondary`)**: `#44403c` (Stone 700 - Contraste > 4.5:1 para metadatos y subtítulos).
*   **Texto Atenuado (`--text-muted`)**: `#78716c` (Stone 500 - Leyendas y datos secundarios).
*   **Acento Primario (`--accent-primary`)**: `#2563eb` (Azul Royal accesible - Contraste de 4.5:1 sobre fondo claro, interactivo).
*   **Acento Secundario (`--accent-secondary`)**: `#7c3aed` (Violeta vibrante para acciones secundarias o estados interactivos).
*   **Estado Desaparecido (`--state-missing`)**: `#dc2626` (Rojo vivo de advertencia) / Fondo suave: `rgba(220, 38, 38, 0.08)`.
*   **Estado Localizado (`--state-found`)**: `#16a34a` (Verde esmeralda de confirmación) / Fondo suave: `rgba(22, 163, 74, 0.08)`.

---

## 2. TIPOGRAFÍA
*   **Encabezados (`--font-heading`)**: `'Outfit', sans-serif` (Pesos: 700, 800; tracking estrecho `-0.5px` para un look corporativo moderno).
*   **Cuerpo y Formularios (`--font-sans`)**: `'Plus Jakarta Sans', sans-serif` (Pesos: 400, 500, 600; alta legibilidad).

---

## 3. PATRONES DE COMPONENTES
*   **Cabecera Glassmorphic**: Cabecera fija o flotante con fondo `--bg-card` semi-transparente (`rgba(255,255,255,0.85)`) y desenfoque de fondo (`backdrop-filter: blur(12px)`).
*   **Efecto Elevación en Tarjetas (Cards)**:
    - Estado normal: Sombra ligera y borde Stone 200.
    - Estado hover: Desplazamiento de -4px, sombra proyectada de capas (`box-shadow` suave) y borde Azul Royal para indicar interactividad.
*   **Zona de Carga de Foto para Búsqueda (Drag & Drop)**:
    - Área interactiva con borde discontinuo (`dashed`), fondo de Stone 100, animaciones de arrastre en hover, y carga fluida con barra de carga.
*   **Insignia de Similitud Visual**:
    - Gradiente dinámico de fondo (Azul a Violeta) con texto en blanco de alto contraste para indicar el porcentaje de coincidencia en la búsqueda por foto.

---

## 4. REGLAS RESPONSIVAS (Mobile-First)
*   **Layout base**: Paddings de `16px` en móvil, escalando a `32px` en escritorio.
*   **Grillas**:
    - Menos de 580px: 1 columna.
    - De 580px a 900px: 2 columnas.
    - Más de 900px: 3 o 4 columnas según el ancho máximo de `1200px`.
*   **Formularios y Modales**:
    - El formulario de reporte se apila a una sola columna en pantallas menores de 600px.
    - Altura máxima de los modales limitada a `95vh` con scroll nativo para el contenido.
