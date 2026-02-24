<!-- Modal -->
<div class="modal fade" id="ModalNovelty" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="noveltyModalTitle">Registrar Novedad</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form autocomplete="off">
                    <input type="hidden" id="id" value="">

                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-3">Empleados</legend>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label for="link_type">Tipo de vínculo</label>
                                    <select id="link_type" class="form-control">
                                        <option value="">Seleccione...</option>
                                        <option value="LABORAL">Laboral (empleados)</option>
                                        <option value="CONTRATISTA">Contratista</option>
                                    </select>
                                    <small class="text-muted">Usa el vínculo para cargar el listado correcto.</small>
                                    <span class="text-danger" id="error_link_type"></span>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="form-group mb-0">
                                    <label for="employee_ids">Selecciona uno o varios</label>
                                    <select id="employee_ids" class="form-control" multiple></select>
                                    <small class="text-muted">Búsca por nombre o identificación. Puedes marcar varios.</small>
                                    <span class="text-danger" id="error_employee_ids"></span>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-3">Concepto y valores</legend>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nomina_concept_id">Concepto</label>
                                    <select id="nomina_concept_id" class="form-control">
                                        <option value="">Cargando...</option>
                                    </select>
                                    <span class="text-danger" id="error_nomina_concept_id"></span>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="quantity">Cantidad (opcional)</label>
                                    <input type="number" step="0.0001" id="quantity" class="form-control" placeholder="Ej: 10">
                                    <span class="text-danger" id="error_quantity"></span>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="amount">Valor</label>
                                    <input type="number" step="0.01" id="amount" class="form-control" placeholder="Ej: 150000">
                                    <span class="text-danger" id="error_amount"></span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="period_start">Inicio periodo</label>
                                    <input type="date" id="period_start" class="form-control">
                                    <span class="text-danger" id="error_period_start"></span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="period_end">Fin periodo</label>
                                    <input type="date" id="period_end" class="form-control">
                                    <span class="text-danger" id="error_period_end"></span>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Descripción</label>
                                    <input type="text" id="description" class="form-control" placeholder="Soporte / detalle de la novedad">
                                    <span class="text-danger" id="error_description"></span>
                                </div>
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
