<div class="modal fade" id="ModalOrder" aria-hidden="true" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="orderModalTitle">Registrar Orden</h4>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="id">

        <div class="row">
          <div class="col-md-4">
            <label>Código</label>
            <input type="text" id="code" class="form-control">
            <span class="text-danger" id="error_code"></span>
          </div>
          <div class="col-md-8">
            <label>Producto</label>
            <select id="product_id" class="form-control"></select>
            <span class="text-danger" id="error_product_id"></span>
          </div>
        </div>

        <div class="row mt-2">
          <div class="col-md-4">
            <label>Objetivo (piezas)</label>
            <input type="number" step="0.01" id="objective_qty" class="form-control">
            <span class="text-danger" id="error_objective_qty"></span>
          </div>
          <div class="col-md-4">
            <label>Inicio</label>
            <input type="date" id="start_date" class="form-control">
            <span class="text-danger" id="error_start_date"></span>
          </div>
          <div class="col-md-4">
            <label>Fin</label>
            <input type="date" id="end_date" class="form-control">
            <span class="text-danger" id="error_end_date"></span>
          </div>
        </div>

        <div class="row mt-2">
          <div class="col-md-4">
            <label>Estado</label>
            <select id="status" class="form-control">
              <option value="DRAFT">DRAFT</option>
              <option value="IN_PROGRESS">IN_PROGRESS</option>
              <option value="CLOSED">CLOSED</option>
              <option value="CANCELLED">CANCELLED</option>
            </select>
            <span class="text-danger" id="error_status"></span>
          </div>
          <div class="col-md-8">
            <label>Notas</label>
            <input type="text" id="notes" class="form-control">
            <span class="text-danger" id="error_notes"></span>
          </div>
        </div>

      </div>

      <div class="modal-footer"></div>
    </div>
  </div>
</div>
