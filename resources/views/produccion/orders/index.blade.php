@extends('adminlte::page')

@section('title', 'Órdenes de Producción')
@section('plugin.Datatables')
@section('plugin.Sweetalert2')

@section('content')
    @php
    $breadcrumbs = [['title'=>'Producción','icon'=>'fas fa-industry','url'=>null]];
    $currentTitle='Órdenes';
    $currentIcon='fas fa-clipboard-list';
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

    <div class="card">
    <div class="card-header"><h4>Órdenes de producción</h4></div>
    <div class="card-body">

        <ul class="nav nav-tabs" id="prod-order-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="tab-orders" data-toggle="tab" href="#orders-pane" role="tab">Órdenes</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="tab-routing" data-toggle="tab" href="#routing-pane" role="tab">Routing (Operaciones)</a>
        </li>
        </ul>

        <div class="tab-content mt-3">
        {{-- TAB 1: Órdenes --}}
        <div class="tab-pane fade show active" id="orders-pane" role="tabpanel">
            @if(auth()->user()->can('ordenes.create'))
                <div class="col-md-1 mb-2">
                <button class="btn btn-primary btn-block" onclick="regOrder()" title="Crear Orden">
                    <i class="fas fa-plus"></i>
                </button>
                </div>
            @endif
             <div class="col-md-12 my-3">
            <div class="table-responsive">
            <table id="orders-table" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th class="exclude">id</th>
                    <th>Código</th>
                    <th>Producto</th>
                    <th>Objetivo</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Estado</th>
                    <th class="exclude">Acciones</th>
                </tr>
                </thead>
            </table>
            </div>
        </div>

        {{-- TAB 2: Routing --}}
        <div class="tab-pane fade" id="routing-pane" role="tabpanel">
            <div class="row">
            <div class="col-md-6">
                <label>Orden</label>
                <select id="routing_order_id" class="form-control"></select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary btn-block" onclick="loadRouting()">
                <i class="fas fa-search"></i> Cargar
                </button>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-success btn-block" onclick="regRouting()">
                <i class="fas fa-plus"></i> Agregar Operación
                </button>
            </div>
            </div>

            <div class="table-responsive mt-3">
            <table id="routing-table" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th class="exclude">id</th>
                    <th>Operación</th>
                    <th>Secuencia</th>
                    <th class="text-right">Qty x unidad</th>
                    <th class="text-right">Requerido</th>
                    <th class="text-center">Estado</th>
                    <th class="exclude text-center">Acciones</th>
                </tr>
                </thead>
            </table>
            </div>

        </div>
        </div>

    </div>
    </div>
@stop

@include('produccion.orders.modal')
@include('produccion.orders.routing_modal')

@section('js')
<script src="{{asset('assets/js/produccion/orders/orders.js') }}"></script>
<script src="{{asset('assets/js/produccion/orders/routing.js') }}"></script>
@stop




