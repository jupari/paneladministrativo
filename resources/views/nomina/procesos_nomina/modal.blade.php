<!-- Modal -->
<div class="modal fade" id="ModalPayRun" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="payrunModalTitle">Registrar Periodo</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form autocomplete="off">
                    <input type="hidden" id="id" value="">

                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-3">Periodo</legend>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="run_type">Tipo de nómina</label>
                                    <select id="run_type" class="form-control">
                                        <option value="">Seleccione...</option>
                                        <option value="NOMINA">Nómina (Laboral)</option>
                                        <option value="CONTRATISTAS">Contratistas</option>
                                        <option value="MIXTO">Mixto</option>
                                    </select>
                                    <span class="text-danger" id="error_run_type"></span>
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

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="pay_date">Fecha pago</label>
                                    <input type="date" id="pay_date" class="form-control">
                                    <span class="text-danger" id="error_pay_date"></span>
                                </div>
                            </div>

                            <div class="col-md-4 pt-4">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="include_laboral" checked>
                                    <label class="form-check-label" for="include_laboral">Incluir empleados (LABORAL)</label>
                                </div>
                                <span class="text-danger" id="error_include_laboral"></span>
                            </div>

                            <div class="col-md-4 pt-4">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="include_contratistas" checked>
                                    <label class="form-check-label" for="include_contratistas">Incluir contratistas (Tercero tipo 4)</label>
                                </div>
                                <span class="text-danger" id="error_include_contratistas"></span>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes">Observaciones</label>
                                    <textarea id="notes" rows="3" class="form-control" placeholder="Notas del periodo..."></textarea>
                                    <span class="text-danger" id="error_notes"></span>
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
