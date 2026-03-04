// $(function () {

//     toastr.options = {
//         "closeButton": true,
//         "debug": false,
//         "newestOnTop": false,
//         "progressBar": false,
//         "positionClass": "toast-bottom-right",
//         "preventDuplicates": false,
//         "onclick": null,
//         "showDuration": "300",
//         "hideDuration": "1000",
//         "timeOut": "5000",
//         "extendedTimeOut": "1000",
//         "showEasing": "swing",
//         "hideEasing": "linear",
//         "showMethod": "fadeIn",
//         "hideMethod": "fadeOut"
//     }

//     initNoveltyForm();
//     loadConceptsForNoveltySelect();
//     Cargar();
// });

// function initNoveltyForm() {
//     const $participants = $('#participants');
//     const $linkType = $('#link_type');

//     if ($participants.data('select2')) {
//         $participants.select2('destroy');
//     }

//     $participants.select2({
//         dropdownParent: $('#ModalNovelty'),
//         placeholder: 'Busca y selecciona empleado(s)',
//         width: '100%',
//         multiple: true,
//         language: {
//             noResults: () => 'Sin resultados'
//         },
//         ajax: {
//             url: '/admin/admin.nomina.novelties.participants',
//             dataType: 'json',
//             delay: 250,
//             data: params => ({
//                 search: params.term || '',
//                 link_type: $linkType.val(),
//             }),
//             processResults: resp => {
//                 const items = resp.data || [];
//                 return { results: items.map(i => ({ id: i.id, text: i.text })) };
//             },
//             cache: true
//         }
//     });

//     $linkType.on('change', () => {
//         $('#error_link_type').text('');
//         clearParticipantsSelect();
//     });
// }

// function clearParticipantsSelect() {
//     const $participants = $('#participants');
//     $participants.val(null).trigger('change');
// }

// function setParticipantsSelected(options) {
//     const $participants = $('#participants');
//     $participants.empty();
//     options.forEach(opt => {
//         const option = new Option(opt.text, opt.id, true, true);
//         $participants.append(option);
//     });
//     $participants.trigger('change');
// }

// function Cargar() {

//     if ($.fn.DataTable.isDataTable('#novelties-table')) {
//         $('#novelties-table').DataTable().destroy();
//     }

//     $('#novelties-table').DataTable({
//         language: { "url": "/assets/js/spanish.json" },
//         responsive: true,
//         dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
//             "<'row'<'col-sm-12'ltr>>" +
//             "<'row'<'col-sm-5'i><'col-sm-7'p>>",
//         buttons: [
//             {
//                 extend: 'excel',
//                 className: 'btn btn-success',
//                 exportOptions: { columns: ':not(.exclude)' },
//                 text: '<i class="far fa-file-excel"></i>',
//                 titleAttr: 'Exportar a Excel',
//                 filename: 'nomina_novelties'
//             }
//         ],
//         ajax: '/admin/admin.nomina.novelties.index',
//         columns: [
//             { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false, searchable: false },
//             { data: 'id', name: 'id', className: 'exclude' },
//             { data: 'link_type', name: 'link_type' },
//             { data: 'participant', name: 'participant' }, // sugerido: "Empleado #12" o "Tercero #9"
//             { data: 'concept', name: 'concept' },         // sugerido: "LAB_BASICO - Salario..."
//             { data: 'period', name: 'period' },           // "start a end"
//             { data: 'amount', name: 'amount' },           // formateado
//             { data: 'status', name: 'status', className: 'text-center' }, // badge
//             { data: 'created_at', name: 'created_at' },
//             { data: 'acciones', name: 'acciones', className: 'text-center exclude', orderable: false, searchable: false }
//         ],
//         order: [[1, "desc"]],
//         pageLength: 10,
//         lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "Todo(s)"]],
//     });
// }

// function cleanInput() {
//     const fields = [
//         'id',
//         'link_type',
//         'nomina_concept_id',
//         'quantity',
//         'amount',
//         'period_start',
//         'period_end',
//         'description'
//     ];

//     fields.forEach(f => $('#' + f).val(''));
//     clearParticipantsSelect();
// }

