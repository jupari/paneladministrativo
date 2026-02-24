@extends('adminlte::page')

@section('title', 'Empleados')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')


@section('content')

    {{-- Breadcrumbs mejorados usando componente --}}
    @php
        $breadcrumbs = [
            [
                'title' => 'Recursos Humanos',
                'icon' => 'fas fa-users',
                'url' => null
            ]
        ];
        $currentTitle = 'Empleados';
        $currentIcon = 'fas fa-user';
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

    <div class="card">
        <div class="card-header">
            <h4>Empleados</h4>
        </div>
        <div class="card-body" >
            @if(auth()->user()->can('empleados.create'))
                <div class="col-md-1">
                <button type="button" onclick="regEmpleado()" class="btn btn-primary btn-block mb-1" data-toggle="tooltip" data-placement="top" title="Crear Empleado">
                    <i class="fas fa-user-plus"></i>
                </button>
                </div>
            @endif
            <div class="col-md-12 my-3">
              <div class="table-responsive">
                  <table id="empleados-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                           <th>#</th>
                           <th>Id</th>
                           <th>Nombres</th>
                           <th>Identificación</th>
                           <th>Expedida en</th>
                           <th>Fecha nacimiento</th>
                           <th>Fecha inicio labor</th>
                           <th>Dirección</th>
                           <th>Cargo</th>
                           <th>Estado</th>
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


@include('contratos.empleados.modal')


@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script>
        const user_id = @json($user_id);
        const paises = @json($paises);
        const ciudades = @json($ciudades);
        // Hacer los datos disponibles globalmente para el JavaScript
        window.paises = paises;
        window.ciudades = ciudades;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.6.0/dist/autoNumeric.min.js"></script>
    <script src="{{asset('assets/js/contratos/empleados/empleados.js') }}" type="text/javascript"></script>
@stop
