<!-- Modal Maestro -->
<div class="modal fade" id="modalMovimiento" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form id="formMovimiento">
            @csrf
            <input type="hidden" name="id" id="movimiento_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Movimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <input type="hidden" name="user_id" id="user_id" value="{{ auth()->user()->id }}">
                        <div class="col-md-4">
                            <label>Número Documento</label>
                            <input type="text" name="num_doc" id="num_doc" class="form-control">
                            <div class="text-danger" id="error_num_doc"></div>
                        </div>
                        <div class="col-md-4">
                            <label>Tipo</label>
                            <input type="text" name="tipo" id="tipo" class="form-control">
                            <div class="text-danger" id="error_tipo"></div>
                        </div>
                        <div class="col-md-4">
                            <label>Doc Ref</label>
                            <input type="text" name="doc_ref" id="doc_ref" class="form-control">
                            <div class="text-danger" id="error_doc_ref"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Observación</label>
                        <textarea name="observacion" id="observacion" class="form-control"></textarea>
                        <div class="text-danger" id="error_observacion"></div>
                    </div>
                    <div class="row my-1 px-1">
                        <div class="col-3">
                            <button type="submit" class="btn btn-success" id="btn-guardar-movimiento">Guardar</button>
                        </div>
                    </div>
                    <hr>
                    <h5>Detalles</h5>
                    <fieldset class="border col-12 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap my-2 px-2">
                            <button type="button" class="btn btn-primary mb-2" id="btnAddDetalle">
                                Nuevo Registro
                            </button>
                            <input type="text" id="buscar-detalles"
                                class="form-control w-auto mb-2"
                                placeholder="Buscar en detalles...">
                        </div>
                        <div class="col-md-12">
                            <div id="tabla-detalles"></div>
                        </div>
                    </fieldset>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </form>
    </div>
</div>
