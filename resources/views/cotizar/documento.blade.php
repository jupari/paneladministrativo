@extends('adminlte::page')

@section('title', 'Clientes')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')


@section('content')

    <div class="card">
        <div class="card-header">
            <h4>Crea documento de cotización</h4>
        </div>
        <div class="card-body" >
            <div class="container">
                <p class="text-muted">Permite crear nuevo documento de cotización, agrega datos de la cabecera primero guarda y luego agrega los detalles al documento.</p>

                <form>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="estado" class="form-label">Estado:</label>
                            <select id="estado" class="form-select">
                                <option value="1">Creado</option>
                                <option value="1">Cerrado</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="documento" class="form-label">Documento:</label>
                            <select id="documento" class="form-select"></select>
                        </div>
                        <div class="col-md-4">
                            <label for="proyecto" class="form-label">Proyecto:</label>
                            <input type="text" id="proyecto" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label for="autorizacion" class="form-label">Autorización:</label>
                            <input type="text" id="autorizacion" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Origen de la Cotización:</label>
                            <input type="text" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Versión de la Cotización:</label>
                            <input type="text" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha:</label>
                            <input type="date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Nombre del cliente:</label>
                            <select id="estado" class="form-select">
                                <option value="1">Cliente de Prueba1</option>
                                <option value="1">Cliente de Prueba2</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sede:</label>
                            <select class="form-select">
                                <option value="1">Sede 1</option>
                                <option value="1">sede 2</option>
                            </select>
                        </div>
                    </div>
                </form>

                <hr>

                <div class="d-flex gap-2 mb-3">
                    <button class="btn btn-primary">Agregar Productos</button>
                    <button class="btn btn-danger">Quitar Productos</button>
                </div>

                <div class="accordion" id="cotizacionAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#impuestos">Ingreso de impuestos</button>
                        </h2>
                        <div id="impuestos" class="accordion-collapse collapse" data-bs-parent="#cotizacionAccordion">
                            <div class="accordion-body">Contenido de impuestos...</div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#items">Ingresar consecutivo de items</button>
                        </h2>
                        <div id="items" class="accordion-collapse collapse" data-bs-parent="#cotizacionAccordion">
                            <div class="accordion-body">Contenido de items...</div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#observaciones">Ingresar Observaciones a la cotizacion</button>
                        </h2>
                        <div id="observaciones" class="accordion-collapse collapse" data-bs-parent="#cotizacionAccordion">
                            <div class="accordion-body">Contenido de observaciones...</div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#condiciones">Ingresar las condiciones comerciales</button>
                        </h2>
                        <div id="condiciones" class="accordion-collapse collapse" data-bs-parent="#cotizacionAccordion">
                            <div class="accordion-body">Contenido de condiciones comerciales...</div>
                        </div>
                    </div>
                </div>
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
    <script src="{{asset('assets/js/cotizr/cotizacion.js') }}" type="text/javascript"></script>
@stop
