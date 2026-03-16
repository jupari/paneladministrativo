$(function () {
  initSelect2();
  loadOpsTable();
  loadLogsTable();
  loadDamagesTable();
  loadSettlementData();
});

function initSelect2(){
  var $modal = $('#ModalProdLog');

  // Empleados
  $('#employee_ids').select2({
    width: '100%',
    placeholder: 'Seleccione empleados',
    dropdownParent: $modal,
    ajax: {
      url: window.PROD.routes.empSelect2,
      dataType: 'json',
      delay: 250,
      data: params => ({ q: params.term }),
      processResults: data => ({ results: data.results })
    }
  });

  // Operaciones de la orden
  $('#order_operation_id').select2({
    width: '100%',
    placeholder: 'Seleccione operación',
    dropdownParent: $modal,
    ajax: {
      url: window.PROD.routes.opsSelect2,
      dataType: 'json',
      delay: 250,
      data: params => ({ q: params.term }),
      processResults: data => ({ results: data.results })
    }
  });
}

function loadOpsTable(){
  if ($.fn.DataTable.isDataTable('#ops-table')) $('#ops-table').DataTable().destroy();

  $('#ops-table').DataTable({
    language: { url: "/assets/js/spanish.json" },
    responsive: true,
    ajax: window.PROD.routes.opsTable,
    columns: [
      { data: 'DT_RowIndex', orderable:false, searchable:false },
      { data: 'seq' },
      { data: 'code' },
      { data: 'name' },
      { data: 'required_qty', className:'text-right' },
      { data: 'damaged_qty', className:'text-right text-danger' },
      { data: 'adjusted_required', className:'text-right' },
      { data: 'done_qty', className:'text-right' },
      { data: 'remaining_qty', className:'text-right' },
      { data: 'progress', className:'text-center', orderable:false, searchable:false },
      { data: 'computed_status', className:'text-center', orderable:false, searchable:false }
    ],
    order: [[1,'asc']],
    pageLength: 10,
  });
}

function loadLogsTable(){
  if ($.fn.DataTable.isDataTable('#logs-table')) $('#logs-table').DataTable().destroy();

  $('#logs-table').DataTable({
    language: { url: "/assets/js/spanish.json" },
    responsive: true,
    ajax: window.PROD.routes.logsTable,
    columns: [
      { data: 'DT_RowIndex', orderable:false, searchable:false },
      { data: 'worked_at' },
      { data: 'operation' },
      { data: 'employee' },
      { data: 'qty', className:'text-right' },
      { data: 'notes' },
    ],
    order: [[1,'desc']],
    pageLength: 10,
  });
}

function loadDamagesTable(){
  if ($.fn.DataTable.isDataTable('#damages-table')) $('#damages-table').DataTable().destroy();

  $('#damages-table').DataTable({
    language: { url: "/assets/js/spanish.json" },
    responsive: true,
    ajax: window.PROD.routes.damagesTable,
    columns: [
      { data: 'DT_RowIndex', orderable:false, searchable:false },
      { data: 'registered_at' },
      { data: 'damage_type' },
      { data: 'quantity', className:'text-right text-danger font-weight-bold' },
      { data: 'registered_by' },
      { data: 'notes' },
    ],
    order: [[1,'desc']],
    pageLength: 10,
  });
}

function openLogModal(){
  limpiarErroresLog();
  $('#ModalProdLog').modal('show');
}

function limpiarErroresLog(){
  ['order_operation_id','employee_ids','qty','worked_at','notes'].forEach(f => $('#error_'+f).text(''));
}

function storeLog(){
  limpiarErroresLog();

  let data = new FormData();
  data.append('order_operation_id', $('#order_operation_id').val());
  data.append('qty', $('#qty').val());
  data.append('worked_at', $('#worked_at').val());
  data.append('notes', $('#notes').val());

  // multi empleados
  let empIds = $('#employee_ids').val() || [];
  empIds.forEach(id => data.append('employee_ids[]', id));

  $.ajax({
    url: window.PROD.routes.logStore,
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    type: 'POST',
    data: data,
    processData: false,
    contentType: false,
  }).then(resp => {
    toastr.success(resp.message);
    $('#ModalProdLog').modal('hide');
    loadOpsTable();
    loadLogsTable();
  }).catch(e => {
    if (e.status === 422) {
      let errs = e.responseJSON.errors || {};
      Object.keys(errs).forEach(k => {
        $('#error_'+k.replace('.','_')).text(errs[k][0]);
      });
      toastr.warning('Revisa los errores del formulario.');
      return;
    }
    toastr.error(e.responseJSON?.message || 'Error guardando producción.');
  });
}

/* ── Liquidación ───────────────────────────────────── */

