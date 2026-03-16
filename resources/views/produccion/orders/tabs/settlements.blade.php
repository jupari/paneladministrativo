<div id="sett-container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="fas fa-file-invoice-dollar mr-1"></i> Liquidación de la Orden</h5>
    <button class="btn btn-sm btn-success" id="btn-calc-settlement" onclick="calcSettlement()">
      <i class="fas fa-calculator mr-1"></i> Calcular Liquidación
    </button>
  </div>

  {{-- Tarjetas resumen --}}
  <div class="row" id="sett-summary-cards">
    <div class="col-lg-3 col-md-6">
      <div class="info-box">
        <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total a Pagar</span>
          <span class="info-box-number" id="sett-total-amount">$0</span>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="info-box">
        <span class="info-box-icon bg-info"><i class="fas fa-cubes"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Unidades Liquidadas</span>
          <span class="info-box-number" id="sett-total-qty">0</span>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="info-box">
        <span class="info-box-icon bg-primary"><i class="fas fa-users"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Operarios</span>
          <span class="info-box-number" id="sett-total-workers">0</span>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="info-box">
        <span class="info-box-icon bg-warning"><i class="fas fa-money-check-alt"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Costo Proyectado</span>
          <span class="info-box-number" id="sett-cost-projected">$0</span>
        </div>
      </div>
    </div>
  </div>

  {{-- Estado de liquidación --}}
  <div class="mb-3" id="sett-status-bar" style="display:none;">
    <small class="text-muted mr-2">Estado registros:</small>
    <span id="sett-status-badges"></span>
  </div>

  <div class="row">
    {{-- Tabla por operación --}}
    <div class="col-lg-7">
      <div class="card card-outline card-primary">
        <div class="card-header py-2">
          <h6 class="card-title mb-0"><i class="fas fa-cogs mr-1"></i> Desglose por Operación</h6>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-striped table-hover mb-0" id="sett-ops-table">
              <thead class="thead-light">
                <tr>
                  <th>Operación</th>
                  <th class="text-right">Cantidad</th>
                  <th class="text-right">Tarifa</th>
                  <th class="text-right">Monto</th>
                  <th class="text-center">Operarios</th>
                </tr>
              </thead>
              <tbody></tbody>
              <tfoot>
                <tr class="font-weight-bold bg-light">
                  <td>TOTAL</td>
                  <td class="text-right" id="sett-ops-total-qty"></td>
                  <td></td>
                  <td class="text-right" id="sett-ops-total-amount"></td>
                  <td class="text-center" id="sett-ops-total-workers"></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>

    {{-- Tabla por empleado --}}
    <div class="col-lg-5">
      <div class="card card-outline card-info">
        <div class="card-header py-2">
          <h6 class="card-title mb-0"><i class="fas fa-user-hard-hat mr-1"></i> Desglose por Operario</h6>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-striped table-hover mb-0" id="sett-emp-table">
              <thead class="thead-light">
                <tr>
                  <th>Operario</th>
                  <th class="text-right">Cantidad</th>
                  <th class="text-right">Total a Pagar</th>
                </tr>
              </thead>
              <tbody></tbody>
              <tfoot>
                <tr class="font-weight-bold bg-light">
                  <td>TOTAL</td>
                  <td class="text-right" id="sett-emp-total-qty"></td>
                  <td class="text-right" id="sett-emp-total-amount"></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Mensaje cuando no hay datos --}}
  <div id="sett-empty" class="text-center py-4" style="display:none;">
    <i class="fas fa-calculator fa-3x text-muted mb-3"></i>
    <p class="text-muted">No hay liquidación calculada para esta orden.</p>
    <p class="text-muted">Presiona <strong>"Calcular Liquidación"</strong> para generar los montos.</p>
  </div>
</div>
