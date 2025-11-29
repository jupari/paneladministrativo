<!-- Modal -->
<div class="modal fade" id="ModalVendedor" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-xl"> <!-- Clase 'modal-xl' para hacer el modal más ancho -->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="exampleModalLabel">Registrar Vendedor</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form autocomplete="off">
                    <!-- Agrupación: Identificación -->
                    <input type="hidden" id="id" value="">
                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-3">Información</legend>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="identificacion">Identificación*</label>
                                        <input type="text" id="identificacion" class="form-control" placeholder="Número de identificación">
                                    </div>
                                    <span class="text-danger" id="error_identificacion"></span>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="nombre_completo">Nombres*</label>
                                        <input type="text" id="nombre_completo" class="form-control" placeholder="Ingrese el nombre completo">
                                    </div>
                                    <span class="text-danger" id="error_nombre_completo"></span>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="form-check d-flex-row">
                                            <div class="my-4 py-2">
                                                <input class="form-check-input" type="checkbox" id="active">
                                                <label for="active">Activo*</label>
                                            </div>
                                        </div>
                                        <span class="text-danger" id="error_active"></span>
                                    </div>
                                </div>
                            </div>
                    </fieldset>
                </form>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
