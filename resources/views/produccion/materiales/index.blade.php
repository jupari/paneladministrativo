@extends('adminlte::page')

@section('title', 'Materiales')

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
                <a href="{{ route('admin.materiales.index') }}">Materiales</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Materiales
            </li>
        </ol>
    </nav>

   <div class="card">
    <div class="card-header">
        <h4>Materiales</h4>
    </div>
    <div class="card-body">
        @if(auth()->user()->hasRole('Administrator'))
            <div class="col-md-1">
                <button type="button" onclick="regMaterial()" class="btn btn-primary btn-block mb-1" title="Crear Material">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        @endif
        <div class="col-md-12 my-3">
            <div class="table-responsive">
                <table id="materiales-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                           <th>#</th>
                           <th>id</th>
                           <th>Código</th>
                           <th>Nombre</th>
                           <th>Descripción</th>
                           <th>Unidad Medida</th>
                           <th>Estado</th>
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
    @include('produccion.materiales.modal')
@endpush

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script src="{{asset('assets/js/produccion/materiales/materiales.js') }}" type="text/javascript"></script>
@stop