// function limpiarValidaciones() {
//     const fields = [
//         'link_type',
//         'participants',
//         'nomina_concept_id',
//         'quantity',
//         'amount',
//         'period_start',
//         'period_end',
//         'description'
//     ];
//     fields.forEach(f => $('#error_' + f).text(''));
// }

// function regNovelty() {
//     $('#ModalNovelty').modal('show');
//     $('#noveltyModalTitle').html('Registrar Novedad');
//     cleanInput();
//     limpiarValidaciones();

//     const r =
//         '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>' +
//         '<button type="button" class="btn btn-primary" onclick="registerNovelty()">Agregar</button>';

//     $(".modal-footer").html(r);
// }

// function registerNovelty() {

//     const route = "/admin/admin.nomina.novelties.store";

//     const linkType = $('#link_type').val();
//     const participants = $('#participants').val() || [];

//     if (!linkType) {
//         $('#error_link_type').text('Seleccione el tipo de vínculo.');
//         return;
//     }
//     if (!participants.length) {
//         $('#error_participants').text('Seleccione al menos un participante.');
//         return;
//     }

//     const ajax_data = new FormData();
//     ajax_data.append('link_type', linkType);
//     participants.forEach(id => ajax_data.append('participants[]', id));
//     ajax_data.append('nomina_concept_id', $('#nomina_concept_id').val());
//     ajax_data.append('quantity', $('#quantity').val());
//     ajax_data.append('amount', $('#amount').val());
//     ajax_data.append('period_start', $('#period_start').val());
//     ajax_data.append('period_end', $('#period_end').val());
//     ajax_data.append('description', $('#description').val());

//     $.ajax({
//         url: route,
//         headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
//         type: 'POST',
//         dataType: 'json',
//         data: ajax_data,
//         contentType: false,
//         processData: false,
//     }).then(response => {
//         Cargar();
//         $('#ModalNovelty').modal('hide');
//         toastr.success(response.message || 'Novedad creada correctamente.');
//     }).catch(e => {
//         limpiarValidaciones();
//         const arr = e.responseJSON || {};
//         if (e.status === 422 && arr.errors) {
//             $.each(arr.errors, function (key, value) {
//                 $('#error_' + key).text(value[0]);
//             });
//             toastr.warning('No fue posible guardar el registro, revisar los errores en los campos.');
//         } else if (e.status === 403) {
//             toastr.warning(arr.message || 'No autorizado.');
//         } else {
//             toastr.error(arr.message || 'Error inesperado.');
//         }
//     });
// }

// function showCustomNovelty(id) {
//     $.get("/admin/admin.nomina.novelties.edit/" + id, (response) => {
//         const row = response.data;

//         $('#id').val(row.id);
//         $('#link_type').val(row.link_type);
//         if (row.participant_id) {
//             setParticipantsSelected([{
//                 id: row.participant_id,
//                 text: row.participant_label || `Participante #${row.participant_id}`
//             }]);
//         }
//         $('#nomina_concept_id').val(row.nomina_concept_id).change();
//         $('#quantity').val(row.quantity ?? '');
//         $('#amount').val(row.amount ?? '');
//         $('#period_start').val(row.period_start);
//         $('#period_end').val(row.period_end);
//         $('#description').val(row.description ?? '');
//     });
// }

// function upNovelty(id) {
//     $('#ModalNovelty').modal('show');
//     $('#noveltyModalTitle').html('Editar Novedad');
//     cleanInput();
//     limpiarValidaciones();
//     showCustomNovelty(id);

//     const u =
//         '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>' +
//         '<button class="btn btn-primary" onclick="updateNovelty(' + id + ')">Guardar</button>';

//     $(".modal-footer").html(u);
// }

// function updateNovelty(id) {
//     const route = `/admin/admin.nomina.novelties.update/${id}`;

//     const ajax_data = new FormData();
//     const participants = $('#participants').val() || [];
//     const linkType = $('#link_type').val();

//     if (!linkType) {
//         $('#error_link_type').text('Seleccione el tipo de vínculo.');
//         return;
//     }
//     if (!participants.length) {
//         $('#error_participants').text('Seleccione al menos un participante.');
//         return;
//     }

//     const participantType = linkType === 'CONTRATISTA'
//         ? 'App\\Models\\Tercero'
//         : 'App\\Models\\Empleado';

