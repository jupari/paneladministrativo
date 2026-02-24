<div class="modal fade" id="ModalLog" aria-hidden="true" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="logModalTitle">Registrar Log</h4>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="id">

        <div class="row">
          {{-- <div class="col-md-4">
            <label>Orden</label>
            <input type="number" id="order_id" class="form-control">
            <span class="text-danger" id="error_order_id"></span>
          </div> --}}
          <input type="hidden" name="order_id" id="order_id">
          <div class="col-md-12">
            <label for="order_operation_id">Operación (Routing)</label>
            <select id="order_operation_id" class="form-control"></select>
            <span class="text-danger" id="error_order_operation_id"></span>
          </div>
          <div class="col-md-4">
            <label>Empleado</label>
            <select id="employee_id" class="form-control"></select>
            <span class="text-danger" id="error_employee_id"></span>
          </div>
        </div>

        <div class="row mt-2">
          <div class="col-md-4">
            <label>Fecha</label>
            <input type="date" id="work_date" class="form-control">
            <span class="text-danger" id="error_work_date"></span>
          </div>
          <div class="col-md-4">
            <label>Turno</label>
            <select id="shift" class="form-control">
              <option value="">--</option>
              <option value="AM">AM</option>
              <option value="PM">PM</option>
              <option value="NIGHT">NIGHT</option>
            </select>
            <span class="text-danger" id="error_shift"></span>
          </div>
          <div class="col-md-4">
            <label>Notas</label>
            <input type="text" id="notes" class="form-control">
            <span class="text-danger" id="error_notes"></span>
          </div>
        </div>

        <div class="row mt-2">
          <div class="col-md-6">
            <label>Cantidad</label>
            <input type="number" step="0.01" id="qty" class="form-control">
            <span class="text-danger" id="error_qty"></span>
          </div>
          <div class="col-md-6">
            <label>Rechazadas</label>
            <input type="number" step="0.01" id="rejected_qty" class="form-control" value="0">
            <span class="text-danger" id="error_rejected_qty"></span>
          </div>
        </div>

      </div>
      <div class="modal-footer"></div>
    </div>
  </div>
</div>
