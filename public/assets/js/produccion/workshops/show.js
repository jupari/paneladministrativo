$(function () {
  toastr.options = { closeButton:true, positionClass:"toast-bottom-right", timeOut:"5000" };
  loadDevices();
});

function loadDevices() {
  if ($.fn.DataTable.isDataTable('#devices-table')) {
    $('#devices-table').DataTable().destroy();
  }

  $('#devices-table').DataTable({
    language: { url: "/assets/js/spanish.json" },
    responsive: true,
    ajax: {
      url: window.WORKSHOP_SHOW.routes.devices,
      dataSrc: 'data'
    },
    columns: [
      { data: null, render: (_, __, ___, meta) => meta.row + 1, orderable:false, searchable:false },
      { data: 'id' },
      { data: 'device_uuid' },
      { data: 'device_name', defaultContent: 'N/A' },
      { data: 'platform' },
      { data: 'app_version', defaultContent: 'N/A' },
      { data: 'os_version', defaultContent: 'N/A' },
      { data: 'last_login_at', defaultContent: 'N/A' },
      { data: 'last_sync_at', defaultContent: 'N/A' },
      {
        data: 'status',
        className: 'text-center',
        render: function (value) {
          const map = { active:'success', blocked:'warning', revoked:'danger' };
          const badge = map[value] || 'secondary';
          return `<span class="badge badge-${badge}">${(value || '').toUpperCase()}</span>`;
        }
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        className: 'text-center',
        render: function (row) {
          return `
            <button class="btn btn-sm btn-success" onclick="changeDeviceStatus(${row.id}, 'active')" title="Activar"><i class="fas fa-check"></i></button>
            <button class="btn btn-sm btn-warning" onclick="changeDeviceStatus(${row.id}, 'blocked')" title="Bloquear"><i class="fas fa-ban"></i></button>
            <button class="btn btn-sm btn-danger" onclick="changeDeviceStatus(${row.id}, 'revoked')" title="Revocar"><i class="fas fa-times"></i></button>
          `;
        }
      },
    ],
    order: [[1, 'desc']],
  });
}

function generatePairingQr() {
  $.ajax({
    url: window.WORKSHOP_SHOW.routes.generateQr,
    type: 'POST',
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    data: { ttl_minutes: 5 }
  }).done(function (resp) {
    const d = resp.data || {};
    $('#qr-section').show();
    $('#pairing_token').val(d.pairing_token || '');
    $('#qr_payload').val(d.qr_payload || '');
    $('#expires_at').val(d.expires_at || '');

    const encoded = encodeURIComponent(d.qr_payload || '');
    $('#qr-image').attr('src', 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=' + encoded);

    toastr.success('QR generado correctamente.');
  }).fail(function (e) {
    toastr.error(e.responseJSON?.message || 'No fue posible generar el QR.');
  });
}

function changeDeviceStatus(deviceId, status) {
  $.ajax({
    url: window.WORKSHOP_SHOW.routes.updateDeviceStatusBase + '/' + deviceId + '/status',
    type: 'PATCH',
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
      'X-Requested-With': 'XMLHttpRequest'
    },
    data: { status }
  }).done(function () {
    toastr.success('Estado de dispositivo actualizado.');
    loadDevices();
  }).fail(function (e) {
    toastr.error(e.responseJSON?.message || 'No fue posible actualizar el dispositivo.');
  });
}
