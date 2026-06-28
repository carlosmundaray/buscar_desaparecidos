<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Buscar Desaparecidos Venezuela — Registro de Personas tras Terremoto y Sismo')</title>
    
    <!-- Meta tags SEO -->
    <meta name="description" content="Plataforma de búsqueda y reporte de personas desaparecidas en Venezuela. Herramienta solidaria ante contingencias por terremoto, sismo o desastres naturales. Localiza familiares por nombre o cédula.">
    <meta name="keywords" content="desaparecidos venezuela, terremoto venezuela, sismo venezuela, personas desaparecidas terremoto, buscar desaparecidos sismo, reportar desaparecidos venezuela, sismo, temblor venezuela, localizacion de personas">
    <meta name="author" content="Buscar Desaparecidos">
    <meta name="robots" content="index, follow">

    <!-- Open Graph (Facebook / WhatsApp / etc) -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Buscar Desaparecidos Venezuela — Búsqueda de Personas tras Terremoto y Sismo">
    <meta property="og:description" content="Plataforma de registro y búsqueda inmediata de personas desaparecidas en Venezuela ante eventos sísmicos o terremotos.">
    <meta property="og:image" content="{{ asset('images/og-share.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">

    <!-- Google Fonts: Plus Jakarta Sans & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome para Iconos Premium -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Estilos CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    @yield('content')

    <!-- Scripts JS -->
    <script src="{{ asset('js/search.js') }}?v={{ time() }}"></script>
</body>
</html>
