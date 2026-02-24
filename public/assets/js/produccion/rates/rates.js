$(function () {
  toastr.options = { closeButton:true, positionClass:"toast-bottom-right", timeOut:"5000" };
  loadProducts();
  loadOperations();
  Cargar();
});

function Cargar() {
  if ($.fn.DataTable.isDataTable('#rates-table')) $('#rates-table').DataTable().destroy();

  $('#rates-table').DataTable({
    language: { url: "/assets/js/spanish.json" },
    responsive: true,
    dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
         "<'row'<'col-sm-12'ltr>>" +
         "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    buttons: [{
      extend: 'excel',
      className: 'btn btn-success',
      text: '<i class="far fa-file-excel"></i>',
      exportOptions: { columns: ':not(.exclude)' }
    }],
    ajax: '/admin/admin.produccion.rates.index',
    columns: [
      { data: 'DT_RowIndex', className:'exclude', orderable:false, searchable:false },
      { data: 'id', className:'exclude' },
      { data: 'producto' },
      { data: 'operacion' },
      { data: 'amount', className:'text-right' },
      { data: 'valid_from' },
      { data: 'valid_to' },
      { data: 'is_active', className:'text-center' },
      { data: 'acciones', className:'exclude text-center', orderable:false, searchable:false },
    ],
    order: [[1, "desc"]],
  });
}

function loadProducts(){
  $.get('/admin/admin.produccion.products.list', (resp)=>{
    const $s = $('#product_id');
    $s.empty().append('<option value="">Seleccione...</option>');
    (resp.data||[]).forEach(i => $s.append(`<option value="${i.id}">${i.text}</option>`));
  });
}
function loadOperations(){
  $.get('/admin/admin.produccion.operations.list', { ajax: 1 }, (resp)=>{
    // OJO: esta ruta devuelve DataTables si es ajax; mejor usamos un endpoint list.
    // Como no lo tenemos, hacemos fallback: si resp.data viene.
  });
  // Mejor: crear endpoint list, pero para no frenarte:
  $.get('/admin/admin.produccion.operations.list', (resp)=>{
    const $s = $('#operation_id');
    $s.empty().append('<option value="">Seleccione...</option>');
    (resp.data||[]).forEach(i => $s.append(`<option value="${i.id}">${i.text}</option>`));
  }).fail(()=>{
    // Si aún no creaste operations.list, al final te dejo el endpoint.
  });
}

function limpiarValidaciones(){
  ['product_id','operation_id','amount','valid_from','valid_to','is_active'].forEach(f=>$('#error_'+f).text(''));
}
function cleanInput(){
  $('#id').val('');
  $('#product_id').val('');
  $('#operation_id').val('');
  $('#amount').val('');
  $('#valid_from').val('');
  $('#valid_to').val('');
  $('#is_active').prop('checked', true);
}

function regRate(){
  $('#ModalRate').modal('show');
  $('#rateModalTitle').text('Registrar Tarifa');
  cleanInput(); limpiarValidaciones();
  $(".modal-footer").html(
    '<button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
    '<button class="btn btn-primary" onclick="storeRate()">Guardar</button>'
  );
}

function storeRate(){
  limpiarValidaciones();
  const fd = new FormData();
  ['product_id','operation_id','amount','valid_from','valid_to'].forEach(f=>fd.append(f,$('#'+f).val()));
  fd.append('is_active', $('#is_active').is(':checked') ? 1 : 0);

  $.ajax({
    url:'/admin/admin.produccion.rates.store',
    headers:{ 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    type:'POST',
    data:fd,
    contentType:false,
    processData:false
  }).then(r=>{
    $('#ModalRate').modal('hide');
    toastr.success(r.message||'Tarifa creada');
    Cargar();
  }).catch(e=>{
    const arr = e.responseJSON || {};
    if(e.status===422 && arr.errors){
      Object.keys(arr.errors).forEach(k=>$('#error_'+k).text(arr.errors[k][0]));
      toastr.warning('Revisa los campos.');
    } else toastr.error(arr.message||'Error');
  });
}

function upRate(id){
  $('#ModalRate').modal('show');
  $('#rateModalTitle').text('Editar Tarifa');
  cleanInput(); limpiarValidaciones();

  $.get('/admin/admin.produccion.rates.edit/'+id, (resp)=>{
    const r = resp.data;
    $('#id').val(r.id);
    $('#product_id').val(r.product_id);
    $('#operation_id').val(r.operation_id);
    $('#amount').val(r.amount);
    $('#valid_from').val(r.valid_from);
    $('#valid_to').val(r.valid_to);
    $('#is_active').prop('checked', r.is_active == 1);
  });

  $(".modal-footer").html(
    '<button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
    '<button class="btn btn-primary" onclick="updateRate('+id+')">Actualizar</button>'
  );
}

function updateRate(id){
  limpiarValidaciones();
  const fd = new FormData();
  ['product_id','operation_id','amount','valid_from','valid_to'].forEach(f=>fd.append(f,$('#'+f).val()));
  fd.append('is_active', $('#is_active').is(':checked') ? 1 : 0);

  $.ajax({
    url:'/admin/admin.produccion.rates.update/'+id,
    headers:{ 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'X-HTTP-Method-Override': 'POST' },
    type:'POST',
    data:fd,
    contentType:false,
    processData:false
  }).then(r=>{
    $('#ModalRate').modal('hide');
    toastr.success(r.message||'Tarifa actualizada');
    Cargar();
  }).catch(e=>{
    const arr = e.responseJSON || {};
    if(e.status===422 && arr.errors){
      Object.keys(arr.errors).forEach(k=>$('#error_'+k).text(arr.errors[k][0]));
      toastr.warning('Revisa los campos.');
    } else toastr.error(arr.message||'Error');
  });
}
