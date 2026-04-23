<!-- Modal Conceptos -->
<div class="modal fade" id="modalConcepto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalConceptoLabel"></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formConcepto" autocomplete="off">

                    <div class="form-group">
                        <label for="concepto_nombre">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="concepto_nombre" placeholder="Nombre del concepto" maxlength="100">
                        <span class="text-danger small" id="error_concepto_nombre"></span>
                    </div>

                    <div class="form-group">
                        <label for="concepto_tipo">Tipo</label>
                        <select class="form-control" id="concepto_tipo">
                            <option value="">-- Sin tipo --</option>
                            <option value="DESCUENTO">Descuento</option>
                            <option value="IMPUESTO">Impuesto / IVA</option>
                            <option value="RETENCION">Retención</option>
                        </select>
                        <span class="text-danger small" id="error_concepto_tipo"></span>
                    </div>

                    <div class="form-group">
                        <label for="concepto_porcentaje">Porcentaje por defecto (%)</label>
                        <input type="number" class="form-control" id="concepto_porcentaje" placeholder="Ej: 19.00" min="0" max="100" step="0.01">
                        <span class="text-danger small" id="error_concepto_porcentaje"></span>
                    </div>

                </form>
            </div>
            <div class="modal-footer" id="modalConceptoFooter">
            </div>
        </div>
    </div>
</div>
