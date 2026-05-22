@extends('adminlte::page')

@section('title', 'Parametrización')

@section('plugin.Tabulator')

@section('plugin.Sweetalert2')

@section('content')
    {{-- Breadcrumbs mejorados usando componente --}}
    @php
        $breadcrumbs = [
            [
                'title' => 'Parametrización',
                'icon' => 'fas fa-cog',
                'url' => null
            ]
        ];
        $currentTitle = 'Parametrización Liquidación';
        $currentIcon = 'fas fa-tags';
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />
    <div class="card">
        <div class="card-header">
            <h4>Parametrización</h4>
        </div>
        <div class="card-body" >
            @if(auth()->user()->can('configuracion.nomina.index'))
                 <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="novedades-tab" data-toggle="tab" data-target="#novedades-tab-pane" type="button" role="tab" aria-controls="novedades-tab-pane" aria-selected="true">Novedades</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="costos-tab" data-toggle="tab" data-target="#costos-tab-pane" type="button" role="tab" aria-controls="costos-tab-pane" aria-selected="false">Costos</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tabla-precios-tab" data-toggle="tab" data-target="#tabla-precios-tab-pane"
                            type="button" role="tab" aria-controls="tabla-precios-tab-pane" aria-selected="false">
                            Tabla de precios por cargo
                        </button>
                    </li>
                 </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="novedades-tab-pane" role="tabpanel" aria-labelledby="novedades-tab" tabindex="0">
                        <fieldset class="border p-3 mb-4 d-none">
                            <div class="d-flex justify-content-start justify-content-lg-start  my-3">
                                <button type="button" class="btn btn-primary mb-3" onclick="abrirModalNovedad()">Nuevo Registro (Tabulator)</button>
                                <button id="btn-refresh" class="btn btn-outline-secondary mb-3" onclick="CargarNovedades()">Actualizar</button>
                            </div>
                            <div id="tabla-parametrizacion"></div>
                            <button id="guardarNovedades" class="btn btn-success mt-3" onclick="saveDataNovedades(null)">Guardar</button>
                        </fieldset>

                        <!-- NUEVA SECCIÓN: DataTable Novedades -->
                        <fieldset class="border p-3 mb-4 bg-light">
                            <div class="my-3">
                                <button type="button" class="btn btn-primary mb-3" onclick="abrirModalNovedad()">Nuevo Registro</button>
                                <button id="btn-refresh-dt" class="btn btn-outline-secondary mb-3" onclick="CargarNovedadesDT()">Actualizar</button>
                            </div>
                            <div id="tabla-parametrizacion-dt-wrapper" class="table-responsive">
                                <table id="tabla-parametrizacion-dt" class="table table-bordered table-striped" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Categoría</th>
                                            <th>Cargo</th>
                                            <th>Novedad</th>
                                            <th class="text-center">Valor Admon</th>
                                            <th class="text-center">Valor Obra</th>
                                            <th class="text-center">Valor %/$</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <button id="guardarNovedadesDT" class="btn btn-success mt-3" onclick="saveDataNovedadesDT()">Guardar</button>
                        </fieldset>
                    </div>
                    <div class="tab-pane fade" id="costos-tab-pane" role="tabpanel" aria-labelledby="costos-tab" tabindex="0">
                         <fieldset class="border p-3 mb-4">
                            <div class="col-12 col-md-6 my-3">
                                <button type="button" class="btn btn-primary mb-3" id="btn-nuevo">Nuevo Registro</button>
                                <button id="btn-refresh" class="btn btn-outline-secondary mb-3" onclick="CargarCostos()">Actualizar</button>
                                <button type="button" class="btn btn-success mb-3" onclick="importarCostosDesdeExcel()">
                                    <i class="fas fa-file-excel"></i> Importar Excel
                                </button>
                                <button id="btn-guardar-costos" class="btn btn-success mb-3" onclick="saveDataCostos(null)">
                                    <i class="fas fa-save"></i> Guardar todo
                                </button>
                            </div>
                            <div id="tabla-parametrizacion-costos"></div>
                        </fieldset>
                    </div>
                    <div class="tab-pane fade" id="tabla-precios-tab-pane" role="tabpanel" aria-labelledby="tabla-precios-tab" tabindex="0">
                        <fieldset class="border p-3 mb-4">
                            <div class="d-flex flex-wrap align-items-center mb-3">
                            <button type="button" class="btn btn-warning mr-2 mb-2" id="btn-gen-tabla-precios">
                                <i class="fas fa-calculator"></i> Generar / Recalcular
                            </button>

                            <button type="button" class="btn btn-outline-secondary mr-2 mb-2" id="btn-refresh-tabla-precios">
                                <i class="fas fa-sync"></i> Actualizar
                            </button>

                            <small class="text-muted mb-2" id="lbl-updated-tabla-precios"></small>
                            </div>

                            {{-- ── Panel: Fuentes activas por cargo ── --}}
                            <div class="mb-3">
                                <a class="d-flex align-items-center text-decoration-none text-dark"
                                   data-toggle="collapse" href="#panelFuentesActivas" role="button"
                                   aria-expanded="false" aria-controls="panelFuentesActivas">
                                    <i class="fas fa-info-circle text-info mr-2"></i>
                                    <strong style="font-size:.85rem;">Fuentes activas por cargo</strong>
                                    <i class="fas fa-chevron-down ml-auto text-muted" style="font-size:.75rem;"></i>
                                </a>
                                <div class="collapse mt-2" id="panelFuentesActivas">
                                    <div class="p-2 rounded" style="background:#f0f4f8; border:1px solid #cdd8e3; font-size:.78rem;">
                                        <p class="text-muted mb-2" style="font-size:.75rem;">
                                            Muestra qué dato usará el motor al <strong>Generar / Recalcular</strong> la tabla de precios de cada cargo.
                                        </p>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-1" style="font-size:.78rem;">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Cargo</th>
                                                        <th class="text-center">Salario Base</th>
                                                        <th class="text-center">ARL</th>
                                                        <th class="text-center">Aux. Transporte</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($cargosConfig as $c)
                                                    @php
                                                        $tieneSalario    = $c->salario_base !== null;
                                                        $tieneBasico     = array_key_exists($c->id, $cargosConBasico);
                                                        $nivel           = $c->arl_nivel ?? 1;
                                                        $pctArl          = $arlNivelesConfig[$nivel] ?? 0.522;
                                                        $arlLabels       = [1=>'Nivel I',2=>'Nivel II',3=>'Nivel III',4=>'Nivel IV',5=>'Nivel V'];
                                                        $arlLabel        = ($arlLabels[$nivel] ?? 'N/A') . ' (' . number_format($pctArl, 3) . '%)';

                                                        // Salario base
                                                        if ($tieneSalario) {
                                                            $salBadge = '<span class="badge badge-success"><i class="fas fa-check mr-1"></i>Cargo: $'
                                                                . number_format($c->salario_base, 0, ',', '.') . '</span>';
                                                        } elseif ($tieneBasico) {
                                                            $salBadge = '<span class="badge badge-warning text-dark"><i class="fas fa-table mr-1"></i>Parametrización</span>';
                                                        } else {
                                                            $salBadge = '<span class="badge badge-info"><i class="fas fa-equals mr-1"></i>SMLV vigente</span>';
                                                        }

                                                        // Aux. Transporte
                                                        if ($tieneSalario && $paramGlobal) {
                                                            $smlv2 = (float)$paramGlobal->smlv * 2;
                                                            $aplica = (float)$c->salario_base <= $smlv2;
                                                            if ($aplica) {
                                                                $auxBadge = '<span class="badge badge-success"><i class="fas fa-check mr-1"></i>Global: $'
                                                                    . number_format($paramGlobal->aux_transporte, 0, ',', '.') . '</span>';
                                                            } else {
                                                                $auxBadge = '<span class="badge badge-secondary"><i class="fas fa-times mr-1"></i>No aplica (>2 SMLV)</span>';
                                                            }
                                                        } elseif ($tieneBasico) {
                                                            $auxBadge = '<span class="badge badge-warning text-dark"><i class="fas fa-table mr-1"></i>Parametrización</span>';
                                                        } else {
                                                            $auxBadge = '<span class="badge badge-secondary"><i class="fas fa-times mr-1"></i>$0</span>';
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $c->nombre }}</td>
                                                        <td class="text-center">{!! $salBadge !!}</td>
                                                        <td class="text-center">
                                                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i>{{ $arlLabel }}</span>
                                                        </td>
                                                        <td class="text-center">{!! $auxBadge !!}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex flex-wrap" style="gap:8px; font-size:.72rem;">
                                            <span><span class="badge badge-success">&#x2713;</span> Configurado en cargo (prioridad)</span>
                                            <span><span class="badge badge-warning text-dark">&#x25A6;</span> Usando parametrización (fallback)</span>
                                            <span><span class="badge badge-info">&#x3D;</span> SMLV vigente (último recurso)</span>
                                            <span><span class="badge badge-secondary">&#x2715;</span> No aplica / $0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- ── Fin panel ── --}}

                            <div id="tabla-precios-cargo"></div>
                        </fieldset>
                    </div>
                </div>
            @endif
        </div>
    </div>
{{-- ── Modal: Crear / Editar Novedad ──────────────────────────────────────── --}}
<div class="modal fade" id="modal-novedad" tabindex="-1" role="dialog" aria-labelledby="modal-novedad-label" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modal-novedad-label">
                    <i class="fas fa-tag mr-2"></i>
                    <span id="modal-novedad-title">Nueva Novedad</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-novedad" autocomplete="off">
                    <input type="hidden" id="novedad-modal-id">

                    <div class="form-group">
                        <label for="novedad-modal-categoria">Categoría <span class="text-danger">*</span></label>
                        <select id="novedad-modal-categoria" class="form-control">
                            <option value="">-- Seleccione --</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="novedad-modal-cargo">Cargo <span class="text-danger">*</span></label>
                        <select id="novedad-modal-cargo" class="form-control">
                            <option value="">-- Seleccione --</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="novedad-modal-novedad">Novedad <span class="text-danger">*</span></label>
                        <select id="novedad-modal-novedad" class="form-control">
                            <option value="">-- Seleccione --</option>
                        </select>
                    </div>

                    <div class="form-row mb-2">
                        <div class="col-auto">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="novedad-modal-admon">
                                <label class="form-check-label" for="novedad-modal-admon">Valor Admon</label>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="novedad-modal-obra">
                                <label class="form-check-label" for="novedad-modal-obra">Valor Obra</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="novedad-modal-valor-pct">Valor %/$</label>
                        <input type="text" id="novedad-modal-valor-pct" class="form-control" placeholder="0">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary btn-guardar-novedad" onclick="guardarNovedad()">
                    <i class="fas fa-save mr-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>
