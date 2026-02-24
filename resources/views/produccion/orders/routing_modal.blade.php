<div class="modal fade" id="ModalRouting" aria-hidden="true" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="routingModalTitle">Agregar Operación</h4>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="routing_id">

        <div class="row">
          <div class="col-md-6">
            <label>Operación</label>
            <select id="routing_operation_id" class="form-control"></select>
            <span class="text-danger" id="error_operation_id"></span>
          </div>

          <div class="col-md-2">
            <label>Secuencia</label>
            <input type="number" id="routing_seq" class="form-control" value="1">
            <span class="text-danger" id="error_seq"></span>
          </div>

          <div class="col-md-2">
            <label>Qty x unidad</label>
            <input type="number" step="0.0001" id="routing_qty_per_unit" class="form-control" value="1">
            <span class="text-danger" id="error_qty_per_unit"></span>
          </div>

          <div class="col-md-2">
            <label>Estado</label>
            <select id="routing_status" class="form-control">
              <option value="PENDING">PENDING</option>
              <option value="IN_PROGRESS">IN_PROGRESS</option>
              <option value="DONE">DONE</option>
            </select>
            <span class="text-danger" id="error_status"></span>
          </div>
        </div>

        <small class="text-muted">
          El requerido se calcula automáticamente: objetivo_orden * qty_x_unidad.
        </small>
      </div>

      <div class="modal-footer"></div>
    </div>
  </div>
</div>
