<!-- Modal -->
<div class="modal fade" id="ModalSaldoProducto" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="producto_id">
                <div class="row">
                    <div class="form-group col-12 col-md-4">
                        <label class="form-label">Código*</label>
                        <input type="text" class="form-control" name="codigo" id="codigo">
                        <span class="text-danger" id="error_codigo"></span>
                    </div>
                    <div class="form-group col-12 col-md-8">
                        <label class="form-label">Bodega*</label>
                        <input type="text" class="form-control" name="bodega" id="bodega">
                        <span class="text-danger" id="error_bodega"></span>
                    </div>
                </div>
                <fieldset class="border col-12 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap my-2 px-2">
                        <input type="text" id="buscar-detalles"
                            class="form-control w-auto mb-2"
                            placeholder="Buscar en detalles...">
                    </div>
                    <div id="saldosproductos-table"></div>
                </fieldset>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

