@extends('adminlte::page')

@section('title', 'Gestión de Empresas')

@section('plugins.Datatables', true)

@section('plugins.Sweetalert2', true)

@section('plugins.JqueryValidation', true)

@section('content')
    {{-- Breadcrumbs mejorados usando componente --}}
    @php
        $breadcrumbs = [
            [
                'title' => 'Administración',
                'icon' => 'fas fa-shield-alt',
                'url' => null
            ]
        ];
        $currentTitle = 'Gestión de Empresas';
        $currentIcon = 'fas fa-building';
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

    {{-- Alertas de licencia --}}
    @if(isset($companyConfig) && $companyConfig['is_expiring_soon'])
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>¡Atención!</strong> La licencia de su empresa expira en {{ $companyConfig['days_until_expiration'] }} días.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Alerta informativa --}}
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle mr-2"></i>
        <strong>💡 Información:</strong> Si esta es la primera vez que accedes a este módulo, asegúrate de haber ejecutado el script SQL
        <code>sistema_multiempresa.sql</code> para crear la estructura de empresas en tu base de datos.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4><i class="fas fa-building mr-2"></i>Empresas del Sistema</h4>
        </div>
        <div class="card-body">
            @can('companies.create')
                <div class="my-3 d-flex justify-content-start">
                    <button type="button" class="btn btn-primary" onclick="createCompany()">
                        <i class="fas fa-plus mr-2"></i>Nueva Empresa
                    </button>
                </div>
            @endcan

            <div class="table-responsive">
                <table id="companies-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>NIT</th>
                            <th>Estado</th>
                            <th>Información de Licencia</th>
                            <th>Usuarios</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal para renovar licencia --}}
    <div class="modal fade" id="renewLicenseModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Renovar Licencia</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="renewLicenseForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="months">Meses a agregar:</label>
                            <input type="number" class="form-control" id="months" name="months" min="1" max="60" value="12" required>
                        </div>
                        <div class="form-group">
                            <label for="license_type">Tipo de Licencia:</label>
                            <select class="form-control" id="license_type" name="license_type" required>
                                <option value="trial">Trial</option>
                                <option value="standard">Standard</option>
                                <option value="premium" selected>Premium</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Renovar Licencia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .alert {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .card-header h4 {
        margin: 0;
        color: white
    }

    .table th {
        background-color: var(--company-primary, #007bff);
        color: white;
        border: none;
    }

    .badge {
        font-size: 0.8em;
        padding: 5px 8px;
    }

    .btn-group .btn {
        margin: 0 2px;
    }

    .progress {
        background-color: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
    }

    .progress-bar {
        font-size: 0.75em;
        font-weight: bold;
        text-align: center;
        line-height: 20px;
    }
</style>
@stop

@section('js')
    <script src="{{ asset('assets/js/company/companies.js') }}" type="text/javascript"></script>
    <script>
        $(document).ready(function() {
            // El DataTable ya está inicializado en companies.js
            console.log('Companies management loaded successfully');
        });
    </script>
@endsection
