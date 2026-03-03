<div class="modal fade" id="modalDetalle" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="formDetalle">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetalleLabel">Agregar detalle</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="detalle_index" value="">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="detalle_nombre">Nombre</label>
                            <input type="text" name="detalle_nombre" id="detalle_nombre" class="form-control" maxlength="150" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="detalle_valor_admon">Valor admon</label>
                            <input type="number" name="detalle_valor_admon" id="detalle_valor_admon" class="form-control" min="0" step="0.01" placeholder="0.00">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="detalle_valor_operativo">Valor operativo</label>
                            <input type="number" name="detalle_valor_operativo" id="detalle_valor_operativo" class="form-control" min="0" step="0.01" placeholder="0.00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="saveDetalleFromModal()" id="btnDetalleSubmit">Agregar</button>
                </div>
            </div>
        </form>
    </div>
</div>
