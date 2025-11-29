<div class="modal fade" id="ModalMaterial" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Registrar Material</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form autocomplete="off">
                    @csrf
                    <input type="hidden" id="id" value="">
                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-3">Información</legend>
                        <div class="row">
                             <div class="col-md-4">
                                <label for="codigo">Código*</label>
                                <input type="text" id="codigo" class="form-control" placeholder="Código del material">
                                <span class="text-danger" id="error_codigo"></span>
                            </div>
                            <div class="col-md-4">
                                <label for="nombre">Nombre*</label>
                                <input type="text" id="nombre" class="form-control" placeholder="Nombre del material">
                                <span class="text-danger" id="error_nombre"></span>
                            </div>
                            <div class="col-md-4">
                                <label for="unidad_medida">Unidad Medida*</label>
                                <input type="text" id="unidad_medida" class="form-control" placeholder="Ej: m, kg, und">
                                <span class="text-danger" id="error_unidad_medida"></span>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check my-4">
                                    <input class="form-check-input" type="checkbox" id="active">
                                    <label for="active">Activo*</label>
                                </div>
                                <span class="text-danger" id="error_active"></span>
                            </div>
                            <div class="col-md-12 mt-3">
                                <label for="descripcion">Descripción</label>
                                <textarea id="descripcion" class="form-control" rows="3" placeholder="Descripción opcional"></textarea>
                                <span class="text-danger" id="error_descripcion"></span>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
