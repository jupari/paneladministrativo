<!-- Modal Cargo -->
<div class="modal fade" id="ModalCargo" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header" style="background:linear-gradient(135deg,#1e3c72 0%,#2a5298 100%);">
                <div class="d-flex align-items-center">
                    <div class="bg-white rounded-circle p-2 mr-3 shadow-sm">
                        <i class="fas fa-briefcase text-primary" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0 font-weight-bold text-white" id="exampleModalLabel">Cargo</h5>
                        <small class="text-white" style="opacity:.75;">Gestión de perfiles de cargo</small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-4">
                <form autocomplete="off">
                    <input type="hidden" id="id" value="">

                    <!-- ── SECCIÓN 1: Identificación ── -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-tag text-primary mr-2"></i>
                            <span class="font-weight-bold text-uppercase text-secondary"
                                  style="font-size:.75rem; letter-spacing:.08em;">Identificación</span>
                            <hr class="flex-grow-1 ml-2 mt-0 mb-0">
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group mb-1">
                                    <label for="nombre" class="font-weight-bold mb-1">
                                        Nombre del cargo <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="nombre" class="form-control"
                                           placeholder="Ej: Operario, Técnico Electricista, Supervisor…">
                                    <small class="text-danger" id="error_nombre"></small>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-center pt-3">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="active" checked>
                                    <label class="custom-control-label font-weight-bold" for="active">
                                        Cargo activo
                                    </label>
                                </div>
                                <small class="text-danger ml-2" id="error_active"></small>
                            </div>
                        </div>
                    </div>

                    <!-- ── SECCIÓN 2: Configuración de Nómina ── -->
                    <div class="mb-2">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-calculator text-success mr-2"></i>
                            <span class="font-weight-bold text-uppercase text-secondary"
                                  style="font-size:.75rem; letter-spacing:.08em;">Configuración de Nómina</span>
                            <hr class="flex-grow-1 ml-2 mt-0 mb-0">
                        </div>

                        <!-- Alerta informativa -->
                        <div class="alert py-2 px-3 mb-3 d-flex align-items-start"
                             style="background:#e8f4fd; border:1px solid #bee5f5; border-radius:6px;">
                            <i class="fas fa-info-circle text-info mt-1 mr-2" style="flex-shrink:0;"></i>
                            <small class="text-secondary">
                                Estos campos son usados por el <strong>Motor de Liquidación de Nómina</strong>
                                para calcular el costo empresa real al cotizar personal.
                                Si no se configuran, el motor asume el <strong>SMLV vigente</strong> y
                                <strong>ARL Nivel I</strong>.
                            </small>
                        </div>

                        <div class="row">
                            <!-- Salario Base -->
                            <div class="col-md-5">
                                <div class="form-group mb-1">
                                    <label for="salario_base" class="font-weight-bold mb-1 d-flex align-items-center">
                                        Salario base mensual
                                        <span class="badge badge-secondary ml-2" style="font-size:.65rem;">Opcional</span>
                                        <i class="fas fa-question-circle text-muted ml-1"
                                           data-toggle="tooltip" data-placement="top"
                                           title="Salario bruto mensual del trabajador (sin cargas). Si se deja vacío, el motor usa el SMLV del año de cálculo. Ejemplo: 1,423,500 para SMLV 2025."></i>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light text-muted"
                                                  style="font-size:.85rem;">$</span>
                                        </div>
                                        <input type="number" id="salario_base" class="form-control"
                                               placeholder="Vacío = SMLV vigente"
                                               min="0" step="1000">
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        SMLV 2025: $1,423,500
                                    </small>
                                    <small class="text-danger d-block" id="error_salario_base"></small>
                                </div>
                            </div>

                            <!-- ARL Nivel -->
                            <div class="col-md-4">
                                <div class="form-group mb-1">
                                    <label for="arl_nivel" class="font-weight-bold mb-1 d-flex align-items-center">
                                        Nivel de Riesgo ARL
                                        <i class="fas fa-question-circle text-muted ml-1"
                                           data-toggle="tooltip" data-placement="top"
                                           title="Clasifica el riesgo ocupacional del cargo según el Decreto 1607/2002. Afecta el % de cotización ARL que paga el empleador sobre el IBC."></i>
                                    </label>
                                    <select id="arl_nivel" class="form-control">
                                        <option value="1">Nivel I — Mínimo (0.522%)</option>
                                        <option value="2">Nivel II — Bajo (1.044%)</option>
                                        <option value="3">Nivel III — Medio (2.436%)</option>
                                        <option value="4">Nivel IV — Alto (4.350%)</option>
                                        <option value="5">Nivel V — Máximo (6.960%)</option>
                                    </select>
                                    <small class="text-muted">
                                        <i class="fas fa-hard-hat mr-1"></i>
                                        Oficina/Admin → Nivel I · Alturas/Eléctrico → III–V
                                    </small>
                                    <small class="text-danger d-block" id="error_arl_nivel"></small>
                                </div>
                            </div>

                            <!-- Exoneración Ley 1607 -->
                            <div class="col-md-3 d-flex flex-column justify-content-start pt-1">
                                <label class="font-weight-bold mb-1 d-flex align-items-center">
                                    Exoneración Ley 1607
                                    <i class="fas fa-question-circle text-muted ml-1"
                                       data-toggle="tooltip" data-placement="top"
                                       title="Si el salario es menor a 10 SMLV y la empresa es contribuyente de renta, está exonerada de pagar Salud empleador (8.5%), SENA (2%) e ICBF (3%). Aplica a la mayoría de cargos operativos."></i>
                                </label>
                                <div class="custom-control custom-switch mt-1">
                                    <input type="checkbox" class="custom-control-input"
                                           id="aplica_exoneracion_ley1607" checked>
                                    <label class="custom-control-label" for="aplica_exoneracion_ley1607">
                                        Aplica exoneración
                                    </label>
                                </div>
                                <small class="text-muted mt-1">
                                    <span id="labelExoneracion" class="badge badge-success">
                                        <i class="fas fa-check mr-1"></i>Exonerado: −13.5%
                                    </span>
                                </small>
                                <small class="text-danger d-block" id="error_aplica_exoneracion_ley1607"></small>
                            </div>
                        </div>

                        <!-- Resumen visual del impacto -->
                        <div id="resumenARL" class="mt-3 p-3 rounded"
                             style="background:#f8f9fa; border:1px solid #dee2e6; font-size:.8rem;">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="text-muted mb-1">ARL</div>
                                    <strong id="pctARL" class="text-danger">0.522%</strong>
                                </div>
                                <div class="col-4">
                                    <div class="text-muted mb-1">Salud + SENA + ICBF empleador</div>
                                    <strong id="pctSalud" class="text-success">
                                        <i class="fas fa-check-circle"></i> Exonerado
                                    </strong>
                                </div>
                                <div class="col-4">
                                    <div class="text-muted mb-1">Caja Compensación</div>
                                    <strong class="text-secondary">4.00%</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div><!-- /modal-body -->

            <div class="modal-footer" style="background:#f8f9fa;">
                <!-- Footer se inyecta dinámicamente desde cargos.js -->
            </div>
        </div>
    </div>
</div>

<script>
// Actualiza el badge de exoneración y el resumen ARL en tiempo real
(function () {
    const arlPct = { 1: '0.522', 2: '1.044', 3: '2.436', 4: '4.350', 5: '6.960' };

    function actualizarResumen() {
        const nivel   = parseInt($('#arl_nivel').val()) || 1;
        const exoner  = $('#aplica_exoneracion_ley1607').is(':checked');

        $('#pctARL').text(arlPct[nivel] + '%');

        if (exoner) {
            $('#pctSalud').html('<i class="fas fa-check-circle text-success"></i> Exonerado (−13.5%)');
            $('#labelExoneracion').removeClass('badge-danger').addClass('badge-success')
                .html('<i class="fas fa-check mr-1"></i>Exonerado: −13.5%');
        } else {
            $('#pctSalud').html('<span class="text-danger">13.5% a cargo</span>');
            $('#labelExoneracion').removeClass('badge-success').addClass('badge-danger')
                .html('<i class="fas fa-times mr-1"></i>No exonerado: +13.5%');
        }
    }

    $(document).on('change', '#arl_nivel, #aplica_exoneracion_ley1607', actualizarResumen);
    // Inicializar al abrir el modal
    $('#ModalCargo').on('shown.bs.modal', function () {
        actualizarResumen();
        $('[data-toggle="tooltip"]').tooltip();
    });
})();
</script>
