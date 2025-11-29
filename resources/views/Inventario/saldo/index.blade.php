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
                Saldos de Inventario
            </li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header">
            <h4>Saldos de Inventario</h4>
        </div>
        <div class="card-body">
            <table id="saldos-table" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Talla</th>
                        <th>Color</th>
                        <th>Bodega</th>
                        <th>Saldo</th>
                        <th>Costo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop

@push('modals')
@include('inventario.saldo.modal')
@endpush

@section('js')
<script src="{{asset('assets/js/inventario/saldo.js') }}" type="text/javascript"></script>
@stop
