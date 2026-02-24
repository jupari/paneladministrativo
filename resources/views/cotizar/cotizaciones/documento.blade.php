@extends('adminlte::page')

@section('title', 'Cotizaciones')

@section('content_header')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('plugin.Datatables')

@section('plugin.Sweetalert2')

@section('plugin.Toastr')

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
@endpush


@section('content')
    <!-- Toast Container -->
    <div id="toast-container" class="toast-container" aria-live="polite" aria-atomic="true"></div>

    {{-- Breadcrumbs mejorados usando componente --}}
    @php
        $breadcrumbs = [
            [
                'title' => 'Cotizaciones',
                'icon' => 'fas fa-file-invoice',
                'url' => null
            ],
            [
                'title' => 'Cotizar',
                'icon' => 'fas fa-calculator',
                'url' => route('admin.cotizaciones.index')
            ]
        ];
        $currentTitle = 'Elaboración de la Cotización';
        $currentIcon = 'fas fa-edit';
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />
    {{-- Header principal mejorado --}}
    <div class="card border-0 shadow-lg mb-4">
        <div class="card-header bg-gradient-primary text-white p-4 border-0">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-1 fw-bold d-flex align-items-center">
                        <i class="fas fa-file-invoice-dollar me-3 fa-lg"></i>
                        Documento de Cotización
                    </h3>
                    <p class="mb-0 opacity-75">
                        <label id="document-label" class="me-2"></label>
                        <small>Gestione su cotización de manera profesional</small>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="cotization-status">
                        <span class="badge bg-light text-dark px-3 py-2 fs-6 shadow-sm">
                            <i class="fas fa-clock me-1"></i>
                            <span id="cotization-status">En progreso</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Resumen Financiero Sticky -->
            <div class="row mt-2" id="resumen-sticky">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center bg-dark bg-opacity-25 rounded-pill px-4 py-2">
                        <div class="text-center">
                            <small class="opacity-75">Subtotal</small>
                            <div class="fw-bold" id="sticky-subtotal">$0.00</div>
                        </div>
                        <div class="text-center">
                            <small class="opacity-75">Descuentos</small>
                            <div class="fw-bold text-warning" id="sticky-descuentos">$0.00</div>
                        </div>
                        <div class="text-center">
                            <small class="opacity-75">Impuestos</small>
                            <div class="fw-bold text-info" id="sticky-impuestos">$0.00</div>
                        </div>
                        <div class="text-center border-start border-light ps-3">
                            <small class="opacity-75">Total Final</small>
                            <div class="h5 mb-0 fw-bold text-success" id="sticky-total">$0.00</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-light rounded-pill" onclick="actualizarStickyAhora()" title="Ver resumen detallado">
                            <i class="fas fa-chart-line"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill ml-1" onclick="debugElementos()" title="Debug elementos">
                            <i class="fas fa-bug"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Indicador de progreso de cotización -->
    <div class="card border-0 shadow-sm mb-3" id="progress-indicator">
        <div class="card-body py-2">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fas fa-tasks text-primary me-2"></i>
                    <small class="text-muted">Progreso de Cotización</small>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="progress" style="width: 150px; height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 75%" id="cotization-progress"></div>
                    </div>
                    <small class="text-muted" id="progress-text">75% completado</small>
                    <span class="badge bg-success" id="status-badge">Activa</span>
                </div>
            </div>
            <!-- Barra de información mejorada -->
            <div class="card-body bg-light border-bottom">
                <div class="row align-items-center py-2">
                    <div class="col-md-1">
                        <div class="info-icon">
                            <i class="fas fa-info-circle text-primary fa-2x"></i>
                        </div>
                    </div>
                    <div class="col-md-11">
                        <div class="info-content">
                            <h6 class="mb-1 text-primary fw-bold">Guía de Proceso</h6>
                            <p class="mb-0 text-muted">
                                <i class="fas fa-lightbulb text-warning me-2"></i>
                                <strong>Paso a paso:</strong> Complete los datos de la cabecera, guarde la información y proceda a agregar los detalles del documento.
                            </p>
                            <div class="process-steps mt-2">
                                <span class="badge bg-primary me-1">
                                    <i class="fas fa-user"></i> 1. Cliente
                                </span>
                                <span class="badge bg-secondary me-1">
                                    <i class="fas fa-save"></i> 2. Guardar
                                </span>
                                <span class="badge bg-success">
                                    <i class="fas fa-plus"></i> 3. Productos
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body my-3">
                <!-- Skeleton Loader -->
                <div id="skeleton-loader" style="display: none;">
                    <div class="container">
                        <div class="skeleton-text mb-3" style="height: 16px; width: 70%;"></div>

                        <!-- Header Section Skeleton -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="skeleton-text mb-2" style="height: 14px; width: 30%;"></div>
                                <div class="skeleton-input"></div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="skeleton-text mb-2" style="height: 14px; width: 40%;"></div>
                                <div class="skeleton-input"></div>
                            </div>
                            <div class="col-md-4">
                                <div class="skeleton-text mb-2" style="height: 14px; width: 25%;"></div>
                                <div class="skeleton-input"></div>
                            </div>
                            <div class="col-md-4">
                                <div class="skeleton-text mb-2" style="height: 14px; width: 30%;"></div>
                                <div class="skeleton-input"></div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="skeleton-text mb-2" style="height: 14px; width: 45%;"></div>
                                <div class="skeleton-input"></div>
                            </div>
                            <div class="col-md-4">
                                <div class="skeleton-text mb-2" style="height: 14px; width: 50%;"></div>
                                <div class="skeleton-input"></div>
                            </div>
                            <div class="col-md-4">
                                <div class="skeleton-text mb-2" style="height: 14px; width: 20%;"></div>
                                <div class="skeleton-input"></div>
                            </div>
                        </div>

                        <!-- Client Section Skeleton -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="skeleton-text mb-2" style="height: 14px; width: 40%;"></div>
                                <div class="skeleton-input"></div>
                            </div>
                            <div class="col-md-4">
                                <div class="skeleton-text mb-2" style="height: 14px; width: 20%;"></div>
                                <div class="skeleton-input"></div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="skeleton-text mb-2" style="height: 14px; width: 25%;"></div>
                                <div class="skeleton-input"></div>
                            </div>
                        </div>

                        <!-- Observations Skeleton -->
                        <div class="row my-2">
                            <div class="col-12">
                                <div class="skeleton-text mb-2" style="height: 14px; width: 30%;"></div>
                                <div class="skeleton-textarea"></div>
                            </div>
                        </div>

                        <!-- Totals Card Skeleton -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <div class="skeleton-text" style="height: 18px; width: 40%;"></div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <div class="skeleton-text mb-2" style="height: 14px; width: 30%;"></div>
                                        <div class="skeleton-input"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="skeleton-text mb-2" style="height: 14px; width: 30%;"></div>
                                        <div class="skeleton-input"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="skeleton-text mb-2" style="height: 14px; width: 40%;"></div>
                                        <div class="skeleton-input"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Accordion Skeleton -->
                        <div class="mt-4">
                            <div class="skeleton-accordion mb-2"></div>
                            <div class="skeleton-accordion mb-2"></div>
                            <div class="skeleton-accordion mb-2"></div>
                        </div>

                        <!-- Action Buttons Skeleton -->
                        <div class="d-flex gap-3 mt-4">
                            <div class="skeleton-button"></div>
                            <div class="skeleton-button"></div>
                            <div class="skeleton-button"></div>
                        </div>
                    </div>
                </div>

                {{-- Contenido principal --}}
                <div id="main-content" class="container-fluid">
                    {{-- El contenido del formulario continuará aquí --}}

                    <form id="cotizacionForm" novalidate>
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="estado_id" class="form-label">Estado: <span class="text-danger">*</span></label>
                                <select id="estado_id" name="estado_id" class="form-select">
                                </select>
                                <div class="invalid-feedback" id="error_estado_id"></div>
                            </div>
                        </div>
                        <input type="hidden" name="tipo" id="tipo" value="COT">
                        <input type="hidden" name="id" id="id">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="num_documento" class="form-label">Número de Documento: <span class="text-danger">*</span></label>
                                <input type="text" id="num_documento" name="num_documento" class="form-control" readonly>
                                <div class="invalid-feedback" id="error_num_documento"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="proyecto" class="form-label">Proyecto:</label>
                                <input type="text" id="proyecto" name="proyecto" class="form-control">
                                <div class="invalid-feedback" id="error_proyecto"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="autorizacion_id" class="form-label">Autorización:</label>
                                <input type="text" id="autorizacion_id" name="autorizacion_id" class="form-control" readonly>
                                <div class="invalid-feedback" id="error_autorizacion_id"></div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="doc_origen" class="form-label">Origen de la Cotización:</label>
                                <input type="text" class="form-control" id="doc_origen" name="doc_origen" readonly>
                                <div class="invalid-feedback" id="error_doc_origen"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="version" class="form-label">Versión de la Cotización:</label>
                                <input type="number" class="form-control" id="version" name="version" readonly>
                                <div class="invalid-feedback" id="error_version"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="fecha" class="form-label">Fecha:</label>
                                <input type="date" class="form-control" id="fecha" name="fecha" value="{{ date('Y-m-d') }}">
                                <div class="invalid-feedback" id="error_fecha"></div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="cliente_id" class="form-label">Nombre del cliente <span class="text-danger">*</span></label>
                                <select id="cliente_id" name="cliente_id" class="form-select" onchange="fetchSucursales(this.value)"></select>
                                <div class="invalid-feedback" id="error_cliente_id"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="tercero_sucursal_id" class="form-label">Sede:</label>
                                <select class="form-select" id="tercero_sucursal_id" name="tercero_sucursal_id"></select>
                                <div class="invalid-feedback" id="error_tercero_sucursal_id"></div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="tercero_contacto_id" class="form-label">Contacto</label>
                                <select id="tercero_contacto_id" name="tercero_contacto_id" class="form-select"></select>
                                <div class="invalid-feedback" id="error_tercero_contacto_id"></div>
                            </div>
                        </div>

                        <div class="row my-2">
                            <div class="col-12">
                                <label for="observacion" class="form-label">Observaciones:</label>
                                <textarea class="form-control" name="observacion" id="observacion" rows="3" maxlength="1000" placeholder="Ingrese observaciones adicionales..."></textarea>
                                <div class="form-text">
                                    <span id="observacion_count">0</span>/1000 caracteres
                                </div>
                                <div class="invalid-feedback" id="error_observacion"></div>
                            </div>
                        </div>

                        <!-- Resumen Financiero Detallado -->

                    </form>
                    <!--botones anterior-->

                    <!-- Sistema de Pasos Progresivos -->
                    <div class="row mt-4" id="sistemaProgresivo">
                        <div class="col-12">
                            <!-- Barra de Progreso -->
                            <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="text-white mb-0">
                                            <i class="fas fa-route mr-2"></i>Completar Cotización
                                        </h5>
                                        <span class="badge badge-secondary" id="pasoActual">Paso 1 de 5</span>
                                    </div>
                                    <div class="progress mt-3" style="height: 8px; background-color: rgba(255,255,255,0.3);">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 20%;" id="barraProgreso"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Indicador de Estado de Guardado -->
                            <div class="alert alert-info d-none" id="estadoGuardado">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle text-success mr-2" id="iconoEstado"></i>
                                    <span id="mensajeEstado">Información básica guardada. Continúe con los siguientes pasos.</span>
                                </div>
                            </div>

                            <!-- Secciones Progresivas -->
                            <div id="seccionesProgresivas">
                                <!-- Elemento requerido por documento.js -->
                                <div id="accordionCotizacionDetails" style="display: none;"></div>

                                <!-- Paso 1: Impuestos y Descuentos -->
                                <div class="card mb-4 section-step d-none" id="paso-impuestos" data-paso="1">
                                    <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div class="step-circle mr-3">1</div>
                                            <div>
                                                <h6 class="mb-0">
                                                    <i class="fas fa-percent mr-2"></i>Impuestos y Descuentos
                                                </h6>
                                                <small class="opacity-75">Configure los impuestos y descuentos aplicables</small>
                                            </div>
                                        </div>
                                        <div class="step-status">
                                            <i class="fas fa-clock text-warning" id="status-impuestos"></i>
                                        </div>
                                    </div>
                                    <div class="card-body" id="impuestos">
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-12">
                                                    <p class="text-muted mb-3">Configure los impuestos y descuentos aplicables a esta cotización</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Sección de Descuentos -->
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0 text-primary"><i class="fas fa-tags mr-2"></i>Descuentos</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label for="concepto_descuento" class="form-label">Concepto de Descuento:</label>
                                                    <select class="form-select" id="concepto_descuento"></select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="tipo_descuento" class="form-label">Tipo:</label>
                                                    <select class="form-select" id="tipo_descuento">
                                                        <option value="porcentaje">Porcentaje (%)</option>
                                                        <option value="valor">Valor Fijo ($)</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="valor_descuento" class="form-label">Valor/Porcentaje:</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text" id="simbolo_descuento">%</span>
                                                        <input type="number" class="form-control" id="valor_descuento" min="0" step="0.01" placeholder="0.00">
                                                    </div>
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-primary btn-sm w-100" id="agregar_descuento">
                                                        <i class="fas fa-plus"></i> Agregar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Sección de Impuestos -->
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0 text-success"><i class="fas fa-percent mr-2"></i>Impuestos</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label for="concepto_impuesto" class="form-label">Concepto de Impuesto:</label>
                                                    <select class="form-select" id="concepto_impuesto"></select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="tipo_impuesto" class="form-label">Tipo:</label>
                                                    <select class="form-select" id="tipo_impuesto">
                                                        <option value="porcentaje">Porcentaje (%)</option>
                                                        <option value="valor">Valor Fijo ($)</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="valor_impuesto" class="form-label">Valor/Porcentaje:</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text" id="simbolo_impuesto">%</span>
                                                        <input type="number" class="form-control" id="valor_impuesto" min="0" step="0.01" placeholder="0.00">
                                                    </div>
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-success btn-sm w-100" id="agregar_impuesto">
                                                        <i class="fas fa-plus"></i> Agregar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tabla de Impuestos y Descuentos Agregados -->
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fas fa-list mr-2"></i>Impuestos y Descuentos Aplicados</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover" id="tabla_impuestos_descuentos">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th width="5%">
                                                                <input type="checkbox" id="select_all_impuestos" title="Seleccionar todos">
                                                            </th>
                                                            <th width="15%">Registro</th>
                                                            <th width="25%">Concepto</th>
                                                            <th width="15%">Tipo</th>
                                                            <th width="15%">Porcentaje/Valor</th>
                                                            <th width="10%">Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tbody_impuestos_descuentos">
                                                        <tr id="no_items_row">
                                                            <td colspan="7" class="text-center text-muted py-3">
                                                                <i class="fas fa-info-circle mr-2"></i>
                                                                No hay impuestos o descuentos agregados
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            {{-- <div class="row mt-3">
                                                <div class="col-md-12">
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-outline-danger btn-sm" id="eliminar_seleccionados" disabled>
                                                            <i class="fas fa-trash mr-1"></i> Eliminar Seleccionados
                                                        </button>
                                                        <button type="button" class="btn btn-outline-warning btn-sm" id="limpiar_todo">
                                                            <i class="fas fa-broom mr-1"></i> Limpiar Todo
                                                        </button>
                                                    </div>
                                                </div>
                                            </div> --}}
                                        </div>
                                    </div>

                                    <div class="alert alert-info mt-3">
                                        <small>
                                            <strong>Nota:</strong> Los impuestos y descuentos se aplicarán automáticamente al subtotal de la cotización.
                                            Los valores calculados se actualizarán en tiempo real en la sección de totales.
                                        </small>
                                    </div>
                                </div>

                                <!-- Paso 2: Items y Subitems -->
                                <div class="card mb-4 section-step d-none" id="paso-items" data-paso="2">
                                    <div class="card-header bg-gradient-success text-white d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div class="step-circle mr-3">2</div>
                                            <div>
                                                <h6 class="mb-0">
                                                    <i class="fas fa-list mr-2"></i>Items del Proyecto
                                                </h6>
                                                <small class="opacity-75">Agregue los elementos principales</small>
                                            </div>
                                        </div>
                                        <div class="step-status">
                                            <i class="fas fa-clock text-warning" id="status-items"></i>
                                        </div>
                                    </div>
                                    <div class="card-body" id="conitems">
                                        <div class="row mb-3">
                                            <div class="col-12">
                                                <p class="text-muted mb-3">Gestione los items y subitems de la cotización</p>
                                            </div>
                                        </div>

                                        <!-- Formulario para agregar item -->
                                        <div class="card mb-4">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0 text-primary"><i class="fas fa-plus mr-2"></i>Agregar Nuevo Item</h6>
                                            </div>
                                            <div class="card-body">
                                                <form id="formAgregarItem">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="item_nombre" class="form-label">Nombre del Item <span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control" id="item_nombre" placeholder="Ingrese el nombre del item" maxlength="255">
                                                                <div class="invalid-feedback" id="error_item_nombre"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <button type="button" class="btn btn-success" id="btn_agregar_item">
                                                                <i class="fas fa-plus"></i> Agregar Item
                                                            </button>
                                                            <button type="button" class="btn btn-secondary ml-2" id="btn_limpiar_item">
                                                                <i class="fas fa-broom"></i> Limpiar
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Tabla de items -->
                                        <div class="card">
                                            <div class="card-header bg-light">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0 text-dark"><i class="fas fa-list mr-2"></i>Items de la Cotización</h6>
                                                    <div>
                                                        <button type="button" class="btn btn-danger btn-sm" id="btn_eliminar_items_seleccionados" disabled>
                                                            <i class="fas fa-trash"></i> Eliminar Seleccionados
                                                        </button>
                                                        <button type="button" class="btn btn-warning btn-sm ml-2" id="btn_limpiar_todos_items">
                                                            <i class="fas fa-broom"></i> Limpiar Todo
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-hover table-bordered mb-0" id="tabla_items">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th width="40">
                                                                    <input type="checkbox" id="select_all_items" onchange="toggleSelectAllItems()" title="Seleccionar todos">
                                                                </th>
                                                                <th width="60">#</th>
                                                                <th width="200">Nombre del Item</th>
                                                                <th>Subitems</th>
                                                                <th width="100">Estado</th>
                                                                <th width="150">Acciones</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="tbody_items">
                                                            <tr class="" id="no_items_row_items">
                                                                <td colspan="6" class="text-center text-muted py-3">
                                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                                    No hay items agregados
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Información adicional -->
                                        <div class="row mt-3">
                                            <div class="col-md-12">
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle mr-2"></i>
                                                    <strong>Información:</strong> Los items representan categorías principales de la cotización.
                                                    Para agregar detalles específicos como cantidades y precios, cree subitems asociados a cada item.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Paso 3: Observaciones -->
                                <div class="card mb-4 section-step d-none" id="paso-observaciones" data-paso="3">
                                    <div class="card-header bg-gradient-info text-white d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div class="step-circle mr-3">3</div>
                                            <div>
                                                <h6 class="mb-0">
                                                    <i class="fas fa-comment mr-2"></i>Observaciones Adicionales
                                                </h6>
                                                <small class="opacity-75">Agregue comentarios importantes</small>
                                            </div>
                                        </div>
                                        <div class="step-status">
                                            <i class="fas fa-clock text-warning" id="status-observaciones"></i>
                                        </div>
                                    </div>
                                    <div class="card-body" id="observaciones">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <form id="formObservacion">
                                                    <div class="row">
                                                        <div class="col-md-10">
                                                            <div class="form-group">
                                                                <label for="observacionSelect">Seleccione una observación</label>
                                                                <select class="form-control" id="observacionSelect">
                                                                    <option value="">Seleccione una observación...</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label>&nbsp;</label>
                                                                <button type="button" class="btn btn-success btn-block" id="agregar_observacion" onclick="agregarObservacion()">
                                                                    <i class="fas fa-plus"></i> Agregar
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>

                                                <!-- Lista de observaciones agregadas -->
                                                <div class="mt-3">
                                                    <h6>Observaciones Seleccionadas</h6>
                                                    <div class="table-responsive">
                                                        <table id="tablaObservaciones" class="table table-bordered table-striped" style="display: none;">
                                                            <thead class="table-secondary">
                                                                <tr>
                                                                    <th width="5%">#</th>
                                                                    <th width="85%">Observación</th>
                                                                    <th width="10%">Acciones</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="observacionesTableBody">
                                                            </tbody>
                                                        </table>
                                                        <div id="noObservacionesMessage" class="alert alert-info" style="display: block;">
                                                            No se han seleccionado observaciones.
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Paso 4: Condiciones Comerciales -->
                                <div class="card mb-4 section-step d-none" id="paso-condiciones" data-paso="4">
                                    <div class="card-header bg-gradient-warning text-dark d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div class="step-circle mr-3">4</div>
                                            <div>
                                                <h6 class="mb-0">
                                                    <i class="fas fa-handshake mr-2"></i>Condiciones Comerciales
                                                </h6>
                                                <small class="opacity-75">Defina términos y condiciones</small>
                                            </div>
                                        </div>
                                        <div class="step-status">
                                            <i class="fas fa-clock text-warning" id="status-condiciones"></i>
                                        </div>
                                    </div>
                                    <div class="card-body" id="condicionescomerciales">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <form id="formCondicionesComerciales">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="tiempo_entrega">Tiempo de Entrega</label>
                                                                    <input type="text" class="form-control" id="tiempo_entrega" name="tiempo_entrega" placeholder="Ej: 15 días hábiles" maxlength="255">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="duracion_oferta">Duración de la Oferta</label>
                                                                    <input type="text" class="form-control" id="duracion_oferta" name="duracion_oferta" placeholder="Ej: 30 días calendario" maxlength="255">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <label for="lugar_obra">Lugar de Obra</label>
                                                                    <textarea class="form-control" id="lugar_obra" name="lugar_obra" rows="2" placeholder="Dirección completa del lugar donde se realizará la obra" maxlength="500"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <label for="garantia">Garantía</label>
                                                                    <textarea class="form-control" id="garantia" name="garantia" rows="3" placeholder="Describa las condiciones de garantía ofrecidas" maxlength="500"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <label for="forma_pago">Forma de Pago</label>
                                                                    <textarea class="form-control" id="forma_pago" name="forma_pago" rows="3" placeholder="Especifique las condiciones y modalidades de pago" maxlength="500"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="form-group text-end">
                                                                    <button type="button" class="btn btn-secondary" onclick="limpiarCondicionesComerciales()">
                                                                        <i class="fas fa-eraser"></i> Limpiar
                                                                    </button>
                                                                    <button type="button" class="btn btn-success ml-2" id="btnGuardarCondiciones"  onclick="guardarCondicionesComerciales()">
                                                                        <i class="fas fa-save"></i> Guardar Condiciones
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                    <!-- Resumen de condiciones guardadas -->
                                                    <div id="resumenCondiciones" class="mt-3" style="display: none;">
                                                        <div class="alert alert-success">
                                                            <h6><i class="fas fa-check-circle"></i> Condiciones Comerciales Guardadas</h6>
                                                            <div id="resumenContenido"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Paso 5: Agregar Productos -->
                                <div class="card mb-4 section-step d-none" id="paso-productos" data-paso="5">
                                    <div class="card-header bg-gradient-danger text-white d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div class="step-circle mr-3">5</div>
                                            <div>
                                                <h6 class="mb-0">
                                                    <i class="fas fa-box mr-2"></i>Agregar Productos
                                                </h6>
                                                <small class="opacity-75">Finalice agregando productos y salarios</small>
                                            </div>
                                        </div>
                                        <div class="step-status">
                                            <i class="fas fa-clock text-warning" id="status-productos"></i>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex gap-2 mb-3" id="botonesAgregarProductos">
                                            <button class="btn btn-primary" id="agregarProductos" onclick="window.testModal ? window.testModal() : console.log('testModal no disponible')">
                                                <i class="fas fa-plus-circle mr-2"></i>Agregar Productos
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de navegación -->
                            <div class="d-flex justify-content-between mb-4" id="botonesNavegacion" style="display: none !important;">
                                <button class="btn btn-outline-secondary" id="btnAnterior" disabled>
                                    <i class="fas fa-chevron-left mr-2"></i>Anterior
                                </button>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-primary" id="btnOmitir">
                                        <i class="fas fa-forward mr-2"></i>Omitir Paso
                                    </button>
                                    <button class="btn btn-primary" id="btnSiguiente">
                                        Siguiente<i class="fas fa-chevron-right ml-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección de Productos Guardados -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <i class="fas fa-box-open"></i> Productos en la Cotización
                                    </h6>
                                    <div>
                                        <span class="badge badge-light" id="contadorProductosGuardados">0</span>
                                        <button class="btn btn-sm btn-outline-light ml-2" id="btnAplicarUtilidadHeader" onclick="mostrarModalUtilidad()" title="Aplicar margen de utilidad">
                                            <i class="fas fa-percentage"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-light ml-2" id="btnRefrescarProductos" onclick="cargarProductosGuardados()">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <!-- Loading state -->
                                    <div id="loadingProductosGuardados" class="text-center p-4 d-none">
                                        <div class="spinner-border text-info" role="status">
                                            <span class="sr-only">Cargando productos...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Cargando productos guardados...</p>
                                    </div>

                                    <!-- Empty state -->
                                    <div id="emptyProductosGuardados" class="text-center p-5">
                                        <i class="fas fa-box text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <h6 class="text-muted mt-3">No hay productos agregados</h6>
                                        <p class="text-muted small">Los productos que agregues aparecerán aquí</p>
                                    </div>

                                    <!-- Products table -->
                                    <div class="table-responsive" id="tablaProductosGuardados" style="display: none;">
                                        <table class="table table-hover mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="5%">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" id="selectAllProductosGuardados" onchange="toggleSelectAllProductosGuardados()">
                                                            <label class="custom-control-label" for="selectAllProductosGuardados"></label>
                                                        </div>
                                                    </th>
                                                    <th width="35%">Producto</th>
                                                    <th width="10%">Cantidad</th>
                                                    <th width="15%">Valor Unit.</th>
                                                    <th width="10%">Descuento</th>
                                                    <th width="15%">Total</th>
                                                    <th width="10%">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyProductosGuardados">
                                                <!-- Los productos se cargarán aquí dinámicamente -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Actions footer -->
                                    <div class="card-footer bg-light d-none" id="footerProductosGuardados">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="text-muted small">Productos seleccionados: <span id="contadorSeleccionadosGuardados">0</span></span>
                                            </div>
                                            <div>
                                                <button class="btn btn-sm btn-success ml-2" id="btnAplicarUtilidad" onclick="mostrarModalUtilidad()" title="Aplicar margen de utilidad">
                                                    <i class="fas fa-percentage"></i> Utilidad
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" id="btnEliminarSeleccionados" onclick="eliminarProductosSeleccionados()" disabled>
                                                    <i class="fas fa-trash"></i> Eliminar Seleccionados
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Totales de la Cotización -->
                                    <div class="card shadow-lg border-0 my-3" id="resumen-totales-cotizacion" style="position: sticky; top: 20px; z-index: 100;">
                                        <div class="card-header bg-gradient-primary text-white border-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0 d-flex align-items-center">
                                                    <i class="fas fa-calculator me-2"></i>💰 Resumen Financiero de la Cotización
                                                </h5>
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-sm btn-outline-light" onclick="actualizarTotalesManualmente()" title="Recalcular">
                                                        <i class="fas fa-sync-alt"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-light" onclick="toggleResumenDetalle()" id="btn-toggle-resumen">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body p-4" id="resumen-detalle-content">
                                            <!-- Campos ocultos para el formulario -->
                                            <input type="hidden" id="subtotal" name="subtotal" value="0.00">
                                            <input type="hidden" id="descuento" name="descuento" value="0.00">
                                            <input type="hidden" id="total_impuesto" name="total_impuesto" value="0.00">
                                            <input type="hidden" id="total" name="total" value="0.00">

                                            <!-- Totales informativos con diseño mejorado -->
                                            <div class="row g-3 mb-4">
                                                <div class="col-md-3">
                                                    <div class="card border-0 shadow-sm h-100">
                                                        <div class="card-body text-center p-3">
                                                            <div class="d-flex align-items-center justify-content-center mb-2">
                                                                <div class="bg-success bg-opacity-10 rounded-circle p-2 me-2">
                                                                    <i class="fas fa-chart-bar text-success"></i>
                                                                </div>
                                                                <h6 class="card-title text-success mb-0">Subtotal</h6>
                                                            </div>
                                                            <div class="h4 fw-bold text-success mb-1" id="display-subtotal-valor">$0.00</div>
                                                            <small class="text-muted">Suma de productos</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="card border-0 shadow-sm h-100">
                                                        <div class="card-body text-center p-3">
                                                            <div class="d-flex align-items-center justify-content-center mb-2">
                                                                <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-2">
                                                                    <i class="fas fa-percentage text-warning"></i>
                                                                </div>
                                                                <h6 class="card-title text-warning mb-0">Descuentos</h6>
                                                            </div>
                                                            <div class="h4 fw-bold text-warning mb-1" id="display-descuento-valor">$0.00</div>
                                                            <small class="text-muted">Total descuentos</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="card border-0 shadow-sm h-100">
                                                        <div class="card-body text-center p-3">
                                                            <div class="d-flex align-items-center justify-content-center mb-2">
                                                                <div class="bg-info bg-opacity-10 rounded-circle p-2 me-2">
                                                                    <i class="fas fa-receipt text-info"></i>
                                                                </div>
                                                                <h6 class="card-title text-info mb-0">Impuestos</h6>
                                                            </div>
                                                            <div class="h4 fw-bold text-info mb-1" id="display-impuesto-valor">$0.00</div>
                                                            <small class="text-muted">IVA y otros</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="card border-0 shadow-lg bg-gradient-primary text-white h-100">
                                                        <div class="card-body text-center p-3">
                                                            <div class="d-flex align-items-center justify-content-center mb-2">
                                                                <div class="bg-white bg-opacity-20 rounded-circle p-2 me-2">
                                                                    <i class="fas fa-money-bill-wave text-dark"></i>
                                                                </div>
                                                                <h6 class="card-title mb-0">Total Final</h6>
                                                            </div>
                                                            <div class="h3 fw-bold mb-1" id="display-total-valor">$0.00</div>
                                                            <small class="opacity-75">Valor a pagar</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Fórmula visual mejorada -->
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="alert alert-light border-0 bg-light bg-opacity-50">
                                                        <div class="d-flex align-items-center justify-content-center flex-wrap gap-2">
                                                            <div class="d-flex align-items-center">
                                                                <span class="badge bg-success me-1">Subtotal</span>
                                                            </div>
                                                            <i class="fas fa-minus text-muted"></i>
                                                            <div class="d-flex align-items-center">
                                                                <span class="badge bg-warning">Descuentos</span>
                                                            </div>
                                                            <i class="fas fa-plus text-muted"></i>
                                                            <div class="d-flex align-items-center">
                                                                <span class="badge bg-info">Impuestos</span>
                                                            </div>
                                                            <i class="fas fa-equals text-muted"></i>
                                                            <div class="d-flex align-items-center">
                                                                <span class="badge bg-primary fs-6">Total Final</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- FINNNNN -->

                                    <!--botones -->
                                     <div class="d-flex justify-content-between align-items-center my-4" id="botones">
                                        <div class="">
                                            <a class="btn btn-secondary" href="javascript:history.back()">
                                                <i class="fas fa-arrow-left mr-1"></i>
                                                Atras
                                            </a>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <!-- Botones de PDF -->
                                            <div class="btn-group" role="group" style="" id="pdf-buttons">
                                                <button type="button" class="btn btn-outline-danger" onclick="previewPdf()" title="Vista previa PDF">
                                                    <i class="fas fa-eye me-1"></i>
                                                    <span class="d-none d-md-inline">Vista Previa</span>
                                                </button>
                                                <button type="button" class="btn btn-danger" onclick="downloadPdf()" title="Descargar PDF">
                                                    <i class="fas fa-file-pdf me-1"></i>
                                                    <span class="d-none d-md-inline">Descargar PDF</span>
                                                </button>
                                            </div>

                                            <button class="btn btn-primary" id="agregarCotizacion">
                                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="spinnerRegister"></span>
                                                Guardar Cotización
                                            </button>
                                        </div>
                                    </div>
                                    <!--fin botones-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- <!-- Modal para Quitar Productos/Salarios -->
    <div class="modal fade" id="modalQuitarProductos" tabindex="-1" aria-labelledby="modalQuitarProductosLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalQuitarProductosLabel">
                        <i class="fas fa-minus-circle"></i> Quitar Productos y Salarios
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Advertencia:</strong> Seleccione los elementos que desea quitar de la cotización.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="40">
                                        <input type="checkbox" id="selectAllElementos">
                                    </th>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Cantidad/Personal</th>
                                    <th>Costo Total</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyElementosAQuitar">
                                <tr id="noElementosAQuitar">
                                    <td colspan="5" class="text-center text-muted">
                                        No hay elementos para quitar
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <strong>Total a Quitar: $<span id="totalAQuitar">0.00</span></strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmarQuitarProductos" disabled>
                        <i class="fas fa-trash"></i> Confirmar y Quitar
                    </button>
                </div>
            </div>
        </div>
    </div> --}}

    <!-- Modal para crear subitem -->
    <div class="modal fade" id="modalCrearSubitem" tabindex="-1" aria-labelledby="modalCrearSubitemLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCrearSubitemLabel">
                        <i class="fas fa-plus"></i> Crear Nuevo Subitem
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formCrearSubitem" onsubmit="guardarSubitem(event)">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <input type="hidden" name="cotizacion_item_id" id="cotizacion_item_id">
                                <input type="hidden" name="subitem_id_edit" id="subitem_id_edit">
                                <div class="form-group">
                                    <label for="subitem_codigo" class="form-label">Código <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="subitem_codigo" maxlength="50" placeholder="Ej: SUB-001">
                                    <div class="invalid-feedback" id="error_subitem_codigo"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="subitem_nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="subitem_nombre" maxlength="255" placeholder="Nombre del subitem">
                                    <div class="invalid-feedback" id="error_subitem_nombre"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="subitem_unidad_medida" class="form-label">Unidad de Medida <span class="text-danger">*</span></label>
                                    <select class="form-control" id="subitem_unidad_medida">
                                        <option value="">Seleccione unidad...</option>
                                    </select>
                                    <div class="invalid-feedback" id="error_subitem_unidad_medida"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="subitem_cantidad" class="form-label">Cantidad Base</label>
                                    <input type="number" class="form-control" id="subitem_cantidad" min="0" step="0.01" value="1">
                                    <div class="invalid-feedback" id="error_subitem_cantidad"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="subitem_observacion" class="form-label">Observación</label>
                                    <textarea class="form-control" id="subitem_observacion" rows="3" maxlength="500" placeholder="Descripción adicional del subitem" oninput="actualizarContadorObservacion()"></textarea>
                                    <div class="form-text">
                                        <span id="subitem_observacion_count">0</span>/500 caracteres
                                    </div>
                                    <div class="invalid-feedback" id="error_subitem_observacion"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="btn_guardar_subitem">
                            <i class="fas fa-save"></i> Guardar Subitem
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@include('cotizar.cotizaciones.modal-agregar-productos');
@include('cotizar.cotizaciones.modal-utilidad');

