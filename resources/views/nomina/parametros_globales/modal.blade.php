<!-- Modal Parámetros Globales de Nómina -->
<div class="modal fade" id="ModalParametros" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header" style="background:linear-gradient(135deg,#1e3c72 0%,#2a5298 100%);">
                <div class="d-flex align-items-center">
                    <div class="bg-white rounded-circle p-2 mr-3 shadow-sm">
                        <i class="fas fa-sliders-h text-primary" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0 font-weight-bold text-white" id="modalParametrosLabel">Parámetros Globales</h5>
                        <small class="text-white" style="opacity:.75;">Valores macroeconómicos anuales del Motor de Nómina</small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-4">
                <form autocomplete="off">
                    <input type="hidden" id="param_id" value="">

                    <!-- ── SECCIÓN 1: Identificación ── -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-calendar-alt text-primary mr-2"></i>
                            <span class="font-weight-bold text-uppercase text-secondary"
                                  style="font-size:.75rem; letter-spacing:.08em;">Identificación</span>
                            <hr class="flex-grow-1 ml-2 mt-0 mb-0">
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-1">
                                    <label for="param_vigencia" class="font-weight-bold mb-1">
                                        Año fiscal <span class="text-danger">*</span>
                                        <i class="fas fa-question-circle text-muted ml-1"
                                           data-toggle="tooltip" data-placement="top"
                                           title="Año al que aplican estos parámetros. Cada año debe tener su propio registro."></i>
                                    </label>
                                    <input type="number" id="param_vigencia" class="form-control"
                                           placeholder="Ej: 2025" min="2020" max="2050">
                                    <small class="text-danger d-block" id="error_vigencia"></small>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-center pt-3">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="param_active" checked>
                                    <label class="custom-control-label font-weight-bold" for="param_active">
                                        Registro activo
                                    </label>
                                </div>
                                <small class="text-danger ml-2" id="error_active"></small>
                            </div>
                            <div class="col-md-4 d-flex align-items-center pt-3">
                                <div class="alert py-1 px-2 mb-0 w-100"
                                     style="background:#f3f4f6; border:1px solid #d1d5db; border-radius:4px; font-size:.75rem;">
                                    <i class="fas fa-info-circle text-info mr-1"></i>
                                    Solo un registro por año. El motor usa el año activo; si no existe, toma el más reciente.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── SECCIÓN 2: Salario Mínimo ── -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-dollar-sign text-success mr-2"></i>
                            <span class="font-weight-bold text-uppercase text-secondary"
                                  style="font-size:.75rem; letter-spacing:.08em;">Salario Mínimo Legal Vigente</span>
                            <hr class="flex-grow-1 ml-2 mt-0 mb-0">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-1">
                                    <label for="param_smlv" class="font-weight-bold mb-1 d-flex align-items-center">
                                        SMLV mensual <span class="text-danger ml-1">*</span>
                                        <i class="fas fa-question-circle text-muted ml-1"
                                           data-toggle="tooltip" data-placement="top"
                                           title="Salario Mínimo Legal Vigente mensual. Se usa como base cuando un cargo no tiene salario_base configurado, y para calcular topes de ARL, exoneración y auxilio de transporte."></i>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light text-muted" style="font-size:.85rem;">$</span>
                                        </div>
                                        <input type="number" id="param_smlv" class="form-control"
                                               placeholder="Ej: 1423500" min="0" step="100">
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        2025: $1,423,500 · 2026: ~$1,500,000
                                    </small>
                                    <small class="text-danger d-block" id="error_smlv"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-1">
                                    <label for="param_aux_transporte" class="font-weight-bold mb-1 d-flex align-items-center">
                                        Auxilio de transporte
                                        <i class="fas fa-question-circle text-muted ml-1"
                                           data-toggle="tooltip" data-placement="top"
                                           title="Solo aplica a trabajadores con salario básico ≤ 2 SMLV. No constituye salario para efectos de seguridad social, pero sí para liquidación de prestaciones."></i>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light text-muted" style="font-size:.85rem;">$</span>
                                        </div>
                                        <input type="number" id="param_aux_transporte" class="form-control"
                                               placeholder="Ej: 200000" min="0" step="1000">
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-bus mr-1"></i>
                                        2025: $200,000
                                    </small>
                                    <small class="text-danger d-block" id="error_aux_transporte"></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── SECCIÓN 3: Otros Valores ── -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-receipt text-info mr-2"></i>
                            <span class="font-weight-bold text-uppercase text-secondary"
                                  style="font-size:.75rem; letter-spacing:.08em;">Referencia Tributaria</span>
                            <hr class="flex-grow-1 ml-2 mt-0 mb-0">
                        </div>
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group mb-1">
                                    <label for="param_uvt" class="font-weight-bold mb-1 d-flex align-items-center">
                                        UVT (Unidad de Valor Tributario) <span class="text-danger ml-1">*</span>
                                        <i class="fas fa-question-circle text-muted ml-1"
                                           data-toggle="tooltip" data-placement="top"
                                           title="Valor de la Unidad de Valor Tributario para el año. Fijada anualmente por la DIAN. Usada en cálculos de retención en la fuente y límites de deducción."></i>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light text-muted" style="font-size:.85rem;">$</span>
                                        </div>
                                        <input type="number" id="param_uvt" class="form-control"
                                               placeholder="Ej: 49799" min="0" step="1">
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-landmark mr-1"></i>
                                        2025: $49,799 · Fijada por la DIAN
                                    </small>
                                    <small class="text-danger d-block" id="error_uvt"></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── SECCIÓN 4: Exoneración Ley 1607 ── -->
                    <div class="mb-2">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-balance-scale text-warning mr-2"></i>
                            <span class="font-weight-bold text-uppercase text-secondary"
                                  style="font-size:.75rem; letter-spacing:.08em;">Exoneración Ley 1607</span>
                            <hr class="flex-grow-1 ml-2 mt-0 mb-0">
                        </div>
                        <div class="row align-items-start">
                            <div class="col-md-4">
                                <div class="form-group mb-1">
                                    <label for="param_tope" class="font-weight-bold mb-1 d-flex align-items-center">
                                        Tope (× SMLV) <span class="text-danger ml-1">*</span>
                                        <i class="fas fa-question-circle text-muted ml-1"
                                           data-toggle="tooltip" data-placement="top"
                                           title="Número de SMLV que define el tope salarial para aplicar la exoneración de aportes patronales (Salud 8.5%, SENA 2%, ICBF 3%). Por defecto = 10 SMLV según la Ley 1607 de 2012."></i>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" id="param_tope" class="form-control"
                                               value="10" min="1" max="25" step="1">
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-light text-muted" style="font-size:.85rem;">× SMLV</span>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Rango permitido: 1–25 SMLV
                                    </small>
                                    <small class="text-danger d-block" id="error_tope_exoneracion_ley1607"></small>
                                </div>
                            </div>
                            <div class="col-md-8 pt-1">
                                <div class="p-3 rounded" style="background:#fffbeb; border:1px solid #fde68a; font-size:.8rem;">
                                    <div class="mb-1 font-weight-bold text-secondary">
                                        <i class="fas fa-calculator mr-1 text-warning"></i> Tope en pesos
                                    </div>
                                    <div id="labelTopePesos" class="font-weight-bold" style="font-size:1rem; color:#1e3c72;">
                                        calculando…
                                    </div>
                                    <div class="text-muted mt-1" style="font-size:.75rem;">
                                        Trabajadores con salario por debajo de este tope quedan exonerados de
                                        <strong>Salud empleador (8.5%)</strong>, <strong>SENA (2%)</strong> e <strong>ICBF (3%)</strong>
                                        cuando la empresa es contribuyente de renta.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div><!-- /modal-body -->

            <div class="modal-footer" style="background:#f8f9fa;">
                <!-- Inyectado dinámicamente desde parametros.js -->
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    function actualizarTopePesos() {
        const smlv  = parseFloat($('#param_smlv').val()) || 0;
        const tope  = parseInt($('#param_tope').val())   || 0;
        const total = smlv * tope;

        const fmt = total > 0
            ? '$' + total.toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 })
            : '—';

        $('#labelTopePesos').text(
            tope > 0 && smlv > 0
                ? tope + ' × $' + smlv.toLocaleString('es-CO') + ' = ' + fmt
                : 'Ingrese SMLV y tope para ver el valor'
        );
    }

    $(document).on('input change', '#param_smlv, #param_tope', actualizarTopePesos);

    $('#ModalParametros').on('shown.bs.modal', function () {
        actualizarTopePesos();
        $('[data-toggle="tooltip"]').tooltip();
    });
})();
</script>