function fmt(n) {
  return new Intl.NumberFormat('es-CO', { style:'currency', currency:'COP', minimumFractionDigits:0, maximumFractionDigits:0 }).format(n);
}
function fmtRate(n) {
  return new Intl.NumberFormat('es-CO', { style:'currency', currency:'COP', minimumFractionDigits:2 }).format(n);
}
function fmtQty(n) {
  return new Intl.NumberFormat('es-CO', { minimumFractionDigits:0, maximumFractionDigits:2 }).format(n);
}

function loadSettlementData() {
  $.getJSON(window.PROD.routes.settlementData)
    .done(function(data) {
      var t = data.totals;
      var hasData = t && parseInt(t.total_rows) > 0;

      if (!hasData) {
        $('#sett-summary-cards').hide();
        $('#sett-status-bar').hide();
        $('#sett-ops-table').closest('.row').hide();
        $('#sett-empty').show();
        return;
      }

      $('#sett-empty').hide();
      $('#sett-summary-cards').show();
      $('#sett-ops-table').closest('.row').show();

      // Tarjetas resumen
      $('#sett-total-amount').text(fmt(t.total_amount));
      $('#sett-total-qty').text(fmtQty(t.total_qty));
      $('#sett-total-workers').text(t.total_workers);
      var projected = parseFloat(data.cost_projected);
      $('#sett-cost-projected').text(projected > 0 ? fmt(projected) : fmt(data.cost_per_unit) + '/ud');

      // Estado badges
      if (data.status_counts && Object.keys(data.status_counts).length) {
        var html = '';
        var colors = { DRAFT:'secondary', APPROVED:'primary', SYNCED_TO_NOMINA:'success' };
        var labels = { DRAFT:'Borrador', APPROVED:'Aprobado', SYNCED_TO_NOMINA:'En Nómina' };
        $.each(data.status_counts, function(st, cnt) {
          html += '<span class="badge badge-'+(colors[st]||'info')+' mr-1">'+(labels[st]||st)+': '+cnt+'</span> ';
        });
        $('#sett-status-badges').html(html);
        $('#sett-status-bar').show();
      }

      // Tabla por operación
      var opsBody = '';
      var sumQty = 0, sumAmt = 0, sumW = 0;
      $.each(data.by_operation, function(i, r) {
        var q = parseFloat(r.total_qty);
        var a = parseFloat(r.total_amount);
        var rate = parseFloat(r.rate);
        sumQty += q; sumAmt += a; sumW += parseInt(r.workers);
        opsBody += '<tr>'
          + '<td><span class="text-muted mr-1">'+r.op_code+'</span>'+r.op_name+'</td>'
          + '<td class="text-right">'+fmtQty(q)+'</td>'
          + '<td class="text-right">'+fmtRate(rate)+'</td>'
          + '<td class="text-right font-weight-bold">'+fmt(a)+'</td>'
          + '<td class="text-center">'+r.workers+'</td>'
          + '</tr>';
      });
      $('#sett-ops-table tbody').html(opsBody);
      $('#sett-ops-total-qty').text(fmtQty(sumQty));
      $('#sett-ops-total-amount').text(fmt(sumAmt));
      $('#sett-ops-total-workers').text(sumW);

      // Tabla por empleado
      var empBody = '';
      var eSumQ = 0, eSumA = 0;
      $.each(data.by_employee, function(i, r) {
        var q = parseFloat(r.total_qty);
        var a = parseFloat(r.total_amount);
        eSumQ += q; eSumA += a;
        empBody += '<tr>'
          + '<td><small class="text-muted mr-1">'+r.doc+'</small> '+r.name+'</td>'
          + '<td class="text-right">'+fmtQty(q)+'</td>'
          + '<td class="text-right font-weight-bold">'+fmt(a)+'</td>'
          + '</tr>';
      });
      $('#sett-emp-table tbody').html(empBody);
      $('#sett-emp-total-qty').text(fmtQty(eSumQ));
      $('#sett-emp-total-amount').text(fmt(eSumA));
    })
    .fail(function() {
      $('#sett-summary-cards').hide();
      $('#sett-ops-table').closest('.row').hide();
      $('#sett-empty').show();
    });
}

function calcSettlement() {
  var btn = $('#btn-calc-settlement');
  btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Calculando...');

  $.ajax({
    url: window.PROD.routes.settlementCalculate,
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    type: 'POST',
  }).done(function(resp) {
    toastr.success(resp.message);
    loadSettlementData();
  }).fail(function(e) {
    toastr.error(e.responseJSON?.message || 'Error calculando liquidación.');
  }).always(function() {
    btn.prop('disabled', false).html('<i class="fas fa-calculator mr-1"></i> Calcular Liquidación');
  });
}
