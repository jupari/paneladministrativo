$(function(){
  toastr.options={ closeButton:true, positionClass:"toast-bottom-right", timeOut:"5000" };
  loadOrders();
  Cargar();
});

let settlementsTable=null;

function Cargar(){
  if ($.fn.DataTable.isDataTable('#settlements-table')) $('#settlements-table').DataTable().destroy();

  settlementsTable = $('#settlements-table').DataTable({
    language:{ url:"/assets/js/spanish.json" },
    responsive:true,
    processing:true,
    serverSide:true,
    dom:"<'row'<'col-sm-6'B><'col-sm-6'f>>" +
        "<'row'<'col-sm-12'ltr>>" +
        "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    buttons:[{ extend:'excel', className:'btn btn-success', text:'<i class="far fa-file-excel"></i>' }],
    ajax:{
      url:'/admin/admin.produccion.settlements.index',
      data:function(d){ d.order_id=$('#filter_order_id').val(); },
      dataSrc:function(json){
        const s=json.summary||null;
        $('#sum_total').text(s ? s.total_pagar : '0,00');
        $('#sum_qty').text(s ? s.total_qty : '0,00');
        return json.data;
      }
    },
    columns:[
      { data:'DT_RowIndex', orderable:false, searchable:false },
      { data:'order_code' },
      { data:'producto' },
      { data:'operacion' },
      { data:'empleado' },
      { data:'qty', className:'text-right' },
      { data:'rate', className:'text-right' },
      { data:'gross_amount', className:'text-right' },
      { data:'status', className:'text-center', orderable:false, searchable:false },
      { data:'updated_at' },
    ],
    order:[[0,'desc']]
  });
}

function loadOrders(){
  $.get('/admin/admin.produccion.orders.list', (resp)=>{
    const $s=$('#filter_order_id');
    $s.empty().append('<option value="">-- Seleccione --</option>');
    (resp.data||[]).forEach(i=>$s.append(`<option value="${i.id}">${i.text}</option>`));
  });
}

function applySettlementFilters(){ settlementsTable?.ajax.reload(); }

function calculateSettlement(){
  const orderId=$('#filter_order_id').val();
  if(!orderId){ toastr.warning('Seleccione una orden'); return; }

  $.ajax({
    url:'/admin/admin.produccion.settlements.calculate/'+orderId,
    headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
    type:'POST',
  }).then(r=>{
    toastr.success(r.message||'Calculado');
    applySettlementFilters();
  }).catch(e=>{
    toastr.error((e.responseJSON||{}).message||'Error');
  });
}

function sendToNomina(){
  const orderId=$('#filter_order_id').val();
  const ps=$('#period_start').val();
  const pe=$('#period_end').val();

  if(!orderId){ toastr.warning('Seleccione una orden'); return; }
  if(!ps || !pe){ toastr.warning('Defina period_start y period_end'); return; }

  const fd=new FormData();
  fd.append('period_start', ps);
  fd.append('period_end', pe);

  $.ajax({
    url:'/admin/admin.produccion.settlements.send_to_nomina/'+orderId,
    headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
    type:'POST',
    data:fd, contentType:false, processData:false
  }).then(r=>{
    toastr.success(r.message||'Enviado a nómina');
    applySettlementFilters();
  }).catch(e=>{
    toastr.error((e.responseJSON||{}).message||'Error');
  });
}
