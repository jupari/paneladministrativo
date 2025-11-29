@extends('adminlte::page')

@section('title', 'Configuración')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')


@section('content')

    {{-- Breadcrumbs --}}
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light px-3 py-2 rounded">
            <li class="breadcrumb-item">
                <a href="{{ url('/dashboard') }}">Inicio</a>
            </li>
            <li class="breadcrumb-item">
                <a href="#">Configuración</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Parametros
            </li>
        </ol>
    </nav>
    <div class="card">
        <div class="card-header">
            <h4>Parametros</h4>
        </div>
        <div class="card-body" >
            @if(auth()->user()->hasRole('Administrator'))
                <div class="col-md-1">
                <button type="button" onclick="regParametro()" class="btn btn-primary btn-block mb-1" data-toggle="tooltip" data-placement="top" title="Crear parametro">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                </button>
                </div>
            @endif
            <div class="col-md-12 my-3">
              <div class="table-responsive">
                  <table id="parametros-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                           <th>#</th>
                           <th>id</th>
                           <th>Código</th>
                           <th>Nombre</th>
                           <th>Valor</th>
                           <th class="text-center">Estado</th>
                           <th>Acciones</th>
                        </tr>
                     </thead>
                     <tbody>
                     </tbody>
                  </table>
              </div>
            </div>
        </div>
    </div>
@stop


@include('admin.configuracion.modal')

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script src="{{asset('assets/js/configuracion/parametros.js') }}" type="text/javascript"></script>
@stop