//     ajax_data.append('participant_type', participantType);
//     ajax_data.append('participant_id', participants[0]);
//     ajax_data.append('link_type', linkType);
//     ajax_data.append('nomina_concept_id', $('#nomina_concept_id').val());
//     ajax_data.append('quantity', $('#quantity').val());
//     ajax_data.append('amount', $('#amount').val());
//     ajax_data.append('period_start', $('#period_start').val());
//     ajax_data.append('period_end', $('#period_end').val());
//     ajax_data.append('description', $('#description').val());

//     $.ajax({
//         url: route,
//         headers: {
//             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
//             'X-HTTP-Method-Override': 'POST'
//         },
//         type: 'POST',
//         dataType: 'json',
//         data: ajax_data,
//         contentType: false,
//         processData: false,
//     }).then(response => {
//         Cargar();
//         $('#ModalNovelty').modal('hide');
//         toastr.success(response.message || 'Novedad actualizada correctamente.');
//     }).catch(e => {
//         limpiarValidaciones();
//         const arr = e.responseJSON || {};
//         if (e.status === 422 && arr.errors) {
//             $.each(arr.errors, function (key, value) {
//                 $('#error_' + key).text(value[0]);
//             });
//             toastr.warning('No fue posible guardar el registro, revisar los errores en los campos.');
//         } else if (e.status === 403) {
//             toastr.warning(arr.message || 'No autorizado.');
//         } else {
//             toastr.error(arr.message || 'Error inesperado.');
//         }
//     });
// }

// /**
//  * Carga conceptos para el select (recomendación):
//  * Crea un endpoint simple GET /admin/admin.nomina.concepts.list (opcional)
//  * o reutiliza /admin/admin.nomina.concepts.index?list=1
//  */
// function loadConceptsForNoveltySelect() {
//     // Sugerencia: endpoint liviano que devuelva [{id, text}]
//     const url = '/admin/admin.nomina.concepts.index?list=1';

//     $.get(url, (resp) => {
//         const $sel = $('#nomina_concept_id');
//         $sel.empty();
//         $sel.append('<option value="">Seleccione...</option>');

//         // Si el backend devuelve array
//         const items = resp.data || resp || [];
//         items.forEach(i => {
//             $sel.append(`<option value="${i.id}">${i.text}</option>`);
//         });
//     }).catch(() => {
//         // fallback: dejar el select vacío
//         $('#nomina_concept_id').html('<option value="">No fue posible cargar conceptos</option>');
//     });
// }


$(function () {
  toastr.options = { closeButton:true, positionClass:"toast-bottom-right", timeOut:"5000" };

  // defaults: último mes (si quieres)
  // aquí lo dejo en blanco para que el usuario filtre

    loadEmployees();
    loadConcepts();
    loadPayRunsForRecalc();
    initNoveltiesTable();
    initEmployeeSelect2();
    initDuplicateSelect2();
    loadEmployeesForDuplicate();
});

let novTable = null;

function initNoveltiesTable(){
  if ($.fn.DataTable.isDataTable('#novelties-table')) $('#novelties-table').DataTable().destroy();

  novTable = $('#novelties-table').DataTable({
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
    ajax: {
      url: '/admin/admin.nomina.novelties.index',
      data: function (d) {
        d.period_start = $('#filter_start').val();
        d.period_end   = $('#filter_end').val();
        d.status       = $('#filter_status').val();
      }
    },
    columns: [
      { data: 'DT_RowIndex', className:'exclude', orderable:false, searchable:false },
      { data: 'id', className:'exclude' },
      { data: 'participant_name' },
      { data: 'link_type' },
      { data: 'concept' },
      { data: 'quantity', className:'text-right' },
      { data: 'amount', className:'text-right' },
      { data: 'period', },
      { data: 'status_badge', className:'text-center' },
      { data: 'created_at' },
      { data: 'acciones', className:'exclude text-center', orderable:false, searchable:false },
    ],
    order: [[1, "desc"]],
  });
}

function reloadNovelties(){
  if(novTable) novTable.ajax.reload();
}

