<!-- Modal -->
<!-- Modal -->
<div class="modal fade" id="ModalGenerarContrato" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-xl"> <!-- Clase 'modal-xl' para hacer el modal más ancho -->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="exampleModalLabel">Registrar Empleado</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="id" value="">
                <input type="hidden" id="user_id" value="{{ $user_id }}">
                <!-- Agrupación: Información Básica -->
                <fieldset class="border p-3 mb-4">
                    <legend class="w-auto px-3">Información Empleado</legend>
                    <div class="col-md-12 d-block d-sm-flex d-md-flex">
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label for="nombres">Nombres</label>
                                <input type="text" id="nombres" class="form-control" placeholder="Ingrese los nombres">
                                <span class="text-danger" id="error_nombres"></span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label for="apellidos">Apellidos</label>
                                <input type="text" id="apellidos" class="form-control" placeholder="Ingrese los apellidos">
                                <span class="text-danger" id="error_apellidos"></span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label for="identificacion">Identificación</label>
                                <input type="text" id="identificacion" class="form-control" placeholder="Número de identificación">
                                <span class="text-danger" id="error_identificacion"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 d-block d-sm-flex d-md-flex">
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label for="expedida_en">Expedida en</label>
                                <input type="text" id="expedida_en" class="form-control" placeholder="Lugar de expedición">
                                <span class="text-danger" id="error_expedida_en"></span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                                <input type="date" id="fecha_nacimiento" class="form-control">
                                <span class="text-danger" id="error_fecha_nacimiento"></span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label for="fecha_inicio_labor">Fecha de Inicio Laboral</label>
                                <input type="date" id="fecha_inicio_labor" class="form-control">
                                <span class="text-danger" id="error_fecha_inicio_labor"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 d-block d-sm-flex d-md-flex">
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label for="direccion">Dirección</label>
                                <input type="text" id="direccion" class="form-control" placeholder="Ingrese la dirección">
                                <span class="text-danger" id="error_direccion"></span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label for="cargo_id">Cargo</label>
                                <select id="cargo_id" class="form-control">
                                    <option value="" selected>Seleccione...</option>
                                    @foreach($cargos as $cargo)
                                        <option value="{{ $cargo->id }}">{{ $cargo->nombre }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger" id="error_cargo_id"></span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label for="salario">Salario</label>
                                <input type="number" id="salario" class="form-control" placeholder="Ingrese el salario">
                                <span class="text-danger" id="error_salario"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 d-block d-sm-flex d-md-flex">
                        <div class="col-12 col-sm-3 col-md-3">
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="text" id="telefono" class="form-control" placeholder="Ingrese la teléfono">
                                <span class="text-danger" id="error_telefono"></span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-3 col-md-3">
                            <div class="form-group">
                                <label for="celular">Celular</label>
                                <input type="text" id="celular" class="form-control" placeholder="Ingrese la celular">
                                <span class="text-danger" id="error_celular"></span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-3 col-md-3">
                            <div class="form-group">
                                <label for="correo">Correo Electrónico</label>
                                <input type="text" id="correo" class="form-control" placeholder="Ingrese la correo">
                                <span class="text-danger" id="error_celular"></span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-3 col-md-3">
                            <div class="form-group">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="active">
                                    <label for="active">Activo</label>
                                </div>
                                <span class="text-danger" id="error_active"></span>
                            </div>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="border p-3 mb-4">
                    <legend class="w-auto px-3">Seleccionar Plantilla</legend>
                    <div class="col-12">
                        <div class="form-group">
                            <label for="nombres">Seleccionar Plantilla</label>
                            <select  id="plantilla-select" class="form-select">
                                <option value="">Seleccione</option>
                                @foreach($plantillas as $plantilla)
                                    <option value="{{ $plantilla->id }}">{{ $plantilla->plantilla }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger" id="error_plantilla-select"></span>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="border p-3 mb-4">
                    <legend class="w-auto px-3">Digitar Campos Manuales</legend>
                    <div class="col-12" id="campos-manuales">
                    </div>
                </fieldset>
                <fieldset class="border p-3 mb-4">
                    <legend class="w-auto px-3">Descargar de PDF</legend>
                    <div class="col-12" id="descargar-archivos">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Archivo</th>
                                        <th>Link</th>
                                    </tr>
                                </thead>
                                <tbody id="body-descargas">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
