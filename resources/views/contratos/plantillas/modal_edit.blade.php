<!-- Modal -->
<div class="modal fade" id="ModalPlantillaEdit" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-xl"> <!-- Clase 'modal-xl' para hacer el modal más ancho -->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="exampleModalLabel">Registrar Empleado</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form autocomplete="off">
                    <input type="hidden" id="id" value="">
                    <input type="hidden" id="user_id" value="{{ $user_id }}">
                    <!-- Agrupación: Información Básica -->
                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-3">Información</legend>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="plantilla">Plantilla digite un nombre para la plantilla</label>
                                    <input type="text" id="plantilla" class="form-control" placeholder="Ingrese un nombre para la plantilla">
                                    <span class="text-danger" id="error_plantilla"></span>
                                </div>
                            </div>
                              <!-- Checkbox activo -->
                              <div class="col-md-2">
                                <div class="form-check mt-5">
                                    <input class="form-check-input" type="checkbox" id="active">
                                    <label class="form-check-label" for="active">Activo</label>
                                    <span class="text-danger d-block" id="error_active"></span>
                                </div>
                            </div>
                            <!-- Campo: Archivo -->
                            {{-- <div class="mb-3">
                                <label for="archivo" class="form-label">Archivo (solo .docx)</label>
                                <input type="file" name="archivo" id="archivo" class="form-control" accept=".docx" required>
                                <input type="text" id="nombre_archivo" class="form-control my-2" readonly>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="active">
                                        <label for="active">Activo</label>
                                    </div>
                                    <span class="text-danger" id="error_active"></span>
                                </div>
                            </div>
                            <button type="button" id="save-file" class="btn btn-success" onclick="registerPlantilla()">Guardar Archivo</button> --}}
                            <div class="row align-items-end gx-3">
                                <!-- Subir archivo -->
                                <div class="col-12 col-md-10">
                                    <label for="archivo" class="form-label">Archivo (.docx)</label>
                                    <input type="file" name="archivo" id="archivo" class="form-control" accept=".docx" required>
                                </div>
                                <!-- Botón guardar -->
                                <div class="col-md-2 text-end">
                                    <button type="button" id="save-file" class="btn btn-success mt-4" onclick="registerPlantilla()">Guardar Archivo</button>
                                </div>
                            </div>
                            <div class="row">
                                 <!-- Nombre del archivo -->
                                 <div class="col-md-12">
                                    <label for="nombre_archivo" class="form-label">Nombre del archivo</label>
                                    <input type="text" id="nombre_archivo" class="form-control" readonly>
                                </div>
                            </div>
                            <input type="hidden" id="plantillaId" value="0">
                            <!-- Campo: Campos dinámicos -->
                            {{-- <div class="mb-3">
                                <label for="campos" class="form-label">Campos Dinámicos</label>
                                <div id="campos-container">
                                    {{-- <div class="input-group mb-2">
                                        <input type="text" name="campos[]" class="form-control" placeholder="Ingrese un campo dinámico">
                                        <button type="button" class="btn btn-danger btn-remove-campo"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                    <span class="text-danger" id="error_campos"></span>
                                </div>
                                <button type="button" id="add-campo" class="btn btn-secondary">Agregar Campo</button>
                            </div> --}}
                        </div>
                    </fieldset>
                </form>
                <div id="mapping" class="my-2">

                </div>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
