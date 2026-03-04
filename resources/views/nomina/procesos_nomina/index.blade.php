@extends('adminlte::page')

@section('title', 'Nómina - Periodos')

@section('plugin.Datatables')
@section('plugin.Sweetalert2')

@section('content')

    @php
        $breadcrumbs = [
            [
                'title' => 'Recursos Humanos',
                'icon' => 'fas fa-users',
                'url' => null
            ],
            [
                'title' => 'Nómina',
                'icon' => 'fas fa-money-check-alt',
                'url' => null
            ]
        ];
        $currentTitle = 'Periodos de Nómina';
        $currentIcon = 'fas fa-calendar-alt';
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

    <div class="card">
        <div class="card-header">
            <h4>Periodos de Nómina</h4>
        </div>

        <div class="card-body">
            @if(auth()->user()->can('nomina.procesos.create'))
                <div class="col-md-1">
                    <button type="button" onclick="regPayRun()"
                        class="btn btn-primary btn-block mb-1"
                        data-toggle="tooltip" data-placement="top" title="Crear Periodo">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            @endif

            <div class="col-md-12 my-3">
                <div class="table-responsive">
                    <table id="payruns-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th class="exclude">id</th>
                                <th>Tipo</th>
                                <th>Periodo</th>
                                <th>Fecha pago</th>
                                <th class="text-center">Estado</th>
                                <th>Creación</th>
                                <th class="text-center exclude">Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

        </div>
    </div>
@stop

@include('nomina.procesos_nomina.modal')

@section('css')
@stop

@section('js')
    <script src="{{ asset('assets/js/nomina/procesos_nomina/pay_runs.js') }}" type="text/javascript"></script>
@stop
