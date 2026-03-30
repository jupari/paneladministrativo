@extends('adminlte::page')

@section('title', 'Clientes')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')


@section('content')

    {{-- Breadcrumbs mejorados usando componente --}}
    @php
        $breadcrumbs = [
            [
                'title' => 'Recursos Humanos',
                'icon' => 'fas fa-users',
                'url' => null
            ]
        ];
        $currentTitle = 'Cargos';
        $currentIcon = 'fas fa-briefcase';
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center"
             style="background:linear-gradient(135deg,#1e3c72 0%,#2a5298 100%);">
            <div class="d-flex align-items-center">
                <i class="fas fa-briefcase text-white mr-2"></i>
                <h5 class="mb-0 text-white font-weight-bold">Catálogo de Cargos</h5>
            </div>
            @if(auth()->user()->can('cargos.create'))
            <button type="button" onclick="regCargo()"
                    class="btn btn-warning btn-sm font-weight-bold shadow-sm">
                <i class="fas fa-plus mr-1"></i> Nuevo Cargo
            </button>
            @endif
        </div>

        <!-- Leyenda de campos de nómina -->
        <div class="px-4 pt-3 pb-2">
            <div class="alert py-2 px-3 mb-0 d-flex align-items-start"
                 style="background:#e8f4fd; border:1px solid #bee5f5; border-radius:6px; font-size:.82rem;">
                <i class="fas fa-calculator text-info mt-1 mr-2" style="flex-shrink:0;"></i>
                <span class="text-secondary">
                    Los campos <strong>Salario Base</strong>, <strong>ARL</strong> y <strong>Ley 1607</strong>
                    son usados por el Motor de Liquidación de Nómina al cotizar personal.
                    Configúrelos para obtener costos empresa precisos.
                    Si no se configuran, el motor asume <strong>SMLV vigente + ARL Nivel I + Exonerado</strong>.
                </span>
            </div>
        </div>

        <div class="card-body pt-2">
            <div class="table-responsive">
                <table id="cargos-table" class="table table-bordered table-hover table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center" style="width:40px;">#</th>
                            <th>Nombre</th>
                            <th class="text-center">Salario Base</th>
                            <th class="text-center">ARL</th>
                            <th class="text-center">Ley 1607</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@stop

@include('contratos.cargos.modal')


@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script src="{{asset('assets/js/contratos/cargos/cargos.js') }}" type="text/javascript"></script>
@stop
