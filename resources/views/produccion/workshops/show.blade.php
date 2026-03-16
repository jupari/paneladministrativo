@extends('adminlte::page')

@section('title', 'Detalle Taller')
@section('plugin.Datatables', true)
@section('plugin.Sweetalert2', true)

@section('content')
@php
  $breadcrumbs = [
    ['title' => 'Producción', 'icon' => 'fas fa-industry', 'url' => null],
    ['title' => 'Talleres', 'icon' => 'fas fa-warehouse', 'url' => route('admin.produccion.workshops.index')],
  ];
  $currentTitle = 'Detalle Taller';
  $currentIcon = 'fas fa-tools';
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h4 class="mb-0">{{ $workshop->name }} <small class="text-muted">({{ $workshop->code }})</small></h4>
  </div>
  <div class="card-body">
    <div class="d-flex justify-content-md-start my-3">
        <button class="btn btn-primary" onclick="generatePairingQr()">
          <i class="fas fa-qrcode"></i> Generar QR de vinculación
        </button>
    </div>

    <div class="row mb-3">
      <div class="col-md-4"><b>Dirección:</b> {{ $workshop->address ?: 'N/A' }}</div>
      <div class="col-md-4"><b>Coordinador:</b> {{ $workshop->coordinator_name ?: 'N/A' }}</div>
      <div class="col-md-4"><b>Teléfono:</b> {{ $workshop->coordinator_phone ?: 'N/A' }}</div>
    </div>

    <div class="row mb-4">
      <div class="col-md-3">
        <div class="small-box bg-info">
          <div class="inner">
            <h3>{{ $devicesCount }}</h3>
            <p>Dispositivos</p>
          </div>
          <div class="icon"><i class="fas fa-mobile-alt"></i></div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="small-box bg-success">
          <div class="inner">
            <h3>{{ $activeDevicesCount }}</h3>
            <p>Activos</p>
          </div>
          <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
      </div>
    </div>

    <div class="row mb-4" id="qr-section" style="display:none;">
      <div class="col-md-4 text-center">
        <img id="qr-image" src="" alt="QR vinculación" style="max-width:260px; width:100%; border:1px solid #ddd; padding:8px; border-radius:8px;"/>
      </div>
      <div class="col-md-8">
        <div class="form-group">
          <label>Payload QR</label>
          <input type="text" id="qr_payload" class="form-control" readonly>
        </div>
        <div class="form-group">
          <label>Token de vinculación</label>
          <input type="text" id="pairing_token" class="form-control" readonly>
        </div>
        <div class="form-group mb-0">
          <label>Expira en</label>
          <input type="text" id="expires_at" class="form-control" readonly>
        </div>
      </div>
    </div>

    <h5>Dispositivos vinculados</h5>
    <div class="table-responsive">
      <table id="devices-table" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>ID</th>
            <th>UUID</th>
            <th>Nombre</th>
            <th>Plataforma</th>
            <th>Versión app</th>
            <th>Versión SO</th>
            <th>Último login</th>
            <th>Última sync</th>
            <th>Estado</th>
            <th class="text-center">Acciones</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>
@stop

@section('js')
<script>
window.WORKSHOP_SHOW = {
  id: {{ $workshop->id }},
  routes: {
    generateQr: "{{ route('admin.produccion.workshops.pairing-qr', $workshop->id) }}",
    devices: "{{ route('admin.produccion.workshops.devices', $workshop->id) }}",
    updateDeviceStatusBase: "{{ url('/admin/admin.produccion.workshops/'.$workshop->id.'/devices') }}"
  }
};
</script>
<script src="{{ asset('assets/js/produccion/workshops/show.js') }}?v={{ time() }}"></script>
@stop
