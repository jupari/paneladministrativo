$(function () {
  toastr.options = { closeButton:true, positionClass:"toast-bottom-right", timeOut:"5000" };
  loadProducts();
  Cargar();
});

function Cargar(){
  if ($.fn.DataTable.isDataTable('#orders-table')) $('#orders-table').DataTable().destroy();

  $('#orders-table').DataTable({
    language:{ url:"/assets/js/spanish.json" },
    responsive:true,
    dom:"<'row'<'col-sm-6'B><'col-sm-6'f>>" +
        "<'row'<'col-sm-12'ltr>>" +
        "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    buttons:[{ extend:'excel', className:'btn btn-success', text:'<i class="far fa-file-excel"></i>', exportOptions:{ columns:':not(.exclude)'} }],
    ajax:'/admin/admin.produccion.orders',
    columns:[
      { data:'DT_RowIndex', className:'exclude', orderable:false, searchable:false },
      { data:'id', className:'exclude' },
      { data:'code' },
      { data:'producto' },
      { data:'objective_qty' },
      { data:'start_date' },
      { data:'end_date' },
      { data:'status', className:'text-center' },
      { data:'acciones', className:'text-center exclude', orderable:false, searchable:false },
    ],
    order:[[1,'desc']]
  });
}

function loadProducts(){
  $.get('/admin/admin.produccion.products.list', (resp)=>{
    const $s = $('#product_id');
    $s.empty().append('<option value="">Seleccione...</option>');
    (resp.data||[]).forEach(i => $s.append(`<option value="${i.id}">${i.text}</option>`));
  });
}

function limpiarValidaciones(){
  ['code','product_id','objective_qty','start_date','end_date','status','notes'].forEach(f=>$('#error_'+f).text(''));
}

function cleanInput(){
  ['id','code','objective_qty','start_date','end_date','notes'].forEach(f=>$('#'+f).val(''));
  $('#status').val('DRAFT');
  $('#product_id').val('');
}

function regOrder(){
  $('#ModalOrder').modal('show');
  $('#orderModalTitle').text('Registrar Orden');
  cleanInput(); limpiarValidaciones();
  $('.modal-footer').html(
    '<button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
    '<button class="btn btn-primary" onclick="registerOrder()">Guardar</button>'
  );
}

function registerOrder(){
  limpiarValidaciones();
  const fd = new FormData();
  ['code','product_id','objective_qty','start_date','end_date','status','notes'].forEach(f=>fd.append(f, $('#'+f).val()));

  $.ajax({
    url:'/admin/admin.produccion.orders.store',
    headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
    type:'POST', data:fd, contentType:false, processData:false
  }).then(r=>{
    $('#ModalOrder').modal('hide');
    toastr.success(r.message||'Guardado');
    Cargar();
  }).catch(e=>{
    const arr=e.responseJSON||{};
    if(e.status===422 && arr.errors){
      Object.keys(arr.errors).forEach(k=>$('#error_'+k).text(arr.errors[k][0]));
      toastr.warning('Revisa los campos.');
    } else toastr.error(arr.message||'Error');
  });
}

function upOrder(id){
  $('#ModalOrder').modal('show');
  $('#orderModalTitle').text('Editar Orden');
  cleanInput(); limpiarValidaciones();

  $.get('/admin/admin.produccion.orders.edit/'+id, (resp)=>{
    const o=resp.data;
    $('#id').val(o.id);
    $('#code').val(o.code);
    $('#product_id').val(o.product_id);
    $('#objective_qty').val(o.objective_qty);
    $('#start_date').val(o.start_date);
    $('#end_date').val(o.end_date);
    $('#status').val(o.status);
    $('#notes').val(o.notes);
  });

  $('.modal-footer').html(
    '<button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
    '<button class="btn btn-primary" onclick="updateOrder('+id+')">Actualizar</button>'
  );
}

function updateOrder(id){
  limpiarValidaciones();
  const fd = new FormData();
  ['code','product_id','objective_qty','start_date','end_date','status','notes'].forEach(f=>fd.append(f, $('#'+f).val()));

  $.ajax({
    url:'/admin/admin.produccion.orders.update/'+id,
    headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content'),'X-HTTP-Method-Override':'POST'},
    type:'POST', data:fd, contentType:false, processData:false
  }).then(r=>{
    $('#ModalOrder').modal('hide');
    toastr.success(r.message||'Actualizado');
    Cargar();
  }).catch(e=>{
    const arr=e.responseJSON||{};
    if(e.status===422 && arr.errors){
      Object.keys(arr.errors).forEach(k=>$('#error_'+k).text(arr.errors[k][0]));
      toastr.warning('Revisa los campos.');
    } else toastr.error(arr.message||'Error');
  });
}
