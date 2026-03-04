@extends('adminlte::page')

@section('title', 'Proveedores')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')


@section('content')

    {{-- Breadcrumbs mejorados usando componente --}}
    @php
        $breadcrumbs = [
            [
                'title' => 'Terceros',
                'icon' => 'fas fa-users',
                'url' => null
            ]
        ];
        $currentTitle = 'Proveedores';
        $currentIcon = 'fas fa-truck';
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />


    <div class="card">
        <div class="card-header">
            <h4>Proveedores</h4>
        </div>
        <div class="card-body" >
            @if(auth()->user()->can('proveedores.create'))
                <div class="col-md-1">
                <!-- Botón para modal simple (sin pasos) -->
                <button type="button" onclick="regProvDirect()" class="btn btn-success btn-block mb-1" data-toggle="tooltip" data-placement="top" title="Crear Proveedor (Simple)">
                    <i class="fas fa-user-plus"></i> Simple
                </button>
                <!-- Botón para modal con pasos (backup) -->
                <button type="button" onclick="regProv()" class="btn btn-primary btn-block mb-1" data-toggle="tooltip" data-placement="top" title="Crear Proveedor (Con Pasos)">
                    <i class="fas fa-user-plus"></i> Pasos
                </button>
                </div>
            @endif
            <div class="col-md-12 my-3">
              <div class="table-responsive">
                  <table id="proveedores-table" class="table table-bordered table-striped">
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

@include('terceros.proveedores.modal-simple')
@include('terceros.proveedores.modal-simple-direct')


@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script>
        const permisos =  @json($user);
        const dataPaises = @json($paises);
        const tiposPersona = @json($tiposPersona);
        const tiposIdentificacion = @json($tiposIdentificacion);
        const vendedores = @json($vendedores);
    </script>
    <script src="{{asset('assets/js/Terceros/proveedores/proveedor.js') }}" type="text/javascript"></script>
    <script src="{{asset('assets/js/Terceros/proveedores/proveedores-modal-steps.js') }}" type="text/javascript"></script>
    <script src="{{asset('assets/js/Terceros/proveedores/proveedor-direct.js') }}" type="text/javascript"></script>
@stop
