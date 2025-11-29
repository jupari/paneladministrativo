@extends('adminlte::page')

@section('title','Movimientos de Inventario')

@section('content')

    {{-- Breadcrumbs --}}
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light px-3 py-2 rounded">
            <li class="breadcrumb-item">
                <a href="{{ url('/dashboard') }}">Inicio</a>
            </li>
            <li class="breadcrumb-item">
                <a href="#">Inventarios</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Listado de Movimientos
            </li>
        </ol>
    </nav>

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