// function loadEmployees(){
//   $.get('/admin/admin.empleados.list', (resp)=>{
//     const $s = $('#employee_id');
//     $s.empty().append('<option value="">Seleccione...</option>');
//     (resp.data||[]).forEach(i => $s.append(`<option value="${i.id}">${i.text}</option>`));
//   });
// }

function loadConcepts(){
  $.get('/admin/admin.nomina.concepts.list', (resp)=>{
    const $s = $('#nomina_concept_id');
    $s.empty().append('<option value="">Seleccione...</option>');
    (resp.data||[]).forEach(i => $s.append(`<option value="${i.id}">${i.text}</option>`));
  });
}

function loadPayRunsForRecalc(){
  // ideal: endpoint por periodo; por ahora cargamos últimos N
  $.get('/admin/admin.nomina.payruns.list', (resp)=>{
    const $s = $('#recalc_pay_run_id');
    $s.empty().append('<option value="">(Sin PayRun)</option>');
    (resp.data||[]).forEach(i => $s.append(`<option value="${i.id}">${i.text}</option>`));
  });
}

function limpiarValidacionesNovelty(){
  ['employee_ids','nomina_concept_id','period_start','period_end','quantity','amount','description','status']
    .forEach(f => $('#error_'+f).text(''));
}

function cleanNovelty(){
    $('#id').val('');
//   $('#employee_id').val('');
    $('#employee_ids').val(null).trigger('change'); // multi
    $('#nomina_concept_id').val('');
    $('#period_start').val('');
    $('#period_end').val('');
    $('#quantity').val(1);
    $('#amount').val(0);
    $('#description').val('');
    $('#status').val('PENDING');
}

function regNovelty(){
  $('#ModalNovelty').modal('show');
  $('#noveltyModalTitle').text('Registrar Novedad');
  cleanNovelty();
  limpiarValidacionesNovelty();

  // ✅ MODO CREAR: multi habilitado
  setEmployeeModeCreate();

  $('.modal-footer').html(
    '<button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
    '<button class="btn btn-primary" onclick="storeNovelty()">Guardar</button>'
  );
}

// function storeNovelty(){
//   limpiarValidacionesNovelty();
//   const fd = new FormData();

//   // OJO: backend normalmente guarda morph participant; aquí mandamos employee_id y el backend lo convierte
//   fd.append('employee_id', $('#employee_id').val());
//   fd.append('nomina_concept_id', $('#nomina_concept_id').val());
//   fd.append('period_start', $('#period_start').val());
//   fd.append('period_end', $('#period_end').val());
//   fd.append('quantity', $('#quantity').val());
//   fd.append('amount', $('#amount').val());
//   fd.append('description', $('#description').val());
//   fd.append('status', $('#status').val());

//   $.ajax({
//     url: '/admin/admin.nomina.novelties.store',
//     headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
//     type: 'POST',
//     data: fd,
//     contentType: false,
//     processData: false,
//   }).then(r => {
//     $('#ModalNovelty').modal('hide');
//     toastr.success(r.message || 'Novedad creada');
//     reloadNovelties();
//   }).catch(e => {
//     const arr = e.responseJSON || {};
//     if (e.status === 422 && arr.errors) {
//       Object.keys(arr.errors).forEach(k => $('#error_'+k).text(arr.errors[k][0]));
//       toastr.warning('Revisa los campos.');
//     } else {
//       toastr.error(arr.message || 'Error');
//     }
//   });
// }

function storeNovelty(){
  limpiarValidacionesNovelty();

  const employeeIds = $('#employee_ids').val() || [];
  if(employeeIds.length === 0){
    $('#error_employee_ids').text('Seleccione al menos un empleado.');
    toastr.warning('Seleccione empleados.');
    return;
  }

  const fd = new FormData();
  employeeIds.forEach(id => fd.append('employee_ids[]', id)); // <- array

  fd.append('nomina_concept_id', $('#nomina_concept_id').val());
  fd.append('period_start', $('#period_start').val());
  fd.append('period_end', $('#period_end').val());
  fd.append('quantity', $('#quantity').val());
  fd.append('amount', $('#amount').val());
  fd.append('description', $('#description').val());
  fd.append('status', $('#status').val());

  $.ajax({
    url: '/admin/admin.nomina.novelties.store',
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    type: 'POST',
    data: fd,
    contentType: false,
    processData: false,
  }).then(r => {
    $('#ModalNovelty').modal('hide');
    toastr.success(r.message || 'Novedades creadas');
    reloadNovelties();
  }).catch(e => {
    const arr = e.responseJSON || {};
    if (e.status === 422 && arr.errors) {
      Object.keys(arr.errors).forEach(k => {
        // para employee_ids.0 etc:
        if(k.startsWith('employee_ids')) $('#error_employee_ids').text(arr.errors[k][0]);
        else $('#error_'+k).text(arr.errors[k][0]);
      });
      toastr.warning('Revisa los campos.');
    } else {
      toastr.error(arr.message || 'Error');
    }
  });
}


