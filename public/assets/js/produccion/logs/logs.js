$(function(){
  toastr.options={ closeButton:true, positionClass:"toast-bottom-right", timeOut:"5000" };
  loadOrdersFilter();
  loadEmployees();
  Cargar();
});

let logsTable=null;

function Cargar(){
  if ($.fn.DataTable.isDataTable('#logs-table')) $('#logs-table').DataTable().destroy();

  logsTable = $('#logs-table').DataTable({
    language:{ url:"/assets/js/spanish.json" },
    responsive:true,
    dom:"<'row'<'col-sm-6'B><'col-sm-6'f>>" +
        "<'row'<'col-sm-12'ltr>>" +
        "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    buttons:[{ extend:'excel', className:'btn btn-success', text:'<i class="far fa-file-excel"></i>', exportOptions:{ columns:':not(.exclude)'} }],
    ajax:{
      url:'/admin/admin.produccion.logs.index',
      data:function(d){ d.order_id = $('#filter_order_id').val(); }
    },
    columns:[
      { data:'DT_RowIndex', className:'exclude', orderable:false, searchable:false },
      { data:'id', className:'exclude' },
      { data:'work_date' },
      { data:'shift' },
      { data:'order_code' },
      { data:'producto' },
      { data:'operacion' },
      { data:'empleado' },
      { data:'qty', className:'text-right' },
      { data:'rejected_qty', className:'text-right' },
      { data:'accepted_qty', className:'text-right' },
      { data:'acciones', className:'text-center exclude', orderable:false, searchable:false },
    ],
    order:[[1,'desc']]
  });
}

function applyLogFilters(){ logsTable?.ajax.reload(); }

function loadOrdersFilter(){
  $.get('/admin/admin.produccion.orders.list', (resp)=>{
    const $s=$('#filter_order_id');
    $s.empty().append('<option value="">-- Todas --</option>');
    (resp.data||[]).forEach(i=>$s.append(`<option value="${i.id}">${i.text}</option>`));
  });
}

function loadEmployees(){
  $.get('/admin/admin.produccion.employees.list', (resp)=>{
    const $s=$('#employee_id');
    $s.empty().append('<option value="">Seleccione...</option>');
    (resp.data||[]).forEach(i=>$s.append(`<option value="${i.id}">${i.text}</option>`));
  });
}

function limpiarValidaciones(){
  const fields = [
    'order_id',
    'order_operation_id',
    'employee_id',
    'qty',
    'work_date'
  ];
  fields.forEach(f => $('#error_' + f).text(''));
}


function cleanInput(){
  ['id','order_id','order_operation_id','work_date','notes','qty','rejected_qty'].forEach(f=>$('#'+f).val(''));
  $('#shift').val('');
  $('#employee_id').val('');
  $('#rejected_qty').val(0);
}

function regLog(){
    $('#ModalLog').modal('show');
    $('#logModalTitle').text('Registrar Log');

    cleanInput();
    const orderId = $('#filter_order_id').val();
    if(orderId) {
        document.getElementById('order_id').value = orderId; // setear directamente el valor del input
    }

    limpiarValidaciones();
    clearOperationsSelect(); // importante
    loadOrderOperations(orderId); // cargar operaciones si ya hay una orden seleccionada en el filtro
    $('.modal-footer').html(
        '<button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
        '<button class="btn btn-primary" onclick="registerLog()">Guardar</button>'
    );
}

function registerLog(){
  limpiarValidaciones();
  const fd=new FormData();
  ['order_id','order_operation_id','employee_id','work_date','shift','qty','rejected_qty','notes']
    .forEach(f=>fd.append(f,$('#'+f).val()));

  $.ajax({
    url:'/admin/admin.produccion.logs.store',
    headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
    type:'POST', data:fd, contentType:false, processData:false
  }).then(r=>{
    $('#ModalLog').modal('hide');
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

function showCustomLog(id){
  $.get("/admin/admin.produccion.logs.edit/" + id, (response) => {
    const log = response.data;

    // setear campos básicos primero
    $('#id').val(log.id);
    $('#order_id').val(log.order_id);
    $('#employee_id').val(log.employee_id);
    $('#qty').val(log.qty);
    $('#work_date').val(log.work_date);

    // IMPORTANTE: cargar operaciones por order_id y luego seleccionar
    loadOrderOperations(log.order_id, log.order_operation_id);
  });
}

function upLog(id){
  $('#ModalLog').modal('show');
  $('#logModalTitle').text('Editar Log');
  cleanInput(); limpiarValidaciones();

  $.get('/admin/admin.produccion.logs.edit/'+id, (resp)=>{
    const l=resp.data;
    $('#id').val(l.id);
    $('#order_id').val(l.order_id);
    $('#order_operation_id').val(l.order_operation_id);
    $('#employee_id').val(l.employee_id);
    $('#work_date').val(l.work_date);
    $('#shift').val(l.shift);
    $('#qty').val(l.qty);
    $('#rejected_qty').val(l.rejected_qty);
    $('#notes').val(l.notes);
  });

  $('.modal-footer').html(
    '<button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
    '<button class="btn btn-primary" onclick="updateLog('+id+')">Actualizar</button>'
  );
}

function updateLog(id){
  limpiarValidaciones();
  const fd=new FormData();
  ['order_id','order_operation_id','employee_id','work_date','shift','qty','rejected_qty','notes']
    .forEach(f=>fd.append(f,$('#'+f).val()));

  $.ajax({
    url:'/admin/admin.produccion.logs.update/'+id,
    headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content'),'X-HTTP-Method-Override':'POST'},
    type:'POST', data:fd, contentType:false, processData:false
  }).then(r=>{
    $('#ModalLog').modal('hide');
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


function clearOperationsSelect(){
  $('#order_operation_id').empty().append('<option value="">Seleccione una orden...</option>');
}

function loadOrderOperations(orderId, selectedId = null){
  if(!orderId){
    clearOperationsSelect();
    return $.Deferred().resolve().promise();
  }

  $('#order_operation_id').prop('disabled', true);
  $('#order_operation_id').empty().append('<option value="">Cargando...</option>');

  return $.get('/admin/admin.produccion.orders.operations.list', { order_id: orderId })
    .then((resp)=>{
      const $s = $('#order_operation_id');
      $s.empty().append('<option value="">Seleccione...</option>');
      (resp.data || []).forEach(i => $s.append(`<option value="${i.id}">${i.text}</option>`));

      if(selectedId){
        $s.val(String(selectedId));
      }
      $s.prop('disabled', false);
    })
    .catch(()=>{
      $('#order_operation_id').empty().append('<option value="">No se pudieron cargar operaciones</option>');
      $('#order_operation_id').prop('disabled', false);
    });
}

// cuando cambie order_id (input), recargar operaciones
// $('#order_id').on('change keyup', function(){
//   const orderId = $(this).val();
//   loadOrderOperations(orderId);
// });

// $(document).on('change', '#order_id', function(){
//   const orderId = $(this).val();
//   loadOrderOperations(orderId);
// });
