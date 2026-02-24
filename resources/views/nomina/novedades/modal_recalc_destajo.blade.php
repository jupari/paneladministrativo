<div class="modal fade" id="ModalRecalcDestajo" aria-hidden="true" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Recalcular Destajo (LAB_DESTAJO)</h4>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <div class="modal-body">
        <div class="alert alert-info">
          Esto recalcula novedades LAB_DESTAJO desde <b>prod_worker_settlements</b> por <b>created_at</b> en el periodo.
          Opcionalmente, si eliges un PayRun, lo deja en <b>DRAFT</b> y limpia líneas para forzar el recálculo.
        </div>

        <div class="form-group">
          <label>Periodo desde</label>
          <input type="date" id="recalc_start" class="form-control">
          <span class="text-danger" id="error_recalc_start"></span>
        </div>

        <div class="form-group">
          <label>Periodo hasta</label>
          <input type="date" id="recalc_end" class="form-control">
          <span class="text-danger" id="error_recalc_end"></span>
        </div>

        <div class="form-group">
          <label>PayRun (opcional)</label>
          <select id="recalc_pay_run_id" class="form-control"></select>
          <small class="text-muted">Si lo seleccionas, el payrun se resetea a DRAFT.</small>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary" onclick="recalculateDestajo()">
          <i class="fas fa-sync-alt"></i> Recalcular
        </button>
      </div>
    </div>
  </div>
</div>