function upNovelty(id){
  $('#ModalNovelty').modal('show');
  $('#noveltyModalTitle').text('Editar Novedad');
  cleanNovelty();
  limpiarValidacionesNovelty();

  $.get('/admin/admin.nomina.novelties.edit/' + id, (resp)=>{
    const n = resp.data;
    $('#id').val(n.id);

    // ✅ Bloquear select y dejar solo ese empleado
    setEmployeeModeEdit(n.participant_id);
    $('#employee_id').val(n.participant_id);
    $('#nomina_concept_id').val(n.nomina_concept_id);
    $('#period_start').val(n.period_start);
    $('#period_end').val(n.period_end);
    $('#quantity').val(n.quantity);
    $('#amount').val(n.amount);
    $('#description').val(n.description || '');
    $('#status').val(n.status);
  });

  $('.modal-footer').html(
    '<button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
    '<button class="btn btn-primary" onclick="updateNovelty('+id+')">Actualizar</button>'
  );
}

function updateNovelty(id){
    limpiarValidacionesNovelty();

    const ids = $('#employee_ids').val() || [];
    const employeeId = ids.length ? ids[0] : null;

    if(!employeeId){
        $('#error_employee_ids').text('Empleado requerido.');
        toastr.warning('Empleado requerido.');
        return;
    }

    const fd = new FormData();
    fd.append('employee_id', employeeId);
    fd.append('nomina_concept_id', $('#nomina_concept_id').val());
    fd.append('period_start', $('#period_start').val());
    fd.append('period_end', $('#period_end').val());
    fd.append('quantity', $('#quantity').val());
    fd.append('amount', $('#amount').val());
    fd.append('description', $('#description').val());
    fd.append('status', $('#status').val());

    $.ajax({
        url: '/admin/admin.nomina.novelties.update/' + id,
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'X-HTTP-Method-Override': 'POST'
        },
        type: 'POST',
        data: fd,
        contentType: false,
        processData: false,
    }).then(r => {
        $('#ModalNovelty').modal('hide');
        toastr.success(r.message || 'Novedad actualizada');
        reloadNovelties();
    }).catch(e => {
        // const arr = e.responseJSON || {};
        // if (e.status === 422 && arr.errors) {
        //     Object.keys(arr.errors).forEach(k => $('#error_'+k).text(arr.errors[k][0]));
        //     toastr.warning('Revisa los campos.');
        // } else {
        //     toastr.error(arr.message || 'Error');
        // }
        const arr = e.responseJSON || {};
        if (e.status === 422 && arr.errors) {
            Object.keys(arr.errors).forEach(k => {
                if(k === 'employee_id') $('#error_employee_ids').text(arr.errors[k][0]);
                else $('#error_'+k).text(arr.errors[k][0]);
            });
            toastr.warning('Revisa los campos.');
        } else {
            toastr.error(arr.message || 'Error');
        }
    });
}

function openRecalcDestajoModal(){
  $('#ModalRecalcDestajo').modal('show');
  $('#recalc_start').val($('#filter_start').val());
  $('#recalc_end').val($('#filter_end').val());
}

