@extends('adminlte::page')
@section('title', 'Desprendible de Nómina')

@section('content')
<div class="payslip-page">

  <div class="d-print-none mb-2">
    <button class="btn btn-primary btn-sm" onclick="window.print()">
      <i class="fas fa-print"></i> Imprimir
    </button>
    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
      <i class="fas fa-arrow-left"></i> Volver
    </a>
  </div>

  <div class="payslip-card">
    {{-- Header --}}
    <div class="ps-header">
      <div>
        <div class="ps-title">DESPRENDIBLE DE NÓMINA</div>
        <div class="ps-sub">
          Periodo: <strong>{{ $payRun->period_start }} a {{ $payRun->period_end }}</strong>
          &nbsp;|&nbsp; Pago: <strong>{{ $payRun->pay_date }}</strong>
        </div>
      </div>
      <div class="ps-right">
        <div><strong>Estado:</strong> {{ $participant->status }}</div>
        <div><strong>Tipo:</strong> {{ $payRun->run_type }}</div>
        <div><strong>Ref:</strong> PR-{{ $payRun->id }}-{{ $participant->id }}</div>
      </div>
    </div>

    {{-- Employee box --}}
    <div class="ps-box">
      <div class="ps-row">
        <div class="ps-col">
          <div><strong>Empleado:</strong> {{ $empleado ? ($empleado->nombres.' '.$empleado->apellidos) : ($participant->participant_id) }}</div>
          <div><strong>Identificación:</strong> {{ $empleado?->identificacion ?? '-' }}</div>
        </div>
        <div class="ps-col">
          <div><strong>Cargo:</strong> {{ $empleado?->cargo?->nombre ?? '-' }}</div>
          <div><strong>Vinculación:</strong> {{ $participant->link_type }}</div>
        </div>
      </div>
    </div>

    {{-- Tables --}}
    <div class="ps-grid">

      {{-- Devengado Salarial --}}
      <div class="ps-panel">
        <div class="ps-panel-title">Devengados Salariales</div>
        <table class="ps-table">
          <thead>
            <tr>
              <th>Concepto</th>
              <th class="t-r">Cant</th>
              <th class="t-r">Valor</th>
            </tr>
          </thead>
          <tbody>
            @forelse($devSalarial as $l)
              <tr>
                <td>{{ $l->concept->name ?? ('Concepto #'.$l->nomina_concept_id) }}</td>
                <td class="t-r">{{ rtrim(rtrim(number_format((float)$l->quantity, 2, '.', ''), '0'), '.') }}</td>
                <td class="t-r">$ {{ number_format((float)$l->amount, 0, ',', '.') }}</td>
              </tr>
            @empty
              <tr><td colspan="3" class="t-c muted">Sin registros</td></tr>
            @endforelse
          </tbody>
          <tfoot>
            <tr>
              <th colspan="2" class="t-r">Total Salarial</th>
              <th class="t-r">$ {{ number_format((float)$totDevSal, 0, ',', '.') }}</th>
            </tr>
          </tfoot>
        </table>
      </div>

      {{-- Devengado No Salarial --}}
      <div class="ps-panel">
        <div class="ps-panel-title">Devengados No Salariales</div>
        <table class="ps-table">
          <thead>
            <tr>
              <th>Concepto</th>
              <th class="t-r">Cant</th>
              <th class="t-r">Valor</th>
            </tr>
          </thead>
          <tbody>
            @forelse($devNoSalarial as $l)
              <tr>
                <td>{{ $l->concept->name ?? ('Concepto #'.$l->nomina_concept_id) }}</td>
                <td class="t-r">{{ rtrim(rtrim(number_format((float)$l->quantity, 2, '.', ''), '0'), '.') }}</td>
                <td class="t-r">$ {{ number_format((float)$l->amount, 0, ',', '.') }}</td>
              </tr>
            @empty
              <tr><td colspan="3" class="t-c muted">Sin registros</td></tr>
            @endforelse
          </tbody>
          <tfoot>
            <tr>
              <th colspan="2" class="t-r">Total No Salarial</th>
              <th class="t-r">$ {{ number_format((float)$totDevNoSal, 0, ',', '.') }}</th>
            </tr>
          </tfoot>
        </table>
      </div>

      {{-- Deducciones --}}
      <div class="ps-panel ps-panel-full">
        <div class="ps-panel-title">Deducciones</div>
        <table class="ps-table">
          <thead>
            <tr>
              <th>Concepto</th>
              <th class="t-r">Cant</th>
              <th class="t-r">Valor</th>
            </tr>
          </thead>
          <tbody>
            @forelse($deducciones as $l)
              <tr>
                <td>{{ $l->concept->name ?? ('Concepto #'.$l->nomina_concept_id) }}</td>
                <td class="t-r">{{ rtrim(rtrim(number_format((float)$l->quantity, 2, '.', ''), '0'), '.') }}</td>
                <td class="t-r">$ {{ number_format((float)$l->amount, 0, ',', '.') }}</td>
              </tr>
            @empty
              <tr><td colspan="3" class="t-c muted">Sin registros</td></tr>
            @endforelse
          </tbody>
          <tfoot>
            <tr>
              <th colspan="2" class="t-r">Total Deducciones</th>
              <th class="t-r">$ {{ number_format((float)$totDed, 0, ',', '.') }}</th>
            </tr>
          </tfoot>
        </table>
      </div>

    </div>

    {{-- Summary --}}
    <div class="ps-summary">
      <div class="ps-summary-row">
        <div class="lbl">Total Devengado</div>
        <div class="val">$ {{ number_format((float)$totDev, 0, ',', '.') }}</div>
      </div>
      <div class="ps-summary-row">
        <div class="lbl">Total Deducciones</div>
        <div class="val">$ {{ number_format((float)$totDed, 0, ',', '.') }}</div>
      </div>
      <div class="ps-summary-row net">
        <div class="lbl">NETO A PAGAR</div>
        <div class="val">$ {{ number_format((float)$neto, 0, ',', '.') }}</div>
      </div>
    </div>

    {{-- Sign --}}
    <div class="ps-sign">
      <div class="sig">
        <div class="line"></div>
        <div class="cap">Firma Empleado</div>
      </div>
      <div class="sig">
        <div class="line"></div>
        <div class="cap">Firma Empresa</div>
      </div>
    </div>

  </div>