@section('css')
    <style>
        /* Skeleton Loader Styles */
        .skeleton-text, .skeleton-input, .skeleton-textarea, .skeleton-button, .skeleton-accordion {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite;
            border-radius: 4px;
        }

        .skeleton-text {
            height: 14px;
            margin-bottom: 8px;
        }

        .skeleton-input {
            height: 38px;
            width: 100%;
            border-radius: 6px;
        }

        .skeleton-textarea {
            height: 80px;
            width: 100%;
            border-radius: 6px;
        }

        .skeleton-button {
            height: 38px;
            width: 120px;
            border-radius: 6px;
        }

        .skeleton-accordion {
            height: 48px;
            width: 100%;
            border-radius: 6px;
        }

        @keyframes skeleton-loading {
            0% {
                background-position: 200% 0;
            }
            100% {
                background-position: -200% 0;
            }
        }

        /* Fade transition for skeleton */
        #skeleton-loader, #main-content {
            transition: opacity 0.3s ease-in-out;
        }

        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        /* Rest of existing styles */
        .is-invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: #dc3545;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }

        /* Estilos para la sección de totales */
        .card-header h5 {
            color: #495057;
            font-weight: 600;
        }

        .input-group-text {
            background-color: #e9ecef;
            border-color: #ced4da;
            color: #6c757d;
            font-weight: 500;
        }

        #subtotal_menos_descuento {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        #total {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            font-weight: 700;
            color: #0c5460;
            font-size: 1.1em;
        }

        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }

        /* Animación para campos calculados */
        .total-field {
            transition: all 0.3s ease;
        }

        .total-field:focus {
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .accordion .btn-link {
            color: #000; /* o el color que quieras */
            text-decoration: none !important;
        }

        .accordion .btn-link:hover {
            color: #000;
            text-decoration: none !important;
        }

        /* Estilos responsivos */
        @media (max-width: 768px) {
            .input-group-text {
                font-size: 0.875rem;
            }

            .alert-info {
                font-size: 0.8rem;
            }
        }

        /* Estilos para el accordion de impuestos y descuentos */
        .badge.bg-success {
            background-color: #28a745 !important;
        }

        .badge.bg-primary {
            background-color: #007bff !important;
        }

        #tabla_impuestos_descuentos {
            font-size: 0.9rem;
        }

        #tabla_impuestos_descuentos th {
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            background-color: #f8f9fa;
        }

        #tabla_impuestos_descuentos .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .text-success {
            color: #28a745 !important;
        }

        .text-primary {
            color: #007bff !important;
        }

        .btn-outline-danger:hover {
            color: #fff;
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .card .card-header h6 {
            margin-bottom: 0;
            font-weight: 600;
        }

        .input-group-text {
            min-width: 40px;
            justify-content: center;
        }

        /* Estilo para badges */
        .badge {
            font-size: 0.7rem;
            margin-right: 0.5rem;
        }

        /* Estilos para la sección de items */
        #tabla_items {
            font-size: 0.9rem;
        }

        #tabla_items th {
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            background-color: #f8f9fa;
            vertical-align: middle;
        }

        #tabla_items .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        #formAgregarItem .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        /* Modal de subitem */
        #modalCrearSubitem .modal-header {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        /* Botones de items */
        #btn_agregar_item:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
        }

        /* Total de items */
        #total_items_valor {
            font-size: 1.1rem;
            font-weight: bold;
        }

        /* Estilos para contenedor de subitems */
        .subitems-container {
            min-width: 150px;
        }

        .subitems-table-container {
            margin-top: 10px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            background-color: #f8f9fa;
            padding: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Estilos para modales de productos y salarios */
        #modalAgregarProductos .modal-xl {
            max-width: 1200px;
        }

        #modalAgregarProductos .nav-tabs .nav-link {
            font-weight: 500;
            border: none;
            border-bottom: 3px solid transparent;
            background: none;
            color: #6c757d;
        }

        #modalAgregarProductos .nav-tabs .nav-link.active {
            color: #495057;
            border-bottom-color: #007bff;
            background-color: #f8f9fa;
        }

        #modalAgregarProductos .table-responsive {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }

        #modalAgregarProductos .sticky-top {
            top: 0;
            z-index: 1020;
        }

        .producto-seleccionado {
            background-color: #e3f2fd !important;
        }

        .personal-item {
            background-color: #f8f9fa;
            border-left: 4px solid #28a745;
        }

        /* Botones de productos y salarios */
        #botonesAgregarProductos .btn {
            min-width: 150px;
            font-weight: 500;
        }

        #botonesAgregarProductos .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        /* Total displays */
        #totalProductos, #totalSalarios, #totalGeneral, #totalAQuitar {
            color: #28a745;
            font-weight: bold;
        }

        /* Input de búsqueda */
        #buscarProducto:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .subitems-table-container .table {
            margin-bottom: 0;
            background-color: white;
            border-radius: 4px;
            overflow: hidden;
        }

        .subitems-table-container .table th {
            background-color: #e9ecef;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 8px;
        }

        .subitems-table-container .table td {
            padding: 8px;
            font-size: 0.85rem;
            vertical-align: middle;
        }

        .subitems-table-container code {
            background-color: #f1f3f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.8rem;
        }

        /* Botón toggle mejorado */
        .toggle-subitems {
            transition: all 0.3s ease;
            min-width: 140px;
            text-align: left;
        }

        .toggle-subitems:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,123,255,0.3);
        }

        /* Animación para mostrar/ocultar */
        .subitems-table-container {
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive para tabla de subitems */
        @media (max-width: 768px) {
            .subitems-table-container .table th,
            .subitems-table-container .table td {
                padding: 4px;
                font-size: 0.75rem;
            }
        }

        /* Mejoras visuales */
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
        }

        .text-primary {
            color: #007bff !important;
        }
    </style>
        <!-- Estilos específicos para productos guardados -->
    <style>
        .product-icon {
            width: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .producto-guardado-checkbox:checked + .custom-control-label::before {
            background-color: #007bff;
            border-color: #007bff;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.775rem;
        }

        .badge {
            font-size: 0.75rem;
        }

        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }

        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }

        .table thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 10;
        }

        .card-header .badge {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .table tbody tr {
            animation: fadeIn 0.3s ease-in-out;
        }

        /* Estilos personalizados para el modal de edición */
        .swal2-popup {
            font-size: 0.875rem !important;
        }

        .swal2-title {
            font-size: 1.5rem !important;
            margin-bottom: 1rem !important;
        }

        .swal2-html-container {
            margin: 0 !important;
            padding: 0 !important;
        }

        .form-label {
            margin-bottom: 0.5rem !important;
            font-weight: 600 !important;
        }

        .input-group-text {
            background-color: #f8f9fa;
            border-color: #ced4da;
            color: #6c757d;
        }

        .card.bg-light {
            background-color: #f8f9fa !important;
            border: 1px solid #dee2e6 !important;
        }

        .card-body {
            padding: 1rem !important;
        }

        .text-sm {
            font-size: 0.875rem !important;
        }

        .swal2-confirm {
            margin-right: 0.5rem !important;
        }

        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }

        /* Mejoras para los inputs del modal */
        .swal2-popup input.form-control {
            padding: 0.5rem 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }

        .swal2-popup input.form-control:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .swal2-validation-message {
            background: #f8d7da !important;
            color: #721c24 !important;
            border: 1px solid #f5c6cb !important;
        }

        /* === Mejoras de UX/UI para el Header === */
        .bg-gradient-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
        }

        .bg-gradient-light {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        }

        .shadow-lg {
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
        }

        .rounded-lg {
            border-radius: 0.5rem !important;
        }

        /* === Resumen Financiero Mejorado === */
        #resumen-sticky {
            animation: fadeInDown 0.6s ease-out;
        }

        #resumen-sticky .bg-dark {
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        #resumen-totales-cotizacion {
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        #resumen-totales-cotizacion:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
        }

        #resumen-totales-cotizacion .card-body .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        #resumen-totales-cotizacion .card-body .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        #progress-indicator {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .progress {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .progress-bar {
            border-radius: 10px;
            transition: width 0.8s ease;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* === Responsive para Resumen Sticky === */
        @media (max-width: 768px) {
            #resumen-sticky .d-flex {
                flex-direction: column;
                gap: 1rem;
            }

            #resumen-sticky .text-center {
                border: none !important;
                padding: 0 !important;
            }

            #resumen-totales-cotizacion {
                position: static !important;
                margin-top: 1rem;
            }
        }

        .cotization-status .badge {
            border: 2px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .cotization-status .badge:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .info-icon {
            text-align: center;
            padding: 10px;
            background: rgba(0, 123, 255, 0.1);
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: auto;
        }

        .process-steps .badge {
            font-size: 0.75rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            transition: all 0.2s ease;
        }

        .process-steps .badge:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: "›";
            font-weight: bold;
            color: #6c757d;
        }

        .breadcrumb-item.active {
            color: #495057 !important;
        }

        .info-content h6 {
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .card-header.bg-gradient-primary {
            border: none;
            position: relative;
            overflow: hidden;
        }

        .card-header.bg-gradient-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.1) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.1) 50%, rgba(255, 255, 255, 0.1) 75%, transparent 75%, transparent);
            background-size: 20px 20px;
            animation: move 2s linear infinite;
            opacity: 0.3;
        }

        @keyframes move {
            0% {
                background-position: 0 0;
            }
            100% {
                background-position: 20px 20px;
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .info-icon {
                width: 50px;
                height: 50px;
            }

            .card-header h3 {
                font-size: 1.5rem;
            }

            .process-steps {
                text-align: center;
            }

            .process-steps .badge {
                display: block;
                margin: 0.25rem 0;
            }
        }

        /* ===========================================
           Sistema Progresivo de Pasos
        =========================================== */

        /* Círculos de pasos */
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
            border: 2px solid rgba(255, 255, 255, 0.5);
            transition: all 0.3s ease;
        }

        .step-circle.completed {
            background: #28a745;
            border-color: #28a745;
            color: white;
        }

        .step-circle.active {
            background: white;
            color: #007bff;
            border-color: white;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }

        /* Estados de las secciones */
        .section-step {
            transition: all 0.3s ease;
            transform: translateY(0);
        }

        .section-step.completed .card-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
        }

        .section-step.completed .step-status i {
            color: #fff !important;
        }

        .section-step.completed .step-status i::before {
            content: "\f00c";
        }

        /* Animaciones */
        .section-step.slide-in {
            animation: slideInUp 0.5s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Barra de progreso personalizada */
        .progress {
            height: 8px;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            transition: width 0.6s ease;
            border-radius: 10px;
        }

        /* Botones de navegación */
        #botonesNavegacion .btn {
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        #botonesNavegacion .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        /* Estados del indicador */
        .alert {
            border: none;
            border-radius: 15px;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .step-circle {
                width: 35px;
                height: 35px;
                font-size: 1rem;
            }

            #botonesNavegacion {
                flex-direction: column;
                gap: 0.5rem;
            }

            #botonesNavegacion .btn {
                width: 100%;
            }
        }
    </style>