@stop


@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
 @stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script type="text/javascript">
        let parametrizacion = @json($parametrizacion);
        let parametrizacioncostos = @json($parametrizacioncostos);
        const categorias = @json($categorias);
        const cargos = @json($cargos);
        const novedadesCombo = @json($novedadesDetalle);
        const unidades = @json($unidades);
        const itemsPropios = @json($itemsPropios);
        const cantHorasDiarias = @json($cantHorasDiarias);
        let initialData = @json($parametrizacioncostos);
        const firstTime = true;
        const TABLA_PRECIOS_GET_URL = '/admin/admin.parametrizacion.tabla_precios';
        const TABLA_PRECIOS_POST_URL = '/admin/admin.parametrizacion.generar_tabla_precios';
        window.categorias = categorias;
        window.itemsPropios = itemsPropios;

        </script>
    @php
        $paramNovedadesVer = filemtime(public_path('assets/js/contratos/parametrizacion/parametrizacion.js'));
        $paramCostosVer = filemtime(public_path('assets/js/contratos/parametrizacion/parametrizacionCostos.js'));
        $tablaPreciosVer = filemtime(public_path('assets/js/contratos/parametrizacion/tablaPreciosCargo.js'));
        @endphp
    <script src="{{ asset('assets/js/contratos/parametrizacion/parametrizacion.js') . '?v=' . $paramNovedadesVer }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/contratos/parametrizacion/parametrizacionCostos.js') . '?v=' . $paramCostosVer }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/contratos/parametrizacion/tablaPreciosCargo.js') . '?v=' . $tablaPreciosVer }}" type="text/javascript"></script>
    <!-- Script DataTable Novedades (prueba) -->
    <script src="{{ asset('assets/js/contratos/parametrizacion/parametrizacionDT.js') }}?v={{ time() }}" type="text/javascript"></script>
    <script src="/assets/js/contratos/parametrizacion/importarCostosExcel.js"></script>
    <script src="{{ asset('assets/js/contratos/parametrizacion/novedadesModal.js') }}?v={{ filemtime(public_path('assets/js/contratos/parametrizacion/novedadesModal.js')) }}" type="text/javascript"></script>
@stop
