@extends('adminlte::page')

@section('title','Movimientos de Inventario')

@section('content')

    {{-- Breadcrumbs mejorados usando componente --}}
    @php
        $breadcrumbs = [
            [
                'title' => 'Inventarios',
                'icon' => 'fas fa-boxes',
                'url' => null
            ]
        ];
        $currentTitle = 'Movimientos de Inventario';
        $currentIcon = 'fas fa-exchange-alt';
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

    <div class="card">
        <div class="card-header">
            <h4>Movimientos de Inventario</h4>
        </div>
        @if(auth()->user()->hasRole('Administrator'))
            <div class="col-md-1 my-3">
                <button type="button" id="btn-nuevo-movimiento" class="btn btn-primary btn-block mb-1" data-toggle="tooltip" data-placement="top" title="Crear Movimiento">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                </button>
            </div>
        @endif
        <div class="card-body">
            <table id="movimientos-table" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ID</th>
                        <th>Número de Documento</th>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Tipo</th>
                        <th>Observación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop

@push('modals')
@include('inventario.movimiento.modal')
@endpush

@section('js')
<script src="{{asset('assets/js/inventario/movimiento.js') }}" type="text/javascript"></script>
@stop
