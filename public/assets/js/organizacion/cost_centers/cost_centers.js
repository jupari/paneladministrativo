$(function () {
  toastr.options = { closeButton:true, positionClass:"toast-bottom-right", timeOut:"5000" };

  initParentSelect();
  loadParents();
  cargarCC();
});

function initParentSelect(){
  $('#parent_id').select2({
    placeholder: '(Sin padre)',
    width: '100%',
    dropdownParent: $('#ModalCostCenter'),
    allowClear: true
  });
}

function loadParents(){
  $.get('admin.organization.cost-centers.list', (resp)=>{
    const $s = $('#parent_id');
    $s.empty().append(new Option('(Sin padre)', '', false, false));
    (resp.data||[]).forEach(i => $s.append(new Option(i.text, i.id, false, false)));
    $s.trigger('change');
  });
}

function cargarCC(){
  if ($.fn.DataTable.isDataTable('#cc-table')) $('#cc-table').DataTable().destroy();

  $('#cc-table').DataTable({
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
    ajax: 'admin.organization.cost-centers',
    columns: [
      { data:'DT_RowIndex', className:'exclude', orderable:false, searchable:false },
      { data:'id', className:'exclude' },
      { data:'code' },
      { data:'name' },
      { data:'parent' },
      { data:'is_active', className:'text-center' },
      { data:'created_at' },
      { data:'acciones', className:'exclude text-center', orderable:false, searchable:false },
    ],
    order: [[1,'desc']],
  });
}

function limpiarValidacionesCC(){
  ['code','name','description','parent_id','is_active'].forEach(f=>$('#error_'+f).text(''));
}

function cleanCC(){
  $('#id').val('');
  $('#code').val('');
  $('#name').val('');
  $('#description').val('');
  $('#parent_id').val('').trigger('change');
  $('#is_active').prop('checked', true);
}

function regCostCenter(){
  $('#ModalCostCenter').modal('show');
  $('#ccModalTitle').text('Registrar Centro de Costo');
  cleanCC(); limpiarValidacionesCC();

  $('.modal-footer').html(
    '<button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
    '<button class="btn btn-primary" onclick="storeCC()">Guardar</button>'
  );
}

function storeCC(){
  limpiarValidacionesCC();

  const fd = new FormData();
  fd.append('code', $('#code').val());
  fd.append('name', $('#name').val());
  fd.append('description', $('#description').val());
  fd.append('parent_id', $('#parent_id').val() || '');
  fd.append('is_active', $('#is_active').is(':checked') ? 1 : 0);

  $.ajax({
    url: 'admin.organization.cost-centers.store',
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    type: 'POST',
    data: fd,
    contentType: false,
    processData: false,
  }).then(r=>{
    $('#ModalCostCenter').modal('hide');
    toastr.success(r.message || 'Creado');
    loadParents();
    cargarCC();
  }).catch(e=>{
    const arr = e.responseJSON || {};
    if(e.status===422 && arr.errors){
      Object.keys(arr.errors).forEach(k=>$('#error_'+k).text(arr.errors[k][0]));
      toastr.warning('Revisa los campos.');
    } else toastr.error(arr.message || 'Error');
  });
}

function upCostCenter(id){
  $('#ModalCostCenter').modal('show');
  $('#ccModalTitle').text('Editar Centro de Costo');
  cleanCC(); limpiarValidacionesCC();

  $.get('admin.organization.cost-centers.edit', { id: id }, (resp)=>{
    const c = resp.data;
    $('#id').val(c.id);
    $('#code').val(c.code);
    $('#name').val(c.name);
    $('#description').val(c.description || '');
    $('#parent_id').val(c.parent_id || '').trigger('change');
    $('#is_active').prop('checked', c.is_active ? true : false);
  });

  $('.modal-footer').html(
    '<button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
    '<button class="btn btn-primary" onclick="updateCC('+id+')">Actualizar</button>'
  );
}

function updateCC(id){
  limpiarValidacionesCC();

  const fd = new FormData();
  fd.append('code', $('#code').val());
  fd.append('name', $('#name').val());
  fd.append('description', $('#description').val());
  fd.append('parent_id', $('#parent_id').val() || '');
  fd.append('is_active', $('#is_active').is(':checked') ? 1 : 0);

  $.ajax({
    url: 'admin.organization.cost-centers.update/'+id,
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
      'X-HTTP-Method-Override': 'POST'
    },
    type: 'POST',
    data: fd,
    contentType: false,
    processData: false,
  }).then(r=>{
    $('#ModalCostCenter').modal('hide');
    toastr.success(r.message || 'Actualizado');
    loadParents();
    cargarCC();
  }).catch(e=>{
    const arr = e.responseJSON || {};
    if(e.status===422 && arr.errors){
      Object.keys(arr.errors).forEach(k=>$('#error_'+k).text(arr.errors[k][0]));
      toastr.warning('Revisa los campos.');
    } else toastr.error(arr.message || 'Error');
  });
}
