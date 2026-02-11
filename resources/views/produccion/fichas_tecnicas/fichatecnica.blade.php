@extends('adminlte::page')

@section('title', 'Fichas Técnicas - Elaboración')

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
            <a href="#">Producción</a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('admin.fichas-tecnicas.index') }}">Fichas Técnicas</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            Elaboración Ficha Técnica
        </li>
    </ol>
</nav>

<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Fichas Técnicas - Elaboración</h4>
    </div>

    <div class="card-body">
        {{-- <input type="hidden" name="fichatecnica_id" value="{{ $ficha->id }}"> --}}
        {{-- Nav tabs --}}
        <ul class="nav nav-tabs" id="fichaTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="general-tab" data-toggle="tab" data-target="#general"
                        type="button" role="tab" aria-controls="general" aria-selected="true">
                    Información General
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="bocetos-tab" data-toggle="tab" data-target="#bocetos"
                        type="button" role="tab" aria-controls="bocetos" aria-selected="false">
                    Bocetos
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="materiales-tab" data-toggle="tab" data-target="#materiales"
                        type="button" role="tab" aria-controls="materiales" aria-selected="false">
                    Materiales
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="procesos-tab" data-toggle="tab" data-target="#procesos"
                        type="button" role="tab" aria-controls="procesos" aria-selected="false">
                    Procesos
                </button>
            </li>
        </ul>

        {{-- Tab content --}}
        <div class="tab-content mt-3" id="fichaTabsContent">
            {{-- Información General --}}
            <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                <input type="hidden" id="fichatecnica_id" value="{{ $fichaTecnica[0]->id??''}}">
                <div class="row">
                    <div class="col-12 col-md-3 mb-3">
                        <label for="codigo" class="form-label">Código*</label>
                        <input type="text" class="form-control text-uppercase" name="codigo" id="codigo" value="{{ $fichaTecnica[0]->codigo??''}}" oninput="this.value = this.value.toUpperCase()">
                        <span class="text-danger" id="error_codigo"></span>
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label for="nombre" class="form-label">Nombre*</label>
                        <input type="text" class="form-control" name="nombre" id="nombre" value="{{ $fichaTecnica[0]->nombre??''}}" oninput="this.value = this.value.toUpperCase()">
                        <span class="text-danger" id="error_nombre"></span>
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <label for="nombre" class="form-label">Producto terminado</label>
                        <input type="text" class="form-control" name="nombre" id="nombre" value="{{ $fichaTecnica[0]->codigo_producto_terminado??''}}" oninput="this.value = this.value.toUpperCase()">
                        <span class="text-danger" id="error_nombre"></span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12 col-md-6">
                        <label for="coleccion" class="form-label">Colección</label>
                        <input type="text" class="form-control" name="coleccion" id="coleccion" value="{{ $fichaTecnica[0]->coleccion??''}}" oninput="this.value = this.value.toUpperCase()">
                        <span class="text-danger" id="error_coleccion"></span>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="fecha" class="form-label">Fecha</label>
                        <input type="date" class="form-control" name="fecha" id="fecha" value="{{ isset($fichaTecnica[0]->fecha) ? \Carbon\Carbon::parse($fichaTecnica[0]->fecha)->format('Y-m-d') : '' }}">
                        <span class="text-danger" id="error_fecha"></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-12">
                        <label for="observacion" class="form-label">Observación</label>
                        <textarea class="form-control" name="observacion" rows="3" id="observacion"  oninput="this.value = this.value.toUpperCase()">{{ $fichaTecnica[0]->observacion??''}}</textarea>
                        <span class="text-danger" id="error_observacion"></span>
                    </div>
                </div>
                <div class="row my-3">
                    <div class="col-12 col-md-3">
                        <button type="button" class="btn btn-success" id="guardar-ficha-tecnica">Guardar Ficha Técnica</button>
                    </div>
                </div>
            </div>

            {{-- Bocetos --}}
            <div class="tab-pane fade" id="bocetos" role="tabpanel" aria-labelledby="bocetos-tab">
                <form method="POST" enctype="multipart/form-data">
                    @csrf
                    <div id="bocetos-container">
                        {{-- Si hay bocetos guardados --}}
                        @if($fichaTecnica)
                            @if($fichaTecnica[0]->bocetos->count())
                            @foreach($fichaTecnica[0]->bocetos as $boceto)
                                <input type="hidden" name="fichatecnicaboceto_id[]" value="{{ $boceto->id }}">
                                <input type="hidden" name="fichatecnica_id[]" value="{{ $boceto->fichatecnica_id }}">
                                <input type="hidden" name="codigo[]" value="{{ $boceto->codigo }}">
                                <div class="boceto-item border p-3 mb-3 rounded">
                                    <div class="mb-3">
                                        <label class="form-label">Nombre del Boceto</label>
                                        <input type="text" class="form-control" name="nombre[]" value="{{ $boceto->nombre }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Observación</label>
                                        <textarea type="text" class="form-control" name="observacion[]">{{ $boceto->observacion }}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Archivo actual</label>
                                        <div class="preview mb-2">
                                            <img src="{{ asset('storage/'.$boceto->archivo) }}" class="img-thumbnail" style="max-height:150px;">
                                        </div>
                                        <label class="form-label">Actualizar archivo (opcional)</label>
                                        <input type="file" class="form-control boceto-file" name="archivo[]">
                                    </div>
                                    <button type="button" class="btn btn-danger btn-sm remove-boceto" onclick="deleteImagen({{ $boceto->id }})">Eliminar</button>
                                </div>
                            @endforeach
                            @else
                            {{-- Si no hay bocetos aún --}}
                            <div class="boceto-item border p-3 mb-3 rounded">
                                <div class="mb-3">
                                    <input type="hidden" name="fichatecnicaboceto_id[]" value="0">
                                    <input type="hidden" name="fichatecnica_id[]" value="{{ $fichaTecnica[0]->id??''}}">
                                    <input type="hidden" name="codigo[]" value="{{ $fichaTecnica[0]->codigo ??''}}">
                                    <label class="form-label">Nombre del Boceto</label>
                                    <input type="text" class="form-control" name="nombre[]" placeholder="Nombre">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Observación</label>
                                    <textarea type="text" class="form-control" name="observacion[]"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Archivo</label>
                                    <input type="file" class="form-control boceto-file" name="archivo[]">
                                    <div class="preview mt-2"></div>
                                </div>
                                <button type="button" class="btn btn-danger btn-sm remove-boceto">Eliminar</button>
                            </div>
                            @endif
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <button type="button" id="add-boceto" class="btn btn-outline-primary btn-sm mb-3">
                                + Agregar otro boceto
                            </button>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary" id="guardar-ficha-tecnica-boceto">Guardar Bocetos</button>
                        </div>
                    </div>
                </form>
            </div>
            {{-- Materiales --}}
            <div class="tab-pane fade" id="materiales" role="tabpanel" aria-labelledby="materiales-tab">
                <button id="add-material" class="btn btn-success btn-sm mb-3">
                    <i class="fas fa-plus"></i> Agregar Material
                </button>
                <table id="materiales-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Referencia Material</th>
                            <th>Unidad de Medida</th>
                            <th>Propiedad 1</th>
                            <th>Propiedad 2</th>
                            <th>Cantidad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>

            {{-- Procesos --}}
            <div class="tab-pane fade" id="procesos" role="tabpanel" aria-labelledby="procesos-tab">
                <button id="add-proceso" class="btn btn-success btn-sm mb-3">
                    <i class="fas fa-plus"></i> Agregar Proceso
                </button>
                <table id="procesos-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Código proceso</th>
                            <th>Observación</th>
                            <th>Costo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@stop


{{-- @include('contratos.cargos.modal') --}}

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script>
        const statusFT = @json($statusFT);
    </script>
    <script src="{{asset('assets/js/produccion/fichatecnica/fichatecnica.js') }}" type="text/javascript"></script>
    <script src="{{asset('assets/js/produccion/fichatecnica/fichatecnicadet.js') }}" type="text/javascript"></script>
    <script src="{{asset('assets/js/produccion/fichatecnica/fichatecnicamateriales.js') }}" type="text/javascript"></script>
    <script src="{{asset('assets/js/produccion/fichatecnica/fichatecnicaprocesos.js') }}" type="text/javascript"></script>
@stop
