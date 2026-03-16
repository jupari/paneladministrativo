$(function () {
  toastr.options = { closeButton:true, positionClass:"toast-bottom-right", timeOut:"5000" };
  loadWorkshops();
});

function loadWorkshops() {
  if ($.fn.DataTable.isDataTable('#workshops-table')) {
    $('#workshops-table').DataTable().destroy();
  }

  $('#workshops-table').DataTable({
    language: { url: "/assets/js/spanish.json" },
    responsive: true,
    ajax: '/admin/admin.produccion.workshops.index',
    columns: [
      { data: 'DT_RowIndex', className:'exclude', orderable:false, searchable:false },
      { data: 'id', className:'exclude' },
      { data: 'code' },
      { data: 'name' },
      { data: 'address' },
      { data: 'coordinator_name' },
      { data: 'coordinator_phone' },
      { data: 'devices_count', className:'text-center' },
      { data: 'status', className:'text-center' },
      { data: 'last_sync_at' },
      { data: 'acciones', className:'exclude text-center', orderable:false, searchable:false },
    ],
    order: [[1, 'desc']],
    pageLength: 10,
  });
}

function clearErrors() {
  ['code','name','address','coordinator_name','coordinator_phone','status'].forEach(f => $('#error_'+f).text(''));
}

function clearForm() {
  $('#workshop_id').val('');
  $('#workshop_code').val('');
  $('#workshop_name').val('');
  $('#workshop_address').val('');
  $('#workshop_coordinator_name').val('');
  $('#workshop_coordinator_phone').val('');
  $('#workshop_status').val('active');
}

function createWorkshop() {
  clearForm(); clearErrors();
  $('#workshopModalTitle').text('Nuevo taller');
  $('#WorkshopModal').modal('show');
  $('.modal-footer').html(
    '<button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
    '<button class="btn btn-primary" onclick="storeWorkshop()">Guardar</button>'
  );
}

function storeWorkshop() {
  clearErrors();

  $.ajax({
    url: '/admin/admin.produccion.workshops.store',
    type: 'POST',
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    data: {
      code: $('#workshop_code').val(),
      name: $('#workshop_name').val(),
      address: $('#workshop_address').val(),
      coordinator_name: $('#workshop_coordinator_name').val(),
      coordinator_phone: $('#workshop_coordinator_phone').val(),
      status: $('#workshop_status').val(),
    }
  }).done(function (r) {
    $('#WorkshopModal').modal('hide');
    toastr.success(r.message || 'Taller creado.');
    loadWorkshops();
  }).fail(function (e) {
    const arr = e.responseJSON || {};
    if (e.status === 422 && arr.errors) {
      Object.keys(arr.errors).forEach(k => $('#error_'+k).text(arr.errors[k][0]));
      toastr.warning('Revisa los campos.');
    } else {
      toastr.error(arr.message || 'Error al crear taller.');
    }
  });
}

function editWorkshop(id) {
  clearForm(); clearErrors();
  $('#workshopModalTitle').text('Editar taller');
  $('#WorkshopModal').modal('show');

  $.get('/admin/admin.produccion.workshops.edit/' + id, function (resp) {
    const w = resp.data;
    $('#workshop_id').val(w.id);
    $('#workshop_code').val(w.code);
    $('#workshop_name').val(w.name);
    $('#workshop_address').val(w.address || '');
    $('#workshop_coordinator_name').val(w.coordinator_name || '');
    $('#workshop_coordinator_phone').val(w.coordinator_phone || '');
    $('#workshop_status').val(w.status || 'active');
  });

  $('.modal-footer').html(
    '<button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
    '<button class="btn btn-primary" onclick="updateWorkshop('+id+')">Actualizar</button>'
  );
}

function updateWorkshop(id) {
  clearErrors();

  $.ajax({
    url: '/admin/admin.produccion.workshops.update/' + id,
    type: 'POST',
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    data: {
      code: $('#workshop_code').val(),
      name: $('#workshop_name').val(),
      address: $('#workshop_address').val(),
      coordinator_name: $('#workshop_coordinator_name').val(),
      coordinator_phone: $('#workshop_coordinator_phone').val(),
      status: $('#workshop_status').val(),
    }
  }).done(function (r) {
    $('#WorkshopModal').modal('hide');
    toastr.success(r.message || 'Taller actualizado.');
    loadWorkshops();
  }).fail(function (e) {
    const arr = e.responseJSON || {};
    if (e.status === 422 && arr.errors) {
      Object.keys(arr.errors).forEach(k => $('#error_'+k).text(arr.errors[k][0]));
      toastr.warning('Revisa los campos.');
    } else {
      toastr.error(arr.message || 'Error al actualizar taller.');
    }
  });
}

function toggleWorkshopStatus(id) {
  $.ajax({
    url: '/admin/admin.produccion.workshops.toggle-status/' + id,
    type: 'POST',
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
  }).done(function (r) {
    toastr.success(r.message || 'Estado actualizado.');
    loadWorkshops();
  }).fail(function (e) {
    toastr.error(e.responseJSON?.message || 'No fue posible actualizar estado.');
  });
}
