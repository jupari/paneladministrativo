$(function () {
  initSelect2();
  loadOpsTable();
  loadLogsTable();
});

function initSelect2(){
  // Empleados: ideal un endpoint /admin/empleados/select2
  $('#employee_ids').select2({
    width: '100%',
    placeholder: 'Seleccione empleados',
    ajax: {
      url: '/admin/admin.empleados.select2', // AJUSTA a tu ruta real
      dataType: 'json',
      delay: 250,
      data: params => ({ q: params.term }),
      processResults: data => ({ results: data.results })
    }
  });

  // Operaciones de la orden: endpoint para traer prod_order_operations
  $('#order_operation_id').select2({
    width: '100%',
    placeholder: 'Seleccione operación',
    ajax: {
      url: '/admin/produccion/orders/'+window.PROD.orderId+'/operations/select2', // endpoint sugerido
      dataType: 'json',
      delay: 250,
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
