@extends('adminlte::page')

@section('title', 'Parámetros Globales de Nómina')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')

@section('content')
    @php
        $breadcrumbs = [
            [
                'title' => 'Nómina',
                'icon'  => 'fas fa-money-check-alt',
                'url'   => null,
            ],
        ];
        $currentTitle = 'Parámetros Globales';
        $currentIcon  = 'fas fa-sliders-h';
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

    {{-- Alerta informativa: parámetros activos --}}
    @if($paramActivo)
    <div class="alert d-flex align-items-start mb-3 py-2 px-3"
         style="background:#e8f4fd; border:1px solid #bee5f5; border-radius:6px; font-size:.84rem;">
        <i class="fas fa-info-circle text-info mt-1 mr-2" style="flex-shrink:0; font-size:1rem;"></i>
        <div>
            <strong>Parámetros activos ({{ $paramActivo->vigencia }}):</strong>
            &nbsp;SMLV <strong>${{ number_format($paramActivo->smlv, 0, ',', '.') }}</strong>
            &nbsp;·&nbsp; Aux. Transporte <strong>${{ number_format($paramActivo->aux_transporte, 0, ',', '.') }}</strong>
            &nbsp;·&nbsp; UVT <strong>${{ number_format($paramActivo->uvt, 0, ',', '.') }}</strong>
            &nbsp;·&nbsp; Tope Ley 1607 <strong>×{{ $paramActivo->tope_exoneracion_ley1607 }} SMLV</strong>
            &nbsp;·&nbsp;
            <span class="text-muted" style="font-size:.78rem;">
                Estos valores son usados por el <strong>Motor de Liquidación de Nómina</strong> al cotizar personal.
            </span>
        </div>
    </div>
    @else
    <div class="alert alert-warning d-flex align-items-center py-2 px-3 mb-3" style="font-size:.84rem;">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <strong>Sin parámetros activos.</strong>&nbsp;
        Configure al menos un registro activo para que el motor de liquidación funcione correctamente.
    </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center"
             style="background:linear-gradient(135deg,#1e3c72 0%,#2a5298 100%);">
            <div class="d-flex align-items-center">
                <i class="fas fa-sliders-h text-white mr-2"></i>
                <h5 class="mb-0 text-white font-weight-bold">Parámetros Globales de Nómina</h5>
            </div>
        </div>

        <div class="px-4 pt-3 pb-2">
            <div class="alert py-2 px-3 mb-0 d-flex align-items-start"
                 style="background:#fff8e1; border:1px solid #ffe082; border-radius:6px; font-size:.81rem;">
                <i class="fas fa-calculator text-warning mt-1 mr-2" style="flex-shrink:0;"></i>
                <span class="text-secondary">
                    Cada registro corresponde a un <strong>año fiscal</strong>.
                    El motor usa el registro activo del año en curso; si no existe, toma el más reciente activo.
                    Los valores afectan directamente el cálculo de costos empresa en cotizaciones de personal.
                </span>
            </div>
        </div>
        <div class="d-flex justify-content-lg-start my-2 mx-3">
            @if(auth()->user()->can('nomina.parametros.create'))
            <button type="button" onclick="regParametro()"
                    class="btn btn-warning btn-sm font-weight-bold shadow-sm">
                <i class="fas fa-plus mr-1"></i> Nuevo Parámetro
            </button>
            @endif
        </div>

        <div class="card-body pt-2">
            <div class="table-responsive">
                <table id="parametros-table" class="table table-bordered table-hover table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center" style="width:40px;">#</th>
                            <th class="text-center">Vigencia</th>
                            <th class="text-center">SMLV</th>
                            <th class="text-center">Aux. Transporte</th>
                            <th class="text-center">UVT</th>
                            <th class="text-center">Tope Ley 1607</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@stop

@include('nomina.parametros_globales.modal')

@section('css')
@stop

@section('js')
    <script src="{{ asset('assets/js/nomina/parametros_globales/parametros.js') }}?v={{ filemtime(public_path('assets/js/nomina/parametros_globales/parametros.js')) }}" type="text/javascript"></script>
@stop
