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
    {{-- Font Awesome para iconos si no está incluido --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    {{-- Estilos de empresa --}}
    <x-company-styles />

    <style>
        {{-- Estilos específicos adicionales para esta vista --}}
        .card-header {
            background: linear-gradient(135deg, var(--company-primary, #007bff), rgba(var(--company-primary-rgb, 0, 123, 255), 0.85)) !important;
            color: white !important;
            border-bottom: none !important;
            box-shadow: 0 2px 4px rgba(var(--company-primary-rgb, 0, 123, 255), 0.2) !important;
        }

        .card-header h4 {
            color: white !important;
            font-weight: 600 !important;
            margin: 0 !important;
            font-size: 1.1rem !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1) !important;
        }

        .btn-primary {
            background-color: var(--company-primary, #007bff) !important;
            border-color: var(--company-primary, #007bff) !important;
            transition: all 0.3s ease !important;
        }

        .btn-primary:hover {
            background-color: color-mix(in srgb, var(--company-primary, #007bff) 85%, black) !important;
            border-color: color-mix(in srgb, var(--company-primary, #007bff) 85%, black) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 8px rgba(var(--company-primary-rgb, 0, 123, 255), 0.3) !important;
        }
    </style>
@stop

@section('js')
    <script>
        // Variables globales - disponibles inmediatamente
        const permisos =  @json($user);
        const dataPaises = @json($paises);
        console.log('dataPaises', dataPaises);

        // Código que usa jQuery - envuelto en document ready
        $(document).ready(function() {
            console.log('📋 Variables globales inicializadas:', {
                permisos: permisos,
                dataPaises: dataPaises
            });
        });
    </script>
    <script src="{{asset('assets/js/Terceros/clientes/quick-diagnosis.js') }}" type="text/javascript"></script>
    <script src="{{asset('assets/js/Terceros/clientes/bootstrap-4-6-compatibility.js') }}" type="text/javascript"></script>
    <script src="{{asset('assets/js/Terceros/clientes/clientes.js') }}" type="text/javascript"></script>
    <script src="{{asset('assets/js/Terceros/clientes/modal-steps-enhanced.js') }}" type="text/javascript"></script>
    <script src="{{asset('assets/js/Terceros/clientes/modal-testing.js') }}" type="text/javascript"></script>
    <script src="{{asset('assets/js/Terceros/clientes/modal-force.js') }}" type="text/javascript"></script>
@stop
