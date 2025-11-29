@extends('adminlte::page')

@section('title', 'Usuarios')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')


@section('content')

    <div class="card">
        <div class="card-header">
            <h4>Usuarios</h4>
        </div>
        <div class="card-body" >
            @if(auth()->user()->hasRole('Administrator'))
                <div class="col-md-1">
                <button type="button" onclick="regUsr()" class="btn btn-primary btn-block mb-1" data-toggle="tooltip" data-placement="top" title="Crear usuario">
                    <i class="fas fa-user-plus"></i>
                </button>
                </div>
            @endif
            <div class="col-md-12 my-3">
              <table id="user-table" class="table table-bordered table-striped">
                {{-- <thead>
                    <th>Id</th>
                    <th>Nombres</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Fecha de creación</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </thead>
                <tbody>
                    @foreach ($users as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->roles[0]->name }}</td>
                        <td>{{ $item->created_at }}</td>
                        <td>Activo</td>
                        <td><div class="d-flex flex-column  flex-md-row justify-content-md-around">
                                <button class="btn btn-primary"><i class="fas fa-eye fa-fw"></i></button>
                                <button class="btn btn-success"><i class="fas fa-edit fa-fw"></i></button>
                                <button class="btn btn-danger"><i class="fas fa-trash fa-fw" ></i> </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody> --}}
                <thead>
                    <tr>
                       <th>#</th>
                       <th>Nombre(s)</th>
                       <th>Correo electrónico</th>
                       <th>Identificación</th>
                       <th>Rol</th>
                       <th>Fecha creación</th>
                       <th class="text-center">Activo</th>
                       <th>Acciones</th>
                    </tr>
                 </thead>
              </table>
            </div>
        </div>
    </div>
@stop

@foreach(['modal', 'password'] as $view)
    @include("admin.users.$view")
@endforeach

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script src="{{asset('assets/js/usuario/usuario.js') }}" type="text/javascript"></script>
@stop
