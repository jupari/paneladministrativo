@extends('adminlte::page')

@section('title', 'Gestión de Bodegas')

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
        $currentTitle = 'Bodegas';
        $currentIcon = 'fas fa-warehouse';
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Bodegas</h4>
        </div>
        @if(auth()->user()->hasRole('Administrator'))
            <div class="col-md-1 my-3">
                <button type="button" id="btn-nuevo-bodega" class="btn btn-primary btn-block mb-1" data-toggle="tooltip" data-placement="top" title="Crear bodega">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                </button>
            </div>
        @endif
        <div class="card-body">
            <table class="table table-bordered" id="bodegas-table" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ID</th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop

@push('modals')
@include('inventario.bodega.modal')
@endpush

@section('js')
<script src="{{ asset('assets/js/inventario/bodega.js') }}" type="text/javascript"></script>
@stop
