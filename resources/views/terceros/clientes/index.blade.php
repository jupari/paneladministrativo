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
    {{-- Extensión Responsive de DataTables (columnas que se ocultan/expanden en móvil) --}}
    <link rel="stylesheet" href="//cdn.datatables.net/responsive/3.0.8/css/responsive.bootstrap5.min.css">

    {{-- Estilos de empresa --}}
    <x-company-styles />

    <style>
        /* Indicador de "Procesando..." más visible mientras el datatable carga/filtra */
        #clientes-table_processing {
            padding: 10px 20px !important;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
            font-weight: 600;
        }

        /* Espaciado de los badges de Tipo de persona */
        #clientes-table .badge {
            font-size: 85%;
            font-weight: 600;
            padding: 5px 10px;
        }

        /* Botón de colvis junto al de excel */
        #clientes-table_wrapper .dt-buttons .btn + .btn {
            margin-left: 4px;
        }
    </style>
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
    {{-- Extensión Responsive de DataTables y botón de visibilidad de columnas (colvis) --}}
    <script src="//cdn.datatables.net/responsive/3.0.8/js/dataTables.responsive.min.js" type="text/javascript"></script>
    <script src="//cdn.datatables.net/responsive/3.0.8/js/responsive.bootstrap5.min.js" type="text/javascript"></script>
    <script src="//cdn.datatables.net/buttons/3.1.1/js/buttons.colVis.min.js" type="text/javascript"></script>
    <script src="{{asset('assets/js/Terceros/clientes/quick-diagnosis.js') }}" type="text/javascript"></script>
    <script src="{{asset('assets/js/Terceros/clientes/bootstrap-4-6-compatibility.js') }}" type="text/javascript"></script>
    <script src="{{asset('assets/js/Terceros/clientes/clientes.js') }}" type="text/javascript"></script>
    <script src="{{asset('assets/js/Terceros/clientes/modal-steps-enhanced.js') }}" type="text/javascript"></script>
    <script src="{{asset('assets/js/Terceros/clientes/modal-testing.js') }}" type="text/javascript"></script>
    <script src="{{asset('assets/js/Terceros/clientes/modal-force.js') }}" type="text/javascript"></script>
@stop
