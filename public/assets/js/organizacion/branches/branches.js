$(function () {
  toastr.options = { closeButton:true, positionClass:"toast-bottom-right", timeOut:"5000" };
  cargarBranches();
});

function cargarBranches(){
  if ($.fn.DataTable.isDataTable('#branches-table')) $('#branches-table').DataTable().destroy();

  $('#branches-table').DataTable({
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
    ajax: 'admin.organization.branches',
    columns: [
      { data:'DT_RowIndex', className:'exclude', orderable:false, searchable:false },
      { data:'id', className:'exclude' },
      { data:'code' },
      { data:'name' },
      { data:'city' },
      { data:'phone' },
      { data:'is_active', className:'text-center' },
      { data:'created_at' },
      { data:'acciones', className:'exclude text-center', orderable:false, searchable:false },
    ],
    order: [[1,'desc']],
  });
}

function limpiarValidacionesBranch(){
  ['code','name','address','city','phone','is_active'].forEach(f=>$('#error_'+f).text(''));
}

function cleanBranch(){
  $('#id').val('');
  $('#code').val('');
  $('#name').val('');
  $('#address').val('');
  $('#city').val('');
  $('#phone').val('');
  $('#is_active').prop('checked', true);
}

function regBranch(){
  $('#ModalBranch').modal('show');
  $('#branchModalTitle').text('Registrar Sucursal');
  cleanBranch();
  limpiarValidacionesBranch();

  $('.modal-footer').html(
    '<button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
    '<button class="btn btn-primary" onclick="storeBranch()">Guardar</button>'
  );
}

function storeBranch(){
  limpiarValidacionesBranch();

  const fd = new FormData();
  fd.append('code', $('#code').val());
  fd.append('name', $('#name').val());
  fd.append('address', $('#address').val());
  fd.append('city', $('#city').val());
  fd.append('phone', $('#phone').val());
  fd.append('is_active', $('#is_active').is(':checked') ? 1 : 0);

  $.ajax({
    url: 'admin.organization.branches.store',
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    type: 'POST',
    data: fd,
    contentType: false,
    processData: false,
  }).then(r=>{
    $('#ModalBranch').modal('hide');
    toastr.success(r.message || 'Creado');
    cargarBranches();
  }).catch(e=>{
    const arr = e.responseJSON || {};
    if(e.status===422 && arr.errors){
      Object.keys(arr.errors).forEach(k=>$('#error_'+k).text(arr.errors[k][0]));
      toastr.warning('Revisa los campos.');
    } else toastr.error(arr.message || 'Error');
  });
}

function upBranch(id){
  $('#ModalBranch').modal('show');
  $('#branchModalTitle').text('Editar Sucursal');
  cleanBranch();
  limpiarValidacionesBranch();

  $.get('admin.organization.branches.edit/' + id, (resp)=>{
    const b = resp.data;
    $('#id').val(b.id);
    $('#code').val(b.code || '');
    $('#name').val(b.name);
    $('#address').val(b.address || '');
    $('#city').val(b.city || '');
    $('#phone').val(b.phone || '');
    $('#is_active').prop('checked', b.is_active ? true : false);
  });

  $('.modal-footer').html(
    '<button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
    '<button class="btn btn-primary" onclick="updateBranch('+id+')">Actualizar</button>'
  );
}

function updateBranch(id){
  limpiarValidacionesBranch();

  const fd = new FormData();
  fd.append('code', $('#code').val());
  fd.append('name', $('#name').val());
  fd.append('address', $('#address').val());
  fd.append('city', $('#city').val());
  fd.append('phone', $('#phone').val());
  fd.append('is_active', $('#is_active').is(':checked') ? 1 : 0);

  $.ajax({
    url: 'admin.organization.branches.update/' + id,
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
      'X-HTTP-Method-Override': 'POST'
    },
    type: 'POST',
    data: fd,
    contentType: false,
    processData: false,
  }).then(r=>{
    $('#ModalBranch').modal('hide');
    toastr.success(r.message || 'Actualizado');
    cargarBranches();
  }).catch(e=>{
    const arr = e.responseJSON || {};
    if(e.status===422 && arr.errors){
      Object.keys(arr.errors).forEach(k=>$('#error_'+k).text(arr.errors[k][0]));
      toastr.warning('Revisa los campos.');
    } else toastr.error(arr.message || 'Error');
  });
}
