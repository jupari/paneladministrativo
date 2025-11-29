@extends('adminlte::page')

@section('title', 'Items Propios')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')


@section('content')
    <div class="card">
        <div class="card-header">
            <h4>Registrar Item Propio</h4>
        </div>
        <div class="card-body" >
                 <fieldset class="border p-3 mb-4">
                    <div class="col-md-3 my-3">
                        <div class="d-flex gap-2 mb-3">
                            <button id="btn-nuevo" class="btn btn-primary">Nuevo</button>
                            <button id="btn-refresh" class="btn btn-outline-secondary">Actualizar</button>
                        </div>
                    </div>
                    <div id="tabla-items-propios"></div>
                    {{-- <button id="guardarItemsPropios" class="btn btn-success mt-3" onclick="saveDataItemsPropios()">Guardar</button> --}}
                </fieldset>
        </div>
    </div>
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script>
        const categorias = @json($categorias);
        const unidades   = @json($unidades);
    </script>
    <script src="{{asset('assets/js/contratos/items/itempropio.js') }}" type="text/javascript"></script>
@stop
