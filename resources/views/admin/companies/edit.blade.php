@extends('adminlte::page')

@section('title', 'Editar Empresa')

@section('plugins.BootstrapColorpicker', true)
@section('plugins.Select2', true)
@section('plugins.JqueryValidation', true)
@section('plugins.Sweetalert2', true)

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
        $currentTitle = 'Editar: ' . $company->name;
        $currentIcon = 'fas fa-edit';
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

    <div class="row">
        <div class="col-md-12">
            {{-- Alertas de estado de la empresa --}}
            @if(!$company->is_active)
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>¡Atención!</strong> Esta empresa está desactivada. Los usuarios no podrán acceder al sistema.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(!$company->isLicenseValid())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-times-circle mr-2"></i>
                    <strong>¡Licencia Expirada!</strong> La licencia de esta empresa expiró el {{ $company->license_expires_at->format('d/m/Y') }}.
                    <a href="javascript:renewLicense({{ $company->id }}, '{{ $company->name }}')" class="alert-link">Renovar ahora</a>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @elseif($company->isLicenseExpiringSoon())
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-clock mr-2"></i>
                    <strong>Licencia por Expirar!</strong> La licencia expira en {{ $company->daysUntilExpiration() }} días ({{ $company->license_expires_at->format('d/m/Y') }}).
                    <a href="javascript:renewLicense({{ $company->id }}, '{{ $company->name }}')" class="alert-link">Renovar ahora</a>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit mr-2"></i>Editar Empresa: {{ $company->name }}
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-{{ $company->is_active ? 'success' : 'danger' }}">
                            {{ $company->is_active ? 'Activa' : 'Inactiva' }}
                        </span>
                        <span class="badge badge-info ml-1">{{ ucfirst($company->license_type) }}</span>
                    </div>
                </div>

                <form id="company-form" data-method="PUT" action="{{ route('admin.companies.update', $company) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <div class="row">
                            {{-- Información General --}}
                            <div class="col-md-6">
                                <h5 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-info-circle mr-2"></i>Información General
                                </h5>

                                <div class="form-group">
                                    <label for="name">
                                        <i class="fas fa-building mr-1"></i>Nombre de la Empresa <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name', $company->name) }}"
                                           placeholder="Ej: Mi Empresa S.A.S.">
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="nit">
                                        <i class="fas fa-id-card mr-1"></i>NIT <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('nit') is-invalid @enderror"
                                           id="nit" name="nit" value="{{ old('nit', $company->nit) }}"
                                           placeholder="Ej: 900123456-1">
                                    @error('nit')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email">
                                        <i class="fas fa-envelope mr-1"></i>Email Corporativo <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email', $company->email) }}"
                                           placeholder="contacto@miempresa.com">
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="phone">
                                        <i class="fas fa-phone mr-1"></i>Teléfono
                                    </label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                           id="phone" name="phone" value="{{ old('phone', $company->phone) }}"
                                           placeholder="+57 301 234 5678">
                                    @error('phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="address">
                                        <i class="fas fa-map-marker-alt mr-1"></i>Dirección
                                    </label>
                                    <textarea class="form-control @error('address') is-invalid @enderror"
                                              id="address" name="address" rows="3"
                                              placeholder="Dirección completa de la empresa">{{ old('address', $company->address) }}</textarea>
                                    @error('address')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Logo --}}
                                <div class="form-group">
                                    <label for="logo">
                                        <i class="fas fa-image mr-1"></i>Logo de la Empresa
                                    </label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input @error('logo') is-invalid @enderror"
                                                   id="logo" name="logo" accept="image/*">
                                            <label class="custom-file-label" for="logo">
                                                {{ $company->logo ? 'Cambiar logo actual...' : 'Seleccionar archivo...' }}
                                            </label>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        Formatos permitidos: JPG, PNG, SVG. Tamaño máximo: 2MB. Recomendado: 200x200px
                                    </small>
                                    @error('logo')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror

                                    {{-- Vista previa del logo --}}
                                    <div class="mt-3">
                                        @if($company->logo)
                                            <img id="logo-current" src="{{ $company->getLogoUrl() }}" alt="Logo actual"
                                                 class="img-thumbnail" style="max-width: 150px;">
                                        @endif
                                        <img id="logo-preview" src="" alt="Vista previa"
                                             class="img-thumbnail" style="max-width: 150px; display: none;">
                                    </div>
                                </div>

                                {{-- Estadísticas de la Empresa --}}
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Usuarios Registrados</span>
                                        <span class="info-box-number">
                                            {{ $company->users()->count() }} / {{ $company->max_users }}
                                            <small class="text-muted">({{ number_format(($company->users()->count() / $company->max_users) * 100, 1) }}% utilizado)</small>
                                        </span>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" style="width: {{ ($company->users()->count() / $company->max_users) * 100 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Configuración de Licencia --}}
                            <div class="col-md-6">
                                <h5 class="text-success border-bottom pb-2 mb-3">
                                    <i class="fas fa-certificate mr-2"></i>Configuración de Licencia
                                </h5>

                                <div class="form-group">
                                    <label for="license_type">
                                        <i class="fas fa-tag mr-1"></i>Tipo de Licencia <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control select2 @error('license_type') is-invalid @enderror"
                                            id="license_type" name="license_type">
                                        <option value="">Seleccionar tipo...</option>
                                        <option value="trial" {{ old('license_type', $company->license_type) == 'trial' ? 'selected' : '' }}>
                                            Trial - 30 días (3 usuarios)
                                        </option>
                                        <option value="standard" {{ old('license_type', $company->license_type) == 'standard' ? 'selected' : '' }}>
                                            Standard - 1 año (25 usuarios)
                                        </option>
                                        <option value="premium" {{ old('license_type', $company->license_type) == 'premium' ? 'selected' : '' }}>
                                            Premium - 1 año (100 usuarios)
                                        </option>
                                    </select>
                                    @error('license_type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="license_expires_at">
                                        <i class="fas fa-calendar-alt mr-1"></i>Fecha de Expiración
                                    </label>
                                    <input type="date" class="form-control @error('license_expires_at') is-invalid @enderror"
                                           id="license_expires_at" name="license_expires_at"
                                           value="{{ old('license_expires_at', $company->license_expires_at ? $company->license_expires_at->format('Y-m-d') : '') }}">
                                    @error('license_expires_at')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="max_users">
                                        <i class="fas fa-users mr-1"></i>Máximo de Usuarios <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control @error('max_users') is-invalid @enderror"
                                           id="max_users" name="max_users" value="{{ old('max_users', $company->max_users) }}"
                                           min="1" max="1000" placeholder="25">
                                    @error('max_users')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    @if($company->users()->count() > 0)
                                        <small class="form-text text-warning">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Actualmente hay {{ $company->users()->count() }} usuarios registrados
                                        </small>
                                    @endif
                                </div>

                                {{-- Estado --}}
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_active"
                                               name="is_active" value="1" {{ old('is_active', $company->is_active) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">
                                            <i class="fas fa-power-off mr-1"></i>Empresa Activa
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Solo las empresas activas pueden acceder al sistema
                                    </small>
                                </div>

                                {{-- Personalización Visual --}}
                                <h5 class="text-warning border-bottom pb-2 mb-3 mt-4">
                                    <i class="fas fa-palette mr-2"></i>Personalización Visual
                                </h5>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="primary_color">
                                                <i class="fas fa-palette mr-1"></i>Color Primario <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="color" class="form-control form-control-color @error('primary_color') is-invalid @enderror"
                                                       id="primary_color" name="primary_color"
                                                       value="{{ old('primary_color', $company->primary_color) }}">
                                                <div class="input-group-append">
                                                    <span class="input-group-text" id="color-preview-primary"
                                                          style="background-color: {{ old('primary_color', $company->primary_color) }}; width: 40px;"></span>
                                                </div>
                                            </div>
                                            @error('primary_color')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="secondary_color">
                                                <i class="fas fa-palette mr-1"></i>Color Secundario <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="color" class="form-control form-control-color @error('secondary_color') is-invalid @enderror"
                                                       id="secondary_color" name="secondary_color"
                                                       value="{{ old('secondary_color', $company->secondary_color) }}">
                                                <div class="input-group-append">
                                                    <span class="input-group-text" id="color-preview-secondary"
                                                          style="background-color: {{ old('secondary_color', $company->secondary_color) }}; width: 40px;"></span>
                                                </div>
                                            </div>
                                            @error('secondary_color')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Configuraciones Adicionales --}}
                                <div class="form-group">
                                    <label for="copy_from_company">
                                        <i class="fas fa-copy mr-1"></i>Copiar Configuraciones de:
                                    </label>
                                    <select class="form-control select2" id="copy_from_company">
                                        <option value="">No copiar configuraciones</option>
                                        @foreach(App\Models\Company::where('is_active', true)->where('id', '!=', $company->id)->get() as $otherCompany)
                                            <option value="{{ $otherCompany->id }}">{{ $otherCompany->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">
                                        Selecciona una empresa para copiar sus colores y configuraciones
                                    </small>
                                </div>

                                {{-- Acciones Rápidas --}}
                                <div class="card card-outline card-secondary mt-3">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-bolt mr-1"></i>Acciones Rápidas
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="btn-group btn-group-sm d-flex" role="group">
                                            <button type="button" class="btn btn-info" onclick="renewLicense({{ $company->id }}, '{{ $company->name }}')">
                                                <i class="fas fa-sync-alt mr-1"></i>Renovar Licencia
                                            </button>
                                            <button type="button" class="btn btn-{{ $company->is_active ? 'warning' : 'success' }}"
                                                    onclick="toggleStatus({{ $company->id }}, '{{ $company->name }}', {{ $company->is_active ? 'true' : 'false' }})">
                                                <i class="fas fa-{{ $company->is_active ? 'pause' : 'play' }} mr-1"></i>
                                                {{ $company->is_active ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Configuraciones Avanzadas (Colapsable) --}}
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card card-outline card-info collapsed-card">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-cogs mr-2"></i>Configuraciones Avanzadas
                                        </h3>
                                        <div class="card-tools">
                                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="timezone">
                                                        <i class="fas fa-clock mr-1"></i>Zona Horaria
                                                    </label>
                                                    <select class="form-control select2" id="timezone" name="settings[timezone]">
                                                        <option value="America/Bogota" {{ old('settings.timezone', $company->settings['timezone'] ?? '') == 'America/Bogota' ? 'selected' : '' }}>América/Bogotá (Colombia)</option>
                                                        <option value="America/New_York" {{ old('settings.timezone', $company->settings['timezone'] ?? '') == 'America/New_York' ? 'selected' : '' }}>América/New_York (USA Este)</option>
                                                        <option value="America/Los_Angeles" {{ old('settings.timezone', $company->settings['timezone'] ?? '') == 'America/Los_Angeles' ? 'selected' : '' }}>América/Los_Angeles (USA Oeste)</option>
                                                        <option value="Europe/Madrid" {{ old('settings.timezone', $company->settings['timezone'] ?? '') == 'Europe/Madrid' ? 'selected' : '' }}>Europa/Madrid (España)</option>
                                                        <option value="America/Mexico_City" {{ old('settings.timezone', $company->settings['timezone'] ?? '') == 'America/Mexico_City' ? 'selected' : '' }}>América/Mexico_City (México)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="currency">
                                                        <i class="fas fa-dollar-sign mr-1"></i>Moneda
                                                    </label>
                                                    <select class="form-control select2" id="currency" name="settings[currency]">
                                                        <option value="COP" {{ old('settings.currency', $company->settings['currency'] ?? '') == 'COP' ? 'selected' : '' }}>Peso Colombiano (COP)</option>
                                                        <option value="USD" {{ old('settings.currency', $company->settings['currency'] ?? '') == 'USD' ? 'selected' : '' }}>Dólar Americano (USD)</option>
                                                        <option value="EUR" {{ old('settings.currency', $company->settings['currency'] ?? '') == 'EUR' ? 'selected' : '' }}>Euro (EUR)</option>
                                                        <option value="MXN" {{ old('settings.currency', $company->settings['currency'] ?? '') == 'MXN' ? 'selected' : '' }}>Peso Mexicano (MXN)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="date_format">
                                                        <i class="fas fa-calendar mr-1"></i>Formato de Fecha
                                                    </label>
                                                    <select class="form-control select2" id="date_format" name="settings[date_format]">
                                                        <option value="d/m/Y" {{ old('settings.date_format', $company->settings['date_format'] ?? '') == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY</option>
                                                        <option value="m/d/Y" {{ old('settings.date_format', $company->settings['date_format'] ?? '') == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY</option>
                                                        <option value="Y-m-d" {{ old('settings.date_format', $company->settings['date_format'] ?? '') == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                                                        <option value="d-M-Y" {{ old('settings.date_format', $company->settings['date_format'] ?? '') == 'd-M-Y' ? 'selected' : '' }}>DD-MMM-YYYY</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-2"></i>Cancelar
                                </a>
                                <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-outline-info ml-2">
                                    <i class="fas fa-eye mr-2"></i>Ver Detalles
                                </a>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save mr-2"></i>Actualizar Empresa
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/js/company/companies.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            // Mostrar nombre del archivo seleccionado
            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass('selected').html(fileName);
            });

            // Inicializar vista previa de colores
            previewColors();

            // Auto-guardar cada 30 segundos (opcional)
            // setInterval(function() {
            //     // Auto-save logic here if needed
            // }, 30000);
        });
    </script>
@endsection