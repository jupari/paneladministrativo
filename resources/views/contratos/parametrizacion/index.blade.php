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
            @if(auth()->user()->can('parametrizacion.index'))
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
                        <fieldset class="border p-3 mb-4">
                            <div class="col-md-3 my-3">
                                <button type="button" class="btn btn-primary mb-3" onclick="agregarFilaNovedades()">Nuevo Registro</button>
                                <button id="btn-refresh" class="btn btn-outline-secondary mb-3" onclick="CargarNovedades()">Actualizar</button>
                                {{-- <button type="button" class="btn btn-warning mb-3" id="btn-gen-tabla-precios">
                                        <i class="fas fa-calculator"></i> Generar precios
                                </button> --}}
                            </div>
                            <div id="tabla-parametrizacion"></div>
                            <button id="guardarNovedades" class="btn btn-success mt-3" onclick="saveDataNovedades(null)">Guardar</button>
                        </fieldset>
                    </div>
                    <div class="tab-pane fade" id="costos-tab-pane" role="tabpanel" aria-labelledby="costos-tab" tabindex="0">
                         <fieldset class="border p-3 mb-4">
                            <div class="col-md-3 my-3">
                                {{-- <button type="button" class="btn btn-primary mb-3" id="btn-nuevo" onclick="agregarFilaCostos()">Nuevo Registro</button> --}}
                                <button type="button" class="btn btn-primary mb-3" id="btn-nuevo">Nuevo Registro</button>
                                <button id="btn-refresh" class="btn btn-outline-secondary mb-3" onclick="CargarCostos()">Actualizar</button>
                            </div>
                            <div id="tabla-parametrizacion-costos"></div>
                            {{-- <button id="btn-guardar-costos" class="btn btn-success mt-3" onclick="saveDataCostos(null)">Guardar</button> --}}
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

                            <div id="tabla-precios-cargo"></div>
                        </fieldset>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop


@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
 @stop

@section('js')
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

    </script>
    @php
        $paramNovedadesVer = filemtime(public_path('assets/js/contratos/parametrizacion/parametrizacion.js'));
        $paramCostosVer = filemtime(public_path('assets/js/contratos/parametrizacion/parametrizacionCostos.js'));
        $tablaPreciosVer = filemtime(public_path('assets/js/contratos/parametrizacion/tablaPreciosCargo.js'));
    @endphp
    <script src="{{ asset('assets/js/contratos/parametrizacion/parametrizacion.js') . '?v=' . $paramNovedadesVer }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/contratos/parametrizacion/parametrizacionCostos.js') . '?v=' . $paramCostosVer }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/contratos/parametrizacion/tablaPreciosCargo.js') . '?v=' . $tablaPreciosVer }}" type="text/javascript"></script>
@stop
