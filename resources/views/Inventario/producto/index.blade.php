@extends('adminlte::page')

@section('title', 'Inventarios - Productos')

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
        $currentTitle = 'Listado de Productos';
        $currentIcon = 'fas fa-cube';
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Productos</h4>
        </div>
        <div class="card-body">
            @if(auth()->user()->hasRole('Administrator'))
                    <div class="col-md-1">
                    <button type="button" id="btn-nuevo" class="btn btn-primary btn-block mb-1" data-toggle="tooltip" data-placement="top" title="Crear producto">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                    </button>
                    </div>
            @endif


            <table id="productos-table" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ID</th>
                        <th>Tipo de prenda</th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Unidad de medida</th>
                        <th>Marca</th>
                        <th>Categoría</th>
                        <th>Subcategoría</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop

@push('modals')
@include('inventario.producto.modal')
@include('inventario.producto.modal-skeleton')
@endpush

@section('js')
    <script src="{{asset('assets/js/inventario/producto.js') }}" type="text/javascript"></script>
@stop
