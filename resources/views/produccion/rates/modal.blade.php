<div class="modal fade" id="ModalRate" aria-hidden="true" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="rateModalTitle">Registrar Tarifa</h4>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="id">

        <fieldset class="border p-3 mb-2">
          <legend class="w-auto px-3">Tarifa</legend>

          <div class="row">
            <div class="col-md-6">
              <label>Producto</label>
              <select id="product_id" class="form-control"></select>
              <span class="text-danger" id="error_product_id"></span>
            </div>
            <div class="col-md-6">
              <label>Operación</label>
              <select id="operation_id" class="form-control"></select>
              <span class="text-danger" id="error_operation_id"></span>
            </div>
          </div>

          <div class="row mt-2">
            <div class="col-md-4">
              <label>Tarifa (por unidad)</label>
              <input type="number" step="0.01" id="amount" class="form-control">
              <span class="text-danger" id="error_amount"></span>
            </div>
            <div class="col-md-4">
              <label>Vigencia Desde</label>
              <input type="date" id="valid_from" class="form-control">
              <span class="text-danger" id="error_valid_from"></span>
            </div>
            <div class="col-md-4">
              <label>Vigencia Hasta</label>
              <input type="date" id="valid_to" class="form-control">
              <span class="text-danger" id="error_valid_to"></span>
            </div>
          </div>

          <div class="row mt-2">
            <div class="col-md-2">
              <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" id="is_active" checked>
                <label class="form-check-label" for="is_active">Activo</label>
              </div>
              <span class="text-danger" id="error_is_active"></span>
            </div>
          </div>

        </fieldset>
      </div>

      <div class="modal-footer"></div>
    </div>
  </div>
</div>
