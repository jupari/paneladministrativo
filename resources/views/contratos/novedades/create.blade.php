@extends('adminlte::page')

@section('title', 'Novedades')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')


@section('content')

    <div class="card">
        <div class="card-header">
            <h4>Registrar Novedad</h4>
        </div>
        <div class="card-body" >
            <form id="formNovedad">
                @csrf
                <div class="mb-3">
                    <label for="nombre">Nombre de la novedad</label>
                    <input type="text" name="nombre" class="form-control" id="nombre" required>
                    <span class="text-danger error-nombre"></span>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="active" name="active">
                            <label for="active">Activo</label>
                        </div>
                        <span class="text-danger error_active"></span>
                    </div>
                </div>

                <h5>Detalles</h5>
                <div class="col-12 col-md-3 my-3">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalDetalle">
                        Agregar Detalle
                    </button>
                </div>
                <table id="tabla-detalles" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <span class="text-danger error-detalles"></span>

                <button type="button" class="btn btn-success mt-3" onclick="saveData()">Guardar Novedad</button>
            </form>
        </div>
    </div>
@stop


@include('contratos.novedades.modal')

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script src="{{asset('assets/js/contratos/novedades/create.js') }}" type="text/javascript"></script>
@stop
