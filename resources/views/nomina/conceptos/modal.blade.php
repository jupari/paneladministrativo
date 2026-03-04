<!-- Modal -->
<div class="modal fade" id="ModalConcept" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="conceptModalTitle">Registrar Concepto</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form autocomplete="off">
                    <input type="hidden" id="id" value="">

                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-3">Información</legend>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="code">Código</label>
                                    <input type="text" id="code" class="form-control" placeholder="Ej: LAB_BASICO">
                                    <span class="text-danger" id="error_code"></span>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">Nombre</label>
                                    <input type="text" id="name" class="form-control" placeholder="Nombre del concepto">
                                    <span class="text-danger" id="error_name"></span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="scope">Scope</label>
                                    <select id="scope" class="form-control">
                                        <option value="">Seleccione...</option>
                                        <option value="LABORAL">LABORAL</option>
                                        <option value="CONTRATISTA">CONTRATISTA</option>
                                        <option value="AMBOS">AMBOS</option>
                                    </select>
                                    <span class="text-danger" id="error_scope"></span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="kind">Tipo</label>
                                    <select id="kind" class="form-control">
                                        <option value="">Seleccione...</option>
                                        <option value="DEVENGADO">DEVENGADO</option>
                                        <option value="DEDUCCION">DEDUCCIÓN</option>
                                        <option value="APORTE">APORTE</option>
                                        <option value="INFORMATIVO">INFORMATIVO</option>
                                    </select>
                                    <span class="text-danger" id="error_kind"></span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="calc_method">Método cálculo</label>
                                    <select id="calc_method" class="form-control">
                                        <option value="">Seleccione...</option>
                                        <option value="MANUAL">MANUAL</option>
                                        <option value="FORMULA">FÓRMULA</option>
                                        <option value="PORCENTAJE">PORCENTAJE</option>
                                        <option value="FIJO">FIJO</option>
                                    </select>
                                    <span class="text-danger" id="error_calc_method"></span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tax_nature">Naturaleza</label>
                                    <select id="tax_nature" class="form-control">
                                        <option value="">Seleccione...</option>
                                        <option value="SALARIAL">SALARIAL</option>
                                        <option value="NO_SALARIAL">NO SALARIAL</option>
                                        <option value="N_A">N/A</option>
                                    </select>
                                    <span class="text-danger" id="error_tax_nature"></span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="base_code">Base (opcional)</label>
                                    <input type="text" id="base_code" class="form-control" placeholder="IBC, HON_BASE...">
                                    <span class="text-danger" id="error_base_code"></span>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="priority">Prioridad</label>
                                    <input type="number" id="priority" class="form-control" value="100">
                                    <span class="text-danger" id="error_priority"></span>
                                </div>
                            </div>

                            <div class="col-md-2 pt-4">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="is_active" checked>
                                    <label class="form-check-label" for="is_active">Activo</label>
                                </div>
                                <span class="text-danger" id="error_is_active"></span>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>

            <div class="modal-footer">
                {{-- botones desde JS --}}
            </div>
        </div>
    </div>
</div>
