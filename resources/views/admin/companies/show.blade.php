@extends('adminlte::page')

@section('title', 'Detalles de Empresa')

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)
@section('plugins.JqueryValidation', true)

@section('content')
    {{-- Breadcrumbs --}}
    @php
        $breadcrumbs = [
            [
                'title' => 'Administración',
                'icon' => 'fas fa-shield-alt',
                'url' => null
            ],
            [
                'title' => 'Empresas',
                'icon' => 'fas fa-building',
                'url' => route('admin.companies.index')
            ]
        ];
        $currentTitle = $company->name;
        $currentIcon = 'fas fa-eye';
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

    {{-- Header con acciones --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-{{ $company->is_active ? 'success' : 'danger' }}">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="card-title mb-0">
                                @if($company->logo)
                                    <img src="{{ $company->getLogoUrl() }}" alt="Logo"
                                         style="height: 40px; width: auto; margin-right: 15px;">
                                @else
                                    <i class="fas fa-building mr-2"></i>
                                @endif
                                {{ $company->name }}
                            </h3>
                        </div>
                        <div class="col-md-4 text-right">
                            @can('admin.companies.edit')
                                <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit mr-1"></i>Editar
                                </a>
                            @endcan

                            <button type="button" class="btn btn-info btn-sm" onclick="renewLicense({{ $company->id }}, '{{ $company->name }}')">
                                <i class="fas fa-sync-alt mr-1"></i>Renovar Licencia
                            </button>

                            <button type="button" class="btn btn-{{ $company->is_active ? 'warning' : 'success' }} btn-sm"
                                    onclick="toggleStatus({{ $company->id }}, '{{ $company->name }}', {{ $company->is_active ? 'true' : 'false' }})">
                                <i class="fas fa-{{ $company->is_active ? 'pause' : 'play' }} mr-1"></i>
                                {{ $company->is_active ? 'Desactivar' : 'Activar' }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        {{-- Estado y Alertas --}}
                        <div class="col-md-12">
                            @if(!$company->is_active)
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <strong>Empresa Desactivada:</strong> Los usuarios de esta empresa no pueden acceder al sistema.
                                </div>
                            @endif

                            @if(!$company->isLicenseValid())
                                <div class="alert alert-danger">
                                    <i class="fas fa-times-circle mr-2"></i>
                                    <strong>Licencia Expirada:</strong> La licencia expiró el {{ $company->license_expires_at->format('d/m/Y') }}.
                                    <button class="btn btn-sm btn-outline-danger ml-2" onclick="renewLicense({{ $company->id }}, '{{ $company->name }}')">
                                        <i class="fas fa-sync-alt mr-1"></i>Renovar
                                    </button>
                                </div>
                            @elseif($company->isLicenseExpiringSoon())
                                <div class="alert alert-warning">
                                    <i class="fas fa-clock mr-2"></i>
                                    <strong>Licencia por Expirar:</strong> Expira en {{ $company->daysUntilExpiration() }} días ({{ $company->license_expires_at->format('d/m/Y') }}).
                                    <button class="btn btn-sm btn-outline-warning ml-2" onclick="renewLicense({{ $company->id }}, '{{ $company->name }}')">
                                        <i class="fas fa-sync-alt mr-1"></i>Renovar
                                    </button>
                                </div>
                            @else
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <strong>Licencia Activa:</strong> La empresa tiene acceso completo al sistema.
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Información Principal --}}
                    <div class="row">
                        {{-- Datos de la Empresa --}}
                        <div class="col-md-6">
                            <div class="card card-outline card-primary h-100">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-info-circle mr-2"></i>Información de la Empresa
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-striped table-sm">
                                        <tr>
                                            <td><strong>Nombre:</strong></td>
                                            <td>{{ $company->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>NIT:</strong></td>
                                            <td>{{ $company->nit }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>
                                                <a href="mailto:{{ $company->email }}">{{ $company->email }}</a>
                                            </td>
                                        </tr>
                                        @if($company->phone)
                                        <tr>
                                            <td><strong>Teléfono:</strong></td>
                                            <td>
                                                <a href="tel:{{ $company->phone }}">{{ $company->phone }}</a>
                                            </td>
                                        </tr>
                                        @endif
                                        @if($company->address)
                                        <tr>
                                            <td><strong>Dirección:</strong></td>
                                            <td>{{ $company->address }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td><strong>Estado:</strong></td>
                                            <td>
                                                <span class="badge badge-{{ $company->is_active ? 'success' : 'danger' }}">
                                                    {{ $company->is_active ? 'Activa' : 'Inactiva' }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Creada:</strong></td>
                                            <td>{{ $company->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        @if($company->updated_at != $company->created_at)
                                        <tr>
                                            <td><strong>Última actualización:</strong></td>
                                            <td>{{ $company->updated_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Información de Licencia --}}
                        <div class="col-md-6">
                            <div class="card card-outline card-success h-100">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-certificate mr-2"></i>Información de Licencia
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-striped table-sm">
                                        <tr>
                                            <td><strong>Tipo:</strong></td>
                                            <td>
                                                <span class="badge badge-info">{{ ucfirst($company->license_type) }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Estado:</strong></td>
                                            <td>
                                                @if($company->isLicenseValid())
                                                    <span class="badge badge-success">Válida</span>
                                                @else
                                                    <span class="badge badge-danger">Expirada</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Fecha de Expiración:</strong></td>
                                            <td>
                                                {{ $company->license_expires_at ? $company->license_expires_at->format('d/m/Y') : 'Sin expiración' }}
                                                @if($company->license_expires_at && $company->daysUntilExpiration() !== null)
                                                    <br>
                                                    @if($company->daysUntilExpiration() > 0)
                                                        <small class="text-muted">({{ $company->daysUntilExpiration() }} días restantes)</small>
                                                    @else
                                                        <small class="text-danger">(Expiró hace {{ abs($company->daysUntilExpiration()) }} días)</small>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Usuarios Máximos:</strong></td>
                                            <td>{{ $company->max_users }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Usuarios Actuales:</strong></td>
                                            <td>
                                                {{ $company->users()->count() }}
                                                <div class="progress mt-1" style="height: 10px;">
                                                    @php
                                                        $percentage = ($company->users()->count() / $company->max_users) * 100;
                                                        $barClass = $percentage > 80 ? 'bg-danger' : ($percentage > 60 ? 'bg-warning' : 'bg-success');
                                                    @endphp
                                                    <div class="progress-bar {{ $barClass }}" style="width: {{ $percentage }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ number_format($percentage, 1) }}% utilizado</small>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Personalización Visual --}}
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card card-outline card-warning">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-palette mr-2"></i>Personalización Visual
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <h5>Logo</h5>
                                            @if($company->logo)
                                                <img src="{{ $company->getLogoUrl() }}" alt="Logo"
                                                     class="img-thumbnail" style="max-width: 150px;">
                                                <br><br>
                                                <small class="text-muted">{{ basename($company->logo) }}</small>
                                            @else
                                                <div class="text-center p-4 border rounded">
                                                    <i class="fas fa-image fa-3x text-muted"></i>
                                                    <p class="text-muted mt-2">Sin logo</p>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-3">
                                            <h5>Color Primario</h5>
                                            <div class="color-preview mb-2"
                                                 style="background-color: {{ $company->primary_color }}; height: 60px; border-radius: 5px; border: 1px solid #ddd;"></div>
                                            <code>{{ $company->primary_color }}</code>
                                        </div>
                                        <div class="col-md-3">
                                            <h5>Color Secundario</h5>
                                            <div class="color-preview mb-2"
                                                 style="background-color: {{ $company->secondary_color }}; height: 60px; border-radius: 5px; border: 1px solid #ddd;"></div>
                                            <code>{{ $company->secondary_color }}</code>
                                        </div>
                                        <div class="col-md-3">
                                            <h5>Vista Previa</h5>
                                            <div class="card" style="border-top: 3px solid {{ $company->primary_color }};">
                                                <div class="card-header" style="background-color: {{ $company->primary_color }}20;">
                                                    <span style="color: {{ $company->primary_color }};">
                                                        <i class="fas fa-chart-bar mr-2"></i>Panel de Control
                                                    </span>
                                                </div>
                                                <div class="card-body py-2">
                                                    <small class="text-muted">Vista previa del tema</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Configuraciones y Estadísticas --}}
                    <div class="row mt-4">
                        {{-- Configuraciones --}}
                        <div class="col-md-6">
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-cogs mr-2"></i>Configuraciones
                                    </h3>
                                </div>
                                <div class="card-body">
                                    @if($company->settings && count($company->settings) > 0)
                                        <table class="table table-striped table-sm">
                                            @foreach($company->settings as $key => $value)
                                                @if($value)
                                                    <tr>
                                                        <td><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong></td>
                                                        <td>{{ $value }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </table>
                                    @else
                                        <div class="text-center text-muted py-3">
                                            <i class="fas fa-cog fa-3x mb-3"></i>
                                            <p>No hay configuraciones personalizadas</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Estadísticas --}}
                        <div class="col-md-6">
                            <div class="card card-outline card-secondary">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-chart-pie mr-2"></i>Estadísticas de Uso
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <div class="info-box bg-success">
                                                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Usuarios</span>
                                                    <span class="info-box-number">{{ $company->users()->count() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box bg-info">
                                                <span class="info-box-icon"><i class="fas fa-file-invoice"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Cotizaciones</span>
                                                    <span class="info-box-number">{{ $company->cotizaciones()->count() ?? 0 }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box bg-warning">
                                                <span class="info-box-icon"><i class="fas fa-box"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Productos</span>
                                                    <span class="info-box-number">{{ $company->productos()->count() ?? 0 }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Usuarios de la Empresa --}}
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-users mr-2"></i>Usuarios de la Empresa ({{ $company->users()->count() }}/{{ $company->max_users }})
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if($company->users()->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover" id="users-table">
                                                <thead>
                                                    <tr>
                                                        <th>Nombre</th>
                                                        <th>Email</th>
                                                        <th>Rol</th>
                                                        <th>Estado</th>
                                                        <th>Último Acceso</th>
                                                        <th>Registrado</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($company->users()->with('roles')->orderBy('name')->get() as $user)
                                                        <tr>
                                                            <td>
                                                                @if($user->avatar)
                                                                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}"
                                                                         class="img-circle mr-2" style="width: 30px; height: 30px;">
                                                                @else
                                                                    <div class="bg-primary rounded-circle d-inline-flex justify-content-center align-items-center mr-2"
                                                                         style="width: 30px; height: 30px; font-size: 12px; color: white;">
                                                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                                                    </div>
                                                                @endif
                                                                {{ $user->name }}
                                                            </td>
                                                            <td>{{ $user->email }}</td>
                                                            <td>
                                                                @foreach($user->roles as $role)
                                                                    <span class="badge badge-secondary">{{ $role->name }}</span>
                                                                @endforeach
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-{{ $user->is_active ?? true ? 'success' : 'danger' }}">
                                                                    {{ $user->is_active ?? true ? 'Activo' : 'Inactivo' }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Nunca' }}
                                                            </td>
                                                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-users-slash fa-3x mb-3"></i>
                                            <h5>No hay usuarios registrados</h5>
                                            <p>Esta empresa aún no tiene usuarios asignados.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer con acciones --}}
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i>Volver a la Lista
                            </a>
                        </div>
                        <div class="col-md-6 text-right">
                            @can('admin.companies.edit')
                                <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-warning">
                                    <i class="fas fa-edit mr-2"></i>Editar Empresa
                                </a>
                            @endcan

                            @can('admin.companies.destroy')
                                <button type="button" class="btn btn-danger ml-2"
                                        onclick="deleteCompany({{ $company->id }}, '{{ $company->name }}')">
                                    <i class="fas fa-trash mr-2"></i>Eliminar
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/js/company/companies.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Inicializar DataTable para usuarios
            if ($('#users-table').length && $('#users-table tbody tr').length > 0) {
                $('#users-table').DataTable({
                    responsive: true,
                    pageLength: 10,
                    order: [[0, 'asc']],
                    language: {
                        url: '/assets/js/spanish.json'
                    }
                });
            }

            // Auto-refresh de estadísticas cada 5 minutos
            setInterval(function() {
                // Opcional: actualizar estadísticas sin recargar página
                // updateCompanyStats({{ $company->id }});
            }, 300000); // 5 minutos
        });

        // Función para actualizar estadísticas (opcional)
        function updateCompanyStats(companyId) {
            // Implementar si se requiere actualización en tiempo real
            $.get(`/admin/companies/${companyId}/stats`)
                .done(function(data) {
                    // Actualizar elementos de estadísticas
                })
                .fail(function() {
                    console.log('Error al actualizar estadísticas');
                });
        }
    </script>
@endsection
