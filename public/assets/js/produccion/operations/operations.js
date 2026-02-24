$(function () {
  toastr.options = { closeButton:true, positionClass:"toast-bottom-right", timeOut:"5000" };
  Cargar();
});

function Cargar() {
  if ($.fn.DataTable.isDataTable('#operations-table')) {
    $('#operations-table').DataTable().destroy();
  }

  $('#operations-table').DataTable({
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
    ajax: '/admin/admin.produccion.operations',
    columns: [
      { data: 'DT_RowIndex', className:'exclude', orderable:false, searchable:false },
      { data: 'id', className:'exclude' },
      { data: 'code' },
      { data: 'name' },
      { data: 'description' },
      { data: 'is_active', className:'text-center' },
      { data: 'created_at' },
      { data: 'acciones', className:'exclude text-center', orderable:false, searchable:false },
    ],
    order: [[1, "desc"]],
    pageLength: 10,
    lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "Todo(s)"]],
  });
}

function limpiarValidaciones() {
  ['code','name','description','is_active'].forEach(f => $('#error_'+f).text(''));
}
function cleanInput() {
  $('#id').val('');
  $('#code').val('');
  $('#name').val('');
  $('#description').val('');
  $('#is_active').prop('checked', true);
}

function regOperation() {
  $('#ModalOperation').modal('show');
  $('#operationModalTitle').text('Registrar Operación');
  cleanInput(); limpiarValidaciones();
  $(".modal-footer").html(
    '<button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
    '<button class="btn btn-primary" onclick="storeOperation()">Guardar</button>'
  );
}

function storeOperation() {
  limpiarValidaciones();

  const fd = new FormData();
  fd.append('code', $('#code').val());
  fd.append('name', $('#name').val());
  fd.append('description', $('#description').val());
  fd.append('is_active', $('#is_active').is(':checked') ? 1 : 0);

  $.ajax({
    url: '/admin/admin.produccion.operations.store',
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    type: 'POST',
    data: fd,
    contentType: false,
    processData: false,
  }).then(r => {
    $('#ModalOperation').modal('hide');
    toastr.success(r.message || 'Operación creada');
    Cargar();
  }).catch(e => {
    const arr = e.responseJSON || {};
    if (e.status === 422 && arr.errors) {
      Object.keys(arr.errors).forEach(k => $('#error_'+k).text(arr.errors[k][0]));
      toastr.warning('Revisa los campos.');
    } else {
      toastr.error(arr.message || 'Error');
    }
  });
}

function upOperation(id) {
  $('#ModalOperation').modal('show');
  $('#operationModalTitle').text('Editar Operación');
  cleanInput(); limpiarValidaciones();

  $.get('/admin/admin.produccion.operations.edit/' + id, (resp) => {
    const o = resp.data;
    $('#id').val(o.id);
    $('#code').val(o.code);
    $('#name').val(o.name);
    $('#description').val(o.description || '');
    $('#is_active').prop('checked', o.is_active == 1);
  });

  $(".modal-footer").html(
    '<button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
    '<button class="btn btn-primary" onclick="updateOperation('+id+')">Actualizar</button>'
  );
}

function updateOperation(id) {
  limpiarValidaciones();

  const fd = new FormData();
  fd.append('code', $('#code').val());
  fd.append('name', $('#name').val());
  fd.append('description', $('#description').val());
  fd.append('is_active', $('#is_active').is(':checked') ? 1 : 0);

  $.ajax({
    url: '/admin/admin.produccion.operations.update/' + id,
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
      'X-HTTP-Method-Override': 'POST'
    },
    type: 'POST',
    data: fd,
    contentType: false,
    processData: false,
  }).then(r => {
    $('#ModalOperation').modal('hide');
    toastr.success(r.message || 'Operación actualizada');
    Cargar();
  }).catch(e => {
    const arr = e.responseJSON || {};
    if (e.status === 422 && arr.errors) {
      Object.keys(arr.errors).forEach(k => $('#error_'+k).text(arr.errors[k][0]));
      toastr.warning('Revisa los campos.');
    } else {
      toastr.error(arr.message || 'Error');
    }
  });
}
