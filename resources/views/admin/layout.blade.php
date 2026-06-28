<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin - Buscar Desaparecidos')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Estilos Generales Públicos para consistencia -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- Estilos Exclusivos para la Consola de Administración -->
    <style>
        :root {
            --sidebar-width: 260px;
        }

        body {
            background-color: var(--bg-main);
            color: var(--text-primary);
            font-family: var(--font-sans);
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }

        .admin-layout-wrapper {
            display: flex;
            flex: 1;
        }

        /* Sidebar */
        .admin-sidebar {
            width: var(--sidebar-width);
            background-color: var(--bg-card);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 1.5rem 1rem;
        }

        .admin-logo {
            font-family: var(--font-heading);
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 2.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .admin-logo span {
            color: var(--accent-primary);
        }

        .sidebar-menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            flex: 1;
        }

        .sidebar-item a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.85rem 1rem;
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            border-radius: var(--border-radius-sm);
            transition: var(--transition-smooth);
        }

        .sidebar-item a:hover,
        .sidebar-item.active a {
            background-color: var(--accent-primary-glow);
            color: var(--accent-primary);
        }

        .sidebar-item.active a {
            font-weight: 600;
        }

        .sidebar-footer {
            margin-top: auto;
            border-top: 1px solid var(--border-color);
            padding-top: 1rem;
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding: 0 0.5rem;
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background-color: var(--border-color);
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .user-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
            text-overflow: ellipsis;
            break-word: break-all;
        }

        .user-role {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .logout-btn {
            width: 100%;
            background: none;
            border: 1px solid var(--border-color);
            padding: 0.6rem;
            border-radius: var(--border-radius-sm);
            color: var(--state-missing);
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: var(--transition-smooth);
        }

        .logout-btn:hover {
            background-color: var(--state-missing-glow);
        }

        /* Content Area */
        .admin-main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 2rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1rem;
        }

        .admin-title h2 {
            font-family: var(--font-heading);
            font-size: 1.75rem;
            color: var(--text-primary);
            font-weight: 700;
        }

        .admin-title p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        /* Flash Notifications */
        .alert {
            padding: 1rem;
            border-radius: var(--border-radius-sm);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
        }

        .alert-success {
            background-color: var(--state-found-glow);
            color: var(--state-found);
            border: 1px solid rgba(22, 163, 74, 0.2);
        }

        .alert-error {
            background-color: var(--state-missing-glow);
            color: var(--state-missing);
            border: 1px solid rgba(220, 38, 38, 0.2);
        }

        /* Responsive Layout */
        @media (max-width: 768px) {
            .admin-sidebar {
                width: 100%;
                height: auto;
                position: relative;
                top: auto;
                bottom: auto;
                left: auto;
                border-right: none;
                border-bottom: 1px solid var(--border-color);
                padding: 1.25rem 1rem;
            }
            .admin-logo {
                margin-bottom: 1.5rem;
            }
            .admin-main-content {
                margin-left: 0;
                padding: 1.25rem 1rem;
            }
            .admin-layout-wrapper {
                flex-direction: column;
            }
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            .admin-actions {
                width: 100%;
            }
            .admin-actions .btn {
                width: 100%;
                text-align: center;
                justify-content: center;
                display: flex;
            }
            .sidebar-menu {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 0.5rem;
                margin-bottom: 1.25rem;
            }
            .sidebar-item {
                flex: 1 1 calc(50% - 0.5rem);
            }
            .sidebar-footer {
                margin-top: 1.25rem;
                padding-top: 1rem;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                width: 100%;
                gap: 1rem;
            }
            .sidebar-user {
                margin-bottom: 0;
                padding: 0;
            }
            .logout-btn {
                width: auto;
                padding: 0.5rem 1rem;
            }
        }
        @media (max-width: 480px) {
            .sidebar-item {
                flex: 1 1 100%;
            }
            .sidebar-footer {
                flex-direction: column;
                align-items: stretch;
            }
            .logout-btn {
                width: 100%;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="admin-layout-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <a href="{{ route('home') }}" class="admin-logo">
                <i class="fa-solid fa-shield-halved"></i>
                Buscar<span>Admin</span>
            </a>

            <ul class="sidebar-menu">
                <li class="sidebar-item {{ Route::is('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="fa-solid fa-chart-pie"></i> Panel General
                    </a>
                </li>
                <li class="sidebar-item {{ Route::is('admin.casos.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.casos.index') }}">
                        <i class="fa-solid fa-users-viewfinder"></i> Casos Reportados
                    </a>
                </li>
                <li class="sidebar-item {{ Route::is('admin.importar.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.importar.index') }}">
                        <i class="fa-solid fa-file-import"></i> Importar Excel / CSV
                    </a>
                </li>
                <li class="sidebar-item {{ Route::is('admin.perfil') ? 'active' : '' }}">
                    <a href="{{ route('admin.perfil') }}">
                        <i class="fa-solid fa-user-gear"></i> Mi Perfil
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('home') }}" target="_blank">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Ver Sitio Público
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                @auth
                    <div class="sidebar-user">
                        <div class="user-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div class="user-info">
                            <span class="user-name">{{ auth()->user()->name }}</span>
                            <span class="user-role">Administrador</span>
                        </div>
                    </div>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="logout-btn">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar Sesión
                        </button>
                    </form>
                @endauth
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="admin-main-content">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-alert">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('admin_content')
        </main>
    </div>

    @yield('scripts')
</body>
</html>