function recalculateDestajo(){
  $('#error_recalc_start').text('');
  $('#error_recalc_end').text('');

  const period_start = $('#recalc_start').val();
  const period_end = $('#recalc_end').val();
  const pay_run_id = $('#recalc_pay_run_id').val();

  if(!period_start){ $('#error_recalc_start').text('Requerido'); return; }
  if(!period_end){ $('#error_recalc_end').text('Requerido'); return; }

  const fd = new FormData();
  fd.append('period_start', period_start);
  fd.append('period_end', period_end);
  if(pay_run_id) fd.append('pay_run_id', pay_run_id);

  $.ajax({
    url: '/admin/admin.nomina.novelties.recalculate-destajo-settlements',
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    type: 'POST',
    data: fd,
    contentType: false,
    processData: false,
  }).then(r=>{
    $('#ModalRecalcDestajo').modal('hide');
    toastr.success(r.message || 'Recalculo OK');
    reloadNovelties();
  }).catch(e=>{
    const arr = e.responseJSON || {};
    toastr.error(arr.message || 'Error recalculando destajo');
  });
}

function initEmployeeSelect2(){
    // Por si el modal se abre/cierra, select2 en modal necesita dropdownParent
    $('#employee_ids').select2({
        placeholder: 'Seleccione empleados...',
        width: '100%',
        dropdownParent: $('#ModalNovelty'),
        allowClear: true
    });
}

function loadEmployees(){
  $.get('/admin/admin.empleados.list', (resp)=>{
    const $s = $('#employee_ids');
    $s.empty();
    (resp.data||[]).forEach(i => {
      $s.append(new Option(i.text, i.id, false, false));
    });
    // refrescar select2
    $s.trigger('change');
  });
}

function setEmployeeModeCreate(){
  // habilitar multi
  $('#employee_ids').prop('disabled', false);
  // permite múltiples
  $('#employee_ids').attr('multiple', 'multiple');
  // limpiar selección
  $('#employee_ids').val(null).trigger('change');

  // mensaje UI opcional
  $('#employeeHelp').text('Puedes seleccionar varios empleados.');
}

function setEmployeeModeEdit(singleEmployeeId){
  // bloquear selección
  $('#employee_ids').prop('disabled', true);

  // dejar seleccionado el empleado del registro
  $('#employee_ids').val([String(singleEmployeeId)]).trigger('change');

  $('#employeeHelp').text('En edición solo se permite un empleado.');
}

function openDuplicateModal(id){
    $('#dup_id').val(id);
    $('#dup_employee_ids').val(null).trigger('change');
    $('#dup_skip_existing').prop('checked', true);
    $('#error_dup_employee_ids').text('');
    $('#ModalDuplicateNovelty').modal('show');
}

function initDuplicateSelect2(){
    $('#dup_employee_ids').select2({
        placeholder: 'Seleccione empleados destino...',
        width: '100%',
        dropdownParent: $('#ModalDuplicateNovelty'),
        allowClear: true
    });
}

function loadEmployeesForDuplicate(){
    $.get('/admin/admin.empleados.list', (resp)=>{
        const $s = $('#dup_employee_ids');
        $s.empty();
        (resp.data||[]).forEach(i => $s.append(new Option(i.text, i.id, false, false)));
        $s.trigger('change');
    });
}

function confirmDuplicateNovelty(){
    const id = $('#dup_id').val();
    const employeeIds = $('#dup_employee_ids').val() || [];
    const skipExisting = $('#dup_skip_existing').is(':checked') ? 1 : 0;

    $('#error_dup_employee_ids').text('');

    if(employeeIds.length === 0){
        $('#error_dup_employee_ids').text('Seleccione al menos un empleado.');
        toastr.warning('Seleccione empleados destino.');
        return;
    }

    const fd = new FormData();
    employeeIds.forEach(eid => fd.append('employee_ids[]', eid));
    fd.append('skip_existing', skipExisting);

    $.ajax({
        url: '/admin/admin.nomina.novelties.duplicate/' + id,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'POST',
        data: fd,
        contentType: false,
        processData: false,
    }).then(r=>{
        $('#ModalDuplicateNovelty').modal('hide');
        toastr.success(r.message || 'Duplicación realizada');
        reloadNovelties();
    }).catch(e=>{
        const arr = e.responseJSON || {};
        if(e.status === 422 && arr.errors){
        // si viene employee_ids.0 etc
        $('#error_dup_employee_ids').text(Object.values(arr.errors)[0][0]);
        toastr.warning('Revisa los campos.');
        } else {
        toastr.error(arr.message || 'Error duplicando');
        }
    });
}
