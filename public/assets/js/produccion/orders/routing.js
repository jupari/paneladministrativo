let routingTable = null;

$(function(){
  loadRoutingOrders();
  loadOperationsForRouting();

  // opcional: al cambiar orden en select, recargar
  $('#routing_order_id').change(function(){ /* no auto, para no molestar */ });
});

function loadRoutingOrders(){
  $.get('/admin/admin.produccion.orders.list', (resp)=>{
    const $s=$('#routing_order_id');
    $s.empty().append('<option value="">-- Seleccione --</option>');
    (resp.data||[]).forEach(i=>$s.append(`<option value="${i.id}">${i.text}</option>`));
  });
}

function loadOperationsForRouting(){
  $.get('/admin/admin.produccion.operations.list', (resp)=>{
    const $s=$('#routing_operation_id');
    $s.empty().append('<option value="">Seleccione...</option>');
    (resp.data||[]).forEach(i=>$s.append(`<option value="${i.id}">${i.text}</option>`));
  });
}

function loadRouting(){
  const orderId = $('#routing_order_id').val();
  if(!orderId){ toastr.warning('Seleccione una orden'); return; }

  if ($.fn.DataTable.isDataTable('#routing-table')) $('#routing-table').DataTable().destroy();

  routingTable = $('#routing-table').DataTable({
    language:{ url:"/assets/js/spanish.json" },
    responsive:true,
    ajax:'/admin/admin.produccion.orders.operations/' + orderId,
    columns:[
      { data:'DT_RowIndex', className:'exclude', orderable:false, searchable:false },
      { data:'id', className:'exclude' },
      { data:'operacion' },
      { data:'seq' },
      { data:'qty_per_unit', className:'text-right' },
      { data:'required_qty', className:'text-right' },
      { data:'status', className:'text-center' },
      { data:'acciones', className:'exclude text-center', orderable:false, searchable:false },
    ],
    order:[[3,'asc']]
  });
}

function regRouting(){
  const orderId = $('#routing_order_id').val();
  if(!orderId){ toastr.warning('Seleccione una orden'); return; }

  $('#ModalRouting').modal('show');
  $('#routingModalTitle').text('Agregar Operación a la Orden');
  $('#routing_id').val('');
  $('#routing_operation_id').val('');
  $('#routing_seq').val(1);
  $('#routing_qty_per_unit').val(1); // tu regla
  $('#routing_status').val('PENDING');
  limpiarValidacionesRouting();

  $('.modal-footer').html(
    '<button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
    '<button class="btn btn-primary" onclick="storeRouting()">Guardar</button>'
  );
}

function limpiarValidacionesRouting(){
  ['operation_id','seq','qty_per_unit','status'].forEach(f=>$('#error_'+f).text(''));
}

function storeRouting(){
  limpiarValidacionesRouting();

  const orderId = $('#routing_order_id').val();
  const fd = new FormData();
  fd.append('operation_id', $('#routing_operation_id').val());
  fd.append('seq', $('#routing_seq').val());
  fd.append('qty_per_unit', $('#routing_qty_per_unit').val());
  fd.append('status', $('#routing_status').val());

  $.ajax({
    url:'/admin/admin.produccion.orders.operations.store/' + orderId,
    headers:{ 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    type:'POST',
    data:fd, contentType:false, processData:false
  }).then(r=>{
    $('#ModalRouting').modal('hide');
    toastr.success(r.message||'Guardado');
    loadRouting();
  }).catch(e=>{
    const arr=e.responseJSON||{};
    if(e.status===422 && arr.errors){
      Object.keys(arr.errors).forEach(k=>$('#error_'+k).text(arr.errors[k][0]));
      toastr.warning('Revisa los campos.');
    } else toastr.error(arr.message||'Error');
  });
}

function upRouting(id){
  $('#ModalRouting').modal('show');
  $('#routingModalTitle').text('Editar Operación de la Orden');
  limpiarValidacionesRouting();

  $.get('/admin/admin.produccion.orders.operations.edit/' + id, (resp)=>{
    const r = resp.data;
    $('#routing_id').val(r.id);
    $('#routing_operation_id').val(r.operation_id);
    $('#routing_seq').val(r.seq);
    $('#routing_qty_per_unit').val(r.qty_per_unit);
    $('#routing_status').val(r.status);
  });

  $('.modal-footer').html(
    '<button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
    '<button class="btn btn-primary" onclick="updateRouting('+id+')">Actualizar</button>'
  );
}

function updateRouting(id){
  limpiarValidacionesRouting();
  const fd = new FormData();
  fd.append('operation_id', $('#routing_operation_id').val());
  fd.append('seq', $('#routing_seq').val());
  fd.append('qty_per_unit', $('#routing_qty_per_unit').val());
  fd.append('status', $('#routing_status').val());

  $.ajax({
    url:'/admin/admin.produccion.orders.operations.update/' + id,
    headers:{ 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'X-HTTP-Method-Override':'POST' },
    type:'POST',
    data:fd, contentType:false, processData:false
  }).then(r=>{
    $('#ModalRouting').modal('hide');
    toastr.success(r.message||'Actualizado');
    loadRouting();
  }).catch(e=>{
    const arr=e.responseJSON||{};
    if(e.status===422 && arr.errors){
      Object.keys(arr.errors).forEach(k=>$('#error_'+k).text(arr.errors[k][0]));
      toastr.warning('Revisa los campos.');
    } else toastr.error(arr.message||'Error');
  });
}
