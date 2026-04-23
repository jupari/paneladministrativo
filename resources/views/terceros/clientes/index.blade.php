@extends('adminlte::page')

@section('title', 'Clientes')

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
        $currentTitle = 'Clientes';
        $currentIcon = 'fas fa-user-tie';
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

    <div class="card">
        <div class="card-header">
            <h4>Clientes</h4>
        </div>
        <div class="card-body" >
            @if(auth()->user()->can('clientes.create'))
                <div class="col-md-2 mb-3">
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

@include('terceros.clientes.modal-simple', [
    'tercerotipo_id' => $tercerotipo_id ?? 1,
    'user_id' => $user_id ?? auth()->id(),
    'tiposIdentificacion' => $tiposIdentificacion ?? [],
    'tiposPersona' => $tiposPersona ?? [],
    'paises' => $paises ?? [],
    'vendedores' => $vendedores ?? [],
    'vendedorxrol' => $vendedorxrol ?? null
])

@section('css')
    {{-- Estilos para el modal de clientes mejorado --}}
    <link rel="stylesheet" href="{{asset('assets/css/modal-clientes-enhanced.css')}}">
    {{-- Forzar tema claro para el modal --}}
    <link rel="stylesheet" href="{{asset('assets/css/force-light-theme.css')}}">

    {{-- Estilos de empresa --}}
    <x-company-styles />
@stop

@section('js')
    <script>
        // Variables globales - disponibles inmediatamente
        const permisos =  @json($user);
        const dataPaises = @json($paises);

        // Código que usa jQuery - envuelto en document ready
        $(document).ready(function() {

        });
    </script>
    <script src="{{asset('assets/js/Terceros/clientes/quick-diagnosis.js') }}" type="text/javascript"></script>
    <script src="{{asset('assets/js/Terceros/clientes/bootstrap-4-6-compatibility.js') }}" type="text/javascript"></script>
    <script src="{{asset('assets/js/Terceros/clientes/clientes.js') }}" type="text/javascript"></script>
    <script src="{{asset('assets/js/Terceros/clientes/modal-steps-enhanced.js') }}" type="text/javascript"></script>
    <script src="{{asset('assets/js/Terceros/clientes/modal-testing.js') }}" type="text/javascript"></script>
    <script src="{{asset('assets/js/Terceros/clientes/modal-force.js') }}" type="text/javascript"></script>
@stop
