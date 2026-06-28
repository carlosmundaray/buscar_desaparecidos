@extends('admin.layout')

@section('title', 'Mi Perfil - Admin')

@section('styles')
<style>
    .profile-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        padding: 2.5rem;
        max-width: 600px;
        margin: 0 auto 2.5rem auto;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .form-group label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .form-group input {
        padding: 0.75rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-sm);
        background-color: var(--bg-input);
        color: var(--text-primary);
        font-family: var(--font-sans);
        font-size: 0.95rem;
        outline: none;
        transition: var(--transition-smooth);
    }

    .form-group input:focus {
        border-color: var(--accent-primary);
        background-color: var(--bg-card);
        box-shadow: 0 0 0 3px var(--accent-primary-glow);
    }

    .profile-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 1.5rem;
    }

    .profile-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background-color: var(--accent-primary-glow);
        color: var(--accent-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 800;
        border: 1.5px solid var(--border-color);
    }

    .profile-meta h3 {
        font-family: var(--font-heading);
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .profile-meta span {
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    .btn-save {
        width: 100%;
        padding: 0.85rem;
        font-size: 0.95rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 1rem;
    }
</style>
@endsection

@section('admin_content')
<div class="admin-header">
    <div class="admin-title">
        <h2>Mi Perfil</h2>
        <p>Gestiona tu información de cuenta y actualiza tu contraseña de acceso</p>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-error" style="max-width: 600px; margin: 0 auto 1.5rem auto;">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <div>
            <strong style="display: block; margin-bottom: 0.25rem;">No se pudo actualizar la contraseña:</strong>
            <ul style="list-style: square; padding-left: 1.25rem; font-size: 0.85rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="profile-card">
    <div class="profile-header">
        <div class="profile-avatar">
            {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>
        <div class="profile-meta">
            <h3>{{ $user->name }}</h3>
            <span>{{ $user->email }} &bull; Rol Administrador</span>
        </div>
    </div>

    <form action="{{ route('admin.perfil.password') }}" method="POST">
        @csrf

        <div style="font-weight: 700; font-size: 1rem; margin-bottom: 1.25rem; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-key" style="color: var(--accent-primary);"></i> Cambiar Contraseña
        </div>

        <div class="form-group">
            <label for="current_password">Contraseña Actual *</label>
            <input type="password" id="current_password" name="current_password" required placeholder="Introduce tu contraseña actual">
        </div>

        <div class="form-group">
            <label for="password">Nueva Contraseña *</label>
            <input type="password" id="password" name="password" required placeholder="Mínimo 8 caracteres">
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirmar Nueva Contraseña *</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Repite la nueva contraseña">
        </div>

        <button type="submit" class="btn btn-primary btn-save">
            <i class="fa-solid fa-shield-halved"></i> Actualizar Contraseña
        </button>
    </form>
</div>
@endsection
