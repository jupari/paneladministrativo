<div class="modal fade" id="ModalProdLog" aria-hidden="true" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Registrar Producción</h4>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <div class="modal-body">
        <form autocomplete="off">
          <fieldset class="border p-3 mb-3">
            <legend class="w-auto px-3">Datos</legend>

            <div class="row">
              <div class="col-md-6">
                <label>Operación de Orden</label>
                <select id="order_operation_id" class="form-control">
                  {{-- lo ideal: cargar por ajax, por ahora se puede cargar server-side si quieres --}}
                </select>
                <span class="text-danger" id="error_order_operation_id"></span>
              </div>

              <div class="col-md-6">
                <label>Fecha trabajo</label>
                <input type="datetime-local" id="worked_at" class="form-control">
                <span class="text-danger" id="error_worked_at"></span>
              </div>

              <div class="col-md-12 mt-2">
                <label>Empleados</label>
                <select id="employee_ids" class="form-control" multiple></select>
                <small class="text-muted">Puedes seleccionar varios empleados a la vez.</small>
                <span class="text-danger" id="error_employee_ids"></span>
              </div>

              <div class="col-md-6 mt-2">
                <label>Cantidad (por empleado)</label>
                <input type="number" step="0.0001" id="qty" class="form-control" value="1">
                <span class="text-danger" id="error_qty"></span>
              </div>

              <div class="col-md-6 mt-2">
                <label>Notas</label>
                <input type="text" id="notes" class="form-control">
                <span class="text-danger" id="error_notes"></span>
              </div>
            </div>
          </fieldset>
        </form>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary" onclick="storeLog()">
          Guardar
        </button>
      </div>
    </div>
  </div>
</div>
