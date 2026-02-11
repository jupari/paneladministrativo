<!-- Modal de Categorías -->
<div class="modal fade" id="ModalCargo" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="exampleModalLabel">Registrar Categoría</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form autocomplete="off">
                    <input type="hidden" id="id" value="">
                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-3">Categoría</legend>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nombre">Nombre</label>
                                    <input type="text" id="nombre" class="form-control" placeholder="Ingrese el nombre de la categoría">
                                </div>
                                <span class="text-danger" id="error_nombre"></span>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="active" checked>
                                        <label class="form-check-label" for="active">Activo</label>
                                    </div>
                                    <span class="text-danger" id="error_active"></span>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="guardarCategoria()">Guardar</button>
            </div>
        </div>
    </div>
</div>
