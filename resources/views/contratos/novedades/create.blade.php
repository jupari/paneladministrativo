@extends('adminlte::page')

@section('title', 'Novedades')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')


@section('content')

    <div class="card">
        <div class="card-header d-flex align-items-start justify-content-between flex-wrap">
            <div>
                <h4 class="mb-1">Registrar Novedad</h4>
                <small class="text-muted">Define el nombre, activa el estado y agrega los detalles antes de guardar.</small>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="{{ route('admin.novedad.index') }}" class="btn btn-outline-secondary btn-sm mr-2">Volver</a>
                <button type="button" class="btn btn-success btn-sm" onclick="saveData()">
                    <i class="fas fa-save mr-1"></i> Guardar novedad
                </button>
            </div>
        </div>
        <div class="card-body">
            <form id="formNovedad">
                <input type="hidden" name="novedad_id" id="novedad_id" >
                @csrf
                <div class="row">
                    <div class="col-lg-8 col-12">
                        <div class="form-group">
                            <label for="nombre" class="font-weight-semibold">Nombre de la novedad</label>
                            <input type="text" name="nombre" class="form-control" id="nombre" maxlength="150" placeholder="Ej. Cambio de horario especial" required>
                            <small class="text-muted">Sé específico para reconocerla luego.</small>
                            <span class="text-danger error-nombre"></span>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch mb-2">
                                <input class="custom-control-input" type="checkbox" id="active" name="active">
                                <label class="custom-control-label" for="active">Activo</label>
                            </div>
                            <div class="custom-control custom-switch">
                                <input class="custom-control-input" type="checkbox" id="grupo_cotiza" name="grupo_cotiza">
                                <label class="custom-control-label" for="grupo_cotiza">Grupo Cotiza</label>
                            </div>
                            <small class="text-muted">Si está inactiva, no estará disponible en listados.</small>
                            <span class="text-danger error_active"></span>
                            <span class="text-danger error-grupo_cotiza"></span>
                        </div>

                        <div class="d-flex align-items-center mb-2">
                            <h5 class="mb-0 mr-2">Detalles</h5>
                            <span class="badge badge-light">Opcional</span>
                        </div>
                        <p class="text-muted">Agrega líneas con valores administrativos y operativos para desglosar la novedad.</p>

                        <div class="mb-3">
                            <button type="button" class="btn btn-primary" onclick="openDetalleModal()">
                                <i class="fas fa-plus mr-1"></i> Agregar detalle
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table id="tabla-detalles" class="table table-bordered mb-2">
                                <thead>
                                    <tr>
                                        <th style="width: 60px">#</th>
                                        <th>Nombre</th>
                                        <th style="width: 140px">Valor admon</th>
                                        <th style="width: 140px">Valor operativo</th>
                                        <th style="width: 120px">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <small class="form-text text-muted">Si no agregas detalles, la novedad se guardará solo con el nombre.</small>
                        </div>
                        <span class="text-danger error-detalles"></span>

                        <div class="row mt-3">
                            <div class="col-md-4 mb-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="auto_totales" checked>
                                    <label class="custom-control-label" for="auto_totales">Calcular totales automático</label>
                                </div>
                                <small class="text-muted">Suma los valores administrativos y operativos de todos los detalles.</small>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="total_valor_admon" class="mb-1">Total valor admon</label>
                                <input type="number" step="0.01" class="form-control" id="total_valor_admon" name="total_valor_admon" readonly>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="total_valor_operativo" class="mb-1">Total valor operativo</label>
                                <input type="number" step="0.01" class="form-control" id="total_valor_operativo" name="total_valor_operativo" readonly>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <button type="button" class="btn btn-secondary mr-2" onclick="window.location='{{ route('admin.novedad.index') }}'">Volver</button>
                            {{-- <button type="button" class="btn btn-success" onclick="saveData()"><i class="fas fa-save mr-1"></i> Guardar novedad</button> --}}
                        </div>
                    </div>

                    <div class="col-lg-4 col-12 mt-4 mt-lg-0">
                        <div class="border rounded p-3 bg-light h-100">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge badge-primary mr-2">Tip</span>
                                <strong class="mb-0">Creación sin sorpresas</strong>
                            </div>
                            <ul class="pl-3 mb-0 text-muted">
                                <li>Usa un nombre claro para distinguirla rápido.</li>
                                <li>Activa solo si debe verse en los listados.</li>
                                <li>Agrega detalles para dividir tareas o notas.</li>
                                <li>Podrás editar la novedad más adelante.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop


@include('contratos.novedades.modal')

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script src="{{asset('assets/js/contratos/novedades/create.js') }}" type="text/javascript"></script>
@stop
