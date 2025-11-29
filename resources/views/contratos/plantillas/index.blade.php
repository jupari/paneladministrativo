@extends('adminlte::page')

@section('title', 'Plantillas')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')


@section('content')

    <div class="card">
        <div class="card-header">
            <h4>Plantillas</h4>
        </div>
        <div class="card-body" >
            @if(auth()->user()->hasRole('Administrator'))
                <div class="col-md-1">
                <button type="button" onclick="regPlantilla()" class="btn btn-primary btn-block mb-1" data-toggle="tooltip" data-placement="top" title="Crear Plantilla">
                    <i class="fas fa-file"></i>
                </button>
                </div>
            @endif
            <div class="col-md-12 my-3">
              <div class="table-responsive">
                  <table id="plantillas-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                           <th>#</th>
                           <th>Id</th>
                           <th>Plantilla</th>
                           <th>Archivo</th>
                           <th>Campos</th>
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

@push('modals')
@include('contratos.plantillas.modal')
@endpush

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script>
        //const user_id = @json($user_id);
    </script>
    <script src="{{asset('assets/js/contratos/plantillas/plantillas.js') }}" type="text/javascript"></script>
    {{-- <script src="{{asset('assets/js/contratos/plantillas/plantillasEdit.js') }}" type="text/javascript"></script> --}}
@stop
