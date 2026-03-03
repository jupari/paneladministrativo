@extends('adminlte::page')

@section('title', 'Permisos')

@section('plugin.Datatables')

@section('plugin.Toastr')

@section('plugin.Sweetalert2')

@section('content')
    @php
        $breadcrumbs = [
            [
                'title' => 'Parametrización de Seguridad',
                'icon' => 'fas fa-users-cog',
                'url' => null
            ]
        ];
        $currentTitle = 'Permisos';
        $currentIcon = 'fas fa-user-shield';
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />
    <div class="card">
        <div class="card-header">
            <h4>Permisos</h4>
        </div>
        <div class="card-body" >
            @if(auth()->user()->can('permission.create'))
                <div class="col-md-1">
                <button type="button" onclick="regPerm()" class="btn btn-primary btn-block mb-1" data-toggle="tooltip" data-placement="top" title="Crear Permiso">
                    <i class="fas fa-user-plus"></i>
                </button>
                </div>
            @endif
            <div class="col-md-12 my-3">
              <div class="table-responsive">
                  <table id="perm-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                           <th>#</th>
                           <th>Permiso</th>
                           <th>Descripción</th>
                           <th>Guard</th>
                           <th>Acciones</th>
                        </tr>
                     </thead>
                  </table>
              </div>
        </div>
    </div>
@stop

@include('admin.permission.modal')


@push('css')
   {{-- <link rel="stylesheet" href="{{asset('assets/AdminLTE-3.2.0/plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}"> --}}
@endpush

@push('js')
   <script src="{{asset('assets/js/permisos/permisos.js') }}" type="text/javascript"></script>
@endpush