@stop

@section('js')

     <script>
        // Configurar variables desde el servidor y delegar al coordinador
        document.addEventListener('DOMContentLoaded', function() {
            const config = {
                variable: @json($variable),
                clientes: @json($clientes ?? []),
                estados: @json($estados ?? []),
                consecutivo: @json($consecutivo ?? ''),
                cotizacion: @json($cotizacion ?? null)
            };
            // Delegar la inicialización al coordinador
            if (window.initializeCotizacionWithServerConfig) {
                window.initializeCotizacionWithServerConfig(config);
            } else {
                console.error('❌ Función de inicialización no disponible');
            }
        });
    </script>

    <!--<script src="{{ asset('assets/js/cotizar/documento-protection.js') }}" type="text/javascript"></script>-->
    <script
        src="{{ asset('assets/js/cotizar/documento-protection.js') }}?v={{ time() }}"
        type="text/javascript">
    </script>
    <!--<script src="{{ asset('assets/js/cotizar/documento-base.js') }}" type="text/javascript"></script>-->
    <script
        src="{{ asset('assets/js/cotizar/documento-base.js') }}?v={{ time() }}"
        type="text/javascript">
    </script>
    <!--<script src="{{ asset('assets/js/cotizar/sticky-summary.js') }}" type="text/javascript"></script>-->
    <script
        src="{{ asset('assets/js/cotizar/sticky-summary.js') }}?v={{ time() }}"
        type="text/javascript">
    </script>

    <!--<script src="{{ asset('assets/js/cotizar/documento-progressive.js') }}" type="text/javascript"></script>-->
    <script
        src="{{ asset('assets/js/cotizar/documento-progressive.js') }}?v={{ time() }}"
        type="text/javascript">
    </script>

    <!--<script src="{{ asset('assets/js/cotizar/documento.js') }}" type="text/javascript"></script>-->
    <script
        src="{{ asset('assets/js/cotizar/documento.js') }}?v={{ time() }}"
        type="text/javascript">
    </script>

    <!--<script src="{{ asset('assets/js/cotizar/documento-coordinator.js') }}" type="text/javascript"></script>-->
    <script
        src="{{ asset('assets/js/cotizar/documento-coordinator.js') }}?v={{ time() }}"
        type="text/javascript">
    </script>

    <script
        src="{{ asset('assets/js/cotizar/utilidades.js') }}?v={{ time() }}"
        type="text/javascript">
    </script>

@stop

