{{-- Vista de error 403 personalizada para restricciones de sysadmin --}}
@extends('adminlte::page')

@section('title', 'Acceso Denegado')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-shield-alt"></i>
                        Acceso Denegado
                    </h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-user-shield fa-5x text-danger"></i>
                    </div>

                    <h3 class="text-danger">Permisos Insuficientes</h3>

                    <p class="lead text-muted mb-4">
                        Solo los usuarios con rol <strong>sysadmin</strong> pueden acceder a esta funcionalidad.
                    </p>

                    <div class="alert alert-warning">
                        <h5 class="alert-heading">
                            <i class="fas fa-exclamation-triangle"></i>
                            Funciones Restringidas
                        </h5>
                        <hr>
                        <ul class="list-unstyled mb-0">
                            <li><i class="fas fa-building text-primary"></i> Gestión de Empresas</li>
                            <li><i class="fas fa-users-cog text-success"></i> Gestión de Roles</li>
                            <li><i class="fas fa-key text-warning"></i> Gestión de Permisos</li>
                        </ul>
                    </div>

                    @if(auth()->check())
                        <p class="text-muted">
                            <strong>Usuario actual:</strong> {{ auth()->user()->name }}<br>
                            <strong>Rol:</strong>
                            @if(auth()->user()->roles->count() > 0)
                                {{ auth()->user()->roles->pluck('name')->join(', ') }}
                            @else
                                <span class="text-danger">Sin rol asignado</span>
                            @endif
                        </p>
                    @endif

                    <div class="mt-4">
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Regresar
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-primary ml-2">
                            <i class="fas fa-home"></i>
                            Ir al Dashboard
                        </a>
                    </div>
                </div>

                <div class="card-footer text-muted text-center">
                    Si necesita acceso a estas funciones, contacte al administrador del sistema.
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.fa-5x {
    font-size: 5rem !important;
}

.alert-warning {
    border-left: 5px solid #ffc107;
}

.list-unstyled li {
    padding: 0.25rem 0;
}
</style>
@stop
