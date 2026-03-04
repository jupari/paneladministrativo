@extends('adminlte::page')

@section('title', 'Nómina - Conceptos')

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
        $currentTitle = 'Conceptos de Nómina';
        $currentIcon = 'fas fa-list';
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

    <div class="card">
        <div class="card-header">
            <h4>Conceptos</h4>
        </div>

        <div class="card-body">
            @if(auth()->user()->can('nomina.conceptos.create'))
                <div class="col-md-1">
                    <button type="button" onclick="regConcept()"
                        class="btn btn-primary btn-block mb-1"
                        data-toggle="tooltip" data-placement="top" title="Crear Concepto">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            @endif

            <div class="col-md-12 my-3">
                <div class="table-responsive">
                    <table id="concepts-table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th class="exclude">id</th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Scope</th>
                            <th>Tipo</th>
                            <th>Método</th>
                            <th class="text-center">Activo</th>
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

@include('nomina.conceptos.modal')

@section('css')
@stop

@section('js')
    <script src="{{ asset('assets/js/nomina/conceptos/conceptos.js') }}" type="text/javascript"></script>
@stop