</div>
@stop

@section('css')
    <style>
        /* --- Media carta (Half Letter) --- */
        @page { size: 5.5in 8.5in; margin: 0.35in; }
        .payslip-page{ font-size: 11px; }
        .payslip-card{
        background:#fff;
        border: 1px solid #dcdcdc;
        border-radius: 8px;
        padding: 12px;
        }

        /* header */
        .ps-header{ display:flex; justify-content:space-between; gap:10px; border-bottom:1px solid #eee; padding-bottom:8px; margin-bottom:10px; }
        .ps-title{ font-weight: 700; font-size: 14px; letter-spacing:.3px; }
        .ps-sub{ color:#666; font-size: 11px; }
        .ps-right{ text-align:right; font-size: 11px; color:#333; }

        /* box */
        .ps-box{ border:1px solid #eee; border-radius:8px; padding:8px; margin-bottom:10px; }
        .ps-row{ display:flex; gap:10px; }
        .ps-col{ flex:1; }

        /* grid */
        .ps-grid{ display:grid; grid-template-columns: 1fr 1fr; gap:10px; }
        .ps-panel{ border:1px solid #eee; border-radius:8px; overflow:hidden; }
        .ps-panel-full{ grid-column: 1 / -1; }
        .ps-panel-title{ background:#f7f7f7; padding:6px 8px; font-weight:600; }

        /* table */
        .ps-table{ width:100%; border-collapse:collapse; }
        .ps-table th, .ps-table td{ padding:5px 6px; border-bottom:1px solid #f0f0f0; vertical-align:top; }
        .ps-table thead th{ background:#fafafa; font-weight:600; }
        .ps-table tfoot th{ background:#fafafa; }
        .t-r{ text-align:right; }
        .t-c{ text-align:center; }
        .muted{ color:#888; }

        /* summary */
        .ps-summary{ margin-top:10px; border:1px solid #eee; border-radius:8px; overflow:hidden; }
        .ps-summary-row{ display:flex; justify-content:space-between; padding:7px 10px; border-bottom:1px solid #f0f0f0; }
        .ps-summary-row:last-child{ border-bottom:none; }
        .ps-summary-row.net{ background:#f7f7f7; font-weight:700; font-size: 13px; }

        /* sign */
        .ps-sign{ display:flex; gap:16px; margin-top:14px; }
        .sig{ flex:1; text-align:center; }
        .sig .line{ border-top:1px solid #999; margin:26px 0 6px; }
        .sig .cap{ color:#666; font-size: 10px; }

        /* print cleanup */
        @media print{
        .d-print-none{ display:none !important; }
        .content-wrapper, .content{ background:#fff !important; }
        .payslip-card{ border:none; }
        }
    </style>
@stop
