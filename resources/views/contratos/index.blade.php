@extends('adminlte::page')

@section('title', 'Contratos')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')


@section('content')

    <div class="card">
        <div class="card-header">
            <h4>Contratos</h4>
        </div>
        <div class="card-body" >
            @if(auth()->user()->hasRole('Administrator'))
                <div class="col-md-3">
                <button type="button" onclick="openModalGenerarContratosEmpleados()" class="btn btn-primary btn-block mb-1" data-toggle="tooltip" data-placement="top" title="Generar Contratos">
                    <i class="fas fa-file"></i>Generar Contratos
                </button>
                </div>
            @endif
            <div class="col-md-12 my-3">
              <div class="table-responsive">
                  <table id="contratos-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll">
                            </th>
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
                            <th class="d-none">Acciones</th>
                         </tr>
                     </thead>
                  </table>
                {{-- <x-app::data-table
                    id="contratos-table"
                    :columns="[
                        ['data' => null, 'render' => 'function (data, type, row) { return `<input type="checkbox" class="employee-select" value="${row.id}">`; }', 'orderable' => false, 'searchable' => false],
                        ['data' => 'id', 'name' => 'id', 'title' => 'ID'],
                        ['data' => 'nombres_completos', 'name' => 'nombres_completos', 'title' => 'Nombres Completos'],
                        ['data' => 'identificacion', 'name' => 'identificacion', 'title' => 'Identificación'],
                        ['data' => 'expedida_en', 'name' => 'expedida_en', 'title' => 'Expedida En'],
                        ['data' => 'fecha_nacimiento', 'name' => 'fecha_nacimiento', 'title' => 'Fecha Nacimiento'],
                        ['data' => 'fecha_inicio_labor', 'name' => 'fecha_inicio_labor', 'title' => 'Fecha Inicio'],
                        ['data' => 'direccion', 'name' => 'direccion', 'title' => 'Dirección'],
                        ['data' => 'cargo', 'name' => 'cargo', 'title' => 'Cargo'],
                        ['data' => 'active', 'name' => 'active', 'title' => 'Activo', 'className' => 'text-center'],
                        ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Creado En'],
                        ['data' => 'acciones', 'name' => 'acciones', 'title' => 'Acciones', 'className' => 'exclude']
                    ]"
                    :ajaxUrl="route('admin.contratos.index')"
                    :buttons="[
                        {
                            extend: 'excel',
                            className: 'btn btn-success',
                            exportOptions: {
                                columns: ':not(.exclude)'
                            },
                            text: '<i class="far fa-file-excel"></i>',
                            titleAttr: 'Exportar a Excel',
                            filename: 'reporte_excel'
                        }
                    ]"
                    :customOptions="[
                        'columnDefs' => [
                            { 'targets': 1, 'visible': false }
                        ],
                        'order' => [[1, 'asc']],
                        'pageLength' => 10,
                        'lengthMenu' => [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, 'Todo(s)']]
                    ]"
                /> --}}
              </div>
            </div>
        </div>
    </div>
@stop

@push('modals')
@include('contratos.modal-generar')
@include('contratos.modal-generar-contratos')
@include('utilities.load')
@endpush
@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
 @stop

@section('js')
    <script src="{{asset('assets/js/utilities/load.js') }}" type="text/javascript">
        //const user_id = @json($user_id);
    </script>
    <script src="{{asset('assets/js/contratos/contratos.js') }}" type="text/javascript"></script>
@stop
