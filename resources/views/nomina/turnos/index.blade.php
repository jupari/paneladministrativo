@extends('adminlte::page')

@section('title', 'Turnos de Trabajo')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')

@section('content')
    @php
        $breadcrumbs = [
            ['title' => 'Nómina', 'icon' => 'fas fa-money-check-alt', 'url' => null],
        ];
        $currentTitle = 'Turnos de Trabajo';
        $currentIcon  = 'fas fa-clock';
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center"
             style="background:linear-gradient(135deg,#1e3c72 0%,#2a5298 100%);">
            <div class="d-flex align-items-center">
                <i class="fas fa-clock text-white mr-2"></i>
                <h5 class="mb-0 text-white font-weight-bold">Catálogo de Turnos de Trabajo</h5>
            </div>
            @if(auth()->user()->can('nomina.turnos.create'))
            <button type="button" onclick="regTurno()"
                    class="btn btn-warning btn-sm font-weight-bold shadow-sm">
                <i class="fas fa-plus mr-1"></i> Nuevo Turno
            </button>
            @endif
        </div>

        <div class="px-4 pt-3 pb-2">
            <div class="alert py-2 px-3 mb-0 d-flex align-items-start"
                 style="background:#e8f4fd; border:1px solid #bee5f5; border-radius:6px; font-size:.81rem;">
                <i class="fas fa-info-circle text-info mt-1 mr-2" style="flex-shrink:0;"></i>
                <span class="text-secondary">
                    Los turnos configurados aquí están disponibles en el modal de cotización de nómina
                    al usar la modalidad <strong>Costo Día</strong>. Cada turno define el tipo de jornada
                    (diurna/nocturna), si aplica a domingos/festivos, y los tipos de horas extra permitidos.
                    Restricción legal: máx. <strong>7 h ordinarias/día</strong> y <strong>2 h extra/día</strong>.
                </span>
            </div>
        </div>

        <div class="d-flex justify-content-lg-start my-2 mx-3">
            @if(auth()->user()->can('nomina.turnos.create'))
            <button type="button" onclick="regTurno()"
                    class="btn btn-warning btn-sm font-weight-bold shadow-sm">
                <i class="fas fa-plus mr-1"></i> Nuevo Turno
            </button>
            @endif
        </div>

        <div class="card-body pt-2">
            <div class="table-responsive">
                <table id="turnos-table" class="table table-bordered table-hover table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center" style="width:40px;">#</th>
                            <th>Nombre</th>
                            <th class="text-center">Tipo jornada</th>
                            <th class="text-center">Dom/Fest</th>
                            <th class="text-center">H. Ordinarias</th>
                            <th class="text-center">Horas Extra</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@stop

@include('nomina.turnos.modal')

@section('css')
@stop

@section('js')
    <script src="{{ asset('assets/js/nomina/turnos/turnos.js') }}?v={{ filemtime(public_path('assets/js/nomina/turnos/turnos.js')) }}" type="text/javascript"></script>
@stop
