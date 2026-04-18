<!-- Modal Turno de Trabajo -->
<div class="modal fade" id="ModalTurno" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header" style="background:linear-gradient(135deg,#1e3c72 0%,#2a5298 100%);">
                <div class="d-flex align-items-center">
                    <div class="bg-white rounded-circle p-2 mr-3 shadow-sm">
                        <i class="fas fa-clock text-primary" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0 font-weight-bold text-white" id="modalTurnoLabel">Turno de Trabajo</h5>
                        <small class="text-white" style="opacity:.75;">Configuración de jornada laboral</small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-4">
                <form autocomplete="off">
                    <input type="hidden" id="turno_id" value="">

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
                                    <label for="turno_nombre" class="font-weight-bold mb-1">
                                        Nombre del turno <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="turno_nombre" class="form-control"
                                           placeholder="Ej: Turno Diurno, Turno Nocturno…" maxlength="100">
                                    <small class="text-danger d-block" id="error_nombre"></small>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-center pt-3">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="turno_active" checked>
                                    <label class="custom-control-label font-weight-bold" for="turno_active">
                                        Turno activo
                                    </label>
                                </div>
                                <small class="text-danger ml-2" id="error_active"></small>
                            </div>
                            <div class="col-md-12 mt-2">
                                <div class="form-group mb-1">
                                    <label for="turno_descripcion" class="font-weight-bold mb-1">
                                        Descripción
                                        <span class="badge badge-secondary ml-1" style="font-size:.65rem;">Opcional</span>
                                    </label>
                                    <input type="text" id="turno_descripcion" class="form-control"
                                           placeholder="Descripción breve del turno" maxlength="255">
                                    <small class="text-danger d-block" id="error_descripcion"></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── SECCIÓN 2: Configuración Horaria ── -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-sun text-warning mr-2"></i>
                            <span class="font-weight-bold text-uppercase text-secondary"
                                  style="font-size:.75rem; letter-spacing:.08em;">Configuración de Jornada</span>
                            <hr class="flex-grow-1 ml-2 mt-0 mb-0">
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-1">
                                    <label class="font-weight-bold mb-2 d-flex align-items-center">
                                        Tipo de jornada <span class="text-danger ml-1">*</span>
                                        <i class="fas fa-question-circle text-muted ml-1"
                                           data-toggle="tooltip" data-placement="top"
                                           title="Define el factor de recargo de las horas ordinarias. Diurna: horas entre 6am y 10pm. Nocturna: horas entre 10pm y 6am (recargo +35%)."></i>
                                    </label>
                                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                        <label class="btn btn-outline-warning active" id="btnDiurna">
                                            <input type="radio" id="tipo_diurna" name="tipo_ordinaria" value="diurna" checked>
                                            <i class="fas fa-sun mr-1"></i> Diurna
                                        </label>
                                        <label class="btn btn-outline-dark" id="btnNocturna">
                                            <input type="radio" id="tipo_nocturna" name="tipo_ordinaria" value="nocturna">
                                            <i class="fas fa-moon mr-1"></i> Nocturna
                                        </label>
                                    </div>
                                    <small class="text-danger d-block mt-1" id="error_tipo_ordinaria"></small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-1">
                                    <label for="turno_max_horas_ord" class="font-weight-bold mb-1 d-flex align-items-center">
                                        Máx. h ordinarias/día <span class="text-danger ml-1">*</span>
                                        <i class="fas fa-question-circle text-muted ml-1"
                                           data-toggle="tooltip" data-placement="top"
                                           title="Máximo de horas ordinarias por día. Según el CST colombiano el máximo es 8h diarias (jornada máxima). En la práctica, se recomienda 7h para dejar margen a extras."></i>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" id="turno_max_horas_ord" class="form-control"
                                               value="{{ $maxOrd }}" min="1" max="{{ $maxOrd }}" step="1">
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-light text-muted" style="font-size:.85rem;">h/día</span>
                                        </div>
                                    </div>
                                    <small class="text-muted">Máximo legal: {{ $maxOrd }} h/día</small>
                                    <small class="text-danger d-block" id="error_max_horas_ordinarias"></small>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-center pt-1">
                                <div>
                                    <label class="font-weight-bold mb-1 d-flex align-items-center">
                                        Dom/Festivo
                                        <i class="fas fa-question-circle text-muted ml-1"
                                           data-toggle="tooltip" data-placement="top"
                                           title="Activa el recargo por trabajo dominical o festivo. Diurno: +75% (factor ×1.75). Nocturno: +110% (factor ×2.10)."></i>
                                    </label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="turno_dominical">
                                        <label class="custom-control-label" for="turno_dominical">
                                            Aplica recargo dominical
                                        </label>
                                    </div>
                                    <small class="text-danger d-block" id="error_es_dominical_festivo"></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── SECCIÓN 3: Horas Extra ── -->
                    <div class="mb-2">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-plus-circle text-danger mr-2"></i>
                            <span class="font-weight-bold text-uppercase text-secondary"
                                  style="font-size:.75rem; letter-spacing:.08em;">Recargos</span>
                            <hr class="flex-grow-1 ml-2 mt-0 mb-0">
                        </div>

                        {{-- <div class="alert py-2 px-3 mb-3 d-flex align-items-start"
                             style="background:#fff3cd; border:1px solid #ffc107; border-radius:6px; font-size:.8rem;">
                            <i class="fas fa-exclamation-triangle text-warning mt-1 mr-2" style="flex-shrink:0;"></i>
                            <span class="text-dark">
                                Máximo legal: <strong>2 horas extra por día</strong> (Art. 167 CST colombiano).
                                Configure qué tipos de recargos aplican a este turno.
                            </span>
                        </div> --}}

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-1">
                                    <label for="turno_max_extras" class="font-weight-bold mb-1 d-flex align-items-center">
                                        Máx. h extra/día <span class="text-danger ml-1">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" id="turno_max_extras" class="form-control"
                                               value="{{ $maxExtra }}" min="0" max="{{ $maxExtra }}" step="1">
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-light text-muted" style="font-size:.85rem;">h/día</span>
                                        </div>
                                    </div>
                                    <small class="text-muted">0 = sin recargo extra en este turno</small>
                                    <small class="text-danger d-block" id="error_max_horas_extras"></small>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-center pt-1">
                                <div>
                                    <label class="font-weight-bold mb-1 d-flex align-items-center">
                                        <i class="fas fa-sun text-warning mr-1"></i> Recargo Diurno
                                        <i class="fas fa-question-circle text-muted ml-1"
                                           data-toggle="tooltip" data-placement="top"
                                           title="Horas extra entre 6am y 10pm. Factor ×1.25 (o ×2.00 si es dominical)."></i>
                                    </label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="turno_he_diurnas" checked>
                                        <label class="custom-control-label" for="turno_he_diurnas">
                                            Permite Recargo Diurno
                                        </label>
                                    </div>
                                    <small class="text-danger d-block" id="error_tiene_extras_diurnas"></small>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-center pt-1">
                                <div>
                                    <label class="font-weight-bold mb-1 d-flex align-items-center">
                                        <i class="fas fa-moon text-dark mr-1"></i> Recargo Nocturno
                                        <i class="fas fa-question-circle text-muted ml-1"
                                           data-toggle="tooltip" data-placement="top"
                                           title="Horas extra entre 10pm y 6am. Factor ×1.75 (o ×2.50 si es dominical)."></i>
                                    </label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="turno_he_nocturnas">
                                        <label class="custom-control-label" for="turno_he_nocturnas">
                                            Permite Recargo Nocturno
                                        </label>
                                    </div>
                                    <small class="text-danger d-block" id="error_tiene_extras_nocturnas"></small>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen de factores -->
                        <div id="resumenFactores" class="mt-3 p-3 rounded"
                             style="background:#f8f9fa; border:1px solid #dee2e6; font-size:.78rem;">
                            <div class="font-weight-bold text-secondary mb-2">
                                <i class="fas fa-calculator mr-1"></i> Factores CST que aplican a este turno
                            </div>
                            <div class="row text-center" id="badgesFactores">
                                <!-- Populated by JS -->
                            </div>
                        </div>
                    </div>

                </form>
            </div><!-- /modal-body -->

            <div class="modal-footer" style="background:#f8f9fa;">
                <!-- Inyectado dinámicamente desde turnos.js -->
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const factoresMapa = {
        diurna_normal_ord:   { label: 'Ordinaria Diurna',     factor: '×1.00', color: 'warning text-dark' },
        nocturna_normal_ord: { label: 'Ordinaria Nocturna',   factor: '×1.35', color: 'dark' },
        dom_diurna_ord:      { label: 'Dom/Fest Diurna',      factor: '×1.75', color: 'danger' },
        dom_nocturna_ord:    { label: 'Dom/Fest Nocturna',    factor: '×2.10', color: 'danger' },
        he_diurna:           { label: 'HE Diurna',            factor: '×1.25', color: 'info' },
        he_nocturna:         { label: 'HE Nocturna',          factor: '×1.75', color: 'secondary' },
        he_dom_diurna:       { label: 'HE Dom Diurna',        factor: '×2.00', color: 'danger' },
        he_dom_nocturna:     { label: 'HE Dom Nocturna',      factor: '×2.50', color: 'dark' },
    };

    function actualizarFactores() {
        const isDiurna  = $('input[name="tipo_ordinaria"]:checked').val() === 'diurna';
        const isDom     = $('#turno_dominical').is(':checked');
        const heD       = $('#turno_he_diurnas').is(':checked');
        const heN       = $('#turno_he_nocturnas').is(':checked');
        const maxHE     = parseInt($('#turno_max_extras').val()) || 0;

        const badges = [];

        // Ordinaria
        if (!isDom) {
            badges.push(isDiurna ? 'diurna_normal_ord' : 'nocturna_normal_ord');
        } else {
            badges.push(isDiurna ? 'dom_diurna_ord' : 'dom_nocturna_ord');
        }

        // Extras
        if (maxHE > 0) {
            if (!isDom) {
                if (heD) badges.push('he_diurna');
                if (heN) badges.push('he_nocturna');
            } else {
                if (heD) badges.push('he_dom_diurna');
                if (heN) badges.push('he_dom_nocturna');
            }
        }

        const html = badges.map(k => {
            const f = factoresMapa[k];
            return `<div class="col-auto mb-1">
                <span class="badge badge-${f.color} p-2">
                    ${f.label}<br><strong>${f.factor}</strong>
                </span>
            </div>`;
        }).join('');

        $('#badgesFactores').html(html || '<div class="col text-muted">Sin factores configurados.</div>');
    }

    $(document).on('change', 'input[name="tipo_ordinaria"], #turno_dominical, #turno_he_diurnas, #turno_he_nocturnas, #turno_max_extras', actualizarFactores);

    $('#ModalTurno').on('shown.bs.modal', function () {
        actualizarFactores();
        $('[data-toggle="tooltip"]').tooltip();
    });
})();
</script>
