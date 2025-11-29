@extends('adminlte::page')

@section('title', 'Clientes')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')


@section('content')

    <div class="card">
        <div class="card-header">
            <h4>Clientes</h4>
        </div>
        <div class="card-body" >
            @if(auth()->user()->can('clientes.index'))
                <div class="col-md-1">
                <button type="button" onclick="regCli()" class="btn btn-primary btn-block mb-1" data-toggle="tooltip" data-placement="top" title="Crear Cliente">
                    <i class="fas fa-user-plus"></i>
                </button>
                </div>
            @endif
            <div class="col-md-12 my-3">
              <div class="table-responsive">
                  <table id="clientes-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                           <th>#</th>
                           <th>Tipo de identificación</th>
                           <th>Identificación</th>
                           <th>Tipo de persona</th>
                           <th>Nombre(s)</th>
                           <th>Apellidos(s)</th>
                           <th>Establecimiento</th>
                           <th>Correo electrónico</th>
                           <th>Número de tel.</th>
                           <th>Número de Celular</th>
                           <th>Fecha creación</th>
                           <th>Acciones</th>
                        </tr>
                     </thead>
                  </table>
              </div>
            </div>
        </div>
    </div>
@stop

@section('modals')
    @include('terceros.clientes.modal')
    <h1>holaaa</h1>
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script>
        const permisos =  @json($user);
        const dataPaises = @json($paises);
    </script>
    <script src="{{asset('assets/js/terceros/clientes/clientes.js') }}" type="text/javascript"></script>
@stop
